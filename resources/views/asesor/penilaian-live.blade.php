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
        keputusan: '{{ old('keputusan', $pendaftaran->rekomendasi->keputusan ?? $saranRekomendasi) }}',
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
                    <a href="{{ route('asesor.skema.ak-07', $pendaftaran->skema_id) }}" target="_blank" class="inline-flex items-center gap-1.5 px-2.5 py-0.5 rounded-lg border border-slate-200 bg-white hover:bg-slate-50 text-slate-700 text-xs font-bold transition shadow-2xs" title="Buka Master FR.AK.07 untuk seluruh asesi pada skema">
                        <i class="fa-solid fa-file-pen text-indigo-600"></i>
                        <span>Master FR.AK.07</span>
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
                <div x-show="activeTab === 'observasi'" class="space-y-6">
                    <!-- Header Card FR.IA.01 -->
                    <div class="bg-white rounded-2xl border border-slate-200/80 p-5 sm:p-6 shadow-xs flex flex-col md:flex-row md:items-center justify-between gap-4">
                        <div>
                            <div class="flex items-center gap-2">
                                <span class="px-2.5 py-0.5 rounded-full bg-blue-50 text-blue-700 border border-blue-200 text-[11px] font-extrabold uppercase tracking-wider">
                                    FR.IA.01 &bull; Ceklis Observasi Aktivitas
                                </span>
                                @if(!empty($recordIa01))
                                    <span class="px-2 py-0.5 rounded-full bg-emerald-50 text-emerald-700 border border-emerald-200 text-[10px] font-bold">
                                        Data Tersimpan
                                    </span>
                                @endif
                            </div>
                            <h2 class="text-lg font-bold text-slate-900 mt-1">Ceklis Observasi Aktivitas di Tempat Kerja / TUK</h2>
                            <p class="text-xs text-slate-500 mt-0.5">Observasi langsung unjuk kerja asesi per kriteria unjuk kerja (KUK) berdasarkan standar industri / tempat kerja.</p>
                        </div>
                        <div class="flex items-center gap-2.5 flex-wrap">
                            <button type="button" 
                                    @click="setAllKGlobal()"
                                    class="px-3.5 py-2 bg-emerald-600 hover:bg-emerald-700 text-white rounded-xl text-xs font-bold transition shadow-xs inline-flex items-center gap-1.5 cursor-pointer"
                                    title="Tandai seluruh KUK pada seluruh unit kompetensi sebagai Kompeten (K)">
                                <i class="fa-solid fa-check-double"></i>
                                <span>Set Semua KUK Kompeten (K)</span>
                            </button>
                            <a href="{{ route('formulir.ia01', $pendaftaran->id) }}" target="_blank" 
                               class="px-3.5 py-2 bg-slate-100 hover:bg-slate-200 text-slate-700 border border-slate-300 rounded-xl text-xs font-bold transition inline-flex items-center gap-1.5 shadow-2xs" 
                               title="Buka Lembar Resmi Standar BNSP FR.IA.01 (Format Cetak)">
                                <i class="fa-solid fa-print text-slate-600"></i>
                                <span>Format Resmi BNSP (Cetak)</span>
                            </a>
                        </div>
                    </div>

                    <!-- Panduan Singkat Bagi Asesor -->
                    <div class="bg-amber-50/70 border border-amber-200/80 rounded-xl p-4 text-xs text-amber-900 flex items-start gap-3">
                        <i class="fa-solid fa-circle-info text-amber-600 mt-0.5 flex-shrink-0 text-sm"></i>
                        <div class="space-y-1">
                            <span class="font-bold block uppercase tracking-wider text-[11px] text-amber-950">Panduan Asesor:</span>
                            <p>Beri centang <strong>K</strong> jika asesi mendemonstrasikan unjuk kerja sesuai standar, atau <strong>BK</strong> bila belum. Kolom Penilaian Lanjut diisi bila diperlukan metode pembuktian tambahan.</p>
                        </div>
                    </div>

                    <!-- Unit Kompetensi Loops -->
                    @foreach($pendaftaran->skema->unitKompetensi as $idxUnit => $unit)
                        <div class="bg-white rounded-2xl border border-slate-200/80 shadow-xs overflow-hidden">
                            <!-- Unit Header -->
                            <div class="bg-slate-50/80 border-b border-slate-200/80 p-4 sm:p-5 flex flex-col sm:flex-row sm:items-center justify-between gap-3">
                                <div class="space-y-1">
                                    <div class="flex items-center gap-2">
                                        <span class="px-2 py-0.5 rounded-md bg-blue-100 text-blue-800 font-extrabold text-[11px]">
                                            Unit {{ $idxUnit + 1 }}
                                        </span>
                                        <span class="font-mono text-xs font-bold text-slate-600">{{ $unit->kode_unit }}</span>
                                    </div>
                                    <h3 class="text-sm sm:text-base font-bold text-slate-900">{{ $unit->judul_unit }}</h3>
                                </div>
                                <div>
                                    <button type="button" 
                                            @click="setAllK('{{ $unit->id }}')"
                                            class="px-3 py-1.5 bg-white hover:bg-emerald-50 text-emerald-700 hover:text-emerald-800 border border-emerald-300 rounded-lg text-xs font-bold transition shadow-2xs inline-flex items-center gap-1.5 cursor-pointer">
                                        <i class="fa-solid fa-check"></i>
                                        <span>Set Semua K Unit Ini</span>
                                    </button>
                                </div>
                            </div>

                            <!-- Table Elemen & KUK -->
                            <div class="overflow-x-auto">
                                <table class="w-full text-left text-xs">
                                    <thead class="bg-slate-100/70 text-slate-700 font-bold border-b border-slate-200 uppercase tracking-wider text-[11px]">
                                        <tr>
                                            <th class="p-3 w-10 text-center">No</th>
                                            <th class="p-3 w-1/4 min-w-[180px]">Elemen Kompetensi</th>
                                            <th class="p-3 min-w-[260px]">Kriteria Unjuk Kerja (KUK)</th>
                                            <th class="p-3 w-1/5 min-w-[180px]">Standar Industri / SOP</th>
                                            <th class="p-3 w-28 text-center">Pencapaian</th>
                                            <th class="p-3 w-36 min-w-[140px]">Penilaian Lanjut</th>
                                        </tr>
                                    </thead>
                                    <tbody class="divide-y divide-slate-100 text-slate-800">
                                        @forelse($unit->elemenKompetensi as $idxElem => $elem)
                                            @php
                                                $totalKuk = count($elem->kriteriaUnjukKerja);
                                            @endphp
                                            @if($totalKuk > 0)
                                                @foreach($elem->kriteriaUnjukKerja as $kIdx => $kuk)
                                                    @php
                                                        $valKuk = $savedPenilaianIa01[$kuk->id] ?? 'K';
                                                        $curStandar = $masterStandardsIa01[$kuk->id] 
                                                            ?? ($masterStandardsIa01[$elem->id] 
                                                            ?? 'Sesuai SOP / Standar Industri Tempat Kerja');
                                                        $curLanjut = $savedLanjutIa01[$kuk->id] ?? '';
                                                    @endphp
                                                    <tr class="hover:bg-slate-50/50 transition-colors">
                                                        @if($kIdx === 0)
                                                            <td rowspan="{{ $totalKuk }}" class="p-3 text-center font-bold align-top bg-slate-50/30 border-r border-slate-100 text-slate-500">
                                                                {{ $idxElem + 1 }}
                                                            </td>
                                                            <td rowspan="{{ $totalKuk }}" class="p-3 align-top bg-slate-50/30 border-r border-slate-100 space-y-1">
                                                                <span class="font-bold text-slate-900 block">{{ $elem->nomor_elemen }}. {{ $elem->nama_elemen }}</span>
                                                                @if(!empty($elem->pertanyaan_elemen))
                                                                    <span class="text-[11px] text-blue-600 italic block">"{{ $elem->pertanyaan_elemen }}"</span>
                                                                @endif
                                                            </td>
                                                        @endif

                                                        <td class="p-3 align-top space-y-1">
                                                            <div class="leading-relaxed">
                                                                <span class="font-bold font-mono text-blue-700 bg-blue-50 px-1.5 py-0.5 rounded text-[11px] mr-1">
                                                                    {{ $elem->nomor_elemen }}.{{ $kuk->nomor_kuk }}
                                                                </span>
                                                                <span>{{ $kuk->pernyataan_kuk }}</span>
                                                            </div>
                                                        </td>

                                                        <td class="p-2.5 align-top">
                                                            <textarea name="standar_industri[{{ $kuk->id }}]" 
                                                                      rows="2" 
                                                                      placeholder="Standar Industri..."
                                                                      class="w-full bg-slate-50/60 border border-slate-200 rounded-lg p-2 text-xs text-slate-700 focus:bg-white focus:ring-1 focus:ring-blue-500 outline-hidden resize-y">{{ $curStandar }}</textarea>
                                                        </td>

                                                        <td class="p-2.5 align-middle text-center">
                                                            <div class="flex items-center justify-center gap-1.5">
                                                                <div>
                                                                    <input type="radio" 
                                                                           id="kuk_{{ $kuk->id }}_k" 
                                                                           name="penilaian_kuk[{{ $kuk->id }}]" 
                                                                           value="K" 
                                                                           data-unit="{{ $unit->id }}"
                                                                           {{ $valKuk === 'K' ? 'checked' : '' }} 
                                                                           class="custom-radio-k hidden peer">
                                                                    <label for="kuk_{{ $kuk->id }}_k" 
                                                                           class="w-8 h-7 rounded-md border border-slate-200 bg-white text-slate-600 font-bold text-xs cursor-pointer flex items-center justify-center hover:bg-slate-50 transition-all select-none">
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
                                                                           class="w-8 h-7 rounded-md border border-slate-200 bg-white text-slate-600 font-bold text-xs cursor-pointer flex items-center justify-center hover:bg-slate-50 transition-all select-none">
                                                                        BK
                                                                    </label>
                                                                </div>
                                                            </div>
                                                        </td>

                                                        <td class="p-2.5 align-top">
                                                            <input type="text" 
                                                                   name="penilaian_lanjut[{{ $kuk->id }}]" 
                                                                   value="{{ $curLanjut }}" 
                                                                   placeholder="Tindak lanjut..."
                                                                   class="w-full bg-slate-50/60 border border-slate-200 rounded-lg p-2 text-xs text-slate-700 focus:bg-white focus:ring-1 focus:ring-blue-500 outline-hidden">
                                                        </td>
                                                    </tr>
                                                @endforeach
                                            @else
                                                <tr>
                                                    <td class="p-3 text-center text-slate-400">{{ $idxElem + 1 }}</td>
                                                    <td class="p-3 font-semibold text-slate-800">{{ $elem->nomor_elemen }}. {{ $elem->nama_elemen }}</td>
                                                    <td colspan="4" class="p-3 text-slate-400 italic">Belum ada Kriteria Unjuk Kerja pada elemen ini.</td>
                                                </tr>
                                            @endif
                                        @empty
                                            <tr>
                                                <td colspan="6" class="p-6 text-center text-slate-400 italic">Belum ada elemen kompetensi pada unit ini.</td>
                                            </tr>
                                        @endforelse
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    @endforeach

                    <!-- Bottom Card FR.IA.01: Catatan & Rekomendasi Observasi -->
                    <div class="bg-white rounded-2xl border border-slate-200/80 p-5 sm:p-6 shadow-xs space-y-5">
                        <div class="space-y-2">
                            <label class="block text-xs font-bold text-slate-800 uppercase tracking-wider">
                                Umpan Balik & Catatan Observasi Asesor Terhadap Kinerja Asesi:
                            </label>
                            <textarea name="catatan_observasi" rows="3" 
                                      placeholder="Tuliskan catatan hasil observasi unjuk kerja asesi..."
                                      class="w-full bg-white border border-slate-200 rounded-xl p-3.5 text-xs sm:text-sm text-slate-800 focus:ring-2 focus:ring-blue-500 outline-hidden">{{ $savedCatatanIa01 ?: 'Asesi telah diobservasi secara langsung dan mampu mendemonstrasikan seluruh kriteria unjuk kerja dengan baik sesuai SOP.' }}</textarea>
                        </div>

                        <div class="space-y-2 pt-2 border-t border-slate-100">
                            <label class="block text-xs font-bold text-slate-800 uppercase tracking-wider">
                                Rekomendasi Hasil Observasi (FR.IA.01):
                            </label>
                            <div class="grid grid-cols-1 sm:grid-cols-2 gap-3 pt-1">
                                <div>
                                    <input type="radio" id="rekomendasi_ia01_k" name="rekomendasi_ia01" value="K" 
                                           {{ $savedRekomendasiIa01 !== 'BK' ? 'checked' : '' }}
                                           class="hidden peer">
                                    <label for="rekomendasi_ia01_k" 
                                           class="p-3 rounded-xl border border-slate-200 peer-checked:border-emerald-500 peer-checked:bg-emerald-50/50 cursor-pointer flex items-center gap-3 transition-all">
                                        <div class="w-6 h-6 rounded-lg bg-emerald-600 text-white font-bold text-xs flex items-center justify-center flex-shrink-0">✓</div>
                                        <div>
                                            <div class="font-bold text-slate-900 text-xs">Kompeten (K)</div>
                                            <div class="text-[11px] text-slate-500">Seluruh KUK dapat didemonstrasikan dengan baik.</div>
                                        </div>
                                    </label>
                                </div>
                                <div>
                                    <input type="radio" id="rekomendasi_ia01_bk" name="rekomendasi_ia01" value="BK" 
                                           {{ $savedRekomendasiIa01 === 'BK' ? 'checked' : '' }}
                                           class="hidden peer">
                                    <label for="rekomendasi_ia01_bk" 
                                           class="p-3 rounded-xl border border-slate-200 peer-checked:border-rose-500 peer-checked:bg-rose-50/50 cursor-pointer flex items-center gap-3 transition-all">
                                        <div class="w-6 h-6 rounded-lg bg-rose-600 text-white font-bold text-xs flex items-center justify-center flex-shrink-0">✕</div>
                                        <div>
                                            <div class="font-bold text-slate-900 text-xs">Belum Kompeten (BK)</div>
                                            <div class="text-[11px] text-slate-500">Terdapat KUK yang belum terpenuhi.</div>
                                        </div>
                                    </label>
                                </div>
                            </div>
                        </div>

                        <!-- Action Bar Tab 1: Observasi -->
                        <div class="pt-4 border-t border-slate-200 flex items-center justify-between gap-3 flex-wrap">
                            <span class="text-xs text-slate-500">Simpan formulir ini untuk memperbarui bukti penilaian KUK asesi.</span>
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
                                @elseif($pendaftaran->skema->hasInstrumen('FR.IA.03'))
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
                            <div class="flex items-center gap-2 flex-wrap">
                                <a href="{{ route('formulir.ia02', $pendaftaran->id) }}" target="_blank" 
                                   class="px-3.5 py-2 bg-slate-100 hover:bg-slate-200 text-slate-700 border border-slate-300 rounded-xl text-xs font-bold transition-colors inline-flex items-center gap-1.5 shadow-2xs" 
                                   title="Buka Lembar Resmi Standar BNSP FR.IA.02 (Format Cetak)">
                                    <i class="fa-solid fa-print text-slate-600"></i>
                                    <span>Format Resmi BNSP (Cetak)</span>
                                </a>
                                @if($dokumenPraktik)
                                    <a href="{{ asset($dokumenPraktik->file_path) }}" target="_blank" 
                                       class="px-4 py-2.5 bg-blue-600 hover:bg-blue-700 text-white text-xs font-bold rounded-xl shadow-xs transition-colors flex items-center gap-2">
                                        <span>Unduh Berkas Asesi</span>
                                    </a>
                                @endif
                            </div>
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

                        <div class="space-y-2 pt-2 border-t border-slate-100">
                            <label class="block text-xs font-bold text-slate-800 uppercase tracking-wider">
                                Rekomendasi Tugas Praktik (FR.IA.02):
                            </label>
                            <div class="grid grid-cols-1 sm:grid-cols-2 gap-3 pt-1">
                                <div>
                                    <input type="radio" id="rekomendasi_praktik_k" name="rekomendasi_praktik" value="K" 
                                           {{ $savedRekomendasiPraktik !== 'BK' ? 'checked' : '' }}
                                           class="hidden peer">
                                    <label for="rekomendasi_praktik_k" 
                                           class="p-3 rounded-xl border border-slate-200 peer-checked:border-emerald-500 peer-checked:bg-emerald-50/50 cursor-pointer flex items-center gap-3 transition-all">
                                        <div class="w-6 h-6 rounded-lg bg-emerald-600 text-white font-bold text-xs flex items-center justify-center flex-shrink-0">✓</div>
                                        <div>
                                            <div class="font-bold text-slate-900 text-xs">Memenuhi (K)</div>
                                            <div class="text-[11px] text-slate-500">Tugas praktik terlaksana sesuai instruksi kerja.</div>
                                        </div>
                                    </label>
                                </div>
                                <div>
                                    <input type="radio" id="rekomendasi_praktik_bk" name="rekomendasi_praktik" value="BK" 
                                           {{ $savedRekomendasiPraktik === 'BK' ? 'checked' : '' }}
                                           class="hidden peer">
                                    <label for="rekomendasi_praktik_bk" 
                                           class="p-3 rounded-xl border border-slate-200 peer-checked:border-rose-500 peer-checked:bg-rose-50/50 cursor-pointer flex items-center gap-3 transition-all">
                                        <div class="w-6 h-6 rounded-lg bg-rose-600 text-white font-bold text-xs flex items-center justify-center flex-shrink-0">✕</div>
                                        <div>
                                            <div class="font-bold text-slate-900 text-xs">Belum Memenuhi (BK)</div>
                                            <div class="text-[11px] text-slate-500">Tugas praktik belum memenuhi spesifikasi.</div>
                                        </div>
                                    </label>
                                </div>
                            </div>
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
                    <div class="bg-white rounded-2xl border border-slate-200/80 p-5 shadow-xs flex flex-col sm:flex-row sm:items-center justify-between gap-4">
                        <div>
                            <h2 class="text-base font-bold text-slate-900">FR.IA.03 Pertanyaan Lisan / Wawancara Pendukung Praktik</h2>
                            <p class="text-xs text-slate-500 mt-0.5">Ajukan pertanyaan konseptual berikut untuk memverifikasi pemahaman mendalam asesi, catat ringkasan tanggapan, dan beri nilai K/BK.</p>
                        </div>
                        <div class="flex items-center gap-2 flex-wrap">
                            <a href="{{ route('formulir.ia03', $pendaftaran->id) }}" target="_blank" 
                               class="px-3.5 py-2 bg-slate-100 hover:bg-slate-200 text-slate-700 border border-slate-300 rounded-xl text-xs font-bold transition-colors inline-flex items-center gap-1.5 shadow-2xs" 
                               title="Buka Lembar Resmi Standar BNSP FR.IA.03 (Format Cetak)">
                                <i class="fa-solid fa-print text-slate-600"></i>
                                <span>Format Resmi BNSP (Cetak)</span>
                            </a>
                        </div>
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

                    <!-- Bottom Card Tab 3: Catatan & Rekomendasi Pertanyaan Lisan -->
                    <div class="bg-white rounded-2xl border border-slate-200/80 p-5 sm:p-6 shadow-xs space-y-4">
                        <div class="space-y-1">
                            <label class="block text-xs font-bold text-slate-800 uppercase tracking-wider">
                                Catatan & Kesimpulan Asesor untuk Pertanyaan Lisan:
                            </label>
                            <textarea name="catatan_lisan" rows="3" 
                                      placeholder="Tuliskan catatan umum mengenai respons dan pemahaman asesi..."
                                      class="w-full bg-white border border-slate-200 rounded-xl p-3 text-xs sm:text-sm text-slate-800 focus:ring-2 focus:ring-blue-500 outline-hidden">{{ $savedCatatanLisan ?: 'Asesi mampu menjawab pertanyaan lisan pendukung dengan baik.' }}</textarea>
                        </div>

                        <div class="space-y-2 pt-2 border-t border-slate-100">
                            <label class="block text-xs font-bold text-slate-800 uppercase tracking-wider">
                                Rekomendasi Pertanyaan Lisan (FR.IA.03):
                            </label>
                            <div class="grid grid-cols-1 sm:grid-cols-2 gap-3 pt-1">
                                <div>
                                    <input type="radio" id="rekomendasi_lisan_k" name="rekomendasi_lisan" value="K" 
                                           {{ $savedRekomendasiLisan !== 'BK' ? 'checked' : '' }}
                                           class="hidden peer">
                                    <label for="rekomendasi_lisan_k" 
                                           class="p-3 rounded-xl border border-slate-200 peer-checked:border-emerald-500 peer-checked:bg-emerald-50/50 cursor-pointer flex items-center gap-3 transition-all">
                                        <div class="w-6 h-6 rounded-lg bg-emerald-600 text-white font-bold text-xs flex items-center justify-center flex-shrink-0">✓</div>
                                        <div>
                                            <div class="font-bold text-slate-900 text-xs">Kompeten (K)</div>
                                            <div class="text-[11px] text-slate-500">Mampu menjawab pertanyaan rujukan dengan tepat.</div>
                                        </div>
                                    </label>
                                </div>
                                <div>
                                    <input type="radio" id="rekomendasi_lisan_bk" name="rekomendasi_lisan" value="BK" 
                                           {{ $savedRekomendasiLisan === 'BK' ? 'checked' : '' }}
                                           class="hidden peer">
                                    <label for="rekomendasi_lisan_bk" 
                                           class="p-3 rounded-xl border border-slate-200 peer-checked:border-rose-500 peer-checked:bg-rose-50/50 cursor-pointer flex items-center gap-3 transition-all">
                                        <div class="w-6 h-6 rounded-lg bg-rose-600 text-white font-bold text-xs flex items-center justify-center flex-shrink-0">✕</div>
                                        <div>
                                            <div class="font-bold text-slate-900 text-xs">Belum Kompeten (BK)</div>
                                            <div class="text-[11px] text-slate-500">Terdapat jawaban konsep yang belum memadai.</div>
                                        </div>
                                    </label>
                                </div>
                            </div>
                        </div>

                        <!-- Action Bar Tab 3: Pertanyaan Lisan -->
                        <div class="pt-4 border-t border-slate-200 flex items-center justify-between gap-3 flex-wrap">
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
                                @elseif($pendaftaran->skema->hasInstrumen('FR.IA.11'))
                                    <button type="button" 
                                            @click="activeTab = 'mutu'" 
                                            class="px-4 py-2.5 rounded-xl bg-slate-100 hover:bg-slate-200 text-slate-700 font-semibold text-xs transition-colors cursor-pointer">
                                        Lanjut ke Ceklis Mutu &rarr;
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
                 TAB 4: FR.IA.04A PROYEK / TOR (JIKA AKTIF)
                 ===================================================================== -->
            @if($pendaftaran->skema->hasInstrumen('FR.IA.04A'))
                <div x-show="activeTab === 'proyek'" class="space-y-6">
                    <div class="bg-white rounded-2xl border border-slate-200/80 p-6 sm:p-7 shadow-xs space-y-5">
                        <div class="border-b border-slate-200/80 pb-3">
                            <span class="text-xs font-bold text-indigo-600 uppercase tracking-wider">FR.IA.04A &bull; Lembar Penilaian Proyek / TOR</span>
                            <h3 class="text-lg font-bold text-slate-900 mt-1">Evaluasi Hasil Proyek Asesi</h3>
                            <p class="text-xs text-slate-600 mt-1">Periksa kesesuaian dokumen artefak proyek terhadap spesifikasi yang ditentukan pada Term of Reference.</p>
                        </div>

                        <div class="space-y-2">
                            <label class="block text-xs font-bold text-slate-800 uppercase tracking-wider">
                                Catatan Hasil Evaluasi Proyek Asesi:
                            </label>
                            <textarea name="catatan_proyek" rows="3" 
                                      placeholder="Tuliskan catatan hasil verifikasi proyek / artefak kerja asesi..."
                                      class="w-full bg-white border border-slate-200 rounded-xl p-3.5 text-xs sm:text-sm text-slate-800 focus:ring-2 focus:ring-blue-500 outline-hidden">{{ $savedCatatanIa04a ?: 'Hasil proyek asesi telah diperiksa dan sesuai dengan spesifikasi TOR.' }}</textarea>
                        </div>

                        <div class="space-y-2 pt-2 border-t border-slate-100">
                            <label class="block text-xs font-bold text-slate-800 uppercase tracking-wider">
                                Rekomendasi Proyek (FR.IA.04A):
                            </label>
                            <div class="grid grid-cols-1 sm:grid-cols-2 gap-3 pt-1">
                                <div>
                                    <input type="radio" id="rekomendasi_proyek_k" name="rekomendasi_proyek" value="K" 
                                           {{ $savedRekomendasiIa04a !== 'BK' ? 'checked' : '' }}
                                           class="hidden peer">
                                    <label for="rekomendasi_proyek_k" 
                                           class="p-3 rounded-xl border border-slate-200 peer-checked:border-emerald-500 peer-checked:bg-emerald-50/50 cursor-pointer flex items-center gap-3 transition-all">
                                        <div class="w-6 h-6 rounded-lg bg-emerald-600 text-white font-bold text-xs flex items-center justify-center flex-shrink-0">✓</div>
                                        <div>
                                            <div class="font-bold text-slate-900 text-xs">Memenuhi (K)</div>
                                            <div class="text-[11px] text-slate-500">Proyek memenuhi standar spesifikasi TOR.</div>
                                        </div>
                                    </label>
                                </div>
                                <div>
                                    <input type="radio" id="rekomendasi_proyek_bk" name="rekomendasi_proyek" value="BK" 
                                           {{ $savedRekomendasiIa04a === 'BK' ? 'checked' : '' }}
                                           class="hidden peer">
                                    <label for="rekomendasi_proyek_bk" 
                                           class="p-3 rounded-xl border border-slate-200 peer-checked:border-rose-500 peer-checked:bg-rose-50/50 cursor-pointer flex items-center gap-3 transition-all">
                                        <div class="w-6 h-6 rounded-lg bg-rose-600 text-white font-bold text-xs flex items-center justify-center flex-shrink-0">✕</div>
                                        <div>
                                            <div class="font-bold text-slate-900 text-xs">Belum Memenuhi (BK)</div>
                                            <div class="text-[11px] text-slate-500">Terdapat parameter proyek yang belum tercapai.</div>
                                        </div>
                                    </label>
                                </div>
                            </div>
                        </div>

                        <!-- Action Bar Tab 4: Proyek -->
                        <div class="pt-4 border-t border-slate-200 flex items-center justify-between gap-3 flex-wrap">
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
                                @if($pendaftaran->skema->hasInstrumen('FR.IA.11'))
                                    <button type="button" 
                                            @click="activeTab = 'mutu'" 
                                            class="px-4 py-2.5 rounded-xl bg-slate-100 hover:bg-slate-200 text-slate-700 font-semibold text-xs transition-colors cursor-pointer">
                                        Lanjut ke Ceklis Mutu &rarr;
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
                 TAB 5: FR.IA.11 CEKLIS MUTU PRODUK / JASA (JIKA AKTIF)
                 ===================================================================== -->
            @if($pendaftaran->skema->hasInstrumen('FR.IA.11'))
                <div x-show="activeTab === 'mutu'" class="space-y-6">
                    <div class="bg-white rounded-2xl border border-slate-200/80 p-6 sm:p-7 shadow-xs space-y-5">
                        <div class="border-b border-slate-200/80 pb-3">
                            <span class="text-xs font-bold text-amber-600 uppercase tracking-wider">FR.IA.11 &bull; Ceklis Verifikasi Mutu</span>
                            <h3 class="text-lg font-bold text-slate-900 mt-1">Pemeriksaan Kualitas Hasil Kerja / Produk Asesi</h3>
                            <p class="text-xs text-slate-600 mt-1">Verifikasi kriteria mutu produk akhir berdasarkan standar toleransi industri dan mutu BNSP.</p>
                        </div>

                        <div class="space-y-2">
                            <label class="block text-xs font-bold text-slate-800 uppercase tracking-wider">
                                Catatan Hasil Ceklis Mutu:
                            </label>
                            <textarea name="catatan_mutu" rows="3" 
                                      placeholder="Tuliskan catatan verifikasi spesifikasi mutu produk asesi..."
                                      class="w-full bg-white border border-slate-200 rounded-xl p-3.5 text-xs sm:text-sm text-slate-800 focus:ring-2 focus:ring-blue-500 outline-hidden">{{ $savedCatatanIa11 ?: 'Ceklis mutu dan spesifikasi produk asesi telah diverifikasi dan memenuhi standar.' }}</textarea>
                        </div>

                        <div class="space-y-2 pt-2 border-t border-slate-100">
                            <label class="block text-xs font-bold text-slate-800 uppercase tracking-wider">
                                Rekomendasi Ceklis Mutu (FR.IA.11):
                            </label>
                            <div class="grid grid-cols-1 sm:grid-cols-2 gap-3 pt-1">
                                <div>
                                    <input type="radio" id="rekomendasi_mutu_k" name="rekomendasi_mutu" value="K" 
                                           {{ $savedRekomendasiIa11 !== 'BK' ? 'checked' : '' }}
                                           class="hidden peer">
                                    <label for="rekomendasi_mutu_k" 
                                           class="p-3 rounded-xl border border-slate-200 peer-checked:border-emerald-500 peer-checked:bg-emerald-50/50 cursor-pointer flex items-center gap-3 transition-all">
                                        <div class="w-6 h-6 rounded-lg bg-emerald-600 text-white font-bold text-xs flex items-center justify-center flex-shrink-0">✓</div>
                                        <div>
                                            <div class="font-bold text-slate-900 text-xs">Sesuai Mutu (K)</div>
                                            <div class="text-[11px] text-slate-500">Produk memenuhi standar mutu dan toleransi.</div>
                                        </div>
                                    </label>
                                </div>
                                <div>
                                    <input type="radio" id="rekomendasi_mutu_bk" name="rekomendasi_mutu" value="BK" 
                                           {{ $savedRekomendasiIa11 === 'BK' ? 'checked' : '' }}
                                           class="hidden peer">
                                    <label for="rekomendasi_mutu_bk" 
                                           class="p-3 rounded-xl border border-slate-200 peer-checked:border-rose-500 peer-checked:bg-rose-50/50 cursor-pointer flex items-center gap-3 transition-all">
                                        <div class="w-6 h-6 rounded-lg bg-rose-600 text-white font-bold text-xs flex items-center justify-center flex-shrink-0">✕</div>
                                        <div>
                                            <div class="font-bold text-slate-900 text-xs">Belum Sesuai (BK)</div>
                                            <div class="text-[11px] text-slate-500">Terdapat cacat atau toleransi di luar batas.</div>
                                        </div>
                                    </label>
                                </div>
                            </div>
                        </div>

                        <!-- Action Bar Tab 5: Mutu -->
                        <div class="pt-4 border-t border-slate-200 flex items-center justify-between gap-3 flex-wrap">
                            <button type="button" 
                                    @click="activeTab = '{{ $pendaftaran->skema->hasInstrumen('FR.IA.04A') ? 'proyek' : ($pendaftaran->skema->hasInstrumen('FR.IA.03') ? 'lisan' : ($pendaftaran->skema->hasInstrumen('FR.IA.02') ? 'praktik' : 'observasi')) }}'" 
                                    class="px-5 py-2.5 rounded-xl border border-slate-300 text-slate-700 bg-white hover:bg-slate-50 font-semibold text-xs transition-colors cursor-pointer">
                                &larr; Tab Sebelumnya
                            </button>
                            <div class="flex items-center gap-2">
                                <button type="submit" 
                                        name="tab_action" 
                                        value="mutu"
                                        formnovalidate
                                        class="px-5 py-2.5 rounded-xl bg-blue-600 hover:bg-blue-700 text-white font-bold text-xs shadow-xs transition-colors cursor-pointer">
                                    Simpan FR.IA.11 (Ceklis Mutu)
                                </button>
                                <button type="button" 
                                        @click="activeTab = 'rekap'" 
                                        class="px-4 py-2.5 rounded-xl bg-slate-100 hover:bg-slate-200 text-slate-700 font-semibold text-xs transition-colors cursor-pointer">
                                    Lanjut ke Rekap & Rekomendasi &rarr;
                                </button>
                            </div>
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

                    <!-- Rangkuman Bukti Hasil Asesmen (Portofolio Bukti Terkumpul Asesi) -->
                    <div class="space-y-3">
                        <div class="flex items-center justify-between flex-wrap gap-2">
                            <div>
                                <label class="block text-xs font-bold text-slate-800 uppercase tracking-wider">
                                    Rangkuman Bukti Hasil Asesmen yang Dikerjakan Asesi:
                                </label>
                                <span class="text-[11px] text-slate-500">Bukti seluruh formulir yang telah diisi dan diverifikasi untuk bahan pertimbangan rekomendasi akhir.</span>
                            </div>
                            <span class="text-[11px] font-semibold text-blue-600 bg-blue-50 border border-blue-200 px-2.5 py-0.5 rounded-full">
                                Portofolio Bukti Terpadu
                            </span>
                        </div>

                        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-3.5">
                            <!-- Card IA.01 -->
                            @if($pendaftaran->skema->hasInstrumen('FR.IA.01'))
                                <div class="p-4 rounded-xl border {{ $ringkasanBukti['ia01']['bk_count'] > 0 ? 'border-rose-200 bg-rose-50/40' : ($ringkasanBukti['ia01']['k_count'] > 0 ? 'border-emerald-200 bg-emerald-50/40' : 'border-slate-200 bg-slate-50/40') }} space-y-2">
                                    <div class="flex items-center justify-between">
                                        <span class="text-xs font-bold text-slate-800">1. FR.IA.01 Observasi</span>
                                        @if($ringkasanBukti['ia01']['bk_count'] > 0)
                                            <span class="px-2 py-0.5 rounded text-[10px] font-bold bg-rose-100 text-rose-800 border border-rose-300">BK ({{ $ringkasanBukti['ia01']['bk_count'] }})</span>
                                        @elseif($ringkasanBukti['ia01']['k_count'] > 0)
                                            <span class="px-2 py-0.5 rounded text-[10px] font-bold bg-emerald-100 text-emerald-800 border border-emerald-300">Kompeten ({{ $ringkasanBukti['ia01']['k_count'] }})</span>
                                        @else
                                            <span class="px-2 py-0.5 rounded text-[10px] font-medium bg-slate-100 text-slate-600">Belum Dinilai</span>
                                        @endif
                                    </div>
                                    <div class="text-[11px] text-slate-600">
                                        Total KUK Teruji: <strong>{{ $ringkasanBukti['ia01']['k_count'] + $ringkasanBukti['ia01']['bk_count'] }} / {{ $ringkasanBukti['ia01']['total_kuk'] }} KUK</strong>
                                    </div>
                                    @if(!empty($ringkasanBukti['ia01']['catatan']))
                                        <p class="text-[11px] text-slate-500 line-clamp-2 italic">"{{ $ringkasanBukti['ia01']['catatan'] }}"</p>
                                    @endif
                                </div>
                            @endif

                            <!-- Card IA.02 -->
                            @if($pendaftaran->skema->hasInstrumen('FR.IA.02'))
                                <div class="p-4 rounded-xl border {{ $ringkasanBukti['ia02']['rekomendasi'] === 'BK' ? 'border-rose-200 bg-rose-50/40' : 'border-emerald-200 bg-emerald-50/40' }} space-y-2">
                                    <div class="flex items-center justify-between">
                                        <span class="text-xs font-bold text-slate-800">2. FR.IA.02 Tugas Praktik</span>
                                        <span class="px-2 py-0.5 rounded text-[10px] font-bold {{ $ringkasanBukti['ia02']['rekomendasi'] === 'BK' ? 'bg-rose-100 text-rose-800 border border-rose-300' : 'bg-emerald-100 text-emerald-800 border border-emerald-300' }}">
                                            {{ $ringkasanBukti['ia02']['rekomendasi'] === 'BK' ? 'BK' : 'K' }}
                                        </span>
                                    </div>
                                    <div class="text-[11px] text-slate-600">
                                        Bukti Fisik: <strong>{{ $ringkasanBukti['ia02']['has_dokumen'] ? 'Laporan Terunggah' : 'Observasi Demonstrasi' }}</strong>
                                    </div>
                                    @if(!empty($ringkasanBukti['ia02']['catatan']))
                                        <p class="text-[11px] text-slate-500 line-clamp-2 italic">"{{ $ringkasanBukti['ia02']['catatan'] }}"</p>
                                    @endif
                                </div>
                            @endif

                            <!-- Card IA.03 / IA.07 -->
                            @if($pendaftaran->skema->hasInstrumen('FR.IA.03') || $pendaftaran->skema->hasInstrumen('FR.IA.07'))
                                <div class="p-4 rounded-xl border {{ $ringkasanBukti['ia03']['bk_count'] > 0 ? 'border-rose-200 bg-rose-50/40' : ($ringkasanBukti['ia03']['k_count'] > 0 ? 'border-emerald-200 bg-emerald-50/40' : 'border-slate-200 bg-slate-50/40') }} space-y-2">
                                    <div class="flex items-center justify-between">
                                        <span class="text-xs font-bold text-slate-800">3. FR.IA.03 Pertanyaan Lisan</span>
                                        @if($ringkasanBukti['ia03']['bk_count'] > 0)
                                            <span class="px-2 py-0.5 rounded text-[10px] font-bold bg-rose-100 text-rose-800 border border-rose-300">BK</span>
                                        @elseif($ringkasanBukti['ia03']['k_count'] > 0)
                                            <span class="px-2 py-0.5 rounded text-[10px] font-bold bg-emerald-100 text-emerald-800 border border-emerald-300">Kompeten</span>
                                        @else
                                            <span class="px-2 py-0.5 rounded text-[10px] font-medium bg-slate-100 text-slate-600">Belum Dinilai</span>
                                        @endif
                                    </div>
                                    <div class="text-[11px] text-slate-600">
                                        Hasil Tanya Jawab: <strong>{{ $ringkasanBukti['ia03']['k_count'] }} K, {{ $ringkasanBukti['ia03']['bk_count'] }} BK</strong>
                                    </div>
                                    @if(!empty($ringkasanBukti['ia03']['catatan']))
                                        <p class="text-[11px] text-slate-500 line-clamp-2 italic">"{{ $ringkasanBukti['ia03']['catatan'] }}"</p>
                                    @endif
                                </div>
                            @endif

                            <!-- Card IA.05 CBT (Jika ada di skema) -->
                            @if($pendaftaran->skema->hasInstrumen('FR.IA.05'))
                                <div class="p-4 rounded-xl border {{ $ringkasanBukti['ia05']['status'] === 'BK' ? 'border-rose-200 bg-rose-50/40' : ($ringkasanBukti['ia05']['status'] === 'K' ? 'border-emerald-200 bg-emerald-50/40' : 'border-slate-200 bg-slate-50/40') }} space-y-2">
                                    <div class="flex items-center justify-between">
                                        <span class="text-xs font-bold text-slate-800">Ujian Teori CBT (IA.05)</span>
                                        @if($ringkasanBukti['ia05']['status'] === 'K')
                                            <span class="px-2 py-0.5 rounded text-[10px] font-bold bg-emerald-100 text-emerald-800 border border-emerald-300">Lulus</span>
                                        @elseif($ringkasanBukti['ia05']['status'] === 'BK')
                                            <span class="px-2 py-0.5 rounded text-[10px] font-bold bg-rose-100 text-rose-800 border border-rose-300">Tidak Lulus</span>
                                        @else
                                            <span class="px-2 py-0.5 rounded text-[10px] font-medium bg-slate-100 text-slate-600">Belum Ada</span>
                                        @endif
                                    </div>
                                    <div class="text-[11px] text-slate-600">
                                        Skor Asesi: <strong>{{ $ringkasanBukti['ia05']['skor'] !== null ? $ringkasanBukti['ia05']['skor'] . ' / 100' : '-' }}</strong>
                                    </div>
                                </div>
                            @endif

                            <!-- Card IA.06 Esai (Jika ada di skema) -->
                            @if($pendaftaran->skema->hasInstrumen('FR.IA.06') || $pendaftaran->skema->hasInstrumen('FR.IA.06B'))
                                <div class="p-4 rounded-xl border {{ $ringkasanBukti['ia06']['rekomendasi'] === 'BK' ? 'border-rose-200 bg-rose-50/40' : ($ringkasanBukti['ia06']['rekomendasi'] === 'K' ? 'border-emerald-200 bg-emerald-50/40' : 'border-slate-200 bg-slate-50/40') }} space-y-2">
                                    <div class="flex items-center justify-between">
                                        <span class="text-xs font-bold text-slate-800">Ujian Esai (IA.06)</span>
                                        <span class="px-2 py-0.5 rounded text-[10px] font-bold {{ $ringkasanBukti['ia06']['rekomendasi'] === 'BK' ? 'bg-rose-100 text-rose-800 border border-rose-300' : ($ringkasanBukti['ia06']['rekomendasi'] === 'K' ? 'bg-emerald-100 text-emerald-800 border border-emerald-300' : 'bg-slate-100 text-slate-600') }}">
                                            {{ $ringkasanBukti['ia06']['is_graded'] ? strtoupper($ringkasanBukti['ia06']['rekomendasi']) : 'Belum Dikoreksi' }}
                                        </span>
                                    </div>
                                    <div class="text-[11px] text-slate-600">
                                        Status Koreksi: <strong>{{ $ringkasanBukti['ia06']['is_graded'] ? 'Sudah Dikoreksi' : 'Menunggu' }}</strong>
                                    </div>
                                </div>
                            @endif

                            <!-- Card IA.04A Proyek (Jika ada di skema) -->
                            @if($pendaftaran->skema->hasInstrumen('FR.IA.04A'))
                                <div class="p-4 rounded-xl border {{ $ringkasanBukti['ia04a']['rekomendasi'] === 'BK' ? 'border-rose-200 bg-rose-50/40' : 'border-emerald-200 bg-emerald-50/40' }} space-y-2">
                                    <div class="flex items-center justify-between">
                                        <span class="text-xs font-bold text-slate-800">FR.IA.04A Proyek / TOR</span>
                                        <span class="px-2 py-0.5 rounded text-[10px] font-bold {{ $ringkasanBukti['ia04a']['rekomendasi'] === 'BK' ? 'bg-rose-100 text-rose-800 border border-rose-300' : 'bg-emerald-100 text-emerald-800 border border-emerald-300' }}">
                                            {{ $ringkasanBukti['ia04a']['rekomendasi'] === 'BK' ? 'BK' : 'K' }}
                                        </span>
                                    </div>
                                    @if(!empty($ringkasanBukti['ia04a']['catatan']))
                                        <p class="text-[11px] text-slate-500 line-clamp-2 italic">"{{ $ringkasanBukti['ia04a']['catatan'] }}"</p>
                                    @endif
                                </div>
                            @endif
                        </div>
                    </div>

                    <!-- Ringkasan Unit Kompetensi Skema Berdasarkan KUK -->
                    <div class="space-y-2">
                        <label class="block text-xs font-bold text-slate-800 uppercase tracking-wider">
                            Daftar Unit Kompetensi Teruji & Pencapaian KUK:
                        </label>
                        <div class="border border-slate-200 rounded-xl overflow-hidden text-xs">
                            <table class="w-full text-left">
                                <thead class="bg-slate-50 text-slate-600 font-bold border-b border-slate-200">
                                    <tr>
                                        <th class="p-3 w-12 text-center">No</th>
                                        <th class="p-3 w-40">Kode Unit</th>
                                        <th class="p-3">Judul Unit Kompetensi</th>
                                        <th class="p-3 text-center w-36">Status KUK</th>
                                    </tr>
                                </thead>
                                <tbody class="divide-y divide-slate-100 text-slate-700">
                                    @foreach($pendaftaran->skema->unitKompetensi as $idx => $u)
                                        @php
                                            $unitStatus = $statusPerUnit[$u->id] ?? 'belum_dinilai';
                                        @endphp
                                        <tr>
                                            <td class="p-3 text-center font-bold">{{ $idx + 1 }}</td>
                                            <td class="p-3 font-mono font-bold text-blue-700">{{ $u->kode_unit }}</td>
                                            <td class="p-3">{{ $u->judul_unit }}</td>
                                            <td class="p-3 text-center">
                                                @if($unitStatus === 'kompeten')
                                                    <span class="px-2.5 py-0.5 bg-emerald-50 text-emerald-700 font-bold rounded-md border border-emerald-200 inline-flex items-center gap-1">
                                                        <i class="fa-solid fa-check text-[10px]"></i> Kompeten
                                                    </span>
                                                @elseif($unitStatus === 'belum_kompeten')
                                                    <span class="px-2.5 py-0.5 bg-rose-50 text-rose-700 font-bold rounded-md border border-rose-200 inline-flex items-center gap-1">
                                                        <i class="fa-solid fa-xmark text-[10px]"></i> Belum Kompeten
                                                    </span>
                                                @else
                                                    <span class="px-2.5 py-0.5 bg-slate-100 text-slate-600 font-medium rounded-md border border-slate-200 inline-flex items-center gap-1">
                                                        Belum Dinilai
                                                    </span>
                                                @endif
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    </div>

                    <!-- Smart Recommendation Advisory Box -->
                    @if($saranRekomendasi === 'kompeten')
                        <div class="p-4 bg-emerald-50/80 border border-emerald-200 rounded-xl text-xs text-emerald-900 flex items-start gap-3">
                            <i class="fa-solid fa-circle-check text-emerald-600 mt-0.5 text-base flex-shrink-0"></i>
                            <div class="space-y-0.5">
                                <span class="font-bold block uppercase tracking-wider text-[11px] text-emerald-950">Rekomendasi Sistem: KOMPETEN (K)</span>
                                <p>Berdasarkan rekaman bukti formulir observasi dan instrumen yang telah diisi, asesi memenuhi seluruh kriteria unjuk kerja yang dipersyaratkan.</p>
                            </div>
                        </div>
                    @else
                        <div class="p-4 bg-amber-50/90 border border-amber-200 rounded-xl text-xs text-amber-900 flex items-start gap-3">
                            <i class="fa-solid fa-triangle-exclamation text-amber-600 mt-0.5 text-base flex-shrink-0"></i>
                            <div class="space-y-0.5">
                                <span class="font-bold block uppercase tracking-wider text-[11px] text-amber-950">Perhatian: Indikasi BELUM KOMPETEN (BK)</span>
                                <p>Terdapat kriteria unjuk kerja atau bukti instrumen yang dinilai Belum Kompeten (BK). Pastikan meninjau kembali seluruh lembar penilaian sebelum mengesahkan rekomendasi akhir.</p>
                            </div>
                        </div>
                    @endif

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
                    <div class="border-t border-slate-200/80 pt-6 space-y-4">
                        <div class="flex items-center justify-between">
                            <div>
                                <label class="text-xs font-bold text-slate-800 uppercase tracking-wider block">Pengesahan Tanda Tangan Asesor Penguji</label>
                                <span class="text-xs text-slate-500">Pilih metode tanda tangan untuk mengesahkan keputusan FR.AK.02.</span>
                            </div>
                        </div>

                        <!-- Mode Modal Trigger -->
                        <div class="space-y-2">
                            <div class="flex items-center justify-between">
                                <span class="text-xs text-slate-500 font-medium">Buka popup untuk menandatangani:</span>
                            </div>
                            
                            <div id="container-ttd-asesor-preview" class="p-3 border-2 border-dashed border-slate-300 rounded-xl bg-slate-50 flex flex-col items-center justify-center min-h-[112px]">
                                @php
                                    $existingSig = $pendaftaran->rekomendasi->tanda_tangan_asesor ?? ($pendaftaran->tanda_tangan_asesor ?? '');
                                    $hasSig = !empty($existingSig);
                                @endphp

                                @if($hasSig)
                                    <img src="{{ Str::startsWith($existingSig, 'data:image') ? $existingSig : asset($existingSig) }}" id="preview-ttd-asesor" alt="TTD Asesor" class="max-h-20 object-contain mb-2">
                                    <button type="button" onclick="bukaModalTtd()" id="btn-ubah-ttd" class="text-[10px] text-indigo-600 hover:underline mt-0.5 font-semibold">Ubah Tanda Tangan</button>
                                    <button type="button" id="btn-trigger-modal" onclick="bukaModalTtd()" class="hidden px-3 py-1.5 bg-indigo-50 text-indigo-700 border border-indigo-200 rounded-lg text-xs font-bold hover:bg-indigo-100 shadow-sm items-center gap-2 transition-colors">
                                        <i class="fa-solid fa-pen"></i> Gores Tanda Tangan
                                    </button>
                                @else
                                    <img src="" id="preview-ttd-asesor" alt="TTD Asesor" class="hidden max-h-20 object-contain mb-2">
                                    <button type="button" id="btn-trigger-modal" onclick="bukaModalTtd()" class="flex px-3 py-1.5 bg-indigo-50 text-indigo-700 border border-indigo-200 rounded-lg text-xs font-bold hover:bg-indigo-100 shadow-sm items-center gap-2 transition-colors">
                                        <i class="fa-solid fa-pen"></i> Gores Tanda Tangan
                                    </button>
                                    <button type="button" onclick="bukaModalTtd()" id="btn-ubah-ttd" class="hidden text-[10px] text-indigo-600 hover:underline mt-0.5 font-semibold">Ubah Tanda Tangan</button>
                                @endif
                            </div>
                            <input type="hidden" name="tanda_tangan_asesor" id="inputSignatureAsesor" value="{{ $existingSig }}">
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
    // Validasi form manual
    document.addEventListener('DOMContentLoaded', function() {
        const form = document.querySelector('form');
        
        form.addEventListener('submit', function(e) {
            // Hanya periksa tanda tangan jika yang diklik adalah pengesahan akhir (rekap_final)
            const submitBtn = e.submitter;
            if (submitBtn && submitBtn.value !== 'rekap_final') {
                return; // Penyimpanan draf atau formulir per tab tidak memblokir tanda tangan
            }

            const hiddenInput = document.getElementById('inputSignatureAsesor');
            if (!hiddenInput || !hiddenInput.value) {
                e.preventDefault();
                if (typeof Swal !== 'undefined') {
                    Swal.fire({
                        icon: 'warning',
                        title: 'Tanda Tangan Diperlukan',
                        text: 'Silakan gores tanda tangan Anda pada modal terlebih dahulu untuk mengesahkan keputusan ini.',
                        confirmButtonColor: '#4f46e5'
                    });
                } else {
                    alert('Silakan gores tanda tangan Anda pada modal terlebih dahulu untuk mengesahkan keputusan ini.');
                }
            }
        });
    });
</script>

<!-- MODAL POPUP CANVAS SIGNATURE PAD -->
<div class="modal-overlay" id="modalCanvasTtd" style="display:none; position: fixed; inset: 0; background: rgba(0,0,0,0.5); z-index: 99999; justify-content: center; align-items: center;">
    <div class="modal-konten" style="background: white; border-radius: 12px; padding: 1.75rem; width: 100%; max-width: 500px; text-align: center; box-shadow: 0 20px 25px -5px rgba(0, 0, 0, 0.1), 0 8px 10px -6px rgba(0, 0, 0, 0.1);">
        <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 1rem;">
            <h3 style="color: #0f172a; margin: 0; font-weight: 800; font-size: 1.15rem;">Tanda Tangan Digital</h3>
            <button type="button" onclick="document.getElementById('modalCanvasTtd').style.display = 'none'" style="background: none; border: none; font-size: 1.4rem; cursor: pointer; color: #64748b; line-height: 1;">&times;</button>
        </div>

        <p style="font-size: 0.85rem; color: #64748b; margin-bottom: 1rem; text-align: left;">
            Gunakan kursor atau layar sentuh untuk menggambar tanda tangan Anda secara presisi pada bidang di bawah:
        </p>

        <canvas id="canvas-ttd-modal" width="440" height="180" class="canvas-signature-pad" style="border: 2px dashed #94a3b8; border-radius: 8px; background: #ffffff; cursor: crosshair; touch-action: none; display: block; margin: 0 auto; width: 100%; max-width: 440px;"></canvas>

        <div style="margin-top: 1.25rem; display: flex; gap: 0.75rem; justify-content: center;">
            <button type="button" class="tombol tombol-sekunder tombol-sm px-4 py-2 bg-slate-100 text-slate-700 font-semibold rounded border border-slate-200 shadow-sm" onclick="if(window.signaturePadModal) window.signaturePadModal.clear();">
                Bersihkan
            </button>
            <button type="button" class="tombol tombol-utama tombol-sm px-4 py-2 text-white font-semibold rounded shadow-sm" style="background: #059669; border: 1px solid #059669;" onclick="window.saveTtdAsesor()">
                Gunakan Tanda Tangan
            </button>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/signature_pad@4.1.7/dist/signature_pad.umd.min.js"></script>
<script>
    window.signaturePadModal = null;

    window.bukaModalTtd = function() {
        const modal = document.getElementById('modalCanvasTtd');
        if (modal) {
            modal.style.display = 'flex';
            
            if (!window.signaturePadModal) {
                const canvas = document.getElementById('canvas-ttd-modal');
                if (canvas && window.SignaturePad) {
                    window.signaturePadModal = new SignaturePad(canvas, {
                        backgroundColor: 'rgb(255, 255, 255)'
                    });
                    
                    const ratio = Math.max(window.devicePixelRatio || 1, 1);
                    canvas.width = canvas.offsetWidth * ratio;
                    canvas.height = canvas.offsetHeight * ratio;
                    canvas.getContext("2d").scale(ratio, ratio);
                    window.signaturePadModal.clear();
                }
            } else {
                window.signaturePadModal.clear();
            }
        }
    };

    window.saveTtdAsesor = function() {
        if (window.signaturePadModal && !window.signaturePadModal.isEmpty()) {
            const dataUrl = window.signaturePadModal.toDataURL('image/png');
            
            // For penilaian-live.blade.php
            const hiddenInput = document.getElementById('inputSignatureAsesor');
            if(hiddenInput) hiddenInput.value = dataUrl;
            
            const previewImg = document.getElementById('preview-ttd-asesor');
            if(previewImg) {
                previewImg.src = dataUrl;
                previewImg.classList.remove('hidden');
            }
            
            const btnTrigger = document.getElementById('btn-trigger-modal');
            if(btnTrigger) {
                btnTrigger.classList.add('hidden');
                btnTrigger.classList.remove('flex');
            }
            
            const btnUbah = document.getElementById('btn-ubah-ttd');
            if(btnUbah) {
                btnUbah.classList.remove('hidden');
            }

            document.getElementById('modalCanvasTtd').style.display = 'none';
        } else {
            if (typeof Swal !== 'undefined') {
                Swal.fire({
                    icon: 'warning',
                    title: 'Canvas Kosong',
                    text: 'Silakan gambar tanda tangan Anda terlebih dahulu.',
                    confirmButtonText: 'Mengerti',
                    confirmButtonColor: '#059669'
                });
            } else {
                alert('Silakan gambar tanda tangan Anda terlebih dahulu.');
            }
        }
    };
</script>
@endpush
