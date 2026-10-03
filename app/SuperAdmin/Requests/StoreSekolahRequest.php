<?php

namespace App\SuperAdmin\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreSekolahRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->is_super_admin === true;
    }

    public function rules(): array
    {
        return [
            'nama' => ['required', 'string', 'max:255'],
            'npsn' => ['nullable', 'string', 'max:20', 'unique:sekolah,npsn'],
            'kode' => [
                'required',
                'string',
                'max:50',
                'alpha_dash',
                'unique:sekolah,kode',
            ],
            'alamat' => ['nullable', 'string', 'max:1000'],
            'logo' => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:2048'],
            'status' => ['required', Rule::in(['aktif', 'nonaktif'])],
        ];
    }

    public function messages(): array
    {
        return [
            'nama.required' => 'Nama sekolah wajib diisi.',
            'kode.required' => 'Kode sekolah wajib diisi.',
            'kode.alpha_dash' => 'Kode hanya boleh berisi huruf, angka, strip, dan garis bawah.',
            'kode.unique' => 'Kode sekolah sudah digunakan oleh sekolah lain.',
            'npsn.unique' => 'NPSN sudah terdaftar untuk sekolah lain.',
            'logo.image' => 'File logo harus berupa gambar.',
            'logo.max' => 'Ukuran logo maksimal 2 MB.',
            'status.required' => 'Status sekolah wajib dipilih.',
            'status.in' => 'Status sekolah tidak valid.',
        ];
    }
}
