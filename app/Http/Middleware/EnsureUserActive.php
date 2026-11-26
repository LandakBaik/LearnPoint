<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class EnsureUserActive
{
    /**
     * Memastikan user yang sedang terautentikasi (web maupun API Sanctum) tidak berstatus nonaktif.
     */
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user() ?? Auth::user();

        if (!$user) {
            return $next($request);
        }

        $user->loadMissing(['guru', 'siswa']);

        if (
            $user->status === 'nonaktif'
            || $user->guru?->status === 'nonaktif'
            || $user->siswa?->status === 'nonaktif'
        ) {
            if ($request->expectsJson() || $request->is('api/*')) {
                $token = method_exists($user, 'currentAccessToken') ? $user->currentAccessToken() : null;
                if ($token && method_exists($token, 'delete')) {
                    $token->delete();
                }

                return response()->json([
                    'success' => false,
                    'message' => 'Akun Anda saat ini berstatus NONAKTIF. Silakan hubungi pihak administrator/sekolah.',
                ], 403);
            }

            Auth::logout();
            if ($request->hasSession()) {
                $request->session()->invalidate();
                $request->session()->regenerateToken();
            }

            return redirect()->route('login')->withErrors([
                'login' => 'Akun Anda saat ini berstatus NONAKTIF. Silakan hubungi pihak administrator/sekolah.',
            ]);
        }

        return $next($request);
    }
}
