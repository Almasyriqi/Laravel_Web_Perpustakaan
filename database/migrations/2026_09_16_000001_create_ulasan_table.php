<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Rating (1-5) & ulasan buku oleh anggota; satu ulasan per anggota per buku.
 * Soft delete buku/anggota tidak menghapus ulasan (riwayat tetap utuh).
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('ulasan', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('buku_id');
            $table->unsignedBigInteger('anggota_id');
            $table->unsignedTinyInteger('rating');
            $table->text('komentar')->nullable();
            $table->timestamps();

            $table->foreign('buku_id')->references('id')->on('buku');
            $table->foreign('anggota_id')->references('nim')->on('anggota');
            $table->unique(['buku_id', 'anggota_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('ulasan');
    }
};
