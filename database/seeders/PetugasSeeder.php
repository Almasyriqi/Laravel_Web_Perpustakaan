<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class PetugasSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        DB::table('petugas')->insert([
            'user_id' => 3,
            'tgl_lahir' => '1989-04-11',
            'no_hp' => '082213587056',
            'alamat' => 'Jl. Bunga Mawar no.20'
        ]);
    }
}
