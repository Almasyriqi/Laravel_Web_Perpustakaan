{{--
    Form pencarian server-side untuk halaman daftar admin/petugas.
    Props: action (URL), q (kata kunci saat ini), placeholder; slot untuk select filter tambahan.
--}}
@props(['action', 'q' => '', 'placeholder' => 'Cari…'])

<form method="get" action="{{ $action }}" class="form-inline mb-3" role="search">
    <div class="input-group mr-2 mb-2">
        <input type="search" name="q" class="form-control" value="{{ $q }}" placeholder="{{ $placeholder }}" maxlength="100">
        <div class="input-group-append">
            <button type="submit" class="btn btn-primary" title="Cari"><i class="fas fa-search"></i></button>
        </div>
    </div>
    {{ $slot }}
    @if ($q !== '' || $attributes->has('data-terfilter'))
    <a href="{{ $action }}" class="btn btn-outline-secondary mb-2" title="Hapus filter"><i class="fas fa-times"></i> Reset</a>
    @endif
</form>
