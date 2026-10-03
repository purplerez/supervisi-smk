<?php

namespace App\Models;

use App\Tenant\Traits\BelongsToSekolah;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Str;

class ImportBatch extends Model
{
    use BelongsToSekolah, HasFactory;

    protected $table = 'import_batches';

    protected $fillable = [
        'sekolah_id',
        'user_id',
        'nama_file',
        'total',
        'sukses',
        'gagal',
        'errors',
        'status',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'total' => 'integer',
            'sukses' => 'integer',
            'gagal' => 'integer',
            'errors' => 'array',
        ];
    }

    protected static function booted(): void
    {
        static::creating(function (ImportBatch $batch) {
            if (empty($batch->ulid)) {
                $batch->ulid = (string) Str::ulid();
            }
        });
    }

    /**
     * Gunakan ULID sebagai route key (resource sensitif).
     */
    public function getRouteKeyName(): string
    {
        return 'ulid';
    }

    /**
     * Relasi ke pengguna (admin) yang melakukan impor.
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }
}
