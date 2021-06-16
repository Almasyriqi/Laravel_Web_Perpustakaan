@extends('layouts.adminlte')

@section('title', 'Edit Buku')

@section('content_header')
    <h1>Edit Buku</h1>
@stop

@section('css-custom')
<link rel="stylesheet" href="//code.jquery.com/ui/1.12.1/themes/base/jquery-ui.css">
@endsection

@section('content-custom')
    <div class="container mt-5">

        <div class="row justify-content-center align-items-center">
            <div class="card" style="width: 24rem;">
                <div class="card-header">
                    Edit Buku
                </div>
                <div class="card-body">
                    @if ($errors->any())
                        <div class="alert alert-danger">
                            <strong>Whoops!</strong> There were some problems with your input.<br><br>
                            <ul>
                                @foreach ($errors->all() as $error)
                                    <li>{{ $error }}</li>
                                @endforeach
                            </ul>
                        </div>
                    @endif
                    @if (Auth::user()->role == 'admin')
                    <form method="post" action="/admin/buku/{{ $buku->id }}" id="myForm" enctype="multipart/form-data">
                    @else
                    <form method="post" action="/petugas/buku/{{ $buku->id }}" id="myForm" enctype="multipart/form-data">
                    @endif
                        @csrf
                        @method('PUT')
                        <div class="form-group">
                            <label for="kategori">Kategori</label>
                            <select name="kategori" class="form-control" id="kategori">
                                @foreach ($kategori as $k)
                                    <option value="{{$k->id}}" {{$buku->kategori_id == $k->id ? 'selected' : ''}}>{{$k->nama}}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="form-group">
                            <label for="judul">Judul</label>
                            <input type="judul" name="judul" class="form-control" id="judul"
                                aria-describedby="judul" value="{{ $buku->judul }}">
                        </div>
                        <div class="form-group">
                            <label for="penerbit">Penerbit</label>
                            <input type="penerbit" name="penerbit" class="form-control" id="penerbit"
                                aria-describedby="penerbit" value="{{ $buku->penerbit }}">
                        </div>
                        <div class="form-group">
                            <label for="penulis">Penulis</label>
                            <input type="penulis" name="penulis" class="form-control" id="penulis"
                                aria-describedby="penulis" value="{{ $buku->penulis }}">
                        </div>
                        <div class="form-group">
                            <label for="keterangan">Keterangan</label>
                            <textarea type="keterangan" name="keterangan" class="form-control" id="keterangan" aria-describedby="keterangan"
                                value="{{ $buku->keterangan }}">{{ $buku->keterangan }}</textarea>
                        </div>
                        <div class="form-group">
                            <label for="stok">Stok</label>
                            <input type="stok" name="stok" class="form-control" id="stok" aria-describedby="stok"
                                value="{{ $buku->stok }}">
                        </div>
                        <div class="form-group">
                            <label for="gambar">Gambar</label>
                            <input type="file" name="gambar" class="form-control" id="field-file"
                                aria-describedby="gambar" value="{{ $buku->gambar }}">
                        </div>
                        <button type="submit" class="btn btn-primary">Submit</button>
                    </form>
                </div>
            </div>
        </div>
    </div>
@endsection

@section('js')
<script src="https://code.jquery.com/ui/1.12.1/jquery-ui.js"></script>
<script>
    $( function() {
        $( ".datepicker" ).datepicker({
            dateFormat: 'yy-mm-dd',
            changeMonth: true,
            changeYear: true
        });
    });
</script>
@endsection