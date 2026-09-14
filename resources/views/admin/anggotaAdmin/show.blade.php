@extends('layouts.adminlte')

@section('title', 'Detail Anggota')

@section('content_header')
    <h1>Detail Anggota</h1>
@stop

@section('content')
<div class="container mt-5">
    <div class="row justify-content-center align-items-center">
        <div class="card" style="width: 24rem; max-width: 100%;">
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
                    <li class="list-group-item"><b>Kode QR: </b><code>{{ $kode }}</code><br>
                        <img width="110" src="{{ $qr }}" alt="QR {{ $kode }}" class="mt-1">
                    </li>
                </ul>
            </div>
            @php $prefix = Auth::user()->isAdmin() ? 'admin' : 'petugas'; @endphp
            <div class="card-footer d-flex flex-wrap">
                <a class="btn btn-success m-1" href="/{{ $prefix }}/anggota"><i class="fas fa-undo"></i> Kembali</a>
                <a class="btn btn-outline-primary m-1" href="/{{ $prefix }}/anggota/{{ $anggota->nim }}/kartu" target="_blank" title="Kartu anggota dengan QR (PDF ukuran kartu)">
                    <i class="fas fa-id-card"></i> Cetak kartu anggota
                </a>
            </div>

        </div>
    </div>
</div>
@endsection