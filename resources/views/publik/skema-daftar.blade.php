@extends('tata-letak.publik')

@section('judul', 'Daftar Skema Sertifikasi')

@push('css')
    <link rel="stylesheet" href="{{ asset('css/publik/beranda.css') }}">
@endpush

@section('konten')
<div style="max-width: 1200px; margin: 3rem auto; padding: 0 1.5rem;" class="animasi-slide">
    <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 2rem;">
        <div>
            <h1 style="font-size: 2rem; color: var(--biru-malam);">Daftar Skema Sertifikasi</h1>
            <p style="color: var(--abu-teks);">Temukan skema sertifikasi kompetensi sesuai program keahlian Anda</p>
        </div>
    </div>

    <!-- PENCARIAN & FILTER -->
    <form action="{{ route('publik.skema') }}" method="GET" style="display: flex; gap: 1rem; margin-bottom: 2rem; background: var(--putih); padding: 1.25rem; border-radius: var(--radius-lg); border: 1px solid var(--biru-soft);">
        <input type="text" name="q" class="input-control" placeholder="Cari skema atau kode..." value="{{ $kataKunci }}">
        <select name="kategori" class="input-control" style="width: 220px;">
            <option value="">Semua Kategori</option>
            @foreach($kategoriList as $k)
                <option value="{{ $k }}" {{ $kategori == $k ? 'selected' : '' }}>{{ $k }}</option>
            @endforeach
        </select>
        <button type="submit" class="tombol tombol-utama"><i class="fa-solid fa-magnifying-glass"></i> Cari</button>
    </form>

    <!-- GRID SKEMA -->
    <div class="grid-skema-publik">
        @forelse($skemaList as $s)
            <div class="kartu kartu-hover">
                <div style="display: flex; justify-content: space-between; margin-bottom: 0.75rem;">
                    <span class="lencana lencana-biru">{{ $s->kategori }}</span>
                    <span style="font-weight: 700; color: var(--biru-utama);">{{ $s->kode_skema }}</span>
                </div>
                <h3 style="font-size: 1.2rem; color: var(--biru-malam); margin-bottom: 0.5rem;">{{ $s->nama_skema }}</h3>
                <p style="color: var(--abu-teks); font-size: 0.88rem; margin-bottom: 1rem;">
                    <i class="fa-solid fa-list-check" style="color: var(--biru-muda);"></i> {{ $s->unit_kompetensi_count }} Unit Kompetensi
                </p>
                <p style="color: var(--abu-teks); font-size: 0.9rem; margin-bottom: 1.5rem;">{{ Str::limit($s->deskripsi, 90) }}</p>
                <div style="display: flex; justify-content: flex-end; align-items: center; border-top: 1px solid var(--biru-soft); padding-top: 1rem;">
                    <a href="{{ route('publik.skema.detail', $s->id) }}" class="tombol tombol-sekunder tombol-sm">Lihat Detail &rarr;</a>
                </div>
            </div>
        @empty
            <div style="grid-column: 1/-1; text-align: center; color: var(--abu-teks); padding: 3rem;" class="kartu">
                Tidak ada skema sertifikasi yang ditemukan.
            </div>
        @endforelse
    </div>

    <div style="margin-top: 2rem;">
        {{ $skemaList->links() }}
    </div>
</div>
@endsection
