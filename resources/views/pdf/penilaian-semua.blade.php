<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Berkas Lengkap Supervisi - {{ $penugasan->guru->nama }} - {{ $penugasan->periode->nama }}</title>
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

        .page-break {
            page-break-before: always;
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

    {{-- HALAMAN 1: COVER & RANGKUMAN RAPOR LENGKAP HASIL SUPERVISI GURU --}}
    <table class="kop-table">
        <tr>
            <td class="kop-text">
                <div class="kop-instansi">PEMERINTAH PROVINSI / DAERAH</div>
                <div class="kop-sekolah">{{ $penugasan->sekolah->nama }}</div>
                <div class="kop-alamat">
                    {{ $penugasan->sekolah->alamat ?: 'Alamat Sekolah Belum Diisi' }}
                    @if($penugasan->sekolah->npsn) &bull; NPSN: {{ $penugasan->sekolah->npsn }} @endif
                </div>
            </td>
        </tr>
    </table>

    <div class="doc-header-official" style="margin-top: 15px; margin-bottom: 20px;">
        <h1 class="doc-title-main" style="font-size: 13pt;">RAPOR LENGKAP HASIL SUPERVISI AKADEMIK GURU</h1>
        <div style="font-size: 10.5pt; font-weight: bold; color: #F2842F; margin-top: 3px;">
            PERIODE: {{ strtoupper($penugasan->periode->nama) }} ({{ $penugasan->periode->tahun_ajaran }})
        </div>
    </div>

    {{-- IDENTITAS SUBJEK SUPERVISI --}}
    <table class="meta-official-table" style="font-size: 9.5pt; margin-bottom: 20px;">
        <tr>
            <td class="meta-label">Nama Guru</td>
            <td class="meta-sep">:</td>
            <td class="meta-val"><strong>{{ $penugasan->guru->nama }}</strong></td>
            <td class="meta-label">Supervisor (Penilai)</td>
            <td class="meta-sep">:</td>
            <td class="meta-val"><strong>{{ $penugasan->penilai->nama }}</strong></td>
        </tr>
        <tr>
            <td class="meta-label">NIP / NUPTK</td>
            <td class="meta-sep">:</td>
            <td class="meta-val">{{ $penugasan->guru->nip ?: ($penugasan->guru->nuptk ?: '-') }}</td>
            <td class="meta-label">NIP Supervisor</td>
            <td class="meta-sep">:</td>
            <td class="meta-val">{{ $penugasan->penilai->nip ?: '-' }}</td>
        </tr>
        <tr>
            <td class="meta-label">Mata Pelajaran</td>
            <td class="meta-sep">:</td>
            <td class="meta-val">{{ $penugasan->infoSupervisi?->mata_pelajaran ?: '-' }}</td>
            <td class="meta-label">Kelas / Semester</td>
            <td class="meta-sep">:</td>
            <td class="meta-val">
                {{ $penugasan->infoSupervisi?->kelas ?: '-' }} / {{ $penugasan->infoSupervisi?->semester ?: ($penugasan->periode->semester ?: '-') }}
            </td>
        </tr>
        <tr>
            <td class="meta-label">Fase / Elemen</td>
            <td class="meta-sep">:</td>
            <td class="meta-val">
                Fase {{ $penugasan->infoSupervisi?->fase ?: '-' }}
                @if($penugasan->infoSupervisi?->elemen) / {{ $penugasan->infoSupervisi->elemen }} @endif
            </td>
            <td class="meta-label">Kepala Sekolah</td>
            <td class="meta-sep">:</td>
            <td class="meta-val">{{ $penugasan->sekolah->kepalaSekolah?->nama ?: '-' }}</td>
        </tr>
    </table>

    {{-- TABEL RANGKUMAN HASIL SEMUA INSTRUMEN --}}
    <div style="font-weight: bold; font-size: 10pt; color: #253C6D; margin-bottom: 6px;">
        Rangkuman Capaian 5 Instrumen Resmi Supervisi:
    </div>

    <table class="content-table" style="font-size: 8.5pt;">
        <thead>
            <tr>
                <th style="width: 5%;">No</th>
                <th style="width: 32%;">Instrumen Supervisi</th>
                <th style="width: 15%;">Status</th>
                <th style="width: 12%;">Skor / Nilai</th>
                <th style="width: 12%;">Predikat</th>
                <th style="width: 24%;">Kesimpulan / Catatan</th>
            </tr>
        </thead>
        <tbody>
            @php
                $urutanKunci = ['pra_observasi', 'telaah_perencanaan', 'observasi_pembelajaran', 'refleksi_pasca_observasi', 'rencana_pengembangan_diri'];
                $totalNilai = 0;
                $countNilai = 0;
            @endphp

            @foreach($urutanKunci as $noIdx => $kodeKunci)
                @php
                    $penItem = $penugasan->penilaian->firstWhere('jenisInstrumen.kode', $kodeKunci);
                    $namaInst = $penItem?->jenisInstrumen?->nama ?? ucfirst(str_replace('_', ' ', $kodeKunci));
                    $isFinal = $penItem?->isFinal() ?? false;
                    $isKuan = $penItem?->isKuantitatif() ?? false;

                    if ($isFinal && $isKuan && $penItem->nilai !== null) {
                        $totalNilai += (float) $penItem->nilai;
                        $countNilai++;
                    }
                @endphp
                <tr>
                    <td class="text-center font-bold">{{ $noIdx + 1 }}</td>
                    <td style="font-weight: 600;">{{ $namaInst }}</td>
                    <td class="text-center">
                        @if($isFinal)
                            <strong style="color: #1B5E20;">Final (Selesai)</strong>
                        @elseif($penItem?->isDraft())
                            <span style="color: #8A5A00;">Draft</span>
                        @elseif($penItem?->isDirevisi())
                            <span style="color: #8A3B00;">Direvisi</span>
                        @else
                            <span style="color: #6B7280;">Belum Dinilai</span>
                        @endif
                    </td>
                    <td class="text-center" style="font-weight: bold;">
                        @if($isFinal)
                            @if($isKuan)
                                {{ number_format($penItem->nilai, 2) }}
                            @else
                                <span style="color: #1B5E20;">Terlaksana</span>
                            @endif
                        @else
                            -
                        @endif
                    </td>
                    <td class="text-center">
                        @if($isFinal && $isKuan)
                            <strong>{{ $penItem->predikat }}</strong>
                        @elseif($isFinal && ! $isKuan)
                            Baik
                        @else
                            -
                        @endif
                    </td>
                    <td style="font-size: 8pt; color: #4B5563;">
                        {{ $isFinal ? ($penItem->catatan ?: 'Selesai dan disepakati.') : 'Menunggu penyelesaian penilaian.' }}
                    </td>
                </tr>
            @endforeach
        </tbody>
        @if($countNilai > 0)
            @php
                $rerata = round($totalNilai / $countNilai, 2);
                $predikatAkhir = 'C';
                if ($rerata >= 86) $predikatAkhir = 'A';
                elseif ($rerata >= 76) $predikatAkhir = 'B';
                elseif ($rerata < 56) $predikatAkhir = 'D';
            @endphp
            <tfoot>
                <tr style="background-color: #E6ECF7; font-weight: bold; color: #253C6D;">
                    <td colspan="3" class="text-right" style="padding: 6px;">RATA-RATA NILAI AKADEMIK (KUANTITATIF):</td>
                    <td class="text-center" style="font-size: 10.5pt; color: #1B5E20;">{{ number_format($rerata, 2) }}</td>
                    <td class="text-center" style="font-size: 10.5pt; color: #1B5E20;">{{ $predikatAkhir }}</td>
                    <td style="font-size: 8pt; color: #253C6D;">Berdasarkan instrumen final</td>
                </tr>
            </tfoot>
        @endif
    </table>

    {{-- TANDA TANGAN COVER --}}
    <table class="ttd-table" style="margin-top: 30px;">
        <tr>
            <td style="width: 50%; text-align: center; vertical-align: top;">
                Guru yang Disupervisi,
                <div class="ttd-space"></div>
                <div class="ttd-nama">{{ $penugasan->guru->nama }}</div>
                <div style="font-size: 8.5pt; color: #4B5563;">NIP: {{ $penugasan->guru->nip ?: '-' }}</div>
            </td>
            <td style="width: 50%; text-align: center; vertical-align: top;">
                {{ $penugasan->sekolah->alamat ? explode(',', $penugasan->sekolah->alamat)[0] : 'Di Tempat' }}, 
                {{ now()->translatedFormat('d F Y') }}<br>
                Supervisor Penilai,
                <div class="ttd-space"></div>
                <div class="ttd-nama">{{ $penugasan->penilai->nama }}</div>
                <div style="font-size: 8.5pt; color: #4B5563;">NIP: {{ $penugasan->penilai->nip ?: '-' }}</div>
            </td>
        </tr>
    </table>

    {{-- HALAMAN BERIKUTNYA: SETIAP INSTRUMEN DILAMPIRKAN SECARA LENGKAP --}}
    @foreach($urutanKunci as $kodeKunci)
        @php
            $penilaian = $penugasan->penilaian->firstWhere('jenisInstrumen.kode', $kodeKunci);
        @endphp

        @if($penilaian)
            <div class="page-break"></div>

            {{-- KOP SEKOLAH UNTUK SETIAP LAMPIRAN --}}
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

            @if($kodeKunci === 'pra_observasi')
                @include('pdf.instrumen.pra-observasi', ['penilaian' => $penilaian])
            @elseif($kodeKunci === 'telaah_perencanaan')
                @include('pdf.instrumen.telaah-perencanaan', ['penilaian' => $penilaian])
            @elseif($kodeKunci === 'observasi_pembelajaran')
                @include('pdf.instrumen.observasi-pembelajaran', ['penilaian' => $penilaian])
            @elseif($kodeKunci === 'refleksi_pasca_observasi')
                @include('pdf.instrumen.refleksi-pasca-observasi', ['penilaian' => $penilaian])
            @elseif($kodeKunci === 'rencana_pengembangan_diri')
                @include('pdf.instrumen.rencana-pengembangan-diri', ['penilaian' => $penilaian])
            @endif
        @endif
    @endforeach

</body>
</html>
