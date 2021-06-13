@extends('layouts.adminlte')

@section('title', 'Detail Anggota Admin')

@section('content_header')
    <h1>Detail Admin</h1>
@stop

@section('content')
<div class="container mt-5">
    <div class="row justify-content-center align-items-center">
        <div class="card" style="width: 24rem;">
            <div class="card-header">
                Detail Admin
            </div>
            <div class="card-body">
                <ul class="list-group list-group-flush">
                    <li class="list-group-item"><b>Id: </b>{{ $kategori->id }}</li>
                    <li class="list-group-item"><b>Nama: </b>{{ $kategori->nama }}</li>
                    <li class="list-group-item"><b>Keterangan: </b>{{ $kategori->keterangan }}</li>
                </ul>
            </div>
            @if (Auth::user()->role == 'admin')
            <a class="btn btn-success mt-3" href="/admin/kategori">Kembali</a>
            @else
            <a class="btn btn-success mt-3" href="/petugas/kategori">Kembali</a>
            @endif
        </div>
    </div>
</div>
@endsection