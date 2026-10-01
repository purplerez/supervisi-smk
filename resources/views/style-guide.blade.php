<!DOCTYPE html>
<html lang="id" class="h-full">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Style Guide — {{ config('app.name') }}</title>
    <meta name="description" content="Panduan visual komponen UI aplikasi Supervisi Guru.">
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body style="background-color: var(--color-cream); padding-bottom: 4rem;">

{{-- ===== HERO ===== --}}
<div style="background: var(--color-navy-900); padding: 3rem 2rem 2.5rem;">
    <div style="max-width: 1100px; margin: 0 auto;">
        <p style="color: var(--color-orange-500); font-size: 0.82rem; font-weight: 600; letter-spacing: 0.08em; text-transform: uppercase; margin-bottom: 0.5rem;">Design System</p>
        <h1 style="color: #fff; font-size: 2rem; font-weight: 700; margin: 0 0 0.5rem;">Style Guide</h1>
        <p style="color: rgba(255,255,255,0.6); margin: 0; font-size: 1rem;">Supervisi Guru – token warna, tipografi, dan komponen UI</p>
    </div>
</div>

<div style="max-width: 1100px; margin: 2.5rem auto; padding: 0 1.5rem;" x-data="{}">

    {{-- ===== PALET WARNA ===== --}}
    <section aria-labelledby="sec-warna" class="mb-12">
        <h2 id="sec-warna" class="section-title mb-6" style="display:flex;align-items:center;gap:.5rem;">
            <span style="display:inline-block;width:4px;height:20px;background:var(--color-orange-500);border-radius:2px;"></span>
            Palet Warna
        </h2>

        <div style="display: grid; grid-template-columns: repeat(auto-fill, minmax(160px, 1fr)); gap: 1rem;">
            @php
            $swatches = [
                ['name' => 'navy-900', 'hex' => '#253C6D', 'text' => '#fff', 'desc' => 'Tombol utama, top bar'],
                ['name' => 'navy-700', 'hex' => '#30497D', 'text' => '#fff', 'desc' => 'Hover, link'],
                ['name' => 'navy-50',  'hex' => '#E6ECF7', 'text' => '#253C6D', 'desc' => 'Header tabel, aktif'],
                ['name' => 'cream',    'hex' => '#FFFAF3', 'text' => '#1F2937', 'desc' => 'Latar halaman'],
                ['name' => 'ink',      'hex' => '#1F2937', 'text' => '#fff', 'desc' => 'Teks utama'],
                ['name' => 'muted',    'hex' => '#5B6475', 'text' => '#fff', 'desc' => 'Teks sekunder'],
                ['name' => 'border',   'hex' => '#E8E0D2', 'text' => '#1F2937', 'desc' => 'Border hangat'],
                ['name' => 'orange-500 (aksen)', 'hex' => '#F2842F', 'text' => '#fff', 'desc' => 'HANYA ornamen'],
                ['name' => 'orange-700', 'hex' => '#B54A08', 'text' => '#fff', 'desc' => 'Teks oranye di krem'],
                ['name' => 'green (tambah)', 'hex' => '#2E7D32', 'text' => '#fff', 'desc' => 'Tombol Tambah/Simpan'],
                ['name' => 'yellow (edit)',  'hex' => '#F5B82E', 'text' => '#1F2937', 'desc' => 'Tombol Edit/Ubah'],
                ['name' => 'red (hapus)',    'hex' => '#C62828', 'text' => '#fff', 'desc' => 'Tombol Hapus'],
            ];
            @endphp

            @foreach($swatches as $s)
            <div class="card p-0 overflow-hidden">
                <div style="height: 80px; background: {{ $s['hex'] }}; display:flex; align-items:flex-end; padding: 0.5rem 0.75rem;">
                    <code style="color: {{ $s['text'] }}; font-size: 0.75rem; opacity: 0.85; font-family: monospace;">{{ $s['hex'] }}</code>
                </div>
                <div style="padding: 0.75rem;">
                    <p style="font-weight: 600; font-size: 0.82rem; margin: 0 0 0.15rem; color: var(--color-ink);">{{ $s['name'] }}</p>
                    <p style="font-size: 0.76rem; color: var(--color-muted); margin: 0;">{{ $s['desc'] }}</p>
                </div>
            </div>
            @endforeach
        </div>
    </section>

    {{-- ===== TIPOGRAFI ===== --}}
    <section aria-labelledby="sec-tipografi" class="mb-12">
        <h2 id="sec-tipografi" class="section-title mb-6" style="display:flex;align-items:center;gap:.5rem;">
            <span style="display:inline-block;width:4px;height:20px;background:var(--color-orange-500);border-radius:2px;"></span>
            Tipografi
        </h2>

        <div class="card">
            <div class="mb-6 pb-6" style="border-bottom: 1px solid var(--color-border);">
                <p class="text-muted text-sm mb-2">Judul Halaman — 28px / Bold</p>
                <h1 style="margin:0;">Supervisi Guru Semester Ganjil</h1>
            </div>
            <div class="mb-6 pb-6" style="border-bottom: 1px solid var(--color-border);">
                <p class="text-muted text-sm mb-2">Judul Bagian — 20px / SemiBold</p>
                <h2 style="margin:0;">Daftar Penilaian KBM</h2>
            </div>
            <div class="mb-6 pb-6" style="border-bottom: 1px solid var(--color-border);">
                <p class="text-muted text-sm mb-2">Teks Dasar — 17px / Regular (1.6 line-height)</p>
                <p style="margin:0;">Guru dapat mengisi informasi kelas, mata pelajaran, dan capaian pembelajaran pada setiap periode supervisi. Informasi ini dikunci setelah penilaian pertama dimulai.</p>
            </div>
            <div class="mb-6 pb-6" style="border-bottom: 1px solid var(--color-border);">
                <p class="text-muted text-sm mb-2">Teks Kecil — 14px / Regular (batas minimum)</p>
                <p style="font-size: 14px; margin:0; color: var(--color-muted);">Catatan: semua teks minimal 14px sesuai panduan aksesibilitas.</p>
            </div>
            <div>
                <p class="text-muted text-sm mb-2">Font Family</p>
                <p style="margin:0; font-size: 1.1rem;">Inter — <span style="font-weight:400;">Regular</span> · <span style="font-weight:500;">Medium</span> · <span style="font-weight:600;">SemiBold</span> · <span style="font-weight:700;">Bold</span></p>
                <p style="margin: 0.5rem 0 0; font-size: 0.82rem; color: var(--color-muted);">Di-bundle lokal via @fontsource/inter — tanpa CDN</p>
            </div>
        </div>
    </section>

    {{-- ===== TOMBOL ===== --}}
    <section aria-labelledby="sec-tombol" class="mb-12">
        <h2 id="sec-tombol" class="section-title mb-6" style="display:flex;align-items:center;gap:.5rem;">
            <span style="display:inline-block;width:4px;height:20px;background:var(--color-orange-500);border-radius:2px;"></span>
            Tombol
        </h2>

        <div class="card mb-4">
            <p class="text-muted text-sm mb-4">Ukuran: md (default)</p>
            <div class="flex flex-wrap gap-3 mb-6">
                <x-button variant="primary" id="btn-primary-demo">Tombol Utama</x-button>
                <x-button variant="tambah" id="btn-tambah-demo">
                    <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true"><path fill-rule="evenodd" d="M10 3a1 1 0 011 1v5h5a1 1 0 110 2h-5v5a1 1 0 11-2 0v-5H4a1 1 0 110-2h5V4a1 1 0 011-1z" clip-rule="evenodd"/></svg>
                    Tambah Guru
                </x-button>
                <x-button variant="edit" id="btn-edit-demo">
                    <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true"><path d="M13.586 3.586a2 2 0 112.828 2.828l-.793.793-2.828-2.828.793-.793zM11.379 5.793L3 14.172V17h2.828l8.38-8.379-2.83-2.828z"/></svg>
                    Ubah Data
                </x-button>
                <x-button variant="hapus" id="btn-hapus-demo">
                    <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true"><path fill-rule="evenodd" d="M9 2a1 1 0 00-.894.553L7.382 4H4a1 1 0 000 2v10a2 2 0 002 2h8a2 2 0 002-2V6a1 1 0 100-2h-3.382l-.724-1.447A1 1 0 0011 2H9zM7 8a1 1 0 012 0v6a1 1 0 11-2 0V8zm5-1a1 1 0 00-1 1v6a1 1 0 102 0V8a1 1 0 00-1-1z" clip-rule="evenodd"/></svg>
                    Hapus
                </x-button>
                <x-button variant="outline" id="btn-outline-demo">Outline Navy</x-button>
                <x-button variant="ghost" id="btn-ghost-demo">Ghost</x-button>
            </div>

            <p class="text-muted text-sm mb-4">Ukuran: sm dan lg</p>
            <div class="flex flex-wrap gap-3 mb-6">
                <x-button variant="primary" size="sm" id="btn-sm-demo">Kecil (sm)</x-button>
                <x-button variant="tambah" size="md" id="btn-md-demo">Sedang (md)</x-button>
                <x-button variant="primary" size="lg" id="btn-lg-demo">Besar (lg)</x-button>
            </div>

            <p class="text-muted text-sm mb-4">Status: disabled</p>
            <div class="flex flex-wrap gap-3">
                <x-button variant="primary" :disabled="true" id="btn-disabled-primary">Utama (disabled)</x-button>
                <x-button variant="tambah" :disabled="true" id="btn-disabled-tambah">Tambah (disabled)</x-button>
                <x-button variant="hapus" :disabled="true" id="btn-disabled-hapus">Hapus (disabled)</x-button>
            </div>
        </div>
    </section>

    {{-- ===== BADGE STATUS ===== --}}
    <section aria-labelledby="sec-badge" class="mb-12">
        <h2 id="sec-badge" class="section-title mb-6" style="display:flex;align-items:center;gap:.5rem;">
            <span style="display:inline-block;width:4px;height:20px;background:var(--color-orange-500);border-radius:2px;"></span>
            Badge Status Penilaian
        </h2>

        <div class="card">
            <div class="flex flex-wrap gap-4 items-center">
                <div class="text-center">
                    <x-badge-status status="belum" id="badge-belum-demo"/>
                    <p class="text-xs text-muted mt-2">belum</p>
                </div>
                <div class="text-center">
                    <x-badge-status status="draft" id="badge-draft-demo"/>
                    <p class="text-xs text-muted mt-2">draft</p>
                </div>
                <div class="text-center">
                    <x-badge-status status="final" id="badge-final-demo"/>
                    <p class="text-xs text-muted mt-2">final</p>
                </div>
                <div class="text-center">
                    <x-badge-status status="direvisi" id="badge-direvisi-demo"/>
                    <p class="text-xs text-muted mt-2">direvisi</p>
                </div>
            </div>
        </div>
    </section>

    {{-- ===== FORM ===== --}}
    <section aria-labelledby="sec-form" class="mb-12">
        <h2 id="sec-form" class="section-title mb-6" style="display:flex;align-items:center;gap:.5rem;">
            <span style="display:inline-block;width:4px;height:20px;background:var(--color-orange-500);border-radius:2px;"></span>
            Elemen Form
        </h2>

        <div class="card">
            <div style="display:grid; grid-template-columns: 1fr 1fr; gap: 0 2rem;">
                <div>
                    <x-input label="Nama Lengkap" name="demo_nama" id="demo-nama" placeholder="cth. Siti Rahayu, S.Pd." :required="true"/>
                    <x-input label="Username" name="demo_username" id="demo-username" hint="Unik per sekolah, tidak bisa diubah" placeholder="guru.rahma"/>
                    <x-input label="NIP" name="demo_nip" id="demo-nip" error="NIP harus 18 digit angka." placeholder="198812312015041001" value="abc"/>
                </div>
                <div>
                    <x-select
                        label="Peran"
                        name="demo_role"
                        id="demo-role"
                        :options="['guru'=>'Guru','supervisor'=>'Supervisor','admin'=>'Admin']"
                        placeholder="Pilih peran..."
                        :required="true"/>
                    <x-textarea label="Catatan" name="demo_catatan" id="demo-catatan" placeholder="Tulis catatan supervisi..." rows="4"/>
                    <x-input label="Dengan galat" name="demo_err" id="demo-err" error="Bidang ini wajib diisi." value=""/>
                </div>
            </div>
        </div>
    </section>

    {{-- ===== TABEL ===== --}}
    <section aria-labelledby="sec-tabel" class="mb-12">
        <h2 id="sec-tabel" class="section-title mb-6" style="display:flex;align-items:center;gap:.5rem;">
            <span style="display:inline-block;width:4px;height:20px;background:var(--color-orange-500);border-radius:2px;"></span>
            Tabel (responsif)
        </h2>

        <div class="card" padding="false">
            <x-table id="demo-tabel" :headers="['Nama Guru', 'Penilai', 'Status KBM', 'Aksi']">
                @foreach([
                    ['Budi Santoso, S.Pd.', 'Hj. Aminah, M.Pd.', 'final'],
                    ['Rina Wulandari, S.Pd.', 'Hj. Aminah, M.Pd.', 'draft'],
                    ['Tono Prasetyo, S.Kom.', '-', 'belum'],
                    ['Dewi Lestari, S.Pd.', 'Pak Yusuf, S.Pd.', 'direvisi'],
                ] as $row)
                <tr>
                    <td class="font-medium">{{ $row[0] }}</td>
                    <td style="color: var(--color-muted);">{{ $row[1] }}</td>
                    <td><x-badge-status :status="$row[2]"/></td>
                    <td>
                        <div class="flex gap-2">
                            <x-button variant="edit" size="sm">Ubah</x-button>
                            <x-button variant="hapus" size="sm">Hapus</x-button>
                        </div>
                    </td>
                </tr>
                {{-- Mobile kartu --}}
                @endforeach
            </x-table>
        </div>
    </section>

    {{-- ===== ALERT BANNER ===== --}}
    <section aria-labelledby="sec-alert" class="mb-12">
        <h2 id="sec-alert" class="section-title mb-6" style="display:flex;align-items:center;gap:.5rem;">
            <span style="display:inline-block;width:4px;height:20px;background:var(--color-orange-500);border-radius:2px;"></span>
            Banner Pesan
        </h2>

        <x-alert-banner type="sukses" id="banner-sukses-demo">Data guru berhasil disimpan.</x-alert-banner>
        <x-alert-banner type="galat" title="Terjadi kesalahan" id="banner-galat-demo">Tidak dapat menyimpan data. Periksa kembali formulir di bawah.</x-alert-banner>
        <x-alert-banner type="peringatan" id="banner-peringatan-demo">Masih ada guru yang belum memiliki penilai pada periode ini.</x-alert-banner>
        <x-alert-banner type="info" id="banner-info-demo">Periode ini sudah ditutup. Hubungi admin untuk membuka kembali.</x-alert-banner>
    </section>

    {{-- ===== EMPTY STATE ===== --}}
    <section aria-labelledby="sec-empty" class="mb-12">
        <h2 id="sec-empty" class="section-title mb-6" style="display:flex;align-items:center;gap:.5rem;">
            <span style="display:inline-block;width:4px;height:20px;background:var(--color-orange-500);border-radius:2px;"></span>
            Empty State
        </h2>

        <div class="card">
            <x-empty-state
                title="Belum ada data guru"
                message="Tambahkan guru baru atau impor dari file Excel untuk memulai."
            >
                <x-slot:actions>
                    <x-button variant="tambah" id="empty-state-cta">
                        <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true"><path fill-rule="evenodd" d="M10 3a1 1 0 011 1v5h5a1 1 0 110 2h-5v5a1 1 0 11-2 0v-5H4a1 1 0 110-2h5V4a1 1 0 011-1z" clip-rule="evenodd"/></svg>
                        Tambah Guru
                    </x-button>
                </x-slot:actions>
            </x-empty-state>
        </div>
    </section>

    {{-- ===== MODAL KONFIRMASI ===== --}}
    <section aria-labelledby="sec-modal" class="mb-12">
        <h2 id="sec-modal" class="section-title mb-6" style="display:flex;align-items:center;gap:.5rem;">
            <span style="display:inline-block;width:4px;height:20px;background:var(--color-orange-500);border-radius:2px;"></span>
            Modal Konfirmasi
        </h2>

        <div class="card">
            <div class="flex flex-wrap gap-3">
                <x-button
                    variant="hapus"
                    id="buka-modal-hapus-btn"
                    @click="$dispatch('buka-modal-modal-hapus-demo')"
                >
                    Demo Modal Hapus
                </x-button>

                <x-button
                    variant="primary"
                    id="buka-modal-finalisasi-btn"
                    @click="$dispatch('buka-modal-modal-finalisasi-demo')"
                >
                    Demo Modal Finalisasi
                </x-button>
            </div>
        </div>

        {{-- Modal hapus --}}
        <x-modal-konfirmasi
            id="modal-hapus-demo"
            title="Hapus Data Guru"
            pesan="Apakah Anda yakin ingin menghapus guru ini? Tindakan ini tidak dapat dibatalkan dan semua data penilaian terkait akan ikut terhapus."
            label-konfirm="Ya, Hapus Data"
            variant-konfirm="hapus"
        />

        {{-- Modal finalisasi --}}
        <x-modal-konfirmasi
            id="modal-finalisasi-demo"
            title="Finalisasi Penilaian"
            pesan="Setelah difinalisasi, nilai tidak dapat diubah kecuali admin membuka kunci. Pastikan semua butir penilaian sudah terisi dengan benar."
            label-konfirm="Ya, Finalisasi Sekarang"
            variant-konfirm="primary"
        />
    </section>

    {{-- ===== CARD & PAGE HEADER ===== --}}
    <section aria-labelledby="sec-card" class="mb-12">
        <h2 id="sec-card" class="section-title mb-6" style="display:flex;align-items:center;gap:.5rem;">
            <span style="display:inline-block;width:4px;height:20px;background:var(--color-orange-500);border-radius:2px;"></span>
            Kartu & Page Header
        </h2>

        <x-page-header title="Daftar Guru Periode 2024/2025" subtitle="SMA Negeri 1 Contoh — 42 guru terdaftar">
            <x-slot:actions>
                <x-button variant="outline" id="pg-import-btn">Impor Excel</x-button>
                <x-button variant="tambah" id="pg-tambah-btn">
                    <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true"><path fill-rule="evenodd" d="M10 3a1 1 0 011 1v5h5a1 1 0 110 2h-5v5a1 1 0 11-2 0v-5H4a1 1 0 110-2h5V4a1 1 0 011-1z" clip-rule="evenodd"/></svg>
                    Tambah Guru
                </x-button>
            </x-slot:actions>
        </x-page-header>

        <x-card title="Riwayat Penilaian">
            <x-slot:actions>
                <x-button variant="outline" size="sm" id="card-ekspor-btn">Ekspor PDF</x-button>
            </x-slot:actions>
            <p class="text-muted">Konten kartu dengan judul dan tombol di header.</p>
        </x-card>
    </section>

    {{-- ===== NAV ITEM ===== --}}
    <section aria-labelledby="sec-nav" class="mb-12">
        <h2 id="sec-nav" class="section-title mb-6" style="display:flex;align-items:center;gap:.5rem;">
            <span style="display:inline-block;width:4px;height:20px;background:var(--color-orange-500);border-radius:2px;"></span>
            Nav Item (Sidebar)
        </h2>
        <div class="card" style="max-width: 260px; padding: 0.5rem;">
            <x-nav-item href="#" :active="true" id="nav-active-demo">Beranda</x-nav-item>
            <x-nav-item href="#" id="nav-inactive-demo">Daftar Guru</x-nav-item>
            <x-nav-item href="#" id="nav-periode-demo">Periode</x-nav-item>
            <x-nav-item href="#" id="nav-laporan-demo">Laporan</x-nav-item>
        </div>
    </section>

</div>

</body>
</html>
