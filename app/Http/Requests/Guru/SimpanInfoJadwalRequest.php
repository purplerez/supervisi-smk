<?php

namespace App\Http\Requests\Guru;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

class SimpanInfoJadwalRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'kelas' => ['required', 'string', 'max:50'],
            'semester' => ['nullable', 'string', 'max:20'],
            'fase' => ['nullable', 'string', 'max:20'],
            'mata_pelajaran' => ['required', 'string', 'max:100'],
            'elemen' => ['nullable', 'string', 'max:100'],
            'cp' => ['nullable', 'string'],
            'catatan' => ['nullable', 'string'],
            'jadwal' => ['nullable', 'array'],
            'jadwal.*.tanggal' => ['nullable', 'date'],
            'jadwal.*.jam_mulai' => ['nullable', 'string', 'max:10'],
            'jadwal.*.jam_selesai' => ['nullable', 'string', 'max:10'],
        ];
    }

    /**
     * Custom attribute names in Indonesian.
     *
     * @return array<string, string>
     */
    public function attributes(): array
    {
        return [
            'kelas' => 'Kelas',
            'semester' => 'Semester',
            'fase' => 'Fase',
            'mata_pelajaran' => 'Mata Pelajaran',
            'elemen' => 'Elemen / Materi Pokok',
            'cp' => 'Capaian Pembelajaran (CP)',
            'catatan' => 'Catatan Khusus',
            'jadwal.*.tanggal' => 'Tanggal Supervisi',
            'jadwal.*.jam_mulai' => 'Jam Mulai',
            'jadwal.*.jam_selesai' => 'Jam Selesai',
        ];
    }

    /**
     * Custom validation messages.
     *
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'kelas.required' => 'Kelas wajib diisi.',
            'mata_pelajaran.required' => 'Mata pelajaran wajib diisi.',
            'jadwal.*.tanggal.date' => 'Format tanggal supervisi tidak valid.',
        ];
    }
}
