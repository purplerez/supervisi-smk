<x-app-layout :namaSekolah="$sekolah->nama" title="Impor Data Guru (Excel)">
    <div x-data="{ modalGalat: false, galatDetail: [] }">
        {{-- Header Halaman --}}
        <x-page-header
            title="Impor Data Guru"
            subtitle="Unggah dan perbarui data akun guru secara massal menggunakan file Excel (.xlsx)."
        >
            <x-slot name="actions">
                <div class="flex flex-wrap items-center gap-3">
                    <x-button variant="outline" :href="route('admin.guru.index', ['kode' => $sekolah->kode])">
                        &larr; Kembali ke Daftar Guru
                    </x-button>

                    <x-button variant="tambah" :href="route('admin.guru.import.template', ['kode' => $sekolah->kode])" download="Template-Data-Guru.xlsx">
                        <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 20 20" fill="currentColor" class="mr-1.5">
                            <path fill-rule="evenodd" d="M3 17a1 1 0 011-1h12a1 1 0 110 2H4a1 1 0 01-1-1zm3.293-7.707a1 1 0 011.414 0L9 10.586V3a1 1 0 112 0v7.586l1.293-1.293a1 1 0 111.414 1.414l-3 3a1 1 0 01-1.414 0l-3-3a1 1 0 010-1.414z" clip-rule="evenodd"/>
                        </svg>
                        Unduh Template Excel
                    </x-button>
                </div>
            </x-slot>
        </x-page-header>

        {{-- Indikator Langkah (Step Indicator untuk pengguna 35+) --}}
        <div class="mb-8">
            <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                {{-- Langkah 1 --}}
                <div class="flex items-center gap-3 p-4 rounded-xl border {{ !$pratinjau ? 'bg-amber-50 border-amber-300' : 'bg-white border-gray-200 opacity-60' }}">
                    <div class="w-8 h-8 rounded-full flex items-center justify-center font-bold text-sm {{ !$pratinjau ? 'bg-amber-500 text-white' : 'bg-gray-200 text-gray-700' }}">
                        1
                    </div>
                    <div>
                        <p class="font-bold text-sm text-gray-900">Langkah 1: Unggah</p>
                        <p class="text-xs text-gray-500">Pilih file Excel template</p>
                    </div>
                </div>

                {{-- Langkah 2 --}}
                <div class="flex items-center gap-3 p-4 rounded-xl border {{ $pratinjau ? 'bg-amber-50 border-amber-300' : 'bg-white border-gray-200 opacity-60' }}">
                    <div class="w-8 h-8 rounded-full flex items-center justify-center font-bold text-sm {{ $pratinjau ? 'bg-amber-500 text-white' : 'bg-gray-200 text-gray-700' }}">
                        2
                    </div>
                    <div>
                        <p class="font-bold text-sm text-gray-900">Langkah 2: Pratinjau & Validasi</p>
                        <p class="text-xs text-gray-500">Pemeriksaan tanpa menyimpan</p>
                    </div>
                </div>

                {{-- Langkah 3 --}}
                <div class="flex items-center gap-3 p-4 rounded-xl border bg-white border-gray-200 opacity-60">
                    <div class="w-8 h-8 rounded-full flex items-center justify-center font-bold text-sm bg-gray-200 text-gray-700">
                        3
                    </div>
                    <div>
                        <p class="font-bold text-sm text-gray-900">Langkah 3: Selesai</p>
                        <p class="text-xs text-gray-500">Data tersimpan & dicatat</p>
                    </div>
                </div>
            </div>
        </div>

        {{-- ========================================================================= --}}
        {{-- KONDISI 1: FORM UNGGAH FILE (LANGKAH 1) --}}
        {{-- ========================================================================= --}}
        @if(!$pratinjau)
            <div class="grid grid-cols-1 lg:grid-cols-3 gap-6 mb-8">
                {{-- Form Unggah --}}
                <div class="lg:col-span-2">
                    <x-card class="p-6">
                        <h2 class="text-lg font-bold text-gray-900 mb-2">Unggah Berkas Excel</h2>
                        <p class="text-sm text-gray-600 mb-6 leading-relaxed">
                            Pastikan Anda menggunakan template Excel yang sesuai. Sistem akan memeriksa data terlebih dahulu pada tahap pratinjau (dry run) sebelum data benar-benar disimpan ke dalam sistem.
                        </p>

                        <form method="POST" action="{{ route('admin.guru.import.upload', ['kode' => $sekolah->kode]) }}" enctype="multipart/form-data" class="space-y-6">
                            @csrf

                            <div>
                                <label for="file" class="block text-sm font-semibold text-gray-700 mb-2">
                                    Pilih Berkas Excel (.xlsx, .xls, .csv) *
                                </label>
                                <input
                                    type="file"
                                    name="file"
                                    id="file"
                                    required
                                    accept=".xlsx,.xls,.csv"
                                    class="w-full text-sm text-gray-500 file:mr-4 file:py-2.5 file:px-4 file:rounded-lg file:border-0 file:text-sm file:font-semibold file:bg-blue-50 file:text-blue-900 hover:file:bg-blue-100 border border-gray-300 rounded-lg p-2 focus:ring-2 focus:ring-amber-500"
                                >
                                @error('file')
                                    <p class="text-xs text-red-600 font-semibold mt-1">{{ $message }}</p>
                                @enderror
                            </div>

                            <div class="flex items-center justify-end gap-3 pt-4 border-t border-gray-200">
                                <x-button variant="primary" type="submit">
                                    <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 20 20" fill="currentColor" class="mr-1.5">
                                        <path fill-rule="evenodd" d="M3 17a1 1 0 011-1h12a1 1 0 110 2H4a1 1 0 01-1-1zM6.293 6.707a1 1 0 010-1.414l3-3a1 1 0 011.414 0l3 3a1 1 0 01-1.414 1.414L11 5.414V13a1 1 0 11-2 0V5.414L7.707 6.707a1 1 0 01-1.414 0z" clip-rule="evenodd"/>
                                    </svg>
                                    Unggah & Periksa File
                                </x-button>
                            </div>
                        </form>
                    </x-card>
                </div>

                {{-- Panduan Pengisian Template --}}
                <div>
                    <x-card class="p-6 bg-blue-50/50 border-blue-200">
                        <h3 class="font-bold text-base text-gray-900 mb-3 flex items-center gap-2">
                            <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 20 20" fill="currentColor" class="text-blue-700">
                                <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7-4a1 1 0 11-2 0 1 1 0 012 0zM9 9a1 1 0 000 2v3a1 1 0 001 1h1a1 1 0 100-2v-3a1 1 0 00-1-1H9z" clip-rule="evenodd"/>
                            </svg>
                            Ketentuan Kolom Template
                        </h3>
                        <ul class="text-xs text-gray-700 space-y-2.5 leading-relaxed">
                            <li><strong>nama:</strong> Wajib diisi (nama lengkap beserta gelar).</li>
                            <li><strong>username:</strong> Wajib diisi, unik per sekolah, tanpa spasi (dipakai untuk masuk/login).</li>
                            <li><strong>email:</strong> Opsional, isi jika ada.</li>
                            <li><strong>nip / nuptk:</strong> Opsional, boleh dikosongkan atau diisi tanda strip (-).</li>
                            <li><strong>password:</strong> Wajib diisi minimal 8 karakter untuk <strong>akun baru</strong>. Pada akun lama, kolom password akan <em>dilewati</em> secara otomatis.</li>
                            <li><strong>Role:</strong> Semua akun baru otomatis memiliki peran <strong>Guru</strong>. Peran Supervisor dapat ditentukan kemudian lewat dasbor admin.</li>
                        </ul>
                    </x-card>
                </div>
            </div>
        @endif

        {{-- ========================================================================= --}}
        {{-- KONDISI 2: PRATINJAU & VALIDASI DRY RUN (LANGKAH 2) --}}
        {{-- ========================================================================= --}}
        @if($pratinjau)
            <div class="mb-8 space-y-6">
                {{-- Ringkasan Statistik Dry Run --}}
                <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
                    <x-card class="p-4 text-center">
                        <p class="text-xs font-semibold text-gray-500 uppercase">Total Baris</p>
                        <p class="text-2xl font-bold text-gray-900 mt-1">{{ $pratinjau['total_baris'] }}</p>
                    </x-card>

                    <x-card class="p-4 text-center border-l-4 border-l-green-500">
                        <p class="text-xs font-semibold text-green-700 uppercase">Akun Baru</p>
                        <p class="text-2xl font-bold text-green-900 mt-1">{{ $pratinjau['jumlah_baru'] }}</p>
                    </x-card>

                    <x-card class="p-4 text-center border-l-4 border-l-blue-500">
                        <p class="text-xs font-semibold text-blue-700 uppercase">Akun Diperbarui</p>
                        <p class="text-2xl font-bold text-blue-900 mt-1">{{ $pratinjau['jumlah_update'] }}</p>
                    </x-card>

                    <x-card class="p-4 text-center border-l-4 {{ $pratinjau['jumlah_galat'] > 0 ? 'border-l-red-500' : 'border-l-gray-300' }}">
                        <p class="text-xs font-semibold {{ $pratinjau['jumlah_galat'] > 0 ? 'text-red-700' : 'text-gray-500' }} uppercase">Baris Galat</p>
                        <p class="text-2xl font-bold {{ $pratinjau['jumlah_galat'] > 0 ? 'text-red-900' : 'text-gray-700' }} mt-1">{{ $pratinjau['jumlah_galat'] }}</p>
                    </x-card>
                </div>

                {{-- Daftar Galat jika ada --}}
                @if(count($pratinjau['galat_list']) > 0)
                    <div class="p-4 rounded-xl border border-red-200 bg-red-50">
                        <div class="flex items-center gap-2 font-bold text-red-900 text-sm mb-2">
                            <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 20 20" fill="currentColor">
                                <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7 4a1 1 0 11-2 0 1 1 0 012 0zm-1-9a1 1 0 00-1 1v4a1 1 0 102 0V6a1 1 0 00-1-1z" clip-rule="evenodd"/>
                            </svg>
                            Ditemukan {{ count($pratinjau['galat_list']) }} Catatan Galat:
                        </div>
                        <ul class="list-disc list-inside text-xs text-red-800 space-y-1">
                            @foreach(array_slice($pratinjau['galat_list'], 0, 10) as $err)
                                <li>{{ $err }}</li>
                            @endforeach
                            @if(count($pratinjau['galat_list']) > 10)
                                <li class="font-semibold italic">...dan {{ count($pratinjau['galat_list']) - 10 }} catatan lainnya.</li>
                            @endif
                        </ul>
                    </div>
                @else
                    <div class="p-4 rounded-xl border border-green-200 bg-green-50 flex items-center gap-3">
                        <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 20 20" fill="currentColor" class="text-green-700 shrink-0">
                            <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/>
                        </svg>
                        <p class="text-sm font-semibold text-green-900">
                            Semua data pada berkas valid dan siap untuk diproses ke dalam database.
                        </p>
                    </div>
                @endif

                {{-- Tombol Tindakan Pratinjau --}}
                <div class="flex flex-wrap items-center justify-between gap-4 p-4 rounded-xl bg-white border border-gray-200 shadow-sm">
                    <div class="text-sm text-gray-700">
                        Berkas yang sedang diperiksa: <strong class="font-mono text-gray-900">{{ $originalName }}</strong>
                    </div>

                    <div class="flex items-center gap-3">
                        {{-- Batalkan --}}
                        <form method="POST" action="{{ route('admin.guru.import.cancel', ['kode' => $sekolah->kode]) }}">
                            @csrf
                            @method('DELETE')
                            <x-button variant="outline" type="submit" onclick="return confirm('Batalkan proses impor dan hapus berkas sementara?')">
                                Batalkan & Hapus Berkas
                            </x-button>
                        </form>

                        {{-- Konfirmasi --}}
                        <form method="POST" action="{{ route('admin.guru.import.confirm', ['kode' => $sekolah->kode]) }}">
                            @csrf
                            <x-button
                                variant="tambah"
                                type="submit"
                                :disabled="!$pratinjau['bisa_diproses']"
                                onclick="return confirm('Proses impor data sekarang? Akun lama hanya akan diperbarui profilnya tanpa mengubah sandi.')"
                            >
                                <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 20 20" fill="currentColor" class="mr-1.5">
                                    <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"/>
                                </svg>
                                Konfirmasi & Simpan ke Sistem
                            </x-button>
                        </form>
                    </div>
                </div>

                {{-- Tabel Pratinjau Baris Data --}}
                <x-card class="overflow-hidden">
                    <div class="px-5 py-4 border-b border-gray-200 font-bold text-gray-900" style="background-color: var(--color-navy-50);">
                        Pratinjau Setiap Baris Data (Maksimal 50 baris pertama)
                    </div>
                    <div class="overflow-x-auto">
                        <table class="w-full text-left text-sm text-gray-700">
                            <thead class="text-xs uppercase font-semibold text-gray-700 bg-gray-50 border-b">
                                <tr>
                                    <th class="px-4 py-3">Baris</th>
                                    <th class="px-4 py-3">Nama Guru</th>
                                    <th class="px-4 py-3">Username</th>
                                    <th class="px-4 py-3">Status Akun</th>
                                    <th class="px-4 py-3">Perlakuan Sandi</th>
                                    <th class="px-4 py-3">Hasil Validasi</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-gray-200">
                                @foreach(array_slice($pratinjau['daftar_baris'], 0, 50) as $b)
                                    <tr class="{{ !$b['is_valid'] ? 'bg-red-50/50' : '' }}">
                                        <td class="px-4 py-3 font-mono text-xs text-gray-500">
                                            #{{ $b['nomor_baris'] }}
                                        </td>
                                        <td class="px-4 py-3 font-bold text-gray-900">
                                            {{ $b['nama'] ?: '(Kosong)' }}
                                            @if($b['nip']) <span class="block text-xs text-gray-500 font-normal">NIP: {{ $b['nip'] }}</span> @endif
                                        </td>
                                        <td class="px-4 py-3 font-mono text-xs text-gray-800">
                                            {{ $b['username'] ?: '(Kosong)' }}
                                        </td>
                                        <td class="px-4 py-3">
                                            @if($b['status_akun'] === 'baru')
                                                <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-semibold bg-green-100 text-green-800">
                                                    Akun Baru
                                                </span>
                                            @else
                                                <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-semibold bg-blue-100 text-blue-800">
                                                    Perbarui Data
                                                </span>
                                            @endif
                                        </td>
                                        <td class="px-4 py-3 text-xs text-gray-600">
                                            {{ $b['catatan_password'] }}
                                        </td>
                                        <td class="px-4 py-3 text-xs">
                                            @if($b['is_valid'])
                                                <span class="text-green-700 font-semibold flex items-center gap-1">
                                                    <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 20 20" fill="currentColor">
                                                        <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"/>
                                                    </svg>
                                                    Valid
                                                </span>
                                            @else
                                                <span class="text-red-700 font-semibold">
                                                    {{ implode(', ', $b['galat']) }}
                                                </span>
                                            @endif
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </x-card>
            </div>
        @endif

        {{-- ========================================================================= --}}
        {{-- RIWAYAT BATCH IMPOR --}}
        {{-- ========================================================================= --}}
        <div class="mt-10">
            <h2 class="text-lg font-bold text-gray-900 mb-4">Riwayat Impor Data Guru</h2>

            <x-card class="overflow-hidden">
                <div class="overflow-x-auto">
                    <table class="w-full text-left text-sm text-gray-700">
                        <thead class="text-xs uppercase font-semibold text-gray-700 border-b" style="background-color: var(--color-navy-50);">
                            <tr>
                                <th class="px-5 py-4">Waktu Impor</th>
                                <th class="px-5 py-4">Nama Berkas</th>
                                <th class="px-5 py-4">Diimpor Oleh</th>
                                <th class="px-5 py-4">Total</th>
                                <th class="px-5 py-4">Berhasil / Gagal</th>
                                <th class="px-5 py-4">Status</th>
                                <th class="px-5 py-4 text-right">Rincian</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-200">
                            @forelse($riwayat as $r)
                                <tr>
                                    <td class="px-5 py-4 text-xs text-gray-600 font-mono">
                                        {{ $r->created_at->format('d M Y, H:i') }}
                                    </td>
                                    <td class="px-5 py-4 font-semibold text-gray-900">
                                        {{ $r->nama_file }}
                                    </td>
                                    <td class="px-5 py-4 text-xs text-gray-700">
                                        {{ $r->user ? $r->user->nama : 'Sistem' }}
                                    </td>
                                    <td class="px-5 py-4 text-sm font-semibold">
                                        {{ $r->total }}
                                    </td>
                                    <td class="px-5 py-4 text-xs">
                                        <span class="text-green-700 font-semibold">{{ $r->sukses }} sukses</span>
                                        @if($r->gagal > 0)
                                            <span class="text-red-700 font-semibold ml-1">/ {{ $r->gagal }} gagal</span>
                                        @endif
                                    </td>
                                    <td class="px-5 py-4">
                                        @if($r->status === 'sukses')
                                            <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-semibold bg-green-100 text-green-800">
                                                Sukses
                                            </span>
                                        @elseif($r->status === 'sebagian')
                                            <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-semibold bg-amber-100 text-amber-800">
                                                Sebagian
                                            </span>
                                        @else
                                            <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-semibold bg-red-100 text-red-800">
                                                Gagal
                                            </span>
                                        @endif
                                    </td>
                                    <td class="px-5 py-4 text-right">
                                        @if($r->errors && count($r->errors) > 0)
                                            <button
                                                type="button"
                                                @click="galatDetail = {{ json_encode($r->errors) }}; modalGalat = true;"
                                                class="px-2.5 py-1 text-xs font-semibold rounded border border-red-300 text-red-700 hover:bg-red-50 transition-colors"
                                            >
                                                Lihat Galat
                                            </button>
                                        @else
                                            <span class="text-xs text-gray-400">-</span>
                                        @endif
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="7" class="px-5 py-8 text-center text-gray-500 text-sm">
                                        Belum ada riwayat impor data guru di sekolah ini.
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

                @if($riwayat->hasPages())
                    <div class="px-5 py-4 border-t border-gray-200">
                        {{ $riwayat->links() }}
                    </div>
                @endif
            </x-card>
        </div>

        {{-- Modal Detail Galat Riwayat --}}
        <div
            x-show="modalGalat"
            class="fixed inset-0 z-50 overflow-y-auto bg-black/50 flex items-center justify-center p-4"
            x-transition.opacity
            style="display: none;"
        >
            <div
                @click.outside="modalGalat = false"
                class="bg-white rounded-2xl shadow-xl w-full max-w-lg overflow-hidden border border-gray-200"
            >
                <div class="px-6 py-4 border-b border-gray-200 flex items-center justify-between bg-red-50">
                    <h3 class="font-bold text-lg text-red-900">Rincian Galat Impor</h3>
                    <button type="button" @click="modalGalat = false" class="text-gray-400 hover:text-gray-600 text-2xl font-bold">&times;</button>
                </div>

                <div class="p-6 max-h-96 overflow-y-auto space-y-3">
                    <template x-for="(err, idx) in galatDetail" :key="idx">
                        <div class="p-3 rounded-lg bg-gray-50 border border-gray-200 text-xs">
                            <span class="font-bold text-gray-800" x-text="`Baris #${err.baris} (Username: ${err.username || '-'}):`"></span>
                            <p class="text-red-700 mt-0.5" x-text="err.pesan"></p>
                        </div>
                    </template>
                </div>

                <div class="px-6 py-3 border-t border-gray-200 flex justify-end bg-gray-50">
                    <x-button variant="outline" type="button" @click="modalGalat = false">Tutup</x-button>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
