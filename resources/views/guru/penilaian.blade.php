<x-app-layout :namaSekolah="$sekolah->nama" :title="'Pengisian ' . $penilaian->jenisInstrumen->nama">
    <div class="mb-6">
        <a href="{{ route('guru.dashboard', ['kode' => $sekolah->kode]) }}" class="inline-flex items-center gap-2 text-sm font-semibold text-navy-700 hover:text-navy-900 transition">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18" />
            </svg>
            Kembali ke Dasbor Supervisi Saya
        </a>
    </div>

    <x-page-header
        :title="'Pengisian Mandiri: ' . $penilaian->jenisInstrumen->nama"
        :subtitle="'Periode ' . $penilaian->penugasan->periode->nama . ' | Supervisor: ' . $penilaian->penugasan->penilai->nama"
    />

    <livewire:guru.form-penilaian-guru :penilaian="$penilaian" />
</x-app-layout>
