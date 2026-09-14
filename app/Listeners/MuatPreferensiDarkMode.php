<?php

namespace App\Listeners;

use JeroenNoten\LaravelAdminLte\Events\ReadingDarkModePreference;

/**
 * Saat AdminLTE membaca preferensi dark mode dan session belum menyimpannya
 * (mis. baru login), muat pilihan tersimpan milik akun ke session.
 */
class MuatPreferensiDarkMode
{
    public function handle(ReadingDarkModePreference $event): void
    {
        $user = auth()->user();

        if ($user === null || $user->dark_mode === null || session()->has('adminlte_dark_mode')) {
            return;
        }

        $user->dark_mode ? $event->darkMode->enable() : $event->darkMode->disable();
    }
}
