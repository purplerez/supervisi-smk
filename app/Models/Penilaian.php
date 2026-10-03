<?php

namespace App\Models;

use App\Tenant\Traits\BelongsToSekolah;
use DomainException;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Str;

class Penilaian extends Model
{
    use BelongsToSekolah, HasFactory;

    protected $table = 'penilaian';

    protected $fillable = [
        'ulid',
        'sekolah_id',
        'penugasan_id',
        'jenis_instrumen_id',
        'versi_instrumen_id',
        'status',
        'total_skor',
        'skor_maks',
        'nilai',
        'predikat',
        'catatan',
        'tindak_lanjut',
        'jumlah_buka_kunci',
        'finalized_at',
        'finalized_by',
        'diperbarui_oleh',
    ];

    protected function casts(): array
    {
        return [
            'total_skor' => 'integer',
            'skor_maks' => 'integer',
            'nilai' => 'decimal:2',
            'jumlah_buka_kunci' => 'integer',
            'finalized_at' => 'datetime',
        ];
    }

    protected static function booted(): void
    {
        static::creating(function (Penilaian $model) {
            if (empty($model->ulid)) {
                $model->ulid = (string) Str::ulid();
            }
        });

        static::updating(function (Penilaian $model) {
            // Jika periode sudah ditutup, tolak perubahan pada data penilaian
            if ($model->penugasan?->periode?->isDitutup()) {
                throw new DomainException('Penilaian tidak dapat diubah karena periode supervisi telah ditutup.');
            }

            // Tolak perubahan apapun pada penilaian final, kecuali transisi buka kunci ke 'direvisi'
            if ($model->getOriginal('status') === 'final' && $model->status === 'final' && $model->isDirty()) {
                throw new DomainException('Penilaian telah difinalisasi dan terkunci.');
            }
        });
    }

    /**
     * Route model binding menggunakan ulid demi keamanan URL.
     */
    public function getRouteKeyName(): string
    {
        return 'ulid';
    }

    /**
     * Relasi ke Sekolah pemilik.
     */
    public function sekolah(): BelongsTo
    {
        return $this->belongsTo(Sekolah::class, 'sekolah_id');
    }

    /**
     * Relasi ke Penugasan induk.
     */
    public function penugasan(): BelongsTo
    {
        return $this->belongsTo(Penugasan::class, 'penugasan_id');
    }

    /**
     * Relasi ke Jenis Instrumen (KBM, Administrasi, Pengelolaan Kelas, Perencanaan).
     */
    public function jenisInstrumen(): BelongsTo
    {
        return $this->belongsTo(JenisInstrumen::class, 'jenis_instrumen_id');
    }

    /**
     * Relasi ke Versi Instrumen yang digunakan saat penilaian dimulai.
     */
    public function versiInstrumen(): BelongsTo
    {
        return $this->belongsTo(VersiInstrumen::class, 'versi_instrumen_id');
    }

    /**
     * Relasi ke User yang memfinalisasi penilaian.
     */
    public function finalizedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'finalized_by');
    }

    /**
     * Relasi ke User yang terakhir memperbarui nilai.
     */
    public function diperbaruiOleh(): BelongsTo
    {
        return $this->belongsTo(User::class, 'diperbarui_oleh');
    }

    /**
     * Relasi ke rincian skor butir-butir instrumen.
     */
    public function penilaianButir(): HasMany
    {
        return $this->hasMany(PenilaianButir::class, 'penilaian_id');
    }

    /**
     * Relasi ke riwayat Audit Log penilaian ini.
     */
    public function auditLogs(): HasMany
    {
        return $this->hasMany(AuditLog::class, 'penilaian_id')->latest();
    }

    /**
     * Status helpers.
     */
    public function isBelum(): bool
    {
        return $this->status === 'belum';
    }

    public function isDraft(): bool
    {
        return $this->status === 'draft';
    }

    public function isFinal(): bool
    {
        return $this->status === 'final';
    }

    public function isDirevisi(): bool
    {
        return $this->status === 'direvisi';
    }

    public function isTerkunci(): bool
    {
        return $this->isFinal() || ($this->penugasan?->periode?->isDitutup() ?? false);
    }

    /**
     * Cek apakah penilaian dapat diedit oleh supervisor (hanya saat draft atau direvisi, dan periode belum ditutup).
     */
    public function bisaDiedit(): bool
    {
        if ($this->penugasan?->periode?->isDitutup()) {
            return false;
        }

        return in_array($this->status, ['belum', 'draft', 'direvisi'], true);
    }
}
