<?php

namespace Tests\Traits;

use App\Models\Sekolah;
use App\Models\User;
use App\Tenant\TenantContext;

trait InteractsWithTenants
{
    /**
     * Otentikasi sebagai user dengan role tertentu di sekolah tertentu.
     * Mengatur TenantContext aktif ke sekolah tersebut, menyiapkan session, dan memanggil actingAs($user).
     */
    public function actingAsSekolah(Sekolah|int $sekolah, string $role = 'guru', array $userAttributes = []): User
    {
        $sekolahModel = $sekolah instanceof Sekolah ? $sekolah : Sekolah::findOrFail($sekolah);

        TenantContext::set($sekolahModel->id);

        $defaultAttrs = array_merge([
            'must_change_password' => false,
            'aktif' => true,
        ], $userAttributes);

        /** @var User $user */
        $user = match ($role) {
            'admin' => User::factory()->forSekolah($sekolahModel)->admin()->create($defaultAttrs),
            'supervisor' => User::factory()->forSekolah($sekolahModel)->supervisor()->create($defaultAttrs),
            'guru' => User::factory()->forSekolah($sekolahModel)->guru()->create($defaultAttrs),
            'supervisor+guru', 'supervisor,guru' => User::factory()->forSekolah($sekolahModel)->supervisorDanGuru()->create($defaultAttrs),
            default => User::factory()->forSekolah($sekolahModel)->create($defaultAttrs),
        };

        $activeRole = in_array($role, ['supervisor+guru', 'supervisor,guru'], true) ? 'supervisor' : $role;

        session([
            'active_role' => $activeRole,
            'sekolah_kode' => $sekolahModel->kode,
            'sekolah_id' => $sekolahModel->id,
            'nama_sekolah' => $sekolahModel->nama,
        ]);

        $this->actingAs($user);

        return $user;
    }

    /**
     * Eksekusi callback di dalam lingkup tenant tertentu dan kembalikan hasilnya.
     */
    public function withTenant(Sekolah|int $sekolah, callable $callback): mixed
    {
        return TenantContext::runAs($sekolah, $callback);
    }
}
