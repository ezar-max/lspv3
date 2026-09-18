@extends('tata-letak.dasbor')

@section('judul', 'FR.AK.07 - Kesepakatan Penyesuaian yang Wajar')

@push('css')
    <!-- Signature Pad CDN -->
    <script src="https://cdn.jsdelivr.net/npm/signature_pad@4.1.7/dist/signature_pad.umd.min.js"></script>
@endpush

@section('konten')
@php
    $asesorNama = $pendaftaran->asesor->nama_lengkap ?? $pendaftaran->jadwal?->asesor?->nama_lengkap ?? 'Asesor LSP';
    $asesorMet = $pendaftaran->asesor->nomor_registrasi ?? $pendaftaran->jadwal?->asesor?->nomor_registrasi ?? 'MET.000.004455';
    $profileTtd = auth()->user()->tanda_tangan;
    $isSignedByAsesi = $ak07 ? !empty($ak07->asesi_signature) : false;
    $isConfirmed = $ak07 ? $ak07->isConfirmed() : false;
    $savedChecklist = (array) ($ak07->items_checklist ?? []);
    if (empty($savedChecklist)) {
        $savedChecklist = \App\Models\AssessmentAk07Adjustment::defaultChecklistItems();
    }
    $selectedPotensi = (int) ($ak07->potensi_asesi ?? 1);
    $selectedFase = $ak07->fase_penggunaan ?? 'saat_pra_asesmen';
    $asesorSigToShow = ($ak07 && $ak07->asesor_signature) ? $ak07->asesor_signature : ($pendaftaran->tanda_tangan_asesor_ak01 ?? $pendaftaran->asesor?->tanda_tangan);

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
                @if($isConfirmed)
                    <span class="px-3 py-1 rounded-full bg-emerald-50 text-emerald-700 border border-emerald-200 text-xs font-bold flex items-center gap-1.5 shadow-2xs">
                        <span class="w-2 h-2 rounded-full bg-emerald-500 animate-pulse"></span>
                        CONFIRMED &bull; Terkonfirmasi & Terkunci
                    </span>
                @else
                    <span class="px-3 py-1 rounded-full bg-amber-50 text-amber-700 border border-amber-200 text-xs font-bold flex items-center gap-1.5 shadow-2xs">
                        <span class="w-2 h-2 rounded-full bg-amber-500"></span>
                        {{ $isSignedByAsesi ? 'Menunggu Asesor' : 'Perlu Tanda Tangan Asesi' }}
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
                    {{ $ak07->acuan_pembanding_disepakati ?? "Standar Kompetensi Kerja Nasional Indonesia (SKKNI) {$pendaftaran->skema->nama_skema}" }}
                </div>
            </div>

            <div class="space-y-1">
                <span class="font-bold text-slate-700 block">
                    Metode Asesmen Disepakati:
                </span>
                <div class="p-2.5 rounded-xl border border-slate-200 bg-slate-50/60 text-xs text-slate-800 font-medium">
                    {{ $ak07->metode_disepakati ?? 'Observasi Demonstrasi Langsung & Wawancara Terstruktur Klarifikasi' }}
                </div>
            </div>

            <div class="space-y-1">
                <span class="font-bold text-slate-700 block">
                    Instrumen Pengganti / Penyesuaian:
                </span>
                <div class="p-2.5 rounded-xl border border-slate-200 bg-slate-50/60 text-xs text-slate-800 font-medium">
                    {{ $ak07->instrumen_disepakati ?? 'FR.IA.01 (Observasi Praktik), FR.IA.03 (Pertanyaan Pendukung Observasi)' }}
                </div>
            </div>
        </div>

        <div class="space-y-1 pt-1 text-xs">
            <span class="font-bold text-slate-700 block">
                Catatan Tambahan Asesor:
            </span>
            <div class="p-2.5 rounded-xl border border-slate-200 bg-slate-50/60 text-xs text-slate-700 italic">
                {{ ($ak07 && $ak07->catatan_asesor) ? $ak07->catatan_asesor : 'Seluruh proses asesmen disepakati dapat dilaksanakan dengan penyesuaian yang wajar sesuai kesepakatan bersama.' }}
            </div>
        </div>
    </div>

    <!-- 5. BAGIAN PENGESAHAN TANDA TANGAN DIGITAL & STATUS KESEPAKATAN -->
    <div class="bg-white rounded-2xl border border-slate-200/90 shadow-2xs p-4 sm:p-5 space-y-4" x-data="asesiAk07App()">
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
                            Tertera TTD ({{ $ak07->asesor_signed_at ? \Carbon\Carbon::parse($ak07->asesor_signed_at)->format('d/m/Y H:i') : 'Tersimpan' }})
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
                    @if($isSignedByAsesi)
                        <span class="px-2 py-0.5 rounded-full bg-emerald-50 text-emerald-700 border border-emerald-200 text-[10px] font-bold">
                            Disetujui Asesi ({{ $ak07->asesi_signed_at ? \Carbon\Carbon::parse($ak07->asesi_signed_at)->format('d/m/Y H:i') : 'Tersimpan' }})
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

                @if($isSignedByAsesi)
                    <div class="p-3 border border-emerald-200 bg-emerald-50/30 rounded-xl flex flex-col items-center justify-center h-36 text-center space-y-1">
                        <img src="{{ asset($ak07->asesi_signature) }}" alt="TTD Asesi" class="max-h-20 object-contain mb-1">
                        <span class="text-[10px] text-emerald-700 font-bold">
                            Telah Ditandatangani & Disetujui ({{ $ak07->asesi_signed_at ? \Carbon\Carbon::parse($ak07->asesi_signed_at)->format('d/m/Y H:i') : '' }})
                        </span>
                    </div>
                @else
                    <form id="formSignAsesi" action="{{ route('asesi.ak07.sign-asesi', $pendaftaran->id) }}" method="POST" class="space-y-2.5">
                        @csrf
                        <input type="hidden" name="tanda_tangan_asesi" id="inputTtdAsesi" value="{{ $profileTtd }}">

                        <div class="flex items-center gap-3">
                            @if(!empty($profileTtd))
                                <label class="inline-flex items-center gap-1.5 cursor-pointer text-[11px] font-medium text-slate-700">
                                    <input type="radio" name="sigMode" value="profile" x-model="signatureMode" class="accent-blue-600">
                                    <span>Gunakan TTD Akun Profil</span>
                                </label>
                            @endif
                            <label class="inline-flex items-center gap-1.5 cursor-pointer text-[11px] font-medium text-slate-700">
                                <input type="radio" name="sigMode" value="canvas" x-model="signatureMode" class="accent-blue-600">
                                <span>Gores TTD Baru</span>
                            </label>
                        </div>

                        <!-- Canvas Box -->
                        <div x-show="signatureMode === 'canvas'" class="space-y-1">
                            <div class="border-2 border-dashed border-slate-300 rounded-xl bg-white relative p-1">
                                <canvas id="canvasAk07Asesi" class="w-full h-24 rounded-lg cursor-crosshair touch-none"></canvas>
                                <button type="button" @click="clearCanvas()" class="absolute top-1 right-1 px-1.5 py-0.5 bg-slate-100 text-slate-600 rounded text-[9px] font-bold">
                                    Bersihkan
                                </button>
                            </div>
                        </div>

                        <!-- Preview Profil Box -->
                        <div x-show="signatureMode === 'profile'" class="p-2 border border-slate-200 rounded-xl bg-white flex items-center justify-center h-24">
                            @if(!empty($profileTtd))
                                <img src="{{ asset($profileTtd) }}" alt="TTD Profil" class="max-h-20 object-contain">
                            @else
                                <span class="text-slate-400 italic">Belum ada tanda tangan di profil.</span>
                            @endif
                        </div>

                        <button type="button" @click="submitSignature()" class="w-full py-2.5 bg-blue-600 hover:bg-blue-700 text-white rounded-xl font-bold text-xs shadow-xs transition flex items-center justify-center cursor-pointer">
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
    function asesiAk07App() {
        return {
            signatureMode: '{{ !empty($profileTtd) ? "profile" : "canvas" }}',
            canvasSignatureData: '',
            signaturePad: null,

            init() {
                this.$nextTick(() => {
                    this.initSignaturePad();
                });
            },

            initSignaturePad() {
                const canvas = document.getElementById('canvasAk07Asesi');
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

            submitSignature() {
                if (this.signatureMode === 'canvas' && this.signaturePad && !this.signaturePad.isEmpty()) {
                    this.canvasSignatureData = this.signaturePad.toDataURL('image/png');
                    document.getElementById('inputTtdAsesi').value = this.canvasSignatureData;
                } else if (this.signatureMode === 'profile') {
                    document.getElementById('inputTtdAsesi').value = '{{ $profileTtd }}';
                }
                document.getElementById('formSignAsesi').submit();
            }
        };
    }
</script>
@endsection
