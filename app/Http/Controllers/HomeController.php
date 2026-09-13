<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class HomeController extends Controller
{
    /**
     * Arahkan pengguna yang baru login ke dashboard sesuai role-nya.
     */
    public function index(Request $request)
    {
        $role = Auth::user()->role;

        if (in_array($role, ['admin', 'petugas', 'anggota'], true)) {
            alert()->success('Success', 'Berhasil login sebagai '.$role);

            return redirect()->to('/'.$role);
        }

        // Role tidak dikenal: putuskan sesi alih-alih memutar ke rute logout GET
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();
        alert()->error('Error', 'Terjadi kesalahan saat login');

        return redirect()->to('/');
    }
}
