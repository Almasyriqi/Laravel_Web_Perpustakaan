<?php

namespace App\Http\Controllers;

use App\Models\Anggota;
use App\Models\Buku;
use App\Models\Peminjaman;
use Illuminate\Http\Request;

class PeminjamanController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $pinjam = Peminjaman::join('anggota', 'peminjaman.anggota_id', '=', 'anggota.nim')->join('buku', 'peminjaman.buku_id', '=', 'buku.id')
            ->join('users', 'anggota.user_id', '=', 'users.id')->get(['peminjaman.*', 'anggota.*', 'users.name', 'buku.judul']);
        return view('admin.peminjaman.index', compact('pinjam'));
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
        return view('admin.peminjaman.create', ['anggota' => $anggota, 'buku' => $buku]);
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
            'tgl_pinjam' => 'required|date',
            'status' => 'required',
        ]);
        //TODO : Implementasikan Proses Simpan Ke Database
        $pinjam = new Peminjaman();
        $pinjam->anggota_id = $request->get('anggota');
        $pinjam->buku_id = $request->get('judul');
        $pinjam->jumlah = $request->get('jumlah');
        $pinjam->tgl_pinjam = $request->get('tgl_pinjam');
        $pinjam->status = $request->get('status');
        $pinjam->perpanjang = 0;
        $pinjam->save();

        //jika data berhasil ditambahkan, akan kembali ke halaman utama
        return redirect()->route('peminjaman.index')->with('success', 'Peminjaman Berhasil Ditambahkan');
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
        return view('admin.peminjaman.show', compact('pinjam'));
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function edit($id)
    {
        $anggota = Anggota::with('user')->get();
        $buku = Buku::all();
        $pinjam = Peminjaman::find($id);
        return view('admin.peminjaman.edit', ['anggota' => $anggota, 'buku' => $buku, 'pinjam' => $pinjam]);
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
        $request->validate([
            'anggota' => 'required', 
            'judul' => 'required',
            'jumlah' => 'required|integer',
            'tgl_pinjam' => 'required|date',
            'status' => 'required',
        ]);
        //TODO : Implementasikan Proses Simpan Ke Database
        $pinjam = Peminjaman::find($id);
        $status = $request->get('status');
        $pinjam->anggota_id = $request->get('anggota');
        $pinjam->buku_id = $request->get('judul');
        $pinjam->jumlah = $request->get('jumlah');
        $pinjam->tgl_pinjam = $request->get('tgl_pinjam');
        $pinjam->status = $status;
        $pinjam->perpanjang = $request->get('perpanjang');
        
        if($status == 'kembali'){
            $pinjam->tgl_kembali = $request->get('tgl_kembali');
            $pinjam->lama_pinjam = $request->get('lama_pinjam');
            $pinjam->denda = $request->get('denda');
        }
        $pinjam->save();

        //jika data berhasil diupdate, akan kembali ke halaman utama
        return redirect()->route('peminjaman.index')->with('success', 'Peminjaman Berhasil Diupdate');
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
        return redirect()->to('/admin/peminjaman')->with('success', 'Peminjaman Berhasil Dihapus');
    }

    public function delete($id)
    {
        $pinjam = Peminjaman::find($id);
        return view('admin.peminjaman.delete', compact('pinjam'));
    }
}
