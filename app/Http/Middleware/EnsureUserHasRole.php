<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Pemakaian: ->middleware('role:admin') atau 'role:admin,petugas'.
 * Dijalankan setelah `auth`, jadi user dijamin ada.
 */
class EnsureUserHasRole
{
    public function handle(Request $request, Closure $next, string ...$roles): Response
    {
        if (! $request->user()->hasRole(...$roles)) {
            alert()->error('Error', 'Anda tidak memiliki akses ke halaman ini.');

            return redirect()->to('/');
        }

        return $next($request);
    }
}
