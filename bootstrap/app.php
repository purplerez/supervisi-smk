<?php

use App\Http\Middleware\EnsurePasswordChanged;
use App\Http\Middleware\EnsureSuperAdmin;
use App\Http\Middleware\EnsureUserRole;
use App\Http\Middleware\SetTenantContext;
use Illuminate\Auth\AuthenticationException;
use Illuminate\Auth\Middleware\Authenticate;
use Illuminate\Auth\Middleware\Authorize;
use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Foundation\Http\Middleware\ConvertEmptyStringsToNull;
use Illuminate\Foundation\Http\Middleware\PreventRequestsDuringMaintenance;
use Illuminate\Foundation\Http\Middleware\TrimStrings;
use Illuminate\Http\Middleware\ValidatePostSize;
use Illuminate\Http\Request;
use Illuminate\Routing\Middleware\SubstituteBindings;
use Illuminate\Session\Middleware\StartSession;
use Illuminate\View\Middleware\ShareErrorsFromSession;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware): void {
        $middleware->web(append: [
            SetTenantContext::class,
        ]);

        $middleware->alias([
            'tenant' => SetTenantContext::class,
            'must.change.password' => EnsurePasswordChanged::class,
            'role' => EnsureUserRole::class,
            'super.admin' => EnsureSuperAdmin::class,
        ]);

        $middleware->priority([
            PreventRequestsDuringMaintenance::class,
            ValidatePostSize::class,
            TrimStrings::class,
            ConvertEmptyStringsToNull::class,
            StartSession::class,
            ShareErrorsFromSession::class,
            SetTenantContext::class,
            Authenticate::class,
            EnsurePasswordChanged::class,
            EnsureUserRole::class,
            EnsureSuperAdmin::class,
            SubstituteBindings::class,
            Authorize::class,
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        $exceptions->shouldRenderJsonWhen(
            fn (Request $request) => $request->is('api/*') || $request->expectsJson(),
        );

        // Redirect ke login yang sesuai berdasarkan jalur request
        $exceptions->render(function (AuthenticationException $e, Request $request) {
            if ($request->is('super-admin/*') || $request->is('super-admin')) {
                return redirect()->route('super-admin.login');
            }

            // Untuk rute tenant, coba extract kode sekolah dari URL
            if (preg_match('#^s/([^/]+)#', $request->path(), $m)) {
                return redirect()->route('sekolah.login', ['kode' => $m[1]]);
            }

            // Coba extract dari referer (misal Livewire / AJAX request)
            $referer = $request->headers->get('referer', '');
            if (preg_match('#/s/([^/?#]+)#', $referer, $m)) {
                return redirect()->route('sekolah.login', ['kode' => $m[1]]);
            }

            // Coba dari session
            if ($request->hasSession() && $request->session()->has('sekolah_kode')) {
                return redirect()->route('sekolah.login', ['kode' => $request->session()->get('sekolah_kode')]);
            }

            return redirect()->route('super-admin.login');
        });
    })->create();
