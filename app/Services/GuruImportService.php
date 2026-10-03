<?php

namespace App\Services;

use App\Imports\GuruImportParser;
use App\Models\ImportBatch;
use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use Maatwebsite\Excel\Facades\Excel;

class GuruImportService
{
    /**
     * Lakukan validasi dan pratinjau tanpa menyimpan ke database (dry run).
     *
     * @return array<string, mixed>
     */
    public function validasiDanPratinjau(string $filePathDiskPrivate, int $sekolahId): array
    {
        $parser = new GuruImportParser;
        Excel::import($parser, $filePathDiskPrivate, 'private');

        $rows = $parser->rows;
        $existingUsers = User::where('sekolah_id', $sekolahId)->get()->keyBy('username');

        $totalBaris = count($rows);
        $jumlahBaru = 0;
        $jumlahUpdate = 0;
        $jumlahGalat = 0;

        $daftarBaris = [];
        $galatList = [];
        $seenUsernames = [];

        foreach ($rows as $index => $row) {
            $nomorBaris = $index + 2; // Baris 1 adalah heading

            $nama = isset($row['nama']) ? trim((string) $row['nama']) : '';
            $username = isset($row['username']) ? trim((string) $row['username']) : '';
            $email = isset($row['email']) ? trim((string) $row['email']) : '';
            $nip = isset($row['nip']) ? trim((string) $row['nip']) : '';
            $nuptk = isset($row['nuptk']) ? trim((string) $row['nuptk']) : '';
            $password = isset($row['password']) ? (string) $row['password'] : '';

            $barisGalat = [];

            // Validasi nama
            if ($nama === '') {
                $barisGalat[] = 'Nama wajib diisi.';
            }

            // Validasi username
            if ($username === '') {
                $barisGalat[] = 'Username wajib diisi.';
            } elseif (preg_match('/\s/', $username)) {
                $barisGalat[] = 'Username tidak boleh mengandung spasi.';
            } elseif (in_array(strtolower($username), $seenUsernames, true)) {
                $barisGalat[] = 'Username duplikat dalam file Excel ini.';
            } else {
                $seenUsernames[] = strtolower($username);
            }

            // Validasi email jika diisi
            if ($email !== '' && ! filter_var($email, FILTER_VALIDATE_EMAIL)) {
                $barisGalat[] = 'Format email tidak valid.';
            }

            // Cek apakah akun lama atau baru
            $isAkunLama = $username !== '' && $existingUsers->has($username);

            $statusAkun = $isAkunLama ? 'update' : 'baru';
            $catatanPassword = '';

            if ($isAkunLama) {
                $catatanPassword = 'Password dilewati (akun lama tetap menggunakan password saat ini)';
            } else {
                // Untuk akun baru, password wajib dan minimal 8 karakter
                if ($password === '') {
                    $barisGalat[] = 'Password wajib diisi untuk akun baru.';
                } elseif (strlen($password) < 8) {
                    $barisGalat[] = 'Password untuk akun baru minimal 8 karakter.';
                } else {
                    $catatanPassword = 'Password baru akan diset (wajib ganti saat login)';
                }
            }

            $isValid = count($barisGalat) === 0;

            if ($isValid) {
                if ($isAkunLama) {
                    $jumlahUpdate++;
                } else {
                    $jumlahBaru++;
                }
            } else {
                $jumlahGalat++;
                foreach ($barisGalat as $g) {
                    $galatList[] = "Baris {$nomorBaris}: {$g}";
                }
            }

            $daftarBaris[] = [
                'nomor_baris' => $nomorBaris,
                'nama' => $nama,
                'username' => $username,
                'email' => $email,
                'nip' => $nip,
                'nuptk' => $nuptk,
                'status_akun' => $statusAkun,
                'catatan_password' => $catatanPassword,
                'is_valid' => $isValid,
                'galat' => $barisGalat,
            ];
        }

        return [
            'total_baris' => $totalBaris,
            'jumlah_baru' => $jumlahBaru,
            'jumlah_update' => $jumlahUpdate,
            'jumlah_galat' => $jumlahGalat,
            'daftar_baris' => $daftarBaris,
            'galat_list' => $galatList,
            'bisa_diproses' => ($jumlahBaru + $jumlahUpdate) > 0,
        ];
    }

    /**
     * Proses impor data ke database (upsert aman).
     */
    public function prosesImpor(string $filePathDiskPrivate, int $sekolahId, int $userId, string $originalFileName): ImportBatch
    {
        $parser = new GuruImportParser;
        Excel::import($parser, $filePathDiskPrivate, 'private');

        $rows = $parser->rows;
        $existingUsers = User::where('sekolah_id', $sekolahId)->get()->keyBy('username');

        $sukses = 0;
        $gagal = 0;
        $errors = [];
        $seenUsernames = [];

        foreach ($rows as $index => $row) {
            $nomorBaris = $index + 2;

            $nama = isset($row['nama']) ? trim((string) $row['nama']) : '';
            $username = isset($row['username']) ? trim((string) $row['username']) : '';
            $email = isset($row['email']) ? trim((string) $row['email']) : '';
            $nip = isset($row['nip']) ? trim((string) $row['nip']) : '';
            $nuptk = isset($row['nuptk']) ? trim((string) $row['nuptk']) : '';
            $password = isset($row['password']) ? (string) $row['password'] : '';

            $barisGalat = [];

            if ($nama === '') {
                $barisGalat[] = 'Nama wajib diisi.';
            }

            if ($username === '') {
                $barisGalat[] = 'Username wajib diisi.';
            } elseif (preg_match('/\s/', $username)) {
                $barisGalat[] = 'Username tidak boleh mengandung spasi.';
            } elseif (in_array(strtolower($username), $seenUsernames, true)) {
                $barisGalat[] = 'Username duplikat dalam file.';
            } else {
                $seenUsernames[] = strtolower($username);
            }

            if ($email !== '' && ! filter_var($email, FILTER_VALIDATE_EMAIL)) {
                $barisGalat[] = 'Format email tidak valid.';
            }

            $isAkunLama = $username !== '' && $existingUsers->has($username);

            if (! $isAkunLama) {
                if ($password === '') {
                    $barisGalat[] = 'Password wajib diisi untuk akun baru.';
                } elseif (strlen($password) < 8) {
                    $barisGalat[] = 'Password untuk akun baru minimal 8 karakter.';
                }
            }

            // Jika ada galat, catat kegagalan tanpa password
            if (count($barisGalat) > 0) {
                $gagal++;
                $errors[] = [
                    'baris' => $nomorBaris,
                    'username' => $username,
                    'pesan' => implode(' ', $barisGalat),
                ];

                continue;
            }

            if ($isAkunLama) {
                // ATURAN IMPOR ULANG:
                // Hanya nama, email, NIP, NUPTK yang diperbarui.
                // Password dan role TIDAK PERNAH DITIMPA.
                /** @var User $akunLama */
                $akunLama = $existingUsers->get($username);
                $akunLama->update([
                    'nama' => $nama,
                    'email' => $email !== '' ? $email : null,
                    'nip' => $nip !== '' ? $nip : null,
                    'nuptk' => $nuptk !== '' ? $nuptk : null,
                ]);

                $sukses++;
            } else {
                // Akun baru: buat akun dengan role guru dan must_change_password = true
                $akunBaru = User::create([
                    'sekolah_id' => $sekolahId,
                    'nama' => $nama,
                    'username' => $username,
                    'email' => $email !== '' ? $email : null,
                    'nip' => $nip !== '' ? $nip : null,
                    'nuptk' => $nuptk !== '' ? $nuptk : null,
                    'password' => Hash::make($password),
                    'must_change_password' => true,
                    'aktif' => true,
                    'is_super_admin' => false,
                ]);

                $akunBaru->assignRole('guru');
                $sukses++;
            }
        }

        // Hapus file unggahan sementara dari disk privat
        if (Storage::disk('private')->exists($filePathDiskPrivate)) {
            Storage::disk('private')->delete($filePathDiskPrivate);
        }

        $status = $gagal === 0 ? 'sukses' : ($sukses > 0 ? 'sebagian' : 'gagal');

        // Catat riwayat batch impor
        return ImportBatch::create([
            'sekolah_id' => $sekolahId,
            'user_id' => $userId,
            'nama_file' => $originalFileName,
            'total' => count($rows),
            'sukses' => $sukses,
            'gagal' => $gagal,
            'errors' => count($errors) > 0 ? $errors : null,
            'status' => $status,
        ]);
    }
}
