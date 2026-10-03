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
        ];
    }

    public function messages(): array
    {
        return [
            'periode_id.required' => 'Periode wajib dipilih.',
            'penilai_id.required' => 'Supervisor penilai wajib dipilih.',
            'guru_ids.required' => 'Pilih minimal satu guru untuk ditugaskan.',
            'guru_ids.min' => 'Pilih minimal satu guru untuk ditugaskan.',
        ];
    }
}
