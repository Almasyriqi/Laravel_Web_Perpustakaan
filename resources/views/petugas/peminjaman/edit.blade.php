@extends('layouts.adminlte')

@section('title', 'Data Peminjaman')

@section('content-custom')
<div class="row">
    <div class="col-lg-12 margin-tb">
        <div class="pull-left mt-2">
            <h2>Data Peminjaman Perpustakaan</h2>
            <hr>
        </div>
    </div>
    <div class="col-lg-12">
        <p>
            <b>Nama :</b> {{ $anggota->user->name }} <br>
            <b>NIM :</b> {{ $anggota->nim }} <br>
            <b>Jurusan :</b> {{ $anggota->jurusan }} <br><br>
        </p>
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
            <td>Rp {{ $peminjaman->denda }}</td>
            <td>{{ $peminjaman->status }}</td>
            <td>
                <a class="btn btn-info" href="/petugas/transaksi/{{  $peminjaman->id }}">
                    <i class="fas fa-eye"></i> Show</a>

                @php
                $tgl1 = new DateTime($peminjaman->tgl_pinjam);
                $tgl2 = new DateTime(now());
                $d = $tgl2->diff($tgl1)->days + 1;
                @endphp
                @if ($peminjaman->status == 'dipinjam')
                    @if ($d < 7)
                    <a class="btn btn-warning" href="/petugas/transaksi/{{ $peminjaman->id }}/edit">
                        <i class="fas fa-edit"></i> Perpanjang</a>
                    @endif
                @endif
                @if ($peminjaman->status != 'kembali')
                <a class="btn btn-success" href="/petugas/transaksi/{{ $peminjaman->id }}/edit">
                    <i class="fas fa-undo"></i> Kembali</a>
                @endif
            </td>
        </tr>
        @endforeach
    </tbody>
</table>
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