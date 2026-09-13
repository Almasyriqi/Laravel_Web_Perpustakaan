<?php

namespace App\Models;

use App\Services\PeminjamanService;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Buku extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'buku';

    protected $fillable = [
        'id',
        'kategori_id',
        'judul',
        'penerbit',
        'penulis',
        'keterangan',
        'stok',
        'gambar',
    ];

    public function kategori(): BelongsTo
    {
        return $this->belongsTo(Kategori::class);
    }

    public function peminjaman(): HasMany
    {
        return $this->hasMany(Peminjaman::class);
    }

    /**
     * Masih ada eksemplar yang berada di tangan anggota.
     */
    public function sedangDipinjam(): bool
    {
        return $this->peminjaman()->whereIn('status', PeminjamanService::STATUS_MENAHAN_STOK)->exists();
    }
}
