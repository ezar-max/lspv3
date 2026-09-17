@extends('tata-letak.dasbor')

@section('judul', 'Manajemen Master Skema & Unit Kompetensi')

@push('css')
    <link rel="stylesheet" href="{{ asset('css/admin/dashboard-admin.css') }}">
    <style>
        .kartu-skema-accordion {
            background: #ffffff;
            border-radius: 12px;
            border: 1px solid #e2e8f0;
            margin-bottom: 1rem;
            overflow: hidden;
            box-shadow: 0 1px 3px rgba(0,0,0,0.03);
            transition: all 0.2s ease;
        }
        .kartu-skema-accordion:hover {
            border-color: #cbd5e1;
            box-shadow: 0 4px 12px rgba(0,0,0,0.05);
        }
        .header-skema-accordion {
            padding: 1.25rem 1.5rem;
            display: flex;
            justify-content: space-between;
            align-items: center;
            cursor: pointer;
            user-select: none;
            background: #ffffff;
            gap: 1.5rem;
            transition: background 0.15s ease;
        }
        .header-skema-accordion:hover {
            background: #f8fafc;
        }
        .konten-skema-accordion {
            display: grid;
            grid-template-rows: 0fr;
            opacity: 0;
            background: #f8fafc;
            border-top: 1px solid transparent;
            transition: grid-template-rows 0.25s ease-out, opacity 0.2s ease-out, border-color 0.2s ease;
        }
        .konten-skema-accordion.terbuka {
            grid-template-rows: 1fr;
            opacity: 1;
            border-top-color: #e2e8f0;
        }
        .konten-skema-inner {
            overflow: hidden;
            padding: 1.5rem;
        }
        .btn-toggle-unit {
            background: #f1f5f9;
            color: #1e293b;
            border: 1px solid #e2e8f0;
            padding: 0.45rem 0.85rem;
            border-radius: 6px;
            font-size: 0.82rem;
            font-weight: 600;
            display: inline-flex;
            align-items: center;
            gap: 0.35rem;
            cursor: pointer;
            transition: all 0.15s ease;
        }
        .btn-toggle-unit:hover {
            background: #e2e8f0;
            color: #0f172a;
        }
        .btn-action-edit {
            background: #ffffff;
            color: #334155;
            border: 1px solid #cbd5e1;
            padding: 0.45rem 0.85rem;
            border-radius: 6px;
            font-size: 0.82rem;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.15s ease;
        }
        .btn-action-edit:hover {
            background: #f1f5f9;
            color: #0f172a;
            border-color: #94a3b8;
        }
        .btn-action-delete {
            background: #ffffff;
            color: #e11d48;
            border: 1px solid #fecdd3;
            padding: 0.45rem 0.85rem;
            border-radius: 6px;
            font-size: 0.82rem;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.15s ease;
        }
        .btn-action-delete:hover {
            background: #fff1f2;
            border-color: #fda4af;
        }

        /* ── MODERN POPUP / MODAL ENGINE ── */
        .modal-overlay {
            backdrop-filter: blur(8px) !important;
            -webkit-backdrop-filter: blur(8px) !important;
            background: rgba(15, 23, 42, 0.65) !important;
            transition: opacity 0.25s cubic-bezier(0.16, 1, 0.3, 1), visibility 0.25s !important;
            overscroll-behavior: contain !important;
        }
        .modal-konten-modern {
            background: #ffffff;
            border-radius: 20px;
            box-shadow: 0 25px 50px -12px rgba(15, 23, 42, 0.25), 0 0 0 1px rgba(226, 232, 240, 0.9);
            border: none;
            overflow: hidden;
            display: flex;
            flex-direction: column;
            max-height: 88vh;
            height: auto;
            transform: scale(0.96) translateY(8px);
            transition: transform 0.25s cubic-bezier(0.16, 1, 0.3, 1);
            overscroll-behavior: contain;
        }
        .modal-konten-modern form,
        .modal-konten-modern > form {
            display: flex;
            flex-direction: column;
            flex: 1 1 auto;
            min-height: 0;
            overflow: hidden;
        }
        .modal-overlay.terbuka .modal-konten-modern {
            transform: scale(1) translateY(0);
        }
        .modal-scroll-body {
            flex: 1 1 auto;
            min-height: 0;
            overflow-y: auto;
            overscroll-behavior: contain;
            scrollbar-width: thin;
            scrollbar-color: #cbd5e1 transparent;
        }
        .modal-scroll-body::-webkit-scrollbar {
            width: 6px;
        }
        .modal-scroll-body::-webkit-scrollbar-track {
            background: transparent;
        }
        .modal-scroll-body::-webkit-scrollbar-thumb {
            background-color: #cbd5e1;
            border-radius: 9999px;
        }
        .modal-scroll-body::-webkit-scrollbar-thumb:hover {
            background-color: #94a3b8;
        }
        .baris-unit-input {
            display: grid;
            grid-template-columns: 1.5fr 2.5fr 1.2fr 40px;
            gap: 0.75rem;
            align-items: center;
            margin-bottom: 0.75rem;
        }
        .form-inline-unit {
            display: grid;
            grid-template-columns: 1.5fr 2.5fr 1.2fr 130px;
            gap: 0.75rem;
            align-items: center;
        }
        @media (max-width: 860px) {
            .header-skema-accordion {
                flex-direction: column;
                align-items: flex-start;
                gap: 1rem;
            }
            .baris-unit-input, .form-inline-unit {
                grid-template-columns: 1fr;
            }
        }
    </style>
@endpush

@section('konten')
<div style="max-width: 1100px;" class="animasi-slide">
    
    <!-- HEADER -->
    <div style="display: flex; justify-content: space-between; align-items: flex-start; margin-bottom: 1.75rem; flex-wrap: wrap; gap: 1rem;">
        <div>
            <h1 style="font-size: 1.75rem; color: var(--biru-malam); margin-bottom: 0.35rem; font-weight: 800;">
                Manajemen Master Skema & Unit Kompetensi
            </h1>
            <p style="color: var(--abu-teks); margin: 0; font-size: 0.92rem;">
                Kelola daftar skema sertifikasi keahlian vokasi dan unit kompetensi standar BNSP
            </p>
        </div>
        <button class="tombol tombol-utama" onclick="bukaModal('modalTambahSkema')" style="padding: 0.65rem 1.25rem; font-weight: 700;">
            + Tambah Master Skema Baru
        </button>
    </div>

    @if(session('sukses'))
        <div class="pesan-sukses" style="margin-bottom: 1.5rem; background: #f0fdf4; border: 1px solid #86efac; color: #166534; padding: 1rem 1.25rem; border-radius: 10px;">
            {{ session('sukses') }}
        </div>
    @endif

    <!-- DAFTAR SKEMA BERBENTUK KARTU AKORDEON BERSIH -->
    <div class="daftar-skema-container">
        @forelse($skemaList as $s)
            <div class="kartu-skema-accordion" id="accordion-skema-{{ $s->id }}">
                <div class="header-skema-accordion" 
                     role="button" 
                     tabindex="0" 
                     aria-expanded="false" 
                     onclick="toggleAccordion('{{ $s->id }}')"
                     onkeydown="if(event.key==='Enter'||event.key===' '){toggleAccordion('{{ $s->id }}'); event.preventDefault();}">
                    
                    <!-- INFORMASI SKEMA (KIRI) -->
                    <div style="flex: 1; min-width: 0;">
                        <div style="display: flex; align-items: center; gap: 0.65rem; margin-bottom: 0.35rem; flex-wrap: wrap;">
                            <span style="color: #0f172a; font-size: 1.05rem; font-weight: 700; line-height: 1.4;">
                                {{ $s->nama_skema }}
                            </span>
                            <span class="lencana lencana-biru" style="font-size: 0.75rem; padding: 0.2rem 0.6rem;">
                                {{ $s->kategori }}
                            </span>
                            @if($s->status_aktif)
                                <span class="lencana lencana-hijau" style="font-size: 0.75rem; padding: 0.2rem 0.6rem;">Aktif</span>
                            @else
                                <span class="lencana lencana-merah" style="font-size: 0.75rem; padding: 0.2rem 0.6rem;">Non-Aktif</span>
                            @endif
                        </div>
                        <div style="font-size: 0.84rem; color: #64748b;">
                            Kode Skema: <strong style="color: #2563eb; font-family: monospace;">{{ $s->kode_skema }}</strong> &bull; 
                            {{ $s->unit_kompetensi_count }} Unit Kompetensi
                        </div>
                    </div>

                    <!-- AKSI & TOGGLE DETAIL (KANAN) -->
                    <div style="display: flex; align-items: center; gap: 0.65rem; flex-shrink: 0;" onclick="event.stopPropagation();">
                        <button type="button" class="btn-toggle-unit" onclick="toggleAccordion('{{ $s->id }}')" id="btn-toggle-{{ $s->id }}">
                            <span id="text-toggle-{{ $s->id }}">Detail Unit</span>
                            <span id="arrow-toggle-{{ $s->id }}" style="font-size: 0.75rem; transition: transform 0.2s ease;">▼</span>
                        </button>
                        
                        <button type="button" class="btn-action-edit" onclick="bukaModal('modalUbah_{{ $s->id }}')">
                            Edit
                        </button>
                        
                        <form action="{{ route('admin.skema.hapus', $s->id) }}" method="POST" onsubmit="return confirm('Hapus skema sertifikasi {{ $s->nama_skema }}?')" style="display: inline;">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="btn-action-delete">
                                Hapus
                            </button>
                        </form>
                    </div>
                </div>

                <!-- KONTEN AKORDEON DETAIL UNIT KOMPETENSI -->
                <div class="konten-skema-accordion" id="konten-skema-{{ $s->id }}">
                    <div class="konten-skema-inner">
                        @if($s->deskripsi)
                            <div style="margin-bottom: 1.25rem; background: #ffffff; padding: 1rem 1.25rem; border-radius: 8px; border: 1px solid #e2e8f0; font-size: 0.88rem; color: #334155;">
                                <strong style="color: #0f172a;">Deskripsi Skema:</strong> {{ $s->deskripsi }}
                            </div>
                        @endif

                        <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 0.85rem;">
                            <h2 style="color: #0f172a; font-size: 1rem; font-weight: 700; margin: 0;">
                                Daftar Unit Kompetensi ({{ $s->unitKompetensi->count() }} Unit)
                            </h2>
                        </div>

                        <div class="tabel-wadah" style="margin-bottom: 1.5rem;">
                            <table class="tabel-custom">
                                <thead>
                                    <tr>
                                        <th style="width: 45px; text-align: center;">No.</th>
                                        <th style="width: 180px;">Kode Unit</th>
                                        <th>Judul Unit Kompetensi</th>
                                        <th style="width: 130px;">Jenis Standar</th>
                                        <th style="width: 160px;">Elemen & KUK</th>
                                        <th style="width: 160px; text-align: center;">Aksi</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @forelse($s->unitKompetensi as $idx => $u)
                                        <tr>
                                            <td style="text-align: center; color: var(--abu-teks); font-weight: 600;">{{ $idx + 1 }}</td>
                                            <td><span class="font-mono">{{ $u->kode_unit }}</span></td>
                                            <td style="font-weight: 600; color: var(--biru-malam);">{{ $u->judul_unit }}</td>
                                            <td>
                                                @php
                                                    $std = strtoupper($u->standar_kompetensi ?? 'SKKNI');
                                                @endphp
                                                <span class="lencana lencana-biru">
                                                    {{ $u->standar_kompetensi ?? 'SKKNI' }}
                                                </span>
                                            </td>
                                            <td>
                                                <span class="lencana lencana-hijau">
                                                    {{ $u->elemenKompetensi->count() }} Elemen / {{ $u->elemenKompetensi->sum(fn($e) => $e->kriteriaUnjukKerja->count()) }} KUK
                                                </span>
                                            </td>
                                            <td style="text-align: center;">
                                                <div style="display: inline-flex; gap: 0.4rem; align-items: center;">
                                                    <button type="button" class="tombol tombol-utama tombol-sm" onclick="bukaModal('modalKelolaElemen_{{ $u->id }}')">
                                                        Elemen & KUK
                                                    </button>
                                                    <form action="{{ route('admin.unit.hapus', $u->id) }}" method="POST" onsubmit="return confirm('Hapus unit {{ $u->kode_unit }}?')" style="display: inline;">
                                                        @csrf
                                                        @method('DELETE')
                                                        <button type="submit" class="btn-action-delete" style="padding: 0.35rem 0.6rem; font-size: 0.78rem;">
                                                            Hapus
                                                        </button>
                                                    </form>
                                                </div>
                                            </td>
                                        </tr>
                                    @empty
                                        <tr>
                                            <td colspan="6" style="text-align: center; color: var(--abu-teks); padding: 2.5rem 1rem;">
                                                Belum ada unit kompetensi yang didaftarkan pada skema ini.
                                            </td>
                                        </tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>

                        <!-- FORM INLINE TAMBAH UNIT BARU KE SKEMA INI -->
                        <div style="background: #ffffff; padding: 1.25rem; border-radius: 8px; border: 1px solid #e2e8f0;">
                            <div style="font-weight: 700; color: #0f172a; margin-bottom: 0.75rem; font-size: 0.88rem;">
                                Tambah Unit Kompetensi ke Skema Ini:
                            </div>
                            <form action="{{ route('admin.unit.simpan', $s->id) }}" method="POST" class="form-inline-unit">
                                @csrf
                                <input type="text" name="kode_unit" class="input-control" placeholder="Kode Unit (misal: J.620100.001.01)" required>
                                <input type="text" name="judul_unit" class="input-control" placeholder="Judul Unit Kompetensi" required>
                                <select name="standar_kompetensi" class="input-control" required>
                                    <option value="KKNI">KKNI</option>
                                    <option value="Okupasi" selected>Okupasi</option>
                                    <option value="Klaster">Klaster</option>
                                </select>
                                <button type="submit" class="tombol tombol-utama tombol-sm" style="font-weight: 700; height: 38px;">
                                    + Tambah Unit
                                </button>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        @empty
            <div style="background: #ffffff; border-radius: 12px; border: 1px solid #e2e8f0; padding: 3.5rem 2rem; text-align: center;">
                <h3 style="font-size: 1.25rem; font-weight: 700; color: #1e293b; margin-bottom: 0.5rem;">
                    Belum Ada Skema Sertifikasi
                </h3>
                <p style="color: #64748b; max-width: 480px; margin: 0 auto 1.5rem auto; font-size: 0.92rem;">
                    Klik tombol di bawah untuk menambahkan master skema sertifikasi dan unit kompetensi pertama.
                </p>
                <button type="button" class="tombol tombol-utama" onclick="bukaModal('modalTambahSkema')" style="font-weight: 700;">
                    + Tambah Master Skema Pertama
                </button>
            </div>
        @endforelse
    </div>

    <div style="margin-top: 1.5rem;">
        {{ $skemaList->links() }}
    </div>
</div>

<!-- ========================================== -->
<!-- GLOBAL MODALS (CLEAN & MODERN)            -->
<!-- ========================================== -->

<!-- 1. MODAL KELOLA ELEMEN & KUK UNTUK MASING-MASING UNIT -->
@foreach($skemaList as $s)
    @foreach($s->unitKompetensi as $u)
        <div class="modal-overlay" id="modalKelolaElemen_{{ $u->id }}">
            <div class="modal-konten-modern w-[95vw] max-w-4xl">
                <!-- 1. MODAL HEADER (Sticky & Clean) -->
                <div class="px-6 py-4 sm:py-5 bg-white border-b border-slate-200/80 flex items-start justify-between gap-4 shrink-0">
                    <div class="flex items-start gap-3.5">
                        <div class="w-11 h-11 rounded-2xl bg-gradient-to-br from-indigo-50 to-blue-50 border border-indigo-100/90 text-indigo-600 flex items-center justify-center shrink-0 shadow-2xs">
                            <i class="fa-solid fa-layer-group text-lg"></i>
                        </div>
                        <div>
                            <div class="flex items-center gap-2 flex-wrap mb-1">
                                <span class="px-2.5 py-0.5 rounded-md font-mono text-xs font-extrabold bg-indigo-50 text-indigo-700 border border-indigo-200/80">
                                    {{ $u->kode_unit }}
                                </span>
                                <span class="px-2 py-0.5 rounded-md text-[11px] font-bold bg-slate-100 text-slate-600 border border-slate-200">
                                    Standar {{ $u->standar_kompetensi ?? 'SKKNI' }}
                                </span>
                            </div>
                            <h3 class="text-base sm:text-lg font-extrabold text-slate-900 tracking-tight leading-snug">
                                {{ $u->judul_unit }}
                            </h3>
                            <p class="text-xs text-slate-500 mt-0.5">
                                Kelola daftar elemen kompetensi, benchmark pertanyaan asesi, dan butir KUK unit ini.
                            </p>
                        </div>
                    </div>

                    <div class="flex items-center gap-2 shrink-0">
                        <button type="button" 
                                onclick="tutupModal('modalKelolaElemen_{{ $u->id }}')" 
                                aria-label="Tutup modal" 
                                class="w-9 h-9 rounded-xl bg-slate-100 hover:bg-slate-200 text-slate-500 hover:text-slate-800 transition-colors flex items-center justify-center cursor-pointer">
                            <i class="fa-solid fa-xmark text-sm"></i>
                        </button>
                    </div>
                </div>

                <!-- 2. MODAL BODY (Scrollable with modern scrollbar) -->
                <div class="modal-scroll-body p-5 sm:p-6 space-y-6 bg-slate-50/50">
                    
                    <!-- DAFTAR ELEMEN KOMPETENSI SAAT INI -->
                    <div>
                        <div class="flex items-center justify-between gap-2 mb-3">
                            <div class="flex items-center gap-2 flex-wrap">
                                <h4 class="text-xs font-bold uppercase tracking-wider text-slate-500">
                                    Daftar Elemen Kompetensi
                                </h4>
                                <span class="px-2 py-0.5 rounded-full text-[11px] font-bold bg-indigo-100 text-indigo-800 border border-indigo-200">
                                    {{ $u->elemenKompetensi->count() }} Elemen
                                </span>
                                <span class="px-2 py-0.5 rounded-full text-[11px] font-bold bg-emerald-100 text-emerald-800 border border-emerald-200">
                                    {{ $u->elemenKompetensi->sum(fn($e) => $e->kriteriaUnjukKerja->count()) }} KUK
                                </span>
                            </div>
                        </div>

                        <div class="space-y-4">
                            @forelse($u->elemenKompetensi as $e)
                                <div class="bg-white rounded-2xl border border-slate-200/90 shadow-2xs p-5 transition-all hover:border-indigo-200">
                                    <!-- Elemen Top Row: Title, Pertanyaan, & Tombol Hapus -->
                                    <div class="flex flex-col sm:flex-row sm:items-start justify-between gap-3 pb-3.5 border-b border-slate-100">
                                        <div class="space-y-1.5">
                                            <div class="flex items-center gap-2 flex-wrap">
                                                <span class="px-2.5 py-0.5 rounded-md font-mono text-xs font-extrabold bg-indigo-50 text-indigo-700 border border-indigo-100">
                                                    ELEMEN {{ $e->nomor_elemen }}
                                                </span>
                                                <h5 class="text-sm sm:text-base font-bold text-slate-900 leading-snug">
                                                    {{ $e->nama_elemen }}
                                                </h5>
                                            </div>
                                            @if($e->pertanyaan_elemen)
                                                <div class="flex items-start gap-2 text-xs text-slate-600 italic bg-slate-50 px-3.5 py-2 rounded-xl border border-slate-200/70">
                                                    <i class="fa-solid fa-quote-left text-indigo-400 text-xs mt-0.5 shrink-0"></i>
                                                    <span>"{{ $e->pertanyaan_elemen }}"</span>
                                                </div>
                                            @endif
                                        </div>

                                        <form action="{{ route('admin.elemen.hapus', $e->id) }}" method="POST" onsubmit="return confirm('Hapus Elemen {{ $e->nomor_elemen }} beserta seluruh KUK-nya?')">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" 
                                                    class="px-3 py-1.5 rounded-xl bg-rose-50 hover:bg-rose-100 text-rose-600 hover:text-rose-700 text-xs font-bold border border-rose-200/80 transition-colors cursor-pointer flex items-center gap-1.5 shrink-0" 
                                                    title="Hapus elemen ini beserta KUK">
                                                <i class="fa-regular fa-trash-can text-xs"></i>
                                                <span>Hapus Elemen</span>
                                            </button>
                                        </form>
                                    </div>

                                    <!-- DAFTAR KUK DALAM ELEMEN INI -->
                                    <div class="mt-4 pt-1">
                                        <div class="flex items-center justify-between text-xs font-bold uppercase tracking-wider text-slate-600 mb-2">
                                            <span>Kriteria Unjuk Kerja (KUK):</span>
                                            <span class="text-[11px] font-mono text-slate-400 font-semibold">{{ $e->kriteriaUnjukKerja->count() }} butir</span>
                                        </div>

                                        <div class="bg-slate-50/70 rounded-xl border border-slate-200/80 overflow-hidden divide-y divide-slate-200/60">
                                            @forelse($e->kriteriaUnjukKerja as $k)
                                                <div class="flex items-center justify-between gap-3 px-3.5 py-2.5 hover:bg-white transition-colors">
                                                    <div class="flex items-start gap-2.5 min-w-0">
                                                        <span class="font-mono text-xs font-bold text-indigo-700 bg-indigo-50 border border-indigo-100 px-2 py-0.5 rounded shrink-0">
                                                            {{ $k->nomor_kuk }}
                                                        </span>
                                                        <span class="text-xs text-slate-700 leading-relaxed break-words">
                                                            {{ $k->pernyataan_kuk }}
                                                        </span>
                                                    </div>
                                                    <form action="{{ route('admin.kuk.hapus', $k->id) }}" method="POST" onsubmit="return confirm('Hapus KUK {{ $k->nomor_kuk }}?')">
                                                        @csrf
                                                        @method('DELETE')
                                                        <button type="submit" 
                                                                class="w-7 h-7 rounded-lg hover:bg-rose-50 text-slate-400 hover:text-rose-600 transition-colors flex items-center justify-center cursor-pointer shrink-0" 
                                                                title="Hapus KUK ini">
                                                            <i class="fa-solid fa-xmark text-xs"></i>
                                                        </button>
                                                    </form>
                                                </div>
                                            @empty
                                                <div class="px-4 py-4 text-center text-xs text-slate-400 italic">
                                                    Belum ada Kriteria Unjuk Kerja (KUK) pada elemen ini.
                                                </div>
                                            @endforelse
                                        </div>

                                        <!-- FORM INLINE TAMBAH KUK BARU KE ELEMEN INI -->
                                        <form action="{{ route('admin.kuk.simpan', $e->id) }}" method="POST" class="mt-3 flex flex-col sm:flex-row items-center gap-2">
                                            @csrf
                                            <div class="w-full sm:w-28 shrink-0">
                                                <input type="text" 
                                                       name="nomor_kuk" 
                                                       class="w-full bg-white border border-slate-200 rounded-xl px-3 py-2 text-xs font-mono font-bold text-slate-800 placeholder-slate-400 focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 transition-colors shadow-2xs" 
                                                       placeholder="No ({{ $e->nomor_elemen }}.1)" 
                                                       required>
                                            </div>
                                            <div class="flex-1 w-full">
                                                <input type="text" 
                                                       name="pernyataan_kuk" 
                                                       class="w-full bg-white border border-slate-200 rounded-xl px-3.5 py-2 text-xs text-slate-800 placeholder-slate-400 focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 transition-colors shadow-2xs" 
                                                       placeholder="Pernyataan Kriteria Unjuk Kerja..." 
                                                       required>
                                            </div>
                                            <button type="submit" 
                                                    class="w-full sm:w-auto px-4 py-2 rounded-xl bg-indigo-600 hover:bg-indigo-700 text-white font-bold text-xs shadow-xs transition-colors cursor-pointer flex items-center justify-center gap-1.5 shrink-0">
                                                <i class="fa-solid fa-plus text-[10px]"></i>
                                                <span>KUK</span>
                                            </button>
                                        </form>
                                    </div>
                                </div>
                            @empty
                                <div class="text-center py-8 px-4 bg-white rounded-2xl border border-dashed border-slate-300 text-slate-500 space-y-2">
                                    <div class="w-10 h-10 rounded-full bg-slate-100 text-slate-400 flex items-center justify-center mx-auto">
                                        <i class="fa-solid fa-folder-open text-base"></i>
                                    </div>
                                    <p class="text-xs font-semibold">Belum ada elemen kompetensi pada unit ini.</p>
                                    <p class="text-[11px] text-slate-400">Gunakan formulir di bawah untuk menambahkan elemen pertama.</p>
                                </div>
                            @endforelse
                        </div>
                    </div>

                    <!-- FORM TAMBAH ELEMEN KOMPETENSI BARU BESERTA KUK AWAL -->
                    <div class="bg-white rounded-2xl border border-indigo-100/90 shadow-2xs p-5 relative overflow-hidden">
                        <div class="absolute top-0 left-0 right-0 h-1 bg-gradient-to-r from-indigo-500 to-blue-500"></div>
                        
                        <div class="flex items-center gap-2.5 mb-4">
                            <div class="w-8 h-8 rounded-lg bg-indigo-50 text-indigo-600 flex items-center justify-center font-bold text-sm">
                                <i class="fa-solid fa-plus text-xs"></i>
                            </div>
                            <div>
                                <h4 class="text-sm font-bold text-slate-900">Tambah Elemen Kompetensi Baru</h4>
                                <p class="text-[11px] text-slate-500">Tambahkan elemen kompetensi dan daftarkan KUK awalnya secara langsung.</p>
                            </div>
                        </div>

                        <form action="{{ route('admin.elemen.simpan', $u->id) }}" method="POST" class="space-y-3.5">
                            @csrf
                            <div class="grid grid-cols-1 sm:grid-cols-12 gap-3">
                                <div class="sm:col-span-3">
                                    <label class="block text-xs font-bold text-slate-700 mb-1">No. Elemen</label>
                                    <input type="number" 
                                           name="nomor_elemen" 
                                           class="w-full bg-slate-50 border border-slate-200 rounded-xl px-3.5 py-2 text-xs font-mono font-bold text-slate-800 focus:bg-white focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 transition-colors shadow-2xs" 
                                           value="{{ $u->elemenKompetensi->count() + 1 }}" 
                                           required>
                                </div>
                                <div class="sm:col-span-9">
                                    <label class="block text-xs font-bold text-slate-700 mb-1">Nama Elemen Kompetensi</label>
                                    <input type="text" 
                                           name="nama_elemen" 
                                           class="w-full bg-slate-50 border border-slate-200 rounded-xl px-3.5 py-2 text-xs font-semibold text-slate-800 placeholder-slate-400 focus:bg-white focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 transition-colors shadow-2xs" 
                                           placeholder="misal: Melakukan persiapan pekerjaan" 
                                           required>
                                </div>
                            </div>

                            <div>
                                <label class="block text-xs font-bold text-slate-700 mb-1">
                                    Pertanyaan Elemen (Opsional)
                                    <span class="text-[10px] font-normal text-slate-400 ml-1">- Benchmark pertanyaan asesi</span>
                                </label>
                                <input type="text" 
                                       name="pertanyaan_elemen" 
                                       class="w-full bg-slate-50 border border-slate-200 rounded-xl px-3.5 py-2 text-xs text-slate-800 placeholder-slate-400 focus:bg-white focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 transition-colors shadow-2xs" 
                                       placeholder="misal: Dapatkah Saya Menyiapkan Pekerjaan sesuai SOP?">
                            </div>

                            <div class="bg-slate-50 rounded-xl border border-slate-200 p-4 space-y-2.5">
                                <div class="flex items-center justify-between">
                                    <label class="text-xs font-bold text-slate-700 uppercase tracking-wider block">
                                        Daftar KUK Awal (Opsional):
                                    </label>
                                    <button type="button" 
                                            class="px-2.5 py-1 rounded-lg bg-white border border-slate-200 hover:bg-slate-100 text-slate-700 font-bold text-[11px] shadow-2xs transition-colors cursor-pointer flex items-center gap-1" 
                                            onclick="tambahBarisKukModal('container-kuk-unit-{{ $u->id }}', {{ $u->elemenKompetensi->count() + 1 }})">
                                        <i class="fa-solid fa-plus text-[9px]"></i>
                                        <span>Tambah Baris KUK</span>
                                    </button>
                                </div>

                                <div id="container-kuk-unit-{{ $u->id }}" class="space-y-2">
                                    <div class="baris-kuk-input flex items-center gap-2">
                                        <div class="w-24 shrink-0">
                                            <input type="text" 
                                                   name="kuk_nomor[]" 
                                                   class="w-full bg-white border border-slate-200 rounded-lg px-2.5 py-1.5 text-xs font-mono font-bold text-slate-800 placeholder-slate-400 focus:ring-2 focus:ring-indigo-500" 
                                                   placeholder="No. ({{ $u->elemenKompetensi->count() + 1 }}.1)">
                                        </div>
                                        <div class="flex-1">
                                            <input type="text" 
                                                   name="kuk_pernyataan[]" 
                                                   class="w-full bg-white border border-slate-200 rounded-lg px-3 py-1.5 text-xs text-slate-800 placeholder-slate-400 focus:ring-2 focus:ring-indigo-500" 
                                                   placeholder="Pernyataan KUK...">
                                        </div>
                                        <button type="button" 
                                                class="w-7 h-7 rounded-lg hover:bg-rose-50 text-slate-400 hover:text-rose-600 transition-colors flex items-center justify-center cursor-pointer shrink-0" 
                                                onclick="this.closest('.baris-kuk-input').remove()">
                                            <i class="fa-solid fa-xmark text-xs"></i>
                                        </button>
                                    </div>
                                </div>
                            </div>

                            <div class="flex justify-end pt-2">
                                <button type="submit" 
                                        class="px-5 py-2.5 rounded-xl bg-indigo-600 hover:bg-indigo-700 text-white font-bold text-xs shadow-xs transition-colors cursor-pointer flex items-center gap-2">
                                    <i class="fa-solid fa-check text-xs"></i>
                                    <span>Simpan Elemen Kompetensi</span>
                                </button>
                            </div>
                        </form>
                    </div>
                </div>

                <!-- 3. MODAL FOOTER -->
                <div class="px-6 py-3.5 bg-slate-50 border-t border-slate-200/80 flex items-center justify-end shrink-0">
                    <button type="button" 
                            onclick="tutupModal('modalKelolaElemen_{{ $u->id }}')" 
                            class="px-4 py-2 rounded-xl bg-white hover:bg-slate-100 text-slate-700 font-bold text-xs border border-slate-200 shadow-2xs transition-colors cursor-pointer">
                        Selesai / Tutup
                    </button>
                </div>
            </div>
        </div>
    @endforeach
@endforeach

<!-- 2. MODAL UBAH SKEMA -->
@foreach($skemaList as $s)
    <div class="modal-overlay" id="modalUbah_{{ $s->id }}">
        <div class="modal-konten-modern w-[95vw] max-w-xl">
            <!-- Modal Header -->
            <div class="px-6 py-4 sm:py-5 bg-white border-b border-slate-200/80 flex items-center justify-between gap-4 shrink-0">
                <div class="flex items-center gap-3">
                    <div class="w-10 h-10 rounded-xl bg-blue-50 border border-blue-100 text-blue-600 flex items-center justify-center shrink-0">
                        <i class="fa-solid fa-pen-to-square text-sm"></i>
                    </div>
                    <div>
                        <h3 class="text-base sm:text-lg font-extrabold text-slate-900 tracking-tight">
                            Ubah Skema Sertifikasi
                        </h3>
                        <p class="text-xs text-slate-500">Perbarui identitas dan status skema keahlian</p>
                    </div>
                </div>
                <button type="button" 
                        onclick="tutupModal('modalUbah_{{ $s->id }}')" 
                        aria-label="Tutup modal" 
                        class="w-9 h-9 rounded-xl bg-slate-100 hover:bg-slate-200 text-slate-500 hover:text-slate-800 transition-colors flex items-center justify-center cursor-pointer">
                    <i class="fa-solid fa-xmark text-sm"></i>
                </button>
            </div>

            <!-- Modal Body Form -->
            <form action="{{ route('admin.skema.ubah', $s->id) }}" method="POST" class="flex flex-col flex-1 min-h-0 overflow-hidden">
                @csrf
                <div class="modal-scroll-body p-6 space-y-4 bg-slate-50/50">
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                        <div>
                            <label class="block text-xs font-bold text-slate-700 mb-1">Kode Skema</label>
                            <input type="text" name="kode_skema" class="w-full bg-white border border-slate-200 rounded-xl px-3.5 py-2.5 text-xs font-mono font-bold text-slate-800 focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 transition-colors shadow-2xs" value="{{ $s->kode_skema }}" required>
                        </div>
                        <div>
                            <label class="block text-xs font-bold text-slate-700 mb-1">Kategori Keahlian</label>
                            <input type="text" name="kategori" class="w-full bg-white border border-slate-200 rounded-xl px-3.5 py-2.5 text-xs font-semibold text-slate-800 focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 transition-colors shadow-2xs" value="{{ $s->kategori }}" required>
                        </div>
                    </div>

                    <div>
                        <label class="block text-xs font-bold text-slate-700 mb-1">Nama Skema Sertifikasi</label>
                        <input type="text" name="nama_skema" class="w-full bg-white border border-slate-200 rounded-xl px-3.5 py-2.5 text-xs font-semibold text-slate-800 focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 transition-colors shadow-2xs" value="{{ $s->nama_skema }}" required>
                    </div>

                    <div>
                        <label class="block text-xs font-bold text-slate-700 mb-1">Deskripsi Skema</label>
                        <textarea name="deskripsi" class="w-full bg-white border border-slate-200 rounded-xl px-3.5 py-2 text-xs text-slate-800 focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 transition-colors shadow-2xs" rows="3">{{ $s->deskripsi }}</textarea>
                    </div>

                    <div class="p-3 bg-white rounded-xl border border-slate-200 flex items-center gap-3">
                        <input type="checkbox" name="status_aktif" value="1" id="status_aktif_{{ $s->id }}" {{ $s->status_aktif ? 'checked' : '' }} class="w-4 h-4 text-indigo-600 rounded border-slate-300 focus:ring-indigo-500">
                        <label for="status_aktif_{{ $s->id }}" class="text-xs font-bold text-slate-700 cursor-pointer select-none">
                            Aktifkan Skema Ini (Tersedia untuk pendaftaran asesmen)
                        </label>
                    </div>
                </div>

                <!-- Modal Footer -->
                <div class="px-6 py-4 bg-slate-50 border-t border-slate-200/80 flex items-center justify-end gap-2 shrink-0">
                    <button type="button" class="px-4 py-2 rounded-xl bg-white hover:bg-slate-100 text-slate-700 font-bold text-xs border border-slate-200 shadow-2xs transition-colors cursor-pointer" onclick="tutupModal('modalUbah_{{ $s->id }}')">
                        Batal
                    </button>
                    <button type="submit" class="px-5 py-2 rounded-xl bg-indigo-600 hover:bg-indigo-700 text-white font-bold text-xs shadow-xs transition-colors cursor-pointer">
                        Simpan Perubahan
                    </button>
                </div>
            </form>
        </div>
    </div>
@endforeach

<!-- 3. MODAL TAMBAH MASTER SKEMA -->
<div class="modal-overlay" id="modalTambahSkema">
    <div class="modal-konten-modern w-[95vw] max-w-3xl">
        <!-- Modal Header -->
        <div class="px-6 py-4 sm:py-5 bg-white border-b border-slate-200/80 flex items-center justify-between gap-4 shrink-0">
            <div class="flex items-center gap-3">
                <div class="w-10 h-10 rounded-xl bg-indigo-50 border border-indigo-100 text-indigo-600 flex items-center justify-center shrink-0">
                    <i class="fa-solid fa-plus text-sm"></i>
                </div>
                <div>
                    <h3 class="text-base sm:text-lg font-extrabold text-slate-900 tracking-tight">
                        Tambah Master Skema & Unit Kompetensi
                    </h3>
                    <p class="text-xs text-slate-500">Daftarkan kemasan skema sertifikasi baru beserta paket unit kompetensinya</p>
                </div>
            </div>
            <button type="button" 
                    onclick="tutupModal('modalTambahSkema')" 
                    aria-label="Tutup modal" 
                    class="w-9 h-9 rounded-xl bg-slate-100 hover:bg-slate-200 text-slate-500 hover:text-slate-800 transition-colors flex items-center justify-center cursor-pointer">
                <i class="fa-solid fa-xmark text-sm"></i>
            </button>
        </div>

        <form action="{{ route('admin.skema.simpan') }}" method="POST" enctype="multipart/form-data" class="flex flex-col flex-1 min-h-0 overflow-hidden">
            @csrf
            <div class="modal-scroll-body p-6 space-y-6 bg-slate-50/50">
                <!-- SEKSI 1: INFORMASI SKEMA -->
                <div class="bg-white rounded-2xl border border-slate-200/90 p-5 space-y-4 shadow-2xs">
                    <div class="flex items-center gap-2 border-b border-slate-100 pb-3">
                        <span class="w-6 h-6 rounded-full bg-indigo-50 text-indigo-700 font-extrabold text-xs flex items-center justify-center">1</span>
                        <h4 class="text-xs font-bold text-slate-800 uppercase tracking-wider">Informasi Utama Skema</h4>
                    </div>

                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                        <div>
                            <label class="block text-xs font-bold text-slate-700 mb-1">Kode Skema</label>
                            <input type="text" name="kode_skema" class="w-full bg-slate-50 border border-slate-200 rounded-xl px-3.5 py-2.5 text-xs font-mono font-bold text-slate-800 focus:bg-white focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 transition-colors" placeholder="contoh: OKP-RPL-001" required>
                        </div>
                        <div>
                            <label class="block text-xs font-bold text-slate-700 mb-1">Kategori Keahlian</label>
                            <input type="text" name="kategori" class="w-full bg-slate-50 border border-slate-200 rounded-xl px-3.5 py-2.5 text-xs font-semibold text-slate-800 focus:bg-white focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 transition-colors" value="Teknologi Informasi" placeholder="contoh: Teknologi Informasi" required>
                        </div>
                    </div>

                    <div>
                        <label class="block text-xs font-bold text-slate-700 mb-1">Nama Skema Sertifikasi</label>
                        <input type="text" name="nama_skema" class="w-full bg-slate-50 border border-slate-200 rounded-xl px-3.5 py-2.5 text-xs font-semibold text-slate-800 focus:bg-white focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 transition-colors" placeholder="contoh: Pemrogram Junior (Junior Coder)" required>
                    </div>

                    <div>
                        <label class="block text-xs font-bold text-slate-700 mb-1">Deskripsi Skema</label>
                        <textarea name="deskripsi" class="w-full bg-slate-50 border border-slate-200 rounded-xl px-3.5 py-2 text-xs text-slate-800 focus:bg-white focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 transition-colors" rows="2" placeholder="Deskripsi kualifikasi skema..."></textarea>
                    </div>

                    <div class="flex items-center gap-3 pt-1">
                        <input type="checkbox" name="status_aktif" value="1" id="status_aktif_tambah" checked class="w-4 h-4 text-indigo-600 rounded border-slate-300 focus:ring-indigo-500">
                        <label for="status_aktif_tambah" class="text-xs font-bold text-slate-700 cursor-pointer select-none">
                            Aktifkan Skema Ini
                        </label>
                    </div>
                </div>

                <!-- SEKSI 2: MASUKKAN UNIT-UNIT KOMPETENSI -->
                <div class="bg-white rounded-2xl border border-slate-200/90 p-5 space-y-4 shadow-2xs">
                    <div class="flex items-center justify-between gap-3 border-b border-slate-100 pb-3">
                        <div class="flex items-center gap-2">
                            <span class="w-6 h-6 rounded-full bg-indigo-50 text-indigo-700 font-extrabold text-xs flex items-center justify-center">2</span>
                            <div>
                                <h4 class="text-xs font-bold text-slate-800 uppercase tracking-wider">Unit-Unit Kompetensi</h4>
                                <p class="text-[11px] text-slate-500">Isi kode dan judul unit kompetensi yang terdaftar dalam kemasan skema ini</p>
                            </div>
                        </div>
                        <button type="button" class="px-3 py-1.5 rounded-xl bg-indigo-50 hover:bg-indigo-100 text-indigo-700 font-bold text-xs border border-indigo-200/80 transition-colors cursor-pointer flex items-center gap-1.5 shrink-0" id="btn-tambah-baris-unit">
                            <i class="fa-solid fa-plus text-[10px]"></i>
                            <span>Tambah Baris Unit</span>
                        </button>
                    </div>

                    <div id="container-baris-unit" class="space-y-3">
                        <div class="baris-unit-input grid grid-cols-1 sm:grid-cols-12 gap-2.5 items-center p-3 rounded-xl bg-slate-50/70 border border-slate-200/80">
                            <div class="sm:col-span-4">
                                <input type="text" name="unit_kode[]" class="w-full bg-white border border-slate-200 rounded-lg px-3 py-2 text-xs font-mono font-bold text-slate-800 focus:ring-2 focus:ring-indigo-500 shadow-2xs" placeholder="Kode (misal: J.620100.004.01)" required>
                            </div>
                            <div class="sm:col-span-5">
                                <input type="text" name="unit_judul[]" class="w-full bg-white border border-slate-200 rounded-lg px-3 py-2 text-xs font-semibold text-slate-800 focus:ring-2 focus:ring-indigo-500 shadow-2xs" placeholder="Judul Unit Kompetensi" required>
                            </div>
                            <div class="sm:col-span-2">
                                <select name="unit_standar[]" class="w-full bg-white border border-slate-200 rounded-lg px-2.5 py-2 text-xs font-semibold text-slate-800 focus:ring-2 focus:ring-indigo-500 shadow-2xs" required>
                                    <option value="KKNI">KKNI</option>
                                    <option value="Okupasi" selected>Okupasi</option>
                                    <option value="Klaster">Klaster</option>
                                </select>
                            </div>
                            <div class="sm:col-span-1 text-right sm:text-center">
                                <button type="button" class="btn-action-delete btn-hapus-baris-unit w-8 h-8 rounded-lg bg-rose-50 hover:bg-rose-100 text-rose-600 transition-colors inline-flex items-center justify-center cursor-pointer" title="Hapus Baris">
                                    <i class="fa-solid fa-trash-can text-xs"></i>
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Modal Footer -->
            <div class="px-6 py-4 bg-slate-50 border-t border-slate-200/80 flex items-center justify-end gap-2 shrink-0">
                <button type="button" class="px-4 py-2 rounded-xl bg-white hover:bg-slate-100 text-slate-700 font-bold text-xs border border-slate-200 shadow-2xs transition-colors cursor-pointer" onclick="tutupModal('modalTambahSkema')">
                    Batal
                </button>
                <button type="submit" class="px-5 py-2 rounded-xl bg-indigo-600 hover:bg-indigo-700 text-white font-bold text-xs shadow-xs transition-colors cursor-pointer">
                    Simpan Skema & Unit Kompetensi
                </button>
            </div>
        </form>
    </div>
</div>
@endsection

@push('js')
<script>
    function toggleAccordion(skemaId) {
        const konten = document.getElementById('konten-skema-' + skemaId);
        const text = document.getElementById('text-toggle-' + skemaId);
        const arrow = document.getElementById('arrow-toggle-' + skemaId);

        if (konten) {
            const isTerbuka = konten.classList.toggle('terbuka');
            if (arrow) {
                arrow.style.transform = isTerbuka ? 'rotate(180deg)' : 'rotate(0deg)';
            }
            if (text) {
                text.textContent = isTerbuka ? 'Tutup Unit' : 'Detail Unit';
            }
        }
    }

    document.addEventListener('DOMContentLoaded', () => {
        const btnTambahUnit = document.getElementById('btn-tambah-baris-unit');
        const containerUnit = document.getElementById('container-baris-unit');

        if (btnTambahUnit && containerUnit) {
            btnTambahUnit.addEventListener('click', () => {
                const newRow = document.createElement('div');
                newRow.className = 'baris-unit-input grid grid-cols-1 sm:grid-cols-12 gap-2.5 items-center p-3 rounded-xl bg-slate-50/70 border border-slate-200/80 animasi-fade';
                newRow.innerHTML = `
                    <div class="sm:col-span-4">
                        <input type="text" name="unit_kode[]" class="w-full bg-white border border-slate-200 rounded-lg px-3 py-2 text-xs font-mono font-bold text-slate-800 focus:ring-2 focus:ring-indigo-500 shadow-2xs" placeholder="Kode (misal: J.620100.017.01)" required>
                    </div>
                    <div class="sm:col-span-5">
                        <input type="text" name="unit_judul[]" class="w-full bg-white border border-slate-200 rounded-lg px-3 py-2 text-xs font-semibold text-slate-800 focus:ring-2 focus:ring-indigo-500 shadow-2xs" placeholder="Judul Unit Kompetensi" required>
                    </div>
                    <div class="sm:col-span-2">
                        <select name="unit_standar[]" class="w-full bg-white border border-slate-200 rounded-lg px-2.5 py-2 text-xs font-semibold text-slate-800 focus:ring-2 focus:ring-indigo-500 shadow-2xs" required>
                            <option value="KKNI">KKNI</option>
                            <option value="Okupasi" selected>Okupasi</option>
                            <option value="Klaster">Klaster</option>
                        </select>
                    </div>
                    <div class="sm:col-span-1 text-right sm:text-center">
                        <button type="button" class="btn-hapus-baris-unit w-8 h-8 rounded-lg bg-rose-50 hover:bg-rose-100 text-rose-600 transition-colors inline-flex items-center justify-center cursor-pointer" title="Hapus Baris">
                            <i class="fa-solid fa-trash-can text-xs"></i>
                        </button>
                    </div>
                `;
                containerUnit.appendChild(newRow);
            });

            containerUnit.addEventListener('click', (e) => {
                const btnHapus = e.target.closest('.btn-hapus-baris-unit');
                if (btnHapus) {
                    const row = btnHapus.closest('.baris-unit-input');
                    if (containerUnit.querySelectorAll('.baris-unit-input').length > 1) {
                        row.remove();
                    } else {
                        alert('Minimal harus ada 1 baris unit kompetensi.');
                    }
                }
            });
        }
    });

    function tambahBarisKukModal(containerId, elemenNomor) {
        const container = document.getElementById(containerId);
        if (container) {
            const count = container.querySelectorAll('.baris-kuk-input').length + 1;
            const newRow = document.createElement('div');
            newRow.className = 'baris-kuk-input flex items-center gap-2 animasi-fade';
            newRow.innerHTML = `
                <div class="w-24 shrink-0">
                    <input type="text" name="kuk_nomor[]" class="w-full bg-white border border-slate-200 rounded-lg px-2.5 py-1.5 text-xs font-mono font-bold text-slate-800 placeholder-slate-400 focus:ring-2 focus:ring-indigo-500" value="${elemenNomor}.${count}" placeholder="No.">
                </div>
                <div class="flex-1">
                    <input type="text" name="kuk_pernyataan[]" class="w-full bg-white border border-slate-200 rounded-lg px-3 py-1.5 text-xs text-slate-800 placeholder-slate-400 focus:ring-2 focus:ring-indigo-500" placeholder="Pernyataan KUK...">
                </div>
                <button type="button" class="w-7 h-7 rounded-lg hover:bg-rose-50 text-slate-400 hover:text-rose-600 transition-colors flex items-center justify-center cursor-pointer shrink-0" onclick="this.closest('.baris-kuk-input').remove()">
                    <i class="fa-solid fa-xmark text-xs"></i>
                </button>
            `;
            container.appendChild(newRow);
        }
    }
</script>
@endpush
