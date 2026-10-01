<?php

namespace App\Http\Middleware;

use App\Models\User;
use App\Tenant\TenantContext;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class EnsureSuperAdmin
{
    /**
     * Handle an incoming request.
     */
    public function handle(Request $request, Closure $next): Response
    {
        if (! Auth::check()) {
            return redirect()->route('super-admin.login');
        }

        /** @var User $user */
        $user = Auth::user();

        if (! $user->is_super_admin) {
            abort(403, 'Akses khusus Super Admin.');
        }

        TenantContext::clear();

        return $next($request);
    }
}
