@extends('adminlte::page')

@section('title', 'Home Admin')

@section('content_header')
    <h1>Home Admin</h1>
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