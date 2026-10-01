@props([
    'label'       => '',
    'name'        => '',
    'type'        => 'text',
    'id'          => null,
    'required'    => false,
    'hint'        => null,
    'error'       => null,   // string pesan galat
    'value'       => '',
    'placeholder' => '',
    'autocomplete'=> 'off',
])

@php
$inputId    = $id ?? $name;
$hasError   = !empty($error);
$inputClass = 'form-input ' . ($hasError ? 'form-input-error' : '');
@endphp

<div class="mb-5">
    @if($label)
        <label for="{{ $inputId }}" class="form-label">
            {{ $label }}
            @if($required) <span class="text-red-600 ml-0.5" aria-hidden="true">*</span> @endif
        </label>
    @endif

    <input
        type="{{ $type }}"
        id="{{ $inputId }}"
        name="{{ $name }}"
        value="{{ old($name, $value) }}"
        placeholder="{{ $placeholder }}"
        autocomplete="{{ $autocomplete }}"
        class="{{ $inputClass }}"
        @if($required) required @endif
        @if($hasError) aria-describedby="{{ $inputId }}-error" aria-invalid="true" @endif
        {{ $attributes->except(['class', 'id', 'name', 'type', 'value', 'placeholder', 'autocomplete']) }}
    >

    @if($hint && !$hasError)
        <p class="form-hint">{{ $hint }}</p>
    @endif

    @if($hasError)
        <p id="{{ $inputId }}-error" class="form-error" role="alert">
            <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true" class="shrink-0 mt-0.5">
                <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7 4a1 1 0 11-2 0 1 1 0 012 0zm-1-9a1 1 0 00-1 1v4a1 1 0 102 0V6a1 1 0 00-1-1z" clip-rule="evenodd"/>
            </svg>
            {{ $error }}
        </p>
    @endif
</div>
