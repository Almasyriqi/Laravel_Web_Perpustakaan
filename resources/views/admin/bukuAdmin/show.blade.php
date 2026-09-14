@extends('layouts.adminlte')

@section('title', 'Detail Buku Admin')

@section('content_header')
    <h1>Detail Buku</h1>
@stop

@section('content')
@php
    $prefix = Auth::user()->isAdmin() ? 'admin' : 'petugas';
@endphp
<div class="container mt-4">
    @if ($message = Session::get('success'))
    <div class="alert alert-success">
        <p class="mb-0">{{ $message }}</p>
    </div>
    @endif

    <div class="row">
        <div class="col-lg-5 mb-4">
            <div class="card h-100">
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
                        <li class="list-group-item"><b>Rating: </b>
                            @include('partials.bintang', ['rating' => $buku->ulasan_avg_rating, 'jumlah' => $buku->ulasan_count])
                        </li>
                        <li class="list-group-item"><b>Gambar: </b></li>
                        <li class="list-group-item"><img width="150px" src="{{ $buku->gambar_url }}" alt="Sampul {{ $buku->judul }}"></li>
                        <li class="list-group-item"><b>Kode QR: </b><code>{{ $kode }}</code><br>
                            <img width="110" src="{{ $qr }}" alt="QR {{ $kode }}" class="mt-1">
                        </li>
                    </ul>
                </div>
                <div class="card-footer d-flex flex-wrap">
                    <a class="btn btn-success m-1" href="/{{ $prefix }}/buku"><i class="fas fa-undo"></i> Kembali</a>
                    <a class="btn btn-outline-primary m-1" href="/{{ $prefix }}/buku/{{ $buku->id }}/label" target="_blank" title="Label QR untuk ditempel di buku (PDF 60x40 mm)">
                        <i class="fas fa-qrcode"></i> Cetak label QR
                    </a>
                </div>
            </div>
        </div>

        <div class="col-lg-7 mb-4">
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title"><i class="fas fa-comments"></i> Ulasan Anggota ({{ $ulasan->count() }})</h3>
                </div>
                <div class="card-body p-0">
                    <ul class="list-group list-group-flush">
                        @forelse ($ulasan as $u)
                        <li class="list-group-item">
                            <div class="d-flex justify-content-between align-items-start flex-wrap">
                                <div>
                                    <b>{{ $u->anggota?->user?->name ?? 'Anggota' }}</b>
                                    <small class="text-muted">({{ $u->anggota_id }}) · {{ $u->updated_at->format('d-m-Y') }}</small>
                                    <div>@include('partials.bintang', ['rating' => $u->rating])</div>
                                </div>
                                {{-- Moderasi: hapus ulasan yang tidak pantas --}}
                                <form method="post" action="/{{ $prefix }}/buku/{{ $buku->id }}/ulasan/{{ $u->id }}"
                                    onsubmit="return confirm('Hapus ulasan ini?')">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="btn btn-xs btn-outline-danger" title="Hapus ulasan">
                                        <i class="fas fa-trash"></i> Hapus
                                    </button>
                                </form>
                            </div>
                            @if ($u->komentar)
                            <p class="mb-0 mt-1">{{ $u->komentar }}</p>
                            @endif
                        </li>
                        @empty
                        <li class="list-group-item text-muted">Belum ada ulasan untuk buku ini.</li>
                        @endforelse
                    </ul>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
