<?php

namespace App\Services;

use App\Models\Penugasan;
use App\Models\Periode;
use App\Models\SuratTugas;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Validation\ValidationException;
use PhpOffice\PhpWord\Element\Table;
use PhpOffice\PhpWord\IOFactory;
use PhpOffice\PhpWord\PhpWord;
use PhpOffice\PhpWord\SimpleType\Jc;
use PhpOffice\PhpWord\SimpleType\JcTable;
use PhpOffice\PhpWord\SimpleType\TblWidth;

class SuratTugasService
{
    /**
     * Mengambil daftar supervisor yang memiliki penugasan pada periode tertentu
     * beserta status surat tugas masing-masing (belum, terbit, perlu_ulang).
     *
     * @return Collection<int, array{
     *     penilai: User,
     *     jumlah_guru: int,
     *     guru_list: array<array{id: int, nama: string, nip: ?string, nuptk: ?string}>,
     *     surat_tugas: ?SuratTugas,
     *     status: 'belum'|'terbit'|'perlu_ulang'
     * }>
     */
    public function daftarPenilaiDenganStatusSurat(Periode $periode): Collection
    {
        // Ambil semua penugasan pada periode ini beserta relasi guru dan penilai
        $penugasanGrouped = Penugasan::where('periode_id', $periode->id)
            ->with(['guru', 'penilai'])
            ->get()
            ->groupBy('penilai_id');

        // Ambil semua surat tugas yang sudah ada pada periode ini
        $suratTugasMap = SuratTugas::where('periode_id', $periode->id)
            ->get()
            ->keyBy('penilai_id');

        $hasil = collect();

        foreach ($penugasanGrouped as $penilaiId => $penugasanList) {
            $penilai = $penugasanList->first()?->penilai;
            if (! $penilai) {
                continue;
            }

            /** @var SuratTugas|null $suratTugas */
            $suratTugas = $suratTugasMap->get($penilaiId);

            $currentGuruList = $penugasanList->map(function (Penugasan $p) {
                return [
                    'id' => $p->guru->id,
                    'nama' => $p->guru->nama,
                    'nip' => $p->guru->nip,
                    'nuptk' => $p->guru->nuptk,
                ];
            })->values()->toArray();

            $currentGuruIds = array_column($currentGuruList, 'id');

            $status = 'belum';
            if ($suratTugas) {
                if ($suratTugas->isPerluDiterbitkanUlang($currentGuruIds)) {
                    $status = 'perlu_ulang';
                } else {
                    $status = 'terbit';
                }
            }

            $hasil->push([
                'penilai' => $penilai,
                'jumlah_guru' => count($currentGuruList),
                'guru_list' => $currentGuruList,
                'surat_tugas' => $suratTugas,
                'status' => $status,
            ]);
        }

        // Urutkan berdasarkan nama penilai
        return $hasil->sortBy(fn ($item) => $item['penilai']->nama)->values();
    }

    /**
     * Terbitkan atau perbarui surat tugas untuk supervisor pada periode tertentu.
     *
     * @param  array{
     *     nomor_surat: string,
     *     tanggal_surat: string,
     *     penandatangan_nama: string,
     *     penandatangan_nip?: ?string,
     *     penandatangan_jabatan?: ?string
     * }  $data
     */
    public function simpanSuratTugas(Periode $periode, int $penilaiId, array $data): SuratTugas
    {
        $penugasanList = Penugasan::where('periode_id', $periode->id)
            ->where('penilai_id', $penilaiId)
            ->with('guru')
            ->get();

        if ($penugasanList->isEmpty()) {
            throw ValidationException::withMessages([
                'penilai_id' => 'Supervisor ini belum memiliki guru yang ditugaskan pada periode yang dipilih.',
            ]);
        }

        // Buat snapshot daftar guru saat surat diterbitkan
        $daftarGuru = $penugasanList->map(function (Penugasan $p) {
            return [
                'id' => $p->guru->id,
                'nama' => $p->guru->nama,
                'nip' => $p->guru->nip,
                'nuptk' => $p->guru->nuptk,
            ];
        })->values()->toArray();

        return SuratTugas::updateOrCreate(
            [
                'periode_id' => $periode->id,
                'penilai_id' => $penilaiId,
            ],
            [
                'nomor_surat' => trim($data['nomor_surat']),
                'tanggal_surat' => $data['tanggal_surat'],
                'penandatangan_nama' => trim($data['penandatangan_nama']),
                'penandatangan_nip' => isset($data['penandatangan_nip']) && filled($data['penandatangan_nip']) ? trim($data['penandatangan_nip']) : null,
                'penandatangan_jabatan' => isset($data['penandatangan_jabatan']) && filled($data['penandatangan_jabatan']) ? trim($data['penandatangan_jabatan']) : 'Kepala Sekolah',
                'daftar_guru' => $daftarGuru,
                'diterbitkan_at' => now(),
            ]
        );
    }

    /**
     * Buat file DOCX untuk surat tugas dan simpan ke file temporary.
     * Mengembalikan path absolut file temporary yang harus dihapus setelah dikirim.
     */
    public function buatDokumenDocx(SuratTugas $suratTugas): string
    {
        $suratTugas->loadMissing(['sekolah', 'periode', 'penilai']);

        $sekolah = $suratTugas->sekolah;
        $periode = $suratTugas->periode;
        $penilai = $suratTugas->penilai;

        $phpWord = new PhpWord;
        $phpWord->setDefaultFontName('Times New Roman');
        $phpWord->setDefaultFontSize(12);

        // Konfigurasi Halaman A4 dengan margin standar
        // 1 cm = 567 twips
        $section = $phpWord->addSection([
            'paperSize' => 'A4',
            'marginTop' => 1134,    // 2.0 cm
            'marginBottom' => 1134, // 2.0 cm
            'marginLeft' => 1418,   // 2.5 cm
            'marginRight' => 1418,  // 2.5 cm
        ]);

        // === 1. KOP SURAT SEKOLAH ===
        $kopTable = $section->addTable([
            'alignment' => JcTable::CENTER,
            'width' => 100 * 50,
            'unit' => TblWidth::PERCENT,
        ]);
        $kopRow = $kopTable->addRow();
        $kopCell = $kopRow->addCell(9000);

        $kopCell->addText(
            mb_strtoupper($sekolah->nama ?? 'SEKOLAH'),
            ['name' => 'Times New Roman', 'size' => 14, 'bold' => true],
            ['alignment' => Jc::CENTER, 'spaceAfter' => 40]
        );

        if (! empty($sekolah->alamat)) {
            $kopCell->addText(
                $sekolah->alamat,
                ['name' => 'Times New Roman', 'size' => 10, 'italic' => false],
                ['alignment' => Jc::CENTER, 'spaceAfter' => 60]
            );
        }

        // Garis pemisah kop surat
        $kopCell->addBottomBorder(['size' => 12, 'color' => '000000', 'style' => 'single']);
        $section->addTextBreak(1);

        // === 2. JUDUL SURAT TUGAS & NOMOR ===
        $section->addText(
            'SURAT TUGAS',
            ['name' => 'Times New Roman', 'size' => 13, 'bold' => true, 'underline' => 'single'],
            ['alignment' => Jc::CENTER, 'spaceAfter' => 30]
        );
        $section->addText(
            'Nomor: '.$suratTugas->nomor_surat,
            ['name' => 'Times New Roman', 'size' => 11],
            ['alignment' => Jc::CENTER, 'spaceAfter' => 200]
        );

        // === 3. KALIMAT PEMBUKA ===
        $jabatanPenandatangan = $suratTugas->penandatangan_jabatan ?: 'Kepala Sekolah';
        $section->addText(
            "Yang bertanda tangan di bawah ini, {$jabatanPenandatangan} {$sekolah->nama}, memberikan tugas supervisi pembelajaran kepada:",
            ['name' => 'Times New Roman', 'size' => 11],
            ['alignment' => Jc::BOTH, 'spaceAfter' => 120]
        );

        // === 4. IDENTITAS PENILAI (SUPERVISOR) ===
        $identitasTable = $section->addTable([
            'alignment' => JcTable::CENTER,
            'width' => 100 * 50,
            'unit' => TblWidth::PERCENT,
        ]);

        $this->tambahBarisIdentitas($identitasTable, 'Nama', $penilai->nama);
        $nipNuptk = $penilai->nip ?: ($penilai->nuptk ?: '-');
        $this->tambahBarisIdentitas($identitasTable, 'NIP / NUPTK', $nipNuptk);
        $this->tambahBarisIdentitas($identitasTable, 'Jabatan / Peran', 'Supervisor / Penilai Supervisi');

        $section->addTextBreak(1);

        // === 5. KALIMAT PENUGASAN DENGAN INFO PERIODE ===
        $tglMulai = Carbon::parse($periode->tanggal_mulai)->locale('id')->isoFormat('D MMMM Y');
        $tglSelesai = Carbon::parse($periode->tanggal_selesai)->locale('id')->isoFormat('D MMMM Y');
        $semesterInfo = $periode->semester ? " Semester {$periode->semester}" : '';

        $section->addText(
            "Untuk melaksanakan kegiatan supervisi pembelajaran pada {$periode->nama} Tahun Ajaran {$periode->tahun_ajaran}{$semesterInfo} (Periode: {$tglMulai} s.d. {$tglSelesai}) terhadap guru-guru sebagai berikut:",
            ['name' => 'Times New Roman', 'size' => 11],
            ['alignment' => Jc::BOTH, 'spaceAfter' => 140]
        );

        // === 6. TABEL DAFTAR GURU (SNAPSHOT) ===
        $guruTable = $section->addTable([
            'alignment' => JcTable::CENTER,
            'width' => 100 * 50,
            'unit' => TblWidth::PERCENT,
            'borderSize' => 6,
            'borderColor' => '333333',
        ]);

        // Header tabel
        $guruTable->addRow(350);
        $guruTable->addCell(800, ['bgColor' => 'F2F2F2', 'valign' => 'center'])->addText(
            'No',
            ['bold' => true, 'size' => 10],
            ['alignment' => Jc::CENTER, 'spaceAfter' => 0]
        );
        $guruTable->addCell(5200, ['bgColor' => 'F2F2F2', 'valign' => 'center'])->addText(
            'Nama Guru',
            ['bold' => true, 'size' => 10],
            ['alignment' => Jc::LEFT, 'spaceAfter' => 0]
        );
        $guruTable->addCell(3000, ['bgColor' => 'F2F2F2', 'valign' => 'center'])->addText(
            'NIP / NUPTK',
            ['bold' => true, 'size' => 10],
            ['alignment' => Jc::LEFT, 'spaceAfter' => 0]
        );

        // Baris daftar guru dari snapshot
        $daftarGuru = $suratTugas->daftar_guru ?? [];
        if (empty($daftarGuru)) {
            $guruTable->addRow(300);
            $guruTable->addCell(9000, ['gridSpan' => 3])->addText(
                'Tidak ada daftar guru dalam snapshot.',
                ['italic' => true, 'size' => 10],
                ['alignment' => Jc::CENTER, 'spaceAfter' => 0]
            );
        } else {
            foreach ($daftarGuru as $index => $guruItem) {
                $guruTable->addRow(300);
                $guruTable->addCell(800, ['valign' => 'center'])->addText(
                    (string) ($index + 1),
                    ['size' => 10],
                    ['alignment' => Jc::CENTER, 'spaceAfter' => 0]
                );
                $guruTable->addCell(5200, ['valign' => 'center'])->addText(
                    $guruItem['nama'] ?? '-',
                    ['size' => 10],
                    ['alignment' => Jc::LEFT, 'spaceAfter' => 0]
                );
                $nipGuru = ! empty($guruItem['nip']) ? $guruItem['nip'] : (! empty($guruItem['nuptk']) ? $guruItem['nuptk'] : '-');
                $guruTable->addCell(3000, ['valign' => 'center'])->addText(
                    $nipGuru,
                    ['size' => 10],
                    ['alignment' => Jc::LEFT, 'spaceAfter' => 0]
                );
            }
        }

        $section->addTextBreak(1);

        // === 7. KALIMAT PENUTUP ===
        $section->addText(
            'Demikian surat tugas ini diterbitkan untuk dapat dilaksanakan dengan penuh dedikasi dan tanggung jawab.',
            ['name' => 'Times New Roman', 'size' => 11],
            ['alignment' => Jc::BOTH, 'spaceAfter' => 200]
        );

        // === 8. BLOK TANDA TANGAN ===
        $tglSurat = Carbon::parse($suratTugas->tanggal_surat)->locale('id')->isoFormat('D MMMM Y');

        $ttdTable = $section->addTable([
            'alignment' => JcTable::CENTER,
            'width' => 100 * 50,
            'unit' => TblWidth::PERCENT,
        ]);
        $ttdRow = $ttdTable->addRow();
        // Kolom kiri (kosong sebagai spacer)
        $ttdRow->addCell(5000);
        // Kolom kanan (blok tanda tangan)
        $ttdCell = $ttdRow->addCell(4000);

        $ttdCell->addText(
            $tglSurat,
            ['size' => 11],
            ['alignment' => Jc::LEFT, 'spaceAfter' => 20]
        );
        $ttdCell->addText(
            $suratTugas->penandatangan_jabatan ?: 'Kepala Sekolah',
            ['size' => 11],
            ['alignment' => Jc::LEFT, 'spaceAfter' => 500] // Ruang untuk tanda tangan
        );
        $ttdCell->addText(
            $suratTugas->penandatangan_nama,
            ['bold' => true, 'underline' => 'single', 'size' => 11],
            ['alignment' => Jc::LEFT, 'spaceAfter' => 20]
        );
        if (! empty($suratTugas->penandatangan_nip)) {
            $ttdCell->addText(
                'NIP. '.$suratTugas->penandatangan_nip,
                ['size' => 10],
                ['alignment' => Jc::LEFT, 'spaceAfter' => 0]
            );
        }

        // === 9. SATU BARIS ALAMAT LOGIN SEKOLAH ===
        $loginUrl = route('sekolah.login', ['kode' => $sekolah->kode]);
        $section->addTextBreak(2);
        $section->addText(
            "Aplikasi Supervisi Guru — Masuk ke sistem: {$loginUrl}",
            ['name' => 'Times New Roman', 'size' => 8, 'italic' => true, 'color' => '666666'],
            ['alignment' => Jc::RIGHT, 'spaceAfter' => 0]
        );

        $footer = $section->addFooter();
        $footer->addText(
            "Aplikasi Supervisi Guru — Masuk ke sistem: {$loginUrl}",
            ['name' => 'Times New Roman', 'size' => 8, 'italic' => true, 'color' => '666666'],
            ['alignment' => Jc::RIGHT, 'spaceAfter' => 0]
        );

        // Tulis file ke temporary file
        $tempFile = tempnam(sys_get_temp_dir(), 'surat_tugas_');
        $writer = IOFactory::createWriter($phpWord, 'Word2007');
        $writer->save($tempFile);

        return $tempFile;
    }

    /**
     * Helper untuk membuat baris pada tabel identitas supervisor.
     */
    protected function tambahBarisIdentitas(Table $table, string $label, string $nilai): void
    {
        $row = $table->addRow(240);
        $row->addCell(2000)->addText($label, ['size' => 10, 'bold' => false], ['spaceAfter' => 0]);
        $row->addCell(300)->addText(':', ['size' => 10], ['spaceAfter' => 0]);
        $row->addCell(6700)->addText($nilai, ['size' => 10, 'bold' => true], ['spaceAfter' => 0]);
    }
}
