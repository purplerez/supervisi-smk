<?php

namespace App\SuperAdmin\Controllers;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Tenant\TenantContext;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Str;
use Illuminate\View\View;

class SuperAdminAuthController extends Controller
{
    /**
     * Formulir login Super Admin.
     */
    public function showLoginForm(): View
    {
        // Super admin tidak menggunakan konteks tenant
        TenantContext::clear();

        return view('auth.super-admin-login');
    }

    /**
     * Proses autentikasi Super Admin.
     */
    public function login(Request $request): RedirectResponse
    {
        TenantContext::clear();

        $validated = $request->validate([
            'email' => ['required', 'email'],
            'password' => ['required', 'string'],
        ], [
            'email.required' => 'Alamat email wajib diisi.',
            'email.email' => 'Format email tidak valid.',
            'password.required' => 'Kata sandi wajib diisi.',
        ]);

        $throttleKey = Str::transliterate(
            'login:superadmin:'.strtolower($validated['email']).'|'.$request->ip()
        );

        if (RateLimiter::tooManyAttempts($throttleKey, 5)) {
            $seconds = RateLimiter::availableIn($throttleKey);

            return back()->withInput($request->only('email'))->withErrors([
                'email' => sprintf('Terlalu banyak percobaan masuk yang gagal. Silakan coba lagi dalam %d detik.', $seconds),
            ]);
        }

        // Query super admin tanpa filter tenant (karena dipanggil dari namespace App\SuperAdmin\)
        $user = User::tanpaTenant()
            ->where('email', $validated['email'])
            ->where('is_super_admin', true)
            ->first();

        if (! $user || ! Hash::check($validated['password'], $user->password)) {
            RateLimiter::hit($throttleKey, 60);

            return back()->withInput($request->only('email'))->withErrors([
                'email' => 'Email atau kata sandi Super Admin tidak cocok.',
            ]);
        }

        if (! $user->aktif) {
            return back()->withInput($request->only('email'))->withErrors([
                'email' => 'Akun Super Admin ini telah dinonaktifkan.',
            ]);
        }

        RateLimiter::clear($throttleKey);

        Auth::login($user, $request->boolean('remember'));
        $request->session()->regenerate();

        session(['is_super_admin' => true]);
        TenantContext::clear();

        return redirect()->route('super-admin.dashboard');
    }

    /**
     * Logout Super Admin.
     */
    public function logout(Request $request): RedirectResponse
    {
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();
        TenantContext::clear();

        return redirect()->route('super-admin.login')
            ->with('sukses', 'Anda telah berhasil keluar.');
    }
}
