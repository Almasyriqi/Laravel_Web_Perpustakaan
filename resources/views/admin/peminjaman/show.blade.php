@extends('layouts.adminlte')

@section('title', 'Detail Buku Admin')

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
                    <li class="list-group-item"><b>Kategori: </b>{{ $buku->kategori->nama }}</li>
                    <li class="list-group-item"><b>Judul: </b>{{ $buku->judul }}</li>
                    <li class="list-group-item"><b>Penerbit: </b>{{$buku->penerbit }}</li>
                    <li class="list-group-item"><b>Penulis: </b>{{ $buku->penulis }}</li>
                    <li class="list-group-item"><b>Keterangan: </b>{{ $buku->keterangan }}</li>
                    <li class="list-group-item"><b>Stok: </b>{{ $buku->stok }}</li>
                    <li class="list-group-item"><b>Gambar: </b></li>
                    <li class="list-group-item"><img width="150px" src="{{ $buku->gambar }}"></li>
                </ul>
            </div>
            @if (Auth::user()->role == 'admin')
            <a class="btn btn-success mt-3" href="/admin/buku">Kembali</a>
                    @else
                    <a class="btn btn-success mt-3" href="/petugas/buku">Kembali</a>
                    @endif
            

        </div>
    </div>
</div>
@endsection