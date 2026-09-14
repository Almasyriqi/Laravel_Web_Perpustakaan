<?php

namespace App\Http\Controllers;

use App\Http\Requests\TransaksiRequest;
use App\Models\Anggota;
use App\Models\Buku;
use App\Models\Peminjaman;
use App\Services\PeminjamanService;
use Illuminate\Http\Request;

class TransaksiPetugasController extends Controller
{
    public function __construct(private readonly PeminjamanService $service) {}

    /**
     * Satu baris per anggota yang punya transaksi aktif, yang terbaru di atas;
     * bisa dicari (?q= nama/NIM) dan dipaginasi.
     */
    public function index(Request $request)
    {
        $aktif = fn ($q) => $q->whereNotIn('status', PeminjamanService::STATUS_MENUNGGU);
        $pinjam = Anggota::with('user')
            ->whereHas('peminjaman', $aktif)
            ->cari($request->query('q'))
            ->withMax(['peminjaman as last_id' => $aktif], 'id')
            ->orderByDesc('last_id')
            ->paginate(AdminController::PER_HALAMAN)
            ->withQueryString();

        return view('petugas.peminjaman.index', ['pinjam' => $pinjam, 'q' => (string) $request->query('q')]);
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
        $pinjam = Peminjaman::with(['anggota.user', 'buku'])->findOrFail($id);

        return view('petugas.peminjaman.show', compact('pinjam'));
    }

    /**
     * Daftar transaksi aktif seorang anggota ($id = nim).
     */
    public function edit($id)
    {
        $anggota = Anggota::with('user')->findOrFail($id);
        $pinjam = $anggota->peminjaman()->with('buku')
            ->where('status', '!=', PeminjamanService::STATUS_KONFIRMASI)
            ->latest('id')
            ->get();

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

    /**
     * Pengajuan yang menunggu persetujuan + antrean booking (menunggu stok).
     */
    public function konfirmasiPeminjaman()
    {
        $menunggu = Peminjaman::with(['anggota.user', 'buku'])
            ->whereIn('status', PeminjamanService::STATUS_MENUNGGU)
            ->oldest('id')
            ->get();

        return view('petugas.peminjaman.confirm', [
            'pinjam' => $menunggu->where('status', PeminjamanService::STATUS_KONFIRMASI),
            'booking' => $menunggu->where('status', PeminjamanService::STATUS_BOOKING),
        ]);
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
