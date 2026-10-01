<?php

use App\Http\Controllers\Auth\SekolahAuthController;
use App\Http\Controllers\DashboardController;
use App\SuperAdmin\Controllers\SuperAdminAuthController;
use App\SuperAdmin\Controllers\SuperAdminDashboardController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Web Routes — Aplikasi Supervisi Guru
|--------------------------------------------------------------------------
*/

Route::get('/', function () {
    return view('welcome');
});

// Logout global
Route::post('/logout', [SekolahAuthController::class, 'logout'])->name('logout');

/*
|--------------------------------------------------------------------------
| Super Admin Routes
|--------------------------------------------------------------------------
*/
Route::prefix('super-admin')->name('super-admin.')->group(function () {
    Route::middleware('guest')->group(function () {
        Route::get('/login', [SuperAdminAuthController::class, 'showLoginForm'])->name('login');
        Route::post('/login', [SuperAdminAuthController::class, 'login'])->name('login.post');
    });

    Route::middleware(['auth', 'super.admin'])->group(function () {
        Route::get('/dashboard', [SuperAdminDashboardController::class, 'index'])->name('dashboard');
        Route::post('/logout', [SuperAdminAuthController::class, 'logout'])->name('logout');
    });
});

/*
|--------------------------------------------------------------------------
| Sekolah Tenant Routes (/s/{kode})
|--------------------------------------------------------------------------
*/
Route::prefix('s/{kode}')->group(function () {
    // 1. Halaman Login Sekolah (Guest)
    Route::middleware('guest')->group(function () {
        Route::get('/login', [SekolahAuthController::class, 'showLoginForm'])->name('sekolah.login');
        Route::post('/login', [SekolahAuthController::class, 'login'])->name('sekolah.login.post');
    });

    // 2. Rute Wajib Ganti Password (khusus saat must_change_password = true)
    Route::middleware(['auth', 'tenant'])->group(function () {
        Route::get('/ganti-password', [SekolahAuthController::class, 'showChangePasswordForm'])->name('password.change');
        Route::post('/ganti-password', [SekolahAuthController::class, 'updatePassword'])->name('password.update');
    });

    // 3. Rute Sekolah Terautentikasi (Dibatasi middleware must_change_password)
    Route::middleware(['auth', 'tenant', 'must.change.password'])->group(function () {
        // Ganti Role Aktif
        Route::post('/switch-role', [SekolahAuthController::class, 'switchRole'])->name('role.switch');
        Route::post('/logout', [SekolahAuthController::class, 'logout'])->name('sekolah.logout');

        // Dasbor Admin Sekolah
        Route::middleware('role:admin')->prefix('admin')->group(function () {
            Route::get('/dashboard', [DashboardController::class, 'admin'])->name('dashboard.admin');
        });

        // Dasbor Supervisor / Penilai
        Route::middleware('role:supervisor')->prefix('supervisor')->group(function () {
            Route::get('/dashboard', [DashboardController::class, 'supervisor'])->name('dashboard.supervisor');
        });

        // Dasbor Guru
        Route::middleware('role:guru')->prefix('guru')->group(function () {
            Route::get('/dashboard', [DashboardController::class, 'guru'])->name('dashboard.guru');
        });
    });
});

/*
|--------------------------------------------------------------------------
| Style Guide — hanya tersedia di lingkungan local
|--------------------------------------------------------------------------
*/
if (app()->isLocal()) {
    Route::get('/style-guide', function () {
        return view('style-guide');
    })->name('style-guide');
}
