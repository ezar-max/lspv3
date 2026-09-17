@extends('tata-letak.dasbor')

@section('judul', 'Master FR.AK.07 - ' . ($skema->nama_skema ?? 'Skema'))

@push('css')
    <!-- Signature Pad CDN -->
    <script src="https://cdn.jsdelivr.net/npm/signature_pad@4.1.7/dist/signature_pad.umd.min.js"></script>
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
    $userSignature = auth()->user()->tanda_tangan;
    $currentSignature = $masterAk07->tanda_tangan_asesor ?? $userSignature;
    $isSigned = !empty($currentSignature);
    $isConfigured = $masterAk07->exists && ($masterAk07->status === 'selesai' || !empty($masterAk07->updated_at));
    $savedChecklist = (array) ($masterAk07->items_checklist ?? []);
    $selectedPotensi = (int) ($masterAk07->potensi_asesi ?? 1);
    $selectedFase = $masterAk07->fase_penggunaan ?? 'saat_pra_asesmen';
@endphp

<div class="max-w-5xl mx-auto px-2 sm:px-4 py-3 space-y-4" x-data="ak07SkemaApp()">

    <!-- BREADCRUMB -->
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-2 pb-1">
        <div class="flex items-center gap-2 text-xs text-slate-500">
            <a href="{{ route('asesor.dashboard') }}" class="hover:text-indigo-600 font-medium">Dashboard</a>
            <span>/</span>
            <a href="{{ route('asesor.mapa', ['skema_id' => $skema->id]) }}" class="hover:text-indigo-600 font-medium">Pusat Formulir</a>
            <span>/</span>
            <span class="text-slate-800 font-bold">Master FR.AK.07</span>
        </div>

        <div class="flex items-center gap-2">
            <a href="{{ (url()->previous() && url()->previous() !== url()->current()) ? url()->previous() : route('asesor.mapa', ['skema_id' => $skema->id]) }}" 
               onclick="if (document.referrer && document.referrer !== window.location.href) { window.location.href = document.referrer; return false; } else if (window.history.length > 1) { window.history.back(); return false; }"
               class="inline-flex items-center px-3 py-1.5 rounded-xl border border-slate-200 bg-white hover:bg-slate-50 text-slate-700 text-xs font-semibold shadow-2xs transition cursor-pointer">
                <span>&larr; Kembali</span>
            </a>
            @if($isConfigured)
                <button type="button" @click="toggleEditMode()" 
                        class="inline-flex items-center px-3 py-1.5 rounded-xl border text-xs font-bold shadow-2xs transition"
                        :class="isEditMode ? 'bg-amber-500 hover:bg-amber-600 text-white border-amber-600' : 'bg-indigo-600 hover:bg-indigo-700 text-white border-indigo-700'">
                    <span x-text="isEditMode ? 'Kunci / Batal Edit' : 'Edit Formulir'"></span>
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
                @if($masterAk07->exists && $masterAk07->status === 'selesai')
                    <span class="px-3 py-1 rounded-full bg-emerald-50 text-emerald-700 border border-emerald-200 text-xs font-bold flex items-center gap-1.5">
                        <span class="w-2 h-2 rounded-full bg-emerald-500"></span>
                        ✓ Ditetapkan & Aktif
                    </span>
                @else
                    <span class="px-3 py-1 rounded-full bg-amber-50 text-amber-700 border border-amber-200 text-xs font-bold flex items-center gap-1.5">
                        <span class="w-2 h-2 rounded-full bg-amber-500 animate-pulse"></span>
                        Draf Pengaturan
                    </span>
                @endif
            </div>
        </div>

        <!-- INFO BANNER SINKRONISASI OTOMATIS -->
        <div class="bg-slate-50 border border-indigo-100 rounded-xl p-3.5 flex items-start gap-3 text-xs text-slate-700 leading-relaxed">
            <div class="space-y-0.5">
                <strong class="text-slate-900 block font-bold">Otomatis Berlaku dan Ditampilkan ke Seluruh Asesi:</strong>
                <span>
                    Format checklist penyesuaian, acuan pembanding, metode, dan tanda tangan digital asesor yang disimpan pada formulir ini akan <strong>otomatis diselaraskan dan ditampilkan</strong> ke seluruh asesi yang mendaftar di skema <strong>{{ $skema->nama_skema }}</strong>. Asesi dapat langsung melihat penyesuaian yang telah disiapkan asesor dan membubuhkan persetujuan digital.
                </span>
            </div>
        </div>
    </div>

    <!-- BANNER MODE TAMPILAN / EDIT -->
    <div x-show="!isEditMode" class="bg-slate-50 border border-slate-200 rounded-2xl p-4 flex items-center justify-between gap-3 text-xs text-slate-600 shadow-2xs">
        <div>
            <strong class="text-slate-800 font-bold block text-sm">Mode Tampilan (Terkunci)</strong>
            <span>Formulir telah dikonfigurasi dan ditampilkan dalam mode hanya lihat. Klik tombol <strong>Edit Formulir</strong> di atas untuk mengubah kesepakatan atau tanda tangan.</span>
        </div>
    </div>

    <div x-show="isEditMode && {{ $isConfigured ? 'true' : 'false' }}" class="bg-amber-50 border border-amber-200 rounded-2xl p-4 flex items-center gap-3 text-xs text-amber-800 shadow-2xs">
        <div>
            <strong class="text-amber-900 font-bold block text-sm">Mode Edit Aktif</strong>
            <span>Anda sekarang dapat mengubah potensi asesi, matriks penyesuaian, rekomendasi kesepakatan, dan tanda tangan digital. Klik tombol simpan di bawah setelah selesai.</span>
        </div>
    </div>

    <!-- MAIN FORM -->
    <form id="formMasterAk07" action="{{ route('asesor.skema.ak-07.simpan', $skema->id) }}" method="POST" @submit.prevent="submitAk07($event)" class="space-y-4">
        @csrf

        <fieldset :disabled="!isEditMode" :class="!isEditMode ? 'opacity-95' : ''" class="space-y-4 border-0 p-0 m-0">

        <!-- 1. POTENSI ASESI -->
        <div class="bg-white rounded-2xl border border-slate-200/90 shadow-2xs p-4 sm:p-5 space-y-3">
            <div class="flex items-center gap-2 text-sm font-bold text-slate-900 border-b border-slate-100 pb-2.5">
                <span class="w-6 h-6 rounded-lg bg-indigo-50 text-indigo-700 flex items-center justify-center text-xs font-black">1</span>
                <span>Potensi Asesi Standar (Kategori Kandidat MAPA.01)</span>
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-2 pt-1">
                @foreach($potensiDefinitions as $pVal => $pLabel)
                    <label class="relative flex items-start gap-2.5 p-3 rounded-xl border transition-all cursor-pointer text-xs {{ $selectedPotensi === $pVal ? 'border-indigo-500 bg-indigo-50/50 text-slate-900 font-semibold shadow-2xs' : 'border-slate-200 bg-white text-slate-600 hover:bg-slate-50/80' }}">
                        <input type="radio" name="potensi_asesi" value="{{ $pVal }}" {{ $selectedPotensi === $pVal ? 'checked' : '' }} class="mt-0.5 accent-indigo-600">
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
            <div class="flex items-center gap-2 text-sm font-bold text-slate-900 border-b border-slate-100 pb-2.5">
                <span class="w-6 h-6 rounded-lg bg-indigo-50 text-indigo-700 flex items-center justify-center text-xs font-black">2</span>
                <span>Fase Pelaksanaan Penyesuaian</span>
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
                    <label class="relative flex items-start gap-2.5 p-3 rounded-xl border transition-all cursor-pointer {{ $selectedFase === $fKey ? 'border-indigo-500 bg-indigo-50/50 text-slate-900 font-semibold shadow-2xs' : 'border-slate-200 bg-white text-slate-600 hover:bg-slate-50/80' }}">
                        <input type="radio" name="fase_penggunaan" value="{{ $fKey }}" {{ $selectedFase === $fKey ? 'checked' : '' }} class="mt-0.5 accent-indigo-600">
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
                <div class="flex items-center gap-2 text-sm font-bold text-slate-900">
                    <span class="w-6 h-6 rounded-lg bg-indigo-50 text-indigo-700 flex items-center justify-center text-xs font-black">3</span>
                    <span>Matriks Kebutuhan Penyesuaian yang Wajar (8 Kategori Standar BNSP)</span>
                </div>
                <span class="text-[11px] text-slate-400 font-medium">Acuan Baku BNSP FR.AK.07</span>
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
                        <div class="p-3.5 bg-slate-50/80 flex flex-col sm:flex-row sm:items-center justify-between gap-2.5 border-b border-slate-100">
                            <div class="flex items-center gap-2.5">
                                <span class="w-6 h-6 rounded-md bg-white border border-slate-200 text-slate-700 font-bold text-xs flex items-center justify-center shrink-0">
                                    {{ $cId }}
                                </span>
                                <div class="font-bold text-xs sm:text-sm text-slate-800">
                                    {{ $crit['title'] }}
                                </div>
                            </div>

                            <div class="inline-flex bg-white p-1 rounded-lg border border-slate-200 shadow-2xs text-xs">
                                <label class="px-2.5 py-1 rounded-md cursor-pointer transition-all flex items-center gap-1.5 font-bold" :class="!isNeed ? 'bg-slate-700 text-white' : 'text-slate-500 hover:text-slate-800'">
                                    <input type="radio" name="items_checklist[{{ $cId }}][perlu]" value="0" @change="isNeed = false" :checked="!isNeed" class="sr-only">
                                    <span>Tidak Perlu</span>
                                </label>
                                <label class="px-2.5 py-1 rounded-md cursor-pointer transition-all flex items-center gap-1.5 font-bold" :class="isNeed ? 'bg-indigo-600 text-white' : 'text-slate-500 hover:text-slate-800'">
                                    <input type="radio" name="items_checklist[{{ $cId }}][perlu]" value="1" @change="isNeed = true" :checked="isNeed" class="sr-only">
                                    <span>Perlu Penyesuaian</span>
                                </label>
                            </div>
                        </div>

                        <div x-show="isNeed" x-transition class="p-3.5 bg-indigo-50/20 space-y-3 text-xs border-t border-indigo-100">
                            <div>
                                <span class="font-bold text-slate-700 block mb-1.5">
                                    Pilih Opsi Bentuk Penyesuaian yang Disepakati:
                                </span>
                                <div class="grid grid-cols-1 sm:grid-cols-2 gap-2">
                                    @foreach($crit['sub_options'] as $subKey => $subLabel)
                                        <label class="flex items-start gap-2 p-2 rounded-lg bg-white border border-slate-200 hover:border-indigo-400 cursor-pointer transition-colors">
                                            <input type="checkbox" name="items_checklist[{{ $cId }}][opsi][]" value="{{ $subKey }}" {{ in_array($subKey, $opsiSaved) ? 'checked' : '' }} class="mt-0.5 accent-indigo-600">
                                            <span class="text-[11px] text-slate-700 leading-snug">{{ $subLabel }}</span>
                                        </label>
                                    @endforeach
                                </div>
                            </div>

                            <div>
                                <label class="font-bold text-slate-700 block mb-1">
                                    Uraian Detail / Catatan Khusus Penyesuaian Kategori {{ $cId }}:
                                </label>
                                <input type="text" name="items_checklist[{{ $cId }}][keterangan]" value="{{ $ketSaved }}" class="w-full px-3 py-1.5 rounded-lg border border-slate-200 bg-white text-xs focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500" placeholder="Contoh: Menggunakan instruksi bahasa yang komunikatif & waktu tambahan...">
                            </div>
                        </div>
                    </div>
                @endforeach
            </div>
        </div>

        <!-- 4. REKOMENDASI KESEPAKATAN ASESMEN -->
        <div class="bg-white rounded-2xl border border-slate-200/90 shadow-2xs p-4 sm:p-5 space-y-3">
            <div class="flex items-center gap-2 text-sm font-bold text-slate-900 border-b border-slate-100 pb-2.5">
                <span class="w-6 h-6 rounded-lg bg-indigo-50 text-indigo-700 flex items-center justify-center text-xs font-black">4</span>
                <span>Rekomendasi Hasil Kesepakatan Penyesuaian</span>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-3 gap-3 text-xs">
                <div class="space-y-1">
                    <label class="font-bold text-slate-700 block">
                        Acuan Pembanding Disepakati:
                    </label>
                    <textarea name="acuan_pembanding_disepakati" rows="3" class="w-full p-2.5 rounded-xl border border-slate-200 text-xs focus:ring-2 focus:ring-indigo-500" placeholder="Standar kompetensi pembanding...">{{ old('acuan_pembanding_disepakati', $masterAk07->acuan_pembanding_disepakati ?? "Standar Kompetensi Kerja Nasional Indonesia (SKKNI) {$skema->nama_skema}") }}</textarea>
                </div>

                <div class="space-y-1">
                    <label class="font-bold text-slate-700 block">
                        Metode Asesmen Disepakati:
                    </label>
                    <textarea name="metode_disepakati" rows="3" class="w-full p-2.5 rounded-xl border border-slate-200 text-xs focus:ring-2 focus:ring-indigo-500" placeholder="Metode asesmen yang disepakati...">{{ old('metode_disepakati', $masterAk07->metode_disepakati ?? 'Observasi Demonstrasi Langsung & Wawancara Terstruktur') }}</textarea>
                </div>

                <div class="space-y-1">
                    <label class="font-bold text-slate-700 block">
                        Instrumen Pendukung:
                    </label>
                    <textarea name="instrumen_disepakati" rows="3" class="w-full p-2.5 rounded-xl border border-slate-200 text-xs focus:ring-2 focus:ring-indigo-500" placeholder="Instrumen asesmen yang disesuaikan...">{{ old('instrumen_disepakati', $masterAk07->instrumen_disepakati ?? 'FR.IA.01 (Observasi Praktik), FR.IA.03 (Pertanyaan Pendukung Observasi)') }}</textarea>
                </div>
            </div>

            <div class="space-y-1 pt-1 text-xs">
                <label class="font-bold text-slate-700 block">
                    Catatan Tambahan Asesor:
                </label>
                <textarea name="catatan_asesor" rows="2" class="w-full p-2.5 rounded-xl border border-slate-200 text-xs focus:ring-2 focus:ring-indigo-500" placeholder="Catatan atau instruksi khusus pelaksanaan penyesuaian asesmen...">{{ old('catatan_asesor', $masterAk07->catatan_asesor) }}</textarea>
            </div>
        </div>

        <!-- 5. TANDA TANGAN ASESOR -->
        <div class="bg-white rounded-2xl border border-slate-200/90 shadow-2xs p-4 sm:p-5 space-y-4">
            <div class="flex items-center gap-2 text-sm font-bold text-slate-900 border-b border-slate-100 pb-2.5">
                <span class="w-6 h-6 rounded-lg bg-indigo-50 text-indigo-700 flex items-center justify-center text-xs font-black">5</span>
                <span>Tanda Tangan Digital Asesor</span>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-4 text-xs">
                <div class="border border-slate-200 rounded-xl p-3.5 space-y-3 bg-slate-50/50">
                    <div class="flex items-center justify-between border-b border-slate-200 pb-2">
                        <span class="font-bold text-slate-800">Asesor Penguji</span>
                        @if($masterAk07->tanda_tangan_asesor)
                            <span class="px-2 py-0.5 rounded-full bg-emerald-50 text-emerald-700 border border-emerald-200 text-[10px] font-bold">
                                Tertera TTD
                            </span>
                        @else
                            <span class="px-2 py-0.5 rounded-full bg-amber-50 text-amber-700 border border-amber-200 text-[10px] font-bold">
                                Belum TTD
                            </span>
                        @endif
                    </div>

                    <div class="text-slate-600">
                        <strong>{{ auth()->user()->nama_lengkap }}</strong> (No. MET: {{ auth()->user()->nomor_registrasi ?? 'MET.000.004455' }})
                    </div>

                    <div class="space-y-2">
                        <div class="flex items-center gap-3">
                            @if(!empty($userSignature))
                                <label class="inline-flex items-center gap-1.5 cursor-pointer text-xs font-medium text-slate-700">
                                    <input type="radio" name="sign_mode" value="profile" x-model="signatureMode" class="accent-indigo-600">
                                    <span>Gunakan TTD Akun Profil</span>
                                </label>
                            @endif
                            <label class="inline-flex items-center gap-1.5 cursor-pointer text-xs font-medium text-slate-700">
                                <input type="radio" name="sign_mode" value="canvas" x-model="signatureMode" class="accent-indigo-600">
                                <span>Gores TTD Baru</span>
                            </label>
                        </div>

                        <!-- Canvas Box -->
                        <div x-show="signatureMode === 'canvas'" class="space-y-1.5">
                            <div class="border-2 border-dashed border-slate-300 rounded-xl bg-white relative p-1">
                                <canvas id="canvasMasterAk07" class="w-full h-28 rounded-lg cursor-crosshair touch-none"></canvas>
                                <button type="button" @click="clearCanvas()" class="absolute top-2 right-2 px-2 py-1 bg-slate-100 hover:bg-slate-200 text-slate-600 rounded text-[10px] font-bold transition">
                                    Bersihkan
                                </button>
                            </div>
                            <input type="hidden" name="tanda_tangan_asesor" id="inputSignatureMasterAk07" x-ref="signatureInput">
                        </div>

                        <!-- Preview Profil Box -->
                        <div x-show="signatureMode === 'profile'" class="p-2 border border-slate-200 rounded-xl bg-white flex items-center justify-center h-28">
                            @if(!empty($userSignature))
                                <img src="{{ asset($userSignature) }}" alt="TTD Profil" class="max-h-24 object-contain">
                            @else
                                <span class="text-slate-400 italic">Belum ada tanda tangan di profil.</span>
                            @endif
                        </div>
                    </div>
                </div>

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

        <!-- SUBMIT BAR -->
        <div x-show="isEditMode" class="flex flex-col sm:flex-row sm:items-center justify-between gap-3 pt-3 pb-8">
            <div class="text-xs text-slate-500">
                Klik simpan untuk menerapkan dokumen ini ke seluruh asesi skema <strong>{{ $skema->nama_skema }}</strong>.
            </div>

            <div class="flex items-center gap-2">
                @if($isConfigured)
                    <button type="button" @click="toggleEditMode()" class="px-4 py-2 rounded-xl border border-slate-200 text-xs font-bold text-slate-600 hover:bg-slate-50 transition">
                        Batal
                    </button>
                @endif
                <button type="submit" :disabled="isSubmitting" class="px-6 py-2.5 rounded-xl bg-indigo-600 hover:bg-indigo-700 text-white text-xs font-bold shadow-xs transition flex items-center gap-2">
                    <span x-text="isSubmitting ? 'Menyimpan...' : 'Simpan & Sahkan Master FR.AK.07'"></span>
                </button>
            </div>
        </div>
    </form>

</div>

@push('scripts')
<script>
function ak07SkemaApp() {
    return {
        isEditMode: {{ $isConfigured ? 'false' : 'true' }},
        isSubmitting: false,
        signatureMode: '{{ !empty($userSignature) ? "profile" : "canvas" }}',
        sigPad: null,

        init() {
            this.$nextTick(() => {
                this.initCanvas();
            });

            this.$watch('signatureMode', (val) => {
                if (val === 'canvas') {
                    this.$nextTick(() => this.initCanvas());
                }
            });
        },

        toggleEditMode() {
            this.isEditMode = !this.isEditMode;
            if (this.isEditMode) {
                this.$nextTick(() => this.initCanvas());
            }
        },

        initCanvas() {
            const canvas = document.getElementById('canvasMasterAk07');
            if (canvas && !this.sigPad) {
                const ratio = Math.max(window.devicePixelRatio || 1, 1);
                canvas.width = canvas.offsetWidth * ratio;
                canvas.height = canvas.offsetHeight * ratio;
                canvas.getContext('2d').scale(ratio, ratio);

                this.sigPad = new SignaturePad(canvas, {
                    backgroundColor: 'rgb(255, 255, 255)',
                    penColor: 'rgb(15, 23, 42)'
                });
            }
        },

        clearCanvas() {
            if (this.sigPad) {
                this.sigPad.clear();
            }
        },

        submitAk07(e) {
            this.isSubmitting = true;

            const inputTtd = document.getElementById('inputSignatureMasterAk07');
            if (this.signatureMode === 'canvas') {
                if (this.sigPad && !this.sigPad.isEmpty()) {
                    inputTtd.value = this.sigPad.toDataURL();
                }
            } else {
                inputTtd.value = '{{ $userSignature }}';
            }

            e.target.submit();
        }
    };
}
</script>
@endpush
@endsection
