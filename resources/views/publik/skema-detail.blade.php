@extends('tata-letak.publik')

@section('judul', $skema->nama_skema)

@push('css')
    <link rel="stylesheet" href="{{ asset('css/publik/beranda.css') }}">
@endpush

@section('konten')
<div style="max-width: 1000px; margin: 3rem auto; padding: 0 1.5rem;" class="animasi-slide">
    <a href="{{ route('publik.skema') }}" class="tombol tombol-sekunder tombol-sm" style="margin-bottom: 1.5rem;">
        <i class="fa-solid fa-arrow-left"></i> Kembali ke Daftar Skema
    </a>

    <div class="kartu" style="padding: 2.5rem;">
        <div style="margin-bottom: 1rem;">
            <span class="lencana lencana-biru" style="margin-bottom: 0.5rem;">{{ $skema->kategori }}</span>
            <h1 style="font-size: 2rem; color: var(--biru-malam);">{{ $skema->nama_skema }}</h1>
            <p style="color: var(--biru-utama); font-weight: 700;">Kode Skema: {{ $skema->kode_skema }}</p>
        </div>

        <hr style="border: none; border-top: 1px solid var(--biru-soft); margin: 1.5rem 0;">

        <h3 style="color: var(--biru-malam); margin-bottom: 0.75rem;">Deskripsi Skema</h3>
        <p style="color: var(--abu-teks); line-height: 1.7; margin-bottom: 2rem;">{{ $skema->deskripsi }}</p>

        <h3 style="color: var(--biru-malam); margin-bottom: 1rem;">Daftar Unit Kompetensi (SKKNI)</h3>
        <div class="tabel-wadah">
            <table class="tabel-custom">
                <thead>
                    <tr>
                        <th style="width: 60px;">No</th>
                        <th>Kode Unit</th>
                        <th>Judul Unit Kompetensi</th>
                        <th>Standar</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($skema->unitKompetensi as $index => $u)
                        <tr>
                            <td>{{ $index + 1 }}</td>
                            <td><strong style="color: var(--biru-utama);">{{ $u->kode_unit }}</strong></td>
                            <td>{{ $u->judul_unit }}</td>
                            <td><span class="lencana lencana-biru">{{ $u->standar_kompetensi }}</span></td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="4" style="text-align: center; color: var(--abu-teks);">Belum ada unit kompetensi terdaftar.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <div style="margin-top: 2.5rem; text-align: center; background: var(--biru-bg); padding: 2rem; border-radius: var(--radius-lg); border: 1px dashed var(--biru-muda);">
            <h3 style="color: var(--biru-malam); margin-bottom: 0.5rem;">Tertarik Mengikuti Asesmen Skema Ini?</h3>
            <p style="color: var(--abu-teks); margin-bottom: 1.5rem;">Daftarkan diri Anda sebagai Asesi dan unggah portofolio APL-01 & APL-02 secara online.</p>
            <a href="{{ route('registrasi') }}" class="tombol tombol-utama">
                <i class="fa-solid fa-user-plus"></i> Daftar Sertifikasi Sekarang
            </a>
        </div>
    </div>
</div>
@endsection
