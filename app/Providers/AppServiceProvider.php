<?php

namespace App\Providers;

use App\Enums\Role;
use App\Models\User;
use Illuminate\Pagination\Paginator;
use Illuminate\Support\Carbon;
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
        // Tampilan AdminLTE memakai Bootstrap 4; view paginator bawaan Laravel adalah Tailwind
        Paginator::useBootstrapFour();

        // "5 menit yang lalu" pada diffForHumans() (notifikasi, ulasan) tanpa mengubah locale validasi
        Carbon::setLocale('id');

        // Gate per role, dipakai oleh menu AdminLTE lewat key 'can' di config/adminlte.php
        Gate::define('admin-only', fn (User $user) => $user->hasRole(Role::Admin));
        Gate::define('petugas-only', fn (User $user) => $user->hasRole(Role::Petugas));
        Gate::define('anggota-only', fn (User $user) => $user->hasRole(Role::Anggota));

        Blade::directive('currency', function ($expression) {
            return "Rp <?php echo number_format($expression,0,',','.'); ?>";
        });
    }
}
