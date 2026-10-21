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
            'Pra-Observasi',
            'Nilai Telaah Perencanaan',
            'Predikat Telaah Perencanaan',
            'Nilai Supervisi Perangkat 2026',
            'Predikat Supervisi Perangkat 2026',
            'Nilai Observasi KBM',
            'Predikat Observasi KBM',
            'Refleksi Pasca-Observasi',
            'Rencana Pengembangan Diri',
            'Total Nilai',
            'Predikat Akhir',
            'Jumlah Final (n/6)',
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

        // Kelompokkan 6 penilaian berdasarkan kode jenis instrumen
        $penilaianByKode = $penugasan->penilaian->keyBy(function ($p) {
            return $p->jenisInstrumen->kode ?? '';
        });

        $penPra = $penilaianByKode->get('pra_observasi');
        $penTelaah = $penilaianByKode->get('telaah_perencanaan');
        $penSupervisi = $penilaianByKode->get('supervisi_perangkat');
        $penObs = $penilaianByKode->get('observasi_pembelajaran');
        $penRefleksi = $penilaianByKode->get('refleksi_pasca_observasi');
        $penRpd = $penilaianByKode->get('rencana_pengembangan_diri');

        $statusPra = $penPra ? ($penPra->isFinal() ? 'Selesai' : ucfirst($penPra->status)) : '-';
        $nilaiTelaah = ($penTelaah && $penTelaah->isFinal()) ? (float) $penTelaah->nilai : '';
        $predikatTelaah = ($penTelaah && $penTelaah->isFinal()) ? (string) $penTelaah->predikat : '';
        $nilaiSupervisi = ($penSupervisi && $penSupervisi->isFinal()) ? (float) $penSupervisi->nilai : '';
        $predikatSupervisi = ($penSupervisi && $penSupervisi->isFinal()) ? (string) $penSupervisi->predikat : '';
        $nilaiObs = ($penObs && $penObs->isFinal()) ? (float) $penObs->nilai : '';
        $predikatObs = ($penObs && $penObs->isFinal()) ? (string) $penObs->predikat : '';
        $statusRefleksi = $penRefleksi ? ($penRefleksi->isFinal() ? 'Selesai' : ucfirst($penRefleksi->status)) : '-';
        $statusRpd = $penRpd ? ($penRpd->isFinal() ? 'Selesai' : ucfirst($penRpd->status)) : '-';

        // Hitung total nilai (rata-rata instrumen kuantitatif final) dan progres final (n/6)
        $penilaianFinalList = $penugasan->penilaian->filter(fn ($p) => $p->isFinal());
        $finalCount = $penilaianFinalList->count();
        $totalNilai = $penugasan->nilai_akhir !== null ? (float) $penugasan->nilai_akhir : '';
        $predikatAkhir = $penugasan->predikat_akhir ?? '';

        $totalCount = $penugasan->penilaian->count() ?: 6;
        $status = ($finalCount === $totalCount && $totalCount > 0) ? 'Selesai' : 'Belum Selesai';

        return [
            $this->rowNumber,
            $penugasan->guru->nama ?? '-',
            $penugasan->guru->nip ?: '-',
            $penugasan->penilai->nama ?? '-',
            $statusPra,
            $nilaiTelaah,
            $predikatTelaah,
            $nilaiSupervisi,
            $predikatSupervisi,
            $nilaiObs,
            $predikatObs,
            $statusRefleksi,
            $statusRpd,
            $totalNilai,
            $predikatAkhir,
            "{$finalCount}/{$totalCount}",
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

            // Center alignment untuk kolom No, Nilai, Predikat, n/6, dan Status
            $sheet->getStyle("A2:A{$highestRow}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
            $sheet->getStyle("E2:{$highestColumn}{$highestRow}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
        }

        return [];
    }
}
