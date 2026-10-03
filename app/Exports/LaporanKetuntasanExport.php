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

class LaporanKetuntasanExport implements FromCollection, ShouldAutoSize, WithHeadings, WithMapping, WithStyles, WithTitle
{
    use HasLaporanSupervisiMapping;

    public function __construct(
        public readonly Periode $periode,
        public readonly ?int $penilaiId = null,
        public readonly ?string $statusFilter = null
    ) {}

    /**
     * Ambil data penugasan menggunakan Eloquent Model agar global scope tenant berlaku.
     * Mengurutkan berdasarkan ketuntasan (selesai 4/4 terlebih dahulu) lalu nama guru.
     */
    public function collection(): Collection
    {
        $query = Penugasan::where('periode_id', $this->periode->id)
            ->with([
                'guru',
                'penilai',
                'penilaian.jenisInstrumen',
            ]);

        if ($this->penilaiId) {
            $query->where('penilai_id', $this->penilaiId);
        }

        $items = $query->get();

        if ($this->statusFilter === 'selesai') {
            $items = $items->filter(fn ($item) => $item->penilaian->where('status', 'final')->count() === 4);
        } elseif ($this->statusFilter === 'belum_selesai') {
            $items = $items->filter(fn ($item) => $item->penilaian->where('status', 'final')->count() < 4);
        }

        // Urutkan: jumlah final terbanyak (descending), lalu nama guru (ascending)
        return $items->sort(function ($a, $b) {
            $finalA = $a->penilaian->where('status', 'final')->count();
            $finalB = $b->penilaian->where('status', 'final')->count();

            if ($finalA === $finalB) {
                return strcmp($a->guru->nama ?? '', $b->guru->nama ?? '');
            }

            return $finalB <=> $finalA;
        })->values();
    }

    /**
     * Nama sheet Excel (maksimal 31 karakter).
     */
    public function title(): string
    {
        $nama = 'Ketuntasan '.$this->periode->nama;

        return mb_substr(preg_replace('/[\\/*?:\[\]]/', '', $nama), 0, 30);
    }
}
