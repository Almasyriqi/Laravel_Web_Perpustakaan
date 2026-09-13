<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <title>Label {{ $kode }}</title>
    <style>
        @page { margin: 0; }
        body { font-family: DejaVu Sans, Helvetica, Arial, sans-serif; margin: 0; padding: 8pt; font-size: 8pt; color: #222; }
        table { width: 100%; border-collapse: collapse; }
        td { vertical-align: top; }
        .qr { width: 78pt; }
        .qr img { width: 78pt; height: 78pt; }
        .perpus { font-size: 6.5pt; letter-spacing: 0.5pt; white-space: nowrap; text-transform: uppercase; color: #666; margin-bottom: 3pt; }
        .judul { font-size: 9pt; font-weight: bold; line-height: 1.2; margin-bottom: 2pt; }
        .meta { font-size: 7pt; color: #444; }
        .kode { font-family: DejaVu Sans Mono, monospace; font-size: 9pt; font-weight: bold; margin-top: 4pt; }
    </style>
</head>
<body>
    <table>
        <tr>
            <td class="qr"><img src="{{ $qr }}" alt="{{ $kode }}"></td>
            <td style="padding-left: 6pt;">
                <div class="perpus">Polinema Library</div>
                <div class="judul">{{ \Illuminate\Support\Str::limit($buku->judul, 60) }}</div>
                <div class="meta">{{ $buku->penulis }}</div>
                <div class="meta">{{ $buku->kategori?->nama }}</div>
                <div class="kode">{{ $kode }}</div>
            </td>
        </tr>
    </table>
</body>
</html>
