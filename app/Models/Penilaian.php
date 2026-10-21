<?php

namespace App\Models;

use App\Tenant\Traits\BelongsToSekolah;
use DomainException;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Str;

class Penilaian extends Model
{
    use BelongsToSekolah, HasFactory;

    protected $table = 'penilaian';

    protected $fillable = [
        'ulid',
        'sekolah_id',
        'penugasan_id',
        'jenis_instrumen_id',
        'versi_instrumen_id',
        'status',
        'total_skor',
        'skor_maks',
        'nilai',
        'predikat',
        'catatan',
        'tindak_lanjut',
        'data_kualitatif',
        'jumlah_buka_kunci',
        'finalized_at',
        'finalized_by',
        'diperbarui_oleh',
    ];

    protected function casts(): array
    {
        return [
            'total_skor' => 'integer',
            'skor_maks' => 'integer',
            'nilai' => 'decimal:2',
            'data_kualitatif' => 'array',
            'jumlah_buka_kunci' => 'integer',
            'finalized_at' => 'datetime',
        ];
    }

    protected static function booted(): void
    {
        static::creating(function (Penilaian $model) {
            if (empty($model->ulid)) {
                $model->ulid = (string) Str::ulid();
            }
        });

        static::updating(function (Penilaian $model) {
            // Jika periode sudah ditutup, tolak perubahan pada data penilaian
            if ($model->penugasan?->periode?->isDitutup()) {
                throw new DomainException('Penilaian tidak dapat diubah karena periode supervisi telah ditutup.');
            }

            // Tolak perubahan apapun pada penilaian final, kecuali transisi buka kunci ke 'direvisi'
            if ($model->getOriginal('status') === 'final' && $model->status === 'final' && $model->isDirty()) {
                throw new DomainException('Penilaian telah difinalisasi dan terkunci.');
            }
        });
    }

    /**
     * Route model binding menggunakan ulid demi keamanan URL.
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
     * Relasi ke Penugasan induk.
     */
    public function penugasan(): BelongsTo
    {
        return $this->belongsTo(Penugasan::class, 'penugasan_id');
    }

    /**
     * Relasi ke Jenis Instrumen (KBM, Administrasi, Pengelolaan Kelas, Perencanaan).
     */
    public function jenisInstrumen(): BelongsTo
    {
        return $this->belongsTo(JenisInstrumen::class, 'jenis_instrumen_id');
    }

    /**
     * Relasi ke Versi Instrumen yang digunakan saat penilaian dimulai.
     */
    public function versiInstrumen(): BelongsTo
    {
        return $this->belongsTo(VersiInstrumen::class, 'versi_instrumen_id');
    }

    /**
     * Relasi ke User yang memfinalisasi penilaian.
     */
    public function finalizedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'finalized_by');
    }

    /**
     * Relasi ke User yang terakhir memperbarui nilai.
     */
    public function diperbaruiOleh(): BelongsTo
    {
        return $this->belongsTo(User::class, 'diperbarui_oleh');
    }

    /**
     * Relasi ke rincian skor butir-butir instrumen.
     */
    public function penilaianButir(): HasMany
    {
        return $this->hasMany(PenilaianButir::class, 'penilaian_id');
    }

    /**
     * Relasi ke riwayat Audit Log penilaian ini.
     */
    public function auditLogs(): HasMany
    {
        return $this->hasMany(AuditLog::class, 'penilaian_id')->latest();
    }

    /**
     * Status helpers.
     */
    public function isBelum(): bool
    {
        return $this->status === 'belum';
    }

    public function isDraft(): bool
    {
        return $this->status === 'draft';
    }

    public function isFinal(): bool
    {
        return $this->status === 'final';
    }

    public function isDirevisi(): bool
    {
        return $this->status === 'direvisi';
    }

    public function isTerkunci(): bool
    {
        return $this->isFinal() || ($this->penugasan?->periode?->isDitutup() ?? false);
    }

    /**
     * Cek apakah instrumen merupakan instrumen kuantitatif (memiliki skor numerik).
     */
    public function isKuantitatif(): bool
    {
        $kode = $this->jenisInstrumen?->kode;

        return in_array($kode, ['telaah_perencanaan', 'supervisi_perangkat', 'observasi_pembelajaran'], true);
    }

    /**
     * Cek apakah instrumen merupakan instrumen kualitatif / dialogis / matriks aksi.
     */
    public function isKualitatif(): bool
    {
        return ! $this->isKuantitatif();
    }

    /**
     * Urutan standar 6 instrumen supervisi resmi Kemendikdasmen (5 tahap pelaksanaan).
     * Tahap 2 (Dokumen Perencanaan Pembelajaran) menaungi 2 instrumen: telaah_perencanaan & supervisi_perangkat.
     *
     * @var array<int, string>
     */
    public const URUTAN_KODE = [
        1 => 'pra_observasi',
        2 => 'telaah_perencanaan',
        3 => 'supervisi_perangkat',
        4 => 'observasi_pembelajaran',
        5 => 'refleksi_pasca_observasi',
        6 => 'rencana_pengembangan_diri',
    ];

    /**
     * Dapatkan nomor urutan tahap instrumen ini (1 s/d 5).
     */
    public function getNomorTahapAttribute(): int
    {
        $kode = $this->jenisInstrumen?->kode;
        $tahap = array_search($kode, self::URUTAN_KODE, true);

        return $tahap !== false ? (int) $tahap : 99;
    }

    /**
     * Dapatkan penilaian tahap prasyarat langsung sebelumnya pada penugasan yang sama.
     */
    public function tahapPrasyarat(): ?Penilaian
    {
        $nomorTahap = $this->nomor_tahap;
        if ($nomorTahap <= 1) {
            return null;
        }

        $kodePrasyarat = self::URUTAN_KODE[$nomorTahap - 1] ?? null;
        if (! $kodePrasyarat) {
            return null;
        }

        if ($this->relationLoaded('penugasan') && $this->penugasan?->relationLoaded('penilaian')) {
            return $this->penugasan->penilaian
                ->first(fn ($p) => ($p->jenisInstrumen?->kode ?? '') === $kodePrasyarat);
        }

        return static::where('penugasan_id', $this->penugasan_id)
            ->whereHas('jenisInstrumen', fn ($q) => $q->where('kode', $kodePrasyarat))
            ->first();
    }

    /**
     * Cek apakah penilaian ini terkunci oleh tahap prasyarat yang belum final.
     */
    public function isTerkunciPrasyarat(): bool
    {
        $prasyarat = $this->tahapPrasyarat();
        if (! $prasyarat) {
            return false;
        }

        return ! $prasyarat->isFinal();
    }

    /**
     * Cek apakah instrumen ini diisi mandiri oleh guru terlebih dahulu.
     */
    public function isDiisiMandiriGuru(): bool
    {
        $kode = $this->jenisInstrumen?->kode;

        return in_array($kode, ['pra_observasi', 'refleksi_pasca_observasi', 'rencana_pengembangan_diri'], true);
    }

    /**
     * Cek apakah guru sudah mengirimkan/memfinalisasi pengisian mandirinya ke supervisor.
     */
    public function isGuruSudahSubmit(): bool
    {
        if (! $this->isDiisiMandiriGuru()) {
            return false;
        }

        $data = $this->data_kualitatif ?? [];

        return ! empty($data['guru_selesai']);
    }

    /**
     * Cek apakah instrumen memerlukan tinjauan supervisor (guru sudah kirim tetapi supervisor belum final).
     */
    public function isMenungguTinjauanSupervisor(): bool
    {
        return $this->isDiisiMandiriGuru()
            && $this->isGuruSudahSubmit()
            && ! $this->isFinal();
    }
}
