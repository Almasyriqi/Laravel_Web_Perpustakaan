<?php

namespace Database\Factories;

use App\Models\Anggota;
use App\Models\Buku;
use App\Models\Peminjaman;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Peminjaman>
 */
class PeminjamanFactory extends Factory
{
    protected $model = Peminjaman::class;

    /**
     * Default: pengajuan dari anggota yang belum dikonfirmasi (stok belum berkurang).
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'anggota_id' => Anggota::factory(),
            'buku_id' => Buku::factory(),
            'jumlah' => 1,
            'tgl_pinjam' => now()->toDateString(),
            'tgl_kembali' => null,
            'lama_pinjam' => null,
            'status' => 'konfirmasi',
            'perpanjang' => 0,
            'denda' => 0,
        ];
    }

    public function konfirmasi(): static
    {
        return $this->state(fn (array $attributes) => ['status' => 'konfirmasi']);
    }

    public function dipinjam(): static
    {
        return $this->state(fn (array $attributes) => ['status' => 'dipinjam']);
    }

    public function perpanjang(): static
    {
        return $this->state(fn (array $attributes) => ['status' => 'perpanjang', 'perpanjang' => 1]);
    }

    public function kembali(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => 'kembali',
            'tgl_kembali' => now()->toDateString(),
            'lama_pinjam' => 0,
        ]);
    }
}
