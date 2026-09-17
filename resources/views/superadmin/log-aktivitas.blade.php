@extends('tata-letak.dasbor')

@section('judul', 'Audit Log Aktivitas')

@push('css')
    <link rel="stylesheet" href="{{ asset('css/superadmin/dashboard-superadmin.css') }}">
@endpush

@section('konten')
<div style="max-width: 1100px;" class="animasi-slide">
    <div style="margin-bottom: 2rem;">
        <h1 style="font-size: 1.8rem; color: var(--biru-malam);">Audit Log Aktivitas Sistem</h1>
        <p style="color: var(--abu-teks);">Rekam jejak audit trail seluruh tindakan penting pengguna di dalam portal LSP</p>
    </div>

    <div class="kartu">
        <div class="tabel-wadah">
            <table class="tabel-custom">
                <thead>
                    <tr>
                        <th>Waktu & Tanggal</th>
                        <th>Pengguna</th>
                        <th>Peran</th>
                        <th>Aktivitas</th>
                        <th>Rincian Tindakan</th>
                        <th>IP Address</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($logList as $log)
                        <tr>
                            <td><small style="color: var(--abu-teks);">{{ date('d M Y, H:i:s', strtotime($log->created_at)) }}</small></td>
                            <td><strong>{{ $log->pengguna->nama_lengkap ?? 'Sistem' }}</strong></td>
                            <td><span class="lencana lencana-biru">{{ ucfirst($log->pengguna->peran ?? 'guest') }}</span></td>
                            <td><strong style="color: var(--biru-utama);">{{ $log->aktivitas }}</strong></td>
                            <td>{{ $log->deskripsi }}</td>
                            <td><code>{{ $log->ip_address }}</code></td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" style="text-align: center; color: var(--abu-teks); padding: 2rem;">Belum ada log aktivitas tercatat.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <div style="margin-top: 1.5rem;">
            {{ $logList->links() }}
        </div>
    </div>
</div>
@endsection
