<?php

namespace App\Services;

use App\Models\Anggota;
use App\Models\Buku;
use App\Models\Peminjaman;
use Carbon\CarbonInterface;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

/**
 * Seluruh perubahan status peminjaman dan mutasi stok buku lewat sini.
 *
 * Setiap method yang menyentuh stok berjalan di dalam transaksi dan mengunci
 * baris buku (SELECT ... FOR UPDATE) supaya dua petugas yang mengonfirmasi
 * bersamaan tidak bisa membuat stok minus.
 */
class PeminjamanService
{
    /** Masa pinjam normal (hari). */
    public const MASA_PINJAM = 7;

    /** Total masa pinjam setelah diperpanjang 1x (hari). */
    public const MASA_PERPANJANG = 14;

    /** Denda keterlambatan per hari untuk tiap judul (rupiah). */
    public const DENDA_PER_HARI = 2000;

    public const STATUS_KONFIRMASI = 'konfirmasi';

    public const STATUS_DIPINJAM = 'dipinjam';

    public const STATUS_PERPANJANG = 'perpanjang';

    public const STATUS_KEMBALI = 'kembali';

    public const SEMUA_STATUS = [
        self::STATUS_KONFIRMASI,
        self::STATUS_DIPINJAM,
        self::STATUS_PERPANJANG,
        self::STATUS_KEMBALI,
    ];

    /** Status yang berarti buku sedang di tangan anggota (menahan stok). */
    public const STATUS_MENAHAN_STOK = [self::STATUS_DIPINJAM, self::STATUS_PERPANJANG];

    /**
     * Anggota mengajukan peminjaman dari katalog. Stok belum berkurang
     * sampai petugas mengonfirmasi, tapi ketersediaan dicek lebih dulu.
     */
    public function ajukan(Anggota $anggota, Buku $buku, int $jumlah): Peminjaman
    {
        return DB::transaction(function () use ($anggota, $buku, $jumlah) {
            $buku = $this->kunciBuku($buku->id);
            $this->pastikanStokCukup($buku, $jumlah);

            return Peminjaman::create([
                'anggota_id' => $anggota->nim,
                'buku_id' => $buku->id,
                'jumlah' => $jumlah,
                'tgl_pinjam' => now()->toDateString(),
                'status' => self::STATUS_KONFIRMASI,
                'perpanjang' => 0,
                'denda' => 0,
            ]);
        });
    }

    /**
     * Petugas/admin mencatat peminjaman langsung di loket.
     */
    public function pinjamLangsung(
        int $anggotaId,
        int $bukuId,
        int $jumlah,
        ?string $tglPinjam = null,
        string $status = self::STATUS_DIPINJAM,
    ): Peminjaman {
        return DB::transaction(function () use ($anggotaId, $bukuId, $jumlah, $tglPinjam, $status) {
            $buku = $this->kunciBuku($bukuId);

            if ($this->menahanStok($status)) {
                $this->pastikanStokCukup($buku, $jumlah);
                $buku->decrement('stok', $jumlah);
            }

            return Peminjaman::create([
                'anggota_id' => $anggotaId,
                'buku_id' => $buku->id,
                'jumlah' => $jumlah,
                'tgl_pinjam' => $tglPinjam ?? now()->toDateString(),
                'status' => $status,
                'perpanjang' => $status === self::STATUS_PERPANJANG ? 1 : 0,
                'denda' => 0,
            ]);
        });
    }

    /**
     * Petugas menyetujui pengajuan: stok berkurang, status menjadi dipinjam.
     */
    public function konfirmasi(Peminjaman $peminjaman): Peminjaman
    {
        return DB::transaction(function () use ($peminjaman) {
            $peminjaman = $this->kunciPeminjaman($peminjaman);

            if ($peminjaman->status !== self::STATUS_KONFIRMASI) {
                throw ValidationException::withMessages(['status' => 'Peminjaman ini sudah dikonfirmasi.']);
            }

            $buku = $this->kunciBuku($peminjaman->buku_id);
            $this->pastikanStokCukup($buku, $peminjaman->jumlah);
            $buku->decrement('stok', $peminjaman->jumlah);

            $peminjaman->status = self::STATUS_DIPINJAM;
            $peminjaman->save();

            return $peminjaman;
        });
    }

    /**
     * Perpanjangan hanya boleh 1x dan hanya saat status dipinjam.
     */
    public function perpanjang(Peminjaman $peminjaman): Peminjaman
    {
        if ($peminjaman->status !== self::STATUS_DIPINJAM || $peminjaman->perpanjang) {
            throw ValidationException::withMessages([
                'perpanjang' => 'Peminjaman hanya bisa diperpanjang satu kali saat berstatus dipinjam.',
            ]);
        }

        $peminjaman->status = self::STATUS_PERPANJANG;
        $peminjaman->perpanjang = 1;
        $peminjaman->save();

        return $peminjaman;
    }

    /**
     * Pengembalian: hitung lama pinjam & denda, kembalikan stok.
     */
    public function kembalikan(Peminjaman $peminjaman, ?CarbonInterface $tglKembali = null): Peminjaman
    {
        return DB::transaction(function () use ($peminjaman, $tglKembali) {
            $peminjaman = $this->kunciPeminjaman($peminjaman);

            if (! $this->menahanStok($peminjaman->status)) {
                throw ValidationException::withMessages(['status' => 'Buku ini tidak sedang dipinjam.']);
            }

            $tglKembali ??= now();
            $lamaPinjam = $this->hitungLamaPinjam($peminjaman->tgl_pinjam, $tglKembali);

            $this->kunciBuku($peminjaman->buku_id)->increment('stok', $peminjaman->jumlah);

            $peminjaman->status = self::STATUS_KEMBALI;
            $peminjaman->tgl_kembali = $tglKembali->toDateString();
            $peminjaman->lama_pinjam = $lamaPinjam;
            $peminjaman->denda = $this->hitungDenda($lamaPinjam, (bool) $peminjaman->perpanjang);
            $peminjaman->save();

            return $peminjaman;
        });
    }

    /**
     * Membatalkan pengajuan yang belum dikonfirmasi (tidak menyentuh stok).
     */
    public function batalkan(Peminjaman $peminjaman): void
    {
        if ($peminjaman->status !== self::STATUS_KONFIRMASI) {
            throw ValidationException::withMessages([
                'status' => 'Hanya pengajuan berstatus konfirmasi yang dapat dibatalkan.',
            ]);
        }

        $peminjaman->delete();
    }

    /**
     * Admin menghapus transaksi apa pun; stok dikembalikan bila buku masih di luar.
     */
    public function hapus(Peminjaman $peminjaman): void
    {
        DB::transaction(function () use ($peminjaman) {
            $peminjaman = $this->kunciPeminjaman($peminjaman);

            if ($this->menahanStok($peminjaman->status)) {
                $this->kunciBuku($peminjaman->buku_id)->increment('stok', $peminjaman->jumlah);
            }

            $peminjaman->delete();
        });
    }

    /**
     * Admin mengedit transaksi secara bebas. Stok disinkronkan dari selisih
     * "berapa yang ditahan sebelum" vs "berapa yang ditahan sesudah", jadi
     * mengedit tanpa mengubah apa pun tidak menggeser stok.
     *
     * @param  array{jumlah: int|string, tgl_pinjam: string, status: string, perpanjang?: int|string|null, tgl_kembali?: string|null}  $data
     */
    public function ubah(Peminjaman $peminjaman, array $data): Peminjaman
    {
        return DB::transaction(function () use ($peminjaman, $data) {
            $peminjaman = $this->kunciPeminjaman($peminjaman);
            $buku = $this->kunciBuku($peminjaman->buku_id);

            $statusBaru = $data['status'];
            $jumlahBaru = (int) $data['jumlah'];

            $ditahanLama = $this->menahanStok($peminjaman->status) ? $peminjaman->jumlah : 0;
            $ditahanBaru = $this->menahanStok($statusBaru) ? $jumlahBaru : 0;
            $selisih = $ditahanBaru - $ditahanLama;

            if ($selisih > 0) {
                $this->pastikanStokCukup($buku, $selisih);
                $buku->decrement('stok', $selisih);
            } elseif ($selisih < 0) {
                $buku->increment('stok', -$selisih);
            }

            $peminjaman->jumlah = $jumlahBaru;
            $peminjaman->tgl_pinjam = $data['tgl_pinjam'];
            $peminjaman->status = $statusBaru;
            $peminjaman->perpanjang = (int) ($data['perpanjang'] ?? $peminjaman->perpanjang);

            if ($statusBaru === self::STATUS_KEMBALI) {
                $tglKembali = Carbon::parse($data['tgl_kembali'] ?? now());
                $lamaPinjam = $this->hitungLamaPinjam($peminjaman->tgl_pinjam, $tglKembali);
                $peminjaman->tgl_kembali = $tglKembali->toDateString();
                $peminjaman->lama_pinjam = $lamaPinjam;
                $peminjaman->denda = $this->hitungDenda($lamaPinjam, (bool) $peminjaman->perpanjang);
            } else {
                $peminjaman->tgl_kembali = null;
                $peminjaman->lama_pinjam = null;
                $peminjaman->denda = 0;
            }

            $peminjaman->save();

            return $peminjaman;
        });
    }

    public function hitungLamaPinjam(string|CarbonInterface $tglPinjam, CarbonInterface $tglKembali): int
    {
        $mulai = Carbon::parse($tglPinjam)->startOfDay();
        $selesai = Carbon::parse($tglKembali)->startOfDay();

        return max(0, (int) $mulai->diffInDays($selesai));
    }

    /**
     * Denda = hari keterlambatan x tarif; batas 7 hari, atau 14 hari bila diperpanjang.
     */
    public function hitungDenda(int $lamaPinjam, bool $diperpanjang): int
    {
        $batas = $diperpanjang ? self::MASA_PERPANJANG : self::MASA_PINJAM;

        return max(0, $lamaPinjam - $batas) * self::DENDA_PER_HARI;
    }

    public function menahanStok(string $status): bool
    {
        return in_array($status, self::STATUS_MENAHAN_STOK, true);
    }

    private function kunciBuku(int $bukuId): Buku
    {
        return Buku::whereKey($bukuId)->lockForUpdate()->firstOrFail();
    }

    private function kunciPeminjaman(Peminjaman $peminjaman): Peminjaman
    {
        return Peminjaman::whereKey($peminjaman->getKey())->lockForUpdate()->firstOrFail();
    }

    private function pastikanStokCukup(Buku $buku, int $jumlah): void
    {
        if ($buku->stok < $jumlah) {
            throw ValidationException::withMessages([
                'jumlah' => "Stok buku \"{$buku->judul}\" tidak mencukupi (tersisa {$buku->stok}).",
            ]);
        }
    }
}
