@extends('layouts.adminlte')

@section('title', 'Arsip Anggota')

@section('content-custom')
@php $prefix = Auth::user()->role == 'admin' ? '/admin' : '/petugas'; @endphp
<div class="row">
    <div class="col-lg-12 margin-tb">
        <div class="pull-left mt-2">
            <h2>Arsip Anggota</h2>
            <p class="text-muted">Akun anggota yang diarsipkan tidak bisa login; riwayat peminjamannya tetap tersimpan.</p>
            <hr>
        </div>
        <div class="float-right my-2">
            <a class="btn btn-secondary" href="{{ $prefix }}/anggota"><i class="fas fa-arrow-left"></i> Kembali ke Data Anggota</a>
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
            <th>NIM</th>
            <th>Nama</th>
            <th>Email</th>
            <th>Jurusan</th>
            <th>Dihapus pada</th>
            <th>Aksi</th>
        </tr>
    </thead>
    <tbody>
        @forelse ($paginate as $anggota)
        <tr>
            <td>{{ $anggota->nim }}</td>
            <td>{{ $anggota->user->name ?? '-' }}</td>
            <td>{{ $anggota->user->email ?? '-' }}</td>
            <td>{{ $anggota->jurusan }}</td>
            <td>{{ $anggota->deleted_at->format('d-m-Y H:i') }}</td>
            <td>
                <form method="post" action="{{ $prefix }}/anggota/{{ $anggota->nim }}/pulihkan" class="d-inline">
                    @csrf
                    @method('PUT')
                    <button type="submit" class="btn btn-success btn-sm"><i class="fas fa-undo"></i> Pulihkan</button>
                </form>
            </td>
        </tr>
        @empty
        <tr><td colspan="6" class="text-center">Arsip kosong.</td></tr>
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
