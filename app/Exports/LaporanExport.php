<?php

namespace App\Exports;

use App\Models\Peminjaman;
use App\Support\PeriodeLaporan;
use Illuminate\Database\Eloquent\Builder;
use Maatwebsite\Excel\Concerns\FromQuery;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithTitle;

/**
 * Lembar Excel laporan peminjaman — kolom sama dengan laporan PDF.
 */
class LaporanExport implements FromQuery, ShouldAutoSize, WithHeadings, WithMapping, WithTitle
{
    public function __construct(private readonly PeriodeLaporan $periode) {}

    public function query(): Builder
    {
        return $this->periode->terapkan(Peminjaman::query())
            ->with(['anggota.user', 'buku'])
            ->orderBy('tgl_pinjam')
            ->orderBy('id');
    }

    /**
     * @return list<string>
     */
    public function headings(): array
    {
        return [
            'Nama Peminjam', 'NIM', 'Buku', 'Jumlah', 'Tanggal Pinjam', 'Harus Kembali',
            'Tanggal Kembali', 'Lama Pinjam (hari)', 'Status', 'Denda (Rp)',
        ];
    }

    /**
     * @param  Peminjaman  $row
     * @return list<int|string>
     */
    public function map($row): array
    {
        return [
            $row->anggota->user->name,
            (string) $row->anggota_id,
            $row->buku->judul,
            $row->jumlah,
            self::tanggal($row->tgl_pinjam),
            self::tanggal($row->tgl_harus_kembali, '-'),
            self::tanggal($row->tgl_kembali, 'Belum'),
            $row->lama_pinjam ?? 0,
            $row->status,
            $row->denda,
        ];
    }

    /**
     * Nama sheet (Excel membatasi 31 karakter, jadi memakai slug bukan label).
     */
    public function title(): string
    {
        return 'Laporan '.$this->periode->slug();
    }

    private static function tanggal(?string $tgl, string $kosong = ''): string
    {
        return $tgl === null ? $kosong : date('d-m-Y', strtotime($tgl));
    }
}
