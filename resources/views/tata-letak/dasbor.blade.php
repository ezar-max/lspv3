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
    $isNavbarPortal = ($peran !== 'superadmin');

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
        
        <!-- TOP STICKY NAVBAR (Solid di mobile, transparan glassmorphism di desktop) -->
        <nav class="sticky top-0 z-40 bg-white border-b border-slate-200/80 lg:border-none lg:bg-white/50 lg:backdrop-blur-md transition-all duration-300">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                <div class="flex items-center justify-between h-16 gap-4">
                    
                    <!-- LEFT: BRAND LOGO & ROLE -->
                    <div class="flex items-center gap-3 shrink-0">
                        <a href="{{ $brandRoute }}" class="flex items-center gap-2.5 group">
                            <img src="{{ asset('images/logo-lsp.jpeg') }}" alt="Logo LSP" class="h-8 w-8 object-contain rounded-lg">
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
                                $isAdminDokumenActive = request()->routeIs('dokumen-asesmen.*') || request()->routeIs('admin.dokumen*') || request()->routeIs('formulir.*');
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
                                       class="block px-3 py-1.5 rounded-xl transition-colors {{ request()->routeIs('admin.dokumen*') ? 'bg-emerald-50 text-emerald-700 font-semibold' : 'hover:bg-slate-50 text-slate-700 hover:text-slate-900' }}">
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
        </nav>

        <!-- Mobile Navigation Backdrop Overlay (Dark & Blur - Full Screen Viewport) -->
        <div x-show="mobileNavOpen" 
             x-transition:enter="transition-opacity ease-out duration-300"
             x-transition:enter-start="opacity-0"
             x-transition:enter-end="opacity-100"
             x-transition:leave="transition-opacity ease-in duration-250"
             x-transition:leave-start="opacity-100"
             x-transition:leave-end="opacity-0"
             @click="mobileNavOpen = false"
             @keydown.escape.window="mobileNavOpen = false"
             x-effect="document.body.style.overflow = mobileNavOpen ? 'hidden' : ''"
             class="fixed inset-0 w-full h-full h-[100dvh] bg-slate-950/60 backdrop-blur-xs z-[999] lg:hidden"
             style="display: none;"
             aria-hidden="true"></div>

        <!-- MOBILE NAVIGATION SLIDE-OVER DRAWER (RESPONSIVE VIEWPORT & NATIVE FEEL) -->
        <div x-show="mobileNavOpen" 
             x-transition:enter="transition ease-[cubic-bezier(0.16,1,0.3,1)] duration-300 transform-gpu"
             x-transition:enter-start="translate-x-full"
             x-transition:enter-end="translate-x-0"
             x-transition:leave="transition ease-[cubic-bezier(0.16,1,0.3,1)] duration-250 transform-gpu"
             x-transition:leave-start="translate-x-0"
             x-transition:leave-end="translate-x-full"
             class="fixed inset-y-0 right-0 w-[84vw] min-w-[270px] max-w-[325px] sm:max-w-[350px] h-[100dvh] max-h-[100dvh] bg-white z-[1000] shadow-[-10px_0_30px_rgba(0,0,0,0.12)] flex flex-col border-l border-slate-200/80 rounded-l-2xl sm:rounded-l-3xl overflow-hidden lg:hidden"
             style="display: none; background-color: #ffffff !important; opacity: 1 !important;">
            
            <!-- 1. Drawer Header -->
            <div class="p-4 border-b border-slate-100 flex items-center justify-between bg-slate-50/90 backdrop-blur-xs shrink-0 pt-[calc(1rem+env(safe-area-inset-top,0px))]">
                <div class="flex items-center gap-2.5">
                    <img src="{{ asset('images/logo-lsp.jpeg') }}" alt="Logo LSP" class="h-8 w-8 object-contain rounded-lg border border-slate-200/80 p-0.5 bg-white shadow-xs">
                    <div class="leading-tight">
                        <div class="font-bold text-slate-900 text-xs">LSP SMKN 1 Gunungputri</div>
                        <div class="text-[9px] font-extrabold uppercase tracking-wider {{ $roleBadgeClass }}">
                            Portal {{ ucfirst($peran ?? 'Pengguna') }}
                        </div>
                    </div>
                </div>
                <button type="button" 
                        @click="mobileNavOpen = false"
                        class="w-8 h-8 rounded-lg bg-slate-100 text-slate-500 hover:bg-slate-200 hover:text-slate-800 flex items-center justify-center transition-colors cursor-pointer"
                        aria-label="Tutup Menu">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.2" d="M6 18L18 6M6 6l12 12" />
                    </svg>
                </button>
            </div>

            <!-- 2. User Profile Card -->
            <div class="p-3.5 bg-slate-50/60 border-b border-slate-100 flex items-center justify-between gap-3 shrink-0">
                <div class="flex items-center gap-2.5 min-w-0">
                    <div class="w-9 h-9 rounded-full bg-gradient-to-tr from-blue-600 to-indigo-600 text-white font-bold flex items-center justify-center text-xs shrink-0 shadow-xs">
                        {{ strtoupper(substr($user?->nama_lengkap ?? 'U', 0, 2)) }}
                    </div>
                    <div class="min-w-0 leading-tight">
                        <div class="font-bold text-slate-800 text-xs truncate" title="{{ $user?->nama_lengkap }}">
                            {{ $user?->nama_lengkap ?? 'Pengguna' }}
                        </div>
                        <div class="text-[11px] text-slate-500 truncate">{{ $user?->email ?? '-' }}</div>
                    </div>
                </div>
                @if($peran === 'asesi')
                    <a href="{{ route('asesi.profil') }}" 
                       @click="mobileNavOpen = false"
                       class="px-2.5 py-1 text-[11px] font-bold text-blue-700 bg-blue-50 border border-blue-200/80 rounded-lg hover:bg-blue-100 transition-colors shrink-0">
                        Profil
                    </a>
                @endif
            </div>

            <!-- 3. Scrollable Navigation Menu -->
            <div class="flex-1 overflow-y-auto px-3 py-3.5 space-y-4 text-xs bg-white overscroll-contain">

                    @if($peran === 'asesi')
                        <!-- Dashboard Asesi -->
                        <div>
                            <a href="{{ route('asesi.dashboard') }}" 
                               @click="mobileNavOpen = false"
                               class="group flex items-center justify-between px-3 py-2 rounded-xl transition-all {{ request()->routeIs('asesi.dashboard') ? 'bg-blue-50 text-blue-700 font-bold border border-blue-100' : 'text-slate-700 hover:bg-slate-50 hover:text-slate-900 font-medium' }}">
                                <div class="flex items-center gap-2.5">
                                    <div class="w-7 h-7 rounded-lg flex items-center justify-center {{ request()->routeIs('asesi.dashboard') ? 'bg-blue-600 text-white shadow-2xs' : 'bg-slate-100 text-slate-500 group-hover:text-slate-700' }}">
                                        <i class="fa-solid fa-chart-pie text-[11px]"></i>
                                    </div>
                                    <span>Dashboard Asesi</span>
                                </div>
                                <i class="fa-solid fa-chevron-right text-[10px] {{ request()->routeIs('asesi.dashboard') ? 'text-blue-500' : 'text-slate-300 group-hover:text-slate-400 group-hover:translate-x-0.5' }} transition-all"></i>
                            </a>
                        </div>

                        <!-- Pelaksanaan Asesmen -->
                        <div>
                            <div class="text-[10px] font-bold text-slate-400 uppercase tracking-wider px-2 mb-1.5">Pelaksanaan Asesmen</div>
                            <div class="space-y-0.5">
                                <a href="{{ route('asesi.jadwal') }}" 
                                   @click="mobileNavOpen = false"
                                   class="group flex items-center justify-between px-3 py-2 rounded-xl transition-all {{ request()->routeIs('asesi.jadwal*') ? 'bg-blue-50 text-blue-700 font-bold border border-blue-100' : 'text-slate-700 hover:bg-slate-50 hover:text-slate-900 font-medium' }}">
                                    <div class="flex items-center gap-2.5">
                                        <div class="w-7 h-7 rounded-lg flex items-center justify-center {{ request()->routeIs('asesi.jadwal*') ? 'bg-blue-600 text-white shadow-2xs' : 'bg-slate-100 text-slate-500 group-hover:text-slate-700' }}">
                                            <i class="fa-solid fa-calendar-days text-[11px]"></i>
                                        </div>
                                        <span>Jadwal & Lokasi Uji</span>
                                    </div>
                                    <i class="fa-solid fa-chevron-right text-[10px] {{ request()->routeIs('asesi.jadwal*') ? 'text-blue-500' : 'text-slate-300 group-hover:text-slate-400 group-hover:translate-x-0.5' }} transition-all"></i>
                                </a>

                                @php
                                    $isTahapanActive = (request()->routeIs('asesi.tahapan*') || request()->routeIs('asesi.ak01*') || request()->routeIs('asesi.apl02*') || request()->routeIs('asesi.formulir*') || request()->routeIs('asesi.biodata*') || request()->routeIs('asesi.pendaftaran*'));
                                @endphp
                                <a href="{{ route('asesi.tahapan') }}" 
                                   @click="mobileNavOpen = false"
                                   class="group flex items-center justify-between px-3 py-2 rounded-xl transition-all {{ $isTahapanActive ? 'bg-blue-50 text-blue-700 font-bold border border-blue-100' : 'text-slate-700 hover:bg-slate-50 hover:text-slate-900 font-medium' }}">
                                    <div class="flex items-center gap-2.5">
                                        <div class="w-7 h-7 rounded-lg flex items-center justify-center {{ $isTahapanActive ? 'bg-blue-600 text-white shadow-2xs' : 'bg-slate-100 text-slate-500 group-hover:text-slate-700' }}">
                                            <i class="fa-solid fa-clipboard-list text-[11px]"></i>
                                        </div>
                                        <span>Tahapan Formulir</span>
                                    </div>
                                    <i class="fa-solid fa-chevron-right text-[10px] {{ $isTahapanActive ? 'text-blue-500' : 'text-slate-300 group-hover:text-slate-400 group-hover:translate-x-0.5' }} transition-all"></i>
                                </a>
                            </div>
                        </div>

                        <!-- Hasil & Dokumen -->
                        <div>
                            <div class="text-[10px] font-bold text-slate-400 uppercase tracking-wider px-2 mb-1.5">Hasil & Dokumen</div>
                            <div class="space-y-0.5">
                                @php
                                    $isHasilActive = (request()->routeIs('asesi.hasil*') || request()->routeIs('asesi.hasil-nilai*'));
                                @endphp
                                <a href="{{ route('asesi.hasil') }}" 
                                   @click="mobileNavOpen = false"
                                   class="group flex items-center justify-between px-3 py-2 rounded-xl transition-all {{ $isHasilActive ? 'bg-blue-50 text-blue-700 font-bold border border-blue-100' : 'text-slate-700 hover:bg-slate-50 hover:text-slate-900 font-medium' }}">
                                    <div class="flex items-center gap-2.5">
                                        <div class="w-7 h-7 rounded-lg flex items-center justify-center {{ $isHasilActive ? 'bg-blue-600 text-white shadow-2xs' : 'bg-slate-100 text-slate-500 group-hover:text-slate-700' }}">
                                            <i class="fa-solid fa-award text-[11px]"></i>
                                        </div>
                                        <span>Hasil & Sertifikat</span>
                                    </div>
                                    <i class="fa-solid fa-chevron-right text-[10px] {{ $isHasilActive ? 'text-blue-500' : 'text-slate-300 group-hover:text-slate-400 group-hover:translate-x-0.5' }} transition-all"></i>
                                </a>

                                <a href="{{ route('asesi.dokumen') }}" 
                                   @click="mobileNavOpen = false"
                                   class="group flex items-center justify-between px-3 py-2 rounded-xl transition-all {{ request()->routeIs('asesi.dokumen*') ? 'bg-blue-50 text-blue-700 font-bold border border-blue-100' : 'text-slate-700 hover:bg-slate-50 hover:text-slate-900 font-medium' }}">
                                    <div class="flex items-center gap-2.5">
                                        <div class="w-7 h-7 rounded-lg flex items-center justify-center {{ request()->routeIs('asesi.dokumen*') ? 'bg-blue-600 text-white shadow-2xs' : 'bg-slate-100 text-slate-500 group-hover:text-slate-700' }}">
                                            <i class="fa-solid fa-folder-open text-[11px]"></i>
                                        </div>
                                        <span>Berkas & Portofolio</span>
                                    </div>
                                    <i class="fa-solid fa-chevron-right text-[10px] {{ request()->routeIs('asesi.dokumen*') ? 'text-blue-500' : 'text-slate-300 group-hover:text-slate-400 group-hover:translate-x-0.5' }} transition-all"></i>
                                </a>
                            </div>
                        </div>

                    @elseif($peran === 'asesor')
                        <!-- Dashboard Asesor -->
                        <div>
                            <a href="{{ route('asesor.dashboard') }}" 
                               @click="mobileNavOpen = false"
                               class="group flex items-center justify-between px-3 py-2 rounded-xl transition-all {{ request()->routeIs('asesor.dashboard') ? 'bg-indigo-50 text-indigo-700 font-bold border border-indigo-100' : 'text-slate-700 hover:bg-slate-50 hover:text-slate-900 font-medium' }}">
                                <div class="flex items-center gap-2.5">
                                    <div class="w-7 h-7 rounded-lg flex items-center justify-center {{ request()->routeIs('asesor.dashboard') ? 'bg-indigo-600 text-white shadow-2xs' : 'bg-slate-100 text-slate-500 group-hover:text-slate-700' }}">
                                        <i class="fa-solid fa-chart-pie text-[11px]"></i>
                                    </div>
                                    <span>Dashboard Asesor</span>
                                </div>
                                <i class="fa-solid fa-chevron-right text-[10px] {{ request()->routeIs('asesor.dashboard') ? 'text-indigo-500' : 'text-slate-300 group-hover:text-slate-400 group-hover:translate-x-0.5' }} transition-all"></i>
                            </a>
                        </div>

                        <!-- Pelaksanaan Asesmen -->
                        <div>
                            <div class="text-[10px] font-bold text-slate-400 uppercase tracking-wider px-2 mb-1.5">Pelaksanaan Asesmen</div>
                            <div class="space-y-0.5">
                                <a href="{{ route('asesor.jadwal') }}" 
                                   @click="mobileNavOpen = false"
                                   class="group flex items-center justify-between px-3 py-2 rounded-xl transition-all {{ request()->routeIs('asesor.jadwal*') ? 'bg-indigo-50 text-indigo-700 font-bold border border-indigo-100' : 'text-slate-700 hover:bg-slate-50 hover:text-slate-900 font-medium' }}">
                                    <div class="flex items-center gap-2.5">
                                        <div class="w-7 h-7 rounded-lg flex items-center justify-center {{ request()->routeIs('asesor.jadwal*') ? 'bg-indigo-600 text-white shadow-2xs' : 'bg-slate-100 text-slate-500 group-hover:text-slate-700' }}">
                                            <i class="fa-solid fa-calendar-check text-[11px]"></i>
                                        </div>
                                        <span>Jadwal & Penugasan</span>
                                    </div>
                                    <i class="fa-solid fa-chevron-right text-[10px] {{ request()->routeIs('asesor.jadwal*') ? 'text-indigo-500' : 'text-slate-300 group-hover:text-slate-400 group-hover:translate-x-0.5' }} transition-all"></i>
                                </a>

                                <a href="{{ route('asesor.mapa') }}" 
                                   @click="mobileNavOpen = false"
                                   class="group flex items-center justify-between px-3 py-2 rounded-xl transition-all {{ (request()->routeIs('asesor.mapa*') || request()->routeIs('asesor.formulir*')) ? 'bg-indigo-50 text-indigo-700 font-bold border border-indigo-100' : 'text-slate-700 hover:bg-slate-50 hover:text-slate-900 font-medium' }}">
                                    <div class="flex items-center gap-2.5">
                                        <div class="w-7 h-7 rounded-lg flex items-center justify-center {{ (request()->routeIs('asesor.mapa*') || request()->routeIs('asesor.formulir*')) ? 'bg-indigo-600 text-white shadow-2xs' : 'bg-slate-100 text-slate-500 group-hover:text-slate-700' }}">
                                            <i class="fa-solid fa-file-signature text-[11px]"></i>
                                        </div>
                                        <span>Formulir Asesmen</span>
                                    </div>
                                    <i class="fa-solid fa-chevron-right text-[10px] {{ (request()->routeIs('asesor.mapa*') || request()->routeIs('asesor.formulir*')) ? 'text-indigo-500' : 'text-slate-300 group-hover:text-slate-400 group-hover:translate-x-0.5' }} transition-all"></i>
                                </a>

                                @php
                                    $isPenilaianActive = (request()->routeIs('asesor.penilaian*') || request()->routeIs('asesor.daftar-peserta*') || request()->routeIs('asesor.input-penilaian*') || request()->routeIs('asesor.penilaian-live*'));
                                @endphp
                                <a href="{{ route('asesor.penilaian') }}" 
                                   @click="mobileNavOpen = false"
                                   class="group flex items-center justify-between px-3 py-2 rounded-xl transition-all {{ $isPenilaianActive ? 'bg-indigo-50 text-indigo-700 font-bold border border-indigo-100' : 'text-slate-700 hover:bg-slate-50 hover:text-slate-900 font-medium' }}">
                                    <div class="flex items-center gap-2.5">
                                        <div class="w-7 h-7 rounded-lg flex items-center justify-center {{ $isPenilaianActive ? 'bg-indigo-600 text-white shadow-2xs' : 'bg-slate-100 text-slate-500 group-hover:text-slate-700' }}">
                                            <i class="fa-solid fa-clipboard-user text-[11px]"></i>
                                        </div>
                                        <span>Penilaian Peserta</span>
                                    </div>
                                    <i class="fa-solid fa-chevron-right text-[10px] {{ $isPenilaianActive ? 'text-indigo-500' : 'text-slate-300 group-hover:text-slate-400 group-hover:translate-x-0.5' }} transition-all"></i>
                                </a>

                                <a href="{{ route('asesor.koreksi-teori') }}" 
                                   @click="mobileNavOpen = false"
                                   class="group flex items-center justify-between px-3 py-2 rounded-xl transition-all {{ request()->routeIs('asesor.koreksi-teori*') ? 'bg-indigo-50 text-indigo-700 font-bold border border-indigo-100' : 'text-slate-700 hover:bg-slate-50 hover:text-slate-900 font-medium' }}">
                                    <div class="flex items-center gap-2.5">
                                        <div class="w-7 h-7 rounded-lg flex items-center justify-center {{ request()->routeIs('asesor.koreksi-teori*') ? 'bg-indigo-600 text-white shadow-2xs' : 'bg-slate-100 text-slate-500 group-hover:text-slate-700' }}">
                                            <i class="fa-solid fa-file-circle-check text-[11px]"></i>
                                        </div>
                                        <span>Koreksi Teori (IA.05 & 06)</span>
                                    </div>
                                    <i class="fa-solid fa-chevron-right text-[10px] {{ request()->routeIs('asesor.koreksi-teori*') ? 'text-indigo-500' : 'text-slate-300 group-hover:text-slate-400 group-hover:translate-x-0.5' }} transition-all"></i>
                                </a>
                            </div>
                        </div>

                        <!-- Hasil & Dokumen Asesor -->
                        <div>
                            <div class="text-[10px] font-bold text-slate-400 uppercase tracking-wider px-2 mb-1.5">Hasil & Dokumen</div>
                            <div class="space-y-0.5">
                                <a href="{{ route('asesor.berita-acara') }}" 
                                   @click="mobileNavOpen = false"
                                   class="group flex items-center justify-between px-3 py-2 rounded-xl transition-all {{ request()->routeIs('asesor.berita-acara*') ? 'bg-indigo-50 text-indigo-700 font-bold border border-indigo-100' : 'text-slate-700 hover:bg-slate-50 hover:text-slate-900 font-medium' }}">
                                    <div class="flex items-center gap-2.5">
                                        <div class="w-7 h-7 rounded-lg flex items-center justify-center {{ request()->routeIs('asesor.berita-acara*') ? 'bg-indigo-600 text-white shadow-2xs' : 'bg-slate-100 text-slate-500 group-hover:text-slate-700' }}">
                                            <i class="fa-solid fa-scroll text-[11px]"></i>
                                        </div>
                                        <span>Berita Acara & Rekap</span>
                                    </div>
                                    <i class="fa-solid fa-chevron-right text-[10px] {{ request()->routeIs('asesor.berita-acara*') ? 'text-indigo-500' : 'text-slate-300 group-hover:text-slate-400 group-hover:translate-x-0.5' }} transition-all"></i>
                                </a>

                                <a href="{{ route('dokumen-asesmen.index') }}" 
                                   @click="mobileNavOpen = false"
                                   class="group flex items-center justify-between px-3 py-2 rounded-xl transition-all {{ request()->routeIs('dokumen-asesmen.*') ? 'bg-indigo-50 text-indigo-700 font-bold border border-indigo-100' : 'text-slate-700 hover:bg-slate-50 hover:text-slate-900 font-medium' }}">
                                    <div class="flex items-center gap-2.5">
                                        <div class="w-7 h-7 rounded-lg flex items-center justify-center {{ request()->routeIs('dokumen-asesmen.*') ? 'bg-indigo-600 text-white shadow-2xs' : 'bg-slate-100 text-slate-500 group-hover:text-slate-700' }}">
                                            <i class="fa-solid fa-folder-tree text-[11px]"></i>
                                        </div>
                                        <span>Dokumen Asesmen</span>
                                    </div>
                                    <i class="fa-solid fa-chevron-right text-[10px] {{ request()->routeIs('dokumen-asesmen.*') ? 'text-indigo-500' : 'text-slate-300 group-hover:text-slate-400 group-hover:translate-x-0.5' }} transition-all"></i>
                                </a>
                            </div>
                        </div>

                    @elseif($peran === 'superadmin')
                        <!-- Superadmin Dashboard -->
                        <div>
                            <a href="{{ route('superadmin.dashboard') }}" 
                               @click="mobileNavOpen = false"
                               class="group flex items-center justify-between px-3 py-2 rounded-xl transition-all {{ request()->routeIs('superadmin.dashboard') ? 'bg-rose-50 text-rose-700 font-bold border border-rose-100' : 'text-slate-700 hover:bg-slate-50 hover:text-slate-900 font-medium' }}">
                                <div class="flex items-center gap-2.5">
                                    <div class="w-7 h-7 rounded-lg flex items-center justify-center {{ request()->routeIs('superadmin.dashboard') ? 'bg-rose-600 text-white shadow-2xs' : 'bg-slate-100 text-slate-500 group-hover:text-slate-700' }}">
                                        <i class="fa-solid fa-shield-halved text-[11px]"></i>
                                    </div>
                                    <span>Control Panel</span>
                                </div>
                                <i class="fa-solid fa-chevron-right text-[10px] {{ request()->routeIs('superadmin.dashboard') ? 'text-rose-500' : 'text-slate-300 group-hover:text-slate-400 group-hover:translate-x-0.5' }} transition-all"></i>
                            </a>
                        </div>

                        <!-- Sistem & Keamanan -->
                        <div>
                            <div class="text-[10px] font-bold text-slate-400 uppercase tracking-wider px-2 mb-1.5">Sistem & Keamanan</div>
                            <div class="space-y-0.5">
                                <a href="{{ route('superadmin.manajemen-pengguna') }}" 
                                   @click="mobileNavOpen = false"
                                   class="group flex items-center justify-between px-3 py-2 rounded-xl transition-all {{ request()->routeIs('superadmin.manajemen-pengguna*') ? 'bg-rose-50 text-rose-700 font-bold border border-rose-100' : 'text-slate-700 hover:bg-slate-50 hover:text-slate-900 font-medium' }}">
                                    <div class="flex items-center gap-2.5">
                                        <div class="w-7 h-7 rounded-lg flex items-center justify-center {{ request()->routeIs('superadmin.manajemen-pengguna*') ? 'bg-rose-600 text-white shadow-2xs' : 'bg-slate-100 text-slate-500 group-hover:text-slate-700' }}">
                                            <i class="fa-solid fa-users-gear text-[11px]"></i>
                                        </div>
                                        <span>Manajemen Akun</span>
                                    </div>
                                    <i class="fa-solid fa-chevron-right text-[10px] {{ request()->routeIs('superadmin.manajemen-pengguna*') ? 'text-rose-500' : 'text-slate-300 group-hover:text-slate-400 group-hover:translate-x-0.5' }} transition-all"></i>
                                </a>

                                <a href="{{ route('superadmin.log-aktivitas') }}" 
                                   @click="mobileNavOpen = false"
                                   class="group flex items-center justify-between px-3 py-2 rounded-xl transition-all {{ request()->routeIs('superadmin.log-aktivitas*') ? 'bg-rose-50 text-rose-700 font-bold border border-rose-100' : 'text-slate-700 hover:bg-slate-50 hover:text-slate-900 font-medium' }}">
                                    <div class="flex items-center gap-2.5">
                                        <div class="w-7 h-7 rounded-lg flex items-center justify-center {{ request()->routeIs('superadmin.log-aktivitas*') ? 'bg-rose-600 text-white shadow-2xs' : 'bg-slate-100 text-slate-500 group-hover:text-slate-700' }}">
                                            <i class="fa-solid fa-clock-rotate-left text-[11px]"></i>
                                        </div>
                                        <span>Audit Log Aktivitas</span>
                                    </div>
                                    <i class="fa-solid fa-chevron-right text-[10px] {{ request()->routeIs('superadmin.log-aktivitas*') ? 'text-rose-500' : 'text-slate-300 group-hover:text-slate-400 group-hover:translate-x-0.5' }} transition-all"></i>
                                </a>

                                <a href="{{ route('superadmin.pengaturan-sistem') }}" 
                                   @click="mobileNavOpen = false"
                                   class="group flex items-center justify-between px-3 py-2 rounded-xl transition-all {{ request()->routeIs('superadmin.pengaturan-sistem*') ? 'bg-rose-50 text-rose-700 font-bold border border-rose-100' : 'text-slate-700 hover:bg-slate-50 hover:text-slate-900 font-medium' }}">
                                    <div class="flex items-center gap-2.5">
                                        <div class="w-7 h-7 rounded-lg flex items-center justify-center {{ request()->routeIs('superadmin.pengaturan-sistem*') ? 'bg-rose-600 text-white shadow-2xs' : 'bg-slate-100 text-slate-500 group-hover:text-slate-700' }}">
                                            <i class="fa-solid fa-sliders text-[11px]"></i>
                                        </div>
                                        <span>Pengaturan Global</span>
                                    </div>
                                    <i class="fa-solid fa-chevron-right text-[10px] {{ request()->routeIs('superadmin.pengaturan-sistem*') ? 'text-rose-500' : 'text-slate-300 group-hover:text-slate-400 group-hover:translate-x-0.5' }} transition-all"></i>
                                </a>
                            </div>
                        </div>

                        <!-- Dokumen Asesmen Superadmin -->
                        <div>
                            <div class="text-[10px] font-bold text-slate-400 uppercase tracking-wider px-2 mb-1.5">Dokumen Asesmen</div>
                            <div class="space-y-0.5">
                                <a href="{{ route('dokumen-asesmen.index') }}" 
                                   @click="mobileNavOpen = false"
                                   class="group flex items-center justify-between px-3 py-2 rounded-xl transition-all {{ request()->routeIs('dokumen-asesmen.index') ? 'bg-rose-50 text-rose-700 font-bold border border-rose-100' : 'text-slate-700 hover:bg-slate-50 hover:text-slate-900 font-medium' }}">
                                    <div class="flex items-center gap-2.5">
                                        <div class="w-7 h-7 rounded-lg flex items-center justify-center {{ request()->routeIs('dokumen-asesmen.index') ? 'bg-rose-600 text-white shadow-2xs' : 'bg-slate-100 text-slate-500 group-hover:text-slate-700' }}">
                                            <i class="fa-solid fa-folder-tree text-[11px]"></i>
                                        </div>
                                        <span>Pusat Dokumen Hub</span>
                                    </div>
                                    <i class="fa-solid fa-chevron-right text-[10px] {{ request()->routeIs('dokumen-asesmen.index') ? 'text-rose-500' : 'text-slate-300 group-hover:text-slate-400 group-hover:translate-x-0.5' }} transition-all"></i>
                                </a>

                                <a href="{{ route('dokumen-asesmen.ak05.index') }}" 
                                   @click="mobileNavOpen = false"
                                   class="group flex items-center justify-between px-3 py-2 rounded-xl transition-all {{ request()->routeIs('dokumen-asesmen.ak05.*') ? 'bg-rose-50 text-rose-700 font-bold border border-rose-100' : 'text-slate-700 hover:bg-slate-50 hover:text-slate-900 font-medium' }}">
                                    <div class="flex items-center gap-2.5">
                                        <div class="w-7 h-7 rounded-lg flex items-center justify-center {{ request()->routeIs('dokumen-asesmen.ak05.*') ? 'bg-rose-600 text-white shadow-2xs' : 'bg-slate-100 text-slate-500 group-hover:text-slate-700' }}">
                                            <i class="fa-solid fa-file-lines text-[11px]"></i>
                                        </div>
                                        <span>FR.AK.05 &bull; Laporan Asesmen</span>
                                    </div>
                                    <i class="fa-solid fa-chevron-right text-[10px] {{ request()->routeIs('dokumen-asesmen.ak05.*') ? 'text-rose-500' : 'text-slate-300 group-hover:text-slate-400 group-hover:translate-x-0.5' }} transition-all"></i>
                                </a>

                                <a href="{{ route('dokumen-asesmen.ak06.index') }}" 
                                   @click="mobileNavOpen = false"
                                   class="group flex items-center justify-between px-3 py-2 rounded-xl transition-all {{ request()->routeIs('dokumen-asesmen.ak06.*') ? 'bg-rose-50 text-rose-700 font-bold border border-rose-100' : 'text-slate-700 hover:bg-slate-50 hover:text-slate-900 font-medium' }}">
                                    <div class="flex items-center gap-2.5">
                                        <div class="w-7 h-7 rounded-lg flex items-center justify-center {{ request()->routeIs('dokumen-asesmen.ak06.*') ? 'bg-rose-600 text-white shadow-2xs' : 'bg-slate-100 text-slate-500 group-hover:text-slate-700' }}">
                                            <i class="fa-solid fa-file-waveform text-[11px]"></i>
                                        </div>
                                        <span>FR.AK.06 &bull; Meninjau Asesmen</span>
                                    </div>
                                    <i class="fa-solid fa-chevron-right text-[10px] {{ request()->routeIs('dokumen-asesmen.ak06.*') ? 'text-rose-500' : 'text-slate-300 group-hover:text-slate-400 group-hover:translate-x-0.5' }} transition-all"></i>
                                </a>

                                <a href="{{ route('dokumen-asesmen.va.index') }}" 
                                   @click="mobileNavOpen = false"
                                   class="group flex items-center justify-between px-3 py-2 rounded-xl transition-all {{ request()->routeIs('dokumen-asesmen.va.*') ? 'bg-rose-50 text-rose-700 font-bold border border-rose-100' : 'text-slate-700 hover:bg-slate-50 hover:text-slate-900 font-medium' }}">
                                    <div class="flex items-center gap-2.5">
                                        <div class="w-7 h-7 rounded-lg flex items-center justify-center {{ request()->routeIs('dokumen-asesmen.va.*') ? 'bg-rose-600 text-white shadow-2xs' : 'bg-slate-100 text-slate-500 group-hover:text-slate-700' }}">
                                            <i class="fa-solid fa-stamp text-[11px]"></i>
                                        </div>
                                        <span>FR.VA &bull; Validasi Asesmen</span>
                                    </div>
                                    <i class="fa-solid fa-chevron-right text-[10px] {{ request()->routeIs('dokumen-asesmen.va.*') ? 'text-rose-500' : 'text-slate-300 group-hover:text-slate-400 group-hover:translate-x-0.5' }} transition-all"></i>
                                </a>
                            </div>
                        </div>

                    @else
                        <!-- Dashboard Admin -->
                        <div>
                            <a href="{{ route('admin.dashboard') }}" 
                               @click="mobileNavOpen = false"
                               class="group flex items-center justify-between px-3 py-2 rounded-xl transition-all {{ request()->routeIs('admin.dashboard') ? 'bg-emerald-50 text-emerald-700 font-bold border border-emerald-100' : 'text-slate-700 hover:bg-slate-50 hover:text-slate-900 font-medium' }}">
                                <div class="flex items-center gap-2.5">
                                    <div class="w-7 h-7 rounded-lg flex items-center justify-center {{ request()->routeIs('admin.dashboard') ? 'bg-emerald-600 text-white shadow-2xs' : 'bg-slate-100 text-slate-500 group-hover:text-slate-700' }}">
                                        <i class="fa-solid fa-chart-pie text-[11px]"></i>
                                    </div>
                                    <span>Dashboard Admin</span>
                                </div>
                                <i class="fa-solid fa-chevron-right text-[10px] {{ request()->routeIs('admin.dashboard') ? 'text-emerald-500' : 'text-slate-300 group-hover:text-slate-400 group-hover:translate-x-0.5' }} transition-all"></i>
                            </a>
                        </div>

                        <!-- Data Asesi -->
                        <div>
                            <div class="text-[10px] font-bold text-slate-400 uppercase tracking-wider px-2 mb-1.5">Asesi & Verifikasi</div>
                            <div class="space-y-0.5">
                                @php
                                    $isAsesiAdminActive = (request()->routeIs('admin.manajemen-asesi*') || request()->routeIs('admin.verifikasi*') || request()->routeIs('admin.detail-asesi'));
                                @endphp
                                <a href="{{ route('admin.manajemen-asesi') }}" 
                                   @click="mobileNavOpen = false"
                                   class="group flex items-center justify-between px-3 py-2 rounded-xl transition-all {{ $isAsesiAdminActive ? 'bg-emerald-50 text-emerald-700 font-bold border border-emerald-100' : 'text-slate-700 hover:bg-slate-50 hover:text-slate-900 font-medium' }}">
                                    <div class="flex items-center gap-2.5">
                                        <div class="w-7 h-7 rounded-lg flex items-center justify-center {{ $isAsesiAdminActive ? 'bg-emerald-600 text-white shadow-2xs' : 'bg-slate-100 text-slate-500 group-hover:text-slate-700' }}">
                                            <i class="fa-solid fa-user-check text-[11px]"></i>
                                        </div>
                                        <span>Data & Verifikasi Asesi</span>
                                    </div>
                                    <i class="fa-solid fa-chevron-right text-[10px] {{ $isAsesiAdminActive ? 'text-emerald-500' : 'text-slate-300 group-hover:text-slate-400 group-hover:translate-x-0.5' }} transition-all"></i>
                                </a>
                            </div>
                        </div>

                        <!-- Skema & Jadwal -->
                        <div>
                            <div class="text-[10px] font-bold text-slate-400 uppercase tracking-wider px-2 mb-1.5">Skema & Pelaksanaan</div>
                            <div class="space-y-0.5">
                                <a href="{{ route('admin.manajemen-skema') }}" 
                                   @click="mobileNavOpen = false"
                                   class="group flex items-center justify-between px-3 py-2 rounded-xl transition-all {{ request()->routeIs('admin.manajemen-skema*') ? 'bg-emerald-50 text-emerald-700 font-bold border border-emerald-100' : 'text-slate-700 hover:bg-slate-50 hover:text-slate-900 font-medium' }}">
                                    <div class="flex items-center gap-2.5">
                                        <div class="w-7 h-7 rounded-lg flex items-center justify-center {{ request()->routeIs('admin.manajemen-skema*') ? 'bg-emerald-600 text-white shadow-2xs' : 'bg-slate-100 text-slate-500 group-hover:text-slate-700' }}">
                                            <i class="fa-solid fa-layer-group text-[11px]"></i>
                                        </div>
                                        <span>Master Skema Sertifikasi</span>
                                    </div>
                                    <i class="fa-solid fa-chevron-right text-[10px] {{ request()->routeIs('admin.manajemen-skema*') ? 'text-emerald-500' : 'text-slate-300 group-hover:text-slate-400 group-hover:translate-x-0.5' }} transition-all"></i>
                                </a>

                                <a href="{{ route('admin.manajemen-jadwal') }}" 
                                   @click="mobileNavOpen = false"
                                   class="group flex items-center justify-between px-3 py-2 rounded-xl transition-all {{ request()->routeIs('admin.manajemen-jadwal*') ? 'bg-emerald-50 text-emerald-700 font-bold border border-emerald-100' : 'text-slate-700 hover:bg-slate-50 hover:text-slate-900 font-medium' }}">
                                    <div class="flex items-center gap-2.5">
                                        <div class="w-7 h-7 rounded-lg flex items-center justify-center {{ request()->routeIs('admin.manajemen-jadwal*') ? 'bg-emerald-600 text-white shadow-2xs' : 'bg-slate-100 text-slate-500 group-hover:text-slate-700' }}">
                                            <i class="fa-solid fa-calendar-days text-[11px]"></i>
                                        </div>
                                        <span>Jadwal Asesmen</span>
                                    </div>
                                    <i class="fa-solid fa-chevron-right text-[10px] {{ request()->routeIs('admin.manajemen-jadwal*') ? 'text-emerald-500' : 'text-slate-300 group-hover:text-slate-400 group-hover:translate-x-0.5' }} transition-all"></i>
                                </a>

                                <a href="{{ route('admin.master-muk.index') }}" 
                                   @click="mobileNavOpen = false"
                                   class="group flex items-center justify-between px-3 py-2 rounded-xl transition-all {{ request()->routeIs('admin.master-muk*') ? 'bg-emerald-50 text-emerald-700 font-bold border border-emerald-100' : 'text-slate-700 hover:bg-slate-50 hover:text-slate-900 font-medium' }}">
                                    <div class="flex items-center gap-2.5">
                                        <div class="w-7 h-7 rounded-lg flex items-center justify-center {{ request()->routeIs('admin.master-muk*') ? 'bg-emerald-600 text-white shadow-2xs' : 'bg-slate-100 text-slate-500 group-hover:text-slate-700' }}">
                                            <i class="fa-solid fa-file-signature text-[11px]"></i>
                                        </div>
                                        <span>Formulir & MUK</span>
                                    </div>
                                    <i class="fa-solid fa-chevron-right text-[10px] {{ request()->routeIs('admin.master-muk*') ? 'text-emerald-500' : 'text-slate-300 group-hover:text-slate-400 group-hover:translate-x-0.5' }} transition-all"></i>
                                </a>
                            </div>
                        </div>

                        <!-- Dokumen Asesmen Admin -->
                        <div>
                            <div class="text-[10px] font-bold text-slate-400 uppercase tracking-wider px-2 mb-1.5">Dokumen Asesmen</div>
                            <div class="space-y-0.5">
                                <a href="{{ route('dokumen-asesmen.index') }}" 
                                   @click="mobileNavOpen = false"
                                   class="group flex items-center justify-between px-3 py-2 rounded-xl transition-all {{ request()->routeIs('dokumen-asesmen.index') ? 'bg-emerald-50 text-emerald-700 font-bold border border-emerald-100' : 'text-slate-700 hover:bg-slate-50 hover:text-slate-900 font-medium' }}">
                                    <div class="flex items-center gap-2.5">
                                        <div class="w-7 h-7 rounded-lg flex items-center justify-center {{ request()->routeIs('dokumen-asesmen.index') ? 'bg-emerald-600 text-white shadow-2xs' : 'bg-slate-100 text-slate-500 group-hover:text-slate-700' }}">
                                            <i class="fa-solid fa-folder-tree text-[11px]"></i>
                                        </div>
                                        <span>Pusat Dokumen Hub</span>
                                    </div>
                                    <i class="fa-solid fa-chevron-right text-[10px] {{ request()->routeIs('dokumen-asesmen.index') ? 'text-emerald-500' : 'text-slate-300 group-hover:text-slate-400 group-hover:translate-x-0.5' }} transition-all"></i>
                                </a>

                                <a href="{{ route('dokumen-asesmen.ak05.index') }}" 
                                   @click="mobileNavOpen = false"
                                   class="group flex items-center justify-between px-3 py-2 rounded-xl transition-all {{ request()->routeIs('dokumen-asesmen.ak05.*') ? 'bg-emerald-50 text-emerald-700 font-bold border border-emerald-100' : 'text-slate-700 hover:bg-slate-50 hover:text-slate-900 font-medium' }}">
                                    <div class="flex items-center gap-2.5">
                                        <div class="w-7 h-7 rounded-lg flex items-center justify-center {{ request()->routeIs('dokumen-asesmen.ak05.*') ? 'bg-emerald-600 text-white shadow-2xs' : 'bg-slate-100 text-slate-500 group-hover:text-slate-700' }}">
                                            <i class="fa-solid fa-file-lines text-[11px]"></i>
                                        </div>
                                        <span>FR.AK.05 &bull; Laporan Asesmen</span>
                                    </div>
                                    <i class="fa-solid fa-chevron-right text-[10px] {{ request()->routeIs('dokumen-asesmen.ak05.*') ? 'text-emerald-500' : 'text-slate-300 group-hover:text-slate-400 group-hover:translate-x-0.5' }} transition-all"></i>
                                </a>

                                <a href="{{ route('dokumen-asesmen.ak06.index') }}" 
                                   @click="mobileNavOpen = false"
                                   class="group flex items-center justify-between px-3 py-2 rounded-xl transition-all {{ request()->routeIs('dokumen-asesmen.ak06.*') ? 'bg-emerald-50 text-emerald-700 font-bold border border-emerald-100' : 'text-slate-700 hover:bg-slate-50 hover:text-slate-900 font-medium' }}">
                                    <div class="flex items-center gap-2.5">
                                        <div class="w-7 h-7 rounded-lg flex items-center justify-center {{ request()->routeIs('dokumen-asesmen.ak06.*') ? 'bg-emerald-600 text-white shadow-2xs' : 'bg-slate-100 text-slate-500 group-hover:text-slate-700' }}">
                                            <i class="fa-solid fa-file-waveform text-[11px]"></i>
                                        </div>
                                        <span>FR.AK.06 &bull; Meninjau Asesmen</span>
                                    </div>
                                    <i class="fa-solid fa-chevron-right text-[10px] {{ request()->routeIs('dokumen-asesmen.ak06.*') ? 'text-emerald-500' : 'text-slate-300 group-hover:text-slate-400 group-hover:translate-x-0.5' }} transition-all"></i>
                                </a>

                                <a href="{{ route('dokumen-asesmen.va.index') }}" 
                                   @click="mobileNavOpen = false"
                                   class="group flex items-center justify-between px-3 py-2 rounded-xl transition-all {{ request()->routeIs('dokumen-asesmen.va.*') ? 'bg-emerald-50 text-emerald-700 font-bold border border-emerald-100' : 'text-slate-700 hover:bg-slate-50 hover:text-slate-900 font-medium' }}">
                                    <div class="flex items-center gap-2.5">
                                        <div class="w-7 h-7 rounded-lg flex items-center justify-center {{ request()->routeIs('dokumen-asesmen.va.*') ? 'bg-emerald-600 text-white shadow-2xs' : 'bg-slate-100 text-slate-500 group-hover:text-slate-700' }}">
                                            <i class="fa-solid fa-stamp text-[11px]"></i>
                                        </div>
                                        <span>FR.VA &bull; Validasi Asesmen</span>
                                    </div>
                                    <i class="fa-solid fa-chevron-right text-[10px] {{ request()->routeIs('dokumen-asesmen.va.*') ? 'text-emerald-500' : 'text-slate-300 group-hover:text-slate-400 group-hover:translate-x-0.5' }} transition-all"></i>
                                </a>
                            </div>
                        </div>

                        <!-- Administrasi & Laporan -->
                        <div>
                            <div class="text-[10px] font-bold text-slate-400 uppercase tracking-wider px-2 mb-1.5">Administrasi & Manajemen</div>
                            <div class="space-y-0.5">
                                <a href="{{ route('admin.dokumen.index') }}" 
                                   @click="mobileNavOpen = false"
                                   class="group flex items-center justify-between px-3 py-2 rounded-xl transition-all {{ (request()->routeIs('admin.dokumen*') || request()->routeIs('formulir.*')) ? 'bg-emerald-50 text-emerald-700 font-bold border border-emerald-100' : 'text-slate-700 hover:bg-slate-50 hover:text-slate-900 font-medium' }}">
                                    <div class="flex items-center gap-2.5">
                                        <div class="w-7 h-7 rounded-lg flex items-center justify-center {{ (request()->routeIs('admin.dokumen*') || request()->routeIs('formulir.*')) ? 'bg-emerald-600 text-white shadow-2xs' : 'bg-slate-100 text-slate-500 group-hover:text-slate-700' }}">
                                            <i class="fa-solid fa-folder-open text-[11px]"></i>
                                        </div>
                                        <span>Berkas Umum LSP</span>
                                    </div>
                                    <i class="fa-solid fa-chevron-right text-[10px] {{ (request()->routeIs('admin.dokumen*') || request()->routeIs('formulir.*')) ? 'text-emerald-500' : 'text-slate-300 group-hover:text-slate-400 group-hover:translate-x-0.5' }} transition-all"></i>
                                </a>

                                <a href="{{ route('admin.manajemen-pengumuman') }}" 
                                   @click="mobileNavOpen = false"
                                   class="group flex items-center justify-between px-3 py-2 rounded-xl transition-all {{ request()->routeIs('admin.manajemen-pengumuman*') ? 'bg-emerald-50 text-emerald-700 font-bold border border-emerald-100' : 'text-slate-700 hover:bg-slate-50 hover:text-slate-900 font-medium' }}">
                                    <div class="flex items-center gap-2.5">
                                        <div class="w-7 h-7 rounded-lg flex items-center justify-center {{ request()->routeIs('admin.manajemen-pengumuman*') ? 'bg-emerald-600 text-white shadow-2xs' : 'bg-slate-100 text-slate-500 group-hover:text-slate-700' }}">
                                            <i class="fa-solid fa-bullhorn text-[11px]"></i>
                                        </div>
                                        <span>Informasi & Pengumuman</span>
                                    </div>
                                    <i class="fa-solid fa-chevron-right text-[10px] {{ request()->routeIs('admin.manajemen-pengumuman*') ? 'text-emerald-500' : 'text-slate-300 group-hover:text-slate-400 group-hover:translate-x-0.5' }} transition-all"></i>
                                </a>

                                <a href="{{ route('admin.laporan-kelulusan') }}" 
                                   @click="mobileNavOpen = false"
                                   class="group flex items-center justify-between px-3 py-2 rounded-xl transition-all {{ request()->routeIs('admin.laporan-kelulusan*') ? 'bg-emerald-50 text-emerald-700 font-bold border border-emerald-100' : 'text-slate-700 hover:bg-slate-50 hover:text-slate-900 font-medium' }}">
                                    <div class="flex items-center gap-2.5">
                                        <div class="w-7 h-7 rounded-lg flex items-center justify-center {{ request()->routeIs('admin.laporan-kelulusan*') ? 'bg-emerald-600 text-white shadow-2xs' : 'bg-slate-100 text-slate-500 group-hover:text-slate-700' }}">
                                            <i class="fa-solid fa-chart-column text-[11px]"></i>
                                        </div>
                                        <span>Laporan Kelulusan</span>
                                    </div>
                                    <i class="fa-solid fa-chevron-right text-[10px] {{ request()->routeIs('admin.laporan-kelulusan*') ? 'text-emerald-500' : 'text-slate-300 group-hover:text-slate-400 group-hover:translate-x-0.5' }} transition-all"></i>
                                </a>

                                <a href="{{ route('admin.manajemen-asesor') }}" 
                                   @click="mobileNavOpen = false"
                                   class="group flex items-center justify-between px-3 py-2 rounded-xl transition-all {{ request()->routeIs('admin.manajemen-asesor*') ? 'bg-emerald-50 text-emerald-700 font-bold border border-emerald-100' : 'text-slate-700 hover:bg-slate-50 hover:text-slate-900 font-medium' }}">
                                    <div class="flex items-center gap-2.5">
                                        <div class="w-7 h-7 rounded-lg flex items-center justify-center {{ request()->routeIs('admin.manajemen-asesor*') ? 'bg-emerald-600 text-white shadow-2xs' : 'bg-slate-100 text-slate-500 group-hover:text-slate-700' }}">
                                            <i class="fa-solid fa-chalkboard-user text-[11px]"></i>
                                        </div>
                                        <span>Data & Surat Tugas Asesor</span>
                                    </div>
                                    <i class="fa-solid fa-chevron-right text-[10px] {{ request()->routeIs('admin.manajemen-asesor*') ? 'text-emerald-500' : 'text-slate-300 group-hover:text-slate-400 group-hover:translate-x-0.5' }} transition-all"></i>
                                </a>
                            </div>
                        </div>
                    @endif
                </div>

                <!-- 4. Drawer Footer (Sticky Bottom with Notification, Public Link, Logout) -->
                <div class="p-3.5 border-t border-slate-100 bg-slate-50/95 backdrop-blur-xs space-y-2 shrink-0 pb-[calc(0.875rem+env(safe-area-inset-bottom,0px))]">
                    <!-- Global Notifikasi Link (Diletakkan di Bagian Paling Bawah) -->
                    @php
                        $unreadNavbarCount = $user ? $user->unreadNotifications()->count() : 0;
                    @endphp
                    <a href="{{ route('notifications.index') }}" 
                       @click="mobileNavOpen = false"
                       class="group flex items-center justify-between px-3 py-2.5 rounded-xl font-semibold transition-all {{ request()->routeIs('notifications.*') ? 'bg-blue-600 text-white shadow-xs' : 'text-slate-700 bg-white border border-slate-200/80 hover:bg-slate-100 shadow-2xs' }}">
                        <div class="flex items-center gap-2.5">
                            <div class="w-7 h-7 rounded-lg flex items-center justify-center {{ request()->routeIs('notifications.*') ? 'bg-blue-700 text-white' : 'bg-slate-100 text-slate-600 group-hover:bg-slate-200' }}">
                                <i class="fa-solid fa-bell text-xs"></i>
                            </div>
                            <span class="text-xs">Notifikasi & Pemberitahuan</span>
                        </div>
                        @if($unreadNavbarCount > 0)
                            <span class="px-2 py-0.5 rounded-full {{ request()->routeIs('notifications.*') ? 'bg-white text-blue-700' : 'bg-rose-500 text-white' }} text-[10px] font-black shadow-2xs">
                                {{ $unreadNavbarCount > 99 ? '99+' : $unreadNavbarCount }}
                            </span>
                        @else
                            <i class="fa-solid fa-chevron-right text-[10px] {{ request()->routeIs('notifications.*') ? 'text-blue-300' : 'text-slate-300 group-hover:text-slate-400 group-hover:translate-x-0.5' }} transition-all"></i>
                        @endif
                    </a>

                    <a href="{{ route('beranda') }}" 
                       @click="mobileNavOpen = false"
                       class="flex items-center justify-center gap-2 w-full px-3 py-2 rounded-xl text-slate-600 hover:text-slate-900 hover:bg-slate-100 text-xs font-semibold transition-colors">
                        <i class="fa-solid fa-globe text-slate-400 text-xs"></i>
                        <span>Website Publik LSP</span>
                    </a>

                    <form action="{{ route('keluar') }}" method="POST">
                        @csrf
                        <button type="submit" class="w-full flex items-center justify-center gap-2 px-3 py-2.5 rounded-xl text-rose-600 bg-rose-50/80 hover:bg-rose-100 border border-rose-200/60 font-semibold transition-colors text-xs cursor-pointer">
                            <i class="fa-solid fa-arrow-right-from-bracket text-xs"></i>
                            <span>Keluar dari Akun</span>
                        </button>
                    </form>

                    <div class="text-[10px] text-slate-400 text-center pt-0.5 font-mono">
                        Lisensi Resmi BNSP-LSP-2629-ID
                    </div>
                </div>
            </div>

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
            </div>
        </footer>
    </div>

@else
    <!-- =========================================================================
         LAYOUT MODE: SIDEBAR MODERN (UNTUK SUPER ADMIN)
         - Tampilan Sidebar Profesional, Modern, Elegan & Tidak Norak
         - Akses Penuh ke Seluruh Fitur Superadmin & Admin
         ========================================================================= -->
    <style>
        .custom-sidebar-scroll::-webkit-scrollbar {
            width: 4px;
        }
        .custom-sidebar-scroll::-webkit-scrollbar-track {
            background: transparent;
        }
        .custom-sidebar-scroll::-webkit-scrollbar-thumb {
            background: rgba(255, 255, 255, 0.12);
            border-radius: 9999px;
        }
        .custom-sidebar-scroll::-webkit-scrollbar-thumb:hover {
            background: rgba(255, 255, 255, 0.25);
        }
    </style>

    <div class="min-h-screen bg-slate-50 flex flex-col lg:flex-row font-sans" x-data="{ sidebarOpen: false }">
        
        <!-- BACKDROP OVERLAY FOR MOBILE -->
        <div x-show="sidebarOpen" 
             x-transition:enter="transition-opacity ease-linear duration-200"
             x-transition:enter-start="opacity-0"
             x-transition:enter-end="opacity-100"
             x-transition:leave="transition-opacity ease-linear duration-200"
             x-transition:leave-start="opacity-100"
             x-transition:leave-end="opacity-0"
             @click="sidebarOpen = false" 
             class="fixed inset-0 bg-slate-900/30 backdrop-blur-xs z-40 lg:hidden"
             style="display: none;"></div>

        <!-- SIDEBAR (BRIGHT PROFESSIONAL THEME, NO ICONS) -->
        <aside class="fixed inset-y-0 left-0 z-50 w-72 bg-white text-slate-700 border-r border-slate-200/90 flex flex-col justify-between transform transition-transform duration-200 ease-in-out lg:translate-x-0 shadow-xs"
               :class="sidebarOpen ? 'translate-x-0 shadow-xl' : '-translate-x-full lg:translate-x-0'">
            
            <!-- TOP BRAND HEADER -->
            <div class="p-5 border-b border-slate-100 bg-white">
                <div class="flex items-center justify-between">
                    <a href="{{ route('superadmin.dashboard') }}" class="flex items-center gap-3">
                        <img src="{{ asset('images/logo-lsp.jpeg') }}" alt="Logo LSP" class="h-9 w-9 object-contain rounded-xl border border-slate-200 p-0.5 bg-white shadow-2xs">
                        <div>
                            <div class="font-bold text-slate-900 text-sm tracking-tight leading-tight">
                                LSP SMKN 1 Gunungputri
                            </div>
                            <div class="inline-block mt-1 px-2 py-0.5 rounded-md bg-blue-50 border border-blue-200 text-[10px] font-extrabold text-blue-700 uppercase tracking-wider">
                                Super Admin Console
                            </div>
                        </div>
                    </a>
                    <button type="button" @click="sidebarOpen = false" class="lg:hidden text-slate-400 hover:text-slate-700 px-2 py-1 text-xs font-semibold rounded-lg hover:bg-slate-100">
                        Tutup
                    </button>
                </div>
            </div>

            <!-- NAVIGATION MENU (SCROLLABLE, NO ICONS) -->
            <div class="flex-1 overflow-y-auto px-4 py-4 space-y-5 custom-sidebar-scroll">
                
                <!-- GRUP 1: RINGKASAN & KONTROL -->
                <div>
                    <div class="text-[10px] uppercase font-extrabold tracking-wider text-slate-400 px-3 mb-1.5">
                        Ringkasan & Kontrol
                    </div>
                    <div class="space-y-1 text-xs">
                        <a href="{{ route('superadmin.dashboard') }}" 
                           class="block px-3.5 py-2.5 rounded-xl transition-all {{ request()->routeIs('superadmin.dashboard*') ? 'bg-blue-600 text-white font-bold shadow-xs' : 'hover:bg-slate-100 hover:text-slate-900 text-slate-600 font-medium' }}">
                            Control Panel Super Admin
                        </a>
                        <a href="{{ route('admin.dashboard') }}" 
                           class="block px-3.5 py-2.5 rounded-xl transition-all {{ request()->routeIs('admin.dashboard*') ? 'bg-slate-900 text-white font-bold shadow-xs' : 'hover:bg-slate-100 hover:text-slate-900 text-slate-600 font-medium' }}">
                            Dashboard Operasional Admin
                        </a>
                    </div>
                </div>

                <!-- GRUP 2: MANAJEMEN PENGGUNA -->
                <div>
                    <div class="text-[10px] uppercase font-extrabold tracking-wider text-slate-400 px-3 mb-1.5 flex items-center justify-between">
                        <span>Manajemen Pengguna</span>
                        <span class="text-[9px] px-1.5 py-0.2 rounded bg-slate-100 text-slate-600 font-bold">CRUD</span>
                    </div>
                    <div class="space-y-1 text-xs">
                        <a href="{{ route('superadmin.manajemen-pengguna') }}" 
                           class="flex items-center justify-between px-3.5 py-2.5 rounded-xl transition-all {{ request()->routeIs('superadmin.manajemen-pengguna*') ? 'bg-blue-600 text-white font-bold shadow-xs' : 'hover:bg-slate-100 hover:text-slate-900 text-slate-600 font-medium' }}">
                            <span>Kelola Seluruh Pengguna</span>
                            <span class="text-[9px] px-1.5 py-0.5 rounded {{ request()->routeIs('superadmin.manajemen-pengguna*') ? 'bg-white/20 text-white' : 'bg-slate-100 text-slate-600' }} font-bold">Master</span>
                        </a>
                        <a href="{{ route('admin.manajemen-asesi') }}" 
                           class="block px-3.5 py-2.5 rounded-xl transition-all {{ (request()->routeIs('admin.manajemen-asesi*') || request()->routeIs('admin.verifikasi*') || request()->routeIs('admin.detail-asesi')) ? 'bg-blue-600 text-white font-bold shadow-xs' : 'hover:bg-slate-100 hover:text-slate-900 text-slate-600 font-medium' }}">
                            Data & Verifikasi Asesi
                        </a>
                        <a href="{{ route('admin.manajemen-asesor') }}" 
                           class="block px-3.5 py-2.5 rounded-xl transition-all {{ request()->routeIs('admin.manajemen-asesor*') ? 'bg-blue-600 text-white font-bold shadow-xs' : 'hover:bg-slate-100 hover:text-slate-900 text-slate-600 font-medium' }}">
                            Data Master Asesor
                        </a>
                    </div>
                </div>

                <!-- GRUP 3: SKEMA & PERANGKAT UJI -->
                <div>
                    <div class="text-[10px] uppercase font-extrabold tracking-wider text-slate-400 px-3 mb-1.5">
                        Skema & Perangkat Uji
                    </div>
                    <div class="space-y-1 text-xs">
                        <a href="{{ route('admin.manajemen-skema') }}" 
                           class="block px-3.5 py-2.5 rounded-xl transition-all {{ request()->routeIs('admin.manajemen-skema*') ? 'bg-blue-600 text-white font-bold shadow-xs' : 'hover:bg-slate-100 hover:text-slate-900 text-slate-600 font-medium' }}">
                            Skema & Unit Kompetensi
                        </a>
                        <a href="{{ route('admin.manajemen-jadwal') }}" 
                           class="block px-3.5 py-2.5 rounded-xl transition-all {{ request()->routeIs('admin.manajemen-jadwal*') ? 'bg-blue-600 text-white font-bold shadow-xs' : 'hover:bg-slate-100 hover:text-slate-900 text-slate-600 font-medium' }}">
                            Jadwal Uji Asesmen
                        </a>
                        <a href="{{ route('admin.master-muk.index') }}" 
                           class="block px-3.5 py-2.5 rounded-xl transition-all {{ request()->routeIs('admin.master-muk*') ? 'bg-blue-600 text-white font-bold shadow-xs' : 'hover:bg-slate-100 hover:text-slate-900 text-slate-600 font-medium' }}">
                            Master MUK & Bank Soal
                        </a>
                    </div>
                </div>

                <!-- GRUP 4: DOKUMEN & MUTU BNSP -->
                <div>
                    <div class="text-[10px] uppercase font-extrabold tracking-wider text-slate-400 px-3 mb-1.5">
                        Dokumen & Mutu BNSP
                    </div>
                    <div class="space-y-1 text-xs">
                        <a href="{{ route('admin.dokumen.index') }}" 
                           class="block px-3.5 py-2.5 rounded-xl transition-all {{ request()->routeIs('admin.dokumen*') ? 'bg-blue-600 text-white font-bold shadow-xs' : 'hover:bg-slate-100 hover:text-slate-900 text-slate-600 font-medium' }}">
                            Pusat Dokumen & Legalitas
                        </a>
                        <a href="{{ route('dokumen-asesmen.index') }}" 
                           class="block px-3.5 py-2.5 rounded-xl transition-all {{ request()->routeIs('dokumen-asesmen*') ? 'bg-blue-600 text-white font-bold shadow-xs' : 'hover:bg-slate-100 hover:text-slate-900 text-slate-600 font-medium' }}">
                            Dokumen Asesmen Hub
                        </a>
                        <a href="{{ route('admin.laporan-kelulusan') }}" 
                           class="block px-3.5 py-2.5 rounded-xl transition-all {{ request()->routeIs('admin.laporan-kelulusan*') ? 'bg-blue-600 text-white font-bold shadow-xs' : 'hover:bg-slate-100 hover:text-slate-900 text-slate-600 font-medium' }}">
                            Laporan & Rekap Kelulusan
                        </a>
                        <a href="{{ route('admin.manajemen-pengumuman') }}" 
                           class="block px-3.5 py-2.5 rounded-xl transition-all {{ request()->routeIs('admin.manajemen-pengumuman*') ? 'bg-blue-600 text-white font-bold shadow-xs' : 'hover:bg-slate-100 hover:text-slate-900 text-slate-600 font-medium' }}">
                            Informasi & Pengumuman
                        </a>
                    </div>
                </div>

                <!-- GRUP 5: SISTEM & AUDIT -->
                <div>
                    <div class="text-[10px] uppercase font-extrabold tracking-wider text-slate-400 px-3 mb-1.5">
                        Sistem & Keamanan
                    </div>
                    <div class="space-y-1 text-xs">
                        <a href="{{ route('superadmin.log-aktivitas') }}" 
                           class="block px-3.5 py-2.5 rounded-xl transition-all {{ request()->routeIs('superadmin.log-aktivitas*') ? 'bg-blue-600 text-white font-bold shadow-xs' : 'hover:bg-slate-100 hover:text-slate-900 text-slate-600 font-medium' }}">
                            Audit Log Aktivitas
                        </a>
                        <a href="{{ route('superadmin.pengaturan-sistem') }}" 
                           class="block px-3.5 py-2.5 rounded-xl transition-all {{ request()->routeIs('superadmin.pengaturan-sistem*') ? 'bg-blue-600 text-white font-bold shadow-xs' : 'hover:bg-slate-100 hover:text-slate-900 text-slate-600 font-medium' }}">
                            Pengaturan Global LSP
                        </a>
                    </div>
                </div>

            </div>

            <!-- SIDEBAR FOOTER (USER PROFILE & LOGOUT, NO ICONS) -->
            <div class="p-4 border-t border-slate-200/80 bg-slate-50/80">
                <div class="flex items-center justify-between gap-3">
                    <div class="flex items-center gap-2.5 min-w-0">
                        <div class="w-9 h-9 rounded-xl bg-blue-100 text-blue-800 font-extrabold text-xs flex items-center justify-center shrink-0 border border-blue-200">
                            {{ strtoupper(substr($user?->nama_lengkap ?? 'SA', 0, 2)) }}
                        </div>
                        <div class="min-w-0 flex-1">
                            <div class="font-bold text-xs text-slate-900 truncate" title="{{ $user?->nama_lengkap }}">
                                {{ $user?->nama_lengkap ?? 'Super Admin' }}
                            </div>
                            <div class="text-[10px] text-blue-700 font-semibold">
                                Super Administrator
                            </div>
                        </div>
                    </div>
                    <form action="{{ route('keluar') }}" method="POST">
                        @csrf
                        <button type="submit" class="px-2.5 py-1.5 rounded-lg text-xs font-bold text-rose-700 bg-rose-50 hover:bg-rose-100 border border-rose-200 transition-colors" title="Keluar dari Akun">
                            Keluar
                        </button>
                    </form>
                </div>
            </div>

        </aside>

        <!-- MAIN CONTENT WRAPPER -->
        <div class="flex-1 lg:pl-72 flex flex-col min-h-screen">
            
            <!-- TOPBAR DESKTOP & MOBILE (NO ICONS) -->
            <header class="sticky top-0 z-30 h-16 bg-white/95 backdrop-blur-md border-b border-slate-200 px-4 sm:px-6 lg:px-8 flex items-center justify-between no-print transition-all">
                <div class="flex items-center gap-3">
                    <button type="button" @click="sidebarOpen = true" class="lg:hidden px-3 py-1.5 rounded-xl border border-slate-200 text-slate-700 font-bold text-xs hover:bg-slate-100 transition-colors cursor-pointer">
                        Menu
                    </button>
                    <div class="flex items-center gap-2">
                        <span class="font-bold text-slate-900 text-xs sm:text-sm">LSP SMKN 1 Gunungputri</span>
                        <span class="text-slate-300 hidden sm:inline">&bull;</span>
                        <span class="hidden sm:inline-block px-2.5 py-0.5 rounded-md bg-slate-100 text-slate-700 border border-slate-200 text-[11px] font-bold">
                            Super Admin Console &bull; Akses Penuh Sistem
                        </span>
                    </div>
                </div>

                <div class="flex items-center gap-3">
                    <a href="{{ route('beranda') }}" target="_blank" class="hidden sm:inline-block px-3 py-1.5 rounded-xl text-xs font-semibold text-slate-600 hover:text-blue-700 hover:bg-blue-50 border border-slate-200 transition-colors">
                        Website Publik
                    </a>

                    <x-notification-bell />

                    <div class="flex items-center gap-2.5 pl-3 border-l border-slate-200">
                        <div class="w-8 h-8 rounded-xl bg-blue-50 text-blue-700 border border-blue-200 flex items-center justify-center font-bold text-xs shrink-0">
                            {{ strtoupper(substr($user?->nama_lengkap ?? 'SA', 0, 2)) }}
                        </div>
                        <div class="hidden md:block text-left leading-tight">
                            <div class="font-bold text-xs text-slate-900 truncate max-w-[140px]" title="{{ $user?->nama_lengkap }}">
                                {{ $user?->nama_lengkap ?? 'Super Admin' }}
                            </div>
                            <div class="text-[10px] text-blue-700 font-semibold">Super Administrator</div>
                        </div>
                    </div>
                </div>
            </header>

            <!-- MAIN CONTAINER -->
            <main class="flex-1 p-4 sm:p-6 lg:p-8">
                <x-assessment-alert-banner />
                @yield('konten')
            </main>

            <!-- FOOTER -->
            <footer class="no-print py-4 px-6 border-t border-slate-200 bg-white text-xs text-slate-500 flex flex-col sm:flex-row justify-between items-center gap-2">
                <div>
                    &copy; {{ date('Y') }} <strong>{{ config('lsp.nama_lsp', 'LSP SMKN 1 Gunungputri') }}</strong> &bull; Lisensi BNSP: <strong class="text-slate-700">{{ config('lsp.nomor_lisensi', 'BNSP-LSP-2629-ID') }}</strong>
                </div>
                <div class="text-[11px] text-slate-400">
                    Sistem Manajemen Sertifikasi Terakreditasi BNSP
                </div>
            </footer>
        </div>

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
@stack('scripts')
</body>
</html>
