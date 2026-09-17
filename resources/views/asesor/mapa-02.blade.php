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
    </style>
@endpush

@section('konten')
@php
    $isAsesi = auth()->check() && auth()->user()->peran === 'asesi';
    $isMasterMode = !empty($isMasterMode) || (isset($pendaftaran) && (empty($pendaftaran->id) || $pendaftaran->id === 0));
    $asesorNama = $pendaftaran->asesor->nama_lengkap ?? auth()->user()->nama_lengkap;
    $asesorMet = $pendaftaran->asesor->nomor_registrasi ?? auth()->user()->nomor_registrasi ?? 'MET.000.001234';
    $profileTtd = auth()->user()->tanda_tangan ?: $pendaftaran->tanda_tangan_asesor;
    $asesorTtd = $mapa02->tanda_tangan_asesor ?? $profileTtd;
    $savedPeta = $mapa02->matriks_peta ?? [];
    $isConfirmed = ($mapa02->status_mapa ?? '') === 'selesai';
    $isConfigured = !empty($mapa02->exists) && (!empty($savedPeta) || $isConfirmed);
@endphp

<div class="max-w-6xl mx-auto px-2 sm:px-4 py-3 space-y-4" x-data="mapa02App()" x-cloak>

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

        <div class="flex items-center gap-2">
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
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-2.5 pt-1 text-xs">
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
                <span class="text-[10px] font-bold uppercase tracking-wider text-slate-400">Asesor Penguji</span>
                <div class="font-bold text-slate-800 truncate" title="{{ $asesorNama }}">
                    {{ $asesorNama }}
                </div>
                <div class="text-[11px] text-slate-500">No. Reg: {{ $asesorMet }}</div>
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
    <div class="bg-slate-50 rounded-xl border border-slate-200/80 p-3 text-[11px] text-slate-600 flex flex-wrap items-center gap-x-4 gap-y-1.5">
        <span class="font-bold text-slate-700 uppercase tracking-wider text-[10px] flex items-center gap-1.5">
            <span class="w-1.5 h-1.5 rounded-full bg-blue-600"></span> Legenda Instrumen:
        </span>
        <span class="inline-flex items-center gap-1"><strong>CLO</strong>: IA.01 Ceklis Observasi</span>
        <span class="inline-flex items-center gap-1"><strong>DPT</strong>: IA.02 Tugas Praktik</span>
        <span class="inline-flex items-center gap-1"><strong>PMO</strong>: IA.03 Pertanyaan Observasi</span>
        <span class="inline-flex items-center gap-1"><strong>DPE</strong>: IA.05/06 Uji Tulis PG & Esai</span>
        <span class="inline-flex items-center gap-1"><strong>DPL</strong>: IA.07 Pertanyaan Lisan</span>
        <span class="inline-flex items-center gap-1"><strong>VP</strong>: IA.08 Verifikasi Portofolio</span>
        <span class="inline-flex items-center gap-1"><strong>PW</strong>: IA.09 Pertanyaan Wawancara</span>
        <span class="inline-flex items-center gap-1"><strong>CRP</strong>: IA.11 Reviu Produk</span>
    </div>

    <!-- BANNER MODE TAMPILAN / EDIT -->
    @if(!$isAsesi)
    <div x-show="!isEditMode" class="bg-slate-50 border border-slate-200 rounded-2xl p-4 flex items-center justify-between gap-3 text-xs text-slate-600 shadow-2xs no-print">
        <div>
            <strong class="text-slate-800 font-bold block text-sm">Mode Tampilan (Terkunci)</strong>
            <span>Peta instrumen telah dikonfigurasi dan ditampilkan dalam mode hanya lihat. Klik tombol <strong>Edit Formulir</strong> di atas jika ingin mengubah matriks instrumen.</span>
        </div>
    </div>

    <div x-show="isEditMode && {{ $isConfigured ? 'true' : 'false' }}" class="bg-amber-50 border border-amber-200 rounded-2xl p-4 flex items-center gap-3 text-xs text-amber-800 shadow-2xs no-print">
        <div>
            <strong class="text-amber-900 font-bold block text-sm">Mode Edit Aktif</strong>
            <span>Anda sekarang dapat mencentang atau menghapus instrumen asesmen pada matriks di bawah, lalu klik Simpan Draft atau Konfirmasi di bawah.</span>
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
            <div class="bg-white rounded-2xl border border-slate-200/90 shadow-2xs overflow-hidden page-break" x-data="{ expanded: true }">
                
                <!-- Unit Header Bar -->
                <div class="px-4 py-3 bg-slate-50/90 border-b border-slate-200/80 flex flex-col sm:flex-row sm:items-center justify-between gap-2">
                    <div class="flex items-center gap-2.5 min-w-0">
                        <button type="button" @click="expanded = !expanded" class="text-xs font-semibold text-slate-500 hover:text-slate-800 px-2 py-0.5 rounded bg-slate-100 hover:bg-slate-200 transition-colors no-print">
                            <span x-text="expanded ? 'Tutup' : 'Buka'"></span>
                        </button>
                        <div class="min-w-0">
                            <span class="text-[10px] font-bold text-blue-700 uppercase tracking-wider block">
                                Unit {{ $indexUnit + 1 }} &bull; {{ $unit->kode_unit }}
                            </span>
                            <h2 class="font-bold text-xs sm:text-sm text-slate-900 truncate" title="{{ $unit->judul_unit }}">
                                {{ $unit->judul_unit }}
                            </h2>
                        </div>
                    </div>

                    <!-- Quick Action Buttons Per Unit -->
                    <div class="flex items-center gap-1.5 no-print shrink-0 self-end sm:self-center" x-show="isEditMode">
                        <button type="button" @click="toggleInstrumentForUnit({{ $unit->id }}, 'clo')" class="px-2 py-1 bg-white hover:bg-slate-100 border border-slate-200 rounded-md text-[10px] font-semibold text-slate-700 transition">
                            + Observasi (CLO)
                        </button>
                        <button type="button" @click="toggleInstrumentForUnit({{ $unit->id }}, 'dpt')" class="px-2 py-1 bg-white hover:bg-slate-100 border border-slate-200 rounded-md text-[10px] font-semibold text-slate-700 transition">
                            + Praktik (DPT)
                        </button>
                        <button type="button" @click="toggleInstrumentForUnit({{ $unit->id }}, 'dpe')" class="px-2 py-1 bg-white hover:bg-slate-100 border border-slate-200 rounded-md text-[10px] font-semibold text-slate-700 transition">
                            + Tulis (DPE)
                        </button>
                    </div>
                </div>

                <!-- Matriks Tabel Unit -->
                <div class="overflow-x-auto" x-show="expanded">
                    <table class="w-full text-left border-collapse text-xs">
                        <thead>
                            <tr class="bg-slate-50/50 border-b border-slate-200 text-[11px] font-bold text-slate-600 uppercase tracking-wider">
                                <th class="py-2.5 px-3 w-1/4 min-w-[180px]">Elemen Kompetensi</th>
                                <th class="py-2.5 px-3 w-1/3 min-w-[220px]">Kriteria Unjuk Kerja (KUK)</th>
                                <th class="py-2.5 px-2 text-center w-12" title="IA.01: Ceklis Observasi">CLO</th>
                                <th class="py-2.5 px-2 text-center w-12" title="IA.02: Tugas Praktik">DPT</th>
                                <th class="py-2.5 px-2 text-center w-12" title="IA.03: Pertanyaan Pendukung Observasi">PMO</th>
                                <th class="py-2.5 px-2 text-center w-12" title="IA.05/06: Uji Tertulis CBT & Esai">DPE</th>
                                <th class="py-2.5 px-2 text-center w-12" title="IA.07: Pertanyaan Lisan">DPL</th>
                                <th class="py-2.5 px-2 text-center w-12" title="IA.08: Verifikasi Portofolio">VP</th>
                                <th class="py-2.5 px-2 text-center w-12" title="IA.09: Pertanyaan Wawancara">PW</th>
                                <th class="py-2.5 px-2 text-center w-12" title="IA.11: Ceklis Reviu Produk">CRP</th>
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
                                            $clo = $savedItem['clo'] ?? 1;
                                            $dpt = $savedItem['dpt'] ?? 1;
                                            $pmo = $savedItem['pmo'] ?? 0;
                                            $dpe = $savedItem['dpe'] ?? 1;
                                            $dpl = $savedItem['dpl'] ?? 0;
                                            $vp  = $savedItem['vp']  ?? 0;
                                            $pw  = $savedItem['pw']  ?? 0;
                                            $crp = $savedItem['crp'] ?? 0;
                                        @endphp
                                        <tr class="hover:bg-slate-50/50 transition-colors">
                                            @if($kIndex === 0)
                                                <td class="py-2.5 px-3 align-top bg-slate-50/30 border-r border-slate-100 font-medium" rowspan="{{ $totalKuk }}">
                                                    <span class="font-bold text-slate-900 block leading-tight">
                                                        {{ $elem->nomor_elemen }}. {{ $elem->nama_elemen }}
                                                    </span>
                                                    @if(!empty($elem->pertanyaan_elemen))
                                                        <span class="text-[10px] text-slate-500 italic block mt-1">"{{ $elem->pertanyaan_elemen }}"</span>
                                                    @endif
                                                </td>
                                            @endif
                                            <td class="py-2 px-3 align-top border-r border-slate-100">
                                                <div class="flex items-start gap-1.5">
                                                    <span class="font-bold text-blue-700 shrink-0">{{ $kuk->nomor_kuk }}</span>
                                                    <span class="text-slate-700 leading-snug">{{ $kuk->pernyataan_kuk }}</span>
                                                </div>
                                            </td>
                                            <td class="py-2 px-1 text-center align-middle">
                                                <input type="checkbox" name="matriks_peta[{{ $unit->id }}][{{ $elem->id }}][{{ $kKey }}][clo]" value="1" {{ $clo ? 'checked' : '' }} class="unit-{{ $unit->id }}-clo rounded-sm text-blue-600 focus:ring-0 cursor-pointer">
                                            </td>
                                            <td class="py-2 px-1 text-center align-middle">
                                                <input type="checkbox" name="matriks_peta[{{ $unit->id }}][{{ $elem->id }}][{{ $kKey }}][dpt]" value="1" {{ $dpt ? 'checked' : '' }} class="unit-{{ $unit->id }}-dpt rounded-sm text-blue-600 focus:ring-0 cursor-pointer">
                                            </td>
                                            <td class="py-2 px-1 text-center align-middle">
                                                <input type="checkbox" name="matriks_peta[{{ $unit->id }}][{{ $elem->id }}][{{ $kKey }}][pmo]" value="1" {{ $pmo ? 'checked' : '' }} class="unit-{{ $unit->id }}-pmo rounded-sm text-blue-600 focus:ring-0 cursor-pointer">
                                            </td>
                                            <td class="py-2 px-1 text-center align-middle">
                                                <input type="checkbox" name="matriks_peta[{{ $unit->id }}][{{ $elem->id }}][{{ $kKey }}][dpe]" value="1" {{ $dpe ? 'checked' : '' }} class="unit-{{ $unit->id }}-dpe rounded-sm text-blue-600 focus:ring-0 cursor-pointer">
                                            </td>
                                            <td class="py-2 px-1 text-center align-middle">
                                                <input type="checkbox" name="matriks_peta[{{ $unit->id }}][{{ $elem->id }}][{{ $kKey }}][dpl]" value="1" {{ $dpl ? 'checked' : '' }} class="unit-{{ $unit->id }}-dpl rounded-sm text-blue-600 focus:ring-0 cursor-pointer">
                                            </td>
                                            <td class="py-2 px-1 text-center align-middle">
                                                <input type="checkbox" name="matriks_peta[{{ $unit->id }}][{{ $elem->id }}][{{ $kKey }}][vp]" value="1" {{ $vp ? 'checked' : '' }} class="unit-{{ $unit->id }}-vp rounded-sm text-blue-600 focus:ring-0 cursor-pointer">
                                            </td>
                                            <td class="py-2 px-1 text-center align-middle">
                                                <input type="checkbox" name="matriks_peta[{{ $unit->id }}][{{ $elem->id }}][{{ $kKey }}][pw]" value="1" {{ $pw ? 'checked' : '' }} class="unit-{{ $unit->id }}-pw rounded-sm text-blue-600 focus:ring-0 cursor-pointer">
                                            </td>
                                            <td class="py-2 px-1 text-center align-middle">
                                                <input type="checkbox" name="matriks_peta[{{ $unit->id }}][{{ $elem->id }}][{{ $kKey }}][crp]" value="1" {{ $crp ? 'checked' : '' }} class="unit-{{ $unit->id }}-crp rounded-sm text-blue-600 focus:ring-0 cursor-pointer">
                                            </td>
                                        </tr>
                                    @endforeach
                                @else
                                    @php
                                        $savedItem = $savedPeta[$unit->id][$elem->id]['elem_only'] ?? [];
                                    @endphp
                                    <tr class="hover:bg-slate-50/50 transition-colors">
                                        <td class="py-2.5 px-3 align-top bg-slate-50/30 border-r border-slate-100 font-medium">
                                            <span class="font-bold text-slate-900 block leading-tight">
                                                {{ $elem->nomor_elemen }}. {{ $elem->nama_elemen }}
                                            </span>
                                        </td>
                                        <td class="py-2 px-3 align-top border-r border-slate-100 italic text-slate-400">
                                            KUK belum diinput untuk elemen ini.
                                        </td>
                                        <td class="py-2 px-1 text-center align-middle">
                                            <input type="checkbox" name="matriks_peta[{{ $unit->id }}][{{ $elem->id }}][elem_only][clo]" value="1" {{ ($savedItem['clo'] ?? 1) ? 'checked' : '' }} class="unit-{{ $unit->id }}-clo rounded-sm text-blue-600 focus:ring-0 cursor-pointer">
                                        </td>
                                        <td class="py-2 px-1 text-center align-middle">
                                            <input type="checkbox" name="matriks_peta[{{ $unit->id }}][{{ $elem->id }}][elem_only][dpt]" value="1" {{ ($savedItem['dpt'] ?? 1) ? 'checked' : '' }} class="unit-{{ $unit->id }}-dpt rounded-sm text-blue-600 focus:ring-0 cursor-pointer">
                                        </td>
                                        <td class="py-2 px-1 text-center align-middle">
                                            <input type="checkbox" name="matriks_peta[{{ $unit->id }}][{{ $elem->id }}][elem_only][pmo]" value="1" {{ ($savedItem['pmo'] ?? 0) ? 'checked' : '' }} class="unit-{{ $unit->id }}-pmo rounded-sm text-blue-600 focus:ring-0 cursor-pointer">
                                        </td>
                                        <td class="py-2 px-1 text-center align-middle">
                                            <input type="checkbox" name="matriks_peta[{{ $unit->id }}][{{ $elem->id }}][elem_only][dpe]" value="1" {{ ($savedItem['dpe'] ?? 1) ? 'checked' : '' }} class="unit-{{ $unit->id }}-dpe rounded-sm text-blue-600 focus:ring-0 cursor-pointer">
                                        </td>
                                        <td class="py-2 px-1 text-center align-middle">
                                            <input type="checkbox" name="matriks_peta[{{ $unit->id }}][{{ $elem->id }}][elem_only][dpl]" value="1" {{ ($savedItem['dpl'] ?? 0) ? 'checked' : '' }} class="unit-{{ $unit->id }}-dpl rounded-sm text-blue-600 focus:ring-0 cursor-pointer">
                                        </td>
                                        <td class="py-2 px-1 text-center align-middle">
                                            <input type="checkbox" name="matriks_peta[{{ $unit->id }}][{{ $elem->id }}][elem_only][vp]" value="1" {{ ($savedItem['vp'] ?? 0) ? 'checked' : '' }} class="unit-{{ $unit->id }}-vp rounded-sm text-blue-600 focus:ring-0 cursor-pointer">
                                        </td>
                                        <td class="py-2 px-1 text-center align-middle">
                                            <input type="checkbox" name="matriks_peta[{{ $unit->id }}][{{ $elem->id }}][elem_only][pw]" value="1" {{ ($savedItem['pw'] ?? 0) ? 'checked' : '' }} class="unit-{{ $unit->id }}-pw rounded-sm text-blue-600 focus:ring-0 cursor-pointer">
                                        </td>
                                        <td class="py-2 px-1 text-center align-middle">
                                            <input type="checkbox" name="matriks_peta[{{ $unit->id }}][{{ $elem->id }}][elem_only][crp]" value="1" {{ ($savedItem['crp'] ?? 0) ? 'checked' : '' }} class="unit-{{ $unit->id }}-crp rounded-sm text-blue-600 focus:ring-0 cursor-pointer">
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
        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
            
            <!-- KOLOM 1: CATATAN ASESOR -->
            <div class="bg-white rounded-2xl border border-slate-200/90 shadow-2xs p-4 sm:p-5 space-y-2.5">
                <h2 class="font-bold text-xs sm:text-sm text-slate-800 border-b border-slate-100 pb-2">
                    Catatan Perencanaan Asesor
                </h2>
                <div class="space-y-1.5">
                    <label class="text-[11px] font-medium text-slate-600 block">Catatan metodologi / modifikasi rencana instrumen asesmen:</label>
                    <textarea name="catatan_asesor" rows="4" class="w-full bg-slate-50 border border-slate-200 rounded-xl p-3 text-xs text-slate-800 focus:bg-white focus:ring-1 focus:ring-blue-500 transition-colors" placeholder="Tuliskan catatan khusus terkait pemilihan metode dan instrumen asesmen...">{{ old('catatan_asesor', $mapa02->catatan_asesor ?? '') }}</textarea>
                </div>
            </div>

            <!-- KOLOM 2: PENGESAHAN TANDA TANGAN ASESOR -->
            <div class="bg-white rounded-2xl border border-slate-200/90 shadow-2xs p-4 sm:p-5 space-y-3">
                <div class="flex items-center justify-between border-b border-slate-100 pb-2">
                    <h2 class="font-bold text-xs sm:text-sm text-slate-800">
                        Pengesahan Asesor Penguji
                    </h2>
                    @if($isConfirmed)
                        <span class="px-2 py-0.5 rounded-md bg-emerald-100 text-emerald-800 font-bold text-[10px]">
                            ✓ Telah Disahkan
                        </span>
                    @endif
                </div>

                <!-- Pilihan Metode TTD -->
                <div class="flex items-center gap-3 text-xs no-print">
                    <label class="flex items-center gap-1.5 cursor-pointer font-medium text-slate-700">
                        <input type="radio" name="ttd_mode" value="profile" x-model="signatureMode" class="text-blue-600 focus:ring-0">
                        <span>Gunakan TTD Profil</span>
                    </label>
                    <label class="flex items-center gap-1.5 cursor-pointer font-medium text-slate-700">
                        <input type="radio" name="ttd_mode" value="canvas" x-model="signatureMode" class="text-blue-600 focus:ring-0">
                        <span>Gambar TTD Digital</span>
                    </label>
                </div>

                <!-- Mode Profile Signature -->
                <div x-show="signatureMode === 'profile'" class="space-y-1.5">
                    <div class="h-28 bg-slate-50 rounded-xl border border-slate-200 flex items-center justify-center p-2">
                        @if($profileTtd)
                            <img src="{{ asset($profileTtd) }}" alt="TTD Profil Asesor" class="max-h-24 object-contain">
                        @else
                            <span class="text-xs text-slate-400 italic">Tanda tangan profil belum diatur. Silakan pilih "Gambar TTD Digital".</span>
                        @endif
                    </div>
                    <input type="hidden" name="tanda_tangan_asesor" :value="signatureMode === 'profile' ? '{{ $profileTtd }}' : canvasSignatureData" id="inputTtdAsesor">
                </div>

                <!-- Mode Canvas Signature -->
                <div x-show="signatureMode === 'canvas'" class="space-y-1.5" style="display: none;">
                    <div class="border border-slate-300 rounded-xl bg-white relative overflow-hidden">
                        <canvas id="canvasMapa02Asesor" class="w-full h-28 bg-white cursor-crosshair block touch-none"></canvas>
                    </div>
                    <div class="flex items-center justify-between text-[11px]">
                        <span class="text-slate-400">Gunakan mouse atau layar sentuh.</span>
                        <button type="button" @click="clearCanvas()" class="text-rose-600 hover:text-rose-800 font-semibold cursor-pointer">
                            [ Bersihkan ]
                        </button>
                    </div>
                </div>                <div class="text-[11px] text-slate-500 pt-1">
                    Asesor: <strong>{{ $asesorNama }}</strong> (No. Reg: <strong>{{ $asesorMet }}</strong>)
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
            signatureMode: '{{ !empty($profileTtd) ? "profile" : "canvas" }}',
            canvasSignatureData: '',
            signaturePad: null,
            isSubmitting: false,
            showConfirmModal: false,

            init() {
                this.$nextTick(() => {
                    this.initSignaturePad();
                });
            },

            toggleEditMode() {
                this.isEditMode = !this.isEditMode;
                if (this.isEditMode && this.signatureMode === 'canvas') {
                    this.$nextTick(() => {
                        this.initSignaturePad();
                    });
                }
            },

            initSignaturePad() {
                const canvas = document.getElementById('canvasMapa02Asesor');
                if (!canvas) return;

                function resizeCanvas() {
                    const ratio = Math.max(window.devicePixelRatio || 1, 1);
                    canvas.width = canvas.offsetWidth * ratio;
                    canvas.height = canvas.offsetHeight * ratio;
                    canvas.getContext('2d').scale(ratio, ratio);
                }

                window.addEventListener('resize', resizeCanvas);
                resizeCanvas();

                this.signaturePad = new SignaturePad(canvas, {
                    backgroundColor: 'rgb(255, 255, 255)',
                    penColor: 'rgb(15, 23, 42)',
                    minWidth: 1.2,
                    maxWidth: 2.5
                });
            },

            clearCanvas() {
                if (this.signaturePad) {
                    this.signaturePad.clear();
                    this.canvasSignatureData = '';
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
                if (this.signatureMode === 'canvas' && this.signaturePad && !this.signaturePad.isEmpty()) {
                    this.canvasSignatureData = this.signaturePad.toDataURL('image/png');
                    const hiddenInput = document.getElementById('inputTtdAsesor');
                    if (hiddenInput) {
                        hiddenInput.value = this.canvasSignatureData;
                    }
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
