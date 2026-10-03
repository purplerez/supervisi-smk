<?php

namespace App\Services;

use App\Models\Penugasan;
use App\Models\Periode;
use App\Models\User;
use DomainException;
use Illuminate\Database\Eloquent\Collection;

class GuruSupervisiService
{
    /**
     * Ambil penugasan guru pada periode aktif di sekolah saat ini.
     */
    public function ambilPenugasanPeriodeAktif(User $guru): ?Penugasan
    {
        $periodeAktif = Periode::where('status', 'aktif')->first();

        if (! $periodeAktif) {
            return null;
        }

        return $this->ambilPenugasanPeriode($guru, $periodeAktif);
    }

    /**
     * Ambil penugasan guru pada periode tertentu dengan eager loading lengkap.
     */
    public function ambilPenugasanPeriode(User $guru, Periode $periode): ?Penugasan
    {
        return Penugasan::where('periode_id', $periode->id)
            ->where('guru_id', $guru->id)
            ->with([
                'periode',
                'penilai',
                'infoSupervisi',
                'jadwalSupervisi.jenisInstrumen',
                'penilaian' => function ($query) {
                    $query->with('jenisInstrumen');
                },
            ])
            ->first();
    }

    /**
     * Ambil riwayat penugasan guru di seluruh periode yang pernah diikutinya.
     *
     * @return Collection<int, Penugasan>
     */
    public function ambilRiwayatPenugasan(User $guru): Collection
    {
        return Penugasan::where('guru_id', $guru->id)
            ->with([
                'periode',
                'penilai',
                'penilaian',
            ])
            ->join('periode', 'penugasan.periode_id', '=', 'periode.id')
            ->orderByDesc('periode.tanggal_mulai')
            ->select('penugasan.*')
            ->get();
    }

    /**
     * Simpan informasi kelas/mata pelajaran dan jadwal supervisi guru.
     *
     * @param  array{
     *     kelas: string,
     *     semester?: ?string,
     *     fase?: ?string,
     *     mata_pelajaran: string,
     *     elemen?: ?string,
     *     cp?: ?string,
     *     catatan?: ?string
     * }  $infoData
     * @param  array<int, array{
     *     tanggal?: ?string,
     *     jam_mulai?: ?string,
     *     jam_selesai?: ?string
     * }>  $jadwalData
     */
    public function simpanInfoDanJadwal(
        Penugasan $penugasan,
        array $infoData,
        array $jadwalData,
        User $pengguna
    ): void {
        // Jika pengguna yang menyimpan adalah guru subjek, pastikan form belum terkunci
        if ($pengguna->id === $penugasan->guru_id && ! $penugasan->guruBisaUbahInfoDanJadwal()) {
            throw new DomainException('Informasi dan jadwal supervisi telah dikunci karena proses supervisi telah dimulai oleh supervisor Anda.');
        }

        $penugasan->getConnection()->transaction(function () use ($penugasan, $infoData, $jadwalData) {
            // 1. Simpan atau perbarui info supervisi
            $penugasan->infoSupervisi()->updateOrCreate(
                ['penugasan_id' => $penugasan->id],
                [
                    'sekolah_id' => $penugasan->sekolah_id,
                    'kelas' => trim($infoData['kelas']),
                    'semester' => isset($infoData['semester']) && filled($infoData['semester']) ? trim($infoData['semester']) : null,
                    'fase' => isset($infoData['fase']) && filled($infoData['fase']) ? trim($infoData['fase']) : null,
                    'mata_pelajaran' => trim($infoData['mata_pelajaran']),
                    'elemen' => isset($infoData['elemen']) && filled($infoData['elemen']) ? trim($infoData['elemen']) : null,
                    'cp' => isset($infoData['cp']) && filled($infoData['cp']) ? trim($infoData['cp']) : null,
                    'catatan' => isset($infoData['catatan']) && filled($infoData['catatan']) ? trim($infoData['catatan']) : null,
                ]
            );

            // 2. Simpan jadwal supervisi per instrumen
            foreach ($jadwalData as $jenisInstrumenId => $jadwalItem) {
                $tanggal = $jadwalItem['tanggal'] ?? null;
                $jamMulai = $jadwalItem['jam_mulai'] ?? null;
                $jamSelesai = $jadwalItem['jam_selesai'] ?? null;

                if (filled($tanggal) && filled($jamMulai) && filled($jamSelesai)) {
                    $penugasan->jadwalSupervisi()->updateOrCreate(
                        [
                            'penugasan_id' => $penugasan->id,
                            'jenis_instrumen_id' => $jenisInstrumenId,
                        ],
                        [
                            'sekolah_id' => $penugasan->sekolah_id,
                            'tanggal' => $tanggal,
                            'jam_mulai' => $jamMulai,
                            'jam_selesai' => $jamSelesai,
                        ]
                    );
                } else {
                    // Jika dikosongkan, hapus jadwal instrumen tersebut jika ada
                    $penugasan->jadwalSupervisi()
                        ->where('jenis_instrumen_id', $jenisInstrumenId)
                        ->delete();
                }
            }
        });
    }

    /**
     * Hitung ringkasan progres penyelesaian supervisi.
     *
     * @return array{
     *     selesai_count: int,
     *     total_count: int,
     *     is_selesai: bool,
     *     label: string,
     *     persentase: int
     * }
     */
    public function hitungProgresPenugasan(Penugasan $penugasan): array
    {
        $penilaian = $penugasan->penilaian;
        $total = $penilaian->count();
        $selesai = $penilaian->where('status', 'final')->count();

        $isSelesai = ($total > 0 && $selesai === $total);
        $label = $isSelesai ? "Selesai ({$selesai}/{$total})" : "Belum selesai ({$selesai}/{$total})";
        $persentase = $total > 0 ? (int) round(($selesai / $total) * 100) : 0;

        return [
            'selesai_count' => $selesai,
            'total_count' => $total,
            'is_selesai' => $isSelesai,
            'label' => $label,
            'persentase' => $persentase,
        ];
    }
}
