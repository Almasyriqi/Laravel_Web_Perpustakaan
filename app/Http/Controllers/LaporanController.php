<?php

namespace App\Http\Controllers;

use App\Models\Peminjaman;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Auth;

class LaporanController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return Response
     */
    public function index() {}

    /**
     * Show the form for creating a new resource.
     *
     * @return Response
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     *
     * @return Response
     */
    public function store(Request $request)
    {
        //
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return Response
     */
    public function show($id)
    {
        $laporan = Peminjaman::join('anggota', 'peminjaman.anggota_id', '=', 'anggota.nim')->join('buku', 'peminjaman.buku_id', '=', 'buku.id')
            ->join('users', 'anggota.user_id', '=', 'users.id')->whereMonth('peminjaman.tgl_pinjam', '=', $id)
            ->get(['peminjaman.*', 'anggota.*', 'users.name', 'buku.judul']);
        $sekarang = $id;

        return view('admin.laporan.index', compact('laporan', 'sekarang'));
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return Response
     */
    public function edit($id)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  int  $id
     * @return Response
     */
    public function update(Request $request, $id)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return Response
     */
    public function destroy($id)
    {
        //
    }

    public function cetak_pdf($id)
    {
        $laporan = Peminjaman::with('buku')->join('anggota', 'peminjaman.anggota_id', '=', 'anggota.nim')
            ->join('users', 'anggota.user_id', '=', 'users.id')->whereMonth('peminjaman.tgl_pinjam', '=', $id)
            ->get(['peminjaman.*', 'anggota.*', 'users.name']);
        $sekarang = (int) $id - 1;

        if (Auth::user()->role == 'admin') {
            $pdf = Pdf::loadView('admin.laporan.laporan_pdf', compact('laporan', 'sekarang'));

            return $pdf->stream();
        } else {
            $pdf = Pdf::loadView('petugas.laporan.laporan_pdf', compact('laporan', 'sekarang'));

            return $pdf->stream();
        }
    }
}
