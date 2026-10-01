# Desain Aplikasi Supervisi Guru (Laravel + MySQL, Multitenant)

Dokumen ini merangkum keputusan Anda dan rekomendasi desain. Belum ada kode yang dijalankan atau diuji; ini rancangan untuk dijadikan acuan migrasi, model, dan policy.

---

## 1. Keputusan arsitektur

### 1.1 Multitenancy: satu database, kolom `sekolah_id`

| Opsi | Kelebihan | Kekurangan |
| --- | --- | --- |
| **Shared schema + `sekolah_id` (rekomendasi)** | Satu migrasi, deploy sederhana, super-admin mudah membuat laporan lintas sekolah | Data bocor lintas sekolah jika ada query yang lupa scope |
| Database per sekolah (mis. `stancl/tenancy`) | Isolasi kuat | Migrasi dijalankan N kali, backup/monitoring rumit, berlebihan untuk skala sekolah |

Alasan memilih shared schema: jumlah data per sekolah kecil (ratusan guru, beberapa periode), dan kebutuhan isolasi bisa dipenuhi dengan scope + policy + test. \[Medium confidence: bergantung pada skala sekolah yang Anda targetkan dan ada tidaknya syarat regulasi isolasi data.\]

**Pengaman tenant (wajib dibangun sejak awal, bukan belakangan):**

*Lapis 1 — filter per sekolah yang tertutup secara bawaan (fail-closed)*

- Satu `TenantContext` menyimpan sekolah yang sedang dilayani. Untuk halaman login, konteks diisi dari kode sekolah di URL; setelah login, dari `sekolah_id` user. Job queue dan command mengisinya secara eksplisit.
- Trait `BelongsToSekolah` dipasang di semua model bertenant. Global scope membaca `TenantContext`. **Bila konteks kosong, scope melempar exception**, bukan melewatkan filter. Hook `creating` mengisi `sekolah_id` dari konteks.
- Bypass hanya lewat satu method bernama jelas (`tanpaTenant()`), dan hanya boleh dipanggil dari namespace super-admin.

*Lapis 2 — izin*

- Base Policy memeriksa `sekolah_id` sama sebelum aturan role.
- Route model binding melewati global scope, jadi ID milik sekolah lain menghasilkan **404**.

*Lapis 3 — database*

- Semua unique constraint komposit dengan `sekolah_id`.
- FK komposit `(id, sekolah_id)` untuk relasi penting (`penugasan` → `users`/`periode`, `penilaian` → `penugasan`, dst.), sehingga relasi lintas sekolah ditolak database.
- MySQL tidak punya row-level security bawaan, jadi pembacaan data tidak bisa dipaksa di level database. \[High confidence\]

*Lapis 4 — pagar otomatis di CI*

- **Arch test** yang menggagalkan build bila `withoutGlobalScopes`, `DB::table`, `DB::select` atau `DB::raw` dipakai di luar daftar yang diizinkan. Query builder dan join mentah tidak terkena global scope Eloquent.
- **Test skema:** setiap tabel yang punya kolom `sekolah_id` harus dipakai oleh model dengan trait tenant.
- **Test lintas-tenant otomatis:** ulangi semua route ber-parameter, termasuk unduhan Excel dan PDF, memakai user sekolah lain. Hasil wajib 404.
- **Test job:** job yang dijalankan tanpa `sekolah_id` harus gagal.

*Lapis 5 — di luar database*

- File (PDF, hasil impor) di storage privat, folder per sekolah, diakses lewat controller yang memeriksa izin. Tidak ada tautan publik.
- Key cache diprefiks `sekolah_id`.
- Laporan dan export dibuat dari model Eloquent, bukan query mentah.
- Jangan menulis password atau nilai penilaian ke log.

*Lapis 6 — ULID sebagai route key (opsional, untuk resource sensitif)*

- Primary key tetap auto-increment. Resource sensitif (`penilaian`, `surat_tugas`, `import_batches`) memiliki kolom `ulid` unik yang dipakai di URL. Ini hanya penghalang tambahan agar ID tidak bisa ditebak, bukan pengganti lapis 1-4.

**Batasan:** tidak ada jaminan nol kebocoran selama manusia menulis kode. Lapis 4 yang menangkap kesalahan sebelum sampai ke pengguna.

### 1.2 Super-admin

- Disimpan di tabel `users` dengan `is_super_admin = true` dan `sekolah_id = NULL`.
- Login terpisah (`/super-admin/login`, memakai email).
- Fitur: tambah/ubah/nonaktifkan sekolah; buat akun **admin untuk sekolah tersebut** (satu atau lebih) dengan username dan password awal; atur ulang password admin; nonaktifkan admin; lihat statistik ringkas lintas sekolah.
- Alur pendaftaran: super-admin mengisi data sekolah → menambahkan akun admin → admin login di `/s/{kode}/login` dan wajib mengganti password.
- Admin tambahan hanya dibuat oleh super-admin (default, perlu konfirmasi).
- Sekolah harus selalu punya minimal satu admin aktif: sistem menolak menonaktifkan admin aktif terakhir. Jika admin lupa password, hanya super-admin yang dapat mengaturnya ulang, karena tidak ada admin lain yang bisa menolong.
- Sekolah berstatus `nonaktif` → semua user sekolah itu ditolak saat login (middleware).

### 1.3 Login pengguna sekolah

- Setiap sekolah punya alamat login sendiri: **`/s/{kode}/login`**. Form hanya berisi **username + password**. Alamat ini dicetak di surat tugas dan lembar kredensial, sehingga pengguna tidak perlu mengingat kode sekolah.
- Setelah login, sistem memastikan `sekolah_id` user sama dengan sekolah pada URL; bila tidak, login ditolak.
- `username` unik per sekolah. Alasan: guru honorer sering tidak punya email dan NIP bisa kosong. Guru yang mengajar di dua sekolah memiliki dua akun terpisah.
- Primary key semua tabel: `bigIncrements` (sesuai keputusan Anda).
- Password awal diisi per pengguna dari file impor (di-hash saat disimpan). Kolom `must_change_password` memaksa ganti password saat login pertama.
- Percobaan login dibatasi (rate limit per kombinasi sekolah + username + IP).
- Pengguna yang berpindah atau tidak aktif **dinonaktifkan** (`aktif = false`), tidak dihapus, agar riwayat supervisi tetap utuh.

---

## 2. Role dan hak akses

Hanya ada tiga role sekolah. Posisi kepala sekolah adalah **supervisor** (tidak ada role wakil atau kepala sekolah tersendiri).

| Role | Keterangan |
| --- | --- |
| `admin` | Admin sekolah (dibuat oleh super-admin) |
| `supervisor` | Penilai. **Dipilih lewat dasbor admin**, tidak diimpor dari Excel. Menambahkan role ini **tidak menghapus** role guru |
| `guru` | Yang disupervisi. Semua akun hasil impor otomatis berperan guru; admin dapat mencabut peran ini bagi yang tidak disupervisi (mis. kepala sekolah) |

**Supervisor juga berlaku sebagai guru biasa:** seorang supervisor tetap disupervisi seperti guru lain (oleh supervisor lain atau kepala sekolah), dapat mengisi informasi supervisinya sendiri, dan melihat rapornya sendiri. Role bersifat menambah, bukan menggantikan. Admin sekolah juga boleh merangkap guru/supervisor bila ia memang mengajar.

Role disimpan di tabel `user_roles` (satu user boleh banyak role). **Jangan** pakai kolom enum tunggal di `users`, karena supervisor juga seorang guru yang disupervisi (oleh kepala sekolah). UI memakai *role switcher* karena satu orang bisa membuka dasbor sebagai guru dan sebagai supervisor.

| Aksi | super-admin | admin | supervisor | guru |
| --- | --- | --- | --- | --- |
| Kelola sekolah | ✔ |  |  |  |
| CRUD guru, import Excel |  | ✔ |  |  |
| Pilih/cabut role supervisor dan guru |  | ✔ |  |  |
| CRUD periode |  | ✔ |  |  |
| Penugasan, surat tugas (DOCX) |  | ✔ |  |  |
| Rekap, laporan, daftar "belum punya penilai" |  | ✔ |  |  |
| Lihat guru yang ditugaskan |  |  | ✔ (miliknya) |  |
| Isi informasi kelas/mapel/CP/jadwal tiap periode |  |  |  | ✔ (dirinya) |
| Nilai (draft/direvisi) dan finalisasi |  |  | ✔ (jika penilainya) |  |
| Buka kunci penilaian final (alasan wajib) |  | ✔ |  |  |
| Cetak hasil penilaian |  | ✔ | ✔ (miliknya) | ✔ (final, dirinya) |
| Rapor, riwayat, timeline |  |  |  | ✔ (dirinya) |

Otorisasi lewat Laravel Policy di sisi server. Menyembunyikan menu saja tidak cukup.

---

## 3. Skema database (MySQL 8)

Semua tabel bertenant memiliki `sekolah_id` (FK ke `sekolah`, indexed) dan `timestamps`. Tidak diulang di bawah kecuali penting.

### 3.1 Tenant dan pengguna

```
sekolah
  id, nama, npsn (null, unique), kode (unique), alamat, logo_path,
  kepala_sekolah_id (null, FK users; diisi admin sekolah), status enum(aktif,nonaktif)

users
  id, sekolah_id (null untuk super-admin), nama,
  username, email (null), password, nip (null), nuptk (null),
  must_change_password bool default true, aktif bool, is_super_admin bool
  unique(sekolah_id, username)
  catatan: super-admin login via email; MySQL mengizinkan banyak NULL pada unique,
           jadi pastikan username tidak dipakai untuk super-admin.

user_roles
  id, sekolah_id, user_id, role enum(admin,supervisor,guru)
  unique(user_id, role)

import_batches
  id, sekolah_id, user_id, nama_file, total, sukses, gagal, errors json, status
```

### 3.2 Periode dan penugasan

```
periode
  id, sekolah_id, nama, tahun_ajaran, semester (null),
  tanggal_mulai, tanggal_selesai, status enum(draft,aktif,ditutup)

penugasan
  id, sekolah_id, periode_id, guru_id (subjek, FK users),
  penilai_id (FK users, harus berperan supervisor)
  unique(periode_id, guru_id)            -- satu penilai per guru per periode
  CHECK (guru_id <> penilai_id)          -- MySQL >= 8.0.16

info_supervisi            -- diisi guru ULANG pada setiap periode (melekat ke penugasan)
  id, sekolah_id, penugasan_id (unique), kelas, semester, fase,
  mata_pelajaran, elemen, cp (text), catatan

jadwal_supervisi
  id, sekolah_id, penugasan_id, jenis_instrumen_id (null = jadwal umum),
  tanggal, jam_mulai, jam_selesai
```

Guru mengisi ulang kelas, mata pelajaran, CP, dan jadwal pada tiap periode. Tidak ada data yang disalin otomatis dari periode sebelumnya.

**Pengunci informasi (default, perlu konfirmasi):** guru dapat mengubah informasi dan jadwal selama keempat penilaian masih `belum`. Setelah salah satu penilaian dimulai, informasi dikunci bagi guru; admin dan penilai tetap dapat mengubahnya. Tujuannya agar hasil cetak tidak berbeda dari data yang dinilai.

### 3.3 Instrumen (dinamis, berversi)

Usulan Anda (`jenis_instrumen` → `butir_instrumen`) saya pertahankan, dengan dua tambahan: **versi** dan **bagian**.

```
jenis_instrumen
  id, sekolah_id (null = template global milik super-admin),
  kode (kbm, administrasi, pengelolaan_kelas, perencanaan), nama, urutan, aktif

versi_instrumen
  id, jenis_instrumen_id, nomor_versi, status enum(draft,terbit),
  skor_maks_butir default 4,
  ambang_predikat json   -- mis. {"A":86,"B":76,"C":56}, sisanya D

bagian_instrumen
  id, versi_instrumen_id, kode, judul, urutan, kategori (null; mis. wajib/penunjang)

butir_instrumen
  id, bagian_id, urutan, uraian (text), skor_maks default 4
```

Aturan:

- Versi berstatus `terbit` **tidak boleh diubah** setelah dipakai satu penilaian. Perubahan = salin jadi versi baru (draft) lalu diterbitkan. Dengan begitu penilaian lama tidak berubah dan snapshot teks butir tidak perlu disimpan di tabel nilai.
- Skor maksimal **selalu dihitung** dari butir (`SUM(skor_maks)`), bukan diketik manual. Label "30 x 4" yang salah pada Excel lama adalah akibat angka manual.
- Template global dari super-admin bisa disalin sekolah untuk disesuaikan (`sekolah_id` terisi).
- Isi butir untuk Kurikulum Merdeka harus Anda sediakan. Saya tidak mengarang instrumen resmi. Sediakan fitur import butir via Excel atau CRUD admin.

### 3.4 Penilaian

```
penilaian
  id, ulid (route key, unik), sekolah_id, penugasan_id, jenis_instrumen_id,
  versi_instrumen_id (null sampai mulai dinilai),
  status enum(belum,draft,final,direvisi) default belum,
  total_skor, skor_maks, nilai decimal(5,2), predikat,
  catatan (text), tindak_lanjut (text),
  jumlah_buka_kunci int default 0,
  finalized_at, finalized_by, diperbarui_oleh
  unique(penugasan_id, jenis_instrumen_id)

penilaian_butir
  id, penilaian_id, butir_instrumen_id, skor tinyint, catatan (null)
  unique(penilaian_id, butir_instrumen_id)

audit_log
  id, sekolah_id, penilaian_id (null), user_id,
  aksi enum(mulai,simpan_butir,finalisasi,buka_kunci,ubah_catatan),
  sebelum json, sesudah json, alasan (null), ip, created_at
```

Saat penugasan dibuat, otomatis dibuat **4 baris `penilaian`** berstatus `belum`. Hasilnya: timeline guru, progres supervisor, dan rekap admin cukup membaca satu tabel.

### 3.5 Surat tugas

```
surat_tugas
  id, ulid (route key, unik), sekolah_id, periode_id, penilai_id,
  nomor_surat, tanggal_surat,
  penandatangan_nama, penandatangan_nip, penandatangan_jabatan,
  daftar_guru json,       -- snapshot guru saat surat diterbitkan
  diterbitkan_at
  unique(periode_id, penilai_id)
```

- Nomor dan tanggal disimpan agar cetak ulang tidak berubah.
- Penandatangan diambil dari `sekolah.kepala_sekolah_id` dan disalin ke kolom penandatangan saat surat dibuat (dapat diubah admin sebelum diunduh).
- Surat tugas diunduh dalam format **DOCX**, dibuat dari template saat diminta. File tidak disimpan; yang disimpan hanya data dan snapshot-nya.
- Jika penugasan berubah setelah surat terbit, bandingkan snapshot dengan data saat ini dan tampilkan peringatan "perlu diterbitkan ulang".

---

## 4. Skema penilai berlapis (supervisor disupervisi kepala sekolah)

Tidak perlu tabel khusus. Semua dipetakan lewat `penugasan` karena subjek bisa siapa saja yang berperan guru, termasuk supervisor yang juga berperan guru. Kepala sekolah adalah supervisor biasa.

**Aturan:**

1. `guru_id` (subjek) harus berperan `guru`.
2. `penilai_id` harus berperan `supervisor`.
3. `penilai_id <> guru_id` (constraint DB + validasi aplikasi).
4. Penandatangan surat tugas diambil dari `sekolah.kepala_sekolah_id`, apa pun penilainya.
5. Saling menilai (A menilai B dan B menilai A pada periode sama) **diizinkan dengan peringatan** di UI.
6. Satu periode aktif pada satu waktu per sekolah (rekomendasi), agar "timeline supervisi berjalan" bagi guru tidak ambigu.
7. **Daftar "belum punya penilai":** admin melihat semua pengguna aktif berperan guru yang belum punya penugasan pada periode terpilih. Sistem memberi peringatan sebelum periode diaktifkan jika daftar ini tidak kosong.
8. Pengguna nonaktif tidak muncul di daftar itu dan tidak bisa ditugaskan baru, tetapi penugasan lamanya tetap tersimpan.

**Belum terjawab:** siapa yang menilai kepala sekolah? Opsi paling sederhana: pengawas dibuatkan akun di sekolah itu dengan role `supervisor`, lalu kepala sekolah berperan guru.

---

## 5. Status penilaian dan audit log

```
belum ──simpan pertama──▶ draft ──finalisasi──▶ final (terkunci)
                                                   │
                           direvisi ◀── buka kunci (admin, alasan wajib)
                               │
                               └─────────finalisasi────▶ final
```

**Aturan (keputusan Anda):**

- **Edit hanya saat `draft` atau `direvisi`.** Pada `final`, supervisor tidak dapat mengubah apa pun. Penolakan dilakukan di server, bukan sekadar menyembunyikan tombol.
- **Finalisasi** hanya bila semua butir terisi. Tombolnya memakai dialog konfirmasi tegas ("Setelah difinalisasi, nilai tidak dapat diubah"). Sistem menghitung total, nilai, dan predikat, lalu mengunci.
- **Buka kunci:** hanya **admin**, alasan wajib, tercatat di `audit_log`. Status menjadi `direvisi`, `jumlah_buka_kunci` bertambah.
- Periode `ditutup` mengunci semua penilaian. Untuk buka kunci pada periode tertutup, admin harus membuka periodenya dulu.
- **Selesai** (pengganti istilah "tuntas") = keempat penilaian pada penugasan berstatus `final`. Penilaian yang sedang `direvisi` dihitung belum final.

**Timeline dan rapor guru** menampilkan status, bukan nilai, kecuali setelah final:

| Status internal | Label untuk guru | Nilai tampil? |
| --- | --- | --- |
| `belum` | Belum dinilai | Tidak |
| `draft` | Sedang dinilai | Tidak |
| `final` | Selesai | Ya (nilai, predikat, catatan) |
| `direvisi` | Sedang direvisi | Tidak (hanya label) |

Ringkasan per periode untuk guru dan admin: **Selesai (4/4)** atau **Belum selesai (n/4)**, dengan n = jumlah penilaian berstatus final.

**Tidak termasuk (belum diputuskan):** alur permintaan buka kunci dari supervisor ke admin di dalam aplikasi. Saat ini supervisor harus menghubungi admin di luar aplikasi.

**Audit log:**

- Wajib rinci (siapa, kapan, sebelum, sesudah, alasan) untuk `finalisasi` dan `buka_kunci`.
- Perubahan saat draft/direvisi cukup dicatat sebagai `diperbarui_oleh` dan `updated_at` pada `penilaian`.

---

## 6. Import Excel guru

Template unduhan (sheet `Data Guru`). **Tidak ada kolom role**: semua akun hasil impor berperan `guru`, dan peran `supervisor` dipilih admin lewat dasbor.

| Kolom | Wajib | Keterangan |
| --- | --- | --- |
| `nama` | ✔ |  |
| `username` | ✔ | Unik per sekolah; dipakai sebagai kunci upsert |
| `email` |  | Opsional |
| `nip` |  | Boleh kosong atau "-" |
| `nuptk` |  |  |
| `password` | ✔ untuk akun baru | Diabaikan untuk akun yang sudah ada |

Alur: **unggah → validasi (dry run) → pratinjau + error per baris → konfirmasi → proses**.

**Aturan impor ulang (default, perlu konfirmasi):**

- Akun yang sudah ada hanya diperbarui datanya (nama, email, NIP, NUPTK). **Password dan role tidak pernah ditimpa**, supaya impor ulang tidak mengembalikan password yang sudah diganti pengguna atau menghapus pilihan supervisor.
- Kolom password pada baris akun lama dilaporkan sebagai "dilewati". Reset password dilakukan lewat aksi admin yang terpisah.

Catatan keamanan untuk kolom password:

- File Excel berisi password mentah itu sensitif. Hash dengan `Hash::make` saat impor, jangan tulis nilainya ke log atau `import_batches.errors`.
- Simpan file unggahan di storage sementara, hapus setelah diproses.
- Set `must_change_password = true` untuk semua akun baru.
- Tolak password awal yang kosong atau terlalu pendek saat validasi impor (batas minimal ditentukan Anda), dan terapkan rate limit login (lihat §1.3). Password awal yang seragam dan mudah ditebak membuat akun bisa diambil alih sebelum login pertama.

---

## 7. Paket yang dipakai

| Kebutuhan | Paket |
| --- | --- |
| Import dan export Excel, template, draft laporan | `maatwebsite/excel` |
| Surat tugas **DOCX** dari template (placeholder + baris daftar guru) | `phpoffice/phpword` (TemplateProcessor) |
| Cetak hasil penilaian (PDF) | `barryvdh/laravel-dompdf` (jika layout rumit, pertimbangkan Browsershot) |

Pastikan versi paket cocok dengan versi PHP dan Laravel proyek Anda sebelum instalasi. \[Medium confidence: saya tidak memverifikasi kompatibilitas karena belum tahu versi yang dipakai.\]

Audit log dibuat sendiri (bukan paket generik) karena event-nya bersifat domain: finalisasi dan buka kunci.

---

## 8. Laporan

- **Supervisor:** daftar guru + progres (x/4) + unduh Excel per periode.
- **Admin:** rekap seluruh guru; filter **Selesai / Belum selesai**; daftar **Belum punya penilai**; unduh Excel; unduh surat tugas DOCX per penilai.
- **Cetak hasil penilaian:** PDF yang meniru formulir (identitas, butir, skor, predikat, catatan, tanda tangan).
- Format Excel final menyusul. Siapkan kelas export terpisah (`RekapPeriodeExport`, `LaporanKetuntasanExport`) dengan kolom draf: nama guru, penilai, nilai tiap instrumen, rata-rata, predikat, status.

---

## 9. Urutan pengerjaan yang disarankan

1. Tenant, auth, role, `TenantContext` fail-closed, trait scope, **pagar otomatis (arch test + test lintas-tenant) sebelum fitur lain**.
2. Super-admin: CRUD sekolah dan akun admin sekolah (termasuk atur ulang password admin).
3. Import guru, pemilihan supervisor di dasbor admin, periode.
4. Penugasan, daftar "belum punya penilai", surat tugas DOCX.
5. Instrumen berversi (admin) + seeder 4 jenis.
6. Info/jadwal guru dan dasbor timeline guru.
7. Form penilaian (draft/final), audit log, buka kunci oleh admin.
8. Cetak PDF hasil penilaian, rekap, export Excel.

---

## 10. Keputusan yang masih terbuka

| Keputusan | Rekomendasi / default |
| --- | --- |
| Siapa menilai kepala sekolah | Pengawas dengan akun `supervisor` di sekolah itu |
| Periode aktif bersamaan | Satu aktif per sekolah |
| Instrumen global vs per sekolah | Template global + salin per sekolah |
| Impor ulang dan password/role akun lama | Tidak ditimpa (lihat §6) |
| Pengunci informasi guru setelah penilaian dimulai | Terkunci bagi guru (lihat §3.2) |
| Permintaan buka kunci di dalam aplikasi | Belum termasuk |
| Jadwal: satu per supervisi atau per instrumen | Per instrumen, diisi guru |
| Template surat tugas | Perlu contoh surat; opsi unggah template per sekolah |
| Pendaftaran sekolah dan akun admin | Hanya lewat super-admin (sesuai keputusan Anda) |
| Admin tambahan sekolah | Hanya dibuat super-admin |