@extends('layouts.adminlte')

@section('title', 'Data Peminjaman')

@section('content-custom')
<div class="row">
    <div class="col-lg-12 margin-tb">
        <div class="pull-left mt-2">
            <h2>Data Peminjaman Perpustakaan</h2>
            <hr>
        </div>
        <div class="float-right my-2">
            <a class="btn btn-success" href="/admin/peminjaman/create"><i class="fas fa-arrow-circle-down"></i> Input
                Peminjaman</a>
        </div>
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
            <th>Id</th>
            <th>Anggota</th>
            <th>Judul Buku</th>
            <th>Jumlah</th>
            <th>Tanggal Pinjam</th>
            <th>Status</th>
            <th width="320px">Action</th>
        </tr>
    </thead>
    <tbody>
        @foreach ($pinjam as $peminjaman)
        <tr>
            <td>{{ $peminjaman->id }}</td>
            <td>{{ $peminjaman->name }}</td>
            <td>{{ $peminjaman->judul }}</td>
            <td>{{$peminjaman->jumlah}}</td>
            <td>{{ date('d-m-Y', strtotime($peminjaman->tgl_pinjam)) }}</td>
            <td>{{ $peminjaman->status }}</td>
            <td>
                <a class="btn btn-info" href="/admin/peminjaman/{{  $peminjaman->id }}">
                    <i class="fas fa-eye"></i> Show</a>

                <a class="btn btn-primary" href="/admin/peminjaman/{{ $peminjaman->id }}/edit">
                    <i class="fas fa-pencil-alt"></i> Edit</a>

                <a class="btn btn-danger" href="" data-toggle="modal" id="smallButton" data-target="#smallModal"
                    data-attr="/admin/peminjaman/delete/{{ $peminjaman->id }}" title="Delete peminjaman">
                    <i class="fas fa-trash"></i> Delete
                </a>
            </td>
        </tr>
        @endforeach
    </tbody>
</table>

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