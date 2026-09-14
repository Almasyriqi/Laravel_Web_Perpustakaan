<?php

namespace App\Notifications;

use App\Models\Peminjaman;

/**
 * In-app untuk anggota: pengembalian tercatat, beserta denda bila terlambat.
 */
class BukuDikembalikan extends NotifikasiPerpustakaan
{
    public function __construct(public readonly Peminjaman $peminjaman) {}

    public function judul(): string
    {
        return $this->peminjaman->denda > 0 ? 'Buku dikembalikan — ada denda' : 'Buku dikembalikan';
    }

    public function pesan(): string
    {
        $judul = $this->peminjaman->buku->judul;

        if ($this->peminjaman->denda > 0) {
            return "\"{$judul}\" diterima kembali dengan denda ".self::rupiah((int) $this->peminjaman->denda)
                .' — dibayarkan ke petugas.';
        }

        return "\"{$judul}\" diterima kembali tepat waktu. Terima kasih! Anda kini bisa menulis ulasan untuk buku ini.";
    }

    public function url(object $notifiable): string
    {
        return $this->peminjaman->denda > 0
            ? url('/anggota/pinjam')
            : url('/anggota/buku/'.$this->peminjaman->buku_id);
    }

    public function ikon(): string
    {
        return $this->peminjaman->denda > 0 ? 'fas fa-money-bill-wave' : 'fas fa-undo';
    }

    public function warna(): string
    {
        return $this->peminjaman->denda > 0 ? 'warning' : 'success';
    }
}
