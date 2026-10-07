@props([
    'href'   => '#',
    'active' => false,
    'icon'   => null,
    'badge'  => null,
    'download' => false,
])

<a
    href="{{ $href }}"
    @if($download) download @endif
    class="nav-item group flex items-center justify-between px-4 py-3 my-1 text-sm font-normal rounded-2xl no-underline transition-all duration-150 {{ $active ? 'nav-item-active' : 'nav-item-inactive' }}"
    @if($active) aria-current="page" @endif
    {{ $attributes->except('class') }}
>
    <div class="flex items-center gap-3.5 min-w-0">
        @if($icon)
            <span class="shrink-0 w-5 h-5 flex items-center justify-center transition-colors" aria-hidden="true">
                {!! $icon !!}
            </span>
        @endif

        {{ $slot }}
    </div>

    @if($badge)
        <span class="px-2.5 py-0.5 text-xs font-medium rounded-full bg-[#f3f4f6] text-[#374151] border border-gray-200/60 shrink-0 ml-2">
            {{ $badge }}
        </span>
    @endif
</a>



