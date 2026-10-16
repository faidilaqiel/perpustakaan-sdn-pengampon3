<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;

class AuthController extends Controller
{
    // ===== TAMPILKAN HALAMAN LOGIN =====
    public function showLogin()
    {
        return view('auth.login');
    }

    // ===== PROSES LOGIN =====
    public function login(Request $request)
    {
        $request->validate([
            'username' => 'required',
            'password' => 'required',
        ], [
            'username.required' => 'Username wajib diisi!',
            'password.required' => 'Password wajib diisi!',
        ]);

        // Cari user berdasarkan NISN, NIP, atau NIK
        $user = \App\Models\User::where('nisn', $request->username)
                                ->orWhere('nip', $request->username)
                                ->orWhere('nik', $request->username)
                                ->first();

        if (!$user) {
            return back()->with('error', 'Username tidak terdaftar!');
        }

        if ($user->status !== 'aktif') {
            return back()->with('error', 'Akun Anda tidak aktif. Hubungi petugas.');
        }

        if (!Hash::check($request->password, $user->password)) {
            return back()->with('error', 'Password salah!');
        }

        // Login (dengan remember me)
        Auth::login($user, $request->has('remember'));
        $request->session()->regenerate();

        // Redirect sesuai role
        return match ($user->role) {
            'siswa', 'guru' => redirect()->route('siswa.katalog'),
            'petugas' => redirect()->route('petugas.dashboard'),
            'kepsek' => redirect()->route('kepsek.laporan'),
            default => redirect('/'),
        };
    }

    // ===== LOGOUT =====
    public function logout(Request $request)
    {
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();
        return redirect()->route('login');
    }
}