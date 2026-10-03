<?php

use App\Models\JenisInstrumen;
use App\Models\Penilaian;
use App\Models\Penugasan;
use App\Models\Periode;
use App\Models\Sekolah;
use App\Models\User;
use App\Services\PenugasanService;
use App\Tenant\TenantContext;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;

uses(RefreshDatabase::class);

beforeEach(function () {
    TenantContext::clear();

    $instrumenCodes = [
        'perencanaan' => 'Perencanaan Pembelajaran',
        'kbm' => 'Pelaksanaan Pembelajaran (KBM)',
        'pengelolaan_kelas' => 'Pengelolaan Lingkungan Kelas',
        'administrasi' => 'Administrasi Guru',
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
 * Setup sekolah, supervisor, 2 guru, dan periode aktif.
 */
function setupGuruEnvironment(): array
{
    TenantContext::clear();

    $sekolah = Sekolah::create([
        'nama' => 'SMK Negeri 2 Bandung',
        'kode' => 'smkn2-bdg-'.uniqid(),
        'alamat' => 'Jl. Ciliwung No. 4, Bandung',
        'status' => 'aktif',
    ]);

    TenantContext::set($sekolah->id);

    $kepalaSekolah = User::create([
        'sekolah_id' => $sekolah->id,
        'nama' => 'Dr. H. Asep Sunandar, M.Pd.',
        'username' => 'kepsek_'.uniqid(),
        'nip' => '196501011990031001',
        'password' => Hash::make('password123'),
        'must_change_password' => false,
        'aktif' => true,
    ]);
    $kepalaSekolah->roles()->create(['sekolah_id' => $sekolah->id, 'role' => 'supervisor']);
    $sekolah->update(['kepala_sekolah_id' => $kepalaSekolah->id]);

    $supervisor = User::create([
        'sekolah_id' => $sekolah->id,
        'nama' => 'Dra. Hj. Nurhayati, M.M.',
        'username' => 'spv_'.uniqid(),
        'nip' => '197004121995122001',
        'password' => Hash::make('password123'),
        'must_change_password' => false,
        'aktif' => true,
    ]);
    $supervisor->roles()->create(['sekolah_id' => $sekolah->id, 'role' => 'supervisor']);

    $guruA = User::create([
        'sekolah_id' => $sekolah->id,
        'nama' => 'Rina Marlina, S.Pd.',
        'username' => 'guru_rina_'.uniqid(),
        'nip' => '198802202011012003',
        'password' => Hash::make('password123'),
        'must_change_password' => false,
        'aktif' => true,
    ]);
    $guruA->roles()->create(['sekolah_id' => $sekolah->id, 'role' => 'guru']);

    $guruB = User::create([
        'sekolah_id' => $sekolah->id,
        'nama' => 'Deden Kurnia, S.T.',
        'username' => 'guru_deden_'.uniqid(),
        'nip' => '198906152014021001',
        'password' => Hash::make('password123'),
        'must_change_password' => false,
        'aktif' => true,
    ]);
    $guruB->roles()->create(['sekolah_id' => $sekolah->id, 'role' => 'guru']);

    $periodeAktif = Periode::create([
        'sekolah_id' => $sekolah->id,
        'nama' => 'Supervisi Semester Ganjil 2026/2027',
        'tahun_ajaran' => '2026/2027',
        'semester' => 'Ganjil',
        'tanggal_mulai' => '2026-08-01',
        'tanggal_selesai' => '2026-11-30',
        'status' => 'aktif',
    ]);

    // Tugaskan guru A saja ke supervisor
    $penugasanService = app(PenugasanService::class);
    $hasil = $penugasanService->buatPenugasan($periodeAktif, $supervisor->id, [$guruA->id]);
    $penugasanA = $hasil['penugasan'][0];

    return compact('sekolah', 'kepalaSekolah', 'supervisor', 'guruA', 'guruB', 'periodeAktif', 'penugasanA');
}

test('periode tanpa penugasan: guru melihat pesan ramah di dasbor', function () {
    $data = setupGuruEnvironment();

    // Guru B belum ditugaskan pada periode aktif
    $response = $this->actingAs($data['guruB'])
        ->withSession(['active_role' => 'guru', 'sekolah_kode' => $data['sekolah']->kode])
        ->get(route('guru.dashboard', ['kode' => $data['sekolah']->kode]));

    $response->assertOk();
    $response->assertSee('Halo, Bapak/Ibu '.$data['guruB']->nama);
    $response->assertSee('Anda belum memiliki penugasan supervisi');
    $response->assertSee($data['periodeAktif']->nama);
});

test('guru hanya melihat data penugasan dirinya sendiri di dasbor dan rapor', function () {
    $data = setupGuruEnvironment();

    // Guru A membuka dasbor: melihat namanya dan supervisornya
    $resA = $this->actingAs($data['guruA'])
        ->withSession(['active_role' => 'guru', 'sekolah_kode' => $data['sekolah']->kode])
        ->get(route('guru.dashboard', ['kode' => $data['sekolah']->kode]));

    $resA->assertOk();
    $resA->assertSee($data['supervisor']->nama);
    $resA->assertSee('Belum selesai (0/4)');

    // Guru B mencoba membuka rapor periode aktif (karena tidak punya penugasan di periode ini, 404)
    $resB = $this->actingAs($data['guruB'])
        ->withSession(['active_role' => 'guru', 'sekolah_kode' => $data['sekolah']->kode])
        ->get(route('guru.rapor', ['kode' => $data['sekolah']->kode, 'periode' => $data['periodeAktif']->id]));

    $resB->assertNotFound();
});

test('status belum, draft, dan direvisi tidak pernah menampilkan nilai numerik (hanya label)', function () {
    $data = setupGuruEnvironment();
    $penugasan = $data['penugasanA'];

    // Atur penilaian:
    // 1. perencanaan -> draft (nilai sudah diisi supervisor misal 85.00 tapi belum difinalisasi)
    // 2. kbm -> direvisi (nilai 90.00 pernah difinalkan lalu dibuka kunci)
    // 3. administrasi -> belum
    $penilaianPerencanaan = $penugasan->penilaian->firstWhere('jenisInstrumen.kode', 'perencanaan');
    $penilaianPerencanaan->update([
        'status' => 'draft',
        'nilai' => 85.50,
        'predikat' => 'B',
        'catatan' => 'Catatan draft supervisor rahasia',
    ]);

    $penilaianKbm = $penugasan->penilaian->firstWhere('jenisInstrumen.kode', 'kbm');
    $penilaianKbm->update([
        'status' => 'direvisi',
        'nilai' => 90.00,
        'predikat' => 'A',
        'catatan' => 'Catatan revisi supervisor',
    ]);

    // 1. Cek pada Dasbor Guru
    $resDasbor = $this->actingAs($data['guruA'])
        ->withSession(['active_role' => 'guru', 'sekolah_kode' => $data['sekolah']->kode])
        ->get(route('guru.dashboard', ['kode' => $data['sekolah']->kode]));

    $resDasbor->assertOk();
    $resDasbor->assertSee('Sedang dinilai');
    $resDasbor->assertSee('Sedang direvisi');
    $resDasbor->assertSee('Belum dinilai');

    // Pastikan angka nilai 85,50 atau 90,00 TIDAK MUNCUL di tampilan HTML
    $resDasbor->assertDontSee('85,50');
    $resDasbor->assertDontSee('85.50');
    $resDasbor->assertDontSee('90,00');
    $resDasbor->assertDontSee('90.00');
    $resDasbor->assertDontSee('Catatan draft supervisor rahasia');

    // 2. Cek pada Rapor Guru
    $resRapor = $this->actingAs($data['guruA'])
        ->withSession(['active_role' => 'guru', 'sekolah_kode' => $data['sekolah']->kode])
        ->get(route('guru.rapor', ['kode' => $data['sekolah']->kode, 'periode' => $data['periodeAktif']->id]));

    $resRapor->assertOk();
    $resRapor->assertSee('Sedang dinilai');
    $resRapor->assertSee('Sedang direvisi');
    $resRapor->assertDontSee('85,50');
    $resRapor->assertDontSee('85.50');
    $resRapor->assertDontSee('90,00');
    $resRapor->assertDontSee('90.00');
    $resRapor->assertDontSee('Catatan draft supervisor rahasia');
});

test('status final menampilkan nilai numerik, predikat, catatan, dan tindak lanjut', function () {
    $data = setupGuruEnvironment();
    $penugasan = $data['penugasanA'];

    // Set penilaian kbm menjadi final
    $penilaianKbm = $penugasan->penilaian->firstWhere('jenisInstrumen.kode', 'kbm');
    $penilaianKbm->update([
        'status' => 'final',
        'nilai' => 92.50,
        'predikat' => 'A',
        'catatan' => 'Penguasaan materi sangat baik dan interaktif.',
        'tindak_lanjut' => 'Pertahankan dan tularkan praktik baik ke MGMP.',
        'finalized_at' => now(),
    ]);

    // Cek pada dasbor
    $resDasbor = $this->actingAs($data['guruA'])
        ->withSession(['active_role' => 'guru', 'sekolah_kode' => $data['sekolah']->kode])
        ->get(route('guru.dashboard', ['kode' => $data['sekolah']->kode]));

    $resDasbor->assertOk();
    $resDasbor->assertSee('92,50');
    $resDasbor->assertSee('Predikat: A');

    // Cek pada rapor
    $resRapor = $this->actingAs($data['guruA'])
        ->withSession(['active_role' => 'guru', 'sekolah_kode' => $data['sekolah']->kode])
        ->get(route('guru.rapor', ['kode' => $data['sekolah']->kode, 'periode' => $data['periodeAktif']->id]));

    $resRapor->assertOk();
    $resRapor->assertSee('92,50');
    $resRapor->assertSee('Penguasaan materi sangat baik dan interaktif.');
    $resRapor->assertSee('Pertahankan dan tularkan praktik baik ke MGMP.');
});

test('guru dapat mengisi dan memperbarui informasi supervisi dan jadwal saat penilaian masih belum dimulai', function () {
    $data = setupGuruEnvironment();
    $penugasan = $data['penugasanA'];

    $jenisKbm = JenisInstrumen::where('kode', 'kbm')->first();

    $payload = [
        'kelas' => 'XII RPL 1',
        'semester' => 'Ganjil',
        'fase' => 'F (Kelas XI & XII)',
        'mata_pelajaran' => 'Pemrograman Web dan Perangkat Bergerak',
        'elemen' => 'Framework Laravel',
        'cp' => 'Peserta didik mampu merancang arsitektur aplikasi berbasis web.',
        'catatan' => 'Praktikum di Lab Komputer 1.',
        'jadwal' => [
            $jenisKbm->id => [
                'tanggal' => '2026-09-15',
                'jam_mulai' => '08:00',
                'jam_selesai' => '09:30',
            ],
        ],
    ];

    $response = $this->actingAs($data['guruA'])
        ->withSession(['active_role' => 'guru', 'sekolah_kode' => $data['sekolah']->kode])
        ->post(route('guru.info-jadwal.simpan', ['kode' => $data['sekolah']->kode]), $payload);

    $response->assertRedirect(route('guru.info-jadwal', ['kode' => $data['sekolah']->kode]));

    $penugasan->refresh();
    expect($penugasan->infoSupervisi)->not->toBeNull();
    expect($penugasan->infoSupervisi->kelas)->toBe('XII RPL 1');
    expect($penugasan->infoSupervisi->mata_pelajaran)->toBe('Pemrograman Web dan Perangkat Bergerak');

    $jadwal = $penugasan->jadwalSupervisi()->where('jenis_instrumen_id', $jenisKbm->id)->first();
    expect($jadwal)->not->toBeNull();
    expect($jadwal->tanggal->format('Y-m-d'))->toBe('2026-09-15');
});

test('pengunci form: guru ditolak saat mencoba memperbarui informasi atau jadwal jika salah satu penilaian sudah dimulai', function () {
    $data = setupGuruEnvironment();
    $penugasan = $data['penugasanA'];

    // Ubah salah satu penilaian menjadi status draft (supervisi telah dimulai)
    $penilaianKbm = $penugasan->penilaian->firstWhere('jenisInstrumen.kode', 'kbm');
    $penilaianKbm->update(['status' => 'draft']);

    expect($penugasan->guruBisaUbahInfoDanJadwal())->toBeFalse();

    // Halaman info-jadwal menampilkan pemberitahuan bahwa form terkunci
    $resView = $this->actingAs($data['guruA'])
        ->withSession(['active_role' => 'guru', 'sekolah_kode' => $data['sekolah']->kode])
        ->get(route('guru.info-jadwal', ['kode' => $data['sekolah']->kode]));
    $resView->assertSee('Formulir Telah Dikunci (Hanya-Baca)');

    // Guru mencoba POST data pembaruan
    $payload = [
        'kelas' => 'XII RPL 2 (Ubah Ilegal)',
        'mata_pelajaran' => 'Matematika',
    ];

    $response = $this->actingAs($data['guruA'])
        ->withSession(['active_role' => 'guru', 'sekolah_kode' => $data['sekolah']->kode])
        ->post(route('guru.info-jadwal.simpan', ['kode' => $data['sekolah']->kode]), $payload);

    $response->assertRedirect(route('guru.info-jadwal', ['kode' => $data['sekolah']->kode]));
    $response->assertSessionHas('galat');

    $penugasan->refresh();
    expect($penugasan->infoSupervisi?->kelas)->not->toBe('XII RPL 2 (Ubah Ilegal)');
});

test('halaman riwayat menampilkan daftar periode sebelumnya dan tautan ke rapor', function () {
    $data = setupGuruEnvironment();

    // Buat periode lampau
    $periodeLalu = Periode::create([
        'sekolah_id' => $data['sekolah']->id,
        'nama' => 'Supervisi Tahun Ajaran 2025/2026',
        'tahun_ajaran' => '2025/2026',
        'tanggal_mulai' => '2025-08-01',
        'tanggal_selesai' => '2025-11-30',
        'status' => 'ditutup',
    ]);

    // Buat penugasan di periode lalu
    $penugasanLalu = Penugasan::create([
        'sekolah_id' => $data['sekolah']->id,
        'periode_id' => $periodeLalu->id,
        'guru_id' => $data['guruA']->id,
        'penilai_id' => $data['supervisor']->id,
    ]);

    // Buka halaman riwayat
    $response = $this->actingAs($data['guruA'])
        ->withSession(['active_role' => 'guru', 'sekolah_kode' => $data['sekolah']->kode])
        ->get(route('guru.riwayat', ['kode' => $data['sekolah']->kode]));

    $response->assertOk();
    $response->assertSee('Daftar Periode Supervisi Anda (2)');
    $response->assertSee('Supervisi Semester Ganjil 2026/2027');
    $response->assertSee('Supervisi Tahun Ajaran 2025/2026');
    $response->assertSee(route('guru.rapor', ['kode' => $data['sekolah']->kode, 'periode' => $periodeLalu->id]));
});

test('akses lintas-tenant: guru sekolah A mencoba mengakses data sekolah B menghasilkan 404', function () {
    $dataA = setupGuruEnvironment();

    // Setup Sekolah B
    TenantContext::clear();
    $sekolahB = Sekolah::create([
        'nama' => 'SMK Bina Bangsa',
        'kode' => 'smk-bina-'.uniqid(),
        'status' => 'aktif',
    ]);
    TenantContext::set($sekolahB->id);

    $guruB = User::create([
        'sekolah_id' => $sekolahB->id,
        'nama' => 'Guru Sekolah B',
        'username' => 'guru_b_'.uniqid(),
        'password' => Hash::make('password123'),
        'must_change_password' => false,
        'aktif' => true,
    ]);
    $guruB->roles()->create(['sekolah_id' => $sekolahB->id, 'role' => 'guru']);

    // Guru A mencoba mengakses dasbor dengan URL Sekolah B -> ditolak oleh middleware tenant
    $response = $this->actingAs($dataA['guruA'])
        ->withSession(['active_role' => 'guru', 'sekolah_kode' => $sekolahB->kode])
        ->get("/s/{$sekolahB->kode}/guru/dashboard");

    $response->assertRedirect("/s/{$sekolahB->kode}/login");
    $response->assertSessionHasErrors('username');

    // Periode milik Sekolah B (dibuat dalam konteks Sekolah B)
    $periodeB = TenantContext::runAs($sekolahB->id, function () use ($sekolahB) {
        return Periode::create([
            'sekolah_id' => $sekolahB->id,
            'nama' => 'Periode Sekolah B',
            'tahun_ajaran' => '2026/2027',
            'tanggal_mulai' => '2026-08-01',
            'tanggal_selesai' => '2026-11-30',
            'status' => 'aktif',
        ]);
    });

    // Guru A mencoba mengakses rapor periode milik Sekolah B melalui URL sekolahnya sendiri -> 404
    $resRaporB = $this->actingAs($dataA['guruA'])
        ->withSession(['active_role' => 'guru', 'sekolah_kode' => $dataA['sekolah']->kode])
        ->get(route('guru.rapor', ['kode' => $dataA['sekolah']->kode, 'periode' => $periodeB->id]));

    $resRaporB->assertNotFound();
});
