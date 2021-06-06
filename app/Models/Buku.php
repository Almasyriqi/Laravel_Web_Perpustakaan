<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Buku extends Model
{
    use HasFactory;

    protected $table = "buku";
    protected $fillable = [
        'id',
        'kategori_id',
        'judul',
        'penerbit',
        'penulis',
        'keterangan',
        'stok'
    ];

    public function kategori()
    {
        return $this->belongsTo(Kategori::class);
    }

    public function anggota()
    {
        return $this->belongsToMany(Anggota::class, 'peminjaman', 'anggota_id', 'buku_id')->withPivot('jumlah', 'tgl_pinjam', 'tgl_kembali', 'lama_pinjam', 'status', 'denda');
    }
}
