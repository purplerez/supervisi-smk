<?php

namespace App\Tenant\Traits;

use App\Models\Sekolah;
use App\Models\User;
use App\Tenant\Exceptions\TenantBypassDisallowedException;
use App\Tenant\Exceptions\TenantContextMissingException;
use App\Tenant\Scopes\SekolahScope;
use App\Tenant\TenantContext;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

trait BelongsToSekolah
{
    /**
     * Boot trait BelongsToSekolah.
     * Mendaftarkan global scope SekolahScope dan hook creating.
     */
    public static function bootBelongsToSekolah(): void
    {
        static::addGlobalScope(new SekolahScope);

        static::creating(function (Model $model) {
            // Super admin tidak terikat pada sekolah manapun (sekolah_id = null)
            if ($model instanceof User && $model->is_super_admin) {
                return;
            }

            $sekolahId = TenantContext::get();

            if ($sekolahId === null) {
                throw new TenantContextMissingException(
                    sprintf('Tidak dapat membuat data [%s] tanpa TenantContext aktif (fail-closed).', get_class($model))
                );
            }

            // Selalu isi/paksa sekolah_id dari context aktif demi keamanan
            $model->sekolah_id = $sekolahId;
        });
    }

    /**
     * Relasi ke model Sekolah.
     */
    public function sekolah(): BelongsTo
    {
        return $this->belongsTo(Sekolah::class, 'sekolah_id');
    }

    /**
     * Satu-satunya jalan bypass scope tenant.
     * HANYA boleh dipanggil dari namespace super-admin (App\SuperAdmin\).
     *
     * @throws TenantBypassDisallowedException bila dipanggil di luar namespace yang sah.
     */
    public static function tanpaTenant(): Builder
    {
        static::pastikanDipanggilDariSuperAdmin();

        return static::withoutGlobalScope(SekolahScope::class);
    }

    /**
     * Validasi pemanggil untuk memastikan hanya namespace super-admin yang dapat membypass tenant.
     *
     * @throws TenantBypassDisallowedException
     */
    protected static function pastikanDipanggilDariSuperAdmin(): void
    {
        $trace = debug_backtrace(DEBUG_BACKTRACE_IGNORE_ARGS, 10);

        $allowedNamespaces = [
            'App\\SuperAdmin\\',
            'Tests\\Feature\\SuperAdmin\\',
            'Tests\\Unit\\SuperAdmin\\',
        ];

        $callerClass = null;

        foreach ($trace as $frame) {
            if (! isset($frame['class'])) {
                continue;
            }

            $class = $frame['class'];

            // Lewati internal trait dan framework Eloquent
            if ($class === self::class
                || $class === static::class
                || is_subclass_of($class, Model::class)
                || is_subclass_of($class, Builder::class)
                || $class === 'Illuminate\\Database\\Eloquent\\Model'
                || $class === 'Illuminate\\Database\\Eloquent\\Builder') {
                continue;
            }

            $callerClass = $class;
            break;
        }

        if ($callerClass === null) {
            throw new TenantBypassDisallowedException('tanpaTenant() dipanggil tanpa kelas pemanggil yang sah.');
        }

        foreach ($allowedNamespaces as $namespace) {
            if (str_starts_with($callerClass, $namespace)) {
                return;
            }
        }

        throw new TenantBypassDisallowedException(
            sprintf('Bypass tanpaTenant() ditolak. Pemanggil [%s] berada di luar namespace super-admin.', $callerClass)
        );
    }
}
