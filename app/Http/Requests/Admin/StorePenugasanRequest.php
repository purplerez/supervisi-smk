<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;

class StorePenugasanRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'periode_id' => ['required', 'integer', 'exists:periode,id'],
            'penilai_id' => ['required', 'integer', 'exists:users,id'],
            'guru_ids' => ['required', 'array', 'min:1'],
            'guru_ids.*' => ['required', 'integer', 'exists:users,id'],
            'redirect_to' => ['nullable', 'string', 'in:dashboard,penugasan'],
        ];
    }

    public function messages(): array
    {
        return [
            'periode_id.required' => 'Periode supervisi wajib dipilih.',
            'periode_id.exists' => 'Periode supervisi tidak valid.',
            'penilai_id.required' => 'Supervisor penilai wajib dipilih.',
            'penilai_id.exists' => 'Supervisor penilai tidak valid.',
            'guru_ids.required' => 'Pilih minimal satu guru untuk didaftarkan.',
            'guru_ids.min' => 'Pilih minimal satu guru untuk didaftarkan.',
            'guru_ids.*.exists' => 'Data guru yang dipilih tidak valid.',
        ];
    }
}
