<x-app-layout :namaSekolah="$sekolah->nama" title="Dasbor Supervisor">
    <x-page-header
        title="Dasbor Supervisor"
        subtitle="Daftar guru yang ditugaskan kepada Anda pada periode supervisi aktif."
    >
        @if($periode && $daftarPenugasan->isNotEmpty())
            <x-slot:actions>
                <a href="{{ route('supervisor.laporan.export.rekap', ['kode' => $sekolah->kode, 'periode_id' => $periode->id]) }}"
                   class="px-4 py-2.5 rounded-xl font-bold text-white bg-emerald-700 hover:bg-emerald-800 transition min-h-[44px] flex items-center gap-2 shadow-sm text-sm">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                    </svg>
                    <span>Unduh Rekap Bimbingan (.xlsx)</span>
                </a>
            </x-slot:actions>
        @endif
    </x-page-header>

    @if(!$periode)
        <x-card class="p-8 text-center">
            <x-empty-state
                title="Tidak Ada Periode Aktif"
                description="Saat ini belum ada periode supervisi yang berstatus aktif di sekolah Anda. Penugasan akan muncul otomatis setelah admin mengaktifkan periode."
            />
        </x-card>
    @else
        <div class="mb-6 bg-white rounded-xl shadow-sm border border-neutral-200 p-6 flex flex-col sm:flex-row items-start sm:items-center justify-between gap-4">
            <div>
                <div class="flex items-center gap-3">
                    <h2 class="text-xl font-bold text-navy-900">{{ $periode->nama }}</h2>
                    <span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-semibold bg-emerald-100 text-emerald-800">
                        Periode Aktif
                    </span>
                </div>
                <p class="text-sm text-muted mt-1">
                    Tahun Ajaran: {{ $periode->tahun_ajaran }} &bull; Semester: {{ $periode->semester ?: '-' }} &bull; Rentang: {{ $periode->tanggal_mulai->format('d/m/Y') }} &ndash; {{ $periode->tanggal_selesai->format('d/m/Y') }}
                </p>
            </div>
            <div class="text-left sm:text-right">
                <span class="text-xs font-semibold uppercase text-muted tracking-wider block">Total Guru Disupervisi</span>
                <span class="text-2xl font-bold text-navy-900">{{ $daftarPenugasan->count() }} Guru</span>
            </div>
        </div>

        {{-- Kartu Rekaman Supervisi Diri Sendiri (Sebagai Guru yang Disupervisi) --}}
        @if(isset($penugasanSebagaiGuru) && $penugasanSebagaiGuru)
            @php
                $selesaiDiri = $penugasanSebagaiGuru->penilaian->where('status', 'final')->count();
                $persenDiri = round(($selesaiDiri / 4) * 100);
            @endphp
            <div class="mb-6 bg-white rounded-2xl shadow-sm border-2 border-navy-100 p-6 relative overflow-hidden">
                <div class="flex flex-col md:flex-row md:items-center justify-between gap-4 border-b border-neutral-100 pb-4">
                    <div>
                        <div class="flex items-center gap-2.5">
                            <span class="w-8 h-8 rounded-lg bg-orange-500 text-white flex items-center justify-center font-bold text-sm">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" />
                                </svg>
                            </span>
                            <div>
                                <h3 class="text-lg font-bold text-navy-900">Rekaman Supervisi Anda (Sebagai Guru)</h3>
                                <p class="text-xs text-muted">Penilai / Supervisor Anda: <strong class="text-navy-900">{{ $penugasanSebagaiGuru->penilai?->nama ?? 'Belum ditentukan' }}</strong></p>
                            </div>
                        </div>
                    </div>
                    <div class="flex items-center gap-3">
                        <span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-bold {{ $selesaiDiri === 4 ? 'bg-emerald-100 text-emerald-800' : 'bg-amber-100 text-amber-800' }}">
                            Progres Penilaian: {{ $selesaiDiri }}/4 Instrumen
                        </span>
                        <a href="{{ route('guru.rapor', ['kode' => $sekolah->kode]) }}" class="px-4 py-2 rounded-xl text-xs font-bold text-white bg-navy-900 hover:bg-navy-800 transition shadow-sm flex items-center gap-1.5 min-h-[38px]">
                            <span>Buka Rapor Saya</span>
                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
                            </svg>
                        </a>
                    </div>
                </div>

                {{-- Status 4 Instrumen Saya --}}
                <div class="grid grid-cols-2 sm:grid-cols-4 gap-3 mt-4">
                    @foreach($penugasanSebagaiGuru->penilaian as $penDiri)
                        @php
                            $namaInstrumenDiri = match($penDiri->jenisInstrumen->kode ?? '') {
                                'kbm' => 'Observasi KBM',
                                'administrasi' => 'Administrasi',
                                'pengelolaan_kelas' => 'Pengelolaan Kelas',
                                'perencanaan' => 'Perencanaan',
                                default => $penDiri->jenisInstrumen->nama ?? '-'
                            };
                        @endphp
                        <div class="p-3 rounded-xl border {{ $penDiri->isFinal() ? 'bg-emerald-50/60 border-emerald-200' : ($penDiri->isDraft() ? 'bg-amber-50/60 border-amber-200' : 'bg-neutral-50 border-neutral-200') }}">
                            <p class="text-xs font-semibold text-navy-900 truncate">{{ $namaInstrumenDiri }}</p>
                            <p class="text-xs mt-1">
                                @if($penDiri->isFinal())
                                    <span class="font-bold text-emerald-700">{{ number_format($penDiri->nilai, 2) }}</span> <span class="text-[11px] text-emerald-600 font-medium">({{ $penDiri->predikat }})</span>
                                @elseif($penDiri->isDraft())
                                    <span class="text-amber-700 font-medium">Sedang dinilai</span>
                                @elseif($penDiri->isDirevisi())
                                    <span class="text-orange-700 font-medium">Sedang direvisi</span>
                                @else
                                    <span class="text-neutral-500">Belum dinilai</span>
                                @endif
                            </p>
                        </div>
                    @endforeach
                </div>
            </div>
        @elseif(auth()->user()?->hasRole('guru'))
            <div class="mb-6 bg-navy-50/70 border border-navy-200 rounded-2xl p-5 flex flex-col sm:flex-row sm:items-center justify-between gap-4">
                <div class="flex items-center gap-3">
                    <span class="w-9 h-9 rounded-xl bg-navy-900 text-white flex items-center justify-center flex-shrink-0">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253" />
                        </svg>
                    </span>
                    <div>
                        <h3 class="font-bold text-navy-900 text-base">Modul Supervisi Saya (Sebagai Guru)</h3>
                        <p class="text-xs text-muted">Akses informasi jadwal, rapor penilaian, dan riwayat supervisi Anda.</p>
                    </div>
                </div>
                <div class="flex items-center gap-2">
                    <a href="{{ route('guru.dashboard', ['kode' => $sekolah->kode]) }}" class="px-4 py-2 rounded-xl text-xs font-bold text-navy-900 bg-white border border-neutral-300 hover:bg-neutral-50 transition min-h-[38px] flex items-center">
                        Buka Dasbor Guru
                    </a>
                    <a href="{{ route('guru.rapor', ['kode' => $sekolah->kode]) }}" class="px-4 py-2 rounded-xl text-xs font-bold text-white bg-navy-900 hover:bg-navy-800 transition min-h-[38px] flex items-center">
                        Buka Rapor Saya
                    </a>
                </div>
            </div>
        @endif

        <div class="mb-4 flex items-center justify-between">
            <h3 class="text-lg font-bold text-navy-900">Daftar Guru Binaan Anda</h3>
            <span class="text-xs text-muted">Klik tombol untuk mulai atau melanjutkan penilaian instrumen</span>
        </div>

        @if($daftarPenugasan->isEmpty())
            <x-card class="p-8 text-center">
                <x-empty-state
                    title="Belum Ada Guru Ditugaskan"
                    description="Anda belum memiliki daftar guru binaan untuk disupervisi pada periode aktif ini. Hubungi administrator sekolah bila ada penugasan yang perlu disesuaikan."
                />
            </x-card>
        @else
            <div class="space-y-4">
                <div class="grid grid-cols-1 gap-4">
                    @foreach($daftarPenugasan as $item)
                        @php
                            $selesaiCount = $item->penilaian->where('status', 'final')->count();
                            $persenSelesai = round(($selesaiCount / 4) * 100);
                        @endphp
                        <div class="bg-white rounded-xl shadow-sm border border-neutral-200 hover:border-navy-300 p-6 transition flex flex-col lg:flex-row lg:items-center justify-between gap-6">
                            {{-- Info Guru & Progres --}}
                            <div class="space-y-3 flex-1">
                                <div class="flex flex-col sm:flex-row sm:items-center gap-2 sm:gap-4">
                                    <h3 class="text-xl font-bold text-navy-900">
                                        {{ $item->guru->nama }}
                                    </h3>
                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-bold {{ $selesaiCount === 4 ? 'bg-emerald-100 text-emerald-800' : 'bg-navy-50 text-navy-900' }}">
                                        Progres: {{ $selesaiCount }}/4 Instrumen
                                    </span>
                                </div>
                                <div class="flex flex-wrap items-center gap-y-1 gap-x-3 text-sm text-muted">
                                    <span>NIP: {{ $item->guru->nip ?: '-' }} &bull; NUPTK: {{ $item->guru->nuptk ?: '-' }}</span>
                                    @if($item->infoSupervisi && $item->infoSupervisi->mata_pelajaran)
                                        <span class="text-xs font-semibold text-navy-900 bg-navy-50 px-2 py-0.5 rounded border border-navy-100">
                                            {{ $item->infoSupervisi->mata_pelajaran }} ({{ $item->infoSupervisi->kelas ?: '-' }})
                                        </span>
                                    @else
                                        <span class="text-xs font-semibold text-amber-800 bg-amber-50 px-2 py-0.5 rounded border border-amber-200">
                                            ⚠️ Belum isi jadwal & info pembelajaran
                                        </span>
                                    @endif
                                </div>

                                {{-- Progress bar --}}
                                <div class="w-full max-w-md bg-neutral-100 rounded-full h-2.5 overflow-hidden">
                                    <div class="bg-orange-500 h-2.5 rounded-full transition-all duration-300" style="width: {{ $persenSelesai }}%"></div>
                                </div>

                                {{-- Badge Status 4 Instrumen --}}
                                <div class="flex flex-wrap gap-2 pt-1">
                                    @foreach($item->penilaian as $pen)
                                        @php
                                            $namaInstrumen = match($pen->jenisInstrumen->kode ?? '') {
                                                'kbm' => 'KBM',
                                                'administrasi' => 'Administrasi',
                                                'pengelolaan_kelas' => 'Pengelolaan Kelas',
                                                'perencanaan' => 'Perencanaan',
                                                default => $pen->jenisInstrumen->nama ?? '-'
                                            };
                                        @endphp
                                        <div class="inline-flex items-center gap-1.5 px-3 py-1 rounded-lg text-xs font-medium border
                                            {{ $pen->isFinal() ? 'bg-emerald-50 text-emerald-800 border-emerald-200' : '' }}
                                            {{ $pen->isDraft() ? 'bg-amber-50 text-amber-800 border-amber-200' : '' }}
                                            {{ $pen->isDirevisi() ? 'bg-orange-50 text-orange-800 border-orange-200' : '' }}
                                            {{ $pen->isBelum() ? 'bg-neutral-50 text-neutral-600 border-neutral-200' : '' }}">
                                            <span class="font-semibold">{{ $namaInstrumen }}:</span>
                                            <span>
                                                @if($pen->isFinal()) Final
                                                @elseif($pen->isDraft()) Draft
                                                @elseif($pen->isDirevisi()) Direvisi
                                                @else Belum Dinilai
                                                @endif
                                            </span>
                                        </div>
                                    @endforeach
                                </div>
                            </div>

                            {{-- Tombol Aksi Detail --}}
                            <div class="flex items-center justify-end">
                                <a href="{{ route('supervisor.penugasan.show', ['kode' => $sekolah->kode, 'penugasan' => $item->id]) }}" 
                                   class="px-5 py-2.5 rounded-xl font-bold text-white bg-navy-900 hover:bg-navy-800 active:scale-[0.98] transition shadow-sm min-h-[44px] flex items-center gap-2">
                                    <span>Buka Lembar Penilaian</span>
                                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
                                    </svg>
                                </a>
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>
        @endif
    @endif
</x-app-layout>
