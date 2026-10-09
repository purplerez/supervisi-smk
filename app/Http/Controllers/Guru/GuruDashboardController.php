<?php

namespace App\Http\Controllers\Guru;

use App\Http\Controllers\Controller;
use App\Http\Requests\Guru\SimpanInfoJadwalRequest;
use App\Models\JenisInstrumen;
use App\Models\Periode;
use App\Models\Sekolah;
use App\Services\GuruSupervisiService;
use DomainException;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

class GuruDashboardController extends Controller
{
    public function __construct(
        private readonly GuruSupervisiService $guruSupervisiService,
    ) {}

    /**
     * Halaman Dasbor Utama Guru: Timeline Supervisi Periode Berjalan.
     */
    public function index(Request $request, string $kode): View
    {
        $sekolah = Sekolah::where('kode', $kode)->firstOrFail();
        $guru = $request->user();

        $periodeAktif = Periode::where('status', 'aktif')->first();
        $penugasan = null;
        $progres = null;
        $canEdit = false;

        if ($periodeAktif) {
            $penugasan = $this->guruSupervisiService->ambilPenugasanPeriodeAktif($guru);

            if ($penugasan) {
                $progres = $this->guruSupervisiService->hitungProgresPenugasan($penugasan);
                $canEdit = $penugasan->guruBisaUbahInfoDanJadwal();
            }
        }

        return view('guru.dashboard', [
            'sekolah' => $sekolah,
            'guru' => $guru,
            'periodeAktif' => $periodeAktif,
            'penugasan' => $penugasan,
            'progres' => $progres,
            'canEdit' => $canEdit,
        ]);
    }

    /**
     * Halaman Pengisian Informasi Supervisi, Pilihan Supervisor & Jadwal Observasi.
     */
    public function infoJadwal(Request $request, string $kode): View|RedirectResponse
    {
        $sekolah = Sekolah::where('kode', $kode)->firstOrFail();
        $guru = $request->user();

        $periodeAktif = Periode::where('status', 'aktif')->first();

        if (! $periodeAktif) {
            return redirect()
                ->route('guru.dashboard', ['kode' => $kode])
                ->with('peringatan', 'Saat ini belum ada periode supervisi yang aktif di sekolah Anda.');
        }

        $penugasan = $this->guruSupervisiService->ambilPenugasanPeriodeAktif($guru);
        $supervisors = collect();

        // Jika guru belum mengajukan supervisi: siapkan daftar supervisor yang dapat dipilih
        if (! $penugasan) {
            $supervisors = $this->guruSupervisiService->ambilSupervisorsTersedia($sekolah, $guru);
            $canEdit = true;

            $jenisInstrumenList = JenisInstrumen::milikSekolah()
                ->where('sekolah_id', $sekolah->id)
                ->orderBy('urutan')
                ->get();

            if ($jenisInstrumenList->isEmpty()) {
                $jenisInstrumenList = JenisInstrumen::globalTemplate()
                    ->orderBy('urutan')
                    ->get();
            }

            $jadwalMap = collect();

            return view('guru.info-jadwal', [
                'sekolah' => $sekolah,
                'guru' => $guru,
                'periode' => $periodeAktif,
                'penugasan' => null,
                'info' => null,
                'canEdit' => $canEdit,
                'supervisors' => $supervisors,
                'jenisInstrumenList' => $jenisInstrumenList,
                'jadwalMap' => $jadwalMap,
            ]);
        }

        $canEdit = $penugasan->guruBisaUbahInfoDanJadwal();

        // Ambil 4 jenis instrumen penugasan (tanpa duplikasi dengan template global)
        $jenisInstrumenList = $penugasan->penilaian
            ->map(fn ($p) => $p->jenisInstrumen)
            ->filter()
            ->unique('id')
            ->sortBy('urutan')
            ->values();

        if ($jenisInstrumenList->isEmpty()) {
            $jenisInstrumenList = JenisInstrumen::milikSekolah()
                ->where('sekolah_id', $sekolah->id)
                ->orderBy('urutan')
                ->get();

            if ($jenisInstrumenList->isEmpty()) {
                $jenisInstrumenList = JenisInstrumen::globalTemplate()
                    ->orderBy('urutan')
                    ->get();
            }
        }

        // Peta jadwal yang sudah tersimpan [jenis_instrumen_id => JadwalSupervisi]
        $jadwalMap = $penugasan->jadwalSupervisi->keyBy('jenis_instrumen_id');

        return view('guru.info-jadwal', [
            'sekolah' => $sekolah,
            'guru' => $guru,
            'periode' => $periodeAktif,
            'penugasan' => $penugasan,
            'info' => $penugasan->infoSupervisi,
            'canEdit' => $canEdit,
            'supervisors' => $supervisors,
            'jenisInstrumenList' => $jenisInstrumenList,
            'jadwalMap' => $jadwalMap,
        ]);
    }

    /**
     * Simpan pengajuan supervisi (baru) atau pembaruan formulir Informasi Supervisi dan Jadwal.
     */
    public function simpanInfoJadwal(SimpanInfoJadwalRequest $request, string $kode): RedirectResponse
    {
        $sekolah = Sekolah::where('kode', $kode)->firstOrFail();
        $guru = $request->user();
        $periodeAktif = Periode::where('status', 'aktif')->first();

        if (! $periodeAktif) {
            return redirect()
                ->route('guru.dashboard', ['kode' => $kode])
                ->with('galat', 'Tidak ada periode supervisi yang sedang aktif di sekolah Anda.');
        }

        $penugasan = $this->guruSupervisiService->ambilPenugasanPeriodeAktif($guru);

        try {
            $infoData = $request->only([
                'kelas',
                'semester',
                'fase',
                'mata_pelajaran',
                'elemen',
                'cp',
                'catatan',
            ]);

            $jadwalData = $request->input('jadwal', []);

            // Kasus 1: Pengajuan baru oleh guru mandiri
            if (! $penugasan) {
                $penilaiId = (int) $request->validated('penilai_id');

                $hasil = $this->guruSupervisiService->ajukanSupervisi(
                    $guru,
                    $periodeAktif,
                    $penilaiId,
                    $infoData,
                    $jadwalData
                );

                $redirect = redirect()
                    ->route('guru.dashboard', ['kode' => $kode])
                    ->with('sukses', 'Pengajuan supervisi berhasil diajukan. Penugasan Anda bersama supervisor telah aktif.');

                if (! empty($hasil['peringatan'])) {
                    $redirect->with('peringatan', implode(' ', $hasil['peringatan']));
                }

                return $redirect;
            }

            // Kasus 2: Pembaruan info/jadwal pada penugasan yang sudah ada
            $this->guruSupervisiService->simpanInfoDanJadwal(
                $penugasan,
                $infoData,
                $jadwalData,
                $guru
            );

            return redirect()
                ->route('guru.info-jadwal', ['kode' => $kode])
                ->with('sukses', 'Informasi supervisi dan jadwal berhasil disimpan.');
        } catch (ValidationException $e) {
            return back()->withInput()->withErrors($e->errors())->with('galat', $e->getMessage());
        } catch (DomainException $e) {
            return back()->withInput()->with('galat', $e->getMessage());
        }
    }

    /**
     * Halaman Rapor Hasil Supervisi Guru per Periode.
     */
    public function rapor(Request $request, string $kode, string|int|null $periode = null): View|RedirectResponse
    {
        $sekolah = Sekolah::where('kode', $kode)->with('kepalaSekolah')->firstOrFail();
        $guru = $request->user();

        // Jika periode ditentukan pada URL, cari periode tersebut (scoped ke tenant)
        if ($periode !== null) {
            $periodeModel = Periode::find($periode);
            if (! $periodeModel) {
                abort(404, 'Data periode supervisi tidak ditemukan.');
            }
            $periode = $periodeModel;
        } else {
            // Jika tidak ditentukan, cari periode aktif atau periode terbaru yang diikuti guru
            $penugasanTerbaru = $this->guruSupervisiService->ambilRiwayatPenugasan($guru)->first();
            $periode = $penugasanTerbaru?->periode;
        }

        if (! $periode) {
            return redirect()
                ->route('guru.dashboard', ['kode' => $kode])
                ->with('peringatan', 'Belum ada data supervisi atau rapor yang dapat ditampilkan.');
        }

        // Ambil penugasan guru di periode tersebut (wajib milik guru yang login)
        $penugasan = $this->guruSupervisiService->ambilPenugasanPeriode($guru, $periode);

        if (! $penugasan) {
            abort(404, 'Data penugasan supervisi Anda tidak ditemukan pada periode ini.');
        }

        $progres = $this->guruSupervisiService->hitungProgresPenugasan($penugasan);

        // Ambil daftar riwayat periode guru untuk navigasi tab antar periode
        $riwayatPenugasan = $this->guruSupervisiService->ambilRiwayatPenugasan($guru);

        return view('guru.rapor', [
            'sekolah' => $sekolah,
            'guru' => $guru,
            'periode' => $periode,
            'penugasan' => $penugasan,
            'progres' => $progres,
            'riwayatPenugasan' => $riwayatPenugasan,
            'kepalaSekolah' => $sekolah->kepalaSekolah,
        ]);
    }

    /**
     * Halaman Riwayat Periode Supervisi Guru.
     */
    public function riwayat(Request $request, string $kode): View
    {
        $sekolah = Sekolah::where('kode', $kode)->firstOrFail();
        $guru = $request->user();

        $riwayat = $this->guruSupervisiService->ambilRiwayatPenugasan($guru);

        return view('guru.riwayat', [
            'sekolah' => $sekolah,
            'guru' => $guru,
            'riwayat' => $riwayat,
        ]);
    }
}
