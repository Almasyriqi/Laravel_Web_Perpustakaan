<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Buku dan anggota tidak dihapus permanen agar riwayat peminjaman tetap
     * utuh; users ikut karena anggota.user_id ber-FK ke users dan akun yang
     * dihapus harus otomatis tidak bisa login.
     */
    public function up(): void
    {
        foreach (['buku', 'anggota', 'users'] as $tabel) {
            Schema::table($tabel, function (Blueprint $table) {
                $table->softDeletes();
            });
        }
    }

    public function down(): void
    {
        foreach (['buku', 'anggota', 'users'] as $tabel) {
            Schema::table($tabel, function (Blueprint $table) {
                $table->dropSoftDeletes();
            });
        }
    }
};
