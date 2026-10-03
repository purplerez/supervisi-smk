<?php

use App\Livewire\Supervisor\FormPenilaian;
use App\Models\BagianInstrumen;
use App\Models\ButirInstrumen;
use App\Models\JenisInstrumen;
use App\Models\Penilaian;
use App\Models\Penugasan;
use App\Models\Periode;
use App\Models\Sekolah;
use App\Models\User;
use App\Models\VersiInstrumen;
use App\Tenant\TenantContext;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Livewire\Livewire;

uses(RefreshDatabase::class);

beforeEach(function () {
    TenantContext::clear();
});

afterEach(function () {
    TenantContext::clear();
});

test('supervisor dashboard menampilkan guru binaan dan rekaman supervisi diri sendiri sebagai guru', function () {
    $sekolah = Sekolah::create([
        'nama' => 'SMK Negeri 1 Test',
        'kode' => 'smkn1-test',
        'status' => 'aktif',
    ]);

    TenantContext::set($sekolah->id);

    $kepsek = User::create([
        'sekolah_id' => $sekolah->id,
        'nama' => 'Kepala Sekolah',
        'email' => 'kepsek@test.com',
        'password' => Hash::make('password'),
        'must_change_password' => false,
        'aktif' => true,
    ]);
    $kepsek->assignRole('supervisor');

    $spvGuru = User::create([
        'sekolah_id' => $sekolah->id,
        'nama' => 'Supervisor merangkap Guru',
        'email' => 'spvguru@test.com',
        'password' => Hash::make('password'),
        'must_change_password' => false,
        'aktif' => true,
    ]);
    $spvGuru->assignRole('supervisor');
    $spvGuru->assignRole('guru');

    $guruBinaan = User::create([
        'sekolah_id' => $sekolah->id,
        'nama' => 'Guru Binaan',
        'email' => 'guru@test.com',
        'password' => Hash::make('password'),
        'must_change_password' => false,
        'aktif' => true,
    ]);
    $guruBinaan->assignRole('guru');

    $periode = Periode::create([
        'sekolah_id' => $sekolah->id,
        'nama' => 'Semester Ganjil 2026/2027',
        'tahun_ajaran' => '2026/2027',
        'semester' => 'Ganjil',
        'tanggal_mulai' => now()->subMonth(),
        'tanggal_selesai' => now()->addMonth(),
        'status' => 'aktif',
    ]);

    $jenisKbm = JenisInstrumen::create([
        'sekolah_id' => $sekolah->id,
        'kode' => 'kbm',
        'nama' => 'Observasi KBM',
        'urutan' => 1,
        'aktif' => true,
    ]);

    // Penugasan di mana spvGuru membina guruBinaan
    $penugasanBinaan = Penugasan::create([
        'sekolah_id' => $sekolah->id,
        'periode_id' => $periode->id,
        'guru_id' => $guruBinaan->id,
        'penilai_id' => $spvGuru->id,
    ]);

    // Penugasan di mana kepsek membina spvGuru
    $penugasanDiri = Penugasan::create([
        'sekolah_id' => $sekolah->id,
        'periode_id' => $periode->id,
        'guru_id' => $spvGuru->id,
        'penilai_id' => $kepsek->id,
    ]);

    // Login sebagai spvGuru
    $response = $this->actingAs($spvGuru)
        ->withSession(['sekolah_kode' => $sekolah->kode, 'active_role' => 'supervisor'])
        ->get(route('dashboard.supervisor', ['kode' => $sekolah->kode]));

    $response->assertOk();
    $response->assertSee('Guru Binaan');
    $response->assertSee('Rekaman Supervisi Anda (Sebagai Guru)');
    $response->assertSee('Kepala Sekolah');
});

test('livewire form penilaian dapat memilih skor dan menyimpan draf', function () {
    $sekolah = Sekolah::create([
        'nama' => 'SMK Negeri 2 Test',
        'kode' => 'smkn2-test',
        'status' => 'aktif',
    ]);

    TenantContext::set($sekolah->id);

    $spv = User::create([
        'sekolah_id' => $sekolah->id,
        'nama' => 'Supervisor Penilai',
        'email' => 'spv@test.com',
        'password' => Hash::make('password'),
        'must_change_password' => false,
        'aktif' => true,
    ]);
    $spv->assignRole('supervisor');

    $guru = User::create([
        'sekolah_id' => $sekolah->id,
        'nama' => 'Guru Yang Dinilai',
        'email' => 'guru2@test.com',
        'password' => Hash::make('password'),
        'must_change_password' => false,
        'aktif' => true,
    ]);
    $guru->assignRole('guru');

    $periode = Periode::create([
        'sekolah_id' => $sekolah->id,
        'nama' => 'Semester Ganjil 2026/2027',
        'tahun_ajaran' => '2026/2027',
        'semester' => 'Ganjil',
        'tanggal_mulai' => now()->subMonth(),
        'tanggal_selesai' => now()->addMonth(),
        'status' => 'aktif',
    ]);

    $jenisKbm = JenisInstrumen::create([
        'sekolah_id' => $sekolah->id,
        'kode' => 'kbm',
        'nama' => 'Observasi KBM',
        'urutan' => 1,
        'aktif' => true,
    ]);

    $versi = VersiInstrumen::create([
        'sekolah_id' => $sekolah->id,
        'jenis_instrumen_id' => $jenisKbm->id,
        'nomor_versi' => 1,
        'aktif' => true,
    ]);

    $bagian = BagianInstrumen::create([
        'versi_instrumen_id' => $versi->id,
        'kode' => 'A',
        'judul' => 'Kegiatan Pendahuluan',
        'urutan' => 1,
    ]);

    $butir1 = ButirInstrumen::create([
        'bagian_id' => $bagian->id,
        'urutan' => 1,
        'uraian' => 'Guru membuka pelajaran dengan salam dan doa',
        'skor_maks' => 4,
    ]);

    $penugasan = Penugasan::create([
        'sekolah_id' => $sekolah->id,
        'periode_id' => $periode->id,
        'guru_id' => $guru->id,
        'penilai_id' => $spv->id,
    ]);

    $penilaian = Penilaian::create([
        'sekolah_id' => $sekolah->id,
        'penugasan_id' => $penugasan->id,
        'jenis_instrumen_id' => $jenisKbm->id,
        'versi_instrumen_id' => $versi->id,
        'status' => 'draft',
    ]);

    $this->actingAs($spv);

    Livewire::test(FormPenilaian::class, ['penilaian' => $penilaian])
        ->set("skor.{$butir1->id}", 4)
        ->set("catatanButir.{$butir1->id}", 'Bagus sekali')
        ->set('catatan', 'Pembelajaran berjalan lancar')
        ->set('tindakLanjut', 'Pertahankan')
        ->call('simpanDraft')
        ->assertHasNoErrors()
        ->assertSee('Draf penilaian berhasil disimpan.');

    $this->assertDatabaseHas('penilaian_butir', [
        'penilaian_id' => $penilaian->id,
        'butir_instrumen_id' => $butir1->id,
        'skor' => 4,
        'catatan' => 'Bagus sekali',
    ]);
});
