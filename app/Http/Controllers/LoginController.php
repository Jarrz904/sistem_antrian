<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class LoginController extends Controller
{
    // 1. Menampilkan Halaman Login
    public function index() 
    {
        return view('auth.login');
    }

    // 2. Proses Login
    public function login(Request $request) 
    {
        $credentials = $request->validate([
            'username' => 'required',
            'password' => 'required',
        ]);

        if (Auth::attempt($credentials)) {
            $request->session()->regenerate();

            // Ambil data user yang baru saja login
            $user = Auth::user();

            // Logika Pengalihan Berdasarkan Role
            if ($user->role === 'admin') {
                return redirect('/admin'); 
            } elseif ($user->role === 'petugas') {
                return redirect('/petugas');
            }

            // Jika role tidak dikenali, logout dan balik ke login
            Auth::logout();
            return redirect('/login')->withErrors(['username' => 'Role tidak valid.']);
        }

        return back()->withErrors(['username' => 'Username atau password salah.']);
    }

    // 3. Proses Logout (PENTING: Jangan sampai tertinggal)
    public function logout(Request $request) 
    {
        Auth::logout();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect('/login');
    }
}