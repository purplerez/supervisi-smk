<div class="space-y-6">
    {{-- Banner Notifikasi --}}
    @if (session()->has('sukses'))
        <div class="bg-emerald-50 border-l-4 border-emerald-600 p-4 rounded-r-lg shadow-sm flex items-center justify-between">
            <div class="flex items-center space-x-3">
                <svg class="w-6 h-6 text-emerald-600 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                </svg>
                <span class="text-emerald-900 font-medium text-[16px]">{{ session('sukses') }}</span>
            </div>
        </div>
    @endif

    @if (session()->has('galat'))
        <div class="bg-rose-50 border-l-4 border-rose-600 p-4 rounded-r-lg shadow-sm flex items-center justify-between">
            <div class="flex items-center space-x-3">
                <svg class="w-6 h-6 text-rose-600 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                </svg>
                <span class="text-rose-900 font-medium text-[16px]">{{ session('galat') }}</span>
            </div>
        </div>
    @endif

    {{-- Ringkasan Header Form Penilaian --}}
    <div class="bg-white rounded-xl shadow-sm border border-neutral-200 p-6">
        <div class="flex flex-col md:flex-row md:items-center justify-between gap-4 border-b border-neutral-100 pb-5">
            <div>
                <div class="flex items-center gap-3">
                    <h2 class="text-2xl font-bold text-navy-900">{{ $penilaian->jenisInstrumen->nama }}</h2>
                    @if ($penilaian->status === 'belum')
                        <span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-semibold bg-neutral-100 text-neutral-600">Belum Dinilai</span>
                    @elseif ($penilaian->status === 'draft')
                        <span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-semibold bg-amber-100 text-amber-800">Draft Penilaian</span>
                    @elseif ($penilaian->status === 'direvisi')
                        <span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-semibold bg-orange-100 text-orange-800">Sedang Direvisi</span>
                    @elseif ($penilaian->status === 'final')
                        <span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-semibold bg-emerald-100 text-emerald-800">Final (Terkunci)</span>
                    @endif
                </div>
                <p class="text-muted text-[15px] mt-1">
                    Guru: <strong class="text-navy-900">{{ $penilaian->penugasan->guru->nama }}</strong>
                    @if($penilaian->penugasan->infoSupervisi && $penilaian->penugasan->infoSupervisi->mata_pelajaran)
                        &bull; Mapel: <strong class="text-navy-900">{{ $penilaian->penugasan->infoSupervisi->mata_pelajaran }}</strong>
                        &bull; Kelas: <strong class="text-navy-900">{{ $penilaian->penugasan->infoSupervisi->kelas }}</strong>
                    @else
                        &bull; <span class="text-amber-700 font-medium">⚠️ Belum ada jadwal & info mapel dari guru</span>
                    @endif
                    &bull; Versi Instrumen: v{{ $penilaian->versiInstrumen?->nomor_versi ?? '-' }}
                </p>
            </div>

            {{-- Indikator Kelengkapan Butir --}}
            <div class="flex flex-col sm:flex-row items-start sm:items-center gap-4 bg-navy-50/70 p-4 rounded-xl border border-navy-100">
                <div class="text-right sm:text-left">
                    <p class="text-xs text-muted uppercase font-semibold">Kelengkapan Butir</p>
                    <p class="text-xl font-bold text-navy-900">{{ $this->jumlahTerisi }} <span class="text-sm font-normal text-muted">/ {{ $this->totalButir }} terisi</span></p>
                </div>
                <div class="w-36 bg-neutral-200 rounded-full h-3">
                    @php
                        $persen = $this->totalButir > 0 ? round(($this->jumlahTerisi / $this->totalButir) * 100) : 0;
                    @endphp
                    <div class="bg-orange-500 h-3 rounded-full transition-all duration-300" style="width: {{ $persen }}%"></div>
                </div>
                @if ($penilaian->isFinal())
                    <div class="border-l border-neutral-300 pl-4">
                        <p class="text-xs text-muted uppercase font-semibold">Nilai Akhir</p>
                        <p class="text-xl font-bold text-emerald-700">{{ number_format($penilaian->nilai, 2) }} <span class="text-base font-semibold">({{ $penilaian->predikat }})</span></p>
                    </div>
                @endif
            </div>
        </div>

        {{-- Penjelasan Pengisian / Status --}}
        @if ($penilaian->isFinal())
            <div class="mt-4 p-3 bg-neutral-50 rounded-lg text-[15px] text-muted flex items-center gap-2 border border-neutral-200">
                <svg class="w-5 h-5 text-emerald-600 flex-shrink-0" fill="currentColor" viewBox="0 0 20 20">
                    <path fill-rule="evenodd" d="M10 2a8 8 0 100 16 8 8 0 000-16zm3.707 9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd" />
                </svg>
                <span>Penilaian ini telah <strong>Final</strong> pada {{ $penilaian->finalized_at?->translatedFormat('d F Y, H:i') }} oleh {{ $penilaian->finalizedBy?->nama }}. Data terkunci dan tidak dapat diubah lagi kecuali dibuka kembali oleh Admin.</span>
            </div>
        @else
            <div class="mt-4 text-[15px] text-muted">
                Beri skor pada setiap butir instrumen di bawah ini. Anda dapat menyimpan sementara sebagai draft atau langsung memfinalisasi setelah seluruh butir terisi.
            </div>
        @endif
    </div>

    {{-- Daftar Bagian & Butir Instrumen (Accordion) --}}
    <div class="space-y-4" x-data="{ openSections: {{ json_encode($bagianList->pluck('id')->values()->all()) }} }">
        @if ($bagianList->isNotEmpty())
            @foreach ($bagianList as $indexBagian => $bagian)
                <div class="bg-white rounded-xl shadow-sm border border-neutral-200 overflow-hidden">
                    {{-- Accordion Header --}}
                    <button type="button" 
                            @click="openSections.includes({{ $bagian->id }}) ? openSections = openSections.filter(id => id !== {{ $bagian->id }}) : openSections.push({{ $bagian->id }})"
                            class="w-full flex items-center justify-between p-5 bg-navy-50/50 hover:bg-navy-50 text-left transition min-h-[56px]">
                        <div class="flex items-center gap-3">
                            <span class="w-8 h-8 rounded-lg bg-navy-900 text-white flex items-center justify-center font-bold text-sm">
                                {{ $indexBagian + 1 }}
                            </span>
                            <div>
                                <h3 class="font-bold text-navy-900 text-lg">{{ $bagian->judul }}</h3>
                                @if ($bagian->kategori)
                                    <p class="text-xs text-muted">{{ $bagian->kategori }}</p>
                                @endif
                            </div>
                        </div>
                        <div class="flex items-center gap-3">
                            <span class="text-xs font-semibold px-2.5 py-1 rounded bg-white text-navy-900 border border-neutral-200">
                                {{ $bagian->butirInstrumen->count() }} Butir
                            </span>
                            <svg class="w-5 h-5 text-navy-900 transition-transform duration-200" 
                                 :class="openSections.includes({{ $bagian->id }}) ? 'rotate-180' : ''" 
                                 fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
                            </svg>
                        </div>
                    </button>

                    {{-- Accordion Content --}}
                    <div x-show="openSections.includes({{ $bagian->id }})" x-collapse class="divide-y divide-neutral-100 p-6 space-y-6">
                        @foreach ($bagian->butirInstrumen as $indexButir => $butir)
                            <div class="pt-6 first:pt-0">
                                <div class="flex items-start gap-4">
                                    <span class="w-7 h-7 rounded-full bg-neutral-100 text-navy-900 font-semibold text-sm flex items-center justify-center flex-shrink-0 mt-0.5">
                                        {{ $butir->urutan }}
                                    </span>
                                    <div class="flex-1 space-y-4">
                                        <p class="text-ink text-[16px] leading-relaxed font-medium">
                                            {{ $butir->uraian }}
                                        </p>

                                        {{-- Skor Radio Buttons 1..skor_maks --}}
                                        <div>
                                            <label class="block text-xs font-semibold uppercase tracking-wider text-muted mb-2">Pilihan Skor:</label>
                                            <div class="grid grid-cols-2 sm:grid-cols-4 md:grid-cols-5 gap-3">
                                                @for ($s = 1; $s <= $butir->skor_maks; $s++)
                                                    @php
                                                        $isSelected = (int)($skor[$butir->id] ?? 0) === $s;
                                                    @endphp
                                                    <button
                                                        type="button"
                                                        @if(! $penilaian->isFinal()) wire:click="$set('skor.{{ $butir->id }}', {{ $s }})" @endif
                                                        class="relative flex items-center justify-center p-3 rounded-xl border min-h-[48px] select-none transition w-full {{ $isSelected ? 'bg-navy-900 border-navy-900 text-white font-bold ring-2 ring-orange-500 ring-offset-2 shadow' : 'bg-white border-neutral-300 text-ink hover:bg-navy-50 hover:border-navy-400' }} {{ $penilaian->isFinal() ? 'cursor-not-allowed opacity-80' : 'cursor-pointer' }}"
                                                        {{ $penilaian->isFinal() ? 'disabled' : '' }}
                                                        aria-label="Skor {{ $s }}"
                                                    >
                                                        <span class="block text-base font-semibold">{{ $s }}</span>
                                                    </button>
                                                @endfor
                                            </div>
                                        </div>

                                        {{-- Catatan Butir (Opsional) --}}
                                        <div>
                                            <label class="block text-xs font-medium text-muted mb-1">Catatan Khusus Butir Ini (opsional):</label>
                                            <input type="text" 
                                                   wire:model="catatanButir.{{ $butir->id }}"
                                                   {{ $penilaian->isFinal() ? 'disabled' : '' }}
                                                   placeholder="Tuliskan catatan khusus atau bukti pengamatan..." 
                                                   class="w-full text-sm border-neutral-300 rounded-lg focus:border-navy-700 focus:ring focus:ring-orange-500/20 disabled:bg-neutral-50 disabled:text-neutral-500">
                                        </div>
                                    </div>
                                </div>
                            </div>
                        @endforeach
                    </div>
                </div>
            @endforeach
        @endif
    </div>

    {{-- Kolom Catatan Umum dan Tindak Lanjut --}}
    <div class="bg-white rounded-xl shadow-sm border border-neutral-200 p-6 space-y-5">
        <h3 class="text-lg font-bold text-navy-900 border-b border-neutral-100 pb-3">Catatan Keseluruhan & Tindak Lanjut</h3>
        
        <div>
            <label class="block text-sm font-semibold text-ink mb-1.5">Catatan Supervisor / Rekomendasi</label>
            <textarea wire:model="catatan" 
                      rows="3" 
                      {{ $penilaian->isFinal() ? 'disabled' : '' }}
                      placeholder="Tuliskan catatan umum mengenai proses pembelajaran atau administrasi guru..."
                      class="w-full text-[15px] border-neutral-300 rounded-lg focus:border-navy-700 focus:ring focus:ring-orange-500/20 disabled:bg-neutral-50 disabled:text-neutral-500"></textarea>
        </div>

        <div>
            <label class="block text-sm font-semibold text-ink mb-1.5">Rencana Tindak Lanjut</label>
            <textarea wire:model="tindakLanjut" 
                      rows="3" 
                      {{ $penilaian->isFinal() ? 'disabled' : '' }}
                      placeholder="Tuliskan langkah-langkah tindak lanjut pengembangan atau pembinaan selanjutnya..."
                      class="w-full text-[15px] border-neutral-300 rounded-lg focus:border-navy-700 focus:ring focus:ring-orange-500/20 disabled:bg-neutral-50 disabled:text-neutral-500"></textarea>
        </div>
    </div>

    {{-- Tombol Aksi --}}
    @if (! $penilaian->isFinal())
        <div class="bg-white rounded-xl shadow-sm border border-neutral-200 p-6 flex flex-col sm:flex-row items-center justify-between gap-4"
             x-data="{ showFinalDialog: false }">
            <div class="flex items-center gap-2 text-sm text-muted">
                <svg class="w-5 h-5 text-orange-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                </svg>
                <span>Draft dapat disimpan berulang kali sebelum Anda memutuskan memfinalisasi.</span>
            </div>

            <div class="flex items-center gap-3 w-full sm:w-auto justify-end">
                {{-- Tombol Simpan Draft (Hijau #2E7D32) --}}
                <button type="button" 
                        wire:click="simpanDraft" 
                        wire:loading.attr="disabled"
                        class="px-6 py-3 rounded-xl font-bold text-white bg-[#2E7D32] hover:bg-[#256628] active:scale-[0.98] transition shadow-sm min-h-[44px] flex items-center justify-center gap-2">
                    <span wire:loading.remove wire:target="simpanDraft">Simpan Draft</span>
                    <span wire:loading wire:target="simpanDraft" class="flex items-center gap-2">
                        <svg class="animate-spin h-5 w-5 text-white" fill="none" viewBox="0 0 24 24">
                            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v8H4z"></path>
                        </svg>
                        Menyimpan...
                    </span>
                </button>

                {{-- Tombol Finalisasi (Navy-900) --}}
                <button type="button" 
                        @click="showFinalDialog = true"
                        class="px-6 py-3 rounded-xl font-bold text-white bg-navy-900 hover:bg-navy-800 active:scale-[0.98] transition shadow-sm min-h-[44px] flex items-center justify-center gap-2">
                    Finalisasi
                </button>
            </div>

            {{-- Dialog Konfirmasi Finalisasi --}}
            <div x-show="showFinalDialog" 
                 x-cloak
                 class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-navy-900/60 backdrop-blur-sm"
                 @keydown.escape.window="showFinalDialog = false">
                <div class="bg-white rounded-2xl max-w-lg w-full p-6 shadow-2xl border border-neutral-200 space-y-5"
                     @click.away="showFinalDialog = false">
                    <div class="flex items-center gap-4">
                        <div class="w-12 h-12 rounded-full bg-amber-100 text-amber-700 flex items-center justify-center flex-shrink-0">
                            <svg class="w-7 h-7" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
                            </svg>
                        </div>
                        <div>
                            <h4 class="text-xl font-bold text-navy-900">Konfirmasi Finalisasi Nilai</h4>
                            <p class="text-sm text-muted mt-1">Setelah difinalisasi, nilai tidak dapat diubah lagi kecuali dibuka kuncinya oleh Administrator. Lanjutkan?</p>
                        </div>
                    </div>

                    @if ($this->jumlahTerisi < $this->totalButir)
                        <div class="p-3 bg-rose-50 border border-rose-200 rounded-lg text-rose-800 text-sm">
                            <strong>Perhatian:</strong> Masih ada {{ $this->totalButir - $this->jumlahTerisi }} butir yang belum terisi skor. Semua butir wajib diisi sebelum finalisasi.
                        </div>
                    @endif

                    <div class="flex items-center justify-end gap-3 pt-3 border-t border-neutral-100">
                        <button type="button" 
                                @click="showFinalDialog = false"
                                class="px-5 py-2.5 rounded-xl font-semibold text-neutral-700 bg-neutral-100 hover:bg-neutral-200 transition min-h-[44px]">
                            Batal
                        </button>
                        <button type="button" 
                                wire:click="finalisasi" 
                                @click="showFinalDialog = false"
                                wire:loading.attr="disabled"
                                class="px-5 py-2.5 rounded-xl font-bold text-white bg-navy-900 hover:bg-navy-800 transition min-h-[44px] flex items-center gap-2">
                            <span wire:loading.remove wire:target="finalisasi">Ya, Finalisasi Sekarang</span>
                            <span wire:loading wire:target="finalisasi">Memproses...</span>
                        </button>
                    </div>
                </div>
            </div>
        </div>
    @else
        <div class="flex justify-end">
            <a href="{{ route('supervisor.penugasan.show', ['kode' => $penilaian->sekolah?->kode ?? session('sekolah_kode') ?? request()->route('kode'), 'penugasan' => $penilaian->penugasan_id]) }}" 
                class="px-6 py-3 rounded-xl font-semibold text-navy-900 bg-white border border-navy-300 hover:bg-navy-50 transition min-h-[44px] flex items-center gap-2 shadow-sm">
                &larr; Kembali ke Detail Guru
            </a>
        </div>
    @endif
</div>
