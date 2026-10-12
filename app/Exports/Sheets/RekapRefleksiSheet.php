<?php

namespace App\Exports\Sheets;

use App\Models\Penugasan;
use App\Models\Periode;
use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\WithTitle;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class RekapRefleksiSheet implements FromCollection, ShouldAutoSize, WithHeadings, WithMapping, WithStyles, WithTitle
{
    private int $rowNumber = 0;

    public function __construct(
        public readonly Periode $periode,
        public readonly ?int $penilaiId = null,
    ) {}

    public function collection(): Collection
    {
        $query = Penugasan::where('periode_id', $this->periode->id)
            ->with([
                'guru',
                'penilai',
                'infoSupervisi',
                'jadwalSupervisi.jenisInstrumen',
                'penilaian' => function ($q) {
                    $q->whereHas('jenisInstrumen', fn ($ji) => $ji->where('kode', 'refleksi_pasca_observasi'))
                        ->with(['penilaianButir', 'versiInstrumen.bagian.butir']);
                },
            ]);

        if ($this->penilaiId) {
            $query->where('penilai_id', $this->penilaiId);
        }

        return $query->get()->sortBy('guru.nama')->values();
    }

    public function headings(): array
    {
        return [
            'No',
            'Nama Guru',
            'NIP',
            'Mata Pelajaran',
            'Kelas',
            'Supervisor Penilai',
            'Status',
            'Pelajaran Diperoleh',
            'Hal Belum Memuaskan & Hambatan',
            'Rencana Tindak Lanjut',
            'Umpan Balik untuk Guru Model',
            'Refleksi Pribadi',
            'Catatan Umum Supervisor',
        ];
    }

    public function map($penugasan): array
    {
        $this->rowNumber++;

        $penilaian = $penugasan->penilaian->first();
        $mapJawaban = $penilaian?->penilaianButir->keyBy('butir_instrumen_id') ?? collect();

        $pelajaranDiperoleh = '';
        $belumMemuaskan = '';
        $rencanaLanjut = '';
        $umpanBalik = '';
        $refleksiPribadi = '';

        if ($penilaian && $penilaian->versiInstrumen) {
            $allButir = $penilaian->versiInstrumen->bagian->flatMap->butir;
            foreach ($allButir as $b) {
                $catatan = $mapJawaban->get($b->id)?->catatan ?? '';
                $teks = strtolower($b->uraian);

                if (str_contains($teks, 'pelajaran apa yang telah diperoleh')) {
                    $pelajaranDiperoleh = $catatan;
                } elseif (str_contains($teks, 'belum memuaskan')) {
                    $belumMemuaskan = $catatan;
                } elseif (str_contains($teks, 'rencana tindak lanjut')) {
                    $rencanaLanjut = $catatan;
                } elseif (str_contains($teks, 'umpan balik untuk guru model')) {
                    $umpanBalik = $catatan;
                } elseif (str_contains($teks, 'refleksi pribadi')) {
                    $refleksiPribadi = $catatan;
                }
            }
        }

        $statusTeks = match ($penilaian?->status) {
            'final' => 'Final (Selesai)',
            'draft' => 'Draft',
            'direvisi' => 'Direvisi',
            default => 'Belum Dinilai',
        };

        return [
            $this->rowNumber,
            $penugasan->guru->nama ?? '-',
            $penugasan->guru->nip ?: '-',
            $penugasan->infoSupervisi?->mata_pelajaran ?: '-',
            $penugasan->infoSupervisi?->kelas ?: '-',
            $penugasan->penilai->nama ?? '-',
            $statusTeks,
            $pelajaranDiperoleh ?: '-',
            $belumMemuaskan ?: '-',
            $rencanaLanjut ?: ($penilaian?->tindak_lanjut ?: '-'),
            $umpanBalik ?: '-',
            $refleksiPribadi ?: '-',
            $penilaian?->catatan ?: '-',
        ];
    }

    public function styles(Worksheet $sheet): array
    {
        $highestRow = $sheet->getHighestRow();
        $highestColumn = $sheet->getHighestColumn();

        $sheet->getStyle("A1:{$highestColumn}1")->applyFromArray([
            'font' => [
                'bold' => true,
                'color' => ['rgb' => 'FFFFFF'],
                'size' => 10,
            ],
            'fill' => [
                'fillType' => Fill::FILL_SOLID,
                'startColor' => ['rgb' => '253C6D'],
            ],
            'alignment' => [
                'horizontal' => Alignment::HORIZONTAL_CENTER,
                'vertical' => Alignment::VERTICAL_CENTER,
                'wrapText' => true,
            ],
        ]);

        $sheet->getRowDimension(1)->setRowHeight(28);

        if ($highestRow > 1) {
            $sheet->getStyle("A1:{$highestColumn}{$highestRow}")->applyFromArray([
                'borders' => [
                    'allBorders' => [
                        'borderStyle' => Border::BORDER_THIN,
                        'color' => ['rgb' => 'D0D7DE'],
                    ],
                ],
                'alignment' => [
                    'vertical' => Alignment::VERTICAL_TOP,
                    'wrapText' => true,
                ],
            ]);

            $sheet->getStyle("A2:A{$highestRow}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
            $sheet->getStyle("G2:G{$highestRow}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
        }

        return [];
    }

    public function title(): string
    {
        return 'Refleksi Pasca-Observasi';
    }
}
