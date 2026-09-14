<?php

namespace App\Http\Controllers;

use App\Http\Requests\BukuRequest;
use App\Models\Buku;
use App\Models\Kategori;
use App\Support\KodeQr;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\ValidationException;

/**
 * Pengelolaan koleksi buku oleh admin dan petugas.
 */
class BukuController extends Controller
{
    /**
     * Daftar buku dengan pencarian server-side (?q= judul/penulis/penerbit, ?kategori=) dan paginasi.
     */
    public function index(Request $request)
    {
        $paginate = Buku::with('kategori')
            ->cari($request->query('q'))
            ->dariKategori($request->integer('kategori') ?: null)
            ->orderBy('judul')
            ->paginate(AdminController::PER_HALAMAN)
            ->withQueryString();

        return view('admin.bukuAdmin.index', [
            'paginate' => $paginate,
            'kategori' => Kategori::orderBy('nama')->get(),
            'q' => (string) $request->query('q'),
            'kategoriDipilih' => $request->integer('kategori'),
        ]);
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
        $buku = Buku::with('kategori')->denganRating()->findOrFail($id);
        $ulasan = $buku->ulasan()->with('anggota.user')->latest('updated_at')->get();
        $kode = KodeQr::buku($buku);

        return view('admin.bukuAdmin.show', [
            'buku' => $buku,
            'ulasan' => $ulasan,
            'kode' => $kode,
            'qr' => KodeQr::svg($kode, 4),
        ]);
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

    public function arsip(Request $request)
    {
        $paginate = Buku::onlyTrashed()->with('kategori')
            ->cari($request->query('q'))
            ->latest('deleted_at')
            ->paginate(AdminController::PER_HALAMAN)
            ->withQueryString();

        return view('admin.bukuAdmin.arsip', ['paginate' => $paginate, 'q' => (string) $request->query('q')]);
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
        return Auth::user()->isAdmin() ? '/admin' : '/petugas';
    }
}
