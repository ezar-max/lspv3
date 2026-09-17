@extends('tata-letak.dasbor')

@section('judul', 'Super Admin Control Panel')

@push('css')
    <link rel="stylesheet" href="{{ asset('css/superadmin/dashboard-superadmin.css') }}">
@endpush

@section('konten')
<div class="header-superadmin">
    <h1 style="font-size: 1.8rem; margin-bottom: 0.4rem;"><i class="fa-solid fa-shield-halved"></i> Super Admin Control Panel</h1>
    <p style="opacity: 0.9; font-size: 0.95rem;">Manajemen sistem tingkat tinggi, manajemen pengguna semua role, audit log, dan pengaturan global LSP</p>
</div>

<div class="grid-role-stat">
    <div class="kartu" style="border-top: 4px solid var(--biru-malam);">
        <div style="font-size: 0.8rem; color: var(--abu-teks); font-weight: 700;">SUPER ADMIN</div>
        <div style="font-size: 2rem; font-weight: 800; color: var(--biru-malam); margin-top: 0.25rem;">{{ $statistik['superadmin'] }}</div>
    </div>
    <div class="kartu" style="border-top: 4px solid var(--biru-utama);">
        <div style="font-size: 0.8rem; color: var(--abu-teks); font-weight: 700;">ADMINISTRATOR</div>
        <div style="font-size: 2rem; font-weight: 800; color: var(--biru-utama); margin-top: 0.25rem;">{{ $statistik['admin'] }}</div>
    </div>
    <div class="kartu" style="border-top: 4px solid var(--hijau-sukses);">
        <div style="font-size: 0.8rem; color: var(--abu-teks); font-weight: 700;">ASESOR PENGUJI</div>
        <div style="font-size: 2rem; font-weight: 800; color: var(--hijau-sukses); margin-top: 0.25rem;">{{ $statistik['asesor'] }}</div>
    </div>
    <div class="kartu" style="border-top: 4px solid var(--amber-peringatan);">
        <div style="font-size: 0.8rem; color: var(--abu-teks); font-weight: 700;">ASESI (PESERTA)</div>
        <div style="font-size: 2rem; font-weight: 800; color: var(--amber-peringatan); margin-top: 0.25rem;">{{ $statistik['asesi'] }}</div>
    </div>
</div>

<div class="kartu">
    <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 1.25rem;">
        <h3 style="color: var(--biru-malam);"><i class="fa-solid fa-list-check" style="color: var(--biru-utama);"></i> Log Audit Aktivitas Terbaru</h3>
        <a href="{{ route('superadmin.log-aktivitas') }}" class="tombol tombol-sekunder tombol-sm">Lihat Semua Audit Log &rarr;</a>
    </div>

    <div class="tabel-wadah">
        <table class="tabel-custom">
            <thead>
                <tr>
                    <th>Waktu</th>
                    <th>Pengguna</th>
                    <th>Aktivitas</th>
                    <th>Deskripsi Rincian</th>
                    <th>IP Address</th>
                </tr>
            </thead>
            <tbody>
                @forelse($logTerbaru as $log)
                    <tr>
                        <td><small style="color: var(--abu-teks);">{{ date('d/m/Y H:i', strtotime($log->created_at)) }}</small></td>
                        <td><strong>{{ $log->pengguna->nama_lengkap ?? 'Sistem' }}</strong></td>
                        <td><span class="lencana lencana-biru">{{ $log->aktivitas }}</span></td>
                        <td>{{ $log->deskripsi }}</td>
                        <td><code>{{ $log->ip_address }}</code></td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="5" style="text-align: center; color: var(--abu-teks); padding: 2rem;">Belum ada log aktivitas.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>
@endsection
