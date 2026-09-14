<?php

namespace App\Listeners;

use JeroenNoten\LaravelAdminLte\Events\DarkModeWasToggled;

/**
 * Setiap kali tombol dark mode di navbar ditekan, simpan pilihannya ke akun
 * supaya berlaku lintas perangkat dan sesi.
 */
class SimpanPreferensiDarkMode
{
    public function handle(DarkModeWasToggled $event): void
    {
        auth()->user()?->forceFill(['dark_mode' => $event->darkMode->isEnabled()])->save();
    }
}
