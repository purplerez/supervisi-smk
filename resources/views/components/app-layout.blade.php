<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="h-full">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>{{ isset($title) ? $title . ' — ' : '' }}{{ config('app.name', 'Supervisi Guru') }}</title>
    <meta name="description" content="{{ $description ?? 'Aplikasi Supervisi Guru berbasis web untuk sekolah.' }}">

    @livewireStyles
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>

{{-- Latar krem sesuai design system --}}
<body class="h-full antialiased" style="background-color: var(--color-cream);">

{{--
    =========================================================
    STRUKTUR:  top-bar (64px) + [sidebar | konten]
    =========================================================
--}}

<div class="min-h-full flex flex-col" x-data="{ sidebarTerbuka: false }">

    {{-- ===== TOP BAR ===== --}}
    <header
        class="fixed top-0 inset-x-0 z-40 flex items-center justify-between px-4 md:px-6 gap-4"
        style="
            background-color: var(--color-navy-900);
            height: 64px;
            box-shadow: 0 1px 0 rgba(255,255,255,0.06);
        "
        role="banner"
    >
        {{-- Kiri: hamburger (mobile) + nama aplikasi --}}
        <div class="flex items-center gap-3">
            {{-- Hamburger (mobile) --}}
            <button
                type="button"
                class="md:hidden touch-target rounded-lg text-white/80 hover:text-white hover:bg-white/10 transition-colors"
                @click="sidebarTerbuka = !sidebarTerbuka"
                :aria-expanded="sidebarTerbuka.toString()"
                aria-controls="sidebar-nav"
                aria-label="Buka menu navigasi"
            >
                <svg x-show="!sidebarTerbuka" xmlns="http://www.w3.org/2000/svg" width="22" height="22" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true">
                    <path fill-rule="evenodd" d="M3 5a1 1 0 011-1h12a1 1 0 110 2H4a1 1 0 01-1-1zM3 10a1 1 0 011-1h12a1 1 0 110 2H4a1 1 0 01-1-1zM3 15a1 1 0 011-1h12a1 1 0 110 2H4a1 1 0 01-1-1z" clip-rule="evenodd"/>
                </svg>
                <svg x-show="sidebarTerbuka" xmlns="http://www.w3.org/2000/svg" width="22" height="22" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true" style="display:none;">
                    <path fill-rule="evenodd" d="M4.293 4.293a1 1 0 011.414 0L10 8.586l4.293-4.293a1 1 0 111.414 1.414L11.414 10l4.293 4.293a1 1 0 01-1.414 1.414L10 11.414l-4.293 4.293a1 1 0 01-1.414-1.414L8.586 10 4.293 5.707a1 1 0 010-1.414z" clip-rule="evenodd"/>
                </svg>
            </button>

            {{-- Logo + Nama App --}}
            <a href="{{ url('/') }}" class="flex items-center gap-2 no-underline">
                <span class="flex items-center justify-center w-8 h-8 rounded-lg"
                      style="background-color: var(--color-orange-500);">
                    <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 20 20"
                         fill="white" aria-hidden="true">
                        <path d="M10.394 2.08a1 1 0 00-.788 0l-7 3a1 1 0 000 1.84L5.25 8.051a.999.999 0 01.356-.257l4-1.714a1 1 0 11.788 1.838L7.667 9.088l1.94.831a1 1 0 00.787 0l7-3a1 1 0 000-1.838l-7-3zM3.31 9.397L5 10.12v4.102a8.969 8.969 0 00-1.05-.174 1 1 0 01-.89-.89 11.115 11.115 0 01.25-3.762zM9.3 16.573A9.026 9.026 0 007 14.935v-3.957l1.818.78a3 3 0 002.364 0l5.508-2.361a11.026 11.026 0 01.25 3.762 1 1 0 01-.89.89 8.968 8.968 0 00-5.35 2.524 1 1 0 01-1.4 0zM6 18a1 1 0 001-1v-2.065a8.935 8.935 0 00-2-.712V17a1 1 0 001 1z"/>
                    </svg>
                </span>
                <span class="text-white font-bold text-base hidden sm:inline leading-tight">
                    {{ config('app.name', 'Supervisi Guru') }}
                </span>
            </a>

            {{-- Nama sekolah --}}
            @if(isset($namaSekolah) || session('nama_sekolah'))
                <span class="hidden md:flex items-center text-white/60 text-sm gap-1">
                    <span class="text-white/30">|</span>
                    {{ $namaSekolah ?? session('nama_sekolah', '') }}
                </span>
            @endif
        </div>

        {{-- Kanan: role switcher + menu pengguna --}}
        <div class="flex items-center gap-2">
            {{-- Role Switcher --}}
            @if(isset($roleSwitcher))
                {{ $roleSwitcher }}
            @elseif(auth()->check() && !auth()->user()->is_super_admin)
                @php
                    $sekolahKode = session('sekolah_kode') ?? request()->route('kode');
                    $rolesUser = auth()->user()->roles->pluck('role')->unique()->all();
                    $activeRole = session('active_role', 'guru');
                    $roleLabels = [
                        'admin' => 'Admin Sekolah',
                        'supervisor' => 'Supervisor',
                        'guru' => 'Guru',
                    ];
                @endphp
                @if(count($rolesUser) > 1 && $sekolahKode)
                    <div class="relative" x-data="{ switcherBuka: false }">
                        <button
                            type="button"
                            class="touch-target flex items-center gap-1.5 px-3 py-1.5 rounded-lg text-xs font-semibold uppercase tracking-wider text-white transition-colors"
                            style="background-color: var(--color-navy-700); border: 1px solid rgba(255,255,255,0.2);"
                            @click="switcherBuka = !switcherBuka"
                            :aria-expanded="switcherBuka.toString()"
                            aria-haspopup="true"
                            aria-label="Ganti peran aktif saat ini"
                            id="role-switcher-button"
                        >
                            <span class="w-2 h-2 rounded-full" style="background-color: var(--color-orange-500);"></span>
                            <span>{{ $roleLabels[$activeRole] ?? ucfirst($activeRole) }}</span>
                            <svg xmlns="http://www.w3.org/2000/svg" width="12" height="12" viewBox="0 0 20 20" fill="currentColor">
                                <path fill-rule="evenodd" d="M5.293 7.293a1 1 0 011.414 0L10 10.586l3.293-3.293a1 1 0 111.414 1.414l-4 4a1 1 0 01-1.414 0l-4-4a1 1 0 010-1.414z" clip-rule="evenodd"/>
                            </svg>
                        </button>

                        <div
                            x-show="switcherBuka"
                            @click.outside="switcherBuka = false"
                            class="absolute right-0 mt-2 w-48 rounded-xl shadow-xl py-1.5 z-50"
                            style="background: #ffffff; border: 1px solid var(--color-border); display:none;"
                        >
                            <p class="px-3 py-1 text-xs font-semibold text-gray-400 uppercase tracking-wider">
                                Ganti Peran Aktif:
                            </p>
                            @foreach($rolesUser as $r)
                                <form method="POST" action="{{ route('role.switch', ['kode' => $sekolahKode]) }}">
                                    @csrf
                                    <input type="hidden" name="role" value="{{ $r }}">
                                    <button
                                        type="submit"
                                        class="w-full flex items-center justify-between px-3 py-2 text-sm text-left transition-colors hover:bg-gray-50 {{ $r === $activeRole ? 'font-bold bg-blue-50/50' : '' }}"
                                        style="color: var(--color-ink);"
                                    >
                                        <span>{{ $roleLabels[$r] ?? ucfirst($r) }}</span>
                                        @if($r === $activeRole)
                                            <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 20 20" fill="currentColor" style="color: var(--color-action-green);">
                                                <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"/>
                                            </svg>
                                        @endif
                                    </button>
                                </form>
                            @endforeach
                        </div>
                    </div>
                @endif
            @endif

            {{-- Menu pengguna --}}
            <div class="relative" x-data="{ menuTerbuka: false }">
                <button
                    type="button"
                    class="touch-target flex items-center gap-2 rounded-lg px-3 text-white/90 hover:text-white hover:bg-white/10 transition-colors"
                    @click="menuTerbuka = !menuTerbuka"
                    :aria-expanded="menuTerbuka.toString()"
                    aria-haspopup="true"
                    aria-controls="user-menu"
                    id="user-menu-button"
                >
                    <span class="w-8 h-8 rounded-full flex items-center justify-center text-sm font-semibold"
                          style="background-color: var(--color-navy-700);">
                        {{ auth()->check() ? substr(auth()->user()->nama ?? 'U', 0, 1) : 'U' }}
                    </span>
                    <span class="hidden sm:block text-sm font-medium max-w-[140px] truncate">
                        {{ auth()->check() ? (auth()->user()->nama ?? 'Pengguna') : 'Pengguna' }}
                    </span>
                    <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true">
                        <path fill-rule="evenodd" d="M5.293 7.293a1 1 0 011.414 0L10 10.586l3.293-3.293a1 1 0 111.414 1.414l-4 4a1 1 0 01-1.414 0l-4-4a1 1 0 010-1.414z" clip-rule="evenodd"/>
                    </svg>
                </button>

                <div
                    id="user-menu"
                    role="menu"
                    aria-labelledby="user-menu-button"
                    x-show="menuTerbuka"
                    @click.outside="menuTerbuka = false"
                    x-transition:enter="transition ease-out duration-100"
                    x-transition:enter-start="opacity-0 scale-95"
                    x-transition:enter-end="opacity-100 scale-100"
                    x-transition:leave="transition ease-in duration-75"
                    x-transition:leave-start="opacity-100 scale-100"
                    x-transition:leave-end="opacity-0 scale-95"
                    class="absolute right-0 mt-2 w-52 rounded-xl shadow-lg py-1 z-50"
                    style="background: #ffffff; border: 1px solid var(--color-border); top: 100%; display:none;"
                >
                    <div class="px-4 py-2 border-b" style="border-color: var(--color-border);">
                        <p class="text-xs font-semibold text-gray-500 uppercase tracking-wider">Sedang Masuk Sebagai</p>
                        <p class="text-sm font-bold truncate" style="color: var(--color-navy-900);">{{ auth()->user()?->nama ?? 'Pengguna' }}</p>
                    </div>

                    <form method="POST" action="{{ route('logout') }}">
                        @csrf
                        <button type="submit" role="menuitem"
                                class="w-full flex items-center gap-3 px-4 py-2.5 text-sm text-left transition-colors hover:bg-red-50"
                                style="color: var(--color-action-red);">
                            <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true">
                                <path fill-rule="evenodd" d="M3 3a1 1 0 00-1 1v12a1 1 0 102 0V4a1 1 0 00-1-1zm10.293 9.293a1 1 0 001.414 1.414l3-3a1 1 0 000-1.414l-3-3a1 1 0 10-1.414 1.414L14.586 9H7a1 1 0 100 2h7.586l-1.293 1.293z" clip-rule="evenodd"/>
                            </svg>
                            Keluar
                        </button>
                    </form>
                </div>
            </div>
        </div>
    </header>

    {{-- ===== BODY (sidebar + konten) ===== --}}
    <div class="flex flex-1" style="padding-top: 64px;">

        {{-- Overlay mobile (menutup sidebar) --}}
        <div
            class="fixed inset-0 z-30 bg-black/30 md:hidden"
            x-show="sidebarTerbuka"
            @click="sidebarTerbuka = false"
            x-transition:enter="transition ease-out duration-200"
            x-transition:enter-start="opacity-0"
            x-transition:enter-end="opacity-100"
            x-transition:leave="transition ease-in duration-150"
            x-transition:leave-start="opacity-100"
            x-transition:leave-end="opacity-0"
            style="display:none;"
            aria-hidden="true"
        ></div>

        {{-- ===== SIDEBAR ===== --}}
        <nav
            id="sidebar-nav"
            class="fixed left-0 bottom-0 z-30 flex flex-col w-64 overflow-y-auto
                   transition-transform duration-200 ease-in-out
                   -translate-x-full md:translate-x-0"
            :class="sidebarTerbuka ? 'translate-x-0 shadow-2xl' : ''"
            style="
                top: 64px;
                background: #ffffff;
                border-right: 1px solid var(--color-border);
            "
            aria-label="Menu navigasi utama"
        >
            <div class="flex-1 py-4 px-2 space-y-0.5">
                {{-- Menu injected dari halaman --}}
                {{ $nav ?? '' }}

                {{-- Default menu berdasarkan role aktif bila $nav tidak diisi --}}
                @unless(isset($nav))
                    @if(auth()->check() && auth()->user()->is_super_admin)
                        <x-nav-item href="{{ route('super-admin.dashboard') }}" :active="request()->routeIs('super-admin.dashboard')">
                            <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true">
                                <path d="M10.707 2.293a1 1 0 00-1.414 0l-7 7a1 1 0 001.414 1.414L4 10.414V17a1 1 0 001 1h2a1 1 0 001-1v-2a1 1 0 011-1h2a1 1 0 011 1v2a1 1 0 001 1h2a1 1 0 001-1v-6.586l.293.293a1 1 0 001.414-1.414l-7-7z"/>
                            </svg>
                            Dasbor
                        </x-nav-item>
                        <x-nav-item href="{{ route('super-admin.sekolah.index') }}" :active="request()->routeIs('super-admin.sekolah.*') && !request()->routeIs('super-admin.sekolah.create')">
                            <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true">
                                <path fill-rule="evenodd" d="M4 4a2 2 0 012-2h8a2 2 0 012 2v12a1 1 0 110 2h-3a1 1 0 01-1-1v-2a1 1 0 00-1-1H9a1 1 0 00-1 1v2a1 1 0 01-1 1H4a1 1 0 110-2V4zm3 1h2v2H7V5zm2 4H7v2h2V9zm2-4h2v2h-2V5zm2 4h-2v2h2V9z" clip-rule="evenodd"/>
                            </svg>
                            Kelola Sekolah
                        </x-nav-item>
                        <x-nav-item href="{{ route('super-admin.sekolah.create') }}" :active="request()->routeIs('super-admin.sekolah.create')">
                            <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true">
                                <path fill-rule="evenodd" d="M10 3a1 1 0 011 1v5h5a1 1 0 110 2h-5v5a1 1 0 11-2 0v-5H4a1 1 0 110-2h5V4a1 1 0 011-1z" clip-rule="evenodd"/>
                            </svg>
                            Tambah Sekolah
                        </x-nav-item>
                        <x-nav-item href="{{ route('super-admin.instrumen.index') }}" :active="request()->routeIs('super-admin.instrumen.*')">
                            <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true">
                                <path fill-rule="evenodd" d="M4 4a2 2 0 012-2h4.586A2 2 0 0112 2.586L15.414 6A2 2 0 0116 7.414V16a2 2 0 01-2 2H6a2 2 0 01-2-2V4zm2 6a1 1 0 011-1h6a1 1 0 110 2H7a1 1 0 01-1-1zm1 3a1 1 0 100 2h6a1 1 0 100-2H7z" clip-rule="evenodd"/>
                            </svg>
                            Template Instrumen
                        </x-nav-item>
                    @elseif(auth()->check())
                        @php
                            $kode = session('sekolah_kode') ?? request()->route('kode');
                            $role = session('active_role', 'guru');
                        @endphp
                        @if($kode)
                            @if($role === 'admin')
                                <x-nav-item href="{{ route('dashboard.admin', ['kode' => $kode]) }}" :active="request()->routeIs('dashboard.admin') || request()->routeIs('admin.dashboard')">
                                    <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true">
                                        <path d="M10.707 2.293a1 1 0 00-1.414 0l-7 7a1 1 0 001.414 1.414L4 10.414V17a1 1 0 001 1h2a1 1 0 001-1v-2a1 1 0 011-1h2a1 1 0 011 1v2a1 1 0 001 1h2a1 1 0 001-1v-6.586l.293.293a1 1 0 001.414-1.414l-7-7z"/>
                                    </svg>
                                    Dasbor Admin
                                </x-nav-item>
                                <x-nav-item href="{{ route('admin.guru.index', ['kode' => $kode]) }}" :active="request()->routeIs('admin.guru.*') && !request()->routeIs('admin.guru.import*')">
                                    <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true">
                                        <path d="M9 6a3 3 0 11-6 0 3 3 0 016 0zM17 6a3 3 0 11-6 0 3 3 0 016 0zM12.93 17c.046-.327.07-.66.07-1a6.97 6.97 0 00-1.5-4.33A5 5 0 0119 16v1h-6.07zM6 11a5 5 0 015 5v1H1v-1a5 5 0 015-5z"/>
                                    </svg>
                                    Data Guru & Pengguna
                                </x-nav-item>
                                <x-nav-item href="{{ route('admin.guru.import', ['kode' => $kode]) }}" :active="request()->routeIs('admin.guru.import*')">
                                    <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true">
                                        <path fill-rule="evenodd" d="M3 17a1 1 0 011-1h12a1 1 0 110 2H4a1 1 0 01-1-1zM6.293 6.707a1 1 0 010-1.414l3-3a1 1 0 011.414 0l3 3a1 1 0 01-1.414 1.414L11 5.414V13a1 1 0 11-2 0V5.414L7.707 6.707a1 1 0 01-1.414 0z" clip-rule="evenodd"/>
                                    </svg>
                                    Impor Data Guru
                                </x-nav-item>
                                <x-nav-item href="{{ route('admin.instrumen.index', ['kode' => $kode]) }}" :active="request()->routeIs('admin.instrumen.*')">
                                    <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true">
                                        <path fill-rule="evenodd" d="M4 4a2 2 0 012-2h4.586A2 2 0 0112 2.586L15.414 6A2 2 0 0116 7.414V16a2 2 0 01-2 2H6a2 2 0 01-2-2V4zm2 6a1 1 0 011-1h6a1 1 0 110 2H7a1 1 0 01-1-1zm1 3a1 1 0 100 2h6a1 1 0 100-2H7z" clip-rule="evenodd"/>
                                    </svg>
                                    Instrumen Supervisi
                                </x-nav-item>
                                <x-nav-item href="{{ route('admin.periode.index', ['kode' => $kode]) }}" :active="request()->routeIs('admin.periode.*')">
                                    <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true">
                                        <path fill-rule="evenodd" d="M6 2a1 1 0 00-1 1v1H4a2 2 0 00-2 2v10a2 2 0 002 2h12a2 2 0 002-2V6a2 2 0 00-2-2h-1V3a1 1 0 10-2 0v1H7V3a1 1 0 00-1-1zm0 5a1 1 0 000 2h8a1 1 0 100-2H6z" clip-rule="evenodd"/>
                                    </svg>
                                    Periode Supervisi
                                </x-nav-item>
                                <x-nav-item href="{{ route('admin.penugasan.index', ['kode' => $kode]) }}" :active="request()->routeIs('admin.penugasan.index')">
                                    <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true">
                                        <path d="M8 9a3 3 0 100-6 3 3 0 000 6zM8 11a6 6 0 00-6 6h12a6 6 0 00-6-6zM16 7a1 1 0 10-2 0v1h-1a1 1 0 100 2h1v1a1 1 0 102 0v-1h1a1 1 0 100-2h-1V7z"/>
                                    </svg>
                                    Penugasan Supervisi
                                </x-nav-item>
                                <x-nav-item href="{{ route('admin.penugasan.belum-dinilai', ['kode' => $kode]) }}" :active="request()->routeIs('admin.penugasan.belum-dinilai')">
                                    <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true">
                                        <path fill-rule="evenodd" d="M8.257 3.099c.765-1.36 2.722-1.36 3.486 0l5.58 9.92c.75 1.334-.213 2.98-1.742 2.98H4.42c-1.53 0-2.493-1.646-1.743-2.98l5.58-9.92zM11 13a1 1 0 11-2 0 1 1 0 012 0zm-1-8a1 1 0 00-1 1v3a1 1 0 002 0V6a1 1 0 00-1-1z" clip-rule="evenodd"/>
                                    </svg>
                                    Belum Punya Penilai
                                </x-nav-item>
                                <x-nav-item href="{{ route('admin.surat-tugas.index', ['kode' => $kode]) }}" :active="request()->routeIs('admin.surat-tugas.*')">
                                    <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true">
                                        <path fill-rule="evenodd" d="M4 4a2 2 0 012-2h4.586A2 2 0 0112 2.586L15.414 6A2 2 0 0116 7.414V16a2 2 0 01-2 2H6a2 2 0 01-2-2V4zm2 6a1 1 0 011-1h6a1 1 0 110 2H7a1 1 0 01-1-1zm1 3a1 1 0 100 2h6a1 1 0 100-2H7z" clip-rule="evenodd"/>
                                    </svg>
                                    Surat Tugas
                                </x-nav-item>
                                <x-nav-item href="{{ route('admin.laporan.index', ['kode' => $kode]) }}" :active="request()->routeIs('admin.laporan.*')">
                                    <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true">
                                        <path d="M2 11a1 1 0 011-1h2a1 1 0 011 1v5a1 1 0 01-1 1H3a1 1 0 01-1-1v-5zM8 7a1 1 0 011-1h2a1 1 0 011 1v9a1 1 0 01-1 1H9a1 1 0 01-1-1V7zM14 4a1 1 0 011-1h2a1 1 0 011 1v12a1 1 0 01-1 1h-2a1 1 0 01-1-1V4z"/>
                                    </svg>
                                    Laporan & Rekap
                                </x-nav-item>
                            @elseif($role === 'supervisor')
                                <div class="px-3 pt-2 pb-1 text-xs font-bold text-muted uppercase tracking-wider">
                                    Menu Penilai
                                </div>
                                <x-nav-item href="{{ route('dashboard.supervisor', ['kode' => $kode]) }}" :active="request()->routeIs('dashboard.supervisor') || request()->routeIs('supervisor.penugasan.*') || request()->routeIs('supervisor.penilaian.*')">
                                    <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true">
                                        <path d="M9 2a1 1 0 000 2h2a1 1 0 100-2H9z"/>
                                        <path fill-rule="evenodd" d="M4 5a2 2 0 012-2 3 3 0 003 3h2a3 3 0 003-3 2 2 0 012 2v11a2 2 0 01-2 2H6a2 2 0 01-2-2V5zm3 4a1 1 0 000 2h.01a1 1 0 100-2H7zm3 0a1 1 0 000 2h3a1 1 0 100-2h-3zm-3 4a1 1 0 100 2h.01a1 1 0 100-2H7zm3 0a1 1 0 100 2h3a1 1 0 100-2h-3z" clip-rule="evenodd"/>
                                    </svg>
                                    Dasbor Supervisor
                                </x-nav-item>
                                <x-nav-item href="{{ route('supervisor.laporan.export.rekap', ['kode' => $kode]) }}" :active="false" download>
                                    <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true">
                                        <path fill-rule="evenodd" d="M3 17a1 1 0 011-1h12a1 1 0 110 2H4a1 1 0 01-1-1zm3.293-7.707a1 1 0 011.414 0L9 10.586V3a1 1 0 112 0v7.586l1.293-1.293a1 1 0 111.414 1.414l-3 3a1 1 0 01-1.414 0l-3-3a1 1 0 010-1.414z" clip-rule="evenodd"/>
                                    </svg>
                                    Unduh Rekap Nilai (.xlsx)
                                </x-nav-item>
                                <x-nav-item href="{{ route('supervisor.laporan.export.ketuntasan', ['kode' => $kode]) }}" :active="false" download>
                                    <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true">
                                        <path fill-rule="evenodd" d="M3 17a1 1 0 011-1h12a1 1 0 110 2H4a1 1 0 01-1-1zm3.293-7.707a1 1 0 011.414 0L9 10.586V3a1 1 0 112 0v7.586l1.293-1.293a1 1 0 111.414 1.414l-3 3a1 1 0 01-1.414 0l-3-3a1 1 0 010-1.414z" clip-rule="evenodd"/>
                                    </svg>
                                    Unduh Ketuntasan (.xlsx)
                                </x-nav-item>

                                <div class="pt-4 mt-3 border-t border-neutral-200 px-3 pb-1 text-xs font-bold text-muted uppercase tracking-wider">
                                    Supervisi Saya (Sebagai Guru)
                                </div>
                                <x-nav-item href="{{ route('guru.dashboard', ['kode' => $kode]) }}" :active="request()->routeIs('guru.dashboard') || request()->routeIs('dashboard.guru')">
                                    <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true">
                                        <path d="M10.707 2.293a1 1 0 00-1.414 0l-7 7a1 1 0 001.414 1.414L4 10.414V17a1 1 0 001 1h2a1 1 0 001-1v-2a1 1 0 011-1h2a1 1 0 011 1v2a1 1 0 001 1h2a1 1 0 001-1v-6.586l.293.293a1 1 0 001.414-1.414l-7-7z"/>
                                    </svg>
                                    Dasbor Supervisi Saya
                                </x-nav-item>
                                <x-nav-item href="{{ route('guru.info-jadwal', ['kode' => $kode]) }}" :active="request()->routeIs('guru.info-jadwal*')">
                                    <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true">
                                        <path fill-rule="evenodd" d="M6 2a1 1 0 00-1 1v1H4a2 2 0 00-2 2v10a2 2 0 002 2h12a2 2 0 002-2V6a2 2 0 00-2-2h-1V3a1 1 0 10-2 0v1H7V3a1 1 0 00-1-1zm0 5a1 1 0 000 2h8a1 1 0 100-2H6z" clip-rule="evenodd"/>
                                    </svg>
                                    Informasi & Jadwal Saya
                                </x-nav-item>
                                <x-nav-item href="{{ route('guru.rapor', ['kode' => $kode]) }}" :active="request()->routeIs('guru.rapor*')">
                                    <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true">
                                        <path d="M10.394 2.08a1 1 0 00-.788 0l-7 3a1 1 0 000 1.84L5.25 8.051a.999.999 0 01.356-.257l4-1.714a1 1 0 11.788 1.838L7.667 9.088l1.94.831a1 1 0 00.787 0l7-3a1 1 0 000-1.838l-7-3zM3.31 9.397L5 10.12v4.102a8.969 8.969 0 00-1.05-.174 1 1 0 01-.89-.89 11.115 11.115 0 01.25-3.762zM9.3 16.573A9.026 9.026 0 007 14.935v-3.957l1.818.78a3 3 0 002.364 0l5.508-2.361a11.026 11.026 0 01.25 3.762 1 1 0 01-.89.89 8.968 8.968 0 00-5.35 2.524 1 1 0 01-1.4 0zM6 18a1 1 0 001-1v-2.065a8.935 8.935 0 00-2-.712V17a1 1 0 001 1z"/>
                                    </svg>
                                    Rapor Nilai Saya
                                </x-nav-item>
                                <x-nav-item href="{{ route('guru.riwayat', ['kode' => $kode]) }}" :active="request()->routeIs('guru.riwayat*')">
                                    <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true">
                                        <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm1-12a1 1 0 10-2 0v4a1 1 0 00.293.707l2.828 2.829a1 1 0 101.415-1.415L11 9.586V6z" clip-rule="evenodd"/>
                                    </svg>
                                    Riwayat Supervisi Saya
                                </x-nav-item>
                            @else
                                <x-nav-item href="{{ route('guru.dashboard', ['kode' => $kode]) }}" :active="request()->routeIs('guru.dashboard') || request()->routeIs('dashboard.guru')">
                                    <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true">
                                        <path d="M10.707 2.293a1 1 0 00-1.414 0l-7 7a1 1 0 001.414 1.414L4 10.414V17a1 1 0 001 1h2a1 1 0 001-1v-2a1 1 0 011-1h2a1 1 0 011 1v2a1 1 0 001 1h2a1 1 0 001-1v-6.586l.293.293a1 1 0 001.414-1.414l-7-7z"/>
                                    </svg>
                                    Dasbor Supervisi
                                </x-nav-item>
                                <x-nav-item href="{{ route('guru.info-jadwal', ['kode' => $kode]) }}" :active="request()->routeIs('guru.info-jadwal*')">
                                    <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true">
                                        <path fill-rule="evenodd" d="M6 2a1 1 0 00-1 1v1H4a2 2 0 00-2 2v10a2 2 0 002 2h12a2 2 0 002-2V6a2 2 0 00-2-2h-1V3a1 1 0 10-2 0v1H7V3a1 1 0 00-1-1zm0 5a1 1 0 000 2h8a1 1 0 100-2H6z" clip-rule="evenodd"/>
                                    </svg>
                                    Informasi & Jadwal
                                </x-nav-item>
                                <x-nav-item href="{{ route('guru.rapor', ['kode' => $kode]) }}" :active="request()->routeIs('guru.rapor*')">
                                    <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true">
                                        <path d="M10.394 2.08a1 1 0 00-.788 0l-7 3a1 1 0 000 1.84L5.25 8.051a.999.999 0 01.356-.257l4-1.714a1 1 0 11.788 1.838L7.667 9.088l1.94.831a1 1 0 00.787 0l7-3a1 1 0 000-1.838l-7-3zM3.31 9.397L5 10.12v4.102a8.969 8.969 0 00-1.05-.174 1 1 0 01-.89-.89 11.115 11.115 0 01.25-3.762zM9.3 16.573A9.026 9.026 0 007 14.935v-3.957l1.818.78a3 3 0 002.364 0l5.508-2.361a11.026 11.026 0 01.25 3.762 1 1 0 01-.89.89 8.968 8.968 0 00-5.35 2.524 1 1 0 01-1.4 0zM6 18a1 1 0 001-1v-2.065a8.935 8.935 0 00-2-.712V17a1 1 0 001 1z"/>
                                    </svg>
                                    Rapor Supervisi
                                </x-nav-item>
                                <x-nav-item href="{{ route('guru.riwayat', ['kode' => $kode]) }}" :active="request()->routeIs('guru.riwayat*')">
                                    <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true">
                                        <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm1-12a1 1 0 10-2 0v4a1 1 0 00.293.707l2.828 2.829a1 1 0 101.415-1.415L11 9.586V6z" clip-rule="evenodd"/>
                                    </svg>
                                    Riwayat Supervisi
                                </x-nav-item>
                            @endif
                        @else
                            <x-nav-item href="{{ url('/') }}" :active="request()->is('/')">
                                <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true">
                                    <path d="M10.707 2.293a1 1 0 00-1.414 0l-7 7a1 1 0 001.414 1.414L4 10.414V17a1 1 0 001 1h2a1 1 0 001-1v-2a1 1 0 011-1h2a1 1 0 011 1v2a1 1 0 001 1h2a1 1 0 001-1v-6.586l.293.293a1 1 0 001.414-1.414l-7-7z"/>
                                </svg>
                                Beranda
                            </x-nav-item>
                        @endif
                    @else
                        <x-nav-item href="{{ url('/') }}" :active="request()->is('/')">
                            <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true">
                                <path d="M10.707 2.293a1 1 0 00-1.414 0l-7 7a1 1 0 001.414 1.414L4 10.414V17a1 1 0 001 1h2a1 1 0 001-1v-2a1 1 0 011-1h2a1 1 0 011 1v2a1 1 0 001 1h2a1 1 0 001-1v-6.586l.293.293a1 1 0 001.414-1.414l-7-7z"/>
                            </svg>
                            Beranda
                        </x-nav-item>
                    @endif
                @endunless
            </div>

            {{-- Footer sidebar --}}
            <div class="p-4 text-xs" style="border-top: 1px solid var(--color-border); color: var(--color-muted);">
                {{ config('app.name') }} &copy; {{ date('Y') }}
            </div>
        </nav>

        {{-- ===== KONTEN UTAMA ===== --}}
        <main
            class="flex-1 min-w-0 md:ml-64"
            id="main-content"
            tabindex="-1"
        >
            <div class="p-4 sm:p-6 lg:p-8 max-w-7xl mx-auto">
                {{-- Flash messages --}}
                @if(session('sukses'))
                    <x-alert-banner type="sukses" class="mb-4">{{ session('sukses') }}</x-alert-banner>
                @endif
                @if(session('galat'))
                    <x-alert-banner type="galat" class="mb-4">{{ session('galat') }}</x-alert-banner>
                @endif
                @if(session('peringatan'))
                    <x-alert-banner type="peringatan" class="mb-4">{{ session('peringatan') }}</x-alert-banner>
                @endif

                {{ $slot }}
            </div>
        </main>
    </div>
</div>

@livewireScripts
</body>
</html>
