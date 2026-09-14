<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Notification;

/**
 * Kelas dasar semua notifikasi aplikasi. Setiap turunan otomatis punya versi
 * in-app (database channel → lonceng di navbar); yang butuh email menyalakan
 * $lewatEmail dan menyediakan toMail().
 *
 * Isi in-app disimpan sebagai array {judul, pesan, url, ikon, warna} supaya
 * halaman /notifikasi dan dropdown lonceng bisa merendernya tanpa tahu jenisnya.
 */
abstract class NotifikasiPerpustakaan extends Notification implements ShouldQueue
{
    use Queueable;

    /** Kirim juga lewat email, selain in-app. */
    protected bool $lewatEmail = false;

    abstract public function judul(): string;

    abstract public function pesan(): string;

    /**
     * Halaman yang dibuka saat notifikasi diklik.
     */
    public function url(object $notifiable): string
    {
        return url('/anggota/pinjam');
    }

    /** Kelas ikon Font Awesome. */
    public function ikon(): string
    {
        return 'fas fa-bell';
    }

    /** Warna Bootstrap (info, success, warning, danger, primary, secondary). */
    public function warna(): string
    {
        return 'info';
    }

    /**
     * @return list<string>
     */
    public function via(object $notifiable): array
    {
        return $this->lewatEmail ? ['mail', 'database'] : ['database'];
    }

    /**
     * @return array{judul: string, pesan: string, url: string, ikon: string, warna: string}
     */
    public function toArray(object $notifiable): array
    {
        return [
            'judul' => $this->judul(),
            'pesan' => $this->pesan(),
            'url' => $this->url($notifiable),
            'ikon' => $this->ikon(),
            'warna' => $this->warna(),
        ];
    }

    protected static function rupiah(int $nilai): string
    {
        return 'Rp '.number_format($nilai, 0, ',', '.');
    }

    protected static function tanggal(?string $tgl): string
    {
        return $tgl === null ? '-' : date('d-m-Y', strtotime($tgl));
    }
}
