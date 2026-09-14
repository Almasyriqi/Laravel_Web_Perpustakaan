<?php

namespace Tests\Feature;

use App\Models\Admin;
use App\Models\Anggota;
use App\Models\Buku;
use App\Models\Peminjaman;
use App\Models\Petugas;
use App\Notifications\BukuDikembalikan;
use App\Notifications\BukuTersedia;
use App\Notifications\PengajuanBaru;
use App\Notifications\PengajuanDisetujui;
use App\Notifications\PengingatJatuhTempo;
use App\Services\PeminjamanService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * Notifikasi in-app (database channel) + lonceng navbar. Sengaja tanpa
 * Notification::fake() supaya baris di tabel notifications benar-benar dibuat
 * (queue sync, mail array — lihat phpunit.xml).
 */
class NotifikasiTest extends TestCase
{
    use RefreshDatabase;

    private Anggota $anggota;

    private Petugas $petugas;

    private Admin $admin;

    private Buku $buku;

    protected function setUp(): void
    {
        parent::setUp();

        $this->travelTo('2026-09-15 09:00');
        $this->anggota = Anggota::factory()->create();
        $this->petugas = Petugas::factory()->create();
        $this->admin = Admin::factory()->create();
        $this->buku = Buku::factory()->create(['judul' => 'Buku Notif', 'stok' => 3]);
    }

    public function test_pengajuan_dan_booking_memberi_tahu_petugas_dan_admin(): void
    {
        $this->actingAs($this->anggota->user)->post('/anggota/peminjaman/'.$this->buku->id, ['jumlah' => 1]);

        foreach ([$this->petugas->user, $this->admin->user] as $penerima) {
            $this->assertDatabaseHas('notifications', [
                'notifiable_id' => $penerima->id, 'notifiable_type' => $penerima::class, 'type' => PengajuanBaru::class,
            ]);
        }
        $this->assertDatabaseMissing('notifications', ['notifiable_id' => $this->anggota->user->id, 'type' => PengajuanBaru::class]);

        $data = $this->petugas->user->notifications()->first()->data;
        $this->assertSame('Pengajuan peminjaman baru', $data['judul']);
        $this->assertStringContainsString('Buku Notif', $data['pesan']);
        $this->assertSame(url('/petugas/transaksi/konfirmasi'), $data['url']);
        $this->assertSame(url('/admin/peminjaman'), $this->admin->user->notifications()->first()->data['url']);

        // Booking buku habis juga diumumkan
        $habis = Buku::factory()->create(['stok' => 0]);
        $this->actingAs($this->anggota->user)->post('/anggota/booking/'.$habis->id);
        $this->assertSame(2, $this->petugas->user->unreadNotifications()->count());
        $this->assertTrue($this->petugas->user->notifications->contains(fn ($n) => $n->data['judul'] === 'Booking baru'));
    }

    public function test_konfirmasi_dan_pengembalian_memberi_tahu_anggota(): void
    {
        $pinjam = Peminjaman::factory()->konfirmasi()->create(['anggota_id' => $this->anggota->nim, 'buku_id' => $this->buku->id]);

        $this->actingAs($this->petugas->user)->put('/petugas/transaksi/konfirmasi/'.$pinjam->id);

        $disetujui = $this->anggota->user->notifications()->where('type', PengajuanDisetujui::class)->first();
        $this->assertNotNull($disetujui);
        $this->assertStringContainsString('22-09-2026', $disetujui->data['pesan']);

        // Dikembalikan terlambat 3 hari → denda 6.000
        $this->travelTo('2026-09-25 09:00');
        $this->actingAs($this->petugas->user)->put('/petugas/transaksi/'.$pinjam->id);

        $kembali = $this->anggota->user->notifications()->where('type', BukuDikembalikan::class)->first();
        $this->assertNotNull($kembali);
        $this->assertSame('Buku dikembalikan — ada denda', $kembali->data['judul']);
        $this->assertStringContainsString('Rp 6.000', $kembali->data['pesan']);
        $this->assertSame('warning', $kembali->data['warna']);
    }

    public function test_notifikasi_email_lama_juga_tersimpan_in_app(): void
    {
        $pinjam = Peminjaman::factory()->dipinjam()->create(['anggota_id' => $this->anggota->nim, 'tgl_pinjam' => '2026-09-09']);

        $this->artisan('perpus:kirim-pengingat');

        $this->assertDatabaseHas('notifications', ['notifiable_id' => $this->anggota->user->id, 'type' => PengingatJatuhTempo::class]);
        $this->assertSame('Pengingat jatuh tempo', $this->anggota->user->notifications()->first()->data['judul']);

        // Promosi booking → BukuTersedia tersimpan
        $habis = Buku::factory()->create(['stok' => 0]);
        $pembooking = Anggota::factory()->create();
        app(PeminjamanService::class)->booking($pembooking, $habis);
        app(PeminjamanService::class)->kembalikan(Peminjaman::factory()->dipinjam()->create(['buku_id' => $habis->id]));

        $this->assertDatabaseHas('notifications', ['notifiable_id' => $pembooking->user->id, 'type' => BukuTersedia::class]);
    }

    public function test_endpoint_ringkas_untuk_lonceng_navbar(): void
    {
        $user = $this->anggota->user;

        $this->actingAs($user)->getJson('/notifikasi/ringkas')
            ->assertOk()
            ->assertJson(['label' => 0, 'icon_color' => 'secondary'])
            ->assertJsonFragment(['label_color' => 'danger']);

        Peminjaman::factory()->count(3)->konfirmasi()->create(['anggota_id' => $this->anggota->nim])
            ->each(fn ($p) => app(PeminjamanService::class)->konfirmasi($p));

        $respons = $this->actingAs($user)->getJson('/notifikasi/ringkas')->assertOk()->assertJson(['label' => 3, 'icon_color' => 'warning']);
        $this->assertStringContainsString('3 belum dibaca', $respons->json('dropdown'));
        $this->assertStringContainsString('Peminjaman disetujui', $respons->json('dropdown'));
        $this->assertSame(3, substr_count($respons->json('dropdown'), '/buka"'));
    }

    public function test_halaman_daftar_buka_dan_tandai_semua_dibaca(): void
    {
        $user = $this->anggota->user;
        $pinjam = Peminjaman::factory()->konfirmasi()->create(['anggota_id' => $this->anggota->nim, 'buku_id' => $this->buku->id]);
        app(PeminjamanService::class)->konfirmasi($pinjam);
        $notif = $user->notifications()->first();

        $this->actingAs($user)->get('/notifikasi')
            ->assertOk()
            ->assertSee('1 belum dibaca')
            ->assertSee('Peminjaman disetujui')
            ->assertSee('Tandai semua sudah dibaca');

        $this->actingAs($user)->get('/notifikasi/'.$notif->id.'/buka')->assertRedirect(url('/anggota/pinjam'));
        $this->assertNotNull($notif->fresh()->read_at);

        app(PeminjamanService::class)->konfirmasi(Peminjaman::factory()->konfirmasi()->create(['anggota_id' => $this->anggota->nim]));
        $this->assertSame(1, $user->unreadNotifications()->count());

        $this->actingAs($user)->post('/notifikasi/baca-semua')->assertRedirect('/notifikasi');
        $this->assertSame(0, $user->unreadNotifications()->count());
        $this->actingAs($user)->get('/notifikasi')->assertOk()->assertDontSee('Tandai semua sudah dibaca');
    }

    public function test_notifikasi_milik_user_lain_tidak_bisa_dibuka(): void
    {
        $pinjam = Peminjaman::factory()->konfirmasi()->create(['anggota_id' => $this->anggota->nim]);
        app(PeminjamanService::class)->konfirmasi($pinjam);
        $notif = $this->anggota->user->notifications()->first();

        $orangLain = Anggota::factory()->create()->user;
        $this->actingAs($orangLain)->get('/notifikasi/'.$notif->id.'/buka')->assertNotFound();
        $this->assertNull($notif->fresh()->read_at);

        // Petugas melihat notifikasi pengajuan baru dari alur normal (lewat service)
        $this->actingAs($this->anggota->user)->post('/anggota/peminjaman/'.$this->buku->id, ['jumlah' => 1]);
        $this->actingAs($this->petugas->user)->get('/notifikasi')->assertOk()->assertSee('Pengajuan peminjaman baru');
    }
}
