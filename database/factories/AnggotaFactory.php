<?php

namespace Database\Factories;

use App\Models\Anggota;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Anggota>
 */
class AnggotaFactory extends Factory
{
    protected $model = Anggota::class;

    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'user_id' => User::factory()->anggota(),
            'jurusan' => 'Teknologi Informasi',
            'tgl_lahir' => fake()->date(),
            'no_hp' => fake()->phoneNumber(),
            'alamat' => fake()->address(),
        ];
    }
}
