@extends('adminlte::page')

@section('title', 'Home Anggota')

@section('content_header')
    <h1>Home Anggota</h1>
@stop

@section('content')
    <p>Selamat datang di Polinema Library {{$user->name}}.</p>
    @include('sweetalert::alert')
@stop

@section('css')
    <link rel="stylesheet" href="/css/adminStyle.css">
@stop

@section('footer')
    @include('layouts.footer')
@endsection