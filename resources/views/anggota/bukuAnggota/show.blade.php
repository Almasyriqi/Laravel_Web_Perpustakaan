@extends('layouts.adminlte')

@section('title', 'Detail Buku')

@section('content_header')
    <h1>Detail Buku</h1>
@stop

@section('content')
<div class="container mt-5">
    <div class="row justify-content-center align-items-center">
        <div class="card" style="width: 24rem;">
            <div class="card-header">
                Detail Buku
            </div>
            <div class="card-body">
                <ul class="list-group list-group-flush">
                    <li class="list-group-item"><b>Id: </b>{{ $buku->id }}</li>
                    <li class="list-group-item"><b>Kategori Id: </b>{{ $buku->kategori->nama }}</li>
                    <li class="list-group-item"><b>Judul: </b>{{ $buku->judul }}</li>
                    <li class="list-group-item"><b>Penerbit: </b>{{$buku->penerbit }}</li>
                    <li class="list-group-item"><b>Penulis: </b>{{ $buku->penulis }}</li>
                    <li class="list-group-item"><b>Keterangan: </b>{{ $buku->keterangan }}</li>
                    <li class="list-group-item"><b>Stok: </b>{{ $buku->stok }}</li>
                    <li class="list-group-item"><b>Gambar: </b></li>
                    <li class="list-group-item"><img width="150px" src="{{ $buku->gambar }}"></li>
                </ul>
            </div>
            <a class="btn btn-success mt-3" href="/anggota/buku">Kembali</a>
        </div>
    </div>
</div>
@endsection