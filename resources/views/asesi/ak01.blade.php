@extends('tata-letak.dasbor')

@section('judul', 'FR.AK.01 - Persetujuan Asesmen & Kerahasiaan')

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
    $pengguna = auth()->user();
    $isSignedByAsesi = !empty($pendaftaran->tanda_tangan_asesi_ak01) || in_array($pendaftaran->status_ak01, ['disetujui_asesi', 'selesai']);
    $isSignedByAsesor = !empty($pendaftaran->tanda_tangan_asesor_ak01) || in_array($pendaftaran->status_ak01, ['disetujui_asesor', 'selesai']);
    $isLocked = true; // Formulir kesepakatan selalu read-only untuk Asesi
    $savedBukti = (array) ($pendaftaran->bukti_dikumpulkan ?? ['Observasi Praktik Demonstrasi', 'Uji Tertulis (CBT)', 'Tanya Jawab Lisan']);

    // Ambil tanda tangan yang sudah ada secara hierarki:
    // 1. TTD AK.01 jika sudah ada
    // 2. TTD pendaftaran (APL.01 / APL.02)
    // 3. TTD profil akun asesi
    $existingTtd = $pendaftaran->tanda_tangan_asesi_ak01 
        ?: ($pendaftaran->tanda_tangan_asesi 
        ?: ($pendaftaran->asesi->tanda_tangan ?? $pengguna->tanda_tangan ?? null));

    $hasExistingTtd = !empty($existingTtd);
    $existingTtdUrl = null;
    if ($hasExistingTtd) {
        $existingTtdUrl = \Illuminate\Support\Str::startsWith($existingTtd, ['data:', 'http://', 'https://'])
            ? $existingTtd
            : asset($existingTtd);
    }
@endphp

<div class="max-w-5xl mx-auto px-2 sm:px-4 py-3 space-y-4" x-data="ak01App()">

    <!-- =========================================================================
         TOP BREADCRUMB & MULTI-SCHEME SWITCHER
         ========================================================================= -->
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-2 pb-1">
        <div class="flex items-center gap-2 text-xs text-slate-500">
            <a href="{{ route('asesi.dashboard') }}" class="hover:text-blue-600 font-medium">Dashboard</a>
            <span>/</span>
            <a href="{{ route('asesi.tahapan', ['step' => 2, 'pendaftaran_id' => $pendaftaran->id]) }}" class="hover:text-blue-600 font-medium">Tahapan Asesmen</a>
            <span>/</span>
            <span class="text-slate-800 font-bold">FR.AK.01</span>
        </div>

        @if(isset($semuaPendaftaran) && $semuaPendaftaran->count() > 1)
            <div class="flex items-center gap-2">
                <span class="text-xs text-slate-500 font-medium">Skema:</span>
                <select class="text-xs bg-white border border-slate-200 rounded-lg px-2.5 py-1 text-slate-800 font-semibold focus:ring-1 focus:ring-blue-500"
                        onchange="window.location.href='{{ url('/asesi/ak-01') }}/' + this.value">
                    @foreach($semuaPendaftaran as $sp)
                        <option value="{{ $sp->id }}" {{ $sp->id === $pendaftaran->id ? 'selected' : '' }}>
                            {{ $sp->skema->nama_skema ?? 'Skema #' . $sp->id }}
                        </option>
                    @endforeach
                </select>
            </div>
        @endif
    </div>

    <!-- =========================================================================
         HEADER CARD: FR.AK.01 PERSURATAN RESMI BNSP
         ========================================================================= -->
    <div class="bg-white rounded-2xl border border-slate-200/90 shadow-2xs p-4 sm:p-5 space-y-3">
        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3 border-b border-slate-100 pb-3">
            <div class="flex items-center gap-3">
                <div class="w-10 h-10 rounded-xl bg-blue-50 text-blue-700 border border-blue-100 flex items-center justify-center font-black text-sm shrink-0">
                    AK.01
                </div>
                <div>
                    <h1 class="text-base sm:text-lg font-bold text-slate-900 leading-tight">
                        Persetujuan Asesmen & Kerahasiaan
                    </h1>
                    <p class="text-xs text-slate-500">
                        Formulir kesepakatan rencana asesmen dan komitmen kerahasiaan antara Asesi dan Asesor.
                    </p>
                </div>
            </div>

            <!-- Status Pill Header -->
            <div class="flex items-center gap-2">
                @if($isSignedByAsesi && $isSignedByAsesor)
                    <span class="px-3 py-1 rounded-full bg-emerald-50 text-emerald-700 border border-emerald-200 text-xs font-bold flex items-center gap-1.5">
                        <span class="w-2 h-2 rounded-full bg-emerald-500"></span>
                        Disahkan Kedua Pihak
                    </span>
                @elseif($isSignedByAsesor)
                    <span class="px-3 py-1 rounded-full bg-indigo-50 text-indigo-700 border border-indigo-200 text-xs font-bold flex items-center gap-1.5">
                        <span class="w-2 h-2 rounded-full bg-indigo-500 animate-pulse"></span>
                        Ditetapkan Asesor &bull; Menunggu Tanda Tangan Asesi
                    </span>
                @elseif($isSignedByAsesi)
                    <span class="px-3 py-1 rounded-full bg-amber-50 text-amber-700 border border-amber-200 text-xs font-bold flex items-center gap-1.5">
                        <span class="w-2 h-2 rounded-full bg-amber-500 animate-pulse"></span>
                        Menunggu Pengesahan Asesor
                    </span>
                @else
                    <span class="px-3 py-1 rounded-full bg-slate-100 text-slate-700 border border-slate-200 text-xs font-bold flex items-center gap-1.5">
                        <span class="w-2 h-2 rounded-full bg-slate-400"></span>
                        Menunggu Tanda Tangan
                    </span>
                @endif
            </div>
        </div>

        <!-- Metadata Grid (2 Kolom Desktop, Wrap Mobile) -->
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-2.5 pt-1 text-xs">
            <div class="bg-slate-50 border border-slate-100 rounded-xl p-2.5 space-y-0.5">
                <span class="text-[10px] font-bold uppercase tracking-wider text-slate-400">Skema Sertifikasi</span>
                <div class="font-bold text-slate-800 truncate" title="{{ $pendaftaran->skema->nama_skema ?? '-' }}">
                    {{ $pendaftaran->skema->nama_skema ?? '-' }}
                </div>
                <div class="text-[11px] text-slate-500 font-mono">{{ $pendaftaran->skema->kode_skema ?? '-' }}</div>
            </div>

            <div class="bg-slate-50 border border-slate-100 rounded-xl p-2.5 space-y-0.5">
                <span class="text-[10px] font-bold uppercase tracking-wider text-slate-400">Nama Asesi</span>
                <div class="font-bold text-slate-800 truncate" title="{{ $pendaftaran->asesi->nama_lengkap ?? '-' }}">
                    {{ $pendaftaran->asesi->nama_lengkap ?? '-' }}
                </div>
                <div class="text-[11px] text-slate-500">Reg: {{ $pendaftaran->nomor_pendaftaran }}</div>
            </div>

            <div class="bg-slate-50 border border-slate-100 rounded-xl p-2.5 space-y-0.5">
                <span class="text-[10px] font-bold uppercase tracking-wider text-slate-400">Asesor Penguji</span>
                <div class="font-bold text-slate-800 truncate" title="{{ $pendaftaran->asesor->nama_lengkap ?? $pendaftaran->jadwal?->asesor?->nama_lengkap ?? 'Akan Ditugaskan' }}">
                    {{ $pendaftaran->asesor->nama_lengkap ?? $pendaftaran->jadwal?->asesor?->nama_lengkap ?? 'Akan Ditugaskan' }}
                </div>
                <div class="text-[11px] text-slate-500">No. Reg: {{ $pendaftaran->asesor->nomor_registrasi ?? '-' }}</div>
            </div>

            <div class="bg-slate-50 border border-slate-100 rounded-xl p-2.5 space-y-0.5">
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

    <!-- BANNER PANDUAN PENGISIAN ASESI & STATUS PENGESAHAN -->
    @if(!$isSignedByAsesi && !$isSignedByAsesor)
        <div class="bg-gradient-to-r from-blue-600 to-indigo-600 text-white rounded-2xl p-4 sm:p-5 shadow-sm flex flex-col sm:flex-row sm:items-center justify-between gap-4 animate-in fade-in duration-200">
            <div class="flex items-center gap-3">
                <div class="w-9 h-9 rounded-xl bg-white/20 flex items-center justify-center font-bold text-xs shrink-0 tracking-wider">
                    AK.01
                </div>
                <div>
                    <div class="font-extrabold text-sm flex items-center gap-2">
                        <span>Persetujuan Asesmen & Kerahasiaan (FR.AK.01)</span>
                        <span class="px-2 py-0.5 rounded-md bg-white/20 text-[10px] font-bold uppercase">Disetujui Otomatis</span>
                    </div>
                    <p class="text-xs text-blue-100 mt-0.5">
                        Rencana pelaksanaan asesmen (TUK dan metode bukti) telah ditetapkan oleh Asesor / LSP. Silakan periksa rincian kesepakatan dan setujui dengan membubuhkan tanda tangan digital Anda di bawah. Formulir langsung disahkan secara otomatis tanpa perlu menunggu verifikasi asesor.
                    </p>
                </div>
            </div>
        </div>
    @elseif($isSignedByAsesor && !$isSignedByAsesi)
        <div class="bg-gradient-to-r from-blue-600 to-indigo-600 text-white rounded-2xl p-4 sm:p-5 shadow-sm flex flex-col sm:flex-row sm:items-center justify-between gap-4 animate-in fade-in duration-200">
            <div class="flex items-center gap-3">
                <div class="w-9 h-9 rounded-xl bg-white/20 flex items-center justify-center font-bold text-xs shrink-0 tracking-wider">
                    AK.01
                </div>
                <div>
                    <div class="font-extrabold text-sm flex items-center gap-2">
                        <span>Rencana Asesmen Telah Ditetapkan oleh Asesor</span>
                        <span class="px-2 py-0.5 rounded-md bg-white/20 text-[10px] font-bold uppercase">Siap Disetujui</span>
                    </div>
                    <p class="text-xs text-blue-100 mt-0.5">
                        Asesor Penguji ({{ $pendaftaran->asesor->nama_lengkap ?? 'Asesor' }}) telah menetapkan dan mengesahkan formulir FR.AK.01 ini. Silakan periksa kesepakatan dan bubuhkan/konfirmasi tanda tangan digital Anda untuk menyetujui.
                    </p>
                </div>
            </div>
        </div>
    @endif

    <!-- BANNER SESI ASESMEN SUDAH DIMULAI -->
    @if($pendaftaran->isRuangUjiOpen() && empty($pendaftaran->rekomendasi))
        <div class="bg-gradient-to-r from-emerald-600 to-teal-600 text-white rounded-2xl p-4 sm:p-5 shadow-sm flex flex-col sm:flex-row sm:items-center justify-between gap-4 animate-in fade-in duration-200">
            <div class="flex items-center gap-3">
                <div class="w-9 h-9 rounded-xl bg-white/20 flex items-center justify-center font-bold text-xs shrink-0">
                    AKTIF
                </div>
                <div>
                    <div class="font-extrabold text-sm flex items-center gap-2">
                        <span>Sesi Asesmen Telah Dimulai</span>
                        <span class="px-2 py-0.5 rounded-md bg-white/20 text-[10px] font-bold uppercase">Ruang Uji Aktif</span>
                    </div>
                    <p class="text-xs text-emerald-100 mt-0.5">
                        Formulir FR.AK.01 telah sah. Anda dapat langsung mengerjakan instrumen ujian pada Ruang Ujian Online.
                    </p>
                </div>
            </div>
            <a href="{{ route('asesi.ujian', ['pendaftaran_id' => $pendaftaran->id]) }}" 
               class="px-4 py-2 rounded-xl bg-white text-emerald-800 hover:bg-emerald-50 font-bold text-xs shadow-xs transition-colors shrink-0 inline-flex items-center">
                <span>Buka Ruang Ujian</span>
            </a>
        </div>
    @endif

    <!-- =========================================================================
         MAIN FORM CONTAINER
         ========================================================================= -->
    <form id="formAk01" action="{{ route('asesi.ak01.sign', $pendaftaran->id) }}" method="POST" @submit.prevent="submitAk01($event)" class="space-y-4">
        @csrf
        <input type="hidden" name="pendaftaran_id" value="{{ $pendaftaran->id }}">

        <!-- 1. BAGIAN PENETAPAN INSTRUMEN ASESMEN & UNIT KOMPETENSI -->
        <div class="bg-white rounded-2xl border border-slate-200/90 shadow-2xs overflow-hidden">
            <div class="px-4 py-3 bg-slate-50/80 border-b border-slate-200/80 flex items-center justify-between">
                <div>
                    <h2 class="font-bold text-xs sm:text-sm text-slate-800">1. Penetapan Unit Kompetensi & Metode Asesmen</h2>
                    <p class="text-[11px] text-slate-500">Daftar unit kompetensi dan rencana metode uji yang telah ditetapkan sesuai skema sertifikasi.</p>
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
                            <th class="py-2.5 px-3 w-64 text-right">Rencana Metode Asesmen</th>
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
                                            <span class="inline-flex items-center px-2 py-0.5 rounded-md bg-amber-100 text-amber-800 font-semibold text-[10px] shrink-0 border border-amber-200" title="Terdapat butir KUK yang memerlukan verifikasi praktik langsung">
                                                Perlu Verifikasi Praktik
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

        <!-- 2. KESEPAKATAN TUK & METODE BUKTI (DITETAPKAN OLEH ASESOR / LSP) -->
        <div class="bg-white rounded-2xl border border-slate-200/90 shadow-2xs p-4 sm:p-5 space-y-4">
            <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-2 border-b border-slate-100 pb-2">
                <div>
                    <h2 class="font-bold text-xs sm:text-sm text-slate-800 flex items-center gap-2">
                        <span>2. Kesepakatan Pelaksanaan Asesmen</span>
                        <span class="inline-flex items-center px-2 py-0.5 rounded-md bg-slate-100 text-slate-600 font-semibold text-[10px] border border-slate-200">
                            Ditetapkan Asesor / LSP
                        </span>
                    </h2>
                    <p class="text-[11px] text-slate-500">
                        Rencana Tempat Uji Kompetensi (TUK) dan metode pengumpulan bukti telah ditetapkan oleh Asesor / LSP sesuai skema sertifikasi (read-only).
                    </p>
                </div>
                <div class="flex items-center gap-1.5 self-start sm:self-auto">
                    @if($isSignedByAsesor)
                        <span class="inline-flex items-center px-2.5 py-1 rounded-lg bg-indigo-50 border border-indigo-200 text-indigo-700 text-xs font-semibold">
                            Disahkan Asesor
                        </span>
                    @endif
                </div>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-4 text-xs">
                <!-- Pilihan TUK (Read-Only) -->
                <div class="space-y-1.5">
                    <label class="font-bold text-slate-700 block flex items-center justify-between">
                        <span>Jenis Tempat Uji Kompetensi (TUK)</span>
                        <span class="text-[10px] text-slate-500 font-medium bg-slate-100 px-2 py-0.5 rounded-md">Ditetapkan</span>
                    </label>
                    <input type="hidden" name="tuk_type" value="{{ old('tuk_type', $pendaftaran->tuk_type ?? 'Sewaktu') }}">
                    <select disabled 
                            class="w-full bg-slate-50 border border-slate-200 rounded-xl px-3 py-2 text-slate-800 font-semibold cursor-not-allowed opacity-90">
                        <option value="Sewaktu" {{ old('tuk_type', $pendaftaran->tuk_type) === 'Sewaktu' ? 'selected' : '' }}>TUK Sewaktu (SMKN 1 Gunungputri / Sekolah Mitra)</option>
                        <option value="Tempat Kerja" {{ old('tuk_type', $pendaftaran->tuk_type) === 'Tempat Kerja' ? 'selected' : '' }}>TUK Tempat Kerja / Fasilitas Industri (DUDI)</option>
                        <option value="Mandiri" {{ old('tuk_type', $pendaftaran->tuk_type) === 'Mandiri' ? 'selected' : '' }}>TUK Mandiri</option>
                    </select>
                </div>

                <!-- Metode Pengumpulan Bukti (Read-Only) -->
                <div class="space-y-1.5">
                    <label class="font-bold text-slate-700 block flex items-center justify-between">
                        <span>Metode Pengumpulan Bukti yang Disepakati</span>
                        <span class="text-[10px] text-slate-500 font-medium bg-slate-100 px-2 py-0.5 rounded-md">Ditetapkan</span>
                    </label>
                    @foreach($savedBukti as $b)
                        <input type="hidden" name="bukti_dikumpulkan[]" value="{{ $b }}">
                    @endforeach

                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-2 pt-0.5">
                        <label class="flex items-center gap-2 p-2 rounded-xl border border-slate-200 text-slate-700 bg-slate-50/70 cursor-not-allowed select-none">
                            <input type="checkbox" disabled
                                   {{ in_array('Observasi Praktik Demonstrasi', $savedBukti) || in_array('Uji Praktik / Observasi Demonstrasi', $savedBukti) ? 'checked' : '' }}
                                   class="rounded text-blue-600 cursor-not-allowed">
                            <span class="text-[11px] font-medium">Observasi Praktik</span>
                        </label>
                        <label class="flex items-center gap-2 p-2 rounded-xl border border-slate-200 text-slate-700 bg-slate-50/70 cursor-not-allowed select-none">
                            <input type="checkbox" disabled
                                   {{ in_array('Uji Tertulis (CBT)', $savedBukti) ? 'checked' : '' }}
                                   class="rounded text-blue-600 cursor-not-allowed">
                            <span class="text-[11px] font-medium">Uji Tertulis CBT</span>
                        </label>
                        <label class="flex items-center gap-2 p-2 rounded-xl border border-slate-200 text-slate-700 bg-slate-50/70 cursor-not-allowed select-none">
                            <input type="checkbox" disabled
                                   {{ in_array('Tanya Jawab Lisan', $savedBukti) ? 'checked' : '' }}
                                   class="rounded text-blue-600 cursor-not-allowed">
                            <span class="text-[11px] font-medium">Tanya Jawab Lisan</span>
                        </label>
                        <label class="flex items-center gap-2 p-2 rounded-xl border border-slate-200 text-slate-700 bg-slate-50/70 cursor-not-allowed select-none">
                            <input type="checkbox" disabled
                                   {{ in_array('Verifikasi Portofolio', $savedBukti) || in_array('Hasil Verifikasi Portofolio', $savedBukti) ? 'checked' : '' }}
                                   class="rounded text-blue-600 cursor-not-allowed">
                            <span class="text-[11px] font-medium">Verifikasi Portofolio</span>
                        </label>
                    </div>

                    <!-- Bukti lainnya (Read-Only) -->
                    <div class="mt-2 space-y-1">
                        <label class="text-[11px] font-bold text-slate-600 block">Catatan / Bukti Lainnya:</label>
                        <input type="hidden" name="bukti_dikumpulkan_lainnya" value="{{ old('bukti_dikumpulkan_lainnya', $pendaftaran->bukti_dikumpulkan_lainnya) }}">
                        <div class="text-[11px] text-slate-700 bg-slate-50 p-2.5 rounded-xl border border-slate-200 font-medium">
                            {{ $pendaftaran->bukti_dikumpulkan_lainnya ?: 'Tidak ada catatan bukti lainnya.' }}
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- 3. KLAUSUL PERSETUJUAN & KERAHASIAAN RESMI -->
        <div class="bg-slate-50 rounded-2xl border border-slate-200/90 p-4 sm:p-5 space-y-3">
            <h2 class="font-bold text-xs sm:text-sm text-slate-800">
                3. Klausul Persetujuan Asesmen & Komitmen Kerahasiaan
            </h2>

            <div class="text-xs text-slate-600 space-y-2 bg-white rounded-xl p-3.5 border border-slate-200/80 leading-relaxed font-sans">
                <p>
                    <strong>Pernyataan Asesi:</strong> &ldquo;Saya menyatakan bahwa saya telah mendapatkan penjelasan yang memadai mengenai proses asesmen, jadwal, metode, serta hak banding dalam proses sertifikasi ini. Saya menjamin bahwa seluruh data, dokumen portofolio, dan bukti unjuk kerja yang saya serahkan adalah sah dan benar milik saya pribadi.&rdquo;
                </p>
                <p>
                    <strong>Komitmen Kerahasiaan (Non-Disclosure):</strong> &ldquo;Saya bersedia menjaga kerahasiaan seluruh materi asesmen, instrumen uji, dan perangkat evaluasi yang digunakan selama proses asesmen berlangsung, serta tidak akan menggandakan, menyebarluaskan, maupun mendokumentasikan materi uji tanpa izin resmi tertulis dari LSP SMKN 1 Gunungputri.&rdquo;
                </p>
            </div>

            @if($isSignedByAsesi)
                <div class="text-xs text-emerald-700 font-semibold pt-1 bg-emerald-50/80 px-3 py-2 rounded-xl border border-emerald-200/60">
                    Anda telah menyetujui seluruh klausul persetujuan dan komitmen kerahasiaan asesmen di atas.
                </div>
            @else
                <label class="flex items-start gap-2.5 text-xs text-slate-800 font-semibold cursor-pointer pt-1 select-none">
                    <input type="checkbox" x-model="agreedToClause" class="mt-0.5 rounded-sm text-blue-600 focus:ring-0">
                    <span>Saya telah membaca, memahami, dan menyetujui seluruh klausul persetujuan serta komitmen kerahasiaan asesmen di atas.</span>
                </label>
            @endif
        </div>

        <!-- 4. SECTION TANDA TANGAN DIGITAL (2 PIHAK) -->
        <div class="bg-white rounded-2xl border border-slate-200/90 shadow-2xs p-4 sm:p-5 space-y-4">
            <h2 class="font-bold text-xs sm:text-sm text-slate-800 border-b border-slate-100 pb-2">
                4. Pengesahan & Tanda Tangan Digital Kedua Pihak
            </h2>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-5">
                
                <!-- KOLOM 1: ASESI -->
                <div class="border border-slate-200 rounded-xl p-3.5 space-y-2.5 bg-slate-50/50">
                    <div class="flex items-center justify-between">
                        <div>
                            <span class="text-[11px] font-bold text-slate-700 uppercase tracking-wider block">Tanda Tangan Asesi</span>
                            <span class="text-xs font-semibold text-slate-900">{{ $pendaftaran->asesi->nama_lengkap ?? $pengguna->nama_lengkap }}</span>
                        </div>
                        <div class="flex items-center gap-1.5">
                            @if($isSignedByAsesi)
                                <span class="px-2 py-0.5 rounded-md bg-emerald-100 text-emerald-800 font-bold text-[10px]">
                                    ✓ Telah Ditandatangani
                                </span>
                            @elseif($hasExistingTtd)
                                <span class="px-2 py-0.5 rounded-md bg-blue-100 text-blue-800 font-bold text-[10px]">
                                    Otomatis Terisi
                                </span>
                            @endif
                        </div>
                    </div>

                    <!-- Hidden Input TTD Asesi -->
                    <input type="hidden" name="tanda_tangan_asesi_ak01" id="inputSignatureAk01" x-ref="signatureInput" value="{{ $existingTtd }}">

                    <!-- MODE PREVIEW TTD (Jika ada TTD dan tidak sedang dalam mode edit) -->
                    <div x-show="!isEditingSignature" class="space-y-2">
                        <div class="h-32 bg-white rounded-lg border border-slate-200 flex items-center justify-center p-2 relative">
                            @if($hasExistingTtd)
                                <img src="{{ $existingTtdUrl }}" alt="Tanda Tangan Asesi" class="max-h-28 object-contain">
                            @else
                                <span class="text-xs text-slate-400 italic">Belum ada tanda tangan digital tersimpan</span>
                            @endif
                        </div>
                        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-1.5 text-[11px]">
                            <div class="text-slate-500">
                                @if($isSignedByAsesi)
                                    Waktu TTD: <strong>{{ $pendaftaran->tanggal_ttd_asesi_ak01 ? date('d/m/Y H:i', strtotime($pendaftaran->tanggal_ttd_asesi_ak01)) : 'Tercatat' }}</strong>
                                @elseif($hasExistingTtd)
                                    <span class="text-emerald-700 font-medium">
                                        Otomatis dari profil/pendaftaran Anda.
                                    </span>
                                @else
                                    <span class="text-slate-400">Silakan bubuhkan tanda tangan digital Anda.</span>
                                @endif
                            </div>

                            <!-- Tombol Ubah / Edit TTD -->
                            @if($pendaftaran->status_ak01 !== 'selesai' || !$isSignedByAsesi)
                                <button type="button" 
                                        @click="enableEditSignature()" 
                                        class="px-2.5 py-1 rounded-lg bg-white hover:bg-blue-50 text-blue-700 font-bold text-xs border border-blue-200 transition-colors cursor-pointer self-end sm:self-auto shadow-2xs">
                                    {{ $hasExistingTtd ? 'Ubah Tanda Tangan' : 'Gambar Tanda Tangan' }}
                                </button>
                            @endif
                        </div>
                    </div>

                    <!-- MODE EDIT / CANVAS TTD (Jika sedang edit atau belum ada TTD tersimpan) -->
                    <div x-show="isEditingSignature" class="space-y-1.5" style="display: none;">
                        <div class="border border-blue-300 ring-2 ring-blue-100 rounded-xl bg-white relative overflow-hidden">
                            <canvas id="canvasAk01Asesi" width="800" height="240" class="w-full h-32 bg-white cursor-crosshair block touch-none" style="touch-action: none; -ms-touch-action: none;"></canvas>
                        </div>
                        <div class="flex items-center justify-between text-[11px]">
                            <span class="text-slate-500">Gunakan mouse atau layar sentuh untuk tanda tangan.</span>
                            <div class="flex items-center gap-1.5">
                                <button type="button" @click="clearSignature()" class="text-rose-600 hover:text-rose-800 font-semibold cursor-pointer">
                                    [ Hapus ]
                                </button>
                                <template x-if="hasSavedSignature">
                                    <button type="button" @click="cancelEditSignature()" class="text-slate-600 hover:text-slate-800 font-semibold cursor-pointer ml-1">
                                        [ Batal Ubah ]
                                    </button>
                                </template>
                            </div>
                        </div>
                        <template x-if="hasSavedSignature">
                            <div class="text-[10px] text-blue-700 bg-blue-50 border border-blue-100 rounded-lg p-1.5 text-center">
                                Mode ubah tanda tangan aktif. Gambar tanda tangan baru atau klik <strong>Batal Ubah</strong> untuk memakai tanda tangan tersimpan.
                            </div>
                        </template>
                    </div>
                </div>

                <!-- KOLOM 2: ASESOR -->
                <div class="border border-slate-200 rounded-xl p-3.5 space-y-2.5 bg-slate-50/50">
                    <div class="flex items-center justify-between">
                        <div>
                            <span class="text-[11px] font-bold text-slate-700 uppercase tracking-wider block">Tanda Tangan Asesor</span>
                            <span class="text-xs font-semibold text-slate-900">{{ $pendaftaran->asesor->nama_lengkap ?? 'Asesor Penguji' }}</span>
                        </div>
                        @if($isSignedByAsesor)
                            <span class="px-2 py-0.5 rounded-md bg-emerald-100 text-emerald-800 font-bold text-[10px]">
                                ✓ Telah Ditetapkan & Disahkan
                            </span>
                        @else
                            <span class="px-2 py-0.5 rounded-md bg-slate-200 text-slate-600 font-medium text-[10px]">
                                Belum Mengisi & Menandatangani
                            </span>
                        @endif
                    </div>

                    <div class="h-32 bg-white rounded-lg border border-slate-200 flex flex-col items-center justify-center p-3 text-center">
                        @if($isSignedByAsesor && $pendaftaran->tanda_tangan_asesor_ak01)
                            @php
                                $asesorTtdUrl = \Illuminate\Support\Str::startsWith($pendaftaran->tanda_tangan_asesor_ak01, ['data:', 'http://', 'https://'])
                                    ? $pendaftaran->tanda_tangan_asesor_ak01
                                    : asset($pendaftaran->tanda_tangan_asesor_ak01);
                            @endphp
                            <img src="{{ $asesorTtdUrl }}" alt="Tanda Tangan Asesor" class="max-h-24 object-contain">
                            <span class="text-[10px] text-emerald-700 font-semibold mt-1">Disahkan pada {{ $pendaftaran->tanggal_ttd_asesor_ak01 ? date('d/m/Y H:i', strtotime($pendaftaran->tanggal_ttd_asesor_ak01)) : '-' }}</span>
                        @elseif($isSignedByAsesor)
                            <span class="text-xs font-bold text-emerald-800">Telah Disahkan secara Otomatis</span>
                            <span class="text-[10px] text-slate-400">{{ $pendaftaran->tanggal_ttd_asesor_ak01 ? date('d/m/Y H:i', strtotime($pendaftaran->tanggal_ttd_asesor_ak01)) : '' }}</span>
                        @else
                            <span class="text-xs text-slate-400 italic">Asesor Penguji belum mengisi dan menandatangani formulir ini.</span>
                        @endif
                    </div>
                    <div class="text-[11px] text-slate-500">
                        Status Asesor: <strong>{{ $isSignedByAsesor ? 'Telah Ditetapkan & Disahkan' : 'Menunggu Pengisian Asesor' }}</strong>
                    </div>
                </div>

            </div>
        </div>

        <!-- ACTION BAR / SUBMIT -->
        <div class="bg-white rounded-2xl border border-slate-200/90 p-4 shadow-2xs flex flex-col sm:flex-row items-center justify-between gap-3">
            <a href="{{ route('asesi.tahapan', ['step' => 2, 'pendaftaran_id' => $pendaftaran->id]) }}" 
               class="w-full sm:w-auto px-4 py-2.5 rounded-xl border border-slate-200 hover:bg-slate-50 text-slate-700 text-xs font-semibold transition-colors text-center">
                Kembali ke FR.APL.02
            </a>

            @if(!$isSignedByAsesi)
                <button type="submit" 
                        :disabled="isSubmitting"
                        :class="isSubmitting ? 'opacity-70 cursor-wait bg-blue-500' : 'bg-blue-600 hover:bg-blue-700 cursor-pointer'"
                        class="w-full sm:w-auto px-6 py-2.5 text-white text-xs font-bold rounded-xl shadow-xs transition-all flex items-center justify-center">
                    <span x-show="!isSubmitting" x-text="(hasSavedSignature && !isEditingSignature) ? 'Kirim & Setujui FR.AK.01' : 'Simpan & Kirim FR.AK.01'"></span>
                    <span x-show="isSubmitting" style="display: none;">Mengirim Formulir...</span>
                </button>
            @else
                <!-- Jika sudah pernah menandatangani, tapi sedang aktif mode ubah TTD -->
                <div x-show="isEditingSignature" class="w-full sm:w-auto flex items-center gap-2">
                    <button type="button" 
                            @click="cancelEditSignature()" 
                            class="px-4 py-2.5 rounded-xl border border-slate-200 hover:bg-slate-50 text-slate-700 text-xs font-semibold transition-colors cursor-pointer">
                        Batal
                    </button>
                    <button type="submit" 
                            :disabled="isSubmitting"
                            :class="isSubmitting ? 'opacity-70 cursor-wait bg-blue-500' : 'bg-blue-600 hover:bg-blue-700 cursor-pointer'"
                            class="px-6 py-2.5 text-white text-xs font-bold rounded-xl shadow-xs transition-all flex items-center justify-center">
                        <span x-show="!isSubmitting">Simpan & Kirim Ulang FR.AK.01</span>
                        <span x-show="isSubmitting" style="display: none;">Menyimpan Perubahan...</span>
                    </button>
                </div>

                <!-- Tampilan status biasa jika tidak sedang mode edit -->
                <div x-show="!isEditingSignature" class="w-full sm:w-auto flex flex-col sm:flex-row items-center gap-2.5">
                    @if(!$isSignedByAsesor)
                        <div class="text-xs text-amber-700 font-semibold bg-amber-50 border border-amber-200/80 px-3 py-2 rounded-xl">
                            FR.AK.01 Menunggu Pengesahan & Tanda Tangan Asesor Penguji
                        </div>
                        <a href="{{ route('asesi.tahapan', ['pendaftaran_id' => $pendaftaran->id]) }}" class="px-4 py-2 rounded-xl bg-slate-100 hover:bg-slate-200 text-slate-700 font-bold text-xs transition-colors">
                            Lihat Tahapan Asesmen
                        </a>
                    @else
                        <span class="text-xs text-emerald-700 font-semibold">
                            FR.AK.01 Telah Ditandatangani
                        </span>
                        <a href="{{ route('asesi.dashboard', ['pendaftaran_id' => $pendaftaran->id]) }}" class="px-5 py-2.5 rounded-xl bg-blue-600 hover:bg-blue-700 text-white font-bold text-xs shadow-xs transition-colors">
                            Kembali ke Dashboard &rarr;
                        </a>
                    @endif
                </div>
            @endif
        </div>
    </form>
</div>

<!-- Alpine.js & Signature Engine Logic -->
<script>
    function ak01App() {
        const initialTtd = @json($existingTtd);
        const hasInitialTtd = Boolean(initialTtd);
        const isSigned = {{ $isSignedByAsesi ? 'true' : 'false' }};

        return {
            agreedToClause: true,
            isSubmitting: false,
            hasSavedSignature: hasInitialTtd,
            savedSignature: initialTtd,
            isEditingSignature: !hasInitialTtd, // Jika belum ada TTD tersimpan, langsung buka mode gambar
            engine: null,

            init() {
                if (this.isEditingSignature) {
                    this.$nextTick(() => {
                        this.initSignatureEngine();
                    });
                }
            },

            enableEditSignature() {
                this.isEditingSignature = true;
                this.$nextTick(() => {
                    if (!this.engine) {
                        this.initSignatureEngine();
                    } else {
                        this.engine.clear();
                        this.engine.resize();
                    }
                    const sigInput = document.getElementById('inputSignatureAk01') || (this.$refs && this.$refs.signatureInput);
                    if (sigInput) {
                        sigInput.value = '';
                    }
                });
            },

            cancelEditSignature() {
                this.isEditingSignature = false;
                if (this.engine) {
                    this.engine.clear();
                }
                const sigInput = document.getElementById('inputSignatureAk01') || (this.$refs && this.$refs.signatureInput);
                if (sigInput) {
                    sigInput.value = this.savedSignature || '';
                }
            },

            clearSignature() {
                if (this.engine) {
                    this.engine.clear();
                }
                const sigInput = document.getElementById('inputSignatureAk01') || (this.$refs && this.$refs.signatureInput);
                if (sigInput) {
                    sigInput.value = '';
                }
            },

            initSignatureEngine() {
                const canvas = document.getElementById('canvasAk01Asesi');
                const input = document.getElementById('inputSignatureAk01');
                if (!canvas) return;

                this.engine = initUnifiedSignatureEngine(canvas, input);
            },

            submitAk01(e) {
                if (this.isSubmitting) return;

                if (!this.agreedToClause) {
                    Swal.fire({
                        icon: 'warning',
                        title: 'Persetujuan Klausul',
                        text: 'Silakan centang persetujuan klausul sebelum mengirim formulir FR.AK.01.',
                        confirmButtonColor: '#2563eb'
                    });
                    return;
                }

                const sigInput = document.getElementById('inputSignatureAk01') || (this.$refs && this.$refs.signatureInput);

                // Cek tanda tangan
                if (this.isEditingSignature) {
                    if (this.engine && this.engine.isEmpty()) {
                        Swal.fire({
                            icon: 'warning',
                            title: 'Tanda Tangan Kosong',
                            text: this.hasSavedSignature 
                                ? 'Silakan bubuhkan tanda tangan baru pada canvas, atau klik Batal Ubah untuk memakai tanda tangan tersimpan.'
                                : 'Silakan bubuhkan tanda tangan digital Anda pada canvas terlebih dahulu.',
                            confirmButtonColor: '#2563eb'
                        });
                        return;
                    }
                    if (this.engine && !this.engine.isEmpty()) {
                        const dataUrl = this.engine.toDataURL();
                        if (sigInput) sigInput.value = dataUrl;
                        if (this.$refs && this.$refs.signatureInput) {
                            this.$refs.signatureInput.value = dataUrl;
                        }
                    }
                } else {
                    // Mode tanda tangan otomatis tersimpan
                    const val = (sigInput && sigInput.value) 
                        ? sigInput.value 
                        : ((this.$refs && this.$refs.signatureInput && this.$refs.signatureInput.value) 
                            ? this.$refs.signatureInput.value 
                            : this.savedSignature);
                    if (!val) {
                        Swal.fire({
                            icon: 'warning',
                            title: 'Tanda Tangan Kosong',
                            text: 'Silakan bubuhkan tanda tangan digital Anda terlebih dahulu.',
                            confirmButtonColor: '#2563eb'
                        });
                        return;
                    }
                    if (sigInput) sigInput.value = val;
                    if (this.$refs && this.$refs.signatureInput) {
                        this.$refs.signatureInput.value = val;
                    }
                }

                this.isSubmitting = true;
                const form = document.getElementById('formAk01') || (e && e.target ? (e.target.closest ? e.target.closest('form') : e.target) : null);
                if (form) {
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
                                    text: 'Persetujuan Asesmen (FR.AK.01) telah berhasil ditandatangani. Silakan menunggu sesi asesmen Anda dimulai sesuai jadwal.',
                                    confirmButtonColor: '#2563eb',
                                    confirmButtonText: 'Oke'
                                }).then(() => {
                                    window.location.href = redirectUrl;
                                });
                            } else {
                                alert('Persetujuan Asesmen (FR.AK.01) telah berhasil ditandatangani. Silakan menunggu sesi asesmen Anda dimulai sesuai jadwal.');
                                window.location.href = redirectUrl;
                            }
                        } else {
                            this.isSubmitting = false;
                            alert(data.message || 'Terjadi kesalahan saat menyimpan FR.AK.01.');
                        }
                    })
                    .catch(err => {
                        if (typeof HTMLFormElement !== 'undefined' && HTMLFormElement.prototype && HTMLFormElement.prototype.submit) {
                            HTMLFormElement.prototype.submit.call(form);
                        } else {
                            form.submit();
                        }
                    });
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
</script>
@endsection
