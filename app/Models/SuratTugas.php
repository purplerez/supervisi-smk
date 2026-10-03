<?php

namespace App\Models;

use App\Tenant\Traits\BelongsToSekolah;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Str;

class SuratTugas extends Model
{
    use BelongsToSekolah, HasFactory;

    protected $table = 'surat_tugas';

    protected $fillable = [
        'ulid',
        'sekolah_id',
        'periode_id',
        'penilai_id',
        'nomor_surat',
        'tanggal_surat',
        'penandatangan_nama',
        'penandatangan_nip',
        'penandatangan_jabatan',
        'daftar_guru',
        'diterbitkan_at',
    ];

    protected function casts(): array
    {
        return [
            'tanggal_surat' => 'date',
            'daftar_guru' => 'array',
            'diterbitkan_at' => 'datetime',
        ];
    }

    protected static function booted(): void
    {
        static::creating(function (SuratTugas $model) {
            if (empty($model->ulid)) {
                $model->ulid = (string) Str::ulid();
            }
        });
    }

    /**
     * Route model binding menggunakan ulid demi keamanan URL dan isolasi ID.
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
     * Relasi ke Periode supervisi.
     */
    public function periode(): BelongsTo
    {
        return $this->belongsTo(Periode::class, 'periode_id');
    }

    /**
     * Relasi ke Pengguna penilai (Supervisor).
     */
    public function penilai(): BelongsTo
    {
        return $this->belongsTo(User::class, 'penilai_id');
    }

    /**
     * Memeriksa apakah surat tugas perlu diterbitkan ulang karena
     * snapshot daftar guru berbeda dengan data penugasan aktif saat ini.
     *
     * @param  array<int>|null  $currentGuruIds
     */
    public function isPerluDiterbitkanUlang(?array $currentGuruIds = null): bool
    {
        if ($currentGuruIds === null) {
            $currentGuruIds = Penugasan::where('periode_id', $this->periode_id)
                ->where('penilai_id', $this->penilai_id)
                ->pluck('guru_id')
                ->map(fn ($id) => (int) $id)
                ->all();
        } else {
            $currentGuruIds = array_map(fn ($id) => (int) $id, $currentGuruIds);
        }

        $snapshotGuruIds = array_map(
            fn ($item) => (int) ($item['id'] ?? 0),
            $this->daftar_guru ?? []
        );

        sort($currentGuruIds);
        sort($snapshotGuruIds);

        return $currentGuruIds !== $snapshotGuruIds;
    }
}
