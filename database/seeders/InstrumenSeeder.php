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
     * Seed 5 jenis instrumen supervisi resmi template global milik super-admin
     * berdasarkan dokumen resmi Kemendikdasmen / BBGTK Provinsi Jawa Timur
     * (Pembelajaran Mendalam dan Kerangka Asesmen Autentik).
     */
    public function run(): void
    {
        $instrumenData = [
            // 1. PRA OBSERVASI
            [
                'kode' => 'pra_observasi',
                'nama' => 'Lembar Catatan Percakapan Pra-Observasi',
                'urutan' => 1,
                'skor_maks_butir' => 0,
                'bagian' => [
                    [
                        'kode' => 'PRA_OBSERVASI',
                        'judul' => 'Percakapan dan Kesepakatan Pra-Observasi',
                        'urutan' => 1,
                        'kategori' => 'Wajib',
                        'butir' => [
                            [
                                'uraian' => "Tujuan Pembelajaran:\nApa tujuan pembelajaran yang ingin dicapai peserta didik pada pertemuan ini?",
                                'skor_maks' => 0,
                            ],
                            [
                                'uraian' => "Fokus Perilaku:\nFokus perilaku atau kompetensi apa yang ingin diobservasi secara khusus selama proses pembelajaran?",
                                'skor_maks' => 0,
                            ],
                            [
                                'uraian' => "Area Pengembangan yang Hendak Dicapai:\nArea pengembangan atau kompetensi apa yang ingin ditingkatkan oleh guru?",
                                'skor_maks' => 0,
                            ],
                            [
                                'uraian' => "Strategi yang Dipersiapkan:\nStrategi, metode, dan media pembelajaran apa yang telah dipersiapkan untuk mencapai tujuan?",
                                'skor_maks' => 0,
                            ],
                        ],
                    ],
                ],
            ],

            // 2. TELAAH PERENCANAAN PEMBELAJARAN
            [
                'kode' => 'telaah_perencanaan',
                'nama' => 'Instrumen Telaah Perencanaan Pembelajaran',
                'urutan' => 2,
                'skor_maks_butir' => 4,
                'bagian' => [
                    [
                        'kode' => 'KESELARASAN',
                        'judul' => 'Bagian I: Keselarasan',
                        'urutan' => 1,
                        'kategori' => 'Wajib',
                        'butir' => [
                            [
                                'uraian' => 'Tujuan pembelajaran, langkah pembelajaran, dan asesmen pembelajaran sudah mengarah pada pencapaian Dimensi Profil Lulusan.',
                                'skor_maks' => 4,
                            ],
                            [
                                'uraian' => 'Tujuan pembelajaran, langkah pembelajaran, dan asesmen pembelajaran sudah selaras.',
                                'skor_maks' => 4,
                            ],
                        ],
                    ],
                    [
                        'kode' => 'KERANGKA',
                        'judul' => 'Bagian II: Kerangka Pembelajaran',
                        'urutan' => 2,
                        'kategori' => 'Wajib',
                        'butir' => [
                            [
                                'uraian' => 'Praktik pedagogis yang dituliskan sudah tergambar pada langkah pembelajaran dan/atau asesmen pembelajaran.',
                                'skor_maks' => 4,
                            ],
                            [
                                'uraian' => 'Lingkungan belajar yang dituliskan sudah tergambar pada langkah pembelajaran dan/atau asesmen pembelajaran.',
                                'skor_maks' => 4,
                            ],
                            [
                                'uraian' => 'Kemitraan pembelajaran yang dituliskan sudah tergambar pada langkah pembelajaran dan/atau asesmen pembelajaran.',
                                'skor_maks' => 4,
                            ],
                            [
                                'uraian' => 'Pemanfaatan digital yang dituliskan sudah tergambar pada langkah pembelajaran dan/atau asesmen pembelajaran.',
                                'skor_maks' => 4,
                            ],
                        ],
                    ],
                    [
                        'kode' => 'LANGKAH',
                        'judul' => 'Bagian III: Langkah Pembelajaran',
                        'urutan' => 3,
                        'kategori' => 'Wajib',
                        'butir' => [
                            [
                                'uraian' => "Langkah pembelajaran dapat memfasilitasi murid untuk merasakan pengalaman belajar MEMAHAMI (terlibat aktif mengonstruksi pengetahuan agar dapat memahami secara mendalam konsep atau materi dari berbagai sumber dan konteks).\n\nIndikator amatan:\na. Menghubungkan pengetahuan baru dengan pengetahuan sebelumnya\nb. Menstimulasi proses berpikir murid\nc. Menghubungkan dengan konteks nyata dan/atau kehidupan sehari-hari\nd. Memberikan kebebasan eksploratif dan kolaboratif\ne. Menanamkan nilai-nilai moral dan etika dan nilai positif lainnya\nf. Mengaitkan pembelajaran dengan pembentukan karakter murid",
                                'skor_maks' => 4,
                            ],
                            [
                                'uraian' => "Langkah pembelajaran dapat memfasilitasi murid untuk merasakan pengalaman belajar MENGAPLIKASI (mengaplikasi pemahaman secara kontekstual dalam kehidupan nyata sebagai bagian dari pendalaman pengetahuan).\n\nIndikator amatan:\na. Menghubungkan konsep baru dengan pengetahuan sebelumnya\nb. Menerapkan pengetahuan ke dalam situasi nyata atau bidang lain\nc. Mengembangkan pemahaman dengan eksplorasi lebih lanjut\nd. Berpikir kritis dan mencari solusi inovatif berdasarkan pengetahuan yang ada",
                                'skor_maks' => 4,
                            ],
                            [
                                'uraian' => "Langkah pembelajaran dapat memfasilitasi murid untuk merasakan pengalaman belajar MEREFLEKSI (mengevaluasi dan memaknai proses serta hasil dari tindakan atau praktik nyata yang telah mereka lakukan dan menentukan tindaklanjut ke depan; serta mengelola proses belajarnya secara mandiri).\n\nIndikator amatan:\na. Memotivasi diri sendiri untuk terus belajar bagaimana cara belajar\nb. Refleksi terhadap pencapaian tujuan pembelajaran (evaluasi diri)\nc. Menerapkan strategi berpikir\nd. Memiliki kemampuan metakognisi (meregulasi diri dalam pembelajaran)\ne. Meregulasi emosi dalam pembelajaran",
                                'skor_maks' => 4,
                            ],
                            [
                                'uraian' => 'Langkah perencanaan pembelajaran dapat memfasilitasi tindakan saling MEMULIAKAN antara pendidik-Murid, Murid-pendidik, Murid-Murid yang tercermin dalam bahasa verbal dan nonverbal.',
                                'skor_maks' => 4,
                            ],
                            [
                                'uraian' => 'Prinsip pembelajaran mendalam berupa berkesadaran, bermakna, dan/atau menggembirakan sudah tergambar pada setiap pengalaman belajar di langkah pembelajaran.',
                                'skor_maks' => 4,
                            ],
                            [
                                'uraian' => 'Perencanaan pembelajaran sudah mengakomodir pengalaman belajar yang sesuai dengan karakteristik peserta didik.',
                                'skor_maks' => 4,
                            ],
                        ],
                    ],
                    [
                        'kode' => 'ASESMEN',
                        'judul' => 'Bagian IV: Asesmen',
                        'urutan' => 4,
                        'kategori' => 'Wajib',
                        'butir' => [
                            [
                                'uraian' => 'Asesmen pada awal pembelajaran telah dilaksanakan untuk mendapatkan bukti kesiapan belajar secara emosional dan mental, pengetahuan awal, dan kebutuhan belajar murid.',
                                'skor_maks' => 4,
                            ],
                            [
                                'uraian' => 'Asesmen selama Proses Pembelajaran telah dilaksanakan sesuai perencanaan untuk memantau perkembangan belajar murid, memberikan umpan balik untuk perbaikan kontinyu (baik dari pendidik ke murid, maupun dari murid ke pendidik), melalui beragam teknik.',
                                'skor_maks' => 4,
                            ],
                            [
                                'uraian' => 'Asesmen hasil Pembelajaran direncanakan untuk mengukur pencapaian kompetensi sebagai bukti keberhasilan pembelajaran dengan beragam cara, antara lain: tes, portofolio, proyek, presentasi, dsb.',
                                'skor_maks' => 4,
                            ],
                        ],
                    ],
                    [
                        'kode' => 'CATATAN_KRITIS',
                        'judul' => 'Bagian V: Catatan Telaah & Rekomendasi',
                        'urutan' => 5,
                        'kategori' => 'Kualitatif',
                        'butir' => [
                            [
                                'uraian' => 'Tuliskan kelebihan Perencanaan Pembelajaran:',
                                'skor_maks' => 0,
                            ],
                            [
                                'uraian' => 'Tuliskan hal yang perlu ditingkatkan dari Perencanaan Pembelajaran:',
                                'skor_maks' => 0,
                            ],
                            [
                                'uraian' => 'Tuliskan rekomendasi dan lanjutkan dengan revisi Perencanaan Pembelajaran sesuai prinsip PM dan KAA:',
                                'skor_maks' => 0,
                            ],
                        ],
                    ],
                ],
            ],

            // 3. OBSERVASI PELAKSANAAN PEMBELAJARAN
            [
                'kode' => 'observasi_pembelajaran',
                'nama' => 'Instrumen Observasi Pelaksanaan Pembelajaran',
                'urutan' => 3,
                'skor_maks_butir' => 4,
                'bagian' => [
                    [
                        'kode' => 'KESELARASAN',
                        'judul' => 'Bagian I: Keselarasan',
                        'urutan' => 1,
                        'kategori' => 'Wajib',
                        'butir' => [
                            [
                                'uraian' => "Apakah implementasi pembelajaran selaras dengan perencanaan?\n\nIndikator amatan:\na. Awal pembelajaran\nb. Inti pembelajaran dalam proses memahami, mengaplikasi, dan merefleksi\nc. Penutupan pembelajaran",
                                'skor_maks' => 4,
                            ],
                            [
                                'uraian' => 'Apakah upaya mencapai tujuan pembelajaran menuju pencapaian dimensi profil lulusan selaras dengan perencanaan pembelajaran dan disesuaikan dengan konteks kebutuhan belajar siswa?',
                                'skor_maks' => 4,
                            ],
                        ],
                    ],
                    [
                        'kode' => 'KERANGKA',
                        'judul' => 'Bagian II: Implementasi Kerangka Pembelajaran',
                        'urutan' => 2,
                        'kategori' => 'Wajib',
                        'butir' => [
                            [
                                'uraian' => "Praktik Pedagogis\nApakah kegiatan pembelajaran dan/atau asesmen pembelajaran menggambarkan praktik pedagogis?\n\nIndikator amatan:\nFokus pada pengalaman belajar peserta didik yang autentik, mengutamakan praktik nyata, mendorong keterampilan berpikir tingkat tinggi dan kolaborasi.",
                                'skor_maks' => 4,
                            ],
                            [
                                'uraian' => "Lingkungan Belajar\nApakah kegiatan pembelajaran dan/atau asesmen pembelajaran menggambarkan lingkungan belajar yang kondusif?\n\nIndikator amatan:\nMembentuk hubungan yang dinamis antara pendidik, peserta didik, orang tua, komunitas, dan mitra profesional. Pendekatan ini memindahkan kontrol pembelajaran dari pendidik saja menjadi kolaborasi bersama.",
                                'skor_maks' => 4,
                            ],
                            [
                                'uraian' => "Kemitraan Pembelajaran\nApakah kegiatan pembelajaran dan/atau asesmen pembelajaran menggambarkan kemitraan pembelajaran?\n\nIndikator amatan:\nMenekankan integrasi antara ruang fisik, ruang virtual, dan budaya belajar sebagai tempat yang mendorong kolaborasi, refleksi, eksplorasi, dan berbagi ide, sehingga dapat mengakomodasi berbagai gaya belajar peserta didik dengan optimal.",
                                'skor_maks' => 4,
                            ],
                            [
                                'uraian' => "Pemanfaatan Digital\nApakah kegiatan pembelajaran dan/atau asesmen pembelajaran menggambarkan pemanfaatan digital?\n\nIndikator amatan:\nMenciptakan pembelajaran dan menyediakan sumber belajar yang lebih interaktif, kolaboratif, dan kontekstual.",
                                'skor_maks' => 4,
                            ],
                        ],
                    ],
                    [
                        'kode' => 'LANGKAH',
                        'judul' => 'Bagian III: Langkah Pembelajaran',
                        'urutan' => 3,
                        'kategori' => 'Wajib',
                        'butir' => [
                            [
                                'uraian' => "Apakah langkah pembelajaran memfasilitasi tindakan saling MEMULIAKAN antara pendidik-Murid, Murid-pendidik, Murid-Murid yang tercermin dalam bahasa verbal dan nonverbal?\n\nIndikator amatan:\nMemuliakan dapat dimaknai sebagai pelayanan pembelajaran yang menghargai keberagaman, menjunjung keseimbangan gender, serta memberikan kesempatan yang sama bagi seluruh murid tanpa membedakan agama, suku, maupun jenis kelamin, termasuk murid penyandang disabilitas.",
                                'skor_maks' => 4,
                            ],
                            [
                                'uraian' => "Apakah langkah pembelajaran memfasilitasi murid untuk MEMAHAMI?\n(MEMAHAMI mendorong murid terlibat aktif mengonstruksi pengetahuan agar dapat memahami secara mendalam konsep atau materi dari berbagai sumber dan konteks)\n\nIndikator amatan:\na. Menghubungkan pengetahuan baru dengan pengetahuan sebelumnya\nb. Menstimulasi proses berpikir murid\nc. Menghubungkan dengan konteks nyata dan/atau kehidupan sehari-hari\nd. Memberikan kebebasan eksploratif dan kolaboratif\ne. Mengonstruksi pemahaman melalui pengalaman nyata\nf. Menanamkan nilai-nilai moral dan etika dan nilai positif lainnya\ng. Mengaitkan pembelajaran dengan pembentukan karakter murid",
                                'skor_maks' => 4,
                            ],
                            [
                                'uraian' => "Apakah langkah pembelajaran memfasilitasi murid untuk MENGAPLIKASI?\n(MENGAPLIKASI pemahaman secara kontekstual dalam kehidupan nyata sebagai bagian dari pendalaman pengetahuan)\n\nIndikator amatan:\na. Menghubungkan konsep baru dengan pengetahuan sebelumnya\nb. Menerapkan pengetahuan ke dalam situasi nyata atau bidang lain\nc. Melakukan praktik pemecahan masalah/isu yang kontekstual dalam kehidupan\nd. Mengembangkan pemahaman dengan eksplorasi lebih lanjut\ne. Berpikir kritis, menghubungkan ide-ide, dan mencari solusi inovatif berdasarkan pengetahuan yang ada",
                                'skor_maks' => 4,
                            ],
                            [
                                'uraian' => "Apakah langkah pembelajaran memfasilitasi murid untuk MEREFLEKSI?\n(MEREFLEKSI merupakan proses evaluasi dan memaknai proses serta hasil dari tindakan atau praktik nyata yang telah mereka lakukan dan menentukan tindaklanjut ke depan; serta mengelola proses belajarnya secara mandiri)\n\nIndikator amatan:\na. Memotivasi diri sendiri untuk terus belajar bagaimana cara belajar\nb. Refleksi terhadap pencapaian tujuan pembelajaran (evaluasi diri)\nc. Menerapkan strategi berpikir\nd. Memiliki kemampuan metakognisi (meregulasi diri dalam pembelajaran)\ne. Meregulasi emosi dalam pembelajaran",
                                'skor_maks' => 4,
                            ],
                            [
                                'uraian' => "Apakah pembelajaran menerapkan prinsip berkesadaran, bermakna, dan/atau menggembirakan?\n\nIndikator amatan:\nPrinsip tergambar pada setiap pengalaman belajar melalui langkah pembelajaran yang diimplementasikan.",
                                'skor_maks' => 4,
                            ],
                            [
                                'uraian' => "Apakah pembelajaran mengakomodir pengalaman belajar yang sesuai dengan karakteristik peserta didik?\n\nIndikator amatan:\nKarakteristik mencakup umur, tingkat perkembangan, kemampuan, bakat dan minat, gaya belajar, dll.",
                                'skor_maks' => 4,
                            ],
                        ],
                    ],
                    [
                        'kode' => 'ASESMEN',
                        'judul' => 'Bagian IV: Asesmen',
                        'urutan' => 4,
                        'kategori' => 'Wajib',
                        'butir' => [
                            [
                                'uraian' => "Apakah pendidik melaksanakan asesmen awal pembelajaran?\n\nIndikator amatan:\nAsesmen untuk mendapatkan bukti kesiapan belajar secara emosional dan mental, pengetahuan awal, dan kebutuhan belajar murid.",
                                'skor_maks' => 4,
                            ],
                            [
                                'uraian' => "Apakah pendidik melakukan asesmen selama pembelajaran melalui beragam teknik?\n\nIndikator amatan:\nAsesmen selama pembelajaran untuk memantau perkembangan belajar murid, memberikan umpan balik untuk perbaikan berkelanjutan baik dari pendidik ke murid, maupun dari murid ke pendidik.",
                                'skor_maks' => 4,
                            ],
                            [
                                'uraian' => "Apakah pendidik melakukan asesmen hasil pembelajaran untuk mengukur pencapaian kompetensi?\n\nIndikator amatan:\nAsesmen hasil pembelajaran sebagai bukti keberhasilan pembelajaran melalui beragam cara: tes, portofolio, proyek, presentasi, dsb.",
                                'skor_maks' => 4,
                            ],
                        ],
                    ],
                    [
                        'kode' => 'CATATAN_OBSERVASI',
                        'judul' => 'Bagian V: Catatan Khusus & Rekomendasi',
                        'urutan' => 5,
                        'kategori' => 'Kualitatif',
                        'butir' => [
                            [
                                'uraian' => 'Tuliskan kelebihan pelaksanaan pembelajaran:',
                                'skor_maks' => 0,
                            ],
                            [
                                'uraian' => 'Tuliskan hal yang perlu ditingkatkan dari pelaksanaan pembelajaran:',
                                'skor_maks' => 0,
                            ],
                            [
                                'uraian' => 'Tuliskan rekomendasi dan lanjutkan dengan revisi pelaksanaan pembelajaran sesuai prinsip PM dan KAA:',
                                'skor_maks' => 0,
                            ],
                        ],
                    ],
                ],
            ],

            // 4. REFLEKSI PASCA OBSERVASI
            [
                'kode' => 'refleksi_pasca_observasi',
                'nama' => 'Instrumen Refleksi Pasca Pembelajaran',
                'urutan' => 4,
                'skor_maks_butir' => 0,
                'bagian' => [
                    [
                        'kode' => 'KESELARASAN',
                        'judul' => 'Bagian I: Keselarasan',
                        'urutan' => 1,
                        'kategori' => 'Wajib',
                        'butir' => [
                            [
                                'uraian' => "Tindakan implementasi perencanaan selaras dengan perencanaan pembelajaran pada:\n\nIndikator amatan:\na. Awal pembelajaran\nb. Inti pembelajaran dalam proses memahami, mengaplikasi, dan merefleksi\nc. Penutupan pembelajaran",
                                'skor_maks' => 0,
                            ],
                            [
                                'uraian' => 'Upaya mencapai tujuan pembelajaran menuju pencapaian dimensi profil lulusan selaras dengan perencanaan pembelajaran.',
                                'skor_maks' => 0,
                            ],
                        ],
                    ],
                    [
                        'kode' => 'KERANGKA',
                        'judul' => 'Bagian II: Implementasi Kerangka Pembelajaran',
                        'urutan' => 2,
                        'kategori' => 'Wajib',
                        'butir' => [
                            [
                                'uraian' => 'Praktik pedagogis yang diimplementasikan sudah tergambar pada langkah pembelajaran dan/atau asesmen pembelajaran.',
                                'skor_maks' => 0,
                            ],
                            [
                                'uraian' => 'Lingkungan belajar yang diimplementasikan sudah tergambar pada langkah pembelajaran dan/atau asesmen pembelajaran.',
                                'skor_maks' => 0,
                            ],
                            [
                                'uraian' => 'Kemitraan pembelajaran yang diimplementasikan sudah tergambar pada langkah pembelajaran dan/atau asesmen pembelajaran.',
                                'skor_maks' => 0,
                            ],
                            [
                                'uraian' => 'Pemanfaatan digital yang diimplementasikan sudah tergambar pada langkah pembelajaran dan/atau asesmen pembelajaran.',
                                'skor_maks' => 0,
                            ],
                        ],
                    ],
                    [
                        'kode' => 'LANGKAH',
                        'judul' => 'Bagian III: Langkah Pembelajaran',
                        'urutan' => 3,
                        'kategori' => 'Wajib',
                        'butir' => [
                            [
                                'uraian' => 'Langkah pembelajaran yang dilakukan sesuai perencanaan dapat memfasilitasi tindakan saling MEMULIAKAN antara Guru-Murid, Murid-Guru, Murid-Murid yang tercermin dalam bahasa verbal dan nonverbal.',
                                'skor_maks' => 0,
                            ],
                            [
                                'uraian' => 'Langkah pembelajaran sudah memfasilitasi murid untuk merasakan pengalaman belajar MEMAHAMI (terlibat aktif mengonstruksi pengetahuan agar dapat memahami secara mendalam konsep atau materi dari berbagai sumber dan konteks).',
                                'skor_maks' => 0,
                            ],
                            [
                                'uraian' => 'Langkah pembelajaran sudah memfasilitasi murid untuk merasakan pengalaman belajar MENGAPLIKASI (mengaplikasi pemahaman secara kontekstual dalam kehidupan nyata sebagai bagian dari pendalaman pengetahuan).',
                                'skor_maks' => 0,
                            ],
                            [
                                'uraian' => 'Langkah pembelajaran sudah memfasilitasi murid untuk merasakan pengalaman belajar MEREFLEKSI (mengevaluasi dan memaknai proses serta hasil dari tindakan atau praktik nyata yang telah mereka lakukan dan menentukan tindaklanjut ke depan; serta mengelola proses belajarnya secara mandiri).',
                                'skor_maks' => 0,
                            ],
                            [
                                'uraian' => 'Prinsip pembelajaran mendalam berupa berkesadaran, bermakna, dan/atau menggembirakan sudah tergambar pada setiap pengalaman belajar di langkah pembelajaran yang diimplementasikan.',
                                'skor_maks' => 0,
                            ],
                            [
                                'uraian' => 'Praktik pembelajaran sudah mengakomodir pengalaman belajar yang sesuai dengan karakteristik murid (umur, tingkat perkembangan, kemampuan, bakat dan minat, gaya belajar, dll.).',
                                'skor_maks' => 0,
                            ],
                        ],
                    ],
                    [
                        'kode' => 'ASESMEN',
                        'judul' => 'Bagian IV: Asesmen',
                        'urutan' => 4,
                        'kategori' => 'Wajib',
                        'butir' => [
                            [
                                'uraian' => 'Praktik Pembelajaran sudah melakukan asesmen untuk memberikan umpan balik guna memperbaiki proses pembelajaran.',
                                'skor_maks' => 0,
                            ],
                            [
                                'uraian' => 'Praktik Pembelajaran sudah melakukan asesmen untuk mengukur ketercapaian tujuan pembelajaran sesuai karakteristik peserta didik.',
                                'skor_maks' => 0,
                            ],
                            [
                                'uraian' => 'Asesmen sudah dilakukan berdasarkan kriteria yang jelas dalam mengukur ketercapaian tujuan pembelajaran.',
                                'skor_maks' => 0,
                            ],
                        ],
                    ],
                    [
                        'kode' => 'REFLEKSI_DIALOGIS',
                        'judul' => 'Bagian V: Refleksi & Umpan Balik',
                        'urutan' => 5,
                        'kategori' => 'Kualitatif',
                        'butir' => [
                            [
                                'uraian' => 'Pelajaran apa yang telah diperoleh dari Implementasi Perencanaan Pembelajaran yang telah dilakukan beserta faktor-faktor pendukungnya?',
                                'skor_maks' => 0,
                            ],
                            [
                                'uraian' => 'Hal-hal apa saja yang pencapaiannya belum memuaskan dari Implementasi Perencanaan Pembelajaran yang telah dilakukan beserta faktor-faktor penghambatnya?',
                                'skor_maks' => 0,
                            ],
                            [
                                'uraian' => 'Rencana tindak lanjut apa yang akan dibuat untuk perbaikan ke depan?',
                                'skor_maks' => 0,
                            ],
                            [
                                'uraian' => 'Catatan Umpan Balik untuk Guru Model (Komentar dan saran konstruktif yang dapat membantu guru model meningkatkan praktik pembelajarannya):',
                                'skor_maks' => 0,
                            ],
                            [
                                'uraian' => 'Refleksi Pribadi (Satu hal penting yang Anda pelajari dari kegiatan ini dan bagaimana Anda akan menerapkannya dalam praktik pembelajaran Anda sendiri):',
                                'skor_maks' => 0,
                            ],
                        ],
                    ],
                ],
            ],

            // 5. RENCANA PENGEMBANGAN DIRI
            [
                'kode' => 'rencana_pengembangan_diri',
                'nama' => 'Lembar Rencana Pengembangan Diri Guru',
                'urutan' => 5,
                'skor_maks_butir' => 0,
                'bagian' => [
                    [
                        'kode' => 'RENCANA_AKSI',
                        'judul' => 'Komitmen Rencana Pengembangan Keprofesian Guru',
                        'urutan' => 1,
                        'kategori' => 'Wajib',
                        'butir' => [
                            [
                                'uraian' => 'Aktivitas Pengembangan: Rencana kegiatan pengembangan diri (mis. Pelatihan Mandiri PMM, Kombel, Micro-learning, Peer-teaching).',
                                'skor_maks' => 0,
                            ],
                            [
                                'uraian' => 'Tujuan yang Hendak Dicapai: Peningkatan kompetensi spesifik yang diharapkan.',
                                'skor_maks' => 0,
                            ],
                            [
                                'uraian' => 'Ukuran/Indikator Keberhasilan: Bukti konkret ketercapaian target pengembangan diri.',
                                'skor_maks' => 0,
                            ],
                            [
                                'uraian' => 'Dukungan & Sumber Daya: Sumber daya yang diperlukan (rekan sejawat, kepala sekolah, sarana, dsb.).',
                                'skor_maks' => 0,
                            ],
                            [
                                'uraian' => 'Target Waktu Pelaksanaan: Jadwal dan batas waktu penyelesaian rencana aksi.',
                                'skor_maks' => 0,
                            ],
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
            } else {
                $jenis->update([
                    'nama' => $d['nama'],
                    'urutan' => $d['urutan'],
                    'aktif' => true,
                ]);
            }

            // Dapatkan atau buat versi 1
            $versi = VersiInstrumen::where('jenis_instrumen_id', $jenis->id)
                ->where('nomor_versi', 1)
                ->first();

            if (! $versi) {
                $versi = VersiInstrumen::create([
                    'jenis_instrumen_id' => $jenis->id,
                    'nomor_versi' => 1,
                    'status' => 'terbit',
                    'skor_maks_butir' => $d['skor_maks_butir'],
                    'ambang_predikat' => [
                        'A' => 86,
                        'B' => 76,
                        'C' => 56,
                    ],
                ]);
            } else {
                $versi->update([
                    'status' => 'terbit',
                    'skor_maks_butir' => $d['skor_maks_butir'],
                    'ambang_predikat' => [
                        'A' => 86,
                        'B' => 76,
                        'C' => 56,
                    ],
                ]);
            }

            // Hapus bagian dan butir lama pada versi ini agar terbarukan dengan indikator lengkap
            $bagianLamaIds = BagianInstrumen::where('versi_instrumen_id', $versi->id)->pluck('id');
            ButirInstrumen::whereIn('bagian_id', $bagianLamaIds)->delete();
            BagianInstrumen::where('versi_instrumen_id', $versi->id)->delete();

            // Masukkan bagian dan butir baru lengkap dengan indikator amatan
            foreach ($d['bagian'] as $b) {
                $bagian = BagianInstrumen::create([
                    'versi_instrumen_id' => $versi->id,
                    'kode' => $b['kode'],
                    'judul' => $b['judul'],
                    'urutan' => $b['urutan'],
                    'kategori' => $b['kategori'],
                ]);

                foreach ($b['butir'] as $idx => $bData) {
                    ButirInstrumen::create([
                        'bagian_id' => $bagian->id,
                        'urutan' => $idx + 1,
                        'uraian' => $bData['uraian'],
                        'skor_maks' => $bData['skor_maks'],
                    ]);
                }
            }
        }
    }
}
