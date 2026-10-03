<?php

namespace App\Services;

use App\Models\Periode;
use App\Models\User;
use DomainException;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Validation\ValidationException;

class PeriodeService
{
    /**
     * Dapatkan daftar periode supervisi sekolah.
     */
    public function daftarPeriode(?string $cari = null, int $perPage = 15): LengthAwarePaginator
    {
        return Periode::query()
            ->when($cari, function ($q, $cari) {
                $q->where(function ($sub) use ($cari) {
                    $sub->where('nama', 'like', "%{$cari}%")
                        ->orWhere('tahun_ajaran', 'like', "%{$cari}%");
                });
            })
            ->withCount('penugasan')
            ->orderByRaw("CASE WHEN status = 'aktif' THEN 1 WHEN status = 'draft' THEN 2 ELSE 3 END")
            ->orderByDesc('tanggal_mulai')
            ->paginate($perPage)
            ->withQueryString();
    }

    /**
     * Tambah periode baru (status awal selalu draft).
     */
    public function tambahPeriode(array $data): Periode
    {
        return Periode::create([
            'nama' => $data['nama'],
            'tahun_ajaran' => $data['tahun_ajaran'],
            'semester' => $data['semester'] ?? null,
            'tanggal_mulai' => $data['tanggal_mulai'],
            'tanggal_selesai' => $data['tanggal_selesai'],
            'status' => 'draft',
        ]);
    }

    /**
     * Perbarui data periode.
     */
    public function perbaruiPeriode(Periode $periode, array $data): Periode
    {
        if ($periode->isDitutup()) {
            throw new DomainException('Periode yang telah ditutup tidak dapat diubah datanya.');
        }

        $periode->update([
            'nama' => $data['nama'],
            'tahun_ajaran' => $data['tahun_ajaran'],
            'semester' => $data['semester'] ?? null,
            'tanggal_mulai' => $data['tanggal_mulai'],
            'tanggal_selesai' => $data['tanggal_selesai'],
        ]);

        return $periode->fresh();
    }

    /**
     * Hapus periode (hanya boleh bila status draft dan belum ada penugasan).
     */
    public function hapusPeriode(Periode $periode): void
    {
        if (! $periode->isDraft()) {
            throw new DomainException('Hanya periode berstatus Draf yang dapat dihapus.');
        }

        if ($periode->penugasan()->exists()) {
            throw new DomainException('Periode tidak dapat dihapus karena sudah memiliki data penugasan.');
        }

        $periode->delete();
    }

    /**
     * Aktifkan periode supervisi.
     * Hanya SATU periode aktif per sekolah. Validasi dilakukan di dalam transaksi database.
     *
     * @return array{sukses: bool, konfirmasi_diperlukan?: bool, guru_belum?: Collection}
     */
    public function aktifkanPeriode(Periode $periode, bool $konfirmasiLanjut = false): array
    {
        if ($periode->isDitutup()) {
            throw new DomainException('Periode yang telah ditutup tidak dapat diaktifkan kembali.');
        }

        // Lakukan validasi dan pembaruan dalam transaksi database
        return $periode->getConnection()->transaction(function () use ($periode, $konfirmasiLanjut) {
            // Cek apakah ada periode lain yang sedang aktif di sekolah ini
            $periodeAktifLain = Periode::where('status', 'aktif')
                ->where('id', '!=', $periode->id)
                ->first();

            if ($periodeAktifLain) {
                throw ValidationException::withMessages([
                    'status' => "Hanya satu periode yang dapat aktif dalam satu waktu. Periode \"{$periodeAktifLain->nama}\" saat ini masih aktif. Silakan tutup periode tersebut terlebih dahulu.",
                ]);
            }

            // Cek apakah ada guru aktif yang belum punya penilai
            $guruBelum = $this->guruBelumPunyaPenilai($periode);

            if ($guruBelum->isNotEmpty() && ! $konfirmasiLanjut) {
                return [
                    'sukses' => false,
                    'konfirmasi_diperlukan' => true,
                    'guru_belum' => $guruBelum,
                ];
            }

            $periode->update(['status' => 'aktif']);

            return [
                'sukses' => true,
                'konfirmasi_diperlukan' => false,
            ];
        });
    }

    /**
     * Tutup periode supervisi. Mengunci seluruh penilaian supervisi.
     */
    public function tutupPeriode(Periode $periode): void
    {
        if ($periode->isDraft()) {
            throw new DomainException('Periode berstatus Draf tidak dapat langsung ditutup. Aktifkan terlebih dahulu.');
        }

        $periode->update(['status' => 'ditutup']);
    }

    /**
     * Dapatkan daftar pengguna aktif bersekolah ini yang berperan guru tetapi belum memiliki penilai pada periode ini.
     *
     * @return Collection<int, User>
     */
    public function guruBelumPunyaPenilai(Periode $periode): Collection
    {
        return User::query()
            ->where('sekolah_id', $periode->sekolah_id)
            ->where('aktif', true)
            ->whereHas('roles', fn ($q) => $q->where('role', 'guru'))
            ->whereDoesntHave('penugasanSebagaiGuru', fn ($q) => $q->where('periode_id', $periode->id))
            ->orderBy('nama', 'asc')
            ->get();
    }
}
