<!DOCTYPE html>
<html lang="id" class="h-full">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Wajib Ganti Kata Sandi — {{ $sekolah->nama }}</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="min-h-full flex flex-col justify-center py-12 sm:px-6 lg:px-8 antialiased" style="background-color: var(--color-cream);">
    <div class="sm:mx-auto sm:w-full sm:max-w-md px-4">
        {{-- Header --}}
        <div class="text-center mb-6">
            <div class="inline-flex items-center justify-center w-14 h-14 rounded-2xl shadow-sm mb-4"
                 style="background-color: var(--color-orange-500);">
                <svg xmlns="http://www.w3.org/2000/svg" width="28" height="28" viewBox="0 0 20 20" fill="white" aria-hidden="true">
                    <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7-4a1 1 0 11-2 0 1 1 0 012 0zM9 9a1 1 0 000 2v3a1 1 0 001 1h1a1 1 0 100-2v-3a1 1 0 00-1-1H9z" clip-rule="evenodd"/>
                </svg>
            </div>

            <h1 class="text-2xl font-bold tracking-tight" style="color: var(--color-navy-900);">
                Ganti Kata Sandi Awal
            </h1>
            <p class="mt-1 text-sm" style="color: var(--color-muted);">
                {{ $user->nama }} ({{ $sekolah->nama }})
            </p>
        </div>

        @if(session('peringatan'))
            <div class="mb-4">
                <x-alert-banner type="peringatan">{{ session('peringatan') }}</x-alert-banner>
            </div>
        @endif

        {{-- Kartu Ganti Kata Sandi --}}
        <div class="card p-6 sm:p-8">
            <p class="text-sm mb-5 leading-relaxed" style="color: var(--color-ink);">
                Demi keamanan akun Anda, silakan buat kata sandi baru pribadi Anda (minimal 8 karakter).
            </p>

            <form method="POST" action="{{ route('password.update', ['kode' => $sekolah->kode]) }}" class="space-y-5" novalidate>
                @csrf

                {{-- Password Baru --}}
                <x-input
                    name="password"
                    label="Kata Sandi Baru"
                    type="password"
                    required
                    placeholder="Minimal 8 karakter"
                    helper="Gunakan kombinasi huruf, angka, atau simbol agar lebih aman."
                    autofocus
                />

                {{-- Konfirmasi Password --}}
                <x-input
                    name="password_confirmation"
                    label="Ulangi Kata Sandi Baru"
                    type="password"
                    required
                    placeholder="Masukkan ulang kata sandi baru"
                />

                <div class="pt-2">
                    <x-button
                        type="submit"
                        variant="primary"
                        class="w-full justify-center text-base py-3"
                    >
                        Simpan Kata Sandi Baru
                    </x-button>
                </div>
            </form>
        </div>

        {{-- Opsi Keluar bila tidak ingin lanjut --}}
        <div class="mt-6 text-center">
            <form method="POST" action="{{ route('logout') }}">
                @csrf
                <button type="submit" class="text-sm underline hover:opacity-80 transition-opacity" style="color: var(--color-action-red);">
                    Keluar dari akun
                </button>
            </form>
        </div>
    </div>
</body>
</html>
