<?php

namespace App\Notifications;

use App\Models\Peminjaman;
use Illuminate\Notifications\Messages\MailMessage;

/**
 * Email + in-app H-N sebelum jatuh tempo: buku masih di tangan anggota dan sebentar lagi harus kembali.
 */
class PengingatJatuhTempo extends NotifikasiPerpustakaan
{
    protected bool $lewatEmail = true;

    public function __construct(public readonly Peminjaman $peminjaman) {}

    public function judul(): string
    {
        return 'Pengingat jatuh tempo';
    }

    public function pesan(): string
    {
        return "Buku \"{$this->peminjaman->buku->judul}\" harus dikembalikan paling lambat "
            .self::tanggal($this->peminjaman->tgl_harus_kembali).'.';
    }

    public function ikon(): string
    {
        return 'fas fa-clock';
    }

    public function warna(): string
    {
        return 'warning';
    }

    public function toMail(object $notifiable): MailMessage
    {
        $judul = $this->peminjaman->buku->judul;
        $jatuhTempo = self::tanggal($this->peminjaman->tgl_harus_kembali);
        $denda = self::rupiah((int) config('perpustakaan.denda_per_hari'));

        return (new MailMessage)
            ->subject("Pengingat: \"{$judul}\" jatuh tempo {$jatuhTempo}")
            ->greeting("Halo {$notifiable->name},")
            ->line("Buku \"{$judul}\" ({$this->peminjaman->jumlah} eksemplar) yang Anda pinjam harus dikembalikan paling lambat **{$jatuhTempo}**.")
            ->line("Keterlambatan dikenakan denda {$denda} per hari untuk setiap judul buku.")
            ->action('Lihat Peminjaman Saya', url('/anggota/pinjam'))
            ->line('Terima kasih telah menjaga koleksi perpustakaan.');
    }
}
