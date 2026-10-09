<x-app-layout :namaSekolah="$sekolah->nama" title="Pengajuan & Informasi Supervisi">

    <x-page-header
        title="{{ $penugasan ? 'Informasi & Jadwal Supervisi' : 'Pengajuan Supervisi Akademik' }}"
        subtitle="Pilih supervisor penilai, lengkapi informasi kelas, mata pelajaran, serta usulan jadwal observasi supervisi."
    >
        <x-slot:actions>
            <x-button variant="sekunder" href="{{ route('guru.dashboard', ['kode' => $sekolah->kode]) }}">
                &larr; Kembali ke Dasbor
            </x-button>
        </x-slot:actions>
    </x-page-header>

    {{-- Banner Status Penguncian / Pengajuan Form --}}
    @if(!$penugasan)
        <div class="mb-6 p-4 rounded-xl border flex items-start gap-3" style="background-color: var(--color-navy-50); border-color: var(--color-border);">
            <svg class="w-5 h-5 shrink-0 mt-0.5" style="color: var(--color-navy-900);" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor">
                <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7-4a1 1 0 11-2 0 1 1 0 012 0zM9 9a1 1 0 000 2v3a1 1 0 001 1h1a1 1 0 100-2v-3a1 1 0 00-1-1H9z" clip-rule="evenodd" />
            </svg>
            <p class="text-xs leading-relaxed" style="color: var(--color-navy-900);">
                Anda sedang melakukan pengajuan supervisi pada periode aktif <strong>{{ $periode->nama }}</strong>.
                Silakan pilih supervisor penilai Anda serta ajukan mata pelajaran dan kelas yang akan diobservasi.
                <strong>Pilihan supervisor akan langsung aktif dan terkunci setelah formulir diajukan.</strong>
            </p>
        </div>
    @elseif(!$canEdit)
        <div class="mb-6 p-4 rounded-xl border flex items-start gap-3" style="background-color: #FFF3D6; border-color: #FFE082;">
            <svg class="w-6 h-6 text-amber-800 shrink-0 mt-0.5" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor">
                <path fill-rule="evenodd" d="M5 9V7a5 5 0 0110 0v2a2 2 0 012 2v5a2 2 0 01-2 2H5a2 2 0 01-2-2v-5a2 2 0 012-2zm8-2v2H7V7a3 3 0 016 0z" clip-rule="evenodd" />
            </svg>
            <div>
                <h3 class="text-sm font-bold text-amber-900">Formulir Telah Dikunci (Hanya-Baca)</h3>
                <p class="text-xs text-amber-800 mt-0.5 leading-relaxed">
                    Salah satu atau seluruh tahapan supervisi telah dimulai oleh supervisor Anda (<strong>{{ $penugasan->penilai->nama }}</strong>).
                    Untuk menjaga keabsahan data, pengubahan informasi dan jadwal oleh guru telah dikunci.
                    Jika terdapat perbaikan kelas atau jadwal, silakan berkoordinasi langsung dengan supervisor atau admin sekolah.
                </p>
            </div>
        </div>
    @else
        <div class="mb-6 p-4 rounded-xl border flex items-start gap-3" style="background-color: var(--color-navy-50); border-color: var(--color-border);">
            <svg class="w-5 h-5 shrink-0 mt-0.5" style="color: var(--color-navy-900);" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor">
                <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7-4a1 1 0 11-2 0 1 1 0 012 0zM9 9a1 1 0 000 2v3a1 1 0 001 1h1a1 1 0 100-2v-3a1 1 0 00-1-1H9z" clip-rule="evenodd" />
            </svg>
            <p class="text-xs leading-relaxed" style="color: var(--color-navy-900);">
                Data ini diisi <strong>ulang pada setiap periode supervisi</strong> dan melekat pada penugasan periode berjalan (<strong>{{ $periode->nama }}</strong>).
                Anda bebas memperbarui kelas, mata pelajaran, dan jadwal hingga proses penilaian pertama dimulai oleh supervisor Anda.
            </p>
        </div>
    @endif

    <form method="POST" action="{{ route('guru.info-jadwal.simpan', ['kode' => $sekolah->kode]) }}" class="space-y-6">
        @csrf

        {{-- BAGIAN 1: SUPERVISOR PENILAI --}}
        <x-card class="p-6 md:p-8">
            <div class="border-b pb-4 mb-6 flex items-center justify-between" style="border-color: var(--color-border);">
                <div>
                    <h2 class="text-lg font-bold" style="color: var(--color-navy-900);">
                        1. Supervisor Penilai
                    </h2>
                    <p class="text-xs text-gray-500 mt-0.5">
                        {{ $penugasan ? 'Supervisor penilai yang mengobservasi pembelajaran Anda pada periode ini.' : 'Pilih supervisor yang akan mendampingi dan menilai kegiatan supervisi akademik Anda.' }}
                    </p>
                </div>
                @if($penugasan)
                    <span class="inline-flex items-center px-2.5 py-1 rounded-full text-xs font-semibold bg-green-100 text-green-800">
                        Telah Ditetapkan
                    </span>
                @endif
            </div>

            @if(!$penugasan)
                <div>
                    <label for="penilai_id" class="block text-xs font-bold uppercase tracking-wider mb-1" style="color: var(--color-navy-900);">
                        Pilih Supervisor <span class="text-red-600">*</span>
                    </label>
                    <select
                        name="penilai_id"
                        id="penilai_id"
                        required
                        class="form-input text-sm w-full md:w-2/3"
                    >
                        <option value="">-- Pilih Salah Satu Supervisor --</option>
                        @foreach($supervisors as $spv)
                            <option value="{{ $spv->id }}" {{ (string) old('penilai_id') === (string) $spv->id ? 'selected' : '' }}>
                                {{ $spv->nama }} (NIP: {{ $spv->nip ?: '-' }})
                            </option>
                        @endforeach
                    </select>
                    @error('penilai_id')
                        <p class="text-xs text-red-600 mt-1">{{ $message }}</p>
                    @enderror
                    <p class="text-xs text-gray-500 mt-2">
                        Pilihan supervisor akan langsung aktif dan terkunci setelah diajukan. Penggantian supervisor selanjutnya hanya dapat dilakukan oleh admin sekolah.
                    </p>
                </div>
            @else
                <div class="p-4 rounded-xl border flex flex-col sm:flex-row sm:items-center justify-between gap-3 bg-gray-50/70" style="border-color: var(--color-border);">
                    <div>
                        <h3 class="font-bold text-base" style="color: var(--color-navy-900);">
                            {{ $penugasan->penilai->nama }}
                        </h3>
                        <p class="text-xs text-gray-500 mt-0.5">
                            NIP: {{ $penugasan->penilai->nip ?: '-' }} &bull; Peran: Supervisor Penilai
                        </p>
                    </div>
                    <span class="text-xs text-gray-400 italic">
                        Terkunci (Penggantian hanya via Admin)
                    </span>
                </div>
            @endif
        </x-card>

        {{-- BAGIAN 2: INFORMASI KELAS & MATAPELAJARAN --}}
        <x-card class="p-6 md:p-8">
            <div class="border-b pb-4 mb-6" style="border-color: var(--color-border);">
                <h2 class="text-lg font-bold" style="color: var(--color-navy-900);">
                    2. Informasi Kelas & Pembelajaran
                </h2>
                <p class="text-xs text-gray-500 mt-0.5">
                    Informasi subjek kelas dan materi yang diajukan untuk diobservasi dalam kegiatan supervisi akademik.
                </p>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-3 gap-5">
                {{-- Kelas --}}
                <div>
                    <label for="kelas" class="block text-xs font-bold uppercase tracking-wider mb-1" style="color: var(--color-navy-900);">
                        Kelas / Rombel <span class="text-red-600">*</span>
                    </label>
                    <input
                        type="text"
                        name="kelas"
                        id="kelas"
                        required
                        {{ !$canEdit ? 'disabled' : '' }}
                        value="{{ old('kelas', $info?->kelas) }}"
                        placeholder="Contoh: X TJKT 1"
                        class="form-input text-sm w-full {{ !$canEdit ? 'bg-gray-100 cursor-not-allowed' : '' }}"
                    />
                    @error('kelas')
                        <p class="text-xs text-red-600 mt-1">{{ $message }}</p>
                    @enderror
                </div>

                {{-- Semester --}}
                <div>
                    <label for="semester" class="block text-xs font-bold uppercase tracking-wider mb-1" style="color: var(--color-navy-900);">
                        Semester
                    </label>
                    <select
                        name="semester"
                        id="semester"
                        {{ !$canEdit ? 'disabled' : '' }}
                        class="form-input text-sm w-full {{ !$canEdit ? 'bg-gray-100 cursor-not-allowed' : '' }}"
                    >
                        <option value="">Pilih Semester</option>
                        <option value="Ganjil" {{ old('semester', $info?->semester ?? $periode->semester) === 'Ganjil' ? 'selected' : '' }}>Ganjil</option>
                        <option value="Genap" {{ old('semester', $info?->semester ?? $periode->semester) === 'Genap' ? 'selected' : '' }}>Genap</option>
                    </select>
                    @error('semester')
                        <p class="text-xs text-red-600 mt-1">{{ $message }}</p>
                    @enderror
                </div>

                {{-- Fase --}}
                <div>
                    <label for="fase" class="block text-xs font-bold uppercase tracking-wider mb-1" style="color: var(--color-navy-900);">
                        Fase Kurikulum
                    </label>
                    <select
                        name="fase"
                        id="fase"
                        {{ !$canEdit ? 'disabled' : '' }}
                        class="form-input text-sm w-full {{ !$canEdit ? 'bg-gray-100 cursor-not-allowed' : '' }}"
                    >
                        <option value="">Pilih Fase</option>
                        <option value="E (Kelas X)" {{ old('fase', $info?->fase) === 'E (Kelas X)' || old('fase', $info?->fase) === 'E' ? 'selected' : '' }}>Fase E (Kelas X)</option>
                        <option value="F (Kelas XI & XII)" {{ old('fase', $info?->fase) === 'F (Kelas XI & XII)' || old('fase', $info?->fase) === 'F' ? 'selected' : '' }}>Fase F (Kelas XI & XII)</option>
                    </select>
                    @error('fase')
                        <p class="text-xs text-red-600 mt-1">{{ $message }}</p>
                    @enderror
                </div>

                {{-- Mata Pelajaran --}}
                <div class="md:col-span-2">
                    <label for="mata_pelajaran" class="block text-xs font-bold uppercase tracking-wider mb-1" style="color: var(--color-navy-900);">
                        Mata Pelajaran <span class="text-red-600">*</span>
                    </label>
                    <input
                        type="text"
                        name="mata_pelajaran"
                        id="mata_pelajaran"
                        required
                        {{ !$canEdit ? 'disabled' : '' }}
                        value="{{ old('mata_pelajaran', $info?->mata_pelajaran) }}"
                        placeholder="Contoh: Konsentrasi Keahlian Teknik Komputer dan Jaringan"
                        class="form-input text-sm w-full {{ !$canEdit ? 'bg-gray-100 cursor-not-allowed' : '' }}"
                    />
                    @error('mata_pelajaran')
                        <p class="text-xs text-red-600 mt-1">{{ $message }}</p>
                    @enderror
                </div>

                {{-- Elemen / Materi Pokok --}}
                <div>
                    <label for="elemen" class="block text-xs font-bold uppercase tracking-wider mb-1" style="color: var(--color-navy-900);">
                        Elemen / Materi Pokok
                    </label>
                    <input
                        type="text"
                        name="elemen"
                        id="elemen"
                        {{ !$canEdit ? 'disabled' : '' }}
                        value="{{ old('elemen', $info?->elemen) }}"
                        placeholder="Contoh: Administrasi Server Jaringan"
                        class="form-input text-sm w-full {{ !$canEdit ? 'bg-gray-100 cursor-not-allowed' : '' }}"
                    />
                    @error('elemen')
                        <p class="text-xs text-red-600 mt-1">{{ $message }}</p>
                    @enderror
                </div>

                {{-- Capaian Pembelajaran (CP) --}}
                <div class="md:col-span-3">
                    <label for="cp" class="block text-xs font-bold uppercase tracking-wider mb-1" style="color: var(--color-navy-900);">
                        Capaian Pembelajaran (CP) / Tujuan Pembelajaran
                    </label>
                    <textarea
                        name="cp"
                        id="cp"
                        rows="3"
                        {{ !$canEdit ? 'disabled' : '' }}
                        placeholder="Tuliskan tujuan pembelajaran atau CP yang hendak dicapai dalam sesi pembelajaran ini..."
                        class="form-input text-sm w-full {{ !$canEdit ? 'bg-gray-100 cursor-not-allowed' : '' }}"
                    >{{ old('cp', $info?->cp) }}</textarea>
                    @error('cp')
                        <p class="text-xs text-red-600 mt-1">{{ $message }}</p>
                    @enderror
                </div>

                {{-- Catatan --}}
                <div class="md:col-span-3">
                    <label for="catatan" class="block text-xs font-bold uppercase tracking-wider mb-1" style="color: var(--color-navy-900);">
                        Catatan Khusus untuk Supervisor (Opsional)
                    </label>
                    <textarea
                        name="catatan"
                        id="catatan"
                        rows="2"
                        {{ !$canEdit ? 'disabled' : '' }}
                        placeholder="Misalnya: Observasi dilakukan di Ruang Lab Komputer 2, mohon hadir 5 menit sebelum KBM dimulai..."
                        class="form-input text-sm w-full {{ !$canEdit ? 'bg-gray-100 cursor-not-allowed' : '' }}"
                    >{{ old('catatan', $info?->catatan) }}</textarea>
                    @error('catatan')
                        <p class="text-xs text-red-600 mt-1">{{ $message }}</p>
                    @enderror
                </div>
            </div>
        </x-card>

        {{-- BAGIAN 3: JADWAL SUPERVISI PER INSTRUMEN --}}
        <x-card class="p-6 md:p-8">
            <div class="border-b pb-4 mb-6" style="border-color: var(--color-border);">
                <h2 class="text-lg font-bold" style="color: var(--color-navy-900);">
                    3. Usulan Jadwal Supervisi per Instrumen
                </h2>
                <p class="text-xs text-gray-500 mt-0.5">
                    Tentukan tanggal dan rentang jam pelaksanaan untuk setiap instrumen yang akan diobservasi oleh supervisor Anda.
                </p>
            </div>

            <div class="space-y-4">
                @foreach($jenisInstrumenList as $jenis)
                    @php
                        $jadwal = $jadwalMap->get($jenis->id);
                        $tglVal = old("jadwal.{$jenis->id}.tanggal", $jadwal?->tanggal?->format('Y-m-d'));
                        $mulaiVal = old("jadwal.{$jenis->id}.jam_mulai", $jadwal ? substr($jadwal->jam_mulai, 0, 5) : '');
                        $selesaiVal = old("jadwal.{$jenis->id}.jam_selesai", $jadwal ? substr($jadwal->jam_selesai, 0, 5) : '');
                    @endphp

                    <div class="p-4 rounded-xl border flex flex-col md:flex-row md:items-center justify-between gap-4" style="border-color: var(--color-border); background-color: var(--color-cream);">
                        <div class="md:w-1/3">
                            <span class="text-xs font-bold uppercase tracking-wider text-gray-400 block">
                                Instrumen {{ $jenis->urutan }}
                            </span>
                            <span class="text-base font-bold text-navy-900 block" style="color: var(--color-navy-900);">
                                {{ $jenis->nama }}
                            </span>
                        </div>

                        <div class="grid grid-cols-1 sm:grid-cols-3 gap-3 md:w-2/3">
                            {{-- Tanggal --}}
                            <div>
                                <label class="block text-[11px] font-semibold text-gray-600 mb-1">
                                    Tanggal Pelaksanaan
                                </label>
                                <input
                                    type="date"
                                    name="jadwal[{{ $jenis->id }}][tanggal]"
                                    value="{{ $tglVal }}"
                                    {{ !$canEdit ? 'disabled' : '' }}
                                    class="form-input text-xs w-full {{ !$canEdit ? 'bg-gray-100 cursor-not-allowed' : '' }}"
                                />
                            </div>

                            {{-- Jam Mulai --}}
                            <div>
                                <label class="block text-[11px] font-semibold text-gray-600 mb-1">
                                    Jam Mulai (WIB)
                                </label>
                                <input
                                    type="time"
                                    name="jadwal[{{ $jenis->id }}][jam_mulai]"
                                    value="{{ $mulaiVal }}"
                                    {{ !$canEdit ? 'disabled' : '' }}
                                    class="form-input text-xs w-full {{ !$canEdit ? 'bg-gray-100 cursor-not-allowed' : '' }}"
                                />
                            </div>

                            {{-- Jam Selesai --}}
                            <div>
                                <label class="block text-[11px] font-semibold text-gray-600 mb-1">
                                    Jam Selesai (WIB)
                                </label>
                                <input
                                    type="time"
                                    name="jadwal[{{ $jenis->id }}][jam_selesai]"
                                    value="{{ $selesaiVal }}"
                                    {{ !$canEdit ? 'disabled' : '' }}
                                    class="form-input text-xs w-full {{ !$canEdit ? 'bg-gray-100 cursor-not-allowed' : '' }}"
                                />
                            </div>
                        </div>
                    </div>
                @endforeach
            </div>
        </x-card>

        {{-- Tombol Aksi Simpan --}}
        @if($canEdit)
            <div class="flex items-center justify-end gap-3 pt-2">
                <x-button variant="sekunder" href="{{ route('guru.dashboard', ['kode' => $sekolah->kode]) }}">
                    Batal
                </x-button>
                <x-button type="submit" variant="tambah">
                    <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 20 20" fill="currentColor">
                        <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"/>
                    </svg>
                    {{ $penugasan ? 'Simpan Perubahan Informasi & Jadwal' : 'Ajukan Supervisi & Simpan Jadwal' }}
                </x-button>
            </div>
        @endif
    </form>

</x-app-layout>
