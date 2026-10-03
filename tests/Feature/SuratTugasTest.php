<?php

use App\Models\JenisInstrumen;
use App\Models\Penugasan;
use App\Models\Periode;
use App\Models\Sekolah;
use App\Models\SuratTugas;
use App\Models\User;
use App\Services\PenugasanService;
use App\Tenant\TenantContext;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;

uses(RefreshDatabase::class);

beforeEach(function () {
    TenantContext::clear();

    // Pastikan instrumen global tersedia untuk pembuatan 4 penilaian otomatis
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
 * Setup sekolah, kepala sekolah, admin, supervisor, dan 2 guru.
 */
function setupSekolahDenganPenugasan(): array
{
    TenantContext::clear();

    $sekolah = Sekolah::create([
        'nama' => 'SMK Negeri 1 Surabaya',
        'kode' => 'smkn1-sby-'.uniqid(),
        'alamat' => 'Jl. Ketintang No. 10, Surabaya',
        'status' => 'aktif',
    ]);

    TenantContext::set($sekolah->id);

    $kepalaSekolah = User::create([
        'sekolah_id' => $sekolah->id,
        'nama' => 'Drs. H. Mulyono, M.Pd.',
        'username' => 'kepsek_'.uniqid(),
        'nip' => '196805101994031005',
        'password' => Hash::make('password123'),
        'must_change_password' => false,
        'aktif' => true,
    ]);
    $kepalaSekolah->roles()->create(['sekolah_id' => $sekolah->id, 'role' => 'supervisor']);

    $sekolah->update(['kepala_sekolah_id' => $kepalaSekolah->id]);

    $admin = User::create([
        'sekolah_id' => $sekolah->id,
        'nama' => 'Admin Sekolah',
        'username' => 'admin_'.uniqid(),
        'password' => Hash::make('password123'),
        'must_change_password' => false,
        'aktif' => true,
    ]);
    $admin->roles()->create(['sekolah_id' => $sekolah->id, 'role' => 'admin']);

    $supervisor = User::create([
        'sekolah_id' => $sekolah->id,
        'nama' => 'Ahmad Supervisor, S.Pd.',
        'username' => 'spv_'.uniqid(),
        'nip' => '197508122002121003',
        'password' => Hash::make('password123'),
        'must_change_password' => false,
        'aktif' => true,
    ]);
    $supervisor->roles()->create(['sekolah_id' => $sekolah->id, 'role' => 'supervisor']);

    $guruA = User::create([
        'sekolah_id' => $sekolah->id,
        'nama' => 'Dewi Lestari, S.Pd.',
        'username' => 'guru_a_'.uniqid(),
        'nip' => '198501012010012001',
        'password' => Hash::make('password123'),
        'must_change_password' => false,
        'aktif' => true,
    ]);
    $guruA->roles()->create(['sekolah_id' => $sekolah->id, 'role' => 'guru']);

    $guruB = User::create([
        'sekolah_id' => $sekolah->id,
        'nama' => 'Bambang Tri, S.Kom.',
        'username' => 'guru_b_'.uniqid(),
        'nip' => '198703152011011002',
        'password' => Hash::make('password123'),
        'must_change_password' => false,
        'aktif' => true,
    ]);
    $guruB->roles()->create(['sekolah_id' => $sekolah->id, 'role' => 'guru']);

    $periode = Periode::create([
        'sekolah_id' => $sekolah->id,
        'nama' => 'Supervisi Semester Ganjil 2026/2027',
        'tahun_ajaran' => '2026/2027',
        'semester' => 'Ganjil',
        'tanggal_mulai' => '2026-08-01',
        'tanggal_selesai' => '2026-11-30',
        'status' => 'aktif',
    ]);

    // Tugaskan guru A dan guru B ke supervisor
    $penugasanService = app(PenugasanService::class);
    $penugasanService->buatPenugasan($periode, $supervisor->id, [$guruA->id, $guruB->id]);

    session([
        'active_role' => 'admin',
        'sekolah_kode' => $sekolah->kode,
    ]);

    return compact('sekolah', 'kepalaSekolah', 'admin', 'supervisor', 'guruA', 'guruB', 'periode');
}

test('admin dapat melihat halaman surat tugas dengan daftar supervisor dan status belum terbit', function () {
    $data = setupSekolahDenganPenugasan();

    $response = $this->actingAs($data['admin'])
        ->withSession(['active_role' => 'admin', 'sekolah_kode' => $data['sekolah']->kode])
        ->get(route('admin.surat-tugas.index', ['kode' => $data['sekolah']->kode, 'periode_id' => $data['periode']->id]));

    $response->assertOk();
    $response->assertSee('Surat Tugas Supervisor');
    $response->assertSee($data['supervisor']->nama);
    $response->assertSee('Belum Terbit');
    $response->assertSee('2 Guru');
});

test('admin dapat menerbitkan surat tugas dengan nomor dan snapshot tersimpan di database', function () {
    $data = setupSekolahDenganPenugasan();

    $payload = [
        'nomor_surat' => '800/015/SMKN1/X/2026',
        'tanggal_surat' => '2026-10-02',
        'penandatangan_nama' => 'Drs. H. Mulyono, M.Pd.',
        'penandatangan_nip' => '196805101994031005',
        'penandatangan_jabatan' => 'Kepala Sekolah',
    ];

    $response = $this->actingAs($data['admin'])
        ->withSession(['active_role' => 'admin', 'sekolah_kode' => $data['sekolah']->kode])
        ->post(
            route('admin.surat-tugas.store', [
                'kode' => $data['sekolah']->kode,
                'periode' => $data['periode']->id,
                'penilai' => $data['supervisor']->id,
            ]),
            $payload
        );

    $response->assertRedirect(route('admin.surat-tugas.index', ['kode' => $data['sekolah']->kode, 'periode_id' => $data['periode']->id]));

    $surat = SuratTugas::where('periode_id', $data['periode']->id)
        ->where('penilai_id', $data['supervisor']->id)
        ->first();

    expect($surat)->not->toBeNull();
    expect($surat->nomor_surat)->toBe('800/015/SMKN1/X/2026');
    expect($surat->penandatangan_nama)->toBe('Drs. H. Mulyono, M.Pd.');
    expect($surat->ulid)->not->toBeEmpty();

    // Verifikasi snapshot daftar guru memuat 2 guru
    $daftarGuru = $surat->daftar_guru;
    expect($daftarGuru)->toBeArray()->toHaveCount(2);

    $namaGuruSnapshot = array_column($daftarGuru, 'nama');
    expect($namaGuruSnapshot)->toContain($data['guruA']->nama);
    expect($namaGuruSnapshot)->toContain($data['guruB']->nama);
});

test('unduh DOCX menghasilkan file zip valid dan word/document.xml memuat penilai, guru, nomor surat, dan url login', function () {
    $data = setupSekolahDenganPenugasan();

    // Terbitkan surat tugas terlebih dahulu
    $suratTugas = SuratTugas::create([
        'sekolah_id' => $data['sekolah']->id,
        'periode_id' => $data['periode']->id,
        'penilai_id' => $data['supervisor']->id,
        'nomor_surat' => '800/099/ST-SUPERVISI/2026',
        'tanggal_surat' => '2026-10-02',
        'penandatangan_nama' => 'Drs. H. Mulyono, M.Pd.',
        'penandatangan_nip' => '196805101994031005',
        'penandatangan_jabatan' => 'Kepala Sekolah',
        'daftar_guru' => [
            ['id' => $data['guruA']->id, 'nama' => $data['guruA']->nama, 'nip' => $data['guruA']->nip, 'nuptk' => null],
            ['id' => $data['guruB']->id, 'nama' => $data['guruB']->nama, 'nip' => $data['guruB']->nip, 'nuptk' => null],
        ],
        'diterbitkan_at' => now(),
    ]);

    $response = $this->actingAs($data['admin'])
        ->withSession(['active_role' => 'admin', 'sekolah_kode' => $data['sekolah']->kode])
        ->get(route('admin.surat-tugas.download', [
            'kode' => $data['sekolah']->kode,
            'suratTugas' => $suratTugas->ulid,
        ]));

    $response->assertOk();
    $response->assertHeader('content-type', 'application/vnd.openxmlformats-officedocument.wordprocessingml.document');

    // Ambil file biner DOCX
    $tempDocx = tempnam(sys_get_temp_dir(), 'test_dl_');
    $file = $response->getFile();
    copy($file->getPathname(), $tempDocx);

    // Buka file sebagai ZIP
    $zip = new ZipArchive;
    $isOpen = $zip->open($tempDocx);
    expect($isOpen)->toBeTrue('File yang dihasilkan harus merupakan ZIP/DOCX valid.');

    $documentXml = $zip->getFromName('word/document.xml');
    $zip->close();
    @unlink($tempDocx);

    expect($documentXml)->not->toBeFalse();

    // Verifikasi isi XML memuat komponen penting
    expect($documentXml)->toContain('SURAT TUGAS');
    expect($documentXml)->toContain('800/099/ST-SUPERVISI/2026');
    expect($documentXml)->toContain($data['supervisor']->nama);
    expect($documentXml)->toContain($data['guruA']->nama);
    expect($documentXml)->toContain($data['guruB']->nama);
    expect($documentXml)->toContain($data['kepalaSekolah']->nama);
    expect($documentXml)->toContain($data['sekolah']->kode);
});

test('perubahan penugasan guru membuat status surat menjadi perlu diterbitkan ulang', function () {
    $data = setupSekolahDenganPenugasan();

    // 1. Terbitkan surat tugas dengan snapshot 2 guru (A & B)
    SuratTugas::create([
        'sekolah_id' => $data['sekolah']->id,
        'periode_id' => $data['periode']->id,
        'penilai_id' => $data['supervisor']->id,
        'nomor_surat' => '800/001/ST/2026',
        'tanggal_surat' => '2026-10-02',
        'penandatangan_nama' => 'Drs. H. Mulyono, M.Pd.',
        'penandatangan_nip' => '196805101994031005',
        'daftar_guru' => [
            ['id' => $data['guruA']->id, 'nama' => $data['guruA']->nama, 'nip' => $data['guruA']->nip, 'nuptk' => null],
            ['id' => $data['guruB']->id, 'nama' => $data['guruB']->nama, 'nip' => $data['guruB']->nip, 'nuptk' => null],
        ],
        'diterbitkan_at' => now(),
    ]);

    // Halaman awal: status harus "Terbit"
    $resAwal = $this->actingAs($data['admin'])
        ->withSession(['active_role' => 'admin', 'sekolah_kode' => $data['sekolah']->kode])
        ->get(route('admin.surat-tugas.index', ['kode' => $data['sekolah']->kode, 'periode_id' => $data['periode']->id]));
    $resAwal->assertSee('Terbit');
    $resAwal->assertDontSee('Perlu Diterbitkan Ulang');

    // 2. Tambah guru baru C ke sekolah dan tugaskan ke supervisor yang sama
    $guruC = User::create([
        'sekolah_id' => $data['sekolah']->id,
        'nama' => 'Ratna Guru Fisika, S.Pd.',
        'username' => 'guru_c_'.uniqid(),
        'password' => Hash::make('password123'),
        'must_change_password' => false,
        'aktif' => true,
    ]);
    $guruC->roles()->create(['sekolah_id' => $data['sekolah']->id, 'role' => 'guru']);

    app(PenugasanService::class)->buatPenugasan($data['periode'], $data['supervisor']->id, [$guruC->id]);

    // 3. Muat kembali halaman: harus terdeteksi "Perlu Diterbitkan Ulang"
    $resSetelahTambah = $this->actingAs($data['admin'])
        ->withSession(['active_role' => 'admin', 'sekolah_kode' => $data['sekolah']->kode])
        ->get(route('admin.surat-tugas.index', ['kode' => $data['sekolah']->kode, 'periode_id' => $data['periode']->id]));

    $resSetelahTambah->assertSee('Perlu Diterbitkan Ulang');
    $resSetelahTambah->assertSee('Terbitkan Ulang');

    // 4. Terbitkan ulang surat tugas
    $resTerbitUlang = $this->actingAs($data['admin'])
        ->withSession(['active_role' => 'admin', 'sekolah_kode' => $data['sekolah']->kode])
        ->post(
            route('admin.surat-tugas.store', [
                'kode' => $data['sekolah']->kode,
                'periode' => $data['periode']->id,
                'penilai' => $data['supervisor']->id,
            ]),
            [
                'nomor_surat' => '800/001.REV/ST/2026',
                'tanggal_surat' => '2026-10-03',
                'penandatangan_nama' => 'Drs. H. Mulyono, M.Pd.',
                'penandatangan_nip' => '196805101994031005',
                'penandatangan_jabatan' => 'Kepala Sekolah',
            ]
        );

    $resTerbitUlang->assertRedirect();

    $suratDiperbarui = SuratTugas::where('periode_id', $data['periode']->id)
        ->where('penilai_id', $data['supervisor']->id)
        ->first();

    expect($suratDiperbarui->nomor_surat)->toBe('800/001.REV/ST/2026');
    expect($suratDiperbarui->daftar_guru)->toHaveCount(3);

    // Sekarang status kembali menjadi "Terbit"
    $resAkhir = $this->actingAs($data['admin'])
        ->withSession(['active_role' => 'admin', 'sekolah_kode' => $data['sekolah']->kode])
        ->get(route('admin.surat-tugas.index', ['kode' => $data['sekolah']->kode, 'periode_id' => $data['periode']->id]));

    $resAkhir->assertSee('Terbit');
    $resAkhir->assertDontSee('Perlu Diterbitkan Ulang');
});

test('akses lintas-tenant ke unduh surat tugas menghasilkan 404', function () {
    $dataA = setupSekolahDenganPenugasan();

    // Buat surat tugas untuk Sekolah A
    $suratA = SuratTugas::create([
        'sekolah_id' => $dataA['sekolah']->id,
        'periode_id' => $dataA['periode']->id,
        'penilai_id' => $dataA['supervisor']->id,
        'nomor_surat' => 'ST/SEKOLAH-A/01',
        'tanggal_surat' => '2026-10-02',
        'penandatangan_nama' => 'Kepala Sekolah A',
        'daftar_guru' => [],
        'diterbitkan_at' => now(),
    ]);

    // Setup Sekolah B
    TenantContext::clear();
    $sekolahB = Sekolah::create([
        'nama' => 'SMK Swasta B',
        'kode' => 'smk-b-'.uniqid(),
        'status' => 'aktif',
    ]);
    TenantContext::set($sekolahB->id);

    $adminB = User::create([
        'sekolah_id' => $sekolahB->id,
        'nama' => 'Admin Sekolah B',
        'username' => 'admin_b_'.uniqid(),
        'password' => Hash::make('password123'),
        'must_change_password' => false,
        'aktif' => true,
    ]);
    $adminB->roles()->create(['sekolah_id' => $sekolahB->id, 'role' => 'admin']);

    // Admin B mencoba mengakses unduh surat tugas milik Sekolah A
    $response = $this->actingAs($adminB)
        ->withSession(['active_role' => 'admin', 'sekolah_kode' => $sekolahB->kode])
        ->get("/s/{$sekolahB->kode}/admin/surat-tugas/{$suratA->ulid}/unduh");

    $response->assertNotFound();
});

test('role non-admin (guru atau supervisor saja) ditolak saat mengakses surat tugas (403)', function () {
    $data = setupSekolahDenganPenugasan();

    // Supervisor mencoba mengakses halaman admin surat tugas
    $responseSpv = $this->actingAs($data['supervisor'])
        ->withSession(['active_role' => 'supervisor', 'sekolah_kode' => $data['sekolah']->kode])
        ->get(route('admin.surat-tugas.index', ['kode' => $data['sekolah']->kode]));

    $responseSpv->assertForbidden();

    // Guru mencoba mengakses
    $responseGuru = $this->actingAs($data['guruA'])
        ->withSession(['active_role' => 'guru', 'sekolah_kode' => $data['sekolah']->kode])
        ->get(route('admin.surat-tugas.index', ['kode' => $data['sekolah']->kode]));

    $responseGuru->assertForbidden();
});

test('gagal menerbitkan surat tugas jika supervisor tidak memiliki penugasan guru pada periode terpilih', function () {
    $data = setupSekolahDenganPenugasan();

    // Buat supervisor baru yang belum punya penugasan guru
    $spvKosong = User::create([
        'sekolah_id' => $data['sekolah']->id,
        'nama' => 'Supervisor Tanpa Guru',
        'username' => 'spv_kosong_'.uniqid(),
        'password' => Hash::make('password123'),
        'must_change_password' => false,
        'aktif' => true,
    ]);
    $spvKosong->roles()->create(['sekolah_id' => $data['sekolah']->id, 'role' => 'supervisor']);

    $response = $this->actingAs($data['admin'])
        ->withSession(['active_role' => 'admin', 'sekolah_kode' => $data['sekolah']->kode])
        ->post(
            route('admin.surat-tugas.store', [
                'kode' => $data['sekolah']->kode,
                'periode' => $data['periode']->id,
                'penilai' => $spvKosong->id,
            ]),
            [
                'nomor_surat' => '800/999/ST/2026',
                'tanggal_surat' => '2026-10-02',
                'penandatangan_nama' => 'Drs. H. Mulyono, M.Pd.',
            ]
        );

    $response->assertSessionHasErrors('penilai_id');
});
