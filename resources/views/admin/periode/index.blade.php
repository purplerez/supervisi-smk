<x-app-layout :namaSekolah="$sekolah->nama" title="Periode Supervisi">

    <x-page-header
        title="Periode Supervisi"
        subtitle="Kelola periode pelaksanaan supervisi guru di {{ $sekolah->nama }}."
    >
        <x-slot:actions>
            <x-button type="button" variant="tambah" @click="$dispatch('buka-modal-tambah-periode')">
                <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 20 20" fill="currentColor">
                    <path fill-rule="evenodd" d="M10 3a1 1 0 011 1v5h5a1 1 0 110 2h-5v5a1 1 0 11-2 0v-5H4a1 1 0 110-2h5V4a1 1 0 011-1z" clip-rule="evenodd" />
                </svg>
                Tambah Periode Baru
            </x-button>
        </x-slot:actions>
    </x-page-header>

    {{-- Banner Peringatan Konfirmasi Aktivasi (Bila Masih Ada Guru Belum Ditugaskan) --}}
    @if(session('konfirmasi_aktifkan_id'))
        <div class="mb-6 p-5 rounded-2xl border-2 border-amber-300 bg-amber-50 text-amber-900 text-sm leading-relaxed shadow-sm"
             role="alert">
            <div class="flex items-start gap-3.5">
                <div class="w-10 h-10 rounded-xl bg-amber-100 flex items-center justify-center shrink-0 mt-0.5 text-amber-700">
                    <svg xmlns="http://www.w3.org/2000/svg" width="22" height="22" viewBox="0 0 20 20" fill="currentColor">
                        <path fill-rule="evenodd" d="M8.257 3.099c.765-1.36 2.722-1.36 3.486 0l5.58 9.92c.75 1.334-.213 2.98-1.742 2.98H4.42c-1.53 0-2.493-1.646-1.743-2.98l5.58-9.92zM11 13a1 1 0 11-2 0 1 1 0 012 0zm-1-8a1 1 0 00-1 1v3a1 1 0 002 0V6a1 1 0 00-1-1z" clip-rule="evenodd" />
                    </svg>
                </div>
                <div class="flex-1">
                    <p class="font-bold text-base text-amber-950 mb-1">Perhatian: Masih Ada Guru Belum Memiliki Penilai</p>
                    <p class="text-xs text-amber-900 mb-4">{{ session('konfirmasi_pesan') }}</p>
                    <div class="flex flex-wrap items-center gap-3">
                        <form method="POST" action="{{ route('admin.periode.aktifkan', ['kode' => $sekolah->kode, 'periode' => session('konfirmasi_aktifkan_id')]) }}">
                            @csrf
                            @method('PATCH')
                            <input type="hidden" name="konfirmasi_lanjut" value="1">
                            <x-button type="submit" variant="tambah" size="sm">
                                <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 20 20" fill="currentColor" class="mr-1.5">
                                    <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"/>
                                </svg>
                                Tetap Aktifkan Sekarang
                            </x-button>
                        </form>
                        <x-button variant="outline" size="sm" :href="route('admin.penugasan.belum-dinilai', ['kode' => $sekolah->kode, 'periode_id' => session('konfirmasi_aktifkan_id')])">
                            Lihat & Tugaskan Guru Terlebih Dahulu &rarr;
                        </x-button>
                    </div>
                </div>
            </div>
        </div>
    @endif

    {{-- Kartu Status Periode Supervisi Berjalan --}}
    @php
        $periodeAktifBerjalan = $daftarPeriode->firstWhere('status', 'aktif');
        $periodeDraftTersedia = $daftarPeriode->firstWhere('status', 'draft');
    @endphp

    @if(!$periodeAktifBerjalan && $periodeDraftTersedia)
        <div class="mb-6 p-5 rounded-2xl border border-amber-200 bg-amber-50/80 flex flex-col md:flex-row md:items-center justify-between gap-4 shadow-sm">
            <div class="flex items-start gap-3.5">
                <div class="w-10 h-10 rounded-xl bg-amber-200/80 flex items-center justify-center shrink-0 mt-0.5 text-amber-900">
                    <svg xmlns="http://www.w3.org/2000/svg" width="22" height="22" viewBox="0 0 20 20" fill="currentColor">
                        <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm1-12a1 1 0 10-2 0v4a1 1 0 00.293.707l2.828 2.829a1 1 0 101.415-1.415L11 9.586V6z" clip-rule="evenodd" />
                    </svg>
                </div>
                <div>
                    <h2 class="text-base font-bold text-amber-950">Periode Supervisi Saat Ini Berstatus Draf</h2>
                    <p class="text-xs text-amber-900 mt-0.5 leading-relaxed">
                        Guru dan supervisor tidak dapat mengisi informasi jadwal atau memulai penilaian supervisi selama periode belum diaktifkan.
                        Periode siap aktif: <strong>{{ $periodeDraftTersedia->nama }}</strong> ({{ $periodeDraftTersedia->penugasan_count }} penugasan guru).
                    </p>
                </div>
            </div>
            <form method="POST" action="{{ route('admin.periode.aktifkan', ['kode' => $sekolah->kode, 'periode' => $periodeDraftTersedia]) }}" class="shrink-0">
                @csrf
                @method('PATCH')
                <x-button type="submit" variant="tambah">
                    <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 20 20" fill="currentColor" class="mr-1.5">
                        <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM9.555 7.168A1 1 0 008 8v4a1 1 0 001.555.832l3-2a1 1 0 000-1.664l-3-2z" clip-rule="evenodd" />
                    </svg>
                    Aktifkan Periode Ini Sekarang
                </x-button>
            </form>
        </div>
    @elseif($periodeAktifBerjalan)
        <div class="mb-6 p-4 rounded-2xl border border-green-200 bg-green-50/60 flex flex-col md:flex-row md:items-center justify-between gap-4">
            <div class="flex items-center gap-3">
                <span class="w-3 h-3 rounded-full bg-green-500 animate-pulse"></span>
                <div>
                    <p class="text-xs font-semibold text-green-800 uppercase tracking-wider">Periode Supervisi Sedang Aktif</p>
                    <p class="text-sm font-bold text-green-950">{{ $periodeAktifBerjalan->nama }} &bull; TA {{ $periodeAktifBerjalan->tahun_ajaran }} ({{ ucfirst($periodeAktifBerjalan->semester ?? 'Ganjil') }})</p>
                </div>
            </div>
            <div class="text-xs text-green-800 flex items-center gap-2">
                <span>Rentang: <strong>{{ $periodeAktifBerjalan->tanggal_mulai->translatedFormat('d M Y') }} &ndash; {{ $periodeAktifBerjalan->tanggal_selesai->translatedFormat('d M Y') }}</strong></span>
            </div>
        </div>
    @endif

    {{-- Form Pencarian --}}
    <div class="mb-6 flex flex-col sm:flex-row gap-3 items-center justify-between">
        <form method="GET" action="{{ route('admin.periode.index', ['kode' => $sekolah->kode]) }}" class="w-full sm:w-80">
            <div class="relative">
                <input
                    type="search"
                    name="cari"
                    value="{{ $cari }}"
                    placeholder="Cari nama atau tahun ajaran..."
                    class="form-input text-sm w-full pl-9 pr-4 py-2"
                >
                <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 20 20" fill="currentColor"
                     class="absolute left-3 top-3 text-gray-400">
                    <path fill-rule="evenodd" d="M8 4a4 4 0 100 8 4 4 0 000-8zM2 8a6 6 0 1110.89 3.476l4.817 4.817a1 1 0 01-1.414 1.414l-4.816-4.816A6 6 0 012 8z" clip-rule="evenodd" />
                </svg>
            </div>
        </form>

        <div class="text-xs text-gray-500 self-start sm:self-auto">
            Hanya <strong>satu periode</strong> yang dapat aktif dalam satu waktu.
        </div>
    </div>

    {{-- Daftar Periode --}}
    <x-card class="overflow-hidden p-0" x-data="{
        modalEdit: false,
        periodeEdit: { id: '', nama: '', tahun_ajaran: '', semester: '', tanggal_mulai: '', tanggal_selesai: '' },
        bukaEdit(p) {
            this.periodeEdit = {
                id: p.id,
                nama: p.nama,
                tahun_ajaran: p.tahun_ajaran,
                semester: p.semester || '',
                tanggal_mulai: p.tanggal_mulai,
                tanggal_selesai: p.tanggal_selesai
            };
            this.modalEdit = true;
        }
    }">
        @if($daftarPeriode->isEmpty())
            <div class="p-8 text-center">
                <x-empty-state
                    title="Belum Ada Periode Supervisi"
                    description="{{ $cari ? 'Tidak ada periode yang sesuai dengan kata kunci pencarian.' : 'Silakan tambahkan periode supervisi baru untuk memulai penugasan guru.' }}"
                >
                    @if(!$cari)
                        <x-button type="button" variant="tambah" @click="$dispatch('buka-modal-tambah-periode')">
                            Tambah Periode Pertama
                        </x-button>
                    @endif
                </x-empty-state>
            </div>
        @else
            <div class="overflow-x-auto">
                <table class="w-full text-left text-sm" style="color: var(--color-ink);">
                    <thead style="background-color: var(--color-navy-50); color: var(--color-navy-900);">
                        <tr>
                            <th class="py-3.5 px-4 font-bold">Nama Periode</th>
                            <th class="py-3.5 px-4 font-bold">Tahun Ajaran / Semester</th>
                            <th class="py-3.5 px-4 font-bold">Rentang Tanggal</th>
                            <th class="py-3.5 px-4 font-bold text-center">Status</th>
                            <th class="py-3.5 px-4 font-bold text-center">Penugasan</th>
                            <th class="py-3.5 px-4 font-bold text-right">Aksi</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y" style="border-color: var(--color-border);">
                        @foreach($daftarPeriode as $periode)
                            <tr class="hover:bg-gray-50/60 transition-colors {{ $periode->isAktif() ? 'bg-green-50/30' : '' }}">
                                <td class="py-3.5 px-4 font-bold" style="color: var(--color-navy-900);">
                                    <div class="flex items-center gap-2">
                                        <span>{{ $periode->nama }}</span>
                                        @if($periode->isAktif())
                                            <span class="px-2 py-0.5 text-xs rounded-full bg-green-100 text-green-800 font-semibold">Sedang Berjalan</span>
                                        @endif
                                    </div>
                                </td>
                                <td class="py-3.5 px-4">
                                    {{ $periode->tahun_ajaran }}
                                    @if($periode->semester)
                                        <span class="text-xs text-gray-500 font-medium">({{ ucfirst($periode->semester) }})</span>
                                    @endif
                                </td>
                                <td class="py-3.5 px-4 whitespace-nowrap text-xs">
                                    {{ $periode->tanggal_mulai->translatedFormat('d M Y') }} &ndash; {{ $periode->tanggal_selesai->translatedFormat('d M Y') }}
                                </td>
                                <td class="py-3.5 px-4 text-center">
                                    @if($periode->isAktif())
                                        <x-badge-status status="final">Aktif</x-badge-status>
                                    @elseif($periode->isDraft())
                                        <x-badge-status status="draft">Draf</x-badge-status>
                                    @else
                                        <x-badge-status status="belum">Ditutup</x-badge-status>
                                    @endif
                                </td>
                                <td class="py-3.5 px-4 text-center font-bold">
                                    <a href="{{ route('admin.penugasan.index', ['kode' => $sekolah->kode, 'periode_id' => $periode->id]) }}"
                                       class="text-blue-700 hover:underline">
                                        {{ $periode->penugasan_count }} guru
                                    </a>
                                </td>
                                <td class="py-3.5 px-4 text-right">
                                    <div class="flex items-center justify-end gap-2">
                                        {{-- Tombol Aktifkan (Jika Draf) --}}
                                        @if($periode->isDraft())
                                            <form method="POST" action="{{ route('admin.periode.aktifkan', ['kode' => $sekolah->kode, 'periode' => $periode]) }}">
                                                @csrf
                                                @method('PATCH')
                                                <x-button type="submit" variant="tambah" size="sm" title="Aktifkan Periode Ini">
                                                    <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 20 20" fill="currentColor" class="mr-1">
                                                        <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM9.555 7.168A1 1 0 008 8v4a1 1 0 001.555.832l3-2a1 1 0 000-1.664l-3-2z" clip-rule="evenodd" />
                                                    </svg>
                                                    Aktifkan
                                                </x-button>
                                            </form>
                                        @endif

                                        {{-- Tombol Tutup (Jika Aktif) --}}
                                        @if($periode->isAktif())
                                            <form method="POST" action="{{ route('admin.periode.tutup', ['kode' => $sekolah->kode, 'periode' => $periode]) }}"
                                                  onsubmit="return confirm('Apakah Anda yakin ingin MENUTUP periode {{ $periode->nama }}? Seluruh penilaian pada periode ini akan TERKUNCI secara permanen.');">
                                                @csrf
                                                @method('PATCH')
                                                <x-button type="submit" variant="outline" size="sm" title="Tutup Periode (Kunci Penilaian)">
                                                    Tutup Periode
                                                </x-button>
                                            </form>
                                        @endif

                                        {{-- Tombol Ubah (Jika Belum Ditutup) --}}
                                        @if(!$periode->isDitutup())
                                            <x-button type="button" variant="edit" size="sm" @click="bukaEdit({{ json_encode([
                                                'id' => $periode->id,
                                                'nama' => $periode->nama,
                                                'tahun_ajaran' => $periode->tahun_ajaran,
                                                'semester' => $periode->semester,
                                                'tanggal_mulai' => $periode->tanggal_mulai->format('Y-m-d'),
                                                'tanggal_selesai' => $periode->tanggal_selesai->format('Y-m-d'),
                                            ]) }})"
                                                    title="Ubah Data Periode">
                                                Ubah
                                            </x-button>
                                        @endif

                                        {{-- Tombol Hapus (Hanya Draf dan 0 penugasan) --}}
                                        @if($periode->isDraft() && $periode->penugasan_count === 0)
                                            <form method="POST" action="{{ route('admin.periode.destroy', ['kode' => $sekolah->kode, 'periode' => $periode]) }}"
                                                  onsubmit="return confirm('Hapus periode supervisi ini?');">
                                                @csrf
                                                @method('DELETE')
                                                <x-button type="submit" variant="hapus" size="sm" title="Hapus Periode">
                                                    Hapus
                                                </x-button>
                                            </form>
                                        @endif
                                    </div>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

            @if($daftarPeriode->hasPages())
                <div class="p-4 border-t" style="border-color: var(--color-border);">
                    {{ $daftarPeriode->links() }}
                </div>
            @endif
        @endif

        {{-- MODAL EDIT PERIODE --}}
        <div x-show="modalEdit" class="fixed inset-0 z-50 overflow-y-auto bg-black/40 flex items-center justify-center p-4" style="display: none;">
            <div @click.outside="modalEdit = false" class="bg-white rounded-xl max-w-lg w-full p-6 shadow-xl border" style="border-color: var(--color-border);">
                <h3 class="text-xl font-bold mb-4" style="color: var(--color-navy-900);">Ubah Periode Supervisi</h3>
                <form :action="'{{ url('/s/' . $sekolah->kode . '/admin/periode') }}/' + periodeEdit.id" method="POST">
                    @csrf
                    @method('PUT')
                    <div class="space-y-4 mb-5 text-sm">
                        <div>
                            <label class="block font-medium mb-1" style="color: var(--color-navy-900);">Nama Periode *</label>
                            <input type="text" name="nama" x-model="periodeEdit.nama" required class="form-input text-sm w-full">
                        </div>
                        <div class="grid grid-cols-2 gap-3">
                            <div>
                                <label class="block font-medium mb-1" style="color: var(--color-navy-900);">Tahun Ajaran *</label>
                                <input type="text" name="tahun_ajaran" x-model="periodeEdit.tahun_ajaran" placeholder="mis. 2026/2027" required class="form-input text-sm w-full">
                            </div>
                            <div>
                                <label class="block font-medium mb-1" style="color: var(--color-navy-900);">Semester</label>
                                <select name="semester" x-model="periodeEdit.semester" class="form-input text-sm w-full">
                                    <option value="">Pilih Semester</option>
                                    <option value="ganjil">Ganjil</option>
                                    <option value="genap">Genap</option>
                                </select>
                            </div>
                        </div>
                        <div class="grid grid-cols-2 gap-3">
                            <div>
                                <label class="block font-medium mb-1" style="color: var(--color-navy-900);">Tanggal Mulai *</label>
                                <input type="date" name="tanggal_mulai" x-model="periodeEdit.tanggal_mulai" required class="form-input text-sm w-full">
                            </div>
                            <div>
                                <label class="block font-medium mb-1" style="color: var(--color-navy-900);">Tanggal Selesai *</label>
                                <input type="date" name="tanggal_selesai" x-model="periodeEdit.tanggal_selesai" required class="form-input text-sm w-full">
                            </div>
                        </div>
                    </div>
                    <div class="flex justify-end gap-2">
                        <button type="button" @click="modalEdit = false" class="px-4 py-2 text-sm rounded border border-gray-300">Batal</button>
                        <x-button type="submit" variant="tambah">Simpan Perubahan</x-button>
                    </div>
                </form>
            </div>
        </div>
    </x-card>

    {{-- MODAL TAMBAH PERIODE --}}
    <div x-data="{ modalTambah: false }"
         @buka-modal-tambah-periode.window="modalTambah = true"
         x-show="modalTambah"
         class="fixed inset-0 z-50 overflow-y-auto bg-black/40 flex items-center justify-center p-4"
         style="display: none;">
        <div @click.outside="modalTambah = false" class="bg-white rounded-xl max-w-lg w-full p-6 shadow-xl border" style="border-color: var(--color-border);">
            <h3 class="text-xl font-bold mb-4" style="color: var(--color-navy-900);">Tambah Periode Supervisi</h3>
            <form action="{{ route('admin.periode.store', ['kode' => $sekolah->kode]) }}" method="POST">
                @csrf
                <div class="space-y-4 mb-5 text-sm">
                    <div>
                        <label class="block font-medium mb-1" style="color: var(--color-navy-900);">Nama Periode *</label>
                        <input type="text" name="nama" placeholder="mis. Supervisi Akademik Semester Ganjil 2026/2027" required class="form-input text-sm w-full">
                    </div>
                    <div class="grid grid-cols-2 gap-3">
                        <div>
                            <label class="block font-medium mb-1" style="color: var(--color-navy-900);">Tahun Ajaran *</label>
                            <input type="text" name="tahun_ajaran" placeholder="mis. 2026/2027" required class="form-input text-sm w-full">
                        </div>
                        <div>
                            <label class="block font-medium mb-1" style="color: var(--color-navy-900);">Semester</label>
                            <select name="semester" class="form-input text-sm w-full">
                                <option value="">Pilih Semester</option>
                                <option value="ganjil">Ganjil</option>
                                <option value="genap">Genap</option>
                            </select>
                        </div>
                    </div>
                    <div class="grid grid-cols-2 gap-3">
                        <div>
                            <label class="block font-medium mb-1" style="color: var(--color-navy-900);">Tanggal Mulai *</label>
                            <input type="date" name="tanggal_mulai" required class="form-input text-sm w-full">
                        </div>
                        <div>
                            <label class="block font-medium mb-1" style="color: var(--color-navy-900);">Tanggal Selesai *</label>
                            <input type="date" name="tanggal_selesai" required class="form-input text-sm w-full">
                        </div>
                    </div>
                </div>
                <div class="flex justify-end gap-2">
                    <button type="button" @click="modalTambah = false" class="px-4 py-2 text-sm rounded border border-gray-300">Batal</button>
                    <x-button type="submit" variant="tambah">Simpan Periode</x-button>
                </div>
            </form>
        </div>
    </div>

</x-app-layout>
