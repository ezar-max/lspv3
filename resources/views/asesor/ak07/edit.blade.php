@extends('tata-letak.dasbor')

@section('judul', 'FR.AK.07 - Ceklis Penyesuaian yang Wajar')

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
        }
    </style>
@endpush

@section('konten')
@php
    $asesorNama = $pendaftaran->asesor->nama_lengkap ?? auth()->user()->nama_lengkap;
    $asesorMet = $pendaftaran->asesor->nomor_registrasi ?? auth()->user()->nomor_registrasi ?? 'MET.000.004455';
    $profileTtd = auth()->user()->tanda_tangan ?: $pendaftaran->tanda_tangan_asesor;
    $isConfirmed = $ak07->isConfirmed();
    $savedChecklist = (array) ($ak07->items_checklist ?? []);
    $selectedPotensi = (int) ($ak07->potensi_asesi ?? 1);
    $selectedFase = $ak07->fase_penggunaan ?? 'saat_pra_asesmen';
@endphp

<div class="max-w-6xl mx-auto px-2 sm:px-4 py-3 space-y-4" x-data="ak07App()" x-cloak>

    <!-- =========================================================================
         TOP BREADCRUMB & ACTION BAR
         ========================================================================= -->
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-2 pb-1 no-print">
        <div class="flex items-center gap-2 text-xs text-slate-500">
            <a href="{{ route('asesor.dashboard') }}" class="hover:text-blue-600 font-medium">Dashboard</a>
            <span>/</span>
            <a href="{{ route('asesor.daftar-peserta') }}" class="hover:text-blue-600 font-medium">Penilaian Peserta</a>
            <span>/</span>
            <span class="text-slate-800 font-bold">FR.AK.07</span>
        </div>

        <div class="flex items-center gap-2">
            <a href="{{ (url()->previous() && url()->previous() !== url()->current()) ? url()->previous() : route('asesor.daftar-peserta') }}" 
               onclick="if (document.referrer && document.referrer !== window.location.href) { window.location.href = document.referrer; return false; } else if (window.history.length > 1) { window.history.back(); return false; }"
               class="inline-flex items-center px-3 py-1.5 rounded-lg border border-slate-200 bg-white text-xs font-semibold text-slate-700 hover:bg-slate-50 transition-colors shadow-2xs cursor-pointer">
                <span>&larr; Kembali</span>
            </a>
            @if($ak07->exists)
                <a href="{{ route('asesor.pendaftaran.ak07.cetak', $pendaftaran->id) }}" target="_blank" class="inline-flex items-center px-3 py-1.5 rounded-lg border border-slate-200 bg-white text-xs font-semibold text-slate-700 hover:bg-slate-50 transition-colors shadow-2xs">
                    <span>Cetak Lembar A4</span>
                </a>
            @endif
        </div>
    </div>

    <!-- NOTIFIKASI SUKSES / ERROR -->
    @if(session('sukses'))
        <div class="p-3.5 bg-emerald-50 border border-emerald-200 rounded-xl text-emerald-800 text-xs font-semibold flex items-center gap-2.5 shadow-2xs">
            <span>{{ session('sukses') }}</span>
        </div>
    @endif

    @if(session('error'))
        <div class="p-3.5 bg-rose-50 border border-rose-200 rounded-xl text-rose-800 text-xs font-semibold flex items-center gap-2.5 shadow-2xs">
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
                        DRAFT &bull; Perlu Pengesahan
                    </span>
                @endif
            </div>
        </div>

        <!-- Metadata Grid Compact -->
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
                    {{ $pendaftaran->asesi->nama_lengkap ?? '-' }}
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
                    {{ $pendaftaran->jadwal->nama_tuk ?? 'TUK Mandiri LSP' }}
                </div>
                <div class="text-[10px] text-slate-500">
                    {{ $pendaftaran->jadwal?->tanggal_mulai ? \Carbon\Carbon::parse($pendaftaran->jadwal->tanggal_mulai)->translatedFormat('d M Y') : 'Sesuai Jadwal' }}
                </div>
            </div>
        </div>
    </div>

    <!-- =========================================================================
         FORMULIR PENYESUAIAN ASESMEN (FR.AK.07)
         ========================================================================= -->
    <form id="formAk07" action="{{ route('asesor.pendaftaran.ak07.update', $pendaftaran->id) }}" method="POST" class="space-y-4">
        @csrf
        <input type="hidden" name="aksi" id="inputAksi" value="draft">
        <input type="hidden" name="tanda_tangan_asesor" id="inputTtdAsesor" value="{{ $profileTtd }}">

        <!-- 1. BAGIAN POTENSI ASESI (1 s.d. 5) -->
        <div class="bg-white rounded-2xl border border-slate-200/90 shadow-2xs p-4 sm:p-5 space-y-3">
            <div class="flex items-center gap-2 text-sm font-bold text-slate-900 border-b border-slate-100 pb-2.5">
                <span class="w-6 h-6 rounded-lg bg-blue-50 text-blue-700 flex items-center justify-center text-xs font-black">1</span>
                <span>Potensi Asesi (Karakteristik & Latar Belakang Kandidat)</span>
            </div>
            <p class="text-xs text-slate-500">
                Pilih kategori potensi kandidat asesi yang mendasari perlunya kontekstualisasi penyesuaian asesmen:
            </p>

            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-2 pt-1">
                @foreach($potensiDefinitions as $pVal => $pLabel)
                    <label class="relative flex items-start gap-2.5 p-3 rounded-xl border transition-all cursor-pointer text-xs {{ $selectedPotensi === $pVal ? 'border-blue-500 bg-blue-50/50 text-slate-900 font-semibold shadow-2xs' : 'border-slate-200 bg-white text-slate-600 hover:bg-slate-50/80' }}">
                        <input type="radio" name="potensi_asesi" value="{{ $pVal }}" {{ $selectedPotensi === $pVal ? 'checked' : '' }} class="mt-0.5 accent-blue-600">
                        <div class="space-y-0.5 leading-snug">
                            <span class="inline-block px-1.5 py-0.2 rounded bg-slate-200/80 text-[10px] font-bold text-slate-700">Kategori {{ $pVal }}</span>
                            <div class="text-[11px]">{{ $pLabel }}</div>
                        </div>
                    </label>
                @endforeach
            </div>
        </div>

        <!-- 2. BAGIAN FASE PENGGUNAAN PENYESUAIAN -->
        <div class="bg-white rounded-2xl border border-slate-200/90 shadow-2xs p-4 sm:p-5 space-y-3">
            <div class="flex items-center gap-2 text-sm font-bold text-slate-900 border-b border-slate-100 pb-2.5">
                <span class="w-6 h-6 rounded-lg bg-blue-50 text-blue-700 flex items-center justify-center text-xs font-black">2</span>
                <span>Fase Pelaksanaan Penyesuaian</span>
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-3 gap-2 pt-1 text-xs">
                @php
                    $faseList = [
                        'pra_asesmen' => ['title' => 'Pra Asesmen', 'desc' => 'Diterapkan sebelum tahapan asesmen dimulai (misal: verifikasi berkas & konsultasi pra-uji)'],
                        'saat_pra_asesmen' => ['title' => 'Pada Saat Asesmen', 'desc' => 'Diterapkan secara langsung selama sesi demonstrasi praktik, ujian tertulis, atau wawancara'],
                        'setelah_pra_asesmen' => ['title' => 'Setelah Asesmen', 'desc' => 'Diterapkan pada tahap penyusunan rekomendasi, umpan balik, dan pengumpulan bukti tambahan'],
                    ];
                @endphp
                @foreach($faseList as $fKey => $fInfo)
                    <label class="relative flex items-start gap-2.5 p-3 rounded-xl border transition-all cursor-pointer {{ $selectedFase === $fKey ? 'border-blue-500 bg-blue-50/50 text-slate-900 font-semibold shadow-2xs' : 'border-slate-200 bg-white text-slate-600 hover:bg-slate-50/80' }}">
                        <input type="radio" name="fase_penggunaan" value="{{ $fKey }}" {{ $selectedFase === $fKey ? 'checked' : '' }} class="mt-0.5 accent-blue-600">
                        <div class="space-y-0.5">
                            <div class="font-bold text-slate-800">{{ $fInfo['title'] }}</div>
                            <div class="text-[11px] text-slate-500 leading-tight">{{ $fInfo['desc'] }}</div>
                        </div>
                    </label>
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

                    <div class="border border-slate-200 rounded-xl overflow-hidden transition-all" x-data="{ isNeed: {{ $isPerluSaved ? 'true' : 'false' }} }">
                        <!-- Category Header Bar -->
                        <div class="p-3.5 bg-slate-50/80 flex flex-col sm:flex-row sm:items-center justify-between gap-2.5 border-b border-slate-100">
                            <div class="flex items-center gap-2.5">
                                <span class="w-6 h-6 rounded-md bg-white border border-slate-200 text-slate-700 font-bold text-xs flex items-center justify-center shrink-0">
                                    {{ $cId }}
                                </span>
                                <div class="font-bold text-xs sm:text-sm text-slate-800">
                                    {{ $crit['title'] }}
                                </div>
                            </div>

                            <!-- Perlu Penyesuaian Switch (Ya / Tidak) -->
                            <div class="inline-flex bg-white p-1 rounded-lg border border-slate-200 shadow-2xs text-xs">
                                <label class="px-2.5 py-1 rounded-md cursor-pointer transition-all flex items-center gap-1.5 font-bold" :class="!isNeed ? 'bg-slate-700 text-white' : 'text-slate-500 hover:text-slate-800'">
                                    <input type="radio" name="items_checklist[{{ $cId }}][perlu]" value="0" @change="isNeed = false" :checked="!isNeed" class="sr-only">
                                    <span>Tidak Perlu</span>
                                </label>
                                <label class="px-2.5 py-1 rounded-md cursor-pointer transition-all flex items-center gap-1.5 font-bold" :class="isNeed ? 'bg-amber-600 text-white' : 'text-slate-500 hover:text-slate-800'">
                                    <input type="radio" name="items_checklist[{{ $cId }}][perlu]" value="1" @change="isNeed = true" :checked="isNeed" class="sr-only">
                                    <span>Perlu Penyesuaian</span>
                                </label>
                            </div>
                        </div>

                        <!-- Expandable Options Body when "Perlu" is checked -->
                        <div x-show="isNeed" x-transition class="p-3.5 bg-amber-50/20 space-y-3 text-xs border-t border-amber-100">
                            <div>
                                <span class="font-bold text-slate-700 block mb-1.5">
                                    Pilih Opsi Bentuk Penyesuaian yang Disepakati:
                                </span>
                                <div class="grid grid-cols-1 sm:grid-cols-2 gap-2">
                                    @foreach($crit['sub_options'] as $subKey => $subLabel)
                                        <label class="flex items-start gap-2 p-2 rounded-lg bg-white border border-slate-200 hover:border-amber-400 cursor-pointer transition-colors">
                                            <input type="checkbox" name="items_checklist[{{ $cId }}][opsi][]" value="{{ $subKey }}" {{ in_array($subKey, $opsiSaved) ? 'checked' : '' }} class="mt-0.5 accent-amber-600">
                                            <span class="text-[11px] text-slate-700 leading-snug">{{ $subLabel }}</span>
                                        </label>
                                    @endforeach
                                </div>
                            </div>

                            <div>
                                <label class="font-bold text-slate-700 block mb-1">
                                    Uraian Detail / Catatan Khusus Penyesuaian Kategori {{ $cId }}:
                                </label>
                                <input type="text" name="items_checklist[{{ $cId }}][keterangan]" value="{{ $ketSaved }}" class="w-full px-3 py-1.5 rounded-lg border border-slate-200 bg-white text-xs focus:ring-2 focus:ring-blue-500 focus:border-blue-500" placeholder="Contoh: Menggunakan teks soal dengan font 16pt dan pendamping juru bahasa...">
                            </div>
                        </div>
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
                    <label class="font-bold text-slate-700 block">
                        Acuan Pembanding Disepakati:
                    </label>
                    <textarea name="acuan_pembanding_disepakati" rows="3" class="w-full p-2.5 rounded-xl border border-slate-200 text-xs focus:ring-2 focus:ring-blue-500" placeholder="Tuliskan standar kompetensi / benchmark pembanding yang disepakati...">{{ old('acuan_pembanding_disepakati', $ak07->acuan_pembanding_disepakati ?? "Standar Kompetensi Kerja Nasional Indonesia (SKKNI) {$pendaftaran->skema->nama_skema}") }}</textarea>
                </div>

                <div class="space-y-1">
                    <label class="font-bold text-slate-700 block">
                        Metode Asesmen Disepakati:
                    </label>
                    <textarea name="metode_disepakati" rows="3" class="w-full p-2.5 rounded-xl border border-slate-200 text-xs focus:ring-2 focus:ring-blue-500" placeholder="Tuliskan metode asesmen yang telah disesuaikan...">{{ old('metode_disepakati', $ak07->metode_disepakati ?? 'Observasi Demonstrasi Langsung & Wawancara Terstruktur Klarifikasi') }}</textarea>
                </div>

                <div class="space-y-1">
                    <label class="font-bold text-slate-700 block">
                        Instrumen Pengganti / Penyesuaian:
                    </label>
                    <textarea name="instrumen_disepakati" rows="3" class="w-full p-2.5 rounded-xl border border-slate-200 text-xs focus:ring-2 focus:ring-blue-500" placeholder="Tuliskan instrumen pendukung (FR.IA) yang disesuaikan...">{{ old('instrumen_disepakati', $ak07->instrumen_disepakati ?? 'FR.IA.01 (Observasi Praktik), FR.IA.03 (Pertanyaan Pendukung Observasi)') }}</textarea>
                </div>
            </div>

            <div class="space-y-1 pt-1 text-xs">
                <label class="font-bold text-slate-700 block">
                    Catatan Tambahan Asesor:
                </label>
                <textarea name="catatan_asesor" rows="2" class="w-full p-2.5 rounded-xl border border-slate-200 text-xs focus:ring-2 focus:ring-blue-500" placeholder="Catatan khusus atau instruksi lanjutan terkait pelaksanaan penyesuaian asesmen...">{{ old('catatan_asesor', $ak07->catatan_asesor) }}</textarea>
            </div>
        </div>

        <!-- 5. BAGIAN PENGESAHAN TANDA TANGAN ASESOR & STATUS ASESI -->
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
                        @if($ak07->asesor_signature)
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

                    <!-- Tanda Tangan Selector Asesor -->
                    <div class="space-y-2">
                        <div class="flex items-center gap-2">
                            @if(!empty($profileTtd))
                                <label class="inline-flex items-center gap-1.5 cursor-pointer text-[11px] font-medium text-slate-700">
                                    <input type="radio" name="sigMode" value="profile" x-model="signatureMode" class="accent-blue-600">
                                    <span>Gunakan TTD Akun Profil</span>
                                </label>
                            @endif
                            <label class="inline-flex items-center gap-1.5 cursor-pointer text-[11px] font-medium text-slate-700">
                                <input type="radio" name="sigMode" value="canvas" x-model="signatureMode" class="accent-blue-600">
                                <span>Gores TTD Baru di Layar</span>
                            </label>
                        </div>

                        <!-- Canvas Box -->
                        <div x-show="signatureMode === 'canvas'" class="space-y-1.5">
                            <div class="border-2 border-dashed border-slate-300 rounded-xl bg-white relative p-1">
                                <canvas id="canvasAk07Asesor" class="w-full h-28 rounded-lg cursor-crosshair touch-none"></canvas>
                                <button type="button" @click="clearCanvas()" class="absolute top-2 right-2 px-2 py-1 bg-slate-100 hover:bg-slate-200 text-slate-600 rounded text-[10px] font-bold transition">
                                    Bersihkan
                                </button>
                            </div>
                            <span class="text-[10px] text-slate-400 italic">Tandatangani di atas kotak putih menggunakan sentuhan layar atau mouse.</span>
                        </div>

                        <!-- Preview Profil Box -->
                        <div x-show="signatureMode === 'profile'" class="p-2 border border-slate-200 rounded-xl bg-white flex items-center justify-center h-28">
                            @if(!empty($profileTtd))
                                <img src="{{ asset($profileTtd) }}" alt="TTD Profil" class="max-h-24 object-contain">
                            @else
                                <span class="text-slate-400 italic">Belum ada tanda tangan di profil.</span>
                            @endif
                        </div>
                    </div>
                </div>

                <!-- Sisi Asesi -->
                <div class="border border-slate-200 rounded-xl p-3.5 space-y-3 bg-slate-50/50">
                    <div class="flex items-center justify-between border-b border-slate-200 pb-2">
                        <span class="font-bold text-slate-800">Asesi (Kandidat)</span>
                        @if($ak07->asesi_signature)
                            <span class="px-2 py-0.5 rounded-full bg-emerald-50 text-emerald-700 border border-emerald-200 text-[10px] font-bold">
                                Disetujui Asesi ({{ $ak07->asesi_signed_at ? \Carbon\Carbon::parse($ak07->asesi_signed_at)->format('d/m/Y H:i') : 'Tersimpan' }})
                            </span>
                        @else
                            <span class="px-2 py-0.5 rounded-full bg-slate-200 text-slate-600 text-[10px] font-bold">
                                Menunggu Tanda Tangan Asesi
                            </span>
                        @endif
                    </div>

                    <div class="text-slate-600">
                        <strong>{{ $pendaftaran->asesi->nama_lengkap ?? '-' }}</strong>
                    </div>

                    <div class="p-3 border border-slate-200 rounded-xl bg-white flex flex-col items-center justify-center h-36 text-center space-y-1">
                        @if($ak07->asesi_signature)
                            <img src="{{ asset($ak07->asesi_signature) }}" alt="TTD Asesi" class="max-h-24 object-contain">
                            <span class="text-[10px] text-emerald-700 font-bold">Kesepakatan telah disetujui asesi</span>
                        @else
                            <span class="text-slate-500 font-medium">Asesi dapat menandatangani dokumen ini melalui portal akun asesi (Menu FR.AK.07).</span>
                        @endif
                    </div>
                </div>

            </div>
        </div>

        <!-- =========================================================================
             BOTTOM ACTION BAR: SIMPAN DRAFT / SAHKAN
             ========================================================================= -->
        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3 pt-2 pb-6 no-print">
            <div class="text-xs text-slate-500">
                Setiap perubahan pada dokumen yang telah disahkan akan mengembalikan status menjadi draft untuk konfirmasi ulang.
            </div>

            <div class="flex items-center gap-2.5">
                <button type="button" @click="submitAsDraft()" :disabled="isSubmitting" class="px-4 py-2.5 rounded-xl border border-slate-300 bg-white hover:bg-slate-50 text-slate-700 text-xs font-bold transition shadow-2xs">
                    <span x-text="isSubmitting ? 'Menyimpan...' : 'Simpan Draft'"></span>
                </button>

                <button type="button" @click="openConfirmModal()" :disabled="isSubmitting" class="px-5 py-2.5 rounded-xl bg-blue-600 hover:bg-blue-700 text-white text-xs font-bold shadow-xs transition flex items-center">
                    <span>Sahkan FR.AK.07 (Confirmed)</span>
                </button>
            </div>
        </div>

    </form>

    <!-- =========================================================================
         MODAL KONFIRMASI PENGESAHAN DOKUMEN FR.AK.07
         ========================================================================= -->
    <div x-show="showConfirmModal" class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-slate-900/60 backdrop-blur-xs no-print" style="display: none;">
        <div class="bg-white rounded-2xl max-w-md w-full p-5 shadow-2xl border border-slate-100 space-y-3 animate-in fade-in zoom-in duration-150" @click.away="showConfirmModal = false">
            <div class="flex items-center gap-3">
                <div class="w-10 h-10 rounded-xl bg-blue-50 text-blue-700 flex items-center justify-center shrink-0 font-bold text-xs">
                    TTD
                </div>
                <div>
                    <h3 class="font-bold text-slate-900 text-sm">Pengesahan Dokumen FR.AK.07</h3>
                    <p class="text-xs text-slate-500">Penyesuaian yang Wajar dan Beralasan</p>
                </div>
            </div>

            <p class="text-xs text-slate-600 leading-relaxed">
                Apakah Anda yakin data penyesuaian yang disepakati telah sesuai dengan kebutuhan kandidat dan standar kompetensi BNSP? Dokumen akan dikunci dengan status <strong>CONFIRMED</strong>.
            </p>

            <div class="flex items-center justify-end gap-2 pt-2 border-t border-slate-100">
                <button type="button" @click="showConfirmModal = false" class="px-3.5 py-2 rounded-xl border border-slate-200 text-xs font-semibold text-slate-700 hover:bg-slate-50 transition">
                    Batal
                </button>
                <button type="button" @click="submitAsConfirmed()" class="px-4 py-2 rounded-xl bg-blue-600 hover:bg-blue-700 text-white text-xs font-bold shadow-xs transition">
                    Ya, Sahkan Dokumen
                </button>
            </div>
        </div>
    </div>

</div>

<!-- Alpine.js Application Component -->
<script>
    function ak07App() {
        return {
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

            initSignaturePad() {
                const canvas = document.getElementById('canvasAk07Asesor');
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

            prepareSignature() {
                if (this.signatureMode === 'canvas' && this.signaturePad && !this.signaturePad.isEmpty()) {
                    this.canvasSignatureData = this.signaturePad.toDataURL('image/png');
                    const hiddenInput = document.getElementById('inputTtdAsesor');
                    if (hiddenInput) {
                        hiddenInput.value = this.canvasSignatureData;
                    }
                } else if (this.signatureMode === 'profile') {
                    const hiddenInput = document.getElementById('inputTtdAsesor');
                    if (hiddenInput) {
                        hiddenInput.value = '{{ $profileTtd }}';
                    }
                }
            },

            submitAsDraft() {
                this.prepareSignature();
                document.getElementById('inputAksi').value = 'draft';
                this.isSubmitting = true;
                document.getElementById('formAk07').submit();
            },

            openConfirmModal() {
                this.prepareSignature();
                this.showConfirmModal = true;
            },

            submitAsConfirmed() {
                this.showConfirmModal = false;
                this.prepareSignature();
                document.getElementById('inputAksi').value = 'confirm';
                this.isSubmitting = true;
                document.getElementById('formAk07').submit();
            }
        };
    }
</script>
@endsection
