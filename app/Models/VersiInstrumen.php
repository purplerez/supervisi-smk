<?php

namespace App\Models;

use App\Exceptions\ImmutableVersionException;
use App\Tenant\TenantContext;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Facades\Schema;

class VersiInstrumen extends Model
{
    use HasFactory;

    protected $table = 'versi_instrumen';

    protected $fillable = [
        'jenis_instrumen_id',
        'nomor_versi',
        'status',
        'skor_maks_butir',
        'ambang_predikat',
    ];

    protected function casts(): array
    {
        return [
            'nomor_versi' => 'integer',
            'skor_maks_butir' => 'integer',
            'ambang_predikat' => 'array',
        ];
    }

    protected static function booted(): void
    {
        static::updating(function (VersiInstrumen $model) {
            if ($model->isTerpakai()) {
                if ($model->isDirty('ambang_predikat') || $model->isDirty('skor_maks_butir') || $model->isDirty('nomor_versi')) {
                    throw new ImmutableVersionException('Versi instrumen yang sudah dipakai oleh penilaian tidak boleh diubah nilainya.');
                }
            }
        });

        static::deleting(function (VersiInstrumen $model) {
            if ($model->isTerpakai()) {
                throw new ImmutableVersionException('Versi instrumen yang sudah dipakai oleh penilaian tidak boleh dihapus.');
            }
        });
    }

    /**
     * Relasi ke JenisInstrumen induk.
     */
    public function jenisInstrumen(): BelongsTo
    {
        return $this->belongsTo(JenisInstrumen::class, 'jenis_instrumen_id');
    }

    /**
     * Relasi ke bagian-bagian dalam versi ini.
     */
    public function bagian(): HasMany
    {
        return $this->hasMany(BagianInstrumen::class, 'versi_instrumen_id')->orderBy('urutan', 'asc');
    }

    /**
     * Alias relasi bagianInstrumen.
     */
    public function bagianInstrumen(): HasMany
    {
        return $this->bagian();
    }

    /**
     * Apakah versi ini sudah terbit.
     */
    public function isTerbit(): bool
    {
        return $this->status === 'terbit';
    }

    /**
     * Apakah versi ini masih draf.
     */
    public function isDraft(): bool
    {
        return $this->status === 'draft';
    }

    /**
     * Resolver khusus untuk pengetesan apakah versi terpakai sebelum tabel penilaian ada.
     */
    public static ?\Closure $isTerpakaiResolver = null;

    /**
     * Cek apakah versi ini sudah digunakan oleh minimal satu baris penilaian.
     */
    public function isTerpakai(): bool
    {
        if (static::$isTerpakaiResolver !== null) {
            return (bool) call_user_func(static::$isTerpakaiResolver, $this);
        }

        if (! Schema::hasTable('penilaian')) {
            return false;
        }

        $penilaianClass = 'App\\Models\\Penilaian';
        if (class_exists($penilaianClass)) {
            $sekolahId = TenantContext::get() ?? $this->jenisInstrumen?->sekolah_id;

            if ($sekolahId !== null) {
                return TenantContext::runAs($sekolahId, function () use ($penilaianClass) {
                    return $penilaianClass::where('versi_instrumen_id', $this->id)->exists();
                });
            }

            return false;
        }

        return false;
    }

    /**
     * Skor maksimal SELALU dihitung dinamis dari butir (accessor).
     */
    public function getSkorMaksAttribute(): int
    {
        return (int) $this->bagian->sum(fn ($b) => $b->skor_maks);
    }

    /**
     * Total butir SELALU dihitung dinamis dari butir (accessor).
     */
    public function getJumlahButirAttribute(): int
    {
        return (int) $this->bagian->sum(fn ($b) => $b->jumlah_butir);
    }

    /**
     * Total bagian dalam versi ini.
     */
    public function getJumlahBagianAttribute(): int
    {
        return (int) $this->bagian->count();
    }
}
