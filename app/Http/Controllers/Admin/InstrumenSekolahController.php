<?php

namespace App\Http\Controllers\Admin;

use App\Exceptions\ImmutableVersionException;
use App\Http\Controllers\Controller;
use App\Models\BagianInstrumen;
use App\Models\ButirInstrumen;
use App\Models\JenisInstrumen;
use App\Models\Sekolah;
use App\Models\VersiInstrumen;
use App\Services\InstrumenService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;
use InvalidArgumentException;

class InstrumenSekolahController extends Controller
{
    public function __construct(
        private readonly InstrumenService $instrumenService,
    ) {}

    /**
     * Halaman instrumen admin sekolah:
     * Menampilkan tab instrumen kustom milik sekolah dan tab template global (read-only + tombol salin).
     */
    public function index(Request $request, string $kode): View
    {
        $sekolah = Sekolah::where('kode', $kode)->firstOrFail();

        $instrumenSekolah = JenisInstrumen::milikSekolah()
            ->where('sekolah_id', $sekolah->id)
            ->with(['versi' => fn ($q) => $q->orderByDesc('nomor_versi')])
            ->orderBy('urutan')
            ->get();

        $templateGlobal = JenisInstrumen::globalTemplate()
            ->with(['versi' => fn ($q) => $q->orderByDesc('nomor_versi')])
            ->orderBy('urutan')
            ->get();

        return view('admin.instrumen.index', compact('sekolah', 'instrumenSekolah', 'templateGlobal'));
    }

    /**
     * Salin template global menjadi instrumen kustom milik sekolah.
     */
    public function salinKeSekolah(Request $request, string $kode, JenisInstrumen $templateGlobal): RedirectResponse
    {
        $sekolah = Sekolah::where('kode', $kode)->firstOrFail();

        try {
            $jenisSekolah = $this->instrumenService->salinKeSekolah($templateGlobal, $sekolah->id);
        } catch (InvalidArgumentException $e) {
            return back()->with('galat', $e->getMessage());
        }

        return redirect()
            ->route('admin.instrumen.show', ['kode' => $sekolah->kode, 'jenis' => $jenisSekolah])
            ->with('sukses', "Template \"{$templateGlobal->nama}\" berhasil disalin ke sekolah Anda sebagai Draf baru. Silakan sesuaikan bagian dan butir penilaian.");
    }

    /**
     * Detail instrumen kustom sekolah (redirect ke versi terbarunya).
     */
    public function show(Request $request, string $kode, JenisInstrumen $jenis): View|RedirectResponse
    {
        $sekolah = Sekolah::where('kode', $kode)->firstOrFail();

        // Pengguna sekolah hanya boleh mengedit instrumen miliknya
        if ($jenis->isGlobal()) {
            return redirect()
                ->route('admin.instrumen.index', ['kode' => $sekolah->kode])
                ->with('galat', 'Template global bersifat hanya-baca. Gunakan tombol "Salin ke Sekolah Saya" untuk menyesuaikan.');
        }

        $versi = $jenis->versiTerbitTerbaru ?: $jenis->versi()->orderByDesc('nomor_versi')->first();

        if (! $versi) {
            $versi = $jenis->versi()->create([
                'nomor_versi' => 1,
                'status' => 'draft',
                'skor_maks_butir' => 4,
                'ambang_predikat' => ['A' => 86, 'B' => 76, 'C' => 56],
            ]);
        }

        return redirect()->route('admin.instrumen.versi.show', ['kode' => $sekolah->kode, 'jenis' => $jenis, 'versi' => $versi]);
    }

    /**
     * Editor versi instrumen sekolah.
     */
    public function showVersi(Request $request, string $kode, JenisInstrumen $jenis, VersiInstrumen $versi): View|RedirectResponse
    {
        $sekolah = Sekolah::where('kode', $kode)->firstOrFail();

        // Pengguna sekolah tidak boleh mengubah template global
        if ($jenis->isGlobal()) {
            return redirect()
                ->route('admin.instrumen.index', ['kode' => $sekolah->kode])
                ->with('galat', 'Template global tidak dapat diedit langsung.');
        }

        $versi->load(['bagian.butir']);
        $semuaVersi = $jenis->versi()->orderByDesc('nomor_versi')->get();

        return view('admin.instrumen.show', compact('sekolah', 'jenis', 'versi', 'semuaVersi'));
    }

    /**
     * Buat versi baru (salin versi lama menjadi draft baru).
     */
    public function buatVersiBaru(Request $request, string $kode, JenisInstrumen $jenis, VersiInstrumen $versi): RedirectResponse
    {
        $sekolah = Sekolah::where('kode', $kode)->firstOrFail();

        abort_if($jenis->isGlobal(), 403, 'Template global tidak dapat diubah oleh sekolah.');

        $versiBaru = $this->instrumenService->buatVersiBaru($versi);

        return redirect()
            ->route('admin.instrumen.versi.show', ['kode' => $sekolah->kode, 'jenis' => $jenis, 'versi' => $versiBaru])
            ->with('sukses', "Versi {$versiBaru->nomor_versi} (Draft) berhasil dibuat.");
    }

    /**
     * Terbitkan versi draft menjadi terbit.
     */
    public function terbitkanVersi(Request $request, string $kode, JenisInstrumen $jenis, VersiInstrumen $versi): RedirectResponse
    {
        $sekolah = Sekolah::where('kode', $kode)->firstOrFail();

        abort_if($jenis->isGlobal(), 403, 'Template global tidak dapat diubah oleh sekolah.');

        try {
            $this->instrumenService->terbitkanVersi($versi);
        } catch (InvalidArgumentException $e) {
            return back()->with('galat', $e->getMessage());
        }

        return redirect()
            ->route('admin.instrumen.versi.show', ['kode' => $sekolah->kode, 'jenis' => $jenis, 'versi' => $versi])
            ->with('sukses', "Versi {$versi->nomor_versi} berhasil diterbitkan dan siap dipakai supervisi.");
    }

    /**
     * Perbarui ambang predikat dan skor maksimal butir.
     */
    public function updateAmbang(Request $request, string $kode, JenisInstrumen $jenis, VersiInstrumen $versi): RedirectResponse
    {
        abort_if($jenis->isGlobal(), 403, 'Template global tidak dapat diubah oleh sekolah.');

        if ($versi->isTerbit()) {
            return back()->with('galat', 'Versi yang sudah terbit tidak dapat diubah ambang predikatnya.');
        }

        $validated = $request->validate([
            'skor_maks_butir' => ['required', 'integer', 'min:1', 'max:10'],
            'ambang_a' => ['required', 'numeric', 'min:0', 'max:100'],
            'ambang_b' => ['required', 'numeric', 'min:0', 'max:100'],
            'ambang_c' => ['required', 'numeric', 'min:0', 'max:100'],
        ]);

        $versi->update([
            'skor_maks_butir' => $validated['skor_maks_butir'],
            'ambang_predikat' => [
                'A' => (float) $validated['ambang_a'],
                'B' => (float) $validated['ambang_b'],
                'C' => (float) $validated['ambang_c'],
            ],
        ]);

        return back()->with('sukses', 'Pengaturan ambang predikat berhasil diperbarui.');
    }

    /**
     * Tambah bagian baru ke versi instrumen sekolah.
     */
    public function storeBagian(Request $request, string $kode, JenisInstrumen $jenis, VersiInstrumen $versi): RedirectResponse
    {
        abort_if($jenis->isGlobal(), 403, 'Template global tidak dapat diubah oleh sekolah.');

        if ($versi->isTerbit()) {
            return back()->with('galat', 'Versi yang sudah terbit tidak dapat ditambah bagian. Buat versi baru terlebih dahulu.');
        }

        $validated = $request->validate([
            'kode' => ['required', 'string', 'max:50'],
            'judul' => ['required', 'string', 'max:255'],
            'kategori' => ['nullable', 'string', 'max:100'],
        ]);

        $urutan = (int) $versi->bagian()->max('urutan') + 1;

        $versi->bagian()->create([
            'kode' => $validated['kode'],
            'judul' => $validated['judul'],
            'kategori' => $validated['kategori'] ?? null,
            'urutan' => $urutan,
        ]);

        return back()->with('sukses', "Bagian \"{$validated['judul']}\" berhasil ditambahkan.");
    }

    /**
     * Perbarui bagian instrumen sekolah.
     */
    public function updateBagian(Request $request, string $kode, JenisInstrumen $jenis, VersiInstrumen $versi, BagianInstrumen $bagian): RedirectResponse
    {
        abort_if($jenis->isGlobal(), 403, 'Template global tidak dapat diubah oleh sekolah.');

        if ($versi->isTerbit()) {
            return back()->with('galat', 'Versi yang sudah terbit tidak dapat diubah.');
        }

        $validated = $request->validate([
            'kode' => ['required', 'string', 'max:50'],
            'judul' => ['required', 'string', 'max:255'],
            'kategori' => ['nullable', 'string', 'max:100'],
        ]);

        try {
            $bagian->update($validated);
        } catch (ImmutableVersionException $e) {
            return back()->with('galat', $e->getMessage());
        }

        return back()->with('sukses', 'Bagian berhasil diperbarui.');
    }

    /**
     * Hapus bagian instrumen sekolah.
     */
    public function destroyBagian(Request $request, string $kode, JenisInstrumen $jenis, VersiInstrumen $versi, BagianInstrumen $bagian): RedirectResponse
    {
        abort_if($jenis->isGlobal(), 403, 'Template global tidak dapat diubah oleh sekolah.');

        if ($versi->isTerbit()) {
            return back()->with('galat', 'Versi yang sudah terbit tidak dapat dihapus bagiannya.');
        }

        try {
            $bagian->delete();
        } catch (ImmutableVersionException $e) {
            return back()->with('galat', $e->getMessage());
        }

        return back()->with('sukses', 'Bagian berhasil dihapus.');
    }

    /**
     * Geser urutan bagian naik / turun.
     */
    public function geserBagian(Request $request, string $kode, JenisInstrumen $jenis, VersiInstrumen $versi, BagianInstrumen $bagian): RedirectResponse
    {
        abort_if($jenis->isGlobal(), 403, 'Template global tidak dapat diubah oleh sekolah.');

        $arah = $request->input('arah', 'naik');

        try {
            $this->instrumenService->geserUrutanBagian($bagian, $arah);
        } catch (ImmutableVersionException $e) {
            return back()->with('galat', $e->getMessage());
        }

        return back()->with('sukses', 'Urutan bagian berhasil diubah.');
    }

    /**
     * Tambah butir baru ke bagian instrumen sekolah.
     */
    public function storeButir(Request $request, string $kode, JenisInstrumen $jenis, VersiInstrumen $versi, BagianInstrumen $bagian): RedirectResponse
    {
        abort_if($jenis->isGlobal(), 403, 'Template global tidak dapat diubah oleh sekolah.');

        if ($versi->isTerbit()) {
            return back()->with('galat', 'Versi yang sudah terbit tidak dapat ditambah butir. Buat versi baru terlebih dahulu.');
        }

        $validated = $request->validate([
            'uraian' => ['required', 'string'],
            'skor_maks' => ['required', 'integer', 'min:1', 'max:10'],
        ]);

        $urutan = (int) $bagian->butir()->max('urutan') + 1;

        $bagian->butir()->create([
            'uraian' => $validated['uraian'],
            'skor_maks' => $validated['skor_maks'],
            'urutan' => $urutan,
        ]);

        return back()->with('sukses', 'Butir penilaian berhasil ditambahkan.');
    }

    /**
     * Perbarui butir instrumen sekolah.
     */
    public function updateButir(Request $request, string $kode, JenisInstrumen $jenis, VersiInstrumen $versi, BagianInstrumen $bagian, ButirInstrumen $butir): RedirectResponse
    {
        abort_if($jenis->isGlobal(), 403, 'Template global tidak dapat diubah oleh sekolah.');

        if ($versi->isTerbit()) {
            return back()->with('galat', 'Versi yang sudah terbit tidak dapat diubah butirnya.');
        }

        $validated = $request->validate([
            'uraian' => ['required', 'string'],
            'skor_maks' => ['required', 'integer', 'min:1', 'max:10'],
        ]);

        try {
            $butir->update($validated);
        } catch (ImmutableVersionException $e) {
            return back()->with('galat', $e->getMessage());
        }

        return back()->with('sukses', 'Butir penilaian berhasil diperbarui.');
    }

    /**
     * Hapus butir instrumen sekolah.
     */
    public function destroyButir(Request $request, string $kode, JenisInstrumen $jenis, VersiInstrumen $versi, BagianInstrumen $bagian, ButirInstrumen $butir): RedirectResponse
    {
        abort_if($jenis->isGlobal(), 403, 'Template global tidak dapat diubah oleh sekolah.');

        if ($versi->isTerbit()) {
            return back()->with('galat', 'Versi yang sudah terbit tidak dapat dihapus butirnya.');
        }

        try {
            $butir->delete();
        } catch (ImmutableVersionException $e) {
            return back()->with('galat', $e->getMessage());
        }

        return back()->with('sukses', 'Butir penilaian berhasil dihapus.');
    }

    /**
     * Geser urutan butir naik / turun.
     */
    public function geserButir(Request $request, string $kode, JenisInstrumen $jenis, VersiInstrumen $versi, BagianInstrumen $bagian, ButirInstrumen $butir): RedirectResponse
    {
        abort_if($jenis->isGlobal(), 403, 'Template global tidak dapat diubah oleh sekolah.');

        $arah = $request->input('arah', 'naik');

        try {
            $this->instrumenService->geserUrutanButir($butir, $arah);
        } catch (ImmutableVersionException $e) {
            return back()->with('galat', $e->getMessage());
        }

        return back()->with('sukses', 'Urutan butir berhasil diubah.');
    }
}
