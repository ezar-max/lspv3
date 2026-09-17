<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateAssessmentAk07Request extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return auth()->check() && in_array(auth()->user()->peran, ['asesor', 'admin', 'superadmin']);
    }

    /**
     * Get the validation rules that apply to the request.
     */
    public function rules(): array
    {
        return [
            'potensi_asesi' => 'nullable|integer|between:1,5',
            'fase_penggunaan' => 'nullable|string|in:pra_asesmen,saat_pra_asesmen,setelah_pra_asesmen',
            'items_checklist' => 'nullable|array',
            'acuan_pembanding_disepakati' => 'nullable|string|max:2000',
            'metode_disepakati' => 'nullable|string|max:2000',
            'instrumen_disepakati' => 'nullable|string|max:2000',
            'catatan_asesor' => 'nullable|string|max:3000',
            'tanda_tangan_asesor' => 'nullable|string',
            'aksi' => 'nullable|string|in:draft,confirm,konfirmasi',
        ];
    }

    /**
     * Custom validation messages.
     */
    public function messages(): array
    {
        return [
            'potensi_asesi.between' => 'Kategori potensi asesi harus bernilai antara 1 sampai 5.',
            'fase_penggunaan.in' => 'Fase penggunaan penyesuaian tidak valid.',
        ];
    }
}
