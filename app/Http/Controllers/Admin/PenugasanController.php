<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\StorePenugasanRequest;
use App\Http\Requests\Admin\UpdatePenugasanPenilaiRequest;
use App\Models\Penilaian;
use App\Models\Penugasan;
use App\Models\Periode;
use App\Models\Sekolah;
use App\Models\User;
use App\Services\PenilaianService;
use App\Services\PenugasanService;
use App\Services\PeriodeService;
use DomainException;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

class PenugasanController extends Controller
{
    public function __construct(
        private readonly PenugasanService $penugasanService,
        private readonly PeriodeService $periodeService,
        private readonly PenilaianService $penilaianService,
    ) {}

    /**
     * Halaman manajemen penugasan supervisi.
     */
    public function index(Request $request, string $kode): View
    {
        $sekolah = Sekolah::where('kode', $kode)->firstOrFail();

        $semuaPeriode = Periode::orderByDesc('tanggal_mulai')->get();

        $periodeId = $request->query('periode_id');
        $periode = null;

        if ($periodeId) {
            $periode = Periode::find($periodeId);
        }

        if (! $periode) {
            // Default: periode yang sedang aktif, atau periode terbaru
            $periode = Periode::where('status', 'aktif')->first() ?: $semuaPeriode->first();
        }

        $daftarPenugasan = null;
        $supervisors = collect();
        $guruBelum = collect();
        $rekapSupervisor = collect();

        if ($periode) {
            $penilaiFilter = $request->query('penilai_id') ? (int) $request->query('penilai_id') : null;
            $cari = $request->query('cari');

            $daftarPenugasan = $this->penugasanService->daftarPenugasan($periode, $penilaiFilter, $cari);
            $guruBelum = $this->periodeService->guruBelumPunyaPenilai($periode);
            $rekapSupervisor = $this->penugasanService->rekapPerSupervisor($periode);

            $supervisors = User::where('aktif', true)
                ->whereHas('roles', fn ($q) => $q->where('role', 'supervisor'))
                ->orderBy('nama', 'asc')
                ->get();
        }

        return view('admin.penugasan.index', compact(
            'sekolah',
            'semuaPeriode',
            'periode',
            'daftarPenugasan',
            'supervisors',
            'guruBelum',
            'rekapSupervisor',
        ));
    }

    /**
     * Simpan penugasan baru untuk beberapa guru ke satu penilai.
     */
    public function store(StorePenugasanRequest $request, string $kode): RedirectResponse
    {
        $sekolah = Sekolah::where('kode', $kode)->firstOrFail();
        $periode = Periode::where('id', $request->validated('periode_id'))
            ->where('sekolah_id', $sekolah->id)
            ->firstOrFail();

        try {
            $hasil = $this->penugasanService->buatPenugasan(
                $periode,
                (int) $request->validated('penilai_id'),
                $request->validated('guru_ids')
            );
        } catch (ValidationException $e) {
            return back()->withInput()->with('galat', $e->getMessage());
        } catch (DomainException $e) {
            return back()->withInput()->with('galat', $e->getMessage());
        }

        $jumlah = count($hasil['penugasan']);
        $pesanSukses = "Berhasil membuat penugasan untuk {$jumlah} guru beserta 4 baris penilaian supervisi otomatis.";

        $redirect = redirect()->route('admin.penugasan.index', [
            'kode' => $sekolah->kode,
            'periode_id' => $periode->id,
        ])->with('sukses', $pesanSukses);

        // Jika terjadi saling menilai, tampilkan peringatan
        if (! empty($hasil['peringatan'])) {
            $redirect->with('peringatan', implode(' ', $hasil['peringatan']));
        }

        return $redirect;
    }

    /**
     * Ganti supervisor penilai pada penugasan yang penilaiannya masih 'belum'.
     */
    public function updatePenilai(UpdatePenugasanPenilaiRequest $request, string $kode, Penugasan $penugasan): RedirectResponse
    {
        $sekolah = Sekolah::where('kode', $kode)->firstOrFail();

        try {
            $hasil = $this->penugasanService->gantiPenilai(
                $penugasan,
                (int) $request->validated('penilai_id')
            );
        } catch (ValidationException $e) {
            return back()->with('galat', $e->getMessage());
        } catch (DomainException $e) {
            return back()->with('galat', $e->getMessage());
        }

        $redirect = redirect()->route('admin.penugasan.index', [
            'kode' => $sekolah->kode,
            'periode_id' => $penugasan->periode_id,
        ])->with('sukses', "Penilai untuk guru {$penugasan->guru->nama} berhasil diperbarui.");

        if (! empty($hasil['peringatan'])) {
            $redirect->with('peringatan', $hasil['peringatan']);
        }

        return $redirect;
    }

    /**
     * Hapus penugasan (hanya bila 4 penilaian masih berstatus belum).
     */
    public function destroy(Request $request, string $kode, Penugasan $penugasan): RedirectResponse
    {
        $sekolah = Sekolah::where('kode', $kode)->firstOrFail();
        $periodeId = $penugasan->periode_id;

        try {
            $this->penugasanService->hapusPenugasan($penugasan);
        } catch (DomainException $e) {
            return back()->with('galat', $e->getMessage());
        }

        return redirect()->route('admin.penugasan.index', [
            'kode' => $sekolah->kode,
            'periode_id' => $periodeId,
        ])->with('sukses', 'Penugasan guru berhasil dibatalkan dan dihapus.');
    }

    /**
     * Halaman daftar guru yang belum memiliki penilai pada periode terpilih.
     */
    public function belumPunyaPenilai(Request $request, string $kode): View
    {
        $sekolah = Sekolah::where('kode', $kode)->firstOrFail();
        $semuaPeriode = Periode::orderByDesc('tanggal_mulai')->get();

        $periodeId = $request->query('periode_id');
        $periode = null;

        if ($periodeId) {
            $periode = Periode::find($periodeId);
        }

        if (! $periode) {
            $periode = Periode::where('status', 'aktif')->first() ?: $semuaPeriode->first();
        }

        $guruBelum = collect();
        $supervisors = collect();

        if ($periode) {
            $guruBelum = $this->periodeService->guruBelumPunyaPenilai($periode);
            $supervisors = User::where('aktif', true)
                ->whereHas('roles', fn ($q) => $q->where('role', 'supervisor'))
                ->orderBy('nama', 'asc')
                ->get();
        }

        return view('admin.penugasan.belum-dinilai', compact(
            'sekolah',
            'semuaPeriode',
            'periode',
            'guruBelum',
            'supervisors'
        ));
    }

    /**
     * Buka kunci penilaian final oleh Admin dengan alasan wajib.
     */
    public function bukaKunciPenilaian(Request $request, string $kode, Penilaian $penilaian): RedirectResponse
    {
        $this->authorize('bukaKunci', $penilaian);

        $request->validate([
            'alasan' => ['required', 'string', 'min:5', 'max:500'],
        ], [
            'alasan.required' => 'Alasan buka kunci wajib diisi.',
            'alasan.min' => 'Alasan buka kunci minimal 5 karakter.',
            'alasan.max' => 'Alasan buka kunci maksimal 500 karakter.',
        ]);

        try {
            $this->penilaianService->bukaKunci(
                $penilaian,
                $request->user(),
                $request->input('alasan'),
                $request->ip()
            );
        } catch (DomainException $e) {
            return back()->with('galat', $e->getMessage());
        }

        return back()->with('sukses', "Penilaian {$penilaian->jenisInstrumen->nama} berhasil dibuka kuncinya untuk perbaikan supervisor.");
    }
}
