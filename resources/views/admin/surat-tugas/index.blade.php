<x-app-layout :namaSekolah="$sekolah->nama" title="Surat Tugas Supervisor">

    <x-page-header
        title="Surat Tugas Supervisor"
        subtitle="Terbitkan surat tugas supervisi akademik dan unduh dokumen resmi dalam format DOCX siap cetak."
    />

    {{-- Filter Periode & Ringkasan --}}
    <div class="mb-6 grid grid-cols-1 md:grid-cols-4 gap-4">
        {{-- Pemilih Periode --}}
        <x-card class="p-4 md:col-span-2">
            <form method="GET" action="{{ route('admin.surat-tugas.index', ['kode' => $sekolah->kode]) }}" id="form-pilih-periode">
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

        {{-- Statistik Ringkasan --}}
        <x-card class="p-4 text-center">
            <span class="block text-xs font-semibold uppercase tracking-wider" style="color: var(--color-muted);">Total Supervisor</span>
            <span class="block text-2xl font-bold mt-1" style="color: var(--color-navy-900);">
                {{ $daftarPenilai->count() }}
            </span>
            <span class="block text-xs mt-1 text-gray-500">Memiliki penugasan</span>
        </x-card>

        <x-card class="p-4 text-center">
            <span class="block text-xs font-semibold uppercase tracking-wider" style="color: var(--color-muted);">Surat Terbit</span>
            <span class="block text-2xl font-bold mt-1 text-green-700">
                {{ $daftarPenilai->where('status', 'terbit')->count() }}
            </span>
            @php
                $perluUlangCount = $daftarPenilai->where('status', 'perlu_ulang')->count();
            @endphp
            @if($perluUlangCount > 0)
                <span class="block text-xs mt-1 font-bold text-amber-700">
                    {{ $perluUlangCount }} perlu terbit ulang
                </span>
            @else
                <span class="block text-xs mt-1 text-gray-500">
                    {{ $daftarPenilai->where('status', 'belum')->count() }} belum diterbitkan
                </span>
            @endif
        </x-card>
    </div>

    @if(!$periode)
        <x-card class="p-8 text-center">
            <x-empty-state
                title="Belum Ada Periode Supervisi"
                description="Buat periode supervisi terlebih dahulu sebelum menerbitkan surat tugas."
            >
                <x-button variant="tambah" href="{{ route('admin.periode.index', ['kode' => $sekolah->kode]) }}">
                    Ke Halaman Periode
                </x-button>
            </x-empty-state>
        </x-card>
    @elseif($daftarPenilai->isEmpty())
        <x-card class="p-8 text-center">
            <x-empty-state
                title="Belum Ada Penugasan Supervisor"
                description="Belum ada supervisor yang ditugaskan membina guru pada periode ini. Silakan buat penugasan terlebih dahulu."
            >
                <x-button variant="tambah" href="{{ route('admin.penugasan.index', ['kode' => $sekolah->kode, 'periode_id' => $periode->id]) }}">
                    Ke Halaman Penugasan
                </x-button>
            </x-empty-state>
        </x-card>
    @else
        {{-- Kontainer Utama dengan Alpine.js untuk Modal Penerbitan --}}
        <div x-data="{
            modalTerbitkan: false,
            penilaiId: null,
            penilaiNama: '',
            penilaiNip: '',
            nomorSurat: '',
            tanggalSurat: '{{ date('Y-m-d') }}',
            penandatanganNama: @js($kepalaSekolah?->nama ?? ''),
            penandatanganNip: @js($kepalaSekolah?->nip ?? ''),
            penandatanganJabatan: 'Kepala Sekolah',
            guruList: [],
            isUlang: false,

            bukaModal(penilai, guruList, suratTugas, isUlang = false) {
                this.penilaiId = penilai.id;
                this.penilaiNama = penilai.nama;
                this.penilaiNip = penilai.nip || penilai.nuptk || '-';
                this.guruList = guruList;
                this.isUlang = isUlang;

                if (suratTugas) {
                    this.nomorSurat = suratTugas.nomor_surat || '';
                    this.tanggalSurat = suratTugas.tanggal_surat ? suratTugas.tanggal_surat.substring(0, 10) : '{{ date('Y-m-d') }}';
                    this.penandatanganNama = suratTugas.penandatangan_nama || @js($kepalaSekolah?->nama ?? '');
                    this.penandatanganNip = suratTugas.penandatangan_nip || @js($kepalaSekolah?->nip ?? '');
                    this.penandatanganJabatan = suratTugas.penandatangan_jabatan || 'Kepala Sekolah';
                } else {
                    this.nomorSurat = '';
                    this.tanggalSurat = '{{ date('Y-m-d') }}';
                    this.penandatanganNama = @js($kepalaSekolah?->nama ?? '');
                    this.penandatanganNip = @js($kepalaSekolah?->nip ?? '');
                    this.penandatanganJabatan = 'Kepala Sekolah';
                }

                this.modalTerbitkan = true;
            }
        }">
            {{-- Tabel Daftar Penilai & Surat Tugas --}}
            <x-card class="overflow-hidden">
                <div class="px-6 py-4 border-b flex flex-wrap items-center justify-between gap-4" style="border-color: var(--color-border); background-color: var(--color-navy-50);">
                    <div>
                        <h2 class="text-base font-bold" style="color: var(--color-navy-900);">
                            Daftar Surat Tugas Supervisor ({{ $daftarPenilai->count() }})
                        </h2>
                        <p class="text-xs" style="color: var(--color-muted);">
                            Setiap supervisor dapat diterbitkan satu surat tugas resmi yang memuat seluruh guru binaannya.
                        </p>
                    </div>
                </div>

                <div class="overflow-x-auto">
                    <table class="w-full text-left border-collapse text-sm">
                        <thead>
                            <tr class="border-b text-xs font-bold uppercase tracking-wider" style="border-color: var(--color-border); background: var(--color-cream); color: var(--color-navy-900);">
                                <th class="py-3 px-4 w-12 text-center">No</th>
                                <th class="py-3 px-4">Supervisor (Penilai)</th>
                                <th class="py-3 px-4">Guru Dibina</th>
                                <th class="py-3 px-4">Status Surat</th>
                                <th class="py-3 px-4">Nomor & Tanggal Surat</th>
                                <th class="py-3 px-4 text-center w-64">Aksi</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y" style="divide-color: var(--color-border);">
                            @foreach($daftarPenilai as $index => $item)
                                @php
                                    $penilai = $item['penilai'];
                                    $surat = $item['surat_tugas'];
                                    $status = $item['status'];
                                @endphp
                                <tr class="hover:bg-amber-50/20 transition-colors {{ $status === 'perlu_ulang' ? 'bg-amber-50/40' : '' }}">
                                    <td class="py-3.5 px-4 text-center font-medium text-gray-500">
                                        {{ $index + 1 }}
                                    </td>
                                    <td class="py-3.5 px-4">
                                        <div class="font-bold text-base" style="color: var(--color-navy-900);">
                                            {{ $penilai->nama }}
                                        </div>
                                        <div class="text-xs text-gray-500 mt-0.5">
                                            @if($penilai->nip)
                                                NIP. {{ $penilai->nip }}
                                            @elseif($penilai->nuptk)
                                                NUPTK. {{ $penilai->nuptk }}
                                            @else
                                                <span class="italic text-gray-400">Tanpa NIP/NUPTK</span>
                                            @endif
                                        </div>
                                    </td>
                                    <td class="py-3.5 px-4">
                                        <div class="font-semibold" style="color: var(--color-ink);">
                                            {{ $item['jumlah_guru'] }} Guru
                                        </div>
                                        <div class="text-xs text-gray-500 mt-1 max-w-xs truncate" title="{{ implode(', ', array_column($item['guru_list'], 'nama')) }}">
                                            {{ implode(', ', array_column($item['guru_list'], 'nama')) }}
                                        </div>
                                    </td>
                                    <td class="py-3.5 px-4">
                                        @if($status === 'belum')
                                            <span class="inline-flex items-center px-2.5 py-1 rounded-full text-xs font-semibold" style="background-color: #EEF0F4; color: #4B5563;">
                                                Belum Terbit
                                            </span>
                                        @elseif($status === 'terbit')
                                            <span class="inline-flex items-center px-2.5 py-1 rounded-full text-xs font-semibold" style="background-color: #E8F5E9; color: #1B5E20;">
                                                Terbit
                                            </span>
                                        @else
                                            <div class="space-y-1">
                                                <span class="inline-flex items-center px-2.5 py-1 rounded-full text-xs font-semibold" style="background-color: #FFF3D6; color: #8A5A00;">
                                                    Perlu Diterbitkan Ulang
                                                </span>
                                                <p class="text-[11px] leading-tight text-amber-800">
                                                    Penugasan guru telah berubah dari saat surat diterbitkan.
                                                </p>
                                            </div>
                                        @endif
                                    </td>
                                    <td class="py-3.5 px-4">
                                        @if($surat)
                                            <div class="font-medium" style="color: var(--color-ink);">
                                                {{ $surat->nomor_surat }}
                                            </div>
                                            <div class="text-xs text-gray-500 mt-0.5">
                                                {{ $surat->tanggal_surat->locale('id')->isoFormat('D MMMM Y') }}
                                            </div>
                                        @else
                                            <span class="text-xs text-gray-400 italic">&mdash;</span>
                                        @endif
                                    </td>
                                    <td class="py-3.5 px-4 text-center">
                                        <div class="flex items-center justify-center gap-2 flex-wrap">
                                            @if($status === 'belum')
                                                <button
                                                    type="button"
                                                    @click="bukaModal({{ json_encode($penilai) }}, {{ json_encode($item['guru_list']) }}, null, false)"
                                                    class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-lg text-xs font-semibold text-white shadow-sm transition-all focus:outline-none focus:ring-2 focus:ring-offset-1 focus:ring-green-600"
                                                    style="background-color: var(--color-action-green);"
                                                >
                                                    <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 20 20" fill="currentColor">
                                                        <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm1-11a1 1 0 10-2 0v2H7a1 1 0 100 2h2v2a1 1 0 102 0v-2h2a1 1 0 100-2h-2V7z" clip-rule="evenodd"/>
                                                    </svg>
                                                    Terbitkan Surat
                                                </button>
                                            @elseif($status === 'terbit')
                                                <a
                                                    href="{{ route('admin.surat-tugas.download', ['kode' => $sekolah->kode, 'suratTugas' => $surat->ulid]) }}"
                                                    download
                                                    class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-lg text-xs font-semibold text-white shadow-sm transition-all hover:opacity-95 focus:outline-none focus:ring-2 focus:ring-offset-1 focus:ring-blue-600"
                                                    style="background-color: var(--color-navy-900);"
                                                    title="Unduh dokumen DOCX resmi"
                                                >
                                                    <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 20 20" fill="currentColor">
                                                        <path fill-rule="evenodd" d="M3 17a1 1 0 011-1h12a1 1 0 110 2H4a1 1 0 01-1-1zm3.293-7.707a1 1 0 011.414 0L9 10.586V3a1 1 0 112 0v7.586l1.293-1.293a1 1 0 111.414 1.414l-3 3a1 1 0 01-1.414 0l-3-3a1 1 0 010-1.414z" clip-rule="evenodd"/>
                                                    </svg>
                                                    Unduh DOCX
                                                </a>
                                                <button
                                                    type="button"
                                                    @click="bukaModal({{ json_encode($penilai) }}, {{ json_encode($item['guru_list']) }}, {{ json_encode($surat) }}, false)"
                                                    class="inline-flex items-center gap-1 px-2.5 py-1.5 rounded-lg text-xs font-semibold transition-all border hover:bg-yellow-50 focus:outline-none"
                                                    style="color: #78350F; border-color: #FCD34D;"
                                                    title="Ubah nomor atau penandatangan"
                                                >
                                                    <svg xmlns="http://www.w3.org/2000/svg" width="13" height="13" viewBox="0 0 20 20" fill="currentColor">
                                                        <path d="M13.586 3.586a2 2 0 112.828 2.828l-.793.793-2.828-2.828.793-.793zM11.379 5.793L3 14.172V17h2.828l8.38-8.379-2.83-2.828z" />
                                                    </svg>
                                                    Ubah
                                                </button>
                                            @else
                                                 {{-- Status: Perlu Diterbitkan Ulang --}}
                                                <button
                                                    type="button"
                                                    @click="bukaModal({{ json_encode($penilai) }}, {{ json_encode($item['guru_list']) }}, {{ json_encode($surat) }}, true)"
                                                    class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-lg text-xs font-semibold text-white shadow-sm transition-all focus:outline-none focus:ring-2 focus:ring-offset-1 focus:ring-amber-600"
                                                    style="background-color: #D97706;"
                                                >
                                                    <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 20 20" fill="currentColor">
                                                        <path fill-rule="evenodd" d="M4 2a1 1 0 011 1v2.101a7.002 7.002 0 0111.601 2.566 1 1 0 11-1.885.666A5.002 5.002 0 005.999 7H9a1 1 0 010 2H4a1 1 0 01-1-1V3a1 1 0 011-1zm.008 9.057a1 1 0 011.276.61A5.002 5.002 0 0014.001 13H11a1 1 0 110-2h5a1 1 0 011 1v5a1 1 0 11-2 0v-2.101a7.002 7.002 0 01-11.601-2.566 1 1 0 01.61-1.276z" clip-rule="evenodd"/>
                                                    </svg>
                                                    Terbitkan Ulang
                                                </button>
                                                <a
                                                    href="{{ route('admin.surat-tugas.download', ['kode' => $sekolah->kode, 'suratTugas' => $surat->ulid]) }}"
                                                    download
                                                    class="inline-flex items-center gap-1 px-2.5 py-1.5 rounded-lg text-xs font-semibold transition-all border hover:bg-gray-50 focus:outline-none"
                                                    style="color: var(--color-navy-900); border-color: var(--color-navy-900);"
                                                    title="Unduh versi lama sebelum diperbarui"
                                                >
                                                    Unduh Lama
                                                </a>
                                            @endif
                                        </div>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </x-card>

            {{-- Modal Form Terbitkan / Terbitkan Ulang Surat Tugas --}}
            <div
                x-show="modalTerbitkan"
                class="fixed inset-0 z-50 overflow-y-auto"
                style="display: none;"
                aria-labelledby="modal-terbitkan-title"
                role="dialog"
                aria-modal="true"
            >
                {{-- Backdrop --}}
                <div
                    x-show="modalTerbitkan"
                    x-transition:enter="ease-out duration-200"
                    x-transition:enter-start="opacity-0"
                    x-transition:enter-end="opacity-100"
                    x-transition:leave="ease-in duration-150"
                    x-transition:leave-start="opacity-100"
                    x-transition:leave-end="opacity-0"
                    class="fixed inset-0 bg-black/40 backdrop-blur-xs transition-opacity"
                    @click="modalTerbitkan = false"
                ></div>

                <div class="flex min-h-full items-center justify-center p-4 text-center sm:p-0">
                    <div
                        x-show="modalTerbitkan"
                        x-transition:enter="ease-out duration-200"
                        x-transition:enter-start="opacity-0 translate-y-4 sm:scale-95"
                        x-transition:enter-end="opacity-100 translate-y-0 sm:scale-100"
                        x-transition:leave="ease-in duration-150"
                        x-transition:leave-start="opacity-100 translate-y-0 sm:scale-100"
                        x-transition:leave-end="opacity-0 translate-y-4 sm:scale-95"
                        class="relative transform overflow-hidden rounded-2xl text-left shadow-2xl transition-all sm:my-8 sm:w-full sm:max-w-2xl"
                        style="background: #ffffff; border: 1px solid var(--color-border);"
                    >
                        {{-- Form POST ke SuratTugasController@store --}}
                        <form
                            :action="'{{ url('/s/'.$sekolah->kode.'/admin/surat-tugas/'.$periode->id) }}/' + penilaiId"
                            method="POST"
                        >
                            @csrf

                            {{-- Modal Header --}}
                            <div class="px-6 py-4 border-b flex items-center justify-between" style="border-color: var(--color-border); background-color: var(--color-navy-50);">
                                <div>
                                    <h3 class="text-lg font-bold" id="modal-terbitkan-title" style="color: var(--color-navy-900);">
                                        <span x-text="isUlang ? 'Terbitkan Ulang Surat Tugas' : 'Terbitkan Surat Tugas'"></span>
                                    </h3>
                                    <p class="text-xs" style="color: var(--color-muted);">
                                        Supervisor: <strong class="text-navy-900" x-text="penilaiNama"></strong> &bull; NIP: <span x-text="penilaiNip"></span>
                                    </p>
                                </div>
                                <button
                                    type="button"
                                    @click="modalTerbitkan = false"
                                    class="p-1 rounded-lg text-gray-400 hover:text-gray-600 transition-colors"
                                >
                                    <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 20 20" fill="currentColor">
                                        <path fill-rule="evenodd" d="M4.293 4.293a1 1 0 011.414 0L10 8.586l4.293-4.293a1 1 0 111.414 1.414L11.414 10l4.293 4.293a1 1 0 01-1.414 1.414L10 11.414l-4.293 4.293a1 1 0 01-1.414-1.414L8.586 10 4.293 5.707a1 1 0 010-1.414z" clip-rule="evenodd" />
                                    </svg>
                                </button>
                            </div>

                            {{-- Modal Body --}}
                            <div class="px-6 py-5 space-y-4 max-h-[70vh] overflow-y-auto">
                                <template x-if="isUlang">
                                    <div class="p-3 rounded-lg border border-amber-300 bg-amber-50 text-xs text-amber-900">
                                        <strong>Pemberitahuan Penerbitan Ulang:</strong>
                                        Daftar guru binaan telah mengalami perubahan sejak surat tugas sebelumnya diterbitkan.
                                        Menerbitkan ulang akan memperbarui snapshot daftar guru dengan data penugasan aktif saat ini.
                                    </div>
                                </template>

                                {{-- Nomor Surat & Tanggal Surat --}}
                                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                                    <div>
                                        <label for="nomor_surat" class="block text-xs font-bold uppercase tracking-wider mb-1" style="color: var(--color-navy-900);">
                                            Nomor Surat <span class="text-red-600">*</span>
                                        </label>
                                        <input
                                            type="text"
                                            id="nomor_surat"
                                            name="nomor_surat"
                                            x-model="nomorSurat"
                                            required
                                            placeholder="Contoh: 800/042/SMK-SPV/X/2026"
                                            class="form-input text-sm w-full"
                                        />
                                        <p class="text-[11px] text-gray-500 mt-1">Nomor surat resmi sekolah (teks bebas).</p>
                                    </div>

                                    <div>
                                        <label for="tanggal_surat" class="block text-xs font-bold uppercase tracking-wider mb-1" style="color: var(--color-navy-900);">
                                            Tanggal Surat <span class="text-red-600">*</span>
                                        </label>
                                        <input
                                            type="date"
                                            id="tanggal_surat"
                                            name="tanggal_surat"
                                            x-model="tanggalSurat"
                                            required
                                            class="form-input text-sm w-full"
                                        />
                                        <p class="text-[11px] text-gray-500 mt-1">Tanggal penetapan surat tugas.</p>
                                    </div>
                                </div>

                                {{-- Identitas Penandatangan --}}
                                <div class="p-4 rounded-xl border space-y-3" style="border-color: var(--color-border); background-color: var(--color-cream);">
                                    <div class="flex items-center justify-between">
                                        <span class="text-xs font-bold uppercase tracking-wider" style="color: var(--color-navy-900);">
                                            Identitas Penandatangan Surat
                                        </span>
                                        <span class="text-[11px] text-gray-500 italic">
                                            (Terisi otomatis dari profil kepala sekolah)
                                        </span>
                                    </div>

                                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                                        <div>
                                            <label for="penandatangan_nama" class="block text-xs font-semibold mb-1 text-gray-700">
                                                Nama Lengkap <span class="text-red-600">*</span>
                                            </label>
                                            <input
                                                type="text"
                                                id="penandatangan_nama"
                                                name="penandatangan_nama"
                                                x-model="penandatanganNama"
                                                required
                                                class="form-input text-sm w-full"
                                            />
                                        </div>

                                        <div>
                                            <label for="penandatangan_nip" class="block text-xs font-semibold mb-1 text-gray-700">
                                                NIP Penandatangan (Opsional)
                                            </label>
                                            <input
                                                type="text"
                                                id="penandatangan_nip"
                                                name="penandatangan_nip"
                                                x-model="penandatanganNip"
                                                placeholder="Kosongkan jika bukan ASN"
                                                class="form-input text-sm w-full"
                                            />
                                        </div>

                                        <div class="sm:col-span-2">
                                            <label for="penandatangan_jabatan" class="block text-xs font-semibold mb-1 text-gray-700">
                                                Jabatan Penandatangan
                                            </label>
                                            <input
                                                type="text"
                                                id="penandatangan_jabatan"
                                                name="penandatangan_jabatan"
                                                x-model="penandatanganJabatan"
                                                placeholder="Kepala Sekolah"
                                                class="form-input text-sm w-full"
                                            />
                                        </div>
                                    </div>
                                </div>

                                {{-- Pratinjau Snapshot Daftar Guru --}}
                                <div>
                                    <div class="flex items-center justify-between mb-1.5">
                                        <label class="block text-xs font-bold uppercase tracking-wider" style="color: var(--color-navy-900);">
                                            Snapshot Guru yang Ditugaskan (<span x-text="guruList.length"></span> Guru)
                                        </label>
                                        <span class="text-[11px] text-gray-500">Akan tercetak pada tabel dokumen DOCX</span>
                                    </div>

                                    <div class="border rounded-xl overflow-hidden max-h-48 overflow-y-auto" style="border-color: var(--color-border);">
                                        <table class="w-full text-left text-xs">
                                            <thead class="bg-gray-100 border-b font-semibold text-gray-700">
                                                <tr>
                                                    <th class="py-2 px-3 w-10 text-center">No</th>
                                                    <th class="py-2 px-3">Nama Guru</th>
                                                    <th class="py-2 px-3">NIP / NUPTK</th>
                                                </tr>
                                            </thead>
                                            <tbody class="divide-y divide-gray-100">
                                                <template x-for="(guru, idx) in guruList" :key="guru.id">
                                                    <tr class="hover:bg-gray-50">
                                                        <td class="py-1.5 px-3 text-center text-gray-500" x-text="idx + 1"></td>
                                                        <td class="py-1.5 px-3 font-medium text-gray-900" x-text="guru.nama"></td>
                                                        <td class="py-1.5 px-3 text-gray-500" x-text="guru.nip || guru.nuptk || '-'"></td>
                                                    </tr>
                                                </template>
                                            </tbody>
                                        </table>
                                    </div>
                                </div>
                            </div>

                            {{-- Modal Footer --}}
                            <div class="px-6 py-4 border-t flex items-center justify-end gap-3" style="border-color: var(--color-border); background-color: var(--color-cream);">
                                <x-button type="button" variant="sekunder" @click="modalTerbitkan = false">
                                    Batal
                                </x-button>
                                <x-button type="submit" variant="tambah">
                                    <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 20 20" fill="currentColor">
                                        <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"/>
                                    </svg>
                                    <span x-text="isUlang ? 'Simpan & Terbitkan Ulang' : 'Simpan & Terbitkan Surat'"></span>
                                </x-button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    @endif

</x-app-layout>
