@extends('layouts.adminlte')

@section('title', 'Tambah Peminjaman')

@section('content_header')
<h1>Tambah Peminjaman</h1>
@stop

@section('content-custom')
<div class="container mt-5">
    <div class="row justify-content-center align-items-center">
        <div class="card" style="width: 24rem; max-width: 100%;">
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
                {{-- Scan QR kartu anggota (AG-nim) / label buku (BK-id): scanner USB mengetik kode + Enter --}}
                <div class="form-group">
                    <label for="scan"><i class="fas fa-qrcode"></i> Scan kode QR</label>
                    <input type="text" class="form-control" id="scan" autofocus autocomplete="off"
                        placeholder="Arahkan scanner ke kartu anggota atau label buku, lalu Enter">
                    <small class="form-text text-muted" id="scan-info">Kode AG-… memilih anggota, BK-… memilih buku.</small>
                </div>
                <form method="post" action="/petugas/transaksi" id="myForm">
                    @csrf
                    <div class="form-group">
                        <label for="anggota">anggota</label>
                        <select name="anggota" class="form-control select" id="anggota">
                            @foreach ($anggota as $a)
                            <option value="{{$a->nim}}">{{$a->user->name}} ({{$a->nim}})</option>
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

        // Hasil scan: AG-<nim> memilih anggota, BK-<id> memilih buku, lalu fokus ke kolom berikutnya
        $('#scan').on('keydown', function(e) {
            if (e.key !== 'Enter') return;
            e.preventDefault();

            var kode = $(this).val().trim().toUpperCase();
            var cocok = kode.match(/^(AG|BK)-(\d+)$/);
            var info = $('#scan-info');

            if (!cocok) {
                info.removeClass('text-muted text-success').addClass('text-danger').text('Kode "' + kode + '" tidak dikenal.');
            } else {
                var target = cocok[1] === 'AG' ? '#anggota' : '#judul';
                var ada = $(target + ' option[value="' + cocok[2] + '"]').length > 0;

                if (ada) {
                    $(target).val(cocok[2]).trigger('change');
                    info.removeClass('text-muted text-danger').addClass('text-success')
                        .text((cocok[1] === 'AG' ? 'Anggota' : 'Buku') + ' terpilih: ' + $(target + ' option:selected').text());
                    if (cocok[1] === 'BK') $('#jumlah').focus();
                } else {
                    info.removeClass('text-muted text-success').addClass('text-danger').text('Kode ' + kode + ' tidak ditemukan.');
                }
            }

            $(this).val('');
            if (!cocok || cocok[1] === 'AG') $(this).focus();
        });
    });
</script>
@endsection