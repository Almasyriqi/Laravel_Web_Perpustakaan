@extends('layouts.adminlte')

@section('title', 'Data Peminjaman')

@section('content-custom')
<div class="row">
    <div class="col-lg-12 margin-tb">
        <div class="pull-left mt-2">
            <h2><a href="/petugas/transaksi" class="back">
                    <</a> Detail Peminjaman Perpustakaan</h2> <hr>
        </div>
    </div>
    <div class="col-lg-12">
        <h5>
            <b>Nama :</b> {{ $anggota->user->name }} <br>
            <b>NIM :</b> {{ $anggota->nim }} <br>
            <b>Jurusan :</b> {{ $anggota->jurusan }} <br><br>
        </h5>
        <hr>
    </div>
</div>

@if ($message = Session::get('success'))
<div class="alert alert-success">
    <p>{{ $message }}</p>
</div>
@endif

<table class="table table-bordered" id="example">
    <thead>
        <tr>
            <th>Judul Buku</th>
            <th>Jumlah</th>
            <th>Tanggal Pinjam</th>
            <th>Denda</th>
            <th>Status</th>
            <th width="320px">Action</th>
        </tr>
    </thead>
    <tbody>
        @foreach ($pinjam as $peminjaman)
        <tr>
            <td>{{ $peminjaman->judul }}</td>
            <td>{{$peminjaman->jumlah}}</td>
            <td>{{ date('d-m-Y', strtotime($peminjaman->tgl_pinjam)) }}</td>
            <td>@currency($peminjaman->denda)</td>
            <td>{{ $peminjaman->status }}</td>
            <td>
                <a class="btn btn-info" href="/petugas/transaksi/{{  $peminjaman->id }}">
                    <i class="fas fa-eye"></i> Show</a>

                @php
                $tgl1 = new DateTime($peminjaman->tgl_pinjam);
                $tgl2 = new DateTime(now());
                $d = $tgl2->diff($tgl1)->days;
                @endphp
                @if ($peminjaman->status == 'dipinjam')
                    @if ($d <= 7) 
                        <a class="btn btn-warning" href="" data-toggle="modal" id="Button"
                        title="Perpanjang Peminjaman" data-target="#defaultModal"
                        data-attr="/petugas/transaksi/perpanjang/{{  $peminjaman->id }}">
                        <i class="fas fa-edit"></i> Perpanjang</a>
                    @endif
                @endif
                @if ($peminjaman->status != 'kembali')
                    <a class="btn btn-success" href="" data-toggle="modal" id="smallButton" data-target="#smallModal"
                        data-attr="/petugas/transaksi/kembali/{{ $peminjaman->id }}" title="Mengembalikan Buku">
                        <i class="fas fa-undo"></i> Kembali
                    </a>
                @endif
            </td>
        </tr>
        @endforeach
    </tbody>
</table>

<div class="modal fade" id="defaultModal" role="dialog" aria-hidden="true">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body" id="defaultBody">
                <div>
                    <!-- the result to be displayed apply here -->
                </div>
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

// display default modal
$(document).on('click', '#Button', function(event) {
            event.preventDefault();
            let href = $(this).attr('data-attr');
            $.ajax({
                url: href,
                beforeSend: function() {
                    $('#loader').show();
                },
                // return the result
                success: function(result) {
                    $('#defaultModal').modal("show");
                    $('#defaultBody').html(result).show();
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