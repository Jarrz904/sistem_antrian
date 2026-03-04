<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class RoleManager
{
    /**
     * Handle an incoming request.
     */
    public function handle(Request $request, Closure $next, $role): Response
    {
        // 1. Cek apakah user sudah login
        if (!Auth::check()) {
            return redirect()->route('login');
        }

        // 2. Cek apakah role user sesuai dengan yang diminta di route
        $userRole = Auth::user()->role;

        if ($userRole === $role) {
            return $next($request);
        }

        // 3. Jika tidak sesuai, arahkan ke dashboard masing-masing (Proteksi Silang)
        if ($userRole === 'admin') {
            return redirect('/admin')->with('error', 'Anda tidak memiliki akses ke halaman petugas.');
        }

        if ($userRole === 'petugas') {
            return redirect('/petugas')->with('error', 'Anda tidak memiliki akses ke halaman admin.');
        }

        // Jika tidak punya role sama sekali
        Auth::logout();
        return redirect()->route('login')->withErrors(['username' => 'Akses ditolak.']);
    }
}