<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use RealRashid\SweetAlert\Facades\Alert;


class HomeController extends Controller
{
    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct()
    {
        $this->middleware('auth');
    }

    /**
     * Show the application dashboard.
     *
     * @return \Illuminate\Contracts\Support\Renderable
     */
    public function index()
    {
        $role = Auth::user()->role;
        if($role == "admin"){
            alert()->success('Success','Berhasil login sebagai ' . $role);
            return redirect()->to('/admin');
        } else if($role == "petugas"){
            alert()->success('Success','Berhasil login sebagai ' . $role);
            return redirect()->to('petugas');
         } else if($role == "anggota"){
            alert()->success('Success','Berhasil login sebagai ' . $role);
             return redirect()->to('anggota');
         }
         else {
            alert()->error('Error','Terjadi kesalahan saat login');
            return redirect()->to('logout');
        }
    }
}
