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

    public function test_dark_mode_dapat_diaktifkan_dan_bertahan_antar_halaman(): void
    {
        $admin = Admin::factory()->create()->user;
        $bodyGelap = '/<body[^>]*class="[^"]*\bdark-mode\b/';

        $awal = $this->actingAs($admin)->get('/admin')->assertOk()->assertSee('adminlte-darkmode-widget');
        $this->assertDoesNotMatchRegularExpression($bodyGelap, $awal->getContent());

        $this->actingAs($admin)->post('/adminlte/darkmode/toggle')->assertOk();

        $this->assertMatchesRegularExpression($bodyGelap, $this->actingAs($admin)->get('/admin')->assertOk()->getContent());
        $this->assertMatchesRegularExpression($bodyGelap, $this->actingAs($admin)->get('/admin/buku')->assertOk()->getContent());

        $this->actingAs($admin)->post('/adminlte/darkmode/toggle');
        $this->assertDoesNotMatchRegularExpression($bodyGelap, $this->actingAs($admin)->get('/admin')->getContent());
    }

    public function test_preferensi_dark_mode_tersimpan_per_akun(): void
    {
        $admin = Admin::factory()->create()->user;
        $petugas = Petugas::factory()->create()->user;
        $bodyGelap = '/<body[^>]*class="[^"]*\bdark-mode\b/';

        $this->assertNull($admin->dark_mode);

        $this->actingAs($admin)->post('/adminlte/darkmode/toggle')->assertOk();
        $this->assertTrue($admin->fresh()->dark_mode);

        // Session baru (perangkat/login lain): preferensi dimuat dari akun
        $this->flushSession();
        $this->assertMatchesRegularExpression($bodyGelap, $this->actingAs($admin)->get('/admin')->assertOk()->getContent());

        // Akun lain tidak ikut gelap
        $this->flushSession();
        $this->assertDoesNotMatchRegularExpression($bodyGelap, $this->actingAs($petugas)->get('/petugas')->assertOk()->getContent());
        $this->assertNull($petugas->fresh()->dark_mode);

        // Matikan lagi → tersimpan false dan session baru pun terang
        $this->flushSession();
        $this->actingAs($admin)->get('/admin');
        $this->actingAs($admin)->post('/adminlte/darkmode/toggle')->assertOk();
        $this->assertFalse($admin->fresh()->dark_mode);
        $this->flushSession();
        $this->assertDoesNotMatchRegularExpression($bodyGelap, $this->actingAs($admin)->get('/admin')->getContent());
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
