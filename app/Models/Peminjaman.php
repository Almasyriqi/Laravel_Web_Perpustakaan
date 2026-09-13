<?php

namespace App\Models;

use App\Services\PeminjamanService;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

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
    ];

    /**
     * anggota_id merujuk ke anggota.nim, bukan kolom id.
     */
    public function anggota(): BelongsTo
    {
        return $this->belongsTo(Anggota::class, 'anggota_id', 'nim');
    }

    public function buku(): BelongsTo
    {
        return $this->belongsTo(Buku::class);
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
}
