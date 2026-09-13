<?php

namespace Tests\Feature;

use App\Models\Admin;
use App\Models\Anggota;
use App\Models\Buku;
use App\Models\Peminjaman;
use App\Models\Petugas;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * Jaring pengaman: memastikan halaman utama tiap role masih bisa dirender
 * dan middleware role menolak akses silang.
 */
class SmokeTest extends TestCase
{
    use RefreshDatabase;

    public function test_landing_page_dapat_diakses_tanpa_login(): void
    {
        $this->get('/')->assertOk();
        $this->get('/login')->assertOk();
        $this->get('/register')->assertOk();
    }

    public function test_tamu_diarahkan_ke_login(): void
    {
        $this->get('/home')->assertRedirect('/login');
        $this->get('/admin')->assertRedirect('/login');
    }

    public function test_home_mengarahkan_ke_dashboard_sesuai_role(): void
    {
        $this->actingAs(Admin::factory()->create()->user)->get('/home')->assertRedirect('/admin');
        $this->actingAs(Petugas::factory()->create()->user)->get('/home')->assertRedirect('/petugas');
        $this->actingAs(Anggota::factory()->create()->user)->get('/home')->assertRedirect('/anggota');
    }

    public function test_halaman_admin_dapat_dirender(): void
    {
        $admin = Admin::factory()->create();
        Buku::factory()->create();
        Peminjaman::factory()->dipinjam()->create();

        $this->actingAs($admin->user);

        foreach ([
            '/admin', '/admin/admin', '/admin/petugas', '/admin/anggota', '/admin/kategori',
            '/admin/buku', '/admin/peminjaman', '/admin/peminjaman/create', '/admin/laporan/'.now()->month,
            '/profile', '/password',
        ] as $url) {
            $this->get($url)->assertOk();
        }
    }

    public function test_halaman_petugas_dapat_dirender(): void
    {
        $petugas = Petugas::factory()->create();
        $pinjam = Peminjaman::factory()->dipinjam()->create();

        $this->actingAs($petugas->user);

        foreach ([
            '/petugas', '/petugas/anggota', '/petugas/kategori', '/petugas/buku',
            '/petugas/transaksi', '/petugas/transaksi/create', '/petugas/transaksi/konfirmasi',
            '/petugas/transaksi/'.$pinjam->anggota_id.'/edit', '/petugas/laporan/'.now()->month,
        ] as $url) {
            $this->get($url)->assertOk();
        }
    }

    public function test_halaman_anggota_dapat_dirender(): void
    {
        $anggota = Anggota::factory()->create();
        $buku = Buku::factory()->create();

        $this->actingAs($anggota->user);

        foreach (['/anggota', '/anggota/buku', '/anggota/buku/'.$buku->id, '/anggota/pinjam'] as $url) {
            $this->get($url)->assertOk();
        }
    }

    public function test_middleware_role_menolak_akses_silang(): void
    {
        $anggota = Anggota::factory()->create()->user;
        $petugas = Petugas::factory()->create()->user;
        $admin = Admin::factory()->create()->user;

        $this->actingAs($anggota)->get('/admin')->assertRedirect('/');
        $this->actingAs($anggota)->get('/petugas')->assertRedirect('/');
        $this->actingAs($petugas)->get('/admin')->assertRedirect('/');
        $this->actingAs($admin)->get('/petugas')->assertRedirect('/');
        $this->actingAs($admin)->get('/anggota')->assertRedirect('/');
    }

    public function test_laporan_pdf_menghasilkan_dokumen_pdf(): void
    {
        $admin = Admin::factory()->create();
        Peminjaman::factory()->dipinjam()->create(['tgl_pinjam' => now()->toDateString()]);

        $this->actingAs($admin->user)
            ->get('/admin/laporan/cetak_pdf/'.now()->month)
            ->assertOk()
            ->assertHeader('content-type', 'application/pdf');
    }
}
