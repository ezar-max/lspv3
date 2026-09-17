<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreMasterInstrumentRequest extends FormRequest
{
    public function authorize(): bool
    {
        return auth()->check() && in_array(auth()->user()->peran, ['admin', 'asesor', 'superadmin', 'komite_teknis']);
    }

    public function rules(): array
    {
        return [
            'skema_id' => 'required|exists:skema_sertifikasi,id',
            'unit_kompetensi_id' => 'nullable|exists:unit_kompetensi,id',
            'instrument_code' => 'required|string|in:ia02,ia03,ia04a,ia05,ia06,ia07,ia11',
            'title' => 'required|string|max:255',
            'instructions' => 'nullable|string',
            'time_limit_minutes' => 'nullable|integer|min:1|max:480',
            'is_active' => 'nullable|boolean',
            'additional_metadata' => 'nullable|array',
        ];
    }

    public function messages(): array
    {
        return [
            'skema_id.required' => 'Skema sertifikasi wajib dipilih.',
            'instrument_code.required' => 'Kode instrumen asesmen wajib dipilih.',
            'instrument_code.in' => 'Kode instrumen tidak valid (Pilihan: IA.02, IA.03, IA.04A, IA.05, IA.06, IA.07, IA.11).',
            'title.required' => 'Judul instrumen wajib diisi.',
            'time_limit_minutes.integer' => 'Batas waktu pengerjaan harus berupa angka menit.',
        ];
    }
}
