@extends('tata-letak.dasbor')

@section('judul', 'Kelola Bank Soal - ' . $instrument->title)

@push('css')
    <link rel="stylesheet" href="{{ asset('css/admin/dashboard-admin.css') }}">
    <link rel="stylesheet" href="{{ asset('css/formulir/formulir-bnsp.css') }}">
    <style>
        /* Modern Micro-Interactions & Styling */
        .muk-input-field {
            width: 100%;
            background-color: #ffffff;
            border: 1.5px solid #e2e8f0;
            border-radius: 0.75rem;
            padding: 0.65rem 0.95rem;
            font-size: 0.875rem;
            color: #1e293b;
            transition: all 0.2s cubic-bezier(0.16, 1, 0.3, 1);
            outline: none;
        }
        .muk-input-field:focus {
            border-color: #3b82f6;
            background-color: #ffffff;
            box-shadow: 0 0 0 3px rgba(59, 130, 246, 0.12);
        }
        .muk-input-field::placeholder {
            color: #94a3b8;
        }

        /* Option Choice Row Builder (Interactive) */
        .muk-choice-card {
            display: flex;
            align-items: center;
            gap: 0.75rem;
            padding: 0.65rem 0.85rem;
            border-radius: 0.75rem;
            border: 1.5px solid #e2e8f0;
            background: #f8fafc;
            transition: all 0.2s ease;
            position: relative;
        }
        .muk-choice-card:hover {
            border-color: #cbd5e1;
            background: #ffffff;
        }
        .muk-choice-card.is-active-choice {
            background: #f0fdf4;
            border-color: #10b981;
            box-shadow: 0 0 0 1px #10b981;
        }
        .muk-choice-letter {
            width: 2rem;
            height: 2rem;
            border-radius: 0.5rem;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            font-weight: 800;
            font-size: 0.825rem;
            background: #e2e8f0;
            color: #475569;
            flex-shrink: 0;
            transition: all 0.2s ease;
            cursor: pointer;
        }
        .muk-choice-card.is-active-choice .muk-choice-letter {
            background: #10b981;
            color: #ffffff;
        }
        .muk-correct-tag {
            display: none;
            font-size: 0.7rem;
            font-weight: 700;
            padding: 0.2rem 0.6rem;
            border-radius: 0.375rem;
            background: #dcfce7;
            color: #166534;
            flex-shrink: 0;
        }
        .muk-choice-card.is-active-choice .muk-correct-tag {
            display: inline-flex;
            align-items: center;
            gap: 0.25rem;
        }

        /* Question Item Card */
        .muk-question-card {
            background: #ffffff;
            border: 1px solid #e2e8f0;
            border-radius: 1rem;
            padding: 1.5rem;
            margin-bottom: 1.25rem;
            box-shadow: 0 1px 3px rgba(15, 23, 42, 0.04);
            transition: all 0.25s cubic-bezier(0.16, 1, 0.3, 1);
        }
        .muk-question-card:hover {
            border-color: #cbd5e1;
            box-shadow: 0 10px 25px -5px rgba(15, 23, 42, 0.06);
            transform: translateY(-1px);
        }

        /* Question Options Display */
        .muk-option-display {
            display: flex;
            align-items: center;
            gap: 0.75rem;
            padding: 0.7rem 0.95rem;
            border-radius: 0.75rem;
            border: 1px solid #f1f5f9;
            background: #f8fafc;
            font-size: 0.875rem;
            color: #334155;
            transition: all 0.2s ease;
        }
        .muk-option-display.is-correct-answer {
            background: #f0fdf4;
            border-color: #86efac;
            font-weight: 600;
            color: #166534;
        }

        /* Modal Backdrop & Animations */
        .modal-overlay {
            position: fixed !important;
            top: 0 !important; left: 0 !important; right: 0 !important; bottom: 0 !important;
            background: rgba(15, 23, 42, 0.6) !important;
            display: none;
            align-items: center !important;
            justify-content: center !important;
            z-index: 99999 !important;
            backdrop-filter: blur(4px) !important;
            padding: 1rem !important;
        }
        .modal-overlay.terbuka,
        .modal-overlay.is-open {
            display: flex !important;
        }
        .modal-box-admin {
            background: #ffffff;
            border-radius: 1.25rem;
            width: 100%;
            max-width: 540px;
            padding: 1.75rem;
            box-shadow: 0 25px 50px -12px rgba(15, 23, 42, 0.25);
            animation: modalSlideUp 0.25s cubic-bezier(0.16, 1, 0.3, 1);
            position: relative;
        }
        @keyframes modalSlideUp {
            from { transform: translateY(16px) scale(0.98); opacity: 0; }
            to { transform: translateY(0) scale(1); opacity: 1; }
        }

        /* Lock / Read-Only Mode */
        .mode-muk-locked .form-tambah-soal-card,
        .mode-muk-locked .btn-hapus-item,
        .mode-muk-locked .btn-submit-metadata {
            display: none !important;
        }
        .mode-muk-locked .input-metadata-muk {
            pointer-events: none !important;
            background-color: #f8fafc !important;
            border-color: #e2e8f0 !important;
            cursor: default !important;
        }
    </style>
@endpush

@section('konten')
@php
    $codeKey = \App\Models\SchemeMasterInstrument::normalizeCode($instrument->instrument_code);
    $hasItems = $instrument->questionBanks->count() > 0 || $instrument->productSpecifications->count() > 0 || !empty($instrument->additional_metadata);
@endphp

<div class="max-w-6xl mx-auto px-2 sm:px-4 py-3 {{ $hasItems ? 'mode-muk-locked' : '' }}" id="container-manage-muk">

    <!-- TOP TOOLBAR: NAVIGASI & AKSI CEPAT -->
    <div class="mb-5 flex flex-col sm:flex-row sm:items-center justify-between gap-3.5">
        <!-- Tombol Kembali & Badge Kode -->
        <div class="flex items-center gap-2.5">
            @php
                $targetKembaliMuk = (auth()->check() && auth()->user()->peran === 'asesor')
                    ? route('asesor.mapa', ['skema_id' => $instrument->skema_id])
                    : route('admin.master-muk.index', ['skema_id' => $instrument->skema_id]);
            @endphp
            <a href="{{ $targetKembaliMuk }}" 
               class="inline-flex items-center gap-2 px-3.5 py-1.5 rounded-xl border border-slate-200 bg-white hover:bg-slate-50 hover:border-slate-300 text-slate-700 font-semibold text-xs transition-all shadow-2xs">
                <i class="fa-solid fa-arrow-left text-[11px] text-slate-500"></i>
                <span>Kembali</span>
            </a>
            <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-xl bg-blue-50/80 border border-blue-200/60 text-blue-700 font-mono text-xs font-bold shadow-2xs">
                <span class="w-1.5 h-1.5 rounded-full bg-blue-500"></span>
                <span>{{ strtoupper($instrument->instrument_code) }} Master</span>
            </span>
        </div>
        
        <!-- Action Buttons Group -->
        <div class="flex items-center gap-2 flex-wrap">
            @if($hasItems)
                <button type="button" id="btn-toggle-edit-muk" onclick="toggleEditMuk()" 
                        class="inline-flex items-center gap-1.5 px-3.5 py-1.5 rounded-xl text-white font-semibold text-xs transition-all shadow-2xs cursor-pointer"
                        style="background: #4f46e5;">
                    <i class="fa-solid fa-pen-to-square text-[11px]"></i>
                    <span id="text-toggle-muk">Edit Formulir</span>
                </button>
            @endif

            <button type="button" onclick="bukaModalClone(event)" class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-xl border border-slate-200 bg-white hover:bg-slate-50 hover:border-slate-300 text-slate-700 font-semibold text-xs transition-colors shadow-2xs cursor-pointer">
                <i class="fa-regular fa-clone text-[11px] text-slate-500"></i>
                <span>Duplikasi Paket</span>
            </button>
            
            <a href="{{ route('admin.master-muk.export', ['instrumentId' => $instrument->id, 'format' => 'csv']) }}" class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-xl border border-slate-200 bg-white hover:bg-slate-50 hover:border-slate-300 text-slate-700 font-semibold text-xs transition-colors shadow-2xs">
                <i class="fa-solid fa-file-export text-[11px] text-slate-500"></i>
                <span>Export CSV</span>
            </a>

            <a href="{{ route('admin.master-muk.edit', $instrument->id) }}" class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-xl border border-slate-200 bg-white hover:bg-slate-50 hover:border-slate-300 text-slate-700 font-semibold text-xs transition-colors shadow-2xs">
                <i class="fa-solid fa-sliders text-[11px] text-slate-500"></i>
                <span>Pengaturan</span>
            </a>
        </div>
    </div>

    <!-- NOTIFIKASI SUKSES / ERROR -->
    @if(session('sukses'))
        <div class="mb-5 bg-emerald-50/90 border border-emerald-200 text-emerald-800 px-4 py-3 rounded-2xl flex items-center gap-3 text-xs font-medium shadow-2xs">
            <div class="w-6 h-6 rounded-full bg-emerald-100 text-emerald-600 flex items-center justify-center shrink-0">
                <i class="fa-solid fa-check text-xs"></i>
            </div>
            <span>{{ session('sukses') }}</span>
        </div>
    @endif

    @if(session('error'))
        <div class="mb-5 bg-rose-50/90 border border-rose-200 text-rose-800 px-4 py-3 rounded-2xl flex items-center gap-3 text-xs font-medium shadow-2xs">
            <div class="w-6 h-6 rounded-full bg-rose-100 text-rose-600 flex items-center justify-center shrink-0">
                <i class="fa-solid fa-triangle-exclamation text-xs"></i>
            </div>
            <span>{{ session('error') }}</span>
        </div>
    @endif

    <!-- HERO / INSTRUMENT HEADER CARD -->
    <div class="relative bg-white rounded-2xl border border-slate-200/90 shadow-2xs p-5 sm:p-6 mb-6 overflow-hidden">
        <!-- Subtle gradient backdrop accent -->
        <div class="absolute -top-24 -right-24 w-60 h-60 bg-gradient-to-br from-blue-50/60 to-indigo-50/40 rounded-full blur-2xl pointer-events-none"></div>

        <div class="relative flex flex-col md:flex-row md:items-center justify-between gap-5">
            <div class="space-y-2.5 max-w-3xl">
                <div class="inline-flex items-center gap-2 px-3 py-1 rounded-lg bg-slate-100 border border-slate-200/70 text-slate-700 text-xs font-medium">
                    <i class="fa-solid fa-layer-group text-slate-500 text-[11px]"></i>
                    <span>{{ $instrument->skema->kode_skema ?? 'SKEMA' }} • {{ $instrument->skema->nama_skema ?? 'Skema Sertifikasi' }}</span>
                </div>

                <h1 class="text-xl sm:text-2xl font-black text-slate-900 tracking-tight leading-snug">
                    {{ $instrument->title }}
                </h1>

                <!-- Metadata Badges -->
                <div class="flex items-center gap-2.5 flex-wrap pt-0.5 text-xs">
                    <span class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-xl bg-slate-50 border border-slate-200/80 text-slate-600 font-medium shadow-2xs">
                        <i class="fa-regular fa-clock text-slate-400"></i>
                        <span>Durasi: <strong class="text-slate-900 font-bold">{{ $instrument->time_limit_minutes ?? 60 }} Menit</strong></span>
                    </span>

                    <span class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-xl bg-slate-50 border border-slate-200/80 text-slate-600 font-medium shadow-2xs">
                        <i class="fa-regular fa-folder-open text-slate-400"></i>
                        @if(in_array($codeKey, ['ia_11', 'ia11']))
                            <span>Total: <strong class="text-slate-900 font-bold">{{ $instrument->productSpecifications->count() }} Spesifikasi</strong></span>
                        @else
                            <span>Total: <strong class="text-slate-900 font-bold">{{ $instrument->questionBanks->count() }} Butir Soal</strong></span>
                        @endif
                    </span>

                    <span class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-xl {{ $instrument->is_active ? 'bg-emerald-50/80 border border-emerald-200/80 text-emerald-700' : 'bg-slate-100 border border-slate-200 text-slate-600' }} font-semibold shadow-2xs">
                        <span class="w-2 h-2 rounded-full {{ $instrument->is_active ? 'bg-emerald-500 animate-pulse' : 'bg-slate-400' }}"></span>
                        <span>Status: <strong class="{{ $instrument->is_active ? 'text-emerald-800' : 'text-slate-700' }}">{{ $instrument->is_active ? 'Aktif' : 'Nonaktif' }}</strong></span>
                    </span>
                </div>
            </div>

            <!-- Tipe Format Instrument Badge -->
            <div class="shrink-0 self-start md:self-auto">
                @php
                    $tipeConfig = match($codeKey) {
                        'ia_05', 'ia05' => ['label' => 'Pilihan Ganda (CBT)', 'icon' => 'fa-list-check', 'class' => 'bg-blue-50 text-blue-700 border-blue-200/80'],
                        'ia_06', 'ia06' => ['label' => 'Pertanyaan Esai', 'icon' => 'fa-pen-clip', 'class' => 'bg-indigo-50 text-indigo-700 border-indigo-200/80'],
                        'ia_07', 'ia07' => ['label' => 'Pertanyaan Lisan (DPL)', 'icon' => 'fa-comments', 'class' => 'bg-sky-50 text-sky-700 border-sky-200/80'],
                        'ia_03', 'ia03' => ['label' => 'Pertanyaan Pendukung Observasi (PMO)', 'icon' => 'fa-clipboard-question', 'class' => 'bg-amber-50 text-amber-700 border-amber-200/80'],
                        'ia_01', 'ia01' => ['label' => 'Ceklis Observasi Praktik (CLO)', 'icon' => 'fa-square-check', 'class' => 'bg-blue-50 text-blue-700 border-blue-200/80'],
                        'ia_02', 'ia02' => ['label' => 'Tugas Praktik Demonstrasi', 'icon' => 'fa-laptop-code', 'class' => 'bg-violet-50 text-violet-700 border-violet-200/80'],
                        'ia_04a', 'ia04a' => ['label' => 'TOR Proyek Singkat', 'icon' => 'fa-diagram-project', 'class' => 'bg-amber-50 text-amber-700 border-amber-200/80'],
                        'ia_11', 'ia11' => ['label' => 'Mutu Produk (IA.11)', 'icon' => 'fa-circle-check', 'class' => 'bg-emerald-50 text-emerald-700 border-emerald-200/80'],
                        default => ['label' => strtoupper($instrument->instrument_code), 'icon' => 'fa-file-lines', 'class' => 'bg-slate-50 text-slate-700 border-slate-200/80'],
                    };
                @endphp
                <div class="inline-flex items-center gap-2 px-4 py-2.5 rounded-xl border font-bold text-xs sm:text-sm shadow-2xs {{ $tipeConfig['class'] }}">
                    <i class="fa-solid {{ $tipeConfig['icon'] }} text-xs"></i>
                    <span>{{ $tipeConfig['label'] }}</span>
                </div>
            </div>
        </div>
    </div>

    <!-- BANNER MODE TAMPILAN / EDIT FORMULIR -->
    @if($hasItems)
        <div id="banner-muk-locked" class="bg-slate-50 border border-slate-200/90 rounded-2xl p-4 mb-6 flex flex-col sm:flex-row sm:items-center justify-between gap-3 text-xs text-slate-600 shadow-2xs">
            <div class="flex items-start sm:items-center gap-3">
                <div class="w-8 h-8 rounded-xl bg-slate-200/70 text-slate-600 flex items-center justify-center shrink-0 mt-0.5 sm:mt-0">
                    <i class="fa-solid fa-lock text-xs"></i>
                </div>
                <div>
                    <strong class="text-slate-900 font-bold block text-sm">Mode Tampilan (Hanya Lihat)</strong>
                    <span class="text-slate-500">Bank soal dalam status terkunci. Klik <strong>Edit Formulir</strong> untuk menambah, memperbarui, atau menghapus butir soal.</span>
                </div>
            </div>
            <button type="button" onclick="toggleEditMuk()" class="inline-flex items-center gap-1.5 px-4 py-2 bg-indigo-600 hover:bg-indigo-700 active:bg-indigo-800 text-white rounded-xl font-bold text-xs shrink-0 shadow-2xs transition-all cursor-pointer self-start sm:self-auto">
                <i class="fa-solid fa-lock-open text-[11px]"></i>
                <span>Edit Formulir</span>
            </button>
        </div>

        <div id="banner-muk-editing" class="bg-amber-50/90 border border-amber-200/90 rounded-2xl p-4 mb-6 text-xs text-amber-800 shadow-2xs" style="display: none;">
            <div class="flex items-center gap-3">
                <div class="w-8 h-8 rounded-xl bg-amber-100 text-amber-700 flex items-center justify-center shrink-0">
                    <i class="fa-solid fa-pen-ruler text-xs"></i>
                </div>
                <div>
                    <strong class="text-amber-950 font-bold block text-sm">Mode Edit Aktif</strong>
                    <span class="text-amber-700">Anda dapat menambah butir soal baru, mengubah opsi jawaban, kunci penilaian, atau menghapus butir soal.</span>
                </div>
            </div>
        </div>
    @endif

    <!-- ========================================================================= -->
    <!-- BAGIAN 1: FR.IA.05 (PILIHAN GANDA / CBT) -->
    <!-- ========================================================================= -->
    @if(in_array($codeKey, ['ia_05', 'ia05']))

        <!-- FORM TAMBAH SOAL PG (ELEVATED CARD DESIGN) -->
        <div class="form-tambah-soal-card bg-white rounded-2xl border border-slate-200/90 shadow-2xs overflow-hidden mb-8">
            <!-- Card Header -->
            <div class="px-6 py-4 border-b border-slate-100 bg-slate-50/50 flex items-center justify-between">
                <div class="flex items-center gap-3">
                    <div class="w-9 h-9 rounded-xl bg-blue-50 text-blue-600 border border-blue-100 flex items-center justify-center font-bold">
                        <i class="fa-solid fa-circle-plus text-sm"></i>
                    </div>
                    <div>
                        <h2 class="text-base font-bold text-slate-900 tracking-tight">
                            Tambah Butir Soal Pilihan Ganda (PG)
                        </h2>
                        <p class="text-xs text-slate-500">
                            Lengkapi pertanyaan, unggah gambar pendukung jika ada, lalu tentukan kunci jawaban yang benar.
                        </p>
                    </div>
                </div>
            </div>

            <form action="{{ route('admin.master-muk.soal.store') }}" method="POST" enctype="multipart/form-data" class="p-6">
                @csrf
                <input type="hidden" name="scheme_master_instrument_id" value="{{ $instrument->id }}">
                <input type="hidden" name="question_type" value="multiple_choice">

                <!-- ROW 1: RELASI KUK & BOBOT POIN -->
                <div class="grid grid-cols-1 md:grid-cols-3 gap-4 mb-4">
                    <div class="md:col-span-2">
                        <label class="block font-semibold text-xs text-slate-700 mb-1.5">
                            Relasi Elemen / KUK Standar Kompetensi
                        </label>
                        <div class="relative">
                            <select name="kuk_id" class="muk-input-field appearance-none pr-10 cursor-pointer">
                                <option value="">-- Umum / Seluruh Unit Kompetensi --</option>
                                @foreach($kukList as $k)
                                    <option value="{{ $k['id'] }}">{{ $k['label'] }}</option>
                                @endforeach
                            </select>
                            <div class="absolute inset-y-0 right-0 flex items-center px-3 pointer-events-none text-slate-400">
                                <i class="fa-solid fa-chevron-down text-xs"></i>
                            </div>
                        </div>
                    </div>

                    <div>
                        <label class="block font-semibold text-xs text-slate-700 mb-1.5">
                            Bobot Nilai / Poin
                        </label>
                        <div class="relative">
                            <input type="number" name="points" value="1" min="1" max="100" class="muk-input-field pr-12 font-bold text-slate-800">
                            <span class="absolute inset-y-0 right-0 flex items-center px-3.5 text-xs text-slate-400 font-semibold pointer-events-none">
                                Poin
                            </span>
                        </div>
                    </div>
                </div>

                <!-- ROW 2: TEKS PERTANYAAN -->
                <div class="mb-4">
                    <label class="block font-semibold text-xs text-slate-700 mb-1.5">
                        Teks Butir Pertanyaan <span class="text-rose-500">*</span>
                    </label>
                    <textarea name="question_text" rows="3" class="muk-input-field leading-relaxed resize-y" placeholder="Ketik kalimat butir pertanyaan soal pilihan ganda di sini..." required></textarea>
                </div>

                <!-- ROW 3: GAMBAR PENDUKUNG SOAL (MODERN FILE UPLOAD PREVIEW) -->
                <div class="mb-5">
                    <label class="block font-semibold text-xs text-slate-700 mb-1.5">
                        Gambar Pendukung Soal <span class="text-slate-400 font-normal">(Diagram / Skema / Ilustrasi - Opsional)</span>
                    </label>
                    
                    <div class="border-1.5 border-dashed border-slate-200 hover:border-slate-300 rounded-xl p-3.5 bg-slate-50/40 hover:bg-white transition-all flex flex-col sm:flex-row sm:items-center justify-between gap-3">
                        <div class="flex items-center gap-3">
                            <div class="w-10 h-10 rounded-xl bg-blue-50 text-blue-600 flex items-center justify-center shrink-0 border border-blue-100">
                                <i class="fa-regular fa-image text-sm"></i>
                            </div>
                            <div>
                                <input type="file" name="image" id="pg-image-input" accept="image/*" class="text-xs text-slate-600 file:mr-3 file:py-1.5 file:px-3 file:rounded-lg file:border-0 file:text-xs file:font-semibold file:bg-blue-50 file:text-blue-700 hover:file:bg-blue-100 cursor-pointer">
                                <div class="text-[11px] text-slate-400 mt-0.5">Format file: PNG, JPG, JPEG, GIF (Maks. 2MB)</div>
                            </div>
                        </div>
                        <div id="pg-image-preview-wrap" class="hidden shrink-0">
                            <img id="pg-image-preview" src="#" alt="Preview" class="h-12 w-16 object-cover rounded-lg border border-slate-200 shadow-2xs">
                        </div>
                    </div>
                </div>

                <!-- ROW 4: PILIHAN JAWABAN & PENENTUAN KUNCI JAWABAN (MODERN CHOICE CARDS) -->
                <div class="mb-5">
                    <div class="flex items-center justify-between mb-2">
                        <label class="block font-semibold text-xs text-slate-700">
                            Pilihan Jawaban & Tentukan Kunci Jawaban yang Benar <span class="text-rose-500">*</span>
                        </label>
                        <span class="text-[11px] text-slate-500 font-medium">
                            <i class="fa-solid fa-circle-info text-blue-500 mr-1"></i>Pilih radio pada opsi yang menjadi kunci jawaban benar
                        </span>
                    </div>

                    <div class="space-y-2.5">
                        @foreach(['A', 'B', 'C', 'D'] as $optKey)
                            <div class="muk-choice-card {{ $optKey === 'A' ? 'is-active-choice' : '' }}" id="choice-card-{{ $optKey }}">
                                <label for="radio-choice-{{ $optKey }}" class="muk-choice-letter" title="Pilih sebagai kunci jawaban">
                                    {{ $optKey }}
                                </label>
                                
                                <input type="radio" 
                                       name="correct_answer" 
                                       id="radio-choice-{{ $optKey }}" 
                                       value="{{ $optKey }}" 
                                       {{ $optKey === 'A' ? 'checked' : '' }} 
                                       onchange="handleChoiceChange('{{ $optKey }}')" 
                                       class="w-4 h-4 text-emerald-600 focus:ring-emerald-500 focus:ring-2 cursor-pointer">

                                <input type="text" 
                                       name="options[{{ $optKey }}]" 
                                       placeholder="Tuliskan teks jawaban untuk Opsi {{ $optKey }}..." 
                                       class="flex-1 bg-transparent border-0 text-slate-800 text-xs sm:text-sm font-medium focus:ring-0 focus:outline-none placeholder:text-slate-400" 
                                       required>

                                <span class="muk-correct-tag">
                                    <i class="fa-solid fa-check text-[10px]"></i>
                                    <span>Kunci Benar</span>
                                </span>
                            </div>
                        @endforeach
                    </div>
                </div>

                <!-- ROW 5: PENJELASAN / PEMBAHASAN JAWABAN -->
                <div class="mb-5">
                    <label class="block font-semibold text-xs text-slate-700 mb-1.5">
                        Penjelasan / Pembahasan Jawaban <span class="text-slate-400 font-normal">(Rujukan Bagi Asesor - Opsional)</span>
                    </label>
                    <textarea name="rubric_guide" rows="2" class="muk-input-field leading-relaxed resize-y" placeholder="Tuliskan penjelasan rasional mengapa pilihan tersebut benar sebagai panduan bagi asesor saat validasi..."></textarea>
                </div>

                <!-- SUBMIT BUTTON -->
                <div class="flex items-center justify-end pt-2 border-t border-slate-100">
                    <button type="submit" class="inline-flex items-center gap-2 px-5 py-2.5 rounded-xl bg-blue-600 hover:bg-blue-700 active:bg-blue-800 text-white font-semibold text-xs shadow-xs hover:shadow transition-all cursor-pointer">
                        <i class="fa-solid fa-floppy-disk text-xs"></i>
                        <span>Simpan Butir Soal PG</span>
                    </button>
                </div>
            </form>
        </div>

        <!-- DAFTAR BUTIR SOAL PG YANG SUDAH ADA -->
        <div class="mb-8">
            <div class="flex items-center justify-between mb-4">
                <div class="flex items-center gap-2.5">
                    <h3 class="text-base font-bold text-slate-900 tracking-tight">
                        Daftar Butir Soal PG
                    </h3>
                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-bold bg-slate-100 text-slate-700 border border-slate-200/60">
                        {{ $instrument->questionBanks->count() }} Butir
                    </span>
                </div>
            </div>

            @if($instrument->questionBanks->count() > 0)
                <!-- Bulk Selection Toolbar -->
                <div class="p-3 mb-4 rounded-xl bg-slate-50 border border-slate-200/90 flex flex-wrap items-center justify-between gap-3 shadow-2xs">
                    <div class="flex items-center gap-3">
                        <label class="inline-flex items-center gap-2 text-xs font-semibold text-slate-700 cursor-pointer select-none">
                            <input type="checkbox" onchange="togglePilihSemuaSoal(this)" class="w-4 h-4 rounded border-slate-300 text-rose-600 focus:ring-rose-500 cursor-pointer checkbox-select-all">
                            <span>Pilih Semua Soal</span>
                        </label>
                        <span class="text-slate-300">|</span>
                        <span class="text-xs text-slate-500">
                            <span class="font-bold text-slate-800 count-terpilih">0</span> soal dipilih
                        </span>
                    </div>

                    <div>
                        <button type="button" onclick="hapusSoalTerpilih()" class="btn-hapus-bulk inline-flex items-center gap-1.5 px-3.5 py-1.5 rounded-xl bg-rose-600 hover:bg-rose-700 active:bg-rose-800 disabled:opacity-40 disabled:cursor-not-allowed text-white font-semibold text-xs shadow-2xs transition-all cursor-pointer" disabled>
                            <i class="fa-regular fa-trash-can text-xs"></i>
                            <span>Hapus Terpilih</span>
                        </button>
                    </div>
                </div>
            @endif

            @forelse($instrument->questionBanks as $q)
                <div class="muk-question-card" data-question-id="{{ $q->id }}">
                    <!-- Top Info Row -->
                    <div class="flex items-center justify-between gap-3 mb-3">
                        <div class="flex items-center gap-2.5 flex-wrap">
                            <input type="checkbox" name="selected_questions[]" value="{{ $q->id }}" onchange="updateBulkSelectionState()" class="w-4 h-4 rounded border-slate-300 text-rose-600 focus:ring-rose-500 cursor-pointer checkbox-item-soal" title="Pilih butir soal ini">

                            <span class="w-7 h-7 rounded-lg bg-slate-900 text-white font-mono font-bold text-xs flex items-center justify-center shadow-2xs">
                                {{ $q->order }}
                            </span>

                            @if($q->kriteriaUnjukKerja)
                                <span class="inline-flex items-center gap-1 px-2.5 py-1 rounded-lg text-[11px] font-bold text-blue-700 bg-blue-50 border border-blue-200/60">
                                    <i class="fa-solid fa-tag text-[10px]"></i>
                                    <span>KUK {{ $q->kriteriaUnjukKerja->nomor_kuk }}</span>
                                </span>
                            @endif

                            <span class="inline-flex items-center px-2 py-0.5 rounded-md text-[11px] font-semibold text-slate-500 bg-slate-100">
                                {{ $q->points ?? 1 }} Poin
                            </span>
                        </div>

                        <!-- Action Button -->
                        <div>
                            <form action="{{ route('admin.master-muk.soal.destroy', $q->id) }}" method="POST" onsubmit="return konfirmasiHapus(event, this, 'butir soal #{{ $q->order }}')" class="inline btn-hapus-item">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="inline-flex items-center gap-1 px-2.5 py-1 rounded-lg bg-rose-50 hover:bg-rose-100 text-rose-600 font-semibold text-xs border border-rose-200/70 transition-all cursor-pointer" title="Hapus butir soal ini">
                                    <i class="fa-regular fa-trash-can text-[11px]"></i>
                                    <span>Hapus</span>
                                </button>
                            </form>
                        </div>
                    </div>

                    <!-- Question Text -->
                    <div class="text-sm sm:text-base font-semibold text-slate-800 leading-relaxed mb-4">
                        {{ $q->question_text }}
                    </div>

                    <!-- Question Image (If Any) -->
                    @if($q->image_path)
                        <div class="mb-4">
                            <img src="{{ $q->image_path }}" alt="Gambar Soal" class="max-w-xs max-h-56 object-contain rounded-xl border border-slate-200 bg-slate-50 p-1 shadow-2xs">
                        </div>
                    @endif

                    <!-- Options Grid (2 Columns on Desktop) -->
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-2.5 mb-3.5">
                        @foreach(['A', 'B', 'C', 'D'] as $opt)
                            @if(isset($q->options[$opt]) && $q->options[$opt] !== '')
                                @php $isCorrect = ($q->correct_answer === $opt); @endphp
                                <div class="muk-option-display {{ $isCorrect ? 'is-correct-answer' : '' }}">
                                    <span class="w-6 h-6 rounded-md flex items-center justify-center font-bold text-xs shrink-0 {{ $isCorrect ? 'bg-emerald-600 text-white' : 'bg-slate-200 text-slate-700' }}">
                                        {{ $opt }}
                                    </span>
                                    <span class="text-xs sm:text-sm {{ $isCorrect ? 'font-semibold text-emerald-950' : 'text-slate-700' }}">
                                        {{ $q->options[$opt] }}
                                    </span>
                                    @if($isCorrect)
                                        <span class="ml-auto inline-flex items-center gap-1 text-[11px] font-bold text-emerald-700 bg-emerald-100/70 px-2 py-0.5 rounded shrink-0">
                                            <i class="fa-solid fa-check text-[10px]"></i> Kunci
                                        </span>
                                    @endif
                                </div>
                            @endif
                        @endforeach
                    </div>

                    <!-- Rubric / Pembahasan (If Any) -->
                    @if($q->rubric_guide)
                        <div class="bg-blue-50/60 border-l-3 border-blue-500 px-3.5 py-2 rounded-r-xl text-xs text-blue-900 mt-2">
                            <strong class="text-blue-950 font-bold block mb-0.5">Pembahasan / Rujukan Penilaian:</strong>
                            <span>{{ $q->rubric_guide }}</span>
                        </div>
                    @endif
                </div>
            @empty
                <!-- Modern Empty State -->
                <div class="bg-white rounded-2xl border-2 border-dashed border-slate-200/90 p-10 text-center">
                    <div class="w-14 h-14 rounded-2xl bg-blue-50 text-blue-600 border border-blue-100 flex items-center justify-center mx-auto mb-3.5 text-xl shadow-2xs">
                        <i class="fa-solid fa-clipboard-question"></i>
                    </div>
                    <h4 class="text-sm sm:text-base font-bold text-slate-800 mb-1">
                        Belum Ada Butir Soal Pilihan Ganda
                    </h4>
                    <p class="text-xs text-slate-500 max-w-md mx-auto mb-5 leading-relaxed">
                        Paket instrumen ini belum memiliki butir soal. Anda dapat menambahkannya secara manual melalui formulir di atas.
                    </p>

                    <div class="flex items-center justify-center gap-3 flex-wrap">
                        <button type="button" onclick="toggleEditMuk()" class="inline-flex items-center gap-1.5 px-4 py-2.5 rounded-xl border border-slate-200 bg-white hover:bg-slate-50 text-slate-700 font-semibold text-xs shadow-2xs transition cursor-pointer">
                            <i class="fa-solid fa-plus text-xs text-slate-500"></i>
                            <span>Tambah Manual Melalui Form di Atas</span>
                        </button>
                    </div>
                </div>
            @endforelse
        </div>

    <!-- ========================================================================= -->
    <!-- BAGIAN 2: FR.IA.06 (PERTANYAAN ESAI & RUBRIK ASESOR) -->
    <!-- ========================================================================= -->
    @elseif(in_array($codeKey, ['ia_06', 'ia06']))

        <!-- FORM TAMBAH SOAL ESAI -->
        <div class="form-tambah-soal-card bg-white rounded-2xl border border-slate-200/90 shadow-2xs overflow-hidden mb-8">
            <div class="px-6 py-4 border-b border-slate-100 bg-slate-50/50 flex items-center justify-between">
                <div class="flex items-center gap-3">
                    <div class="w-9 h-9 rounded-xl bg-indigo-50 text-indigo-600 border border-indigo-100 flex items-center justify-center font-bold">
                        <i class="fa-solid fa-pen-clip text-sm"></i>
                    </div>
                    <div>
                        <h2 class="text-base font-bold text-slate-900 tracking-tight">
                            Tambah Butir Soal Uraian / Studi Kasus Esai (FR.IA.06)
                        </h2>
                        <p class="text-xs text-slate-500">
                            Rumuskan pertanyaan studi kasus esai serta kunci jawaban rujukan standar bagi asesor.
                        </p>
                    </div>
                </div>
            </div>

            <form action="{{ route('admin.master-muk.soal.store') }}" method="POST" class="p-6">
                @csrf
                <input type="hidden" name="scheme_master_instrument_id" value="{{ $instrument->id }}">
                <input type="hidden" name="question_type" value="essay">

                <div class="mb-4">
                    <label class="block font-semibold text-xs text-slate-700 mb-1.5">
                        Relasi Elemen / KUK Standar Kompetensi
                    </label>
                    <div class="relative">
                        <select name="kuk_id" class="muk-input-field appearance-none pr-10 cursor-pointer">
                            <option value="">-- Umum / Seluruh Unit Kompetensi --</option>
                            @foreach($kukList as $k)
                                <option value="{{ $k['id'] }}">{{ $k['label'] }}</option>
                            @endforeach
                        </select>
                        <div class="absolute inset-y-0 right-0 flex items-center px-3 pointer-events-none text-slate-400">
                            <i class="fa-solid fa-chevron-down text-xs"></i>
                        </div>
                    </div>
                </div>

                <div class="mb-4">
                    <label class="block font-semibold text-xs text-slate-700 mb-1.5">
                        Teks Pertanyaan / Studi Kasus Uraian <span class="text-rose-500">*</span>
                    </label>
                    <textarea name="question_text" rows="3" class="muk-input-field leading-relaxed resize-y" placeholder="Ketik teks studi kasus atau pertanyaan esai yang menuntut penalaran teknis..." required></textarea>
                </div>

                <div class="mb-5">
                    <label class="block font-semibold text-xs text-slate-700 mb-1.5">
                        Kunci Jawaban Rujukan Standar & Rubrik Penilaian Asesor <span class="text-rose-500">*</span>
                    </label>
                    <textarea name="correct_answer" rows="3" class="muk-input-field leading-relaxed resize-y" placeholder="Tuliskan poin-poin acuan jawaban yang wajib disampaikan asesi agar dinyatakan Memuaskan (M)..." required></textarea>
                </div>

                <div class="flex items-center justify-end pt-2 border-t border-slate-100">
                    <button type="submit" class="inline-flex items-center gap-2 px-5 py-2.5 rounded-xl bg-indigo-600 hover:bg-indigo-700 active:bg-indigo-800 text-white font-semibold text-xs shadow-xs transition-all cursor-pointer">
                        <i class="fa-solid fa-floppy-disk text-xs"></i>
                        <span>Simpan Butir Soal Esai</span>
                    </button>
                </div>
            </form>
        </div>

        <!-- DAFTAR BUTIR SOAL ESAI -->
        <div class="mb-8">
            <div class="flex items-center justify-between mb-4">
                <div class="flex items-center gap-2.5">
                    <h3 class="text-base font-bold text-slate-900 tracking-tight">
                        Daftar Butir Soal Esai
                    </h3>
                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-bold bg-slate-100 text-slate-700 border border-slate-200/60">
                        {{ $instrument->questionBanks->count() }} Butir
                    </span>
                </div>
            </div>

            @if($instrument->questionBanks->count() > 0)
                <!-- Bulk Selection Toolbar -->
                <div class="p-3 mb-4 rounded-xl bg-slate-50 border border-slate-200/90 flex flex-wrap items-center justify-between gap-3 shadow-2xs">
                    <div class="flex items-center gap-3">
                        <label class="inline-flex items-center gap-2 text-xs font-semibold text-slate-700 cursor-pointer select-none">
                            <input type="checkbox" onchange="togglePilihSemuaSoal(this)" class="w-4 h-4 rounded border-slate-300 text-rose-600 focus:ring-rose-500 cursor-pointer checkbox-select-all">
                            <span>Pilih Semua Soal</span>
                        </label>
                        <span class="text-slate-300">|</span>
                        <span class="text-xs text-slate-500">
                            <span class="font-bold text-slate-800 count-terpilih">0</span> soal dipilih
                        </span>
                    </div>

                    <div>
                        <button type="button" onclick="hapusSoalTerpilih()" class="btn-hapus-bulk inline-flex items-center gap-1.5 px-3.5 py-1.5 rounded-xl bg-rose-600 hover:bg-rose-700 active:bg-rose-800 disabled:opacity-40 disabled:cursor-not-allowed text-white font-semibold text-xs shadow-2xs transition-all cursor-pointer" disabled>
                            <i class="fa-regular fa-trash-can text-xs"></i>
                            <span>Hapus Terpilih</span>
                        </button>
                    </div>
                </div>
            @endif

            @forelse($instrument->questionBanks as $q)
                <div class="muk-question-card" data-question-id="{{ $q->id }}">
                    <div class="flex items-center justify-between gap-3 mb-3">
                        <div class="flex items-center gap-2.5 flex-wrap">
                            <input type="checkbox" name="selected_questions[]" value="{{ $q->id }}" onchange="updateBulkSelectionState()" class="w-4 h-4 rounded border-slate-300 text-rose-600 focus:ring-rose-500 cursor-pointer checkbox-item-soal" title="Pilih butir soal ini">

                            <span class="w-7 h-7 rounded-lg bg-indigo-600 text-white font-mono font-bold text-xs flex items-center justify-center shadow-2xs">
                                {{ $q->order }}
                            </span>
                            @if($q->kriteriaUnjukKerja)
                                <span class="inline-flex items-center gap-1 px-2.5 py-1 rounded-lg text-[11px] font-bold text-indigo-700 bg-indigo-50 border border-indigo-200/60">
                                    <i class="fa-solid fa-tag text-[10px]"></i>
                                    <span>KUK {{ $q->kriteriaUnjukKerja->nomor_kuk }}</span>
                                </span>
                            @endif
                        </div>
                        <div>
                            <form action="{{ route('admin.master-muk.soal.destroy', $q->id) }}" method="POST" onsubmit="return konfirmasiHapus(event, this, 'butir esai #{{ $q->order }}')" class="inline btn-hapus-item">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="inline-flex items-center gap-1 px-2.5 py-1 rounded-lg bg-rose-50 hover:bg-rose-100 text-rose-600 font-semibold text-xs border border-rose-200/70 transition-all cursor-pointer">
                                    <i class="fa-regular fa-trash-can text-[11px]"></i>
                                    <span>Hapus</span>
                                </button>
                            </form>
                        </div>
                    </div>

                    <div class="text-sm sm:text-base font-semibold text-slate-800 leading-relaxed mb-3">
                        {{ $q->question_text }}
                    </div>

                    <div class="bg-emerald-50/70 border-l-3 border-emerald-500 px-3.5 py-2.5 rounded-r-xl text-xs text-emerald-900">
                        <strong class="text-emerald-950 font-bold block mb-0.5">Kunci Jawaban Rujukan & Rubrik Penilaian:</strong>
                        <span>{{ $q->correct_answer }}</span>
                    </div>
                </div>
            @empty
                <div class="bg-white rounded-2xl border-2 border-dashed border-slate-200/90 p-10 text-center">
                    <div class="w-14 h-14 rounded-2xl bg-indigo-50 text-indigo-600 border border-indigo-100 flex items-center justify-center mx-auto mb-3 text-xl shadow-2xs">
                        <i class="fa-solid fa-pen-to-square"></i>
                    </div>
                    <p class="text-sm font-semibold text-slate-600">Belum ada butir soal esai di paket ini.</p>
                </div>
            @endforelse
        </div>

    <!-- ========================================================================= -->
    <!-- BAGIAN 3: FR.IA.07 (PERTANYAAN LISAN / DPL) -->
    <!-- ========================================================================= -->
    @elseif(in_array($codeKey, ['ia_07', 'ia07']))

        <!-- FORM TAMBAH SOAL LISAN -->
        <div class="form-tambah-soal-card bg-white rounded-2xl border border-slate-200/90 shadow-2xs overflow-hidden mb-8">
            <div class="px-6 py-4 border-b border-slate-100 bg-slate-50/50 flex items-center justify-between">
                <div class="flex items-center gap-3">
                    <div class="w-9 h-9 rounded-xl bg-amber-50 text-amber-600 border border-amber-100 flex items-center justify-center font-bold">
                        <i class="fa-solid fa-comments text-sm"></i>
                    </div>
                    <div>
                        <h2 class="text-base font-bold text-slate-900 tracking-tight">
                            Tambah Butir Pertanyaan Lisan ({{ strtoupper($instrument->instrument_code) }})
                        </h2>
                        <p class="text-xs text-slate-500">
                            Pertanyaan klarifikasi lisan untuk menguji pemahaman konsep dan aspek kritis asesi.
                        </p>
                    </div>
                </div>
            </div>

            <form action="{{ route('admin.master-muk.soal.store') }}" method="POST" class="p-6">
                @csrf
                <input type="hidden" name="scheme_master_instrument_id" value="{{ $instrument->id }}">
                <input type="hidden" name="question_type" value="oral">

                <div class="mb-4">
                    <label class="block font-semibold text-xs text-slate-700 mb-1.5">
                        Relasi Elemen / KUK Standar Kompetensi
                    </label>
                    <div class="relative">
                        <select name="kuk_id" class="muk-input-field appearance-none pr-10 cursor-pointer">
                            <option value="">-- Umum / Seluruh Unit Kompetensi --</option>
                            @foreach($kukList as $k)
                                <option value="{{ $k['id'] }}">{{ $k['label'] }}</option>
                            @endforeach
                        </select>
                        <div class="absolute inset-y-0 right-0 flex items-center px-3 pointer-events-none text-slate-400">
                            <i class="fa-solid fa-chevron-down text-xs"></i>
                        </div>
                    </div>
                </div>

                <div class="mb-4">
                    <label class="block font-semibold text-xs text-slate-700 mb-1.5">
                        Butir Pertanyaan Lisan yang Diajukan Asesor <span class="text-rose-500">*</span>
                    </label>
                    <textarea name="question_text" rows="2" class="muk-input-field leading-relaxed resize-y" placeholder="Pertanyaan lisan untuk mengklarifikasi pemahaman atau aspek kritis..." required></textarea>
                </div>

                <div class="mb-5">
                    <label class="block font-semibold text-xs text-slate-700 mb-1.5">
                        Kunci Jawaban Rujukan Asesor <span class="text-rose-500">*</span>
                    </label>
                    <textarea name="correct_answer" rows="2" class="muk-input-field leading-relaxed resize-y" placeholder="Poin jawaban lisan yang diharapkan dari asesi..." required></textarea>
                </div>

                <div class="flex items-center justify-end pt-2 border-t border-slate-100">
                    <button type="submit" class="inline-flex items-center gap-2 px-5 py-2.5 rounded-xl bg-amber-600 hover:bg-amber-700 active:bg-amber-800 text-white font-semibold text-xs shadow-xs transition-all cursor-pointer">
                        <i class="fa-solid fa-floppy-disk text-xs"></i>
                        <span>Simpan Butir Pertanyaan Lisan</span>
                    </button>
                </div>
            </form>
        </div>

        <!-- DAFTAR PERTANYAAN LISAN -->
        <div class="mb-8">
            <div class="flex items-center justify-between mb-4">
                <div class="flex items-center gap-2.5">
                    <h3 class="text-base font-bold text-slate-900 tracking-tight">
                        Daftar Pertanyaan Lisan
                    </h3>
                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-bold bg-slate-100 text-slate-700 border border-slate-200/60">
                        {{ $instrument->questionBanks->count() }} Butir
                    </span>
                </div>
            </div>

            @if($instrument->questionBanks->count() > 0)
                <!-- Bulk Selection Toolbar -->
                <div class="p-3 mb-4 rounded-xl bg-slate-50 border border-slate-200/90 flex flex-wrap items-center justify-between gap-3 shadow-2xs">
                    <div class="flex items-center gap-3">
                        <label class="inline-flex items-center gap-2 text-xs font-semibold text-slate-700 cursor-pointer select-none">
                            <input type="checkbox" onchange="togglePilihSemuaSoal(this)" class="w-4 h-4 rounded border-slate-300 text-rose-600 focus:ring-rose-500 cursor-pointer checkbox-select-all">
                            <span>Pilih Semua Soal</span>
                        </label>
                        <span class="text-slate-300">|</span>
                        <span class="text-xs text-slate-500">
                            <span class="font-bold text-slate-800 count-terpilih">0</span> soal dipilih
                        </span>
                    </div>

                    <div>
                        <button type="button" onclick="hapusSoalTerpilih()" class="btn-hapus-bulk inline-flex items-center gap-1.5 px-3.5 py-1.5 rounded-xl bg-rose-600 hover:bg-rose-700 active:bg-rose-800 disabled:opacity-40 disabled:cursor-not-allowed text-white font-semibold text-xs shadow-2xs transition-all cursor-pointer" disabled>
                            <i class="fa-regular fa-trash-can text-xs"></i>
                            <span>Hapus Terpilih</span>
                        </button>
                    </div>
                </div>
            @endif

            @forelse($instrument->questionBanks as $q)
                <div class="muk-question-card" data-question-id="{{ $q->id }}">
                    <div class="flex items-center justify-between gap-3 mb-3">
                        <div class="flex items-center gap-2.5 flex-wrap">
                            <input type="checkbox" name="selected_questions[]" value="{{ $q->id }}" onchange="updateBulkSelectionState()" class="w-4 h-4 rounded border-slate-300 text-rose-600 focus:ring-rose-500 cursor-pointer checkbox-item-soal" title="Pilih butir soal ini">

                            <span class="w-7 h-7 rounded-lg bg-amber-600 text-white font-mono font-bold text-xs flex items-center justify-center shadow-2xs">
                                {{ $q->order }}
                            </span>
                            @if($q->kriteriaUnjukKerja)
                                <span class="inline-flex items-center gap-1 px-2.5 py-1 rounded-lg text-[11px] font-bold text-amber-700 bg-amber-50 border border-amber-200/60">
                                    <i class="fa-solid fa-tag text-[10px]"></i>
                                    <span>KUK {{ $q->kriteriaUnjukKerja->nomor_kuk }}</span>
                                </span>
                            @endif
                        </div>
                        <div>
                            <form action="{{ route('admin.master-muk.soal.destroy', $q->id) }}" method="POST" onsubmit="return konfirmasiHapus(event, this, 'butir pertanyaan lisan #{{ $q->order }}')" class="inline btn-hapus-item">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="inline-flex items-center gap-1 px-2.5 py-1 rounded-lg bg-rose-50 hover:bg-rose-100 text-rose-600 font-semibold text-xs border border-rose-200/70 transition-all cursor-pointer">
                                    <i class="fa-regular fa-trash-can text-[11px]"></i>
                                    <span>Hapus</span>
                                </button>
                            </form>
                        </div>
                    </div>

                    <div class="text-sm sm:text-base font-semibold text-slate-800 leading-relaxed mb-3">
                        {{ $q->question_text }}
                    </div>

                    <div class="bg-amber-50/70 border-l-3 border-amber-500 px-3.5 py-2.5 rounded-r-xl text-xs text-amber-900">
                        <strong class="text-amber-950 font-bold block mb-0.5">Kunci Jawaban Rujukan Asesor:</strong>
                        <span>{{ $q->correct_answer }}</span>
                    </div>
                </div>
            @empty
                <div class="bg-white rounded-2xl border-2 border-dashed border-slate-200/90 p-10 text-center">
                    <div class="w-14 h-14 rounded-2xl bg-amber-50 text-amber-600 border border-amber-100 flex items-center justify-center mx-auto mb-3 text-xl shadow-2xs">
                        <i class="fa-solid fa-comments"></i>
                    </div>
                    <p class="text-sm font-semibold text-slate-600">Belum ada butir pertanyaan lisan di paket ini.</p>
                </div>
            @endforelse
        </div>

    <!-- ========================================================================= -->
    <!-- BAGIAN 3B: FR.IA.03 (PERTANYAAN UNTUK MENDUKUNG OBSERVASI - PERSIS GAMBAR BNSP) -->
    <!-- ========================================================================= -->
    @elseif(in_array($codeKey, ['ia_03', 'ia03']))

        @php
            $skema = $instrument->skema;
            $units = ($skema && $skema->unitKompetensi->isNotEmpty()) 
                ? $skema->unitKompetensi 
                : ($instrument->unitKompetensi ? collect([$instrument->unitKompetensi]) : collect());
            $meta = $instrument->additional_metadata ?? [];
            if (!is_array($meta)) $meta = json_decode($meta, true) ?: [];

            $skemaNama = $skema->nama_skema ?? 'Skema Sertifikasi';
            $skemaKode = $skema->kode_skema ?? '-';
            $totalUnits = $units->count();

            // Distribusikan unit ke 3 kelompok pekerjaan sesuai template gambar BNSP
            $kelompokUnits = [1 => collect(), 2 => collect(), 3 => collect()];
            if ($totalUnits <= 1) {
                $kelompokUnits[1] = $units;
                $kelompokUnits[2] = $units;
                $kelompokUnits[3] = $units;
            } elseif ($totalUnits === 2) {
                $kelompokUnits[1] = $units->slice(0, 1);
                $kelompokUnits[2] = $units->slice(1, 1);
                $kelompokUnits[3] = $units;
            } else {
                $base = intdiv($totalUnits, 3);
                $remainder = $totalUnits % 3;
                $s1 = $base + ($remainder > 0 ? 1 : 0);
                $s2 = $base + ($remainder > 1 ? 1 : 0);
                $kelompokUnits[1] = $units->slice(0, $s1);
                $kelompokUnits[2] = $units->slice($s1, $s2);
                $kelompokUnits[3] = $units->slice($s1 + $s2);
            }

            $savedKelompokSoal = $meta['kelompok_soal'] ?? [];
            $savedUmpanBalik = $meta['umpan_balik'] ?? 'Asesi menunjukkan pemahaman yang sangat baik terhadap konsep kerja, kepatuhan K3, dan penanganan aspek kritis kejuruan.';

            $generateDefaultQ = function($kIdx, $qIdx, $unitsInGroup) use ($skemaNama) {
                $uSample = $unitsInGroup->pluck('judul_unit')->filter()->take(2)->implode(' & ') ?: $skemaNama;
                if ($qIdx === 1) {
                    return [
                        'tanya' => "Bagaimanakah Anda memastikan penerapan prosedur K3L, kesiapan peralatan kerja, serta ketaatan instruksi kerja (SOP) sebelum memulai tugas pada unit: {$uSample}?",
                        'jawab' => 'Asesi menjelaskan tahapan pemeriksaan keselamatan kerja, penggunaan APD wajib standar industri, dan kesiapan operasional peralatan sesuai SOP kejuruan.'
                    ];
                } elseif ($qIdx === 2) {
                    return [
                        'tanya' => "Tindakan teknis apa yang Anda lakukan apabila menjumpai kendala operasional, deviasi spesifikasi, atau situasi kritis saat melaksanakan pekerjaan ini?",
                        'jawab' => 'Asesi mampu mengidentifikasi sumber masalah secara tepat, menghentikan proses darurat sesuai SOP, dan mengambil tindakan korektif secara terukur dan aman.'
                    ];
                } else {
                    return [
                        'tanya' => "Bagaimana cara Anda memverifikasi bahwa dimensi hasil kerja dan standar mutu pada unit ini telah memenuhi kriteria toleransi yang disyaratkan?",
                        'jawab' => 'Asesi mendemonstrasikan metode pengukuran dan pemeriksaan kualitas hasil kerja menggunakan alat ukur presisi dan membandingkannya pada lembar standar mutu.'
                    ];
                }
            };
        @endphp

        <form action="{{ route('admin.master-muk.update-metadata', $instrument->id) }}" method="POST">
            @csrf
            
            <!-- DOKUMEN PERSIS GAMBAR BNSP -->
            <div class="dokumen-kertas-preview p-6 sm:p-10 bg-white rounded-2xl border border-slate-200 shadow-sm max-w-5xl mx-auto mb-8 text-slate-900" style="font-family: 'Segoe UI', Arial, sans-serif; font-size: 0.88rem; line-height: 1.5;">

                <!-- HEADER RESMI -->
                <div style="font-size: 0.95rem; font-weight: 800; color: #000000; margin-bottom: 0.85rem; letter-spacing: 0.3px; text-transform: uppercase;">
                    FR.IA.03. &nbsp; PERTANYAAN UNTUK MENDUKUNG OBSERVASI
                </div>

                <!-- TABEL IDENTITAS -->
                <table style="width: 100%; border-collapse: collapse; border: 1px solid #000000; margin-bottom: 0.5rem; font-size: 0.86rem;">
                    <tr>
                        <td rowspan="2" style="width: 25%; font-weight: 700; vertical-align: middle; border: 1px solid #000000; padding: 6px 10px;">
                            Skema Sertifikasi<br>
                            <span style="font-weight: normal; font-size: 0.82rem;">(KKNI/Okupasi/Klaster)</span>
                        </td>
                        <td style="width: 14%; font-weight: 600; border: 1px solid #000000; padding: 6px 10px;">Judul</td>
                        <td style="width: 2%; text-align: center; border: 1px solid #000000; padding: 6px 10px;">:</td>
                        <td style="font-weight: 700; border: 1px solid #000000; padding: 6px 10px;">{{ $skemaNama }}</td>
                    </tr>
                    <tr>
                        <td style="font-weight: 600; border: 1px solid #000000; padding: 6px 10px;">Nomor</td>
                        <td style="text-align: center; border: 1px solid #000000; padding: 6px 10px;">:</td>
                        <td style="font-weight: 700; border: 1px solid #000000; padding: 6px 10px;">{{ $skemaKode }}</td>
                    </tr>
                    <tr>
                        <td colspan="2" style="font-weight: 600; border: 1px solid #000000; padding: 6px 10px;">TUK</td>
                        <td style="text-align: center; border: 1px solid #000000; padding: 6px 10px;">:</td>
                        <td style="border: 1px solid #000000; padding: 6px 10px;">Sewaktu/Tempat Kerja/Mandiri*</td>
                    </tr>
                    <tr>
                        <td colspan="2" style="font-weight: 600; border: 1px solid #000000; padding: 6px 10px;">Nama Asesor</td>
                        <td style="text-align: center; border: 1px solid #000000; padding: 6px 10px;">:</td>
                        <td style="border: 1px solid #000000; padding: 6px 10px;"><strong>{{ auth()->user()->nama_lengkap ?? 'Asesor LSP' }}</strong></td>
                    </tr>
                    <tr>
                        <td colspan="2" style="font-weight: 600; border: 1px solid #000000; padding: 6px 10px;">Nama Asesi</td>
                        <td style="text-align: center; border: 1px solid #000000; padding: 6px 10px;">:</td>
                        <td style="border: 1px solid #000000; padding: 6px 10px;">Nama Asesi Terdaftar</td>
                    </tr>
                    <tr>
                        <td colspan="2" style="font-weight: 600; border: 1px solid #000000; padding: 6px 10px;">Tanggal</td>
                        <td style="text-align: center; border: 1px solid #000000; padding: 6px 10px;">:</td>
                        <td style="border: 1px solid #000000; padding: 6px 10px;">{{ date('d-m-Y') }}</td>
                    </tr>
                </table>
                <div style="font-size: 0.75rem; font-style: italic; color: #222222; margin-top: -0.25rem; margin-bottom: 1.25rem;">*Coret yang tidak perlu</div>

                <!-- PANDUAN BAGI ASESOR (PERSIS GAMBAR) -->
                <div style="border: 1px solid #000000; padding: 0.85rem 1.15rem; margin-bottom: 1.35rem; background-color: #ffffff;">
                    <div style="font-weight: 800; font-size: 0.88rem; color: #000000; margin-bottom: 0.45rem; text-transform: uppercase;">
                        PANDUAN BAGI ASESOR
                    </div>
                    <ul style="margin: 0; padding-left: 1.2rem; font-size: 0.84rem; color: #000000; line-height: 1.6;">
                        <li style="margin-bottom: 0.3rem;">Formulir ini di isi oleh asesor kompetensi dapat sebelum, pada saat atau setelah melakukan asesmen dengan metode observasi demonstrasi.</li>
                        <li style="margin-bottom: 0.3rem;">Pertanyaan dibuat dengan tujuan untuk menggali, dapat berisi pertanyaan yang berkaitan dengan dimensi kompetensi, batasan variabel dan aspek kritis yang relevan dengan skenario tugas dan praktik demonstrasi.</li>
                        <li style="margin-bottom: 0.3rem;">Jika pertanyaan disampaikan sebelum asesi melakukan praktik demonstrasi, maka pertanyaan dibuat berkaitan dengan aspek K3L, SOP, penggunaan peralatan dan perlengkapan.</li>
                        <li style="margin-bottom: 0.3rem;">Jika setelah asesi melakukan praktik demonstrasi terdapat item pertanyaan pendukung observasi telah terpenuhi, maka pertanyaan tersebut tidak perlu ditanyakan lagi dan cukup memberi catatan bahwa sudah terpenuhi pada saat tugas praktek demonstrasi pada kolom tanggapan</li>
                        <li style="margin-bottom: 0.3rem;">Jika pada saat observasi ada hal yang perlu dikonfirmasi sedangkan di instrumen daftar pertanyaan pendukung observasi tidak ada, maka asesor dapat memberikan pertanyaan dengan syarat pertanyaan harus berkaitan dengan tugas praktek demonstrasi. Jika dilakukan, asesor harus mencatat dalam instrumen pertanyaan pendukung observasi.</li>
                        <li style="margin-bottom: 0.3rem;">Tanggapan asesi ditulis pada kolom tanggapan.</li>
                    </ul>
                </div>

                <!-- 3 KELOMPOK PEKERJAAN & TABEL PERTANYAAN (PERSIS GAMBAR) -->
                @for($k = 1; $k <= 3; $k++)
                    @php
                        $unitsInK = $kelompokUnits[$k]->values();
                        $maxRows = max($unitsInK->count(), 3);
                        $leftColRowspan = $maxRows + 2;
                    @endphp

                    @if($k === 2 || $k === 3)
                        <div style="border-top: 2px dashed #cbd5e1; margin: 2rem 0 1.5rem 0; text-align: center; position: relative;">
                            <span style="background: #ffffff; padding: 0 12px; font-size: 0.75rem; color: #64748b; font-style: italic; position: relative; top: -10px;">
                                Halaman {{ $k }} (Standar Dokumen BNSP)
                            </span>
                        </div>
                    @endif

                    <!-- TABEL KELOMPOK PEKERJAAN X -->
                    <table style="width: 100%; border-collapse: collapse; border: 1px solid #000000; margin-bottom: 0.85rem; font-size: 0.86rem;">
                        <tbody>
                            <tr>
                                <td rowspan="{{ $leftColRowspan }}" style="width: 25%; text-align: center; vertical-align: middle; font-weight: 700; font-size: 0.95rem; border: 1px solid #000000; padding: 10px; background-color: #ffffff;">
                                    Kelompok<br>Pekerjaan {{ $k }}
                                </td>
                                <th style="width: 8%; text-align: center; border: 1px solid #000000; padding: 6px 4px; font-weight: 700; background-color: #ffffff;">No.</th>
                                <th style="width: 28%; text-align: center; border: 1px solid #000000; padding: 6px 8px; font-weight: 700; background-color: #ffffff;">Kode Unit</th>
                                <th style="text-align: center; border: 1px solid #000000; padding: 6px 8px; font-weight: 700; background-color: #ffffff;">Judul Unit</th>
                            </tr>

                            @for($i = 0; $i < $maxRows; $i++)
                                @php $u = $unitsInK->get($i); @endphp
                                <tr>
                                    <td style="text-align: center; font-weight: 700; border: 1px solid #000000; padding: 6px 4px;">{{ $i + 1 }}.</td>
                                    <td style="font-weight: 600; border: 1px solid #000000; padding: 6px 8px; font-family: monospace;">
                                        {{ $u ? $u->kode_unit : '' }}
                                    </td>
                                    <td style="font-weight: 600; border: 1px solid #000000; padding: 6px 8px;">
                                        {{ $u ? ($u->nama_unit ?? $u->judul_unit) : '' }}
                                    </td>
                                </tr>
                            @endfor

                            <tr>
                                <td style="text-align: center; font-weight: 700; border: 1px solid #000000; padding: 6px 4px;">Dst..</td>
                                <td style="border: 1px solid #000000; padding: 6px 8px;"></td>
                                <td style="border: 1px solid #000000; padding: 6px 8px;"></td>
                            </tr>
                        </tbody>
                    </table>

                    <!-- TABEL PERTANYAAN -->
                    <table style="width: 100%; border-collapse: collapse; border: 1px solid #000000; margin-bottom: 1.35rem; font-size: 0.86rem;">
                        <thead>
                            <tr>
                                <th colspan="2" rowspan="2" style="text-align: center; font-weight: 700; vertical-align: middle; border: 1px solid #000000; padding: 6px 8px;">
                                    Pertanyaan
                                </th>
                                <th colspan="2" style="width: 14%; text-align: center; font-weight: 700; border: 1px solid #000000; padding: 6px 8px;">
                                    Pencapaian
                                </th>
                            </tr>
                            <tr>
                                <th style="width: 7%; text-align: center; font-weight: 700; border: 1px solid #000000; padding: 4px 6px;">Ya</th>
                                <th style="width: 7%; text-align: center; font-weight: 700; border: 1px solid #000000; padding: 4px 6px;">Tdk</th>
                            </tr>
                        </thead>
                        <tbody>
                            @for($q = 1; $q <= 3; $q++)
                                @php
                                    $defQ = $generateDefaultQ($k, $q, $unitsInK);
                                    $valPertanyaan = $savedKelompokSoal[$k][$q]['pertanyaan'] ?? $defQ['tanya'];
                                    $valTanggapan = $savedKelompokSoal[$k][$q]['tanggapan'] ?? $defQ['jawab'];
                                @endphp
                                <tr>
                                    <td style="width: 5%; text-align: center; font-weight: 700; vertical-align: top; border: 1px solid #000000; border-bottom: none; padding: 6px 4px;">
                                        {{ $q }}.
                                    </td>
                                    <td style="vertical-align: top; border: 1px solid #000000; border-bottom: none; padding: 6px 8px;">
                                        <textarea name="metadata_kelompok_soal[{{ $k }}][{{ $q }}][pertanyaan]" class="w-full bg-slate-50 border border-slate-300 rounded p-1.5 text-xs text-slate-900 font-semibold focus:bg-white focus:border-slate-800 outline-none" rows="2" placeholder="Tuliskan pertanyaan pendukung observasi...">{{ $valPertanyaan }}</textarea>
                                    </td>
                                    <td style="border: 1px solid #000000; border-bottom: none;"></td>
                                    <td style="border: 1px solid #000000; border-bottom: none;"></td>
                                </tr>
                                <tr>
                                    <td style="border: 1px solid #000000; border-top: none;"></td>
                                    <td style="vertical-align: top; border: 1px solid #000000; border-top: none; padding: 2px 8px 8px 8px;">
                                        <div style="font-weight: 700; font-size: 0.85rem; color: #000000; margin-bottom: 0.2rem;">
                                            Tanggapan:
                                        </div>
                                        <textarea name="metadata_kelompok_soal[{{ $k }}][{{ $q }}][tanggapan]" class="w-full bg-slate-50 border border-slate-300 rounded p-1.5 text-xs text-slate-800 focus:bg-white focus:border-slate-800 outline-none" rows="2" placeholder="Tuliskan panduan tanggapan / respons yang diharapkan dari asesi...">{{ $valTanggapan }}</textarea>
                                    </td>
                                    <td style="text-align: center; vertical-align: middle; border: 1px solid #000000; border-top: none;">
                                        <input type="checkbox" checked disabled style="width: 16px; height: 16px; accent-color: #000000;">
                                    </td>
                                    <td style="text-align: center; vertical-align: middle; border: 1px solid #000000; border-top: none;">
                                        <input type="checkbox" disabled style="width: 16px; height: 16px;">
                                    </td>
                                </tr>
                            @endfor
                        </tbody>
                    </table>
                @endfor

                <!-- UMPAN BALIK UNTUK ASESI -->
                <div style="border: 1px solid #000000; padding: 0.85rem 1rem; margin-bottom: 1.35rem; background-color: #ffffff;">
                    <label style="font-weight: 700; font-size: 0.88rem; color: #000000; margin-bottom: 0.4rem; display: block;">
                        Umpan balik untuk asesi:
                    </label>
                    <textarea name="metadata_umpan_balik" class="w-full bg-slate-50 border border-slate-300 rounded p-2 text-xs text-slate-800 focus:bg-white focus:border-slate-800 outline-none" rows="3" placeholder="Tuliskan standar catatan umpan balik untuk asesi...">{{ $savedUmpanBalik }}</textarea>
                </div>

                <!-- TABEL TANDA TANGAN ASESI & ASESOR -->
                <table style="width: 100%; border-collapse: collapse; border: 1px solid #000000; margin-top: 1.25rem; font-size: 0.86rem;">
                    <tr>
                        <td colspan="3" style="font-weight: 700; background-color: #ffffff; border: 1px solid #000000; padding: 6px 10px;">Asesi :</td>
                    </tr>
                    <tr>
                        <td style="width: 25%; font-weight: 600; border: 1px solid #000000; padding: 6px 10px;">Nama</td>
                        <td style="width: 2%; text-align: center; border: 1px solid #000000; padding: 6px 10px;">:</td>
                        <td style="font-weight: 700; border: 1px solid #000000; padding: 6px 10px;">Nama Asesi Terdaftar</td>
                    </tr>
                    <tr>
                        <td style="font-weight: 600; vertical-align: top; border: 1px solid #000000; padding: 6px 10px;">Tanda tangan dan Tanggal</td>
                        <td style="text-align: center; vertical-align: top; border: 1px solid #000000; padding: 6px 10px;">:</td>
                        <td style="min-height: 45px; vertical-align: middle; border: 1px solid #000000; padding: 6px 10px; color: #64748b; font-style: italic;">
                            (Area Tanda Tangan Digital & Tanggal Asesi)
                        </td>
                    </tr>
                    <tr>
                        <td colspan="3" style="font-weight: 700; background-color: #ffffff; border: 1px solid #000000; padding: 6px 10px;">Asesor :</td>
                    </tr>
                    <tr>
                        <td style="font-weight: 600; border: 1px solid #000000; padding: 6px 10px;">Nama</td>
                        <td style="text-align: center; border: 1px solid #000000; padding: 6px 10px;">:</td>
                        <td style="font-weight: 700; border: 1px solid #000000; padding: 6px 10px;">{{ auth()->user()->nama_lengkap ?? 'Asesor LSP' }}</td>
                    </tr>
                    <tr>
                        <td style="font-weight: 600; border: 1px solid #000000; padding: 6px 10px;">No. Reg</td>
                        <td style="text-align: center; border: 1px solid #000000; padding: 6px 10px;">:</td>
                        <td style="border: 1px solid #000000; padding: 6px 10px;">{{ auth()->user()->nomor_registrasi ?? 'MET.000.004455.2023' }}</td>
                    </tr>
                    <tr>
                        <td style="font-weight: 600; vertical-align: top; border: 1px solid #000000; padding: 6px 10px;">Tanda tangan dan Tanggal</td>
                        <td style="text-align: center; vertical-align: top; border: 1px solid #000000; padding: 6px 10px;">:</td>
                        <td style="min-height: 45px; vertical-align: middle; border: 1px solid #000000; padding: 6px 10px;">
                            <span style="font-weight: 600; color: #0f172a;">{{ date('d-m-Y') }}</span>
                        </td>
                    </tr>
                </table>

                <!-- FOOTER RESMI BNSP -->
                <div style="font-size: 0.72rem; color: #333333; margin-top: 0.5rem; font-style: italic; line-height: 1.4;">
                    Diadaptasi dari template yang disediakan di Departemen Pendidikan dan Pelatihan, Australia, Merancang instrumen asesmen untuk hasil yang berkualitas di VET, 2008 di VET, 2008
                </div>
            </div>

            <!-- TOMBOL SIMPAN MASTER FR.IA.03 STICKY / FLOATING ACTION BAR -->
            <div class="sticky bottom-6 z-30 max-w-5xl mx-auto flex items-center justify-between bg-slate-900/95 backdrop-blur-md text-white px-6 py-4 rounded-2xl shadow-xl border border-slate-700/60">
                <div>
                    <div class="font-bold text-sm text-white">Kelola Master Instrumen FR.IA.03</div>
                    <div class="text-xs text-slate-300">Simpan susunan pertanyaan dan panduan tanggapan ke bank instrumen skema.</div>
                </div>
                <div class="flex items-center gap-3">
                    <a href="{{ route('admin.master-muk.index', ['skema_id' => $instrument->skema_id]) }}" class="px-4 py-2 rounded-xl bg-slate-800 hover:bg-slate-700 text-slate-200 text-xs font-semibold border border-slate-600 transition-all">
                        Kembali
                    </a>
                    <button type="submit" class="px-5 py-2 rounded-xl bg-amber-600 hover:bg-amber-500 text-white text-xs font-bold shadow-md transition-all flex items-center gap-2 cursor-pointer">
                        <i class="fa-solid fa-floppy-disk"></i>
                        <span>Simpan Perubahan Master FR.IA.03</span>
                    </button>
                </div>
            </div>
        </form>

    <!-- ========================================================================= -->
    <!-- ========================================================================= -->
    <!-- BAGIAN 4: FR.IA.02 (TUGAS PRAKTIK DEMONSTRASI - SESUAI GAMBAR DOKUMEN BNSP) -->
    <!-- ========================================================================= -->
    @elseif(in_array($codeKey, ['ia_02', 'ia02']))

        @php
            $skema = $instrument->skema;
            $units = ($skema && $skema->unitKompetensi->isNotEmpty()) 
                ? $skema->unitKompetensi 
                : ($instrument->unitKompetensi ? collect([$instrument->unitKompetensi]) : collect());
            $meta = $instrument->additional_metadata ?? [];
            if (!is_array($meta)) $meta = json_decode($meta, true) ?: [];

            $skemaNama = $skema->nama_skema ?? 'Skema Sertifikasi';
            $unitTitles = $units->pluck('judul_unit')->filter()->values();

            // Default Skenario dinamis dari database unit kompetensi jika belum disimpan khusus
            $defaultScenario = "Anda ditugaskan untuk mendemonstrasikan tugas praktik kerja pada skema {$skemaNama}, mencakup unit kompetensi: "
                . ($unitTitles->isNotEmpty() ? $unitTitles->map(fn($t, $i) => ($i + 1) . '. ' . $t)->implode('; ') : 'sesuai unit kompetensi yang dipersyaratkan')
                . " dengan mengacu kepada Standar Operasional Prosedur (SOP), Instruksi Kerja (WI), dan Kriteria Unjuk Kerja (KUK) yang berlaku.";

            // Default Perlengkapan & Bahan dinamis dari skema database
            $defaultTools = "Peralatan kerja, mesin/alat uji, instrumen, bahan kerja, serta Alat Pelindung Diri (APD) standar yang dipersyaratkan untuk pelaksanaan demonstrasi unit kompetensi pada skema {$skemaNama}.";

            $scenario = $meta['scenario'] ?? ($meta['skenario'] ?? $defaultScenario);
            $tools = $meta['tools_equipment'] ?? ($meta['peralatan_bahan'] ?? $defaultTools);
            $durasiWaktu = $instrument->time_limit_minutes ? ($instrument->time_limit_minutes . ' Menit') : ($meta['durasi_waktu'] ?? '120 Menit');
            $kelompokSplit = (int)($meta['kelompok_split'] ?? 0);
            
            $savedPV = $meta['penyusun_validator'] ?? [];
            $savedKelompok = $meta['kelompok_skenario'] ?? [];

            // Pembagian Kelompok Pekerjaan dinamis dari unit kompetensi skema
            if ($kelompokSplit > 0 && $units->count() > $kelompokSplit) {
                $kelompokList = [
                    1 => $units->slice(0, $kelompokSplit),
                    2 => $units->slice($kelompokSplit),
                ];
            } elseif ($units->count() > 4) {
                $half = (int) ceil($units->count() / 2);
                $kelompokList = [
                    1 => $units->slice(0, $half),
                    2 => $units->slice($half),
                ];
            } else {
                $kelompokList = [
                    1 => $units,
                ];
            }

            $asesorNama = auth()->user()->nama_lengkap ?? 'Asesor LSP';
            $asesorMet = auth()->user()->nomor_registrasi ?? ($savedPV['penyusun_1_met'] ?? 'MET.000.004455.2023');
            $asesorTtd = auth()->user()->tanda_tangan ?? null;
        @endphp

        <form action="{{ route('admin.master-muk.update-metadata', $instrument->id) }}" method="POST">
            @csrf

            <!-- BAR KONTROL & PENGATURAN CEPAT -->
            <div class="bg-white rounded-2xl border border-slate-200/90 shadow-2xs p-5 mb-6">
                <div class="flex flex-col lg:flex-row lg:items-center justify-between gap-4 pb-4 border-b border-slate-100">
                    <div class="flex items-center gap-3">
                        <div class="w-10 h-10 rounded-xl bg-violet-50 text-violet-600 border border-violet-100 flex items-center justify-center font-bold">
                            <i class="fa-solid fa-file-contract text-base"></i>
                        </div>
                        <div>
                            <h2 class="text-base sm:text-lg font-bold text-slate-900 tracking-tight">
                                Formulir FR.IA.02 - Tugas Praktik Demonstrasi (TPD)
                            </h2>
                            <p class="text-xs text-slate-500">
                                Template resmi BNSP untuk instrumen Tugas Praktik Demonstrasi pada skema <strong>{{ $instrument->skema->nama_skema ?? '-' }}</strong>.
                            </p>
                        </div>
                    </div>

                    <div class="flex items-center gap-2.5 flex-wrap">
                        <a href="{{ route('formulir.ia02', ['skema_id' => $instrument->skema_id]) }}" target="_blank" class="inline-flex items-center gap-1.5 px-3.5 py-2 rounded-xl bg-slate-100 hover:bg-slate-200 text-slate-700 text-xs font-semibold transition">
                            <i class="fa-solid fa-arrow-up-right-from-square text-[11px]"></i>
                            <span>Buka Pratinjau Asesi</span>
                        </a>

                        <button type="submit" class="inline-flex items-center gap-2 px-5 py-2 rounded-xl bg-violet-600 hover:bg-violet-700 active:bg-violet-800 text-white font-semibold text-xs shadow-xs transition cursor-pointer btn-submit-metadata">
                            <i class="fa-solid fa-floppy-disk text-xs"></i>
                            <span>Simpan Formulir FR.IA.02</span>
                        </button>
                    </div>
                </div>

                <!-- Parameter Ringkas -->
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 pt-4 text-xs">
                    <div>
                        <label class="block font-semibold text-slate-700 mb-1">
                            Durasi Waktu Praktik (Menit)
                        </label>
                        <div class="relative">
                            <input type="number" name="time_limit_minutes" value="{{ $instrument->time_limit_minutes ?? 120 }}" min="1" max="1440" class="muk-input-field input-metadata-muk pr-14 font-bold text-slate-800">
                            <span class="absolute inset-y-0 right-0 flex items-center px-3 text-slate-400 font-semibold pointer-events-none">
                                Menit
                            </span>
                        </div>
                    </div>

                    <div>
                        <label class="block font-semibold text-slate-700 mb-1">
                            Pembagian Kelompok Pekerjaan
                        </label>
                        <select name="metadata_kelompok_split" class="muk-input-field input-metadata-muk">
                            <option value="0" {{ $kelompokSplit == 0 ? 'selected' : '' }}>Semua Unit pada Kelompok Pekerjaan 1</option>
                            @if($units->count() > 1)
                                @for($i = 1; $i < $units->count(); $i++)
                                    <option value="{{ $i }}" {{ $kelompokSplit == $i ? 'selected' : '' }}>
                                        Bagi 2 Kelompok: Unit 1-{{ $i }} (Kelompok 1) & Unit {{ $i + 1 }}-{{ $units->count() }} (Kelompok 2)
                                    </option>
                                @endfor
                            @endif
                        </select>
                    </div>
                </div>
            </div>

            <!-- ========================================================================= -->
            <!-- LEMBAR FORMULIR ASLI BNSP (SESUAI GAMBAR DOKUMEN FR.IA.02 PERSIS) -->
            <!-- ========================================================================= -->
            <div class="dokumen-ia02-master" style="background-color: #ffffff; color: #000000; font-family: 'Segoe UI', Arial, sans-serif; font-size: 0.88rem; line-height: 1.5; padding: 2.5rem 3rem; margin: 0 auto 2.5rem auto; max-width: 950px; box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.08); border: 1px solid #cbd5e1;">

                <!-- ====================================================================
                     HALAMAN 1: IDENTITAS, PETUNJUK, & KELOMPOK PEKERJAAN 1
                     ==================================================================== -->
                <div style="font-size: 0.95rem; font-weight: 800; color: #000000; margin-bottom: 0.85rem; letter-spacing: 0.3px; text-transform: uppercase;">
                    FR.IA.02. &nbsp; TPD - TUGAS PRAKTIK DEMONSTRASI
                </div>

                <!-- TABEL IDENTITAS (PERSIS GAMBAR) -->
                <table style="width: 100%; border-collapse: collapse; border: 1px solid #000000; margin-bottom: 0.5rem; font-size: 0.86rem;">
                    <tr>
                        <td rowspan="2" style="width: 25%; font-weight: 700; vertical-align: middle; border: 1px solid #000000; padding: 6px 10px;">
                            Skema Sertifikasi<br>
                            <span style="font-weight: normal; font-size: 0.82rem;">(KKNI/Okupasi/Klaster)</span>
                        </td>
                        <td style="width: 14%; font-weight: 600; border: 1px solid #000000; padding: 6px 10px;">Judul</td>
                        <td style="width: 2%; text-align: center; border: 1px solid #000000; padding: 6px 4px;">:</td>
                        <td style="font-weight: 700; border: 1px solid #000000; padding: 6px 10px;">{{ $instrument->skema->nama_skema ?? '-' }}</td>
                    </tr>
                    <tr>
                        <td style="font-weight: 600; border: 1px solid #000000; padding: 6px 10px;">Nomor</td>
                        <td style="text-align: center; border: 1px solid #000000; padding: 6px 4px;">:</td>
                        <td style="font-weight: 700; border: 1px solid #000000; padding: 6px 10px;">{{ $instrument->skema->kode_skema ?? '-' }}</td>
                    </tr>
                    <tr>
                        <td colspan="2" style="font-weight: 600; border: 1px solid #000000; padding: 6px 10px;">TUK</td>
                        <td style="text-align: center; border: 1px solid #000000; padding: 6px 4px;">:</td>
                        <td style="border: 1px solid #000000; padding: 6px 10px;">Sewaktu/Tempat Kerja/Mandiri*</td>
                    </tr>
                    <tr>
                        <td colspan="2" style="font-weight: 600; border: 1px solid #000000; padding: 6px 10px;">Nama Asesor</td>
                        <td style="text-align: center; border: 1px solid #000000; padding: 6px 4px;">:</td>
                        <td style="border: 1px solid #000000; padding: 6px 10px;"><strong>{{ $asesorNama }}</strong></td>
                    </tr>
                    <tr>
                        <td colspan="2" style="font-weight: 600; border: 1px solid #000000; padding: 6px 10px;">Nama Asesi</td>
                        <td style="text-align: center; border: 1px solid #000000; padding: 6px 4px;">:</td>
                        <td style="border: 1px solid #000000; padding: 6px 10px; font-style: italic; color: #475569;">(Disesuaikan pada sesi asesmen)</td>
                    </tr>
                    <tr>
                        <td colspan="2" style="font-weight: 600; border: 1px solid #000000; padding: 6px 10px;">Tanggal</td>
                        <td style="text-align: center; border: 1px solid #000000; padding: 6px 4px;">:</td>
                        <td style="border: 1px solid #000000; padding: 6px 10px;">{{ date('d-m-Y') }}</td>
                    </tr>
                </table>
                <div style="font-size: 0.75rem; font-style: italic; color: #222222; margin-top: -0.25rem; margin-bottom: 1.25rem;">*Coret yang tidak perlu</div>

                <!-- A. PETUNJUK (PERSIS GAMBAR) -->
                <div style="font-weight: 800; font-size: 0.92rem; color: #000000; margin-top: 1.25rem; margin-bottom: 0.4rem;">A. Petunjuk</div>
                <ol style="margin: 0 0 1.25rem 0; padding-left: 1.35rem; font-size: 0.86rem; color: #000000; line-height: 1.6;">
                    <li>Baca dan pelajari setiap instruksi kerja di bawah ini dengan cermat sebelum melaksanakan praktek</li>
                    <li>Klarifikasi kepada asesor kompetensi apabila ada hal-hal yang belum jelas</li>
                    <li>Laksanakan pekerjaan sesuai dengan urutan proses yang sudah ditetapkan</li>
                    <li>Seluruh proses kerja mengacu kepada SOP/WI yang dipersyaratkan (Jika Ada)</li>
                </ol>

                <!-- B. SKENARIO TUGAS PRAKTIK DEMONSTRASI (PERSIS GAMBAR) -->
                <div style="font-weight: 800; font-size: 0.92rem; color: #000000; margin-top: 1.25rem; margin-bottom: 0.4rem;">B. Skenario Tugas Praktik Demonstrasi</div>

                @foreach($kelompokList as $kIndex => $unitsInGroup)
                    @php
                        $kSkenario = $savedKelompok[$kIndex]['skenario'] ?? ($kIndex === 1 ? $scenario : 'Demonstrasikan seluruh proses kerja teknis pada kelompok pekerjaan ' . $kIndex . ' sesuai SOP yang berlaku.');
                        $kPeralatan = $savedKelompok[$kIndex]['peralatan'] ?? ($kIndex === 1 ? $tools : 'Peralatan dan instrumen kerja standar unit kompetensi.');
                        $kWaktu = $savedKelompok[$kIndex]['waktu'] ?? ($kIndex === 1 ? $durasiWaktu : '120 Menit');
                        
                        $displayUnits = $unitsInGroup->values();
                        $maxRows = max($displayUnits->count(), 3);
                        $leftColRowspan = $maxRows + 2;
                    @endphp

                    @if($kIndex > 1)
                        <!-- PEMBATAS HALAMAN / KELOMPOK BERIKUTNYA -->
                        <div style="border-top: 2px dashed #cbd5e1; margin: 2.5rem 0 2rem 0; position: relative;">
                            <span style="position: absolute; top: -10px; left: 50%; transform: translateX(-50%); background: #ffffff; padding: 0 10px; font-size: 0.72rem; color: #64748b; font-style: italic;">
                                Halaman 2 (Kelompok Pekerjaan Lanjutan)
                            </span>
                        </div>
                    @endif

                    <!-- TABEL KELOMPOK PEKERJAAN (BENTUK PERSIS GAMBAR BNSP) -->
                    <table style="width: 100%; border-collapse: collapse; border: 1px solid #000000; margin-bottom: 0.85rem; font-size: 0.86rem;">
                        <tbody>
                            <!-- Baris 1: Kolom Kiri "Kelompok Pekerjaan X" menyatu dari atas + Header No/Kode/Judul -->
                            <tr>
                                <td rowspan="{{ $leftColRowspan }}" style="width: 25%; text-align: center; vertical-align: middle; font-weight: 700; font-size: 0.95rem; border: 1px solid #000000; padding: 10px; background-color: #ffffff;">
                                    Kelompok<br>Pekerjaan {{ $kIndex }}
                                </td>
                                <th style="width: 8%; text-align: center; border: 1px solid #000000; padding: 6px 4px; font-weight: 700; background-color: #ffffff;">No.</th>
                                <th style="width: 28%; text-align: center; border: 1px solid #000000; padding: 6px 8px; font-weight: 700; background-color: #ffffff;">Kode Unit</th>
                                <th style="text-align: center; border: 1px solid #000000; padding: 6px 8px; font-weight: 700; background-color: #ffffff;">Judul Unit</th>
                            </tr>

                            <!-- Baris 1, 2, 3.. Unit Kompetensi -->
                            @for($i = 0; $i < $maxRows; $i++)
                                @php $u = $displayUnits->get($i); @endphp
                                <tr>
                                    <td style="text-align: center; font-weight: 700; border: 1px solid #000000; padding: 6px 4px;">{{ $i + 1 }}.</td>
                                    <td style="font-weight: 600; border: 1px solid #000000; padding: 6px 8px;">
                                        {{ $u ? $u->kode_unit : '' }}
                                    </td>
                                    <td style="font-weight: 600; border: 1px solid #000000; padding: 6px 8px;">
                                        {{ $u ? $u->judul_unit : '' }}
                                    </td>
                                </tr>
                            @endfor

                            <!-- Baris Dst.. (Persis Gambar) -->
                            <tr>
                                <td style="text-align: center; font-weight: 700; border: 1px solid #000000; padding: 6px 4px;">Dst..</td>
                                <td style="border: 1px solid #000000; padding: 6px 8px;"></td>
                                <td style="border: 1px solid #000000; padding: 6px 8px;"></td>
                            </tr>
                        </tbody>
                    </table>

                    <!-- ISIAN SKENARIO TUGAS PRAKTIK DEMONSTRASI (PERSIS GAMBAR) -->
                    <label style="font-weight: 700; font-size: 0.88rem; color: #000000; margin-top: 0.75rem; margin-bottom: 0.35rem; display: block;">
                        Skenario Tugas Praktik Demonstrasi:
                    </label>
                    <textarea name="metadata_kelompok_skenario[{{ $kIndex }}][skenario]" rows="4" class="muk-input-field input-metadata-muk" style="width: 100%; border: 1px solid #94a3b8; border-radius: 2px; padding: 0.5rem 0.65rem; font-size: 0.85rem; line-height: 1.45; margin-bottom: 0.75rem;" placeholder="Tuliskan skenario tugas praktik demonstrasi yang harus dilaksanakan oleh asesi...">{{ $kSkenario }}</textarea>
                    
                    @if($kIndex === 1)
                        <input type="hidden" name="metadata_scenario" value="{{ $kSkenario }}">
                    @endif

                    <!-- PERLENGKAPAN, PERALATAN, DAN WAKTU (PERSIS GAMBAR) -->
                    <div style="margin-top: 0.5rem; margin-bottom: 1.5rem; display: flex; flex-direction: column; gap: 0.65rem;">
                        <div style="display: flex; align-items: flex-start; gap: 0.75rem;">
                            <div style="width: 220px; font-weight: 700; color: #000000; font-size: 0.88rem; flex-shrink: 0; padding-top: 0.25rem;">
                                Perlengkapan dan Peralatan :
                            </div>
                            <div style="flex-grow: 1;">
                                <textarea name="metadata_kelompok_skenario[{{ $kIndex }}][peralatan]" rows="3" class="muk-input-field input-metadata-muk" style="width: 100%; border: 1px solid #94a3b8; border-radius: 2px; padding: 0.5rem 0.65rem; font-size: 0.85rem; line-height: 1.45;" placeholder="Sebutkan perlengkapan kerja, bahan uji, APD, dan peralatan yang digunakan...">{{ $kPeralatan }}</textarea>
                                @if($kIndex === 1)
                                    <input type="hidden" name="metadata_tools" value="{{ $kPeralatan }}">
                                @endif
                            </div>
                        </div>

                        <div style="display: flex; align-items: center; gap: 0.75rem;">
                            <div style="width: 220px; font-weight: 700; color: #000000; font-size: 0.88rem; flex-shrink: 0;">
                                {{ $kIndex === 1 ? 'Durasi Waktu :' : 'Waktu :' }}
                            </div>
                            <div style="flex-grow: 1;">
                                <input type="text" name="metadata_kelompok_skenario[{{ $kIndex }}][waktu]" value="{{ $kWaktu }}" class="muk-input-field input-metadata-muk" style="max-width: 260px; border: 1px solid #94a3b8; border-radius: 2px; padding: 0.35rem 0.6rem; font-size: 0.85rem;" placeholder="Contoh: 120 Menit">
                            </div>
                        </div>
                    </div>
                @endforeach

                <!-- ====================================================================
                     HALAMAN 3: ASESI, ASESOR, & PENYUSUN DAN VALIDATOR (PERSIS GAMBAR)
                     ==================================================================== -->
                <div style="border-top: 2px dashed #cbd5e1; margin: 2.5rem 0 2rem 0; position: relative;">
                    <span style="position: absolute; top: -10px; left: 50%; transform: translateX(-50%); background: #ffffff; padding: 0 10px; font-size: 0.72rem; color: #64748b; font-style: italic;">
                        Halaman 3 (Pengesahan & Validator)
                    </span>
                </div>

                <!-- TABEL PENGESAHAN ASESI & ASESOR (FORMAT VERTIKAL PERSIS GAMBAR HALAMAN 3) -->
                <table style="width: 100%; border-collapse: collapse; border: 1px solid #000000; margin-bottom: 2rem; font-size: 0.86rem;">
                    <!-- SEKSI ASESI -->
                    <tr>
                        <th colspan="3" style="text-align: left; font-weight: 800; padding: 6px 10px; font-size: 0.9rem; background: #ffffff; border: 1px solid #000000;">
                            ASESI :
                        </th>
                    </tr>
                    <tr>
                        <td style="width: 26%; font-weight: 600; padding: 6px 10px; border: 1px solid #000000;">Nama</td>
                        <td style="width: 3%; text-align: center; border: 1px solid #000000;">:</td>
                        <td style="padding: 6px 10px; border: 1px solid #000000; font-style: italic; color: #475569;">
                            (Diisi oleh asesi pada saat pelaksanaan demonstrasi)
                        </td>
                    </tr>
                    <tr>
                        <td style="font-weight: 600; padding: 6px 10px; vertical-align: top; border: 1px solid #000000;">
                            Tanda tangan dan Tanggal
                        </td>
                        <td style="text-align: center; vertical-align: top; padding-top: 10px; border: 1px solid #000000;">:</td>
                        <td style="padding: 8px 10px; height: 75px; vertical-align: middle; border: 1px solid #000000; color: #94a3b8; font-style: italic; font-size: 0.82rem;">
                            (Tanda tangan asesi pada lembar asesmen)
                        </td>
                    </tr>

                    <!-- SEKSI ASESOR -->
                    <tr>
                        <th colspan="3" style="text-align: left; font-weight: 800; padding: 6px 10px; font-size: 0.9rem; background: #ffffff; border: 1px solid #000000;">
                            ASESOR :
                        </th>
                    </tr>
                    <tr>
                        <td style="font-weight: 600; padding: 6px 10px; border: 1px solid #000000;">Nama</td>
                        <td style="text-align: center; border: 1px solid #000000;">:</td>
                        <td style="padding: 6px 10px; border: 1px solid #000000;">
                            <strong>{{ $asesorNama }}</strong>
                        </td>
                    </tr>
                    <tr>
                        <td style="font-weight: 600; padding: 6px 10px; border: 1px solid #000000;">No. Reg</td>
                        <td style="text-align: center; border: 1px solid #000000;">:</td>
                        <td style="padding: 6px 10px; border: 1px solid #000000;">
                            <strong>{{ $asesorMet }}</strong>
                        </td>
                    </tr>
                    <tr>
                        <td style="font-weight: 600; padding: 6px 10px; vertical-align: top; border: 1px solid #000000;">
                            Tanda tangan dan Tanggal
                        </td>
                        <td style="text-align: center; vertical-align: top; padding-top: 10px; border: 1px solid #000000;">:</td>
                        <td style="padding: 8px 10px; height: 75px; vertical-align: middle; border: 1px solid #000000;">
                            @if(!empty($asesorTtd))
                                <img src="{{ asset($asesorTtd) }}" alt="Tanda Tangan Asesor" style="max-height: 48px; display: block; margin-bottom: 4px;">
                                <span style="font-size: 0.72rem; color: #059669; font-weight: 700;">✓ Terverifikasi Asesor</span>
                            @else
                                <span style="color: #94a3b8; font-style: italic; font-size: 0.82rem;">(Tanda Tangan Asesor)</span>
                            @endif
                            <div style="font-size: 0.78rem; color: #475569; margin-top: 2px;">{{ date('d-m-Y') }}</div>
                        </td>
                    </tr>
                </table>

                <!-- TABEL PENYUSUN DAN VALIDATOR (PERSIS GAMBAR HALAMAN 3) -->
                <div>
                    <div style="font-weight: 800; font-size: 0.92rem; color: #000000; margin-bottom: 0.5rem; text-transform: uppercase;">
                        PENYUSUN DAN VALIDATOR
                    </div>
                    
                    <table style="width: 100%; border-collapse: collapse; border: 1px solid #000000; font-size: 0.86rem;">
                        <thead>
                            <tr>
                                <th style="width: 18%; text-align: center; border: 1px solid #000000; padding: 6px 8px; font-weight: 700; background-color: #ffffff;">STATUS</th>
                                <th style="width: 6%; text-align: center; border: 1px solid #000000; padding: 6px 4px; font-weight: 700; background-color: #ffffff;">NO</th>
                                <th style="width: 32%; text-align: center; border: 1px solid #000000; padding: 6px 8px; font-weight: 700; background-color: #ffffff;">NAMA</th>
                                <th style="width: 22%; text-align: center; border: 1px solid #000000; padding: 6px 8px; font-weight: 700; background-color: #ffffff;">NOMOR MET</th>
                                <th style="width: 22%; text-align: center; border: 1px solid #000000; padding: 6px 8px; font-weight: 700; background-color: #ffffff;">TANDA TANGAN DAN TANGGAL</th>
                            </tr>
                        </thead>
                        <tbody>
                            <!-- PENYUSUN 1 -->
                            <tr>
                                <td rowspan="2" style="font-weight: 800; text-align: center; vertical-align: middle; background: #ffffff; border: 1px solid #000000; padding: 6px 8px;">
                                    PENYUSUN
                                </td>
                                <td style="text-align: center; font-weight: 700; border: 1px solid #000000; padding: 6px 4px;">1</td>
                                <td style="padding: 6px 8px; border: 1px solid #000000;"><strong>{{ $asesorNama }}</strong></td>
                                <td style="padding: 6px 8px; border: 1px solid #000000;">{{ $asesorMet }}</td>
                                <td style="text-align: center; padding: 4px; border: 1px solid #000000;">
                                    @if(!empty($asesorTtd))
                                        <img src="{{ asset($asesorTtd) }}" alt="TTD" style="max-height: 36px; margin: 0 auto; display: block;">
                                    @endif
                                    <span style="font-size: 0.75rem; color: #475569;">{{ date('d/m/Y') }}</span>
                                </td>
                            </tr>
                            <!-- PENYUSUN 2 -->
                            <tr>
                                <td style="text-align: center; font-weight: 700; border: 1px solid #000000; padding: 6px 4px;">2</td>
                                <td style="padding: 4px; border: 1px solid #000000;">
                                    <input type="text" name="metadata_penyusun_validator[penyusun_2_nama]" class="muk-input-field input-metadata-muk" style="width: 100%; border: 1px solid #94a3b8; border-radius: 2px; padding: 4px 6px; font-size: 0.82rem;" value="{{ $savedPV['penyusun_2_nama'] ?? '' }}" placeholder="Nama Penyusun 2...">
                                </td>
                                <td style="padding: 4px; border: 1px solid #000000;">
                                    <input type="text" name="metadata_penyusun_validator[penyusun_2_met]" class="muk-input-field input-metadata-muk" style="width: 100%; border: 1px solid #94a3b8; border-radius: 2px; padding: 4px 6px; font-size: 0.82rem;" value="{{ $savedPV['penyusun_2_met'] ?? '' }}" placeholder="No. MET...">
                                </td>
                                <td style="padding: 4px; border: 1px solid #000000;">
                                    <input type="text" name="metadata_penyusun_validator[penyusun_2_ttd]" class="muk-input-field input-metadata-muk" style="width: 100%; text-align: center; border: 1px solid #94a3b8; border-radius: 2px; padding: 4px 6px; font-size: 0.82rem;" value="{{ $savedPV['penyusun_2_ttd'] ?? '' }}" placeholder="TTD & Tgl...">
                                </td>
                            </tr>

                            <!-- VALIDATOR 1 -->
                            <tr>
                                <td rowspan="2" style="font-weight: 800; text-align: center; vertical-align: middle; background: #ffffff; border: 1px solid #000000; padding: 6px 8px;">
                                    VALIDATOR
                                </td>
                                <td style="text-align: center; font-weight: 700; border: 1px solid #000000; padding: 6px 4px;">1</td>
                                <td style="padding: 4px; border: 1px solid #000000;">
                                    <input type="text" name="metadata_penyusun_validator[validator_1_nama]" class="muk-input-field input-metadata-muk" style="width: 100%; border: 1px solid #94a3b8; border-radius: 2px; padding: 4px 6px; font-size: 0.82rem;" value="{{ $savedPV['validator_1_nama'] ?? '' }}" placeholder="Nama Validator 1...">
                                </td>
                                <td style="padding: 4px; border: 1px solid #000000;">
                                    <input type="text" name="metadata_penyusun_validator[validator_1_met]" class="muk-input-field input-metadata-muk" style="width: 100%; border: 1px solid #94a3b8; border-radius: 2px; padding: 4px 6px; font-size: 0.82rem;" value="{{ $savedPV['validator_1_met'] ?? '' }}" placeholder="No. MET...">
                                </td>
                                <td style="padding: 4px; border: 1px solid #000000;">
                                    <input type="text" name="metadata_penyusun_validator[validator_1_ttd]" class="muk-input-field input-metadata-muk" style="width: 100%; text-align: center; border: 1px solid #94a3b8; border-radius: 2px; padding: 4px 6px; font-size: 0.82rem;" value="{{ $savedPV['validator_1_ttd'] ?? '' }}" placeholder="TTD & Tgl...">
                                </td>
                            </tr>
                            <!-- VALIDATOR 2 -->
                            <tr>
                                <td style="text-align: center; font-weight: 700; border: 1px solid #000000; padding: 6px 4px;">2</td>
                                <td style="padding: 4px; border: 1px solid #000000;">
                                    <input type="text" name="metadata_penyusun_validator[validator_2_nama]" class="muk-input-field input-metadata-muk" style="width: 100%; border: 1px solid #94a3b8; border-radius: 2px; padding: 4px 6px; font-size: 0.82rem;" value="{{ $savedPV['validator_2_nama'] ?? '' }}" placeholder="Nama Validator 2...">
                                </td>
                                <td style="padding: 4px; border: 1px solid #000000;">
                                    <input type="text" name="metadata_penyusun_validator[validator_2_met]" class="muk-input-field input-metadata-muk" style="width: 100%; border: 1px solid #94a3b8; border-radius: 2px; padding: 4px 6px; font-size: 0.82rem;" value="{{ $savedPV['validator_2_met'] ?? '' }}" placeholder="No. MET...">
                                </td>
                                <td style="padding: 4px; border: 1px solid #000000;">
                                    <input type="text" name="metadata_penyusun_validator[validator_2_ttd]" class="muk-input-field input-metadata-muk" style="width: 100%; text-align: center; border: 1px solid #94a3b8; border-radius: 2px; padding: 4px 6px; font-size: 0.82rem;" value="{{ $savedPV['validator_2_ttd'] ?? '' }}" placeholder="TTD & Tgl...">
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>

            </div>

        </form>

    <!-- ========================================================================= -->
    <!-- BAGIAN 4B: FR.IA.04A (PROYEK SINGKAT) -->
    <!-- ========================================================================= -->
    @elseif(in_array($codeKey, ['ia_04a', 'ia04a']))

        @php
            $meta = $instrument->additional_metadata ?? [];
            $scenario = $meta['scenario'] ?? "Anda ditugaskan untuk membangun sistem modul aplikasi berbasis web sesuai spesifikasi kebutuhan bisnis klien...";
            $tools = $meta['tools_equipment'] ?? "1. Perangkat Komputer / Laptop Terinstal Web Server (Apache/Nginx) & Database MySQL\n2. Code Editor (VS Code/Sublime Text)\n3. Browser Web Modern & Postman API Client";
            $deliverables = $meta['deliverables'] ?? "1. Source code aplikasi lengkap & skema basis data (.sql)\n2. Dokumentasi API & Manual Penggunaan Sistem\n3. Lembar hasil pengujian sistem";
        @endphp

        <div class="bg-white rounded-2xl border border-slate-200/90 shadow-2xs p-6 mb-8">
            <div class="flex items-center gap-3 pb-4 mb-5 border-b border-slate-100">
                <div class="w-10 h-10 rounded-xl bg-violet-50 text-violet-600 border border-violet-100 flex items-center justify-center font-bold">
                    <i class="fa-solid fa-file-contract text-base"></i>
                </div>
                <div>
                    <h2 class="text-base sm:text-lg font-bold text-slate-900 tracking-tight">
                        Editor Skenario Proyek & Format Luaran (FR.IA.04A)
                    </h2>
                    <p class="text-xs text-slate-500">
                        Atur narasi penugasan proyek singkat, sarana Tempat Uji Kompetensi (TUK), serta luaran berkas yang wajib diserahkan.
                    </p>
                </div>
            </div>

            <form action="{{ route('admin.master-muk.update-metadata', $instrument->id) }}" method="POST">
                @csrf

                <!-- SKENARIO MASALAH / TUGAS -->
                <div class="mb-5">
                    <label class="block font-semibold text-xs text-slate-700 mb-1.5">
                        Skenario Masalah & Batasan Proyek
                    </label>
                    <textarea name="metadata_scenario" rows="5" class="muk-input-field input-metadata-muk leading-relaxed font-sans text-xs sm:text-sm">{{ old('metadata_scenario', $scenario) }}</textarea>
                </div>

                <!-- DAFTAR ALAT & BAHAN TUK -->
                <div class="mb-5">
                    <label class="block font-semibold text-xs text-slate-700 mb-1.5">
                        Kebutuhan Alat & Bahan Tempat Uji Kompetensi (TUK)
                    </label>
                    <textarea name="metadata_tools" rows="4" class="muk-input-field input-metadata-muk leading-relaxed font-sans text-xs sm:text-sm">{{ old('metadata_tools', $tools) }}</textarea>
                </div>

                <!-- FORMAT LUARAN / DELIVERABLES -->
                <div class="mb-6">
                    <label class="block font-semibold text-xs text-slate-700 mb-1.5">
                        Format Luaran / Berkas Proyek yang Wajib Diserahkan Asesi (Deliverables)
                    </label>
                    <textarea name="metadata_deliverables" rows="4" class="muk-input-field input-metadata-muk leading-relaxed font-sans text-xs sm:text-sm">{{ old('metadata_deliverables', $deliverables) }}</textarea>
                </div>

                <div class="flex items-center justify-end pt-2 border-t border-slate-100">
                    <button type="submit" class="inline-flex items-center gap-2 px-6 py-2.5 rounded-xl bg-violet-600 hover:bg-violet-700 active:bg-violet-800 text-white font-semibold text-xs shadow-xs transition-all cursor-pointer btn-submit-metadata">
                        <i class="fa-solid fa-floppy-disk text-xs"></i>
                        <span>Simpan Skenario & Format Tugas</span>
                    </button>
                </div>
            </form>
        </div>

    <!-- ========================================================================= -->
    <!-- BAGIAN 5: FR.IA.11 (CEKLIS SPESIFIKASI MUTU PRODUK) -->
    <!-- ========================================================================= -->
    @elseif(in_array($codeKey, ['ia_11', 'ia11']))

        <div class="form-tambah-soal-card bg-white rounded-2xl border border-slate-200/90 shadow-2xs overflow-hidden mb-8">
            <div class="px-6 py-4 border-b border-slate-100 bg-slate-50/50 flex items-center justify-between">
                <div class="flex items-center gap-3">
                    <div class="w-9 h-9 rounded-xl bg-emerald-50 text-emerald-600 border border-emerald-100 flex items-center justify-center font-bold">
                        <i class="fa-solid fa-circle-check text-sm"></i>
                    </div>
                    <div>
                        <h2 class="text-base font-bold text-slate-900 tracking-tight">
                            Tambah Parameter Spesifikasi Mutu Produk (FR.IA.11)
                        </h2>
                        <p class="text-xs text-slate-500">
                            Tentukan parameter keberterimaan mutu produk dan batas toleransi standar hasil kerja asesi.
                        </p>
                    </div>
                </div>
            </div>

            <form action="{{ route('admin.master-muk.spec.store') }}" method="POST" class="p-6">
                @csrf
                <input type="hidden" name="scheme_master_instrument_id" value="{{ $instrument->id }}">

                <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-5">
                    <div>
                        <label class="block font-semibold text-xs text-slate-700 mb-1.5">
                            Nama Parameter / Fitur Spesifikasi <span class="text-rose-500">*</span>
                        </label>
                        <input type="text" name="spec_name" placeholder="Contoh: Waktu Muat Halaman (Page Load Time)" class="muk-input-field" required>
                    </div>

                    <div>
                        <label class="block font-semibold text-xs text-slate-700 mb-1.5">
                            Standar / Batas Toleransi (Waktu) <span class="text-rose-500">*</span>
                        </label>
                        <input type="time" name="standard_tolerance" step="1" class="muk-input-field" required>
                        <p class="text-[11px] text-slate-400 mt-1">Hanya format waktu yang diizinkan (JJ:MM:DD / JJ:MM).</p>
                    </div>
                </div>

                <div class="flex items-center justify-end pt-2 border-t border-slate-100">
                    <button type="submit" class="inline-flex items-center gap-2 px-5 py-2.5 rounded-xl bg-emerald-600 hover:bg-emerald-700 active:bg-emerald-800 text-white font-semibold text-xs shadow-xs transition-all cursor-pointer">
                        <i class="fa-solid fa-floppy-disk text-xs"></i>
                        <span>Simpan Parameter Spesifikasi</span>
                    </button>
                </div>
            </form>
        </div>

        <div class="mb-8">
            <div class="flex items-center justify-between mb-4">
                <div class="flex items-center gap-2.5">
                    <h3 class="text-base font-bold text-slate-900 tracking-tight">
                        Parameter Spesifikasi Produk
                    </h3>
                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-bold bg-slate-100 text-slate-700 border border-slate-200/60">
                        {{ $instrument->productSpecifications->count() }} Parameter
                    </span>
                </div>
            </div>

            <div class="bg-white rounded-2xl border border-slate-200/90 shadow-2xs overflow-hidden">
                <table class="w-full text-left border-collapse text-xs">
                    <thead>
                        <tr class="bg-slate-50/80 border-b border-slate-200/80 text-slate-600 font-bold uppercase tracking-wider text-[11px]">
                            <th class="py-3 px-4 w-12 text-center">No.</th>
                            <th class="py-3 px-4">Nama Parameter Spesifikasi</th>
                            <th class="py-3 px-4">Standar / Batas Toleransi</th>
                            <th class="py-3 px-4 w-36 text-center">Aksi</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100">
                        @forelse($instrument->productSpecifications as $spec)
                            <tr class="hover:bg-slate-50/50 transition">
                                <td class="py-3 px-4 text-center font-bold text-slate-700">{{ $spec->order }}</td>
                                <td class="py-3 px-4 font-semibold text-slate-900">{{ $spec->spec_name }}</td>
                                <td class="py-3 px-4">
                                    <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-lg bg-blue-50 text-blue-700 border border-blue-200/60 font-mono text-xs font-semibold">
                                        <i class="fa-regular fa-clock text-blue-500"></i>
                                        <span>{{ $spec->standard_tolerance }}</span>
                                    </span>
                                </td>
                                <td class="py-3 px-4 text-center">
                                    <div class="flex items-center justify-center gap-1.5 flex-wrap">
                                        <button type="button" 
                                                onclick="bukaModalEditSpec({{ $spec->id }}, '{{ addslashes($spec->spec_name) }}', '{{ addslashes($spec->standard_tolerance) }}', {{ $spec->order }})"
                                                class="inline-flex items-center gap-1 px-2.5 py-1 rounded-lg bg-amber-50 hover:bg-amber-100 text-amber-700 font-semibold text-[11px] border border-amber-200/70 transition cursor-pointer"
                                                title="Edit parameter spesifikasi ini">
                                            <i class="fa-regular fa-pen-to-square"></i>
                                            <span>Edit</span>
                                        </button>
                                        <form action="{{ route('admin.master-muk.spec.destroy', $spec->id) }}" method="POST" onsubmit="return konfirmasiHapus(event, this, 'parameter spesifikasi ini')" class="inline">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="inline-flex items-center gap-1 px-2.5 py-1 rounded-lg bg-rose-50 hover:bg-rose-100 text-rose-600 font-semibold text-[11px] border border-rose-200/70 transition cursor-pointer" title="Hapus parameter ini">
                                                <i class="fa-regular fa-trash-can"></i>
                                                <span>Hapus</span>
                                            </button>
                                        </form>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="4" class="py-8 text-center text-slate-500 font-medium">
                                    <div class="flex flex-col items-center justify-center gap-1.5">
                                        <i class="fa-regular fa-clock text-slate-300 text-2xl"></i>
                                        <span>Belum ada parameter spesifikasi produk yang ditambahkan.</span>
                                        <span class="text-[11px] text-slate-400">Silakan masukkan nama parameter dan standar batas toleransi waktu pada formulir di atas.</span>
                                    </div>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

    @elseif(in_array($codeKey, ['ia_01', 'ia01']))
        <!-- ========================================================================= -->
        <!-- BAGIAN 6: FR.IA.01 (CEKLIS OBSERVASI AKTIVITAS PRAKTIK) -->
        <!-- ========================================================================= -->
        @php
            $units = ($instrument->skema && $instrument->skema->unitKompetensi->isNotEmpty()) 
                ? $instrument->skema->unitKompetensi 
                : ($instrument->unitKompetensi ? collect([$instrument->unitKompetensi]) : collect());
            $meta = $instrument->additional_metadata ?? [];
            if (!is_array($meta)) $meta = json_decode($meta, true) ?: [];
            
            $defaultStandard = $meta['default_standard'] ?? ('Standar Operasional Prosedur (SOP) & SKKNI ' . ($instrument->skema->kode_skema ?? ''));
            $standarPerElemen = $meta['standar_elemen'] ?? [];
            $kelompokSplit = (int)($meta['kelompok_split'] ?? 0);
            $umpanBalik = $meta['umpan_balik'] ?? 'Seluruh instruksi kerja dan demonstrasi praktik telah diobservasi dengan baik sesuai standar kompetensi SKKNI.';

            $totalKukCount = 0;
            foreach ($units as $u) {
                foreach ($u->elemenKompetensi as $e) {
                    $totalKukCount += $e->kriteriaUnjukKerja->count();
                }
            }

            // Pisahkan unit bila ada pembagian Kelompok Pekerjaan
            if ($kelompokSplit > 0 && $units->count() > $kelompokSplit) {
                $group1Units = $units->slice(0, $kelompokSplit);
                $group2Units = $units->slice($kelompokSplit);
            } else {
                $group1Units = $units;
                $group2Units = collect();
            }
        @endphp

        <form action="{{ route('admin.master-muk.update-metadata', $instrument->id) }}" method="POST">
            @csrf

            <!-- BAR KONTROL & PENGATURAN CEPAT -->
            <div class="bg-white rounded-2xl border border-slate-200/90 shadow-2xs p-5 mb-6">
                <div class="flex flex-col lg:flex-row lg:items-center justify-between gap-4 pb-4 border-b border-slate-100">
                    <div class="flex items-center gap-3">
                        <div class="w-10 h-10 rounded-xl bg-blue-50 text-blue-600 border border-blue-100 flex items-center justify-center font-bold">
                            <i class="fa-solid fa-clipboard-check text-base"></i>
                        </div>
                        <div>
                            <h2 class="text-base sm:text-lg font-bold text-slate-900 tracking-tight">
                                Formulir FR.IA.01 - Ceklis Observasi Aktivitas Praktik
                            </h2>
                            <p class="text-xs text-slate-500">
                                Template resmi BNSP untuk observasi di tempat kerja atau tempat kerja simulasi.
                            </p>
                        </div>
                    </div>

                    <div class="flex items-center gap-2.5 flex-wrap">
                        <a href="{{ route('formulir.ia01', ['skema_id' => $instrument->skema_id]) }}" target="_blank" class="inline-flex items-center gap-1.5 px-3.5 py-2 rounded-xl bg-slate-100 hover:bg-slate-200 text-slate-700 text-xs font-semibold transition">
                            <i class="fa-solid fa-arrow-up-right-from-square text-[11px]"></i>
                            <span>Buka Pratinjau Asesi</span>
                        </a>

                        <button type="submit" class="inline-flex items-center gap-2 px-5 py-2 rounded-xl bg-blue-600 hover:bg-blue-700 active:bg-blue-800 text-white font-semibold text-xs shadow-xs transition cursor-pointer">
                            <i class="fa-solid fa-floppy-disk text-xs"></i>
                            <span>Simpan Formulir FR.IA.01</span>
                        </button>
                    </div>
                </div>

                <!-- Opsi Tambahan Parameter Form -->
                <div class="grid grid-cols-1 sm:grid-cols-3 gap-4 pt-4 text-xs">
                    <div>
                        <label class="block font-semibold text-slate-700 mb-1">
                            Standar Acuan Utama (Default)
                        </label>
                        <input type="text" name="metadata_default_standard" value="{{ old('metadata_default_standard', $defaultStandard) }}" class="muk-input-field" placeholder="Contoh: Standar Operasional Prosedur (SOP) Industri & SKKNI" required>
                    </div>

                    <div>
                        <label class="block font-semibold text-slate-700 mb-1">
                            Estimasi Waktu Observasi
                        </label>
                        <div class="relative">
                            <input type="number" name="time_limit_minutes" value="{{ $instrument->time_limit_minutes ?? 120 }}" min="1" max="1440" class="muk-input-field pr-14 font-bold text-slate-800">
                            <span class="absolute inset-y-0 right-0 flex items-center px-3 text-slate-400 font-semibold pointer-events-none">
                                Menit
                            </span>
                        </div>
                    </div>

                    <div>
                        <label class="block font-semibold text-slate-700 mb-1">
                            Kelompok Pekerjaan
                        </label>
                        <select name="metadata_kelompok_split" class="muk-input-field">
                            <option value="0" {{ $kelompokSplit == 0 ? 'selected' : '' }}>Semua Unit pada Kelompok Pekerjaan 1</option>
                            @if($units->count() > 1)
                                @for($i = 1; $i < $units->count(); $i++)
                                    <option value="{{ $i }}" {{ $kelompokSplit == $i ? 'selected' : '' }}>
                                        Bagi 2: Unit 1-{{ $i }} (Kelompok 1) & Unit {{ $i + 1 }}-{{ $units->count() }} (Kelompok 2)
                                    </option>
                                @endfor
                            @endif
                        </select>
                    </div>
                </div>
            </div>

            <!-- ========================================================================= -->
            <!-- LEMBAR FORMULIR ASLI BNSP (SESUAI GAMBAR DOKUMEN FR.IA.01) -->
            <!-- ========================================================================= -->
            <div class="dokumen-kertas shadow-sm" style="border: 2px solid #0f172a; padding: 2rem; background: #ffffff; max-width: 1050px; margin: 0 auto 3rem auto; color: #0f172a;">
                
                <!-- JUDUL FORMULIR -->
                <div style="font-size: 1.05rem; font-weight: 800; text-align: left; color: #0f172a; margin-bottom: 1.25rem; letter-spacing: 0.2px;">
                    FR.IA.01. CL - CEKLIS OBSERVASI AKTIVITAS DI TEMPAT KERJA ATAU TEMPAT KERJA SIMULASI
                </div>

                <!-- TABEL IDENTITAS -->
                <table class="tabel-bnsp" style="width: 100%; border-collapse: collapse; margin-bottom: 0.35rem; font-size: 0.88rem;">
                    <tr>
                        <td rowspan="2" style="width: 28%; font-weight: 700; vertical-align: middle; background-color: #f8fafc; border: 1px solid #0f172a; padding: 6px 10px;">
                            Skema Sertifikasi<br>
                            <span style="font-weight: 500; font-size: 0.8rem; color: #475569;">(KKNI/Okupasi/Klaster)</span>
                        </td>
                        <td style="width: 12%; font-weight: 600; border: 1px solid #0f172a; padding: 6px 10px;">Judul</td>
                        <td style="width: 2%; text-align: center; border: 1px solid #0f172a; padding: 6px 4px;">:</td>
                        <td style="font-weight: 700; color: #0f172a; border: 1px solid #0f172a; padding: 6px 10px;">
                            {{ $instrument->skema->nama_skema ?? '-' }}
                        </td>
                    </tr>
                    <tr>
                        <td style="font-weight: 600; border: 1px solid #0f172a; padding: 6px 10px;">Nomor</td>
                        <td style="text-align: center; border: 1px solid #0f172a; padding: 6px 4px;">:</td>
                        <td style="font-weight: 700; color: #0f172a; border: 1px solid #0f172a; padding: 6px 10px;">
                            {{ $instrument->skema->kode_skema ?? '-' }}
                        </td>
                    </tr>
                    <tr>
                        <td colspan="2" style="font-weight: 600; border: 1px solid #0f172a; padding: 6px 10px;">TUK</td>
                        <td style="text-align: center; border: 1px solid #0f172a; padding: 6px 4px;">:</td>
                        <td style="border: 1px solid #0f172a; padding: 6px 10px;">Sewaktu/Tempat Kerja/Mandiri*</td>
                    </tr>
                    <tr>
                        <td colspan="2" style="font-weight: 600; border: 1px solid #0f172a; padding: 6px 10px;">Nama Asesor</td>
                        <td style="text-align: center; border: 1px solid #0f172a; padding: 6px 4px;">:</td>
                        <td style="border: 1px solid #0f172a; padding: 6px 10px; font-weight: 600;">
                            {{ auth()->user()->nama_lengkap ?? 'Asesor Kompetensi' }}
                        </td>
                    </tr>
                    <tr>
                        <td colspan="2" style="font-weight: 600; border: 1px solid #0f172a; padding: 6px 10px;">Nama Asesi</td>
                        <td style="text-align: center; border: 1px solid #0f172a; padding: 6px 4px;">:</td>
                        <td style="border: 1px solid #0f172a; padding: 6px 10px; color: #64748b; font-style: italic;">
                            (Diisi secara dinamis pada saat asesmen berjalan)
                        </td>
                    </tr>
                    <tr>
                        <td colspan="2" style="font-weight: 600; border: 1px solid #0f172a; padding: 6px 10px;">Tanggal</td>
                        <td style="text-align: center; border: 1px solid #0f172a; padding: 6px 4px;">:</td>
                        <td style="border: 1px solid #0f172a; padding: 6px 10px;">{{ date('d-m-Y') }}</td>
                    </tr>
                </table>
                <div style="font-size: 0.75rem; font-style: italic; color: #64748b; margin-top: 2px; margin-bottom: 1.25rem;">
                    *Coret yang tidak perlu
                </div>

                <!-- PANDUAN BAGI ASESOR (SESUAI PERSIS DENGAN GAMBAR) -->
                <div style="border: 1px solid #0f172a; padding: 0.85rem 1.25rem; background: #ffffff; margin-bottom: 1.5rem;">
                    <div style="font-weight: 800; font-size: 0.92rem; color: #0f172a; margin-bottom: 0.45rem; text-transform: uppercase;">
                        PANDUAN BAGI ASESOR
                    </div>
                    <ul style="margin: 0; padding-left: 1.2rem; font-size: 0.84rem; color: #0f172a; line-height: 1.65; list-style-type: disc;">
                        <li>Lengkapi nama unit kompetensi, elemen, dan kriteria unjuk kerja sesuai kolom dalam tabel.</li>
                        <li>Isilah standar industri atau tempat kerja</li>
                        <li>Beri tanda centang (&radic;) pada kolom "YA" jika Anda yakin asesi dapat melakukan/mendemonstrasikan tugas sesuai KUK, atau centang (&radic;) pada kolom "Tidak" bila sebaliknya.</li>
                        <li>Penilaian Lanjut diisi bila hasil belum dapat disimpulkan, untuk itu gunakan metode lain sehingga keputusan dapat dibuat.</li>
                        <li>Isilah kolom KUK sesuai dengan Unit Kompetensi/ SKKNI</li>
                    </ul>
                </div>

                <!-- ========================================================================= -->
                <!-- KELOMPOK PEKERJAAN 1 -->
                <!-- ========================================================================= -->
                <table class="tabel-bnsp" style="width: 100%; border-collapse: collapse; margin-bottom: 1.5rem; font-size: 0.86rem;">
                    <thead>
                        <tr>
                            <th rowspan="{{ $group1Units->count() + 1 }}" style="width: 25%; text-align: center; vertical-align: middle; font-weight: 800; background-color: #ffffff; border: 1px solid #0f172a; padding: 8px 10px;">
                                Kelompok Pekerjaan 1
                            </th>
                            <th style="width: 8%; text-align: center; font-weight: 700; border: 1px solid #0f172a; padding: 6px;">No.</th>
                            <th style="width: 27%; text-align: center; font-weight: 700; border: 1px solid #0f172a; padding: 6px;">Kode Unit</th>
                            <th style="text-align: center; font-weight: 700; border: 1px solid #0f172a; padding: 6px;">Judul Unit</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($group1Units as $idx => $unit)
                            <tr>
                                <td style="text-align: center; font-weight: 700; border: 1px solid #0f172a; padding: 6px;">{{ $idx + 1 }}.</td>
                                <td style="font-weight: 600; color: #0f172a; border: 1px solid #0f172a; padding: 6px 10px;">{{ $unit->kode_unit }}</td>
                                <td style="font-weight: 600; color: #0f172a; border: 1px solid #0f172a; padding: 6px 10px;">{{ $unit->judul_unit }}</td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="3" style="text-align: center; color: #64748b; font-style: italic; border: 1px solid #0f172a; padding: 10px;">
                                    Belum ada data unit kompetensi terdaftar pada skema.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>

                <!-- TABEL RINCIAN UNIT KOMPETENSI KELOMPOK 1 -->
                @foreach($group1Units as $idxUnit => $unit)
                    <div style="margin-top: 1.5rem; margin-bottom: 2rem;">
                        <!-- HEADER BAR UNIT KOMPETENSI -->
                        <table class="tabel-bnsp" style="width: 100%; border-collapse: collapse; margin-bottom: 0; font-size: 0.88rem;">
                            <tr>
                                <td rowspan="2" style="width: 25%; font-weight: 800; vertical-align: middle; background-color: #ffffff; border: 1px solid #0f172a; padding: 8px 10px;">
                                    Unit Kompetensi {{ $idxUnit + 1 }}
                                </td>
                                <td style="width: 14%; font-weight: 600; border: 1px solid #0f172a; padding: 6px 10px;">Kode Unit</td>
                                <td style="width: 2%; text-align: center; border: 1px solid #0f172a; padding: 6px 4px;">:</td>
                                <td style="font-weight: 700; color: #0f172a; border: 1px solid #0f172a; padding: 6px 10px;">{{ $unit->kode_unit }}</td>
                            </tr>
                            <tr>
                                <td style="font-weight: 600; border: 1px solid #0f172a; padding: 6px 10px;">Judul Unit</td>
                                <td style="text-align: center; border: 1px solid #0f172a; padding: 6px 4px;">:</td>
                                <td style="font-weight: 700; color: #0f172a; border: 1px solid #0f172a; padding: 6px 10px;">{{ $unit->judul_unit }}</td>
                            </tr>
                        </table>

                        <!-- TABEL CEKLIS OBSERVASI UNIT -->
                        <table class="tabel-bnsp" style="width: 100%; border-collapse: collapse; font-size: 0.82rem; margin-top: -1px;">
                            <thead>
                                <tr>
                                    <th rowspan="2" style="width: 5%; text-align: center; border: 1px solid #0f172a; padding: 6px;">No.</th>
                                    <th rowspan="2" style="width: 22%; text-align: center; border: 1px solid #0f172a; padding: 6px;">Elemen</th>
                                    <th rowspan="2" style="width: 33%; text-align: center; border: 1px solid #0f172a; padding: 6px;">Kriteria Unjuk Kerja</th>
                                    <th rowspan="2" style="width: 20%; text-align: center; border: 1px solid #0f172a; padding: 6px;">Standar Industri atau Tempat Kerja</th>
                                    <th colspan="2" style="width: 12%; text-align: center; border: 1px solid #0f172a; padding: 6px;">Pencapaian</th>
                                    <th rowspan="2" style="width: 8%; text-align: center; border: 1px solid #0f172a; padding: 6px;">Penilaian Lanjut</th>
                                </tr>
                                <tr>
                                    <th style="width: 6%; text-align: center; border: 1px solid #0f172a; padding: 4px;">Ya</th>
                                    <th style="width: 6%; text-align: center; border: 1px solid #0f172a; padding: 4px;">Tidak</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($unit->elemenKompetensi as $idxElem => $elem)
                                    @php
                                        $totalKuk = $elem->kriteriaUnjukKerja->count();
                                        $elemStandar = $standarPerElemen[$elem->id] ?? $defaultStandard;
                                    @endphp
                                    @if($totalKuk > 0)
                                        @foreach($elem->kriteriaUnjukKerja as $kIdx => $kuk)
                                            <tr>
                                                @if($kIdx === 0)
                                                    <td rowspan="{{ $totalKuk }}" style="text-align: center; font-weight: 700; vertical-align: top; border: 1px solid #0f172a; padding: 6px;">
                                                        {{ $idxElem + 1 }}
                                                    </td>
                                                    <td rowspan="{{ $totalKuk }}" style="font-weight: 600; color: #0f172a; vertical-align: top; border: 1px solid #0f172a; padding: 6px 8px;">
                                                        {{ $elem->nomor_elemen }}. {{ $elem->nama_elemen }}
                                                    </td>
                                                @endif
                                                
                                                <!-- KUK -->
                                                <td style="border: 1px solid #0f172a; padding: 6px 8px; vertical-align: top;">
                                                    <span style="font-weight: 700;">{{ $elem->nomor_elemen }}.{{ $kuk->nomor_kuk }}</span> {{ $kuk->pernyataan_kuk }}
                                                </td>

                                                @if($kIdx === 0)
                                                    <!-- Standar Industri per Elemen (Rowspan sesuai gambar) -->
                                                    <td rowspan="{{ $totalKuk }}" style="border: 1px solid #0f172a; padding: 6px; vertical-align: middle; text-align: center; background: #fafafa;">
                                                        <textarea name="metadata_standar_elemen[{{ $elem->id }}]" rows="{{ max(2, $totalKuk) }}" class="input-inline-bnsp" style="width: 100%; border: 1px dashed #94a3b8; padding: 4px; font-size: 0.78rem; text-align: center; resize: vertical;" placeholder="Isilah standar industri...">{{ old("metadata_standar_elemen.{$elem->id}", $elemStandar) }}</textarea>
                                                    </td>
                                                @endif

                                                <!-- Checkbox Pencapaian (Mockup Kotak Centang Sesuai Gambar) -->
                                                <td style="text-align: center; vertical-align: middle; border: 1px solid #0f172a; padding: 4px;">
                                                    <input type="checkbox" disabled style="width: 15px; height: 15px; cursor: not-allowed; accent-color: #0f172a;">
                                                </td>
                                                <td style="text-align: center; vertical-align: middle; border: 1px solid #0f172a; padding: 4px;">
                                                    <input type="checkbox" disabled style="width: 15px; height: 15px; cursor: not-allowed; accent-color: #0f172a;">
                                                </td>

                                                <!-- Penilaian Lanjut -->
                                                <td style="border: 1px solid #0f172a; padding: 4px; text-align: center; background: #ffffff;">
                                                    <span style="color: #94a3b8; font-size: 0.75rem;">-</span>
                                                </td>
                                            </tr>
                                        @endforeach
                                    @else
                                        <tr>
                                            <td style="text-align: center; font-weight: 700; border: 1px solid #0f172a; padding: 6px;">{{ $idxElem + 1 }}</td>
                                            <td style="font-weight: 600; border: 1px solid #0f172a; padding: 6px 8px;">{{ $elem->nomor_elemen }}. {{ $elem->nama_elemen }}</td>
                                            <td style="font-style: italic; color: #64748b; border: 1px solid #0f172a; padding: 6px 8px;">KUK belum diinput.</td>
                                            <td style="border: 1px solid #0f172a; padding: 6px;"><input type="text" class="input-inline-bnsp" value="{{ $defaultStandard }}" style="font-size: 0.78rem;"></td>
                                            <td style="text-align: center; border: 1px solid #0f172a; padding: 4px;"><input type="checkbox" disabled style="width: 15px; height: 15px;"></td>
                                            <td style="text-align: center; border: 1px solid #0f172a; padding: 4px;"><input type="checkbox" disabled style="width: 15px; height: 15px;"></td>
                                            <td style="border: 1px solid #0f172a; padding: 4px; text-align: center;">-</td>
                                        </tr>
                                    @endif
                                @empty
                                    <tr>
                                        <td colspan="7" style="text-align: center; color: #64748b; font-style: italic; border: 1px solid #0f172a; padding: 10px;">
                                            Belum ada data elemen kompetensi.
                                        </td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                @endforeach

                <!-- ========================================================================= -->
                <!-- UMPAN BALIK UNTUK ASESI (SESUAI GAMBAR) -->
                <!-- ========================================================================= -->
                <div style="border: 1px solid #0f172a; padding: 0.85rem 1rem; margin-top: 1.5rem; margin-bottom: 2rem;">
                    <div style="font-weight: 700; font-size: 0.92rem; margin-bottom: 0.45rem; color: #0f172a;">
                        Umpan Balik untuk asesi:
                    </div>
                    <textarea name="metadata_umpan_balik" rows="3" class="input-inline-bnsp" style="width: 100%; border: 1px dashed #cbd5e1; padding: 0.5rem; font-size: 0.85rem; border-radius: 4px; box-sizing: border-box;" placeholder="Tuliskan umpan balik standar untuk asesi...">{{ old('metadata_umpan_balik', $umpanBalik) }}</textarea>
                </div>

                <!-- ========================================================================= -->
                <!-- KELOMPOK PEKERJAAN 2 (JIKA DIBAGI / ADA KELOMPOK KEDUA) -->
                <!-- ========================================================================= -->
                @if($group2Units->count() > 0)
                    <table class="tabel-bnsp" style="width: 100%; border-collapse: collapse; margin-top: 2rem; margin-bottom: 1.5rem; font-size: 0.86rem;">
                        <thead>
                            <tr>
                                <th rowspan="{{ $group2Units->count() + 1 }}" style="width: 25%; text-align: center; vertical-align: middle; font-weight: 800; background-color: #ffffff; border: 1px solid #0f172a; padding: 8px 10px;">
                                    Kelompok Pekerjaan 2
                                </th>
                                <th style="width: 8%; text-align: center; font-weight: 700; border: 1px solid #0f172a; padding: 6px;">No.</th>
                                <th style="width: 27%; text-align: center; font-weight: 700; border: 1px solid #0f172a; padding: 6px;">Kode Unit</th>
                                <th style="text-align: center; font-weight: 700; border: 1px solid #0f172a; padding: 6px;">Judul Unit</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($group2Units as $idx => $unit)
                                <tr>
                                    <td style="text-align: center; font-weight: 700; border: 1px solid #0f172a; padding: 6px;">{{ $idx + 1 }}.</td>
                                    <td style="font-weight: 600; color: #0f172a; border: 1px solid #0f172a; padding: 6px 10px;">{{ $unit->kode_unit }}</td>
                                    <td style="font-weight: 600; color: #0f172a; border: 1px solid #0f172a; padding: 6px 10px;">{{ $unit->judul_unit }}</td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>

                    <!-- TABEL RINCIAN UNIT KOMPETENSI KELOMPOK 2 -->
                    @foreach($group2Units as $idxUnit => $unit)
                        <div style="margin-top: 1.5rem; margin-bottom: 2rem;">
                            <!-- HEADER BAR UNIT KOMPETENSI -->
                            <table class="tabel-bnsp" style="width: 100%; border-collapse: collapse; margin-bottom: 0; font-size: 0.88rem;">
                                <tr>
                                    <td rowspan="2" style="width: 25%; font-weight: 800; vertical-align: middle; background-color: #ffffff; border: 1px solid #0f172a; padding: 8px 10px;">
                                        Unit Kompetensi {{ $group1Units->count() + $idxUnit + 1 }}
                                    </td>
                                    <td style="width: 14%; font-weight: 600; border: 1px solid #0f172a; padding: 6px 10px;">Kode Unit</td>
                                    <td style="width: 2%; text-align: center; border: 1px solid #0f172a; padding: 6px 4px;">:</td>
                                    <td style="font-weight: 700; color: #0f172a; border: 1px solid #0f172a; padding: 6px 10px;">{{ $unit->kode_unit }}</td>
                                </tr>
                                <tr>
                                    <td style="font-weight: 600; border: 1px solid #0f172a; padding: 6px 10px;">Judul Unit</td>
                                    <td style="text-align: center; border: 1px solid #0f172a; padding: 6px 4px;">:</td>
                                    <td style="font-weight: 700; color: #0f172a; border: 1px solid #0f172a; padding: 6px 10px;">{{ $unit->judul_unit }}</td>
                                </tr>
                            </table>

                            <!-- TABEL CEKLIS OBSERVASI UNIT -->
                            <table class="tabel-bnsp" style="width: 100%; border-collapse: collapse; font-size: 0.82rem; margin-top: -1px;">
                                <thead>
                                    <tr>
                                        <th rowspan="2" style="width: 5%; text-align: center; border: 1px solid #0f172a; padding: 6px;">No.</th>
                                        <th rowspan="2" style="width: 22%; text-align: center; border: 1px solid #0f172a; padding: 6px;">Elemen</th>
                                        <th rowspan="2" style="width: 33%; text-align: center; border: 1px solid #0f172a; padding: 6px;">Kriteria Unjuk Kerja</th>
                                        <th rowspan="2" style="width: 20%; text-align: center; border: 1px solid #0f172a; padding: 6px;">Standar Industri atau Tempat Kerja</th>
                                        <th colspan="2" style="width: 12%; text-align: center; border: 1px solid #0f172a; padding: 6px;">Pencapaian</th>
                                        <th rowspan="2" style="width: 8%; text-align: center; border: 1px solid #0f172a; padding: 6px;">Penilaian Lanjut</th>
                                    </tr>
                                    <tr>
                                        <th style="width: 6%; text-align: center; border: 1px solid #0f172a; padding: 4px;">Ya</th>
                                        <th style="width: 6%; text-align: center; border: 1px solid #0f172a; padding: 4px;">Tidak</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @forelse($unit->elemenKompetensi as $idxElem => $elem)
                                        @php
                                            $totalKuk = $elem->kriteriaUnjukKerja->count();
                                            $elemStandar = $standarPerElemen[$elem->id] ?? $defaultStandard;
                                        @endphp
                                        @if($totalKuk > 0)
                                            @foreach($elem->kriteriaUnjukKerja as $kIdx => $kuk)
                                                <tr>
                                                    @if($kIdx === 0)
                                                        <td rowspan="{{ $totalKuk }}" style="text-align: center; font-weight: 700; vertical-align: top; border: 1px solid #0f172a; padding: 6px;">
                                                            {{ $idxElem + 1 }}
                                                        </td>
                                                        <td rowspan="{{ $totalKuk }}" style="font-weight: 600; color: #0f172a; vertical-align: top; border: 1px solid #0f172a; padding: 6px 8px;">
                                                            {{ $elem->nomor_elemen }}. {{ $elem->nama_elemen }}
                                                        </td>
                                                    @endif
                                                    
                                                    <td style="border: 1px solid #0f172a; padding: 6px 8px; vertical-align: top;">
                                                        <span style="font-weight: 700;">{{ $elem->nomor_elemen }}.{{ $kuk->nomor_kuk }}</span> {{ $kuk->pernyataan_kuk }}
                                                    </td>

                                                    @if($kIdx === 0)
                                                        <td rowspan="{{ $totalKuk }}" style="border: 1px solid #0f172a; padding: 6px; vertical-align: middle; text-align: center; background: #fafafa;">
                                                            <textarea name="metadata_standar_elemen[{{ $elem->id }}]" rows="{{ max(2, $totalKuk) }}" class="input-inline-bnsp" style="width: 100%; border: 1px dashed #94a3b8; padding: 4px; font-size: 0.78rem; text-align: center; resize: vertical;" placeholder="Isilah standar industri...">{{ old("metadata_standar_elemen.{$elem->id}", $elemStandar) }}</textarea>
                                                        </td>
                                                    @endif

                                                    <td style="text-align: center; vertical-align: middle; border: 1px solid #0f172a; padding: 4px;">
                                                        <input type="checkbox" disabled style="width: 15px; height: 15px; cursor: not-allowed;">
                                                    </td>
                                                    <td style="text-align: center; vertical-align: middle; border: 1px solid #0f172a; padding: 4px;">
                                                        <input type="checkbox" disabled style="width: 15px; height: 15px; cursor: not-allowed;">
                                                    </td>
                                                    <td style="border: 1px solid #0f172a; padding: 4px; text-align: center; background: #ffffff;">
                                                        <span style="color: #94a3b8; font-size: 0.75rem;">-</span>
                                                    </td>
                                                </tr>
                                            @endforeach
                                        @else
                                            <tr>
                                                <td style="text-align: center; font-weight: 700; border: 1px solid #0f172a; padding: 6px;">{{ $idxElem + 1 }}</td>
                                                <td style="font-weight: 600; border: 1px solid #0f172a; padding: 6px 8px;">{{ $elem->nomor_elemen }}. {{ $elem->nama_elemen }}</td>
                                                <td style="font-style: italic; color: #64748b; border: 1px solid #0f172a; padding: 6px 8px;">KUK belum diinput.</td>
                                                <td style="border: 1px solid #0f172a; padding: 6px;"><input type="text" class="input-inline-bnsp" value="{{ $defaultStandard }}" style="font-size: 0.78rem;"></td>
                                                <td style="text-align: center; border: 1px solid #0f172a; padding: 4px;"><input type="checkbox" disabled style="width: 15px; height: 15px;"></td>
                                                <td style="text-align: center; border: 1px solid #0f172a; padding: 4px;"><input type="checkbox" disabled style="width: 15px; height: 15px;"></td>
                                                <td style="border: 1px solid #0f172a; padding: 4px; text-align: center;">-</td>
                                            </tr>
                                        @endif
                                    @empty
                                        <tr>
                                            <td colspan="7" style="text-align: center; color: #64748b; font-style: italic; border: 1px solid #0f172a; padding: 10px;">
                                                Belum ada data elemen kompetensi.
                                            </td>
                                        </tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>
                    @endforeach
                @endif

                <!-- TOMBOL SIMPAN DI BAGIAN BAWAH FORM -->
                <div class="mt-8 pt-4 border-t border-slate-200 flex items-center justify-between flex-wrap gap-3">
                    <div class="text-xs text-slate-500">
                        *Klik tombol simpan untuk memperbarui konfigurasi standar industri dan template FR.IA.01 skema.
                    </div>
                    <button type="submit" class="inline-flex items-center gap-2 px-6 py-2.5 rounded-xl bg-blue-600 hover:bg-blue-700 active:bg-blue-800 text-white font-semibold text-xs shadow-xs transition cursor-pointer">
                        <i class="fa-solid fa-floppy-disk text-xs"></i>
                        <span>Simpan Formulir FR.IA.01</span>
                    </button>
                </div>

            </div>
        </form>

    @else
        <!-- ========================================================================= -->
        <!-- FALLBACK UNTUK INSTRUMEN LAINNYA (GENERIC MUK BUILDER) -->
        <!-- ========================================================================= -->
        <div class="form-tambah-soal-card bg-white rounded-2xl border border-slate-200/90 shadow-2xs overflow-hidden mb-8">
            <div class="px-6 py-4 border-b border-slate-100 bg-slate-50/50 flex items-center justify-between">
                <div class="flex items-center gap-3">
                    <div class="w-9 h-9 rounded-xl bg-blue-50 text-blue-600 border border-blue-100 flex items-center justify-center font-bold">
                        <i class="fa-solid fa-plus text-sm"></i>
                    </div>
                    <div>
                        <h2 class="text-base font-bold text-slate-900 tracking-tight">
                            Tambah Butir Instrumen {{ strtoupper($instrument->instrument_code) }}
                        </h2>
                        <p class="text-xs text-slate-500">
                            Kelola butir soal atau item verifikasi untuk perangkat instrumen asesmen ini.
                        </p>
                    </div>
                </div>
            </div>

            <form action="{{ route('admin.master-muk.soal.store') }}" method="POST" class="p-6">
                @csrf
                <input type="hidden" name="scheme_master_instrument_id" value="{{ $instrument->id }}">
                <input type="hidden" name="question_type" value="essay">

                <div class="mb-4">
                    <label class="block font-semibold text-xs text-slate-700 mb-1.5">
                        Relasi Elemen / KUK Standar Kompetensi
                    </label>
                    <div class="relative">
                        <select name="kuk_id" class="muk-input-field appearance-none pr-10 cursor-pointer">
                            <option value="">-- Umum / Seluruh Unit Kompetensi --</option>
                            @foreach($kukList as $k)
                                <option value="{{ $k['id'] }}">{{ $k['label'] }}</option>
                            @endforeach
                        </select>
                        <div class="absolute inset-y-0 right-0 flex items-center px-3 pointer-events-none text-slate-400">
                            <i class="fa-solid fa-chevron-down text-xs"></i>
                        </div>
                    </div>
                </div>

                <div class="mb-4">
                    <label class="block font-semibold text-xs text-slate-700 mb-1.5">
                        Teks Butir / Pertanyaan / Perintah <span class="text-rose-500">*</span>
                    </label>
                    <textarea name="question_text" rows="3" class="muk-input-field leading-relaxed resize-y" placeholder="Tuliskan butir tugas atau pertanyaan instrumen..." required></textarea>
                </div>

                <div class="mb-5">
                    <label class="block font-semibold text-xs text-slate-700 mb-1.5">
                        Rujukan Penilaian / Standar Keberterimaan
                    </label>
                    <textarea name="correct_answer" rows="2" class="muk-input-field leading-relaxed resize-y" placeholder="Poin jawaban rujukan atau kriteria pemenuhan bukti..."></textarea>
                </div>

                <div class="flex items-center justify-end pt-2 border-t border-slate-100">
                    <button type="submit" class="inline-flex items-center gap-2 px-5 py-2.5 rounded-xl bg-blue-600 hover:bg-blue-700 active:bg-blue-800 text-white font-semibold text-xs shadow-xs transition-all cursor-pointer">
                        <i class="fa-solid fa-floppy-disk text-xs"></i>
                        <span>Simpan Butir</span>
                    </button>
                </div>
            </form>
        </div>

        <div class="mb-8">
            <div class="flex items-center justify-between mb-4">
                <div class="flex items-center gap-2.5">
                    <h3 class="text-base font-bold text-slate-900 tracking-tight">
                        Daftar Butir Instrumen
                    </h3>
                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-bold bg-slate-100 text-slate-700 border border-slate-200/60">
                        {{ $instrument->questionBanks->count() }} Butir
                    </span>
                </div>
            </div>

            @forelse($instrument->questionBanks as $q)
                <div class="muk-question-card" data-question-id="{{ $q->id }}">
                    <div class="flex items-center justify-between gap-3 mb-3">
                        <div class="flex items-center gap-2.5 flex-wrap">
                            <span class="w-7 h-7 rounded-lg bg-slate-900 text-white font-mono font-bold text-xs flex items-center justify-center shadow-2xs">
                                {{ $q->order }}
                            </span>
                            @if($q->kriteriaUnjukKerja)
                                <span class="inline-flex items-center gap-1 px-2.5 py-1 rounded-lg text-[11px] font-bold text-blue-700 bg-blue-50 border border-blue-200/60">
                                    <i class="fa-solid fa-tag text-[10px]"></i>
                                    <span>KUK {{ $q->kriteriaUnjukKerja->nomor_kuk }}</span>
                                </span>
                            @endif
                        </div>
                        <div>
                            <form action="{{ route('admin.master-muk.soal.destroy', $q->id) }}" method="POST" onsubmit="return konfirmasiHapus(event, this, 'butir #{{ $q->order }}')" class="inline btn-hapus-item">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="inline-flex items-center gap-1 px-2.5 py-1 rounded-lg bg-rose-50 hover:bg-rose-100 text-rose-600 font-semibold text-xs border border-rose-200/70 transition-all cursor-pointer">
                                    <i class="fa-regular fa-trash-can text-[11px]"></i>
                                    <span>Hapus</span>
                                </button>
                            </form>
                        </div>
                    </div>

                    <div class="text-sm font-semibold text-slate-800 leading-relaxed mb-3">
                        {{ $q->question_text }}
                    </div>

                    @if($q->correct_answer)
                        <div class="bg-blue-50/70 border-l-3 border-blue-500 px-3.5 py-2.5 rounded-r-xl text-xs text-blue-900">
                            <strong class="text-blue-950 font-bold block mb-0.5">Rujukan Penilaian:</strong>
                            <span>{{ $q->correct_answer }}</span>
                        </div>
                    @endif
                </div>
            @empty
                <div class="bg-white rounded-2xl border-2 border-dashed border-slate-200/90 p-10 text-center">
                    <div class="w-14 h-14 rounded-2xl bg-slate-100 text-slate-500 flex items-center justify-center mx-auto mb-3 text-xl shadow-2xs">
                        <i class="fa-solid fa-folder-open"></i>
                    </div>
                    <p class="text-sm font-semibold text-slate-600">Belum ada butir yang ditambahkan pada paket instrumen ini.</p>
                </div>
            @endforelse
        </div>
    @endif

</div>

<!-- ========================================================================= -->
<!-- MODAL 1: KLONING / DUPLIKASI PAKET KE SKEMA LAIN -->
<!-- ========================================================================= -->
<div id="modal-clone-muk" class="modal-overlay">
    <div class="modal-box-admin">
        <div class="flex items-center justify-between pb-3.5 mb-4 border-b border-slate-100">
            <div class="flex items-center gap-2.5">
                <div class="w-8 h-8 rounded-lg bg-indigo-50 text-indigo-600 flex items-center justify-center">
                    <i class="fa-regular fa-clone text-xs"></i>
                </div>
                <h3 class="text-base font-bold text-slate-900">
                    Duplikasi Paket Instrumen
                </h3>
            </div>
            <button type="button" onclick="tutupModalClone()" class="w-8 h-8 rounded-lg text-slate-400 hover:text-slate-700 hover:bg-slate-100 flex items-center justify-center transition cursor-pointer">
                <i class="fa-solid fa-xmark text-sm"></i>
            </button>
        </div>

        <p class="text-xs text-slate-500 mb-4 leading-relaxed">
            Seluruh butir soal, kunci rujukan, bobot poin, dan konfigurasi paket ini akan disalin menjadi paket baru pada skema target.
        </p>

        <form action="{{ route('admin.master-muk.clone', $instrument->id) }}" method="POST">
            @csrf

            <div class="mb-4">
                <label class="block font-semibold text-xs text-slate-700 mb-1.5">
                    Target Skema Sertifikasi <span class="text-rose-500">*</span>
                </label>
                <div class="relative">
                    <select name="target_skema_id" class="muk-input-field appearance-none pr-10 cursor-pointer" required>
                        @foreach($skemaList as $sk)
                            <option value="{{ $sk->id }}" {{ $instrument->skema_id == $sk->id ? 'selected' : '' }}>
                                {{ $sk->kode_skema }} - {{ $sk->nama_skema }}
                            </option>
                        @endforeach
                    </select>
                    <div class="absolute inset-y-0 right-0 flex items-center px-3 pointer-events-none text-slate-400">
                        <i class="fa-solid fa-chevron-down text-xs"></i>
                    </div>
                </div>
            </div>

            <div class="mb-5">
                <label class="block font-semibold text-xs text-slate-700 mb-1.5">
                    Judul Paket Baru <span class="text-rose-500">*</span>
                </label>
                <input type="text" name="new_title" value="{{ $instrument->title }} (Salinan)" class="muk-input-field" required>
            </div>

            <div class="flex items-center justify-end gap-2.5 pt-3 border-t border-slate-100">
                <button type="button" onclick="tutupModalClone()" class="px-4 py-2 rounded-xl border border-slate-200 bg-white hover:bg-slate-50 text-slate-700 font-semibold text-xs transition cursor-pointer">
                    Batal
                </button>
                <button type="submit" class="inline-flex items-center gap-1.5 px-4 py-2 rounded-xl bg-indigo-600 hover:bg-indigo-700 text-white font-semibold text-xs shadow-xs transition cursor-pointer">
                    <i class="fa-regular fa-clone text-[11px]"></i>
                    <span>Mulai Duplikasi</span>
                </button>
            </div>
        </form>
    </div>
</div>

<!-- ========================================================================= -->
<!-- MODAL: EDIT PARAMETER SPESIFIKASI MUTU PRODUK (FR.IA.11) -->
<!-- ========================================================================= -->
<div id="modal-edit-spec" class="modal-overlay" style="display: none;">
    <div class="modal-box-admin max-w-lg">
        <div class="flex items-center justify-between pb-3.5 mb-4 border-b border-slate-100">
            <div class="flex items-center gap-2.5">
                <div class="w-8 h-8 rounded-lg bg-amber-50 text-amber-600 flex items-center justify-center font-bold">
                    <i class="fa-solid fa-pen-to-square text-xs"></i>
                </div>
                <div>
                    <h3 class="text-base font-bold text-slate-900">
                        Edit Parameter Spesifikasi
                    </h3>
                    <p class="text-[11px] text-slate-500">Sesuaikan parameter mutu produk dan batas toleransi waktu.</p>
                </div>
            </div>
            <button type="button" onclick="tutupModalEditSpec()" class="w-8 h-8 rounded-lg text-slate-400 hover:text-slate-700 hover:bg-slate-100 flex items-center justify-center transition cursor-pointer">
                <i class="fa-solid fa-xmark text-sm"></i>
            </button>
        </div>

        <form id="form-edit-spec" action="" method="POST">
            @csrf
            <div class="mb-4">
                <label class="block font-semibold text-xs text-slate-700 mb-1.5">
                    Nama Parameter / Fitur Spesifikasi <span class="text-rose-500">*</span>
                </label>
                <input type="text" id="edit_spec_name" name="spec_name" class="muk-input-field" required>
            </div>

            <div class="mb-5">
                <label class="block font-semibold text-xs text-slate-700 mb-1.5">
                    Standar / Batas Toleransi (Waktu) <span class="text-rose-500">*</span>
                </label>
                <input type="time" id="edit_standard_tolerance" name="standard_tolerance" step="1" class="muk-input-field" required>
                <p class="text-[11px] text-slate-400 mt-1">Hanya format waktu yang diizinkan (JJ:MM:DD / JJ:MM).</p>
            </div>

            <div class="flex items-center justify-end gap-2.5 pt-3 border-t border-slate-100">
                <button type="button" onclick="tutupModalEditSpec()" class="px-4 py-2 rounded-xl border border-slate-200 bg-white hover:bg-slate-50 text-slate-700 font-semibold text-xs transition cursor-pointer">
                    Batal
                </button>
                <button type="submit" class="inline-flex items-center gap-1.5 px-4 py-2 rounded-xl bg-amber-600 hover:bg-amber-700 text-white font-semibold text-xs shadow-xs transition cursor-pointer">
                    <i class="fa-regular fa-floppy-disk text-[11px]"></i>
                    <span>Simpan Perubahan</span>
                </button>
            </div>
        </form>
    </div>
</div>

<!-- ========================================================================= -->
<!-- FORM HIDDEN: BULK DELETE SOAL TERPILIH -->
<!-- ========================================================================= -->
<form id="form-bulk-delete-soal" action="{{ route('admin.master-muk.soal.bulk-delete') }}" method="POST" class="hidden">
    @csrf
    @method('DELETE')
    <div id="bulk-delete-ids-container"></div>
</form>

@endsection

@push('js')
    <script>
        let isMukEditMode = {{ $hasItems ? 'false' : 'true' }};

        function applyMukMode() {
            const container = document.getElementById('container-manage-muk');
            const textToggle = document.getElementById('text-toggle-muk');
            const btnToggle = document.getElementById('btn-toggle-edit-muk');
            const bannerLocked = document.getElementById('banner-muk-locked');
            const bannerEditing = document.getElementById('banner-muk-editing');

            if (!isMukEditMode) {
                if (container) container.classList.add('mode-muk-locked');
                if (textToggle) textToggle.textContent = 'Edit Formulir';
                if (btnToggle) {
                    btnToggle.style.background = '#4f46e5';
                }
                if (bannerLocked) bannerLocked.style.display = 'flex';
                if (bannerEditing) bannerEditing.style.display = 'none';

                // Set inputs in metadata forms to readonly
                document.querySelectorAll('.input-metadata-muk').forEach(el => {
                    el.readOnly = true;
                });
            } else {
                if (container) container.classList.remove('mode-muk-locked');
                if (textToggle) textToggle.textContent = 'Kunci Formulir';
                if (btnToggle) {
                    btnToggle.style.background = '#d97706';
                }
                if (bannerLocked) bannerLocked.style.display = 'none';
                if (bannerEditing) bannerEditing.style.display = 'flex';

                document.querySelectorAll('.input-metadata-muk').forEach(el => {
                    el.readOnly = false;
                });
            }
        }

        function toggleEditMuk() {
            isMukEditMode = !isMukEditMode;
            applyMukMode();
        }

        function handleChoiceChange(selectedKey) {
            ['A', 'B', 'C', 'D'].forEach(key => {
                const card = document.getElementById('choice-card-' + key);
                if (card) {
                    if (key === selectedKey) {
                        card.classList.add('is-active-choice');
                    } else {
                        card.classList.remove('is-active-choice');
                    }
                }
            });
        }

        document.addEventListener('DOMContentLoaded', function() {
            applyMukMode();

            // Preview image on file select for PG question
            const imgInput = document.getElementById('pg-image-input');
            const previewWrap = document.getElementById('pg-image-preview-wrap');
            const previewImg = document.getElementById('pg-image-preview');

            if (imgInput && previewWrap && previewImg) {
                imgInput.addEventListener('change', function() {
                    const file = this.files[0];
                    if (file) {
                        const reader = new FileReader();
                        reader.onload = function(e) {
                            previewImg.src = e.target.result;
                            previewWrap.classList.remove('hidden');
                        };
                        reader.readAsDataURL(file);
                    } else {
                        previewWrap.classList.add('hidden');
                    }
                });
            }
        });

        // =========================================================================
        // FITUR PILIH BEBERAPA / SEMUA SOAL & HAPUS MASSAL (BULK DELETE)
        // =========================================================================
        window.togglePilihSemuaSoal = function(masterCheckbox) {
            const isChecked = masterCheckbox ? masterCheckbox.checked : false;
            const itemCheckboxes = document.querySelectorAll('.checkbox-item-soal');
            itemCheckboxes.forEach(cb => {
                cb.checked = isChecked;
                const card = cb.closest('.muk-question-card');
                if (card) {
                    if (isChecked) {
                        card.classList.add('ring-2', 'ring-rose-400', 'bg-rose-50/20', 'border-rose-300');
                    } else {
                        card.classList.remove('ring-2', 'ring-rose-400', 'bg-rose-50/20', 'border-rose-300');
                    }
                }
            });

            // Sinkronkan semua master checkbox jika ada lebih dari satu
            document.querySelectorAll('.checkbox-select-all').forEach(master => {
                master.checked = isChecked;
                master.indeterminate = false;
            });

            updateBulkSelectionState();
        };

        window.updateBulkSelectionState = function() {
            const itemCheckboxes = document.querySelectorAll('.checkbox-item-soal');
            const total = itemCheckboxes.length;
            let checkedCount = 0;

            itemCheckboxes.forEach(cb => {
                const card = cb.closest('.muk-question-card');
                if (cb.checked) {
                    checkedCount++;
                    if (card) {
                        card.classList.add('ring-2', 'ring-rose-400', 'bg-rose-50/20', 'border-rose-300');
                    }
                } else {
                    if (card) {
                        card.classList.remove('ring-2', 'ring-rose-400', 'bg-rose-50/20', 'border-rose-300');
                    }
                }
            });

            // Update counter teks
            document.querySelectorAll('.count-terpilih').forEach(el => {
                el.textContent = checkedCount;
            });

            // Update tombol hapus massal
            document.querySelectorAll('.btn-hapus-bulk').forEach(btn => {
                if (checkedCount > 0) {
                    btn.removeAttribute('disabled');
                    btn.classList.remove('opacity-40', 'cursor-not-allowed');
                } else {
                    btn.setAttribute('disabled', 'disabled');
                    btn.classList.add('opacity-40', 'cursor-not-allowed');
                }
            });

            // Update status checkbox master (Pilih Semua)
            document.querySelectorAll('.checkbox-select-all').forEach(master => {
                if (total === 0 || checkedCount === 0) {
                    master.checked = false;
                    master.indeterminate = false;
                } else if (checkedCount === total) {
                    master.checked = true;
                    master.indeterminate = false;
                } else {
                    master.checked = false;
                    master.indeterminate = true;
                }
            });
        };

        window.hapusSoalTerpilih = function() {
            const selectedCheckboxes = document.querySelectorAll('.checkbox-item-soal:checked');
            const count = selectedCheckboxes.length;

            if (count === 0) {
                if (typeof Swal !== 'undefined') {
                    Swal.fire({
                        icon: 'info',
                        title: 'Tidak Ada Soal Dipilih',
                        text: 'Silakan pilih setidaknya satu butir soal yang ingin dihapus dengan mencentang kotak pilihan.',
                        confirmButtonColor: '#4f46e5',
                        confirmButtonText: 'OK'
                    });
                } else {
                    alert('Silakan pilih setidaknya satu butir soal yang ingin dihapus.');
                }
                return;
            }

            const pesan = `Apakah Anda yakin ingin menghapus ${count} butir soal yang dipilih? Tindakan ini tidak dapat dibatalkan.`;

            if (typeof Swal !== 'undefined') {
                Swal.fire({
                    title: 'Konfirmasi Hapus Massal',
                    text: pesan,
                    icon: 'warning',
                    showCancelButton: true,
                    confirmButtonColor: '#e11d48',
                    cancelButtonColor: '#64748b',
                    confirmButtonText: `Ya, Hapus ${count} Soal`,
                    cancelButtonText: 'Batal',
                    reverseButtons: true,
                    customClass: {
                        popup: 'rounded-2xl shadow-xl border border-slate-100',
                        confirmButton: 'px-4 py-2 rounded-xl font-semibold text-xs text-white shadow-xs',
                        cancelButton: 'px-4 py-2 rounded-xl font-semibold text-xs text-white'
                    }
                }).then((result) => {
                    if (result.isConfirmed) {
                        prosesSubmitBulkDelete(selectedCheckboxes);
                    }
                });
            } else {
                if (confirm(pesan)) {
                    prosesSubmitBulkDelete(selectedCheckboxes);
                }
            }
        };

        function prosesSubmitBulkDelete(selectedCheckboxes) {
            const form = document.getElementById('form-bulk-delete-soal');
            const container = document.getElementById('bulk-delete-ids-container');
            if (!form || !container) return;

            container.innerHTML = '';
            selectedCheckboxes.forEach(cb => {
                const hiddenInput = document.createElement('input');
                hiddenInput.type = 'hidden';
                hiddenInput.name = 'question_ids[]';
                hiddenInput.value = cb.value;
                container.appendChild(hiddenInput);
            });

            form.submit();
        }

        window.konfirmasiHapus = function(e, form, itemName) {
            if (e && e.preventDefault) e.preventDefault();
            if (e && e.stopPropagation) e.stopPropagation();

            const namaItem = itemName || 'item ini';

            if (typeof Swal !== 'undefined') {
                Swal.fire({
                    title: 'Konfirmasi Hapus',
                    text: `Apakah Anda yakin ingin menghapus ${namaItem}? Tindakan ini tidak dapat dibatalkan.`,
                    icon: 'warning',
                    showCancelButton: true,
                    confirmButtonColor: '#e11d48',
                    cancelButtonColor: '#64748b',
                    confirmButtonText: 'Ya, Hapus',
                    cancelButtonText: 'Batal',
                    reverseButtons: true,
                    customClass: {
                        popup: 'rounded-2xl shadow-xl border border-slate-100',
                        confirmButton: 'px-4 py-2 rounded-xl font-semibold text-xs text-white shadow-xs',
                        cancelButton: 'px-4 py-2 rounded-xl font-semibold text-xs text-white'
                    }
                }).then((result) => {
                    if (result.isConfirmed) {
                        form.submit();
                    }
                });
            } else {
                if (confirm(`Hapus ${namaItem}?`)) {
                    form.submit();
                }
            }

            return false;
        };

        window.bukaModalClone = function(e) {
            if (e && e.preventDefault) e.preventDefault();
            if (e && e.stopPropagation) e.stopPropagation();
            const modal = document.getElementById('modal-clone-muk');
            if (modal) {
                modal.classList.add('terbuka', 'is-open');
                modal.style.display = 'flex';
            }
        };

        window.tutupModalClone = function(e) {
            if (e && e.preventDefault) e.preventDefault();
            if (e && e.stopPropagation) e.stopPropagation();
            const modal = document.getElementById('modal-clone-muk');
            if (modal) {
                modal.classList.remove('terbuka', 'is-open');
                modal.style.display = 'none';
            }
        };

        window.bukaModalEditSpec = function(id, name, tolerance, order) {
            const modal = document.getElementById('modal-edit-spec');
            const form = document.getElementById('form-edit-spec');
            if (modal && form) {
                form.action = "{{ url('admin/master-muk/spec') }}/" + id + "/ubah";
                const nameInput = document.getElementById('edit_spec_name');
                const toleranceInput = document.getElementById('edit_standard_tolerance');
                if (nameInput) nameInput.value = name;
                if (toleranceInput) toleranceInput.value = tolerance;
                modal.classList.add('terbuka', 'is-open');
                modal.style.display = 'flex';
            }
        };

        window.tutupModalEditSpec = function(e) {
            if (e && e.preventDefault) e.preventDefault();
            if (e && e.stopPropagation) e.stopPropagation();
            const modal = document.getElementById('modal-edit-spec');
            if (modal) {
                modal.classList.remove('terbuka', 'is-open');
                modal.style.display = 'none';
            }
        };

        // Close on escape key
        document.addEventListener('keydown', function(e) {
            if (e.key === 'Escape') {
                tutupModalClone(e);
                tutupModalEditSpec(e);
            }
        });

        // Close on clicking backdrop
        window.addEventListener('click', function(e) {
            const modalClone = document.getElementById('modal-clone-muk');
            if (e.target === modalClone) tutupModalClone(e);

            const modalEditSpec = document.getElementById('modal-edit-spec');
            if (e.target === modalEditSpec) tutupModalEditSpec(e);
        });
    </script>
@endpush
