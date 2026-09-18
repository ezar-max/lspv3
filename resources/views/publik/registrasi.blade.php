@extends('tata-letak.publik')

@section('judul', 'Pendaftaran Akun Asesi')

@push('css')
    <link rel="stylesheet" href="{{ asset('css/publik/registrasi.css') }}">
@endpush

@section('konten')
<div class="halaman-registrasi">
    <div class="auth-ambient-glow"></div>
    <div class="kartu-registrasi animasi-slide">
        <div class="header-registrasi">
            <h1>Buat Akun Asesi</h1>
            <p>Lengkapi formulir di bawah ini untuk memulai proses sertifikasi kompetensi</p>
        </div>

        <form action="{{ route('registrasi.proses') }}" method="POST">
            @csrf
            <div class="grup-form">
                <label class="label-form">
                    <i class="fa-solid fa-user"></i>
                    <span>Nama Lengkap Asesi (Sesuai KTP/Ijazah) <span class="tanda-wajib">*</span></span>
                </label>
                <input type="text" name="nama_lengkap" class="input-control" placeholder="contoh: Ahmad Rizky" value="{{ old('nama_lengkap') }}" required autofocus>
                @error('nama_lengkap') <small style="color: #ef4444; font-size: 0.78rem; display: block; margin-top: 0.35rem;">{{ $message }}</small> @enderror
            </div>

            <div class="grid-dua-kolom">
                <div class="grup-form">
                    <label class="label-form">
                        <i class="fa-solid fa-envelope"></i>
                        <span>Email Resmi <span class="tanda-wajib">*</span></span>
                    </label>
                    <input type="email" name="email" class="input-control" placeholder="ahmad@gmail.com" value="{{ old('email') }}" required>
                    @error('email') <small style="color: #ef4444; font-size: 0.78rem; display: block; margin-top: 0.35rem;">{{ $message }}</small> @enderror
                </div>
                <div class="grup-form">
                    <label class="label-form">
                        <i class="fa-brands fa-whatsapp"></i>
                        <span>Nomor WhatsApp / Telepon <span class="tanda-wajib">*</span></span>
                    </label>
                    <input type="text" name="nomor_telepon" class="input-control" placeholder="08123456789" value="{{ old('nomor_telepon') }}" required>
                    @error('nomor_telepon') <small style="color: #ef4444; font-size: 0.78rem; display: block; margin-top: 0.35rem;">{{ $message }}</small> @enderror
                </div>
            </div>

            <div class="grid-dua-kolom">
                <div class="grup-form">
                    <label class="label-form">
                        <i class="fa-solid fa-id-card"></i>
                        <span>NIK (16 Digit KTP) <span class="tanda-wajib">*</span></span>
                    </label>
                    <input type="text" name="nik" class="input-control" placeholder="16 digit angka NIK" value="{{ old('nik') }}" required minlength="16" maxlength="16" pattern="[0-9]{16}" inputmode="numeric" oninput="this.value = this.value.replace(/[^0-9]/g, '').slice(0, 16)" title="NIK harus berjumlah tepat 16 digit angka">
                    @error('nik') <small style="color: #ef4444; font-size: 0.78rem; display: block; margin-top: 0.35rem;">{{ $message }}</small> @enderror
                </div>
                <div class="grup-form">
                    <label class="label-form">
                        <i class="fa-solid fa-building-columns"></i>
                        <span>Nama Sekolah / Instansi</span>
                    </label>
                    <input type="text" name="nama_sekolah_instansi" class="input-control" placeholder="SMKN 1 Gunungputri" value="{{ old('nama_sekolah_instansi') }}">
                    @error('nama_sekolah_instansi') <small style="color: #ef4444; font-size: 0.78rem; display: block; margin-top: 0.35rem;">{{ $message }}</small> @enderror
                </div>
            </div>

            <div class="grid-dua-kolom">
                <div class="grup-form">
                    <label class="label-form">
                        <i class="fa-solid fa-lock"></i>
                        <span>Kata Sandi <span class="tanda-wajib">*</span></span>
                    </label>
                    <div class="input-sandi-wrapper">
                        <input type="password" name="kata_sandi" id="reg_kata_sandi" class="input-control" placeholder="Minimal 6 karakter" required>
                        <button type="button" class="tombol-intip-sandi" title="Tampilkan/Sembunyikan Kata Sandi">
                            <i class="fa-solid fa-eye"></i>
                        </button>
                    </div>
                    @error('kata_sandi') <small style="color: #ef4444; font-size: 0.78rem; display: block; margin-top: 0.35rem;">{{ $message }}</small> @enderror
                </div>
                <div class="grup-form">
                    <label class="label-form">
                        <i class="fa-solid fa-shield-halved"></i>
                        <span>Konfirmasi Sandi <span class="tanda-wajib">*</span></span>
                    </label>
                    <div class="input-sandi-wrapper">
                        <input type="password" name="kata_sandi_confirmation" id="reg_kata_sandi_confirm" class="input-control" placeholder="Ulangi kata sandi" required>
                        <button type="button" class="tombol-intip-sandi" title="Tampilkan/Sembunyikan Kata Sandi">
                            <i class="fa-solid fa-eye"></i>
                        </button>
                    </div>
                </div>
            </div>

            <button type="submit" class="tombol-submit-auth">
                <i class="fa-solid fa-user-plus"></i>
                <span>Daftar Akun Asesi Baru</span>
            </button>
        </form>

        <div class="footer-link-auth">
            Sudah memiliki akun? <a href="{{ route('masuk') }}">Masuk ke Portal &rarr;</a>
        </div>
    </div>
</div>
@endsection
