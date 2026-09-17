@extends('tata-letak.publik')

@section('judul', $berita->judul)

@push('css')
    <link rel="stylesheet" href="{{ asset('css/publik/berita.css') }}">
@endpush

@section('konten')
<div style="max-width: 900px; margin: 3rem auto; padding: 0 1.5rem;" class="animasi-slide">
    <a href="{{ route('publik.berita') }}" class="tombol tombol-sekunder tombol-sm" style="margin-bottom: 1.5rem;">
        <i class="fa-solid fa-arrow-left"></i> Kembali ke Pengumuman
    </a>

    <article class="kartu" style="padding: 2.5rem;">
        <span class="lencana lencana-amber" style="margin-bottom: 1rem; text-transform: uppercase;">{{ $berita->kategori }}</span>
        <h1 style="font-size: 2.2rem; color: var(--biru-malam); margin-bottom: 1rem; line-height: 1.3;">{{ $berita->judul }}</h1>
        <div style="display: flex; gap: 1.5rem; color: var(--abu-teks); font-size: 0.9rem; margin-bottom: 2rem; border-bottom: 1px solid var(--biru-soft); padding-bottom: 1rem;">
            <span><i class="fa-solid fa-calendar"></i> {{ date('d F Y', strtotime($berita->tanggal_publikasi)) }}</span>
            <span><i class="fa-solid fa-user"></i> Ditulis oleh {{ $berita->penulis->nama_lengkap ?? 'Admin LSP' }}</span>
        </div>

        <div style="font-size: 1.05rem; color: var(--hitam-teks); line-height: 1.8; margin-bottom: 2rem;">
            {!! nl2br(e($berita->konten)) !!}
        </div>
    </article>
</div>
@endsection
