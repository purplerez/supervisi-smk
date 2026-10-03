<?php

namespace App\Livewire\SuperAdmin;

use App\Models\Sekolah;
use App\Tenant\TenantContext;
use Illuminate\Validation\Rule;
use Livewire\Component;
use Livewire\WithPagination;

class SekolahManager extends Component
{
    use WithPagination;

    public $search = '';

    public $modalFormTerbuka = false;

    public $sekolah_id = null;

    public $nama = '';

    public $kode = '';

    public $npsn = '';

    public $alamat = '';

    public $status = 'aktif';

    public function mount()
    {
        TenantContext::clear(); // Super-admin namespace
    }

    public function rules()
    {
        return [
            'nama' => 'required|string|max:255',
            'kode' => ['required', 'string', 'max:50', Rule::unique('sekolah', 'kode')->ignore($this->sekolah_id)],
            'npsn' => ['nullable', 'string', 'max:50', Rule::unique('sekolah', 'npsn')->ignore($this->sekolah_id)],
            'alamat' => 'nullable|string',
            'status' => 'required|in:aktif,nonaktif',
        ];
    }

    public function updatedSearch()
    {
        $this->resetPage();
    }

    public function tambah()
    {
        $this->reset(['sekolah_id', 'nama', 'kode', 'npsn', 'alamat']);
        $this->status = 'aktif';
        $this->resetValidation();
        $this->modalFormTerbuka = true;
    }

    public function edit($id)
    {
        $this->resetValidation();
        $sekolah = Sekolah::findOrFail($id);
        $this->sekolah_id = $sekolah->id;
        $this->nama = $sekolah->nama;
        $this->kode = $sekolah->kode;
        $this->npsn = $sekolah->npsn;
        $this->alamat = $sekolah->alamat;
        $this->status = $sekolah->status;
        $this->modalFormTerbuka = true;
    }

    public function simpan()
    {
        $this->validate();

        Sekolah::updateOrCreate(
            ['id' => $this->sekolah_id],
            [
                'nama' => $this->nama,
                'kode' => $this->kode,
                'npsn' => $this->npsn ?: null,
                'alamat' => $this->alamat,
                'status' => $this->status,
            ]
        );

        $this->modalFormTerbuka = false;
        session()->flash('pesan', 'Data sekolah berhasil disimpan.');
    }

    public function render()
    {
        return view('livewire.super-admin.sekolah-manager', [
            'sekolahs' => Sekolah::where('nama', 'like', '%'.$this->search.'%')
                ->orWhere('kode', 'like', '%'.$this->search.'%')
                ->latest()
                ->paginate(10),
        ]);
    }
}
