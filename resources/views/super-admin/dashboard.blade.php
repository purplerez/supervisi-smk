<x-layouts.super-admin title="Dasbor Super Admin">
    <x-page-header
        title="Dasbor Super Admin"
        subtitle="Manajemen dan pemantauan lintas sekolah."
    >
        <x-slot:actions>
            <x-button variant="tambah" href="{{ route('super-admin.sekolah.create') }}">
                <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true">
                    <path fill-rule="evenodd" d="M10 3a1 1 0 011 1v5h5a1 1 0 110 2h-5v5a1 1 0 11-2 0v-5H4a1 1 0 110-2h5V4a1 1 0 011-1z" clip-rule="evenodd"/>
                </svg>
                Tambah Sekolah
            </x-button>
        </x-slot:actions>
    </x-page-header>

    <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-8">
        <x-card class="p-6">
            <div class="flex items-center gap-4">
                <div class="w-12 h-12 rounded-xl flex items-center justify-center text-white"
                     style="background-color: var(--color-navy-900);">
                    <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 20 20" fill="currentColor">
                        <path d="M10.707 2.293a1 1 0 00-1.414 0l-7 7a1 1 0 001.414 1.414L4 10.414V17a1 1 0 001 1h2a1 1 0 001-1v-2a1 1 0 011-1h2a1 1 0 011 1v2a1 1 0 001 1h2a1 1 0 001-1v-6.586l.293.293a1 1 0 001.414-1.414l-7-7z"/>
                    </svg>
                </div>
                <div>
                    <p class="text-sm font-medium" style="color: var(--color-muted);">Total Sekolah Terdaftar</p>
                    <p class="text-2xl font-bold" style="color: var(--color-ink);">{{ $jumlahSekolah }}</p>
                </div>
            </div>
        </x-card>
    </div>

    <livewire:super-admin.sekolah-manager />
</x-layouts.super-admin>
