<!DOCTYPE html>
<html lang="id" class="h-full">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Masuk — {{ $sekolah->nama }} — {{ config('app.name', 'Supervisi Guru') }}</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="min-h-full flex flex-col justify-center py-12 sm:px-6 lg:px-8 antialiased" style="background-color: var(--color-cream);">
    <div class="sm:mx-auto sm:w-full sm:max-w-md px-4">
        {{-- Header / Logo Sekolah --}}
        <div class="text-center mb-6">
            <div class="inline-flex items-center justify-center w-14 h-14 rounded-2xl shadow-sm mb-4"
                 style="background-color: var(--color-navy-900);">
                @if($sekolah->logo_path)
                    <img src="{{ asset('storage/' . $sekolah->logo_path) }}" alt="Logo {{ $sekolah->nama }}" class="w-10 h-10 object-contain">
                @else
                    <svg xmlns="http://www.w3.org/2000/svg" width="28" height="28" viewBox="0 0 20 20" fill="white" aria-hidden="true">
                        <path d="M10.394 2.08a1 1 0 00-.788 0l-7 3a1 1 0 000 1.84L5.25 8.051a.999.999 0 01.356-.257l4-1.714a1 1 0 11.788 1.838L7.667 9.088l1.94.831a1 1 0 00.787 0l7-3a1 1 0 000-1.838l-7-3zM3.31 9.397L5 10.12v4.102a8.969 8.969 0 00-1.05-.174 1 1 0 01-.89-.89 11.115 11.115 0 01.25-3.762zM9.3 16.573A9.026 9.026 0 007 14.935v-3.957l1.818.78a3 3 0 002.364 0l5.508-2.361a11.026 11.026 0 01.25 3.762 1 1 0 01-.89.89 8.968 8.968 0 00-5.35 2.524 1 1 0 01-1.4 0zM6 18a1 1 0 001-1v-2.065a8.935 8.935 0 00-2-.712V17a1 1 0 001 1z"/>
                    </svg>
                @endif
            </div>

            <h1 class="text-2xl font-bold tracking-tight" style="color: var(--color-navy-900);">
                {{ $sekolah->nama }}
            </h1>
            <p class="mt-1 text-sm" style="color: var(--color-muted);">
                Aplikasi Supervisi Guru
            </p>
        </div>

        {{-- Banner Pesan Sukses / Galat / Status Sekolah --}}
        @if($sekolah->status === 'nonaktif')
            <div class="mb-4">
                <x-alert-banner type="galat">
                    Sekolah ini sedang berstatus <strong>nonaktif</strong>. Akses login ditutup sementara. Silakan hubungi administrator.
                </x-alert-banner>
            </div>
        @endif

        @if(session('sukses'))
            <div class="mb-4">
                <x-alert-banner type="sukses">{{ session('sukses') }}</x-alert-banner>
            </div>
        @endif

        @if($errors->has('username'))
            <div class="mb-4">
                <x-alert-banner type="galat">{{ $errors->first('username') }}</x-alert-banner>
            </div>
        @endif

        {{-- Kartu Login --}}
        <div class="card p-6 sm:p-8">
            <form method="POST" action="{{ route('sekolah.login.post', ['kode' => $sekolah->kode]) }}" class="space-y-5" novalidate>
                @csrf

                {{-- Input Username --}}
                <x-input
                    name="username"
                    label="Nama Pengguna (Username)"
                    type="text"
                    autocomplete="username"
                    :value="old('username')"
                    required
                    placeholder="Masukkan username Anda"
                    autofocus
                />

                {{-- Input Password --}}
                <x-input
                    name="password"
                    label="Kata Sandi (Password)"
                    type="password"
                    autocomplete="current-password"
                    required
                    placeholder="Masukkan kata sandi Anda"
                />

                {{-- Opsi Ingat Saya --}}
                <div class="flex items-center justify-between pt-1">
                    <label class="flex items-center gap-2.5 text-sm cursor-pointer select-none" style="color: var(--color-ink);">
                        <input
                            type="checkbox"
                            name="remember"
                            class="w-4 h-4 rounded text-blue-900 border-gray-300 focus:ring-2 focus:ring-offset-2"
                            style="accent-color: var(--color-navy-900);"
                        >
                        <span>Ingat saya di perangkat ini</span>
                    </label>
                </div>

                {{-- Tombol Masuk --}}
                <div class="pt-2">
                    <x-button
                        type="submit"
                        variant="primary"
                        class="w-full justify-center text-base py-3"
                        :disabled="$sekolah->status === 'nonaktif'"
                    >
                        Masuk ke Aplikasi
                    </x-button>
                </div>
            </form>
        </div>

        <p class="mt-6 text-center text-xs" style="color: var(--color-muted);">
            Lupa kata sandi? Silakan hubungi admin sekolah Anda.
        </p>
    </div>
</body>
</html>
