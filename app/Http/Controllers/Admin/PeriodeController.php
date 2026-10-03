<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\StorePeriodeRequest;
use App\Http\Requests\Admin\UpdatePeriodeRequest;
use App\Models\Periode;
use App\Models\Sekolah;
use App\Services\PeriodeService;
use DomainException;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

class PeriodeController extends Controller
{
    public function __construct(
        private readonly PeriodeService $periodeService,
    ) {}

    /**
     * Tampilkan daftar periode supervisi sekolah.
     */
    public function index(Request $request, string $kode): View
    {
        $sekolah = Sekolah::where('kode', $kode)->firstOrFail();
        $cari = $request->query('cari');

        $daftarPeriode = $this->periodeService->daftarPeriode($cari);

        return view('admin.periode.index', compact('sekolah', 'daftarPeriode', 'cari'));
    }

    /**
     * Simpan periode baru.
     */
    public function store(StorePeriodeRequest $request, string $kode): RedirectResponse
    {
        $sekolah = Sekolah::where('kode', $kode)->firstOrFail();

        $periode = $this->periodeService->tambahPeriode($request->validated());

        return redirect()
            ->route('admin.periode.index', ['kode' => $sekolah->kode])
            ->with('sukses', "Periode \"{$periode->nama}\" berhasil dibuat sebagai Draf.");
    }

    /**
     * Perbarui data periode.
     */
    public function update(UpdatePeriodeRequest $request, string $kode, Periode $periode): RedirectResponse
    {
        $sekolah = Sekolah::where('kode', $kode)->firstOrFail();

        try {
            $this->periodeService->perbaruiPeriode($periode, $request->validated());
        } catch (DomainException $e) {
            return back()->with('galat', $e->getMessage());
        }

        return redirect()
            ->route('admin.periode.index', ['kode' => $sekolah->kode])
            ->with('sukses', 'Data periode supervisi berhasil diperbarui.');
    }

    /**
     * Hapus periode (hanya draf tanpa penugasan).
     */
    public function destroy(Request $request, string $kode, Periode $periode): RedirectResponse
    {
        $sekolah = Sekolah::where('kode', $kode)->firstOrFail();

        try {
            $this->periodeService->hapusPeriode($periode);
        } catch (DomainException $e) {
            return back()->with('galat', $e->getMessage());
        }

        return redirect()
            ->route('admin.periode.index', ['kode' => $sekolah->kode])
            ->with('sukses', 'Periode supervisi berhasil dihapus.');
    }

    /**
     * Aktifkan periode supervisi (maksimal 1 periode aktif per sekolah).
     */
    public function aktifkan(Request $request, string $kode, Periode $periode): RedirectResponse
    {
        $sekolah = Sekolah::where('kode', $kode)->firstOrFail();
        $konfirmasiLanjut = $request->boolean('konfirmasi_lanjut');

        try {
            $hasil = $this->periodeService->aktifkanPeriode($periode, $konfirmasiLanjut);
        } catch (ValidationException $e) {
            return back()->with('galat', $e->getMessage());
        } catch (DomainException $e) {
            return back()->with('galat', $e->getMessage());
        }

        if (! empty($hasil['konfirmasi_diperlukan'])) {
            // Simpan daftar guru yang belum punya penilai ke session flash untuk konfirmasi
            $daftarNama = $hasil['guru_belum']->pluck('nama')->take(5)->implode(', ');
            $sisa = $hasil['guru_belum']->count() - 5;
            $teksGuru = $sisa > 0 ? "{$daftarNama}, dan {$sisa} guru lainnya" : $daftarNama;

            return back()->with([
                'konfirmasi_aktifkan_id' => $periode->id,
                'konfirmasi_pesan' => "Terdapat {$hasil['guru_belum']->count()} guru aktif yang belum memiliki penilai ({$teksGuru}). Apakah Anda yakin ingin tetap mengaktifkan periode ini sekarang?",
            ]);
        }

        return redirect()
            ->route('admin.periode.index', ['kode' => $sekolah->kode])
            ->with('sukses', "Periode \"{$periode->nama}\" berhasil diaktifkan.");
    }

    /**
     * Tutup periode supervisi. Mengunci seluruh penilaian.
     */
    public function tutup(Request $request, string $kode, Periode $periode): RedirectResponse
    {
        $sekolah = Sekolah::where('kode', $kode)->firstOrFail();

        try {
            $this->periodeService->tutupPeriode($periode);
        } catch (DomainException $e) {
            return back()->with('galat', $e->getMessage());
        }

        return redirect()
            ->route('admin.periode.index', ['kode' => $sekolah->kode])
            ->with('sukses', "Periode \"{$periode->nama}\" telah ditutup. Seluruh penilaian supervisi pada periode ini kini terkunci.");
    }
}
