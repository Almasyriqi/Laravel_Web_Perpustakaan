<?php

namespace App\Http\Controllers;

use App\Models\Anggota;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;

class AnggotaController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $anggota = Anggota::with('user')->get();
        $paginate = Anggota::orderBy('nim', 'desc')->paginate(10);
        return view('admin.anggotaAdmin.index', ['anggota' => $anggota, 'paginate' => $paginate]);
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        return view('admin.anggotaAdmin.create');
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        //melakukan validasi data
        $request->validate([
            'username' => 'required', 'string', 'max:20', 'unique:users',
            'password' => 'required', 'string', 'min:8',
            'nim' => 'required|numeric',
            'nama' => 'required',
            'jurusan' => 'required',
            'tgl_lahir' => 'required|date',
            'no_hp' => 'required',
            'email' => 'required|email',
        ]);
        //TODO : Implementasikan Proses Simpan Ke Database
        $anggota = new Anggota();
        $anggota->nim = $request->get('nim');
        $anggota->jurusan = $request->get('jurusan');
        $anggota->tgl_lahir = $request->get('tgl_lahir');
        $anggota->no_hp = $request->get('no_hp');
        $anggota->alamat = $request->get('alamat');
        $anggota->save();

        $user = new User();
        $user->username = $request->get('username');
        $user->password = Hash::make($request->get('password'));
        $user->name = $request->get('nama');
        $user->email = $request->get('email');
        $user->role = 'anggota';
        $user->email_verified_at = now();
        $user->save();

        // fungsi eloquent untuk menambah data dengan relasi belongsTo
        $anggota->user()->associate($user);
        $anggota->save();

        //jika data berhasil ditambahkan, akan kembali ke halaman utama
        if (Auth::user()->role == 'admin') {
            return redirect()->to('/admin/anggota')->with('success', 'Anggota Berhasil Ditambah');
        }
        else {
            return redirect()->to('/petugas/anggota')->with('success', 'Anggota Berhasil Ditambah');
        }
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        $anggota = Anggota::with('user')->where('nim', $id)->first();
        return view('admin.anggotaAdmin.show', compact('anggota'));
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function edit($id)
    {
        $anggota = Anggota::with('user')->where('nim', $id)->first();
        return view('admin.anggotaAdmin.edit', compact('anggota'));
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
        //melakukan validasi data
        $request->validate([
            'username' => 'required', 'string', 'max:20', 'unique:users',
            'nim' => 'required|numeric',
            'nama' => 'required',
            'jurusan' => 'required',
            'tgl_lahir' => 'required|date',
            'no_hp' => 'required',
            'email' => 'required|email',
        ]);
        //TODO : Implementasikan Proses Simpan Ke Database
        $anggota = Anggota::find($id);
        $user_id = $anggota->user_id;
        $anggota->nim = $request->get('nim');
        $anggota->jurusan = $request->get('jurusan');
        $anggota->tgl_lahir = $request->get('tgl_lahir');
        $anggota->no_hp = $request->get('no_hp');
        $anggota->alamat = $request->get('alamat');
        $anggota->save();

        $user = User::find($user_id);
        $user->username = $request->get('username');
        $user->name = $request->get('nama');
        $user->email = $request->get('email');
        $user->role = 'anggota';
        $user->save();

        // fungsi eloquent untuk menambah data dengan relasi belongsTo
        $anggota->user()->associate($user);
        $anggota->save();

        //jika data berhasil ditambahkan, akan kembali ke halaman utama
        if (Auth::user()->role == 'admin') {
            return redirect()->to('/admin/anggota')->with('success', 'Anggota Berhasil Diupdate');
        }
        else {
            return redirect()->to('/petugas/anggota')->with('success', 'Anggota Berhasil Diupdate');
        }
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        $anggota = Anggota::find($id);
        $user_id = $anggota->user_id;
        $user = User::find($user_id);
        $anggota->delete();
        $user->delete();
        if (Auth::user()->role == 'admin') {
            return redirect()->to('/admin/anggota')->with('success', 'Anggota Berhasil Dihapus');
        }
        else {
            return redirect()->to('/petugas/anggota')->with('success', 'Anggota Berhasil Dihapus');
        }
    }

    public function delete($id)
    {
        $anggota = Anggota::find($id);
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
}
