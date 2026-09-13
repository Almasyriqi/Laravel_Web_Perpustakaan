<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Anggota extends Model
{
    use HasFactory;

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

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Kolom peminjaman.anggota_id merujuk ke nim (bukan id), jadi FK dan
     * local key harus disebut eksplisit.
     */
    public function peminjaman(): HasMany
    {
        return $this->hasMany(Peminjaman::class, 'anggota_id', 'nim');
    }
}
