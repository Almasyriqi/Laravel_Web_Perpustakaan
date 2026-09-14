<?php

use App\Http\Controllers\AdminController;
use App\Http\Controllers\AnggotaController;
use App\Http\Controllers\Auth\PasswordController;
use App\Http\Controllers\BukuAnggotaController;
use App\Http\Controllers\BukuController;
use App\Http\Controllers\CetakController;
use App\Http\Controllers\HomeController;
use App\Http\Controllers\KategoriController;
use App\Http\Controllers\LaporanController;
use App\Http\Controllers\NotifikasiController;
use App\Http\Controllers\PeminjamanAnggotaController;
use App\Http\Controllers\PeminjamanController;
use App\Http\Controllers\PetugasController;
use App\Http\Controllers\TransaksiPetugasController;
use App\Http\Controllers\UlasanController;
use App\Http\Controllers\UserController;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| contains the "web" middleware group. Now create something great!
|
*/

Route::get('/', function () {
    return view('welcome');
});

// Termasuk rute verifikasi email (email/verify, email/resend) dan POST /logout
Auth::routes(['verify' => true]);

Route::middleware(['auth', 'verified'])->group(function () {

    Route::get('/home', [HomeController::class, 'index'])->name('home');

    Route::get('password', [PasswordController::class, 'edit'])->name('user.password.edit');

    Route::patch('password', [PasswordController::class, 'update'])->name('user.password.update');

    Route::resource('/profile', UserController::class);

    // Notifikasi in-app (lonceng navbar) — semua role
    Route::get('/notifikasi', [NotifikasiController::class, 'index'])->name('notifikasi.index');
    Route::get('/notifikasi/ringkas', [NotifikasiController::class, 'ringkas'])->name('notifikasi.ringkas');
    Route::get('/notifikasi/{id}/buka', [NotifikasiController::class, 'buka'])->name('notifikasi.buka');
    Route::post('/notifikasi/baca-semua', [NotifikasiController::class, 'bacaSemua'])->name('notifikasi.baca_semua');

    Route::middleware('role:admin')->group(function () {
        Route::prefix('admin')->group(function () {
            Route::get('/', [AdminController::class, 'home']);

            // CRUD Anggota
            Route::get('/anggota/arsip', [AnggotaController::class, 'arsip']);
            Route::put('/anggota/{id}/pulihkan', [AnggotaController::class, 'pulihkan']);
            Route::get('/anggota/delete/{id}', [AnggotaController::class, 'delete']);
            Route::get('/anggota/cari', [AnggotaController::class, 'search']);
            Route::get('/anggota/{nim}/kartu', [CetakController::class, 'kartuAnggota']);
            Route::resource('/anggota', AnggotaController::class);

            // CRUD Admin
            Route::get('/admin/delete/{id}', [AdminController::class, 'delete']);
            Route::get('/admin/cari', [AdminController::class, 'search']);
            Route::resource('/admin', AdminController::class);

            // CRUD Petugas
            Route::get('/petugas/delete/{id}', [PetugasController::class, 'delete']);
            Route::get('/petugas/cari', [PetugasController::class, 'search']);
            Route::resource('/petugas', PetugasController::class);

            // CRUD Kategori
            Route::get('/kategori/delete/{id}', [KategoriController::class, 'delete']);
            Route::resource('/kategori', KategoriController::class);

            // CRUD Buku
            Route::get('/buku/arsip', [BukuController::class, 'arsip']);
            Route::put('/buku/{id}/pulihkan', [BukuController::class, 'pulihkan']);
            Route::get('/buku/delete/{id}', [BukuController::class, 'delete']);
            Route::delete('/buku/{buku}/ulasan/{ulasan}', [UlasanController::class, 'moderasi']);
            Route::get('/buku/{id}/label', [CetakController::class, 'labelBuku']);
            Route::resource('/buku', BukuController::class);

            // CRUD Peminjaman
            Route::get('/peminjaman/delete/{id}', [PeminjamanController::class, 'delete']);
            Route::resource('/peminjaman', PeminjamanController::class);

            // Laporan (rute rentang bebas didaftarkan sebelum {bulan} agar tidak tertangkap wildcard)
            Route::get('/laporan/rentang', [LaporanController::class, 'show'])->name('admin.laporan.rentang');
            Route::get('/laporan/rentang/pdf', [LaporanController::class, 'cetak_pdf'])->name('admin.cetak_pdf.rentang');
            Route::get('/laporan/rentang/excel', [LaporanController::class, 'excel'])->name('admin.excel.rentang');
            Route::get('/laporan/excel/{bulan}', [LaporanController::class, 'excel'])->name('admin.excel');
            Route::get('/laporan/cetak_pdf/{bulan}', [LaporanController::class, 'cetak_pdf'])->name('admin.cetak_pdf');
            Route::get('/laporan/{bulan}', [LaporanController::class, 'show'])->name('admin.laporan');
        });
    });

    Route::middleware('role:petugas')->group(function () {
        Route::prefix('petugas')->group(function () {
            Route::get('/', [PetugasController::class, 'home']);

            // CRUD Anggota
            Route::get('/anggota/arsip', [AnggotaController::class, 'arsip']);
            Route::put('/anggota/{id}/pulihkan', [AnggotaController::class, 'pulihkan']);
            Route::get('/anggota/delete/{id}', [AnggotaController::class, 'delete']);
            Route::get('/anggota/cari', [AnggotaController::class, 'search']);
            Route::get('/anggota/{nim}/kartu', [CetakController::class, 'kartuAnggota']);
            Route::resource('/anggota', AnggotaController::class);

            // CRUD Kategori
            Route::get('/kategori/delete/{id}', [KategoriController::class, 'delete']);
            Route::resource('/kategori', KategoriController::class);

            // CRUD Buku
            Route::get('/buku/arsip', [BukuController::class, 'arsip']);
            Route::put('/buku/{id}/pulihkan', [BukuController::class, 'pulihkan']);
            Route::get('/buku/delete/{id}', [BukuController::class, 'delete']);
            Route::delete('/buku/{buku}/ulasan/{ulasan}', [UlasanController::class, 'moderasi']);
            Route::get('/buku/{id}/label', [CetakController::class, 'labelBuku']);
            Route::resource('/buku', BukuController::class);

            // Transaksi
            Route::get('/transaksi/delete/{id}', [TransaksiPetugasController::class, 'delete']);
            Route::get('/transaksi/konfirmasi', [TransaksiPetugasController::class, 'konfirmasiPeminjaman']);
            Route::get('/transaksi/confirm/{id}', [TransaksiPetugasController::class, 'confirm']);
            Route::put('/transaksi/konfirmasi/{id}', [TransaksiPetugasController::class, 'konfirmasi']);
            Route::get('/transaksi/perpanjang/{id}', [TransaksiPetugasController::class, 'modalPerpanjang']);
            Route::put('/transaksi/perpanjang/{id}', [TransaksiPetugasController::class, 'perpanjang']);
            Route::get('/transaksi/kembali/{id}', [TransaksiPetugasController::class, 'kembali']);
            Route::resource('/transaksi', TransaksiPetugasController::class);

            // Laporan (lihat catatan di grup admin)
            Route::get('/laporan/rentang', [LaporanController::class, 'show'])->name('petugas.laporan.rentang');
            Route::get('/laporan/rentang/pdf', [LaporanController::class, 'cetak_pdf'])->name('petugas.cetak_pdf.rentang');
            Route::get('/laporan/rentang/excel', [LaporanController::class, 'excel'])->name('petugas.excel.rentang');
            Route::get('/laporan/excel/{bulan}', [LaporanController::class, 'excel'])->name('petugas.excel');
            Route::get('/laporan/cetak_pdf/{bulan}', [LaporanController::class, 'cetak_pdf'])->name('petugas.cetak_pdf');
            Route::get('/laporan/{bulan}', [LaporanController::class, 'show'])->name('petugas.laporan');
        });
    });

    Route::middleware('role:anggota')->group(function () {
        Route::prefix('anggota')->group(function () {
            Route::get('/', [AnggotaController::class, 'home']);
            Route::get('/kartu', [CetakController::class, 'kartuSaya']);
            Route::resource('/buku', BukuAnggotaController::class)->only(['index', 'show']);

            // Rating & ulasan buku
            Route::post('/buku/{buku}/ulasan', [UlasanController::class, 'store']);
            Route::delete('/buku/{buku}/ulasan', [UlasanController::class, 'destroy']);

            Route::get('/pinjam/delete/{id}', [PeminjamanAnggotaController::class, 'delete']);
            Route::get('/pinjam/perpanjang/{id}', [PeminjamanAnggotaController::class, 'modalPerpanjang']);
            Route::put('/perpanjang/{id}', [PeminjamanAnggotaController::class, 'perpanjang']);
            Route::get('/modal/pinjam/{id}', [PeminjamanAnggotaController::class, 'pinjam']);
            Route::post('/peminjaman/{id}', [PeminjamanAnggotaController::class, 'peminjaman']);
            Route::get('/modal/booking/{id}', [PeminjamanAnggotaController::class, 'modalBooking']);
            Route::post('/booking/{id}', [PeminjamanAnggotaController::class, 'booking']);
            Route::resource('/pinjam', PeminjamanAnggotaController::class);
        });
    });
});
