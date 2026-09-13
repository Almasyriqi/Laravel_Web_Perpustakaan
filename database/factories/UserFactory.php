<?php

namespace Database\Factories;

use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends Factory<User>
 */
class UserFactory extends Factory
{
    protected $model = User::class;

    /**
     * Password bersama untuk seluruh user hasil factory (di-hash sekali saja).
     */
    protected static ?string $password;

    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            // kolom users.username adalah string(20)
            'username' => Str::limit(fake()->unique()->userName(), 20, ''),
            'name' => fake()->name(),
            'email' => fake()->unique()->safeEmail(),
            'email_verified_at' => now(),
            'password' => static::$password ??= bcrypt('12345678'),
            'role' => 'anggota',
            'remember_token' => Str::random(10),
        ];
    }

    public function unverified(): static
    {
        return $this->state(fn (array $attributes) => [
            'email_verified_at' => null,
        ]);
    }

    public function admin(): static
    {
        return $this->state(fn (array $attributes) => ['role' => 'admin']);
    }

    public function petugas(): static
    {
        return $this->state(fn (array $attributes) => ['role' => 'petugas']);
    }

    public function anggota(): static
    {
        return $this->state(fn (array $attributes) => ['role' => 'anggota']);
    }
}
