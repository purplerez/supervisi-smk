<x-app-layout title="Dasbor Super Admin">
    <x-page-header
        title="Dasbor Super Admin"
        subtitle="Manajemen dan pemantauan lintas sekolah."
    />

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

    <x-card class="p-6">
        <h2 class="text-lg font-bold mb-4" style="color: var(--color-navy-900);">Daftar Sekolah Terbaru</h2>
        @if($sekolahList->isEmpty())
            <x-empty-state
                title="Belum ada sekolah terdaftar"
                description="Tambahkan sekolah baru untuk memulai sistem supervisi multi-tenant."
            />
        @else
            <x-table :headers="['Nama Sekolah', 'Kode', 'NPSN', 'Status']">
                @foreach($sekolahList as $s)
                    <tr class="hover:bg-gray-50 transition-colors">
                        <td class="font-semibold text-gray-900">{{ $s->nama }}</td>
                        <td class="font-mono text-xs">{{ $s->kode }}</td>
                        <td>{{ $s->npsn ?? '-' }}</td>
                        <td>
                            <x-badge-status :status="$s->status === 'aktif' ? 'final' : 'belum'" :label="ucfirst($s->status)"/>
                        </td>
                    </tr>
                @endforeach
            </x-table>
        @endif
    </x-card>
</x-app-layout>
