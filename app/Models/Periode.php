<?php

namespace App\Models;

use App\Tenant\Traits\BelongsToSekolah;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Periode extends Model
{
    use BelongsToSekolah, HasFactory;

    protected $table = 'periode';

    protected $fillable = [
        'sekolah_id',
        'nama',
        'tahun_ajaran',
        'semester',
        'tanggal_mulai',
        'tanggal_selesai',
        'status',
    ];

    protected function casts(): array
    {
        return [
            'tanggal_mulai' => 'date',
            'tanggal_selesai' => 'date',
        ];
    }

    /**
     * Relasi ke Sekolah pemilik.
     */
    public function sekolah(): BelongsTo
    {
        return $this->belongsTo(Sekolah::class, 'sekolah_id');
    }

    /**
     * Relasi ke penugasan dalam periode ini.
     */
    public function penugasan(): HasMany
    {
        return $this->hasMany(Penugasan::class, 'periode_id');
    }

    /**
     * Cek status periode.
     */
    public function isDraft(): bool
    {
        return $this->status === 'draft';
    }

    public function isAktif(): bool
    {
        return $this->status === 'aktif';
    }

    public function isDitutup(): bool
    {
        return $this->status === 'ditutup';
    }

    /**
     * Scope untuk mengambil periode yang aktif.
     */
    public function scopeAktif(Builder $query): Builder
    {
        return $query->where('status', 'aktif');
    }

    /**
     * Relasi ke surat tugas pada periode ini.
     */
    public function suratTugas(): HasMany
    {
        return $this->hasMany(SuratTugas::class, 'periode_id');
    }
}
