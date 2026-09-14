<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

// Email pengingat jatuh tempo & keterlambatan, tiap pagi.
// Butuh scheduler berjalan: `php artisan schedule:work` (dev) atau cron `* * * * * php artisan schedule:run`.
Schedule::command('perpus:kirim-pengingat')
    ->dailyAt('07:00')
    ->withoutOverlapping();
