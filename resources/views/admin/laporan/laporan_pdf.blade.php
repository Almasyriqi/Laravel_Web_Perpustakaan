<!DOCTYPE html>
<html lang="">

    <head>
        <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css"
            integrity="sha384-JcKb8q3iqJ61gNV9KGb8thSsNjpSL0n8PARn9HuZOnIxN0hoP+VmmDGMN5t9UJ0Z" crossorigin="anonymous">
        <script src="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"
            integrity="sha384-B4gt1jrGC7Jh4AgTPSdUtOBvfO8shuf57BaghqFfPlYxofvL8/KUEfYiJOMMV+rV" crossorigin="anonymous">
        </script>
        <script src="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.bundle.min.js"
            integrity="sha384-LtrjvnR4Twt/qOuYxE721u19sVFLVSA4hf/rRt6PrZTmiPltdZcI7q7PXQBYTKyf" crossorigin="anonymous">
        </script>
        <title>Data Laporan</title>
    </head>
    @php
    $bulan = ['JANUARI', 'FEBRUARI', 'MARET', 'APRIL', 'MEI', 'JUNI',
    'JULI', 'AGUSTUS', 'SEPTEMBER', 'OKTOBER', 'NOVEMBER', 'DESEMBER'];
    @endphp

    <body>
        <div class="text-center card-header">
            <h3>LAPORAN PERPUSTAKAAN POLINEMA</h3>
            <h3>BULAN {{$bulan[$sekarang]}}</h3>
        </div>
        <table class="table table-bordered">
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
            @if($laporan)
            @foreach($laporan as $lp)
            <tr>
                <td>{{ $lp->name }}</td>
                <td>{{ $lp->buku->judul }}</td>
                <td>{{ $lp->jumlah }}</td>
                <td>{{ date('d-m-Y', strtotime($lp->tgl_pinjam)) }}</td>
                @if ($lp->tgl_kembali==null)
                <td>Belum</td>
                <td>0</td>
                <td>{{ $lp->status }}</td>
                <td>Rp. 0</td>
                @else
                <td>{{ date('d-m-Y', strtotime($lp->tgl_kembali)) }}</td>
                <td>{{ $lp->lama_pinjam }}</td>
                <td>{{ $lp->status }}</td>
                <td>@currency($lp->denda)</td>
                @endif

            </tr>
            @endforeach
            @endif
        </table>
    </body>

</html>