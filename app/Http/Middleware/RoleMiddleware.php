<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class RoleMiddleware
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     * @param  string  ...$roles
     */
    public function handle(Request $request, Closure $next, ...$roles): Response
    {
        if (!Auth::check()) {
            if ($request->expectsJson() || $request->is('api/*')) {
                return response()->json([
                    'success' => false,
                    'message' => 'Unauthenticated.',
                ], 401);
            }
            return redirect()->route('login');
        }

        $user = Auth::user();
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

        $userRole = $user->role;

        // Map 'admin' alias to 'operator'
        $allowedRoles = array_map(function ($role) {
            return $role === 'admin' ? 'operator' : $role;
        }, $roles);

        if (in_array($userRole, $allowedRoles) || ($userRole === 'operator' && in_array('admin', $roles))) {
            return $next($request);
        }

        if ($request->expectsJson() || $request->is('api/*')) {
            return response()->json([
                'success' => false,
                'message' => 'Akses ditolak. Role Anda tidak memiliki izin.',
            ], 403);
        }

        // Jika role tidak diizinkan, arahkan langsung ke dashboard masing-masing secara seamless
        $redirectRoute = match ($userRole) {
            'operator', 'admin' => 'admin.dashboard',
            'guru' => 'guru.dashboard',
            'siswa' => 'siswa.dashboard',
            'kepala_sekolah' => 'kepala_sekolah.dashboard',
            default => 'login',
        };

        return redirect()->route($redirectRoute);
    }
}
