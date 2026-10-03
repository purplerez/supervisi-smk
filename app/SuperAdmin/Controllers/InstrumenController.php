<?php

namespace App\SuperAdmin\Controllers;

use App\Exceptions\ImmutableVersionException;
use App\Http\Controllers\Controller;
use App\Models\BagianInstrumen;
use App\Models\ButirInstrumen;
use App\Models\JenisInstrumen;
use App\Models\VersiInstrumen;
use App\Services\InstrumenService;
use App\Tenant\TenantContext;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;
use InvalidArgumentException;

class InstrumenController extends Controller
{
    public function __construct(
        private readonly InstrumenService $instrumenService,
    ) {}

    /**
     * Daftar 4 template instrumen global.
     */
    public function index(): View
    {
        TenantContext::clear();

        $instrumen = JenisInstrumen::globalTemplate()
            ->with(['versi' => fn ($q) => $q->orderByDesc('nomor_versi')])
            ->orderBy('urutan')
            ->get();

        return view('super-admin.instrumen.index', compact('instrumen'));
    }

    /**
     * Tampilkan detail jenis instrumen dan versi terbarunya.
     */
    public function show(JenisInstrumen $jenis): View|RedirectResponse
    {
        TenantContext::clear();

        // Cari versi terbit terbaru, atau versi terakhir
        $versi = $jenis->versiTerbitTerbaru ?: $jenis->versi()->orderByDesc('nomor_versi')->first();

        if (! $versi) {
            // Jika belum ada versi sama sekali, buat versi 1 (draft)
            $versi = $jenis->versi()->create([
                'nomor_versi' => 1,
                'status' => 'draft',
                'skor_maks_butir' => 4,
                'ambang_predikat' => ['A' => 86, 'B' => 76, 'C' => 56],
            ]);
        }

        return redirect()->route('super-admin.instrumen.versi.show', [$jenis, $versi]);
    }

    /**
     * Tampilan editor versi instrumen, bagian, dan butir.
     */
    public function showVersi(JenisInstrumen $jenis, VersiInstrumen $versi): View
    {
        TenantContext::clear();

        $versi->load(['bagian.butir']);
        $semuaVersi = $jenis->versi()->orderByDesc('nomor_versi')->get();

        return view('super-admin.instrumen.show', compact('jenis', 'versi', 'semuaVersi'));
    }

    /**
     * Buat versi baru (salin versi lama menjadi draft baru).
     */
    public function buatVersiBaru(JenisInstrumen $jenis, VersiInstrumen $versi): RedirectResponse
    {
        TenantContext::clear();

        $versiBaru = $this->instrumenService->buatVersiBaru($versi);

        return redirect()
            ->route('super-admin.instrumen.versi.show', [$jenis, $versiBaru])
            ->with('sukses', "Versi {$versiBaru->nomor_versi} (Draft) berhasil dibuat dari Versi {$versi->nomor_versi}.");
    }

    /**
     * Terbitkan versi draft menjadi terbit.
     */
    public function terbitkanVersi(JenisInstrumen $jenis, VersiInstrumen $versi): RedirectResponse
    {
        TenantContext::clear();

        try {
            $this->instrumenService->terbitkanVersi($versi);
        } catch (InvalidArgumentException $e) {
            return back()->with('galat', $e->getMessage());
        }

        return redirect()
            ->route('super-admin.instrumen.versi.show', [$jenis, $versi])
            ->with('sukses', "Versi {$versi->nomor_versi} berhasil diterbitkan.");
    }

    /**
     * Perbarui ambang predikat dan skor maksimal butir.
     */
    public function updateAmbang(Request $request, JenisInstrumen $jenis, VersiInstrumen $versi): RedirectResponse
    {
        TenantContext::clear();

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
     * Tambah bagian baru ke versi instrumen.
     */
    public function storeBagian(Request $request, JenisInstrumen $jenis, VersiInstrumen $versi): RedirectResponse
    {
        TenantContext::clear();

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
     * Perbarui bagian instrumen.
     */
    public function updateBagian(Request $request, JenisInstrumen $jenis, VersiInstrumen $versi, BagianInstrumen $bagian): RedirectResponse
    {
        TenantContext::clear();

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
     * Hapus bagian instrumen.
     */
    public function destroyBagian(JenisInstrumen $jenis, VersiInstrumen $versi, BagianInstrumen $bagian): RedirectResponse
    {
        TenantContext::clear();

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
    public function geserBagian(Request $request, JenisInstrumen $jenis, VersiInstrumen $versi, BagianInstrumen $bagian): RedirectResponse
    {
        TenantContext::clear();

        $arah = $request->input('arah', 'naik');

        try {
            $this->instrumenService->geserUrutanBagian($bagian, $arah);
        } catch (ImmutableVersionException $e) {
            return back()->with('galat', $e->getMessage());
        }

        return back()->with('sukses', 'Urutan bagian berhasil diubah.');
    }

    /**
     * Tambah butir baru ke bagian instrumen.
     */
    public function storeButir(Request $request, JenisInstrumen $jenis, VersiInstrumen $versi, BagianInstrumen $bagian): RedirectResponse
    {
        TenantContext::clear();

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
     * Perbarui butir instrumen.
     */
    public function updateButir(Request $request, JenisInstrumen $jenis, VersiInstrumen $versi, BagianInstrumen $bagian, ButirInstrumen $butir): RedirectResponse
    {
        TenantContext::clear();

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
     * Hapus butir instrumen.
     */
    public function destroyButir(JenisInstrumen $jenis, VersiInstrumen $versi, BagianInstrumen $bagian, ButirInstrumen $butir): RedirectResponse
    {
        TenantContext::clear();

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
    public function geserButir(Request $request, JenisInstrumen $jenis, VersiInstrumen $versi, BagianInstrumen $bagian, ButirInstrumen $butir): RedirectResponse
    {
        TenantContext::clear();

        $arah = $request->input('arah', 'naik');

        try {
            $this->instrumenService->geserUrutanButir($butir, $arah);
        } catch (ImmutableVersionException $e) {
            return back()->with('galat', $e->getMessage());
        }

        return back()->with('sukses', 'Urutan butir berhasil diubah.');
    }
}
