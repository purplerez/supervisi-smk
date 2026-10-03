<?php

namespace App\Models;

use App\Exceptions\ImmutableVersionException;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ButirInstrumen extends Model
{
    use HasFactory;

    protected $table = 'butir_instrumen';

    protected $fillable = [
        'bagian_id',
        'urutan',
        'uraian',
        'skor_maks',
    ];

    protected function casts(): array
    {
        return [
            'urutan' => 'integer',
            'skor_maks' => 'integer',
        ];
    }

    protected static function booted(): void
    {
        static::creating(function (ButirInstrumen $model) {
            if ($model->bagian && $model->bagian->versiInstrumen && $model->bagian->versiInstrumen->isTerpakai()) {
                throw new ImmutableVersionException('Tidak boleh menambah butir pada versi instrumen yang sudah dipakai.');
            }
        });

        static::updating(function (ButirInstrumen $model) {
            if ($model->bagian && $model->bagian->versiInstrumen && $model->bagian->versiInstrumen->isTerpakai()) {
                throw new ImmutableVersionException('Butir pada versi instrumen yang sudah dipakai tidak boleh diubah.');
            }
        });

        static::deleting(function (ButirInstrumen $model) {
            if ($model->bagian && $model->bagian->versiInstrumen && $model->bagian->versiInstrumen->isTerpakai()) {
                throw new ImmutableVersionException('Butir pada versi instrumen yang sudah dipakai tidak boleh dihapus.');
            }
        });
    }

    /**
     * Relasi ke BagianInstrumen induk.
     */
    public function bagian(): BelongsTo
    {
        return $this->belongsTo(BagianInstrumen::class, 'bagian_id');
    }
}
