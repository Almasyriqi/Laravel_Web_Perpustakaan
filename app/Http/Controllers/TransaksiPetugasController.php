<?php

namespace App\Http\Controllers;

use App\Models\Anggota;
use App\Models\Buku;
use App\Models\Peminjaman;
use App\Models\User;
use Illuminate\Http\Request;

class TransaksiPetugasController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $pinjam = Peminjaman::join('anggota', 'peminjaman.anggota_id', '=', 'anggota.nim')
        ->join('users', 'anggota.user_id', '=', 'users.id')->orderBy('peminjaman.id', 'desc')
        ->distinct()->get(['anggota.*', 'users.name', 'users.email']);
        return view('petugas.peminjaman.index', compact('pinjam'));
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        $anggota = Anggota::with('user')->get();
        $buku = Buku::all();
        return view('petugas.peminjaman.create', ['anggota' => $anggota, 'buku' => $buku]);
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        $request->validate([
            'anggota' => 'required', 
            'judul' => 'required',
            'jumlah' => 'required|integer',
        ]);
        //TODO : Implementasikan Proses Simpan Ke Database
        $pinjam = new Peminjaman();
        $pinjam->anggota_id = $request->get('anggota');
        $pinjam->buku_id = $request->get('judul');
        $pinjam->jumlah = $request->get('jumlah');
        $pinjam->tgl_pinjam = now();
        $pinjam->status ='dipinjam';
        $pinjam->denda = 0;
        $pinjam->perpanjang = 0;
        $pinjam->save();

        //jika data berhasil ditambahkan, akan kembali ke halaman utama
        return redirect()->route('transaksi.index')->with('success', 'Peminjaman Berhasil Ditambahkan');
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function edit($id)
    {
        $pinjam = Peminjaman::join('anggota', 'peminjaman.anggota_id', '=', 'anggota.nim')->join('buku', 'peminjaman.buku_id', '=', 'buku.id')
            ->where('peminjaman.anggota_id', '=', $id)
            ->get(['peminjaman.*', 'anggota.*', 'buku.judul']);
        $anggota = Anggota::with('user')->where('nim', $id)->first();
        return view('petugas.peminjaman.edit', compact('pinjam', 'anggota'));
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        //
    }

    public function konfirmasi()
    {
        # code...
    }
}
