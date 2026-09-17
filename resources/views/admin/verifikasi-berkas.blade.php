@extends('tata-letak.dasbor')

@section('judul', 'Verifikasi Berkas APL')

@push('css')
    <link rel="stylesheet" href="{{ asset('css/admin/dashboard-admin.css') }}">
@endpush

@section('konten')
<div style="max-width: 1100px;" class="animasi-slide">
    <div style="margin-bottom: 2rem;">
        <h1 style="font-size: 1.8rem; color: var(--biru-malam);">Verifikasi Berkas Pendaftaran (APL-01 & APL-02)</h1>
        <p style="color: var(--abu-teks);">Periksa keabsahan dokumen persyaratan dasar dan portofolio asesi</p>
    </div>

    <!-- TABS FILTER -->
    <div style="display: flex; gap: 0.5rem; margin-bottom: 1.5rem;">
        <a href="{{ route('admin.verifikasi-berkas', ['status' => 'diajukan']) }}" class="tombol {{ $status === 'diajukan' ? 'tombol-utama' : 'tombol-sekunder' }} tombol-sm">
            Perlu Verifikasi (Diajukan)
        </a>
        <a href="{{ route('admin.verifikasi-berkas', ['status' => 'diverifikasi']) }}" class="tombol {{ $status === 'diverifikasi' ? 'tombol-utama' : 'tombol-sekunder' }} tombol-sm">
            Terverifikasi (Disetujui)
        </a>
        <a href="{{ route('admin.verifikasi-berkas', ['status' => 'semua']) }}" class="tombol {{ $status === 'semua' ? 'tombol-utama' : 'tombol-sekunder' }} tombol-sm">
            Semua Pendaftaran
        </a>
    </div>

    <div class="kartu">
        <div class="tabel-wadah">
            <table class="tabel-custom">
                <thead>
                    <tr>
                        <th>No. Pendaftaran</th>
                        <th>Nama Asesi</th>
                        <th>Skema Sertifikasi</th>
                        <th>Jumlah Berkas</th>
                        <th>Status</th>
                        <th>Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($pendaftaranList as $p)
                        <tr>
                            <td><span class="font-mono">{{ $p->nomor_pendaftaran }}</span></td>
                            <td>
                                <strong style="color: var(--biru-malam);">{{ $p->asesi->nama_lengkap }}</strong><br>
                                <small style="color: var(--abu-teks);">Instansi: {{ $p->asesi->profilAsesi->nama_sekolah_instansi ?? '-' }}</small>
                            </td>
                            <td>{{ $p->skema->nama_skema }}</td>
                            <td><span class="lencana lencana-biru">{{ $p->dokumen->count() }} File</span></td>
                            <td>
                                @if($p->status_pendaftaran === 'diajukan')
                                    <span class="lencana lencana-amber">Diajukan (Pending)</span>
                                @elseif($p->status_pendaftaran === 'diverifikasi')
                                    <span class="lencana lencana-hijau">Disetujui Admin</span>
                                @else
                                    <span class="lencana lencana-merah">Ditolak</span>
                                @endif
                            </td>
                            <td>
                                <a href="{{ route('admin.detail-verifikasi', $p->id) }}" class="tombol tombol-utama tombol-sm">
                                    Periksa Berkas
                                </a>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" style="text-align: center; color: var(--abu-teks); padding: 2rem;">
                                Tidak ada data pendaftaran untuk diverifikasi.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <div style="margin-top: 1.5rem;">
            {{ $pendaftaranList->links() }}
        </div>
    </div>
</div>
@endsection
