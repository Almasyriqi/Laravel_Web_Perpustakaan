@extends('layouts.adminlte')

@section('title', 'Data Laporan')

@section('content-custom')
<div class="row">
    <div class="col-lg-12 margin-tb">
        <div class="pull-left mt-2">
            <h2>Data Laporan Perpustakaan</h2>
            <p class="text-muted mb-0">Periode: <b>{{ $namaBulan[$sekarang] }} {{ $tahun }}</b></p>
            <hr>
        </div>
    </div>
</div>

@if ($message = Session::get('success'))
<div class="alert alert-success">
    <p>{{ $message }}</p>
</div>
@endif

<div class="d-flex flex-wrap align-items-center mb-3">
    <a href="{{ route($routePrefix.'.cetak_pdf', ['bulan' => $sekarang, 'tahun' => $tahun]) }}" class="btn btn-warning mr-3">
        <i class="fas fa-print"></i> Cetak Laporan
    </a>

    <form method="get" action="{{ route($routePrefix.'.laporan', ['bulan' => $sekarang]) }}" class="form-inline">
        <label for="tahun" class="mr-2">Tahun</label>
        <select name="tahun" id="tahun" class="form-control" onchange="this.form.submit()">
            @foreach (range(now()->year + 1, now()->year - 5) as $th)
            <option value="{{ $th }}" @selected($th == $tahun)>{{ $th }}</option>
            @endforeach
        </select>
    </form>
</div>

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
        @foreach ($laporan as $lp)
        <tr>
            <td>{{ $lp->id }}</td>
            <td>{{ $lp->name }}</td>
            <td>{{ $lp->judul }}</td>
            <td>{{ $lp->jumlah }}</td>
            <td>{{ date('d-m-Y', strtotime($lp->tgl_pinjam)) }}</td>
            <td>{{ $lp->status }}</td>
        </tr>
        @endforeach
    </tbody>
</table>

<div class="row">
    <div class="col-12">
        <div class="card">
            <div class="card-header">
                <h3 class="card-title">Pilih Bulan</h3>
            </div>
            <div class="card-body">
                <ul class="pagination pagination-month justify-content-center">
                    @foreach ($namaBulan as $i => $item)
                    <li class="page-item {{ $i == $sekarang ? 'active' : '' }}">
                        <a class="page-link" href="{{ route($routePrefix.'.laporan', ['bulan' => $i, 'tahun' => $tahun]) }}">
                            <p class="page-month">{{ $item }}</p>
                            <p class="page-year">{{ $tahun }}</p>
                        </a>
                    </li>
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
