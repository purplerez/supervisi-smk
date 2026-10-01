# Aplikasi Supervisi Guru (multitenant): aturan tetap untuk semua agent

Rujukan lengkap: `docs/DESAIN.md` (skema, status, hak akses). Bila instruksi tugas bertentangan dengan file ini atau DESAIN.md, BERHENTI dan tanyakan. Jangan menebak.

## Stack
- Laravel versi stabil terbaru, PHP 8.3+, MySQL 8.0.16+ (CHECK constraint dipakai).
- Blade + Livewire 3 + Alpine.js + Tailwind CSS via Vite. Font dan skrip di-bundle lokal (tanpa CDN).
- Paket: maatwebsite/excel, phpoffice/phpword (surat tugas DOCX), barryvdh/laravel-dompdf (PDF hasil penilaian), Pest (test), Pint.
- UI berbahasa Indonesia. Nama tabel, kolom, model mengikuti DESAIN.md (snake_case, istilah Indonesia). Primary key `bigIncrements`.

## Domain
- Satu database, banyak sekolah. Semua tabel bertenant punya `sekolah_id`.
- Role: super-admin (flag di users), `admin`, `supervisor`, `guru` (tabel user_roles, boleh banyak per user). Kepala sekolah = supervisor. Supervisor juga bisa berperan guru.
- Alur: periode -> penugasan (guru_id, penilai_id; unik per periode+guru) -> 4 penilaian per penugasan: kbm, administrasi, pengelolaan_kelas, perencanaan.
- Status penilaian: belum -> draft -> final; `direvisi` setelah admin membuka kunci. Edit hanya saat draft/direvisi. Final terkunci (tolak di SERVER). Hanya admin boleh buka kunci, alasan wajib, tercatat di audit_log. "Selesai" = 4 penilaian final.
- Guru hanya melihat nilai dari penilaian final. Status lain tampil sebagai label: Belum dinilai / Sedang dinilai / Sedang direvisi.
- Instrumen berversi. Versi yang sudah dipakai tidak boleh diubah. Skor maksimal selalu dihitung dari butir, jangan hardcode.
- Penilai tidak boleh sama dengan guru yang dinilai. Saling menilai boleh dengan peringatan.

## Aturan tenant (tidak boleh dilanggar)
1. Semua model bertenant memakai trait `BelongsToSekolah`: global scope membaca `TenantContext`. Bila konteks kosong, LEMPAR exception (fail-closed). Hook `creating` mengisi `sekolah_id`.
2. Bypass hanya lewat `tanpaTenant()` dan hanya dari namespace super-admin.
3. Dilarang `DB::table`, `DB::select`, `DB::raw`, `withoutGlobalScopes` di luar daftar yang diizinkan arch test.
4. Base Policy memeriksa `sekolah_id` sama sebelum aturan role. ID sekolah lain menghasilkan 404.
5. Unique selalu komposit dengan `sekolah_id`. FK komposit `(id, sekolah_id)` untuk relasi penting.
6. Job/command membawa `sekolah_id` dan men-set TenantContext. File di storage privat per sekolah, diakses lewat controller ber-otorisasi. Key cache diprefiks sekolah.
7. Resource sensitif (penilaian, surat_tugas, import_batches) memakai kolom `ulid` sebagai route key.
8. Setiap fitur baru WAJIB disertai test lintas-tenant (user sekolah B mengakses data sekolah A harus 404).

## Keamanan
- Password di-hash dan tidak pernah ditulis ke log, pesan error, atau import_batches. Login dibatasi rate limit. `must_change_password` dipaksa.
- Otorisasi di server (Policy), bukan hanya menyembunyikan menu. Validasi memakai FormRequest.
- Jangan commit `.env`. Jangan menambah paket di luar daftar tanpa izin.

## Desain UI: terang, bersih, ramah pengguna usia 35+
Tema HANYA terang. Tidak ada dark mode.
Token warna (definisikan di konfigurasi Tailwind):
- navy-900 #253C6D (tombol utama, judul, top bar), navy-700 #30497D (hover, link, menu aktif), navy-50 #E6ECF7 (header tabel, baris terpilih).
- cream #FFFAF3 (latar halaman), white #FFFFFF (kartu), ink #1F2937 (teks), muted #5B6475, border #E8E0D2.
- orange-500 #F2842F = AKSEN SAJA: garis menu aktif, progress bar, fokus ring, ornamen. Jangan dipakai sebagai warna teks, dan jangan sebagai latar dengan teks putih (kontras hanya 2,6:1). Teks oranye di atas krem memakai orange-700 #B54A08.
- Tombol aksi: Tambah/Simpan hijau #2E7D32 (teks putih, hover #256628). Edit/Ubah kuning #F5B82E (teks #1F2937, hover #E5A71A). Hapus merah #C62828 (teks putih, hover #A91F1F). Tombol utama lain navy-900 teks putih. Sekunder: outline navy.
- Badge status: belum abu (bg #EEF0F4, teks #4B5563), draft kuning (#FFF3D6 / #8A5A00), final hijau (#E8F5E9 / #1B5E20), direvisi oranye muda (#FDE8D4 / #8A3B00). Badge selalu berisi teks, bukan hanya warna.
Aturan tata letak:
- Font dasar 17px, line-height 1.6. Judul halaman 28px, bagian 20px. Tidak ada teks di bawah 14px.
- Target klik minimal 44x44px. Label tombol berupa kata kerja jelas ("Tambah Guru", "Simpan", "Hapus"). Ikon selalu disertai teks.
- Banyak ruang kosong, kartu putih sudut 12px, bayangan tipis, border hangat. Satu aksi utama per halaman.
- Top bar navy tipis (64px). Sidebar TERANG (putih), menu aktif navy-50 dengan garis oranye di kiri. Jangan ada area gelap yang luas. Mobile: menu hamburger.
- Fokus ring oranye 3px, bisa dinavigasi keyboard. Kontras teks minimal 4,5:1.
- Hapus, finalisasi, dan buka kunci selalu lewat dialog konfirmasi berbahasa jelas.
- Tabel: header navy-50, paginasi, pencarian. Di layar kecil menjadi kartu.
- Pesan sukses/galat berupa banner di atas konten, bahasa Indonesia sederhana tanpa istilah teknis.

## Test dan kualitas
- Pest. Tugas dianggap selesai hanya bila `php artisan test` hijau dan `vendor/bin/pint` bersih.
- Arch test: larangan pada aturan tenant no. 3, dan setiap model yang tabelnya punya `sekolah_id` memakai trait.
- Jangan membuat isi instrumen resmi. Seeder hanya butir DUMMY bertanda "CONTOH - GANTI".
- Di akhir tiap tugas: tulis ringkasan perubahan, daftar file, dan hal yang belum dikerjakan.