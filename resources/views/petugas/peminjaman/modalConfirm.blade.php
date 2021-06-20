
<form method="post" action="/petugas/transaksi/konfirmasi/{{  $pinjam->id }}" id="myForm">
    <div class="modal-body">
        @csrf
        @method('PUT')
        <h5 class="text-center">Apakah Anda yakin melakukan peminjaman?</h5>
    </div>
    <div class="modal-footer">
        <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancel</button>
        <button type="submit" class="btn btn-warning">Yes, I'm sure</button>
    </div>
</form>