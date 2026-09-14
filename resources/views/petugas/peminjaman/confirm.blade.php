@extends('layouts.adminlte')

@section('title', 'Konfirmasi Peminjaman')

@section('content-custom')
<div class="row">
    <div class="col-lg-12 margin-tb">
        <div class="pull-left mt-2">
            <h2>Konfirmasi Peminjaman</h2>
            <hr>
        </div>
    </div>
</div>

@if ($message = Session::get('success'))
<div class="alert alert-success">
    <p>{{ $message }}</p>
</div>
@endif
@include('partials.errors')

<table class="table table-bordered" id="example">
    <thead>
        <tr>
            <th>NIM</th>
            <th>Nama</th>
            <th>Judul Buku</th>
            <th>Diajukan</th>
            <th width="220px">Action</th>
        </tr>
    </thead>
    <tbody>
        @foreach ($pinjam as $peminjaman)
        <tr>
            <td>{{ $peminjaman->anggota->nim }}</td>
            <td>{{ $peminjaman->anggota->user->name }}</td>
            <td>{{ $peminjaman->buku->judul }}</td>
            <td>{{ date('d-m-Y', strtotime($peminjaman->tgl_pinjam))}}</td>
            <td>
                <a class="btn btn-warning" href="" data-toggle="modal" id="Button" title="Konfirmasi Peminjaman"
                    data-target="#defaultModal" data-attr="/petugas/transaksi/confirm/{{  $peminjaman->id }}">
                    <i class="fas fa-check-circle"></i> Konfirmasi</a>
                <a class="btn btn-danger" href="" data-toggle="modal" id="smallButton" data-target="#smallModal"
                    data-attr="/petugas/transaksi/delete/{{ $peminjaman->id }}" title="Batal peminjaman">
                    <i class="fas fa-times-circle"></i> Batal
                </a>
            </td>
        </tr>
        @endforeach
    </tbody>
</table>

{{-- Antrean booking: naik otomatis ke tabel di atas begitu stok buku kembali --}}
<div class="card card-outline card-warning mt-4">
    <div class="card-header">
        <h3 class="card-title"><i class="fas fa-bookmark"></i> Antrean Booking ({{ $booking->count() }})</h3>
    </div>
    <div class="card-body p-0 table-responsive">
        <table class="table table-hover mb-0">
            <thead>
                <tr>
                    <th>NIM</th>
                    <th>Nama</th>
                    <th>Judul Buku</th>
                    <th>Stok</th>
                    <th>Tanggal Booking</th>
                    <th width="120px">Action</th>
                </tr>
            </thead>
            <tbody>
                @forelse ($booking as $antre)
                <tr>
                    <td>{{ $antre->anggota->nim }}</td>
                    <td>{{ $antre->anggota->user->name }}</td>
                    <td>{{ $antre->buku->judul }}</td>
                    <td>{{ $antre->buku->stok }}</td>
                    <td>{{ date('d-m-Y', strtotime($antre->tgl_pinjam)) }}</td>
                    <td>
                        <a class="btn btn-sm btn-danger" href="" data-toggle="modal" id="smallButton" data-target="#smallModal"
                            data-attr="/petugas/transaksi/delete/{{ $antre->id }}" title="Batalkan booking">
                            <i class="fas fa-times-circle"></i> Batal
                        </a>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="6" class="text-center text-muted py-3">Tidak ada booking yang menunggu stok.</td>
                </tr>
                @endforelse
            </tbody>
        </table>
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
<div class="modal fade" id="defaultModal" role="dialog"
    aria-hidden="true">
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
            aaSorting: [[3, 'desc']],
          });
        });
</script>
@stop