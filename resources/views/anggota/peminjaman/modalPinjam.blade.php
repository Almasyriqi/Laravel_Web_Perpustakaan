<form method="post" action="/anggota/peminjaman/{{$pinjam->id}}" id="myForm">
    <div class="modal-body">
        @csrf
        <h5 class="text-center">Apakah Anda yakin meminjam buku ini?</h5>
        <div class="form-group">
            <label for="jumlah">Jumlah</label>
            <input type="jumlah" name="jumlah" class="form-control" id="jumlah" aria-describedby="jumlah"
                value="{{old("jumlah")}}" placeholder="Input Jumlah Buku">
        </div>
    </div>
    <div class="modal-footer">
        <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancel</button>
        <button type="submit" class="btn btn-success">Yes, I'm sure</button>
    </div>
</form>