@props([
    'id'           => 'modal-konfirmasi',
    'title'        => 'Konfirmasi',
    'pesan'        => 'Apakah Anda yakin ingin melanjutkan tindakan ini?',
    'labelKonfirm' => 'Ya, Lanjutkan',
    'labelBatal'   => 'Batal',
    'variantKonfirm' => 'hapus',   // primary | tambah | edit | hapus
    'action'       => null,        // URL form action (jika dipakai sebagai form)
    'method'       => 'POST',
])

{{--
    Penggunaan via Alpine.js x-data + x-show.
    Contoh pemicu:
        @click="$dispatch('buka-modal', { id: '{{ $id }}' })"

    Di parent, pasang:
        <div x-data="{ buka: false }" @buka-modal.window="if($event.detail.id==='{{ $id }}') buka=true">
    Atau gunakan id unik dan $dispatch.
--}}

<div
    id="{{ $id }}"
    x-data="{ terbuka: false }"
    @buka-modal-{{ $id }}.window="terbuka = true"
    x-show="terbuka"
    x-cloak
    class="fixed inset-0 z-50 flex items-center justify-center p-4"
    role="dialog"
    aria-modal="true"
    aria-labelledby="{{ $id }}-judul"
    aria-describedby="{{ $id }}-pesan"
>
    {{-- Overlay --}}
    <div
        class="absolute inset-0 bg-black/40 backdrop-blur-sm"
        @click="terbuka = false"
        aria-hidden="true"
        x-show="terbuka"
        x-transition:enter="transition ease-out duration-200"
        x-transition:enter-start="opacity-0"
        x-transition:enter-end="opacity-100"
        x-transition:leave="transition ease-in duration-150"
        x-transition:leave-start="opacity-100"
        x-transition:leave-end="opacity-0"
    ></div>

    {{-- Panel --}}
    <div
        class="relative w-full max-w-md"
        style="background: #ffffff; border-radius: 16px; box-shadow: 0 20px 60px rgb(0 0 0 / 0.20); padding: 2rem;"
        x-show="terbuka"
        x-transition:enter="transition ease-out duration-200"
        x-transition:enter-start="opacity-0 scale-95"
        x-transition:enter-end="opacity-100 scale-100"
        x-transition:leave="transition ease-in duration-150"
        x-transition:leave-start="opacity-100 scale-100"
        x-transition:leave-end="opacity-0 scale-95"
        @click.stop
    >
        {{-- Ikon peringatan --}}
        <div class="flex items-center gap-3 mb-4">
            @if($variantKonfirm === 'hapus')
                <div class="flex-shrink-0 w-10 h-10 rounded-full flex items-center justify-center"
                     style="background-color: #FEECEC;">
                    <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 20 20"
                         fill="currentColor" style="color: #C62828;" aria-hidden="true">
                        <path fill-rule="evenodd"
                              d="M9 2a1 1 0 00-.894.553L7.382 4H4a1 1 0 000 2v10a2 2 0 002 2h8a2 2 0 002-2V6a1 1 0 100-2h-3.382l-.724-1.447A1 1 0 0011 2H9zM7 8a1 1 0 012 0v6a1 1 0 11-2 0V8zm5-1a1 1 0 00-1 1v6a1 1 0 102 0V8a1 1 0 00-1-1z"
                              clip-rule="evenodd"/>
                    </svg>
                </div>
            @elseif($variantKonfirm === 'primary' || $variantKonfirm === 'tambah')
                <div class="flex-shrink-0 w-10 h-10 rounded-full flex items-center justify-center"
                     style="background-color: #E6ECF7;">
                    <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 20 20"
                         fill="currentColor" style="color: #253C6D;" aria-hidden="true">
                        <path fill-rule="evenodd"
                              d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7-4a1 1 0 11-2 0 1 1 0 012 0zM9 9a1 1 0 000 2v3a1 1 0 001 1h1a1 1 0 100-2v-3a1 1 0 00-1-1H9z"
                              clip-rule="evenodd"/>
                    </svg>
                </div>
            @endif

            <h2 id="{{ $id }}-judul" class="section-title mb-0">{{ $title }}</h2>
        </div>

        <p id="{{ $id }}-pesan" class="mb-6 leading-relaxed" style="color: var(--color-muted);">
            {{ $pesan }}
        </p>

        @if($slot->isNotEmpty())
            <div class="mb-6">{{ $slot }}</div>
        @endif

        <div class="flex justify-end gap-3 flex-wrap">
            <button
                type="button"
                class="btn btn-ghost"
                @click="terbuka = false"
                id="{{ $id }}-batal"
            >
                {{ $labelBatal }}
            </button>

            @if($action)
                <form method="POST" action="{{ $action }}" id="{{ $id }}-form" class="inline">
                    @csrf
                    @if(strtoupper($method) !== 'POST')
                        @method($method)
                    @endif
                    <button type="submit" class="btn btn-{{ $variantKonfirm }}" id="{{ $id }}-konfirm">
                        {{ $labelKonfirm }}
                    </button>
                </form>
            @else
                <button
                    type="button"
                    class="btn btn-{{ $variantKonfirm }}"
                    id="{{ $id }}-konfirm"
                    @click="terbuka = false; $dispatch('konfirmasi-{{ $id }}')"
                >
                    {{ $labelKonfirm }}
                </button>
            @endif
        </div>

        {{-- Tombol tutup --}}
        <button
            type="button"
            class="absolute top-4 right-4 touch-target rounded-lg"
            style="color: var(--color-muted);"
            @click="terbuka = false"
            aria-label="Tutup dialog"
        >
            <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true">
                <path fill-rule="evenodd" d="M4.293 4.293a1 1 0 011.414 0L10 8.586l4.293-4.293a1 1 0 111.414 1.414L11.414 10l4.293 4.293a1 1 0 01-1.414 1.414L10 11.414l-4.293 4.293a1 1 0 01-1.414-1.414L8.586 10 4.293 5.707a1 1 0 010-1.414z" clip-rule="evenodd"/>
            </svg>
        </button>
    </div>
</div>
