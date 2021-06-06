<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class BukuSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        DB::table('buku')->insert([
            'kategori_id' => 1,
            'judul' => 'Harry Potter',
            'penerbit' => 'PT. Elex Media',
            'penulis' => 'JK Rowling',
            'keterangan' => 'Novel ini bercerita tentang petualangan Harry Potter',
            'stok' => 5,
            'gambar' => '/images/harry_potter.jpg'
        ]);
    }
}
