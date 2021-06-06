<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class AdminSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        DB::table('admin')->insert([
            'user_id' => 1,
            'no_hp' => '082213588054',
            'alamat' => 'Jl. Kembang Turi no.21'
        ]);
    }
}
