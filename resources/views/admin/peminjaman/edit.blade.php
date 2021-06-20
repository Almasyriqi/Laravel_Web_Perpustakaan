@extends('layouts.adminlte')

@section('title', 'Edit Peminjaman')

@section('content_header')
<h1>Edit Peminjaman</h1>
@stop

@section('content-custom')
<div class="container mt-5">

    <div class="row justify-content-center align-items-center">
        <div class="card" style="width: 24rem;">
            <div class="card-header">
                Edit Peminjaman
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
                <form method="post" action="/admin/peminjaman/{{ $pinjam->id }}" id="myForm">
                    @csrf
                    @method('PUT')
                    <div class="form-group">
                        <label for="anggota">anggota</label>
                        <input type="anggota" name="anggota" class="form-control" id="anggota"
                            aria-describedby="anggota" readonly value="{{$pinjam->name}}">
                    </div>
                    <div class="form-group">
                        <label for="judul">Judul</label>
                        <input type="judul" name="judul" class="form-control" id="judul" aria-describedby="judul"
                            readonly value="{{$pinjam->buku->judul}}">
                    </div>
                    <div class="form-group">
                        <label for="jumlah">Jumlah</label>
                        <input type="jumlah" name="jumlah" class="form-control" id="jumlah" aria-describedby="jumlah"
                            value="{{$pinjam->jumlah}}">
                    </div>
                    <div class="form-group">
                        <label for="tgl_pinjam">Tanggal Pinjam</label>
                        <input type="date" name="tgl_pinjam" class="form-control" id="tgl_pinjam"
                            aria-describedby="tgl_pinjam" value="{{$pinjam->tgl_pinjam}}">
                    </div>
                    <div class="form-group">
                        <label for="tgl_kembali" id="label_kembali">Tanggal Kembali</label>
                        <input type="date" name="tgl_kembali" class="form-control" id="tgl_kembali"
                            aria-describedby="tgl_kembali" value="{{$pinjam->tgl_kembali}}">
                    </div>
                    <div class="form-group">
                        <label for="lama_pinjam" id="label_lama">Lama Pinjam</label>
                        <input type="lama_pinjam" name="lama_pinjam" class="form-control" id="lama_pinjam" readonly
                            value="{{$pinjam->lama_pinjam}}">
                    </div>
                    <div class="form-group">
                        <label for="denda" id="label_denda">Denda</label>
                        <input type="denda" name="denda" class="form-control" id="denda" readonly
                            value="{{$pinjam->denda}}">
                    </div>
                    <div class="form-group">
                        <label for="status">status</label>
                        @php
                        $status = ['konfirmasi', 'dipinjam', 'perpanjang', 'kembali'];
                        @endphp
                        <select name="status" class="form-control" id="status">
                            @foreach ($status as $item)
                            <option value="{{$item}}" {{$pinjam->status == $item ? 'selected' : ''}}>{{$item}}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="form-group">
                        <label for="perpanjang">Perpanjang</label>
                        @php
                        $perpanjang = [['key' => 'Iya', 'value' => 1], ['key' => 'Tidak', 'value' => 0]];
                        @endphp
                        <select name="perpanjang" class="form-control" id="perpanjang">
                            @foreach ($perpanjang as $item)
                            <option value="{{$item['value']}}"
                                {{$pinjam->perpanjang == $item['value'] ? 'selected' : ''}}>{{$item['key']}}</option>
                            @endforeach
                        </select>
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
<script>
    $(document).ready(function(){
        var status = $("#status").val();
        if (status != 'kembali') {
            $("#tgl_kembali").hide();
            $("#lama_pinjam").hide();
            $("#denda").hide();
            $("#label_kembali").hide();
            $("#label_lama").hide();
            $("#label_denda").hide();   
        }
            $("#status").change(function(){
                $(this).find("option:selected").each(function(){
                    var optionValue = $(this).attr("value");
                    if(optionValue == 'kembali') {
                        $("#tgl_kembali").show();
                        $("#lama_pinjam").show();
                        $("#denda").show();
                        $("#label_kembali").show();
                        $("#label_lama").show();
                        $("#label_denda").show();
                    } else {
                        $("#tgl_kembali").hide();
                        $("#lama_pinjam").hide();
                        $("#denda").hide();
                        $("#label_kembali").hide();
                        $("#label_lama").hide();
                        $("#label_denda").hide();
                    }
                })
            });

            // Hitung lama pinjam
            $("#tgl_kembali").change(function(){
            var a=$('#tgl_pinjam').val();
            var b=$('#tgl_kembali').val();

            var startDay = new Date(a);  
            var endDay = new Date(b); 
            var millisBetween = endDay.getTime() - startDay.getTime();  
            var days = millisBetween / (1000 * 3600 * 24);
            if(days > 0){
                var selisih = Math.round(Math.abs(days));
            }
            else{
                var selisih = 0;
            }   
            $('#lama_pinjam').val(selisih); 

            var lama_pinjam = $("#lama_pinjam").val();
                var perpanjang = $("#perpanjang").val();
                var denda = 0;
                if(perpanjang == 1){
                    if(lama_pinjam > 14){
                        lama_pinjam -= 14;
                        denda = lama_pinjam * 2000;
                        $('#denda').val(denda); 
                    } else{
                        $('#denda').val(denda);
                    }
                } else{
                    if(lama_pinjam > 7){
                        lama_pinjam -= 7;
                        denda = lama_pinjam * 2000;
                        $('#denda').val(denda);
                    } else{
                        $('#denda').val(denda);
                    }
                }
            });
        });
</script>
@endsection