<?php

namespace Tests\Feature;

use App\Enums\Role;
use App\Models\Admin;
use App\Models\Anggota;
use App\Models\Buku;
use App\Models\Petugas;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * Aturan validasi Form Request benar-benar dieksekusi (dulu rule seperti
 * unique:users diabaikan karena salah bentuk array).
 */
class ValidationTest extends TestCase
{
    use RefreshDatabase;

    /**
     * @return array<string, string>
     */
    private function dataAnggota(array $override = []): array
    {
        return array_merge([
            'username' => 'siti',
            'password' => 'rahasia123',
            'nim' => '1941720100',
            'nama' => 'Siti Aminah',
            'jurusan' => 'Teknologi Informasi',
            'tgl_lahir' => '2001-05-05',
            'no_hp' => '08123456789',
            'email' => 'siti@example.com',
            'alamat' => 'Malang',
        ], $override);
    }

    public function test_username_duplikat_ditolak_saat_tambah_anggota(): void
    {
        User::factory()->create(['username' => 'siti']);
        $admin = Admin::factory()->create()->user;

        $this->actingAs($admin)
            ->from('/admin/anggota/create')
            ->post('/admin/anggota', $this->dataAnggota())
            ->assertRedirect('/admin/anggota/create')
            ->assertSessionHasErrors('username');

        $this->assertDatabaseMissing('anggota', ['nim' => 1941720100]);
        $this->assertDatabaseCount('users', 2);
    }

    public function test_email_duplikat_ditolak_saat_tambah_petugas(): void
    {
        User::factory()->create(['email' => 'sama@example.com']);

        $this->actingAs(Admin::factory()->create()->user)
            ->from('/admin/petugas/create')
            ->post('/admin/petugas', [
                'username' => 'petugasbaru',
                'password' => 'rahasia123',
                'nama' => 'Petugas Baru',
                'email' => 'sama@example.com',
                'tgl_lahir' => '1990-01-01',
                'no_hp' => '0811',
            ])
            ->assertSessionHasErrors('email');

        $this->assertDatabaseMissing('users', ['username' => 'petugasbaru']);
    }

    public function test_tambah_anggota_yang_valid_membuat_user_dan_profil(): void
    {
        $this->actingAs(Petugas::factory()->create()->user)
            ->post('/petugas/anggota', $this->dataAnggota())
            ->assertRedirect('/petugas/anggota');

        $user = User::where('username', 'siti')->firstOrFail();
        $this->assertSame(Role::Anggota, $user->role);
        $this->assertDatabaseHas('anggota', ['nim' => 1941720100, 'user_id' => $user->id]);
    }

    public function test_edit_anggota_tanpa_mengubah_username_tetap_diterima(): void
    {
        $anggota = Anggota::factory()->create();
        $anggota->user->update(['username' => 'tetap']);

        $this->actingAs(Admin::factory()->create()->user)
            ->put('/admin/anggota/'.$anggota->nim, $this->dataAnggota([
                'username' => 'tetap',
                'nim' => (string) $anggota->nim,
                'email' => $anggota->user->email,
                'nama' => 'Nama Baru',
            ]))
            ->assertRedirect('/admin/anggota')
            ->assertSessionDoesntHaveErrors();

        $this->assertSame('Nama Baru', $anggota->user->fresh()->name);
    }

    public function test_edit_anggota_memakai_username_milik_orang_lain_ditolak(): void
    {
        User::factory()->create(['username' => 'punyaorang']);
        $anggota = Anggota::factory()->create();

        $this->actingAs(Admin::factory()->create()->user)
            ->from('/admin/anggota/'.$anggota->nim.'/edit')
            ->put('/admin/anggota/'.$anggota->nim, $this->dataAnggota([
                'username' => 'punyaorang',
                'nim' => (string) $anggota->nim,
                'email' => $anggota->user->email,
            ]))
            ->assertSessionHasErrors('username');
    }

    public function test_profil_hanya_mengubah_data_sendiri(): void
    {
        $saya = Anggota::factory()->create();
        $orangLain = Anggota::factory()->create();

        $this->actingAs($saya->user)
            ->put('/profile/'.$orangLain->nim, [
                'username' => 'namasaya',
                'nama' => 'Nama Saya',
                'email' => 'saya@example.com',
                'no_hp' => '0812',
                'nim' => (string) $saya->nim,
                'jurusan' => 'TI',
                'tgl_lahir' => '2000-01-01',
            ])
            ->assertRedirect('/profile');

        $this->assertSame('namasaya', $saya->user->fresh()->username);
        $this->assertNotSame('namasaya', $orangLain->user->fresh()->username);
    }

    public function test_jumlah_pinjam_nol_atau_bukan_angka_ditolak(): void
    {
        $anggota = Anggota::factory()->create();
        $buku = Buku::factory()->create(['stok' => 5]);

        foreach (['0', '-1', 'abc', ''] as $jumlah) {
            $this->actingAs($anggota->user)
                ->from('/anggota/buku')
                ->post('/anggota/peminjaman/'.$buku->id, ['jumlah' => $jumlah])
                ->assertSessionHasErrors('jumlah');
        }

        $this->assertDatabaseCount('peminjaman', 0);
    }

    public function test_status_peminjaman_di_luar_daftar_ditolak(): void
    {
        $anggota = Anggota::factory()->create();
        $buku = Buku::factory()->create(['stok' => 5]);

        $this->actingAs(Admin::factory()->create()->user)
            ->from('/admin/peminjaman/create')
            ->post('/admin/peminjaman', [
                'anggota' => $anggota->nim,
                'judul' => $buku->id,
                'jumlah' => 1,
                'tgl_pinjam' => '2026-09-01',
                'status' => 'hilang',
            ])
            ->assertSessionHasErrors('status');

        $this->assertDatabaseCount('peminjaman', 0);
    }

    public function test_email_tidak_valid_ditolak(): void
    {
        $this->actingAs(Admin::factory()->create()->user)
            ->from('/admin/admin/create')
            ->post('/admin/admin', [
                'username' => 'adminbaru',
                'password' => 'rahasia123',
                'nama' => 'Admin Baru',
                'email' => 'bukan-email',
                'no_hp' => '0811',
            ])
            ->assertSessionHasErrors('email');
    }
}
