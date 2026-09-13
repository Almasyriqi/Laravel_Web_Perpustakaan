<?php

namespace App\Providers;

use App\Models\User;
use Illuminate\Support\Facades\Blade;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        // Gate per role, dipakai oleh menu AdminLTE lewat key 'can' di config/adminlte.php
        Gate::define('admin-only', fn (User $user) => $user->role === 'admin');
        Gate::define('petugas-only', fn (User $user) => $user->role === 'petugas');
        Gate::define('anggota-only', fn (User $user) => $user->role === 'anggota');

        Blade::directive('currency', function ($expression) {
            return "Rp <?php echo number_format($expression,0,',','.'); ?>";
        });
    }
}
