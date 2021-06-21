<?php

namespace App\Http\Controllers;

use App\Models\Anggota;
use App\Models\Buku;
use App\Models\Peminjaman;
use App\Models\User;
use DateTime;
use Illuminate\Http\Request;

class TransaksiPetugasController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $pinjam = Peminjaman::join('anggota', 'peminjaman.anggota_id', '=', 'anggota.nim')
            ->join('users', 'anggota.user_id', '=', 'users.id')->where('peminjaman.status', '!=', 'konfirmasi')
            ->orderBy('peminjaman.id', 'desc')->distinct()->get(['anggota.*', 'users.name', 'users.email']);
        return view('petugas.peminjaman.index', compact('pinjam'));
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        $anggota = Anggota::with('user')->get();
        $buku = Buku::all();
        return view('petugas.peminjaman.create', ['anggota' => $anggota, 'buku' => $buku]);
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        //TODO : Implementasikan Proses Simpan Ke Database
        $pinjam = new Peminjaman();
        $pinjam->anggota_id = $request->get('anggota');
        $buku_id = $request->get('judul');
        $pinjam->buku_id = $buku_id;
        $jumlah = $request->get('jumlah');
        $pinjam->jumlah = $jumlah;
        $pinjam->tgl_pinjam = now();
        $pinjam->status = 'dipinjam';
        $pinjam->denda = 0;
        $pinjam->perpanjang = 0;

        $buku = Buku::find($buku_id);
        $request->validate([
            'anggota' => 'required',
            'judul' => 'required',
            'jumlah' => 'required|integer|max:'.$buku->stok,
        ]);
        $buku->stok -= $jumlah;
        $buku->save();
        $pinjam->save();

        //jika data berhasil ditambahkan, akan kembali ke halaman utama
        return redirect()->route('transaksi.index')->with('success', 'Peminjaman Berhasil Ditambahkan');
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        $pinjam = Peminjaman::with('buku')->join('anggota', 'peminjaman.anggota_id', '=', 'anggota.nim')
            ->join('users', 'anggota.user_id', '=', 'users.id')->where('peminjaman.id', '=', $id)
            ->select(['peminjaman.*', 'anggota.*', 'users.name'])->first();
        return view('petugas.peminjaman.show', compact('pinjam'));
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function edit($id)
    {
        $pinjam = Peminjaman::join('anggota', 'peminjaman.anggota_id', '=', 'anggota.nim')->join('buku', 'peminjaman.buku_id', '=', 'buku.id')
            ->where('peminjaman.anggota_id', '=', $id)->where('peminjaman.status', '!=', 'konfirmasi')
            ->get(['peminjaman.*', 'anggota.*', 'buku.judul']);
        $anggota = Anggota::with('user')->where('nim', $id)->first();
        return view('petugas.peminjaman.edit', compact('pinjam', 'anggota'));
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update($id)
    {
        $kembali = Peminjaman::find($id);
        $jumlah = $kembali->jumlah;
        $buku_id = $kembali->buku_id;
        $nim = $kembali->anggota_id;
        $kembali->status = 'kembali';
        $tgl_pinjam = $kembali->tgl_pinjam;
        $tgl_kembali = now();
        $kembali->tgl_kembali = $tgl_kembali;

        // Menghitung lama pinjam
        $tgl1 = new DateTime($tgl_pinjam);
        $tgl2 = new DateTime($tgl_kembali);
        $d = $tgl2->diff($tgl1)->days;
        $kembali->lama_pinjam = $d;

        // Menghitung denda
        $lama_pinjam = $d;
        $perpanjang = $kembali->perpanjang;
        $denda = 0;
        if($perpanjang == 1){
            if($lama_pinjam > 14){
                $lama_pinjam -= 14;
                $denda = $lama_pinjam * 2000;
                $kembali->denda = $denda;
            }
        }
        else {
            if($lama_pinjam > 7){
                $lama_pinjam -= 7;
                $denda = $lama_pinjam * 2000;
                $kembali->denda = $denda;
            }
        }

        $buku = Buku::find($buku_id);
        $buku->stok += $jumlah;
        $buku->save();
        $kembali->save();

        if($denda > 0){
            return redirect()->to('/petugas/transaksi/' . $nim . '/edit')->with('success', 'Berhasil Mengembalikan Buku, 
            mendapatkan denda sebesar Rp '. $denda . ' Silahkan langsung membayar denda ke petugas!');
        }
        else {
            return redirect()->to('/petugas/transaksi/' . $nim . '/edit')->with('success', 'Berhasil Mengembalikan Buku, Terima kasih telah mengembalikan tepat waktu');
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
        $peminjaman = Peminjaman::find($id);
        $peminjaman->delete();
        return redirect()->to('/petugas/transaksi/konfirmasi')->with('success', 'Peminjaman Berhasil Dibatalkan');
    }

    public function delete($id)
    {
        $pinjam = Peminjaman::find($id);
        return view('petugas.peminjaman.delete', compact('pinjam'));
    }

    public function konfirmasiPeminjaman()
    {
        $pinjam = Peminjaman::join('anggota', 'peminjaman.anggota_id', '=', 'anggota.nim')->join('buku', 'peminjaman.buku_id', '=', 'buku.id')
            ->join('users', 'anggota.user_id', '=', 'users.id')->where('peminjaman.status', '=', 'konfirmasi')
            ->get(['peminjaman.*', 'anggota.*', 'users.name', 'buku.judul']);
        return view('petugas.peminjaman.confirm', compact('pinjam'));
    }

    public function confirm($id)
    {
        $pinjam = Peminjaman::find($id);
        return view('petugas.peminjaman.modalConfirm', compact('pinjam'));
    }

    public function konfirmasi($id)
    {
        $pinjam = Peminjaman::find($id);
        $pinjam->status = 'dipinjam';
        $buku_id = $pinjam->buku_id;
        $buku = Buku::find($buku_id);
        $buku->stok -= $pinjam->jumlah;
        $buku->save();
        $pinjam->save();
        return redirect()->to('/petugas/transaksi/konfirmasi')->with('success', 'Konfirmasi Peminjaman Berhasil!');
    }

    public function modalPerpanjang($id)
    {
        $pinjam = Peminjaman::find($id);
        return view('petugas.peminjaman.modalPerpanjang', compact('pinjam'));
    }

    public function perpanjang($id)
    {
        $pinjam = Peminjaman::find($id);
        $nim = $pinjam->anggota_id;
        $pinjam->status = 'perpanjang';
        $pinjam->perpanjang = 1;
        $pinjam->save();
        return redirect()->to('/petugas/transaksi/' . $nim . '/edit')->with('success', 'Perpanjang Peminjaman Berhasil!');
    }

    public function kembali($id)
    {
        $pinjam = Peminjaman::find($id);
        return view('petugas.peminjaman.modalKembali', compact('pinjam'));
    }
}
