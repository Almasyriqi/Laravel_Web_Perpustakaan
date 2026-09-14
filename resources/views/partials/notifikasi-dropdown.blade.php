{{-- Isi dropdown lonceng navbar; dirender ke string oleh NotifikasiController::ringkas() --}}
<span class="dropdown-item dropdown-header">
    {{ $belumDibaca > 0 ? "$belumDibaca belum dibaca" : 'Tidak ada notifikasi baru' }}
</span>
@foreach ($terbaru as $n)
<div class="dropdown-divider"></div>
<a href="/notifikasi/{{ $n->id }}/buka" class="dropdown-item {{ $n->read_at ? 'text-muted' : 'font-weight-bold' }}">
    <i class="{{ $n->data['ikon'] ?? 'fas fa-bell' }} text-{{ $n->data['warna'] ?? 'info' }} mr-2"></i>
    <span class="text-truncate d-inline-block align-middle" style="max-width: 240px" title="{{ $n->data['pesan'] ?? '' }}">
        {{ $n->data['judul'] ?? 'Notifikasi' }}
    </span>
    <span class="float-right text-muted text-sm">{{ $n->created_at->diffForHumans(short: true) }}</span>
</a>
@endforeach
