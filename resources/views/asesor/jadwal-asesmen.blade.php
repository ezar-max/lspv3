@extends('tata-letak.dasbor')

@section('judul', 'Jadwal Penugasan Asesmen')

@section('konten')
<div class="space-y-6 animasi-fade">

    <!-- =========================================================================
         1. PAGE HEADER & BREADCRUMB
         ========================================================================= -->
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 pb-1">
        <div class="space-y-1">
            <div class="flex items-center gap-2 text-xs text-slate-500">
                <a href="{{ route('asesor.dashboard') }}" class="hover:text-indigo-600 font-medium transition-colors">Dashboard</a>
                <span>/</span>
                <span class="text-slate-400">Pelaksanaan Asesmen</span>
                <span>/</span>
                <span class="text-slate-800 font-bold">Jadwal Penugasan</span>
            </div>
            <h1 class="text-xl sm:text-2xl font-black text-slate-900 tracking-tight">
                Jadwal Penugasan Uji Asesmen
            </h1>
            <p class="text-xs sm:text-sm text-slate-500">
                Daftar seluruh jadwal pelaksanaan uji kompetensi yang ditugaskan kepada Anda sebagai Asesor.
            </p>
        </div>

        <div class="flex items-center gap-2 flex-wrap">
            <a href="{{ route('asesor.dashboard') }}" 
               class="px-3.5 py-2 rounded-xl border border-slate-200 hover:bg-slate-50 text-slate-700 font-semibold text-xs transition-colors inline-flex items-center gap-1.5 shadow-2xs">
                <i class="fa-solid fa-arrow-left text-[11px] text-slate-400"></i>
                <span>Ke Dashboard</span>
            </a>
            <a href="{{ route('asesor.daftar-peserta') }}" 
               class="px-3.5 py-2 rounded-xl bg-indigo-600 hover:bg-indigo-700 text-white font-bold text-xs transition-colors inline-flex items-center gap-1.5 shadow-2xs">
                <i class="fa-solid fa-users text-[11px]"></i>
                <span>Penilaian Peserta</span>
            </a>
            <a href="{{ route('asesor.berita-acara') }}" 
               class="px-3.5 py-2 rounded-xl border border-slate-200 hover:bg-slate-50 text-slate-700 font-semibold text-xs transition-colors inline-flex items-center gap-1.5 shadow-2xs">
                <i class="fa-solid fa-file-signature text-[11px] text-slate-400"></i>
                <span>Berita Acara</span>
            </a>
        </div>
    </div>

    <!-- =========================================================================
         2. STAT / SUMMARY CARDS
         ========================================================================= -->
    <div class="grid grid-cols-2 sm:grid-cols-4 gap-3.5 sm:gap-4">
        <!-- Total Semua Jadwal -->
        <a href="{{ route('asesor.jadwal') }}" 
           class="p-4 rounded-2xl bg-white border {{ empty($statusFilter) ? 'border-indigo-500 ring-2 ring-indigo-100 shadow-sm' : 'border-slate-200/90 shadow-2xs hover:border-slate-300' }} transition-all flex items-center gap-3.5 group">
            <div class="w-11 h-11 rounded-xl bg-indigo-50 text-indigo-600 flex items-center justify-center shrink-0 text-base font-black group-hover:scale-105 transition-transform">
                <i class="fa-regular fa-calendar-days"></i>
            </div>
            <div>
                <div class="text-[11px] font-bold text-slate-400 uppercase tracking-wider">Total Jadwal</div>
                <div class="text-lg sm:text-xl font-black text-slate-900 leading-tight mt-0.5">
                    {{ $countSemua ?? $jadwalList->total() }}
                </div>
            </div>
        </a>

        <!-- Sesi Aktif / Berlangsung -->
        <a href="{{ route('asesor.jadwal', ['status' => 'berlangsung']) }}" 
           class="p-4 rounded-2xl bg-white border {{ ($statusFilter ?? '') === 'berlangsung' ? 'border-emerald-500 ring-2 ring-emerald-100 shadow-sm' : 'border-slate-200/90 shadow-2xs hover:border-emerald-200' }} transition-all flex items-center gap-3.5 group">
            <div class="w-11 h-11 rounded-xl bg-emerald-50 text-emerald-600 flex items-center justify-center shrink-0 text-base font-black group-hover:scale-105 transition-transform relative">
                <i class="fa-solid fa-bolt"></i>
                @if(($countBerlangsung ?? 0) > 0)
                    <span class="absolute -top-1 -right-1 flex h-2.5 w-2.5">
                        <span class="animate-ping absolute inline-flex h-full w-full rounded-full bg-emerald-400 opacity-75"></span>
                        <span class="relative inline-flex rounded-full h-2.5 w-2.5 bg-emerald-500"></span>
                    </span>
                @endif
            </div>
            <div>
                <div class="text-[11px] font-bold text-emerald-600 uppercase tracking-wider">Live Berlangsung</div>
                <div class="text-lg sm:text-xl font-black text-slate-900 leading-tight mt-0.5">
                    {{ $countBerlangsung ?? 0 }}
                </div>
            </div>
        </a>

        <!-- Terjadwal (Mendatang) -->
        <a href="{{ route('asesor.jadwal', ['status' => 'terjadwal']) }}" 
           class="p-4 rounded-2xl bg-white border {{ ($statusFilter ?? '') === 'terjadwal' ? 'border-blue-500 ring-2 ring-blue-100 shadow-sm' : 'border-slate-200/90 shadow-2xs hover:border-blue-200' }} transition-all flex items-center gap-3.5 group">
            <div class="w-11 h-11 rounded-xl bg-blue-50 text-blue-600 flex items-center justify-center shrink-0 text-base font-black group-hover:scale-105 transition-transform">
                <i class="fa-regular fa-clock"></i>
            </div>
            <div>
                <div class="text-[11px] font-bold text-slate-400 uppercase tracking-wider">Terjadwal</div>
                <div class="text-lg sm:text-xl font-black text-slate-900 leading-tight mt-0.5">
                    {{ $countTerjadwal ?? 0 }}
                </div>
            </div>
        </a>

        <!-- Selesai -->
        <a href="{{ route('asesor.jadwal', ['status' => 'selesai']) }}" 
           class="p-4 rounded-2xl bg-white border {{ ($statusFilter ?? '') === 'selesai' ? 'border-slate-800 ring-2 ring-slate-200 shadow-sm' : 'border-slate-200/90 shadow-2xs hover:border-slate-300' }} transition-all flex items-center gap-3.5 group">
            <div class="w-11 h-11 rounded-xl bg-slate-100 text-slate-600 flex items-center justify-center shrink-0 text-base font-black group-hover:scale-105 transition-transform">
                <i class="fa-solid fa-circle-check"></i>
            </div>
            <div>
                <div class="text-[11px] font-bold text-slate-400 uppercase tracking-wider">Selesai</div>
                <div class="text-lg sm:text-xl font-black text-slate-900 leading-tight mt-0.5">
                    {{ $countSelesai ?? 0 }}
                </div>
            </div>
        </a>
    </div>

    <!-- =========================================================================
         3. SEARCH & FILTER TOOLBAR
         ========================================================================= -->
    <div class="bg-white rounded-2xl border border-slate-200/90 shadow-2xs p-3.5 sm:p-4 space-y-3">
        <form action="{{ route('asesor.jadwal') }}" method="GET" class="space-y-3">
            <div class="flex flex-col sm:flex-row items-center gap-2.5">
                <!-- Search Input -->
                <div class="relative flex-1 w-full">
                    <i class="fa-solid fa-magnifying-glass absolute left-3.5 top-1/2 -translate-y-1/2 text-slate-400 text-xs pointer-events-none"></i>
                    <input type="text" 
                           name="q" 
                           value="{{ request('q') }}" 
                           placeholder="Cari kode jadwal, nama skema, atau TUK..."
                           class="w-full bg-slate-50 border border-slate-200 rounded-xl pl-9 pr-3 py-2 text-xs text-slate-800 placeholder-slate-400 focus:bg-white focus:outline-none focus:ring-1 focus:ring-indigo-500 transition-colors">
                </div>

                <!-- Hidden preserve status filter if present -->
                @if(request('status'))
                    <input type="hidden" name="status" value="{{ request('status') }}">
                @endif

                <!-- Action buttons -->
                <div class="flex items-center gap-1.5 w-full sm:w-auto">
                    <button type="submit" class="flex-1 sm:flex-none px-4 py-2 bg-indigo-600 hover:bg-indigo-700 text-white font-bold text-xs rounded-xl transition-colors inline-flex items-center justify-center gap-1.5 cursor-pointer shadow-2xs">
                        <i class="fa-solid fa-filter text-[10px]"></i>
                        <span>Cari</span>
                    </button>
                    @if(request('q') || request('status'))
                        <a href="{{ route('asesor.jadwal') }}" class="px-3.5 py-2 border border-slate-200 hover:bg-slate-100 text-slate-600 font-semibold rounded-xl text-xs transition-colors inline-flex items-center gap-1.5" title="Reset Pencarian">
                            <i class="fa-solid fa-rotate-left text-[11px]"></i>
                            <span>Reset</span>
                        </a>
                    @endif
                </div>
            </div>

            <!-- Status Filter Pills -->
            <div class="flex items-center gap-1.5 overflow-x-auto pb-1 text-xs custom-scrollbar pt-1 border-t border-slate-100">
                <span class="text-[11px] font-bold text-slate-400 uppercase tracking-wider mr-1 shrink-0">Filter Status:</span>

                <a href="{{ request()->fullUrlWithQuery(['status' => null, 'page' => 1]) }}" 
                   class="px-3 py-1 rounded-lg font-semibold shrink-0 transition-colors {{ empty($statusFilter) ? 'bg-indigo-600 text-white shadow-2xs' : 'bg-slate-100 text-slate-600 hover:bg-slate-200' }}">
                    Semua ({{ $countSemua ?? $jadwalList->total() }})
                </a>

                <a href="{{ request()->fullUrlWithQuery(['status' => 'berlangsung', 'page' => 1]) }}" 
                   class="px-3 py-1 rounded-lg font-semibold shrink-0 transition-colors {{ ($statusFilter ?? '') === 'berlangsung' ? 'bg-emerald-600 text-white shadow-2xs' : 'bg-emerald-50 text-emerald-700 hover:bg-emerald-100 border border-emerald-200/60' }}">
                    Live Berlangsung ({{ $countBerlangsung ?? 0 }})
                </a>

                <a href="{{ request()->fullUrlWithQuery(['status' => 'terjadwal', 'page' => 1]) }}" 
                   class="px-3 py-1 rounded-lg font-semibold shrink-0 transition-colors {{ ($statusFilter ?? '') === 'terjadwal' ? 'bg-blue-600 text-white shadow-2xs' : 'bg-blue-50 text-blue-700 hover:bg-blue-100 border border-blue-200/60' }}">
                    Terjadwal ({{ $countTerjadwal ?? 0 }})
                </a>

                <a href="{{ request()->fullUrlWithQuery(['status' => 'selesai', 'page' => 1]) }}" 
                   class="px-3 py-1 rounded-lg font-semibold shrink-0 transition-colors {{ ($statusFilter ?? '') === 'selesai' ? 'bg-slate-800 text-white shadow-2xs' : 'bg-slate-100 text-slate-700 hover:bg-slate-200' }}">
                    Selesai ({{ $countSelesai ?? 0 }})
                </a>
            </div>
        </form>
    </div>

    <!-- =========================================================================
         4. JADWAL DATA TABLE CARD
         ========================================================================= -->
    <div class="bg-white rounded-2xl border border-slate-200/90 shadow-2xs overflow-hidden">
        <!-- Table Top Header -->
        <div class="px-4 py-3 bg-slate-50/80 border-b border-slate-200/80 flex flex-col sm:flex-row sm:items-center justify-between gap-2">
            <div class="flex items-center gap-2">
                <span class="font-bold text-xs sm:text-sm text-slate-800">Daftar Jadwal Penugasan</span>
                <span class="text-xs text-slate-400">({{ $jadwalList->total() }} Jadwal)</span>
            </div>
            <span class="text-[11px] text-slate-500 font-medium">
                Menampilkan {{ $jadwalList->firstItem() ?? 0 }} - {{ $jadwalList->lastItem() ?? 0 }} dari {{ $jadwalList->total() }} jadwal
            </span>
        </div>

        <!-- Table Container -->
        <div class="overflow-x-auto">
            <table class="w-full text-left border-collapse text-xs">
                <thead>
                    <tr class="bg-slate-50/50 border-b border-slate-200 text-[11px] font-bold text-slate-500 uppercase tracking-wider">
                        <th class="py-3 px-3 w-12 text-center">No</th>
                        <th class="py-3 px-4 whitespace-nowrap min-w-[150px]">Kode Jadwal</th>
                        <th class="py-3 px-4 min-w-[280px]">Skema Sertifikasi</th>
                        <th class="py-3 px-4 whitespace-nowrap min-w-[170px]">Waktu Pelaksanaan</th>
                        <th class="py-3 px-4 min-w-[140px]">Tempat Uji (TUK)</th>
                        <th class="py-3 px-4 min-w-[130px]">Peserta Asesi</th>
                        <th class="py-3 px-4 whitespace-nowrap min-w-[140px] text-center">Status</th>
                        <th class="py-3 px-4 whitespace-nowrap min-w-[160px] text-right">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @forelse($jadwalList as $idx => $j)
                        @php
                            $isWaktuAktif = method_exists($j, 'isWaktuAktif') ? $j->isWaktuAktif() : ($j->status_jadwal === 'berlangsung');
                            $pesertaCount = $j->pendaftaranAsesi->count();
                            $kuota = $j->kuota ?? 0;
                            $persenKuota = $kuota > 0 ? min(100, round(($pesertaCount / $kuota) * 100)) : 0;
                            $isHariIni = !empty($j->tanggal_uji) && date('Y-m-d', strtotime($j->tanggal_uji)) === date('Y-m-d');
                        @endphp
                        <tr class="hover:bg-slate-50/70 transition-colors {{ $isWaktuAktif ? 'bg-emerald-50/20' : '' }}">
                            
                            <!-- 1. No -->
                            <td class="py-3.5 px-3 text-center text-slate-400 font-medium">
                                {{ $jadwalList->firstItem() + $idx }}
                            </td>

                            <!-- 2. Kode Jadwal -->
                            <td class="py-3.5 px-4 whitespace-nowrap">
                                <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-lg bg-indigo-50/80 border border-indigo-100/90 font-mono font-bold text-xs text-indigo-700 tracking-tight">
                                    <i class="fa-solid fa-hashtag text-[10px] text-indigo-400"></i>
                                    <span>{{ $j->kode_jadwal }}</span>
                                </span>
                            </td>

                            <!-- 3. Skema Sertifikasi -->
                            <td class="py-3.5 px-4">
                                <div class="font-bold text-slate-900 text-xs sm:text-sm leading-snug">
                                    {{ $j->skema->nama_skema ?? 'Skema Tidak Ditemukan' }}
                                </div>
                                @if(!empty($j->skema->kode_skema))
                                    <div class="text-[11px] text-slate-400 font-mono flex items-center gap-1.5 mt-1">
                                        <i class="fa-solid fa-certificate text-indigo-400 text-[10px]"></i>
                                        <span>{{ $j->skema->kode_skema }}</span>
                                    </div>
                                @endif
                            </td>

                            <!-- 4. Waktu Pelaksanaan -->
                            <td class="py-3.5 px-4 whitespace-nowrap">
                                <div class="flex items-center gap-1.5 font-bold text-slate-800 text-xs">
                                    <i class="fa-regular fa-calendar-days text-slate-400 text-[11px]"></i>
                                    <span>{{ date('d M Y', strtotime($j->tanggal_uji)) }}</span>
                                    @if($isHariIni)
                                        <span class="px-1.5 py-0.2 rounded bg-amber-100 text-amber-800 text-[9px] font-black uppercase tracking-wider">
                                            Hari Ini
                                        </span>
                                    @endif
                                </div>
                                <div class="flex items-center gap-1.5 text-[11px] text-slate-500 mt-1">
                                    <i class="fa-regular fa-clock text-slate-400 text-[10px]"></i>
                                    <span>{{ substr($j->waktu_mulai, 0, 5) }} - {{ substr($j->waktu_selesai, 0, 5) }} WIB</span>
                                </div>
                            </td>

                            <!-- 5. Tempat Uji (TUK) -->
                            <td class="py-3.5 px-4">
                                <div class="font-semibold text-slate-800 text-xs flex items-center gap-1.5">
                                    <i class="fa-solid fa-location-dot text-rose-500 text-[11px]"></i>
                                    <span>{{ $j->nama_tuk ?? 'TUK LSP SMKN 1' }}</span>
                                </div>
                                <div class="text-[10px] text-slate-400 pl-4 mt-0.5">
                                    {{ $j->tipe_tuk ?? 'TUK Terverifikasi' }}
                                </div>
                            </td>

                            <!-- 6. Peserta Asesi -->
                            <td class="py-3.5 px-4">
                                <div class="flex items-center justify-between text-xs mb-1">
                                    <span class="font-bold text-slate-800">{{ $pesertaCount }}</span>
                                    <span class="text-slate-400 text-[11px]">/ {{ $kuota }} Kuota</span>
                                </div>
                                <div class="w-24 bg-slate-100 rounded-full h-1.5 overflow-hidden">
                                    <div class="h-1.5 rounded-full {{ $persenKuota >= 100 ? 'bg-emerald-500' : 'bg-indigo-600' }}" 
                                         style="width: {{ $persenKuota }}%"></div>
                                </div>
                                <div class="text-[10px] text-slate-400 mt-1">
                                    {{ $pesertaCount }} Asesi Terdaftar
                                </div>
                            </td>

                            <!-- 7. Status -->
                            <td class="py-3.5 px-4 whitespace-nowrap text-center">
                                @if($j->status_jadwal === 'berlangsung')
                                    <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full text-[11px] font-bold bg-emerald-50 text-emerald-700 border border-emerald-200/80 shadow-2xs">
                                        <span class="relative flex h-2 w-2">
                                            <span class="animate-ping absolute inline-flex h-full w-full rounded-full bg-emerald-400 opacity-75"></span>
                                            <span class="relative inline-flex rounded-full h-2 w-2 bg-emerald-500"></span>
                                        </span>
                                        <span>Aktif (Berlangsung)</span>
                                    </span>
                                @elseif($j->status_jadwal === 'selesai')
                                    <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full text-[11px] font-semibold bg-slate-100 text-slate-600 border border-slate-200">
                                        <i class="fa-solid fa-circle-check text-slate-400 text-[10px]"></i>
                                        <span>Selesai</span>
                                    </span>
                                @elseif($j->status_jadwal === 'dibatalkan')
                                    <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full text-[11px] font-semibold bg-rose-50 text-rose-700 border border-rose-200">
                                        <i class="fa-solid fa-ban text-rose-400 text-[10px]"></i>
                                        <span>Dibatalkan</span>
                                    </span>
                                @else
                                    <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full text-[11px] font-semibold bg-blue-50 text-blue-700 border border-blue-200">
                                        <i class="fa-regular fa-clock text-blue-400 text-[10px]"></i>
                                        <span>Terjadwal</span>
                                    </span>
                                @endif
                            </td>

                            <!-- 8. Aksi -->
                            <td class="py-3.5 px-4 whitespace-nowrap text-right">
                                <div class="inline-flex items-center gap-1.5 justify-end">
                                    <!-- Tombol Utama: Peserta / Uji Live -->
                                    <a href="{{ route('asesor.daftar-peserta', ['jadwal_id' => $j->id]) }}" 
                                       class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-xl {{ $isWaktuAktif ? 'bg-emerald-600 hover:bg-emerald-700 text-white font-bold shadow-emerald-100' : 'bg-indigo-600 hover:bg-indigo-700 text-white font-semibold shadow-indigo-100' }} text-xs shadow-2xs transition-all hover:shadow-xs cursor-pointer">
                                        @if($isWaktuAktif)
                                            <i class="fa-solid fa-play text-[9px]"></i>
                                            <span>Uji Live</span>
                                        @else
                                            <i class="fa-solid fa-users text-[10px]"></i>
                                            <span>Peserta</span>
                                        @endif
                                    </a>

                                    <!-- Tombol Cepat: Koreksi Teori -->
                                    <a href="{{ route('asesor.koreksi-teori', ['jadwal_id' => $j->id]) }}" 
                                       class="p-1.5 rounded-xl border border-slate-200 hover:border-indigo-300 hover:bg-indigo-50 text-slate-500 hover:text-indigo-600 text-xs transition-colors shadow-2xs" 
                                       title="Koreksi Teori (IA.05 / IA.06)">
                                        <i class="fa-solid fa-file-pen"></i>
                                    </a>

                                    <!-- Tombol Cepat: Berita Acara -->
                                    <a href="{{ route('asesor.berita-acara') }}" 
                                       class="p-1.5 rounded-xl border border-slate-200 hover:border-emerald-300 hover:bg-emerald-50 text-slate-500 hover:text-emerald-600 text-xs transition-colors shadow-2xs" 
                                       title="Buat / Lihat Berita Acara">
                                        <i class="fa-solid fa-file-signature"></i>
                                    </a>
                                </div>
                            </td>

                        </tr>
                    @empty
                        <tr>
                            <td colspan="8" class="py-12 px-4 text-center">
                                <div class="max-w-sm mx-auto space-y-3">
                                    <div class="w-12 h-12 rounded-2xl bg-slate-100 text-slate-400 flex items-center justify-center mx-auto text-xl">
                                        <i class="fa-regular fa-calendar-xmark"></i>
                                    </div>
                                    <div class="space-y-1">
                                        <h3 class="font-bold text-slate-800 text-sm">Tidak Ada Jadwal Ditemukan</h3>
                                        <p class="text-xs text-slate-400">
                                            @if(request('q') || request('status'))
                                                Tidak ada jadwal yang sesuai dengan filter atau kata kunci pencarian Anda.
                                            @else
                                                Belum ada jadwal penugasan asesmen yang dialokasikan kepada akun Anda.
                                            @endif
                                        </p>
                                    </div>
                                    @if(request('q') || request('status'))
                                        <div class="pt-1">
                                            <a href="{{ route('asesor.jadwal') }}" class="inline-flex items-center gap-1.5 px-3.5 py-1.5 rounded-xl bg-slate-100 hover:bg-slate-200 text-slate-700 font-bold text-xs transition-colors">
                                                <i class="fa-solid fa-rotate-left text-[10px]"></i>
                                                <span>Bersihkan Filter</span>
                                            </a>
                                        </div>
                                    @endif
                                </div>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <!-- Table Footer / Pagination -->
        @if($jadwalList->hasPages())
            <div class="px-4 py-3 border-t border-slate-200/80 bg-slate-50/50">
                {{ $jadwalList->links() }}
            </div>
        @endif
    </div>

</div>
@endsection
