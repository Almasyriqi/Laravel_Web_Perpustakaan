<?php

namespace App\Http\Controllers;

use App\Http\Requests\BukuRequest;
use App\Models\Buku;
use App\Models\Kategori;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\ValidationException;

/**
 * Pengelolaan koleksi buku oleh admin dan petugas.
 */
class BukuController extends Controller
{
    public function index()
    {
        $paginate = Buku::with('kategori')->get();

        return view('admin.bukuAdmin.index', compact('paginate'));
    }

    public function create()
    {
        $kategori = Kategori::all();

        return view('admin.bukuAdmin.create', compact('kategori'));
    }

    public function store(BukuRequest $request)
    {
        $data = $request->validated();

        Buku::create([
            'kategori_id' => $data['kategori'],
            'judul' => $data['judul'],
            'penerbit' => $data['penerbit'],
            'penulis' => $data['penulis'],
            'keterangan' => $data['keterangan'],
            'stok' => $data['stok'],
            'gambar' => Buku::simpanSampul($request->file('gambar')),
        ]);

        return redirect()->to($this->prefix().'/buku')->with('success', 'Buku Berhasil Ditambahkan');
    }

    public function show($id)
    {
        $buku = Buku::with('kategori')->findOrFail($id);

        return view('admin.bukuAdmin.show', compact('buku'));
    }

    public function edit($id)
    {
        $buku = Buku::with('kategori')->findOrFail($id);
        $kategori = Kategori::all();

        return view('admin.bukuAdmin.edit', compact('buku', 'kategori'));
    }

    public function update(BukuRequest $request, $id)
    {
        $buku = Buku::findOrFail($id);
        $data = $request->validated();

        $buku->fill([
            'kategori_id' => $data['kategori'],
            'judul' => $data['judul'],
            'penerbit' => $data['penerbit'],
            'penulis' => $data['penulis'],
            'keterangan' => $data['keterangan'],
            'stok' => $data['stok'],
        ]);

        if ($request->hasFile('gambar')) {
            $buku->gantiSampul($request->file('gambar'));
        }

        $buku->save();

        return redirect()->to($this->prefix().'/buku')->with('success', 'Buku Berhasil DiUpdate');
    }

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
