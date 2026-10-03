<?php

namespace App\SuperAdmin\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateSekolahRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->is_super_admin === true;
    }

    public function rules(): array
    {
        $sekolahId = $this->route('sekolah')->id;

        return [
            'nama' => ['required', 'string', 'max:255'],
            'npsn' => [
                'nullable',
                'string',
                'max:20',
                Rule::unique('sekolah', 'npsn')->ignore($sekolahId),
            ],
            // kode TIDAK boleh diubah setelah dibuat
            'alamat' => ['nullable', 'string', 'max:1000'],
            'logo' => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:2048'],
            'status' => ['required', Rule::in(['aktif', 'nonaktif'])],
        ];
    }

    public function messages(): array
    {
        return [
            'nama.required' => 'Nama sekolah wajib diisi.',
            'npsn.unique' => 'NPSN sudah terdaftar untuk sekolah lain.',
            'logo.image' => 'File logo harus berupa gambar.',
            'logo.max' => 'Ukuran logo maksimal 2 MB.',
            'status.required' => 'Status sekolah wajib dipilih.',
            'status.in' => 'Status sekolah tidak valid.',
        ];
    }
}
