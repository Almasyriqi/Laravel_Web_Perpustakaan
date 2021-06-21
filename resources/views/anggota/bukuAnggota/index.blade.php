@extends('layouts.adminlte')

@section('title', 'Daftar Buku Polinema Library')

@section('content-custom')
<div class="row">
    <div class="col-lg-12 margin-tb">
        <div class="pull-left mt-2">
            <h2>Daftar Buku Perpustakaan</h2>
            <hr>
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
            <th>Kategori</th>
            <th>Judul</th>
            <th>Penerbit</th>
            <th>Penulis</th>
            <th>Keterangan</th>
            <th>Stok</th>
            <th>Gambar</th>
            <th width="200px">Action</th>
        </tr>
    </thead>
    <tbody>
        @foreach ($paginate as $buku)
        <tr>
            <td>{{ $buku->id }}</td>
            <td>{{ $buku->kategori->nama }}</td>
            <td>{{ $buku->judul }}</td>
            <td>{{ $buku->penerbit }}</td>
            <td>{{ $buku->penulis }}</td>
            <td>{{ $buku->keterangan }}</td>
            <td>{{ $buku->stok }}</td>
            <td>
                <img width="150px" src="{{ $buku->gambar }}"></td>
            <td>
                <a class="btn btn-info" href="/anggota/buku/{{  $buku->id }}">
                    <i class="fas fa-eye"></i> Show</a>

                <a class="btn btn-success" href="" data-toggle="modal" id="smallButton" data-target="#smallModal"
                    data-attr="/anggota/modal/pinjam/{{ $buku->id }}" title="Pinjam Buku">
                    <i class="fas fa-book"></i> Pinjam
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