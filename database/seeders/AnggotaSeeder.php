<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class AnggotaSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        DB::table('anggota')->insert([
            'nim' => 1941720057,
            'user_id' => 2,
            'jurusan' => 'Teknologi Informasi',
            'tgl_lahir' => '2000-07-06',
            'no_hp' => '082213589072',
            'alamat' => 'Puri Cempaka Putih 2 Blok AY-02'
        ]);
    }
}
