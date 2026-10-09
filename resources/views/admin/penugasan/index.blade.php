<x-app-layout :namaSekolah="$sekolah->nama" title="Penugasan Supervisi">

    <x-page-header
        title="Penugasan Supervisi"
        subtitle="Petakan guru yang disupervisi kepada supervisor penilai pada periode supervisi."
    >
        <x-slot:actions>
            {{-- Penugasan kini dilakukan secara mandiri oleh masing-masing guru --}}
        </x-slot:actions>
    </x-page-header>

    {{-- Filter Periode & Ringkasan --}}
    <div class="mb-6 grid grid-cols-1 md:grid-cols-4 gap-4">
        {{-- Pemilih Periode --}}
        <x-card class="p-4 md:col-span-2">
            <form method="GET" action="{{ route('admin.penugasan.index', ['kode' => $sekolah->kode]) }}" id="form-pilih-periode">
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
                    <span>Status:</span>
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

        {{-- Statistik Guru Ditugaskan --}}
        <x-card class="p-4 text-center">
            <span class="block text-xs font-semibold uppercase tracking-wider" style="color: var(--color-muted);">Total Guru Ditugaskan</span>
            <span class="block text-2xl font-bold mt-1" style="color: var(--color-navy-900);">
                {{ $daftarPenugasan ? $daftarPenugasan->total() : 0 }}
            </span>
            <span class="block text-xs mt-1 text-green-700">Sudah memiliki penilai</span>
        </x-card>

        {{-- Statistik Guru Belum Punya Penilai --}}
        <x-card class="p-4 text-center">
            <span class="block text-xs font-semibold uppercase tracking-wider" style="color: var(--color-muted);">Belum Punya Penilai</span>
            <span class="block text-2xl font-bold mt-1 text-orange-700">
                {{ $guruBelum->count() }}
            </span>
            @if($guruBelum->count() > 0 && $periode)
                <a href="{{ route('admin.penugasan.belum-dinilai', ['kode' => $sekolah->kode, 'periode_id' => $periode->id]) }}"
                   class="inline-block mt-1 text-xs font-semibold text-blue-700 hover:underline">
                    Lihat Daftar &rarr;
                </a>
            @else
                <span class="block text-xs mt-1 text-gray-500">Seluruh guru telah dipetakan</span>
            @endif
        </x-card>
    </div>

    @if(!$periode)
        <x-card class="p-8 text-center">
            <x-empty-state
                title="Belum Ada Periode Supervisi"
                description="Buat periode supervisi terlebih dahulu sebelum menugaskan guru dan supervisor."
            >
                <x-button variant="tambah" href="{{ route('admin.periode.index', ['kode' => $sekolah->kode]) }}">
                    Ke Halaman Periode
                </x-button>
            </x-empty-state>
        </x-card>
    @else
        {{-- Tab Navigasi: Daftar Penugasan vs Rekap per Supervisor --}}
        <div x-data="{
            tab: 'daftar',
            modalGantiPenilai: false,
            penugasanAktif: { id: '', namaGuru: '', penilaiId: '' },
            bukaGantiPenilai(id, namaGuru, penilaiId) {
                this.penugasanAktif = { id: id, namaGuru: namaGuru, penilaiId: penilaiId };
                this.modalGantiPenilai = true;
            },
            modalBukaKunci: false,
            penilaianAktif: { id: '', namaGuru: '', namaInstrumen: '' },
            bukaModalBukaKunci(id, namaGuru, namaInstrumen) {
                this.penilaianAktif = { id: id, namaGuru: namaGuru, namaInstrumen: namaInstrumen };
                this.modalBukaKunci = true;
            }
        }">
            <div class="flex border-b mb-6" style="border-color: var(--color-border);">
                <button
                    type="button"
                    @click="tab = 'daftar'"
                    :class="tab === 'daftar' ? 'border-b-2 font-bold' : 'text-gray-500 hover:text-gray-700'"
                    class="py-3 px-5 text-sm transition-colors"
                    :style="tab === 'daftar' ? 'color: var(--color-navy-900); border-color: var(--color-orange-500);' : ''"
                >
                    Daftar Penugasan Guru ({{ $daftarPenugasan->total() }})
                </button>
                <button
                    type="button"
                    @click="tab = 'rekap'"
                    :class="tab === 'rekap' ? 'border-b-2 font-bold' : 'text-gray-500 hover:text-gray-700'"
                    class="py-3 px-5 text-sm transition-colors"
                    :style="tab === 'rekap' ? 'color: var(--color-navy-900); border-color: var(--color-orange-500);' : ''"
                >
                    Rekap per Supervisor ({{ $rekapSupervisor->count() }})
                </button>
            </div>

            {{-- TAB 1: DAFTAR PENUGASAN GURU --}}
            <div x-show="tab === 'daftar'" class="space-y-4">
                {{-- Filter & Pencarian Tabel --}}
                <div class="flex flex-col sm:flex-row gap-3 items-center justify-between">
                    <form method="GET" action="{{ route('admin.penugasan.index', ['kode' => $sekolah->kode]) }}" class="flex flex-wrap gap-2 w-full sm:w-auto">
                        <input type="hidden" name="periode_id" value="{{ $periode->id }}">

                        <select name="penilai_id" class="form-input text-sm" onchange="this.form.submit()">
                            <option value="">Semua Supervisor</option>
                            @foreach($supervisors as $spv)
                                <option value="{{ $spv->id }}" {{ request('penilai_id') == $spv->id ? 'selected' : '' }}>
                                    {{ $spv->nama }}
                                </option>
                            @endforeach
                        </select>

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
                        @if(request('cari') || request('penilai_id'))
                            <a href="{{ route('admin.penugasan.index', ['kode' => $sekolah->kode, 'periode_id' => $periode->id]) }}"
                               class="text-xs self-center px-2 py-1 text-gray-600 hover:underline">Reset</a>
                        @endif
                    </form>
                </div>

                {{-- Tabel Penugasan --}}
                <x-card class="overflow-hidden p-0">
                    @if($daftarPenugasan->isEmpty())
                        <div class="p-8 text-center">
                            <x-empty-state
                                title="Belum Ada Penugasan"
                                description="{{ request('cari') || request('penilai_id') ? 'Tidak ada penugasan yang sesuai dengan filter.' : 'Belum ada guru yang mengajukan supervisi pada periode ini. Guru akan memilih supervisor dan mengajukan penugasan secara mandiri di dasbor mereka.' }}"
                            />
                        </div>
                    @else
                        <div class="overflow-x-auto">
                            <table class="w-full text-left text-sm" style="color: var(--color-ink);">
                                <thead style="background-color: var(--color-navy-50); color: var(--color-navy-900);">
                                    <tr>
                                        <th class="py-3.5 px-4 font-bold">Guru yang Disupervisi</th>
                                        <th class="py-3.5 px-4 font-bold">Supervisor Penilai</th>
                                        <th class="py-3.5 px-4 font-bold text-center">Status 4 Penilaian</th>
                                        <th class="py-3.5 px-4 font-bold text-right">Aksi</th>
                                    </tr>
                                </thead>
                                <tbody class="divide-y" style="border-color: var(--color-border);">
                                    @foreach($daftarPenugasan as $item)
                                        @php
                                            $bisaUbah = $item->bisaDiubahAtauDihapus() && !$periode->isDitutup();
                                        @endphp
                                        <tr class="hover:bg-gray-50/60 transition-colors">
                                            {{-- Guru --}}
                                            <td class="py-3.5 px-4">
                                                <p class="font-bold text-base" style="color: var(--color-navy-900);">
                                                    {{ $item->guru->nama }}
                                                </p>
                                                <p class="text-xs text-gray-500">
                                                    NIP: {{ $item->guru->nip ?: '-' }} &bull; Username: {{ $item->guru->username }}
                                                </p>
                                            </td>

                                            {{-- Penilai --}}
                                            <td class="py-3.5 px-4">
                                                <p class="font-semibold text-sm" style="color: var(--color-navy-900);">
                                                    {{ $item->penilai->nama }}
                                                </p>
                                                <p class="text-xs text-gray-500">
                                                    NIP: {{ $item->penilai->nip ?: '-' }}
                                                </p>
                                            </td>

                                            {{-- Status 4 Penilaian --}}
                                            <td class="py-3.5 px-4 text-center">
                                                <div class="inline-flex flex-wrap gap-1 justify-center max-w-xs">
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
                                                        @if($pen->isFinal() && !$periode->isDitutup())
                                                            <button type="button"
                                                                    @click="bukaModalBukaKunci({{ $pen->id }}, '{{ addslashes($item->guru->nama) }}', '{{ addslashes($pen->jenisInstrumen->nama ?? '') }}')"
                                                                    class="px-2 py-0.5 rounded text-xs font-semibold bg-emerald-100 text-emerald-800 hover:bg-emerald-200 border border-emerald-300 inline-flex items-center gap-1 cursor-pointer transition"
                                                                    title="Klik untuk Buka Kunci {{ $pen->jenisInstrumen->nama }}">
                                                                <span>{{ $singkatan }}: Final</span>
                                                                <svg class="w-3 h-3 text-emerald-700" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 11V7a4 4 0 118 0m-4 8v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2z"/></svg>
                                                            </button>
                                                        @else
                                                            <span class="px-2 py-0.5 rounded text-xs font-semibold
                                                                {{ $pen->isFinal() ? 'bg-emerald-100 text-emerald-800' : '' }}
                                                                {{ $pen->isDraft() ? 'bg-amber-100 text-amber-800' : '' }}
                                                                {{ $pen->isDirevisi() ? 'bg-orange-100 text-orange-800' : '' }}
                                                                {{ $pen->isBelum() ? 'bg-neutral-100 text-neutral-600' : '' }}"
                                                                  title="{{ $pen->jenisInstrumen->nama ?? '' }}: {{ ucfirst($pen->status) }}">
                                                                {{ $singkatan }}: {{ ucfirst($pen->status) }}
                                                            </span>
                                                        @endif
                                                    @endforeach
                                                </div>
                                            </td>

                                            {{-- Aksi --}}
                                            <td class="py-3.5 px-4 text-right">
                                                <div class="flex items-center justify-end gap-1.5">
                                                    @if($bisaUbah)
                                                        <button type="button"
                                                                @click="bukaGantiPenilai({{ $item->id }}, '{{ addslashes($item->guru->nama) }}', {{ $item->penilai_id }})"
                                                                class="px-2.5 py-1 text-xs font-semibold rounded transition-colors"
                                                                style="background-color: var(--color-action-edit); color: var(--color-ink);"
                                                                title="Ganti Penilai">
                                                            Ganti Penilai
                                                        </button>

                                                        <form method="POST" action="{{ route('admin.penugasan.destroy', ['kode' => $sekolah->kode, 'penugasan' => $item]) }}"
                                                              onsubmit="return confirm('Hapus penugasan untuk {{ addslashes($item->guru->nama) }}? Empat baris penilaian supervisi yang belum dinilai juga akan dihapus.');">
                                                            @csrf
                                                            @method('DELETE')
                                                            <button type="submit" class="px-2.5 py-1 text-xs font-semibold rounded text-white transition-colors"
                                                                    style="background-color: var(--color-action-delete);"
                                                                    title="Hapus Penugasan">
                                                                Hapus
                                                            </button>
                                                        </form>
                                                    @else
                                                        @php
                                                            $penilaianFinalList = $item->penilaian->filter(fn($p) => $p->isFinal());
                                                        @endphp
                                                        @if($penilaianFinalList->isNotEmpty() && !$periode->isDitutup())
                                                            <span class="text-xs text-muted">Buka kunci via badge final</span>
                                                        @else
                                                            <span class="text-xs text-gray-400 italic" title="Penugasan tidak dapat diubah karena penilaian sudah dimulai atau periode telah ditutup">
                                                                Terkunci
                                                            </span>
                                                        @endif
                                                    @endif
                                                </div>
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>

                        @if($daftarPenugasan->hasPages())
                            <div class="p-4 border-t" style="border-color: var(--color-border);">
                                {{ $daftarPenugasan->links() }}
                            </div>
                        @endif
                    @endif
                </x-card>
            </div>

            {{-- TAB 2: REKAP PER SUPERVISOR --}}
            <div x-show="tab === 'rekap'" class="space-y-4" style="display: none;">
                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
                    @forelse($rekapSupervisor as $spv)
                        <x-card class="flex flex-col justify-between h-full">
                            <div>
                                <div class="flex items-start justify-between gap-3 pb-3 mb-3 border-b" style="border-color: var(--color-border);">
                                    <div>
                                        <h4 class="font-bold text-base" style="color: var(--color-navy-900);">
                                            {{ $spv->nama }}
                                        </h4>
                                        <p class="text-xs text-gray-500">NIP: {{ $spv->nip ?: '-' }}</p>
                                    </div>
                                    <span class="px-2.5 py-1 rounded-full text-xs font-bold bg-blue-100 text-blue-900">
                                        {{ $spv->penugasan_sebagai_penilai_count }} Guru
                                    </span>
                                </div>

                                {{-- Daftar Guru yang Dinilai --}}
                                <div class="space-y-1.5 text-xs mb-3">
                                    <span class="block font-semibold text-gray-600 mb-1">Guru yang disupervisi:</span>
                                    @forelse($spv->penugasanSebagaiPenilai as $tugas)
                                        <div class="p-2 rounded bg-gray-50 border flex items-center justify-between" style="border-color: var(--color-border);">
                                            <span class="font-medium text-gray-800">{{ $tugas->guru->nama }}</span>
                                            <span class="text-gray-400 text-xs">{{ $tugas->guru->nip ?: '' }}</span>
                                        </div>
                                    @empty
                                        <p class="text-gray-400 italic">Belum ada guru yang ditugaskan kepada supervisor ini.</p>
                                    @endforelse
                                </div>
                            </div>
                        </x-card>
                    @empty
                        <div class="col-span-3">
                            <x-empty-state
                                title="Belum Ada Supervisor Aktif"
                                description="Pastikan ada pengguna sekolah yang telah diberi peran Supervisor di menu Data Guru."
                            />
                        </div>
                    @endforelse
                </div>
            </div>

            {{-- MODAL GANTI PENILAI --}}
            <div x-show="modalGantiPenilai" class="fixed inset-0 z-50 overflow-y-auto bg-black/40 flex items-center justify-center p-4" style="display: none;">
                <div @click.outside="modalGantiPenilai = false" class="bg-white rounded-xl max-w-md w-full p-6 shadow-xl border" style="border-color: var(--color-border);">
                    <h3 class="text-lg font-bold mb-3" style="color: var(--color-navy-900);">Ganti Supervisor Penilai</h3>
                    <p class="text-xs text-gray-600 mb-4">
                        Pilih supervisor pengganti untuk guru <strong x-text="penugasanAktif.namaGuru"></strong>. Penggantian hanya diizinkan bila keempat penilaian supervisi masih berstatus "Belum".
                    </p>

                    <form :action="'{{ url('/s/' . $sekolah->kode . '/admin/penugasan') }}/' + penugasanAktif.id + '/penilai'" method="POST">
                        @csrf
                        @method('PATCH')
                        <div class="mb-4">
                            <label class="block text-xs font-bold mb-1" style="color: var(--color-navy-900);">Supervisor Baru *</label>
                            <select name="penilai_id" required class="form-input text-sm w-full" x-model="penugasanAktif.penilaiId">
                                <option value="">Pilih Supervisor</option>
                                @foreach($supervisors as $spv)
                                    <option value="{{ $spv->id }}">{{ $spv->nama }} (NIP: {{ $spv->nip ?: '-' }})</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="flex justify-end gap-2">
                            <button type="button" @click="modalGantiPenilai = false" class="px-3 py-1.5 text-xs rounded border border-gray-300">Batal</button>
                            <x-button type="submit" variant="tambah" class="text-xs py-1.5">Simpan Penilai</x-button>
                        </div>
                    </form>
                </div>
            </div>

            {{-- MODAL BUKA KUNCI PENILAIAN (KHUSUS ADMIN) --}}
            <div x-show="modalBukaKunci" class="fixed inset-0 z-50 overflow-y-auto bg-black/40 flex items-center justify-center p-4" style="display: none;">
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
                        Membuka kunci instrumen <strong x-text="penilaianAktif.namaInstrumen"></strong> akan mengubah status menjadi <strong>Sedang Direvisi</strong> sehingga supervisor penilai dapat memperbarui skor atau catatan.
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
                                      placeholder="Misal: Perlu perbaikan skor butir 3 dan penambahan tindak lanjut sesuai kesepakatan refleksi supervisi..." 
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
