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
use App\Http\Controllers\PeminjamanController;
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
            Route::resource('/laporan', LaporanController::class);
            Route::get('/laporan/cetak_pdf', [LaporanController::class, 'cetak_pdf'])->name('admin.cetak_pdf');
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
        });
    });

    Route::middleware(['anggota'])->group(function () {
        Route::get('anggota', [AnggotaController::class, 'home']);
        Route::resource('/anggota/buku', BukuAnggotaController::class);
    });

    Route::get('/logout', function () {
        Auth::logout();
        redirect('/');
    });
});
