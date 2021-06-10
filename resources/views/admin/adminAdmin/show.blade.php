@extends('layouts.adminlte')

@section('title', 'Detail Anggota Admin')

@section('content_header')
    <h1>Detail Admin</h1>
@stop

@section('content')
<div class="container mt-5">
    <div class="row justify-content-center align-items-center">
        <div class="card" style="width: 24rem;">
            <div class="card-header">
                Detail Admin
            </div>
            <div class="card-body">
                <ul class="list-group list-group-flush">
                    <li class="list-group-item"><b>id: </b>{{ $admin->id }}</li>
                    <li class="list-group-item"><b>Username: </b>{{ $admin->user->username }}</li>
                    <li class="list-group-item"><b>Nama: </b>{{ $admin->user->name }}</li>
                    <li class="list-group-item"><b>No_Handphone: </b>{{ $admin->no_hp }}</li>
                    <li class="list-group-item"><b>Email: </b>{{ $admin->user->email }}</li>
                    <li class="list-group-item"><b>Alamat: </b>{{ $admin->alamat }}</li>
                </ul>
            </div>
            <a class="btn btn-success mt-3" href="{{ route('admin.index') }}">Kembali</a>

        </div>
    </div>
</div>
@endsection