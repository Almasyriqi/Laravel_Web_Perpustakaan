<?php

namespace App\Services;

use App\Models\Peminjaman;
use App\Notifications\PemberitahuanTerlambat;
use App\Notifications\PengingatJatuhTempo;
use Illuminate\Database\Eloquent\Builder;

/**
 * Mengirim email pengingat jatuh tempo dan pemberitahuan keterlambatan.
 *
 * Setiap peminjaman hanya dikirimi sekali per jenis email; penandanya kolom
 * pengingat_dikirim_at / teguran_dikirim_at, jadi command boleh dijalankan
 * berulang pada hari yang sama tanpa mengirim email ganda.
 */
class PengingatService
{
    /**
     * Pengingat H-N: jatuh tempo tepat N hari lagi (N dari config pengingat_hari_sebelum).
     *
     * @return int jumlah email yang (akan) dikirim
     */
    public function kirimPengingat(bool $dryRun = false): int
    {
        $jatuhTempo = now()->addDays($this->hariSebelum())->toDateString();

        $query = Peminjaman::whereIn('status', PeminjamanService::STATUS_MENAHAN_STOK)
            ->where('tgl_harus_kembali', $jatuhTempo)
            ->whereNull('pengingat_dikirim_at');

        return $this->kirim($query, PengingatJatuhTempo::class, 'pengingat_dikirim_at', $dryRun);
    }

    /**
     * Pemberitahuan keterlambatan: sudah lewat jatuh tempo dan belum pernah ditegur.
     *
     * @return int jumlah email yang (akan) dikirim
     */
    public function kirimTeguran(bool $dryRun = false): int
    {
        $query = Peminjaman::lewatTempo()->whereNull('teguran_dikirim_at');

        return $this->kirim($query, PemberitahuanTerlambat::class, 'teguran_dikirim_at', $dryRun);
    }

    public function hariSebelum(): int
    {
        return max(0, (int) config('perpustakaan.pengingat_hari_sebelum', 1));
    }

    /**
     * @param  class-string<PengingatJatuhTempo|PemberitahuanTerlambat>  $notifikasi
     */
    private function kirim(Builder $query, string $notifikasi, string $kolomPenanda, bool $dryRun): int
    {
        $terkirim = 0;

        foreach ($query->with(['anggota.user', 'buku'])->lazyById(100) as $peminjaman) {
            $user = $peminjaman->anggota?->user;

            if ($user === null || $user->trashed()) {
                continue;
            }

            if (! $dryRun) {
                $user->notify(new $notifikasi($peminjaman));
                $peminjaman->forceFill([$kolomPenanda => now()])->save();
            }

            $terkirim++;
        }

        return $terkirim;
    }
}
