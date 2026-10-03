<x-app-layout :namaSekolah="$sekolah->nama" title="Rapor Supervisi Guru">

    <x-page-header
        title="Rapor Hasil Supervisi"
        subtitle="Rincian hasil penilaian supervisi akademik resmi pada periode {{ $periode->nama }}."
    >
        <x-slot:actions>
            <div class="flex items-center gap-2">
                <x-button variant="sekunder" href="{{ route('guru.dashboard', ['kode' => $sekolah->kode]) }}">
                    &larr; Dasbor Utama
                </x-button>
                <x-button variant="sekunder" href="{{ route('guru.riwayat', ['kode' => $sekolah->kode]) }}">
                    Riwayat Periode
                </x-button>
            </div>
        </x-slot:actions>
    </x-page-header>

    {{-- Pemilih Periode (Jika Guru Mengikuti Lebih dari 1 Periode) --}}
    @if($riwayatPenugasan->count() > 1)
        <x-card class="p-4 mb-6">
            <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3">
                <label for="pilih_periode" class="text-xs font-bold uppercase tracking-wider" style="color: var(--color-navy-900);">
                    Pilih Periode Rapor:
                </label>
                <div class="flex gap-2">
                    <select
                        id="pilih_periode"
                        class="form-input text-sm"
                        onchange="window.location.href = '{{ url('/s/'.$sekolah->kode.'/guru/rapor') }}/' + this.value"
                    >
                        @foreach($riwayatPenugasan as $item)
                            <option value="{{ $item->periode_id }}" {{ $item->periode_id === $periode->id ? 'selected' : '' }}>
                                {{ $item->periode->nama }} ({{ $item->periode->tahun_ajaran }})
                            </option>
                        @endforeach
                    </select>
                </div>
            </div>
        </x-card>
    @endif

    {{-- Kartu Dokumen Rapor --}}
    <x-card class="p-6 md:p-10 mb-8 max-w-4xl mx-auto shadow-md">
        {{-- KOP DOKUMEN --}}
        <div class="text-center pb-6 border-b-2 border-gray-900 mb-6">
            <h2 class="text-xl font-bold uppercase tracking-wider text-gray-900">
                {{ $sekolah->nama }}
            </h2>
            @if($sekolah->alamat)
                <p class="text-xs text-gray-600 mt-0.5">{{ $sekolah->alamat }}</p>
            @endif
            <div class="mt-4 pt-3 border-t border-gray-300">
                <h3 class="text-base font-bold uppercase tracking-wider text-gray-800">
                    RAPOR HASIL SUPERVISI AKADEMIK GURU
                </h3>
                <p class="text-xs font-medium text-gray-600 mt-0.5">
                    Periode: {{ $periode->nama }} (Tahun Ajaran {{ $periode->tahun_ajaran }}{{ $periode->semester ? ', Semester '.$periode->semester : '' }})
                </p>
            </div>
        </div>

        {{-- IDENTITAS SUBJEK SUPERVISI --}}
        <div class="grid grid-cols-1 md:grid-cols-2 gap-4 p-4 rounded-xl mb-6 text-xs" style="background-color: var(--color-cream); border: 1px solid var(--color-border);">
            <div class="space-y-1.5">
                <div class="flex">
                    <span class="w-32 font-semibold text-gray-600">Nama Guru</span>
                    <span class="w-3">:</span>
                    <span class="font-bold text-gray-900">{{ $guru->nama }}</span>
                </div>
                <div class="flex">
                    <span class="w-32 font-semibold text-gray-600">NIP / NUPTK</span>
                    <span class="w-3">:</span>
                    <span>{{ $guru->nip ?: ($guru->nuptk ?: '-') }}</span>
                </div>
                <div class="flex">
                    <span class="w-32 font-semibold text-gray-600">Mata Pelajaran</span>
                    <span class="w-3">:</span>
                    <span>{{ $penugasan->infoSupervisi?->mata_pelajaran ?? '-' }}</span>
                </div>
                <div class="flex">
                    <span class="w-32 font-semibold text-gray-600">Kelas / Fase</span>
                    <span class="w-3">:</span>
                    <span>
                        {{ $penugasan->infoSupervisi?->kelas ?? '-' }}
                        @if($penugasan->infoSupervisi?->fase)
                            ({{ $penugasan->infoSupervisi->fase }})
                        @endif
                    </span>
                </div>
            </div>

            <div class="space-y-1.5">
                <div class="flex">
                    <span class="w-36 font-semibold text-gray-600">Supervisor (Penilai)</span>
                    <span class="w-3">:</span>
                    <span class="font-bold text-gray-900">{{ $penugasan->penilai->nama }}</span>
                </div>
                <div class="flex">
                    <span class="w-36 font-semibold text-gray-600">NIP Supervisor</span>
                    <span class="w-3">:</span>
                    <span>{{ $penugasan->penilai->nip ?: ($penugasan->penilai->nuptk ?: '-') }}</span>
                </div>
                <div class="flex">
                    <span class="w-36 font-semibold text-gray-600">Status Supervisi</span>
                    <span class="w-3">:</span>
                    <span class="font-bold {{ $progres['is_selesai'] ? 'text-green-800' : 'text-amber-800' }}">
                        {{ $progres['label'] }}
                    </span>
                </div>
                <div class="flex">
                    <span class="w-36 font-semibold text-gray-600">Kepala Sekolah</span>
                    <span class="w-3">:</span>
                    <span>{{ $kepalaSekolah?->nama ?? '-' }}</span>
                </div>
            </div>
        </div>

        {{-- TABEL HASIL PENILAIAN PER INSTRUMEN --}}
        <div class="overflow-x-auto mb-6">
            <table class="w-full text-left border-collapse text-xs border border-gray-300">
                <thead>
                    <tr class="border-b font-bold uppercase tracking-wider bg-gray-100 text-gray-800">
                        <th class="py-2.5 px-3 w-10 text-center border-r border-gray-300">No</th>
                        <th class="py-2.5 px-3 border-r border-gray-300">Instrumen Supervisi</th>
                        <th class="py-2.5 px-3 w-28 text-center border-r border-gray-300">Status</th>
                        <th class="py-2.5 px-3 w-20 text-center border-r border-gray-300">Nilai</th>
                        <th class="py-2.5 px-3 w-20 text-center border-r border-gray-300">Predikat</th>
                        <th class="py-2.5 px-3 border-r border-gray-300">Catatan Supervisor</th>
                        <th class="py-2.5 px-3">Rekomendasi Tindak Lanjut</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-200">
                    @php
                        $urutanKunci = ['perencanaan', 'kbm', 'pengelolaan_kelas', 'administrasi'];
                        $totalNilaiFinal = 0;
                        $jumlahFinal = 0;
                    @endphp

                    @foreach($urutanKunci as $idx => $kodeJenis)
                        @php
                            $penilaian = $penugasan->penilaian->firstWhere('jenisInstrumen.kode', $kodeJenis);
                            $namaInstrumen = $penilaian?->jenisInstrumen?->nama ?? ucfirst(str_replace('_', ' ', $kodeJenis));
                            $status = $penilaian?->status ?? 'belum';

                            if ($status === 'final' && $penilaian && $penilaian->nilai !== null) {
                                $totalNilaiFinal += (float) $penilaian->nilai;
                                $jumlahFinal++;
                            }
                        @endphp
                        <tr class="hover:bg-gray-50/50">
                            <td class="py-3 px-3 text-center font-medium text-gray-500 border-r border-gray-300">
                                {{ $idx + 1 }}
                            </td>
                            <td class="py-3 px-3 font-semibold text-gray-900 border-r border-gray-300">
                                {{ $namaInstrumen }}
                            </td>
                            <td class="py-3 px-3 text-center border-r border-gray-300">
                                @if($status === 'final')
                                    <span class="inline-flex items-center px-2 py-0.5 rounded text-[11px] font-bold" style="background-color: #E8F5E9; color: #1B5E20;">
                                        Selesai
                                    </span>
                                @elseif($status === 'direvisi')
                                    <span class="inline-flex items-center px-2 py-0.5 rounded text-[11px] font-bold" style="background-color: #FDE8D4; color: #8A3B00;">
                                        Sedang direvisi
                                    </span>
                                @elseif($status === 'draft')
                                    <span class="inline-flex items-center px-2 py-0.5 rounded text-[11px] font-bold" style="background-color: #FFF3D6; color: #8A5A00;">
                                        Sedang dinilai
                                    </span>
                                @else
                                    <span class="inline-flex items-center px-2 py-0.5 rounded text-[11px] font-bold" style="background-color: #EEF0F4; color: #4B5563;">
                                        Belum dinilai
                                    </span>
                                @endif
                            </td>

                            {{-- Kolom Nilai: HANYA TAMPIL JIKA STATUS FINAL --}}
                            <td class="py-3 px-3 text-center font-bold border-r border-gray-300">
                                @if($status === 'final')
                                    <span class="text-sm text-green-700">
                                        {{ number_format($penilaian->nilai, 2, ',', '.') }}
                                    </span>
                                @elseif($status === 'direvisi')
                                    <span class="text-[11px] text-amber-800 italic">Sedang direvisi</span>
                                @elseif($status === 'draft')
                                    <span class="text-[11px] text-amber-700 italic">Sedang dinilai</span>
                                @else
                                    <span class="text-[11px] text-gray-400 italic">&mdash;</span>
                                @endif
                            </td>

                            {{-- Predikat: HANYA TAMPIL JIKA STATUS FINAL --}}
                            <td class="py-3 px-3 text-center font-semibold border-r border-gray-300">
                                @if($status === 'final')
                                    <span class="text-xs px-2 py-0.5 rounded font-bold" style="background-color: var(--color-navy-50); color: var(--color-navy-900);">
                                        {{ $penilaian->predikat ?? '-' }}
                                    </span>
                                @else
                                    <span class="text-gray-400">&mdash;</span>
                                @endif
                            </td>

                            {{-- Catatan --}}
                            <td class="py-3 px-3 text-gray-700 border-r border-gray-300">
                                @if($status === 'final')
                                    {{ $penilaian->catatan ?: '-' }}
                                @else
                                    <span class="text-gray-400 italic">Tersedia setelah final</span>
                                @endif
                            </td>

                            {{-- Tindak Lanjut --}}
                            <td class="py-3 px-3 text-gray-700">
                                @if($status === 'final')
                                    {{ $penilaian->tindak_lanjut ?: '-' }}
                                @else
                                    <span class="text-gray-400 italic">Tersedia setelah final</span>
                                @endif
                            </td>
                        </tr>
                    @endforeach
                </tbody>
                @if($progres['is_selesai'] && $jumlahFinal === 4)
                    @php
                        $rataRata = $totalNilaiFinal / 4;
                    @endphp
                    <tfoot>
                        <tr class="bg-green-50/70 border-t-2 border-gray-400 font-bold text-gray-900">
                            <td colspan="3" class="py-3 px-3 text-right uppercase tracking-wider border-r border-gray-300">
                                Rata-Rata Nilai Akhir Supervisi:
                            </td>
                            <td class="py-3 px-3 text-center text-sm text-green-800 border-r border-gray-300">
                                {{ number_format($rataRata, 2, ',', '.') }}
                            </td>
                            <td class="py-3 px-3 text-center border-r border-gray-300">
                                @php
                                    $predikatAkhir = 'C';
                                    if ($rataRata >= 86) $predikatAkhir = 'A';
                                    elseif ($rataRata >= 76) $predikatAkhir = 'B';
                                    elseif ($rataRata < 56) $predikatAkhir = 'D';
                                @endphp
                                <span class="px-2 py-0.5 rounded text-xs font-bold bg-green-200 text-green-900">
                                    {{ $predikatAkhir }}
                                </span>
                            </td>
                            <td colspan="2" class="py-3 px-3 text-xs font-medium text-gray-600">
                                Rapor supervisi lengkap (4/4 tuntas).
                            </td>
                        </tr>
                    </tfoot>
                @endif
            </table>
        </div>

        {{-- Keterangan / Penjelasan jika belum selesai --}}
        @if(!$progres['is_selesai'])
            <div class="p-3 rounded-lg border border-amber-200 bg-amber-50 text-xs text-amber-900 mb-6">
                <strong>Catatan Nilai Resmi:</strong>
                Sesuai dengan ketentuan supervisi akademik, nilai numerik dan predikat resmi hanya ditampilkan apabila penilaian pada instrumen tersebut telah berstatus <strong>Final (Selesai)</strong>.
            </div>
        @endif

        {{-- BLOK TANDA TANGAN RESMI --}}
        <div class="grid grid-cols-2 gap-8 pt-8 mt-4 text-xs border-t border-gray-200 text-center">
            <div>
                <p class="text-gray-500 mb-1">Guru yang Disupervisi,</p>
                <div class="h-16"></div>
                <p class="font-bold underline text-gray-900">{{ $guru->nama }}</p>
                @if($guru->nip)
                    <p class="text-gray-500">NIP. {{ $guru->nip }}</p>
                @endif
            </div>

            <div>
                <p class="text-gray-500 mb-1">Supervisor Penilai,</p>
                <div class="h-16"></div>
                <p class="font-bold underline text-gray-900">{{ $penugasan->penilai->nama }}</p>
                @if($penugasan->penilai->nip)
                    <p class="text-gray-500">NIP. {{ $penugasan->penilai->nip }}</p>
                @endif
            </div>
        </div>
    </x-card>

</x-app-layout>
