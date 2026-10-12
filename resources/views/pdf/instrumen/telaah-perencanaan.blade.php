@php
    $mapJawaban = $penilaian->penilaianButir->keyBy('butir_instrumen_id');
    $nomorUrut = 1;
@endphp

<div class="instrumen-wrapper">
    <div class="doc-header-official">
        <h2 class="doc-title-main">Instrumen Telaah Perencanaan Pembelajaran</h2>
    </div>

    {{-- IDENTITAS SUPERVISI --}}
    <table class="meta-official-table">
        <tr>
            <td class="meta-label">Nama Guru</td>
            <td class="meta-sep">:</td>
            <td class="meta-val"><strong>{{ $penilaian->penugasan->guru->nama }}</strong></td>
            <td class="meta-label">Mata Pelajaran</td>
            <td class="meta-sep">:</td>
            <td class="meta-val">{{ $penilaian->penugasan->infoSupervisi?->mata_pelajaran ?: '-' }}</td>
        </tr>
        <tr>
            <td class="meta-label">NIP</td>
            <td class="meta-sep">:</td>
            <td class="meta-val">{{ $penilaian->penugasan->guru->nip ?: '-' }}</td>
            <td class="meta-label">Kelas / Semester</td>
            <td class="meta-sep">:</td>
            <td class="meta-val">{{ $penilaian->penugasan->infoSupervisi?->kelas ?: '-' }} / {{ $penilaian->penugasan->infoSupervisi?->semester ?: ($penilaian->penugasan->periode->semester ?: '-') }}</td>
        </tr>
        <tr>
            <td class="meta-label">Supervisor Penilai</td>
            <td class="meta-sep">:</td>
            <td class="meta-val">{{ $penilaian->penugasan->penilai->nama }}</td>
            <td class="meta-label">Tanggal Pelaksanaan</td>
            <td class="meta-sep">:</td>
            <td class="meta-val">{{ $penilaian->finalized_at?->translatedFormat('d F Y') ?: now()->translatedFormat('d F Y') }}</td>
        </tr>
    </table>

    {{-- TABEL TELAAH PERENCANAAN PEMBELAJARAN SESUAI DOKUMEN RESMI --}}
    <table class="content-table">
        <thead>
            <tr>
                <th style="width: 5%;" rowspan="2">No</th>
                <th style="width: 52%;" rowspan="2">Aspek yang diamati</th>
                <th style="width: 20%;" colspan="5">Skala</th>
                <th style="width: 23%;" rowspan="2">Komentar Kritis</th>
            </tr>
            <tr>
                <th style="width: 4%;">0</th>
                <th style="width: 4%;">1</th>
                <th style="width: 4%;">2</th>
                <th style="width: 4%;">3</th>
                <th style="width: 4%;">4</th>
            </tr>
        </thead>
        <tbody>
            @if($penilaian->versiInstrumen)
                @foreach($penilaian->versiInstrumen->bagian->sortBy('urutan') as $bagian)
                    @php
                        $isBagianKualitatif = $bagian->kategori === 'Kualitatif' || str_contains(strtolower($bagian->judul), 'catatan') || str_contains(strtolower($bagian->judul), 'rekomendasi');
                    @endphp

                    <tr class="bagian-header">
                        <td colspan="8">
                            {{ $bagian->judul }}
                        </td>
                    </tr>

                    @foreach($bagian->butir->sortBy('urutan') as $butir)
                        @php
                            $jawaban = $mapJawaban->get($butir->id);
                            $skorButir = $jawaban?->skor !== null ? (int)$jawaban->skor : null;
                        @endphp

                        @if($isBagianKualitatif || $butir->skor_maks == 0)
                            {{-- Baris Catatan Kritis / Rekomendasi (Butir 16, 17, 18 dsb.) --}}
                            <tr>
                                <td class="text-center">{{ $nomorUrut++ }}</td>
                                <td colspan="6" style="font-weight: 600; background-color: #FAFAFA;">
                                    {!! nl2br(e($butir->uraian)) !!}
                                </td>
                                <td style="font-size: 8.5pt; color: #1F2937;">
                                    {!! $jawaban?->catatan ? nl2br(e($jawaban->catatan)) : '<em style="color:#9CA3AF;">-</em>' !!}
                                </td>
                            </tr>
                        @else
                            {{-- Baris Skala 0 - 4 --}}
                            <tr>
                                <td class="text-center">{{ $nomorUrut++ }}</td>
                                <td style="line-height: 1.35;">
                                    {!! nl2br(e($butir->uraian)) !!}
                                </td>
                                @for($s = 0; $s <= 4; $s++)
                                    <td class="text-center" style="font-weight: bold; font-size: 9pt; {{ $skorButir === $s ? 'background-color: #E6ECF7; color: #253C6D;' : '' }}">
                                        @if($skorButir === $s)
                                            ✓
                                        @endif
                                    </td>
                                @endfor
                                <td style="font-size: 8pt; color: #374151;">
                                    {{ $jawaban?->catatan ?: '-' }}
                                </td>
                            </tr>
                        @endif
                    @endforeach
                @endforeach
            @endif
        </tbody>
    </table>

    {{-- KETERANGAN SKALA RESMI --}}
    <div style="font-size: 8pt; color: #374151; margin-top: 6px; margin-bottom: 10px; line-height: 1.3;">
        <strong>Skala yang digunakan dalam menelaah:</strong><br>
        0 = tidak ada &nbsp;|&nbsp; 1 = sangat kurang &nbsp;|&nbsp; 2 = kurang &nbsp;|&nbsp; 3 = baik &nbsp;|&nbsp; 4 = sangat baik
    </div>

    {{-- REKAPITULASI SKOR RESMI --}}
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
                <td class="rekap-highlight" style="font-size: 12pt; color: #2E7D32;">
                    {{ $penilaian->predikat }}
                </td>
            </tr>
        </tbody>
    </table>

    {{-- CATATAN UMUM DAN TINDAK LANJUT --}}
    @if($penilaian->catatan || $penilaian->tindak_lanjut)
        <table class="notes-table">
            @if($penilaian->catatan)
                <tr>
                    <th>Catatan & Rekomendasi Supervisor:</th>
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

    {{-- BLOK TANDA TANGAN --}}
    <table class="ttd-table">
        <tr>
            <td style="width: 50%; text-align: center; vertical-align: top;">
                Guru yang Disupervisi,
                <div class="ttd-space"></div>
                <div class="ttd-nama">{{ $penilaian->penugasan->guru->nama }}</div>
                <div style="font-size: 8.5pt; color: #4B5563;">NIP: {{ $penilaian->penugasan->guru->nip ?: '-' }}</div>
            </td>
            <td style="width: 50%; text-align: center; vertical-align: top;">
                {{ $penilaian->sekolah->alamat ? explode(',', $penilaian->sekolah->alamat)[0] : 'Di Tempat' }}, 
                {{ $penilaian->finalized_at?->translatedFormat('d F Y') ?: now()->translatedFormat('d F Y') }}<br>
                Supervisor Penilai,
                <div class="ttd-space"></div>
                <div class="ttd-nama">{{ $penilaian->penugasan->penilai->nama }}</div>
                <div style="font-size: 8.5pt; color: #4B5563;">NIP: {{ $penilaian->penugasan->penilai->nip ?: '-' }}</div>
            </td>
        </tr>
    </table>
</div>
