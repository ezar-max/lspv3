<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class ImportQuestionBankRequest extends FormRequest
{
    public function authorize(): bool
    {
        return auth()->check() && in_array(auth()->user()->peran, ['admin', 'asesor', 'superadmin', 'komite_teknis']);
    }

    public function rules(): array
    {
        return [
            'scheme_master_instrument_id' => 'required|exists:scheme_master_instruments,id',
            'file_import' => 'nullable|file|max:10240',
            'google_form_url' => 'nullable|string|url',
            'google_sheet_url' => 'nullable|string|url',
        ];
    }

    public function withValidator($validator)
    {
        $validator->after(function ($validator) {
            if (!$this->hasFile('file_import') && !$this->filled('google_form_url') && !$this->filled('google_sheet_url')) {
                $validator->errors()->add('google_form_url', 'Silakan masukkan tautan Google Form atau pilih berkas CSV untuk diimpor.');
            }
        });
    }

    public function messages(): array
    {
        return [
            'google_form_url.url' => 'Tautan Google Form tidak valid. Pastikan diawali https:// (contoh: https://forms.gle/... atau https://docs.google.com/forms/d/...)',
            'google_sheet_url.url' => 'Tautan Google Spreadsheet tidak valid.',
            'file_import.max' => 'Ukuran berkas import maksimal 10 MB.',
        ];
    }
}
