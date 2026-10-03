<?php

namespace App\Services;

use App\Models\JenisInstrumen;
use App\Models\Penilaian;
use App\Models\Penugasan;
use App\Models\Periode;
use App\Models\User;
use DomainException;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Validation\ValidationException;

class PenugasanService
{
    /**
     * Dapatkan daftar penugasan pada periode tertentu dengan filter supervisor dan pencarian nama guru.
     */
    public function daftarPenugasan(Periode $periode, ?int $penilaiId = null, ?string $cari = null, int $perPage = 20): LengthAwarePaginator
    {
        return Penugasan::query()
            ->where('periode_id', $periode->id)
            ->when($penilaiId, fn ($q) => $q->where('penilai_id', $penilaiId))
            ->when($cari, function ($q, $cari) {
                $q->whereHas('guru', function ($sub) use ($cari) {
                    $sub->where('nama', 'like', "%{$cari}%")
                        ->orWhere('nip', 'like', "%{$cari}%")
                        ->orWhere('username', 'like', "%{$cari}%");
                });
            })
            ->with(['guru', 'penilai', 'penilaian.jenisInstrumen'])
            ->orderBy('id', 'desc')
            ->paginate($perPage)
            ->withQueryString();
    }

    /**
     * Buat penugasan baru untuk beberapa guru ke satu supervisor.
     * Otomatis membuat 4 baris penilaian berstatus 'belum' untuk setiap penugasan.
     * Semua berjalan dalam satu transaksi database.
     *
     * @param  array<int>  $guruIds
     * @return array{penugasan: array<Penugasan>, peringatan: array<string>}
     */
    public function buatPenugasan(Periode $periode, int $penilaiId, array $guruIds): array
    {
        if ($periode->isDitutup()) {
            throw new DomainException('Tidak dapat menambah penugasan pada periode yang telah ditutup.');
        }

        return $periode->getConnection()->transaction(function () use ($periode, $penilaiId, $guruIds) {
            $penilai = User::where('id', $penilaiId)
                ->where('sekolah_id', $periode->sekolah_id)
                ->firstOrFail();

            if (! $penilai->aktif) {
                throw ValidationException::withMessages([
                    'penilai_id' => "Supervisor \"{$penilai->nama}\" berstatus nonaktif dan tidak dapat diberi penugasan.",
                ]);
            }

            if (! $penilai->hasRole('supervisor')) {
                throw ValidationException::withMessages([
                    'penilai_id' => "Pengguna \"{$penilai->nama}\" tidak memiliki peran Supervisor.",
                ]);
            }

            $peringatan = [];
            $penugasanDibuat = [];

            // 4 kode jenis instrumen wajib supervisi sesuai DESAIN 3.3 & 3.4
            $kodeInstrumen = ['kbm', 'administrasi', 'pengelolaan_kelas', 'perencanaan'];

            // Siapkan pemetaan JenisInstrumen (sekolah atau global fallback)
            $mapJenis = [];
            foreach ($kodeInstrumen as $kode) {
                $jenis = JenisInstrumen::milikSekolah()->where('kode', $kode)->first()
                    ?: JenisInstrumen::globalTemplate()->where('kode', $kode)->first();

                if ($jenis) {
                    $mapJenis[$kode] = $jenis;
                }
            }

            foreach ($guruIds as $guruId) {
                $guruId = (int) $guruId;

                // Validasi: penilai tidak boleh sama dengan guru
                if ($guruId === $penilaiId) {
                    throw ValidationException::withMessages([
                        'guru_ids' => "Penilai ({$penilai->nama}) tidak boleh ditugaskan untuk menilai dirinya sendiri.",
                    ]);
                }

                $guru = User::where('id', $guruId)
                    ->where('sekolah_id', $periode->sekolah_id)
                    ->firstOrFail();

                if (! $guru->aktif) {
                    throw ValidationException::withMessages([
                        'guru_ids' => "Guru \"{$guru->nama}\" berstatus nonaktif dan tidak dapat ditugaskan.",
                    ]);
                }

                if (! $guru->hasRole('guru')) {
                    throw ValidationException::withMessages([
                        'guru_ids' => "Pengguna \"{$guru->nama}\" tidak memiliki peran Guru.",
                    ]);
                }

                // Cek keunikan penugasan per periode + guru
                $sudahDitugaskan = Penugasan::where('periode_id', $periode->id)
                    ->where('guru_id', $guruId)
                    ->exists();

                if ($sudahDitugaskan) {
                    throw ValidationException::withMessages([
                        'guru_ids' => "Guru \"{$guru->nama}\" sudah memiliki penilai pada periode ini.",
                    ]);
                }

                // Cek saling menilai (A menilai B dan B menilai A pada periode yang sama)
                $salingMenilai = Penugasan::where('periode_id', $periode->id)
                    ->where('guru_id', $penilaiId)
                    ->where('penilai_id', $guruId)
                    ->exists();

                if ($salingMenilai) {
                    $peringatan[] = "Perhatian: Terjadi saling menilai antara Bapak/Ibu {$penilai->nama} dan {$guru->nama} pada periode ini.";
                }

                // 1. Buat Baris Penugasan
                $penugasan = Penugasan::create([
                    'sekolah_id' => $periode->sekolah_id,
                    'periode_id' => $periode->id,
                    'guru_id' => $guruId,
                    'penilai_id' => $penilaiId,
                ]);

                // 2. Buat Otomatis 4 Baris Penilaian berstatus 'belum'
                foreach ($mapJenis as $jenis) {
                    Penilaian::create([
                        'sekolah_id' => $periode->sekolah_id,
                        'penugasan_id' => $penugasan->id,
                        'jenis_instrumen_id' => $jenis->id,
                        'versi_instrumen_id' => null, // null sampai mulai dinilai sesuai DESAIN 3.4
                        'status' => 'belum',
                        'jumlah_buka_kunci' => 0,
                    ]);
                }

                $penugasanDibuat[] = $penugasan;
            }

            return [
                'penugasan' => $penugasanDibuat,
                'peringatan' => $peringatan,
            ];
        });
    }

    /**
     * Ganti penilai pada penugasan.
     * Hanya boleh jika keempat penilaian masih 'belum'.
     *
     * @return array{sukses: bool, peringatan?: string|null}
     */
    public function gantiPenilai(Penugasan $penugasan, int $penilaiBaruId): array
    {
        if ($penugasan->periode->isDitutup()) {
            throw new DomainException('Tidak dapat mengganti penilai pada periode yang telah ditutup.');
        }

        if (! $penugasan->bisaDiubahAtauDihapus()) {
            throw new DomainException('Penilai tidak dapat diganti karena penilaian supervisi guru ini sudah dimulai (status bukan lagi "belum").');
        }

        if ($penugasan->guru_id === $penilaiBaruId) {
            throw ValidationException::withMessages([
                'penilai_id' => 'Penilai tidak boleh sama dengan guru yang disupervisi.',
            ]);
        }

        return $penugasan->getConnection()->transaction(function () use ($penugasan, $penilaiBaruId) {
            $penilaiBaru = User::where('id', $penilaiBaruId)
                ->where('sekolah_id', $penugasan->sekolah_id)
                ->firstOrFail();

            if (! $penilaiBaru->aktif) {
                throw ValidationException::withMessages([
                    'penilai_id' => "Supervisor \"{$penilaiBaru->nama}\" berstatus nonaktif.",
                ]);
            }

            if (! $penilaiBaru->hasRole('supervisor')) {
                throw ValidationException::withMessages([
                    'penilai_id' => "Pengguna \"{$penilaiBaru->nama}\" tidak memiliki peran Supervisor.",
                ]);
            }

            // Cek saling menilai
            $salingMenilai = Penugasan::where('periode_id', $penugasan->periode_id)
                ->where('guru_id', $penilaiBaruId)
                ->where('penilai_id', $penugasan->guru_id)
                ->exists();

            $peringatan = $salingMenilai
                ? "Perhatian: Terjadi saling menilai antara Bapak/Ibu {$penilaiBaru->nama} dan {$penugasan->guru->nama} pada periode ini."
                : null;

            $penugasan->update([
                'penilai_id' => $penilaiBaruId,
            ]);

            return [
                'sukses' => true,
                'peringatan' => $peringatan,
            ];
        });
    }

    /**
     * Hapus penugasan supervisi.
     * Hanya boleh jika keempat penilaian masih 'belum'.
     */
    public function hapusPenugasan(Penugasan $penugasan): void
    {
        if ($penugasan->periode->isDitutup()) {
            throw new DomainException('Tidak dapat menghapus penugasan pada periode yang telah ditutup.');
        }

        if (! $penugasan->bisaDiubahAtauDihapus()) {
            throw new DomainException('Penugasan tidak dapat dihapus karena penilaian supervisi guru ini sudah dimulai atau diselesaikan.');
        }

        $penugasan->delete();
    }

    /**
     * Rekap penugasan per supervisor pada periode tertentu.
     *
     * @return Collection<int, User>
     */
    public function rekapPerSupervisor(Periode $periode): Collection
    {
        return User::query()
            ->where('sekolah_id', $periode->sekolah_id)
            ->where('aktif', true)
            ->whereHas('roles', fn ($q) => $q->where('role', 'supervisor'))
            ->withCount(['penugasanSebagaiPenilai' => fn ($q) => $q->where('periode_id', $periode->id)])
            ->with(['penugasanSebagaiPenilai' => function ($q) use ($periode) {
                $q->where('periode_id', $periode->id)->with('guru');
            }])
            ->orderBy('nama', 'asc')
            ->get();
    }
}
