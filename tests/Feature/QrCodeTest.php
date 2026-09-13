<?php

namespace Tests\Feature;

use App\Models\Admin;
use App\Models\Anggota;
use App\Models\Buku;
use App\Models\Petugas;
use App\Support\KodeQr;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * Kode QR buku & kartu anggota: format kode, cetakan PDF, dan hak akses.
 */
class QrCodeTest extends TestCase
{
    use RefreshDatabase;

    public function test_kode_buku_dan_anggota_bisa_diurai_kembali(): void
    {
        $buku = Buku::factory()->create();
        $anggota = Anggota::factory()->create();

        $this->assertSame('BK-'.$buku->id, KodeQr::buku($buku));
        $this->assertSame('AG-'.$anggota->nim, KodeQr::anggota($anggota));

        $this->assertSame(['tipe' => 'buku', 'id' => $buku->id], KodeQr::parse(KodeQr::buku($buku)));
        $this->assertSame(['tipe' => 'anggota', 'id' => $anggota->nim], KodeQr::parse(KodeQr::anggota($anggota)));
        $this->assertSame(['tipe' => 'buku', 'id' => 7], KodeQr::parse(' bk-7 '), 'huruf kecil & spasi ditoleransi');

        $this->assertNull(KodeQr::parse('XX-1'));
        $this->assertNull(KodeQr::parse('BK-'));
        $this->assertNull(KodeQr::parse('BK-1;DROP'));
        $this->assertNull(KodeQr::parse(''));
    }

    public function test_gambar_qr_berupa_data_uri(): void
    {
        $this->assertStringStartsWith('data:image/png;base64,', KodeQr::png('BK-1'));
        $this->assertStringStartsWith('data:image/svg+xml;base64,', KodeQr::svg('AG-1941720057'));
    }

    public function test_admin_dan_petugas_dapat_mencetak_label_buku_dan_kartu_anggota(): void
    {
        $buku = Buku::factory()->create();
        $anggota = Anggota::factory()->create();

        foreach (['admin' => Admin::factory()->create()->user, 'petugas' => Petugas::factory()->create()->user] as $prefix => $user) {
            $this->actingAs($user)
                ->get("/{$prefix}/buku/{$buku->id}/label")
                ->assertOk()
                ->assertHeader('content-type', 'application/pdf')
                ->assertHeader('content-disposition', "inline; filename=label-buku-{$buku->id}.pdf");

            $this->actingAs($user)
                ->get("/{$prefix}/anggota/{$anggota->nim}/kartu")
                ->assertOk()
                ->assertHeader('content-type', 'application/pdf')
                ->assertHeader('content-disposition', "inline; filename=kartu-anggota-{$anggota->nim}.pdf");

            $this->actingAs($user)->get("/{$prefix}/buku/999/label")->assertNotFound();
        }
    }

    public function test_halaman_detail_menampilkan_qr_dan_tombol_cetak(): void
    {
        $buku = Buku::factory()->create();
        $anggota = Anggota::factory()->create();
        $admin = Admin::factory()->create()->user;

        $this->actingAs($admin)->get('/admin/buku/'.$buku->id)
            ->assertOk()
            ->assertSee('BK-'.$buku->id)
            ->assertSee('data:image/svg+xml;base64,')
            ->assertSee('/admin/buku/'.$buku->id.'/label');

        $this->actingAs($admin)->get('/admin/anggota/'.$anggota->nim)
            ->assertOk()
            ->assertSee('AG-'.$anggota->nim)
            ->assertSee('/admin/anggota/'.$anggota->nim.'/kartu');
    }

    public function test_anggota_hanya_bisa_mencetak_kartunya_sendiri(): void
    {
        $saya = Anggota::factory()->create();
        $orangLain = Anggota::factory()->create();

        $this->actingAs($saya->user)
            ->get('/anggota/kartu')
            ->assertOk()
            ->assertHeader('content-type', 'application/pdf')
            ->assertHeader('content-disposition', "inline; filename=kartu-anggota-{$saya->nim}.pdf");

        // Rute admin/petugas untuk kartu orang lain ditolak middleware role
        $this->actingAs($saya->user)->get('/admin/anggota/'.$orangLain->nim.'/kartu')->assertRedirect('/');
        $this->actingAs($saya->user)->get('/petugas/anggota/'.$orangLain->nim.'/kartu')->assertRedirect('/');

        $this->actingAs($saya->user)->get('/profile')->assertOk()->assertSee('/anggota/kartu');
    }

    public function test_form_loket_dan_daftar_transaksi_punya_input_scan(): void
    {
        $petugas = Petugas::factory()->create()->user;

        $this->actingAs($petugas)->get('/petugas/transaksi/create')->assertOk()->assertSee('id="scan"', false);
        $this->actingAs($petugas)->get('/petugas/transaksi')->assertOk()->assertSee('id="scan"', false);
    }
}
