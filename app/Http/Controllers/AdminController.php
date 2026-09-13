<?php

namespace App\Http\Controllers;

use App\Http\Requests\AdminRequest;
use App\Models\Admin;
use App\Models\Anggota;
use App\Models\Buku;
use App\Models\Kategori;
use App\Models\Petugas;
use App\Models\User;
use App\Services\StatistikService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class AdminController extends Controller
{
    public function index()
    {
        $paginate = Admin::with('user')->orderBy('id', 'desc')->paginate(10);

        return view('admin.adminAdmin.index', compact('paginate'));
    }

    public function create()
    {
        return view('admin.adminAdmin.create');
    }

    public function store(AdminRequest $request)
    {
        $data = $request->validated();

        DB::transaction(function () use ($data) {
            $user = User::create([
                'username' => $data['username'],
                'password' => Hash::make($data['password']),
                'name' => $data['nama'],
                'email' => $data['email'],
                'role' => 'admin',
                'email_verified_at' => now(),
            ]);

            Admin::create([
                'user_id' => $user->id,
                'no_hp' => $data['no_hp'],
                'alamat' => $data['alamat'] ?? '',
            ]);
        });

        return redirect()->route('admin.index')->with('success', 'Admin Berhasil Ditambahkan');
    }

    public function show($id)
    {
        $admin = Admin::with('user')->findOrFail($id);

        return view('admin.adminAdmin.show', compact('admin'));
    }

    public function edit($id)
    {
        $admin = Admin::with('user')->findOrFail($id);

        return view('admin.adminAdmin.edit', compact('admin'));
    }

    public function update(AdminRequest $request, $id)
    {
        $data = $request->validated();
        $admin = Admin::with('user')->findOrFail($id);

        DB::transaction(function () use ($admin, $data) {
            $admin->update([
                'no_hp' => $data['no_hp'],
                'alamat' => $data['alamat'] ?? '',
            ]);

            $admin->user->update([
                'username' => $data['username'],
                'name' => $data['nama'],
                'email' => $data['email'],
            ]);
        });

        return redirect()->route('admin.index')->with('success', 'Admin Berhasil Diedit');
    }

    public function destroy($id)
    {
        $admin = Admin::with('user')->findOrFail($id);

        DB::transaction(function () use ($admin) {
            $admin->delete();
            $admin->user?->delete();
        });

        return redirect()->route('admin.index')
            ->with('success', 'Admin Berhasil Dihapus');
    }

    public function delete($id)
    {
        $admin = Admin::findOrFail($id);

        return view('admin.adminAdmin.delete', compact('admin'));
    }

    public function search(Request $request)
    {
        $paginate = Admin::with('user')
            ->when($request->keyword, fn ($query, $keyword) => $query->whereHas('user', fn ($user) => $user
                ->where('name', 'like', "%{$keyword}%")
                ->orWhere('email', 'like', "%{$keyword}%")))
            ->orderBy('id', 'desc')
            ->paginate(10);
        $paginate->appends($request->only('keyword'));

        return view('admin.adminAdmin.index', compact('paginate'));
    }

    public function home(StatistikService $statistik)
    {
        return view('admin.home', [
            'anggota' => Anggota::count(),
            'buku' => Buku::count(),
            'kategori' => Kategori::count(),
            'petugas' => Petugas::count(),
            'statistik' => $statistik->dashboard(),
        ]);
    }
}
