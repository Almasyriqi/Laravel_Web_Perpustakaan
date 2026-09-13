<?php

namespace App\Support;

use App\Models\Anggota;
use App\Models\Buku;
use chillerlan\QRCode\Common\EccLevel;
use chillerlan\QRCode\Output\QRGdImagePNG;
use chillerlan\QRCode\Output\QRMarkupSVG;
use chillerlan\QRCode\QRCode;
use chillerlan\QRCode\QROptions;

/**
 * Kode QR untuk label buku dan kartu anggota.
 *
 * Isi QR sengaja teks polos ("BK-12" / "AG-1941720057") supaya scanner USB
 * keyboard-wedge maupun kamera HP tinggal "mengetik" kode ke input loket.
 */
final class KodeQr
{
    public const AWALAN_BUKU = 'BK-';

    public const AWALAN_ANGGOTA = 'AG-';

    public static function buku(Buku|int $buku): string
    {
        return self::AWALAN_BUKU.($buku instanceof Buku ? $buku->id : $buku);
    }

    public static function anggota(Anggota|int $anggota): string
    {
        return self::AWALAN_ANGGOTA.($anggota instanceof Anggota ? $anggota->nim : $anggota);
    }

    /**
     * Mengurai kode hasil scan; null bila formatnya tidak dikenal.
     *
     * @return array{tipe: 'buku'|'anggota', id: int}|null
     */
    public static function parse(string $kode): ?array
    {
        $kode = strtoupper(trim($kode));

        if (preg_match('/^(BK|AG)-(\d{1,20})$/', $kode, $m) !== 1) {
            return null;
        }

        return ['tipe' => $m[1] === 'BK' ? 'buku' : 'anggota', 'id' => (int) $m[2]];
    }

    /**
     * Data URI PNG (via GD) — paling aman dirender DomPDF untuk cetak.
     */
    public static function png(string $data, int $skala = 6): string
    {
        return (new QRCode(self::opsi(QRGdImagePNG::class, $skala)))->render($data);
    }

    /**
     * Data URI SVG — tajam di layar untuk pratinjau di halaman detail.
     */
    public static function svg(string $data, int $skala = 6): string
    {
        return (new QRCode(self::opsi(QRMarkupSVG::class, $skala)))->render($data);
    }

    private static function opsi(string $output, int $skala): QROptions
    {
        return new QROptions([
            'outputInterface' => $output,
            'outputBase64' => true,
            'eccLevel' => EccLevel::M,
            'scale' => $skala,
            'addQuietzone' => true,
            'quietzoneSize' => 2,
        ]);
    }
}
