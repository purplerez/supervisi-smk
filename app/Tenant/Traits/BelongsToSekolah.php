<?php

namespace App\Tenant\Traits;

use App\Models\JenisInstrumen;
use App\Models\Sekolah;
use App\Models\User;
use App\Tenant\Exceptions\TenantBypassDisallowedException;
use App\Tenant\Exceptions\TenantContextMissingException;
use App\Tenant\Scopes\SekolahScope;
use App\Tenant\TenantContext;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Scope;

trait BelongsToSekolah
{
    /**
     * Boot trait BelongsToSekolah.
     * Mendaftarkan scope tenant (default: SekolahScope) dan hook creating.
     */
    public static function bootBelongsToSekolah(): void
    {
        static::addGlobalScope(static::getSekolahScope());

        static::creating(function (Model $model) {
            // Super admin tidak terikat pada sekolah manapun (sekolah_id = null)
            if ($model instanceof User && $model->is_super_admin) {
                return;
            }

            // JenisInstrumen template global (sekolah_id = null) milik super-admin
            if ($model instanceof JenisInstrumen && $model->sekolah_id === null) {
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
     * Dapatkan instance scope tenant yang digunakan model ini.
     * Default: SekolahScope. Model khusus seperti JenisInstrumen me-override ini.
     */
    protected static function getSekolahScope(): Scope
    {
        return new SekolahScope;
    }

    /**
     * Dapatkan nama class scope tenant yang digunakan model ini.
     */
    protected static function getSekolahScopeClass(): string
    {
        return get_class(static::getSekolahScope());
    }

    /**
     * Relasi ke model Sekolah.
     */
    public function sekolah(): BelongsTo
    {
        return $this->belongsTo(Sekolah::class, 'sekolah_id');
    }

    /**
     * Satu-satunya jalan bypass scope tenant (static).
     * HANYA boleh dipanggil dari namespace super-admin (App\SuperAdmin\).
     *
     * @throws TenantBypassDisallowedException bila dipanggil di luar namespace yang sah.
     */
    public static function tanpaTenant(): Builder
    {
        static::pastikanDipanggilDariSuperAdmin();

        return static::withoutGlobalScope(static::getSekolahScopeClass());
    }

    /**
     * Scope untuk bypass tenant pada query builder / relasi.
     *
     * @throws TenantBypassDisallowedException bila dipanggil di luar namespace yang sah.
     */
    public function scopeTanpaTenant(Builder $query): Builder
    {
        static::pastikanDipanggilDariSuperAdmin();

        return $query->withoutGlobalScope(static::getSekolahScopeClass());
    }

    /**
     * Validasi pemanggil untuk memastikan hanya namespace super-admin yang dapat membypass tenant.
     *
     * @throws TenantBypassDisallowedException
     */
    protected static function pastikanDipanggilDariSuperAdmin(): void
    {
        $trace = debug_backtrace(DEBUG_BACKTRACE_IGNORE_ARGS, 30);

        $allowedNamespaces = [
            'App\\SuperAdmin\\',
            'Tests\\Feature\\SuperAdmin\\',
            'Tests\\Unit\\SuperAdmin\\',
            'Database\\Seeders\\',
        ];

        $callerClass = null;

        foreach ($trace as $frame) {
            if (! isset($frame['class'])) {
                continue;
            }

            $class = $frame['class'];

            // Lewati internal trait dan seluruh framework Illuminate
            if ($class === self::class
                || $class === static::class
                || str_starts_with($class, 'Illuminate\\')) {
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
