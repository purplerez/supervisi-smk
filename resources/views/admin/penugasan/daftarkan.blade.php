<x-app-layout :namaSekolah="$sekolah->nama" title="Daftarkan Guru ke Supervisor">

    <x-page-header
        title="Daftarkan Guru ke Supervisor"
        subtitle="Pilih supervisor, cari dan centang guru yang akan didaftarkan, lalu simpan penugasan."
    >
        <x-slot:actions>
            <x-button variant="outline" href="{{ route('admin.penugasan.index', ['kode' => $sekolah->kode, 'periode_id' => $periode?->id]) }}">
                &larr; Kembali ke Penugasan
            </x-button>
        </x-slot:actions>
    </x-page-header>

    {{-- Filter Periode --}}
    <x-card class="mb-6 p-4">
        <form method="GET" action="{{ route('admin.penugasan.daftarkan', ['kode' => $sekolah->kode]) }}" class="flex flex-col sm:flex-row items-center gap-3">
            <label for="periode_id" class="text-sm font-bold whitespace-nowrap" style="color: var(--color-navy-900);">
                Pilih Periode:
            </label>
            <select name="periode_id" id="periode_id" class="form-input text-sm w-full sm:w-80" onchange="this.form.submit()">
                @forelse($semuaPeriode as $p)
                    <option value="{{ $p->id }}" {{ $periode && $periode->id === $p->id ? 'selected' : '' }}>
                        {{ $p->nama }} ({{ $p->tahun_ajaran }}) &mdash; {{ ucfirst($p->status) }}
                    </option>
                @empty
                    <option value="">Belum ada periode supervisi</option>
                @endforelse
            </select>
        </form>
    </x-card>

    @if(!$periode)
        <x-card class="p-8 text-center">
            <x-empty-state
                title="Periode Belum Dipilih"
                description="Silakan buat atau pilih periode supervisi terlebih dahulu."
            />
        </x-card>
    @elseif($guruBelum->isEmpty())
        <x-card class="p-8 text-center">
            <x-empty-state
                title="Semua Guru Sudah Memiliki Penilai"
                description="Tidak ada guru aktif yang belum memiliki supervisor penilai pada periode {{ $periode->nama }}."
            >
                <x-button variant="outline" href="{{ route('admin.penugasan.index', ['kode' => $sekolah->kode, 'periode_id' => $periode->id]) }}">
                    &larr; Kembali ke Penugasan
                </x-button>
            </x-empty-state>
        </x-card>
    @elseif($supervisors->isEmpty())
        <x-card class="p-8 text-center">
            <x-empty-state
                title="Belum Ada Supervisor"
                description="Tambahkan supervisor terlebih dahulu sebelum mendaftarkan guru."
            />
        </x-card>
    @else
        {{-- Flash messages --}}
        @if(session('sukses'))
            <div class="mb-5 p-4 rounded-xl bg-emerald-50 border border-emerald-200 text-sm text-emerald-800 font-medium flex items-start gap-3">
                <svg xmlns="http://www.w3.org/2000/svg" class="w-5 h-5 mt-0.5 shrink-0 text-emerald-600" viewBox="0 0 20 20" fill="currentColor">
                    <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/>
                </svg>
                <span>{{ session('sukses') }}</span>
            </div>
        @endif
        @if(session('galat') || $errors->any())
            <div class="mb-5 p-4 rounded-xl bg-red-50 border border-red-200 text-sm text-red-800 font-medium flex items-start gap-3">
                <svg xmlns="http://www.w3.org/2000/svg" class="w-5 h-5 mt-0.5 shrink-0 text-red-500" viewBox="0 0 20 20" fill="currentColor">
                    <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd"/>
                </svg>
                <span>{{ session('galat') ?? $errors->first() }}</span>
            </div>
        @endif
        @if(session('peringatan'))
            <div class="mb-5 p-4 rounded-xl bg-amber-50 border border-amber-200 text-sm text-amber-800 font-medium flex items-start gap-3">
                <svg xmlns="http://www.w3.org/2000/svg" class="w-5 h-5 mt-0.5 shrink-0 text-amber-500" viewBox="0 0 20 20" fill="currentColor">
                    <path fill-rule="evenodd" d="M8.257 3.099c.765-1.36 2.722-1.36 3.486 0l5.58 9.92c.75 1.334-.213 2.98-1.742 2.98H4.42c-1.53 0-2.493-1.646-1.743-2.98l5.58-9.92zM11 13a1 1 0 11-2 0 1 1 0 012 0zm-1-8a1 1 0 00-1 1v3a1 1 0 002 0V6a1 1 0 00-1-1z" clip-rule="evenodd"/>
                </svg>
                <span>{{ session('peringatan') }}</span>
            </div>
        @endif

        <x-card class="p-6 border" style="border-color: var(--color-border);"
            x-data="{
                cariGuru: '',
                hanyaDipilih: false,
                selectedIds: {{ json_encode(array_map('strval', (array) old('guru_ids', []))) }},
                pilihSemua: false,

                isSelected(id) {
                    return this.selectedIds.includes(String(id));
                },

                isMatch(el) {
                    if (!el || !el.dataset) return true;
                    if (this.hanyaDipilih && !this.selectedIds.includes(String(el.dataset.id))) {
                        return false;
                    }
                    if (!this.cariGuru) {
                        return true;
                    }
                    return (el.dataset.search || '').includes(this.cariGuru.toLowerCase().trim());
                },

                togglePilihSemua() {
                    const q = this.cariGuru.toLowerCase().trim();
                    const rows = this.$el.querySelectorAll('.guru-row');
                    rows.forEach(el => {
                        const matchSearch = !q || (el.dataset.search || '').includes(q);
                        const matchSelected = !this.hanyaDipilih || this.selectedIds.includes(String(el.dataset.id));
                        if (matchSearch && matchSelected) {
                            const id = String(el.dataset.id);
                            if (this.pilihSemua) {
                                if (!this.selectedIds.includes(id)) {
                                    this.selectedIds.push(id);
                                }
                            } else {
                                this.selectedIds = this.selectedIds.filter(i => i !== id);
                            }
                        }
                    });
                },

                hasVisibleRow() {
                    if (!this.cariGuru && !this.hanyaDipilih) return true;
                    const q = this.cariGuru.toLowerCase().trim();
                    const rows = this.$el.querySelectorAll('.guru-row');
                    for (let el of rows) {
                        const matchSearch = !q || (el.dataset.search || '').includes(q);
                        const matchSelected = !this.hanyaDipilih || this.selectedIds.includes(String(el.dataset.id));
                        if (matchSearch && matchSelected) return true;
                    }
                    return false;
                }
            }"
        >
            <form method="POST" action="{{ route('admin.penugasan.daftarkan.store', ['kode' => $sekolah->kode]) }}">
                @csrf
                <input type="hidden" name="periode_id" value="{{ $periode->id }}">

                {{-- 1. Pilih Supervisor Penilai --}}
                <div class="mb-5">
                    <label for="penilai_id" class="block text-sm font-bold mb-1.5" style="color: var(--color-navy-900);">
                        Supervisor Penilai <span class="text-red-600">*</span>
                    </label>
                    <p class="text-xs text-gray-500 mb-2">Pilih supervisor yang akan menilai guru-guru yang Anda centang di bawah.</p>
                    <select name="penilai_id" id="penilai_id" required
                        class="form-input w-full sm:w-96 text-sm @error('penilai_id') border-red-500 @enderror">
                        <option value="">— Pilih supervisor penilai —</option>
                        @foreach($supervisors as $spv)
                            <option value="{{ $spv->id }}" {{ old('penilai_id') == $spv->id ? 'selected' : '' }}>
                                {{ $spv->nama }}
                            </option>
                        @endforeach
                    </select>
                    @error('penilai_id')
                        <p class="text-xs text-red-600 mt-1">{{ $message }}</p>
                    @enderror
                </div>

                {{-- 2. Search Guru di Bawah Dropdown Supervisor --}}
                <div class="mb-3 pt-4 border-t" style="border-color: var(--color-border);">
                    <div class="flex items-center justify-between mb-2 flex-wrap gap-2">
                        <div>
                            <label for="cari_guru_input" class="block text-sm font-bold" style="color: var(--color-navy-900);">
                                Cari & Pilih Guru <span class="text-red-600">*</span>
                            </label>
                            <p class="text-xs text-gray-500 mt-0.5">
                                Centang guru yang didaftarkan. Pilihan checkbox tetap tersimpan saat mencari nama guru lain.
                            </p>
                        </div>
                        <div class="flex items-center gap-2">
                            <span class="inline-flex items-center px-2.5 py-1 rounded-full text-xs font-semibold"
                                  :class="selectedIds.length > 0 ? 'bg-emerald-100 text-emerald-800' : 'bg-gray-100 text-gray-600'">
                                <span x-text="selectedIds.length"></span> guru dipilih dari {{ $guruBelum->count() }}
                            </span>
                            <button
                                type="button"
                                x-show="selectedIds.length > 0"
                                @click="hanyaDipilih = !hanyaDipilih"
                                class="inline-flex items-center px-2.5 py-1 rounded-full border text-xs font-semibold transition-colors"
                                :class="hanyaDipilih ? 'bg-blue-900 text-white border-blue-900' : 'bg-white text-gray-700 border-gray-300 hover:bg-gray-50'"
                            >
                                <span x-text="hanyaDipilih ? 'Tampilkan Semua' : 'Lihat Terpilih (' + selectedIds.length + ')'"></span>
                            </button>
                        </div>
                    </div>

                    <div class="relative">
                        <span class="absolute inset-y-0 left-3 flex items-center text-gray-400 pointer-events-none">
                            <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 20 20" fill="currentColor">
                                <path fill-rule="evenodd" d="M8 4a4 4 0 100 8 4 4 0 000-8zM2 8a6 6 0 1110.89 3.476l4.817 4.817a1 1 0 01-1.414 1.414l-4.816-4.816A6 6 0 012 8z" clip-rule="evenodd"/>
                            </svg>
                        </span>
                        <input
                            id="cari_guru_input"
                            type="text"
                            x-model="cariGuru"
                            placeholder="Ketik nama guru, NIP, atau username untuk memfilter..."
                            class="form-input w-full pl-9 pr-9 text-sm"
                            autocomplete="off"
                        >
                        <button
                            type="button"
                            x-show="cariGuru.length > 0"
                            @click="cariGuru = ''"
                            class="absolute inset-y-0 right-3 flex items-center text-gray-400 hover:text-gray-600"
                            title="Bersihkan pencarian"
                        >
                            <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 20 20" fill="currentColor">
                                <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd"/>
                            </svg>
                        </button>
                    </div>
                    @error('guru_ids')
                        <p class="text-xs text-red-600 mt-2">{{ $message }}</p>
                    @enderror
                </div>

                {{-- 3. Daftar Checklist Guru --}}
                <div class="border rounded-xl overflow-hidden mb-6" style="border-color: var(--color-border);">
                    {{-- Header Checklist: Pilih Semua --}}
                    <div class="px-4 py-3 flex items-center justify-between border-b" style="background-color: var(--color-navy-50); border-color: var(--color-border);">
                        <label class="flex items-center gap-2.5 cursor-pointer select-none">
                            <input type="checkbox"
                                x-model="pilihSemua"
                                @change="togglePilihSemua()"
                                class="rounded border-gray-300 w-4 h-4 text-navy-900 focus:ring-orange-500">
                            <span class="text-xs font-bold" style="color: var(--color-navy-900);">Pilih Semua yang Terlihat</span>
                        </label>
                        <span class="text-xs text-gray-500">{{ $guruBelum->count() }} guru siap didaftarkan</span>
                    </div>

                    {{-- Baris Guru --}}
                    <div class="divide-y max-h-80 overflow-y-auto" style="border-color: var(--color-border);">
                        @foreach($guruBelum as $guru)
                            <label
                                data-id="{{ $guru->id }}"
                                data-search="{{ strtolower($guru->nama . ' ' . ($guru->username ?? '') . ' ' . ($guru->nip ?? '') . ' ' . ($guru->nuptk ?? '')) }}"
                                x-show="isMatch($el)"
                                class="guru-row flex items-center gap-3 px-4 py-3.5 hover:bg-blue-50/60 cursor-pointer transition-colors"
                                :class="isSelected('{{ $guru->id }}') ? 'bg-blue-50/40' : ''"
                            >
                                <input type="checkbox"
                                    name="guru_ids[]"
                                    value="{{ $guru->id }}"
                                    x-model="selectedIds"
                                    class="rounded border-gray-300 w-4 h-4 shrink-0 text-navy-900 focus:ring-orange-500">
                                <div class="min-w-0 flex-1">
                                    <p class="text-sm font-semibold text-gray-900 truncate">{{ $guru->nama }}</p>
                                    <p class="text-xs text-gray-500 mt-0.5">
                                        {{ $guru->username }}
                                        @if($guru->nip) · NIP: {{ $guru->nip }} @endif
                                        @if($guru->nuptk) · NUPTK: {{ $guru->nuptk }} @endif
                                    </p>
                                </div>
                                <span x-show="isSelected('{{ $guru->id }}')"
                                    class="shrink-0 inline-flex items-center justify-center w-5 h-5 rounded-full text-white text-xs"
                                    style="background-color: var(--color-navy-700);">
                                    <svg xmlns="http://www.w3.org/2000/svg" width="10" height="10" viewBox="0 0 20 20" fill="currentColor">
                                        <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"/>
                                    </svg>
                                </span>
                            </label>
                        @endforeach

                        {{-- Empty search state --}}
                        <div x-show="!hasVisibleRow()" class="px-4 py-8 text-center text-sm text-gray-500">
                            Tidak ada guru yang sesuai dengan pencarian atau filter.
                        </div>
                    </div>
                </div>

                {{-- 4. Tombol Aksi --}}
                <div class="flex items-center gap-3 pt-3 border-t flex-wrap" style="border-color: var(--color-border);">
                    <x-button variant="tambah" type="submit" class="min-h-[44px]">
                        <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4 mr-1.5" viewBox="0 0 20 20" fill="currentColor">
                            <path d="M8 9a3 3 0 100-6 3 3 0 000 6zM8 11a6 6 0 00-6 6h12a6 6 0 00-6-6zM16 7a1 1 0 10-2 0v1h-1a1 1 0 100 2h1v1a1 1 0 102 0v-1h1a1 1 0 100-2h-1V7z"/>
                        </svg>
                        Simpan Penugasan (<span x-text="selectedIds.length">0</span> guru)
                    </x-button>
                    <x-button variant="outline" href="{{ route('admin.penugasan.index', ['kode' => $sekolah->kode, 'periode_id' => $periode->id]) }}" class="min-h-[44px]">
                        Batal
                    </x-button>
                </div>
            </form>
        </x-card>
    @endif

</x-app-layout>
