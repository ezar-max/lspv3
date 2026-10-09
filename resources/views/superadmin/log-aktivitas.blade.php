@extends('tata-letak.dasbor')

@section('judul', 'Audit Log & Rekam Aktivitas - Super Admin')

@section('konten')
<div class="space-y-6 max-w-7xl mx-auto pb-12">

    <!-- HEADER SECTION (BRIGHT THEME, NO ICONS) -->
    <div class="bg-white rounded-3xl p-6 sm:p-8 border border-slate-200 shadow-xs">
        <div class="flex flex-col md:flex-row md:items-center justify-between gap-6">
            <div class="space-y-2">
                <div class="inline-block px-3 py-1 rounded-md bg-blue-50 border border-blue-200 text-blue-700 text-xs font-bold tracking-wide uppercase">
                    Security & Audit Trail
                </div>
                <h1 class="text-2xl sm:text-3xl font-black tracking-tight text-slate-900 leading-tight">
                    Audit Log Aktivitas Sistem
                </h1>
                <p class="text-slate-600 text-xs sm:text-sm max-w-2xl leading-relaxed">
                    Rekam jejak digital terenkripsi yang mencatat seluruh tindakan krusial pengguna, perubahan profil, verifikasi berkas, dan aktivitas sistem lainnya.
                </p>
            </div>

            <div class="flex items-center gap-3 shrink-0">
                <a href="{{ route('superadmin.dashboard') }}" 
                   class="px-4 py-2.5 rounded-xl bg-slate-100 hover:bg-slate-200 text-slate-700 font-semibold text-xs sm:text-sm border border-slate-200 transition-colors">
                    Kembali ke Panel
                </a>
            </div>
        </div>
    </div>

    <!-- TABLE CARD (BRIGHT THEME, NO ICONS) -->
    <div class="bg-white rounded-3xl border border-slate-200 shadow-xs overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-left border-collapse">
                <thead>
                    <tr class="bg-slate-50 border-b border-slate-200 text-[11px] font-extrabold text-slate-500 uppercase tracking-wider">
                        <th class="py-3.5 px-6">Waktu & Tanggal</th>
                        <th class="py-3.5 px-4">Pengguna</th>
                        <th class="py-3.5 px-4">Hak Akses</th>
                        <th class="py-3.5 px-4">Tindakan / Event</th>
                        <th class="py-3.5 px-6">Rincian Perubahan Data</th>
                        <th class="py-3.5 px-6 text-right">IP Address</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100 text-xs sm:text-sm">
                    @forelse($logList as $log)
                        @php
                            $roleBadge = match($log->pengguna?->peran) {
                                'superadmin' => ['bg' => 'bg-slate-100', 'text' => 'text-slate-900', 'border' => 'border-slate-300'],
                                'admin' => ['bg' => 'bg-emerald-50', 'text' => 'text-emerald-800', 'border' => 'border-emerald-200'],
                                'asesor' => ['bg' => 'bg-amber-50', 'text' => 'text-amber-800', 'border' => 'border-amber-200'],
                                'asesi' => ['bg' => 'bg-blue-50', 'text' => 'text-blue-800', 'border' => 'border-blue-200'],
                                default => ['bg' => 'bg-slate-100', 'text' => 'text-slate-600', 'border' => 'border-slate-200'],
                            };
                        @endphp
                        <tr class="hover:bg-slate-50 transition-colors">
                            <td class="py-4 px-6 text-slate-600 whitespace-nowrap text-xs">
                                <div class="font-semibold text-slate-800">{{ date('d M Y', strtotime($log->created_at)) }}</div>
                                <div class="text-[10px] text-slate-400">{{ date('H:i:s', strtotime($log->created_at)) }} WIB</div>
                            </td>
                            <td class="py-4 px-4">
                                <div class="font-bold text-slate-900">{{ $log->pengguna->nama_lengkap ?? 'Sistem Otomatis' }}</div>
                                <div class="text-[11px] text-slate-400 truncate max-w-xs">{{ $log->pengguna->email ?? '-' }}</div>
                            </td>
                            <td class="py-4 px-4 whitespace-nowrap">
                                <span class="px-2.5 py-0.5 rounded text-[11px] font-bold border {{ $roleBadge['bg'] }} {{ $roleBadge['text'] }} {{ $roleBadge['border'] }}">
                                    {{ strtoupper($log->pengguna?->peran ?? 'SYSTEM') }}
                                </span>
                            </td>
                            <td class="py-4 px-4 whitespace-nowrap">
                                <span class="px-2.5 py-1 rounded text-xs font-semibold bg-slate-100 text-slate-800 border border-slate-200 inline-block">
                                    {{ $log->aktivitas }}
                                </span>
                            </td>
                            <td class="py-4 px-6 text-slate-600 text-xs max-w-md">
                                {{ $log->deskripsi }}
                            </td>
                            <td class="py-4 px-6 text-right whitespace-nowrap">
                                <code class="px-2 py-0.5 rounded bg-slate-100 text-slate-600 font-mono text-[11px] border border-slate-200">
                                    {{ $log->ip_address ?? '127.0.0.1' }}
                                </code>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="py-12 text-center text-slate-400">
                                <div class="font-bold text-slate-700 text-base">Belum Ada Log Aktivitas</div>
                                <p class="text-xs text-slate-400 max-w-sm mx-auto mt-1">Aktivitas pengguna akan tercatat otomatis di sini.</p>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if($logList->hasPages())
            <div class="p-4 sm:p-5 border-t border-slate-100 bg-slate-50/50">
                {{ $logList->links() }}
            </div>
        @endif
    </div>

</div>
@endsection
