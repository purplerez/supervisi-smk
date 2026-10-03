<?php

namespace App\Http\Requests\Admin;

use App\Tenant\TenantContext;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreGuruRequest extends FormRequest
{
    public function authorize(): bool
    {
        return auth()->check() && auth()->user()->hasRole('admin');
    }

    public function rules(): array
    {
        $sekolahId = TenantContext::get();

        return [
            'nama' => ['required', 'string', 'max:255'],
            'username' => [
                'required',
                'string',
                'max:100',
                'regex:/^[a-zA-Z0-9._-]+$/',
                Rule::unique('users', 'username')->where(fn ($q) => $q->where('sekolah_id', $sekolahId)),
            ],
            'email' => ['nullable', 'email', 'max:255'],
            'nip' => ['nullable', 'string', 'max:50'],
            'nuptk' => ['nullable', 'string', 'max:50'],
            'password' => ['required', 'string', 'min:8'],
        ];
    }

    public function messages(): array
    {
        return [
            'nama.required' => 'Nama lengkap wajib diisi.',
            'username.required' => 'Username wajib diisi.',
            'username.regex' => 'Username hanya boleh memuat huruf, angka, titik, strip (-), dan garis bawah (_).',
            'username.unique' => 'Username sudah digunakan di sekolah ini.',
            'email.email' => 'Format email tidak valid.',
            'password.required' => 'Password awal wajib diisi.',
            'password.min' => 'Password minimal 8 karakter.',
        ];
    }
}
