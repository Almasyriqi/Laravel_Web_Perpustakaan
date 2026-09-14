<?php

namespace App\Notifications;

use App\Models\Peminjaman;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

/**
 * Buku yang di-booking sudah tersedia; booking sudah dinaikkan menjadi
 * pengajuan konfirmasi, anggota tinggal datang ke loket.
 */
class BukuTersedia extends Notification implements ShouldQueue
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

        return (new MailMessage)
            ->subject("Buku \"{$judul}\" yang Anda booking sudah tersedia")
            ->greeting("Halo {$notifiable->name},")
            ->line("Kabar baik: buku \"{$judul}\" yang Anda booking kini tersedia kembali.")
            ->line('Booking Anda otomatis masuk antrean konfirmasi. Silakan datang ke petugas perpustakaan untuk mengambil buku; stok akan dikurangi saat petugas mengonfirmasi.')
            ->line('Bila tidak jadi meminjam, batalkan pengajuan lewat halaman peminjaman agar anggota lain bisa mendapat giliran.')
            ->action('Lihat Peminjaman Saya', url('/anggota/pinjam'));
    }
}
