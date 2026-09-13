<?php

/*
|--------------------------------------------------------------------------
| Aturan Peminjaman
|--------------------------------------------------------------------------
|
| Dipakai oleh App\Services\PeminjamanService dan ditampilkan di dashboard
| anggota. Semua nilai bisa ditimpa lewat .env tanpa mengubah kode.
|
*/

return [

    // Masa pinjam normal (hari kalender sejak tanggal pinjam)
    'masa_pinjam' => (int) env('PERPUS_MASA_PINJAM', 7),

    // Total masa pinjam setelah diperpanjang satu kali (hari)
    'masa_perpanjang' => (int) env('PERPUS_MASA_PERPANJANG', 14),

    // Berapa kali sebuah peminjaman boleh diperpanjang
    'maks_perpanjang' => (int) env('PERPUS_MAKS_PERPANJANG', 1),

    // Denda keterlambatan per hari untuk tiap judul buku (rupiah)
    'denda_per_hari' => (int) env('PERPUS_DENDA_PER_HARI', 2000),

];
