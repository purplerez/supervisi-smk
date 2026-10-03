<?php

namespace App\Exports\Concerns;

use App\Models\Penugasan;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

trait HasLaporanSupervisiMapping
{
    private int $rowNumber = 0;

    /**
     * Definisi judul kolom Excel.
     * Dipusatkan di sini agar mudah disesuaikan ketika format akhir ditentukan.
     *
     * @return array<int, string>
     */
    public function headings(): array
    {
        return [
            'No',
            'Nama Guru',
            'NIP',
            'Supervisor Penilai',
            'Nilai KBM',
            'Predikat KBM',
            'Nilai Administrasi',
            'Predikat Administrasi',
            'Nilai Pengelolaan Kelas',
            'Predikat Pengelolaan Kelas',
            'Nilai Perencanaan',
            'Predikat Perencanaan',
            'Rata-rata Nilai',
            'Jumlah Final (n/4)',
            'Status',
        ];
    }

    /**
     * Transformasi model Penugasan ke baris data Excel.
     * Nilai dan predikat tiap instrumen kosong bila belum final.
     *
     * @param  Penugasan  $penugasan
     * @return array<int, mixed>
     */
    public function map($penugasan): array
    {
        $this->rowNumber++;

        // Kelompokkan 4 penilaian berdasarkan kode jenis instrumen
        $penilaianByKode = $penugasan->penilaian->keyBy(function ($p) {
            return $p->jenisInstrumen->kode ?? '';
        });

        $penKbm = $penilaianByKode->get('kbm');
        $penAdm = $penilaianByKode->get('administrasi');
        $penKelas = $penilaianByKode->get('pengelolaan_kelas');
        $penRencana = $penilaianByKode->get('perencanaan');

        // Nilai dan predikat hanya tampil jika status final
        $nilaiKbm = ($penKbm && $penKbm->isFinal()) ? (float) $penKbm->nilai : '';
        $predikatKbm = ($penKbm && $penKbm->isFinal()) ? (string) $penKbm->predikat : '';

        $nilaiAdm = ($penAdm && $penAdm->isFinal()) ? (float) $penAdm->nilai : '';
        $predikatAdm = ($penAdm && $penAdm->isFinal()) ? (string) $penAdm->predikat : '';

        $nilaiKelas = ($penKelas && $penKelas->isFinal()) ? (float) $penKelas->nilai : '';
        $predikatKelas = ($penKelas && $penKelas->isFinal()) ? (string) $penKelas->predikat : '';

        $nilaiRencana = ($penRencana && $penRencana->isFinal()) ? (float) $penRencana->nilai : '';
        $predikatRencana = ($penRencana && $penRencana->isFinal()) ? (string) $penRencana->predikat : '';

        // Hitung rata-rata dan progres final (n/4)
        $penilaianFinalList = $penugasan->penilaian->filter(fn ($p) => $p->isFinal());
        $finalCount = $penilaianFinalList->count();

        $rataRata = '';
        if ($finalCount > 0) {
            $rataRata = round($penilaianFinalList->sum('nilai') / $finalCount, 2);
        }

        $status = $finalCount === 4 ? 'Selesai' : 'Belum Selesai';

        return [
            $this->rowNumber,
            $penugasan->guru->nama ?? '-',
            $penugasan->guru->nip ?: '-',
            $penugasan->penilai->nama ?? '-',
            $nilaiKbm,
            $predikatKbm,
            $nilaiAdm,
            $predikatAdm,
            $nilaiKelas,
            $predikatKelas,
            $nilaiRencana,
            $predikatRencana,
            $rataRata,
            "{$finalCount}/4",
            $status,
        ];
    }

    /**
     * Pengaturan gaya tabel spreadsheet (header navy-900, border bersih, tata letak rapi).
     */
    public function styles(Worksheet $sheet): array
    {
        $highestRow = $sheet->getHighestRow();
        $highestColumn = $sheet->getHighestColumn();

        // Style header (baris 1)
        $sheet->getStyle("A1:{$highestColumn}1")->applyFromArray([
            'font' => [
                'bold' => true,
                'color' => ['rgb' => 'FFFFFF'],
                'size' => 11,
            ],
            'fill' => [
                'fillType' => Fill::FILL_SOLID,
                'startColor' => ['rgb' => '253C6D'], // navy-900
            ],
            'alignment' => [
                'horizontal' => Alignment::HORIZONTAL_CENTER,
                'vertical' => Alignment::VERTICAL_CENTER,
                'wrapText' => true,
            ],
        ]);

        $sheet->getRowDimension(1)->setRowHeight(30);

        if ($highestRow > 1) {
            // Border tipis ke seluruh tabel
            $sheet->getStyle("A1:{$highestColumn}{$highestRow}")->applyFromArray([
                'borders' => [
                    'allBorders' => [
                        'borderStyle' => Border::BORDER_THIN,
                        'color' => ['rgb' => 'D0D7DE'],
                    ],
                ],
                'alignment' => [
                    'vertical' => Alignment::VERTICAL_CENTER,
                ],
            ]);

            // Center alignment untuk kolom No, Nilai, Predikat, n/4, dan Status
            $sheet->getStyle("A2:A{$highestRow}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
            $sheet->getStyle("E2:O{$highestRow}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
        }

        return [];
    }
}
