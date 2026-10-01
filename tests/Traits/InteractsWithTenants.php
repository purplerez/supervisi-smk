<?php

namespace Tests\Traits;

use App\Models\Sekolah;
use App\Models\User;
use App\Tenant\TenantContext;

trait InteractsWithTenants
{
    /**
     * Otentikasi sebagai user dengan role tertentu di sekolah tertentu.
     * Mengatur TenantContext aktif ke sekolah tersebut dan memanggil actingAs($user).
     */
    public function actingAsSekolah(Sekolah|int $sekolah, string $role = 'guru'): User
    {
        $sekolahModel = $sekolah instanceof Sekolah ? $sekolah : Sekolah::findOrFail($sekolah);

        TenantContext::set($sekolahModel->id);

        /** @var User $user */
        $user = match ($role) {
            'admin' => User::factory()->forSekolah($sekolahModel)->admin()->create(),
            'supervisor' => User::factory()->forSekolah($sekolahModel)->supervisor()->create(),
            'guru' => User::factory()->forSekolah($sekolahModel)->guru()->create(),
            'supervisor+guru', 'supervisor,guru' => User::factory()->forSekolah($sekolahModel)->supervisorDanGuru()->create(),
            default => User::factory()->forSekolah($sekolahModel)->create(),
        };

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
