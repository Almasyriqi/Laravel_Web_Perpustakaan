<?php

namespace Database\Factories;

use App\Models\Buku;
use App\Models\Kategori;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Buku>
 */
class BukuFactory extends Factory
{
    protected $model = Buku::class;

    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'kategori_id' => Kategori::factory(),
            'judul' => fake()->sentence(3, true),
            'penerbit' => fake()->company(),
            'penulis' => fake()->name(),
            'keterangan' => fake()->text(100),
            'stok' => fake()->numberBetween(1, 100),
            'gambar' => '/images/harry_potter.jpg',
        ];
    }
}
