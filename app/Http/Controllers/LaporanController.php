<?php

namespace App\Http\Controllers;

use App\Exports\LaporanKetuntasanExport;
use App\Exports\RekapKualitatifExport;
use App\Exports\RekapPeriodeExport;
use App\Models\Penilaian;
use App\Models\Penugasan;
use App\Models\Periode;
use App\Models\Sekolah;
use App\Models\User;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Str;
use Illuminate\View\View;
use Maatwebsite\Excel\Facades\Excel;
use Symfony\Component\HttpFoundation\BinaryFileResponse;

class LaporanController extends Controller
{
    /**
     * Halaman Rekap & Laporan Supervisi (Admin).
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
            $periode = Periode::where('status', 'aktif')->first() ?: $semuaPeriode->first();
        }

        $daftarPenugasan = collect();
        $supervisors = collect();
        $totalSelesai = 0;
        $totalBelumSelesai = 0;
        $persenKetuntasan = 0;

        if ($periode) {
            $query = Penugasan::where('periode_id', $periode->id)
                ->with([
                    'guru',
                    'penilai',
                    'penilaian.jenisInstrumen',
                    'infoSupervisi',
                ]);

            if ($request->filled('penilai_id')) {
                $query->where('penilai_id', (int) $request->query('penilai_id'));
            }

            if ($request->filled('cari')) {
                $cari = trim($request->query('cari'));
                $query->whereHas('guru', function ($q) use ($cari) {
                    $q->where('nama', 'like', "%{$cari}%")
                        ->orWhere('nip', 'like', "%{$cari}%")
                        ->orWhere('username', 'like', "%{$cari}%");
                });
            }

            $rawCollection = $query->get()->sortBy('guru.nama');

            // Hitung statistik seluruh penugasan pada periode ini
            $semuaPenugasanPeriode = Penugasan::where('periode_id', $periode->id)
                ->with('penilaian')
                ->get();

            $totalSelesai = $semuaPenugasanPeriode->filter(fn ($p) => $p->penilaian->isNotEmpty() && $p->penilaian->where('status', 'final')->count() === $p->penilaian->count())->count();
            $totalBelumSelesai = $semuaPenugasanPeriode->count() - $totalSelesai;
            $persenKetuntasan = $semuaPenugasanPeriode->count() > 0 ? round(($totalSelesai / $semuaPenugasanPeriode->count()) * 100) : 0;

            // Filter status: selesai (seluruh instrumen final) atau belum selesai
            $statusFilter = $request->query('status');
            if ($statusFilter === 'selesai') {
                $daftarPenugasan = $rawCollection->filter(fn ($p) => $p->penilaian->isNotEmpty() && $p->penilaian->where('status', 'final')->count() === $p->penilaian->count());
            } elseif ($statusFilter === 'belum_selesai') {
                $daftarPenugasan = $rawCollection->filter(fn ($p) => $p->penilaian->isEmpty() || $p->penilaian->where('status', 'final')->count() < $p->penilaian->count());
            } else {
                $daftarPenugasan = $rawCollection;
            }

            $supervisors = User::where('aktif', true)
                ->whereHas('roles', fn ($q) => $q->where('role', 'supervisor'))
                ->orderBy('nama', 'asc')
                ->get();
        }

        return view('admin.laporan.index', compact(
            'sekolah',
            'semuaPeriode',
            'periode',
            'daftarPenugasan',
            'supervisors',
            'totalSelesai',
            'totalBelumSelesai',
            'persenKetuntasan'
        ));
    }

    /**
     * Unduh Excel Rekap Periode Supervisi (Admin).
     */
    public function exportRekap(Request $request, string $kode): BinaryFileResponse
    {
        $sekolah = Sekolah::where('kode', $kode)->firstOrFail();

        $periodeId = $request->query('periode_id');
        $periode = $periodeId ? Periode::find($periodeId) : Periode::where('status', 'aktif')->first();

        if (! $periode) {
            abort(404, 'Periode supervisi tidak ditemukan.');
        }

        $penilaiId = $request->filled('penilai_id') ? (int) $request->query('penilai_id') : null;
        $statusFilter = $request->query('status');

        $safePeriode = str_replace(['/', '\\', ' '], '-', $periode->nama);
        $namaFile = "Rekap-Supervisi-{$safePeriode}.xlsx";

        return Excel::download(
            new RekapPeriodeExport($periode, $penilaiId, $statusFilter),
            $namaFile,
            \Maatwebsite\Excel\Excel::XLSX,
            [
                'Content-Type' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
                'Content-Disposition' => 'attachment; filename="'.$namaFile.'"',
            ]
        );
    }

    /**
     * Unduh Excel Laporan Ketuntasan Supervisi (Admin).
     */
    public function exportKetuntasan(Request $request, string $kode): BinaryFileResponse
    {
        $sekolah = Sekolah::where('kode', $kode)->firstOrFail();

        $periodeId = $request->query('periode_id');
        $periode = $periodeId ? Periode::find($periodeId) : Periode::where('status', 'aktif')->first();

        if (! $periode) {
            abort(404, 'Periode supervisi tidak ditemukan.');
        }

        $penilaiId = $request->filled('penilai_id') ? (int) $request->query('penilai_id') : null;
        $statusFilter = $request->query('status');

        $safePeriode = str_replace(['/', '\\', ' '], '-', $periode->nama);
        $namaFile = "Laporan-Ketuntasan-Supervisi-{$safePeriode}.xlsx";

        return Excel::download(
            new LaporanKetuntasanExport($periode, $penilaiId, $statusFilter),
            $namaFile,
            \Maatwebsite\Excel\Excel::XLSX,
            [
                'Content-Type' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
                'Content-Disposition' => 'attachment; filename="'.$namaFile.'"',
            ]
        );
    }

    /**
     * Unduh Excel Rekap Bimbingan Supervisor.
     * Supervisor hanya dapat mengunduh data guru binaannya.
     */
    public function exportRekapSupervisor(Request $request, string $kode): BinaryFileResponse|RedirectResponse
    {
        $sekolah = Sekolah::where('kode', $kode)->firstOrFail();
        $user = $request->user();

        if ($request->filled('periode_id')) {
            $periode = Periode::find($request->query('periode_id'));
            if (! $periode) {
                abort(404, 'Periode supervisi tidak ditemukan pada sekolah ini.');
            }
        } else {
            $periode = Periode::where('status', 'aktif')->first();
            if (! $periode) {
                return redirect()
                    ->route('dashboard.supervisor', ['kode' => $kode])
                    ->with('peringatan', 'Saat ini belum ada periode supervisi yang berstatus aktif di sekolah Anda.');
            }
        }

        $statusFilter = $request->query('status');
        $safeUser = str_replace(['/', '\\', ' '], '-', $user->username ?? Str::slug($user->nama ?? 'supervisor'));
        $safePeriode = str_replace(['/', '\\', ' '], '-', $periode->nama);
        $namaFile = "Progres-Bimbingan-{$safeUser}-{$safePeriode}.xlsx";

        return Excel::download(
            new RekapPeriodeExport($periode, $user->id, $statusFilter),
            $namaFile,
            \Maatwebsite\Excel\Excel::XLSX,
            [
                'Content-Type' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
                'Content-Disposition' => 'attachment; filename="'.$namaFile.'"',
            ]
        );
    }

    /**
     * Unduh Excel Laporan Ketuntasan Bimbingan Supervisor.
     */
    public function exportKetuntasanSupervisor(Request $request, string $kode): BinaryFileResponse|RedirectResponse
    {
        $sekolah = Sekolah::where('kode', $kode)->firstOrFail();
        $user = $request->user();

        if ($request->filled('periode_id')) {
            $periode = Periode::find($request->query('periode_id'));
            if (! $periode) {
                abort(404, 'Periode supervisi tidak ditemukan pada sekolah ini.');
            }
        } else {
            $periode = Periode::where('status', 'aktif')->first();
            if (! $periode) {
                return redirect()
                    ->route('dashboard.supervisor', ['kode' => $kode])
                    ->with('peringatan', 'Saat ini belum ada periode supervisi yang berstatus aktif di sekolah Anda.');
            }
        }

        $statusFilter = $request->query('status');
        $safeUser = str_replace(['/', '\\', ' '], '-', $user->username ?? Str::slug($user->nama ?? 'supervisor'));
        $safePeriode = str_replace(['/', '\\', ' '], '-', $periode->nama);
        $namaFile = "Ketuntasan-Bimbingan-{$safeUser}-{$safePeriode}.xlsx";

        return Excel::download(
            new LaporanKetuntasanExport($periode, $user->id, $statusFilter),
            $namaFile,
            \Maatwebsite\Excel\Excel::XLSX,
            [
                'Content-Type' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
                'Content-Disposition' => 'attachment; filename="'.$namaFile.'"',
            ]
        );
    }

    /**
     * Cetak Lembar Hasil Penilaian Supervisi ke Format PDF (A4).
     *
     * Aturan:
     * - Hanya untuk penilaian yang berstatus final.
     * - Hak akses: Admin sekolah, Supervisor penilai bersangkutan, atau Guru yang dinilai bersangkutan.
     */
    public function cetakPenilaianPdf(Request $request, string $kode, Penilaian $penilaian): Response
    {
        $sekolah = Sekolah::where('kode', $kode)->firstOrFail();

        if ((int) $penilaian->sekolah_id !== (int) $sekolah->id) {
            abort(404, 'Dokumen penilaian tidak ditemukan pada sekolah ini.');
        }

        $user = $request->user();

        // Pemeriksaan Hak Akses
        $isAdmin = $user->hasRole('admin');
        $isPenilai = $user->hasRole('supervisor') && (int) $penilaian->penugasan?->penilai_id === (int) $user->id;
        $isGuru = $user->hasRole('guru') && (int) $penilaian->penugasan?->guru_id === (int) $user->id;

        if (! ($isAdmin || $isPenilai || $isGuru)) {
            abort(403, 'Anda tidak memiliki hak akses untuk mengunduh dokumen penilaian ini.');
        }

        // Hanya penilaian berstatus final yang dapat dicetak
        if (! $penilaian->isFinal()) {
            abort(403, 'Hasil penilaian hanya dapat dicetak setelah penilaian berstatus final.');
        }

        $penilaian->loadMissing([
            'sekolah',
            'penugasan.guru',
            'penugasan.penilai',
            'penugasan.periode',
            'penugasan.infoSupervisi',
            'jenisInstrumen',
            'versiInstrumen.bagian.butir',
            'penilaianButir',
        ]);

        $pdf = Pdf::loadView('pdf.penilaian', compact('penilaian'))
            ->setPaper('a4', 'portrait');

        $namaFile = sprintf(
            'Penilaian-%s-%s.pdf',
            $penilaian->jenisInstrumen->kode ?? 'supervisi',
            $penilaian->penugasan->guru->username ?? 'guru'
        );

        return $pdf->download($namaFile);
    }

    /**
     * Cetak Lembar Hasil Penilaian Semua Instrumen ke Format PDF (A4).
     *
     * Aturan:
     * - Hak akses: Admin sekolah, Supervisor penilai bersangkutan, atau Guru yang dinilai bersangkutan.
     */
    public function cetakSemuaPenilaianPdf(Request $request, string $kode, Penugasan $penugasan): Response
    {
        $sekolah = Sekolah::where('kode', $kode)->with('kepalaSekolah')->firstOrFail();

        if ((int) $penugasan->sekolah_id !== (int) $sekolah->id) {
            abort(404, 'Penugasan tidak ditemukan pada sekolah ini.');
        }

        $user = $request->user();

        // Pemeriksaan Hak Akses
        $isAdmin = $user->hasRole('admin');
        $isPenilai = $user->hasRole('supervisor') && (int) $penugasan->penilai_id === (int) $user->id;
        $isGuru = $user->hasRole('guru') && (int) $penugasan->guru_id === (int) $user->id;

        if (! ($isAdmin || $isPenilai || $isGuru)) {
            abort(403, 'Anda tidak memiliki hak akses untuk mengunduh berkas laporan supervisi ini.');
        }

        $penugasan->loadMissing([
            'sekolah.kepalaSekolah',
            'guru',
            'penilai',
            'periode',
            'infoSupervisi',
            'jadwalSupervisi.jenisInstrumen',
            'penilaian' => function ($q) {
                $q->with([
                    'sekolah.kepalaSekolah',
                    'penugasan.guru',
                    'penugasan.penilai',
                    'penugasan.periode',
                    'penugasan.infoSupervisi',
                    'penugasan.jadwalSupervisi.jenisInstrumen',
                    'jenisInstrumen',
                    'versiInstrumen.bagian.butir',
                    'penilaianButir',
                ]);
            },
        ]);

        $pdf = Pdf::loadView('pdf.penilaian-semua', compact('penugasan'))
            ->setPaper('a4', 'portrait');

        $namaFile = sprintf(
            'Laporan-Supervisi-Lengkap-%s.pdf',
            $penugasan->guru->username ?? Str::slug($penugasan->guru->nama ?? 'guru')
        );

        return $pdf->download($namaFile);
    }

    /**
     * Unduh Excel Rekap Nilai & Data Instrumen Kualitatif (Supervisor).
     * Supervisor hanya dapat mengunduh data guru binaannya.
     */
    public function exportKualitatifSupervisorExcel(Request $request, string $kode): BinaryFileResponse|RedirectResponse
    {
        $sekolah = Sekolah::where('kode', $kode)->firstOrFail();
        $user = $request->user();

        if ($request->filled('periode_id')) {
            $periode = Periode::find($request->query('periode_id'));
            if (! $periode) {
                abort(404, 'Periode supervisi tidak ditemukan pada sekolah ini.');
            }
        } else {
            $periode = Periode::where('status', 'aktif')->first();
            if (! $periode) {
                return redirect()
                    ->route('dashboard.supervisor', ['kode' => $kode])
                    ->with('peringatan', 'Saat ini belum ada periode supervisi yang berstatus aktif di sekolah Anda.');
            }
        }

        $kategori = $request->query('kategori', 'semua');
        $safeUser = str_replace(['/', '\\', ' '], '-', $user->username ?? Str::slug($user->nama ?? 'supervisor'));
        $safePeriode = str_replace(['/', '\\', ' '], '-', $periode->nama);
        $namaFile = "Rekap-Kualitatif-{$kategori}-{$safeUser}-{$safePeriode}.xlsx";

        return Excel::download(
            new RekapKualitatifExport($periode, $user->id, $kategori),
            $namaFile,
            \Maatwebsite\Excel\Excel::XLSX,
            [
                'Content-Type' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
                'Content-Disposition' => 'attachment; filename="'.$namaFile.'"',
            ]
        );
    }

    /**
     * Unduh PDF Rekap Nilai & Data Instrumen Kualitatif (Supervisor).
     * Supervisor hanya dapat mengunduh data guru binaannya.
     */
    public function exportKualitatifSupervisorPdf(Request $request, string $kode): Response|RedirectResponse
    {
        $sekolah = Sekolah::where('kode', $kode)->with('kepalaSekolah')->firstOrFail();
        $user = $request->user();

        if ($request->filled('periode_id')) {
            $periode = Periode::find($request->query('periode_id'));
            if (! $periode) {
                abort(404, 'Periode supervisi tidak ditemukan pada sekolah ini.');
            }
        } else {
            $periode = Periode::where('status', 'aktif')->first();
            if (! $periode) {
                return redirect()
                    ->route('dashboard.supervisor', ['kode' => $kode])
                    ->with('peringatan', 'Saat ini belum ada periode supervisi yang berstatus aktif di sekolah Anda.');
            }
        }

        $kategori = $request->query('kategori', 'semua');

        $query = Penugasan::where('periode_id', $periode->id)
            ->where('penilai_id', $user->id)
            ->with([
                'guru',
                'penilai',
                'infoSupervisi',
                'jadwalSupervisi.jenisInstrumen',
                'penilaian' => function ($q) {
                    $q->with(['penilaianButir', 'versiInstrumen.bagian.butir', 'jenisInstrumen']);
                },
            ]);

        $daftarPenugasan = $query->get()->sortBy('guru.nama')->values();
        $supervisor = $user;

        $judulKategori = match ($kategori) {
            'pra_observasi' => 'Percakapan Pra-Observasi',
            'refleksi_pasca_observasi' => 'Refleksi Pasca-Observasi',
            'rencana_pengembangan_diri' => 'Rencana Pengembangan Diri',
            default => 'Semua Kategori Kualitatif',
        };

        $pdf = Pdf::loadView('pdf.rekap-kualitatif', compact('sekolah', 'periode', 'daftarPenugasan', 'supervisor', 'kategori', 'judulKategori'))
            ->setPaper('a4', 'landscape');

        $safeUser = str_replace(['/', '\\', ' '], '-', $user->username ?? Str::slug($user->nama ?? 'supervisor'));
        $safePeriode = str_replace(['/', '\\', ' '], '-', $periode->nama);
        $namaFile = "Rekap-Kualitatif-{$kategori}-{$safeUser}-{$safePeriode}.pdf";

        return $pdf->download($namaFile);
    }

    /**
     * Unduh Excel Rekap Nilai & Data Instrumen Kualitatif (Admin).
     */
    public function exportKualitatifAdminExcel(Request $request, string $kode): BinaryFileResponse
    {
        $sekolah = Sekolah::where('kode', $kode)->firstOrFail();

        $periodeId = $request->query('periode_id');
        $periode = $periodeId ? Periode::find($periodeId) : Periode::where('status', 'aktif')->first();

        if (! $periode) {
            abort(404, 'Periode supervisi tidak ditemukan.');
        }

        $penilaiId = $request->filled('penilai_id') ? (int) $request->query('penilai_id') : null;
        $kategori = $request->query('kategori', 'semua');

        $safePeriode = str_replace(['/', '\\', ' '], '-', $periode->nama);
        $namaFile = "Rekap-Kualitatif-{$kategori}-{$safePeriode}.xlsx";

        return Excel::download(
            new RekapKualitatifExport($periode, $penilaiId, $kategori),
            $namaFile,
            \Maatwebsite\Excel\Excel::XLSX,
            [
                'Content-Type' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
                'Content-Disposition' => 'attachment; filename="'.$namaFile.'"',
            ]
        );
    }

    /**
     * Unduh PDF Rekap Nilai & Data Instrumen Kualitatif (Admin).
     */
    public function exportKualitatifAdminPdf(Request $request, string $kode): Response
    {
        $sekolah = Sekolah::where('kode', $kode)->with('kepalaSekolah')->firstOrFail();

        $periodeId = $request->query('periode_id');
        $periode = $periodeId ? Periode::find($periodeId) : Periode::where('status', 'aktif')->first();

        if (! $periode) {
            abort(404, 'Periode supervisi tidak ditemukan.');
        }

        $penilaiId = $request->filled('penilai_id') ? (int) $request->query('penilai_id') : null;
        $kategori = $request->query('kategori', 'semua');

        $query = Penugasan::where('periode_id', $periode->id)
            ->with([
                'guru',
                'penilai',
                'infoSupervisi',
                'jadwalSupervisi.jenisInstrumen',
                'penilaian' => function ($q) {
                    $q->with(['penilaianButir', 'versiInstrumen.bagian.butir', 'jenisInstrumen']);
                },
            ]);

        if ($penilaiId) {
            $query->where('penilai_id', $penilaiId);
        }

        $daftarPenugasan = $query->get()->sortBy('guru.nama')->values();
        $supervisor = $penilaiId ? User::find($penilaiId) : null;

        $judulKategori = match ($kategori) {
            'pra_observasi' => 'Percakapan Pra-Observasi',
            'refleksi_pasca_observasi' => 'Refleksi Pasca-Observasi',
            'rencana_pengembangan_diri' => 'Rencana Pengembangan Diri',
            default => 'Semua Kategori Kualitatif',
        };

        $pdf = Pdf::loadView('pdf.rekap-kualitatif', compact('sekolah', 'periode', 'daftarPenugasan', 'supervisor', 'kategori', 'judulKategori'))
            ->setPaper('a4', 'landscape');

        $safePeriode = str_replace(['/', '\\', ' '], '-', $periode->nama);
        $namaFile = "Rekap-Kualitatif-{$kategori}-{$safePeriode}.pdf";

        return $pdf->download($namaFile);
    }
}
