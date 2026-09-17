<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreQuestionBankRequest extends FormRequest
{
    public function authorize(): bool
    {
        return auth()->check() && in_array(auth()->user()->peran, ['admin', 'asesor', 'superadmin', 'komite_teknis']);
    }

    public function rules(): array
    {
        $rules = [
            'scheme_master_instrument_id' => 'required|exists:scheme_master_instruments,id',
            'kuk_id' => 'nullable|exists:kriteria_unjuk_kerja,id',
            'question_type' => 'required|in:multiple_choice,essay,oral',
            'question_text' => 'required|string|min:5',
            'image' => 'nullable|image|mimes:jpeg,png,jpg,webp|max:3072',
            'points' => 'nullable|integer|min:1|max:100',
            'order' => 'nullable|integer|min:1',
            'rubric_guide' => 'nullable|string',
            'correct_answer' => 'required|string',
        ];

        if ($this->input('question_type') === 'multiple_choice') {
            $rules['options'] = 'required|array|min:4';
            $rules['options.A'] = 'required|string';
            $rules['options.B'] = 'required|string';
            $rules['options.C'] = 'required|string';
            $rules['options.D'] = 'required|string';
            $rules['correct_answer'] = 'required|string|in:A,B,C,D,E';
        }

        return $rules;
    }

    public function messages(): array
    {
        return [
            'scheme_master_instrument_id.required' => 'ID Instrumen Master wajib disertakan.',
            'question_text.required' => 'Teks pertanyaan atau studi kasus wajib diisi.',
            'question_text.min' => 'Teks butir soal minimal terdiri dari 5 karakter.',
            'options.required' => 'Pilihan opsi jawaban A, B, C, dan D wajib diisi lengkap untuk soal pilihan ganda.',
            'options.min' => 'Soal pilihan ganda wajib memiliki minimal 4 opsi (A, B, C, D).',
            'correct_answer.required' => 'Kunci jawaban yang benar wajib ditentukan.',
            'correct_answer.in' => 'Kunci jawaban pilihan ganda harus berupa salah satu opsi (A, B, C, D).',
            'image.image' => 'Berkas gambar pendukung soal harus berformat gambar (JPG/PNG/WebP).',
            'image.max' => 'Ukuran gambar soal maksimal 3MB.',
        ];
    }
}
