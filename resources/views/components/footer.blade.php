@props([
    'containerClass' => 'max-w-7xl',
])

<footer {{ $attributes->merge(['class' => 'mt-auto border-t py-4 px-4 sm:px-6 lg:px-8 bg-white']) }}
        style="border-color: var(--color-border, #E8E0D2);"
        role="contentinfo">
    <div class="{{ $containerClass }} mx-auto flex flex-col sm:flex-row items-center justify-between gap-3 text-sm"
         style="color: var(--color-muted, #5B6475);">
        <p class="text-center sm:text-left">
            &copy; {{ date('Y') }} {{ config('app.name', 'Supervisi Guru') }}. Hak Cipta Dilindungi.
        </p>

        <div class="flex flex-wrap items-center justify-center sm:justify-end gap-x-4 gap-y-2 text-center sm:text-right">
            <span class="font-medium" style="color: var(--color-ink, #1F2937);">
                kebun kode - PPLG SMKN 1 Banyuwangi
            </span>
            @if($slot->isNotEmpty())
                <span class="hidden sm:inline text-gray-300" aria-hidden="true">|</span>
                {{ $slot }}
            @endif
        </div>
    </div>
</footer>
