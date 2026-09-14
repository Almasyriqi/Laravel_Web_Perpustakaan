@extends('layouts.adminlte')

@section('title', 'Detail Buku')

@section('content_header')
<h1>Detail Buku</h1>
@stop

@section('content')
<div class="container mt-4">
    @if ($message = Session::get('success'))
    <div class="alert alert-success">
        <p class="mb-0">{{ $message }}</p>
    </div>
    @endif
    @include('partials.errors')

    <div class="row">
        {{-- Kartu detail --}}
        <div class="col-lg-5 mb-4">
            <div class="card h-100">
                <div class="card-header">
                    Detail Buku
                </div>
                <div class="card-body">
                    <ul class="list-group list-group-flush">
                        <li class="list-group-item"><b>Id: </b>{{ $buku->id }}</li>
                        <li class="list-group-item"><b>Kategori: </b>{{ $buku->kategori->nama }}</li>
                        <li class="list-group-item"><b>Judul: </b>{{ $buku->judul }}</li>
                        <li class="list-group-item"><b>Penerbit: </b>{{$buku->penerbit }}</li>
                        <li class="list-group-item"><b>Penulis: </b>{{ $buku->penulis }}</li>
                        <li class="list-group-item"><b>Keterangan: </b>{{ $buku->keterangan }}</li>
                        <li class="list-group-item"><b>Stok: </b>
                            @if ($buku->stok > 0)
                                <span class="badge badge-success">{{ $buku->stok }}</span>
                            @else
                                <span class="badge badge-danger">Habis</span>
                            @endif
                        </li>
                        <li class="list-group-item"><b>Rating: </b>
                            @include('partials.bintang', ['rating' => $buku->ulasan_avg_rating, 'jumlah' => $buku->ulasan_count])
                        </li>
                        <li class="list-group-item"><img width="150px" src="{{ $buku->gambar_url }}" alt="Sampul {{ $buku->judul }}"></li>
                    </ul>
                </div>
                <div class="card-footer d-flex flex-wrap">
                    @if ($buku->stok > 0)
                    <a class="btn btn-success m-1" href="" data-toggle="modal" id="smallButton" data-target="#smallModal"
                        data-attr="/anggota/modal/pinjam/{{ $buku->id }}" title="Pinjam Buku">
                        <i class="fas fa-book"></i> Pinjam
                    </a>
                    @else
                    <a class="btn btn-warning m-1" href="" data-toggle="modal" id="smallButton" data-target="#smallModal"
                        data-attr="/anggota/modal/booking/{{ $buku->id }}" title="Booking (antre saat stok habis)">
                        <i class="fas fa-bookmark"></i> Booking
                    </a>
                    @endif
                    <a class="btn btn-secondary m-1" href="/anggota/buku"><i class="fas fa-undo"></i> Kembali</a>
                </div>
            </div>
        </div>

        {{-- Ulasan --}}
        <div class="col-lg-7 mb-4">
            @if ($bolehMengulas)
            <div class="card card-outline card-warning">
                <div class="card-header">
                    <h3 class="card-title">
                        <i class="fas fa-star"></i> {{ $ulasanSaya ? 'Ubah ulasan Anda' : 'Tulis ulasan' }}
                    </h3>
                </div>
                <form method="post" action="/anggota/buku/{{ $buku->id }}/ulasan">
                    @csrf
                    <div class="card-body">
                        <div class="form-group">
                            <label class="d-block">Rating</label>
                            <div class="pilih-bintang">
                                @for ($i = 5; $i >= 1; $i--)
                                <input type="radio" name="rating" id="rating{{ $i }}" value="{{ $i }}"
                                    @checked((int) old('rating', $ulasanSaya?->rating) === $i) required>
                                <label for="rating{{ $i }}" title="{{ $i }} bintang"><i class="fas fa-star"></i></label>
                                @endfor
                            </div>
                        </div>
                        <div class="form-group mb-0">
                            <label for="komentar">Komentar <small class="text-muted">(opsional)</small></label>
                            <textarea name="komentar" id="komentar" class="form-control" rows="3" maxlength="1000"
                                placeholder="Bagaimana pendapat Anda tentang buku ini?">{{ old('komentar', $ulasanSaya?->komentar) }}</textarea>
                        </div>
                    </div>
                    <div class="card-footer d-flex flex-wrap">
                        <button type="submit" class="btn btn-warning m-1">
                            <i class="fas fa-paper-plane"></i> {{ $ulasanSaya ? 'Simpan perubahan' : 'Kirim ulasan' }}
                        </button>
                        @if ($ulasanSaya)
                        <button type="submit" class="btn btn-outline-danger m-1" form="form-hapus-ulasan">
                            <i class="fas fa-trash"></i> Hapus ulasan saya
                        </button>
                        @endif
                    </div>
                </form>
                @if ($ulasanSaya)
                <form method="post" action="/anggota/buku/{{ $buku->id }}/ulasan" id="form-hapus-ulasan">
                    @csrf
                    @method('DELETE')
                </form>
                @endif
            </div>
            @else
            <div class="alert alert-light border">
                <i class="fas fa-info-circle"></i>
                Ulasan bisa ditulis setelah Anda meminjam dan mengembalikan buku ini.
            </div>
            @endif

            <div class="card">
                <div class="card-header">
                    <h3 class="card-title"><i class="fas fa-comments"></i> Ulasan Anggota ({{ $ulasan->count() }})</h3>
                </div>
                <div class="card-body p-0">
                    <ul class="list-group list-group-flush">
                        @forelse ($ulasan as $u)
                        <li class="list-group-item">
                            <div class="d-flex justify-content-between flex-wrap">
                                <b>{{ $u->anggota?->user?->name ?? 'Anggota' }}</b>
                                <small class="text-muted">{{ $u->updated_at->format('d-m-Y') }}</small>
                            </div>
                            @include('partials.bintang', ['rating' => $u->rating])
                            @if ($u->komentar)
                            <p class="mb-0 mt-1">{{ $u->komentar }}</p>
                            @endif
                        </li>
                        @empty
                        <li class="list-group-item text-muted">Belum ada ulasan untuk buku ini. Jadilah yang pertama!</li>
                        @endforelse
                    </ul>
                </div>
            </div>
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
