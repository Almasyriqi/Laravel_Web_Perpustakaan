<?php

namespace App\Models;

use App\Services\PeminjamanService;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Anggota extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'anggota';

    protected $primaryKey = 'nim';

    protected $fillable = [
        'nim',
        'user_id',
        'jurusan',
        'tgl_lahir',
        'no_hp',
        'alamat',
    ];

    /**
     * withTrashed: akun ikut diarsipkan bersama anggota, tapi nama tetap harus
     * bisa ditampilkan di riwayat dan halaman arsip.
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class)->withTrashed();
    }

    /**
     * Kolom peminjaman.anggota_id merujuk ke nim (bukan id), jadi FK dan
     * local key harus disebut eksplisit.
     */
    public function peminjaman(): HasMany
    {
        return $this->hasMany(Peminjaman::class, 'anggota_id', 'nim');
    }

    /**
     * Masih memegang buku (dipinjam / perpanjang).
     */
    public function sedangMeminjam(): bool
    {
        return $this->peminjaman()->whereIn('status', PeminjamanService::STATUS_MENAHAN_STOK)->exists();
    }

    public function ulasan(): HasMany
    {
        return $this->hasMany(Ulasan::class, 'anggota_id', 'nim');
    }

    /**
     * Hanya anggota yang pernah meminjam dan mengembalikan buku itu yang boleh mengulasnya.
     */
    public function bolehMengulas(Buku $buku): bool
    {
        return $this->peminjaman()
            ->where('buku_id', $buku->id)
            ->where('status', PeminjamanService::STATUS_KEMBALI)
            ->exists();
    }

    public function ulasanUntuk(Buku $buku): ?Ulasan
    {
        return $this->ulasan()->where('buku_id', $buku->id)->first();
    }
}
