<?php

namespace Tests\Feature;

use App\Models\Admin;
use App\Models\Buku;
use App\Models\Kategori;
use App\Models\Petugas;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class SampulBukuTest extends TestCase
{
    use RefreshDatabase;

    private User $admin;

    private Kategori $kategori;

    protected function setUp(): void
    {
        parent::setUp();

        Storage::fake('public');
        $this->admin = Admin::factory()->create()->user;
        $this->kategori = Kategori::factory()->create();
    }

    /**
     * @return array<string, mixed>
     */
    private function dataBuku(array $override = []): array
    {
        return array_merge([
            'kategori' => $this->kategori->id,
            'judul' => 'Laskar Pelangi',
            'penerbit' => 'Bentang',
            'penulis' => 'Andrea Hirata',
            'keterangan' => 'Novel',
            'stok' => 3,
        ], $override);
    }

    public function test_tambah_buku_menyimpan_sampul_ke_storage_disk(): void
    {
        $this->actingAs($this->admin)
            ->post('/admin/buku', $this->dataBuku(['gambar' => UploadedFile::fake()->image('sampul.jpg', 300, 400)]))
            ->assertRedirect('/admin/buku');

        $buku = Buku::where('judul', 'Laskar Pelangi')->firstOrFail();

        $this->assertStringStartsWith('sampul/', $buku->gambar);
        Storage::disk('public')->assertExists($buku->gambar);
        $this->assertSame(Storage::disk('public')->url($buku->gambar), $buku->gambar_url);
        $this->assertStringContainsString('/storage/sampul/', $buku->gambar_url);
    }

    public function test_tambah_buku_tanpa_sampul_ditolak(): void
    {
        $this->actingAs($this->admin)
            ->from('/admin/buku/create')
            ->post('/admin/buku', $this->dataBuku())
            ->assertSessionHasErrors('gambar');

        $this->assertDatabaseCount('buku', 0);
    }

    public function test_ganti_sampul_menghapus_file_lama(): void
    {
        $lama = UploadedFile::fake()->image('lama.jpg')->store('sampul', 'public');
        $buku = Buku::factory()->create(['kategori_id' => $this->kategori->id, 'gambar' => $lama]);

        $this->actingAs(Petugas::factory()->create()->user)
            ->put('/petugas/buku/'.$buku->id, $this->dataBuku(['gambar' => UploadedFile::fake()->image('baru.png')]))
            ->assertRedirect('/petugas/buku');

        $buku->refresh();
        $this->assertNotSame($lama, $buku->gambar);
        Storage::disk('public')->assertMissing($lama);
        Storage::disk('public')->assertExists($buku->gambar);
    }

    public function test_edit_tanpa_unggah_mempertahankan_sampul(): void
    {
        $path = UploadedFile::fake()->image('tetap.jpg')->store('sampul', 'public');
        $buku = Buku::factory()->create(['kategori_id' => $this->kategori->id, 'gambar' => $path]);

        $this->actingAs($this->admin)
            ->put('/admin/buku/'.$buku->id, $this->dataBuku(['judul' => 'Judul Baru']))
            ->assertRedirect('/admin/buku');

        $buku->refresh();
        $this->assertSame('Judul Baru', $buku->judul);
        $this->assertSame($path, $buku->gambar);
        Storage::disk('public')->assertExists($path);
    }

    public function test_path_sampul_legacy_tetap_dilayani_apa_adanya(): void
    {
        $legacy = Buku::factory()->create(['gambar' => '/images/harry_potter.jpg']);
        $url = Buku::factory()->create(['gambar' => 'https://example.com/cover.jpg']);

        $this->assertSame('/images/harry_potter.jpg', $legacy->gambar_url);
        $this->assertSame('https://example.com/cover.jpg', $url->gambar_url);

        $this->actingAs($this->admin)->get('/admin/buku')->assertOk()->assertSee('src="/images/harry_potter.jpg"', false);
    }

    public function test_ganti_sampul_legacy_tidak_menyentuh_public_images(): void
    {
        $buku = Buku::factory()->create(['kategori_id' => $this->kategori->id, 'gambar' => '/images/harry_potter.jpg']);

        $this->actingAs($this->admin)
            ->put('/admin/buku/'.$buku->id, $this->dataBuku(['gambar' => UploadedFile::fake()->image('baru.jpg')]))
            ->assertRedirect('/admin/buku');

        $this->assertStringStartsWith('sampul/', $buku->fresh()->gambar);
        $this->assertFileExists(public_path('images/harry_potter.jpg'));
    }

    public function test_hapus_buku_tidak_menghapus_file_sampul(): void
    {
        $path = UploadedFile::fake()->image('arsip.jpg')->store('sampul', 'public');
        $buku = Buku::factory()->create(['kategori_id' => $this->kategori->id, 'gambar' => $path]);

        $this->actingAs($this->admin)->delete('/admin/buku/'.$buku->id)->assertRedirect('/admin/buku');

        $this->assertSoftDeleted('buku', ['id' => $buku->id]);
        Storage::disk('public')->assertExists($path);
    }
}
