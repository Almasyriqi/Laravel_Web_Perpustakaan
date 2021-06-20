@extends('layouts.adminlte')

@section('title', 'Data Peminjaman')

@section('content-custom')
<div class="row">
    <div class="col-lg-12 margin-tb">
        <div class="pull-left mt-2">
            <h2>Data Peminjaman Perpustakaan</h2>
            <hr>
        </div>
        <div class="float-right my-2">
            <a class="btn btn-success" href="/petugas/transaksi/create"><i class="fas fa-arrow-circle-down"></i> Input
                Peminjaman</a>
        </div>
    </div>
</div>

@if ($message = Session::get('success'))
<div class="alert alert-success">
    <p>{{ $message }}</p>
</div>
@endif

<table class="table table-bordered" id="example">
    <thead>
        <tr>
            <th>NIM</th>
            <th>Nama</th>
            <th>Jurusan</th>
            <th>Email</th>
            <th width="120px">Action</th>
        </tr>
    </thead>
    <tbody>
        @foreach ($pinjam as $peminjaman)
        <tr>
            <td>{{ $peminjaman->nim }}</td>
            <td>{{ $peminjaman->name }}</td>
            <td>{{ $peminjaman->jurusan }}</td>
            <td>{{$peminjaman->email}}</td>
            <td>
                <a class="btn btn-info" href="/petugas/transaksi/{{  $peminjaman->nim }}/edit">
                    <i class="fas fa-eye"></i> View</a>
            </td>
        </tr>
        @endforeach
    </tbody>
</table>
@endsection

@section('js')
<script>
    $(function () {
          $('#example').DataTable({
            "paging": true,
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