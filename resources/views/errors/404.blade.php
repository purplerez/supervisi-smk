<!DOCTYPE html>
<html lang="id" class="h-full">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>404 — Halaman Tidak Ditemukan — {{ config('app.name', 'Supervisi Guru') }}</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="min-h-full flex items-center justify-center py-12 px-4 sm:px-6 lg:px-8 antialiased" style="background-color: var(--color-cream);">
    <div class="max-w-md w-full text-center">
        <div class="card p-8">
            <div class="w-16 h-16 mx-auto mb-4 rounded-2xl flex items-center justify-center text-white"
                 style="background-color: var(--color-navy-700);">
                <svg xmlns="http://www.w3.org/2000/svg" width="32" height="32" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true">
                    <path fill-rule="evenodd" d="M8 4a4 4 0 100 8 4 4 0 000-8zM2 8a6 6 0 1110.89 3.476l4.817 4.817a1 1 0 01-1.414 1.414l-4.816-4.816A6 6 0 012 8z" clip-rule="evenodd"/>
                </svg>
            </div>

            <p class="text-sm font-bold uppercase tracking-wider mb-1" style="color: var(--color-orange-700);">
                Galat 404
            </p>
            <h1 class="text-2xl font-bold mb-3" style="color: var(--color-navy-900);">
                Halaman Tidak Ditemukan
            </h1>
            <p class="text-base mb-6 leading-relaxed" style="color: var(--color-ink);">
                {{ $exception->getMessage() ?: 'Halaman atau data yang Anda cari tidak tersedia, sudah dipindahkan, atau Anda tidak memiliki akses ke data sekolah tersebut.' }}
            </p>

            <div class="flex flex-col sm:flex-row gap-3 justify-center">
                <a href="{{ url()->previous() !== url()->current() ? url()->previous() : url('/') }}"
                   class="btn btn-outline touch-target">
                    Kembali ke Sebelumnya
                </a>
                <a href="{{ url('/') }}"
                   class="btn btn-primary touch-target">
                    Ke Halaman Utama
                </a>
            </div>
        </div>
    </div>
</body>
</html>
