<?php

namespace Tests\Unit;

use App\Services\PeminjamanService;
use Illuminate\Support\Carbon;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

class PeminjamanServiceTest extends TestCase
{
    /**
     * @return array<string, array{int, bool, int}>
     */
    public static function kasusDenda(): array
    {
        return [
            'tepat 7 hari tanpa perpanjang' => [7, false, 0],
            'hari ke-8 tanpa perpanjang' => [8, false, 2000],
            'hari ke-10 tanpa perpanjang' => [10, false, 6000],
            'hari ke-14 dengan perpanjang' => [14, true, 0],
            'hari ke-20 dengan perpanjang' => [20, true, 12000],
            'dikembalikan hari yang sama' => [0, false, 0],
        ];
    }

    #[DataProvider('kasusDenda')]
    public function test_hitung_denda(int $lamaPinjam, bool $diperpanjang, int $denda): void
    {
        $this->assertSame($denda, (new PeminjamanService)->hitungDenda($lamaPinjam, $diperpanjang));
    }

    public function test_lama_pinjam_dihitung_per_hari_kalender(): void
    {
        $service = new PeminjamanService;

        $this->assertSame(0, $service->hitungLamaPinjam('2026-09-01', Carbon::parse('2026-09-01 23:59')));
        $this->assertSame(1, $service->hitungLamaPinjam('2026-09-01', Carbon::parse('2026-09-02 00:01')));
        $this->assertSame(10, $service->hitungLamaPinjam('2026-09-01', Carbon::parse('2026-09-11')));
        // tanggal kembali sebelum tanggal pinjam (salah input) tidak menghasilkan nilai negatif
        $this->assertSame(0, $service->hitungLamaPinjam('2026-09-10', Carbon::parse('2026-09-01')));
    }
}
