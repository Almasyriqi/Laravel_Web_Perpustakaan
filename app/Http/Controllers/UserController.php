<?php

namespace App\Http\Controllers;

use App\Enums\Role;
use App\Http\Requests\ProfileRequest;
use App\Models\Admin;
use App\Models\Anggota;
use App\Models\Petugas;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

/**
 * Profil pengguna yang sedang login (admin/petugas/anggota).
 */
class UserController extends Controller
{
    public function index()
    {
        $user = $this->profilSaya();

        return view('profile', compact('user'));
    }

    /**
     * Parameter {profile} di URL diabaikan: yang diubah selalu profil milik
     * pengguna yang login, supaya id orang lain tidak bisa disisipkan.
     */
    public function update(ProfileRequest $request, $id)
    {
        $data = $request->validated();
        $profil = $this->profilSaya();

        DB::transaction(function () use ($profil, $data) {
            $profil->update(match (Auth::user()->role) {
                Role::Admin => [
                    'no_hp' => $data['no_hp'],
                    'alamat' => $data['alamat'] ?? '',
                ],
                Role::Petugas => [
                    'tgl_lahir' => $data['tgl_lahir'],
                    'no_hp' => $data['no_hp'],
                    'alamat' => $data['alamat'] ?? '',
                ],
                default => [
                    'nim' => $data['nim'],
                    'jurusan' => $data['jurusan'],
                    'tgl_lahir' => $data['tgl_lahir'],
                    'no_hp' => $data['no_hp'],
                    'alamat' => $data['alamat'] ?? '',
                ],
            });

            $profil->user->update([
                'username' => $data['username'],
                'name' => $data['nama'],
                'email' => $data['email'],
            ]);
        });

        return redirect()->route('profile.index')->with('success', 'Berhasil edit profile');
    }

    private function profilSaya(): Model
    {
        $user = Auth::user();

        $model = match ($user->role) {
            Role::Admin => Admin::class,
            Role::Petugas => Petugas::class,
            Role::Anggota => Anggota::class,
        };

        return $model::with('user')->where('user_id', $user->id)->firstOrFail();
    }
}
