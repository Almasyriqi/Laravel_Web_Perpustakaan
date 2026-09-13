<?php

namespace App\Support;

/**
 * Nama bulan dalam Bahasa Indonesia (dipakai laporan & dashboard).
 */
final class Bulan
{
    public const NAMA = [
        1 => 'Januari', 'Februari', 'Maret', 'April', 'Mei', 'Juni',
        'Juli', 'Agustus', 'September', 'Oktober', 'November', 'Desember',
    ];

    public static function nama(int $bulan): string
    {
        return self::NAMA[$bulan];
    }

    /**
     * Tiga huruf pertama, mis. "Sep" — untuk label chart yang sempit.
     */
    public static function singkat(int $bulan): string
    {
        return mb_substr(self::NAMA[$bulan], 0, 3);
    }
}
