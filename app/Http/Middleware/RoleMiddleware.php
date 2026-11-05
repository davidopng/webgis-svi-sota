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
            return redirect()->guest('/login')->withErrors([
                'auth' => 'Silakan masuk terlebih dahulu untuk mengakses halaman ini.',
            ]);
        }

        $userRole = Auth::user()->role ?? '';

        // Pecah seluruh parameter roles (mendukung pemisahan koma, array, dan spasi)
        $allowedRoles = [];
        foreach ($roles as $role) {
            foreach (explode(',', (string)$role) as $r) {
                $trimmed = trim($r);
                if ($trimmed !== '') {
                    $allowedRoles[] = $trimmed;
                }
            }
        }

        // 1. Jika peran user persis ada di daftar peran yang diizinkan
        if (in_array($userRole, $allowedRoles, true)) {
            return $next($request);
        }

        // 2. Alias kepala_kampung <-> pimpinan
        if (
            ($userRole === 'kepala_kampung' && (in_array('pimpinan', $allowedRoles, true) || in_array('kepala_kampung', $allowedRoles, true))) ||
            ($userRole === 'pimpinan' && (in_array('kepala_kampung', $allowedRoles, true) || in_array('pimpinan', $allowedRoles, true)))
        ) {
            return $next($request);
        }

        // Jika user mencoba mengakses rute pimpinan tanpa izin Kepala Kampung
        if ($request->is('pimpinan*')) {
            abort(403, 'Akses Ditolak. Anda tidak memiliki izin untuk mengakses halaman laporan pimpinan.');
        }

        // Fallback jika tidak berhak pada rute lainnya (admin / operator)
        abort(403, 'Akses Ditolak. Anda tidak memiliki izin untuk mengakses halaman ini.');
    }
}

