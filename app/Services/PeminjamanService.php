<?php

namespace App\Services;

use App\Enums\Role;
use App\Models\Anggota;
use App\Models\Buku;
use App\Models\Peminjaman;
use App\Models\User;
use App\Notifications\BukuDikembalikan;
use App\Notifications\BukuTersedia;
use App\Notifications\NotifikasiPerpustakaan;
use App\Notifications\PengajuanBaru;
use App\Notifications\PengajuanDisetujui;
use App\Notifications\PengajuanKedaluwarsa;
use Carbon\CarbonInterface;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Notification;
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
    public const STATUS_KONFIRMASI = 'konfirmasi';

    public const STATUS_DIPINJAM = 'dipinjam';

    public const STATUS_PERPANJANG = 'perpanjang';

    public const STATUS_KEMBALI = 'kembali';

    /** Antrean saat stok habis; otomatis naik ke konfirmasi begitu ada stok kembali. */
    public const STATUS_BOOKING = 'booking';

    public const SEMUA_STATUS = [
        self::STATUS_BOOKING,
        self::STATUS_KONFIRMASI,
        self::STATUS_DIPINJAM,
        self::STATUS_PERPANJANG,
        self::STATUS_KEMBALI,
    ];

    /** Status yang berarti buku sedang di tangan anggota (menahan stok). */
    public const STATUS_MENAHAN_STOK = [self::STATUS_DIPINJAM, self::STATUS_PERPANJANG];

    /** Status sebelum buku diserahkan: belum menahan stok, belum punya jatuh tempo, boleh dibatalkan anggota. */
    public const STATUS_MENUNGGU = [self::STATUS_BOOKING, self::STATUS_KONFIRMASI];

    /** Status yang sudah disetujui petugas (dihitung sebagai peminjaman nyata di statistik). */
    public const STATUS_TERKONFIRMASI = [self::STATUS_DIPINJAM, self::STATUS_PERPANJANG, self::STATUS_KEMBALI];

    /**
     * Aturan (masa_pinjam, masa_perpanjang, maks_perpanjang, denda_per_hari)
     * dari config/perpustakaan.php; bisa diberikan langsung untuk unit test.
     *
     * @var array{masa_pinjam: int, masa_perpanjang: int, maks_perpanjang: int, denda_per_hari: int}
     */
    private array $aturan;

    /**
     * @param  array{masa_pinjam?: int, masa_perpanjang?: int, maks_perpanjang?: int, denda_per_hari?: int}|null  $aturan
     */
    public function __construct(?array $aturan = null)
    {
        $this->aturan = $aturan ?? config('perpustakaan');
    }

    public function masaPinjam(): int
    {
        return $this->aturan['masa_pinjam'];
    }

    public function masaPerpanjang(): int
    {
        return $this->aturan['masa_perpanjang'];
    }

    public function dendaPerHari(): int
    {
        return $this->aturan['denda_per_hari'];
    }

    /**
     * Anggota mengajukan peminjaman dari katalog. Stok belum berkurang
     * sampai petugas mengonfirmasi, tapi ketersediaan dicek lebih dulu.
     */
    public function ajukan(Anggota $anggota, Buku $buku, int $jumlah): Peminjaman
    {
        return DB::transaction(function () use ($anggota, $buku, $jumlah) {
            $buku = $this->kunciBuku($buku->id);
            $this->pastikanStokCukup($buku, $jumlah);

            $peminjaman = Peminjaman::create([
                'anggota_id' => $anggota->nim,
                'buku_id' => $buku->id,
                'jumlah' => $jumlah,
                'tgl_pinjam' => now()->toDateString(),
                'status' => self::STATUS_KONFIRMASI,
                'perpanjang' => 0,
                'denda' => 0,
            ]);

            $this->beritahuPetugas($peminjaman);

            return $peminjaman;
        });
    }

    /**
     * Anggota memesan buku yang stoknya habis. Begitu ada eksemplar kembali,
     * booking tertua otomatis naik menjadi pengajuan (konfirmasi) — lihat
     * prosesAntreanBooking(). Satu anggota hanya boleh punya satu transaksi
     * yang belum selesai per buku.
     */
    public function booking(Anggota $anggota, Buku $buku): Peminjaman
    {
        return DB::transaction(function () use ($anggota, $buku) {
            $buku = $this->kunciBuku($buku->id);

            if ($buku->stok > 0) {
                throw ValidationException::withMessages([
                    'booking' => "Stok buku \"{$buku->judul}\" masih tersedia, silakan ajukan peminjaman langsung.",
                ]);
            }

            $sudahAda = Peminjaman::where('anggota_id', $anggota->nim)
                ->where('buku_id', $buku->id)
                ->where('status', '!=', self::STATUS_KEMBALI)
                ->exists();

            if ($sudahAda) {
                throw ValidationException::withMessages([
                    'booking' => 'Anda sudah memiliki booking atau peminjaman yang belum selesai untuk buku ini.',
                ]);
            }

            $booking = Peminjaman::create([
                'anggota_id' => $anggota->nim,
                'buku_id' => $buku->id,
                'jumlah' => 1,
                'tgl_pinjam' => now()->toDateString(),
                'status' => self::STATUS_BOOKING,
                'perpanjang' => 0,
                'denda' => 0,
            ]);

            $this->beritahuPetugas($booking);

            return $booking;
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

            $tglPinjam ??= now()->toDateString();
            $diperpanjang = $status === self::STATUS_PERPANJANG;

            return Peminjaman::create([
                'anggota_id' => $anggotaId,
                'buku_id' => $buku->id,
                'jumlah' => $jumlah,
                'tgl_pinjam' => $tglPinjam,
                'tgl_harus_kembali' => $this->menunggu($status) ? null : $this->jatuhTempo($tglPinjam, $diperpanjang),
                'status' => $status,
                'perpanjang' => $diperpanjang ? 1 : 0,
                'denda' => 0,
            ]);
        });
    }

    /**
     * Petugas menyetujui pengajuan: stok berkurang, status menjadi dipinjam.
     *
     * Selama status booking/konfirmasi, tgl_pinjam berarti "tanggal masuk
     * antrean" (diisi saat ajukan/booking/promosi). Saat dikonfirmasi, tanggal
     * itu diganti hari ini supaya masa pinjam dihitung sejak buku benar-benar
     * diserahkan, bukan sejak anggota mengajukan. Tanggal pengajuan tetap
     * tersimpan di created_at.
     */
    public function konfirmasi(Peminjaman $peminjaman): Peminjaman
    {
        return DB::transaction(function () use ($peminjaman) {
            $peminjaman = $this->kunciPeminjaman($peminjaman);

            if ($peminjaman->status !== self::STATUS_KONFIRMASI) {
                throw ValidationException::withMessages(['status' => 'Hanya pengajuan berstatus konfirmasi yang dapat disetujui.']);
            }

            $buku = $this->kunciBuku($peminjaman->buku_id);
            $this->pastikanStokCukup($buku, $peminjaman->jumlah);
            $buku->decrement('stok', $peminjaman->jumlah);

            $peminjaman->status = self::STATUS_DIPINJAM;
            $peminjaman->tgl_pinjam = now()->toDateString();
            $peminjaman->tgl_harus_kembali = $this->jatuhTempo($peminjaman->tgl_pinjam, false);
            $peminjaman->save();

            $this->beritahuAnggota($peminjaman, new PengajuanDisetujui($peminjaman));

            return $peminjaman;
        });
    }

    /**
     * Perpanjangan hanya saat buku masih di tangan anggota dan belum melewati
     * batas maks_perpanjang (default 1x).
     */
    public function perpanjang(Peminjaman $peminjaman): Peminjaman
    {
        if (! $this->menahanStok($peminjaman->status) || $peminjaman->perpanjang >= $this->aturan['maks_perpanjang']) {
            throw ValidationException::withMessages([
                'perpanjang' => "Peminjaman hanya bisa diperpanjang {$this->aturan['maks_perpanjang']} kali saat berstatus dipinjam.",
            ]);
        }

        $peminjaman->status = self::STATUS_PERPANJANG;
        $peminjaman->perpanjang = $peminjaman->perpanjang + 1;
        $peminjaman->tgl_harus_kembali = $this->jatuhTempo($peminjaman->tgl_pinjam, true);
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

            $buku = $this->kunciBuku($peminjaman->buku_id);
            $buku->increment('stok', $peminjaman->jumlah);

            $peminjaman->status = self::STATUS_KEMBALI;
            $peminjaman->tgl_kembali = $tglKembali->toDateString();
            $peminjaman->lama_pinjam = $lamaPinjam;
            $peminjaman->tgl_harus_kembali ??= $this->jatuhTempo($peminjaman->tgl_pinjam, (bool) $peminjaman->perpanjang);
            $peminjaman->denda = $this->hitungDendaDariJatuhTempo($peminjaman->tgl_harus_kembali, $tglKembali);
            $peminjaman->save();

            $this->beritahuAnggota($peminjaman, new BukuDikembalikan($peminjaman));
            $this->prosesAntreanBooking($buku);

            return $peminjaman;
        });
    }

    /**
     * Membatalkan booking / pengajuan yang belum dikonfirmasi (tidak menyentuh stok).
     */
    public function batalkan(Peminjaman $peminjaman): void
    {
        if (! $this->menunggu($peminjaman->status)) {
            throw ValidationException::withMessages([
                'status' => 'Hanya booking atau pengajuan berstatus konfirmasi yang dapat dibatalkan.',
            ]);
        }

        $peminjaman->delete();
    }

    /**
     * Membatalkan pengajuan konfirmasi yang lewat batas ambil (lihat
     * Peminjaman::batasAmbil()) — termasuk hasil promosi booking. Stok tidak
     * berubah, tetapi antrean booking buku itu diproses lagi supaya giliran
     * berpindah ke anggota berikutnya. Dijalankan command perpus:kedaluwarsa-pengajuan.
     *
     * @return int jumlah pengajuan yang (akan) dibatalkan
     */
    public function kedaluwarsakan(bool $dryRun = false): int
    {
        $jumlah = 0;

        foreach (Peminjaman::kedaluwarsa()->with(['anggota.user', 'buku'])->lazyById(100) as $pengajuan) {
            $jumlah++;

            if ($dryRun) {
                continue;
            }

            DB::transaction(function () use ($pengajuan) {
                $buku = $this->kunciBuku($pengajuan->buku_id);
                $notifikasi = new PengajuanKedaluwarsa($pengajuan); // salin data sebelum barisnya dihapus

                $pengajuan->delete();

                $this->beritahuAnggota($pengajuan, $notifikasi);
                $this->prosesAntreanBooking($buku);
            });
        }

        return $jumlah;
    }

    /**
     * Admin menghapus transaksi apa pun; stok dikembalikan bila buku masih di luar.
     */
    public function hapus(Peminjaman $peminjaman): void
    {
        DB::transaction(function () use ($peminjaman) {
            $peminjaman = $this->kunciPeminjaman($peminjaman);
            $buku = $this->kunciBuku($peminjaman->buku_id);
            $stokKembali = $this->menahanStok($peminjaman->status);

            if ($stokKembali) {
                $buku->increment('stok', $peminjaman->jumlah);
            }

            $peminjaman->delete();

            if ($stokKembali) {
                $this->prosesAntreanBooking($buku);
            }
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
            $peminjaman->tgl_harus_kembali = $this->menunggu($statusBaru)
                ? null
                : $this->jatuhTempo($peminjaman->tgl_pinjam, (bool) $peminjaman->perpanjang);

            if ($statusBaru === self::STATUS_KEMBALI) {
                $tglKembali = Carbon::parse($data['tgl_kembali'] ?? now());
                $lamaPinjam = $this->hitungLamaPinjam($peminjaman->tgl_pinjam, $tglKembali);
                $peminjaman->tgl_kembali = $tglKembali->toDateString();
                $peminjaman->lama_pinjam = $lamaPinjam;
                $peminjaman->denda = $this->hitungDendaDariJatuhTempo($peminjaman->tgl_harus_kembali, $tglKembali);
            } else {
                $peminjaman->tgl_kembali = null;
                $peminjaman->lama_pinjam = null;
                $peminjaman->denda = 0;
            }

            $peminjaman->save();

            // Transaksi yang baru saja diubah admin menjadi booking tidak ikut dipromosikan
            if ($selisih < 0) {
                $this->prosesAntreanBooking($buku, kecualiId: $peminjaman->id);
            }

            return $peminjaman;
        });
    }

    /**
     * Stok baru saja bertambah: booking tertua (sebanyak stok yang ada) dinaikkan
     * menjadi pengajuan konfirmasi dengan tgl_pinjam hari ini, lalu anggota
     * diberi tahu lewat email agar datang ke loket. Dipanggil di dalam transaksi
     * dengan baris buku sudah terkunci.
     *
     * @return Collection<int, Peminjaman> booking yang dipromosikan
     */
    private function prosesAntreanBooking(Buku $buku, ?int $kecualiId = null): Collection
    {
        $stok = (int) $buku->fresh()->stok;

        if ($stok <= 0) {
            return new Collection;
        }

        $antrean = Peminjaman::with('anggota.user')
            ->where('buku_id', $buku->id)
            ->where('status', self::STATUS_BOOKING)
            ->when($kecualiId, fn ($query) => $query->whereKeyNot($kecualiId))
            ->oldest('id')
            ->limit($stok)
            ->get();

        foreach ($antrean as $booking) {
            $booking->status = self::STATUS_KONFIRMASI;
            $booking->tgl_pinjam = now()->toDateString();
            $booking->save();

            $this->beritahuAnggota($booking, new BukuTersedia($booking));
        }

        return $antrean;
    }

    /**
     * Status yang belum menyentuh stok (booking / konfirmasi).
     */
    public function menunggu(string $status): bool
    {
        return in_array($status, self::STATUS_MENUNGGU, true);
    }

    /**
     * Tanggal buku harus dikembalikan: tgl_pinjam + masa pinjam (atau masa perpanjang).
     */
    public function jatuhTempo(string|CarbonInterface $tglPinjam, bool $diperpanjang): string
    {
        return Carbon::parse($tglPinjam)
            ->addDays($diperpanjang ? $this->masaPerpanjang() : $this->masaPinjam())
            ->toDateString();
    }

    /**
     * Denda = hari keterlambatan setelah jatuh tempo x tarif.
     */
    public function hitungDendaDariJatuhTempo(string|CarbonInterface $tglHarusKembali, CarbonInterface $tglKembali): int
    {
        $terlambat = $this->hitungLamaPinjam($tglHarusKembali, $tglKembali);

        return $terlambat * $this->dendaPerHari();
    }

    public function hitungLamaPinjam(string|CarbonInterface $tglPinjam, CarbonInterface $tglKembali): int
    {
        $mulai = Carbon::parse($tglPinjam)->startOfDay();
        $selesai = Carbon::parse($tglKembali)->startOfDay();

        return max(0, (int) $mulai->diffInDays($selesai));
    }

    /**
     * Denda = hari keterlambatan x tarif; batas masa_pinjam, atau masa_perpanjang bila diperpanjang.
     */
    public function hitungDenda(int $lamaPinjam, bool $diperpanjang): int
    {
        $batas = $diperpanjang ? $this->masaPerpanjang() : $this->masaPinjam();

        return max(0, $lamaPinjam - $batas) * $this->dendaPerHari();
    }

    public function menahanStok(string $status): bool
    {
        return in_array($status, self::STATUS_MENAHAN_STOK, true);
    }

    /**
     * Notifikasi ke pemilik peminjaman; ditunda sampai transaksi DB commit.
     */
    private function beritahuAnggota(Peminjaman $peminjaman, NotifikasiPerpustakaan $notifikasi): void
    {
        $peminjaman->anggota?->user?->notify($notifikasi->afterCommit());
    }

    /**
     * Pengajuan/booking baru diumumkan ke semua petugas dan admin (in-app saja).
     */
    private function beritahuPetugas(Peminjaman $peminjaman): void
    {
        $penerima = User::whereIn('role', [Role::Petugas->value, Role::Admin->value])->get();

        Notification::send($penerima, (new PengajuanBaru($peminjaman))->afterCommit());
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
