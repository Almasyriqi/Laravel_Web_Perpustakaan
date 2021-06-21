@extends('layouts.adminlte')

@section('title', 'Detail Buku')

@section('content_header')
<h1>Detail Buku</h1>
@stop

@section('content')
<div class="container mt-5">
    <div class="row justify-content-center align-items-center">
        <div class="card" style="width: 24rem;">
            <div class="card-header">
                Detail Buku
            </div>
            <div class="card-body">
                <ul class="list-group list-group-flush">
                    <li class="list-group-item"><b>Id: </b>{{ $buku->id }}</li>
                    <li class="list-group-item"><b>Kategori Id: </b>{{ $buku->kategori->nama }}</li>
                    <li class="list-group-item"><b>Judul: </b>{{ $buku->judul }}</li>
                    <li class="list-group-item"><b>Penerbit: </b>{{$buku->penerbit }}</li>
                    <li class="list-group-item"><b>Penulis: </b>{{ $buku->penulis }}</li>
                    <li class="list-group-item"><b>Keterangan: </b>{{ $buku->keterangan }}</li>
                    <li class="list-group-item"><b>Stok: </b>{{ $buku->stok }}</li>
                    <li class="list-group-item"><b>Gambar: </b></li>
                    <li class="list-group-item"><img width="150px" src="{{ $buku->gambar }}"></li>
                </ul>
            </div>
            <a class="btn btn-info" href="" data-toggle="modal" id="smallButton" data-target="#smallModal"
                data-attr="/anggota/modal/pinjam/{{ $buku->id }}" title="Pinjam Buku">
                <i class="fas fa-book"></i> Pinjam
            </a>
            <a class="btn btn-success mt-3" href="/anggota/buku"><i class="fas fa-undo"></i> Kembali</a>
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
@stop