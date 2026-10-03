<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\FromArray;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\WithTitle;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class GuruTemplateExport implements FromArray, ShouldAutoSize, WithHeadings, WithStyles, WithTitle
{
    /**
     * Header kolom template Excel.
     */
    public function headings(): array
    {
        return [
            'nama',
            'username',
            'email',
            'nip',
            'nuptk',
            'password',
        ];
    }

    /**
     * Contoh data baris untuk memudahkan pengguna usia 35+.
     */
    public function array(): array
    {
        return [
            [
                'Ahmad Dahlan, S.Pd.',
                'ahmad.dahlan',
                'ahmad@sekolah.sch.id',
                '198501012010011001',
                '1234567890123456',
                'PasswordGuru123!',
            ],
            [
                'Siti Rahmawati, M.Pd.',
                'siti.rahmawati',
                'siti@sekolah.sch.id',
                '-',
                '',
                'PasswordGuru123!',
            ],
        ];
    }

    /**
     * Nama sheet harus 'Data Guru'.
     */
    public function title(): string
    {
        return 'Data Guru';
    }

    /**
     * Styling header agar jelas dan terbaca.
     */
    public function styles(Worksheet $sheet): array
    {
        return [
            1 => [
                'font' => [
                    'bold' => true,
                    'color' => ['rgb' => '253C6D'], // navy-900
                ],
            ],
        ];
    }
}
