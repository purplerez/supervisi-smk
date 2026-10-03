<?php

namespace App\Http\Controllers\Supervisor;

use App\Http\Controllers\Controller;
use App\Models\Penilaian;
use App\Models\Penugasan;
use App\Models\Periode;
use App\Models\Sekolah;
use App\Services\PenilaianService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class SupervisorDashboardController extends Controller
{
    public function __construct(
        private readonly PenilaianService $penilaianService,
    ) {}

    /**
     * Dasbor Supervisor: Daftar guru yang ditugaskan pada periode aktif dengan progres n/4.
     */
    public function index(Request $request, string $kode): View
    {
        $sekolah = Sekolah::where('kode', $kode)->firstOrFail();
        $user = Auth::user();

        // Periode aktif saat ini
        $periode = Periode::where('status', 'aktif')->first();

        $daftarPenugasan = collect();
        $penugasanSebagaiGuru = null;
        if ($periode) {
            $daftarPenugasan = Penugasan::where('periode_id', $periode->id)
                ->where('penilai_id', $user->id)
                ->with(['guru', 'penilaian.jenisInstrumen', 'infoSupervisi', 'jadwalSupervisi'])
                ->get();

            $penugasanSebagaiGuru = Penugasan::where('periode_id', $periode->id)
                ->where('guru_id', $user->id)
                ->with(['penilai', 'penilaian.jenisInstrumen', 'infoSupervisi', 'jadwalSupervisi'])
                ->first();
        }

        return view('supervisor.dashboard', compact('sekolah', 'user', 'periode', 'daftarPenugasan', 'penugasanSebagaiGuru'));
    }

    /**
     * Detail Guru: Informasi supervisi & jadwal (hanya baca) dan 4 kartu/tombol instrumen.
     */
    public function showPenugasan(Request $request, string $kode, Penugasan $penugasan): View
    {
        $sekolah = Sekolah::where('kode', $kode)->firstOrFail();
        $user = Auth::user();

        // Supervisor hanya dapat mengakses penugasan di mana ia penilainya
        if ((int) $penugasan->penilai_id !== (int) $user->id) {
            abort(403, 'Anda tidak memiliki hak akses terhadap penugasan supervisi ini.');
        }

        $penugasan->load([
            'guru',
            'periode',
            'infoSupervisi',
            'jadwalSupervisi.jenisInstrumen',
            'penilaian.jenisInstrumen',
        ]);

        return view('supervisor.penugasan-detail', compact('sekolah', 'user', 'penugasan'));
    }

    /**
     * Formulir Penilaian: Komponen interaktif penilaian instrumen.
     */
    public function showPenilaian(Request $request, string $kode, Penilaian $penilaian): View
    {
        $sekolah = Sekolah::where('kode', $kode)->firstOrFail();
        $user = Auth::user();

        // Guru tidak bisa mengakses form penilaian; Admin tidak menilai; Hanya supervisor penilai
        if (! $penilaian->penugasan || (int) $penilaian->penugasan->penilai_id !== (int) $user->id) {
            abort(403, 'Hanya supervisor penilai yang dapat mengakses lembar penilaian ini.');
        }

        // Jika status masih belum, inisialisasi ke status draft dan kunci versi aktif
        if ($penilaian->isBelum() && ! $penilaian->penugasan->periode->isDitutup()) {
            $penilaian = $this->penilaianService->inisialisasiDraft($penilaian, $user);
        }

        $penilaian->load([
            'penugasan.guru',
            'penugasan.periode',
            'jenisInstrumen',
            'versiInstrumen.bagianInstrumen.butirInstrumen',
            'penilaianButir',
        ]);

        return view('supervisor.penilaian', compact('sekolah', 'user', 'penilaian'));
    }
}
