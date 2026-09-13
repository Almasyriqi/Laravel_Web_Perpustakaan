<?php

namespace App\Http\Controllers;

use App\Http\Requests\KategoriRequest;
use App\Models\Kategori;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Auth;

class KategoriController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return Response
     */
    public function index()
    {
        $paginate = Kategori::all();

        return view('admin.kategoriAdmin.index', compact('paginate'));
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return Response
     */
    public function create()
    {
        return view('admin.kategoriAdmin.create');
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  Request  $request
     * @return Response
     */
    public function store(KategoriRequest $request)
    {
        $kategori = new Kategori;
        $kategori->nama = $request->get('nama');
        $kategori->keterangan = $request->get('keterangan');
        $kategori->save();

        // jika data berhasil ditambahkan, akan kembali ke halaman utama
        if (Auth::user()->role == 'admin') {
            return redirect()->to('/admin/kategori')->with('success', 'Kategori Berhasil Ditambah');
        } else {
            return redirect()->to('/petugas/kategori')->with('success', 'Kategori Berhasil Ditambah');
        }
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return Response
     */
    public function show($id)
    {
        $kategori = Kategori::find($id);

        return view('admin.kategoriAdmin.show', compact('kategori'));
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return Response
     */
    public function edit($id)
    {
        $kategori = Kategori::find($id);

        return view('admin.kategoriAdmin.edit', compact('kategori'));
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  Request  $request
     * @param  int  $id
     * @return Response
     */
    public function update(KategoriRequest $request, $id)
    {
        $kategori = Kategori::findOrFail($id);
        $kategori->nama = $request->get('nama');
        $kategori->keterangan = $request->get('keterangan');
        $kategori->save();

        if (Auth::user()->role == 'admin') {
            return redirect()->to('/admin/kategori')->with('success', 'Kategori Berhasil Diupdate');
        } else {
            return redirect()->to('/petugas/kategori')->with('success', 'Kategori Berhasil Diupdate');
        }
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return Response
     */
    public function destroy($id)
    {
        $kategori = Kategori::findOrFail($id);
        $kategori->delete();
        if (Auth::user()->role == 'admin') {
            return redirect()->to('/admin/kategori')->with('success', 'Kategori Berhasil Dihapus');
        } else {
            return redirect()->to('/petugas/kategori')->with('success', 'Kategori Berhasil Dihapus');
        }
    }

    public function delete($id)
    {
        $kategori = Kategori::find($id);

        return view('admin.kategoriAdmin.delete', compact('kategori'));
    }
}
