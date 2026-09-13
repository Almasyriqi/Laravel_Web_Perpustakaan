<?php

namespace Tests\Feature;

use App\Models\Admin;
use App\Models\Buku;
use App\Models\Peminjaman;
use App\Models\Petugas;
use App\Services\StatistikService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * Statistik dashboard admin & petugas (StatistikService + tampilannya).
 */
class StatistikTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->travelTo('2026-09-15');
    }

    private function service(): StatistikService
    {
        return app(StatistikService::class);
    }

    public function test_buku_terpopuler_diurutkan_dari_transaksi_terbanyak(): void
    {
        $laris = Buku::factory()->create(['judul' => 'Buku Laris']);
        $biasa = Buku::factory()->create(['judul' => 'Buku Biasa']);
        $tidakLaku = Buku::factory()->create(['judul' => 'Buku Tidak Laku']);
        $lama = Buku::factory()->create(['judul' => 'Buku Tahun Lalu']);

        Peminjaman::factory()->count(3)->kembali()->create(['buku_id' => $laris->id]);
        Peminjaman::factory()->dipinjam()->create(['buku_id' => $biasa->id]);
        // Pengajuan yang belum dikonfirmasi tidak dihitung
        Peminjaman::factory()->konfirmasi()->create(['buku_id' => $tidakLaku->id]);
        // Di luar jendela 12 bulan
        Peminjaman::factory()->kembali()->create(['buku_id' => $lama->id, 'tgl_pinjam' => '2025-08-01']);

        $terpopuler = $this->service()->bukuTerpopuler();

        $this->assertSame(['Buku Laris', 'Buku Biasa'], $terpopuler->pluck('judul')->all());
        $this->assertSame(3, $terpopuler->first()->peminjaman_count);
    }

    public function test_tren_bulanan_selalu_12_label_dan_bulan_kosong_bernilai_nol(): void
    {
        Peminjaman::factory()->count(2)->dipinjam()->create(['tgl_pinjam' => '2026-09-03']);
        Peminjaman::factory()->kembali()->create(['tgl_pinjam' => '2026-07-20']);
        Peminjaman::factory()->konfirmasi()->create(['tgl_pinjam' => '2026-09-10']); // tidak dihitung
        Peminjaman::factory()->kembali()->create(['tgl_pinjam' => '2025-09-30']); // di luar jendela

        $tren = $this->service()->trenBulanan();

        $this->assertCount(12, $tren['labels']);
        $this->assertCount(12, $tren['data']);
        $this->assertSame('Okt 2025', $tren['labels'][0]);
        $this->assertSame('Sep 2026', $tren['labels'][11]);
        $this->assertSame(2, $tren['data'][11]);
        $this->assertSame(1, $tren['data'][9]);
        $this->assertSame(0, $tren['data'][0]);
        $this->assertSame(3, array_sum($tren['data']));
    }

    public function test_daftar_keterlambatan_hanya_yang_aktif_dan_lewat_tempo(): void
    {
        $terlambat = Peminjaman::factory()->dipinjam()->create(['tgl_pinjam' => '2026-09-01']); // tempo 08-09
        $sangatTerlambat = Peminjaman::factory()->perpanjang()->create(['tgl_pinjam' => '2026-08-01']); // tempo 15-08
        Peminjaman::factory()->dipinjam()->create(['tgl_pinjam' => '2026-09-12']); // belum jatuh tempo
        Peminjaman::factory()->kembali()->create(['tgl_pinjam' => '2026-08-01']); // sudah kembali
        Peminjaman::factory()->konfirmasi()->create(['tgl_pinjam' => '2026-08-01']); // belum dipinjam

        $daftar = $this->service()->daftarKeterlambatan();

        $this->assertSame([$sangatTerlambat->id, $terlambat->id], $daftar->pluck('id')->all());
        $this->assertSame(31, $daftar->first()->hariTerlambat());
        $this->assertSame(31 * config('perpustakaan.denda_per_hari'), $daftar->first()->estimasiDenda());
        $this->assertSame(7, $daftar->last()->hariTerlambat());
    }

    public function test_ringkasan_menghitung_status_dan_denda_bulan_ini(): void
    {
        Peminjaman::factory()->count(2)->dipinjam()->create(['tgl_pinjam' => '2026-09-10']);
        Peminjaman::factory()->dipinjam()->create(['tgl_pinjam' => '2026-08-20']); // terlambat
        Peminjaman::factory()->count(3)->konfirmasi()->create();
        Peminjaman::factory()->kembali()->create(['tgl_kembali' => '2026-09-02', 'denda' => 4000]);
        Peminjaman::factory()->kembali()->create(['tgl_kembali' => '2026-09-14', 'denda' => 6000]);
        Peminjaman::factory()->kembali()->create(['tgl_kembali' => '2026-08-30', 'denda' => 10000]); // bulan lalu

        $ringkasan = $this->service()->ringkasan();

        $this->assertSame(3, $ringkasan['sedang_dipinjam']);
        $this->assertSame(3, $ringkasan['menunggu_konfirmasi']);
        $this->assertSame(1, $ringkasan['terlambat']);
        $this->assertSame(10000, $ringkasan['denda_bulan_ini']);
    }

    public function test_dashboard_admin_dan_petugas_menampilkan_statistik(): void
    {
        $buku = Buku::factory()->create(['judul' => 'Buku Paling Laris']);
        Peminjaman::factory()->count(2)->kembali()->create(['buku_id' => $buku->id]);
        $terlambat = Peminjaman::factory()->dipinjam()->create(['tgl_pinjam' => '2026-08-25']);

        $this->actingAs(Admin::factory()->create()->user)->get('/admin')
            ->assertOk()
            ->assertSee('Buku Paling Laris')
            ->assertSee('Daftar Keterlambatan')
            ->assertSee($terlambat->buku->judul)
            ->assertSee('/admin/peminjaman/'.$terlambat->id)
            ->assertSee('Sep 2026');

        $this->actingAs(Petugas::factory()->create()->user)->get('/petugas')
            ->assertOk()
            ->assertSee('Buku Paling Laris')
            ->assertSee('/petugas/transaksi/'.$terlambat->anggota_id.'/edit');
    }
}
