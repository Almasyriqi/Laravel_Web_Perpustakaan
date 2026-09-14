<?php

namespace App\Console\Commands;

use App\Services\PengingatService;
use Illuminate\Console\Command;

/**
 * Dijadwalkan harian di routes/console.php; bisa juga dijalankan manual.
 */
class KirimPengingatJatuhTempo extends Command
{
    protected $signature = 'perpus:kirim-pengingat
                            {--dry-run : Hanya menghitung yang akan dikirim, tanpa mengirim email atau menandai}';

    protected $description = 'Kirim email pengingat jatuh tempo (H-N) dan pemberitahuan keterlambatan ke anggota';

    public function handle(PengingatService $service): int
    {
        $dryRun = (bool) $this->option('dry-run');
        $awalan = $dryRun ? '[dry-run] ' : '';

        $pengingat = $service->kirimPengingat($dryRun);
        $this->info("{$awalan}Pengingat jatuh tempo (H-{$service->hariSebelum()}): {$pengingat} email.");

        $teguran = $service->kirimTeguran($dryRun);
        $this->info("{$awalan}Pemberitahuan keterlambatan: {$teguran} email.");

        return self::SUCCESS;
    }
}
