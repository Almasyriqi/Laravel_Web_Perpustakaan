{{-- Isi dropdown lonceng navbar; dirender ke string oleh NotifikasiController::ringkas() --}}
<span class="dropdown-item dropdown-header">
    {{ $belumDibaca > 0 ? "$belumDibaca belum dibaca" : 'Tidak ada notifikasi baru' }}
</span>
@foreach ($terbaru as $n)
<div class="dropdown-divider"></div>
<a href="/notifikasi/{{ $n->id }}/buka" class="dropdown-item d-flex align-items-center {{ $n->read_at ? 'text-muted' : 'font-weight-bold' }}"
    title="{{ $n->data['pesan'] ?? '' }}">
    <i class="{{ $n->data['ikon'] ?? 'fas fa-bell' }} text-{{ $n->data['warna'] ?? 'info' }} mr-2"></i>
    <span class="flex-fill text-truncate">{{ $n->data['judul'] ?? 'Notifikasi' }}</span>
    <small class="text-muted ml-2 text-nowrap font-weight-normal">{{ $n->created_at->diffForHumans(short: true) }}</small>
</a>
@endforeach
