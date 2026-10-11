<?php

namespace App\Services;

use App\Models\JenisInstrumen;
use App\Models\VersiInstrumen;
use App\Tenant\TenantContext;

class InstrumenResolver
{
    /**
     * 5 Kode baku jenis instrumen supervisi resmi sesuai Kemendikdasmen / BBGTK Jatim.
     */
    public const KODE_STANDAR = [
        'pra_observasi' => 'Lembar Catatan Percakapan Pra-Observasi',
        'telaah_perencanaan' => 'Instrumen Telaah Perencanaan Pembelajaran',
        'observasi_pembelajaran' => 'Instrumen Observasi Pelaksanaan Pembelajaran',
        'refleksi_pasca_observasi' => 'Instrumen Refleksi Pasca Pembelajaran',
        'rencana_pengembangan_diri' => 'Lembar Rencana Pengembangan Diri Guru',
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
     * Resolusi seluruh 5 instrumen standar untuk sekolah.
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
