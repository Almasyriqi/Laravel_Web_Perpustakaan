@extends('layouts.adminlte')

@section('title', 'Data Kategori Buku')

@section('content-custom')
    <div class="row">
        <div class="col-lg-12 margin-tb">
            <div class="pull-left mt-2">
                <h2>Data Kategori Buku Perpustakaan</h2><hr>
            </div>
            <div class="float-right my-2">
                @if (Auth::user()->role == 'admin')
                <a class="btn btn-success" href="/admin/kategori/create"><i class="fas fa-arrow-circle-down"></i>
                    Input Kategori Buku</a>
                @else
                <a class="btn btn-success" href="/petugas/kategori/create"><i class="fas fa-arrow-circle-down"></i>
                    Input Kategori Buku</a>
                @endif
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
                <th>Nama</th>
                <th>Keterangan</th>
                <th width="320px">Action</th>
            </tr>
        </thead>
        <tbody>
            @foreach ($paginate as $kategori)
                <tr>
                    <td>{{ $kategori->id }}</td>
                    <td>{{ $kategori->nama }}</td>
                    <td>{{ $kategori->keterangan }}</td>
                    <td>
                        @if (Auth::user()->role == 'admin')
                        <a class="btn btn-info" href="/admin/kategori/{{ $kategori->id }}">
                            <i class="fas fa-eye"></i> Show</a>

                        <a class="btn btn-primary" href="/admin/kategori/{{ $kategori->id }}/edit">
                            <i class="fas fa-pencil-alt"></i> Edit</a>

                        <a class="btn btn-danger" href="" data-toggle="modal" id="smallButton" data-target="#smallModal"
                            data-attr="/admin/kategori/delete/{{ $kategori->id }}" title="Delete Kategori Buku">
                            <i class="fas fa-trash"></i> Delete
                        </a>
                        @else
                        <a class="btn btn-info" href="/petugas/kategori/{{ $kategori->id }}">
                            <i class="fas fa-eye"></i> Show</a>

                        <a class="btn btn-primary" href="/petugas/kategori/{{ $kategori->id }}/edit">
                            <i class="fas fa-pencil-alt"></i> Edit</a>

                        <a class="btn btn-danger" href="" data-toggle="modal" id="smallButton" data-target="#smallModal"
                            data-attr="/petugas/kategori/delete/{{ $kategori->id }}" title="Delete Kategori Buku">
                            <i class="fas fa-trash"></i> Delete
                        </a>
                        @endif
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
        $(function() {
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
