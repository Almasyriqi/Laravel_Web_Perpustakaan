<?php

namespace App\Notifications;

use App\Models\Peminjaman;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

/**
 * Email H-N sebelum jatuh tempo: buku masih di tangan anggota dan sebentar lagi harus kembali.
 */
class PengingatJatuhTempo extends Notification implements ShouldQueue
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
        $denda = number_format((int) config('perpustakaan.denda_per_hari'), 0, ',', '.');

        return (new MailMessage)
            ->subject("Pengingat: \"{$judul}\" jatuh tempo {$jatuhTempo}")
            ->greeting("Halo {$notifiable->name},")
            ->line("Buku \"{$judul}\" ({$this->peminjaman->jumlah} eksemplar) yang Anda pinjam harus dikembalikan paling lambat **{$jatuhTempo}**.")
            ->line("Keterlambatan dikenakan denda Rp {$denda} per hari untuk setiap judul buku.")
            ->action('Lihat Peminjaman Saya', url('/anggota/pinjam'))
            ->line('Terima kasih telah menjaga koleksi perpustakaan.');
    }
}
