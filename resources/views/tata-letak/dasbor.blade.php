<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('judul', 'Dasbor') - LSP SMKN 1 Gunungputri</title>
    <link rel="icon" type="image/jpeg" href="{{ asset('images/logo-lsp.jpeg') }}">
    
    <!-- Google Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Public+Sans:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    
    <!-- FontAwesome Icons -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">

    <!-- SweetAlert2 Pop-up Notifications -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/sweetalert2@11/dist/sweetalert2.min.css">

    <!-- CSS Tata Letak & Animasi Global -->
    <link rel="stylesheet" href="{{ asset('css/tata-letak.css') }}">
    <link rel="stylesheet" href="{{ asset('css/animasi.css') }}">

    <!-- Tailwind CSS & Alpine.js -->
    <script src="https://cdn.tailwindcss.com"></script>
    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.14.8/dist/cdn.min.js"></script>

    <!-- CSS Khusus Per Halaman -->
    @stack('css')
</head>
<body class="bg-slate-50/70 text-slate-800 antialiased min-h-screen">
@php
    $user = auth()->user();
    $peran = $user ? $user->peran : null;
    $isNavbarPortal = true;

    $brandRoute = match($peran) {
        'asesi' => route('asesi.dashboard'),
        'asesor' => route('asesor.dashboard'),
        'superadmin' => route('superadmin.dashboard'),
        default => route('admin.dashboard'),
    };
    $roleBadgeClass = match($peran) {
        'asesi' => 'text-blue-600',
        'asesor' => 'text-indigo-600',
        'superadmin' => 'text-rose-600',
        default => 'text-emerald-600',
    };
    $userAvatarClass = match($peran) {
        'asesi' => 'bg-blue-50 text-blue-700 border-blue-200',
        'asesor' => 'bg-indigo-50 text-indigo-700 border-indigo-200',
        'superadmin' => 'bg-rose-50 text-rose-700 border-rose-200',
        default => 'bg-emerald-50 text-emerald-700 border-emerald-200',
    };
@endphp

@if($isNavbarPortal)
    <!-- =========================================================================
         LAYOUT MODE: TOP NAVBAR MINIMALIS (UNTUK SEMUA PERAN)
         - Tanpa Icon di Navbar Links
         - Dropdown Terpadu & Minimalis
         - Desain Tidak Bertumpuk / Lega
         ========================================================================= -->
    <div class="min-h-screen flex flex-col justify-between" x-data="{ mobileNavOpen: false, userDropdownOpen: false, openDropdown: null }">
        
        <!-- TOP STICKY NAVBAR -->
        <nav class="sticky top-0 z-40 bg-white/95 backdrop-blur-md border-b border-slate-200/80 shadow-2xs">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                <div class="flex items-center justify-between h-16 gap-4">
                    
                    <!-- LEFT: BRAND LOGO & ROLE -->
                    <div class="flex items-center gap-3 shrink-0">
                        <a href="{{ $brandRoute }}" class="flex items-center gap-2.5 group">
                            <img src="{{ asset('images/logo-lsp.jpeg') }}" alt="Logo LSP" class="h-8 w-8 object-contain rounded-lg shadow-2xs border border-slate-200/80">
                            <div class="leading-tight">
                                <div class="font-bold text-slate-900 text-xs sm:text-sm group-hover:text-blue-600 transition-colors">
                                    LSP SMKN 1 Gunungputri
                                </div>
                                <div class="text-[10px] tracking-wider font-extrabold uppercase {{ $roleBadgeClass }}">
                                    Portal {{ ucfirst($peran ?? 'Admin') }}
                                </div>
                            </div>
                        </a>
                    </div>

                    <!-- CENTER: MINIMALIST TEXT-ONLY NAVIGATION WITH DROPDOWNS -->
                    <div class="hidden lg:flex items-center gap-1.5 text-xs font-semibold">
                        @if($peran === 'asesi')
                            <!-- 1. Dashboard -->
                            <a href="{{ route('asesi.dashboard') }}" 
                               class="px-3.5 py-2 rounded-xl transition-colors {{ request()->routeIs('asesi.dashboard') ? 'bg-blue-50 text-blue-700 font-bold border border-blue-100' : 'text-slate-600 hover:text-slate-900 hover:bg-slate-100/70 font-medium' }}">
                                Dashboard
                            </a>

                            <!-- 2. Dropdown: Pelaksanaan Asesmen -->
                            @php
                                $isAsesmenActive = request()->routeIs('asesi.jadwal*') || request()->routeIs('asesi.tahapan*') || request()->routeIs('asesi.ak01*') || request()->routeIs('asesi.ak07*') || request()->routeIs('asesi.apl02*') || request()->routeIs('asesi.formulir*') || request()->routeIs('asesi.biodata*') || request()->routeIs('asesi.pendaftaran*') || request()->routeIs('asesi.ujian*');
                            @endphp
                            <div class="relative" @click.outside="if (openDropdown === 'asesmen') openDropdown = null">
                                <button type="button" 
                                        @click="openDropdown = (openDropdown === 'asesmen' ? null : 'asesmen')"
                                        class="px-3.5 py-2 rounded-xl transition-colors flex items-center gap-1.5 cursor-pointer {{ $isAsesmenActive ? 'bg-blue-50 text-blue-700 font-bold border border-blue-100' : 'text-slate-600 hover:text-slate-900 hover:bg-slate-100/70 font-medium' }}">
                                    <span>Formulir Asesmen</span>
                                    <svg class="w-3.5 h-3.5 text-slate-400 transition-transform duration-200" :class="openDropdown === 'asesmen' ? 'rotate-180 text-blue-600' : ''" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
                                    </svg>
                                </button>

                                <div x-show="openDropdown === 'asesmen'" 
                                     x-transition:enter="transition ease-out duration-150"
                                     x-transition:enter-start="opacity-0 scale-95 -translate-y-1"
                                     x-transition:enter-end="opacity-100 scale-100 translate-y-0"
                                     x-transition:leave="transition ease-in duration-100"
                                     x-transition:leave-start="opacity-100 scale-100 translate-y-0"
                                     x-transition:leave-end="opacity-0 scale-95 -translate-y-1"
                                     class="absolute left-0 mt-2 w-64 rounded-2xl bg-white border border-slate-200/90 shadow-xl p-1.5 z-50 space-y-0.5"
                                     style="display: none;">
                                    
                                    <a href="{{ route('asesi.jadwal') }}" 
                                       @click="openDropdown = null"
                                       class="block px-3 py-2 rounded-xl transition-colors {{ request()->routeIs('asesi.jadwal*') ? 'bg-blue-50 text-blue-700 font-semibold' : 'text-slate-700 hover:bg-slate-50 hover:text-slate-900' }}">
                                        <div class="font-semibold text-xs leading-tight">Jadwal & Lokasi Uji</div>
                                        <div class="text-[10px] text-slate-400 mt-0.5">Informasi sesi dan lokasi uji kompetensi</div>
                                    </a>

                                    <a href="{{ route('asesi.tahapan') }}" 
                                       @click="openDropdown = null"
                                       class="block px-3 py-2 rounded-xl transition-colors {{ (request()->routeIs('asesi.tahapan*') || request()->routeIs('asesi.ak01*') || request()->routeIs('asesi.apl02*') || request()->routeIs('asesi.formulir*') || request()->routeIs('asesi.biodata*') || request()->routeIs('asesi.pendaftaran*')) ? 'bg-blue-50 text-blue-700 font-semibold' : 'text-slate-700 hover:bg-slate-50 hover:text-slate-900' }}">
                                        <div class="font-semibold text-xs leading-tight">Tahapan Formulir</div>
                                        <div class="text-[10px] text-slate-400 mt-0.5">Pendaftaran, asesmen mandiri, dan persetujuan</div>
                                    </a>

                                    <a href="{{ route('asesi.ak07') }}" 
                                       @click="openDropdown = null"
                                       class="block px-3 py-2 rounded-xl transition-colors {{ request()->routeIs('asesi.ak07*') ? 'bg-blue-50 text-blue-700 font-semibold' : 'text-slate-700 hover:bg-slate-50 hover:text-slate-900' }}">
                                        <div class="font-semibold text-xs leading-tight">Penyesuaian Asesmen (FR.AK.07)</div>
                                        <div class="text-[10px] text-slate-400 mt-0.5">Ceklis penyesuaian yang wajar & beralasan</div>
                                    </a>

                                    <a href="{{ route('asesi.ujian') }}" 
                                       @click="openDropdown = null"
                                       class="block px-3 py-2 rounded-xl transition-colors {{ request()->routeIs('asesi.ujian*') ? 'bg-blue-50 text-blue-700 font-semibold' : 'text-slate-700 hover:bg-slate-50 hover:text-slate-900' }}">
                                        <div class="font-semibold text-xs leading-tight">Ruang Ujian Online (FR.IA)</div>
                                        <div class="text-[10px] text-slate-400 mt-0.5">Pelaksanaan tes tertulis CBT dan instrumen asesmen</div>
                                    </a>
                                </div>
                            </div>

                            <!-- 3. Dropdown: Hasil & Dokumen -->
                            @php
                                $isHasilActive = request()->routeIs('asesi.hasil*') || request()->routeIs('asesi.hasil-nilai*') || request()->routeIs('asesi.dokumen*');
                            @endphp
                            <div class="relative" @click.outside="if (openDropdown === 'hasil') openDropdown = null">
                                <button type="button" 
                                        @click="openDropdown = (openDropdown === 'hasil' ? null : 'hasil')"
                                        class="px-3.5 py-2 rounded-xl transition-colors flex items-center gap-1.5 cursor-pointer {{ $isHasilActive ? 'bg-blue-50 text-blue-700 font-bold border border-blue-100' : 'text-slate-600 hover:text-slate-900 hover:bg-slate-100/70 font-medium' }}">
                                    <span>Hasil & Portofolio</span>
                                    <svg class="w-3.5 h-3.5 text-slate-400 transition-transform duration-200" :class="openDropdown === 'hasil' ? 'rotate-180 text-blue-600' : ''" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
                                    </svg>
                                </button>

                                <div x-show="openDropdown === 'hasil'" 
                                     x-transition:enter="transition ease-out duration-150"
                                     x-transition:enter-start="opacity-0 scale-95 -translate-y-1"
                                     x-transition:enter-end="opacity-100 scale-100 translate-y-0"
                                     x-transition:leave="transition ease-in duration-100"
                                     x-transition:leave-start="opacity-100 scale-100 translate-y-0"
                                     x-transition:leave-end="opacity-0 scale-95 -translate-y-1"
                                     class="absolute left-0 mt-2 w-64 rounded-2xl bg-white border border-slate-200/90 shadow-xl p-1.5 z-50 space-y-0.5"
                                     style="display: none;">
                                    
                                    <a href="{{ route('asesi.hasil') }}" 
                                       @click="openDropdown = null"
                                       class="block px-3 py-2 rounded-xl transition-colors {{ (request()->routeIs('asesi.hasil*') || request()->routeIs('asesi.hasil-nilai*')) ? 'bg-blue-50 text-blue-700 font-semibold' : 'text-slate-700 hover:bg-slate-50 hover:text-slate-900' }}">
                                        <div class="font-semibold text-xs leading-tight">Hasil & Sertifikat</div>
                                        <div class="text-[10px] text-slate-400 mt-0.5">Keputusan kelulusan dan sertifikasi</div>
                                    </a>

                                    <a href="{{ route('asesi.dokumen') }}" 
                                       @click="openDropdown = null"
                                       class="block px-3 py-2 rounded-xl transition-colors {{ request()->routeIs('asesi.dokumen*') ? 'bg-blue-50 text-blue-700 font-semibold' : 'text-slate-700 hover:bg-slate-50 hover:text-slate-900' }}">
                                        <div class="font-semibold text-xs leading-tight">Berkas & Bukti Portofolio</div>
                                        <div class="text-[10px] text-slate-400 mt-0.5">Manajemen lampiran berkas pendukung</div>
                                    </a>
                                </div>
                            </div>

                        @elseif($peran === 'asesor')
                            <!-- Asesor 1. Dashboard -->
                            <a href="{{ route('asesor.dashboard') }}" 
                               class="px-3.5 py-2 rounded-xl transition-colors {{ request()->routeIs('asesor.dashboard') ? 'bg-indigo-50 text-indigo-700 font-bold border border-indigo-100' : 'text-slate-600 hover:text-slate-900 hover:bg-slate-100/70 font-medium' }}">
                                Dashboard
                            </a>

                            <!-- Asesor 2. Dropdown: Pelaksanaan Asesmen -->
                            @php
                                $isAsesorAsesmenActive = request()->routeIs('asesor.jadwal*') || request()->routeIs('asesor.mapa*') || request()->routeIs('asesor.formulir*') || request()->routeIs('asesor.penilaian*') || request()->routeIs('asesor.daftar-peserta*') || request()->routeIs('asesor.input-penilaian*') || request()->routeIs('asesor.penilaian-live*');
                            @endphp
                            <div class="relative" @click.outside="if (openDropdown === 'asesor_asesmen') openDropdown = null">
                                <button type="button" 
                                        @click="openDropdown = (openDropdown === 'asesor_asesmen' ? null : 'asesor_asesmen')"
                                        class="px-3.5 py-2 rounded-xl transition-colors flex items-center gap-1.5 cursor-pointer {{ $isAsesorAsesmenActive ? 'bg-indigo-50 text-indigo-700 font-bold border border-indigo-100' : 'text-slate-600 hover:text-slate-900 hover:bg-slate-100/70 font-medium' }}">
                                    <span>Pelaksanaan Asesmen</span>
                                    <svg class="w-3.5 h-3.5 text-slate-400 transition-transform duration-200" :class="openDropdown === 'asesor_asesmen' ? 'rotate-180 text-indigo-600' : ''" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
                                    </svg>
                                </button>

                                <div x-show="openDropdown === 'asesor_asesmen'" 
                                     x-transition:enter="transition ease-out duration-150"
                                     x-transition:enter-start="opacity-0 scale-95 -translate-y-1"
                                     x-transition:enter-end="opacity-100 scale-100 translate-y-0"
                                     x-transition:leave="transition ease-in duration-100"
                                     x-transition:leave-start="opacity-100 scale-100 translate-y-0"
                                     x-transition:leave-end="opacity-0 scale-95 -translate-y-1"
                                     class="absolute left-0 mt-2 w-64 rounded-2xl bg-white border border-slate-200/90 shadow-xl p-1.5 z-50 space-y-0.5"
                                     style="display: none;">
                                    
                                    <a href="{{ route('asesor.jadwal') }}" 
                                       @click="openDropdown = null"
                                       class="block px-3 py-2 rounded-xl transition-colors {{ request()->routeIs('asesor.jadwal*') ? 'bg-indigo-50 text-indigo-700 font-semibold' : 'text-slate-700 hover:bg-slate-50 hover:text-slate-900' }}">
                                        <div class="font-semibold text-xs leading-tight">Jadwal & Penugasan</div>
                                        <div class="text-[10px] text-slate-400 mt-0.5">Daftar jadwal dan penugasan asesmen</div>
                                    </a>

                                    <a href="{{ route('asesor.mapa') }}" 
                                       @click="openDropdown = null"
                                       class="block px-3 py-2 rounded-xl transition-colors {{ (request()->routeIs('asesor.mapa*') || request()->routeIs('asesor.formulir*')) ? 'bg-indigo-50 text-indigo-700 font-semibold' : 'text-slate-700 hover:bg-slate-50 hover:text-slate-900' }}">
                                        <div class="font-semibold text-xs leading-tight">Formulir</div>
                                        <div class="text-[10px] text-slate-400 mt-0.5">Pembuatan formulir MAPA dan FR.IA</div>
                                    </a>

                                    <a href="{{ route('asesor.penilaian') }}" 
                                       @click="openDropdown = null"
                                       class="block px-3 py-2 rounded-xl transition-colors {{ (request()->routeIs('asesor.penilaian*') || request()->routeIs('asesor.daftar-peserta*') || request()->routeIs('asesor.input-penilaian*') || request()->routeIs('asesor.penilaian-live*')) ? 'bg-indigo-50 text-indigo-700 font-semibold' : 'text-slate-700 hover:bg-slate-50 hover:text-slate-900' }}">
                                        <div class="font-semibold text-xs leading-tight">Penilaian Peserta</div>
                                        <div class="text-[10px] text-slate-400 mt-0.5">Verifikasi APL.02, observasi praktik & live ujian</div>
                                    </a>

                                    <a href="{{ route('asesor.koreksi-teori') }}" 
                                       @click="openDropdown = null"
                                       class="block px-3 py-2 rounded-xl transition-colors {{ request()->routeIs('asesor.koreksi-teori*') ? 'bg-indigo-50 text-indigo-700 font-semibold' : 'text-slate-700 hover:bg-slate-50 hover:text-slate-900' }}">
                                        <div class="font-semibold text-xs leading-tight flex items-center justify-between">
                                            <span>Koreksi Teori (IA.05 & 06)</span>
                                            <span class="px-1.5 py-0.2 rounded bg-indigo-100 text-indigo-800 text-[9px] font-bold">Baru</span>
                                        </div>
                                        <div class="text-[10px] text-slate-400 mt-0.5">Rekap CBT & koreksi esai seluruh asesi dalam 1 layar</div>
                                    </a>
                                </div>
                            </div>

                            <!-- Asesor 3. Link: Berita Acara & Rekap -->
                            @php
                                $isAsesorHasilActive = request()->routeIs('asesor.berita-acara*');
                            @endphp
                            <a href="{{ route('asesor.berita-acara') }}" 
                               class="px-3.5 py-2 rounded-xl transition-colors flex items-center gap-1.5 cursor-pointer {{ $isAsesorHasilActive ? 'bg-indigo-50 text-indigo-700 font-bold border border-indigo-100' : 'text-slate-600 hover:text-slate-900 hover:bg-slate-100/70 font-medium' }}">
                                <span>Berita Acara & Rekap</span>
                            </a>

                            <!-- Asesor 4. Link: Dokumen Asesmen -->
                            @php
                                $isAsesorDokumenActive = request()->routeIs('dokumen-asesmen.*');
                            @endphp
                            <a href="{{ route('dokumen-asesmen.index') }}" 
                               class="px-3.5 py-2 rounded-xl transition-colors flex items-center gap-1.5 cursor-pointer {{ $isAsesorDokumenActive ? 'bg-indigo-50 text-indigo-700 font-bold border border-indigo-100' : 'text-slate-600 hover:text-slate-900 hover:bg-slate-100/70 font-medium' }}">
                                <span>Dokumen Asesmen</span>
                            </a>




                        @elseif($peran === 'superadmin')
                            <!-- Superadmin 1. Control Panel -->
                            <a href="{{ route('superadmin.dashboard') }}" 
                               class="px-3.5 py-2 rounded-xl transition-colors {{ request()->routeIs('superadmin.dashboard') ? 'bg-rose-50 text-rose-700 font-bold border border-rose-100' : 'text-slate-600 hover:text-slate-900 hover:bg-slate-100/70 font-medium' }}">
                                Control Panel
                            </a>

                            <!-- Superadmin 2. Dropdown: Sistem & Keamanan -->
                            @php
                                $isSuperadminSistemActive = request()->routeIs('superadmin.manajemen-pengguna*') || request()->routeIs('superadmin.log-aktivitas*') || request()->routeIs('superadmin.pengaturan-sistem*') || request()->routeIs('formulir.*');
                            @endphp
                            <div class="relative" @click.outside="if (openDropdown === 'superadmin_sistem') openDropdown = null">
                                <button type="button" 
                                        @click="openDropdown = (openDropdown === 'superadmin_sistem' ? null : 'superadmin_sistem')"
                                        class="px-3.5 py-2 rounded-xl transition-colors flex items-center gap-1.5 cursor-pointer {{ $isSuperadminSistemActive ? 'bg-rose-50 text-rose-700 font-bold border border-rose-100' : 'text-slate-600 hover:text-slate-900 hover:bg-slate-100/70 font-medium' }}">
                                    <span>Sistem & Keamanan</span>
                                    <svg class="w-3.5 h-3.5 text-slate-400 transition-transform duration-200" :class="openDropdown === 'superadmin_sistem' ? 'rotate-180 text-rose-600' : ''" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
                                    </svg>
                                </button>

                                <div x-show="openDropdown === 'superadmin_sistem'" 
                                     x-transition:enter="transition ease-out duration-150"
                                     x-transition:enter-start="opacity-0 scale-95 -translate-y-1"
                                     x-transition:enter-end="opacity-100 scale-100 translate-y-0"
                                     x-transition:leave="transition ease-in duration-100"
                                     x-transition:leave-start="opacity-100 scale-100 translate-y-0"
                                     x-transition:leave-end="opacity-0 scale-95 -translate-y-1"
                                     class="absolute left-0 mt-2 w-64 rounded-2xl bg-white border border-slate-200/90 shadow-xl p-1.5 z-50 space-y-0.5"
                                     style="display: none;">
                                    
                                    <a href="{{ route('superadmin.manajemen-pengguna') }}" 
                                       @click="openDropdown = null"
                                       class="block px-3 py-2 rounded-xl transition-colors {{ request()->routeIs('superadmin.manajemen-pengguna*') ? 'bg-rose-50 text-rose-700 font-semibold' : 'text-slate-700 hover:bg-slate-50 hover:text-slate-900' }}">
                                        <div class="font-semibold text-xs leading-tight">Manajemen Akun</div>
                                        <div class="text-[10px] text-slate-400 mt-0.5">Kelola akun pengguna, peran & hak akses</div>
                                    </a>

                                    <a href="{{ route('superadmin.log-aktivitas') }}" 
                                       @click="openDropdown = null"
                                       class="block px-3 py-2 rounded-xl transition-colors {{ request()->routeIs('superadmin.log-aktivitas*') ? 'bg-rose-50 text-rose-700 font-semibold' : 'text-slate-700 hover:bg-slate-50 hover:text-slate-900' }}">
                                        <div class="font-semibold text-xs leading-tight">Audit Log Aktivitas</div>
                                        <div class="text-[10px] text-slate-400 mt-0.5">Riwayat log & jejak audit sistem</div>
                                    </a>

                                    <a href="{{ route('superadmin.pengaturan-sistem') }}" 
                                       @click="openDropdown = null"
                                       class="block px-3 py-2 rounded-xl transition-colors {{ request()->routeIs('superadmin.pengaturan-sistem*') ? 'bg-rose-50 text-rose-700 font-semibold' : 'text-slate-700 hover:bg-slate-50 hover:text-slate-900' }}">
                                        <div class="font-semibold text-xs leading-tight">Pengaturan Global</div>
                                        <div class="text-[10px] text-slate-400 mt-0.5">Konfigurasi parameter sistem & lisensi</div>
                                    </a>
                                </div>
                            </div>

                            <!-- Superadmin 3. Dropdown: Dokumen Asesmen -->
                            @php
                                $isSuperadminDokumenActive = request()->routeIs('dokumen-asesmen.*');
                            @endphp
                            <div class="relative" @click.outside="if (openDropdown === 'superadmin_dokumen_asesmen') openDropdown = null">
                                <button type="button" 
                                        @click="openDropdown = (openDropdown === 'superadmin_dokumen_asesmen' ? null : 'superadmin_dokumen_asesmen')"
                                        class="px-3.5 py-2 rounded-xl transition-colors flex items-center gap-1.5 cursor-pointer {{ $isSuperadminDokumenActive ? 'bg-rose-50 text-rose-700 font-bold border border-rose-100' : 'text-slate-600 hover:text-slate-900 hover:bg-slate-100/70 font-medium' }}">
                                    <span>Dokumen Asesmen</span>
                                    <svg class="w-3.5 h-3.5 text-slate-400 transition-transform duration-200" :class="openDropdown === 'superadmin_dokumen_asesmen' ? 'rotate-180 text-rose-600' : ''" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
                                    </svg>
                                </button>

                                <div x-show="openDropdown === 'superadmin_dokumen_asesmen'" 
                                     x-transition:enter="transition ease-out duration-150"
                                     x-transition:enter-start="opacity-0 scale-95 -translate-y-1"
                                     x-transition:enter-end="opacity-100 scale-100 translate-y-0"
                                     x-transition:leave="transition ease-in duration-100"
                                     x-transition:leave-start="opacity-100 scale-100 translate-y-0"
                                     x-transition:leave-end="opacity-0 scale-95 -translate-y-1"
                                     class="absolute left-0 mt-2 w-72 rounded-2xl bg-white border border-slate-200/90 shadow-xl p-1.5 z-50 space-y-0.5"
                                     style="display: none;">
                                    
                                    <a href="{{ route('dokumen-asesmen.index') }}" 
                                       @click="openDropdown = null"
                                       class="block px-3 py-2 rounded-xl transition-colors {{ request()->routeIs('dokumen-asesmen.index') ? 'bg-rose-50 text-rose-700 font-semibold' : 'text-slate-700 hover:bg-slate-50 hover:text-slate-900' }}">
                                        <div class="font-semibold text-xs leading-tight flex items-center justify-between">
                                            <span>Pusat Dokumen Asesmen</span>
                                            <span class="text-[9px] px-1.5 py-0.2 rounded bg-rose-100 text-rose-700 font-mono">Hub</span>
                                        </div>
                                        <div class="text-[10px] text-slate-400 mt-0.5">Audit, reopen versi, & monitoring seluruh dokumen</div>
                                    </a>

                                    <div class="border-t border-slate-100 my-1"></div>

                                    <a href="{{ route('dokumen-asesmen.index', ['jenis' => 'FR.AK.02']) }}" 
                                       @click="openDropdown = null"
                                       class="block px-3 py-1.5 rounded-xl transition-colors hover:bg-slate-50 text-slate-700 hover:text-slate-900">
                                        <div class="font-semibold text-xs leading-tight">FR.AK.02 &bull; Rekaman Asesmen</div>
                                        <div class="text-[10px] text-slate-400">Keputusan K/BK unit kompetensi</div>
                                    </a>

                                    <a href="{{ route('dokumen-asesmen.index', ['jenis' => 'FR.AK.03']) }}" 
                                       @click="openDropdown = null"
                                       class="block px-3 py-1.5 rounded-xl transition-colors hover:bg-slate-50 text-slate-700 hover:text-slate-900">
                                        <div class="font-semibold text-xs leading-tight">FR.AK.03 &bull; Umpan Balik Asesmen</div>
                                        <div class="text-[10px] text-slate-400">Arsip respon kuesioner asesi</div>
                                    </a>

                                    <a href="{{ route('dokumen-asesmen.ak05.index') }}" 
                                       @click="openDropdown = null"
                                       class="block px-3 py-1.5 rounded-xl transition-colors hover:bg-slate-50 text-slate-700 hover:text-slate-900">
                                        <div class="font-semibold text-xs leading-tight">FR.AK.05 &bull; Laporan Asesmen</div>
                                        <div class="text-[10px] text-slate-400">Laporan rekapitulasi sesi uji asesor</div>
                                    </a>

                                    <a href="{{ route('dokumen-asesmen.ak06.index') }}" 
                                       @click="openDropdown = null"
                                       class="block px-3 py-1.5 rounded-xl transition-colors hover:bg-slate-50 text-slate-700 hover:text-slate-900">
                                        <div class="font-semibold text-xs leading-tight">FR.AK.06 &bull; Meninjau Proses Asesmen</div>
                                        <div class="text-[10px] text-slate-400">Review mutu 4 prinsip & 5 dimensi</div>
                                    </a>

                                    <a href="{{ route('dokumen-asesmen.va.index') }}" 
                                       @click="openDropdown = null"
                                       class="block px-3 py-1.5 rounded-xl transition-colors hover:bg-slate-50 text-slate-700 hover:text-slate-900">
                                        <div class="font-semibold text-xs leading-tight">FR.VA &bull; Validasi Asesmen</div>
                                        <div class="text-[10px] text-slate-400">Validasi independen & perbaikan mutu</div>
                                    </a>
                                </div>
                            </div>

                        @else
                            <!-- Admin (Default) 1. Dashboard -->
                            <a href="{{ route('admin.dashboard') }}" 
                               class="px-3.5 py-2 rounded-xl transition-colors {{ request()->routeIs('admin.dashboard') ? 'bg-emerald-50 text-emerald-700 font-bold border border-emerald-100' : 'text-slate-600 hover:text-slate-900 hover:bg-slate-100/70 font-medium' }}">
                                Dashboard
                            </a>

                            <!-- Admin 2. Dropdown: Asesi -->
                            @php
                                $isAdminAsesiActive = request()->routeIs('admin.manajemen-asesi*') || request()->routeIs('admin.verifikasi*') || request()->routeIs('admin.detail-asesi');
                            @endphp
                            <div class="relative" @click.outside="if (openDropdown === 'admin_asesi') openDropdown = null">
                                <button type="button" 
                                        @click="openDropdown = (openDropdown === 'admin_asesi' ? null : 'admin_asesi')"
                                        class="px-3.5 py-2 rounded-xl transition-colors flex items-center gap-1.5 cursor-pointer {{ $isAdminAsesiActive ? 'bg-emerald-50 text-emerald-700 font-bold border border-emerald-100' : 'text-slate-600 hover:text-slate-900 hover:bg-slate-100/70 font-medium' }}">
                                    <span>Asesi</span>
                                    <svg class="w-3.5 h-3.5 text-slate-400 transition-transform duration-200" :class="openDropdown === 'admin_asesi' ? 'rotate-180 text-emerald-600' : ''" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
                                    </svg>
                                </button>

                                <div x-show="openDropdown === 'admin_asesi'" 
                                     x-transition:enter="transition ease-out duration-150"
                                     x-transition:enter-start="opacity-0 scale-95 -translate-y-1"
                                     x-transition:enter-end="opacity-100 scale-100 translate-y-0"
                                     x-transition:leave="transition ease-in duration-100"
                                     x-transition:leave-start="opacity-100 scale-100 translate-y-0"
                                     x-transition:leave-end="opacity-0 scale-95 -translate-y-1"
                                     class="absolute left-0 mt-2 w-64 rounded-2xl bg-white border border-slate-200/90 shadow-xl p-1.5 z-50 space-y-0.5"
                                     style="display: none;">
                                    
                                    <a href="{{ route('admin.manajemen-asesi') }}" 
                                       @click="openDropdown = null"
                                       class="block px-3 py-2 rounded-xl transition-colors {{ $isAdminAsesiActive ? 'bg-emerald-50 text-emerald-700 font-semibold' : 'text-slate-700 hover:bg-slate-50 hover:text-slate-900' }}">
                                        <div class="font-semibold text-xs leading-tight">Data & Verifikasi</div>
                                        <div class="text-[10px] text-slate-400 mt-0.5">Kelola data peserta asesi & verifikasi berkas</div>
                                    </a>
                                </div>
                            </div>

                            <!-- Admin 3. Dropdown: Skema & Asesmen -->
                            @php
                                $isAdminSkemaActive = request()->routeIs('admin.manajemen-skema*') || request()->routeIs('admin.manajemen-jadwal*') || request()->routeIs('admin.master-muk*');
                            @endphp
                            <div class="relative" @click.outside="if (openDropdown === 'admin_skema') openDropdown = null">
                                <button type="button" 
                                        @click="openDropdown = (openDropdown === 'admin_skema' ? null : 'admin_skema')"
                                        class="px-3.5 py-2 rounded-xl transition-colors flex items-center gap-1.5 cursor-pointer {{ $isAdminSkemaActive ? 'bg-emerald-50 text-emerald-700 font-bold border border-emerald-100' : 'text-slate-600 hover:text-slate-900 hover:bg-slate-100/70 font-medium' }}">
                                    <span>Skema</span>
                                    <svg class="w-3.5 h-3.5 text-slate-400 transition-transform duration-200" :class="openDropdown === 'admin_skema' ? 'rotate-180 text-emerald-600' : ''" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
                                    </svg>
                                </button>

                                <div x-show="openDropdown === 'admin_skema'" 
                                     x-transition:enter="transition ease-out duration-150"
                                     x-transition:enter-start="opacity-0 scale-95 -translate-y-1"
                                     x-transition:enter-end="opacity-100 scale-100 translate-y-0"
                                     x-transition:leave="transition ease-in duration-100"
                                     x-transition:leave-start="opacity-100 scale-100 translate-y-0"
                                     x-transition:leave-end="opacity-0 scale-95 -translate-y-1"
                                     class="absolute left-0 mt-2 w-64 rounded-2xl bg-white border border-slate-200/90 shadow-xl p-1.5 z-50 space-y-0.5"
                                     style="display: none;">
                                    
                                    <a href="{{ route('admin.manajemen-skema') }}" 
                                       @click="openDropdown = null"
                                       class="block px-3 py-2 rounded-xl transition-colors {{ request()->routeIs('admin.manajemen-skema*') ? 'bg-emerald-50 text-emerald-700 font-semibold' : 'text-slate-700 hover:bg-slate-50 hover:text-slate-900' }}">
                                        <div class="font-semibold text-xs leading-tight">Master Skema</div>
                                        <div class="text-[10px] text-slate-400 mt-0.5">Master skema sertifikasi & unit kompetensi</div>
                                    </a>

                                    <a href="{{ route('admin.manajemen-jadwal') }}" 
                                       @click="openDropdown = null"
                                       class="block px-3 py-2 rounded-xl transition-colors {{ request()->routeIs('admin.manajemen-jadwal*') ? 'bg-emerald-50 text-emerald-700 font-semibold' : 'text-slate-700 hover:bg-slate-50 hover:text-slate-900' }}">
                                        <div class="font-semibold text-xs leading-tight">Jadwal Asesmen</div>
                                        <div class="text-[10px] text-slate-400 mt-0.5">Jadwal gelombang & penugasan asesor</div>
                                    </a>

                                    <a href="{{ route('admin.master-muk.index') }}" 
                                       @click="openDropdown = null"
                                       class="block px-3 py-2 rounded-xl transition-colors {{ request()->routeIs('admin.master-muk*') ? 'bg-emerald-50 text-emerald-700 font-semibold' : 'text-slate-700 hover:bg-slate-50 hover:text-slate-900' }}">
                                        <div class="font-semibold text-xs leading-tight">Formulir</div>
                                        <div class="text-[10px] text-slate-400 mt-0.5">Pembuatan formulir MAPA dan FR.IA per skema</div>
                                    </a>
                                </div>
                            </div>

                            <!-- Admin 4. Dropdown: Dokumen Asesmen -->
                            @php
                                $isAdminDokumenActive = request()->routeIs('dokumen-asesmen.*');
                            @endphp
                            <div class="relative" @click.outside="if (openDropdown === 'admin_dokumen_asesmen') openDropdown = null">
                                <button type="button" 
                                        @click="openDropdown = (openDropdown === 'admin_dokumen_asesmen' ? null : 'admin_dokumen_asesmen')"
                                        class="px-3.5 py-2 rounded-xl transition-colors flex items-center gap-1.5 cursor-pointer {{ $isAdminDokumenActive ? 'bg-emerald-50 text-emerald-700 font-bold border border-emerald-100' : 'text-slate-600 hover:text-slate-900 hover:bg-slate-100/70 font-medium' }}">
                                    <span>Dokumen</span>
                                    <svg class="w-3.5 h-3.5 text-slate-400 transition-transform duration-200" :class="openDropdown === 'admin_dokumen_asesmen' ? 'rotate-180 text-emerald-600' : ''" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
                                    </svg>
                                </button>

                                <div x-show="openDropdown === 'admin_dokumen_asesmen'" 
                                     x-transition:enter="transition ease-out duration-150"
                                     x-transition:enter-start="opacity-0 scale-95 -translate-y-1"
                                     x-transition:enter-end="opacity-100 scale-100 translate-y-0"
                                     x-transition:leave="transition ease-in duration-100"
                                     x-transition:leave-start="opacity-100 scale-100 translate-y-0"
                                     x-transition:leave-end="opacity-0 scale-95 -translate-y-1"
                                     class="absolute left-0 mt-2 w-72 rounded-2xl bg-white border border-slate-200/90 shadow-xl p-1.5 z-50 space-y-0.5"
                                     style="display: none;">
                                    
                                    <a href="{{ route('dokumen-asesmen.index') }}" 
                                       @click="openDropdown = null"
                                       class="block px-3 py-2 rounded-xl transition-colors {{ request()->routeIs('dokumen-asesmen.index') ? 'bg-emerald-50 text-emerald-700 font-semibold' : 'text-slate-700 hover:bg-slate-50 hover:text-slate-900' }}">
                                        <div class="font-semibold text-xs leading-tight flex items-center justify-between">
                                            <span>Pusat Dokumen Asesmen</span>
                                            <span class="text-[9px] px-1.5 py-0.2 rounded bg-emerald-100 text-emerald-700 font-mono">Hub</span>
                                        </div>
                                        <div class="text-[10px] text-slate-400 mt-0.5">Monitoring status, filter & audit trail seluruh dokumen</div>
                                    </a>

                                    <div class="border-t border-slate-100 my-1"></div>

                                    <a href="{{ route('dokumen-asesmen.index', ['jenis' => 'FR.AK.02']) }}" 
                                       @click="openDropdown = null"
                                       class="block px-3 py-1.5 rounded-xl transition-colors hover:bg-slate-50 text-slate-700 hover:text-slate-900">
                                        <div class="font-semibold text-xs leading-tight">FR.AK.02 &bull; Rekaman Asesmen</div>
                                        <div class="text-[10px] text-slate-400">Keputusan K/BK unit kompetensi</div>
                                    </a>

                                    <a href="{{ route('dokumen-asesmen.index', ['jenis' => 'FR.AK.03']) }}" 
                                       @click="openDropdown = null"
                                       class="block px-3 py-1.5 rounded-xl transition-colors hover:bg-slate-50 text-slate-700 hover:text-slate-900">
                                        <div class="font-semibold text-xs leading-tight">FR.AK.03 &bull; Umpan Balik Asesmen</div>
                                        <div class="text-[10px] text-slate-400">Arsip respon kuesioner asesi</div>
                                    </a>

                                    <a href="{{ route('dokumen-asesmen.ak05.index') }}" 
                                       @click="openDropdown = null"
                                       class="block px-3 py-1.5 rounded-xl transition-colors hover:bg-slate-50 text-slate-700 hover:text-slate-900">
                                        <div class="font-semibold text-xs leading-tight">FR.AK.05 &bull; Laporan Asesmen</div>
                                        <div class="text-[10px] text-slate-400">Laporan rekapitulasi sesi uji asesor</div>
                                    </a>

                                    <a href="{{ route('dokumen-asesmen.ak06.index') }}" 
                                       @click="openDropdown = null"
                                       class="block px-3 py-1.5 rounded-xl transition-colors hover:bg-slate-50 text-slate-700 hover:text-slate-900">
                                        <div class="font-semibold text-xs leading-tight">FR.AK.06 &bull; Meninjau Proses Asesmen</div>
                                        <div class="text-[10px] text-slate-400">Review mutu 4 prinsip & 5 dimensi</div>
                                    </a>

                                    <a href="{{ route('dokumen-asesmen.va.index') }}" 
                                       @click="openDropdown = null"
                                       class="block px-3 py-1.5 rounded-xl transition-colors hover:bg-slate-50 text-slate-700 hover:text-slate-900">
                                        <div class="font-semibold text-xs leading-tight">FR.VA &bull; Validasi Asesmen</div>
                                        <div class="text-[10px] text-slate-400">Validasi independen & perbaikan mutu</div>
                                    </a>

                                    <div class="border-t border-slate-100 my-1"></div>

                                    <a href="{{ route('admin.dokumen.index') }}" 
                                       @click="openDropdown = null"
                                       class="block px-3 py-1.5 rounded-xl transition-colors hover:bg-slate-50 text-slate-700 hover:text-slate-900">
                                        <div class="font-semibold text-xs leading-tight">Arsip Berkas & Dokumen Lainnya</div>
                                        <div class="text-[10px] text-slate-400">Manajemen berkas umum LSP</div>
                                    </a>
                                </div>
                            </div>

                            <!-- Admin 5. Informasi -->
                            <a href="{{ route('admin.manajemen-pengumuman') }}" 
                               class="px-3.5 py-2 rounded-xl transition-colors {{ request()->routeIs('admin.manajemen-pengumuman*') ? 'bg-emerald-50 text-emerald-700 font-bold border border-emerald-100' : 'text-slate-600 hover:text-slate-900 hover:bg-slate-100/70 font-medium' }}">
                                Informasi
                            </a>

                            <!-- Admin 6. Laporan -->
                            <a href="{{ route('admin.laporan-kelulusan') }}" 
                               class="px-3.5 py-2 rounded-xl transition-colors {{ request()->routeIs('admin.laporan-kelulusan*') ? 'bg-emerald-50 text-emerald-700 font-bold border border-emerald-100' : 'text-slate-600 hover:text-slate-900 hover:bg-slate-100/70 font-medium' }}">
                                Laporan
                            </a>

                            <!-- Admin 7. Data Asesor -->
                            <a href="{{ route('admin.manajemen-asesor') }}" 
                               class="px-3.5 py-2 rounded-xl transition-colors {{ request()->routeIs('admin.manajemen-asesor*') ? 'bg-emerald-50 text-emerald-700 font-bold border border-emerald-100' : 'text-slate-600 hover:text-slate-900 hover:bg-slate-100/70 font-medium' }}">
                                Asesor
                            </a>
                        @endif
                    </div>

                    <!-- RIGHT: NOTIFICATION BELL & USER PROFILE DROPDOWN -->
                    <div class="flex items-center gap-2 sm:gap-3">
                        <!-- Notification Bell Component -->
                        <x-notification-bell />

                        <!-- User Profile Dropdown -->
                        <div class="relative" @click.outside="userDropdownOpen = false">
                            <button type="button" 
                                    @click="userDropdownOpen = !userDropdownOpen"
                                    class="flex items-center gap-2 p-1 sm:px-2.5 sm:py-1.5 rounded-xl border border-slate-200/80 bg-white hover:bg-slate-50 transition-colors cursor-pointer text-left">
                                <div class="w-8 h-8 rounded-lg {{ $userAvatarClass }} border flex items-center justify-center font-bold text-xs shrink-0">
                                    {{ strtoupper(substr($user?->nama_lengkap ?? 'U', 0, 2)) }}
                                </div>
                                <div class="hidden md:block leading-tight max-w-[130px]">
                                    <div class="font-bold text-xs text-slate-800 truncate" title="{{ $user?->nama_lengkap }}">
                                        {{ $user?->nama_lengkap ?? 'Pengguna' }}
                                    </div>
                                    <div class="text-[10px] text-slate-500 capitalize">
                                        {{ $peran }}
                                    </div>
                                </div>
                                <svg class="w-3 h-3 text-slate-400 hidden sm:inline-block ml-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
                                </svg>
                            </button>

                            <!-- User Dropdown Menu -->
                            <div x-show="userDropdownOpen" 
                                 x-transition:enter="transition ease-out duration-150"
                                 x-transition:enter-start="opacity-0 scale-95"
                                 x-transition:enter-end="opacity-100 scale-100"
                                 x-transition:leave="transition ease-in duration-100"
                                 x-transition:leave-start="opacity-100 scale-100"
                                 x-transition:leave-end="opacity-0 scale-95"
                                 class="absolute right-0 mt-2 w-64 rounded-2xl bg-white border border-slate-200/90 shadow-xl p-2 z-50"
                                 style="display: none;">
                                
                                <!-- Summary Box -->
                                <div class="p-3 bg-slate-50 rounded-xl border border-slate-100 mb-1.5 space-y-0.5">
                                    <div class="font-bold text-xs text-slate-800 truncate">{{ $user?->nama_lengkap ?? 'Pengguna' }}</div>
                                    <div class="text-[11px] text-slate-500 truncate">{{ $user?->email ?? '-' }}</div>
                                    @if($user?->nomor_registrasi)
                                        <div class="text-[10px] text-blue-600 font-mono font-bold mt-1">No. Reg: {{ $user->nomor_registrasi }}</div>
                                    @endif
                                </div>

                                <div class="space-y-0.5 text-xs text-slate-700">


                                    @if($peran === 'asesi')
                                        <a href="{{ route('asesi.profil') }}" class="flex items-center gap-2 px-3 py-2 rounded-xl hover:bg-slate-50 hover:text-blue-600 font-medium transition-colors">
                                            <span>Kelola Profil & Biodata</span>
                                        </a>
                                    @endif

                                    <a href="{{ route('beranda') }}" class="flex items-center gap-2 px-3 py-2 rounded-xl hover:bg-slate-50 hover:text-blue-600 font-medium transition-colors">
                                        <span>Website Publik LSP</span>
                                    </a>

                                    <div class="border-t border-slate-100 my-1"></div>

                                    <form action="{{ route('keluar') }}" method="POST">
                                        @csrf
                                        <button type="submit" class="w-full flex items-center gap-2 px-3 py-2 rounded-xl text-rose-600 hover:bg-rose-50 font-semibold transition-colors text-left cursor-pointer">
                                            <span>Keluar dari Akun</span>
                                        </button>
                                    </form>
                                </div>
                            </div>
                        </div>

                        <!-- Mobile Hamburger Button -->
                        <button type="button" 
                                @click="mobileNavOpen = !mobileNavOpen"
                                class="lg:hidden p-2 rounded-xl text-slate-600 hover:bg-slate-100 transition-colors focus:outline-hidden cursor-pointer"
                                aria-label="Menu Navigasi">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16" x-show="!mobileNavOpen" />
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" x-show="mobileNavOpen" style="display: none;" />
                            </svg>
                        </button>
                    </div>
                </div>
            </div>

            <!-- MOBILE NAVIGATION DRAWER (ACCORDION / CLEAN TEXT) -->
            <div x-show="mobileNavOpen" 
                 x-transition:enter="transition ease-out duration-200"
                 x-transition:enter-start="opacity-0 -translate-y-2"
                 x-transition:enter-end="opacity-100 translate-y-0"
                 x-transition:leave="transition ease-in duration-150"
                 x-transition:leave-start="opacity-100 translate-y-0"
                 x-transition:leave-end="opacity-0 -translate-y-2"
                 class="lg:hidden border-t border-slate-100 bg-white px-4 py-3 space-y-2 text-xs shadow-md"
                 style="display: none;">
                <!-- Global Mobile Notifikasi Link -->
                <a href="{{ route('notifications.index') }}" 
                   class="flex items-center justify-between px-3 py-2.5 rounded-xl font-bold transition-colors {{ request()->routeIs('notifications.*') ? 'bg-blue-50 text-blue-700' : 'text-slate-700 bg-slate-50/80 hover:bg-slate-100' }}">
                    <span class="flex items-center gap-2">
                        <i class="fa-solid fa-bell text-blue-600"></i>
                        <span>Notifikasi & Pemberitahuan</span>
                    </span>
                    @php
                        $unreadNavbarCount = $user ? $user->unreadNotifications()->count() : 0;
                    @endphp
                    @if($unreadNavbarCount > 0)
                        <span class="px-2 py-0.5 rounded-full bg-rose-500 text-white text-[10px] font-black">
                            {{ $unreadNavbarCount > 99 ? '99+' : $unreadNavbarCount }} Baru
                        </span>
                    @endif
                </a>

                @if($peran === 'asesi')
                    <a href="{{ route('asesi.dashboard') }}" class="block px-3 py-2 rounded-xl font-medium {{ request()->routeIs('asesi.dashboard') ? 'bg-blue-50 text-blue-700 font-bold' : 'text-slate-700' }}">
                        Dashboard
                    </a>
                    <div class="border-t border-slate-100 pt-1.5 space-y-1">
                        <span class="text-[10px] font-bold text-slate-400 uppercase tracking-wider px-3">Pelaksanaan Asesmen</span>
                        <a href="{{ route('asesi.jadwal') }}" class="block px-3 py-1.5 rounded-lg {{ request()->routeIs('asesi.jadwal*') ? 'bg-blue-50 text-blue-700 font-semibold' : 'text-slate-600' }}">
                            Jadwal & Lokasi Uji
                        </a>
                        <a href="{{ route('asesi.tahapan') }}" class="block px-3 py-1.5 rounded-lg {{ (request()->routeIs('asesi.tahapan*') || request()->routeIs('asesi.ak01*') || request()->routeIs('asesi.apl02*') || request()->routeIs('asesi.formulir*') || request()->routeIs('asesi.biodata*') || request()->routeIs('asesi.pendaftaran*')) ? 'bg-blue-50 text-blue-700 font-semibold' : 'text-slate-600' }}">
                            Tahapan Formulir
                        </a>
                        <a href="{{ route('asesi.ak07') }}" class="block px-3 py-1.5 rounded-lg {{ request()->routeIs('asesi.ak07*') ? 'bg-blue-50 text-blue-700 font-semibold' : 'text-slate-600' }}">
                            Penyesuaian Asesmen (FR.AK.07)
                        </a>
                        <a href="{{ route('asesi.ujian') }}" class="block px-3 py-1.5 rounded-lg {{ request()->routeIs('asesi.ujian*') ? 'bg-blue-50 text-blue-700 font-semibold' : 'text-slate-600' }}">
                            Ruang Ujian Online (FR.IA)
                        </a>
                    </div>
                    <div class="border-t border-slate-100 pt-1.5 space-y-1">
                        <span class="text-[10px] font-bold text-slate-400 uppercase tracking-wider px-3">Hasil & Dokumen</span>
                        <a href="{{ route('asesi.hasil') }}" class="block px-3 py-1.5 rounded-lg {{ (request()->routeIs('asesi.hasil*') || request()->routeIs('asesi.hasil-nilai*')) ? 'bg-blue-50 text-blue-700 font-semibold' : 'text-slate-600' }}">
                            Hasil & Sertifikat
                        </a>
                        <a href="{{ route('asesi.dokumen') }}" class="block px-3 py-1.5 rounded-lg {{ request()->routeIs('asesi.dokumen*') ? 'bg-blue-50 text-blue-700 font-semibold' : 'text-slate-600' }}">
                            Berkas & Bukti Portofolio
                        </a>
                    </div>
                @elseif($peran === 'asesor')
                    <a href="{{ route('asesor.dashboard') }}" class="block px-3 py-2 rounded-xl font-medium {{ request()->routeIs('asesor.dashboard') ? 'bg-indigo-50 text-indigo-700 font-bold' : 'text-slate-700' }}">
                        Dashboard
                    </a>
                    <div class="border-t border-slate-100 pt-1.5 space-y-1">
                        <span class="text-[10px] font-bold text-slate-400 uppercase tracking-wider px-3">Pelaksanaan Asesmen</span>
                        <a href="{{ route('asesor.jadwal') }}" class="block px-3 py-1.5 rounded-lg {{ request()->routeIs('asesor.jadwal*') ? 'bg-indigo-50 text-indigo-700 font-semibold' : 'text-slate-600' }}">
                            Jadwal & Penugasan
                        </a>
                        <a href="{{ route('asesor.mapa') }}" class="block px-3 py-1.5 rounded-lg {{ (request()->routeIs('asesor.mapa*') || request()->routeIs('asesor.formulir*')) ? 'bg-indigo-50 text-indigo-700 font-semibold' : 'text-slate-600' }}">
                            Formulir
                        </a>
                        <a href="{{ route('asesor.penilaian') }}" class="block px-3 py-1.5 rounded-lg {{ (request()->routeIs('asesor.penilaian*') || request()->routeIs('asesor.daftar-peserta*') || request()->routeIs('asesor.input-penilaian*') || request()->routeIs('asesor.penilaian-live*')) ? 'bg-indigo-50 text-indigo-700 font-semibold' : 'text-slate-600' }}">
                            Penilaian Peserta (APL.02 & Ujian)
                        </a>
                        <a href="{{ route('asesor.koreksi-teori') }}" class="block px-3 py-1.5 rounded-lg {{ request()->routeIs('asesor.koreksi-teori*') ? 'bg-indigo-50 text-indigo-700 font-semibold' : 'text-slate-600' }}">
                            Koreksi Teori (IA.05 & 06)
                        </a>
                    </div>
                    <div class="border-t border-slate-100 pt-1.5 space-y-1">
                        <span class="text-[10px] font-bold text-slate-400 uppercase tracking-wider px-3">Hasil & Rekap</span>
                        <a href="{{ route('asesor.berita-acara') }}" class="block px-3 py-1.5 rounded-lg {{ request()->routeIs('asesor.berita-acara*') ? 'bg-indigo-50 text-indigo-700 font-semibold' : 'text-slate-600' }}">
                            Berita Acara & Rekap
                        </a>
                    </div>
                    <div class="border-t border-slate-100 pt-1.5 space-y-1">
                        <span class="text-[10px] font-bold text-slate-400 uppercase tracking-wider px-3">Dokumen</span>
                        <a href="{{ route('dokumen-asesmen.index') }}" class="block px-3 py-1.5 rounded-lg {{ request()->routeIs('dokumen-asesmen.*') ? 'bg-indigo-50 text-indigo-700 font-semibold' : 'text-slate-600' }}">
                            Dokumen Asesmen
                        </a>
                    </div>

                @elseif($peran === 'superadmin')
                    <a href="{{ route('superadmin.dashboard') }}" class="block px-3 py-2 rounded-xl font-medium {{ request()->routeIs('superadmin.dashboard') ? 'bg-rose-50 text-rose-700 font-bold' : 'text-slate-700' }}">
                        Control Panel
                    </a>
                    <div class="border-t border-slate-100 pt-1.5 space-y-1">
                        <span class="text-[10px] font-bold text-slate-400 uppercase tracking-wider px-3">Sistem & Keamanan</span>
                        <a href="{{ route('superadmin.manajemen-pengguna') }}" class="block px-3 py-1.5 rounded-lg {{ request()->routeIs('superadmin.manajemen-pengguna*') ? 'bg-rose-50 text-rose-700 font-semibold' : 'text-slate-600' }}">
                            Manajemen Akun
                        </a>
                        <a href="{{ route('superadmin.log-aktivitas') }}" class="block px-3 py-1.5 rounded-lg {{ request()->routeIs('superadmin.log-aktivitas*') ? 'bg-rose-50 text-rose-700 font-semibold' : 'text-slate-600' }}">
                            Audit Log Aktivitas
                        </a>
                        <a href="{{ route('superadmin.pengaturan-sistem') }}" class="block px-3 py-1.5 rounded-lg {{ request()->routeIs('superadmin.pengaturan-sistem*') ? 'bg-rose-50 text-rose-700 font-semibold' : 'text-slate-600' }}">
                            Pengaturan Global
                        </a>
                    </div>
                    <div class="border-t border-slate-100 pt-1.5 space-y-1">
                        <span class="text-[10px] font-bold text-slate-400 uppercase tracking-wider px-3">Dokumen Asesmen</span>
                        <a href="{{ route('dokumen-asesmen.index') }}" class="block px-3 py-1.5 rounded-lg font-bold text-rose-700 bg-rose-50">
                            Pusat Dokumen Asesmen (Hub)
                        </a>
                        <a href="{{ route('dokumen-asesmen.ak05.index') }}" class="block px-3 py-1.5 rounded-lg text-slate-600">
                            FR.AK.05 &bull; Laporan Asesmen
                        </a>
                        <a href="{{ route('dokumen-asesmen.ak06.index') }}" class="block px-3 py-1.5 rounded-lg text-slate-600">
                            FR.AK.06 &bull; Meninjau Proses Asesmen
                        </a>
                        <a href="{{ route('dokumen-asesmen.va.index') }}" class="block px-3 py-1.5 rounded-lg text-slate-600">
                            FR.VA &bull; Validasi Asesmen
                        </a>
                    </div>
                @else
                    <a href="{{ route('admin.dashboard') }}" class="block px-3 py-2 rounded-xl font-medium {{ request()->routeIs('admin.dashboard') ? 'bg-emerald-50 text-emerald-700 font-bold' : 'text-slate-700' }}">
                        Dashboard
                    </a>
                    <div class="border-t border-slate-100 pt-1.5 space-y-1">
                        <span class="text-[10px] font-bold text-slate-400 uppercase tracking-wider px-3">Asesi</span>
                        <a href="{{ route('admin.manajemen-asesi') }}" class="block px-3 py-1.5 rounded-lg {{ (request()->routeIs('admin.manajemen-asesi*') || request()->routeIs('admin.verifikasi*') || request()->routeIs('admin.detail-asesi')) ? 'bg-emerald-50 text-emerald-700 font-semibold' : 'text-slate-600' }}">
                            Data & Verifikasi
                        </a>
                    </div>
                    <div class="border-t border-slate-100 pt-1.5 space-y-1">
                        <span class="text-[10px] font-bold text-slate-400 uppercase tracking-wider px-3">Skema</span>
                        <a href="{{ route('admin.manajemen-skema') }}" class="block px-3 py-1.5 rounded-lg {{ request()->routeIs('admin.manajemen-skema*') ? 'bg-emerald-50 text-emerald-700 font-semibold' : 'text-slate-600' }}">
                            Master Skema
                        </a>
                        <a href="{{ route('admin.manajemen-jadwal') }}" class="block px-3 py-1.5 rounded-lg {{ request()->routeIs('admin.manajemen-jadwal*') ? 'bg-emerald-50 text-emerald-700 font-semibold' : 'text-slate-600' }}">
                            Jadwal Asesmen
                        </a>
                        <a href="{{ route('admin.master-muk.index') }}" class="block px-3 py-1.5 rounded-lg {{ request()->routeIs('admin.master-muk*') ? 'bg-emerald-50 text-emerald-700 font-semibold' : 'text-slate-600' }}">
                            Formulir
                        </a>
                    </div>
                    <div class="border-t border-slate-100 pt-1.5 space-y-1">
                        <span class="text-[10px] font-bold text-slate-400 uppercase tracking-wider px-3">Dokumen</span>
                        <a href="{{ route('dokumen-asesmen.index') }}" class="block px-3 py-1.5 rounded-lg font-bold text-emerald-700 bg-emerald-50">
                            Pusat Dokumen Asesmen (Hub)
                        </a>
                        <a href="{{ route('dokumen-asesmen.ak05.index') }}" class="block px-3 py-1.5 rounded-lg text-slate-600">
                            FR.AK.05 &bull; Laporan Asesmen
                        </a>
                        <a href="{{ route('dokumen-asesmen.ak06.index') }}" class="block px-3 py-1.5 rounded-lg text-slate-600">
                            FR.AK.06 &bull; Meninjau Proses Asesmen
                        </a>
                        <a href="{{ route('dokumen-asesmen.va.index') }}" class="block px-3 py-1.5 rounded-lg text-slate-600">
                            FR.VA &bull; Validasi Asesmen
                        </a>
                    </div>
                    <div class="border-t border-slate-100 pt-1.5 space-y-1">
                        <a href="{{ route('admin.dokumen.index') }}" class="block px-3 py-1.5 rounded-lg {{ (request()->routeIs('admin.dokumen*') || request()->routeIs('formulir.*')) ? 'bg-emerald-50 text-emerald-700 font-semibold' : 'text-slate-600' }}">
                            Berkas Umum
                        </a>
                        <a href="{{ route('admin.manajemen-pengumuman') }}" class="block px-3 py-1.5 rounded-lg {{ request()->routeIs('admin.manajemen-pengumuman*') ? 'bg-emerald-50 text-emerald-700 font-semibold' : 'text-slate-600' }}">
                            Informasi
                        </a>
                        <a href="{{ route('admin.laporan-kelulusan') }}" class="block px-3 py-1.5 rounded-lg {{ request()->routeIs('admin.laporan-kelulusan*') ? 'bg-emerald-50 text-emerald-700 font-semibold' : 'text-slate-600' }}">
                            Laporan
                        </a>
                        <a href="{{ route('admin.manajemen-asesor') }}" class="block px-3 py-1.5 rounded-lg {{ request()->routeIs('admin.manajemen-asesor*') ? 'bg-emerald-50 text-emerald-700 font-semibold' : 'text-slate-600' }}">
                            Asesor
                        </a>
                    </div>
                @endif
            </div>
        </nav>

        <!-- MAIN FULL-WIDTH CONTAINER -->
        <main class="w-full max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-5 flex-1 animasi-fade">
            <!-- Global Contextual Assessment Alert Banner -->
            <x-assessment-alert-banner />

            <!-- Page Content -->
            @yield('konten')
        </main>

        <!-- FOOTER FULL-WIDTH -->
        <footer class="no-print bg-white border-t border-slate-200/80 mt-10 py-5">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 flex flex-col sm:flex-row justify-between items-center gap-3 text-xs text-slate-500">
                <div>
                    &copy; {{ date('Y') }} <strong>{{ config('lsp.nama_lsp', 'LSP-P1 SMKN 1 Gunungputri') }}</strong> &bull; Lisensi BNSP: <strong class="text-slate-700">{{ config('lsp.nomor_lisensi', 'BNSP-LSP-2629-ID') }}</strong>
                </div>
                <div>
                    <a href="{{ config('lsp.url_cek_lisensi', 'https://bnsp.go.id') }}" target="_blank" rel="noopener noreferrer" class="inline-flex items-center gap-1.5 text-blue-600 hover:text-blue-800 font-semibold bg-blue-50/70 border border-blue-100 px-3 py-1.5 rounded-lg transition-colors">
                        <i class="fa-solid fa-shield-check"></i>
                        <span>Cek Lisensi Resmi di BNSP</span>
                        <i class="fa-solid fa-arrow-up-right-from-square text-[10px]"></i>
                    </a>
                </div>
            </div>
        </footer>
    </div>

@else
    <!-- =========================================================================
         LAYOUT MODE: SIDEBAR (UNTUK ADMIN & SUPERADMIN)
         ========================================================================= -->
    <div class="wadah-dasbor">
        <!-- SIDEBAR ROLE -->
        <aside class="sidebar-dasbor">
            <div>
                <a href="{{ route('beranda') }}" class="brand-lsp" style="margin-bottom: 1.5rem; padding-left: 0.5rem;">
                    <img src="{{ asset('images/logo-lsp.jpeg') }}" alt="Logo LSP" style="height: 36px; width: 36px; object-fit: contain; border-radius: 6px;">
                    <div>
                        <div style="font-size: 0.95rem; font-weight: 800; color: var(--biru-malam);">LSP SMKN 1 Gunungputri</div>
                        <div style="font-size: 0.72rem; color: var(--biru-muda); text-transform: uppercase; font-weight: 700;">
                            PORTAL {{ strtoupper($peran ?? 'ADMIN') }}
                        </div>
                    </div>
                </a>

                <ul class="menu-sidebar">
                    @if($peran === 'admin')
                        <!-- 1. Dashboard -->
                        <li class="item-menu-sidebar {{ request()->routeIs('admin.dashboard') ? 'aktif' : '' }}">
                            <a href="{{ route('admin.dashboard') }}">Dashboard</a>
                        </li>

                        <!-- 2. Asesi (Data & Verifikasi) -->
                        <li class="grup-sidebar-dropdown {{ (request()->routeIs('admin.manajemen-asesi*') || request()->routeIs('admin.verifikasi*') || request()->routeIs('admin.detail-asesi')) ? 'terbuka' : '' }}">
                            <div class="header-sidebar-dropdown" onclick="this.parentElement.classList.toggle('terbuka')">
                                <span>Asesi</span>
                            </div>
                            <div class="konten-sidebar-dropdown">
                                <div class="inner-sidebar-dropdown">
                                    <div class="item-menu-sidebar {{ (request()->routeIs('admin.manajemen-asesi*') || request()->routeIs('admin.verifikasi*') || request()->routeIs('admin.detail-asesi')) ? 'aktif' : '' }}">
                                        <a href="{{ route('admin.manajemen-asesi') }}">Data & Verifikasi</a>
                                    </div>
                                </div>
                            </div>
                        </li>

                        <!-- 3. Skema & Asesmen (Skema, Jadwal Asesmen, Bank Soal & MUK) -->
                        <li class="grup-sidebar-dropdown {{ (request()->routeIs('admin.manajemen-skema*') || request()->routeIs('admin.manajemen-jadwal*') || request()->routeIs('admin.master-muk*')) ? 'terbuka' : '' }}">
                            <div class="header-sidebar-dropdown" onclick="this.parentElement.classList.toggle('terbuka')">
                                <span>Skema</span>
                            </div>
                            <div class="konten-sidebar-dropdown">
                                <div class="inner-sidebar-dropdown">
                                    <div class="item-menu-sidebar {{ request()->routeIs('admin.manajemen-skema*') ? 'aktif' : '' }}">
                                        <a href="{{ route('admin.manajemen-skema') }}">Skema</a>
                                    </div>
                                    <div class="item-menu-sidebar {{ request()->routeIs('admin.manajemen-jadwal*') ? 'aktif' : '' }}">
                                        <a href="{{ route('admin.manajemen-jadwal') }}">Jadwal Asesmen</a>
                                    </div>
                                    <div class="item-menu-sidebar {{ request()->routeIs('admin.master-muk*') ? 'aktif' : '' }}">
                                        <a href="{{ route('admin.master-muk.index') }}">Formulir</a>
                                    </div>
                                </div>
                            </div>
                        </li>

                        <!-- 4. Dokumen -->
                        <li class="item-menu-sidebar {{ (request()->routeIs('admin.dokumen*') || request()->routeIs('formulir.*')) ? 'aktif' : '' }}">
                            <a href="{{ route('admin.dokumen.index') }}">Dokumen</a>
                        </li>

                        <!-- 5. Informasi -->
                        <li class="item-menu-sidebar {{ request()->routeIs('admin.manajemen-pengumuman*') ? 'aktif' : '' }}">
                            <a href="{{ route('admin.manajemen-pengumuman') }}">Informasi</a>
                        </li>

                        <!-- 6. Laporan -->
                        <li class="item-menu-sidebar {{ request()->routeIs('admin.laporan-kelulusan*') ? 'aktif' : '' }}">
                            <a href="{{ route('admin.laporan-kelulusan') }}">Laporan</a>
                        </li>

                        <!-- 7. Data Asesor -->
                        <li class="item-menu-sidebar {{ request()->routeIs('admin.manajemen-asesor*') ? 'aktif' : '' }}">
                            <a href="{{ route('admin.manajemen-asesor') }}">Asesor</a>
                        </li>
                    @elseif($peran === 'superadmin')
                        <li class="item-menu-sidebar {{ request()->routeIs('superadmin.dashboard') ? 'aktif' : '' }}">
                            <a href="{{ route('superadmin.dashboard') }}">Control Panel</a>
                        </li>

                        <li class="grup-sidebar-dropdown {{ (request()->routeIs('superadmin.manajemen-pengguna*') || request()->routeIs('superadmin.log-aktivitas*') || request()->routeIs('superadmin.pengaturan-sistem*') || request()->routeIs('formulir.*')) ? 'terbuka' : '' }}">
                            <div class="header-sidebar-dropdown" onclick="this.parentElement.classList.toggle('terbuka')">
                                <span>Sistem & Keamanan</span>
                            </div>
                            <div class="konten-sidebar-dropdown">
                                <div class="inner-sidebar-dropdown">
                                    <div class="item-menu-sidebar {{ request()->routeIs('superadmin.manajemen-pengguna*') ? 'aktif' : '' }}">
                                        <a href="{{ route('superadmin.manajemen-pengguna') }}">Manajemen Akun</a>
                                    </div>
                                    <div class="item-menu-sidebar {{ request()->routeIs('superadmin.log-aktivitas*') ? 'aktif' : '' }}">
                                        <a href="{{ route('superadmin.log-aktivitas') }}">Audit Log Aktivitas</a>
                                    </div>
                                    <div class="item-menu-sidebar {{ request()->routeIs('superadmin.pengaturan-sistem*') ? 'aktif' : '' }}">
                                        <a href="{{ route('superadmin.pengaturan-sistem') }}">Pengaturan Global</a>
                                    </div>
                                </div>
                            </div>
                        </li>
                    @endif
                </ul>
            </div>

            <!-- PROFIL PENGGUNA & SIDEBAR BOTTOM -->
            <div style="padding-top: 1rem; border-top: 1px solid var(--biru-soft);">
                <div style="display: flex; align-items: center; gap: 0.75rem; margin-bottom: 0.85rem;">
                    <div style="width: 38px; height: 38px; border-radius: 50%; background: var(--biru-soft); display: flex; align-items: center; justify-content: center; color: var(--biru-utama); font-weight: 700; flex-shrink: 0;">
                        {{ strtoupper(substr($user?->nama_lengkap ?? 'A', 0, 2)) }}
                    </div>
                    <div style="overflow: hidden; flex: 1;">
                        <div style="font-weight: 700; font-size: 0.88rem; color: var(--biru-malam); text-overflow: ellipsis; white-space: nowrap; overflow: hidden;" title="{{ $user?->nama_lengkap }}">
                            {{ $user?->nama_lengkap ?? 'Administrator' }}
                        </div>
                        <div style="font-size: 0.72rem; color: var(--abu-teks); text-transform: capitalize;">
                            {{ $user?->peran ?? 'Admin' }}
                        </div>
                    </div>
                </div>

                <form action="{{ route('keluar') }}" method="POST" style="margin-bottom: 0.85rem;">
                    @csrf
                    <button type="submit" class="tombol tombol-sekunder tombol-sm" style="width: 100%; font-size: 0.8rem; padding: 0.4rem 0.75rem;">
                        Keluar
                    </button>
                </form>
            </div>
        </aside>

        <!-- KONTEN UTAMA DASBOR (ADMIN / SUPERADMIN) -->
        <main class="konten-utama-dasbor animasi-fade">
            <!-- TOPBAR GLOBAL -->
            <header class="topbar-dasbor-global no-print flex items-center justify-between gap-4 mb-4 pb-3 border-b border-slate-200/80">
                <div class="flex items-center gap-3">
                    <button type="button" class="lg:hidden p-2 rounded-xl text-slate-600 hover:bg-slate-100 transition-colors cursor-pointer" onclick="toggleSidebar()" aria-label="Buka Menu Sidebar">
                        <i class="fa-solid fa-bars text-lg"></i>
                    </button>
                    <div class="flex items-center gap-2.5">
                        <span class="font-bold text-slate-800 text-xs sm:text-sm">Control Panel LSP SMKN 1 Gunungputri</span>
                        <span class="text-[10px] uppercase font-bold text-blue-600 bg-blue-50 px-1.5 py-0.2 rounded-md border border-blue-100">
                            {{ $peran ?? 'Admin' }}
                        </span>
                    </div>
                </div>

                <div class="flex items-center gap-2 sm:gap-3">
                    <x-notification-bell />

                    <div class="flex items-center gap-2 pl-2 border-l border-slate-200">
                        <div class="w-8 h-8 rounded-full bg-blue-50 text-blue-700 border border-blue-200/70 flex items-center justify-center font-bold text-xs shrink-0">
                            {{ strtoupper(substr($user?->nama_lengkap ?? 'A', 0, 2)) }}
                        </div>
                        <div class="hidden md:block text-left leading-tight max-w-[150px]">
                            <div class="font-semibold text-xs text-slate-800 truncate" title="{{ $user?->nama_lengkap }}">
                                {{ $user?->nama_lengkap ?? 'Administrator' }}
                            </div>
                            <div class="text-[10px] text-slate-500 capitalize">
                                {{ $user?->peran ?? 'Admin' }}
                            </div>
                        </div>
                    </div>
                </div>
            </header>

            <x-assessment-alert-banner />

            @yield('konten')

            <!-- FOOTER DASBOR RESMI (NO-PRINT) -->
            <footer class="footer-dasbor no-print" style="margin-top: 3.5rem; padding: 1.5rem 0 0.5rem 0; border-top: 1px solid var(--biru-soft); display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 1rem; font-size: 0.82rem; color: var(--abu-teks);">
                <div>
                    &copy; {{ date('Y') }} <strong>{{ config('lsp.nama_lsp', 'LSP-P1 SMKN 1 Gunungputri') }}</strong> &bull; Lisensi BNSP: <strong style="color: var(--biru-malam);">{{ config('lsp.nomor_lisensi', 'BNSP-LSP-2629-ID') }}</strong> (SK: {{ config('lsp.no_sk_lisensi', 'KEP.1215/BNSP/V/2025') }})
                </div>
                <div>
                    <a href="{{ config('lsp.url_cek_lisensi', 'https://bnsp.go.id') }}" target="_blank" rel="noopener noreferrer" style="color: var(--biru-utama); text-decoration: none; font-weight: 700; display: inline-flex; align-items: center; gap: 0.35rem; background: var(--biru-bg); padding: 0.35rem 0.75rem; border-radius: 6px; border: 1px solid var(--biru-soft);">
                        <i class="fa-solid fa-shield-check"></i> Cek Lisensi Resmi di BNSP <i class="fa-solid fa-arrow-up-right-from-square" style="font-size: 0.7rem;"></i>
                    </a>
                </div>
            </footer>
        </main>
    </div>
@endif

<!-- GLOBAL IMAGE LIGHTBOX POPUP MODAL FOR ALL ROLES -->
<div class="modal-overlay" id="modalPratinjauGambar" style="z-index: 999999;">
    <div class="modal-konten" style="max-width: 800px; text-align: center; padding: 1.5rem; background: #ffffff; border-radius: var(--radius-lg);">
        <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 1rem; border-bottom: 1px solid var(--biru-soft); padding-bottom: 0.75rem;">
            <h3 style="color: var(--biru-malam); margin: 0; font-size: 1.1rem;" id="judulPratinjauGambar">
                <i class="fa-solid fa-image" style="color: var(--biru-utama);"></i> Detail Pratinjau Berkas Gambar
            </h3>
            <button type="button" onclick="tutupModal('modalPratinjauGambar')" style="background: none; border: none; font-size: 1.5rem; cursor: pointer; color: var(--abu-teks);">&times;</button>
        </div>
        <div style="max-height: 70vh; overflow-y: auto; display: flex; justify-content: center; align-items: center; background: #f8fafc; padding: 1rem; border-radius: var(--radius-md); border: 1px solid var(--biru-soft);">
            <img id="imgPratinjauGambar" src="" alt="Pratinjau Gambar" style="max-width: 100%; max-height: 65vh; object-fit: contain; border-radius: var(--radius-sm); box-shadow: var(--bayangan-soft);">
        </div>
        <div style="margin-top: 1.25rem; display: flex; justify-content: space-between; align-items: center;">
            <a id="btnDownloadGambar" href="" download target="_blank" class="tombol tombol-sekunder tombol-sm">
                <i class="fa-solid fa-download"></i> Unduh / Buka File Original
            </a>
            <button type="button" class="tombol tombol-utama tombol-sm" onclick="tutupModal('modalPratinjauGambar')">
                <i class="fa-solid fa-xmark"></i> Tutup Pratinjau
            </button>
        </div>
    </div>
</div>

<!-- SweetAlert2 Script -->
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

<!-- JS Tata Letak Global -->
<script src="{{ asset('js/tata-letak.js') }}"></script>

<!-- Auto Popup Notifikasi Session Flash (SweetAlert2) -->
<script>
    document.addEventListener('DOMContentLoaded', function() {
        @if (session('sukses'))
            Swal.fire({
                icon: 'success',
                title: 'Berhasil!',
                text: {!! json_encode(session('sukses')) !!},
                confirmButtonColor: '#16a34a',
                confirmButtonText: '✓ Tutup',
                customClass: {
                    popup: 'swal2-modern-popup'
                }
            });
        @endif

        @if (session('error'))
            Swal.fire({
                icon: 'error',
                title: 'Perhatian!',
                text: {!! json_encode(session('error')) !!},
                confirmButtonColor: '#ef4444',
                confirmButtonText: '✓ Tutup',
                customClass: {
                    popup: 'swal2-modern-popup'
                }
            });
        @endif

        @if (session('info'))
            Swal.fire({
                icon: 'info',
                title: 'Informasi',
                text: {!! json_encode(session('info')) !!},
                confirmButtonColor: '#2563eb',
                confirmButtonText: '✓ Tutup',
                customClass: {
                    popup: 'swal2-modern-popup'
                }
            });
        @endif

        @if (session('warning'))
            Swal.fire({
                icon: 'warning',
                title: 'Peringatan',
                text: {!! json_encode(session('warning')) !!},
                confirmButtonColor: '#f59e0b',
                confirmButtonText: '✓ Tutup',
                customClass: {
                    popup: 'swal2-modern-popup'
                }
            });
        @endif
    });
</script>

@include('komponen.modal-notifikasi-ujian-asesi')

<!-- JS Khusus Per Halaman -->
@stack('js')
</body>
</html>
