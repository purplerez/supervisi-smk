<?php

namespace App\Exports;

use App\Exports\Sheets\RekapPraObservasiSheet;
use App\Exports\Sheets\RekapRefleksiSheet;
use App\Exports\Sheets\RekapRencanaPengembanganSheet;
use App\Models\Periode;
use Maatwebsite\Excel\Concerns\Export;
use Maatwebsite\Excel\Concerns\WithMultipleSheets;

class RekapKualitatifExport implements Export, WithMultipleSheets
{
    public function __construct(
        public readonly Periode $periode,
        public readonly ?int $penilaiId = null,
        public readonly ?string $kategori = 'semua'
    ) {}

    /**
     * Daftarkan lembar kerja spreadsheet berdasarkan kategori instrumen yang dipilih.
     *
     * @return array<int, object>
     */
    public function sheets(): array
    {
        $sheets = [];

        if ($this->kategori === 'pra_observasi' || $this->kategori === 'semua' || empty($this->kategori)) {
            $sheets[] = new RekapPraObservasiSheet($this->periode, $this->penilaiId);
        }

        if ($this->kategori === 'refleksi_pasca_observasi' || $this->kategori === 'semua' || empty($this->kategori)) {
            $sheets[] = new RekapRefleksiSheet($this->periode, $this->penilaiId);
        }

        if ($this->kategori === 'rencana_pengembangan_diri' || $this->kategori === 'semua' || empty($this->kategori)) {
            $sheets[] = new RekapRencanaPengembanganSheet($this->periode, $this->penilaiId);
        }

        return $sheets;
    }
}
