<?php

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        api: __DIR__.'/../routes/api.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware) {
        $middleware->web(append: [
            \App\Http\Middleware\EnsureSingleSession::class,
        ]);

        $middleware->redirectUsersTo(fn () => route('dashboard'));

        $middleware->alias([
            'role' => \App\Http\Middleware\RoleMiddleware::class,
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions) {
        $exceptions->render(function (\Illuminate\Auth\AuthenticationException $e, \Illuminate\Http\Request $request) {
            if ($request->is('api/*') || $request->expectsJson()) {
                return response()->json([
                    'success' => false,
                    'code' => 'SESSION_EXPIRED',
                    'message' => 'Sesi Anda tidak valid atau telah berakhir karena akun digunakan di perangkat lain. Silakan login kembali.',
                ], 401);
            }
        });

        $exceptions->render(function (\Illuminate\Http\Exceptions\PostTooLargeException $e, $request) {
            return back()->with('error', 'Ukuran file yang Anda unggah melebihi kapasitas maksimum yang diizinkan server. Silakan kompres atau pilih file dengan ukuran lebih kecil (maks. 3 MB).')->withInput();
        });
    })->create();
