<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PenilaianButir extends Model
{
    use HasFactory;

    protected $table = 'penilaian_butir';

    protected $fillable = [
        'penilaian_id',
        'butir_instrumen_id',
        'skor',
        'catatan',
    ];

    protected function casts(): array
    {
        return [
            'skor' => 'integer',
        ];
    }

    /**
     * Relasi ke Penilaian induk.
     */
    public function penilaian(): BelongsTo
    {
        return $this->belongsTo(Penilaian::class, 'penilaian_id');
    }

    /**
     * Relasi ke Butir Instrumen acuan.
     */
    public function butirInstrumen(): BelongsTo
    {
        return $this->belongsTo(ButirInstrumen::class, 'butir_instrumen_id');
    }
}
