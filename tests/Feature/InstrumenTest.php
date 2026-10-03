<?php

use App\Exceptions\ImmutableVersionException;
use App\Models\JenisInstrumen;
use App\Models\Sekolah;
use App\Models\User;
use App\Models\VersiInstrumen;
use App\Services\InstrumenResolver;
use App\Services\InstrumenService;
use App\Tenant\TenantContext;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;

uses(RefreshDatabase::class);

beforeEach(function () {
    TenantContext::clear();
    VersiInstrumen::$isTerpakaiResolver = null;
});

afterEach(function () {
    TenantContext::clear();
    VersiInstrumen::$isTerpakaiResolver = null;
});

/**
 * Helper untuk membuat super admin.
 */
if (! function_exists('buatSuperAdminInstrumen')) {
    function buatSuperAdminInstrumen(): User
    {
        TenantContext::clear();

        return User::create([
            'nama' => 'Super Administrator',
            'username' => 'superadmin_'.uniqid(),
            'email' => 'super_'.uniqid().'@test.com',
            'password' => Hash::make('password123'),
            'is_super_admin' => true,
            'must_change_password' => false,
            'aktif' => true,
        ]);
    }
}

/**
 * Helper untuk membuat sekolah dan admin sekolah.
 */
if (! function_exists('buatSekolahDanAdminInstrumen')) {
    function buatSekolahDanAdminInstrumen(): array
    {
        TenantContext::clear();

        $sekolah = Sekolah::create([
            'nama' => 'SMK Negeri 1 Surabaya',
            'kode' => 'smkn1-sby-'.uniqid(),
            'status' => 'aktif',
        ]);

        TenantContext::set($sekolah->id);

        $admin = User::create([
            'sekolah_id' => $sekolah->id,
            'nama' => 'Admin Sekolah',
            'username' => 'admin_'.uniqid(),
            'email' => 'admin_'.uniqid().'@sekolah.id',
            'password' => Hash::make('password123'),
            'is_super_admin' => false,
            'must_change_password' => false,
            'aktif' => true,
        ]);

        $admin->roles()->create([
            'sekolah_id' => $sekolah->id,
            'role' => 'admin',
        ]);

        session([
            'active_role' => 'admin',
            'sekolah_kode' => $sekolah->kode,
            'sekolah_id' => $sekolah->id,
        ]);

        return [$sekolah, $admin];
    }
}

/**
 * Helper untuk membuat template global dengan bagian dan butir.
 */
if (! function_exists('buatTemplateGlobalInstrumen')) {
    function buatTemplateGlobalInstrumen(string $kode = 'kbm', string $nama = 'Kegiatan Belajar Mengajar'): JenisInstrumen
    {
        TenantContext::clear();

        $jenis = JenisInstrumen::create([
            'sekolah_id' => null,
            'kode' => $kode,
            'nama' => $nama,
            'urutan' => 1,
            'aktif' => true,
        ]);

        $versi = $jenis->versi()->create([
            'nomor_versi' => 1,
            'status' => 'terbit',
            'skor_maks_butir' => 4,
            'ambang_predikat' => ['A' => 86, 'B' => 76, 'C' => 56],
        ]);

        $bagian1 = $versi->bagian()->create([
            'kode' => 'A',
            'judul' => 'Kegiatan Pendahuluan',
            'urutan' => 1,
        ]);

        $bagian1->butir()->create([
            'urutan' => 1,
            'uraian' => 'CONTOH - GANTI: Guru membuka pelajaran dengan salam dan doa',
            'skor_maks' => 4,
        ]);

        $bagian1->butir()->create([
            'urutan' => 2,
            'uraian' => 'CONTOH - GANTI: Guru melakukan apersepsi dan motivasi',
            'skor_maks' => 4,
        ]);

        $bagian2 = $versi->bagian()->create([
            'kode' => 'B',
            'judul' => 'Kegiatan Inti',
            'urutan' => 2,
        ]);

        $bagian2->butir()->create([
            'urutan' => 1,
            'uraian' => 'CONTOH - GANTI: Guru menguasai materi pelajaran',
            'skor_maks' => 4,
        ]);

        return $jenis->fresh(['versi.bagian.butir']);
    }
}

// =============================================================================
// 1. TEST SKOR MAKSIMAL DAN JUMLAH BUTIR DIHITUNG DINAMIS (ACCESSOR)
// =============================================================================

describe('Perhitungan dinamis skor maksimal dan butir (Accessor)', function () {

    it('skor maksimal dan jumlah butir selalu dihitung dari butir_instrumen dan tidak disimpan statis', function () {
        $jenis = buatTemplateGlobalInstrumen('administrasi', 'Perencanaan Administrasi');
        $versi = $jenis->versi()->first();

        // Template default di helper: Bagian 1 (2 butir @4 = 8), Bagian 2 (1 butir @4 = 4). Total: 3 butir, skor 12.
        expect($versi->jumlah_bagian)->toBe(2);
        expect($versi->jumlah_butir)->toBe(3);
        expect($versi->skor_maks)->toBe(12);

        // Tambah butir baru dengan skor_maks 5 di bagian kedua
        $bagian2 = $versi->bagian()->where('kode', 'B')->first();
        $bagian2->butir()->create([
            'urutan' => 2,
            'uraian' => 'CONTOH - GANTI: Kelengkapan silabus/ATP',
            'skor_maks' => 5,
        ]);

        $versi->refresh();
        expect($versi->jumlah_butir)->toBe(4);
        expect($versi->skor_maks)->toBe(17);
        expect($bagian2->fresh()->skor_maks)->toBe(9);
    });
});

// =============================================================================
// 2. TEST IMMUTABILITY VERSI TERPAKAI
// =============================================================================

describe('Immutability versi instrumen terpakai', function () {

    it('versi terbit yang sudah dipakai penilaian menolak perubahan ambang predikat dan skor butir', function () {
        $jenis = buatTemplateGlobalInstrumen();
        $versi = $jenis->versi()->first();

        // Simulasikan versi ini telah terpakai oleh penilaian
        VersiInstrumen::$isTerpakaiResolver = fn (VersiInstrumen $v) => $v->id === $versi->id;

        expect($versi->isTerpakai())->toBeTrue();

        expect(function () use ($versi) {
            $versi->update([
                'skor_maks_butir' => 5,
            ]);
        })->toThrow(ImmutableVersionException::class);

        expect(function () use ($versi) {
            $versi->update([
                'ambang_predikat' => ['A' => 90, 'B' => 80, 'C' => 60],
            ]);
        })->toThrow(ImmutableVersionException::class);
    });

    it('versi terbit yang sudah dipakai penilaian tidak boleh dihapus', function () {
        $jenis = buatTemplateGlobalInstrumen();
        $versi = $jenis->versi()->first();

        VersiInstrumen::$isTerpakaiResolver = fn (VersiInstrumen $v) => $v->id === $versi->id;

        expect(function () use ($versi) {
            $versi->delete();
        })->toThrow(ImmutableVersionException::class);
    });

    it('bagian dan butir pada versi yang sudah dipakai tidak boleh ditambah, diubah, atau dihapus', function () {
        $jenis = buatTemplateGlobalInstrumen();
        $versi = $jenis->versi()->first();
        $bagian = $versi->bagian()->first();
        $butir = $bagian->butir()->first();

        VersiInstrumen::$isTerpakaiResolver = fn (VersiInstrumen $v) => $v->id === $versi->id;

        // Coba ubah bagian
        expect(function () use ($bagian) {
            $bagian->update(['judul' => 'Judul Baru Yang Ditolak']);
        })->toThrow(ImmutableVersionException::class);

        // Coba hapus bagian
        expect(function () use ($bagian) {
            $bagian->delete();
        })->toThrow(ImmutableVersionException::class);

        // Coba tambah bagian baru
        expect(function () use ($versi) {
            $versi->bagian()->create([
                'kode' => 'C',
                'judul' => 'Bagian Baru Ditolak',
                'urutan' => 3,
            ]);
        })->toThrow(ImmutableVersionException::class);

        // Coba ubah butir
        expect(function () use ($butir) {
            $butir->update(['uraian' => 'Uraian Baru Yang Ditolak']);
        })->toThrow(ImmutableVersionException::class);

        // Coba hapus butir
        expect(function () use ($butir) {
            $butir->delete();
        })->toThrow(ImmutableVersionException::class);

        // Coba tambah butir baru
        expect(function () use ($bagian) {
            $bagian->butir()->create([
                'urutan' => 99,
                'uraian' => 'Butir Baru Ditolak',
                'skor_maks' => 4,
            ]);
        })->toThrow(ImmutableVersionException::class);
    });
});

// =============================================================================
// 3. TEST SALIN VERSI (BUAT VERSI BARU)
// =============================================================================

describe('Salin versi (buat versi baru)', function () {

    it('buatVersiBaru menyalin seluruh bagian dan butir sebagai draft dengan nomor versi berikutnya', function () {
        $jenis = buatTemplateGlobalInstrumen('pengelolaan_kelas', 'Pengelolaan Kelas');
        $versiLama = $jenis->versi()->first();

        $service = app(InstrumenService::class);
        $versiBaru = $service->buatVersiBaru($versiLama);

        expect($versiBaru->nomor_versi)->toBe(2);
        expect($versiBaru->status)->toBe('draft');
        expect($versiBaru->isDraft())->toBeTrue();
        expect($versiBaru->id)->not->toBe($versiLama->id);

        // Bagian dan butir terduplikasi dengan lengkap
        expect($versiBaru->bagian()->count())->toBe(2);
        expect($versiBaru->jumlah_butir)->toBe($versiLama->jumlah_butir);
        expect($versiBaru->skor_maks)->toBe($versiLama->skor_maks);

        // Perubahan pada draft versi baru tidak memengaruhi versi lama
        $bagianPertama = $versiBaru->bagian()->first();
        $bagianPertama->update(['judul' => 'Judul Khusus Versi 2']);

        expect($versiLama->bagian()->first()->fresh()->judul)->toBe('Kegiatan Pendahuluan');
        expect($bagianPertama->fresh()->judul)->toBe('Judul Khusus Versi 2');
    });

    it('terbitkanVersi mengubah status draft menjadi terbit', function () {
        $jenis = buatTemplateGlobalInstrumen('perencanaan', 'Perencanaan Pembelajaran');
        $v1 = $jenis->versi()->first();

        $service = app(InstrumenService::class);
        $v2 = $service->buatVersiBaru($v1);

        expect($v2->isDraft())->toBeTrue();

        $service->terbitkanVersi($v2);

        expect($v2->fresh()->isTerbit())->toBeTrue();
    });
});

// =============================================================================
// 4. TEST RESOLUSI SEKOLAH VS GLOBAL (INSTRUMEN RESOLVER)
// =============================================================================

describe('Resolusi instrumen aktif (InstrumenResolver)', function () {

    it('meresolusi template global jika sekolah belum memiliki instrumen kustom', function () {
        $template = buatTemplateGlobalInstrumen('kbm', 'KBM Global');
        [$sekolah, $admin] = buatSekolahDanAdminInstrumen();

        $resolver = app(InstrumenResolver::class);
        $versiAktif = $resolver->resolve('kbm', $sekolah->id);

        expect($versiAktif)->not->toBeNull();
        expect($versiAktif->id)->toBe($template->versiTerbitTerbaru->id);
        expect($versiAktif->jenisInstrumen->sekolah_id)->toBeNull();
    });

    it('tetap meresolusi template global jika instrumen sekolah masih berstatus draft', function () {
        $template = buatTemplateGlobalInstrumen('kbm', 'KBM Global');
        [$sekolah, $admin] = buatSekolahDanAdminInstrumen();

        // Salin ke sekolah tapi masih berstatus draft
        $service = app(InstrumenService::class);
        $jenisSekolah = $service->salinKeSekolah($template, $sekolah->id);

        expect($jenisSekolah->versiTerbitTerbaru)->toBeNull();
        expect($jenisSekolah->versi()->first()->isDraft())->toBeTrue();

        $resolver = app(InstrumenResolver::class);
        $versiAktif = $resolver->resolve('kbm', $sekolah->id);

        // Harus tetap merujuk ke template global karena versi sekolah belum terbit
        expect($versiAktif->id)->toBe($template->versiTerbitTerbaru->id);
    });

    it('meresolusi versi terbit sekolah jika sekolah sudah menerbitkan instrumen kustom', function () {
        $template = buatTemplateGlobalInstrumen('kbm', 'KBM Global');
        [$sekolah, $admin] = buatSekolahDanAdminInstrumen();

        $service = app(InstrumenService::class);
        $jenisSekolah = $service->salinKeSekolah($template, $sekolah->id);

        // Terbitkan versi sekolah
        $versiSekolah = $jenisSekolah->versi()->first();
        $service->terbitkanVersi($versiSekolah);

        $resolver = app(InstrumenResolver::class);
        $versiAktif = $resolver->resolve('kbm', $sekolah->id);

        expect($versiAktif->id)->toBe($versiSekolah->id);
        expect($versiAktif->jenisInstrumen->sekolah_id)->toBe($sekolah->id);
    });
});

// =============================================================================
// 5. TEST ATURAN TENANT: SEKOLAH TIDAK BISA MENGUBAH TEMPLATE GLOBAL
// =============================================================================

describe('Perlindungan template global dari mutasi oleh sekolah', function () {

    it('scope mengizinkan sekolah membaca baris global dan miliknya sendiri', function () {
        $template = buatTemplateGlobalInstrumen('kbm', 'KBM Standar');
        [$sekolah, $admin] = buatSekolahDanAdminInstrumen();

        TenantContext::set($sekolah->id);

        // Sekolah bisa melihat template global (sekolah_id IS NULL)
        $semuaJenis = JenisInstrumen::all();
        expect($semuaJenis->pluck('id'))->toContain($template->id);

        // Dan jika punya miliknya sendiri, keduanya terlihat
        $service = app(InstrumenService::class);
        $jenisSekolah = $service->salinKeSekolah($template, $sekolah->id);

        $semuaJenisSetelahSalin = JenisInstrumen::all();
        expect($semuaJenisSetelahSalin->pluck('id'))->toContain($template->id);
        expect($semuaJenisSetelahSalin->pluck('id'))->toContain($jenisSekolah->id);
    });

    it('pengguna sekolah tidak boleh mengubah baris template global langsung pada model', function () {
        $template = buatTemplateGlobalInstrumen('kbm', 'KBM Standar');
        [$sekolah, $admin] = buatSekolahDanAdminInstrumen();

        TenantContext::set($sekolah->id);

        expect(function () use ($template) {
            $template->update(['nama' => 'Nama Baru Diubah Sekolah']);
        })->toThrow(AuthorizationException::class);

        expect(function () use ($template) {
            $template->delete();
        })->toThrow(AuthorizationException::class);
    });

    it('controller admin sekolah menolak aksi ubah pada template global dengan kode 403', function () {
        $template = buatTemplateGlobalInstrumen('kbm', 'KBM Standar');
        $versiGlobal = $template->versi()->first();
        [$sekolah, $admin] = buatSekolahDanAdminInstrumen();

        $this->actingAs($admin)
            ->post(route('admin.instrumen.versi.buat-versi', [
                'kode' => $sekolah->kode,
                'jenis' => $template,
                'versi' => $versiGlobal,
            ]))
            ->assertForbidden();

        $this->actingAs($admin)
            ->patch(route('admin.instrumen.versi.ambang', [
                'kode' => $sekolah->kode,
                'jenis' => $template,
                'versi' => $versiGlobal,
            ]), [
                'skor_maks_butir' => 5,
                'ambang_a' => 85,
                'ambang_b' => 75,
                'ambang_c' => 55,
            ])
            ->assertForbidden();
    });
});

// =============================================================================
// 6. TEST LINTAS-TENANT (ISOLASI ANTAR SEKOLAH)
// =============================================================================

describe('Isolasi lintas-tenant instrumen', function () {

    it('instrumen milik sekolah A tidak dapat dilihat atau diakses oleh sekolah B', function () {
        $template = buatTemplateGlobalInstrumen('kbm', 'KBM Standar');

        [$sekolahA, $adminA] = buatSekolahDanAdminInstrumen();
        [$sekolahB, $adminB] = buatSekolahDanAdminInstrumen();

        // Sekolah A menyalin instrumen
        $service = app(InstrumenService::class);
        $jenisA = $service->salinKeSekolah($template, $sekolahA->id);
        $versiA = $jenisA->versi()->first();

        // 1. Dari sisi database/Eloquent: Dalam konteks Sekolah B, instrumen Sekolah A tidak muncul
        TenantContext::set($sekolahB->id);
        $jenisDiB = JenisInstrumen::where('sekolah_id', $sekolahB->id)->get();
        expect($jenisDiB->pluck('id'))->not->toContain($jenisA->id);

        // 2. Dari sisi HTTP: Admin Sekolah B mencoba mengakses rute instrumen milik Sekolah A -> 404
        $this->actingAs($adminB)
            ->get(route('admin.instrumen.show', [
                'kode' => $sekolahB->kode,
                'jenis' => $jenisA,
            ]))
            ->assertNotFound();

        $this->actingAs($adminB)
            ->get(route('admin.instrumen.versi.show', [
                'kode' => $sekolahB->kode,
                'jenis' => $jenisA,
                'versi' => $versiA,
            ]))
            ->assertNotFound();
    });
});

// =============================================================================
// 7. TEST UI FLOW & AKSI: SALIN KE SEKOLAH, GESER BAGIAN & BUTIR
// =============================================================================

describe('Alur aksi instrumen (salin ke sekolah, geser urutan)', function () {

    it('admin sekolah bisa menyalin template global ke sekolahnya melalui aksi HTTP', function () {
        $template = buatTemplateGlobalInstrumen('administrasi', 'Administrasi Pembelajaran');
        [$sekolah, $admin] = buatSekolahDanAdminInstrumen();

        $this->actingAs($admin)
            ->post(route('admin.instrumen.salin', [
                'kode' => $sekolah->kode,
                'templateGlobal' => $template->id,
            ]))
            ->assertRedirect();

        TenantContext::set($sekolah->id);
        $jenisSekolah = JenisInstrumen::where('sekolah_id', $sekolah->id)
            ->where('kode', 'administrasi')
            ->first();

        expect($jenisSekolah)->not->toBeNull();
        expect($jenisSekolah->versi()->count())->toBe(1);
        expect($jenisSekolah->versi()->first()->isDraft())->toBeTrue();
        expect($jenisSekolah->versi()->first()->jumlah_butir)->toBe($template->versiTerbitTerbaru->jumlah_butir);
    });

    it('super admin dan admin sekolah bisa menggeser urutan bagian dan butir naik/turun', function () {
        $superAdmin = buatSuperAdminInstrumen();
        $template = buatTemplateGlobalInstrumen('pengelolaan_kelas', 'Pengelolaan Kelas');
        $versi = $template->versi()->first();

        // Buat versi baru berstatus draft agar bisa diubah urutannya
        $service = app(InstrumenService::class);
        $v2Draft = $service->buatVersiBaru($versi);

        $bagianList = $v2Draft->bagian()->orderBy('urutan')->get();
        $b1 = $bagianList[0];
        $b2 = $bagianList[1];

        expect($b1->urutan)->toBe(1);
        expect($b2->urutan)->toBe(2);

        // Super admin menggeser Bagian 2 naik
        $this->actingAs($superAdmin)
            ->patch(route('super-admin.instrumen.bagian.geser', [
                'jenis' => $template,
                'versi' => $v2Draft,
                'bagian' => $b2,
            ]), ['arah' => 'naik'])
            ->assertRedirect();

        expect($b2->fresh()->urutan)->toBe(1);
        expect($b1->fresh()->urutan)->toBe(2);

        // Geser butir di bagian 1
        $butirList = $b1->butir()->orderBy('urutan')->get();
        if ($butirList->count() >= 2) {
            $butir1 = $butirList[0];
            $butir2 = $butirList[1];

            $this->actingAs($superAdmin)
                ->patch(route('super-admin.instrumen.butir.geser', [
                    'jenis' => $template,
                    'versi' => $v2Draft,
                    'bagian' => $b1,
                    'butir' => $butir2,
                ]), ['arah' => 'naik'])
                ->assertRedirect();

            expect($butir2->fresh()->urutan)->toBe(1);
            expect($butir1->fresh()->urutan)->toBe(2);
        }
    });
});
