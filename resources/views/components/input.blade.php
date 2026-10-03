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
$isPassword = $type === 'password';
$inputClass = 'form-input ' . ($hasError ? 'form-input-error' : '') . ($isPassword ? ' pr-12' : '');
@endphp

<div class="mb-5" @if($isPassword) x-data="{ showPass: false }" @endif>
    @if($label)
        <label for="{{ $inputId }}" class="form-label">
            {{ $label }}
            @if($required) <span class="text-red-600 ml-0.5" aria-hidden="true">*</span> @endif
        </label>
    @endif

    <div class="relative">
        <input
            @if($isPassword)
                type="password"
                :type="showPass ? 'text' : 'password'"
            @else
                type="{{ $type }}"
            @endif
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

        @if($isPassword)
            {{-- Tombol tampilkan/sembunyikan kata sandi --}}
            <button
                type="button"
                @click="showPass = !showPass"
                :aria-label="showPass ? 'Sembunyikan kata sandi' : 'Tampilkan kata sandi'"
                class="absolute inset-y-0 right-0 flex items-center px-3.5 text-gray-400 hover:text-gray-600 focus:outline-none focus:text-gray-700 transition-colors cursor-pointer"
                tabindex="-1"
            >
                {{-- Ikon Mata (password tersembunyi) --}}
                <svg x-show="!showPass" xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true">
                    <path d="M10 12a2 2 0 100-4 2 2 0 000 4z"/>
                    <path fill-rule="evenodd" d="M.458 10C1.732 5.943 5.522 3 10 3s8.268 2.943 9.542 7c-1.274 4.057-5.064 7-9.542 7S1.732 14.057.458 10zM14 10a4 4 0 11-8 0 4 4 0 018 0z" clip-rule="evenodd"/>
                </svg>
                {{-- Ikon Mata Tercoret (password terlihat) --}}
                <svg x-show="showPass" xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true" style="display:none">
                    <path fill-rule="evenodd" d="M3.707 2.293a1 1 0 00-1.414 1.414l14 14a1 1 0 001.414-1.414l-1.473-1.473A10.014 10.014 0 0019.542 10C18.268 5.943 14.478 3 10 3a9.958 9.958 0 00-4.512 1.074l-1.78-1.781zm4.261 4.26l1.514 1.515a2.003 2.003 0 012.45 2.45l1.514 1.514a4 4 0 00-5.478-5.478z" clip-rule="evenodd"/>
                    <path d="M12.454 16.697L9.75 13.992a4 4 0 01-3.742-3.741L2.335 6.578A9.98 9.98 0 00.458 10c1.274 4.057 5.064 7 9.542 7 .847 0 1.669-.105 2.454-.303z"/>
                </svg>
            </button>
        @endif
    </div>

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
