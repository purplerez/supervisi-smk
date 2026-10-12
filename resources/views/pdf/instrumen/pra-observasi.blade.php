@php
    $jadwal = $penilaian->penugasan->jadwalSupervisi->firstWhere('jenis_instrumen_id', $penilaian->jenis_instrumen_id);
    $tanggalPelaksanaan = $jadwal?->tanggal ? $jadwal->tanggal->translatedFormat('l, d F Y') : ($penilaian->finalized_at ? $penilaian->finalized_at->translatedFormat('l, d F Y') : now()->translatedFormat('l, d F Y'));
    $waktuPercakapan = $jadwal?->jam_mulai ? substr($jadwal->jam_mulai, 0, 5) . ($jadwal->jam_selesai ? ' - ' . substr($jadwal->jam_selesai, 0, 5) . ' WIB' : ' WIB') : ($jadwal?->tanggal ? '08:00 - selesai WIB' : '-');
    $mapJawaban = $penilaian->penilaianButir->keyBy('butir_instrumen_id');

    // Ekstrak 4 poin pra-observasi
    $isiTujuan = null;
    $isiFokus = null;
    $isiArea = null;
    $isiStrategi = null;

    if ($penilaian->versiInstrumen) {
        $allButir = $penilaian->versiInstrumen->bagian->flatMap->butir;
        foreach ($allButir as $idx => $b) {
            $catatan = $mapJawaban->get($b->id)?->catatan;
            $teks = strtolower($b->uraian);
            if (str_contains($teks, 'tujuan') || $idx === 0) {
                if ($isiTujuan === null) $isiTujuan = $catatan;
            }
            if (str_contains($teks, 'fokus') || $idx === 1) {
                if ($isiFokus === null) $isiFokus = $catatan;
            }
            if (str_contains($teks, 'area') || str_contains($teks, 'kompetensi') || $idx === 2) {
                if ($isiArea === null) $isiArea = $catatan;
            }
            if (str_contains($teks, 'strategi') || str_contains($teks, 'metode') || $idx === 3) {
                if ($isiStrategi === null) $isiStrategi = $catatan;
            }
        }
    }
@endphp

<div class="instrumen-wrapper">
    {{-- JUDUL DOKUMEN SESUAI FILE RESMI: LEMBAR CATATAN PERCAKAPAN PRA-OBSERVASI KELAS --}}
    <div class="doc-header-official">
        <h2 class="doc-title-main">LEMBAR CATATAN PERCAKAPAN PRA-OBSERVASI KELAS</h2>
    </div>

    {{-- METADATA HEADER SESUAI FORMAT RESMI --}}
    <table class="meta-official-table">
        <tr>
            <td class="meta-label">Hari/ Tanggal</td>
            <td class="meta-sep">:</td>
            <td class="meta-val">{{ $tanggalPelaksanaan }}</td>
            <td class="meta-label">Sekolah</td>
            <td class="meta-sep">:</td>
            <td class="meta-val">{{ $penilaian->sekolah->nama }}</td>
        </tr>
        <tr>
            <td class="meta-label">Nama Guru</td>
            <td class="meta-sep">:</td>
            <td class="meta-val"><strong>{{ $penilaian->penugasan->guru->nama }}</strong></td>
            <td class="meta-label">Kelas</td>
            <td class="meta-sep">:</td>
            <td class="meta-val">{{ $penilaian->penugasan->infoSupervisi?->kelas ?: '-' }}</td>
        </tr>
        <tr>
            <td class="meta-label">Mata Pelajaran</td>
            <td class="meta-sep">:</td>
            <td class="meta-val">{{ $penilaian->penugasan->infoSupervisi?->mata_pelajaran ?: '-' }}</td>
            <td class="meta-label">Waktu Percakapan</td>
            <td class="meta-sep">:</td>
            <td class="meta-val">{{ $waktuPercakapan }}</td>
        </tr>
    </table>

    {{-- 4 KOTAK BERBINGKAI SESUAI DOKUMEN RESMI --}}
    <div class="pra-box-container">
        <div class="pra-box-title">Tujuan Pembelajaran</div>
        <div class="pra-box-content">
            {!! $isiTujuan ? nl2br(e($isiTujuan)) : ($penilaian->penugasan->infoSupervisi?->cp ? nl2br(e($penilaian->penugasan->infoSupervisi->cp)) : '<em style="color: #9CA3AF;">Belum ada catatan tujuan pembelajaran.</em>') !!}
        </div>
    </div>

    <div class="pra-box-container">
        <div class="pra-box-title">Fokus Perilaku</div>
        <div class="pra-box-content">
            {!! $isiFokus ? nl2br(e($isiFokus)) : '<em style="color: #9CA3AF;">Belum ada catatan fokus perilaku.</em>' !!}
        </div>
    </div>

    <div class="pra-box-container">
        <div class="pra-box-title">Area Pengembangan yang hendak dicapai</div>
        <div class="pra-box-content">
            {!! $isiArea ? nl2br(e($isiArea)) : '<em style="color: #9CA3AF;">Belum ada catatan area pengembangan.</em>' !!}
        </div>
    </div>

    <div class="pra-box-container">
        <div class="pra-box-title">Strategi yang dipersiapkan</div>
        <div class="pra-box-content">
            {!! $isiStrategi ? nl2br(e($isiStrategi)) : '<em style="color: #9CA3AF;">Belum ada catatan strategi yang dipersiapkan.</em>' !!}
        </div>
    </div>

    {{-- CATATAN UMUM & TINDAK LANJUT SUPERVISOR --}}
    @if($penilaian->catatan || $penilaian->tindak_lanjut)
        <table class="notes-table" style="margin-top: 14px;">
            @if($penilaian->catatan)
                <tr>
                    <th style="background-color: #E6ECF7; color: #253C6D; padding: 4px 8px; font-size: 8.5pt;">Catatan Tambahan Supervisor:</th>
                </tr>
                <tr>
                    <td style="padding: 6px 8px; font-size: 8.5pt; background-color: #FAFAFA;">{!! nl2br(e($penilaian->catatan)) !!}</td>
                </tr>
            @endif
            @if($penilaian->tindak_lanjut)
                <tr>
                    <th style="background-color: #E6ECF7; color: #253C6D; padding: 4px 8px; font-size: 8.5pt;">Rencana Tindak Lanjut:</th>
                </tr>
                <tr>
                    <td style="padding: 6px 8px; font-size: 8.5pt; background-color: #FAFAFA;">{!! nl2br(e($penilaian->tindak_lanjut)) !!}</td>
                </tr>
            @endif
        </table>
    @endif

    {{-- BLOK TANDA TANGAN --}}
    <table class="ttd-table" style="margin-top: 20px;">
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
