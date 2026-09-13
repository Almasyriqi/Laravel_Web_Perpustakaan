<?php

namespace Tests\Feature;

use App\Models\Admin;
use App\Models\Anggota;
use App\Models\Buku;
use App\Models\Peminjaman;
use App\Models\Petugas;
use App\Notifications\BukuTersedia;
use App\Services\PeminjamanService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Notification;
use Tests\TestCase;

/**
 * Booking buku yang stoknya habis: antrean naik otomatis ke konfirmasi saat stok kembali.
 */
class BookingTest extends TestCase
{
    use RefreshDatabase;

    private Buku $buku;

    protected function setUp(): void
    {
        parent::setUp();

        Notification::fake();
        $this->travelTo('2026-09-15');
        $this->buku = Buku::factory()->create(['judul' => 'Buku Langka', 'stok' => 0]);
    }

    private function booking(Anggota $anggota, ?Buku $buku = null): Peminjaman
    {
        return app(PeminjamanService::class)->booking($anggota, $buku ?? $this->buku);
    }

    public function test_booking_hanya_saat_stok_habis(): void
    {
        $anggota = Anggota::factory()->create();
        $tersedia = Buku::factory()->create(['stok' => 2]);

        $this->actingAs($anggota->user)->from('/anggota/buku/'.$tersedia->id)
            ->post('/anggota/booking/'.$tersedia->id)
            ->assertRedirect('/anggota/buku/'.$tersedia->id)
            ->assertSessionHasErrors('booking');

        $this->actingAs($anggota->user)
            ->post('/anggota/booking/'.$this->buku->id)
            ->assertRedirect('/anggota/pinjam')
            ->assertSessionHas('success');

        $this->assertDatabaseHas('peminjaman', [
            'anggota_id' => $anggota->nim, 'buku_id' => $this->buku->id, 'status' => 'booking',
            'jumlah' => 1, 'tgl_harus_kembali' => null,
        ]);
        $this->assertSame(0, $this->buku->fresh()->stok, 'booking tidak menyentuh stok');
    }

    public function test_tombol_booking_menggantikan_pinjam_saat_stok_habis(): void
    {
        $anggota = Anggota::factory()->create();

        $this->actingAs($anggota->user)->get('/anggota/buku')
            ->assertOk()
            ->assertSee('/anggota/modal/booking/'.$this->buku->id)
            ->assertDontSee('/anggota/modal/pinjam/'.$this->buku->id);

        $this->actingAs($anggota->user)->get('/anggota/modal/booking/'.$this->buku->id)
            ->assertOk()
            ->assertSee('/anggota/booking/'.$this->buku->id);
    }

    public function test_anggota_tidak_bisa_booking_ganda_untuk_buku_yang_sama(): void
    {
        $anggota = Anggota::factory()->create();
        $this->booking($anggota);

        $this->actingAs($anggota->user)->from('/anggota/buku')
            ->post('/anggota/booking/'.$this->buku->id)
            ->assertSessionHasErrors('booking');

        $this->assertSame(1, Peminjaman::where('anggota_id', $anggota->nim)->count());
    }

    public function test_pengembalian_mempromosikan_booking_tertua_dan_mengirim_email(): void
    {
        $pertama = Anggota::factory()->create();
        $kedua = Anggota::factory()->create();
        $bookingPertama = $this->booking($pertama);
        $this->travelTo('2026-09-16');
        $bookingKedua = $this->booking($kedua);

        // Satu eksemplar sedang dipinjam anggota lain, lalu dikembalikan
        $dipinjam = Peminjaman::factory()->dipinjam()->create(['buku_id' => $this->buku->id, 'tgl_pinjam' => '2026-09-10']);
        $this->travelTo('2026-09-20');
        app(PeminjamanService::class)->kembalikan($dipinjam);

        $this->assertSame(1, $this->buku->fresh()->stok);

        $bookingPertama->refresh();
        $this->assertSame('konfirmasi', $bookingPertama->status);
        $this->assertSame('2026-09-20', $bookingPertama->tgl_pinjam, 'tgl_pinjam diset ke tanggal promosi agar jatuh tempo dihitung dari situ');
        $this->assertNull($bookingPertama->tgl_harus_kembali);

        $this->assertSame('booking', $bookingKedua->fresh()->status, 'hanya sebanyak stok yang dipromosikan');

        Notification::assertSentTo($pertama->user, BukuTersedia::class, fn (BukuTersedia $n) => $n->peminjaman->is($bookingPertama));
        Notification::assertNotSentTo($kedua->user, BukuTersedia::class);
    }

    public function test_promosi_sebanyak_stok_yang_kembali(): void
    {
        $anggota = Anggota::factory()->count(3)->create();
        foreach ($anggota as $a) {
            $this->booking($a);
        }

        $dipinjam = Peminjaman::factory()->dipinjam()->create(['buku_id' => $this->buku->id, 'jumlah' => 2]);
        app(PeminjamanService::class)->kembalikan($dipinjam);

        $this->assertSame(2, Peminjaman::where('buku_id', $this->buku->id)->where('status', 'konfirmasi')->count());
        $this->assertSame(1, Peminjaman::where('buku_id', $this->buku->id)->where('status', 'booking')->count());
        Notification::assertSentTimes(BukuTersedia::class, 2);
    }

    public function test_konfirmasi_petugas_setelah_promosi_mengurangi_stok(): void
    {
        $anggota = Anggota::factory()->create();
        $booking = $this->booking($anggota);
        $dipinjam = Peminjaman::factory()->dipinjam()->create(['buku_id' => $this->buku->id]);
        app(PeminjamanService::class)->kembalikan($dipinjam);

        $this->actingAs(Petugas::factory()->create()->user)
            ->put('/petugas/transaksi/konfirmasi/'.$booking->id)
            ->assertRedirect('/petugas/transaksi/konfirmasi');

        $booking->refresh();
        $this->assertSame('dipinjam', $booking->status);
        $this->assertSame('2026-09-22', $booking->tgl_harus_kembali);
        $this->assertSame(0, $this->buku->fresh()->stok);
    }

    public function test_booking_tidak_bisa_dikonfirmasi_sebelum_stok_ada(): void
    {
        $booking = $this->booking(Anggota::factory()->create());

        $this->actingAs(Petugas::factory()->create()->user)->from('/petugas/transaksi/konfirmasi')
            ->put('/petugas/transaksi/konfirmasi/'.$booking->id)
            ->assertRedirect('/petugas/transaksi/konfirmasi')
            ->assertSessionHasErrors('status');

        $this->assertSame('booking', $booking->fresh()->status);
    }

    public function test_anggota_dan_petugas_dapat_membatalkan_booking(): void
    {
        $anggota = Anggota::factory()->create();
        $booking = $this->booking($anggota);

        $this->actingAs($anggota->user)->get('/anggota/pinjam')
            ->assertOk()->assertSee('/anggota/pinjam/delete/'.$booking->id);

        $this->actingAs($anggota->user)->delete('/anggota/pinjam/'.$booking->id)->assertRedirect('/anggota/pinjam');
        $this->assertDatabaseMissing('peminjaman', ['id' => $booking->id]);

        $bookingLain = $this->booking(Anggota::factory()->create());
        $this->actingAs(Petugas::factory()->create()->user)->get('/petugas/transaksi/konfirmasi')
            ->assertOk()->assertSee('Antrean Booking (1)')->assertSee($bookingLain->anggota->user->name);

        $this->actingAs(Petugas::factory()->create()->user)
            ->delete('/petugas/transaksi/'.$bookingLain->id)
            ->assertRedirect('/petugas/transaksi/konfirmasi');
        $this->assertDatabaseMissing('peminjaman', ['id' => $bookingLain->id]);
    }

    public function test_hapus_oleh_admin_yang_mengembalikan_stok_juga_memproses_antrean(): void
    {
        $anggota = Anggota::factory()->create();
        $booking = $this->booking($anggota);
        $dipinjam = Peminjaman::factory()->dipinjam()->create(['buku_id' => $this->buku->id]);

        $this->actingAs(Admin::factory()->create()->user)
            ->delete('/admin/peminjaman/'.$dipinjam->id)
            ->assertRedirect();

        $this->assertSame(1, $this->buku->fresh()->stok);
        $this->assertSame('konfirmasi', $booking->fresh()->status);
        Notification::assertSentTo($anggota->user, BukuTersedia::class);
    }

    public function test_admin_mengubah_status_ke_booking_melepas_stok_lalu_antrean_diproses(): void
    {
        $pinjam = Peminjaman::factory()->dipinjam()->create(['buku_id' => $this->buku->id, 'jumlah' => 1]);
        $lebihDulu = $this->booking(Anggota::factory()->create());

        $this->actingAs(Admin::factory()->create()->user)
            ->put('/admin/peminjaman/'.$pinjam->id, [
                'jumlah' => 1, 'tgl_pinjam' => '2026-09-15', 'status' => 'booking',
            ])
            ->assertSessionHasNoErrors();

        // Stok kembali (buku tidak lagi ditahan) dan langsung dipakai booking tertua di antrean
        $this->assertSame(1, $this->buku->fresh()->stok);
        $this->assertSame('konfirmasi', $lebihDulu->fresh()->status);

        $pinjam->refresh();
        $this->assertSame('booking', $pinjam->status);
        $this->assertNull($pinjam->tgl_harus_kembali);
    }
}
