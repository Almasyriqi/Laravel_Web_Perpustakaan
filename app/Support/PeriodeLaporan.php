<?php

namespace App\Support;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Validation\ValidationException;

/**
 * Periode laporan: satu bulan penuh (bulan + tahun) atau rentang tanggal bebas.
 * Dipakai tampilan HTML, PDF, dan Excel supaya ketiganya memakai filter yang sama.
 */
final readonly class PeriodeLaporan
{
    /** Batas rentang bebas supaya export tidak menelan seluruh tabel. */
    public const MAKS_HARI = 366;

    private function __construct(
        public Carbon $dari,
        public Carbon $sampai,
        public ?int $bulan = null,
        public ?int $tahun = null,
    ) {}

    public static function dariBulan(int $bulan, int $tahun): self
    {
        $awal = Carbon::create($tahun, $bulan, 1)->startOfDay();

        return new self($awal, $awal->copy()->endOfMonth()->startOfDay(), $bulan, $tahun);
    }

    public static function dariRentang(string $dari, string $sampai): self
    {
        return new self(Carbon::parse($dari)->startOfDay(), Carbon::parse($sampai)->startOfDay());
    }

    /**
     * Bulan dari segmen URL (1-12) + ?tahun= (default tahun berjalan); bila
     * $bulan null, periode diambil dari ?dari= & ?sampai= (rentang bebas).
     *
     * @throws ValidationException
     */
    public static function dariRequest(Request $request, ?string $bulan = null): self
    {
        if ($bulan === null) {
            $data = $request->validate([
                'dari' => ['required', 'date_format:Y-m-d'],
                'sampai' => ['required', 'date_format:Y-m-d', 'after_or_equal:dari'],
            ], [], ['dari' => 'tanggal awal', 'sampai' => 'tanggal akhir']);

            $periode = self::dariRentang($data['dari'], $data['sampai']);

            if ($periode->dari->diffInDays($periode->sampai) >= self::MAKS_HARI) {
                throw ValidationException::withMessages([
                    'sampai' => 'Rentang laporan maksimal '.self::MAKS_HARI.' hari.',
                ]);
            }

            return $periode;
        }

        if (! ctype_digit($bulan) || (int) $bulan < 1 || (int) $bulan > 12) {
            throw ValidationException::withMessages(['bulan' => 'Bulan harus di antara 1 dan 12.']);
        }

        $tahun = $request->integer('tahun', now()->year);
        if ($tahun < 2000 || $tahun > 2100) {
            throw ValidationException::withMessages(['tahun' => 'Tahun tidak valid.']);
        }

        return self::dariBulan((int) $bulan, $tahun);
    }

    public function bulanan(): bool
    {
        return $this->bulan !== null;
    }

    /**
     * "September 2026" atau "01-09-2026 s.d. 15-09-2026".
     */
    public function label(): string
    {
        if ($this->bulanan()) {
            return Bulan::nama($this->bulan).' '.$this->tahun;
        }

        return $this->dari->format('d-m-Y').' s.d. '.$this->sampai->format('d-m-Y');
    }

    /**
     * Potongan nama file: "2026-09" atau "2026-09-01_2026-09-15".
     */
    public function slug(): string
    {
        if ($this->bulanan()) {
            return sprintf('%04d-%02d', $this->tahun, $this->bulan);
        }

        return $this->dari->toDateString().'_'.$this->sampai->toDateString();
    }

    /**
     * Parameter query string / route untuk membangun tautan PDF & Excel.
     *
     * @return array<string, int|string>
     */
    public function parameter(): array
    {
        if ($this->bulanan()) {
            return ['bulan' => $this->bulan, 'tahun' => $this->tahun];
        }

        return ['dari' => $this->dari->toDateString(), 'sampai' => $this->sampai->toDateString()];
    }

    /**
     * whereBetween pada kolom tanggal (inklusif kedua ujung); memakai index tgl_pinjam.
     */
    public function terapkan(Builder $query, string $kolom = 'tgl_pinjam'): Builder
    {
        return $query->whereBetween($kolom, [$this->dari->toDateString(), $this->sampai->toDateString()]);
    }
}
