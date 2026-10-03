<?php

namespace App\Services;

use App\Exceptions\ImmutableVersionException;
use App\Models\BagianInstrumen;
use App\Models\ButirInstrumen;
use App\Models\JenisInstrumen;
use App\Models\VersiInstrumen;
use App\Tenant\TenantContext;
use InvalidArgumentException;

class InstrumenService
{
    /**
     * Buat versi baru (draft) dengan menyalin seluruh isi dari versi lama.
     */
    public function buatVersiBaru(VersiInstrumen $versiLama): VersiInstrumen
    {
        $jenis = $versiLama->jenisInstrumen;
        $nomorVersiBaru = (int) $jenis->versi()->max('nomor_versi') + 1;

        $versiBaru = VersiInstrumen::create([
            'jenis_instrumen_id' => $jenis->id,
            'nomor_versi' => $nomorVersiBaru,
            'status' => 'draft',
            'skor_maks_butir' => $versiLama->skor_maks_butir,
            'ambang_predikat' => $versiLama->ambang_predikat,
        ]);

        foreach ($versiLama->bagian as $b) {
            $bagianBaru = BagianInstrumen::create([
                'versi_instrumen_id' => $versiBaru->id,
                'kode' => $b->kode,
                'judul' => $b->judul,
                'urutan' => $b->urutan,
                'kategori' => $b->kategori,
            ]);

            foreach ($b->butir as $bt) {
                ButirInstrumen::create([
                    'bagian_id' => $bagianBaru->id,
                    'urutan' => $bt->urutan,
                    'uraian' => $bt->uraian,
                    'skor_maks' => $bt->skor_maks,
                ]);
            }
        }

        return $versiBaru->fresh(['bagian.butir']);
    }

    /**
     * Salin template global menjadi instrumen kustom milik sekolah.
     * Dibuat berstatus 'draft' agar dapat langsung disesuaikan oleh admin sekolah.
     */
    public function salinKeSekolah(JenisInstrumen $templateGlobal, int $sekolahId): JenisInstrumen
    {
        if (! $templateGlobal->isGlobal()) {
            throw new InvalidArgumentException('Hanya template global yang dapat disalin ke sekolah.');
        }

        return TenantContext::runAs($sekolahId, function () use ($templateGlobal, $sekolahId) {
            // Cek apakah sekolah sudah punya jenis instrumen untuk kode ini
            $jenisSekolah = JenisInstrumen::milikSekolah()
                ->where('sekolah_id', $sekolahId)
                ->where('kode', $templateGlobal->kode)
                ->first();

            if (! $jenisSekolah) {
                $jenisSekolah = JenisInstrumen::create([
                    'sekolah_id' => $sekolahId,
                    'kode' => $templateGlobal->kode,
                    'nama' => $templateGlobal->nama,
                    'urutan' => $templateGlobal->urutan,
                    'aktif' => true,
                ]);
            }

            $versiSumber = $templateGlobal->versiTerbitTerbaru ?: $templateGlobal->versi()->latest('nomor_versi')->first();

            if ($versiSumber) {
                $nomorVersiBaru = (int) $jenisSekolah->versi()->max('nomor_versi') + 1;

                $versiBaru = VersiInstrumen::create([
                    'jenis_instrumen_id' => $jenisSekolah->id,
                    'nomor_versi' => $nomorVersiBaru,
                    'status' => 'draft', // Draft agar bisa disesuaikan
                    'skor_maks_butir' => $versiSumber->skor_maks_butir,
                    'ambang_predikat' => $versiSumber->ambang_predikat,
                ]);

                foreach ($versiSumber->bagian as $b) {
                    $bagianBaru = BagianInstrumen::create([
                        'versi_instrumen_id' => $versiBaru->id,
                        'kode' => $b->kode,
                        'judul' => $b->judul,
                        'urutan' => $b->urutan,
                        'kategori' => $b->kategori,
                    ]);

                    foreach ($b->butir as $bt) {
                        ButirInstrumen::create([
                            'bagian_id' => $bagianBaru->id,
                            'urutan' => $bt->urutan,
                            'uraian' => $bt->uraian,
                            'skor_maks' => $bt->skor_maks,
                        ]);
                    }
                }
            }

            return $jenisSekolah->fresh(['versi.bagian.butir']);
        });
    }

    /**
     * Terbitkan versi instrumen (mengubah draft menjadi terbit).
     */
    public function terbitkanVersi(VersiInstrumen $versi): void
    {
        if ($versi->jumlah_butir === 0) {
            throw new InvalidArgumentException('Versi instrumen tidak dapat diterbitkan karena belum memiliki butir penilaian.');
        }

        $versi->update(['status' => 'terbit']);
    }

    /**
     * Geser urutan bagian naik atau turun.
     */
    public function geserUrutanBagian(BagianInstrumen $bagian, string $arah): void
    {
        if ($bagian->versiInstrumen->isTerpakai()) {
            throw new ImmutableVersionException('Urutan bagian tidak boleh diubah pada versi yang sudah terpakai.');
        }

        $versiId = $bagian->versi_instrumen_id;
        $urutanSekarang = $bagian->urutan;

        if ($arah === 'naik') {
            $tetangga = BagianInstrumen::where('versi_instrumen_id', $versiId)
                ->where('urutan', '<', $urutanSekarang)
                ->orderByDesc('urutan')
                ->first();
        } else {
            $tetangga = BagianInstrumen::where('versi_instrumen_id', $versiId)
                ->where('urutan', '>', $urutanSekarang)
                ->orderBy('urutan')
                ->first();
        }

        if ($tetangga) {
            $urutanTetangga = $tetangga->urutan;
            $tetangga->update(['urutan' => $urutanSekarang]);
            $bagian->update(['urutan' => $urutanTetangga]);
        }
    }

    /**
     * Geser urutan butir naik atau turun dalam satu bagian.
     */
    public function geserUrutanButir(ButirInstrumen $butir, string $arah): void
    {
        if ($butir->bagian->versiInstrumen->isTerpakai()) {
            throw new ImmutableVersionException('Urutan butir tidak boleh diubah pada versi yang sudah terpakai.');
        }

        $bagianId = $butir->bagian_id;
        $urutanSekarang = $butir->urutan;

        if ($arah === 'naik') {
            $tetangga = ButirInstrumen::where('bagian_id', $bagianId)
                ->where('urutan', '<', $urutanSekarang)
                ->orderByDesc('urutan')
                ->first();
        } else {
            $tetangga = ButirInstrumen::where('bagian_id', $bagianId)
                ->where('urutan', '>', $urutanSekarang)
                ->orderBy('urutan')
                ->first();
        }

        if ($tetangga) {
            $urutanTetangga = $tetangga->urutan;
            $tetangga->update(['urutan' => $urutanSekarang]);
            $butir->update(['urutan' => $urutanTetangga]);
        }
    }
}
