<?php

namespace Tests\Feature;

use App\Models\Admin;
use App\Models\Peminjaman;
use App\Models\Petugas;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class LaporanTest extends TestCase
{
    use RefreshDatabase;

    public function test_laporan_hanya_menampilkan_bulan_pada_tahun_yang_diminta(): void
    {
        $tahunIni = Peminjaman::factory()->dipinjam()->create(['tgl_pinjam' => '2026-09-05']);
        $tahunLalu = Peminjaman::factory()->dipinjam()->create(['tgl_pinjam' => '2025-09-05']);
        $bulanLain = Peminjaman::factory()->dipinjam()->create(['tgl_pinjam' => '2026-08-31']);

        $this->travelTo('2026-09-13');

        $response = $this->actingAs(Admin::factory()->create()->user)->get('/admin/laporan/9');

        $response->assertOk()
            ->assertSee($tahunIni->buku->judul)
            ->assertDontSee($tahunLalu->buku->judul)
            ->assertDontSee($bulanLain->buku->judul)
            ->assertSee('September 2026');
    }

    public function test_parameter_tahun_dihormati(): void
    {
        $tahunLalu = Peminjaman::factory()->dipinjam()->create(['tgl_pinjam' => '2025-09-05']);
        $tahunIni = Peminjaman::factory()->dipinjam()->create(['tgl_pinjam' => '2026-09-05']);

        $this->travelTo('2026-09-13');

        $this->actingAs(Petugas::factory()->create()->user)
            ->get('/petugas/laporan/9?tahun=2025')
            ->assertOk()
            ->assertSee($tahunLalu->buku->judul)
            ->assertDontSee($tahunIni->buku->judul)
            ->assertSee('September 2025');
    }

    public function test_bulan_tidak_valid_ditolak(): void
    {
        $admin = Admin::factory()->create()->user;

        $this->actingAs($admin)->from('/admin')->get('/admin/laporan/13')->assertRedirect('/admin')->assertSessionHasErrors('bulan');
        $this->actingAs($admin)->from('/admin')->get('/admin/laporan/abc')->assertRedirect('/admin')->assertSessionHasErrors('bulan');
        $this->actingAs($admin)->from('/admin')->get('/admin/laporan/9?tahun=1999')->assertRedirect('/admin')->assertSessionHasErrors('tahun');
    }

    public function test_cetak_pdf_memakai_filter_tahun(): void
    {
        Peminjaman::factory()->dipinjam()->create(['tgl_pinjam' => '2025-09-05']);

        $this->actingAs(Petugas::factory()->create()->user)
            ->get('/petugas/laporan/cetak_pdf/9?tahun=2025')
            ->assertOk()
            ->assertHeader('content-type', 'application/pdf')
            ->assertHeader('content-disposition', 'inline; filename=laporan-2025-9.pdf');
    }
}
