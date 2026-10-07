<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="h-full">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>{{ isset($title) ? $title . ' — ' : '' }}Super Admin — {{ config('app.name', 'Supervisi Guru') }}</title>
    <meta name="description" content="{{ $description ?? 'Panel Super Admin Aplikasi Supervisi Guru.' }}">

    @livewireStyles
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>

<body class="h-full antialiased" style="background-color: var(--color-cream);">

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
            <a href="{{ route('super-admin.dashboard') }}" class="flex items-center gap-2 no-underline">
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

            {{-- Badge Super Admin --}}
            <span class="hidden sm:inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-semibold"
                  style="background-color: var(--color-orange-500); color: white;">
                Super Admin
            </span>
        </div>

        {{-- Kanan: menu pengguna --}}
        <div class="relative" x-data="{ menuTerbuka: false }">
            <button
                type="button"
                class="touch-target flex items-center gap-2 rounded-lg px-3 text-white/90 hover:text-white hover:bg-white/10 transition-colors"
                @click="menuTerbuka = !menuTerbuka"
                :aria-expanded="menuTerbuka.toString()"
                aria-haspopup="true"
                aria-controls="user-menu-super"
                id="user-menu-super-button"
            >
                <span class="w-8 h-8 rounded-full flex items-center justify-center text-sm font-semibold"
                      style="background-color: var(--color-orange-500);">
                    {{ auth()->check() ? substr(auth()->user()->nama ?? 'S', 0, 1) : 'S' }}
                </span>
                <span class="hidden sm:block text-sm font-medium max-w-[140px] truncate">
                    {{ auth()->check() ? (auth()->user()->nama ?? 'Super Admin') : 'Super Admin' }}
                </span>
                <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true">
                    <path fill-rule="evenodd" d="M5.293 7.293a1 1 0 011.414 0L10 10.586l3.293-3.293a1 1 0 111.414 1.414l-4 4a1 1 0 01-1.414 0l-4-4a1 1 0 010-1.414z" clip-rule="evenodd"/>
                </svg>
            </button>

            <div
                id="user-menu-super"
                role="menu"
                aria-labelledby="user-menu-super-button"
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
                    <p class="text-xs font-semibold text-gray-500 uppercase tracking-wider">Super Admin</p>
                    <p class="text-sm font-bold truncate" style="color: var(--color-navy-900);">{{ auth()->user()?->nama ?? 'Super Admin' }}</p>
                </div>

                <form method="POST" action="{{ route('super-admin.logout') }}">
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
    </header>

    {{-- ===== BODY (sidebar + konten) ===== --}}
    <div class="flex flex-1" style="padding-top: 64px;">

        {{-- Overlay mobile --}}
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
            class="fixed left-0 bottom-0 z-30 flex flex-col w-64
                   transition-transform duration-200 ease-in-out
                   -translate-x-full md:translate-x-0 bg-white border-r border-slate-100/80 shadow-xs"
            :class="sidebarTerbuka ? 'translate-x-0 shadow-2xl' : ''"
            style="top: 64px; border-right: 1px solid rgba(0,0,0,0.05);"
            aria-label="Menu navigasi super admin"
        >
            {{-- Header Sidebar Brand Card (Huge Icons style) --}}
            <div class="px-5 pt-6 pb-4 flex items-center justify-between">
                <div class="flex items-center gap-3.5">
                    <span class="flex items-center justify-center w-11 h-11 rounded-2xl bg-[#c5f4a4] text-[#1c3017] font-bold shadow-xs shrink-0">
                        <svg xmlns="http://www.w3.org/2000/svg" width="22" height="22" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true">
                            <path d="M10.394 2.08a1 1 0 00-.788 0l-7 3a1 1 0 000 1.84L5.25 8.051a.999.999 0 01.356-.257l4-1.714a1 1 0 11.788 1.838L7.667 9.088l1.94.831a1 1 0 00.787 0l7-3a1 1 0 000-1.838l-7-3zM3.31 9.397L5 10.12v4.102a8.969 8.969 0 00-1.05-.174 1 1 0 01-.89-.89 11.115 11.115 0 01.25-3.762zM9.3 16.573A9.026 9.026 0 007 14.935v-3.957l1.818.78a3 3 0 002.364 0l5.508-2.361a11.026 11.026 0 01.25 3.762 1 1 0 01-.89.89 8.968 8.968 0 00-5.35 2.524 1 1 0 01-1.4 0zM6 18a1 1 0 001-1v-2.065a8.935 8.935 0 00-2-.712V17a1 1 0 001 1z"/>
                        </svg>
                    </span>
                    <div class="min-w-0">
                        <h2 class="font-extrabold text-[#0d0d0d] text-lg leading-snug tracking-tight truncate">
                            {{ config('app.name', 'Supervisi Guru') }}
                        </h2>
                        <p class="text-xs font-semibold text-[#8b92a1] tracking-wider uppercase truncate mt-0.5">
                            Panel Super Admin
                        </p>
                    </div>
                </div>
            </div>

            {{-- Nav Items Scroll Area --}}
            <div class="flex-1 px-5 py-2 overflow-y-auto space-y-1">
                <x-nav-item href="{{ route('super-admin.dashboard') }}" :active="request()->routeIs('super-admin.dashboard')">
                    <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true">
                        <path d="M10.707 2.293a1 1 0 00-1.414 0l-7 7a1 1 0 001.414 1.414L4 10.414V17a1 1 0 001 1h2a1 1 0 001-1v-2a1 1 0 011-1h2a1 1 0 011 1v2a1 1 0 001 1h2a1 1 0 001-1v-6.586l.293.293a1 1 0 001.414-1.414l-7-7z"/>
                    </svg>
                    Dasbor
                </x-nav-item>

                <x-nav-item href="{{ route('super-admin.sekolah.index') }}" :active="request()->routeIs('super-admin.sekolah.*') && !request()->routeIs('super-admin.sekolah.create')">
                    <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true">
                        <path fill-rule="evenodd" d="M4 4a2 2 0 012-2h8a2 2 0 012 2v12a1 1 0 110 2h-3a1 1 0 01-1-1v-2a1 1 0 00-1-1H9a1 1 0 00-1 1v2a1 1 0 01-1 1H4a1 1 0 110-2V4zm3 1h2v2H7V5zm2 4H7v2h2V9zm2-4h2v2h-2V5zm2 4h-2v2h2V9z" clip-rule="evenodd"/>
                    </svg>
                    Kelola Sekolah
                </x-nav-item>

                <x-nav-item href="{{ route('super-admin.sekolah.create') }}" :active="request()->routeIs('super-admin.sekolah.create')">
                    <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true">
                        <path fill-rule="evenodd" d="M10 3a1 1 0 011 1v5h5a1 1 0 110 2h-5v5a1 1 0 11-2 0v-5H4a1 1 0 110-2h5V4a1 1 0 011-1z" clip-rule="evenodd"/>
                    </svg>
                    Tambah Sekolah
                </x-nav-item>

                <x-nav-item href="{{ route('super-admin.instrumen.index') }}" :active="request()->routeIs('super-admin.instrumen.*')">
                    <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true">
                        <path fill-rule="evenodd" d="M4 4a2 2 0 012-2h4.586A2 2 0 0112 2.586L15.414 6A2 2 0 0116 7.414V16a2 2 0 01-2 2H6a2 2 0 01-2-2V4zm2 6a1 1 0 011-1h6a1 1 0 110 2H7a1 1 0 01-1-1zm1 3a1 1 0 100 2h6a1 1 0 100-2H7z" clip-rule="evenodd"/>
                    </svg>
                    Template Instrumen
                </x-nav-item>
            </div>

            {{-- Footer Sidebar Profile Card (Huge Icons Style) --}}
            <div class="mt-auto p-4 border-t border-slate-100 bg-white">
                @if(auth()->check())
                    <div class="p-3 bg-[#f4f5f8] rounded-2xl flex items-center justify-between gap-2.5">
                        <div class="flex items-center gap-3 min-w-0">
                            <span class="w-10 h-10 rounded-full bg-[#c5f4a4] text-[#1c3017] font-bold text-sm flex items-center justify-center shrink-0 shadow-2xs">
                                {{ substr(auth()->user()->nama ?? 'S', 0, 1) }}
                            </span>
                            <div class="min-w-0">
                                <h4 class="font-bold text-[#0d0d0d] text-sm leading-snug truncate">
                                    {{ auth()->user()->nama ?? 'Super Admin' }}
                                </h4>
                                <p class="text-xs font-normal text-[#6b7280] truncate">
                                    @<span>{{ Str::slug(auth()->user()->nama ?? 'super_admin', '_') }}</span>
                                </p>
                            </div>
                        </div>

                        {{-- Action 3-dots Dropdown Menu --}}
                        <div class="relative shrink-0" x-data="{ userMenuBuka: false }">
                            <button
                                type="button"
                                @click="userMenuBuka = !userMenuBuka"
                                class="p-1.5 text-slate-400 hover:text-slate-700 hover:bg-white/60 rounded-lg transition-colors focus:outline-none"
                                aria-label="Menu opsi pengguna"
                            >
                                <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4" fill="currentColor" viewBox="0 0 20 20">
                                    <path d="M6 10a2 2 0 11-4 0 2 2 0 014 0zM12 10a2 2 0 11-4 0 2 2 0 014 0zM18 10a2 2 0 11-4 0 2 2 0 014 0z"/>
                                </svg>
                            </button>

                            <div
                                x-show="userMenuBuka"
                                @click.outside="userMenuBuka = false"
                                x-transition:enter="transition ease-out duration-100"
                                x-transition:enter-start="opacity-0 scale-95"
                                x-transition:enter-end="opacity-100 scale-100"
                                x-transition:leave="transition ease-in duration-75"
                                x-transition:leave-start="opacity-100 scale-100"
                                x-transition:leave-end="opacity-0 scale-95"
                                class="absolute right-0 bottom-full mb-2 w-44 bg-white rounded-xl shadow-lg border border-slate-100 py-1 z-50"
                                style="display: none;"
                            >
                                <div class="px-3 py-1.5 border-b border-slate-100">
                                    <p class="text-[10px] font-bold text-slate-400 uppercase tracking-wider">Peran Saat Ini</p>
                                    <p class="text-xs font-semibold text-slate-700 truncate">Super Admin</p>
                                </div>
                                <form method="POST" action="{{ route('logout') }}">
                                    @csrf
                                    <button
                                        type="submit"
                                        class="w-full flex items-center gap-2 px-3 py-2 text-xs font-medium text-red-600 hover:bg-red-50 transition-colors text-left"
                                    >
                                        <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4 text-red-500 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                            <path stroke-linecap="round" stroke-linejoin="round" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1" />
                                        </svg>
                                        <span>Keluar</span>
                                    </button>
                                </form>
                            </div>
                        </div>
                    </div>
                @endif
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
