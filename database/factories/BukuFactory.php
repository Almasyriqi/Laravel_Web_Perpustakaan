<?php

namespace Database\Factories;

use App\Models\Buku;
use Illuminate\Database\Eloquent\Factories\Factory;

class BukuFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = Buku::class;

    /**
     * Define the model's default state.
     *
     * @return array
     */
    public function definition()
    {
        return [
            'kategori_id' => $this->faker->numberBetween(1,3),
            'judul' => $this->faker->sentence(3, true),
            'penerbit' => $this->faker->company,
            'penulis' => $this->faker->name(),
            'keterangan' => $this->faker->text(100),
            'stok' => $this->faker->numberBetween(1,100),
            'gambar' => $this->faker->imageUrl(640, 480, 'Buku', true),
        ];
    }
}
