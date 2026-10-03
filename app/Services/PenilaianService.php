<?php

namespace App\Services;

use App\Models\AuditLog;
use App\Models\Penilaian;
use App\Models\User;
use DomainException;
use Illuminate\Validation\ValidationException;

class PenilaianService
{
    public function __construct(
        private readonly InstrumenResolver $instrumenResolver,
    ) {}

    /**
     * Kunci versi instrumen ke versi aktif saat ini dan ubah status belum -> draft.
     */
    public function inisialisasiDraft(Penilaian $penilaian, User $user): Penilaian
    {
        if ($penilaian->isFinal()) {
            return $penilaian;
        }

        if ($penilaian->penugasan?->periode?->isDitutup()) {
            throw new DomainException('Periode supervisi telah ditutup.');
        }

        if ($penilaian->status === 'belum') {
            $versi = $this->instrumenResolver->resolve(
                $penilaian->jenisInstrumen->kode,
                $penilaian->sekolah_id
            );

            if (! $versi) {
                throw new DomainException('Tidak ada versi instrumen terbit untuk '.$penilaian->jenisInstrumen->nama.'. Silakan hubungi admin sekolah.');
            }

            $penilaian->versi_instrumen_id = $versi->id;
            $penilaian->status = 'draft';
            $penilaian->diperbarui_oleh = $user->id;
            $penilaian->save();
        }

        return $penilaian;
    }

    /**
     * Simpan draf penilaian (skor butir, catatan butir, dan catatan umum).
     *
     * @param  array<int, int|string|null>  $skorButir
     * @param  array<int, string|null>  $catatanButir
     */
    public function simpanDraft(
        Penilaian $penilaian,
        array $skorButir = [],
        array $catatanButir = [],
        ?string $catatan = null,
        ?string $tindakLanjut = null,
        ?User $user = null
    ): Penilaian {
        $user = $user ?? (auth()->user() instanceof User ? auth()->user() : $penilaian->penugasan?->penilai);

        if ($penilaian->isFinal()) {
            throw new DomainException('Penilaian telah difinalisasi dan terkunci.');
        }

        if ($penilaian->penugasan?->periode?->isDitutup()) {
            throw new DomainException('Penilaian tidak dapat diubah karena periode supervisi telah ditutup.');
        }

        if (! in_array($penilaian->status, ['belum', 'draft', 'direvisi'], true)) {
            throw new DomainException('Penilaian tidak dalam status yang dapat diubah.');
        }

        return $penilaian->getConnection()->transaction(function () use (
            $penilaian,
            $skorButir,
            $catatanButir,
            $catatan,
            $tindakLanjut,
            $user
        ) {
            // Jika masih 'belum', inisialisasi versi aktif dan ubah ke 'draft'
            if ($penilaian->status === 'belum') {
                $versi = $this->instrumenResolver->resolve(
                    $penilaian->jenisInstrumen->kode,
                    $penilaian->sekolah_id
                );

                if (! $versi) {
                    throw new DomainException('Tidak ada versi instrumen terbit untuk '.$penilaian->jenisInstrumen->nama.'.');
                }

                $penilaian->versi_instrumen_id = $versi->id;
                $penilaian->status = 'draft';
            }

            // Simpan skor dan catatan per butir
            foreach ($skorButir as $butirId => $skor) {
                if ($skor !== null && $skor !== '') {
                    $penilaian->penilaianButir()->updateOrCreate(
                        ['butir_instrumen_id' => $butirId],
                        [
                            'skor' => (int) $skor,
                            'catatan' => $catatanButir[$butirId] ?? null,
                        ]
                    );
                }
            }

            $penilaian->catatan = $catatan;
            $penilaian->tindak_lanjut = $tindakLanjut;
            $penilaian->diperbarui_oleh = $user?->id ?? $penilaian->penugasan?->penilai_id;
            $penilaian->save();

            return $penilaian;
        });
    }

    /**
     * Finalisasi penilaian: memvalidasi semua butir terisi, menghitung total skor,
     * skor maksimal, nilai persentase, predikat, mengisi finalized_at, dan status final.
     * Dicatat ke tabel audit_log secara rinci.
     */
    public function finalisasi(Penilaian $penilaian, User $user, ?string $ip = null): Penilaian
    {
        if ($penilaian->isFinal()) {
            throw new DomainException('Penilaian sudah berstatus final dan terkunci.');
        }

        if ($penilaian->isBelum()) {
            throw new DomainException('Penilaian belum pernah dimulai.');
        }

        if ($penilaian->penugasan?->periode?->isDitutup()) {
            throw new DomainException('Penilaian tidak dapat difinalisasi karena periode supervisi telah ditutup.');
        }

        if (! in_array($penilaian->status, ['draft', 'direvisi'], true)) {
            throw new DomainException('Penilaian hanya dapat difinalisasi saat berstatus draft atau direvisi.');
        }

        $penilaian->loadMissing(['versiInstrumen.bagian.butir', 'penilaianButir']);

        $versi = $penilaian->versiInstrumen;
        if (! $versi) {
            throw new DomainException('Versi instrumen tidak ditemukan.');
        }

        // Ambil semua butir dari seluruh bagian versi instrumen ini
        $semuaButir = $versi->bagian->flatMap->butir;
        $skorMaks = (int) $semuaButir->sum('skor_maks');

        if ($semuaButir->isEmpty() || $skorMaks <= 0) {
            throw new DomainException('Instrumen belum memiliki butir penilaian yang valid.');
        }

        // Validasi: seluruh butir harus sudah diisi skor (>= 1)
        $jawabanMap = $penilaian->penilaianButir->keyBy('butir_instrumen_id');
        $butirBelumDiisi = [];

        foreach ($semuaButir as $butir) {
            $jawaban = $jawabanMap->get($butir->id);
            if (! $jawaban || $jawaban->skor === null || $jawaban->skor < 1) {
                $butirBelumDiisi[] = $butir->urutan;
            }
        }

        if (! empty($butirBelumDiisi)) {
            throw ValidationException::withMessages([
                'butir' => 'Semua butir instrumen wajib diisi sebelum melakukan finalisasi. Masih ada '.count($butirBelumDiisi).' butir yang belum dinilai.',
            ]);
        }

        return $penilaian->getConnection()->transaction(function () use (
            $penilaian,
            $skorMaks,
            $versi,
            $user,
            $ip
        ) {
            $totalSkor = (int) $penilaian->penilaianButir()->sum('skor');
            $nilai = round(($totalSkor / $skorMaks) * 100, 2);

            // Tentukan predikat berdasarkan ambang_predikat pada versi_instrumen
            $ambang = $versi->ambang_predikat ?? ['A' => 86, 'B' => 76, 'C' => 56];
            $predikat = $this->hitungPredikat($nilai, $ambang);

            // Snapshot sebelum difinalisasi
            $sebelum = $penilaian->only([
                'status',
                'total_skor',
                'skor_maks',
                'nilai',
                'predikat',
                'finalized_at',
                'finalized_by',
            ]);

            $penilaian->update([
                'status' => 'final',
                'total_skor' => $totalSkor,
                'skor_maks' => $skorMaks,
                'nilai' => $nilai,
                'predikat' => $predikat,
                'finalized_at' => now(),
                'finalized_by' => $user->id,
                'diperbarui_oleh' => $user->id,
            ]);

            // Catat audit log rinci
            AuditLog::create([
                'sekolah_id' => $penilaian->sekolah_id,
                'penilaian_id' => $penilaian->id,
                'user_id' => $user->id,
                'aksi' => 'finalisasi',
                'sebelum' => $sebelum,
                'sesudah' => $penilaian->only([
                    'status',
                    'total_skor',
                    'skor_maks',
                    'nilai',
                    'predikat',
                    'finalized_at',
                    'finalized_by',
                ]),
                'alasan' => 'Finalisasi penilaian supervisi',
                'ip' => $ip,
                'created_at' => now(),
            ]);

            return $penilaian;
        });
    }

    /**
     * Hitung predikat huruf (A/B/C/D) berdasarkan nilai desimal dan ambang predikat.
     *
     * @param  array<string, int|float>  $ambang
     */
    public function hitungPredikat(float $nilai, array $ambang): string
    {
        if ($nilai >= (float) ($ambang['A'] ?? 86)) {
            return 'A';
        }
        if ($nilai >= (float) ($ambang['B'] ?? 76)) {
            return 'B';
        }
        if ($nilai >= (float) ($ambang['C'] ?? 56)) {
            return 'C';
        }

        return 'D';
    }

    /**
     * Buka kunci penilaian final oleh Admin sekolah dengan alasan wajib.
     * Status berubah menjadi 'direvisi', jumlah_buka_kunci bertambah, dan tercatat di audit_log.
     */
    public function bukaKunci(
        Penilaian $penilaian,
        User|string $adminOrAlasan,
        User|string $alasanOrAdmin,
        ?string $ip = null
    ): Penilaian {
        if ($adminOrAlasan instanceof User) {
            $admin = $adminOrAlasan;
            $alasan = (string) $alasanOrAdmin;
        } else {
            $alasan = (string) $adminOrAlasan;
            $admin = $alasanOrAdmin instanceof User ? $alasanOrAdmin : auth()->user();
        }
        if (! $admin->hasRole('admin')) {
            throw ValidationException::withMessages([
                'admin' => 'Hanya admin sekolah yang memiliki wewenang untuk membuka kunci penilaian.',
            ]);
        }

        if (blank($alasan)) {
            throw ValidationException::withMessages([
                'alasan' => 'Alasan pembukaan kunci wajib diisi.',
            ]);
        }

        if (! $penilaian->isFinal()) {
            throw new DomainException('Hanya penilaian berstatus final yang dapat dibuka kuncinya.');
        }

        if ($penilaian->penugasan?->periode?->isDitutup()) {
            throw new DomainException('Penilaian pada periode yang telah ditutup tidak dapat dibuka kuncinya. Admin harus membuka kembali periode terlebih dahulu.');
        }

        return $penilaian->getConnection()->transaction(function () use (
            $penilaian,
            $alasan,
            $admin,
            $ip
        ) {
            $sebelum = $penilaian->only([
                'status',
                'jumlah_buka_kunci',
                'finalized_at',
                'finalized_by',
            ]);

            $penilaian->status = 'direvisi';
            $penilaian->jumlah_buka_kunci = ($penilaian->jumlah_buka_kunci ?? 0) + 1;
            $penilaian->diperbarui_oleh = $admin->id;
            $penilaian->save();

            // Catat audit log rinci
            AuditLog::create([
                'sekolah_id' => $penilaian->sekolah_id,
                'penilaian_id' => $penilaian->id,
                'user_id' => $admin->id,
                'aksi' => 'buka_kunci',
                'sebelum' => $sebelum,
                'sesudah' => $penilaian->only(['status', 'jumlah_buka_kunci']),
                'alasan' => trim($alasan),
                'ip' => $ip,
                'created_at' => now(),
            ]);

            return $penilaian;
        });
    }
}
