<?php

namespace App\Notifications;

use App\Models\Peminjaman;
use Illuminate\Notifications\Messages\MailMessage;

/**
 * Email + in-app: pengajuan (termasuk hasil promosi booking) tidak diambil
 * dalam batas waktu sehingga dibatalkan otomatis. Data disalin sebagai nilai
 * polos karena baris peminjaman-nya sudah dihapus saat notifikasi dikirim.
 */
class PengajuanKedaluwarsa extends NotifikasiPerpustakaan
{
    protected bool $lewatEmail = true;

    public readonly int $bukuId;

    public readonly string $judulBuku;

    public readonly int $jumlah;

    public readonly string $batasAmbil;

    public function __construct(Peminjaman $peminjaman)
    {
        $this->bukuId = $peminjaman->buku_id;
        $this->judulBuku = $peminjaman->buku->judul;
        $this->jumlah = $peminjaman->jumlah;
        $this->batasAmbil = $peminjaman->batasAmbil()?->format('d-m-Y') ?? '-';
    }

    public function judul(): string
    {
        return 'Pengajuan dibatalkan otomatis';
    }

    public function pesan(): string
    {
        return "\"{$this->judulBuku}\" tidak diambil sampai {$this->batasAmbil}, pengajuan dibatalkan. "
            .'Ajukan lagi bila masih membutuhkan.';
    }

    public function url(object $notifiable): string
    {
        return url('/anggota/buku/'.$this->bukuId);
    }

    public function ikon(): string
    {
        return 'fas fa-hourglass-end';
    }

    public function warna(): string
    {
        return 'secondary';
    }

    public function toMail(object $notifiable): MailMessage
    {
        return (new MailMessage)
            ->subject("Pengajuan \"{$this->judulBuku}\" dibatalkan otomatis")
            ->greeting("Halo {$notifiable->name},")
            ->line("Pengajuan peminjaman buku \"{$this->judulBuku}\" ({$this->jumlah} eksemplar) tidak diambil ke petugas sampai batas **{$this->batasAmbil}**, sehingga dibatalkan otomatis agar anggota lain bisa mendapat giliran.")
            ->line('Bila masih membutuhkan buku ini, silakan ajukan peminjaman (atau booking bila stok habis) kembali lewat katalog.')
            ->action('Buka Katalog', url('/anggota/buku/'.$this->bukuId));
    }
}
