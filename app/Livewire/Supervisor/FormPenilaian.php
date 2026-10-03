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
            'jenisInstrumen',
            'versiInstrumen.bagian.butir',
            'penilaianButir',
        ]);

        $user = auth()->user();

        // Otorisasi melihat halaman
        if ($user->cannot('view', $this->penilaian)) {
            abort(403, 'Anda tidak memiliki akses ke penilaian ini.');
        }

        // Jika status masih 'belum' dan pengguna adalah supervisor yang ditugaskan,
        // kunci versi instrumen ke versi aktif saat ini dan ubah ke 'draft'
        if ($this->penilaian->status === 'belum' && $user->can('update', $this->penilaian)) {
            $service = app(PenilaianService::class);
            $service->inisialisasiDraft($this->penilaian, $user);
            $this->penilaian->refresh();
            $this->penilaian->loadMissing(['versiInstrumen.bagian.butir', 'penilaianButir']);
        }

        // Isi form dengan data yang sudah tersimpan
        foreach ($this->penilaian->penilaianButir as $item) {
            $this->skor[$item->butir_instrumen_id] = $item->skor;
            $this->catatanButir[$item->butir_instrumen_id] = $item->catatan;
        }

        $this->catatan = $this->penilaian->catatan;
        $this->tindakLanjut = $this->penilaian->tindak_lanjut;
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
                $user
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
                $user
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
     * Hitung butir yang sudah terisi skor >= 1.
     */
    public function getJumlahTerisiProperty(): int
    {
        $jumlah = 0;
        foreach ($this->skor as $s) {
            if ($s !== null && $s !== '' && (int) $s >= 1) {
                $jumlah++;
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

    public function render(): View
    {
        $versi = $this->penilaian->versiInstrumen;
        $bagianList = $versi ? $versi->bagian->sortBy('urutan') : collect();
        $isReadonly = $this->penilaian->isFinal() || ($this->penilaian->penugasan?->periode?->isDitutup() ?? false);

        return view('livewire.supervisor.form-penilaian', [
            'bagianList' => $bagianList,
            'isReadonly' => $isReadonly,
        ]);
    }
}
