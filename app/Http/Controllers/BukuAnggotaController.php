<?php

namespace App\Http\Controllers;

use App\Http\Requests\KatalogRequest;
use App\Models\Anggota;
use App\Models\Buku;
use App\Models\Kategori;

class BukuAnggotaController extends Controller
{
    /** Jumlah kartu buku per halaman katalog. */
    public const PER_HALAMAN = 12;

    /**
     * Katalog buku dengan pencarian (judul/penulis/penerbit), filter kategori,
     * dan opsi hanya menampilkan buku yang stoknya tersedia.
     */
    public function index(KatalogRequest $request)
    {
        $filter = $request->validated();

        $buku = Buku::with('kategori')
            ->denganRating()
            ->cari($filter['q'] ?? null)
            ->dariKategori($filter['kategori'] ?? null)
            ->when($request->boolean('tersedia'), fn ($query) => $query->tersedia())
            ->orderBy('judul')
            ->paginate(self::PER_HALAMAN)
            ->withQueryString();

        return view('anggota.bukuAnggota.index', [
            'buku' => $buku,
            'kategori' => Kategori::orderBy('nama')->get(),
            'filter' => [
                'q' => $filter['q'] ?? '',
                'kategori' => (int) ($filter['kategori'] ?? 0),
                'tersedia' => $request->boolean('tersedia'),
            ],
        ]);
    }

    /**
     * Detail buku beserta rating, daftar ulasan, dan form ulasan bila anggota berhak.
     */
    public function show($id)
    {
        $buku = Buku::with('kategori')->denganRating()->findOrFail($id);
        $anggota = Anggota::where('user_id', auth()->id())->firstOrFail();

        return view('anggota.bukuAnggota.show', [
            'buku' => $buku,
            'ulasan' => $buku->ulasan()->with('anggota.user')->latest('updated_at')->get(),
            'bolehMengulas' => $anggota->bolehMengulas($buku),
            'ulasanSaya' => $anggota->ulasanUntuk($buku),
        ]);
    }
}
