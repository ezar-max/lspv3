@extends('tata-letak.publik')

@section('judul', 'Berita & Pengumuman')

@push('css')
    <link rel="stylesheet" href="{{ asset('css/publik/berita.css') }}">
@endpush

@section('konten')
<div style="max-width: 1200px; margin: 3rem auto; padding: 0 1.5rem;" class="animasi-slide">
    <div style="margin-bottom: 2rem;">
        <h1 style="font-size: 2rem; color: var(--biru-malam);">Berita & Pengumuman Portal LSP</h1>
        <p style="color: var(--abu-teks);">Informasi resmi seputar pelaksanaan asesmen, jadwal sertifikasi, dan berita vokasi</p>
    </div>

    <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(320px, 1fr)); gap: 2rem;">
        @forelse($beritaList as $b)
            <div class="kartu kartu-hover">
                <span class="lencana lencana-amber" style="margin-bottom: 0.75rem; text-transform: uppercase;">{{ $b->kategori }}</span>
                <h3 style="font-size: 1.2rem; color: var(--biru-malam); margin-bottom: 0.75rem;">{{ $b->judul }}</h3>
                <p style="color: var(--abu-teks); font-size: 0.9rem; margin-bottom: 1.5rem; line-height: 1.6;">{{ Str::limit($b->ringkasan, 110) }}</p>
                <div style="display: flex; justify-content: space-between; align-items: center; border-top: 1px solid var(--biru-soft); padding-top: 1rem;">
                    <small style="color: var(--abu-teks);"><i class="fa-solid fa-user"></i> {{ $b->penulis->nama_lengkap ?? 'Admin LSP' }}</small>
                    <a href="{{ route('publik.berita.detail', $b->slug) }}" class="tombol tombol-sekunder tombol-sm">Baca Artikel &rarr;</a>
                </div>
            </div>
        @empty
            <div style="grid-column: 1/-1; text-align: center; color: var(--abu-teks); padding: 3rem;" class="kartu">
                Belum ada berita atau pengumuman dipublikasikan.
            </div>
        @endforelse
    </div>

    <div style="margin-top: 2rem;">
        {{ $beritaList->links() }}
    </div>
</div>
@endsection
