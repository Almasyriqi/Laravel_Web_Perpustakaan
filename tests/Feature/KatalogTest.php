<?php

namespace Tests\Feature;

use App\Http\Controllers\BukuAnggotaController;
use App\Models\Anggota;
use App\Models\Buku;
use App\Models\Kategori;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * Pencarian & filter katalog buku untuk anggota.
 */
class KatalogTest extends TestCase
{
    use RefreshDatabase;

    private function anggota()
    {
        return Anggota::factory()->create()->user;
    }

    public function test_kata_kunci_mencocokkan_judul_penulis_dan_penerbit(): void
    {
        Buku::factory()->create(['judul' => 'Belajar Laravel', 'penulis' => 'Andi', 'penerbit' => 'Informatika']);
        Buku::factory()->create(['judul' => 'Basis Data', 'penulis' => 'Budi Laravelo', 'penerbit' => 'Andi Offset']);
        Buku::factory()->create(['judul' => 'Jaringan Komputer', 'penulis' => 'Citra', 'penerbit' => 'Laravel Press']);
        Buku::factory()->create(['judul' => 'Kalkulus', 'penulis' => 'Dedi', 'penerbit' => 'Erlangga']);

        $this->actingAs($this->anggota())
            ->get('/anggota/buku?q=laravel')
            ->assertOk()
            ->assertSee('Belajar Laravel')
            ->assertSee('Basis Data')
            ->assertSee('Jaringan Komputer')
            ->assertDontSee('Kalkulus')
            ->assertSee('Menampilkan <b>3</b> dari <b>3</b> buku', false);
    }

    public function test_filter_kategori(): void
    {
        $novel = Kategori::factory()->create(['nama' => 'Novel']);
        $teknik = Kategori::factory()->create(['nama' => 'Teknik']);
        Buku::factory()->create(['judul' => 'Laskar Pelangi', 'kategori_id' => $novel->id]);
        Buku::factory()->create(['judul' => 'Mekanika Teknik', 'kategori_id' => $teknik->id]);

        $this->actingAs($this->anggota())
            ->get('/anggota/buku?kategori='.$novel->id)
            ->assertOk()
            ->assertSee('Laskar Pelangi')
            ->assertDontSee('Mekanika Teknik');
    }

    public function test_filter_tersedia_menyembunyikan_stok_habis(): void
    {
        Buku::factory()->create(['judul' => 'Buku Ada', 'stok' => 2]);
        Buku::factory()->create(['judul' => 'Buku Habis', 'stok' => 0]);

        $anggota = $this->anggota();

        $this->actingAs($anggota)->get('/anggota/buku')
            ->assertOk()->assertSee('Buku Ada')->assertSee('Buku Habis')->assertSee('Stok habis');

        $this->actingAs($anggota)->get('/anggota/buku?tersedia=1')
            ->assertOk()->assertSee('Buku Ada')->assertDontSee('Buku Habis');
    }

    public function test_paginasi_mempertahankan_query_string(): void
    {
        $kategori = Kategori::factory()->create();
        Buku::factory()->count(BukuAnggotaController::PER_HALAMAN + 1)->create(['kategori_id' => $kategori->id]);

        $response = $this->actingAs($this->anggota())
            ->get('/anggota/buku?kategori='.$kategori->id.'&q=')
            ->assertOk()
            ->assertSee('Menampilkan <b>'.BukuAnggotaController::PER_HALAMAN.'</b> dari <b>'.(BukuAnggotaController::PER_HALAMAN + 1).'</b>', false);

        $response->assertSee('/anggota/buku?kategori='.$kategori->id.'&amp;page=2', false);
    }

    public function test_kategori_tidak_valid_ditolak(): void
    {
        $this->actingAs($this->anggota())
            ->from('/anggota')
            ->get('/anggota/buku?kategori=999')
            ->assertRedirect('/anggota')
            ->assertSessionHasErrors('kategori');
    }

    public function test_route_resource_selain_index_dan_show_tidak_terdaftar(): void
    {
        $this->actingAs($this->anggota())->get('/anggota/buku/create')->assertNotFound();
    }
}
