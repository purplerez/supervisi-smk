<?php

namespace App\Http\Middleware;

use App\Models\User;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class EnsureUserRole
{
    /**
     * Handle an incoming request.
     */
    public function handle(Request $request, Closure $next, string $role): Response
    {
        if (! Auth::check()) {
            $kode = $request->route('kode') ?? session('sekolah_kode');

            if ($kode) {
                return redirect()->route('sekolah.login', ['kode' => $kode]);
            }

            return redirect()->route('super-admin.login');
        }

        /** @var User $user */
        $user = Auth::user();

        // Pastikan user memiliki role tersebut
        if (! $user->hasRole($role)) {
            abort(403, sprintf('Akses ditolak. Anda tidak memiliki hak akses sebagai %s.', ucfirst($role)));
        }

        // Sinkronkan active_role di session
        session(['active_role' => $role]);

        return $next($request);
    }
}
