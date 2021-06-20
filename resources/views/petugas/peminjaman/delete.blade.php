{{-- !-- Delete Warning Modal -->  --}}
    <form method="post" action="/petugas/transaksi/{{  $pinjam->id }}" id="myForm">
    <div class="modal-body">
        @csrf
        @method('DELETE')
        <h5 class="text-center">Apakah Anda yakin membatalkan peminjaman?</h5>
    </div>
    <div class="modal-footer">
        <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancel</button>
        <button type="submit" class="btn btn-danger">Yes, I'm sure</button>
    </div>
</form>