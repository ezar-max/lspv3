@extends('tata-letak.dasbor')

@section('judul', 'Pengesahan FR.AK.01 - ' . ($pendaftaran->asesi->nama_lengkap ?? 'Asesi'))

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
    $isSignedByAsesi = !empty($pendaftaran->tanda_tangan_asesi_ak01) || in_array($pendaftaran->status_ak01, ['disetujui_asesi', 'selesai']);
    $isSignedByAsesor = !empty($pendaftaran->tanda_tangan_asesor_ak01) || in_array($pendaftaran->status_ak01, ['disetujui_asesor', 'selesai']);
    $savedBukti = (array) ($pendaftaran->bukti_dikumpulkan ?? []);
@endphp

<div class="max-w-5xl mx-auto space-y-5" x-data="asesorAk01App()">

    <!-- =========================================================================
         1. BREADCRUMB & HEADER INFO
         ========================================================================= -->
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3 pb-1">
        <div class="space-y-0.5">
            <div class="flex items-center gap-2 text-xs text-slate-500">
                <a href="{{ route('asesor.dashboard') }}" class="hover:text-indigo-600 font-medium">Dashboard</a>
                <span>/</span>
                <a href="{{ route('asesor.penilaian') }}" class="hover:text-indigo-600 font-medium">Penilaian Peserta</a>
                <span>/</span>
                <span class="text-slate-800 font-bold">Pengesahan FR.AK.01</span>
            </div>
            <h1 class="text-lg sm:text-xl font-bold text-slate-900 leading-tight">
                Pengesahan Persetujuan Asesmen & Kerahasiaan (FR.AK.01)
            </h1>
            <p class="text-xs text-slate-500">
                Tinjau kesepakatan rencana asesmen dan sahkan komitmen kerahasiaan sebelum menyusun FR.MAPA.
            </p>
        </div>

        <div class="flex items-center gap-2">
            <a href="{{ (url()->previous() && url()->previous() !== url()->current()) ? url()->previous() : route('asesor.daftar-peserta') }}" 
               onclick="if (document.referrer && document.referrer !== window.location.href) { window.location.href = document.referrer; return false; } else if (window.history.length > 1) { window.history.back(); return false; }"
               class="px-3.5 py-2 rounded-xl border border-slate-200 hover:bg-slate-50 text-slate-700 font-semibold text-xs transition-colors flex items-center cursor-pointer shadow-2xs">
                <span>&larr; Kembali</span>
            </a>
        </div>
    </div>

    <!-- =========================================================================
         2. METADATA SUMMARY CARD
         ========================================================================= -->
    <div class="bg-white rounded-3xl border border-slate-200/90 shadow-2xs p-5 sm:p-6 space-y-4">
        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3 border-b border-slate-100 pb-3">
            <div class="flex items-center gap-3">
                <div class="w-10 h-10 rounded-xl bg-indigo-50 text-indigo-700 border border-indigo-100 flex items-center justify-center font-black text-sm shrink-0">
                    AK.01
                </div>
                <div>
                    <h2 class="text-sm sm:text-base font-bold text-slate-900 leading-tight">
                        {{ $pendaftaran->asesi->nama_lengkap ?? 'Nama Asesi' }}
                    </h2>
                    <p class="text-xs text-slate-500 font-mono">
                        No. Registrasi Pendaftaran: {{ $pendaftaran->nomor_pendaftaran }}
                    </p>
                </div>
            </div>

            <!-- Status Lencana -->
            <div class="flex items-center gap-2">
                @if($isSignedByAsesi && $isSignedByAsesor)
                    <span class="px-3 py-1 rounded-full bg-emerald-50 text-emerald-700 border border-emerald-200 text-xs font-bold flex items-center gap-1.5">
                        <span class="w-2 h-2 rounded-full bg-emerald-500"></span> Disahkan Kedua Pihak
                    </span>
                @elseif($isSignedByAsesi)
                    <span class="px-3 py-1 rounded-full bg-blue-50 text-blue-700 border border-blue-200 text-xs font-bold flex items-center gap-1.5">
                        <span class="w-2 h-2 rounded-full bg-blue-500 animate-pulse"></span> Menunggu Pengesahan Anda
                    </span>
                @else
                    <span class="px-3 py-1 rounded-full bg-amber-50 text-amber-700 border border-amber-200 text-xs font-bold flex items-center gap-1.5">
                        <span class="w-2 h-2 rounded-full bg-amber-500"></span> Menunggu Tanda Tangan Asesi
                    </span>
                @endif
            </div>
        </div>

        <!-- Grid 4 Info -->
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-3 text-xs">
            <div class="bg-slate-50 border border-slate-100 rounded-2xl p-3 space-y-0.5">
                <span class="text-[10px] font-bold uppercase tracking-wider text-slate-400">Skema Sertifikasi</span>
                <div class="font-bold text-slate-800 truncate" title="{{ $pendaftaran->skema->nama_skema ?? '-' }}">
                    {{ $pendaftaran->skema->nama_skema ?? '-' }}
                </div>
                <div class="text-[11px] text-slate-500 font-mono">{{ $pendaftaran->skema->kode_skema ?? '-' }}</div>
            </div>

            <div class="bg-slate-50 border border-slate-100 rounded-2xl p-3 space-y-0.5">
                <span class="text-[10px] font-bold uppercase tracking-wider text-slate-400">Asal Instansi / Sekolah</span>
                <div class="font-bold text-slate-800 truncate" title="{{ $pendaftaran->asesi->profilAsesi->nama_sekolah_instansi ?? '-' }}">
                    {{ $pendaftaran->asesi->profilAsesi->nama_sekolah_instansi ?? 'SMKN 1 Gunungputri' }}
                </div>
                <div class="text-[11px] text-slate-500">NIK: {{ $pendaftaran->asesi->profilAsesi->nik ?? '-' }}</div>
            </div>

            <div class="bg-slate-50 border border-slate-100 rounded-2xl p-3 space-y-0.5">
                <span class="text-[10px] font-bold uppercase tracking-wider text-slate-400">Asesor Penguji</span>
                <div class="font-bold text-slate-800 truncate" title="{{ auth()->user()->nama_lengkap }}">
                    {{ auth()->user()->nama_lengkap }}
                </div>
                <div class="text-[11px] text-slate-500">No. Reg: {{ auth()->user()->nomor_registrasi ?? '-' }}</div>
            </div>

            <div class="bg-slate-50 border border-slate-100 rounded-2xl p-3 space-y-0.5">
                <span class="text-[10px] font-bold uppercase tracking-wider text-slate-400">Jadwal & Lokasi TUK</span>
                <div class="font-bold text-slate-800">
                    {{ $pendaftaran->jadwal ? date('d/m/Y', strtotime($pendaftaran->jadwal->tanggal_uji)) : 'Sesuai Jadwal' }}
                </div>
                <div class="text-[11px] text-slate-500 truncate" title="{{ $pendaftaran->jadwal->nama_tuk ?? 'TUK LSP' }}">
                    {{ $pendaftaran->jadwal->nama_tuk ?? 'TUK SMKN 1 Gunungputri' }}
                </div>
            </div>
        </div>
    </div>

    <!-- BANNER SESI ASESMEN SUDAH DIMULAI -->
    @if($pendaftaran->isRuangUjiOpen() && empty($pendaftaran->rekomendasi))
        <div class="bg-gradient-to-r from-emerald-600 to-teal-600 text-white rounded-2xl p-4 sm:p-5 shadow-sm flex flex-col sm:flex-row sm:items-center justify-between gap-4 animate-in fade-in duration-200">
            <div class="flex items-center gap-3">
                <div class="w-9 h-9 rounded-xl bg-white/20 flex items-center justify-center font-bold text-sm shrink-0">
                    <span class="relative flex h-2.5 w-2.5">
                        <span class="animate-ping absolute inline-flex h-full w-full rounded-full bg-white opacity-80"></span>
                        <span class="relative inline-flex rounded-full h-2.5 w-2.5 bg-white"></span>
                    </span>
                </div>
                <div>
                    <div class="font-extrabold text-sm flex items-center gap-2">
                        <span>Sesi Asesmen Telah Dimulai!</span>
                        <span class="px-2 py-0.5 rounded-md bg-white/20 text-[10px] font-bold uppercase">Sesi Uji Aktif</span>
                    </div>
                    <p class="text-xs text-emerald-100 mt-0.5">
                        Formulir FR.AK.01 telah sah dan ruang uji aktif. Anda dapat langsung melakukan observasi dan penilaian live.
                    </p>
                </div>
            </div>
            <a href="{{ route('asesor.penilaian-live', $pendaftaran->id) }}" 
               class="px-4 py-2 rounded-xl bg-white text-emerald-800 hover:bg-emerald-50 font-bold text-xs shadow-xs transition-colors shrink-0 inline-flex items-center">
                <span>Buka Penilaian Live</span>
            </a>
        </div>
    @endif

    <!-- =========================================================================
         3. FORM PENGESAHAN ASESOR
         ========================================================================= -->
    <form id="formPengesahanAk01" action="{{ route('asesor.ak01.simpan', $pendaftaran->id) }}" method="POST" @submit.prevent="submitPengesahan($event)" class="space-y-5">
        @csrf

        <!-- 1. BAGIAN PENETAPAN UNIT KOMPETENSI -->
        <div class="bg-white rounded-3xl border border-slate-200/90 shadow-2xs overflow-hidden">
            <div class="px-5 py-4 bg-slate-50/80 border-b border-slate-200/80 flex items-center justify-between">
                <div>
                    <h3 class="font-bold text-xs sm:text-sm text-slate-800">1. Unit Kompetensi & Rencana Pengujian</h3>
                    <p class="text-[11px] text-slate-500">Daftar unit kompetensi skema yang akan diuji sesuai kesepakatan asesmen.</p>
                </div>
                <span class="text-xs font-semibold text-slate-600 bg-white border border-slate-200 px-2.5 py-1 rounded-lg">
                    {{ $pendaftaran->skema->unitKompetensi->count() ?? 0 }} Unit
                </span>
            </div>

            <div class="overflow-x-auto">
                <table class="w-full text-left border-collapse text-xs">
                    <thead>
                        <tr class="bg-slate-50/60 border-b border-slate-200 text-[11px] font-bold text-slate-600 uppercase tracking-wider">
                            <th class="py-2.5 px-3 w-10 text-center">No</th>
                            <th class="py-2.5 px-3 w-36 sm:w-44">Kode Unit</th>
                            <th class="py-2.5 px-3 min-w-[200px]">Judul Unit Kompetensi</th>
                            <th class="py-2.5 px-3 w-64 text-right">Rencana Instrumen</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100">
                        @forelse($pendaftaran->skema->unitKompetensi as $idx => $unit)
                            @php
                                $hasBk = $unitHasBkMap[$unit->id] ?? false;
                            @endphp
                            <tr class="hover:bg-slate-50/50 transition-colors {{ $hasBk ? 'bg-amber-50/30' : '' }}">
                                <td class="py-2.5 px-3 text-center text-slate-400 font-medium">{{ $idx + 1 }}</td>
                                <td class="py-2.5 px-3 font-mono font-semibold text-slate-800">{{ $unit->kode_unit }}</td>
                                <td class="py-2.5 px-3 font-medium text-slate-900">
                                    <div class="flex items-center gap-2">
                                        <span>{{ $unit->judul_unit }}</span>
                                        @if($hasBk)
                                            <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-md bg-amber-100 text-amber-800 font-semibold text-[10px] shrink-0 border border-amber-200">
                                                <span class="w-1.5 h-1.5 rounded-full bg-amber-500 mr-1"></span> Perlu Verifikasi Praktik Langsung
                                            </span>
                                        @endif
                                    </div>
                                </td>
                                <td class="py-2.5 px-3 text-right">
                                    @php
                                        $instUnit = $pendaftaran->getInstrumenPerUnit($unit->id);
                                    @endphp
                                    <div class="inline-flex items-center gap-1.5 justify-end flex-wrap">
                                        @if($instUnit['clo'])
                                            <span class="px-2 py-0.5 rounded-md bg-blue-50 text-blue-700 border border-blue-100 font-medium text-[10px]" title="Ceklis Observasi Praktik Langsung">
                                                Observasi (IA.01)
                                            </span>
                                        @endif
                                        @if($instUnit['dpt'])
                                            <span class="px-2 py-0.5 rounded-md bg-sky-50 text-sky-700 border border-sky-100 font-medium text-[10px]" title="Tugas Praktik Demonstrasi">
                                                Tugas Praktik (IA.02)
                                            </span>
                                        @endif
                                        @if($instUnit['pmo'])
                                            <span class="px-2 py-0.5 rounded-md bg-amber-50 text-amber-700 border border-amber-100 font-medium text-[10px]" title="Pertanyaan Pendukung Observasi">
                                                Tanya Jawab (IA.03)
                                            </span>
                                        @endif
                                        @if($instUnit['dpe'])
                                            <span class="px-2 py-0.5 rounded-md bg-indigo-50 text-indigo-700 border border-indigo-100 font-medium text-[10px]" title="Uji Tertulis CBT & Esai">
                                                Uji Tertulis (IA.05/06)
                                            </span>
                                        @endif
                                        @if($instUnit['dpl'])
                                            <span class="px-2 py-0.5 rounded-md bg-amber-50 text-amber-700 border border-amber-100 font-medium text-[10px]" title="Pertanyaan Lisan">
                                                Tanya Jawab Lisan (IA.07)
                                            </span>
                                        @endif
                                        @if($instUnit['vp'])
                                            <span class="px-2 py-0.5 rounded-md bg-emerald-50 text-emerald-700 border border-emerald-100 font-medium text-[10px]" title="Verifikasi Portofolio">
                                                Portofolio (IA.08)
                                            </span>
                                        @endif
                                        @if($instUnit['pw'])
                                            <span class="px-2 py-0.5 rounded-md bg-purple-50 text-purple-700 border border-purple-100 font-medium text-[10px]" title="Pertanyaan Wawancara">
                                                Wawancara (IA.09)
                                            </span>
                                        @endif
                                        @if($instUnit['crp'])
                                            <span class="px-2 py-0.5 rounded-md bg-teal-50 text-teal-700 border border-teal-100 font-medium text-[10px]" title="Ceklis Reviu Produk">
                                                Reviu Produk (IA.11)
                                            </span>
                                        @endif
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="4" class="py-6 text-center text-slate-400 italic">Tidak ada unit kompetensi pada skema ini.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

        <!-- 2. KESEPAKATAN TUK & METODE PENGUMPULAN BUKTI (DIISI OLEH ASESOR) -->
        <div class="bg-white rounded-3xl border border-slate-200/90 shadow-2xs p-5 space-y-4">
            <div class="border-b border-slate-100 pb-3 flex items-center justify-between">
                <div>
                    <h3 class="font-bold text-xs sm:text-sm text-slate-800 flex items-center gap-2">
                        <span>2. Kesepakatan Pelaksanaan Asesmen (Ditetapkan oleh Asesor)</span>
                    </h3>
                    <p class="text-[11px] text-slate-500">Tentukan Tempat Uji Kompetensi (TUK) dan rencana metode pengumpulan bukti untuk asesi ini.</p>
                </div>
                <span class="px-2.5 py-1 rounded-lg bg-indigo-50 text-indigo-700 text-[10px] font-bold border border-indigo-100">
                    Wajib Diisi Asesor
                </span>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-4 text-xs">
                <!-- Pilihan TUK -->
                <div class="space-y-1.5">
                    <label class="font-bold text-slate-700 block">
                        Jenis Tempat Uji Kompetensi (TUK) <span class="text-rose-500">*</span>
                    </label>
                    @php $tukDipilih = old('tuk_type', $pendaftaran->tuk_type ?? ''); @endphp
                    <select name="tuk_type" 
                            {{ $isSignedByAsesor ? 'disabled' : 'required' }} 
                            class="w-full bg-slate-50 border border-slate-200 rounded-xl px-3 py-2.5 text-slate-800 font-semibold focus:ring-1 focus:ring-indigo-500 focus:bg-white transition-colors">
                        <option value="" {{ $tukDipilih === '' ? 'selected' : '' }}></option>
                        <option value="Sewaktu" {{ $tukDipilih === 'Sewaktu' ? 'selected' : '' }}>TUK Sewaktu (SMKN 1 Gunungputri / Sekolah Mitra)</option>
                        <option value="Tempat Kerja" {{ $tukDipilih === 'Tempat Kerja' ? 'selected' : '' }}>TUK Tempat Kerja / Fasilitas Industri (DUDI)</option>
                        <option value="Mandiri" {{ $tukDipilih === 'Mandiri' ? 'selected' : '' }}>TUK Mandiri</option>
                    </select>
                </div>

                <!-- Metode Pengumpulan Bukti -->
                <div class="space-y-1.5">
                    <label class="font-bold text-slate-700 block">
                        Metode Pengumpulan Bukti yang Ditetapkan <span class="text-rose-500">*</span>
                    </label>
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-2 pt-0.5">
                        @php $savedBukti = is_array($pendaftaran->bukti_dikumpulkan) ? $pendaftaran->bukti_dikumpulkan : json_decode($pendaftaran->bukti_dikumpulkan ?? '[]', true) ?? []; @endphp
                        <label class="flex items-center gap-2 p-2 rounded-xl bg-slate-50 border border-slate-200/80 text-slate-700 {{ $isSignedByAsesor ? 'cursor-default' : 'cursor-pointer hover:bg-slate-100' }}">
                            <input type="checkbox" name="bukti_dikumpulkan[]" value="Observasi Praktik Demonstrasi"
                                   {{ in_array('Observasi Praktik Demonstrasi', $savedBukti) || in_array('Uji Praktik / Observasi Demonstrasi', $savedBukti) ? 'checked' : '' }}
                                   {{ $isSignedByAsesor ? 'disabled' : '' }}
                                   class="rounded text-indigo-600 focus:ring-0">
                            <span class="text-[11px] font-medium">Observasi Praktik</span>
                        </label>
                        <label class="flex items-center gap-2 p-2 rounded-xl bg-slate-50 border border-slate-200/80 text-slate-700 {{ $isSignedByAsesor ? 'cursor-default' : 'cursor-pointer hover:bg-slate-100' }}">
                            <input type="checkbox" name="bukti_dikumpulkan[]" value="Uji Tertulis (CBT)"
                                   {{ in_array('Uji Tertulis (CBT)', $savedBukti) ? 'checked' : '' }}
                                   {{ $isSignedByAsesor ? 'disabled' : '' }}
                                   class="rounded text-indigo-600 focus:ring-0">
                            <span class="text-[11px] font-medium">Uji Tertulis CBT</span>
                        </label>
                        <label class="flex items-center gap-2 p-2 rounded-xl bg-slate-50 border border-slate-200/80 text-slate-700 {{ $isSignedByAsesor ? 'cursor-default' : 'cursor-pointer hover:bg-slate-100' }}">
                            <input type="checkbox" name="bukti_dikumpulkan[]" value="Tanya Jawab Lisan"
                                   {{ in_array('Tanya Jawab Lisan', $savedBukti) ? 'checked' : '' }}
                                   {{ $isSignedByAsesor ? 'disabled' : '' }}
                                   class="rounded text-indigo-600 focus:ring-0">
                            <span class="text-[11px] font-medium">Tanya Jawab Lisan</span>
                        </label>
                        <label class="flex items-center gap-2 p-2 rounded-xl bg-slate-50 border border-slate-200/80 text-slate-700 {{ $isSignedByAsesor ? 'cursor-default' : 'cursor-pointer hover:bg-slate-100' }}">
                            <input type="checkbox" name="bukti_dikumpulkan[]" value="Verifikasi Portofolio"
                                   {{ in_array('Verifikasi Portofolio', $savedBukti) || in_array('Hasil Verifikasi Portofolio', $savedBukti) ? 'checked' : '' }}
                                   {{ $isSignedByAsesor ? 'disabled' : '' }}
                                   class="rounded text-indigo-600 focus:ring-0">
                            <span class="text-[11px] font-medium">Verifikasi Portofolio</span>
                        </label>
                    </div>
                </div>
            </div>

            <div class="space-y-1 pt-1 text-xs">
                <label class="font-bold text-slate-700 block">Catatan / Bukti Lainnya (Opsional)</label>
                <input type="text" name="bukti_dikumpulkan_lainnya" 
                       value="{{ old('bukti_dikumpulkan_lainnya', $pendaftaran->bukti_dikumpulkan_lainnya) }}"
                       {{ $isSignedByAsesor ? 'disabled' : '' }}
                       placeholder="Contoh: Wawancara langsung, presentasi produk portofolio"
                       class="w-full bg-slate-50 border border-slate-200 rounded-xl px-3 py-2 text-slate-800 text-xs focus:ring-1 focus:ring-indigo-500 focus:bg-white transition-colors">
            </div>
        </div>

        <!-- 3. KLAUSUL PERSETUJUAN & KERAHASIAAN RESMI -->
        <div class="bg-slate-50 rounded-3xl border border-slate-200/90 p-5 space-y-3">
            <h3 class="font-bold text-xs sm:text-sm text-slate-800 flex items-center gap-2">
                <span>3. Klausul Persetujuan & Komitmen Kerahasiaan BNSP</span>
            </h3>

            <div class="text-xs text-slate-600 space-y-2 bg-white rounded-2xl p-4 border border-slate-200/80 leading-relaxed font-sans">
                <p>
                    <strong>Pernyataan Asesor:</strong> &ldquo;Saya menyatakan bahwa saya telah menjelaskan proses asesmen secara lengkap, hak banding, serta prosedur pengumpulan bukti kepada Asesi. Saya akan melaksanakan asesmen secara objektif, independen, dan berpegang teguh pada prinsip Valid, Asli, Terkini, dan Memadai (VATM).&rdquo;
                </p>
                <p>
                    <strong>Komitmen Kerahasiaan:</strong> &ldquo;Seluruh pihak berkomitmen menjaga kerahasiaan materi uji, hasil asesmen mandiri, dan catatan evaluasi sesuai pedoman Badan Nasional Sertifikasi Profesi (BNSP).&rdquo;
                </p>
            </div>

            @if(!$isSignedByAsesor)
                <label class="flex items-start gap-2.5 text-xs text-slate-800 font-semibold cursor-pointer pt-1 select-none">
                    <input type="checkbox" x-model="agreedToClause" required class="mt-0.5 rounded text-indigo-600 focus:ring-0">
                    <span>Saya menyatakan telah menyusun rencana asesmen dan siap mengesahkan formulir FR.AK.01 ini.</span>
                </label>
            @endif
        </div>

        <!-- 4. TANDA TANGAN 2 PIHAK -->
        <div class="bg-white rounded-3xl border border-slate-200/90 shadow-2xs p-5 space-y-4">
            <h3 class="font-bold text-xs sm:text-sm text-slate-800 border-b border-slate-100 pb-2">
                4. Tanda Tangan & Pengesahan Kedua Pihak
            </h3>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-5">
                
                <!-- KOLOM ASESI (READ ONLY PREVIEW) -->
                <div class="border border-slate-200 rounded-2xl p-4 space-y-2.5 bg-slate-50/50">
                    <div class="flex items-center justify-between">
                        <div>
                            <span class="text-[11px] font-bold text-slate-700 uppercase tracking-wider block">Tanda Tangan Asesi</span>
                            <span class="text-xs font-semibold text-slate-900">{{ $pendaftaran->asesi->nama_lengkap }}</span>
                        </div>
                        @if($isSignedByAsesi)
                            <span class="px-2 py-0.5 rounded-md bg-emerald-100 text-emerald-800 font-bold text-[10px]">
                                ✓ Telah Ditandatangani
                            </span>
                        @else
                            <span class="px-2 py-0.5 rounded-md bg-amber-100 text-amber-800 font-bold text-[10px]">
                                Menunggu TTD Asesi
                            </span>
                        @endif
                    </div>

                    <div class="h-36 bg-white rounded-xl border border-slate-200 flex items-center justify-center p-2">
                        @if($pendaftaran->tanda_tangan_asesi_ak01)
                            <img src="{{ asset($pendaftaran->tanda_tangan_asesi_ak01) }}" alt="Tanda Tangan Asesi" class="max-h-28 object-contain">
                        @else
                            <div class="flex flex-col items-center justify-center text-center p-3 space-y-1">
                                <span class="text-xs font-semibold text-slate-600">Menunggu Tanda Tangan Asesi</span>
                                <span class="text-[11px] text-slate-400">Asesi akan meninjau dan menandatangani formulir ini setelah Anda mengisinya.</span>
                            </div>
                        @endif
                    </div>
                    <div class="text-[11px] text-slate-500">
                        Waktu TTD Asesi: <strong>{{ $pendaftaran->tanggal_ttd_asesi_ak01 ? date('d/m/Y H:i', strtotime($pendaftaran->tanggal_ttd_asesi_ak01)) : '-' }}</strong>
                    </div>
                </div>

                <!-- KOLOM ASESOR -->
                <div class="border border-slate-200 rounded-2xl p-4 space-y-3 bg-slate-50/50">
                    <div class="flex items-center justify-between">
                        <div>
                            <span class="text-[11px] font-bold text-slate-700 uppercase tracking-wider block">Tanda Tangan Asesor</span>
                            <span class="text-xs font-semibold text-slate-900">{{ auth()->user()->nama_lengkap }}</span>
                        </div>
                        @if($isSignedByAsesor)
                            <span class="px-2 py-0.5 rounded-md bg-emerald-100 text-emerald-800 font-bold text-[10px]">
                                ✓ Telah Disahkan
                            </span>
                        @else
                            <span class="px-2 py-0.5 rounded-md bg-indigo-100 text-indigo-800 font-bold text-[10px] animate-pulse">
                                Siap Pengesahan
                            </span>
                        @endif
                    </div>

                    @if($isSignedByAsesor)
                        <!-- Preview TTD Asesor Tersimpan -->
                        <div class="h-36 bg-white rounded-xl border border-slate-200 flex flex-col items-center justify-center p-2">
                            @if($pendaftaran->tanda_tangan_asesor_ak01)
                                <img src="{{ asset($pendaftaran->tanda_tangan_asesor_ak01) }}" alt="Tanda Tangan Asesor" class="max-h-24 object-contain">
                            @else
                                <span class="text-xs font-bold text-emerald-800">Telah Disahkan oleh Asesor</span>
                            @endif
                        </div>
                        <div class="text-[11px] text-slate-500">
                            Waktu Pengesahan: <strong>{{ $pendaftaran->tanggal_ttd_asesor_ak01 ? date('d/m/Y H:i', strtotime($pendaftaran->tanggal_ttd_asesor_ak01)) : 'Tercatat' }}</strong>
                        </div>
                    @else
                        <!-- OPSI TANDA TANGAN ASESOR (PROFIL vs CANVAS) -->
                        <div class="space-y-2.5">
                            @if(!empty(auth()->user()->tanda_tangan))
                                <div class="flex items-center gap-2 p-1 bg-slate-200/70 rounded-xl text-xs font-semibold">
                                    <button type="button" 
                                            @click="modeTtd = 'profil'"
                                            :class="modeTtd === 'profil' ? 'bg-white text-indigo-700 shadow-2xs' : 'text-slate-600 hover:text-slate-900'"
                                            class="flex-1 py-1.5 px-2.5 rounded-lg transition-all flex items-center justify-center gap-1.5 cursor-pointer">
                                        <span>Gunakan TTD Profil</span>
                                    </button>
                                    <button type="button" 
                                            @click="modeTtd = 'canvas'; $nextTick(() => initSignatureEngine())"
                                            :class="modeTtd === 'canvas' ? 'bg-white text-indigo-700 shadow-2xs' : 'text-slate-600 hover:text-slate-900'"
                                            class="flex-1 py-1.5 px-2.5 rounded-lg transition-all flex items-center justify-center gap-1.5 cursor-pointer">
                                        <span>Gambar TTD Baru</span>
                                    </button>
                                </div>
                            @endif

                            <!-- Mode 1: Tanda Tangan Profil -->
                            <div x-show="modeTtd === 'profil'" class="space-y-2">
                                <div class="h-32 bg-white rounded-xl border border-indigo-200 p-2 flex flex-col items-center justify-center relative overflow-hidden bg-indigo-50/20">
                                    <div class="absolute top-2 right-2 px-2 py-0.5 rounded-md bg-emerald-100 text-emerald-800 text-[10px] font-bold flex items-center gap-1">
                                        <span class="w-1.5 h-1.5 rounded-full bg-emerald-500 mr-1"></span> Siap Digunakan
                                    </div>
                                    <img src="{{ asset(auth()->user()->tanda_tangan) }}" alt="Spesimen Tanda Tangan" class="max-h-24 object-contain">
                                </div>
                                <p class="text-[11px] text-slate-500">
                                    Menggunakan spesimen tanda tangan digital yang tersimpan pada profil Asesor Anda.
                                </p>
                            </div>

                            <!-- Mode 2: Interactive Signature Canvas -->
                            <div x-show="modeTtd === 'canvas'" class="space-y-1.5">
                                <div class="border border-slate-300 rounded-xl bg-white relative overflow-hidden">
                                    <canvas id="canvasAk01Asesor" width="800" height="240" class="w-full h-32 bg-white cursor-crosshair block touch-none" style="touch-action: none; -ms-touch-action: none;"></canvas>
                                </div>
                                <div class="flex items-center justify-between text-[11px]">
                                    <span class="text-slate-400">Tanda tangani menggunakan mouse atau layar sentuh.</span>
                                    <button type="button" @click="clearSignature()" class="text-rose-600 hover:text-rose-800 font-semibold cursor-pointer">
                                        [ Hapus / Ulangi ]
                                    </button>
                                </div>
                            </div>

                            <input type="hidden" name="tanda_tangan_asesor_ak01" id="inputSignatureAk01Asesor" x-ref="signatureInput">
                        </div>
                    @endif
                </div>

            </div>
        </div>

        @if(!$isSignedByAsesi)
            <div class="p-3.5 rounded-2xl bg-indigo-50 border border-indigo-200/90 text-indigo-900 text-xs flex items-start gap-3 shadow-2xs">
                <div class="space-y-0.5">
                    <h4 class="font-bold text-indigo-950 text-xs">Alur Pengisian & Pengesahan</h4>
                    <p class="text-indigo-800 leading-relaxed">
                        Sebagai Asesor, Anda menetapkan jenis TUK, metode pengumpulan bukti, serta menandatangani formulir ini terlebih dahulu. Setelah Anda sahkan, formulir akan diteruskan ke Asesi ({{ $pendaftaran->asesi->nama_lengkap ?? 'Asesi' }}) untuk ditandatangani.
                    </p>
                </div>
            </div>
        @endif

        <!-- ACTION BAR -->
        <div class="bg-white rounded-2xl border border-slate-200/90 p-4 shadow-2xs flex flex-col sm:flex-row items-center justify-between gap-3">
            <a href="{{ (url()->previous() && url()->previous() !== url()->current()) ? url()->previous() : route('asesor.daftar-peserta') }}" 
               onclick="if (document.referrer && document.referrer !== window.location.href) { window.location.href = document.referrer; return false; } else if (window.history.length > 1) { window.history.back(); return false; }"
               class="w-full sm:w-auto px-4 py-2.5 rounded-xl border border-slate-200 hover:bg-slate-50 text-slate-700 text-xs font-semibold transition-colors text-center cursor-pointer">
                &larr; Kembali
            </a>

            @if(!$isSignedByAsesor)
                <button type="submit" 
                        :disabled="!agreedToClause || isSubmitting"
                        :class="(!agreedToClause || isSubmitting) ? 'opacity-50 cursor-not-allowed bg-indigo-400' : 'bg-indigo-600 hover:bg-indigo-700 cursor-pointer shadow-sm'"
                        class="w-full sm:w-auto px-6 py-2.5 text-white text-xs font-bold rounded-xl transition-all flex items-center justify-center gap-2">
                    <template x-if="!isSubmitting">
                        <span>Simpan & Sahkan FR.AK.01</span>
                    </template>
                    <template x-if="isSubmitting">
                        <span>Menyimpan Pengesahan...</span>
                    </template>
                </button>
            @else
                <div class="flex items-center gap-2.5">
                    <span class="text-xs text-emerald-700 font-semibold flex items-center gap-1.5">
                        <span class="w-1.5 h-1.5 rounded-full bg-emerald-500 mr-1.5"></span>
                        <span>FR.AK.01 Telah Disahkan</span>
                    </span>
                    <a href="{{ route('asesor.dashboard') }}" 
                       class="px-5 py-2.5 rounded-xl bg-indigo-600 hover:bg-indigo-700 text-white font-bold text-xs shadow-xs transition-colors flex items-center">
                        <span>Kembali ke Dashboard</span>
                    </a>
                </div>
            @endif
        </div>
    </form>
</div>

<!-- Alpine.js & Signature Engine Logic -->
<script>
    function asesorAk01App() {
        return {
            agreedToClause: {{ $isSignedByAsesor ? 'true' : 'false' }},
            isSubmitting: false,
            modeTtd: '{{ !empty(auth()->user()->tanda_tangan) ? 'profil' : 'canvas' }}',
            engine: null,

            init() {
                @if(!$isSignedByAsesor)
                    this.$nextTick(() => {
                        if (this.modeTtd === 'canvas') {
                            this.initSignatureEngine();
                        }
                    });

                    this.$watch('modeTtd', (val) => {
                        if (val === 'canvas') {
                            this.$nextTick(() => {
                                this.initSignatureEngine();
                            });
                        }
                    });
                @endif
            },

            initSignatureEngine() {
                const canvas = document.getElementById('canvasAk01Asesor');
                const input = document.getElementById('inputSignatureAk01Asesor');
                if (!canvas) return;

                if (!this.engine) {
                    this.engine = initUnifiedSignatureEngine(canvas, input);
                } else {
                    this.engine.resize();
                }
            },

            clearSignature() {
                if (this.engine) {
                    this.engine.clear();
                }
                if (this.$refs.signatureInput) {
                    this.$refs.signatureInput.value = '';
                }
            },

            submitPengesahan(e) {
                if (this.isSubmitting) return;

                if (!this.agreedToClause) {
                    Swal.fire({
                        icon: 'warning',
                        title: 'Persetujuan Klausul',
                        text: 'Silakan centang pernyataan persetujuan sebelum mengesahkan FR.AK.01.',
                        confirmButtonColor: '#4f46e5'
                    });
                    return;
                }

                if (this.modeTtd === 'profil') {
                    if (this.$refs.signatureInput) {
                        this.$refs.signatureInput.value = '{{ auth()->user()->tanda_tangan }}';
                    }
                } else if (this.modeTtd === 'canvas') {
                    if (this.engine) {
                        if (this.engine.isEmpty()) {
                            Swal.fire({
                                icon: 'warning',
                                title: 'Tanda Tangan Belum Dibubuhkan',
                                text: 'Silakan bubuhkan tanda tangan digital Anda pada canvas atau pilih opsi gunakan tanda tangan profil.',
                                confirmButtonColor: '#4f46e5'
                            });
                            return;
                        }
                        const dataUrl = this.engine.toDataURL();
                        if (this.$refs.signatureInput) {
                            this.$refs.signatureInput.value = dataUrl;
                        }
                    }
                }

                this.isSubmitting = true;
                document.getElementById('formPengesahanAk01').submit();
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
</script>
@endsection
