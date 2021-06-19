@extends('layouts.adminlte')

@section('title', 'Tambah Peminjaman')

@section('content_header')
<h1>Tambah Peminjaman</h1>
@stop

@section('content-custom')
<div class="container mt-5">
    <div class="row justify-content-center align-items-center">
        <div class="card" style="width: 24rem;">
            <div class="card-header">
                Tambah pinjam
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
                <form method="post" action="/petugas/transaksi" id="myForm">
                    @csrf
                    <div class="form-group">
                        <label for="anggota">anggota</label>
                        <select name="anggota" class="form-control select" id="anggota">
                            @foreach ($anggota as $a)
                            <option value="{{$a->nim}}">{{$a->user->name}}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="form-group">
                        <label for="judul">Judul</label>
                        <select name="judul" class="form-control select" id="judul">
                            @foreach ($buku as $b)
                            <option value="{{$b->id}}">{{$b->judul}}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="form-group">
                        <label for="jumlah">Jumlah</label>
                        <input type="jumlah" name="jumlah" class="form-control" id="jumlah" aria-describedby="jumlah"
                            value="{{old("jumlah")}}">
                    </div>
                    <button type="submit" class="btn btn-primary">Submit</button>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection

@section('js')
<script>
    $(document).ready(function() {
    $('.select').select2();
});
</script>
@endsection