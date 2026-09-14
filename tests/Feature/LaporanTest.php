<?php

namespace Tests\Feature;

use App\Exports\LaporanExport;
use App\Models\Admin;
use App\Models\Peminjaman;
use App\Models\Petugas;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Maatwebsite\Excel\Facades\Excel;
use Tests\TestCase;

class LaporanTest extends TestCase
{
    use RefreshDatabase;

    private const XLSX = 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet';

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
            ->assertHeader('content-disposition', 'inline; filename=laporan-2025-09.pdf');
    }

    public function test_export_excel_bulanan_mengunduh_xlsx(): void
    {
        Peminjaman::factory()->dipinjam()->create(['tgl_pinjam' => '2025-09-05']);

        $response = $this->actingAs(Admin::factory()->create()->user)
            ->get('/admin/laporan/excel/9?tahun=2025')
            ->assertOk()
            ->assertHeader('content-type', self::XLSX);

        $this->assertStringContainsString('laporan-2025-09.xlsx', $response->headers->get('content-disposition'));
    }

    public function test_export_excel_memakai_periode_yang_sama_dengan_laporan(): void
    {
        Excel::fake();

        $masuk = Peminjaman::factory()->kembali()->create(['tgl_pinjam' => '2026-09-05', 'denda' => 4000]);
        Peminjaman::factory()->dipinjam()->create(['tgl_pinjam' => '2026-08-31']);

        $this->actingAs(Petugas::factory()->create()->user)
            ->get('/petugas/laporan/excel/9?tahun=2026')
            ->assertOk();

        Excel::assertDownloaded('laporan-2026-09.xlsx', function (LaporanExport $export) use ($masuk) {
            $baris = $export->query()->get();

            $this->assertCount(1, $baris);
            $this->assertSame($masuk->id, $baris->first()->id);

            $peta = $export->map($baris->first());
            $this->assertSame($masuk->anggota->user->name, $peta[0]);
            $this->assertSame((string) $masuk->anggota_id, $peta[1]);
            $this->assertSame('05-09-2026', $peta[4]);
            $this->assertSame(4000, $peta[9]);
            $this->assertCount(count($export->headings()), $peta);

            return true;
        });
    }

    public function test_rentang_tanggal_bebas_inklusif_kedua_ujung(): void
    {
        $awal = Peminjaman::factory()->dipinjam()->create(['tgl_pinjam' => '2026-09-01']);
        $tengah = Peminjaman::factory()->dipinjam()->create(['tgl_pinjam' => '2026-09-10']);
        $akhir = Peminjaman::factory()->dipinjam()->create(['tgl_pinjam' => '2026-09-15']);
        $sebelum = Peminjaman::factory()->dipinjam()->create(['tgl_pinjam' => '2026-08-31']);
        $sesudah = Peminjaman::factory()->dipinjam()->create(['tgl_pinjam' => '2026-09-16']);

        $this->actingAs(Admin::factory()->create()->user)
            ->get('/admin/laporan/rentang?dari=2026-09-01&sampai=2026-09-15')
            ->assertOk()
            ->assertSee('01-09-2026 s.d. 15-09-2026')
            ->assertSee($awal->buku->judul)
            ->assertSee($tengah->buku->judul)
            ->assertSee($akhir->buku->judul)
            ->assertDontSee($sebelum->buku->judul)
            ->assertDontSee($sesudah->buku->judul)
            ->assertSee('/admin/laporan/rentang/pdf?dari=2026-09-01&amp;sampai=2026-09-15', false)
            ->assertSee('/admin/laporan/rentang/excel?dari=2026-09-01&amp;sampai=2026-09-15', false);
    }

    public function test_pdf_dan_excel_rentang_bebas(): void
    {
        Peminjaman::factory()->dipinjam()->create(['tgl_pinjam' => '2026-09-10']);
        $petugas = Petugas::factory()->create()->user;

        $this->actingAs($petugas)
            ->get('/petugas/laporan/rentang/pdf?dari=2026-09-01&sampai=2026-09-15')
            ->assertOk()
            ->assertHeader('content-type', 'application/pdf')
            ->assertHeader('content-disposition', 'inline; filename=laporan-2026-09-01_2026-09-15.pdf');

        $response = $this->actingAs($petugas)
            ->get('/petugas/laporan/rentang/excel?dari=2026-09-01&sampai=2026-09-15')
            ->assertOk()
            ->assertHeader('content-type', self::XLSX);

        $this->assertStringContainsString('laporan-2026-09-01_2026-09-15.xlsx', $response->headers->get('content-disposition'));
    }

    public function test_rentang_tidak_valid_ditolak(): void
    {
        $admin = Admin::factory()->create()->user;

        // sampai sebelum dari
        $this->actingAs($admin)->from('/admin')
            ->get('/admin/laporan/rentang?dari=2026-09-15&sampai=2026-09-01')
            ->assertRedirect('/admin')->assertSessionHasErrors('sampai');

        // parameter tidak lengkap
        $this->actingAs($admin)->from('/admin')
            ->get('/admin/laporan/rentang?dari=2026-09-01')
            ->assertRedirect('/admin')->assertSessionHasErrors('sampai');

        // lebih dari satu tahun
        $this->actingAs($admin)->from('/admin')
            ->get('/admin/laporan/rentang?dari=2025-01-01&sampai=2026-06-30')
            ->assertRedirect('/admin')->assertSessionHasErrors('sampai');

        // format salah
        $this->actingAs($admin)->from('/admin')
            ->get('/admin/laporan/rentang?dari=01-09-2026&sampai=2026-09-15')
            ->assertRedirect('/admin')->assertSessionHasErrors('dari');
    }
}
