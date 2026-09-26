@extends('tata-letak.dasbor')

@section('judul', 'Jadwal & Pengumuman Asesmen')

@push('css')
    <link rel="stylesheet" href="{{ asset('css/asesi/dashboard-asesi.css') }}">
@endpush

@section('konten')
<div style="max-width: 1000px;" class="animasi-slide">
    <div style="margin-bottom: 2rem;">
        <h1 style="font-size: 1.8rem; color: var(--biru-malam);">Jadwal Pelaksanaan Uji Kompetensi</h1>
        <p style="color: var(--abu-teks);">Jadwal pengujian dan penempatan Tempat Uji Kompetensi (TUK) Anda</p>
    </div>

    <div class="kartu">
        <div class="tabel-wadah">
            <table class="tabel-custom">
                <thead>
                    <tr>
                        <th>No. Pendaftaran</th>
                        <th>Skema Sertifikasi</th>
                        <th>Jadwal & Waktu</th>
                        <th>Lokasi TUK</th>
                        <th>Asesor Penguji</th>
                        <th>Status</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($pendaftaranList as $p)
                        @php
                            $isPendaftaranDitolak = ($p->status_pendaftaran === 'ditolak' || $p->rekomendasi_admin_status === 'tidak_diterima');
                        @endphp
                        <tr>
                            <td><strong>{{ $p->nomor_pendaftaran }}</strong></td>
                            <td>{{ $p->skema->nama_skema }}</td>
                            <td>
                                @if($isPendaftaranDitolak)
                                    <span style="color: var(--merah-tua, #dc2626); font-weight: 600;">Permohonan Ditolak</span>
                                    @if($p->catatan_verifikasi)
                                        <div style="font-size: 0.78rem; color: #b91c1c; margin-top: 0.2rem;">
                                            Catatan: {{ $p->catatan_verifikasi }}
                                        </div>
                                    @endif
                                @elseif($p->jadwal)
                                    <div>{{ date('d F Y', strtotime($p->jadwal->tanggal_uji)) }}</div>
                                    <small style="color: var(--abu-teks);">{{ substr($p->jadwal->waktu_mulai, 0, 5) }} - {{ substr($p->jadwal->waktu_selesai, 0, 5) }} WIB</small>
                                @else
                                    <span style="color: var(--abu-teks);">Belum dijadwalkan</span>
                                @endif
                            </td>
                            <td>{{ $isPendaftaranDitolak ? '-' : ($p->jadwal->nama_tuk ?? '-') }}</td>
                            <td>{{ $isPendaftaranDitolak ? '-' : ($p->jadwal->asesor->nama_lengkap ?? ($p->asesor->nama_lengkap ?? '-')) }}</td>
                            <td>
                                @if($isPendaftaranDitolak)
                                    <span class="lencana lencana-merah" style="font-weight: 700;">&times; Ditolak</span>
                                @elseif($p->jadwal)
                                    @if($p->jadwal->status_jadwal === 'berlangsung')
                                        <span class="lencana lencana-hijau" style="display: inline-flex; align-items: center; gap: 0.35rem;">
                                            <span style="display: inline-block; width: 6px; height: 6px; border-radius: 50%; background-color: #10b981;"></span>
                                            Aktif (Sedang Berlangsung)
                                        </span>
                                    @elseif($p->jadwal->status_jadwal === 'selesai')
                                        <span class="lencana" style="background-color: #f1f5f9; color: #64748b; border: 1px solid #cbd5e1;">Selesai</span>
                                    @elseif($p->jadwal->status_jadwal === 'dibatalkan')
                                        <span class="lencana lencana-merah">Dibatalkan</span>
                                    @else
                                        <span class="lencana lencana-biru">Terjadwal (Belum Mulai)</span>
                                    @endif
                                @elseif($p->status_pendaftaran === 'revisi')
                                    <span class="lencana lencana-amber">Perlu Revisi</span>
                                @elseif($p->status_pendaftaran === 'diajukan')
                                    <span class="lencana lencana-amber">Menunggu Verifikasi Admin</span>
                                @else
                                    <span class="lencana lencana-amber">Menunggu Penugasan</span>
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" style="text-align: center; color: var(--abu-teks); padding: 2rem;">
                                Belum ada jadwal uji kompetensi yang aktif.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection
