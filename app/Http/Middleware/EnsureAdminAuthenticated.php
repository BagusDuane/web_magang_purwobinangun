<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class EnsureAdminAuthenticated
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        if (!Auth::check() || !Auth::user()->isAdmin() || in_array(Auth::user()->status, ['rejected', 'archived', 'inactive'], true)) {
            $isInactiveAdmin = Auth::check() && Auth::user()->isAdmin() && in_array(Auth::user()->status, ['rejected', 'archived', 'inactive'], true);
            if (Auth::check()) {
                Auth::logout();
                $request->session()->invalidate();
                $request->session()->regenerateToken();
            }

            return redirect()->guest(route('admin.login'))
                ->with('error', $isInactiveAdmin
                    ? 'Akun petugas Anda telah dinonaktifkan (pindah tugas / pensiun).'
                    : 'Silakan masuk ke panel admin petugas terlebih dahulu.');
        }

        return $next($request);
    }
}
