<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <title>Laporan Peminjaman {{ $namaBulan }} {{ $tahun }}</title>
    <style>
        body { font-family: DejaVu Sans, Helvetica, Arial, sans-serif; font-size: 12px; }
        h3 { margin: 0 0 4px; text-align: center; }
        .judul { margin-bottom: 16px; }
        table { width: 100%; border-collapse: collapse; }
        th, td { border: 1px solid #444; padding: 4px 6px; text-align: left; }
        th { background: #eee; }
        td.angka { text-align: right; }
    </style>
</head>
<body>
    <div class="judul">
        <h3>LAPORAN PERPUSTAKAAN POLINEMA</h3>
        <h3>BULAN {{ strtoupper($namaBulan) }} {{ $tahun }}</h3>
    </div>
    <table>
        <thead>
            <tr>
                <th>Nama Peminjam</th>
                <th>Buku</th>
                <th>Jumlah</th>
                <th>Tanggal Pinjam</th>
                <th>Tanggal Kembali</th>
                <th>Lama Pinjam</th>
                <th>Status</th>
                <th>Denda</th>
            </tr>
        </thead>
        <tbody>
            @forelse ($laporan as $lp)
            <tr>
                <td>{{ $lp->name }}</td>
                <td>{{ $lp->buku->judul }}</td>
                <td class="angka">{{ $lp->jumlah }}</td>
                <td>{{ date('d-m-Y', strtotime($lp->tgl_pinjam)) }}</td>
                @if ($lp->tgl_kembali === null)
                <td>Belum</td>
                <td class="angka">0</td>
                <td>{{ $lp->status }}</td>
                <td class="angka">Rp 0</td>
                @else
                <td>{{ date('d-m-Y', strtotime($lp->tgl_kembali)) }}</td>
                <td class="angka">{{ $lp->lama_pinjam }}</td>
                <td>{{ $lp->status }}</td>
                <td class="angka">@currency($lp->denda)</td>
                @endif
            </tr>
            @empty
            <tr>
                <td colspan="8" style="text-align:center">Tidak ada peminjaman pada periode ini.</td>
            </tr>
            @endforelse
        </tbody>
    </table>
</body>
</html>
