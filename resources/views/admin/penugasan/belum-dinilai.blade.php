<x-app-layout :namaSekolah="$sekolah->nama" title="Guru Belum Punya Penilai">

    <x-page-header
        title="Guru Belum Memiliki Penilai"
        subtitle="Daftar guru aktif yang belum dipetakan kepada supervisor penilai pada periode supervisi terpilih."
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
        <x-card class="p-0 overflow-hidden" x-data="{
            modalCepat: false,
            guruTerpilih: { id: '', nama: '' },
            bukaTugaskan(id, nama) {
                this.guruTerpilih = { id: id, nama: nama };
                this.modalCepat = true;
            }
        }">
            @if($guruBelum->isEmpty())
                <div class="p-12 text-center">
                    <svg xmlns="http://www.w3.org/2000/svg" width="48" height="48" viewBox="0 0 20 20" fill="currentColor" class="mx-auto mb-3 text-green-600">
                        <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd" />
                    </svg>
                    <h3 class="text-lg font-bold" style="color: var(--color-navy-900);">Seluruh Guru Sudah Ditugaskan</h3>
                    <p class="text-sm text-gray-500 mt-1">Tidak ada guru aktif yang belum memiliki supervisor penilai pada periode {{ $periode->nama }}.</p>
                </div>
            @else
                <div class="p-4 bg-orange-50 border-b flex items-center justify-between" style="border-color: var(--color-border);">
                    <div class="flex items-center gap-2 text-sm text-orange-900">
                        <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 20 20" fill="currentColor" class="text-orange-600">
                            <path fill-rule="evenodd" d="M8.257 3.099c.765-1.36 2.722-1.36 3.486 0l5.58 9.92c.75 1.334-.213 2.98-1.742 2.98H4.42c-1.53 0-2.493-1.646-1.743-2.98l5.58-9.92zM11 13a1 1 0 11-2 0 1 1 0 012 0zm-1-8a1 1 0 00-1 1v3a1 1 0 002 0V6a1 1 0 00-1-1z" clip-rule="evenodd" />
                        </svg>
                        <span>Terdapat <strong>{{ $guruBelum->count() }} guru</strong> yang belum memiliki penilai.</span>
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
                                <th class="py-3.5 px-4 font-bold text-right">Aksi</th>
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
                                    <td class="py-3.5 px-4 text-right">
                                        @if(!$periode->isDitutup())
                                            <button
                                                type="button"
                                                @click="bukaTugaskan({{ $guru->id }}, '{{ addslashes($guru->nama) }}')"
                                                class="px-3 py-1.5 text-xs font-bold rounded text-white transition-colors"
                                                style="background-color: var(--color-action-add);"
                                            >
                                                + Tugaskan
                                            </button>
                                        @else
                                            <span class="text-xs text-gray-400 italic">Periode Ditutup</span>
                                        @endif
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>

                {{-- MODAL CEPAT TUGASKAN --}}
                <div x-show="modalCepat" class="fixed inset-0 z-50 overflow-y-auto bg-black/40 flex items-center justify-center p-4" style="display: none;">
                    <div @click.outside="modalCepat = false" class="bg-white rounded-xl max-w-md w-full p-6 shadow-xl border" style="border-color: var(--color-border);">
                        <h3 class="text-lg font-bold mb-2" style="color: var(--color-navy-900);">Tugaskan Guru</h3>
                        <p class="text-xs text-gray-600 mb-4">
                            Pilih supervisor penilai untuk guru <strong x-text="guruTerpilih.nama"></strong>.
                        </p>

                        <form action="{{ route('admin.penugasan.store', ['kode' => $sekolah->kode]) }}" method="POST">
                            @csrf
                            <input type="hidden" name="periode_id" value="{{ $periode->id }}">
                            <input type="hidden" name="guru_ids[]" :value="guruTerpilih.id">

                            <div class="mb-4">
                                <label class="block text-xs font-bold mb-1" style="color: var(--color-navy-900);">Pilih Supervisor *</label>
                                <select name="penilai_id" required class="form-input text-sm w-full">
                                    <option value="">-- Pilih Supervisor --</option>
                                    @foreach($supervisors as $spv)
                                        <option value="{{ $spv->id }}">{{ $spv->nama }} (NIP: {{ $spv->nip ?: '-' }})</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="flex justify-end gap-2">
                                <button type="button" @click="modalCepat = false" class="px-3 py-1.5 text-xs rounded border border-gray-300">Batal</button>
                                <x-button type="submit" variant="tambah" class="text-xs py-1.5">Simpan Penugasan</x-button>
                            </div>
                        </form>
                    </div>
                </div>
            @endif
        </x-card>
    @endif

</x-app-layout>
