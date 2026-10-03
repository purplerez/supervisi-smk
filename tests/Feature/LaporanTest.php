<?php

use App\Exports\LaporanKetuntasanExport;
use App\Exports\RekapPeriodeExport;
use App\Models\BagianInstrumen;
use App\Models\ButirInstrumen;
use App\Models\InfoSupervisi;
use App\Models\JenisInstrumen;
use App\Models\Penilaian;
use App\Models\PenilaianButir;
use App\Models\Penugasan;
use App\Models\Periode;
use App\Models\Sekolah;
use App\Models\User;
use App\Models\VersiInstrumen;
use App\Tenant\TenantContext;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Maatwebsite\Excel\Facades\Excel;

uses(RefreshDatabase::class);

beforeEach(function () {
    TenantContext::clear();
});

afterEach(function () {
    TenantContext::clear();
});

/**
 * Helper untuk membuat lingkungan supervisi lengkap untuk pengujian laporan.
 */
function buatLingkunganLaporan(string $prefix = 'smk1'): array
{
    TenantContext::clear();

    $sekolah = Sekolah::create([
        'nama' => 'SMK Negeri '.strtoupper($prefix),
        'kode' => $prefix.'-'.uniqid(),
        'alamat' => 'Jl. Pendidikan No. 45, Kota '.strtoupper($prefix),
        'status' => 'aktif',
    ]);

    TenantContext::set($sekolah->id);

    // 4 Jenis Instrumen
    $jenisKbm = JenisInstrumen::firstOrCreate(
        ['kode' => 'kbm', 'sekolah_id' => $sekolah->id],
        ['nama' => 'Kegiatan Belajar Mengajar', 'urutan' => 1, 'aktif' => true]
    );
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

    // Versi terbit untuk KBM dengan butir
    $versiKbm = VersiInstrumen::create([
        'jenis_instrumen_id' => $jenisKbm->id,
        'nomor_versi' => 1,
        'status' => 'terbit',
        'skor_maks_butir' => 4,
        'ambang_predikat' => ['A' => 86, 'B' => 76, 'C' => 56],
    ]);

    $bagian1 = BagianInstrumen::create([
        'versi_instrumen_id' => $versiKbm->id,
        'kode' => 'BAG-1',
        'judul' => 'Kegiatan Pendahuluan',
        'urutan' => 1,
    ]);

    $butir1 = ButirInstrumen::create([
        'bagian_id' => $bagian1->id,
        'urutan' => 1,
        'uraian' => 'Guru menyapa siswa dan memimpin doa bersama',
        'skor_maks' => 4,
    ]);

    $butir2 = ButirInstrumen::create([
        'bagian_id' => $bagian1->id,
        'urutan' => 2,
        'uraian' => 'Guru menyampaikan apersepsi dan tujuan pembelajaran',
        'skor_maks' => 4,
    ]);

    // Admin Sekolah
    $admin = User::create([
        'sekolah_id' => $sekolah->id,
        'nama' => 'Admin '.$prefix,
        'username' => 'admin_'.$prefix.'_'.uniqid(),
        'email' => 'admin_'.$prefix.'_'.uniqid().'@sekolah.id',
        'nip' => '198501012010011001',
        'password' => Hash::make('password123'),
        'aktif' => true,
    ]);
    $admin->roles()->create(['role' => 'admin']);

    // Supervisor 1 & 2
    $spv1 = User::create([
        'sekolah_id' => $sekolah->id,
        'nama' => 'Supervisor Satu '.$prefix,
        'username' => 'spv1_'.$prefix.'_'.uniqid(),
        'email' => 'spv1_'.$prefix.'_'.uniqid().'@sekolah.id',
        'nip' => '197505052000031002',
        'password' => Hash::make('password123'),
        'aktif' => true,
    ]);
    $spv1->roles()->create(['role' => 'supervisor']);

    $spv2 = User::create([
        'sekolah_id' => $sekolah->id,
        'nama' => 'Supervisor Dua '.$prefix,
        'username' => 'spv2_'.$prefix.'_'.uniqid(),
        'email' => 'spv2_'.$prefix.'_'.uniqid().'@sekolah.id',
        'nip' => '197606062001031003',
        'password' => Hash::make('password123'),
        'aktif' => true,
    ]);
    $spv2->roles()->create(['role' => 'supervisor']);

    // Guru 1 (Binaan Spv 1)
    $guru1 = User::create([
        'sekolah_id' => $sekolah->id,
        'nama' => 'Ahmad Guru '.$prefix,
        'username' => 'guru1_'.$prefix.'_'.uniqid(),
        'email' => 'guru1_'.$prefix.'_'.uniqid().'@sekolah.id',
        'nip' => '199001012015011005',
        'password' => Hash::make('password123'),
        'aktif' => true,
    ]);
    $guru1->roles()->create(['role' => 'guru']);

    // Guru 2 (Binaan Spv 2)
    $guru2 = User::create([
        'sekolah_id' => $sekolah->id,
        'nama' => 'Budi Guru '.$prefix,
        'username' => 'guru2_'.$prefix.'_'.uniqid(),
        'email' => 'guru2_'.$prefix.'_'.uniqid().'@sekolah.id',
        'nip' => '199102022016021006',
        'password' => Hash::make('password123'),
        'aktif' => true,
    ]);
    $guru2->roles()->create(['role' => 'guru']);

    // Guru 3 (Bukan binaan, atau belum dinilai)
    $guru3 = User::create([
        'sekolah_id' => $sekolah->id,
        'nama' => 'Citra Guru '.$prefix,
        'username' => 'guru3_'.$prefix.'_'.uniqid(),
        'email' => 'guru3_'.$prefix.'_'.uniqid().'@sekolah.id',
        'nip' => '199203032017032007',
        'password' => Hash::make('password123'),
        'aktif' => true,
    ]);
    $guru3->roles()->create(['role' => 'guru']);

    // Periode Aktif
    $periode = Periode::create([
        'sekolah_id' => $sekolah->id,
        'nama' => 'Tahun Ajaran 2026/2027 Ganjil',
        'tahun_ajaran' => '2026/2027',
        'semester' => 'ganjil',
        'tanggal_mulai' => now()->subMonth(),
        'tanggal_selesai' => now()->addMonths(2),
        'status' => 'aktif',
    ]);

    // Penugasan Guru 1 -> Spv 1
    $penugasan1 = Penugasan::create([
        'sekolah_id' => $sekolah->id,
        'periode_id' => $periode->id,
        'guru_id' => $guru1->id,
        'penilai_id' => $spv1->id,
    ]);

    // Info supervisi Guru 1
    InfoSupervisi::create([
        'sekolah_id' => $sekolah->id,
        'penugasan_id' => $penugasan1->id,
        'kelas' => 'XII RPL 1',
        'semester' => 'Ganjil',
        'fase' => 'F',
        'mata_pelajaran' => 'Pemrograman Web dan Perangkat Bergerak',
        'elemen' => 'Pemrograman Aplikasi Web',
        'capaian_pembelajaran' => 'Peserta didik mampu merancang dan membuat aplikasi web dinamis dengan framework.',
        'catatan' => 'Fokus pengamatan pada kolaborasi antar siswa.',
    ]);

    // 4 Penilaian Guru 1 (Semua FINAL = Selesai 4/4)
    $penilaian1Kbm = Penilaian::create([
        'sekolah_id' => $sekolah->id,
        'penugasan_id' => $penugasan1->id,
        'jenis_instrumen_id' => $jenisKbm->id,
        'versi_instrumen_id' => $versiKbm->id,
        'status' => 'final',
        'total_skor' => 8,
        'skor_maks' => 8,
        'nilai' => 100.00,
        'predikat' => 'A',
        'catatan' => 'Sangat menguasai materi.',
        'tindak_lanjut' => 'Pertahankan dan tularkan kepada rekan sejawat.',
        'finalized_at' => now(),
        'finalized_by' => $spv1->id,
    ]);

    // Isi butir penilaian KBM
    PenilaianButir::create([
        'sekolah_id' => $sekolah->id,
        'penilaian_id' => $penilaian1Kbm->id,
        'butir_instrumen_id' => $butir1->id,
        'skor' => 4,
        'catatan' => 'Doa khusyuk dan presensi lengkap',
    ]);
    PenilaianButir::create([
        'sekolah_id' => $sekolah->id,
        'penilaian_id' => $penilaian1Kbm->id,
        'butir_instrumen_id' => $butir2->id,
        'skor' => 4,
        'catatan' => 'Tujuan dijelaskan sangat jelas',
    ]);

    $penilaian1Adm = Penilaian::create([
        'sekolah_id' => $sekolah->id,
        'penugasan_id' => $penugasan1->id,
        'jenis_instrumen_id' => $jenisAdm->id,
        'status' => 'final',
        'total_skor' => 36,
        'skor_maks' => 40,
        'nilai' => 90.00,
        'predikat' => 'A',
        'finalized_at' => now(),
        'finalized_by' => $spv1->id,
    ]);

    $penilaian1Kelas = Penilaian::create([
        'sekolah_id' => $sekolah->id,
        'penugasan_id' => $penugasan1->id,
        'jenis_instrumen_id' => $jenisKelas->id,
        'status' => 'final',
        'total_skor' => 32,
        'skor_maks' => 40,
        'nilai' => 80.00,
        'predikat' => 'B',
        'finalized_at' => now(),
        'finalized_by' => $spv1->id,
    ]);

    $penilaian1Rencana = Penilaian::create([
        'sekolah_id' => $sekolah->id,
        'penugasan_id' => $penugasan1->id,
        'jenis_instrumen_id' => $jenisRencana->id,
        'status' => 'final',
        'total_skor' => 34,
        'skor_maks' => 40,
        'nilai' => 85.00,
        'predikat' => 'B',
        'finalized_at' => now(),
        'finalized_by' => $spv1->id,
    ]);

    // Penugasan Guru 2 -> Spv 2 (Hanya 1 draft, 3 belum = Belum Selesai 0/4)
    $penugasan2 = Penugasan::create([
        'sekolah_id' => $sekolah->id,
        'periode_id' => $periode->id,
        'guru_id' => $guru2->id,
        'penilai_id' => $spv2->id,
    ]);

    $penilaian2Kbm = Penilaian::create([
        'sekolah_id' => $sekolah->id,
        'penugasan_id' => $penugasan2->id,
        'jenis_instrumen_id' => $jenisKbm->id,
        'versi_instrumen_id' => $versiKbm->id,
        'status' => 'draft',
        'total_skor' => 4,
        'skor_maks' => 8,
        'nilai' => 50.00,
        'predikat' => 'D',
    ]);

    $penilaian2Adm = Penilaian::create([
        'sekolah_id' => $sekolah->id,
        'penugasan_id' => $penugasan2->id,
        'jenis_instrumen_id' => $jenisAdm->id,
        'status' => 'belum',
    ]);

    $penilaian2Kelas = Penilaian::create([
        'sekolah_id' => $sekolah->id,
        'penugasan_id' => $penugasan2->id,
        'jenis_instrumen_id' => $jenisKelas->id,
        'status' => 'belum',
    ]);

    $penilaian2Rencana = Penilaian::create([
        'sekolah_id' => $sekolah->id,
        'penugasan_id' => $penugasan2->id,
        'jenis_instrumen_id' => $jenisRencana->id,
        'status' => 'belum',
    ]);

    return compact(
        'sekolah', 'admin', 'spv1', 'spv2', 'guru1', 'guru2', 'guru3',
        'periode', 'penugasan1', 'penugasan2',
        'jenisKbm', 'jenisAdm', 'jenisKelas', 'jenisRencana',
        'versiKbm', 'bagian1', 'butir1', 'butir2',
        'penilaian1Kbm', 'penilaian1Adm', 'penilaian1Kelas', 'penilaian1Rencana',
        'penilaian2Kbm', 'penilaian2Adm', 'penilaian2Kelas', 'penilaian2Rencana'
    );
}

/*
|--------------------------------------------------------------------------
| 1. Pengujian Halaman Rekap Admin
|--------------------------------------------------------------------------
*/

test('admin dapat melihat halaman rekap laporan supervisi dengan daftar guru, progres n/4, dan status', function () {
    $env = buatLingkunganLaporan();

    $response = $this->actingAs($env['admin'])
        ->get(route('admin.laporan.index', [
            'kode' => $env['sekolah']->kode,
            'periode_id' => $env['periode']->id,
        ]));

    $response->assertOk()
        ->assertSee('Rekap & Laporan Supervisi')
        ->assertSee($env['guru1']->nama)
        ->assertSee($env['guru2']->nama)
        ->assertSee($env['spv1']->nama)
        ->assertSee($env['spv2']->nama)
        ->assertSee('4/4') // Guru 1 selesai 4/4
        ->assertSee('0/4') // Guru 2 belum selesai 0/4
        ->assertSee('Selesai')
        ->assertSee('Belum Selesai');
});

test('admin dapat memfilter rekap berdasarkan status ketuntasan selesai dan belum selesai', function () {
    $env = buatLingkunganLaporan();

    // Filter Selesai -> hanya tampil Guru 1
    $responseSelesai = $this->actingAs($env['admin'])
        ->get(route('admin.laporan.index', [
            'kode' => $env['sekolah']->kode,
            'periode_id' => $env['periode']->id,
            'status' => 'selesai',
        ]));

    $responseSelesai->assertOk()
        ->assertSee($env['guru1']->nama)
        ->assertDontSee($env['guru2']->nama);

    // Filter Belum Selesai -> hanya tampil Guru 2
    $responseBelum = $this->actingAs($env['admin'])
        ->get(route('admin.laporan.index', [
            'kode' => $env['sekolah']->kode,
            'periode_id' => $env['periode']->id,
            'status' => 'belum_selesai',
        ]));

    $responseBelum->assertOk()
        ->assertSee($env['guru2']->nama)
        ->assertDontSee($env['guru1']->nama);
});

test('admin dapat mencari guru berdasarkan nama pada rekap laporan', function () {
    $env = buatLingkunganLaporan();

    $response = $this->actingAs($env['admin'])
        ->get(route('admin.laporan.index', [
            'kode' => $env['sekolah']->kode,
            'periode_id' => $env['periode']->id,
            'cari' => 'Ahmad',
        ]));

    $response->assertOk()
        ->assertSee($env['guru1']->nama)
        ->assertDontSee($env['guru2']->nama);
});

test('non-admin ditolak saat mencoba mengakses halaman rekap admin', function () {
    $env = buatLingkunganLaporan();

    $this->actingAs($env['spv1'])
        ->get(route('admin.laporan.index', ['kode' => $env['sekolah']->kode]))
        ->assertForbidden();

    $this->actingAs($env['guru1'])
        ->get(route('admin.laporan.index', ['kode' => $env['sekolah']->kode]))
        ->assertForbidden();
});

/*
|--------------------------------------------------------------------------
| 2. Pengujian Ekspor Excel (maatwebsite/excel)
|--------------------------------------------------------------------------
*/

test('admin dapat mengunduh rekap periode excel dengan kolom dan nilai yang benar', function () {
    $env = buatLingkunganLaporan();

    Excel::fake();

    $response = $this->actingAs($env['admin'])
        ->get(route('admin.laporan.export.rekap', [
            'kode' => $env['sekolah']->kode,
            'periode_id' => $env['periode']->id,
        ]));

    $response->assertOk();

    Excel::assertDownloaded('Rekap-Supervisi-'.str_replace(['/', '\\', ' '], '-', $env['periode']->nama).'.xlsx', function (RekapPeriodeExport $export) use ($env) {
        $headings = $export->headings();
        expect($headings)->toContain('No', 'Nama Guru', 'NIP', 'Supervisor Penilai', 'Rata-rata Nilai', 'Jumlah Final (n/4)', 'Status');

        $collection = $export->collection();
        expect($collection)->toHaveCount(2);

        // Uji pemetaan baris Guru 1 (semua final)
        $barisGuru1 = $export->map($env['penugasan1']);
        expect($barisGuru1[1])->toBe($env['guru1']->nama);
        expect($barisGuru1[4])->toBe(100.0); // KBM
        expect($barisGuru1[5])->toBe('A');
        expect($barisGuru1[12])->toBe(88.75); // Rata-rata (100 + 90 + 80 + 85) / 4
        expect($barisGuru1[13])->toBe('4/4'); // 4/4
        expect($barisGuru1[14])->toBe('Selesai');

        // Uji pemetaan baris Guru 2 (belum final: nilai & predikat harus string kosong '')
        $barisGuru2 = $export->map($env['penugasan2']);
        expect($barisGuru2[1])->toBe($env['guru2']->nama);
        expect($barisGuru2[4])->toBe(''); // KBM draft -> kosong
        expect($barisGuru2[5])->toBe(''); // predikat kosong
        expect($barisGuru2[12])->toBe(''); // rata-rata kosong '' karena belum ada final
        expect($barisGuru2[13])->toBe('0/4'); // 0/4
        expect($barisGuru2[14])->toBe('Belum Selesai');

        return true;
    });
});

test('admin dapat mengunduh laporan ketuntasan excel terurut berdasarkan ketuntasan', function () {
    $env = buatLingkunganLaporan();

    Excel::fake();

    $response = $this->actingAs($env['admin'])
        ->get(route('admin.laporan.export.ketuntasan', [
            'kode' => $env['sekolah']->kode,
            'periode_id' => $env['periode']->id,
        ]));

    $response->assertOk();

    Excel::assertDownloaded('Laporan-Ketuntasan-Supervisi-'.str_replace(['/', '\\', ' '], '-', $env['periode']->nama).'.xlsx', function (LaporanKetuntasanExport $export) use ($env) {
        $collection = $export->collection();
        // Terurut: Guru 1 (4 final) lebih dulu dari Guru 2 (0 final)
        expect($collection->first()->id)->toBe($env['penugasan1']->id);
        expect($collection->last()->id)->toBe($env['penugasan2']->id);

        return true;
    });
});

test('supervisor dapat mengunduh progres guru binaannya dan tidak memuat guru binaan supervisor lain', function () {
    $env = buatLingkunganLaporan();

    Excel::fake();

    // Spv 1 mengunduh progres bimbingan
    $response = $this->actingAs($env['spv1'])
        ->get(route('supervisor.laporan.export.rekap', [
            'kode' => $env['sekolah']->kode,
            'periode_id' => $env['periode']->id,
        ]));

    $response->assertOk();

    $safeSpv = str_replace(['/', '\\', ' '], '-', $env['spv1']->username);
    $safePeriode = str_replace(['/', '\\', ' '], '-', $env['periode']->nama);

    Excel::assertDownloaded('Progres-Bimbingan-'.$safeSpv.'-'.$safePeriode.'.xlsx', function (RekapPeriodeExport $export) use ($env) {
        $collection = $export->collection();
        // Hanya memuat Guru 1 (binaan Spv 1), TIDAK memuat Guru 2 (binaan Spv 2)
        expect($collection)->toHaveCount(1);
        expect($collection->first()->guru_id)->toBe($env['guru1']->id);

        return true;
    });
});

/*
|--------------------------------------------------------------------------
| 3. Pengujian Multitenant Pada Ekspor & Laporan
|--------------------------------------------------------------------------
*/

test('ekspor excel tidak pernah memuat data dari sekolah lain (tenant isolation)', function () {
    $envA = buatLingkunganLaporan('sekolaha');
    $envB = buatLingkunganLaporan('sekolahb');

    // Admin Sekolah A mengunduh
    TenantContext::set($envA['sekolah']->id);
    $exportA = new RekapPeriodeExport($envA['periode']);
    $dataA = $exportA->collection();

    // Pastikan hanya memuat guru dari Sekolah A
    foreach ($dataA as $p) {
        expect($p->sekolah_id)->toBe($envA['sekolah']->id);
        expect($p->guru->sekolah_id)->toBe($envA['sekolah']->id);
        expect($p->penilai->sekolah_id)->toBe($envA['sekolah']->id);
    }

    // Pastikan tidak ada satupun ID dari Sekolah B
    $guruIdsA = $dataA->pluck('guru_id')->all();
    expect($guruIdsA)->not->toContain($envB['guru1']->id);
    expect($guruIdsA)->not->toContain($envB['guru2']->id);
});

test('admin sekolah B tidak dapat mengunduh laporan dari sekolah A (404/redirect)', function () {
    $envA = buatLingkunganLaporan('sekolaha');
    $envB = buatLingkunganLaporan('sekolahb');

    // Admin B mencoba mengakses URL unduhan Sekolah A
    $response = $this->actingAs($envB['admin'])
        ->get(route('admin.laporan.export.rekap', [
            'kode' => $envA['sekolah']->kode,
            'periode_id' => $envA['periode']->id,
        ]));

    // Ditolak oleh SetTenantContext (redirect ke login dengan error akun tidak terdaftar)
    $response->assertRedirect(route('sekolah.login', ['kode' => $envA['sekolah']->kode]));
});

/*
|--------------------------------------------------------------------------
| 4. Pengujian Cetak Hasil Penilaian PDF (DomPDF, A4)
|--------------------------------------------------------------------------
*/

test('cetak pdf berhasil untuk penilaian yang berstatus final oleh admin, supervisor penilai, dan guru bersangkutan', function () {
    $env = buatLingkunganLaporan();

    $penilaianFinal = $env['penilaian1Kbm'];

    // 1. Admin sekolah dapat mencetak PDF
    $resAdmin = $this->actingAs($env['admin'])
        ->get(route('penilaian.cetak-pdf', [
            'kode' => $env['sekolah']->kode,
            'penilaian' => $penilaianFinal,
        ]));
    $resAdmin->assertOk()
        ->assertHeader('Content-Type', 'application/pdf');

    // 2. Supervisor penilai bersangkutan dapat mencetak PDF
    $resSpv = $this->actingAs($env['spv1'])
        ->get(route('penilaian.cetak-pdf', [
            'kode' => $env['sekolah']->kode,
            'penilaian' => $penilaianFinal,
        ]));
    $resSpv->assertOk()
        ->assertHeader('Content-Type', 'application/pdf');

    // 3. Guru yang dinilai bersangkutan dapat mencetak PDF
    $resGuru = $this->actingAs($env['guru1'])
        ->get(route('penilaian.cetak-pdf', [
            'kode' => $env['sekolah']->kode,
            'penilaian' => $penilaianFinal,
        ]));
    $resGuru->assertOk()
        ->assertHeader('Content-Type', 'application/pdf');
});

test('cetak pdf ditolak (403) jika status penilaian belum final', function () {
    $env = buatLingkunganLaporan();

    // Penilaian Guru 2 KBM berstatus draft
    $penilaianDraft = $env['penilaian2Kbm'];

    // Admin mencoba cetak penilaian draft -> 403
    $this->actingAs($env['admin'])
        ->get(route('penilaian.cetak-pdf', [
            'kode' => $env['sekolah']->kode,
            'penilaian' => $penilaianDraft,
        ]))
        ->assertForbidden();

    // Penilai mencoba cetak penilaian draft -> 403
    $this->actingAs($env['spv2'])
        ->get(route('penilaian.cetak-pdf', [
            'kode' => $env['sekolah']->kode,
            'penilaian' => $penilaianDraft,
        ]))
        ->assertForbidden();

    // Penilaian berstatus belum -> 403
    $penilaianBelum = $env['penilaian2Adm'];
    $this->actingAs($env['admin'])
        ->get(route('penilaian.cetak-pdf', [
            'kode' => $env['sekolah']->kode,
            'penilaian' => $penilaianBelum,
        ]))
        ->assertForbidden();
});

test('cetak pdf ditolak (403) jika diakses oleh supervisor atau guru yang tidak bersangkutan', function () {
    $env = buatLingkunganLaporan();

    $penilaianFinalGuru1 = $env['penilaian1Kbm'];

    // Supervisor 2 (bukan penilai Guru 1) mencoba mengakses PDF -> 403
    $this->actingAs($env['spv2'])
        ->get(route('penilaian.cetak-pdf', [
            'kode' => $env['sekolah']->kode,
            'penilaian' => $penilaianFinalGuru1,
        ]))
        ->assertForbidden();

    // Guru 2 (bukan guru yang dinilai) mencoba mengakses PDF -> 403
    $this->actingAs($env['guru2'])
        ->get(route('penilaian.cetak-pdf', [
            'kode' => $env['sekolah']->kode,
            'penilaian' => $penilaianFinalGuru1,
        ]))
        ->assertForbidden();
});

test('cetak pdf ditolak jika diakses lintas tenant oleh user sekolah lain', function () {
    $envA = buatLingkunganLaporan('sekolaha');
    $envB = buatLingkunganLaporan('sekolahb');

    $penilaianFinalA = $envA['penilaian1Kbm'];

    // 1. Admin B mencoba mengakses penilaian Sekolah A lewat rute Sekolah B -> 404 (tidak ditemukan dalam tenant B)
    $response404 = $this->actingAs($envB['admin'])
        ->withSession(['active_role' => 'admin', 'sekolah_kode' => $envB['sekolah']->kode])
        ->get("/s/{$envB['sekolah']->kode}/penilaian/{$penilaianFinalA->ulid}/cetak-pdf");

    $response404->assertNotFound();

    // 2. Admin B mencoba mengakses langsung rute Sekolah A -> dialihkan (ditolak)
    $responseDirect = $this->actingAs($envB['admin'])
        ->get("/s/{$envA['sekolah']->kode}/penilaian/{$penilaianFinalA->ulid}/cetak-pdf");

    $responseDirect->assertRedirect(route('sekolah.login', ['kode' => $envA['sekolah']->kode]));
});
