<x-app-layout :namaSekolah="$sekolah->nama" title="Guru Belum Punya Penilai">

    <x-page-header
        title="Guru Belum Memiliki Penilai"
        subtitle="Daftar guru aktif yang belum mengajukan atau memilih supervisor penilai pada periode supervisi terpilih."
    >
        <x-slot:actions>
            <x-button variant="outline" href="{{ route('admin.penugasan.index', ['kode' => $sekolah->kode, 'periode_id' => $periode?->id]) }}">
                &larr; Kembali ke Penugasan
            </x-button>
        </x-slot:actions>
    </x-page-header>

    {{-- Filter Periode --}}
    <x-card class="mb-6 p-4">
        <form method="GET" action="{{ route('admin.penugasan.belum-dinilai', ['kode' => $sekolah->kode]) }}" class="flex flex-col sm:flex-row items-center gap-3">
            <label for="periode_id" class="text-sm font-bold whitespace-nowrap" style="color: var(--color-navy-900);">
                Pilih Periode:
            </label>
            <select name="periode_id" id="periode_id" class="form-input text-sm w-full sm:w-80" onchange="this.form.submit()">
                @forelse($semuaPeriode as $p)
                    <option value="{{ $p->id }}" {{ $periode && $periode->id === $p->id ? 'selected' : '' }}>
                        {{ $p->nama }} ({{ $p->tahun_ajaran }}) &mdash; {{ ucfirst($p->status) }}
                    </option>
                @empty
                    <option value="">Belum ada periode supervisi</option>
                @endforelse
            </select>
        </form>
    </x-card>

    @if(!$periode)
        <x-card class="p-8 text-center">
            <x-empty-state
                title="Periode Belum Dipilih"
                description="Silakan buat atau pilih periode supervisi terlebih dahulu."
            />
        </x-card>
    @else
        <x-card class="p-0 overflow-hidden">
            @if($guruBelum->isEmpty())
                <div class="p-12 text-center">
                    <svg xmlns="http://www.w3.org/2000/svg" width="48" height="48" viewBox="0 0 20 20" fill="currentColor" class="mx-auto mb-3 text-green-600">
                        <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd" />
                    </svg>
                    <h3 class="text-lg font-bold" style="color: var(--color-navy-900);">Seluruh Guru Sudah Memiliki Penilai</h3>
                    <p class="text-sm text-gray-500 mt-1">Tidak ada guru aktif yang belum memiliki supervisor penilai pada periode {{ $periode->nama }}.</p>
                </div>
            @else
                <div class="p-4 bg-orange-50 border-b flex items-center justify-between" style="border-color: var(--color-border);">
                    <div class="flex items-center gap-2 text-sm text-orange-900">
                        <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 20 20" fill="currentColor" class="text-orange-600">
                            <path fill-rule="evenodd" d="M8.257 3.099c.765-1.36 2.722-1.36 3.486 0l5.58 9.92c.75 1.334-.213 2.98-1.742 2.98H4.42c-1.53 0-2.493-1.646-1.743-2.98l5.58-9.92zM11 13a1 1 0 11-2 0 1 1 0 012 0zm-1-8a1 1 0 00-1 1v3a1 1 0 002 0V6a1 1 0 00-1-1z" clip-rule="evenodd" />
                        </svg>
                        <span>Terdapat <strong>{{ $guruBelum->count() }} guru</strong> yang belum mengajukan supervisi mandiri.</span>
                    </div>
                </div>

                <div class="overflow-x-auto">
                    <table class="w-full text-left text-sm" style="color: var(--color-ink);">
                        <thead style="background-color: var(--color-navy-50); color: var(--color-navy-900);">
                            <tr>
                                <th class="py-3.5 px-4 font-bold">No</th>
                                <th class="py-3.5 px-4 font-bold">Nama Guru</th>
                                <th class="py-3.5 px-4 font-bold">NIP / NUPTK</th>
                                <th class="py-3.5 px-4 font-bold">Username</th>
                                <th class="py-3.5 px-4 font-bold text-center">Status Pengajuan</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y" style="border-color: var(--color-border);">
                            @foreach($guruBelum as $index => $guru)
                                <tr class="hover:bg-gray-50/60 transition-colors">
                                    <td class="py-3.5 px-4 font-bold text-gray-500">{{ $index + 1 }}</td>
                                    <td class="py-3.5 px-4 font-bold" style="color: var(--color-navy-900);">
                                        {{ $guru->nama }}
                                    </td>
                                    <td class="py-3.5 px-4 text-xs text-gray-600">
                                        NIP: {{ $guru->nip ?: '-' }}<br>
                                        NUPTK: {{ $guru->nuptk ?: '-' }}
                                    </td>
                                    <td class="py-3.5 px-4 text-xs font-mono text-gray-600">
                                        {{ $guru->username }}
                                    </td>
                                    <td class="py-3.5 px-4 text-center">
                                        <span class="inline-flex items-center px-2.5 py-1 rounded-full text-xs font-semibold bg-gray-100 text-gray-700">
                                            Belum Mengajukan
                                        </span>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @endif
        </x-card>
    @endif

</x-app-layout>
