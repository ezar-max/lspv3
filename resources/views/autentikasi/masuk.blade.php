@extends('tata-letak.publik')

@section('judul', 'Masuk ke Portal LSP')

@push('css')
    <link rel="stylesheet" href="{{ asset('css/autentikasi/masuk.css') }}">
@endpush

@section('konten')
<div class="halaman-autentikasi">
    <div class="auth-ambient-glow"></div>
    <div class="kartu-autentikasi animasi-slide">
        <div class="header-autentikasi">
            <h2>Masuk ke Akun Anda</h2>
            <p>Akses sistem informasi sertifikasi kompetensi SMKN 1 Gunungputri</p>
        </div>

        <form action="{{ route('masuk.proses') }}" method="POST">
            @csrf
            <div class="grup-form">
                <label class="label-form">
                    <i class="fa-solid fa-envelope"></i>
                    <span>Email Terdaftar</span>
                </label>
                <input type="email" name="email" class="input-control" placeholder="nama@email.com" value="{{ old('email') }}" required autofocus>
                @error('email') <small style="color: #ef4444; font-size: 0.78rem; display: block; margin-top: 0.35rem;">{{ $message }}</small> @enderror
            </div>

            <div class="grup-form">
                <label class="label-form">
                    <i class="fa-solid fa-lock"></i>
                    <span>Kata Sandi</span>
                </label>
                <div class="input-sandi-wrapper">
                    <input type="password" name="kata_sandi" id="kata_sandi" class="input-control" placeholder="••••••••" required>
                    <button type="button" class="tombol-intip-sandi" title="Tampilkan/Sembunyikan Kata Sandi">
                        <i class="fa-solid fa-eye"></i>
                    </button>
                </div>
                @error('kata_sandi') <small style="color: #ef4444; font-size: 0.78rem; display: block; margin-top: 0.35rem;">{{ $message }}</small> @enderror
            </div>

            <button type="submit" class="tombol-submit-auth">
                <i class="fa-solid fa-arrow-right-to-bracket"></i>
                <span>Masuk Sekarang</span>
            </button>
        </form>

        <div class="footer-link-auth">
            Belum memiliki akun Asesi? <a href="{{ route('registrasi') }}">Daftar Akun Baru &rarr;</a>
        </div>
    </div>
</div>
@endsection
