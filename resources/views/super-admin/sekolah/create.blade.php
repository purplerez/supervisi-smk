<x-layouts.super-admin :title="'Tambah Sekolah'">

    <x-page-header
        title="Tambah Sekolah Baru"
        subtitle="Isi data sekolah. Kode sekolah tidak dapat diubah setelah disimpan."
        :back="route('super-admin.sekolah.index')"
        backLabel="Kembali ke Daftar Sekolah"
    />

    <x-card class="max-w-2xl">
        <form
            method="POST"
            action="{{ route('super-admin.sekolah.store') }}"
            enctype="multipart/form-data"
            id="form-tambah-sekolah"
            novalidate
        >
            @csrf

            <div class="space-y-5">
                {{-- Nama Sekolah --}}
                <x-input
                    name="nama"
                    label="Nama Sekolah"
                    :value="old('nama')"
                    required
                    placeholder="cth. SMP Negeri 1 Kota Maju"
                    autocomplete="off"
                />

                {{-- Kode Sekolah --}}
                <div>
                    <x-input
                        name="kode"
                        label="Kode Sekolah (slug URL)"
                        :value="old('kode')"
                        required
                        placeholder="cth. smpn1-kota-maju"
                        autocomplete="off"
                        :hint="'Hanya huruf kecil, angka, strip, dan garis bawah. Tidak dapat diubah setelah disimpan.'"
                    />
                    <p class="mt-1 text-sm" style="color: var(--color-muted);">
                        URL login:
                        <code class="px-1 rounded text-xs" style="background: var(--color-navy-50);">
                            {{ url('/s/') }}/<span id="preview-kode" class="font-semibold">{{ old('kode', 'kode-sekolah') }}</span>/login
                        </code>
                    </p>
                </div>

                {{-- NPSN --}}
                <x-input
                    name="npsn"
                    label="NPSN (opsional)"
                    :value="old('npsn')"
                    placeholder="Nomor Pokok Sekolah Nasional"
                    inputmode="numeric"
                />

                {{-- Alamat --}}
                <x-textarea
                    name="alamat"
                    label="Alamat Sekolah (opsional)"
                    :value="old('alamat')"
                    rows="3"
                    placeholder="Jl. Contoh No. 1, Kelurahan, Kecamatan, Kota"
                />

                {{-- Logo --}}
                <div>
                    <label class="form-label" for="logo">Logo Sekolah (opsional)</label>
                    <input
                        type="file"
                        id="logo"
                        name="logo"
                        accept="image/jpeg,image/png,image/webp"
                        class="block w-full text-sm file:mr-4 file:py-2 file:px-4 file:rounded-lg file:border-0
                               file:font-semibold file:text-white file:cursor-pointer
                               focus:outline-none focus:ring-2"
                        style="
                            color: var(--color-ink);
                            file:background-color: var(--color-navy-900);
                            focus:ring-color: var(--color-orange-500);
                        "
                    >
                    @error('logo')
                        <p class="form-error" role="alert">{{ $message }}</p>
                    @enderror
                    <p class="mt-1 text-sm" style="color: var(--color-muted);">Format: JPG, PNG, atau WebP. Maksimal 2 MB.</p>
                </div>

                {{-- Status --}}
                <x-select
                    name="status"
                    label="Status"
                    :selected="old('status', 'aktif')"
                    required
                    :options="['aktif' => 'Aktif', 'nonaktif' => 'Nonaktif']"
                />
            </div>

            <div class="mt-8 flex flex-wrap gap-3 pt-6" style="border-top: 1px solid var(--color-border);">
                <x-button type="submit" variant="tambah" id="btn-simpan-sekolah">
                    <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true">
                        <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"/>
                    </svg>
                    Simpan Sekolah
                </x-button>
                <x-button variant="outline" href="{{ route('super-admin.sekolah.index') }}">
                    Batal
                </x-button>
            </div>
        </form>
    </x-card>

    @push('scripts')
    <script>
        // Preview kode sekolah secara real-time
        const inputKode = document.querySelector('input[name="kode"]');
        const previewKode = document.getElementById('preview-kode');
        if (inputKode && previewKode) {
            inputKode.addEventListener('input', () => {
                previewKode.textContent = inputKode.value || 'kode-sekolah';
            });
        }
    </script>
    @endpush

</x-layouts.super-admin>
