<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;

class UpdatePenugasanPenilaiRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'penilai_id' => ['required', 'integer', 'exists:users,id'],
        ];
    }

    public function messages(): array
    {
        return [
            'penilai_id.required' => 'Supervisor penilai pengganti wajib dipilih.',
            'penilai_id.exists' => 'Supervisor yang dipilih tidak valid.',
        ];
    }
}
