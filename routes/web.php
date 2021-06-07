<?php

use App\Http\Controllers\AdminController;
use App\Http\Controllers\AnggotaController;
use App\Http\Controllers\PetugasController;
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

Auth::routes();

Route::middleware(['auth'])->group(function () {
 
    Route::get('/home', [App\Http\Controllers\HomeController::class, 'index'])->name('home');
 
    Route::middleware(['admin'])->group(function () {
        Route::prefix('admin')->group(function () {
            Route::get('/', [AdminController::class, 'home']);
            Route::get('/anggota/delete/{id}', [AnggotaController::class, 'delete']);
            Route::get('/anggota/cari', [AnggotaController::class, 'search']);
            Route::resource('/anggota', AnggotaController::class);
            Route::get('/admin/delete/{id}', [AdminController::class, 'delete']);
            Route::get('/admin/cari', [AdminController::class, 'search']);
            Route::resource('/admin', AdminController::class);
        });
    });
 
    Route::middleware(['petugas'])->group(function () {
        Route::get('petugas', [PetugasController::class, 'home']);
    });

    Route::middleware(['anggota'])->group(function () {
        Route::get('anggota', [AnggotaController::class, 'home']);
    });
 
    Route::get('/logout', function() {
        Auth::logout();
        redirect('/');
    });
 
});
