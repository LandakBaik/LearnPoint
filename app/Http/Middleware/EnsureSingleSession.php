<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class EnsureSingleSession
{
    /**
     * Memastikan hanya satu sesi web yang aktif per akun pengguna.
     * Jika pengguna login di perangkat lain, sesi lama akan otomatis tidak valid.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        if (Auth::guard('web')->check() && $request->hasSession()) {
            $user = Auth::guard('web')->user();
            $sessionId = $request->session()->getId();

            if ($user && !empty($user->current_session_id) && $user->current_session_id !== $sessionId) {
                Auth::guard('web')->logout();
                $request->session()->invalidate();
                $request->session()->regenerateToken();

                $message = 'Sesi Anda telah berakhir karena akun ini sedang digunakan di perangkat lain.';

                if ($request->expectsJson()) {
                    return response()->json([
                        'success' => false,
                        'message' => $message,
                    ], 401);
                }

                return redirect()->route('login')->withErrors([
                    'login' => $message,
                ]);
            }
        }

        return $next($request);
    }
}
