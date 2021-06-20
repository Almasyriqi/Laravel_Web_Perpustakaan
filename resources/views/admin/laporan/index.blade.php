@extends('layouts.adminlte')

@section('title', 'Data Peminjaman')

@section('content-custom')
<div class="row">
    <div class="col-lg-12 margin-tb">
        <div class="pull-left mt-2">
            <h2>Data Laporan Perpustakaan</h2>
            <hr>
        </div>
    </div>
</div>

@if ($message = Session::get('success'))
<div class="alert alert-success">
    <p>{{ $message }}</p>
</div>
@endif
@if (Auth::user()->role == 'admin')
                    <form method="post" action="/admin/laporan" id="myForm" enctype="multipart/form-data">
                    @else
                    <form method="post" action="/petugas/laporan" id="myForm" enctype="multipart/form-data">
                    @endif

    <table class="table table-bordered">

<table class="table table-bordered" id="example">
    <thead>
        <tr>
            <th>Id</th>
            <th>Anggota</th>
            <th>Judul Buku</th>
            <th>Jumlah</th>
            <th>Tanggal Pinjam</th>
            <th>Status</th>
        </tr>
    </thead>
    <tbody>

        @if (Auth::user()->role == 'admin')
        <a href="{{ route('admin.cetak_pdf') }}" class="btn btn-warning"><i class="fas fa-print"> Cetak Laporan</a></i>
        @else
        <a href="{{ route('petugas.cetak_pdf') }}" class="btn btn-warning"><i class="fas fa-print"> Cetak Laporan</a></i>
        @endif

        @foreach ($laporan as $lp)
        <tr>
            <td>{{ $lp->id }}</td>
            <td>{{ $lp->name }}</td>
            <td>{{ $lp->judul }}</td>
            <td>{{$lp->jumlah}}</td>
            <td>{{ date('d-m-Y', strtotime($lp->tgl_pinjam)) }}</td>
            <td>{{ $lp->status }}</td>
            <td>
            </td>
        </tr>
        @endforeach
    </tbody>
</table>

<div class="row">
    <div class="col-12">
      <div class="card">
        <div class="card-header">
          <h3 class="card-title">Select Month</h3>
        </div>
        <div class="card-body">
          <ul class="pagination pagination-month justify-content-center">
            <li class="page-item"><a class="page-link" href="#">«</a></li>
            <li class="page-item">
                <a class="page-link" href="#">
                    <p class="page-month">Jan</p>
                    <p class="page-year">2021</p>
                </a>
            </li>
            <li class="page-item active">
                <a class="page-link" href="#">
                    <p class="page-month">Feb</p>
                    <p class="page-year">2021</p>
                </a>
            </li>
            <li class="page-item">
                <a class="page-link" href="#">
                    <p class="page-month">Mar</p>
                    <p class="page-year">2021</p>
                </a>
            </li>
            <li class="page-item">
                <a class="page-link" href="#">
                    <p class="page-month">Apr</p>
                    <p class="page-year">2021</p>
                </a>
            </li>
            <li class="page-item">
                <a class="page-link" href="#">
                    <p class="page-month">May</p>
                    <p class="page-year">2021</p>
                </a>
            </li>
            <li class="page-item">
                <a class="page-link" href="#">
                    <p class="page-month">Jun</p>
                    <p class="page-year">2021</p>
                </a>
            </li>
            <li class="page-item">
                <a class="page-link" href="#">
                    <p class="page-month">Jul</p>
                    <p class="page-year">2021</p>
                </a>
            </li>
            <li class="page-item">
                <a class="page-link" href="#">
                    <p class="page-month">Aug</p>
                    <p class="page-year">2021</p>
                </a>
            </li>
            <li class="page-item">
                <a class="page-link" href="#">
                    <p class="page-month">Sep</p>
                    <p class="page-year">2021</p>
                </a>
            </li>
            <li class="page-item">
                <a class="page-link" href="#">
                    <p class="page-month">Oct</p>
                    <p class="page-year">2021</p>
                </a>
            </li>
            <li class="page-item">
                <a class="page-link" href="#">
                    <p class="page-month">Nov</p>
                    <p class="page-year">2021</p>
                </a>
            </li>
            <li class="page-item">
                <a class="page-link" href="#">
                    <p class="page-month">Dec</p>
                    <p class="page-year">2021</p>
                </a>
            </li>
            <li class="page-item"><a class="page-link" href="#">»</a></li>
          </ul>
        </div>
      </div>
    </div>
  </div>

<div class="modal fade" id="smallModal" tabindex="-1" role="dialog" aria-labelledby="smallModalLabel"
    aria-hidden="true">
    <div class="modal-dialog modal-sm" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body" id="smallBody">
                <div>
                    <!-- the result to be displayed apply here -->
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@section('js')
<script>
    // display a modal (small modal)
        $(document).on('click', '#smallButton', function(event) {
            event.preventDefault();
            let href = $(this).attr('data-attr');
            $.ajax({
                url: href,
                beforeSend: function() {
                    $('#loader').show();
                },
                // return the result
                success: function(result) {
                    $('#smallModal').modal("show");
                    $('#smallBody').html(result).show();
                },
                complete: function() {
                    $('#loader').hide();
                },
                error: function(jqXHR, testStatus, error) {
                    console.log(error);
                    alert("Page " + href + " cannot open. Error:" + error);
                    $('#loader').hide();
                },
                timeout: 8000
            })
        });

</script>
<script>
    $(function () {
          $('#example').DataTable({
            "paging": true,
            "lengthChange": false,
            "searching": true,
            "ordering": true,
            "info": false,
            "autoWidth": false,
            "responsive": true,
          });
        });
</script>
@stop