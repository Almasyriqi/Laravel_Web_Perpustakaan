<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Ulasan extends Model
{
    use HasFactory;

    public const RATING_MIN = 1;

    public const RATING_MAKS = 5;

    protected $table = 'ulasan';

    protected $fillable = [
        'buku_id',
        'anggota_id',
        'rating',
        'komentar',
    ];

    public function buku(): BelongsTo
    {
        return $this->belongsTo(Buku::class)->withTrashed();
    }

    /**
     * anggota_id merujuk ke anggota.nim; withTrashed agar nama pengulas yang
     * sudah diarsipkan tetap tampil.
     */
    public function anggota(): BelongsTo
    {
        return $this->belongsTo(Anggota::class, 'anggota_id', 'nim')->withTrashed();
    }
}
