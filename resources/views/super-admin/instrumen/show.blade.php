<x-layouts.super-admin :title="'Editor ' . $jenis->nama . ' — v' . $versi->nomor_versi">

    {{-- Navigasi Kembali & Header --}}
    <div class="mb-4">
        <a href="{{ route('super-admin.instrumen.index') }}" class="inline-flex items-center gap-1.5 text-sm font-medium hover:underline" style="color: var(--color-navy-700);">
            <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 20 20" fill="currentColor">
                <path fill-rule="evenodd" d="M9.707 16.707a1 1 0 01-1.414 0l-6-6a1 1 0 010-1.414l6-6a1 1 0 011.414 1.414L5.414 9H17a1 1 0 110 2H5.414l4.293 4.293a1 1 0 010 1.414z" clip-rule="evenodd" />
            </svg>
            Kembali ke Daftar Template
        </a>
    </div>

    <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-4 mb-6 pb-4 border-b" style="border-color: var(--color-border);">
        <div>
            <div class="flex items-center gap-2 mb-1">
                <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-semibold uppercase tracking-wider"
                      style="background-color: var(--color-navy-50); color: var(--color-navy-900);">
                    {{ $jenis->kode }}
                </span>
                <span class="text-sm font-semibold" style="color: var(--color-muted);">Template Global</span>
            </div>
            <h1 class="text-2xl sm:text-3xl font-bold" style="color: var(--color-navy-900);">
                {{ $jenis->nama }}
            </h1>
        </div>

        {{-- Aksi Versi --}}
        <div class="flex flex-wrap items-center gap-2">
            @if($versi->isDraft())
                <form method="POST" action="{{ route('super-admin.instrumen.versi.terbitkan', [$jenis, $versi]) }}"
                      onsubmit="return confirm('Apakah Anda yakin ingin menerbitkan Versi {{ $versi->nomor_versi }}? Setelah diterbitkan dan dipakai penilaian, instrumen tidak dapat diubah lagi.');">
                    @csrf
                    @method('PATCH')
                    <x-button type="submit" variant="tambah">
                        <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 20 20" fill="currentColor">
                            <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"/>
                        </svg>
                        Terbitkan Versi {{ $versi->nomor_versi }}
                    </x-button>
                </form>
            @else
                <form method="POST" action="{{ route('super-admin.instrumen.versi.buat-versi', [$jenis, $versi]) }}"
                      onsubmit="return confirm('Buat versi draf baru dengan menyalin seluruh isi Versi {{ $versi->nomor_versi }}?');">
                    @csrf
                    <x-button type="submit" variant="primary">
                        <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 20 20" fill="currentColor">
                            <path d="M7 9a2 2 0 012-2h6a2 2 0 012 2v6a2 2 0 01-2 2H9a2 2 0 01-2-2V9z" />
                            <path d="M5 3a2 2 0 00-2 2v6a2 2 0 002 2V5h8a2 2 0 00-2-2H5z" />
                        </svg>
                        Buat Versi Baru
                    </x-button>
                </form>
            @endif
        </div>
    </div>

    {{-- Pemilih Versi & Kartu Ringkasan --}}
    <div class="grid grid-cols-1 lg:grid-cols-4 gap-6 mb-6">
        {{-- Versi Aktif & Navigasi Versi --}}
        <div class="lg:col-span-1 space-y-4">
            <x-card>
                <h3 class="text-base font-bold mb-3" style="color: var(--color-navy-900);">Riwayat Versi</h3>
                <div class="space-y-2">
                    @foreach($semuaVersi as $v)
                        <a href="{{ route('super-admin.instrumen.versi.show', [$jenis, $v]) }}"
                           class="flex items-center justify-between p-2.5 rounded-lg border text-sm no-underline transition-colors
                                  {{ $v->id === $versi->id ? 'border-orange-500 bg-orange-50/50 font-semibold' : 'border-gray-200 hover:bg-gray-50' }}">
                            <div class="flex items-center gap-2">
                                <span style="color: var(--color-navy-900);">Versi {{ $v->nomor_versi }}</span>
                                @if($v->isTerbit())
                                    <span class="px-2 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-800">Terbit</span>
                                @else
                                    <span class="px-2 py-0.5 rounded-full text-xs font-medium bg-yellow-100 text-yellow-800">Draf</span>
                                @endif
                            </div>
                            <span class="text-xs" style="color: var(--color-muted);">{{ $v->jumlah_butir }} butir</span>
                        </a>
                    @endforeach
                </div>
            </x-card>

            {{-- Pengaturan Ambang Predikat --}}
            <x-card>
                <div class="flex items-center justify-between mb-3">
                    <h3 class="text-base font-bold" style="color: var(--color-navy-900);">Ambang Predikat</h3>
                    @if($versi->isDraft())
                        <span class="text-xs text-green-700 font-medium">Bisa Diedit</span>
                    @else
                        <span class="text-xs text-gray-500 font-medium">Terkunci</span>
                    @endif
                </div>

                @if($versi->isDraft())
                    <form method="POST" action="{{ route('super-admin.instrumen.versi.ambang', [$jenis, $versi]) }}">
                        @csrf
                        @method('PATCH')
                        <div class="space-y-3 text-sm">
                            <div>
                                <label class="block font-medium mb-1" style="color: var(--color-navy-900);">Skor Maks / Butir</label>
                                <input type="number" name="skor_maks_butir" value="{{ old('skor_maks_butir', $versi->skor_maks_butir) }}" min="1" max="10" required class="form-input text-sm w-full">
                            </div>
                            <div class="grid grid-cols-3 gap-2">
                                <div>
                                    <label class="block font-medium mb-1 text-xs">Predikat A (&ge;)</label>
                                    <input type="number" step="0.1" name="ambang_a" value="{{ old('ambang_a', $versi->ambang_predikat['A'] ?? 86) }}" required class="form-input text-sm w-full">
                                </div>
                                <div>
                                    <label class="block font-medium mb-1 text-xs">Predikat B (&ge;)</label>
                                    <input type="number" step="0.1" name="ambang_b" value="{{ old('ambang_b', $versi->ambang_predikat['B'] ?? 76) }}" required class="form-input text-sm w-full">
                                </div>
                                <div>
                                    <label class="block font-medium mb-1 text-xs">Predikat C (&ge;)</label>
                                    <input type="number" step="0.1" name="ambang_c" value="{{ old('ambang_c', $versi->ambang_predikat['C'] ?? 56) }}" required class="form-input text-sm w-full">
                                </div>
                            </div>
                            <x-button type="submit" variant="primary" class="w-full justify-center text-sm py-2">
                                Simpan Ambang
                            </x-button>
                        </div>
                    </form>
                @else
                    <div class="space-y-2 text-sm">
                        <div class="flex justify-between py-1 border-b" style="border-color: var(--color-border);">
                            <span style="color: var(--color-muted);">Skor Maks / Butir:</span>
                            <span class="font-bold">{{ $versi->skor_maks_butir }}</span>
                        </div>
                        <div class="flex justify-between py-1 border-b" style="border-color: var(--color-border);">
                            <span style="color: var(--color-muted);">Sangat Baik (A):</span>
                            <span class="font-bold">&ge; {{ $versi->ambang_predikat['A'] ?? 86 }}%</span>
                        </div>
                        <div class="flex justify-between py-1 border-b" style="border-color: var(--color-border);">
                            <span style="color: var(--color-muted);">Baik (B):</span>
                            <span class="font-bold">&ge; {{ $versi->ambang_predikat['B'] ?? 76 }}%</span>
                        </div>
                        <div class="flex justify-between py-1 border-b" style="border-color: var(--color-border);">
                            <span style="color: var(--color-muted);">Cukup (C):</span>
                            <span class="font-bold">&ge; {{ $versi->ambang_predikat['C'] ?? 56 }}%</span>
                        </div>
                        <div class="flex justify-between py-1" style="border-color: var(--color-border);">
                            <span style="color: var(--color-muted);">Kurang (D):</span>
                            <span class="font-bold">&lt; {{ $versi->ambang_predikat['C'] ?? 56 }}%</span>
                        </div>
                    </div>
                @endif
            </x-card>
        </div>

        {{-- Ringkasan Statistik Versi Ini --}}
        <div class="lg:col-span-3 space-y-6">
            <div class="grid grid-cols-2 sm:grid-cols-4 gap-4">
                <x-card class="text-center p-4">
                    <span class="block text-xs font-semibold uppercase tracking-wider" style="color: var(--color-muted);">Status Versi</span>
                    <div class="mt-1">
                        @if($versi->isTerbit())
                            <x-badge-status status="final">Terbit</x-badge-status>
                        @else
                            <x-badge-status status="draft">Draf</x-badge-status>
                        @endif
                    </div>
                </x-card>
                <x-card class="text-center p-4">
                    <span class="block text-xs font-semibold uppercase tracking-wider" style="color: var(--color-muted);">Total Bagian</span>
                    <span class="block text-2xl font-bold mt-1" style="color: var(--color-navy-900);">{{ $versi->jumlah_bagian }}</span>
                </x-card>
                <x-card class="text-center p-4">
                    <span class="block text-xs font-semibold uppercase tracking-wider" style="color: var(--color-muted);">Total Butir</span>
                    <span class="block text-2xl font-bold mt-1" style="color: var(--color-navy-900);">{{ $versi->jumlah_butir }}</span>
                </x-card>
                <x-card class="text-center p-4">
                    <span class="block text-xs font-semibold uppercase tracking-wider" style="color: var(--color-muted);">Skor Maksimal</span>
                    <span class="block text-2xl font-bold mt-1 text-green-700">{{ $versi->skor_maks }}</span>
                </x-card>
            </div>

            @if($versi->isTerbit())
                <div class="p-4 rounded-xl border bg-blue-50/60 border-blue-200 text-blue-900 text-sm flex items-center justify-between">
                    <div class="flex items-center gap-3">
                        <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 20 20" fill="currentColor" class="text-blue-600 shrink-0">
                            <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7-4a1 1 0 11-2 0 1 1 0 012 0zM9 9a1 1 0 000 2v3a1 1 0 001 1h1a1 1 0 100-2v-3a1 1 0 00-1-1H9z" clip-rule="evenodd"/>
                        </svg>
                        <span>Versi ini sudah <strong>Terbit</strong> dan terkunci. Untuk mengubah butir atau bagian, buat versi baru.</span>
                    </div>
                    <form method="POST" action="{{ route('super-admin.instrumen.versi.buat-versi', [$jenis, $versi]) }}">
                        @csrf
                        <x-button type="submit" variant="primary" class="text-xs">Buat Versi Baru</x-button>
                    </form>
                </div>
            @endif

            {{-- Daftar Bagian & Butir Instrumen --}}
            <div class="space-y-6">
                @forelse($versi->bagian as $bagianIndex => $bagian)
                    <div class="rounded-xl border bg-white shadow-sm overflow-hidden" style="border-color: var(--color-border);"
                         x-data="{ editBagian: false, tambahButir: false }">
                        
                        {{-- Header Bagian --}}
                        <div class="p-4 flex flex-col sm:flex-row sm:items-center justify-between gap-3"
                             style="background-color: var(--color-navy-50); border-bottom: 1px solid var(--color-border);">
                            <div>
                                <div class="flex items-center gap-2">
                                    <span class="font-bold text-sm px-2 py-0.5 rounded bg-white" style="color: var(--color-navy-900); border: 1px solid var(--color-border);">
                                        Bagian {{ $bagian->urutan }}: {{ $bagian->kode }}
                                    </span>
                                    @if($bagian->kategori)
                                        <span class="text-xs px-2 py-0.5 rounded bg-blue-100 text-blue-800">{{ $bagian->kategori }}</span>
                                    @endif
                                </div>
                                <h3 class="text-lg font-bold mt-1" style="color: var(--color-navy-900);">
                                    {{ $bagian->judul }}
                                </h3>
                                <span class="text-xs" style="color: var(--color-muted);">
                                    {{ $bagian->jumlah_butir }} butir &bull; Skor Maksimal: {{ $bagian->skor_maks }}
                                </span>
                            </div>

                            {{-- Tombol Aksi Bagian (Naik, Turun, Edit, Hapus) --}}
                            @if($versi->isDraft())
                                <div class="flex items-center gap-1.5 shrink-0">
                                    {{-- Geser Naik --}}
                                    <form method="POST" action="{{ route('super-admin.instrumen.bagian.geser', [$jenis, $versi, $bagian]) }}">
                                        @csrf
                                        @method('PATCH')
                                        <input type="hidden" name="arah" value="naik">
                                        <button type="submit" title="Geser Naik" class="p-1.5 rounded hover:bg-white text-gray-700 border border-gray-300 transition-colors"
                                                {{ $bagianIndex === 0 ? 'disabled class="opacity-40 cursor-not-allowed"' : '' }}>
                                            <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 20 20" fill="currentColor">
                                                <path fill-rule="evenodd" d="M14.707 12.707a1 1 0 01-1.414 0L10 9.414l-3.293 3.293a1 1 0 01-1.414-1.414l4-4a1 1 0 011.414 0l4 4a1 1 0 010 1.414z" clip-rule="evenodd" />
                                            </svg>
                                        </button>
                                    </form>

                                    {{-- Geser Turun --}}
                                    <form method="POST" action="{{ route('super-admin.instrumen.bagian.geser', [$jenis, $versi, $bagian]) }}">
                                        @csrf
                                        @method('PATCH')
                                        <input type="hidden" name="arah" value="turun">
                                        <button type="submit" title="Geser Turun" class="p-1.5 rounded hover:bg-white text-gray-700 border border-gray-300 transition-colors"
                                                {{ $bagianIndex === $versi->bagian->count() - 1 ? 'disabled class="opacity-40 cursor-not-allowed"' : '' }}>
                                            <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 20 20" fill="currentColor">
                                                <path fill-rule="evenodd" d="M5.293 7.293a1 1 0 011.414 0L10 10.586l3.293-3.293a1 1 0 111.414 1.414l-4 4a1 1 0 01-1.414 0l-4-4a1 1 0 010-1.414z" clip-rule="evenodd" />
                                            </svg>
                                        </button>
                                    </form>

                                    {{-- Edit Bagian --}}
                                    <button type="button" @click="editBagian = !editBagian"
                                            class="px-2.5 py-1 text-xs font-medium rounded transition-colors"
                                            style="background-color: var(--color-action-edit); color: var(--color-ink);">
                                        Ubah
                                    </button>

                                    {{-- Hapus Bagian --}}
                                    <form method="POST" action="{{ route('super-admin.instrumen.bagian.destroy', [$jenis, $versi, $bagian]) }}"
                                          onsubmit="return confirm('Hapus bagian ini beserta seluruh butir di dalamnya?');">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="px-2.5 py-1 text-xs font-medium rounded transition-colors text-white"
                                                style="background-color: var(--color-action-delete);">
                                            Hapus
                                        </button>
                                    </form>
                                </div>
                            @endif
                        </div>

                        {{-- Form Edit Bagian (Inline Toggle) --}}
                        <div x-show="editBagian" class="p-4 bg-yellow-50/60 border-b border-yellow-200" style="display: none;">
                            <form method="POST" action="{{ route('super-admin.instrumen.bagian.update', [$jenis, $versi, $bagian]) }}">
                                @csrf
                                @method('PUT')
                                <div class="grid grid-cols-1 sm:grid-cols-3 gap-3 mb-3">
                                    <div>
                                        <label class="block text-xs font-bold mb-1">Kode Bagian</label>
                                        <input type="text" name="kode" value="{{ old('kode', $bagian->kode) }}" required class="form-input text-sm w-full">
                                    </div>
                                    <div class="sm:col-span-2">
                                        <label class="block text-xs font-bold mb-1">Judul Bagian</label>
                                        <input type="text" name="judul" value="{{ old('judul', $bagian->judul) }}" required class="form-input text-sm w-full">
                                    </div>
                                    <div class="sm:col-span-3">
                                        <label class="block text-xs font-bold mb-1">Kategori (Opsional)</label>
                                        <input type="text" name="kategori" value="{{ old('kategori', $bagian->kategori) }}" class="form-input text-sm w-full">
                                    </div>
                                </div>
                                <div class="flex justify-end gap-2">
                                    <button type="button" @click="editBagian = false" class="px-3 py-1.5 text-xs rounded border border-gray-300">Batal</button>
                                    <x-button type="submit" variant="tambah" class="text-xs py-1.5">Simpan Perubahan</x-button>
                                </div>
                            </form>
                        </div>

                        {{-- Butir-Butir Penilaian --}}
                        <div class="p-4 space-y-3">
                            @forelse($bagian->butir as $butirIndex => $butir)
                                <div class="p-3 rounded-lg border flex flex-col sm:flex-row sm:items-start justify-between gap-3 hover:bg-gray-50/50 transition-colors"
                                     style="border-color: var(--color-border);"
                                     x-data="{ editButir: false }">
                                    
                                    {{-- Kiri: Nomor urut + Uraian --}}
                                    <div class="flex items-start gap-3 flex-1">
                                        <span class="flex items-center justify-center w-7 h-7 rounded-full text-xs font-bold shrink-0 mt-0.5"
                                              style="background-color: var(--color-navy-50); color: var(--color-navy-900);">
                                            {{ $butir->urutan }}
                                        </span>
                                        <div class="flex-1">
                                            <p class="text-sm leading-relaxed" style="color: var(--color-ink);">
                                                {{ $butir->uraian }}
                                            </p>
                                            <span class="inline-block mt-1 text-xs font-medium text-green-800 bg-green-50 px-2 py-0.5 rounded border border-green-200">
                                                Skor Maks: {{ $butir->skor_maks }}
                                            </span>

                                            {{-- Form Edit Butir --}}
                                            <div x-show="editButir" class="mt-3 p-3 bg-yellow-50 rounded-lg border border-yellow-200" style="display: none;">
                                                <form method="POST" action="{{ route('super-admin.instrumen.butir.update', [$jenis, $versi, $bagian, $butir]) }}">
                                                    @csrf
                                                    @method('PUT')
                                                    <div class="space-y-2 mb-2">
                                                        <div>
                                                            <label class="block text-xs font-bold mb-1">Uraian Butir Penilaian</label>
                                                            <textarea name="uraian" rows="2" required class="form-input text-sm w-full">{{ old('uraian', $butir->uraian) }}</textarea>
                                                        </div>
                                                        <div>
                                                            <label class="block text-xs font-bold mb-1">Skor Maksimal</label>
                                                            <input type="number" name="skor_maks" value="{{ old('skor_maks', $butir->skor_maks) }}" min="1" max="10" required class="form-input text-sm w-28">
                                                        </div>
                                                    </div>
                                                    <div class="flex justify-end gap-2">
                                                        <button type="button" @click="editButir = false" class="px-2.5 py-1 text-xs rounded border border-gray-300">Batal</button>
                                                        <x-button type="submit" variant="tambah" class="text-xs py-1">Simpan</x-button>
                                                    </div>
                                                </form>
                                            </div>
                                        </div>
                                    </div>

                                    {{-- Kanan: Aksi Butir (Naik, Turun, Edit, Hapus) --}}
                                    @if($versi->isDraft())
                                        <div class="flex items-center gap-1 self-end sm:self-start shrink-0">
                                            {{-- Geser Naik --}}
                                            <form method="POST" action="{{ route('super-admin.instrumen.butir.geser', [$jenis, $versi, $bagian, $butir]) }}">
                                                @csrf
                                                @method('PATCH')
                                                <input type="hidden" name="arah" value="naik">
                                                <button type="submit" title="Geser Naik" class="p-1 rounded hover:bg-gray-100 text-gray-600 border border-gray-200 transition-colors"
                                                        {{ $butirIndex === 0 ? 'disabled class="opacity-40 cursor-not-allowed"' : '' }}>
                                                    <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 20 20" fill="currentColor">
                                                        <path fill-rule="evenodd" d="M14.707 12.707a1 1 0 01-1.414 0L10 9.414l-3.293 3.293a1 1 0 01-1.414-1.414l4-4a1 1 0 011.414 0l4 4a1 1 0 010 1.414z" clip-rule="evenodd" />
                                                    </svg>
                                                </button>
                                            </form>

                                            {{-- Geser Turun --}}
                                            <form method="POST" action="{{ route('super-admin.instrumen.butir.geser', [$jenis, $versi, $bagian, $butir]) }}">
                                                @csrf
                                                @method('PATCH')
                                                <input type="hidden" name="arah" value="turun">
                                                <button type="submit" title="Geser Turun" class="p-1 rounded hover:bg-gray-100 text-gray-600 border border-gray-200 transition-colors"
                                                        {{ $butirIndex === $bagian->butir->count() - 1 ? 'disabled class="opacity-40 cursor-not-allowed"' : '' }}>
                                                    <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 20 20" fill="currentColor">
                                                        <path fill-rule="evenodd" d="M5.293 7.293a1 1 0 011.414 0L10 10.586l3.293-3.293a1 1 0 111.414 1.414l-4 4a1 1 0 01-1.414 0l-4-4a1 1 0 010-1.414z" clip-rule="evenodd" />
                                                    </svg>
                                                </button>
                                            </form>

                                            {{-- Edit --}}
                                            <button type="button" @click="editButir = !editButir"
                                                    class="px-2 py-1 text-xs font-medium rounded transition-colors"
                                                    style="background-color: var(--color-action-edit); color: var(--color-ink);">
                                                Ubah
                                            </button>

                                            {{-- Hapus --}}
                                            <form method="POST" action="{{ route('super-admin.instrumen.butir.destroy', [$jenis, $versi, $bagian, $butir]) }}"
                                                  onsubmit="return confirm('Hapus butir ini?');">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" class="px-2 py-1 text-xs font-medium rounded transition-colors text-white"
                                                        style="background-color: var(--color-action-delete);">
                                                    Hapus
                                                </button>
                                            </form>
                                        </div>
                                    @endif
                                </div>
                            @empty
                                <div class="text-sm p-4 text-center rounded-lg bg-gray-50 border border-dashed border-gray-300" style="color: var(--color-muted);">
                                    Belum ada butir penilaian dalam bagian ini.
                                </div>
                            @endforelse

                            {{-- Tombol / Form Tambah Butir --}}
                            @if($versi->isDraft())
                                <div class="pt-2">
                                    <div x-show="!tambahButir">
                                        <button type="button" @click="tambahButir = true"
                                                class="inline-flex items-center gap-1.5 text-xs font-bold px-3 py-2 rounded-lg border border-dashed hover:bg-green-50 transition-colors"
                                                style="color: var(--color-action-add); border-color: var(--color-action-add);">
                                            <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 20 20" fill="currentColor">
                                                <path fill-rule="evenodd" d="M10 3a1 1 0 011 1v5h5a1 1 0 110 2h-5v5a1 1 0 11-2 0v-5H4a1 1 0 110-2h5V4a1 1 0 011-1z" clip-rule="evenodd" />
                                            </svg>
                                            Tambah Butir ke Bagian Ini
                                        </button>
                                    </div>
                                    <div x-show="tambahButir" class="p-4 rounded-lg bg-green-50/60 border border-green-200" style="display: none;">
                                        <form method="POST" action="{{ route('super-admin.instrumen.butir.store', [$jenis, $versi, $bagian]) }}">
                                            @csrf
                                            <div class="space-y-3 mb-3">
                                                <div>
                                                    <label class="block text-xs font-bold mb-1" style="color: var(--color-navy-900);">Uraian Butir Penilaian</label>
                                                    <textarea name="uraian" rows="2" placeholder="Tuliskan butir indikator penilaian..." required class="form-input text-sm w-full"></textarea>
                                                </div>
                                                <div>
                                                    <label class="block text-xs font-bold mb-1" style="color: var(--color-navy-900);">Skor Maksimal Butir</label>
                                                    <input type="number" name="skor_maks" value="{{ $versi->skor_maks_butir }}" min="1" max="10" required class="form-input text-sm w-32">
                                                </div>
                                            </div>
                                            <div class="flex justify-end gap-2">
                                                <button type="button" @click="tambahButir = false" class="px-3 py-1.5 text-xs rounded border border-gray-300">Batal</button>
                                                <x-button type="submit" variant="tambah" class="text-xs py-1.5">Simpan Butir</x-button>
                                            </div>
                                        </form>
                                    </div>
                                </div>
                            @endif
                        </div>
                    </div>
                @empty
                    <x-empty-state
                        title="Belum ada bagian instrumen"
                        description="Instrumen ini belum memiliki bagian penilaian. Tambahkan bagian pertama di bawah ini."
                    />
                @endforelse

                {{-- Tambah Bagian Baru --}}
                @if($versi->isDraft())
                    <x-card x-data="{ buka: false }">
                        <div x-show="!buka">
                            <button type="button" @click="buka = true"
                                    class="w-full flex items-center justify-center gap-2 py-4 rounded-lg border-2 border-dashed font-bold hover:bg-green-50 transition-colors"
                                    style="color: var(--color-action-add); border-color: var(--color-action-add);">
                                <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 20 20" fill="currentColor">
                                    <path fill-rule="evenodd" d="M10 3a1 1 0 011 1v5h5a1 1 0 110 2h-5v5a1 1 0 11-2 0v-5H4a1 1 0 110-2h5V4a1 1 0 011-1z" clip-rule="evenodd" />
                                </svg>
                                Tambah Bagian Baru
                            </button>
                        </div>
                        <div x-show="buka" style="display: none;">
                            <h3 class="text-base font-bold mb-4" style="color: var(--color-navy-900);">Tambah Bagian Baru</h3>
                            <form method="POST" action="{{ route('super-admin.instrumen.bagian.store', [$jenis, $versi]) }}">
                                @csrf
                                <div class="grid grid-cols-1 sm:grid-cols-3 gap-4 mb-4">
                                    <div>
                                        <label class="block text-xs font-bold mb-1" style="color: var(--color-navy-900);">Kode Bagian (mis. A, B, RPP)</label>
                                        <input type="text" name="kode" placeholder="mis. A" required class="form-input text-sm w-full">
                                    </div>
                                    <div class="sm:col-span-2">
                                        <label class="block text-xs font-bold mb-1" style="color: var(--color-navy-900);">Judul Bagian</label>
                                        <input type="text" name="judul" placeholder="mis. Kegiatan Pendahuluan Pembelajaran" required class="form-input text-sm w-full">
                                    </div>
                                    <div class="sm:col-span-3">
                                        <label class="block text-xs font-bold mb-1" style="color: var(--color-navy-900);">Kategori (Opsional)</label>
                                        <input type="text" name="kategori" placeholder="mis. Pelaksanaan Pembelajaran" class="form-input text-sm w-full">
                                    </div>
                                </div>
                                <div class="flex justify-end gap-2">
                                    <button type="button" @click="buka = false" class="px-4 py-2 text-sm rounded border border-gray-300">Batal</button>
                                    <x-button type="submit" variant="tambah">Simpan Bagian</x-button>
                                </div>
                            </form>
                        </div>
                    </x-card>
                @endif
            </div>
        </div>
    </div>

</x-layouts.super-admin>
