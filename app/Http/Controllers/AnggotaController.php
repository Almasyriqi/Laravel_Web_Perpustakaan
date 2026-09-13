<?php

namespace App\Http\Controllers;

use App\Http\Requests\AnggotaRequest;
use App\Models\Anggota;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class AnggotaController extends Controller
{
    public function index()
    {
        $anggota = Anggota::with('user')->get();
        $paginate = Anggota::orderBy('nim', 'desc')->paginate(10);

        return view('admin.anggotaAdmin.index', ['anggota' => $anggota, 'paginate' => $paginate]);
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

        return view('admin.anggotaAdmin.show', compact('anggota'));
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

    public function destroy($id)
    {
        $anggota = Anggota::with('user')->findOrFail($id);

        DB::transaction(function () use ($anggota) {
            $anggota->delete();
            $anggota->user?->delete();
        });

        return redirect()->to($this->prefix().'/anggota')->with('success', 'Anggota Berhasil Dihapus');
    }

    public function delete($id)
    {
        $anggota = Anggota::findOrFail($id);

        return view('admin.anggotaAdmin.delete', compact('anggota'));
    }

    public function search(Request $request)
    {
        $paginate = Anggota::join('users', 'anggota.user_id', '=', 'users.id')->when($request->keyword, function ($query) use ($request) {
            $query->where('name', 'like', "%{$request->keyword}%")
                ->orWhere('email', 'like', "%{$request->keyword}%")
                ->orWhere('Jurusan', 'like', "%{$request->keyword}%");
        })->paginate(10);
        $paginate->appends($request->only('keyword'));

        return view('admin.anggotaAdmin.index', compact('paginate'));
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
        return Auth::user()->role === 'admin' ? '/admin' : '/petugas';
    }
}
