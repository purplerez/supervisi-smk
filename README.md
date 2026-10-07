# Aplikasi Supervisi Guru (Multitenant)

Aplikasi web multitenant untuk manajemen, pelaksanaan, dan pelaporan supervisi akademik guru sekolah menengah kejuruan (SMK) dan sederajat. Dibangun dengan fokus pada kemudahan penggunaan bagi guru dan supervisor (ramah usia 35+), keamanan isolasi data antarsekolah yang ketat (*fail-closed multitenancy*), serta kepatuhan standar audit.

---

## 1. Stack Teknologi

- **Backend**: Laravel 11.x, PHP 8.4+, MySQL 8.0.16+ (memanfaatkan `CHECK constraint` dan indeks komposit).
- **Frontend**: Blade, Livewire 3, Alpine.js, Tailwind CSS (seluruh font dan skrip dibundle lokal via Vite, **tanpa ketergantungan CDN luar**).
- **Desain UI**: Tema **HANYA TERANG** (*no dark mode*), palet warna ramah mata (Navy `#253C6D`, Krem `#FFFAF3`, Putih, aksen Oranye `#F2842F`, teks kontras >= 4.5:1, target klik minimal 44x44px, teks minimal 14px).
- **Ekspor Dokumen**:
  - `maatwebsite/excel` (Ekspor Rekap dan Laporan Ketuntasan format Excel `.xlsx`).
  - `phpoffice/phpword` (Penerbitan Surat Tugas Supervisor dinamis format Word `.docx`).
  - `barryvdh/laravel-dompdf` (Cetak Hasil Penilaian Instrumen format PDF A4).
- **Testing & Mutu Kode**: Pest PHP (181 tests, 723 assertions, 100% passed), Laravel Pint (clean PSR-12/Laravel style).

---

## 2. Arsitektur Multitenant & Keamanan

1. **Satu Database, Banyak Sekolah**: Setiap tabel bertenant memiliki kolom `sekolah_id` dengan foreign key dan indeks komposit `(id, sekolah_id)`.
2. **Fail-Closed Global Scope**: Semua model bertenant menerapkan trait `BelongsToSekolah`. Trait ini secara otomatis menginjeksi `SekolahScope` yang memeriksa `TenantContext::getSekolahId()`. Jika konteks tenant kosong pada eksekusi query model bertenant, sistem **langsung melempar exception** (*fail-closed*).
3. **URL Route Tenant**: Seluruh fitur sekolah berada di bawah rute `/s/{kode}/...`. Middleware `SetTenantContext` dijalankan sebelum middleware autentikasi guna memastikan isolasi data aktif sejak awal *request lifecycle*.
4. **Proteksi Akses Lintas Tenant**: Base Policy dan Sweeper Test otomatis memastikan bahwa pengguna Sekolah B yang memanggil ID/ULID milik Sekolah A akan menerima respons **HTTP 404 Not Found**, bukan 403 atau 500.

---

## 3. Cara Instalasi & Persyaratan Sistem

### Persyaratan
- PHP >= 8.3 dengan ekstensi: `pdo_mysql`, `mbstring`, `zip`, `gd`, `xml`, `bcmath`.
- MySQL >= 8.0.16
- Composer >= 2.x
- Node.js >= 18.x & NPM

### Langkah Instalasi
1. Clone repositori ke mesin lokal Anda:
   ```bash
   git clone <repository_url> supervisi-app
   cd supervisi-app
   ```
2. Salin berkas lingkungan (`.env`):
   ```bash
   cp .env.example .env
   ```
3. Sesuaikan konfigurasi database pada `.env`:
   ```env
   DB_CONNECTION=mysql
   DB_HOST=127.0.0.1
   DB_PORT=3306
   DB_DATABASE=supervisi_app
   DB_USERNAME=root
   DB_PASSWORD=
   ```
4. Pasang dependensi PHP dan Node.js:
   ```bash
   composer install
   npm install
   ```
5. Generate application key & storage link:
   ```bash
   php artisan key:generate
   php artisan storage:link
   ```
6. Kompilasi aset frontend lokal:
   ```bash
   npm run build
   # atau 'npm run dev' untuk mode live reload
   ```

---

## 4. Migrasi & Seeder Demo

Jalankan perintah migrasi dan seeder:
```bash
php artisan migrate:fresh --seed
```

> **Catatan Keamanan**: `DemoSeeder` dilindungi oleh pemeriksaan `if (! app()->isLocal()) return;` sehingga data demo hanya dapat dibuat pada *environment* lokal/pengujian.

---

## 5. Akun Demo (Hanya untuk Environment Local)

Seluruh akun demo di bawah ini menggunakan kata sandi default: `password123`

### A. Super Admin (Akses Global)
- **URL Login**: `http://supervisi-app.test/super-admin/login` (atau `/super-admin/login`)
- **Username**: `superadmin` (atau email `superadmin@supervisi.test`)
- **Password**: `password123`
- **Fitur**: Kelola master data sekolah, penetapan template instrumen supervisi global.

---

### B. Sekolah 1: SMK Negeri 1 Surabaya (`smkn1-sby`)
- **URL Login**: `http://supervisi-app.test/s/smkn1-sby/login`

| Peran | Nama | Username | Keterangan Data Supervisi |
| :--- | :--- | :--- | :--- |
| **Admin Sekolah** | Admin SMKN 1 SBY | `admin_smkn1-sby` | Kelola guru, penugasan, surat tugas, buka kunci penilaian, rekap. |
| **Kepala Sekolah** | Budi Santoso, M.Pd | `kepsek_smkn1-sby` | Supervisor 1 & Penandatangan Surat Tugas. Menilai Guru 1, 2, 3. |
| **Supervisor 2** | Siti Aminah, S.Pd | `spv_smkn1-sby` | **Peran ganda**: Supervisor 2 (menilai Guru 4, 5, 6) sekaligus Guru disupervisi. |
| **Guru 1** | Ahmad Riyadi, S.Pd | `guru1_smkn1-sby` | **Status 4/4 Selesai (Final)**: Siap dicetak rapor supervisi PDF. |
| **Guru 2** | Dewi Sartika, S.Pd | `guru2_smkn1-sby` | **Status Sedang Dinilai (Draft)**: KBM tersimpan draft, lainnya belum. |
| **Guru 3** | Eko Prasetyo, S.Kom | `guru3_smkn1-sby` | **Status Sedang Direvisi**: KBM dibuka kunci oleh admin (jumlah buka: 1x). |
| **Guru 4** | Fajar Nugroho, S.T | `guru4_smkn1-sby` | **Status 0/4 (Belum Dinilai)**: Info & jadwal terisi lengkap. |
| **Guru 5** | Gita Permata, M.Pd | `guru5_smkn1-sby` | **Status Belum Dinilai**. |
| **Guru 6** | Hendra Kurnia, S.Pd | `guru6_smkn1-sby` | **Status Belum Dinilai**. |

---

### C. Sekolah 2: SMK Negeri 2 Malang (`smkn2-mlg`)
- **URL Login**: `http://supervisi-app.test/s/smkn2-mlg/login`

| Peran | Nama | Username | Keterangan |
| :--- | :--- | :--- | :--- |
| **Admin Sekolah** | Admin SMKN 2 MLG | `admin_smkn2-mlg` | Data terpisah total dari SMKN 1 Surabaya. |
| **Kepala Sekolah** | Drs. Bambang Sutrisno | `kepsek_smkn2-mlg` | Supervisor 1 SMKN 2 Malang. |
| **Supervisor 2** | Endah Rahayu, M.Pd | `spv_smkn2-mlg` | Supervisor 2 (merangkap peran guru). |
| **Guru 1 - 6** | Guru SMKN 2 MLG | `guru1_smkn2-mlg` s/d `guru6_smkn2-mlg` | Akun guru Sekolah 2 untuk pengujian isolasi tenant. |

---

## 6. Menjalankan Pengujian (Testing) & Kualitas Kode

### Menjalankan Test Suite (Pest)
```bash
php artisan test
```
- **Total Test**: 181 passing tests (723 assertions).
- **Cakupan Test**:
  1. `TenantArchTest`: Pengujian arsitektur statis untuk kepatuhan larangan `DB::table`, `DB::select`, `DB::raw`, dan kewajiban `BelongsToSekolah`.
  2. `CrossTenantSweeperTest`: Sweeper otomatis yang membaca seluruh rute berparameter dari router Laravel dan menguji akses lintas tenant. Memverifikasi juga unduhan Excel, PDF, DOCX, dan interaksi komponen Livewire. Semuanya menghasilkan **HTTP 404**.
  3. `Feature Tests`: Otentikasi, alur status penilaian (`belum` -> `draft` -> `final` -> `direvisi`), kalkulasi skor dinamis, batas buka kunci admin, ekspor laporan, dan audit log.

### Memeriksa Format Kode (Pint)
```bash
vendor/bin/pint --test
```

---

## 7. Laporan Arch Test & Dokumentasi Allowlist

Sesuai aturan `AGENTS.md`, penggunaan query mentah dan pemintasan global scope dilarang keras untuk mencegah kebocoran data lintas sekolah.

### Aturan Arsitektur yang Diterapkan:
1. **Dilarang**: Pemanggilan `DB::table`, `DB::select`, `DB::raw`, `withoutGlobalScopes` di kode aplikasi tenant.
2. **Wajib**: Setiap model yang tabelnya memiliki kolom `sekolah_id` **wajib** menggunakan trait `App\Tenant\Traits\BelongsToSekolah`.

### Entri Allowlist Resmi:
- **Lokasi Berkas**: `app/Tenant/Traits/BelongsToSekolah.php`
- **Metode**: `static::withoutGlobalScope(SekolahScope::class)` di dalam fungsi pembungkus `tanpaTenant(callable $callback)`.
- **Alasan & Batasan**:
  Ini adalah satu-satunya gerbang resmi (*official architectural boundary*) untuk mematikan scope tenant sementara waktu. Metode ini hanya boleh dipanggil dari:
  1. Namespace Super Admin (`App\SuperAdmin\*`) untuk rekap tingkat sistem / master data platform.
  2. Database Seeders (`Database\Seeders\*`) untuk seeding multi-sekolah.
  Kode dalam modul sekolah (`App\Http\Controllers\Admin\*`, `Supervisor\*`, `Guru\*`) dilarang memanggil `tanpaTenant()`.

---

## 8. Struktur Direktori Utama

```
app/
├── Exports/               # Export Excel (RekapPeriodeExport, LaporanKetuntasanExport)
├── Http/
│   ├── Controllers/
│   │   ├── Admin/         # Controller Admin (Guru, Periode, Penugasan, Surat Tugas, Buka Kunci, Rekap)
│   │   ├── Supervisor/    # Dasbor Supervisor & Detail Penugasan Bimbingan
│   │   ├── Guru/          # Dasbor Guru, Pengisian Info Supervisi & Jadwal
│   │   └── Penilaian/     # Cetak Dokumen PDF Rapor Hasil Penilaian
│   └── Middleware/        # SetTenantContext, EnsureUserBelongsToTenant, SuperAdminMiddleware
├── Livewire/              # FormPenilaian (Pengisian skor interaktif, autosave, modal finalisasi)
├── Models/                # Model Eloquent (User, Sekolah, Periode, Penugasan, Penilaian, dll)
├── Policies/              # Otorisasi di tingkat server berbasis peran dan tenant
├── Services/              # InstrumenResolver, SuratTugasDocxService
├── SuperAdmin/            # Modul Super Admin (Manajemen Sekolah & Template Instrumen Global)
└── Tenant/                # Inti Multitenancy (TenantContext, SekolahScope, BelongsToSekolah)
```

---

## 9. Asumsi & Nilai Default yang Belum Dikonfirmasi

1. **Format Surat Tugas DOCX**: Template saat ini mengacu pada tata naskah dinas pendidikan standar dengan kop nama sekolah, alamat, konsiderans penugasan, tabel guru yang disupervisi, dan blok tanda tangan kepala sekolah. Penyesuaian nomor surat/perihal lokal diserahkan pada input admin.
2. **Penguncian Form Guru**: Setelah salah satu instrumen penilaian berstatus `draft` atau `final` (proses supervisi telah dimulai oleh supervisor), form Informasi Supervisi dan Jadwal pada dasbor guru dikunci (*read-only*) guna mencegah inkonsistensi data pengamatan di lapangan.
3. **Format Rapor Penilaian PDF**: Saat ini PDF dicetak per instrumen penilaian final lengkap dengan rincian butir, skor per butir, catatan, tindak lanjut, serta kolom tanda tangan supervisor dan guru. Rapor rangkuman 4 instrumen sekaligus dalam 1 berkas dapat ditambahkan bila ada format baku dinas.

---

## 10. Daftar Hal yang Belum Dikerjakan (Roadmap / Backlog)

1. **Tanda Tangan Elektronik / QR Code Verifikasi**: Penambahan kode QR dinamis pada dokumen cetak PDF dan DOCX yang memvalidasi keaslian dokumen via URL publik terenkripsi.
2. **Notifikasi Otomatis (Email / WhatsApp Gateway)**: Pengiriman notifikasi pengingat jadwal supervisi kepada guru dan pemberitahuan pembukaan kunci revisi kepada supervisor.
3. **Kustomisasi Butir Instrumen Mandiri per Sekolah**: Saat ini sekolah mewarisi instrumen global terbitan super admin. Antarmuka untuk sekolah menduplikasi dan mengedit butir instrumen mandiri (sesuai kewenangan sekolah) dapat diaktifkan pada iterasi lanjutan.
