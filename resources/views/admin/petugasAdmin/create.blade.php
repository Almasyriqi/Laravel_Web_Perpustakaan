@extends('layouts.adminlte')

@section('title', 'Tambah Petugas Admin')

@section('content_header')
    <h1>Tambah Petugas Admin</h1>
@stop

@section('css-custom')
<link rel="stylesheet" href="//code.jquery.com/ui/1.12.1/themes/base/jquery-ui.css">
@endsection

@section('content-custom')
    <div class="container mt-5">

        <div class="row justify-content-center align-items-center">
            <div class="card" style="width: 24rem;">
                <div class="card-header">
                    Tambah Petugas
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
                    <form method="post" action="{{ route('petugas.store') }}" id="myForm">
                        @csrf
                        <div class="form-group">
                            <label for="username">Username</label>
                            <input type="username" name="username" class="form-control" id="username" aria-describedby="username"
                                value="{{ old('username') }}">
                        </div>
                        <div class="form-group">
                            <label for="password">Password</label>
                            <input type="password" name="password" class="form-control" id="password"
                                aria-describedby="password" value="{{old("password")}}">
                        </div>
                        <div class="form-group">
                            <label for="nama">Nama</label>
                            <input type="nama" name="nama" class="form-control" id="nama" aria-describedby="nama"
                                value="{{ old('nama') }}">
                        </div>
                        <div class="form-group">
                            <label for="tgl_lahir">Tanggal Lahir</label>
                            <input type="date" name="tgl_lahir" class="form-control" id="tgl_lahir"
                                aria-describedby="tgl_lahir" value="{{ old('tgl_lahir') }}">
                        </div>
                        <div class="form-group">
                            <label for="no_hp">No Handphone</label>
                            <input type="no_hp" name="no_hp" class="form-control" id="no_hp"
                                aria-describedby="no_hp" value="{{ old('no_hp') }}">
                        </div>
                        <div class="form-group">
                            <label for="email">Email</label>
                            <input type="email" name="email" class="form-control" id="email" aria-describedby="email"
                                value="{{ old('email') }}">
                        </div>
                        <div class="form-group">
                            <label for="alamat">Alamat</label>
                            <textarea type="alamat" name="alamat" class="form-control" id="alamat" aria-describedby="alamat"
                                value="{{ old('alamat') }}">{{ old('alamat') }}</textarea>
                        </div>
                        <button type="submit" class="btn btn-primary">Submit</button>
                    </form>
                </div>
            </div>
        </div>
    </div>
@endsection
