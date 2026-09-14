<?php

namespace App\Notifications;

use App\Models\Peminjaman;

/**
 * In-app untuk anggota: petugas sudah mengonfirmasi, buku resmi dipinjam.
 */
class PengajuanDisetujui extends NotifikasiPerpustakaan
{
    public function __construct(public readonly Peminjaman $peminjaman) {}

    public function judul(): string
    {
        return 'Peminjaman disetujui';
    }

    public function pesan(): string
    {
        return "\"{$this->peminjaman->buku->judul}\" ({$this->peminjaman->jumlah} eksemplar) resmi dipinjam. "
            .'Kembalikan paling lambat '.self::tanggal($this->peminjaman->tgl_harus_kembali).'.';
    }

    public function ikon(): string
    {
        return 'fas fa-check-circle';
    }

    public function warna(): string
    {
        return 'success';
    }
}
