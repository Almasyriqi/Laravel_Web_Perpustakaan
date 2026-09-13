<?php

namespace App\Services;

use App\Models\Buku;
use App\Models\Peminjaman;
use App\Support\Bulan;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Carbon;

/**
 * Angka-angka untuk dashboard admin & petugas. Semua query ditulis agar
 * jalan di MySQL maupun SQLite (test), jadi agregasi per bulan dilakukan di PHP.
 */
class StatistikService
{
    /**
     * Semua data yang dibutuhkan partial `partials.statistik`.
     *
     * @return array{ringkasan: array<string, int>, tren: array{labels: list<string>, data: list<int>}, terpopuler: Collection<int, Buku>, keterlambatan: Collection<int, Peminjaman>}
     */
    public function dashboard(): array
    {
        return [
            'ringkasan' => $this->ringkasan(),
            'tren' => $this->trenBulanan(),
            'terpopuler' => $this->bukuTerpopuler(),
            'keterlambatan' => $this->daftarKeterlambatan(),
        ];
    }

    /**
     * @return array{sedang_dipinjam: int, menunggu_konfirmasi: int, terlambat: int, denda_bulan_ini: int}
     */
    public function ringkasan(): array
    {
        $awalBulan = now()->startOfMonth()->toDateString();
        $akhirBulan = now()->endOfMonth()->toDateString();

        return [
            'sedang_dipinjam' => Peminjaman::whereIn('status', PeminjamanService::STATUS_MENAHAN_STOK)->count(),
            'menunggu_konfirmasi' => Peminjaman::where('status', PeminjamanService::STATUS_KONFIRMASI)->count(),
            'terlambat' => Peminjaman::lewatTempo()->count(),
            'denda_bulan_ini' => (int) Peminjaman::where('status', PeminjamanService::STATUS_KEMBALI)
                ->whereBetween('tgl_kembali', [$awalBulan, $akhirBulan])
                ->sum('denda'),
        ];
    }

    /**
     * Buku dengan transaksi terkonfirmasi terbanyak dalam N bulan terakhir.
     *
     * @return Collection<int, Buku>
     */
    public function bukuTerpopuler(int $limit = 5, int $bulan = 12): Collection
    {
        $mulai = $this->awalPeriode($bulan);
        $terkonfirmasi = fn ($query) => $query
            ->whereIn('status', PeminjamanService::STATUS_TERKONFIRMASI)
            ->where('tgl_pinjam', '>=', $mulai);

        // whereHas (bukan HAVING) supaya buku tanpa transaksi tersaring juga di SQLite
        return Buku::whereHas('peminjaman', $terkonfirmasi)
            ->withCount(['peminjaman' => $terkonfirmasi])
            ->orderByDesc('peminjaman_count')
            ->orderBy('judul')
            ->limit($limit)
            ->get();
    }

    /**
     * Jumlah transaksi terkonfirmasi per bulan, N bulan terakhir termasuk bulan ini.
     * Bulan tanpa transaksi tetap muncul dengan nilai 0.
     *
     * @return array{labels: list<string>, data: list<int>}
     */
    public function trenBulanan(int $bulan = 12): array
    {
        $mulai = $this->awalPeriode($bulan);

        $perBulan = Peminjaman::whereIn('status', PeminjamanService::STATUS_TERKONFIRMASI)
            ->where('tgl_pinjam', '>=', $mulai)
            ->pluck('tgl_pinjam')
            ->countBy(fn (string $tgl) => substr($tgl, 0, 7));

        $labels = [];
        $data = [];

        for ($i = 0; $i < $bulan; $i++) {
            $periode = Carbon::parse($mulai)->addMonths($i);
            $labels[] = Bulan::singkat($periode->month).' '.$periode->year;
            $data[] = $perBulan[$periode->format('Y-m')] ?? 0;
        }

        return ['labels' => $labels, 'data' => $data];
    }

    /**
     * Peminjaman aktif yang sudah lewat jatuh tempo, yang paling lama di atas.
     *
     * @return Collection<int, Peminjaman>
     */
    public function daftarKeterlambatan(int $limit = 10): Collection
    {
        return Peminjaman::lewatTempo()
            ->with(['anggota.user', 'buku'])
            ->orderBy('tgl_harus_kembali')
            ->orderBy('id')
            ->limit($limit)
            ->get();
    }

    /**
     * Tanggal 1 dari (N-1) bulan sebelum bulan ini, sebagai string Y-m-d.
     */
    private function awalPeriode(int $bulan): string
    {
        return now()->startOfMonth()->subMonths($bulan - 1)->toDateString();
    }
}
