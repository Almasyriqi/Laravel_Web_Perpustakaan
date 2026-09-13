<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class HomeController extends Controller
{
    /**
     * Arahkan pengguna yang baru login ke dashboard sesuai role-nya.
     */
    public function index(Request $request)
    {
        $role = $request->user()->role;

        alert()->success('Success', 'Berhasil login sebagai '.$role->label());

        return redirect()->to($role->dashboardPath());
    }
}
