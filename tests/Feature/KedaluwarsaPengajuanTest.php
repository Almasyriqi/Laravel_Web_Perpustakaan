<?php

namespace Tests\Feature;

use App\Models\Anggota;
use App\Models\Buku;
use App\Models\Peminjaman;
use App\Models\Petugas;
use App\Notifications\BukuTersedia;
use App\Notifications\PengajuanKedaluwarsa;
use App\Services\PeminjamanService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Notification;
use Tests\TestCase;

/**
 * Command perpus:kedaluwarsa-pengajuan: pengajuan konfirmasi yang tidak
 * diambil dalam masa_ambil_pengajuan hari dibatalkan otomatis.
 */
class KedaluwarsaPengajuanTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        Notification::fake();
        $this->travelTo('2026-09-15 07:05'); // batas default 3 hari: diajukan <= 11-09 sudah lewat
    }

    public function test_pengajuan_lewat_batas_dibatalkan_dan_anggota_diberi_tahu(): void
    {
        $basi = Peminjaman::factory()->konfirmasi()->create(['tgl_pinjam' => '2026-09-11']);
        $tepatBatas = Peminjaman::factory()->konfirmasi()->create(['tgl_pinjam' => '2026-09-12']); // batas 15-09 = hari ini, masih boleh
        $baru = Peminjaman::factory()->konfirmasi()->create(['tgl_pinjam' => '2026-09-14']);
        $booking = Peminjaman::factory()->create(['status' => 'booking', 'tgl_pinjam' => '2026-09-01']);
        $dipinjam = Peminjaman::factory()->dipinjam()->create(['tgl_pinjam' => '2026-09-01']);
        $stokAwal = $basi->buku->stok;

        $this->artisan('perpus:kedaluwarsa-pengajuan')
            ->expectsOutputToContain('Pengajuan kedaluwarsa (lebih dari 3 hari tidak diambil): 1.')
            ->assertSuccessful();

        $this->assertDatabaseMissing('peminjaman', ['id' => $basi->id]);
        foreach ([$tepatBatas, $baru, $booking, $dipinjam] as $tetap) {
            $this->assertDatabaseHas('peminjaman', ['id' => $tetap->id]);
        }
        $this->assertSame($stokAwal, $basi->buku->fresh()->stok, 'pengajuan tidak pernah menahan stok');

        Notification::assertSentTo($basi->anggota->user, PengajuanKedaluwarsa::class, function (PengajuanKedaluwarsa $n) use ($basi) {
            return $n->judulBuku === $basi->buku->judul && $n->batasAmbil === '14-09-2026';
        });
        Notification::assertNotSentTo($baru->anggota->user, PengajuanKedaluwarsa::class);

        // Dijalankan lagi: tidak ada yang tersisa
        $this->artisan('perpus:kedaluwarsa-pengajuan')->expectsOutputToContain(': 0.');
    }

    public function test_setelah_kedaluwarsa_booking_berikutnya_naik(): void
    {
        $buku = Buku::factory()->create(['stok' => 1]);
        $basi = Peminjaman::factory()->konfirmasi()->create(['buku_id' => $buku->id, 'tgl_pinjam' => '2026-09-10']);
        $pengantre = Anggota::factory()->create();
        $booking = Peminjaman::factory()->create([
            'status' => 'booking', 'buku_id' => $buku->id, 'anggota_id' => $pengantre->nim, 'tgl_pinjam' => '2026-09-12',
        ]);

        $this->artisan('perpus:kedaluwarsa-pengajuan');

        $this->assertDatabaseMissing('peminjaman', ['id' => $basi->id]);
        $booking->refresh();
        $this->assertSame('konfirmasi', $booking->status);
        $this->assertSame('2026-09-15', $booking->tgl_pinjam, 'batas ambil booking yang naik dihitung dari hari promosi');
        Notification::assertSentTo($pengantre->user, BukuTersedia::class);
    }

    public function test_dry_run_tidak_mengubah_data(): void
    {
        $basi = Peminjaman::factory()->konfirmasi()->create(['tgl_pinjam' => '2026-09-01']);

        $this->artisan('perpus:kedaluwarsa-pengajuan --dry-run')
            ->expectsOutputToContain('[dry-run] Pengajuan kedaluwarsa (lebih dari 3 hari tidak diambil): 1.');

        $this->assertDatabaseHas('peminjaman', ['id' => $basi->id]);
        Notification::assertNothingSent();
    }

    public function test_batas_ambil_mengikuti_config(): void
    {
        config(['perpustakaan.masa_ambil_pengajuan' => 7]);
        $pengajuan = Peminjaman::factory()->konfirmasi()->create(['tgl_pinjam' => '2026-09-10']);

        $this->assertSame('2026-09-17', $pengajuan->batasAmbil()->toDateString());
        $this->assertNull(Peminjaman::factory()->dipinjam()->create()->batasAmbil());
        $this->assertNull(Peminjaman::factory()->create(['status' => 'booking'])->batasAmbil());

        $this->artisan('perpus:kedaluwarsa-pengajuan')->expectsOutputToContain('(lebih dari 7 hari tidak diambil): 0.');
        $this->assertDatabaseHas('peminjaman', ['id' => $pengajuan->id]);
    }

    public function test_halaman_konfirmasi_dan_riwayat_menampilkan_batas_ambil(): void
    {
        $pengajuan = Peminjaman::factory()->konfirmasi()->create(['tgl_pinjam' => '2026-09-12']); // batas 15-09 (hari ini)

        $this->actingAs(Petugas::factory()->create()->user)->get('/petugas/transaksi/konfirmasi')
            ->assertOk()
            ->assertSee('Ambil sebelum')
            ->assertSee('15-09-2026 (hari ini)');

        $this->actingAs($pengajuan->anggota->user)->get('/anggota/pinjam')
            ->assertOk()
            ->assertSee('ambil sebelum <b>15-09-2026</b>', false);
    }

    public function test_service_kedaluwarsakan_mengembalikan_jumlah(): void
    {
        Peminjaman::factory()->count(2)->konfirmasi()->create(['tgl_pinjam' => '2026-09-01']);
        Peminjaman::factory()->konfirmasi()->create(['tgl_pinjam' => '2026-09-14']);

        $this->assertSame(2, app(PeminjamanService::class)->kedaluwarsakan(dryRun: true));
        $this->assertSame(2, app(PeminjamanService::class)->kedaluwarsakan());
        $this->assertSame(1, Peminjaman::where('status', 'konfirmasi')->count());
    }
}
