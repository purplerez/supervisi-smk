<?php

namespace App\Exports;

use App\Exports\Concerns\HasLaporanSupervisiMapping;
use App\Models\Penugasan;
use App\Models\Periode;
use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\WithTitle;

class RekapPeriodeExport implements FromCollection, ShouldAutoSize, WithHeadings, WithMapping, WithStyles, WithTitle
{
    use HasLaporanSupervisiMapping;

    public function __construct(
        public readonly Periode $periode,
        public readonly ?int $penilaiId = null,
        public readonly ?string $statusFilter = null
    ) {}

    /**
     * Ambil data penugasan menggunakan Eloquent Model agar global scope tenant berlaku.
     */
    public function collection(): Collection
    {
        $query = Penugasan::where('periode_id', $this->periode->id)
            ->with([
                'guru',
                'penilai',
                'penilaian.jenisInstrumen',
            ]);

        // Filter penilai jika diunduh oleh supervisor tertentu
        if ($this->penilaiId) {
            $query->where('penilai_id', $this->penilaiId);
        }

        $items = $query->get()->sortBy('guru.nama');

        // Filter status selesai / belum selesai bila dipilih
        if ($this->statusFilter === 'selesai') {
            $items = $items->filter(function ($item) {
                return $item->penilaian->where('status', 'final')->count() === 4;
            });
        } elseif ($this->statusFilter === 'belum_selesai') {
            $items = $items->filter(function ($item) {
                return $item->penilaian->where('status', 'final')->count() < 4;
            });
        }

        return $items->values();
    }

    /**
     * Nama sheet Excel (maksimal 31 karakter).
     */
    public function title(): string
    {
        $nama = 'Rekap '.$this->periode->nama;

        return mb_substr(preg_replace('/[\\/*?:\[\]]/', '', $nama), 0, 30);
    }
}
