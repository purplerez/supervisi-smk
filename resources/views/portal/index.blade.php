<!DOCTYPE html>
<html lang="id" class="h-full">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Portal Supervisi Guru — Masuk ke Sekolah Anda</title>
    <meta name="description" content="Portal satu pintu aplikasi supervisi guru untuk memilih dan masuk ke halaman supervisi sekolah.">

    @livewireStyles
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="min-h-full flex flex-col antialiased text-[#1F2937]" style="background-color: var(--color-cream, #FFFAF3);">

    {{-- Top Bar Navy Sesuai Panduan Design System --}}
    <header class="w-full flex items-center justify-between px-4 sm:px-8 py-3.5 shadow-sm"
            style="background-color: var(--color-navy-900, #253C6D); height: 64px;">
        <div class="flex items-center gap-3">
            <span class="flex items-center justify-center w-10 h-10 rounded-xl"
                  style="background-color: var(--color-orange-500, #F2842F);">
                <svg xmlns="http://www.w3.org/2000/svg" width="22" height="22" viewBox="0 0 20 20" fill="white" aria-hidden="true">
                    <path d="M10.394 2.08a1 1 0 00-.788 0l-7 3a1 1 0 000 1.84L5.25 8.051a.999.999 0 01.356-.257l4-1.714a1 1 0 11.788 1.838L7.667 9.088l1.94.831a1 1 0 00.787 0l7-3a1 1 0 000-1.838l-7-3zM3.31 9.397L5 10.12v4.102a8.969 8.969 0 00-1.05-.174 1 1 0 01-.89-.89 11.115 11.115 0 01.25-3.762zM9.3 16.573A9.026 9.026 0 007 14.935v-3.957l1.818.78a3 3 0 002.364 0l5.508-2.361a11.026 11.026 0 01.25 3.762 1 1 0 01-.89.89 8.968 8.968 0 00-5.35 2.524 1 1 0 01-1.4 0zM6 18a1 1 0 001-1v-2.065a8.935 8.935 0 00-2-.712V17a1 1 0 001 1z"/>
                </svg>
            </span>
            <div class="leading-tight">
                <span class="text-white font-bold text-lg sm:text-xl tracking-tight block">
                    {{ config('app.name', 'Supervisi Guru') }}
                </span>
                <span class="text-white/70 text-xs sm:text-sm">
                    Portal Masuk Sekolah
                </span>
            </div>
        </div>

        <div>
            <a href="{{ route('super-admin.login') }}"
               class="inline-flex items-center gap-1.5 px-3.5 py-2 text-sm font-medium text-white/90 hover:text-white rounded-lg border border-white/20 hover:border-white/40 transition-colors"
               style="min-height: 44px;">
                <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true">
                    <path fill-rule="evenodd" d="M10 9a3 3 0 100-6 3 3 0 000 6zm-7 9a7 7 0 1114 0H3z" clip-rule="evenodd"/>
                </svg>
                <span>Login Super Admin</span>
            </a>
        </div>
    </header>

    {{-- Konten Utama --}}
    <main class="flex-1 w-full max-w-4xl mx-auto px-4 sm:px-6 py-8 sm:py-12 flex flex-col gap-8">

        {{-- Hero Header --}}
        <div class="text-center max-w-2xl mx-auto">
            <h1 class="text-2xl sm:text-3xl font-extrabold tracking-tight" style="color: var(--color-navy-900, #253C6D);">
                Selamat Datang di Aplikasi Supervisi Guru
            </h1>
            <p class="mt-2.5 text-base sm:text-lg leading-relaxed text-[#5B6475]">
                Cari atau pilih nama sekolah Anda di bawah ini untuk langsung menuju halaman masuk tanpa perlu mengingat alamat link yang panjang.
            </p>
        </div>

        {{-- Kartu Akses Cepat: Sekolah Terakhir yang Dikunjungi --}}
        @if($lastSekolah)
            <div class="bg-white rounded-2xl p-6 sm:p-7 shadow-xs border-2"
                 style="border-color: var(--color-navy-900, #253C6D);">
                <div class="flex items-center gap-2 mb-3">
                    <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-xs font-bold uppercase tracking-wider text-white"
                          style="background-color: var(--color-navy-900, #253C6D);">
                        <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 20 20" fill="currentColor">
                            <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm1-12a1 1 0 10-2 0v4a1 1 0 00.293.707l2.828 2.829a1 1 0 101.415-1.415L11 9.586V6z" clip-rule="evenodd"/>
                        </svg>
                        Sekolah Terakhir Anda
                    </span>
                    <span class="text-xs text-[#5B6475]">Tersimpan di perangkat ini</span>
                </div>

                <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
                    <div class="flex items-center gap-4">
                        <div class="flex-shrink-0 w-14 h-14 rounded-xl flex items-center justify-center text-white font-bold text-xl shadow-xs"
                             style="background-color: var(--color-navy-50, #E6ECF7); color: var(--color-navy-900, #253C6D);">
                            @if($lastSekolah->logo_path)
                                <img src="{{ asset('storage/' . $lastSekolah->logo_path) }}" alt="{{ $lastSekolah->nama }}" class="w-10 h-10 object-contain">
                            @else
                                <svg xmlns="http://www.w3.org/2000/svg" width="30" height="30" viewBox="0 0 20 20" fill="currentColor">
                                    <path d="M10.394 2.08a1 1 0 00-.788 0l-7 3a1 1 0 000 1.84L5.25 8.051a.999.999 0 01.356-.257l4-1.714a1 1 0 11.788 1.838L7.667 9.088l1.94.831a1 1 0 00.787 0l7-3a1 1 0 000-1.838l-7-3zM3.31 9.397L5 10.12v4.102a8.969 8.969 0 00-1.05-.174 1 1 0 01-.89-.89 11.115 11.115 0 01.25-3.762zM9.3 16.573A9.026 9.026 0 007 14.935v-3.957l1.818.78a3 3 0 002.364 0l5.508-2.361a11.026 11.026 0 01.25 3.762 1 1 0 01-.89.89 8.968 8.968 0 00-5.35 2.524 1 1 0 01-1.4 0zM6 18a1 1 0 001-1v-2.065a8.935 8.935 0 00-2-.712V17a1 1 0 001 1z"/>
                                </svg>
                            @endif
                        </div>
                        <div>
                            <h2 class="text-xl font-bold" style="color: var(--color-navy-900, #253C6D);">
                                {{ $lastSekolah->nama }}
                            </h2>
                            <p class="text-sm text-[#5B6475] mt-0.5">
                                NPSN: <span class="font-semibold text-[#1F2937]">{{ $lastSekolah->npsn }}</span>
                                @if($lastSekolah->alamat)
                                    &bull; <span class="truncate">{{ $lastSekolah->alamat }}</span>
                                @endif
                            </p>
                        </div>
                    </div>

                    <div class="flex items-center gap-2.5 sm:self-center">
                        <a href="{{ $lastSekolah->urlLogin() }}"
                           class="inline-flex items-center justify-center gap-2 px-6 py-3.5 text-base font-bold text-white rounded-xl shadow-xs transition-colors"
                           style="background-color: var(--color-action-green, #2E7D32); min-height: 48px;">
                            <span>Lanjut Masuk ke Sekolah Ini</span>
                            <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 20 20" fill="currentColor">
                                <path fill-rule="evenodd" d="M10.293 3.293a1 1 0 011.414 0l6 6a1 1 0 010 1.414l-6 6a1 1 0 01-1.414-1.414L14.586 11H3a1 1 0 110-2h11.586l-4.293-4.293a1 1 0 010-1.414z" clip-rule="evenodd"/>
                            </svg>
                        </a>
                        <a href="{{ route('sekolah.qr', ['kode' => $lastSekolah->kode]) }}"
                           title="Cetak Poster QR Code"
                           class="inline-flex items-center justify-center p-3 text-[#253C6D] bg-[#E6ECF7] hover:bg-[#d8e3f4] rounded-xl transition-colors"
                           style="min-height: 48px; min-width: 48px;">
                            <svg xmlns="http://www.w3.org/2000/svg" width="22" height="22" viewBox="0 0 20 20" fill="currentColor">
                                <path fill-rule="evenodd" d="M3 4a1 1 0 011-1h3a1 1 0 011 1v3a1 1 0 01-1 1H4a1 1 0 01-1-1V4zm2 2V5h1v1H5zM3 13a1 1 0 011-1h3a1 1 0 011 1v3a1 1 0 01-1 1H4a1 1 0 01-1-1v-3zm2 2v-1h1v1H5zM13 3a1 1 0 00-1 1v3a1 1 0 001 1h3a1 1 0 001-1V4a1 1 0 00-1-1h-3zm1 2v1h1V5h-1z" clip-rule="evenodd" />
                                <path d="M11 4a1 1 0 10-2 0v1a1 1 0 002 0V4zM10 7a1 1 0 011 1v1h2a1 1 0 110 2h-3a1 1 0 01-1-1V8a1 1 0 011-1zM16 9a1 1 0 100 2 1 1 0 000-2zM9 13a1 1 0 011-1h1a1 1 0 110 2v2a1 1 0 11-2 0v-3zM7 11a1 1 0 100-2H4a1 1 0 100 2h3zM17 13a1 1 0 01-1 1h-2a1 1 0 110-2h2a1 1 0 011 1zM16 17a1 1 0 100-2h-3a1 1 0 100 2h3z" />
                            </svg>
                        </a>
                    </div>
                </div>
            </div>
        @endif

        {{-- Komponen Pencarian Sekolah (Alpine.js Instan) --}}
        <div class="bg-white rounded-2xl p-6 sm:p-8 shadow-xs border"
             style="border-color: var(--color-border, #E8E0D2);"
             x-data="{
                search: '',
                sekolahs: @js($sekolahList->map(fn($s) => [
                    'id' => $s->id,
                    'nama' => $s->nama,
                    'npsn' => $s->npsn,
                    'kode' => $s->kode,
                    'alamat' => $s->alamat,
                    'url' => $s->urlLogin(),
                    'qr_url' => route('sekolah.qr', ['kode' => $s->kode]),
                    'logo_url' => $s->logo_path ? asset('storage/' . $s->logo_path) : null,
                ])),
                get filteredSekolahs() {
                    if (!this.search.trim()) return this.sekolahs;
                    const q = this.search.toLowerCase().trim();
                    return this.sekolahs.filter(s =>
                        s.nama.toLowerCase().includes(q) ||
                        (s.npsn && s.npsn.toLowerCase().includes(q)) ||
                        (s.alamat && s.alamat.toLowerCase().includes(q))
                    );
                }
             }">

            <div class="mb-6">
                <label for="search-input" class="block text-base sm:text-lg font-bold mb-2" style="color: var(--color-navy-900, #253C6D);">
                    Cari Sekolah Anda:
                </label>
                <div class="relative">
                    <div class="absolute inset-y-0 left-0 pl-4 flex items-center pointer-events-none text-[#5B6475]">
                        <svg xmlns="http://www.w3.org/2000/svg" width="22" height="22" viewBox="0 0 20 20" fill="currentColor">
                            <path fill-rule="evenodd" d="M8 4a4 4 0 100 8 4 4 0 000-8zM2 8a6 6 0 1110.89 3.476l4.817 4.817a1 1 0 01-1.414 1.414l-4.816-4.816A6 6 0 012 8z" clip-rule="evenodd" />
                        </svg>
                    </div>
                    <input
                        type="text"
                        id="search-input"
                        x-model="search"
                        placeholder="Ketik nama sekolah atau nomor NPSN di sini..."
                        class="w-full pl-12 pr-12 py-3.5 text-base sm:text-lg rounded-xl border border-gray-300 focus:outline-none focus:ring-3 focus:ring-[#F2842F] focus:border-transparent transition-all"
                        style="min-height: 52px; background-color: #FFFFFF;"
                        autofocus
                    >
                    <button
                        type="button"
                        x-show="search.length > 0"
                        @click="search = ''"
                        class="absolute inset-y-0 right-0 pr-4 flex items-center text-[#5B6475] hover:text-[#1F2937]"
                        aria-label="Bersihkan pencarian"
                    >
                        <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 20 20" fill="currentColor">
                            <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd" />
                        </svg>
                    </button>
                </div>
                <p class="text-xs sm:text-sm text-[#5B6475] mt-2">
                    Menampilkan <span class="font-semibold text-[#1F2937]" x-text="filteredSekolahs.length"></span> sekolah terdaftar.
                </p>
            </div>

            {{-- Daftar Sekolah --}}
            <div class="space-y-3">
                <template x-for="item in filteredSekolahs" :key="item.id">
                    <div class="flex flex-col sm:flex-row sm:items-center justify-between p-4 sm:p-5 rounded-xl border border-gray-200 hover:border-[#30497D] hover:bg-[#E6ECF7]/30 transition-all gap-4">
                        <div class="flex items-start sm:items-center gap-3.5">
                            <div class="w-12 h-12 rounded-xl flex items-center justify-center shrink-0 font-bold text-lg"
                                 style="background-color: var(--color-navy-50, #E6ECF7); color: var(--color-navy-900, #253C6D);">
                                <template x-if="item.logo_url">
                                    <img :src="item.logo_url" :alt="item.nama" class="w-8 h-8 object-contain">
                                </template>
                                <template x-if="!item.logo_url">
                                    <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 20 20" fill="currentColor">
                                        <path d="M10.394 2.08a1 1 0 00-.788 0l-7 3a1 1 0 000 1.84L5.25 8.051a.999.999 0 01.356-.257l4-1.714a1 1 0 11.788 1.838L7.667 9.088l1.94.831a1 1 0 00.787 0l7-3a1 1 0 000-1.838l-7-3zM3.31 9.397L5 10.12v4.102a8.969 8.969 0 00-1.05-.174 1 1 0 01-.89-.89 11.115 11.115 0 01.25-3.762zM9.3 16.573A9.026 9.026 0 007 14.935v-3.957l1.818.78a3 3 0 002.364 0l5.508-2.361a11.026 11.026 0 01.25 3.762 1 1 0 01-.89.89 8.968 8.968 0 00-5.35 2.524 1 1 0 01-1.4 0zM6 18a1 1 0 001-1v-2.065a8.935 8.935 0 00-2-.712V17a1 1 0 001 1z"/>
                                    </svg>
                                </template>
                            </div>
                            <div>
                                <h3 class="text-base sm:text-lg font-bold leading-snug" style="color: var(--color-navy-900, #253C6D);" x-text="item.nama"></h3>
                                <div class="text-xs sm:text-sm text-[#5B6475] mt-1 flex flex-wrap items-center gap-x-2 gap-y-1">
                                    <span>NPSN: <strong class="text-[#1F2937]" x-text="item.npsn"></strong></span>
                                    <template x-if="item.alamat">
                                        <span>&bull; <span x-text="item.alamat"></span></span>
                                    </template>
                                </div>
                            </div>
                        </div>

                        <div class="flex items-center gap-2 self-end sm:self-center shrink-0">
                            <a :href="item.qr_url"
                               class="inline-flex items-center justify-center p-2.5 rounded-lg border border-gray-300 hover:border-gray-400 text-[#5B6475] hover:text-[#253C6D] transition-colors"
                               title="Cetak Poster QR Code Sekolah"
                               style="min-height: 44px; min-width: 44px;">
                                <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 20 20" fill="currentColor">
                                    <path fill-rule="evenodd" d="M3 4a1 1 0 011-1h3a1 1 0 011 1v3a1 1 0 01-1 1H4a1 1 0 01-1-1V4zm2 2V5h1v1H5zM3 13a1 1 0 011-1h3a1 1 0 011 1v3a1 1 0 01-1 1H4a1 1 0 01-1-1v-3zm2 2v-1h1v1H5zM13 3a1 1 0 00-1 1v3a1 1 0 001 1h3a1 1 0 001-1V4a1 1 0 00-1-1h-3zm1 2v1h1V5h-1z" clip-rule="evenodd" />
                                    <path d="M11 4a1 1 0 10-2 0v1a1 1 0 002 0V4zM10 7a1 1 0 011 1v1h2a1 1 0 110 2h-3a1 1 0 01-1-1V8a1 1 0 011-1zM16 9a1 1 0 100 2 1 1 0 000-2zM9 13a1 1 0 011-1h1a1 1 0 110 2v2a1 1 0 11-2 0v-3zM7 11a1 1 0 100-2H4a1 1 0 100 2h3zM17 13a1 1 0 01-1 1h-2a1 1 0 110-2h2a1 1 0 011 1zM16 17a1 1 0 100-2h-3a1 1 0 100 2h3z" />
                                </svg>
                            </a>

                            <a :href="item.url"
                               class="inline-flex items-center justify-center gap-1.5 px-5 py-2.5 text-sm sm:text-base font-semibold text-white rounded-xl shadow-xs transition-colors"
                               style="background-color: var(--color-navy-900, #253C6D); min-height: 44px;">
                                <span>Pilih Sekolah</span>
                                <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 20 20" fill="currentColor">
                                    <path fill-rule="evenodd" d="M7.293 14.707a1 1 0 010-1.414L10.586 10 7.293 6.707a1 1 0 011.414-1.414l4 4a1 1 0 010 1.414l-4 4a1 1 0 01-1.414 0z" clip-rule="evenodd" />
                                </svg>
                            </a>
                        </div>
                    </div>
                </template>

                {{-- Status Kosong jika Tidak Ada Hasil --}}
                <div x-show="filteredSekolahs.length === 0" class="text-center py-10 px-4">
                    <div class="inline-flex items-center justify-center w-12 h-12 rounded-full bg-amber-50 text-amber-600 mb-3">
                        <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 20 20" fill="currentColor">
                            <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7 4a1 1 0 11-2 0 1 1 0 012 0zm-1-9a1 1 0 00-1 1v4a1 1 0 102 0V6a1 1 0 00-1-1z" clip-rule="evenodd" />
                        </svg>
                    </div>
                    <h4 class="text-base font-bold text-[#1F2937]">Sekolah tidak ditemukan</h4>
                    <p class="text-sm text-[#5B6475] mt-1 max-w-md mx-auto">
                        Tidak ada sekolah yang cocok dengan kata kunci "<span class="font-semibold" x-text="search"></span>". Pastikan penulisan nama sekolah atau nomor NPSN sudah benar.
                    </p>
                </div>
            </div>
        </div>

        {{-- Panduan Cepat untuk Pengguna Senior / Guru Usia 50+ --}}
        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
            {{-- Panduan Pasang di HP --}}
            <div class="bg-white rounded-2xl p-5 sm:p-6 border shadow-xs flex items-start gap-4"
                 style="border-color: var(--color-border, #E8E0D2);">
                <div class="w-12 h-12 rounded-xl flex items-center justify-center shrink-0"
                     style="background-color: var(--color-navy-50, #E6ECF7); color: var(--color-navy-900, #253C6D);">
                    <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 20 20" fill="currentColor">
                        <path d="M7 3a1 1 0 000 2h6a1 1 0 100-2H7zM4 7a1 1 0 011-1h10a1 1 0 011 1v10a2 2 0 01-2 2H6a2 2 0 01-2-2V7z"/>
                    </svg>
                </div>
                <div>
                    <h4 class="text-base font-bold" style="color: var(--color-navy-900, #253C6D);">
                        Tips Pasang di Layar Utama HP
                    </h4>
                    <p class="text-sm text-[#5B6475] mt-1 leading-relaxed">
                        Setelah membuka halaman login sekolah, buka menu browser Anda (ikon titik tiga atau tombol bagikan), lalu pilih <strong>"Tambahkan ke Layar Utama" (Add to Home screen)</strong>. Ikon aplikasi akan langsung ada di layar HP seperti WhatsApp!
                    </p>
                </div>
            </div>

            {{-- Panduan Cetak QR Code --}}
            <div class="bg-white rounded-2xl p-5 sm:p-6 border shadow-xs flex items-start gap-4"
                 style="border-color: var(--color-border, #E8E0D2);">
                <div class="w-12 h-12 rounded-xl flex items-center justify-center shrink-0"
                     style="background-color: var(--color-navy-50, #E6ECF7); color: var(--color-navy-900, #253C6D);">
                    <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 20 20" fill="currentColor">
                        <path fill-rule="evenodd" d="M3 4a1 1 0 011-1h3a1 1 0 011 1v3a1 1 0 01-1 1H4a1 1 0 01-1-1V4zm2 2V5h1v1H5zM3 13a1 1 0 011-1h3a1 1 0 011 1v3a1 1 0 01-1 1H4a1 1 0 01-1-1v-3zm2 2v-1h1v1H5zM13 3a1 1 0 00-1 1v3a1 1 0 001 1h3a1 1 0 001-1V4a1 1 0 00-1-1h-3zm1 2v1h1V5h-1z" clip-rule="evenodd" />
                    </svg>
                </div>
                <div>
                    <h4 class="text-base font-bold" style="color: var(--color-navy-900, #253C6D);">
                        Scan QR Code di Ruang Guru
                    </h4>
                    <p class="text-sm text-[#5B6475] mt-1 leading-relaxed">
                        Admin sekolah dapat mencetak poster QR Code sekolah untuk ditempel di ruang guru. Bapak/Ibu guru cukup membuka kamera ponsel untuk langsung menuju ke halaman login tanpa mengetik apa pun.
                    </p>
                </div>
            </div>
        </div>

    </main>

    {{-- Footer --}}
    <footer class="w-full py-6 text-center text-xs text-[#5B6475] border-t"
            style="border-color: var(--color-border, #E8E0D2); background-color: #FFFFFF;">
        <div class="max-w-4xl mx-auto px-4 flex flex-col sm:flex-row items-center justify-between gap-3">
            <span>&copy; {{ date('Y') }} {{ config('app.name', 'Supervisi Guru') }}. Seluruh hak cipta dilindungi.</span>
            <div class="flex items-center gap-4">
                <a href="{{ route('super-admin.login') }}" class="text-[#5B6475] hover:text-[#253C6D] underline">
                    Login Administrator Sistem
                </a>
            </div>
        </div>
    </footer>

    @livewireScripts
</body>
</html>
