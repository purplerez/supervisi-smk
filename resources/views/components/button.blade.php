@props([
    'variant'  => 'primary',   // primary | tambah | edit | hapus | outline | ghost
    'size'     => 'md',        // sm | md | lg
    'type'     => 'button',
    'href'     => null,
    'disabled' => false,
    'icon'     => null,        // nama heroicon (opsional, untuk slot ikon)
])

@php
$variantClass = match($variant) {
    'tambah'  => 'btn-tambah',
    'edit'    => 'btn-edit',
    'hapus'   => 'btn-hapus',
    'outline' => 'btn-outline',
    'ghost'   => 'btn-ghost',
    default   => 'btn-primary',
};

$sizeClass = match($size) {
    'sm' => 'btn-sm',
    'lg' => 'btn-lg',
    default => '',
};

$classes = trim("btn {$variantClass} {$sizeClass} " . ($attributes->get('class', '')));
@endphp

@if($href)
    <a
        href="{{ $href }}"
        {{ $attributes->except('class')->merge(['class' => $classes]) }}
        @if($disabled) aria-disabled="true" tabindex="-1" @endif
    >
        {{ $slot }}
    </a>
@else
    <button
        type="{{ $type }}"
        @disabled($disabled)
        {{ $attributes->except('class')->merge(['class' => $classes]) }}
    >
        {{ $slot }}
    </button>
@endif
