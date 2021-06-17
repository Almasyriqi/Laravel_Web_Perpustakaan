@extends('layouts.adminlte')

@section('title', 'Edit Anggota Admin')

@section('content_header')
<h1>Edit Anggota</h1>
@stop

@section('css-custom')
<link rel="stylesheet" href="//code.jquery.com/ui/1.12.1/themes/base/jquery-ui.css">
@endsection

@section('content-custom')
<div class="container mt-5">

    <div class="row justify-content-center align-items-center">
        <div class="card" style="width: 24rem;">
            <div class="card-header">
                Edit Anggota
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
                <form method="post" action="/admin/anggota/{{$anggota->nim }}" id="myForm">
                @else
                    <form method="post" action="/petugas/anggota/{{$anggota->nim }}" id="myForm">
                @endif

                        @csrf
                        @method('PUT')
                        <div class="form-group">
                            <label for="username">Username</label>
                            <input type="username" name="username" class="form-control" id="username"
                                aria-describedby="username" value="{{ $anggota->user->username }}">
                        </div>
                        <div class="form-group">
                            <label for="nim">Nim</label>
                            <input type="text" name="nim" class="form-control" id="nim" aria-describedby="nim"
                                value="{{ $anggota->nim }}">
                        </div>
                        <div class="form-group">
                            <label for="nama">Nama</label>
                            <input type="nama" name="nama" class="form-control" id="nama" aria-describedby="nama"
                                value="{{ $anggota->user->name }}">
                        </div>
                        <div class="form-group">
                            <label for="jurusan">Jurusan</label>
                            @php
                            $jurusan = ['Teknologi Informasi', 'Teknik Elektro', 'Teknik Mesin',
                            'Teknik Sipil', 'Teknik Kimia', 'Akuntansi', 'Administrasi Niaga'];
                            @endphp
                            <select name="jurusan" class="form-control" id="jurusan">
                                @foreach ($jurusan as $item)
                                <option value="{{$item}}" {{$anggota->jurusan == $item ? 'selected' : ''}}>{{$item}}
                                </option>
                                @endforeach
                            </select>
                        </div>
                        <div class="form-group">
                            <label for="tgl_lahir">Tanggal Lahir</label>
                            <input type="date" name="tgl_lahir" class="form-control" id="tgl_lahir" aria-describedby="tgl_lahir"
                            value="{{\Carbon\Carbon::parse($anggota->tgl_lahir)->toDateString()}}"> 
                        </div>
                        <div class="form-group">
                            <label for="no_hp">No Handphone</label>
                            <input type="no_hp" name="no_hp" class="form-control" id="no_hp" aria-describedby="no_hp"
                                value="{{ $anggota->no_hp }}">
                        </div>
                        <div class="form-group">
                            <label for="email">Email</label>
                            <input type="email" name="email" class="form-control" id="email" aria-describedby="email"
                                value="{{ $anggota->user->email }}">
                        </div>
                        <div class="form-group">
                            <label for="alamat">Alamat</label>
                            <textarea type="alamat" name="alamat" class="form-control" id="alamat"
                                aria-describedby="alamat"
                                value="{{ $anggota->alamat }}">{{ $anggota->alamat }}</textarea>
                        </div>
                        <button type="submit" class="btn btn-primary">Submit</button>
                    </form>
            </div>
        </div>
    </div>
</div>
@endsection