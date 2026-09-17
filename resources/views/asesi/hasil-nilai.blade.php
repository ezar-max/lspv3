@extends('tata-letak.dasbor')

@section('judul', 'Hasil & Nilai Asesmen')

@push('css')
    <link rel="stylesheet" href="{{ asset('css/asesi/dashboard-asesi.css') }}">
@endpush

@section('konten')
<div style="max-width: 1000px;" class="animasi-slide">
    <div style="margin-bottom: 2rem;">
        <h1 style="font-size: 1.8rem; color: var(--biru-malam);">Hasil & Nilai Kelulusan Uji Kompetensi</h1>
        <p style="color: var(--abu-teks);">Rincian penilaian kompetensi per unit dan status rekomendasi kelulusan asesor</p>
    </div>

    @forelse($pendaftaranList as $p)
        <div class="kartu" style="margin-bottom: 2rem; padding: 2rem;">
            <div style="display: flex; justify-content: space-between; align-items: flex-start; margin-bottom: 1.5rem;">
                <div>
                    <span class="lencana lencana-biru">{{ $p->skema->kode_skema }}</span>
                    <h2 style="color: var(--biru-malam); font-size: 1.4rem; margin-top: 0.25rem;">{{ $p->skema->nama_skema }}</h2>
                    <div style="font-size: 0.88rem; color: var(--abu-teks);">No. Registrasi Pendaftaran: {{ $p->nomor_pendaftaran }}</div>
                </div>
                <div>
                    @if($p->rekomendasi)
                        @if($p->rekomendasi->keputusan === 'kompeten')
                            <span class="lencana lencana-hijau" style="padding: 0.6rem 1.25rem; font-size: 1rem; font-weight: 800;">
                                KOMPETEN (K)
                            </span>
                        @else
                            <span class="lencana lencana-merah" style="padding: 0.6rem 1.25rem; font-size: 1rem; font-weight: 800;">
                                BELUM KOMPETEN (BK)
                            </span>
                        @endif
                    @else
                        <span class="lencana lencana-amber" style="padding: 0.6rem 1.25rem; font-size: 0.9rem;">
                            Proses Penilaian Asesor
                        </span>
                    @endif
                </div>
            </div>

            <h4 style="color: var(--biru-malam); margin-bottom: 0.75rem;">Nilai Per Unit Kompetensi:</h4>
            <div class="tabel-wadah">
                <table class="tabel-custom">
                    <thead>
                        <tr>
                            <th>Kode Unit</th>
                            <th>Judul Unit Kompetensi</th>
                            <th>Status Nilai</th>
                            <th>Catatan Asesor</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($p->penilaian as $nilai)
                            <tr>
                                <td><strong style="color: var(--biru-utama);">{{ $nilai->unit->kode_unit ?? '-' }}</strong></td>
                                <td>{{ $nilai->unit->judul_unit ?? '-' }}</td>
                                <td>
                                    @if($nilai->nilai_kompetensi === 'K')
                                        <span class="lencana lencana-hijau">Kompeten (K)</span>
                                    @else
                                        <span class="lencana lencana-merah">Belum Kompeten (BK)</span>
                                    @endif
                                </td>
                                <td>{{ $nilai->catatan_asesor ?? '-' }}</td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="4" style="text-align: center; color: var(--abu-teks); padding: 1.5rem;">
                                    Instrumen penilaian belum diisi oleh asesor penguji.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            @if($p->rekomendasi)
                <div style="background: var(--biru-bg); padding: 1.25rem; border-radius: var(--radius-md); margin-top: 1.5rem; border: 1px solid var(--biru-soft);">
                    <div style="font-weight: 700; color: var(--biru-malam); margin-bottom: 0.25rem;">Umpan Balik & Catatan Rekomendasi Asesor:</div>
                    <p style="color: var(--hitam-teks); font-size: 0.92rem;">"{{ $p->rekomendasi->catatan_rekomendasi ?? 'Tidak ada catatan tambahan.' }}"</p>
                    <small style="color: var(--abu-teks); display: block; margin-top: 0.5rem;">
                        Penguji: {{ $p->rekomendasi->asesor->nama_lengkap ?? 'Asesor LSP' }} | Tanggal Rekomendasi: {{ date('d F Y', strtotime($p->rekomendasi->tanggal_rekomendasi)) }}
                    </small>
                </div>
            @endif
        </div>
    @empty
        <div class="kartu" style="text-align: center; padding: 3rem; color: var(--abu-teks);">
            Belum ada data nilai uji kompetensi.
        </div>
    @endforelse
</div>
@endsection
