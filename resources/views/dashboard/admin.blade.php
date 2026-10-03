<x-app-layout :namaSekolah="$sekolah->nama" title="Dasbor Admin">
    <x-page-header
        title="Dasbor Admin"
        subtitle="Selamat datang di panel administrasi supervisi {{ $sekolah->nama }}."
    />

    <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-8">
        <x-card class="p-6">
            <div class="flex items-center gap-4">
                <div class="w-12 h-12 rounded-xl flex items-center justify-center text-white"
                     style="background-color: var(--color-navy-900);">
                    <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 20 20" fill="currentColor">
                        <path d="M9 6a3 3 0 11-6 0 3 3 0 016 0zM17 6a3 3 0 11-6 0 3 3 0 016 0zM12.93 17c.046-.327.07-.66.07-1a6.97 6.97 0 00-1.5-4.33A5 5 0 0119 16v1h-6.07zM6 11a5 5 0 015 5v1H1v-1a5 5 0 015-5z"/>
                    </svg>
                </div>
                <div>
                    <p class="text-sm font-medium" style="color: var(--color-muted);">Peran Anda</p>
                    <p class="text-xl font-bold" style="color: var(--color-ink);">Admin Sekolah</p>
                </div>
            </div>
        </x-card>

        <x-card class="p-6">
            <div class="flex items-center gap-4">
                <div class="w-12 h-12 rounded-xl flex items-center justify-center text-white"
                     style="background-color: var(--color-action-green);">
                    <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 20 20" fill="currentColor">
                        <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/>
                    </svg>
                </div>
                <div>
                    <p class="text-sm font-medium" style="color: var(--color-muted);">Status Sistem</p>
                    <p class="text-xl font-bold" style="color: var(--color-ink);">Siap Beroperasi</p>
                </div>
            </div>
        </x-card>

        <x-card class="p-6">
            <div class="flex items-center gap-4">
                <div class="w-12 h-12 rounded-xl flex items-center justify-center text-white"
                     style="background-color: var(--color-orange-500);">
                    <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 20 20" fill="currentColor">
                        <path fill-rule="evenodd" d="M6 2a1 1 0 00-1 1v1H4a2 2 0 00-2 2v10a2 2 0 002 2h12a2 2 0 002-2V6a2 2 0 00-2-2h-1V3a1 1 0 10-2 0v1H7V3a1 1 0 00-1-1zm0 5a1 1 0 000 2h8a1 1 0 100-2H6z" clip-rule="evenodd"/>
                    </svg>
                </div>
                <div>
                    <p class="text-sm font-medium" style="color: var(--color-muted);">Kode Sekolah</p>
                    <p class="text-xl font-bold" style="color: var(--color-ink);">{{ $sekolah->kode }}</p>
                </div>
            </div>
        </x-card>
    </div>

    <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-8">
        <x-card class="p-6">
            <h2 class="text-lg font-bold mb-2" style="color: var(--color-navy-900);">Manajemen Guru & Pengguna</h2>
            <p class="text-sm leading-relaxed text-gray-600 mb-4">
                Kelola akun guru, penetapan peran supervisor (penilai), dan kepala sekolah di {{ $sekolah->nama }}.
            </p>
            <x-button variant="primary" :href="route('admin.guru.index', ['kode' => $sekolah->kode])">
                Buka Data Guru & Pengguna &rarr;
            </x-button>
        </x-card>

        <x-card class="p-6">
            <h2 class="text-lg font-bold mb-2" style="color: var(--color-navy-900);">Impor Data Guru (Excel)</h2>
            <p class="text-sm leading-relaxed text-gray-600 mb-4">
                Unggah data guru secara massal menggunakan file Excel dengan alur 3 langkah dan validasi dry run.
            </p>
            <x-button variant="tambah" :href="route('admin.guru.import', ['kode' => $sekolah->kode])">
                Buka Impor Excel &rarr;
            </x-button>
        </x-card>
    </div>
</x-app-layout>
