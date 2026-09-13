<?php

namespace Database\Seeders;

use App\Models\Anggota;
use App\Models\Buku;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        $this->call([
            UserSeeder::class,
            AdminSeeder::class,
            PetugasSeeder::class,
            AnggotaSeeder::class,
            KategoriSeeder::class,
            BukuSeeder::class,
        ]);

        // Data dummy: tiap anggota otomatis membuat user berperan anggota
        Anggota::factory(20)->create();
        Buku::factory(50)->create([
            'kategori_id' => fn () => fake()->numberBetween(1, 3),
        ]);
    }
}
