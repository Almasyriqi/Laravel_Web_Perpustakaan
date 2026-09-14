{{--
    Bintang rating. Variabel: $rating (float|null), $jumlah (int|null, jumlah ulasan; sembunyikan bila null)
--}}
@php
    $nilai = round((float) ($rating ?? 0), 1);
    $bulat = (int) round($nilai);
@endphp
<span class="bintang text-warning text-nowrap" title="{{ $nilai > 0 ? "Rating $nilai dari 5" : 'Belum ada rating' }}">
    @for ($i = 1; $i <= 5; $i++)
        <i class="{{ $i <= $bulat ? 'fas' : 'far' }} fa-star"></i>
    @endfor
    @if ($nilai > 0)
        <span class="text-muted small">{{ number_format($nilai, 1) }}@isset($jumlah) ({{ $jumlah }})@endisset</span>
    @else
        <span class="text-muted small">Belum ada ulasan</span>
    @endif
</span>
