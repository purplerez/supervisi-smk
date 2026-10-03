<x-layouts.super-admin :title="'Ubah Sekolah: ' . $sekolah->nama">

    <x-page-header
        :title="'Ubah Data: ' . $sekolah->nama"
        subtitle="Perbarui data sekolah. Kode sekolah tidak dapat diubah."
        :back="route('super-admin.sekolah.show', $sekolah)"
        backLabel="Kembali ke Detail Sekolah"
    />

    <x-card class="max-w-2xl">
        <form
            method="POST"
            action="{{ route('super-admin.sekolah.update', $sekolah) }}"
            enctype="multipart/form-data"
            id="form-ubah-sekolah"
            novalidate
        >
            @csrf
            @method('PUT')

            <div class="space-y-5">
                {{-- Nama Sekolah --}}
                <x-input
                    name="nama"
                    label="Nama Sekolah"
                    :value="old('nama', $sekolah->nama)"
                    required
                    autocomplete="off"
                />

                {{-- Kode Sekolah (readonly, tidak bisa diubah) --}}
                <div>
                    <label class="form-label">Kode Sekolah</label>
                    <div class="form-input opacity-60 cursor-not-allowed select-none"
                         style="background: var(--color-navy-50);">
                        {{ $sekolah->kode }}
                    </div>
                    <p class="mt-1 text-sm" style="color: var(--color-muted);">
                        Kode tidak dapat diubah setelah sekolah dibuat.
                        URL login:
                        <code class="px-1 rounded text-xs" style="background: var(--color-navy-50);">
                            {{ $sekolah->urlLogin() }}
                        </code>
                    </p>
                </div>

                {{-- NPSN --}}
                <x-input
                    name="npsn"
                    label="NPSN (opsional)"
                    :value="old('npsn', $sekolah->npsn)"
                    placeholder="Nomor Pokok Sekolah Nasional"
                    inputmode="numeric"
                />

                {{-- Alamat --}}
                <x-textarea
                    name="alamat"
                    label="Alamat Sekolah (opsional)"
                    :value="old('alamat', $sekolah->alamat)"
                    rows="3"
                />

                {{-- Logo --}}
                <div>
                    <label class="form-label" for="logo">Logo Sekolah</label>
                    @if($sekolah->logo_path)
                        <div class="mb-3 flex items-center gap-3">
                            <span class="text-sm" style="color: var(--color-muted);">Logo saat ini tersimpan di sistem.</span>
                        </div>
                    @endif
                    <input
                        type="file"
                        id="logo"
                        name="logo"
                        accept="image/jpeg,image/png,image/webp"
                        class="block w-full text-sm file:mr-4 file:py-2 file:px-4 file:rounded-lg file:border-0
                               file:font-semibold file:text-white file:cursor-pointer focus:outline-none focus:ring-2"
                    >
                    @error('logo')
                        <p class="form-error" role="alert">{{ $message }}</p>
                    @enderror
                    <p class="mt-1 text-sm" style="color: var(--color-muted);">
                        Unggah file baru untuk mengganti logo. Biarkan kosong jika tidak ingin mengubah logo.
                    </p>
                </div>

                {{-- Status --}}
                <x-select
                    name="status"
                    label="Status"
                    :selected="old('status', $sekolah->status)"
                    required
                    :options="['aktif' => 'Aktif', 'nonaktif' => 'Nonaktif']"
                />

                @if($sekolah->status === 'aktif')
                    <p class="text-sm p-3 rounded-lg" style="background: #FFF3D6; color: #8A5A00;">
                        <strong>Perhatian:</strong> Mengubah status menjadi <em>Nonaktif</em> akan mencegah semua pengguna sekolah ini masuk ke sistem.
                    </p>
                @endif
            </div>

            <div class="mt-8 flex flex-wrap gap-3 pt-6" style="border-top: 1px solid var(--color-border);">
                <x-button type="submit" variant="primary" id="btn-simpan-ubah-sekolah">
                    <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true">
                        <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"/>
                    </svg>
                    Simpan Perubahan
                </x-button>
                <x-button variant="outline" href="{{ route('super-admin.sekolah.show', $sekolah) }}">
                    Batal
                </x-button>
            </div>
        </form>
    </x-card>

</x-layouts.super-admin>
