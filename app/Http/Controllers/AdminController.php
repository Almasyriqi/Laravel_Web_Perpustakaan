<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\Anggota;
use App\Models\Admin;
use App\Models\Buku;
use App\Models\Kategori;
use App\Models\Petugas;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

class AdminController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $admin = Admin::with('user')->get();
        $paginate = Admin::orderBy('id', 'desc')->paginate(10);
        return view('admin.adminAdmin.index', ['admin' => $admin, 'paginate' => $paginate]);
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        return view('admin.adminAdmin.create');
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        $request->validate([
            'username' => 'required', 'string', 'max:20', 'unique:users',
            'password' => 'required', 'string', 'min:8',
            'nama' => 'required',
            'no_hp' => 'required',
            'email' => 'required|email',
        ]);
        //TODO : Implementasikan Proses Simpan Ke Database
        $admin = new Admin();
        $admin->id = $request->get('id');
        $admin->no_hp = $request->get('no_hp');
        $admin->alamat = $request->get('alamat');
        $admin->save();

        $user = new User();
        $user->username = $request->get('username');
        $user->password = Hash::make($request->get('password'));
        $user->name = $request->get('nama');
        $user->email = $request->get('email');
        $user->role = 'admin';
        $user->email_verified_at = now();
        $user->save();

        // fungsi eloquent untuk menambah data dengan relasi belongsTo
        $admin->user()->associate($user);
        $admin->save();

        //jika data berhasil ditambahkan, akan kembali ke halaman utama
        return redirect()->route('admin.index')->with('success', 'Admin Berhasil Ditambahkan');
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        $admin = Admin::with('user')->where('id', $id)->first();
        return view('admin.adminAdmin.show', compact('admin'));
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function edit($id)
    {
        $admin = Admin::with('user')->where('id', $id)->first();
        return view('admin.adminAdmin.edit', compact('admin'));
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id)
    {
        $request->validate([
            'username' => 'required', 'string', 'max:20', 'unique:users',
            'id' => 'required',
            'nama' => 'required',
            'no_hp' => 'required',
            'email' => 'required|email',
        ]);
        //TODO : Implementasikan Proses Simpan Ke Database
        $admin = Admin::find($id);
        $user_id = $admin->user_id;
        $admin->id = $request->get('id');
        $admin->no_hp = $request->get('no_hp');
        $admin->alamat = $request->get('alamat');
        $admin->save();

        $user = User::find($user_id);
        $user->username = $request->get('username');
        $user->name = $request->get('nama');
        $user->email = $request->get('email');
        $user->role = 'admin';
        $user->save();

        // fungsi eloquent untuk menambah data dengan relasi belongsTo
        $admin->user()->associate($user);
        $admin->save();

        //jika data berhasil ditambahkan, akan kembali ke halaman utama
        return redirect()->route('admin.index')->with('success', 'Admin Berhasil Diedit');
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        $admin = Admin::find($id);
        $user_id = $admin->user_id;
        $user = User::find($user_id);
        $admin->delete();
        $user->delete();
        return redirect()->route('admin.index')
            ->with('success', 'Admin Berhasil Dihapus');
    }

    public function delete($id)
    {
        $admin = Admin::find($id);
        return view('admin.adminAdmin.delete', compact('admin'));
    }

    public function search(Request $request)
    {
        $paginate = Admin::join('users', 'admin.user_id', '=', 'users.id')->select('admin.*', 'users.name', 'users.username', 'users.email')->when($request->keyword, function ($query) use ($request) {
            $query->where('name', 'like', "%{$request->keyword}%")
                ->orWhere('email', 'like', "%{$request->keyword}%");
        })->paginate(10);
        $paginate->appends($request->only('keyword'));
        return view('admin.adminAdmin.index', compact('paginate'));
    }

    public function home()
    {
        $a = Anggota::all();
        $b = Buku::all();
        $k = Kategori::all();
        $p = Petugas::all();
        $anggota = count($a);
        $buku = count($b);
        $kategori = count($k);
        $petugas = count($p);
        return view('admin.home', compact('anggota', 'buku', 'kategori', 'petugas'));
    }
}
