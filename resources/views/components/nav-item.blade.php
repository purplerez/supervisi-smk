@props([
    'href'   => '#',
    'active' => false,
    'icon'   => null,   // SVG path string opsional
])

<a
    href="{{ $href }}"
    class="nav-item flex items-center gap-3 px-4 py-2.5 text-sm font-medium rounded-lg mx-1 transition-colors duration-150 {{ $active ? 'nav-item-active' : 'nav-item-inactive' }}"
    @if($active) aria-current="page" @endif
    {{ $attributes->except('class') }}
>
    @if($icon)
        <span class="shrink-0 w-5 h-5 flex items-center justify-center" aria-hidden="true">
            {!! $icon !!}
        </span>
    @else
        {{-- Placeholder ikon agar lebar konsisten --}}
        <span class="shrink-0 w-1.5 h-1.5 rounded-full ml-1.5 {{ $active ? 'bg-orange-500' : 'bg-gray-300' }}" aria-hidden="true"></span>
    @endif

    <span class="leading-snug">{{ $slot }}</span>
</a>
