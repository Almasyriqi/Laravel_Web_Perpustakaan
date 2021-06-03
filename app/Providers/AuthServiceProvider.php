<?php

namespace App\Providers;

use Illuminate\Foundation\Support\Providers\AuthServiceProvider as ServiceProvider;
use Illuminate\Support\Facades\Gate;

class AuthServiceProvider extends ServiceProvider
{
    /**
     * The policy mappings for the application.
     *
     * @var array
     */
    protected $policies = [
        // 'App\Models\Model' => 'App\Policies\ModelPolicy',
    ];

    /**
     * Register any authentication / authorization services.
     *
     * @return void
     */
    public function boot()
    {
        $this->registerPolicies();

        Gate::define('admin-only', function ($user) {
            if ($user->role == 'admin'){
                return true;
            }
            return false;
        });

        Gate::define('petugas-only', function ($user) {
            if ($user->role == 'petugas'){
                return true;
            }
            return false;
        });

        Gate::define('anggota-only', function ($user) {
            if ($user->role == 'anggota'){
                return true;
            }
            return false;
        });
    }
}
