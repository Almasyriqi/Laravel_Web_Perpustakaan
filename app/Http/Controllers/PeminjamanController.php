<?php

namespace App\Http\Controllers;

use App\Http\Requests\PeminjamanRequest;
use App\Models\Anggota;
use App\Models\Buku;
use App\Models\Peminjaman;
use App\Services\PeminjamanService;

class PeminjamanController extends Controller
{
    public function __construct(private readonly PeminjamanService $service) {}

    public function index()
    {
        $pinjam = Peminjaman::with(['anggota.user', 'buku'])->latest('id')->get();

        return view('admin.peminjaman.index', compact('pinjam'));
    }

    public function create()
    {
        $anggota = Anggota::with('user')->get();
        $buku = Buku::where('stok', '>', 0)->get();

        return view('admin.peminjaman.create', ['anggota' => $anggota, 'buku' => $buku]);
    }

    public function store(PeminjamanRequest $request)
    {
        $data = $request->validated();

        $this->service->pinjamLangsung(
            anggotaId: (int) $data['anggota'],
            bukuId: (int) $data['judul'],
            jumlah: (int) $data['jumlah'],
            tglPinjam: $data['tgl_pinjam'],
            status: $data['status'],
        );

        return redirect()->route('peminjaman.index')->with('success', 'Peminjaman Berhasil Ditambahkan');
    }

    public function show($id)
    {
        $pinjam = $this->detail($id);

        return view('admin.peminjaman.show', compact('pinjam'));
    }

    public function edit($id)
    {
        $pinjam = $this->detail($id);

        return view('admin.peminjaman.edit', ['pinjam' => $pinjam]);
    }

    /**
     * Edit bebas oleh admin; stok disinkronkan oleh service dari perubahan
     * status/jumlah, lama pinjam & denda dihitung ulang bila status kembali.
     */
    public function update(PeminjamanRequest $request, $id)
    {
        $this->service->ubah(Peminjaman::findOrFail($id), $request->validated());

        return redirect()->route('peminjaman.index')->with('success', 'Peminjaman Berhasil Diupdate');
    }

    public function destroy($id)
    {
        $this->service->hapus(Peminjaman::findOrFail($id));

        return redirect()->to('/admin/peminjaman')->with('success', 'Peminjaman Berhasil Dihapus');
    }

    public function delete($id)
    {
        $pinjam = Peminjaman::findOrFail($id);

        return view('admin.peminjaman.delete', compact('pinjam'));
    }

    private function detail($id): Peminjaman
    {
        return Peminjaman::with(['anggota.user', 'buku'])->findOrFail($id);
    }
}
