<?php

namespace App\Http\Controllers;

use App\Http\Requests\UlasanRequest;
use App\Models\Anggota;
use App\Models\Buku;
use App\Models\Ulasan;
use Illuminate\Validation\ValidationException;

/**
 * Rating & ulasan buku: anggota menulis/mengubah/menghapus ulasannya sendiri,
 * admin & petugas dapat menghapus ulasan siapa pun (moderasi).
 */
class UlasanController extends Controller
{
    /**
     * Simpan atau perbarui ulasan anggota untuk sebuah buku (satu ulasan per anggota per buku).
     */
    public function store(UlasanRequest $request, Buku $buku)
    {
        $anggota = $this->anggotaSaatIni();

        if (! $anggota->bolehMengulas($buku)) {
            throw ValidationException::withMessages([
                'rating' => 'Ulasan hanya bisa ditulis setelah Anda meminjam dan mengembalikan buku ini.',
            ]);
        }

        $ulasan = Ulasan::updateOrCreate(
            ['buku_id' => $buku->id, 'anggota_id' => $anggota->nim],
            $request->validated(),
        );

        return redirect()->to('/anggota/buku/'.$buku->id)
            ->with('success', $ulasan->wasRecentlyCreated ? 'Terima kasih, ulasan Anda tersimpan.' : 'Ulasan Anda diperbarui.');
    }

    /**
     * Anggota menghapus ulasannya sendiri.
     */
    public function destroy(Buku $buku)
    {
        $this->anggotaSaatIni()->ulasan()->where('buku_id', $buku->id)->delete();

        return redirect()->to('/anggota/buku/'.$buku->id)->with('success', 'Ulasan dihapus.');
    }

    /**
     * Moderasi oleh admin/petugas: hapus ulasan mana pun dari halaman detail buku.
     */
    public function moderasi(Buku $buku, Ulasan $ulasan)
    {
        abort_unless($ulasan->buku_id === $buku->id, 404);

        $ulasan->delete();

        $prefix = auth()->user()->isAdmin() ? 'admin' : 'petugas';

        return redirect()->to("/{$prefix}/buku/{$buku->id}")->with('success', 'Ulasan dihapus.');
    }

    private function anggotaSaatIni(): Anggota
    {
        return Anggota::where('user_id', auth()->id())->firstOrFail();
    }
}
