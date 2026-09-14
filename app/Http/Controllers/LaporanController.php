<?php

namespace App\Http\Controllers;

use App\Exports\LaporanExport;
use App\Models\Peminjaman;
use App\Support\Bulan;
use App\Support\PeriodeLaporan;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;
use Maatwebsite\Excel\Facades\Excel;

/**
 * Laporan peminjaman per bulan (/laporan/{bulan}?tahun=) atau rentang tanggal
 * bebas (/laporan/rentang?dari=&sampai=), dalam bentuk HTML, PDF, dan Excel.
 */
class LaporanController extends Controller
{
    /** Alias agar pemanggil lama tetap jalan; sumbernya kini App\Support\Bulan. */
    public const NAMA_BULAN = Bulan::NAMA;

    public function show(Request $request, ?string $bulan = null)
    {
        $periode = PeriodeLaporan::dariRequest($request, $bulan);
        $prefix = $request->user()->isAdmin() ? 'admin' : 'petugas';
        $akhiran = $periode->bulanan() ? '' : '.rentang';

        return view('laporan.index', [
            'laporan' => $this->queryLaporan($periode)->get(),
            'periode' => $periode,
            'sekarang' => $periode->bulan,
            'tahun' => $periode->tahun ?? now()->year,
            'namaBulan' => Bulan::NAMA,
            'routePrefix' => $prefix,
            'urlPdf' => route($prefix.'.cetak_pdf'.$akhiran, $periode->parameter()),
            'urlExcel' => route($prefix.'.excel'.$akhiran, $periode->parameter()),
        ]);
    }

    public function cetak_pdf(Request $request, ?string $bulan = null)
    {
        $periode = PeriodeLaporan::dariRequest($request, $bulan);

        $pdf = Pdf::loadView('laporan.pdf', [
            'laporan' => $this->queryLaporan($periode)->get(),
            'periode' => $periode,
        ]);

        return $pdf->stream("laporan-{$periode->slug()}.pdf");
    }

    public function excel(Request $request, ?string $bulan = null)
    {
        $periode = PeriodeLaporan::dariRequest($request, $bulan);

        return Excel::download(new LaporanExport($periode), "laporan-{$periode->slug()}.xlsx");
    }

    private function queryLaporan(PeriodeLaporan $periode)
    {
        return $periode->terapkan(Peminjaman::with(['anggota.user', 'buku']))
            ->orderBy('tgl_pinjam')
            ->orderBy('id');
    }
}
