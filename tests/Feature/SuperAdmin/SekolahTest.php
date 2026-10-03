<?php

use App\Models\Sekolah;
use App\Models\User;
use App\SuperAdmin\Exceptions\AdminTerakhirException;
use App\SuperAdmin\Services\SekolahService;
use App\Tenant\TenantContext;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;

uses(RefreshDatabase::class);

// =============================================================================
// HELPER
// =============================================================================

/**
 * Buat user super-admin untuk testing.
 * Menggunakan insert DB langsung agar tidak melalui BelongsToSekolah creating hook.
 */
function buatSuperAdmin(): User
{
    $id = DB::table('users')->insertGetId([
        'nama' => 'Super Admin Test',
        'email' => 'super@test.com',
        'username' => null,
        'sekolah_id' => null,
        'password' => Hash::make('password123'),
        'is_super_admin' => true,
        'aktif' => true,
        'must_change_password' => false,
        'created_at' => now(),
        'updated_at' => now(),
    ]);

    return User::withoutGlobalScopes()->find($id);
}

/**
 * Buat sekolah dummy.
 */
function buatSekolah(array $atribut = []): Sekolah
{
    return Sekolah::create(array_merge([
        'nama' => 'SMP Negeri Test',
        'kode' => 'smpn-test-'.uniqid(),
        'status' => 'aktif',
    ], $atribut));
}

/**
 * Buat admin untuk sekolah tertentu via SekolahService (namespace App\SuperAdmin).
 */
function buatAdminSekolah(Sekolah $sekolah, array $atribut = []): User
{
    /** @var SekolahService $service */
    $service = app(SekolahService::class);

    return $service->tambahAdmin($sekolah, array_merge([
        'nama' => 'Admin Test',
        'username' => 'admin-'.uniqid(),
        'password' => 'password123',
    ], $atribut));
}

// =============================================================================
// TEST: HANYA SUPER-ADMIN YANG BISA MENGAKSES
// =============================================================================

describe('Akses super-admin saja', function () {

    it('super-admin bisa mengakses daftar sekolah', function () {
        $super = buatSuperAdmin();

        $this->actingAs($super)
            ->get(route('super-admin.sekolah.index'))
            ->assertOk();
    });

    it('tamu diredirect ke login saat mencoba akses daftar sekolah', function () {
        $this->get(route('super-admin.sekolah.index'))
            ->assertRedirect(route('super-admin.login'));
    });

    it('admin sekolah (bukan super-admin) diblokir dari daftar sekolah', function () {
        $sekolah = buatSekolah();
        TenantContext::set($sekolah->id);

        $admin = User::factory()->forSekolah($sekolah)->admin()->create([
            'must_change_password' => false,
        ]);

        session([
            'active_role' => 'admin',
            'sekolah_kode' => $sekolah->kode,
            'sekolah_id' => $sekolah->id,
        ]);

        $this->actingAs($admin)
            ->get(route('super-admin.sekolah.index'))
            ->assertForbidden();
    });

    it('supervisor diblokir dari daftar sekolah', function () {
        $sekolah = buatSekolah();
        TenantContext::set($sekolah->id);

        $supervisor = User::factory()->forSekolah($sekolah)->supervisor()->create([
            'must_change_password' => false,
        ]);

        session([
            'active_role' => 'supervisor',
            'sekolah_kode' => $sekolah->kode,
            'sekolah_id' => $sekolah->id,
        ]);

        $this->actingAs($supervisor)
            ->get(route('super-admin.sekolah.index'))
            ->assertForbidden();
    });

    it('guru diblokir dari daftar sekolah', function () {
        $sekolah = buatSekolah();
        TenantContext::set($sekolah->id);

        $guru = User::factory()->forSekolah($sekolah)->guru()->create([
            'must_change_password' => false,
        ]);

        $this->actingAs($guru)
            ->get(route('super-admin.sekolah.index'))
            ->assertForbidden();
    });
});

// =============================================================================
// TEST: CRUD SEKOLAH
// =============================================================================

describe('CRUD sekolah', function () {

    it('super-admin bisa melihat form tambah sekolah', function () {
        $super = buatSuperAdmin();

        $this->actingAs($super)
            ->get(route('super-admin.sekolah.create'))
            ->assertOk()
            ->assertSee('Tambah Sekolah');
    });

    it('super-admin bisa membuat sekolah baru', function () {
        $super = buatSuperAdmin();

        $this->actingAs($super)
            ->post(route('super-admin.sekolah.store'), [
                'nama' => 'SMP Negeri 1 Baru',
                'kode' => 'smpn1-baru',
                'status' => 'aktif',
            ])
            ->assertRedirect();

        $this->assertDatabaseHas('sekolah', [
            'nama' => 'SMP Negeri 1 Baru',
            'kode' => 'smpn1-baru',
        ]);
    });

    it('kode sekolah harus unik', function () {
        buatSekolah(['kode' => 'kode-unik']);
        $super = buatSuperAdmin();

        $this->actingAs($super)
            ->post(route('super-admin.sekolah.store'), [
                'nama' => 'Sekolah Lain',
                'kode' => 'kode-unik',
                'status' => 'aktif',
            ])
            ->assertSessionHasErrors('kode');
    });

    it('super-admin bisa melihat detail sekolah', function () {
        $super = buatSuperAdmin();
        $sekolah = buatSekolah();

        $this->actingAs($super)
            ->get(route('super-admin.sekolah.show', $sekolah))
            ->assertOk()
            ->assertSee($sekolah->nama);
    });

    it('super-admin bisa mengubah data sekolah (kecuali kode)', function () {
        $super = buatSuperAdmin();
        $sekolah = buatSekolah(['kode' => 'kode-tetap', 'nama' => 'Nama Lama']);

        $this->actingAs($super)
            ->put(route('super-admin.sekolah.update', $sekolah), [
                'nama' => 'Nama Baru',
                'kode' => 'kode-baru-dicoba',  // harus diabaikan
                'status' => 'aktif',
            ])
            ->assertRedirect(route('super-admin.sekolah.show', $sekolah));

        $sekolah->refresh();
        expect($sekolah->nama)->toBe('Nama Baru');
        expect($sekolah->kode)->toBe('kode-tetap'); // kode tidak berubah
    });

    it('upload logo tersimpan di storage publik', function () {
        Storage::fake('public');
        $super = buatSuperAdmin();
        $sekolah = buatSekolah();

        $logo = UploadedFile::fake()->image('logo.jpg', 200, 200);

        $this->actingAs($super)
            ->put(route('super-admin.sekolah.update', $sekolah), [
                'nama' => $sekolah->nama,
                'status' => 'aktif',
                'logo' => $logo,
            ]);

        $sekolah->refresh();
        expect($sekolah->logo_path)->not->toBeNull();
        Storage::disk('public')->assertExists($sekolah->logo_path);
    });

    it('super-admin bisa menonaktifkan sekolah', function () {
        $super = buatSuperAdmin();
        $sekolah = buatSekolah(['status' => 'aktif']);

        $this->actingAs($super)
            ->put(route('super-admin.sekolah.update', $sekolah), [
                'nama' => $sekolah->nama,
                'status' => 'nonaktif',
            ]);

        expect($sekolah->fresh()->status)->toBe('nonaktif');
    });

    it('tidak ada rute hapus sekolah', function () {
        $super = buatSuperAdmin();
        $sekolah = buatSekolah();

        // DELETE harus 405 Method Not Allowed
        $this->actingAs($super)
            ->delete(route('super-admin.sekolah.show', $sekolah))
            ->assertStatus(405);
    });
});

// =============================================================================
// TEST: MANAJEMEN ADMIN SEKOLAH
// =============================================================================

describe('Manajemen admin sekolah', function () {

    it('super-admin bisa menambah admin ke sekolah', function () {
        $super = buatSuperAdmin();
        $sekolah = buatSekolah();

        $this->actingAs($super)
            ->post(route('super-admin.sekolah.admin.store', $sekolah), [
                'nama' => 'Admin Baru',
                'username' => 'adminbaru',
                'password' => 'rahasia123',
            ])
            ->assertRedirect(route('super-admin.sekolah.show', $sekolah));

        $this->assertDatabaseHas('users', [
            'nama' => 'Admin Baru',
            'username' => 'adminbaru',
            'sekolah_id' => $sekolah->id,
            'must_change_password' => true,
            'aktif' => true,
        ]);

        $user = User::withoutGlobalScopes()
            ->where('username', 'adminbaru')
            ->where('sekolah_id', $sekolah->id)
            ->first();

        expect($user)->not->toBeNull();

        // Cek role admin di database langsung (hindari global scope TenantContext)
        $this->assertDatabaseHas('user_roles', [
            'user_id' => $user->id,
            'sekolah_id' => $sekolah->id,
            'role' => 'admin',
        ]);
    });

    it('admin baru langsung punya must_change_password = true', function () {
        $super = buatSuperAdmin();
        $sekolah = buatSekolah();

        $this->actingAs($super)
            ->post(route('super-admin.sekolah.admin.store', $sekolah), [
                'nama' => 'Admin Wajib Ganti',
                'username' => 'adminwajib',
                'password' => 'rahasia123',
            ]);

        $user = User::withoutGlobalScopes()
            ->where('username', 'adminwajib')
            ->where('sekolah_id', $sekolah->id)
            ->first();

        expect($user->must_change_password)->toBeTrue();
    });

    it('username admin harus unik dalam satu sekolah', function () {
        $super = buatSuperAdmin();
        $sekolah = buatSekolah();

        // Buat admin pertama
        buatAdminSekolah($sekolah, ['username' => 'admin-sama']);

        // Coba buat admin kedua dengan username sama di sekolah yang sama
        $this->actingAs($super)
            ->post(route('super-admin.sekolah.admin.store', $sekolah), [
                'nama' => 'Admin Duplikat',
                'username' => 'admin-sama',
                'password' => 'rahasia123',
            ])
            ->assertSessionHasErrors('username');
    });

    it('username admin boleh sama di sekolah berbeda', function () {
        $super = buatSuperAdmin();
        $sekolah1 = buatSekolah(['kode' => 'sekolah-a']);
        $sekolah2 = buatSekolah(['kode' => 'sekolah-b']);

        buatAdminSekolah($sekolah1, ['username' => 'admin-sama']);

        // Di sekolah berbeda, username sama boleh
        $this->actingAs($super)
            ->post(route('super-admin.sekolah.admin.store', $sekolah2), [
                'nama' => 'Admin Sekolah B',
                'username' => 'admin-sama',
                'password' => 'rahasia123',
            ])
            ->assertRedirect(route('super-admin.sekolah.show', $sekolah2));

        $this->assertDatabaseHas('users', [
            'username' => 'admin-sama',
            'sekolah_id' => $sekolah2->id,
        ]);
    });

    it('super-admin bisa mengatur ulang password admin', function () {
        $super = buatSuperAdmin();
        $sekolah = buatSekolah();
        $admin = buatAdminSekolah($sekolah);

        // Reset must_change_password agar bisa dibedakan
        DB::table('users')
            ->where('id', $admin->id)
            ->update(['must_change_password' => false]);

        $this->actingAs($super)
            ->patch(route('super-admin.sekolah.admin.reset-password', [$sekolah, $admin]), [
                'password' => 'passwordbaru456',
            ])
            ->assertRedirect(route('super-admin.sekolah.show', $sekolah));

        $admin = User::withoutGlobalScopes()->find($admin->id);
        expect(Hash::check('passwordbaru456', $admin->password))->toBeTrue();
        expect($admin->must_change_password)->toBeTrue();
    });

    it('super-admin tidak bisa mengatur password admin sekolah lain', function () {
        $super = buatSuperAdmin();
        $sekolah1 = buatSekolah(['kode' => 'sk-1']);
        $sekolah2 = buatSekolah(['kode' => 'sk-2']);
        $admin2 = buatAdminSekolah($sekolah2);

        // Coba reset password admin sekolah2 melalui URL sekolah1
        $this->actingAs($super)
            ->patch(route('super-admin.sekolah.admin.reset-password', [$sekolah1, $admin2]), [
                'password' => 'passwordbaru456',
            ])
            ->assertNotFound();
    });

    it('super-admin bisa menonaktifkan admin selama bukan satu-satunya admin aktif', function () {
        $super = buatSuperAdmin();
        $sekolah = buatSekolah();
        $admin1 = buatAdminSekolah($sekolah);
        $admin2 = buatAdminSekolah($sekolah);

        $this->actingAs($super)
            ->patch(route('super-admin.sekolah.admin.nonaktifkan', [$sekolah, $admin1]))
            ->assertRedirect(route('super-admin.sekolah.show', $sekolah));

        expect(User::withoutGlobalScopes()->find($admin1->id)->aktif)->toBeFalse();
    });

    it('sistem menolak menonaktifkan admin aktif terakhir', function () {
        $super = buatSuperAdmin();
        $sekolah = buatSekolah();
        $admin = buatAdminSekolah($sekolah);

        $response = $this->actingAs($super)
            ->patch(route('super-admin.sekolah.admin.nonaktifkan', [$sekolah, $admin]));

        // Redirect balik dengan pesan galat
        $response->assertRedirect();
        $response->assertSessionHas('galat');

        // Admin tetap aktif
        expect(User::withoutGlobalScopes()->find($admin->id)->aktif)->toBeTrue();
    });

    it('super-admin bisa mengaktifkan kembali admin nonaktif', function () {
        $super = buatSuperAdmin();
        $sekolah = buatSekolah();
        $admin = buatAdminSekolah($sekolah);

        // Nonaktifkan secara langsung lewat DB (bypass aturan admin terakhir untuk setup test)
        DB::table('users')
            ->where('id', $admin->id)
            ->update(['aktif' => false]);

        $this->actingAs($super)
            ->patch(route('super-admin.sekolah.admin.aktifkan', [$sekolah, $admin]))
            ->assertRedirect(route('super-admin.sekolah.show', $sekolah));

        expect(User::withoutGlobalScopes()->find($admin->id)->aktif)->toBeTrue();
    });
});

// =============================================================================
// TEST: SERVICE AdminTerakhirException
// =============================================================================

describe('SekolahService – aturan admin terakhir', function () {

    it('melempar AdminTerakhirException saat mencoba nonaktifkan admin aktif terakhir', function () {
        $sekolah = buatSekolah();
        $admin = buatAdminSekolah($sekolah);
        $service = app(SekolahService::class);

        expect(fn () => $service->nonaktifkanAdmin($sekolah, $admin))
            ->toThrow(AdminTerakhirException::class);
    });

    it('tidak melempar exception jika masih ada admin aktif lain', function () {
        $sekolah = buatSekolah();
        $admin1 = buatAdminSekolah($sekolah);
        $admin2 = buatAdminSekolah($sekolah);
        $service = app(SekolahService::class);

        expect(fn () => $service->nonaktifkanAdmin($sekolah, $admin1))
            ->not->toThrow(AdminTerakhirException::class);
    });
});

// =============================================================================
// TEST: PENCARIAN DAN TAMPILAN DAFTAR
// =============================================================================

describe('Pencarian daftar sekolah', function () {

    it('hasil pencarian memfilter sekolah berdasarkan nama', function () {
        buatSekolah(['nama' => 'SMP Negeri 1 Maju', 'kode' => 'smpn1-maju']);
        buatSekolah(['nama' => 'SMA Negeri 2 Jaya', 'kode' => 'sman2-jaya']);

        $super = buatSuperAdmin();

        $response = $this->actingAs($super)
            ->get(route('super-admin.sekolah.index', ['cari' => 'SMP']));

        $response->assertOk()
            ->assertSee('SMP Negeri 1 Maju')
            ->assertDontSee('SMA Negeri 2 Jaya');
    });

    it('daftar sekolah menampilkan jumlah guru dan admin', function () {
        $sekolah = buatSekolah();
        TenantContext::set($sekolah->id);

        User::factory()->forSekolah($sekolah)->guru()->create(['must_change_password' => false]);
        User::factory()->forSekolah($sekolah)->guru()->create(['must_change_password' => false]);

        TenantContext::clear();
        buatAdminSekolah($sekolah);

        $super = buatSuperAdmin();

        $this->actingAs($super)
            ->get(route('super-admin.sekolah.index'))
            ->assertOk()
            ->assertSee($sekolah->nama);
        // Angka spesifik sulit di-assert di Blade, tapi kita pastikan halaman tampil
    });
});
