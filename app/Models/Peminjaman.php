<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Peminjaman extends Model
{
    use HasFactory;

    protected $table = 'peminjaman';

    public $timestamps = false;

    protected $fillable = [
        'id',
        'anggota_id',
        'buku_id',
        'jumlah',
        'tgl_pinjam',
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
}
