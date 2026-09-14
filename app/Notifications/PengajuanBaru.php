<?php

namespace App\Notifications;

use App\Models\Peminjaman;
use App\Services\PeminjamanService;

/**
 * In-app untuk petugas & admin: ada pengajuan pinjam atau booking baru dari anggota.
 */
class PengajuanBaru extends NotifikasiPerpustakaan
{
    public function __construct(public readonly Peminjaman $peminjaman) {}

    public function judul(): string
    {
        return $this->booking() ? 'Booking baru' : 'Pengajuan peminjaman baru';
    }

    public function pesan(): string
    {
        $nama = $this->peminjaman->anggota?->user?->name ?? 'Anggota';
        $judul = $this->peminjaman->buku->judul;

        return $this->booking()
            ? "{$nama} mem-booking \"{$judul}\" (stok sedang habis)."
            : "{$nama} mengajukan pinjam \"{$judul}\" ({$this->peminjaman->jumlah} eksemplar) — menunggu konfirmasi.";
    }

    public function url(object $notifiable): string
    {
        return method_exists($notifiable, 'isPetugas') && $notifiable->isPetugas()
            ? url('/petugas/transaksi/konfirmasi')
            : url('/admin/peminjaman');
    }

    public function ikon(): string
    {
        return $this->booking() ? 'fas fa-bookmark' : 'fas fa-inbox';
    }

    public function warna(): string
    {
        return $this->booking() ? 'warning' : 'primary';
    }

    private function booking(): bool
    {
        return $this->peminjaman->status === PeminjamanService::STATUS_BOOKING;
    }
}
