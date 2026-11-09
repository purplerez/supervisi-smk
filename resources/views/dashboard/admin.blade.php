<x-app-layout :namaSekolah="$sekolah->nama" title="Dasbor Admin">
    <x-page-header
        title="Dasbor Admin"
        subtitle="Selamat datang di panel administrasi supervisi {{ $sekolah->nama }}."
    >
        @if($periode)
            <x-slot:actions>
                <div class="flex items-center gap-2 flex-wrap">
                    <span class="inline-flex items-center gap-1.5 text-xs font-semibold px-3 py-1.5 rounded-full border bg-white" style="border-color: var(--color-border); color: var(--color-navy-900);">
                        <span class="w-2 h-2 rounded-full {{ $periode->isAktif() ? 'bg-emerald-500' : 'bg-amber-500' }}"></span>
                        <span>Periode: <strong>{{ $periode->nama }}</strong> ({{ ucfirst($periode->status) }})</span>
                    </span>
                    <x-button variant="outline" href="{{ route('admin.penugasan.index', ['kode' => $sekolah->kode, 'periode_id' => $periode->id]) }}" class="text-xs py-1.5">
                        Lihat Penugasan &rarr;
                    </x-button>
                </div>
            </x-slot:actions>
        @endif
    </x-page-header>

    {{-- Ringkasan & Statistik Supervisi --}}
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
                    <p class="text-xs text-gray-500 mt-0.5">Terdaftar di sekolah</p>
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
                    <p class="text-xs text-gray-500 mt-0.5">Penilai aktif</p>
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
                    <p class="text-xs text-emerald-700 mt-0.5 font-medium">Memiliki penilai</p>
                </div>
            </div>
        </x-card>

        {{-- Card 4: Belum Punya Penilai --}}
        <x-card class="p-5 hover:border-slate-300 transition-all">
            <div class="flex items-center gap-4">
                <div class="w-12 h-12 rounded-2xl flex items-center justify-center text-white shadow-2xs shrink-0"
                     style="background-color: {{ $guruBelum->count() > 0 ? 'var(--color-orange-500)' : 'var(--color-action-green)' }};">
                    @if($guruBelum->count() > 0)
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
                    <p class="text-2xl font-bold mt-0.5 {{ $guruBelum->count() > 0 ? 'text-amber-800' : 'text-emerald-800' }}">
                        {{ $guruBelum->count() }}
                    </p>
                    <p class="text-xs mt-0.5 {{ $guruBelum->count() > 0 ? 'text-amber-800 font-semibold' : 'text-gray-500' }}">
                        {{ $guruBelum->count() > 0 ? 'Perlu didaftarkan' : 'Semua telah dipetakan' }}
                    </p>
                </div>
            </div>
        </x-card>
    </div>

    {{-- =========================================================================
         FITUR UTAMA: DAFTARKAN GURU KEPADA SUPERVISOR
         ========================================================================= --}}
    <div id="daftar-guru-supervisor" class="mb-8">
        <x-card class="p-6 border" style="border-color: var(--color-border);">
            <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3 pb-4 mb-5 border-b" style="border-color: var(--color-border);">
                <div>
                    <div class="flex items-center gap-2">
                        <span class="w-8 h-8 rounded-lg flex items-center justify-center text-white shrink-0" style="background-color: var(--color-navy-900);">
                            <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 20 20" fill="currentColor">
                                <path d="M8 9a3 3 0 100-6 3 3 0 000 6zM8 11a6 6 0 00-6 6h12a6 6 0 00-6-6zM16 7a1 1 0 10-2 0v1h-1a1 1 0 100 2h1v1a1 1 0 102 0v-1h1a1 1 0 100-2h-1V7z"/>
                            </svg>
                        </span>
                        <h2 class="text-lg font-bold" style="color: var(--color-navy-900);">
                            Daftarkan Guru kepada Supervisor
                        </h2>
                    </div>
                    <p class="text-sm text-[#5B6475] mt-1 ml-10">
                        Pilih supervisor penilai dan centang guru yang akan disupervisi pada periode supervisi saat ini.
                    </p>
                </div>

                @if($periode && $semuaPeriode->count() > 1)
                    <form method="GET" action="{{ route('dashboard.admin', ['kode' => $sekolah->kode]) }}" class="flex items-center gap-2 self-start sm:self-center">
                        <label for="periode_filter" class="text-xs font-semibold text-gray-500 whitespace-nowrap">Pilih Periode:</label>
                        <select name="periode_id" id="periode_filter" class="form-input text-xs py-1.5 rounded-lg" onchange="this.form.submit()">
                            @foreach($semuaPeriode as $p)
                                <option value="{{ $p->id }}" {{ $periode->id === $p->id ? 'selected' : '' }}>
                                    {{ $p->nama }} ({{ ucfirst($p->status) }})
                                </option>
                            @endforeach
                        </select>
                    </form>
                @endif
            </div>

            {{-- KONDISI 1: Belum Ada Periode Supervisi --}}
            @if(!$periode)
                <div class="py-10 text-center">
                    <x-empty-state
                        title="Belum Ada Periode Supervisi"
                        description="Buat periode supervisi terlebih dahulu sebelum mendaftarkan guru kepada supervisor penilai."
                    >
                        <x-button variant="tambah" href="{{ route('admin.periode.index', ['kode' => $sekolah->kode]) }}" class="rounded-xl">
                            Buat Periode Supervisi &rarr;
                        </x-button>
                    </x-empty-state>
                </div>

            {{-- KONDISI 2: Periode Ditutup --}}
            @elseif($periode->isDitutup())
                <div class="p-6 rounded-xl bg-gray-50 border border-gray-200 text-center">
                    <div class="w-12 h-12 rounded-full bg-gray-200 text-gray-600 flex items-center justify-center mx-auto mb-3">
                        <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 20 20" fill="currentColor">
                            <path fill-rule="evenodd" d="M5 9V7a5 5 0 0110 0v2a2 2 0 012 2v5a2 2 0 01-2 2H5a2 2 0 01-2-2v-5a2 2 0 012-2zm8-2v2H7V7a3 3 0 016 0z" clip-rule="evenodd"/>
                        </svg>
                    </div>
                    <h3 class="font-bold text-base text-gray-900">Periode Supervisi Telah Ditutup</h3>
                    <p class="text-sm text-gray-600 mt-1 max-w-md mx-auto">
                        Periode <strong>{{ $periode->nama }}</strong> saat ini berstatus Ditutup sehingga penugasan baru tidak dapat ditambahkan. Silakan buka periode draf atau aktif untuk melakukan penugasan guru.
                    </p>
                    <div class="mt-4">
                        <x-button variant="outline" href="{{ route('admin.periode.index', ['kode' => $sekolah->kode]) }}" class="text-xs">
                            Buka Manajemen Periode
                        </x-button>
                    </div>
                </div>

            {{-- KONDISI 3: Belum Ada Supervisor Aktif --}}
            @elseif($supervisors->isEmpty())
                <div class="p-6 rounded-xl bg-amber-50 border border-amber-200">
                    <div class="flex items-start gap-4">
                        <div class="w-10 h-10 rounded-full bg-amber-200 text-amber-800 flex items-center justify-center shrink-0">
                            <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 20 20" fill="currentColor">
                                <path fill-rule="evenodd" d="M8.257 3.099c.765-1.36 2.722-1.36 3.486 0l5.58 9.92c.75 1.334-.213 2.98-1.742 2.98H4.42c-1.53 0-2.493-1.646-1.743-2.98l5.58-9.92zM11 13a1 1 0 11-2 0 1 1 0 012 0zm-1-8a1 1 0 00-1 1v3a1 1 0 002 0V6a1 1 0 00-1-1z" clip-rule="evenodd"/>
                            </svg>
                        </div>
                        <div>
                            <h3 class="font-bold text-base text-amber-900">Belum Ada Supervisor Aktif</h3>
                            <p class="text-sm text-amber-800 mt-1">
                                Sekolah ini belum memiliki pengguna dengan peran <strong>Supervisor</strong>. Anda dapat menetapkan peran supervisor pada guru di menu Data Guru & Pengguna sebelum mendaftarkan guru.
                            </p>
                            <div class="mt-4">
                                <x-button variant="primary" href="{{ route('admin.guru.index', ['kode' => $sekolah->kode]) }}" class="rounded-xl text-xs">
                                    Atur Peran Supervisor &rarr;
                                </x-button>
                            </div>
                        </div>
                    </div>
                </div>

            {{-- KONDISI 4: Seluruh Guru Sudah Memiliki Supervisor --}}
            @elseif($guruBelum->isEmpty())
                <div class="p-8 rounded-xl bg-emerald-50/70 border border-emerald-200 text-center">
                    <div class="w-14 h-14 rounded-full bg-emerald-100 text-emerald-700 flex items-center justify-center mx-auto mb-3 shadow-2xs">
                        <svg xmlns="http://www.w3.org/2000/svg" width="28" height="28" viewBox="0 0 20 20" fill="currentColor">
                            <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"/>
                        </svg>
                    </div>
                    <h3 class="font-bold text-lg text-emerald-900">Seluruh Guru Telah Memiliki Supervisor Penilai</h3>
                    <p class="text-sm text-emerald-800 mt-1 max-w-lg mx-auto">
                        Semua <strong>{{ $totalGuruAktif }} guru aktif</strong> di {{ $sekolah->nama }} sudah didaftarkan kepada supervisor untuk periode <strong>{{ $periode->nama }}</strong>.
                    </p>
                    <div class="mt-5 flex items-center justify-center gap-3">
                        <x-button variant="primary" href="{{ route('admin.penugasan.index', ['kode' => $sekolah->kode, 'periode_id' => $periode->id]) }}" class="rounded-xl text-sm">
                            Buka Daftar Penugasan &rarr;
                        </x-button>
                    </div>
                </div>

            {{-- KONDISI 5: FORM PENDAFTARAN GURU KEPADA SUPERVISOR --}}
            @else
                <form
                    method="POST"
                    action="{{ route('admin.penugasan.store', ['kode' => $sekolah->kode]) }}"
                    x-data="{
                        penilaiId: '{{ old('penilai_id', '') }}',
                        cariGuru: '',
                        guruTerpilih: {{ json_encode(array_map('strval', old('guru_ids', []))) }},
                        semuaGuru: {{ json_encode($guruBelum->map(fn($g) => ['id' => (string)$g->id, 'nama' => $g->nama, 'nip' => $g->nip ?: '-', 'username' => $g->username])) }},

                        get guruTersaring() {
                            let q = this.cariGuru.toLowerCase().trim();
                            return this.semuaGuru.filter(g => {
                                if (!q) return true;
                                return g.nama.toLowerCase().includes(q) || g.nip.toLowerCase().includes(q) || g.username.toLowerCase().includes(q);
                            });
                        },

                        pilihSemua() {
                            let validIds = this.guruTersaring
                                .filter(g => String(g.id) !== String(this.penilaiId))
                                .map(g => String(g.id));

                            let semuaTerpilih = validIds.length > 0 && validIds.every(id => this.guruTerpilih.includes(id));
                            if (semuaTerpilih) {
                                this.guruTerpilih = this.guruTerpilih.filter(id => !validIds.includes(id));
                            } else {
                                let gabung = new Set([...this.guruTerpilih, ...validIds]);
                                this.guruTerpilih = Array.from(gabung);
                            }
                        },

                        cekApakahPenilai(id) {
                            return Boolean(this.penilaiId && String(id) === String(this.penilaiId));
                        }
                    }"
                    class="space-y-6"
                >
                    @csrf
                    <input type="hidden" name="redirect_to" value="dashboard">
                    <input type="hidden" name="periode_id" value="{{ $periode->id }}">

                    {{-- Baris 1: Periode & Supervisor --}}
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-5">
                        {{-- Periode Aktif --}}
                        <div>
                            <label class="block text-xs font-bold uppercase tracking-wider mb-2" style="color: var(--color-navy-900);">
                                Periode Supervisi
                            </label>
                            <div class="p-3.5 rounded-xl border bg-gray-50/80 flex items-center justify-between" style="border-color: var(--color-border);">
                                <div>
                                    <p class="font-bold text-sm" style="color: var(--color-navy-900);">
                                        {{ $periode->nama }} ({{ $periode->tahun_ajaran }})
                                    </p>
                                    <p class="text-xs text-gray-500 mt-0.5">
                                        {{ $periode->tanggal_mulai->format('d/m/Y') }} &ndash; {{ $periode->tanggal_selesai->format('d/m/Y') }}
                                    </p>
                                </div>
                                <span class="text-xs font-bold px-2.5 py-1 rounded-full {{ $periode->isAktif() ? 'bg-emerald-100 text-emerald-800 border border-emerald-300' : 'bg-amber-100 text-amber-800 border border-amber-300' }}">
                                    {{ ucfirst($periode->status) }}
                                </span>
                            </div>
                        </div>

                        {{-- Pilihan Supervisor Penilai --}}
                        <div>
                            <label for="penilai_id" class="block text-xs font-bold uppercase tracking-wider mb-2" style="color: var(--color-navy-900);">
                                Pilih Supervisor Penilai *
                            </label>
                            <select
                                name="penilai_id"
                                id="penilai_id"
                                required
                                x-model="penilaiId"
                                class="form-input text-sm w-full rounded-xl py-2.5"
                                style="border-color: var(--color-border);"
                            >
                                <option value="">-- Pilih Supervisor Penilai --</option>
                                @foreach($supervisors as $spv)
                                    <option value="{{ $spv->id }}" {{ old('penilai_id') == $spv->id ? 'selected' : '' }}>
                                        {{ $spv->nama }} (NIP: {{ $spv->nip ?: '-' }})
                                    </option>
                                @endforeach
                            </select>
                            <p class="text-xs text-[#5B6475] mt-1.5">
                                Guru yang Anda centang di bawah akan ditugaskan kepada supervisor ini.
                            </p>
                            @error('penilai_id')
                                <p class="text-xs text-red-600 font-semibold mt-1">{{ $message }}</p>
                            @enderror
                        </div>
                    </div>

                    {{-- Baris 2: Daftar Guru Belum Ditugaskan --}}
                    <div>
                        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-2 mb-2.5">
                            <div>
                                <label class="block text-xs font-bold uppercase tracking-wider" style="color: var(--color-navy-900);">
                                    Pilih Guru yang Disupervisi *
                                </label>
                                <p class="text-xs text-[#5B6475] mt-0.5">
                                    Terdapat <strong>{{ $guruBelum->count() }} guru</strong> yang belum memiliki supervisor penilai pada periode ini.
                                </p>
                            </div>

                            <div class="flex items-center gap-2">
                                <button
                                    type="button"
                                    @click="pilihSemua()"
                                    class="text-xs font-semibold px-3 py-1.5 rounded-lg border border-slate-300 hover:bg-slate-100 text-slate-700 transition"
                                >
                                    <span x-text="guruTerpilih.length > 0 && guruTerpilih.length === guruTersaring.filter(g => String(g.id) !== String(penilaiId)).length ? 'Batalkan Semua' : 'Pilih Semua'">Pilih Semua</span>
                                </button>
                                <span class="text-xs font-bold px-2.5 py-1 rounded-lg bg-blue-50 text-blue-900 border border-blue-200" x-text="guruTerpilih.length + ' guru dipilih'">
                                    0 guru dipilih
                                </span>
                            </div>
                        </div>

                        {{-- Pencarian Cepat Guru --}}
                        <div class="mb-2 relative">
                            <input
                                type="search"
                                x-model="cariGuru"
                                placeholder="Cari nama guru atau NIP..."
                                class="form-input text-xs w-full pl-8 py-2 rounded-xl"
                                style="border-color: var(--color-border);"
                            >
                            <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 20 20" fill="currentColor"
                                 class="absolute left-2.5 top-2.5 text-gray-400">
                                <path fill-rule="evenodd" d="M8 4a4 4 0 100 8 4 4 0 000-8zM2 8a6 6 0 1110.89 3.476l4.817 4.817a1 1 0 01-1.414 1.414l-4.816-4.816A6 6 0 012 8z" clip-rule="evenodd" />
                            </svg>
                        </div>

                        {{-- Kotak Checklist Guru --}}
                        <div class="border rounded-xl divide-y overflow-y-auto max-h-72 bg-white" style="border-color: var(--color-border);">
                            <template x-for="guru in guruTersaring" :key="guru.id">
                                <label
                                    class="flex items-center justify-between p-3.5 hover:bg-slate-50 cursor-pointer transition min-h-[44px]"
                                    :class="{
                                        'bg-emerald-50/60': guruTerpilih.includes(String(guru.id)),
                                        'opacity-50 cursor-not-allowed bg-gray-100': cekApakahPenilai(guru.id)
                                    }"
                                >
                                    <div class="flex items-center gap-3">
                                        <input
                                            type="checkbox"
                                            name="guru_ids[]"
                                            :value="guru.id"
                                            x-model="guruTerpilih"
                                            :disabled="cekApakahPenilai(guru.id)"
                                            class="w-4 h-4 text-emerald-600 rounded border-gray-300 focus:ring-emerald-500"
                                        >
                                        <div>
                                            <p class="font-bold text-sm text-gray-900" x-text="guru.nama"></p>
                                            <p class="text-xs text-gray-500">
                                                NIP: <span x-text="guru.nip"></span> &bull; Username: <span x-text="guru.username"></span>
                                            </p>
                                        </div>
                                    </div>
                                    <template x-if="cekApakahPenilai(guru.id)">
                                        <span class="text-xs font-semibold text-amber-800 bg-amber-100 px-2 py-0.5 rounded border border-amber-300">
                                            Penilai terpilih (tidak dapat menilai diri sendiri)
                                        </span>
                                    </template>
                                </label>
                            </template>

                            <div x-show="guruTersaring.length === 0" class="p-6 text-center text-xs text-gray-500" style="display: none;">
                                Tidak ada guru yang sesuai dengan pencarian "<span x-text="cariGuru"></span>".
                            </div>
                        </div>

                        @error('guru_ids')
                            <p class="text-xs text-red-600 font-semibold mt-1">{{ $message }}</p>
                        @enderror
                    </div>

                    {{-- Tombol Aksi Submit --}}
                    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3 pt-4 border-t" style="border-color: var(--color-border);">
                        <p class="text-xs text-[#5B6475]">
                            Setiap pendaftaran otomatis membentuk baris instrumen penilaian supervisi untuk masing-masing guru.
                        </p>
                        <x-button
                            type="submit"
                            variant="tambah"
                            class="rounded-xl px-6 py-3 text-sm font-bold flex items-center justify-center gap-2 shadow-xs cursor-pointer"
                            ::disabled="!penilaiId || guruTerpilih.length === 0"
                        >
                            <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 20 20" fill="currentColor">
                                <path d="M8 9a3 3 0 100-6 3 3 0 000 6zM8 11a6 6 0 00-6 6h12a6 6 0 00-6-6zM16 7a1 1 0 10-2 0v1h-1a1 1 0 100 2h1v1a1 1 0 102 0v-1h1a1 1 0 100-2h-1V7z"/>
                            </svg>
                            <span>Daftarkan Guru ke Supervisor</span>
                        </x-button>
                    </div>
                </form>
            @endif
        </x-card>
    </div>

    {{-- Penugasan Terbaru (Preview Cepat) --}}
    @if($periode && $penugasanTerbaru->isNotEmpty())
        <div class="mb-8">
            <x-card class="p-5 border" style="border-color: var(--color-border);">
                <div class="flex items-center justify-between mb-4 pb-3 border-b" style="border-color: var(--color-border);">
                    <div>
                        <h3 class="font-bold text-base" style="color: var(--color-navy-900);">
                            Penugasan Terbaru pada Periode Ini
                        </h3>
                        <p class="text-xs text-gray-500 mt-0.5">
                            Menampilkan 5 penugasan terakhir yang telah didaftarkan.
                        </p>
                    </div>
                    <x-button variant="outline" href="{{ route('admin.penugasan.index', ['kode' => $sekolah->kode, 'periode_id' => $periode->id]) }}" class="text-xs py-1.5">
                        Lihat Semua ({{ $totalPenugasan }}) &rarr;
                    </x-button>
                </div>

                <div class="overflow-x-auto">
                    <table class="w-full text-left text-sm" style="color: var(--color-ink);">
                        <thead style="background-color: var(--color-navy-50); color: var(--color-navy-900);">
                            <tr>
                                <th class="py-2.5 px-4 font-bold text-xs">Guru yang Disupervisi</th>
                                <th class="py-2.5 px-4 font-bold text-xs">Supervisor Penilai</th>
                                <th class="py-2.5 px-4 font-bold text-xs text-right">Aksi</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y" style="border-color: var(--color-border);">
                            @foreach($penugasanTerbaru as $tugas)
                                <tr class="hover:bg-gray-50/60 transition-colors">
                                    <td class="py-2.5 px-4">
                                        <p class="font-semibold text-sm" style="color: var(--color-navy-900);">{{ $tugas->guru->nama }}</p>
                                        <p class="text-xs text-gray-500">NIP: {{ $tugas->guru->nip ?: '-' }}</p>
                                    </td>
                                    <td class="py-2.5 px-4">
                                        <p class="font-medium text-sm text-gray-800">{{ $tugas->penilai->nama }}</p>
                                        <p class="text-xs text-gray-500">NIP: {{ $tugas->penilai->nip ?: '-' }}</p>
                                    </td>
                                    <td class="py-2.5 px-4 text-right">
                                        <a href="{{ route('admin.penugasan.index', ['kode' => $sekolah->kode, 'periode_id' => $periode->id, 'cari' => $tugas->guru->nama]) }}"
                                           class="text-xs font-semibold text-blue-700 hover:underline">
                                            Rincian &rarr;
                                        </a>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </x-card>
        </div>
    @endif

    {{-- Menu Pintas Modul Admin --}}
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 mb-8">
        <x-card class="p-5 hover:border-slate-300 transition-all flex flex-col justify-between">
            <div>
                <h3 class="text-base font-bold mb-1 text-[#0d0d0d]">Data Guru & Pengguna</h3>
                <p class="text-xs leading-relaxed text-[#5B6475] mb-4">
                    Kelola akun guru, penetapan peran supervisor, dan kepala sekolah.
                </p>
            </div>
            <x-button variant="outline" :href="route('admin.guru.index', ['kode' => $sekolah->kode])" class="rounded-xl text-xs w-full justify-center">
                Buka Data Guru &rarr;
            </x-button>
        </x-card>

        <x-card class="p-5 hover:border-slate-300 transition-all flex flex-col justify-between">
            <div>
                <h3 class="text-base font-bold mb-1 text-[#0d0d0d]">Impor Excel Guru</h3>
                <p class="text-xs leading-relaxed text-[#5B6475] mb-4">
                    Unggah massal data guru dengan file Excel dan pratinjau dry-run.
                </p>
            </div>
            <x-button variant="outline" :href="route('admin.guru.import', ['kode' => $sekolah->kode])" class="rounded-xl text-xs w-full justify-center">
                Buka Impor Excel &rarr;
            </x-button>
        </x-card>

        <x-card class="p-5 hover:border-slate-300 transition-all flex flex-col justify-between">
            <div>
                <h3 class="text-base font-bold mb-1 text-[#0d0d0d]">Periode Supervisi</h3>
                <p class="text-xs leading-relaxed text-[#5B6475] mb-4">
                    Kelola jadwal tahun ajaran, semester, dan aktivasi periode supervisi.
                </p>
            </div>
            <x-button variant="outline" :href="route('admin.periode.index', ['kode' => $sekolah->kode])" class="rounded-xl text-xs w-full justify-center">
                Buka Periode &rarr;
            </x-button>
        </x-card>

        <x-card class="p-5 hover:border-slate-300 transition-all flex flex-col justify-between">
            <div>
                <h3 class="text-base font-bold mb-1 text-[#0d0d0d]">Surat Tugas Penilai</h3>
                <p class="text-xs leading-relaxed text-[#5B6475] mb-4">
                    Terbitkan dan unduh surat tugas resmi supervisor dalam format DOCX.
                </p>
            </div>
            <x-button variant="outline" :href="route('admin.surat-tugas.index', ['kode' => $sekolah->kode])" class="rounded-xl text-xs w-full justify-center">
                Buka Surat Tugas &rarr;
            </x-button>
        </x-card>
    </div>
</x-app-layout>
