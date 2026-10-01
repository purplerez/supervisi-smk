<?php

namespace App\Http\Middleware;

use App\Models\Sekolah;
use App\Models\User;
use App\Tenant\TenantContext;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class SetTenantContext
{
    /**
     * Handle an incoming request.
     */
    public function handle(Request $request, Closure $next): Response
    {
        $kode = $request->route('kode');

        // Jika route memiliki parameter {kode} sekolah
        if ($kode) {
            $sekolah = Sekolah::where('kode', $kode)->first();

            if (! $sekolah) {
                abort(404, 'Sekolah dengan kode tersebut tidak ditemukan.');
            }

            // Jika sekolah nonaktif dan bukan halaman login, tolak akses
            if ($sekolah->status === 'nonaktif' && ! $request->routeIs('sekolah.login*')) {
                if (Auth::check()) {
                    Auth::logout();
                    $request->session()->invalidate();
                    $request->session()->regenerateToken();
                }

                TenantContext::clear();

                return redirect()->route('sekolah.login', ['kode' => $kode])
                    ->withErrors(['username' => 'Sekolah ini sedang dinonaktifkan. Silakan hubungi administrator.']);
            }

            TenantContext::set($sekolah->id);

            // Jika user telah login, pastikan sekolah_id user sama dengan sekolah pada URL
            if (Auth::check()) {
                /** @var User $user */
                $user = Auth::user();

                if (! $user->is_super_admin && (int) $user->sekolah_id !== (int) $sekolah->id) {
                    Auth::logout();
                    $request->session()->invalidate();
                    $request->session()->regenerateToken();
                    TenantContext::clear();

                    return redirect()->route('sekolah.login', ['kode' => $kode])
                        ->withErrors(['username' => 'Akun Anda tidak terdaftar di sekolah ini.']);
                }
            }
        } elseif (Auth::check()) {
            /** @var User $user */
            $user = Auth::user();

            if ($user->is_super_admin) {
                TenantContext::clear();
            } elseif ($user->sekolah_id) {
                TenantContext::set($user->sekolah_id);
            }
        } else {
            TenantContext::clear();
        }

        return $next($request);
    }
}
