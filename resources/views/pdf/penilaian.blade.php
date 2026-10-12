<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Hasil Penilaian Supervisi - {{ $penilaian->penugasan->guru->nama }} - {{ $penilaian->jenisInstrumen->nama }}</title>
    <style>
        @page {
            size: A4 portrait;
            margin: 12mm 15mm 15mm 15mm;
        }

        body {
            font-family: DejaVu Sans, Helvetica, Arial, sans-serif;
            font-size: 9.5pt;
            line-height: 1.4;
            color: #1F2937;
        }

        /* Kop Surat Sekolah */
        .kop-table {
            width: 100%;
            border-bottom: 2.5px solid #253C6D;
            padding-bottom: 6px;
            margin-bottom: 12px;
        }

        .kop-text {
            text-align: center;
            vertical-align: middle;
        }

        .kop-instansi {
            font-size: 10.5pt;
            font-weight: normal;
            letter-spacing: 0.5px;
            text-transform: uppercase;
            color: #374151;
        }

        .kop-sekolah {
            font-size: 13.5pt;
            font-weight: bold;
            color: #253C6D;
            margin: 1px 0;
            text-transform: uppercase;
        }

        .kop-alamat {
            font-size: 8pt;
            color: #4B5563;
        }

        /* Judul Dokumen Resmi */
        .doc-header-official {
            text-align: center;
            margin-bottom: 12px;
        }

        .doc-title-main {
            font-size: 11.5pt;
            font-weight: bold;
            color: #253C6D;
            text-transform: uppercase;
            margin: 0;
            letter-spacing: 0.3px;
        }

        /* Tabel Identitas / Meta Resmi */
        .meta-official-table {
            width: 100%;
            border-collapse: collapse;
            font-size: 8.5pt;
            margin-bottom: 10px;
        }

        .meta-official-table td {
            padding: 2.5px 3px;
            vertical-align: top;
        }

        .meta-label {
            width: 20%;
            color: #374151;
            font-weight: 600;
        }

        .meta-sep {
            width: 2%;
            text-align: center;
        }

        .meta-val {
            width: 28%;
            color: #111827;
        }

        /* 4 Kotak Berbingkai Pra-Observasi */
        .pra-box-container {
            border: 1.5px solid #1F2937;
            margin-bottom: 10px;
            page-break-inside: avoid;
        }

        .pra-box-title {
            background-color: #F3F4F6;
            font-weight: bold;
            font-style: italic;
            font-size: 9pt;
            padding: 4px 8px;
            border-bottom: 1px solid #1F2937;
            color: #111827;
        }

        .pra-box-content {
            padding: 8px 10px;
            min-height: 60px;
            font-size: 9pt;
            line-height: 1.45;
            color: #1F2937;
            background-color: #FFFFFF;
        }

        /* Tabel Penilaian Standar */
        .content-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 10px;
            font-size: 8pt;
        }

        .content-table th,
        .content-table td {
            border: 1px solid #9CA3AF;
            padding: 4px 5px;
            vertical-align: top;
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
            font-size: 8.5pt;
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

        /* Rekapitulasi Skor Box */
        .rekap-box {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 10px;
            font-size: 8.5pt;
            page-break-inside: avoid;
        }

        .rekap-box th, .rekap-box td {
            border: 1px solid #253C6D;
            padding: 5px 6px;
        }

        .rekap-box th {
            background-color: #253C6D;
            color: #FFFFFF;
            text-align: center;
            font-weight: bold;
        }

        .rekap-highlight {
            font-size: 10.5pt;
            font-weight: bold;
            color: #253C6D;
            text-align: center;
        }

        /* Catatan Tambahan */
        .notes-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 12px;
            font-size: 8.5pt;
            page-break-inside: avoid;
        }

        .notes-table th {
            background-color: #E6ECF7;
            color: #253C6D;
            text-align: left;
            padding: 4px 6px;
            border: 1px solid #9CA3AF;
            font-weight: bold;
        }

        .notes-table td {
            border: 1px solid #9CA3AF;
            padding: 5px 6px;
            vertical-align: top;
            background-color: #FAFAFA;
        }

        /* Tanda Tangan */
        .ttd-table {
            width: 100%;
            margin-top: 14px;
            page-break-inside: avoid;
            font-size: 8.5pt;
        }

        .ttd-table td {
            width: 50%;
            text-align: center;
            vertical-align: top;
        }

        .ttd-space {
            height: 55px;
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

    {{-- KONTEN INSTRUMEN SPESIFIK BERDASARKAN KODE RESMI --}}
    @php
        $kodeInstrumen = $penilaian->jenisInstrumen?->kode;
    @endphp

    @if($kodeInstrumen === 'pra_observasi')
        @include('pdf.instrumen.pra-observasi')
    @elseif($kodeInstrumen === 'telaah_perencanaan')
        @include('pdf.instrumen.telaah-perencanaan')
    @elseif($kodeInstrumen === 'observasi_pembelajaran')
        @include('pdf.instrumen.observasi-pembelajaran')
    @elseif($kodeInstrumen === 'refleksi_pasca_observasi')
        @include('pdf.instrumen.refleksi-pasca-observasi')
    @elseif($kodeInstrumen === 'rencana_pengembangan_diri')
        @include('pdf.instrumen.rencana-pengembangan-diri')
    @else
        {{-- Fallback untuk instrumen kustom lainnya --}}
        <div class="doc-header-official">
            <h2 class="doc-title-main">{{ $penilaian->jenisInstrumen->nama }}</h2>
        </div>
        @include('pdf.instrumen.telaah-perencanaan')
    @endif

</body>
</html>
