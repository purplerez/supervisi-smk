<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Hasil Penilaian Supervisi - {{ $penilaian->penugasan->guru->nama }} - {{ $penilaian->jenisInstrumen->nama }}</title>
    <style>
        @page {
            size: A4 portrait;
            margin: 15mm 15mm 15mm 15mm;
        }

        body {
            font-family: DejaVu Sans, Helvetica, Arial, sans-serif;
            font-size: 10pt;
            line-height: 1.4;
            color: #1F2937;
        }

        /* Kop Surat Sekolah */
        .kop-table {
            width: 100%;
            border-bottom: 2.5px solid #253C6D;
            padding-bottom: 8px;
            margin-bottom: 15px;
        }

        .kop-logo {
            width: 65px;
            vertical-align: middle;
            text-align: center;
        }

        .kop-text {
            text-align: center;
            vertical-align: middle;
        }

        .kop-instansi {
            font-size: 11pt;
            font-weight: normal;
            letter-spacing: 0.5px;
            text-transform: uppercase;
        }

        .kop-sekolah {
            font-size: 14pt;
            font-weight: bold;
            color: #253C6D;
            margin: 2px 0;
            text-transform: uppercase;
        }

        .kop-alamat {
            font-size: 8.5pt;
            color: #4B5563;
        }

        /* Judul Dokumen */
        .doc-header {
            text-align: center;
            margin-bottom: 14px;
        }

        .doc-title {
            font-size: 12pt;
            font-weight: bold;
            color: #253C6D;
            text-transform: uppercase;
            margin: 0;
        }

        .doc-subtitle {
            font-size: 10.5pt;
            font-weight: bold;
            color: #F2842F;
            margin-top: 3px;
            text-transform: uppercase;
        }

        /* Informasi Metadata / Identitas */
        .info-table {
            width: 100%;
            margin-bottom: 12px;
            border-collapse: collapse;
            font-size: 9pt;
        }

        .info-table td {
            padding: 3px 4px;
            vertical-align: top;
        }

        .info-label {
            width: 22%;
            font-weight: bold;
            color: #374151;
        }

        .info-separator {
            width: 2%;
            text-align: center;
        }

        .info-value {
            width: 26%;
            color: #111827;
        }

        /* Tabel Penilaian */
        .content-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 12px;
            font-size: 8.5pt;
        }

        .content-table th,
        .content-table td {
            border: 1px solid #D1D5DB;
            padding: 5px 6px;
        }

        .content-table th {
            background-color: #E6ECF7;
            color: #253C6D;
            font-weight: bold;
            text-align: center;
        }

        .bagian-header {
            background-color: #F3F4F6;
            font-weight: bold;
            color: #1F2937;
        }

        .text-center {
            text-align: center;
        }

        .text-right {
            text-align: right;
        }

        /* Rekapitulasi Skor Box */
        .rekap-box {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 12px;
            font-size: 9pt;
        }

        .rekap-box th, .rekap-box td {
            border: 1px solid #253C6D;
            padding: 6px 8px;
        }

        .rekap-box th {
            background-color: #253C6D;
            color: #FFFFFF;
            text-align: center;
            font-weight: bold;
        }

        .rekap-highlight {
            font-size: 11pt;
            font-weight: bold;
            color: #253C6D;
            text-align: center;
        }

        /* Bagian Catatan & Tindak Lanjut */
        .notes-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 14px;
            font-size: 9pt;
        }

        .notes-table th {
            background-color: #E6ECF7;
            color: #253C6D;
            text-align: left;
            padding: 5px 8px;
            border: 1px solid #D1D5DB;
            font-weight: bold;
        }

        .notes-table td {
            border: 1px solid #D1D5DB;
            padding: 6px 8px;
            vertical-align: top;
            background-color: #FAFAFA;
        }

        /* Tanda Tangan */
        .ttd-table {
            width: 100%;
            margin-top: 15px;
            page-break-inside: avoid;
            font-size: 9pt;
        }

        .ttd-table td {
            width: 50%;
            text-align: center;
            vertical-align: top;
        }

        .ttd-space {
            height: 60px;
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
                <div class="kop-instansi">PEMERINTAH PROVINSI / DAERAH</div>
                <div class="kop-sekolah">{{ $penilaian->sekolah->nama }}</div>
                <div class="kop-alamat">
                    {{ $penilaian->sekolah->alamat ?: 'Alamat Sekolah Belum Diisi' }}
                    @if($penilaian->sekolah->npsn) &bull; NPSN: {{ $penilaian->sekolah->npsn }} @endif
                </div>
            </td>
        </tr>
    </table>

    {{-- JUDUL DOKUMEN --}}
    <div class="doc-header">
        <h1 class="doc-title">LEMBAR HASIL PENILAIAN SUPERVISI AKADEMIK</h1>
        <div class="doc-subtitle">INSTRUMEN: {{ strtoupper($penilaian->jenisInstrumen->nama) }}</div>
    </div>

    {{-- IDENTITAS & INFORMASI SUPERVISI --}}
    <table class="info-table">
        <tr>
            <td class="info-label">Nama Guru</td>
            <td class="info-separator">:</td>
            <td class="info-value"><strong>{{ $penilaian->penugasan->guru->nama }}</strong></td>
            <td class="info-label">Mata Pelajaran</td>
            <td class="info-separator">:</td>
            <td class="info-value">{{ $penilaian->penugasan->infoSupervisi?->mata_pelajaran ?: '-' }}</td>
        </tr>
        <tr>
            <td class="info-label">NIP / NUPTK</td>
            <td class="info-separator">:</td>
            <td class="info-value">{{ $penilaian->penugasan->guru->nip ?: ($penilaian->penugasan->guru->nuptk ?: '-') }}</td>
            <td class="info-label">Kelas / Semester</td>
            <td class="info-separator">:</td>
            <td class="info-value">
                {{ $penilaian->penugasan->infoSupervisi?->kelas ?: '-' }} 
                / {{ $penilaian->penugasan->infoSupervisi?->semester ?: ($penilaian->penugasan->periode->semester ?: '-') }}
            </td>
        </tr>
        <tr>
            <td class="info-label">Supervisor Penilai</td>
            <td class="info-separator">:</td>
            <td class="info-value">{{ $penilaian->penugasan->penilai->nama }}</td>
            <td class="info-label">Fase / Elemen</td>
            <td class="info-separator">:</td>
            <td class="info-value">
                Fase {{ $penilaian->penugasan->infoSupervisi?->fase ?: '-' }}
                @if($penilaian->penugasan->infoSupervisi?->elemen) / {{ $penilaian->penugasan->infoSupervisi->elemen }} @endif
            </td>
        </tr>
        <tr>
            <td class="info-label">Periode Supervisi</td>
            <td class="info-separator">:</td>
            <td class="info-value">{{ $penilaian->penugasan->periode->nama }} ({{ $penilaian->penugasan->periode->tahun_ajaran }})</td>
            <td class="info-label">Tanggal Finalisasi</td>
            <td class="info-separator">:</td>
            <td class="info-value">{{ $penilaian->finalized_at?->translatedFormat('d F Y') ?: '-' }}</td>
        </tr>
    </table>

    {{-- TABEL BUTIR PENILAIAN / MATRIKS AKSI --}}
    @if($penilaian->jenisInstrumen?->kode === 'rencana_pengembangan_diri')
        {{-- Tabel Khusus Matriks Rencana Pengembangan Diri --}}
        <table class="content-table">
            <thead>
                <tr>
                    <th style="width: 5%;">No</th>
                    <th style="width: 25%;">Aktivitas Pengembangan</th>
                    <th style="width: 22%;">Tujuan yang Dicapai</th>
                    <th style="width: 20%;">Ukuran Keberhasilan</th>
                    <th style="width: 16%;">Dukungan Dibutuhkan</th>
                    <th style="width: 12%;">Waktu</th>
                </tr>
            </thead>
            <tbody>
                @forelse($penilaian->data_kualitatif['rencana'] ?? [] as $idx => $r)
                    <tr>
                        <td class="text-center font-bold">{{ $idx + 1 }}</td>
                        <td>{{ $r['aktivitas'] ?: '-' }}</td>
                        <td>{{ $r['tujuan'] ?: '-' }}</td>
                        <td>{{ $r['ukuran'] ?: '-' }}</td>
                        <td>{{ $r['dukungan'] ?: '-' }}</td>
                        <td class="text-center">{{ $r['waktu'] ?: '-' }}</td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="6" class="text-center">Belum ada baris rencana aktivitas.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    @else
        <table class="content-table">
            <thead>
                <tr>
                    <th style="width: 6%;">No</th>
                    @if($penilaian->isKuantitatif())
                        <th style="width: 50%;">Aspek / Butir Pengamatan</th>
                        @if($penilaian->jenisInstrumen?->kode === 'observasi_pembelajaran')
                            <th style="width: 20%;">Bukti Pembelajaran</th>
                        @endif
                        <th style="width: 8%;">Skor</th>
                        <th style="width: 16%;">Catatan / Umpan Balik</th>
                    @else
                        <th style="width: 44%;">Aspek / Pertanyaan Reflektif</th>
                        <th style="width: 50%;">Catatan Percakapan / Hasil Refleksi</th>
                    @endif
                </tr>
            </thead>
            <tbody>
                @php
                    $mapJawaban = $penilaian->penilaianButir->keyBy('butir_instrumen_id');
                    $buktiMap = $penilaian->data_kualitatif['bukti'] ?? [];
                    $nomorUrut = 1;
                @endphp

                @if($penilaian->versiInstrumen)
                    @foreach($penilaian->versiInstrumen->bagian as $bagian)
                        <tr class="bagian-header">
                            <td colspan="{{ $penilaian->isKuantitatif() ? ($penilaian->jenisInstrumen?->kode === 'observasi_pembelajaran' ? 5 : 4) : 3 }}">
                                {{ $bagian->kode ? $bagian->kode . '. ' : '' }}{{ $bagian->judul }}
                                @if($bagian->kategori) <span style="font-weight: normal; font-size: 8pt;">({{ $bagian->kategori }})</span> @endif
                            </td>
                        </tr>
                        @foreach($bagian->butir as $butir)
                            @php
                                $jawaban = $mapJawaban->get($butir->id);
                            @endphp
                            <tr>
                                <td class="text-center">{{ $nomorUrut++ }}</td>
                                <td style="white-space: pre-line; line-height: 1.4;">{!! nl2br(e($butir->uraian)) !!}</td>
                                @if($penilaian->isKuantitatif())
                                    @if($penilaian->jenisInstrumen?->kode === 'observasi_pembelajaran')
                                        <td style="font-size: 8pt; color: #1F2937;">{{ $buktiMap[$butir->id] ?? '-' }}</td>
                                    @endif
                                    <td class="text-center" style="font-weight: bold; font-size: 10pt;">{{ $jawaban?->skor ?? '-' }}</td>
                                    <td style="font-size: 8pt; color: #4B5563;">{{ $jawaban?->catatan ?: '-' }}</td>
                                @else
                                    <td style="font-size: 9pt; color: #1F2937;">{{ $jawaban?->catatan ?: '-' }}</td>
                                @endif
                            </tr>
                        @endforeach
                    @endforeach
                @else
                    <tr>
                        <td colspan="4" class="text-center">Data versi instrumen tidak tersedia.</td>
                    </tr>
                @endif
            </tbody>
        </table>
    @endif

    {{-- REKAPITULASI SKOR (KUANTITATIF) ATAU STATUS (KUALITATIF) --}}
    @if($penilaian->isKuantitatif())
        <table class="rekap-box">
            <thead>
                <tr>
                    <th style="width: 25%;">Total Skor Perolehan</th>
                    <th style="width: 25%;">Total Skor Maksimal</th>
                    <th style="width: 25%;">Nilai Akhir (0 - 100)</th>
                    <th style="width: 25%;">Predikat</th>
                </tr>
            </thead>
            <tbody>
                <tr>
                    <td class="rekap-highlight">{{ $penilaian->total_skor }}</td>
                    <td class="rekap-highlight">{{ $penilaian->skor_maks }}</td>
                    <td class="rekap-highlight">{{ number_format($penilaian->nilai, 2) }}</td>
                    <td class="rekap-highlight" style="font-size: 13pt; color: #2E7D32;">
                        {{ $penilaian->predikat }}
                    </td>
                </tr>
            </tbody>
        </table>
    @else
        <table class="rekap-box">
            <thead>
                <tr>
                    <th style="width: 50%;">Status Pelaksanaan</th>
                    <th style="width: 50%;">Kesepakatan Supervisi</th>
                </tr>
            </thead>
            <tbody>
                <tr>
                    <td class="rekap-highlight" style="color: #2E7D32;">Terlaksana dan Lengkap</td>
                    <td class="rekap-highlight" style="font-size: 9pt; font-weight: normal; color: #1F2937;">
                        Disepakati bersama antara Guru dan Supervisor Penilai
                    </td>
                </tr>
            </tbody>
        </table>
    @endif

    {{-- CATATAN & RENCANA TINDAK LANJUT --}}
    <table class="notes-table">
        <tr>
            <th>Catatan Umum & Rekomendasi Supervisor:</th>
        </tr>
        <tr>
            <td>{{ $penilaian->catatan ?: 'Tidak ada catatan umum.' }}</td>
        </tr>
        <tr>
            <th>Rencana Tindak Lanjut:</th>
        </tr>
        <tr>
            <td>{{ $penilaian->tindak_lanjut ?: 'Tidak ada catatan rencana tindak lanjut.' }}</td>
        </tr>
    </table>

    {{-- BLOK TANDA TANGAN --}}
    <table class="ttd-table">
        <tr>
            <td>
                <br>
                Guru yang Disupervisi,
                <div class="ttd-space"></div>
                <div class="ttd-nama">{{ $penilaian->penugasan->guru->nama }}</div>
                <div>NIP: {{ $penilaian->penugasan->guru->nip ?: '-' }}</div>
            </td>
            <td>
                {{ $penilaian->sekolah->alamat ? explode(',', $penilaian->sekolah->alamat)[0] : 'Di Tempat' }}, 
                {{ $penilaian->finalized_at?->translatedFormat('d F Y') ?: now()->translatedFormat('d F Y') }}<br>
                Supervisor Penilai,
                <div class="ttd-space"></div>
                <div class="ttd-nama">{{ $penilaian->penugasan->penilai->nama }}</div>
                <div>NIP: {{ $penilaian->penugasan->penilai->nip ?: '-' }}</div>
            </td>
        </tr>
    </table>

</body>
</html>
