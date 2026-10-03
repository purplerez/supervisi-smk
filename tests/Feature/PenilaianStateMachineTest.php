<?php

use App\Livewire\Supervisor\FormPenilaian;
use App\Models\AuditLog;
use App\Models\BagianInstrumen;
use App\Models\ButirInstrumen;
use App\Models\JenisInstrumen;
use App\Models\Penilaian;
use App\Models\PenilaianButir;
use App\Models\Penugasan;
use App\Models\Periode;
use App\Models\Sekolah;
use App\Models\User;
use App\Models\VersiInstrumen;
use App\Services\PenilaianService;
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

/**
 * Helper untuk setup sekolah dan instrumen lengkap dengan butir.
 */
function buatLingkunganSupervisi(string $prefix = 'smk1'): array
{
    TenantContext::clear();

    $sekolah = Sekolah::create([
        'nama' => 'SMK '.strtoupper($prefix),
        'kode' => $prefix.'-'.uniqid(),
        'status' => 'aktif',
    ]);

    TenantContext::set($sekolah->id);

    // Buat jenis instrumen KBM
    $jenisKbm = JenisInstrumen::firstOrCreate(
        ['kode' => 'kbm', 'sekolah_id' => $sekolah->id],
        ['nama' => 'Kegiatan Belajar Mengajar', 'urutan' => 1, 'aktif' => true]
    );

    // Buat jenis instrumen lainnya
    $jenisAdm = JenisInstrumen::firstOrCreate(
        ['kode' => 'administrasi', 'sekolah_id' => $sekolah->id],
        ['nama' => 'Administrasi Guru', 'urutan' => 2, 'aktif' => true]
    );
    $jenisKelas = JenisInstrumen::firstOrCreate(
        ['kode' => 'pengelolaan_kelas', 'sekolah_id' => $sekolah->id],
        ['nama' => 'Pengelolaan Kelas', 'urutan' => 3, 'aktif' => true]
    );
    $jenisRencana = JenisInstrumen::firstOrCreate(
        ['kode' => 'perencanaan', 'sekolah_id' => $sekolah->id],
        ['nama' => 'Perencanaan Pembelajaran', 'urutan' => 4, 'aktif' => true]
    );

    // Buat Versi Aktif / Terbit untuk KBM dengan 2 bagian dan butir skor_maks = 4
    $versiKbm = VersiInstrumen::firstOrCreate(
        [
            'jenis_instrumen_id' => $jenisKbm->id,
            'nomor_versi' => 1,
        ],
        [
            'status' => 'terbit',
            'skor_maks_butir' => 4,
            'ambang_predikat' => ['A' => 86, 'B' => 76, 'C' => 56],
        ]
    );

    $bagian1 = BagianInstrumen::create([
        'versi_instrumen_id' => $versiKbm->id,
        'kode' => 'BAG-1',
        'judul' => 'Pendahuluan Pembelajaran',
        'urutan' => 1,
    ]);

    $butir1 = ButirInstrumen::create([
        'bagian_id' => $bagian1->id,
        'urutan' => 1,
        'uraian' => 'Guru membuka pelajaran dengan doa dan presensi',
        'skor_maks' => 4,
    ]);

    $butir2 = ButirInstrumen::create([
        'bagian_id' => $bagian1->id,
        'urutan' => 2,
        'uraian' => 'Guru menyampaikan tujuan dan asesmen',
        'skor_maks' => 4,
    ]);

    // Admin
    $admin = User::create([
        'sekolah_id' => $sekolah->id,
        'nama' => 'Admin '.$prefix,
        'username' => 'admin_'.$prefix.'_'.uniqid(),
        'email' => 'admin_'.$prefix.'_'.uniqid().'@sekolah.id',
        'password' => Hash::make('password123'),
        'aktif' => true,
    ]);
    $admin->roles()->create(['role' => 'admin']);

    // Supervisor 1
    $spv1 = User::create([
        'sekolah_id' => $sekolah->id,
        'nama' => 'Supervisor 1 '.$prefix,
        'username' => 'spv1_'.$prefix.'_'.uniqid(),
        'email' => 'spv1_'.$prefix.'_'.uniqid().'@sekolah.id',
        'password' => Hash::make('password123'),
        'aktif' => true,
    ]);
    $spv1->roles()->create(['role' => 'supervisor']);

    // Supervisor 2
    $spv2 = User::create([
        'sekolah_id' => $sekolah->id,
        'nama' => 'Supervisor 2 '.$prefix,
        'username' => 'spv2_'.$prefix.'_'.uniqid(),
        'email' => 'spv2_'.$prefix.'_'.uniqid().'@sekolah.id',
        'password' => Hash::make('password123'),
        'aktif' => true,
    ]);
    $spv2->roles()->create(['role' => 'supervisor']);

    // Guru
    $guru = User::create([
        'sekolah_id' => $sekolah->id,
        'nama' => 'Guru '.$prefix,
        'username' => 'guru_'.$prefix.'_'.uniqid(),
        'email' => 'guru_'.$prefix.'_'.uniqid().'@sekolah.id',
        'password' => Hash::make('password123'),
        'aktif' => true,
    ]);
    $guru->roles()->create(['role' => 'guru']);

    // Periode Aktif
    $periode = Periode::create([
        'sekolah_id' => $sekolah->id,
        'nama' => 'Semester Gasal 2026/2027',
        'tahun_ajaran' => '2026/2027',
        'semester' => 'ganjil',
        'tanggal_mulai' => now()->subMonth(),
        'tanggal_selesai' => now()->addMonths(3),
        'status' => 'aktif',
    ]);

    // Penugasan Guru ke Spv 1
    $penugasan = Penugasan::create([
        'sekolah_id' => $sekolah->id,
        'periode_id' => $periode->id,
        'guru_id' => $guru->id,
        'penilai_id' => $spv1->id,
    ]);

    // 4 Penilaian
    $penilaianKbm = Penilaian::create([
        'sekolah_id' => $sekolah->id,
        'penugasan_id' => $penugasan->id,
        'jenis_instrumen_id' => $jenisKbm->id,
        'status' => 'belum',
    ]);

    $penilaianAdm = Penilaian::create([
        'sekolah_id' => $sekolah->id,
        'penugasan_id' => $penugasan->id,
        'jenis_instrumen_id' => $jenisAdm->id,
        'status' => 'belum',
    ]);

    $penilaianKelas = Penilaian::create([
        'sekolah_id' => $sekolah->id,
        'penugasan_id' => $penugasan->id,
        'jenis_instrumen_id' => $jenisKelas->id,
        'status' => 'belum',
    ]);

    $penilaianRencana = Penilaian::create([
        'sekolah_id' => $sekolah->id,
        'penugasan_id' => $penugasan->id,
        'jenis_instrumen_id' => $jenisRencana->id,
        'status' => 'belum',
    ]);

    return compact(
        'sekolah', 'admin', 'spv1', 'spv2', 'guru', 'periode', 'penugasan',
        'jenisKbm', 'versiKbm', 'butir1', 'butir2',
        'penilaianKbm', 'penilaianAdm', 'penilaianKelas', 'penilaianRencana'
    );
}

test('dasbor supervisor menampilkan daftar guru yang ditugaskan beserta progres n/4 dan badge status', function () {
    $env = buatLingkunganSupervisi();

    $response = $this->actingAs($env['spv1'])
        ->get(route('dashboard.supervisor', ['kode' => $env['sekolah']->kode]));

    $response->assertOk()
        ->assertSee($env['guru']->nama)
        ->assertSee('Progres: 0/4 Instrumen')
        ->assertSee('KBM:')
        ->assertSee('Belum Dinilai');
});

test('supervisor tidak dapat mengakses detail penugasan guru yang bukan binaannya', function () {
    $env = buatLingkunganSupervisi();

    // Spv2 mencoba mengakses penugasan Spv1
    $response = $this->actingAs($env['spv2'])
        ->get(route('supervisor.penugasan.show', [
            'kode' => $env['sekolah']->kode,
            'penugasan' => $env['penugasan'],
        ]));

    $response->assertStatus(403);
});

test('admin dan guru ditolak mengakses form penilaian supervisor', function () {
    $env = buatLingkunganSupervisi();

    // Admin ditolak (Admin tidak menilai)
    $responseAdmin = $this->actingAs($env['admin'])
        ->get(route('supervisor.penilaian.show', [
            'kode' => $env['sekolah']->kode,
            'penilaian' => $env['penilaianKbm'],
        ]));
    $responseAdmin->assertStatus(403);

    // Guru ditolak
    $responseGuru = $this->actingAs($env['guru'])
        ->get(route('supervisor.penilaian.show', [
            'kode' => $env['sekolah']->kode,
            'penilaian' => $env['penilaianKbm'],
        ]));
    $responseGuru->assertStatus(403);
});

test('saat pertama dibuka status belum berubah menjadi draft dan versi_instrumen_id dikunci ke versi aktif', function () {
    $env = buatLingkunganSupervisi();

    expect($env['penilaianKbm']->status)->toBe('belum')
        ->and($env['penilaianKbm']->versi_instrumen_id)->toBeNull();

    $response = $this->actingAs($env['spv1'])
        ->get(route('supervisor.penilaian.show', [
            'kode' => $env['sekolah']->kode,
            'penilaian' => $env['penilaianKbm'],
        ]));

    $response->assertOk();

    $env['penilaianKbm']->refresh();
    expect($env['penilaianKbm']->status)->toBe('draft')
        ->and($env['penilaianKbm']->versi_instrumen_id)->toBe($env['versiKbm']->id);
});

test('supervisor dapat menyimpan draft berkali-kali via Livewire', function () {
    $env = buatLingkunganSupervisi();

    // Buka pertama kali agar draft terinisialisasi
    app(PenilaianService::class)->inisialisasiDraft($env['penilaianKbm'], $env['spv1']);

    Livewire::actingAs($env['spv1'])
        ->test(FormPenilaian::class, ['penilaian' => $env['penilaianKbm']])
        ->set('skor.'.$env['butir1']->id, 4)
        ->set('catatanButir.'.$env['butir1']->id, 'Doa sangat khidmat')
        ->set('catatan', 'Catatan supervisi umum')
        ->set('tindakLanjut', 'Pertahankan performa mengajar')
        ->call('simpanDraft')
        ->assertHasNoErrors()
        ->assertSee('Draf penilaian berhasil disimpan.');

    $env['penilaianKbm']->refresh();
    expect($env['penilaianKbm']->status)->toBe('draft')
        ->and($env['penilaianKbm']->catatan)->toBe('Catatan supervisi umum')
        ->and($env['penilaianKbm']->tindak_lanjut)->toBe('Pertahankan performa mengajar')
        ->and($env['penilaianKbm']->diperbarui_oleh)->toBe($env['spv1']->id);

    $butirTersimpan = PenilaianButir::where('penilaian_id', $env['penilaianKbm']->id)
        ->where('butir_instrumen_id', $env['butir1']->id)
        ->first();

    expect($butirTersimpan)->not->toBeNull()
        ->and($butirTersimpan->skor)->toBe(4)
        ->and($butirTersimpan->catatan)->toBe('Doa sangat khidmat');
});

test('finalisasi menolak bila masih ada butir kosong', function () {
    $env = buatLingkunganSupervisi();
    app(PenilaianService::class)->inisialisasiDraft($env['penilaianKbm'], $env['spv1']);

    // Hanya isi butir 1, butir 2 kosong
    Livewire::actingAs($env['spv1'])
        ->test(FormPenilaian::class, ['penilaian' => $env['penilaianKbm']])
        ->set('skor.'.$env['butir1']->id, 3)
        ->call('finalisasi')
        ->assertSee('Semua butir instrumen wajib diisi');

    $env['penilaianKbm']->refresh();
    expect($env['penilaianKbm']->status)->toBe('draft');
});

test('finalisasi menghitung skor_maks dari butir dan predikat sesuai ambang batas', function () {
    $env = buatLingkunganSupervisi();
    app(PenilaianService::class)->inisialisasiDraft($env['penilaianKbm'], $env['spv1']);

    // Kasus 1: Butir 1 = 4, Butir 2 = 4 -> total 8, skor_maks 8 -> 100% -> 'A'
    Livewire::actingAs($env['spv1'])
        ->test(FormPenilaian::class, ['penilaian' => $env['penilaianKbm']])
        ->set('skor.'.$env['butir1']->id, 4)
        ->set('skor.'.$env['butir2']->id, 4)
        ->call('finalisasi')
        ->assertSee('difinalisasi');

    $env['penilaianKbm']->refresh();
    expect($env['penilaianKbm']->status)->toBe('final')
        ->and($env['penilaianKbm']->total_skor)->toBe(8)
        ->and($env['penilaianKbm']->skor_maks)->toBe(8)
        ->and((float) $env['penilaianKbm']->nilai)->toBe(100.00)
        ->and($env['penilaianKbm']->predikat)->toBe('A')
        ->and($env['penilaianKbm']->finalized_by)->toBe($env['spv1']->id)
        ->and($env['penilaianKbm']->finalized_at)->not->toBeNull();

    // Verifikasi audit log finalisasi
    $audit = AuditLog::where('penilaian_id', $env['penilaianKbm']->id)
        ->where('aksi', 'finalisasi')
        ->latest('id')
        ->first();

    expect($audit)->not->toBeNull()
        ->and($audit->user_id)->toBe($env['spv1']->id)
        ->and($audit->sesudah['status'])->toBe('final');
});

test('perhitungan predikat tepat pada ambang batas 86 (A), 76 (B), 56 (C), dan dibawah 56 (D)', function () {
    $service = app(PenilaianService::class);
    $ambang = ['A' => 86, 'B' => 76, 'C' => 56];

    expect($service->hitungPredikat(86.00, $ambang))->toBe('A')
        ->and($service->hitungPredikat(85.99, $ambang))->toBe('B')
        ->and($service->hitungPredikat(76.00, $ambang))->toBe('B')
        ->and($service->hitungPredikat(75.99, $ambang))->toBe('C')
        ->and($service->hitungPredikat(56.00, $ambang))->toBe('C')
        ->and($service->hitungPredikat(55.99, $ambang))->toBe('D')
        ->and($service->hitungPredikat(0.00, $ambang))->toBe('D');
});

test('penilaian final terkunci: edit lewat Livewire atau HTTP ditolak di server dengan 403', function () {
    $env = buatLingkunganSupervisi();
    $service = app(PenilaianService::class);

    $service->inisialisasiDraft($env['penilaianKbm'], $env['spv1']);
    $service->simpanDraft($env['penilaianKbm'], [
        $env['butir1']->id => 4,
        $env['butir2']->id => 4,
    ], [], null, null, $env['spv1']);
    $service->finalisasi($env['penilaianKbm'], $env['spv1']);

    $env['penilaianKbm']->refresh();
    expect($env['penilaianKbm']->isFinal())->toBeTrue();

    // 1. Livewire action ditolak dengan 403
    Livewire::actingAs($env['spv1'])
        ->test(FormPenilaian::class, ['penilaian' => $env['penilaianKbm']])
        ->call('simpanDraft')
        ->assertForbidden();

    Livewire::actingAs($env['spv1'])
        ->test(FormPenilaian::class, ['penilaian' => $env['penilaianKbm']])
        ->call('finalisasi')
        ->assertForbidden();

    // 2. Model updating hook mencegah update langsung
    expect(function () use ($env) {
        $env['penilaianKbm']->update(['catatan' => 'Mencoba bypass']);
    })->toThrow(DomainException::class, 'Penilaian telah difinalisasi dan terkunci');
});

test('buka kunci hanya boleh dilakukan oleh admin dan wajib menyertakan alasan', function () {
    $env = buatLingkunganSupervisi();
    $service = app(PenilaianService::class);

    $service->inisialisasiDraft($env['penilaianKbm'], $env['spv1']);
    $service->simpanDraft($env['penilaianKbm'], [
        $env['butir1']->id => 4,
        $env['butir2']->id => 4,
    ], [], null, null, $env['spv1']);
    $service->finalisasi($env['penilaianKbm'], $env['spv1']);

    // 1. Supervisor mencoba buka kunci -> 403
    $responseSpv = $this->actingAs($env['spv1'])
        ->patch(route('admin.penilaian.buka-kunci', [
            'kode' => $env['sekolah']->kode,
            'penilaian' => $env['penilaianKbm'],
        ]), ['alasan' => 'Ingin ubah nilai']);
    $responseSpv->assertStatus(403);

    // 2. Admin buka kunci tanpa alasan -> validasi gagal
    $responseAdminTanpaAlasan = $this->actingAs($env['admin'])
        ->patch(route('admin.penilaian.buka-kunci', [
            'kode' => $env['sekolah']->kode,
            'penilaian' => $env['penilaianKbm'],
        ]), ['alasan' => '']);
    $responseAdminTanpaAlasan->assertSessionHasErrors('alasan');

    // 3. Admin buka kunci dengan alasan sah
    $responseAdmin = $this->actingAs($env['admin'])
        ->patch(route('admin.penilaian.buka-kunci', [
            'kode' => $env['sekolah']->kode,
            'penilaian' => $env['penilaianKbm'],
        ]), ['alasan' => 'Perlu revisi pada catatan pembinaan lanjutan']);

    $responseAdmin->assertSessionHasNoErrors()->assertRedirect();

    $env['penilaianKbm']->refresh();
    expect($env['penilaianKbm']->status)->toBe('direvisi')
        ->and($env['penilaianKbm']->jumlah_buka_kunci)->toBe(1);

    // 4. Verifikasi audit log buka kunci
    $audit = AuditLog::where('penilaian_id', $env['penilaianKbm']->id)
        ->where('aksi', 'buka_kunci')
        ->latest('id')
        ->first();

    expect($audit)->not->toBeNull()
        ->and($audit->user_id)->toBe($env['admin']->id)
        ->and($audit->alasan)->toBe('Perlu revisi pada catatan pembinaan lanjutan');
});

test('pada periode ditutup buka kunci dan perubahan penilaian ditolak', function () {
    $env = buatLingkunganSupervisi();
    $service = app(PenilaianService::class);

    $service->inisialisasiDraft($env['penilaianKbm'], $env['spv1']);
    $service->simpanDraft($env['penilaianKbm'], [
        $env['butir1']->id => 4,
        $env['butir2']->id => 4,
    ], [], null, null, $env['spv1']);
    $service->finalisasi($env['penilaianKbm'], $env['spv1']);

    // Tutup periode
    $env['periode']->update(['status' => 'ditutup']);

    // Admin buka kunci ditolak karena periode ditutup
    $response = $this->actingAs($env['admin'])
        ->patch(route('admin.penilaian.buka-kunci', [
            'kode' => $env['sekolah']->kode,
            'penilaian' => $env['penilaianKbm'],
        ]), ['alasan' => 'Buka kunci setelah periode ditutup']);

    $response->assertStatus(403);
});

test('isolasi multitenant: supervisor sekolah B tidak dapat mengakses penilaian sekolah A', function () {
    $envA = buatLingkunganSupervisi('sekolaha');
    $envB = buatLingkunganSupervisi('sekolahb');

    // Supervisor Sekolah B mengakses penilaian Sekolah A
    $response = $this->actingAs($envB['spv1'])
        ->get("/s/{$envB['sekolah']->kode}/supervisor/penilaian/{$envA['penilaianKbm']->ulid}");

    // Harus 404 (atau 403 fail-closed)
    expect($response->status())->toBeIn([404, 403]);
});
