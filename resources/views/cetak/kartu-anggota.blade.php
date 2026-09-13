<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <title>Kartu Anggota {{ $anggota->nim }}</title>
    <style>
        @page { margin: 0; }
        body { font-family: DejaVu Sans, Helvetica, Arial, sans-serif; margin: 0; font-size: 8pt; color: #222; }
        .kartu { width: 242.6pt; height: 153.1pt; overflow: hidden; }
        .kepala { background: #007bff; color: #fff; padding: 7pt 10pt; }
        .kepala .nama-perpus { font-size: 11pt; font-weight: bold; letter-spacing: 0.5pt; }
        .kepala .sub { font-size: 6.5pt; opacity: 0.9; }
        table { width: 100%; border-collapse: collapse; }
        td { vertical-align: top; padding: 8pt 10pt 0 10pt; }
        .qr { width: 78pt; padding-left: 0; }
        .qr img { width: 78pt; height: 78pt; }
        .label { font-size: 6pt; text-transform: uppercase; letter-spacing: 0.5pt; color: #777; }
        .nilai { font-size: 9pt; font-weight: bold; margin-bottom: 4pt; }
        .nim { font-family: DejaVu Sans Mono, monospace; }
        .kaki { font-size: 6pt; color: #888; padding: 0 10pt; }
    </style>
</head>
<body>
    <div class="kartu">
        <div class="kepala">
            <div class="nama-perpus">POLINEMA LIBRARY</div>
            <div class="sub">Kartu Anggota Perpustakaan</div>
        </div>
        <table>
            <tr>
                <td>
                    <div class="label">Nama</div>
                    <div class="nilai">{{ \Illuminate\Support\Str::limit($anggota->user->name, 32) }}</div>
                    <div class="label">NIM</div>
                    <div class="nilai nim">{{ $anggota->nim }}</div>
                    <div class="label">Jurusan</div>
                    <div class="nilai">{{ $anggota->jurusan }}</div>
                </td>
                <td class="qr"><img src="{{ $qr }}" alt="{{ $kode }}"></td>
            </tr>
        </table>
        <div class="kaki">{{ $kode }} · Tunjukkan kartu ini kepada petugas saat meminjam dan mengembalikan buku.</div>
    </div>
</body>
</html>
