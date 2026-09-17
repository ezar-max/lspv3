@extends('tata-letak.dasbor')

@section('judul', 'Pengaturan Global Sistem')

@push('css')
    <link rel="stylesheet" href="{{ asset('css/superadmin/dashboard-superadmin.css') }}">
@endpush

@section('konten')
<div style="max-width: 950px;" class="animasi-slide">
    <div style="margin-bottom: 2rem;">
        <h1 style="font-size: 1.8rem; color: var(--biru-malam);">Pengaturan Global Sistem LSP</h1>
        <p style="color: var(--abu-teks);">Konfigurasi profil identitas LSP, nomor lisensi BNSP, kontak resmi, serta visi-misi</p>
    </div>

    <div class="kartu" style="padding: 2rem;">
        <form action="{{ auth()->user()->peran === 'admin' ? route('admin.pengaturan-sistem.simpan') : route('superadmin.pengaturan-sistem.simpan') }}" method="POST" enctype="multipart/form-data">
            @csrf
            <h3 style="color: var(--biru-malam); margin-bottom: 1.25rem; border-bottom: 1px solid var(--biru-soft); padding-bottom: 0.5rem;">
                <i class="fa-solid fa-building" style="color: var(--biru-utama);"></i> Identitas & Legalitas Resmi LSP
            </h3>

            <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 1.25rem;">
                <div class="grup-form">
                    <label class="label-form">Nama Resmi Lembaga</label>
                    <input type="text" name="nama_lsp" class="input-control" value="{{ old('nama_lsp', $pengaturan->nama_lsp) }}" required>
                </div>
                <div class="grup-form">
                    <label class="label-form">Kode LSP</label>
                    <input type="text" name="kode_lsp" class="input-control" value="{{ old('kode_lsp', $pengaturan->kode_lsp) }}" required>
                </div>
            </div>

            <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 1.25rem;">
                <div class="grup-form">
                    <label class="label-form">No. SK Lisensi BNSP</label>
                    <input type="text" name="no_sk_lisensi" class="input-control" value="{{ old('no_sk_lisensi', $pengaturan->no_sk_lisensi) }}" placeholder="Contoh: KEP.1215/BNSP/V/2025">
                </div>
                <div class="grup-form">
                    <label class="label-form">No. Lisensi Resmi BNSP</label>
                    <input type="text" name="nomor_lisensi" class="input-control" value="{{ old('nomor_lisensi', $pengaturan->nomor_lisensi) }}" required placeholder="Contoh: BNSP-LSP-2629-ID">
                </div>
            </div>

            <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 1.25rem;">
                <div class="grup-form">
                    <label class="label-form">Masa Berlaku Lisensi</label>
                    <input type="text" name="masa_berlaku" class="input-control" value="{{ old('masa_berlaku', $pengaturan->masa_berlaku) }}" placeholder="Contoh: Hingga 23 Mei 2030">
                </div>
                <div class="grup-form">
                    <label class="label-form">Status Keaktifan Lisensi</label>
                    <select name="status_keaktifan" class="input-control">
                        <option value="Aktif" {{ old('status_keaktifan', $pengaturan->status_keaktifan) == 'Aktif' ? 'selected' : '' }}>Aktif</option>
                        <option value="Perpanjangan" {{ old('status_keaktifan', $pengaturan->status_keaktifan) == 'Perpanjangan' ? 'selected' : '' }}>Dalam Proses Perpanjangan</option>
                        <option value="Tidak Aktif" {{ old('status_keaktifan', $pengaturan->status_keaktifan) == 'Tidak Aktif' ? 'selected' : '' }}>Tidak Aktif</option>
                    </select>
                </div>
            </div>

            <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 1.25rem;">
                <div class="grup-form">
                    <label class="label-form">Email Resmi LSP</label>
                    <input type="email" name="email_resmi" class="input-control" value="{{ old('email_resmi', $pengaturan->email_resmi) }}" required>
                </div>
                <div class="grup-form">
                    <label class="label-form">Nomor Telepon Sekretariat</label>
                    <input type="text" name="nomor_telepon" class="input-control" value="{{ old('nomor_telepon', $pengaturan->nomor_telepon) }}" required>
                </div>
            </div>

            <div class="grup-form">
                <label class="label-form">Alamat Operasional Lengkap</label>
                <input type="text" name="alamat_lengkap" class="input-control" value="{{ old('alamat_lengkap', $pengaturan->alamat_lengkap) }}" required>
            </div>

            <h3 style="color: var(--biru-malam); margin-top: 2rem; margin-bottom: 1.25rem; border-bottom: 1px solid var(--biru-soft); padding-bottom: 0.5rem;">
                <i class="fa-solid fa-bullseye" style="color: var(--biru-muda);"></i> Profil, Visi, dan Misi
            </h3>

            <div class="grup-form">
                <label class="label-form">Tentang LSP</label>
                <textarea name="tentang_lsp" class="input-control" rows="3">{{ old('tentang_lsp', $pengaturan->tentang_lsp) }}</textarea>
            </div>

            <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 1.25rem;">
                <div class="grup-form">
                    <label class="label-form">Visi LSP</label>
                    <textarea name="visi" class="input-control" rows="3">{{ old('visi', $pengaturan->visi) }}</textarea>
                </div>
                <div class="grup-form">
                    <label class="label-form">Misi LSP</label>
                    <textarea name="misi" class="input-control" rows="3">{{ old('misi', $pengaturan->misi) }}</textarea>
                </div>
            </div>

            <div style="margin-top: 2.5rem; text-align: right;">
                <button type="submit" class="tombol tombol-utama">
                    <i class="fa-solid fa-floppy-disk"></i> Simpan Konfigurasi Sistem
                </button>
            </div>
        </form>
    </div>
</div>
@endsection
