@php
    $jadwal = $penilaian->penugasan->jadwalSupervisi->firstWhere('jenis_instrumen_id', $penilaian->jenis_instrumen_id);
    $tanggalTeks = $penilaian->finalized_at?->translatedFormat('d F Y') ?: ($jadwal?->tanggal ? $jadwal->tanggal->translatedFormat('d F Y') : now()->translatedFormat('d F Y'));
    $hariTeks = $penilaian->finalized_at?->translatedFormat('l') ?: ($jadwal?->tanggal ? $jadwal->tanggal->translatedFormat('l') : now()->translatedFormat('l'));
    $rencanaList = $penilaian->data_kualitatif['rencana'] ?? [];
@endphp

<div class="instrumen-wrapper">
    <div class="doc-header-official">
        <h2 class="doc-title-main">LEMBAR RENCANA PENGEMBANGAN DIRI</h2>
    </div>

    {{-- METADATA HEADER IDENTITAS SESUAI DOKUMEN RESMI --}}
    <table class="meta-official-table">
        <tr>
            <td class="meta-label">Nama Guru</td>
            <td class="meta-sep">:</td>
            <td class="meta-val"><strong>{{ $penilaian->penugasan->guru->nama }}</strong></td>
            <td class="meta-label">Tahun Pelajaran</td>
            <td class="meta-sep">:</td>
            <td class="meta-val">{{ $penilaian->penugasan->periode->tahun_ajaran }}</td>
        </tr>
        <tr>
            <td class="meta-label">NIP</td>
            <td class="meta-sep">:</td>
            <td class="meta-val">{{ $penilaian->penugasan->guru->nip ?: '-' }}</td>
            <td class="meta-label">Sekolah</td>
            <td class="meta-sep">:</td>
            <td class="meta-val">{{ $penilaian->sekolah->nama }}</td>
        </tr>
        <tr>
            <td class="meta-label" rowspan="2" style="vertical-align: top;">Supervisor</td>
            <td class="meta-sep" rowspan="2" style="vertical-align: top;">:</td>
            <td class="meta-val" style="padding-bottom: 2px;">
                1. {{ $penilaian->penugasan->penilai->nama }}
            </td>
            <td class="meta-label">Kelas</td>
            <td class="meta-sep">:</td>
            <td class="meta-val">{{ $penilaian->penugasan->infoSupervisi?->kelas ?: '-' }}</td>
        </tr>
        <tr>
            <td class="meta-val">
                2. {{ $penilaian->sekolah->kepalaSekolah ? $penilaian->sekolah->kepalaSekolah->nama : '-' }}
            </td>
            <td class="meta-label">Mata Pelajaran</td>
            <td class="meta-sep">:</td>
            <td class="meta-val">{{ $penilaian->penugasan->infoSupervisi?->mata_pelajaran ?: '-' }}</td>
        </tr>
    </table>

    {{-- TABEL MATRIKS RENCANA PENGEMBANGAN DIRI SESUAI FORMAT RESMI --}}
    <table class="content-table" style="margin-top: 10px;">
        <thead>
            <tr>
                <th style="width: 5%;">No</th>
                <th style="width: 25%;">Aktivitas Pengembangan</th>
                <th style="width: 23%;">Tujuan yang hendak dicapai</th>
                <th style="width: 20%;">Ukuran Keberhasilan</th>
                <th style="width: 15%;">Pendukung</th>
                <th style="width: 12%;">Waktu</th>
            </tr>
        </thead>
        <tbody>
            @forelse($rencanaList as $idx => $r)
                <tr>
                    <td class="text-center font-bold">{{ $idx + 1 }}</td>
                    <td style="font-weight: 600;">{{ $r['aktivitas'] ?: '-' }}</td>
                    <td>{{ $r['tujuan'] ?: '-' }}</td>
                    <td>{{ $r['ukuran'] ?: '-' }}</td>
                    <td>{{ $r['dukungan'] ?: '-' }}</td>
                    <td class="text-center">{{ $r['waktu'] ?: '-' }}</td>
                </tr>
            @empty
                {{-- Template kosong bila belum ada baris diisi --}}
                @for($i = 1; $i <= 4; $i++)
                    <tr>
                        <td class="text-center">{{ $i }}</td>
                        <td>&nbsp;</td>
                        <td>&nbsp;</td>
                        <td>&nbsp;</td>
                        <td>&nbsp;</td>
                        <td>&nbsp;</td>
                    </tr>
                @endfor
            @endforelse
        </tbody>
    </table>

    {{-- CATATAN UMUM DAN TINDAK LANJUT SUPERVISOR --}}
    @if($penilaian->catatan || $penilaian->tindak_lanjut)
        <table class="notes-table" style="margin-top: 10px;">
            @if($penilaian->catatan)
                <tr>
                    <th>Catatan Kesepakatan Supervisor:</th>
                </tr>
                <tr>
                    <td>{!! nl2br(e($penilaian->catatan)) !!}</td>
                </tr>
            @endif
            @if($penilaian->tindak_lanjut)
                <tr>
                    <th>Rencana Tindak Lanjut:</th>
                </tr>
                <tr>
                    <td>{!! nl2br(e($penilaian->tindak_lanjut)) !!}</td>
                </tr>
            @endif
        </table>
    @endif

    {{-- BLOK TANDA TANGAN KESEPAKATAN BERSAMA SESUAI FORMAT RESMI --}}
    <div style="margin-top: 16px; page-break-inside: avoid;">
        <table style="width: 100%; border: none; font-size: 9pt;">
            <tr>
                <td style="width: 40%; vertical-align: top;">
                    <strong>Disepakati Bersama</strong><br>
                    <table style="border: none; margin-top: 3px; font-size: 8.5pt;">
                        <tr>
                            <td style="padding: 2px 4px 2px 0;">Hari</td>
                            <td style="padding: 2px 4px;">:</td>
                            <td style="padding: 2px 4px;">{{ $hariTeks }}</td>
                        </tr>
                        <tr>
                            <td style="padding: 2px 4px 2px 0;">Tanggal</td>
                            <td style="padding: 2px 4px;">:</td>
                            <td style="padding: 2px 4px;">{{ $tanggalTeks }}</td>
                        </tr>
                    </table>
                </td>
                <td style="width: 30%; text-align: center; vertical-align: top;">
                    Guru,<br>
                    <div style="height: 55px;"></div>
                    <strong>( {{ $penilaian->penugasan->guru->nama }} )</strong>
                    <div style="font-size: 8pt; color: #4B5563;">NIP: {{ $penilaian->penugasan->guru->nip ?: '................................' }}</div>
                </td>
                <td style="width: 30%; text-align: center; vertical-align: top;">
                    Supervisor,<br>
                    <div style="height: 55px;"></div>
                    <strong>( {{ $penilaian->penugasan->penilai->nama }} )</strong>
                    <div style="font-size: 8pt; color: #4B5563;">NIP: {{ $penilaian->penugasan->penilai->nip ?: '................................' }}</div>
                </td>
            </tr>
        </table>
    </div>
</div>
