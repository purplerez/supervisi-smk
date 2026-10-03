<?php

namespace App\SuperAdmin\Services;

use App\Models\Sekolah;
use App\Models\User;
use App\Models\UserRole;
use App\SuperAdmin\Exceptions\AdminTerakhirException;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;

class SekolahService
{
    /**
     * Simpan sekolah baru beserta logo-nya.
     */
    public function simpanSekolah(array $data, ?UploadedFile $logo): Sekolah
    {
        $logoPath = null;

        if ($logo !== null) {
            $logoPath = $logo->store('logo-sekolah', 'public');
        }

        // Sekolah bukan model bertenant; kode unik & tidak boleh konflik
        return Sekolah::create([
            'nama' => $data['nama'],
            'npsn' => $data['npsn'] ?? null,
            'kode' => $data['kode'],
            'alamat' => $data['alamat'] ?? null,
            'status' => $data['status'],
            'logo_path' => $logoPath,
        ]);
    }

    /**
     * Perbarui data sekolah (kode tidak boleh diubah).
     */
    public function perbaruiSekolah(Sekolah $sekolah, array $data, ?UploadedFile $logo): Sekolah
    {
        if ($logo !== null) {
            // Hapus logo lama bila ada
            if ($sekolah->logo_path) {
                Storage::disk('public')->delete($sekolah->logo_path);
            }

            $data['logo_path'] = $logo->store('logo-sekolah', 'public');
        }

        // Pastikan kode tidak berubah
        unset($data['kode']);

        $sekolah->update($data);

        return $sekolah->fresh();
    }

    /**
     * Tambah akun admin untuk sekolah tertentu.
     * Akun dibuat tanpa TenantContext karena super-admin bypass tenant.
     */
    public function tambahAdmin(Sekolah $sekolah, array $data): User
    {
        // Buat instance User secara manual agar tidak melalui creating hook
        // yang membutuhkan TenantContext aktif.
        $user = new User;
        $user->sekolah_id = $sekolah->id;
        $user->nama = $data['nama'];
        $user->username = $data['username'];
        $user->email = $data['email'] ?? null;
        $user->password = Hash::make($data['password']);
        $user->must_change_password = true;
        $user->aktif = true;
        $user->is_super_admin = false;

        // Simpan langsung tanpa melalui global scope
        $user->saveQuietly();

        // Tambahkan role admin - bypass creating hook yang butuh TenantContext
        $role = new UserRole;
        $role->sekolah_id = $sekolah->id;
        $role->user_id = $user->id;
        $role->role = 'admin';
        $role->saveQuietly();

        return $user;
    }

    /**
     * Atur ulang password admin; setelah reset must_change_password = true.
     */
    public function aturUlangPassword(User $admin, string $passwordBaru): void
    {
        User::tanpaTenant()
            ->where('id', $admin->id)
            ->update([
                'password' => Hash::make($passwordBaru),
                'must_change_password' => true,
            ]);
    }

    /**
     * Cari admin milik sekolah tertentu (abort 404 jika tidak ditemukan atau beda sekolah).
     */
    public function cariAdmin(Sekolah $sekolah, int|string $adminId): User
    {
        $admin = User::tanpaTenant()
            ->where('sekolah_id', $sekolah->id)
            ->where('id', $adminId)
            ->first();

        if (! $admin) {
            abort(404);
        }

        return $admin;
    }

    /**
     * Nonaktifkan admin. Sistem menolak menonaktifkan admin aktif terakhir.
     *
     * @throws AdminTerakhirException
     */
    public function nonaktifkanAdmin(Sekolah $sekolah, User $admin): void
    {
        if (! $admin->aktif) {
            return; // Sudah nonaktif, tidak perlu proses
        }

        $jumlahAdminAktif = User::tanpaTenant()
            ->where('sekolah_id', $sekolah->id)
            ->where('aktif', true)
            ->whereHas('roles', fn ($q) => $q->tanpaTenant()->where('role', 'admin'))
            ->count();

        if ($jumlahAdminAktif <= 1) {
            throw new AdminTerakhirException(
                'Tidak dapat menonaktifkan admin ini karena merupakan satu-satunya admin aktif di sekolah ini. '.
                'Tambahkan admin lain terlebih dahulu atau aktifkan admin yang sudah nonaktif.'
            );
        }

        User::tanpaTenant()
            ->where('id', $admin->id)
            ->update(['aktif' => false]);
    }

    /**
     * Aktifkan kembali akun admin.
     */
    public function aktifkanAdmin(User $admin): void
    {
        User::tanpaTenant()
            ->where('id', $admin->id)
            ->update(['aktif' => true]);
    }

    /**
     * Ambil daftar admin sekolah tertentu (dengan/tanpa scope tenant).
     *
     * @return Collection<int, User>
     */
    public function adminSekolah(Sekolah $sekolah): Collection
    {
        return User::tanpaTenant()
            ->where('sekolah_id', $sekolah->id)
            ->whereHas('roles', fn ($q) => $q->tanpaTenant()->where('role', 'admin'))
            ->with(['roles' => fn ($q) => $q->tanpaTenant()])
            ->orderBy('nama')
            ->get();
    }

    /**
     * Ambil statistik ringkas lintas sekolah untuk dasbor.
     *
     * @return array<string, int>
     */
    public function statistikDasbor(): array
    {
        $jumlahSekolah = Sekolah::count();
        $sekolahAktif = Sekolah::where('status', 'aktif')->count();

        $jumlahAdmin = User::tanpaTenant()
            ->whereNotNull('sekolah_id')
            ->whereHas('roles', fn ($q) => $q->tanpaTenant()->where('role', 'admin'))
            ->count();

        $jumlahGuru = User::tanpaTenant()
            ->whereNotNull('sekolah_id')
            ->whereHas('roles', fn ($q) => $q->tanpaTenant()->where('role', 'guru'))
            ->count();

        return compact('jumlahSekolah', 'sekolahAktif', 'jumlahAdmin', 'jumlahGuru');
    }
}
