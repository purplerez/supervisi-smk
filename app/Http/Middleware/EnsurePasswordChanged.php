<?php

namespace App\Http\Middleware;

use App\Models\User;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class EnsurePasswordChanged
{
    /**
     * Handle an incoming request.
     */
    public function handle(Request $request, Closure $next): Response
    {
        if (Auth::check()) {
            /** @var User $user */
            $user = Auth::user();

            if ($user->must_change_password) {
                // Izinkan rute ganti password dan logout
                if (! $request->routeIs('password.change') && ! $request->routeIs('password.update') && ! $request->routeIs('logout')) {
                    $kode = $request->route('kode') ?? session('sekolah_kode');

                    if ($kode) {
                        return redirect()->route('password.change', ['kode' => $kode])
                            ->with('peringatan', 'Anda wajib mengganti kata sandi awal sebelum dapat mengakses halaman lain.');
                    }
                }
            }
        }

        return $next($request);
    }
}
