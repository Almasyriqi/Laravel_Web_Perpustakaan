<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

/**
 * Notifikasi in-app (database channel) untuk user yang sedang login —
 * halaman daftar + endpoint JSON yang dipoll lonceng navbar AdminLTE.
 */
class NotifikasiController extends Controller
{
    /** Jumlah notifikasi terbaru yang ditampilkan di dropdown lonceng. */
    public const DI_DROPDOWN = 5;

    public function index(Request $request)
    {
        $notifikasi = $request->user()->notifications()->paginate(20);

        return view('notifikasi.index', [
            'notifikasi' => $notifikasi,
            'belumDibaca' => $request->user()->unreadNotifications()->count(),
        ]);
    }

    /**
     * Format yang dimengerti komponen navbar-notification AdminLTE:
     * label (angka badge), label_color, icon_color, dropdown (HTML).
     */
    public function ringkas(Request $request)
    {
        $user = $request->user();
        $belumDibaca = $user->unreadNotifications()->count();

        return response()->json([
            'label' => $belumDibaca,
            'label_color' => 'danger',
            'icon_color' => $belumDibaca > 0 ? 'warning' : 'secondary',
            'dropdown' => view('partials.notifikasi-dropdown', [
                'terbaru' => $user->notifications()->limit(self::DI_DROPDOWN)->get(),
                'belumDibaca' => $belumDibaca,
            ])->render(),
        ]);
    }

    /**
     * Tandai dibaca lalu lompat ke halaman terkait. 404 bila bukan milik user.
     */
    public function buka(Request $request, string $id)
    {
        $notifikasi = $request->user()->notifications()->findOrFail($id);
        $notifikasi->markAsRead();

        return redirect()->to($notifikasi->data['url'] ?? '/home');
    }

    public function bacaSemua(Request $request)
    {
        $request->user()->unreadNotifications->markAsRead();

        return redirect()->to('/notifikasi')->with('success', 'Semua notifikasi ditandai sudah dibaca.');
    }
}
