<?php

namespace App\Notifications;

use App\Models\Peminjaman;
use Illuminate\Notifications\Messages\MailMessage;

/**
 * Email + in-app sekali saat peminjaman sudah melewati jatuh tempo, berisi estimasi denda berjalan.
 */
class PemberitahuanTerlambat extends NotifikasiPerpustakaan
{
    protected bool $lewatEmail = true;

    public function __construct(public readonly Peminjaman $peminjaman) {}

    public function judul(): string
    {
        return 'Buku terlambat dikembalikan';
    }

    public function pesan(): string
    {
        return "\"{$this->peminjaman->buku->judul}\" terlambat {$this->peminjaman->hariTerlambat()} hari, "
            .'estimasi denda '.self::rupiah($this->peminjaman->estimasiDenda()).'.';
    }

    public function ikon(): string
    {
        return 'fas fa-exclamation-triangle';
    }

    public function warna(): string
    {
        return 'danger';
    }

    public function toMail(object $notifiable): MailMessage
    {
        $judul = $this->peminjaman->buku->judul;
        $jatuhTempo = self::tanggal($this->peminjaman->tgl_harus_kembali);
        $hari = $this->peminjaman->hariTerlambat();
        $denda = self::rupiah($this->peminjaman->estimasiDenda());
        $tarif = self::rupiah((int) config('perpustakaan.denda_per_hari'));

        return (new MailMessage)
            ->subject("Terlambat: \"{$judul}\" belum dikembalikan")
            ->greeting("Halo {$notifiable->name},")
            ->line("Buku \"{$judul}\" seharusnya dikembalikan pada **{$jatuhTempo}** dan kini terlambat **{$hari} hari**.")
            ->line("Estimasi denda saat ini {$denda} ({$tarif} per hari) dan terus bertambah sampai buku dikembalikan.")
            ->line('Segera kembalikan buku ke petugas perpustakaan dan bayar denda saat pengembalian.')
            ->action('Lihat Peminjaman Saya', url('/anggota/pinjam'));
    }
}
