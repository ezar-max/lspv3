@extends('tata-letak.dasbor')

@section('judul', 'FR.AK.02 - Rekaman Asesmen Kompetensi')

@push('css')
    <!-- Signature Pad CDN -->
    <script src="https://cdn.jsdelivr.net/npm/signature_pad@4.1.7/dist/signature_pad.umd.min.js"></script>
    <style>
        .th-compact { padding: 6px 8px; font-size: 10px; font-weight: 800; text-transform: uppercase; letter-spacing: 0.04em; }
        .td-compact { padding: 6px 8px; font-size: 11px; vertical-align: middle; }
    </style>
@endpush

@section('konten')
@php
    $user = auth()->user();
    $isAsesor = in_array($user->peran, ['asesor', 'admin', 'superadmin']);
    $isAsesi = $user->peran === 'asesi';
    $isFinal = $ak02->isFinalized();
    $canEdit = $isAsesor && !$isFinal;
    $units = $pendaftaran->skema ? $pendaftaran->skema->unitKompetensi : collect();
    $matriksSaved = (array) ($ak02->matriks_bukti ?? []);
    $rekomendasiSaved = (array) ($ak02->rekomendasi_unit ?? []);
@endphp

<div class="space-y-4" x-data="ak02FormApp()" x-cloak>

    <!-- TOP BREADCRUMB & ACTION BAR (STICKY HEADER) -->
    <div class="sticky top-16 z-30 bg-white/95 backdrop-blur-md border-b border-slate-200/90 py-2.5 -mx-4 sm:-mx-6 lg:-mx-8 px-4 sm:px-6 lg:px-8 shadow-2xs flex flex-col sm:flex-row sm:items-center justify-between gap-3">
        <div class="flex items-center gap-2 text-xs">
            <a href="{{ (url()->previous() && url()->previous() !== url()->current()) ? url()->previous() : route('dokumen-asesmen.index') }}" 
               onclick="if (document.referrer && document.referrer !== window.location.href) { window.location.href = document.referrer; return false; } else if (window.history.length > 1) { window.history.back(); return false; }"
               class="px-2.5 py-1 rounded-lg border border-slate-200 bg-white hover:bg-slate-50 text-slate-700 font-semibold transition-colors cursor-pointer shadow-2xs">
                &larr; Kembali
            </a>
            <span class="text-slate-300">/</span>
            <span class="px-2 py-0.5 rounded-md bg-blue-50 text-blue-700 font-mono font-bold text-[10px]">FR.AK.02</span>
            <span class="text-slate-800 font-bold hidden md:inline">Rekaman Asesmen Kompetensi</span>
        </div>

        <div class="flex items-center gap-2 flex-wrap justify-end text-xs">
            <!-- Autosave Indicator -->
            <div class="flex items-center gap-1.5 px-2.5 py-1 rounded-lg bg-slate-50 border border-slate-200/80 text-slate-500 text-[11px]">
                <span class="w-1.5 h-1.5 rounded-full" :class="isSaving ? 'bg-amber-500 animate-ping' : 'bg-emerald-500'"></span>
                <span x-text="saveStatusText"></span>
            </div>

            <!-- Pratinjau Cetak -->
            <a href="{{ route('dokumen-asesmen.ak02.cetak', $pendaftaran->id) }}" target="_blank" class="inline-flex items-center px-3 py-1.5 rounded-xl border border-slate-200 bg-white text-slate-700 font-semibold hover:bg-slate-50 transition-colors shadow-2xs">
                <span>Cetak A4</span>
            </a>

            <!-- Tombol Buka Kembali (Admin / Superadmin) -->
            @if($isFinal && in_array($user->peran, ['admin', 'superadmin']))
                <button type="button" @click="openReopenModal = true" class="inline-flex items-center px-3 py-1.5 rounded-xl bg-amber-50 border border-amber-200 text-amber-800 font-bold hover:bg-amber-100 transition-colors">
                    <span>Buka Revisi</span>
                </button>
            @endif

            <!-- Tombol Simpan Draf (Asesor) -->
            @if($canEdit)
                <button type="button" @click="manualSaveDraft()" class="inline-flex items-center px-3.5 py-1.5 rounded-xl bg-slate-900 text-white font-bold hover:bg-slate-800 transition-colors shadow-2xs">
                    <span>Simpan Draf</span>
                </button>
            @endif
        </div>
    </div>

    <!-- NOTIFIKASI FLASH -->
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

    <!-- CONTEXTUAL INFORMATION PANEL (AUTO-FILLED HEADER) -->
    <div class="bg-white rounded-2xl border border-slate-200/90 shadow-2xs p-4 sm:p-5 space-y-3">
        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-2 border-b border-slate-100 pb-3">
            <div>
                <div class="text-[10px] font-mono font-black text-blue-700 uppercase tracking-wider">FR.AK.02 &bull; STANDAR BNSP</div>
                <h2 class="text-base sm:text-lg font-black text-slate-900 leading-tight">Rekaman Asesmen Kompetensi</h2>
            </div>

            <!-- Status Pill -->
            <div class="flex items-center gap-2">
                @php
                    $statusBadge = match($ak02->status) {
                        'final', 'terkunci' => 'bg-emerald-50 text-emerald-700 border-emerald-200',
                        'decision_recorded', 'signed' => 'bg-blue-50 text-blue-700 border-blue-200',
                        'dalam_pengisian' => 'bg-amber-50 text-amber-700 border-amber-200',
                        default => 'bg-slate-100 text-slate-600 border-slate-200',
                    };
                @endphp
                <span class="px-3 py-1 rounded-full border text-xs font-extrabold uppercase tracking-wider {{ $statusBadge }}">
                    {{ str_replace('_', ' ', $ak02->status) }} &bull; v{{ $ak02->version }}
                </span>
            </div>
        </div>

        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-3 text-xs">
            <div class="bg-slate-50/70 border border-slate-100 rounded-xl p-3">
                <span class="text-[10px] font-bold text-slate-400 uppercase tracking-wider block">Skema Sertifikasi</span>
                <span class="font-bold text-slate-800 block mt-0.5">{{ $pendaftaran->skema->nama_skema ?? '-' }}</span>
                <span class="text-[10px] text-slate-500 font-mono">Kode: {{ $pendaftaran->skema->kode_skema ?? '-' }}</span>
            </div>

            <div class="bg-slate-50/70 border border-slate-100 rounded-xl p-3">
                <span class="text-[10px] font-bold text-slate-400 uppercase tracking-wider block">Tempat Uji Kompetensi (TUK)</span>
                <span class="font-bold text-slate-800 block mt-0.5">{{ $pendaftaran->jadwal->nama_tuk ?? 'TUK Mandiri SMKN 1 Gunungputri' }}</span>
                <span class="text-[10px] text-slate-500">Jenis: Sewaktu / Tempat Kerja / Mandiri</span>
            </div>

            <div class="bg-slate-50/70 border border-slate-100 rounded-xl p-3">
                <span class="text-[10px] font-bold text-slate-400 uppercase tracking-wider block">Nama Asesi (Peserta)</span>
                <span class="font-bold text-slate-800 block mt-0.5">{{ $pendaftaran->asesi->nama_lengkap ?? '-' }}</span>
                <span class="text-[10px] text-blue-600 font-mono">Reg: #{{ $pendaftaran->nomor_pendaftaran }}</span>
            </div>

            <div class="bg-slate-50/70 border border-slate-100 rounded-xl p-3">
                <span class="text-[10px] font-bold text-slate-400 uppercase tracking-wider block">Nama Asesor</span>
                <span class="font-bold text-slate-800 block mt-0.5">{{ $pendaftaran->asesor->nama_lengkap ?? auth()->user()->nama_lengkap }}</span>
                <span class="text-[10px] text-slate-500 font-mono">MET: {{ $pendaftaran->asesor->nomor_registrasi ?? 'MET.000.004455' }}</span>
            </div>
        </div>
    </div>

    <!-- MAIN FORM BODY -->
    <form id="ak02Form" method="POST" action="{{ route('dokumen-asesmen.ak02.simpan', $pendaftaran->id) }}" class="space-y-4">
        @csrf

        <!-- SECTION 1: MATRIKS METODE BUKTI ASESMEN PER UNIT KOMPETENSI -->
        <div class="bg-white rounded-2xl border border-slate-200/90 shadow-2xs overflow-hidden">
            <div class="px-4 py-3 bg-slate-50/80 border-b border-slate-200 flex flex-col sm:flex-row sm:items-center justify-between gap-2">
                <div>
                    <h3 class="text-xs font-black text-slate-800 uppercase tracking-wider">
                        1. Matriks Metode Bukti Asesmen Per Unit Kompetensi
                    </h3>
                    <p class="text-[11px] text-slate-500">
                        Beri tanda centang pada kolom metode pengumpulan bukti yang digunakan untuk setiap unit kompetensi.
                    </p>
                </div>
                <div class="text-[11px] text-slate-500 font-mono">
                    Total Unit: <strong class="text-slate-800">{{ $units->count() }}</strong>
                </div>
            </div>

            <div class="overflow-x-auto">
                <table class="w-full text-left border-collapse border-b border-slate-200 text-xs">
                    <thead>
                        <tr class="bg-slate-100/80 text-slate-600 border-b border-slate-200">
                            <th class="th-compact w-8 text-center">No</th>
                            <th class="th-compact min-w-[220px]">Unit Kompetensi</th>
                            <th class="th-compact text-center" title="Observasi Demonstrasi">Obs. Demo</th>
                            <th class="th-compact text-center" title="Portofolio">Portofolio</th>
                            <th class="th-compact text-center" title="Pernyataan Pihak Ketiga">Pihak ke-3</th>
                            <th class="th-compact text-center" title="Pertanyaan Wawancara">Wawancara</th>
                            <th class="th-compact text-center" title="Pertanyaan Lisan">Lisan</th>
                            <th class="th-compact text-center" title="Pertanyaan Tertulis">Tertulis</th>
                            <th class="th-compact text-center" title="Proyek Kerja">Proyek</th>
                            <th class="th-compact text-center" title="Metode Lainnya">Lainnya</th>
                            <th class="th-compact min-w-[140px]">Keputusan Unit</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100 font-medium">
                        @foreach($units as $idx => $u)
                            @php
                                $uSaved = $matriksSaved[$u->id] ?? [];
                                $methods = $uSaved['methods'] ?? [];
                                $rekUnit = $rekomendasiSaved[$u->id] ?? [];
                                $hasilUnit = strtoupper(is_array($rekUnit) ? ($rekUnit['hasil'] ?? 'K') : $rekUnit);
                            @endphp
                            <tr class="hover:bg-slate-50/50">
                                <td class="td-compact text-center font-bold text-slate-400">{{ $idx + 1 }}</td>
                                <td class="td-compact">
                                    <div class="font-mono text-[10px] font-extrabold text-blue-700">{{ $u->kode_unit }}</div>
                                    <div class="font-bold text-slate-800 leading-tight">{{ $u->judul_unit }}</div>
                                </td>

                                <!-- Observasi Demonstrasi -->
                                <td class="td-compact text-center">
                                    <input type="checkbox" name="matriks_bukti[{{ $u->id }}][methods][observasi_demonstrasi]" value="1"
                                           {{ !empty($methods['observasi_demonstrasi']) ? 'checked' : '' }}
                                           {{ !$canEdit ? 'disabled' : '' }}
                                           @change="triggerAutosave()"
                                           class="rounded border-slate-300 text-blue-600 focus:ring-blue-500">
                                </td>

                                <!-- Portofolio -->
                                <td class="td-compact text-center">
                                    <input type="checkbox" name="matriks_bukti[{{ $u->id }}][methods][portofolio]" value="1"
                                           {{ !empty($methods['portofolio']) ? 'checked' : '' }}
                                           {{ !$canEdit ? 'disabled' : '' }}
                                           @change="triggerAutosave()"
                                           class="rounded border-slate-300 text-blue-600 focus:ring-blue-500">
                                </td>

                                <!-- Pernyataan Pihak Ketiga -->
                                <td class="td-compact text-center">
                                    <input type="checkbox" name="matriks_bukti[{{ $u->id }}][methods][pernyataan_pihak_ketiga]" value="1"
                                           {{ !empty($methods['pernyataan_pihak_ketiga']) ? 'checked' : '' }}
                                           {{ !$canEdit ? 'disabled' : '' }}
                                           @change="triggerAutosave()"
                                           class="rounded border-slate-300 text-blue-600 focus:ring-blue-500">
                                </td>

                                <!-- Pertanyaan Wawancara -->
                                <td class="td-compact text-center">
                                    <input type="checkbox" name="matriks_bukti[{{ $u->id }}][methods][pertanyaan_wawancara]" value="1"
                                           {{ !empty($methods['pertanyaan_wawancara']) ? 'checked' : '' }}
                                           {{ !$canEdit ? 'disabled' : '' }}
                                           @change="triggerAutosave()"
                                           class="rounded border-slate-300 text-blue-600 focus:ring-blue-500">
                                </td>

                                <!-- Pertanyaan Lisan -->
                                <td class="td-compact text-center">
                                    <input type="checkbox" name="matriks_bukti[{{ $u->id }}][methods][pertanyaan_lisan]" value="1"
                                           {{ !empty($methods['pertanyaan_lisan']) ? 'checked' : '' }}
                                           {{ !$canEdit ? 'disabled' : '' }}
                                           @change="triggerAutosave()"
                                           class="rounded border-slate-300 text-blue-600 focus:ring-blue-500">
                                </td>

                                <!-- Pertanyaan Tertulis -->
                                <td class="td-compact text-center">
                                    <input type="checkbox" name="matriks_bukti[{{ $u->id }}][methods][pertanyaan_tertulis]" value="1"
                                           {{ !empty($methods['pertanyaan_tertulis']) ? 'checked' : '' }}
                                           {{ !$canEdit ? 'disabled' : '' }}
                                           @change="triggerAutosave()"
                                           class="rounded border-slate-300 text-blue-600 focus:ring-blue-500">
                                </td>

                                <!-- Proyek Kerja -->
                                <td class="td-compact text-center">
                                    <input type="checkbox" name="matriks_bukti[{{ $u->id }}][methods][proyek_kerja]" value="1"
                                           {{ !empty($methods['proyek_kerja']) ? 'checked' : '' }}
                                           {{ !$canEdit ? 'disabled' : '' }}
                                           @change="triggerAutosave()"
                                           class="rounded border-slate-300 text-blue-600 focus:ring-blue-500">
                                </td>

                                <!-- Lainnya -->
                                <td class="td-compact text-center">
                                    <input type="checkbox" name="matriks_bukti[{{ $u->id }}][methods][lainnya]" value="1"
                                           {{ !empty($methods['lainnya']) ? 'checked' : '' }}
                                           {{ !$canEdit ? 'disabled' : '' }}
                                           @change="triggerAutosave()"
                                           class="rounded border-slate-300 text-blue-600 focus:ring-blue-500">
                                </td>

                                <!-- Keputusan Unit K / BK -->
                                <td class="td-compact whitespace-nowrap">
                                    <div class="inline-flex p-0.5 bg-slate-100 rounded-lg border border-slate-200">
                                        <label class="cursor-pointer px-2 py-0.5 rounded text-[10px] font-bold transition-colors flex items-center gap-1"
                                               :class="unitResults['{{ $u->id }}'] === 'K' ? 'bg-emerald-600 text-white shadow-2xs' : 'text-slate-600 hover:text-slate-900'">
                                            <input type="radio" name="rekomendasi_unit[{{ $u->id }}][hasil]" value="K"
                                                   x-model="unitResults['{{ $u->id }}']"
                                                   @change="calculateCounts(); triggerAutosave()"
                                                   {{ !$canEdit ? 'disabled' : '' }}
                                                   class="sr-only">
                                            <span>K</span>
                                        </label>

                                        <label class="cursor-pointer px-2 py-0.5 rounded text-[10px] font-bold transition-colors flex items-center gap-1"
                                               :class="unitResults['{{ $u->id }}'] === 'BK' ? 'bg-rose-600 text-white shadow-2xs' : 'text-slate-600 hover:text-slate-900'">
                                            <input type="radio" name="rekomendasi_unit[{{ $u->id }}][hasil]" value="BK"
                                                   x-model="unitResults['{{ $u->id }}']"
                                                   @change="calculateCounts(); triggerAutosave()"
                                                   {{ !$canEdit ? 'disabled' : '' }}
                                                   class="sr-only">
                                            <span>BK</span>
                                        </label>
                                    </div>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>

        <!-- SECTION 2: REKAPITULASI KEPUTUSAN & TINDAK LANJUT -->
        <div class="grid grid-cols-1 lg:grid-cols-3 gap-4">
            <!-- Left 2 Cols: Rekomendasi, Tindak Lanjut & Komentar -->
            <div class="lg:col-span-2 bg-white rounded-2xl border border-slate-200/90 shadow-2xs p-4 sm:p-5 space-y-4">
                <div class="flex items-center justify-between border-b border-slate-100 pb-2.5">
                    <h3 class="text-xs font-black text-slate-800 uppercase tracking-wider">
                        2. Rekomendasi & Ulasan Asesor
                    </h3>
                    <!-- Calculated Status Pill -->
                    <div class="flex items-center gap-2">
                        <span class="text-xs font-semibold text-slate-500">Hasil Akhir:</span>
                        <span class="px-2.5 py-0.5 rounded-full text-xs font-extrabold"
                              :class="calculatedStatus === 'kompeten' ? 'bg-emerald-100 text-emerald-800' : 'bg-rose-100 text-rose-800'"
                              x-text="calculatedStatus === 'kompeten' ? 'KOMPETEN (K)' : 'BELUM KOMPETEN (BK)'">
                        </span>
                    </div>
                </div>

                <!-- Conditional: Tindak Lanjut yang Dibutuhkan (Wajib Jika BK) -->
                <div x-show="totalBk > 0" class="p-3.5 bg-rose-50/60 border border-rose-200 rounded-xl space-y-1.5">
                    <div class="flex items-center gap-1.5 text-rose-800 font-bold text-xs">
                        <span>Tindak Lanjut yang Dibutuhkan (Wajib diisi karena ada unit BK):</span>
                    </div>
                    <textarea name="tindak_lanjut" rows="3" 
                              {{ !$canEdit ? 'disabled' : '' }}
                              @input="triggerAutosave()"
                              placeholder="Deskripsikan pekerjaan tambahan, bukti tambahan, atau asesmen ulang yang disyaratkan..."
                              class="w-full text-xs rounded-xl border-rose-200 focus:border-rose-500 focus:ring-rose-500 p-2.5 bg-white">{{ old('tindak_lanjut', $ak02->tindak_lanjut) }}</textarea>
                </div>

                <!-- Komentar / Observasi oleh Asesor -->
                <div class="space-y-1.5">
                    <label class="block text-xs font-bold text-slate-700">
                        Komentar / Catatan Observasi oleh Asesor:
                    </label>
                    <textarea name="komentar_asesor" rows="3"
                              {{ !$canEdit ? 'disabled' : '' }}
                              @input="triggerAutosave()"
                              placeholder="Catatan profesional mengenai performa, pemenuhan standar K3, dan aspek unggul asesi..."
                              class="w-full text-xs rounded-xl border-slate-200 focus:border-blue-500 focus:ring-blue-500 p-2.5">{{ old('komentar_asesor', $ak02->komentar_asesor) }}</textarea>
                </div>

                <!-- Linked Documents Information -->
                <div class="pt-2 border-t border-slate-100 space-y-1.5">
                    <span class="text-[10px] font-bold text-slate-400 uppercase tracking-wider block">Dokumen & Bukti Terkait Terintegrasi:</span>
                    <div class="flex items-center gap-2 flex-wrap text-[11px]">
                        <span class="px-2 py-1 rounded-lg bg-slate-100 text-slate-700 font-medium flex items-center">
                            <span>FR.APL.01: Disetujui</span>
                        </span>
                        <span class="px-2 py-1 rounded-lg bg-slate-100 text-slate-700 font-medium flex items-center">
                            <span>FR.APL.02: Terverifikasi</span>
                        </span>
                        <span class="px-2 py-1 rounded-lg bg-slate-100 text-slate-700 font-medium flex items-center">
                            <span>Portofolio & Bukti Uji</span>
                        </span>
                    </div>
                </div>
            </div>

            <!-- Right 1 Col: Score Summary & Counters -->
            <div class="bg-white rounded-2xl border border-slate-200/90 shadow-2xs p-4 sm:p-5 space-y-4">
                <h3 class="text-xs font-black text-slate-800 uppercase tracking-wider border-b border-slate-100 pb-2.5">
                    Ringkasan Hasil Unit
                </h3>

                <div class="space-y-3">
                    <div class="flex items-center justify-between p-3 bg-slate-50 rounded-xl border border-slate-100">
                        <span class="text-xs text-slate-600 font-semibold">Total Unit Kompetensi</span>
                        <span class="font-extrabold text-base text-slate-800" x-text="totalUnits"></span>
                    </div>

                    <div class="flex items-center justify-between p-3 bg-emerald-50/70 rounded-xl border border-emerald-100">
                        <span class="text-xs text-emerald-700 font-semibold">Jumlah Kompeten (K)</span>
                        <span class="font-extrabold text-base text-emerald-700" x-text="totalK"></span>
                    </div>

                    <div class="flex items-center justify-between p-3 bg-rose-50/70 rounded-xl border border-rose-100">
                        <span class="text-xs text-rose-700 font-semibold">Jumlah Belum Kompeten (BK)</span>
                        <span class="font-extrabold text-base text-rose-700" x-text="totalBk"></span>
                    </div>
                </div>

                <div class="p-3 rounded-xl bg-blue-50/60 border border-blue-100 text-slate-600 text-[11px] leading-relaxed">
                    Keputusan asesmen kompeten hanya dapat ditetapkan jika <strong>seluruh unit kompetensi</strong> telah dinyatakan <strong>Kompeten (K)</strong>.
                </div>
            </div>
        </div>
    </form>

    <!-- SECTION 3: TANDA TANGAN DIGITAL DUA BELAH PIHAK -->
    <div class="bg-white rounded-2xl border border-slate-200/90 shadow-2xs p-4 sm:p-5 space-y-4">
        <div class="border-b border-slate-100 pb-2.5">
            <h3 class="text-xs font-black text-slate-800 uppercase tracking-wider">
                3. Pengesahan & Tanda Tangan Digital
            </h3>
            <p class="text-[11px] text-slate-500">
                Persetujuan dan pengesahan hasil asesmen oleh Asesor Kompetensi dan Asesi (Peserta).
            </p>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
            <!-- ASESI SIGNATURE BOX -->
            <div class="border border-slate-200 rounded-2xl p-4 bg-slate-50/50 space-y-3">
                <div class="flex items-center justify-between border-b border-slate-200/80 pb-2">
                    <span class="text-xs font-bold text-slate-800 uppercase tracking-wider">Asesi (Peserta)</span>
                    @if($ak02->tanda_tangan_asesi)
                        <span class="px-2 py-0.5 rounded-full bg-emerald-100 text-emerald-800 text-[10px] font-bold">
                            Telah Ditandatangani
                        </span>
                    @else
                        <span class="px-2 py-0.5 rounded-full bg-amber-100 text-amber-800 text-[10px] font-bold">
                            Menunggu Tanda Tangan
                        </span>
                    @endif
                </div>

                <div class="space-y-1 text-xs">
                    <div class="font-bold text-slate-800">{{ $pendaftaran->asesi->nama_lengkap ?? '-' }}</div>
                    <div class="text-slate-500 text-[11px]">
                        Tanggal: {{ $ak02->tanggal_ttd_asesi ? $ak02->tanggal_ttd_asesi->format('d/m/Y H:i') : '-' }}
                    </div>
                </div>

                <!-- Display Signature or Sign Button -->
                <div class="h-28 border border-dashed border-slate-300 rounded-xl bg-white flex items-center justify-center p-2">
                    @if($ak02->tanda_tangan_asesi)
                        <img src="{{ $ak02->tanda_tangan_asesi }}" alt="Tanda Tangan Asesi" class="max-h-24 object-contain">
                    @else
                        <span class="text-slate-400 text-xs italic">Lembar belum ditandatangani oleh asesi</span>
                    @endif
                </div>

                @if(!$ak02->tanda_tangan_asesi && ($isAsesi || in_array($user->peran, ['admin', 'superadmin'])))
                    <button type="button" @click="openSignModal('asesi')" class="w-full py-2 px-3 rounded-xl bg-blue-600 hover:bg-blue-700 text-white font-bold text-xs transition-colors shadow-2xs">
                        Bubuhkan Tanda Tangan Asesi
                    </button>
                @endif
            </div>

            <!-- ASESOR SIGNATURE BOX -->
            <div class="border border-slate-200 rounded-2xl p-4 bg-slate-50/50 space-y-3">
                <div class="flex items-center justify-between border-b border-slate-200/80 pb-2">
                    <span class="text-xs font-bold text-slate-800 uppercase tracking-wider">Asesor Kompetensi</span>
                    @if($ak02->tanda_tangan_asesor)
                        <span class="px-2 py-0.5 rounded-full bg-emerald-100 text-emerald-800 text-[10px] font-bold">
                            Keputusan Disahkan
                        </span>
                    @else
                        <span class="px-2 py-0.5 rounded-full bg-amber-100 text-amber-800 text-[10px] font-bold">
                            Belum Disahkan
                        </span>
                    @endif
                </div>

                <div class="space-y-1 text-xs">
                    <div class="font-bold text-slate-800">{{ $pendaftaran->asesor->nama_lengkap ?? auth()->user()->nama_lengkap }}</div>
                    <div class="text-slate-500 text-[11px]">
                        No. Reg: {{ $pendaftaran->asesor->nomor_registrasi ?? 'MET.000.004455' }} &bull;
                        {{ $ak02->tanggal_ttd_asesor ? $ak02->tanggal_ttd_asesor->format('d/m/Y H:i') : '-' }}
                    </div>
                </div>

                <div class="h-28 border border-dashed border-slate-300 rounded-xl bg-white flex items-center justify-center p-2">
                    @if($ak02->tanda_tangan_asesor)
                        <img src="{{ $ak02->tanda_tangan_asesor }}" alt="Tanda Tangan Asesor" class="max-h-24 object-contain">
                    @else
                        <span class="text-slate-400 text-xs italic">Menunggu tanda tangan pengesahan keputusan</span>
                    @endif
                </div>

                @if(!$ak02->tanda_tangan_asesor && $isAsesor)
                    <button type="button" @click="openSignModal('asesor')" class="w-full py-2 px-3 rounded-xl bg-slate-900 hover:bg-slate-800 text-white font-bold text-xs transition-colors shadow-2xs">
                        Sahkan Keputusan & Tanda Tangan
                    </button>
                @endif
            </div>
        </div>
    </div>

    <!-- MODAL SIGNATURE PAD -->
    <div x-show="signModalOpen" class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-slate-900/60 backdrop-blur-xs" style="display: none;">
        <div class="bg-white rounded-2xl border border-slate-200 shadow-2xl max-w-md w-full p-5 space-y-4" @click.outside="signModalOpen = false">
            <div class="flex items-center justify-between border-b border-slate-100 pb-3">
                <h4 class="font-bold text-sm text-slate-800">Tanda Tangan Digital</h4>
                <button type="button" @click="signModalOpen = false" class="text-slate-400 hover:text-slate-600 text-xs font-semibold px-2 py-1 rounded-lg hover:bg-slate-50">
                    Tutup
                </button>
            </div>

            <div class="space-y-2">
                <p class="text-xs text-slate-500">Torehkan tanda tangan Anda pada kanvas di bawah ini:</p>
                <div class="border border-slate-300 rounded-xl overflow-hidden bg-white">
                    <canvas id="signatureCanvas" width="400" height="180" class="w-full h-44 cursor-crosshair touch-none"></canvas>
                </div>
                <div class="flex items-center justify-between text-xs">
                    <button type="button" @click="clearSignaturePad()" class="text-rose-600 hover:underline">
                        Bersihkan Kanvas
                    </button>
                    @if(auth()->user()->peran === 'asesi' && auth()->user()->tanda_tangan)
                        <button type="button" @click="useProfileSignature()" class="text-blue-600 hover:underline">
                            Gunakan Tanda Tangan Profil
                        </button>
                    @endif
                </div>
            </div>

            <form id="signForm" method="POST" :action="signActionUrl">
                @csrf
                <input type="hidden" name="signature" id="signatureInput" value="">
                <div class="flex items-center gap-2 pt-2 border-t border-slate-100">
                    <button type="button" @click="signModalOpen = false" class="flex-1 py-2 rounded-xl bg-slate-100 text-slate-700 font-bold text-xs hover:bg-slate-200">
                        Batal
                    </button>
                    <button type="button" @click="submitSignature()" class="flex-1 py-2 rounded-xl bg-blue-600 text-white font-bold text-xs hover:bg-blue-700 shadow-2xs">
                        Konfirmasi & Sahkan
                    </button>
                </div>
            </form>
        </div>
    </div>

    <!-- MODAL REOPEN REVISION -->
    <div x-show="openReopenModal" class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-slate-900/60 backdrop-blur-xs" style="display: none;">
        <div class="bg-white rounded-2xl border border-slate-200 shadow-2xl max-w-md w-full p-5 space-y-4" @click.outside="openReopenModal = false">
            <div class="border-b border-slate-100 pb-2">
                <h4 class="font-bold text-sm text-slate-800">Buka Kembali Dokumen (Reopen)</h4>
                <p class="text-xs text-slate-500">Membuka kembali dokumen final akan menaikkan nomor revisi versi baru.</p>
            </div>

            <form method="POST" action="{{ route('dokumen-asesmen.ak02.reopen', $pendaftaran->id) }}" class="space-y-3">
                @csrf
                <div>
                    <label class="block text-xs font-bold text-slate-700 mb-1">Alasan Pembukaan Kembali:</label>
                    <textarea name="alasan_revisi" rows="3" required placeholder="Contoh: Koreksi catatan observasi unit atau tindak lanjut asesmen..."
                              class="w-full text-xs rounded-xl border-slate-200 focus:border-blue-500 focus:ring-blue-500 p-2.5"></textarea>
                </div>

                <div class="flex items-center gap-2 pt-2 border-t border-slate-100">
                    <button type="button" @click="openReopenModal = false" class="flex-1 py-2 rounded-xl bg-slate-100 text-slate-700 font-bold text-xs">
                        Batal
                    </button>
                    <button type="submit" class="flex-1 py-2 rounded-xl bg-amber-600 text-white font-bold text-xs hover:bg-amber-700">
                        Buka Dokumen
                    </button>
                </div>
            </form>
        </div>
    </div>

</div>

@push('css')
<script>
    function ak02FormApp() {
        return {
            unitResults: @json(collect($units)->mapWithKeys(fn($u) => [$u->id => strtoupper(is_array($rekomendasiSaved[$u->id] ?? null) ? ($rekomendasiSaved[$u->id]['hasil'] ?? 'K') : ($rekomendasiSaved[$u->id] ?? 'K'))])),
            totalUnits: {{ $units->count() }},
            totalK: {{ $ak02->total_k ?? $units->count() }},
            totalBk: {{ $ak02->total_bk ?? 0 }},
            calculatedStatus: '{{ $ak02->keputusan_final ?? "kompeten" }}',
            isSaving: false,
            saveStatusText: 'Tersimpan otomatis',
            autosaveTimer: null,
            signModalOpen: false,
            openReopenModal: false,
            signRole: 'asesor',
            signActionUrl: '',
            signaturePad: null,

            init() {
                this.calculateCounts();
            },

            calculateCounts() {
                let k = 0;
                let bk = 0;
                for (let key in this.unitResults) {
                    if (this.unitResults[key] === 'K') k++;
                    else if (this.unitResults[key] === 'BK') bk++;
                }
                this.totalK = k;
                this.totalBk = bk;
                this.calculatedStatus = (bk === 0 && k === this.totalUnits) ? 'kompeten' : 'belum_kompeten';
            },

            triggerAutosave() {
                clearTimeout(this.autosaveTimer);
                this.saveStatusText = 'Menyimpan...';
                this.isSaving = true;

                this.autosaveTimer = setTimeout(() => {
                    const form = document.getElementById('ak02Form');
                    const formData = new FormData(form);

                    fetch("{{ route('dokumen-asesmen.ak02.autosave', $pendaftaran->id) }}", {
                        method: 'POST',
                        headers: {
                            'X-CSRF-TOKEN': '{{ csrf_token() }}',
                            'Accept': 'application/json',
                        },
                        body: formData
                    })
                    .then(res => res.json())
                    .then(data => {
                        this.isSaving = false;
                        this.saveStatusText = 'Tersimpan otomatis pk ' + data.saved_at;
                    })
                    .catch(() => {
                        this.isSaving = false;
                        this.saveStatusText = 'Gagal menyimpan draf';
                    });
                }, 1200);
            },

            manualSaveDraft() {
                document.getElementById('ak02Form').submit();
            },

            openSignModal(role) {
                this.signRole = role;
                this.signActionUrl = role === 'asesor' 
                    ? "{{ route('dokumen-asesmen.ak02.sign-asesor', $pendaftaran->id) }}"
                    : "{{ route('dokumen-asesmen.ak02.sign-asesi', $pendaftaran->id) }}";
                this.signModalOpen = true;

                this.$nextTick(() => {
                    const canvas = document.getElementById('signatureCanvas');
                    if (canvas) {
                        this.signaturePad = new SignaturePad(canvas, {
                            backgroundColor: 'rgb(255, 255, 255)',
                            penColor: 'rgb(15, 23, 42)',
                        });
                    }
                });
            },

            clearSignaturePad() {
                if (this.signaturePad) {
                    this.signaturePad.clear();
                }
            },

            useProfileSignature() {
                document.getElementById('signatureInput').value = "{{ auth()->user()->tanda_tangan }}";
                document.getElementById('signForm').submit();
            },

            submitSignature() {
                if (this.signaturePad && !this.signaturePad.isEmpty()) {
                    document.getElementById('signatureInput').value = this.signaturePad.toDataURL();
                }
                document.getElementById('signForm').submit();
            }
        };
    }
</script>
@endpush
@endsection
