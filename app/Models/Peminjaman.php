<?php

namespace App\Models;

use App\Services\PeminjamanService;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Carbon;

class Peminjaman extends Model
{
    use HasFactory;

    protected $table = 'peminjaman';

    protected $fillable = [
        'id',
        'anggota_id',
        'buku_id',
        'jumlah',
        'tgl_pinjam',
        'tgl_harus_kembali',
        'tgl_kembali',
        'lama_pinjam',
        'perpanjang',
        'status',
        'denda',
        'pengingat_dikirim_at',
        'teguran_dikirim_at',
    ];

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'pengingat_dikirim_at' => 'datetime',
            'teguran_dikirim_at' => 'datetime',
        ];
    }

    /**
     * anggota_id merujuk ke anggota.nim, bukan kolom id. withTrashed agar
     * riwayat tetap menampilkan anggota/buku yang sudah diarsipkan.
     */
    public function anggota(): BelongsTo
    {
        return $this->belongsTo(Anggota::class, 'anggota_id', 'nim')->withTrashed();
    }

    public function buku(): BelongsTo
    {
        return $this->belongsTo(Buku::class)->withTrashed();
    }

    /**
     * Buku masih di tangan anggota (dipinjam / perpanjang).
     */
    public function masihDipinjam(): bool
    {
        return in_array($this->status, PeminjamanService::STATUS_MENAHAN_STOK, true);
    }

    /**
     * Sudah lewat jatuh tempo dan belum dikembalikan.
     */
    public function terlambat(): bool
    {
        return $this->masihDipinjam()
            && $this->tgl_harus_kembali !== null
            && now()->startOfDay()->gt($this->tgl_harus_kembali);
    }

    /**
     * Pencarian daftar peminjaman: nama anggota atau judul buku.
     */
    public function scopeCari(Builder $query, ?string $kata): Builder
    {
        $kata = trim((string) $kata);

        if ($kata === '') {
            return $query;
        }

        return $query->where(fn (Builder $q) => $q
            ->whereHas('anggota.user', fn (Builder $user) => $user->where('name', 'like', "%{$kata}%"))
            ->orWhereHas('buku', fn (Builder $buku) => $buku->where('judul', 'like', "%{$kata}%")));
    }

    public function scopeStatus(Builder $query, ?string $status): Builder
    {
        return $query->when(
            in_array($status, PeminjamanService::SEMUA_STATUS, true),
            fn (Builder $q) => $q->where('status', $status),
        );
    }

    /**
     * Versi query dari terlambat() — dipanggil Peminjaman::lewatTempo(): masih di tangan anggota dan jatuh tempo sudah lewat.
     */
    public function scopeLewatTempo(Builder $query): Builder
    {
        return $query
            ->whereIn('status', PeminjamanService::STATUS_MENAHAN_STOK)
            ->where('tgl_harus_kembali', '<', now()->toDateString());
    }

    /**
     * Batas anggota mengambil buku ke loket untuk pengajuan berstatus konfirmasi:
     * tgl_pinjam (tanggal masuk antrean) + masa_ambil_pengajuan. Null untuk status lain.
     */
    public function batasAmbil(): ?Carbon
    {
        if ($this->status !== PeminjamanService::STATUS_KONFIRMASI) {
            return null;
        }

        return Carbon::parse($this->tgl_pinjam)->startOfDay()->addDays(self::masaAmbil());
    }

    /**
     * Pengajuan konfirmasi yang batas ambilnya sudah lewat (hari ini > batas).
     */
    public function scopeKedaluwarsa(Builder $query): Builder
    {
        return $query
            ->where('status', PeminjamanService::STATUS_KONFIRMASI)
            ->where('tgl_pinjam', '<', now()->subDays(self::masaAmbil())->toDateString());
    }

    public static function masaAmbil(): int
    {
        return max(1, (int) config('perpustakaan.masa_ambil_pengajuan', 3));
    }

    /**
     * Berapa hari sudah lewat jatuh tempo (0 bila belum terlambat).
     */
    public function hariTerlambat(): int
    {
        if (! $this->terlambat()) {
            return 0;
        }

        return (int) Carbon::parse($this->tgl_harus_kembali)->startOfDay()->diffInDays(now()->startOfDay());
    }

    /**
     * Denda yang akan dikenakan bila buku dikembalikan hari ini.
     */
    public function estimasiDenda(): int
    {
        return $this->hariTerlambat() * (int) config('perpustakaan.denda_per_hari');
    }
}
