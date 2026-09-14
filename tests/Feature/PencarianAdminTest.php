<?php

namespace Tests\Feature;

use App\Http\Controllers\AdminController;
use App\Models\Admin;
use App\Models\Anggota;
use App\Models\Buku;
use App\Models\Kategori;
use App\Models\Peminjaman;
use App\Models\Petugas;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * Pencarian server-side + paginasi di halaman daftar admin/petugas.
 * Sebelumnya paginate(10) tanpa links() menyembunyikan baris ke-11 dst.
 */
class PencarianAdminTest extends TestCase
{
    use RefreshDatabase;

    private function admin()
    {
        return Admin::factory()->create()->user;
    }

    public function test_anggota_di_luar_halaman_pertama_tetap_bisa_dilihat(): void
    {
        $anggota = Anggota::factory()->count(AdminController::PER_HALAMAN + 1)->create();
        // orderBy nim desc → anggota pertama yang dibuat (nim terkecil) ada di halaman 2
        $tertua = $anggota->sortBy('nim')->first();

        $this->actingAs($this->admin())->get('/admin/anggota')
            ->assertOk()
            ->assertDontSee($tertua->user->email)
            ->assertSee('/admin/anggota?page=2', false);

        $this->actingAs($this->admin())->get('/admin/anggota?page=2')
            ->assertOk()
            ->assertSee($tertua->user->email);
    }

    public function test_cari_anggota_mencocokkan_nama_nim_email_jurusan(): void
    {
        $a = Anggota::factory()->create(['jurusan' => 'Teknik Kimia']);
        $a->user->update(['name' => 'Budi Santoso', 'email' => 'budi@polinema.ac.id', 'username' => 'budisan']);
        $b = Anggota::factory()->create(['jurusan' => 'Akuntansi']);
        $b->user->update(['name' => 'Citra Dewi', 'email' => 'citra@polinema.ac.id', 'username' => 'citradewi']); // username tanpa angka agar pencarian NIM tidak ambigu

        $admin = $this->admin();
        $this->actingAs($admin)->get('/admin/anggota?q=budi')->assertOk()->assertSee('Budi Santoso')->assertDontSee('Citra Dewi');
        $this->actingAs($admin)->get('/admin/anggota?q=kimia')->assertOk()->assertSee('Budi Santoso')->assertDontSee('Citra Dewi');
        $this->actingAs($admin)->get('/admin/anggota?q='.$b->nim)->assertOk()->assertSee('Citra Dewi')->assertDontSee('Budi Santoso');
        $this->actingAs($admin)->get('/admin/anggota?q=citra@')->assertOk()->assertSee('Citra Dewi')->assertDontSee('Budi Santoso');

        // Petugas memakai halaman yang sama
        $this->actingAs(Petugas::factory()->create()->user)->get('/petugas/anggota?q=budi')->assertOk()->assertSee('Budi Santoso');

        // Rute lama /cari sudah tidak ada
        $this->actingAs($admin)->get('/admin/anggota/cari?keyword=budi')->assertNotFound();
    }

    public function test_cari_buku_dan_filter_kategori(): void
    {
        $novel = Kategori::factory()->create(['nama' => 'Novel']);
        $teknik = Kategori::factory()->create(['nama' => 'Teknik']);
        Buku::factory()->create(['judul' => 'Laskar Pelangi', 'penulis' => 'Andrea Hirata', 'kategori_id' => $novel->id]);
        Buku::factory()->create(['judul' => 'Mekanika Fluida', 'penulis' => 'Andrea White', 'kategori_id' => $teknik->id]);
        Buku::factory()->create(['judul' => 'Kalkulus', 'penulis' => 'Purcell', 'kategori_id' => $teknik->id]);

        $admin = $this->admin();
        $this->actingAs($admin)->get('/admin/buku?q=andrea')->assertOk()
            ->assertSee('Laskar Pelangi')->assertSee('Mekanika Fluida')->assertDontSee('Kalkulus');
        $this->actingAs($admin)->get('/admin/buku?q=andrea&kategori='.$teknik->id)->assertOk()
            ->assertSee('Mekanika Fluida')->assertDontSee('Laskar Pelangi')->assertDontSee('Kalkulus');
        $this->actingAs($admin)->get('/admin/buku?kategori='.$novel->id)->assertOk()
            ->assertSee('Laskar Pelangi')->assertDontSee('Kalkulus');

        $this->actingAs(Petugas::factory()->create()->user)->get('/petugas/buku?q=kalkulus')->assertOk()
            ->assertSee('Kalkulus')->assertDontSee('Laskar Pelangi');
    }

    public function test_daftar_buku_dipaginasi(): void
    {
        Buku::factory()->count(AdminController::PER_HALAMAN + 2)->create();

        $respons = $this->actingAs($this->admin())->get('/admin/buku')->assertOk()->assertSee('/admin/buku?page=2', false);
        $this->assertSame(AdminController::PER_HALAMAN, substr_count($respons->getContent(), 'data-attr="/admin/buku/delete/'));

        $halaman2 = $this->actingAs($this->admin())->get('/admin/buku?page=2')->assertOk();
        $this->assertSame(2, substr_count($halaman2->getContent(), 'data-attr="/admin/buku/delete/'));
    }

    public function test_cari_dan_filter_status_peminjaman(): void
    {
        $dipinjam = Peminjaman::factory()->dipinjam()->create();
        $kembali = Peminjaman::factory()->kembali()->create();
        $dipinjam->anggota->user->update(['name' => 'Rina Peminjam']);

        $admin = $this->admin();
        $this->actingAs($admin)->get('/admin/peminjaman?status=dipinjam')->assertOk()
            ->assertSee($dipinjam->buku->judul)->assertDontSee($kembali->buku->judul);
        $this->actingAs($admin)->get('/admin/peminjaman?q=rina')->assertOk()
            ->assertSee($dipinjam->buku->judul)->assertDontSee($kembali->buku->judul);
        $this->actingAs($admin)->get('/admin/peminjaman?q='.$kembali->buku->judul)->assertOk()
            ->assertSee($kembali->buku->judul)->assertDontSee($dipinjam->buku->judul);
        // status tidak dikenal diabaikan
        $this->actingAs($admin)->get('/admin/peminjaman?status=xyz')->assertOk()
            ->assertSee($dipinjam->buku->judul)->assertSee($kembali->buku->judul);
    }

    public function test_cari_admin_petugas_dan_transaksi_petugas(): void
    {
        $adminA = Admin::factory()->create();
        $adminA->user->update(['name' => 'Admin Alpha']);
        $adminB = Admin::factory()->create();
        $adminB->user->update(['name' => 'Admin Beta']);
        $petugasX = Petugas::factory()->create();
        $petugasX->user->update(['name' => 'Petugas Xena']);
        $petugasY = Petugas::factory()->create();
        $petugasY->user->update(['name' => 'Petugas Yuda']);

        $aktor = $this->admin(); // nama aktor tampil di navbar, jadi jangan pakai adminA/adminB sebagai pengakses
        $this->actingAs($aktor)->get('/admin/admin?q=beta')->assertOk()->assertSee('Admin Beta')->assertDontSee('Admin Alpha');
        $this->actingAs($aktor)->get('/admin/petugas?q=xena')->assertOk()->assertSee('Petugas Xena')->assertDontSee('Petugas Yuda');

        // Daftar transaksi petugas: hanya anggota dengan pinjaman aktif, bisa dicari
        $aktif = Peminjaman::factory()->dipinjam()->create();
        $aktif->anggota->user->update(['name' => 'Sari Aktif']);
        $menunggu = Peminjaman::factory()->konfirmasi()->create();
        $menunggu->anggota->user->update(['name' => 'Tono Menunggu']);

        $this->actingAs($petugasX->user)->get('/petugas/transaksi')->assertOk()->assertSee('Sari Aktif')->assertDontSee('Tono Menunggu');
        $this->actingAs($petugasX->user)->get('/petugas/transaksi?q=sari')->assertOk()->assertSee('Sari Aktif');
        $this->actingAs($petugasX->user)->get('/petugas/transaksi?q=zzz')->assertOk()->assertDontSee('Sari Aktif');
    }

    public function test_arsip_bisa_dicari(): void
    {
        $arsip = Buku::factory()->create(['judul' => 'Buku Terarsip']);
        $arsip->delete();
        $arsipLain = Buku::factory()->create(['judul' => 'Arsip Lainnya']);
        $arsipLain->delete();

        $this->actingAs($this->admin())->get('/admin/buku/arsip?q=terarsip')->assertOk()
            ->assertSee('Buku Terarsip')->assertDontSee('Arsip Lainnya');
    }
}
