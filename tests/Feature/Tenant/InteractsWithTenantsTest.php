<?php

use App\Models\Sekolah;
use App\Models\User;
use App\Tenant\TenantContext;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\Traits\InteractsWithTenants;

uses(RefreshDatabase::class, InteractsWithTenants::class);

test('actingAsSekolah mengotentikasi user dan mengatur TenantContext dengan benar', function (string $role) {
    $sekolah = Sekolah::factory()->create();

    $user = $this->actingAsSekolah($sekolah, $role);

    expect($user)->toBeInstanceOf(User::class);
    expect($user->sekolah_id)->toBe($sekolah->id);
    expect(auth()->id())->toBe($user->id);
    expect(TenantContext::get())->toBe($sekolah->id);
    expect($user->hasRole($role))->toBeTrue();
})->with('lintas_tenant_roles');
