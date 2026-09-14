<?php

namespace Tests\Feature;

use App\Models\Admin;
use App\Models\Anggota;
use App\Models\Buku;
use App\Models\Peminjaman;
use App\Models\Petugas;
use App\Models\Ulasan;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * Rating & ulasan buku oleh anggota + moderasi admin/petugas.
 */
class UlasanTest extends TestCase
{
    use RefreshDatabase;

    private Anggota $anggota;

    private Buku $buku;

    protected function setUp(): void
    {
        parent::setUp();

        $this->anggota = Anggota::factory()->create();
        $this->buku = Buku::factory()->create(['judul' => 'Buku Yang Diulas']);
    }

    /** Anggota sudah pernah meminjam dan mengembalikan buku. */
    private function sudahMengembalikan(): void
    {
        Peminjaman::factory()->kembali()->create(['anggota_id' => $this->anggota->nim, 'buku_id' => $this->buku->id]);
    }

    public function test_hanya_yang_pernah_mengembalikan_buku_boleh_mengulas(): void
    {
        $url = '/anggota/buku/'.$this->buku->id.'/ulasan';

        // Belum pernah meminjam
        $this->actingAs($this->anggota->user)->from('/anggota/buku/'.$this->buku->id)
            ->post($url, ['rating' => 5])
            ->assertRedirect('/anggota/buku/'.$this->buku->id)
            ->assertSessionHasErrors('rating');
        $this->assertDatabaseCount('ulasan', 0);

        // Masih dipinjam (belum kembali) juga belum boleh
        $pinjam = Peminjaman::factory()->dipinjam()->create(['anggota_id' => $this->anggota->nim, 'buku_id' => $this->buku->id]);
        $this->actingAs($this->anggota->user)->post($url, ['rating' => 5])->assertSessionHasErrors('rating');

        // Setelah dikembalikan
        $pinjam->update(['status' => 'kembali', 'tgl_kembali' => now()->toDateString()]);
        $this->actingAs($this->anggota->user)->post($url, ['rating' => 4, 'komentar' => 'Bagus sekali'])
            ->assertRedirect('/anggota/buku/'.$this->buku->id)
            ->assertSessionHas('success');

        $this->assertDatabaseHas('ulasan', [
            'buku_id' => $this->buku->id, 'anggota_id' => $this->anggota->nim, 'rating' => 4, 'komentar' => 'Bagus sekali',
        ]);
    }

    public function test_rating_di_luar_1_sampai_5_ditolak(): void
    {
        $this->sudahMengembalikan();
        $url = '/anggota/buku/'.$this->buku->id.'/ulasan';

        $this->actingAs($this->anggota->user)->post($url, ['rating' => 0])->assertSessionHasErrors('rating');
        $this->actingAs($this->anggota->user)->post($url, ['rating' => 6])->assertSessionHasErrors('rating');
        $this->actingAs($this->anggota->user)->post($url, ['komentar' => 'tanpa rating'])->assertSessionHasErrors('rating');
        $this->actingAs($this->anggota->user)->post($url, ['rating' => 3, 'komentar' => str_repeat('a', 1001)])->assertSessionHasErrors('komentar');

        $this->assertDatabaseCount('ulasan', 0);
    }

    public function test_mengirim_ulang_memperbarui_ulasan_bukan_membuat_duplikat(): void
    {
        $this->sudahMengembalikan();
        $url = '/anggota/buku/'.$this->buku->id.'/ulasan';

        $this->actingAs($this->anggota->user)->post($url, ['rating' => 2, 'komentar' => 'Awalnya biasa']);
        $this->actingAs($this->anggota->user)->post($url, ['rating' => 5, 'komentar' => 'Ternyata bagus']);

        $this->assertDatabaseCount('ulasan', 1);
        $this->assertDatabaseHas('ulasan', ['anggota_id' => $this->anggota->nim, 'rating' => 5, 'komentar' => 'Ternyata bagus']);
    }

    public function test_rata_rata_rating_tampil_di_katalog_dan_detail(): void
    {
        Ulasan::factory()->create(['buku_id' => $this->buku->id, 'rating' => 5, 'komentar' => 'Luar biasa']);
        Ulasan::factory()->create(['buku_id' => $this->buku->id, 'rating' => 4]);
        Ulasan::factory()->create(['buku_id' => $this->buku->id, 'rating' => 3]);

        $this->actingAs($this->anggota->user)->get('/anggota/buku')
            ->assertOk()
            ->assertSee('Rating 4 dari 5')
            ->assertSee('4.0')
            ->assertSee('(3)');

        $this->actingAs($this->anggota->user)->get('/anggota/buku/'.$this->buku->id)
            ->assertOk()
            ->assertSee('Ulasan Anggota (3)')
            ->assertSee('Luar biasa')
            ->assertSee('Ulasan bisa ditulis setelah Anda meminjam');
    }

    public function test_form_ulasan_muncul_dan_terisi_ulasan_sendiri(): void
    {
        $this->sudahMengembalikan();
        Ulasan::factory()->create(['buku_id' => $this->buku->id, 'anggota_id' => $this->anggota->nim, 'rating' => 3, 'komentar' => 'Komentar saya']);

        $this->actingAs($this->anggota->user)->get('/anggota/buku/'.$this->buku->id)
            ->assertOk()
            ->assertSee('Ubah ulasan Anda')
            ->assertSee('Komentar saya')
            ->assertSee('Hapus ulasan saya');
    }

    public function test_anggota_hanya_bisa_menghapus_ulasannya_sendiri(): void
    {
        $orangLain = Anggota::factory()->create();
        $milikOrangLain = Ulasan::factory()->create(['buku_id' => $this->buku->id, 'anggota_id' => $orangLain->nim]);
        $milikSaya = Ulasan::factory()->create(['buku_id' => $this->buku->id, 'anggota_id' => $this->anggota->nim]);

        $this->actingAs($this->anggota->user)
            ->delete('/anggota/buku/'.$this->buku->id.'/ulasan')
            ->assertRedirect('/anggota/buku/'.$this->buku->id);

        $this->assertDatabaseMissing('ulasan', ['id' => $milikSaya->id]);
        $this->assertDatabaseHas('ulasan', ['id' => $milikOrangLain->id]);
    }

    public function test_admin_dan_petugas_dapat_memoderasi_ulasan(): void
    {
        $ulasan1 = Ulasan::factory()->create(['buku_id' => $this->buku->id, 'komentar' => 'Komentar tidak pantas']);
        $ulasan2 = Ulasan::factory()->create(['buku_id' => $this->buku->id]);
        $bukuLain = Buku::factory()->create();

        $admin = Admin::factory()->create()->user;
        $this->actingAs($admin)->get('/admin/buku/'.$this->buku->id)->assertOk()->assertSee('Komentar tidak pantas');
        $this->actingAs($admin)->delete('/admin/buku/'.$this->buku->id.'/ulasan/'.$ulasan1->id)
            ->assertRedirect('/admin/buku/'.$this->buku->id);
        $this->assertDatabaseMissing('ulasan', ['id' => $ulasan1->id]);

        // Ulasan harus milik buku di URL
        $this->actingAs($admin)->delete('/admin/buku/'.$bukuLain->id.'/ulasan/'.$ulasan2->id)->assertNotFound();

        $this->actingAs(Petugas::factory()->create()->user)
            ->delete('/petugas/buku/'.$this->buku->id.'/ulasan/'.$ulasan2->id)
            ->assertRedirect('/petugas/buku/'.$this->buku->id);
        $this->assertDatabaseCount('ulasan', 0);

        // Anggota tidak punya akses ke rute moderasi
        $ulasan3 = Ulasan::factory()->create(['buku_id' => $this->buku->id]);
        $this->actingAs($this->anggota->user)->delete('/admin/buku/'.$this->buku->id.'/ulasan/'.$ulasan3->id)->assertRedirect('/');
        $this->assertDatabaseHas('ulasan', ['id' => $ulasan3->id]);
    }
}
