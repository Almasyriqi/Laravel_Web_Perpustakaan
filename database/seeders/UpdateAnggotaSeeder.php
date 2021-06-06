<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class UpdateAnggotaSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $id = 1;
        for ($i=4; $i <24 ; $i++) { 
            DB::table('anggota')->where('nim',$id)->update(['user_id' => $i]);
            $id++;
        }
    }
}
