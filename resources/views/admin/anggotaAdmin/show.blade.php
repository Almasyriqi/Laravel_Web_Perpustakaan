@extends('layouts.adminlte')

@section('title', 'Detail Anggota')

@section('content_header')
    <h1>Detail Anggota</h1>
@stop

@section('content')
<div class="container mt-5">
    <div class="row justify-content-center align-items-center">
        <div class="card" style="width: 24rem;">
            <div class="card-header">
                Detail anggota
            </div>
            <div class="card-body">
                <ul class="list-group list-group-flush">
                    <li class="list-group-item"><b>Nim: </b>{{ $anggota->nim }}</li>
                    <li class="list-group-item"><b>Username: </b>{{ $anggota->user->username }}</li>
                    <li class="list-group-item"><b>Nama: </b>{{ $anggota->user->name }}</li>
                    <li class="list-group-item"><b>Jurusan: </b>{{ $anggota->jurusan }}</li>
                    <li class="list-group-item"><b>Tanggal Lahir: </b>{{isset($anggota->tgl_lahir) ? \Carbon\Carbon::parse($anggota->tgl_lahir)->toFormattedDateString() : ''}}</li>
                    <li class="list-group-item"><b>No_Handphone: </b>{{ $anggota->no_hp }}</li>
                    <li class="list-group-item"><b>Email: </b>{{ $anggota->user->email }}</li>
                    <li class="list-group-item"><b>Alamat: </b>{{ $anggota->alamat }}</li>
                </ul>
            </div>
            @if (Auth::user()->role == 'admin')
            <a class="btn btn-success mt-3" href="/admin/anggota">Kembali</a>
            @else
            <a class="btn btn-success mt-3" href="/petugas/anggota">Kembali</a>
            @endif

        </div>
    </div>
</div>
@endsection