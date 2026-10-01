@props([
    'title'   => null,
    'padding' => true,   // false untuk konten tanpa padding (mis. tabel penuh)
    'class'   => '',
])

<div class="card {{ $class }}" {{ $attributes->except('class') }}>
    @if($title)
        <div class="flex items-center justify-between mb-4 pb-3"
             style="border-bottom: 1px solid var(--color-border);">
            <h2 class="section-title mb-0">{{ $title }}</h2>
            @if(isset($actions))
                <div class="flex gap-2 flex-wrap">{{ $actions }}</div>
            @endif
        </div>
    @endif

    <div @class(['p-0' => !$padding])>
        {{ $slot }}
    </div>
</div>
