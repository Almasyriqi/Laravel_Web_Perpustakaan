<?php

namespace Database\Factories;

use App\Models\Anggota;
use App\Models\Buku;
use App\Models\Ulasan;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Ulasan>
 */
class UlasanFactory extends Factory
{
    protected $model = Ulasan::class;

    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'buku_id' => Buku::factory(),
            'anggota_id' => Anggota::factory(),
            'rating' => fake()->numberBetween(Ulasan::RATING_MIN, Ulasan::RATING_MAKS),
            'komentar' => fake()->sentence(),
        ];
    }
}
