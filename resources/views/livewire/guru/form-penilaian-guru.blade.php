<div class="space-y-6">
    {{-- Banner Notifikasi --}}
    @if (session()->has('sukses'))
        <div class="bg-emerald-50 border-l-4 border-emerald-600 p-4 rounded-r-xl shadow-sm flex items-center justify-between">
            <div class="flex items-center space-x-3">
                <svg class="w-6 h-6 text-emerald-600 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                </svg>
                <span class="text-emerald-900 font-medium text-[16px]">{{ session('sukses') }}</span>
            </div>
        </div>
    @endif

    @if (session()->has('galat'))
        <div class="bg-rose-50 border-l-4 border-rose-600 p-4 rounded-r-xl shadow-sm flex items-center justify-between">
            <div class="flex items-center space-x-3">
                <svg class="w-6 h-6 text-rose-600 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                </svg>
                <span class="text-rose-900 font-medium text-[16px]">{{ session('galat') }}</span>
            </div>
        </div>
    @endif

    {{-- Banner Status Penyerahan ke Supervisor --}}
    @if ($penilaian->isGuruSudahSubmit() && ! $penilaian->isFinal())
        <div class="bg-amber-50/80 border-2 border-amber-300 p-4 rounded-2xl shadow-sm flex flex-col sm:flex-row sm:items-center justify-between gap-3">
            <div class="flex items-start sm:items-center gap-3">
                <span class="w-10 h-10 rounded-xl bg-amber-500 text-white flex items-center justify-center shrink-0 font-bold">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                    </svg>
                </span>
                <div>
                    <h4 class="font-bold text-amber-950 text-base">Isian Mandiri Terkirim ke Supervisor</h4>
                    <p class="text-xs text-amber-900 mt-0.5">
                        Dokumen telah diserahkan pada <strong>{{ \Carbon\Carbon::parse($penilaian->data_kualitatif['guru_submitted_at'] ?? now())->translatedFormat('d F Y, H:i') }} WIB</strong>. Supervisor Anda ({{ $penilaian->penugasan->penilai->nama }}) sedang meninjau dan akan melengkapi rekomendasi serta memfinalisasi dokumen.
                    </p>
                </div>
            </div>
            <span class="inline-flex items-center px-3 py-1.5 rounded-full text-xs font-extrabold bg-amber-200 text-amber-900 shrink-0">
                Menunggu Respon Supervisor
            </span>
        </div>
    @endif

    {{-- Ringkasan Header Form Penilaian --}}
    <div class="bg-white rounded-2xl shadow-xs border border-slate-100 p-6">
        <div class="flex flex-col md:flex-row md:items-center justify-between gap-4 border-b border-neutral-100 pb-5">
            <div>
                <div class="flex items-center gap-3">
                    <h2 class="text-2xl font-bold text-navy-900">{{ $penilaian->jenisInstrumen->nama }}</h2>
                    @if ($penilaian->isFinal())
                        <span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-semibold bg-emerald-100 text-emerald-800">Final (Terkunci)</span>
                    @elseif ($penilaian->isGuruSudahSubmit())
                        <span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-semibold bg-amber-100 text-amber-900">Terkirim ke Supervisor</span>
                    @elseif ($penilaian->isDraft())
                        <span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-semibold bg-amber-50 text-amber-800 border border-amber-200">Draf Isian Guru</span>
                    @else
                        <span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-semibold bg-neutral-100 text-neutral-600">Belum Diisi</span>
                    @endif
                </div>
                <p class="text-muted text-[15px] mt-1">
                    Supervisor (Penilai): <strong class="text-navy-900">{{ $penilaian->penugasan->penilai->nama }}</strong>
                    @if($penilaian->penugasan->infoSupervisi && $penilaian->penugasan->infoSupervisi->mata_pelajaran)
                        &bull; Mapel: <strong class="text-navy-900">{{ $penilaian->penugasan->infoSupervisi->mata_pelajaran }}</strong> ({{ $penilaian->penugasan->infoSupervisi->kelas }})
                    @endif
                    &bull; Tahap {{ $penilaian->nomor_tahap }} dari 5
                </p>
            </div>

            {{-- Indikator Kelengkapan Butir --}}
            <div class="flex items-center gap-4 bg-navy-50/70 p-4 rounded-xl border border-navy-100 shrink-0">
                <div>
                    <p class="text-xs text-muted uppercase font-semibold">Kelengkapan Isian Anda</p>
                    <p class="text-xl font-bold text-navy-900">{{ $this->jumlahTerisi }} <span class="text-sm font-normal text-muted">/ {{ $this->totalButir }} aspek terisi</span></p>
                </div>
                @if ($penilaian->isFinal())
                    <div class="border-l border-neutral-300 pl-4">
                        <span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-bold bg-emerald-100 text-emerald-800">
                            Selesai & Difinalisasi
                        </span>
                    </div>
                @endif
            </div>
        </div>

        {{-- Penjelasan Pengisian Mandiri --}}
        <div class="mt-4 text-[15px] text-muted">
            @if ($penilaian->isFinal())
                <p>Dokumen ini telah resmi difinalisasi oleh Supervisor. Seluruh isian telah terkunci dan dapat diunduh dalam bentuk berkas PDF.</p>
            @elseif ($penilaian->jenisInstrumen?->kode === 'pra_observasi')
                <p>Silakan isi tujuan pembelajaran, fokus perilaku pengamatan, area pengembangan diri, dan strategi yang Anda persiapkan untuk pertemuan supervisi kelas. Anda dapat menyimpan draf sewaktu-waktu atau langsung mengirimkannya ke Supervisor.</p>
            @elseif ($penilaian->jenisInstrumen?->kode === 'rencana_pengembangan_diri')
                <p>Susun komitmen rencana aksi peningkatan keprofesian mandiri Anda pada tabel matriks rencana aksi di bawah ini.</p>
            @else
                <p>Isi lembar refleksi pasca pembelajaran ini secara jujur dan mendalam sebagai bahan evaluasi serta tindak lanjut pembinaan bersama Supervisor.</p>
            @endif
        </div>
    </div>

    {{-- KONTEN FORM BERDASARKAN JENIS INSTRUMEN --}}

    {{-- 1. Matriks Rencana Pengembangan Diri Guru --}}
    @if ($penilaian->jenisInstrumen?->kode === 'rencana_pengembangan_diri')
        <div class="bg-white rounded-2xl shadow-xs border border-slate-100 p-6 space-y-5">
            <div class="flex items-center justify-between border-b border-neutral-100 pb-4">
                <div>
                    <h3 class="font-bold text-navy-900 text-lg">Matriks Rencana Pengembangan Diri</h3>
                    <p class="text-sm text-muted">Tuliskan rencana aksi konkret peningkatan kompetensi yang akan Anda lakukan.</p>
                </div>
                @if (! $penilaian->isFinal())
                    <button type="button" 
                            wire:click="tambahBarisRencana"
                            class="px-4 py-2 rounded-xl font-semibold text-sm text-navy-900 bg-navy-50 hover:bg-navy-100 border border-navy-200 transition min-h-[44px] flex items-center gap-1.5">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
                        </svg>
                        Tambah Baris Aktivitas
                    </button>
                @endif
            </div>

            <div class="overflow-x-auto">
                <table class="w-full text-left border-collapse">
                    <thead>
                        <tr class="bg-navy-50 text-navy-900 text-xs font-bold uppercase tracking-wider">
                            <th class="p-3 border border-neutral-200 w-12 text-center">No</th>
                            <th class="p-3 border border-neutral-200 w-1/4">Aktivitas Pengembangan</th>
                            <th class="p-3 border border-neutral-200 w-1/4">Tujuan yang Dicapai</th>
                            <th class="p-3 border border-neutral-200">Ukuran Keberhasilan</th>
                            <th class="p-3 border border-neutral-200">Dukungan Dibutuhkan</th>
                            <th class="p-3 border border-neutral-200 w-28">Target Waktu</th>
                            <th class="p-3 border border-neutral-200 w-28">Status</th>
                            @if (! $penilaian->isFinal())
                                <th class="p-3 border border-neutral-200 w-14 text-center">Aksi</th>
                            @endif
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-neutral-200 text-sm">
                        @forelse ($dataKualitatif['rencana'] ?? [] as $idx => $baris)
                            <tr class="hover:bg-neutral-50/50">
                                <td class="p-3 border border-neutral-200 text-center font-bold text-navy-900 align-top">
                                    {{ $idx + 1 }}
                                </td>
                                <td class="p-2 border border-neutral-200 align-top">
                                    <textarea wire:model.blur="dataKualitatif.rencana.{{ $idx }}.aktivitas"
                                              rows="2"
                                              {{ $penilaian->isFinal() ? 'disabled' : '' }}
                                              placeholder="Mis. Mengikuti Pelatihan Mandiri PMM Topik Pembelajaran Mendalam..."
                                              class="w-full text-sm border-neutral-300 rounded-lg p-2 focus:ring focus:ring-orange-500/20"></textarea>
                                </td>
                                <td class="p-2 border border-neutral-200 align-top">
                                    <textarea wire:model.blur="dataKualitatif.rencana.{{ $idx }}.tujuan"
                                              rows="2"
                                              {{ $penilaian->isFinal() ? 'disabled' : '' }}
                                              placeholder="Peningkatan kemampuan merancang asesmen autentik..."
                                              class="w-full text-sm border-neutral-300 rounded-lg p-2 focus:ring focus:ring-orange-500/20"></textarea>
                                </td>
                                <td class="p-2 border border-neutral-200 align-top">
                                    <textarea wire:model.blur="dataKualitatif.rencana.{{ $idx }}.ukuran"
                                              rows="2"
                                              {{ $penilaian->isFinal() ? 'disabled' : '' }}
                                              placeholder="Modul ajar dan instrumen asesmen siap pakai..."
                                              class="w-full text-sm border-neutral-300 rounded-lg p-2 focus:ring focus:ring-orange-500/20"></textarea>
                                </td>
                                <td class="p-2 border border-neutral-200 align-top">
                                    <textarea wire:model.blur="dataKualitatif.rencana.{{ $idx }}.dukungan"
                                              rows="2"
                                              {{ $penilaian->isFinal() ? 'disabled' : '' }}
                                              placeholder="Diskusi rekan Kombel & masukan Kepala Sekolah..."
                                              class="w-full text-sm border-neutral-300 rounded-lg p-2 focus:ring focus:ring-orange-500/20"></textarea>
                                </td>
                                <td class="p-2 border border-neutral-200 align-top">
                                    <input type="text"
                                           wire:model.blur="dataKualitatif.rencana.{{ $idx }}.waktu"
                                           {{ $penilaian->isFinal() ? 'disabled' : '' }}
                                           placeholder="1 Bulan / Nov 2026"
                                           class="w-full text-sm border-neutral-300 rounded-lg p-2 focus:ring focus:ring-orange-500/20">
                                </td>
                                <td class="p-2 border border-neutral-200 align-top">
                                    <select wire:model="dataKualitatif.rencana.{{ $idx }}.status"
                                            {{ $penilaian->isFinal() ? 'disabled' : '' }}
                                            class="w-full text-xs font-semibold border-neutral-300 rounded-lg p-2 focus:ring focus:ring-orange-500/20">
                                        <option value="Rencana">Rencana</option>
                                        <option value="Sedang Berjalan">Berjalan</option>
                                        <option value="Tercapai">Tercapai</option>
                                    </select>
                                </td>
                                @if (! $penilaian->isFinal())
                                    <td class="p-2 border border-neutral-200 text-center align-top">
                                        <button type="button" 
                                                wire:click="hapusBarisRencana({{ $idx }})"
                                                class="p-2 rounded-lg text-rose-600 hover:bg-rose-50 transition"
                                                title="Hapus Baris">
                                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                                            </svg>
                                        </button>
                                    </td>
                                @endif
                            </tr>
                        @empty
                            <tr>
                                <td colspan="{{ $penilaian->isFinal() ? 7 : 8 }}" class="p-6 text-center text-muted">
                                    Belum ada baris rencana aktivitas. Klik tombol "Tambah Baris Aktivitas" di atas.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    @else
        {{-- 2. Accordion Bagian & Butir (Pra-Observasi & Refleksi Pasca-Observasi) --}}
        <div class="space-y-4" x-data="{ openSections: {{ json_encode($bagianList->pluck('id')->values()->all()) }} }">
            @if ($bagianList->isNotEmpty())
                @foreach ($bagianList as $indexBagian => $bagian)
                    <div class="bg-white rounded-2xl shadow-xs border border-slate-100 overflow-hidden" wire:key="bagian-{{ $bagian->id }}">
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
                                <span class="text-xs font-semibold px-2.5 py-1 rounded-full bg-white text-navy-900 border border-neutral-200">
                                    {{ $bagian->butirInstrumen->count() }} Aspek
                                </span>
                                <svg class="w-5 h-5 text-navy-900 transition-transform duration-200" 
                                     :class="openSections.includes({{ $bagian->id }}) ? 'rotate-180' : ''" 
                                     fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                     <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
                                </svg>
                            </div>
                        </button>

                        <div x-show="openSections.includes({{ $bagian->id }})" x-collapse class="divide-y divide-neutral-100 p-6 space-y-6">
                            @foreach ($bagian->butirInstrumen as $indexButir => $butir)
                                <div class="pt-6 first:pt-0" wire:key="butir-guru-{{ $butir->id }}">
                                    <div class="flex items-start gap-4">
                                        <span class="w-7 h-7 rounded-full bg-navy-100 text-navy-900 font-semibold text-sm flex items-center justify-center shrink-0 mt-0.5">
                                            {{ $butir->urutan }}
                                        </span>
                                        <div class="flex-1 space-y-3">
                                            {{-- Uraian Pertanyaan & Indikator --}}
                                            @php
                                                $adaIndikator = str_contains($butir->uraian, "Indikator amatan:\n");
                                                if ($adaIndikator) {
                                                    $parts = explode("Indikator amatan:\n", $butir->uraian, 2);
                                                    $pertanyaan = trim($parts[0]);
                                                    $indikatorTeks = trim($parts[1] ?? '');
                                                } else {
                                                    $pertanyaan = $butir->uraian;
                                                    $indikatorTeks = null;
                                                }
                                            @endphp

                                            <div>
                                                <h4 class="text-ink text-[16px] leading-relaxed font-semibold">
                                                    {{ $pertanyaan }}
                                                </h4>
                                                @if ($indikatorTeks)
                                                    <div class="mt-2 p-3 bg-amber-50/70 border border-amber-200/80 rounded-xl text-xs text-slate-800 whitespace-pre-line">
                                                        <strong class="text-amber-900 block mb-1">Panduan Pengamatan:</strong>
                                                        {{ $indikatorTeks }}
                                                    </div>
                                                @endif
                                            </div>

                                            <div>
                                                <label class="block text-xs font-semibold text-navy-900 mb-1.5">Jawaban / Refleksi Mandiri Anda:</label>
                                                <textarea wire:model.blur="catatanButir.{{ $butir->id }}"
                                                          rows="3"
                                                          {{ $penilaian->isFinal() ? 'disabled' : '' }}
                                                          placeholder="Tuliskan jawaban atau uraian Anda untuk aspek ini..."
                                                          class="w-full text-[15px] border-neutral-300 rounded-xl focus:border-navy-700 focus:ring focus:ring-orange-500/20 disabled:bg-neutral-50 disabled:text-neutral-500"></textarea>
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
    @endif

    {{-- Panel Informasi Catatan & Rekomendasi Supervisor (Read-Only untuk Guru) --}}
    <div class="bg-white rounded-2xl shadow-xs border border-slate-100 p-6 space-y-4">
        <h3 class="text-lg font-bold text-navy-900 border-b border-neutral-100 pb-3 flex items-center gap-2">
            <svg class="w-5 h-5 text-navy-700" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
            </svg>
            Kesimpulan, Rekomendasi & Tindak Lanjut dari Supervisor
        </h3>

        @if ($penilaian->catatan || $penilaian->tindak_lanjut)
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div class="p-4 rounded-xl bg-neutral-50 border border-neutral-200">
                    <span class="block text-xs font-bold uppercase tracking-wider text-muted mb-1">Catatan Umum / Rekomendasi Supervisor:</span>
                    <p class="text-ink text-sm whitespace-pre-line">{{ $penilaian->catatan ?: '-' }}</p>
                </div>
                <div class="p-4 rounded-xl bg-neutral-50 border border-neutral-200">
                    <span class="block text-xs font-bold uppercase tracking-wider text-muted mb-1">Rencana Tindak Lanjut Pembinaan:</span>
                    <p class="text-ink text-sm whitespace-pre-line">{{ $penilaian->tindak_lanjut ?: '-' }}</p>
                </div>
            </div>
        @else
            <div class="p-4 rounded-xl bg-amber-50/60 border border-amber-200 text-amber-900 text-sm">
                <p class="font-medium flex items-center gap-2">
                    <svg class="w-5 h-5 text-amber-700 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                    </svg>
                    Bagian ini akan diisi oleh Supervisor ({{ $penilaian->penugasan->penilai->nama }}) setelah Anda mengirimkan isian mandiri ini.
                </p>
            </div>
        @endif
    </div>

    {{-- Tombol Aksi Guru --}}
    @if (! $penilaian->isFinal())
        <div class="bg-white rounded-2xl shadow-xs border border-slate-100 p-6 flex flex-col sm:flex-row items-center justify-between gap-4">
            <div class="flex items-center gap-2 text-sm text-muted">
                <svg class="w-5 h-5 text-orange-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                </svg>
                <span>Anda dapat menyimpan draf kapan saja sebelum menyerahkan dokumen ini kepada Supervisor.</span>
            </div>

            <div class="flex items-center gap-3 w-full sm:w-auto justify-end">
                {{-- Simpan Draft --}}
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

                {{-- Kirim ke Supervisor --}}
                <button type="button" 
                        wire:click="bukaKonfirmasiKirim"
                        class="px-6 py-3 rounded-xl font-bold text-white bg-navy-900 hover:bg-navy-800 active:scale-[0.98] transition shadow-sm min-h-[44px] flex items-center justify-center gap-2">
                    <span>Kirim ke Supervisor &rarr;</span>
                </button>
            </div>
        </div>

        {{-- Dialog Konfirmasi Kirim --}}
        @if ($modalKonfirmasiKirim)
            <div class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-navy-900/60 backdrop-blur-sm">
                <div class="bg-white rounded-2xl max-w-lg w-full p-6 shadow-2xl border border-neutral-200 space-y-5">
                    <div class="flex items-center gap-4">
                        <div class="w-12 h-12 rounded-full bg-emerald-100 text-emerald-700 flex items-center justify-center shrink-0">
                            <svg class="w-7 h-7" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                            </svg>
                        </div>
                        <div>
                            <h4 class="text-xl font-bold text-navy-900">Serahkan ke Supervisor?</h4>
                            <p class="text-sm text-muted mt-1">
                                Dokumen ini akan diserahkan kepada Supervisor penilai ({{ $penilaian->penugasan->penilai->nama }}) untuk ditinjau dan dilengkapi dengan rekomendasi tindak lanjut.
                            </p>
                        </div>
                    </div>

                    <div class="flex items-center justify-end gap-3 pt-3 border-t border-neutral-100">
                        <button type="button" 
                                wire:click="$set('modalKonfirmasiKirim', false)"
                                class="px-5 py-2.5 rounded-xl font-semibold text-neutral-700 bg-neutral-100 hover:bg-neutral-200 transition min-h-[44px]">
                            Batal
                        </button>
                        <button type="button" 
                                wire:click="kirimKeSupervisor"
                                wire:loading.attr="disabled"
                                class="px-5 py-2.5 rounded-xl font-bold text-white bg-navy-900 hover:bg-navy-800 transition min-h-[44px] flex items-center gap-2">
                            <span wire:loading.remove wire:target="kirimKeSupervisor">Ya, Serahkan Sekarang</span>
                            <span wire:loading wire:target="kirimKeSupervisor">Memproses...</span>
                        </button>
                    </div>
                </div>
            </div>
        @endif
    @else
        <div class="flex justify-end">
            <a href="{{ route('guru.dashboard', ['kode' => $penilaian->sekolah?->kode ?? session('sekolah_kode')]) }}" 
               class="px-6 py-3 rounded-xl font-semibold text-navy-900 bg-white border border-navy-300 hover:bg-navy-50 transition min-h-[44px] flex items-center gap-2 shadow-sm">
                &larr; Kembali ke Dasbor Guru
            </a>
        </div>
    @endif
</div>
