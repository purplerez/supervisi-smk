<?php

namespace App\Http\Controllers;

use App\Models\Penugasan;
use App\Models\Periode;
use App\Models\Sekolah;
use App\Models\User;
use App\Services\PeriodeService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class DashboardController extends Controller
{
    public function __construct(
        private readonly PeriodeService $periodeService,
    ) {}

    /**
     * Halaman Dasbor Admin Sekolah.
     */
    public function admin(Request $request, string $kode): View
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

        $supervisors = User::where('aktif', true)
            ->whereHas('roles', fn ($q) => $q->where('role', 'supervisor'))
            ->orderBy('nama', 'asc')
            ->get();

        $guruBelum = collect();
        $penugasanTerbaru = collect();
        $totalPenugasan = 0;

        if ($periode) {
            $guruBelum = $this->periodeService->guruBelumPunyaPenilai($periode);
            $totalPenugasan = Penugasan::where('periode_id', $periode->id)->count();
            $penugasanTerbaru = Penugasan::where('periode_id', $periode->id)
                ->with(['guru', 'penilai'])
                ->orderByDesc('id')
                ->limit(5)
                ->get();
        }

        $totalGuruAktif = User::where('aktif', true)
            ->whereHas('roles', fn ($q) => $q->where('role', 'guru'))
            ->count();

        $totalSupervisorAktif = $supervisors->count();

        return view('dashboard.admin', [
            'sekolah' => $sekolah,
            'user' => Auth::user(),
            'periode' => $periode,
            'semuaPeriode' => $semuaPeriode,
            'supervisors' => $supervisors,
            'guruBelum' => $guruBelum,
            'totalPenugasan' => $totalPenugasan,
            'totalGuruAktif' => $totalGuruAktif,
            'totalSupervisorAktif' => $totalSupervisorAktif,
            'penugasanTerbaru' => $penugasanTerbaru,
        ]);
    }

    /**
     * Halaman Dasbor Supervisor / Penilai.
     */
    public function supervisor(string $kode): View
    {
        $sekolah = Sekolah::where('kode', $kode)->firstOrFail();

        return view('dashboard.supervisor', [
            'sekolah' => $sekolah,
            'user' => Auth::user(),
        ]);
    }

    /**
     * Halaman Dasbor Guru.
     */
    public function guru(string $kode): View
    {
        $sekolah = Sekolah::where('kode', $kode)->firstOrFail();

        return view('dashboard.guru', [
            'sekolah' => $sekolah,
            'user' => Auth::user(),
        ]);
    }
}
