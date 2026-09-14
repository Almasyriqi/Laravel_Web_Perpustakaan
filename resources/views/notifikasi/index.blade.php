@extends('layouts.adminlte')

@section('title', 'Notifikasi')

@section('content-custom')
<div class="row">
    <div class="col-lg-12 margin-tb">
        <div class="pull-left mt-2">
            <h2><i class="fas fa-bell"></i> Notifikasi</h2>
            <p class="text-muted mb-0">{{ $belumDibaca }} belum dibaca dari {{ $notifikasi->total() }} notifikasi</p>
            <hr>
        </div>
    </div>
</div>

@if ($message = Session::get('success'))
<div class="alert alert-success">
    <p class="mb-0">{{ $message }}</p>
</div>
@endif

@if ($belumDibaca > 0)
<form method="post" action="/notifikasi/baca-semua" class="mb-3">
    @csrf
    <button type="submit" class="btn btn-outline-primary btn-sm">
        <i class="fas fa-check-double"></i> Tandai semua sudah dibaca
    </button>
</form>
@endif

<div class="card">
    <div class="card-body p-0">
        <ul class="list-group list-group-flush">
            @forelse ($notifikasi as $n)
            <li class="list-group-item {{ $n->read_at ? '' : 'bg-light' }}">
                <a href="/notifikasi/{{ $n->id }}/buka" class="d-flex align-items-start text-reset">
                    <span class="mr-3 mt-1 text-{{ $n->data['warna'] ?? 'info' }}">
                        <i class="{{ $n->data['ikon'] ?? 'fas fa-bell' }} fa-lg"></i>
                    </span>
                    <span class="flex-fill">
                        <span class="d-block {{ $n->read_at ? '' : 'font-weight-bold' }}">{{ $n->data['judul'] ?? 'Notifikasi' }}</span>
                        <span class="d-block">{{ $n->data['pesan'] ?? '' }}</span>
                        <small class="text-muted" title="{{ $n->created_at->format('d-m-Y H:i') }}">{{ $n->created_at->diffForHumans() }}</small>
                    </span>
                    @unless ($n->read_at)
                    <span class="badge badge-primary ml-2">baru</span>
                    @endunless
                </a>
            </li>
            @empty
            <li class="list-group-item text-muted text-center py-4">
                <i class="far fa-bell-slash fa-2x d-block mb-2"></i> Belum ada notifikasi.
            </li>
            @endforelse
        </ul>
    </div>
</div>

<div class="d-flex justify-content-center mt-3">
    {{ $notifikasi->links() }}
</div>
@endsection
