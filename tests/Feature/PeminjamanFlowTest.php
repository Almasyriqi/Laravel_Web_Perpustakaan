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
 * Alur ajukan -> konfirmasi -> perpanjang -> kembali beserta stok dan denda.
 */
class PeminjamanFlowTest extends TestCase
{
    use RefreshDatabase;

    private Anggota $anggota;

    private Petugas $petugas;

    private Buku $buku;

    protected function setUp(): void
    {
        parent::setUp();

        $this->travelTo('2026-09-01 10:00:00');

        $this->anggota = Anggota::factory()->create();
        $this->petugas = Petugas::factory()->create();
        $this->buku = Buku::factory()->create(['stok' => 5]);
    }

    public function test_anggota_mengajukan_peminjaman_tanpa_mengurangi_stok(): void
    {
        $this->actingAs($this->anggota->user)
            ->post('/anggota/peminjaman/'.$this->buku->id, ['jumlah' => 2])
            ->assertRedirect('/anggota/buku');

        $this->assertDatabaseHas('peminjaman', [
            'anggota_id' => $this->anggota->nim,
            'buku_id' => $this->buku->id,
            'jumlah' => 2,
            'status' => 'konfirmasi',
            'tgl_pinjam' => '2026-09-01',
        ]);
        $this->assertSame(5, $this->buku->fresh()->stok);
    }

    public function test_pengajuan_melebihi_stok_ditolak(): void
    {
        $this->actingAs($this->anggota->user)
            ->from('/anggota/buku')
            ->post('/anggota/peminjaman/'.$this->buku->id, ['jumlah' => 6])
            ->assertRedirect('/anggota/buku')
            ->assertSessionHasErrors('jumlah');

        $this->assertDatabaseCount('peminjaman', 0);
        $this->assertSame(5, $this->buku->fresh()->stok);
    }

    public function test_petugas_mengonfirmasi_pengajuan_dan_stok_berkurang(): void
    {
        $pinjam = $this->pengajuan(jumlah: 2);

        $this->actingAs($this->petugas->user)
            ->put('/petugas/transaksi/konfirmasi/'.$pinjam->id)
            ->assertRedirect('/petugas/transaksi/konfirmasi');

        $this->assertSame('dipinjam', $pinjam->fresh()->status);
        $this->assertSame(3, $this->buku->fresh()->stok);
    }

    public function test_konfirmasi_ditolak_bila_stok_sudah_habis(): void
    {
        $pinjam = $this->pengajuan(jumlah: 2);
        $this->buku->update(['stok' => 1]);

        $this->actingAs($this->petugas->user)
            ->from('/petugas/transaksi/konfirmasi')
            ->put('/petugas/transaksi/konfirmasi/'.$pinjam->id)
            ->assertRedirect('/petugas/transaksi/konfirmasi')
            ->assertSessionHasErrors('jumlah');

        $this->assertSame('konfirmasi', $pinjam->fresh()->status);
        $this->assertSame(1, $this->buku->fresh()->stok);
    }

    public function test_konfirmasi_dua_kali_tidak_mengurangi_stok_dua_kali(): void
    {
        $pinjam = $this->pengajuan(jumlah: 2);

        $this->actingAs($this->petugas->user)->put('/petugas/transaksi/konfirmasi/'.$pinjam->id);
        $this->actingAs($this->petugas->user)
            ->from('/petugas/transaksi/konfirmasi')
            ->put('/petugas/transaksi/konfirmasi/'.$pinjam->id)
            ->assertSessionHasErrors('status');

        $this->assertSame(3, $this->buku->fresh()->stok);
    }

    public function test_petugas_memperpanjang_hanya_sekali(): void
    {
        $pinjam = $this->dipinjam(jumlah: 1);

        $this->actingAs($this->petugas->user)
            ->put('/petugas/transaksi/perpanjang/'.$pinjam->id)
            ->assertRedirect('/petugas/transaksi/'.$this->anggota->nim.'/edit');

        $pinjam->refresh();
        $this->assertSame('perpanjang', $pinjam->status);
        $this->assertSame(1, $pinjam->perpanjang);

        $this->actingAs($this->petugas->user)
            ->from('/petugas/transaksi')
            ->put('/petugas/transaksi/perpanjang/'.$pinjam->id)
            ->assertSessionHasErrors('perpanjang');
    }

    public function test_pengembalian_tepat_waktu_tanpa_denda(): void
    {
        $pinjam = $this->dipinjam(jumlah: 2);

        $this->travel(5)->days();

        $this->actingAs($this->petugas->user)
            ->put('/petugas/transaksi/'.$pinjam->id)
            ->assertRedirect('/petugas/transaksi/'.$this->anggota->nim.'/edit')
            ->assertSessionHas('success', fn (string $m) => str_contains($m, 'tepat waktu'));

        $pinjam->refresh();
        $this->assertSame('kembali', $pinjam->status);
        $this->assertSame('2026-09-06', $pinjam->tgl_kembali);
        $this->assertSame(5, $pinjam->lama_pinjam);
        $this->assertSame(0, $pinjam->denda);
        $this->assertSame(5, $this->buku->fresh()->stok);
    }

    public function test_pengembalian_terlambat_dikenai_denda_per_hari(): void
    {
        $pinjam = $this->dipinjam(jumlah: 1);

        $this->travel(10)->days();

        $this->actingAs($this->petugas->user)
            ->put('/petugas/transaksi/'.$pinjam->id)
            ->assertSessionHas('success', fn (string $m) => str_contains($m, 'Rp 6.000'));

        $pinjam->refresh();
        $this->assertSame(10, $pinjam->lama_pinjam);
        $this->assertSame(6000, $pinjam->denda);
        $this->assertSame(5, $this->buku->fresh()->stok);
    }

    public function test_pengembalian_setelah_perpanjangan_memakai_batas_14_hari(): void
    {
        $pinjam = $this->dipinjam(jumlah: 1);
        $this->actingAs($this->petugas->user)->put('/petugas/transaksi/perpanjang/'.$pinjam->id);

        $this->travel(20)->days();
        $this->actingAs($this->petugas->user)->put('/petugas/transaksi/'.$pinjam->id);

        $pinjam->refresh();
        $this->assertSame(20, $pinjam->lama_pinjam);
        $this->assertSame(12000, $pinjam->denda);
        $this->assertSame(5, $this->buku->fresh()->stok);
    }

    public function test_tarif_denda_mengikuti_konfigurasi(): void
    {
        config(['perpustakaan.denda_per_hari' => 5000, 'perpustakaan.masa_pinjam' => 3]);
        $pinjam = $this->dipinjam(jumlah: 1);

        $this->travel(6)->days(); // 3 hari terlambat
        $this->actingAs($this->petugas->user)->put('/petugas/transaksi/'.$pinjam->id);

        $this->assertSame(15000, $pinjam->fresh()->denda);
    }

    public function test_konfirmasi_menetapkan_jatuh_tempo_dan_timestamps(): void
    {
        $pinjam = $this->pengajuan(jumlah: 1);
        $this->assertNull($pinjam->tgl_harus_kembali);

        $this->actingAs($this->petugas->user)->put('/petugas/transaksi/konfirmasi/'.$pinjam->id);

        $pinjam->refresh();
        $this->assertSame('2026-09-08', $pinjam->tgl_harus_kembali);
        $this->assertNotNull($pinjam->created_at);
        $this->assertNotNull($pinjam->updated_at);
    }

    public function test_perpanjangan_menggeser_jatuh_tempo(): void
    {
        $pinjam = $this->dipinjam(jumlah: 1);
        $this->assertSame('2026-09-08', $pinjam->tgl_harus_kembali);

        $this->actingAs($this->petugas->user)->put('/petugas/transaksi/perpanjang/'.$pinjam->id);

        $this->assertSame('2026-09-15', $pinjam->fresh()->tgl_harus_kembali);
    }

    public function test_riwayat_menandai_peminjaman_yang_terlambat(): void
    {
        $pinjam = $this->dipinjam(jumlah: 1);

        $this->travel(9)->days();

        $this->actingAs($this->anggota->user)->get('/anggota/pinjam')
            ->assertOk()
            ->assertSee('08-09-2026')
            ->assertSee('Terlambat')
            ->assertDontSee('Perpanjang</a>', false);
        $this->assertTrue($pinjam->fresh()->terlambat());
    }

    public function test_admin_mengubah_tgl_pinjam_menggeser_jatuh_tempo(): void
    {
        $pinjam = $this->dipinjam(jumlah: 1);

        $this->actingAs(Admin::factory()->create()->user)->put('/admin/peminjaman/'.$pinjam->id, [
            'jumlah' => 1, 'tgl_pinjam' => '2026-09-03', 'status' => 'dipinjam', 'perpanjang' => 0,
        ]);

        $this->assertSame('2026-09-10', $pinjam->fresh()->tgl_harus_kembali);
    }

    public function test_pengembalian_dua_kali_tidak_menambah_stok_dua_kali(): void
    {
        $pinjam = $this->dipinjam(jumlah: 1);

        $this->actingAs($this->petugas->user)->put('/petugas/transaksi/'.$pinjam->id);
        $this->actingAs($this->petugas->user)
            ->from('/petugas/transaksi')
            ->put('/petugas/transaksi/'.$pinjam->id)
            ->assertSessionHasErrors('status');

        $this->assertSame(5, $this->buku->fresh()->stok);
    }

    public function test_petugas_mencatat_peminjaman_langsung(): void
    {
        $this->actingAs($this->petugas->user)
            ->post('/petugas/transaksi', [
                'anggota' => $this->anggota->nim,
                'judul' => $this->buku->id,
                'jumlah' => 3,
            ])
            ->assertRedirect('/petugas/transaksi');

        $this->assertDatabaseHas('peminjaman', ['buku_id' => $this->buku->id, 'jumlah' => 3, 'status' => 'dipinjam']);
        $this->assertSame(2, $this->buku->fresh()->stok);
    }

    public function test_anggota_membatalkan_pengajuan_sendiri(): void
    {
        $pinjam = $this->pengajuan(jumlah: 1);

        $this->actingAs($this->anggota->user)
            ->delete('/anggota/pinjam/'.$pinjam->id)
            ->assertRedirect('/anggota/pinjam');

        $this->assertDatabaseMissing('peminjaman', ['id' => $pinjam->id]);
    }

    public function test_anggota_tidak_bisa_membatalkan_peminjaman_orang_lain(): void
    {
        $milikOrangLain = Peminjaman::factory()->create(['buku_id' => $this->buku->id]);

        $this->actingAs($this->anggota->user)
            ->delete('/anggota/pinjam/'.$milikOrangLain->id)
            ->assertNotFound();

        $this->assertDatabaseHas('peminjaman', ['id' => $milikOrangLain->id]);
    }

    public function test_anggota_tidak_bisa_membatalkan_yang_sudah_dipinjam(): void
    {
        $pinjam = $this->dipinjam(jumlah: 2);

        $this->actingAs($this->anggota->user)
            ->from('/anggota/pinjam')
            ->delete('/anggota/pinjam/'.$pinjam->id)
            ->assertSessionHasErrors('status');

        $this->assertDatabaseHas('peminjaman', ['id' => $pinjam->id]);
        $this->assertSame(3, $this->buku->fresh()->stok);
    }

    public function test_admin_mengedit_tanpa_mengubah_apa_pun_tidak_menggeser_stok(): void
    {
        $pinjam = $this->dipinjam(jumlah: 2);
        $admin = Admin::factory()->create()->user;

        $this->actingAs($admin)
            ->put('/admin/peminjaman/'.$pinjam->id, [
                'jumlah' => 2,
                'tgl_pinjam' => '2026-09-01',
                'status' => 'dipinjam',
                'perpanjang' => 0,
            ])
            ->assertRedirect('/admin/peminjaman');

        $this->assertSame(3, $this->buku->fresh()->stok);
    }

    public function test_admin_mengubah_jumlah_dan_status_menyinkronkan_stok(): void
    {
        $pinjam = $this->dipinjam(jumlah: 2); // stok 3
        $admin = Admin::factory()->create()->user;

        // jumlah 2 -> 4 saat masih dipinjam: butuh 2 tambahan
        $this->actingAs($admin)->put('/admin/peminjaman/'.$pinjam->id, [
            'jumlah' => 4, 'tgl_pinjam' => '2026-09-01', 'status' => 'dipinjam', 'perpanjang' => 0,
        ]);
        $this->assertSame(1, $this->buku->fresh()->stok);

        // status -> kembali pada 2026-09-11: stok pulih, denda dihitung ulang (10 hari, 3 hari telat)
        $this->actingAs($admin)->put('/admin/peminjaman/'.$pinjam->id, [
            'jumlah' => 4, 'tgl_pinjam' => '2026-09-01', 'tgl_kembali' => '2026-09-11', 'status' => 'kembali', 'perpanjang' => 0,
        ]);
        $pinjam->refresh();
        $this->assertSame(5, $this->buku->fresh()->stok);
        $this->assertSame(10, $pinjam->lama_pinjam);
        $this->assertSame(6000, $pinjam->denda);
    }

    public function test_admin_menghapus_peminjaman_aktif_mengembalikan_stok(): void
    {
        $pinjam = $this->dipinjam(jumlah: 2);

        $this->actingAs(Admin::factory()->create()->user)
            ->delete('/admin/peminjaman/'.$pinjam->id)
            ->assertRedirect('/admin/peminjaman');

        $this->assertDatabaseMissing('peminjaman', ['id' => $pinjam->id]);
        $this->assertSame(5, $this->buku->fresh()->stok);
    }

    private function pengajuan(int $jumlah): Peminjaman
    {
        return Peminjaman::factory()->konfirmasi()->create([
            'anggota_id' => $this->anggota->nim,
            'buku_id' => $this->buku->id,
            'jumlah' => $jumlah,
            'tgl_pinjam' => '2026-09-01',
        ]);
    }

    /**
     * Peminjaman yang sudah dikonfirmasi: stok buku sudah dikurangi.
     */
    private function dipinjam(int $jumlah): Peminjaman
    {
        $this->buku->decrement('stok', $jumlah);

        return Peminjaman::factory()->dipinjam()->create([
            'anggota_id' => $this->anggota->nim,
            'buku_id' => $this->buku->id,
            'jumlah' => $jumlah,
            'tgl_pinjam' => '2026-09-01',
        ]);
    }
}
