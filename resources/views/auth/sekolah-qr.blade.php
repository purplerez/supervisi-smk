<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Cetak QR Code Login — {{ $sekolah->nama }}</title>
    @livewireStyles
    @vite(['resources/css/app.css', 'resources/js/app.js'])

    <style>
        @media print {
            body {
                background-color: #FFFFFF !important;
                padding: 0 !important;
            }
            .no-print {
                display: none !important;
            }
            .print-poster {
                border: 2px solid #253C6D !important;
                box-shadow: none !important;
                page-break-inside: avoid;
                margin: 0 auto !important;
                width: 100% !important;
                max-width: 100% !important;
            }
            @page {
                size: A4 portrait;
                margin: 1.5cm;
            }
        }
    </style>
</head>
<body class="min-h-full py-6 sm:py-10 px-4 antialiased" style="background-color: var(--color-cream, #FFFAF3);">

    {{-- Kontrol Aksi & Petunjuk (Disembunyikan saat Dicetak) --}}
    <div class="no-print max-w-2xl mx-auto mb-6 flex flex-col sm:flex-row items-center justify-between gap-4 bg-white p-4 sm:p-5 rounded-2xl shadow-xs border"
         style="border-color: var(--color-border, #E8E0D2);">
        <div class="flex items-center gap-3">
            <a href="{{ route('sekolah.login', ['kode' => $sekolah->kode]) }}"
               class="inline-flex items-center gap-1.5 px-4 py-2.5 rounded-xl border border-gray-300 text-sm font-semibold text-[#1F2937] hover:bg-gray-50 transition-colors"
               style="min-height: 44px;">
                <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 20 20" fill="currentColor">
                    <path fill-rule="evenodd" d="M9.707 16.707a1 1 0 01-1.414 0l-6-6a1 1 0 010-1.414l6-6a1 1 0 011.414 1.414L5.414 9H17a1 1 0 110 2H5.414l4.293 4.293a1 1 0 010 1.414z" clip-rule="evenodd"/>
                </svg>
                <span>Halaman Login</span>
            </a>
            <span class="text-xs sm:text-sm text-[#5B6475]">Siap dicetak di kertas A4</span>
        </div>

        <button
            type="button"
            onclick="window.print()"
            class="w-full sm:w-auto inline-flex items-center justify-center gap-2 px-6 py-2.5 text-base font-bold text-white rounded-xl shadow-xs transition-colors"
            style="background-color: var(--color-action-green, #2E7D32); min-height: 44px;">
            <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 20 20" fill="currentColor">
                <path fill-rule="evenodd" d="M5 4v3H4a2 2 0 00-2 2v3a2 2 0 002 2h1v2a2 2 0 002 2h6a2 2 0 002-2v-2h1a2 2 0 002-2V9a2 2 0 00-2-2h-1V4a2 2 0 00-2-2H7a2 2 0 00-2 2zm8 0H7v3h6V4zm0 8H7v4h6v-4z" clip-rule="evenodd" />
            </svg>
            <span>Cetak Poster (Print)</span>
        </button>
    </div>

    {{-- Poster Fisik yang Siap Dicetak --}}
    <div class="print-poster max-w-2xl mx-auto bg-white rounded-3xl p-8 sm:p-12 shadow-sm border-2 text-center"
         style="border-color: var(--color-navy-900, #253C6D);">

        {{-- Kop Poster Sekolah --}}
        <div class="flex items-center justify-center gap-4 mb-4">
            <div class="w-16 h-16 rounded-2xl flex items-center justify-center shrink-0"
                 style="background-color: var(--color-navy-900, #253C6D); color: #FFFFFF;">
                @if($sekolah->logo_path)
                    <img src="{{ asset('storage/' . $sekolah->logo_path) }}" alt="Logo {{ $sekolah->nama }}" class="w-12 h-12 object-contain">
                @else
                    <svg xmlns="http://www.w3.org/2000/svg" width="36" height="36" viewBox="0 0 20 20" fill="currentColor">
                        <path d="M10.394 2.08a1 1 0 00-.788 0l-7 3a1 1 0 000 1.84L5.25 8.051a.999.999 0 01.356-.257l4-1.714a1 1 0 11.788 1.838L7.667 9.088l1.94.831a1 1 0 00.787 0l7-3a1 1 0 000-1.838l-7-3zM3.31 9.397L5 10.12v4.102a8.969 8.969 0 00-1.05-.174 1 1 0 01-.89-.89 11.115 11.115 0 01.25-3.762zM9.3 16.573A9.026 9.026 0 007 14.935v-3.957l1.818.78a3 3 0 002.364 0l5.508-2.361a11.026 11.026 0 01.25 3.762 1 1 0 01-.89.89 8.968 8.968 0 00-5.35 2.524 1 1 0 01-1.4 0zM6 18a1 1 0 001-1v-2.065a8.935 8.935 0 00-2-.712V17a1 1 0 001 1z"/>
                    </svg>
                @endif
            </div>

            <div class="text-left">
                <h1 class="text-xl sm:text-2xl font-black uppercase tracking-tight" style="color: var(--color-navy-900, #253C6D);">
                    {{ $sekolah->nama }}
                </h1>
                <p class="text-xs sm:text-sm text-[#5B6475] font-medium">
                    NPSN: {{ $sekolah->npsn }}
                    @if($sekolah->alamat)
                        &bull; {{ $sekolah->alamat }}
                    @endif
                </p>
            </div>
        </div>

        <div class="w-full border-t-2 my-5" style="border-color: var(--color-navy-900, #253C6D);"></div>

        {{-- Judul Poster --}}
        <div class="mb-6">
            <span class="inline-block px-4 py-1.5 rounded-full text-xs sm:text-sm font-bold tracking-widest uppercase mb-2 text-white"
                  style="background-color: var(--color-navy-900, #253C6D);">
                PORTAL SUPERVISI GURU
            </span>
            <h2 class="text-2xl sm:text-3xl font-extrabold text-[#1F2937]">
                Pindai (Scan) Untuk Masuk
            </h2>
            <p class="text-sm sm:text-base text-[#5B6475] mt-1 max-w-lg mx-auto">
                Arahkan kamera ponsel Anda ke kode QR di bawah untuk langsung membuka formulir masuk tanpa mengetik alamat link.
            </p>
        </div>

        {{-- Gambar QR Code Besar --}}
        <div class="inline-block p-4 sm:p-5 rounded-2xl border-4 bg-white shadow-sm mb-6"
             style="border-color: var(--color-navy-900, #253C6D);">
            <div class="flex items-center justify-center">
                {!! $qrSvg !!}
            </div>
        </div>

        {{-- Teks URL Alternatif --}}
        <div class="mb-8">
            <p class="text-xs text-[#5B6475] uppercase font-semibold tracking-wider mb-1">
                Atau ketik alamat di browser ponsel/laptop:
            </p>
            <div class="inline-block px-4 py-2 rounded-xl bg-gray-100 font-mono text-sm sm:text-base font-bold text-[#253C6D] border border-gray-200 select-all">
                {{ $loginUrl }}
            </div>
        </div>

        {{-- Panduan 3 Langkah Mudah --}}
        <div class="bg-[#FFFAF3] rounded-2xl p-5 sm:p-6 border text-left"
             style="border-color: var(--color-border, #E8E0D2);">
            <h3 class="text-sm sm:text-base font-bold text-[#1F2937] mb-3 flex items-center gap-2">
                <span class="w-6 h-6 rounded-full flex items-center justify-center text-xs font-bold text-white"
                      style="background-color: var(--color-orange-500, #F2842F);">i</span>
                Petunjuk Akses untuk Bapak/Ibu Guru:
            </h3>
            <ol class="space-y-2 text-xs sm:text-sm text-[#1F2937]">
                <li class="flex items-start gap-2.5">
                    <span class="font-bold text-[#253C6D]">1.</span>
                    <span>Buka aplikasi <strong>Kamera</strong> pada ponsel (atau pemindai QR WhatsApp).</span>
                </li>
                <li class="flex items-start gap-2.5">
                    <span class="font-bold text-[#253C6D]">2.</span>
                    <span>Arahkan kamera ke gambar <strong>QR Code</strong> di atas sampai muncul tautan warna kuning/biru.</span>
                </li>
                <li class="flex items-start gap-2.5">
                    <span class="font-bold text-[#253C6D]">3.</span>
                    <span>Ketuk tautan tersebut, lalu masukkan <strong>Username</strong> dan <strong>Password</strong> Anda.</span>
                </li>
            </ol>
        </div>

        <div class="mt-8 pt-4 border-t text-xs text-[#5B6475] flex flex-col sm:flex-row items-center justify-between gap-2">
            <span>Aplikasi Supervisi Guru — {{ $sekolah->nama }}</span>
            <span>Jika lupa kata sandi, silakan hubungi Admin Sekolah / TU</span>
        </div>
    </div>

    @livewireScripts
</body>
</html>
