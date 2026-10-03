<?php

namespace App\Models;

use App\Exceptions\ImmutableVersionException;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class BagianInstrumen extends Model
{
    use HasFactory;

    protected $table = 'bagian_instrumen';

    protected $fillable = [
        'versi_instrumen_id',
        'kode',
        'judul',
        'urutan',
        'kategori',
    ];

    protected function casts(): array
    {
        return [
            'urutan' => 'integer',
        ];
    }

    protected static function booted(): void
    {
        static::creating(function (BagianInstrumen $model) {
            if ($model->versiInstrumen && $model->versiInstrumen->isTerpakai()) {
                throw new ImmutableVersionException('Tidak boleh menambah bagian pada versi instrumen yang sudah dipakai.');
            }
        });

        static::updating(function (BagianInstrumen $model) {
            if ($model->versiInstrumen && $model->versiInstrumen->isTerpakai()) {
                throw new ImmutableVersionException('Bagian pada versi instrumen yang sudah dipakai tidak boleh diubah.');
            }
        });

        static::deleting(function (BagianInstrumen $model) {
            if ($model->versiInstrumen && $model->versiInstrumen->isTerpakai()) {
                throw new ImmutableVersionException('Bagian pada versi instrumen yang sudah dipakai tidak boleh dihapus.');
            }
        });
    }

    /**
     * Relasi ke VersiInstrumen induk.
     */
    public function versiInstrumen(): BelongsTo
    {
        return $this->belongsTo(VersiInstrumen::class, 'versi_instrumen_id');
    }

    /**
     * Relasi ke butir-butir dalam bagian ini.
     */
    public function butir(): HasMany
    {
        return $this->hasMany(ButirInstrumen::class, 'bagian_id')->orderBy('urutan', 'asc');
    }

    /**
     * Alias relasi butirInstrumen.
     */
    public function butirInstrumen(): HasMany
    {
        return $this->butir();
    }

    /**
     * Skor maksimal bagian SELALU dihitung dari butir (accessor).
     */
    public function getSkorMaksAttribute(): int
    {
        return (int) $this->butir->sum('skor_maks');
    }

    /**
     * Jumlah butir dalam bagian ini.
     */
    public function getJumlahButirAttribute(): int
    {
        return (int) $this->butir->count();
    }
}
