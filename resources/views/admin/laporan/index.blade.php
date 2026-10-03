<x-app-layout :namaSekolah="$sekolah->nama" title="Rekap & Laporan Supervisi">

    <x-page-header
        title="Rekap & Laporan Supervisi"
        subtitle="Pantau ketuntasan supervisi akademik guru dan unduh laporan berkala."
    >
        <x-slot:actions>
            @if($periode)
                <div class="flex flex-wrap items-center gap-2">
                    {{-- Tombol Unduh Excel Rekap Nilai --}}
                    <a href="{{ route('admin.laporan.export.rekap', ['kode' => $sekolah->kode, 'periode_id' => $periode->id, 'status' => request('status'), 'penilai_id' => request('penilai_id')]) }}"
                       download
                       class="px-4 py-2.5 rounded-xl font-bold text-white bg-emerald-700 hover:bg-emerald-800 transition min-h-[44px] flex items-center gap-2 shadow-sm text-sm">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                        </svg>
                        <span>Unduh Rekap Nilai (.xlsx)</span>
                    </a>

                    {{-- Tombol Unduh Excel Laporan Ketuntasan --}}
                    <a href="{{ route('admin.laporan.export.ketuntasan', ['kode' => $sekolah->kode, 'periode_id' => $periode->id, 'status' => request('status'), 'penilai_id' => request('penilai_id')]) }}"
                       download
                       class="px-4 py-2.5 rounded-xl font-bold text-navy-900 bg-white border border-navy-300 hover:bg-navy-50 transition min-h-[44px] flex items-center gap-2 shadow-sm text-sm">
                        <svg class="w-4 h-4 text-navy-700" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 17v-2m3 2v-4m3 4v-6m2 10H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                        </svg>
                        <span>Laporan Ketuntasan (.xlsx)</span>
                    </a>
                </div>
            @endif
        </x-slot:actions>
    </x-page-header>

    {{-- Filter Periode & Ringkasan Ketuntasan --}}
    <div class="mb-6 grid grid-cols-1 md:grid-cols-4 gap-4">
        {{-- Pemilih Periode --}}
        <x-card class="p-4 md:col-span-2">
            <form method="GET" action="{{ route('admin.laporan.index', ['kode' => $sekolah->kode]) }}" id="form-pilih-periode">
                <label for="periode_id" class="block text-xs font-bold uppercase tracking-wider mb-2" style="color: var(--color-navy-900);">
                    Pilih Periode Supervisi
                </label>
                <div class="flex gap-2">
                    <select name="periode_id" id="periode_id" class="form-input text-sm w-full" onchange="this.form.submit()">
                        @forelse($semuaPeriode as $p)
                            <option value="{{ $p->id }}" {{ $periode && $periode->id === $p->id ? 'selected' : '' }}>
                                {{ $p->nama }} ({{ $p->tahun_ajaran }}) &mdash; {{ ucfirst($p->status) }}
                            </option>
                        @empty
                            <option value="">Belum ada periode supervisi</option>
                        @endforelse
                    </select>
                </div>
            </form>
            @if($periode)
                <div class="mt-2 text-xs flex items-center gap-2" style="color: var(--color-muted);">
                    <span>Status Periode:</span>
                    @if($periode->isAktif())
                        <x-badge-status status="final">Aktif</x-badge-status>
                    @elseif($periode->isDraft())
                        <x-badge-status status="draft">Draf</x-badge-status>
                    @else
                        <x-badge-status status="belum">Ditutup</x-badge-status>
                    @endif
                    <span class="text-gray-400">&bull;</span>
                    <span>{{ $periode->tanggal_mulai->format('d/m/Y') }} &ndash; {{ $periode->tanggal_selesai->format('d/m/Y') }}</span>
                </div>
            @endif
        </x-card>

        {{-- Statistik Selesai (4/4) --}}
        <x-card class="p-4 text-center">
            <span class="block text-xs font-semibold uppercase tracking-wider" style="color: var(--color-muted);">Selesai (4/4 Instrumen)</span>
            <span class="block text-2xl font-bold mt-1 text-emerald-700">
                {{ $totalSelesai }} Guru
            </span>
            <span class="block text-xs mt-1 text-muted">Ketuntasan: {{ $persenKetuntasan }}%</span>
        </x-card>

        {{-- Statistik Belum Selesai (<4) --}}
        <x-card class="p-4 text-center">
            <span class="block text-xs font-semibold uppercase tracking-wider" style="color: var(--color-muted);">Belum Selesai (&lt;4)</span>
            <span class="block text-2xl font-bold mt-1 text-amber-700">
                {{ $totalBelumSelesai }} Guru
            </span>
            <span class="block text-xs mt-1 text-muted">Sedang berproses</span>
        </x-card>
    </div>

    @if(!$periode)
        <x-card class="p-8 text-center">
            <x-empty-state
                title="Belum Ada Periode Supervisi"
                description="Buat dan aktifkan periode supervisi terlebih dahulu untuk memantau rekapitulasi nilai."
            />
        </x-card>
    @else
        <div x-data="{
            modalBukaKunci: false,
            penilaianAktif: { id: '', namaGuru: '', namaInstrumen: '' },
            bukaModalBukaKunci(id, namaGuru, namaInstrumen) {
                this.penilaianAktif = { id: id, namaGuru: namaGuru, namaInstrumen: namaInstrumen };
                this.modalBukaKunci = true;
            }
        }">
            {{-- Filter & Pencarian Baris --}}
            <div class="mb-4 flex flex-col md:flex-row items-center justify-between gap-3">
                <form method="GET" action="{{ route('admin.laporan.index', ['kode' => $sekolah->kode]) }}" class="flex flex-wrap items-center gap-2 w-full md:w-auto">
                    <input type="hidden" name="periode_id" value="{{ $periode->id }}">

                    {{-- Filter Status Ketuntasan --}}
                    <select name="status" class="form-input text-sm" onchange="this.form.submit()">
                        <option value="">Semua Status (Selesai & Belum)</option>
                        <option value="selesai" {{ request('status') === 'selesai' ? 'selected' : '' }}>Hanya Selesai (4/4)</option>
                        <option value="belum_selesai" {{ request('status') === 'belum_selesai' ? 'selected' : '' }}>Hanya Belum Selesai (&lt;4)</option>
                    </select>

                    {{-- Filter Supervisor --}}
                    <select name="penilai_id" class="form-input text-sm" onchange="this.form.submit()">
                        <option value="">Semua Supervisor Penilai</option>
                        @foreach($supervisors as $spv)
                            <option value="{{ $spv->id }}" {{ request('penilai_id') == $spv->id ? 'selected' : '' }}>
                                {{ $spv->nama }}
                            </option>
                        @endforeach
                    </select>

                    {{-- Input Pencarian Nama / NIP --}}
                    <div class="relative flex-1 sm:w-64">
                        <input
                            type="search"
                            name="cari"
                            value="{{ request('cari') }}"
                            placeholder="Cari nama atau NIP guru..."
                            class="form-input text-sm w-full pl-8 py-1.5"
                        >
                        <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 20 20" fill="currentColor"
                             class="absolute left-2.5 top-2.5 text-gray-400">
                            <path fill-rule="evenodd" d="M8 4a4 4 0 100 8 4 4 0 000-8zM2 8a6 6 0 1110.89 3.476l4.817 4.817a1 1 0 01-1.414 1.414l-4.816-4.816A6 6 0 012 8z" clip-rule="evenodd" />
                        </svg>
                    </div>

                    <x-button type="submit" variant="primary" class="text-xs py-1.5">Filter</x-button>
                    @if(request('cari') || request('status') || request('penilai_id'))
                        <a href="{{ route('admin.laporan.index', ['kode' => $sekolah->kode, 'periode_id' => $periode->id]) }}"
                           class="text-xs self-center px-2 py-1 text-gray-600 hover:underline">Reset</a>
                    @endif
                </form>

                <div class="text-xs text-muted self-end md:self-center">
                    Menampilkan <strong>{{ $daftarPenugasan->count() }}</strong> guru
                </div>
            </div>

            {{-- Tabel Rekapitulasi Guru --}}
            <x-card class="overflow-hidden p-0">
                @if($daftarPenugasan->isEmpty())
                    <div class="p-8 text-center">
                        <x-empty-state
                            title="Tidak Ada Data Penugasan"
                            description="{{ request('cari') || request('status') || request('penilai_id') ? 'Tidak ada guru yang sesuai dengan kriteria filter.' : 'Belum ada guru yang ditugaskan pada periode ini.' }}"
                        />
                    </div>
                @else
                    <div class="overflow-x-auto">
                        <table class="w-full text-left text-sm" style="color: var(--color-ink);">
                            <thead style="background-color: var(--color-navy-50); color: var(--color-navy-900);">
                                <tr>
                                    <th class="py-3 px-4 font-bold">Guru & NIP</th>
                                    <th class="py-3 px-4 font-bold">Supervisor</th>
                                    <th class="py-3 px-4 font-bold text-center">Status 4 Instrumen</th>
                                    <th class="py-3 px-4 font-bold text-center">Progres</th>
                                    <th class="py-3 px-4 font-bold text-center">Rata-rata</th>
                                    <th class="py-3 px-4 font-bold text-center">Status</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y" style="border-color: var(--color-border);">
                                @foreach($daftarPenugasan as $item)
                                    @php
                                        $finalList = $item->penilaian->filter(fn($p) => $p->isFinal());
                                        $finalCount = $finalList->count();
                                        $isSelesai = $finalCount === 4;
                                        $rataRata = $finalCount > 0 ? round($finalList->sum('nilai') / $finalCount, 2) : null;
                                    @endphp
                                    <tr class="hover:bg-gray-50/60 transition-colors">
                                        {{-- Guru --}}
                                        <td class="py-3.5 px-4">
                                            <p class="font-bold text-base" style="color: var(--color-navy-900);">
                                                {{ $item->guru->nama }}
                                            </p>
                                            <p class="text-xs text-gray-500">
                                                NIP: {{ $item->guru->nip ?: '-' }}
                                            </p>
                                        </td>

                                        {{-- Supervisor --}}
                                        <td class="py-3.5 px-4">
                                            <p class="font-semibold text-sm" style="color: var(--color-navy-900);">
                                                {{ $item->penilai->nama }}
                                            </p>
                                        </td>

                                        {{-- Badge Status 4 Instrumen --}}
                                        <td class="py-3.5 px-4 text-center">
                                            <div class="inline-flex flex-wrap gap-1.5 justify-center max-w-xs">
                                                @foreach($item->penilaian as $pen)
                                                    @php
                                                        $singkatan = match($pen->jenisInstrumen->kode ?? '') {
                                                            'kbm' => 'KBM',
                                                            'administrasi' => 'ADM',
                                                            'pengelolaan_kelas' => 'KELAS',
                                                            'perencanaan' => 'RENC',
                                                            default => substr($pen->jenisInstrumen->nama ?? 'N', 0, 3)
                                                        };
                                                    @endphp

                                                    <div class="inline-flex items-center rounded overflow-hidden border text-xs
                                                        {{ $pen->isFinal() ? 'bg-emerald-50 border-emerald-300 text-emerald-900' : '' }}
                                                        {{ $pen->isDraft() ? 'bg-amber-50 border-amber-200 text-amber-800' : '' }}
                                                        {{ $pen->isDirevisi() ? 'bg-orange-50 border-orange-200 text-orange-800' : '' }}
                                                        {{ $pen->isBelum() ? 'bg-neutral-50 border-neutral-200 text-neutral-600' : '' }}">
                                                        
                                                        <span class="px-2 py-0.5 font-semibold">
                                                            {{ $singkatan }}: {{ ucfirst($pen->status) }}
                                                            @if($pen->isFinal()) ({{ number_format($pen->nilai, 0) }}) @endif
                                                        </span>

                                                        @if($pen->isFinal())
                                                            <div class="flex items-center border-l border-emerald-200">
                                                                {{-- Tombol Cetak PDF --}}
                                                                <a href="{{ route('penilaian.cetak-pdf', ['kode' => $sekolah->kode, 'penilaian' => $pen]) }}"
                                                                   target="_blank"
                                                                   class="px-1.5 py-0.5 hover:bg-emerald-200 text-emerald-800 transition"
                                                                   title="Cetak Lembar Penilaian {{ $pen->jenisInstrumen->nama }} (PDF)">
                                                                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z" />
                                                                    </svg>
                                                                </a>

                                                                {{-- Tombol Buka Kunci --}}
                                                                @if(!$periode->isDitutup())
                                                                    <button type="button"
                                                                            @click="bukaModalBukaKunci({{ $pen->id }}, '{{ addslashes($item->guru->nama) }}', '{{ addslashes($pen->jenisInstrumen->nama ?? '') }}')"
                                                                            class="px-1.5 py-0.5 hover:bg-emerald-200 text-orange-700 transition"
                                                                            title="Buka Kunci {{ $pen->jenisInstrumen->nama }}">
                                                                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 11V7a4 4 0 118 0m-4 8v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2z" />
                                                                        </svg>
                                                                    </button>
                                                                @endif
                                                            </div>
                                                        @endif
                                                    </div>
                                                @endforeach
                                            </div>
                                        </td>

                                        {{-- Progres n/4 --}}
                                        <td class="py-3.5 px-4 text-center">
                                            <span class="inline-flex items-center px-2.5 py-1 rounded-full text-xs font-bold
                                                {{ $isSelesai ? 'bg-emerald-100 text-emerald-800' : 'bg-neutral-100 text-neutral-700' }}">
                                                {{ $finalCount }}/4
                                            </span>
                                        </td>

                                        {{-- Nilai Rata-Rata --}}
                                        <td class="py-3.5 px-4 text-center">
                                            @if($rataRata !== null)
                                                <span class="font-bold text-sm text-navy-900">{{ number_format($rataRata, 2) }}</span>
                                            @else
                                                <span class="text-xs text-muted">-</span>
                                            @endif
                                        </td>

                                        {{-- Status Selesai / Belum Selesai --}}
                                        <td class="py-3.5 px-4 text-center">
                                            @if($isSelesai)
                                                <span class="inline-flex items-center gap-1 px-3 py-1 rounded-full text-xs font-bold bg-emerald-100 text-emerald-800">
                                                    <svg class="w-3.5 h-3.5" fill="currentColor" viewBox="0 0 20 20">
                                                        <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd" />
                                                    </svg>
                                                    Selesai
                                                </span>
                                            @else
                                                <span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-semibold bg-amber-100 text-amber-800">
                                                    Belum Selesai
                                                </span>
                                            @endif
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                @endif
            </x-card>

            {{-- MODAL BUKA KUNCI PENILAIAN (KHUSUS ADMIN) --}}
            <div x-show="modalBukaKunci" 
                 class="fixed inset-0 z-50 overflow-y-auto bg-black/40 flex items-center justify-center p-4" 
                 style="display: none;">
                <div @click.outside="modalBukaKunci = false" class="bg-white rounded-xl max-w-md w-full p-6 shadow-xl border" style="border-color: var(--color-border);">
                    <div class="flex items-center gap-3 mb-3">
                        <div class="w-10 h-10 rounded-full bg-orange-100 text-orange-700 flex items-center justify-center flex-shrink-0">
                            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 11V7a4 4 0 118 0m-4 8v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2z"/>
                            </svg>
                        </div>
                        <div>
                            <h3 class="text-lg font-bold" style="color: var(--color-navy-900);">Buka Kunci Penilaian</h3>
                            <p class="text-xs text-gray-500">Guru: <span class="font-bold text-gray-800" x-text="penilaianAktif.namaGuru"></span></p>
                        </div>
                    </div>

                    <p class="text-xs text-gray-600 mb-4">
                        Membuka kunci instrumen <strong x-text="penilaianAktif.namaInstrumen"></strong> akan mengubah status menjadi <strong>Sedang Direvisi</strong> sehingga supervisor dapat memperbaiki skor atau catatan.
                    </p>

                    <form :action="'{{ url('/s/' . $sekolah->kode . '/admin/penilaian') }}/' + penilaianAktif.id + '/buka-kunci'" method="POST">
                        @csrf
                        @method('PATCH')
                        <div class="mb-4">
                            <label class="block text-xs font-bold mb-1" style="color: var(--color-navy-900);">Alasan Buka Kunci * (Wajib dicatat di Audit Log)</label>
                            <textarea name="alasan" 
                                      required 
                                      rows="3" 
                                      minlength="5"
                                      placeholder="Misal: Perlu perbaikan skor butir dan penambahan tindak lanjut sesuai hasil evaluasi supervisi..." 
                                      class="form-input text-sm w-full"></textarea>
                            <p class="text-[11px] text-gray-500 mt-1">Alasan pembukaan kunci akan tercatat secara permanen di audit log sistem.</p>
                        </div>
                        <div class="flex justify-end gap-2">
                            <button type="button" @click="modalBukaKunci = false" class="px-3 py-1.5 text-xs rounded border border-gray-300">Batal</button>
                            <x-button type="submit" variant="primary" class="text-xs py-1.5">Buka Kunci Sekarang</x-button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    @endif

</x-app-layout>
