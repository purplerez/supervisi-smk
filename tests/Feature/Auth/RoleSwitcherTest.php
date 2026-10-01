<?php

use App\Models\Sekolah;
use App\Tenant\TenantContext;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\Traits\InteractsWithTenants;

uses(RefreshDatabase::class, InteractsWithTenants::class);

beforeEach(function () {
    $this->withoutVite();
    TenantContext::clear();
});

test('user dengan kombinasi role dapat beralih peran melalui role switch', function () {
    $sekolah = Sekolah::factory()->create();

    // User memiliki peran supervisor dan guru sekaligus
    $user = $this->actingAsSekolah($sekolah, 'supervisor+guru');

    expect(session('active_role'))->toBe('supervisor');

    // Alihkan ke role guru
    $response = $this->post(route('role.switch', ['kode' => $sekolah->kode]), [
        'role' => 'guru',
    ]);

    $response->assertRedirect(route('dashboard.guru', ['kode' => $sekolah->kode]));
    expect(session('active_role'))->toBe('guru');

    // Alihkan kembali ke supervisor
    $response = $this->post(route('role.switch', ['kode' => $sekolah->kode]), [
        'role' => 'supervisor',
    ]);

    $response->assertRedirect(route('dashboard.supervisor', ['kode' => $sekolah->kode]));
    expect(session('active_role'))->toBe('supervisor');
});

test('switch ke role yang tidak dimiliki pengguna menghasilkan 403', function () {
    $sekolah = Sekolah::factory()->create();

    // User HANYA berperan sebagai guru
    $this->actingAsSekolah($sekolah, 'guru');

    $response = $this->post(route('role.switch', ['kode' => $sekolah->kode]), [
        'role' => 'admin',
    ]);

    $response->assertForbidden();
});

test('user dengan role guru ditolak mengakses route dasbor admin (403)', function () {
    $sekolah = Sekolah::factory()->create();

    $this->actingAsSekolah($sekolah, 'guru');

    $response = $this->get(route('dashboard.admin', ['kode' => $sekolah->kode]));

    $response->assertForbidden();
});

test('user dengan role supervisor ditolak mengakses route dasbor admin (403)', function () {
    $sekolah = Sekolah::factory()->create();

    $this->actingAsSekolah($sekolah, 'supervisor');

    $response = $this->get(route('dashboard.admin', ['kode' => $sekolah->kode]));

    $response->assertForbidden();
});

test('akses lintas-tenant ke rute sekolah lain ditolak', function () {
    $sekolahA = Sekolah::factory()->create(['kode' => 'sekolah-a']);
    $sekolahB = Sekolah::factory()->create(['kode' => 'sekolah-b']);

    // User terdaftar di Sekolah A
    $this->actingAsSekolah($sekolahA, 'admin');

    // Mencoba mengakses dasbor sekolah B
    $response = $this->get(route('dashboard.admin', ['kode' => $sekolahB->kode]));

    // Sesuai SetTenantContext middleware: jika user sekolah A akses URL sekolah B, ditolak dan diarahkan ke login
    $response->assertRedirect(route('sekolah.login', ['kode' => $sekolahB->kode]));
    $response->assertSessionHasErrors('username');
    $this->assertGuest();
});

test('akses ke rute sekolah dengan kode yang tidak terdaftar menghasilkan 404', function () {
    $sekolahA = Sekolah::factory()->create(['kode' => 'sekolah-a']);

    $this->actingAsSekolah($sekolahA, 'admin');

    $response = $this->get('/s/kode-tidak-ada/admin/dashboard');

    $response->assertNotFound();
});
