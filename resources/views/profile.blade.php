@extends('layouts.adminlte')

@section('title', 'Edit Profile')

@section('content_header')
<h1>Edit Profile</h1>
<hr>
@stop

@section('css-custom')
<link rel="stylesheet" href="//code.jquery.com/ui/1.12.1/themes/base/jquery-ui.css">
@endsection

@section('content-custom')
<div class="container">
    <div class="row justify-content-center align-items-center">
        <div class="col-md-10">
            <div class="card card-primary" style="height: 100%">
                <div class="card-header">
                    <h3 class="card-title">Edit Profile</h3>
                </div>
                <!-- /.card-header -->
                <!-- form start -->
                @if (Auth::user()->role == 'anggota')
                    <form method="post" action="{{ route('profile.update', $user->nim) }}" id="myForm">
                @else
                    <form method="post" action="{{ route('profile.update', $user->id) }}" id="myForm">
                @endif
                
                    @csrf
                    @method('PUT')
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
                        <div class="form-group">
                            <label for="username">Username</label>
                            <input type="username" name="username" class="form-control" id="username"
                                aria-describedby="username" value="{{ $user->user->username }}">
                        </div>
                        @if (Auth::user()->role == 'anggota')
                        <div class="form-group">
                            <label for="nim">Nim</label>
                            <input type="text" name="nim" class="form-control" id="nim" aria-describedby="nim"
                                value="{{ $user->nim }}">
                        </div>
                        <div class="form-group">
                            <label for="jurusan">Jurusan</label>
                            @php
                            $jurusan = ['Teknologi Informasi', 'Teknik Elektro', 'Teknik Mesin',
                            'Teknik Sipil', 'Teknik Kimia', 'Akuntansi', 'Administrasi Niaga'];
                            @endphp
                            <select name="jurusan" class="form-control" id="jurusan">
                                @foreach ($jurusan as $item)
                                <option value="{{$item}}" {{$user->jurusan == $item ? 'selected' : ''}}>{{$item}}
                                </option>
                                @endforeach
                            </select>
                        </div>
                        <div class="form-group">
                            <label for="tgl_lahir">Tanggal Lahir</label>
                            <input type="tgl_lahir" name="tgl_lahir" class="form-control datepicker" id="tgl_lahir"
                                aria-describedby="tgl_lahir" placeholder="Year-Month-Day" autocomplete="off"
                                value="{{ $user->tgl_lahir }}">
                        </div>
                        @endif
                        @if (Auth::user()->role == 'petugas')
                        <div class="form-group">
                            <label for="tgl_lahir">Tanggal Lahir</label>
                            <input type="tgl_lahir" name="tgl_lahir" class="form-control datepicker" id="tgl_lahir"
                                aria-describedby="tgl_lahir" placeholder="Year-Month-Day" autocomplete="off"
                                value="{{ $user->tgl_lahir }}">
                        </div>
                        @endif
                        <div class="form-group">
                            <label for="nama">Nama</label>
                            <input type="nama" name="nama" class="form-control" id="nama" aria-describedby="nama"
                                value="{{ $user->user->name }}">
                        </div>
                        <div class="form-group">
                            <label for="no_hp">No Handphone</label>
                            <input type="no_hp" name="no_hp" class="form-control" id="no_hp" aria-describedby="no_hp"
                                value="{{ $user->no_hp }}">
                        </div>
                        <div class="form-group">
                            <label for="email">Email</label>
                            <input type="email" name="email" class="form-control" id="email" aria-describedby="email"
                                value="{{ $user->user->email }}">
                        </div>
                        <div class="form-group">
                            <label for="alamat">Alamat</label>
                            <textarea type="alamat" name="alamat" class="form-control" id="alamat"
                                aria-describedby="alamat" value="{{ $user->alamat }}">{{ $user->alamat }}</textarea>
                        </div>
                    </div>
                    <div class="card-footer">
                        <button type="submit" class="btn btn-primary">Submit</button>
                        @if (Auth::user()->role == 'admin')
                            <a href="/admin" class="btn btn-success float-right">Kembali</a>
                        @endif
                        @if (Auth::user()->role == 'petugas')
                            <a href="/petugas" class="btn btn-success float-right">Kembali</a>
                        @endif
                        @if (Auth::user()->role == 'anggota')
                            <a href="/anggota" class="btn btn-success float-right">Kembali</a>
                        @endif
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection

@section('js')
<script src="https://code.jquery.com/ui/1.12.1/jquery-ui.js"></script>
<script>
    $( function() {
        $( ".datepicker" ).datepicker({
            dateFormat: 'yy-mm-dd',
            changeMonth: true,
            changeYear: true
        });
    });
</script>
@endsection