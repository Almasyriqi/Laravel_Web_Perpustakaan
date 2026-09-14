<?php

namespace App\Http\Controllers;

use App\Http\Requests\PetugasRequest;
use App\Models\Anggota;
use App\Models\Buku;
use App\Models\Kategori;
use App\Models\Petugas;
use App\Models\User;
use App\Services\StatistikService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class PetugasController extends Controller
{
    /**
     * Daftar petugas dengan pencarian server-side (?q=) dan paginasi.
     */
    public function index(Request $request)
    {
        $paginate = Petugas::with('user')
            ->cari($request->query('q'))
            ->orderBy('id', 'desc')
            ->paginate(AdminController::PER_HALAMAN)
            ->withQueryString();

        return view('admin.petugasAdmin.index', ['paginate' => $paginate, 'q' => (string) $request->query('q')]);
    }

    public function create()
    {
        return view('admin.petugasAdmin.create');
    }

    public function store(PetugasRequest $request)
    {
        $data = $request->validated();

        DB::transaction(function () use ($data) {
            $user = User::create([
                'username' => $data['username'],
                'password' => Hash::make($data['password']),
                'name' => $data['nama'],
                'email' => $data['email'],
                'role' => 'petugas',
                'email_verified_at' => now(),
            ]);

            Petugas::create([
                'user_id' => $user->id,
                'tgl_lahir' => $data['tgl_lahir'],
                'no_hp' => $data['no_hp'],
                'alamat' => $data['alamat'] ?? '',
            ]);
        });

        return redirect()->route('petugas.index')->with('success', 'Petugas Berhasil Ditambahkan');
    }

    public function show($id)
    {
        $petugas = Petugas::with('user')->findOrFail($id);

        return view('admin.petugasAdmin.show', compact('petugas'));
    }

    public function edit($id)
    {
        $petugas = Petugas::with('user')->findOrFail($id);

        return view('admin.petugasAdmin.edit', compact('petugas'));
    }

    public function update(PetugasRequest $request, $id)
    {
        $data = $request->validated();
        $petugas = Petugas::with('user')->findOrFail($id);

        DB::transaction(function () use ($petugas, $data) {
            $petugas->update([
                'tgl_lahir' => $data['tgl_lahir'],
                'no_hp' => $data['no_hp'],
                'alamat' => $data['alamat'] ?? '',
            ]);

            $petugas->user->update([
                'username' => $data['username'],
                'name' => $data['nama'],
                'email' => $data['email'],
            ]);
        });

        return redirect()->route('petugas.index')->with('success', 'Petugas Berhasil Diedit');
    }

    public function destroy($id)
    {
        $petugas = Petugas::with('user')->findOrFail($id);

        DB::transaction(function () use ($petugas) {
            $petugas->delete();
            $petugas->user?->delete();
        });

        return redirect()->route('petugas.index')
            ->with('success', 'Petugas Berhasil Dihapus');
    }

    public function delete($id)
    {
        $petugas = Petugas::findOrFail($id);

        return view('admin.petugasAdmin.delete', compact('petugas'));
    }

    public function home(StatistikService $statistik)
    {
        return view('petugas.home', [
            'anggota' => Anggota::count(),
            'buku' => Buku::count(),
            'kategori' => Kategori::count(),
            'statistik' => $statistik->dashboard(),
        ]);
    }
}
