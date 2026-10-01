<?php

use App\Models\Sekolah;
use App\Models\User;
use App\Models\UserRole;
use App\Tenant\Exceptions\TenantBypassDisallowedException;
use App\Tenant\Exceptions\TenantContextMissingException;
use App\Tenant\TenantContext;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

beforeEach(function () {
    TenantContext::clear();
});

test('scope fail-closed: query pada model bertenant tanpa TenantContext melempar exception', function () {
    expect(TenantContext::get())->toBeNull();

    expect(fn () => User::all())
        ->toThrow(TenantContextMissingException::class);

    expect(fn () => User::find(1))
        ->toThrow(TenantContextMissingException::class);

    expect(fn () => UserRole::where('role', 'guru')->get())
        ->toThrow(TenantContextMissingException::class);
});

test('creating fail-closed: pembuatan data bertenant tanpa TenantContext melempar exception', function () {
    expect(TenantContext::get())->toBeNull();

    expect(function () {
        User::create([
            'nama' => 'Budi Santoso',
            'username' => 'budi123',
            'password' => 'secret123',
        ]);
    })->toThrow(TenantContextMissingException::class);
});

test('creating hook mengisi sekolah_id secara otomatis dari TenantContext aktif', function () {
    $sekolah = Sekolah::factory()->create();
    TenantContext::set($sekolah);

    $user = User::create([
        'nama' => 'Ahmad Dahlan',
        'username' => 'ahmad_d',
        'password' => 'secret123',
    ]);

    expect($user->sekolah_id)->toBe($sekolah->id);

    $role = UserRole::create([
        'user_id' => $user->id,
        'role' => 'guru',
    ]);

    expect($role->sekolah_id)->toBe($sekolah->id);
});

test('user sekolah A tidak dapat menemukan data sekolah B melalui find, first, relasi, maupun agregasi', function () {
    $sekolahA = Sekolah::factory()->create();
    $sekolahB = Sekolah::factory()->create();

    // Buat data untuk Sekolah A
    $userA1 = TenantContext::runAs($sekolahA, function () {
        $u = User::factory()->admin()->create(['nama' => 'Admin Sekolah A']);
        UserRole::create(['user_id' => $u->id, 'role' => 'supervisor']);

        return $u;
    });

    // Buat data untuk Sekolah B
    $userB1 = TenantContext::runAs($sekolahB, function () {
        $u = User::factory()->guru()->create(['nama' => 'Guru Sekolah B']);
        UserRole::create(['user_id' => $u->id, 'role' => 'supervisor']);

        return $u;
    });

    // Jalankan dalam konteks Sekolah A
    TenantContext::set($sekolahA);

    // 1. find($id) milik sekolah B harus mengembalikan null
    expect(User::find($userB1->id))->toBeNull();

    // 2. where('id', $id)->first() milik sekolah B harus null
    expect(User::where('id', $userB1->id)->first())->toBeNull();

    // 3. User::all() hanya mengembalikan user sekolah A
    $semuaUser = User::all();
    expect($semuaUser)->toHaveCount(1)
        ->and($semuaUser->first()->id)->toBe($userA1->id)
        ->and($semuaUser->pluck('id'))->not->toContain($userB1->id);

    // 4. Agregasi User::count() hanya menghitung sekolah A
    expect(User::count())->toBe(1);

    // 5. Query UserRole hanya mengembalikan role milik sekolah A
    $roles = UserRole::all();
    expect($roles->pluck('sekolah_id')->unique()->all())->toBe([$sekolahA->id]);
    expect($roles->pluck('user_id'))->not->toContain($userB1->id);
});

test('tanpaTenant() melempar exception bila dipanggil dari luar namespace super-admin', function () {
    // Dipanggil dari test ini (namespace Tests\Feature\Tenant), bukan App\SuperAdmin\
    expect(fn () => User::tanpaTenant()->get())
        ->toThrow(TenantBypassDisallowedException::class);
});

test('super admin tidak memerlukan sekolah_id dan tidak terkena TenantContextMissingException saat pembuatan', function () {
    expect(TenantContext::get())->toBeNull();

    $superAdmin = User::factory()->superAdmin()->create([
        'nama' => 'Super Administrator',
        'email' => 'superadmin@supervisi.test',
    ]);

    expect($superAdmin->is_super_admin)->toBeTrue();
    expect($superAdmin->sekolah_id)->toBeNull();
});

test('helper hasRole dan hasAnyRole pada User berfungsi dengan benar', function () {
    $sekolah = Sekolah::factory()->create();

    $user = TenantContext::runAs($sekolah, function () use ($sekolah) {
        $u = User::factory()->forSekolah($sekolah)->supervisorDanGuru()->create();

        return $u;
    });

    TenantContext::set($sekolah);

    expect($user->hasRole('supervisor'))->toBeTrue();
    expect($user->hasRole('guru'))->toBeTrue();
    expect($user->hasRole('admin'))->toBeFalse();

    expect($user->hasAnyRole(['admin', 'supervisor']))->toBeTrue();
    expect($user->hasAnyRole(['admin']))->toBeFalse();
});
