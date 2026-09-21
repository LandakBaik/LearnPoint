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
            return redirect()->route('login');
        }

        $user = Auth::user();
        $userRole = $user->role;

        // Map 'admin' alias to 'operator'
        $allowedRoles = array_map(function ($role) {
            return $role === 'admin' ? 'operator' : $role;
        }, $roles);

        if (in_array($userRole, $allowedRoles) || ($userRole === 'operator' && in_array('admin', $roles))) {
            return $next($request);
        }

        // Jika role tidak diizinkan, arahkan ke dashboard masing-masing dengan pesan error
        $redirectRoute = match ($userRole) {
            'operator' => 'operator.dashboard',
            'guru' => 'guru.dashboard',
            'siswa' => 'siswa.dashboard',
            'kepala_sekolah' => 'kepala_sekolah.dashboard',
            default => 'login',
        };

        return redirect()->route($redirectRoute)->with('warning', 'Anda tidak memiliki hak akses untuk membuka halaman tersebut.');
    }
}
