<?php

use App\Exports\GuruTemplateExport;
use App\Models\Sekolah;
use App\Models\User;
use App\Models\UserRole;
use App\Services\GuruImportService;
use App\Tenant\TenantContext;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use Maatwebsite\Excel\Concerns\FromArray;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Facades\Excel;

uses(RefreshDatabase::class);

// =============================================================================
// HELPER SETUP
// =============================================================================

function setupSekolahDanAdmin(array $sekolahAttrs = []): array
{
    $sekolah = Sekolah::create(array_merge([
        'nama' => 'SMP Negeri 1 Testing',
        'kode' => 'smpn1-test-'.uniqid(),
        'status' => 'aktif',
    ], $sekolahAttrs));

    TenantContext::set($sekolah->id);

    $admin = User::factory()->forSekolah($sekolah)->admin()->create([
        'nama' => 'Admin Sekolah',
        'username' => 'admin-'.uniqid(),
        'password' => Hash::make('password123'),
        'must_change_password' => false,
        'aktif' => true,
    ]);

    session([
        'active_role' => 'admin',
        'sekolah_kode' => $sekolah->kode,
        'sekolah_id' => $sekolah->id,
    ]);

    return [$sekolah, $admin];
}

// =============================================================================
// TEST: HAK AKSES AREA ADMIN SEKOLAH
// =============================================================================

describe('Hak akses admin sekolah untuk data guru', function () {

    it('admin sekolah bisa mengakses daftar guru', function () {
        [$sekolah, $admin] = setupSekolahDanAdmin();

        $this->actingAs($admin)
            ->get(route('admin.guru.index', ['kode' => $sekolah->kode]))
            ->assertOk()
            ->assertSee('Data Guru & Pengguna Sekolah');
    });

    it('tamu diredirect ke halaman login sekolah', function () {
        $sekolah = Sekolah::create([
            'nama' => 'SMP Test',
            'kode' => 'smp-test-tamu',
            'status' => 'aktif',
        ]);

        $this->get(route('admin.guru.index', ['kode' => $sekolah->kode]))
            ->assertRedirect();
    });

    it('guru biasa tanpa role admin diblokir dari area admin', function () {
        [$sekolah] = setupSekolahDanAdmin();

        $guru = User::factory()->forSekolah($sekolah)->guru()->create([
            'must_change_password' => false,
            'aktif' => true,
        ]);

        session([
            'active_role' => 'guru',
            'sekolah_kode' => $sekolah->kode,
            'sekolah_id' => $sekolah->id,
        ]);

        $this->actingAs($guru)
            ->get(route('admin.guru.index', ['kode' => $sekolah->kode]))
            ->assertForbidden();
    });
});

// =============================================================================
// TEST: CRUD PENGGUNA / GURU
// =============================================================================

describe('CRUD data pengguna oleh admin', function () {

    it('admin bisa menambah pengguna baru dengan role otomatis guru', function () {
        [$sekolah, $admin] = setupSekolahDanAdmin();

        $this->actingAs($admin)
            ->post(route('admin.guru.store', ['kode' => $sekolah->kode]), [
                'nama' => 'Guru Baru Sukses',
                'username' => 'gurubaru',
                'email' => 'gurubaru@test.com',
                'nip' => '199001012015011001',
                'password' => 'rahasia123',
            ])
            ->assertRedirect(route('admin.guru.index', ['kode' => $sekolah->kode]));

        $user = User::where('username', 'gurubaru')->first();
        expect($user)->not->toBeNull();
        expect($user->nama)->toBe('Guru Baru Sukses');
        expect($user->must_change_password)->toBeTrue();
        expect($user->aktif)->toBeTrue();
        expect($user->hasRole('guru'))->toBeTrue();
        expect($user->hasRole('admin'))->toBeFalse();
    });

    it('username harus unik dalam satu sekolah', function () {
        [$sekolah, $admin] = setupSekolahDanAdmin();

        User::factory()->forSekolah($sekolah)->guru()->create([
            'username' => 'guruunik',
            'must_change_password' => false,
        ]);

        $this->actingAs($admin)
            ->post(route('admin.guru.store', ['kode' => $sekolah->kode]), [
                'nama' => 'Guru Duplikat',
                'username' => 'guruunik',
                'password' => 'rahasia123',
            ])
            ->assertSessionHasErrors('username');
    });

    it('username boleh sama di sekolah berbeda', function () {
        [$sekolah1, $admin1] = setupSekolahDanAdmin();
        [$sekolah2, $admin2] = setupSekolahDanAdmin();

        // Di sekolah 1 buat akun dengan username 'gurukembar'
        TenantContext::set($sekolah1->id);
        User::factory()->forSekolah($sekolah1)->guru()->create([
            'username' => 'gurukembar',
            'must_change_password' => false,
        ]);

        // Di sekolah 2, buat akun dengan username 'gurukembar' juga
        TenantContext::set($sekolah2->id);
        session(['sekolah_kode' => $sekolah2->kode, 'sekolah_id' => $sekolah2->id]);

        $this->actingAs($admin2)
            ->post(route('admin.guru.store', ['kode' => $sekolah2->kode]), [
                'nama' => 'Guru Sekolah 2',
                'username' => 'gurukembar',
                'password' => 'rahasia123',
            ])
            ->assertRedirect(route('admin.guru.index', ['kode' => $sekolah2->kode]));

        expect(User::where('username', 'gurukembar')->count())->toBe(1); // 1 per tenant
    });

    it('admin bisa memperbarui profil guru', function () {
        [$sekolah, $admin] = setupSekolahDanAdmin();

        $guru = User::factory()->forSekolah($sekolah)->guru()->create([
            'nama' => 'Nama Lama',
            'email' => 'lama@test.com',
            'must_change_password' => false,
        ]);

        $this->actingAs($admin)
            ->put(route('admin.guru.update', ['kode' => $sekolah->kode, 'guru' => $guru]), [
                'nama' => 'Nama Baru Diubah',
                'email' => 'baru@test.com',
                'nip' => '12345678',
            ])
            ->assertRedirect(route('admin.guru.index', ['kode' => $sekolah->kode]));

        $guru->refresh();
        expect($guru->nama)->toBe('Nama Baru Diubah');
        expect($guru->email)->toBe('baru@test.com');
    });

    it('admin bisa menonaktifkan dan mengaktifkan kembali pengguna', function () {
        [$sekolah, $admin] = setupSekolahDanAdmin();

        $guru = User::factory()->forSekolah($sekolah)->guru()->create([
            'aktif' => true,
            'must_change_password' => false,
        ]);

        // Nonaktifkan
        $this->actingAs($admin)
            ->patch(route('admin.guru.toggle-status', ['kode' => $sekolah->kode, 'guru' => $guru]))
            ->assertRedirect(route('admin.guru.index', ['kode' => $sekolah->kode]));

        expect($guru->fresh()->aktif)->toBeFalse();

        // Aktifkan kembali
        $this->actingAs($admin)
            ->patch(route('admin.guru.toggle-status', ['kode' => $sekolah->kode, 'guru' => $guru]))
            ->assertRedirect(route('admin.guru.index', ['kode' => $sekolah->kode]));

        expect($guru->fresh()->aktif)->toBeTrue();
    });

    it('admin tidak bisa menonaktifkan dirinya sendiri saat login', function () {
        [$sekolah, $admin] = setupSekolahDanAdmin();

        $this->actingAs($admin)
            ->patch(route('admin.guru.toggle-status', ['kode' => $sekolah->kode, 'guru' => $admin]))
            ->assertRedirect();

        expect($admin->fresh()->aktif)->toBeTrue();
    });
});

// =============================================================================
// TEST: PENGATURAN PERAN (SUPERVISOR & GURU) & KEPALA SEKOLAH
// =============================================================================

describe('Pengaturan peran dan kepala sekolah', function () {

    it('toggle supervisor menambahkan peran supervisor dan TIDAK menghapus peran guru', function () {
        [$sekolah, $admin] = setupSekolahDanAdmin();

        $guru = User::factory()->forSekolah($sekolah)->guru()->create([
            'must_change_password' => false,
            'aktif' => true,
        ]);

        expect($guru->hasRole('guru'))->toBeTrue();
        expect($guru->hasRole('supervisor'))->toBeFalse();

        // Berikan peran supervisor
        $this->actingAs($admin)
            ->patch(route('admin.guru.toggle-supervisor', ['kode' => $sekolah->kode, 'guru' => $guru]))
            ->assertRedirect(route('admin.guru.index', ['kode' => $sekolah->kode]));

        $guru->refresh();
        expect($guru->hasRole('supervisor'))->toBeTrue();
        expect($guru->hasRole('guru'))->toBeTrue(); // Peran GURU TETAP UTUH!
    });

    it('toggle supervisor mencabut peran supervisor tanpa menghapus peran guru', function () {
        [$sekolah, $admin] = setupSekolahDanAdmin();

        $guru = User::factory()->forSekolah($sekolah)->supervisorGuru()->create([
            'must_change_password' => false,
            'aktif' => true,
        ]);

        expect($guru->hasRole('supervisor'))->toBeTrue();
        expect($guru->hasRole('guru'))->toBeTrue();

        // Cabut peran supervisor
        $this->actingAs($admin)
            ->patch(route('admin.guru.toggle-supervisor', ['kode' => $sekolah->kode, 'guru' => $guru]))
            ->assertRedirect(route('admin.guru.index', ['kode' => $sekolah->kode]));

        $guru->refresh();
        expect($guru->hasRole('supervisor'))->toBeFalse();
        expect($guru->hasRole('guru'))->toBeTrue(); // Guru tetap ada
    });

    it('toggle guru bisa mencabut peran guru bagi yang memiliki peran supervisor', function () {
        [$sekolah, $admin] = setupSekolahDanAdmin();

        $spv = User::factory()->forSekolah($sekolah)->supervisorGuru()->create([
            'must_change_password' => false,
            'aktif' => true,
        ]);

        // Cabut peran guru
        $this->actingAs($admin)
            ->patch(route('admin.guru.toggle-guru', ['kode' => $sekolah->kode, 'guru' => $spv]))
            ->assertRedirect(route('admin.guru.index', ['kode' => $sekolah->kode]));

        $spv->refresh();
        expect($spv->hasRole('guru'))->toBeFalse();
        expect($spv->hasRole('supervisor'))->toBeTrue();
    });

    it('sistem menolak mencabut peran guru jika pengguna tidak punya peran lain', function () {
        [$sekolah, $admin] = setupSekolahDanAdmin();

        $guru = User::factory()->forSekolah($sekolah)->guru()->create([
            'must_change_password' => false,
            'aktif' => true,
        ]);

        $this->actingAs($admin)
            ->patch(route('admin.guru.toggle-guru', ['kode' => $sekolah->kode, 'guru' => $guru]))
            ->assertRedirect();

        // Peran guru tetap ada karena tidak punya role lain
        expect($guru->fresh()->hasRole('guru'))->toBeTrue();
    });

    it('admin bisa menetapkan kepala sekolah dari pengguna yang berperan supervisor', function () {
        [$sekolah, $admin] = setupSekolahDanAdmin();

        $spv = User::factory()->forSekolah($sekolah)->supervisorGuru()->create([
            'must_change_password' => false,
            'aktif' => true,
        ]);

        $this->actingAs($admin)
            ->patch(route('admin.sekolah.kepala-sekolah', ['kode' => $sekolah->kode]), [
                'kepala_sekolah_id' => $spv->id,
            ])
            ->assertRedirect(route('admin.guru.index', ['kode' => $sekolah->kode]));

        expect($sekolah->fresh()->kepala_sekolah_id)->toBe($spv->id);
    });

    it('sistem menolak menetapkan kepala sekolah dari pengguna yang BUKAN supervisor', function () {
        [$sekolah, $admin] = setupSekolahDanAdmin();

        $guruBiasa = User::factory()->forSekolah($sekolah)->guru()->create([
            'must_change_password' => false,
            'aktif' => true,
        ]);

        $this->actingAs($admin)
            ->patch(route('admin.sekolah.kepala-sekolah', ['kode' => $sekolah->kode]), [
                'kepala_sekolah_id' => $guruBiasa->id,
            ])
            ->assertRedirect();

        expect($sekolah->fresh()->kepala_sekolah_id)->toBeNull();
    });

    it('sistem menolak mencabut peran supervisor dari Kepala Sekolah saat ini', function () {
        [$sekolah, $admin] = setupSekolahDanAdmin();

        $ks = User::factory()->forSekolah($sekolah)->supervisorGuru()->create([
            'must_change_password' => false,
            'aktif' => true,
        ]);

        $sekolah->update(['kepala_sekolah_id' => $ks->id]);

        $this->actingAs($admin)
            ->patch(route('admin.guru.toggle-supervisor', ['kode' => $sekolah->kode, 'guru' => $ks]))
            ->assertRedirect();

        expect($ks->fresh()->hasRole('supervisor'))->toBeTrue();
    });
});

// =============================================================================
// TEST: ATUR ULANG PASSWORD OLEH ADMIN
// =============================================================================

describe('Atur ulang password oleh admin', function () {

    it('admin bisa mengatur ulang password pengguna dan memaksa ganti password', function () {
        [$sekolah, $admin] = setupSekolahDanAdmin();

        $guru = User::factory()->forSekolah($sekolah)->guru()->create([
            'must_change_password' => false,
            'aktif' => true,
        ]);

        $this->actingAs($admin)
            ->patch(route('admin.guru.reset-password', ['kode' => $sekolah->kode, 'guru' => $guru]), [
                'password' => 'PasswordBaru99!',
                'password_confirmation' => 'PasswordBaru99!',
            ])
            ->assertRedirect(route('admin.guru.index', ['kode' => $sekolah->kode]));

        $guru->refresh();
        expect(Hash::check('PasswordBaru99!', $guru->password))->toBeTrue();
        expect($guru->must_change_password)->toBeTrue();
    });
});

// =============================================================================
// TEST: IMPOR EXCEL (3 LANGKAH, DRY RUN, UPSERT, KEAMANAN)
// =============================================================================

describe('Fitur impor data guru via Excel', function () {

    it('unduh template menghasilkan sheet Data Guru dengan kolom yang tepat', function () {
        [$sekolah, $admin] = setupSekolahDanAdmin();

        $template = new GuruTemplateExport;
        expect($template->title())->toBe('Data Guru');
        expect($template->headings())->toBe([
            'nama',
            'username',
            'email',
            'nip',
            'nuptk',
            'password',
        ]);
        // Tidak ada kolom role
        expect($template->headings())->not->toContain('role');

        $this->actingAs($admin)
            ->get(route('admin.guru.import.template', ['kode' => $sekolah->kode]))
            ->assertOk()
            ->assertHeader('content-disposition');
    });

    it('dry run validasi tidak menulis data apa pun ke database', function () {
        [$sekolah, $admin] = setupSekolahDanAdmin();
        Storage::fake('private');

        $jumlahUserAwal = User::count();
        $jumlahRoleAwal = UserRole::count();

        // Buat file excel dummy
        $export = new class implements FromArray, WithHeadings
        {
            public function headings(): array
            {
                return ['nama', 'username', 'email', 'nip', 'nuptk', 'password'];
            }

            public function array(): array
            {
                return [
                    ['Guru Uji Coba', 'guru.ujicoba', 'uji@test.com', '-', '-', 'Password123!'],
                ];
            }
        };

        Excel::store($export, 'temp_test.xlsx', 'private');

        // Panggil service dry run
        $service = app(GuruImportService::class);
        $hasil = $service->validasiDanPratinjau('temp_test.xlsx', $sekolah->id);

        expect($hasil['total_baris'])->toBe(1);
        expect($hasil['jumlah_baru'])->toBe(1);
        expect($hasil['jumlah_galat'])->toBe(0);

        // Pastikan tidak ada data yang masuk ke database
        expect(User::count())->toBe($jumlahUserAwal);
        expect(UserRole::count())->toBe($jumlahRoleAwal);
    });

    it('validasi menolak akun baru jika password kosong atau kurang dari 8 karakter', function () {
        [$sekolah, $admin] = setupSekolahDanAdmin();
        Storage::fake('private');

        $export = new class implements FromArray, WithHeadings
        {
            public function headings(): array
            {
                return ['nama', 'username', 'email', 'nip', 'nuptk', 'password'];
            }

            public function array(): array
            {
                return [
                    ['Guru Pendek', 'guru.pendek', '', '', '', '123'], // Kurang dari 8
                    ['Guru Kosong', 'guru.kosong', '', '', '', ''],    // Kosong
                ];
            }
        };

        Excel::store($export, 'temp_invalid.xlsx', 'private');

        $service = app(GuruImportService::class);
        $hasil = $service->validasiDanPratinjau('temp_invalid.xlsx', $sekolah->id);

        expect($hasil['jumlah_galat'])->toBe(2);
        expect($hasil['jumlah_baru'])->toBe(0);
        expect($hasil['bisa_diproses'])->toBeFalse();
    });

    it('upsert impor membuat akun baru dan tidak menimpa password/role akun lama', function () {
        [$sekolah, $admin] = setupSekolahDanAdmin();
        Storage::fake('private');

        // Buat akun lama dengan peran supervisor dan password khusus
        $passwordLamaHash = Hash::make('PasswordLamaTetap123!');
        $akunLama = User::factory()->forSekolah($sekolah)->supervisorGuru()->create([
            'nama' => 'Guru Lama Original',
            'username' => 'guru.lama',
            'email' => 'lama@sekolah.sch.id',
            'password' => $passwordLamaHash,
            'must_change_password' => false,
            'aktif' => true,
        ]);

        $export = new class implements FromArray, WithHeadings
        {
            public function headings(): array
            {
                return ['nama', 'username', 'email', 'nip', 'nuptk', 'password'];
            }

            public function array(): array
            {
                return [
                    // Akun lama: ganti nama dan NIP, password coba diubah ke 'PasswordBaruDicoba!'
                    ['Guru Lama Diperbarui', 'guru.lama', 'lama_baru@sekolah.sch.id', '19999999', '', 'PasswordBaruDicoba!'],
                    // Akun baru
                    ['Guru Baru Ditambah', 'guru.fresh', 'fresh@sekolah.sch.id', '', '', 'PasswordBaru123!'],
                ];
            }
        };

        Excel::store($export, 'temp_upsert.xlsx', 'private');

        $service = app(GuruImportService::class);
        $batch = $service->prosesImpor('temp_upsert.xlsx', $sekolah->id, $admin->id, 'Data_Guru.xlsx');

        expect($batch->sukses)->toBe(2);
        expect($batch->gagal)->toBe(0);

        // Cek Akun Baru
        $akunBaru = User::where('username', 'guru.fresh')->first();
        expect($akunBaru)->not->toBeNull();
        expect($akunBaru->hasRole('guru'))->toBeTrue();
        expect($akunBaru->must_change_password)->toBeTrue();
        expect(Hash::check('PasswordBaru123!', $akunBaru->password))->toBeTrue();

        // Cek Akun Lama:
        $akunLama->refresh();
        expect($akunLama->nama)->toBe('Guru Lama Diperbarui');
        expect($akunLama->email)->toBe('lama_baru@sekolah.sch.id');
        expect($akunLama->nip)->toBe('19999999');

        // PASSWORD AKUN LAMA TIDAK BERUBAH!
        expect(Hash::check('PasswordLamaTetap123!', $akunLama->password))->toBeTrue();
        expect(Hash::check('PasswordBaruDicoba!', $akunLama->password))->toBeFalse();

        // ROLE SUPERVISOR AKUN LAMA TIDAK HILANG!
        expect($akunLama->hasRole('supervisor'))->toBeTrue();
        expect($akunLama->hasRole('guru'))->toBeTrue();

        // File sementara harus terhapus
        expect(Storage::disk('private')->exists('temp_upsert.xlsx'))->toBeFalse();
    });

    it('import_batches tercatat dan tidak memuat teks password pada kolom errors', function () {
        [$sekolah, $admin] = setupSekolahDanAdmin();
        Storage::fake('private');

        $export = new class implements FromArray, WithHeadings
        {
            public function headings(): array
            {
                return ['nama', 'username', 'email', 'nip', 'nuptk', 'password'];
            }

            public function array(): array
            {
                return [
                    ['Guru Gagal', 'guru.gagal', 'bukan-email', '', '', 'pendek'],
                ];
            }
        };

        Excel::store($export, 'temp_error.xlsx', 'private');

        $service = app(GuruImportService::class);
        $batch = $service->prosesImpor('temp_error.xlsx', $sekolah->id, $admin->id, 'file_bermasalah.xlsx');

        expect($batch->gagal)->toBe(1);
        expect($batch->errors)->not->toBeNull();

        $errorsJson = json_encode($batch->errors);
        // Pastikan tidak ada kata sandi yang bocor
        expect($errorsJson)->not->toContain('pendek');
        expect($errorsJson)->toContain('Format email tidak valid.');
        expect($errorsJson)->toContain('Password untuk akun baru minimal 8 karakter.');
    });
});

// =============================================================================
// TEST: ISOLASI LINTAS-TENANT
// =============================================================================

describe('Isolasi lintas-tenant data guru', function () {

    it('admin sekolah B tidak bisa melihat atau mengubah guru sekolah A', function () {
        [$sekolahA, $adminA] = setupSekolahDanAdmin(['kode' => 'sk-a']);
        [$sekolahB, $adminB] = setupSekolahDanAdmin(['kode' => 'sk-b']);

        // Buat guru di sekolah A
        TenantContext::set($sekolahA->id);
        $guruA = User::factory()->forSekolah($sekolahA)->guru()->create([
            'nama' => 'Guru Sekolah A',
            'username' => 'guru.a',
            'must_change_password' => false,
        ]);

        // Login sebagai Admin Sekolah B
        TenantContext::set($sekolahB->id);
        session(['sekolah_kode' => $sekolahB->kode, 'sekolah_id' => $sekolahB->id]);

        // Coba akses edit/update guru A lewat URL sekolah B -> Wajib 404 (karena global scope sekolah B)
        $this->actingAs($adminB)
            ->put(route('admin.guru.update', ['kode' => $sekolahB->kode, 'guru' => $guruA]), [
                'nama' => 'Nama Bajakan',
            ])
            ->assertNotFound();

        // Coba toggle status guru A lewat sekolah B -> 404
        $this->actingAs($adminB)
            ->patch(route('admin.guru.toggle-status', ['kode' => $sekolahB->kode, 'guru' => $guruA]))
            ->assertNotFound();

        // Nama guru A tidak berubah
        TenantContext::set($sekolahA->id);
        expect($guruA->fresh()->nama)->toBe('Guru Sekolah A');
    });

    it('admin sekolah B tidak bisa mengakses area guru sekolah A', function () {
        [$sekolahA, $adminA] = setupSekolahDanAdmin(['kode' => 'sekolah-a']);
        [$sekolahB, $adminB] = setupSekolahDanAdmin(['kode' => 'sekolah-b']);

        // Admin B mencoba membuka halaman sekolah A di URL
        $this->actingAs($adminB)
            ->get(route('admin.guru.index', ['kode' => $sekolahA->kode]))
            ->assertRedirect(); // Diredirect karena sekolah_id user beda dengan URL
    });
});
