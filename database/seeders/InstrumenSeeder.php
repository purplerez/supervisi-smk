<?php

namespace Database\Seeders;

use App\Models\BagianInstrumen;
use App\Models\ButirInstrumen;
use App\Models\JenisInstrumen;
use App\Models\VersiInstrumen;
use Illuminate\Database\Seeder;

class InstrumenSeeder extends Seeder
{
    /**
     * Seed 4 jenis instrumen template global milik super-admin.
     */
    public function run(): void
    {
        $instrumenData = [
            [
                'kode' => 'kbm',
                'nama' => 'Observasi KBM / Proses Pembelajaran',
                'urutan' => 1,
                'bagian' => [
                    [
                        'kode' => 'PENDAHULUAN',
                        'judul' => 'Kegiatan Pendahuluan Pembelajaran',
                        'urutan' => 1,
                        'kategori' => 'Wajib',
                        'butir' => [
                            'CONTOH - GANTI: Guru menyiapkan peserta didik secara psikis dan fisik untuk mengikuti pembelajaran.',
                            'CONTOH - GANTI: Guru memberikan apersepsi, motivasi, dan menyampaikan tujuan pembelajaran.',
                        ],
                    ],
                    [
                        'kode' => 'INTI',
                        'judul' => 'Kegiatan Inti Pembelajaran',
                        'urutan' => 2,
                        'kategori' => 'Wajib',
                        'butir' => [
                            'CONTOH - GANTI: Guru menerapkan model dan metode pembelajaran yang berpusat pada peserta didik.',
                            'CONTOH - GANTI: Guru memfasilitasi interaksi belajar yang aktif, kritis, dan reflektif.',
                        ],
                    ],
                ],
            ],
            [
                'kode' => 'administrasi',
                'nama' => 'Kelengkapan Administrasi Guru',
                'urutan' => 2,
                'bagian' => [
                    [
                        'kode' => 'PERENCANAAN',
                        'judul' => 'Dokumen Perencanaan Pembelajaran',
                        'urutan' => 1,
                        'kategori' => 'Wajib',
                        'butir' => [
                            'CONTOH - GANTI: Ketersediaan Modul Ajar / RPP yang telah divalidasi dan selaras kurikulum.',
                            'CONTOH - GANTI: Ketersediaan Program Tahunan (Prota) dan Program Semester (Promes).',
                        ],
                    ],
                    [
                        'kode' => 'ASESMEN',
                        'judul' => 'Dokumen Asesmen dan Evaluasi Hasil Belajar',
                        'urutan' => 2,
                        'kategori' => 'Wajib',
                        'butir' => [
                            'CONTOH - GANTI: Ketersediaan instrumen asesmen diagnostik, formatif, dan sumatif.',
                            'CONTOH - GANTI: Ketersediaan buku rekapitulasi nilai dan catatan tindak lanjut hasil belajar.',
                        ],
                    ],
                ],
            ],
            [
                'kode' => 'pengelolaan_kelas',
                'nama' => 'Pengelolaan Kelas & Lingkungan Belajar',
                'urutan' => 3,
                'bagian' => [
                    [
                        'kode' => 'TATA_RUANG',
                        'judul' => 'Tata Ruang dan Kebersihan Kelas',
                        'urutan' => 1,
                        'kategori' => 'Penunjang',
                        'butir' => [
                            'CONTOH - GANTI: Pengaturan tempat duduk siswa fleksibel dan mendukung interaksi aktif.',
                            'CONTOH - GANTI: Kebersihan, ventilasi, dan pencahayaan ruang kelas terjaga dengan baik.',
                        ],
                    ],
                    [
                        'kode' => 'DISIPLIN',
                        'judul' => 'Iklim dan Kesepakatan Kelas',
                        'urutan' => 2,
                        'kategori' => 'Wajib',
                        'butir' => [
                            'CONTOH - GANTI: Adanya kesepakatan/keyakinan kelas yang disepakati dan dipatuhi bersama.',
                            'CONTOH - GANTI: Penerapan disiplin positif tanpa kekerasan atau hukuman fisik/verbal.',
                        ],
                    ],
                ],
            ],
            [
                'kode' => 'perencanaan',
                'nama' => 'Perencanaan Pembelajaran / Modul Ajar',
                'urutan' => 4,
                'bagian' => [
                    [
                        'kode' => 'CP_TP',
                        'judul' => 'Capaian dan Alur Tujuan Pembelajaran',
                        'urutan' => 1,
                        'kategori' => 'Wajib',
                        'butir' => [
                            'CONTOH - GANTI: Perumusan Tujuan Pembelajaran (TP) terukur dan selaras dengan Capaian Pembelajaran (CP).',
                            'CONTOH - GANTI: Alokasi waktu dan urutan materi disusun logis dan berkesinambungan.',
                        ],
                    ],
                    [
                        'kode' => 'DIFERENSIASI',
                        'judul' => 'Rancangan Asesmen dan Diferensiasi',
                        'urutan' => 2,
                        'kategori' => 'Wajib',
                        'butir' => [
                            'CONTOH - GANTI: Rancangan aktivitas mengakomodasi keberagaman kesiapan belajar peserta didik.',
                            'CONTOH - GANTI: Bentuk asesmen autentik dan instrumen penilaian dirancang secara jelas.',
                        ],
                    ],
                ],
            ],
        ];

        foreach ($instrumenData as $d) {
            // Cek apakah jenis instrumen global sudah ada
            $jenis = JenisInstrumen::whereNull('sekolah_id')
                ->where('kode', $d['kode'])
                ->first();

            if (! $jenis) {
                $jenis = JenisInstrumen::create([
                    'sekolah_id' => null, // Template Global
                    'kode' => $d['kode'],
                    'nama' => $d['nama'],
                    'urutan' => $d['urutan'],
                    'aktif' => true,
                ]);
            }

            // Cek apakah versi 1 sudah ada
            $versi = VersiInstrumen::where('jenis_instrumen_id', $jenis->id)
                ->where('nomor_versi', 1)
                ->first();

            if (! $versi) {
                $versi = VersiInstrumen::create([
                    'jenis_instrumen_id' => $jenis->id,
                    'nomor_versi' => 1,
                    'status' => 'terbit',
                    'skor_maks_butir' => 4,
                    'ambang_predikat' => [
                        'A' => 86,
                        'B' => 76,
                        'C' => 56,
                    ],
                ]);

                foreach ($d['bagian'] as $b) {
                    $bagian = BagianInstrumen::create([
                        'versi_instrumen_id' => $versi->id,
                        'kode' => $b['kode'],
                        'judul' => $b['judul'],
                        'urutan' => $b['urutan'],
                        'kategori' => $b['kategori'],
                    ]);

                    foreach ($b['butir'] as $idx => $uraian) {
                        ButirInstrumen::create([
                            'bagian_id' => $bagian->id,
                            'urutan' => $idx + 1,
                            'uraian' => $uraian,
                            'skor_maks' => 4,
                        ]);
                    }
                }
            }
        }
    }
}
