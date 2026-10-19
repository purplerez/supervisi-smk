<?php

namespace App\Livewire\Guru;

use App\Models\Penilaian;
use App\Services\PenilaianService;
use App\Tenant\TenantContext;
use DomainException;
use Illuminate\View\View;
use Livewire\Component;

class FormPenilaianGuru extends Component
{
    public Penilaian $penilaian;

    /**
     * Array catatan/jawaban mandiri per butir [butir_id => catatan].
     *
     * @var array<int, string|null>
     */
    public array $catatanButir = [];

    /**
     * Data kualitatif tambahan (misal matriks rencana pengembangan diri).
     *
     * @var array<string, mixed>
     */
    public array $dataKualitatif = [];

    public bool $modalKonfirmasiKirim = false;

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

        // Otorisasi pengisian mandiri oleh guru
        if ($user->cannot('isiMandiri', $this->penilaian)) {
            abort(403, 'Anda tidak memiliki hak akses untuk mengisi instrumen ini atau instrumen ini masih terkunci.');
        }

        // Inisialisasi draft jika status masih 'belum'
        if ($this->penilaian->status === 'belum') {
            $service = app(PenilaianService::class);
            $service->inisialisasiDraft($this->penilaian, $user);
            $this->penilaian->refresh();
            $this->penilaian->loadMissing(['versiInstrumen.bagian.butir', 'penilaianButir']);
        }

        // Inisialisasi array butir
        if ($this->penilaian->versiInstrumen) {
            foreach ($this->penilaian->versiInstrumen->bagian as $bagian) {
                foreach ($bagian->butir as $butir) {
                    $this->catatanButir[$butir->id] = null;
                }
            }
        }

        // Isi form dengan data yang sudah tersimpan
        foreach ($this->penilaian->penilaianButir as $item) {
            $this->catatanButir[$item->butir_instrumen_id] = $item->catatan;
        }

        $this->dataKualitatif = $this->penilaian->data_kualitatif ?? [];

        // Inisialisasi matriks rencana pengembangan diri jika kosong
        if ($this->penilaian->jenisInstrumen?->kode === 'rencana_pengembangan_diri') {
            if (empty($this->dataKualitatif['rencana']) || ! is_array($this->dataKualitatif['rencana'])) {
                $this->dataKualitatif['rencana'] = [
                    [
                        'aktivitas' => '',
                        'tujuan' => '',
                        'ukuran' => '',
                        'dukungan' => '',
                        'waktu' => '',
                        'status' => 'Rencana',
                    ],
                ];
            }
        }
    }

    public function tambahBarisRencana(): void
    {
        if ($this->penilaian->isFinal()) {
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

    public function hapusBarisRencana(int $index): void
    {
        if ($this->penilaian->isFinal()) {
            return;
        }

        if (isset($this->dataKualitatif['rencana'][$index])) {
            unset($this->dataKualitatif['rencana'][$index]);
            $this->dataKualitatif['rencana'] = array_values($this->dataKualitatif['rencana']);
        }
    }

    public function simpanDraft(): void
    {
        $user = auth()->user();

        if ($this->penilaian->isFinal()) {
            abort(403, 'Penilaian telah difinalisasi dan terkunci.');
        }

        if ($user->cannot('isiMandiri', $this->penilaian)) {
            abort(403, 'Anda tidak berwenang mengubah isian ini.');
        }

        try {
            $service = app(PenilaianService::class);
            $service->simpanMandiriGuru(
                $this->penilaian,
                $this->catatanButir,
                $this->dataKualitatif,
                false,
                $user
            );

            $this->penilaian->refresh();
            session()->flash('sukses', 'Draf isian mandiri berhasil disimpan.');
        } catch (DomainException $e) {
            session()->flash('galat', $e->getMessage());
        }
    }

    public function bukaKonfirmasiKirim(): void
    {
        $user = auth()->user();

        if ($this->penilaian->isFinal()) {
            abort(403, 'Penilaian telah difinalisasi dan terkunci.');
        }

        if ($user->cannot('isiMandiri', $this->penilaian)) {
            abort(403, 'Anda tidak berwenang mengirimkan isian ini.');
        }

        $this->simpanDraft();
        $this->modalKonfirmasiKirim = true;
    }

    public function kirimKeSupervisor(): void
    {
        $user = auth()->user();

        if ($this->penilaian->isFinal()) {
            abort(403, 'Penilaian telah difinalisasi dan terkunci.');
        }

        if ($user->cannot('isiMandiri', $this->penilaian)) {
            abort(403, 'Anda tidak berwenang mengirimkan isian ini.');
        }

        $this->modalKonfirmasiKirim = false;

        try {
            $service = app(PenilaianService::class);
            $service->simpanMandiriGuru(
                $this->penilaian,
                $this->catatanButir,
                $this->dataKualitatif,
                true,
                $user
            );

            $this->penilaian->refresh();
            session()->flash('sukses', 'Isian mandiri Anda berhasil difinalisasi dan diserahkan ke Supervisor penilai.');
        } catch (DomainException $e) {
            session()->flash('galat', $e->getMessage());
        }
    }

    public function getJumlahTerisiProperty(): int
    {
        $jumlah = 0;
        foreach ($this->catatanButir as $c) {
            if ($c !== null && trim($c) !== '') {
                $jumlah++;
            }
        }

        return $jumlah;
    }

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

        return view('livewire.guru.form-penilaian-guru', [
            'penilaian' => $this->penilaian,
            'bagianList' => $bagianList,
            'isReadonly' => $isReadonly,
        ]);
    }
}
