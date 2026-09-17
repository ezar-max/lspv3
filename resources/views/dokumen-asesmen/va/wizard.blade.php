@extends('tata-letak.dasbor')

@section('judul', 'FR.VA - Wizard Validasi Asesmen ' . $va->nomor_validasi)

@push('css')
    <!-- Signature Pad CDN -->
    <script src="https://cdn.jsdelivr.net/npm/signature_pad@4.1.7/dist/signature_pad.umd.min.js"></script>
@endpush

@section('konten')
@php
    $user = auth()->user();
    $isFinal = $va->isFinalized();
    $canEdit = in_array($user->peran, ['asesor', 'admin', 'superadmin']) && !$isFinal;

    $periodeVal = (array) ($va->periode_validasi ?? []);
    $tujuanFokus = (array) ($va->tujuan_fokus ?? []);
    $konteksVal = (array) ($va->konteks_validasi ?? []);
    $pendekatanVal = (array) ($va->pendekatan_validasi ?? []);
    $pesertaList = (array) ($va->peserta_relevan ?? []);
    $acuanPembanding = (array) ($va->acuan_pembanding ?? []);
    $dokumenTerkait = (array) ($va->dokumen_terkait ?? []);
    $komunikasi = (array) ($va->keterampilan_komunikasi ?? []);
    $matriksPenilaian = (array) ($va->matriks_penilaian ?? []);
    $temuanList = (array) ($va->temuan_validasi ?? []);
    $rencanaList = (array) ($va->rencana_perbaikan ?? []);
@endphp

<div class="space-y-4" x-data="vaWizardApp()" x-cloak>

    <!-- TOP BREADCRUMB & HEADER -->
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3 border-b border-slate-200/80 pb-3">
        <div>
            <div class="flex items-center gap-2 text-xs text-slate-500 mb-1">
                <a href="{{ route('dokumen-asesmen.index') }}" class="hover:text-blue-600">Dokumen Asesmen</a>
                <span>/</span>
                <a href="{{ route('dokumen-asesmen.va.index') }}" class="hover:text-blue-600">FR.VA</a>
                <span>/</span>
                <span class="text-slate-800 font-bold font-mono">{{ $va->nomor_validasi }}</span>
            </div>
            <h1 class="text-base sm:text-lg font-black text-slate-900 tracking-tight flex items-center gap-2">
                <span>FR.VA &bull; Memberikan Kontribusi dalam Validasi Asesmen</span>
                <span class="text-xs px-2 py-0.5 rounded-full font-bold uppercase tracking-wider {{ $va->status === 'final' ? 'bg-emerald-100 text-emerald-800 border border-emerald-200' : 'bg-teal-100 text-teal-800 border border-teal-200' }}">
                    {{ $va->status === 'final' ? 'Final / Terkunci' : 'Dalam Pengisian' }}
                </span>
                <span class="text-xs px-2 py-0.5 rounded-full bg-slate-100 text-slate-700 font-mono font-bold">v{{ $va->version }}</span>
            </h1>
        </div>

        <div class="flex items-center gap-2 flex-wrap">
            <a href="{{ route('dokumen-asesmen.va.cetak', $va->id) }}" target="_blank" class="inline-flex items-center px-3 py-1.5 rounded-xl border border-slate-200 bg-white text-slate-700 font-semibold text-xs hover:bg-slate-50 transition-colors shadow-2xs">
                <span>Cetak A4</span>
            </a>

            <!-- AUTOSAVE INDICATOR -->
            <div class="text-[11px] text-slate-500 flex items-center gap-1.5 px-2.5 py-1 bg-slate-50 rounded-lg border border-slate-200">
                <span class="w-2 h-2 rounded-full" :class="isSaving ? 'bg-amber-400 animate-ping' : 'bg-emerald-500'"></span>
                <span x-text="autoSaveText">Tersimpan</span>
            </div>
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

    <!-- 7-STEP WIZARD STEPPER BAR -->
    <div class="bg-white rounded-2xl border border-slate-200/90 shadow-2xs p-3">
        <div class="grid grid-cols-2 sm:grid-cols-4 lg:grid-cols-7 gap-1 text-center">
            <template x-for="(st, idx) in steps" :key="idx">
                <button type="button" @click="goToStep(idx + 1)" :disabled="!canEdit && currentStep !== (idx + 1)"
                    class="p-2 rounded-xl text-xs transition-all flex flex-col items-center justify-center gap-1"
                    :class="currentStep === (idx + 1) ? 'bg-teal-600 text-white font-bold shadow-2xs' : (currentStep > (idx + 1) ? 'bg-teal-50 text-teal-800 hover:bg-teal-100 font-semibold' : 'text-slate-400 hover:bg-slate-50')">
                    <div class="w-5 h-5 rounded-full flex items-center justify-center text-[10px] font-mono"
                        :class="currentStep === (idx + 1) ? 'bg-white text-teal-700 font-bold' : (currentStep > (idx + 1) ? 'bg-teal-200 text-teal-900 font-bold' : 'bg-slate-200 text-slate-500')">
                        <span x-text="idx + 1"></span>
                    </div>
                    <span class="text-[10px] leading-tight" x-text="st.title"></span>
                </button>
            </template>
        </div>
    </div>

    <!-- MAIN FORM CONTAINER -->
    <form id="form-va" method="POST" action="{{ route('dokumen-asesmen.va.save-step', $va->id) }}" class="space-y-4">
        @csrf
        <input type="hidden" name="step" :value="currentStep">
        <input type="hidden" name="finalize" id="input-finalize" value="0">
        <input type="hidden" name="signature" id="input-signature" value="">

        <!-- ================================================================= -->
        <!-- STEP 1: INFORMASI VALIDASI -->
        <!-- ================================================================= -->
        <div x-show="currentStep === 1" class="bg-white rounded-2xl border border-slate-200/90 shadow-2xs p-5 space-y-4">
            <div class="border-b border-slate-100 pb-3">
                <h2 class="text-sm font-bold text-slate-900 flex items-center gap-2">
                    <span class="w-6 h-6 rounded-lg bg-teal-600 text-white text-xs flex items-center justify-center font-bold">1</span>
                    <span>Informasi Umum Kegiatan Validasi</span>
                </h2>
                <p class="text-xs text-slate-500 mt-0.5">Identitas dokumen, skema uji yang divalidasi, jadwal, serta periode pelaksanaan validasi.</p>
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 text-xs">
                <div>
                    <label class="block font-bold text-slate-700 mb-1">Skema Sertifikasi (Otomatis)</label>
                    <input type="text" value="{{ $va->skema->nama_skema ?? '-' }}" readonly class="w-full text-xs p-2.5 rounded-xl border border-slate-200 bg-slate-50 text-slate-700 font-bold">
                    <span class="text-[10px] text-slate-400 block mt-0.5">Kode Skema: {{ $va->skema->kode_skema ?? '-' }}</span>
                </div>

                <div>
                    <label class="block font-bold text-slate-700 mb-1">Ketua Tim / Lead Asesor</label>
                    <input type="text" value="{{ $va->leadAsesor->nama_lengkap ?? '-' }}" readonly class="w-full text-xs p-2.5 rounded-xl border border-slate-200 bg-slate-50 text-slate-700 font-bold">
                    <span class="text-[10px] text-slate-400 block mt-0.5">No. Reg MET: {{ $va->leadAsesor->nomor_registrasi ?? 'MET.000.003344' }}</span>
                </div>

                <div>
                    <label class="block font-bold text-slate-700 mb-1">Tanggal Kegiatan Validasi *</label>
                    <input type="date" name="tanggal_validasi" value="{{ $va->tanggal_validasi ? $va->tanggal_validasi->format('Y-m-d') : date('Y-m-d') }}" class="w-full text-xs p-2.5 rounded-xl border border-slate-200 focus:ring-1 focus:ring-teal-500 bg-white" {{ !$canEdit ? 'readonly' : '' }}>
                </div>

                <div>
                    <label class="block font-bold text-slate-700 mb-1">Tempat Pelaksanaan Validasi *</label>
                    <input type="text" name="tempat_validasi" value="{{ $va->tempat_validasi ?: 'TUK Mandiri SMKN 1 Gunungputri' }}" class="w-full text-xs p-2.5 rounded-xl border border-slate-200 focus:ring-1 focus:ring-teal-500 bg-white" {{ !$canEdit ? 'readonly' : '' }}>
                </div>
            </div>

            <!-- PERIODE VALIDASI (CHECKBOXES) -->
            <div class="border-t border-slate-100 pt-3">
                <label class="block font-bold text-slate-800 text-xs uppercase tracking-wider mb-2">Periode Pelaksanaan Validasi Asesmen *</label>
                <div class="grid grid-cols-1 sm:grid-cols-3 gap-3">
                    <label class="flex items-center gap-2.5 p-3 rounded-xl border border-slate-200 bg-slate-50/60 cursor-pointer hover:bg-slate-50">
                        <input type="checkbox" name="periode_validasi[]" value="sebelum_asesmen" {{ in_array('sebelum_asesmen', $periodeVal) ? 'checked' : '' }} class="rounded text-teal-600 focus:ring-teal-500" {{ !$canEdit ? 'disabled' : '' }}>
                        <div>
                            <span class="text-xs font-bold text-slate-800 block">Sebelum Asesmen</span>
                            <span class="text-[10px] text-slate-500">Uji coba perangkat & instrumen asesmen</span>
                        </div>
                    </label>

                    <label class="flex items-center gap-2.5 p-3 rounded-xl border border-slate-200 bg-slate-50/60 cursor-pointer hover:bg-slate-50">
                        <input type="checkbox" name="periode_validasi[]" value="pada_saat_asesmen" {{ in_array('pada_saat_asesmen', $periodeVal) ? 'checked' : '' }} class="rounded text-teal-600 focus:ring-teal-500" {{ !$canEdit ? 'disabled' : '' }}>
                        <div>
                            <span class="text-xs font-bold text-slate-800 block">Pada Saat Asesmen</span>
                            <span class="text-[10px] text-slate-500">Pengawasan langsung proses pengujian</span>
                        </div>
                    </label>

                    <label class="flex items-center gap-2.5 p-3 rounded-xl border border-slate-200 bg-slate-50/60 cursor-pointer hover:bg-slate-50">
                        <input type="checkbox" name="periode_validasi[]" value="setelah_asesmen" {{ in_array('setelah_asesmen', $periodeVal) ? 'checked' : '' }} class="rounded text-teal-600 focus:ring-teal-500" {{ !$canEdit ? 'disabled' : '' }}>
                        <div>
                            <span class="text-xs font-bold text-slate-800 block">Setelah Asesmen</span>
                            <span class="text-[10px] text-slate-500">Kaji ulang bukti & konsistensi keputusan</span>
                        </div>
                    </label>
                </div>
            </div>
        </div>

        <!-- ================================================================= -->
        <!-- STEP 2: MENYIAPKAN PROSES VALIDASI -->
        <!-- ================================================================= -->
        <div x-show="currentStep === 2" class="bg-white rounded-2xl border border-slate-200/90 shadow-2xs p-5 space-y-4">
            <div class="border-b border-slate-100 pb-3">
                <h2 class="text-sm font-bold text-slate-900 flex items-center gap-2">
                    <span class="w-6 h-6 rounded-lg bg-teal-600 text-white text-xs flex items-center justify-center font-bold">2</span>
                    <span>Menyiapkan Proses Validasi & Peserta Relevan</span>
                </h2>
                <p class="text-xs text-slate-500 mt-0.5">Tentukan fokus/tujuan, konteks, pendekatan validasi, serta konfirmasi peserta yang terlibat.</p>
            </div>

            <!-- TUJUAN / FOKUS -->
            <div class="space-y-2">
                <label class="block font-bold text-slate-800 text-xs uppercase tracking-wider">Tujuan / Fokus Validasi</label>
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-2 text-xs">
                    <label class="flex items-center gap-2 p-2.5 rounded-xl border border-slate-100 bg-slate-50/50">
                        <input type="checkbox" name="tujuan_fokus[penjaminan_mutu]" value="1" {{ !empty($tujuanFokus['penjaminan_mutu']) ? 'checked' : '' }} class="rounded text-teal-600 focus:ring-teal-500" {{ !$canEdit ? 'disabled' : '' }}>
                        <span>Penjaminan mutu pelaksanaan asesmen</span>
                    </label>
                    <label class="flex items-center gap-2 p-2.5 rounded-xl border border-slate-100 bg-slate-50/50">
                        <input type="checkbox" name="tujuan_fokus[mengantisipasi_risiko]" value="1" {{ !empty($tujuanFokus['mengantisipasi_risiko']) ? 'checked' : '' }} class="rounded text-teal-600 focus:ring-teal-500" {{ !$canEdit ? 'disabled' : '' }}>
                        <span>Mengantisipasi risiko ketidaksesuaian asesmen</span>
                    </label>
                    <label class="flex items-center gap-2 p-2.5 rounded-xl border border-slate-100 bg-slate-50/50">
                        <input type="checkbox" name="tujuan_fokus[memenuhi_bnsp]" value="1" {{ !empty($tujuanFokus['memenuhi_bnsp']) ? 'checked' : '' }} class="rounded text-teal-600 focus:ring-teal-500" {{ !$canEdit ? 'disabled' : '' }}>
                        <span>Memenuhi persyaratan kepatuhan regulasi BNSP</span>
                    </label>
                    <label class="flex items-center gap-2 p-2.5 rounded-xl border border-slate-100 bg-slate-50/50">
                        <input type="checkbox" name="tujuan_fokus[kesesuaian_bukti]" value="1" {{ !empty($tujuanFokus['kesesuaian_bukti']) ? 'checked' : '' }} class="rounded text-teal-600 focus:ring-teal-500" {{ !$canEdit ? 'disabled' : '' }}>
                        <span>Memastikan keabsahan bukti & keputusan K/BK</span>
                    </label>
                </div>
            </div>

            <!-- KONTEKS & PENDEKATAN -->
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 border-t border-slate-100 pt-3">
                <div>
                    <label class="block font-bold text-slate-800 text-xs uppercase tracking-wider mb-1.5">Konteks Validasi</label>
                    <div class="space-y-1.5 text-xs">
                        <label class="flex items-center gap-2">
                            <input type="checkbox" name="konteks_validasi[internal]" value="1" {{ !empty($konteksVal['internal']) ? 'checked' : '' }} class="rounded text-teal-600" {{ !$canEdit ? 'disabled' : '' }}>
                            <span>Internal LSP-P1 SMKN 1 Gunungputri</span>
                        </label>
                        <label class="flex items-center gap-2">
                            <input type="checkbox" name="konteks_validasi[dengan_kolega]" value="1" {{ !empty($konteksVal['dengan_kolega']) ? 'checked' : '' }} class="rounded text-teal-600" {{ !$canEdit ? 'disabled' : '' }}>
                            <span>Validasi bersama kolega asesor sejawat</span>
                        </label>
                        <label class="flex items-center gap-2">
                            <input type="checkbox" name="konteks_validasi[lintas_tuk]" value="1" {{ !empty($konteksVal['lintas_tuk']) ? 'checked' : '' }} class="rounded text-teal-600" {{ !$canEdit ? 'disabled' : '' }}>
                            <span>Lintas Tempat Uji Kompetensi (TUK)</span>
                        </label>
                    </div>
                </div>

                <div>
                    <label class="block font-bold text-slate-800 text-xs uppercase tracking-wider mb-1.5">Pendekatan Validasi</label>
                    <div class="space-y-1.5 text-xs">
                        <label class="flex items-center gap-2">
                            <input type="checkbox" name="pendekatan_validasi[panel_asesmen]" value="1" {{ !empty($pendekatanVal['panel_asesmen']) ? 'checked' : '' }} class="rounded text-teal-600" {{ !$canEdit ? 'disabled' : '' }}>
                            <span>Panel telaah asesmen (Peer review)</span>
                        </label>
                        <label class="flex items-center gap-2">
                            <input type="checkbox" name="pendekatan_validasi[mengkaji_perangkat]" value="1" {{ !empty($pendekatanVal['mengkaji_perangkat']) ? 'checked' : '' }} class="rounded text-teal-600" {{ !$canEdit ? 'disabled' : '' }}>
                            <span>Mengkaji instrumen & materi uji (MUK)</span>
                        </label>
                        <label class="flex items-center gap-2">
                            <input type="checkbox" name="pendekatan_validasi[mengkaji_bukti]" value="1" {{ !empty($pendekatanVal['mengkaji_bukti']) ? 'checked' : '' }} class="rounded text-teal-600" {{ !$canEdit ? 'disabled' : '' }}>
                            <span>Mengkaji sampling berkas portofolio asesi</span>
                        </label>
                    </div>
                </div>
            </div>

            <!-- PESERTA RELEVAN (REPEATABLE TABLE) -->
            <div class="border-t border-slate-100 pt-3">
                <div class="flex items-center justify-between mb-2">
                    <label class="font-bold text-slate-800 text-xs uppercase tracking-wider">Orang yang Relevan Terlibat dalam Validasi</label>
                    @if($canEdit)
                        <button type="button" @click="addPeserta()" class="inline-flex items-center px-2.5 py-1 rounded-lg bg-teal-50 text-teal-700 hover:bg-teal-100 font-bold text-xs border border-teal-200">
                            <span>Tambah Peserta</span>
                        </button>
                    @endif
                </div>

                <div class="overflow-x-auto">
                    <table class="w-full text-left text-xs border-collapse">
                        <thead>
                            <tr class="bg-slate-100/75 text-slate-700 font-bold border-b border-slate-200">
                                <th class="py-2 px-2.5 w-8 text-center">No</th>
                                <th class="py-2 px-2.5 w-44">Peran Peserta</th>
                                <th class="py-2 px-2.5 w-48">Nama Lengkap</th>
                                <th class="py-2 px-2.5">Hasil Konfirmasi</th>
                                <th class="py-2 px-2.5">Tujuan Keterlibatan</th>
                                @if($canEdit)
                                    <th class="py-2 px-2 w-10 text-center">Aksi</th>
                                @endif
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100">
                            <template x-for="(p, pIdx) in pesertaList" :key="pIdx">
                                <tr>
                                    <td class="py-2 px-2 text-center text-slate-400 font-mono" x-text="pIdx + 1"></td>
                                    <td class="py-1.5 px-2">
                                        <select :name="'peserta_relevan[' + pIdx + '][peran]'" x-model="p.peran" class="w-full text-[11px] font-semibold py-1 px-1.5 rounded-lg border border-slate-200 bg-white" {{ !$canEdit ? 'disabled' : '' }}>
                                            @foreach(App\Models\AssessmentVa::PARTICIPANT_ROLES as $rKey => $rLabel)
                                                <option value="{{ $rKey }}">{{ $rLabel }}</option>
                                            @endforeach
                                        </select>
                                    </td>
                                    <td class="py-1.5 px-2">
                                        <input type="text" :name="'peserta_relevan[' + pIdx + '][nama]'" x-model="p.nama" placeholder="Nama lengkap..." class="w-full text-xs py-1 px-2 rounded-lg border border-slate-200 bg-white" {{ !$canEdit ? 'readonly' : '' }}>
                                    </td>
                                    <td class="py-1.5 px-2">
                                        <input type="text" :name="'peserta_relevan[' + pIdx + '][hasil_konfirmasi]'" x-model="p.hasil_konfirmasi" placeholder="Konfirmasi..." class="w-full text-xs py-1 px-2 rounded-lg border border-slate-200 bg-white" {{ !$canEdit ? 'readonly' : '' }}>
                                    </td>
                                    <td class="py-1.5 px-2">
                                        <input type="text" :name="'peserta_relevan[' + pIdx + '][tujuan]'" x-model="p.tujuan" placeholder="Tujuan..." class="w-full text-xs py-1 px-2 rounded-lg border border-slate-200 bg-white" {{ !$canEdit ? 'readonly' : '' }}>
                                    </td>
                                    @if($canEdit)
                                        <td class="py-1.5 px-2 text-center">
                                            <button type="button" @click="removePeserta(pIdx)" class="text-[11px] font-bold text-rose-600 hover:text-rose-800 p-1">
                                                Hapus
                                            </button>
                                        </td>
                                    @endif
                                </tr>
                            </template>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <!-- ================================================================= -->
        <!-- STEP 3: ACUAN PEMBANDING & DOKUMEN TERKAIT -->
        <!-- ================================================================= -->
        <div x-show="currentStep === 3" class="bg-white rounded-2xl border border-slate-200/90 shadow-2xs p-5 space-y-4">
            <div class="border-b border-slate-100 pb-3">
                <h2 class="text-sm font-bold text-slate-900 flex items-center gap-2">
                    <span class="w-6 h-6 rounded-lg bg-teal-600 text-white text-xs flex items-center justify-center font-bold">3</span>
                    <span>Acuan Pembanding & Dokumen Terkait</span>
                </h2>
                <p class="text-xs text-slate-500 mt-0.5">Pilih dokumen regulasi, standar kompetensi, dan instrumen yang digunakan sebagai acuan validasi.</p>
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                <div class="space-y-2">
                    <label class="block font-bold text-slate-800 text-xs uppercase tracking-wider">Acuan Pembanding Digunakan</label>
                    <div class="space-y-2 text-xs">
                        <label class="flex items-center gap-2.5 p-2.5 rounded-xl border border-slate-100 bg-slate-50/50">
                            <input type="checkbox" name="acuan_pembanding[standar_kompetensi]" value="1" {{ !empty($acuanPembanding['standar_kompetensi']) ? 'checked' : '' }} class="rounded text-teal-600" {{ !$canEdit ? 'disabled' : '' }}>
                            <span>Standar Kompetensi Kerja Nasional Indonesia (SKKNI)</span>
                        </label>
                        <label class="flex items-center gap-2.5 p-2.5 rounded-xl border border-slate-100 bg-slate-50/50">
                            <input type="checkbox" name="acuan_pembanding[skema_sertifikasi]" value="1" {{ !empty($acuanPembanding['skema_sertifikasi']) ? 'checked' : '' }} class="rounded text-teal-600" {{ !$canEdit ? 'disabled' : '' }}>
                            <span>Skema Sertifikasi Terlisensi BNSP</span>
                        </label>
                        <label class="flex items-center gap-2.5 p-2.5 rounded-xl border border-slate-100 bg-slate-50/50">
                            <input type="checkbox" name="acuan_pembanding[sop_ik]" value="1" {{ !empty($acuanPembanding['sop_ik']) ? 'checked' : '' }} class="rounded text-teal-600" {{ !$canEdit ? 'disabled' : '' }}>
                            <span>SOP / Instruksi Kerja (IK) Industri & Sekolah</span>
                        </label>
                        <label class="flex items-center gap-2.5 p-2.5 rounded-xl border border-slate-100 bg-slate-50/50">
                            <input type="checkbox" name="acuan_pembanding[standar_kinerja]" value="1" {{ !empty($acuanPembanding['standar_kinerja']) ? 'checked' : '' }} class="rounded text-teal-600" {{ !$canEdit ? 'disabled' : '' }}>
                            <span>Standar Kinerja & K3 Tempat Kerja</span>
                        </label>
                    </div>
                </div>

                <div class="space-y-2">
                    <label class="block font-bold text-slate-800 text-xs uppercase tracking-wider">Dokumen Terkait yang Dikaji</label>
                    <div class="space-y-2 text-xs">
                        <label class="flex items-center gap-2.5 p-2.5 rounded-xl border border-slate-100 bg-slate-50/50">
                            <input type="checkbox" name="dokumen_terkait[perangkat_asesmen]" value="1" {{ !empty($dokumenTerkait['perangkat_asesmen']) ? 'checked' : '' }} class="rounded text-teal-600" {{ !$canEdit ? 'disabled' : '' }}>
                            <span>Perangkat Asesmen (FR.IA.01, 02, 03, 05, 06, 07)</span>
                        </label>
                        <label class="flex items-center gap-2.5 p-2.5 rounded-xl border border-slate-100 bg-slate-50/50">
                            <input type="checkbox" name="dokumen_terkait[peraturan_pedoman]" value="1" {{ !empty($dokumenTerkait['peraturan_pedoman']) ? 'checked' : '' }} class="rounded text-teal-600" {{ !$canEdit ? 'disabled' : '' }}>
                            <span>Pedoman BNSP & Panduan Mutu LSP</span>
                        </label>
                        <label class="flex items-center gap-2.5 p-2.5 rounded-xl border border-slate-100 bg-slate-50/50">
                            <input type="checkbox" name="dokumen_terkait[bukti_asesmen]" value="1" {{ !empty($dokumenTerkait['bukti_asesmen']) ? 'checked' : '' }} class="rounded text-teal-600" {{ !$canEdit ? 'disabled' : '' }}>
                            <span>Berkas Bukti Hasil Asesmen Peserta (Sampling)</span>
                        </label>
                        <label class="flex items-center gap-2.5 p-2.5 rounded-xl border border-slate-100 bg-slate-50/50">
                            <input type="checkbox" name="dokumen_terkait[lainnya]" value="1" {{ !empty($dokumenTerkait['lainnya']) ? 'checked' : '' }} class="rounded text-teal-600" {{ !$canEdit ? 'disabled' : '' }}>
                            <span>Dokumen Teknis & Manual Book Terkait</span>
                        </label>
                    </div>
                </div>
            </div>
        </div>

        <!-- ================================================================= -->
        <!-- STEP 4: KONTRIBUSI DALAM VALIDASI -->
        <!-- ================================================================= -->
        <div x-show="currentStep === 4" class="bg-white rounded-2xl border border-slate-200/90 shadow-2xs p-5 space-y-4">
            <div class="border-b border-slate-100 pb-3">
                <h2 class="text-sm font-bold text-slate-900 flex items-center gap-2">
                    <span class="w-6 h-6 rounded-lg bg-teal-600 text-white text-xs flex items-center justify-center font-bold">4</span>
                    <span>Keterampilan Komunikasi & Kontribusi dalam Validasi</span>
                </h2>
                <p class="text-xs text-slate-500 mt-0.5">Penilaian penerapan komunikasi aktif, negosiasi yang membangun, serta catatan kontribusi tim.</p>
            </div>

            <div class="space-y-3">
                <label class="block font-bold text-slate-800 text-xs uppercase tracking-wider">Keterampilan Komunikasi & Negosiasi Diterapkan</label>
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-2 text-xs">
                    <label class="flex items-center gap-2 p-2.5 rounded-xl border border-slate-100 bg-slate-50/50">
                        <input type="checkbox" name="keterampilan_komunikasi[proaktif]" value="1" {{ !empty($komunikasi['proaktif']) ? 'checked' : '' }} class="rounded text-teal-600" {{ !$canEdit ? 'disabled' : '' }}>
                        <span>Menunjukkan inisiatif proaktif dalam diskusi validasi</span>
                    </label>
                    <label class="flex items-center gap-2 p-2.5 rounded-xl border border-slate-100 bg-slate-50/50">
                        <input type="checkbox" name="keterampilan_komunikasi[active_listening]" value="1" {{ !empty($komunikasi['active_listening']) ? 'checked' : '' }} class="rounded text-teal-600" {{ !$canEdit ? 'disabled' : '' }}>
                        <span>Mendengarkan secara aktif masukan anggota panel</span>
                    </label>
                    <label class="flex items-center gap-2 p-2.5 rounded-xl border border-slate-100 bg-slate-50/50">
                        <input type="checkbox" name="keterampilan_komunikasi[empati]" value="1" {{ !empty($komunikasi['empati']) ? 'checked' : '' }} class="rounded text-teal-600" {{ !$canEdit ? 'disabled' : '' }}>
                        <span>Menunjukkan empati dan sikap profesional konstruktif</span>
                    </label>
                    <label class="flex items-center gap-2 p-2.5 rounded-xl border border-slate-100 bg-slate-50/50">
                        <input type="checkbox" name="keterampilan_komunikasi[negosiasi_kesepakatan]" value="1" {{ !empty($komunikasi['negosiasi_kesepakatan']) ? 'checked' : '' }} class="rounded text-teal-600" {{ !$canEdit ? 'disabled' : '' }}>
                        <span>Mencapai konsensus dan kesepakatan solusi perbaikan</span>
                    </label>
                </div>
            </div>

            <div class="border-t border-slate-100 pt-3">
                <label class="block font-bold text-slate-800 text-xs uppercase tracking-wider mb-1.5">Catatan Kontribusi Individu / Tim dalam Validasi</label>
                <textarea name="catatan_kontribusi" rows="3" placeholder="Uraikan bagaimana peserta validasi memberikan kontribusi nyata dalam peninjauan instrumen atau prosedur..." class="w-full text-xs p-3 rounded-xl border border-slate-200 focus:ring-1 focus:ring-teal-500" {{ !$canEdit ? 'readonly' : '' }}>{{ $va->catatan_kontribusi }}</textarea>
            </div>
        </div>

        <!-- ================================================================= -->
        <!-- STEP 5: PENILAIAN 8 ASPEK VALIDASI (MATRIKS VATM & VRFA) -->
        <!-- ================================================================= -->
        <div x-show="currentStep === 5" class="bg-white rounded-2xl border border-slate-200/90 shadow-2xs overflow-hidden">
            <div class="px-5 py-3.5 bg-slate-50 border-b border-slate-200 flex items-center justify-between">
                <div>
                    <h2 class="text-sm font-bold text-slate-900 flex items-center gap-2">
                        <span class="w-6 h-6 rounded-lg bg-teal-600 text-white text-xs flex items-center justify-center font-bold">5</span>
                        <span>Matriks Penilaian 8 Aspek Kegiatan Validasi</span>
                    </h2>
                    <p class="text-xs text-slate-500 mt-0.5">Aturan Bukti (VATM: Valid, Asli, Terkini, Memadai) & Prinsip Asesmen (VRFA: Valid, Reliabel, Fleksibel, Adil)</p>
                </div>
            </div>

            <div class="overflow-x-auto">
                <table class="w-full text-left text-xs border-collapse">
                    <thead>
                        <tr class="bg-slate-100 text-slate-700 font-bold border-b border-slate-200">
                            <th class="py-2.5 px-3 w-8 text-center">No</th>
                            <th class="py-2.5 px-3 min-w-[180px]">Aspek Kegiatan Validasi</th>
                            <th class="py-2.5 px-2 text-center w-36">Aturan Bukti (VATM)</th>
                            <th class="py-2.5 px-2 text-center w-36">Prinsip Asesmen (VRFA)</th>
                            <th class="py-2.5 px-3 min-w-[220px]">Catatan / Temuan Aspek</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-200/80">
                        @foreach(App\Models\AssessmentVa::VALIDATION_ASPECTS as $no => $aspek)
                            @php
                                $mRow = $matriksPenilaian[$no] ?? [
                                    'no' => $no,
                                    'aspek' => $aspek,
                                    'vatm' => ['v' => true, 'a' => true, 't' => true, 'm' => true],
                                    'vrfa' => ['v' => true, 'r' => true, 'f' => true, 'a' => true],
                                    'catatan' => '',
                                ];
                            @endphp
                            <tr class="hover:bg-slate-50/70 transition-colors">
                                <td class="py-3 px-3 text-center text-slate-400 font-mono font-bold">{{ $no }}</td>
                                <td class="py-3 px-3 font-semibold text-slate-800">
                                    {{ $aspek }}
                                    <input type="hidden" name="matriks_penilaian[{{ $no }}][no]" value="{{ $no }}">
                                    <input type="hidden" name="matriks_penilaian[{{ $no }}][aspek]" value="{{ $aspek }}">
                                </td>

                                <!-- VATM -->
                                <td class="py-2 px-2 text-center">
                                    <div class="inline-flex items-center gap-1.5 bg-slate-50 px-2 py-1 rounded-lg border border-slate-200">
                                        <label class="cursor-pointer text-[11px] font-bold" title="Valid">
                                            <input type="checkbox" name="matriks_penilaian[{{ $no }}][vatm][v]" value="1" {{ !empty($mRow['vatm']['v']) ? 'checked' : '' }} class="rounded text-teal-600 focus:ring-0" {{ !$canEdit ? 'disabled' : '' }}>
                                            <span class="text-teal-800">V</span>
                                        </label>
                                        <label class="cursor-pointer text-[11px] font-bold" title="Asli">
                                            <input type="checkbox" name="matriks_penilaian[{{ $no }}][vatm][a]" value="1" {{ !empty($mRow['vatm']['a']) ? 'checked' : '' }} class="rounded text-teal-600 focus:ring-0" {{ !$canEdit ? 'disabled' : '' }}>
                                            <span class="text-teal-800">A</span>
                                        </label>
                                        <label class="cursor-pointer text-[11px] font-bold" title="Terkini">
                                            <input type="checkbox" name="matriks_penilaian[{{ $no }}][vatm][t]" value="1" {{ !empty($mRow['vatm']['t']) ? 'checked' : '' }} class="rounded text-teal-600 focus:ring-0" {{ !$canEdit ? 'disabled' : '' }}>
                                            <span class="text-teal-800">T</span>
                                        </label>
                                        <label class="cursor-pointer text-[11px] font-bold" title="Memadai">
                                            <input type="checkbox" name="matriks_penilaian[{{ $no }}][vatm][m]" value="1" {{ !empty($mRow['vatm']['m']) ? 'checked' : '' }} class="rounded text-teal-600 focus:ring-0" {{ !$canEdit ? 'disabled' : '' }}>
                                            <span class="text-teal-800">M</span>
                                        </label>
                                    </div>
                                </td>

                                <!-- VRFA -->
                                <td class="py-2 px-2 text-center">
                                    <div class="inline-flex items-center gap-1.5 bg-slate-50 px-2 py-1 rounded-lg border border-slate-200">
                                        <label class="cursor-pointer text-[11px] font-bold" title="Valid">
                                            <input type="checkbox" name="matriks_penilaian[{{ $no }}][vrfa][v]" value="1" {{ !empty($mRow['vrfa']['v']) ? 'checked' : '' }} class="rounded text-blue-600 focus:ring-0" {{ !$canEdit ? 'disabled' : '' }}>
                                            <span class="text-blue-800">V</span>
                                        </label>
                                        <label class="cursor-pointer text-[11px] font-bold" title="Reliabel">
                                            <input type="checkbox" name="matriks_penilaian[{{ $no }}][vrfa][r]" value="1" {{ !empty($mRow['vrfa']['r']) ? 'checked' : '' }} class="rounded text-blue-600 focus:ring-0" {{ !$canEdit ? 'disabled' : '' }}>
                                            <span class="text-blue-800">R</span>
                                        </label>
                                        <label class="cursor-pointer text-[11px] font-bold" title="Fleksibel">
                                            <input type="checkbox" name="matriks_penilaian[{{ $no }}][vrfa][f]" value="1" {{ !empty($mRow['vrfa']['f']) ? 'checked' : '' }} class="rounded text-blue-600 focus:ring-0" {{ !$canEdit ? 'disabled' : '' }}>
                                            <span class="text-blue-800">F</span>
                                        </label>
                                        <label class="cursor-pointer text-[11px] font-bold" title="Adil">
                                            <input type="checkbox" name="matriks_penilaian[{{ $no }}][vrfa][a]" value="1" {{ !empty($mRow['vrfa']['a']) ? 'checked' : '' }} class="rounded text-blue-600 focus:ring-0" {{ !$canEdit ? 'disabled' : '' }}>
                                            <span class="text-blue-800">A</span>
                                        </label>
                                    </div>
                                </td>

                                <!-- CATATAN -->
                                <td class="py-2 px-3">
                                    <input type="text" name="matriks_penilaian[{{ $no }}][catatan]" value="{{ $mRow['catatan'] ?? '' }}" placeholder="Catatan kesesuaian / gap..." class="w-full text-xs py-1 px-2.5 rounded-lg border border-slate-200 bg-white" {{ !$canEdit ? 'readonly' : '' }}>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>

        <!-- ================================================================= -->
        <!-- STEP 6: HASIL VALIDASI & TEMUAN -->
        <!-- ================================================================= -->
        <div x-show="currentStep === 6" class="bg-white rounded-2xl border border-slate-200/90 shadow-2xs p-5 space-y-4">
            <div class="border-b border-slate-100 pb-3 flex items-center justify-between">
                <div>
                    <h2 class="text-sm font-bold text-slate-900 flex items-center gap-2">
                        <span class="w-6 h-6 rounded-lg bg-teal-600 text-white text-xs flex items-center justify-center font-bold">6</span>
                        <span>Hasil Validasi & Temuan Gap</span>
                    </h2>
                    <p class="text-xs text-slate-500 mt-0.5">Daftar temuan ketidaksesuaian atau peluang peningkatan mutu beserta bukti pendukung.</p>
                </div>
                @if($canEdit)
                    <button type="button" @click="addTemuan()" class="inline-flex items-center px-2.5 py-1 rounded-lg bg-teal-50 text-teal-700 hover:bg-teal-100 font-bold text-xs border border-teal-200">
                        <span>Tambah Temuan</span>
                    </button>
                @endif
            </div>

            <div class="overflow-x-auto">
                <table class="w-full text-left text-xs border-collapse">
                    <thead>
                        <tr class="bg-slate-100/75 text-slate-700 font-bold border-b border-slate-200">
                            <th class="py-2.5 px-3 w-8 text-center">No</th>
                            <th class="py-2.5 px-3 min-w-[180px]">Temuan / Gap</th>
                            <th class="py-2.5 px-3 w-36">Kategori</th>
                            <th class="py-2.5 px-3 w-28 text-center">Prioritas</th>
                            <th class="py-2.5 px-3 min-w-[200px]">Rekomendasi</th>
                            <th class="py-2.5 px-3 min-w-[140px]">Bukti Pendukung</th>
                            @if($canEdit)
                                <th class="py-2.5 px-2 w-10 text-center">Aksi</th>
                            @endif
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100">
                        <template x-for="(t, tIdx) in temuanList" :key="tIdx">
                            <tr>
                                <td class="py-2 px-2 text-center text-slate-400 font-mono" x-text="tIdx + 1"></td>
                                <td class="py-1.5 px-2">
                                    <input type="text" :name="'temuan_validasi[' + tIdx + '][temuan]'" x-model="t.temuan" placeholder="Uraian temuan..." class="w-full text-xs py-1 px-2 rounded-lg border border-slate-200 bg-white" {{ !$canEdit ? 'readonly' : '' }}>
                                </td>
                                <td class="py-1.5 px-2">
                                    <input type="text" :name="'temuan_validasi[' + tIdx + '][kategori]'" x-model="t.kategori" placeholder="Perangkat / Prosedur..." class="w-full text-xs py-1 px-2 rounded-lg border border-slate-200 bg-white" {{ !$canEdit ? 'readonly' : '' }}>
                                </td>
                                <td class="py-1.5 px-2 text-center">
                                    <select :name="'temuan_validasi[' + tIdx + '][prioritas]'" x-model="t.prioritas" class="w-full text-xs py-1 px-1.5 rounded-lg border border-slate-200 bg-white font-bold" {{ !$canEdit ? 'disabled' : '' }}>
                                        <option value="Tinggi">Tinggi</option>
                                        <option value="Sedang">Sedang</option>
                                        <option value="Rendah">Rendah</option>
                                    </select>
                                </td>
                                <td class="py-1.5 px-2">
                                    <input type="text" :name="'temuan_validasi[' + tIdx + '][rekomendasi]'" x-model="t.rekomendasi" placeholder="Rekomendasi tindakan..." class="w-full text-xs py-1 px-2 rounded-lg border border-slate-200 bg-white" {{ !$canEdit ? 'readonly' : '' }}>
                                </td>
                                <td class="py-1.5 px-2">
                                    <input type="text" :name="'temuan_validasi[' + tIdx + '][bukti_pendukung]'" x-model="t.bukti_pendukung" placeholder="Dokumen / instrumen..." class="w-full text-xs py-1 px-2 rounded-lg border border-slate-200 bg-white" {{ !$canEdit ? 'readonly' : '' }}>
                                </td>
                                @if($canEdit)
                                    <td class="py-1.5 px-2 text-center">
                                        <button type="button" @click="removeTemuan(tIdx)" class="text-[11px] font-bold text-rose-600 hover:text-rose-800 p-1">
                                            Hapus
                                        </button>
                                    </td>
                                @endif
                            </tr>
                        </template>
                        <tr x-show="temuanList.length === 0">
                            <td colspan="{{ $canEdit ? 7 : 6 }}" class="py-6 text-center text-xs text-slate-400">
                                Tidak ada temuan gap validasi. Klik "+ Tambah Temuan" bila terdapat catatan khusus.
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>

        <!-- ================================================================= -->
        <!-- STEP 7: RENCANA IMPLEMENTASI PERBAIKAN & FINALISASI -->
        <!-- ================================================================= -->
        <div x-show="currentStep === 7" class="bg-white rounded-2xl border border-slate-200/90 shadow-2xs p-5 space-y-5">
            <div class="border-b border-slate-100 pb-3 flex items-center justify-between">
                <div>
                    <h2 class="text-sm font-bold text-slate-900 flex items-center gap-2">
                        <span class="w-6 h-6 rounded-lg bg-teal-600 text-white text-xs flex items-center justify-center font-bold">7</span>
                        <span>Rencana Implementasi Perbaikan & Finalisasi</span>
                    </h2>
                    <p class="text-xs text-slate-500 mt-0.5">Rumuskan rencana aksi perbaikan, penanggung jawab, target waktu, dan penandatanganan hasil validasi.</p>
                </div>
                @if($canEdit)
                    <button type="button" @click="addRencana()" class="inline-flex items-center px-2.5 py-1 rounded-lg bg-teal-50 text-teal-700 hover:bg-teal-100 font-bold text-xs border border-teal-200">
                        <span>Tambah Rencana</span>
                    </button>
                @endif
            </div>

            <!-- REKOMENDASI UMUM -->
            <div>
                <label class="block font-bold text-slate-800 text-xs uppercase tracking-wider mb-1.5">Rekomendasi Peningkatan Mutu Secara Umum</label>
                <textarea name="rekomendasi_peningkatan" rows="2" placeholder="Rekomendasi strategis untuk Komite Teknis LSP..." class="w-full text-xs p-3 rounded-xl border border-slate-200 focus:ring-1 focus:ring-teal-500" {{ !$canEdit ? 'readonly' : '' }}>{{ $va->rekomendasi_peningkatan }}</textarea>
            </div>

            <!-- TABEL RENCANA PERBAIKAN -->
            <div class="overflow-x-auto">
                <table class="w-full text-left text-xs border-collapse">
                    <thead>
                        <tr class="bg-slate-100/75 text-slate-700 font-bold border-b border-slate-200">
                            <th class="py-2.5 px-3 w-8 text-center">No</th>
                            <th class="py-2.5 px-3 min-w-[240px]">Kegiatan Perbaikan Sesuai Rekomendasi</th>
                            <th class="py-2.5 px-3 w-36">Waktu Penyelesaian</th>
                            <th class="py-2.5 px-3 w-44">Penanggung Jawab</th>
                            <th class="py-2.5 px-3 w-32">Status</th>
                            @if($canEdit)
                                <th class="py-2.5 px-2 w-10 text-center">Aksi</th>
                            @endif
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100">
                        <template x-for="(r, rIdx) in rencanaList" :key="rIdx">
                            <tr>
                                <td class="py-2 px-2 text-center text-slate-400 font-mono" x-text="rIdx + 1"></td>
                                <td class="py-1.5 px-2">
                                    <input type="text" :name="'rencana_perbaikan[' + rIdx + '][kegiatan]'" x-model="r.kegiatan" placeholder="Kegiatan perbaikan..." class="w-full text-xs py-1 px-2 rounded-lg border border-slate-200 bg-white" {{ !$canEdit ? 'readonly' : '' }}>
                                </td>
                                <td class="py-1.5 px-2">
                                    <input type="date" :name="'rencana_perbaikan[' + rIdx + '][waktu]'" x-model="r.waktu" class="w-full text-xs py-1 px-2 rounded-lg border border-slate-200 bg-white" {{ !$canEdit ? 'readonly' : '' }}>
                                </td>
                                <td class="py-1.5 px-2">
                                    <input type="text" :name="'rencana_perbaikan[' + rIdx + '][penanggung_jawab]'" x-model="r.penanggung_jawab" placeholder="Penanggung jawab..." class="w-full text-xs py-1 px-2 rounded-lg border border-slate-200 bg-white" {{ !$canEdit ? 'readonly' : '' }}>
                                </td>
                                <td class="py-1.5 px-2">
                                    <select :name="'rencana_perbaikan[' + rIdx + '][status]'" x-model="r.status" class="w-full text-xs py-1 px-1.5 rounded-lg border border-slate-200 bg-white font-semibold" {{ !$canEdit ? 'disabled' : '' }}>
                                        <option value="Belum Dimulai">Belum Dimulai</option>
                                        <option value="Berjalan">Berjalan</option>
                                        <option value="Selesai">Selesai</option>
                                        <option value="Ditunda">Ditunda</option>
                                    </select>
                                </td>
                                @if($canEdit)
                                    <td class="py-1.5 px-2 text-center">
                                        <button type="button" @click="removeRencana(rIdx)" class="text-[11px] font-bold text-rose-600 hover:text-rose-800 p-1">
                                            Hapus
                                        </button>
                                    </td>
                                @endif
                            </tr>
                        </template>
                    </tbody>
                </table>
            </div>

            <!-- TANDA TANGAN LEAD ASESOR -->
            <div class="border-t border-slate-200/80 pt-4 grid grid-cols-1 md:grid-cols-2 gap-4">
                <div class="space-y-2">
                    <span class="text-xs font-bold text-slate-800 uppercase tracking-wider block">
                        Tanda Tangan Lead / Ketua Validasi
                    </span>
                    <p class="text-xs text-slate-500">
                        Dengan memfinalisasi, ketua tim validasi mengesahkan bahwa rekomendasi dan rencana perbaikan telah disepakati oleh seluruh anggota panel validasi.
                    </p>
                    <div class="text-xs font-semibold text-slate-700">
                        Nama Lead Asesor: <span class="font-bold text-slate-900">{{ $va->leadAsesor->nama_lengkap ?? '-' }}</span>
                    </div>
                </div>

                <div>
                    @if($va->tanda_tangan_lead)
                        <div class="border border-slate-200 rounded-xl p-3 bg-slate-50/50 flex flex-col items-center justify-center">
                            <img src="{{ $va->tanda_tangan_lead }}" alt="Tanda Tangan Lead Asesor" class="max-h-24 object-contain">
                            <span class="text-[10px] text-slate-400 font-mono mt-1">Ditandatangani pada: {{ $va->tanggal_ttd_lead ? $va->tanggal_ttd_lead->format('d/m/Y H:i') : '-' }}</span>
                        </div>
                    @elseif($canEdit)
                        <div class="border border-slate-200 rounded-xl p-3 bg-slate-50/50 space-y-2">
                            <canvas id="canvas-lead" class="w-full h-24 border border-slate-200 bg-white rounded-lg"></canvas>
                            <div class="flex items-center justify-between">
                                <button type="button" @click="clearSignature()" class="text-[11px] text-slate-500 hover:text-slate-700 font-semibold">
                                    Bersihkan
                                </button>
                                <span class="text-[10px] text-slate-400">Goreskan tanda tangan di kanvas</span>
                            </div>
                        </div>
                    @else
                        <div class="p-4 bg-slate-50 rounded-xl border border-dashed border-slate-200 text-center text-xs text-slate-400">
                            Belum dibubuhi tanda tangan.
                        </div>
                    @endif
                </div>
            </div>
        </div>

        <!-- WIZARD STEP NAVIGATION FOOTER -->
        <div class="flex items-center justify-between pt-2">
            <div>
                <button type="button" @click="goToStep(currentStep - 1)" x-show="currentStep > 1" class="px-4 py-2 rounded-xl border border-slate-200 bg-white text-slate-700 font-bold text-xs hover:bg-slate-50 transition-colors shadow-2xs">
                    <span>Langkah Sebelumnya</span>
                </button>
            </div>

            <div class="flex items-center gap-2">
                @if($canEdit)
                    <button type="button" @click="autoSave(false)" class="px-3.5 py-2 rounded-xl border border-slate-200 bg-white text-slate-700 font-bold text-xs hover:bg-slate-50 transition-colors shadow-2xs">
                        <span>Simpan Langkah</span>
                    </button>

                    <button type="button" @click="goToStep(currentStep + 1)" x-show="currentStep < 7" class="px-4 py-2 rounded-xl bg-teal-600 text-white font-bold text-xs hover:bg-teal-700 transition-colors shadow-2xs">
                        <span>Lanjut ke Langkah Berikutnya</span>
                    </button>

                    <button type="button" @click="openFinalizeModal()" x-show="currentStep === 7" class="px-5 py-2 rounded-xl bg-emerald-600 text-white font-bold text-xs hover:bg-emerald-700 transition-colors shadow-2xs flex items-center">
                        <span>Finalisasi & Kunci FR.VA</span>
                    </button>
                @endif
            </div>
        </div>
    </form>

    <!-- AUDIT TRAIL LOG PREVIEW -->
    <div class="bg-white rounded-2xl border border-slate-200/90 shadow-2xs p-4 sm:p-5">
        <h3 class="text-xs font-bold text-slate-900 uppercase tracking-wider mb-3 flex items-center gap-2">
            <span>Riwayat Audit & Versi FR.VA</span>
        </h3>
        <div class="space-y-2">
            @forelse($va->auditLogs as $log)
                <div class="text-xs p-2.5 bg-slate-50 border border-slate-100 rounded-xl flex items-center justify-between">
                    <div>
                        <span class="font-bold text-slate-800">{{ $log->keterangan }}</span>
                        <span class="text-slate-400 text-[10px] block">Oleh {{ $log->user->nama_lengkap ?? 'Sistem' }} &bull; Versi v{{ $log->version }}</span>
                    </div>
                    <span class="text-[10px] text-slate-400 font-mono">{{ $log->created_at->format('d/m/Y H:i') }}</span>
                </div>
            @empty
                <p class="text-xs text-slate-400 italic">Belum ada riwayat perubahan.</p>
            @endforelse
        </div>
    </div>

    <!-- MODAL KONFIRMASI FINALISASI -->
    <div x-show="showFinalizeModal" class="fixed inset-0 z-50 overflow-y-auto" style="display: none;">
        <div class="fixed inset-0 bg-slate-900/50 backdrop-blur-xs transition-opacity" @click="showFinalizeModal = false"></div>
        <div class="flex min-h-full items-center justify-center p-4">
            <div class="relative transform overflow-hidden rounded-2xl bg-white p-5 text-left shadow-xl transition-all sm:w-full sm:max-w-md border border-slate-200">
                <div class="flex items-start gap-3">
                    <div class="w-10 h-10 rounded-xl bg-emerald-100 text-emerald-800 flex items-center justify-center shrink-0 font-bold text-[10px]">
                        KUNCI
                    </div>
                    <div>
                        <h3 class="text-sm font-bold text-slate-900">Finalisasi Kegiatan Validasi FR.VA?</h3>
                        <p class="text-xs text-slate-500 mt-1">
                            Setelah difinalisasi, seluruh 7 tahapan validasi dan rencana perbaikan akan <strong>dikunci (read-only)</strong> dan diteruskan ke Komite Teknis LSP.
                        </p>
                    </div>
                </div>

                <div class="mt-5 flex items-center justify-end gap-2">
                    <button type="button" @click="showFinalizeModal = false" class="px-3 py-1.5 rounded-xl border border-slate-200 text-slate-600 text-xs font-semibold hover:bg-slate-50">
                        Batal
                    </button>
                    <button type="button" @click="submitFinalize()" class="px-4 py-1.5 rounded-xl bg-emerald-600 text-white text-xs font-bold hover:bg-emerald-700 shadow-2xs">
                        Ya, Finalisasi & Kunci
                    </button>
                </div>
            </div>
        </div>
    </div>

</div>

@push('js')
<script>
function vaWizardApp() {
    return {
        currentStep: {{ max(1, min(7, (int) ($va->current_step ?? 1))) }},
        isSaving: false,
        autoSaveText: 'Tersimpan',
        showFinalizeModal: false,
        padLead: null,

        steps: [
            { title: '1. Informasi' },
            { title: '2. Persiapan' },
            { title: '3. Acuan' },
            { title: '4. Kontribusi' },
            { title: '5. Matriks Aspek' },
            { title: '6. Temuan' },
            { title: '7. Rencana & TTD' },
        ],

        pesertaList: @json($pesertaList),
        temuanList: @json($temuanList),
        rencanaList: @json($rencanaList),

        init() {
            this.$nextTick(() => {
                const canvas = document.getElementById('canvas-lead');
                if (canvas) {
                    this.padLead = new SignaturePad(canvas, {
                        backgroundColor: 'rgb(255, 255, 255)',
                        penColor: 'rgb(15, 23, 42)'
                    });
                }
            });
        },

        addPeserta() {
            this.pesertaList.push({
                peran: 'asesor_kompetensi',
                nama: '',
                hasil_konfirmasi: '',
                tujuan: ''
            });
        },

        removePeserta(idx) {
            this.pesertaList.splice(idx, 1);
        },

        addTemuan() {
            this.temuanList.push({
                temuan: '',
                kategori: 'Perangkat Asesmen',
                prioritas: 'Sedang',
                rekomendasi: '',
                bukti_pendukung: ''
            });
        },

        removeTemuan(idx) {
            this.temuanList.splice(idx, 1);
        },

        addRencana() {
            this.rencanaList.push({
                kegiatan: '',
                waktu: '{{ now()->addWeeks(2)->format('Y-m-d') }}',
                penanggung_jawab: '',
                status: 'Belum Dimulai'
            });
        },

        removeRencana(idx) {
            this.rencanaList.splice(idx, 1);
        },

        clearSignature() {
            if (this.padLead) {
                this.padLead.clear();
            }
        },

        goToStep(targetStep) {
            if (targetStep < 1 || targetStep > 7) return;

            // Trigger background autosave of current step
            this.autoSave(false);
            this.currentStep = targetStep;

            if (targetStep === 7) {
                this.$nextTick(() => {
                    const canvas = document.getElementById('canvas-lead');
                    if (canvas && !this.padLead) {
                        this.padLead = new SignaturePad(canvas, {
                            backgroundColor: 'rgb(255, 255, 255)',
                            penColor: 'rgb(15, 23, 42)'
                        });
                    }
                });
            }
        },

        async autoSave(isFinalize) {
            @if(!$canEdit)
                return;
            @endif

            this.isSaving = true;
            this.autoSaveText = 'Menyimpan...';

            const form = document.getElementById('form-va');
            const formData = new FormData(form);

            if (this.padLead && !this.padLead.isEmpty()) {
                formData.set('signature', this.padLead.toDataURL('image/png'));
            }

            try {
                const response = await fetch("{{ route('dokumen-asesmen.va.save-step', $va->id) }}", {
                    method: 'POST',
                    headers: {
                        'X-Requested-With': 'XMLHttpRequest',
                        'Accept': 'application/json',
                    },
                    body: formData
                });

                if (response.ok) {
                    const res = await response.json();
                    this.autoSaveText = 'Tersimpan otomatis: ' + (res.saved_at || '');
                } else {
                    this.autoSaveText = 'Gagal menyimpan otomatis';
                }
            } catch (e) {
                this.autoSaveText = 'Koneksi terputus';
            } finally {
                this.isSaving = false;
            }
        },

        openFinalizeModal() {
            this.showFinalizeModal = true;
        },

        submitFinalize() {
            document.getElementById('input-finalize').value = '1';

            if (this.padLead && !this.padLead.isEmpty()) {
                document.getElementById('input-signature').value = this.padLead.toDataURL('image/png');
            }

            document.getElementById('form-va').submit();
        }
    };
}
</script>
@endpush
@endsection
