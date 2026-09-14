<?php

namespace App\Notifications;

use App\Models\Peminjaman;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

/**
 * Email sekali saat peminjaman sudah melewati jatuh tempo, berisi estimasi denda berjalan.
 */
class PemberitahuanTerlambat extends Notification implements ShouldQueue
{
    use Queueable;

    public function __construct(public readonly Peminjaman $peminjaman) {}

    /**
     * @return list<string>
     */
    public function via(object $notifiable): array
    {
        return ['mail'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        $judul = $this->peminjaman->buku->judul;
        $jatuhTempo = date('d-m-Y', strtotime($this->peminjaman->tgl_harus_kembali));
        $hari = $this->peminjaman->hariTerlambat();
        $denda = number_format($this->peminjaman->estimasiDenda(), 0, ',', '.');
        $tarif = number_format((int) config('perpustakaan.denda_per_hari'), 0, ',', '.');

        return (new MailMessage)
            ->subject("Terlambat: \"{$judul}\" belum dikembalikan")
            ->greeting("Halo {$notifiable->name},")
            ->line("Buku \"{$judul}\" seharusnya dikembalikan pada **{$jatuhTempo}** dan kini terlambat **{$hari} hari**.")
            ->line("Estimasi denda saat ini Rp {$denda} (Rp {$tarif} per hari) dan terus bertambah sampai buku dikembalikan.")
            ->line('Segera kembalikan buku ke petugas perpustakaan dan bayar denda saat pengembalian.')
            ->action('Lihat Peminjaman Saya', url('/anggota/pinjam'));
    }
}
