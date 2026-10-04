@extends('tata-letak.dasbor')

@section('judul', 'Master FR.AK.07 - ' . ($skema->nama_skema ?? 'Skema'))

@push('css')
    <style>
        .custom-scrollbar::-webkit-scrollbar {
            width: 4px;
            height: 4px;
        }
        .custom-scrollbar::-webkit-scrollbar-track {
            background: #f8fafc;
        }
        .custom-scrollbar::-webkit-scrollbar-thumb {
            background: #cbd5e1;
            border-radius: 4px;
        }
    </style>
@endpush

@section('konten')
@php
    $currentSignature = $masterAk07->tanda_tangan_asesor;
    $isSigned = $masterAk07->status === 'selesai'
        && !empty($currentSignature)
        && !empty($masterAk07->tanggal_ttd_asesor);
    $isConfigured = $isSigned;
    $savedChecklist = (array) ($masterAk07->items_checklist ?? []);
    $selectedPotensi = $masterAk07->potensi_asesi !== null ? (int) $masterAk07->potensi_asesi : null;
    $selectedFase = $masterAk07->fase_penggunaan ?? null;
@endphp

<div class="max-w-5xl mx-auto px-2 sm:px-4 py-3 space-y-4" x-data="ak07SkemaApp()">

    <!-- BREADCRUMB & TOP ACTIONS -->
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-2 pb-1">
        <div class="flex items-center gap-2 text-xs text-slate-500">
            <a href="{{ route('asesor.dashboard') }}" class="hover:text-indigo-600 font-medium">Dashboard</a>
            <span>/</span>
            <a href="{{ route('asesor.mapa', ['skema_id' => $skema->id]) }}" class="hover:text-indigo-600 font-medium">Pusat Formulir</a>
            <span>/</span>
            <span class="text-slate-800 font-bold">Master FR.AK.07</span>
        </div>

        <div class="flex items-center gap-2">
            <a href="{{ route('asesor.mapa', ['skema_id' => $skema->id]) }}" 
               class="inline-flex items-center px-3 py-1.5 rounded-xl border border-slate-200 bg-white hover:bg-slate-50 text-slate-700 text-xs font-semibold shadow-2xs transition cursor-pointer">
                <span>&larr; Kembali ke Pusat Formulir</span>
            </a>
            @if($isConfigured)
                <button type="button" @click="toggleEditMode()" 
                        class="inline-flex items-center gap-1.5 px-3.5 py-1.5 rounded-xl border text-xs font-bold shadow-2xs transition cursor-pointer"
                        :class="isEditMode ? 'bg-amber-500 hover:bg-amber-600 text-white border-amber-600' : 'bg-indigo-600 hover:bg-indigo-700 text-white border-indigo-700'">
                    <span x-show="!isEditMode">✏️</span>
                    <span x-show="isEditMode">✕</span>
                    <span x-text="isEditMode ? 'Batal / Kunci Formulir' : 'Edit Formulir'"></span>
                </button>
            @endif
        </div>
    </div>

    <!-- HEADER CARD -->
    <div class="bg-white rounded-2xl border border-slate-200/90 shadow-2xs p-4 sm:p-5 space-y-3">
        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3 border-b border-slate-100 pb-3">
            <div class="flex items-center gap-3">
                <div class="w-11 h-11 rounded-xl bg-indigo-50 text-indigo-700 border border-indigo-100 flex items-center justify-center font-black text-sm shrink-0">
                    AK.07
                </div>
                <div>
                    <div class="flex items-center gap-2 flex-wrap">
                        <span class="px-2 py-0.5 rounded-md bg-indigo-100 text-indigo-800 text-[10px] font-extrabold uppercase tracking-wider">
                            MASTER TEMPLATE SKEMA
                        </span>
                        <span class="font-mono text-xs font-bold text-slate-500">{{ $skema->kode_skema }}</span>
                    </div>
                    <h1 class="text-base sm:text-lg font-bold text-slate-900 leading-tight mt-0.5">
                        FR.AK.07 &mdash; Ceklis Penyesuaian yang Wajar dan Beralasan
                    </h1>
                    <p class="text-xs text-slate-500">
                        Penetapan penyesuaian asesmen, acuan pembanding, dan metode pendukung untuk seluruh asesi pada skema sertifikasi ini.
                    </p>
                </div>
            </div>

            <!-- Status Pill Header -->
            <div class="flex items-center gap-2">
                @if($isConfigured)
                    <template x-if="!isEditMode">
                        <span class="px-3 py-1 rounded-full bg-emerald-50 text-emerald-700 border border-emerald-200 text-xs font-bold flex items-center gap-1.5 shadow-2xs">
                            <span class="w-2 h-2 rounded-full bg-emerald-500"></span>
                            Ditetapkan & Terkunci
                        </span>
                    </template>
                    <template x-if="isEditMode">
                        <span class="px-3 py-1 rounded-full bg-amber-50 text-amber-700 border border-amber-200 text-xs font-bold flex items-center gap-1.5 shadow-2xs">
                            <span class="w-2 h-2 rounded-full bg-amber-500 animate-pulse"></span>
                            Mode Edit Aktif
                        </span>
                    </template>
                @else
                    <span class="px-3 py-1 rounded-full bg-blue-50 text-blue-700 border border-blue-200 text-xs font-bold flex items-center gap-1.5">
                        <span class="w-2 h-2 rounded-full bg-blue-500 animate-pulse"></span>
                        Draf Baru (Belum Disimpan)
                    </span>
                @endif
            </div>
        </div>

        <!-- INFO BANNER SINKRONISASI OTOMATIS -->
        <div class="bg-slate-50 border border-indigo-100 rounded-xl p-3.5 flex items-start gap-3 text-xs text-slate-700 leading-relaxed">
            <div class="space-y-0.5">
                <strong class="text-slate-900 block font-bold">Otomatis Berlaku dan Ditampilkan ke Seluruh Asesi:</strong>
                <span>
                    Format checklist penyesuaian, acuan pembanding, dan metode yang disimpan pada formulir ini akan <strong>otomatis diselaraskan dan ditampilkan</strong> ke seluruh asesi yang mendaftar di skema <strong>{{ $skema->nama_skema }}</strong>.
                </span>
            </div>
        </div>
    </div>

    <!-- BANNER MODE TAMPILAN (LOCKED) / EDIT -->
    @if($isConfigured)
        <div x-show="!isEditMode" class="bg-indigo-50/70 border border-indigo-200 rounded-2xl p-4 flex flex-col sm:flex-row sm:items-center justify-between gap-3 text-xs text-indigo-950 shadow-2xs">
            <div class="flex items-start sm:items-center gap-3">
                <div class="w-9 h-9 rounded-xl bg-indigo-600 text-white flex items-center justify-center font-bold text-sm shrink-0 shadow-2xs">
                </div>
                <div>
                    <strong class="text-indigo-950 font-bold block text-sm">Formulir Terkunci (Mode Hanya Lihat)</strong>
                    <span class="text-indigo-800">
                        Data formulir Master FR.AK.07 telah tersimpan dan disahkan. Formulir dalam keadaan <strong>terkunci dan tidak dapat diubah</strong> untuk mencegah perubahan tidak disengaja. Klik tombol <strong>Edit Formulir</strong> untuk membuka kunci.
                    </span>
                </div>
            </div>
            <button type="button" @click="toggleEditMode()" class="shrink-0 px-3.5 py-2 bg-indigo-600 hover:bg-indigo-700 text-white rounded-xl font-bold text-xs shadow-2xs transition flex items-center gap-1.5 cursor-pointer">
                <span>Buka Kunci / Edit</span>
            </button>
        </div>

        <div x-show="isEditMode" class="bg-amber-50 border border-amber-300 rounded-2xl p-4 flex flex-col sm:flex-row sm:items-center justify-between gap-3 text-xs text-amber-950 shadow-2xs">
            <div class="flex items-start sm:items-center gap-3">
                <div class="w-9 h-9 rounded-xl bg-amber-500 text-white flex items-center justify-center font-bold text-sm shrink-0 shadow-2xs">
                </div>
                <div>
                    <strong class="text-amber-950 font-bold block text-sm">Mode Pengubahan Terbuka (Edit Mode)</strong>
                    <span class="text-amber-800">
                        Anda sekarang dapat mengubah potensi asesi, matriks penyesuaian, dan rekomendasi kesepakatan. Klik tombol <strong>Simpan Perubahan</strong> di bagian bawah untuk menyimpan dan mengunci kembali formulir.
                    </span>
                </div>
            </div>
            <button type="button" @click="toggleEditMode()" class="shrink-0 px-3.5 py-2 bg-white hover:bg-slate-100 text-slate-700 border border-slate-300 rounded-xl font-bold text-xs transition cursor-pointer">
                <span>Batal / Kunci Kembali</span>
            </button>
        </div>
    @endif

    <!-- MAIN FORM -->
    <form id="formMasterAk07" action="{{ route('asesor.skema.ak-07.simpan', $skema->id) }}" method="POST" @submit.prevent="if (!isEditMode) return false; submitAk07($event)" class="space-y-4">
        @csrf

        <fieldset :disabled="!isEditMode" :class="!isEditMode ? 'pointer-events-none opacity-90' : ''" class="space-y-4 border-0 p-0 m-0 transition-opacity">

        <!-- 1. POTENSI ASESI -->
        <div class="bg-white rounded-2xl border border-slate-200/90 shadow-2xs p-4 sm:p-5 space-y-3">
            <div class="flex items-center justify-between border-b border-slate-100 pb-2.5">
                <div class="flex items-center gap-2 text-sm font-bold text-slate-900">
                    <span class="w-6 h-6 rounded-lg bg-indigo-50 text-indigo-700 flex items-center justify-center text-xs font-black">1</span>
                    <span>Potensi Asesi Standar (Kategori Kandidat MAPA.01)</span>
                </div>
                <template x-if="!isEditMode">
                    <span class="text-[11px] text-slate-400 font-semibold flex items-center gap-1">
                        <span>🔒</span> Terkunci
                    </span>
                </template>
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-2 pt-1">
                @foreach($potensiDefinitions as $pVal => $pLabel)
                    <label class="relative flex items-start gap-2.5 p-3 rounded-xl border transition-all text-xs"
                           :class="[
                               selectedPotensi === {{ $pVal }} ? 'border-indigo-500 bg-indigo-50/50 text-slate-900 font-semibold shadow-2xs' : 'border-slate-200 bg-white text-slate-600',
                               isEditMode ? 'cursor-pointer hover:bg-slate-50/80' : 'cursor-not-allowed'
                           ]">
                        <input type="radio" 
                               name="potensi_asesi" 
                               value="{{ $pVal }}" 
                               @click="isEditMode && (selectedPotensi = (selectedPotensi === {{ $pVal }} ? null : {{ $pVal }}))"
                               :checked="selectedPotensi === {{ $pVal }}" 
                               :disabled="!isEditMode" 
                               class="mt-0.5 accent-indigo-600">
                        <div class="space-y-0.5 leading-snug">
                            <span class="inline-block px-1.5 py-0.2 rounded bg-slate-200/80 text-[10px] font-bold text-slate-700">Kategori {{ $pVal }}</span>
                            <div class="text-[11px]">{{ $pLabel }}</div>
                        </div>
                    </label>
                @endforeach
            </div>
        </div>

        <!-- 2. FASE PENGGUNAAN -->
        <div class="bg-white rounded-2xl border border-slate-200/90 shadow-2xs p-4 sm:p-5 space-y-3">
            <div class="flex items-center justify-between border-b border-slate-100 pb-2.5">
                <div class="flex items-center gap-2 text-sm font-bold text-slate-900">
                    <span class="w-6 h-6 rounded-lg bg-indigo-50 text-indigo-700 flex items-center justify-center text-xs font-black">2</span>
                    <span>Fase Pelaksanaan Penyesuaian</span>
                </div>
                <template x-if="!isEditMode">
                    <span class="text-[11px] text-slate-400 font-semibold flex items-center gap-1">
                        <span>🔒</span> Terkunci
                    </span>
                </template>
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-3 gap-2 pt-1 text-xs">
                @php
                    $faseList = [
                        'pra_asesmen' => ['title' => 'Pra Asesmen', 'desc' => 'Diterapkan sebelum tahapan asesmen dimulai (verifikasi & konsultasi)'],
                        'saat_pra_asesmen' => ['title' => 'Pada Saat Asesmen', 'desc' => 'Diterapkan secara langsung selama sesi ujian/praktik'],
                        'setelah_pra_asesmen' => ['title' => 'Setelah Asesmen', 'desc' => 'Diterapkan pada tahap umpan balik & pengumpulan bukti tambahan'],
                    ];
                @endphp
                @foreach($faseList as $fKey => $fInfo)
                    <label class="relative flex items-start gap-2.5 p-3 rounded-xl border transition-all text-xs"
                           :class="[
                               selectedFase === '{{ $fKey }}' ? 'border-indigo-500 bg-indigo-50/50 text-slate-900 font-semibold shadow-2xs' : 'border-slate-200 bg-white text-slate-600',
                               isEditMode ? 'cursor-pointer hover:bg-slate-50/80' : 'cursor-not-allowed'
                           ]">
                        <input type="radio" name="fase_penggunaan" value="{{ $fKey }}"
                               @click="isEditMode && (selectedFase = (selectedFase === '{{ $fKey }}' ? null : '{{ $fKey }}'))"
                               :checked="selectedFase === '{{ $fKey }}'" :disabled="!isEditMode" class="mt-0.5 accent-indigo-600">
                        <div class="space-y-0.5">
                            <div class="font-bold text-slate-800">{{ $fInfo['title'] }}</div>
                            <div class="text-[11px] text-slate-500 leading-tight">{{ $fInfo['desc'] }}</div>
                        </div>
                    </label>
                @endforeach
            </div>
        </div>

        <!-- 3. MATRIKS 8 KATEGORI STANDAR BNSP -->
        <div class="bg-white rounded-2xl border border-slate-200/90 shadow-2xs p-4 sm:p-5 space-y-4">
            <div class="flex items-center justify-between border-b border-slate-100 pb-2.5">
                <h2 class="text-sm font-bold text-slate-900">3. Matriks Kebutuhan Penyesuaian yang Wajar</h2>
                <div class="flex items-center gap-1.5">
                    <button type="button" @click="setSemuaTidakPerlu()" class="px-2.5 py-1 rounded-lg bg-slate-100 text-slate-700 text-[11px] font-semibold">Set Semua Tidak Perlu</button>
                    <button type="button" @click="kosongkanSemuaChecklist()" class="px-2.5 py-1 rounded-lg bg-slate-100 text-slate-500 text-[11px] font-semibold">Kosongkan Semua</button>
                </div>
            </div>

            <div class="space-y-3">
                @foreach($criteriaDefinitions as $cId => $crit)
                    @php
                        $itemSaved = $savedChecklist[$cId] ?? [];
                        $opsiSaved = (array) ($itemSaved['opsi_dipilih'] ?? []);
                        $ketSaved = $itemSaved['keterangan'] ?? '';
                        $itemState = $itemSaved['perlu_penyesuaian'] ?? null;
                    @endphp
                    <div class="border border-slate-200 rounded-xl p-3.5 space-y-3" data-ak07-checklist-category="{{ $cId }}">
                        <h3 class="text-xs font-bold text-slate-800">{{ $cId }}. {{ $crit['title'] }}</h3>
                        <div class="flex flex-wrap gap-3 text-xs">
                            <label class="flex items-center gap-1.5"><input type="radio" data-ak07-category="{{ $cId }}" name="items_checklist[{{ $cId }}][perlu]" value="0" {{ $itemState === false || $itemState === 0 ? 'checked' : '' }} @change="checklist['{{ $cId }}'] = 'tidak'"> Tidak Perlu</label>
                            <label class="flex items-center gap-1.5"><input type="radio" data-ak07-category="{{ $cId }}" name="items_checklist[{{ $cId }}][perlu]" value="1" {{ $itemState === true || $itemState === 1 ? 'checked' : '' }} @change="checklist['{{ $cId }}'] = 'perlu'"> Perlu Penyesuaian</label>
                        </div>
                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-2">
                            @foreach($crit['sub_options'] as $subKey => $subLabel)
                                <label class="flex items-start gap-2 p-2 rounded-lg bg-white border border-slate-200 text-[11px]">
                                    <input type="checkbox" data-ak07-category="{{ $cId }}" name="items_checklist[{{ $cId }}][opsi][]" value="{{ $subKey }}" {{ in_array($subKey, $opsiSaved) ? 'checked' : '' }}>
                                    <span>{{ $subLabel }}</span>
                                </label>
                            @endforeach
                        </div>
                        <input type="text" data-ak07-category="{{ $cId }}" name="items_checklist[{{ $cId }}][keterangan]" value="{{ $ketSaved }}" class="w-full px-3 py-1.5 rounded-lg border border-slate-200 text-xs" placeholder="Catatan khusus penyesuaian">
                    </div>
                @endforeach
            </div>
        </div>

        <!-- 4. REKOMENDASI KESEPAKATAN ASESMEN -->
        <div class="bg-white rounded-2xl border border-slate-200/90 shadow-2xs p-4 sm:p-5 space-y-3">
            <div class="flex items-center justify-between border-b border-slate-100 pb-2.5">
                <div class="flex items-center gap-2 text-sm font-bold text-slate-900">
                    <span class="w-6 h-6 rounded-lg bg-indigo-50 text-indigo-700 flex items-center justify-center text-xs font-black">4</span>
                    <span>Rekomendasi Hasil Kesepakatan Penyesuaian</span>
                </div>
                <template x-if="!isEditMode">
                    <span class="text-[11px] text-slate-400 font-semibold flex items-center gap-1">
                        <span>🔒</span> Terkunci
                    </span>
                </template>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-3 gap-3 text-xs">
                <div class="space-y-1">
                    <label class="font-bold text-slate-700 block">
                        Acuan Pembanding Disepakati:
                    </label>
                    <textarea name="acuan_pembanding_disepakati" rows="3" 
                              :readonly="!isEditMode" :disabled="!isEditMode"
                              :class="!isEditMode ? 'bg-slate-50 text-slate-700 cursor-not-allowed border-slate-200' : 'bg-white text-slate-900 border-slate-200 focus:ring-2 focus:ring-indigo-500'"
                              class="w-full p-2.5 rounded-xl border text-xs" placeholder="Contoh: Standar Kompetensi Kerja Nasional Indonesia (SKKNI)...">{{ old('acuan_pembanding_disepakati', $masterAk07->acuan_pembanding_disepakati) }}</textarea>
                </div>

                <div class="space-y-1">
                    <label class="font-bold text-slate-700 block">
                        Metode Asesmen Disepakati:
                    </label>
                    <textarea name="metode_disepakati" rows="3" 
                              :readonly="!isEditMode" :disabled="!isEditMode"
                              :class="!isEditMode ? 'bg-slate-50 text-slate-700 cursor-not-allowed border-slate-200' : 'bg-white text-slate-900 border-slate-200 focus:ring-2 focus:ring-indigo-500'"
                              class="w-full p-2.5 rounded-xl border text-xs" placeholder="Contoh: Observasi Demonstrasi Langsung & Wawancara Terstruktur...">{{ old('metode_disepakati', $masterAk07->metode_disepakati) }}</textarea>
                </div>

                <div class="space-y-1">
                    <label class="font-bold text-slate-700 block">
                        Instrumen Pendukung:
                    </label>
                    <textarea name="instrumen_disepakati" rows="3" 
                              :readonly="!isEditMode" :disabled="!isEditMode"
                              :class="!isEditMode ? 'bg-slate-50 text-slate-700 cursor-not-allowed border-slate-200' : 'bg-white text-slate-900 border-slate-200 focus:ring-2 focus:ring-indigo-500'"
                              class="w-full p-2.5 rounded-xl border text-xs" placeholder="Contoh: FR.IA.01 (Observasi Praktik), FR.IA.03 (Pertanyaan Pendukung Observasi)...">{{ old('instrumen_disepakati', $masterAk07->instrumen_disepakati) }}</textarea>
                </div>
            </div>

            <div class="space-y-1 pt-1 text-xs">
                <label class="font-bold text-slate-700 block">
                    Catatan Tambahan Asesor:
                </label>
                <textarea name="catatan_asesor" rows="2" 
                          :readonly="!isEditMode" :disabled="!isEditMode"
                          :class="!isEditMode ? 'bg-slate-50 text-slate-700 cursor-not-allowed border-slate-200' : 'bg-white text-slate-900 border-slate-200 focus:ring-2 focus:ring-indigo-500'"
                          class="w-full p-2.5 rounded-xl border text-xs" placeholder="Catatan atau instruksi khusus pelaksanaan penyesuaian asesmen...">{{ old('catatan_asesor', $masterAk07->catatan_asesor) }}</textarea>
            </div>
        </div>

        <!-- 5. TANDA TANGAN ASESOR -->
        <div class="bg-white rounded-2xl border border-slate-200/90 shadow-2xs p-4 sm:p-5 space-y-4">
            <div class="flex items-center justify-between border-b border-slate-100 pb-2.5">
                <div class="flex items-center gap-2 text-sm font-bold text-slate-900">
                    <span class="w-6 h-6 rounded-lg bg-indigo-50 text-indigo-700 flex items-center justify-center text-xs font-black">5</span>
                    <span>Tanda Tangan Digital Asesor</span>
                </div>
                <template x-if="!isEditMode">
                    <span class="text-[11px] text-slate-400 font-semibold flex items-center gap-1">
                        <span>🔒</span> Terkunci
                    </span>
                </template>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-4 text-xs">
                <!-- SISI KIRI: TANDA TANGAN ASESOR -->
                <div class="border border-slate-200 rounded-xl p-3.5 space-y-3 bg-slate-50/50">
                    <div class="flex items-center justify-between border-b border-slate-200 pb-2">
                        <div>
                            <span class="font-bold text-slate-800 block">{{ auth()->user()?->nama_lengkap ?? 'Asesor' }}</span>
                            <span class="text-[11px] text-slate-500">No. MET: {{ auth()->user()?->nomor_registrasi ?? 'MET.000.004455' }}</span>
                        </div>
                        @if($isSigned)
                            <span class="px-2 py-0.5 rounded-full bg-emerald-50 text-emerald-700 border border-emerald-200 text-[10px] font-bold">
                                ✓ Tertera TTD
                            </span>
                        @else
                            <span class="px-2 py-0.5 rounded-full bg-amber-50 text-amber-700 border border-amber-200 text-[10px] font-bold">
                                Belum TTD
                            </span>
                        @endif
                    </div>

                    <!-- PREVIEW SAAT MODE TERKUNCI (VIEW ONLY) -->
                    <div x-show="!isEditMode" class="space-y-2">
                        <div class="p-3 bg-white rounded-xl border border-slate-200 flex flex-col items-center justify-center min-h-28">
                            @if(!empty($currentSignature))
                                <img src="{{ asset($currentSignature) }}" alt="Tanda Tangan Asesor" class="max-h-24 object-contain">
                                @if(!empty($masterAk07->tanggal_ttd_asesor))
                                    <span class="text-[10px] text-slate-400 mt-2 font-mono">
                                        Disahkan pada: {{ \Carbon\Carbon::parse($masterAk07->tanggal_ttd_asesor)->translatedFormat('d F Y, H:i') }} WIB
                                    </span>
                                @endif
                            @else
                                <span class="text-slate-400 italic text-[11px]">Belum ada tanda tangan yang tersimpan. Klik Edit Formulir untuk membubuhkan tanda tangan.</span>
                            @endif
                        </div>
                    </div>

                    <!-- KONTROL TTD SAAT MODE EDIT AKTIF -->
                    <div x-show="isEditMode" class="space-y-2">
                        <div class="space-y-1.5">
                            <div id="container-ttd-asesor-preview" class="p-3 border-2 border-dashed border-slate-300 rounded-xl bg-slate-50 flex flex-col items-center justify-center min-h-[112px]">
                                <img src="" id="preview-ttd-asesor" alt="TTD Asesor" class="hidden max-h-20 object-contain mb-2">
                                <button type="button" id="btn-trigger-modal" onclick="bukaModalTtd()" class="px-3 py-1.5 bg-indigo-50 text-indigo-700 border border-indigo-200 rounded-lg text-xs font-bold hover:bg-indigo-100 shadow-sm flex items-center gap-2 transition-colors">
                                    <i class="fa-solid fa-pen"></i> Gores Tanda Tangan
                                </button>
                                <button type="button" onclick="bukaModalTtd()" id="btn-ubah-ttd" class="hidden text-[10px] text-indigo-600 hover:underline mt-0.5 font-semibold">Ubah Tanda Tangan</button>
                                <span class="text-[10px] text-slate-500 mt-1">Buat tanda tangan baru setiap kali menyimpan formulir.</span>
                            </div>
                        </div>

                        <input type="hidden" name="tanda_tangan_asesor" id="inputSignatureMasterAk07" x-ref="signatureInput">
                    </div>
                </div>

                <!-- SISI KANAN: INFORMASI KESEPAKATAN -->
                <div class="border border-slate-200 rounded-xl p-3.5 space-y-2 bg-slate-50/50 flex flex-col justify-between">
                    <div>
                        <span class="font-bold text-slate-800 block mb-1">Informasi Kesepakatan Asesi:</span>
                        <p class="text-slate-500 leading-relaxed text-[11px]">
                            Setelah Master FR.AK.07 ini Anda simpan, seluruh formulir asesi di skema ini akan otomatis memuat isi penyesuaian di atas beserta tanda tangan Anda.
                        </p>
                        <p class="text-slate-500 leading-relaxed text-[11px] mt-2">
                            Asesi akan melihat formulir lengkap ini saat membuka menu <strong>FR.AK.07</strong> di portal asesi dan dapat langsung membubuhkan tanda tangan persetujuan.
                        </p>
                    </div>

                    <div class="p-3 bg-emerald-50/80 border border-emerald-200 rounded-xl flex items-center gap-2 text-emerald-800 text-xs font-semibold">
                        <span>✓ Seluruh asesi langsung tersinkronisasi otomatis</span>
                    </div>
                </div>
            </div>
        </div>
        </fieldset>

        <!-- SUBMIT BAR / ACTION FOOTER -->
        @if($isConfigured)
            <!-- FOOTER SAAT MODE TERKUNCI -->
            <div x-show="!isEditMode" class="bg-white rounded-2xl border border-slate-200/90 p-4 shadow-2xs flex flex-col sm:flex-row items-center justify-between gap-3">
                <div class="flex items-center gap-2 text-xs text-slate-500">
                    <span class="w-2 h-2 rounded-full bg-emerald-500"></span>
                    <span>Formulir dalam mode terkunci (hanya lihat). Klik <strong>Edit Formulir</strong> untuk mengubah data.</span>
                </div>

                <div class="flex items-center gap-2">
                    <a href="{{ route('asesor.mapa', ['skema_id' => $skema->id]) }}" class="px-4 py-2 rounded-xl border border-slate-200 text-xs font-bold text-slate-600 hover:bg-slate-50 transition cursor-pointer">
                        &larr; Kembali ke Pusat Formulir
                    </a>
                    <button type="button" @click="toggleEditMode()" class="px-5 py-2 rounded-xl bg-indigo-600 hover:bg-indigo-700 text-white text-xs font-bold shadow-2xs transition cursor-pointer flex items-center gap-1.5">
                        <span>Edit Formulir</span>
                    </button>
                </div>
            </div>

            <!-- FOOTER SAAT MODE EDIT AKTIF -->
            <div x-show="isEditMode" class="bg-white rounded-2xl border border-amber-200 p-4 shadow-2xs flex flex-col sm:flex-row items-center justify-between gap-3">
                <div class="text-xs text-amber-800">
                    Pastikan seluruh data penyesuaian telah sesuai. Klik simpan untuk mengesahkan dan mengunci kembali formulir.
                </div>

                <div class="flex items-center gap-2">
                    <button type="button" @click="toggleEditMode()" class="px-4 py-2.5 rounded-xl border border-slate-200 text-xs font-bold text-slate-600 hover:bg-slate-50 transition cursor-pointer">
                        Batal
                    </button>
                    <button type="submit" :disabled="isSubmitting" class="px-6 py-2.5 rounded-xl bg-indigo-600 hover:bg-indigo-700 text-white text-xs font-bold shadow-xs transition flex items-center gap-2 cursor-pointer">
                        <span x-text="isSubmitting ? 'Menyimpan...' : 'Simpan & Sahkan Master FR.AK.07'"></span>
                    </button>
                </div>
            </div>
        @else
            <!-- FOOTER UNTUK DRAF AWAL -->
            <div class="bg-white rounded-2xl border border-slate-200/90 p-4 shadow-2xs flex flex-col sm:flex-row items-center justify-between gap-3">
                <div class="text-xs text-slate-500">
                    Klik simpan untuk menetapkan dan menerapkan dokumen ini ke seluruh asesi skema <strong>{{ $skema->nama_skema }}</strong>.
                </div>

                <div class="flex items-center gap-2">
                    <a href="{{ route('asesor.mapa', ['skema_id' => $skema->id]) }}" class="px-4 py-2.5 rounded-xl border border-slate-200 text-xs font-bold text-slate-600 hover:bg-slate-50 transition cursor-pointer">
                        Batal
                    </a>
                    <button type="submit" :disabled="isSubmitting" class="px-6 py-2.5 rounded-xl bg-indigo-600 hover:bg-indigo-700 text-white text-xs font-bold shadow-xs transition flex items-center gap-2 cursor-pointer">
                        <span x-text="isSubmitting ? 'Menyimpan...' : 'Simpan & Sahkan Master FR.AK.07'"></span>
                    </button>
                </div>
            </div>
        @endif
    </form>

</div>

@push('js')
<script>
function ak07SkemaApp() {
    return {
        isEditMode: {{ $isConfigured ? 'false' : 'true' }},
        isSubmitting: false,
        selectedPotensi: {!! json_encode($selectedPotensi) !!},
        selectedFase: {!! json_encode($selectedFase) !!},
        checklist: {
            @foreach($criteriaDefinitions as $cId => $crit)
                @php
                    $itemSaved = $savedChecklist[$cId] ?? null;
                    $itemState = null;
                    if ($itemSaved !== null && array_key_exists('perlu_penyesuaian', $itemSaved) && $itemSaved['perlu_penyesuaian'] !== null) {
                        $itemState = filter_var($itemSaved['perlu_penyesuaian'], FILTER_VALIDATE_BOOLEAN) ? 'perlu' : 'tidak';
                    }
                @endphp
                '{{ $cId }}': {!! json_encode($itemState) !!},
            @endforeach
        },

        setSemuaTidakPerlu() {
            if (!this.isEditMode) return;
            for (const cId of Object.keys(this.checklist)) {
                this.checklist[cId] = 'tidak';
                const category = this.$root.querySelector(`[data-ak07-checklist-category="${cId}"]`);
                if (!category) continue;

                category.querySelector('input[type="radio"][value="0"]').checked = true;
                category.querySelector('input[type="radio"][value="1"]').checked = false;
                category.querySelectorAll('input[type="checkbox"]').forEach((input) => {
                    input.checked = false;
                });
                const note = category.querySelector('input[name$="[keterangan]"]');
                if (note) note.value = '';
            }
        },

        kosongkanSemuaChecklist() {
            if (!this.isEditMode) return;
            for (const cId of Object.keys(this.checklist)) {
                this.checklist[cId] = null;
                const category = this.$root.querySelector(`[data-ak07-checklist-category="${cId}"]`);
                if (!category) continue;

                category.querySelectorAll('input[type="radio"], input[type="checkbox"]').forEach((input) => {
                    input.checked = false;
                });
                const note = category.querySelector('input[name$="[keterangan]"]');
                if (note) note.value = '';
            }
        },

        toggleEditMode() {
            this.isEditMode = !this.isEditMode;
        },

        submitAk07(e) {
            if (!this.isEditMode) {
                return false;
            }
            if (this.isSubmitting) return;

            const signature = document.getElementById('inputSignatureMasterAk07')?.value || '';
            if (!/^data:image\/(png|jpeg);base64,/.test(signature)) {
                alert('Silakan bubuhkan tanda tangan Asesor terlebih dahulu sebelum menyimpan FR.AK.07.');
                return false;
            }

            this.isSubmitting = true;
            e.target.submit();
        }
    };
}
</script>

<!-- MODAL POPUP CANVAS SIGNATURE PAD -->
<div class="modal-overlay" id="modalCanvasTtd" style="display:none; position: fixed; inset: 0; background: rgba(0,0,0,0.5); z-index: 99999; justify-content: center; align-items: center;">
    <div class="modal-konten" style="background: white; border-radius: 12px; padding: 1.75rem; width: 100%; max-width: 500px; text-align: center; box-shadow: 0 20px 25px -5px rgba(0, 0, 0, 0.1), 0 8px 10px -6px rgba(0, 0, 0, 0.1);">
        <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 1rem;">
            <h3 style="color: #0f172a; margin: 0; font-weight: 800; font-size: 1.15rem;">Tanda Tangan Digital</h3>
            <button type="button" onclick="window.tutupModalTtd()" style="background: none; border: none; font-size: 1.4rem; cursor: pointer; color: #64748b; line-height: 1;">&times;</button>
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
    let fallbackSignatureDrawn = false;

    window.tutupModalTtd = function() {
        const modal = document.getElementById('modalCanvasTtd');
        if (!modal) return;

        modal.classList.remove('terbuka');
        modal.style.display = 'none';
        document.body.classList.remove('modal-terbuka');
    };

    window.bukaModalTtd = function() {
        const modal = document.getElementById('modalCanvasTtd');
        if (modal) {
            modal.style.display = 'flex';
            modal.classList.add('terbuka');
            document.body.classList.add('modal-terbuka');
            const input = document.getElementById('inputSignatureMasterAk07');
            if (input) input.value = '';
            document.getElementById('preview-ttd-asesor')?.classList.add('hidden');
            document.getElementById('btn-trigger-modal')?.classList.remove('hidden');
            document.getElementById('btn-ubah-ttd')?.classList.add('hidden');

            requestAnimationFrame(() => {
                const canvas = document.getElementById('canvas-ttd-modal');
                if (!canvas) return;

                const ratio = Math.max(window.devicePixelRatio || 1, 1);
                const width = canvas.clientWidth;
                const height = canvas.clientHeight;
                if (!width || !height) return;

                canvas.width = Math.round(width * ratio);
                canvas.height = Math.round(height * ratio);
                const context = canvas.getContext('2d');
                context.setTransform(ratio, 0, 0, ratio, 0, 0);
                fallbackSignatureDrawn = false;

                if (window.signaturePadModal) {
                    window.signaturePadModal.clear();
                } else if (typeof SignaturePad !== 'undefined') {
                    window.signaturePadModal = new SignaturePad(canvas, {
                        backgroundColor: 'rgb(255, 255, 255)',
                        penColor: '#0f172a',
                        minWidth: 1.2,
                        maxWidth: 2.8
                    });
                } else {
                    window.signaturePadModal = null;
                    context.lineWidth = 2.5;
                    context.lineCap = 'round';
                    context.lineJoin = 'round';
                    context.strokeStyle = '#0f172a';
                    if (!canvas.dataset.pointerHandlers) {
                        let drawing = false;
                        canvas.addEventListener('pointerdown', (event) => {
                            event.preventDefault();
                            drawing = true;
                            fallbackSignatureDrawn = true;
                            canvas.setPointerCapture(event.pointerId);
                            const rect = canvas.getBoundingClientRect();
                            const ctx = canvas.getContext('2d');
                            ctx.beginPath();
                            ctx.moveTo(event.clientX - rect.left, event.clientY - rect.top);
                        });
                        canvas.addEventListener('pointermove', (event) => {
                            if (!drawing) return;
                            event.preventDefault();
                            const rect = canvas.getBoundingClientRect();
                            const ctx = canvas.getContext('2d');
                            ctx.lineTo(event.clientX - rect.left, event.clientY - rect.top);
                            ctx.stroke();
                        });
                        const stopDrawing = () => { drawing = false; };
                        canvas.addEventListener('pointerup', stopDrawing);
                        canvas.addEventListener('pointercancel', stopDrawing);
                        canvas.dataset.pointerHandlers = 'true';
                    }
                }
            });
        }
    };

    window.saveTtdAsesor = function() {
        const hasSignature = window.signaturePadModal
            ? !window.signaturePadModal.isEmpty()
            : fallbackSignatureDrawn;
        if (hasSignature) {
            const canvas = document.getElementById('canvas-ttd-modal');
            const dataUrl = window.signaturePadModal
                ? window.signaturePadModal.toDataURL('image/png')
                : canvas.toDataURL('image/png');
            
            const hiddenInput = document.getElementById('inputSignatureMasterAk07');
            if(hiddenInput) hiddenInput.value = dataUrl;
            
            const previewImg = document.getElementById('preview-ttd-asesor');
            if(previewImg) {
                previewImg.src = dataUrl;
                previewImg.classList.remove('hidden');
            }
            
            const btnTrigger = document.getElementById('btn-trigger-modal');
            if(btnTrigger) {
                btnTrigger.classList.add('hidden');
            }
            
            document.getElementById('btn-ubah-ttd')?.classList.remove('hidden');

            window.tutupModalTtd();
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
@endsection
