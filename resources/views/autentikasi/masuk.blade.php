@extends('tata-letak.publik')

@section('judul', 'Masuk Sistem')

@push('css')
    <link rel="stylesheet" href="{{ asset('css/autentikasi/masuk.css') }}">
@endpush

@section('konten')
<div class="halaman-autentikasi">
    <div class="kartu-autentikasi animasi-slide">
        <div class="header-autentikasi">
            <div style="width: 54px; height: 54px; background: var(--biru-soft); color: var(--biru-utama); border-radius: 50%; display: flex; align-items: center; justify-content: center; margin: 0 auto 1rem auto; font-size: 1.5rem;">
                <i class="fa-solid fa-lock"></i>
            </div>
            <h2>Masuk ke Portal LSP</h2>
            <p>Silakan masukkan email dan kata sandi Anda</p>
        </div>

        <form action="{{ route('masuk.proses') }}" method="POST">
            @csrf
            <div class="grup-form">
                <label class="label-form"><i class="fa-solid fa-envelope"></i> Email Resmi</label>
                <input type="email" name="email" class="input-control" placeholder="nama@email.com" value="{{ old('email') }}" required autofocus>
                @error('email') <small style="color: var(--merah-bahaya);">{{ $message }}</small> @enderror
            </div>

            <div class="grup-form">
                <label class="label-form"><i class="fa-solid fa-key"></i> Kata Sandi</label>
                <div class="input-sandi-wrapper">
                    <input type="password" name="kata_sandi" id="kata_sandi" class="input-control" placeholder="••••••••" required>
                    <button type="button" class="tombol-intip-sandi" title="Tampilkan/Sembunyikan Kata Sandi">
                        <i class="fa-solid fa-eye"></i>
                    </button>
                </div>
                @error('kata_sandi') <small style="color: var(--merah-bahaya);">{{ $message }}</small> @enderror
            </div>

            <button type="submit" class="tombol tombol-utama" style="width: 100%; margin-top: 1rem; padding: 0.8rem;">
                <i class="fa-solid fa-right-to-bracket"></i> Masuk Sekarang
            </button>
        </form>

        <div style="text-align: center; margin-top: 1.5rem; font-size: 0.9rem; color: var(--abu-teks);">
            Belum punya akun Asesi? <a href="{{ route('registrasi') }}" style="font-weight: 700;">Daftar Akun Baru</a>
        </div>
    </div>
</div>
@endsection
