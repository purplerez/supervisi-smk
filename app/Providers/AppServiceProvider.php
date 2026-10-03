<?php

namespace App\Providers;

use App\SuperAdmin\Auth\SuperAdminUserProvider;
use App\Tenant\TenantContext;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        $this->app->singleton(TenantContext::class, function () {
            return new TenantContext;
        });
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        Auth::provider('multitenant-eloquent', function ($app, array $config) {
            return new SuperAdminUserProvider($app['hash'], $config['model']);
        });
    }
}
