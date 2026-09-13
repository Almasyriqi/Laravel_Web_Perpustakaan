<?php

namespace Database\Factories;

use App\Models\Petugas;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Petugas>
 */
class PetugasFactory extends Factory
{
    protected $model = Petugas::class;

    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'user_id' => User::factory()->petugas(),
            'tgl_lahir' => fake()->date(),
            'no_hp' => fake()->phoneNumber(),
            'alamat' => fake()->address(),
        ];
    }
}
