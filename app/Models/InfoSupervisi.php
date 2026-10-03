<?php

namespace App\Models;

use App\Tenant\Traits\BelongsToSekolah;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class InfoSupervisi extends Model
{
    use BelongsToSekolah, HasFactory;

    protected $table = 'info_supervisi';

    protected $fillable = [
        'sekolah_id',
        'penugasan_id',
        'kelas',
        'semester',
        'fase',
        'mata_pelajaran',
        'elemen',
        'cp',
        'catatan',
    ];

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
}
