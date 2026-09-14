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

    // Email pengingat dikirim N hari sebelum jatuh tempo (command perpus:kirim-pengingat)
    'pengingat_hari_sebelum' => (int) env('PERPUS_PENGINGAT_HARI_SEBELUM', 1),

    // Pengajuan berstatus konfirmasi yang tidak diambil ke loket dalam N hari dibatalkan otomatis
    // (command perpus:kedaluwarsa-pengajuan); berlaku juga untuk hasil promosi booking
    'masa_ambil_pengajuan' => (int) env('PERPUS_MASA_AMBIL_PENGAJUAN', 3),

];
