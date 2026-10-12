<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Rekapitulasi Instrumen Kualitatif - {{ $sekolah->nama }} - {{ $periode->nama }}</title>
    <style>
        @page {
            size: A4 landscape;
            margin: 10mm 12mm 12mm 12mm;
        }

        body {
            font-family: DejaVu Sans, Helvetica, Arial, sans-serif;
            font-size: 8pt;
            line-height: 1.35;
            color: #1F2937;
        }

        .page-break {
            page-break-before: always;
        }

        /* Kop Surat Sekolah */
        .kop-table {
            width: 100%;
            border-bottom: 2px solid #253C6D;
            padding-bottom: 4px;
            margin-bottom: 8px;
        }

        .kop-text {
            text-align: center;
        }

        .kop-sekolah {
            font-size: 13pt;
            font-weight: bold;
            color: #253C6D;
            text-transform: uppercase;
        }

        .kop-alamat {
            font-size: 7.5pt;
            color: #4B5563;
        }

        /* Judul */
        .doc-header {
            text-align: center;
            margin-bottom: 10px;
        }

        .doc-title {
            font-size: 11pt;
            font-weight: bold;
            color: #253C6D;
            text-transform: uppercase;
            margin: 0;
        }

        .doc-subtitle {
            font-size: 8.5pt;
            color: #4B5563;
            margin-top: 2px;
        }

        /* Tabel Data */
        .rekap-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 12px;
            font-size: 7.5pt;
        }

        .rekap-table th,
        .rekap-table td {
            border: 1px solid #9CA3AF;
            padding: 3.5px 5px;
            vertical-align: top;
        }

        .rekap-table th {
            background-color: #E6ECF7;
            color: #253C6D;
            font-weight: bold;
            text-align: center;
        }

        .sub-header-row {
            background-color: #F3F4F6;
            font-weight: bold;
            color: #1F2937;
            font-size: 8pt;
        }

        .text-center {
            text-align: center;
        }

        .text-right {
            text-align: right;
        }

        .font-bold {
            font-weight: bold;
        }

        /* Tanda Tangan */
        .ttd-table {
            width: 100%;
            margin-top: 15px;
            page-break-inside: avoid;
            font-size: 8pt;
        }

        .ttd-table td {
            width: 50%;
            text-align: center;
            vertical-align: top;
        }

        .ttd-space {
            height: 45px;
        }

        .ttd-nama {
            font-weight: bold;
            text-decoration: underline;
        }
    </style>
</head>
<body>

    {{-- KOP SEKOLAH --}}
    <table class="kop-table">
        <tr>
            <td class="kop-text">
                <div style="font-size: 9.5pt; text-transform: uppercase;">PEMERINTAH PROVINSI / DAERAH</div>
                <div class="kop-sekolah">{{ $sekolah->nama }}</div>
                <div class="kop-alamat">
                    {{ $sekolah->alamat ?: 'Alamat Sekolah Belum Diisi' }}
                    @if($sekolah->npsn) &bull; NPSN: {{ $sekolah->npsn }} @endif
                </div>
            </td>
        </tr>
    </table>

    {{-- JUDUL REKAP --}}
    <div class="doc-header">
        <h1 class="doc-title">REKAPITULASI HASIL SUPERVISI INSTRUMEN KUALITATIF</h1>
        <div class="doc-subtitle">
            Periode: <strong>{{ $periode->nama }} ({{ $periode->tahun_ajaran }})</strong>
            @if($supervisor)
                &bull; Supervisor: <strong>{{ $supervisor->nama }}</strong>
            @else
                &bull; Supervisor: <strong>Semua Supervisor</strong>
            @endif
            &bull; Kategori: <strong>{{ $judulKategori }}</strong>
        </div>
    </div>

    {{-- 1. BAGIAN PRA-OBSERVASI --}}
    @if($kategori === 'pra_observasi' || $kategori === 'semua' || empty($kategori))
        <div style="font-weight: bold; font-size: 9pt; color: #253C6D; margin-bottom: 4px;">
            I. Rekap Lembar Percakapan Pra-Observasi Kelas
        </div>

        <table class="rekap-table">
            <thead>
                <tr>
                    <th style="width: 3%;">No</th>
                    <th style="width: 14%;">Guru & Mapel</th>
                    <th style="width: 7%;">Status</th>
                    <th style="width: 18%;">Tujuan Pembelajaran</th>
                    <th style="width: 17%;">Fokus Perilaku</th>
                    <th style="width: 17%;">Area Pengembangan</th>
                    <th style="width: 17%;">Strategi yang Dipersiapkan</th>
                    <th style="width: 7%;">Catatan Supervisor</th>
                </tr>
            </thead>
            <tbody>
                @forelse($daftarPenugasan as $idx => $p)
                    @php
                        $pen = $p->penilaian->firstWhere('jenisInstrumen.kode', 'pra_observasi');
                        $mapJwb = $pen?->penilaianButir->keyBy('butir_instrumen_id') ?? collect();
                        $isiTujuan = ''; $isiFokus = ''; $isiArea = ''; $isiStrategi = '';

                        if ($pen && $pen->versiInstrumen) {
                            $butirList = $pen->versiInstrumen->bagian->flatMap->butir;
                            foreach ($butirList as $bIdx => $b) {
                                $c = $mapJwb->get($b->id)?->catatan ?? '';
                                $teks = strtolower($b->uraian);
                                if (str_contains($teks, 'tujuan') || $bIdx === 0) { if ($isiTujuan === '') $isiTujuan = $c; }
                                if (str_contains($teks, 'fokus') || $bIdx === 1) { if ($isiFokus === '') $isiFokus = $c; }
                                if (str_contains($teks, 'area') || str_contains($teks, 'kompetensi') || $bIdx === 2) { if ($isiArea === '') $isiArea = $c; }
                                if (str_contains($teks, 'strategi') || str_contains($teks, 'metode') || $bIdx === 3) { if ($isiStrategi === '') $isiStrategi = $c; }
                            }
                        }
                    @endphp
                    <tr>
                        <td class="text-center font-bold">{{ $idx + 1 }}</td>
                        <td>
                            <strong>{{ $p->guru->nama }}</strong><br>
                            <span style="color: #4B5563;">NIP: {{ $p->guru->nip ?: '-' }}</span><br>
                            <span style="color: #253C6D;">{{ $p->infoSupervisi?->mata_pelajaran ?: '-' }} ({{ $p->infoSupervisi?->kelas ?: '-' }})</span>
                        </td>
                        <td class="text-center">
                            @if($pen?->isFinal())
                                <strong style="color: #1B5E20;">Final</strong>
                            @elseif($pen?->isDraft())
                                <span style="color: #8A5A00;">Draft</span>
                            @else
                                <span style="color: #6B7280;">Belum</span>
                            @endif
                        </td>
                        <td>{{ $isiTujuan ?: ($p->infoSupervisi?->cp ?: '-') }}</td>
                        <td>{{ $isiFokus ?: '-' }}</td>
                        <td>{{ $isiArea ?: '-' }}</td>
                        <td>{{ $isiStrategi ?: '-' }}</td>
                        <td>{{ $pen?->catatan ?: '-' }}</td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="8" class="text-center" style="padding: 10px;">Tidak ada data guru yang disupervisi.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    @endif

    {{-- 2. BAGIAN REFLEKSI PASCA OBSERVASI --}}
    @if($kategori === 'refleksi_pasca_observasi' || $kategori === 'semua' || empty($kategori))
        @if($kategori === 'semua')
            <div class="page-break"></div>
        @endif

        <div style="font-weight: bold; font-size: 9pt; color: #253C6D; margin-bottom: 4px; margin-top: 10px;">
            {{ $kategori === 'semua' ? 'II.' : 'I.' }} Rekap Instrumen Refleksi Pasca Pembelajaran
        </div>

        <table class="rekap-table">
            <thead>
                <tr>
                    <th style="width: 3%;">No</th>
                    <th style="width: 14%;">Guru & Mapel</th>
                    <th style="width: 7%;">Status</th>
                    <th style="width: 16%;">Pelajaran Diperoleh</th>
                    <th style="width: 16%;">Hal Belum Memuaskan</th>
                    <th style="width: 15%;">Rencana Tindak Lanjut</th>
                    <th style="width: 15%;">Umpan Balik Guru Model</th>
                    <th style="width: 14%;">Refleksi Pribadi</th>
                </tr>
            </thead>
            <tbody>
                @forelse($daftarPenugasan as $idx => $p)
                    @php
                        $pen = $p->penilaian->firstWhere('jenisInstrumen.kode', 'refleksi_pasca_observasi');
                        $mapJwb = $pen?->penilaianButir->keyBy('butir_instrumen_id') ?? collect();
                        $pelajaranDiperoleh = ''; $belumMemuaskan = ''; $rencanaLanjut = ''; $umpanBalik = ''; $refleksiPribadi = '';

                        if ($pen && $pen->versiInstrumen) {
                            $butirList = $pen->versiInstrumen->bagian->flatMap->butir;
                            foreach ($butirList as $b) {
                                $c = $mapJwb->get($b->id)?->catatan ?? '';
                                $teks = strtolower($b->uraian);
                                if (str_contains($teks, 'pelajaran apa yang telah diperoleh')) { $pelajaranDiperoleh = $c; }
                                elseif (str_contains($teks, 'belum memuaskan')) { $belumMemuaskan = $c; }
                                elseif (str_contains($teks, 'rencana tindak lanjut')) { $rencanaLanjut = $c; }
                                elseif (str_contains($teks, 'umpan balik untuk guru model')) { $umpanBalik = $c; }
                                elseif (str_contains($teks, 'refleksi pribadi')) { $refleksiPribadi = $c; }
                            }
                        }
                    @endphp
                    <tr>
                        <td class="text-center font-bold">{{ $idx + 1 }}</td>
                        <td>
                            <strong>{{ $p->guru->nama }}</strong><br>
                            <span style="color: #4B5563;">NIP: {{ $p->guru->nip ?: '-' }}</span><br>
                            <span style="color: #253C6D;">{{ $p->infoSupervisi?->mata_pelajaran ?: '-' }} ({{ $p->infoSupervisi?->kelas ?: '-' }})</span>
                        </td>
                        <td class="text-center">
                            @if($pen?->isFinal())
                                <strong style="color: #1B5E20;">Final</strong>
                            @elseif($pen?->isDraft())
                                <span style="color: #8A5A00;">Draft</span>
                            @else
                                <span style="color: #6B7280;">Belum</span>
                            @endif
                        </td>
                        <td>{{ $pelajaranDiperoleh ?: '-' }}</td>
                        <td>{{ $belumMemuaskan ?: '-' }}</td>
                        <td>{{ $rencanaLanjut ?: ($pen?->tindak_lanjut ?: '-') }}</td>
                        <td>{{ $umpanBalik ?: '-' }}</td>
                        <td>{{ $refleksiPribadi ?: '-' }}</td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="8" class="text-center" style="padding: 10px;">Tidak ada data guru yang disupervisi.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    @endif

    {{-- 3. BAGIAN RENCANA PENGEMBANGAN DIRI --}}
    @if($kategori === 'rencana_pengembangan_diri' || $kategori === 'semua' || empty($kategori))
        @if($kategori === 'semua')
            <div class="page-break"></div>
        @endif

        <div style="font-weight: bold; font-size: 9pt; color: #253C6D; margin-bottom: 4px; margin-top: 10px;">
            {{ $kategori === 'semua' ? 'III.' : 'I.' }} Rekap Lembar Rencana Pengembangan Diri Guru
        </div>

        <table class="rekap-table">
            <thead>
                <tr>
                    <th style="width: 3%;">No</th>
                    <th style="width: 14%;">Guru & Mapel</th>
                    <th style="width: 6%;">Status</th>
                    <th style="width: 22%;">Aktivitas Pengembangan</th>
                    <th style="width: 20%;">Tujuan yang Dicapai</th>
                    <th style="width: 16%;">Ukuran Keberhasilan</th>
                    <th style="width: 11%;">Dukungan Dibutuhkan</th>
                    <th style="width: 8%;">Waktu & Status</th>
                </tr>
            </thead>
            <tbody>
                @php $nomorRpd = 1; @endphp
                @forelse($daftarPenugasan as $p)
                    @php
                        $pen = $p->penilaian->firstWhere('jenisInstrumen.kode', 'rencana_pengembangan_diri');
                        $rencanaItems = $pen?->data_kualitatif['rencana'] ?? [];
                    @endphp

                    @if(!empty($rencanaItems) && is_array($rencanaItems))
                        @foreach($rencanaItems as $itemIdx => $r)
                            <tr>
                                @if($itemIdx === 0)
                                    <td class="text-center font-bold" rowspan="{{ count($rencanaItems) }}">{{ $nomorRpd++ }}</td>
                                    <td rowspan="{{ count($rencanaItems) }}">
                                        <strong>{{ $p->guru->nama }}</strong><br>
                                        <span style="color: #4B5563;">NIP: {{ $p->guru->nip ?: '-' }}</span><br>
                                        <span style="color: #253C6D;">{{ $p->infoSupervisi?->mata_pelajaran ?: '-' }} ({{ $p->infoSupervisi?->kelas ?: '-' }})</span>
                                    </td>
                                    <td class="text-center" rowspan="{{ count($rencanaItems) }}">
                                        @if($pen?->isFinal())
                                            <strong style="color: #1B5E20;">Final</strong>
                                        @elseif($pen?->isDraft())
                                            <span style="color: #8A5A00;">Draft</span>
                                        @else
                                            <span style="color: #6B7280;">Belum</span>
                                        @endif
                                    </td>
                                @endif
                                <td style="font-weight: 600;">{{ $r['aktivitas'] ?: '-' }}</td>
                                <td>{{ $r['tujuan'] ?: '-' }}</td>
                                <td>{{ $r['ukuran'] ?: '-' }}</td>
                                <td>{{ $r['dukungan'] ?: '-' }}</td>
                                <td class="text-center">
                                    {{ $r['waktu'] ?: '-' }}<br>
                                    <span style="font-size: 7pt; color: #4B5563;">({{ $r['status'] ?? 'Rencana' }})</span>
                                </td>
                            </tr>
                        @endforeach
                    @else
                        <tr>
                            <td class="text-center font-bold">{{ $nomorRpd++ }}</td>
                            <td>
                                <strong>{{ $p->guru->nama }}</strong><br>
                                <span style="color: #4B5563;">NIP: {{ $p->guru->nip ?: '-' }}</span><br>
                                <span style="color: #253C6D;">{{ $p->infoSupervisi?->mata_pelajaran ?: '-' }}</span>
                            </td>
                            <td class="text-center">
                                <span style="color: #6B7280;">Belum diisi</span>
                            </td>
                            <td colspan="5" style="color: #9CA3AF; font-style: italic;">Belum ada butir rencana pengembangan keprofesian yang didaftarkan.</td>
                        </tr>
                    @endif
                @empty
                    <tr>
                        <td colspan="8" class="text-center" style="padding: 10px;">Tidak ada data guru yang disupervisi.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    @endif

    {{-- TANDA TANGAN --}}
    <table class="ttd-table">
        <tr>
            <td>
                Mengetahui,<br>
                Kepala Sekolah
                <div class="ttd-space"></div>
                <div class="ttd-nama">{{ $sekolah->kepalaSekolah ? $sekolah->kepalaSekolah->nama : '...................................................' }}</div>
                <div>NIP: {{ $sekolah->kepalaSekolah?->nip ?: '..........................................' }}</div>
            </td>
            <td>
                {{ $sekolah->alamat ? explode(',', $sekolah->alamat)[0] : 'Di Tempat' }}, {{ now()->translatedFormat('d F Y') }}<br>
                {{ $supervisor ? 'Supervisor Penilai' : 'Koordinator Supervisi' }}
                <div class="ttd-space"></div>
                <div class="ttd-nama">{{ $supervisor ? $supervisor->nama : auth()->user()->nama }}</div>
                <div>NIP: {{ $supervisor ? ($supervisor->nip ?: '-') : (auth()->user()->nip ?: '-') }}</div>
            </td>
        </tr>
    </table>

</body>
</html>
