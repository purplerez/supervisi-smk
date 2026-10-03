<!DOCTYPE html>
<html lang="id" class="h-full">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Masuk Super Admin — {{ config('app.name', 'Supervisi Guru') }}</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="min-h-full flex flex-col justify-center py-12 sm:px-6 lg:px-8 antialiased" style="background-color: var(--color-cream);">
    <div class="sm:mx-auto sm:w-full sm:max-w-md px-4">
        {{-- Header Super Admin --}}
        <div class="text-center mb-6">
            <div class="inline-flex items-center justify-center w-14 h-14 rounded-2xl shadow-sm mb-4"
                 style="background-color: var(--color-navy-900);">
                <svg xmlns="http://www.w3.org/2000/svg" width="28" height="28" viewBox="0 0 20 20" fill="white" aria-hidden="true">
                    <path fill-rule="evenodd" d="M2.166 4.999A11.954 11.954 0 0010 1.944 11.954 11.954 0 0017.834 5c.11.65.166 1.32.166 2.001 0 5.225-3.34 9.67-8 11.317C5.34 16.67 2 12.225 2 7c0-.682.057-1.35.166-2.001zm11.541 3.708a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/>
                </svg>
            </div>

            <h1 class="text-2xl font-bold tracking-tight" style="color: var(--color-navy-900);">
                Portal Super Admin
            </h1>
            <p class="mt-1 text-sm" style="color: var(--color-muted);">
                Pengelolaan Sistem Multi-Sekolah
            </p>
        </div>

        @if(session('sukses'))
            <div class="mb-4">
                <x-alert-banner type="sukses">{{ session('sukses') }}</x-alert-banner>
            </div>
        @endif

        @if($errors->has('login') || $errors->has('email'))
            <div class="mb-4">
                <x-alert-banner type="galat">{{ $errors->first('login') ?: $errors->first('email') }}</x-alert-banner>
            </div>
        @endif

        {{-- Kartu Login Super Admin --}}
        <div class="card p-6 sm:p-8">
            <form method="POST" action="{{ route('super-admin.login.post') }}" class="space-y-5" novalidate>
                @csrf

                {{-- Input Username / Email --}}
                <x-input
                    name="login"
                    label="Username atau Alamat Email"
                    type="text"
                    autocomplete="username"
                    :value="old('login') ?: old('email')"
                    required
                    placeholder="superadmin atau nama@domain.com"
                    autofocus
                />

                {{-- Input Password --}}
                <x-input
                    name="password"
                    label="Kata Sandi"
                    type="password"
                    autocomplete="current-password"
                    required
                    placeholder="Masukkan kata sandi"
                />

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

                <div class="pt-2">
                    <x-button
                        type="submit"
                        variant="primary"
                        class="w-full justify-center text-base py-3"
                    >
                        Masuk sebagai Super Admin
                    </x-button>
                </div>
            </form>
        </div>
    </div>
</body>
</html>
