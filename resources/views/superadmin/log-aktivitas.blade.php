@extends('tata-letak.dasbor')

@section('judul', 'Audit Log & Rekam Aktivitas - Super Admin')

@section('konten')
<div class="space-y-6 max-w-7xl mx-auto pb-12">

    <!-- EXECUTIVE HERO HEADER -->
    <div class="bg-white rounded-3xl p-6 sm:p-8 border border-slate-200/90 shadow-2xs">
        <div class="flex flex-col md:flex-row md:items-center justify-between gap-6">
            <div class="flex items-start sm:items-center gap-4 sm:gap-5">
                <div class="w-14 h-14 sm:w-16 sm:h-16 rounded-2xl bg-blue-50 border border-blue-200/80 text-blue-600 flex items-center justify-center text-2xl sm:text-3xl shrink-0 shadow-2xs">
                    <i class="fa-solid fa-clock-rotate-left"></i>
                </div>
                <div class="space-y-1.5 min-w-0">
                    <div class="flex flex-wrap items-center gap-2">
                        <span class="inline-flex items-center gap-1.5 px-2.5 py-0.5 rounded-full bg-blue-50 border border-blue-200/80 text-blue-700 text-[11px] font-extrabold uppercase tracking-wider">
                            <span class="w-1.5 h-1.5 rounded-full bg-blue-600 animate-pulse"></span>
                            <span>Security &amp; Audit Trail</span>
                        </span>
                        <span class="text-slate-300 hidden sm:inline">&bull;</span>
                        <span class="text-xs font-semibold text-slate-500">Rekam Jejak Aktivitas Terenkripsi</span>
                    </div>
                    <h1 class="text-2xl sm:text-3xl font-black tracking-tight text-slate-900 leading-tight">
                        Audit Log Aktivitas Sistem
                    </h1>
                    <p class="text-slate-600 text-xs sm:text-sm max-w-2xl leading-relaxed">
                        Rekam jejak digital komprehensif yang mencatat seluruh tindakan krusial pengguna, pembaruan profil, verifikasi dokumen asesi, perubahan skema, dan event keamanan sistem.
                    </p>
                </div>
            </div>

            <div class="flex items-center gap-3 shrink-0 self-start md:self-center">
                <a href="{{ route('superadmin.dashboard') }}" 
                   class="inline-flex items-center gap-2 px-4 py-2.5 rounded-xl bg-slate-100 hover:bg-slate-200 text-slate-700 font-bold text-xs sm:text-sm border border-slate-200 transition-colors">
                    <i class="fa-solid fa-arrow-left text-xs text-slate-500"></i>
                    <span>Kembali ke Panel</span>
                </a>
            </div>
        </div>
    </div>

    <!-- TABLE CARD -->
    <div class="bg-white rounded-3xl border border-slate-200/90 shadow-2xs overflow-hidden">
        <div class="p-5 border-b border-slate-100 bg-white flex flex-col sm:flex-row sm:items-center justify-between gap-3">
            <div class="flex items-center gap-2.5">
                <span class="w-2 h-2 rounded-full bg-blue-600"></span>
                <h2 class="font-bold text-sm text-slate-800 tracking-tight">Daftar Rekam Jejak Aktivitas Terakhir</h2>
            </div>
            <div class="text-xs text-slate-400 font-medium">
                Total entri tercatat: <span class="font-bold text-slate-700">{{ $logList->total() }}</span> log
            </div>
        </div>

        <div class="overflow-x-auto">
            <table class="w-full text-left border-collapse">
                <thead>
                    <tr class="bg-slate-50/80 border-b border-slate-200 text-[11px] font-extrabold text-slate-500 uppercase tracking-wider">
                        <th class="py-4 px-6">Waktu &amp; Tanggal</th>
                        <th class="py-4 px-4">Pengguna</th>
                        <th class="py-4 px-4">Hak Akses</th>
                        <th class="py-4 px-4">Tindakan / Event</th>
                        <th class="py-4 px-6">Rincian Perubahan Data</th>
                        <th class="py-4 px-6 text-right">IP Address</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100 text-xs sm:text-sm">
                    @forelse($logList as $log)
                        @php
                            $roleBadge = match($log->pengguna?->peran) {
                                'superadmin' => [
                                    'bg' => 'bg-blue-50',
                                    'text' => 'text-blue-800',
                                    'border' => 'border-blue-200',
                                    'icon' => 'fa-solid fa-shield-halved',
                                    'label' => 'SUPER ADMIN'
                                ],
                                'admin' => [
                                    'bg' => 'bg-emerald-50',
                                    'text' => 'text-emerald-800',
                                    'border' => 'border-emerald-200',
                                    'icon' => 'fa-solid fa-user-gear',
                                    'label' => 'ADMIN LSP'
                                ],
                                'asesor' => [
                                    'bg' => 'bg-amber-50',
                                    'text' => 'text-amber-800',
                                    'border' => 'border-amber-200',
                                    'icon' => 'fa-solid fa-user-tie',
                                    'label' => 'ASESOR'
                                ],
                                'asesi' => [
                                    'bg' => 'bg-sky-50',
                                    'text' => 'text-sky-800',
                                    'border' => 'border-sky-200',
                                    'icon' => 'fa-solid fa-user-graduate',
                                    'label' => 'ASESI'
                                ],
                                default => [
                                    'bg' => 'bg-slate-100',
                                    'text' => 'text-slate-700',
                                    'border' => 'border-slate-200',
                                    'icon' => 'fa-solid fa-robot',
                                    'label' => 'SYSTEM'
                                ],
                            };
                        @endphp
                        <tr class="hover:bg-slate-50/80 transition-colors">
                            <td class="py-4 px-6 whitespace-nowrap text-xs">
                                <div class="font-bold text-slate-800 flex items-center gap-1.5">
                                    <i class="fa-regular fa-calendar text-[11px] text-slate-400"></i>
                                    <span>{{ date('d M Y', strtotime($log->created_at)) }}</span>
                                </div>
                                <div class="text-[11px] text-slate-400 mt-0.5 flex items-center gap-1">
                                    <i class="fa-regular fa-clock text-[10px]"></i>
                                    <span>{{ date('H:i:s', strtotime($log->created_at)) }} WIB</span>
                                </div>
                            </td>
                            <td class="py-4 px-4">
                                <div class="font-bold text-slate-900 flex items-center gap-1.5">
                                    <span>{{ $log->pengguna->nama_lengkap ?? 'Sistem Otomatis' }}</span>
                                </div>
                                <div class="text-[11px] text-slate-400 truncate max-w-xs mt-0.5">
                                    {{ $log->pengguna->email ?? 'Sistem Internal' }}
                                </div>
                            </td>
                            <td class="py-4 px-4 whitespace-nowrap">
                                <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-lg text-xs font-bold border {{ $roleBadge['bg'] }} {{ $roleBadge['text'] }} {{ $roleBadge['border'] }}">
                                    <i class="{{ $roleBadge['icon'] }} text-[11px]"></i>
                                    <span>{{ $roleBadge['label'] }}</span>
                                </span>
                            </td>
                            <td class="py-4 px-4 whitespace-nowrap">
                                <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-lg text-xs font-bold bg-blue-50/70 text-blue-800 border border-blue-200/80">
                                    <span class="w-1.5 h-1.5 rounded-full bg-blue-600"></span>
                                    <span>{{ $log->aktivitas }}</span>
                                </span>
                            </td>
                            <td class="py-4 px-6 text-slate-600 text-xs max-w-md leading-relaxed">
                                {{ $log->deskripsi }}
                            </td>
                            <td class="py-4 px-6 text-right whitespace-nowrap">
                                <code class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-lg bg-slate-100 text-slate-700 font-mono text-[11px] border border-slate-200">
                                    <i class="fa-solid fa-network-wired text-[10px] text-slate-400"></i>
                                    <span>{{ $log->ip_address ?? '127.0.0.1' }}</span>
                                </code>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="py-12 text-center text-slate-400">
                                <div class="w-12 h-12 rounded-2xl bg-slate-100 text-slate-400 flex items-center justify-center mx-auto mb-3 text-lg">
                                    <i class="fa-solid fa-clock-rotate-left"></i>
                                </div>
                                <div class="font-bold text-slate-700 text-base">Belum Ada Log Aktivitas</div>
                                <p class="text-xs text-slate-400 max-w-sm mx-auto mt-1">Seluruh aktivitas pengguna dan sistem akan otomatis terekam secara real-time di sini.</p>
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
