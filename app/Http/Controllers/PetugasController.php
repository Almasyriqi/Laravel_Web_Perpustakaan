<?php

namespace App\Http\Controllers;

use App\Models\Anggota;
use App\Models\Buku;
use App\Models\Kategori;
use App\Models\User;
use App\Models\Petugas;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;

class PetugasController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $petugas = Petugas::with('user')->get();
        $paginate = Petugas::orderBy('id', 'desc')->paginate(10);
        return view('admin.petugasAdmin.index', ['petugas' => $petugas, 'paginate' => $paginate]);
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        return view('admin.petugasAdmin.create');
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
            'tgl_lahir' => 'required|date',
            'no_hp' => 'required',
            'email' => 'required|email',
        ]);
        //TODO : Implementasikan Proses Simpan Ke Database
        $petugas = new Petugas();
        $petugas->id = $request->get('id');
        $petugas->tgl_lahir = $request->get('tgl_lahir');
        $petugas->no_hp = $request->get('no_hp');
        $petugas->alamat = $request->get('alamat');
        $petugas->save();

        $user = new User();
        $user->username = $request->get('username');
        $user->password = Hash::make($request->get('password'));
        $user->name = $request->get('nama');
        $user->email = $request->get('email');
        $user->role = 'petugas';
        $user->email_verified_at = now();
        $user->save();

        // fungsi eloquent untuk menambah data dengan relasi belongsTo
        $petugas->user()->associate($user);
        $petugas->save();

        //jika data berhasil ditambahkan, akan kembali ke halaman utama
        return redirect()->route('petugas.index')->with('success', 'Petugas Berhasil Ditambahkan');
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        $petugas = Petugas::with('user')->where('id', $id)->first();
        return view('admin.petugasAdmin.show', compact('petugas'));
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function edit($id)
    {
        $petugas = Petugas::with('user')->where('id', $id)->first();
        return view('admin.petugasAdmin.edit', compact('petugas'));
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
            'tgl_lahir' => 'required|date',
            'no_hp' => 'required',
            'email' => 'required|email',
        ]);
        //TODO : Implementasikan Proses Simpan Ke Database
        $petugas = Petugas::find($id);
        $user_id = $petugas->user_id;
        $petugas->id = $request->get('id');
        $petugas->tgl_lahir = $request->get('tgl_lahir');
        $petugas->no_hp = $request->get('no_hp');
        $petugas->alamat = $request->get('alamat');
        $petugas->save();

        $user = User::find($user_id);
        $user->username = $request->get('username');
        $user->name = $request->get('nama');
        $user->email = $request->get('email');
        $user->role = 'petugas';
        $user->save();

        // fungsi eloquent untuk menambah data dengan relasi belongsTo
        $petugas->user()->associate($user);
        $petugas->save();

        //jika data berhasil ditambahkan, akan kembali ke halaman utama
        return redirect()->route('petugas.index')->with('success', 'Petugas Berhasil Diedit');
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        $petugas = Petugas::find($id);
        $user_id = $petugas->user_id;
        $user = User::find($user_id);
        $petugas->delete();
        $user->delete();
        return redirect()->route('petugas.index')
            ->with('success', 'Petugas Berhasil Dihapus');
    }

    public function delete($id)
    {
        $petugas = Petugas::find($id);
        return view('admin.petugasAdmin.delete', compact('petugas'));
    }

    public function search(Request $request)
    {
        $paginate = Petugas::join('users', 'petugas.user_id', '=', 'users.id')->select('petugas.*', 'users.name', 'users.username', 'users.email')->when($request->keyword, function ($query) use ($request) {
            $query->where('name', 'like', "%{$request->keyword}%")
                ->orWhere('email', 'like', "%{$request->keyword}%");
        })->paginate(10);
        $paginate->appends($request->only('keyword'));
        return view('admin.petugasAdmin.index', compact('paginate'));
    }

    public function home()
    {
        $a = Anggota::all();
        $b = Buku::all();
        $k = Kategori::all();
        $anggota = count($a);
        $buku = count($b);
        $kategori = count($k);
        return view('petugas.home', compact('anggota', 'buku', 'kategori'));
    }
}
