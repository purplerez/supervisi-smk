<?php

namespace App\Models;

use App\Tenant\Traits\BelongsToSekolah;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AuditLog extends Model
{
    use BelongsToSekolah, HasFactory;

    protected $table = 'audit_log';

    public $timestamps = false;

    protected $fillable = [
        'sekolah_id',
        'penilaian_id',
        'user_id',
        'aksi',
        'sebelum',
        'sesudah',
        'alasan',
        'ip',
        'created_at',
    ];

    protected function casts(): array
    {
        return [
            'sebelum' => 'array',
            'sesudah' => 'array',
            'created_at' => 'datetime',
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
     * Relasi ke Penilaian terkait (opsional).
     */
    public function penilaian(): BelongsTo
    {
        return $this->belongsTo(Penilaian::class, 'penilaian_id');
    }

    /**
     * Relasi ke User pelaksana aksi.
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }
}
