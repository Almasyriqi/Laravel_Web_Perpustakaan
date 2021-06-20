@extends('layouts.adminlte')

@section('title', 'Home Petugas')

@section('content_header')
    <h1>Selamat Datang Petugas</h1><hr>
@stop

@section('css-custom')
    <link href='fullcalendar/main.css' rel='stylesheet' />
@endsection

@section('content-custom')
    <div class="row">
        <div class="col-lg-4 col-6">
            <!-- small card -->
            <div class="small-box bg-success">
                <div class="inner">
                    <h3>{{$anggota}}</h3>

                    <p>Anggota</p>
                </div>
                <div class="icon">
                    <i class="ion ion-person-add"></i>
                </div>
                <a href="/petugas/anggota" class="small-box-footer">
                    More info <i class="fas fa-arrow-circle-right"></i>
                </a>
            </div>
        </div>
        <!-- ./col -->
        <div class="col-lg-4 col-6">
            <!-- small card -->
            <div class="small-box bg-info">
                <div class="inner">
                    <h3>{{$buku}}</h3>

                    <p>Buku</p>
                </div>
                <div class="icon">
                    <i class="fas fa-book"></i>
                </div>
                <a href="/petugas/buku" class="small-box-footer">
                    More info <i class="fas fa-arrow-circle-right"></i>
                </a>
            </div>
        </div>
        <!-- ./col -->
        <div class="col-lg-4 col-6">
            <!-- small card -->
            <div class="small-box bg-warning">
                <div class="inner">
                    <h3>{{$kategori}}</h3>

                    <p>Kategori</p>
                </div>
                <div class="icon">
                    <i class="fas fa-book-open"></i>
                </div>
                <a href="/petugas/kategori" class="small-box-footer">
                    More info <i class="fas fa-arrow-circle-right"></i>
                </a>
            </div>
        </div>
    </div>

    <div class="row">
        <div class="col-lg-7">
            <div class="card card-primary">
                <div class="card-header">
                    <h3 class="card-title">Aturan Peminjaman</h3>

                    <div class="card-tools">
                        <button type="button" class="btn btn-tool" data-card-widget="collapse">
                            <i class="fas fa-minus"></i>
                        </button>
                        <button type="button" class="btn btn-tool" data-card-widget="remove">
                            <i class="fas fa-times"></i>
                        </button>
                    </div>
                </div>
                <div class="card-body">
                    <div class="alert alert-info alert-dismissible" style="margin-top: 20px">
                        <h5><i class="icon fas fa-info"></i> Informasi Aturan Peminjaman</h5>
                        <ol>
                            <li>Waktu Peminjaman maksimal 7 hari</li>
                            <li>Peminjaman dapat diperpanjang maksimal 1 kali (total lama pinjam 14 hari)</li>
                            <li>Jika mengembalikan lebih dari waktu yang ditentukan akan dikenakan denda setiap judul Rp 2.000 / hari</li>
                            <li>Jika telah memilih buku dan klik pinjam, silahkan ke petugas untuk melakukan konfirmasi</li>
                            <li>Jika terlambat mengembalikan buku dan mendapat denda, wajib langsung bayar denda ke petugas saat mengembalikan buku</li>
                        </ol>
                      </div>
                </div>
                <!-- /.card-body -->
            </div>
        </div>
        <div class="col-lg-5">
            {{-- calendar --}}
            <div class="card bg-gradient-success">
                <div class="card-header border-0 ui-sortable-handle" style="cursor: move;">

                    <h3 class="card-title">
                        <i class="far fa-calendar-alt"></i>
                        Calendar
                    </h3>
                    <!-- tools card -->
                    <div class="card-tools">
                        <!-- button with a dropdown -->
                        <button type="button" class="btn btn-success btn-sm" data-card-widget="collapse">
                            <i class="fas fa-minus"></i>
                        </button>
                        <button type="button" class="btn btn-success btn-sm" data-card-widget="remove">
                            <i class="fas fa-times"></i>
                        </button>
                    </div>
                    <!-- /. tools -->
                </div>
                <!-- /.card-header -->
                <div class="card-body pt-0">
                    <!--The calendar -->
                    <div id="calendar" style="width: 100%">

                    </div>
                </div>
                <!-- /.card-body -->
            </div>
        </div>
    </div>
    @include('sweetalert::alert')
@stop

@section('js')
    <script src='fullcalendar/main.js'></script>
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            var calendarEl = document.getElementById('calendar');
            var calendar = new FullCalendar.Calendar(calendarEl, {
                initialView: 'dayGridMonth'
            });
            calendar.render();
        });

    </script>
@stop
