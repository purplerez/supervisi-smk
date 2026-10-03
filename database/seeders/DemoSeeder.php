<?php

namespace Database\Seeders;

use App\Models\BagianInstrumen;
use App\Models\ButirInstrumen;
use App\Models\InfoSupervisi;
use App\Models\JadwalSupervisi;
use App\Models\JenisInstrumen;
use App\Models\Penilaian;
use App\Models\PenilaianButir;
use App\Models\Penugasan;
use App\Models\Periode;
use App\Models\Sekolah;
use App\Models\SuratTugas;
use App\Models\User;
use App\Models\VersiInstrumen;
use App\Tenant\TenantContext;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class DemoSeeder extends Seeder
{
    /**
     * Jalankan seeder data demo untuk lingkungan lokal.
     */
    public function run(): void
    {
        if (! app()->isLocal()) {
            $this->command?->warn('DemoSeeder hanya dapat dijalankan pada lingkungan local.');

            return;
        }

        TenantContext::clear();

        // 1. Akun Super Admin Global
        User::tanpaTenant()->firstOrCreate(
            ['username' => 'superadmin'],
            [
                'nama' => 'Super Administrator',
                'email' => 'superadmin@supervisi.test',
                'password' => Hash::make('password123'),
                'is_super_admin' => true,
                'must_change_password' => false,
                'aktif' => true,
            ]
        );

        // Pastikan template global instrumen ada
        $this->call(InstrumenSeeder::class);

        // 2. Buat 2 Sekolah Demo
        $daftarSekolahData = [
            [
                'nama' => 'SMK Negeri 1 Surabaya',
                'npsn' => '20539991',
                'kode' => 'smkn1-sby',
                'alamat' => 'Jl. SMEA No. 4, Wonokromo, Kota Surabaya, Jawa Timur',
                'kepsek_nama' => 'Drs. H. Bambang Sutrisno, M.Pd.',
                'kepsek_nip' => '196805121994031005',
                'spv_nama' => 'Budi Santoso, S.Pd., M.T.',
                'spv_nip' => '197508212000031002',
            ],
            [
                'nama' => 'SMK Negeri 2 Malang',
                'npsn' => '20539992',
                'kode' => 'smkn2-mlg',
                'alamat' => 'Jl. Veteran No. 17, Kota Malang, Jawa Timur',
                'kepsek_nama' => 'Dr. Hj. Siti Aminah, M.Pd.',
                'kepsek_nip' => '197103141997022001',
                'spv_nama' => 'Agus Priyono, S.Kom., M.Cs.',
                'spv_nip' => '197809152002121004',
            ],
        ];

        foreach ($daftarSekolahData as $sData) {
            $this->seedSekolahDemo($sData);
        }

        TenantContext::clear();
    }

    /**
     * Seed satu sekolah demo lengkap dengan pengguna, periode, instrumen, dan penugasan.
     */
    private function seedSekolahDemo(array $data): void
    {
        TenantContext::clear();

        $sekolah = Sekolah::firstOrCreate(
            ['kode' => $data['kode']],
            [
                'nama' => $data['nama'],
                'npsn' => $data['npsn'],
                'alamat' => $data['alamat'],
                'status' => 'aktif',
            ]
        );

        TenantContext::set($sekolah->id);

        // A. Admin Sekolah
        $admin = User::firstOrCreate(
            ['username' => 'admin_'.$data['kode']],
            [
                'sekolah_id' => $sekolah->id,
                'nama' => 'Admin '.$data['nama'],
                'email' => 'admin@'.$data['kode'].'.sch.id',
                'nip' => '198801012012011001',
                'password' => Hash::make('password123'),
                'must_change_password' => false,
                'aktif' => true,
            ]
        );
        $admin->roles()->firstOrCreate(['sekolah_id' => $sekolah->id, 'role' => 'admin']);

        // B. Supervisor 1 (Menjabat Kepala Sekolah)
        $kepsek = User::firstOrCreate(
            ['username' => 'kepsek_'.$data['kode']],
            [
                'sekolah_id' => $sekolah->id,
                'nama' => $data['kepsek_nama'],
                'email' => 'kepsek@'.$data['kode'].'.sch.id',
                'nip' => $data['kepsek_nip'],
                'password' => Hash::make('password123'),
                'must_change_password' => false,
                'aktif' => true,
            ]
        );
        $kepsek->roles()->firstOrCreate(['sekolah_id' => $sekolah->id, 'role' => 'supervisor']);

        // Set Kepala Sekolah pada profil sekolah
        $sekolah->update(['kepala_sekolah_id' => $kepsek->id]);

        // C. Supervisor 2 (Merangkap Peran Guru)
        $spvGuru = User::firstOrCreate(
            ['username' => 'spv_'.$data['kode']],
            [
                'sekolah_id' => $sekolah->id,
                'nama' => $data['spv_nama'],
                'email' => 'spv@'.$data['kode'].'.sch.id',
                'nip' => $data['spv_nip'],
                'password' => Hash::make('password123'),
                'must_change_password' => false,
                'aktif' => true,
            ]
        );
        $spvGuru->roles()->firstOrCreate(['sekolah_id' => $sekolah->id, 'role' => 'supervisor']);
        $spvGuru->roles()->firstOrCreate(['sekolah_id' => $sekolah->id, 'role' => 'guru']);

        // D. 5 Guru Tambahan (Total 6 Guru bersama Supervisor 2)
        $daftarGuruData = [
            ['nama' => 'Dewi Lestari, S.Pd.', 'username' => 'guru1_'.$data['kode'], 'nip' => '198501152010012015', 'mapel' => 'Bahasa Indonesia'],
            ['nama' => 'Eko Prasetyo, S.Kom.', 'username' => 'guru2_'.$data['kode'], 'nip' => '198804202014021003', 'mapel' => 'Informatika'],
            ['nama' => 'Fajar Nugroho, S.Pd.', 'username' => 'guru3_'.$data['kode'], 'nip' => '199011122016011004', 'mapel' => 'Matematika'],
            ['nama' => 'Gita Permata, M.Pd.', 'username' => 'guru4_'.$data['kode'], 'nip' => '199203252019032011', 'mapel' => 'Bahasa Inggris'],
            ['nama' => 'Hendra Wijaya, S.Pd.', 'username' => 'guru5_'.$data['kode'], 'nip' => '199407182020121008', 'mapel' => 'Dasar Kejuruan RPL'],
        ];

        $guruList = [];
        foreach ($daftarGuruData as $gData) {
            $guru = User::firstOrCreate(
                ['username' => $gData['username']],
                [
                    'sekolah_id' => $sekolah->id,
                    'nama' => $gData['nama'],
                    'email' => $gData['username'].'@'.$data['kode'].'.sch.id',
                    'nip' => $gData['nip'],
                    'password' => Hash::make('password123'),
                    'must_change_password' => false,
                    'aktif' => true,
                ]
            );
            $guru->roles()->firstOrCreate(['sekolah_id' => $sekolah->id, 'role' => 'guru']);
            $guruList[] = ['user' => $guru, 'mapel' => $gData['mapel']];
        }

        // Tambahkan spvGuru sebagai guru ke-6
        $guruList[] = ['user' => $spvGuru, 'mapel' => 'Pemrograman Berorientasi Objek'];

        // E. Buat Instrumen Sekolah Terbit
        $instrumenSekolah = $this->buatInstrumenSekolah($sekolah);

        // F. Periode Aktif
        $periode = Periode::firstOrCreate(
            ['sekolah_id' => $sekolah->id, 'status' => 'aktif'],
            [
                'nama' => 'Tahun Ajaran 2026/2027 - Semester Ganjil',
                'tahun_ajaran' => '2026/2027',
                'semester' => 'ganjil',
                'tanggal_mulai' => now()->subMonths(2),
                'tanggal_selesai' => now()->addMonths(3),
            ]
        );

        // G. Penugasan & Variasi Penilaian
        // 1. Guru 1 (dinilai Kepsek): Selesai (4/4 Final)
        $this->buatPenugasanDenganPenilaian(
            $sekolah, $periode, $guruList[0]['user'], $kepsek, $guruList[0]['mapel'], $instrumenSekolah,
            statusPilihan: ['kbm' => 'final', 'adm' => 'final', 'kelas' => 'final', 'rencana' => 'final']
        );

        // 2. Guru 2 (dinilai Kepsek): Sedang Dinilai (1 Final, 2 Draft, 1 Belum)
        $this->buatPenugasanDenganPenilaian(
            $sekolah, $periode, $guruList[1]['user'], $kepsek, $guruList[1]['mapel'], $instrumenSekolah,
            statusPilihan: ['kbm' => 'final', 'adm' => 'draft', 'kelas' => 'draft', 'rencana' => 'belum']
        );

        // 3. Guru 3 (dinilai SpvGuru): Sedang Direvisi (1 Final, 1 Direvisi, 2 Draft)
        $this->buatPenugasanDenganPenilaian(
            $sekolah, $periode, $guruList[2]['user'], $spvGuru, $guruList[2]['mapel'], $instrumenSekolah,
            statusPilihan: ['kbm' => 'direvisi', 'adm' => 'final', 'kelas' => 'draft', 'rencana' => 'draft'],
            jumlahBukaKunci: 1
        );

        // 4. Guru 4 (dinilai SpvGuru): Belum Dinilai (0/4 Belum)
        $this->buatPenugasanDenganPenilaian(
            $sekolah, $periode, $guruList[3]['user'], $spvGuru, $guruList[3]['mapel'], $instrumenSekolah,
            statusPilihan: ['kbm' => 'belum', 'adm' => 'belum', 'kelas' => 'belum', 'rencana' => 'belum']
        );

        // 5. Guru 5 (dinilai SpvGuru): Draft Awal (2 Draft, 2 Belum)
        $this->buatPenugasanDenganPenilaian(
            $sekolah, $periode, $guruList[4]['user'], $spvGuru, $guruList[4]['mapel'], $instrumenSekolah,
            statusPilihan: ['kbm' => 'draft', 'adm' => 'draft', 'kelas' => 'belum', 'rencana' => 'belum']
        );

        // 6. Guru 6 (Supervisor 2 dinilai Kepsek): 2 Final, 1 Draft, 1 Belum
        $this->buatPenugasanDenganPenilaian(
            $sekolah, $periode, $guruList[5]['user'], $kepsek, $guruList[5]['mapel'], $instrumenSekolah,
            statusPilihan: ['kbm' => 'final', 'adm' => 'final', 'kelas' => 'draft', 'rencana' => 'belum']
        );

        // H. Surat Tugas Supervisor
        SuratTugas::firstOrCreate(
            ['sekolah_id' => $sekolah->id, 'periode_id' => $periode->id, 'penilai_id' => $kepsek->id],
            [
                'nomor_surat' => '800/ST-01/'.$data['kode'].'/'.date('Y'),
                'tanggal_surat' => now()->subMonth()->toDateString(),
                'penandatangan_nama' => $kepsek->nama,
                'penandatangan_nip' => $kepsek->nip,
                'penandatangan_jabatan' => 'Kepala Sekolah',
                'daftar_guru' => [
                    ['nama' => $guruList[0]['user']->nama, 'nip' => $guruList[0]['user']->nip],
                    ['nama' => $guruList[1]['user']->nama, 'nip' => $guruList[1]['user']->nip],
                    ['nama' => $guruList[5]['user']->nama, 'nip' => $guruList[5]['user']->nip],
                ],
                'diterbitkan_at' => now()->subMonth(),
            ]
        );

        SuratTugas::firstOrCreate(
            ['sekolah_id' => $sekolah->id, 'periode_id' => $periode->id, 'penilai_id' => $spvGuru->id],
            [
                'nomor_surat' => '800/ST-02/'.$data['kode'].'/'.date('Y'),
                'tanggal_surat' => now()->subMonth()->toDateString(),
                'penandatangan_nama' => $kepsek->nama,
                'penandatangan_nip' => $kepsek->nip,
                'penandatangan_jabatan' => 'Kepala Sekolah',
                'daftar_guru' => [
                    ['nama' => $guruList[2]['user']->nama, 'nip' => $guruList[2]['user']->nip],
                    ['nama' => $guruList[3]['user']->nama, 'nip' => $guruList[3]['user']->nip],
                    ['nama' => $guruList[4]['user']->nama, 'nip' => $guruList[4]['user']->nip],
                ],
                'diterbitkan_at' => now()->subMonth(),
            ]
        );
    }

    /**
     * Buat instrumen lengkap sekolah dengan bagian dan butir.
     */
    private function buatInstrumenSekolah(Sekolah $sekolah): array
    {
        $definisi = [
            'kbm' => ['nama' => 'Observasi KBM / Proses Pembelajaran', 'urutan' => 1],
            'administrasi' => ['nama' => 'Kelengkapan Administrasi Guru', 'urutan' => 2],
            'pengelolaan_kelas' => ['nama' => 'Pengelolaan Kelas & Budaya Belajar', 'urutan' => 3],
            'perencanaan' => ['nama' => 'Perencanaan Pembelajaran & Modul Ajar', 'urutan' => 4],
        ];

        $hasil = [];

        foreach ($definisi as $kode => $info) {
            $jenis = JenisInstrumen::firstOrCreate(
                ['sekolah_id' => $sekolah->id, 'kode' => $kode],
                ['nama' => $info['nama'], 'urutan' => $info['urutan'], 'aktif' => true]
            );

            $versi = VersiInstrumen::firstOrCreate(
                ['jenis_instrumen_id' => $jenis->id, 'nomor_versi' => 1],
                [
                    'status' => 'terbit',
                    'skor_maks_butir' => 4,
                    'ambang_predikat' => ['A' => 86, 'B' => 76, 'C' => 56],
                ]
            );

            // Bagian
            $bagian = BagianInstrumen::firstOrCreate(
                ['versi_instrumen_id' => $versi->id, 'kode' => 'BAG-1'],
                ['judul' => 'Komponen Pokok '.$info['nama'], 'urutan' => 1]
            );

            // 2 Butir
            $butir1 = ButirInstrumen::firstOrCreate(
                ['bagian_id' => $bagian->id, 'urutan' => 1],
                ['uraian' => 'Kesesuaian pelaksanaan dengan standar mutu operasional (CONTOH - GANTI).', 'skor_maks' => 4]
            );
            $butir2 = ButirInstrumen::firstOrCreate(
                ['bagian_id' => $bagian->id, 'urutan' => 2],
                ['uraian' => 'Keaktifan dan partisipasi peserta didik selama kegiatan (CONTOH - GANTI).', 'skor_maks' => 4]
            );

            $hasil[$kode] = [
                'jenis' => $jenis,
                'versi' => $versi,
                'butir' => [$butir1, $butir2],
            ];
        }

        return $hasil;
    }

    /**
     * Buat penugasan, info supervisi, jadwal, dan 4 penilaian dengan status bervariasi.
     */
    private function buatPenugasanDenganPenilaian(
        Sekolah $sekolah,
        Periode $periode,
        User $guru,
        User $penilai,
        string $mapel,
        array $instrumenSekolah,
        array $statusPilihan,
        int $jumlahBukaKunci = 0
    ): Penugasan {
        $penugasan = Penugasan::firstOrCreate(
            ['sekolah_id' => $sekolah->id, 'periode_id' => $periode->id, 'guru_id' => $guru->id],
            ['penilai_id' => $penilai->id]
        );

        // Info Supervisi
        InfoSupervisi::firstOrCreate(
            ['sekolah_id' => $sekolah->id, 'penugasan_id' => $penugasan->id],
            [
                'kelas' => 'XII RPL 1',
                'semester' => 'Ganjil',
                'fase' => 'F',
                'mata_pelajaran' => $mapel,
                'elemen' => 'Kompetensi Inti Kejuruan',
                'cp' => 'Peserta didik menguasai materi pembelajaran secara komprehensif.',
                'catatan' => 'Observasi dilakukan sesuai kesepakatan jadwal.',
            ]
        );

        // Jadwal & 4 Penilaian
        $kodeList = ['kbm', 'administrasi', 'pengelolaan_kelas', 'perencanaan'];
        $statusKeyMap = [
            'kbm' => 'kbm',
            'administrasi' => 'adm',
            'pengelolaan_kelas' => 'kelas',
            'perencanaan' => 'rencana',
        ];

        foreach ($kodeList as $i => $kode) {
            $dataInstrumen = $instrumenSekolah[$kode];
            $statusTarget = $statusPilihan[$statusKeyMap[$kode]] ?? 'belum';

            // Jadwal Supervisi
            JadwalSupervisi::firstOrCreate(
                ['sekolah_id' => $sekolah->id, 'penugasan_id' => $penugasan->id, 'jenis_instrumen_id' => $dataInstrumen['jenis']->id],
                [
                    'tanggal' => now()->addDays($i * 2)->toDateString(),
                    'jam_mulai' => '08:00:00',
                    'jam_selesai' => '09:30:00',
                ]
            );

            // Penilaian
            $penilaian = Penilaian::firstOrCreate(
                [
                    'sekolah_id' => $sekolah->id,
                    'penugasan_id' => $penugasan->id,
                    'jenis_instrumen_id' => $dataInstrumen['jenis']->id,
                ],
                [
                    'ulid' => (string) Str::ulid(),
                    'versi_instrumen_id' => $dataInstrumen['versi']->id,
                    'status' => $statusTarget,
                    'total_skor' => $statusTarget === 'belum' ? 0 : 7,
                    'skor_maks' => 8,
                    'nilai' => $statusTarget === 'belum' ? 0 : 87.50,
                    'predikat' => $statusTarget === 'belum' ? null : 'A',
                    'catatan' => $statusTarget === 'final' ? 'Pembelajaran terlaksana dengan sangat baik.' : null,
                    'tindak_lanjut' => $statusTarget === 'final' ? 'Pertahankan inovasi media pembelajaran.' : null,
                    'finalized_at' => $statusTarget === 'final' ? now() : null,
                    'finalized_by' => $statusTarget === 'final' ? $penilai->id : null,
                    'jumlah_buka_kunci' => $statusTarget === 'direvisi' ? $jumlahBukaKunci : 0,
                ]
            );

            // Butir penilaian jika bukan 'belum'
            if ($statusTarget !== 'belum') {
                foreach ($dataInstrumen['butir'] as $butir) {
                    PenilaianButir::firstOrCreate(
                        ['penilaian_id' => $penilaian->id, 'butir_instrumen_id' => $butir->id],
                        ['skor' => 4, 'catatan' => 'Sangat baik']
                    );
                }
            }
        }

        return $penugasan;
    }
}
