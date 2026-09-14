<?php

namespace App\Console\Commands;

use App\Models\Peminjaman;
use App\Services\PeminjamanService;
use Illuminate\Console\Command;

/**
 * Dijadwalkan harian di routes/console.php setelah pengingat jatuh tempo.
 */
class KedaluwarsaPengajuan extends Command
{
    protected $signature = 'perpus:kedaluwarsa-pengajuan
                            {--dry-run : Hanya menghitung pengajuan yang akan dibatalkan, tanpa mengubah data}';

    protected $description = 'Batalkan pengajuan konfirmasi (termasuk hasil booking) yang tidak diambil dalam batas waktu';

    public function handle(PeminjamanService $service): int
    {
        $dryRun = (bool) $this->option('dry-run');
        $jumlah = $service->kedaluwarsakan($dryRun);

        $this->info(($dryRun ? '[dry-run] ' : '').'Pengajuan kedaluwarsa (lebih dari '.Peminjaman::masaAmbil()." hari tidak diambil): {$jumlah}.");

        return self::SUCCESS;
    }
}
