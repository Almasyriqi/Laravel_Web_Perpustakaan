@extends('layouts.adminlte')

@section('title', 'Data Petugas Admin')

@section('content-custom')
    <div class="row">
        <div class="col-lg-12 margin-tb">
            <div class="pull-left mt-2">
                <h2>Data Petugas Admin Perpustakaan</h2>
            </div>
            <div class="float-left my-4">
                <form action="/admin/petugas/cari/" method="GET">
                    <div class="input-group">
                        <input type="text" name="keyword" class="form-control" placeholder="Search users...">
                        <button type="submit" class="btn btn-primary">
                            <i class="fas fa-search"></i> Search
                        </button>
                    </div>
                </form>
            </div>
            <div class="float-right my-2">
                <a class="btn btn-success" href="{{ route('petugas.create') }}"><i class="fas fa-arrow-circle-down"></i> Input Petugas</a>
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
                <th width="140px">Tanggal Lahir</th>
                <th>No Handphone</th>
                <th>Email</th>
                <th width="320px">Action</th>
            </tr>
        </thead>
        <tbody>
            @foreach ($paginate as $petugas)
                <tr>
                    <td>{{ $petugas->id }}</td>
                    <td>{{ $petugas->user->name }}</td>
                    <td>{{isset($petugas->tgl_lahir) ? \Carbon\Carbon::parse($petugas->tgl_lahir)->toFormattedDateString() :''}}</td>
                    <td>{{ $petugas->no_hp }}</td>
                    <td>{{ $petugas->user->email }}</td>
                    <td>
                        <a class="btn btn-info" href="{{ route('petugas.show', $petugas->id) }}">
                            <i class="fas fa-eye"></i> Show</a>

                        <a class="btn btn-primary" href="{{ route('petugas.edit', $petugas->id) }}">
                            <i class="fas fa-pencil-alt"></i> Edit</a>

                        <a class="btn btn-danger" href="" data-toggle="modal" id="smallButton" data-target="#smallModal"
                            data-attr="/admin/petugas/delete/{{ $petugas->id }}" title="Delete petugas">
                            <i class="fas fa-trash"></i> Delete
                        </a>
                    </td>
                </tr>
            @endforeach
        </tbody>
    </table>
    <div class="row">
        <div class="col-md-12">
            <nav aria-label="Page navigation example" class="page">
                <ul class="pagination justify-content-center">
                    @if ($paginate->onFirstPage())
                        <li class="page-item disabled">
                            <a class="page-link" href={{ $paginate->previousPageUrl() }} tabindex="-1">Previous</a>
                        </li>
                    @else
                        <li class="page-item">
                            <a class="page-link" href={{ $paginate->previousPageUrl() }} tabindex="-1">Previous</a>
                        </li>
                    @endif

                    @for ($i = 1; $i <= $paginate->lastPage(); $i++)
                        @if ($i == $paginate->currentPage())
                            <li class="page-item active"><a class="page-link"
                                    href="/admin/petugas?page={{ $i }}">{{ $i }}</a></li>
                        @else
                            <li class="page-item"><a class="page-link"
                                    href="/admin/petugas?page={{ $i }}">{{ $i }}</a></li>
                        @endif
                    @endfor
                    <li class="page-item">
                        <a class="page-link" href={{ $paginate->nextPageUrl() }}>Next</a>
                    </li>
                </ul>
            </nav>
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
            "paging": false,
            "lengthChange": false,
            "searching": false,
            "ordering": true,
            "info": false,
            "autoWidth": false,
            "responsive": true,
          });
        });
      </script>
@stop
