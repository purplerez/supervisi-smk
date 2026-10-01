<?php

namespace Tests\Feature\SuperAdmin;

use App\Models\Sekolah;
use App\Models\User;
use App\Tenant\TenantContext;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class TanpaTenantSuperAdminTest extends TestCase
{
    use RefreshDatabase;

    public function test_tanpa_tenant_berhasil_dieksekusi_dari_namespace_super_admin(): void
    {
        $sekolahA = Sekolah::factory()->create();
        $sekolahB = Sekolah::factory()->create();

        TenantContext::runAs($sekolahA, function () {
            User::factory()->admin()->create(['nama' => 'User A']);
        });

        TenantContext::runAs($sekolahB, function () {
            User::factory()->guru()->create(['nama' => 'User B']);
        });

        // Kosongkan tenant context
        TenantContext::clear();

        // Panggilan tanpaTenant() dari namespace Tests\Feature\SuperAdmin (diizinkan)
        $semuaUser = User::tanpaTenant()->get();

        $this->assertCount(2, $semuaUser);
        $this->assertTrue($semuaUser->contains('nama', 'User A'));
        $this->assertTrue($semuaUser->contains('nama', 'User B'));
    }
}
