@extends('adminlte::page')

@section('css')
    <link rel="stylesheet" href="https://code.ionicframework.com/ionicons/2.0.1/css/ionicons.min.css">
    <link rel="stylesheet" href="/css/adminStyle.css">
    @yield('css-admin')
@stop

@section('content')
    @yield('content-admin')
    @include('sweetalert::alert')
@stop

@section('footer')
    @include('layouts.footer')
@endsection