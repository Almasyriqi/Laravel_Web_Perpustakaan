<?php

namespace App\Http\Controllers;

use App\Http\Requests\BukuRequest;
use App\Models\Buku;
use App\Models\Kategori;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\File;
use Illuminate\Validation\ValidationException;

class BukuController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return Response
     */
    public function index()
    {
        $paginate = Buku::with('kategori')->get();

        return view('admin.bukuAdmin.index', compact('paginate'));
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return Response
     */
    public function create()
    {
        $kategori = Kategori::all();

        return view('admin.bukuAdmin.create', compact('kategori'));
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  Request  $request
     * @return Response
     */
    public function store(BukuRequest $request)
    {
        // TODO : Implementasikan Proses Simpan Ke Database
        $buku = new Buku;
        $buku->kategori_id = $request->get('kategori');
        $buku->judul = $request->get('judul');
        $buku->penerbit = $request->get('penerbit');
        $buku->penulis = $request->get('penulis');
        $buku->keterangan = $request->get('keterangan');
        $buku->stok = $request->get('stok');
        $file = $request->file('gambar');
        $image_name = '/images/'.$file->getClientOriginalName();

        // isi dengan nama folder tempat kemana file diupload
        $tujuan_upload = 'images';
        $file->move($tujuan_upload, $image_name);
        $buku->gambar = $image_name;
        $buku->save();

        // jika data berhasil ditambahkan, akan kembali ke halaman utama
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
     * @return Response
     */
    public function show($id)
    {
        $buku = Buku::with('kategori')->findOrFail($id);

        return view('admin.bukuAdmin.show', compact('buku'));
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return Response
     */
    public function edit($id)
    {
        $buku = Buku::with('kategori')->findOrFail($id);
        $kategori = Kategori::all();

        return view('admin.bukuAdmin.edit', compact('buku', 'kategori'));
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  Request  $request
     * @param  int  $id
     * @return Response
     */
    public function update(BukuRequest $request, $id)
    {
        $buku = Buku::findOrFail($id);
        $buku->kategori_id = $request->get('kategori');
        $buku->judul = $request->get('judul');
        $buku->penerbit = $request->get('penerbit');
        $buku->penulis = $request->get('penulis');
        $buku->keterangan = $request->get('keterangan');
        $buku->stok = $request->get('stok');
        $buku->save();
        if ($request->file('gambar') != null) {
            File::delete('/images/'.$buku->gambar);
            $file = $request->file('gambar');
            $image_name = '/images/'.$file->getClientOriginalName();
            $tujuan_upload = 'images';
            $file->move($tujuan_upload, $image_name);
            $buku->gambar = $image_name;
            $buku->save();
        }

        // jika data berhasil ditambahkan, akan kembali ke halaman utama
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
     * @return Response
     */
    /**
     * Soft delete: buku masuk arsip, riwayat peminjaman tetap utuh, file sampul
     * tidak dihapus supaya masih bisa ditampilkan dari riwayat.
     */
    public function destroy($id)
    {
        $buku = Buku::findOrFail($id);

        if ($buku->sedangDipinjam()) {
            throw ValidationException::withMessages([
                'buku' => "Buku \"{$buku->judul}\" masih dipinjam anggota dan belum bisa dihapus.",
            ]);
        }

        $buku->delete();

        return redirect()->to($this->prefix().'/buku')->with('success', 'Buku dipindahkan ke arsip');
    }

    public function delete($id)
    {
        $buku = Buku::findOrFail($id);

        return view('admin.bukuAdmin.delete', compact('buku'));
    }

    public function arsip()
    {
        $paginate = Buku::onlyTrashed()->with('kategori')->latest('deleted_at')->get();

        return view('admin.bukuAdmin.arsip', compact('paginate'));
    }

    public function pulihkan($id)
    {
        $buku = Buku::onlyTrashed()->findOrFail($id);
        $buku->restore();

        return redirect()->to($this->prefix().'/buku')->with('success', "Buku \"{$buku->judul}\" dipulihkan");
    }

    /**
     * Buku dikelola dari panel admin maupun petugas; kembali ke panel yang sesuai.
     */
    private function prefix(): string
    {
        return Auth::user()->role === 'admin' ? '/admin' : '/petugas';
    }
}
