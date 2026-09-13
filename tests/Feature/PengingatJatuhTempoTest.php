<?php

namespace Tests\Feature;

use App\Models\Peminjaman;
use App\Notifications\PemberitahuanTerlambat;
use App\Notifications\PengingatJatuhTempo;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Notification;
use Tests\TestCase;

/**
 * Command perpus:kirim-pengingat: email H-1 jatuh tempo dan teguran keterlambatan.
 */
class PengingatJatuhTempoTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        Notification::fake();
        $this->travelTo('2026-09-15 08:00');
    }

    public function test_pengingat_dikirim_sekali_untuk_yang_jatuh_tempo_besok(): void
    {
        $besok = Peminjaman::factory()->dipinjam()->create(['tgl_pinjam' => '2026-09-09']); // tempo 16-09
        $lusa = Peminjaman::factory()->dipinjam()->create(['tgl_pinjam' => '2026-09-10']); // tempo 17-09
        $sudahKembali = Peminjaman::factory()->kembali()->create(['tgl_pinjam' => '2026-09-09']);
        $belumDikonfirmasi = Peminjaman::factory()->konfirmasi()->create(['tgl_pinjam' => '2026-09-09']);

        $this->artisan('perpus:kirim-pengingat')
            ->expectsOutputToContain('Pengingat jatuh tempo (H-1): 1 email.')
            ->assertSuccessful();

        Notification::assertSentTo($besok->anggota->user, PengingatJatuhTempo::class, function (PengingatJatuhTempo $n) use ($besok) {
            return $n->peminjaman->is($besok);
        });
        Notification::assertNotSentTo($lusa->anggota->user, PengingatJatuhTempo::class);
        Notification::assertNotSentTo($sudahKembali->anggota->user, PengingatJatuhTempo::class);
        Notification::assertNotSentTo($belumDikonfirmasi->anggota->user, PengingatJatuhTempo::class);
        $this->assertNotNull($besok->fresh()->pengingat_dikirim_at);

        // Dijalankan lagi di hari yang sama: tidak ada email ganda
        $this->artisan('perpus:kirim-pengingat')->expectsOutputToContain('Pengingat jatuh tempo (H-1): 0 email.');
        Notification::assertSentTimes(PengingatJatuhTempo::class, 1);
    }

    public function test_teguran_dikirim_sekali_untuk_yang_lewat_tempo(): void
    {
        $terlambat = Peminjaman::factory()->perpanjang()->create(['tgl_pinjam' => '2026-08-20']); // tempo 03-09
        $tepat = Peminjaman::factory()->dipinjam()->create(['tgl_pinjam' => '2026-09-08']); // tempo hari ini
        $sudahDitegur = Peminjaman::factory()->dipinjam()->create([
            'tgl_pinjam' => '2026-09-01', 'teguran_dikirim_at' => '2026-09-09 07:00:00',
        ]);

        $this->artisan('perpus:kirim-pengingat')
            ->expectsOutputToContain('Pemberitahuan keterlambatan: 1 email.')
            ->assertSuccessful();

        Notification::assertSentTo($terlambat->anggota->user, PemberitahuanTerlambat::class);
        Notification::assertNotSentTo($tepat->anggota->user, PemberitahuanTerlambat::class);
        Notification::assertNotSentTo($sudahDitegur->anggota->user, PemberitahuanTerlambat::class);
        $this->assertNotNull($terlambat->fresh()->teguran_dikirim_at);

        $this->artisan('perpus:kirim-pengingat');
        Notification::assertSentTimes(PemberitahuanTerlambat::class, 1);
    }

    public function test_dry_run_tidak_mengirim_dan_tidak_menandai(): void
    {
        $besok = Peminjaman::factory()->dipinjam()->create(['tgl_pinjam' => '2026-09-09']);
        $terlambat = Peminjaman::factory()->dipinjam()->create(['tgl_pinjam' => '2026-09-01']);

        $this->artisan('perpus:kirim-pengingat --dry-run')
            ->expectsOutputToContain('[dry-run] Pengingat jatuh tempo (H-1): 1 email.')
            ->expectsOutputToContain('[dry-run] Pemberitahuan keterlambatan: 1 email.')
            ->assertSuccessful();

        Notification::assertNothingSent();
        $this->assertNull($besok->fresh()->pengingat_dikirim_at);
        $this->assertNull($terlambat->fresh()->teguran_dikirim_at);
    }

    public function test_hari_sebelum_dibaca_dari_config(): void
    {
        config(['perpustakaan.pengingat_hari_sebelum' => 3]);
        $tigaHari = Peminjaman::factory()->dipinjam()->create(['tgl_pinjam' => '2026-09-11']); // tempo 18-09
        $besok = Peminjaman::factory()->dipinjam()->create(['tgl_pinjam' => '2026-09-09']); // tempo 16-09

        $this->artisan('perpus:kirim-pengingat')->expectsOutputToContain('(H-3): 1 email.');

        Notification::assertSentTo($tigaHari->anggota->user, PengingatJatuhTempo::class);
        Notification::assertNotSentTo($besok->anggota->user, PengingatJatuhTempo::class);
    }

    public function test_isi_email_dan_notifikasi_masuk_antrean(): void
    {
        $besok = Peminjaman::factory()->dipinjam()->create(['tgl_pinjam' => '2026-09-09']);
        $terlambat = Peminjaman::factory()->dipinjam()->create(['tgl_pinjam' => '2026-09-01']); // tempo 08-09, 7 hari

        $pengingat = new PengingatJatuhTempo($besok);
        $teguran = new PemberitahuanTerlambat($terlambat);

        $this->assertInstanceOf(ShouldQueue::class, $pengingat);
        $this->assertInstanceOf(ShouldQueue::class, $teguran);

        $mailPengingat = $pengingat->toMail($besok->anggota->user);
        $this->assertStringContainsString($besok->buku->judul, $mailPengingat->subject);
        $this->assertStringContainsString('16-09-2026', $mailPengingat->subject);
        $this->assertSame(url('/anggota/pinjam'), $mailPengingat->actionUrl);

        $mailTeguran = $teguran->toMail($terlambat->anggota->user);
        $this->assertStringContainsString('7 hari', implode(' ', $mailTeguran->introLines));
        $this->assertStringContainsString(number_format(7 * config('perpustakaan.denda_per_hari'), 0, ',', '.'), implode(' ', $mailTeguran->introLines));
    }
}
