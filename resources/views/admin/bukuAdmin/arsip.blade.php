@extends('layouts.adminlte')

@section('title', 'Arsip Buku')

@section('content-custom')
@php $prefix = Auth::user()->role == 'admin' ? '/admin' : '/petugas'; @endphp
<div class="row">
    <div class="col-lg-12 margin-tb">
        <div class="pull-left mt-2">
            <h2>Arsip Buku</h2>
            <p class="text-muted">Buku yang dihapus tetap tercatat di riwayat peminjaman dan bisa dipulihkan.</p>
            <hr>
        </div>
        <div class="float-right my-2">
            <a class="btn btn-secondary" href="{{ $prefix }}/buku"><i class="fas fa-arrow-left"></i> Kembali ke Data Buku</a>
        </div>
    </div>
</div>

@if ($message = Session::get('success'))
<div class="alert alert-success"><p>{{ $message }}</p></div>
@endif
@include('partials.errors')

<table class="table table-bordered" id="example">
    <thead>
        <tr>
            <th>Id</th>
            <th>Judul</th>
            <th>Kategori</th>
            <th>Penulis</th>
            <th>Stok</th>
            <th>Dihapus pada</th>
            <th>Aksi</th>
        </tr>
    </thead>
    <tbody>
        @forelse ($paginate as $buku)
        <tr>
            <td>{{ $buku->id }}</td>
            <td>{{ $buku->judul }}</td>
            <td>{{ $buku->kategori->nama ?? '-' }}</td>
            <td>{{ $buku->penulis }}</td>
            <td>{{ $buku->stok }}</td>
            <td>{{ $buku->deleted_at->format('d-m-Y H:i') }}</td>
            <td>
                <form method="post" action="{{ $prefix }}/buku/{{ $buku->id }}/pulihkan" class="d-inline">
                    @csrf
                    @method('PUT')
                    <button type="submit" class="btn btn-success btn-sm"><i class="fas fa-undo"></i> Pulihkan</button>
                </form>
            </td>
        </tr>
        @empty
        <tr><td colspan="7" class="text-center">Arsip kosong.</td></tr>
        @endforelse
    </tbody>
</table>
@endsection

@section('js')
<script>
$(function () {
    $('#example').DataTable({ "paging": true, "lengthChange": false, "searching": true, "ordering": true, "info": false, "autoWidth": false, "responsive": true });
});
</script>
@stop
