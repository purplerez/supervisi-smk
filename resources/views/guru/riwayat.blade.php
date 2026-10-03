<x-app-layout :namaSekolah="$sekolah->nama" title="Riwayat Supervisi Guru">

    <x-page-header
        title="Riwayat Supervisi Akademik"
        subtitle="Daftar pelaksanaan supervisi akademik yang pernah Anda ikuti di {{ $sekolah->nama }}."
    >
        <x-slot:actions>
            <x-button variant="sekunder" href="{{ route('guru.dashboard', ['kode' => $sekolah->kode]) }}">
                &larr; Dasbor Supervisi Aktif
            </x-button>
        </x-slot:actions>
    </x-page-header>

    @if($riwayat->isEmpty())
        <x-card class="p-8 text-center max-w-2xl mx-auto">
            <div class="w-16 h-16 rounded-full mx-auto flex items-center justify-center mb-4" style="background-color: var(--color-navy-50); color: var(--color-navy-900);">
                <svg xmlns="http://www.w3.org/2000/svg" width="32" height="32" viewBox="0 0 20 20" fill="currentColor">
                    <path d="M9 2a1 1 0 000 2h2a1 1 0 100-2H9z" />
                    <path fill-rule="evenodd" d="M4 5a2 2 0 012-2 3 3 0 003 3h2a3 3 0 003-3 2 2 0 012 2v11a2 2 0 01-2 2H6a2 2 0 01-2-2V5zm3 4a1 1 0 000 2h.01a1 1 0 100-2H7zm3 0a1 1 0 000 2h3a1 1 0 100-2h-3zm-3 4a1 1 0 100 2h.01a1 1 0 100-2H7zm3 0a1 1 0 100 2h3a1 1 0 100-2h-3z" clip-rule="evenodd" />
                </svg>
            </div>
            <h2 class="text-xl font-bold mb-2" style="color: var(--color-navy-900);">Belum Ada Riwayat Supervisi</h2>
            <p class="text-sm leading-relaxed mb-6" style="color: var(--color-muted);">
                Anda belum pernah terdaftar pada periode supervisi sebelumnya di sekolah ini.
            </p>
            <x-button variant="sekunder" href="{{ route('guru.dashboard', ['kode' => $sekolah->kode]) }}">
                Kembali ke Dasbor Utama
            </x-button>
        </x-card>
    @else
        <x-card class="overflow-hidden">
            <div class="px-6 py-4 border-b flex items-center justify-between" style="border-color: var(--color-border); background-color: var(--color-navy-50);">
                <div>
                    <h2 class="text-base font-bold" style="color: var(--color-navy-900);">
                        Daftar Periode Supervisi Anda ({{ $riwayat->count() }})
                    </h2>
                    <p class="text-xs text-gray-500">
                        Klik tombol rapor pada baris yang diinginkan untuk melihat rincian penilaian masing-masing periode.
                    </p>
                </div>
            </div>

            <div class="overflow-x-auto">
                <table class="w-full text-left border-collapse text-sm">
                    <thead>
                        <tr class="border-b text-xs font-bold uppercase tracking-wider" style="border-color: var(--color-border); background: var(--color-cream); color: var(--color-navy-900);">
                            <th class="py-3 px-4 w-12 text-center">No</th>
                            <th class="py-3 px-4">Periode Supervisi</th>
                            <th class="py-3 px-4">Status Periode</th>
                            <th class="py-3 px-4">Supervisor Penilai</th>
                            <th class="py-3 px-4">Hasil / Progres</th>
                            <th class="py-3 px-4 text-center w-36">Aksi</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y" style="divide-color: var(--color-border);">
                        @foreach($riwayat as $index => $item)
                            @php
                                $periode = $item->periode;
                                $penilaian = $item->penilaian;
                                $finalCount = $penilaian->where('status', 'final')->count();
                                $totalCount = $penilaian->count();
                                $isSelesai = ($totalCount > 0 && $finalCount === $totalCount);
                            @endphp
                            <tr class="hover:bg-amber-50/20 transition-colors">
                                <td class="py-4 px-4 text-center font-medium text-gray-500">
                                    {{ $index + 1 }}
                                </td>
                                <td class="py-4 px-4">
                                    <div class="font-bold text-base" style="color: var(--color-navy-900);">
                                        {{ $periode->nama }}
                                    </div>
                                    <div class="text-xs text-gray-500 mt-0.5">
                                        Tahun Ajaran {{ $periode->tahun_ajaran }}
                                        @if($periode->semester)
                                            &bull; Semester {{ $periode->semester }}
                                        @endif
                                        &bull; {{ $periode->tanggal_mulai->format('d/m/Y') }} &ndash; {{ $periode->tanggal_selesai->format('d/m/Y') }}
                                    </div>
                                </td>
                                <td class="py-4 px-4">
                                    @if($periode->isAktif())
                                        <x-badge-status status="final">Periode Aktif</x-badge-status>
                                    @elseif($periode->isDraft())
                                        <x-badge-status status="draft">Draf</x-badge-status>
                                    @else
                                        <x-badge-status status="belum">Ditutup</x-badge-status>
                                    @endif
                                </td>
                                <td class="py-4 px-4">
                                    <div class="font-semibold text-gray-900">
                                        {{ $item->penilai->nama }}
                                    </div>
                                    <div class="text-xs text-gray-500 mt-0.5">
                                        {{ $item->penilai->nip ? 'NIP. '.$item->penilai->nip : ($item->penilai->nuptk ? 'NUPTK. '.$item->penilai->nuptk : '-') }}
                                    </div>
                                </td>
                                <td class="py-4 px-4">
                                    @if($isSelesai)
                                        <span class="inline-flex items-center px-2.5 py-1 rounded-full text-xs font-bold" style="background-color: #E8F5E9; color: #1B5E20;">
                                            Selesai (4/4)
                                        </span>
                                    @else
                                        <span class="inline-flex items-center px-2.5 py-1 rounded-full text-xs font-semibold" style="background-color: #FFF3D6; color: #8A5A00;">
                                            Belum selesai ({{ $finalCount }}/{{ $totalCount }})
                                        </span>
                                    @endif
                                </td>
                                <td class="py-4 px-4 text-center">
                                    <a
                                        href="{{ route('guru.rapor', ['kode' => $sekolah->kode, 'periode' => $periode->id]) }}"
                                        class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-lg text-xs font-semibold text-white shadow-sm transition-all hover:opacity-95"
                                        style="background-color: var(--color-navy-900);"
                                    >
                                        <svg xmlns="http://www.w3.org/2000/svg" width="13" height="13" viewBox="0 0 20 20" fill="currentColor">
                                            <path d="M10 12a2 2 0 100-4 2 2 0 000 4z" />
                                            <path fill-rule="evenodd" d="M.458 10C1.732 5.943 5.522 3 10 3s8.268 2.943 9.542 7c-1.274 4.057-5.064 7-9.542 7S1.732 14.057.458 10zM14 10a4 4 0 11-8 0 4 4 0 018 0z" clip-rule="evenodd" />
                                        </svg>
                                        Buka Rapor
                                    </a>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </x-card>
    @endif

</x-app-layout>
