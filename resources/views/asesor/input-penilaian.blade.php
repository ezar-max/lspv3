@extends('tata-letak.dasbor')

@section('judul', 'Verifikasi FR.APL.02 Asesor')

@push('css')
    <!-- Signature Pad CDN -->
    <script src="https://cdn.jsdelivr.net/npm/signature_pad@4.1.7/dist/signature_pad.umd.min.js"></script>
    <style>
        .custom-scrollbar::-webkit-scrollbar {
            width: 5px;
            height: 5px;
        }
        .custom-scrollbar::-webkit-scrollbar-track {
            background: #f1f5f9;
        }
        .custom-scrollbar::-webkit-scrollbar-thumb {
            background: #cbd5e1;
            border-radius: 4px;
        }
    </style>
@endpush

@section('konten')
@php
    $isLocked = $isLocked ?? ($pendaftaran ? $pendaftaran->isApl02Approved() : false);
    $totalUnits = ($pendaftaran && $pendaftaran->skema) ? $pendaftaran->skema->unitKompetensi->count() : 0;
    $jawabanMap = $jawabanMap ?? (($pendaftaran && $pendaftaran->jawabanApl02) ? $pendaftaran->jawabanApl02->keyBy('elemen_id') : collect([]));
    $buktiApl02Map = $buktiApl02Map ?? (($pendaftaran && $pendaftaran->buktiApl02) ? $pendaftaran->buktiApl02->groupBy('elemen_id') : collect([]));
@endphp

<div x-data="asesorVerifikasiApp()" class="min-h-screen bg-slate-50/70 pb-28">
    <div class="max-w-6xl mx-auto px-3 sm:px-5 pt-3 pb-6 space-y-4">

        <!-- =========================================================================
             TOP NAV & INFORMASI ASESOR
             ========================================================================= -->
        <div class="flex items-center justify-between gap-3 flex-wrap text-xs">
            <div class="flex items-center gap-2 flex-wrap">
                <a href="{{ (url()->previous() && url()->previous() !== url()->current()) ? url()->previous() : route('asesor.daftar-peserta') }}" 
                   onclick="if (document.referrer && document.referrer !== window.location.href) { window.location.href = document.referrer; return false; } else if (window.history.length > 1) { window.history.back(); return false; }"
                   class="px-3 py-1.5 rounded-lg bg-white border border-slate-200 hover:bg-slate-50 text-slate-700 font-semibold transition-colors inline-flex items-center gap-1.5 shadow-2xs cursor-pointer">
                    <span>&larr; Kembali</span>
                </a>
            </div>

            <div class="flex items-center gap-2">
                <span class="text-slate-500 font-medium hidden sm:inline">Asesor Penilai:</span>
                <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-lg bg-blue-50 text-blue-700 border border-blue-200/80 font-bold">
                    <span>{{ auth()->user()->nama_lengkap }}</span>
                    <span class="text-[10px] font-mono text-blue-600/80">({{ auth()->user()->nomor_registrasi ?? 'MET.000.00' . auth()->id() }})</span>
                </span>
            </div>
        </div>

        <!-- =========================================================================
             HERO CARD: INFORMASI ASESI & STATUS WORKFLOW (COMPACT)
             ========================================================================= -->
        <div class="bg-white rounded-xl border border-slate-200 shadow-2xs p-4 sm:p-5 flex flex-col md:flex-row md:items-center md:justify-between gap-4">
            <!-- Left Metadata -->
            <div class="space-y-1.5">
                <div class="flex items-center gap-2 flex-wrap">
                    <span class="px-2 py-0.5 bg-blue-50 text-blue-700 border border-blue-200 text-[10px] font-bold rounded uppercase tracking-wider">
                        FR.APL.02 Verifikasi Asesor
                    </span>
                    <span class="px-2 py-0.5 bg-slate-100 text-slate-700 border border-slate-200 text-[11px] font-mono font-bold rounded">
                        {{ $pendaftaran->nomor_pendaftaran }}
                    </span>
                    <span class="text-[11px] text-slate-400 font-medium">
                        Diajukan: {{ $pendaftaran->tanggal_submit_apl02 ? \Carbon\Carbon::parse($pendaftaran->tanggal_submit_apl02)->format('d/m/Y H:i') : ($pendaftaran->created_at ? $pendaftaran->created_at->format('d/m/Y') : '-') }}
                    </span>
                </div>
                
                <h1 class="text-xl sm:text-2xl font-bold text-slate-900 tracking-tight">
                    {{ $pendaftaran->asesi->nama_lengkap }}
                </h1>
                
                <div class="flex items-center gap-y-1 gap-x-4 text-xs text-slate-500 flex-wrap">
                    <div class="flex items-center gap-1.5">
                        <span>Skema: <strong class="text-slate-700">{{ $pendaftaran->skema->nama_skema }}</strong> ({{ $pendaftaran->skema->kode_skema }})</span>
                    </div>
                    <div class="flex items-center gap-1.5">
                        <span>{{ $pendaftaran->asesi->profilAsesi->nama_sekolah_instansi ?? 'SMKN 1 Gunungputri' }}</span>
                    </div>
                </div>
            </div>

            <!-- Right Status Badges & Live KUK Counter -->
            <div class="flex items-center gap-2.5 flex-wrap self-start md:self-center shrink-0">
                <!-- Live Counter Header Indicator -->
                <span class="inline-flex items-center gap-1 px-3 py-1.5 rounded-lg border font-bold text-xs"
                      :class="allVerified ? 'bg-emerald-50 text-emerald-700 border-emerald-200' : 'bg-amber-50 text-amber-700 border-amber-200'">
                    <span x-show="allVerified">✓ Semua KUK Terverifikasi K (<span x-text="verifiedCount + '/' + totalKuk"></span>)</span>
                    <span x-show="!allVerified"><span x-text="verifiedCount"></span> K &bull; <span x-text="bkCount" class="text-rose-600"></span> BK dari <span x-text="totalKuk"></span> KUK</span>
                </span>

                <!-- Status APL.02 -->
                @if($pendaftaran->isApl02Approved())
                    <span class="inline-flex items-center gap-1 px-3 py-1.5 rounded-lg bg-emerald-50 text-emerald-700 border border-emerald-200 font-bold text-xs">
                        <span>Disetujui (Approved)</span>
                    </span>
                @elseif($pendaftaran->isApl02Revision())
                    <span class="inline-flex items-center gap-1 px-3 py-1.5 rounded-lg bg-rose-50 text-rose-700 border border-rose-200 font-bold text-xs animate-pulse">
                        <span>Perlu Revisi Asesi</span>
                    </span>
                @elseif($pendaftaran->isApl02UnderReview())
                    <span class="inline-flex items-center gap-1 px-3 py-1.5 rounded-lg bg-blue-50 text-blue-700 border border-blue-200 font-bold text-xs">
                        <span>Sedang Diperiksa</span>
                    </span>
                @elseif($pendaftaran->isApl02Submitted())
                    <span class="inline-flex items-center gap-1 px-3 py-1.5 rounded-lg bg-amber-50 text-amber-700 border border-amber-200 font-bold text-xs">
                        <span>Menunggu Pemeriksaan</span>
                    </span>
                @else
                    <span class="inline-flex items-center gap-1 px-3 py-1.5 rounded-lg bg-slate-100 text-slate-600 border border-slate-200 font-bold text-xs">
                        <span>Draft Asesi</span>
                    </span>
                @endif
            </div>
        </div>



        <!-- =========================================================================
             FORM VERIFIKASI FR.APL.02
             ========================================================================= -->
        <form id="form-penilaian-asesor" 
              action="{{ route('asesor.input-penilaian.simpan', $pendaftaran->id) }}" 
              method="POST" 
              @submit="handleSubmit($event)"
              class="space-y-4">
            @csrf

            <!-- HEADER FORMULIR: FR.APL.02 (SIMPLE & MODERN) -->
            <div class="bg-white rounded-xl border border-slate-200 shadow-2xs px-4 sm:px-5 py-3.5 flex flex-col sm:flex-row sm:items-center justify-between gap-3">
                <div class="space-y-0.5">
                    <div class="text-[11px] font-bold text-indigo-600 uppercase tracking-wider font-mono">
                        FR.APL.02
                    </div>
                    <h2 class="text-sm sm:text-base font-bold text-slate-900 leading-tight">
                        Lembar Verifikasi Asesmen Mandiri
                    </h2>
                    <p class="text-[11px] text-slate-500">
                        Verifikasi bukti portofolio dan tentukan keputusan K atau BK untuk setiap KUK.
                    </p>
                </div>
                <div class="flex items-center gap-2 self-start sm:self-center shrink-0">
                    <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-lg bg-slate-50 border border-slate-200 text-slate-600 font-semibold text-xs">
                        <span>{{ $pendaftaran->skema->unitKompetensi->count() }} Unit Kompetensi</span>
                    </span>
                </div>
            </div>

            <!-- =====================================================================
                 LIST UNIT KOMPETENSI, ELEMEN & KUK MATRIX
                 ===================================================================== -->
            <div class="space-y-3.5">
                @forelse($pendaftaran->skema->unitKompetensi as $indexUnit => $unit)

                    <!-- UNIT CARD (COMPACT ENTERPRISE) -->
                    <div x-data="{ openUnit: true }" class="bg-white border border-slate-200 rounded-lg overflow-hidden shadow-2xs">
                        
                        <!-- HEADER UNIT -->
                        <div class="p-3 sm:p-4 bg-slate-50 border-b border-slate-200 flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3">
                            <!-- Kiri: Kode & Judul Unit -->
                            <div class="space-y-0.5 min-w-0 flex-1">
                                <div class="flex items-center gap-2 flex-wrap">
                                    <span class="px-2 py-0.5 rounded bg-blue-100 text-blue-800 font-mono text-[10px] font-bold">
                                        {{ $unit->kode_unit }}
                                    </span>
                                    <span class="text-[10px] font-bold text-slate-400 uppercase tracking-wider">
                                        Unit {{ $indexUnit + 1 }}
                                    </span>
                                </div>
                                <h3 class="text-sm sm:text-base font-bold text-slate-900 leading-snug">
                                    {{ $unit->judul_unit }}
                                </h3>
                            </div>

                            <!-- Kanan: Computed Keputusan Unit (Rules BNSP) & Collapse Button -->
                            <div class="flex items-center gap-2.5 shrink-0 self-end sm:self-center">
                                <!-- Hidden input to submit unit decision -->
                                <input type="hidden" name="nilai[{{ $unit->id }}]" :value="unitDecisions['{{ $unit->id }}']">

                                <!-- Visual Keputusan Unit Badge (Auto-computed by Alpine based on BNSP rules) -->
                                <div class="flex items-center gap-1.5 bg-white px-2.5 py-1 rounded-md border shadow-2xs"
                                     :class="unitDecisions['{{ $unit->id }}'] === 'K' ? 'border-emerald-300 bg-emerald-50/40' : 'border-rose-300 bg-rose-50/40'">
                                    <span class="text-[10px] font-bold text-slate-500 uppercase">Keputusan Unit:</span>
                                    <span class="text-xs font-bold inline-flex items-center gap-1"
                                          :class="unitDecisions['{{ $unit->id }}'] === 'K' ? 'text-emerald-700' : 'text-rose-700'">
                                        <span x-text="unitDecisions['{{ $unit->id }}'] === 'K' ? 'Kompeten (K)' : 'Belum Kompeten (BK)'"></span>
                                    </span>
                                </div>

                                <button type="button" 
                                        @click="openUnit = !openUnit"
                                        class="px-2 py-1 text-slate-600 hover:text-slate-800 rounded hover:bg-slate-200/60 transition-colors cursor-pointer text-xs font-semibold"
                                        :title="openUnit ? 'Tutup Rincian Unit' : 'Buka Rincian Unit'">
                                    <span x-text="openUnit ? 'Tutup' : 'Buka'"></span>
                                </button>
                            </div>
                        </div>

                        <!-- BODY UNIT: ELEMEN & KUK -->
                        <div x-show="openUnit" x-transition.opacity.duration.150ms class="divide-y divide-slate-100">
                            @forelse($unit->elemenKompetensi as $elemen)
                                @php
                                    $listBukti = $buktiApl02Map->get($elemen->id, collect());
                                    $jawabanElem = $jawabanMap->get($elemen->id);
                                    $klaimElem = $jawabanElem ? $jawabanElem->nilai_kompetensi : 'K';
                                @endphp
                                
                                <div class="p-3 sm:p-4 space-y-2.5">
                                    <!-- ELEMEN HEADER & EVIDENCE PILLS -->
                                    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-2 border-b border-slate-100 pb-2">
                                        <!-- Left: Elemen Title -->
                                        <div class="flex items-center gap-2">
                                            <span class="px-1.5 py-0.5 rounded bg-slate-200 text-slate-700 text-[10px] font-mono font-bold shrink-0">
                                                Elemen {{ $elemen->nomor_elemen }}
                                            </span>
                                            <h4 class="text-xs font-bold text-slate-800 leading-snug">
                                                {{ $elemen->nama_elemen }}
                                            </h4>
                                        </div>

                                        <!-- Right: Compact Evidence Pills -->
                                        <div class="flex items-center gap-1.5 flex-wrap shrink-0">
                                            <span class="text-[10px] font-bold uppercase tracking-wider text-slate-400 mr-0.5">Bukti:</span>
                                            @if($listBukti->isNotEmpty())
                                                @foreach($listBukti as $b)
                                                    <button type="button" 
                                                            @click="bukaPreview('{{ $b->url }}', '{{ addslashes($b->nama_tampil) }}', '{{ $b->is_pdf ? 'pdf' : 'image' }}')"
                                                            class="inline-flex items-center gap-1 px-2 py-0.5 rounded bg-slate-100 hover:bg-slate-200 border border-slate-200 text-[11px] text-slate-700 max-w-[200px] truncate transition-colors cursor-pointer shadow-2xs"
                                                            title="Klik untuk melihat bukti: {{ $b->nama_tampil }}">
                                                        <span class="text-[9px] font-bold px-1 rounded bg-white text-slate-700 border border-slate-200">{{ $b->is_pdf ? 'PDF' : 'IMG' }}</span>
                                                        <span class="truncate font-medium">{{ $b->nama_tampil }}</span>
                                                        @if($b->sumber === 'apl01')
                                                            <span class="text-[9px] text-blue-600 font-bold bg-blue-50 px-1 rounded">APL.01</span>
                                                        @endif
                                                    </button>
                                                @endforeach
                                            @elseif($jawabanElem && $jawabanElem->bukti_relevan)
                                                <a href="{{ $jawabanElem->url_bukti ?? asset($jawabanElem->bukti_relevan) }}" target="_blank" 
                                                   class="inline-flex items-center gap-1 px-2 py-0.5 rounded bg-slate-100 hover:bg-slate-200 border border-slate-200 text-[11px] text-slate-700 max-w-[200px] truncate transition-colors shadow-2xs">
                                                    <span class="text-[9px] font-bold px-1 rounded bg-white text-slate-700 border border-slate-200">FILE</span>
                                                    <span class="truncate font-medium">{{ basename($jawabanElem->bukti_relevan) }}</span>
                                                </a>
                                            @else
                                                <span class="text-[11px] text-slate-400 italic">Tidak ada berkas bukti</span>
                                            @endif
                                        </div>
                                    </div>

                                    <!-- KUK TABLE (COMPACT ENTERPRISE) -->
                                    <div class="overflow-x-auto custom-scrollbar border border-slate-200 rounded-md">
                                        <table class="w-full text-left text-xs border-collapse">
                                            <thead>
                                                <tr class="bg-slate-100 text-slate-600 font-bold border-b border-slate-200">
                                                    <th class="py-1.5 px-2.5 w-14 text-center font-mono text-[11px]">KUK</th>
                                                    <th class="py-1.5 px-3">Pernyataan Kriteria Unjuk Kerja</th>
                                                    <th class="py-1.5 px-2.5 w-20 text-center text-[11px]">Asesi</th>
                                                    <th class="py-1.5 px-3 w-36 text-center text-[11px]">Verifikasi Asesor</th>
                                                </tr>
                                            </thead>
                                            <tbody class="divide-y divide-slate-100 bg-white">
                                                @forelse($elemen->kriteriaUnjukKerja as $idxKuk => $kuk)
                                                    @php
                                                        $vKuk = $verifikasiKukMap->get($kuk->id);
                                                        $klaimKuk = $vKuk ? $vKuk->nilai_kompetensi : $klaimElem;
                                                    @endphp
                                                    <tr class="hover:bg-slate-50/70 transition-colors"
                                                        :class="verifiedKuk['{{ $kuk->id }}'] === 'BK' ? 'bg-rose-50/20' : ''">
                                                        <!-- No KUK -->
                                                        <td class="py-2.5 px-2.5 text-center font-mono font-bold text-slate-600 bg-slate-50/50 text-xs align-top">
                                                            {{ $kuk->nomor_kuk ?: ($elemen->nomor_elemen . '.' . ($idxKuk + 1)) }}
                                                        </td>

                                                        <!-- Pernyataan KUK & Sub-baris Catatan BK -->
                                                        <td class="py-2.5 px-3 text-slate-700 leading-relaxed align-top">
                                                            <div>{{ $kuk->pernyataan_kuk }}</div>

                                                            <!-- SUB-BARIS INPUT CATATAN TINDAK LANJUT (MUNCUL JIKA KUK DINILAI BK) -->
                                                            <div x-show="verifiedKuk['{{ $kuk->id }}'] === 'BK'" 
                                                                 x-transition:enter="transition ease-out duration-200"
                                                                 x-transition:enter-start="opacity-0 -translate-y-1"
                                                                 x-transition:enter-end="opacity-100 translate-y-0"
                                                                 class="mt-2 pt-1.5 border-t border-rose-100">
                                                                <div class="flex items-center gap-1.5">
                                                                    <span class="text-[10px] font-bold text-rose-600 shrink-0">
                                                                        <span>Tindak Lanjut:</span>
                                                                    </span>
                                                                    <input type="text" 
                                                                           name="catatan_kuk[{{ $kuk->id }}]" 
                                                                           x-model="catatanKuk['{{ $kuk->id }}']" 
                                                                           placeholder="Catatan tindak lanjut / materi yang perlu diuji langsung..." 
                                                                           {{ $isLocked ? 'readonly' : '' }}
                                                                           class="w-full text-xs rounded-md border border-slate-200 focus:border-rose-500 focus:ring-1 focus:ring-rose-500 py-1 px-2.5 bg-rose-50/40 placeholder:text-slate-400">
                                                                </div>
                                                            </div>
                                                        </td>

                                                        <!-- Penilaian Mandiri Asesi -->
                                                        <td class="py-2.5 px-2.5 text-center align-top">
                                                            @if($klaimKuk === 'K')
                                                                <span class="inline-block px-2.5 py-0.5 rounded text-[11px] font-bold bg-emerald-50 text-emerald-700 border border-emerald-200">
                                                                    K
                                                                </span>
                                                            @else
                                                                <span class="inline-block px-2.5 py-0.5 rounded text-[11px] font-bold bg-rose-50 text-rose-700 border border-rose-200">
                                                                    BK
                                                                </span>
                                                            @endif
                                                        </td>

                                                        <!-- Verifikasi Asesor (2 Opsi Toggle: [ K ] dan [ BK ]) -->
                                                        <td class="py-2.5 px-3 text-center align-top">
                                                            <!-- Hidden Input for Form Submission -->
                                                            <input type="hidden" 
                                                                   name="verifikasi_kuk[{{ $kuk->id }}]" 
                                                                   :value="verifiedKuk['{{ $kuk->id }}']">

                                                            <!-- Segmented 2-Option Control -->
                                                            <div class="inline-flex rounded-lg bg-slate-100 p-0.5 border border-slate-200/80 gap-0.5 shadow-2xs">
                                                                <!-- Tombol Opsi K -->
                                                                <button type="button" 
                                                                        @click="setKuk('{{ $kuk->id }}', 'K')" 
                                                                        {{ $isLocked ? 'disabled' : '' }}
                                                                        class="px-3 py-1 rounded-md text-[11px] transition-all duration-150 select-none border inline-flex items-center justify-center min-w-[28px] {{ $isLocked ? 'cursor-not-allowed opacity-80' : 'cursor-pointer' }}"
                                                                        :class="verifiedKuk['{{ $kuk->id }}'] === 'K' 
                                                                            ? 'bg-emerald-600 text-white font-bold border-emerald-600 shadow-xs' 
                                                                            : 'bg-slate-100 text-slate-600 border-transparent hover:bg-slate-200 font-medium'">
                                                                    <span>K</span>
                                                                </button>

                                                                <!-- Tombol Opsi BK -->
                                                                <button type="button" 
                                                                        @click="setKuk('{{ $kuk->id }}', 'BK')" 
                                                                        {{ $isLocked ? 'disabled' : '' }}
                                                                        class="px-3 py-1 rounded-md text-[11px] transition-all duration-150 select-none border inline-flex items-center justify-center min-w-[28px] {{ $isLocked ? 'cursor-not-allowed opacity-80' : 'cursor-pointer' }}"
                                                                        :class="verifiedKuk['{{ $kuk->id }}'] === 'BK' 
                                                                            ? 'bg-rose-600 text-white font-bold border-rose-600 shadow-xs' 
                                                                            : 'bg-slate-100 text-slate-600 border-transparent hover:bg-slate-200 font-medium'">
                                                                    <span>BK</span>
                                                                </button>
                                                            </div>
                                                        </td>
                                                    </tr>
                                                @empty
                                                    <tr>
                                                        <td colspan="4" class="py-2.5 px-3 text-center text-slate-400 italic">
                                                            Belum ada data KUK pada elemen ini.
                                                        </td>
                                                    </tr>
                                                @endforelse
                                            </tbody>
                                        </table>
                                    </div>
                                </div>
                            @empty
                                <div class="p-4 text-center text-slate-400 text-xs italic">
                                    Belum ada elemen kompetensi pada unit ini.
                                </div>
                            @endforelse
                        </div>
                    </div>
                @empty
                    <div class="bg-white rounded-lg border border-slate-200 p-8 text-center text-slate-500 text-sm">
                        Belum ada Unit Kompetensi terdaftar pada skema ini.
                    </div>
                @endforelse
            </div>



            <!-- =====================================================================
                 REKOMENDASI & PENGESAHAN TANDA TANGAN ASESOR (BNSP FR.APL.02)
                 ===================================================================== -->
            <div class="bg-white rounded-lg border border-slate-200 shadow-2xs p-4 sm:p-5 space-y-4">
                <div class="border-b border-slate-200 pb-2.5">
                    <div class="text-blue-600 font-bold text-[10px] uppercase tracking-wider">
                        Keputusan Akhir & Pengesahan BNSP
                    </div>
                    <h3 class="text-base font-bold text-slate-900 mt-0.5">Rekomendasi Asesor & Tanda Tangan Digital</h3>
                </div>

                <input type="hidden" name="action_type" id="input-action-type" :value="recommendation === 'dapat_dilanjutkan' ? 'approve' : 'revision'">
                <input type="hidden" name="keputusan" value="kompeten">

                <div class="grid grid-cols-1 lg:grid-cols-2 gap-5">
                    <!-- Left: Rekomendasi & Catatan Asesor -->
                    <div class="space-y-3 bg-slate-50 p-3.5 sm:p-4 rounded-lg border border-slate-200">
                        <div class="space-y-1.5">
                            <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider">
                                Rekomendasi Untuk Asesi <span class="text-rose-500">*</span>
                            </label>
                            <p class="text-xs text-slate-500 leading-relaxed">
                                Berdasarkan hasil peninjauan bukti dan asesmen mandiri, maka asesmen:
                            </p>
                            <div class="space-y-2 pt-1">
                                <!-- OPSI REKOMENDASI 1: DAPAT DILANJUTKAN (HANYA JIKA SEMUA KUK KOMPETEN) -->
                                <label class="flex items-start gap-2 p-2.5 rounded-lg border transition-all"
                                       :class="!allVerified ? 'opacity-50 cursor-not-allowed bg-slate-100 border-slate-200 text-slate-400' : (recommendation === 'dapat_dilanjutkan' ? 'bg-emerald-50/70 border-emerald-300 text-emerald-900 font-semibold cursor-pointer' : 'bg-white border-slate-200 text-slate-700 cursor-pointer')">
                                    <input type="radio" 
                                           name="rekomendasi_asesor_status" 
                                           value="dapat_dilanjutkan" 
                                           x-model="recommendation"
                                           :disabled="!allVerified || {{ $isLocked ? 'true' : 'false' }}"
                                           class="mt-0.5 text-emerald-600 focus:ring-emerald-500" 
                                           required>
                                    <div class="text-xs leading-snug">
                                        <div class="font-bold">Asesmen DAPAT Dilanjutkan (Portofolio Memenuhi Syarat - ACC)</div>
                                        <div class="text-[11px] opacity-85 mt-0.5">
                                            <span x-show="allVerified">Seluruh KUK terverifikasi Kompeten (K). Asesi dapat langsung lanjut menandatangani kesepakatan asesmen FR.AK.01.</span>
                                            <span x-show="!allVerified">Tidak dapat dipilih karena masih ada <strong class="text-rose-600" x-text="bkCount"></strong> KUK Belum Kompeten (BK).</span>
                                        </div>
                                    </div>
                                </label>

                                <!-- OPSI REKOMENDASI 2: TIDAK DAPAT DILANJUTKAN (MINTA REVISI DOKUMEN) -->
                                <label class="flex items-start gap-2 p-2.5 rounded-lg border transition-all cursor-pointer"
                                       :class="recommendation === 'tidak_dapat_dilanjutkan' ? 'bg-amber-50/70 border-amber-300 text-amber-900 font-semibold' : 'bg-white border-slate-200 text-slate-700'">
                                    <input type="radio" 
                                           name="rekomendasi_asesor_status" 
                                           value="tidak_dapat_dilanjutkan" 
                                           x-model="recommendation"
                                           class="mt-0.5 text-amber-600 focus:ring-amber-500" 
                                           {{ $isLocked ? 'disabled' : '' }} required>
                                    <div class="text-xs leading-snug">
                                        <div class="font-bold text-amber-800">TIDAK DAPAT Dilanjutkan (Minta Revisi Berkas APL.02)</div>
                                        <div class="text-[11px] opacity-80 mt-0.5">
                                            <span x-show="!allVerified">Terdapat <strong class="text-rose-700" x-text="bkCount"></strong> butir KUK Belum Kompeten (BK). Berkas APL.02 dikembalikan ke asesi untuk diperbaiki.</span>
                                            <span x-show="allVerified">Berkas APL.02 dikembalikan ke asesi untuk melengkapi atau memperbaiki dokumen bukti yang kurang.</span>
                                        </div>
                                    </div>
                                </label>

                                <!-- OPSI REKOMENDASI 3: DITOLAK -->
                                <label class="flex items-start gap-2 p-2.5 rounded-lg border transition-all cursor-pointer"
                                       :class="recommendation === 'ditolak' ? 'bg-rose-50/70 border-rose-300 text-rose-900 font-semibold' : 'bg-white border-slate-200 text-slate-700'">
                                    <input type="radio" 
                                           name="rekomendasi_asesor_status" 
                                           value="ditolak" 
                                           x-model="recommendation"
                                           class="mt-0.5 text-rose-600 focus:ring-rose-500" 
                                           {{ $isLocked ? 'disabled' : '' }} required>
                                    <div class="text-xs leading-snug">
                                        <div class="font-bold text-rose-800">TIDAK DAPAT DITERIMA (Tolak Permohonan Asesmen)</div>
                                        <div class="text-[11px] opacity-80 mt-0.5">Asesi tidak memenuhi persyaratan asesmen mandiri dan permohonan sertifikasi skema ini ditolak.</div>
                                    </div>
                                </label>
                            </div>
                        </div>

                        <div class="space-y-1 pt-1">
                            <label class="block text-xs font-bold text-slate-700" id="label-catatan-rekomendasi">
                                Catatan & Umpan Balik Asesor: 
                                <span class="text-rose-500 text-[11px]" x-show="recommendation === 'tidak_dapat_dilanjutkan'">* (Wajib Diisi untuk Revisi)</span>
                                <span class="text-rose-500 text-[11px]" x-show="recommendation === 'ditolak'">* (Wajib Diisi untuk Penolakan)</span>
                            </label>
                            <textarea name="catatan_rekomendasi" 
                                      id="textarea-catatan-rekomendasi" 
                                      rows="3" 
                                      x-model="catatanRekomendasi"
                                      placeholder="Tuliskan catatan rekomendasi, arahan revisi, atau alasan penolakan..." 
                                      :required="recommendation === 'tidak_dapat_dilanjutkan' || recommendation === 'ditolak'"
                                      {{ $isLocked ? 'readonly' : '' }} 
                                      class="w-full bg-white border border-slate-300 rounded-lg p-2.5 text-xs text-slate-800 focus:ring-2 focus:ring-blue-500 focus:border-blue-500 outline-hidden leading-relaxed">{{ $pendaftaran->catatan_peninjauan_asesor ?? ($pendaftaran->rekomendasi->catatan_rekomendasi ?? '') }}</textarea>
                        </div>
                    </div>

                    <!-- Right: Asesi & Asesor Signature Boxes -->
                    <div class="space-y-3">
                        <!-- Asesi Signature Box -->
                        <div class="bg-slate-50 p-3 rounded-lg border border-slate-200 flex items-center justify-between gap-3">
                            <div class="space-y-0.5">
                                <span class="text-[9px] font-bold text-slate-400 uppercase tracking-wider">Asesi Pemohon:</span>
                                <div class="font-bold text-slate-900 text-xs sm:text-sm">{{ $pendaftaran->asesi->nama_lengkap }}</div>
                                <div class="text-[10px] text-slate-400">
                                    Tanggal: {{ $pendaftaran->tanggal_ttd_asesi ? $pendaftaran->tanggal_ttd_asesi->format('d/m/Y') : date('d/m/Y') }}
                                </div>
                            </div>
                            <div class="w-28 h-12 bg-white rounded border border-slate-200 flex items-center justify-center p-1 shrink-0">
                                @php
                                    $ttdAsesiApl02 = $pendaftaran->tanda_tangan_asesi ?? $pendaftaran->asesi->tanda_tangan;
                                    $srcAsesiApl02 = $ttdAsesiApl02 ? (\Illuminate\Support\Str::startsWith($ttdAsesiApl02, ['data:image', 'http://', 'https://']) ? $ttdAsesiApl02 : asset($ttdAsesiApl02)) : null;
                                @endphp
                                @if($srcAsesiApl02)
                                    <img src="{{ $srcAsesiApl02 }}" alt="TTD Asesi" class="max-h-full object-contain">
                                @else
                                    <span class="text-[9px] text-slate-400 italic">TTD Profil</span>
                                @endif
                            </div>
                        </div>

                        <!-- Asesor Signature Box -->
                        <div class="bg-slate-50 p-3 rounded-lg border border-slate-200 space-y-2.5" id="container-box-ttd-asesor">
                            <div class="flex items-center justify-between">
                                <div class="space-y-0.5">
                                    <span class="text-[9px] font-bold text-slate-400 uppercase tracking-wider">Ditinjau Oleh Asesor:</span>
                                    <div class="font-bold text-slate-900 text-xs sm:text-sm">{{ auth()->user()->nama_lengkap }}</div>
                                    <div class="text-[10px] text-blue-600 font-mono font-semibold">
                                        No. Reg: {{ auth()->user()->nomor_registrasi ?? ('MET.000.00' . auth()->id() . ' 2026') }}
                                    </div>
                                </div>
                                <div class="text-[10px] text-slate-400">
                                    Tanggal: {{ $pendaftaran->tanggal_ttd_asesor ? $pendaftaran->tanggal_ttd_asesor->format('d/m/Y') : date('d/m/Y') }}
                                </div>
                            </div>

                            @php
                                $ttdAsesorApl02 = $pendaftaran->tanda_tangan_asesor ?? auth()->user()->tanda_tangan;
                                $srcAsesorApl02 = $ttdAsesorApl02 ? (\Illuminate\Support\Str::startsWith($ttdAsesorApl02, ['data:image', 'http://', 'https://']) ? $ttdAsesorApl02 : asset($ttdAsesorApl02)) : '';
                            @endphp

                            <!-- Hidden Base64 Input -->
                            <input type="hidden" name="tanda_tangan_asesor" id="input-ttd-asesi-base64" value="{{ old('tanda_tangan_asesor', $ttdAsesorApl02) }}">

                            <!-- Preview & Button -->
                            <div class="flex items-center gap-3">
                                <div id="box-preview-ttd-asesi" class="h-12 w-28 bg-white rounded border border-slate-200 flex items-center justify-center p-1 shrink-0" style="{{ $srcAsesorApl02 ? '' : 'display: none;' }}">
                                    <img id="preview-ttd-asesi-img" src="{{ $srcAsesorApl02 }}" alt="TTD Asesor" class="max-h-full object-contain">
                                </div>

                                <div class="flex-1">
                                    <div id="pesan-ttd-asesor-kosong" class="text-[11px] text-rose-600 italic mb-1" style="{{ $srcAsesorApl02 ? 'display: none;' : 'display: block;' }}">
                                        Tanda Tangan Asesor belum dibubuhkan.
                                    </div>

                                    @if(!$isLocked)
                                        <button type="button" 
                                                id="btn-modal-ttd-asesor" 
                                                @click="bukaModalTtdAsesor()"
                                                class="px-2.5 py-1.5 rounded-lg {{ $srcAsesorApl02 ? 'bg-white border-slate-300 text-slate-700 hover:bg-slate-50' : 'bg-blue-600 text-white hover:bg-blue-700 shadow-2xs' }} border font-semibold text-xs transition-colors inline-flex items-center gap-1 cursor-pointer">
                                            <span>{{ ($pendaftaran->tanda_tangan_asesor || auth()->user()->tanda_tangan) ? 'Ubah Tanda Tangan Canvas' : 'Gambar TTD Digital (Wajib)' }}</span>
                                        </button>
                                    @endif
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- =====================================================================
                 STICKY BOTTOM ACTION BAR (COMPACT & RESPONSIVE)
                 ===================================================================== -->
            <div class="sticky bottom-3 z-30 bg-white/95 backdrop-blur-md border border-slate-200 rounded-xl p-3 sm:p-3.5 shadow-lg flex flex-col md:flex-row items-center justify-between gap-3">
                <!-- Left: Rekapitulasi KUK Realtime BNSP -->
                <div class="flex items-center gap-2 text-xs w-full md:w-auto justify-between md:justify-start">
                    <span class="px-2.5 py-1 rounded-full font-bold inline-flex items-center gap-1.5"
                          :class="allVerified ? 'bg-emerald-100 text-emerald-800 border border-emerald-200' : 'bg-amber-100 text-amber-800 border border-amber-200'">
                        <span x-show="allVerified">✓ Semua KUK Terverifikasi K (<span x-text="verifiedCount + ' / ' + totalKuk"></span>)</span>
                        <span x-show="!allVerified"><span x-text="verifiedCount"></span> K &bull; <span x-text="bkCount" class="text-rose-600 font-bold"></span> BK dari <span x-text="totalKuk"></span> KUK</span>
                    </span>
                    <span class="text-slate-500 font-medium text-[11px] hidden lg:inline" x-show="!allVerified">
                        &bull; Ada KUK BK, formulir perlu direvisi asesi
                    </span>
                </div>

                <!-- Right: Buttons -->
                <div class="flex items-center gap-2.5 w-full md:w-auto justify-end">
                    @if($isLocked)
                        <span class="px-4 py-2 rounded-lg font-bold text-xs inline-flex items-center gap-1.5 {{ $pendaftaran->isApl02Rejected() ? 'bg-rose-50 text-rose-700 border border-rose-200' : 'bg-emerald-50 text-emerald-700 border border-emerald-200' }}">
                            <span>{{ $pendaftaran->isApl02Rejected() ? 'Permohonan Asesmen Telah Ditolak (Terkunci)' : 'Penilaian Telah Disetujui (Terkunci)' }}</span>
                        </span>
                    @else
                        <!-- Tombol Pilihan Tolak -->
                        <button type="button" 
                                @click="bukaModalTolak()" 
                                class="w-1/2 md:w-auto px-4 py-2 rounded-lg bg-white border border-rose-300 hover:bg-rose-50 text-rose-600 hover:text-rose-700 font-bold text-xs transition-colors flex items-center justify-center gap-1.5 cursor-pointer shadow-2xs">
                            <span>Tolak</span>
                        </button>

                        <!-- Tombol Utama Dinamis: Hijau (ACC) jika semua K, berubah jadi Revisi (Orange/Amber) jika ada KUK yang BK -->
                        <button type="submit" 
                                id="btn-submit-penilaian-asesor" 
                                :disabled="saving"
                                @click="handleClickMainButton($event)"
                                class="w-1/2 md:w-auto px-5 py-2 rounded-lg font-bold text-xs shadow-xs transition-all duration-200 flex items-center justify-center gap-1.5 cursor-pointer"
                                :class="allVerified 
                                    ? 'bg-emerald-600 hover:bg-emerald-700 disabled:bg-emerald-400 text-white' 
                                    : 'bg-amber-600 hover:bg-amber-700 disabled:bg-amber-400 text-white'">
                            <span x-text="saving ? 'Menyimpan...' : (allVerified ? 'Sahkan & Setujui APL.02 (ACC)' : 'Revisi')"></span>
                        </button>
                    @endif
                </div>
            </div>
        </form>
    </div>

    <!-- =========================================================================
         MODAL POPUP MINTA REVISI
         ========================================================================= -->
    <div id="modalMintaRevisi" class="fixed inset-0 items-center justify-center bg-slate-950/70 backdrop-blur-xs p-4 z-[99999] hidden" style="display: none; z-index: 99999;">
        <div class="bg-white rounded-xl border border-slate-200 shadow-2xl max-w-lg w-full p-5 space-y-3.5 relative">
            <div class="flex items-center justify-between border-b border-slate-100 pb-2.5">
                <div class="flex items-center gap-2.5">
                    <div class="w-8 h-8 rounded-lg bg-amber-100 text-amber-700 flex items-center justify-center font-bold text-xs shrink-0">
                        REV
                    </div>
                    <div>
                        <h3 class="font-bold text-slate-800 text-sm">Minta Revisi FR.APL.02</h3>
                        <p class="text-[11px] text-slate-500">Kembalikan formulir ke asesi untuk diperbaiki</p>
                    </div>
                </div>
                <button type="button" @click="tutupModalMintaRevisi()" class="text-slate-400 hover:text-slate-600 text-lg font-bold px-1.5 cursor-pointer">&times;</button>
            </div>

            <p class="text-xs text-slate-600 leading-relaxed bg-amber-50/70 p-2.5 rounded-lg border border-amber-200/80">
                Formulir FR.APL.02 akan dikembalikan ke Asesi dengan status <strong>Perlu Revisi</strong>. Asesi akan menerima catatan perbaikan dan dapat memperbaiki isian serta berkas bukti pendukung.
            </p>

            <div class="space-y-1">
                <label class="block text-xs font-bold text-slate-700">
                    Catatan Umum Revisi untuk Asesi: <span class="text-amber-600">* (Wajib Diisi)</span>
                </label>
                <textarea id="modal-catatan-revisi-input" 
                          rows="4" 
                          x-model="modalCatatanRevisi"
                          placeholder="Tuliskan butir KUK atau bukti apa saja yang perlu diperbaiki oleh asesi..." 
                          class="w-full bg-white border border-slate-300 rounded-lg p-2.5 text-xs text-slate-800 focus:ring-2 focus:ring-amber-500 focus:border-amber-500 outline-hidden leading-relaxed"></textarea>
            </div>

            <div class="flex items-center justify-end gap-2 pt-2 border-t border-slate-100">
                <button type="button" @click="tutupModalMintaRevisi()" class="px-3 py-1.5 rounded-lg border border-slate-200 hover:bg-slate-50 text-slate-600 font-semibold text-xs cursor-pointer">
                    Batal
                </button>
                <button type="button" @click="submitMintaRevisiModal()" class="px-4 py-1.5 rounded-lg bg-amber-600 hover:bg-amber-700 text-white font-bold text-xs shadow-xs flex items-center gap-1.5 cursor-pointer">
                    <span>Kirim Catatan Revisi</span>
                </button>
            </div>
        </div>
    </div>

    <!-- =========================================================================
         MODAL POPUP TOLAK PERMOHONAN FR.APL.02
         ========================================================================= -->
    <div id="modalTolak" class="fixed inset-0 items-center justify-center bg-slate-950/70 backdrop-blur-xs p-4 z-[99999] hidden" style="display: none; z-index: 99999;">
        <div class="bg-white rounded-xl border border-slate-200 shadow-2xl max-w-lg w-full p-5 space-y-3.5 relative">
            <div class="flex items-center justify-between border-b border-slate-100 pb-2.5">
                <div class="flex items-center gap-2.5">
                    <div class="w-8 h-8 rounded-lg bg-rose-100 text-rose-600 flex items-center justify-center font-bold text-xs shrink-0">
                        TOLAK
                    </div>
                    <div>
                        <h3 class="font-bold text-slate-800 text-sm">Tolak Permohonan FR.APL.02</h3>
                        <p class="text-[11px] text-slate-500">Asesi tidak memenuhi kriteria persyaratan asesmen</p>
                    </div>
                </div>
                <button type="button" @click="tutupModalTolak()" class="text-slate-400 hover:text-slate-600 text-lg font-bold px-1.5 cursor-pointer">&times;</button>
            </div>

            <p class="text-xs text-rose-700 leading-relaxed bg-rose-50 p-2.5 rounded-lg border border-rose-200">
                <strong>Perhatian:</strong> Permohonan FR.APL.02 asesi akan dinyatakan <strong>Ditolak</strong>. Asesi tidak dapat melanjutkan asesmen mandiri pada skema ini.
            </p>

            <div class="space-y-1">
                <label class="block text-xs font-bold text-slate-700">
                    Alasan / Catatan Penolakan Asesor: <span class="text-rose-500">* (Wajib Diisi)</span>
                </label>
                <textarea id="modal-catatan-tolak-input" 
                          rows="4" 
                          x-model="modalCatatanTolak"
                          placeholder="Tuliskan alasan penolakan permohonan asesmen..." 
                          class="w-full bg-white border border-slate-300 rounded-lg p-2.5 text-xs text-slate-800 focus:ring-2 focus:ring-rose-500 focus:border-rose-500 outline-hidden leading-relaxed"></textarea>
            </div>

            <div class="flex items-center justify-end gap-2 pt-2 border-t border-slate-100">
                <button type="button" @click="tutupModalTolak()" class="px-3 py-1.5 rounded-lg border border-slate-200 hover:bg-slate-50 text-slate-600 font-semibold text-xs cursor-pointer">
                    Batal
                </button>
                <button type="button" @click="submitTolakModal()" class="px-4 py-1.5 rounded-lg bg-rose-600 hover:bg-rose-700 text-white font-bold text-xs shadow-xs flex items-center gap-1.5 cursor-pointer">
                    <span>Konfirmasi Tolak Permohonan</span>
                </button>
            </div>
        </div>
    </div>

    <!-- =========================================================================
         MODAL POPUP CANVAS SIGNATURE PAD
         ========================================================================= -->
    <div id="modalCanvasTtd" class="fixed inset-0 items-center justify-center bg-slate-950/70 backdrop-blur-xs p-4 z-[99999] hidden" style="display: none; z-index: 99999;">
        <div class="bg-white rounded-xl border border-slate-200 shadow-2xl max-w-md w-full p-5 space-y-3 relative">
            <div class="flex items-center justify-between border-b border-slate-100 pb-2">
                <div class="flex items-center gap-2">
                    <h3 class="font-bold text-slate-800 text-sm">Gambar Tanda Tangan Asesor</h3>
                </div>
                <button type="button" @click="tutupModalTtdAsesor()" class="text-slate-400 hover:text-slate-600 text-lg font-bold px-1.5 cursor-pointer">&times;</button>
            </div>

            <div class="bg-slate-50 p-2 rounded-lg border border-slate-200 flex justify-center">
                <canvas id="canvas-ttd-asesor" width="380" height="150" class="bg-white rounded border border-dashed border-blue-200 cursor-crosshair touch-none"></canvas>
            </div>

            <div class="flex items-center justify-between gap-2 pt-2 border-t border-slate-100">
                <button type="button" @click="bersihkanCanvasTtd()" class="px-2.5 py-1.5 rounded-lg border border-slate-200 hover:bg-slate-50 text-slate-600 font-semibold text-xs inline-flex items-center gap-1 cursor-pointer">
                    <span>Bersihkan</span>
                </button>
                <div class="flex items-center gap-2">
                    <button type="button" @click="tutupModalTtdAsesor()" class="px-3 py-1.5 rounded-lg border border-slate-200 hover:bg-slate-50 text-slate-600 font-semibold text-xs cursor-pointer">
                        Batal
                    </button>
                    <button type="button" @click="simpanCanvasTtd()" class="px-4 py-1.5 rounded-lg bg-blue-600 hover:bg-blue-700 text-white font-bold text-xs shadow-xs inline-flex items-center gap-1 cursor-pointer">
                        <span>Gunakan TTD</span>
                    </button>
                </div>
            </div>
        </div>
    </div>

    <!-- =========================================================================
         MODAL PREVIEW BUKTI UNIVERSAL (IMAGE & PDF)
         ========================================================================= -->
    <div id="modal-preview-universal" 
         class="fixed inset-0 items-center justify-center bg-slate-950/80 backdrop-blur-sm p-3 sm:p-6 z-[99999] hidden" 
         style="display: none; z-index: 99999;"
         @click.self="tutupModalPreview()"
         @keydown.escape.window="tutupModalPreview()">
        <div class="bg-white rounded-2xl border border-slate-700/20 shadow-2xl max-w-5xl w-full max-h-[92vh] flex flex-col overflow-hidden relative animate-in fade-in zoom-in-95 duration-150">
            <!-- Header Modal -->
            <div class="flex items-center justify-between px-4 py-3 border-b border-slate-100 bg-slate-50">
                <div class="flex items-center gap-2.5 min-w-0">
                    <span class="w-8 h-8 rounded-lg flex items-center justify-center text-xs shrink-0 font-bold"
                          :class="previewType === 'pdf' ? 'bg-rose-100 text-rose-600' : 'bg-blue-100 text-blue-600'"
                          x-text="previewType === 'pdf' ? 'PDF' : 'IMG'">
                    </span>
                    <div class="min-w-0">
                        <div class="text-[10px] font-bold uppercase tracking-wider text-slate-400">Pratinjau Berkas Bukti</div>
                        <h3 class="font-bold text-slate-800 text-xs sm:text-sm truncate" x-text="previewTitle"></h3>
                    </div>
                </div>
                <div class="flex items-center gap-2 shrink-0">
                    <a :href="previewUrl" target="_blank" download class="px-3 py-1.5 bg-white border border-slate-200 rounded-lg text-xs font-semibold text-slate-700 hover:bg-slate-50 transition-colors inline-flex items-center gap-1.5 shadow-2xs">
                        <span class="hidden sm:inline">Buka / Unduh</span>
                    </a>
                    <button type="button" 
                            @click="tutupModalPreview()" 
                            class="w-8 h-8 rounded-lg bg-slate-100 hover:bg-rose-50 text-slate-500 hover:text-rose-600 flex items-center justify-center font-bold text-base transition-colors cursor-pointer" 
                            title="Tutup Modal (Esc)">
                        &times;
                    </button>
                </div>
            </div>

            <!-- Body Modal: Image / PDF Viewer -->
            <div class="flex-1 bg-slate-950 p-4 flex items-center justify-center overflow-auto min-h-[380px] max-h-[78vh]">
                <template x-if="previewType === 'image'">
                    <div class="flex items-center justify-center w-full h-full">
                        <img :src="previewUrl" :alt="previewTitle" class="max-w-full max-h-[74vh] object-contain rounded-lg shadow-2xl mx-auto select-none">
                    </div>
                </template>
                <template x-if="previewType === 'pdf'">
                    <iframe :src="previewUrl" class="w-full h-[74vh] border-0 rounded-lg bg-white shadow-md"></iframe>
                </template>
            </div>
        </div>
    </div>

</div>
@endsection

@push('js')
<script>
    function asesorVerifikasiApp() {
        return {
            totalKuk: {{ $totalKukCount }},
            verifiedKuk: @js($initialVerifiedKuk),
            catatanKuk: @js($initialCatatanKuk),
            unitDecisions: @js($unitDecisions),
            unitKukMap: @js($unitKukMap),
            recommendation: '{{ $pendaftaran->rekomendasi_asesor_status ?? 'dapat_dilanjutkan' }}',
            catatanRekomendasi: '{{ addslashes($pendaftaran->catatan_peninjauan_asesor ?? ($pendaftaran->rekomendasi->catatan_rekomendasi ?? '')) }}',
            modalCatatanRevisi: '',
            modalCatatanTolak: '',
            verifiedCount: 0,
            bkCount: 0,
            allVerified: false,
            saving: false,
            previewUrl: '',
            previewTitle: '',
            previewType: 'image',
            signaturePad: null,

            init() {
                this.recalculateAll();
                this.modalCatatanRevisi = this.catatanRekomendasi;
                this.modalCatatanTolak = this.catatanRekomendasi;
            },

            setKuk(kukId, status) {
                @if($isLocked)
                    return;
                @endif
                this.verifiedKuk[kukId] = status;
                this.recalculateAll();
            },

            recalculateAll() {
                let countK = 0;
                let countBk = 0;

                // 1. Hitung Status K/BK Per Unit Kompetensi (Rules BNSP)
                for (let unitId in this.unitKukMap) {
                    const kukIds = this.unitKukMap[unitId] || [];
                    let unitAllK = true;

                    for (let kId of kukIds) {
                        const kukStatus = this.verifiedKuk[kId] || 'BK';
                        if (kukStatus === 'K') {
                            countK++;
                        } else {
                            countBk++;
                            unitAllK = false;
                        }
                    }

                    // Aturan BNSP: Jika semua KUK dlm 1 unit = K, maka unit = K. Jika ada min 1 BK, unit = BK
                    this.unitDecisions[unitId] = (kukIds.length > 0 && unitAllK) ? 'K' : 'BK';
                }

                this.verifiedCount = countK;
                this.bkCount = countBk;
                this.allVerified = (countBk === 0 && this.totalKuk > 0);

                // Otomatis sinkronisasi rekomendasi dengan status KUK jika belum diset manual ke 'ditolak'
                if (this.recommendation !== 'ditolak') {
                    if (!this.allVerified) {
                        this.recommendation = 'tidak_dapat_dilanjutkan';
                    } else {
                        this.recommendation = 'dapat_dilanjutkan';
                    }
                }

                const inputAction = document.getElementById('input-action-type');
                if (inputAction) {
                    inputAction.value = this.recommendation === 'dapat_dilanjutkan' ? 'approve' : (this.recommendation === 'ditolak' ? 'reject' : 'revision');
                }
            },

            bukaPreview(url, title, type) {
                this.previewUrl = url;
                this.previewTitle = title || 'Pratinjau Berkas';
                this.previewType = type || (url.toLowerCase().endsWith('.pdf') ? 'pdf' : 'image');
                
                const modal = document.getElementById('modal-preview-universal');
                if (modal) {
                    modal.classList.remove('hidden');
                    modal.style.display = 'flex';
                }
            },

            tutupModalPreview() {
                const modal = document.getElementById('modal-preview-universal');
                if (modal) {
                    modal.classList.add('hidden');
                    modal.style.display = 'none';
                }
            },

            bukaModalMintaRevisi() {
                this.modalCatatanRevisi = this.catatanRekomendasi;
                const modal = document.getElementById('modalMintaRevisi');
                if (modal) {
                    modal.classList.remove('hidden');
                    modal.style.display = 'flex';
                }
            },

            tutupModalMintaRevisi() {
                const modal = document.getElementById('modalMintaRevisi');
                if (modal) {
                    modal.classList.add('hidden');
                    modal.style.display = 'none';
                }
            },

            bukaModalTolak() {
                this.modalCatatanTolak = this.catatanRekomendasi;
                const modal = document.getElementById('modalTolak');
                if (modal) {
                    modal.classList.remove('hidden');
                    modal.style.display = 'flex';
                }
            },

            tutupModalTolak() {
                const modal = document.getElementById('modalTolak');
                if (modal) {
                    modal.classList.add('hidden');
                    modal.style.display = 'none';
                }
            },

            validasiTtdAsesor() {
                const inputTtd = document.getElementById('input-ttd-asesi-base64');
                const ttdVal = inputTtd ? inputTtd.value.trim() : '';

                if (!ttdVal) {
                    alert('PERINGATAN: Tanda Tangan Asesor Penguji wajib dibubuhkan/digambar terlebih dahulu!');
                    const containerTtd = document.getElementById('container-box-ttd-asesor');
                    if (containerTtd) {
                        containerTtd.scrollIntoView({ behavior: 'smooth', block: 'center' });
                        containerTtd.style.outline = '2px solid #ef4444';
                        setTimeout(() => { containerTtd.style.outline = 'none'; }, 3000);
                    }
                    this.bukaModalTtdAsesor();
                    return false;
                }
                return true;
            },

            handleClickMainButton(event) {
                if (this.allVerified) {
                    this.recommendation = 'dapat_dilanjutkan';
                    const inputAction = document.getElementById('input-action-type');
                    if (inputAction) inputAction.value = 'approve';
                    // Let form submit proceed through handleSubmit
                } else {
                    event.preventDefault();
                    // Ada KUK yang BK -> otomatis buka modal revisi
                    this.bukaModalMintaRevisi();
                }
            },

            submitMintaRevisiModal() {
                if (!this.modalCatatanRevisi || !this.modalCatatanRevisi.trim()) {
                    alert('Catatan/arahan revisi wajib diisi agar asesi mengetahui bagian yang perlu diperbaiki!');
                    return;
                }

                if (!this.validasiTtdAsesor()) {
                    return;
                }

                this.recommendation = 'tidak_dapat_dilanjutkan';
                this.catatanRekomendasi = this.modalCatatanRevisi.trim();

                const inputAction = document.getElementById('input-action-type');
                if (inputAction) inputAction.value = 'revision';

                this.tutupModalMintaRevisi();

                const form = document.getElementById('form-penilaian-asesor');
                if (form) {
                    this.saving = true;
                    form.submit();
                }
            },

            submitTolakModal() {
                if (!this.modalCatatanTolak || !this.modalCatatanTolak.trim()) {
                    alert('Alasan/catatan penolakan wajib diisi!');
                    return;
                }

                if (!this.validasiTtdAsesor()) {
                    return;
                }

                if (!confirm('Apakah Anda yakin ingin MENOLAK permohonan FR.APL.02 asesi ini? Tindakan ini tidak dapat dibatalkan.')) {
                    return;
                }

                this.recommendation = 'ditolak';
                this.catatanRekomendasi = this.modalCatatanTolak.trim();

                const inputAction = document.getElementById('input-action-type');
                if (inputAction) inputAction.value = 'reject';

                this.tutupModalTolak();

                const form = document.getElementById('form-penilaian-asesor');
                if (form) {
                    this.saving = true;
                    form.submit();
                }
            },

            bukaModalTtdAsesor() {
                const modal = document.getElementById('modalCanvasTtd');
                if (modal) {
                    modal.classList.remove('hidden');
                    modal.style.display = 'flex';
                    setTimeout(() => {
                        const canvas = document.getElementById('canvas-ttd-asesor');
                        if (canvas && !this.signaturePad) {
                            this.signaturePad = new SignaturePad(canvas, {
                                backgroundColor: 'rgb(255, 255, 255)',
                                penColor: 'rgb(0, 0, 0)'
                            });
                        } else if (this.signaturePad) {
                            this.signaturePad.clear();
                        }
                    }, 100);
                }
            },

            tutupModalTtdAsesor() {
                const modal = document.getElementById('modalCanvasTtd');
                if (modal) {
                    modal.classList.add('hidden');
                    modal.style.display = 'none';
                }
            },

            bersihkanCanvasTtd() {
                if (this.signaturePad) {
                    this.signaturePad.clear();
                }
            },

            simpanCanvasTtd() {
                if (this.signaturePad && this.signaturePad.isEmpty()) {
                    alert('Silakan bubuhkan tanda tangan Anda terlebih dahulu!');
                    return;
                }

                const dataUrl = this.signaturePad.toDataURL('image/png');
                const inputHidden = document.getElementById('input-ttd-asesi-base64');
                const previewBox = document.getElementById('box-preview-ttd-asesi');
                const previewImg = document.getElementById('preview-ttd-asesi-img');
                const pesanKosong = document.getElementById('pesan-ttd-asesor-kosong');
                const btnModal = document.getElementById('btn-modal-ttd-asesor');

                if (inputHidden) inputHidden.value = dataUrl;
                if (previewImg) previewImg.src = dataUrl;
                if (previewBox) previewBox.style.display = 'flex';
                if (pesanKosong) pesanKosong.style.display = 'none';
                if (btnModal) {
                    btnModal.innerHTML = '<span>Ubah Tanda Tangan Canvas</span>';
                    btnModal.classList.remove('bg-blue-600', 'text-white');
                    btnModal.classList.add('bg-white', 'text-slate-700', 'border-slate-300');
                }

                this.tutupModalTtdAsesor();
            },

            handleSubmit(e) {
                // 1. Validasi Keputusan Unit
                for (let unitId in this.unitDecisions) {
                    if (!this.unitDecisions[unitId] || (this.unitDecisions[unitId] !== 'K' && this.unitDecisions[unitId] !== 'BK')) {
                        e.preventDefault();
                        alert('PERINGATAN: Masih ada Unit Kompetensi yang belum ditentukan keputusannya! Silakan lengkapi penilaian seluruh KUK.');
                        return false;
                    }
                }

                // 2. Konfirmasi Rekomendasi Disetujui (ACC)
                if (this.recommendation === 'dapat_dilanjutkan') {
                    if (!this.allVerified) {
                        e.preventDefault();
                        alert('PERINGATAN: Formulir FR.APL.02 tidak dapat disetujui (ACC) karena masih terdapat butir KUK yang dinilai Belum Kompeten (BK). Silakan gunakan tombol Revisi.');
                        return false;
                    }

                    const confirmMsg = 'Konfirmasi: Apakah Anda yakin ingin MENYETUJUI (ACC) Formulir FR.APL.02 asesi ini?\nSeluruh KUK dinilai Kompeten (K) (Portofolio Memenuhi Syarat).\nFormulir FR.AK.01 akan terbuka untuk asesi.';
                    if (!confirm(confirmMsg)) {
                        e.preventDefault();
                        return false;
                    }
                }

                // 3. Validasi Rekomendasi Revisi
                if (this.recommendation === 'tidak_dapat_dilanjutkan' && (!this.catatanRekomendasi || !this.catatanRekomendasi.trim())) {
                    e.preventDefault();
                    alert('PERINGATAN: Catatan/arahan revisi wajib diisi ketika meminta Revisi!');
                    const textarea = document.getElementById('textarea-catatan-rekomendasi');
                    if (textarea) textarea.focus();
                    return false;
                }

                // 4. Validasi Rekomendasi Tolak
                if (this.recommendation === 'ditolak' && (!this.catatanRekomendasi || !this.catatanRekomendasi.trim())) {
                    e.preventDefault();
                    alert('PERINGATAN: Alasan/catatan penolakan wajib diisi ketika menolak permohonan!');
                    const textarea = document.getElementById('textarea-catatan-rekomendasi');
                    if (textarea) textarea.focus();
                    return false;
                }

                // 5. Validasi Tanda Tangan
                if (!this.validasiTtdAsesor()) {
                    e.preventDefault();
                    return false;
                }

                this.saving = true;
                return true;
            }
        };
    }
</script>
@endpush
