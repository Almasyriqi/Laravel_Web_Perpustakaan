<?php

namespace Tests\Feature;

use App\Models\Admin;
use App\Models\Anggota;
use App\Models\Buku;
use App\Models\Peminjaman;
use App\Models\Petugas;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use PHPUnit\Framework\Attributes\DataProvider;
use Tests\TestCase;

/**
 * Halaman daftar harus memakai jumlah query yang konstan (eager loading),
 * bukan bertambah seiring jumlah baris (N+1).
 */
class EagerLoadingTest extends TestCase
{
    use RefreshDatabase;

    /** Batas aman: session/auth + query utama + beberapa query relasi. */
    private const MAKS_QUERY = 6;

    private const JUMLAH_BARIS = 15;

    protected function setUp(): void
    {
        parent::setUp();

        $this->travelTo('2026-09-10');

        Petugas::factory(3)->create();
        Admin::factory(3)->create();
        $buku = Buku::factory(5)->create();

        // 15 anggota, masing-masing punya satu peminjaman aktif dan satu pengajuan
        Anggota::factory(self::JUMLAH_BARIS)->create()->each(function (Anggota $anggota) use ($buku) {
            Peminjaman::factory()->dipinjam()->create([
                'anggota_id' => $anggota->nim,
                'buku_id' => $buku->random()->id,
                'tgl_pinjam' => '2026-09-05',
            ]);
            Peminjaman::factory()->konfirmasi()->create([
                'anggota_id' => $anggota->nim,
                'buku_id' => $buku->random()->id,
                'tgl_pinjam' => '2026-09-08',
            ]);
        });
    }

    /**
     * @return array<string, array{string, string}>
     */
    public static function halamanAdmin(): array
    {
        return [
            'data admin' => ['/admin/admin', 'admin'],
            'data petugas' => ['/admin/petugas', 'admin'],
            'data anggota' => ['/admin/anggota', 'admin'],
            'cari anggota' => ['/admin/anggota/cari?keyword=a', 'admin'],
            'data peminjaman' => ['/admin/peminjaman', 'admin'],
            'laporan' => ['/admin/laporan/9', 'admin'],
            'transaksi petugas' => ['/petugas/transaksi', 'petugas'],
            'konfirmasi petugas' => ['/petugas/transaksi/konfirmasi', 'petugas'],
            'katalog buku' => ['/petugas/buku', 'petugas'],
        ];
    }

    #[DataProvider('halamanAdmin')]
    public function test_halaman_daftar_tidak_n_plus_1(string $url, string $role): void
    {
        $user = $role === 'admin' ? Admin::first()->user : Petugas::first()->user;

        DB::enableQueryLog();
        $this->actingAs($user)->get($url)->assertOk();
        $jumlah = count(DB::getQueryLog());

        $this->assertLessThanOrEqual(
            self::MAKS_QUERY,
            $jumlah,
            "$url memakai $jumlah query untuk ".self::JUMLAH_BARIS.' baris — indikasi N+1'
        );
    }

    public function test_riwayat_anggota_tidak_n_plus_1(): void
    {
        $anggota = Anggota::first();
        foreach (range(1, 10) as $i) {
            Peminjaman::factory()->kembali()->create(['anggota_id' => $anggota->nim, 'buku_id' => Buku::first()->id]);
        }

        DB::enableQueryLog();
        $this->actingAs($anggota->user)->get('/anggota/pinjam')->assertOk();

        $this->assertLessThanOrEqual(self::MAKS_QUERY, count(DB::getQueryLog()));
    }
}
