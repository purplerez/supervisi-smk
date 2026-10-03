<?php

namespace App\Services;

use App\Models\JenisInstrumen;
use App\Models\VersiInstrumen;
use App\Tenant\TenantContext;

class InstrumenResolver
{
    /**
     * 4 Kode baku jenis instrumen sesuai docs/DESAIN.md 3.3.
     */
    public const KODE_STANDAR = [
        'kbm' => 'Observasi KBM / Proses Pembelajaran',
        'administrasi' => 'Kelengkapan Administrasi Guru',
        'pengelolaan_kelas' => 'Pengelolaan Kelas & Lingkungan Belajar',
        'perencanaan' => 'Perencanaan Pembelajaran / Modul Ajar',
    ];

    /**
     * Resolusi versi aktif untuk satu kode instrumen:
     * 1. Versi terbit terbaru milik sekolah bila ada.
     * 2. Jika tidak ada, versi terbit terbaru milik template global.
     */
    public function resolve(string $kode, ?int $sekolahId = null): ?VersiInstrumen
    {
        $targetSekolahId = $sekolahId ?? TenantContext::get();

        // 1. Cek instrumen kustom milik sekolah
        if ($targetSekolahId !== null) {
            $jenisSekolah = JenisInstrumen::milikSekolah()
                ->where('sekolah_id', $targetSekolahId)
                ->where('kode', $kode)
                ->where('aktif', true)
                ->first();

            if ($jenisSekolah) {
                $versiSekolah = $jenisSekolah->versi()
                    ->where('status', 'terbit')
                    ->orderByDesc('nomor_versi')
                    ->with(['bagian.butir'])
                    ->first();

                if ($versiSekolah) {
                    return $versiSekolah;
                }
            }
        }

        // 2. Fallback ke template global (sekolah_id IS NULL)
        $jenisGlobal = JenisInstrumen::globalTemplate()
            ->where('kode', $kode)
            ->where('aktif', true)
            ->first();

        if ($jenisGlobal) {
            return $jenisGlobal->versi()
                ->where('status', 'terbit')
                ->orderByDesc('nomor_versi')
                ->with(['bagian.butir'])
                ->first();
        }

        return null;
    }

    /**
     * Resolusi seluruh 4 instrumen standar untuk sekolah.
     *
     * @return array<string, ?VersiInstrumen>
     */
    public function resolveSemuaUntukSekolah(?int $sekolahId = null): array
    {
        $hasil = [];

        foreach (array_keys(self::KODE_STANDAR) as $kode) {
            $hasil[$kode] = $this->resolve($kode, $sekolahId);
        }

        return $hasil;
    }
}
