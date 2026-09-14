<?php

namespace App\Http\Controllers;

use App\Http\Requests\AnggotaRequest;
use App\Models\Anggota;
use App\Models\User;
use App\Support\KodeQr;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;

class AnggotaController extends Controller
{
    /**
     * Daftar anggota dengan pencarian server-side (?q=: nama, email, username, NIM, jurusan) dan paginasi.
     */
    public function index(Request $request)
    {
        $paginate = Anggota::with('user')
            ->cari($request->query('q'))
            ->orderBy('nim', 'desc')
            ->paginate(AdminController::PER_HALAMAN)
            ->withQueryString();

        return view('admin.anggotaAdmin.index', ['paginate' => $paginate, 'q' => (string) $request->query('q')]);
    }

    public function create()
    {
        return view('admin.anggotaAdmin.create');
    }

    public function store(AnggotaRequest $request)
    {
        $data = $request->validated();

        DB::transaction(function () use ($data) {
            $user = User::create([
                'username' => $data['username'],
                'password' => Hash::make($data['password']),
                'name' => $data['nama'],
                'email' => $data['email'],
                'role' => 'anggota',
                'email_verified_at' => now(),
            ]);

            Anggota::create([
                'nim' => $data['nim'],
                'user_id' => $user->id,
                'jurusan' => $data['jurusan'],
                'tgl_lahir' => $data['tgl_lahir'],
                'no_hp' => $data['no_hp'],
                'alamat' => $data['alamat'] ?? '',
            ]);
        });

        return redirect()->to($this->prefix().'/anggota')->with('success', 'Anggota Berhasil Ditambah');
    }

    public function show($id)
    {
        $anggota = Anggota::with('user')->where('nim', $id)->firstOrFail();
        $kode = KodeQr::anggota($anggota);

        return view('admin.anggotaAdmin.show', [
            'anggota' => $anggota,
            'kode' => $kode,
            'qr' => KodeQr::svg($kode, 4),
        ]);
    }

    public function edit($id)
    {
        $anggota = Anggota::with('user')->where('nim', $id)->firstOrFail();

        return view('admin.anggotaAdmin.edit', compact('anggota'));
    }

    public function update(AnggotaRequest $request, $id)
    {
        $data = $request->validated();
        $anggota = Anggota::with('user')->findOrFail($id);

        DB::transaction(function () use ($anggota, $data) {
            $anggota->update([
                'nim' => $data['nim'],
                'jurusan' => $data['jurusan'],
                'tgl_lahir' => $data['tgl_lahir'],
                'no_hp' => $data['no_hp'],
                'alamat' => $data['alamat'] ?? '',
            ]);

            $anggota->user->update([
                'username' => $data['username'],
                'name' => $data['nama'],
                'email' => $data['email'],
            ]);
        });

        return redirect()->to($this->prefix().'/anggota')->with('success', 'Anggota Berhasil Diupdate');
    }

    /**
     * Soft delete anggota beserta akunnya (akun terhapus otomatis tidak bisa
     * login); riwayat peminjaman tetap utuh dan bisa dipulihkan dari arsip.
     */
    public function destroy($id)
    {
        $anggota = Anggota::with('user')->findOrFail($id);

        if ($anggota->sedangMeminjam()) {
            throw ValidationException::withMessages([
                'anggota' => 'Anggota masih memegang buku pinjaman dan belum bisa dihapus.',
            ]);
        }

        DB::transaction(function () use ($anggota) {
            $anggota->delete();
            $anggota->user?->delete();
        });

        return redirect()->to($this->prefix().'/anggota')->with('success', 'Anggota dipindahkan ke arsip');
    }

    public function arsip(Request $request)
    {
        $paginate = Anggota::onlyTrashed()->with('user')
            ->cari($request->query('q'))
            ->latest('deleted_at')
            ->paginate(AdminController::PER_HALAMAN)
            ->withQueryString();

        return view('admin.anggotaAdmin.arsip', ['paginate' => $paginate, 'q' => (string) $request->query('q')]);
    }

    public function pulihkan($id)
    {
        $anggota = Anggota::onlyTrashed()->with('user')->findOrFail($id);

        DB::transaction(function () use ($anggota) {
            $anggota->restore();
            $anggota->user?->restore();
        });

        return redirect()->to($this->prefix().'/anggota')->with('success', 'Anggota berhasil dipulihkan');
    }

    public function delete($id)
    {
        $anggota = Anggota::findOrFail($id);

        return view('admin.anggotaAdmin.delete', compact('anggota'));
    }

    public function home()
    {
        $user = Auth::user();

        return view('anggota.home', compact('user'));
    }

    /**
     * Anggota dikelola dari panel admin maupun petugas; kembali ke panel yang sesuai.
     */
    private function prefix(): string
    {
        return Auth::user()->isAdmin() ? '/admin' : '/petugas';
    }
}
