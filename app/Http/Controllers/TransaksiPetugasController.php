<?php

namespace App\Http\Controllers;

use App\Http\Requests\TransaksiRequest;
use App\Models\Anggota;
use App\Models\Buku;
use App\Models\Peminjaman;
use App\Services\PeminjamanService;

class TransaksiPetugasController extends Controller
{
    public function __construct(private readonly PeminjamanService $service) {}

    public function index()
    {
        // Satu baris per anggota yang punya transaksi aktif, terbaru di atas.
        // (DISTINCT + ORDER BY kolom di luar SELECT ditolak MySQL 8 mode strict)
        $pinjam = Peminjaman::join('anggota', 'peminjaman.anggota_id', '=', 'anggota.nim')
            ->join('users', 'anggota.user_id', '=', 'users.id')
            ->where('peminjaman.status', '!=', PeminjamanService::STATUS_KONFIRMASI)
            ->selectRaw('anggota.*, users.name, users.email, MAX(peminjaman.id) as last_id')
            ->groupBy('anggota.nim', 'users.name', 'users.email')
            ->orderByDesc('last_id')
            ->get();

        return view('petugas.peminjaman.index', compact('pinjam'));
    }

    public function create()
    {
        $anggota = Anggota::with('user')->get();
        $buku = Buku::all();

        return view('petugas.peminjaman.create', ['anggota' => $anggota, 'buku' => $buku]);
    }

    /**
     * Peminjaman langsung di loket: stok langsung berkurang.
     */
    public function store(TransaksiRequest $request)
    {
        $data = $request->validated();

        $this->service->pinjamLangsung(
            anggotaId: (int) $data['anggota'],
            bukuId: (int) $data['judul'],
            jumlah: (int) $data['jumlah'],
        );

        return redirect()->route('transaksi.index')->with('success', 'Peminjaman Berhasil Ditambahkan');
    }

    public function show($id)
    {
        $pinjam = Peminjaman::with('buku')->join('anggota', 'peminjaman.anggota_id', '=', 'anggota.nim')
            ->join('users', 'anggota.user_id', '=', 'users.id')->where('peminjaman.id', '=', $id)
            ->select(['peminjaman.*', 'anggota.*', 'users.name'])->firstOrFail();

        return view('petugas.peminjaman.show', compact('pinjam'));
    }

    /**
     * Daftar transaksi aktif seorang anggota ($id = nim).
     */
    public function edit($id)
    {
        $pinjam = Peminjaman::join('anggota', 'peminjaman.anggota_id', '=', 'anggota.nim')->join('buku', 'peminjaman.buku_id', '=', 'buku.id')
            ->where('peminjaman.anggota_id', '=', $id)->where('peminjaman.status', '!=', PeminjamanService::STATUS_KONFIRMASI)
            ->get(['peminjaman.*', 'anggota.*', 'buku.judul']);
        $anggota = Anggota::with('user')->where('nim', $id)->firstOrFail();

        return view('petugas.peminjaman.edit', compact('pinjam', 'anggota'));
    }

    /**
     * Pengembalian buku: lama pinjam & denda dihitung, stok kembali.
     */
    public function update($id)
    {
        $kembali = $this->service->kembalikan(Peminjaman::findOrFail($id));
        $tujuan = '/petugas/transaksi/'.$kembali->anggota_id.'/edit';

        if ($kembali->denda > 0) {
            return redirect()->to($tujuan)->with('success', 'Berhasil Mengembalikan Buku, '
                .'mendapatkan denda sebesar Rp '.number_format($kembali->denda, 0, ',', '.')
                .'. Silahkan langsung membayar denda ke petugas!');
        }

        return redirect()->to($tujuan)->with('success', 'Berhasil Mengembalikan Buku, Terima kasih telah mengembalikan tepat waktu');
    }

    /**
     * Menolak/membatalkan pengajuan yang belum dikonfirmasi.
     */
    public function destroy($id)
    {
        $this->service->batalkan(Peminjaman::findOrFail($id));

        return redirect()->to('/petugas/transaksi/konfirmasi')->with('success', 'Peminjaman Berhasil Dibatalkan');
    }

    public function delete($id)
    {
        $pinjam = Peminjaman::findOrFail($id);

        return view('petugas.peminjaman.delete', compact('pinjam'));
    }

    public function konfirmasiPeminjaman()
    {
        $pinjam = Peminjaman::join('anggota', 'peminjaman.anggota_id', '=', 'anggota.nim')->join('buku', 'peminjaman.buku_id', '=', 'buku.id')
            ->join('users', 'anggota.user_id', '=', 'users.id')->where('peminjaman.status', '=', PeminjamanService::STATUS_KONFIRMASI)
            ->get(['peminjaman.*', 'anggota.*', 'users.name', 'buku.judul']);

        return view('petugas.peminjaman.confirm', compact('pinjam'));
    }

    public function confirm($id)
    {
        $pinjam = Peminjaman::findOrFail($id);

        return view('petugas.peminjaman.modalConfirm', compact('pinjam'));
    }

    public function konfirmasi($id)
    {
        $this->service->konfirmasi(Peminjaman::findOrFail($id));

        return redirect()->to('/petugas/transaksi/konfirmasi')->with('success', 'Konfirmasi Peminjaman Berhasil!');
    }

    public function modalPerpanjang($id)
    {
        $pinjam = Peminjaman::findOrFail($id);

        return view('petugas.peminjaman.modalPerpanjang', compact('pinjam'));
    }

    public function perpanjang($id)
    {
        $pinjam = $this->service->perpanjang(Peminjaman::findOrFail($id));

        return redirect()->to('/petugas/transaksi/'.$pinjam->anggota_id.'/edit')->with('success', 'Perpanjang Peminjaman Berhasil!');
    }

    public function kembali($id)
    {
        $pinjam = Peminjaman::findOrFail($id);

        return view('petugas.peminjaman.modalKembali', compact('pinjam'));
    }
}
