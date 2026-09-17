@extends('tata-letak.publik')

@section('judul', 'Pendaftaran Akun Asesi')

@push('css')
    <link rel="stylesheet" href="{{ asset('css/publik/registrasi.css') }}">
@endpush

@section('konten')
<div style="max-width: 650px; margin: 3rem auto; padding: 0 1.5rem;" class="animasi-slide">
    <div class="kartu" style="padding: 2.5rem;">
        <div style="text-align: center; margin-bottom: 2rem;">
            <span class="lencana lencana-biru" style="margin-bottom: 0.5rem;">Pendaftaran Peserta</span>
            <h1 style="font-size: 1.8rem; color: var(--biru-malam);">Buat Akun Asesi Baru</h1>
            <p style="color: var(--abu-teks); font-size: 0.92rem;">Lengkapi formulir di bawah ini untuk memulai pendaftaran sertifikasi kompetensi</p>
        </div>

        <form action="{{ route('registrasi.proses') }}" method="POST">
            @csrf
            <div class="grup-form">
                <label class="label-form">Nama Lengkap Asesi (Sesuai Ijazah/KTP)</label>
                <input type="text" name="nama_lengkap" class="input-control" placeholder="contoh: Ahmad Rizky" value="{{ old('nama_lengkap') }}" required>
                @error('nama_lengkap') <small style="color: var(--merah-bahaya);">{{ $message }}</small> @enderror
            </div>

            <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 1.25rem;">
                <div class="grup-form">
                    <label class="label-form">Email Resmi (Untuk Login)</label>
                    <input type="email" name="email" class="input-control" placeholder="ahmad@gmail.com" value="{{ old('email') }}" required>
                    @error('email') <small style="color: var(--merah-bahaya);">{{ $message }}</small> @enderror
                </div>
                <div class="grup-form">
                    <label class="label-form">Nomor WhatsApp / Telepon</label>
                    <input type="text" name="nomor_telepon" class="input-control" placeholder="08123456789" value="{{ old('nomor_telepon') }}" required>
                    @error('nomor_telepon') <small style="color: var(--merah-bahaya);">{{ $message }}</small> @enderror
                </div>
            </div>

            <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 1.25rem;">
                <div class="grup-form">
                    <label class="label-form">NIK (Nomor Induk Kependudukan) <span style="color: var(--merah-bahaya);">*</span></label>
                    <input type="text" name="nik" class="input-control" placeholder="16 digit NIK" value="{{ old('nik') }}" required minlength="16" maxlength="16" pattern="[0-9]{16}" inputmode="numeric" oninput="this.value = this.value.replace(/[^0-9]/g, '').slice(0, 16)" title="NIK harus berjumlah tepat 16 digit angka">
                    @error('nik') <small style="color: var(--merah-bahaya); display: block; margin-top: 0.25rem;">{{ $message }}</small> @enderror
                </div>
                <div class="grup-form">
                    <label class="label-form">Nama Sekolah / Instansi</label>
                    <input type="text" name="nama_sekolah_instansi" class="input-control" placeholder="SMK Negeri 1 Jakarta" value="{{ old('nama_sekolah_instansi') }}">
                </div>
            </div>

            <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 1.25rem;">
                <div class="grup-form">
                    <label class="label-form">Kata Sandi</label>
                    <div class="input-sandi-wrapper">
                        <input type="password" name="kata_sandi" id="reg_kata_sandi" class="input-control" placeholder="Minimal 6 karakter" required>
                        <button type="button" class="tombol-intip-sandi" title="Tampilkan/Sembunyikan Kata Sandi">
                            <i class="fa-solid fa-eye"></i>
                        </button>
                    </div>
                    @error('kata_sandi') <small style="color: var(--merah-bahaya);">{{ $message }}</small> @enderror
                </div>
                <div class="grup-form">
                    <label class="label-form">Konfirmasi Kata Sandi</label>
                    <div class="input-sandi-wrapper">
                        <input type="password" name="kata_sandi_confirmation" id="reg_kata_sandi_confirm" class="input-control" placeholder="Ulangi kata sandi" required>
                        <button type="button" class="tombol-intip-sandi" title="Tampilkan/Sembunyikan Kata Sandi">
                            <i class="fa-solid fa-eye"></i>
                        </button>
                    </div>
                </div>
            </div>

            <button type="submit" class="tombol tombol-utama" style="width: 100%; margin-top: 1.5rem; padding: 0.8rem;">
                <i class="fa-solid fa-user-check"></i> Daftar Akun Asesi Baru
            </button>
        </form>

        <div style="text-align: center; margin-top: 1.5rem; font-size: 0.9rem; color: var(--abu-teks);">
            Sudah memiliki akun? <a href="{{ route('masuk') }}" style="font-weight: 700;">Masuk ke Portal</a>
        </div>
    </div>
</div>
@endsection
