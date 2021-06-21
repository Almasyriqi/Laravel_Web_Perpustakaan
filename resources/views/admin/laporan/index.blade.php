@extends('layouts.adminlte')

@section('title', 'Data Peminjaman')

@section('content-custom')
<div class="row">
    <div class="col-lg-12 margin-tb">
        <div class="pull-left mt-2">
            <h2>Data Laporan Perpustakaan</h2>
            <hr>
        </div>
    </div>
</div>

@if ($message = Session::get('success'))
<div class="alert alert-success">
    <p>{{ $message }}</p>
</div>
@endif
        <table class="table table-bordered">

            <table class="table table-bordered" id="example">
                <thead>
                    <tr>
                        <th>Id</th>
                        <th>Anggota</th>
                        <th>Judul Buku</th>
                        <th>Jumlah</th>
                        <th>Tanggal Pinjam</th>
                        <th>Status</th>
                    </tr>
                </thead>
                <tbody>

                    @if (Auth::user()->role == 'admin')
                    <a href="{{ route('admin.cetak_pdf', $sekarang) }}" class="btn btn-warning"><i class="fas fa-print"> Cetak
                            Laporan</a></i>
                    @else
                    <a href="{{ route('petugas.cetak_pdf', $sekarang) }}" class="btn btn-warning"><i class="fas fa-print"> Cetak
                            Laporan</a></i>
                    @endif

                    @foreach ($laporan as $lp)
                    <tr>
                        <td>{{ $lp->id }}</td>
                        <td>{{ $lp->name }}</td>
                        <td>{{ $lp->judul }}</td>
                        <td>{{$lp->jumlah}}</td>
                        <td>{{ date('d-m-Y', strtotime($lp->tgl_pinjam)) }}</td>
                        <td>{{ $lp->status }}</td>
                    </tr>
                    @endforeach
                </tbody>
            </table>

            @php
                $bulan = ['Januari', 'Februari', 'Maret', 'April', 'Mei', 'Juni', 
                'Juli', 'Agustus', 'September', 'Oktober', 'November', 'Desember'];
                $tahun = now()->format('Y');
                $i = 1;
            @endphp
            <div class="row">
                <div class="col-12">
                    <div class="card">
                        <div class="card-header">
                            <h3 class="card-title">Select Month</h3>
                        </div>
                        <div class="card-body">
                            <ul class="pagination pagination-month justify-content-center">
                                @foreach ($bulan as $item)
                                @if ($i == $sekarang)
                                <li class="page-item active">
                                    @if (Auth::user()->role == 'admin')
                                    <a class="page-link" href="/admin/laporan/{{$i}}">
                                    @else
                                    <a class="page-link" href="/petugas/laporan/{{$i}}">
                                    @endif
                                        <p class="page-month">{{$item}}</p>
                                        <p class="page-year">{{$tahun}}</p>
                                    </a>
                                </li>
                                @else
                                <li class="page-item">
                                    @if (Auth::user()->role == 'admin')
                                    <a class="page-link" href="/admin/laporan/{{$i}}">
                                    @else
                                    <a class="page-link" href="/petugas/laporan/{{$i}}">
                                    @endif
                                        <p class="page-month">{{$item}}</p>
                                        <p class="page-year">{{$tahun}}</p>
                                    </a>
                                </li>
                                @endif
                                @php
                                    $i++;
                                @endphp
                                @endforeach
                            </ul>
                        </div>
                    </div>
                </div>
            </div>
@endsection

@section('js')
<script>
$(function () {
    $('#example').DataTable({
        "paging": false,
        "lengthChange": false,
        "searching": true,
        "ordering": true,
        "info": false,
        "autoWidth": false,
        "responsive": true,
    });
});
</script>
@stop