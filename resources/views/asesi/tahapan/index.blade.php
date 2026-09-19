@extends('tata-letak.dasbor')

@section('judul', 'Tahapan Pendaftaran & Asesmen')

@push('css')
    <!-- Signature Pad Script -->
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
    </style>
@endpush

@section('konten')
<div class="min-h-screen bg-slate-50/60 pb-16" 
     x-data="{
        currentStep: {{ $currentStep ?? 1 }},
        activeStep: {{ $activeStep ?? 1 }},
        isStep1Complete: {{ ($isDiajukan || $isAccAdmin || $isApl02Selesai || $isAk01Selesai) ? 'true' : 'false' }},
        isStep2Complete: {{ $isApl02Approved ? 'true' : 'false' }},
        isStep3Complete: {{ $isAk01Selesai ? 'true' : 'false' }},
        isStep4Complete: {{ ($isAk07Selesai ?? false) ? 'true' : 'false' }},
        isAsesiSignedAk01: {{ ($pendaftaran && (!empty($pendaftaran->tanda_tangan_asesi_ak01) || in_array($pendaftaran->status_ak01, ['disetujui_asesi', 'selesai']))) ? 'true' : 'false' }},
        setStep(step) {
            if (step === 2 && !this.isStep1Complete) return;
            if (step === 3 && !this.isStep2Complete) {
                alert('Formulir FR.AK.01 belum dapat diakses. Mohon menunggu Formulir FR.APL.02 Anda diperiksa dan disetujui (Approved) oleh Asesor Penguji.');
                return;
            }
            if (step === 4 && !this.isStep3Complete) {
                alert('Formulir FR.AK.07 belum dapat diakses. Mohon selesaikan dan tanda tangani Formulir FR.AK.01 terlebih dahulu.');
                return;
            }
            if (step === 5 && !this.isStep4Complete) {
                alert('Halaman tes/ujian belum dapat diakses. Mohon selesaikan dan tanda tangani Formulir FR.AK.07 terlebih dahulu.');
                return;
            }
            this.currentStep = step;
            window.scrollTo({ top: 120, behavior: 'smooth' });
        }
     }">

    <!-- =========================================================================
         HEADER HALAMAN & STATUS SUMMARY BOX
         ========================================================================= -->
    <div class="max-w-6xl mx-auto px-4 sm:px-6 pt-2 pb-6">
        <div class="bg-white rounded-2xl border border-slate-200/80 shadow-xs p-6 sm:p-8 flex flex-col lg:flex-row lg:items-center lg:justify-between gap-6">
            <!-- Left Info -->
            <div class="space-y-1.5 max-w-2xl">
                <div class="inline-flex items-center px-2.5 py-1 bg-blue-50 text-blue-700 border border-blue-200/60 text-[11px] font-bold rounded-full uppercase tracking-wider">
                    Alur Sertifikasi BNSP
                </div>
                <h1 class="text-2xl sm:text-3xl font-bold text-slate-900 tracking-tight">Tahapan Pendaftaran & Asesmen</h1>
                <p class="text-slate-500 text-sm leading-relaxed">
                    Selesaikan pengisian berkas permohonan, asesmen mandiri, dan penandatanganan kesepakatan asesmen sebelum memasuki ruang uji.
                </p>
            </div>

            <!-- Right: Status Summary Box -->
            <div class="bg-slate-50 border border-slate-200/80 rounded-xl p-4 sm:p-5 lg:min-w-[320px] flex-shrink-0 space-y-3">
                <div class="flex items-start justify-between gap-3">
                    <div class="overflow-hidden">
                        <span class="text-[11px] font-bold uppercase text-slate-400 tracking-wider block">Skema Terpilih</span>
                        <div class="font-bold text-slate-800 text-sm truncate mt-0.5" title="{{ $pendaftaran->skema->nama_skema ?? 'Belum Memilih Skema' }}">
                            {{ $pendaftaran->skema->nama_skema ?? 'Pilih Skema Sertifikasi' }}
                        </div>
                        <div class="text-xs text-slate-500 font-mono mt-0.5">
                            {{ $pendaftaran->skema->kode_skema ?? 'SKEMA-BNSP' }}
                        </div>
                    </div>
                    @if(isset($semuaPendaftaran) && $semuaPendaftaran->count() > 1)
                        <div class="relative flex-shrink-0">
                            <select onchange="window.location.href='?pendaftaran_id=' + this.value" 
                                    class="text-xs font-semibold bg-white border border-slate-200 rounded-lg px-2.5 py-1.5 text-slate-700 focus:ring-2 focus:ring-blue-500 outline-hidden">
                                @foreach($semuaPendaftaran as $itemP)
                                    <option value="{{ $itemP->id }}" {{ (isset($pendaftaran) && $pendaftaran->id == $itemP->id) ? 'selected' : '' }}>
                                        {{ Str::limit($itemP->skema->nama_skema ?? 'Skema', 18) }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                    @endif
                </div>

                <!-- Progress Bar Horizontal -->
                <div class="space-y-1.5 pt-1 border-t border-slate-200/60">
                    <div class="flex justify-between items-center text-xs">
                        <span class="font-medium text-slate-600">Progres Tahapan</span>
                        <span class="font-bold {{ $progressPersen == 100 ? 'text-emerald-600' : 'text-blue-600' }}">
                            {{ $progressPersen }}%
                        </span>
                    </div>
                    <div class="w-full h-2 bg-slate-200/80 rounded-full overflow-hidden">
                        <div class="h-full rounded-full transition-all duration-500 {{ $progressPersen == 100 ? 'bg-emerald-500' : 'bg-blue-600' }}" 
                             style="width: {{ $progressPersen }}%;"></div>
                    </div>
                </div>
            </div>
        </div>

        <!-- BANNER NOTIFIKASI ASESMEN SUDAH DIMULAI -->
        @if(isset($pendaftaran) && $pendaftaran && $pendaftaran->isRuangUjiOpen() && empty($pendaftaran->rekomendasi))
            <div class="mt-4 bg-gradient-to-r from-emerald-600 via-teal-600 to-emerald-700 text-white rounded-2xl p-4 sm:p-5 shadow-lg flex flex-col sm:flex-row items-center justify-between gap-4 animate-in fade-in duration-300">
                <div class="flex items-center gap-3.5">
                    <div class="w-10 h-10 rounded-xl bg-white/20 backdrop-blur-xs flex items-center justify-center text-xl shrink-0">
                        <span class="relative flex h-3 w-3">
                            <span class="animate-ping absolute inline-flex h-full w-full rounded-full bg-white opacity-90"></span>
                            <span class="relative inline-flex rounded-full h-3 w-3 bg-white"></span>
                        </span>
                    </div>
                    <div>
                        <div class="font-extrabold text-sm sm:text-base flex items-center gap-2">
                            <span>Sesi Asesmen Telah Dimulai!</span>
                            <span class="px-2 py-0.5 rounded-md bg-white/20 text-[10px] font-bold uppercase tracking-wider">Hari-H Ujian</span>
                        </div>
                        <p class="text-xs text-emerald-100 mt-0.5">
                            Ruang Uji dan lembar instrumen asesmen telah dibuka oleh Asesor Penguji di {{ $pendaftaran->jadwal->nama_tuk ?? 'TUK' }}.
                        </p>
                    </div>
                </div>
                <button type="button" 
                   @click="setStep(5)" 
                   class="px-4 py-2.5 rounded-xl bg-white text-emerald-800 hover:bg-emerald-50 font-bold text-xs shadow-sm transition-all duration-150 shrink-0 inline-flex items-center gap-2 cursor-pointer">
                    <span>Mulai Ujian / Tes Sekarang</span>
                    <span>&rarr;</span>
                </button>
            </div>
        @endif



        <!-- =========================================================================
             STEP 1: FORMULIR FR.APL.01 (PERMOHONAN SERTIFIKASI)
             ========================================================================= -->
        <div x-show="currentStep === 1" x-transition.opacity.duration.300ms class="mt-6 space-y-6">
            @if($isAccAdmin)
                <div class="bg-emerald-50/90 border border-emerald-200/90 rounded-2xl p-5 sm:p-6 shadow-xs flex flex-col sm:flex-row items-start sm:items-center justify-between gap-4">
                    <div class="flex items-start gap-3.5">
                        <div class="w-10 h-10 rounded-xl bg-emerald-100 text-emerald-600 flex items-center justify-center shrink-0 mt-0.5">
                            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75L11.25 15 15 9.75M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                            </svg>
                        </div>
                        <div class="space-y-1">
                            <div class="flex items-center gap-2 flex-wrap">
                                <span class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-full text-xs font-bold bg-emerald-100 text-emerald-800 uppercase tracking-wider">
                                    <svg class="w-3 h-3 text-emerald-600" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"></path></svg>
                                    Disetujui Admin (ACC) &bull; Read-Only
                                </span>
                                @if($pendaftaran?->nomor_pendaftaran)
                                    <span class="text-xs text-slate-500 font-mono font-semibold">No. Reg: {{ $pendaftaran->nomor_pendaftaran }}</span>
                                @endif
                            </div>
                            <h3 class="text-base font-bold text-emerald-950">Formulir FR.APL.01 Telah Diverifikasi & Disetujui (ACC)</h3>
                            <p class="text-xs text-emerald-800 leading-relaxed max-w-2xl">
                                Data permohonan sertifikasi dan seluruh berkas persyaratan Anda telah diverifikasi serta disetujui (ACC) oleh Admin LSP. Data pada formulir FR.APL.01 ini telah dikunci (read-only) untuk menjamin keabsahan dokumen asesmen.
                                @if($pendaftaran?->asesor)
                                    <span class="font-semibold block mt-1">Asesor Penguji: {{ $pendaftaran->asesor->nama_lengkap }} | Jadwal: {{ $pendaftaran->jadwal->nama_tuk ?? 'TUK Terpilih' }}</span>
                                @endif
                            </p>
                        </div>
                    </div>
                    <button type="button" @click="setStep({{ $isAk01Selesai ? 4 : ($isApl02Approved ? 3 : 2) }})" class="shrink-0 w-full sm:w-auto px-5 py-2.5 rounded-xl bg-emerald-600 hover:bg-emerald-700 text-white font-bold text-xs shadow-xs transition-colors flex items-center justify-center gap-2 cursor-pointer">
                        <span>Lanjut ke Formulir {{ $isAk01Selesai ? 'FR.AK.07' : ($isApl02Approved ? 'FR.AK.01' : 'FR.APL.02') }}</span>
                        <span>&rarr;</span>
                    </button>
                </div>
            @endif

            <form action="{{ route('asesi.tahapan.apl01') }}" method="POST" enctype="multipart/form-data" class="space-y-6">
                @csrf
                <input type="hidden" name="pendaftaran_id" value="{{ $pendaftaran->id ?? '' }}">

                <!-- Card Container -->
                <div class="bg-white rounded-2xl border border-slate-200/80 shadow-xs p-6 sm:p-8 space-y-8">
                    <!-- Section Header -->
                    <div class="border-b border-slate-200/80 pb-4 flex items-start justify-between gap-4">
                        <div>
                            <div class="text-blue-600 font-bold text-xs uppercase tracking-wider">
                                Bagian 1 &bull; Rincian Data Pemohon
                            </div>
                            <h2 class="text-xl font-bold text-slate-900 mt-1">Data Pribadi & Kontak Asesi</h2>
                            <p class="text-slate-500 text-xs mt-0.5">Pastikan NIK dan data identitas sesuai dengan data kependudukan resmi (KTP/Kartu Pelajar).</p>
                        </div>
                        @if($isAccAdmin)
                            <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-xs font-bold bg-slate-100 text-slate-600 border border-slate-200 shrink-0">
                                <svg class="w-3.5 h-3.5 text-slate-500" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M16.5 10.5V6.75a4.5 4.5 0 10-9 0v3.75m-.75 11.25h10.5a2.25 2.25 0 002.25-2.25v-6.75a2.25 2.25 0 00-2.25-2.25H6.75a2.25 2.25 0 00-2.25 2.25v6.75a2.25 2.25 0 002.25 2.25z"/></svg>
                                Terkunci (Read-Only)
                            </span>
                        @endif
                    </div>

                    <!-- Grid Form Data Pribadi -->
                    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-5 text-sm">
                        <!-- Nama Lengkap -->
                        <div class="space-y-1.5 sm:col-span-2">
                            <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider">Nama Lengkap Sesuai KTP <span class="text-red-500">*</span></label>
                            <input type="text" name="nama_lengkap" value="{{ old('nama_lengkap', $draftData['nama_lengkap']) }}" required {{ $isAccAdmin ? 'readonly' : '' }}
                                   class="w-full {{ $isAccAdmin ? 'bg-slate-100 text-slate-600 cursor-not-allowed' : 'bg-white text-slate-800 focus:ring-2 focus:ring-blue-500 focus:border-blue-500' }} border border-slate-200 rounded-xl px-3.5 py-2.5 outline-hidden font-medium">
                        </div>

                        <!-- NIK / NISN -->
                        <div class="space-y-1.5">
                            <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider">NIK / No. KTP <span class="text-red-500">*</span></label>
                            <input type="text" name="nik" minlength="16" maxlength="16" pattern="[0-9]{16}" inputmode="numeric" oninput="this.value = this.value.replace(/[^0-9]/g, '').slice(0, 16)" title="NIK harus berjumlah tepat 16 digit angka" value="{{ old('nik', $draftData['nik']) }}" required placeholder="16 Digit NIK" {{ $isAccAdmin ? 'readonly' : '' }}
                                   class="w-full {{ $isAccAdmin ? 'bg-slate-100 text-slate-600 cursor-not-allowed' : 'bg-white text-slate-800 focus:ring-2 focus:ring-blue-500 focus:border-blue-500' }} border border-slate-200 rounded-xl px-3.5 py-2.5 outline-hidden font-mono">
                        </div>

                        <!-- Tempat Lahir -->
                        <div class="space-y-1.5">
                            <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider">Tempat Lahir <span class="text-red-500">*</span></label>
                            <input type="text" name="tempat_lahir" value="{{ old('tempat_lahir', $draftData['tempat_lahir']) }}" required placeholder="Contoh: Bogor" {{ $isAccAdmin ? 'readonly' : '' }}
                                   class="w-full {{ $isAccAdmin ? 'bg-slate-100 text-slate-600 cursor-not-allowed' : 'bg-white text-slate-800 focus:ring-2 focus:ring-blue-500 focus:border-blue-500' }} border border-slate-200 rounded-xl px-3.5 py-2.5 outline-hidden">
                        </div>

                        <!-- Tanggal Lahir -->
                        <div class="space-y-1.5">
                            <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider">Tanggal Lahir <span class="text-red-500">*</span></label>
                            <input type="date" name="tanggal_lahir" value="{{ old('tanggal_lahir', $draftData['tanggal_lahir']) }}" max="{{ date('Y-m-d') }}" required {{ $isAccAdmin ? 'readonly' : '' }}
                                   class="w-full {{ $isAccAdmin ? 'bg-slate-100 text-slate-600 cursor-not-allowed' : 'bg-white text-slate-800 focus:ring-2 focus:ring-blue-500 focus:border-blue-500' }} border border-slate-200 rounded-xl px-3.5 py-2.5 outline-hidden">
                        </div>

                        <!-- Jenis Kelamin -->
                        <div class="space-y-1.5">
                            <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider">Jenis Kelamin <span class="text-red-500">*</span></label>
                            <select name="jenis_kelamin" required {{ $isAccAdmin ? 'disabled' : '' }}
                                    class="w-full {{ $isAccAdmin ? 'bg-slate-100 text-slate-600 cursor-not-allowed' : 'bg-white text-slate-800 focus:ring-2 focus:ring-blue-500 focus:border-blue-500' }} border border-slate-200 rounded-xl px-3.5 py-2.5 outline-hidden">
                                <option value="Laki-laki" {{ old('jenis_kelamin', $draftData['jenis_kelamin']) === 'Laki-laki' ? 'selected' : '' }}>Laki-laki</option>
                                <option value="Perempuan" {{ old('jenis_kelamin', $draftData['jenis_kelamin']) === 'Perempuan' ? 'selected' : '' }}>Perempuan</option>
                            </select>
                            @if($isAccAdmin)
                                <input type="hidden" name="jenis_kelamin" value="{{ old('jenis_kelamin', $draftData['jenis_kelamin']) }}">
                            @endif
                        </div>

                        <!-- Nomor HP / WhatsApp -->
                        <div class="space-y-1.5">
                            <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider">No. HP / WhatsApp <span class="text-red-500">*</span></label>
                            <input type="text" name="nomor_telepon" value="{{ old('nomor_telepon', $draftData['nomor_telepon']) }}" required placeholder="08xxxxxxxxxx" {{ $isAccAdmin ? 'readonly' : '' }}
                                   class="w-full {{ $isAccAdmin ? 'bg-slate-100 text-slate-600 cursor-not-allowed' : 'bg-white text-slate-800 focus:ring-2 focus:ring-blue-500 focus:border-blue-500' }} border border-slate-200 rounded-xl px-3.5 py-2.5 outline-hidden font-mono">
                        </div>

                        <!-- Email -->
                        <div class="space-y-1.5">
                            <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider">Email Asesi</label>
                            <input type="email" value="{{ $pengguna->email }}" disabled
                                   class="w-full bg-slate-100 border border-slate-200 rounded-xl px-3.5 py-2.5 text-slate-500 cursor-not-allowed">
                        </div>

                        <!-- Lembaga / Sekolah -->
                        <div class="space-y-1.5">
                            <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider">Lembaga / Sekolah / Instansi</label>
                            <input type="text" name="nama_sekolah_instansi" value="{{ old('nama_sekolah_instansi', $draftData['nama_sekolah_instansi'] ?? 'SMKN 1 Gunungputri') }}" {{ $isAccAdmin ? 'readonly' : '' }}
                                   class="w-full {{ $isAccAdmin ? 'bg-slate-100 text-slate-600 cursor-not-allowed' : 'bg-white text-slate-800 focus:ring-2 focus:ring-blue-500 focus:border-blue-500' }} border border-slate-200 rounded-xl px-3.5 py-2.5 outline-hidden">
                        </div>

                        <!-- Alamat Rumah -->
                        <div class="space-y-1.5 sm:col-span-2 lg:col-span-3">
                            <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider">Alamat Domisili Lengkap <span class="text-red-500">*</span></label>
                            <textarea name="alamat" rows="2" required placeholder="Jalan, RT/RW, Kelurahan, Kecamatan, Kota/Kabupaten" {{ $isAccAdmin ? 'readonly' : '' }}
                                      class="w-full {{ $isAccAdmin ? 'bg-slate-100 text-slate-600 cursor-not-allowed' : 'bg-white text-slate-800 focus:ring-2 focus:ring-blue-500 focus:border-blue-500' }} border border-slate-200 rounded-xl px-3.5 py-2 outline-hidden">{{ old('alamat', $draftData['alamat']) }}</textarea>
                        </div>
                    </div>

                    <!-- Section Header 2: Skema Sertifikasi -->
                    <div class="border-t border-slate-200/80 pt-6">
                        <div class="text-blue-600 font-bold text-xs uppercase tracking-wider">
                            Bagian 2 &bull; Data Skema Sertifikasi
                        </div>
                        <h2 class="text-xl font-bold text-slate-900 mt-1">Pilihan Skema & Tujuan Asesmen</h2>
                    </div>

                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-5 text-sm">
                        <!-- Skema Sertifikasi BNSP -->
                        <div class="space-y-1.5">
                            <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider">Skema Sertifikasi BNSP <span class="text-red-500">*</span></label>
                            <select name="skema_id" id="select-skema-tahapan" onchange="if(typeof updateTabelUnitTahapan==='function') updateTabelUnitTahapan()" required {{ $isAccAdmin ? 'disabled' : '' }}
                                    class="w-full {{ $isAccAdmin ? 'bg-slate-100 text-slate-600 cursor-not-allowed' : 'bg-white text-slate-800 focus:ring-2 focus:ring-blue-500 focus:border-blue-500' }} border border-slate-200 rounded-xl px-3.5 py-2.5 outline-hidden font-semibold">
                                <option value="">-- Pilih Skema Sertifikasi --</option>
                                @foreach($skemaList as $sk)
                                    @php
                                        $isDitolak = in_array($sk->id, $ditolakSkemaIds ?? []);
                                        $isSelected = old('skema_id', $draftData['skema_id']) == $sk->id;
                                    @endphp
                                    <option value="{{ $sk->id }}" {{ $isSelected ? 'selected' : '' }} {{ $isDitolak ? 'disabled' : '' }}>
                                        {{ $sk->kode_skema }} - {{ $sk->nama_skema }} {{ $isDitolak ? '(Ditolak)' : '' }}
                                    </option>
                                @endforeach
                            </select>
                            @if($isAccAdmin)
                                <input type="hidden" name="skema_id" value="{{ old('skema_id', $draftData['skema_id']) }}">
                            @endif
                        </div>

                        <!-- Tujuan Asesmen -->
                        <div class="space-y-1.5">
                            <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider">Tujuan Asesmen <span class="text-red-500">*</span></label>
                            <select name="tujuan_asesmen" required {{ $isAccAdmin ? 'disabled' : '' }}
                                    class="w-full {{ $isAccAdmin ? 'bg-slate-100 text-slate-600 cursor-not-allowed' : 'bg-white text-slate-800 focus:ring-2 focus:ring-blue-500 focus:border-blue-500' }} border border-slate-200 rounded-xl px-3.5 py-2.5 outline-hidden">
                                <option value="Sertifikasi" {{ old('tujuan_asesmen', $draftData['tujuan_asesmen']) === 'Sertifikasi' ? 'selected' : '' }}>Sertifikasi</option>
                                <option value="Sertifikasi Ulang" {{ old('tujuan_asesmen', $draftData['tujuan_asesmen']) === 'Sertifikasi Ulang' ? 'selected' : '' }}>Sertifikasi Ulang</option>
                                <option value="Pengakuan Kompetensi Terkini (PKT)" {{ old('tujuan_asesmen', $draftData['tujuan_asesmen']) === 'Pengakuan Kompetensi Terkini (PKT)' ? 'selected' : '' }}>Pengakuan Kompetensi Terkini (PKT)</option>
                                <option value="Rekognisi Pembelajaran Lampau (RPL)" {{ old('tujuan_asesmen', $draftData['tujuan_asesmen']) === 'Rekognisi Pembelajaran Lampau (RPL)' ? 'selected' : '' }}>Rekognisi Pembelajaran Lampau (RPL)</option>
                            </select>
                            @if($isAccAdmin)
                                <input type="hidden" name="tujuan_asesmen" value="{{ old('tujuan_asesmen', $draftData['tujuan_asesmen']) }}">
                            @endif
                        </div>
                    </div>

                    <!-- Container Unit Kompetensi per Skema -->
                    <div id="wrapper-unit-kompetensi" class="mt-4 space-y-4">
                        @foreach($skemaList as $sk)
                            <div class="tabel-unit-tahapan hidden bg-white border border-slate-200/90 rounded-2xl overflow-hidden shadow-2xs transition-all duration-300" data-skema-id="{{ $sk->id }}">
                                <!-- Header Card Unit -->
                                <div class="bg-slate-50/60 border-b border-slate-200/80 px-5 py-3.5 flex flex-wrap items-center justify-between gap-3">
                                    <div class="flex items-center gap-2.5">
                                        <svg class="w-5 h-5 text-blue-600 shrink-0" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" d="M19.5 14.25v-2.625a3.375 3.375 0 00-3.375-3.375h-1.5A1.125 1.125 0 0113.5 7.125v-1.5a3.375 3.375 0 00-3.375-3.375H8.25m0 12.75h7.5m-7.5 3H12M10.5 2.25H5.625c-.621 0-1.125.504-1.125 1.125v17.25c0 .621.504 1.125 1.125 1.125h12.75c.621 0 1.125-.504 1.125-1.125V11.25a9 9 0 00-9-9z"/>
                                        </svg>
                                        <h4 class="text-xs sm:text-sm font-bold text-slate-800">
                                            Daftar Unit Kompetensi: <span class="text-slate-900 font-extrabold">{{ $sk->nama_skema }}</span>
                                        </h4>
                                    </div>
                                    <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-xs font-bold bg-blue-50 text-blue-600 border border-blue-200/80 shadow-2xs">
                                        <svg class="w-3.5 h-3.5 stroke-[2.5]" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" d="M4.5 12.75l6 6 9-13.5"/>
                                        </svg>
                                        {{ $sk->unitKompetensi->count() }} Unit Terdaftar
                                    </span>
                                </div>

                                <!-- Tabel Unit -->
                                <div class="overflow-x-auto">
                                    <table class="w-full text-left text-xs">
                                        <thead class="bg-white text-slate-500 font-bold uppercase tracking-wider border-b border-slate-200/80">
                                            <tr>
                                                <th class="py-3.5 px-5 w-14 text-center font-bold text-slate-500">NO.</th>
                                                <th class="py-3.5 px-5 w-48 font-bold text-slate-500">KODE UNIT</th>
                                                <th class="py-3.5 px-5 font-bold text-slate-500">JUDUL UNIT KOMPETENSI</th>
                                                <th class="py-3.5 px-5 w-52 font-bold text-slate-500 text-right sm:text-left">STANDAR KOMPETENSI KERJA</th>
                                            </tr>
                                        </thead>
                                        <tbody class="divide-y divide-slate-100 text-slate-700 bg-white">
                                            @forelse($sk->unitKompetensi as $idx => $u)
                                                <tr class="hover:bg-slate-50/50 transition-colors">
                                                    <td class="py-3.5 px-5 text-center font-bold text-slate-500">{{ $idx + 1 }}</td>
                                                    <td class="py-3.5 px-5">
                                                        <span class="inline-block px-2.5 py-1 text-xs font-mono font-bold text-blue-600 bg-blue-50 border border-blue-100 rounded-lg shadow-2xs">
                                                            {{ $u->kode_unit }}
                                                        </span>
                                                    </td>
                                                    <td class="py-3.5 px-5 font-bold text-slate-800 leading-snug">{{ $u->judul_unit }}</td>
                                                    <td class="py-3.5 px-5 text-right sm:text-left">
                                                        <span class="inline-block px-2.5 py-1 text-[11px] font-bold tracking-wider text-slate-500 bg-slate-100/90 border border-slate-200 rounded-md">
                                                            {{ $u->standar_kompetensi ?? 'KKNI' }}
                                                        </span>
                                                    </td>
                                                </tr>
                                            @empty
                                                <tr>
                                                    <td colspan="4" class="py-5 text-center text-slate-400 italic">Belum ada unit kompetensi pada skema ini.</td>
                                                </tr>
                                            @endforelse
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        @endforeach
                    </div>

                    <!-- Section Header 3: Bukti Persyaratan Dasar -->
                    <div class="border-t border-slate-200/80 pt-6" id="subseksi-bukti-persyaratan">
                        <div class="text-blue-600 font-bold text-xs uppercase tracking-wider">
                            Bagian 3 &bull; Bukti Persyaratan Dasar (Portofolio)
                        </div>
                        <h2 class="text-xl font-bold text-slate-900 mt-1">Berkas Persyaratan Asesi</h2>
                        <p class="text-slate-500 text-xs mt-0.5">Format yang didukung: PDF, JPG, PNG, WEBP. Ukuran maksimal 5MB per berkas.</p>
                    </div>

                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-5">
                        @php
                            $dokumenRapor = $pendaftaran?->dokumen?->firstWhere('jenis_dokumen', 'Ijazah / Rapor Terakhir');
                            $dokumenPkl = $pendaftaran?->dokumen?->firstWhere('jenis_dokumen', 'Portofolio Sertifikat/Karya');
                            $dokumenKtp = $pendaftaran?->dokumen?->firstWhere('jenis_dokumen', 'KTP / Kartu Pelajar');
                            $dokumenFoto = $pendaftaran?->dokumen?->firstWhere('jenis_dokumen', 'Pasfoto 3x4 Background Merah');

                            $isInvalidKtp = ($dokumenKtp && $dokumenKtp->status_verifikasi === 'tidak_valid');
                            $isInvalidRapor = ($dokumenRapor && $dokumenRapor->status_verifikasi === 'tidak_valid');
                            $isInvalidPkl = ($dokumenPkl && $dokumenPkl->status_verifikasi === 'tidak_valid');
                            $isInvalidFoto = ($dokumenFoto && $dokumenFoto->status_verifikasi === 'tidak_valid');
                        @endphp

                        <!-- 1. KTP / Kartu Pelajar -->
                        <div id="dokumen-ktp" class="bg-slate-50 border border-slate-200/80 rounded-xl p-4 space-y-2.5 transition-all duration-300">
                            <div class="flex items-center justify-between">
                                <label class="text-xs font-bold text-slate-800 uppercase tracking-wider">1. KTP / Kartu Pelajar</label>
                                @if($isAccAdmin)
                                    <span class="text-[10px] px-2 py-0.5 rounded-full bg-emerald-100 text-emerald-800 border border-emerald-200 font-bold flex items-center gap-1">
                                        <svg class="w-3 h-3 text-emerald-600" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"></path></svg>
                                        Terverifikasi Valid
                                    </span>
                                @elseif($isInvalidKtp)
                                    <span class="text-[10px] px-2.5 py-0.5 rounded-full bg-rose-600 text-white font-bold tracking-wide uppercase shadow-2xs">
                                        Perlu Revisi
                                    </span>
                                @elseif($dokumenKtp)
                                    <span class="text-[10px] px-2 py-0.5 rounded-full bg-emerald-50 text-emerald-700 border border-emerald-200 font-semibold">Terunggah</span>
                                @endif
                            </div>

                            @if($isInvalidKtp && !$isAccAdmin)
                                <div class="bg-rose-50 border border-rose-200 rounded-lg p-2.5 text-xs text-rose-900">
                                    <div class="font-bold flex items-center gap-1 text-rose-700 mb-0.5 text-[11px] uppercase tracking-wide">
                                        <svg class="w-3.5 h-3.5 text-rose-600 shrink-0" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M8.257 3.099c.765-1.36 2.722-1.36 3.486 0l5.58 9.92c.75 1.334-.213 2.98-1.742 2.98H4.42c-1.53 0-2.493-1.646-1.743-2.98l5.58-9.92zM11 13a1 1 0 11-2 0 1 1 0 012 0zm-1-8a1 1 0 00-1 1v3a1 1 0 002 0V6a1 1 0 00-1-1z" clip-rule="evenodd"></path></svg>
                                        Catatan Perbaikan Admin LSP:
                                    </div>
                                    <p class="text-xs leading-relaxed text-rose-800">{{ $dokumenKtp->catatan ?: 'Berkas ini ditandai Tidak Valid oleh Verifikator. Harap unggah file pengganti.' }}</p>
                                </div>
                            @endif

                            @if(!$isAccAdmin)
                                <input type="file" name="file_ktp" id="input_file_ktp" accept=".pdf,.jpg,.jpeg,.png,.webp"
                                       class="w-full text-xs text-slate-600 file:mr-3 file:py-1.5 file:px-3 file:rounded-lg file:border-0 file:text-xs file:font-semibold file:bg-blue-50 file:text-blue-700 hover:file:bg-blue-100">
                            @endif

                            @if($dokumenKtp)
                                @php
                                    $urlKtp = $dokumenKtp->url;
                                @endphp
                                <div class="text-xs text-slate-600 p-2.5 bg-white border border-slate-200 rounded-lg flex items-center justify-between gap-2 shadow-2xs">
                                    <div class="flex items-center gap-2 truncate">
                                        <svg class="w-4 h-4 text-emerald-600 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
                                        <span class="truncate font-medium text-slate-700">{{ $dokumenKtp->nama_dokumen }}</span>
                                    </div>
                                    <a href="{{ $urlKtp }}" target="_blank" class="px-2.5 py-1 bg-slate-100 hover:bg-slate-200 text-slate-700 font-semibold rounded-md text-[11px] shrink-0 transition-colors flex items-center gap-1">
                                        <span>Lihat</span>
                                        <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14"/></svg>
                                    </a>
                                </div>
                            @endif
                        </div>

                        <!-- 2. Rapor / Ijazah Terakhir -->
                        <div id="dokumen-rapor" class="bg-slate-50 border border-slate-200/80 rounded-xl p-4 space-y-2.5 transition-all duration-300">
                            <div class="flex items-center justify-between">
                                <label class="text-xs font-bold text-slate-800 uppercase tracking-wider">2. Rapor / Ijazah Terakhir</label>
                                @if($isAccAdmin)
                                    <span class="text-[10px] px-2 py-0.5 rounded-full bg-emerald-100 text-emerald-800 border border-emerald-200 font-bold flex items-center gap-1">
                                        <svg class="w-3 h-3 text-emerald-600" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"></path></svg>
                                        Terverifikasi Valid
                                    </span>
                                @elseif($isInvalidRapor)
                                    <span class="text-[10px] px-2.5 py-0.5 rounded-full bg-rose-600 text-white font-bold tracking-wide uppercase shadow-2xs">
                                        Perlu Revisi
                                    </span>
                                @elseif($dokumenRapor)
                                    <span class="text-[10px] px-2 py-0.5 rounded-full bg-emerald-50 text-emerald-700 border border-emerald-200 font-semibold">Terunggah</span>
                                @endif
                            </div>

                            @if($isInvalidRapor && !$isAccAdmin)
                                <div class="bg-rose-50 border border-rose-200 rounded-lg p-2.5 text-xs text-rose-900">
                                    <div class="font-bold flex items-center gap-1 text-rose-700 mb-0.5 text-[11px] uppercase tracking-wide">
                                        <svg class="w-3.5 h-3.5 text-rose-600 shrink-0" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M8.257 3.099c.765-1.36 2.722-1.36 3.486 0l5.58 9.92c.75 1.334-.213 2.98-1.742 2.98H4.42c-1.53 0-2.493-1.646-1.743-2.98l5.58-9.92zM11 13a1 1 0 11-2 0 1 1 0 012 0zm-1-8a1 1 0 00-1 1v3a1 1 0 002 0V6a1 1 0 00-1-1z" clip-rule="evenodd"></path></svg>
                                        Catatan Perbaikan Admin LSP:
                                    </div>
                                    <p class="text-xs leading-relaxed text-rose-800">{{ $dokumenRapor->catatan ?: 'Berkas ini ditandai Tidak Valid oleh Verifikator. Harap unggah file pengganti.' }}</p>
                                </div>
                            @endif

                            @if(!$isAccAdmin)
                                <input type="file" name="file_rapor" id="input_file_rapor" accept=".pdf,.jpg,.jpeg,.png,.webp"
                                       class="w-full text-xs text-slate-600 file:mr-3 file:py-1.5 file:px-3 file:rounded-lg file:border-0 file:text-xs file:font-semibold file:bg-blue-50 file:text-blue-700 hover:file:bg-blue-100">
                            @endif

                            @if($dokumenRapor)
                                @php
                                    $urlRapor = $dokumenRapor->url;
                                @endphp
                                <div class="text-xs text-slate-600 p-2.5 bg-white border border-slate-200 rounded-lg flex items-center justify-between gap-2 shadow-2xs">
                                    <div class="flex items-center gap-2 truncate">
                                        <svg class="w-4 h-4 text-emerald-600 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
                                        <span class="truncate font-medium text-slate-700">{{ $dokumenRapor->nama_dokumen }}</span>
                                    </div>
                                    <a href="{{ $urlRapor }}" target="_blank" class="px-2.5 py-1 bg-slate-100 hover:bg-slate-200 text-slate-700 font-semibold rounded-md text-[11px] shrink-0 transition-colors flex items-center gap-1">
                                        <span>Lihat</span>
                                        <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14"/></svg>
                                    </a>
                                </div>
                            @endif
                        </div>

                        <!-- 3. Sertifikat PKL / Pelatihan -->
                        <div id="dokumen-pkl" class="bg-slate-50 border border-slate-200/80 rounded-xl p-4 space-y-2.5 transition-all duration-300">
                            <div class="flex items-center justify-between">
                                <label class="text-xs font-bold text-slate-800 uppercase tracking-wider">3. Sertifikat PKL / Pelatihan</label>
                                @if($isAccAdmin)
                                    <span class="text-[10px] px-2 py-0.5 rounded-full bg-emerald-100 text-emerald-800 border border-emerald-200 font-bold flex items-center gap-1">
                                        <svg class="w-3 h-3 text-emerald-600" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"></path></svg>
                                        Terverifikasi Valid
                                    </span>
                                @elseif($isInvalidPkl)
                                    <span class="text-[10px] px-2.5 py-0.5 rounded-full bg-rose-600 text-white font-bold tracking-wide uppercase shadow-2xs">
                                        Perlu Revisi
                                    </span>
                                @elseif($dokumenPkl)
                                    <span class="text-[10px] px-2 py-0.5 rounded-full bg-emerald-50 text-emerald-700 border border-emerald-200 font-semibold">Terunggah</span>
                                @endif
                            </div>

                            @if($isInvalidPkl && !$isAccAdmin)
                                <div class="bg-rose-50 border border-rose-200 rounded-lg p-2.5 text-xs text-rose-900">
                                    <div class="font-bold flex items-center gap-1 text-rose-700 mb-0.5 text-[11px] uppercase tracking-wide">
                                        <svg class="w-3.5 h-3.5 text-rose-600 shrink-0" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M8.257 3.099c.765-1.36 2.722-1.36 3.486 0l5.58 9.92c.75 1.334-.213 2.98-1.742 2.98H4.42c-1.53 0-2.493-1.646-1.743-2.98l5.58-9.92zM11 13a1 1 0 11-2 0 1 1 0 012 0zm-1-8a1 1 0 00-1 1v3a1 1 0 002 0V6a1 1 0 00-1-1z" clip-rule="evenodd"></path></svg>
                                        Catatan Perbaikan Admin LSP:
                                    </div>
                                    <p class="text-xs leading-relaxed text-rose-800">{{ $dokumenPkl->catatan ?: 'Berkas ini ditandai Tidak Valid oleh Verifikator. Harap unggah file pengganti.' }}</p>
                                </div>
                            @endif

                            @if(!$isAccAdmin)
                                <input type="file" name="file_pkl" id="input_file_pkl" accept=".pdf,.jpg,.jpeg,.png,.webp"
                                       class="w-full text-xs text-slate-600 file:mr-3 file:py-1.5 file:px-3 file:rounded-lg file:border-0 file:text-xs file:font-semibold file:bg-blue-50 file:text-blue-700 hover:file:bg-blue-100">
                            @endif

                            @if($dokumenPkl)
                                @php
                                    $urlPkl = $dokumenPkl->url;
                                @endphp
                                <div class="text-xs text-slate-600 p-2.5 bg-white border border-slate-200 rounded-lg flex items-center justify-between gap-2 shadow-2xs">
                                    <div class="flex items-center gap-2 truncate">
                                        <svg class="w-4 h-4 text-emerald-600 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
                                        <span class="truncate font-medium text-slate-700">{{ $dokumenPkl->nama_dokumen }}</span>
                                    </div>
                                    <a href="{{ $urlPkl }}" target="_blank" class="px-2.5 py-1 bg-slate-100 hover:bg-slate-200 text-slate-700 font-semibold rounded-md text-[11px] shrink-0 transition-colors flex items-center gap-1">
                                        <span>Lihat</span>
                                        <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14"/></svg>
                                    </a>
                                </div>
                            @endif
                        </div>

                        <!-- 4. Pasfoto 3x4 Background Merah -->
                        <div id="dokumen-foto" class="bg-slate-50 border border-slate-200/80 rounded-xl p-4 space-y-2.5 transition-all duration-300">
                            <div class="flex items-center justify-between">
                                <label class="text-xs font-bold text-slate-800 uppercase tracking-wider">4. Pasfoto 3x4 (Background Merah)</label>
                                @if($isAccAdmin)
                                    <span class="text-[10px] px-2 py-0.5 rounded-full bg-emerald-100 text-emerald-800 border border-emerald-200 font-bold flex items-center gap-1">
                                        <svg class="w-3 h-3 text-emerald-600" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"></path></svg>
                                        Terverifikasi Valid
                                    </span>
                                @elseif($isInvalidFoto)
                                    <span class="text-[10px] px-2.5 py-0.5 rounded-full bg-rose-600 text-white font-bold tracking-wide uppercase shadow-2xs">
                                        Perlu Revisi
                                    </span>
                                @elseif($dokumenFoto)
                                    <span class="text-[10px] px-2 py-0.5 rounded-full bg-emerald-50 text-emerald-700 border border-emerald-200 font-semibold">Terunggah</span>
                                @endif
                            </div>

                            @if($isInvalidFoto && !$isAccAdmin)
                                <div class="bg-rose-50 border border-rose-200 rounded-lg p-2.5 text-xs text-rose-900">
                                    <div class="font-bold flex items-center gap-1 text-rose-700 mb-0.5 text-[11px] uppercase tracking-wide">
                                        <svg class="w-3.5 h-3.5 text-rose-600 shrink-0" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M8.257 3.099c.765-1.36 2.722-1.36 3.486 0l5.58 9.92c.75 1.334-.213 2.98-1.742 2.98H4.42c-1.53 0-2.493-1.646-1.743-2.98l5.58-9.92zM11 13a1 1 0 11-2 0 1 1 0 012 0zm-1-8a1 1 0 00-1 1v3a1 1 0 002 0V6a1 1 0 00-1-1z" clip-rule="evenodd"></path></svg>
                                        Catatan Perbaikan Admin LSP:
                                    </div>
                                    <p class="text-xs leading-relaxed text-rose-800">{{ $dokumenFoto->catatan ?: 'Berkas ini ditandai Tidak Valid oleh Verifikator. Harap unggah file pengganti.' }}</p>
                                </div>
                            @endif

                            @if(!$isAccAdmin)
                                <input type="file" name="file_foto" id="input_file_foto" accept=".jpg,.jpeg,.png,.webp"
                                       class="w-full text-xs text-slate-600 file:mr-3 file:py-1.5 file:px-3 file:rounded-lg file:border-0 file:text-xs file:font-semibold file:bg-blue-50 file:text-blue-700 hover:file:bg-blue-100">
                            @endif

                            @if($dokumenFoto)
                                @php
                                    $urlFoto = $dokumenFoto->url;
                                @endphp
                                <div class="text-xs text-slate-600 p-2.5 bg-white border border-slate-200 rounded-lg flex items-center justify-between gap-2 shadow-2xs">
                                    <div class="flex items-center gap-2 truncate">
                                        <svg class="w-4 h-4 text-emerald-600 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
                                        <span class="truncate font-medium text-slate-700">{{ $dokumenFoto->nama_dokumen }}</span>
                                    </div>
                                    <a href="{{ $urlFoto }}" target="_blank" class="px-2.5 py-1 bg-slate-100 hover:bg-slate-200 text-slate-700 font-semibold rounded-md text-[11px] shrink-0 transition-colors flex items-center gap-1">
                                        <span>Lihat</span>
                                        <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14"/></svg>
                                    </a>
                                </div>
                            @endif
                        </div>
                    </div>

                    <!-- Digital Signature Pad -->
                    @if($isAccAdmin && !empty($draftData['tanda_tangan_asesi']))
                        <div class="border-t border-slate-200/80 pt-6">
                            <div class="flex items-center justify-between mb-3">
                                <div>
                                    <label class="text-xs font-bold text-slate-800 uppercase tracking-wider block">Tanda Tangan Digital Asesi</label>
                                    <span class="text-xs text-slate-500">Tanda tangan telah disahkan dan diverifikasi secara resmi.</span>
                                </div>
                                <span class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-full text-xs font-bold bg-emerald-50 text-emerald-700 border border-emerald-200">
                                    <svg class="w-3.5 h-3.5" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"></path></svg>
                                    Terverifikasi Sah
                                </span>
                            </div>

                            <div class="border border-slate-200 rounded-xl bg-slate-50/70 p-4 flex flex-col sm:flex-row items-center justify-between gap-4">
                                <div class="space-y-1 text-center sm:text-left">
                                    <span class="text-xs font-bold text-slate-800 block">{{ $pengguna->nama_lengkap }}</span>
                                    <span class="text-xs text-slate-500 block font-mono">NIK: {{ $draftData['nik'] ?? $profil->nik }}</span>
                                    @if($pendaftaran?->tanggal_ttd_asesi)
                                        <span class="text-[11px] text-slate-400 block font-medium">Ditandatangani: {{ date('d M Y', strtotime($pendaftaran->tanggal_ttd_asesi)) }}</span>
                                    @endif
                                </div>
                                <div class="p-2 bg-white rounded-lg border border-slate-200 shadow-2xs">
                                    <img src="{{ Str::startsWith($draftData['tanda_tangan_asesi'], 'data:') ? $draftData['tanda_tangan_asesi'] : asset($draftData['tanda_tangan_asesi']) }}" 
                                         alt="Tanda Tangan Asesi" class="max-h-24 max-w-xs object-contain">
                                </div>
                            </div>
                            <input type="hidden" name="tanda_tangan_asesi" value="{{ $draftData['tanda_tangan_asesi'] }}">
                        </div>
                    @else
                        <div class="border-t border-slate-200/80 pt-6" x-data="signaturePadComponent('canvasSignatureApl01', 'inputSignatureApl01')">
                            <div class="flex items-center justify-between mb-2">
                                <div>
                                    <label class="text-xs font-bold text-slate-800 uppercase tracking-wider block">Tanda Tangan Digital Asesi</label>
                                    <span class="text-xs text-slate-500">Gunakan mouse atau sentuhan layar pada kotak tanda tangan di bawah ini.</span>
                                </div>
                                <button type="button" @click="clearSignature()" class="text-xs text-red-600 hover:text-red-700 font-semibold px-2.5 py-1 bg-red-50 hover:bg-red-100 rounded-lg transition-colors">
                                    Reset
                                </button>
                            </div>

                            <div class="border border-slate-300 rounded-xl bg-white p-2 relative overflow-hidden">
                                <canvas id="canvasSignatureApl01" class="w-full h-36 bg-slate-50/50 rounded-lg cursor-crosshair"></canvas>
                                <input type="hidden" name="tanda_tangan_asesi" id="inputSignatureApl01" value="{{ $draftData['tanda_tangan_asesi'] }}">
                            </div>

                            @if(!empty($draftData['tanda_tangan_asesi']))
                                <div class="mt-2 text-xs text-slate-500 flex items-center gap-2">
                                    <span class="w-2 h-2 rounded-full bg-emerald-500"></span>
                                    <span>Tanda tangan tersimpan tersedia. Anda dapat menandatangani ulang jika ingin mengubah.</span>
                                </div>
                            @endif
                        </div>
                    @endif

                    @if($isAccAdmin)
                        <!-- Pengesahan & Tanda Tangan Admin LSP -->
                        <div class="border-t border-emerald-200/90 pt-6 bg-emerald-50/40 -mx-6 sm:-mx-8 px-6 sm:px-8 pb-4 rounded-b-2xl">
                            <div class="flex flex-col sm:flex-row items-start sm:items-center justify-between gap-4 border-b border-emerald-200/70 pb-4 mb-4">
                                <div>
                                    <div class="text-emerald-700 font-bold text-xs uppercase tracking-wider">
                                        Bukti Verifikasi & Pengesahan Admin LSP
                                    </div>
                                    <h3 class="text-base font-bold text-slate-900 mt-0.5">Rekomendasi Permohonan Sertifikasi (FR.APL.01)</h3>
                                </div>
                                <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-xs font-extrabold bg-emerald-600 text-white shadow-2xs">
                                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2.5"><path stroke-linecap="round" stroke-linejoin="round" d="M4.5 12.75l6 6 9-13.5"/></svg>
                                    Diterima Sebagai Peserta Sertifikasi
                                </span>
                            </div>

                            <div class="grid grid-cols-1 md:grid-cols-3 gap-4 text-xs">
                                <div class="bg-white p-4 rounded-xl border border-emerald-200/80 space-y-1.5 md:col-span-2">
                                    <span class="font-bold text-slate-700 uppercase tracking-wider block text-[11px]">Catatan Verifikator Admin LSP:</span>
                                    <p class="text-slate-600 italic leading-relaxed">
                                        "{{ $pendaftaran->catatan_verifikasi ?? 'Berkas persyaratan telah diperiksa lengkap, valid, dan memenuhi standar skema sertifikasi.' }}"
                                    </p>
                                    @if($pendaftaran->jadwal || $pendaftaran->asesor)
                                        <div class="pt-2 border-t border-slate-100 text-slate-600 space-y-0.5">
                                            @if($pendaftaran->asesor)
                                                <div><strong>Asesor Penguji:</strong> {{ $pendaftaran->asesor->nama_lengkap }}</div>
                                            @endif
                                            @if($pendaftaran->jadwal)
                                                <div><strong>Jadwal Uji:</strong> {{ date('d F Y', strtotime($pendaftaran->jadwal->tanggal_uji)) }} &bull; {{ $pendaftaran->jadwal->nama_tuk }}</div>
                                            @endif
                                        </div>
                                    @endif
                                </div>

                                <div class="bg-white p-4 rounded-xl border border-emerald-200/80 text-center flex flex-col items-center justify-between">
                                    <span class="font-bold text-slate-700 uppercase tracking-wider text-[11px] block">Tanda Tangan Admin LSP:</span>
                                    <div class="my-2">
                                        @if($pendaftaran->tanda_tangan_admin)
                                            <img src="{{ Str::startsWith($pendaftaran->tanda_tangan_admin, 'data:') ? $pendaftaran->tanda_tangan_admin : asset($pendaftaran->tanda_tangan_admin) }}" 
                                                 alt="TTD Admin LSP" class="max-h-16 max-w-full object-contain mx-auto">
                                        @else
                                            <span class="inline-block px-3 py-1 bg-emerald-50 text-emerald-700 rounded-md font-bold text-xs border border-emerald-200">
                                                ✓ Terverifikasi Sistem
                                            </span>
                                        @endif
                                    </div>
                                    <span class="text-[11px] text-slate-400 font-medium">
                                        Tanggal: {{ $pendaftaran->tanggal_ttd_admin ? date('d M Y', strtotime($pendaftaran->tanggal_ttd_admin)) : date('d M Y') }}
                                    </span>
                                </div>
                            </div>
                        </div>
                    @endif
                </div>

                <!-- Bottom Action Buttons Step 1 -->
                @if($isAccAdmin)
                    <div class="flex flex-col sm:flex-row items-center justify-between gap-4 pt-2">
                        <div class="flex items-center gap-2 text-xs text-emerald-800 font-medium">
                            <svg class="w-4 h-4 text-emerald-600 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M16.5 10.5V6.75a4.5 4.5 0 10-9 0v3.75m-.75 11.25h10.5a2.25 2.25 0 002.25-2.25v-6.75a2.25 2.25 0 00-2.25-2.25H6.75a2.25 2.25 0 00-2.25 2.25v6.75a2.25 2.25 0 002.25 2.25z" />
                            </svg>
                            <span>Data FR.APL.01 telah dikunci (Read-Only) karena telah disetujui oleh Admin LSP.</span>
                        </div>
                        <button type="button" @click="setStep({{ $isApl02Approved ? 3 : 2 }})" 
                                class="w-full sm:w-auto px-7 py-2.5 rounded-xl bg-blue-600 hover:bg-blue-700 text-white font-bold text-sm shadow-sm transition-all duration-150 flex items-center justify-center gap-2">
                            <span>Lanjut ke Formulir {{ $isApl02Approved ? 'FR.AK.01' : 'FR.APL.02' }}</span>
                            <span>&rarr;</span>
                        </button>
                    </div>
                @else
                    <div class="flex flex-col sm:flex-row items-center justify-between gap-3 pt-2">
                        <button type="submit" name="aksi" value="draft" 
                                class="w-full sm:w-auto px-6 py-2.5 rounded-xl border border-slate-300 text-slate-700 bg-white hover:bg-slate-50 font-semibold text-sm transition-all duration-150 flex items-center justify-center">
                            Simpan Draf
                        </button>
                        
                        @if($pendaftaran && $pendaftaran->status_pendaftaran === 'revisi')
                            <button type="submit" name="aksi" value="simpan_lanjut" 
                                    class="w-full sm:w-auto px-7 py-2.5 rounded-xl bg-orange-600 hover:bg-orange-700 text-white font-bold text-sm shadow-md transition-all duration-150 flex items-center justify-center gap-2">
                                <span>Ajukan Ulang Berkas Revisi ke Admin LSP</span>
                                <span>&rarr;</span>
                            </button>
                        @else
                            <button type="submit" name="aksi" value="simpan_lanjut" 
                                    class="w-full sm:w-auto px-7 py-2.5 rounded-xl bg-blue-600 hover:bg-blue-700 text-white font-semibold text-sm shadow-sm transition-all duration-150 flex items-center justify-center">
                                Simpan & Lanjut ke APL.02 &rarr;
                            </button>
                        @endif
                    </div>
                @endif
            </form>
        </div>

        <!-- =========================================================================
             STEP 2: FORMULIR FR.APL.02 (ASESMEN MANDIRI)
             ========================================================================= -->
        <div x-show="currentStep === 2" x-transition.opacity.duration.300ms class="mt-6">
            @if(!$pendaftaran || !$pendaftaran->skema)
                <div class="bg-white rounded-2xl border border-slate-200 p-8 text-center space-y-3 shadow-xs">
                    <div class="w-12 h-12 rounded-2xl bg-amber-50 text-amber-600 border border-amber-200/80 flex items-center justify-center mx-auto text-lg font-bold">
                        !
                    </div>
                    <h3 class="text-base font-bold text-slate-900">Belum Ada Permohonan Skema</h3>
                    <p class="text-xs text-slate-500 max-w-md mx-auto leading-relaxed">
                        Silakan lengkapi dan ajukan formulir permohonan FR.APL.01 terlebih dahulu untuk memuat daftar unit kompetensi skema Anda.
                    </p>
                    <button type="button" @click="setStep(1)" class="px-5 py-2.5 bg-blue-600 text-white rounded-xl text-xs font-bold hover:bg-blue-700 transition-colors">
                        &larr; Buka Formulir FR.APL.01
                    </button>
                </div>
            @elseif(!$isAccAdmin)
                <!-- =====================================================================
                     LOCKED STATE: MENUNGGU VERIFIKASI & ACC ADMIN LSP
                     ===================================================================== -->
                <div class="bg-white rounded-3xl border border-slate-200/90 p-7 sm:p-10 shadow-xs space-y-8">
                    <!-- Top Status Badge -->
                    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 border-b border-slate-100 pb-6">
                        <div class="space-y-1">
                            <div class="inline-flex items-center gap-2 px-3 py-1 bg-amber-50 text-amber-800 border border-amber-200 text-[11px] font-bold rounded-full uppercase tracking-wider">
                                <span class="w-2 h-2 rounded-full bg-amber-500 animate-pulse"></span> Menunggu Verifikasi Admin LSP
                            </div>
                            <h2 class="text-xl sm:text-2xl font-bold text-slate-900 tracking-tight">Formulir FR.APL.02 Belum Dapat Diisi</h2>
                        </div>
                        <span class="text-xs text-slate-400 font-mono">No. Reg: {{ $pendaftaran->nomor_pendaftaran }}</span>
                    </div>

                    <!-- Explanatory Box -->
                    <div class="bg-slate-50 border border-slate-200/80 rounded-2xl p-5 sm:p-6 space-y-3">
                        <h4 class="text-sm font-bold text-slate-800">Informasi Penting Verifikasi Administrasi:</h4>
                        <ul class="text-xs sm:text-sm text-slate-600 space-y-2 list-disc list-inside leading-relaxed">
                            <li>Proses verifikasi dokumen administrasi oleh Admin LSP memerlukan waktu 1x24 jam kerja.</li>
                            <li>Jika terdapat berkas yang buram atau tidak sesuai, Admin akan mengirimkan catatan revisi pada akun Anda.</li>
                            <li>Anda dapat memeriksa kembali rincian data permohonan yang telah diinputkan pada formulir FR.APL.01.</li>
                        </ul>
                    </div>

                    <!-- Action Footer -->
                    <div class="flex flex-col sm:flex-row items-center justify-between gap-3 pt-2">
                        <button type="button" @click="setStep(1)" 
                                class="w-full sm:w-auto px-6 py-3 rounded-xl border border-slate-300 text-slate-700 bg-white hover:bg-slate-50 font-semibold text-xs sm:text-sm transition-colors flex items-center justify-center gap-2">
                            &larr; Tinjau Kembali Data Permohonan (FR.APL.01)
                        </button>
                        
                        <div class="text-xs text-slate-400 italic">
                            Halaman akan otomatis dapat diisi setelah Admin menyetujui berkas.
                        </div>
                    </div>
                </div>
            @else
                <!-- =====================================================================
                     UNLOCKED STATE: MODERN & PROFESSIONAL FR.APL.02 FORM (STANDAR BNSP)
                     ===================================================================== -->
                <div class="flex items-center justify-end pb-3 pt-1 text-xs">
                    <button type="button" @click="setStep(1)" class="text-slate-500 hover:text-blue-600 font-medium inline-flex items-center gap-1.5 transition-colors cursor-pointer">
                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/></svg>
                        <span>Lihat Berkas FR.APL.01 (Read-Only)</span>
                    </button>
                </div>

                <form id="form-apl02-asesmen" action="{{ route('asesi.tahapan.apl02') }}" method="POST"
                      x-data="{
                        penilaianKuk: {
                            @if($pendaftaran && $pendaftaran->skema)
                                @foreach($pendaftaran->skema->unitKompetensi as $uData)
                                    @foreach($uData->elemenKompetensi as $eData)
                                        @php
                                            $jwn = $jawabanMap->get($eData->id);
                                            $valSaved = $jwn ? $jwn->nilai_kompetensi : 'K';
                                        @endphp
                                        @if($eData->kriteriaUnjukKerja && $eData->kriteriaUnjukKerja->count() > 0)
                                            @foreach($eData->kriteriaUnjukKerja as $kData)
                                                @php
                                                    $vKukItem = $verifikasiKukMap->get($kData->id);
                                                    if ($vKukItem) {
                                                        if ($isApl02Revision && (!$vKukItem->is_verified || !empty($vKukItem->catatan_asesor) || $vKukItem->nilai_kompetensi === 'BK')) {
                                                            $kukStatusVal = 'BK';
                                                        } else {
                                                            $kukStatusVal = $vKukItem->nilai_kompetensi ?: $valSaved;
                                                        }
                                                    } else {
                                                        $kukStatusVal = $valSaved;
                                                    }
                                                @endphp
                                                '{{ $kData->id }}': '{{ $kukStatusVal }}',
                                            @endforeach
                                        @else
                                            'elem_{{ $eData->id }}': '{{ $valSaved }}',
                                        @endif
                                    @endforeach
                                @endforeach
                            @endif
                        },
                        totalElemen: {{ $totalElemen ?? 0 }},
                        getElemenVal(elemenId, kukIds) {
                            if (!kukIds || kukIds.length === 0) {
                                return this.penilaianKuk['elem_' + elemenId] || 'K';
                            }
                            for (let id of kukIds) {
                                if (this.penilaianKuk[id] === 'BK') {
                                    return 'BK';
                                }
                            }
                            return 'K';
                        },
                        updateKuk(kukKey, val) {
                            @if($isApl02Draft || $isApl02Revision)
                                this.penilaianKuk[kukKey] = val;
                            @endif
                        },
                        get totalTerisi() {
                            return this.totalElemen;
                        },
                        get isComplete() {
                            return true;
                        },
                        setAllKGlobal() {
                            @if($isApl02Draft || $isApl02Revision)
                                Object.keys(this.penilaianKuk).forEach(key => {
                                    this.penilaianKuk[key] = 'K';
                                });
                                this.$el.querySelectorAll('.apl02-radio-k').forEach(el => {
                                    el.checked = true;
                                });
                            @endif
                        },
                        setAllKUnit(unitId) {
                            @if($isApl02Draft || $isApl02Revision)
                                this.$el.querySelectorAll('input[data-unit=\'' + unitId + '\'][value=\'K\']').forEach(el => {
                                    el.checked = true;
                                    const key = el.getAttribute('data-kuk-key');
                                    if (key) {
                                        this.penilaianKuk[key] = 'K';
                                    }
                                });
                            @endif
                        }
                      }" 
                      class="space-y-6">
                    @csrf
                    <input type="hidden" name="pendaftaran_id" value="{{ $pendaftaran->id }}">

                    <!-- =================================================================
                         STATUS CARD WORKFLOW FR.APL.02
                         ================================================================= -->
                    @if($statusApl02 === 'draft')
                        <!-- DRAFT BOX -->
                        <div class="bg-blue-50/80 border border-blue-200 rounded-2xl p-5 sm:p-6 shadow-xs flex flex-col sm:flex-row items-start sm:items-center justify-between gap-4">
                            <div class="flex items-start gap-3.5">
                                <div class="w-10 h-10 rounded-xl bg-blue-600 text-white flex items-center justify-center font-bold text-xs shrink-0 shadow-xs">
                                    APL
                                </div>
                                <div class="space-y-1">
                                    <div class="flex items-center gap-2">
                                        <h3 class="font-bold text-slate-900 text-sm">Status FR.APL.02: Belum Dikirim (Draft)</h3>
                                        <span class="px-2.5 py-0.5 rounded-full bg-blue-100 text-blue-800 text-[10px] font-bold uppercase tracking-wider">Draft</span>
                                    </div>
                                    <p class="text-xs text-slate-600 leading-relaxed">
                                        Lengkapi penilaian mandiri (K/BK) dan lampirkan bukti pendukung pada setiap elemen kompetensi, kemudian klik tombol <strong>Kirim APL.02 untuk Diperiksa</strong> di bagian bawah.
                                    </p>
                                </div>
                            </div>
                        </div>
                    @elseif($statusApl02 === 'submitted' || $statusApl02 === 'under_review')
                        <!-- SUBMITTED / UNDER REVIEW BOX -->
                        <div class="bg-amber-50 border border-amber-200 rounded-2xl p-5 sm:p-6 space-y-3 shadow-xs">
                            <div class="flex items-start gap-3.5">
                                <div class="w-10 h-10 rounded-xl bg-amber-600 text-white flex items-center justify-center font-bold text-xs shrink-0 shadow-xs">
                                    APL
                                </div>
                                <div class="space-y-1">
                                    <div class="flex items-center gap-2 flex-wrap">
                                        <h3 class="font-bold text-slate-900 text-sm">
                                            Status FR.APL.02: {{ $statusApl02 === 'under_review' ? 'Sedang Diperiksa Asesor' : 'Menunggu Pemeriksaan Asesor' }}
                                        </h3>
                                        <span class="px-2.5 py-0.5 rounded-full bg-amber-100 text-amber-800 text-[10px] font-bold uppercase tracking-wider">
                                            {{ $statusApl02 === 'under_review' ? 'Under Review' : 'Submitted' }}
                                        </span>
                                    </div>
                                    <p class="text-xs text-slate-600 leading-relaxed">
                                        Formulir FR.APL.02 telah berhasil dikirim{{ $pendaftaran->tanggal_submit_apl02 ? ' pada ' . $pendaftaran->tanggal_submit_apl02->format('d F Y H:i') : '' }} dan saat ini <strong>{{ $statusApl02 === 'under_review' ? 'sedang ditinjau oleh Asesor Penguji' : 'sedang mengantre pemeriksaan Asesor Penguji' }}</strong>. Isian formulir dikunci sementara untuk menjaga keaslian data.
                                    </p>
                                </div>
                            </div>
                        </div>
                    @elseif($statusApl02 === 'revision')
                        <!-- REVISION BOX WITH ASESOR NOTES -->
                        <div class="bg-rose-50 border-2 border-rose-300 rounded-2xl p-5 sm:p-6 space-y-4 shadow-xs">
                            <div class="flex items-start gap-3.5">
                                <div class="w-10 h-10 rounded-xl bg-rose-600 text-white flex items-center justify-center font-bold text-xs shrink-0 shadow-xs">
                                    REV
                                </div>
                                <div class="flex-1 min-w-0">
                                    <div class="flex items-center gap-2 flex-wrap">
                                        <h3 class="font-bold text-rose-950 text-sm">Status FR.APL.02: Perlu Revisi</h3>
                                        <span class="px-2.5 py-0.5 rounded-full bg-rose-600 text-white text-[10px] font-bold uppercase tracking-wider shadow-2xs">
                                            Action Required
                                        </span>
                                    </div>
                                    <p class="text-xs text-rose-800 mt-1 leading-relaxed">
                                        Asesor Penguji meminta Anda melakukan perbaikan pada isian penilaian mandiri atau bukti pendukung.
                                    </p>
                                </div>
                            </div>

                            <!-- Catatan Asesor -->
                            <div class="bg-white rounded-xl border border-rose-200 p-4 space-y-1.5 shadow-2xs">
                                <div class="text-[11px] font-bold text-rose-700 uppercase tracking-wider">
                                    Catatan & Arahan Perbaikan dari Asesor:
                                </div>
                                <div class="text-xs text-slate-800 font-medium leading-relaxed bg-rose-50/50 p-3 rounded-lg border border-rose-100 italic">
                                    "{{ $pendaftaran->catatan_peninjauan_asesor ?: 'Silakan lengkapi bukti pendukung dan periksa kembali penilaian mandiri pada elemen yang belum sesuai.' }}"
                                </div>
                                <div class="text-[11px] text-slate-400">
                                    Ditinjau oleh Asesor{{ $pendaftaran->tanggal_ttd_asesor ? ' pada: ' . $pendaftaran->tanggal_ttd_asesor->format('d F Y') : '' }}
                                </div>
                            </div>
                        </div>
                    @elseif($statusApl02 === 'rejected')
                        <!-- REJECTED BOX WITH ASESOR REASON -->
                        <div class="bg-rose-50 border-2 border-rose-400 rounded-2xl p-5 sm:p-6 space-y-4 shadow-xs">
                            <div class="flex items-start gap-3.5">
                                <div class="w-10 h-10 rounded-xl bg-rose-600 text-white flex items-center justify-center font-bold text-xs shrink-0 shadow-xs">
                                    TOLAK
                                </div>
                                <div class="flex-1 min-w-0">
                                    <div class="flex items-center gap-2 flex-wrap">
                                        <h3 class="font-bold text-rose-950 text-sm">Status FR.APL.02: Ditolak Asesor (Tidak Dapat Diterima)</h3>
                                        <span class="px-2.5 py-0.5 rounded-full bg-rose-600 text-white text-[10px] font-bold uppercase tracking-wider shadow-2xs">
                                            Ditolak
                                        </span>
                                    </div>
                                    <p class="text-xs text-rose-800 mt-1 leading-relaxed">
                                        Formulir FR.APL.02 Asesmen Mandiri Anda dinyatakan <strong>ditolak / tidak dapat diterima</strong> oleh Asesor Penguji. Permohonan asesmen untuk skema ini tidak dapat dilanjutkan.
                                    </p>
                                </div>
                            </div>

                            <!-- Alasan Penolakan Asesor -->
                            <div class="bg-white rounded-xl border border-rose-200 p-4 space-y-1.5 shadow-2xs">
                                <div class="text-[11px] font-bold text-rose-700 uppercase tracking-wider">
                                    Alasan Penolakan dari Asesor:
                                </div>
                                <div class="text-xs text-slate-800 font-medium leading-relaxed bg-rose-50/50 p-3 rounded-lg border border-rose-100 italic">
                                    "{{ $pendaftaran->catatan_peninjauan_asesor ?: 'Permohonan FR.APL.02 tidak memenuhi kriteria dan ditolak oleh Asesor Penguji.' }}"
                                </div>
                                @if($pendaftaran->tanggal_ttd_asesor)
                                    <div class="text-[11px] text-slate-400">
                                        Ditetapkan oleh Asesor pada: {{ $pendaftaran->tanggal_ttd_asesor->format('d F Y H:i') }}
                                    </div>
                                @endif
                            </div>
                        </div>
                    @elseif($statusApl02 === 'approved')
                        <!-- APPROVED BOX -->
                        <div class="bg-emerald-50 border border-emerald-300 rounded-2xl p-5 sm:p-6 space-y-3 shadow-xs">
                            <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
                                <div class="flex items-start gap-3.5">
                                    <div class="w-10 h-10 rounded-xl bg-emerald-600 text-white flex items-center justify-center font-bold text-xs shrink-0 shadow-xs">
                                        OK
                                    </div>
                                    <div class="space-y-1">
                                        <div class="flex items-center gap-2">
                                            <h3 class="font-bold text-emerald-950 text-sm">Status FR.APL.02: Disetujui (Approved)</h3>
                                            <span class="px-2.5 py-0.5 rounded-full bg-emerald-600 text-white text-[10px] font-bold uppercase tracking-wider">
                                                Disetujui
                                            </span>
                                        </div>
                                        <p class="text-xs text-emerald-800 leading-relaxed">
                                            Formulir FR.APL.02 Asesmen Mandiri Anda telah <strong>disetujui oleh Asesor Penguji</strong> (Rekomendasi: Asesmen DAPAT Dilanjutkan). Anda dapat melanjutkan ke Formulir <strong>FR.AK.01 Persetujuan Asesmen</strong>.
                                        </p>
                                    </div>
                                </div>
                                <div class="shrink-0">
                                    <button type="button" @click="setStep(3)" 
                                            class="px-5 py-2.5 rounded-xl bg-emerald-600 hover:bg-emerald-700 text-white font-bold text-xs shadow-xs transition-colors flex items-center gap-2">
                                        <span>Lanjut ke FR.AK.01</span>
                                        <span>&rarr;</span>
                                    </button>
                                </div>
                            </div>
                        </div>
                    @endif

                    <!-- Hero Card & Top Helper Bar -->
                    <div class="bg-white rounded-2xl border border-slate-200/90 p-5 sm:p-6 shadow-xs space-y-5">
                        <div class="flex flex-col md:flex-row md:items-center justify-between gap-4 border-b border-slate-100 pb-4">
                            <div class="space-y-1.5 min-w-0 flex-1">
                                <div class="inline-flex items-center gap-2 px-2.5 py-0.5 bg-emerald-50 text-emerald-800 border border-emerald-200 text-[11px] font-bold rounded-full uppercase tracking-wider">
                                    <span class="w-2 h-2 rounded-full bg-emerald-600"></span> FR.APL.02 &bull; Asesmen Mandiri
                                </div>
                                <h2 class="text-lg sm:text-xl font-bold text-slate-900 tracking-tight leading-snug">
                                    {{ $pendaftaran->skema->nama_skema }}
                                </h2>
                                <p class="text-xs text-slate-500">
                                    Evaluasi mandiri pencapaian kompetensi pada <strong>{{ $pendaftaran->skema->unitKompetensi->count() }} Unit Kompetensi</strong> (Total {{ $totalElemen }} Elemen).
                                </p>
                            </div>

                            <!-- Quick Action Button 'Pilih Semua K' -->
                            @if($isApl02Draft || $isApl02Revision)
                                <div class="shrink-0">
                                    <button type="button" @click="setAllKGlobal()" 
                                            class="w-full sm:w-auto px-4 py-2 bg-emerald-600 hover:bg-emerald-700 text-white rounded-xl text-xs font-bold shadow-2xs transition-all flex items-center justify-center gap-2">
                                        <span>✓</span>
                                        <span>Pilih Semua K (Kompeten)</span>
                                    </button>
                                </div>
                            @endif
                        </div>

                        <!-- Panduan Penilaian Ringkas & Bersih -->
                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                            <div class="p-3 rounded-xl border border-emerald-200/80 bg-emerald-50/40 flex items-start gap-2.5">
                                <span class="w-5 h-5 rounded-md bg-emerald-600 text-white text-[11px] font-bold flex items-center justify-center shrink-0 mt-0.5">K</span>
                                <div class="text-xs text-emerald-950">
                                    <strong>Kompeten (K)</strong>: Mampu mendemonstrasikan kriteria unjuk kerja secara mandiri sesuai standar industri.
                                </div>
                            </div>

                            <div class="p-3 rounded-xl border border-rose-200/80 bg-rose-50/40 flex items-start gap-2.5">
                                <span class="w-5 h-5 rounded-md bg-rose-600 text-white text-[11px] font-bold flex items-center justify-center shrink-0 mt-0.5">BK</span>
                                <div class="text-xs text-rose-950">
                                    <strong>Belum Kompeten (BK)</strong>: Belum menguasai seluruh kriteria unjuk kerja dan masih memerlukan bimbingan.
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Accordion Unit Kompetensi -->
                    <div class="space-y-4">
                        @forelse($pendaftaran->skema->unitKompetensi as $indexUnit => $unit)
                            @php
                                $isUnitNeedRevision = false;
                                if ($isApl02Revision) {
                                    $pUnit = $penilaianUnitMap->get($unit->id);
                                    if ($pUnit && $pUnit->nilai_kompetensi === 'BK') {
                                        $isUnitNeedRevision = true;
                                    } else {
                                        foreach ($unit->elemenKompetensi as $e) {
                                            foreach ($e->kriteriaUnjukKerja as $k) {
                                                $vK = $verifikasiKukMap->get($k->id);
                                                if ($vK && (!$vK->is_verified || !empty($vK->catatan_asesor))) {
                                                    $isUnitNeedRevision = true;
                                                    break 2;
                                                }
                                            }
                                        }
                                    }
                                }
                            @endphp
                            <div class="bg-white rounded-2xl border shadow-xs overflow-hidden transition-all {{ $isUnitNeedRevision ? 'border-rose-300 border-l-4 border-l-rose-500' : 'border-slate-200/90' }}" 
                                 x-data="{ openUnit: {{ ($isUnitNeedRevision || !$isApl02Revision) ? 'true' : 'false' }} }">
                                <!-- Unit Header Bar -->
                                <div class="px-4 sm:px-6 py-3.5 border-b flex flex-col sm:flex-row sm:items-center justify-between gap-3 cursor-pointer select-none transition-colors {{ $isUnitNeedRevision ? 'bg-rose-50/50 border-rose-100' : 'bg-slate-50/90 border-slate-200/80' }}"
                                     @click="openUnit = !openUnit">
                                    <div class="space-y-1 min-w-0 flex-1 pr-2">
                                        <div class="flex items-center gap-2 flex-wrap">
                                            <span class="inline-flex items-center gap-1.5 px-2.5 py-0.5 rounded-md bg-blue-50 text-blue-700 font-mono text-[11px] font-bold uppercase tracking-wider border border-blue-100">
                                                Unit {{ $indexUnit + 1 }} &bull; {{ $unit->kode_unit }}
                                            </span>
                                            @if($isApl02Revision)
                                                @if($isUnitNeedRevision)
                                                    <span class="px-2 py-0.5 rounded bg-rose-100 text-rose-800 text-[10px] font-bold">
                                                        ⚠️ Belum Kompeten (BK) &bull; Perlu Revisi
                                                    </span>
                                                @else
                                                    <span class="px-2 py-0.5 rounded bg-emerald-100 text-emerald-800 text-[10px] font-bold">
                                                        ✓ Kompeten (K)
                                                    </span>
                                                @endif
                                            @endif
                                        </div>
                                        <h3 class="font-bold text-slate-800 text-xs sm:text-sm leading-snug">{{ $unit->judul_unit }}</h3>
                                    </div>
                                    <div class="flex items-center gap-2.5 shrink-0 self-start sm:self-center" @click.stop>
                                        @if($isApl02Draft || $isApl02Revision)
                                            <button type="button" 
                                                    @click="setAllKUnit('{{ $unit->id }}')"
                                                    class="text-xs bg-white hover:bg-emerald-50 text-emerald-700 border border-emerald-200 hover:border-emerald-300 px-3 py-1.5 rounded-lg font-semibold shadow-2xs transition-colors">
                                                Pilih Semua K pada Unit Ini
                                            </button>
                                        @endif
                                        <button type="button" 
                                                @click="openUnit = !openUnit" 
                                                class="text-slate-500 hover:text-slate-700 text-xs font-semibold px-2 py-1 rounded-lg hover:bg-slate-200/60 transition-colors flex items-center gap-1">
                                            <span x-text="openUnit ? 'Tutup' : 'Buka'"></span>
                                            <svg class="w-4 h-4 transition-transform duration-200" :class="{ 'rotate-180': openUnit }" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path></svg>
                                        </button>
                                    </div>
                                </div>

                                <!-- Unit Elements & KUK Table Matrix -->
                                <div x-show="openUnit" x-transition.opacity.duration.200ms class="p-4 sm:p-5 space-y-4">
                                    @forelse($unit->elemenKompetensi as $idxElem => $elemen)
                                        @php
                                            $jawaban = $jawabanMap->get($elemen->id);
                                            $nilaiSaved = $jawaban ? $jawaban->nilai_kompetensi : 'K';
                                            $listBukti = $buktiApl02Map->get($elemen->id, collect());
                                            $isLockedForm = ($isApl02Approved || $isApl02Submitted || $isApl02UnderReview || $isApl02Rejected);

                                            // Kumpulkan catatan asesor per KUK pada elemen ini
                                            $catatanKukElemen = collect();
                                            if ($isApl02Revision) {
                                                foreach ($elemen->kriteriaUnjukKerja as $k) {
                                                    $vK = $verifikasiKukMap->get($k->id);
                                                    if ($vK && !empty($vK->catatan_asesor)) {
                                                        $catatanKukElemen->push([
                                                            'nomor_kuk' => $k->nomor_kuk ?: ($elemen->nomor_elemen . '.' . $loop->iteration),
                                                            'catatan' => $vK->catatan_asesor
                                                        ]);
                                                    }
                                                }
                                            }
                                        @endphp
                                        <div class="border rounded-xl overflow-hidden bg-white shadow-2xs {{ $catatanKukElemen->isNotEmpty() ? 'border-rose-300' : 'border-slate-200/90' }}">
                                            <!-- Elemen Header Separator Row -->
                                            <div class="px-4 py-2.5 border-b flex items-center justify-between gap-2 {{ $catatanKukElemen->isNotEmpty() ? 'bg-rose-50/60 border-rose-200' : 'bg-slate-100/80 border-slate-200/70' }}">
                                                <div class="flex items-center gap-2">
                                                    <span class="px-2 py-0.5 rounded bg-slate-200 text-slate-700 text-[10px] font-bold font-mono uppercase">
                                                        Elemen {{ $elemen->nomor_elemen ?? ($idxElem + 1) }}
                                                    </span>
                                                    <span class="font-bold text-slate-800 text-xs sm:text-sm">
                                                        {{ $elemen->nama_elemen }}
                                                    </span>
                                                </div>
                                            </div>

                                            <!-- Kotak Catatan Asesor Spesifik pada Elemen -->
                                            @if($isApl02Revision && $catatanKukElemen->isNotEmpty())
                                                <div class="bg-rose-50 border-b border-rose-200 p-3 text-xs text-rose-800 space-y-1">
                                                    <div class="font-bold text-rose-900">
                                                        <span>Catatan Asesor:</span>
                                                    </div>
                                                    <div class="space-y-0.5 pl-4">
                                                        @foreach($catatanKukElemen as $cKuk)
                                                            <div><strong class="text-rose-900">KUK {{ $cKuk['nomor_kuk'] }}:</strong> {{ $cKuk['catatan'] }}</div>
                                                        @endforeach
                                                    </div>
                                                </div>
                                            @endif

                                            <!-- KUK Table Rows -->
                                            @if($elemen->kriteriaUnjukKerja && $elemen->kriteriaUnjukKerja->count() > 0)
                                                <div class="divide-y divide-slate-100">
                                                    @foreach($elemen->kriteriaUnjukKerja as $idxKuk => $kuk)
                                                        @php
                                                            $vKuk = $verifikasiKukMap->get($kuk->id);
                                                            $isKukNeedRev = ($isApl02Revision && $vKuk && (!$vKuk->is_verified || !empty($vKuk->catatan_asesor) || $vKuk->nilai_kompetensi === 'BK'));
                                                        @endphp
                                                        <div class="px-4 py-2.5 flex flex-col sm:flex-row sm:items-center justify-between gap-3 transition-colors {{ $isKukNeedRev ? 'bg-rose-50/30' : 'hover:bg-slate-50/50' }}">
                                                            <div class="flex items-start gap-2.5 flex-1 min-w-0">
                                                                <span class="font-mono font-bold text-xs shrink-0 px-1.5 py-0.5 rounded text-[11px] {{ $isKukNeedRev ? 'bg-rose-100 text-rose-800 border border-rose-200' : 'bg-blue-50/70 text-blue-600 border border-blue-100' }}">
                                                                    {{ $kuk->nomor_kuk ?: ($elemen->nomor_elemen . '.' . ($idxKuk + 1)) }}{{ $isKukNeedRev ? ' • BK' : '' }}
                                                                </span>
                                                                <div class="space-y-1 min-w-0">
                                                                    <span class="text-xs text-slate-700 leading-relaxed block">
                                                                        {{ $kuk->pernyataan_kuk }}
                                                                    </span>
                                                                    @if($isKukNeedRev && !empty($vKuk->catatan_asesor))
                                                                        <div class="text-[11px] text-rose-700 font-medium">
                                                                            Catatan: {{ $vKuk->catatan_asesor }}
                                                                        </div>
                                                                    @endif
                                                                </div>
                                                            </div>

                                                            <!-- Segmented Control for K / BK (Independen Per Butir KUK) -->
                                                            <div class="shrink-0 flex items-center gap-1.5 self-end sm:self-center">
                                                                <div class="inline-flex rounded-lg bg-slate-100 p-0.5 border border-slate-200/80 gap-1">
                                                                    <label class="{{ $isLockedForm ? 'cursor-not-allowed opacity-80' : 'cursor-pointer' }}">
                                                                        <input type="radio" 
                                                                               id="penilaian_kuk_{{ $kuk->id }}_k" 
                                                                               name="penilaian_kuk[{{ $kuk->id }}]" 
                                                                               value="K" 
                                                                               data-unit="{{ $unit->id }}"
                                                                               data-kuk-key="{{ $kuk->id }}"
                                                                               @change="updateKuk('{{ $kuk->id }}', 'K')"
                                                                               :checked="penilaianKuk['{{ $kuk->id }}'] === 'K'" 
                                                                               {{ $isLockedForm ? 'disabled' : '' }}
                                                                               class="peer hidden apl02-radio-k">
                                                                        <span class="inline-flex items-center justify-center px-3 py-1 rounded-md text-xs transition-all duration-150 select-none border"
                                                                              :class="penilaianKuk['{{ $kuk->id }}'] === 'K' 
                                                                                  ? 'bg-emerald-600 text-white font-semibold shadow-xs border-emerald-600' 
                                                                                  : 'bg-slate-100 text-slate-600 hover:bg-slate-200 border-slate-200'">
                                                                            K
                                                                        </span>
                                                                    </label>
                                                                    <label class="{{ $isLockedForm ? 'cursor-not-allowed opacity-80' : 'cursor-pointer' }}">
                                                                        <input type="radio" 
                                                                               id="penilaian_kuk_{{ $kuk->id }}_bk" 
                                                                               name="penilaian_kuk[{{ $kuk->id }}]" 
                                                                               value="BK" 
                                                                               data-unit="{{ $unit->id }}"
                                                                               data-kuk-key="{{ $kuk->id }}"
                                                                               @change="updateKuk('{{ $kuk->id }}', 'BK')"
                                                                               :checked="penilaianKuk['{{ $kuk->id }}'] === 'BK'" 
                                                                               {{ $isLockedForm ? 'disabled' : '' }}
                                                                               class="peer hidden custom-radio-bk">
                                                                        <span class="inline-flex items-center justify-center px-3 py-1 rounded-md text-xs transition-all duration-150 select-none border"
                                                                              :class="penilaianKuk['{{ $kuk->id }}'] === 'BK' 
                                                                                  ? 'bg-rose-600 text-white font-semibold shadow-xs border-rose-600' 
                                                                                  : 'bg-slate-100 text-slate-600 hover:bg-slate-200 border-slate-200'">
                                                                            BK
                                                                        </span>
                                                                    </label>
                                                                </div>
                                                            </div>
                                                        </div>
                                                    @endforeach
                                                </div>
                                                @php
                                                    $kukIdsArr = implode(',', $elemen->kriteriaUnjukKerja->pluck('id')->toArray());
                                                @endphp
                                                <input type="hidden" name="penilaian[{{ $elemen->id }}]" :value="getElemenVal({{ $elemen->id }}, [{{ $kukIdsArr }}])">
                                            @else
                                                <div class="px-4 py-3 flex items-center justify-between gap-3 text-xs">
                                                    <span class="text-slate-600 font-medium">Beri penilaian mandiri untuk elemen ini</span>
                                                    <div class="inline-flex rounded-lg bg-slate-100 p-0.5 border border-slate-200/80 gap-1">
                                                        <label class="{{ $isLockedForm ? 'cursor-not-allowed opacity-80' : 'cursor-pointer' }}">
                                                            <input type="radio" 
                                                                   id="penilaian_elem_{{ $elemen->id }}_k" 
                                                                   name="penilaian[{{ $elemen->id }}]" 
                                                                   value="K" 
                                                                   data-unit="{{ $unit->id }}"
                                                                   data-kuk-key="elem_{{ $elemen->id }}"
                                                                   @change="updateKuk('elem_{{ $elemen->id }}', 'K')"
                                                                   :checked="penilaianKuk['elem_{{ $elemen->id }}'] === 'K'" 
                                                                   {{ $isLockedForm ? 'disabled' : '' }}
                                                                   class="peer hidden apl02-radio-k" required>
                                                            <span class="inline-flex items-center justify-center px-3 py-1 rounded-md text-xs transition-all duration-150 select-none border"
                                                                  :class="penilaianKuk['elem_{{ $elemen->id }}'] === 'K' 
                                                                      ? 'bg-emerald-600 text-white font-semibold shadow-xs border-emerald-600' 
                                                                      : 'bg-slate-100 text-slate-600 hover:bg-slate-200 border-slate-200'">
                                                                K
                                                            </span>
                                                        </label>
                                                        <label class="{{ $isLockedForm ? 'cursor-not-allowed opacity-80' : 'cursor-pointer' }}">
                                                            <input type="radio" 
                                                                   id="penilaian_elem_{{ $elemen->id }}_bk" 
                                                                   name="penilaian[{{ $elemen->id }}]" 
                                                                   value="BK" 
                                                                   data-unit="{{ $unit->id }}"
                                                                   data-kuk-key="elem_{{ $elemen->id }}"
                                                                   @change="updateKuk('elem_{{ $elemen->id }}', 'BK')"
                                                                   :checked="penilaianKuk['elem_{{ $elemen->id }}'] === 'BK'" 
                                                                   {{ $isLockedForm ? 'disabled' : '' }}
                                                                   class="peer hidden custom-radio-bk" required>
                                                            <span class="inline-flex items-center justify-center px-3 py-1 rounded-md text-xs transition-all duration-150 select-none border"
                                                                  :class="penilaianKuk['elem_{{ $elemen->id }}'] === 'BK' 
                                                                      ? 'bg-rose-600 text-white font-semibold shadow-xs border-rose-600' 
                                                                      : 'bg-slate-100 text-slate-600 hover:bg-slate-200 border-slate-200'">
                                                                BK
                                                            </span>
                                                        </label>
                                                    </div>
                                                </div>
                                            @endif

                                            <!-- =========================================================
                                                 KOMPONEN BUKTI PENDUKUNG (MULTI-FILE UPLOAD & APL.01)
                                                 ========================================================= -->
                                            <div class="bg-slate-50/70 p-3.5 border-t border-slate-200/70 space-y-2.5">
                                                <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-2">
                                                    <div>
                                                        <div class="font-bold text-slate-800 text-xs">
                                                            Bukti Pendukung
                                                        </div>
                                                        <p class="text-[11px] text-slate-500">
                                                            Upload dokumen atau file yang dapat mendukung pernyataan kompetensi Anda. (PDF, JPG, JPEG, PNG &bull; Maks. 10 MB)
                                                        </p>
                                                    </div>

                                                    @if($isApl02Draft || $isApl02Revision)
                                                        <div class="flex items-center gap-1.5 shrink-0 self-start sm:self-center">
                                                            <!-- Hidden File Input per Elemen -->
                                                            <input type="file" 
                                                                   id="file-input-elem-{{ $elemen->id }}" 
                                                                   accept=".pdf,.jpg,.jpeg,.png"
                                                                   class="hidden"
                                                                   onchange="handleUploadBukti(this, '{{ $elemen->id }}', '{{ $pendaftaran->id }}')">
                                                            
                                                            <!-- Tombol Upload Bukti Baru -->
                                                            <button type="button" 
                                                                    id="btn-upload-elem-{{ $elemen->id }}"
                                                                    onclick="triggerUploadBukti('{{ $elemen->id }}')"
                                                                    class="px-2.5 py-1.5 rounded-lg bg-blue-600 hover:bg-blue-700 text-white font-semibold text-xs shadow-2xs transition-colors flex items-center gap-1.5 cursor-pointer">
                                                                <span>Upload Bukti</span>
                                                            </button>

                                                            <!-- Tombol Pilih Bukti dari APL.01 -->
                                                            <button type="button" 
                                                                    id="btn-apl01-elem-{{ $elemen->id }}"
                                                                    onclick="bukaModalPilihApl01('{{ $elemen->id }}')"
                                                                    class="px-2.5 py-1.5 rounded-lg bg-white border border-slate-200 hover:bg-slate-100 text-slate-700 font-semibold text-xs shadow-2xs transition-colors flex items-center gap-1.5 cursor-pointer">
                                                                <span>Gunakan Bukti dari APL.01</span>
                                                            </button>
                                                        </div>
                                                    @endif
                                                </div>

                                                <!-- Upload Loading Indicator -->
                                                <div id="upload-loading-elem-{{ $elemen->id }}" class="hidden py-1">
                                                    <div class="flex items-center gap-2 text-xs text-blue-600 font-medium">
                                                        Mengunggah berkas bukti...
                                                    </div>
                                                </div>

                                                <!-- Daftar File Bukti Terunggah -->
                                                <div id="bukti-list-elem-{{ $elemen->id }}" class="space-y-1.5">
                                                    @foreach($listBukti as $b)
                                                        <div id="bukti-item-{{ $b->id }}" class="flex items-center justify-between p-2.5 rounded-lg border border-slate-200 bg-white hover:bg-slate-50/80 transition-colors text-xs gap-3">
                                                            <div class="flex items-start gap-2.5 min-w-0 flex-1">
                                                                @if($b->is_pdf)
                                                                    <div class="w-7 h-7 rounded-md bg-rose-50 text-rose-600 border border-rose-100 flex items-center justify-center font-bold text-xs shrink-0 mt-0.5">
                                                                        PDF
                                                                    </div>
                                                                @else
                                                                    <div class="w-7 h-7 rounded-md bg-blue-50 text-blue-600 border border-blue-100 flex items-center justify-center font-bold text-xs shrink-0 mt-0.5">
                                                                        IMG
                                                                    </div>
                                                                @endif
                                                                <div class="min-w-0 flex-1">
                                                                    <div class="font-semibold text-slate-800 truncate" title="{{ $b->nama_tampil }}">{{ $b->nama_tampil }}</div>
                                                                    <div class="text-[11px] text-slate-400 flex items-center gap-2 mt-0.5">
                                                                        <span>{{ $b->file_size_formatted }}</span>
                                                                        @if($b->sumber === 'apl01')
                                                                            <span class="px-1.5 py-0.2 rounded bg-blue-50 text-blue-700 text-[10px] font-medium border border-blue-200/60">APL.01</span>
                                                                        @endif
                                                                    </div>
                                                                </div>
                                                            </div>
                                                            <div class="flex items-center gap-1.5 shrink-0">
                                                                <button type="button" 
                                                                        onclick="bukaPratinjauBukti('{{ $b->url }}', '{{ addslashes($b->nama_tampil) }}', {{ $b->is_pdf ? 'true' : 'false' }})"
                                                                        class="px-2.5 py-1 rounded-md bg-white border border-slate-200 hover:bg-slate-100 text-slate-700 font-semibold text-xs shadow-2xs transition-colors flex items-center gap-1 cursor-pointer">
                                                                    Lihat
                                                                </button>
                                                                @if($isApl02Draft || $isApl02Revision)
                                                                    <button type="button" 
                                                                            onclick="hapusBukti('{{ $b->id }}', '{{ $elemen->id }}')"
                                                                            class="px-2 py-1 rounded-md bg-white border border-rose-200 hover:bg-rose-50 text-rose-600 font-semibold text-xs transition-colors cursor-pointer"
                                                                            title="Hapus Bukti">
                                                                        Hapus
                                                                    </button>
                                                                @endif
                                                            </div>
                                                        </div>
                                                    @endforeach
                                                </div>

                                                <!-- Status Kosong -->
                                                <div id="bukti-empty-elem-{{ $elemen->id }}" class="text-xs text-slate-400 italic py-1 {{ $listBukti->count() > 0 ? 'hidden' : '' }}">
                                                    Belum ada bukti yang dilampirkan.
                                                </div>
                                            </div>
                                        </div>
                                    @empty
                                        <div class="text-slate-400 text-xs text-center py-4">Belum ada rincian elemen kompetensi pada unit ini.</div>
                                    @endforelse
                                </div>
                            </div>
                        @empty
                            <div class="bg-white rounded-2xl border border-slate-200 p-8 text-center text-slate-500 text-sm">
                                Tidak ada unit kompetensi yang terdaftar pada skema ini.
                            </div>
                        @endforelse
                    </div>

                    <!-- Sticky Footer Bar / Ringkasan Progress Pengerjaan -->
                    <div class="sticky bottom-4 z-30 bg-white/95 backdrop-blur-md rounded-2xl border border-slate-200/90 p-4 shadow-lg flex flex-col sm:flex-row items-center justify-between gap-4">
                        <!-- Progress Counter -->
                        <div class="flex items-center gap-3">
                            <div class="text-xs text-slate-600">
                                Total Elemen Terisi: <strong class="text-slate-900 font-bold" x-text="totalTerisi">0</strong> / <span x-text="totalElemen">0</span>
                            </div>
                            <template x-if="isComplete">
                                <span class="px-2.5 py-0.5 rounded-full bg-emerald-50 text-emerald-700 border border-emerald-200 text-[11px] font-bold">
                                    ✓ Seluruh Elemen Dinilai
                                </span>
                            </template>
                            <template x-if="!isComplete">
                                <span class="px-2.5 py-0.5 rounded-full bg-amber-50 text-amber-700 border border-amber-200 text-[11px] font-bold">
                                    Belum Lengkap (Tersisa <span x-text="totalElemen - totalTerisi"></span> Elemen)
                                </span>
                            </template>
                        </div>

                        <!-- Action Buttons -->
                        <div class="flex items-center gap-3 w-full sm:w-auto">
                            <button type="button" @click="setStep(1)" 
                                    class="w-1/2 sm:w-auto px-4 py-2.5 rounded-xl border border-slate-300 text-slate-700 bg-white hover:bg-slate-50 font-semibold text-xs transition-colors flex items-center justify-center gap-1.5">
                                &larr; Kembali ke APL.01
                            </button>
                            
                            @if($isApl02Draft)
                                <button type="button" 
                                        onclick="bukaModalSubmitApl02()"
                                        class="w-1/2 sm:w-auto px-6 py-2.5 bg-blue-600 hover:bg-blue-700 text-white font-bold text-xs rounded-xl shadow-xs transition-all flex items-center justify-center gap-1.5 cursor-pointer">
                                    <span>Kirim APL.02 untuk Diperiksa</span>
                                </button>
                            @elseif($statusApl02 === 'revision')
                                <button type="button" 
                                        onclick="bukaModalSubmitApl02()"
                                        class="w-1/2 sm:w-auto px-4 py-2.5 bg-blue-600 hover:bg-blue-700 text-white font-medium text-sm rounded-xl shadow-xs transition-all flex items-center justify-center gap-1.5 cursor-pointer">
                                    <span>Ajukan Ulang FR.APL.02 ke Asesor</span>
                                </button>
                            @elseif($statusApl02 === 'submitted' || $statusApl02 === 'under_review')
                                <div class="w-1/2 sm:w-auto px-5 py-2.5 rounded-xl bg-amber-100 text-amber-900 font-bold text-xs flex items-center justify-center gap-1.5">
                                    <span>Menunggu Pemeriksaan Asesor</span>
                                </div>
                            @elseif($statusApl02 === 'approved')
                                <button type="button" @click="setStep({{ $isAk01Selesai ? 4 : 3 }})" 
                                        class="w-1/2 sm:w-auto px-6 py-2.5 bg-emerald-600 hover:bg-emerald-700 text-white font-bold text-xs rounded-xl shadow-xs transition-all flex items-center justify-center gap-1.5 cursor-pointer">
                                    <span>Lanjut ke {{ $isAk01Selesai ? 'FR.AK.07' : 'FR.AK.01' }}</span>
                                    <span>&rarr;</span>
                                </button>
                            @elseif($statusApl02 === 'rejected')
                                <div class="w-1/2 sm:w-auto px-5 py-2.5 rounded-xl bg-rose-100 text-rose-900 font-bold text-xs flex items-center justify-center gap-1.5">
                                    <span>FR.APL.02 Ditolak Asesor</span>
                                </div>
                            @endif
                        </div>
                    </div>
                </form>
            @endif
        </div>

        <!-- =========================================================================
             STEP 3: FORMULIR FR.AK.01 (PERSETUJUAN ASESMEN & KERAHASIAAN)
             ========================================================================= -->
        <div x-show="currentStep === 3" x-transition.opacity.duration.300ms class="mt-6">
            @if(!$pendaftaran || !$isApl02Approved)
                <div class="bg-white rounded-2xl border border-slate-200/80 shadow-xs p-8 text-center space-y-4">
                    <div class="w-14 h-14 rounded-2xl bg-amber-50 text-amber-600 border border-amber-200/70 flex items-center justify-center mx-auto text-xs font-bold uppercase tracking-wider">
                        Kunci
                    </div>
                    <div class="max-w-md mx-auto space-y-1.5">
                        <h3 class="text-base font-bold text-slate-800">Formulir FR.AK.01 Masih Terkunci</h3>
                        <p class="text-xs text-slate-500 leading-relaxed">
                            Formulir FR.AK.01 (Persetujuan Asesmen & Kerahasiaan) hanya dapat diakses setelah Formulir FR.APL.02 Asesmen Mandiri Anda diperiksa dan <strong>disetujui (Approved)</strong> oleh Asesor Penguji.
                        </p>
                    </div>
                    <div class="pt-2">
                        <button type="button" @click="setStep(2)" class="px-5 py-2.5 rounded-xl bg-blue-600 hover:bg-blue-700 text-white font-bold text-xs shadow-xs inline-flex items-center gap-2 cursor-pointer">
                            <span>Kembali ke FR.APL.02</span>
                            <span>&rarr;</span>
                        </button>
                    </div>
                </div>
            @else
                <div class="flex items-center justify-end pb-3 pt-1 text-xs">
                    <div class="flex items-center gap-3">
                        <button type="button" @click="setStep(1)" class="text-slate-500 hover:text-blue-600 font-medium inline-flex items-center gap-1 transition-colors cursor-pointer">
                            Lihat FR.APL.01
                        </button>
                        <span class="text-slate-300">&bull;</span>
                        <button type="button" @click="setStep(2)" class="text-slate-500 hover:text-blue-600 font-medium inline-flex items-center gap-1.5 transition-colors cursor-pointer">
                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/></svg>
                            <span>Lihat FR.APL.02</span>
                        </button>
                    </div>
                </div>

                @if(!empty($pendaftaran->tanda_tangan_asesi_ak01) || $isAk01Selesai)
                    <div class="bg-gradient-to-r from-blue-50/90 via-indigo-50/80 to-blue-50/90 border border-blue-200/80 rounded-2xl p-5 flex flex-col sm:flex-row sm:items-center justify-between gap-4 shadow-xs">
                        <div class="flex items-start sm:items-center gap-3.5">
                            <div class="w-10 h-10 rounded-xl bg-blue-600 text-white flex items-center justify-center font-bold text-xs shrink-0 shadow-xs">
                                AK.07
                            </div>
                            <div>
                                <div class="font-bold text-slate-800 text-sm flex items-center gap-2">
                                    <span>Formulir FR.AK.01 Telah Disetujui</span>
                                    <span class="inline-flex items-center px-2 py-0.5 rounded-full text-[10px] font-semibold bg-emerald-100 text-emerald-800">Selesai</span>
                                </div>
                                <div class="text-xs text-slate-600 mt-0.5">Tahap berikutnya: Lakukan penyesuaian asesmen dan tinjau kebutuhan khusus pada Formulir FR.AK.07.</div>
                            </div>
                        </div>
                        <button type="button" @click="setStep(4)" 
                           class="inline-flex items-center justify-center gap-2 px-5 py-2.5 rounded-xl bg-blue-600 hover:bg-blue-700 text-white text-xs font-bold shadow-xs transition-all shrink-0 hover:shadow-md cursor-pointer">
                            <span>Buka Formulir FR.AK.07</span>
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14 5l7 7m0 0l-7 7m7-7H3"/></svg>
                        </button>
                    </div>
                @endif

                <form id="form-ak01-asesi" action="{{ route('asesi.tahapan.ak01') }}" method="POST" class="space-y-6" onsubmit="return validasiAk01Sebelumkirim(event)">
                    @csrf
                    <input type="hidden" name="pendaftaran_id" value="{{ $pendaftaran->id }}">

                    <div class="bg-white rounded-2xl border border-slate-200/80 shadow-xs p-6 sm:p-8 space-y-8">
                        <!-- Section Header -->
                        <div class="border-b border-slate-200/80 pb-4">
                            <div class="text-blue-600 font-bold text-xs uppercase tracking-wider">
                                FR.AK.01 &bull; Persetujuan Asesmen & Kerahasiaan
                            </div>
                            <h2 class="text-xl font-bold text-slate-900 mt-1">Kesepakatan Pelaksanaan Asesmen</h2>
                            <p class="text-slate-500 text-xs mt-0.5">Tinjau kesepakatan metode asesmen, Tempat Uji Kompetensi (TUK), dan tanda tangani komitmen integritas.</p>
                        </div>

                        <!-- Card Rincian Kesepakatan Asesmen -->
                        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
                            <!-- Skema -->
                            <div class="bg-slate-50 border border-slate-200/80 rounded-xl p-4 space-y-1">
                                <span class="text-[11px] font-bold uppercase text-slate-400 tracking-wider">Skema Uji</span>
                                <div class="font-bold text-slate-800 text-xs sm:text-sm truncate" title="{{ $pendaftaran->skema->nama_skema ?? '-' }}">
                                    {{ $pendaftaran->skema->nama_skema ?? '-' }}
                                </div>
                            </div>

                            <!-- Jadwal Uji -->
                            <div class="bg-slate-50 border border-slate-200/80 rounded-xl p-4 space-y-1">
                                <span class="text-[11px] font-bold uppercase text-slate-400 tracking-wider">Jadwal Asesmen</span>
                                <div class="font-bold text-slate-800 text-xs sm:text-sm">
                                    {{ $pendaftaran->jadwal ? date('d F Y', strtotime($pendaftaran->jadwal->tanggal_uji)) : 'Menunggu Penjadwalan' }}
                                </div>
                            </div>

                            <!-- TUK -->
                            <div class="bg-slate-50 border border-slate-200/80 rounded-xl p-4 space-y-1">
                                <span class="text-[11px] font-bold uppercase text-slate-400 tracking-wider">Lokasi TUK</span>
                                <div class="font-bold text-slate-800 text-xs sm:text-sm">
                                    {{ $pendaftaran->jadwal->nama_tuk ?? 'TUK SMKN 1 Gunungputri' }}
                                </div>
                            </div>

                            <!-- Asesor Penguji -->
                            <div class="bg-slate-50 border border-slate-200/80 rounded-xl p-4 space-y-1">
                                <span class="text-[11px] font-bold uppercase text-slate-400 tracking-wider">Asesor Penguji</span>
                                <div class="font-bold text-slate-800 text-xs sm:text-sm truncate" title="{{ $pendaftaran->asesor->nama_lengkap ?? $pendaftaran->jadwal?->asesor?->nama_lengkap ?? '-' }}">
                                    {{ $pendaftaran->asesor->nama_lengkap ?? $pendaftaran->jadwal?->asesor?->nama_lengkap ?? 'Akan Ditetapkan' }}
                                </div>
                            </div>
                        </div>

                        @if(!empty($pendaftaran->tanda_tangan_asesor_ak01))
                            <div class="bg-indigo-50 border border-indigo-200/90 rounded-xl p-3.5 flex items-start gap-3 text-xs text-indigo-900">
                                <div class="w-8 h-8 rounded-lg bg-indigo-600 text-white flex items-center justify-center shrink-0 mt-0.5 shadow-2xs font-bold text-xs">
                                    AK
                                </div>
                                <div class="space-y-0.5 flex-1">
                                    <div class="font-bold text-slate-900 flex items-center gap-1.5">
                                        <span>Rencana Asesmen Telah Ditetapkan oleh Asesor</span>
                                        <span class="px-2 py-0.5 rounded-full bg-emerald-100 text-emerald-800 text-[10px] font-bold">✓ Terverifikasi</span>
                                    </div>
                                    <p class="text-slate-600 leading-relaxed text-[11px]">
                                        Asesor penguji telah menetapkan jenis Tempat Uji Kompetensi (TUK) dan rencana metode pengumpulan bukti untuk skema sertifikasi ini. Silakan tinjau atau sesuaikan rincian kesepakatan dan bubuhkan tanda tangan digital Anda di bawah.
                                    </p>
                                </div>
                            </div>
                        @else
                            <div class="bg-blue-50 border border-blue-200/90 rounded-xl p-3.5 flex items-start gap-3 text-xs text-blue-900">
                                <div class="w-8 h-8 rounded-lg bg-blue-600 text-white flex items-center justify-center shrink-0 mt-0.5 shadow-2xs font-bold text-xs">
                                    AK
                                </div>
                                <div class="space-y-0.5 flex-1">
                                    <div class="font-bold text-slate-900 flex items-center gap-1.5">
                                        <span>Persetujuan Asesmen & Kerahasiaan (FR.AK.01)</span>
                                        <span class="px-2 py-0.5 rounded-full bg-slate-100 text-slate-700 text-[10px] font-bold border border-slate-200">
                                            Ditetapkan Asesor / LSP
                                        </span>
                                    </div>
                                    <p class="text-slate-600 leading-relaxed text-[11px]">
                                        Rencana pelaksanaan asesmen (TUK dan metode bukti) telah ditetapkan oleh Asesor / LSP sesuai skema sertifikasi (read-only). Silakan periksa kesepakatan dan bubuhkan tanda tangan digital Anda untuk menyetujui.
                                    </p>
                                </div>
                            </div>
                        @endif

                        <!-- Pilihan Jenis TUK & Bukti Yang Dikumpulkan (Read-Only) -->
                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-6 text-sm">
                            <!-- Jenis TUK -->
                            <div class="space-y-2">
                                <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider flex items-center justify-between">
                                    <span>Jenis Tempat Uji Kompetensi (TUK)</span>
                                    <span class="text-slate-500 font-semibold text-[11px] normal-case bg-slate-100 px-2 py-0.5 rounded">Ditetapkan Asesor / LSP</span>
                                </label>
                                <input type="hidden" name="tuk_type" value="{{ old('tuk_type', $pendaftaran->tuk_type ?? '') }}">
                                <select disabled class="w-full bg-slate-100 border border-slate-200 rounded-xl px-3.5 py-2.5 text-slate-800 outline-hidden font-medium opacity-90 cursor-not-allowed">
                                    <option value="" {{ old('tuk_type', $pendaftaran->tuk_type) === null || old('tuk_type', $pendaftaran->tuk_type) === '' ? 'selected' : '' }}></option>
                                    <option value="Sewaktu" {{ old('tuk_type', $pendaftaran->tuk_type) === 'Sewaktu' ? 'selected' : '' }}>TUK Sewaktu (Sekolah/Mitra)</option>
                                    <option value="Tempat Kerja" {{ old('tuk_type', $pendaftaran->tuk_type) === 'Tempat Kerja' ? 'selected' : '' }}>TUK Tempat Kerja / Industri (DUDI)</option>
                                    <option value="Mandiri" {{ old('tuk_type', $pendaftaran->tuk_type) === 'Mandiri' ? 'selected' : '' }}>TUK Mandiri</option>
                                </select>
                                <p class="text-[11px] text-slate-500">Tempat pelaksanaan asesmen telah ditetapkan sesuai perencanaan asesmen.</p>
                            </div>

                            <!-- Metode Uji Yang Disepakati -->
                            <div class="space-y-2">
                                <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider flex items-center justify-between">
                                    <span>Metode Uji Asesmen Yang Disepakati</span>
                                    <span class="text-slate-500 font-semibold text-[11px] normal-case bg-slate-100 px-2 py-0.5 rounded">Ditetapkan Asesor / LSP</span>
                                </label>
                                @php
                                    $savedBukti = (array) ($pendaftaran->bukti_dikumpulkan ?? []);
                                @endphp
                                @foreach($savedBukti as $b)
                                    <input type="hidden" name="bukti_dikumpulkan[]" value="{{ $b }}">
                                @endforeach
                                <div class="grid grid-cols-1 gap-2 pt-1">
                                    <label class="flex items-center gap-2.5 text-xs text-slate-700 cursor-not-allowed opacity-85 select-none">
                                        <input type="checkbox" disabled value="Observasi Praktik Demonstrasi"
                                               {{ in_array('Uji Praktik / Observasi Demonstrasi', $savedBukti) || in_array('Observasi Praktik Demonstrasi', $savedBukti) ? 'checked' : '' }}
                                               class="rounded-sm text-blue-600 cursor-not-allowed border-slate-300">
                                        <span>Observasi Praktik Demonstrasi Kerja</span>
                                    </label>
                                    <label class="flex items-center gap-2.5 text-xs text-slate-700 cursor-not-allowed opacity-85 select-none">
                                        <input type="checkbox" disabled value="Uji Tertulis (CBT)"
                                               {{ in_array('Uji Tertulis (CBT)', $savedBukti) ? 'checked' : '' }}
                                               class="rounded-sm text-blue-600 cursor-not-allowed border-slate-300">
                                        <span>Uji Tertulis Online (CBT / FR.IA.05)</span>
                                    </label>
                                    <label class="flex items-center gap-2.5 text-xs text-slate-700 cursor-not-allowed opacity-85 select-none">
                                        <input type="checkbox" disabled value="Tanya Jawab Lisan"
                                               {{ in_array('Tanya Jawab Lisan', $savedBukti) ? 'checked' : '' }}
                                               class="rounded-sm text-blue-600 cursor-not-allowed border-slate-300">
                                        <span>Tanya Jawab Lisan / Wawancara (FR.IA.07)</span>
                                    </label>
                                    <label class="flex items-center gap-2.5 text-xs text-slate-700 cursor-not-allowed opacity-85 select-none">
                                        <input type="checkbox" disabled value="Verifikasi Portofolio"
                                               {{ in_array('Verifikasi Portofolio', $savedBukti) || in_array('Hasil Verifikasi Portofolio', $savedBukti) ? 'checked' : '' }}
                                               class="rounded-sm text-blue-600 cursor-not-allowed border-slate-300">
                                        <span>Verifikasi Portofolio / Berkas Pendukung</span>
                                    </label>
                                </div>

                                <div class="pt-2 border-t border-slate-100">
                                    <label class="block text-[11px] font-bold text-slate-600 uppercase tracking-wider mb-1">
                                        Bukti / Metode Lainnya:
                                    </label>
                                    <input type="hidden" name="bukti_dikumpulkan_lainnya" value="{{ old('bukti_dikumpulkan_lainnya', $pendaftaran->bukti_dikumpulkan_lainnya) }}">
                                    <div class="text-xs text-slate-700 bg-slate-100/80 rounded-lg px-3 py-2 border border-slate-200">
                                        {{ $pendaftaran->bukti_dikumpulkan_lainnya ?: 'Tidak ada catatan bukti lainnya.' }}
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Klausul Pernyataan Asesi -->
                        <div class="bg-slate-50 border border-slate-200/80 rounded-xl p-5 space-y-3">
                            <div class="font-bold text-xs uppercase tracking-wider text-slate-800">
                                Klausul Pernyataan Asesi & Kerahasiaan BNSP
                            </div>
                            <p class="text-xs text-slate-600 leading-relaxed">
                                &ldquo;Saya menyatakan bahwa data dan berkas yang saya berikan adalah benar dan valid. Saya setuju untuk mengikuti proses asesmen sesuai dengan skema, jadwal, dan metode yang disepakati, serta berkomitmen menjaga kerahasiaan seluruh materi uji kompetensi sesuai regulasi Badan Nasional Sertifikasi Profesi (BNSP).&rdquo;
                            </p>
                            <label class="flex items-center gap-2.5 text-xs font-semibold text-slate-800 cursor-pointer pt-1">
                                <input type="checkbox" required checked class="rounded-sm text-emerald-600 focus:ring-emerald-500 border-slate-300">
                                <span>Saya memahami dan menyetujui seluruh klausul pernyataan di atas.</span>
                            </label>
                        </div>

                        <!-- Tanda Tangan Asesor Preview jika sudah ada -->
                        @if(!empty($pendaftaran->tanda_tangan_asesor_ak01))
                            <div class="p-3.5 bg-slate-50 border border-slate-200 rounded-xl flex items-center justify-between">
                                <div class="flex items-center gap-3">
                                    <div class="w-10 h-10 rounded-lg bg-emerald-50 text-emerald-700 border border-emerald-200 flex items-center justify-center font-bold text-xs">
                                        SAH
                                    </div>
                                    <div>
                                        <span class="text-[11px] font-bold text-slate-700 uppercase block">Telah Disahkan oleh Asesor Penguji</span>
                                        <span class="text-xs font-semibold text-slate-900">{{ $pendaftaran->asesor->nama_lengkap ?? $pendaftaran->jadwal?->asesor?->nama_lengkap ?? 'Asesor Penguji' }}</span>
                                        <span class="text-[10px] text-slate-400 block">{{ $pendaftaran->tanggal_ttd_asesor_ak01 ? date('d M Y H:i', strtotime($pendaftaran->tanggal_ttd_asesor_ak01)) : '' }}</span>
                                    </div>
                                </div>
                                <div class="h-12 w-28 bg-white rounded border border-slate-200 p-1 flex items-center justify-center">
                                    <img src="{{ asset($pendaftaran->tanda_tangan_asesor_ak01) }}" alt="TTD Asesor" class="max-h-10 object-contain">
                                </div>
                            </div>
                        @endif

                        @php
                            $existingTtdTahapan = $pendaftaran->tanda_tangan_asesi_ak01 
                                ?: ($pendaftaran->tanda_tangan_asesi 
                                ?: ($draftData['tanda_tangan_asesi'] ?? (auth()->user()->tanda_tangan ?? null)));
                            $hasExistingTtdTahapan = !empty($existingTtdTahapan);
                            $existingTtdTahapanUrl = null;
                            if ($hasExistingTtdTahapan) {
                                $existingTtdTahapanUrl = \Illuminate\Support\Str::startsWith($existingTtdTahapan, ['data:', 'http://', 'https://'])
                                    ? $existingTtdTahapan
                                    : asset($existingTtdTahapan);
                            }
                        @endphp

                        <!-- Signature Pad Step 3 (Asesi) with Auto-Fill & Edit -->
                        <div class="border-t border-slate-200/80 pt-6" 
                             x-data="{ 
                                isEditing: {{ $hasExistingTtdTahapan ? 'false' : 'true' }},
                                hasSavedTtd: {{ $hasExistingTtdTahapan ? 'true' : 'false' }},
                                savedTtd: '{{ $existingTtdTahapan }}',
                                padComp: signaturePadComponent('canvasSignatureAk01', 'inputSignatureAk01'),
                                init() {
                                    this.padComp.init();
                                    if (this.hasSavedTtd) {
                                        this.$nextTick(() => updateAk01SubmitButton());
                                    }
                                },
                                enableEdit() {
                                    this.isEditing = true;
                                    const input = document.getElementById('inputSignatureAk01');
                                    if (input) input.value = '';
                                    this.$nextTick(() => {
                                        if (this.padComp.engine) {
                                            this.padComp.engine.clear();
                                            this.padComp.engine.resize();
                                        }
                                        updateAk01SubmitButton();
                                    });
                                },
                                cancelEdit() {
                                    this.isEditing = false;
                                    const input = document.getElementById('inputSignatureAk01');
                                    if (input) input.value = this.savedTtd;
                                    if (this.padComp.engine) {
                                        this.padComp.engine.clear();
                                    }
                                    this.$nextTick(() => updateAk01SubmitButton());
                                },
                                clearCanvas() {
                                    this.padComp.clearSignature();
                                }
                             }">

                            <div class="flex items-center justify-between mb-2">
                                <div>
                                    <label class="text-xs font-bold text-slate-800 uppercase tracking-wider block">Tanda Tangan Digital Asesi</label>
                                    <span class="text-xs text-slate-500">Bukti persetujuan sah rencana asesmen FR.AK.01.</span>
                                </div>
                                <div class="flex items-center gap-2">
                                    <template x-if="!isEditing && hasSavedTtd">
                                        <button type="button" @click="enableEdit()" class="text-xs text-blue-600 hover:text-blue-700 font-semibold px-2.5 py-1 bg-blue-50 hover:bg-blue-100 rounded-lg transition-colors inline-flex items-center gap-1 cursor-pointer">
                                            <span>Ubah Tanda Tangan</span>
                                        </button>
                                    </template>
                                    <template x-if="isEditing">
                                        <div class="flex items-center gap-1.5">
                                            <button type="button" @click="clearCanvas()" class="text-xs text-red-600 hover:text-red-700 font-semibold px-2.5 py-1 bg-red-50 hover:bg-red-100 rounded-lg transition-colors cursor-pointer">
                                                Hapus
                                            </button>
                                            <template x-if="hasSavedTtd">
                                                <button type="button" @click="cancelEdit()" class="text-xs text-slate-600 hover:text-slate-700 font-semibold px-2.5 py-1 bg-slate-100 hover:bg-slate-200 rounded-lg transition-colors cursor-pointer">
                                                    Batal Ubah
                                                </button>
                                            </template>
                                        </div>
                                    </template>
                                </div>
                            </div>

                            <!-- Hidden input tanda tangan -->
                            <input type="hidden" name="tanda_tangan_asesi_ak01" id="inputSignatureAk01" value="{{ $existingTtdTahapan }}">

                            <!-- Preview mode (Auto-filled) -->
                            <div x-show="!isEditing" class="border border-slate-200 rounded-xl bg-white p-4 flex flex-col items-center justify-center space-y-2">
                                @if($hasExistingTtdTahapan)
                                    <img src="{{ $existingTtdTahapanUrl }}" alt="TTD Asesi" class="max-h-24 object-contain">
                                    <span class="text-[11px] text-emerald-600 font-medium flex items-center gap-1">
                                        Tanda tangan otomatis terisi dari profil/pendaftaran Anda.
                                    </span>
                                @endif
                            </div>

                            <!-- Canvas mode (Edit / Baru) -->
                            <div x-show="isEditing" class="space-y-1.5" style="display: none;">
                                <div class="border border-blue-300 ring-2 ring-blue-100 rounded-xl bg-white p-2 relative overflow-hidden">
                                    <canvas id="canvasSignatureAk01" width="800" height="240" class="w-full h-36 bg-slate-50/50 rounded-lg cursor-crosshair block touch-none" style="touch-action: none; -ms-touch-action: none;"></canvas>
                                </div>
                                <span class="text-[11px] text-slate-400 block">Gunakan mouse atau layar sentuh untuk menggambar tanda tangan baru.</span>
                            </div>

                            @if(!empty($pendaftaran->tanda_tangan_asesi_ak01))
                                <div class="mt-2 text-xs text-emerald-600 font-medium flex items-center gap-2">
                                    <span class="w-2 h-2 rounded-full bg-emerald-500"></span>
                                    <span>Formulir FR.AK.01 telah ditandatangani pada {{ $pendaftaran->tanggal_ttd_asesi_ak01 ? date('d F Y H:i', strtotime($pendaftaran->tanggal_ttd_asesi_ak01)) : 'saat ini' }}.</span>
                                </div>
                            @endif
                        </div>
                    </div>

                    <!-- Bottom Action Final Button -->
                    <div class="flex flex-col sm:flex-row items-center justify-between gap-3 pt-2">
                        <button type="button" @click="setStep(2)" 
                                class="w-full sm:w-auto px-6 py-2.5 rounded-xl border border-slate-300 text-slate-700 bg-white hover:bg-slate-50 font-semibold text-sm transition-all duration-150 flex items-center justify-center">
                            &larr; Kembali ke APL.02
                        </button>
                        
                        <div class="flex flex-col sm:flex-row items-center gap-3 w-full sm:w-auto">
                            @if(!empty($pendaftaran->tanda_tangan_asesi_ak01) || $isAk01Selesai)
                                <button type="button" @click="setStep(4)"
                                   class="w-full sm:w-auto px-8 py-3 rounded-xl bg-blue-600 hover:bg-blue-700 text-white font-bold text-sm shadow-md transition-all duration-150 flex items-center justify-center gap-2 cursor-pointer">
                                    <span>Lanjut ke Formulir FR.AK.07 &rarr;</span>
                                </button>
                                <button type="submit" id="btn-submit-ak01" x-show="isEditing" style="display: none;"
                                        class="w-full sm:w-auto px-6 py-3 rounded-xl bg-emerald-600 hover:bg-emerald-700 text-white font-bold text-sm shadow-md transition-all duration-150 flex items-center justify-center gap-2 disabled:opacity-50 disabled:cursor-not-allowed cursor-pointer">
                                    Simpan Perubahan Tanda Tangan
                                </button>
                            @else
                                <button type="submit" id="btn-submit-ak01"
                                        class="w-full sm:w-auto px-8 py-3 rounded-xl bg-emerald-600 hover:bg-emerald-700 text-white font-bold text-sm shadow-md transition-all duration-150 flex items-center justify-center gap-2 disabled:opacity-50 disabled:cursor-not-allowed cursor-pointer">
                                    Kirim & Setujui FR.AK.01
                                </button>
                                <p id="peringatan-ttd-ak01" class="text-xs text-amber-600 font-semibold flex items-center gap-1.5 mt-1">
                                    <span>Anda harus membubuhkan tanda tangan digital terlebih dahulu sebelum dapat mengirim formulir.</span>
                                </p>
                            @endif
                        </div>
                    </div>
                </form>
            @endif
        </div>

        <!-- =========================================================================
             STEP 4: FORMULIR FR.AK.07 (PENYESUAIAN YANG WAJAR DAN BERALASAN)
             ========================================================================= -->
        <div x-show="currentStep === 4" x-transition.opacity.duration.300ms class="mt-6 space-y-6">
            @if(!$pendaftaran || !$isAk01Selesai)
                <div class="bg-white rounded-2xl border border-slate-200/80 shadow-xs p-8 text-center space-y-4">
                    <div class="w-14 h-14 rounded-2xl bg-amber-50 text-amber-600 border border-amber-200/70 flex items-center justify-center mx-auto text-xs font-bold uppercase tracking-wider">
                        Kunci
                    </div>
                    <div class="max-w-md mx-auto space-y-1.5">
                        <h3 class="text-base font-bold text-slate-800">Formulir FR.AK.07 Masih Terkunci</h3>
                        <p class="text-xs text-slate-500 leading-relaxed">
                            Formulir FR.AK.07 (Penyesuaian Asesmen yang Wajar) hanya dapat diakses setelah Formulir FR.AK.01 ditandatangani dan disetujui.
                        </p>
                    </div>
                    <div class="pt-2">
                        <button type="button" @click="setStep(3)" class="px-5 py-2.5 rounded-xl bg-blue-600 hover:bg-blue-700 text-white font-bold text-xs shadow-xs inline-flex items-center gap-2 cursor-pointer">
                            <span>Kembali ke FR.AK.01</span>
                            <span>&rarr;</span>
                        </button>
                    </div>
                </div>
            @else
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
                    $profileTtdAk07 = $profileTtd;
                    $isSignedByAsesiAk07 = $isSignedByAsesi;
                    $asesorSigToShow = ($ak07 && $ak07->asesor_signature) ? $ak07->asesor_signature : ($pendaftaran->tanda_tangan_asesor_ak01 ?? $pendaftaran->asesor?->tanda_tangan);

                    $faseList = [
                        'pra_asesmen' => ['title' => 'Pra Asesmen', 'desc' => 'Diterapkan sebelum tahapan asesmen dimulai (misal: verifikasi berkas & konsultasi pra-uji)'],
                        'saat_pra_asesmen' => ['title' => 'Pada Saat Asesmen', 'desc' => 'Diterapkan secara langsung selama sesi demonstrasi praktik, ujian tertulis, atau wawancara'],
                        'setelah_pra_asesmen' => ['title' => 'Setelah Asesmen', 'desc' => 'Diterapkan pada tahap penyusunan rekomendasi, umpan balik, dan pengumpulan bukti tambahan'],
                    ];
                @endphp

                <!-- Top navigation buttons -->
                <div class="flex items-center justify-end pb-3 pt-1 text-xs">
                    <div class="flex items-center gap-3">
                        <button type="button" @click="setStep(1)" class="text-slate-500 hover:text-blue-600 font-medium inline-flex items-center gap-1 transition-colors cursor-pointer">
                            Lihat FR.APL.01
                        </button>
                        <span class="text-slate-300">&bull;</span>
                        <button type="button" @click="setStep(2)" class="text-slate-500 hover:text-blue-600 font-medium inline-flex items-center gap-1 transition-colors cursor-pointer">
                            Lihat FR.APL.02
                        </button>
                        <span class="text-slate-300">&bull;</span>
                        <button type="button" @click="setStep(3)" class="text-slate-500 hover:text-blue-600 font-medium inline-flex items-center gap-1.5 transition-colors cursor-pointer">
                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/></svg>
                            <span>Lihat FR.AK.01</span>
                        </button>
                        @if($isSignedByAsesiAk07)
                            <span class="text-slate-300">&bull;</span>
                            <button type="button" @click="setStep(5)" class="text-blue-600 hover:text-blue-800 font-bold inline-flex items-center gap-1 transition-colors cursor-pointer">
                                <span>Buka Ujian / Tes &rarr;</span>
                            </button>
                        @endif
                    </div>
                </div>

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
                <div class="bg-white rounded-2xl border border-slate-200/90 shadow-2xs p-4 sm:p-5 space-y-4" x-data="tahapanAk07App()">
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
                                <form id="formSignAsesiAk07Tahapan" action="{{ route('asesi.ak07.sign-asesi', $pendaftaran->id) }}" method="POST" class="space-y-2.5">
                                    @csrf
                                    <input type="hidden" name="tanda_tangan_asesi" id="inputTtdAsesiTahapan" value="{{ $profileTtd }}">

                                    <div class="flex items-center gap-3">
                                        @if(!empty($profileTtd))
                                            <label class="inline-flex items-center gap-1.5 cursor-pointer text-[11px] font-medium text-slate-700">
                                                <input type="radio" name="sigModeAk07" value="profile" x-model="signatureMode" class="accent-blue-600">
                                                <span>Gunakan TTD Akun Profil</span>
                                            </label>
                                        @endif
                                        <label class="inline-flex items-center gap-1.5 cursor-pointer text-[11px] font-medium text-slate-700">
                                            <input type="radio" name="sigModeAk07" value="canvas" x-model="signatureMode" class="accent-blue-600">
                                            <span>Gores TTD Baru</span>
                                        </label>
                                    </div>

                                    <!-- Canvas Box -->
                                    <div x-show="signatureMode === 'canvas'" class="space-y-1">
                                        <div class="border-2 border-dashed border-slate-300 rounded-xl bg-white relative p-1">
                                            <canvas id="canvasAk07AsesiTahapan" class="w-full h-24 rounded-lg cursor-crosshair touch-none"></canvas>
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

                <!-- Bottom Action Step 4 -->
                <div class="flex flex-col sm:flex-row items-center justify-between gap-3 pt-2">
                    <button type="button" @click="setStep(3)" 
                            class="w-full sm:w-auto px-6 py-2.5 rounded-xl border border-slate-300 text-slate-700 bg-white hover:bg-slate-50 font-semibold text-sm transition-all duration-150 flex items-center justify-center cursor-pointer">
                        &larr; Kembali ke FR.AK.01
                    </button>
                    
                    @if($isSignedByAsesiAk07)
                        <div class="flex flex-col sm:flex-row items-center gap-3 w-full sm:w-auto">
                            <a href="{{ route('asesi.dashboard') }}" 
                               class="w-full sm:w-auto px-5 py-2.5 rounded-xl border border-slate-200 text-slate-600 hover:bg-slate-50 font-semibold text-xs transition-all duration-150 flex items-center justify-center">
                                Ke Dasbor
                            </a>
                            <button type="button" @click="setStep(5)" 
                               class="w-full sm:w-auto px-7 py-2.5 rounded-xl bg-blue-600 hover:bg-blue-700 text-white font-bold text-xs shadow-md transition-all duration-150 flex items-center justify-center gap-2 cursor-pointer">
                                <span>Lanjut ke Pelaksanaan Ujian / Tes (FR.IA) &rarr;</span>
                            </button>
                        </div>
                    @endif
                </div>
            @endif
        </div>

        <!-- =========================================================================
             STEP 5: PELAKSANAAN UJIAN & ASESMEN (FR.IA)
             ========================================================================= -->
        <div x-show="currentStep === 5" x-transition.opacity.duration.300ms class="mt-6 space-y-6">
            @if(!$pendaftaran || !$isAk07Selesai)
                <div class="bg-amber-50 border border-amber-200 rounded-2xl p-6 text-center space-y-2">
                    <h3 class="text-sm font-bold text-amber-900">Formulir FR.AK.07 Belum Selesai</h3>
                    <p class="text-xs text-amber-800">Silakan selesaikan dan tandatangani Formulir FR.AK.07 terlebih dahulu sebelum masuk ke tahap ujian.</p>
                    <button type="button" @click="setStep(4)" class="px-4 py-2 bg-amber-600 text-white font-bold text-xs rounded-xl shadow-xs cursor-pointer">
                        Buka Formulir FR.AK.07
                    </button>
                </div>
            @else
                <!-- Top navigation buttons -->
                <div class="flex items-center justify-between pb-3 text-xs">
                    <button type="button" @click="setStep(4)" class="inline-flex items-center gap-2 px-3.5 py-1.5 rounded-xl border border-slate-200 bg-white hover:bg-slate-50 text-slate-600 hover:text-blue-600 font-medium transition-all shadow-2xs cursor-pointer">
                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/></svg>
                        <span>Kembali ke Formulir FR.AK.07</span>
                    </button>
                    <span class="text-[11px] font-medium text-slate-400">Tahap 5 &bull; Pelaksanaan Ujian & Asesmen (FR.IA)</span>
                </div>

                @include('asesi.komponen.halaman-ujian-asesi')
            @endif
        </div>
    </div>

    <!-- =========================================================================
         MODAL PILIH BUKTI DARI APL.01
         ========================================================================= -->
    <div id="modal-pilih-apl01" class="fixed inset-0 items-center justify-center bg-slate-900/70 backdrop-blur-xs p-4 hidden" style="z-index: 99999; display: none;">
        <div class="bg-white rounded-2xl border border-slate-200 shadow-2xl max-w-md w-full p-5 space-y-4 animate-scale-up relative">
            <div class="flex items-center justify-between border-b border-slate-100 pb-3">
                <div class="flex items-center gap-2.5">
                    <span class="w-8 h-8 rounded-xl bg-blue-50 text-blue-600 flex items-center justify-center font-bold text-xs">
                        DOK
                    </span>
                    <div>
                        <h3 class="font-bold text-slate-800 text-sm">Pilih Bukti dari APL.01</h3>
                        <p class="text-[11px] text-slate-400">Pilih berkas APL.01 yang ingin ditautkan pada elemen ini</p>
                    </div>
                </div>
                <button type="button" onclick="tutupModalApl01()" class="text-slate-400 hover:text-slate-600 text-xl font-bold p-1 cursor-pointer">&times;</button>
            </div>

            <div class="space-y-2 max-h-64 overflow-y-auto pr-1">
                @forelse($dokumenTeknis as $dok)
                    <label class="flex items-center gap-3 p-3 rounded-xl border border-slate-200 hover:bg-slate-50 cursor-pointer transition-colors">
                        <input type="radio" name="pilih_dokumen_apl01" value="{{ $dok->id }}" class="text-blue-600 focus:ring-blue-500">
                        <div class="min-w-0 flex-1 text-xs">
                            <div class="font-bold text-slate-800">{{ $dok->jenis_dokumen }}</div>
                            <div class="text-slate-500 text-[11px] truncate">{{ $dok->nama_dokumen }}</div>
                        </div>
                    </label>
                @empty
                    <div class="text-center py-6 text-xs text-slate-400 italic">
                        Tidak ada berkas teknis APL.01 yang tersedia.
                    </div>
                @endforelse
            </div>

            <div class="flex items-center justify-end gap-2 pt-2 border-t border-slate-100">
                <button type="button" onclick="tutupModalApl01()" class="px-4 py-2 rounded-xl border border-slate-200 hover:bg-slate-50 text-slate-600 font-semibold text-xs cursor-pointer">
                    Batal
                </button>
                <button type="button" id="btn-submit-taut-apl01" onclick="submitTautkanApl01('{{ $pendaftaran ? $pendaftaran->id : '' }}')" class="px-4 py-2 rounded-xl bg-blue-600 hover:bg-blue-700 text-white font-semibold text-xs shadow-xs flex items-center gap-1.5 cursor-pointer">
                    Gunakan Bukti
                </button>
            </div>
        </div>
    </div>

    <!-- =========================================================================
         MODAL PRATINJAU PDF (UNIVERSAL VIEWER)
         ========================================================================= -->
    <div id="modal-pratinjau-pdf" class="fixed inset-0 items-center justify-center bg-slate-900/75 backdrop-blur-xs p-4 hidden" style="z-index: 99999; display: none;">
        <div class="bg-white rounded-2xl border border-slate-200 shadow-2xl max-w-4xl w-full h-[85vh] flex flex-col overflow-hidden animate-scale-up relative">
            <div class="flex items-center justify-between px-5 py-3.5 border-b border-slate-100 bg-slate-50">
                <div class="flex items-center gap-2 min-w-0">
                    <span class="px-1.5 py-0.5 rounded bg-rose-100 text-rose-700 font-bold text-xs shrink-0">PDF</span>
                    <h3 id="pdf-viewer-title" class="font-bold text-slate-800 text-xs sm:text-sm truncate">Dokumen PDF</h3>
                </div>
                <div class="flex items-center gap-2 shrink-0">
                    <a id="pdf-viewer-download" href="" target="_blank" class="px-3 py-1.5 bg-white border border-slate-200 rounded-lg text-xs font-semibold text-slate-700 hover:bg-slate-50 transition-colors flex items-center gap-1">
                        Buka Tab Baru
                    </a>
                    <button type="button" onclick="tutupModalPdf()" class="text-slate-400 hover:text-slate-600 text-xl font-bold px-2 cursor-pointer">&times;</button>
                </div>
            </div>
            <div class="flex-1 bg-slate-100 p-1">
                <iframe id="pdf-viewer-frame" src="" class="w-full h-full rounded-xl border-none"></iframe>
            </div>
        </div>
    </div>

    <!-- =========================================================================
         MODAL PRATINJAU GAMBAR (UNIVERSAL LIGHTBOX)
         ========================================================================= -->
    <div id="modal-pratinjau-gambar" class="fixed inset-0 items-center justify-center bg-slate-900/75 backdrop-blur-xs p-4 hidden" style="z-index: 99999; display: none;">
        <div class="bg-white rounded-2xl border border-slate-200 shadow-2xl max-w-3xl w-full max-h-[90vh] flex flex-col overflow-hidden animate-scale-up relative">
            <div class="flex items-center justify-between px-5 py-3.5 border-b border-slate-100 bg-slate-50">
                <div class="flex items-center gap-2 min-w-0">
                    <span class="px-1.5 py-0.5 rounded bg-blue-100 text-blue-700 font-bold text-xs shrink-0">IMG</span>
                    <h3 id="judul-pratinjau-gambar-universal" class="font-bold text-slate-800 text-xs sm:text-sm truncate">Pratinjau Gambar</h3>
                </div>
                <div class="flex items-center gap-2 shrink-0">
                    <a id="btn-download-gambar-universal" href="" target="_blank" download class="px-3 py-1.5 bg-white border border-slate-200 rounded-lg text-xs font-semibold text-slate-700 hover:bg-slate-50 transition-colors flex items-center gap-1">
                        Unduh File
                    </a>
                    <button type="button" onclick="tutupModalGambar()" class="text-slate-400 hover:text-slate-600 text-xl font-bold px-2 cursor-pointer">&times;</button>
                </div>
            </div>
            <div class="flex-1 bg-slate-950/90 p-4 flex items-center justify-center overflow-auto min-h-[300px]">
                <img id="img-pratinjau-gambar-universal" src="" alt="Pratinjau Gambar" class="max-w-full max-h-[70vh] object-contain rounded-lg shadow-lg">
            </div>
        </div>
    </div>

    <!-- =========================================================================
         MODAL KONFIRMASI SUBMIT APL.02
         ========================================================================= -->
    <div id="modal-konfirmasi-submit-apl02" class="fixed inset-0 items-center justify-center bg-slate-900/70 backdrop-blur-xs p-4 hidden" style="z-index: 99999; display: none;">
        <div class="bg-white rounded-2xl border border-slate-200 shadow-2xl max-w-md w-full p-6 space-y-4 animate-scale-up relative">
            <div class="flex items-center gap-3">
                <div class="w-11 h-11 rounded-2xl {{ $isApl02Revision ? 'bg-amber-100 text-amber-700' : 'bg-blue-100 text-blue-600' }} flex items-center justify-center font-bold text-xs shrink-0">
                    APL
                </div>
                <div>
                    <h3 class="font-bold text-slate-800 text-base">
                        {{ $isApl02Revision ? 'Ajukan Ulang FR.APL.02 ke Asesor?' : 'Kirim Formulir FR.APL.02?' }}
                    </h3>
                    <p class="text-xs text-slate-500">
                        {{ $isApl02Revision ? 'Konfirmasi pengajuan ulang hasil perbaikan' : 'Konfirmasi pengajuan asesmen mandiri' }}
                    </p>
                </div>
            </div>

            <p class="text-xs text-slate-600 leading-relaxed bg-slate-50 p-3.5 rounded-xl border border-slate-200/80">
                @if($isApl02Revision)
                    Perbaikan penilaian mandiri dan bukti pendukung Anda akan diajukan kembali ke Asesor Penguji untuk diverifikasi ulang.
                @else
                    Pastikan seluruh jawaban APL.02 sudah benar. Setelah dikirim, formulir akan menunggu pemeriksaan asesor dan dikunci dari perubahan.
                @endif
            </p>

            <div class="flex items-center justify-end gap-2.5 pt-2 border-t border-slate-100">
                <button type="button" onclick="tutupModalSubmitApl02()" class="px-4 py-2.5 rounded-xl border border-slate-200 hover:bg-slate-50 text-slate-600 font-semibold text-xs cursor-pointer">
                    Batal
                </button>
                <button type="button" onclick="eksekusiSubmitApl02()" class="px-5 py-2.5 rounded-xl bg-blue-600 hover:bg-blue-700 text-white font-bold text-xs shadow-xs flex items-center gap-2 cursor-pointer">
                    <span>{{ $isApl02Revision ? 'Ajukan Ulang Sekarang' : 'Kirim untuk Diperiksa' }}</span>
                </button>
            </div>
        </div>
    </div>
</div>
@endsection

@push('js')
<script>
    let targetElemenIdForApl01 = null;

    function bukaModalSubmitApl02() {
        const modal = document.getElementById('modal-konfirmasi-submit-apl02');
        if (modal) {
            modal.classList.remove('hidden');
            modal.style.display = 'flex';
        }
    }

    function tutupModalSubmitApl02() {
        const modal = document.getElementById('modal-konfirmasi-submit-apl02');
        if (modal) {
            modal.classList.add('hidden');
            modal.style.display = 'none';
        }
    }

    function eksekusiSubmitApl02() {
        const form = document.getElementById('form-apl02-asesmen');
        if (form) {
            tutupModalSubmitApl02();
            form.submit();
        }
    }

    function triggerUploadBukti(elemenId) {
        const input = document.getElementById(`file-input-elem-${elemenId}`);
        if (input) {
            input.click();
        }
    }

    function handleUploadBukti(input, elemenId, pendaftaranId) {
        if (!input.files || input.files.length === 0) return;
        const file = input.files[0];

        // Validasi client-side
        const allowedExts = ['pdf', 'jpg', 'jpeg', 'png'];
        const ext = file.name.split('.').pop().toLowerCase();
        if (!allowedExts.includes(ext)) {
            alert('Format file tidak didukung. Gunakan PDF, JPG, JPEG, atau PNG.');
            input.value = '';
            return;
        }

        if (file.size > 10 * 1024 * 1024) {
            alert('Ukuran file terlalu besar. Maksimal 10 MB.');
            input.value = '';
            return;
        }

        const formData = new FormData();
        formData.append('pendaftaran_id', pendaftaranId);
        formData.append('elemen_id', elemenId);
        formData.append('file', file);
        formData.append('_token', '{{ csrf_token() }}');

        // Loading state
        const loadingEl = document.getElementById(`upload-loading-elem-${elemenId}`);
        const btnUpload = document.getElementById(`btn-upload-elem-${elemenId}`);
        const btnApl01 = document.getElementById(`btn-apl01-elem-${elemenId}`);
        if (loadingEl) loadingEl.classList.remove('hidden');
        if (btnUpload) btnUpload.disabled = true;
        if (btnApl01) btnApl01.disabled = true;

        fetch('{{ route("asesi.apl02.upload_bukti") }}', {
            method: 'POST',
            body: formData,
            headers: {
                'X-Requested-With': 'XMLHttpRequest'
            }
        })
        .then(res => res.json())
        .then(data => {
            if (loadingEl) loadingEl.classList.add('hidden');
            if (btnUpload) btnUpload.disabled = false;
            if (btnApl01) btnApl01.disabled = false;
            input.value = '';

            if (data.success) {
                tambahKartuBuktiKeDom(elemenId, data.data);
                tampilkanToast(data.message || 'Bukti berhasil diupload.');
            } else {
                alert(data.message || 'Gagal mengupload file.');
            }
        })
        .catch(err => {
            if (loadingEl) loadingEl.classList.add('hidden');
            if (btnUpload) btnUpload.disabled = false;
            if (btnApl01) btnApl01.disabled = false;
            input.value = '';
            alert('Terjadi kesalahan jaringan atau server saat mengupload file.');
        });
    }

    function bukaModalPilihApl01(elemenId) {
        targetElemenIdForApl01 = elemenId;
        const modal = document.getElementById('modal-pilih-apl01');
        if (modal) {
            modal.classList.remove('hidden');
            modal.style.display = 'flex';
        }
    }

    function tutupModalApl01() {
        targetElemenIdForApl01 = null;
        const modal = document.getElementById('modal-pilih-apl01');
        if (modal) {
            modal.classList.add('hidden');
            modal.style.display = 'none';
        }
    }

    function submitTautkanApl01(pendaftaranId) {
        if (!targetElemenIdForApl01) return;
        const selected = document.querySelector('input[name="pilih_dokumen_apl01"]:checked');
        if (!selected) {
            alert('Silakan pilih salah satu dokumen APL.01.');
            return;
        }

        const elemenId = targetElemenIdForApl01;
        const dokumenId = selected.value;

        const btnSubmit = document.getElementById('btn-submit-taut-apl01');
        if (btnSubmit) {
            btnSubmit.disabled = true;
            btnSubmit.innerHTML = 'Menautkan...';
        }

        fetch('{{ route("asesi.apl02.pilih_bukti_apl01") }}', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': '{{ csrf_token() }}',
                'X-Requested-With': 'XMLHttpRequest'
            },
            body: JSON.stringify({
                pendaftaran_id: pendaftaranId,
                elemen_id: elemenId,
                dokumen_id: dokumenId
            })
        })
        .then(res => res.json())
        .then(data => {
            if (btnSubmit) {
                btnSubmit.disabled = false;
                btnSubmit.innerHTML = 'Gunakan Bukti';
            }
            tutupModalApl01();

            if (data.success) {
                tambahKartuBuktiKeDom(elemenId, data.data);
                tampilkanToast(data.message || 'Bukti dari APL.01 berhasil ditautkan.');
            } else {
                alert(data.message || 'Gagal menautkan dokumen APL.01.');
            }
        })
        .catch(err => {
            if (btnSubmit) {
                btnSubmit.disabled = false;
                btnSubmit.innerHTML = 'Gunakan Bukti';
            }
            alert('Terjadi kesalahan saat memproses permintaan.');
        });
    }

    function hapusBukti(buktiId, elemenId) {
        if (!confirm('Apakah Anda yakin ingin menghapus bukti pendukung ini?')) {
            return;
        }

        const itemEl = document.getElementById(`bukti-item-${buktiId}`);
        if (itemEl) itemEl.style.opacity = '0.4';

        fetch('{{ url("/asesi/tahapan/apl02/hapus-bukti") }}/' + buktiId, {
            method: 'DELETE',
            headers: {
                'X-CSRF-TOKEN': '{{ csrf_token() }}',
                'X-Requested-With': 'XMLHttpRequest'
            }
        })
        .then(res => res.json())
        .then(data => {
            if (data.success) {
                if (itemEl) itemEl.remove();
                const listEl = document.getElementById(`bukti-list-elem-${elemenId}`);
                const emptyEl = document.getElementById(`bukti-empty-elem-${elemenId}`);
                if (listEl && listEl.children.length === 0 && emptyEl) {
                    emptyEl.classList.remove('hidden');
                }
                tampilkanToast('Bukti berhasil dihapus.');
            } else {
                if (itemEl) itemEl.style.opacity = '1';
                alert(data.message || 'Gagal menghapus bukti.');
            }
        })
        .catch(err => {
            if (itemEl) itemEl.style.opacity = '1';
            alert('Terjadi kesalahan saat menghapus bukti.');
        });
    }

    function tambahKartuBuktiKeDom(elemenId, item) {
        const listEl = document.getElementById(`bukti-list-elem-${elemenId}`);
        const emptyEl = document.getElementById(`bukti-empty-elem-${elemenId}`);
        if (emptyEl) emptyEl.classList.add('hidden');

        const iconHtml = item.is_pdf
            ? '<div class="w-7 h-7 rounded-md bg-rose-50 text-rose-600 border border-rose-100 flex items-center justify-center font-bold text-xs shrink-0 mt-0.5">PDF</div>'
            : '<div class="w-7 h-7 rounded-md bg-blue-50 text-blue-600 border border-blue-100 flex items-center justify-center font-bold text-xs shrink-0 mt-0.5">IMG</div>';

        const aplBadge = item.sumber === 'apl01'
            ? '<span class="px-1.5 py-0.2 rounded bg-blue-50 text-blue-700 text-[10px] font-medium border border-blue-200/60">APL.01</span>'
            : '';

        const div = document.createElement('div');
        div.id = `bukti-item-${item.id}`;
        div.className = 'flex items-center justify-between p-2.5 rounded-lg border border-slate-200 bg-white hover:bg-slate-50/80 transition-colors text-xs gap-3';
        div.innerHTML = `
            <div class="flex items-start gap-2.5 min-w-0 flex-1">
                ${iconHtml}
                <div class="min-w-0 flex-1">
                    <div class="font-semibold text-slate-800 truncate" title="${item.nama}">${item.nama}</div>
                    <div class="text-[11px] text-slate-400 flex items-center gap-2 mt-0.5">
                        <span>${item.ukuran}</span>
                        ${aplBadge}
                    </div>
                </div>
            </div>
            <div class="flex items-center gap-1.5 shrink-0">
                <button type="button" 
                        onclick="bukaPratinjauBukti('${item.url}', '${item.nama.replace(/'/g, "\\'")}', ${item.is_pdf ? 'true' : 'false'})"
                        class="px-2.5 py-1 rounded-md bg-white border border-slate-200 hover:bg-slate-100 text-slate-700 font-semibold text-xs shadow-2xs transition-colors flex items-center gap-1 cursor-pointer">
                    Lihat
                </button>
                <button type="button" 
                        onclick="hapusBukti('${item.id}', '${elemenId}')"
                        class="px-2 py-1 rounded-md bg-white border border-rose-200 hover:bg-rose-50 text-rose-600 font-semibold text-xs transition-colors cursor-pointer"
                        title="Hapus Bukti">
                    Hapus
                </button>
            </div>
        `;

        if (listEl) {
            listEl.appendChild(div);
        }
    }

    function bukaPratinjauBukti(url, title, isPdf) {
        if (!url) return;
        if (isPdf || url.toLowerCase().includes('.pdf')) {
            const modal = document.getElementById('modal-pratinjau-pdf');
            const frame = document.getElementById('pdf-viewer-frame');
            const titleEl = document.getElementById('pdf-viewer-title');
            const dwnEl = document.getElementById('pdf-viewer-download');
            if (frame) frame.src = url;
            if (titleEl) titleEl.innerText = title || 'Dokumen PDF';
            if (dwnEl) dwnEl.href = url;
            if (modal) {
                modal.classList.remove('hidden');
                modal.style.display = 'flex';
            }
        } else {
            const modal = document.getElementById('modal-pratinjau-gambar');
            const img = document.getElementById('img-pratinjau-gambar-universal');
            const titleEl = document.getElementById('judul-pratinjau-gambar-universal');
            const dwnEl = document.getElementById('btn-download-gambar-universal');
            if (img) img.src = url;
            if (titleEl) titleEl.innerText = title || 'Pratinjau Gambar';
            if (dwnEl) dwnEl.href = url;
            if (modal) {
                modal.classList.remove('hidden');
                modal.style.display = 'flex';
            }
        }
    }

    function tutupModalPdf() {
        const modal = document.getElementById('modal-pratinjau-pdf');
        const frame = document.getElementById('pdf-viewer-frame');
        if (frame) frame.src = '';
        if (modal) {
            modal.classList.add('hidden');
            modal.style.display = 'none';
        }
    }

    function tutupModalGambar() {
        const modal = document.getElementById('modal-pratinjau-gambar');
        const img = document.getElementById('img-pratinjau-gambar-universal');
        if (img) img.src = '';
        if (modal) {
            modal.classList.add('hidden');
            modal.style.display = 'none';
        }
    }

    function tampilkanToast(pesan) {
        const toast = document.createElement('div');
        toast.className = 'fixed bottom-6 right-6 z-99999 bg-slate-900 text-white text-xs font-semibold px-4 py-3 rounded-xl shadow-lg flex items-center gap-2 transition-all duration-300 transform translate-y-4 opacity-0';
        toast.innerHTML = '<span class="text-emerald-400">✓</span> ' + pesan;
        document.body.appendChild(toast);
        setTimeout(() => {
            toast.classList.remove('translate-y-4', 'opacity-0');
        }, 10);
        setTimeout(() => {
            toast.classList.add('translate-y-4', 'opacity-0');
            setTimeout(() => toast.remove(), 300);
        }, 3000);
    }

    function signaturePadComponent(canvasId, inputId) {
        return {
            engine: null,
            init() {
                this.$nextTick(() => {
                    const canvas = document.getElementById(canvasId);
                    const input = document.getElementById(inputId);
                    if (!canvas) return;

                    this.engine = initUnifiedSignatureEngine(canvas, input, (data) => {
                        updateAk01SubmitButton();
                    });

                    // Pastikan resize canvas berjalan saat beralih ke Step 3
                    if (this.$watch) {
                        this.$watch('currentStep', (step) => {
                            if (step === 3 && this.engine) {
                                setTimeout(() => this.engine.resize(), 50);
                                setTimeout(() => this.engine.resize(), 200);
                            }
                        });
                    }

                    // Jika sudah ada TTD tersimpan, aktifkan tombol submit
                    if (input && input.value && (input.value.startsWith('data:image') || input.value.length > 5)) {
                        updateAk01SubmitButton();
                    }
                });
            },
            clearSignature() {
                if (this.engine) {
                    this.engine.clear();
                    updateAk01SubmitButton();
                }
            }
        };
    }

    // Unified Robust Signature Engine (Mendukung SignaturePad CDN & Fallback Native HTML5 Canvas)
    function initUnifiedSignatureEngine(canvas, input, onStrokeCallback) {
        if (!canvas) return null;

        canvas.style.touchAction = 'none';
        const ctx = canvas.getContext('2d');
        let pad = null;
        let isNativeDrawing = false;
        let strokes = [];
        let currentStroke = [];

        function setupNativeContext() {
            ctx.strokeStyle = '#0f172a';
            ctx.fillStyle = '#0f172a';
            ctx.lineWidth = 2.8;
            ctx.lineCap = 'round';
            ctx.lineJoin = 'round';
            ctx.imageSmoothingEnabled = true;
        }

        function getCanvasCoords(e) {
            const rect = canvas.getBoundingClientRect();
            const scaleX = canvas.width / (rect.width || 1);
            const scaleY = canvas.height / (rect.height || 1);

            let clientX = e.clientX;
            let clientY = e.clientY;
            if (e.touches && e.touches.length > 0) {
                clientX = e.touches[0].clientX;
                clientY = e.touches[0].clientY;
            }

            return {
                x: (clientX - rect.left) * scaleX,
                y: (clientY - rect.top) * scaleY
            };
        }

        function redrawNativeStrokes() {
            ctx.clearRect(0, 0, canvas.width, canvas.height);
            setupNativeContext();
            for (let i = 0; i < strokes.length; i++) {
                drawStroke(strokes[i]);
            }
            if (currentStroke.length > 0) {
                drawStroke(currentStroke);
            }
        }

        function drawStroke(pts) {
            if (!pts || pts.length === 0) return;
            if (pts.length < 3) {
                ctx.beginPath();
                ctx.arc(pts[0].x, pts[0].y, ctx.lineWidth / 2, 0, Math.PI * 2);
                ctx.fill();
                return;
            }
            ctx.beginPath();
            ctx.moveTo(pts[0].x, pts[0].y);
            for (let i = 1; i < pts.length - 2; i++) {
                const xc = (pts[i].x + pts[i + 1].x) / 2;
                const yc = (pts[i].y + pts[i + 1].y) / 2;
                ctx.quadraticCurveTo(pts[i].x, pts[i].y, xc, yc);
            }
            ctx.quadraticCurveTo(
                pts[pts.length - 2].x,
                pts[pts.length - 2].y,
                pts[pts.length - 1].x,
                pts[pts.length - 1].y
            );
            ctx.stroke();
        }

        function resizeCanvas() {
            const rect = canvas.getBoundingClientRect();
            const displayWidth = rect.width || canvas.offsetWidth || canvas.clientWidth || 600;
            const displayHeight = rect.height || canvas.offsetHeight || canvas.clientHeight || 150;

            if (displayWidth > 0 && displayHeight > 0) {
                const ratio = Math.max(window.devicePixelRatio || 1, 1);
                const targetW = Math.round(displayWidth * ratio);
                const targetH = Math.round(displayHeight * ratio);

                if (canvas.width !== targetW || canvas.height !== targetH) {
                    let savedData = null;
                    if (pad && !pad.isEmpty()) {
                        savedData = pad.toDataURL();
                    } else if (input && input.value && input.value.startsWith('data:image')) {
                        savedData = input.value;
                    }

                    canvas.width = targetW;
                    canvas.height = targetH;
                    ctx.scale(ratio, ratio);

                    if (pad) {
                        pad.clear();
                        if (savedData) {
                            pad.fromDataURL(savedData);
                        }
                    } else if (strokes.length > 0) {
                        redrawNativeStrokes();
                    } else if (savedData) {
                        const img = new Image();
                        img.onload = () => ctx.drawImage(img, 0, 0, displayWidth, displayHeight);
                        img.src = savedData;
                    }
                }
            }
        }

        // Gunakan SignaturePad jika tersedia, atau Native Canvas Engine sebagai fallback
        if (typeof SignaturePad !== 'undefined') {
            try {
                pad = new SignaturePad(canvas, {
                    backgroundColor: 'rgba(255, 255, 255, 0)',
                    penColor: '#0f172a',
                    minWidth: 1.2,
                    maxWidth: 2.8
                });

                if (input && input.value && input.value.startsWith('data:image')) {
                    pad.fromDataURL(input.value);
                }

                pad.addEventListener('endStroke', () => {
                    const data = pad.toDataURL('image/png');
                    if (input) input.value = data;
                    if (typeof onStrokeCallback === 'function') onStrokeCallback(data);
                });
            } catch (err) {
                console.warn('SignaturePad initialization failed, falling back to native engine:', err);
                pad = null;
            }
        }

        if (!pad) {
            setupNativeContext();

            const startNative = (e) => {
                e.preventDefault();
                isNativeDrawing = true;
                currentStroke = [getCanvasCoords(e)];
                redrawNativeStrokes();
            };

            const moveNative = (e) => {
                if (!isNativeDrawing) return;
                e.preventDefault();
                currentStroke.push(getCanvasCoords(e));
                redrawNativeStrokes();
            };

            const stopNative = (e) => {
                if (!isNativeDrawing) return;
                isNativeDrawing = false;
                if (currentStroke.length > 0) {
                    strokes.push(currentStroke);
                    currentStroke = [];
                    const data = canvas.toDataURL('image/png');
                    if (input) input.value = data;
                    if (typeof onStrokeCallback === 'function') onStrokeCallback(data);
                }
            };

            canvas.addEventListener('mousedown', startNative);
            canvas.addEventListener('mousemove', moveNative);
            canvas.addEventListener('mouseup', stopNative);
            canvas.addEventListener('mouseleave', stopNative);

            canvas.addEventListener('touchstart', startNative, { passive: false });
            canvas.addEventListener('touchmove', moveNative, { passive: false });
            canvas.addEventListener('touchend', stopNative);
        }

        if (window.ResizeObserver) {
            const ro = new ResizeObserver((entries) => {
                for (const entry of entries) {
                    if (entry.contentRect.width > 0) {
                        resizeCanvas();
                    }
                }
            });
            ro.observe(canvas);
        }

        window.addEventListener('resize', resizeCanvas);
        setTimeout(resizeCanvas, 50);
        setTimeout(resizeCanvas, 200);

        return {
            pad,
            clear() {
                if (pad) {
                    pad.clear();
                } else {
                    strokes = [];
                    currentStroke = [];
                    ctx.clearRect(0, 0, canvas.width, canvas.height);
                }
                if (input) input.value = '';
                if (typeof onStrokeCallback === 'function') onStrokeCallback('');
            },
            isEmpty() {
                if (pad) return pad.isEmpty();
                return strokes.length === 0;
            },
            toDataURL() {
                if (pad) return pad.toDataURL('image/png');
                return strokes.length > 0 ? canvas.toDataURL('image/png') : '';
            },
            resize() {
                resizeCanvas();
            }
        };
    }

    // =========================================================================
    // FR.AK.07 SIGNATURE HANDLER (STEP 4)
    // =========================================================================
    function tahapanAk07App() {
        return {
            signatureMode: '{{ !empty($profileTtdAk07) ? "profile" : "canvas" }}',
            canvasSignatureData: '',
            signaturePad: null,

            init() {
                this.$nextTick(() => {
                    this.initSignaturePad();
                });
                if (this.$watch) {
                    this.$watch('currentStep', (step) => {
                        if (step === 4) {
                            setTimeout(() => this.resizeCanvas(), 50);
                            setTimeout(() => this.resizeCanvas(), 200);
                        }
                    });
                }
            },

            initSignaturePad() {
                const canvas = document.getElementById('canvasAk07AsesiTahapan');
                if (!canvas) return;

                const self = this;
                this.resizeCanvas();
                window.addEventListener('resize', () => self.resizeCanvas());

                if (typeof SignaturePad !== 'undefined') {
                    this.signaturePad = new SignaturePad(canvas, {
                        backgroundColor: 'rgba(255, 255, 255, 0)',
                        penColor: '#0f172a',
                        minWidth: 1.2,
                        maxWidth: 2.8
                    });
                }
            },

            resizeCanvas() {
                const canvas = document.getElementById('canvasAk07AsesiTahapan');
                if (!canvas) return;
                const ratio = Math.max(window.devicePixelRatio || 1, 1);
                const rect = canvas.getBoundingClientRect();
                const displayWidth = rect.width || canvas.offsetWidth || 400;
                const displayHeight = rect.height || canvas.offsetHeight || 96;
                if (displayWidth > 0 && displayHeight > 0) {
                    canvas.width = Math.round(displayWidth * ratio);
                    canvas.height = Math.round(displayHeight * ratio);
                    const ctx = canvas.getContext('2d');
                    ctx.scale(ratio, ratio);
                }
            },

            clearCanvas() {
                if (this.signaturePad) {
                    this.signaturePad.clear();
                    this.canvasSignatureData = '';
                }
            },

            submitSignature() {
                const form = document.getElementById('formSignAsesiAk07Tahapan');
                const input = document.getElementById('inputTtdAsesiTahapan');
                if (!form || !input) return;

                if (this.signatureMode === 'canvas') {
                    if (!this.signaturePad || this.signaturePad.isEmpty()) {
                        if (typeof Swal !== 'undefined') {
                            Swal.fire({
                                icon: 'warning',
                                title: 'Tanda Tangan Belum Digores',
                                text: 'Silakan bubuhkan tanda tangan Anda pada canvas terlebih dahulu.',
                                confirmButtonColor: '#2563eb'
                            });
                        } else {
                            alert('Silakan bubuhkan tanda tangan Anda pada canvas terlebih dahulu.');
                        }
                        return;
                    }
                    this.canvasSignatureData = this.signaturePad.toDataURL('image/png');
                    input.value = this.canvasSignatureData;
                } else if (this.signatureMode === 'profile') {
                    input.value = '{{ $profileTtdAk07 ?? "" }}';
                    if (!input.value) {
                        if (typeof Swal !== 'undefined') {
                            Swal.fire({
                                icon: 'warning',
                                title: 'Tanda Tangan Profil Kosong',
                                text: 'Profil Anda belum memiliki tanda tangan. Silakan pilih opsi Gores TTD Baru.',
                                confirmButtonColor: '#2563eb'
                            });
                        } else {
                            alert('Profil Anda belum memiliki tanda tangan. Silakan pilih opsi Gores TTD Baru.');
                        }
                        return;
                    }
                }

                form.submit();
            }
        };
    }

    // =========================================================================
    // VALIDASI TTD AK-01 SEBELUM SUBMIT
    // =========================================================================
    function updateAk01SubmitButton() {
        const input = document.getElementById('inputSignatureAk01');
        const btn = document.getElementById('btn-submit-ak01');
        const warning = document.getElementById('peringatan-ttd-ak01');
        if (!btn) return;

        const hasTtd = input && input.value && (input.value.startsWith('data:image') || input.value.length > 5);
        btn.disabled = !hasTtd;
        if (warning) {
            warning.style.display = hasTtd ? 'none' : 'flex';
        }
    }

    function validasiAk01Sebelumkirim(event) {
        event.preventDefault();

        const input = document.getElementById('inputSignatureAk01');
        const hasTtd = input && input.value && (input.value.startsWith('data:image') || input.value.length > 5);

        if (!hasTtd) {
            if (typeof Swal !== 'undefined') {
                Swal.fire({
                    icon: 'warning',
                    title: 'Tanda Tangan Belum Diisi',
                    text: 'Silakan bubuhkan tanda tangan digital Anda pada canvas terlebih dahulu sebelum mengirim formulir FR.AK.01.',
                    confirmButtonColor: '#059669',
                    confirmButtonText: 'Mengerti'
                });
            } else {
                alert('Silakan bubuhkan tanda tangan digital Anda terlebih dahulu.');
            }
            return false;
        }

        const form = document.getElementById('form-ak01-asesi');
        const btn = document.getElementById('btn-submit-ak01');
        if (btn) {
            btn.disabled = true;
            btn.innerHTML = 'Menyimpan Persetujuan...';
        }

        const formData = new FormData(form);

        fetch(form.action, {
            method: 'POST',
            body: formData,
            headers: {
                'X-Requested-With': 'XMLHttpRequest',
                'Accept': 'application/json'
            }
        })
        .then(res => res.json())
        .then(data => {
            if (data.success) {
                const redirectUrl = data.redirect_url || '{{ route("asesi.dashboard") }}';
                if (typeof Swal !== 'undefined') {
                    Swal.fire({
                        icon: 'success',
                        title: 'Persetujuan FR.AK.01 Selesai!',
                        text: data.message || 'Persetujuan Asesmen (FR.AK.01) telah berhasil ditandatangani. Silakan lanjutkan ke pengisian Formulir FR.AK.07 (Penyesuaian Asesmen).',
                        confirmButtonColor: '#2563eb',
                        confirmButtonText: 'Lanjut ke Formulir FR.AK.07 &rarr;'
                    }).then(() => {
                        window.location.href = redirectUrl;
                    });
                } else {
                    alert(data.message || 'Persetujuan Asesmen (FR.AK.01) telah berhasil ditandatangani. Silakan lanjutkan ke pengisian Formulir FR.AK.07 (Penyesuaian Asesmen).');
                    window.location.href = redirectUrl;
                }
            } else {
                if (btn) {
                    btn.disabled = false;
                    btn.innerHTML = 'Kirim & Setujui FR.AK.01';
                }
                alert(data.message || 'Terjadi kesalahan saat menyimpan FR.AK.01.');
            }
        })
        .catch(err => {
            // Fallback submit biasa jika AJAX gagal
            form.submit();
        });

        return false;
    }

    // Auto-scroll ke dokumen yang perlu direvisi jika ada anchor hash di URL
    document.addEventListener('DOMContentLoaded', function() {
        const hash = window.location.hash;
        if (hash) {
            setTimeout(() => {
                const targetEl = document.querySelector(hash);
                if (targetEl) {
                    targetEl.scrollIntoView({ behavior: 'smooth', block: 'center' });
                    const fileInput = targetEl.querySelector('input[type="file"]');
                    if (fileInput) {
                        fileInput.focus();
                    }
                }
            }, 300);
        }

        // Ketika asesi memilih berkas baru, hilangkan peringatan revisi secara instant di layar
        const fileInputs = document.querySelectorAll('#dokumen-ktp input[type="file"], #dokumen-rapor input[type="file"], #dokumen-pkl input[type="file"], #dokumen-foto input[type="file"]');
        fileInputs.forEach(function(input) {
            input.addEventListener('change', function() {
                if (this.files && this.files.length > 0) {
                    const card = this.closest('[id^="dokumen-"]');
                    if (card) {
                        const alertBox = card.querySelector('.bg-rose-50');
                        if (alertBox) {
                            alertBox.style.transition = 'all 0.4s ease';
                            alertBox.style.opacity = '0';
                            alertBox.style.maxHeight = '0';
                            alertBox.style.padding = '0';
                            alertBox.style.margin = '0';
                            alertBox.style.overflow = 'hidden';
                            setTimeout(() => { alertBox.style.display = 'none'; }, 400);
                        }
                        const badge = card.querySelector('.bg-rose-600');
                        if (badge) {
                            badge.className = 'text-[10px] px-2.5 py-0.5 rounded-full bg-emerald-600 text-white font-bold tracking-wide uppercase shadow-2xs';
                            badge.textContent = 'Berkas Baru Dipilih';
                        }
                    }
                }
            });
        });

        // Dynamic Unit Kompetensi toggle saat skema dipilih
        window.updateTabelUnitTahapan = function() {
            const selectSkemaTahapan = document.querySelector('select[name="skema_id"]') || document.querySelector('input[name="skema_id"]');
            if (!selectSkemaTahapan) return;
            const selectedSkemaId = String(selectSkemaTahapan.value || '');
            document.querySelectorAll('.tabel-unit-tahapan').forEach(table => {
                const tableSkemaId = String(table.getAttribute('data-skema-id') || '');
                if (selectedSkemaId && tableSkemaId === selectedSkemaId) {
                    table.classList.remove('hidden');
                    table.style.display = 'block';
                } else {
                    table.classList.add('hidden');
                    table.style.display = 'none';
                }
            });
        };

        const selectSkemaTahapan = document.querySelector('select[name="skema_id"]');
        if (selectSkemaTahapan) {
            selectSkemaTahapan.addEventListener('change', window.updateTabelUnitTahapan);
        }
        window.updateTabelUnitTahapan();
        updateAk01SubmitButton();
    });
</script>
@endpush
