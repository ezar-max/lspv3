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
            <a href="{{ route('admin.master-muk.index') }}" 
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
    <!-- BAGIAN 3: FR.IA.07 / FR.IA.03 (PERTANYAAN LISAN / DPL) -->
    <!-- ========================================================================= -->
    @elseif(in_array($codeKey, ['ia_07', 'ia07', 'ia_03', 'ia03']))

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
    <!-- BAGIAN 4: FR.IA.02 (PRAKTIK) & FR.IA.04A (PROYEK) -->
    <!-- ========================================================================= -->
    @elseif(in_array($codeKey, ['ia_02', 'ia02', 'ia_04a', 'ia04a']))

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
                        Editor Skenario Praktik & Format Luaran (FR.IA)
                    </h2>
                    <p class="text-xs text-slate-500">
                        Atur narasi tugas praktik demonstrasi atau proyek, sarana Tempat Uji Kompetensi (TUK), serta luaran berkas yang wajib diserahkan.
                    </p>
                </div>
            </div>

            <form action="{{ route('admin.master-muk.update-metadata', $instrument->id) }}" method="POST">
                @csrf

                <!-- SKENARIO MASALAH / TUGAS -->
                <div class="mb-5">
                    <label class="block font-semibold text-xs text-slate-700 mb-1.5">
                        Skenario Masalah & Kasus Praktik / Batasan Proyek
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
                            Standar / Batas Toleransi Keberterimaan <span class="text-rose-500">*</span>
                        </label>
                        <input type="text" name="standard_tolerance" placeholder="Contoh: < 2.5 Detik pada koneksi standar" class="muk-input-field" required>
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
                            <th class="py-3 px-4 w-20 text-center">Aksi</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100">
                        @forelse($instrument->productSpecifications as $spec)
                            <tr class="hover:bg-slate-50/50 transition">
                                <td class="py-3 px-4 text-center font-bold text-slate-700">{{ $spec->order }}</td>
                                <td class="py-3 px-4 font-semibold text-slate-900">{{ $spec->spec_name }}</td>
                                <td class="py-3 px-4 text-slate-600">{{ $spec->standard_tolerance }}</td>
                                <td class="py-3 px-4 text-center">
                                    <form action="{{ route('admin.master-muk.spec.destroy', $spec->id) }}" method="POST" onsubmit="return konfirmasiHapus(event, this, 'parameter spesifikasi ini')" class="inline btn-hapus-item">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="inline-flex items-center gap-1 px-2.5 py-1 rounded-lg bg-rose-50 hover:bg-rose-100 text-rose-600 font-semibold text-[11px] border border-rose-200/70 transition cursor-pointer">
                                            <i class="fa-regular fa-trash-can"></i>
                                            <span>Hapus</span>
                                        </button>
                                    </form>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="4" class="py-8 text-center text-slate-500 font-medium">Belum ada parameter spesifikasi produk yang ditambahkan.</td>
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
        <div class="bg-white rounded-2xl border border-slate-200/90 shadow-2xs p-6 mb-8">
            <div class="flex items-center gap-3 mb-3">
                <div class="w-10 h-10 rounded-xl bg-blue-50 text-blue-600 border border-blue-100 flex items-center justify-center font-bold">
                    <i class="fa-solid fa-square-check text-base"></i>
                </div>
                <div>
                    <h3 class="text-base font-bold text-slate-900">FR.IA.01 - Ceklis Observasi Aktivitas Praktik</h3>
                    <p class="text-xs text-slate-500">Instrumen ceklis observasi terintegrasi langsung dengan standar kompetensi skema.</p>
                </div>
            </div>

            <p class="text-xs text-slate-600 mb-5 leading-relaxed bg-slate-50 p-3.5 rounded-xl border border-slate-200/70">
                Instrumen FR.IA.01 mengacu langsung pada seluruh Unit Kompetensi, Elemen, dan Kriteria Unjuk Kerja (KUK) pada skema sertifikasi <strong>{{ $instrument->skema->nama_skema ?? '' }}</strong>.
            </p>

            <a href="{{ route('formulir.ia01', ['skema_id' => $instrument->skema_id]) }}" target="_blank" class="inline-flex items-center gap-2 px-4 py-2 bg-blue-600 hover:bg-blue-700 active:bg-blue-800 text-white rounded-xl text-xs font-semibold shadow-xs transition">
                <i class="fa-solid fa-arrow-up-right-from-square text-[11px]"></i>
                <span>Lihat Pratinjau Lembar FR.IA.01</span>
            </a>
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

        // Close on escape key
        document.addEventListener('keydown', function(e) {
            if (e.key === 'Escape') {
                tutupModalClone(e);
            }
        });

        // Close on clicking backdrop
        window.addEventListener('click', function(e) {
            const modalClone = document.getElementById('modal-clone-muk');
            if (e.target === modalClone) tutupModalClone(e);
        });
    </script>
@endpush
