@extends('tata-letak.dasbor')

@section('judul', 'FR.AK.07 - Kesepakatan Penyesuaian yang Wajar')

@push('css')
@endpush

@section('konten')
@php
    $asesorNama = $pendaftaran->asesor->nama_lengkap ?? $pendaftaran->jadwal?->asesor?->nama_lengkap ?? 'Asesor LSP';
    $asesorMet = $pendaftaran->asesor->nomor_registrasi ?? $pendaftaran->jadwal?->asesor?->nomor_registrasi ?? 'MET.000.004455';
    $profileTtd = null;
    $isConfirmed = $ak07 && $ak07->isConfirmed() && !empty($ak07->asesor_signature);
    $isSignedByAsesi = $isConfirmed;
    $savedChecklist = (array) ($ak07?->items_checklist ?? []);
    $selectedPotensi = $ak07?->potensi_asesi !== null ? (int) $ak07->potensi_asesi : null;
    $selectedFase = $ak07?->fase_penggunaan;
    $asesorSigToShow = $ak07?->asesor_signature;
    $asesorSigUrl = $asesorSigToShow
        ? (\Illuminate\Support\Str::startsWith($asesorSigToShow, ['data:', 'http://', 'https://']) ? $asesorSigToShow : asset($asesorSigToShow))
        : null;

    $faseList = [
        'pra_asesmen' => ['title' => 'Pra Asesmen', 'desc' => 'Diterapkan sebelum tahapan asesmen dimulai (misal: verifikasi berkas & konsultasi pra-uji)'],
        'saat_pra_asesmen' => ['title' => 'Pada Saat Asesmen', 'desc' => 'Diterapkan secara langsung selama sesi demonstrasi praktik, ujian tertulis, atau wawancara'],
        'setelah_pra_asesmen' => ['title' => 'Setelah Asesmen', 'desc' => 'Diterapkan pada tahap penyusunan rekomendasi, umpan balik, dan pengumpulan bukti tambahan'],
    ];
@endphp

<div class="max-w-5xl mx-auto px-2 sm:px-4 py-3 space-y-4">

    <!-- BREADCRUMB -->
    <div class="flex items-center gap-2 text-xs text-slate-500 pb-1">
        <a href="{{ route('asesi.dashboard') }}" class="hover:text-blue-600 font-medium">Dashboard</a>
        <span>/</span>
        <a href="{{ route('asesi.tahapan', ['step' => 4, 'pendaftaran_id' => $pendaftaran->id]) }}" class="hover:text-blue-600 font-medium">Tahapan Asesmen</a>
        <span>/</span>
        <span class="text-slate-800 font-bold">FR.AK.07</span>
    </div>

    <!-- NOTIFIKASI SUKSES / ERROR -->
    @if(session('sukses'))
        <div class="p-3.5 bg-emerald-50 border border-emerald-200 rounded-xl text-emerald-800 text-xs font-semibold shadow-2xs">
            <span>{{ session('sukses') }}</span>
        </div>
    @endif

    @if(session('error'))
        <div class="p-3.5 bg-rose-50 border border-rose-200 rounded-xl text-rose-800 text-xs font-semibold shadow-2xs">
            <span>{{ session('error') }}</span>
        </div>
    @endif

    <!-- =========================================================================
         HEADER CARD: FR.AK.07 PENYESUAIAN YANG WAJAR DAN BERALASAN
         ========================================================================= -->
    <div class="bg-white rounded-2xl border border-slate-200/90 shadow-2xs p-4 sm:p-5 space-y-3">
        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3 border-b border-slate-100 pb-3">
            <div>
                <h1 class="text-base sm:text-lg font-bold text-slate-900 leading-tight">
                    FR.AK.07 &bull; Ceklis Penyesuaian yang Wajar dan Beralasan
                </h1>
                <p class="text-xs text-slate-500">
                    Formulir asesmen kontekstual untuk asesi dengan kebutuhan/karakteristik khusus sesuai regulasi BNSP.
                </p>
            </div>

            <!-- Status Pill Header -->
            <div class="flex items-center gap-2">
                @if($formBelumDibuat ?? false)
                    <span class="px-3 py-1 rounded-full bg-amber-50 text-amber-700 border border-amber-200 text-xs font-bold flex items-center gap-1.5 shadow-2xs">
                        <span class="w-2 h-2 rounded-full bg-amber-500"></span>
                        Belum Dibuat Asesor / Admin
                    </span>
                @elseif($isConfirmed)
                    <span class="px-3 py-1 rounded-full bg-emerald-50 text-emerald-700 border border-emerald-200 text-xs font-bold flex items-center gap-1.5 shadow-2xs">
                        <span class="w-2 h-2 rounded-full bg-emerald-500 animate-pulse"></span>
                        CONFIRMED &bull; Terkonfirmasi & Terkunci
                    </span>
                @else
                    <span class="px-3 py-1 rounded-full bg-amber-50 text-amber-700 border border-amber-200 text-xs font-bold flex items-center gap-1.5 shadow-2xs">
                        <span class="w-2 h-2 rounded-full bg-amber-500"></span>
                        {{ $isSignedByAsesi ? 'Kesepakatan Dikonfirmasi' : 'Perlu Konfirmasi Asesi' }}
                    </span>
                @endif
            </div>
        </div>

        <!-- Metadata Grid Compact (4 columns) -->
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-2.5 pt-1 text-xs">
            <div class="bg-slate-50/80 border border-slate-100 rounded-xl p-2.5 space-y-0.5">
                <span class="text-[10px] font-bold uppercase tracking-wider text-slate-400">Skema Sertifikasi</span>
                <div class="font-bold text-slate-800 truncate" title="{{ $pendaftaran->skema->nama_skema ?? '-' }}">
                    {{ $pendaftaran->skema->nama_skema ?? '-' }}
                </div>
                <div class="text-[10px] text-slate-500 font-mono">Kode: {{ $pendaftaran->skema->kode_skema ?? '-' }}</div>
            </div>

            <div class="bg-slate-50/80 border border-slate-100 rounded-xl p-2.5 space-y-0.5">
                <span class="text-[10px] font-bold uppercase tracking-wider text-slate-400">Nama Asesi (Peserta)</span>
                <div class="font-bold text-slate-800 truncate">
                    {{ $pendaftaran->asesi->nama_lengkap ?? auth()->user()->nama_lengkap }}
                </div>
                <div class="text-[10px] text-slate-500">Reg: #{{ $pendaftaran->nomor_pendaftaran }}</div>
            </div>

            <div class="bg-slate-50/80 border border-slate-100 rounded-xl p-2.5 space-y-0.5">
                <span class="text-[10px] font-bold uppercase tracking-wider text-slate-400">Asesor Penguji</span>
                <div class="font-bold text-slate-800 truncate">
                    {{ $asesorNama }}
                </div>
                <div class="text-[10px] text-slate-500">No. MET: {{ $asesorMet }}</div>
            </div>

            <div class="bg-slate-50/80 border border-slate-100 rounded-xl p-2.5 space-y-0.5">
                <span class="text-[10px] font-bold uppercase tracking-wider text-slate-400">TUK & Jadwal</span>
                <div class="font-bold text-slate-800 truncate">
                    {{ $pendaftaran->jadwal->nama_tuk ?? ($pendaftaran->tuk_type ? 'TUK ' . $pendaftaran->tuk_type : 'TUK Mandiri LSP') }}
                </div>
                <div class="text-[10px] text-slate-500">
                    {{ $pendaftaran->jadwal?->tanggal_mulai ? \Carbon\Carbon::parse($pendaftaran->jadwal->tanggal_mulai)->translatedFormat('d M Y') : 'Sesuai Jadwal' }}
                </div>
            </div>
        </div>
    </div>

    @if($formBelumDibuat ?? false)
        <div class="p-4 bg-amber-50 border border-amber-200/90 rounded-2xl flex items-start gap-3.5 shadow-2xs">
            <div class="w-9 h-9 rounded-xl bg-amber-100 text-amber-700 flex items-center justify-center shrink-0 font-bold text-sm">
                <i class="fa-solid fa-triangle-exclamation"></i>
            </div>
            <div class="space-y-1">
                <h2 class="text-sm font-bold text-amber-900 leading-tight">Formulir FR.AK.07 Belum Dibuat oleh Asesor / Admin</h2>
                <p class="text-xs text-amber-800 leading-relaxed">
                    Formulir Penyesuaian yang Wajar dan Beralasan (FR.AK.07) untuk skema <strong>{{ $pendaftaran->skema->nama_skema ?? '-' }}</strong> belum dibuat atau belum disahkan oleh Asesor Penguji atau Admin LSP. Anda tetap dapat meninjau format formulir di bawah ini, namun pengesahan tanda tangan akan dibuka setelah formulir dilengkapi oleh pihak Asesor / Admin.
                </p>
            </div>
        </div>
    @endif

    <!-- 1. BAGIAN POTENSI ASESI (1 s.d. 5) -->
    <div class="bg-white rounded-2xl border border-slate-200/90 shadow-2xs p-4 sm:p-5 space-y-3">
        <div class="flex items-center justify-between border-b border-slate-100 pb-2.5">
            <div class="flex items-center gap-2 text-sm font-bold text-slate-900">
                <span class="w-6 h-6 rounded-lg bg-blue-50 text-blue-700 flex items-center justify-center text-xs font-black">1</span>
                <span>Potensi Asesi (Karakteristik & Latar Belakang Kandidat)</span>
            </div>
            <span class="text-[11px] text-slate-400 font-medium">Ditetapkan Asesor</span>
        </div>
        <p class="text-xs text-slate-500">
            Klasifikasi kategori potensi kandidat asesi yang mendasari kontekstualisasi penyesuaian asesmen:
        </p>

        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-2 pt-1">
            @foreach($potensiDefinitions as $pVal => $pLabel)
                @php $isSelected = ($selectedPotensi === $pVal); @endphp
                <div class="relative flex items-start gap-2.5 p-3 rounded-xl border text-xs transition-all {{ $isSelected ? 'border-blue-500 bg-blue-50/50 text-slate-900 font-semibold shadow-2xs ring-1 ring-blue-500/20' : 'border-slate-200 bg-slate-50/40 text-slate-500 opacity-60' }}">
                    <div class="mt-0.5 shrink-0">
                        @if($isSelected)
                            <span class="w-4 h-4 rounded-full bg-blue-600 text-white flex items-center justify-center text-[10px] font-bold">✓</span>
                        @else
                            <span class="w-4 h-4 rounded-full border border-slate-300 bg-white flex items-center justify-center text-[10px] text-slate-400">&minus;</span>
                        @endif
                    </div>
                    <div class="space-y-0.5 leading-snug">
                        <div class="flex items-center gap-1.5">
                            <span class="inline-block px-1.5 py-0.2 rounded {{ $isSelected ? 'bg-blue-600 text-white' : 'bg-slate-200 text-slate-600' }} text-[10px] font-bold">Kategori {{ $pVal }}</span>
                            @if($isSelected)
                                <span class="text-[9px] text-blue-700 font-bold uppercase tracking-wider bg-blue-100/70 px-1.5 py-0.2 rounded">Ditetapkan Asesor</span>
                            @endif
                        </div>
                        <div class="text-[11px] {{ $isSelected ? 'text-slate-800 font-medium' : 'text-slate-500' }}">{{ $pLabel }}</div>
                    </div>
                </div>
            @endforeach
        </div>
    </div>

    <!-- 2. BAGIAN FASE PENGGUNAAN PENYESUAIAN -->
    <div class="bg-white rounded-2xl border border-slate-200/90 shadow-2xs p-4 sm:p-5 space-y-3">
        <div class="flex items-center justify-between border-b border-slate-100 pb-2.5">
            <div class="flex items-center gap-2 text-sm font-bold text-slate-900">
                <span class="w-6 h-6 rounded-lg bg-blue-50 text-blue-700 flex items-center justify-center text-xs font-black">2</span>
                <span>Fase Pelaksanaan Penyesuaian</span>
            </div>
            <span class="text-[11px] text-slate-400 font-medium">Diterapkan Saat Asesmen</span>
        </div>

        <div class="grid grid-cols-1 sm:grid-cols-3 gap-2 pt-1 text-xs">
            @foreach($faseList as $fKey => $fInfo)
                @php $isFaseSelected = ($selectedFase === $fKey); @endphp
                <div class="relative flex items-start gap-2.5 p-3 rounded-xl border transition-all {{ $isFaseSelected ? 'border-blue-500 bg-blue-50/50 text-slate-900 font-semibold shadow-2xs ring-1 ring-blue-500/20' : 'border-slate-200 bg-slate-50/40 text-slate-500 opacity-60' }}">
                    <div class="mt-0.5 shrink-0">
                        @if($isFaseSelected)
                            <span class="w-4 h-4 rounded-full bg-blue-600 text-white flex items-center justify-center text-[10px] font-bold">✓</span>
                        @else
                            <span class="w-4 h-4 rounded-full border border-slate-300 bg-white flex items-center justify-center text-[10px] text-slate-400">&minus;</span>
                        @endif
                    </div>
                    <div class="space-y-0.5">
                        <div class="flex items-center gap-1.5">
                            <div class="font-bold {{ $isFaseSelected ? 'text-blue-900' : 'text-slate-700' }}">{{ $fInfo['title'] }}</div>
                            @if($isFaseSelected)
                                <span class="text-[9px] text-blue-700 font-bold uppercase tracking-wider bg-blue-100/70 px-1.5 py-0.2 rounded">Fase Diterapkan</span>
                            @endif
                        </div>
                        <div class="text-[11px] {{ $isFaseSelected ? 'text-slate-700 font-medium' : 'text-slate-500' }} leading-tight">{{ $fInfo['desc'] }}</div>
                    </div>
                </div>
            @endforeach
        </div>
    </div>

    <!-- 3. MATRIKS 8 KATEGORI KEBUTUHAN PENYESUAIAN YANG WAJAR STANDAR BNSP -->
    <div class="bg-white rounded-2xl border border-slate-200/90 shadow-2xs p-4 sm:p-5 space-y-4">
        <div class="flex items-center justify-between border-b border-slate-100 pb-2.5">
            <div class="flex items-center gap-2 text-sm font-bold text-slate-900">
                <span class="w-6 h-6 rounded-lg bg-blue-50 text-blue-700 flex items-center justify-center text-xs font-black">3</span>
                <span>Matriks Kebutuhan Penyesuaian yang Wajar (8 Kategori BNSP)</span>
            </div>
            <span class="text-[11px] text-slate-400 font-medium">Baku Acuan BNSP FR.AK.07</span>
        </div>

        <div class="space-y-3">
            @foreach($criteriaDefinitions as $cId => $crit)
                @php
                    $itemSaved = $savedChecklist[$cId] ?? [];
                    $isPerluSaved = filter_var($itemSaved['perlu_penyesuaian'] ?? false, FILTER_VALIDATE_BOOLEAN);
                    $opsiSaved = (array) ($itemSaved['opsi_dipilih'] ?? []);
                    $ketSaved = $itemSaved['keterangan'] ?? '';
                @endphp

                <div class="border rounded-xl overflow-hidden transition-all {{ $isPerluSaved ? 'border-amber-300 bg-amber-50/10' : 'border-slate-200 bg-white' }}">
                    <!-- Category Header Bar -->
                    <div class="p-3.5 {{ $isPerluSaved ? 'bg-amber-50/60' : 'bg-slate-50/80' }} flex flex-col sm:flex-row sm:items-center justify-between gap-2.5 border-b border-slate-100">
                        <div class="flex items-center gap-2.5">
                            <span class="w-6 h-6 rounded-md {{ $isPerluSaved ? 'bg-amber-600 text-white' : 'bg-white border border-slate-200 text-slate-700' }} font-bold text-xs flex items-center justify-center shrink-0">
                                {{ $cId }}
                            </span>
                            <div class="font-bold text-xs sm:text-sm text-slate-800">
                                {{ $crit['title'] }}
                            </div>
                        </div>

                        <!-- Perlu / Tidak Perlu Badge (Read-only for Asesi) -->
                        <div class="shrink-0">
                            @if($isPerluSaved)
                                <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full bg-amber-600 text-white text-[11px] font-bold shadow-2xs">
                                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7"/></svg>
                                    <span>Perlu Penyesuaian</span>
                                </span>
                            @else
                                <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full bg-slate-100 text-slate-600 border border-slate-200 text-[11px] font-semibold">
                                    <span class="w-1.5 h-1.5 rounded-full bg-slate-400"></span>
                                    <span>Tidak Perlu</span>
                                </span>
                            @endif
                        </div>
                    </div>

                    <!-- Options Body -->
                    @if($isPerluSaved)
                        <div class="p-3.5 bg-amber-50/20 space-y-3 text-xs border-t border-amber-100">
                            <div>
                                <span class="font-bold text-slate-700 block mb-1.5">
                                    Opsi Bentuk Penyesuaian yang Disepakati:
                                </span>
                                <div class="grid grid-cols-1 sm:grid-cols-2 gap-2">
                                    @foreach($crit['sub_options'] as $subKey => $subLabel)
                                        @php $isOpsiChecked = in_array($subKey, $opsiSaved); @endphp
                                        <div class="flex items-start gap-2 p-2.5 rounded-lg border transition-colors {{ $isOpsiChecked ? 'bg-white border-amber-400 font-semibold text-slate-900 shadow-2xs' : 'bg-slate-50/50 border-slate-200 text-slate-400 opacity-60' }}">
                                            <div class="mt-0.5 shrink-0">
                                                @if($isOpsiChecked)
                                                    <span class="w-4 h-4 rounded bg-amber-600 text-white flex items-center justify-center text-[10px] font-bold">✓</span>
                                                @else
                                                    <span class="w-4 h-4 rounded border border-slate-300 bg-white flex items-center justify-center text-[10px] text-slate-400">&minus;</span>
                                                @endif
                                            </div>
                                            <span class="text-[11px] leading-snug">{{ $subLabel }}</span>
                                        </div>
                                    @endforeach
                                </div>
                            </div>

                            @if(!empty($ketSaved))
                                <div>
                                    <span class="font-bold text-slate-700 block mb-1">
                                        Catatan Khusus Penyesuaian Kategori {{ $cId }}:
                                    </span>
                                    <div class="w-full px-3 py-2 rounded-lg border border-amber-200 bg-white text-xs text-slate-700 italic">
                                        {{ $ketSaved }}
                                    </div>
                                </div>
                            @endif
                        </div>
                    @else
                        <div class="px-4 py-2.5 text-[11px] text-slate-400 italic bg-slate-50/30">
                            Pelaksanaan pada kategori ini disepakati berjalan sesuai prosedur umum tanpa memerlukan penyesuaian khusus.
                        </div>
                    @endif
                </div>
            @endforeach
        </div>
    </div>

    <!-- 4. BAGIAN REKOMENDASI KESEPAKATAN ASESMEN -->
    <div class="bg-white rounded-2xl border border-slate-200/90 shadow-2xs p-4 sm:p-5 space-y-3">
        <div class="flex items-center gap-2 text-sm font-bold text-slate-900 border-b border-slate-100 pb-2.5">
            <span class="w-6 h-6 rounded-lg bg-blue-50 text-blue-700 flex items-center justify-center text-xs font-black">4</span>
            <span>Rekomendasi Hasil Kesepakatan Penyesuaian</span>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-3 gap-3 text-xs">
            <div class="space-y-1">
                <span class="font-bold text-slate-700 block">
                    Acuan Pembanding Disepakati:
                </span>
                <div class="p-2.5 rounded-xl border border-slate-200 bg-slate-50/60 text-xs text-slate-800 font-medium">
                    {{ $ak07?->acuan_pembanding_disepakati ?: 'Belum ditentukan' }}
                </div>
            </div>

            <div class="space-y-1">
                <span class="font-bold text-slate-700 block">
                    Metode Asesmen Disepakati:
                </span>
                <div class="p-2.5 rounded-xl border border-slate-200 bg-slate-50/60 text-xs text-slate-800 font-medium">
                    {{ $ak07?->metode_disepakati ?: 'Belum ditentukan' }}
                </div>
            </div>

            <div class="space-y-1">
                <span class="font-bold text-slate-700 block">
                    Instrumen Pengganti / Penyesuaian:
                </span>
                <div class="p-2.5 rounded-xl border border-slate-200 bg-slate-50/60 text-xs text-slate-800 font-medium">
                    {{ $ak07?->instrumen_disepakati ?: 'Belum ditentukan' }}
                </div>
            </div>
        </div>

        <div class="space-y-1 pt-1 text-xs">
            <span class="font-bold text-slate-700 block">
                Catatan Tambahan Asesor:
            </span>
            <div class="p-2.5 rounded-xl border border-slate-200 bg-slate-50/60 text-xs text-slate-700 italic">
                {{ $ak07?->catatan_asesor ?: 'Tidak ada catatan tambahan.' }}
            </div>
        </div>
    </div>

    @if($asesorSigToShow)
        <div class="bg-white rounded-2xl border border-slate-200/90 shadow-2xs p-4 flex items-center justify-between gap-4">
            <div>
                <h3 class="text-sm font-bold text-slate-900">Disahkan Asesor untuk Seluruh Asesi Skema</h3>
                <p class="text-xs text-slate-500 mt-1">
                    {{ $asesorNama }}{{ $ak07?->asesor_signed_at ? ' · ' . \Carbon\Carbon::parse($ak07->asesor_signed_at)->format('d/m/Y H:i') : '' }}
                </p>
            </div>
            <img src="{{ $asesorSigUrl }}" alt="Tanda tangan Asesor FR.AK.07" class="max-h-16 max-w-40 object-contain">
        </div>
    @endif

    <!-- 5. BAGIAN PENGESAHAN TANDA TANGAN DIGITAL & STATUS KESEPAKATAN -->
    <div class="bg-white rounded-2xl border border-slate-200/90 shadow-2xs p-4 sm:p-5 space-y-4">
        <div class="flex items-center gap-2 text-sm font-bold text-slate-900 border-b border-slate-100 pb-2.5">
            <span class="w-6 h-6 rounded-lg bg-blue-50 text-blue-700 flex items-center justify-center text-xs font-black">5</span>
            <span>Pengesahan Tanda Tangan Digital & Status Kesepakatan</span>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-2 gap-4 text-xs">
            
            <!-- Sisi Asesor -->
            <div class="border border-slate-200 rounded-xl p-3.5 space-y-3 bg-slate-50/50">
                <div class="flex items-center justify-between border-b border-slate-200 pb-2">
                    <span class="font-bold text-slate-800">Asesor Penguji</span>
                    @if($asesorSigToShow)
                        <span class="px-2 py-0.5 rounded-full bg-emerald-50 text-emerald-700 border border-emerald-200 text-[10px] font-bold">
                            Tertera TTD ({{ $ak07?->asesor_signed_at ? \Carbon\Carbon::parse($ak07->asesor_signed_at)->format('d/m/Y H:i') : 'Tersimpan' }})
                        </span>
                    @else
                        <span class="px-2 py-0.5 rounded-full bg-amber-50 text-amber-700 border border-amber-200 text-[10px] font-bold">
                            Belum TTD
                        </span>
                    @endif
                </div>

                <div class="text-slate-600">
                    <strong>{{ $asesorNama }}</strong> (No. MET: {{ $asesorMet }})
                </div>

                <div class="p-3 border border-slate-200 rounded-xl bg-white flex flex-col items-center justify-center h-36 text-center space-y-1">
                    @if($asesorSigToShow)
                        <img src="{{ asset($asesorSigToShow) }}" alt="TTD Asesor" class="max-h-24 object-contain">
                        <span class="text-[10px] text-emerald-700 font-bold">Tanda Tangan Terverifikasi</span>
                    @else
                        <span class="text-slate-400 italic">Menunggu TTD Asesor</span>
                    @endif
                </div>
            </div>

            <!-- Sisi Asesi -->
            <div class="border border-slate-200 rounded-xl p-3.5 space-y-3 bg-slate-50/50">
                <div class="flex items-center justify-between border-b border-slate-200 pb-2">
                    <span class="font-bold text-slate-800">Asesi (Kandidat)</span>
                    @if($formBelumDibuat ?? false)
                        <span class="px-2 py-0.5 rounded-full bg-slate-100 text-slate-600 border border-slate-200 text-[10px] font-bold">
                            Belum Tersedia
                        </span>
                    @elseif($isSignedByAsesi)
                        <span class="px-2 py-0.5 rounded-full bg-emerald-50 text-emerald-700 border border-emerald-200 text-[10px] font-bold">
                            Disetujui Asesi ({{ $ak07?->asesi_signed_at ? \Carbon\Carbon::parse($ak07->asesi_signed_at)->format('d/m/Y H:i') : 'Tersimpan' }})
                        </span>
                    @else
                        <span class="px-2 py-0.5 rounded-full bg-amber-50 text-amber-700 border border-amber-200 text-[10px] font-bold">
                            Perlu Tanda Tangan Anda
                        </span>
                    @endif
                </div>

                <div class="text-slate-600">
                    <strong>{{ $pendaftaran->asesi->nama_lengkap ?? auth()->user()->nama_lengkap }}</strong>
                </div>

                @if($formBelumDibuat ?? false)
                    <div class="p-3 border border-slate-200 bg-white rounded-xl flex flex-col items-center justify-center h-36 text-center space-y-1.5">
                        <div class="w-8 h-8 rounded-full bg-amber-50 text-amber-600 flex items-center justify-center text-xs">
                            <i class="fa-solid fa-lock"></i>
                        </div>
                        <span class="text-[11px] font-bold text-slate-700">Tanda Tangan Terkunci</span>
                        <span class="text-[10px] text-slate-500 max-w-xs">
                            Formulir belum dibuat/ditandatangani Asesor atau Admin.
                        </span>
                    </div>
                @elseif($isSignedByAsesi)
                    <div class="p-3 border border-emerald-200 bg-emerald-50/30 rounded-xl flex flex-col items-center justify-center h-36 text-center space-y-1">
                        <img src="{{ asset($ak07->asesi_signature) }}" alt="TTD Asesi" class="max-h-20 object-contain mb-1">
                        <span class="text-[10px] text-emerald-700 font-bold">
                            Telah Ditandatangani & Disetujui ({{ $ak07?->asesi_signed_at ? \Carbon\Carbon::parse($ak07->asesi_signed_at)->format('d/m/Y H:i') : '' }})
                        </span>
                    </div>
                @else
                    <form id="formSignAsesi" action="{{ route('asesi.ak07.sign-asesi', $pendaftaran->id) }}" method="POST" class="space-y-2.5" x-data="ak07AsesiSignature()" x-init="init()" @submit.prevent="submit($event)">
                        @csrf
                        <input type="hidden" name="tanda_tangan_asesi" x-ref="signatureInput">

                        <div class="space-y-1">
                            <div class="border-2 border-dashed border-slate-300 rounded-xl bg-white relative p-1">
                                <canvas x-ref="canvas" width="800" height="240" class="w-full h-32 rounded-lg cursor-crosshair touch-none block" style="touch-action: none;"></canvas>
                            </div>
                            <div class="flex items-center justify-between text-[11px]">
                                <span class="text-slate-500">Gambar tanda tangan langsung pada kotak ini.</span>
                                <button type="button" @click="clear()" class="text-rose-600 hover:text-rose-800 font-semibold">[ Hapus ]</button>
                            </div>
                        </div>

                        <button type="submit" class="w-full py-2.5 bg-blue-600 hover:bg-blue-700 text-white rounded-xl font-bold text-xs shadow-xs transition flex items-center justify-center cursor-pointer">
                            <span>Tandatangani & Setujui Kesepakatan FR.AK.07</span>
                        </button>
                    </form>
                @endif
            </div>

        </div>
    </div>
</div>

<!-- Alpine.js Component -->
<script>
    function ak07AsesiSignature() {
        return {
            canvas: null,
            context: null,
            strokes: [],
            currentStroke: [],
            drawing: false,
            pixelRatio: 1,
            resizeObserver: null,

            init() {
                this.canvas = this.$refs.canvas;
                this.context = this.canvas.getContext('2d');
                this.resizeCanvas();
                this.resizeObserver = new ResizeObserver(() => this.resizeCanvas());
                this.resizeObserver.observe(this.canvas);
                this.canvas.addEventListener('pointerdown', (event) => this.start(event));
                this.canvas.addEventListener('pointermove', (event) => this.move(event));
                this.canvas.addEventListener('pointerup', () => this.finish());
                this.canvas.addEventListener('pointercancel', () => this.finish());
            },

            resizeCanvas() {
                const rect = this.canvas.getBoundingClientRect();
                if (!rect.width || !rect.height) return;
                this.pixelRatio = Math.max(window.devicePixelRatio || 1, 1);
                this.canvas.width = Math.round(rect.width * this.pixelRatio);
                this.canvas.height = Math.round(rect.height * this.pixelRatio);
                this.context.setTransform(this.pixelRatio, 0, 0, this.pixelRatio, 0, 0);
                this.draw();
            },

            point(event) {
                const rect = this.canvas.getBoundingClientRect();
                return { x: event.clientX - rect.left, y: event.clientY - rect.top };
            },

            start(event) {
                event.preventDefault();
                this.drawing = true;
                this.currentStroke = [this.point(event)];
                this.canvas.setPointerCapture(event.pointerId);
                this.draw();
            },

            move(event) {
                if (!this.drawing) return;
                event.preventDefault();
                this.currentStroke.push(this.point(event));
                this.draw();
            },

            finish() {
                if (!this.drawing) return;
                this.drawing = false;
                if (this.currentStroke.length) {
                    this.strokes.push(this.currentStroke);
                    this.currentStroke = [];
                    this.$refs.signatureInput.value = this.canvas.toDataURL('image/png');
                }
            },

            draw() {
                const rect = this.canvas.getBoundingClientRect();
                this.context.clearRect(0, 0, rect.width, rect.height);
                this.context.strokeStyle = '#0f172a';
                this.context.fillStyle = '#0f172a';
                this.context.lineWidth = 2.8;
                this.context.lineCap = 'round';
                this.context.lineJoin = 'round';
                [...this.strokes, this.currentStroke].forEach((stroke) => {
                    if (!stroke.length) return;
                    this.context.beginPath();
                    this.context.moveTo(stroke[0].x, stroke[0].y);
                    stroke.slice(1).forEach((point) => this.context.lineTo(point.x, point.y));
                    this.context.stroke();
                });
            },

            clear() {
                this.strokes = [];
                this.currentStroke = [];
                this.drawing = false;
                this.context.clearRect(0, 0, this.canvas.width, this.canvas.height);
                this.$refs.signatureInput.value = '';
            },

            submit(event) {
                if (!this.strokes.length) {
                    event.preventDefault();
                    alert('Silakan bubuhkan tanda tangan Anda terlebih dahulu.');
                    return;
                }
                this.$refs.signatureInput.value = this.canvas.toDataURL('image/png');
                HTMLFormElement.prototype.submit.call(event.currentTarget);
            }
        };
    }
</script>

@if($formBelumDibuat ?? false)
<script>
    document.addEventListener('DOMContentLoaded', function() {
        if (typeof Swal !== 'undefined') {
            Swal.fire({
                icon: 'info',
                title: 'Formulir Belum Dibuat',
                text: 'Formulir FR.AK.07 (Penyesuaian yang Wajar dan Beralasan) untuk skema sertifikasi ini belum dibuat atau belum ditandatangani oleh Asesor / Admin.',
                confirmButtonColor: '#2563eb',
                confirmButtonText: 'Saya Mengerti',
                customClass: {
                    popup: 'swal2-modern-popup'
                }
            });
        }
    });
</script>
@endif
@endsection
