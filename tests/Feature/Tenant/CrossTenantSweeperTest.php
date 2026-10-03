<?php

use App\Livewire\Supervisor\FormPenilaian;
use App\Models\BagianInstrumen;
use App\Models\ButirInstrumen;
use App\Models\JenisInstrumen;
use App\Models\Penilaian;
use App\Models\Penugasan;
use App\Models\Periode;
use App\Models\Sekolah;
use App\Models\SuratTugas;
use App\Models\User;
use App\Models\VersiInstrumen;
use App\Tenant\TenantContext;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Routing\Route;
use Illuminate\Routing\RouteCollection;
use Illuminate\Support\Facades\Hash;
use Livewire\Livewire;

uses(RefreshDatabase::class);

beforeEach(function () {
    TenantContext::clear();
});

afterEach(function () {
    TenantContext::clear();
});

/**
 * Setup Sekolah A dan Sekolah B lengkap dengan seluruh jenis resource.
 */
function setupDuaSekolahUntukSapuTenant(): array
{
    TenantContext::clear();

    // 1. Sekolah A
    $sekolahA = Sekolah::create([
        'nama' => 'SMK Negeri 1 Sweeper A',
        'kode' => 'smk-swp-a-'.uniqid(),
        'status' => 'aktif',
    ]);

    TenantContext::set($sekolahA->id);

    $adminA = User::create([
        'sekolah_id' => $sekolahA->id,
        'nama' => 'Admin A',
        'username' => 'admin_a_'.uniqid(),
        'password' => Hash::make('password123'),
        'aktif' => true,
    ]);
    $adminA->roles()->create(['sekolah_id' => $sekolahA->id, 'role' => 'admin']);

    $spvA = User::create([
        'sekolah_id' => $sekolahA->id,
        'nama' => 'Supervisor A',
        'username' => 'spv_a_'.uniqid(),
        'password' => Hash::make('password123'),
        'aktif' => true,
    ]);
    $spvA->roles()->create(['sekolah_id' => $sekolahA->id, 'role' => 'supervisor']);

    $guruA = User::create([
        'sekolah_id' => $sekolahA->id,
        'nama' => 'Guru A',
        'username' => 'guru_a_'.uniqid(),
        'password' => Hash::make('password123'),
        'aktif' => true,
    ]);
    $guruA->roles()->create(['sekolah_id' => $sekolahA->id, 'role' => 'guru']);

    $periodeA = Periode::create([
        'sekolah_id' => $sekolahA->id,
        'nama' => 'Periode Aktif A',
        'tahun_ajaran' => '2026/2027',
        'semester' => 'ganjil',
        'tanggal_mulai' => now()->subMonth(),
        'tanggal_selesai' => now()->addMonths(2),
        'status' => 'aktif',
    ]);

    $jenisA = JenisInstrumen::create([
        'sekolah_id' => $sekolahA->id,
        'kode' => 'kbm',
        'nama' => 'KBM Sekolah A',
        'urutan' => 1,
        'aktif' => true,
    ]);

    $versiA = VersiInstrumen::create([
        'jenis_instrumen_id' => $jenisA->id,
        'nomor_versi' => 1,
        'status' => 'terbit',
        'skor_maks_butir' => 4,
        'ambang_predikat' => ['A' => 86, 'B' => 76, 'C' => 56],
    ]);

    $bagianA = BagianInstrumen::create([
        'versi_instrumen_id' => $versiA->id,
        'kode' => 'BAG-A',
        'judul' => 'Bagian A',
        'urutan' => 1,
    ]);

    $butirA = ButirInstrumen::create([
        'bagian_id' => $bagianA->id,
        'urutan' => 1,
        'uraian' => 'Butir A',
        'skor_maks' => 4,
    ]);

    $penugasanA = Penugasan::create([
        'sekolah_id' => $sekolahA->id,
        'periode_id' => $periodeA->id,
        'guru_id' => $guruA->id,
        'penilai_id' => $spvA->id,
    ]);

    $penilaianA = Penilaian::create([
        'sekolah_id' => $sekolahA->id,
        'penugasan_id' => $penugasanA->id,
        'jenis_instrumen_id' => $jenisA->id,
        'versi_instrumen_id' => $versiA->id,
        'status' => 'final',
        'total_skor' => 4,
        'skor_maks' => 4,
        'nilai' => 100.00,
        'predikat' => 'A',
    ]);

    $suratTugasA = SuratTugas::create([
        'sekolah_id' => $sekolahA->id,
        'periode_id' => $periodeA->id,
        'penilai_id' => $spvA->id,
        'nomor_surat' => 'ST/A/001',
        'tanggal_surat' => now()->toDateString(),
        'penandatangan_nama' => 'Kepala Sekolah A',
        'daftar_guru' => [],
        'diterbitkan_at' => now(),
    ]);

    // 2. Sekolah B
    TenantContext::clear();
    $sekolahB = Sekolah::create([
        'nama' => 'SMK Swasta 2 Sweeper B',
        'kode' => 'smk-swp-b-'.uniqid(),
        'status' => 'aktif',
    ]);

    TenantContext::set($sekolahB->id);

    $adminB = User::create([
        'sekolah_id' => $sekolahB->id,
        'nama' => 'Admin B',
        'username' => 'admin_b_'.uniqid(),
        'password' => Hash::make('password123'),
        'aktif' => true,
    ]);
    $adminB->roles()->create(['sekolah_id' => $sekolahB->id, 'role' => 'admin']);

    $spvB = User::create([
        'sekolah_id' => $sekolahB->id,
        'nama' => 'Supervisor B',
        'username' => 'spv_b_'.uniqid(),
        'password' => Hash::make('password123'),
        'aktif' => true,
    ]);
    $spvB->roles()->create(['sekolah_id' => $sekolahB->id, 'role' => 'supervisor']);

    $guruB = User::create([
        'sekolah_id' => $sekolahB->id,
        'nama' => 'Guru B',
        'username' => 'guru_b_'.uniqid(),
        'password' => Hash::make('password123'),
        'aktif' => true,
    ]);
    $guruB->roles()->create(['sekolah_id' => $sekolahB->id, 'role' => 'guru']);

    $periodeB = Periode::create([
        'sekolah_id' => $sekolahB->id,
        'nama' => 'Periode Aktif B',
        'tahun_ajaran' => '2026/2027',
        'semester' => 'ganjil',
        'tanggal_mulai' => now()->subMonth(),
        'tanggal_selesai' => now()->addMonths(2),
        'status' => 'aktif',
    ]);

    return compact(
        'sekolahA', 'adminA', 'spvA', 'guruA', 'periodeA', 'jenisA', 'versiA', 'bagianA', 'butirA', 'penugasanA', 'penilaianA', 'suratTugasA',
        'sekolahB', 'adminB', 'spvB', 'guruB', 'periodeB'
    );
}

test('semua route ber-parameter menolak akses lintas tenant dengan respons 404', function () {
    $env = setupDuaSekolahUntukSapuTenant();

    /** @var RouteCollection $routes */
    $routes = app('router')->getRoutes();

    // Parameter map berisi instance model milik Sekolah A
    $paramMap = [
        'guru' => $env['guruA']->id,
        'penilai' => $env['spvA']->id,
        'periode' => $env['periodeA']->id,
        'penugasan' => $env['penugasanA']->id,
        'penilaian' => $env['penilaianA']->ulid,
        'suratTugas' => $env['suratTugasA']->ulid,
        'jenis' => $env['jenisA']->id,
        'versi' => $env['versiA']->id,
        'bagian' => $env['bagianA']->id,
        'butir' => $env['butirA']->id,
        'templateGlobal' => $env['jenisA']->id,
    ];

    $testedCount = 0;

    foreach ($routes as $route) {
        /** @var Route $route */
        $uri = $route->uri();

        // Hanya periksa rute tenant (berawalan s/{kode}/)
        if (! str_starts_with($uri, 's/{kode}/')) {
            continue;
        }

        // Ambil seluruh parameter rute selain {kode}
        $paramNames = $route->parameterNames();
        $resourceParams = array_diff($paramNames, ['kode']);

        // Jika rute tidak memiliki parameter model (hanya statis /s/{kode}/...), abaikan
        if (empty($resourceParams)) {
            continue;
        }

        // Abaikan route yang parameter opsionalnya tidak wajib diuji di sini
        if (in_array('periode', $resourceParams, true) && str_contains($uri, '{periode?}')) {
            continue;
        }

        // Tentukan user Sekolah B yang akan mengakses sesuai peran rute
        $actingUser = $env['adminB'];
        $role = 'admin';

        if (str_starts_with($uri, 's/{kode}/admin/')) {
            $actingUser = $env['adminB'];
            $role = 'admin';
        } elseif (str_starts_with($uri, 's/{kode}/supervisor/')) {
            $actingUser = $env['spvB'];
            $role = 'supervisor';
        } elseif (str_starts_with($uri, 's/{kode}/guru/')) {
            $actingUser = $env['guruB'];
            $role = 'guru';
        }

        // Susun path URL dengan kode Sekolah B dan ID model milik Sekolah A
        $targetUri = $uri;
        $targetUri = str_replace('{kode}', $env['sekolahB']->kode, $targetUri);

        $skipRoute = false;
        foreach ($resourceParams as $pName) {
            if (! isset($paramMap[$pName])) {
                $skipRoute = true;
                break;
            }
            $targetUri = str_replace('{'.$pName.'}', (string) $paramMap[$pName], $targetUri);
        }

        if ($skipRoute) {
            continue;
        }

        // Jalankan HTTP request untuk tiap method yang didukung rute
        $methods = array_diff($route->methods(), ['HEAD']);

        foreach ($methods as $method) {
            $testedCount++;

            $response = $this->actingAs($actingUser)
                ->withSession(['active_role' => $role, 'sekolah_kode' => $env['sekolahB']->kode])
                ->call($method, '/'.$targetUri);

            // Seluruh rute dengan ID milik sekolah lain WAJIB menghasilkan 404
            expect($response->status())->toBe(
                404,
                "Rute [{$method} /{$targetUri}] gagal menolak akses lintas tenant! Status yang didapat: {$response->status()}"
            );
        }
    }

    // Pastikan setidaknya puluhan kombinasi rute telah teruji
    expect($testedCount)->toBeGreaterThan(15, "Jumlah rute yang disapu harus signifikan ({$testedCount} diuji).");
});

test('unduhan Excel, PDF, dan DOCX menolak data dari sekolah lain dengan status 404', function () {
    $env = setupDuaSekolahUntukSapuTenant();

    // 1. Ekspor Excel Rekap (Admin) dengan Periode Sekolah A -> 404
    $this->actingAs($env['adminB'])
        ->withSession(['active_role' => 'admin', 'sekolah_kode' => $env['sekolahB']->kode])
        ->get("/s/{$env['sekolahB']->kode}/admin/laporan/export/rekap?periode_id={$env['periodeA']->id}")
        ->assertNotFound();

    // 2. Ekspor Excel Ketuntasan (Admin) dengan Periode Sekolah A -> 404
    $this->actingAs($env['adminB'])
        ->withSession(['active_role' => 'admin', 'sekolah_kode' => $env['sekolahB']->kode])
        ->get("/s/{$env['sekolahB']->kode}/admin/laporan/export/ketuntasan?periode_id={$env['periodeA']->id}")
        ->assertNotFound();

    // 3. Ekspor Excel Rekap Supervisor dengan Periode Sekolah A -> 404
    $this->actingAs($env['spvB'])
        ->withSession(['active_role' => 'supervisor', 'sekolah_kode' => $env['sekolahB']->kode])
        ->get("/s/{$env['sekolahB']->kode}/supervisor/laporan/export/rekap?periode_id={$env['periodeA']->id}")
        ->assertNotFound();

    // 4. Unduh Cetak PDF Penilaian Sekolah A -> 404
    $this->actingAs($env['adminB'])
        ->withSession(['active_role' => 'admin', 'sekolah_kode' => $env['sekolahB']->kode])
        ->get("/s/{$env['sekolahB']->kode}/penilaian/{$env['penilaianA']->ulid}/cetak-pdf")
        ->assertNotFound();

    // 5. Unduh DOCX Surat Tugas Sekolah A -> 404
    $this->actingAs($env['adminB'])
        ->withSession(['active_role' => 'admin', 'sekolah_kode' => $env['sekolahB']->kode])
        ->get("/s/{$env['sekolahB']->kode}/admin/surat-tugas/{$env['suratTugasA']->ulid}/unduh")
        ->assertNotFound();
});

test('komponen Livewire menolak aksi penilaian lintas sekolah dengan 404', function () {
    $env = setupDuaSekolahUntukSapuTenant();

    TenantContext::set($env['sekolahB']->id);

    // Supervisor Sekolah B mencoba mengakses komponen dengan Penilaian Sekolah A
    $component = Livewire::actingAs($env['spvB'])
        ->test(FormPenilaian::class, ['penilaian' => $env['penilaianA']]);

    // Livewire menangkap abort(404) dari BasePolicy dan menetapkan status 404
    $component->assertStatus(404);
});
