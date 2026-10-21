@php
    $mapJawaban = $penilaian->penilaianButir->keyBy('butir_instrumen_id');
    $skor0Count = 0;
    $skor1Count = 0;
    $skor2Count = 0;

    foreach($penilaian->penilaianButir as $pb) {
        if ($pb->skor === 0) $skor0Count++;
        elseif ($pb->skor === 1) $skor1Count++;
        elseif ($pb->skor === 2) $skor2Count++;
    }

    $totalSkor = $penilaian->total_skor ?? $penilaian->penilaianButir->sum('skor');
    $skorMaks = $penilaian->skor_maks ?? 90;
    $nilai = $penilaian->nilai ?? ($skorMaks > 0 ? round(($totalSkor / $skorMaks) * 100, 2) : 0);
    $predikatTeks = match($penilaian->predikat) {
        'A' => 'Sangat baik',
        'B' => 'Baik',
        'C' => 'Cukup',
        'D' => 'Kurang',
        default => ($nilai >= 91 ? 'Sangat baik' : ($nilai >= 81 ? 'Baik' : ($nilai >= 71 ? 'Cukup' : 'Kurang'))),
    };
@endphp

<div class="instrumen-wrapper">
    <div class="doc-header-official text-center" style="margin-bottom: 14px;">
        <h2 style="font-size: 11pt; font-weight: bold; margin: 0; text-transform: uppercase; color: #1F2937;">
            INSTRUMEN SUPERVISI PERANGKAT AJAR<br>
            PERENCANAAN PEMBELAJARAN MENDALAM<br>
            TAHUN PELAJARAN {{ $penilaian->penugasan->periode->tahun_ajaran ?? '2026/2027' }}
        </h2>
    </div>

    {{-- IDENTITAS SUPERVISI SESUAI DOKUMEN --}}
    <table style="width: 100%; font-size: 9pt; margin-bottom: 12px; border-collapse: collapse;">
        <tr>
            <td style="width: 22%; padding: 2px 0;">Nama Sekolah</td>
            <td style="width: 2%;">:</td>
            <td style="width: 76%; font-weight: bold;">{{ $penilaian->sekolah->nama }}</td>
        </tr>
        <tr>
            <td style="padding: 2px 0;">Nama Guru</td>
            <td>:</td>
            <td style="font-weight: bold;">{{ $penilaian->penugasan->guru->nama }}</td>
        </tr>
        <tr>
            <td style="padding: 2px 0;">Mata Pelajaran</td>
            <td>:</td>
            <td>{{ $penilaian->penugasan->infoSupervisi?->mata_pelajaran ?: '-' }}</td>
        </tr>
        <tr>
            <td style="padding: 2px 0;">Fase (Kelas)/Semester</td>
            <td>:</td>
            <td>
                Fase {{ $penilaian->penugasan->infoSupervisi?->fase ?: '-' }} 
                ({{ $penilaian->penugasan->infoSupervisi?->kelas ?: '-' }}) / 
                {{ $penilaian->penugasan->infoSupervisi?->semester ?: ($penilaian->penugasan->periode->semester ?: '-') }}
            </td>
        </tr>
    </table>

    {{-- TABEL PENILAIAN PERANGKAT AJAR --}}
    <table class="content-table" style="width: 100%; border-collapse: collapse; font-size: 8.5pt;">
        <thead>
            <tr style="background-color: #F3F4F6;">
                <th style="width: 6%; text-align: center; border: 1px solid #374151; padding: 4px;" rowspan="2">NO</th>
                <th style="width: 58%; text-align: center; border: 1px solid #374151; padding: 4px;" rowspan="2">KOMPONEN ADMINISTRASI PEMBELAJARAN</th>
                <th style="width: 18%; text-align: center; border: 1px solid #374151; padding: 4px;" colspan="3">SKOR</th>
                <th style="width: 18%; text-align: center; border: 1px solid #374151; padding: 4px;" rowspan="2">KETERANGAN</th>
            </tr>
            <tr style="background-color: #F3F4F6;">
                <th style="width: 6%; text-align: center; border: 1px solid #374151; padding: 3px;">0</th>
                <th style="width: 6%; text-align: center; border: 1px solid #374151; padding: 3px;">1</th>
                <th style="width: 6%; text-align: center; border: 1px solid #374151; padding: 3px;">2</th>
            </tr>
        </thead>
        <tbody>
            @if($penilaian->versiInstrumen)
                @foreach($penilaian->versiInstrumen->bagian->sortBy('urutan') as $bagian)
                    {{-- Header Bagian jika bukan bagian pertama --}}
                    @if($bagian->kode !== 'ADMINISTRASI_PEMBELAJARAN')
                        <tr style="background-color: #F9FAFB; font-weight: bold;">
                            <td colspan="6" style="border: 1px solid #374151; padding: 3px 6px;">
                                {{ $bagian->judul }}
                            </td>
                        </tr>
                    @endif

                    @foreach($bagian->butir->sortBy('urutan') as $butir)
                        @php
                            $jawaban = $mapJawaban->get($butir->id);
                            $skorButir = $jawaban?->skor !== null ? (int)$jawaban->skor : null;
                        @endphp
                        <tr>
                            <td style="border: 1px solid #374151; text-align: center; padding: 3px;">
                                {{ $loop->parent->first ? $loop->iteration : '' }}
                            </td>
                            <td style="border: 1px solid #374151; padding: 3px 6px; line-height: 1.3;">
                                {{ $butir->uraian }}
                            </td>
                            <td style="border: 1px solid #374151; text-align: center; font-weight: bold; padding: 3px; {{ $skorButir === 0 ? 'background-color: #E6ECF7;' : '' }}">
                                {{ $skorButir === 0 ? '✓' : '' }}
                            </td>
                            <td style="border: 1px solid #374151; text-align: center; font-weight: bold; padding: 3px; {{ $skorButir === 1 ? 'background-color: #E6ECF7;' : '' }}">
                                {{ $skorButir === 1 ? '✓' : '' }}
                            </td>
                            <td style="border: 1px solid #374151; text-align: center; font-weight: bold; padding: 3px; {{ $skorButir === 2 ? 'background-color: #E6ECF7;' : '' }}">
                                {{ $skorButir === 2 ? '✓' : '' }}
                            </td>
                            <td style="border: 1px solid #374151; padding: 3px 6px; font-size: 8pt; color: #374151;">
                                {{ $jawaban?->catatan ?: '-' }}
                            </td>
                        </tr>
                    @endforeach
                @endforeach
            @endif

            {{-- Baris Jumlah --}}
            <tr style="font-weight: bold; background-color: #F9FAFB;">
                <td colspan="2" style="border: 1px solid #374151; text-align: left; padding: 4px 6px;">Jumlah</td>
                <td style="border: 1px solid #374151; text-align: center; padding: 4px;">{{ $skor0Count }}</td>
                <td style="border: 1px solid #374151; text-align: center; padding: 4px;">{{ $skor1Count }}</td>
                <td style="border: 1px solid #374151; text-align: center; padding: 4px;">{{ $skor2Count }}</td>
                <td style="border: 1px solid #374151;"></td>
            </tr>

            {{-- Baris Skor Total --}}
            <tr style="font-weight: bold; background-color: #F9FAFB;">
                <td colspan="2" style="border: 1px solid #374151; text-align: left; padding: 4px 6px;">Skor Total</td>
                <td colspan="3" style="border: 1px solid #374151; text-align: center; padding: 4px; font-size: 9.5pt; color: #1B5E20;">
                    {{ $totalSkor }} / {{ $skorMaks }}
                </td>
                <td style="border: 1px solid #374151;"></td>
            </tr>

            {{-- Baris Ketercapaian --}}
            <tr style="font-weight: bold; background-color: #F3F4F6;">
                <td colspan="2" style="border: 1px solid #374151; text-align: left; padding: 4px 6px;">Ketercapaian</td>
                <td colspan="3" style="border: 1px solid #374151; text-align: center; padding: 4px; font-size: 9.5pt; color: #253C6D;">
                    {{ number_format($nilai, 2, ',', '.') }}% ({{ $predikatTeks }})
                </td>
                <td style="border: 1px solid #374151;"></td>
            </tr>
        </tbody>
    </table>

    {{-- KETERANGAN AMBANG PREDIKAT --}}
    <div style="font-size: 8pt; margin-top: 8px; line-height: 1.4; color: #374151;">
        <table style="width: 100%; border-collapse: collapse;">
            <tr>
                <td style="width: 45%; vertical-align: top;">
                    <strong>Keterangan:</strong> NA = (Skor Peroleh / Skor Maksimal) x 100<br>
                    <strong>Ketercapaian:</strong><br>
                    91% - 100% = Sangat baik<br>
                    81% - 90% = Baik<br>
                    71% - 80% = Cukup<br>
                    Dibawah 71% = Kurang
                </td>
                <td style="width: 55%; vertical-align: top;">
                    <strong>Saran:</strong>
                    <div style="min-height: 38px; border: 1px solid #D1D5DB; background-color: #FAFAFA; padding: 4px 6px; border-radius: 4px; margin-top: 3px; font-size: 8pt; font-style: italic;">
                        {{ $penilaian->catatan ?: ($penilaian->tindak_lanjut ?: 'Perangkat pembelajaran telah ditelaah dan memenuhi kaidah kurikulum yang berlaku.') }}
                    </div>
                </td>
            </tr>
        </table>
    </div>

    {{-- BLOK TANDA TANGAN RESMI --}}
    <table class="ttd-table" style="width: 100%; margin-top: 14px; font-size: 8.5pt; border-collapse: collapse;">
        <tr>
            <td style="width: 50%;"></td>
            <td style="width: 50%; text-align: center; vertical-align: top;">
                {{ $penilaian->sekolah->alamat ? explode(',', $penilaian->sekolah->alamat)[0] : 'Di Tempat' }}, 
                {{ $penilaian->finalized_at?->translatedFormat('d F Y') ?: now()->translatedFormat('d F Y') }}
            </td>
        </tr>
        <tr>
            <td style="width: 50%; text-align: center; vertical-align: top;">
                Supervisor Penilai,
                <div class="ttd-space"></div>
                <div class="ttd-nama">{{ $penilaian->penugasan->penilai->nama }}</div>
                <div style="font-size: 8.5pt; color: #4B5563;">NIP: {{ $penilaian->penugasan->penilai->nip ?: '-' }}</div>
            </td>
            <td style="width: 50%; text-align: center; vertical-align: top;">
                Guru yang Disupervisi,
                <div class="ttd-space"></div>
                <div class="ttd-nama">{{ $penilaian->penugasan->guru->nama }}</div>
                <div style="font-size: 8.5pt; color: #4B5563;">NIP: {{ $penilaian->penugasan->guru->nip ?: '-' }}</div>
            </td>
        </tr>
    </table>
</div>
