<x-layouts.super-admin :title="'Daftar Sekolah'">

    <x-page-header title="Daftar Sekolah" subtitle="Kelola semua sekolah yang terdaftar dalam sistem.">
        <x-slot:actions>
            <x-button variant="tambah" href="{{ route('super-admin.sekolah.create') }}">
                <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true">
                    <path fill-rule="evenodd" d="M10 3a1 1 0 011 1v5h5a1 1 0 110 2h-5v5a1 1 0 11-2 0v-5H4a1 1 0 110-2h5V4a1 1 0 011-1z" clip-rule="evenodd"/>
                </svg>
                Tambah Sekolah
            </x-button>
        </x-slot:actions>
    </x-page-header>

    {{-- Form Pencarian --}}
    <div class="mb-6">
        <form method="GET" action="{{ route('super-admin.sekolah.index') }}" role="search">
            <div class="flex gap-3 max-w-lg">
                <div class="flex-1">
                    <label for="cari" class="sr-only">Cari sekolah</label>
                    <div class="relative">
                        <span class="absolute inset-y-0 left-3 flex items-center pointer-events-none" style="color: var(--color-muted);">
                            <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true">
                                <path fill-rule="evenodd" d="M8 4a4 4 0 100 8 4 4 0 000-8zM2 8a6 6 0 1110.89 3.476l4.817 4.817a1 1 0 01-1.414 1.414l-4.816-4.816A6 6 0 012 8z" clip-rule="evenodd"/>
                            </svg>
                        </span>
                        <input
                            type="search"
                            id="cari"
                            name="cari"
                            value="{{ $cari }}"
                            placeholder="Cari nama, kode, atau NPSN sekolah…"
                            class="form-input pl-10"
                            autocomplete="off"
                        >
                    </div>
                </div>
                <x-button type="submit" variant="primary">Cari</x-button>
                @if($cari)
                    <x-button href="{{ route('super-admin.sekolah.index') }}" variant="outline">Hapus Filter</x-button>
                @endif
            </div>
        </form>
    </div>

    {{-- Tabel Sekolah --}}
    <x-card>
        @if($sekolah->isEmpty())
            <x-empty-state
                title="Belum ada sekolah"
                description="{{ $cari ? 'Tidak ada sekolah yang cocok dengan pencarian Anda.' : 'Mulai dengan menambahkan sekolah pertama.' }}"
            >
                @if(!$cari)
                    <x-button variant="tambah" href="{{ route('super-admin.sekolah.create') }}">
                        Tambah Sekolah Pertama
                    </x-button>
                @endif
            </x-empty-state>
        @else
            {{-- Desktop table --}}
            <div class="hidden md:block overflow-x-auto">
                <table class="data-table" aria-label="Daftar sekolah">
                    <thead>
                        <tr>
                            <th scope="col">Nama Sekolah</th>
                            <th scope="col">Kode</th>
                            <th scope="col">NPSN</th>
                            <th scope="col" class="text-center">Guru</th>
                            <th scope="col" class="text-center">Admin</th>
                            <th scope="col" class="text-center">Status</th>
                            <th scope="col"><span class="sr-only">Aksi</span></th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($sekolah as $s)
                            <tr>
                                <td>
                                    <div class="font-semibold" style="color: var(--color-navy-900);">
                                        {{ $s->nama }}
                                    </div>
                                    @if($s->alamat)
                                        <div class="text-sm mt-0.5 truncate max-w-xs" style="color: var(--color-muted);">
                                            {{ $s->alamat }}
                                        </div>
                                    @endif
                                </td>
                                <td>
                                    <code class="text-sm px-2 py-0.5 rounded" style="background: var(--color-navy-50); color: var(--color-navy-900);">
                                        {{ $s->kode }}
                                    </code>
                                </td>
                                <td>{{ $s->npsn ?? '—' }}</td>
                                <td class="text-center font-semibold">{{ $s->jumlah_guru }}</td>
                                <td class="text-center font-semibold">{{ $s->jumlah_admin }}</td>
                                <td class="text-center">
                                    <x-badge-status :status="$s->status === 'aktif' ? 'final' : 'belum'">
                                        {{ $s->status === 'aktif' ? 'Aktif' : 'Nonaktif' }}
                                    </x-badge-status>
                                </td>
                                <td>
                                    <div class="flex items-center justify-end gap-2">
                                        <x-button
                                            variant="outline"
                                            size="sm"
                                            href="{{ route('super-admin.sekolah.show', $s) }}"
                                        >
                                            Detail
                                        </x-button>
                                        <x-button
                                            variant="edit"
                                            size="sm"
                                            href="{{ route('super-admin.sekolah.edit', $s) }}"
                                        >
                                            Ubah
                                        </x-button>
                                    </div>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

            {{-- Mobile cards --}}
            <div class="md:hidden space-y-3">
                @foreach($sekolah as $s)
                    <div class="rounded-xl p-4 border" style="border-color: var(--color-border); background: var(--color-cream);">
                        <div class="flex items-start justify-between gap-2 mb-2">
                            <div>
                                <div class="font-semibold" style="color: var(--color-navy-900);">{{ $s->nama }}</div>
                                <code class="text-xs px-1.5 py-0.5 rounded mt-1 inline-block" style="background: var(--color-navy-50); color: var(--color-navy-900);">
                                    {{ $s->kode }}
                                </code>
                            </div>
                            <x-badge-status :status="$s->status === 'aktif' ? 'final' : 'belum'">
                                {{ $s->status === 'aktif' ? 'Aktif' : 'Nonaktif' }}
                            </x-badge-status>
                        </div>
                        <div class="flex items-center gap-4 text-sm mb-3" style="color: var(--color-muted);">
                            <span>{{ $s->jumlah_guru }} guru</span>
                            <span>{{ $s->jumlah_admin }} admin</span>
                            @if($s->npsn)<span>NPSN: {{ $s->npsn }}</span>@endif
                        </div>
                        <div class="flex gap-2">
                            <x-button variant="outline" size="sm" href="{{ route('super-admin.sekolah.show', $s) }}">Detail</x-button>
                            <x-button variant="edit" size="sm" href="{{ route('super-admin.sekolah.edit', $s) }}">Ubah</x-button>
                        </div>
                    </div>
                @endforeach
            </div>

            {{-- Paginasi --}}
            @if($sekolah->hasPages())
                <div class="mt-4 pt-4" style="border-top: 1px solid var(--color-border);">
                    {{ $sekolah->links() }}
                </div>
            @endif
        @endif
    </x-card>

</x-layouts.super-admin>
