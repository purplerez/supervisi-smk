@props([
    'status' => 'belum',  // belum | draft | final | direvisi
])

@php
$map = [
    'belum'    => ['class' => 'badge badge-belum',    'label' => 'Belum dinilai'],
    'draft'    => ['class' => 'badge badge-draft',    'label' => 'Sedang dinilai'],
    'final'    => ['class' => 'badge badge-final',    'label' => 'Selesai'],
    'direvisi' => ['class' => 'badge badge-direvisi', 'label' => 'Sedang direvisi'],
];
$info = $map[$status] ?? $map['belum'];
@endphp

<span class="{{ $info['class'] }}" role="status">
    {{ $slot->isNotEmpty() ? $slot : $info['label'] }}
</span>
