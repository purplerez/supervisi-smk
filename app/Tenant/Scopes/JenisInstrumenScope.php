<?php

namespace App\Tenant\Scopes;

use App\Tenant\Exceptions\TenantContextMissingException;
use App\Tenant\TenantContext;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Scope;

/**
 * Pengecualian Resmi Domain Instrumen (docs/DESAIN.md 3.3):
 *
 * Pada model bertenant biasa, SekolahScope membatasi data HANYA pada sekolah_id = $sekolahId.
 * Namun pada tabel jenis_instrumen, baris dengan sekolah_id = NULL merupakan Template Global
 * yang disediakan oleh super-admin dan berhak dibaca (read-only) oleh seluruh sekolah.
 *
 * Scope khusus ini secara eksplisit mengizinkan pembacaan:
 * (sekolah_id = $sekolahId OR sekolah_id IS NULL)
 *
 * Ketentuan Keamanan:
 * 1. Scope umum (SekolahScope) TETAP KETAT dan TIDAK DILONGGARKAN.
 * 2. Tetap fail-closed: jika TenantContext kosong di luar namespace super-admin,
 *    scope ini melempar TenantContextMissingException.
 * 3. Baris sekolah lain (sekolah_id = sekolah B) TETAP DITOLAK (tidak dapat dibaca sekolah A).
 * 4. Pengguna sekolah TIDAK DIIZINKAN mengubah/menghapus baris global (sekolah_id IS NULL).
 */
class JenisInstrumenScope implements Scope
{
    /**
     * Terapkan scope jenis instrumen.
     *
     * @throws TenantContextMissingException
     */
    public function apply(Builder $builder, Model $model): void
    {
        $sekolahId = TenantContext::get();

        if ($sekolahId === null) {
            // Jika dieksekusi di konsol (seeder/artisan) atau oleh super-admin,
            // batasi query HANYA pada template global (sekolah_id IS NULL).
            // Data bertenant sekolah lain tetap 100% terlindungi.
            if (app()->runningInConsole() || (auth()->check() && auth()->user()->is_super_admin)) {
                $builder->whereNull($model->qualifyColumn('sekolah_id'));

                return;
            }

            throw new TenantContextMissingException(
                sprintf('TenantContext kosong saat mengakses model [%s]. Operasi ditolak demi keamanan isolasi tenant (fail-closed).', get_class($model))
            );
        }

        $builder->where(function (Builder $query) use ($model, $sekolahId) {
            $query->where($model->qualifyColumn('sekolah_id'), '=', $sekolahId)
                ->orWhereNull($model->qualifyColumn('sekolah_id'));
        });
    }
}
