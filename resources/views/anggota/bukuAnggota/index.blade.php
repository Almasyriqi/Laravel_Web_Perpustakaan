@extends('layouts.adminlte')

@section('title', 'Katalog Buku Polinema Library')

@section('content-custom')
<div class="row">
    <div class="col-lg-12 margin-tb">
        <div class="pull-left mt-2">
            <h2>Katalog Buku Perpustakaan</h2>
            <hr>
        </div>
    </div>
</div>

@if ($message = Session::get('success'))
<div class="alert alert-success">
    <p>{{ $message }}</p>
</div>
@endif
@include('partials.errors')

{{-- Form pencarian & filter (GET, parameter ikut terbawa di link paginasi) --}}
<form method="get" action="/anggota/buku" class="card card-outline card-primary mb-4" id="form-katalog">
    <div class="card-body pb-2">
        <div class="form-row align-items-end">
            <div class="form-group col-md-5">
                <label for="q">Kata kunci</label>
                <input type="search" name="q" id="q" class="form-control" value="{{ $filter['q'] }}"
                    placeholder="Judul, penulis, atau penerbit" maxlength="100">
            </div>
            <div class="form-group col-md-3">
                <label for="kategori">Kategori</label>
                <select name="kategori" id="kategori" class="form-control">
                    <option value="">Semua kategori</option>
                    @foreach ($kategori as $k)
                    <option value="{{ $k->id }}" @selected($k->id === $filter['kategori'])>{{ $k->nama }}</option>
                    @endforeach
                </select>
            </div>
            <div class="form-group col-md-2">
                <div class="custom-control custom-checkbox mb-2">
                    <input type="checkbox" name="tersedia" value="1" id="tersedia" class="custom-control-input"
                        @checked($filter['tersedia'])>
                    <label for="tersedia" class="custom-control-label">Hanya yang tersedia</label>
                </div>
            </div>
            <div class="form-group col-md-2 d-flex">
                <button type="submit" class="btn btn-primary flex-fill mr-1">
                    <i class="fas fa-search"></i> Cari
                </button>
                <a href="/anggota/buku" class="btn btn-outline-secondary" title="Hapus filter">
                    <i class="fas fa-times"></i>
                </a>
            </div>
        </div>
    </div>
</form>

<p class="text-muted">
    Menampilkan <b>{{ $buku->count() }}</b> dari <b>{{ $buku->total() }}</b> buku
    @if ($filter['q'] !== '') untuk kata kunci "<b>{{ $filter['q'] }}</b>" @endif
</p>

{{-- Grid kartu buku --}}
<div class="row">
    @forelse ($buku as $item)
    <div class="col-6 col-md-4 col-lg-3 d-flex">
        <div class="card w-100 shadow-sm kartu-buku">
            <a href="/anggota/buku/{{ $item->id }}" class="kartu-buku-sampul">
                @if ($item->gambar_url !== '')
                <img src="{{ $item->gambar_url }}" alt="Sampul {{ $item->judul }}" class="card-img-top" loading="lazy">
                @else
                <div class="kartu-buku-kosong"><i class="fas fa-book fa-3x"></i></div>
                @endif
            </a>
            <div class="card-body p-3">
                <span class="badge badge-info mb-1">{{ $item->kategori?->nama ?? 'Tanpa kategori' }}</span>
                @if ($item->stok > 0)
                <span class="badge badge-success mb-1">Stok {{ $item->stok }}</span>
                @else
                <span class="badge badge-danger mb-1">Stok habis</span>
                @endif
                <h5 class="card-title mb-1 text-truncate" title="{{ $item->judul }}">{{ $item->judul }}</h5>
                <p class="card-text text-muted small mb-0 text-truncate" title="{{ $item->penulis }}">
                    <i class="fas fa-pen-nib"></i> {{ $item->penulis }}
                </p>
                <p class="card-text text-muted small text-truncate" title="{{ $item->penerbit }}">
                    <i class="fas fa-building"></i> {{ $item->penerbit }}
                </p>
            </div>
            <div class="card-footer bg-white d-flex flex-wrap p-2">
                <a class="btn btn-sm btn-info flex-fill m-1" href="/anggota/buku/{{ $item->id }}">
                    <i class="fas fa-eye"></i> Detail</a>
                @if ($item->stok > 0)
                <a class="btn btn-sm btn-success flex-fill m-1" href="" data-toggle="modal" id="smallButton"
                    data-target="#smallModal" data-attr="/anggota/modal/pinjam/{{ $item->id }}" title="Pinjam Buku">
                    <i class="fas fa-book"></i> Pinjam
                </a>
                @else
                <button type="button" class="btn btn-sm btn-secondary flex-fill m-1" disabled>
                    <i class="fas fa-book"></i> Habis
                </button>
                @endif
            </div>
        </div>
    </div>
    @empty
    <div class="col-12">
        <div class="alert alert-warning">
            <i class="icon fas fa-exclamation-triangle"></i>
            Tidak ada buku yang cocok dengan pencarian Anda.
        </div>
    </div>
    @endforelse
</div>

<div class="d-flex justify-content-center">
    {{ $buku->links() }}
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

        // Ganti kategori / centang "tersedia" langsung memuat ulang hasil
        $('#kategori, #tersedia').on('change', function() {
            $('#form-katalog').submit();
        });
</script>
@stop
