@php
    $mapJawaban = $penilaian->penilaianButir->keyBy('butir_instrumen_id');
    $buktiMap = $penilaian->data_kualitatif['bukti'] ?? [];
    $nomorUrut = 1;
@endphp

<div class="instrumen-wrapper">
    <div class="doc-header-official">
        <h2 class="doc-title-main">Instrumen Observasi</h2>
        <div style="font-size: 11pt; font-weight: bold; color: #1F2937; margin-top: 2px;">
            Implementasi Dan Refleksi Perencanaan Pembelajaran
        </div>
        <div style="font-size: 8.5pt; font-style: italic; color: #4B5563; margin-top: 4px;">
            Berikan umpan balik terhadap praktik yang telah dilakukan dengan menggunakan instrumen berikut!
        </div>
    </div>

    {{-- IDENTITAS SUBJEK DAN PEMBERI UMPAN BALIK --}}
    <table class="meta-official-table" style="margin-top: 8px;">
        <tr>
            <td class="meta-label">Guru Model</td>
            <td class="meta-sep">:</td>
            <td class="meta-val"><strong>{{ $penilaian->penugasan->guru->nama }}</strong></td>
            <td class="meta-label">Mata Pelajaran</td>
            <td class="meta-sep">:</td>
            <td class="meta-val">{{ $penilaian->penugasan->infoSupervisi?->mata_pelajaran ?: '-' }}</td>
        </tr>
        <tr>
            <td class="meta-label">Pemberi Umpan Balik</td>
            <td class="meta-sep">:</td>
            <td class="meta-val"><strong>{{ $penilaian->penugasan->penilai->nama }}</strong></td>
            <td class="meta-label">Kelas / Semester</td>
            <td class="meta-sep">:</td>
            <td class="meta-val">{{ $penilaian->penugasan->infoSupervisi?->kelas ?: '-' }} / {{ $penilaian->penugasan->infoSupervisi?->semester ?: ($penilaian->penugasan->periode->semester ?: '-') }}</td>
        </tr>
        <tr>
            <td class="meta-label">NIP Guru Model</td>
            <td class="meta-sep">:</td>
            <td class="meta-val">{{ $penilaian->penugasan->guru->nip ?: '-' }}</td>
            <td class="meta-label">Tanggal Percakapan</td>
            <td class="meta-sep">:</td>
            <td class="meta-val">{{ $penilaian->finalized_at?->translatedFormat('d F Y') ?: now()->translatedFormat('d F Y') }}</td>
        </tr>
    </table>

    {{-- TABEL 1: PENGAMATAN IMPLEMENTASI DAN ASPEK PEMBELAJARAN --}}
    <table class="content-table">
        <thead>
            <tr>
                <th style="width: 5%;">No.</th>
                <th style="width: 50%;">Aspek yang diamati</th>
                <th style="width: 22%;">Bukti Pembelajaran</th>
                <th style="width: 23%;">Catatan</th>
            </tr>
        </thead>
        <tbody>
            @if($penilaian->versiInstrumen)
                @php
                    $bagianReguler = $penilaian->versiInstrumen->bagian->filter(function($b) {
                        return !str_contains(strtolower($b->judul), 'refleksi & umpan balik') && !str_contains(strtolower($b->judul), 'refleksi dialogis') && $b->kategori !== 'Kualitatif';
                    })->sortBy('urutan');

                    $bagianRefleksi = $penilaian->versiInstrumen->bagian->filter(function($b) {
                        return str_contains(strtolower($b->judul), 'refleksi & umpan balik') || str_contains(strtolower($b->judul), 'refleksi dialogis') || $b->kategori === 'Kualitatif';
                    })->sortBy('urutan');
                @endphp

                @foreach($bagianReguler as $bagian)
                    <tr class="bagian-header">
                        <td colspan="4">{{ $bagian->judul }}</td>
                    </tr>
                    @foreach($bagian->butir->sortBy('urutan') as $butir)
                        @php
                            $jawaban = $mapJawaban->get($butir->id);
                            $buktiTeks = $buktiMap[$butir->id] ?? null;
                        @endphp
                        <tr>
                            <td class="text-center">{{ $nomorUrut++ }}</td>
                            <td style="line-height: 1.35;">{!! nl2br(e($butir->uraian)) !!}</td>
                            <td style="font-size: 8pt; color: #1F2937;">{{ $buktiTeks ?: '-' }}</td>
                            <td style="font-size: 8pt; color: #374151;">{{ $jawaban?->catatan ?: '-' }}</td>
                        </tr>
                    @endforeach
                @endforeach
            @endif
        </tbody>
    </table>

    {{-- TABEL 2: PERTANYAAN REFLEKSI & ANALISIS DIALOGIS --}}
    @if(isset($bagianRefleksi) && $bagianRefleksi->isNotEmpty())
        <div style="margin-top: 12px; margin-bottom: 6px; font-weight: bold; font-size: 9.5pt; color: #253C6D;">
            Refleksi & Catatan Umpan Balik:
        </div>
        <table class="content-table">
            <thead>
                <tr>
                    <th style="width: 5%;">No.</th>
                    <th style="width: 45%;">Fokus Refleksi & Umpan Balik</th>
                    <th style="width: 50%;">Catatan Percakapan / Hasil Refleksi</th>
                </tr>
            </thead>
            <tbody>
                @foreach($bagianRefleksi as $bagian)
                    @foreach($bagian->butir->sortBy('urutan') as $butir)
                        @php
                            $jawaban = $mapJawaban->get($butir->id);
                        @endphp
                        <tr>
                            <td class="text-center">{{ $nomorUrut++ }}</td>
                            <td style="font-weight: 600; line-height: 1.35; background-color: #FAFAFA;">
                                {!! nl2br(e($butir->uraian)) !!}
                            </td>
                            <td style="font-size: 8.5pt; color: #1F2937;">
                                {!! $jawaban?->catatan ? nl2br(e($jawaban->catatan)) : '<em style="color:#9CA3AF;">Belum ada catatan.</em>' !!}
                            </td>
                        </tr>
                    @endforeach
                @endforeach
            </tbody>
        </table>
    @endif

    {{-- CATATAN UMUM DAN TINDAK LANJUT SUPERVISOR --}}
    @if($penilaian->catatan || $penilaian->tindak_lanjut)
        <table class="notes-table">
            @if($penilaian->catatan)
                <tr>
                    <th>Catatan Umpan Balik / Rekomendasi Keseluruhan:</th>
                </tr>
                <tr>
                    <td>{!! nl2br(e($penilaian->catatan)) !!}</td>
                </tr>
            @endif
            @if($penilaian->tindak_lanjut)
                <tr>
                    <th>Rencana Tindak Lanjut Perbaikan:</th>
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
                Guru Model,
                <div class="ttd-space"></div>
                <div class="ttd-nama">{{ $penilaian->penugasan->guru->nama }}</div>
                <div style="font-size: 8.5pt; color: #4B5563;">NIP: {{ $penilaian->penugasan->guru->nip ?: '-' }}</div>
            </td>
            <td style="width: 50%; text-align: center; vertical-align: top;">
                {{ $penilaian->sekolah->alamat ? explode(',', $penilaian->sekolah->alamat)[0] : 'Di Tempat' }}, 
                {{ $penilaian->finalized_at?->translatedFormat('d F Y') ?: now()->translatedFormat('d F Y') }}<br>
                Pemberi Umpan Balik (Supervisor),
                <div class="ttd-space"></div>
                <div class="ttd-nama">{{ $penilaian->penugasan->penilai->nama }}</div>
                <div style="font-size: 8.5pt; color: #4B5563;">NIP: {{ $penilaian->penugasan->penilai->nip ?: '-' }}</div>
            </td>
        </tr>
    </table>
</div>
