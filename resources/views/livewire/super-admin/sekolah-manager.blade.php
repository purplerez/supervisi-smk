<div>
    @if(session('pesan'))
        <x-alert-banner type="success" :message="session('pesan')" class="mb-4" />
    @endif

    <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4 mb-6">
        <div class="w-full sm:w-1/3 relative">
            <x-input wrapperClass="mb-0" wire:model.live.debounce.300ms="search" placeholder="Cari sekolah..." type="search" class="w-full pl-10" />
            <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                <svg class="h-4 w-4 text-gray-400" viewBox="0 0 20 20" fill="currentColor">
                    <path fill-rule="evenodd" d="M8 4a4 4 0 100 8 4 4 0 000-8zM2 8a6 6 0 1110.89 3.476l4.817 4.817a1 1 0 01-1.414 1.414l-4.816-4.816A6 6 0 012 8z" clip-rule="evenodd" />
                </svg>
            </div>
        </div>

        <a href="{{ route('super-admin.sekolah.create') }}" class="btn btn-tambah inline-flex items-center gap-1.5">
            <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true">
                <path fill-rule="evenodd" d="M10 3a1 1 0 011 1v5h5a1 1 0 110 2h-5v5a1 1 0 11-2 0v-5H4a1 1 0 110-2h5V4a1 1 0 011-1z" clip-rule="evenodd"/>
            </svg>
            Tambah Sekolah
        </a>
    </div>

    <x-card class="overflow-x-auto">
        <x-table :headers="['Nama Sekolah', 'Kode', 'NPSN', 'Status', 'Aksi']">
            @forelse($sekolahs as $sekolah)
                <tr class="hover:bg-gray-50/80 transition-colors">
                    <td class="font-medium text-gray-900">{{ $sekolah->nama }}</td>
                    <td class="font-mono text-xs text-gray-500">{{ $sekolah->kode }}</td>
                    <td class="text-sm text-gray-600">{{ $sekolah->npsn ?? '-' }}</td>
                    <td>
                        <x-badge-status :status="$sekolah->status === 'aktif' ? 'final' : 'belum'" :label="ucfirst($sekolah->status)"/>
                    </td>
                    <td class="flex gap-2">
                        <a href="{{ route('super-admin.sekolah.edit', $sekolah->id) }}" class="btn btn-ghost btn-sm">Edit</a>
                        <a href="{{ route('super-admin.sekolah.show', $sekolah->id) }}" class="btn btn-primary btn-sm">Kelola Admin</a>
                    </td>
                </tr>
            @empty
                <tr>
                    <td colspan="5" class="text-center py-6 text-gray-500">Tidak ada data sekolah ditemukan.</td>
                </tr>
            @endforelse
        </x-table>
        <div class="mt-4">
            {{ $sekolahs->links() }}
        </div>
    </x-card>

    <!-- Modal Form -->
    @if($modalFormTerbuka)
    <div class="fixed inset-0 z-50 flex items-center justify-center p-4">
        <div class="absolute inset-0 bg-black/40 backdrop-blur-sm" wire:click="$set('modalFormTerbuka', false)"></div>
        <div class="relative w-full max-w-lg bg-white rounded-2xl p-6 shadow-2xl">
            <h2 class="section-title mb-4">{{ $sekolah_id ? 'Edit Sekolah' : 'Tambah Sekolah' }}</h2>
            
            <form wire:submit="simpan">
                <div class="space-y-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Nama Sekolah</label>
                        <x-input wire:model="nama" class="w-full" required />
                        @error('nama') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
                    </div>
                    
                    <div class="grid grid-cols-2 gap-4">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Kode Sekolah (Unik)</label>
                            <x-input wire:model="kode" class="w-full font-mono" required />
                            @error('kode') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">NPSN (Opsional)</label>
                            <x-input wire:model="npsn" class="w-full" />
                            @error('npsn') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
                        </div>
                    </div>
                    
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Alamat</label>
                        <x-textarea wire:model="alamat" class="w-full" rows="3"></x-textarea>
                        @error('alamat') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
                    </div>
                    
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Status</label>
                        <x-select wire:model="status" class="w-full">
                            <option value="aktif">Aktif</option>
                            <option value="nonaktif">Nonaktif</option>
                        </x-select>
                        @error('status') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
                    </div>
                </div>

                <div class="mt-6 flex justify-end gap-3">
                    <button type="button" wire:click="$set('modalFormTerbuka', false)" class="btn btn-ghost">Batal</button>
                    <button type="submit" class="btn btn-tambah">Simpan</button>
                </div>
            </form>
            
            <button type="button" wire:click="$set('modalFormTerbuka', false)" class="absolute top-4 right-4 text-gray-400 hover:text-gray-600">
                <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 20 20" fill="currentColor">
                    <path fill-rule="evenodd" d="M4.293 4.293a1 1 0 011.414 0L10 8.586l4.293-4.293a1 1 0 111.414 1.414L11.414 10l4.293 4.293a1 1 0 01-1.414 1.414L10 11.414l-4.293 4.293a1 1 0 01-1.414-1.414L8.586 10 4.293 5.707a1 1 0 010-1.414z" clip-rule="evenodd"/>
                </svg>
            </button>
        </div>
    </div>
    @endif
</div>
