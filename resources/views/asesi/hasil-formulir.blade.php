@extends('tata-letak.dasbor')

@section('judul', 'Hasil Formulir Asesmen')

@push('css')
    <link rel="stylesheet" href="{{ asset('css/asesi/dashboard-asesi.css') }}">
    <style>
        .hasil-formulir-card { background: #fff; border: 1px solid #e2e8f0; border-radius: 14px; padding: 1.5rem; margin-bottom: 1rem; }
        .hasil-formulir-fields { display: grid; grid-template-columns: minmax(150px, 0.8fr) minmax(0, 2fr); gap: .65rem 1rem; margin: 1rem 0 0; }
        .hasil-formulir-fields dt { color: #64748b; font-size: .85rem; font-weight: 700; }
        .hasil-formulir-fields dd { color: #1e293b; font-size: .9rem; margin: 0; white-space: pre-wrap; overflow-wrap: anywhere; }
        @media (max-width: 640px) {
            .hasil-formulir-fields { grid-template-columns: 1fr; gap: .2rem; }
            .hasil-formulir-fields dd { margin-bottom: .65rem; }
        }
        @media print {
            .sidebar, .navbar, .tombol-aksi, .footer { display: none !important; }
            .hasil-formulir-card { break-inside: avoid; box-shadow: none !important; }
        }
    </style>
@endpush

@section('konten')
<main style="max-width: 1050px; margin: 0 auto;" class="animasi-slide">
    <header style="display:flex; justify-content:space-between; align-items:flex-start; gap:1rem; flex-wrap:wrap; margin-bottom:1.5rem;">
        <div>
            <h1 style="font-size:1.7rem; color:var(--biru-malam); margin:0 0 .4rem;">Hasil Formulir Asesmen</h1>
            <p style="color:var(--abu-teks); margin:0;">
                {{ $pendaftaran->skema->nama_skema ?? 'Skema Sertifikasi' }} · No. pendaftaran {{ $pendaftaran->nomor_pendaftaran }}
            </p>
        </div>
        <div class="tombol-aksi" style="display:flex; gap:.5rem; flex-wrap:wrap;">
            <a href="{{ route('asesi.hasil-nilai') }}" class="tombol tombol-sekunder">Kembali ke Hasil Nilai</a>
            <button type="button" onclick="window.print()" class="tombol tombol-sekunder">Cetak</button>
        </div>
    </header>

    @if($canSignIa01)
        <section class="hasil-formulir-card" style="border-color:#fbbf24; background:#fffbeb;" role="status">
            <h2 style="font-size:1rem; color:#92400e; margin:0 0 .4rem;">Tanda tangan asesi diperlukan</h2>
            <p style="color:#78350f; margin:0 0 .9rem;">
                Tinjau hasil FR.IA.01, lalu bubuhkan tanda tangan untuk mengesahkan bahwa hasil telah disampaikan kepada Anda.
            </p>
            <a href="{{ route('formulir.ia01', $pendaftaran->id) }}" class="tombol tombol-utama">Buka dan Tanda Tangani FR.IA.01</a>
        </section>
    @endif

    <section class="hasil-formulir-card">
        <h2 style="font-size:1rem; color:var(--biru-malam); margin:0 0 .5rem;">Ringkasan asesmen</h2>
        <div style="display:flex; flex-wrap:wrap; gap:.5rem 1.5rem; color:#475569; font-size:.9rem;">
            <span>Asesor: <strong>{{ $pendaftaran->asesor?->nama_lengkap ?? $pendaftaran->jadwal?->asesor?->nama_lengkap ?? 'Asesor LSP' }}</strong></span>
            <span>Status: <strong>{{ strtoupper($pendaftaran->rekomendasi?->keputusan ?? $pendaftaran->jadwal?->status_jadwal ?? 'SELESAI') }}</strong></span>
            @if($pendaftaran->jadwal?->tanggal_uji)
                <span>Tanggal asesmen: <strong>{{ \Carbon\Carbon::parse($pendaftaran->jadwal->tanggal_uji)->format('d-m-Y') }}</strong></span>
            @endif
        </div>
    </section>

    @forelse($formulir as $item)
        <article class="hasil-formulir-card">
            <div style="display:flex; justify-content:space-between; align-items:flex-start; gap:1rem; flex-wrap:wrap; border-bottom:1px solid #e2e8f0; padding-bottom:.8rem;">
                <div>
                    <div style="font-size:.75rem; font-weight:800; color:#2563eb; letter-spacing:.04em;">{{ $item['code'] }}</div>
                    <h2 style="font-size:1.05rem; color:var(--biru-malam); margin:.2rem 0 0;">{{ $item['name'] }}</h2>
                    @if($item['status'])
                        <small style="display:inline-block; margin-top:.35rem; color:#64748b;">
                            Status formulir:
                            {{ match($item['status']) { 'submitted' => 'Menunggu tanda tangan asesi', 'completed', 'final', 'terkunci' => 'Selesai', default => 'Draft / dalam proses' } }}
                        </small>
                    @endif
                </div>
                @if($item['recommendation'])
                    @php($isCompetent = in_array(strtolower($item['recommendation']), ['k', 'kompeten']))
                    <span class="lencana {{ $isCompetent ? 'lencana-hijau' : 'lencana-merah' }}">
                        {{ $isCompetent ? 'KOMPETEN (K)' : 'BELUM KOMPETEN (BK)' }}
                    </span>
                @endif
            </div>

            @if($item['notes'])
                <p style="margin:.9rem 0 0; color:#334155;"><strong>Catatan:</strong> {{ $item['notes'] }}</p>
            @endif

            @if(count($item['fields']))
                <dl class="hasil-formulir-fields">
                    @foreach($item['fields'] as $field)
                        <dt>{{ $field['label'] }}</dt>
                        <dd>{{ $field['value'] }}</dd>
                    @endforeach
                </dl>
            @else
                <p style="color:#64748b; margin:.9rem 0 0;">Formulir tersimpan tanpa rincian jawaban yang dapat ditampilkan.</p>
            @endif

            @if($item['code'] === 'FR.IA.01')
                <a href="{{ route('formulir.ia01', $pendaftaran->id) }}" class="tombol tombol-sekunder" style="margin-top:1rem;">
                    Lihat lembar resmi FR.IA.01
                </a>
            @endif
        </article>
    @empty
        <section class="hasil-formulir-card" style="text-align:center; color:#64748b;">
            Belum ada formulir penilaian yang tersimpan untuk asesmen ini.
        </section>
    @endforelse
</main>
@endsection
