<?php

namespace App\Http\Controllers;

use App\Models\Peminjaman;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;

class LaporanController extends Controller
{
    public const NAMA_BULAN = [
        1 => 'Januari', 'Februari', 'Maret', 'April', 'Mei', 'Juni',
        'Juli', 'Agustus', 'September', 'Oktober', 'November', 'Desember',
    ];

    /**
     * Daftar peminjaman pada bulan & tahun tertentu (tahun via ?tahun=, default tahun berjalan).
     */
    public function show(Request $request, string $bulan)
    {
        [$bulan, $tahun] = $this->periode($request, $bulan);

        $laporan = $this->queryLaporan($bulan, $tahun)
            ->join('buku', 'peminjaman.buku_id', '=', 'buku.id')
            ->get(['peminjaman.*', 'users.name', 'buku.judul']);

        return view('laporan.index', [
            'laporan' => $laporan,
            'sekarang' => $bulan,
            'tahun' => $tahun,
            'namaBulan' => self::NAMA_BULAN,
            'routePrefix' => $request->user()->role === 'admin' ? 'admin' : 'petugas',
        ]);
    }

    public function cetak_pdf(Request $request, string $bulan)
    {
        [$bulan, $tahun] = $this->periode($request, $bulan);

        $laporan = $this->queryLaporan($bulan, $tahun)
            ->with('buku')
            ->get(['peminjaman.*', 'users.name']);

        $pdf = Pdf::loadView('laporan.pdf', [
            'laporan' => $laporan,
            'namaBulan' => self::NAMA_BULAN[$bulan],
            'tahun' => $tahun,
        ]);

        return $pdf->stream("laporan-{$tahun}-{$bulan}.pdf");
    }

    /**
     * Validasi bulan (1-12) dari URL dan tahun dari query string.
     *
     * @return array{0: int, 1: int}
     */
    private function periode(Request $request, string $bulan): array
    {
        if (! ctype_digit($bulan) || (int) $bulan < 1 || (int) $bulan > 12) {
            throw ValidationException::withMessages(['bulan' => 'Bulan harus di antara 1 dan 12.']);
        }

        $tahun = $request->integer('tahun', now()->year);
        if ($tahun < 2000 || $tahun > 2100) {
            throw ValidationException::withMessages(['tahun' => 'Tahun tidak valid.']);
        }

        return [(int) $bulan, $tahun];
    }

    /**
     * Peminjaman pada bulan+tahun tersebut; whereYear mencegah bulan yang sama
     * di tahun berbeda ikut tercampur.
     */
    private function queryLaporan(int $bulan, int $tahun)
    {
        return Peminjaman::query()
            ->join('anggota', 'peminjaman.anggota_id', '=', 'anggota.nim')
            ->join('users', 'anggota.user_id', '=', 'users.id')
            ->whereMonth('peminjaman.tgl_pinjam', $bulan)
            ->whereYear('peminjaman.tgl_pinjam', $tahun)
            ->orderBy('peminjaman.tgl_pinjam')
            ->orderBy('peminjaman.id');
    }
}
