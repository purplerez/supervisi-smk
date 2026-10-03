<?php

use App\Models\JenisInstrumen;
use App\Models\Penilaian;
use App\Models\Penugasan;
use App\Models\Periode;
use App\Models\Sekolah;
use App\Models\User;
use App\Services\PenugasanService;
use App\Services\PeriodeService;
use App\Tenant\TenantContext;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;

uses(RefreshDatabase::class);

beforeEach(function () {
    TenantContext::clear();

    // Pastikan 4 jenis instrumen global ada untuk pembuatan 4 penilaian otomatis
    $instrumenCodes = [
        'kbm' => 'Kegiatan Belajar Mengajar',
        'administrasi' => 'Administrasi Guru',
        'pengelolaan_kelas' => 'Pengelolaan Kelas',
        'perencanaan' => 'Perencanaan Pembelajaran',
    ];

    foreach ($instrumenCodes as $kode => $nama) {
        JenisInstrumen::firstOrCreate(
            ['kode' => $kode, 'sekolah_id' => null],
            ['nama' => $nama, 'urutan' => 1, 'aktif' => true]
        );
    }
});

afterEach(function () {
    TenantContext::clear();
});

/**
 * Helper untuk setup sekolah, admin, supervisor, dan guru.
 */
function setupLingkunganSekolah(): array
{
    TenantContext::clear();

    $sekolah = Sekolah::create([
        'nama' => 'SMK Negeri 1 Testing',
        'kode' => 'smkn1-test-'.uniqid(),
        'status' => 'aktif',
    ]);

    TenantContext::set($sekolah->id);

    $admin = User::create([
        'sekolah_id' => $sekolah->id,
        'nama' => 'Admin Sekolah',
        'username' => 'admin_'.uniqid(),
        'email' => 'admin_'.uniqid().'@test.com',
        'password' => Hash::make('password123'),
        'must_change_password' => false,
        'aktif' => true,
    ]);
    $admin->roles()->create(['sekolah_id' => $sekolah->id, 'role' => 'admin']);

    $supervisor = User::create([
        'sekolah_id' => $sekolah->id,
        'nama' => 'Budi Supervisor',
        'username' => 'spv_'.uniqid(),
        'password' => Hash::make('password123'),
        'must_change_password' => false,
        'aktif' => true,
    ]);
    $supervisor->roles()->create(['sekolah_id' => $sekolah->id, 'role' => 'supervisor']);

    $guruA = User::create([
        'sekolah_id' => $sekolah->id,
        'nama' => 'Siti Guru Matematika',
        'username' => 'guru_a_'.uniqid(),
        'password' => Hash::make('password123'),
        'must_change_password' => false,
        'aktif' => true,
    ]);
    $guruA->roles()->create(['sekolah_id' => $sekolah->id, 'role' => 'guru']);

    $guruB = User::create([
        'sekolah_id' => $sekolah->id,
        'nama' => 'Joko Guru Bahasa',
        'username' => 'guru_b_'.uniqid(),
        'password' => Hash::make('password123'),
        'must_change_password' => false,
        'aktif' => true,
    ]);
    $guruB->roles()->create(['sekolah_id' => $sekolah->id, 'role' => 'guru']);

    session([
        'active_role' => 'admin',
        'sekolah_kode' => $sekolah->kode,
        'sekolah_id' => $sekolah->id,
    ]);

    return compact('sekolah', 'admin', 'supervisor', 'guruA', 'guruB');
}

// =============================================================================
// 1. TEST PEMBUATAN 4 BARIS PENILAIAN OTOMATIS SAAT PENUGASAN DIBUAT
// =============================================================================

describe('Pembuatan 4 baris penilaian otomatis pada penugasan', function () {

    it('otomatis membuat 4 baris penilaian berstatus belum saat penugasan dibuat', function () {
        extract(setupLingkunganSekolah());

        $periode = Periode::create([
            'sekolah_id' => $sekolah->id,
            'nama' => 'Supervisi Semester Ganjil 2026/2027',
            'tahun_ajaran' => '2026/2027',
            'semester' => 'ganjil',
            'tanggal_mulai' => now()->toDateString(),
            'tanggal_selesai' => now()->addMonths(2)->toDateString(),
            'status' => 'aktif',
        ]);

        $penugasanService = app(PenugasanService::class);
        $hasil = $penugasanService->buatPenugasan($periode, $supervisor->id, [$guruA->id]);

        expect($hasil['penugasan'])->toHaveCount(1);
        $penugasan = $hasil['penugasan'][0];

        // Harus ada tepat 4 baris penilaian untuk penugasan ini
        $penilaian = Penilaian::where('penugasan_id', $penugasan->id)->get();
        expect($penilaian)->toHaveCount(4);

        // Keempat baris harus berstatus 'belum' dan versi_instrumen_id bernilai null
        foreach ($penilaian as $pen) {
            expect($pen->status)->toBe('belum');
            expect($pen->isBelum())->toBeTrue();
            expect($pen->versi_instrumen_id)->toBeNull();
            expect($pen->ulid)->not->toBeEmpty();
            expect($pen->sekolah_id)->toBe($sekolah->id);
        }

        // Memastikan masing-masing jenis instrumen ada satu
        $kodes = $penilaian->map(fn ($p) => $p->jenisInstrumen->kode)->sort()->values()->all();
        expect($kodes)->toBe(['administrasi', 'kbm', 'pengelolaan_kelas', 'perencanaan']);
    });
});

// =============================================================================
// 2. TEST HANYA SATU PERIODE AKTIF PER SEKOLAH
// =============================================================================

describe('Aturan satu periode aktif per sekolah', function () {

    it('menolak pengaktifan periode kedua jika sudah ada periode berstatus aktif di sekolah', function () {
        extract(setupLingkunganSekolah());

        $periode1 = Periode::create([
            'sekolah_id' => $sekolah->id,
            'nama' => 'Periode Aktif 1',
            'tahun_ajaran' => '2026/2027',
            'tanggal_mulai' => now()->toDateString(),
            'tanggal_selesai' => now()->addMonth()->toDateString(),
            'status' => 'aktif',
        ]);

        $periode2 = Periode::create([
            'sekolah_id' => $sekolah->id,
            'nama' => 'Periode Draf 2',
            'tahun_ajaran' => '2026/2027',
            'tanggal_mulai' => now()->addMonths(2)->toDateString(),
            'tanggal_selesai' => now()->addMonths(3)->toDateString(),
            'status' => 'draft',
        ]);

        $periodeService = app(PeriodeService::class);

        // Mencoba mengaktifkan periode2 harus melempar ValidationException
        expect(function () use ($periodeService, $periode2) {
            $periodeService->aktifkanPeriode($periode2);
        })->toThrow(ValidationException::class);

        // Periode 2 tetap berstatus draft
        expect($periode2->fresh()->status)->toBe('draft');

        // Jika periode 1 ditutup, maka periode 2 bisa diaktifkan
        $periodeService->tutupPeriode($periode1);
        expect($periode1->fresh()->status)->toBe('ditutup');

        $periodeService->aktifkanPeriode($periode2, konfirmasiLanjut: true);
        expect($periode2->fresh()->status)->toBe('aktif');
    });
});

// =============================================================================
// 3. TEST DAFTAR GURU BELUM PUNYA PENILAI & PERINGATAN AKTIVASI
// =============================================================================

describe('Daftar guru belum punya penilai dan alur aktivasi periode', function () {

    it('mendeteksi dengan benar daftar guru aktif yang belum ditugaskan pada periode', function () {
        extract(setupLingkunganSekolah());

        $periode = Periode::create([
            'sekolah_id' => $sekolah->id,
            'nama' => 'Periode Supervisi',
            'tahun_ajaran' => '2026/2027',
            'tanggal_mulai' => now()->toDateString(),
            'tanggal_selesai' => now()->addMonth()->toDateString(),
            'status' => 'draft',
        ]);

        $periodeService = app(PeriodeService::class);
        $penugasanService = app(PenugasanService::class);

        // Awalnya guruA dan guruB belum punya penilai
        $belum = $periodeService->guruBelumPunyaPenilai($periode);
        expect($belum->pluck('id')->all())->toContain($guruA->id, $guruB->id);

        // Tugaskan guruA
        $penugasanService->buatPenugasan($periode, $supervisor->id, [$guruA->id]);

        // Sekarang hanya guruB yang tersisa
        $belumSetelah = $periodeService->guruBelumPunyaPenilai($periode);
        expect($belumSetelah->pluck('id')->all())->not->toContain($guruA->id);
        expect($belumSetelah->pluck('id')->all())->toContain($guruB->id);
    });

    it('memberikan peringatan saat mengaktifkan periode yang masih memiliki guru belum ditugaskan', function () {
        extract(setupLingkunganSekolah());

        $periode = Periode::create([
            'sekolah_id' => $sekolah->id,
            'nama' => 'Periode Supervisi Baru',
            'tahun_ajaran' => '2026/2027',
            'tanggal_mulai' => now()->toDateString(),
            'tanggal_selesai' => now()->addMonth()->toDateString(),
            'status' => 'draft',
        ]);

        $periodeService = app(PeriodeService::class);

        // Aktivasi tanpa konfirmasi -> meminta konfirmasi
        $hasil = $periodeService->aktifkanPeriode($periode, konfirmasiLanjut: false);
        expect($hasil['konfirmasi_diperlukan'])->toBeTrue();
        expect($hasil['sukses'])->toBeFalse();
        expect($hasil['guru_belum']->count())->toBeGreaterThan(0);
        expect($periode->fresh()->status)->toBe('draft');

        // Aktivasi dengan konfirmasi -> berhasil
        $hasilKonfirmasi = $periodeService->aktifkanPeriode($periode, konfirmasiLanjut: true);
        expect($hasilKonfirmasi['sukses'])->toBeTrue();
        expect($periode->fresh()->status)->toBe('aktif');
    });
});

// =============================================================================
// 4. TEST ATURAN GANTI PENILAI & HAPUS PENUGASAN (HANYA SAAT 4 PENILAIAN BELUM)
// =============================================================================

describe('Aturan ganti penilai dan hapus penugasan', function () {

    it('bisa mengganti penilai dan menghapus penugasan selama 4 penilaian masih berstatus belum', function () {
        extract(setupLingkunganSekolah());

        $supervisor2 = User::create([
            'sekolah_id' => $sekolah->id,
            'nama' => 'Dewi Supervisor 2',
            'username' => 'spv2_'.uniqid(),
            'password' => Hash::make('password123'),
            'must_change_password' => false,
            'aktif' => true,
        ]);
        $supervisor2->roles()->create(['sekolah_id' => $sekolah->id, 'role' => 'supervisor']);

        $periode = Periode::create([
            'sekolah_id' => $sekolah->id,
            'nama' => 'Periode Supervisi',
            'tahun_ajaran' => '2026/2027',
            'tanggal_mulai' => now()->toDateString(),
            'tanggal_selesai' => now()->addMonth()->toDateString(),
            'status' => 'aktif',
        ]);

        $penugasanService = app(PenugasanService::class);
        $hasil = $penugasanService->buatPenugasan($periode, $supervisor->id, [$guruA->id]);
        $penugasan = $hasil['penugasan'][0];

        expect($penugasan->bisaDiubahAtauDihapus())->toBeTrue();

        // Ganti penilai ke supervisor2
        $penugasanService->gantiPenilai($penugasan, $supervisor2->id);
        expect($penugasan->fresh()->penilai_id)->toBe($supervisor2->id);

        // Hapus penugasan
        $penugasanService->hapusPenugasan($penugasan);
        expect(Penugasan::find($penugasan->id))->toBeNull();
        expect(Penilaian::where('penugasan_id', $penugasan->id)->count())->toBe(0);
    });

    it('menolak ganti penilai dan hapus penugasan jika salah satu penilaian sudah dimulai (draft/final)', function () {
        extract(setupLingkunganSekolah());

        $supervisor2 = User::create([
            'sekolah_id' => $sekolah->id,
            'nama' => 'Supervisor Cadangan',
            'username' => 'spv_cad_'.uniqid(),
            'password' => Hash::make('password123'),
            'must_change_password' => false,
            'aktif' => true,
        ]);
        $supervisor2->roles()->create(['sekolah_id' => $sekolah->id, 'role' => 'supervisor']);

        $periode = Periode::create([
            'sekolah_id' => $sekolah->id,
            'nama' => 'Periode Supervisi',
            'tahun_ajaran' => '2026/2027',
            'tanggal_mulai' => now()->toDateString(),
            'tanggal_selesai' => now()->addMonth()->toDateString(),
            'status' => 'aktif',
        ]);

        $penugasanService = app(PenugasanService::class);
        $hasil = $penugasanService->buatPenugasan($periode, $supervisor->id, [$guruA->id]);
        $penugasan = $hasil['penugasan'][0];

        // Ubah salah satu penilaian menjadi 'draft'
        $penilaianPertama = $penugasan->penilaian()->first();
        $penilaianPertama->update(['status' => 'draft']);

        expect($penugasan->fresh()->bisaDiubahAtauDihapus())->toBeFalse();

        // Ganti penilai ditolak
        expect(function () use ($penugasanService, $penugasan, $supervisor2) {
            $penugasanService->gantiPenilai($penugasan, $supervisor2->id);
        })->toThrow(DomainException::class);

        // Hapus penugasan ditolak
        expect(function () use ($penugasanService, $penugasan) {
            $penugasanService->hapusPenugasan($penugasan);
        })->toThrow(DomainException::class);

        // Data tetap utuh
        expect($penugasan->fresh()->penilai_id)->toBe($supervisor->id);
    });
});

// =============================================================================
// 5. TEST VALIDASI: PENILAI TIDAK BOLEH SAMA DENGAN GURU
// =============================================================================

describe('Validasi penilai tidak boleh sama dengan guru', function () {

    it('menolak penugasan jika penilai adalah guru itu sendiri', function () {
        extract(setupLingkunganSekolah());

        // Buat user yang memiliki peran ganda: guru DAN supervisor
        $userGanda = User::create([
            'sekolah_id' => $sekolah->id,
            'nama' => 'Kepala Lab Guru & Supervisor',
            'username' => 'ganda_'.uniqid(),
            'password' => Hash::make('password123'),
            'must_change_password' => false,
            'aktif' => true,
        ]);
        $userGanda->roles()->create(['sekolah_id' => $sekolah->id, 'role' => 'guru']);
        $userGanda->roles()->create(['sekolah_id' => $sekolah->id, 'role' => 'supervisor']);

        $periode = Periode::create([
            'sekolah_id' => $sekolah->id,
            'nama' => 'Periode Supervisi',
            'tahun_ajaran' => '2026/2027',
            'tanggal_mulai' => now()->toDateString(),
            'tanggal_selesai' => now()->addMonth()->toDateString(),
            'status' => 'aktif',
        ]);

        $penugasanService = app(PenugasanService::class);

        // Mencoba menilai diri sendiri
        expect(function () use ($penugasanService, $periode, $userGanda) {
            $penugasanService->buatPenugasan($periode, $userGanda->id, [$userGanda->id]);
        })->toThrow(ValidationException::class);
    });
});

// =============================================================================
// 6. TEST PERINGATAN SALING MENILAI
// =============================================================================

describe('Peringatan saling menilai antar supervisor', function () {

    it('mengizinkan saling menilai pada periode yang sama namun menampilkan peringatan', function () {
        extract(setupLingkunganSekolah());

        // Buat supervisor kedua yang juga berperan guru
        $supervisor2 = User::create([
            'sekolah_id' => $sekolah->id,
            'nama' => 'Supervisor Budi 2',
            'username' => 'spv2_'.uniqid(),
            'password' => Hash::make('password123'),
            'must_change_password' => false,
            'aktif' => true,
        ]);
        $supervisor2->roles()->create(['sekolah_id' => $sekolah->id, 'role' => 'supervisor']);
        $supervisor2->roles()->create(['sekolah_id' => $sekolah->id, 'role' => 'guru']);

        // Jadikan supervisor1 juga memiliki peran guru
        $supervisor->roles()->create(['sekolah_id' => $sekolah->id, 'role' => 'guru']);

        $periode = Periode::create([
            'sekolah_id' => $sekolah->id,
            'nama' => 'Periode Supervisi',
            'tahun_ajaran' => '2026/2027',
            'tanggal_mulai' => now()->toDateString(),
            'tanggal_selesai' => now()->addMonth()->toDateString(),
            'status' => 'aktif',
        ]);

        $penugasanService = app(PenugasanService::class);

        // 1. Supervisor 1 menilai Supervisor 2 (belum saling menilai)
        $hasil1 = $penugasanService->buatPenugasan($periode, $supervisor->id, [$supervisor2->id]);
        expect($hasil1['peringatan'])->toBeEmpty();

        // 2. Supervisor 2 menilai Supervisor 1 (terjadi saling menilai!)
        $hasil2 = $penugasanService->buatPenugasan($periode, $supervisor2->id, [$supervisor->id]);

        // Penugasan tetap BERHASIL tersimpan
        expect($hasil2['penugasan'])->toHaveCount(1);

        // Tetapi menghasilkan teks peringatan saling menilai
        expect($hasil2['peringatan'])->not->toBeEmpty();
        expect($hasil2['peringatan'][0])->toContain('saling menilai');
    });
});

// =============================================================================
// 7. TEST VALIDASI LINTAS-SEKOLAH & ISOLASI TENANT
// =============================================================================

describe('Validasi lintas-sekolah dan isolasi tenant penugasan', function () {

    it('menolak penugasan jika supervisor atau guru berasal dari sekolah yang berbeda', function () {
        extract(setupLingkunganSekolah());

        // Buat Sekolah B dengan guru dan supervisor miliknya
        TenantContext::clear();
        $sekolahB = Sekolah::create([
            'nama' => 'SMK B',
            'kode' => 'smk-b-'.uniqid(),
            'status' => 'aktif',
        ]);
        TenantContext::set($sekolahB->id);

        $guruSekolahB = User::create([
            'sekolah_id' => $sekolahB->id,
            'nama' => 'Guru Sekolah B',
            'username' => 'guru_b_sekolah_b_'.uniqid(),
            'password' => Hash::make('password123'),
            'must_change_password' => false,
            'aktif' => true,
        ]);
        $guruSekolahB->roles()->create(['sekolah_id' => $sekolahB->id, 'role' => 'guru']);

        // Kembali ke konteks Sekolah A
        TenantContext::set($sekolah->id);

        $periode = Periode::create([
            'sekolah_id' => $sekolah->id,
            'nama' => 'Periode Sekolah A',
            'tahun_ajaran' => '2026/2027',
            'tanggal_mulai' => now()->toDateString(),
            'tanggal_selesai' => now()->addMonth()->toDateString(),
            'status' => 'aktif',
        ]);

        $penugasanService = app(PenugasanService::class);

        // Menugaskan guru dari Sekolah B pada periode Sekolah A harus gagal
        expect(function () use ($penugasanService, $periode, $supervisor, $guruSekolahB) {
            $penugasanService->buatPenugasan($periode, $supervisor->id, [$guruSekolahB->id]);
        })->toThrow(ModelNotFoundException::class);
    });

    it('penugasan sekolah A tidak dapat dilihat atau diakses oleh admin sekolah B (404)', function () {
        extract(setupLingkunganSekolah());

        $periodeA = Periode::create([
            'sekolah_id' => $sekolah->id,
            'nama' => 'Periode Sekolah A',
            'tahun_ajaran' => '2026/2027',
            'tanggal_mulai' => now()->toDateString(),
            'tanggal_selesai' => now()->addMonth()->toDateString(),
            'status' => 'aktif',
        ]);

        $penugasanService = app(PenugasanService::class);
        $hasil = $penugasanService->buatPenugasan($periodeA, $supervisor->id, [$guruA->id]);
        $penugasanA = $hasil['penugasan'][0];

        // Setup Admin Sekolah B
        TenantContext::clear();
        $sekolahB = Sekolah::create([
            'nama' => 'SMK B',
            'kode' => 'smk-b-'.uniqid(),
            'status' => 'aktif',
        ]);
        TenantContext::set($sekolahB->id);

        $adminB = User::create([
            'sekolah_id' => $sekolahB->id,
            'nama' => 'Admin B',
            'username' => 'admin_b_'.uniqid(),
            'password' => Hash::make('password123'),
            'must_change_password' => false,
            'aktif' => true,
        ]);
        $adminB->roles()->create(['sekolah_id' => $sekolahB->id, 'role' => 'admin']);

        session([
            'active_role' => 'admin',
            'sekolah_kode' => $sekolahB->kode,
            'sekolah_id' => $sekolahB->id,
        ]);

        // 1. Query Eloquent dari konteks Sekolah B tidak memuat penugasan Sekolah A
        $daftarDiB = Penugasan::where('sekolah_id', $sekolahB->id)->get();
        expect($daftarDiB->pluck('id'))->not->toContain($penugasanA->id);

        // 2. Akses HTTP dari admin Sekolah B ke penugasan Sekolah A menghasilkan 404
        $this->actingAs($adminB)
            ->delete(route('admin.penugasan.destroy', [
                'kode' => $sekolahB->kode,
                'penugasan' => $penugasanA,
            ]))
            ->assertNotFound();
    });
});
