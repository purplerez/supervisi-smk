<?php

use App\Tenant\Traits\BelongsToSekolah;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schema;

/*
|--------------------------------------------------------------------------
| Pest Arch Tests: Tenant Isolation Guardrails (Lapis 4 CI)
|--------------------------------------------------------------------------
*/

arch('dilarang menggunakan DB facade secara langsung di app')
    ->expect('App')
    ->not->toUse('Illuminate\Support\Facades\DB');

test('larangan withoutGlobalScopes, DB::table, DB::select, DB::raw di app/ kecuali allowlist eksplisit', function () {
    // Allowlist eksplisit: hanya BelongsToSekolah yang diizinkan memanggil withoutGlobalScope
    // untuk keperluan bypass resmi tanpaTenant()
    $allowlist = [
        'Tenant/Traits/BelongsToSekolah.php' => ['withoutGlobalScope'],
    ];

    $forbiddenPatterns = [
        'withoutGlobalScopes',
        'withoutGlobalScope',
        'DB::table',
        'DB::select',
        'DB::raw',
    ];

    $appPath = app_path();
    $iterator = new RecursiveIteratorIterator(new RecursiveDirectoryIterator($appPath));
    $violations = [];

    foreach ($iterator as $file) {
        if (! $file->isFile() || $file->getExtension() !== 'php') {
            continue;
        }

        $relativePath = str_replace($appPath.DIRECTORY_SEPARATOR, '', $file->getPathname());
        $content = file_get_contents($file->getPathname());

        foreach ($forbiddenPatterns as $pattern) {
            if (str_contains($content, $pattern)) {
                $allowedForFile = $allowlist[$relativePath] ?? [];

                if (! in_array($pattern, $allowedForFile, true)) {
                    $violations[] = sprintf('File [app/%s] menggunakan pola terlarang [%s]', $relativePath, $pattern);
                }
            }
        }
    }

    expect($violations)->toBeEmpty(
        "Ditemukan pelanggaran Aturan Tenant No. 3 (larangan query mentah / bypass global scope tanpa izin):\n".implode("\n", $violations)
    );
});

test('setiap tabel database dengan kolom sekolah_id wajib dipetakan ke model dengan trait BelongsToSekolah', function () {
    Artisan::call('migrate');

    $excludedTables = [
        'migrations',
        'sessions',
        'cache',
        'cache_locks',
        'jobs',
        'job_batches',
        'failed_jobs',
        'password_reset_tokens',
    ];

    $tables = Schema::getTableListing();

    // Deteksi seluruh model di app/Models
    $modelMap = [];
    foreach (glob(app_path('Models/*.php')) as $modelFile) {
        $className = 'App\\Models\\'.basename($modelFile, '.php');

        if (class_exists($className)) {
            $instance = new $className;
            if ($instance instanceof Model) {
                $modelMap[$instance->getTable()] = $className;
            }
        }
    }

    $checkedTables = 0;

    foreach ($tables as $table) {
        if (in_array($table, $excludedTables, true)) {
            continue;
        }

        if (Schema::hasColumn($table, 'sekolah_id')) {
            $checkedTables++;

            expect($modelMap)->toHaveKey(
                $table,
                "Tabel database [{$table}] memiliki kolom sekolah_id tetapi belum dipetakan ke model Eloquent di app/Models."
            );

            $modelClass = $modelMap[$table];
            $traits = class_uses_recursive($modelClass);

            expect($traits)->toContain(
                BelongsToSekolah::class,
                "Model [{$modelClass}] untuk tabel [{$table}] WAJIB menggunakan trait BelongsToSekolah."
            );
        }
    }

    expect($checkedTables)->toBeGreaterThan(0, 'Harus ada tabel bertenant yang diperiksa skemanya.');
});
