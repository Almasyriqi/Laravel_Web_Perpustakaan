<?php

namespace App\Http\Controllers;

use App\Http\Requests\AjukanPeminjamanRequest;
use App\Models\Anggota;
use App\Models\Buku;
use App\Models\Peminjaman;
use App\Services\PeminjamanService;

class PeminjamanAnggotaController extends Controller
{
    public function __construct(private readonly PeminjamanService $service) {}

    /**
     * Riwayat peminjaman milik anggota yang sedang login.
     */
    public function index()
    {
        $anggota = $this->anggotaSaatIni();
        $pinjam = $anggota->peminjaman()->with('buku')->latest('id')->get();

        return view('anggota.peminjaman.index', compact('pinjam', 'anggota'));
    }

    public function show($id)
    {
        $pinjam = $this->peminjamanMilikSaya($id)->load(['anggota.user', 'buku']);

        return view('anggota.peminjaman.show', compact('pinjam'));
    }

    /**
     * Membatalkan pengajuan sendiri yang masih berstatus konfirmasi.
     */
    public function destroy($id)
    {
        $this->service->batalkan($this->peminjamanMilikSaya($id));

        return redirect()->to('/anggota/pinjam')->with('success', 'Peminjaman Berhasil Dibatalkan');
    }

    public function delete($id)
    {
        $pinjam = $this->peminjamanMilikSaya($id);

        return view('anggota.peminjaman.delete', compact('pinjam'));
    }

    public function pinjam($id)
    {
        $pinjam = Buku::findOrFail($id);

        return view('anggota.peminjaman.modalPinjam', compact('pinjam'));
    }

    /**
     * Mengajukan peminjaman dari katalog (menunggu konfirmasi petugas).
     */
    public function peminjaman(AjukanPeminjamanRequest $request, $id)
    {
        $buku = Buku::findOrFail($id);

        $this->service->ajukan($this->anggotaSaatIni(), $buku, (int) $request->validated('jumlah'));

        return redirect()->to('/anggota/buku')->with('success', 'Berhasil Meminjam Buku');
    }

    public function modalPerpanjang($id)
    {
        $pinjam = $this->peminjamanMilikSaya($id);

        return view('anggota.peminjaman.modalPerpanjang', compact('pinjam'));
    }

    public function perpanjang($id)
    {
        $this->service->perpanjang($this->peminjamanMilikSaya($id));

        return redirect()->to('/anggota/pinjam')->with('success', 'Perpanjang Peminjaman Berhasil!');
    }

    private function anggotaSaatIni(): Anggota
    {
        return Anggota::where('user_id', auth()->id())->firstOrFail();
    }

    /**
     * 404 bila peminjaman bukan milik anggota yang login, supaya anggota tidak
     * bisa membatalkan/memperpanjang transaksi orang lain lewat id di URL.
     */
    private function peminjamanMilikSaya($id): Peminjaman
    {
        return Peminjaman::where('anggota_id', $this->anggotaSaatIni()->nim)->findOrFail($id);
    }
}
