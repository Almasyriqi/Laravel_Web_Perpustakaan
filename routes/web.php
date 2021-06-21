<?php

use App\Http\Controllers\AdminController;
use App\Http\Controllers\AnggotaController;
use App\Http\Controllers\Auth\PasswordController;
use App\Http\Controllers\PetugasController;
use App\Http\Controllers\KategoriController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\BukuController;
use App\Http\Controllers\LaporanController;
use App\Http\Controllers\BukuAnggotaController;
use App\Http\Controllers\PeminjamanAnggotaController;
use App\Http\Controllers\PeminjamanController;
use App\Http\Controllers\TransaksiPetugasController;
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

Auth::routes(['verify' => true]);

Route::middleware(['auth', 'verified'])->group(function () {

    Route::get('/home', [App\Http\Controllers\HomeController::class, 'index'])->name('home');

    Route::get('password', [PasswordController::class, 'edit'])->name('user.password.edit');

    Route::patch('password', [PasswordController::class, 'update'])->name('user.password.update');

    Route::resource('/profile', UserController::class);

    Route::middleware(['admin'])->group(function () {
        Route::prefix('admin')->group(function () {
            Route::get('/', [AdminController::class, 'home']);

            // CRUD Anggota
            Route::get('/anggota/delete/{id}', [AnggotaController::class, 'delete']);
            Route::get('/anggota/cari', [AnggotaController::class, 'search']);
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
            Route::get('/buku/delete/{id}', [BukuController::class, 'delete']);
            Route::resource('/buku', BukuController::class);

            // CRUD Peminjaman
            Route::get('/peminjaman/delete/{id}', [PeminjamanController::class, 'delete']);
            Route::resource('/peminjaman', PeminjamanController::class);

            // Laporan
            Route::get('/laporan/cetak_pdf/{id}', [LaporanController::class, 'cetak_pdf'])->name('admin.cetak_pdf');
            Route::resource('/laporan', LaporanController::class);
        });
    });

    Route::middleware(['petugas'])->group(function () {
        Route::prefix('petugas')->group(function () {
            Route::get('/', [PetugasController::class, 'home']); 

            // CRUD Anggota
            Route::get('/anggota/delete/{id}', [AnggotaController::class, 'delete']);
            Route::get('/anggota/cari', [AnggotaController::class, 'search']);
            Route::resource('/anggota', AnggotaController::class);

            // CRUD Kategori
            Route::get('/kategori/delete/{id}', [KategoriController::class, 'delete']);
            Route::resource('/kategori', KategoriController::class);

            // CRUD Buku
            Route::get('/buku/delete/{id}', [BukuController::class, 'delete']);
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

            // Laporan
            Route::get('/laporan/cetak_pdf/{id}', [LaporanController::class, 'cetak_pdf'])->name('petugas.cetak_pdf');
            Route::resource('/laporan', LaporanController::class);
        });
    });

    Route::middleware(['anggota'])->group(function () {
        Route::prefix('anggota')->group(function () {
            Route::get('/', [AnggotaController::class, 'home']);
            Route::resource('/buku', BukuAnggotaController::class);
    
            Route::get('/pinjam/delete/{id}', [PeminjamanAnggotaController::class, 'delete']);
            Route::get('/pinjam/perpanjang/{id}', [PeminjamanAnggotaController::class, 'modalPerpanjang']);
            Route::put('/perpanjang/{id}', [PeminjamanAnggotaController::class, 'perpanjang']);
            Route::get('/modal/pinjam/{id}', [PeminjamanAnggotaController::class, 'pinjam']);
            Route::post('/peminjaman/{id}', [PeminjamanAnggotaController::class, 'peminjaman']);
            Route::resource('/pinjam', PeminjamanAnggotaController::class); 
        });
    });

    Route::get('/logout', function () {
        Auth::logout();
        redirect('/');
    });
});
