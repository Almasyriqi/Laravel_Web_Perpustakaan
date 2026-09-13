<?php

namespace App\Http\Controllers;

use App\Models\Anggota;
use App\Models\Buku;
use App\Models\Peminjaman;
use App\Services\PeminjamanService;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class PeminjamanController extends Controller
{
    public function __construct(private readonly PeminjamanService $service) {}

    public function index()
    {
        $pinjam = Peminjaman::join('anggota', 'peminjaman.anggota_id', '=', 'anggota.nim')->join('buku', 'peminjaman.buku_id', '=', 'buku.id')
            ->join('users', 'anggota.user_id', '=', 'users.id')->get(['peminjaman.*', 'anggota.*', 'users.name', 'buku.judul']);

        return view('admin.peminjaman.index', compact('pinjam'));
    }

    public function create()
    {
        $anggota = Anggota::with('user')->get();
        $buku = Buku::where('stok', '>', 0)->get();

        return view('admin.peminjaman.create', ['anggota' => $anggota, 'buku' => $buku]);
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'anggota' => 'required|exists:anggota,nim',
            'judul' => 'required|exists:buku,id',
            'jumlah' => 'required|integer|min:1',
            'tgl_pinjam' => 'required|date',
            'status' => ['required', Rule::in(PeminjamanService::SEMUA_STATUS)],
        ]);

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
    public function update(Request $request, $id)
    {
        $pinjam = Peminjaman::findOrFail($id);

        $data = $request->validate([
            'jumlah' => 'required|integer|min:1',
            'tgl_pinjam' => 'required|date',
            'tgl_kembali' => 'nullable|date|after_or_equal:tgl_pinjam',
            'status' => ['required', Rule::in(PeminjamanService::SEMUA_STATUS)],
            'perpanjang' => 'nullable|boolean',
        ]);

        $this->service->ubah($pinjam, $data);

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
        return Peminjaman::with('buku')->join('anggota', 'peminjaman.anggota_id', '=', 'anggota.nim')
            ->join('users', 'anggota.user_id', '=', 'users.id')->where('peminjaman.id', '=', $id)
            ->select(['peminjaman.*', 'anggota.*', 'users.name'])->firstOrFail();
    }
}
