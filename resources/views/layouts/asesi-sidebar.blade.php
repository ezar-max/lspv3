@php
    $pendaftaranAktifSidebar = \App\Models\PendaftaranAsesi::where('asesi_id', auth()->id())
        ->where('status_pendaftaran', '!=', 'ditolak')
        ->latest()
        ->first();
    
    $isTahapanLengkap = false;
    if ($pendaftaranAktifSidebar) {
        $isTahapanLengkap = (!empty($pendaftaranAktifSidebar->tanda_tangan_asesi_ak01) || in_array($pendaftaranAktifSidebar->status_ak01, ['disetujui_asesi', 'selesai']));
    }
    
    $profilAsesiSidebar = auth()->user()->profilAsesi;
    $identitasAsesi = $profilAsesiSidebar->nik ?? auth()->user()->nomor_registrasi ?? null;
    $namaAsesi = auth()->user()->nama_lengkap ?? auth()->user()->name ?? 'Asesi';
    $inisialNama = strtoupper(substr($namaAsesi, 0, 2));
@endphp

<!-- SIDEBAR ASESI COMPONENT -->
<div class="sidebar-asesi-wrapper flex flex-col justify-between h-full">
    <div>
        <!-- 1. Header Sidebar -->
        <a href="{{ route('asesi.dashboard') }}" class="flex items-center gap-3 px-3 py-3.5 mb-2 border-b border-slate-100 hover:bg-slate-50/60 rounded-xl transition-colors duration-150">
            <img src="{{ asset('images/logo-lsp.jpeg') }}" alt="Logo LSP" class="w-9 h-9 object-contain rounded-lg shadow-xs border border-slate-200/60 flex-shrink-0">
            <div class="min-w-0">
                <div class="font-semibold text-slate-800 text-xs sm:text-sm leading-tight truncate">LSP SMKN 1 Gunungputri</div>
                <div class="text-[10px] tracking-wider font-bold text-blue-600 uppercase mt-0.5">PORTAL ASESI</div>
            </div>
        </a>

        <!-- 2. Menu Navigasi -->
        <ul class="space-y-1 mt-3 px-1 text-sm font-medium text-slate-600">
            <!-- 1. Dashboard -->
            <li>
                <a href="{{ route('asesi.dashboard') }}" 
                   class="flex items-center gap-3 px-3 py-2.5 rounded-xl transition-all duration-150 {{ request()->routeIs('asesi.dashboard') ? 'bg-blue-50 text-blue-700 font-semibold shadow-xs' : 'text-slate-600 hover:bg-slate-100 hover:text-slate-900' }}">
                    <svg class="w-5 h-5 flex-shrink-0 {{ request()->routeIs('asesi.dashboard') ? 'text-blue-600' : 'text-slate-400' }}" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="1.75">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M3.75 6A2.25 2.25 0 016 3.75h2.25A2.25 2.25 0 0110.5 6v2.25a2.25 2.25 0 01-2.25 2.25H6a2.25 2.25 0 01-2.25-2.25V6zM3.75 15.75A2.25 2.25 0 016 13.5h2.25a2.25 2.25 0 012.25 2.25V18a2.25 2.25 0 01-2.25 2.25H6A2.25 2.25 0 013.75 18v-2.25zM13.5 6a2.25 2.25 0 012.25-2.25H18A2.25 2.25 0 0120.25 6v2.25A2.25 2.25 0 0118 10.5h-2.25a2.25 2.25 0 01-2.25-2.25V6zM13.5 15.75a2.25 2.25 0 012.25-2.25H18a2.25 2.25 0 012.25 2.25V18A2.25 2.25 0 0118 20.25h-2.25A2.25 2.25 0 0113.5 18v-2.25z"/>
                    </svg>
                    <span>Dashboard</span>
                </a>
            </li>

            <!-- Group Label: TAHAPAN SERTIFIKASI -->
            <li class="pt-4 pb-1 px-3">
                <span class="text-[11px] font-bold tracking-wider text-slate-400 uppercase">TAHAPAN SERTIFIKASI</span>
            </li>

            <!-- 2. Jadwal & Lokasi Uji -->
            <li>
                <a href="{{ route('asesi.jadwal') }}" 
                   class="flex items-center gap-3 px-3 py-2.5 rounded-xl transition-all duration-150 {{ request()->routeIs('asesi.jadwal*') ? 'bg-blue-50 text-blue-700 font-semibold shadow-xs' : 'text-slate-600 hover:bg-slate-100 hover:text-slate-900' }}">
                    <svg class="w-5 h-5 flex-shrink-0 {{ request()->routeIs('asesi.jadwal*') ? 'text-blue-600' : 'text-slate-400' }}" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="1.75">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M6.75 3v2.25M17.25 3v2.25M3 18.75V7.5a2.25 2.25 0 012.25-2.25h13.5A2.25 2.25 0 0121 7.5v11.25m-18 0A2.25 2.25 0 005.25 21h13.5A2.25 2.25 0 0021 18.75m-18 0v-7.5A2.25 2.25 0 015.25 9h13.5A2.25 2.25 0 0121 11.25v7.5"/>
                    </svg>
                    <span>Jadwal & Lokasi Uji</span>
                </a>
            </li>

            <!-- 3. Tahapan Asesmen (APL.01, APL.02, AK.01) - MENU GABUNGAN UTAMA -->
            <li>
                <a href="{{ route('asesi.tahapan') }}" 
                   class="flex items-center justify-between px-3 py-2.5 rounded-xl transition-all duration-150 {{ (request()->routeIs('asesi.tahapan*') || request()->routeIs('asesi.formulir*') || request()->routeIs('asesi.biodata*') || request()->routeIs('asesi.apl02*') || request()->routeIs('asesi.ak01*') || request()->routeIs('asesi.pendaftaran*')) ? 'bg-blue-50 text-blue-700 font-semibold shadow-xs' : 'text-slate-600 hover:bg-slate-100 hover:text-slate-900' }}">
                    <div class="flex items-center gap-3">
                        <svg class="w-5 h-5 flex-shrink-0 {{ (request()->routeIs('asesi.tahapan*') || request()->routeIs('asesi.formulir*') || request()->routeIs('asesi.biodata*') || request()->routeIs('asesi.apl02*') || request()->routeIs('asesi.ak01*') || request()->routeIs('asesi.pendaftaran*')) ? 'text-blue-600' : 'text-slate-400' }}" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="1.75">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M9 12h3.75M9 15h3.75M9 18h3.75m3 .75H18a2.25 2.25 0 002.25-2.25V6.108c0-1.135-.845-2.098-1.976-2.192a48.424 48.424 0 00-1.123-.08m-5.801 0c-.065.21-.1.433-.1.664 0 .414.336.75.75.75h4.5a.75.75 0 00.75-.75 2.25 2.25 0 00-.1-.664m-5.8 0A2.251 2.251 0 0113.5 2.25H15c1.012 0 1.867.668 2.15 1.586m-5.8 0c-.376.023-.75.05-1.124.08C9.095 4.01 8.25 4.973 8.25 6.108V8.25m0 0H4.875c-.621 0-1.125.504-1.125 1.125v11.25c0 .621.504 1.125 1.125 1.125h9.75c.621 0 1.125-.504 1.125-1.125V9.375c0-.621-.504-1.125-1.125-1.125H8.25zM6.75 12h.008v.008H6.75V12zm0 3h.008v.008H6.75V15zm0 3h.008v.008H6.75V18z"/>
                        </svg>
                        <span>Tahapan Asesmen</span>
                    </div>

                    @if($isTahapanLengkap)
                        <span class="bg-emerald-50 text-emerald-700 border border-emerald-200 text-[10px] px-2 py-0.5 rounded-full font-semibold flex-shrink-0">Lengkap</span>
                    @endif
                </a>
            </li>

            <!-- 4. Ruang Ujian Online (FR.IA) -->
            <li>
                <a href="{{ route('asesi.ujian') }}" 
                   class="flex items-center gap-3 px-3 py-2.5 rounded-xl transition-all duration-150 {{ (request()->routeIs('asesi.ujian*') || request()->routeIs('formulir.*')) ? 'bg-blue-50 text-blue-700 font-semibold shadow-xs' : 'text-slate-600 hover:bg-slate-100 hover:text-slate-900' }}">
                    <svg class="w-5 h-5 flex-shrink-0 {{ (request()->routeIs('asesi.ujian*') || request()->routeIs('formulir.*')) ? 'text-blue-600' : 'text-slate-400' }}" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="1.75">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M4.26 10.147a60.436 60.436 0 00-.491 6.347A48.627 48.627 0 0112 20.904a48.627 48.627 0 018.232-4.41 60.46 60.46 0 00-.491-6.347m-15.482 0a50.57 50.57 0 00-2.658-.813A59.905 59.905 0 0112 3.493a59.902 59.902 0 0110.399 5.84c-.896.248-1.783.52-2.658.814m-15.482 0A50.697 50.697 0 0112 13.489a50.702 50.702 0 017.74-3.342M6.75 15a.75.75 0 100-1.5.75.75 0 000 1.5zm0 0v-3.675A55.378 55.378 0 0112 8.443m-7.007 11.55A5.981 5.981 0 006.75 15.75v-1.5"/>
                    </svg>
                    <span>Ruang Ujian Online</span>
                </a>
            </li>

            <!-- Group Label: LAPORAN -->
            <li class="pt-4 pb-1 px-3">
                <span class="text-[11px] font-bold tracking-wider text-slate-400 uppercase">LAPORAN</span>
            </li>

            <!-- 5. Hasil & Sertifikat -->
            <li>
                <a href="{{ route('asesi.hasil') }}" 
                   class="flex items-center gap-3 px-3 py-2.5 rounded-xl transition-all duration-150 {{ (request()->routeIs('asesi.hasil*') || request()->routeIs('asesi.hasil-nilai*')) ? 'bg-blue-50 text-blue-700 font-semibold shadow-xs' : 'text-slate-600 hover:bg-slate-100 hover:text-slate-900' }}">
                    <svg class="w-5 h-5 flex-shrink-0 {{ (request()->routeIs('asesi.hasil*') || request()->routeIs('asesi.hasil-nilai*')) ? 'text-blue-600' : 'text-slate-400' }}" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="1.75">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M16.5 18.75h-9m9 0a3 3 0 013 3h-15a3 3 0 013-3m9 0v-3.375c0-.621-.504-1.125-1.125-1.125h-.871M7.5 18.75v-3.375c0-.621.504-1.125 1.125-1.125h.872m5.007-4.125A6.002 6.002 0 0012 3.75a6.002 6.002 0 00-5.496 6.504L6.75 15h10.5l.246-4.746z"/>
                    </svg>
                    <span>Hasil & Sertifikat</span>
                </a>
            </li>
        </ul>
    </div>

    <!-- 3. Footer Profil Asesi (Bawah Sidebar) -->
    <div class="border-t border-slate-200 pt-3 mt-6 pb-2 px-1">
        <div class="flex items-center gap-3 mb-3 px-2">
            <div class="w-9 h-9 rounded-full bg-slate-100 border border-slate-200 flex items-center justify-center text-slate-700 font-semibold text-xs flex-shrink-0">
                {{ $inisialNama }}
            </div>
            <div class="overflow-hidden flex-1">
                <div class="font-semibold text-xs text-slate-800 truncate" title="{{ $namaAsesi }}">
                    {{ $namaAsesi }}
                </div>
                <div class="text-[11px] text-slate-500 truncate">
                    Asesi &bull; NISN: {{ $identitasAsesi ?: '-' }}
                </div>
            </div>
        </div>

        <form action="{{ route('keluar') }}" method="POST">
            @csrf
            <button type="submit" class="w-full py-2 px-3 text-xs font-medium text-slate-600 hover:text-slate-900 bg-slate-100 hover:bg-slate-200 border border-slate-200/80 rounded-xl transition-all duration-150 flex items-center justify-center gap-2">
                <svg class="w-4 h-4 text-slate-500" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="1.75">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M15.75 9V5.25A2.25 2.25 0 0013.5 3h-6a2.25 2.25 0 00-2.25 2.25v13.5A2.25 2.25 0 007.5 21h6a2.25 2.25 0 002.25-2.25V15M12 9l-3 3m0 0l3 3m-3-3h12.75"/>
                </svg>
                <span>Keluar</span>
            </button>
        </form>
    </div>
</div>
