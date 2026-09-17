@extends('tata-letak.dasbor')

@section('judul', 'Jadwal Penugasan Asesmen')

@push('css')
    <link rel="stylesheet" href="{{ asset('css/asesor/dashboard-asesor.css') }}">
@endpush

@section('konten')
<div style="max-width: 1000px;" class="animasi-slide">
    <div style="margin-bottom: 2rem;">
        <h1 style="font-size: 1.8rem; color: var(--biru-malam);">Jadwal Penugasan Uji Asesmen</h1>
        <p style="color: var(--abu-teks);">Daftar seluruh jadwal pengujian yang ditugaskan kepada Anda sebagai Asesor</p>
    </div>

    <div class="kartu">
        <div class="tabel-wadah">
            <table class="tabel-custom">
                <thead>
                    <tr>
                        <th>Kode Jadwal</th>
                        <th>Skema Sertifikasi</th>
                        <th>Tanggal Pelaksanaan</th>
                        <th>TUK</th>
                        <th>Peserta</th>
                        <th>Status</th>
                        <th>Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($jadwalList as $j)
                        <tr>
                            <td><strong>{{ $j->kode_jadwal }}</strong></td>
                            <td>{{ $j->skema->nama_skema }}</td>
                            <td>{{ date('d M Y', strtotime($j->tanggal_uji)) }}<br><small style="color: var(--abu-teks);">{{ substr($j->waktu_mulai, 0, 5) }} - {{ substr($j->waktu_selesai, 0, 5) }} WIB</small></td>
                            <td>{{ $j->nama_tuk }}</td>
                            <td>{{ $j->pendaftaranAsesi->count() }} / {{ $j->kuota }} Asesi</td>
                            <td>
                                @if($j->status_jadwal === 'berlangsung')
                                    <span class="lencana lencana-hijau" style="display: inline-flex; align-items: center; gap: 0.35rem;">
                                        <span style="display: inline-block; width: 6px; height: 6px; border-radius: 50%; background-color: #10b981;"></span>
                                        Aktif (Berlangsung)
                                    </span>
                                @elseif($j->status_jadwal === 'selesai')
                                    <span class="lencana" style="background-color: #f1f5f9; color: #64748b; border: 1px solid #cbd5e1;">Selesai</span>
                                @elseif($j->status_jadwal === 'dibatalkan')
                                    <span class="lencana lencana-merah">Dibatalkan</span>
                                @else
                                    <span class="lencana lencana-biru">Terjadwal (Belum Mulai)</span>
                                @endif
                            </td>
                            <td>
                                <a href="{{ route('asesor.daftar-peserta', ['jadwal_id' => $j->id]) }}" class="tombol tombol-utama tombol-sm">
                                    Peserta
                                </a>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" style="text-align: center; color: var(--abu-teks); padding: 2rem;">Belum ada penugasan jadwal.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection
