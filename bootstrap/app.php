<?php

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        api: __DIR__.'/../routes/api.php',  // <-- INI YANG DITAMBAHKAN
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware) {
        //
    })
    ->withExceptions(function (Exceptions $exceptions) {
        // Tangani jika token CSRF kadaluwarsa (error 419) agar dialihkan kembali ke login dengan pesan ramah
        $exceptions->render(function (\Illuminate\Session\TokenMismatchException $e, $request) {
            return redirect()->route('login')->with('error', 'Sesi login telah kadaluwarsa karena tidak ada aktivitas. Silakan coba masuk kembali.');
        });
    })->create();