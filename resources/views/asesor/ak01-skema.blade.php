@extends('tata-letak.dasbor')

@section('judul', 'Master FR.AK.01 - ' . ($skema->nama_skema ?? 'Skema'))

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
    $savedBukti = (array) ($masterAk01->bukti_dikumpulkan ?? []);
    $currentSignature = $masterAk01->tanda_tangan_asesor ?? null;
    $isSigned = !empty($currentSignature);
    $isConfigured = $masterAk01->exists && $masterAk01->status === 'selesai';
@endphp

<div class="max-w-5xl mx-auto px-2 sm:px-4 py-3 space-y-4" x-data="ak01SkemaApp()">

    <!-- BREADCRUMB -->
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-2 pb-1">
        <div class="flex items-center gap-2 text-xs text-slate-500">
            <a href="{{ route('asesor.dashboard') }}" class="hover:text-indigo-600 font-medium">Dashboard</a>
            <span>/</span>
            <a href="{{ route('asesor.mapa', ['skema_id' => $skema->id]) }}" class="hover:text-indigo-600 font-medium">Pusat Formulir</a>
            <span>/</span>
            <span class="text-slate-800 font-bold">Master FR.AK.01</span>
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
            <a href="{{ route('formulir.ak01', ['skema_id' => $skema->id]) }}" target="_blank" 
               class="inline-flex items-center px-3 py-1.5 rounded-xl border border-slate-200 bg-white hover:bg-slate-50 text-slate-700 text-xs font-semibold shadow-2xs transition">
                <span>Pratinjau Cetak</span>
            </a>
        </div>
    </div>

    <!-- HEADER CARD -->
    <div class="bg-white rounded-2xl border border-slate-200/90 shadow-2xs p-4 sm:p-5 space-y-3">
        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3 border-b border-slate-100 pb-3">
            <div class="flex items-center gap-3">
                <div class="w-11 h-11 rounded-xl bg-indigo-50 text-indigo-700 border border-indigo-100 flex items-center justify-center font-black text-sm shrink-0">
                    AK.01
                </div>
                <div>
                    <div class="flex items-center gap-2 flex-wrap">
                        <span class="px-2 py-0.5 rounded-md bg-indigo-100 text-indigo-800 text-[10px] font-extrabold uppercase tracking-wider">
                            MASTER TEMPLATE SKEMA
                        </span>
                        <span class="font-mono text-xs font-bold text-slate-500">{{ $skema->kode_skema }}</span>
                    </div>
                    <h1 class="text-base sm:text-lg font-bold text-slate-900 leading-tight mt-0.5">
                        FR.AK.01 &mdash; Persetujuan Asesmen & Kerahasiaan
                    </h1>
                    <p class="text-xs text-slate-500">
                        Penetapan rencana pelaksanaan asesmen dan komitmen kerahasiaan untuk seluruh asesi pada skema sertifikasi ini.
                    </p>
                </div>
            </div>

            <!-- Status Pill Header -->
            <div class="flex items-center gap-2">
                @if($masterAk01->exists && $masterAk01->status === 'selesai')
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
                <strong class="text-slate-900 block font-bold">Otomatis Berlaku untuk Seluruh Asesi:</strong>
                <span>
                    Pengaturan TUK, metode bukti, dan tanda tangan digital asesor yang disimpan pada formulir ini akan <strong>otomatis diambil dan ditampilkan</strong> pada formulir FR.AK.01 seluruh asesi yang mendaftar di skema <strong>{{ $skema->nama_skema }}</strong>. Asesi cukup meninjau dan menandatangani persetujuan secara digital.
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
            <span>Anda sekarang dapat mengubah data TUK, bukti yang dikumpulkan, catatan, dan tanda tangan digital. Klik tombol simpan di bawah setelah selesai.</span>
        </div>
    </div>

    <!-- MAIN FORM -->
    <form id="formMasterAk01" action="{{ route('asesor.skema.ak-01.simpan', $skema->id) }}" method="POST" @submit.prevent="submitAk01($event)" class="space-y-4">
        @csrf

        <fieldset :disabled="!isEditMode" :class="!isEditMode ? 'opacity-95' : ''" class="space-y-4 border-0 p-0 m-0">

        <!-- 1. RINGKASAN UNIT KOMPETENSI SKEMA -->
        <div class="bg-white rounded-2xl border border-slate-200/90 shadow-2xs overflow-hidden">
            <div class="px-4 py-3 bg-slate-50/80 border-b border-slate-200/80 flex items-center justify-between">
                <div>
                    <h2 class="font-bold text-xs sm:text-sm text-slate-800">1. Unit Kompetensi yang Diujikan pada Skema</h2>
                    <p class="text-[11px] text-slate-500">Daftar unit kompetensi standar yang akan dinilai pada skema ini.</p>
                </div>
                <span class="text-xs font-semibold text-slate-600 bg-white border border-slate-200 px-2.5 py-1 rounded-lg">
                    {{ $allUnits->count() }} Unit Kompetensi
                </span>
            </div>

            <div class="overflow-x-auto max-h-56 custom-scrollbar">
                <table class="w-full text-left border-collapse text-xs">
                    <thead>
                        <tr class="bg-slate-50/60 border-b border-slate-200 text-[11px] font-bold text-slate-600 uppercase tracking-wider sticky top-0 bg-slate-50">
                            <th class="py-2.5 px-3 w-10 text-center">No</th>
                            <th class="py-2.5 px-3 w-40 font-mono">Kode Unit</th>
                            <th class="py-2.5 px-3">Judul Unit Kompetensi</th>
                            <th class="py-2.5 px-3 w-32 text-center">Jumlah Elemen</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100">
                        @forelse($allUnits as $idx => $unit)
                            <tr class="hover:bg-slate-50/50 transition-colors">
                                <td class="py-2 px-3 text-center text-slate-400 font-medium">{{ $idx + 1 }}</td>
                                <td class="py-2 px-3 font-mono font-semibold text-slate-800">{{ $unit->kode_unit }}</td>
                                <td class="py-2 px-3 font-medium text-slate-900">{{ $unit->judul_unit }}</td>
                                <td class="py-2 px-3 text-center text-slate-500 font-medium">{{ $unit->elemenKompetensi->count() }} Elemen</td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="4" class="py-6 text-center text-slate-400 italic">Belum ada unit kompetensi terdaftar pada skema ini.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

        <!-- 2. KESEPAKATAN PELAKSANAAN ASESMEN (TUK & METODE BUKTI) -->
        <div class="bg-white rounded-2xl border border-slate-200/90 shadow-2xs p-4 sm:p-5 space-y-4">
            <div class="border-b border-slate-100 pb-2 flex items-center justify-between">
                <div>
                    <h2 class="font-bold text-xs sm:text-sm text-slate-800">
                        2. Kesepakatan Pelaksanaan Asesmen (Ditetapkan Asesor)
                    </h2>
                    <p class="text-[11px] text-slate-500">Pilih jenis TUK dan rencana metode pengumpulan bukti yang berlaku untuk asesi pada skema ini.</p>
                </div>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-4 text-xs">
                <!-- Pilihan TUK -->
                <div class="space-y-1.5">
                    <label class="font-bold text-slate-700 block">
                        Jenis Tempat Uji Kompetensi (TUK) <span class="text-rose-500">*</span>
                    </label>
                    <select name="tuk_type" required 
                            class="w-full bg-slate-50 border border-slate-200 rounded-xl px-3 py-2 text-slate-800 font-medium focus:ring-1 focus:ring-indigo-500 focus:bg-white transition-colors">
                        <option value="" disabled {{ empty(old('tuk_type', $masterAk01->tuk_type)) ? 'selected' : '' }}>-- Pilih Jenis TUK --</option>
                        <option value="Sewaktu" {{ old('tuk_type', $masterAk01->tuk_type) === 'Sewaktu' ? 'selected' : '' }}>TUK Sewaktu (SMKN 1 Gunungputri / Sekolah Mitra)</option>
                        <option value="Tempat Kerja" {{ old('tuk_type', $masterAk01->tuk_type) === 'Tempat Kerja' ? 'selected' : '' }}>TUK Tempat Kerja / Fasilitas Industri (DUDI)</option>
                        <option value="Mandiri" {{ old('tuk_type', $masterAk01->tuk_type) === 'Mandiri' ? 'selected' : '' }}>TUK Mandiri</option>
                    </select>
                    <span class="text-[10px] text-slate-400 block">Ditetapkan sebagai lokasi uji standar bagi asesi.</span>
                </div>

                <!-- Metode Pengumpulan Bukti -->
                <div class="space-y-1.5">
                    <label class="font-bold text-slate-700 block">
                        Metode Pengumpulan Bukti yang Disepakati
                    </label>
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-1.5 pt-0.5">
                        <label class="flex items-center gap-2 p-2 rounded-lg bg-slate-50 border border-slate-100 text-slate-700 cursor-pointer hover:bg-slate-100/70 transition-colors">
                            <input type="checkbox" name="bukti_dikumpulkan[]" value="Observasi Praktik Demonstrasi"
                                   {{ in_array('Observasi Praktik Demonstrasi', $savedBukti) || in_array('Uji Praktik / Observasi Demonstrasi', $savedBukti) ? 'checked' : '' }}
                                   class="rounded-sm text-indigo-600 focus:ring-0">
                            <span class="text-[11px] font-medium">Observasi Praktik</span>
                        </label>
                        <label class="flex items-center gap-2 p-2 rounded-lg bg-slate-50 border border-slate-100 text-slate-700 cursor-pointer hover:bg-slate-100/70 transition-colors">
                            <input type="checkbox" name="bukti_dikumpulkan[]" value="Uji Tertulis (CBT)"
                                   {{ in_array('Uji Tertulis (CBT)', $savedBukti) ? 'checked' : '' }}
                                   class="rounded-sm text-indigo-600 focus:ring-0">
                            <span class="text-[11px] font-medium">Uji Tertulis CBT</span>
                        </label>
                        <label class="flex items-center gap-2 p-2 rounded-lg bg-slate-50 border border-slate-100 text-slate-700 cursor-pointer hover:bg-slate-100/70 transition-colors">
                            <input type="checkbox" name="bukti_dikumpulkan[]" value="Tanya Jawab Lisan"
                                   {{ in_array('Tanya Jawab Lisan', $savedBukti) ? 'checked' : '' }}
                                   class="rounded-sm text-indigo-600 focus:ring-0">
                            <span class="text-[11px] font-medium">Tanya Jawab Lisan</span>
                        </label>
                        <label class="flex items-center gap-2 p-2 rounded-lg bg-slate-50 border border-slate-100 text-slate-700 cursor-pointer hover:bg-slate-100/70 transition-colors">
                            <input type="checkbox" name="bukti_dikumpulkan[]" value="Verifikasi Portofolio"
                                   {{ in_array('Verifikasi Portofolio', $savedBukti) || in_array('Hasil Verifikasi Portofolio', $savedBukti) ? 'checked' : '' }}
                                   class="rounded-sm text-indigo-600 focus:ring-0">
                            <span class="text-[11px] font-medium">Verifikasi Portofolio</span>
                        </label>
                    </div>
                </div>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-4 text-xs pt-1 border-t border-slate-100">
                <div class="space-y-1.5">
                    <label class="font-bold text-slate-700 block">Bukti Tambahan / Lainnya (Opsional)</label>
                    <input type="text" name="bukti_dikumpulkan_lainnya" 
                           value="{{ old('bukti_dikumpulkan_lainnya', $masterAk01->bukti_dikumpulkan_lainnya) }}" 
                           placeholder="Contoh: Portofolio Proyek, Logbook PKL, Surat Rekomendasi Industri"
                           class="w-full bg-slate-50 border border-slate-200 rounded-xl px-3 py-2 text-slate-800 font-medium focus:ring-1 focus:ring-indigo-500 focus:bg-white transition-colors">
                </div>

                <div class="space-y-1.5">
                    <label class="font-bold text-slate-700 block">Catatan / Arahan Pelaksanaan Asesmen (Opsional)</label>
                    <input type="text" name="catatan_asesor" 
                           value="{{ old('catatan_asesor', $masterAk01->catatan_asesor) }}" 
                           placeholder="Contoh: Asesi wajib membawa perlengkapan APD dan instrumen uji praktik"
                           class="w-full bg-slate-50 border border-slate-200 rounded-xl px-3 py-2 text-slate-800 font-medium focus:ring-1 focus:ring-indigo-500 focus:bg-white transition-colors">
                </div>
            </div>
        </div>

        <!-- 3. KLAUSUL PERSETUJUAN ASESMEN & KERAHASIAAN -->
        <div class="bg-slate-50 rounded-2xl border border-slate-200/90 p-4 sm:p-5 space-y-3">
            <h2 class="font-bold text-xs sm:text-sm text-slate-800 flex items-center gap-2">
                <span>3. Klausul Persetujuan & Komitmen Kerahasiaan Resmi BNSP</span>
            </h2>

            <div class="text-xs text-slate-600 space-y-2 bg-white rounded-xl p-3.5 border border-slate-200/80 leading-relaxed">
                <p>
                    <strong>Komitmen Kerahasiaan (Non-Disclosure):</strong> &ldquo;Asesor dan Asesi menjamin kerahasiaan seluruh materi asesmen, instrumen uji, dan perangkat evaluasi yang digunakan selama proses sertifikasi, serta tidak menyebarluaskan materi uji tanpa izin resmi LSP.&rdquo;
                </p>
                <p>
                    <strong>Pernyataan Kesepakatan:</strong> &ldquo;Proses asesmen diselenggarakan sesuai prinsip asesmen Valid, Andal, Fleksibel, dan Adil. Rencana asesmen telah dibahas dan disepakati bersama.&rdquo;
                </p>
            </div>

            <label class="flex items-start gap-2.5 text-xs text-slate-800 font-semibold cursor-pointer pt-1 select-none">
                <input type="checkbox" x-model="agreedToClause" required class="mt-0.5 rounded-sm text-indigo-600 focus:ring-0">
                <span>Saya menyatakan telah menyusun rencana asesmen FR.AK.01 ini secara baku untuk seluruh asesi pada skema ini.</span>
            </label>
        </div>

        <!-- 4. PENGESAHAN TANDA TANGAN ASESOR -->
        <div class="bg-white rounded-2xl border border-slate-200/90 shadow-2xs p-4 sm:p-5 space-y-4">
            <h2 class="font-bold text-xs sm:text-sm text-slate-800 border-b border-slate-100 pb-2">
                4. Pengesahan & Tanda Tangan Asesor Penguji
            </h2>

            <div class="space-y-3 text-xs">
                @if($currentSignature)
                    <div class="p-3 bg-slate-50 border border-slate-200 rounded-xl mb-3 flex items-center gap-3">
                        <span class="font-bold text-slate-700">Tanda Tangan Asesor Aktif:</span>
                        <img src="{{ Str::startsWith($currentSignature, 'data:') ? $currentSignature : asset($currentSignature) }}" alt="TTD Master" class="max-h-12 object-contain bg-white p-1 border rounded">
                    </div>
                @endif

                <!-- Canvas Tanda Tangan -->
                <div class="space-y-2">
                    <span class="font-bold text-slate-700 block">Bubuhkan Tanda Tangan Digital Baru (Canvas):</span>
                    <div class="border border-slate-300 rounded-xl bg-white relative overflow-hidden">
                        <canvas id="canvasMasterAk01" width="800" height="240" class="w-full h-36 bg-white cursor-crosshair block touch-none" style="touch-action: none;"></canvas>
                        <input type="hidden" name="tanda_tangan_asesor" id="inputSignatureMasterAk01" x-ref="signatureInput" value="{{ $currentSignature ?? '' }}">
                    </div>
                    <div class="flex items-center justify-between text-[11px]">
                        <span class="text-slate-400">Tanda tangan di area putih di atas.</span>
                        <button type="button" @click="clearSignature()" class="text-rose-600 hover:text-rose-800 font-semibold cursor-pointer">
                            [ Hapus / Ulangi ]
                        </button>
                    </div>
                </div>
            </div>
        </div>
        </fieldset>

        <!-- ACTION BAR -->
        <div class="bg-white rounded-2xl border border-slate-200/90 p-4 shadow-2xs flex flex-col sm:flex-row items-center justify-between gap-3">
            <a href="{{ (url()->previous() && url()->previous() !== url()->current()) ? url()->previous() : route('asesor.mapa', ['skema_id' => $skema->id]) }}" 
               onclick="if (document.referrer && document.referrer !== window.location.href) { window.location.href = document.referrer; return false; } else if (window.history.length > 1) { window.history.back(); return false; }"
               class="w-full sm:w-auto px-4 py-2.5 rounded-xl border border-slate-200 hover:bg-slate-50 text-slate-700 text-xs font-semibold transition text-center cursor-pointer">
                &larr; Kembali
            </a>

            <!-- State Keterangan saat locked -->
            <div x-show="!isEditMode" class="text-xs text-slate-500 flex items-center gap-2">
                <span class="w-1.5 h-1.5 rounded-full bg-indigo-500"></span>
                <span>Formulir dalam mode tampilan (hanya lihat). Klik <strong>Edit Formulir</strong> di atas untuk mengubah.</span>
            </div>

            <!-- Tombol Simpan saat edit mode aktif -->
            <button type="submit" 
                    x-show="isEditMode"
                    :disabled="!agreedToClause || isSubmitting"
                    :class="(!agreedToClause || isSubmitting) ? 'opacity-50 cursor-not-allowed bg-indigo-400' : 'bg-indigo-600 hover:bg-indigo-700 cursor-pointer'"
                    class="w-full sm:w-auto px-6 py-2.5 text-white text-xs font-bold rounded-xl shadow-xs transition-all flex items-center justify-center gap-2">
                <template x-if="!isSubmitting">
                    <span>Simpan dan Terapkan ke Seluruh Asesi Skema Ini</span>
                </template>
                <template x-if="isSubmitting">
                    <span>Menyimpan dan Menerapkan...</span>
                </template>
            </button>
        </div>
    </form>
</div>

<!-- Alpine.js & Signature Engine -->
<script>
    function ak01SkemaApp() {
        return {
            isEditMode: {{ $isConfigured ? 'false' : 'true' }},
            agreedToClause: {{ $masterAk01->exists ? 'true' : 'false' }},
            signMode: 'canvas',
            isSubmitting: false,
            pad: null,

            init() {
                this.$nextTick(() => {
                    this.initCanvas();
                });
            },

            toggleEditMode() {
                this.isEditMode = !this.isEditMode;
                if (this.isEditMode && this.signMode === 'canvas') {
                    this.$nextTick(() => {
                        this.initCanvas();
                    });
                }
            },

            initCanvas() {
                const canvas = document.getElementById('canvasMasterAk01');
                if (!canvas) return;

                const ratio = Math.max(window.devicePixelRatio || 1, 1);
                canvas.width = canvas.offsetWidth * ratio;
                canvas.height = canvas.offsetHeight * ratio;
                canvas.getContext("2d").scale(ratio, ratio);

                if (typeof SignaturePad !== 'undefined') {
                    this.pad = new SignaturePad(canvas, {
                        backgroundColor: 'rgba(255, 255, 255, 0)',
                        penColor: '#0f172a'
                    });
                }
            },

            clearSignature() {
                if (this.pad) {
                    this.pad.clear();
                }
                if (this.$refs.signatureInput) {
                    this.$refs.signatureInput.value = '';
                }
            },

            submitAk01(e) {
                if (this.isSubmitting) return;

                if (!this.agreedToClause) {
                    Swal.fire({
                        icon: 'warning',
                        title: 'Pernyataan Asesor',
                        text: 'Silakan centang pernyataan kesiapan penyusunan rencana asesmen terlebih dahulu.',
                        confirmButtonColor: '#4f46e5'
                    });
                    return;
                }

                if (this.pad && !this.pad.isEmpty()) {
                    const dataUrl = this.pad.toDataURL('image/png');
                    if (this.$refs.signatureInput) {
                        this.$refs.signatureInput.value = dataUrl;
                    }
                } else if (!this.$refs.signatureInput || !this.$refs.signatureInput.value) {
                    Swal.fire({
                        icon: 'warning',
                        title: 'Tanda Tangan Kosong',
                        text: 'Silakan bubuhkan tanda tangan digital pada canvas terlebih dahulu.',
                        confirmButtonColor: '#4f46e5'
                    });
                    return;
                }

                this.isSubmitting = true;
                e.target.submit();
            }
        };
    }
</script>
@endsection
