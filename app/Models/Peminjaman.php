<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Peminjaman extends Model
{
    use HasFactory;

    protected $table = "peminjaman";
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
        'denda'
    ];

    public function anggota()
    {
        return $this->belongsTo(Anggota::class);
    }

    public function buku()
    {
        return $this->belongsTo(Buku::class);
    }
}
