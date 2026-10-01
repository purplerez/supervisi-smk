<?php

use App\Models\Sekolah;
use App\Models\User;
use App\Tenant\TenantContext;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\Traits\InteractsWithTenants;

uses(RefreshDatabase::class, InteractsWithTenants::class);

beforeEach(function () {
    $this->withoutVite();
    TenantContext::clear();
});

test('halaman login super admin dapat diakses', function () {
    $response = $this->get(route('super-admin.login'));

    $response->assertOk();
    $response->assertSee('Portal Super Admin');
});

test('login super admin berhasil dan mengarahkan ke dashboard super admin', function () {
    $superAdmin = User::factory()->superAdmin()->create([
        'email' => 'superadmin@supervisi.test',
        'password' => Hash::make('rahasiasuper'),
    ]);

    $response = $this->post(route('super-admin.login.post'), [
        'email' => 'superadmin@supervisi.test',
        'password' => 'rahasiasuper',
    ]);

    $response->assertRedirect(route('super-admin.dashboard'));
    $this->assertAuthenticatedAs($superAdmin);

    // Super admin tidak memiliki konteks tenant
    expect(TenantContext::get())->toBeNull();
});

test('login super admin gagal bila kredensial salah', function () {
    User::factory()->superAdmin()->create([
        'email' => 'superadmin@supervisi.test',
        'password' => Hash::make('rahasiasuper'),
    ]);

    $response = $this->from(route('super-admin.login'))
        ->post(route('super-admin.login.post'), [
            'email' => 'superadmin@supervisi.test',
            'password' => 'passwordsalah',
        ]);

    $response->assertRedirect(route('super-admin.login'));
    $response->assertSessionHasErrors('email');
    $this->assertGuest();
});

test('user sekolah biasa ditolak mengakses dasbor super admin (403)', function () {
    $sekolah = Sekolah::factory()->create();

    $this->actingAsSekolah($sekolah, 'admin');

    $response = $this->get(route('super-admin.dashboard'));

    $response->assertForbidden();
});

test('logout super admin berhasil membersihkan sesi', function () {
    $superAdmin = User::factory()->superAdmin()->create();

    $this->actingAs($superAdmin);

    $response = $this->post(route('super-admin.logout'));

    $response->assertRedirect(route('super-admin.login'));
    $this->assertGuest();
    expect(TenantContext::get())->toBeNull();
});
