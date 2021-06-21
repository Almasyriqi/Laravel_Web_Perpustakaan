@extends('layouts.adminlte')

@section('title', 'Detail pinjam')

@section('content_header')
<h1>Detail Peminjaman Buku</h1>
@stop

@section('content')
<div class="container mt-5">
    <div class="row justify-content-center align-items-center">
        <div class="card" style="width: 24rem;">
            <div class="card-header">
                Detail Peminjaman
            </div>
            <div class="card-body">
                <ul class="list-group list-group-flush">
                    <li class="list-group-item"><b>Id: </b>{{ $pinjam->id }}</li>
                    <li class="list-group-item"><b>NIM Anggota: </b>{{ $pinjam->nim }}</li>
                    <li class="list-group-item"><b>Nama Anggota: </b>{{ $pinjam->name }}</li>
                    <li class="list-group-item"><b>Judul Buku: </b>{{ $pinjam->buku->judul }}</li>
                    <li class="list-group-item"><b>Jumlah: </b>{{$pinjam->jumlah }}</li>
                    <li class="list-group-item"><b>Tanggal Pinjam: </b>{{  date('d-m-Y', strtotime($pinjam->tgl_pinjam)) }}</li>
                    @if ($pinjam->status == 'kembali')
                    <li class="list-group-item"><b>Tanggal Kembali: </b>{{  date('d-m-Y', strtotime($pinjam->tgl_kembali)) }}</li>
                    <li class="list-group-item"><b>Lama Pinjam: </b>{{ $pinjam->lama_pinjam }} Hari</li>
                    <li class="list-group-item"><b>Denda: </b>@currency($pinjam->denda)</li>
                    @endif
                    @php
                    $perpanjang = ['Iya', 'Tidak'];
                    @endphp
                    @if ($pinjam->perpanjang == 1)
                    <li class="list-group-item"><b>Perpanjang: </b>{{ $perpanjang[0] }}</li>
                    @else
                    <li class="list-group-item"><b>Perpanjang: </b>{{ $perpanjang[1] }}</li>
                    @endif
                    <li class="list-group-item"><b>Status: </b>{{ $pinjam->status }}</li>
                </ul>
            </div>
            <a class="btn btn-success mt-3" href="/anggota/pinjam">Kembali</a>
        </div>
    </div>
</div>
@endsection