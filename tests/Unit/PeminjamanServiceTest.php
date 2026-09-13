<?php

namespace Tests\Unit;

use App\Services\PeminjamanService;
use Illuminate\Support\Carbon;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

class PeminjamanServiceTest extends TestCase
{
    /** Aturan default seperti di config/perpustakaan.php (tanpa container). */
    private const ATURAN = [
        'masa_pinjam' => 7,
        'masa_perpanjang' => 14,
        'maks_perpanjang' => 1,
        'denda_per_hari' => 2000,
    ];

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
        $this->assertSame($denda, (new PeminjamanService(self::ATURAN))->hitungDenda($lamaPinjam, $diperpanjang));
    }

    public function test_tarif_dan_masa_pinjam_mengikuti_aturan_yang_diberikan(): void
    {
        $service = new PeminjamanService([
            'masa_pinjam' => 3,
            'masa_perpanjang' => 6,
            'maks_perpanjang' => 1,
            'denda_per_hari' => 5000,
        ]);

        $this->assertSame(0, $service->hitungDenda(3, false));
        $this->assertSame(10000, $service->hitungDenda(5, false));
        $this->assertSame(5000, $service->hitungDenda(7, true));
        $this->assertSame(3, $service->masaPinjam());
        $this->assertSame(5000, $service->dendaPerHari());
    }

    public function test_lama_pinjam_dihitung_per_hari_kalender(): void
    {
        $service = new PeminjamanService(self::ATURAN);

        $this->assertSame(0, $service->hitungLamaPinjam('2026-09-01', Carbon::parse('2026-09-01 23:59')));
        $this->assertSame(1, $service->hitungLamaPinjam('2026-09-01', Carbon::parse('2026-09-02 00:01')));
        $this->assertSame(10, $service->hitungLamaPinjam('2026-09-01', Carbon::parse('2026-09-11')));
        // tanggal kembali sebelum tanggal pinjam (salah input) tidak menghasilkan nilai negatif
        $this->assertSame(0, $service->hitungLamaPinjam('2026-09-10', Carbon::parse('2026-09-01')));
    }
}
