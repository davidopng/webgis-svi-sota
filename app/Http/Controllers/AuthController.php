<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class AuthController extends Controller
{
    /**
     * Tampilkan form login SOTA-GIS
     */
    public function showLoginForm()
    {
        // Jika sudah login, langsung arahkan ke dashboard masing-masing
        if (Auth::check()) {
            return $this->redirectBasedOnRole(Auth::user()->role);
        }

        return view('auth.login');
    }

    /**
     * Proses autentikasi login pengguna
     */
    public function login(Request $request)
    {
        $request->validate([
            'username' => ['required', 'string'],
            'password' => ['required', 'string'],
        ], [
            'username.required' => 'Username (ID) wajib diisi.',
            'password.required' => 'Kata sandi wajib diisi.',
        ]);

        $inputUsername = trim($request->input('username'));

        // Pemetaan alias: Mendukung input format ID resmi @sotagis.id maupun username singkat
        $aliasMap = [
            'admin@sotagis.id' => 'admin',
            'operator1@sotagis.id' => 'operator1',
            'operator2@sotagis.id' => 'operator2',
            'kpkampung@sotagis.id' => 'kepala_kampung',
            'kepalakampung@sotagis.id' => 'kepala_kampung',
        ];

        $targetUsername = $aliasMap[strtolower($inputUsername)] ?? $inputUsername;

        $credentials = [
            'username' => $targetUsername,
            'password' => $request->input('password'),
        ];

        $remember = $request->boolean('remember');

        if (Auth::attempt($credentials, $remember)) {
            $request->session()->regenerate();

            /** @var \App\Models\User $user */
            $user = Auth::user();

            return $this->redirectBasedOnRole($user->role);
        }

        return back()->withErrors([
            'username' => 'Username atau kata sandi yang Anda masukkan salah.',
        ])->onlyInput('username');
    }

    /**
     * Redirect dinamis sesuai role pengguna
     */
    protected function redirectBasedOnRole(string $role)
    {
        return match ($role) {
            'admin' => redirect()->intended('/admin'),
            'kepala_kampung' => redirect()->intended('/pimpinan'),
            'operator' => redirect()->intended('/operator'),
            default => redirect('/'),
        };
    }

    /**
     * Logout pengguna dan hancurkan session
     */
    public function logout(Request $request)
    {
        Auth::logout();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect('/login')->with('status', 'Anda telah berhasil keluar dari sistem.');
    }
}
