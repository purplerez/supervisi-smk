<x-app-layout :namaSekolah="$sekolah->nama" title="Dasbor Admin">
    <x-page-header
        title="Dasbor Admin"
        subtitle="Selamat datang di panel administrasi supervisi {{ $sekolah->nama }}."
    />

    <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-8">
        <x-card class="p-5 hover:border-slate-300 transition-all">
            <div class="flex items-center gap-4">
                <div class="w-11 h-11 rounded-2xl flex items-center justify-center text-white shadow-2xs shrink-0"
                     style="background-color: var(--color-navy-900);">
                    <svg xmlns="http://www.w3.org/2000/svg" width="22" height="22" viewBox="0 0 20 20" fill="currentColor">
                        <path d="M9 6a3 3 0 11-6 0 3 3 0 016 0zM17 6a3 3 0 11-6 0 3 3 0 016 0zM12.93 17c.046-.327.07-.66.07-1a6.97 6.97 0 00-1.5-4.33A5 5 0 0119 16v1h-6.07zM6 11a5 5 0 015 5v1H1v-1a5 5 0 015-5z"/>
                    </svg>
                </div>
                <div>
                    <p class="text-xs font-semibold uppercase tracking-wider text-[#8b92a1]">Peran Anda</p>
                    <p class="text-xl font-bold text-[#0d0d0d] mt-0.5">Admin Sekolah</p>
                </div>
            </div>
        </x-card>

        <x-card class="p-5 hover:border-slate-300 transition-all">
            <div class="flex items-center gap-4">
                <div class="w-11 h-11 rounded-2xl flex items-center justify-center text-white shadow-2xs shrink-0"
                     style="background-color: var(--color-action-green);">
                    <svg xmlns="http://www.w3.org/2000/svg" width="22" height="22" viewBox="0 0 20 20" fill="currentColor">
                        <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/>
                    </svg>
                </div>
                <div>
                    <p class="text-xs font-semibold uppercase tracking-wider text-[#8b92a1]">Status Sistem</p>
                    <p class="text-xl font-bold text-[#0d0d0d] mt-0.5">Siap Beroperasi</p>
                </div>
            </div>
        </x-card>

        <x-card class="p-5 hover:border-slate-300 transition-all">
            <div class="flex items-center gap-4">
                <div class="w-11 h-11 rounded-2xl flex items-center justify-center text-white shadow-2xs shrink-0"
                     style="background-color: var(--color-orange-500);">
                    <svg xmlns="http://www.w3.org/2000/svg" width="22" height="22" viewBox="0 0 20 20" fill="currentColor">
                        <path fill-rule="evenodd" d="M6 2a1 1 0 00-1 1v1H4a2 2 0 00-2 2v10a2 2 0 002 2h12a2 2 0 002-2V6a2 2 0 00-2-2h-1V3a1 1 0 10-2 0v1H7V3a1 1 0 00-1-1zm0 5a1 1 0 000 2h8a1 1 0 100-2H6z" clip-rule="evenodd"/>
                    </svg>
                </div>
                <div>
                    <p class="text-xs font-semibold uppercase tracking-wider text-[#8b92a1]">Kode Sekolah</p>
                    <p class="text-xl font-bold text-[#0d0d0d] mt-0.5">{{ $sekolah->kode }}</p>
                </div>
            </div>
        </x-card>
    </div>

    <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-8">
        <x-card class="p-6 hover:border-slate-300 transition-all">
            <h2 class="text-lg font-bold mb-2 text-[#0d0d0d]">Manajemen Guru & Pengguna</h2>
            <p class="text-sm leading-relaxed text-[#5B6475] mb-5">
                Kelola akun guru, penetapan peran supervisor (penilai), dan kepala sekolah di {{ $sekolah->nama }}.
            </p>
            <x-button variant="primary" :href="route('admin.guru.index', ['kode' => $sekolah->kode])" class="rounded-xl">
                Buka Data Guru & Pengguna &rarr;
            </x-button>
        </x-card>

        <x-card class="p-6 hover:border-slate-300 transition-all">
            <h2 class="text-lg font-bold mb-2 text-[#0d0d0d]">Impor Data Guru (Excel)</h2>
            <p class="text-sm leading-relaxed text-[#5B6475] mb-5">
                Unggah data guru secara massal menggunakan file Excel dengan alur 3 langkah dan validasi dry run.
            </p>
            <x-button variant="tambah" :href="route('admin.guru.import', ['kode' => $sekolah->kode])" class="rounded-xl">
                Buka Impor Excel &rarr;
            </x-button>
        </x-card>
    </div>
</x-app-layout>
