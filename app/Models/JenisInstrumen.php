<?php

namespace App\Models;

use App\Tenant\Scopes\JenisInstrumenScope;
use App\Tenant\TenantContext;
use App\Tenant\Traits\BelongsToSekolah;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\Scope;

class JenisInstrumen extends Model
{
    use BelongsToSekolah, HasFactory;

    protected $table = 'jenis_instrumen';

    protected $fillable = [
        'sekolah_id',
        'kode',
        'nama',
        'urutan',
        'aktif',
    ];

    protected function casts(): array
    {
        return [
            'urutan' => 'integer',
            'aktif' => 'boolean',
        ];
    }

    protected static function booted(): void
    {
        // Larang pengguna sekolah mengubah atau menghapus template global
        static::updating(function (JenisInstrumen $model) {
            if ($model->isGlobal() && (TenantContext::get() !== null || (auth()->check() && ! auth()->user()->is_super_admin))) {
                throw new AuthorizationException('Pengguna sekolah dilarang mengubah template instrumen global.');
            }
        });

        static::deleting(function (JenisInstrumen $model) {
            if ($model->isGlobal() && (TenantContext::get() !== null || (auth()->check() && ! auth()->user()->is_super_admin))) {
                throw new AuthorizationException('Pengguna sekolah dilarang menghapus template instrumen global.');
            }
        });
    }

    /**
     * Override scope tenant khusus untuk JenisInstrumen.
     */
    protected static function getSekolahScope(): Scope
    {
        return new JenisInstrumenScope;
    }

    /**
     * Relasi ke seluruh versi instrumen ini.
     */
    public function versi(): HasMany
    {
        return $this->hasMany(VersiInstrumen::class, 'jenis_instrumen_id');
    }

    /**
     * Relasi ke versi terbit terbaru.
     */
    public function versiTerbitTerbaru(): HasOne
    {
        return $this->hasOne(VersiInstrumen::class, 'jenis_instrumen_id')
            ->where('status', 'terbit')
            ->orderByDesc('nomor_versi');
    }

    /**
     * Apakah instrumen ini merupakan template global milik super-admin.
     */
    public function isGlobal(): bool
    {
        return $this->sekolah_id === null;
    }

    /**
     * Scope khusus untuk mengambil template global saja.
     */
    public function scopeGlobalTemplate(Builder $query): Builder
    {
        return $query->whereNull('sekolah_id');
    }

    /**
     * Scope khusus untuk mengambil instrumen kustom milik sekolah saja.
     */
    public function scopeMilikSekolah(Builder $query): Builder
    {
        return $query->whereNotNull('sekolah_id');
    }
}
