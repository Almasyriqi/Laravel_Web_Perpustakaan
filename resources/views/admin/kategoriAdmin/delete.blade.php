{{-- !-- Delete Warning Modal -->  --}}
@if (Auth::user()->role == 'admin')
<form action="/admin/kategori/{{  $kategori->id }}" method="post">    
@else
<form action="/petugas/kategori/{{  $kategori->id }}" method="post"> 
@endif

    <div class="modal-body">
        @csrf
        @method('DELETE')
        <h5 class="text-center">Are you sure you want to delete data?</h5>
    </div>
    <div class="modal-footer">
        <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancel</button>
        <button type="submit" class="btn btn-danger">Yes, Delete</button>
    </div>
</form>