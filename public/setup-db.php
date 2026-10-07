<?php

use App\Models\User;
use Illuminate\Contracts\Console\Kernel;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

// Load Laravel Bootstrap
require __DIR__.'/../vendor/autoload.php';
$app = require_once __DIR__.'/../bootstrap/app.php';

$kernel = $app->make(Kernel::class);
$kernel->bootstrap();

$results = [];

// 1. Cek Koneksi Database
try {
    DB::connection()->getPdo();
    $results[] = '✅ [1/4] KONEKSI DATABASE: BERHASIL (Nama DB: '.DB::connection()->getDatabaseName().')';
} catch (Throwable $e) {
    $results[] = '❌ [1/4] KONEKSI DATABASE GAGAL: '.$e->getMessage()."\n\nTips: Periksa DB_DATABASE, DB_USERNAME, DB_PASSWORD di file .env dan pastikan user sudah diberi 'ALL PRIVILEGES' ke database di cPanel.";
    echo '<pre style="font-size:15px; font-family:monospace; padding:25px; background:#fff1f2; color:#9f1239; line-height:1.6; border-radius:12px; border:2px solid #fda4af;">'.implode("\n\n------------------------------------\n\n", $results).'</pre>';
    exit;
}

// 2. Generate Key jika belum ada
if (empty(config('app.key'))) {
    try {
        Artisan::call('key:generate', ['--force' => true]);
        $results[] = "✅ [2/4] APP_KEY:\n".trim(Artisan::output());
    } catch (Throwable $e) {
        $results[] = '❌ [2/4] APP_KEY GAGAL: '.$e->getMessage();
    }
} else {
    $results[] = '✅ [2/4] APP_KEY: Sudah terpasang.';
}
// jangan aktifkan step tiga kecuali pada proses setup pertama
// 3. Migrate & Seed
// try {
//     Auth::setUser(new User(['is_super_admin' => true]));
//     Artisan::call('migrate:fresh', ['--seed' => true, '--force' => true]);
//     $results[] = "✅ [3/4] MIGRASI & SEEDER:\n".trim(Artisan::output());
// } catch (Throwable $e) {
//     $results[] = '❌ [3/4] MIGRASI & SEEDER GAGAL: '.$e->getMessage();
// }

// 4. Perbaiki Storage Link (Symlink) khusus Hosting
try {
    $target = storage_path('app/public');
    $link = public_path('storage');

    if (is_link($link)) {
        @unlink($link);
    } elseif (is_dir($link)) {
        @rmdir($link);
    }

    if (file_exists($link) || is_link($link)) {
        @exec('rm -rf '.escapeshellarg($link));
    }

    if (! is_link($link) && ! file_exists($link)) {
        symlink($target, $link);
        $results[] = "✅ [4/4] STORAGE LINK: Berhasil menghubungkan folder public/storage -> {$target}";
    } else {
        $results[] = '⚠️ [4/4] STORAGE LINK: Folder atau symlink "storage" di folder public masih ada. Silakan HAPUS folder/file "storage" di dalam folder repositories/public/ via cPanel File Manager secara manual, lalu refresh halaman ini.';
    }
} catch (Throwable $e) {
    $results[] = '⚠️ [4/4] STORAGE LINK GAGAL: '.$e->getMessage();
}

echo '<pre style="font-size:15px; font-family:monospace; padding:25px; background:#f0fdf4; color:#14532d; line-height:1.6; border-radius:12px; border:2px solid #86efac;">'.implode("\n\n------------------------------------------------------------\n\n", $results).'</pre>';
