<?php

namespace App\Http\Controllers;

use App\Models\Anggota;
use App\Models\Buku;
use App\Support\KodeQr;
use Barryvdh\DomPDF\Facade\Pdf;

/**
 * Cetakan PDF berisi kode QR: label buku dan kartu anggota.
 */
class CetakController extends Controller
{
    /** Ukuran kertas dalam point (1 mm = 2,8346 pt). */
    private const LABEL_BUKU = [0, 0, 170.1, 113.4];   // 60 x 40 mm

    private const KARTU_ANGGOTA = [0, 0, 242.6, 153.1]; // 85,6 x 54 mm (ukuran kartu ATM)

    /**
     * Label untuk ditempel di buku (admin & petugas).
     */
    public function labelBuku($id)
    {
        $buku = Buku::with('kategori')->findOrFail($id);
        $kode = KodeQr::buku($buku);

        return Pdf::loadView('cetak.label-buku', [
            'buku' => $buku,
            'kode' => $kode,
            'qr' => KodeQr::png($kode),
        ])->setPaper(self::LABEL_BUKU)->stream("label-buku-{$buku->id}.pdf");
    }

    /**
     * Kartu anggota siapa pun (admin & petugas).
     */
    public function kartuAnggota($nim)
    {
        return $this->kartu(Anggota::with('user')->findOrFail($nim));
    }

    /**
     * Kartu milik anggota yang sedang login.
     */
    public function kartuSaya()
    {
        return $this->kartu(Anggota::with('user')->where('user_id', auth()->id())->firstOrFail());
    }

    private function kartu(Anggota $anggota)
    {
        $kode = KodeQr::anggota($anggota);

        return Pdf::loadView('cetak.kartu-anggota', [
            'anggota' => $anggota,
            'kode' => $kode,
            'qr' => KodeQr::png($kode),
        ])->setPaper(self::KARTU_ANGGOTA)->stream("kartu-anggota-{$anggota->nim}.pdf");
    }
}
