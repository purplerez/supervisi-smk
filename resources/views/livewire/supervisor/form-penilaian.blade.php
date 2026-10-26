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
                        <span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-semibold bg-neutral-100 text-neutral-600">Belum Dimulai</span>
                    @elseif ($penilaian->status === 'draft')
                        <span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-semibold bg-amber-100 text-amber-800">Draft Isian</span>
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
                    &bull; Versi: v{{ $penilaian->versiInstrumen?->nomor_versi ?? '1' }}
                </p>
            </div>

            {{-- Indikator Kelengkapan Butir & Nilai --}}
            <div class="flex flex-col sm:flex-row items-start sm:items-center gap-4 bg-navy-50/70 p-4 rounded-xl border border-navy-100">
                @if ($penilaian->isKuantitatif())
                    <div class="text-right sm:text-left">
                        <p class="text-xs text-muted uppercase font-semibold">Kelengkapan Skor</p>
                        <p class="text-xl font-bold text-navy-900">{{ $this->jumlahTerisi }} <span class="text-sm font-normal text-muted">/ {{ $this->totalButir }} butir</span></p>
                    </div>
                    <div class="w-28 bg-neutral-200 rounded-full h-3">
                        @php
                            $persen = $this->totalButir > 0 ? round(($this->jumlahTerisi / $this->totalButir) * 100) : 0;
                        @endphp
                        <div class="bg-orange-500 h-3 rounded-full transition-all duration-300" style="width: {{ $persen }}%"></div>
                    </div>
                    <div class="border-l border-neutral-300 pl-4">
                        @if ($penilaian->isFinal())
                            <p class="text-xs text-muted uppercase font-semibold">Skor: {{ $penilaian->total_skor }} / {{ $penilaian->skor_maks }}</p>
                            <p class="text-xl font-bold text-emerald-700">{{ number_format($penilaian->nilai, 2) }} <span class="text-sm font-semibold">({{ $penilaian->predikat }})</span></p>
                        @else
                            <p class="text-xs text-muted font-medium">Skor: <strong class="text-navy-900">{{ $this->skorPerolehan }}</strong> / {{ $this->skorMaksimal }}</p>
                            <p class="text-lg font-bold text-navy-900">
                                {{ number_format($this->nilaiAkhirHitungan, 2) }}
                                <span class="text-xs font-semibold text-muted">({{ $this->estimasiPredikat }})</span>
                            </p>
                            <p class="text-[11px] text-muted">Rumus: (Skor / Maks) &times; 100</p>
                        @endif
                    </div>
                @else
                    <div>
                        <p class="text-xs text-muted uppercase font-semibold">Kelengkapan Isian</p>
                        <p class="text-xl font-bold text-navy-900">{{ $this->jumlahTerisi }} <span class="text-sm font-normal text-muted">aspek terisi</span></p>
                    </div>
                    @if ($penilaian->isFinal())
                        <div class="border-l border-neutral-300 pl-4">
                            <span class="inline-flex items-center px-3 py-1 rounded-full text-sm font-bold bg-emerald-100 text-emerald-800">
                                Selesai & Terlaksana
                            </span>
                        </div>
                    @endif
                @endif
            </div>
        </div>

        {{-- Penjelasan Pengisian / Status --}}
        @if ($penilaian->isFinal())
            <div class="mt-4 p-3 bg-neutral-50 rounded-lg text-[15px] text-muted flex items-center gap-2 border border-neutral-200">
                <svg class="w-5 h-5 text-emerald-600 flex-shrink-0" fill="currentColor" viewBox="0 0 20 20">
                    <path fill-rule="evenodd" d="M10 2a8 8 0 100 16 8 8 0 000-16zm3.707 9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd" />
                </svg>
                <span>Dokumen ini telah <strong>Final</strong> pada {{ $penilaian->finalized_at?->translatedFormat('d F Y, H:i') }} oleh {{ $penilaian->finalizedBy?->nama }}. Data terkunci dan tidak dapat diubah lagi kecuali dibuka kembali oleh Admin.</span>
            </div>
        @else
            @if ($penilaian->isDiisiMandiriGuru())
                @if ($penilaian->isGuruSudahSubmit())
                    <div class="mt-4 p-4 rounded-xl bg-amber-50 border border-amber-300 flex items-start gap-3 shadow-sm">
                        <div class="w-8 h-8 rounded-full bg-amber-200 text-amber-900 flex items-center justify-center shrink-0 mt-0.5 font-bold text-base">
                            🔔
                        </div>
                        <div class="text-[15px] text-amber-950">
                            <p class="font-bold text-amber-900">Guru Telah Menyerahkan Isian Mandiri</p>
                            <p class="mt-0.5 text-amber-800">
                                Diserahkan pada {{ \Carbon\Carbon::parse($penilaian->data_kualitatif['guru_submitted_at'] ?? now())->translatedFormat('d F Y, H:i') }}. 
                                Silakan tinjau respon guru di bawah ini, lalu isi <strong class="text-navy-900">Catatan Umum / Rekomendasi Supervisor</strong> dan <strong class="text-navy-900">Rencana Tindak Lanjut Pembinaan</strong> sebelum memfinalisasi lembar ini.
                            </p>
                        </div>
                    </div>
                @else
                    <div class="mt-4 p-4 rounded-xl bg-sky-50 border border-sky-200 flex items-start gap-3">
                        <div class="w-8 h-8 rounded-full bg-sky-200 text-sky-900 flex items-center justify-center shrink-0 mt-0.5 font-bold text-sm">
                            ℹ️
                        </div>
                        <div class="text-[14px] text-sky-950">
                            <p class="font-bold text-sky-900">Menunggu Penyerahan Isian Mandiri oleh Guru</p>
                            <p class="mt-0.5 text-sky-800">
                                Lembar ini diisi secara mandiri oleh Guru yang disupervisi (<strong>{{ $penilaian->penugasan->guru->nama }}</strong>). Supervisor baru dapat memberikan rekomendasi supervisi dan memfinalisasi setelah Guru menyelesaikan serta menyerahkan isian mandirinya.
                            </p>
                        </div>
                    </div>
                @endif
            @else
                <div class="mt-4 text-[15px] text-muted">
                    @if ($penilaian->isKuantitatif())
                        Beri skala penilaian (0-4) pada setiap aspek pengamatan di bawah ini. Anda dapat melengkapi bukti yang teramati serta catatan supervisor sebelum memfinalisasi.
                    @else
                        Lengkapi catatan dialogis supervisi di bawah ini bersama guru yang disupervisi.
                    @endif
                </div>
            @endif
        @endif
    </div>

    {{-- KONTEN FORM SPESIFIK BERDASARKAN JENIS INSTRUMEN --}}

    {{-- 1. Tampilan Khusus Rencana Pengembangan Diri (Read-Only Isian Guru) --}}
    @if ($penilaian->jenisInstrumen?->kode === 'rencana_pengembangan_diri')
        <div class="bg-white rounded-xl shadow-sm border border-neutral-200 p-6 space-y-5">
            <div class="flex flex-col sm:flex-row sm:items-center justify-between border-b border-neutral-100 pb-4 gap-2">
                <div>
                    <h3 class="font-bold text-navy-900 text-lg flex items-center gap-2">
                        <svg class="w-5 h-5 text-emerald-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-6 9l2 2 4-4" />
                        </svg>
                        <span>Matriks Rencana Pengembangan Diri Guru</span>
                    </h3>
                    <p class="text-sm text-muted mt-0.5">Komitmen rencana aksi peningkatan kompetensi yang disusun secara mandiri oleh Guru.</p>
                </div>
                <span class="px-3 py-1 rounded-full text-xs font-bold bg-emerald-50 text-emerald-800 border border-emerald-200 self-start sm:self-auto flex items-center gap-1.5">
                    <svg class="w-3.5 h-3.5 text-emerald-600" fill="currentColor" viewBox="0 0 20 20">
                        <path fill-rule="evenodd" d="M10 9a3 3 0 100-6 3 3 0 000 6zm-7 9a7 7 0 1114 0H3z" clip-rule="evenodd" />
                    </svg>
                    <span>Diisi Mandiri oleh Guru</span>
                </span>
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
                            <th class="p-3 border border-neutral-200 w-28 text-center">Status</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-neutral-200 text-sm">
                        @forelse ($dataKualitatif['rencana'] ?? [] as $idx => $baris)
                            <tr class="hover:bg-neutral-50/50">
                                <td class="p-3 border border-neutral-200 text-center font-bold text-navy-900 align-top">
                                    {{ $idx + 1 }}
                                </td>
                                <td class="p-3 border border-neutral-200 align-top">
                                    <p class="whitespace-pre-line text-ink font-medium leading-relaxed">{{ $baris['aktivitas'] ?: '-' }}</p>
                                </td>
                                <td class="p-3 border border-neutral-200 align-top">
                                    <p class="whitespace-pre-line text-ink leading-relaxed">{{ $baris['tujuan'] ?: '-' }}</p>
                                </td>
                                <td class="p-3 border border-neutral-200 align-top">
                                    <p class="whitespace-pre-line text-ink leading-relaxed">{{ $baris['ukuran'] ?: '-' }}</p>
                                </td>
                                <td class="p-3 border border-neutral-200 align-top">
                                    <p class="whitespace-pre-line text-ink leading-relaxed">{{ $baris['dukungan'] ?: '-' }}</p>
                                </td>
                                <td class="p-3 border border-neutral-200 align-top">
                                    <span class="font-medium text-navy-900">{{ $baris['waktu'] ?: '-' }}</span>
                                </td>
                                <td class="p-3 border border-neutral-200 align-top text-center">
                                    @php
                                        $st = $baris['status'] ?? 'Rencana';
                                        $statusBadge = match($st) {
                                            'Tercapai' => 'bg-emerald-100 text-emerald-800 border-emerald-200',
                                            'Sedang Berjalan' => 'bg-amber-100 text-amber-800 border-amber-200',
                                            default => 'bg-neutral-100 text-neutral-700 border-neutral-200',
                                        };
                                    @endphp
                                    <span class="inline-flex items-center px-2.5 py-1 rounded-full text-xs font-semibold border {{ $statusBadge }}">
                                        {{ $st }}
                                    </span>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="7" class="p-8 text-center text-muted italic">
                                    Guru belum menyusun atau menyerahkan matriks rencana pengembangan diri.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    @else
        {{-- 2. Form Standar: Accordion Bagian & Butir (Pra-Observasi, Telaah, Observasi, Refleksi) --}}
        <div class="space-y-4" x-data="{ 
            openSections: {{ json_encode($bagianList->pluck('id')->values()->all()) }},
            expandedIndikator: {},
            toggleIndikator(id) {
                this.expandedIndikator[id] = !this.expandedIndikator[id];
            },
            isIndikatorOpen(id) {
                return !!this.expandedIndikator[id];
            },
            bukaSemuaIndikator(ids) {
                ids.forEach(id => { this.expandedIndikator[id] = true; });
            },
            tutupSemuaIndikator(ids) {
                ids.forEach(id => { this.expandedIndikator[id] = false; });
            }
        }">
            @if ($bagianList->isNotEmpty())
                @foreach ($bagianList as $indexBagian => $bagian)
                    @php
                        $isBagianKualitatif = ($bagian->kategori === 'Kualitatif') || str_contains(strtolower($bagian->judul), 'catatan') || str_contains(strtolower($bagian->judul), 'rekomendasi');
                        $adaButirKuantitatif = $penilaian->isKuantitatif() && ! $isBagianKualitatif && $bagian->butirInstrumen->contains(fn($b) => (int)($b->skor_maks ?? 0) > 0);
                        $butirDenganIndikatorIds = $bagian->butirInstrumen->filter(fn($b) => str_contains($b->uraian, "Indikator amatan:\n"))->pluck('id')->values()->all();
                    @endphp

                    <div class="bg-white rounded-xl shadow-sm border border-neutral-200 overflow-hidden" wire:key="bagian-{{ $bagian->id }}">
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

                        @if ($adaButirKuantitatif)
                            {{-- Header Kolom Tabel Visual (Desktop) Khusus Bagian Kuantitatif --}}
                            <div class="hidden md:flex items-center justify-between px-6 py-3 bg-navy-50/70 border-b border-navy-100 text-xs font-bold text-navy-900 uppercase tracking-wider">
                                <div class="flex items-center gap-3 w-7/12 lg:w-8/12">
                                    <span>Aspek &amp; Indikator Pengamatan</span>
                                    @if (count($butirDenganIndikatorIds) > 0)
                                        <div class="flex items-center gap-1.5 font-normal normal-case text-muted text-xs">
                                            <span>&bull;</span>
                                            <button type="button" 
                                                    @click="bukaSemuaIndikator({{ json_encode($butirDenganIndikatorIds) }})"
                                                    class="text-navy-700 hover:text-navy-900 underline font-medium cursor-pointer">
                                                Buka Semua Panduan
                                            </button>
                                            <span>/</span>
                                            <button type="button" 
                                                    @click="tutupSemuaIndikator({{ json_encode($butirDenganIndikatorIds) }})"
                                                    class="text-navy-700 hover:text-navy-900 underline font-medium cursor-pointer">
                                                Tutup Semua
                                            </button>
                                        </div>
                                    @endif
                                </div>
                                <div class="w-5/12 lg:w-4/12 text-center">
                                    Poin Penilaian (Skor)
                                </div>
                            </div>
                        @endif

                        {{-- Accordion Content --}}
                        <div x-show="openSections.includes({{ $bagian->id }})" x-collapse class="{{ $adaButirKuantitatif ? 'divide-y divide-neutral-200/80 p-4 sm:p-6 space-y-4' : 'divide-y divide-neutral-100 p-6 space-y-6' }}">
                            @foreach ($bagian->butirInstrumen as $indexButir => $butir)
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
                                    $isButirKuantitatif = $penilaian->isKuantitatif() && ! $isBagianKualitatif && (int) ($butir->skor_maks ?? 0) > 0;
                                @endphp

                                {{-- A. JIKA BUTIR KUANTITATIF (MENERAPKAN POIN/SKOR) -> FORMAT TABEL 2-KOLOM & CLICK-TO-VIEW --}}
                                @if ($isButirKuantitatif)
                                    @php
                                        $skorMaksButir = (int) ($butir->skor_maks ?: ($penilaian->versiInstrumen?->skor_maks_butir ?: 4));
                                        $currentSkor = isset($skor[$butir->id]) && $skor[$butir->id] !== null && $skor[$butir->id] !== '' ? (int)$skor[$butir->id] : null;
                                    @endphp

                                    <div class="pt-4 first:pt-0" wire:key="butir-item-{{ $butir->id }}">
                                        <div class="grid grid-cols-1 md:grid-cols-12 gap-5 md:gap-6 items-start">
                                            {{-- Sisi Kiri: Pertanyaan, Indikator (Click to View), Bukti & Catatan --}}
                                            <div class="md:col-span-7 lg:col-span-8 space-y-3">
                                                <div class="flex items-start gap-3.5">
                                                    <span class="w-8 h-8 rounded-xl bg-navy-100 text-navy-900 font-bold text-sm flex items-center justify-center shrink-0 mt-0.5 shadow-2xs border border-navy-200/60">
                                                        {{ $butir->urutan }}
                                                    </span>
                                                    <div class="flex-1 space-y-2.5">
                                                        <h4 class="text-ink text-[16px] md:text-[16.5px] leading-relaxed font-semibold">
                                                            {{ $pertanyaan }}
                                                        </h4>

                                                        {{-- Tombol Toggle Click-to-View Indikator Amatan (Kuning) --}}
                                                        @if ($indikatorTeks)
                                                            <div>
                                                                <button
                                                                    type="button"
                                                                    @click="toggleIndikator({{ $butir->id }})"
                                                                    class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-lg text-xs font-semibold transition-all border select-none focus:outline-none focus:ring-2 focus:ring-orange-500/50"
                                                                    :class="isIndikatorOpen({{ $butir->id }}) 
                                                                        ? 'bg-amber-100 text-amber-950 border-amber-300 shadow-2xs' 
                                                                        : 'bg-amber-50 hover:bg-amber-100 text-amber-900 border-amber-200'"
                                                                    aria-label="Lihat atau sembunyikan indikator amatan"
                                                                >
                                                                    <svg class="w-4 h-4 text-amber-700 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                                                                    </svg>
                                                                    <span x-show="!isIndikatorOpen({{ $butir->id }})">Lihat Indikator / Panduan Amatan</span>
                                                                    <span x-show="isIndikatorOpen({{ $butir->id }})" style="display:none;">Sembunyikan Panduan</span>
                                                                    <svg class="w-3.5 h-3.5 transition-transform duration-200 shrink-0"
                                                                         :class="isIndikatorOpen({{ $butir->id }}) ? 'rotate-180' : ''"
                                                                         fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
                                                                    </svg>
                                                                </button>

                                                                {{-- Panel Indikator Collapsible (Latar Kuning) --}}
                                                                <div x-show="isIndikatorOpen({{ $butir->id }})" x-collapse style="display: none;">
                                                                    <div class="mt-2.5 p-4 bg-amber-50/90 border border-amber-200 rounded-xl shadow-2xs">
                                                                        <div class="flex items-center gap-2 mb-2 text-amber-900 font-bold text-xs uppercase tracking-wider">
                                                                            <svg class="w-4 h-4 text-amber-700 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-6 9l2 2 4-4" />
                                                                            </svg>
                                                                            <span>Fokus Indikator / Panduan Pengamatan:</span>
                                                                        </div>
                                                                        <div class="text-[14.5px] leading-relaxed whitespace-pre-line text-slate-800 font-normal pl-4 border-l-2 border-amber-400">
                                                                            {{ $indikatorTeks }}
                                                                        </div>
                                                                    </div>
                                                                </div>
                                                            </div>
                                                        @endif

                                                        {{-- Bukti Pembelajaran (Khusus Observasi Pembelajaran) --}}
                                                        @if ($penilaian->jenisInstrumen?->kode === 'observasi_pembelajaran')
                                                            <div class="pt-1.5">
                                                                <label class="block text-xs font-semibold text-navy-900 mb-1">Bukti Perilaku / Pembelajaran yang Teramati:</label>
                                                                <textarea wire:model.blur="buktiPengamatan.{{ $butir->id }}"
                                                                          rows="2"
                                                                          {{ $penilaian->isFinal() ? 'disabled' : '' }}
                                                                          placeholder="Tuliskan bukti konkret yang teramati di dalam kelas..." 
                                                                          class="w-full text-sm border-neutral-300 rounded-lg focus:border-navy-700 focus:ring focus:ring-orange-500/20 disabled:bg-neutral-50 disabled:text-neutral-500"></textarea>
                                                            </div>
                                                        @endif

                                                        {{-- Catatan / Umpan Balik Supervisor --}}
                                                        <div class="pt-0.5">
                                                            <label class="block text-xs font-medium text-muted mb-1">
                                                                {{ $penilaian->jenisInstrumen?->kode === 'supervisi_perangkat' ? 'Keterangan:' : 'Catatan / Umpan Balik Supervisor:' }}
                                                            </label>
                                                            <input type="text" 
                                                                   wire:model.blur="catatanButir.{{ $butir->id }}"
                                                                   {{ $penilaian->isFinal() ? 'disabled' : '' }}
                                                                   placeholder="{{ $penilaian->jenisInstrumen?->kode === 'supervisi_perangkat' ? 'Tuliskan keterangan dokumen (opsional)...' : 'Tuliskan umpan balik atau saran perbaikan...' }}" 
                                                                   class="w-full text-sm border-neutral-300 rounded-lg focus:border-navy-700 focus:ring focus:ring-orange-500/20 disabled:bg-neutral-50 disabled:text-neutral-500">
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>

                                            {{-- Sisi Kanan: Poin-poin Penilaian / Skala Skor --}}
                                            <div class="md:col-span-5 lg:col-span-4 bg-neutral-50/70 p-3.5 sm:p-4 rounded-xl border border-neutral-200/80 space-y-3">
                                                @if ($skorMaksButir === 2)
                                                    @php
                                                        $labelSkorList = [
                                                            0 => 'Tidak Ada',
                                                            1 => 'Kurang',
                                                            2 => 'Lengkap',
                                                        ];
                                                    @endphp
                                                    <div class="flex items-center justify-between">
                                                        <span class="text-xs font-bold uppercase tracking-wider text-navy-900">Poin Skor (0 - 2):</span>
                                                        @if ($currentSkor !== null)
                                                            <span class="text-xs font-bold px-2 py-0.5 rounded-full bg-emerald-100 text-emerald-800 border border-emerald-300">
                                                                Skor: {{ $currentSkor }} ({{ $labelSkorList[$currentSkor] ?? '' }})
                                                            </span>
                                                        @else
                                                            <span class="text-xs font-medium px-2 py-0.5 rounded-full bg-neutral-200 text-neutral-600">
                                                                Belum Dinilai
                                                            </span>
                                                        @endif
                                                    </div>

                                                    <div class="grid grid-cols-3 gap-2">
                                                        @for ($s = 0; $s <= 2; $s++)
                                                            @php
                                                                $isSelected = $currentSkor === $s;
                                                            @endphp
                                                            <button
                                                                type="button"
                                                                wire:key="butir-{{ $butir->id }}-skor-{{ $s }}"
                                                                @if(! $penilaian->isFinal()) wire:click="setSkor({{ $butir->id }}, {{ $s }})" @endif
                                                                class="relative flex flex-col items-center justify-center p-2 rounded-xl border min-h-[48px] select-none transition w-full {{ $isSelected ? 'bg-navy-900 border-navy-900 text-white font-bold ring-2 ring-orange-500 shadow-sm' : 'bg-white border-neutral-300 text-ink hover:bg-navy-50 hover:border-navy-400' }} {{ $penilaian->isFinal() ? 'cursor-not-allowed opacity-80' : 'cursor-pointer active:scale-95' }}"
                                                                {{ $penilaian->isFinal() ? 'disabled' : '' }}
                                                                aria-label="Skor {{ $s }} - {{ $labelSkorList[$s] }}"
                                                            >
                                                                <span class="block text-base font-extrabold leading-tight">{{ $s }}</span>
                                                                <span class="block text-[11px] {{ $isSelected ? 'text-amber-200' : 'text-muted' }} leading-tight mt-0.5 font-medium truncate max-w-full">
                                                                    {{ $labelSkorList[$s] }}
                                                                </span>
                                                            </button>
                                                        @endfor
                                                    </div>
                                                @else
                                                    @php
                                                        $labelSkorList = [
                                                            1 => 'Sangat Kurang',
                                                            2 => 'Kurang',
                                                            3 => 'Baik',
                                                            4 => 'Sangat Baik',
                                                        ];
                                                        $shortLabelList = [
                                                            1 => 'S. Kurang',
                                                            2 => 'Kurang',
                                                            3 => 'Baik',
                                                            4 => 'S. Baik',
                                                        ];
                                                    @endphp
                                                    <div class="flex items-center justify-between">
                                                        <span class="text-xs font-bold uppercase tracking-wider text-navy-900">Poin Skor (1 - 4):</span>
                                                        @if ($currentSkor !== null)
                                                            <span class="text-xs font-bold px-2 py-0.5 rounded-full bg-emerald-100 text-emerald-800 border border-emerald-300">
                                                                Skor: {{ $currentSkor }} ({{ $labelSkorList[$currentSkor] ?? '' }})
                                                            </span>
                                                        @else
                                                            <span class="text-xs font-medium px-2 py-0.5 rounded-full bg-neutral-200 text-neutral-600">
                                                                Belum Dinilai
                                                            </span>
                                                        @endif
                                                    </div>

                                                    <div class="grid grid-cols-4 gap-2">
                                                        @for ($s = 1; $s <= 4; $s++)
                                                            @php
                                                                $isSelected = $currentSkor === $s;
                                                            @endphp
                                                            <button
                                                                type="button"
                                                                wire:key="butir-{{ $butir->id }}-skor-{{ $s }}"
                                                                @if(! $penilaian->isFinal()) wire:click="setSkor({{ $butir->id }}, {{ $s }})" @endif
                                                                class="relative flex flex-col items-center justify-center p-2 rounded-xl border min-h-[48px] select-none transition w-full {{ $isSelected ? 'bg-navy-900 border-navy-900 text-white font-bold ring-2 ring-orange-500 shadow-sm' : 'bg-white border-neutral-300 text-ink hover:bg-navy-50 hover:border-navy-400' }} {{ $penilaian->isFinal() ? 'cursor-not-allowed opacity-80' : 'cursor-pointer active:scale-95' }}"
                                                                {{ $penilaian->isFinal() ? 'disabled' : '' }}
                                                                aria-label="Skor {{ $s }} - {{ $labelSkorList[$s] }}"
                                                                title="Skor {{ $s }}: {{ $labelSkorList[$s] }}"
                                                            >
                                                                <span class="block text-base font-extrabold leading-tight">{{ $s }}</span>
                                                                <span class="block text-[11px] {{ $isSelected ? 'text-amber-200' : 'text-muted' }} leading-tight mt-0.5 font-medium truncate max-w-full">
                                                                    {{ $shortLabelList[$s] }}
                                                                </span>
                                                            </button>
                                                        @endfor
                                                    </div>
                                                @endif
                                            </div>
                                        </div>
                                    </div>

                                {{-- B. JIKA BUTIR KUALITATIF / DIALOGIS / MEMBUTUHKAN INPUT TEKS (TETAP SEPERTI SEMULA) --}}
                                @else
                                    <div class="pt-6 first:pt-0" wire:key="butir-item-{{ $butir->id }}">
                                        <div class="flex items-start gap-4">
                                            <span class="w-7 h-7 rounded-full bg-neutral-100 text-navy-900 font-semibold text-sm flex items-center justify-center flex-shrink-0 mt-0.5">
                                                {{ $butir->urutan }}
                                            </span>
                                            <div class="flex-1 space-y-4">
                                                <div class="space-y-2.5">
                                                    <h4 class="text-ink text-[16.5px] leading-relaxed font-semibold">
                                                        {{ $pertanyaan }}
                                                    </h4>

                                                    @if ($indikatorTeks)
                                                        <div class="p-4 bg-amber-50/70 border border-amber-200/80 rounded-xl">
                                                            <div class="flex items-center gap-2 mb-2 text-amber-900 font-bold text-xs uppercase tracking-wider">
                                                                <svg class="w-4 h-4 text-amber-700 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-6 9l2 2 4-4" />
                                                                </svg>
                                                                <span>Indikator / Fokus Pengamatan Operasional:</span>
                                                            </div>
                                                            <div class="text-[14.5px] leading-relaxed whitespace-pre-line text-slate-800 font-normal pl-6 border-l-2 border-amber-300">
                                                                {{ $indikatorTeks }}
                                                            </div>
                                                        </div>
                                                    @endif
                                                </div>

                                                @if ($penilaian->isDiisiMandiriGuru())
                                                    <div class="space-y-2">
                                                        <div class="flex items-center justify-between">
                                                            <label class="text-xs font-bold text-navy-900 flex items-center gap-1.5">
                                                                <svg class="w-4 h-4 text-emerald-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/>
                                                                </svg>
                                                                <span>Isian / Refleksi Mandiri Guru:</span>
                                                            </label>
                                                            @if (!empty($catatanButir[$butir->id]))
                                                                <span class="text-[11px] font-semibold text-emerald-800 bg-emerald-50 px-2.5 py-0.5 rounded-full border border-emerald-200">
                                                                    Terisi oleh Guru
                                                                </span>
                                                            @else
                                                                <span class="text-[11px] font-medium text-amber-800 bg-amber-50 px-2.5 py-0.5 rounded-full border border-amber-200">
                                                                    Belum diisi oleh Guru
                                                                </span>
                                                            @endif
                                                        </div>

                                                        @if (!empty($catatanButir[$butir->id]))
                                                            <div class="p-4 rounded-xl bg-slate-50 border border-slate-200 text-ink text-[15px] leading-relaxed whitespace-pre-line shadow-2xs">
                                                                {{ $catatanButir[$butir->id] }}
                                                            </div>
                                                        @else
                                                            <div class="p-4 rounded-xl bg-neutral-50 border border-dashed border-neutral-300 text-muted text-sm italic">
                                                                Guru belum melengkapi jawaban atau refleksi untuk butir aspek ini.
                                                            </div>
                                                        @endif
                                                    </div>
                                                @else
                                                    <div>
                                                        <label class="block text-xs font-semibold text-navy-900 mb-1.5">
                                                            Catatan / Uraian Telaah:
                                                        </label>
                                                        <textarea wire:model.blur="catatanButir.{{ $butir->id }}"
                                                                  rows="3"
                                                                  {{ $penilaian->isFinal() ? 'disabled' : '' }}
                                                                  placeholder="Tuliskan catatan, analisis, kelebihan, atau rekomendasi..." 
                                                                  class="w-full text-[15px] border-neutral-300 rounded-lg focus:border-navy-700 focus:ring focus:ring-orange-500/20 disabled:bg-neutral-50 disabled:text-neutral-500"></textarea>
                                                    </div>
                                                @endif
                                            </div>
                                        </div>
                                    </div>
                                @endif
                            @endforeach
                        </div>
                    </div>
                @endforeach
            @endif
        </div>
    @endif

    @if ($penilaian->isKuantitatif())
        {{-- Rekapitulasi Perhitungan Nilai Akhir Kuantitatif --}}
        <div class="bg-white rounded-xl shadow-sm border border-neutral-200 p-6 space-y-4">
            <div class="flex flex-col sm:flex-row sm:items-center justify-between pb-3 border-b border-neutral-100 gap-2">
                <div>
                    <h3 class="text-lg font-bold text-navy-900 flex items-center gap-2">
                        <svg class="w-5 h-5 text-navy-700" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 7h6m0 10v-3m-3 3h.01M9 17h.01M9 14h.01M12 14h.01M15 11h.01M12 11h.01M9 11h.01M7 21h10a2 2 0 002-2V5a2 2 0 00-2-2H7a2 2 0 00-2 2v14a2 2 0 002 2z"/>
                        </svg>
                        <span>Perhitungan Nilai Akhir Instrumen</span>
                    </h3>
                    <p class="text-xs text-muted mt-0.5">Nilai akhir dihitung secara otomatis dengan rumus: <strong>(Total Skor Perolehan / Total Skor Maksimal) &times; 100</strong>.</p>
                </div>
                <div class="text-left sm:text-right">
                    <span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-semibold {{ $penilaian->isFinal() ? 'bg-emerald-100 text-emerald-800' : 'bg-navy-50 text-navy-800' }}">
                        {{ $penilaian->isFinal() ? 'Nilai Resmi (Final)' : 'Kalkulasi Real-time' }}
                    </span>
                </div>
            </div>

            <div class="grid grid-cols-2 sm:grid-cols-4 gap-4 text-center">
                <div class="bg-navy-50/50 rounded-xl p-4 border border-navy-100">
                    <span class="block text-xs uppercase font-semibold text-muted">Skor Perolehan</span>
                    <span class="text-2xl font-extrabold text-navy-900 mt-1 block">
                        {{ $penilaian->isFinal() ? $penilaian->total_skor : $this->skorPerolehan }}
                    </span>
                    <span class="text-[11px] text-muted">dari {{ $this->jumlahTerisi }} butir terisi</span>
                </div>

                <div class="bg-navy-50/50 rounded-xl p-4 border border-navy-100">
                    <span class="block text-xs uppercase font-semibold text-muted">Skor Maksimal</span>
                    <span class="text-2xl font-extrabold text-navy-900 mt-1 block">
                        {{ $penilaian->isFinal() ? $penilaian->skor_maks : $this->skorMaksimal }}
                    </span>
                    <span class="text-[11px] text-muted">dari {{ $this->totalButirKuantitatif }} butir penilaian</span>
                </div>

                <div class="bg-emerald-50/60 rounded-xl p-4 border border-emerald-200">
                    <span class="block text-xs uppercase font-semibold text-emerald-800">Nilai Akhir (0 - 100)</span>
                    <span class="text-2xl font-extrabold text-emerald-700 mt-1 block">
                        {{ number_format($penilaian->isFinal() ? $penilaian->nilai : $this->nilaiAkhirHitungan, 2) }}
                    </span>
                    <span class="text-[11px] text-emerald-700 font-medium">({{ $penilaian->isFinal() ? $penilaian->total_skor : $this->skorPerolehan }} / {{ $penilaian->isFinal() ? $penilaian->skor_maks : $this->skorMaksimal }}) &times; 100</span>
                </div>

                <div class="bg-emerald-50/60 rounded-xl p-4 border border-emerald-200">
                    <span class="block text-xs uppercase font-semibold text-emerald-800">Predikat Capaian</span>
                    <span class="text-2xl font-extrabold text-emerald-800 mt-1 block">
                        {{ $penilaian->isFinal() ? $penilaian->predikat : $this->estimasiPredikat }}
                    </span>
                    <span class="text-[11px] text-emerald-700">
                        @php
                            $pred = $penilaian->isFinal() ? $penilaian->predikat : $this->estimasiPredikat;
                            $keteranganPredikat = match($pred) {
                                'A' => 'Sangat Baik',
                                'B' => 'Baik',
                                'C' => 'Cukup',
                                default => 'Kurang',
                            };
                        @endphp
                        {{ $keteranganPredikat }}
                    </span>
                </div>
            </div>
        </div>
    @endif

    {{-- Kolom Catatan Umum dan Tindak Lanjut --}}
    <div class="bg-white rounded-xl shadow-sm border {{ $penilaian->isDiisiMandiriGuru() ? 'border-navy-300 ring-2 ring-navy-100' : 'border-neutral-200' }} p-6 space-y-5">
        <div class="border-b border-neutral-100 pb-3 flex flex-col sm:flex-row sm:items-center justify-between gap-2">
            <div>
                <h3 class="text-lg font-bold text-navy-900 flex items-center gap-2">
                    <svg class="w-5 h-5 text-navy-700" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/>
                    </svg>
                    <span>Kesimpulan, Rekomendasi & Tindak Lanjut Supervisor</span>
                </h3>
                @if ($penilaian->isDiisiMandiriGuru())
                    <p class="text-xs text-muted mt-0.5">Dua isian utama yang wajib dilengkapi oleh supervisor sebelum memfinalisasi lembar ini.</p>
                @endif
            </div>
            @if ($penilaian->isDiisiMandiriGuru())
                <span class="px-2.5 py-1 rounded-full text-xs font-bold bg-navy-50 text-navy-800 border border-navy-200 self-start sm:self-auto">
                    Wajib Diisi Supervisor
                </span>
            @endif
        </div>
        
        <div>
            <label class="block text-sm font-semibold text-ink mb-1.5 flex items-center gap-1.5">
                <span>Catatan Umum / Rekomendasi Supervisor</span>
                @if ($penilaian->isDiisiMandiriGuru())
                    <span class="text-rose-600 font-bold">*</span>
                @endif
            </label>
            <textarea wire:model="catatan" 
                      rows="3" 
                      {{ $penilaian->isFinal() ? 'disabled' : '' }}
                      placeholder="Tuliskan catatan apresiasi, analisis menyeluruh, atau rekomendasi perbaikan untuk guru..."
                      class="w-full text-[15px] border-neutral-300 rounded-lg focus:border-navy-700 focus:ring focus:ring-orange-500/20 disabled:bg-neutral-50 disabled:text-neutral-500"></textarea>
        </div>

        <div>
            <label class="block text-sm font-semibold text-ink mb-1.5 flex items-center gap-1.5">
                <span>Rencana Tindak Lanjut Pembinaan</span>
                @if ($penilaian->isDiisiMandiriGuru())
                    <span class="text-rose-600 font-bold">*</span>
                @endif
            </label>
            <textarea wire:model="tindakLanjut" 
                      rows="3" 
                      {{ $penilaian->isFinal() ? 'disabled' : '' }}
                      placeholder="Tuliskan langkah konkret tindak lanjut pembinaan yang akan dilaksanakan berikutnya..."
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
                <span>Draft dapat disimpan berkala. Pastikan data lengkap sebelum melakukan finalisasi.</span>
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
                @if ($penilaian->isDiisiMandiriGuru() && ! $penilaian->isGuruSudahSubmit())
                    <button type="button" 
                            disabled
                            class="px-6 py-3 rounded-xl font-bold text-neutral-400 bg-neutral-200 cursor-not-allowed shadow-none min-h-[44px] flex items-center justify-center gap-2"
                            title="Menunggu penyerahan isian mandiri dari Guru">
                        <svg class="w-5 h-5 text-neutral-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"/>
                        </svg>
                        <span>Menunggu Isian Guru</span>
                    </button>
                @else
                    <button type="button" 
                            @click="showFinalDialog = true"
                            class="px-6 py-3 rounded-xl font-bold text-white bg-navy-900 hover:bg-navy-800 active:scale-[0.98] transition shadow-sm min-h-[44px] flex items-center justify-center gap-2">
                        Finalisasi
                    </button>
                @endif
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
                            <h4 class="text-xl font-bold text-navy-900">Konfirmasi Finalisasi Dokumen</h4>
                            <p class="text-sm text-muted mt-1">Setelah difinalisasi, dokumen akan resmi terkunci dan tidak dapat diubah lagi kecuali dibuka kuncinya oleh Administrator. Lanjutkan?</p>
                        </div>
                    </div>

                    @if ($penilaian->isKuantitatif() && $this->jumlahTerisi < $this->totalButir)
                        <div class="p-3 bg-rose-50 border border-rose-200 rounded-lg text-rose-800 text-sm">
                            <strong>Perhatian:</strong> Masih ada {{ $this->totalButir - $this->jumlahTerisi }} butir yang belum diberi skor. Semua butir wajib diisi sebelum finalisasi.
                        </div>
                    @endif

                    @if ($penilaian->isKuantitatif())
                        <div class="p-4 bg-navy-50/70 border border-navy-200 rounded-xl space-y-2 text-sm">
                            <div class="flex items-center justify-between text-muted">
                                <span>Skor Perolehan:</span>
                                <strong class="text-navy-900">{{ $this->skorPerolehan }} dari {{ $this->skorMaksimal }}</strong>
                            </div>
                            <div class="flex items-center justify-between text-muted text-xs">
                                <span>Rumus Perhitungan:</span>
                                <code>({{ $this->skorPerolehan }} / {{ $this->skorMaksimal }}) &times; 100</code>
                            </div>
                            <div class="flex items-center justify-between text-base font-bold text-emerald-800 pt-2 border-t border-navy-200">
                                <span>Nilai Akhir & Predikat:</span>
                                <span>{{ number_format($this->nilaiAkhirHitungan, 2) }} (Predikat {{ $this->estimasiPredikat }})</span>
                            </div>
                        </div>
                    @endif

                    @if ($penilaian->isDiisiMandiriGuru() && ! $penilaian->isGuruSudahSubmit())
                        <div class="p-3 bg-amber-50 border border-amber-200 rounded-lg text-amber-900 text-sm">
                            <strong>Perhatian:</strong> Guru belum menyelesaikan dan menyerahkan isian mandirinya ke supervisor. Finalisasi belum dapat dilakukan.
                        </div>
                    @endif

                    @if ($penilaian->isDiisiMandiriGuru() && (empty(trim((string) $catatan)) || empty(trim((string) $tindakLanjut))))
                        <div class="p-3 bg-rose-50 border border-rose-200 rounded-lg text-rose-800 text-sm">
                            <strong>Perhatian:</strong> Kolom Catatan Umum / Rekomendasi Supervisor dan Rencana Tindak Lanjut Pembinaan wajib diisi sebelum finalisasi.
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
                                {{ ($penilaian->isDiisiMandiriGuru() && ! $penilaian->isGuruSudahSubmit()) ? 'disabled' : '' }}
                                class="px-5 py-2.5 rounded-xl font-bold text-white bg-navy-900 hover:bg-navy-800 transition min-h-[44px] flex items-center gap-2 disabled:bg-neutral-300 disabled:text-neutral-500 disabled:cursor-not-allowed">
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
                &larr; Kembali ke Detail Supervisi Guru
            </a>
        </div>
    @endif
</div>
