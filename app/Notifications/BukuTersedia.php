<?php

namespace App\Notifications;

use App\Models\Peminjaman;
use Illuminate\Notifications\Messages\MailMessage;

/**
 * Buku yang di-booking sudah tersedia; booking sudah dinaikkan menjadi
 * pengajuan konfirmasi, anggota tinggal datang ke loket.
 */
class BukuTersedia extends NotifikasiPerpustakaan
{
    protected bool $lewatEmail = true;

    public function __construct(public readonly Peminjaman $peminjaman) {}

    public function judul(): string
    {
        return 'Buku booking sudah tersedia';
    }

    public function pesan(): string
    {
        return "\"{$this->peminjaman->buku->judul}\" tersedia kembali. Pengajuan Anda masuk antrean konfirmasi — "
            .'silakan ambil ke petugas'.$this->batasAmbil().'.';
    }

    public function ikon(): string
    {
        return 'fas fa-bookmark';
    }

    public function warna(): string
    {
        return 'success';
    }

    public function toMail(object $notifiable): MailMessage
    {
        $judul = $this->peminjaman->buku->judul;

        return (new MailMessage)
            ->subject("Buku \"{$judul}\" yang Anda booking sudah tersedia")
            ->greeting("Halo {$notifiable->name},")
            ->line("Kabar baik: buku \"{$judul}\" yang Anda booking kini tersedia kembali.")
            ->line('Booking Anda otomatis masuk antrean konfirmasi. Silakan datang ke petugas perpustakaan'.$this->batasAmbil().' untuk mengambil buku; stok akan dikurangi saat petugas mengonfirmasi.')
            ->line('Bila tidak jadi meminjam, batalkan pengajuan lewat halaman peminjaman agar anggota lain bisa mendapat giliran.')
            ->action('Lihat Peminjaman Saya', url('/anggota/pinjam'));
    }

    /**
     * " paling lambat dd-mm-yyyy" bila batas pengambilan diatur (lihat Peminjaman::batasAmbil()).
     */
    private function batasAmbil(): string
    {
        $batas = method_exists($this->peminjaman, 'batasAmbil') ? $this->peminjaman->batasAmbil() : null;

        return $batas ? ' paling lambat '.$batas->format('d-m-Y') : '';
    }
}
