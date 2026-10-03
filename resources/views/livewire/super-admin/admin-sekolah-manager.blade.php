
<x-app-layout title="Kelola Admin Sekolah">
    <div class="mb-6">
        <a href="{{ route('super-admin.dashboard') }}" class="text-sm font-medium hover:underline flex items-center gap-1" style="color: var(--color-navy-700);">
            <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 20 20" fill="currentColor">
                <path fill-rule="evenodd" d="M12.707 5.293a1 1 0 010 1.414L9.414 10l3.293 3.293a1 1 0 01-1.414 1.414l-4-4a1 1 0 010-1.414l4-4a1 1 0 011.414 0z" clip-rule="evenodd"/>
            </svg>
            Kembali ke Dasbor
        </a>
    </div>

    <x-page-header
        title="Kelola Admin: {{ $sekolah->nama }}"
        subtitle="Kelola akun admin untuk {{ $sekolah->nama }} ({{ $sekolah->kode }})."
    />

    @if(session('pesan'))
        <x-alert-banner type="success" :message="session('pesan')" class="mb-4" />
    @endif

    <div class="flex justify-end mb-4">
        <button wire:click="tambah" class="btn btn-tambah">
            Tambah Admin
        </button>
    </div>

    <x-card class="overflow-x-auto">
        <x-table :headers="['Nama', 'Username', 'Email', 'Status', 'Aksi']">
            @foreach($this->admins as $admin)
                <tr class="hover:bg-gray-50 transition-colors">
                    <td class="font-semibold text-gray-900">{{ $admin->nama }}</td>
                    <td class="font-mono text-sm">{{ $admin->username }}</td>
                    <td>{{ $admin->email ?? '-' }}</td>
                    <td>
                        <x-badge-status :status="$admin->aktif ? 'final' : 'belum'" :label="$admin->aktif ? 'Aktif' : 'Nonaktif'"/>
                    </td>
                    <td class="flex gap-2">
                        <button wire:click="edit({{ $admin->id }})" class="btn btn-ghost text-sm py-1 px-3">Edit</button>
                        <button wire:click="konfirmasiReset({{ $admin->id }})" class="btn btn-outline text-sm py-1 px-3 text-yellow-600 border-yellow-600 hover:bg-yellow-50">Reset Password</button>
                    </td>
                </tr>
            @endforeach
        </x-table>
    </x-card>

    <!-- Modal Form -->
    <div x-data="{ open: @entangle('modalFormTerbuka') }" x-show="open" x-cloak class="fixed inset-0 z-50 flex items-center justify-center p-4">
        <div class="absolute inset-0 bg-black/40 backdrop-blur-sm" @click="open = false"></div>
        <div class="relative w-full max-w-lg bg-white rounded-2xl p-6 shadow-2xl" @click.stop>
            <h2 class="section-title mb-4">{{ $user_id ? 'Edit Admin' : 'Tambah Admin' }}</h2>
            
            <form wire:submit="simpan">
                <div class="space-y-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Nama Lengkap</label>
                        <x-input wire:model="nama" class="w-full" required />
                        @error('nama') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
                    </div>
                    
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Username (Unik per sekolah)</label>
                        <x-input wire:model="username" class="w-full font-mono" required />
                        @error('username') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
                    </div>
                    
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Email (Opsional)</label>
                        <x-input wire:model="email" type="email" class="w-full" />
                        @error('email') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
                    </div>
                    
                    <div>
                        <label class="flex items-center gap-2">
                            <input type="checkbox" wire:model="aktif" class="rounded text-orange-500 focus:ring-orange-500">
                            <span class="text-sm font-medium text-gray-700">Akun Aktif</span>
                        </label>
                        @error('aktif') <div class="text-red-500 text-xs mt-1">{{ $message }}</div> @enderror
                    </div>
                </div>

                <div class="mt-6 flex justify-end gap-3">
                    <button type="button" @click="open = false" class="btn btn-ghost">Batal</button>
                    <button type="submit" class="btn btn-tambah">Simpan</button>
                </div>
            </form>
        </div>
    </div>

    <!-- Modal Konfirmasi Reset Password -->
    <div x-data="{ open: @entangle('modalResetTerbuka') }" x-show="open" x-cloak class="fixed inset-0 z-50 flex items-center justify-center p-4">
        <div class="absolute inset-0 bg-black/40 backdrop-blur-sm" @click="open = false"></div>
        <div class="relative w-full max-w-md bg-white rounded-2xl p-6 shadow-2xl" @click.stop>
            <h2 class="section-title mb-4">Reset Password</h2>
            <p class="mb-6 text-gray-600">Anda yakin ingin mereset password admin ini? Password baru akan digenerate otomatis.</p>
            
            <div class="flex justify-end gap-3">
                <button type="button" @click="open = false" class="btn btn-ghost">Batal</button>
                <button type="button" wire:click="resetPassword" class="btn text-white bg-yellow-600 hover:bg-yellow-700">Ya, Reset</button>
            </div>
        </div>
    </div>

    <!-- Modal Tampilkan Password Baru -->
    <div x-data="{ open: @entangle('showPasswordModal') }" x-show="open" x-cloak class="fixed inset-0 z-50 flex items-center justify-center p-4">
        <div class="absolute inset-0 bg-black/40 backdrop-blur-sm" @click="open = false"></div>
        <div class="relative w-full max-w-md bg-white rounded-2xl p-6 shadow-2xl" @click.stop>
            <h2 class="section-title mb-4">Kredensial Admin</h2>
            <p class="mb-4 text-sm text-gray-600">Simpan informasi login ini, karena tidak akan ditampilkan lagi. Pengguna akan diminta mengganti password saat login pertama.</p>
            
            <div class="bg-gray-50 p-4 rounded-lg font-mono text-center mb-6">
                <div class="text-xs text-gray-500 mb-1">Password Sementara:</div>
                <div class="text-xl font-bold tracking-wider text-gray-900">{{ $newPassword }}</div>
            </div>
            
            <div class="flex justify-end">
                <button type="button" @click="open = false" class="btn btn-primary">Tutup</button>
            </div>
        </div>
    </div>
</x-app-layout>