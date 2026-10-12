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

class RekapRencanaPengembanganSheet implements FromCollection, ShouldAutoSize, WithHeadings, WithMapping, WithStyles, WithTitle
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
                'penilaian' => function ($q) {
                    $q->whereHas('jenisInstrumen', fn ($ji) => $ji->where('kode', 'rencana_pengembangan_diri'));
                },
            ]);

        if ($this->penilaiId) {
            $query->where('penilai_id', $this->penilaiId);
        }

        $penugasanList = $query->get()->sortBy('guru.nama');
        $rows = collect();

        foreach ($penugasanList as $penugasan) {
            $penilaian = $penugasan->penilaian->first();
            $rencanaItems = $penilaian?->data_kualitatif['rencana'] ?? [];

            if (! empty($rencanaItems) && is_array($rencanaItems)) {
                foreach ($rencanaItems as $idx => $r) {
                    $rows->push([
                        'penugasan' => $penugasan,
                        'penilaian' => $penilaian,
                        'item_index' => $idx + 1,
                        'aktivitas' => $r['aktivitas'] ?? '-',
                        'tujuan' => $r['tujuan'] ?? '-',
                        'ukuran' => $r['ukuran'] ?? '-',
                        'dukungan' => $r['dukungan'] ?? '-',
                        'waktu' => $r['waktu'] ?? '-',
                        'status_rencana' => $r['status'] ?? 'Rencana',
                    ]);
                }
            } else {
                $rows->push([
                    'penugasan' => $penugasan,
                    'penilaian' => $penilaian,
                    'item_index' => 1,
                    'aktivitas' => '-',
                    'tujuan' => '-',
                    'ukuran' => '-',
                    'dukungan' => '-',
                    'waktu' => '-',
                    'status_rencana' => '-',
                ]);
            }
        }

        return $rows;
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
            'Status Penilaian',
            'No Item',
            'Aktivitas Pengembangan',
            'Tujuan yang Hendak Dicapai',
            'Ukuran Keberhasilan',
            'Dukungan & Sumber Daya',
            'Target Waktu',
            'Status Rencana',
        ];
    }

    public function map($row): array
    {
        $this->rowNumber++;
        $penugasan = $row['penugasan'];
        $penilaian = $row['penilaian'];

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
            $row['item_index'],
            $row['aktivitas'],
            $row['tujuan'],
            $row['ukuran'],
            $row['dukungan'],
            $row['waktu'],
            $row['status_rencana'],
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
            $sheet->getStyle("G2:H{$highestRow}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
            $sheet->getStyle("M2:N{$highestRow}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
        }

        return [];
    }

    public function title(): string
    {
        return 'Rencana Pengembangan Diri';
    }
}
