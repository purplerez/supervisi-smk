<x-layouts.super-admin :title="'Template Instrumen Global'">

    <x-page-header
        title="Template Instrumen Global"
        subtitle="Kelola instrumen penilaian standar nasional (KBM, Administrasi, Pengelolaan Kelas, Perencanaan) yang menjadi acuan seluruh sekolah."
    />

    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
        @foreach($instrumen as $item)
            @php
                $versiTerbit = $item->versiTerbitTerbaru;
                $versiTerbaru = $item->versi->first();
            @endphp
            <x-card class="flex flex-col justify-between h-full">
                <div>
                    {{-- Header Kartu --}}
                    <div class="flex items-start justify-between gap-4 mb-4 pb-3 border-b" style="border-color: var(--color-border);">
                        <div>
                            <span class="inline-flex items-center px-2.5 py-0.5 rounded text-xs font-semibold uppercase tracking-wider mb-1"
                                  style="background-color: var(--color-navy-50); color: var(--color-navy-900);">
                                {{ $item->kode }}
                            </span>
                            <h2 class="text-xl font-bold" style="color: var(--color-navy-900);">
                                {{ $item->nama }}
                            </h2>
                        </div>
                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-semibold {{ $item->aktif ? 'bg-green-100 text-green-800' : 'bg-gray-100 text-gray-700' }}">
                            {{ $item->aktif ? 'Aktif' : 'Nonaktif' }}
                        </span>
                    </div>

                    {{-- Informasi Versi --}}
                    <div class="space-y-3 mb-6">
                        @if($versiTerbit)
                            <div class="p-3 rounded-lg" style="background-color: var(--color-cream); border: 1px solid var(--color-border);">
                                <div class="flex items-center justify-between mb-2">
                                    <span class="text-sm font-semibold" style="color: var(--color-navy-900);">
                                        Versi Terbit: v{{ $versiTerbit->nomor_versi }}
                                    </span>
                                    <x-badge-status status="final">Terbit</x-badge-status>
                                </div>
                                <div class="grid grid-cols-3 gap-2 text-center text-sm pt-2 border-t border-dashed" style="border-color: var(--color-border);">
                                    <div>
                                        <span class="block text-xs" style="color: var(--color-muted);">Bagian</span>
                                        <span class="font-bold text-base" style="color: var(--color-navy-900);">{{ $versiTerbit->jumlah_bagian }}</span>
                                    </div>
                                    <div>
                                        <span class="block text-xs" style="color: var(--color-muted);">Total Butir</span>
                                        <span class="font-bold text-base" style="color: var(--color-navy-900);">{{ $versiTerbit->jumlah_butir }}</span>
                                    </div>
                                    <div>
                                        <span class="block text-xs" style="color: var(--color-muted);">Skor Maksimal</span>
                                        <span class="font-bold text-base text-green-700">{{ $versiTerbit->skor_maks }}</span>
                                    </div>
                                </div>
                            </div>
                        @else
                            <div class="p-3 rounded-lg text-sm bg-yellow-50 text-yellow-800 border border-yellow-200">
                                Belum ada versi yang diterbitkan untuk template ini.
                            </div>
                        @endif

                        {{-- Riwayat Versi Singkat --}}
                        <div class="text-xs space-y-1">
                            <span class="font-semibold block" style="color: var(--color-muted);">Total Versi: {{ $item->versi->count() }} versi</span>
                            <div class="flex flex-wrap gap-1">
                                @foreach($item->versi as $v)
                                    <a href="{{ route('super-admin.instrumen.versi.show', [$item, $v]) }}"
                                       class="px-2 py-0.5 rounded text-xs no-underline hover:underline transition-colors
                                       {{ $v->isTerbit() ? 'bg-green-100 text-green-800' : 'bg-yellow-100 text-yellow-800' }}">
                                        v{{ $v->nomor_versi }} ({{ $v->isTerbit() ? 'Terbit' : 'Draf' }})
                                    </a>
                                @endforeach
                            </div>
                        </div>
                    </div>
                </div>

                {{-- Aksi Kartu --}}
                <div class="pt-4 border-t" style="border-color: var(--color-border);">
                    <x-button
                        href="{{ route('super-admin.instrumen.show', $item) }}"
                        variant="primary"
                        class="w-full justify-center"
                    >
                        <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true">
                            <path d="M13.586 3.586a2 2 0 112.828 2.828l-.793.793-2.828-2.828.793-.793zM11.379 5.793L3 14.172V17h2.828l8.38-8.379-2.83-2.828z" />
                        </svg>
                        Buka Editor Template
                    </x-button>
                </div>
            </x-card>
        @endforeach
    </div>

</x-layouts.super-admin>
