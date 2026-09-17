@extends('tata-letak.dasbor')

@section('judul', 'Edit Master Instrumen - ' . $instrument->title)

@push('css')
    <link rel="stylesheet" href="{{ asset('css/admin/dashboard-admin.css') }}">
    <style>
        .form-muk-card {
            background: #ffffff;
            border-radius: 12px;
            border: 1px solid #e2e8f0;
            padding: 2rem;
            max-width: 900px;
            margin: 0 auto;
            box-shadow: 0 4px 6px -1px rgba(0,0,0,0.03);
        }
    </style>
@endpush

@section('konten')
<div style="margin-bottom: 1.5rem; display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 0.75rem;">
    <div style="display: flex; align-items: center; gap: 0.75rem;">
        <a href="{{ route('admin.master-muk.manage', $instrument->id) }}" class="tombol tombol-sekunder tombol-sm">
            Kembali ke Kelola Soal
        </a>
        <h1 style="font-size: 1.5rem; color: var(--biru-malam); margin: 0; font-weight: 800;">
            Edit Pengaturan Master Instrumen (#{{ $instrument->id }})
        </h1>
    </div>
</div>

@if ($errors->any())
    <div style="background: #fef2f2; border: 1px solid #fca5a5; color: #991b1b; padding: 1rem 1.25rem; border-radius: 10px; margin-bottom: 1.5rem; max-width: 900px; margin-left: auto; margin-right: auto;">
        <strong style="display: block; margin-bottom: 0.35rem;">Periksa kembali data formulir:</strong>
        <ul style="margin: 0; padding-left: 1.25rem; font-size: 0.88rem;">
            @foreach ($errors->all() as $error)
                <li>{{ $error }}</li>
            @endforeach
        </ul>
    </div>
@endif

<div class="form-muk-card">
    <form action="{{ route('admin.master-muk.update', $instrument->id) }}" method="POST">
        @csrf
        @method('PUT')

        <!-- SKEMA SERTIFIKASI -->
        <div style="margin-bottom: 1.5rem;">
            <label style="display: block; font-weight: 700; font-size: 0.92rem; color: #0f172a; margin-bottom: 0.4rem;">
                Skema Sertifikasi <span style="color: #dc2626;">*</span>
            </label>
            <select name="skema_id" class="input-inline-bnsp" style="width: 100%; border: 1.5px solid #cbd5e1; border-radius: 8px; padding: 0.7rem 0.85rem;" required>
                @foreach($skemaList as $sk)
                    <option value="{{ $sk->id }}" {{ (old('skema_id', $instrument->skema_id) == $sk->id) ? 'selected' : '' }}>
                        {{ $sk->kode_skema }} - {{ $sk->nama_skema }}
                    </option>
                @endforeach
            </select>
        </div>

        <!-- KODE INSTRUMEN -->
        <div style="margin-bottom: 1.5rem;">
            <label style="display: block; font-weight: 700; font-size: 0.92rem; color: #0f172a; margin-bottom: 0.4rem;">
                Jenis Formulir Instrumen Asesmen (FR.IA) <span style="color: #dc2626;">*</span>
            </label>
            <select name="instrument_code" class="input-inline-bnsp" style="width: 100%; border: 1.5px solid #cbd5e1; border-radius: 8px; padding: 0.7rem 0.85rem;" required>
                <option value="ia01" {{ old('instrument_code', $instrument->instrument_code) === 'ia01' ? 'selected' : '' }}>FR.IA.01 - Ceklis Observasi Aktivitas Praktik (CLO)</option>
                <option value="ia02" {{ old('instrument_code', $instrument->instrument_code) === 'ia02' ? 'selected' : '' }}>FR.IA.02 - Tugas Praktik Demonstrasi & Skenario (TPD)</option>
                <option value="ia03" {{ old('instrument_code', $instrument->instrument_code) === 'ia03' ? 'selected' : '' }}>FR.IA.03 - Pertanyaan Pendukung Observasi (PMO)</option>
                <option value="ia05" {{ old('instrument_code', $instrument->instrument_code) === 'ia05' ? 'selected' : '' }}>FR.IA.05 - Pertanyaan Tertulis Pilihan Ganda (CBT)</option>
                <option value="ia06" {{ old('instrument_code', $instrument->instrument_code) === 'ia06' ? 'selected' : '' }}>FR.IA.06 - Pertanyaan Tertulis Esai & Rubrik Penilaian</option>
                <option value="ia07" {{ old('instrument_code', $instrument->instrument_code) === 'ia07' ? 'selected' : '' }}>FR.IA.07 - Daftar Pertanyaan Lisan (DPL)</option>
                <option value="ia04a" {{ old('instrument_code', $instrument->instrument_code) === 'ia04a' ? 'selected' : '' }}>FR.IA.04A - Penjelasan Proyek Singkat (TOR)</option>
                <option value="ia11" {{ old('instrument_code', $instrument->instrument_code) === 'ia11' ? 'selected' : '' }}>FR.IA.11 - Ceklis Standar Mutu Produk (CRP)</option>
            </select>
        </div>

        <!-- JUDUL PAKET INSTRUMEN -->
        <div style="margin-bottom: 1.5rem;">
            <label style="display: block; font-weight: 700; font-size: 0.92rem; color: #0f172a; margin-bottom: 0.4rem;">
                Judul Paket Perangkat MUK <span style="color: #dc2626;">*</span>
            </label>
            <input type="text" name="title" value="{{ old('title', $instrument->title) }}" class="input-inline-bnsp" style="width: 100%; border: 1.5px solid #cbd5e1; border-radius: 8px; padding: 0.7rem 0.85rem; font-size: 0.95rem;" required>
        </div>

        <!-- BATAS WAKTU & STATUS -->
        <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 1rem; margin-bottom: 1.5rem;">
            <div>
                <label style="display: block; font-weight: 700; font-size: 0.92rem; color: #0f172a; margin-bottom: 0.4rem;">
                    Batas Waktu Pengerjaan (Menit)
                </label>
                <div style="display: flex; align-items: center; gap: 0.5rem;">
                    <input type="number" name="time_limit_minutes" value="{{ old('time_limit_minutes', $instrument->time_limit_minutes) }}" min="1" max="480" class="input-inline-bnsp" style="width: 100%; border: 1.5px solid #cbd5e1; border-radius: 8px; padding: 0.7rem 0.85rem;">
                    <span style="font-size: 0.88rem; color: #64748b; font-weight: 600;">Menit</span>
                </div>
            </div>

            <div>
                <label style="display: block; font-weight: 700; font-size: 0.92rem; color: #0f172a; margin-bottom: 0.4rem;">
                    Status Ketersediaan
                </label>
                <label style="display: inline-flex; align-items: center; gap: 0.5rem; margin-top: 0.6rem; cursor: pointer; font-weight: 700; color: #166534;">
                    <input type="checkbox" name="is_active" value="1" {{ old('is_active', $instrument->is_active) ? 'checked' : '' }} style="width: 18px; height: 18px; accent-color: #16a34a;">
                    Aktif (Dapat digunakan dalam jadwal asesmen)
                </label>
            </div>
        </div>

        <!-- PETUNJUK PENGERJAAN / INSTRUKSI -->
        <div style="margin-bottom: 2rem;">
            <label style="display: block; font-weight: 700; font-size: 0.92rem; color: #0f172a; margin-bottom: 0.4rem;">
                Petunjuk Pengerjaan / Instruksi bagi Asesi & Asesor
            </label>
            <textarea name="instructions" rows="4" class="input-inline-bnsp" style="width: 100%; border: 1.5px solid #cbd5e1; border-radius: 8px; padding: 0.75rem; font-family: inherit; font-size: 0.92rem;">{{ old('instructions', $instrument->instructions) }}</textarea>
        </div>

        <!-- TOMBOL SIMPAN -->
        <div style="display: flex; justify-content: flex-end; gap: 1rem; border-top: 1px solid #e2e8f0; padding-top: 1.5rem;">
            <a href="{{ route('admin.master-muk.manage', $instrument->id) }}" class="tombol tombol-sekunder">
                Batal
            </a>
            <button type="submit" class="tombol tombol-utama" style="font-weight: 700; padding: 0.7rem 2rem;">
                Perbarui Master Instrumen
            </button>
        </div>

    </form>
</div>
@endsection
