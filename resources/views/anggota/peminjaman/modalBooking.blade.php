<form method="post" action="/anggota/booking/{{ $buku->id }}" id="myForm">
    <div class="modal-body">
        @csrf
        <h5 class="text-center">Booking buku "{{ $buku->judul }}"?</h5>
        <p class="text-muted small text-center mb-0">
            Stok sedang habis. Anda masuk antrean dan akan dikirimi email begitu buku tersedia;
            booking otomatis menjadi pengajuan peminjaman (1 eksemplar).
        </p>
    </div>
    <div class="modal-footer">
        <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancel</button>
        <button type="submit" class="btn btn-warning">Yes, I'm sure</button>
    </div>
</form>
