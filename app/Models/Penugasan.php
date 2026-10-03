<?php

namespace App\Models;

use App\Tenant\Traits\BelongsToSekolah;
use DomainException;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

class Penugasan extends Model
{
    use BelongsToSekolah, HasFactory;

    protected $table = 'penugasan';

    protected $fillable = [
        'sekolah_id',
        'periode_id',
        'guru_id',
        'penilai_id',
    ];

    protected static function booted(): void
    {
        static::saving(function (Penugasan $model) {
            if ($model->guru_id === $model->penilai_id) {
                throw new DomainException('Penilai tidak boleh sama dengan guru yang disupervisi.');
            }
        });

        static::updating(function (Penugasan $model) {
            if ($model->isDirty('penilai_id') && ! $model->bisaDiubahAtauDihapus()) {
                throw new DomainException('Penilai tidak dapat diganti karena penilaian supervisi guru ini sudah dimulai atau diselesaikan.');
            }
        });

        static::deleting(function (Penugasan $model) {
            if (! $model->bisaDiubahAtauDihapus()) {
                throw new DomainException('Penugasan tidak dapat dihapus karena penilaian supervisi guru ini sudah dimulai atau diselesaikan.');
            }
        });
    }

    /**
     * Relasi ke Sekolah pemilik.
     */
    public function sekolah(): BelongsTo
    {
        return $this->belongsTo(Sekolah::class, 'sekolah_id');
    }

    /**
     * Relasi ke Periode supervisi.
     */
    public function periode(): BelongsTo
    {
        return $this->belongsTo(Periode::class, 'periode_id');
    }

    /**
     * Relasi ke Guru yang disupervisi.
     */
    public function guru(): BelongsTo
    {
        return $this->belongsTo(User::class, 'guru_id');
    }

    /**
     * Relasi ke Supervisor / Penilai.
     */
    public function penilai(): BelongsTo
    {
        return $this->belongsTo(User::class, 'penilai_id');
    }

    /**
     * Relasi ke 4 baris penilaian supervisi.
     */
    public function penilaian(): HasMany
    {
        return $this->hasMany(Penilaian::class, 'penugasan_id');
    }

    /**
     * Relasi ke data info supervisi (kelas, mapel, CP).
     */
    public function infoSupervisi(): HasOne
    {
        return $this->hasOne(InfoSupervisi::class, 'penugasan_id');
    }

    /**
     * Relasi ke jadwal supervisi.
     */
    public function jadwalSupervisi(): HasMany
    {
        return $this->hasMany(JadwalSupervisi::class, 'penugasan_id');
    }

    /**
     * Penugasan hanya boleh diubah penilainya atau dihapus bila keempat penilaiannya masih 'belum'.
     */
    public function bisaDiubahAtauDihapus(): bool
    {
        // Jika belum ada baris penilaian, boleh diubah/dihapus
        if ($this->penilaian()->count() === 0) {
            return true;
        }

        // Cek apakah ada penilaian yang statusnya bukan 'belum'
        return ! $this->penilaian()->where('status', '!=', 'belum')->exists();
    }

    /**
     * Guru hanya boleh mengubah informasi dan jadwal supervisi selama keempat
     * penilaian masih berstatus 'belum' dan periode belum ditutup.
     * Setelah salah satu penilaian dimulai (draft/final/direvisi), form dikunci bagi guru.
     */
    public function guruBisaUbahInfoDanJadwal(): bool
    {
        if ($this->periode?->isDitutup()) {
            return false;
        }

        if ($this->penilaian()->count() === 0) {
            return true;
        }

        return ! $this->penilaian()->where('status', '!=', 'belum')->exists();
    }
}
