@extends('layouts.adminlte')

@section('title', 'Detail Anggota Admin')

@section('content_header')
    <h1>Detail Petugas</h1>
@stop

@section('content')
<div class="container mt-5">
    <div class="row justify-content-center align-items-center">
        <div class="card" style="width: 24rem;">
            <div class="card-header">
                Detail Petugas
            </div>
            <div class="card-body">
                <ul class="list-group list-group-flush">
                    <li class="list-group-item"><b>Id: </b>{{ $petugas->id }}</li>
                    <li class="list-group-item"><b>Username: </b>{{ $petugas->user->username }}</li>
                    <li class="list-group-item"><b>Nama: </b>{{ $petugas->user->name }}</li>
                    <li class="list-group-item"><b>Tanggal Lahir: </b>{{isset($petugas->tgl_lahir) ? \Carbon\Carbon::parse($petugas->tgl_lahir)->toFormattedDateString() : ''}}</li>
                    <li class="list-group-item"><b>No_Handphone: </b>{{ $petugas->no_hp }}</li>
                    <li class="list-group-item"><b>Email: </b>{{ $petugas->user->email }}</li>
                    <li class="list-group-item"><b>Alamat: </b>{{ $petugas->alamat }}</li>
                </ul>
            </div>
            <a class="btn btn-success mt-3" href="{{ route('petugas.index') }}">Kembali</a>

        </div>
    </div>
</div>
@endsection