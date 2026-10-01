@props([
    'title'   => 'Tidak ada data',
    'message' => null,
    'icon'    => null,
])

<div class="flex flex-col items-center justify-center py-16 px-4 text-center">
    {{-- Ikon dekoratif --}}
    <div class="mb-4 w-16 h-16 rounded-full flex items-center justify-center"
         style="background-color: var(--color-navy-50);">
        @if($icon)
            {!! $icon !!}
        @else
            <svg xmlns="http://www.w3.org/2000/svg" width="32" height="32" viewBox="0 0 20 20"
                 fill="currentColor" style="color: var(--color-navy-700);" aria-hidden="true">
                <path fill-rule="evenodd" d="M3 4a1 1 0 011-1h12a1 1 0 110 2H4a1 1 0 01-1-1zm0 4a1 1 0 011-1h12a1 1 0 110 2H4a1 1 0 01-1-1zm0 4a1 1 0 011-1h12a1 1 0 110 2H4a1 1 0 01-1-1zm0 4a1 1 0 011-1h12a1 1 0 110 2H4a1 1 0 01-1-1z" clip-rule="evenodd"/>
            </svg>
        @endif
    </div>

    <h3 class="text-base font-semibold mb-1" style="color: var(--color-ink);">{{ $title }}</h3>

    @if($message)
        <p class="text-sm max-w-sm" style="color: var(--color-muted);">{{ $message }}</p>
    @endif

    @if(isset($actions))
        <div class="mt-6">{{ $actions }}</div>
    @endif
</div>
