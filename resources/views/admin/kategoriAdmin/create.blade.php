@extends('layouts.adminlte')

@section('title', 'Tambah Kategori Buku')

@section('content_header')
    <h1>Tambah Kategori Buku</h1>
@stop

@section('css-custom')
<link rel="stylesheet" href="//code.jquery.com/ui/1.12.1/themes/base/jquery-ui.css">
@endsection

@section('content-custom')
    <div class="container mt-5">

        <div class="row justify-content-center align-items-center">
            <div class="card" style="width: 24rem;">
                <div class="card-header">
                    Tambah Kategori Buku
                </div>
                <div class="card-body">
                    @if ($errors->any())
                        <div class="alert alert-danger">
                            <strong>Whoops!</strong> There were some problems with your input.<br><br>
                            <ul>
                                @foreach ($errors->all() as $error)
                                    <li>{{ $error }}</li>
                                @endforeach
                            </ul>
                        </div>
                    @endif
                    @if (Auth::user()->role == 'admin')
                    <form method="post" action="/admin/kategori" id="myForm">
                    @else
                    <form method="post" action="/petugas/kategori" id="myForm">
                    @endif
                    
                        @csrf
                        <div class="form-group">
                            <label for="nama">Nama</label>
                            <input type="nama" name="nama" class="form-control" id="nama" aria-describedby="nama"
                                value="{{ old('nama') }}">
                        </div>
                        <div class="form-group">
                            <label for="keterangan">Keterangan</label>
                            <input type="keterangan" name="keterangan" class="form-control" id="keterangan"
                                aria-describedby="keterangan" value="{{ old('keterangan') }}">
                        </div>
                        <button type="submit" class="btn btn-primary">Submit</button>
                    </form>
                </div>
            </div>
        </div>
    </div>
@endsection
