@extends('adminlte::page')

@section('title', 'Home Petugas')

@section('content_header')
    <h1>Home Petugas</h1>
@stop

@section('content')
    <p>Selamat datang di Polinema Library {{$user->name}}.</p>
    @include('sweetalert::alert')
@stop

@section('css')
    <link rel="stylesheet" href="/css/admin_custom.css">
@stop

@section('js')

@stop