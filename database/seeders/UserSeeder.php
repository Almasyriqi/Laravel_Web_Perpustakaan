<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class UserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $username = ['admin', 'almasyriqi', 'petugas'];
        $name = ['Administrator', 'Riqi', 'Petugas'];
        $email = ['admin@gmail.com', 'almasyriqi@gmail.com', 'petugas@gmail.com'];
        $password = ['12345678', '12345678', '12345678'];
        $role = ['admin', 'anggota', 'petugas'];

        for ($i=0; $i < 3; $i++) { 
            DB::table('users')->insert([
                'username' => $username[$i],
                'name'=> $name[$i],
                'email'=> $email[$i],
                'password'=>Hash::make($password[$i]),
                'role' => $role[$i],
                'email_verified_at' => now()
            ]);
        }
    }
}
