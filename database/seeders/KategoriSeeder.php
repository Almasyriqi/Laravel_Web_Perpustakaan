<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class KategoriSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $nama = ['Novel', 'Fiksi', 'Referensi'];
        $keterangan = ['Koleksi buku novel', 'Koleksi buku Fiksi', 'Koleksi buku Referensi'];

        for ($i=0; $i < 3; $i++) { 
            DB::table('kategori')->insert([
                'nama' => $nama[$i],
                'keterangan' => $keterangan[$i]
            ]);
        }
    }
}
