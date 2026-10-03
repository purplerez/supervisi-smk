<x-app-layout :namaSekolah="$sekolah->nama" title="Instrumen Supervisi">

    <x-page-header
        title="Instrumen Supervisi"
        subtitle="Kelola instrumen penilaian supervisi guru di {{ $sekolah->nama }}."
    />

    {{-- Info Card Penjelasan Resolusi Versi --}}
    <div class="mb-6 p-4 rounded-xl border bg-blue-50/70 border-blue-200 text-blue-900 text-sm leading-relaxed">
        <div class="flex items-start gap-3">
            <svg xmlns="http://www.w3.org/2000/svg" width="22" height="22" viewBox="0 0 20 20" fill="currentColor" class="text-blue-600 shrink-0 mt-0.5">
                <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7-4a1 1 0 11-2 0 1 1 0 012 0zM9 9a1 1 0 000 2v3a1 1 0 001 1h1a1 1 0 100-2v-3a1 1 0 00-1-1H9z" clip-rule="evenodd"/>
            </svg>
            <div>
                <p class="font-semibold text-base mb-1">Pedoman Instrumen Penilaian</p>
                <p>
                    Sistem secara otomatis menggunakan <strong>Versi Terbit Milik Sekolah</strong> apabila tersedia. Jika sekolah Anda belum membuat instrumen kustom, sistem akan menggunakan <strong>Template Global Standar Nasional</strong> secara langsung. Anda dapat menyalin template global ke sekolah untuk menyesuaikan butir penilaian dengan muatan lokal sekolah.
                </p>
            </div>
        </div>
    </div>

    {{-- Tab Pilihan Tampilan --}}
    <div x-data="{ tab: 'sekolah' }" class="space-y-6">
        <div class="flex border-b" style="border-color: var(--color-border);">
            <button
                type="button"
                @click="tab = 'sekolah'"
                :class="tab === 'sekolah' ? 'border-b-2 font-bold' : 'text-gray-500 hover:text-gray-700'"
                class="py-3 px-5 text-sm transition-colors"
                :style="tab === 'sekolah' ? 'color: var(--color-navy-900); border-color: var(--color-orange-500);' : ''"
            >
                Instrumen Khusus Sekolah ({{ $instrumenSekolah->count() }})
            </button>
            <button
                type="button"
                @click="tab = 'global'"
                :class="tab === 'global' ? 'border-b-2 font-bold' : 'text-gray-500 hover:text-gray-700'"
                class="py-3 px-5 text-sm transition-colors"
                :style="tab === 'global' ? 'color: var(--color-navy-900); border-color: var(--color-orange-500);' : ''"
            >
                Template Standar Nasional ({{ $templateGlobal->count() }})
            </button>
        </div>

        {{-- TAB 1: INSTRUMEN KHUSUS SEKOLAH --}}
        <div x-show="tab === 'sekolah'" class="space-y-6">
            @if($instrumenSekolah->isEmpty())
                <x-card class="text-center py-8">
                    <x-empty-state
                        title="Belum Ada Instrumen Kustom"
                        description="Sekolah Anda saat ini aktif menggunakan 4 Template Global standar secara penuh. Untuk membuat instrumen khusus sekolah, silakan beralih ke tab Template Standar Nasional lalu klik Salin ke Sekolah Saya."
                    >
                        <x-button type="button" variant="primary" @click="tab = 'global'">
                            Lihat Template Standar Nasional
                        </x-button>
                    </x-empty-state>
                </x-card>
            @else
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    @foreach($instrumenSekolah as $item)
                        @php
                            $versiTerbit = $item->versiTerbitTerbaru;
                            $versiTerbaru = $item->versi->first();
                        @endphp
                        <x-card class="flex flex-col justify-between h-full">
                            <div>
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
                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-semibold bg-blue-100 text-blue-800">
                                        Milik Sekolah
                                    </span>
                                </div>

                                <div class="space-y-3 mb-6">
                                    @if($versiTerbit)
                                        <div class="p-3 rounded-lg" style="background-color: var(--color-cream); border: 1px solid var(--color-border);">
                                            <div class="flex items-center justify-between mb-2">
                                                <span class="text-sm font-semibold" style="color: var(--color-navy-900);">
                                                    Versi Terbit Aktif: v{{ $versiTerbit->nomor_versi }}
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
                                                    <span class="block text-xs" style="color: var(--color-muted);">Skor Maks</span>
                                                    <span class="font-bold text-base text-green-700">{{ $versiTerbit->skor_maks }}</span>
                                                </div>
                                            </div>
                                        </div>
                                    @else
                                        <div class="p-3 rounded-lg text-sm bg-yellow-50 text-yellow-800 border border-yellow-200">
                                            Versi kustom sekolah masih berstatus <strong>Draf</strong>. Supervisi masih menggunakan template nasional hingga versi ini Anda terbitkan.
                                        </div>
                                    @endif

                                    <div class="text-xs space-y-1">
                                        <span class="font-semibold block" style="color: var(--color-muted);">Daftar Versi:</span>
                                        <div class="flex flex-wrap gap-1">
                                            @foreach($item->versi as $v)
                                                <a href="{{ route('admin.instrumen.versi.show', ['kode' => $sekolah->kode, 'jenis' => $item, 'versi' => $v]) }}"
                                                   class="px-2 py-0.5 rounded text-xs no-underline hover:underline transition-colors
                                                   {{ $v->isTerbit() ? 'bg-green-100 text-green-800' : 'bg-yellow-100 text-yellow-800' }}">
                                                    v{{ $v->nomor_versi }} ({{ $v->isTerbit() ? 'Terbit' : 'Draf' }})
                                                </a>
                                            @endforeach
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <div class="pt-4 border-t" style="border-color: var(--color-border);">
                                <x-button
                                    href="{{ route('admin.instrumen.show', ['kode' => $sekolah->kode, 'jenis' => $item]) }}"
                                    variant="primary"
                                    class="w-full justify-center"
                                >
                                    <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 20 20" fill="currentColor">
                                        <path d="M13.586 3.586a2 2 0 112.828 2.828l-.793.793-2.828-2.828.793-.793zM11.379 5.793L3 14.172V17h2.828l8.38-8.379-2.83-2.828z" />
                                    </svg>
                                    Kelola Bagian & Butir
                                </x-button>
                            </div>
                        </x-card>
                    @endforeach
                </div>
            @endif
        </div>

        {{-- TAB 2: TEMPLATE GLOBAL STANDAR NASIONAL --}}
        <div x-show="tab === 'global'" class="space-y-6" style="display: none;">
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                @foreach($templateGlobal as $global)
                    @php
                        $versiTerbit = $global->versiTerbitTerbaru;
                        $sudahAdaKustom = $instrumenSekolah->firstWhere('kode', $global->kode);
                    @endphp
                    <x-card class="flex flex-col justify-between h-full">
                        <div>
                            <div class="flex items-start justify-between gap-4 mb-4 pb-3 border-b" style="border-color: var(--color-border);">
                                <div>
                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded text-xs font-semibold uppercase tracking-wider mb-1"
                                          style="background-color: var(--color-navy-50); color: var(--color-navy-900);">
                                        {{ $global->kode }}
                                    </span>
                                    <h2 class="text-xl font-bold" style="color: var(--color-navy-900);">
                                        {{ $global->nama }}
                                    </h2>
                                </div>
                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-semibold bg-gray-100 text-gray-700">
                                    Standar Nasional (Hanya Baca)
                                </span>
                            </div>

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
                                                <span class="block text-xs" style="color: var(--color-muted);">Skor Maks</span>
                                                <span class="font-bold text-base text-green-700">{{ $versiTerbit->skor_maks }}</span>
                                            </div>
                                        </div>
                                    </div>
                                @endif

                                @if($sudahAdaKustom)
                                    <div class="p-2.5 rounded-lg bg-green-50 text-green-800 text-xs border border-green-200 flex items-center gap-2">
                                        <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 20 20" fill="currentColor">
                                            <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"/>
                                        </svg>
                                        <span>Sekolah sudah memiliki instrumen kustom untuk jenis ini.</span>
                                    </div>
                                @endif
                            </div>
                        </div>

                        <div class="pt-4 border-t flex flex-col gap-2" style="border-color: var(--color-border);">
                            @if(!$sudahAdaKustom)
                                <form method="POST" action="{{ route('admin.instrumen.salin', ['kode' => $sekolah->kode, 'templateGlobal' => $global->id]) }}"
                                      onsubmit="return confirm('Salin template nasional {{ $global->nama }} ke sekolah Anda? Template akan disalin sebagai Draf baru yang dapat Anda modifikasi.');">
                                    @csrf
                                    <x-button type="submit" variant="tambah" class="w-full justify-center">
                                        <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 20 20" fill="currentColor">
                                            <path d="M7 9a2 2 0 012-2h6a2 2 0 012 2v6a2 2 0 01-2 2H9a2 2 0 01-2-2V9z" />
                                            <path d="M5 3a2 2 0 00-2 2v6a2 2 0 002 2V5h8a2 2 0 00-2-2H5z" />
                                        </svg>
                                        Salin ke Sekolah Saya
                                    </x-button>
                                </form>
                            @else
                                <x-button
                                    href="{{ route('admin.instrumen.show', ['kode' => $sekolah->kode, 'jenis' => $sudahAdaKustom]) }}"
                                    variant="outline"
                                    class="w-full justify-center"
                                >
                                    Kelola Instrumen Milik Sekolah
                                </x-button>
                            @endif
                        </div>
                    </x-card>
                @endforeach
            </div>
        </div>
    </div>

</x-app-layout>
