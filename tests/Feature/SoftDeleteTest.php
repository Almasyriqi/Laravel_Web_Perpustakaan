<?php

namespace Tests\Feature;

use App\Models\Admin;
use App\Models\Anggota;
use App\Models\Buku;
use App\Models\Peminjaman;
use App\Models\Petugas;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class SoftDeleteTest extends TestCase
{
    use RefreshDatabase;

    private User $admin;

    protected function setUp(): void
    {
        parent::setUp();

        $this->travelTo('2026-09-10');
        $this->admin = Admin::factory()->create()->user;
    }

    public function test_buku_berriwayat_dapat_diarsipkan_dan_riwayat_tetap_menampilkan_judul(): void
    {
        $pinjam = Peminjaman::factory()->kembali()->create(['tgl_pinjam' => '2026-09-01']);
        $buku = $pinjam->buku;

        $this->actingAs($this->admin)
            ->delete('/admin/buku/'.$buku->id)
            ->assertRedirect('/admin/buku');

        $this->assertSoftDeleted('buku', ['id' => $buku->id]);
        $this->assertDatabaseHas('peminjaman', ['id' => $pinjam->id]);

        // riwayat anggota dan laporan masih menampilkan judul buku yang diarsipkan
        $this->actingAs($pinjam->anggota->user)->get('/anggota/pinjam')->assertOk()->assertSee($buku->judul);
        $this->actingAs($this->admin)->get('/admin/laporan/9')->assertOk()->assertSee($buku->judul);
        $this->actingAs($this->admin)->get('/admin/peminjaman/'.$pinjam->id)->assertOk()->assertSee($buku->judul);
    }

    public function test_buku_yang_diarsipkan_hilang_dari_katalog_dan_daftar(): void
    {
        $buku = Buku::factory()->create();
        $buku->delete();

        $this->actingAs(Anggota::factory()->create()->user)->get('/anggota/buku')->assertOk()->assertDontSee($buku->judul);
        $this->actingAs($this->admin)->get('/admin/buku')->assertOk()->assertDontSee($buku->judul);
        $this->actingAs($this->admin)->get('/admin/buku/'.$buku->id)->assertNotFound();
    }

    public function test_buku_yang_masih_dipinjam_tidak_bisa_dihapus(): void
    {
        $pinjam = Peminjaman::factory()->dipinjam()->create();

        $this->actingAs($this->admin)
            ->from('/admin/buku')
            ->delete('/admin/buku/'.$pinjam->buku_id)
            ->assertRedirect('/admin/buku')
            ->assertSessionHasErrors('buku');

        $this->assertDatabaseHas('buku', ['id' => $pinjam->buku_id, 'deleted_at' => null]);
    }

    public function test_arsip_buku_menampilkan_dan_memulihkan(): void
    {
        $buku = Buku::factory()->create();
        $buku->delete();

        $this->actingAs($this->admin)->get('/admin/buku/arsip')->assertOk()->assertSee($buku->judul);

        $this->actingAs($this->admin)
            ->put('/admin/buku/'.$buku->id.'/pulihkan')
            ->assertRedirect('/admin/buku');

        $this->assertDatabaseHas('buku', ['id' => $buku->id, 'deleted_at' => null]);
        $this->actingAs($this->admin)->get('/admin/buku')->assertOk()->assertSee($buku->judul); // flash terkonsumsi di sini
        $this->actingAs($this->admin)->get('/admin/buku/arsip')->assertOk()->assertDontSee($buku->judul);
    }

    public function test_petugas_juga_bisa_mengakses_arsip(): void
    {
        $buku = Buku::factory()->create();
        $buku->delete();
        $anggota = Anggota::factory()->create();
        $anggota->delete();

        $petugas = Petugas::factory()->create()->user;
        $this->actingAs($petugas)->get('/petugas/buku/arsip')->assertOk()->assertSee($buku->judul);
        $this->actingAs($petugas)->get('/petugas/anggota/arsip')->assertOk()->assertSee($anggota->user->name);
    }

    public function test_anggota_yang_diarsipkan_tidak_bisa_login_dan_riwayatnya_utuh(): void
    {
        $pinjam = Peminjaman::factory()->kembali()->create(['tgl_pinjam' => '2026-09-01']);
        $anggota = $pinjam->anggota;
        $user = $anggota->user;

        $this->actingAs($this->admin)
            ->delete('/admin/anggota/'.$anggota->nim)
            ->assertRedirect('/admin/anggota');

        $this->assertSoftDeleted('anggota', ['nim' => $anggota->nim]);
        $this->assertSoftDeleted('users', ['id' => $user->id]);
        $this->assertDatabaseHas('peminjaman', ['id' => $pinjam->id]);

        $this->post('/logout');
        $this->post('/login', ['email' => $user->username, 'password' => '12345678']);
        $this->assertGuest();

        $this->actingAs($this->admin)->get('/admin/anggota')->assertOk()->assertDontSee($user->email);
        $this->actingAs($this->admin)->get('/admin/laporan/9')->assertOk()->assertSee($user->name);
    }

    public function test_anggota_yang_masih_meminjam_tidak_bisa_dihapus(): void
    {
        $pinjam = Peminjaman::factory()->dipinjam()->create();

        $this->actingAs($this->admin)
            ->from('/admin/anggota')
            ->delete('/admin/anggota/'.$pinjam->anggota_id)
            ->assertRedirect('/admin/anggota')
            ->assertSessionHasErrors('anggota');

        $this->assertDatabaseHas('anggota', ['nim' => $pinjam->anggota_id, 'deleted_at' => null]);
    }

    public function test_memulihkan_anggota_mengembalikan_akunnya(): void
    {
        $anggota = Anggota::factory()->create();
        $user = $anggota->user;
        $this->actingAs($this->admin)->delete('/admin/anggota/'.$anggota->nim);
        $this->assertSoftDeleted('users', ['id' => $user->id]);

        $this->actingAs($this->admin)->get('/admin/anggota/arsip')->assertOk()->assertSee($user->name);
        $this->actingAs($this->admin)
            ->put('/admin/anggota/'.$anggota->nim.'/pulihkan')
            ->assertRedirect('/admin/anggota');

        $this->assertDatabaseHas('anggota', ['nim' => $anggota->nim, 'deleted_at' => null]);
        $this->assertDatabaseHas('users', ['id' => $user->id, 'deleted_at' => null]);

        $this->post('/logout');
        $this->post('/login', ['email' => $user->username, 'password' => '12345678']);
        $this->assertAuthenticatedAs($user->fresh());
    }

    public function test_username_anggota_yang_diarsipkan_tetap_terpesan(): void
    {
        $anggota = Anggota::factory()->create();
        $anggota->user->update(['username' => 'terpesan']);
        $this->actingAs($this->admin)->delete('/admin/anggota/'.$anggota->nim);

        $this->actingAs($this->admin)
            ->from('/admin/anggota/create')
            ->post('/admin/anggota', [
                'username' => 'terpesan', 'password' => 'rahasia123', 'nim' => '1941720200', 'nama' => 'Baru',
                'jurusan' => 'TI', 'tgl_lahir' => '2001-01-01', 'no_hp' => '0812', 'email' => 'baru@example.com',
            ])
            ->assertSessionHasErrors('username');
    }
}
