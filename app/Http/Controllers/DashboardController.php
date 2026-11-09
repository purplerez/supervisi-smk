<?php

namespace App\Http\Controllers;

use App\Models\Penilaian;
use App\Models\Penugasan;
use App\Models\Periode;
use App\Models\Sekolah;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class DashboardController extends Controller
{
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

        $totalGuruAktif = User::where('aktif', true)
            ->whereHas('roles', fn ($q) => $q->where('role', 'guru'))
            ->count();

        $totalSupervisorAktif = User::where('aktif', true)
            ->whereHas('roles', fn ($q) => $q->where('role', 'supervisor'))
            ->count();

        $totalPenugasan = 0;
        $totalGuruBelum = 0;
        $totalSelesai = 0;
        $totalSedangBerjalan = 0;
        $persenKetuntasan = 0;
        $persenPenugasan = 0;
        $rekapPenilaian = [
            'final' => 0,
            'draft' => 0,
            'direvisi' => 0,
            'belum' => 0,
            'total' => 0,
        ];

        if ($periode) {
            $semuaPenugasan = Penugasan::where('periode_id', $periode->id)
                ->with('penilaian')
                ->get();

            $totalPenugasan = $semuaPenugasan->count();

            // Hitung guru aktif yang belum ditugaskan tanpa memuat daftar model
            $totalGuruBelum = User::where('aktif', true)
                ->whereHas('roles', fn ($q) => $q->where('role', 'guru'))
                ->whereDoesntHave('penugasanSebagaiGuru', fn ($q) => $q->where('periode_id', $periode->id))
                ->count();

            if ($totalGuruAktif > 0) {
                $persenPenugasan = min(100, (int) round(($totalPenugasan / $totalGuruAktif) * 100));
            }

            // Selesai = penugasan yang seluruh baris penilaiannya sudah final
            $totalSelesai = $semuaPenugasan->filter(function ($p) {
                return $p->penilaian->isNotEmpty() && $p->penilaian->where('status', 'final')->count() === $p->penilaian->count();
            })->count();

            $totalSedangBerjalan = $totalPenugasan - $totalSelesai;

            if ($totalPenugasan > 0) {
                $persenKetuntasan = (int) round(($totalSelesai / $totalPenugasan) * 100);
            }

            // Rekap status seluruh penilaian pada periode ini
            $penilaianSemua = Penilaian::whereHas('penugasan', fn ($q) => $q->where('periode_id', $periode->id))->get();
            $rekapPenilaian = [
                'final' => $penilaianSemua->where('status', 'final')->count(),
                'draft' => $penilaianSemua->where('status', 'draft')->count(),
                'direvisi' => $penilaianSemua->where('status', 'direvisi')->count(),
                'belum' => $penilaianSemua->where('status', 'belum')->count(),
                'total' => $penilaianSemua->count(),
            ];
        }

        return view('dashboard.admin', [
            'sekolah' => $sekolah,
            'user' => Auth::user(),
            'periode' => $periode,
            'semuaPeriode' => $semuaPeriode,
            'totalGuruAktif' => $totalGuruAktif,
            'totalSupervisorAktif' => $totalSupervisorAktif,
            'totalPenugasan' => $totalPenugasan,
            'totalGuruBelum' => $totalGuruBelum,
            'totalSelesai' => $totalSelesai,
            'totalSedangBerjalan' => $totalSedangBerjalan,
            'persenKetuntasan' => $persenKetuntasan,
            'persenPenugasan' => $persenPenugasan,
            'rekapPenilaian' => $rekapPenilaian,
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
