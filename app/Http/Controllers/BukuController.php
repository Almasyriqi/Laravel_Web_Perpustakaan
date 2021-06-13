<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Buku;
use App\Models\Kategori;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\File;

class BukuController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $paginate = Buku::with('kategori')->get();
        return view('admin.bukuAdmin.index', compact('paginate'));
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        $kategori = Kategori::all();
        return view('admin.bukuAdmin.create', compact('kategori'));
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
            'kategori' => 'required',
            'judul' => 'required',
            'penerbit' => 'required',
            'penulis' => 'required',
            'keterangan' => 'required',
            'stok' => 'required|integer',
            'gambar' => 'required|file|image|mimes:jpeg,png,jpg',
        ]);
        //TODO : Implementasikan Proses Simpan Ke Database
        $buku = new Buku();
        $buku->kategori_id = $request->get('kategori');
        $buku->judul = $request->get('judul');
        $buku->penerbit = $request->get('penerbit');
        $buku->penulis = $request->get('penulis');
        $buku->keterangan = $request->get('keterangan');
        $buku->stok = $request->get('stok');
        $file = $request->file('gambar');
        $image_name = '/images/' . $file->getClientOriginalName();

        // isi dengan nama folder tempat kemana file diupload
        $tujuan_upload = 'images';
        $file->move($tujuan_upload, $image_name);
        $buku->gambar = $image_name;
        $buku->save();

        //jika data berhasil ditambahkan, akan kembali ke halaman utama
        if (Auth::user()->role == 'admin') {
            return redirect()->to('/admin/buku')->with('success', 'Buku Berhasil Ditambahkan');
        } else {
            return redirect()->to('/petugas/buku')->with('success', 'Buku Berhasil Ditambahkan');
        }
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        $buku = Buku::with('kategori')->where('id', $id)->first();
        return view('admin.bukuAdmin.show', compact('buku'));
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function edit($id)
    {
        $buku = Buku::with('kategori')->where('id', $id)->first();
        $kategori = Kategori::all();
        return view('admin.bukuAdmin.edit', compact('buku', 'kategori'));
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
            'kategori' => 'required',
            'judul' => 'required',
            'penerbit' => 'required',
            'penulis' => 'required',
            'keterangan' => 'required',
            'stok' => 'required|integer',
        ]);
        //TODO : Implementasikan Proses Simpan Ke Database
        $buku = Buku::find($id);
        $buku->kategori_id = $request->get('kategori');
        $buku->judul = $request->get('judul');
        $buku->penerbit = $request->get('penerbit');
        $buku->penulis = $request->get('penulis');
        $buku->keterangan = $request->get('keterangan');
        $buku->stok = $request->get('stok');
        $buku->save();
        if ($request->file('gambar') != null) {
            File::delete('/images/' . $buku->gambar);
            $file = $request->file('gambar');
            $image_name = '/images/'.$file->getClientOriginalName();
            $tujuan_upload = 'images';
            $file->move($tujuan_upload, $image_name);
            $buku->gambar = $image_name;
            $buku->save();
        }

        //jika data berhasil ditambahkan, akan kembali ke halaman utama
        if (Auth::user()->role == 'admin') {
            return redirect()->to('/admin/buku')->with('success', 'Buku Berhasil DiUpdate');
        } else {
            return redirect()->to('/petugas/buku')->with('success', 'Buku Berhasil DiUpdate');
        }
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {

        $buku = Buku::find($id);
        File::delete('/images/' . $buku->image);
        $buku->delete();

        if (Auth::user()->role == 'admin') {
            return redirect()->to('/admin/buku')->with('success', 'Buku Berhasil DiHapus');
        } else {
            return redirect()->to('/petugas/buku')->with('success', 'Buku Berhasil DiHapus');
        }
    }

    public function delete($id)
    {
        $buku = Buku::find($id);
        return view('admin.bukuAdmin.delete', compact('buku'));
    }
}
