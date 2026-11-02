@extends('tata-letak.dasbor')

@section('judul', 'Control Panel & Overview - Super Admin')

@section('konten')
<div class="space-y-6 max-w-7xl mx-auto pb-12 animasi-slide">

    <!-- HERO HEADER CARD -->
    <div class="bg-white rounded-2xl p-6 sm:p-7 border border-slate-200/90 shadow-2xs">
        <div class="flex flex-col md:flex-row md:items-center justify-between gap-6">
            <div class="space-y-2">
                <div class="inline-flex items-center gap-2 px-2.5 py-1 rounded-lg bg-blue-50 border border-blue-200/80 text-blue-700 text-xs font-bold">
                    <i class="fa-solid fa-shield-halved text-blue-600 text-[11px]"></i>
                    <span>Master Operational Control</span>
                </div>
                <h1 class="text-2xl sm:text-3xl font-extrabold tracking-tight text-slate-900 leading-tight">
                    Super Admin Control Panel
                </h1>
                <p class="text-slate-600 text-xs sm:text-sm max-w-2xl leading-relaxed">
                    Pusat kendali master sistem sertifikasi LSP SMKN 1 Gunungputri. Kelola hak akses seluruh pengguna, skema sertifikasi BNSP, pemantauan log audit, dan konfigurasi global sistem.
                </p>
            </div>

            <div class="flex flex-wrap items-center gap-3 shrink-0">
                <a href="{{ route('superadmin.manajemen-pengguna') }}" 
                   class="inline-flex items-center gap-2 px-4 py-2.5 rounded-xl bg-blue-600 hover:bg-blue-700 text-white font-bold text-xs sm:text-sm shadow-xs transition-colors">
                    <i class="fa-solid fa-user-plus text-xs"></i>
                    <span>Kelola Pengguna</span>
                </a>
                <a href="{{ route('admin.dashboard') }}" 
                   class="inline-flex items-center gap-2 px-4 py-2.5 rounded-xl bg-white hover:bg-slate-50 text-slate-700 font-bold text-xs sm:text-sm border border-slate-200 shadow-2xs transition-colors">
                    <i class="fa-solid fa-chart-pie text-xs text-slate-400"></i>
                    <span>Portal Admin</span>
                </a>
            </div>
        </div>
    </div>

    <!-- PRIMARY METRICS KPI (4 CARDS DENGAN IKON & WARNA BIRU/PUTIH) -->
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
        <!-- 1. TOTAL SKEMA -->
        <div class="p-5 rounded-2xl bg-white border border-slate-200/90 shadow-2xs hover:border-slate-300 transition-all flex items-start justify-between">
            <div>
                <div class="text-[11px] font-bold text-slate-400 uppercase tracking-wider">Skema Sertifikasi</div>
                <div class="text-2xl sm:text-3xl font-black text-slate-900 mt-1.5">{{ number_format($totalSkema ?? 0) }}</div>
                <div class="text-[11px] text-blue-600 font-bold mt-1 inline-flex items-center gap-1">
                    <i class="fa-solid fa-check-circle text-[10px]"></i>
                    <span>Standar BNSP Aktif</span>
                </div>
            </div>
            <div class="w-11 h-11 rounded-xl bg-blue-50 text-blue-600 flex items-center justify-center text-lg shrink-0 border border-blue-100/80">
                <i class="fa-solid fa-certificate"></i>
            </div>
        </div>

        <!-- 2. TOTAL PENDAFTARAN -->
        <div class="p-5 rounded-2xl bg-white border border-slate-200/90 shadow-2xs hover:border-slate-300 transition-all flex items-start justify-between">
            <div>
                <div class="text-[11px] font-bold text-slate-400 uppercase tracking-wider">Total Pendaftaran</div>
                <div class="text-2xl sm:text-3xl font-black text-slate-900 mt-1.5">{{ number_format($totalPendaftaran ?? 0) }}</div>
                <div class="text-[11px] text-slate-500 font-medium mt-1 inline-flex items-center gap-1">
                    <i class="fa-solid fa-file-signature text-[10px] text-slate-400"></i>
                    <span>Permohonan Sertifikasi</span>
                </div>
            </div>
            <div class="w-11 h-11 rounded-xl bg-indigo-50 text-indigo-600 flex items-center justify-center text-lg shrink-0 border border-indigo-100/80">
                <i class="fa-solid fa-clipboard-list"></i>
            </div>
        </div>

        <!-- 3. MENUNGGU VERIFIKASI -->
        <a href="{{ route('admin.manajemen-asesi', ['status' => 'diajukan']) }}" 
           class="p-5 rounded-2xl bg-white border border-slate-200/90 shadow-2xs hover:border-amber-300 transition-all flex items-start justify-between block group">
            <div>
                <div class="text-[11px] font-bold text-amber-700 uppercase tracking-wider">Menunggu Verifikasi</div>
                <div class="text-2xl sm:text-3xl font-black text-amber-600 mt-1.5">{{ number_format($totalPending ?? 0) }}</div>
                <div class="text-[11px] text-amber-700 font-bold mt-1 inline-flex items-center gap-1">
                    <i class="fa-solid fa-hourglass-half text-[10px]"></i>
                    <span>Perlu Tinjauan Admin</span>
                </div>
            </div>
            <div class="w-11 h-11 rounded-xl bg-amber-50 text-amber-600 flex items-center justify-center text-lg shrink-0 border border-amber-200/80 group-hover:scale-105 transition-transform">
                <i class="fa-solid fa-inbox"></i>
            </div>
        </a>

        <!-- 4. TOTAL SELURUH AKUN -->
        @php
            $totalAkun = ($statistik['superadmin'] ?? 0) + ($statistik['admin'] ?? 0) + ($statistik['asesor'] ?? 0) + ($statistik['asesi'] ?? 0);
        @endphp
        <a href="{{ route('superadmin.manajemen-pengguna') }}" 
           class="p-5 rounded-2xl bg-white border border-slate-200/90 shadow-2xs hover:border-slate-300 transition-all flex items-start justify-between block group">
            <div>
                <div class="text-[11px] font-bold text-slate-400 uppercase tracking-wider">Total Seluruh Akun</div>
                <div class="text-2xl sm:text-3xl font-black text-slate-900 mt-1.5">{{ number_format($totalAkun) }}</div>
                <div class="text-[11px] text-slate-500 font-medium mt-1 inline-flex items-center gap-1">
                    <i class="fa-solid fa-users text-[10px] text-slate-400"></i>
                    <span>Asesi, Asesor, &amp; Admin</span>
                </div>
            </div>
            <div class="w-11 h-11 rounded-xl bg-blue-50 text-blue-600 flex items-center justify-center text-lg shrink-0 border border-blue-100/80 group-hover:scale-105 transition-transform">
                <i class="fa-solid fa-id-badge"></i>
            </div>
        </a>
    </div>

    <!-- DISTRIBUSI PENGGUNA PER PERAN -->
    <div class="bg-white rounded-2xl p-6 border border-slate-200/90 shadow-2xs space-y-5">
        <div class="flex items-center justify-between flex-wrap gap-2 border-b border-slate-100 pb-4">
            <div>
                <h2 class="text-base font-bold text-slate-900 flex items-center gap-2">
                    <i class="fa-solid fa-users-gear text-blue-600 text-sm"></i>
                    <span>Distribusi Akun &amp; Hak Akses Pengguna</span>
                </h2>
                <p class="text-xs text-slate-500 mt-0.5">Rincian proporsi pengguna terdaftar berdasarkan peran sistem LSP</p>
            </div>
            <a href="{{ route('superadmin.manajemen-pengguna') }}" class="inline-flex items-center gap-1.5 text-xs font-bold text-blue-600 hover:text-blue-700 transition-colors">
                <span>Kelola Semua Akun</span>
                <i class="fa-solid fa-arrow-right text-[10px]"></i>
            </a>
        </div>

        <div class="grid grid-cols-2 sm:grid-cols-4 gap-3.5 sm:gap-4">
            <!-- SUPERADMIN -->
            <a href="{{ route('superadmin.manajemen-pengguna', ['peran' => 'superadmin']) }}" 
               class="p-4 rounded-xl bg-slate-50/80 border border-slate-200/80 hover:border-blue-300 hover:bg-blue-50/30 transition-all block group">
                <div class="flex items-center justify-between mb-2">
                    <span class="text-xs font-bold text-slate-700">Super Admin</span>
                    <span class="w-7 h-7 rounded-lg bg-blue-100 text-blue-700 flex items-center justify-center text-xs">
                        <i class="fa-solid fa-shield-halved"></i>
                    </span>
                </div>
                <div class="text-2xl font-extrabold text-slate-900">{{ $statistik['superadmin'] ?? 0 }}</div>
                <div class="text-[11px] text-slate-400 mt-0.5">Akses Master Penuh</div>
            </a>

            <!-- ADMIN -->
            <a href="{{ route('superadmin.manajemen-pengguna', ['peran' => 'admin']) }}" 
               class="p-4 rounded-xl bg-slate-50/80 border border-slate-200/80 hover:border-blue-300 hover:bg-blue-50/30 transition-all block group">
                <div class="flex items-center justify-between mb-2">
                    <span class="text-xs font-bold text-blue-900">Admin LSP</span>
                    <span class="w-7 h-7 rounded-lg bg-blue-100 text-blue-700 flex items-center justify-center text-xs">
                        <i class="fa-solid fa-user-gear"></i>
                    </span>
                </div>
                <div class="text-2xl font-extrabold text-blue-950">{{ $statistik['admin'] ?? 0 }}</div>
                <div class="text-[11px] text-blue-600 mt-0.5 font-medium">Staf &amp; Verifikator</div>
            </a>

            <!-- ASESOR -->
            <a href="{{ route('superadmin.manajemen-pengguna', ['peran' => 'asesor']) }}" 
               class="p-4 rounded-xl bg-slate-50/80 border border-slate-200/80 hover:border-blue-300 hover:bg-blue-50/30 transition-all block group">
                <div class="flex items-center justify-between mb-2">
                    <span class="text-xs font-bold text-slate-700">Asesor Penguji</span>
                    <span class="w-7 h-7 rounded-lg bg-blue-100 text-blue-700 flex items-center justify-center text-xs">
                        <i class="fa-solid fa-user-tie"></i>
                    </span>
                </div>
                <div class="text-2xl font-extrabold text-slate-900">{{ $statistik['asesor'] ?? 0 }}</div>
                <div class="text-[11px] text-slate-500 mt-0.5">Lisensi MET Resmi</div>
            </a>

            <!-- ASESI -->
            <a href="{{ route('superadmin.manajemen-pengguna', ['peran' => 'asesi']) }}" 
               class="p-4 rounded-xl bg-slate-50/80 border border-slate-200/80 hover:border-blue-300 hover:bg-blue-50/30 transition-all block group">
                <div class="flex items-center justify-between mb-2">
                    <span class="text-xs font-bold text-blue-900">Asesi (Peserta)</span>
                    <span class="w-7 h-7 rounded-lg bg-blue-100 text-blue-700 flex items-center justify-center text-xs">
                        <i class="fa-solid fa-user-graduate"></i>
                    </span>
                </div>
                <div class="text-2xl font-extrabold text-blue-950">{{ $statistik['asesi'] ?? 0 }}</div>
                <div class="text-[11px] text-blue-600 mt-0.5 font-medium">Peserta Sertifikasi</div>
            </a>
        </div>
    </div>

    <!-- PUSAT PINTASAN MODUL TERPADU -->
    <div class="space-y-3">
        <div class="flex items-center justify-between px-1">
            <h3 class="text-xs font-extrabold uppercase tracking-wider text-slate-500 flex items-center gap-2">
                <i class="fa-solid fa-compass text-blue-600"></i>
                <span>Pusat Pintasan Modul Terpadu</span>
            </h3>
            <span class="text-[11px] text-slate-400">Akses langsung ke seluruh subsistem LSP</span>
        </div>

        <div class="grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-6 gap-3">
            <!-- 1. MANAJEMEN ASESI -->
            <a href="{{ route('admin.manajemen-asesi') }}" 
               class="p-4 rounded-xl bg-white border border-slate-200/90 shadow-2xs hover:border-blue-400 hover:shadow-xs transition-all block group">
                <div class="w-8 h-8 rounded-lg bg-blue-50 text-blue-600 flex items-center justify-center text-sm mb-2.5 group-hover:scale-105 transition-transform">
                    <i class="fa-solid fa-id-card"></i>
                </div>
                <div class="font-bold text-slate-900 text-xs">Verifikasi Asesi</div>
                <div class="text-[11px] text-slate-400 mt-0.5">Berkas APL.01</div>
            </a>

            <!-- 2. MASTER SKEMA -->
            <a href="{{ route('admin.manajemen-skema') }}" 
               class="p-4 rounded-xl bg-white border border-slate-200/90 shadow-2xs hover:border-blue-400 hover:shadow-xs transition-all block group">
                <div class="w-8 h-8 rounded-lg bg-blue-50 text-blue-600 flex items-center justify-center text-sm mb-2.5 group-hover:scale-105 transition-transform">
                    <i class="fa-solid fa-certificate"></i>
                </div>
                <div class="font-bold text-slate-900 text-xs">Master Skema</div>
                <div class="text-[11px] text-slate-400 mt-0.5">Unit &amp; Standar</div>
            </a>

            <!-- 3. JADWAL ASESMEN -->
            <a href="{{ route('admin.manajemen-jadwal') }}" 
               class="p-4 rounded-xl bg-white border border-slate-200/90 shadow-2xs hover:border-blue-400 hover:shadow-xs transition-all block group">
                <div class="w-8 h-8 rounded-lg bg-blue-50 text-blue-600 flex items-center justify-center text-sm mb-2.5 group-hover:scale-105 transition-transform">
                    <i class="fa-solid fa-calendar-days"></i>
                </div>
                <div class="font-bold text-slate-900 text-xs">Jadwal Asesmen</div>
                <div class="text-[11px] text-slate-400 mt-0.5">Uji &amp; TUK</div>
            </a>

            <!-- 4. MASTER MUK -->
            <a href="{{ route('admin.master-muk.index') }}" 
               class="p-4 rounded-xl bg-white border border-slate-200/90 shadow-2xs hover:border-blue-400 hover:shadow-xs transition-all block group">
                <div class="w-8 h-8 rounded-lg bg-blue-50 text-blue-600 flex items-center justify-center text-sm mb-2.5 group-hover:scale-105 transition-transform">
                    <i class="fa-solid fa-file-pen"></i>
                </div>
                <div class="font-bold text-slate-900 text-xs">MUK &amp; Bank Soal</div>
                <div class="text-[11px] text-slate-400 mt-0.5">Formulir FR.IA</div>
            </a>

            <!-- 5. DOKUMEN HUB -->
            <a href="{{ route('dokumen-asesmen.index') }}" 
               class="p-4 rounded-xl bg-white border border-slate-200/90 shadow-2xs hover:border-blue-400 hover:shadow-xs transition-all block group">
                <div class="w-8 h-8 rounded-lg bg-blue-50 text-blue-600 flex items-center justify-center text-sm mb-2.5 group-hover:scale-105 transition-transform">
                    <i class="fa-solid fa-folder-tree"></i>
                </div>
                <div class="font-bold text-slate-900 text-xs">Dokumen Asesmen</div>
                <div class="text-[11px] text-slate-400 mt-0.5">Pusat AK &amp; VA</div>
            </a>

            <!-- 6. PENGATURAN GLOBAL -->
            <a href="{{ route('superadmin.pengaturan-sistem') }}" 
               class="p-4 rounded-xl bg-white border border-slate-200/90 shadow-2xs hover:border-blue-400 hover:shadow-xs transition-all block group">
                <div class="w-8 h-8 rounded-lg bg-blue-50 text-blue-600 flex items-center justify-center text-sm mb-2.5 group-hover:scale-105 transition-transform">
                    <i class="fa-solid fa-sliders"></i>
                </div>
                <div class="font-bold text-slate-900 text-xs">Pengaturan Sistem</div>
                <div class="text-[11px] text-slate-400 mt-0.5">Lisensi &amp; Lembaga</div>
            </a>
        </div>
    </div>

    <!-- AUDIT TRAIL LOG TERKINI -->
    <div class="bg-white rounded-2xl border border-slate-200/90 shadow-2xs overflow-hidden">
        <div class="p-5 sm:p-6 border-b border-slate-100 flex items-center justify-between flex-wrap gap-2">
            <div>
                <h3 class="text-base font-bold text-slate-900 leading-tight flex items-center gap-2">
                    <i class="fa-solid fa-clock-rotate-left text-blue-600 text-sm"></i>
                    <span>Jejak Aktivitas &amp; Audit Log Terkini</span>
                </h3>
                <p class="text-xs text-slate-500 mt-0.5">Catatan aktivitas penting pengguna dan perubahan data dalam sistem</p>
            </div>
            <a href="{{ route('superadmin.log-aktivitas') }}" 
               class="inline-flex items-center gap-1.5 px-3.5 py-1.5 rounded-xl bg-slate-100 hover:bg-slate-200 text-slate-700 font-semibold text-xs transition-colors">
                <span>Lihat Seluruh Log</span>
                <i class="fa-solid fa-arrow-right text-[10px]"></i>
            </a>
        </div>

        <div class="overflow-x-auto">
            <table class="w-full text-left border-collapse">
                <thead>
                    <tr class="bg-slate-50 border-b border-slate-200/80 text-[11px] font-bold text-slate-500 uppercase tracking-wider">
                        <th class="py-3 px-6">Waktu</th>
                        <th class="py-3 px-4">Pengguna</th>
                        <th class="py-3 px-4">Aktivitas</th>
                        <th class="py-3 px-6">Deskripsi Rincian</th>
                        <th class="py-3 px-6 text-right">IP Address</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100 text-xs">
                    @forelse($logTerbaru as $log)
                        <tr class="hover:bg-slate-50/70 transition-colors">
                            <td class="py-3.5 px-6 text-slate-500 whitespace-nowrap">
                                <div class="font-semibold text-slate-800">{{ date('d M Y', strtotime($log->created_at)) }}</div>
                                <div class="text-[10px] text-slate-400">{{ date('H:i:s', strtotime($log->created_at)) }} WIB</div>
                            </td>
                            <td class="py-3.5 px-4">
                                <div class="font-bold text-slate-900">{{ $log->pengguna->nama_lengkap ?? 'Sistem LSP' }}</div>
                                <div class="text-[10px] text-slate-400 capitalize">{{ $log->pengguna->peran ?? 'System Event' }}</div>
                            </td>
                            <td class="py-3.5 px-4 whitespace-nowrap">
                                <span class="px-2.5 py-1 rounded-md text-xs font-semibold bg-blue-50 text-blue-700 border border-blue-200/80 inline-block">
                                    {{ $log->aktivitas }}
                                </span>
                            </td>
                            <td class="py-3.5 px-6 text-slate-600 leading-relaxed">
                                {{ $log->deskripsi }}
                            </td>
                            <td class="py-3.5 px-6 text-right whitespace-nowrap">
                                <code class="px-2 py-0.5 rounded bg-slate-100 text-slate-600 font-mono text-[11px] border border-slate-200">
                                    {{ $log->ip_address ?? '127.0.0.1' }}
                                </code>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="py-10 text-center text-slate-400">
                                <div class="w-10 h-10 rounded-xl bg-slate-100 text-slate-400 flex items-center justify-center mx-auto mb-2 text-base">
                                    <i class="fa-regular fa-clock"></i>
                                </div>
                                <div class="font-bold text-slate-700 text-xs">Belum Ada Log Aktivitas</div>
                                <p class="text-[11px] text-slate-400 mt-0.5">Aktivitas pengguna akan tercatat otomatis di sini.</p>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

</div>
@endsection
