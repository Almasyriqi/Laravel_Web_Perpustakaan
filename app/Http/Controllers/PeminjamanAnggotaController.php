<?php

namespace App\Http\Controllers;

use App\Models\Anggota;
use App\Models\Buku;
use App\Models\Peminjaman;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class PeminjamanAnggotaController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $anggota = Anggota::with('user')->where('user_id', Auth::user()->id)->first();
        $pinjam = Peminjaman::join('anggota', 'peminjaman.anggota_id', '=', 'anggota.nim')->join('buku', 'peminjaman.buku_id', '=', 'buku.id')
            ->where('peminjaman.anggota_id', '=', $anggota->nim)->orderBy('peminjaman.id', 'desc')
            ->get(['peminjaman.*', 'anggota.*', 'buku.judul']);
        return view('anggota.peminjaman.index', compact('pinjam', 'anggota'));
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        $pinjam = Peminjaman::with('buku')->join('anggota', 'peminjaman.anggota_id', '=', 'anggota.nim')
            ->join('users', 'anggota.user_id', '=', 'users.id')->where('peminjaman.id', '=', $id)
            ->select(['peminjaman.*', 'anggota.*', 'users.name'])->first();
        return view('anggota.peminjaman.show', compact('pinjam'));
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function edit($id)
    {
        //
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
        
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        $peminjaman = Peminjaman::find($id);
        $peminjaman->delete();
        return redirect()->to('/anggota/pinjam')->with('success', 'Peminjaman Berhasil Dibatalkan');
    }

    public function delete($id)
    {
        $pinjam = Peminjaman::find($id);
        return view('anggota.peminjaman.delete', compact('pinjam'));
    }

    public function pinjam($id)
    {
        $pinjam = Buku::find($id);
        return view('anggota.peminjaman.modalPinjam', compact('pinjam'));
    }

    public function peminjaman(Request $request, $id)
    {
        $buku = Buku::find($id);
        $user_id = Auth::user()->id;
        $anggota = Anggota::getByUser($user_id);

        $request->validate([
            'jumlah' => 'required|integer|max:'.$buku->stok,
        ]);
        $pinjam = new Peminjaman();
        $pinjam->anggota_id = $anggota->nim;
        $pinjam->buku_id = $buku->id;
        $pinjam->jumlah = $request->get('jumlah');
        $pinjam->tgl_pinjam = now();
        $pinjam->status = 'konfirmasi';
        $pinjam->denda = 0;
        $pinjam->perpanjang = 0;
        $pinjam->save();
        
        return redirect()->to('/anggota/buku')->with('success', 'Berhasil Meminjam Buku');
    }

    public function modalPerpanjang($id)
    {
        $pinjam = Peminjaman::find($id);
        return view('anggota.peminjaman.modalPerpanjang', compact('pinjam'));
    }

    public function perpanjang($id)
    {
        $pinjam = Peminjaman::find($id);
        $pinjam->status = 'perpanjang';
        $pinjam->perpanjang = 1;
        $pinjam->save();
        return redirect()->to('/anggota/pinjam')->with('success', 'Perpanjang Peminjaman Berhasil!');
    }
}
