<?php

use App\Models\Sekolah;
use App\Models\User;
use App\Policies\BasePolicy;
use App\Tenant\TenantContext;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

uses(RefreshDatabase::class);

class DummyPolicy extends BasePolicy
{
    public function view(User $user, mixed $model): bool
    {
        return $this->checkTenant($user, $model);
    }
}

test('base policy mengizinkan akses bila model berada di sekolah yang sama dengan user', function () {
    $sekolah = Sekolah::factory()->create();

    $user = TenantContext::runAs($sekolah, fn () => User::factory()->guru()->create());
    $targetUser = TenantContext::runAs($sekolah, fn () => User::factory()->guru()->create());

    $policy = new DummyPolicy;

    expect($policy->view($user, $targetUser))->toBeTrue();
});

test('base policy melempar HTTP 404 (bukan 403) bila model milik sekolah lain', function () {
    $sekolahA = Sekolah::factory()->create();
    $sekolahB = Sekolah::factory()->create();

    $userSekolahA = TenantContext::runAs($sekolahA, fn () => User::factory()->guru()->create());
    $modelSekolahB = TenantContext::runAs($sekolahB, fn () => User::factory()->guru()->create());

    $policy = new DummyPolicy;

    try {
        $policy->view($userSekolahA, $modelSekolahB);
        $this->fail('Seharusnya melempar NotFoundHttpException (404)');
    } catch (NotFoundHttpException $e) {
        expect($e->getStatusCode())->toBe(404);
        expect($e->getMessage())->toBe('Data tidak ditemukan.');
    }
});

test('base policy mengizinkan super admin mengakses data sekolah manapun', function () {
    $sekolah = Sekolah::factory()->create();

    $superAdmin = User::factory()->superAdmin()->create();
    $modelSekolah = TenantContext::runAs($sekolah, fn () => User::factory()->guru()->create());

    $policy = new DummyPolicy;

    expect($policy->view($superAdmin, $modelSekolah))->toBeTrue();
});
