<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Anggota extends Model
{
    use HasFactory;

    protected $table = "anggota";
    protected $primaryKey = 'nim';
    protected $fillable = [
        'nim',
        'user_id',
        'jurusan',
        'tgl_lahir',
        'no_hp',
        'alamat'
    ];

    public function buku()
    {
        return $this->belongsToMany(Buku::class, 'peminjaman', 'buku_id', 'anggota_id')->withPivot('jumlah', 'tgl_pinjam', 'tgl_kembali', 'lama_pinjam', 'status', 'denda');
    }
}
