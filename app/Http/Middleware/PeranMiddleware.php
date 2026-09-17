<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class PeranMiddleware
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     * @param  string  ...$peran
     */
    public function handle(Request $request, Closure $next, ...$peran): Response
    {
        if (!auth()->check()) {
            return redirect()->route('masuk')->with('error', 'Silakan masuk terlebih dahulu untuk mengakses halaman ini.');
        }

        $pengguna = auth()->user();

        if (in_array($pengguna->peran, $peran)) {
            return $next($request);
        }

        // Arahkan ke dashboard yang sesuai jika mencoba mengakses rute lain
        switch ($pengguna->peran) {
            case 'superadmin':
                return redirect()->route('superadmin.dashboard');
            case 'admin':
                return redirect()->route('admin.dashboard');
            case 'asesor':
                return redirect()->route('asesor.dashboard');
            case 'asesi':
            default:
                return redirect()->route('asesi.dashboard');
        }
    }
}
