<x-app-layout :namaSekolah="$sekolah->nama" title="Detail Supervisi Guru">
    <div class="mb-6">
        <a href="{{ route('dashboard.supervisor', ['kode' => $sekolah->kode]) }}" class="inline-flex items-center gap-2 text-sm font-semibold text-navy-700 hover:text-navy-900 transition">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18" />
            </svg>
            Kembali ke Dasbor Supervisor
        </a>
    </div>

    <x-page-header
        title="Supervisi: {{ $penugasan->guru->nama }}"
        subtitle="Periode {{ $penugasan->periode->nama }} ({{ $penugasan->periode->tahun_ajaran }})"
    />

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6 mb-8">
        {{-- Kartu Info Guru & Supervisi (Read-Only) --}}
        <div class="lg:col-span-2 space-y-6">
            <div class="bg-white rounded-xl shadow-sm border border-neutral-200 p-6">
                <h3 class="text-lg font-bold text-navy-900 border-b border-neutral-100 pb-3 flex items-center gap-2">
                    <svg class="w-5 h-5 text-navy-700" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" />
                    </svg>
                    Informasi Pembelajaran yang Disupervisi
                </h3>
                
                @if($penugasan->infoSupervisi)
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mt-4 text-[15px]">
                        <div>
                            <span class="block text-xs font-semibold text-muted uppercase">Mata Pelajaran</span>
                            <span class="font-bold text-ink">{{ $penugasan->infoSupervisi->mata_pelajaran ?: '-' }}</span>
                        </div>
                        <div>
                            <span class="block text-xs font-semibold text-muted uppercase">Kelas / Fase</span>
                            <span class="font-bold text-ink">
                                {{ $penugasan->infoSupervisi->kelas ?: '-' }} 
                                @if($penugasan->infoSupervisi->fase) (Fase {{ $penugasan->infoSupervisi->fase }}) @endif
                            </span>
                        </div>
                        <div>
                            <span class="block text-xs font-semibold text-muted uppercase">Semester</span>
                            <span class="font-bold text-ink">{{ $penugasan->infoSupervisi->semester ?: '-' }}</span>
                        </div>
                        <div>
                            <span class="block text-xs font-semibold text-muted uppercase">Elemen Pembelajaran</span>
                            <span class="font-bold text-ink">{{ $penugasan->infoSupervisi->elemen ?: '-' }}</span>
                        </div>
                        <div class="md:col-span-2">
                            <span class="block text-xs font-semibold text-muted uppercase">Capaian Pembelajaran (CP) / Tujuan</span>
                            <div class="mt-1 p-3 bg-neutral-50 rounded-lg text-ink whitespace-pre-line border border-neutral-100">
                                {{ $penugasan->infoSupervisi->cp ?: 'Belum diisi oleh guru.' }}
                            </div>
                        </div>
                        @if($penugasan->infoSupervisi->catatan)
                            <div class="md:col-span-2">
                                <span class="block text-xs font-semibold text-muted uppercase">Catatan Tambahan Guru</span>
                                <div class="mt-1 p-3 bg-neutral-50 rounded-lg text-ink whitespace-pre-line border border-neutral-100">
                                    {{ $penugasan->infoSupervisi->catatan }}
                                </div>
                            </div>
                        @endif
                    </div>
                @else
                    <div class="py-5 px-4 text-center rounded-xl bg-amber-50 border border-amber-200 text-amber-900 text-sm mt-4">
                        <p class="font-bold flex items-center justify-center gap-2">
                            <svg class="w-5 h-5 text-amber-700 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
                            </svg>
                            Guru belum melengkapi informasi pembelajaran & usulan jadwal
                        </p>
                        <p class="text-xs text-amber-800 mt-1.5">
                            Disarankan untuk mengingatkan guru agar mengisi kelas, mata pelajaran, materi pokok, dan tanggal observasi melalui dasbor guru sebelum lembar penilaian difinalisasi.
                        </p>
                    </div>
                @endif
            </div>

            {{-- Kartu Jadwal Supervisi (Read-Only) --}}
            <div class="bg-white rounded-xl shadow-sm border border-neutral-200 p-6">
                <h3 class="text-lg font-bold text-navy-900 border-b border-neutral-100 pb-3 flex items-center gap-2">
                    <svg class="w-5 h-5 text-navy-700" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
                    </svg>
                    Jadwal Rencana Pelaksanaan Supervisi
                </h3>

                @if($penugasan->jadwalSupervisi->isNotEmpty())
                    <div class="overflow-x-auto mt-4">
                        <table class="w-full text-left text-sm">
                            <thead class="bg-navy-50 text-navy-900 font-bold">
                                <tr>
                                    <th class="py-2.5 px-3">Instrumen</th>
                                    <th class="py-2.5 px-3">Tanggal Pelaksanaan</th>
                                    <th class="py-2.5 px-3">Waktu / Jam</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-neutral-100">
                                @foreach($penugasan->jadwalSupervisi as $jadwal)
                                    <tr>
                                        <td class="py-3 px-3 font-semibold text-ink">
                                            {{ $jadwal->jenisInstrumen?->nama ?? 'Semua Instrumen' }}
                                        </td>
                                        <td class="py-3 px-3 text-ink">
                                            {{ $jadwal->tanggal ? $jadwal->tanggal->translatedFormat('l, d F Y') : '-' }}
                                        </td>
                                        <td class="py-3 px-3 text-muted">
                                            @if($jadwal->jam_mulai)
                                                {{ substr($jadwal->jam_mulai, 0, 5) }} - {{ $jadwal->jam_selesai ? substr($jadwal->jam_selesai, 0, 5) : 'selesai' }}
                                            @else
                                                -
                                            @endif
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                @else
                    <div class="py-6 text-center text-muted text-sm">
                        <p>Guru belum menetapkan rencana jadwal supervisi.</p>
                    </div>
                @endif
            </div>
        </div>

        {{-- Profil Singkat Guru & Ringkasan Nilai --}}
        <div class="space-y-6">
            <div class="bg-white rounded-xl shadow-sm border border-neutral-200 p-6 text-center">
                <div class="w-20 h-20 rounded-full bg-navy-100 text-navy-900 font-bold text-2xl flex items-center justify-center mx-auto mb-4">
                    {{ strtoupper(substr($penugasan->guru->nama, 0, 2)) }}
                </div>
                <h4 class="text-xl font-bold text-navy-900">{{ $penugasan->guru->nama }}</h4>
                <p class="text-sm text-muted mt-1">NIP: {{ $penugasan->guru->nip ?: '-' }}</p>
                <p class="text-xs text-muted">NUPTK: {{ $penugasan->guru->nuptk ?: '-' }}</p>

                <div class="mt-6 pt-4 border-t border-neutral-100 flex items-center justify-between text-left">
                    <div>
                        <span class="block text-xs uppercase font-semibold text-muted">Status Selesai</span>
                        <span class="text-lg font-bold text-navy-900">
                            {{ $penugasan->penilaian->where('status', 'final')->count() }} / 4 Final
                        </span>
                    </div>
                    @if($penugasan->penilaian->where('status', 'final')->count() === 4)
                        <span class="px-3 py-1 rounded-full text-xs font-bold bg-emerald-100 text-emerald-800">
                            Supervisi Lengkap
                        </span>
                    @endif
                </div>
            </div>
        </div>
    </div>

    {{-- EMPAT TOMBOL INSTRUMEN (KBM, Administrasi, Pengelolaan Kelas, Perencanaan) --}}
    <div class="space-y-4">
        <h3 class="text-xl font-bold text-navy-900">Lembar Penilaian Supervisi (4 Instrumen)</h3>
        <p class="text-muted text-[15px]">Klik instrumen di bawah untuk mengisi skor, memperbarui draft, atau melihat hasil finalisasi.</p>

        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
            @php
                // Urutan standar 4 instrumen
                $urutanKode = ['kbm', 'administrasi', 'pengelolaan_kelas', 'perencanaan'];
                $penilaianSorted = $penugasan->penilaian->sortBy(function($p) use ($urutanKode) {
                    $index = array_search($p->jenisInstrumen->kode ?? '', $urutanKode);
                    return $index !== false ? $index : 99;
                });
            @endphp

            @foreach($penilaianSorted as $pen)
                <div class="bg-white rounded-xl shadow-sm border border-neutral-200 p-6 flex flex-col justify-between hover:border-navy-400 transition">
                    <div>
                        <div class="flex items-start justify-between gap-3 mb-2">
                            <h4 class="text-lg font-bold text-navy-900">
                                {{ $pen->jenisInstrumen->nama }}
                            </h4>
                            @if ($pen->status === 'belum')
                                <span class="px-2.5 py-1 rounded-full text-xs font-semibold bg-neutral-100 text-neutral-600">Belum Dinilai</span>
                            @elseif ($pen->status === 'draft')
                                <span class="px-2.5 py-1 rounded-full text-xs font-semibold bg-amber-100 text-amber-800">Draft</span>
                            @elseif ($pen->status === 'direvisi')
                                <span class="px-2.5 py-1 rounded-full text-xs font-semibold bg-orange-100 text-orange-800">Sedang Direvisi</span>
                            @elseif ($pen->status === 'final')
                                <span class="px-2.5 py-1 rounded-full text-xs font-semibold bg-emerald-100 text-emerald-800">Final</span>
                            @endif
                        </div>

                        <p class="text-xs text-muted mb-4">
                            Kode: {{ strtoupper($pen->jenisInstrumen->kode) }} &bull; Versi: v{{ $pen->versiInstrumen?->nomor_versi ?? 'Aktif' }}
                        </p>

                        @if ($pen->isFinal())
                            <div class="bg-emerald-50 rounded-lg p-3 border border-emerald-100 mb-4 flex items-center justify-between">
                                <span class="text-xs font-semibold text-emerald-900">Nilai Akhir:</span>
                                <span class="text-lg font-bold text-emerald-800">{{ number_format($pen->nilai, 2) }} (Predikat {{ $pen->predikat }})</span>
                            </div>
                        @endif
                    </div>

                    <div class="pt-3 border-t border-neutral-100 flex items-center justify-between">
                        <span class="text-xs text-muted">
                            @if($pen->isFinal())
                                Terkunci &bull; {{ $pen->finalized_at?->format('d/m/Y') }}
                            @else
                                {{ $pen->isBelum() ? 'Belum dimulai' : 'Terakhir disimpan: ' . $pen->updated_at->diffForHumans() }}
                            @endif
                        </span>

                        <div class="flex items-center gap-2">
                            @if($pen->isFinal())
                                <a href="{{ route('penilaian.cetak-pdf', ['kode' => $sekolah->kode, 'penilaian' => $pen]) }}" 
                                   target="_blank"
                                   class="px-3 py-2 rounded-xl text-sm font-semibold bg-emerald-50 text-emerald-800 hover:bg-emerald-100 border border-emerald-200 min-h-[44px] flex items-center gap-1.5 transition"
                                   title="Cetak PDF Hasil Penilaian">
                                    <svg class="w-4 h-4 text-emerald-700" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z" />
                                    </svg>
                                    <span>Cetak PDF</span>
                                </a>
                            @endif

                            <a href="{{ route('supervisor.penilaian.show', ['kode' => $sekolah->kode, 'penilaian' => $pen]) }}" 
                               class="px-4 py-2 rounded-xl text-sm font-bold min-h-[44px] flex items-center gap-1.5 transition
                               {{ $pen->isFinal() ? 'bg-navy-50 text-navy-900 hover:bg-navy-100' : 'bg-navy-900 text-white hover:bg-navy-800' }}">
                                @if($pen->isFinal())
                                    <span>Lihat Penilaian</span>
                                @elseif($pen->isBelum())
                                    <span>Mulai Menilai &rarr;</span>
                                @else
                                    <span>Lanjutkan Menilai &rarr;</span>
                                @endif
                            </a>
                        </div>
                    </div>
                </div>
            @endforeach
        </div>
    </div>
</x-app-layout>
