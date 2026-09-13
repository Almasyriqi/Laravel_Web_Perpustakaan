<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('peminjaman', function (Blueprint $table) {
            $table->date('tgl_harus_kembali')->nullable()->after('tgl_pinjam');
            $table->timestamps();
            $table->index('status');
            $table->index('tgl_pinjam');
            $table->index(['anggota_id', 'status']);
        });

        // Isi jatuh tempo untuk transaksi lama yang sudah dikonfirmasi
        $masaPinjam = (int) config('perpustakaan.masa_pinjam', 7);
        $masaPerpanjang = (int) config('perpustakaan.masa_perpanjang', 14);

        DB::table('peminjaman')
            ->where('status', '!=', 'konfirmasi')
            ->whereNull('tgl_harus_kembali')
            ->lazyById(200)
            ->each(function (object $row) use ($masaPinjam, $masaPerpanjang) {
                DB::table('peminjaman')->where('id', $row->id)->update([
                    'tgl_harus_kembali' => Carbon::parse($row->tgl_pinjam)
                        ->addDays($row->perpanjang ? $masaPerpanjang : $masaPinjam)
                        ->toDateString(),
                ]);
            });
    }

    public function down(): void
    {
        Schema::table('peminjaman', function (Blueprint $table) {
            $table->dropIndex(['anggota_id', 'status']);
            $table->dropIndex(['tgl_pinjam']);
            $table->dropIndex(['status']);
            $table->dropTimestamps();
            $table->dropColumn('tgl_harus_kembali');
        });
    }
};
