<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\Sekolah;
use App\Models\User;
use App\Tenant\TenantContext;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

class SekolahAuthController extends Controller
{
    /**
     * Tampilkan formulir login sekolah.
     */
    public function showLoginForm(string $kode): View
    {
        $sekolah = Sekolah::where('kode', $kode)->firstOrFail();

        TenantContext::set($sekolah->id);

        return view('auth.sekolah-login', [
            'sekolah' => $sekolah,
        ]);
    }

    /**
     * Proses autentikasi login sekolah.
     */
    public function login(Request $request, string $kode): RedirectResponse
    {
        $sekolah = Sekolah::where('kode', $kode)->firstOrFail();

        // 1. Periksa status sekolah
        if ($sekolah->status === 'nonaktif') {
            return back()->withInput($request->only('username'))->withErrors([
                'username' => 'Sekolah ini sedang dinonaktifkan. Silakan hubungi administrator.',
            ]);
        }

        // 2. Validasi input: HANYA username dan password
        $validated = $request->validate([
            'username' => ['required', 'string'],
            'password' => ['required', 'string'],
        ], [
            'username.required' => 'Nama pengguna (username) wajib diisi.',
            'password.required' => 'Kata sandi (password) wajib diisi.',
        ]);

        // 3. Rate limiting per kombinasi sekolah + username + IP
        $throttleKey = Str::transliterate(
            'login:sekolah:' . $sekolah->id . '|' . strtolower($validated['username']) . '|' . $request->ip()
        );

        if (RateLimiter::tooManyAttempts($throttleKey, 5)) {
            $seconds = RateLimiter::availableIn($throttleKey);

            return back()->withInput($request->only('username'))->withErrors([
                'username' => sprintf('Terlalu banyak percobaan masuk yang gagal. Silakan coba lagi dalam %d detik.', $seconds),
            ]);
        }

        // 4. Cari pengguna dalam lingkup sekolah ini
        $user = TenantContext::runAs($sekolah->id, function () use ($validated) {
            return User::where('username', $validated['username'])->first();
        });

        // 5. Verifikasi kredensial
        if (! $user || ! Hash::check($validated['password'], $user->password)) {
            RateLimiter::hit($throttleKey, 60);

            return back()->withInput($request->only('username'))->withErrors([
                'username' => 'Nama pengguna atau kata sandi yang Anda masukkan salah.',
            ]);
        }

        // 6. Pastikan sekolah_id pengguna cocok dengan sekolah pada URL
        if ((int) $user->sekolah_id !== (int) $sekolah->id) {
            RateLimiter::hit($throttleKey, 60);

            return back()->withInput($request->only('username'))->withErrors([
                'username' => 'Akun Anda tidak terdaftar di sekolah ini.',
            ]);
        }

        // 7. Periksa status aktif pengguna
        if (! $user->aktif) {
            return back()->withInput($request->only('username'))->withErrors([
                'username' => 'Akun Anda telah dinonaktifkan. Silakan hubungi admin sekolah.',
            ]);
        }

        // Berhasil login: bersihkan rate limiter
        RateLimiter::clear($throttleKey);

        Auth::login($user, $request->boolean('remember'));
        $request->session()->regenerate();

        // 8. Tentukan role aktif default (prioritas: admin -> supervisor -> guru)
        $roles = TenantContext::runAs($sekolah->id, fn () => $user->roles()->pluck('role')->all());

        $defaultRole = 'guru';
        if (in_array('admin', $roles, true)) {
            $defaultRole = 'admin';
        } elseif (in_array('supervisor', $roles, true)) {
            $defaultRole = 'supervisor';
        }

        session([
            'active_role' => $defaultRole,
            'sekolah_kode' => $sekolah->kode,
            'sekolah_id' => $sekolah->id,
            'nama_sekolah' => $sekolah->nama,
        ]);

        TenantContext::set($sekolah->id);

        // 9. Periksa apakah wajib ganti password
        if ($user->must_change_password) {
            return redirect()->route('password.change', ['kode' => $sekolah->kode])
                ->with('peringatan', 'Demi keamanan, Anda wajib mengganti kata sandi awal sebelum melanjutkan.');
        }

        return redirect()->intended(route('dashboard.' . $defaultRole, ['kode' => $sekolah->kode]));
    }

    /**
     * Tampilkan halaman formulir wajib ganti password.
     */
    public function showChangePasswordForm(string $kode): View
    {
        $sekolah = Sekolah::where('kode', $kode)->firstOrFail();
        TenantContext::set($sekolah->id);

        return view('auth.ganti-password', [
            'sekolah' => $sekolah,
            'user' => Auth::user(),
        ]);
    }

    /**
     * Proses pembaruan password wajib.
     */
    public function updatePassword(Request $request, string $kode): RedirectResponse
    {
        $sekolah = Sekolah::where('kode', $kode)->firstOrFail();
        TenantContext::set($sekolah->id);

        $request->validate([
            'password' => ['required', 'string', 'min:8', 'confirmed'],
        ], [
            'password.required' => 'Kata sandi baru wajib diisi.',
            'password.min' => 'Kata sandi minimal harus berjumlah 8 karakter.',
            'password.confirmed' => 'Konfirmasi kata sandi baru tidak cocok.',
        ]);

        /** @var User $user */
        $user = Auth::user();

        TenantContext::runAs($sekolah->id, function () use ($user, $request) {
            $user->password = Hash::make($request->password);
            $user->must_change_password = false;
            $user->save();
        });

        $activeRole = session('active_role', 'guru');

        return redirect()->route('dashboard.' . $activeRole, ['kode' => $sekolah->kode])
            ->with('sukses', 'Kata sandi Anda berhasil diperbarui. Selamat datang di Aplikasi Supervisi!');
    }

    /**
     * Ganti role aktif pengguna melalui Role Switcher.
     */
    public function switchRole(Request $request, string $kode): RedirectResponse
    {
        $sekolah = Sekolah::where('kode', $kode)->firstOrFail();
        TenantContext::set($sekolah->id);

        $request->validate([
            'role' => ['required', 'in:admin,supervisor,guru'],
        ]);

        /** @var User $user */
        $user = Auth::user();
        $targetRole = $request->role;

        // Pastikan pengguna benar-benar memiliki role tersebut
        if (! $user->hasRole($targetRole)) {
            abort(403, 'Anda tidak memiliki hak akses sebagai ' . ucfirst($targetRole) . '.');
        }

        session(['active_role' => $targetRole]);

        return redirect()->route('dashboard.' . $targetRole, ['kode' => $sekolah->kode])
            ->with('sukses', 'Peran berhasil dialihkan ke: ' . ucfirst($targetRole));
    }
}
