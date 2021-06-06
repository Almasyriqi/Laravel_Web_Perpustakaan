<?php

namespace Database\Seeders;

use App\Models\Anggota;
use App\Models\Buku;
use App\Models\User;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     *
     * @return void
     */
    public function run()
    {
        $this->call([
            UserSeeder::class,
            AdminSeeder::class,
            PetugasSeeder::class,
            KategoriSeeder::class,
            BukuSeeder::class,
        ]);
        User::factory(20)->create();
        Anggota::factory(20)->create();
        Buku::factory(50)->create();

        $this->call([
            UpdateAnggotaSeeder::class,
            AnggotaSeeder::class,
        ]);
    }
}
