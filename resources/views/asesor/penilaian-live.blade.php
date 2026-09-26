@extends('tata-letak.dasbor')

@section('judul', 'Lembar Penilaian Live Asesmen Hari H')

@push('css')
    <script src="https://cdn.jsdelivr.net/npm/signature_pad@4.1.7/dist/signature_pad.umd.min.js"></script>
    <style>
        .custom-radio-k:checked + label {
            background-color: #ecfdf5;
            border-color: #10b981;
            color: #047857;
            font-weight: 700;
        }
        .custom-radio-bk:checked + label {
            background-color: #fef2f2;
            border-color: #ef4444;
            color: #b91c1c;
            font-weight: 700;
        }
        .no-scrollbar::-webkit-scrollbar {
            display: none;
        }
        .no-scrollbar {
            -ms-overflow-style: none;
            scrollbar-width: none;
        }
    </style>
@endpush

@section('konten')
@php
    $defaultAsesorTab = request('tab', 'observasi');
    if (!$pendaftaran->skema->hasInstrumen('FR.IA.01') && !request('tab')) {
        if ($pendaftaran->skema->hasInstrumen('FR.IA.02')) {
            $defaultAsesorTab = 'praktik';
        } elseif ($pendaftaran->skema->hasInstrumen('FR.IA.03')) {
            $defaultAsesorTab = 'lisan';
        } elseif ($pendaftaran->skema->hasInstrumen('FR.IA.04A')) {
            $defaultAsesorTab = 'proyek';
        } else {
            $defaultAsesorTab = 'rekap';
        }
    }
@endphp

<div class="min-h-screen bg-slate-50/60 pb-20"
     x-data="{
        activeTab: '{{ request('tab', $defaultAsesorTab) }}',
        isFinalized: {{ $isFinalized ? 'true' : 'false' }},
        keputusan: '{{ old('keputusan', $pendaftaran->rekomendasi->keputusan ?? 'kompeten') }}',
        signatureMode: 'canvas',
        
        setAllK(unitId) {
            document.querySelectorAll('input[data-unit=\'' + unitId + '\'][value=\'K\']').forEach(el => {
                el.checked = true;
            });
        },
        setAllKGlobal() {
            document.querySelectorAll('.custom-radio-k').forEach(el => {
                el.checked = true;
            });
        }
     }">

    <div class="max-w-6xl mx-auto px-4 sm:px-6 pt-4 space-y-6">

        <!-- =========================================================================
             1. HEADER INFORMASI ASESI & STATUS UJIAN
             ========================================================================= -->
        <div class="bg-white rounded-2xl border border-slate-200/80 shadow-xs p-6 sm:p-7 flex flex-col md:flex-row md:items-center justify-between gap-5">
            <div class="space-y-1.5 max-w-2xl">
                <div class="flex items-center gap-2 flex-wrap">
                    <a href="{{ (url()->previous() && url()->previous() !== url()->current()) ? url()->previous() : route('asesor.daftar-peserta', ['jadwal_id' => $pendaftaran->jadwal_id]) }}" 
                       onclick="if (document.referrer && document.referrer !== window.location.href) { window.location.href = document.referrer; return false; } else if (window.history.length > 1) { window.history.back(); return false; }"
                       class="text-xs text-blue-600 font-semibold hover:underline cursor-pointer">
                        &larr; Kembali
                    </a>
                    <span class="text-slate-300">&bull;</span>
                    <span class="text-xs font-bold text-slate-500 uppercase tracking-wider">No. Reg: {{ $pendaftaran->nomor_pendaftaran }}</span>
                    <span class="text-slate-300">&bull;</span>
                    <a href="{{ route('asesor.skema.ak-07', $pendaftaran->skema_id) }}" target="_blank" class="inline-flex items-center gap-1.5 px-2.5 py-0.5 rounded-lg border border-slate-200 bg-white hover:bg-slate-50 text-slate-700 text-xs font-bold transition shadow-2xs" title="Buka Master Formulir Penyesuaian Asesmen (FR.AK.07)">
                    <a href="{{ route('asesor.pendaftaran.ak07.edit', $pendaftaran->id) }}" target="_blank" class="inline-flex items-center gap-1.5 px-2.5 py-0.5 rounded-lg border border-slate-200 bg-white hover:bg-slate-50 text-slate-700 text-xs font-bold transition shadow-2xs" title="Buka Formulir Penyesuaian Asesmen Peserta (FR.AK.07)">
                        <i class="fa-solid fa-file-pen text-indigo-600"></i>
                        <span>Master FR.AK.07</span>
                        <span>FR.AK.07</span>
                    </a>

                    @if($pendaftaran->jadwal)
                        @php $jadwal = $pendaftaran->jadwal; @endphp
                        @if($jadwal->status_jadwal === 'berlangsung' && empty($pendaftaran->rekomendasi))
                            <span class="inline-flex items-center gap-1.5 px-2.5 py-0.5 rounded-full bg-emerald-100 text-emerald-800 border border-emerald-300 text-[10px] font-extrabold shadow-2xs">
                                <span class="relative flex h-2 w-2">
                                    <span class="animate-ping absolute inline-flex h-full w-full rounded-full bg-emerald-400 opacity-75"></span>
                                    <span class="relative inline-flex rounded-full h-2 w-2 bg-emerald-500"></span>
                                </span>
                                Asesmen Dimulai (Sesi Aktif)
                            </span>
                        @endif
                    @endif
                </div>
                <h1 class="text-xl sm:text-2xl font-bold text-slate-900 tracking-tight">
                    Penilaian Asesmen: {{ $pendaftaran->asesi->nama_lengkap }}
                </h1>
                <div class="text-xs text-slate-500 flex flex-wrap items-center gap-x-3 gap-y-1">
                    <span>Skema: <strong class="text-slate-800">{{ $pendaftaran->skema->nama_skema }}</strong></span>
                    <span>&bull;</span>
                    <span>Kode: <strong class="font-mono text-slate-800">{{ $pendaftaran->skema->kode_skema }}</strong></span>
                    <span>&bull;</span>
                    <span>TUK: <strong class="text-slate-800">{{ $pendaftaran->jadwal->nama_tuk ?? 'TUK SMKN 1 Gunungputri' }}</strong></span>
                </div>
            </div>

            <!-- Status Jawaban Asesi Summary Box -->
            <div class="bg-slate-50 border border-slate-200/80 rounded-xl p-4 flex-shrink-0 text-xs space-y-2 min-w-[240px]">
                @if($pendaftaran->skema->hasInstrumen('FR.IA.02'))
                    <div class="flex justify-between items-center">
                        <span class="text-slate-500 font-medium">Praktik (IA.02):</span>
                        @if($dokumenPraktik)
                            <span class="font-bold text-emerald-700 bg-emerald-50 border border-emerald-200 px-2 py-0.5 rounded-md">
                                Berkas Terunggah
                            </span>
                        @else
                            <span class="text-slate-400 italic">Observasi Langsung</span>
                        @endif
                    </div>
                @endif


                <div class="flex justify-between items-center pt-1 border-t border-slate-200/60">
                    <span class="text-slate-500 font-medium">Status Rekomendasi:</span>
                    @if($isFinalized)
                        <span class="font-bold {{ strtolower($pendaftaran->rekomendasi->keputusan ?? '') === 'kompeten' ? 'text-emerald-700' : 'text-rose-700' }}">
                            {{ strtoupper($pendaftaran->rekomendasi->keputusan ?? 'SELESAI') }}
                        </span>
                    @else
                        <span class="font-bold text-blue-600">Sesi Penilaian Aktif</span>
                    @endif
                </div>
            </div>
        </div>

        <!-- =========================================================================
             2. TAB NAVIGATION FOR ASSESSOR (SEGMENTED TABS MODERN TANPA SCROLLBAR PANAH)
             ========================================================================= -->
        <div class="grid grid-flow-col auto-cols-fr gap-1 bg-slate-100 p-1.5 rounded-2xl border border-slate-200/90 w-full">
            @if($pendaftaran->skema->hasInstrumen('FR.IA.01'))
                <button type="button" 
                        @click="activeTab = 'observasi'"
                        :class="activeTab === 'observasi' ? 'bg-white text-blue-700 shadow-2xs font-bold' : 'text-slate-600 hover:text-slate-900 font-medium hover:bg-white/60'"
                        class="py-2 px-2 text-xs transition-all flex items-center justify-center text-center rounded-xl cursor-pointer min-w-0">
                    <span class="truncate">1. FR.IA.01 (Ceklis Observasi)</span>
                </button>
            @endif

            @if($pendaftaran->skema->hasInstrumen('FR.IA.02'))
                <button type="button" 
                        @click="activeTab = 'praktik'"
                        :class="activeTab === 'praktik' ? 'bg-white text-blue-700 shadow-2xs font-bold' : 'text-slate-600 hover:text-slate-900 font-medium hover:bg-white/60'"
                        class="py-2 px-2 text-xs transition-all flex items-center justify-center text-center rounded-xl cursor-pointer min-w-0">
                    <span class="truncate">2. FR.IA.02 (Tugas Praktik)</span>
                </button>
            @endif

            @if($pendaftaran->skema->hasInstrumen('FR.IA.03'))
                <button type="button" 
                        @click="activeTab = 'lisan'"
                        :class="activeTab === 'lisan' ? 'bg-white text-blue-700 shadow-2xs font-bold' : 'text-slate-600 hover:text-slate-900 font-medium hover:bg-white/60'"
                        class="py-2 px-2 text-xs transition-all flex items-center justify-center text-center rounded-xl cursor-pointer min-w-0">
                    <span class="truncate">3. FR.IA.03 (Pertanyaan Lisan)</span>
                </button>
            @endif

            @if($pendaftaran->skema->hasInstrumen('FR.IA.04A'))
                <button type="button" 
                        @click="activeTab = 'proyek'"
                        :class="activeTab === 'proyek' ? 'bg-white text-blue-700 shadow-2xs font-bold' : 'text-slate-600 hover:text-slate-900 font-medium hover:bg-white/60'"
                        class="py-2 px-2 text-xs transition-all flex items-center justify-center text-center rounded-xl cursor-pointer min-w-0">
                    <span class="truncate">FR.IA.04A (Proyek / TOR)</span>
                </button>
            @endif

            @if($pendaftaran->skema->hasInstrumen('FR.IA.11'))
                <button type="button" 
                        @click="activeTab = 'mutu'"
                        :class="activeTab === 'mutu' ? 'bg-white text-blue-700 shadow-2xs font-bold' : 'text-slate-600 hover:text-slate-900 font-medium hover:bg-white/60'"
                        class="py-2 px-2 text-xs transition-all flex items-center justify-center text-center rounded-xl cursor-pointer min-w-0">
                    <span class="truncate">FR.IA.11 (Ceklis Mutu)</span>
                </button>
            @endif

            <button type="button" 
                    @click="activeTab = 'rekap'"
                    :class="activeTab === 'rekap' ? 'bg-emerald-600 text-white shadow-2xs font-bold' : 'text-emerald-800 hover:text-emerald-950 font-semibold hover:bg-white/60'"
                    class="py-2 px-2 text-xs transition-all flex items-center justify-center text-center rounded-xl cursor-pointer min-w-0">
                <span class="truncate">Rekap & Rekomendasi (FR.AK.02)</span>
            </button>
        </div>

        <!-- FORM UTAMA PENILAIAN LIVE ASESOR -->
        <form action="{{ route('asesor.penilaian-live.simpan', $pendaftaran->id) }}" method="POST" class="space-y-6">
            @csrf


            <!-- =====================================================================
                 TAB 1: FR.IA.01 CEKLIS OBSERVASI PRAKTIK LANGSUNG
                 ===================================================================== -->
            @if($pendaftaran->skema->hasInstrumen('FR.IA.01'))
                <div x-show="activeTab === 'observasi'" class="space-y-5">
                    <div class="bg-white rounded-2xl border border-slate-200/80 p-5 shadow-xs flex flex-col sm:flex-row sm:items-center justify-between gap-4">
                        <div>
                            <h2 class="text-base font-bold text-slate-900">FR.IA.01 Ceklis Observasi Aktivitas Praktik di Tempat Kerja / TUK</h2>
                            <p class="text-xs text-slate-500 mt-0.5">Centang K (Kompeten) atau BK (Belum Kompeten) untuk setiap KUK saat mengamati demonstrasi asesi.</p>
                        </div>
                        <button type="button" @click="setAllKGlobal()" 
                                class="px-4 py-2 bg-emerald-50 hover:bg-emerald-100 text-emerald-800 border border-emerald-200 rounded-xl text-xs font-bold transition-colors flex-shrink-0">
                            Set Semua K Global
                        </button>
                    </div>

                    @forelse($pendaftaran->skema->unitKompetensi as $indexUnit => $unit)
                        <div class="bg-white rounded-2xl border border-slate-200/80 shadow-xs overflow-hidden">
                            <div class="bg-slate-50/90 px-6 py-4 border-b border-slate-200/80 flex items-center justify-between gap-3">
                                <div>
                                    <span class="text-[11px] font-bold uppercase tracking-wider text-blue-600">Unit {{ $indexUnit + 1 }} &bull; {{ $unit->kode_unit }}</span>
                                    <h3 class="font-bold text-slate-800 text-sm sm:text-base">{{ $unit->judul_unit }}</h3>
                                </div>
                                <button type="button" @click="setAllK('{{ $unit->id }}')" 
                                        class="text-xs bg-emerald-50 hover:bg-emerald-100 text-emerald-700 border border-emerald-200 px-3 py-1.5 rounded-lg font-semibold transition-colors">
                                    Set Semua K Unit Ini
                                </button>
                            </div>

                            <div class="p-6 space-y-5">
                                @forelse($unit->elemenKompetensi as $elemen)
                                    <div class="border border-slate-200/70 rounded-xl p-4.5 bg-slate-50/30 space-y-3">
                                        <div class="text-xs font-bold text-slate-500 uppercase tracking-wider">
                                            Elemen {{ $elemen->nomor_elemen }}: {{ $elemen->nama_elemen }}
                                        </div>

                                        @if($elemen->kriteriaUnjukKerja && $elemen->kriteriaUnjukKerja->count() > 0)
                                            <div class="space-y-2.5 pt-1">
                                                @foreach($elemen->kriteriaUnjukKerja as $kuk)
                                                    @php
                                                        $valKuk = $savedPenilaianIa01[$kuk->id] ?? 'K';
                                                    @endphp
                                                    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3 bg-white p-3 rounded-lg border border-slate-200/60">
                                                        <div class="text-xs text-slate-700 flex-1">
                                                            <strong class="font-mono text-blue-700 mr-1.5">{{ $kuk->nomor_kuk }}</strong>
                                                            <span>{{ $kuk->pernyataan_kuk }}</span>
                                                        </div>

                                                        <div class="flex items-center gap-2 flex-shrink-0">
                                                            <div>
                                                                <input type="radio" 
                                                                       id="kuk_{{ $kuk->id }}_k" 
                                                                       name="penilaian_kuk[{{ $kuk->id }}]" 
                                                                       value="K" 
                                                                       data-unit="{{ $unit->id }}"
                                                                       {{ $valKuk === 'K' ? 'checked' : '' }} 
                                                                       class="custom-radio-k hidden peer">
                                                                <label for="kuk_{{ $kuk->id }}_k" 
                                                                       class="px-3 py-1 rounded-lg border border-slate-200 bg-white text-slate-600 font-semibold text-xs cursor-pointer flex items-center justify-center hover:bg-slate-50 transition-all">
                                                                    K
                                                                </label>
                                                            </div>

                                                            <div>
                                                                <input type="radio" 
                                                                       id="kuk_{{ $kuk->id }}_bk" 
                                                                       name="penilaian_kuk[{{ $kuk->id }}]" 
                                                                       value="BK" 
                                                                       data-unit="{{ $unit->id }}"
                                                                       {{ $valKuk === 'BK' ? 'checked' : '' }} 
                                                                       class="custom-radio-bk hidden peer">
                                                                <label for="kuk_{{ $kuk->id }}_bk" 
                                                                       class="px-3 py-1 rounded-lg border border-slate-200 bg-white text-slate-600 font-semibold text-xs cursor-pointer flex items-center justify-center hover:bg-slate-50 transition-all">
                                                                    BK
                                                                </label>
                                                            </div>
                                                        </div>
                                                    </div>
                                                @endforeach
                                            </div>
                                        @endif
                                    </div>
                                @empty
                                    <div class="text-xs text-slate-400 italic text-center py-2">Belum ada elemen kompetensi pada unit ini.</div>
                                @endforelse
                            </div>
                        </div>
                    @empty
                        <div class="bg-white rounded-2xl border border-slate-200 p-8 text-center text-slate-500 text-sm">
                            Tidak ada unit kompetensi pada skema ini.
                        </div>
                    @endforelse

                    <!-- Catatan Observasi Asesor -->
                    <div class="bg-white rounded-2xl border border-slate-200/80 p-6 shadow-xs space-y-2">
                        <label class="block text-xs font-bold text-slate-800 uppercase tracking-wider">
                            Catatan Asesor Penguji Terhadap Observasi Praktik Demonstrasi:
                        </label>
                        <textarea name="catatan_observasi" rows="3" 
                                  placeholder="Tuliskan catatan observasi kinerja asesi (penerapan K3, efisiensi kerja, ketelitian)..."
                                  class="w-full bg-white border border-slate-200 rounded-xl p-3.5 text-xs sm:text-sm text-slate-800 focus:ring-2 focus:ring-blue-500 outline-hidden">{{ $savedCatatanIa01 }}</textarea>
                    </div>

                    <!-- Action Bar Tab 1: Observasi -->
                    <div class="bg-white rounded-2xl border border-slate-200/80 p-4 shadow-xs flex items-center justify-between gap-3 flex-wrap">
                        <a href="{{ (url()->previous() && url()->previous() !== url()->current()) ? url()->previous() : route('asesor.daftar-peserta') }}" 
                           onclick="if (document.referrer && document.referrer !== window.location.href) { window.location.href = document.referrer; return false; } else if (window.history.length > 1) { window.history.back(); return false; }"
                           class="px-5 py-2.5 rounded-xl border border-slate-300 text-slate-700 bg-white hover:bg-slate-50 font-semibold text-xs transition-colors cursor-pointer">
                            &larr; Kembali
                        </a>
                        <div class="flex items-center gap-2">
                            <button type="submit" 
                                    name="tab_action" 
                                    value="observasi"
                                    formnovalidate
                                    class="px-5 py-2.5 rounded-xl bg-blue-600 hover:bg-blue-700 text-white font-bold text-xs shadow-xs transition-colors cursor-pointer">
                                Simpan FR.IA.01 (Ceklis Observasi)
                            </button>
                            @if($pendaftaran->skema->hasInstrumen('FR.IA.02'))
                                <button type="button" 
                                        @click="activeTab = 'praktik'" 
                                        class="px-4 py-2.5 rounded-xl bg-slate-100 hover:bg-slate-200 text-slate-700 font-semibold text-xs transition-colors cursor-pointer">
                                    Lanjut ke Tugas Praktik &rarr;
                                </button>
                            @endif
                        </div>
                    </div>
                </div>
            @endif

            <!-- =====================================================================
                 TAB 2: FR.IA.02 LEMBAR TUGAS PRAKTIK DEMONSTRASI & BUKTI ASESI
                 ===================================================================== -->
            @if($pendaftaran->skema->hasInstrumen('FR.IA.02'))
                <div x-show="activeTab === 'praktik'" class="space-y-6">
                    <div class="bg-white rounded-2xl border border-slate-200/80 p-6 sm:p-7 shadow-xs space-y-6">
                        <div class="border-b border-slate-200/80 pb-4 flex flex-col sm:flex-row sm:items-center justify-between gap-4">
                            <div>
                                <span class="text-xs font-bold text-blue-600 uppercase tracking-wider">FR.IA.02 &bull; Lembar Tugas Praktik Demonstrasi</span>
                                <h3 class="text-lg font-bold text-slate-900 mt-1">{{ $panduanPraktik['judul_tugas'] ?? 'Tugas Praktik Demonstrasi' }}</h3>
                                <p class="text-xs text-slate-500 mt-0.5">Alokasi Waktu: <strong>{{ $panduanPraktik['waktu_menit'] ?? 120 }} Menit</strong></p>
                            </div>
                            @if($dokumenPraktik)
                                <a href="{{ asset($dokumenPraktik->file_path) }}" target="_blank" 
                                   class="px-4 py-2.5 bg-blue-600 hover:bg-blue-700 text-white text-xs font-bold rounded-xl shadow-xs transition-colors flex items-center gap-2">
                                    <span>Unduh Berkas Asesi</span>
                                </a>
                            @endif
                        </div>

                        @if(!empty($panduanPraktik['skenario']))
                            <div class="p-4 bg-slate-50 rounded-xl border border-slate-200/70 text-xs sm:text-sm text-slate-700 leading-relaxed space-y-1">
                                <span class="font-bold text-slate-900 block uppercase tracking-wider text-[11px]">Skenario Tugas Praktik:</span>
                                <p>{{ $panduanPraktik['skenario'] }}</p>
                            </div>
                        @endif

                        @if(!empty($panduanPraktik['instruksi_kerja']))
                            <div class="space-y-2">
                                <span class="text-xs font-bold text-slate-800 uppercase tracking-wider">Instruksi Langkah Kerja Asesi:</span>
                                <ul class="text-xs sm:text-sm text-slate-700 space-y-1.5 list-decimal list-inside bg-slate-50 p-4 rounded-xl border border-slate-200/60 leading-relaxed">
                                    @foreach($panduanPraktik['instruksi_kerja'] as $instruksi)
                                        <li>{{ $instruksi }}</li>
                                    @endforeach
                                </ul>
                            </div>
                        @endif

                        @if($dokumenPraktik)
                            <div class="p-4 bg-emerald-50 border border-emerald-200 rounded-xl text-xs text-emerald-800 flex items-center justify-between">
                                <div>
                                    <span class="font-bold block">Berkas Laporan / Foto Benda Kerja Asesi:</span>
                                    <span>{{ $dokumenPraktik->nama_dokumen }}</span>
                                </div>
                                <a href="{{ asset($dokumenPraktik->file_path) }}" target="_blank" class="text-blue-700 font-bold hover:underline">
                                    Buka File &rarr;
                                </a>
                            </div>
                        @endif

                        <div class="space-y-2 pt-3 border-t border-slate-100">
                            <label class="block text-xs font-bold text-slate-800 uppercase tracking-wider">
                                Catatan Asesor Penguji Terhadap Tugas Praktik:
                            </label>
                            <textarea name="catatan_praktik" rows="3" 
                                      placeholder="Tuliskan catatan hasil verifikasi tugas praktik / portofolio asesi..."
                                      class="w-full bg-white border border-slate-200 rounded-xl p-3.5 text-xs sm:text-sm text-slate-800 focus:ring-2 focus:ring-blue-500 outline-hidden">{{ $recordIa02->catatan_asesor ?? ($recordIa02->data_jawaban['catatan_praktik'] ?? '') }}</textarea>
                        </div>

                        <!-- Action Bar Tab 2: Praktik -->
                        <div class="pt-4 border-t border-slate-200 flex items-center justify-between gap-3 flex-wrap">
                            @if($pendaftaran->skema->hasInstrumen('FR.IA.01'))
                                <button type="button" 
                                        @click="activeTab = 'observasi'" 
                                        class="px-5 py-2.5 rounded-xl border border-slate-300 text-slate-700 bg-white hover:bg-slate-50 font-semibold text-xs transition-colors cursor-pointer">
                                    &larr; Kembali ke Observasi
                                </button>
                            @else
                                <a href="{{ (url()->previous() && url()->previous() !== url()->current()) ? url()->previous() : route('asesor.daftar-peserta') }}" 
                                   onclick="if (document.referrer && document.referrer !== window.location.href) { window.location.href = document.referrer; return false; } else if (window.history.length > 1) { window.history.back(); return false; }"
                                   class="px-5 py-2.5 rounded-xl border border-slate-300 text-slate-700 bg-white hover:bg-slate-50 font-semibold text-xs transition-colors cursor-pointer">
                                    &larr; Kembali
                                </a>
                            @endif
                            <div class="flex items-center gap-2">
                                <button type="submit" 
                                        name="tab_action" 
                                        value="praktik"
                                        formnovalidate
                                        class="px-5 py-2.5 rounded-xl bg-blue-600 hover:bg-blue-700 text-white font-bold text-xs shadow-xs transition-colors cursor-pointer">
                                    Simpan Catatan Praktik (FR.IA.02)
                                </button>
                                @if($pendaftaran->skema->hasInstrumen('FR.IA.03'))
                                    <button type="button" 
                                            @click="activeTab = 'lisan'" 
                                            class="px-4 py-2.5 rounded-xl bg-slate-100 hover:bg-slate-200 text-slate-700 font-semibold text-xs transition-colors cursor-pointer">
                                        Lanjut ke Pertanyaan Lisan &rarr;
                                    </button>
                                @else
                                    <button type="button" 
                                            @click="activeTab = 'rekap'" 
                                            class="px-4 py-2.5 rounded-xl bg-slate-100 hover:bg-slate-200 text-slate-700 font-semibold text-xs transition-colors cursor-pointer">
                                        Lanjut ke Rekap & Rekomendasi &rarr;
                                    </button>
                                @endif
                            </div>
                        </div>
                    </div>
                </div>
            @endif

            <!-- =====================================================================
                 TAB 3: FR.IA.03 / IA.07 PERTANYAAN LISAN PENDUKUNG OBSERVASI
                 ===================================================================== -->
            @if($pendaftaran->skema->hasInstrumen('FR.IA.03'))
                <div x-show="activeTab === 'lisan'" class="space-y-5">
                    <div class="bg-white rounded-2xl border border-slate-200/80 p-5 shadow-xs">
                        <h2 class="text-base font-bold text-slate-900">FR.IA.03 Pertanyaan Lisan / Wawancara Pendukung Praktik</h2>
                        <p class="text-xs text-slate-500 mt-0.5">Ajukan pertanyaan konseptual berikut untuk memverifikasi pemahaman mendalam asesi, catat ringkasan tanggapan, dan beri nilai K/BK.</p>
                    </div>

                    <div class="space-y-4">
                        @foreach($daftarLisan as $no => $item)
                            @php
                                $valLisanK = $savedPenilaianLisan[$no] ?? 'K';
                            @endphp
                            <div class="bg-white rounded-2xl border border-slate-200/80 p-6 shadow-xs space-y-4">
                                <div class="flex items-start justify-between gap-4 border-b border-slate-100 pb-3">
                                    <div>
                                        <div class="flex items-center gap-2">
                                            <span class="text-xs font-bold text-blue-600 uppercase tracking-wider">Pertanyaan Lisan {{ $no }}</span>
                                            @if(!empty($item['kuk']))
                                                <span class="text-[11px] text-slate-400 font-medium">&bull; {{ $item['kuk'] }}</span>
                                            @endif
                                        </div>
                                        <h4 class="text-sm font-bold text-slate-900 mt-0.5">{{ $item['tanya'] }}</h4>
                                    </div>

                                    <div class="flex items-center gap-2 flex-shrink-0">
                                        <div>
                                            <input type="radio" 
                                                   id="lisan_{{ $no }}_k" 
                                                   name="penilaian_lisan[{{ $no }}]" 
                                                   value="K" 
                                                   {{ $valLisanK === 'K' ? 'checked' : '' }} 
                                                   class="custom-radio-k hidden peer">
                                            <label for="lisan_{{ $no }}_k" 
                                                   class="px-3.5 py-1.5 rounded-lg border border-slate-200 bg-white text-slate-600 font-semibold text-xs cursor-pointer flex items-center justify-center hover:bg-slate-50 transition-all">
                                                K
                                            </label>
                                        </div>

                                        <div>
                                            <input type="radio" 
                                                   id="lisan_{{ $no }}_bk" 
                                                   name="penilaian_lisan[{{ $no }}]" 
                                                   value="BK" 
                                                   {{ $valLisanK === 'BK' ? 'checked' : '' }} 
                                                   class="custom-radio-bk hidden peer">
                                            <label for="lisan_{{ $no }}_bk" 
                                                   class="px-3.5 py-1.5 rounded-lg border border-slate-200 bg-white text-slate-600 font-semibold text-xs cursor-pointer flex items-center justify-center hover:bg-slate-50 transition-all">
                                                BK
                                            </label>
                                        </div>
                                    </div>
                                </div>

                                <div class="bg-slate-50 p-3 rounded-xl border border-slate-200/60 text-xs text-slate-600">
                                    <span class="font-bold text-slate-700">Kunci / Rubrik Rujukan:</span> {{ $item['kunci'] }}
                                </div>

                                <div class="space-y-1">
                                    <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider">
                                        Ringkasan Tanggapan / Jawaban Lisan Asesi:
                                    </label>
                                    <textarea name="respon_lisan[{{ $no }}]" rows="2" 
                                              placeholder="Catat poin penting yang disampaikan oleh asesi saat wawancara..."
                                              class="w-full bg-white border border-slate-200 rounded-xl p-3 text-xs sm:text-sm text-slate-800 focus:ring-2 focus:ring-blue-500 outline-hidden">{{ $savedResponLisan[$no] ?? '' }}</textarea>
                                </div>
                            </div>
                        @endforeach
                    </div>

                    <!-- Action Bar Tab 3: Pertanyaan Lisan -->
                    <div class="bg-white rounded-2xl border border-slate-200/80 p-4 shadow-xs flex items-center justify-between gap-3 flex-wrap">
                        <button type="button" 
                                @click="activeTab = '{{ $pendaftaran->skema->hasInstrumen('FR.IA.02') ? 'praktik' : ($pendaftaran->skema->hasInstrumen('FR.IA.01') ? 'observasi' : '') }}'" 
                                class="px-5 py-2.5 rounded-xl border border-slate-300 text-slate-700 bg-white hover:bg-slate-50 font-semibold text-xs transition-colors cursor-pointer">
                            &larr; Tab Sebelumnya
                        </button>
                        <div class="flex items-center gap-2">
                            <button type="submit" 
                                    name="tab_action" 
                                    value="lisan"
                                    formnovalidate
                                    class="px-5 py-2.5 rounded-xl bg-blue-600 hover:bg-blue-700 text-white font-bold text-xs shadow-xs transition-colors cursor-pointer">
                                Simpan FR.IA.03 (Pertanyaan Lisan)
                            </button>
                            @if($pendaftaran->skema->hasInstrumen('FR.IA.04A'))
                                <button type="button" 
                                        @click="activeTab = 'proyek'" 
                                        class="px-4 py-2.5 rounded-xl bg-slate-100 hover:bg-slate-200 text-slate-700 font-semibold text-xs transition-colors cursor-pointer">
                                    Lanjut ke Proyek &rarr;
                                </button>
                            @else
                                <button type="button" 
                                        @click="activeTab = 'rekap'" 
                                        class="px-4 py-2.5 rounded-xl bg-slate-100 hover:bg-slate-200 text-slate-700 font-semibold text-xs transition-colors cursor-pointer">
                                    Lanjut ke Rekap & Rekomendasi &rarr;
                                </button>
                            @endif
                        </div>
                    </div>
                </div>
            @endif

            <!-- =====================================================================
                 TAB 5: FR.IA.04A PROYEK / TOR (JIKA AKTIF)
                 ===================================================================== -->
            @if($pendaftaran->skema->hasInstrumen('FR.IA.04A'))
                <div x-show="activeTab === 'proyek'" class="space-y-6">
                    <div class="bg-white rounded-2xl border border-slate-200/80 p-6 sm:p-7 shadow-xs space-y-4">
                        <div class="border-b border-slate-200/80 pb-3">
                            <span class="text-xs font-bold text-indigo-600 uppercase tracking-wider">FR.IA.04A &bull; Lembar Penilaian Proyek / TOR</span>
                            <h3 class="text-lg font-bold text-slate-900 mt-1">Evaluasi Hasil Proyek Asesi</h3>
                        </div>
                        <p class="text-xs text-slate-600">Periksa kesesuaian dokumen artefak proyek terhadap spesifikasi yang ditentukan pada Term of Reference.</p>
                    </div>

                    <!-- Action Bar Tab 4: Proyek -->
                    <div class="bg-white rounded-2xl border border-slate-200/80 p-4 shadow-xs flex items-center justify-between gap-3 flex-wrap">
                        <button type="button" 
                                @click="activeTab = '{{ $pendaftaran->skema->hasInstrumen('FR.IA.03') ? 'lisan' : ($pendaftaran->skema->hasInstrumen('FR.IA.02') ? 'praktik' : 'observasi') }}'" 
                                class="px-5 py-2.5 rounded-xl border border-slate-300 text-slate-700 bg-white hover:bg-slate-50 font-semibold text-xs transition-colors cursor-pointer">
                            &larr; Tab Sebelumnya
                        </button>
                        <div class="flex items-center gap-2">
                            <button type="submit" 
                                    name="tab_action" 
                                    value="proyek"
                                    formnovalidate
                                    class="px-5 py-2.5 rounded-xl bg-blue-600 hover:bg-blue-700 text-white font-bold text-xs shadow-xs transition-colors cursor-pointer">
                                Simpan FR.IA.04A (Proyek)
                            </button>
                            <button type="button" 
                                    @click="activeTab = 'rekap'" 
                                    class="px-4 py-2.5 rounded-xl bg-slate-100 hover:bg-slate-200 text-slate-700 font-semibold text-xs transition-colors cursor-pointer">
                                Lanjut ke Rekap & Rekomendasi &rarr;
                            </button>
                        </div>
                    </div>
                </div>
            @endif

            <!-- =====================================================================
                 TAB FINAL: FR.AK.02 REKAPITULASI, KEPUTUSAN & TANDA TANGAN
                 ===================================================================== -->
            <div x-show="activeTab === 'rekap'" class="space-y-6">
                <div class="bg-white rounded-2xl border border-slate-200/80 p-6 sm:p-7 shadow-xs space-y-6">
                    <div class="border-b border-slate-200/80 pb-4">
                        <div class="text-emerald-700 font-bold text-xs uppercase tracking-wider">
                            FR.AK.02 &bull; Rekapitulasi Hasil Penilaian Asesmen & Umpan Balik
                        </div>
                        <h2 class="text-xl font-bold text-slate-900 mt-1">Keputusan Akhir Asesor Penguji</h2>
                        <p class="text-slate-500 text-xs mt-0.5">Tetapkan rekomendasi kompetensi asesi berdasarkan pencapaian bukti observasi praktik, lisan, CBT, dan esai.</p>
                    </div>

                    <!-- Ringkasan Unit Kompetensi Skema -->
                    <div class="space-y-2">
                        <label class="block text-xs font-bold text-slate-800 uppercase tracking-wider">
                            Daftar Unit Kompetensi Teruji:
                        </label>
                        <div class="border border-slate-200 rounded-xl overflow-hidden text-xs">
                            <table class="w-full text-left">
                                <thead class="bg-slate-50 text-slate-600 font-bold border-b border-slate-200">
                                    <tr>
                                        <th class="p-3 w-12 text-center">No</th>
                                        <th class="p-3 w-40">Kode Unit</th>
                                        <th class="p-3">Judul Unit Kompetensi</th>
                                        <th class="p-3 text-center w-28">Status</th>
                                    </tr>
                                </thead>
                                <tbody class="divide-y divide-slate-100 text-slate-700">
                                    @foreach($pendaftaran->skema->unitKompetensi as $idx => $u)
                                        <tr>
                                            <td class="p-3 text-center font-bold">{{ $idx + 1 }}</td>
                                            <td class="p-3 font-mono font-bold text-blue-700">{{ $u->kode_unit }}</td>
                                            <td class="p-3">{{ $u->judul_unit }}</td>
                                            <td class="p-3 text-center">
                                                <span class="px-2 py-0.5 bg-emerald-50 text-emerald-700 font-bold rounded-md border border-emerald-200">
                                                    Kompeten
                                                </span>
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    </div>

                    <!-- Radio Keputusan Akhir (Kompeten vs Belum Kompeten) -->
                    <div class="space-y-2 pt-2">
                        <label class="block text-xs font-bold text-slate-800 uppercase tracking-wider">
                            Keputusan Rekomendasi Asesmen <span class="text-red-500">*</span>:
                        </label>
                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 pt-1">
                            <div>
                                <input type="radio" id="keputusan_k" name="keputusan" value="kompeten" 
                                       x-model="keputusan"
                                       class="hidden peer">
                                <label for="keputusan_k" 
                                       class="p-4 rounded-2xl border-2 border-slate-200 peer-checked:border-emerald-500 peer-checked:bg-emerald-50/50 cursor-pointer flex items-center gap-3.5 transition-all block">
                                    <div class="w-7 h-7 rounded-xl bg-emerald-600 text-white font-bold text-sm flex items-center justify-center flex-shrink-0">
                                        ✓
                                    </div>
                                    <div>
                                        <div class="font-bold text-slate-900 text-sm">KOMPETEN (K)</div>
                                        <div class="text-xs text-slate-500">Asesi telah memenuhi seluruh kriteria unjuk kerja SKKNI.</div>
                                    </div>
                                </label>
                            </div>

                            <div>
                                <input type="radio" id="keputusan_bk" name="keputusan" value="belum_kompeten" 
                                       x-model="keputusan"
                                       class="hidden peer">
                                <label for="keputusan_bk" 
                                       class="p-4 rounded-2xl border-2 border-slate-200 peer-checked:border-rose-500 peer-checked:bg-rose-50/50 cursor-pointer flex items-center gap-3.5 transition-all block">
                                    <div class="w-7 h-7 rounded-xl bg-rose-600 text-white font-bold text-sm flex items-center justify-center flex-shrink-0">
                                        ✕
                                    </div>
                                    <div>
                                        <div class="font-bold text-slate-900 text-sm">BELUM KOMPETEN (BK)</div>
                                        <div class="text-xs text-slate-500">Masih terdapat kriteria unjuk kerja yang belum terpenuhi.</div>
                                    </div>
                                </label>
                            </div>
                        </div>
                    </div>

                    <!-- Umpan Balik Asesor (FR.AK.03) -->
                    <div class="space-y-2 pt-2">
                        <label class="block text-xs font-bold text-slate-800 uppercase tracking-wider">
                            Catatan Umpan Balik & Rekomendasi Pembinaan (FR.AK.03):
                        </label>
                        <textarea name="catatan_rekomendasi" rows="4"
                                  placeholder="Tuliskan catatan umpan balik mengenai kinerja asesi dan arahan pengembangan kompetensi ke depan..."
                                  class="w-full bg-white border border-slate-200 rounded-xl p-4 text-xs sm:text-sm text-slate-800 focus:ring-2 focus:ring-blue-500 outline-hidden leading-relaxed">{{ $pendaftaran->rekomendasi->catatan_rekomendasi ?? 'Asesi telah menunjukkan kinerja dan pemahaman yang baik dalam seluruh tahapan uji kompetensi praktik maupun teori.' }}</textarea>
                    </div>

                    <!-- Dual Option Tanda Tangan Asesor -->
                    <div class="border-t border-slate-200/80 pt-6 space-y-4" x-data="signaturePadAsesor()">
                        <div class="flex items-center justify-between">
                            <div>
                                <label class="text-xs font-bold text-slate-800 uppercase tracking-wider block">Pengesahan Tanda Tangan Asesor Penguji</label>
                                <span class="text-xs text-slate-500">Pilih metode tanda tangan untuk mengesahkan keputusan FR.AK.02.</span>
                            </div>
                        </div>

                        <!-- Mode Canvas -->
                        <div class="space-y-2">
                            <div class="flex items-center justify-between">
                                <span class="text-xs text-slate-500 font-medium">Torehkan tanda tangan Anda di canvas bawah:</span>
                                <button type="button" @click="clearSignature()" class="text-xs text-red-600 hover:text-red-700 font-semibold px-2.5 py-1 bg-red-50 hover:bg-red-100 rounded-lg transition-colors">
                                    Reset Canvas
                                </button>
                            </div>
                            <div class="border border-slate-300 rounded-xl bg-white p-2 relative overflow-hidden">
                                <canvas id="canvasSignatureAsesor" class="w-full h-36 bg-slate-50/50 rounded-lg cursor-crosshair"></canvas>
                                <input type="hidden" name="tanda_tangan_asesor" id="inputSignatureAsesor" value="{{ $pendaftaran->rekomendasi->tanda_tangan_asesor ?? ($pendaftaran->tanda_tangan_asesor ?? '') }}">
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Bottom Submit Button for Assessor -->
                <div class="flex items-center justify-between gap-4 pt-2 flex-wrap">
                    <a href="{{ (url()->previous() && url()->previous() !== url()->current()) ? url()->previous() : route('asesor.daftar-peserta') }}" 
                       onclick="if (document.referrer && document.referrer !== window.location.href) { window.location.href = document.referrer; return false; } else if (window.history.length > 1) { window.history.back(); return false; }"
                       class="px-6 py-3 rounded-xl border border-slate-300 text-slate-700 bg-white hover:bg-slate-50 font-semibold text-sm transition-colors cursor-pointer">
                        &larr; Kembali
                    </a>

                    <div class="flex items-center gap-3">
                        <button type="submit" 
                                name="tab_action" 
                                value="rekap"
                                formnovalidate
                                class="px-6 py-3.5 rounded-xl bg-slate-100 hover:bg-slate-200 text-slate-800 font-bold text-sm transition-colors cursor-pointer border border-slate-300">
                            Simpan Draf Rekap & Rekomendasi
                        </button>
                        <button type="submit" 
                                name="tab_action" 
                                value="rekap_final"
                                class="px-8 py-3.5 rounded-xl bg-emerald-600 hover:bg-emerald-700 text-white font-bold text-sm shadow-md transition-all flex items-center gap-2 cursor-pointer">
                            <span>Sahkan dan Tutup Sesi Asesmen</span>
                        </button>
                    </div>
                </div>
            </div>
        </form>
    </div>
</div>
@endsection

@push('js')
<script>
    function signaturePadAsesor() {
        return {
            pad: null,
            init() {
                this.$nextTick(() => {
                    const canvas = document.getElementById('canvasSignatureAsesor');
                    const input = document.getElementById('inputSignatureAsesor');
                    if (!canvas) return;

                    const ratio = Math.max(window.devicePixelRatio || 1, 1);
                    canvas.width = canvas.offsetWidth * ratio;
                    canvas.height = canvas.offsetHeight * ratio;
                    canvas.getContext("2d").scale(ratio, ratio);

                    this.pad = new SignaturePad(canvas, {
                        backgroundColor: 'rgba(255, 255, 255, 0)',
                        penColor: '#0f172a'
                    });

                    if (input && input.value && input.value.startsWith('data:image')) {
                        this.pad.fromDataURL(input.value);
                    }

                    this.pad.addEventListener("endStroke", () => {
                        if (input) {
                            input.value = this.pad.toDataURL();
                        }
                    });
                });
            },
            clearSignature() {
                if (this.pad) {
                    this.pad.clear();
                    const input = document.getElementById('inputSignatureAsesor');
                    if (input) input.value = '';
                }
            }
        };
    }
</script>
@endpush

