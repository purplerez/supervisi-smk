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

test('halaman login sekolah dapat dibuka dan menampilkan nama sekolah', function () {
    $sekolah = Sekolah::factory()->create(['nama' => 'SMK Negeri 1 Surabaya']);

    $response = $this->get(route('sekolah.login', ['kode' => $sekolah->kode]));

    $response->assertOk();
    $response->assertSee('SMK Negeri 1 Surabaya');
    $response->assertSee('Nama Pengguna (Username)');
    $response->assertSee('Kata Sandi (Password)');
});

test('halaman login sekolah 404 bila kode sekolah tidak ditemukan', function () {
    $response = $this->get('/s/kode-sekolah-fiktif/login');

    $response->assertNotFound();
});

test('login sekolah berhasil dengan kredensial benar dan mengarahkan ke dasbor', function () {
    $sekolah = Sekolah::factory()->create();

    $user = TenantContext::runAs($sekolah, function () use ($sekolah) {
        return User::factory()->forSekolah($sekolah)->guru()->create([
            'username' => 'guru123',
            'password' => Hash::make('password123'),
            'must_change_password' => false,
            'aktif' => true,
        ]);
    });

    $response = $this->post(route('sekolah.login.post', ['kode' => $sekolah->kode]), [
        'username' => 'guru123',
        'password' => 'password123',
    ]);

    $response->assertRedirect(route('dashboard.guru', ['kode' => $sekolah->kode]));
    $this->assertAuthenticatedAs($user);
    expect(TenantContext::get())->toBe($sekolah->id);
    expect(session('active_role'))->toBe('guru');
    expect(session('sekolah_id'))->toBe($sekolah->id);
});

test('login sekolah gagal bila kata sandi salah', function () {
    $sekolah = Sekolah::factory()->create();

    TenantContext::runAs($sekolah, function () use ($sekolah) {
        User::factory()->forSekolah($sekolah)->guru()->create([
            'username' => 'guru123',
            'password' => Hash::make('password123'),
        ]);
    });

    $response = $this->from(route('sekolah.login', ['kode' => $sekolah->kode]))
        ->post(route('sekolah.login.post', ['kode' => $sekolah->kode]), [
            'username' => 'guru123',
            'password' => 'passwordsalah',
        ]);

    $response->assertRedirect(route('sekolah.login', ['kode' => $sekolah->kode]));
    $response->assertSessionHasErrors('username');
    $this->assertGuest();
});

test('login ditolak bila sekolah berstatus nonaktif dengan pesan bahasa Indonesia yang jelas', function () {
    $sekolah = Sekolah::factory()->create([
        'status' => 'nonaktif',
    ]);

    TenantContext::runAs($sekolah, function () use ($sekolah) {
        User::factory()->forSekolah($sekolah)->guru()->create([
            'username' => 'guru123',
            'password' => Hash::make('password123'),
        ]);
    });

    $response = $this->from(route('sekolah.login', ['kode' => $sekolah->kode]))
        ->post(route('sekolah.login.post', ['kode' => $sekolah->kode]), [
            'username' => 'guru123',
            'password' => 'password123',
        ]);

    $response->assertRedirect(route('sekolah.login', ['kode' => $sekolah->kode]));
    $response->assertSessionHasErrors(['username' => 'Sekolah ini sedang dinonaktifkan. Silakan hubungi administrator.']);
    $this->assertGuest();
});

test('login ditolak bila akun user berstatus nonaktif dengan pesan bahasa Indonesia yang jelas', function () {
    $sekolah = Sekolah::factory()->create();

    TenantContext::runAs($sekolah, function () use ($sekolah) {
        User::factory()->forSekolah($sekolah)->guru()->create([
            'username' => 'guru_nonaktif',
            'password' => Hash::make('password123'),
            'aktif' => false,
        ]);
    });

    $response = $this->from(route('sekolah.login', ['kode' => $sekolah->kode]))
        ->post(route('sekolah.login.post', ['kode' => $sekolah->kode]), [
            'username' => 'guru_nonaktif',
            'password' => 'password123',
        ]);

    $response->assertRedirect(route('sekolah.login', ['kode' => $sekolah->kode]));
    $response->assertSessionHasErrors(['username' => 'Akun Anda telah dinonaktifkan. Silakan hubungi admin sekolah.']);
    $this->assertGuest();
});

test('login ditolak bila user sekolah A mencoba login di URL sekolah B', function () {
    $sekolahA = Sekolah::factory()->create(['kode' => 'smk1']);
    $sekolahB = Sekolah::factory()->create(['kode' => 'smk2']);

    // User terdaftar di Sekolah A
    TenantContext::runAs($sekolahA, function () use ($sekolahA) {
        User::factory()->forSekolah($sekolahA)->guru()->create([
            'username' => 'guru_a',
            'password' => Hash::make('password123'),
        ]);
    });

    // Mencoba login di URL Sekolah B
    $response = $this->from(route('sekolah.login', ['kode' => $sekolahB->kode]))
        ->post(route('sekolah.login.post', ['kode' => $sekolahB->kode]), [
            'username' => 'guru_a',
            'password' => 'password123',
        ]);

    $response->assertRedirect(route('sekolah.login', ['kode' => $sekolahB->kode]));
    $response->assertSessionHasErrors('username');
    $this->assertGuest();
});

test('rate limit login membatasi percobaan setelah 5 kali gagal', function () {
    $sekolah = Sekolah::factory()->create();

    TenantContext::runAs($sekolah, function () use ($sekolah) {
        User::factory()->forSekolah($sekolah)->guru()->create([
            'username' => 'guru_target',
            'password' => Hash::make('password_asli'),
        ]);
    });

    // 5 kali gagal
    for ($i = 0; $i < 5; $i++) {
        $this->post(route('sekolah.login.post', ['kode' => $sekolah->kode]), [
            'username' => 'guru_target',
            'password' => 'salah',
        ]);
    }

    // Percobaan ke-6 harus kena rate limit
    $response = $this->post(route('sekolah.login.post', ['kode' => $sekolah->kode]), [
        'username' => 'guru_target',
        'password' => 'salah',
    ]);

    $response->assertSessionHasErrors('username');
    $errorMessage = session('errors')->first('username');
    expect($errorMessage)->toContain('Terlalu banyak percobaan');
});

test('user dengan must_change_password diarahkan ke formulir ganti password setelah login', function () {
    $sekolah = Sekolah::factory()->create();

    TenantContext::runAs($sekolah, function () use ($sekolah) {
        User::factory()->forSekolah($sekolah)->guru()->create([
            'username' => 'guru_baru',
            'password' => Hash::make('password123'),
            'must_change_password' => true,
        ]);
    });

    $response = $this->post(route('sekolah.login.post', ['kode' => $sekolah->kode]), [
        'username' => 'guru_baru',
        'password' => 'password123',
    ]);

    $response->assertRedirect(route('password.change', ['kode' => $sekolah->kode]));
});

test('middleware memblokir akses ke halaman lain bila must_change_password bernilai true', function () {
    $sekolah = Sekolah::factory()->create();

    $user = $this->actingAsSekolah($sekolah, 'guru', ['must_change_password' => true]);

    $response = $this->get(route('dashboard.guru', ['kode' => $sekolah->kode]));

    $response->assertRedirect(route('password.change', ['kode' => $sekolah->kode]));
});

test('pengguna dapat memperbarui kata sandi dan mematikan flag must_change_password', function () {
    $sekolah = Sekolah::factory()->create();

    $user = $this->actingAsSekolah($sekolah, 'guru', ['must_change_password' => true]);

    $response = $this->post(route('password.update', ['kode' => $sekolah->kode]), [
        'password' => 'passwordBaru123',
        'password_confirmation' => 'passwordBaru123',
    ]);

    $response->assertRedirect(route('dashboard.guru', ['kode' => $sekolah->kode]));

    $user->refresh();
    expect($user->must_change_password)->toBeFalse();
    expect(Hash::check('passwordBaru123', $user->password))->toBeTrue();
});

test('ganti password gagal bila konfirmasi tidak cocok atau panjang kurang dari 8 karakter', function () {
    $sekolah = Sekolah::factory()->create();

    $this->actingAsSekolah($sekolah, 'guru', ['must_change_password' => true]);

    $response = $this->post(route('password.update', ['kode' => $sekolah->kode]), [
        'password' => 'pendek',
        'password_confirmation' => 'pendek',
    ]);

    $response->assertSessionHasErrors('password');
});

test('logout berhasil membersihkan session dan tenant context', function () {
    $sekolah = Sekolah::factory()->create();

    $this->actingAsSekolah($sekolah, 'guru');
    expect(TenantContext::get())->toBe($sekolah->id);

    $response = $this->post(route('logout'));

    $response->assertRedirect(route('sekolah.login', ['kode' => $sekolah->kode]));
    $this->assertGuest();
    expect(TenantContext::get())->toBeNull();
});
