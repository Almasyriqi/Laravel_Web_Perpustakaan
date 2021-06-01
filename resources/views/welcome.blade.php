@extends('layouts.app')

@section('title')
    Selamat Datang
@endsection

@section('css')
    <link rel="stylesheet" href="/css/style.css">    
@endsection

<body>
    <header id="header" class="header-transparent">
        <div class="container">
    
          <div id="logo" class="pull-left">
            <a href="/"><h2>Polinema Library</h2></a>
          </div>
        </div>
      </header><!-- End Header -->
    
      <!-- ======= Hero Section ======= -->
      <div id="hero">
        <div class="hero-container" data-aos="zoom-in" data-aos-delay="100">
          <h1>Welcome to Polinema Library</h1>
          <h2>We are here to make it easier for you to borrow books</h2>
          <a href={{route('login')}} class="btn-get-started">Login</a>
          <a href={{route('register')}} class="btn-get-started">Register</a>
        </div>
      </div>
</body>