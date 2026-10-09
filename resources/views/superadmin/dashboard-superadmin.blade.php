@extends('tata-letak.dasbor')

@section('judul', 'Control Panel & Overview - Super Admin')

@section('konten')
<div class="space-y-6 max-w-7xl mx-auto pb-12">

    <!-- HERO HEADER CARD (BRIGHT THEME, NO ICONS) -->
    <div class="bg-white rounded-3xl p-6 sm:p-8 border border-slate-200 shadow-xs">
        <div class="flex flex-col md:flex-row md:items-center justify-between gap-6">
            <div class="space-y-2">
                <div class="inline-block px-3 py-1 rounded-md bg-blue-50 border border-blue-200 text-blue-700 text-xs font-bold tracking-wide uppercase">
                    Master Operational Overview
                </div>
                <h1 class="text-2xl sm:text-3xl font-black tracking-tight text-slate-900 leading-tight">
                    Super Admin Control Panel
                </h1>
                <p class="text-slate-600 text-xs sm:text-sm max-w-2xl leading-relaxed">
                    Pusat kendali master sistem sertifikasi LSP SMKN 1 Gunungputri. Anda memiliki wewenang penuh untuk mengelola akun pengguna, skema sertifikasi, audit rekam jejak, dan parameter global sistem.
                </p>
            </div>

            <div class="flex flex-wrap items-center gap-3 shrink-0">
                <a href="{{ route('superadmin.manajemen-pengguna') }}" 
                   class="px-5 py-2.5 rounded-xl bg-blue-600 hover:bg-blue-700 text-white font-bold text-xs sm:text-sm shadow-xs transition-colors">
                    Kelola Pengguna
                </a>
                <a href="{{ route('admin.dashboard') }}" 
                   class="px-5 py-2.5 rounded-xl bg-slate-100 hover:bg-slate-200 text-slate-800 font-bold text-xs sm:text-sm border border-slate-200 transition-colors">
                    Portal Admin
                </a>
            </div>
        </div>
    </div>

    <!-- PRIMARY METRICS KPI (BRIGHT, NO ICONS) -->
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
        <!-- 1. TOTAL SKEMA -->
        <div class="p-5 rounded-2xl bg-white border border-slate-200 shadow-xs">
            <div class="text-xs font-bold text-slate-500 uppercase tracking-wider">Skema Sertifikasi</div>
            <div class="text-3xl font-black text-slate-900 mt-2">{{ number_format($totalSkema ?? 0) }}</div>
            <div class="text-xs text-blue-700 font-semibold mt-1">Standar BNSP Aktif</div>
        </div>

        <!-- 2. TOTAL PENDAFTARAN -->
        <div class="p-5 rounded-2xl bg-white border border-slate-200 shadow-xs">
            <div class="text-xs font-bold text-slate-500 uppercase tracking-wider">Pendaftaran Asesi</div>
            <div class="text-3xl font-black text-slate-900 mt-2">{{ number_format($totalPendaftaran ?? 0) }}</div>
            <div class="text-xs text-slate-600 font-medium mt-1">Total Permohonan Uji</div>
        </div>

        <!-- 3. PENDING VERIFIKASI -->
        <div class="p-5 rounded-2xl bg-white border border-slate-200 shadow-xs">
            <div class="text-xs font-bold text-slate-500 uppercase tracking-wider">Menunggu Verifikasi</div>
            <div class="text-3xl font-black text-amber-600 mt-2">{{ number_format($totalPending ?? 0) }}</div>
            <div class="text-xs text-amber-700 font-semibold mt-1">Perlu Tinjauan Admin</div>
        </div>

        <!-- 4. TOTAL PENGGUNA TERDAFTAR -->
        @php
            $totalAkun = ($statistik['superadmin'] ?? 0) + ($statistik['admin'] ?? 0) + ($statistik['asesor'] ?? 0) + ($statistik['asesi'] ?? 0);
        @endphp
        <div class="p-5 rounded-2xl bg-white border border-slate-200 shadow-xs">
            <div class="text-xs font-bold text-slate-500 uppercase tracking-wider">Total Seluruh Akun</div>
            <div class="text-3xl font-black text-slate-900 mt-2">{{ number_format($totalAkun) }}</div>
            <div class="text-xs text-slate-600 font-medium mt-1">Asesi, Asesor & Admin</div>
        </div>
    </div>

    <!-- DISTRIBUSI PENGGUNA PER PERAN (BRIGHT, NO ICONS) -->
    <div class="bg-white rounded-3xl p-6 border border-slate-200 shadow-xs">
        <div class="flex items-center justify-between mb-5">
            <div>
                <h3 class="text-base font-bold text-slate-900">Distribusi Akun & Hak Akses Pengguna</h3>
                <p class="text-xs text-slate-500">Rincian proporsi pengguna terdaftar berdasarkan peran sistem</p>
            </div>
            <a href="{{ route('superadmin.manajemen-pengguna') }}" class="text-xs font-bold text-blue-600 hover:text-blue-800">
                Kelola Semua Akun &rarr;
            </a>
        </div>

        <div class="grid grid-cols-2 sm:grid-cols-4 gap-4">
            <!-- SUPERADMIN -->
            <a href="{{ route('superadmin.manajemen-pengguna', ['peran' => 'superadmin']) }}" 
               class="p-4 rounded-2xl bg-slate-50 border border-slate-200 hover:border-slate-300 transition-colors block">
                <div class="text-xs font-bold text-slate-700 mb-1">Super Admin</div>
                <div class="text-2xl font-black text-slate-900">{{ $statistik['superadmin'] ?? 0 }}</div>
                <div class="text-[11px] text-slate-500 mt-1">Akses Penuh Master</div>
            </a>

            <!-- ADMIN -->
            <a href="{{ route('superadmin.manajemen-pengguna', ['peran' => 'admin']) }}" 
               class="p-4 rounded-2xl bg-slate-50 border border-slate-200 hover:border-slate-300 transition-colors block">
                <div class="text-xs font-bold text-emerald-800 mb-1">Admin LSP</div>
                <div class="text-2xl font-black text-emerald-950">{{ $statistik['admin'] ?? 0 }}</div>
                <div class="text-[11px] text-emerald-700 mt-1">Staf & Verifikator</div>
            </a>

            <!-- ASESOR -->
            <a href="{{ route('superadmin.manajemen-pengguna', ['peran' => 'asesor']) }}" 
               class="p-4 rounded-2xl bg-slate-50 border border-slate-200 hover:border-slate-300 transition-colors block">
                <div class="text-xs font-bold text-amber-800 mb-1">Asesor Penguji</div>
                <div class="text-2xl font-black text-amber-950">{{ $statistik['asesor'] ?? 0 }}</div>
                <div class="text-[11px] text-amber-700 mt-1">Lisensi Uji MET</div>
            </a>

            <!-- ASESI -->
            <a href="{{ route('superadmin.manajemen-pengguna', ['peran' => 'asesi']) }}" 
               class="p-4 rounded-2xl bg-slate-50 border border-slate-200 hover:border-slate-300 transition-colors block">
                <div class="text-xs font-bold text-blue-800 mb-1">Asesi (Peserta)</div>
                <div class="text-2xl font-black text-blue-950">{{ $statistik['asesi'] ?? 0 }}</div>
                <div class="text-[11px] text-blue-700 mt-1">Peserta Sertifikasi</div>
            </a>
        </div>
    </div>

    <!-- PUSAT PINTASAN MODUL (BRIGHT, NO ICONS) -->
    <div class="space-y-3">
        <div class="flex items-center justify-between px-1">
            <h3 class="text-xs font-extrabold uppercase tracking-wider text-slate-500">Pusat Navigasi & Modul Terpadu</h3>
            <span class="text-[11px] text-slate-400">Akses langsung ke seluruh subsistem admin</span>
        </div>

        <div class="grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-6 gap-3 sm:gap-4">
            <!-- 1. MANAJEMEN ASESI -->
            <a href="{{ route('admin.manajemen-asesi') }}" 
               class="p-4 rounded-2xl bg-white border border-slate-200 shadow-xs hover:border-blue-400 hover:shadow-xs transition-all block">
                <div class="font-bold text-slate-900 text-xs">Verifikasi Asesi</div>
                <div class="text-[11px] text-slate-500 mt-1">Berkas APL.01</div>
            </a>

            <!-- 2. MASTER SKEMA -->
            <a href="{{ route('admin.manajemen-skema') }}" 
               class="p-4 rounded-2xl bg-white border border-slate-200 shadow-xs hover:border-blue-400 hover:shadow-xs transition-all block">
                <div class="font-bold text-slate-900 text-xs">Master Skema</div>
                <div class="text-[11px] text-slate-500 mt-1">Unit & Kategori</div>
            </a>

            <!-- 3. JADWAL ASESMEN -->
            <a href="{{ route('admin.manajemen-jadwal') }}" 
               class="p-4 rounded-2xl bg-white border border-slate-200 shadow-xs hover:border-blue-400 hover:shadow-xs transition-all block">
                <div class="font-bold text-slate-900 text-xs">Jadwal Asesmen</div>
                <div class="text-[11px] text-slate-500 mt-1">Event & Jadwal</div>
            </a>

            <!-- 4. MASTER MUK -->
            <a href="{{ route('admin.master-muk.index') }}" 
               class="p-4 rounded-2xl bg-white border border-slate-200 shadow-xs hover:border-blue-400 hover:shadow-xs transition-all block">
                <div class="font-bold text-slate-900 text-xs">MUK & Bank Soal</div>
                <div class="text-[11px] text-slate-500 mt-1">Formulir IA.01 - 11</div>
            </a>

            <!-- 5. DOKUMEN HUB -->
            <a href="{{ route('dokumen-asesmen.index') }}" 
               class="p-4 rounded-2xl bg-white border border-slate-200 shadow-xs hover:border-blue-400 hover:shadow-xs transition-all block">
                <div class="font-bold text-slate-900 text-xs">Dokumen Asesmen</div>
                <div class="text-[11px] text-slate-500 mt-1">Pusat AK & VA</div>
            </a>

            <!-- 6. PENGATURAN GLOBAL -->
            <a href="{{ route('superadmin.pengaturan-sistem') }}" 
               class="p-4 rounded-2xl bg-white border border-slate-200 shadow-xs hover:border-blue-400 hover:shadow-xs transition-all block">
                <div class="font-bold text-slate-900 text-xs">Pengaturan Sistem</div>
                <div class="text-[11px] text-slate-500 mt-1">Lisensi & Lembaga</div>
            </a>
        </div>
    </div>

    <!-- AUDIT TRAIL LOG TERKINI (BRIGHT, NO ICONS) -->
    <div class="bg-white rounded-3xl border border-slate-200 shadow-xs overflow-hidden">
        <div class="p-5 sm:p-6 border-b border-slate-100 flex items-center justify-between">
            <div>
                <h3 class="text-base font-bold text-slate-900 leading-tight">Jejak Aktivitas & Audit Log Terkini</h3>
                <p class="text-xs text-slate-500 mt-0.5">Catatan aktivitas penting pengguna dan perubahan data dalam sistem</p>
            </div>
            <a href="{{ route('superadmin.log-aktivitas') }}" 
               class="px-4 py-2 rounded-xl bg-slate-100 hover:bg-slate-200 text-slate-700 font-semibold text-xs transition-colors">
                Lihat Seluruh Log &rarr;
            </a>
        </div>

        <div class="overflow-x-auto">
            <table class="w-full text-left border-collapse">
                <thead>
                    <tr class="bg-slate-50 border-b border-slate-200 text-[11px] font-extrabold text-slate-500 uppercase tracking-wider">
                        <th class="py-3 px-6">Waktu</th>
                        <th class="py-3 px-4">Pengguna</th>
                        <th class="py-3 px-4">Aktivitas</th>
                        <th class="py-3 px-6">Deskripsi Rincian</th>
                        <th class="py-3 px-6 text-right">IP Address</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100 text-xs sm:text-sm">
                    @forelse($logTerbaru as $log)
                        <tr class="hover:bg-slate-50 transition-colors">
                            <td class="py-3.5 px-6 text-slate-500 text-xs whitespace-nowrap">
                                <div class="font-medium text-slate-700">{{ date('d M Y', strtotime($log->created_at)) }}</div>
                                <div class="text-[10px] text-slate-400">{{ date('H:i:s', strtotime($log->created_at)) }} WIB</div>
                            </td>
                            <td class="py-3.5 px-4">
                                <div class="font-bold text-slate-900">{{ $log->pengguna->nama_lengkap ?? 'Sistem LSP' }}</div>
                                <div class="text-[10px] text-slate-400 capitalize">{{ $log->pengguna->peran ?? 'System Event' }}</div>
                            </td>
                            <td class="py-3.5 px-4 whitespace-nowrap">
                                <span class="px-2.5 py-1 rounded-md text-xs font-semibold bg-slate-100 text-slate-700 border border-slate-200">
                                    {{ $log->aktivitas }}
                                </span>
                            </td>
                            <td class="py-3.5 px-6 text-slate-600 text-xs">
                                {{ $log->deskripsi }}
                            </td>
                            <td class="py-3.5 px-6 text-right">
                                <code class="px-2 py-0.5 rounded bg-slate-100 text-slate-600 font-mono text-[11px] border border-slate-200">
                                    {{ $log->ip_address ?? '127.0.0.1' }}
                                </code>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="py-8 text-center text-slate-400 text-xs">
                                Belum ada catatan aktivitas log tercatat.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

</div>
@endsection
