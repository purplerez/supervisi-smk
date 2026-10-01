@props([
    'title'    => '',
    'subtitle' => null,
    'back'     => null,   // URL untuk tombol kembali
    'backLabel'=> 'Kembali',
])

<div class="mb-6">
    @if($back)
        <a href="{{ $back }}"
           class="inline-flex items-center gap-1 text-sm font-medium mb-3 no-underline"
           style="color: var(--color-navy-700);">
            <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 20 20"
                 fill="currentColor" aria-hidden="true">
                <path fill-rule="evenodd"
                      d="M12.707 5.293a1 1 0 010 1.414L9.414 10l3.293 3.293a1 1 0 01-1.414 1.414l-4-4a1 1 0 010-1.414l4-4a1 1 0 011.414 0z"
                      clip-rule="evenodd"/>
            </svg>
            {{ $backLabel }}
        </a>
    @endif

    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3">
        <div>
            <h1 class="page-title">{{ $title }}</h1>
            @if($subtitle)
                <p class="text-muted text-sm mt-1">{{ $subtitle }}</p>
            @endif
        </div>

        @if(isset($actions))
            <div class="flex gap-2 flex-wrap shrink-0">
                {{ $actions }}
            </div>
        @endif
    </div>
</div>
