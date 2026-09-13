<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Penanda email yang sudah dikirim, supaya command perpus:kirim-pengingat
 * idempoten (aman dijalankan berulang pada hari yang sama).
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('peminjaman', function (Blueprint $table) {
            $table->timestamp('pengingat_dikirim_at')->nullable()->after('denda');
            $table->timestamp('teguran_dikirim_at')->nullable()->after('pengingat_dikirim_at');
        });
    }

    public function down(): void
    {
        Schema::table('peminjaman', function (Blueprint $table) {
            $table->dropColumn(['pengingat_dikirim_at', 'teguran_dikirim_at']);
        });
    }
};
