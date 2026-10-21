<?php

namespace App\Livewire\Supervisor;

use App\Models\Penilaian;
use App\Services\PenilaianService;
use App\Tenant\TenantContext;
use DomainException;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;
use Livewire\Component;

class FormPenilaian extends Component
{
    public Penilaian $penilaian;

    /**
     * Array skor per butir [butir_id => skor].
     *
     * @var array<int, int|string|null>
     */
    public array $skor = [];

    /**
     * Array catatan khusus per butir [butir_id => catatan].
     *
     * @var array<int, string|null>
     */
    public array $catatanButir = [];

    /**
     * Array bukti amatan per butir (khusus observasi pembelajaran) [butir_id => bukti].
     *
     * @var array<int, string|null>
     */
    public array $buktiPengamatan = [];

    /**
     * Data kualitatif tambahan (misal matriks rencana pengembangan diri).
     *
     * @var array<string, mixed>
     */
    public array $dataKualitatif = [];

    public ?string $catatan = null;

    public ?string $tindakLanjut = null;

    public bool $modalKonfirmasi = false;

    public function boot(): void
    {
        if (auth()->check() && auth()->user()->sekolah_id) {
            TenantContext::set(auth()->user()->sekolah_id);
        }
    }

    public function mount(Penilaian $penilaian): void
    {
        $this->penilaian = $penilaian->loadMissing([
            'penugasan.guru',
            'penugasan.penilai',
            'penugasan.periode',
            'penugasan.infoSupervisi',
            'jenisInstrumen',
            'versiInstrumen.bagian.butir',
            'penilaianButir',
        ]);

        $user = auth()->user();

        // Otorisasi melihat halaman
        if ($user->cannot('view', $this->penilaian)) {
            abort(403, 'Anda tidak memiliki akses ke penilaian ini.');
        }

        // Cek penguncian prasyarat alur bertahap
        if ($this->penilaian->isTerkunciPrasyarat()) {
            abort(403, 'Instrumen ini belum dapat dibuka karena instrumen pada tahap sebelumnya belum difinalisasi.');
        }

        // Jika status masih 'belum' dan pengguna adalah supervisor yang ditugaskan,
        // kunci versi instrumen ke versi aktif saat ini dan ubah ke 'draft'
        if ($this->penilaian->status === 'belum' && $user->can('update', $this->penilaian)) {
            $service = app(PenilaianService::class);
            $service->inisialisasiDraft($this->penilaian, $user);
            $this->penilaian->refresh();
            $this->penilaian->loadMissing(['versiInstrumen.bagian.butir', 'penilaianButir']);
        }

        // Inisialisasi semua butir instrumen ke array skor, catatan, dan bukti
        if ($this->penilaian->versiInstrumen) {
            foreach ($this->penilaian->versiInstrumen->bagian as $bagian) {
                foreach ($bagian->butir as $butir) {
                    $this->skor[$butir->id] = null;
                    $this->catatanButir[$butir->id] = null;
                    $this->buktiPengamatan[$butir->id] = null;
                }
            }
        }

        // Isi form dengan data butir yang sudah tersimpan
        foreach ($this->penilaian->penilaianButir as $item) {
            // Butir kualitatif (skor_maks == 0) tidak memiliki skor numerik
            if (! $item->butirInstrumen || (int) $item->butirInstrumen->skor_maks > 0) {
                $this->skor[$item->butir_instrumen_id] = $item->skor;
            }
            $this->catatanButir[$item->butir_instrumen_id] = $item->catatan;
        }

        // Load data kualitatif tambahan jika ada
        $this->dataKualitatif = $this->penilaian->data_kualitatif ?? [];
        if (isset($this->dataKualitatif['bukti']) && is_array($this->dataKualitatif['bukti'])) {
            $this->buktiPengamatan = $this->dataKualitatif['bukti'];
        }

        // Matriks rencana pengembangan diri dimuat dari isian guru
        if ($this->penilaian->jenisInstrumen?->kode === 'rencana_pengembangan_diri') {
            if (empty($this->dataKualitatif['rencana']) || ! is_array($this->dataKualitatif['rencana'])) {
                $this->dataKualitatif['rencana'] = [];
            }
        }

        $this->catatan = $this->penilaian->catatan;
        $this->tindakLanjut = $this->penilaian->tindak_lanjut;
    }

    /**
     * Set skor untuk butir instrumen tertentu.
     */
    public function setSkor(int|string $butirId, int $skorValue): void
    {
        if ($this->penilaian->isFinal() || $this->penilaian->isDiisiMandiriGuru()) {
            return;
        }

        // Tolak pemberian skor pada butir kualitatif / non-skor (skor_maks === 0)
        $versi = $this->penilaian->versiInstrumen;
        $butir = $versi?->bagian->flatMap->butir->firstWhere('id', (int) $butirId);
        if ($butir && (int) $butir->skor_maks === 0) {
            return;
        }

        // Untuk telaah_perencanaan dan observasi_pembelajaran, skor bernilai 1 s/d 4 (poin 0 ditiadakan)
        $kodeInstrumen = $this->penilaian->jenisInstrumen?->kode;
        if (in_array($kodeInstrumen, ['telaah_perencanaan', 'observasi_pembelajaran'], true) && $skorValue < 1) {
            return;
        }

        $this->skor[(int) $butirId] = (int) $skorValue;
    }

    /**
     * Tambah baris baru pada matriks rencana aksi pengembangan diri (hanya guru).
     */
    public function tambahBarisRencana(): void
    {
        // Matriks hanya diisi oleh guru yang disupervisi
        if ($this->penilaian->isFinal() || $this->penilaian->isDiisiMandiriGuru()) {
            return;
        }

        $this->dataKualitatif['rencana'][] = [
            'aktivitas' => '',
            'tujuan' => '',
            'ukuran' => '',
            'dukungan' => '',
            'waktu' => '',
            'status' => 'Rencana',
        ];
    }

    /**
     * Hapus baris pada matriks rencana aksi pengembangan diri (hanya guru).
     */
    public function hapusBarisRencana(int $index): void
    {
        // Matriks hanya diisi oleh guru yang disupervisi
        if ($this->penilaian->isFinal() || $this->penilaian->isDiisiMandiriGuru()) {
            return;
        }

        if (isset($this->dataKualitatif['rencana'][$index])) {
            unset($this->dataKualitatif['rencana'][$index]);
            $this->dataKualitatif['rencana'] = array_values($this->dataKualitatif['rencana']);
        }
    }

    /**
     * Kumpulkan payload data kualitatif lengkap.
     */
    private function siapkanDataKualitatif(): array
    {
        $payload = $this->dataKualitatif;
        if (! empty($this->buktiPengamatan)) {
            $payload['bukti'] = $this->buktiPengamatan;
        }

        return $payload;
    }

    /**
     * Simpan draf penilaian (hanya supervisor yang ditugaskan dan belum final).
     */
    public function simpanDraft(): void
    {
        $user = auth()->user();

        // Penolakan keras di server bila final atau bukan penilai yang ditugaskan
        if ($this->penilaian->isFinal()) {
            abort(403, 'Penilaian telah difinalisasi dan terkunci.');
        }

        if ($user->cannot('update', $this->penilaian)) {
            abort(403, 'Anda tidak berwenang mengubah penilaian ini.');
        }

        try {
            $service = app(PenilaianService::class);
            $service->simpanDraft(
                $this->penilaian,
                $this->skor,
                $this->catatanButir,
                $this->catatan,
                $this->tindakLanjut,
                $user,
                $this->siapkanDataKualitatif()
            );

            $this->penilaian->refresh();
            session()->flash('sukses', 'Draf penilaian berhasil disimpan.');
        } catch (DomainException $e) {
            session()->flash('galat', $e->getMessage());
        }
    }

    /**
     * Buka modal dialog konfirmasi finalisasi.
     */
    public function bukaKonfirmasiFinalisasi(): void
    {
        $user = auth()->user();

        if ($this->penilaian->isFinal()) {
            abort(403, 'Penilaian telah difinalisasi dan terkunci.');
        }

        if ($user->cannot('update', $this->penilaian)) {
            abort(403, 'Anda tidak berwenang memfinalisasi penilaian ini.');
        }

        // Pada instrumen kolaboratif (pra-observasi, refleksi pasca, rencana pengembangan diri),
        // guru wajib menyelesaikan dan menyerahkan isian mandirinya terlebih dahulu,
        // dan supervisor wajib mengisi Catatan Umum dan Tindak Lanjut sebelum finalisasi
        if ($this->penilaian->isDiisiMandiriGuru()) {
            if (! $this->penilaian->isGuruSudahSubmit()) {
                session()->flash('galat', 'Instrumen belum dapat difinalisasi karena guru yang bersangkutan belum menyelesaikan dan menyerahkan isian mandirinya.');

                return;
            }

            if (empty(trim((string) $this->catatan)) || empty(trim((string) $this->tindakLanjut))) {
                session()->flash('galat', 'Kolom Catatan Umum / Rekomendasi Supervisor dan Rencana Tindak Lanjut Pembinaan wajib diisi sebelum finalisasi.');

                return;
            }
        }

        // Simpan draf saat ini sebelum konfirmasi
        $this->simpanDraft();

        $this->modalKonfirmasi = true;
    }

    /**
     * Eksekusi finalisasi penilaian setelah konfirmasi dialog.
     */
    public function finalisasi(): void
    {
        $user = auth()->user();

        // Penolakan keras di server (403/422) bila final
        if ($this->penilaian->isFinal()) {
            abort(403, 'Penilaian telah difinalisasi dan terkunci.');
        }

        if ($user->cannot('update', $this->penilaian)) {
            abort(403, 'Anda tidak berwenang memfinalisasi penilaian ini.');
        }

        if ($this->penilaian->isDiisiMandiriGuru()) {
            if (! $this->penilaian->isGuruSudahSubmit()) {
                session()->flash('galat', 'Instrumen belum dapat difinalisasi karena guru yang bersangkutan belum menyelesaikan dan menyerahkan isian mandirinya.');

                return;
            }

            if (empty(trim((string) $this->catatan)) || empty(trim((string) $this->tindakLanjut))) {
                session()->flash('galat', 'Kolom Catatan Umum / Rekomendasi Supervisor dan Rencana Tindak Lanjut Pembinaan wajib diisi sebelum finalisasi.');

                return;
            }
        }

        $this->modalKonfirmasi = false;

        try {
            $service = app(PenilaianService::class);

            // Simpan perubahan terakhir terlebih dahulu
            $service->simpanDraft(
                $this->penilaian,
                $this->skor,
                $this->catatanButir,
                $this->catatan,
                $this->tindakLanjut,
                $user,
                $this->siapkanDataKualitatif()
            );

            // Eksekusi finalisasi
            $service->finalisasi($this->penilaian, $user, request()->ip());

            $this->penilaian->refresh();
            $this->penilaian->loadMissing(['versiInstrumen.bagian.butir', 'penilaianButir', 'finalizedBy']);

            session()->flash('sukses', 'Penilaian berhasil difinalisasi dan resmi terkunci.');
        } catch (ValidationException $e) {
            $pesan = $e->validator->errors()->first('butir') ?: 'Semua butir instrumen wajib diisi sebelum finalisasi.';
            session()->flash('galat', $pesan);
        } catch (DomainException $e) {
            session()->flash('galat', $e->getMessage());
        }
    }

    /**
     * Hitung butir yang sudah terisi.
     */
    public function getJumlahTerisiProperty(): int
    {
        $jumlah = 0;
        $versi = $this->penilaian->versiInstrumen;

        if ($this->penilaian->isKuantitatif()) {
            $isTanpaNol = in_array($this->penilaian->jenisInstrumen?->kode, ['telaah_perencanaan', 'observasi_pembelajaran'], true);
            $minSkor = $isTanpaNol ? 1 : 0;
            $butirKuantitatifIds = $versi
                ? $versi->bagian->flatMap->butir->where('skor_maks', '>', 0)->pluck('id')->all()
                : [];

            foreach ($this->skor as $id => $s) {
                if (in_array((int) $id, $butirKuantitatifIds, true) && $s !== null && $s !== '' && (int) $s >= $minSkor) {
                    $jumlah++;
                }
            }
        } else {
            foreach ($this->catatanButir as $c) {
                if ($c !== null && trim($c) !== '') {
                    $jumlah++;
                }
            }
        }

        return $jumlah;
    }

    /**
     * Hitung total butir yang ada pada versi instrumen ini.
     */
    public function getTotalButirProperty(): int
    {
        $versi = $this->penilaian->versiInstrumen;
        if (! $versi) {
            return 0;
        }

        return $versi->bagian->flatMap->butir->count();
    }

    /**
     * Hitung total butir kuantitatif (yang ber-skor) pada versi instrumen ini.
     */
    public function getTotalButirKuantitatifProperty(): int
    {
        $versi = $this->penilaian->versiInstrumen;
        if (! $versi) {
            return 0;
        }

        return $versi->bagian->flatMap->butir->where('skor_maks', '>', 0)->count();
    }

    /**
     * Hitung total skor perolehan saat ini dari butir-butir kuantitatif yang telah dipilih supervisor.
     */
    public function getSkorPerolehanProperty(): int
    {
        $versi = $this->penilaian->versiInstrumen;
        $butirKuantitatifIds = $versi
            ? $versi->bagian->flatMap->butir->where('skor_maks', '>', 0)->pluck('id')->all()
            : null;

        $total = 0;
        foreach ($this->skor as $id => $s) {
            if ($s !== null && $s !== '') {
                if ($butirKuantitatifIds === null || in_array((int) $id, $butirKuantitatifIds, true)) {
                    $total += (int) $s;
                }
            }
        }

        return $total;
    }

    /**
     * Hitung total skor maksimal dari seluruh butir versi instrumen ini.
     */
    public function getSkorMaksimalProperty(): int
    {
        $versi = $this->penilaian->versiInstrumen;
        if (! $versi) {
            return 0;
        }

        return (int) $versi->bagian->flatMap->butir->sum('skor_maks');
    }

    /**
     * Hitung nilai akhir berdasarkan rumus resmi: (skor perolehan / skor maksimal) * 100.
     */
    public function getNilaiAkhirHitunganProperty(): ?float
    {
        $skorMaks = $this->skorMaksimal;
        if ($skorMaks <= 0) {
            return null;
        }

        return round(min(100.0, ($this->skorPerolehan / $skorMaks) * 100), 2);
    }

    /**
     * Tentukan estimasi predikat berdasarkan nilai akhir hitungan dan ambang versi instrumen.
     */
    public function getEstimasiPredikatProperty(): ?string
    {
        $nilai = $this->nilaiAkhirHitungan;
        if ($nilai === null) {
            return null;
        }

        $versi = $this->penilaian->versiInstrumen;
        $ambang = $versi?->ambang_predikat ?? ['A' => 86, 'B' => 76, 'C' => 56];

        foreach ($ambang as $huruf => $batas) {
            if ($nilai >= (float) $batas) {
                return $huruf;
            }
        }

        return 'D';
    }

    public function render(): View
    {
        $versi = $this->penilaian->versiInstrumen;
        $bagianList = $versi ? $versi->bagian->sortBy('urutan') : collect();
        $isReadonly = $this->penilaian->isFinal() || ($this->penilaian->penugasan?->periode?->isDitutup() ?? false);

        return view('livewire.supervisor.form-penilaian', [
            'penilaian' => $this->penilaian,
            'bagianList' => $bagianList,
            'isReadonly' => $isReadonly,
        ]);
    }
}
