@extends('tata-letak.dasbor')

@section('judul', 'Pusat Pembuatan Formulir')

@push('css')
    <link rel="stylesheet" href="{{ asset('css/asesor/dashboard-asesor.css') }}">
    <style>
        .ia-grid-cards {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(320px, 1fr));
            gap: 1.15rem;
        }
        .ia-card {
            background: #ffffff;
            border: 1px solid #e2e8f0;
            border-radius: 14px;
            padding: 1.35rem;
            display: flex;
            flex-direction: column;
            justify-content: space-between;
            transition: all 0.2s ease;
        }
        .ia-card:hover {
            border-color: #93c5fd;
            box-shadow: 0 10px 25px -5px rgba(37, 99, 235, 0.08);
            transform: translateY(-2px);
        }
        .ia-code-badge {
            font-family: monospace;
            font-weight: 800;
            font-size: 0.8rem;
            padding: 0.25rem 0.55rem;
            border-radius: 6px;
            display: inline-block;
        }
    </style>
@endpush

@section('konten')
<div class="max-w-7xl mx-auto px-2 sm:px-4 py-2 space-y-5 animasi-slide">

    <!-- =========================================================================
         1. HEADER TITLE (TANPA BANNER)
         ========================================================================= -->
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3 pb-1 border-b border-slate-100">
        <div>
            <h1 class="text-xl sm:text-2xl font-black text-slate-900 tracking-tight">
                Pusat Pembuatan Formulir
            </h1>
            <p class="text-xs sm:text-sm text-slate-500 mt-0.5">
                Penyusunan instrumen FR.MAPA dan FR.IA per skema sertifikasi
            </p>
        </div>

        <!-- Quick Info Tag -->
        <div class="flex items-center gap-2">
            <span class="inline-flex items-center px-3 py-1.5 rounded-xl bg-indigo-50 border border-indigo-100 text-indigo-700 text-xs font-bold">
                Standar BNSP Resmi
            </span>
        </div>
    </div>

    @if(session('sukses'))
        <div class="p-4 rounded-xl bg-emerald-50 border border-emerald-200 text-emerald-800 text-xs sm:text-sm font-semibold flex items-center gap-2">
            <span class="w-2 h-2 rounded-full bg-emerald-500"></span>
            <span>{{ session('sukses') }}</span>
        </div>
    @endif

    @if(session('error'))
        <div class="p-4 rounded-xl bg-red-50 border border-red-200 text-red-800 text-xs sm:text-sm font-semibold flex items-center gap-2">
            <span class="w-2 h-2 rounded-full bg-red-500"></span>
            <span>{{ session('error') }}</span>
        </div>
    @endif

    @if(!$selectedSkema)
        {{-- STATE KOSONG: Belum ada skema aktif --}}
        <div class="bg-white rounded-2xl border-2 border-dashed border-slate-200 p-8 sm:p-12 text-center">
            <div class="w-16 h-16 rounded-2xl bg-slate-100 text-slate-400 flex items-center justify-center mx-auto mb-4">
                <i class="fa-solid fa-folder-open text-2xl"></i>
            </div>
            <h2 class="text-lg sm:text-xl font-bold text-slate-700 mb-2">Belum Ada Skema Sertifikasi Aktif</h2>
            <p class="text-sm text-slate-500 max-w-lg mx-auto mb-6">
                Untuk mulai menyusun formulir asesmen, silakan buat dan aktifkan skema sertifikasi terlebih dahulu melalui menu <strong>Manajemen Skema</strong>.
            </p>
            @if(auth()->check() && in_array(auth()->user()->peran, ['admin', 'superadmin']))
                <a href="{{ route('admin.manajemen-skema') }}" class="inline-flex items-center gap-2 px-5 py-2.5 rounded-xl bg-indigo-600 hover:bg-indigo-700 text-white text-sm font-bold shadow transition">
                    <i class="fa-solid fa-plus"></i>
                    <span>Buat Skema Baru</span>
                </a>
            @else
                <p class="text-xs text-slate-400">Hubungi admin untuk menambahkan skema sertifikasi.</p>
            @endif
        </div>
    @else

    @if(auth()->check() && auth()->user()->peran === 'asesor')
        <!-- =========================================================================
             2. INFO SKEMA PENUGASAN ASESOR (TETAP TANPA DROPDOWN PILIH SKEMA)
             ========================================================================= -->
        <div class="bg-white rounded-2xl border border-slate-200/90 shadow-2xs p-4 sm:p-5">
            <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
                
                <!-- Info Skema Penugasan -->
                <div class="flex items-start sm:items-center gap-3 min-w-0">
                    <div class="w-12 h-12 rounded-xl bg-blue-50 text-blue-700 border border-blue-100 flex items-center justify-center font-black text-sm shrink-0">
                        LSP
                    </div>
                    <div class="min-w-0">
                        <div class="flex items-center gap-2 flex-wrap">
                            <span class="text-[10px] font-bold uppercase tracking-wider px-2 py-0.5 rounded bg-blue-50 text-blue-700 border border-blue-200">
                                Skema Penugasan Anda
                            </span>
                            <span class="text-xs font-mono font-bold text-slate-500">
                                {{ $selectedSkema->kode_skema ?? '-' }}
                            </span>
                        </div>
                        <h2 class="text-base sm:text-lg font-bold text-slate-900 truncate mt-1" title="{{ $selectedSkema->nama_skema ?? '-' }}">
                            {{ $selectedSkema->nama_skema ?? 'Belum Ada Skema Ditugaskan' }}
                        </h2>
                        <div class="text-xs text-slate-500 font-mono mt-0.5 flex flex-wrap items-center gap-x-3 gap-y-1">
                            <span>{{ $selectedSkema ? $selectedSkema->unitKompetensi->count() : 0 }} Unit Kompetensi</span>
                            <span>&bull;</span>
                            <span>{{ $selectedSkema ? $selectedSkema->masterInstruments->count() : 0 }} Perangkat MUK Terdaftar</span>
                        </div>
                    </div>
                </div>

                @if($skemaList->count() > 1)
                    <!-- Dropdown Pemilihan Skema untuk Asesor jika mengampu lebih dari 1 skema penugasan -->
                    @php
                        $asesorFormAction = request()->routeIs('admin.master-muk*') ? route('admin.master-muk.index') : route('asesor.mapa');
                    @endphp
                    <form action="{{ $asesorFormAction }}" method="GET" class="shrink-0 w-full sm:w-auto min-w-[280px]">
                        <div class="relative">
                            <select name="skema_id" id="asesor_skema_id" onchange="this.form.submit()" 
                                    class="w-full text-xs sm:text-sm font-semibold py-2.5 pl-3.5 pr-10 rounded-xl bg-slate-50 text-slate-900 border border-slate-200 shadow-2xs hover:bg-white focus:bg-white focus:outline-none focus:ring-2 focus:ring-blue-500/20 focus:border-blue-500 cursor-pointer transition">
                                @foreach($skemaList as $s)
                                    <option value="{{ $s->id }}" {{ ($selectedSkemaId ?? 0) == $s->id ? 'selected' : '' }}>
                                        {{ $s->kode_skema }} &mdash; {{ $s->nama_skema }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                    </form>
                @endif

            </div>
        </div>
    @else
        <!-- =========================================================================
             2. PANEL PEMILIH SKEMA KHUSUS ADMIN (TEMA SESUAI WEB)
             ========================================================================= -->
        <div class="bg-white rounded-2xl border border-slate-200/90 shadow-2xs p-4 sm:p-5">
            <div class="flex flex-col lg:flex-row lg:items-center justify-between gap-4">
                <div class="space-y-1 min-w-0">
                    <div class="inline-flex items-center px-2.5 py-0.5 rounded-md bg-emerald-50 text-emerald-700 border border-emerald-200 text-[11px] font-bold">
                        <span>Pusat Manajemen Formulir Asesmen (Admin)</span>
                    </div>
                    <h2 class="text-base sm:text-lg font-black text-slate-900 tracking-tight">
                        Pilih Skema yang Mau Dibuatkan Formulir:
                    </h2>
                    <p class="text-xs text-slate-500 max-w-2xl leading-relaxed">
                        Formulir (MAPA & FR.IA) yang Anda buat pada skema ini otomatis berlaku dan langsung muncul di semua asesor yang mengampu skema tersebut.
                    </p>
                </div>

                <!-- Dropdown Pemilihan Skema untuk Admin -->
                <form action="{{ route('admin.master-muk.index') }}" method="GET" class="shrink-0 w-full lg:w-auto min-w-[320px] max-w-md">
                    <div class="relative">
                        <select name="skema_id" id="admin_skema_id" onchange="this.form.submit()" 
                                class="w-full text-xs sm:text-sm font-semibold py-2.5 pl-3.5 pr-10 rounded-xl bg-slate-50 text-slate-900 border border-slate-200 shadow-2xs hover:bg-white focus:bg-white focus:outline-none focus:ring-2 focus:ring-emerald-500/20 focus:border-emerald-500 cursor-pointer transition">
                            @foreach($skemaList as $s)
                                <option value="{{ $s->id }}" {{ ($selectedSkemaId ?? 0) == $s->id ? 'selected' : '' }}>
                                    {{ $s->kode_skema }} &mdash; {{ $s->nama_skema }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                </form>
            </div>

            @if($selectedSkema)
                <!-- Detail Ringkas Skema Aktif Terpilih -->
                <div class="mt-4 pt-3.5 border-t border-slate-100 flex flex-wrap items-center gap-2 text-xs">
                    <span class="text-slate-500 font-semibold">Skema Aktif:</span>
                    <span class="font-bold text-slate-900">{{ $selectedSkema->nama_skema }}</span>
                    <span class="px-2 py-0.5 rounded bg-slate-100 text-slate-700 font-mono text-[11px] font-bold">
                        {{ $selectedSkema->kode_skema }}
                    </span>
                    <span class="px-2 py-0.5 rounded bg-emerald-50 text-emerald-700 border border-emerald-200 font-semibold text-[11px]">
                        {{ $selectedSkema->unitKompetensi->count() }} Unit Kompetensi
                    </span>
                    <span class="px-2 py-0.5 rounded bg-blue-50 text-blue-700 border border-blue-200 font-semibold text-[11px]">
                        {{ $selectedSkema->masterInstruments->count() }} Perangkat MUK Terdaftar
                    </span>
                </div>
            @endif
        </div>
    @endif

    <!-- =========================================================================
         3. BAGIAN 1: FORMULIR MAPA (PERENCANAAN & PETA ASESMEN)
         ========================================================================= -->
    <div class="space-y-3">
        <div class="flex items-center justify-between">
            <div class="flex items-center gap-2">
                <span class="w-2 h-6 bg-indigo-600 rounded-full"></span>
                <h2 class="text-sm sm:text-base font-bold text-slate-800 uppercase tracking-wide">
                    1. Formulir Perencanaan Asesmen (FR.MAPA, FR.AK.01 & FR.AK.07)
                </h2>
            </div>
            <span class="text-xs text-slate-400 font-medium">Acuan Baku Seluruh Peserta Uji</span>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4">
            <!-- FR.MAPA.01 -->
            <div class="bg-white rounded-2xl border border-slate-200/90 shadow-2xs p-4 sm:p-5 flex flex-col justify-between transition hover:border-indigo-200 hover:shadow-xs">
                <div>
                    <div class="flex items-center justify-between gap-2 mb-2">
                        <span class="ia-code-badge bg-indigo-50 text-indigo-700 border border-indigo-200">FR.MAPA.01</span>
                        @php
                            $val01Tabel = $mapa01Master->penyusun_validator_tabel ?? [];
                            $isVal01 = !empty($val01Tabel['validator_1']['ttd']) || (($val01Tabel['validator_1']['status_validasi'] ?? '') === 'tervalidasi');
                        @endphp
                        <div class="flex items-center gap-1.5 flex-wrap justify-end">
                            @if($isVal01)
                                <span class="px-2 py-0.5 rounded-full bg-emerald-50 text-emerald-700 border border-emerald-200 text-[10px] font-bold">
                                    ✓ Tervalidasi Admin
                                </span>
                            @elseif($mapa01Master)
                                <span class="px-2 py-0.5 rounded-full bg-amber-50 text-amber-700 border border-amber-200 text-[10px] font-bold">
                                    Menunggu Validasi
                                </span>
                            @endif

                            @if($mapa01Master && $mapa01Master->status_mapa === 'selesai')
                                <span class="px-2.5 py-0.5 rounded-full bg-emerald-50 text-emerald-700 border border-emerald-200 text-[11px] font-bold">
                                    ✓ Siap Digunakan
                                </span>
                            @elseif($mapa01Master)
                                <span class="px-2.5 py-0.5 rounded-full bg-amber-50 text-amber-700 border border-amber-200 text-[11px] font-bold">
                                    Draft Perencanaan
                                </span>
                            @else
                                <span class="px-2.5 py-0.5 rounded-full bg-slate-100 text-slate-500 text-[11px] font-medium">
                                    Belum Dikonfigurasi
                                </span>
                            @endif
                        </div>
                    </div>

                    <h3 class="text-base font-bold text-slate-900 mb-1">
                        Merencanakan Aktivitas dan Proses Asesmen
                    </h3>
                    <p class="text-xs text-slate-500 leading-relaxed">
                        Penetapan pendekatan asesmen, tujuan asesmen, konteks lingkungan (nyata/simulasi), acuan pembanding standar industri, identifikasi bukti langsung (L), tidak langsung (TL), dan tambahan (T), serta penyesuaian yang wajar.
                    </p>

                    @if($mapa01Master)
                        <div class="mt-3 text-[11px] text-slate-400">
                            Terakhir diselaraskan: <strong>{{ $mapa01Master->updated_at->format('d M Y, H:i') }}</strong>
                        </div>
                    @endif
                </div>

                <div class="mt-4 pt-3.5 border-t border-slate-100 flex items-center gap-2">
                    @php
                        $isMapa01Exist = !is_null($mapa01Master);
                        $targetMapa01 = $selectedSkema ? route('asesor.skema.mapa-01', $selectedSkema->id) : ($samplePendaftaran ? route('asesor.mapa-01', $samplePendaftaran->id) : '#');
                        $isAdminUser = auth()->check() && in_array(auth()->user()->peran, ['admin', 'superadmin']);
                    @endphp
                    @if($isMapa01Exist)
                        <a href="{{ $targetMapa01 }}" class="w-full inline-flex items-center justify-center px-3.5 py-2 rounded-xl {{ $isAdminUser && !$isVal01 ? 'bg-emerald-600 hover:bg-emerald-700 text-white shadow-2xs' : 'bg-indigo-50 hover:bg-indigo-100 text-indigo-700 border border-indigo-200' }} text-xs font-bold transition">
                            <span>{{ $isAdminUser ? ($isVal01 ? 'Kelola & Validasi' : 'Validasi Form MAPA') : 'Kelola Form' }}</span>
                        </a>
                    @else
                        <a href="{{ $targetMapa01 }}" class="w-full inline-flex items-center justify-center px-3.5 py-2 rounded-xl bg-indigo-600 hover:bg-indigo-700 text-white text-xs font-bold shadow-2xs transition">
                            <span>Tambah Form +</span>
                        </a>
                    @endif
                </div>
            </div>

            <!-- FR.MAPA.02 -->
            <div class="bg-white rounded-2xl border border-slate-200/90 shadow-2xs p-4 sm:p-5 flex flex-col justify-between transition hover:border-indigo-200 hover:shadow-xs">
                <div>
                    <div class="flex items-center justify-between gap-2 mb-2">
                        <span class="ia-code-badge bg-indigo-50 text-indigo-700 border border-indigo-200">FR.MAPA.02</span>
                        @if($mapa02Master && $mapa02Master->status_mapa === 'selesai')
                            <span class="px-2.5 py-0.5 rounded-full bg-emerald-50 text-emerald-700 border border-emerald-200 text-[11px] font-bold">
                                ✓ Siap Digunakan
                            </span>
                        @elseif($mapa02Master)
                            <span class="px-2.5 py-0.5 rounded-full bg-amber-50 text-amber-700 border border-amber-200 text-[11px] font-bold">
                                Draft Matriks Peta
                            </span>
                        @else
                            <span class="px-2.5 py-0.5 rounded-full bg-slate-100 text-slate-500 text-[11px] font-medium">
                                Belum Dikonfigurasi
                            </span>
                        @endif
                    </div>

                    <h3 class="text-base font-bold text-slate-900 mb-1">
                        Peta Instrumen Asesmen (Matriks Pemetaan)
                    </h3>
                    <p class="text-xs text-slate-500 leading-relaxed">
                        Pemetaan matriks keselarasan Unit Kompetensi, Elemen, dan KUK terhadap instrumen asesmen bukti (CLO/IA.01, DPT/IA.02, PMO/IA.03, DPE/IA.05/06, DPL/IA.07, VP/IA.08, PW/IA.09, CRP/IA.11).
                    </p>

                    @if($mapa02Master)
                        <div class="mt-3 text-[11px] text-slate-400">
                            Terakhir diselaraskan: <strong>{{ $mapa02Master->updated_at->format('d M Y, H:i') }}</strong>
                        </div>
                    @endif
                </div>

                <div class="mt-4 pt-3.5 border-t border-slate-100 flex items-center gap-2">
                    @php
                        $isMapa02Exist = !is_null($mapa02Master);
                        $targetMapa02 = $selectedSkema ? route('asesor.skema.mapa-02', $selectedSkema->id) : ($samplePendaftaran ? route('asesor.mapa-02', $samplePendaftaran->id) : '#');
                    @endphp
                    @if($isMapa02Exist)
                        <a href="{{ $targetMapa02 }}" class="w-full inline-flex items-center justify-center px-3.5 py-2 rounded-xl bg-indigo-50 hover:bg-indigo-100 text-indigo-700 border border-indigo-200 text-xs font-bold transition">
                            <span>Kelola Form</span>
                        </a>
                    @else
                        <a href="{{ $targetMapa02 }}" class="w-full inline-flex items-center justify-center px-3.5 py-2 rounded-xl bg-indigo-600 hover:bg-indigo-700 text-white text-xs font-bold shadow-2xs transition">
                            <span>Tambah Form +</span>
                        </a>
                    @endif
                </div>
            </div>

            <!-- FR.AK.01 -->
            <div class="bg-white rounded-2xl border border-slate-200/90 shadow-2xs p-4 sm:p-5 flex flex-col justify-between transition hover:border-indigo-200 hover:shadow-xs">
                <div>
                    <div class="flex items-center justify-between gap-2 mb-2">
                        <span class="ia-code-badge bg-indigo-50 text-indigo-700 border border-indigo-200">FR.AK.01</span>
                        @if(!empty($masterAk01) && $masterAk01->status === 'selesai')
                            <span class="px-2.5 py-0.5 rounded-full bg-emerald-50 text-emerald-700 border border-emerald-200 text-[11px] font-bold">
                                ✓ Siap Digunakan
                            </span>
                        @elseif(!empty($masterAk01))
                            <span class="px-2.5 py-0.5 rounded-full bg-amber-50 text-amber-700 border border-amber-200 text-[11px] font-bold">
                                Draft Pengaturan
                            </span>
                        @else
                            <span class="px-2.5 py-0.5 rounded-full bg-slate-100 text-slate-500 text-[11px] font-medium">
                                Belum Dikonfigurasi
                            </span>
                        @endif
                    </div>

                    <h3 class="text-base font-bold text-slate-900 mb-1">
                        Persetujuan Asesmen & Kerahasiaan (Master Skema)
                    </h3>
                    <p class="text-xs text-slate-500 leading-relaxed">
                        Penetapan jenis TUK standar, metode bukti yang disepakati, klausul kerahasiaan resmi BNSP, dan tanda tangan digital asesor yang otomatis diambil oleh seluruh formulir asesi di skema ini.
                    </p>

                    @if(!empty($masterAk01))
                        <div class="mt-3 text-[11px] text-slate-400">
                            Terakhir diselaraskan: <strong>{{ $masterAk01->updated_at->format('d M Y, H:i') }}</strong>
                        </div>
                    @endif
                </div>

                <div class="mt-4 pt-3.5 border-t border-slate-100 flex items-center gap-2">
                    @php
                        $isAk01Exist = !empty($masterAk01);
                        $targetAk01 = $selectedSkema ? route('asesor.skema.ak-01', $selectedSkema->id) : '#';
                    @endphp
                    @if($isAk01Exist)
                        <a href="{{ $targetAk01 }}" class="w-full inline-flex items-center justify-center px-3.5 py-2 rounded-xl bg-indigo-50 hover:bg-indigo-100 text-indigo-700 border border-indigo-200 text-xs font-bold transition">
                            <span>Kelola Form</span>
                        </a>
                    @else
                        <a href="{{ $targetAk01 }}" class="w-full inline-flex items-center justify-center px-3.5 py-2 rounded-xl bg-indigo-600 hover:bg-indigo-700 text-white text-xs font-bold shadow-2xs transition">
                            <span>Tambah Form +</span>
                        </a>
                    @endif
                </div>
            </div>

            <!-- FR.AK.07 -->
            <div class="bg-white rounded-2xl border border-slate-200/90 shadow-2xs p-4 sm:p-5 flex flex-col justify-between transition hover:border-indigo-200 hover:shadow-xs">
                <div>
                    <div class="flex items-center justify-between gap-2 mb-2">
                        <span class="ia-code-badge bg-indigo-50 text-indigo-700 border border-indigo-200">FR.AK.07</span>
                        @if(!empty($masterAk07) && $masterAk07->status === 'selesai')
                            <span class="px-2.5 py-0.5 rounded-full bg-emerald-50 text-emerald-700 border border-emerald-200 text-[11px] font-bold">
                                ✓ Siap Digunakan
                            </span>
                        @elseif(!empty($masterAk07))
                            <span class="px-2.5 py-0.5 rounded-full bg-amber-50 text-amber-700 border border-amber-200 text-[11px] font-bold">
                                Draft Pengaturan
                            </span>
                        @else
                            <span class="px-2.5 py-0.5 rounded-full bg-slate-100 text-slate-500 text-[11px] font-medium">
                                Belum Dikonfigurasi
                            </span>
                        @endif
                    </div>

                    <h3 class="text-base font-bold text-slate-900 mb-1">
                        Penyesuaian Yang Wajar (Master Skema)
                    </h3>
                    <p class="text-xs text-slate-500 leading-relaxed">
                        Penetapan penyesuaian asesmen, acuan pembanding, dan metode pendukung yang otomatis diselaraskan dan ditampilkan kepada seluruh asesi di skema ini.
                    </p>

                    @if(!empty($masterAk07))
                        <div class="mt-3 text-[11px] text-slate-400">
                            Terakhir diselaraskan: <strong>{{ $masterAk07->updated_at->format('d M Y, H:i') }}</strong>
                        </div>
                    @endif
                </div>

                <div class="mt-4 pt-3.5 border-t border-slate-100 flex flex-col gap-2">
                    @php
                        $isAk07Exist = !empty($masterAk07);
                        $targetAk07 = $selectedSkema ? route('asesor.skema.ak-07', $selectedSkema->id) : '#';
                    @endphp
                    @if($isAk07Exist)
                        <a href="{{ $targetAk07 }}" class="w-full inline-flex items-center justify-center px-3.5 py-2 rounded-xl bg-indigo-50 hover:bg-indigo-100 text-indigo-700 border border-indigo-200 text-xs font-bold transition">
                            <span>Kelola Master Form</span>
                        </a>
                    @elseif($selectedSkema)
                        <a href="{{ $targetAk07 }}" class="w-full inline-flex items-center justify-center px-3.5 py-2 rounded-xl bg-indigo-600 hover:bg-indigo-700 text-white text-xs font-bold shadow-2xs transition">
                            <span>Tambah Form +</span>
                        </a>
                    @else
                        <button type="button" disabled class="w-full inline-flex items-center justify-center px-3.5 py-2 rounded-xl bg-slate-100 text-slate-400 text-xs font-semibold cursor-not-allowed">
                            <span>Pilih Skema Dahulu</span>
                        </button>
                    @endif
                    <a href="{{ route('asesor.daftar-peserta') }}" class="w-full inline-flex items-center justify-center px-3.5 py-1.5 rounded-xl text-slate-500 hover:text-slate-800 text-xs font-medium transition">
                        <span>Lihat Per Peserta &rarr;</span>
                    </a>
                </div>
            </div>
        </div>
    </div>

    <!-- =========================================================================
         4. BAGIAN 2: PERANGKAT INSTRUMEN ASESMEN (FR.IA.01 s/d FR.IA.11)
         ========================================================================= -->
    @php
        // Kamus formulir FR.IA terstruktur BNSP
        $frIaList = [
            'praktik' => [
                'nama_kategori' => 'Praktik & Observasi Langsung (Bukti L)',
                'warna' => 'blue',
                'items' => [
                    [
                        'code' => 'ia01',
                        'kode_resmi' => 'FR.IA.01',
                        'judul' => 'Ceklis Observasi Aktivitas Praktik',
                        'deskripsi' => 'Lembar observasi demonstrasi kerja di tempat kerja atau tempat kerja simulasi dengan kriteria penilaian K / BK.',
                        'route_view' => 'ia01',
                    ],
                    [
                        'code' => 'ia02',
                        'kode_resmi' => 'FR.IA.02',
                        'judul' => 'Tugas Praktik Demonstrasi & Skenario',
                        'deskripsi' => 'Skenario demonstrasi kerja, petunjuk pengerjaan tugas proyek, dan alokasi waktu uji praktik asesi.',
                        'route_view' => 'ia02',
                    ],
                    [
                        'code' => 'ia03',
                        'kode_resmi' => 'FR.IA.03',
                        'judul' => 'Pertanyaan Pendukung Observasi (PMO)',
                        'deskripsi' => 'Daftar pertanyaan klarifikasi dan pertanyaan lisan penggali bukti saat atau pasca observasi praktik.',
                        'route_view' => 'ia03',
                    ],
                    [
                        'code' => 'ia04a',
                        'kode_resmi' => 'FR.IA.04A & 04B',
                        'judul' => 'Penjelasan Proyek Singkat & Format Penilaian Produk',
                        'deskripsi' => 'Terms of Reference (TOR) proyek terstruktur dan format penilaian spesifikasi mutu produk hasil kerja asesi.',
                        'route_view' => 'ia04a',
                    ],
                ]
            ],
            'tertulis' => [
                'nama_kategori' => 'Uji Teori & Pertanyaan Tertulis (Bukti T)',
                'warna' => 'emerald',
                'items' => [
                    [
                        'code' => 'ia05',
                        'kode_resmi' => 'FR.IA.05',
                        'judul' => 'Pertanyaan Tertulis Pilihan Ganda (CBT)',
                        'deskripsi' => 'Bank butir soal pilihan ganda, kunci jawaban, dan pembahasan untuk evaluasi penguasaan pengetahuan dasar (K).',
                        'route_view' => 'ia05a',
                    ],
                    [
                        'code' => 'ia06',
                        'kode_resmi' => 'FR.IA.06',
                        'judul' => 'Pertanyaan Tertulis Esai & Kasus',
                        'deskripsi' => 'Daftar soal uraian, studi kasus pemecahan masalah kejuruan, serta rubrik acuan penilaian asesor.',
                        'route_view' => 'ia06a',
                    ],
                    [
                        'code' => 'ia07',
                        'kode_resmi' => 'FR.IA.07',
                        'judul' => 'Daftar Pertanyaan Lisan',
                        'deskripsi' => 'Bank butir pertanyaan lisan mendalam untuk menguji pemahaman KUK dan dimensi kompetensi asesi.',
                        'route_view' => 'ia07',
                    ],
                ]
            ],
            'portofolio' => [
                'nama_kategori' => 'Portofolio, Wawancara & Pihak Ketiga (Bukti TL / Tambahan)',
                'warna' => 'amber',
                'items' => [
                    [
                        'code' => 'ia08',
                        'kode_resmi' => 'FR.IA.08',
                        'judul' => 'Ceklis Verifikasi Portofolio',
                        'deskripsi' => 'Format pemeriksaan keabsahan dokumen portofolio kerja asesi berdasarkan aturan bukti Valid, Asli, Terkini, dan Memadai (VATM).',
                        'route_view' => 'ia08',
                    ],
                    [
                        'code' => 'ia09',
                        'kode_resmi' => 'FR.IA.09',
                        'judul' => 'Pertanyaan Wawancara',
                        'deskripsi' => 'Panduan wawancara berbasis kompetensi untuk mengonfirmasi portofolio kerja asesi yang belum memadai.',
                        'route_view' => 'ia09',
                    ],
                    [
                        'code' => 'ia10',
                        'kode_resmi' => 'FR.IA.10',
                        'judul' => 'Klarifikasi Bukti Pihak Ketiga',
                        'deskripsi' => 'Format verifikasi laporan kinerja dan kesaksian supervisor / atasan langsung di industri tempat asesi beraktivitas.',
                        'route_view' => 'ia10',
                    ],
                ]
            ],
            'validasi' => [
                'nama_kategori' => 'Peninjauan Mutu & Uji Coba Instrumen',
                'warna' => 'teal',
                'items' => [
                    [
                        'code' => 'ia11',
                        'kode_resmi' => 'FR.IA.11',
                        'judul' => 'Ceklis Meninjau Instrumen Asesmen',
                        'deskripsi' => 'Evaluasi kelayakan instrumen sebelum diujikan terhadap prinsip asesmen (validitas, reliabilitas, fleksibilitas, dan keadilan).',
                        'route_view' => 'ia11',
                    ],
                ]
            ]
        ];
    @endphp

    <div class="space-y-6 pt-2">
        <div class="flex items-center justify-between">
            <div class="flex items-center gap-2">
                <span class="w-2 h-6 bg-blue-600 rounded-full"></span>
                <h2 class="text-sm sm:text-base font-bold text-slate-800 uppercase tracking-wide">
                    2. Perangkat Instrumen Asesmen (FR.IA)
                </h2>
            </div>
            <span class="text-xs text-slate-400 font-medium">Bank Soal & Blanko Uji Skema</span>
        </div>

        @foreach($frIaList as $katKey => $kategori)
            <div class="space-y-3">
                <div class="flex items-center gap-2 text-xs font-bold text-slate-600 uppercase tracking-wider pl-1">
                    <span class="w-1.5 h-1.5 rounded-full bg-slate-400"></span>
                    <span>{{ $kategori['nama_kategori'] }}</span>
                </div>

                <div class="ia-grid-cards">
                    @foreach($kategori['items'] as $item)
                        @php
                            $code = $item['code'];
                            // Cek apakah master instrument sudah ada di skema ini
                            $existingInst = $selectedSkema ? $selectedSkema->masterInstruments->first(function($m) use ($code) {
                                return \App\Models\SchemeMasterInstrument::normalizeCode($m->instrument_code) === \App\Models\SchemeMasterInstrument::normalizeCode($code);
                            }) : null;

                            $isExist = !is_null($existingInst);
                            $jumlahSoal = $existingInst ? $existingInst->questionBanks->count() : 0;
                            $jumlahSpec = $existingInst ? $existingInst->productSpecifications->count() : 0;

                            // Link Kelola Form
                            if ($isExist) {
                                $kelolaUrl = route('admin.master-muk.manage', $existingInst->id);
                            } elseif ($selectedSkema) {
                                $kelolaUrl = route('admin.master-muk.create', [
                                    'skema_id' => $selectedSkema->id,
                                    'code' => $code
                                ]);
                            } else {
                                $kelolaUrl = '#';
                            }

                            // Link Pratinjau / Cetak
                            $cetakUrl = route('formulir.' . $item['route_view'], ['skema_id' => $selectedSkema?->id, 'pendaftaran_id' => $samplePendaftaran?->id]);
                        @endphp

                        <div class="ia-card">
                            <div>
                                <div class="flex items-center justify-between gap-2 mb-2">
                                    <span class="ia-code-badge bg-blue-50 text-blue-700 border border-blue-200">
                                        {{ $item['kode_resmi'] }}
                                    </span>

                                    @if($isExist)
                                        <span class="px-2 py-0.5 rounded-full bg-emerald-50 text-emerald-700 border border-emerald-200 text-[11px] font-bold flex items-center gap-1">
                                            <span class="w-1.5 h-1.5 rounded-full bg-emerald-500"></span>
                                            @if($jumlahSoal > 0)
                                                {{ $jumlahSoal }} Butir Soal
                                            @elseif($jumlahSpec > 0)
                                                {{ $jumlahSpec }} Spek Produk
                                            @else
                                                Tersedia
                                            @endif
                                        </span>
                                    @else
                                        <span class="px-2 py-0.5 rounded-full bg-slate-100 text-slate-500 text-[11px] font-medium">
                                            Belum Ada di MUK
                                        </span>
                                    @endif
                                </div>

                                <h3 class="text-sm font-bold text-slate-900 mb-1 leading-snug">
                                    {{ $item['judul'] }}
                                </h3>
                                <p class="text-xs text-slate-500 leading-relaxed">
                                    {{ $item['deskripsi'] }}
                                </p>
                            </div>

                            <div class="mt-4 pt-3.5 border-t border-slate-100 flex items-center gap-2">
                                <a href="{{ $cetakUrl }}" target="_blank" class="px-3 py-2 rounded-xl bg-slate-100 hover:bg-slate-200 text-slate-700 text-xs font-semibold transition flex items-center justify-center gap-1.5" title="Pratinjau Lembar Blangko Standar BNSP">
                                    <i class="fa-solid fa-eye text-slate-500"></i>
                                    <span>Pratinjau</span>
                                </a>
                                @if($isExist)
                                    <a href="{{ $kelolaUrl }}" class="flex-1 inline-flex items-center justify-center px-3.5 py-2 rounded-xl bg-indigo-50 hover:bg-indigo-100 text-indigo-700 border border-indigo-200 text-xs font-bold transition">
                                        <span>Kelola Form</span>
                                    </a>
                                @elseif($selectedSkema)
                                    <a href="{{ $kelolaUrl }}" class="flex-1 inline-flex items-center justify-center px-3.5 py-2 rounded-xl bg-indigo-600 hover:bg-indigo-700 text-white text-xs font-bold shadow-2xs transition">
                                        <span>Tambah Form +</span>
                                    </a>
                                @else
                                    <a href="{{ $cetakUrl }}" target="_blank" class="flex-1 inline-flex items-center justify-center px-3.5 py-2 rounded-xl bg-indigo-50 hover:bg-indigo-100 text-indigo-700 border border-indigo-200 text-xs font-bold transition">
                                        <span>Buka Blangko</span>
                                    </a>
                                @endif
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>
        @endforeach
    </div>

    @endif {{-- End @if(!$selectedSkema) / @else --}}

</div>
@endsection
