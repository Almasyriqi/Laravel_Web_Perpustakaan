<?php

use App\Http\Middleware\AdminMiddleware;
use App\Http\Middleware\AnggotaMiddleware;
use App\Http\Middleware\PetugasMiddleware;
use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Http\Request;
use RealRashid\SweetAlert\ToSweetAlert;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware): void {
        // Alert SweetAlert yang di-flash ke session ikut dirender di setiap respons web
        $middleware->web(append: [
            ToSweetAlert::class,
        ]);

        $middleware->alias([
            'admin' => AdminMiddleware::class,
            'petugas' => PetugasMiddleware::class,
            'anggota' => AnggotaMiddleware::class,
        ]);

        // Pengguna yang sudah login diarahkan ke /home (HomeController memilah per role)
        $middleware->redirectUsersTo('/home');
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        $exceptions->shouldRenderJsonWhen(
            fn (Request $request) => $request->is('api/*') || $request->expectsJson(),
        );
    })->create();
