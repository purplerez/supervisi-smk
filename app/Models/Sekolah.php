<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Sekolah extends Model
{
    use HasFactory;

    protected $table = 'sekolah';

    protected $fillable = [
        'nama',
        'npsn',
        'kode',
        'alamat',
        'logo_path',
        'kepala_sekolah_id',
        'status',
    ];

    /**
     * Relasi ke seluruh user di sekolah ini.
     */
    public function users(): HasMany
    {
        return $this->hasMany(User::class, 'sekolah_id');
    }

    /**
     * Relasi ke kepala sekolah (user tertentu).
     */
    public function kepalaSekolah(): BelongsTo
    {
        return $this->belongsTo(User::class, 'kepala_sekolah_id');
    }

    /**
     * Relasi ke seluruh role user di sekolah ini.
     */
    public function roles(): HasMany
    {
        return $this->hasMany(UserRole::class, 'sekolah_id');
    }
}
