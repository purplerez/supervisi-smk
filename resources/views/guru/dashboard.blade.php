<x-app-layout :namaSekolah="$sekolah->nama" title="Dasbor Supervisi Guru">

    <x-page-header
        title="Supervisi Pembelajaran Saya"
        subtitle="Pantau alur dan perkembangan penilaian supervisi akademik Anda di {{ $sekolah->nama }}."
    >
        <x-slot:actions>
            @if($penugasan)
                <div class="flex items-center gap-2">
                    <x-button variant="sekunder" href="{{ route('guru.info-jadwal', ['kode' => $sekolah->kode]) }}">
                        <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 20 20" fill="currentColor">
                            <path d="M17.414 2.586a2 2 0 00-2.828 0L7 10.172V13h2.828l7.586-7.586a2 2 0 000-2.828z" />
                            <path fill-rule="evenodd" d="M2 6a2 2 0 012-2h4a1 1 0 010 2H4v10h10v-4a1 1 0 112 0v4a2 2 0 01-2 2H4a2 2 0 01-2-2V6z" clip-rule="evenodd" />
                        </svg>
                        {{ $canEdit ? 'Ubah Informasi & Jadwal' : 'Lihat Informasi & Jadwal' }}
                    </x-button>
                    <x-button variant="primer" href="{{ route('guru.rapor', ['kode' => $sekolah->kode, 'periode' => $penugasan->periode_id]) }}">
                        <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 20 20" fill="currentColor">
                            <path fill-rule="evenodd" d="M4 4a2 2 0 012-2h4.586A2 2 0 0112 2.586L15.414 6A2 2 0 0116 7.414V16a2 2 0 01-2 2H6a2 2 0 01-2-2V4zm2 6a1 1 0 011-1h6a1 1 0 110 2H7a1 1 0 01-1-1zm1 3a1 1 0 100 2h6a1 1 0 100-2H7z" clip-rule="evenodd" />
                        </svg>
                        Rapor Supervisi
                    </x-button>
                </div>
            @elseif($periodeAktif)
                <x-button variant="tambah" href="{{ route('guru.info-jadwal', ['kode' => $sekolah->kode]) }}">
                    <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 20 20" fill="currentColor">
                        <path d="M8 9a3 3 0 100-6 3 3 0 000 6zM8 11a6 6 0 016 6H2a6 6 0 016-6zM16 7a1 1 0 10-2 0v1h-1a1 1 0 100 2h1v1a1 1 0 102 0v-1h1a1 1 0 100-2h-1V7z" />
                    </svg>
                    Pilih Supervisor & Ajukan
                </x-button>
            @endif
        </x-slot:actions>
    </x-page-header>

    @if(!$periodeAktif)
        {{-- Kondisi 1: Tidak Ada Periode Aktif --}}
        <x-card class="p-8 text-center max-w-2xl mx-auto">
            <div class="w-16 h-16 rounded-full mx-auto flex items-center justify-center mb-4" style="background-color: var(--color-navy-50); color: var(--color-navy-900);">
                <svg xmlns="http://www.w3.org/2000/svg" width="32" height="32" viewBox="0 0 20 20" fill="currentColor">
                    <path fill-rule="evenodd" d="M6 2a1 1 0 00-1 1v1H4a2 2 0 00-2 2v10a2 2 0 002 2h12a2 2 0 002-2V6a2 2 0 00-2-2h-1V3a1 1 0 10-2 0v1H7V3a1 1 0 00-1-1zm0 5a1 1 0 000 2h8a1 1 0 100-2H6z" clip-rule="evenodd" />
                </svg>
            </div>
            <h2 class="text-xl font-bold mb-2" style="color: var(--color-navy-900);">Belum Ada Periode Supervisi Aktif</h2>
            <p class="text-sm leading-relaxed mb-6" style="color: var(--color-muted);">
                Saat ini pihak sekolah belum membuka periode pelaksanaan supervisi guru. Anda dapat memeriksa riwayat hasil supervisi periode sebelumnya.
            </p>
            <x-button variant="sekunder" href="{{ route('guru.riwayat', ['kode' => $sekolah->kode]) }}">
                Lihat Riwayat Supervisi Lalu &rarr;
            </x-button>
        </x-card>

    @elseif(!$penugasan)
        {{-- Kondisi 2: Ada Periode Aktif tapi Guru Belum Memilih Supervisor / Belum Mengajukan --}}
        <x-card class="p-8 text-center max-w-2xl mx-auto">
            <div class="w-16 h-16 rounded-full mx-auto flex items-center justify-center mb-4" style="background-color: #FFF3D6; color: #8A5A00;">
                <svg xmlns="http://www.w3.org/2000/svg" width="32" height="32" viewBox="0 0 20 20" fill="currentColor">
                    <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7-4a1 1 0 11-2 0 1 1 0 012 0zM9 9a1 1 0 000 2v3a1 1 0 001 1h1a1 1 0 100-2v-3a1 1 0 00-1-1H9z" clip-rule="evenodd" />
                </svg>
            </div>
            <h2 class="text-xl font-bold mb-2" style="color: var(--color-navy-900);">
                Halo, Bapak/Ibu {{ $guru->nama }}
            </h2>
            <p class="text-base leading-relaxed mb-3" style="color: var(--color-ink);">
                Anda belum memiliki penugasan supervisi pada periode aktif saat ini (<strong>{{ $periodeAktif->nama }}</strong>).
            </p>
            <p class="text-sm leading-relaxed mb-6" style="color: var(--color-muted);">
                Silakan pilih supervisor penilai Anda, lalu ajukan mata pelajaran dan kelas yang akan diobservasi dalam kegiatan supervisi akademik.
            </p>
            <div class="flex flex-col sm:flex-row items-center justify-center gap-3">
                <x-button variant="tambah" href="{{ route('guru.info-jadwal', ['kode' => $sekolah->kode]) }}">
                    <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 20 20" fill="currentColor">
                        <path d="M8 9a3 3 0 100-6 3 3 0 000 6zM8 11a6 6 0 016 6H2a6 6 0 016-6zM16 7a1 1 0 10-2 0v1h-1a1 1 0 100 2h1v1a1 1 0 102 0v-1h1a1 1 0 100-2h-1V7z" />
                    </svg>
                    Pilih Supervisor & Ajukan Supervisi &rarr;
                </x-button>
                <x-button variant="sekunder" href="{{ route('guru.riwayat', ['kode' => $sekolah->kode]) }}">
                    Buka Riwayat Sebelumnya
                </x-button>
            </div>
        </x-card>

    @else
        {{-- Kondisi 3: Guru Memiliki Penugasan pada Periode Aktif --}}

        {{-- Kartu Ringkasan Periode, Supervisor, Progres & Nilai Akhir --}}
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-6 mb-8">
            {{-- Informasi Periode --}}
            <x-card class="p-5 flex flex-col justify-between">
                <div>
                    <span class="block text-xs font-bold uppercase tracking-wider text-gray-500 mb-1">
                        Periode Aktif
                    </span>
                    <h2 class="text-lg font-bold" style="color: var(--color-navy-900);">
                        {{ $penugasan->periode->nama }}
                    </h2>
                    <p class="text-xs text-gray-600 mt-0.5">
                        Tahun Ajaran {{ $penugasan->periode->tahun_ajaran }}
                        @if($penugasan->periode->semester)
                            &bull; Semester {{ $penugasan->periode->semester }}
                        @endif
                    </p>
                </div>
                <div class="mt-4 pt-3 border-t text-xs flex items-center justify-between" style="border-color: var(--color-border); color: var(--color-muted);">
                    <span>Rentang:</span>
                    <span class="font-medium text-gray-800">
                        {{ $penugasan->periode->tanggal_mulai->format('d/m/Y') }} &ndash; {{ $penugasan->periode->tanggal_selesai->format('d/m/Y') }}
                    </span>
                </div>
            </x-card>

            {{-- Supervisor Anda --}}
            <x-card class="p-5 flex flex-col justify-between">
                <div>
                    <span class="block text-xs font-bold uppercase tracking-wider text-gray-500 mb-1">
                        Supervisor Anda
                    </span>
                    <h2 class="text-lg font-bold" style="color: var(--color-navy-900);">
                        {{ $penugasan->penilai->nama }}
                    </h2>
                    <p class="text-xs text-gray-600 mt-0.5">
                        @if($penugasan->penilai->nip)
                            NIP. {{ $penugasan->penilai->nip }}
                        @elseif($penugasan->penilai->nuptk)
                            NUPTK. {{ $penugasan->penilai->nuptk }}
                        @else
                            Peran: Supervisor Sekolah
                        @endif
                    </p>
                </div>
                <div class="mt-4 pt-3 border-t text-xs flex items-center justify-between" style="border-color: var(--color-border);">
                    <span class="text-gray-500">Status Penilai:</span>
                    <span class="inline-flex items-center gap-1 font-semibold text-green-700">
                        <span class="w-2 h-2 rounded-full bg-green-500"></span>
                        Aktif Ditugaskan
                    </span>
                </div>
            </x-card>

            {{-- Ringkasan Progres Supervisi --}}
            <x-card class="p-5 flex flex-col justify-between">
                <div>
                    <div class="flex items-center justify-between mb-1">
                        <span class="block text-xs font-bold uppercase tracking-wider text-gray-500">
                            Progres Supervisi
                        </span>
                        @if($progres['is_selesai'])
                            <span class="px-2 py-0.5 rounded-full text-xs font-bold" style="background-color: #E8F5E9; color: #1B5E20;">
                                Selesai
                            </span>
                        @else
                            <span class="px-2 py-0.5 rounded-full text-xs font-bold" style="background-color: #FFF3D6; color: #8A5A00;">
                                Berjalan
                            </span>
                        @endif
                    </div>
                    <div class="text-2xl font-bold mt-1" style="color: var(--color-navy-900);">
                        {{ $progres['label'] }}
                    </div>
                    <p class="text-xs text-gray-500 mt-0.5">
                        {{ $progres['selesai_count'] }} dari {{ $progres['total_count'] }} instrumen selesai.
                    </p>
                </div>

                {{-- Progress Bar --}}
                <div class="mt-4 pt-3 border-t" style="border-color: var(--color-border);">
                    <div class="w-full bg-gray-200 rounded-full h-2.5 overflow-hidden">
                        <div
                            class="h-2.5 rounded-full transition-all duration-500"
                            style="width: {{ $progres['persentase'] }}%; background-color: var(--color-orange-500);"
                        ></div>
                    </div>
                </div>
            </x-card>

            {{-- Nilai Akhir & Predikat Supervisi --}}
            <x-card class="p-5 flex flex-col justify-between">
                <div>
                    <div class="flex items-center justify-between mb-1">
                        <span class="block text-xs font-bold uppercase tracking-wider text-gray-500">
                            Nilai Akhir
                        </span>
                        @if($penugasan->predikat_akhir)
                            @php
                                $predBadge = match($penugasan->predikat_akhir) {
                                    'Diatas Ekspektasi' => 'background-color: #E8F5E9; color: #1B5E20; border-color: #A5D6A7;',
                                    'Sesuai Ekspektasi' => 'background-color: #E6ECF7; color: #253C6D; border-color: #94A3B8;',
                                    default => 'background-color: #FFF3D6; color: #8A5A00; border-color: #FDE047;',
                                };
                            @endphp
                            <span class="px-2 py-0.5 rounded-full text-[11px] font-bold border" style="{{ $predBadge }}">
                                {{ $penugasan->predikat_akhir }}
                            </span>
                        @else
                            <span class="px-2 py-0.5 rounded-full text-[11px] font-semibold bg-gray-100 text-gray-600">
                                Belum dinilai
                            </span>
                        @endif
                    </div>
                    @if($penugasan->nilai_akhir !== null)
                        <div class="text-2xl font-bold mt-1 text-emerald-700">
                            {{ number_format($penugasan->nilai_akhir, 2, ',', '.') }}
                        </div>
                        <p class="text-xs text-gray-600 mt-0.5">
                            Predikat: <strong class="text-gray-900">{{ $penugasan->predikat_akhir }}</strong>
                        </p>
                    @else
                        <div class="text-2xl font-bold mt-1 text-gray-400">
                            &mdash;
                        </div>
                        <p class="text-xs text-gray-500 mt-0.5">
                            Menunggu instrumen kuantitatif final.
                        </p>
                    @endif
                </div>

                <div class="mt-4 pt-3 border-t text-xs flex items-center justify-between" style="border-color: var(--color-border);">
                    <span class="text-gray-500">Rata-rata kuantitatif</span>
                    <a href="{{ route('guru.rapor', ['kode' => $sekolah->kode, 'periode' => $penugasan->periode_id]) }}" class="font-bold text-navy-700 hover:text-navy-900 hover:underline">
                        Lihat Rapor &rarr;
                    </a>
                </div>
            </x-card>
        </div>

        {{-- Pemberitahuan Status Informasi & Jadwal --}}
        @if(!$penugasan->infoSupervisi)
            <div class="p-4 rounded-xl border mb-8 flex flex-col sm:flex-row sm:items-center justify-between gap-4" style="background-color: #FFF8E1; border-color: #FFE082;">
                <div class="flex items-start gap-3">
                    <svg class="w-6 h-6 text-amber-700 shrink-0 mt-0.5" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
                    </svg>
                    <div>
                        <h3 class="text-sm font-bold text-amber-900">Lengkapi Informasi & Jadwal Supervisi Anda</h3>
                        <p class="text-xs text-amber-800 mt-0.5">
                            Silakan isi data kelas, mata pelajaran, materi pokok, dan usulan jadwal supervisi Anda sebelum proses observasi dimulai.
                        </p>
                    </div>
                </div>
                <x-button variant="tambah" href="{{ route('guru.info-jadwal', ['kode' => $sekolah->kode]) }}">
                    Isi Sekarang &rarr;
                </x-button>
            </div>
        @elseif(!$canEdit)
            <div class="p-4 rounded-xl border mb-8 flex items-center gap-3" style="background-color: var(--color-navy-50); border-color: var(--color-border);">
                <svg class="w-5 h-5 shrink-0" style="color: var(--color-navy-900);" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor">
                    <path fill-rule="evenodd" d="M5 9V7a5 5 0 0110 0v2a2 2 0 012 2v5a2 2 0 01-2 2H5a2 2 0 01-2-2v-5a2 2 0 012-2zm8-2v2H7V7a3 3 0 016 0z" clip-rule="evenodd" />
                </svg>
                <p class="text-xs leading-relaxed" style="color: var(--color-navy-900);">
                    <strong>Informasi & Jadwal Terkunci:</strong> Proses penilaian telah dimulai oleh supervisor Anda. Informasi kelas dan jadwal observasi saat ini berstatus hanya-baca (read-only).
                </p>
            </div>
        @endif

        {{-- TIMELINE EMPAT INSTRUMEN SUPERVISI --}}
        <x-card class="p-6 md:p-8 mb-8">
            <div class="border-b pb-4 mb-6 flex flex-wrap items-center justify-between gap-2" style="border-color: var(--color-border);">
                <div>
                    <h2 class="text-xl font-bold" style="color: var(--color-navy-900);">
                        Alur & Tahapan Instrumen Supervisi
                    </h2>
                    <p class="text-xs text-gray-500 mt-0.5">
                        Supervisi terdiri dari 5 instrumen resmi supervisi (Pembelajaran Mendalam & KAA). Nilai resmi hanya akan ditampilkan setelah status penilaian difinalisasi oleh supervisor.
                    </p>
                </div>
                <span class="text-xs font-semibold px-3 py-1 rounded-full bg-gray-100 text-gray-700">
                    5 Tahap Supervisi Resmi
                </span>
            </div>

            {{-- Daftar Baris Timeline 5 Tahap Supervisi Resmi --}}
            <div class="space-y-6">
                @php
                    $tahapanSupervisi = [
                        [
                            'no' => 1,
                            'judul' => 'Percakapan Pra-Observasi',
                            'deskripsi' => 'Dialog awal menyepakati tujuan pembelajaran, fokus perilaku amatan, dan strategi pembelajaran.',
                            'items' => [
                                ['kode' => 'pra_observasi', 'label' => 'Lembar Catatan Percakapan Pra-Observasi', 'sub' => '1']
                            ],
                        ],
                        [
                            'no' => 2,
                            'judul' => 'Dokumen Perencanaan Pembelajaran',
                            'deskripsi' => 'Penelaahan keselarasan dokumen modul ajar/RPP serta supervisi kelengkapan perangkat pembelajaran mendalam 2026.',
                            'items' => [
                                ['kode' => 'telaah_perencanaan', 'label' => 'Instrumen Telaah Perangkat Pembelajaran', 'sub' => '2.1'],
                                ['kode' => 'supervisi_perangkat', 'label' => 'Instrumen Supervisi Perangkat Pembelajaran Mendalam 2026', 'sub' => '2.2'],
                            ],
                        ],
                        [
                            'no' => 3,
                            'judul' => 'Observasi Pelaksanaan Pembelajaran',
                            'deskripsi' => 'Pengamatan langsung proses belajar mengajar tatap muka di dalam kelas berpusat pada murid.',
                            'items' => [
                                ['kode' => 'observasi_pembelajaran', 'label' => 'Instrumen Observasi Pelaksanaan Pembelajaran', 'sub' => '3']
                            ],
                        ],
                        [
                            'no' => 4,
                            'judul' => 'Refleksi Pasca Pembelajaran',
                            'deskripsi' => 'Percakapan pasca-observasi, reviu amatan kelas, evaluasi tantangan, dan praktik baik.',
                            'items' => [
                                ['kode' => 'refleksi_pasca_observasi', 'label' => 'Instrumen Refleksi Pasca Pembelajaran', 'sub' => '4']
                            ],
                        ],
                        [
                            'no' => 5,
                            'judul' => 'Rencana Pengembangan Diri Guru',
                            'deskripsi' => 'Penyusunan rencana aksi komitmen pengembangan keprofesian berkelanjutan guru.',
                            'items' => [
                                ['kode' => 'rencana_pengembangan_diri', 'label' => 'Lembar Rencana Pengembangan Diri Guru', 'sub' => '5']
                            ],
                        ],
                    ];
                @endphp

                @foreach($tahapanSupervisi as $tahap)
                    @php
                        $penilaianTahap = collect($tahap['items'])->map(function($it) use ($penugasan) {
                            return [
                                'meta' => $it,
                                'penilaian' => $penugasan->penilaian->firstWhere('jenisInstrumen.kode', $it['kode']),
                                'jadwal' => $penugasan->jadwalSupervisi->firstWhere('jenisInstrumen.kode', $it['kode']),
                            ];
                        });
                        $semuaFinal = $penilaianTahap->every(fn($p) => $p['penilaian'] && $p['penilaian']->isFinal());
                        $adaTerkunci = $penilaianTahap->contains(fn($p) => $p['penilaian'] ? $p['penilaian']->isTerkunciPrasyarat() : false);
                    @endphp

                    <div class="p-5 sm:p-6 rounded-2xl border {{ $semuaFinal ? 'bg-white border-emerald-200 shadow-xs' : ($adaTerkunci ? 'bg-slate-50/60 border-slate-200 opacity-90' : 'bg-white border-slate-100 hover:border-slate-300 shadow-xs') }} transition-all duration-150">
                        {{-- Header Tahap --}}
                        <div class="flex items-start gap-4 mb-3">
                            <div
                                class="w-11 h-11 rounded-2xl flex items-center justify-center font-bold text-base shrink-0 shadow-2xs"
                                style="
                                    @if($semuaFinal)
                                        background-color: #E8F5E9; color: #1B5E20; border: 1.5px solid #A5D6A7;
                                    @elseif($adaTerkunci)
                                        background-color: #F1F5F9; color: #94A3B8; border: 1.5px solid #CBD5E1;
                                    @else
                                        background-color: #EEF0F4; color: #253C6D; border: 1.5px solid #CBD5E1;
                                    @endif
                                "
                            >
                                @if($semuaFinal)
                                    <svg xmlns="http://www.w3.org/2000/svg" class="w-5 h-5" viewBox="0 0 20 20" fill="currentColor">
                                        <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd" />
                                    </svg>
                                @elseif($adaTerkunci && ! $penilaianTahap->contains(fn($p) => $p['penilaian'] && ! $p['penilaian']->isTerkunciPrasyarat()))
                                    <svg xmlns="http://www.w3.org/2000/svg" class="w-5 h-5 text-slate-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z" />
                                    </svg>
                                @else
                                    {{ $tahap['no'] }}
                                @endif
                            </div>

                            <div class="flex-1">
                                <div class="flex flex-wrap items-center gap-2">
                                    <h3 class="text-base font-bold text-navy-900">
                                        Tahap {{ $tahap['no'] }}: {{ $tahap['judul'] }}
                                    </h3>
                                    @if(count($tahap['items']) > 1)
                                        <span class="inline-flex items-center px-2 py-0.5 rounded-full text-[11px] font-semibold bg-navy-50 text-navy-900 border border-navy-200">
                                            2 Instrumen Penilaian
                                        </span>
                                    @endif
                                </div>
                                <p class="text-xs text-gray-500 mt-0.5">
                                    {{ $tahap['deskripsi'] }}
                                </p>
                            </div>
                        </div>

                        {{-- Rincian Instrumen dalam Tahap ini --}}
                        <div class="space-y-3 {{ count($tahap['items']) > 1 ? 'mt-4 pt-4 border-t border-slate-100' : '' }}">
                            @foreach($penilaianTahap as $pData)
                                @php
                                    $penilaianItem = $pData['penilaian'];
                                    $jadwalItem = $pData['jadwal'];
                                    $metaItem = $pData['meta'];
                                    $status = $penilaianItem?->status ?? 'belum';
                                    $isTerkunci = $penilaianItem ? $penilaianItem->isTerkunciPrasyarat() : false;
                                    $tahapPrasyarat = $penilaianItem ? $penilaianItem->tahapPrasyarat() : null;
                                    $isDiisiGuru = $penilaianItem ? $penilaianItem->isDiisiMandiriGuru() : false;
                                    $guruSudahKirim = $penilaianItem ? $penilaianItem->isGuruSudahSubmit() : false;
                                @endphp

                                <div class="p-4 sm:p-5 rounded-xl border {{ $penilaianItem?->isFinal() ? 'bg-emerald-50/40 border-emerald-200' : ($isTerkunci ? 'bg-slate-50 border-slate-200' : 'bg-neutral-50/70 border-neutral-200') }} flex flex-col md:flex-row md:items-center justify-between gap-4">
                                    {{-- Info Instrumen --}}
                                    <div class="flex-1 min-w-0 space-y-2">
                                        <div class="flex flex-wrap items-center gap-2.5">
                                            <span class="text-sm font-bold text-navy-900">
                                                {{ $metaItem['label'] }}
                                            </span>

                                            {{-- Badge Status --}}
                                            @if($status === 'final')
                                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-semibold bg-emerald-100 text-emerald-800">
                                                    Final
                                                </span>
                                            @elseif($status === 'direvisi')
                                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-semibold bg-orange-100 text-orange-800">
                                                    Sedang direvisi
                                                </span>
                                            @elseif($status === 'draft')
                                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-semibold bg-amber-50 text-amber-800 border border-amber-200">
                                                    Sedang dinilai @if($isDiisiGuru && $guruSudahKirim) (Terkirim) @endif
                                                </span>
                                            @elseif($isTerkunci)
                                                <span class="inline-flex items-center gap-1.5 px-2.5 py-0.5 rounded-full text-xs font-semibold bg-slate-200 text-slate-700">
                                                    <svg class="w-3 h-3 text-slate-500 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"/></svg>
                                                    <span>Terkunci (Selesaikan instrumen sebelumnya)</span>
                                                </span>
                                            @else
                                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-semibold bg-slate-100 text-slate-600">
                                                    Belum dinilai
                                                </span>
                                            @endif
                                        </div>

                                        {{-- Jadwal Observasi jika ada --}}
                                        @if($jadwalItem && $jadwalItem->tanggal)
                                            <div class="inline-flex items-center gap-1.5 px-3 py-1 rounded-lg text-xs font-medium bg-blue-50/70 text-blue-900 border border-blue-100">
                                                <svg xmlns="http://www.w3.org/2000/svg" width="13" height="13" viewBox="0 0 20 20" fill="currentColor" class="shrink-0">
                                                    <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm1-12a1 1 0 10-2 0v4a1 1 0 00.293.707l2.828 2.829a1 1 0 101.415-1.415L11 9.586V6z" clip-rule="evenodd" />
                                                </svg>
                                                <span>
                                                    Jadwal: {{ $jadwalItem->tanggal->locale('id')->isoFormat('dddd, D MMMM Y') }}
                                                    ({{ substr($jadwalItem->jam_mulai, 0, 5) }} &ndash; {{ substr($jadwalItem->jam_selesai, 0, 5) }} WIB)
                                                </span>
                                            </div>
                                        @endif
                                    </div>

                                    {{-- Aksi / Skor --}}
                                    <div class="md:text-right shrink-0 flex flex-col md:items-end justify-center gap-2 md:pl-4">
                                        @if($status === 'final')
                                            <div class="flex items-center md:justify-end gap-2.5">
                                                @if($penilaianItem->isKuantitatif())
                                                    <div class="flex items-baseline gap-1">
                                                        <span class="text-xs font-semibold text-gray-500">Nilai:</span>
                                                        <span class="text-xl font-bold text-green-700">
                                                            {{ number_format($penilaianItem->nilai, 2, ',', '.') }}
                                                        </span>
                                                    </div>
                                                    <div class="inline-flex items-center px-2.5 py-1 rounded-lg text-xs font-bold bg-navy-50 text-navy-900 border border-navy-200">
                                                        Predikat: {{ $penilaianItem->predikat ?? '-' }}
                                                    </div>
                                                @else
                                                    <div class="inline-flex items-center px-3 py-1 rounded-full text-xs font-bold bg-green-100 text-green-800">
                                                        Selesai
                                                    </div>
                                                @endif
                                            </div>
                                            <div class="flex items-center md:justify-end gap-3">
                                                @if($isDiisiGuru)
                                                    <a href="{{ route('guru.penilaian.show', ['kode' => $sekolah->kode, 'penilaian' => $penilaianItem]) }}"
                                                       class="text-xs font-semibold text-navy-700 hover:text-navy-900 hover:underline px-1 py-0.5">
                                                        Lihat Isian
                                                    </a>
                                                @endif
                                                <a href="{{ route('penilaian.cetak-pdf', ['kode' => $sekolah->kode, 'penilaian' => $penilaianItem]) }}"
                                                   target="_blank"
                                                   class="inline-flex items-center gap-1 text-xs font-semibold text-navy-900 hover:text-navy-700 underline focus:ring-2 focus:ring-orange-500 rounded px-1 py-0.5">
                                                    <svg class="w-3.5 h-3.5 shrink-0" viewBox="0 0 20 20" fill="currentColor">
                                                        <path fill-rule="evenodd" d="M6 2a2 2 0 00-2 2v12a2 2 0 002 2h8a2 2 0 002-2V7.414A2 2 0 0015.414 6L12 2.586A2 2 0 0010.586 2H6zm2 10a1 1 0 10-2 0v3a1 1 0 102 0v-3zm2-3a1 1 0 011 1v5a1 1 0 11-2 0v-5a1 1 0 011-1zm4 4a1 1 0 10-2 0v2a1 1 0 102 0v-2z" clip-rule="evenodd" />
                                                    </svg>
                                                    <span>Cetak PDF</span>
                                                </a>
                                            </div>
                                        @elseif($isTerkunci)
                                            <span class="text-xs font-medium text-slate-500 italic block pr-1">
                                                Terkunci oleh prasyarat
                                            </span>
                                        @elseif($isDiisiGuru)
                                            <a href="{{ route('guru.penilaian.show', ['kode' => $sekolah->kode, 'penilaian' => $penilaianItem]) }}"
                                               class="px-4 py-2 rounded-xl text-xs font-bold text-white transition min-h-[40px] flex items-center gap-1.5 shadow-xs
                                                      {{ $guruSudahKirim ? 'bg-navy-900 hover:bg-navy-800' : 'bg-[#2E7D32] hover:bg-[#256628]' }}">
                                                @if($guruSudahKirim)
                                                    <span>Lihat / Perbarui Isian</span>
                                                @elseif($status === 'draft')
                                                    <span>Lanjutkan Isi Draf &rarr;</span>
                                                @else
                                                    <span>Isi Lembar Ini &rarr;</span>
                                                @endif
                                            </a>
                                        @else
                                            <span class="text-xs font-medium text-amber-700 italic block pr-1">
                                                Dinilai langsung oleh Supervisor
                                            </span>
                                        @endif
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    </div>
                @endforeach
            </div>

            {{-- Tombol Buka Rapor Lengkap di Bawah Timeline --}}
            <div class="mt-8 pt-6 border-t flex flex-col sm:flex-row items-center justify-between gap-4" style="border-color: var(--color-border);">
                <div class="text-xs text-gray-500">
                    Semua hasil akhir supervisi akan dirangkum dalam rapor resmi sekolah.
                </div>
                <div class="flex gap-3">
                    <x-button variant="sekunder" href="{{ route('guru.info-jadwal', ['kode' => $sekolah->kode]) }}">
                        Periksa Informasi & Jadwal
                    </x-button>
                    <x-button variant="primer" href="{{ route('guru.rapor', ['kode' => $sekolah->kode, 'periode' => $penugasan->periode_id]) }}">
                        Buka Rapor Supervisi &rarr;
                    </x-button>
                </div>
            </div>
        </x-card>
    @endif

</x-app-layout>
