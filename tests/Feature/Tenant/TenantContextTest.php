<?php

use App\Models\Sekolah;
use App\Tenant\TenantContext;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

test('tenant context dapat mengatur, membaca, dan mengosongkan sekolah aktif', function () {
    $context = app(TenantContext::class);

    expect($context->get())->toBeNull();

    $context->set(10);
    expect($context->get())->toBe(10);

    $context->clear();
    expect($context->get())->toBeNull();
});

test('tenant context dapat menerima model Sekolah', function () {
    $sekolah = Sekolah::factory()->create();

    TenantContext::set($sekolah);
    expect(TenantContext::get())->toBe($sekolah->id);

    TenantContext::clear();
    expect(TenantContext::get())->toBeNull();
});

test('runAs mengeksekusi callback dan mengembalikan konteks ke kondisi awal', function () {
    $sekolah1 = Sekolah::factory()->create();
    $sekolah2 = Sekolah::factory()->create();

    TenantContext::set($sekolah1);
    expect(TenantContext::get())->toBe($sekolah1->id);

    $result = TenantContext::runAs($sekolah2, function () use ($sekolah2) {
        expect(TenantContext::get())->toBe($sekolah2->id);

        return 'hasil';
    });

    expect($result)->toBe('hasil');
    expect(TenantContext::get())->toBe($sekolah1->id);

    TenantContext::clear();
});

test('runAs memulihkan konteks awal meskipun callback melempar exception', function () {
    $sekolah1 = Sekolah::factory()->create();
    $sekolah2 = Sekolah::factory()->create();

    TenantContext::set($sekolah1);

    try {
        TenantContext::runAs($sekolah2, function () {
            throw new RuntimeException('Terjadi galat dalam callback');
        });
    } catch (RuntimeException $e) {
        // tangkap exception
    }

    expect(TenantContext::get())->toBe($sekolah1->id);
    TenantContext::clear();
});
