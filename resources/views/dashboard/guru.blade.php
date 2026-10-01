<x-app-layout :namaSekolah="$sekolah->nama" title="Dasbor Guru">
    <x-page-header
        title="Dasbor Guru"
        subtitle="Selamat datang di panel supervisi mandiri {{ $sekolah->nama }}."
    />

    <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-8">
        <x-card class="p-6">
            <div class="flex items-center gap-4">
                <div class="w-12 h-12 rounded-xl flex items-center justify-center text-white"
                     style="background-color: var(--color-action-green);">
                    <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 20 20" fill="currentColor">
                        <path d="M10.394 2.08a1 1 0 00-.788 0l-7 3a1 1 0 000 1.84L5.25 8.051a.999.999 0 01.356-.257l4-1.714a1 1 0 11.788 1.838L7.667 9.088l1.94.831a1 1 0 00.787 0l7-3a1 1 0 000-1.838l-7-3zM3.31 9.397L5 10.12v4.102a8.969 8.969 0 00-1.05-.174 1 1 0 01-.89-.89 11.115 11.115 0 01.25-3.762zM9.3 16.573A9.026 9.026 0 007 14.935v-3.957l1.818.78a3 3 0 002.364 0l5.508-2.361a11.026 11.026 0 01.25 3.762 1 1 0 01-.89.89 8.968 8.968 0 00-5.35 2.524 1 1 0 01-1.4 0zM6 18a1 1 0 001-1v-2.065a8.935 8.935 0 00-2-.712V17a1 1 0 001 1z"/>
                    </svg>
                </div>
                <div>
                    <p class="text-sm font-medium" style="color: var(--color-muted);">Peran Aktif</p>
                    <p class="text-xl font-bold" style="color: var(--color-ink);">Guru</p>
                </div>
            </div>
        </x-card>
    </div>

    <x-card class="p-6">
        <h2 class="text-lg font-bold mb-2" style="color: var(--color-navy-900);">Informasi Supervisi Anda</h2>
        <p class="text-sm leading-relaxed" style="color: var(--color-ink);">
            Hasil penilaian final, jadwal supervisi, dan instrumen yang perlu diisi akan ditampilkan pada menu ini setelah penugasan dibuat oleh admin sekolah.
        </p>
    </x-card>
</x-app-layout>
