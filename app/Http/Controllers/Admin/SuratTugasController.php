<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\SimpanSuratTugasRequest;
use App\Models\Periode;
use App\Models\Sekolah;
use App\Models\SuratTugas;
use App\Models\User;
use App\Services\SuratTugasService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\View\View;
use Symfony\Component\HttpFoundation\BinaryFileResponse;

class SuratTugasController extends Controller
{
    public function __construct(
        private readonly SuratTugasService $suratTugasService,
    ) {}

    /**
     * Halaman daftar surat tugas per supervisor pada periode tertentu.
     */
    public function index(Request $request, string $kode): View
    {
        $sekolah = Sekolah::where('kode', $kode)->with('kepalaSekolah')->firstOrFail();

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

        $daftarPenilai = collect();

        if ($periode) {
            $daftarPenilai = $this->suratTugasService->daftarPenilaiDenganStatusSurat($periode);
        }

        return view('admin.surat-tugas.index', [
            'sekolah' => $sekolah,
            'semuaPeriode' => $semuaPeriode,
            'periode' => $periode,
            'daftarPenilai' => $daftarPenilai,
            'kepalaSekolah' => $sekolah->kepalaSekolah,
        ]);
    }

    /**
     * Terbitkan atau perbarui surat tugas untuk supervisor pada periode tertentu.
     */
    public function store(
        SimpanSuratTugasRequest $request,
        string $kode,
        Periode $periode,
        User $penilai
    ): RedirectResponse {
        $this->suratTugasService->simpanSuratTugas(
            $periode,
            $penilai->id,
            $request->validated()
        );

        return redirect()
            ->route('admin.surat-tugas.index', ['kode' => $kode, 'periode_id' => $periode->id])
            ->with('sukses', "Surat tugas untuk {$penilai->nama} berhasil diterbitkan.");
    }

    /**
     * Unduh dokumen DOCX surat tugas (dibuat secara dinamis di memori/temp file).
     */
    public function download(Request $request, string $kode, SuratTugas $suratTugas): BinaryFileResponse
    {
        // Pastikan penilai dan periode dimuat
        $suratTugas->loadMissing(['penilai', 'periode']);

        $tempFile = $this->suratTugasService->buatDokumenDocx($suratTugas);

        $sanitizedPenilai = Str::slug($suratTugas->penilai->nama ?? 'supervisor', '_');
        $sanitizedPeriode = Str::slug($suratTugas->periode->nama ?? 'periode', '_');
        $filename = "Surat_Tugas_{$sanitizedPenilai}_{$sanitizedPeriode}.docx";

        return response()->download($tempFile, $filename, [
            'Content-Type' => 'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
            'Content-Disposition' => 'attachment; filename="'.$filename.'"',
        ])->deleteFileAfterSend(true);
    }
}
