<?php

namespace App\Http\Requests\Guru;

use App\Models\Penugasan;
use App\Models\Periode;
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
        $guru = $this->user();
        $periodeAktif = Periode::where('status', 'aktif')->first();

        $penugasanAda = false;
        if ($guru && $periodeAktif) {
            $penugasanAda = Penugasan::where('periode_id', $periodeAktif->id)
                ->where('guru_id', $guru->id)
                ->exists();
        }

        return [
            'penilai_id' => [
                $penugasanAda ? 'nullable' : 'required',
                'integer',
                function ($attribute, $value, $fail) use ($guru) {
                    if ($value && $guru && (int) $value === (int) $guru->id) {
                        $fail('Anda tidak dapat memilih diri Anda sendiri sebagai supervisor penilai.');
                    }
                },
            ],
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
            'penilai_id' => 'Supervisor Penilai',
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
            'penilai_id.required' => 'Silakan pilih salah satu supervisor penilai.',
            'kelas.required' => 'Kelas wajib diisi.',
            'mata_pelajaran.required' => 'Mata pelajaran wajib diisi.',
            'jadwal.*.tanggal.date' => 'Format tanggal supervisi tidak valid.',
        ];
    }
}
