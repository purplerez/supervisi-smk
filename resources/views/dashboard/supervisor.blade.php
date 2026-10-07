<x-app-layout :namaSekolah="$sekolah->nama" title="Dasbor Supervisor">
    <x-page-header
        title="Dasbor Supervisor"
        subtitle="Selamat datang di panel penilai supervisi {{ $sekolah->nama }}."
    />

    <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-8">
        <x-card class="p-5 hover:border-slate-300 transition-all">
            <div class="flex items-center gap-4">
                <div class="w-11 h-11 rounded-2xl flex items-center justify-center text-white shadow-2xs shrink-0"
                     style="background-color: var(--color-navy-700);">
                    <svg xmlns="http://www.w3.org/2000/svg" width="22" height="22" viewBox="0 0 20 20" fill="currentColor">
                        <path d="M10 12a2 2 0 100-4 2 2 0 000 4z"/>
                        <path fill-rule="evenodd" d="M.458 10C1.732 5.943 5.522 3 10 3s8.268 2.943 9.542 7c-1.274 4.057-5.064 7-9.542 7S1.732 14.057.458 10zM14 10a4 4 0 11-8 0 4 4 0 018 0z" clip-rule="evenodd"/>
                    </svg>
                </div>
                <div>
                    <p class="text-xs font-semibold uppercase tracking-wider text-[#8b92a1]">Peran Aktif</p>
                    <p class="text-xl font-bold text-[#0d0d0d] mt-0.5">Supervisor</p>
                </div>
            </div>
        </x-card>
    </div>

    <x-card class="p-6">
        <h2 class="text-lg font-bold mb-2" style="color: var(--color-navy-900);">Informasi Panel Supervisor</h2>
        <p class="text-sm leading-relaxed" style="color: var(--color-ink);">
            Daftar guru yang ditugaskan kepada Anda serta formulir instrumen supervisi akan muncul di sini saat periode aktif dibuka.
        </p>
    </x-card>
</x-app-layout>
