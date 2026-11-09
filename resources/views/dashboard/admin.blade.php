<x-app-layout :namaSekolah="$sekolah->nama" title="Dasbor Admin">
    <x-page-header
        title="Dasbor Admin"
        subtitle="Ringkasan statistik dan progres pelaksanaan supervisi guru di {{ $sekolah->nama }}."
    >
        @if($periode)
            <x-slot:actions>
                <div class="flex items-center gap-3 flex-wrap">
                    @if($semuaPeriode->count() > 1)
                        <form method="GET" action="{{ route('dashboard.admin', ['kode' => $sekolah->kode]) }}" class="flex items-center gap-2">
                            <label for="periode_switcher" class="text-xs font-semibold text-gray-500 whitespace-nowrap">Periode:</label>
                            <select name="periode_id" id="periode_switcher" class="form-input text-xs py-1.5 rounded-lg border-gray-300" onchange="this.form.submit()">
                                @foreach($semuaPeriode as $p)
                                    <option value="{{ $p->id }}" {{ $periode->id === $p->id ? 'selected' : '' }}>
                                        {{ $p->nama }} ({{ ucfirst($p->status) }})
                                    </option>
                                @endforeach
                            </select>
                        </form>
                    @endif

                    <span class="inline-flex items-center gap-1.5 text-xs font-semibold px-3 py-1.5 rounded-full border bg-white" style="border-color: var(--color-border); color: var(--color-navy-900);">
                        <span class="w-2 h-2 rounded-full {{ $periode->isAktif() ? 'bg-emerald-500' : 'bg-amber-500' }}"></span>
                        <span>{{ $periode->nama }} &bull; {{ $periode->tanggal_mulai->format('d/m/Y') }} &ndash; {{ $periode->tanggal_selesai->format('d/m/Y') }}</span>
                    </span>
                </div>
            </x-slot:actions>
        @endif
    </x-page-header>

    {{-- 1. RINGKASAN UTAMA (4 KPI STAT CARDS) --}}
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-5 mb-8">
        {{-- Card 1: Guru Aktif --}}
        <x-card class="p-5 hover:border-slate-300 transition-all">
            <div class="flex items-center gap-4">
                <div class="w-12 h-12 rounded-2xl flex items-center justify-center text-white shadow-2xs shrink-0"
                     style="background-color: var(--color-navy-900);">
                    <svg xmlns="http://www.w3.org/2000/svg" width="22" height="22" viewBox="0 0 20 20" fill="currentColor">
                        <path d="M9 6a3 3 0 11-6 0 3 3 0 016 0zM17 6a3 3 0 11-6 0 3 3 0 016 0zM12.93 17c.046-.327.07-.66.07-1a6.97 6.97 0 00-1.5-4.33A5 5 0 0119 16v1h-6.07zM6 11a5 5 0 015 5v1H1v-1a5 5 0 015-5z"/>
                    </svg>
                </div>
                <div>
                    <p class="text-xs font-semibold uppercase tracking-wider text-[#8b92a1]">Guru Aktif</p>
                    <p class="text-2xl font-bold text-[#0d0d0d] mt-0.5">{{ $totalGuruAktif }}</p>
                    <p class="text-xs text-gray-500 mt-0.5">Guru terdaftar</p>
                </div>
            </div>
        </x-card>

        {{-- Card 2: Supervisor Penilai --}}
        <x-card class="p-5 hover:border-slate-300 transition-all">
            <div class="flex items-center gap-4">
                <div class="w-12 h-12 rounded-2xl flex items-center justify-center text-white shadow-2xs shrink-0"
                     style="background-color: var(--color-action-green);">
                    <svg xmlns="http://www.w3.org/2000/svg" width="22" height="22" viewBox="0 0 20 20" fill="currentColor">
                        <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/>
                    </svg>
                </div>
                <div>
                    <p class="text-xs font-semibold uppercase tracking-wider text-[#8b92a1]">Supervisor Penilai</p>
                    <p class="text-2xl font-bold text-[#0d0d0d] mt-0.5">{{ $totalSupervisorAktif }}</p>
                    <p class="text-xs text-gray-500 mt-0.5">Penilai siap bertugas</p>
                </div>
            </div>
        </x-card>

        {{-- Card 3: Sudah Ditugaskan --}}
        <x-card class="p-5 hover:border-slate-300 transition-all">
            <div class="flex items-center gap-4">
                <div class="w-12 h-12 rounded-2xl flex items-center justify-center text-white shadow-2xs shrink-0"
                     style="background-color: var(--color-navy-700);">
                    <svg xmlns="http://www.w3.org/2000/svg" width="22" height="22" viewBox="0 0 20 20" fill="currentColor">
                        <path d="M9 2a1 1 0 000 2h2a1 1 0 100-2H9z"/>
                        <path fill-rule="evenodd" d="M4 5a2 2 0 012-2 3 3 0 003 3h2a3 3 0 003-3 2 2 0 012 2v11a2 2 0 01-2 2H6a2 2 0 01-2-2V5zm9.707 5.707a1 1 0 00-1.414-1.414L9 12.586l-1.293-1.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/>
                    </svg>
                </div>
                <div>
                    <p class="text-xs font-semibold uppercase tracking-wider text-[#8b92a1]">Sudah Ditugaskan</p>
                    <p class="text-2xl font-bold text-[#0d0d0d] mt-0.5">{{ $totalPenugasan }}</p>
                    <p class="text-xs text-emerald-700 mt-0.5 font-semibold">{{ $persenPenugasan }}% dari total guru</p>
                </div>
            </div>
        </x-card>

        {{-- Card 4: Belum Punya Penilai --}}
        <x-card class="p-5 hover:border-slate-300 transition-all">
            <div class="flex items-center gap-4">
                <div class="w-12 h-12 rounded-2xl flex items-center justify-center text-white shadow-2xs shrink-0"
                     style="background-color: {{ $totalGuruBelum > 0 ? 'var(--color-orange-500)' : 'var(--color-action-green)' }};">
                    @if($totalGuruBelum > 0)
                        <svg xmlns="http://www.w3.org/2000/svg" width="22" height="22" viewBox="0 0 20 20" fill="currentColor">
                            <path fill-rule="evenodd" d="M8.257 3.099c.765-1.36 2.722-1.36 3.486 0l5.58 9.92c.75 1.334-.213 2.98-1.742 2.98H4.42c-1.53 0-2.493-1.646-1.743-2.98l5.58-9.92zM11 13a1 1 0 11-2 0 1 1 0 012 0zm-1-8a1 1 0 00-1 1v3a1 1 0 002 0V6a1 1 0 00-1-1z" clip-rule="evenodd"/>
                        </svg>
                    @else
                        <svg xmlns="http://www.w3.org/2000/svg" width="22" height="22" viewBox="0 0 20 20" fill="currentColor">
                            <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"/>
                        </svg>
                    @endif
                </div>
                <div>
                    <p class="text-xs font-semibold uppercase tracking-wider text-[#8b92a1]">Belum Punya Penilai</p>
                    <p class="text-2xl font-bold mt-0.5 {{ $totalGuruBelum > 0 ? 'text-amber-800' : 'text-emerald-800' }}">
                        {{ $totalGuruBelum }}
                    </p>
                    <p class="text-xs mt-0.5 {{ $totalGuruBelum > 0 ? 'text-amber-800 font-semibold' : 'text-gray-500' }}">
                        {{ $totalGuruBelum > 0 ? 'Perlu didaftarkan' : 'Semua telah terpetakan' }}
                    </p>
                </div>
            </div>
        </x-card>
    </div>

    {{-- 2. STATISTIK ANALISIS PROGRES SUPERVISI (2 KOLOM LEBAR) --}}
    @if(!$periode)
        <div class="mb-8">
            <x-card class="p-8 text-center">
                <x-empty-state
                    title="Belum Ada Periode Supervisi"
                    description="Buat periode supervisi terlebih dahulu untuk memulai pemetaan penugasan dan memantau statistik supervisi."
                >
                    <x-button variant="tambah" href="{{ route('admin.periode.index', ['kode' => $sekolah->kode]) }}" class="rounded-xl">
                        Buat Periode Supervisi &rarr;
                    </x-button>
                </x-empty-state>
            </x-card>
        </div>
    @else
        <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-8">
            {{-- Kartu A: Statistik Penugasan Guru --}}
            <x-card class="p-6 border flex flex-col justify-between" style="border-color: var(--color-border);">
                <div>
                    <div class="flex items-center justify-between pb-3 mb-4 border-b" style="border-color: var(--color-border);">
                        <div>
                            <h2 class="text-lg font-bold" style="color: var(--color-navy-900);">Progres Penugasan Guru</h2>
                            <p class="text-xs text-gray-500 mt-0.5">Pemetaan guru ke supervisor pada periode {{ $periode->nama }}</p>
                        </div>
                        <span class="text-2xl font-extrabold" style="color: var(--color-navy-900);">
                            {{ $persenPenugasan }}%
                        </span>
                    </div>

                    {{-- Progress Bar Penugasan --}}
                    <div class="w-full bg-gray-100 rounded-full h-3.5 mb-4 overflow-hidden border border-gray-200">
                        <div class="h-3.5 rounded-full transition-all duration-500"
                             style="width: {{ $persenPenugasan }}%; background-color: var(--color-navy-700);"></div>
                    </div>

                    {{-- Rincian Angka --}}
                    <div class="grid grid-cols-2 gap-4 p-4 rounded-xl bg-gray-50 border mb-4" style="border-color: var(--color-border);">
                        <div>
                            <p class="text-xs font-semibold text-gray-500 uppercase tracking-wider">Sudah Memiliki Penilai</p>
                            <p class="text-xl font-bold text-gray-900 mt-0.5">
                                {{ $totalPenugasan }} <span class="text-xs font-normal text-gray-500">/ {{ $totalGuruAktif }} guru</span>
                            </p>
                        </div>
                        <div>
                            <p class="text-xs font-semibold text-gray-500 uppercase tracking-wider">Belum Memiliki Penilai</p>
                            <p class="text-xl font-bold mt-0.5 {{ $totalGuruBelum > 0 ? 'text-amber-800' : 'text-emerald-700' }}">
                                {{ $totalGuruBelum }} <span class="text-xs font-normal text-gray-500">guru</span>
                            </p>
                        </div>
                    </div>
                </div>

                {{-- Aksi Cepat Penugasan --}}
                <div class="flex items-center justify-between gap-3 pt-3 border-t flex-wrap" style="border-color: var(--color-border);">
                    <x-button variant="primary" :href="route('admin.penugasan.index', ['kode' => $sekolah->kode, 'periode_id' => $periode->id])" class="text-xs py-2">
                        Buka Penugasan Supervisi &rarr;
                    </x-button>

                    @if($totalGuruBelum > 0)
                        <div class="flex items-center gap-2 flex-wrap">
                            <x-button variant="tambah" :href="route('admin.penugasan.daftarkan', ['kode' => $sekolah->kode, 'periode_id' => $periode->id])" class="text-xs py-2">
                                Daftarkan Guru &rarr;
                            </x-button>
                            <x-button variant="outline" :href="route('admin.penugasan.belum-dinilai', ['kode' => $sekolah->kode, 'periode_id' => $periode->id])" class="text-xs py-2">
                                Belum Punya Penilai ({{ $totalGuruBelum }}) &rarr;
                            </x-button>
                        </div>
                    @endif
                </div>
            </x-card>

            {{-- Kartu B: Statistik Ketuntasan Supervisi --}}
            <x-card class="p-6 border flex flex-col justify-between" style="border-color: var(--color-border);">
                <div>
                    <div class="flex items-center justify-between pb-3 mb-4 border-b" style="border-color: var(--color-border);">
                        <div>
                            <h2 class="text-lg font-bold" style="color: var(--color-navy-900);">Ketuntasan Supervisi</h2>
                            <p class="text-xs text-gray-500 mt-0.5">Penyelesaian seluruh instrumen supervisi (status final)</p>
                        </div>
                        <span class="text-2xl font-extrabold text-emerald-800">
                            {{ $persenKetuntasan }}%
                        </span>
                    </div>

                    {{-- Progress Bar Ketuntasan --}}
                    <div class="w-full bg-gray-100 rounded-full h-3.5 mb-4 overflow-hidden border border-gray-200">
                        <div class="h-3.5 rounded-full transition-all duration-500"
                             style="width: {{ $persenKetuntasan }}%; background-color: var(--color-action-green);"></div>
                    </div>

                    {{-- Rincian Angka --}}
                    <div class="grid grid-cols-2 gap-4 p-4 rounded-xl bg-gray-50 border mb-4" style="border-color: var(--color-border);">
                        <div>
                            <p class="text-xs font-semibold text-gray-500 uppercase tracking-wider">Supervisi Selesai</p>
                            <p class="text-xl font-bold text-emerald-800 mt-0.5">
                                {{ $totalSelesai }} <span class="text-xs font-normal text-gray-500">/ {{ $totalPenugasan }} guru</span>
                            </p>
                            <p class="text-[11px] text-gray-500 mt-0.5">Semua instrumen final</p>
                        </div>
                        <div>
                            <p class="text-xs font-semibold text-gray-500 uppercase tracking-wider">Sedang Berjalan</p>
                            <p class="text-xl font-bold text-gray-900 mt-0.5">
                                {{ $totalSedangBerjalan }} <span class="text-xs font-normal text-gray-500">guru</span>
                            </p>
                            <p class="text-[11px] text-gray-500 mt-0.5">Belum semua instrumen final</p>
                        </div>
                    </div>
                </div>

                {{-- Aksi Cepat Laporan --}}
                <div class="flex items-center justify-between gap-3 pt-3 border-t flex-wrap" style="border-color: var(--color-border);">
                    <x-button variant="outline" :href="route('admin.laporan.index', ['kode' => $sekolah->kode, 'periode_id' => $periode->id])" class="text-xs py-2">
                        Buka Rekapitulasi & Laporan &rarr;
                    </x-button>
                    <span class="text-xs text-gray-500">
                        Ketuntasan dihitung saat seluruh instrumen final
                    </span>
                </div>
            </x-card>
        </div>

        {{-- 4. REKAPITULASI STATUS SELURUH INSTRUMEN PENILAIAN --}}
        <div class="mb-8">
            <x-card class="p-6 border" style="border-color: var(--color-border);">
                <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-2 pb-3 mb-4 border-b" style="border-color: var(--color-border);">
                    <div>
                        <h2 class="text-base font-bold" style="color: var(--color-navy-900);">
                            Status Instrumen Penilaian Supervisi
                        </h2>
                        <p class="text-xs text-gray-500 mt-0.5">
                            Distribusi status instrumen supervisi dari {{ $totalPenugasan }} penugasan aktif (Total {{ $rekapPenilaian['total'] }} penilaian).
                        </p>
                    </div>
                    <span class="text-xs font-semibold text-gray-500">Periode: {{ $periode->nama }}</span>
                </div>

                <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
                    {{-- Status Final --}}
                    <div class="p-4 rounded-xl border bg-emerald-50/70 border-emerald-200">
                        <span class="inline-flex items-center gap-1.5 text-xs font-bold text-emerald-800">
                            <span class="w-2 h-2 rounded-full bg-emerald-600"></span>
                            Final (Terkunci)
                        </span>
                        <p class="text-2xl font-extrabold text-emerald-900 mt-2">{{ $rekapPenilaian['final'] }}</p>
                        <p class="text-xs text-emerald-700 mt-0.5">Telah dinilai & terkunci</p>
                    </div>

                    {{-- Status Draft --}}
                    <div class="p-4 rounded-xl border bg-amber-50/70 border-amber-200">
                        <span class="inline-flex items-center gap-1.5 text-xs font-bold text-amber-800">
                            <span class="w-2 h-2 rounded-full bg-amber-500"></span>
                            Sedang Dinilai (Draf)
                        </span>
                        <p class="text-2xl font-extrabold text-amber-900 mt-2">{{ $rekapPenilaian['draft'] }}</p>
                        <p class="text-xs text-amber-800 mt-0.5">Dalam pengisian penilai</p>
                    </div>

                    {{-- Status Direvisi --}}
                    <div class="p-4 rounded-xl border bg-orange-50/70 border-orange-200">
                        <span class="inline-flex items-center gap-1.5 text-xs font-bold text-orange-800">
                            <span class="w-2 h-2 rounded-full bg-orange-500"></span>
                            Sedang Direvisi
                        </span>
                        <p class="text-2xl font-extrabold text-orange-900 mt-2">{{ $rekapPenilaian['direvisi'] }}</p>
                        <p class="text-xs text-orange-800 mt-0.5">Dibuka kunci oleh admin</p>
                    </div>

                    {{-- Status Belum --}}
                    <div class="p-4 rounded-xl border bg-gray-50 border-gray-200">
                        <span class="inline-flex items-center gap-1.5 text-xs font-bold text-gray-700">
                            <span class="w-2 h-2 rounded-full bg-gray-400"></span>
                            Belum Dinilai
                        </span>
                        <p class="text-2xl font-extrabold text-gray-800 mt-2">{{ $rekapPenilaian['belum'] }}</p>
                        <p class="text-xs text-gray-500 mt-0.5">Menunggu jadwal supervisi</p>
                    </div>
                </div>
            </x-card>
        </div>
    @endif

    {{-- 5. MENU PINTAS MODUL ADMINISTRASI SEKOLAH --}}
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-5 mb-8">
        {{-- Modul Penugasan --}}
        <x-card class="p-5 hover:border-slate-300 transition-all flex flex-col justify-between border" style="border-color: var(--color-border);">
            <div>
                <div class="flex items-center gap-3 mb-3">
                    <span class="w-10 h-10 rounded-xl flex items-center justify-center text-white shrink-0 shadow-2xs"
                          style="background-color: var(--color-navy-900);">
                        <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 20 20" fill="currentColor">
                            <path d="M8 9a3 3 0 100-6 3 3 0 000 6zM8 11a6 6 0 00-6 6h12a6 6 0 00-6-6zM16 7a1 1 0 10-2 0v1h-1a1 1 0 100 2h1v1a1 1 0 102 0v-1h1a1 1 0 100-2h-1V7z"/>
                        </svg>
                    </span>
                    <div>
                        <h3 class="text-base font-bold text-[#0d0d0d]">Penugasan Supervisi</h3>
                        <p class="text-xs text-[#8b92a1]">Petakan guru ke supervisor penilai</p>
                    </div>
                </div>
                <p class="text-xs leading-relaxed text-[#5B6475] mb-4">
                    Pantau daftar guru yang disupervisi, ganti supervisor penilai, atau buka kunci penilaian.
                </p>
            </div>
            <x-button variant="outline" :href="route('admin.penugasan.index', ['kode' => $sekolah->kode])" class="rounded-xl text-xs w-full justify-center min-h-[44px]">
                Buka Penugasan Supervisi &rarr;
            </x-button>
        </x-card>

        {{-- Modul Guru & Pengguna --}}
        <x-card class="p-5 hover:border-slate-300 transition-all flex flex-col justify-between border" style="border-color: var(--color-border);">
            <div>
                <div class="flex items-center gap-3 mb-3">
                    <span class="w-10 h-10 rounded-xl flex items-center justify-center text-white shrink-0 shadow-2xs"
                          style="background-color: var(--color-action-green);">
                        <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 20 20" fill="currentColor">
                            <path d="M9 6a3 3 0 11-6 0 3 3 0 016 0zM17 6a3 3 0 11-6 0 3 3 0 016 0zM12.93 17c.046-.327.07-.66.07-1a6.97 6.97 0 00-1.5-4.33A5 5 0 0119 16v1h-6.07zM6 11a5 5 0 015 5v1H1v-1a5 5 0 015-5z"/>
                        </svg>
                    </span>
                    <div>
                        <h3 class="text-base font-bold text-[#0d0d0d]">Data Guru & Pengguna</h3>
                        <p class="text-xs text-[#8b92a1]">Akun, peran, & kepala sekolah</p>
                    </div>
                </div>
                <p class="text-xs leading-relaxed text-[#5B6475] mb-4">
                    Kelola data akun guru sekolah, penetapan peran supervisor penilai, dan penetapan kepala sekolah.
                </p>
            </div>
            <x-button variant="outline" :href="route('admin.guru.index', ['kode' => $sekolah->kode])" class="rounded-xl text-xs w-full justify-center min-h-[44px]">
                Buka Data Guru &rarr;
            </x-button>
        </x-card>

        {{-- Modul Laporan & Rekap --}}
        <x-card class="p-5 hover:border-slate-300 transition-all flex flex-col justify-between border" style="border-color: var(--color-border);">
            <div>
                <div class="flex items-center gap-3 mb-3">
                    <span class="w-10 h-10 rounded-xl flex items-center justify-center text-white shrink-0 shadow-2xs"
                          style="background-color: var(--color-navy-700);">
                        <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 20 20" fill="currentColor">
                            <path d="M2 11a1 1 0 011-1h2a1 1 0 011 1v5a1 1 0 01-1 1H3a1 1 0 01-1-1v-5zM8 7a1 1 0 11-2 0 1 1 0 012 0v9a1 1 0 01-1 1H9a1 1 0 01-1-1V7zM14 4a1 1 0 011-1h2a1 1 0 011 1v12a1 1 0 01-1 1h-2a1 1 0 01-1-1V4z"/>
                        </svg>
                    </span>
                    <div>
                        <h3 class="text-base font-bold text-[#0d0d0d]">Laporan & Rekapitulasi</h3>
                        <p class="text-xs text-[#8b92a1]">Ekspor Excel & PDF penilaian</p>
                    </div>
                </div>
                <p class="text-xs leading-relaxed text-[#5B6475] mb-4">
                    Pantau nilai rata-rata, predikat kinerja guru, serta unduh laporan rekapitulasi lengkap.
                </p>
            </div>
            <x-button variant="outline" :href="route('admin.laporan.index', ['kode' => $sekolah->kode])" class="rounded-xl text-xs w-full justify-center min-h-[44px]">
                Buka Rekapitulasi Nilai &rarr;
            </x-button>
        </x-card>

        {{-- Modul Periode Supervisi --}}
        <x-card class="p-5 hover:border-slate-300 transition-all flex flex-col justify-between border" style="border-color: var(--color-border);">
            <div>
                <div class="flex items-center gap-3 mb-3">
                    <span class="w-10 h-10 rounded-xl flex items-center justify-center text-white shrink-0 shadow-2xs"
                          style="background-color: var(--color-navy-900);">
                        <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 20 20" fill="currentColor">
                            <path fill-rule="evenodd" d="M6 2a1 1 0 00-1 1v1H4a2 2 0 00-2 2v10a2 2 0 002 2h12a2 2 0 002-2V6a2 2 0 00-2-2h-1V3a1 1 0 10-2 0v1H7V3a1 1 0 00-1-1zm0 5a1 1 0 000 2h8a1 1 0 100-2H6z" clip-rule="evenodd"/>
                        </svg>
                    </span>
                    <div>
                        <h3 class="text-base font-bold text-[#0d0d0d]">Periode Supervisi</h3>
                        <p class="text-xs text-[#8b92a1]">Jadwal & aktivasi periode</p>
                    </div>
                </div>
                <p class="text-xs leading-relaxed text-[#5B6475] mb-4">
                    Kelola jadwal tahun ajaran, semester, aktivasi periode berjalan, dan penutupan periode.
                </p>
            </div>
            <x-button variant="outline" :href="route('admin.periode.index', ['kode' => $sekolah->kode])" class="rounded-xl text-xs w-full justify-center min-h-[44px]">
                Buka Periode Supervisi &rarr;
            </x-button>
        </x-card>

        {{-- Modul Instrumen Supervisi --}}
        <x-card class="p-5 hover:border-slate-300 transition-all flex flex-col justify-between border" style="border-color: var(--color-border);">
            <div>
                <div class="flex items-center gap-3 mb-3">
                    <span class="w-10 h-10 rounded-xl flex items-center justify-center text-white shrink-0 shadow-2xs"
                          style="background-color: var(--color-action-green);">
                        <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 20 20" fill="currentColor">
                            <path fill-rule="evenodd" d="M4 4a2 2 0 012-2h4.586A2 2 0 0112 2.586L15.414 6A2 2 0 0116 7.414V16a2 2 0 01-2 2H6a2 2 0 01-2-2V4zm2 6a1 1 0 011-1h6a1 1 0 110 2H7a1 1 0 01-1-1zm1 3a1 1 0 100 2h6a1 1 0 100-2H7z" clip-rule="evenodd"/>
                        </svg>
                    </span>
                    <div>
                        <h3 class="text-base font-bold text-[#0d0d0d]">Instrumen Supervisi</h3>
                        <p class="text-xs text-[#8b92a1]">Kelola instrumen & butir</p>
                    </div>
                </div>
                <p class="text-xs leading-relaxed text-[#5B6475] mb-4">
                    Kelola versi instrumen kurikulum resmi sekolah, bagian penilaian, dan butir indikator.
                </p>
            </div>
            <x-button variant="outline" :href="route('admin.instrumen.index', ['kode' => $sekolah->kode])" class="rounded-xl text-xs w-full justify-center min-h-[44px]">
                Buka Instrumen Supervisi &rarr;
            </x-button>
        </x-card>

        {{-- Modul Surat Tugas --}}
        <x-card class="p-5 hover:border-slate-300 transition-all flex flex-col justify-between border" style="border-color: var(--color-border);">
            <div>
                <div class="flex items-center gap-3 mb-3">
                    <span class="w-10 h-10 rounded-xl flex items-center justify-center text-white shrink-0 shadow-2xs"
                          style="background-color: var(--color-navy-700);">
                        <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 20 20" fill="currentColor">
                            <path fill-rule="evenodd" d="M4 4a2 2 0 012-2h4.586A2 2 0 0112 2.586L15.414 6A2 2 0 0116 7.414V16a2 2 0 01-2 2H6a2 2 0 01-2-2V4zm2 6a1 1 0 011-1h6a1 1 0 110 2H7a1 1 0 01-1-1zm1 3a1 1 0 100 2h6a1 1 0 100-2H7z" clip-rule="evenodd"/>
                        </svg>
                    </span>
                    <div>
                        <h3 class="text-base font-bold text-[#0d0d0d]">Surat Tugas Penilai</h3>
                        <p class="text-xs text-[#8b92a1]">Penerbitan SK & Unduh DOCX</p>
                    </div>
                </div>
                <p class="text-xs leading-relaxed text-[#5B6475] mb-4">
                    Terbitkan surat tugas resmi supervisor penilai perorangan atau kolektif berformat DOCX.
                </p>
            </div>
            <x-button variant="outline" :href="route('admin.surat-tugas.index', ['kode' => $sekolah->kode])" class="rounded-xl text-xs w-full justify-center min-h-[44px]">
                Buka Surat Tugas &rarr;
            </x-button>
        </x-card>
    </div>
</x-app-layout>
