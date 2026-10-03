<?php

namespace App\Http\Controllers\Admin;

use App\Exports\GuruTemplateExport;
use App\Http\Controllers\Controller;
use App\Models\ImportBatch;
use App\Models\Sekolah;
use App\Services\GuruImportService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\View\View;
use Maatwebsite\Excel\Facades\Excel;
use Symfony\Component\HttpFoundation\BinaryFileResponse;

class GuruImportController extends Controller
{
    public function __construct(
        private readonly GuruImportService $importService,
    ) {}

    /**
     * Halaman impor guru (3 langkah: unggah, pratinjau/dry-run, konfirmasi) serta riwayat impor.
     */
    public function index(Request $request, string $kode): View
    {
        $sekolah = Sekolah::where('kode', $kode)->firstOrFail();

        $tempPath = session('import_temp_file');
        $originalName = session('import_original_name');
        $pratinjau = null;

        // Jika ada file unggahan sementara di session, lakukan dry run validasi
        if ($tempPath && Storage::disk('private')->exists($tempPath)) {
            $pratinjau = $this->importService->validasiDanPratinjau($tempPath, $sekolah->id);
        }

        // Ambil riwayat batch impor
        $riwayat = ImportBatch::where('sekolah_id', $sekolah->id)
            ->with('user')
            ->latest()
            ->paginate(10);

        return view('admin.guru.import', compact(
            'sekolah',
            'riwayat',
            'pratinjau',
            'tempPath',
            'originalName',
        ));
    }

    /**
     * Unduh template file Excel untuk impor data guru.
     */
    public function downloadTemplate(Request $request, string $kode): BinaryFileResponse
    {
        $filename = 'Template-Data-Guru.xlsx';

        return Excel::download(
            new GuruTemplateExport,
            $filename,
            \Maatwebsite\Excel\Excel::XLSX,
            [
                'Content-Type' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
                'Content-Disposition' => 'attachment; filename="'.$filename.'"',
            ]
        );
    }

    /**
     * Langkah 1: Unggah file Excel dan simpan ke storage privat per sekolah.
     */
    public function upload(Request $request, string $kode): RedirectResponse
    {
        $request->validate([
            'file' => ['required', 'file', 'mimes:xlsx,xls,csv', 'max:5120'],
        ], [
            'file.required' => 'Silakan pilih file Excel terlebih dahulu.',
            'file.mimes' => 'Format file harus berupa Excel (.xlsx, .xls) atau CSV (.csv).',
            'file.max' => 'Ukuran file maksimal adalah 5 MB.',
        ]);

        $sekolah = Sekolah::where('kode', $kode)->firstOrFail();
        $file = $request->file('file');

        // Simpan file sementara di storage privat di folder per sekolah
        $tempPath = $file->store("imports/{$sekolah->id}", 'private');

        session([
            'import_temp_file' => $tempPath,
            'import_original_name' => $file->getClientOriginalName(),
        ]);

        return redirect()
            ->route('admin.guru.import', ['kode' => $kode])
            ->with('sukses', 'File berhasil diunggah. Silakan periksa hasil validasi dan pratinjau di bawah ini sebelum konfirmasi.');
    }

    /**
     * Langkah 3: Konfirmasi dan jalankan upsert ke database.
     */
    public function confirm(Request $request, string $kode): RedirectResponse
    {
        $tempPath = session('import_temp_file');
        $originalName = session('import_original_name');

        if (! $tempPath || ! Storage::disk('private')->exists($tempPath)) {
            return redirect()
                ->route('admin.guru.import', ['kode' => $kode])
                ->with('galat', 'Tidak ada file impor sementara yang ditemukan. Silakan unggah file kembali.');
        }

        $sekolah = Sekolah::where('kode', $kode)->firstOrFail();

        $batch = $this->importService->prosesImpor(
            $tempPath,
            $sekolah->id,
            auth()->id(),
            $originalName ?? 'impor_guru.xlsx'
        );

        session()->forget(['import_temp_file', 'import_original_name']);

        $pesan = "Impor selesai: {$batch->sukses} akun berhasil diproses";
        if ($batch->gagal > 0) {
            $pesan .= ", {$batch->gagal} baris gagal/dilewati.";
        } else {
            $pesan .= ' tanpa kendala.';
        }

        return redirect()
            ->route('admin.guru.import', ['kode' => $kode])
            ->with('sukses', $pesan);
    }

    /**
     * Batalkan proses impor (hapus file sementara dari storage privat).
     */
    public function cancel(Request $request, string $kode): RedirectResponse
    {
        $tempPath = session('import_temp_file');

        if ($tempPath && Storage::disk('private')->exists($tempPath)) {
            Storage::disk('private')->delete($tempPath);
        }

        session()->forget(['import_temp_file', 'import_original_name']);

        return redirect()
            ->route('admin.guru.import', ['kode' => $kode])
            ->with('sukses', 'File unggahan sementara berhasil dibatalkan dan dihapus.');
    }
}
