<?php

namespace App\Services;

use App\Models\Sekolah;
use App\Models\User;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\Hash;
use InvalidArgumentException;

class GuruService
{
    /**
     * Ambil daftar pengguna sekolah dengan pencarian dan filter status/role.
     */
    public function daftarPengguna(?string $cari, ?string $status, ?string $role, int $perPage = 15): LengthAwarePaginator
    {
        return User::query()
            ->when($cari, function ($q) use ($cari) {
                $q->where(function ($sub) use ($cari) {
                    $sub->where('nama', 'like', "%{$cari}%")
                        ->orWhere('username', 'like', "%{$cari}%")
                        ->orWhere('email', 'like', "%{$cari}%")
                        ->orWhere('nip', 'like', "%{$cari}%")
                        ->orWhere('nuptk', 'like', "%{$cari}%");
                });
            })
            ->when($status !== null && $status !== '' && $status !== 'semua', function ($q) use ($status) {
                if ($status === 'aktif') {
                    $q->where('aktif', true);
                } elseif ($status === 'nonaktif') {
                    $q->where('aktif', false);
                }
            })
            ->when($role !== null && $role !== '' && $role !== 'semua', function ($q) use ($role) {
                $q->whereHas('roles', fn ($r) => $r->where('role', $role));
            })
            ->with(['roles'])
            ->orderBy('nama', 'asc')
            ->paginate($perPage)
            ->withQueryString();
    }

    /**
     * Tambah pengguna baru secara manual dari form admin.
     * Otomatis diberi peran 'guru'.
     */
    public function tambahPengguna(array $data): User
    {
        $user = User::create([
            'nama' => $data['nama'],
            'username' => $data['username'],
            'email' => $data['email'] ?? null,
            'nip' => $data['nip'] ?? null,
            'nuptk' => $data['nuptk'] ?? null,
            'password' => Hash::make($data['password']),
            'must_change_password' => true,
            'aktif' => true,
            'is_super_admin' => false,
        ]);

        $user->assignRole('guru');

        return $user;
    }

    /**
     * Perbarui data profil pengguna.
     */
    public function perbaruiPengguna(User $user, array $data): User
    {
        $user->update([
            'nama' => $data['nama'],
            'email' => $data['email'] ?? null,
            'nip' => $data['nip'] ?? null,
            'nuptk' => $data['nuptk'] ?? null,
        ]);

        return $user->fresh();
    }

    /**
     * Nonaktifkan atau aktifkan pengguna (TIDAK ADA hapus permanen).
     *
     * @throws InvalidArgumentException
     */
    public function toggleStatus(User $user, User $currentUser, Sekolah $sekolah): bool
    {
        if ($user->id === $currentUser->id && $user->aktif) {
            throw new InvalidArgumentException('Anda tidak dapat menonaktifkan akun Anda sendiri saat sedang masuk.');
        }

        // Cek jika akun yang akan dinonaktifkan adalah admin
        if ($user->aktif && $user->hasRole('admin')) {
            $jumlahAdminAktif = User::where('aktif', true)
                ->whereHas('roles', fn ($q) => $q->where('role', 'admin'))
                ->count();

            if ($jumlahAdminAktif <= 1) {
                throw new InvalidArgumentException('Tidak dapat menonaktifkan admin ini karena merupakan satu-satunya admin aktif di sekolah ini.');
            }
        }

        $statusBaru = ! $user->aktif;
        $user->update(['aktif' => $statusBaru]);

        return $statusBaru;
    }

    /**
     * Toggle peran Supervisor.
     * Menambahkan peran supervisor TIDAK menghapus peran guru!
     *
     * @throws InvalidArgumentException
     */
    public function toggleSupervisor(User $user, Sekolah $sekolah): bool
    {
        if ($user->hasRole('supervisor')) {
            // Jika user adalah Kepala Sekolah saat ini, tolak pencabutan peran supervisor
            if ($sekolah->kepala_sekolah_id === $user->id) {
                throw new InvalidArgumentException(
                    'Tidak dapat mencabut peran supervisor dari Kepala Sekolah. Ganti atau kosongkan penugasan Kepala Sekolah pada profil sekolah terlebih dahulu.'
                );
            }

            // Hapus peran supervisor saja, peran guru tetap utuh!
            $user->roles()->where('role', 'supervisor')->delete();

            return false;
        }

        // Tambah peran supervisor, peran guru tetap utuh!
        $user->assignRole('supervisor');

        return true;
    }

    /**
     * Toggle peran Guru (Disupervisi).
     * Admin boleh mencabut peran guru bagi yang tidak disupervisi (misal kepala sekolah).
     *
     * @throws InvalidArgumentException
     */
    public function toggleGuru(User $user): bool
    {
        if ($user->hasRole('guru')) {
            // Pastikan user memiliki setidaknya satu peran lain (supervisor atau admin)
            // agar akun tidak kehilangan semua hak akses
            $peranLain = $user->roles()->where('role', '!=', 'guru')->exists();

            if (! $peranLain) {
                throw new InvalidArgumentException(
                    'Peran guru tidak dapat dicabut karena pengguna ini tidak memiliki peran lain. Berikan peran Supervisor terlebih dahulu jika pengguna ini adalah penilai yang tidak disupervisi.'
                );
            }

            $user->roles()->where('role', 'guru')->delete();

            return false;
        }

        $user->assignRole('guru');

        return true;
    }

    /**
     * Atur ulang password pengguna (oleh admin).
     * Password baru diisi admin, must_change_password diset true.
     */
    public function aturUlangPassword(User $user, string $passwordBaru): void
    {
        $user->update([
            'password' => Hash::make($passwordBaru),
            'must_change_password' => true,
        ]);
    }

    /**
     * Tetapkan Kepala Sekolah pada profil sekolah.
     * Hanya pengguna dengan peran supervisor yang dapat dipilih.
     *
     * @throws InvalidArgumentException
     */
    public function tetapkanKepalaSekolah(Sekolah $sekolah, ?int $userId): void
    {
        if ($userId === null || $userId === 0) {
            $sekolah->update(['kepala_sekolah_id' => null]);

            return;
        }

        $kandidat = User::findOrFail($userId);

        if (! $kandidat->hasRole('supervisor')) {
            throw new InvalidArgumentException('Kepala Sekolah wajib memiliki peran Supervisor.');
        }

        if (! $kandidat->aktif) {
            throw new InvalidArgumentException('Pengguna nonaktif tidak dapat ditetapkan sebagai Kepala Sekolah.');
        }

        $sekolah->update(['kepala_sekolah_id' => $kandidat->id]);
    }
}
