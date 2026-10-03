<x-app-layout :namaSekolah="$sekolah->nama" title="Data Guru & Pengguna">
    <div x-data="{
        modalTambah: false,
        modalEdit: false,
        modalReset: false,
        modalKepalaSekolah: false,
        modalKonfirm: false,
        konfirmPesan: '',
        konfirmLabel: '',
        konfirmFormId: '',
        editUser: { id: '', nama: '', email: '', nip: '', nuptk: '' },
        resetUser: { id: '', nama: '' },

        bukaEdit(user) {
            this.editUser = {
                id: user.id,
                nama: user.nama,
                email: user.email || '',
                nip: user.nip || '',
                nuptk: user.nuptk || ''
            };
            this.modalEdit = true;
        },

        bukaReset(user) {
            this.resetUser = {
                id: user.id,
                nama: user.nama
            };
            this.modalReset = true;
        },

        bukaKonfirm(formId, pesan, label) {
            this.konfirmFormId = formId;
            this.konfirmPesan = pesan;
            this.konfirmLabel = label;
            this.modalKonfirm = true;
        },

        konfirmSetuju() {
            this.modalKonfirm = false;
            const form = document.getElementById(this.konfirmFormId);
            if (form) form.submit();
        }
    }">
        {{-- Header Halaman --}}
        <x-page-header
            title="Data Guru & Pengguna Sekolah"
            subtitle="Kelola akun guru, peran supervisor, dan penetapan kepala sekolah untuk {{ $sekolah->nama }}."
        >
            <x-slot name="actions">
                <div class="flex flex-wrap items-center gap-3">
                    <x-button variant="tambah" @click="modalTambah = true" type="button">
                        <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 20 20" fill="currentColor" class="mr-1.5">
                            <path fill-rule="evenodd" d="M10 3a1 1 0 011 1v5h5a1 1 0 110 2h-5v5a1 1 0 11-2 0v-5H4a1 1 0 110-2h5V4a1 1 0 011-1z" clip-rule="evenodd"/>
                        </svg>
                        Tambah Pengguna
                    </x-button>

                    <x-button variant="primary" :href="route('admin.guru.import', ['kode' => $sekolah->kode])">
                        <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 20 20" fill="currentColor" class="mr-1.5">
                            <path fill-rule="evenodd" d="M3 17a1 1 0 011-1h12a1 1 0 110 2H4a1 1 0 01-1-1zM6.293 6.707a1 1 0 010-1.414l3-3a1 1 0 011.414 0l3 3a1 1 0 01-1.414 1.414L11 5.414V13a1 1 0 11-2 0V5.414L7.707 6.707a1 1 0 01-1.414 0z" clip-rule="evenodd"/>
                        </svg>
                        Impor Excel
                    </x-button>
                </div>
            </x-slot>
        </x-page-header>

        {{-- Kartu Info Kepala Sekolah --}}
        <x-card class="p-5 mb-6 border-l-4" style="border-left-color: var(--color-navy-900);">
            <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
                <div class="flex items-center gap-4">
                    <div class="w-12 h-12 rounded-xl flex items-center justify-center text-white shrink-0"
                         style="background-color: var(--color-navy-900);">
                        <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 20 20" fill="currentColor">
                            <path fill-rule="evenodd" d="M10 9a3 3 0 100-6 3 3 0 000 6zm-7 9a7 7 0 1114 0H3z" clip-rule="evenodd"/>
                        </svg>
                    </div>
                    <div>
                        <p class="text-xs font-semibold text-gray-500 uppercase tracking-wider">Kepala Sekolah Saat Ini</p>
                        <p class="text-lg font-bold text-gray-900">
                            {{ $sekolah->kepalaSekolah ? $sekolah->kepalaSekolah->nama : 'Belum Ditentukan' }}
                        </p>
                        @if($sekolah->kepalaSekolah && $sekolah->kepalaSekolah->nip)
                            <p class="text-xs text-gray-600">NIP. {{ $sekolah->kepalaSekolah->nip }}</p>
                        @endif
                    </div>
                </div>

                <div>
                    <x-button variant="outline" size="sm" @click="modalKepalaSekolah = true" type="button">
                        <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 20 20" fill="currentColor" class="mr-1">
                            <path d="M13.586 3.586a2 2 0 112.828 2.828l-.793.793-2.828-2.828.793-.793zM11.379 5.793L3 14.172V17h2.828l8.38-8.379-2.83-2.828z"/>
                        </svg>
                        Tetapkan Kepala Sekolah
                    </x-button>
                </div>
            </div>
        </x-card>

        {{-- Filter & Pencarian --}}
        <x-card class="p-5 mb-6">
            <form method="GET" action="{{ route('admin.guru.index', ['kode' => $sekolah->kode]) }}" class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-12 gap-4 items-end">
                <div class="lg:col-span-5">
                    <label for="cari" class="block text-sm font-semibold mb-1 text-gray-700">Pencarian</label>
                    <input
                        type="text"
                        name="cari"
                        id="cari"
                        value="{{ $cari }}"
                        placeholder="Nama, username, NIP, email..."
                        class="w-full rounded-lg border border-gray-300 px-3.5 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-amber-500 focus:border-amber-500"
                    >
                </div>

                <div class="lg:col-span-3">
                    <label for="status" class="block text-sm font-semibold mb-1 text-gray-700">Status Akun</label>
                    <select
                        name="status"
                        id="status"
                        class="w-full rounded-lg border border-gray-300 px-3 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-amber-500 focus:border-amber-500"
                    >
                        <option value="semua" {{ $status === 'semua' || !$status ? 'selected' : '' }}>Semua Status</option>
                        <option value="aktif" {{ $status === 'aktif' ? 'selected' : '' }}>Aktif Saja</option>
                        <option value="nonaktif" {{ $status === 'nonaktif' ? 'selected' : '' }}>Nonaktif Saja</option>
                    </select>
                </div>

                <div class="lg:col-span-2">
                    <label for="role" class="block text-sm font-semibold mb-1 text-gray-700">Peran</label>
                    <select
                        name="role"
                        id="role"
                        class="w-full rounded-lg border border-gray-300 px-3 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-amber-500 focus:border-amber-500"
                    >
                        <option value="semua" {{ $role === 'semua' || !$role ? 'selected' : '' }}>Semua Peran</option>
                        <option value="guru" {{ $role === 'guru' ? 'selected' : '' }}>Guru</option>
                        <option value="supervisor" {{ $role === 'supervisor' ? 'selected' : '' }}>Supervisor</option>
                        <option value="admin" {{ $role === 'admin' ? 'selected' : '' }}>Admin</option>
                    </select>
                </div>

                <div class="lg:col-span-2 flex gap-2">
                    <x-button type="submit" variant="primary" class="w-full justify-center">
                        Cari
                    </x-button>
                    @if($cari || ($status && $status !== 'semua') || ($role && $role !== 'semua'))
                        <x-button :href="route('admin.guru.index', ['kode' => $sekolah->kode])" variant="outline" class="shrink-0" title="Reset Filter">
                            Reset
                        </x-button>
                    @endif
                </div>
            </form>
        </x-card>

        {{-- Tabel Pengguna --}}
        <x-card class="overflow-hidden">
            <div class="overflow-x-auto">
                <table class="w-full text-left text-sm text-gray-700">
                    <thead class="text-xs uppercase font-semibold text-gray-700 border-b" style="background-color: var(--color-navy-50);">
                        <tr>
                            <th class="px-5 py-4">Pengguna</th>
                            <th class="px-5 py-4">Username</th>
                            <th class="px-5 py-4">Peran (Role)</th>
                            <th class="px-5 py-4">Status</th>
                            <th class="px-5 py-4 text-right">Aksi</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-200">
                        @forelse($pengguna as $p)
                            @php
                                $isKepalaSekolah = ($sekolah->kepala_sekolah_id === $p->id);
                                $isSupervisor = $p->hasRole('supervisor');
                                $isGuru = $p->hasRole('guru');
                                $isAdmin = $p->hasRole('admin');
                            @endphp
                            <tr class="hover:bg-amber-50/20 transition-colors {{ !$p->aktif ? 'bg-gray-50/80 opacity-75' : '' }}">
                                {{-- Nama & Kontak --}}
                                <td class="px-5 py-4">
                                    <div class="font-bold text-gray-900 flex items-center gap-1.5">
                                        <span>{{ $p->nama }}</span>
                                        @if($isKepalaSekolah)
                                            <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-semibold bg-amber-100 text-amber-900 border border-amber-300">
                                                Kepala Sekolah
                                            </span>
                                        @endif
                                    </div>
                                    <div class="text-xs text-gray-500 space-y-0.5 mt-0.5">
                                        @if($p->nip) <div>NIP: {{ $p->nip }}</div> @endif
                                        @if($p->nuptk) <div>NUPTK: {{ $p->nuptk }}</div> @endif
                                        @if($p->email) <div>Email: {{ $p->email }}</div> @endif
                                    </div>
                                </td>

                                {{-- Username --}}
                                <td class="px-5 py-4 font-mono text-sm text-gray-800">
                                    {{ $p->username }}
                                </td>

                                {{-- Peran --}}
                                <td class="px-5 py-4">
                                    <div class="flex flex-wrap gap-1.5">
                                        @if($isAdmin)
                                            <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-semibold bg-blue-100 text-blue-900">
                                                Admin
                                            </span>
                                        @endif
                                        @if($isSupervisor)
                                            <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-semibold bg-orange-100 text-orange-900">
                                                Supervisor
                                            </span>
                                        @endif
                                        @if($isGuru)
                                            <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-semibold bg-green-100 text-green-900">
                                                Guru
                                            </span>
                                        @endif
                                        @if(!$isAdmin && !$isSupervisor && !$isGuru)
                                            <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-semibold bg-gray-200 text-gray-700">
                                                Tanpa Peran
                                            </span>
                                        @endif
                                    </div>
                                </td>

                                {{-- Status --}}
                                <td class="px-5 py-4">
                                    @if($p->aktif)
                                        <span class="inline-flex items-center px-2.5 py-1 rounded-full text-xs font-semibold bg-green-100 text-green-800">
                                            <span class="w-1.5 h-1.5 rounded-full bg-green-600 mr-1.5"></span>
                                            Aktif
                                        </span>
                                    @else
                                        <span class="inline-flex items-center px-2.5 py-1 rounded-full text-xs font-semibold bg-gray-100 text-gray-700">
                                            <span class="w-1.5 h-1.5 rounded-full bg-gray-500 mr-1.5"></span>
                                            Nonaktif
                                        </span>
                                    @endif
                                </td>

                                {{-- Aksi --}}
                                <td class="px-5 py-4 text-right">
                                    <div class="flex flex-wrap items-center justify-end gap-1.5">
                                        {{-- Edit Profil --}}
                                        <button
                                            type="button"
                                            @click="bukaEdit({{ json_encode($p) }})"
                                            class="px-2.5 py-1 text-xs font-semibold rounded bg-amber-100 text-amber-900 hover:bg-amber-200 transition-colors"
                                            title="Ubah Profil Pengguna"
                                        >
                                            Ubah
                                        </button>

                                        {{-- Toggle Supervisor --}}
                                        <form
                                            id="form-spv-{{ $p->id }}"
                                            method="POST"
                                            action="{{ route('admin.guru.toggle-supervisor', ['kode' => $sekolah->kode, 'guru' => $p]) }}"
                                            class="hidden"
                                        >
                                            @csrf
                                            @method('PATCH')
                                        </form>
                                        <button
                                            type="button"
                                            @click="bukaKonfirm(
                                                'form-spv-{{ $p->id }}',
                                                '{{ $isSupervisor ? 'Cabut peran Supervisor dari ' . addslashes($p->nama) . '? Peran Guru tetap dipertahankan.' : 'Jadikan ' . addslashes($p->nama) . ' sebagai Supervisor (penilai)? Peran Guru tetap dipertahankan.' }}',
                                                '{{ $isSupervisor ? 'Ya, Cabut Supervisor' : 'Ya, Jadikan Supervisor' }}'
                                            )"
                                            class="px-2.5 py-1 text-xs font-semibold rounded transition-colors {{ $isSupervisor ? 'bg-orange-100 text-orange-900 hover:bg-orange-200' : 'border border-gray-300 text-gray-700 hover:bg-gray-100' }}"
                                            title="Toggle Peran Supervisor"
                                        >
                                            {{ $isSupervisor ? 'Hapus SPV' : '+ SPV' }}
                                        </button>

                                        {{-- Toggle Guru (Disupervisi) --}}
                                        <form
                                            id="form-guru-{{ $p->id }}"
                                            method="POST"
                                            action="{{ route('admin.guru.toggle-guru', ['kode' => $sekolah->kode, 'guru' => $p]) }}"
                                            class="hidden"
                                        >
                                            @csrf
                                            @method('PATCH')
                                        </form>
                                        <button
                                            type="button"
                                            @click="bukaKonfirm(
                                                'form-guru-{{ $p->id }}',
                                                '{{ $isGuru ? 'Cabut peran Guru (tidak akan disupervisi) dari ' . addslashes($p->nama) . '?' : 'Berikan peran Guru (akan disupervisi) kepada ' . addslashes($p->nama) . '?' }}',
                                                '{{ $isGuru ? 'Ya, Cabut Peran Guru' : 'Ya, Berikan Peran Guru' }}'
                                            )"
                                            class="px-2.5 py-1 text-xs font-semibold rounded transition-colors {{ $isGuru ? 'bg-green-100 text-green-900 hover:bg-green-200' : 'border border-gray-300 text-gray-700 hover:bg-gray-100' }}"
                                            title="Toggle Peran Guru (Disupervisi)"
                                        >
                                            {{ $isGuru ? 'Disupervisi' : '+ Guru' }}
                                        </button>

                                        {{-- Atur Ulang Password --}}
                                        <button
                                            type="button"
                                            @click="bukaReset({{ json_encode($p) }})"
                                            class="px-2.5 py-1 text-xs font-semibold rounded border border-blue-300 text-blue-900 hover:bg-blue-50 transition-colors"
                                            title="Atur Ulang Password Pengguna"
                                        >
                                            Reset Sandi
                                        </button>

                                        {{-- Toggle Status Aktif/Nonaktif --}}
                                        <form
                                            id="form-status-{{ $p->id }}"
                                            method="POST"
                                            action="{{ route('admin.guru.toggle-status', ['kode' => $sekolah->kode, 'guru' => $p]) }}"
                                            class="hidden"
                                        >
                                            @csrf
                                            @method('PATCH')
                                        </form>
                                        <button
                                            type="button"
                                            @click="bukaKonfirm(
                                                'form-status-{{ $p->id }}',
                                                '{{ $p->aktif ? 'Nonaktifkan akun ' . addslashes($p->nama) . '? Pengguna tidak akan bisa masuk ke sistem.' : 'Aktifkan kembali akun ' . addslashes($p->nama) . '?' }}',
                                                '{{ $p->aktif ? 'Ya, Nonaktifkan' : 'Ya, Aktifkan' }}'
                                            )"
                                            class="px-2.5 py-1 text-xs font-semibold rounded transition-colors {{ $p->aktif ? 'bg-red-100 text-red-900 hover:bg-red-200' : 'bg-green-100 text-green-900 hover:bg-green-200' }}"
                                            title="{{ $p->aktif ? 'Nonaktifkan Akun' : 'Aktifkan Akun' }}"
                                        >
                                            {{ $p->aktif ? 'Nonaktifkan' : 'Aktifkan' }}
                                        </button>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="5" class="px-5 py-10 text-center text-gray-500">
                                    <div class="max-w-md mx-auto">
                                        <p class="font-bold text-base text-gray-700 mb-1">Belum Ada Data Pengguna</p>
                                        <p class="text-sm">Tidak ditemukan data pengguna sesuai kriteria pencarian Anda. Gunakan tombol Tambah Pengguna atau Impor Excel di atas.</p>
                                    </div>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            @if($pengguna->hasPages())
                <div class="px-5 py-4 border-t border-gray-200">
                    {{ $pengguna->links() }}
                </div>
            @endif
        </x-card>

        {{-- ========================================================================= --}}
        {{-- MODAL KONFIRMASI TERPUSAT (SPV, Guru, Status) --}}
        {{-- ========================================================================= --}}
        <div
            x-show="modalKonfirm"
            class="fixed inset-0 z-50 overflow-y-auto bg-black/50 flex items-center justify-center p-4"
            x-transition:enter="transition ease-out duration-200"
            x-transition:enter-start="opacity-0"
            x-transition:enter-end="opacity-100"
            x-transition:leave="transition ease-in duration-150"
            x-transition:leave-start="opacity-100"
            x-transition:leave-end="opacity-0"
            style="display: none;"
            @keydown.escape.window="modalKonfirm = false"
        >
            <div
                @click.outside="modalKonfirm = false"
                class="bg-white rounded-2xl shadow-xl w-full max-w-md overflow-hidden border border-gray-200"
                x-transition:enter="transition ease-out duration-200"
                x-transition:enter-start="opacity-0 scale-95"
                x-transition:enter-end="opacity-100 scale-100"
                x-transition:leave="transition ease-in duration-150"
                x-transition:leave-start="opacity-100 scale-100"
                x-transition:leave-end="opacity-0 scale-95"
            >
                <div class="px-6 py-4 border-b border-gray-200 flex items-center justify-between" style="background-color: var(--color-navy-50);">
                    <h3 class="font-bold text-lg" style="color: var(--color-navy-900);">Konfirmasi Tindakan</h3>
                    <button type="button" @click="modalKonfirm = false" class="text-gray-400 hover:text-gray-600 text-2xl font-bold leading-none">&times;</button>
                </div>
                <div class="p-6">
                    <div class="flex items-start gap-3 mb-5">
                        <div class="flex-shrink-0 w-10 h-10 rounded-full flex items-center justify-center" style="background-color: #E6ECF7;">
                            <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 20 20" fill="currentColor" style="color: #253C6D;" aria-hidden="true">
                                <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7-4a1 1 0 11-2 0 1 1 0 012 0zM9 9a1 1 0 000 2v3a1 1 0 001 1h1a1 1 0 100-2v-3a1 1 0 00-1-1H9z" clip-rule="evenodd"/>
                            </svg>
                        </div>
                        <p class="text-sm leading-relaxed mt-2" style="color: var(--color-ink);" x-text="konfirmPesan"></p>
                    </div>
                    <div class="flex justify-end gap-3">
                        <button
                            type="button"
                            @click="modalKonfirm = false"
                            class="px-4 py-2 text-sm font-semibold rounded-lg border border-gray-300 text-gray-700 hover:bg-gray-100 transition-colors"
                        >
                            Batal
                        </button>
                        <button
                            type="button"
                            @click="konfirmSetuju()"
                            class="px-4 py-2 text-sm font-semibold rounded-lg text-white transition-colors"
                            style="background-color: var(--color-navy-900); hover:background-color: var(--color-navy-700);"
                            x-text="konfirmLabel"
                        ></button>
                    </div>
                </div>
            </div>
        </div>

        {{-- ========================================================================= --}}
        {{-- MODAL TAMBAH PENGGUNA --}}
        {{-- ========================================================================= --}}
        <div
            x-show="modalTambah"
            class="fixed inset-0 z-50 overflow-y-auto bg-black/50 flex items-center justify-center p-4"
            x-transition.opacity
            style="display: none;"
        >
            <div
                @click.outside="modalTambah = false"
                class="bg-white rounded-2xl shadow-xl w-full max-w-lg overflow-hidden border border-gray-200"
            >
                <div class="px-6 py-4 border-b border-gray-200 flex items-center justify-between" style="background-color: var(--color-navy-50);">
                    <h3 class="font-bold text-lg text-gray-900">Tambah Pengguna Baru</h3>
                    <button type="button" @click="modalTambah = false" class="text-gray-400 hover:text-gray-600 text-2xl font-bold">&times;</button>
                </div>

                <form method="POST" action="{{ route('admin.guru.store', ['kode' => $sekolah->kode]) }}" class="p-6 space-y-4">
                    @csrf
                    <div>
                        <label for="tambah_nama" class="block text-sm font-semibold text-gray-700 mb-1">Nama Lengkap *</label>
                        <input type="text" name="nama" id="tambah_nama" required class="w-full rounded-lg border border-gray-300 px-3.5 py-2 text-sm focus:ring-2 focus:ring-amber-500">
                    </div>

                    <div>
                        <label for="tambah_username" class="block text-sm font-semibold text-gray-700 mb-1">Username (Unik) *</label>
                        <input type="text" name="username" id="tambah_username" required class="w-full rounded-lg border border-gray-300 px-3.5 py-2 text-sm font-mono focus:ring-2 focus:ring-amber-500" placeholder="contoh: budi.santoso">
                    </div>

                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                        <div>
                            <label for="tambah_nip" class="block text-sm font-semibold text-gray-700 mb-1">NIP (Opsional)</label>
                            <input type="text" name="nip" id="tambah_nip" class="w-full rounded-lg border border-gray-300 px-3.5 py-2 text-sm focus:ring-2 focus:ring-amber-500">
                        </div>
                        <div>
                            <label for="tambah_nuptk" class="block text-sm font-semibold text-gray-700 mb-1">NUPTK (Opsional)</label>
                            <input type="text" name="nuptk" id="tambah_nuptk" class="w-full rounded-lg border border-gray-300 px-3.5 py-2 text-sm focus:ring-2 focus:ring-amber-500">
                        </div>
                    </div>

                    <div>
                        <label for="tambah_email" class="block text-sm font-semibold text-gray-700 mb-1">Email (Opsional)</label>
                        <input type="email" name="email" id="tambah_email" class="w-full rounded-lg border border-gray-300 px-3.5 py-2 text-sm focus:ring-2 focus:ring-amber-500">
                    </div>

                    <div>
                        <label for="tambah_password" class="block text-sm font-semibold text-gray-700 mb-1">Password Awal *</label>
                        <input type="password" name="password" id="tambah_password" required minlength="8" class="w-full rounded-lg border border-gray-300 px-3.5 py-2 text-sm focus:ring-2 focus:ring-amber-500" placeholder="Minimal 8 karakter">
                        <p class="text-xs text-gray-500 mt-1">Pengguna otomatis wajib mengganti password saat pertama kali masuk.</p>
                    </div>

                    <div class="pt-4 border-t border-gray-200 flex justify-end gap-3">
                        <x-button variant="outline" type="button" @click="modalTambah = false">Batal</x-button>
                        <x-button variant="tambah" type="submit">Simpan Pengguna</x-button>
                    </div>
                </form>
            </div>
        </div>

        {{-- ========================================================================= --}}
        {{-- MODAL UBAH PENGGUNA --}}
        {{-- ========================================================================= --}}
        <div
            x-show="modalEdit"
            class="fixed inset-0 z-50 overflow-y-auto bg-black/50 flex items-center justify-center p-4"
            x-transition.opacity
            style="display: none;"
        >
            <div
                @click.outside="modalEdit = false"
                class="bg-white rounded-2xl shadow-xl w-full max-w-lg overflow-hidden border border-gray-200"
            >
                <div class="px-6 py-4 border-b border-gray-200 flex items-center justify-between" style="background-color: var(--color-navy-50);">
                    <h3 class="font-bold text-lg text-gray-900">Ubah Data Pengguna</h3>
                    <button type="button" @click="modalEdit = false" class="text-gray-400 hover:text-gray-600 text-2xl font-bold">&times;</button>
                </div>

                <form
                    method="POST"
                    :action="`{{ url('/s/' . $sekolah->kode . '/admin/guru') }}/${editUser.id}`"
                    class="p-6 space-y-4"
                >
                    @csrf
                    @method('PUT')

                    <div>
                        <label for="edit_nama" class="block text-sm font-semibold text-gray-700 mb-1">Nama Lengkap *</label>
                        <input type="text" name="nama" id="edit_nama" x-model="editUser.nama" required class="w-full rounded-lg border border-gray-300 px-3.5 py-2 text-sm focus:ring-2 focus:ring-amber-500">
                    </div>

                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                        <div>
                            <label for="edit_nip" class="block text-sm font-semibold text-gray-700 mb-1">NIP (Opsional)</label>
                            <input type="text" name="nip" id="edit_nip" x-model="editUser.nip" class="w-full rounded-lg border border-gray-300 px-3.5 py-2 text-sm focus:ring-2 focus:ring-amber-500">
                        </div>
                        <div>
                            <label for="edit_nuptk" class="block text-sm font-semibold text-gray-700 mb-1">NUPTK (Opsional)</label>
                            <input type="text" name="nuptk" id="edit_nuptk" x-model="editUser.nuptk" class="w-full rounded-lg border border-gray-300 px-3.5 py-2 text-sm focus:ring-2 focus:ring-amber-500">
                        </div>
                    </div>

                    <div>
                        <label for="edit_email" class="block text-sm font-semibold text-gray-700 mb-1">Email (Opsional)</label>
                        <input type="email" name="email" id="edit_email" x-model="editUser.email" class="w-full rounded-lg border border-gray-300 px-3.5 py-2 text-sm focus:ring-2 focus:ring-amber-500">
                    </div>

                    <div class="pt-4 border-t border-gray-200 flex justify-end gap-3">
                        <x-button variant="outline" type="button" @click="modalEdit = false">Batal</x-button>
                        <x-button variant="edit" type="submit">Simpan Perubahan</x-button>
                    </div>
                </form>
            </div>
        </div>

        {{-- ========================================================================= --}}
        {{-- MODAL RESET PASSWORD --}}
        {{-- ========================================================================= --}}
        <div
            x-show="modalReset"
            class="fixed inset-0 z-50 overflow-y-auto bg-black/50 flex items-center justify-center p-4"
            x-transition.opacity
            style="display: none;"
        >
            <div
                @click.outside="modalReset = false"
                class="bg-white rounded-2xl shadow-xl w-full max-w-md overflow-hidden border border-gray-200"
            >
                <div class="px-6 py-4 border-b border-gray-200 flex items-center justify-between" style="background-color: var(--color-navy-50);">
                    <h3 class="font-bold text-lg text-gray-900">Atur Ulang Password</h3>
                    <button type="button" @click="modalReset = false" class="text-gray-400 hover:text-gray-600 text-2xl font-bold">&times;</button>
                </div>

                <form
                    method="POST"
                    :action="`{{ url('/s/' . $sekolah->kode . '/admin/guru') }}/${resetUser.id}/reset-password`"
                    class="p-6 space-y-4"
                >
                    @csrf
                    @method('PATCH')

                    <div>
                        <p class="text-sm text-gray-600 mb-3">
                            Mengatur ulang password untuk: <strong class="text-gray-900" x-text="resetUser.nama"></strong>. Pengguna akan diwajibkan mengganti password baru saat masuk kembali.
                        </p>
                    </div>

                    <div>
                        <label for="reset_password" class="block text-sm font-semibold text-gray-700 mb-1">Password Baru *</label>
                        <input type="password" name="password" id="reset_password" required minlength="8" class="w-full rounded-lg border border-gray-300 px-3.5 py-2 text-sm focus:ring-2 focus:ring-amber-500" placeholder="Minimal 8 karakter">
                    </div>

                    <div>
                        <label for="reset_password_confirmation" class="block text-sm font-semibold text-gray-700 mb-1">Konfirmasi Password Baru *</label>
                        <input type="password" name="password_confirmation" id="reset_password_confirmation" required minlength="8" class="w-full rounded-lg border border-gray-300 px-3.5 py-2 text-sm focus:ring-2 focus:ring-amber-500" placeholder="Ketik ulang password baru">
                    </div>

                    <div class="pt-4 border-t border-gray-200 flex justify-end gap-3">
                        <x-button variant="outline" type="button" @click="modalReset = false">Batal</x-button>
                        <x-button variant="primary" type="submit">Atur Ulang Password</x-button>
                    </div>
                </form>
            </div>
        </div>

        {{-- ========================================================================= --}}
        {{-- MODAL TETAPKAN KEPALA SEKOLAH --}}
        {{-- ========================================================================= --}}
        <div
            x-show="modalKepalaSekolah"
            class="fixed inset-0 z-50 overflow-y-auto bg-black/50 flex items-center justify-center p-4"
            x-transition.opacity
            style="display: none;"
        >
            <div
                @click.outside="modalKepalaSekolah = false"
                class="bg-white rounded-2xl shadow-xl w-full max-w-lg overflow-hidden border border-gray-200"
            >
                <div class="px-6 py-4 border-b border-gray-200 flex items-center justify-between" style="background-color: var(--color-navy-50);">
                    <h3 class="font-bold text-lg text-gray-900">Tetapkan Kepala Sekolah</h3>
                    <button type="button" @click="modalKepalaSekolah = false" class="text-gray-400 hover:text-gray-600 text-2xl font-bold">&times;</button>
                </div>

                <form method="POST" action="{{ route('admin.sekolah.kepala-sekolah', ['kode' => $sekolah->kode]) }}" class="p-6 space-y-4">
                    @csrf
                    @method('PATCH')

                    <div>
                        <p class="text-sm text-gray-600 leading-relaxed mb-4">
                            Sesuai aturan supervisi, Kepala Sekolah bertindak sebagai <strong>Supervisor</strong>. Hanya pengguna yang telah diberikan peran <strong>Supervisor</strong> yang dapat dipilih sebagai Kepala Sekolah.
                        </p>

                        <label for="kepala_sekolah_id" class="block text-sm font-semibold text-gray-700 mb-1">Pilih Kepala Sekolah</label>
                        <select
                            name="kepala_sekolah_id"
                            id="kepala_sekolah_id"
                            class="w-full rounded-lg border border-gray-300 px-3 py-2.5 text-sm focus:ring-2 focus:ring-amber-500"
                        >
                            <option value="">-- Belum Ditentukan (Kosongkan) --</option>
                            @foreach($supervisorList as $spv)
                                <option value="{{ $spv->id }}" {{ $sekolah->kepala_sekolah_id === $spv->id ? 'selected' : '' }}>
                                    {{ $spv->nama }} {{ $spv->nip ? '(NIP: ' . $spv->nip . ')' : '' }}
                                </option>
                            @endforeach
                        </select>
                        @if($supervisorList->isEmpty())
                            <p class="text-xs text-orange-700 mt-2">
                                Belum ada pengguna dengan peran Supervisor. Silakan berikan peran Supervisor kepada calon Kepala Sekolah pada daftar di bawah terlebih dahulu.
                            </p>
                        @endif
                    </div>

                    <div class="pt-4 border-t border-gray-200 flex justify-end gap-3">
                        <x-button variant="outline" type="button" @click="modalKepalaSekolah = false">Batal</x-button>
                        <x-button variant="primary" type="submit">Simpan Penetapan</x-button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</x-app-layout>
