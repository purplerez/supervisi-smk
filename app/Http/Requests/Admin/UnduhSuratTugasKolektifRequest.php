<?php

namespace App\Http\Requests\Admin;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

class UnduhSuratTugasKolektifRequest extends FormRequest
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
            'nomor_surat' => ['required', 'string', 'max:100'],
            'tanggal_surat' => ['required', 'date'],
            'lampiran_judul' => ['nullable', 'string', 'max:255'],
            'judul_kegiatan' => ['nullable', 'string', 'max:255'],
            'penandatangan_nama' => ['required', 'string', 'max:255'],
            'penandatangan_nip' => ['nullable', 'string', 'max:50'],
            'penandatangan_jabatan' => ['nullable', 'string', 'max:150'],
            'penandatangan_pangkat' => ['nullable', 'string', 'max:100'],
            'kota' => ['nullable', 'string', 'max:100'],
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
            'nomor_surat' => 'Nomor Surat / SK',
            'tanggal_surat' => 'Tanggal Surat / SK',
            'lampiran_judul' => 'Judul Lampiran SK',
            'judul_kegiatan' => 'Judul Kegiatan',
            'penandatangan_nama' => 'Nama Kepala Sekolah',
            'penandatangan_nip' => 'NIP Kepala Sekolah',
            'penandatangan_jabatan' => 'Jabatan Kepala Sekolah',
            'penandatangan_pangkat' => 'Pangkat / Golongan',
            'kota' => 'Kota / Tempat Penetapan',
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
            'nomor_surat.required' => 'Nomor surat / SK wajib diisi.',
            'tanggal_surat.required' => 'Tanggal surat / SK wajib diisi.',
            'tanggal_surat.date' => 'Format tanggal surat tidak valid.',
            'penandatangan_nama.required' => 'Nama kepala sekolah / penandatangan wajib diisi.',
        ];
    }
}
