@extends('tata-letak.dasbor')

@section('judul', 'FR.MAPA 02 - Peta Instrumen Asesmen')

@push('css')
    <!-- Signature Pad CDN -->
    <script src="https://cdn.jsdelivr.net/npm/signature_pad@4.1.7/dist/signature_pad.umd.min.js"></script>
    <style>
        @media print {
            .no-print, .sidebar-dasbor, .header-dasbor, .modal-overlay, nav, footer, header { display: none !important; }
            body { background: #fff !important; font-size: 9pt !important; color: #000 !important; }
            .wadah-dasbor { margin: 0 !important; padding: 0 !important; }
            .shadow-md, .shadow-sm, .shadow-2xs, .shadow-lg { box-shadow: none !important; }
            .border { border-color: #94a3b8 !important; }
            table { page-break-inside: auto; width: 100% !important; }
            tr { page-break-inside: avoid; page-break-after: auto; }
            .page-break { page-break-before: always; }
        }

        /* ============================================================
           COMPACT & BALANCED TABLE STYLING (DESKTOP & GENERAL)
           ============================================================ */
        .mapa02-table {
            font-size: 11px;
            border-collapse: collapse;
            width: 100%;
        }

        /* Kolom Pertama (Elemen): Jarak tepi kiri lega (20px) */
        .mapa02-th-elemen,
        .mapa02-td-elemen {
            padding-left: 1.25rem !important;
            padding-right: 0.75rem !important;
            padding-top: 0.45rem !important;
            padding-bottom: 0.45rem !important;
            font-size: 11px !important;
        }

        /* Kolom KUK */
        .mapa02-th-kuk,
        .mapa02-td-kuk {
            padding-left: 0.75rem !important;
            padding-right: 0.85rem !important;
            padding-top: 0.45rem !important;
            padding-bottom: 0.45rem !important;
            font-size: 11px !important;
        }

        /* 8 Kolom Instrumen Asesmen (IA) */
        .mapa02-th-ia,
        .mapa02-td-ia {
            width: 44px !important;
            min-width: 42px !important;
            padding: 0.45rem 0.25rem !important;
            text-align: center !important;
        }

        /* Kolom Terakhir (CRP): Jarak tepi kanan lega (20px) */
        .mapa02-th-ia:last-child,
        .mapa02-td-ia:last-child {
            padding-right: 1.25rem !important;
            min-width: 54px !important;
        }

        .mapa02-cb {
            width: 1rem !important;
            height: 1rem !important;
            margin: 0 auto;
            display: block;
            cursor: pointer;
        }

        /* ============================================================
           MOBILE ONLY (<= 768px)
           ============================================================ */
        @media (max-width: 768px) {
            .mapa02-table-wrapper {
                display: block !important;
                width: 100% !important;
                max-width: 100% !important;
                min-width: 0 !important;
                overflow-x: auto !important;
                -webkit-overflow-scrolling: touch !important;
                touch-action: pan-x pan-y !important;
                scrollbar-width: thin;
                scrollbar-color: #cbd5e1 #f8fafc;
            }

            .mapa02-table-wrapper::-webkit-scrollbar {
                height: 4px;
            }
            .mapa02-table-wrapper::-webkit-scrollbar-thumb {
                background: #cbd5e1;
                border-radius: 4px;
            }

            .mapa02-table {
                display: table !important;
                table-layout: fixed !important;
                width: 560px !important;
                min-width: 560px !important;
                max-width: 560px !important;
                font-size: 11px !important;
                line-height: 1.3 !important;
            }

            .mapa02-th-elemen,
            .mapa02-td-elemen {
                width: 110px !important;
                min-width: 110px !important;
                max-width: 110px !important;
                padding: 0.4rem 0.35rem !important;
                font-size: 10px !important;
                word-break: break-word !important;
                overflow-wrap: break-word !important;
                white-space: normal !important;
            }

            .mapa02-th-kuk,
            .mapa02-td-kuk {
                width: 170px !important;
                min-width: 170px !important;
                max-width: 170px !important;
                padding: 0.4rem 0.35rem !important;
                font-size: 10px !important;
                word-break: break-word !important;
                overflow-wrap: break-word !important;
                white-space: normal !important;
            }

            /* 8 Kolom Instrumen: masing-masing 35px (8 x 35 = 280px) */
            /* Total: 110 + 170 + 280 = 560px */
            .mapa02-th-ia,
            .mapa02-td-ia {
                width: 35px !important;
                min-width: 35px !important;
                max-width: 35px !important;
                padding: 0.35rem 0.1rem !important;
                text-align: center !important;
            }

            .mapa02-cb {
                width: 1.15rem !important;
                height: 1.15rem !important;
                margin: 0 auto;
                display: block;
                cursor: pointer;
            }

            .mapa02-elem-desc {
                display: -webkit-box;
                -webkit-line-clamp: 2;
                -webkit-box-orient: vertical;
                overflow: hidden;
            }
        }
    </style>
@endpush

@section('konten')
@php
    $isAsesi = auth()->check() && auth()->user()->peran === 'asesi';
    $isAdmin = auth()->check() && in_array(auth()->user()->peran, ['admin', 'superadmin']);
    $isMasterMode = !empty($isMasterMode) || (isset($pendaftaran) && (empty($pendaftaran->id) || $pendaftaran->id === 0));

    // Cek apakah MAPA 02 ini dibuat oleh Admin atau sedang dibuat/dikelola oleh Admin
    $isSignedByAdmin = false;
    if ($isMasterMode) {
        if ($isAdmin) {
            $isSignedByAdmin = true;
        } elseif ($mapa02 && $mapa02->asesor && in_array($mapa02->asesor->peran, ['admin', 'superadmin'])) {
            $isSignedByAdmin = true;
        }
    }

    if ($isMasterMode && $isSignedByAdmin) {
        $pengesahUser = ($isAdmin && auth()->check()) 
            ? auth()->user() 
            : (($mapa02 && $mapa02->asesor) ? $mapa02->asesor : auth()->user());
        $pengesahNama = $pengesahUser->nama_lengkap ?? 'Administrator LSP';
        $asesorMet = $pengesahUser->nomor_registrasi ?? '-';
        $profileTtd = null;
    } else {
        // Mode Asesor (tetap gunakan data Asesor, jangan diubah)
        $pengesahUser = $pendaftaran->asesor ?? auth()->user();
        $pengesahNama = $pengesahUser->nama_lengkap ?? auth()->user()->nama_lengkap;
        $asesorMet = $pengesahUser->nomor_registrasi ?? auth()->user()->nomor_registrasi ?? 'MET.000.001234';
        $profileTtd = $pendaftaran->tanda_tangan_asesor ?? null;
    }

    $savedPeta = $mapa02->matriks_peta ?? [];
    $isConfirmed = ($mapa02->status_mapa ?? '') === 'selesai';
    $isConfigured = !empty($mapa02->exists) && $isConfirmed;
    $displayTtd = ($isConfirmed && !empty($mapa02->tanda_tangan_asesor)) ? $mapa02->tanda_tangan_asesor : null;
@endphp

<div class="max-w-6xl mx-auto px-3 sm:px-6 lg:px-8 py-3.5 space-y-4 mapa02-container" x-data="mapa02App()" x-cloak>

    <!-- =========================================================================
         TOP BREADCRUMB & ACTION BAR
         ========================================================================= -->
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-2 pb-1 no-print">
        <div class="flex items-center gap-2 text-xs text-slate-500">
            <a href="{{ route('asesor.dashboard') }}" class="hover:text-blue-600 font-medium">Dashboard</a>
            <span>/</span>
            <a href="{{ route('asesor.mapa') }}" class="hover:text-blue-600 font-medium">Perencanaan MAPA</a>
            <span>/</span>
            <span class="text-slate-800 font-bold">FR.MAPA.02</span>
        </div>

        <div class="flex items-center gap-2 mapa02-top-actions">
            @if(!$isAsesi && $isConfigured)
                <button type="button" @click="toggleEditMode()" 
                        class="inline-flex items-center px-3 py-1.5 rounded-lg border text-xs font-bold transition-colors"
                        :class="isEditMode ? 'bg-amber-500 hover:bg-amber-600 text-white border-amber-600' : 'bg-indigo-600 hover:bg-indigo-700 text-white border-indigo-700'">
                    <span x-text="isEditMode ? 'Kunci / Batal Edit' : 'Edit Formulir'"></span>
                </button>
            @endif
            <a href="{{ (url()->previous() && url()->previous() !== url()->current()) ? url()->previous() : (!empty($isMasterMode) ? route('asesor.mapa', ['skema_id' => $pendaftaran->skema_id]) : route('asesor.daftar-peserta')) }}" 
               onclick="if (document.referrer && document.referrer !== window.location.href) { window.location.href = document.referrer; return false; } else if (window.history.length > 1) { window.history.back(); return false; }"
               class="inline-flex items-center px-3 py-1.5 rounded-lg border border-slate-200 bg-white text-xs font-semibold text-slate-700 hover:bg-slate-50 transition-colors cursor-pointer">
                <span>&larr; Kembali</span>
            </a>
            <button type="button" onclick="window.print()" class="inline-flex items-center px-3 py-1.5 rounded-lg border border-slate-200 bg-white text-xs font-semibold text-slate-700 hover:bg-slate-50 transition-colors">
                <span>Cetak Dokumen</span>
            </button>
        </div>
    </div>

    <!-- =========================================================================
         HEADER CARD: FR.MAPA.02 PETA INSTRUMEN ASESMEN
         ========================================================================= -->
    <div class="bg-white rounded-2xl border border-slate-200/90 shadow-2xs p-4 sm:p-5 space-y-3">
        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3 border-b border-slate-100 pb-3">
            <div class="flex items-center gap-3">
                <div class="w-10 h-10 rounded-xl bg-indigo-50 text-indigo-700 border border-indigo-100 flex items-center justify-center font-black text-xs shrink-0">
                    MAPA.02
                </div>
                <div>
                    <h1 class="text-base sm:text-lg font-bold text-slate-900 leading-tight">
                        FR.MAPA.02 &bull; Peta Instrumen Asesmen
                    </h1>
                    <p class="text-xs text-slate-500">
                        Pemetaan keselarasan metode dan instrumen asesmen terhadap Unit Kompetensi, Elemen, dan KUK.
                    </p>
                </div>
            </div>

            <!-- Status Pill Header -->
            <div class="flex items-center gap-2">
                @if($isConfirmed)
                    <span class="px-3 py-1 rounded-full bg-emerald-50 text-emerald-700 border border-emerald-200 text-xs font-bold flex items-center gap-1.5 shadow-2xs">
                        <span class="w-2 h-2 rounded-full bg-emerald-500"></span>
                        CONFIRMED &bull; {{ !empty($isMasterMode) ? 'Master Peta Terkonfirmasi & Aktif' : 'Terkonfirmasi & Siap Digunakan' }}
                    </span>
                @else
                    <span class="px-3 py-1 rounded-full bg-amber-50 text-amber-700 border border-amber-200 text-xs font-bold flex items-center gap-1.5 shadow-2xs">
                        <span class="w-2 h-2 rounded-full bg-amber-500"></span>
                        DRAFT &bull; {{ !empty($isMasterMode) ? 'Master Peta Perlu Konfirmasi Asesor' : 'Perlu Konfirmasi Asesor' }}
                    </span>
                @endif
            </div>
        </div>

        <!-- Metadata Grid -->
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-2.5 pt-1 text-xs mapa02-meta-grid">
            <div class="bg-slate-50 border border-slate-100 rounded-xl p-2.5 space-y-0.5">
                <span class="text-[10px] font-bold uppercase tracking-wider text-slate-400">Skema Sertifikasi</span>
                <div class="font-bold text-slate-800 truncate" title="{{ $pendaftaran->skema->nama_skema ?? '-' }}">
                    {{ $pendaftaran->skema->nama_skema ?? '-' }}
                </div>
                <div class="text-[11px] text-slate-500 font-mono">{{ $pendaftaran->skema->kode_skema ?? '-' }}</div>
            </div>

            <div class="bg-slate-50 border border-slate-100 rounded-xl p-2.5 space-y-0.5">
                <span class="text-[10px] font-bold uppercase tracking-wider text-slate-400">{{ !empty($isMasterMode) ? 'Cakupan Template' : 'Asesi / Kandidat' }}</span>
                <div class="font-bold text-slate-800 truncate" title="{{ !empty($isMasterMode) ? 'Template Baku Seluruh Asesi' : ($pendaftaran->asesi->nama_lengkap ?? '-') }}">
                    {{ !empty($isMasterMode) ? 'Template Baku (Semua Asesi)' : ($pendaftaran->asesi->nama_lengkap ?? '-') }}
                </div>
                <div class="text-[11px] {{ !empty($isMasterMode) ? 'text-emerald-600 font-semibold' : 'text-slate-500' }}">
                    {{ !empty($isMasterMode) ? 'Acuan Otomatis Skema' : 'Reg: ' . $pendaftaran->nomor_pendaftaran }}
                </div>
            </div>

            <div class="bg-slate-50 border border-slate-100 rounded-xl p-2.5 space-y-0.5">
                <span class="text-[10px] font-bold uppercase tracking-wider text-slate-400">
                    {{ ($isMasterMode && $isSignedByAdmin) ? 'Penyusun / Validator' : 'Asesor Penguji' }}
                </span>
                <div class="font-bold text-slate-800 truncate" title="{{ $pengesahNama }}">
                    {{ $pengesahNama }}
                </div>
                <div class="text-[11px] {{ ($isMasterMode && $isSignedByAdmin) ? 'text-emerald-600 font-semibold' : 'text-slate-500' }}">
                    {{ ($isMasterMode && $isSignedByAdmin) ? 'Administrator LSP' : ('No. Reg: ' . $asesorMet) }}
                </div>
            </div>

            <div class="bg-slate-50 border border-slate-100 rounded-xl p-2.5 space-y-0.5">
                <span class="text-[10px] font-bold uppercase tracking-wider text-slate-400">Jadwal & Lokasi TUK</span>
                <div class="font-bold text-slate-800">
                    {{ $pendaftaran->jadwal ? date('d/m/Y', strtotime($pendaftaran->jadwal->tanggal_uji)) : 'Sesuai Jadwal' }}
                </div>
                <div class="text-[11px] text-slate-500 truncate" title="{{ $pendaftaran->jadwal->nama_tuk ?? 'TUK LSP' }}">
                    {{ $pendaftaran->jadwal->nama_tuk ?? 'TUK SMKN 1 Gunungputri' }}
                </div>
            </div>
        </div>
    </div>

    <!-- =========================================================================
         LEGEND INSTRUMEN (COMPACT INLINE)
         ========================================================================= -->
    <div class="mapa02-legend bg-slate-50 rounded-lg border border-slate-200/80 p-2 sm:p-2.5 text-[10.5px] text-slate-600 flex flex-wrap items-center gap-x-3.5 gap-y-1">
        <span class="font-bold text-slate-700 uppercase tracking-wider text-[9.5px] flex items-center gap-1.5">
            <span class="w-1.5 h-1.5 rounded-full bg-blue-600"></span> Legenda:
        </span>
        <span class="inline-flex items-center gap-1"><strong>CLO</strong>: Observasi</span>
        <span class="inline-flex items-center gap-1"><strong>DPT</strong>: Praktik</span>
        <span class="inline-flex items-center gap-1"><strong>PMO</strong>: Pertanyaan Obs.</span>
        <span class="inline-flex items-center gap-1"><strong>DPE</strong>: Tulis PG & Esai</span>
        <span class="inline-flex items-center gap-1"><strong>DPL</strong>: Lisan</span>
        <span class="inline-flex items-center gap-1"><strong>VP</strong>: Portofolio</span>
        <span class="inline-flex items-center gap-1"><strong>PW</strong>: Wawancara</span>
        <span class="inline-flex items-center gap-1"><strong>CRP</strong>: Reviu Produk</span>
    </div>

    <!-- BANNER MODE TAMPILAN / EDIT -->
    @if(!$isAsesi)
    <div x-show="!isEditMode" class="bg-slate-50 border border-slate-200 rounded-xl p-3 flex items-center justify-between gap-3 text-xs text-slate-600 shadow-2xs no-print">
        <div>
            <strong class="text-slate-800 font-bold block text-xs">Mode Tampilan (Terkunci)</strong>
            <span class="text-[11px]">Peta instrumen telah dikonfigurasi dan ditampilkan dalam mode hanya lihat. Klik tombol <strong>Edit Formulir</strong> di atas jika ingin mengubah matriks instrumen.</span>
        </div>
    </div>

    <div x-show="isEditMode && {{ $isConfigured ? 'true' : 'false' }}" class="bg-amber-50 border border-amber-200 rounded-xl p-3 flex items-center gap-3 text-xs text-amber-800 shadow-2xs no-print">
        <div>
            <strong class="text-amber-900 font-bold block text-xs">Mode Edit Aktif</strong>
            <span class="text-[11px]">Anda sekarang dapat mencentang atau menghapus instrumen asesmen pada matriks di bawah, lalu klik Simpan Draft atau Konfirmasi di bawah.</span>
        </div>
    </div>
    @endif

    <!-- =========================================================================
         MAIN FORM CONTAINER
         ========================================================================= -->
    <form id="formMapa02" action="{{ !empty($isMasterMode) ? route('asesor.skema.mapa-02.simpan', $pendaftaran->skema_id) : route('asesor.mapa-02.simpan', $pendaftaran->id) }}" method="POST" @submit.prevent="submitForm($event)" class="space-y-4">
        @csrf
        <input type="hidden" name="aksi" id="inputAksi" value="draft">

        <fieldset :disabled="!isEditMode" :class="!isEditMode ? 'opacity-95' : ''" class="space-y-4 border-0 p-0 m-0">

        <!-- LOOP UNIT KOMPETENSI -->
        @forelse($pendaftaran->skema->unitKompetensi as $indexUnit => $unit)
            <div class="bg-white rounded-xl border border-slate-200/90 shadow-2xs overflow-hidden page-break" x-data="{ expanded: true }">
                
                <!-- Unit Header Bar -->
                <div class="px-4 sm:px-5 py-2.5 bg-slate-50/90 border-b border-slate-200/80 flex flex-col sm:flex-row sm:items-center justify-between gap-1.5">
                    <div class="flex items-center gap-2 min-w-0">
                        <button type="button" @click="expanded = !expanded" class="text-[11px] font-semibold text-slate-500 hover:text-slate-800 px-1.5 py-0.5 rounded bg-slate-100 hover:bg-slate-200 transition-colors no-print">
                            <span x-text="expanded ? 'Tutup' : 'Buka'"></span>
                        </button>
                        <div class="min-w-0">
                            <span class="text-[9.5px] font-bold text-blue-700 uppercase tracking-wider block">
                                Unit {{ $indexUnit + 1 }} &bull; {{ $unit->kode_unit }}
                            </span>
                            <h2 class="font-bold text-xs sm:text-sm text-slate-900 truncate" title="{{ $unit->judul_unit }}">
                                {{ $unit->judul_unit }}
                            </h2>
                        </div>
                    </div>

                    <!-- Quick Action Buttons Per Unit -->
                    <div class="mapa02-quick-actions flex items-center gap-1.5 no-print shrink-0 self-end sm:self-center" x-show="isEditMode">
                        <button type="button" @click="toggleInstrumentForUnit({{ $unit->id }}, 'clo')" class="px-2 py-0.5 bg-white hover:bg-slate-100 border border-slate-200 rounded text-[9.5px] font-semibold text-slate-700 transition">
                            + Observasi (CLO)
                        </button>
                        <button type="button" @click="toggleInstrumentForUnit({{ $unit->id }}, 'dpt')" class="px-2 py-0.5 bg-white hover:bg-slate-100 border border-slate-200 rounded text-[9.5px] font-semibold text-slate-700 transition">
                            + Praktik (DPT)
                        </button>
                        <button type="button" @click="toggleInstrumentForUnit({{ $unit->id }}, 'dpe')" class="px-2 py-0.5 bg-white hover:bg-slate-100 border border-slate-200 rounded text-[9.5px] font-semibold text-slate-700 transition">
                            + Tulis (DPE)
                        </button>
                    </div>
                </div>

                <!-- Matriks Tabel Unit -->
                <div class="overflow-x-auto mapa02-table-wrapper" x-show="expanded">
                    <!-- Indikator Geser untuk Layar Mobile (Hanya tampil di mobile) -->
                    <div class="sm:hidden flex items-center justify-between px-3 py-1.5 bg-blue-50/90 border-b border-blue-100 text-[10.5px] text-blue-800 font-medium select-none">
                        <span class="flex items-center gap-1.5">
                            <svg class="w-3.5 h-3.5 text-blue-600 animate-pulse shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M17 8l4 4m0 0l-4 4m4-4H3" />
                            </svg>
                            <span>Geser tabel ke kanan untuk instrumen</span>
                        </span>
                        <span class="bg-blue-100 text-blue-700 px-1.5 py-0.5 rounded text-[9px] font-bold shrink-0 uppercase">8 Kolom IA</span>
                    </div>

                    <table class="w-full text-left border-collapse text-xs mapa02-table">
                        <thead>
                            <tr class="bg-slate-50/75 border-b border-slate-200 text-[10.5px] font-bold text-slate-600 uppercase tracking-wider">
                                <th class="py-1.5 px-2.5 w-[26%] min-w-[170px] mapa02-th-elemen">Elemen Kompetensi</th>
                                <th class="py-1.5 px-2.5 min-w-[220px] mapa02-th-kuk">Kriteria Unjuk Kerja (KUK)</th>
                                <th class="py-1.5 px-1 text-center w-9 min-w-[36px] max-w-[42px] mapa02-th-ia" title="IA.01: Ceklis Observasi">CLO</th>
                                <th class="py-1.5 px-1 text-center w-9 min-w-[36px] max-w-[42px] mapa02-th-ia" title="IA.02: Tugas Praktik">DPT</th>
                                <th class="py-1.5 px-1 text-center w-9 min-w-[36px] max-w-[42px] mapa02-th-ia" title="IA.03: Pertanyaan Pendukung Observasi">PMO</th>
                                <th class="py-1.5 px-1 text-center w-9 min-w-[36px] max-w-[42px] mapa02-th-ia" title="IA.05/06: Uji Tertulis CBT & Esai">DPE</th>
                                <th class="py-1.5 px-1 text-center w-9 min-w-[36px] max-w-[42px] mapa02-th-ia" title="IA.07: Pertanyaan Lisan">DPL</th>
                                <th class="py-1.5 px-1 text-center w-9 min-w-[36px] max-w-[42px] mapa02-th-ia" title="IA.08: Verifikasi Portofolio">VP</th>
                                <th class="py-1.5 px-1 text-center w-9 min-w-[36px] max-w-[42px] mapa02-th-ia" title="IA.09: Pertanyaan Wawancara">PW</th>
                                <th class="py-1.5 px-1 text-center w-9 min-w-[36px] max-w-[42px] mapa02-th-ia" title="IA.11: Ceklis Reviu Produk">CRP</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100 text-slate-800">
                            @forelse($unit->elemenKompetensi as $elem)
                                @php
                                    $kukList = $elem->kriteriaUnjukKerja;
                                    $totalKuk = count($kukList);
                                @endphp

                                @if($totalKuk > 0)
                                    @foreach($kukList as $kIndex => $kuk)
                                        @php
                                            $kKey = $kuk->id;
                                            $savedItem = $savedPeta[$unit->id][$elem->id][$kKey] ?? [];
                                            $clo = !empty($savedItem['clo']);
                                            $dpt = !empty($savedItem['dpt']);
                                            $pmo = !empty($savedItem['pmo']);
                                            $dpe = !empty($savedItem['dpe']);
                                            $dpl = !empty($savedItem['dpl']);
                                            $vp  = !empty($savedItem['vp']);
                                            $pw  = !empty($savedItem['pw']);
                                            $crp = !empty($savedItem['crp']);
                                        @endphp
                                        <tr class="hover:bg-slate-50/60 transition-colors">
                                            @if($kIndex === 0)
                                                <td class="py-1.5 px-2.5 align-top bg-slate-50/40 border-r border-slate-100 font-medium mapa02-td-elemen text-[11px]" rowspan="{{ $totalKuk }}">
                                                    <span class="font-bold text-slate-900 block leading-tight">
                                                        {{ $elem->nomor_elemen }}. {{ $elem->nama_elemen }}
                                                    </span>
                                                    @if(!empty($elem->pertanyaan_elemen))
                                                        <span class="text-[9.5px] text-slate-500 italic block mt-0.5 leading-snug mapa02-elem-desc">"{{ $elem->pertanyaan_elemen }}"</span>
                                                    @endif
                                                </td>
                                            @endif
                                            <td class="py-1.5 px-2.5 align-top border-r border-slate-100 mapa02-td-kuk text-[11px]">
                                                <div class="flex items-start gap-1.5 leading-snug">
                                                    <span class="font-bold text-blue-700 shrink-0">{{ $kuk->nomor_kuk }}</span>
                                                    <span class="text-slate-700">{{ $kuk->pernyataan_kuk }}</span>
                                                </div>
                                            </td>
                                            <td class="py-1 px-0.5 text-center align-middle mapa02-td-ia">
                                                <input type="checkbox" name="matriks_peta[{{ $unit->id }}][{{ $elem->id }}][{{ $kKey }}][clo]" value="1" {{ $clo ? 'checked' : '' }} class="mapa02-cb unit-{{ $unit->id }}-clo rounded-xs text-blue-600 focus:ring-0 cursor-pointer">
                                            </td>
                                            <td class="py-1 px-0.5 text-center align-middle mapa02-td-ia">
                                                <input type="checkbox" name="matriks_peta[{{ $unit->id }}][{{ $elem->id }}][{{ $kKey }}][dpt]" value="1" {{ $dpt ? 'checked' : '' }} class="mapa02-cb unit-{{ $unit->id }}-dpt rounded-xs text-blue-600 focus:ring-0 cursor-pointer">
                                            </td>
                                            <td class="py-1 px-0.5 text-center align-middle mapa02-td-ia">
                                                <input type="checkbox" name="matriks_peta[{{ $unit->id }}][{{ $elem->id }}][{{ $kKey }}][pmo]" value="1" {{ $pmo ? 'checked' : '' }} class="mapa02-cb unit-{{ $unit->id }}-pmo rounded-xs text-blue-600 focus:ring-0 cursor-pointer">
                                            </td>
                                            <td class="py-1 px-0.5 text-center align-middle mapa02-td-ia">
                                                <input type="checkbox" name="matriks_peta[{{ $unit->id }}][{{ $elem->id }}][{{ $kKey }}][dpe]" value="1" {{ $dpe ? 'checked' : '' }} class="mapa02-cb unit-{{ $unit->id }}-dpe rounded-xs text-blue-600 focus:ring-0 cursor-pointer">
                                            </td>
                                            <td class="py-1 px-0.5 text-center align-middle mapa02-td-ia">
                                                <input type="checkbox" name="matriks_peta[{{ $unit->id }}][{{ $elem->id }}][{{ $kKey }}][dpl]" value="1" {{ $dpl ? 'checked' : '' }} class="mapa02-cb unit-{{ $unit->id }}-dpl rounded-xs text-blue-600 focus:ring-0 cursor-pointer">
                                            </td>
                                            <td class="py-1 px-0.5 text-center align-middle mapa02-td-ia">
                                                <input type="checkbox" name="matriks_peta[{{ $unit->id }}][{{ $elem->id }}][{{ $kKey }}][vp]" value="1" {{ $vp ? 'checked' : '' }} class="mapa02-cb unit-{{ $unit->id }}-vp rounded-xs text-blue-600 focus:ring-0 cursor-pointer">
                                            </td>
                                            <td class="py-1 px-0.5 text-center align-middle mapa02-td-ia">
                                                <input type="checkbox" name="matriks_peta[{{ $unit->id }}][{{ $elem->id }}][{{ $kKey }}][pw]" value="1" {{ $pw ? 'checked' : '' }} class="mapa02-cb unit-{{ $unit->id }}-pw rounded-xs text-blue-600 focus:ring-0 cursor-pointer">
                                            </td>
                                            <td class="py-1 px-0.5 text-center align-middle mapa02-td-ia">
                                                <input type="checkbox" name="matriks_peta[{{ $unit->id }}][{{ $elem->id }}][{{ $kKey }}][crp]" value="1" {{ $crp ? 'checked' : '' }} class="mapa02-cb unit-{{ $unit->id }}-crp rounded-xs text-blue-600 focus:ring-0 cursor-pointer">
                                            </td>
                                        </tr>
                                    @endforeach
                                @else
                                    @php
                                        $savedItem = $savedPeta[$unit->id][$elem->id]['elem_only'] ?? [];
                                    @endphp
                                    <tr class="hover:bg-slate-50/60 transition-colors">
                                        <td class="py-1.5 px-2.5 align-top bg-slate-50/40 border-r border-slate-100 font-medium mapa02-td-elemen text-[11px]">
                                            <span class="font-bold text-slate-900 block leading-tight">
                                                {{ $elem->nomor_elemen }}. {{ $elem->nama_elemen }}
                                            </span>
                                        </td>
                                        <td class="py-1.5 px-2.5 align-top border-r border-slate-100 italic text-slate-400 mapa02-td-kuk text-[11px]">
                                            KUK belum diinput untuk elemen ini.
                                        </td>
                                        <td class="py-1 px-0.5 text-center align-middle mapa02-td-ia">
                                            <input type="checkbox" name="matriks_peta[{{ $unit->id }}][{{ $elem->id }}][elem_only][clo]" value="1" {{ !empty($savedItem['clo']) ? 'checked' : '' }} class="mapa02-cb unit-{{ $unit->id }}-clo rounded-xs text-blue-600 focus:ring-0 cursor-pointer">
                                        </td>
                                        <td class="py-1 px-0.5 text-center align-middle mapa02-td-ia">
                                            <input type="checkbox" name="matriks_peta[{{ $unit->id }}][{{ $elem->id }}][elem_only][dpt]" value="1" {{ !empty($savedItem['dpt']) ? 'checked' : '' }} class="mapa02-cb unit-{{ $unit->id }}-dpt rounded-xs text-blue-600 focus:ring-0 cursor-pointer">
                                        </td>
                                        <td class="py-1 px-0.5 text-center align-middle mapa02-td-ia">
                                            <input type="checkbox" name="matriks_peta[{{ $unit->id }}][{{ $elem->id }}][elem_only][pmo]" value="1" {{ !empty($savedItem['pmo']) ? 'checked' : '' }} class="mapa02-cb unit-{{ $unit->id }}-pmo rounded-xs text-blue-600 focus:ring-0 cursor-pointer">
                                        </td>
                                        <td class="py-1 px-0.5 text-center align-middle mapa02-td-ia">
                                            <input type="checkbox" name="matriks_peta[{{ $unit->id }}][{{ $elem->id }}][elem_only][dpe]" value="1" {{ !empty($savedItem['dpe']) ? 'checked' : '' }} class="mapa02-cb unit-{{ $unit->id }}-dpe rounded-xs text-blue-600 focus:ring-0 cursor-pointer">
                                        </td>
                                        <td class="py-1 px-0.5 text-center align-middle mapa02-td-ia">
                                            <input type="checkbox" name="matriks_peta[{{ $unit->id }}][{{ $elem->id }}][elem_only][dpl]" value="1" {{ !empty($savedItem['dpl']) ? 'checked' : '' }} class="mapa02-cb unit-{{ $unit->id }}-dpl rounded-xs text-blue-600 focus:ring-0 cursor-pointer">
                                        </td>
                                        <td class="py-1 px-0.5 text-center align-middle mapa02-td-ia">
                                            <input type="checkbox" name="matriks_peta[{{ $unit->id }}][{{ $elem->id }}][elem_only][vp]" value="1" {{ !empty($savedItem['vp']) ? 'checked' : '' }} class="mapa02-cb unit-{{ $unit->id }}-vp rounded-xs text-blue-600 focus:ring-0 cursor-pointer">
                                        </td>
                                        <td class="py-1 px-0.5 text-center align-middle mapa02-td-ia">
                                            <input type="checkbox" name="matriks_peta[{{ $unit->id }}][{{ $elem->id }}][elem_only][pw]" value="1" {{ !empty($savedItem['pw']) ? 'checked' : '' }} class="mapa02-cb unit-{{ $unit->id }}-pw rounded-xs text-blue-600 focus:ring-0 cursor-pointer">
                                        </td>
                                        <td class="py-1 px-0.5 text-center align-middle mapa02-td-ia">
                                            <input type="checkbox" name="matriks_peta[{{ $unit->id }}][{{ $elem->id }}][elem_only][crp]" value="1" {{ !empty($savedItem['crp']) ? 'checked' : '' }} class="mapa02-cb unit-{{ $unit->id }}-crp rounded-xs text-blue-600 focus:ring-0 cursor-pointer">
                                        </td>
                                    </tr>
                                @endif
                            @empty
                                <tr>
                                    <td colspan="10" class="py-6 text-center text-slate-400 italic">Belum ada elemen kompetensi pada unit ini.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        @empty
            <div class="bg-white rounded-2xl border border-slate-200 p-8 text-center text-slate-400 italic">
                Tidak ada data Unit Kompetensi untuk skema sertifikasi ini.
            </div>
        @endforelse

        <!-- =========================================================================
             CATATAN ASESOR & PENGESAHAN TANDA TANGAN
             ========================================================================= -->
        <div class="grid grid-cols-1 md:grid-cols-2 gap-3 sm:gap-4">
            
            <!-- KOLOM 1: CATATAN ASESOR -->
            <div class="bg-white rounded-xl border border-slate-200/90 shadow-2xs p-3.5 sm:p-4 space-y-2 flex flex-col justify-between">
                <div>
                    <h2 class="font-bold text-xs sm:text-sm text-slate-800 border-b border-slate-100 pb-1.5 flex items-center justify-between">
                        <span>Catatan Perencanaan Asesor</span>
                        <span class="text-[10px] text-slate-400 font-normal">Opsional</span>
                    </h2>
                    <div class="space-y-1 mt-2">
                        <label class="text-[11px] font-medium text-slate-600 block">Catatan metodologi / modifikasi rencana instrumen asesmen:</label>
                        <textarea name="catatan_asesor" rows="2" class="w-full bg-slate-50 border border-slate-200 rounded-lg p-2 text-xs text-slate-800 focus:bg-white focus:ring-1 focus:ring-blue-500 transition-colors" placeholder="Tuliskan catatan khusus terkait pemilihan metode dan instrumen asesmen...">{{ old('catatan_asesor', $mapa02->catatan_asesor ?? '') }}</textarea>
                    </div>
                </div>
                <div class="text-[10px] text-slate-400 italic pt-1">
                    * Catatan khusus rencana pelaksanaan pemetaan instrumen.
                </div>
            </div>

            <!-- KOLOM 2: PENGESAHAN TANDA TANGAN ASESOR / ADMIN -->
            <div class="bg-white rounded-xl border border-slate-200/90 shadow-2xs p-3.5 sm:p-4 space-y-2.5">
                <div class="flex items-center justify-between border-b border-slate-100 pb-1.5">
                    <h2 class="font-bold text-xs sm:text-sm text-slate-800">
                        {{ ($isMasterMode && $isSignedByAdmin) ? 'Pengesahan Administrator LSP' : 'Pengesahan Asesor Penguji' }}
                    </h2>
                    @if($isConfirmed)
                        <span class="px-2 py-0.5 rounded-md bg-emerald-100 text-emerald-800 font-bold text-[10px]">
                            ✓ Telah Disahkan
                        </span>
                    @endif
                </div>

                <!-- Mode Canvas Signature (Compact Box) -->
                <div class="space-y-1.5">
                    @if($displayTtd)
                        <div x-show="!isEditMode" class="h-24 max-w-[280px] bg-slate-50 rounded-lg border border-slate-200 flex items-center justify-center p-1.5">
                            <img src="{{ Str::startsWith($displayTtd, 'data:') ? $displayTtd : asset($displayTtd) }}" alt="TTD {{ ($isMasterMode && $isSignedByAdmin) ? 'Admin' : 'Asesor' }}" class="max-h-20 object-contain">
                        </div>
                    @endif

                    <div x-show="isEditMode" class="space-y-1">
                        <div class="w-full max-w-[280px] border border-slate-300 rounded-lg bg-white relative overflow-hidden shadow-2xs">
                            <canvas id="canvasMapa02Asesor" class="w-full bg-white cursor-crosshair block" style="touch-action: none; height: 90px; width: 100%; display: block;"></canvas>
                        </div>
                        <input type="hidden" name="tanda_tangan_asesor" id="inputTtdAsesor" value="{{ $displayTtd ?? '' }}">
                        <div class="w-full max-w-[280px] flex items-center justify-between text-[10px] text-slate-400">
                            <span>Gunakan mouse / sentuh</span>
                            <button type="button" @click="clearCanvas()" class="text-rose-600 hover:text-rose-800 font-semibold cursor-pointer">
                                [ Bersihkan ]
                            </button>
                        </div>
                    </div>
                </div>

                <div class="text-[11px] text-slate-500 pt-0.5">
                    @if($isMasterMode && $isSignedByAdmin)
                        Administrator: <strong>{{ $pengesahNama }}</strong> (Administrator LSP)
                    @else
                        Asesor: <strong>{{ $pengesahNama }}</strong> (No. Reg: <strong>{{ $asesorMet }}</strong>)
                    @endif
                </div>
            </div>
        </div>
        </fieldset>

        <!-- =========================================================================
             ACTION FOOTER
         ========================================================================= -->
        <div class="bg-white rounded-2xl border border-slate-200/90 p-4 shadow-2xs flex flex-col sm:flex-row items-center justify-between gap-3 no-print">
            <a href="{{ (url()->previous() && url()->previous() !== url()->current()) ? url()->previous() : (!empty($isMasterMode) ? route('asesor.mapa', ['skema_id' => $pendaftaran->skema_id]) : route('asesor.daftar-peserta')) }}" 
               onclick="if (document.referrer && document.referrer !== window.location.href) { window.location.href = document.referrer; return false; } else if (window.history.length > 1) { window.history.back(); return false; }"
               class="w-full sm:w-auto px-4 py-2.5 rounded-xl border border-slate-200 hover:bg-slate-50 text-slate-700 text-xs font-semibold transition-colors text-center cursor-pointer">
                &larr; Kembali
            </a>

            <!-- State Keterangan saat locked -->
            <div x-show="!isEditMode" class="text-xs text-slate-500 flex items-center gap-2">
                <span class="w-1.5 h-1.5 rounded-full bg-indigo-500"></span>
                <span>Formulir dalam mode tampilan (hanya lihat). Klik <strong>Edit Formulir</strong> di atas jika ingin mengubah.</span>
            </div>

            <div class="flex items-center gap-2.5 w-full sm:w-auto justify-end flex-wrap" x-show="isEditMode">
                <!-- Tombol Simpan Draft -->
                <button type="button" @click="submitAsDraft()" :disabled="isSubmitting" class="w-full sm:w-auto px-5 py-2.5 rounded-xl border border-slate-300 bg-white hover:bg-slate-50 text-slate-700 text-xs font-bold shadow-2xs transition-colors flex items-center justify-center gap-1.5 cursor-pointer">
                    <span>Simpan Draft</span>
                </button>

                <!-- Tombol Konfirmasi & Sahkan -->
                <button type="button" @click="openConfirmModal()" :disabled="isSubmitting" class="w-full sm:w-auto px-6 py-2.5 rounded-xl bg-blue-600 hover:bg-blue-700 text-white text-xs font-bold shadow-xs transition-colors flex items-center justify-center gap-1.5 cursor-pointer">
                    <template x-if="!isSubmitting">
                        <span>Konfirmasi & Sahkan Rencana Asesmen</span>
                    </template>
                    <template x-if="isSubmitting">
                        <span>Memproses...</span>
                    </template>
                </button>
            </div>
        </div>
    </form>

    <!-- =========================================================================
         CONFIRMATION MODAL
         ========================================================================= -->
    <div x-show="showConfirmModal" class="fixed inset-0 z-50 overflow-y-auto" style="display: none;">
        <div class="fixed inset-0 bg-slate-900/60 backdrop-blur-xs transition-opacity" @click="showConfirmModal = false"></div>

        <div class="min-h-screen px-4 text-center flex items-center justify-center">
            <div class="inline-block bg-white rounded-2xl p-6 text-left overflow-hidden shadow-xl transform transition-all max-w-md w-full relative z-10 space-y-4 border border-slate-200" @click.stop>
                <div class="flex items-center gap-3 text-slate-900">
                    <div class="w-10 h-10 rounded-xl bg-blue-50 text-blue-600 border border-blue-100 flex items-center justify-center font-bold text-xs shrink-0">
                        MAPA.02
                    </div>
                    <div>
                        <h3 class="text-sm sm:text-base font-bold">Konfirmasi & Sahkan FR.MAPA.02?</h3>
                        <p class="text-xs text-slate-500">Peta instrumen ini akan menjadi acuan instrumen aktif pelaksanaan asesmen.</p>
                    </div>
                </div>

                <div class="bg-amber-50 border border-amber-200/80 rounded-xl p-3 text-xs text-amber-800 leading-relaxed">
                    <p class="font-bold mb-0.5">Perhatian:</p>
                    Setelah disahkan, instrumen FR.IA yang dicentang akan otomatis diaktifkan pada Ruang Ujian dan Lembar Penilaian Asesor Hari-H.
                </div>

                <div class="flex items-center justify-end gap-2 pt-2">
                    <button type="button" @click="showConfirmModal = false" class="px-4 py-2 rounded-xl border border-slate-200 text-xs font-semibold text-slate-700 hover:bg-slate-50 transition">
                        Batal
                    </button>
                    <button type="button" @click="submitAsConfirmed()" class="px-5 py-2 rounded-xl bg-blue-600 hover:bg-blue-700 text-white text-xs font-bold shadow-xs transition">
                        Ya, Sahkan Rencana Asesmen
                    </button>
                </div>
            </div>
        </div>
    </div>

</div>

<!-- Alpine.js & Signature Pad Component -->
<script>
    function mapa02App() {
        return {
            isEditMode: {{ (!$isAsesi && $isConfigured) ? 'false' : 'true' }},
            signatureMode: 'canvas',
            profileTtdData: '',
            canvasSignatureData: '',
            signaturePad: null,
            hasDrawn: false,
            isSubmitting: false,
            showConfirmModal: false,

            init() {
                this.$nextTick(() => {
                    this.initSignaturePad();
                });
                setTimeout(() => {
                    this.initSignaturePad();
                }, 300);
            },

            toggleEditMode() {
                this.isEditMode = !this.isEditMode;
                this.$nextTick(() => {
                    this.initSignaturePad();
                });
                setTimeout(() => {
                    this.initSignaturePad();
                }, 200);
            },

            initSignaturePad() {
                const canvas = document.getElementById('canvasMapa02Asesor');
                if (!canvas) return;

                const parent = canvas.parentElement;
                const width = (parent && parent.clientWidth > 50) ? parent.clientWidth : 280;
                const height = 90;

                canvas.width = width;
                canvas.height = height;

                const ctx = canvas.getContext('2d');
                ctx.lineWidth = 1.8;
                ctx.lineCap = 'round';
                ctx.lineJoin = 'round';
                ctx.strokeStyle = '#0f172a';

                let isDrawing = false;

                const getPos = (e) => {
                    const rect = canvas.getBoundingClientRect();
                    const clientX = e.touches ? e.touches[0].clientX : e.clientX;
                    const clientY = e.touches ? e.touches[0].clientY : e.clientY;
                    return {
                        x: (clientX - rect.left) * (canvas.width / rect.width),
                        y: (clientY - rect.top) * (canvas.height / rect.height)
                    };
                };

                const start = (e) => {
                    isDrawing = true;
                    this.hasDrawn = true;
                    const pos = getPos(e);
                    ctx.beginPath();
                    ctx.moveTo(pos.x, pos.y);
                    if (e.cancelable) e.preventDefault();
                };

                const draw = (e) => {
                    if (!isDrawing) return;
                    const pos = getPos(e);
                    ctx.lineTo(pos.x, pos.y);
                    ctx.stroke();
                    if (e.cancelable) e.preventDefault();
                };

                const stop = () => {
                    if (!isDrawing) return;
                    isDrawing = false;
                    ctx.closePath();
                    const dataUrl = canvas.toDataURL('image/png');
                    this.canvasSignatureData = dataUrl;
                    const hiddenInput = document.getElementById('inputTtdAsesor');
                    if (hiddenInput) {
                        hiddenInput.value = dataUrl;
                    }
                };

                // Remove existing listeners if any
                if (canvas._sigStart) {
                    canvas.removeEventListener('mousedown', canvas._sigStart);
                    canvas.removeEventListener('mousemove', canvas._sigDraw);
                    window.removeEventListener('mouseup', canvas._sigStop);
                    canvas.removeEventListener('touchstart', canvas._sigStart);
                    canvas.removeEventListener('touchmove', canvas._sigDraw);
                    window.removeEventListener('touchend', canvas._sigStop);
                }

                canvas._sigStart = start;
                canvas._sigDraw = draw;
                canvas._sigStop = stop;

                canvas.addEventListener('mousedown', start);
                canvas.addEventListener('mousemove', draw);
                window.addEventListener('mouseup', stop);

                canvas.addEventListener('touchstart', start, { passive: false });
                canvas.addEventListener('touchmove', draw, { passive: false });
                window.addEventListener('touchend', stop);

                // Optional SignaturePad CDN wrapper if loaded
                if (typeof SignaturePad !== 'undefined') {
                    try {
                        this.signaturePad = new SignaturePad(canvas, {
                            backgroundColor: 'rgba(255, 255, 255, 0)',
                            penColor: 'rgb(15, 23, 42)',
                            minWidth: 1.0,
                            maxWidth: 2.0
                        });
                    } catch (err) {
                        // Native canvas will handle it seamlessly
                    }
                }
            },

            clearCanvas() {
                if (this.signaturePad) {
                    try { this.signaturePad.clear(); } catch(e){}
                }
                const canvas = document.getElementById('canvasMapa02Asesor');
                if (canvas) {
                    const ctx = canvas.getContext('2d');
                    ctx.clearRect(0, 0, canvas.width, canvas.height);
                }
                this.canvasSignatureData = '';
                this.hasDrawn = false;
                const hiddenInput = document.getElementById('inputTtdAsesor');
                if (hiddenInput) {
                    hiddenInput.value = '';
                }
            },

            toggleInstrumentForUnit(unitId, instrumentKey) {
                const checkboxes = document.querySelectorAll('.unit-' + unitId + '-' + instrumentKey);
                if (checkboxes.length === 0) return;
                
                const allChecked = Array.from(checkboxes).every(cb => cb.checked);
                checkboxes.forEach(cb => {
                    cb.checked = !allChecked;
                });
            },

            prepareSignature() {
                const canvas = document.getElementById('canvasMapa02Asesor');
                if (this.signaturePad && !this.signaturePad.isEmpty()) {
                    this.canvasSignatureData = this.signaturePad.toDataURL('image/png');
                } else if (canvas && this.hasDrawn) {
                    this.canvasSignatureData = canvas.toDataURL('image/png');
                }
                const hiddenInput = document.getElementById('inputTtdAsesor');
                if (hiddenInput && this.canvasSignatureData) {
                    hiddenInput.value = this.canvasSignatureData;
                }
            },

            submitAsDraft() {
                this.prepareSignature();
                document.getElementById('inputAksi').value = 'draft';
                this.isSubmitting = true;
                document.getElementById('formMapa02').submit();
            },

            openConfirmModal() {
                this.prepareSignature();
                this.showConfirmModal = true;
            },

            submitAsConfirmed() {
                this.showConfirmModal = false;
                this.prepareSignature();
                document.getElementById('inputAksi').value = 'konfirmasi';
                this.isSubmitting = true;
                document.getElementById('formMapa02').submit();
            },

            submitForm(event) {
                this.submitAsDraft();
            }
        };
    }
</script>
@endsection
