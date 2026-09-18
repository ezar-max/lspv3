@extends('tata-letak.dasbor')

@section('judul', 'Verifikasi Berkas FR.APL.01')

@push('css')
    <style>
        .canvas-signature-pad {
            border: 2px dashed #cbd5e1;
            border-radius: 12px;
            background: #ffffff;
            cursor: crosshair;
            touch-action: none;
        }

        /* =====================================================
           CLEAN COMPACT MODAL DESIGN (NO FULL SCREEN BLUR)
        ===================================================== */
        .modal-overlay-verifikasi {
            position: fixed;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            width: 100vw;
            height: 100vh;
            inset: 0;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 16px;
            background: rgba(15, 23, 42, 0.60);
            z-index: 999999;
        }

        .verification-modal {
            width: 900px;
            max-width: 95vw;
            max-height: 84vh;
            background: #ffffff;
            border: 1px solid #e4e8ee;
            border-radius: 16px;
            overflow: hidden;
            box-shadow:
                0 25px 60px rgba(15, 23, 42, .20),
                0 4px 15px rgba(15, 23, 42, .06);
            display: flex;
            flex-direction: column;
        }

        .modal-header-clean {
            height: 54px;
            padding: 0 20px;
            display: flex;
            align-items: center;
            justify-content: space-between;
            border-bottom: 1px solid #edf0f4;
            flex-shrink: 0;
            background: #ffffff;
        }

        .header-left-clean {
            display: flex;
            align-items: center;
            gap: 10px;
            overflow: hidden;
        }

        .title-clean {
            font-size: 14px;
            font-weight: 700;
            letter-spacing: -.2px;
            color: #172033;
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
        }

        .counter-clean {
            padding: 3px 8px;
            background: #f4f6f8;
            border: 1px solid #e9edf1;
            border-radius: 6px;
            font-size: 11px;
            color: #64748b;
            font-weight: 600;
            white-space: nowrap;
        }

        .header-right-clean {
            display: flex;
            align-items: center;
            gap: 14px;
            flex-shrink: 0;
        }

        .open-tab-clean {
            font-size: 11px;
            color: #2563eb;
            text-decoration: none;
            font-weight: 600;
        }

        .open-tab-clean:hover {
            text-decoration: underline;
        }

        .close-button-clean {
            width: 28px;
            height: 28px;
            display: flex;
            align-items: center;
            justify-content: center;
            border: 0;
            background: transparent;
            color: #94a3b8;
            font-size: 20px;
            cursor: pointer;
            border-radius: 6px;
            transition: .15s;
            line-height: 1;
        }

        .close-button-clean:hover {
            background: #f5f6f8;
            color: #475569;
        }

        .modal-main-clean {
            display: grid;
            grid-template-columns: 56% 44%;
            min-height: 380px;
            max-height: calc(84vh - 110px);
            overflow-y: auto;
        }

        .viewer-clean {
            padding: 16px;
            background: #f8fafc;
            border-right: 1px solid #edf0f4;
            display: flex;
            flex-direction: column;
            justify-content: space-between;
        }

        .image-wrapper-clean {
            position: relative;
            height: 290px;
            background: #111827;
            border-radius: 10px;
            overflow: hidden;
            border: 1px solid #e2e6eb;
            display: flex;
            align-items: center;
            justify-content: center;
        }

        .document-image-clean {
            width: 100%;
            height: 100%;
            object-fit: contain;
            display: block;
        }

        .image-arrow-clean {
            position: absolute;
            top: 50%;
            transform: translateY(-50%);
            width: 32px;
            height: 32px;
            border-radius: 50%;
            border: 1px solid rgba(255,255,255,.5);
            background: rgba(255,255,255,.82);
            color: #334155;
            font-size: 19px;
            cursor: pointer;
            display: flex;
            align-items: center;
            justify-content: center;
            transition: .18s;
            z-index: 10;
        }

        .image-arrow-clean:hover:not(:disabled) {
            background: white;
            box-shadow: 0 4px 12px rgba(0,0,0,.15);
        }

        .arrow-left-clean {
            left: 10px;
        }

        .arrow-right-clean {
            right: 10px;
        }

        .thumbnails-clean {
            display: flex;
            gap: 8px;
            margin-top: 10px;
            overflow-x: auto;
            padding-bottom: 2px;
        }

        .thumbnail-clean {
            position: relative;
            width: 58px;
            height: 44px;
            flex-shrink: 0;
            border-radius: 6px;
            overflow: hidden;
            border: 2px solid transparent;
            background: #e2e8f0;
            cursor: pointer;
            transition: .15s;
        }

        .thumbnail-clean:hover {
            border-color: #cbd5e1;
        }

        .thumbnail-clean.active {
            border-color: #2563eb;
        }

        .thumbnail-clean img {
            width: 100%;
            height: 100%;
            object-fit: cover;
        }

        .status-dot-clean {
            position: absolute;
            right: 3px;
            bottom: 3px;
            width: 9px;
            height: 9px;
            border-radius: 50%;
            border: 1.5px solid white;
        }

        .status-dot-clean.valid {
            background: #10b981;
        }

        .status-dot-clean.revision {
            background: #ef4444;
        }

        .status-dot-clean.pending {
            background: #f59e0b;
        }

        .decision-clean {
            padding: 18px 20px 14px;
            display: flex;
            flex-direction: column;
            justify-content: space-between;
        }

        .eyebrow-clean {
            font-size: 9px;
            text-transform: uppercase;
            letter-spacing: .08em;
            color: #94a3b8;
            font-weight: 700;
            margin-bottom: 4px;
        }

        .file-name-clean {
            font-size: 14px;
            font-weight: 700;
            color: #172033;
            margin-bottom: 16px;
            word-break: break-word;
        }

        .label-clean {
            font-size: 11px;
            font-weight: 600;
            color: #475569;
            margin-bottom: 7px;
        }

        .status-selector-clean {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 6px;
            padding: 3px;
            background: #f3f5f7;
            border: 1px solid #e8ebef;
            border-radius: 8px;
        }

        .status-button-clean {
            height: 35px;
            border: 0;
            border-radius: 6px;
            background: transparent;
            color: #64748b;
            font-size: 11px;
            font-weight: 600;
            cursor: pointer;
            transition: .18s;
            display: flex;
            align-items: center;
            justify-content: center;
        }

        .status-button-clean:hover {
            color: #334155;
        }

        .status-button-clean.selected-valid {
            background: white;
            color: #059669;
            box-shadow: 0 1px 3px rgba(15,23,42,.08);
            font-weight: 700;
        }

        .status-button-clean.selected-revision {
            background: white;
            color: #d97706;
            box-shadow: 0 1px 3px rgba(15,23,42,.08);
            font-weight: 700;
        }

        .revision-box-clean {
            margin-top: 12px;
        }

        .note-header-clean {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 6px;
        }

        .note-required-clean {
            font-size: 10px;
            color: #d97706;
            font-weight: 600;
        }

        .note-wrapper-clean {
            position: relative;
        }

        .note-input-clean {
            width: 100%;
            min-height: 80px;
            resize: none;
            padding: 8px 10px 22px;
            border-radius: 8px;
            border: 1px solid #dce2e9;
            background: #fbfcfd;
            outline: none;
            font-family: inherit;
            font-size: 11px;
            line-height: 1.5;
            color: #334155;
            transition: .18s;
        }

        .note-input-clean::placeholder {
            color: #a1acba;
        }

        .note-input-clean:focus {
            background: white;
            border-color: #f59e0b;
            box-shadow: 0 0 0 3px rgba(245,158,11,.08);
        }

        .note-count-clean {
            position: absolute;
            right: 8px;
            bottom: 6px;
            font-size: 10px;
            color: #a0aaba;
            pointer-events: none;
        }

        .next-button-clean {
            width: 100%;
            height: 35px;
            margin-top: 12px;
            border: 1px solid #dfe5ec;
            border-radius: 8px;
            background: #ffffff;
            color: #475569;
            font-size: 11px;
            font-weight: 600;
            cursor: pointer;
            transition: .18s;
        }

        .next-button-clean:hover {
            background: #f8fafc;
            border-color: #cbd5e1;
        }

        .modal-footer-clean {
            min-height: 56px;
            height: 56px;
            padding: 10px 18px;
            border-top: 1px solid #edf0f4;
            display: flex;
            align-items: center;
            gap: 18px;
            flex-shrink: 0;
            background: #ffffff;
        }

        .progress-section-clean {
            flex: 1;
        }

        .progress-header-clean {
            display: flex;
            align-items: center;
            margin-bottom: 6px;
        }

        .progress-title-clean {
            font-size: 11px;
            color: #475569;
            font-weight: 600;
        }

        .progress-count-clean {
            margin-left: 8px;
            font-size: 11px;
            color: #94a3b8;
        }

        .summary-clean {
            margin-left: auto;
            display: flex;
            gap: 10px;
            font-size: 10px;
            font-weight: 600;
        }

        .valid-summary-clean {
            color: #059669;
        }

        .revision-summary-clean {
            color: #dc2626;
        }

        .progress-track-clean {
            width: 100%;
            height: 4px;
            border-radius: 20px;
            background: #e8edf2;
            overflow: hidden;
        }

        .progress-fill-clean {
            height: 100%;
            border-radius: inherit;
            background: #10b981;
            transition: .3s;
        }

        .save-button-clean {
            height: 36px;
            min-width: 190px;
            padding: 0 16px;
            border: 0;
            border-radius: 8px;
            background: #2563eb;
            color: white;
            font-size: 11px;
            font-weight: 700;
            cursor: pointer;
            transition: .18s;
        }

        .save-button-clean:hover {
            background: #1d4ed8;
            box-shadow: 0 4px 12px rgba(37,99,235,.20);
        }

        @media(max-width: 850px) {
            .modal-overlay-verifikasi { padding: 10px; }
            .modal-main-clean { grid-template-columns: 1fr; }
            .viewer-clean { border-right: 0; border-bottom: 1px solid #edf0f4; }
            .modal-footer-clean { flex-direction: column; height: auto; min-height: auto; align-items: stretch; gap: 10px; }
            .save-button-clean { width: 100%; }
        }

        @media(max-width: 550px) {
            .modal-header-clean { padding: 0 12px; }
            .counter-clean, .open-tab-clean, .summary-clean { display: none; }
            .viewer-clean, .decision-clean { padding: 12px; }
            .image-wrapper-clean { height: 240px; }
            .modal-footer-clean { padding: 10px 12px; }
        }
    </style>
@endpush

@section('konten')
@php
    $docsData = $pendaftaran->dokumen->map(function($d, $index) {
        $filePath = (string) $d->file_path;
        $fileName = (string) ($d->nama_dokumen ?: '');
        $checkStr = strtolower($filePath . ' ' . $fileName);
        $isImg = Str::contains($checkStr, ['.jpg', '.jpeg', '.png', '.webp', '.gif', '.svg', '.jfif']);

        if (Str::startsWith($filePath, ['http://', 'https://', 'data:'])) {
            $url = $filePath;
        } else {
            $cleanPath = ltrim($filePath, '/');
            if (Str::startsWith($cleanPath, 'storage/')) {
                $url = asset($cleanPath);
            } else {
                $url = asset('storage/' . $cleanPath);
            }
        }

        return [
            'id' => (string)$d->id,
            'index' => $index,
            'jenis' => $d->jenis_dokumen,
            'nama' => $d->nama_dokumen,
            'url' => $url,
            'isImage' => $isImg,
            'status' => $d->status_verifikasi ?? 'menunggu',
            'catatan' => $d->catatan ?? '',
        ];
    })->values();

    $initialStatuses = [];
    $initialNotes = [];
    foreach ($pendaftaran->dokumen as $d) {
        $initialStatuses[$d->id] = $d->status_verifikasi ?? 'menunggu';
        $initialNotes[$d->id] = $d->catatan ?? '';
    }

    $ttdAdminRaw = auth()->user()->tanda_tangan ?: $pendaftaran->tanda_tangan_admin;
    $srcAdmin = $ttdAdminRaw ? (Str::startsWith($ttdAdminRaw, ['data:image', 'http://', 'https://']) ? $ttdAdminRaw : asset($ttdAdminRaw)) : '';

    $noHp = $pendaftaran->asesi->no_telepon ?: ($pendaftaran->no_hp ?: '-');
    $cleanPhone = preg_replace('/[^0-9]/', '', $noHp);
    if (Str::startsWith($cleanPhone, '0')) {
        $waPhone = '62' . substr($cleanPhone, 1);
    } else {
        $waPhone = $cleanPhone;
    }
    $isVerified = ($pendaftaran->status_pendaftaran === 'diverifikasi');
@endphp

<div class="max-w-6xl mx-auto space-y-6 pb-20"
     x-data="{
        isVerified: {{ $isVerified ? 'true' : 'false' }},
        documents: {{ json_encode($docsData) }},
        docStatuses: {{ json_encode($initialStatuses) }},
        docNotes: {{ json_encode($initialNotes) }},
        galleryOpen: false,
        activeDocIndex: 0,
        ttdAdmin: '{{ $srcAdmin }}',

        init() {
            this.$watch('galleryOpen', val => {
                document.body.style.overflow = val ? 'hidden' : '';
            });
            this.$watch('hasInvalidDoc', val => {
                if (val) {
                    const sel = document.getElementById('select-jadwal-asesmen');
                    if (sel) sel.value = '';
                }
            });
            if (this.hasInvalidDoc) {
                this.$nextTick(() => {
                    const sel = document.getElementById('select-jadwal-asesmen');
                    if (sel) sel.value = '';
                });
            }
        },

        get currentDoc() {
            return this.documents[this.activeDocIndex] || {};
        },

        get validCount() {
            return Object.values(this.docStatuses).filter(s => s === 'valid').length;
        },

        get invalidCount() {
            return Object.values(this.docStatuses).filter(s => s === 'tidak_valid').length;
        },

        get pendingCount() {
            return Object.values(this.docStatuses).filter(s => s === 'menunggu').length;
        },

        get reviewedCount() {
            return this.validCount + this.invalidCount;
        },

        get progressPercentage() {
            if (this.documents.length === 0) return 0;
            return Math.round((this.reviewedCount / this.documents.length) * 100);
        },

        get hasInvalidDoc() {
            return this.invalidCount > 0;
        },

        get allValid() {
            return this.documents.length > 0 && this.validCount === this.documents.length;
        },

        openReview(index = 0) {
            this.activeDocIndex = index;
            this.galleryOpen = true;
            document.body.style.overflow = 'hidden';
        },

        closeReview() {
            this.galleryOpen = false;
            document.body.style.overflow = '';
        },

        setDocStatus(id, status) {
            if (this.isVerified) return;
            this.docStatuses[id] = status;
        },

        prevDoc() {
            if (this.activeDocIndex > 0) {
                this.activeDocIndex--;
            }
        },

        nextDoc() {
            if (this.activeDocIndex < this.documents.length - 1) {
                this.activeDocIndex++;
            }
        },

        setAllValid() {
            if (this.isVerified) return;
            for (let id in this.docStatuses) {
                this.docStatuses[id] = 'valid';
            }
        }
     }">

    <!-- =========================================================================
         TOP NAVIGATION & HEADER BAR
         ========================================================================= -->
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
        <a href="{{ route('admin.verifikasi-berkas') }}" 
           class="inline-flex items-center gap-2 text-xs font-semibold text-slate-600 hover:text-slate-900 bg-white border border-slate-200 px-4 py-2.5 rounded-xl shadow-xs transition-colors w-fit">
            <span>&larr;</span> Kembali ke Antrean Verifikasi
        </a>

        <div class="flex items-center gap-2 text-xs">
            <span class="px-3 py-1 bg-blue-50 text-blue-700 border border-blue-200/80 rounded-full font-bold uppercase tracking-wider">
                Pendaftar #{{ $nomorUrutPendaftaran ?? 1 }} dari {{ $totalPendaftarSkema ?? 1 }}
            </span>
            <span class="px-3 py-1 font-semibold rounded-full border {{ $pendaftaran->status_pendaftaran === 'diverifikasi' ? 'bg-emerald-50 text-emerald-700 border-emerald-200' : ($pendaftaran->status_pendaftaran === 'revisi' ? 'bg-rose-50 text-rose-700 border-rose-200' : 'bg-amber-50 text-amber-700 border-amber-200') }}">
                Status: {{ strtoupper($pendaftaran->status_pendaftaran) }}
            </span>
        </div>
    </div>


    <!-- NOTIFIKASI PERMOHONAN PERBAIKAN DARI ASESI -->
    @if($pendaftaran->request_perbaikan)
        <div class="bg-amber-50 border border-amber-300/80 rounded-2xl p-5 text-amber-900 text-sm space-y-1.5 shadow-xs">
            <div class="font-bold text-amber-950 flex items-center gap-2">
                <span class="w-2 h-2 rounded-full bg-amber-600"></span> Permohonan Perbaikan Biodata oleh Asesi
            </div>
            <p class="text-xs sm:text-sm text-amber-800 leading-relaxed">
                "{{ $pendaftaran->catatan_request_perbaikan }}"
            </p>
            <p class="text-xs text-amber-700/80 pt-1">
                * Anda dapat mengembalikan status pendaftaran ke <strong>Revisi</strong> agar peserta dapat memperbarui data biodatanya.
            </p>
        </div>
    @endif

    <!-- MAIN FORM VERIFIKASI -->
    <form id="form-verifikasi-admin" action="{{ route('admin.verifikasi.simpan', $pendaftaran->id) }}" method="POST" class="space-y-6">
        @csrf
        <input type="hidden" name="tanda_tangan_admin_base64" id="input-ttd-admin-base64" value="{{ auth()->user()->tanda_tangan ?: $pendaftaran->tanda_tangan_admin }}">

        <!-- Dynamic Hidden Inputs for Document Status & Notes -->
        <template x-for="doc in documents" :key="doc.id">
            <div>
                <input type="hidden" :name="'verifikasi_dokumen[' + doc.id + ']'" :value="docStatuses[doc.id]">
                <input type="hidden" :name="'catatan_dokumen[' + doc.id + ']'" :value="docNotes[doc.id]">
            </div>
        </template>

        <!-- =====================================================================
             CARD 1: RINGKASAN DATA PEMOHON & DATA SERTIFIKASI (GRID 2 KOLOM)
             ===================================================================== -->
        <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
            <!-- Kolom Kiri: Data Pemohon -->
            <div class="bg-white rounded-2xl border border-slate-200/90 shadow-xs overflow-hidden">
                <div class="bg-slate-50/90 px-5 py-3.5 border-b border-slate-200/80 flex items-center justify-between">
                    <h3 class="font-bold text-slate-800 text-sm">Data Pemohon (FR.APL.01)</h3>
                    <span class="text-[11px] font-bold text-slate-400 uppercase">Bagian 1</span>
                </div>
                <div class="p-5 space-y-3.5 text-xs text-slate-600">
                    <div class="flex justify-between items-start gap-4 pb-2 border-b border-slate-100">
                        <span class="text-slate-400 font-medium">Nama Lengkap</span>
                        <span class="font-bold text-slate-900 text-sm text-right">{{ $pendaftaran->asesi->nama_lengkap }}</span>
                    </div>
                    <div class="flex justify-between items-center gap-4 pb-2 border-b border-slate-100">
                        <span class="text-slate-400 font-medium">NIK / No. KTP</span>
                        <span class="font-mono font-bold text-slate-800">{{ $pendaftaran->asesi->profilAsesi?->nik ?? '-' }}</span>
                    </div>
                    <div class="flex justify-between items-center gap-4 pb-2 border-b border-slate-100">
                        <span class="text-slate-400 font-medium">Tempat, Tanggal Lahir</span>
                        <span class="font-semibold text-slate-800 text-right">
                            {{ $pendaftaran->asesi->profilAsesi?->tempat_lahir ?? '-' }}, 
                            {{ $pendaftaran->asesi->profilAsesi?->tanggal_lahir ? date('d M Y', strtotime($pendaftaran->asesi->profilAsesi->tanggal_lahir)) : '-' }}
                        </span>
                    </div>
                    <div class="flex justify-between items-center gap-4 pb-2 border-b border-slate-100">
                        <span class="text-slate-400 font-medium">Jenis Kelamin</span>
                        <span class="font-semibold text-slate-800">
                            {{ $pendaftaran->asesi->profilAsesi?->jenis_kelamin ? (in_array(strtolower($pendaftaran->asesi->profilAsesi->jenis_kelamin), ['l', 'laki-laki']) ? 'Laki-laki' : 'Perempuan') : '-' }}
                        </span>
                    </div>
                    <div class="flex justify-between items-start gap-4 pb-2 border-b border-slate-100">
                        <span class="text-slate-400 font-medium">Alamat Domisili</span>
                        <span class="font-medium text-slate-800 text-right max-w-xs leading-relaxed">
                            {{ $pendaftaran->asesi->profilAsesi?->alamat ?? '-' }} (Kode Pos: {{ $pendaftaran->kode_pos ?? '-' }})
                        </span>
                    </div>
                    <div class="flex justify-between items-start gap-4 pb-2 border-b border-slate-100">
                        <span class="text-slate-400 font-medium">Asal Sekolah / Instansi</span>
                        <span class="font-semibold text-slate-800 text-right">{{ $pendaftaran->asesi->profilAsesi?->nama_sekolah_instansi ?? '-' }}</span>
                    </div>
                    <div class="flex justify-between items-center gap-4 pt-1">
                        <span class="text-slate-400 font-medium">Kontak & WhatsApp</span>
                        <div class="flex items-center gap-2">
                            <span class="font-mono text-slate-800">{{ $noHp }}</span>
                            @if($cleanPhone)
                                <a href="https://wa.me/{{ $waPhone }}" target="_blank" 
                                   class="px-2 py-0.5 rounded-md bg-emerald-50 hover:bg-emerald-100 border border-emerald-200 text-[10px] font-bold text-emerald-700 transition-colors">
                                    Hubungi WA
                                </a>
                            @endif
                        </div>
                    </div>
                </div>
            </div>

            <!-- Kolom Kanan: Data Sertifikasi -->
            <div class="bg-white rounded-2xl border border-slate-200/90 shadow-xs overflow-hidden">
                <div class="bg-slate-50/90 px-5 py-3.5 border-b border-slate-200/80 flex items-center justify-between">
                    <h3 class="font-bold text-slate-800 text-sm">Data Sertifikasi & Registrasi</h3>
                    <span class="text-[11px] font-bold text-slate-400 uppercase">Bagian 2</span>
                </div>
                <div class="p-5 space-y-3.5 text-xs text-slate-600">
                    <div class="flex justify-between items-start gap-4 pb-2 border-b border-slate-100">
                        <span class="text-slate-400 font-medium">Skema Sertifikasi</span>
                        <div class="text-right">
                            <div class="font-bold text-slate-900 text-sm">{{ $pendaftaran->skema->nama_skema }}</div>
                            <div class="font-mono text-[11px] text-blue-600 font-semibold">{{ $pendaftaran->skema->kode_skema }}</div>
                        </div>
                    </div>
                    <div class="flex justify-between items-center gap-4 pb-2 border-b border-slate-100">
                        <span class="text-slate-400 font-medium">No. Registrasi</span>
                        <span class="font-mono font-bold text-slate-800 bg-slate-100 px-2.5 py-1 rounded-lg border border-slate-200">{{ $pendaftaran->nomor_pendaftaran }}</span>
                    </div>
                    <div class="flex justify-between items-center gap-4 pb-2 border-b border-slate-100">
                        <span class="text-slate-400 font-medium">Tujuan Asesmen</span>
                        <span class="px-2.5 py-0.5 bg-blue-50 text-blue-700 border border-blue-200/80 rounded-md font-semibold text-xs">
                            {{ ucfirst($pendaftaran->tujuan_asesmen ?? 'Sertifikasi') }}
                        </span>
                    </div>
                    <div class="flex justify-between items-center gap-4 pb-2 border-b border-slate-100">
                        <span class="text-slate-400 font-medium">Jumlah Unit Kompetensi</span>
                        <span class="font-bold text-slate-800">{{ $pendaftaran->skema->unitKompetensi->count() }} Unit SKKNI</span>
                    </div>
                    <div class="flex justify-between items-center gap-4 pb-2 border-b border-slate-100">
                        <span class="text-slate-400 font-medium">Tanggal Pengajuan</span>
                        <span class="font-semibold text-slate-800">
                            {{ $pendaftaran->created_at ? $pendaftaran->created_at->format('d M Y, H:i') . ' WIB' : '-' }}
                        </span>
                    </div>
                    <div class="flex justify-between items-center gap-4 pt-1">
                        <span class="text-slate-400 font-medium">Email Pemohon</span>
                        <span class="font-semibold text-slate-800">{{ $pendaftaran->asesi->email }}</span>
                    </div>
                </div>
            </div>
        </div>

        <!-- =====================================================================
             CARD MAPA.01: PERENCANAAN AKTIVITAS & PROSES ASESMEN
             ===================================================================== -->
        @php
            $mapa01Master = \App\Models\Mapa01::where('skema_id', $pendaftaran->skema_id)->whereNull('pendaftaran_id')->first();
            $penyusunValMaster = $mapa01Master?->penyusun_validator_tabel ?? [];
            $isMasterValidated = !empty($penyusunValMaster['validator_1']['ttd']) 
                || (($penyusunValMaster['validator_1']['status_validasi'] ?? '') === 'tervalidasi');

            $mapa01Peserta = \App\Models\Mapa01::where('pendaftaran_id', $pendaftaran->id)->first();
            $penyusunValPeserta = $mapa01Peserta?->penyusun_validator_tabel ?? [];
            $isPesertaValidated = !empty($penyusunValPeserta['validator_1']['ttd']) 
                || (($penyusunValPeserta['validator_1']['status_validasi'] ?? '') === 'tervalidasi') 
                || !empty($pendaftaran->tanda_tangan_admin);

            $isMapa01PesertaValidated = $isMasterValidated || $isPesertaValidated;
        @endphp
        <div class="bg-white rounded-2xl border border-slate-200/90 shadow-xs overflow-hidden p-5 flex flex-col sm:flex-row sm:items-center justify-between gap-4">
            <div class="flex items-start gap-3.5">
                <div class="w-10 h-10 rounded-xl bg-indigo-50 border border-indigo-200 flex items-center justify-center text-indigo-700 font-black text-xs shrink-0">
                    MAPA
                </div>
                <div>
                    <div class="flex items-center gap-2 flex-wrap">
                        <h4 class="font-bold text-slate-900 text-sm">FR.MAPA.01 &bull; Perencanaan Aktivitas dan Proses Asesmen</h4>
                        @if($isMapa01PesertaValidated)
                            <span class="px-2.5 py-0.5 rounded-full bg-emerald-50 text-emerald-700 border border-emerald-200 text-[10px] font-bold">
                                ✓ Tervalidasi Admin
                            </span>
                        @else
                            <span class="px-2.5 py-0.5 rounded-full bg-amber-50 text-amber-700 border border-amber-200 text-[10px] font-bold">
                                Menunggu Validasi Admin
                            </span>
                        @endif
                    </div>
                    <p class="text-xs text-slate-500 mt-0.5">
                        @if($isMapa01PesertaValidated)
                            Dokumen acuan perencanaan asesmen BNSP untuk skema ini telah resmi divalidasi oleh Validator Admin LSP.
                        @else
                            Dokumen acuan perencanaan asesmen BNSP untuk peserta ini. Validator Admin LSP dapat meninjau dan mengesahkan secara digital.
                        @endif
                    </p>
                </div>
            </div>
            <div class="flex items-center gap-2 shrink-0">
                <a href="{{ route('asesor.mapa-01', $pendaftaran->id) }}" class="inline-flex items-center gap-1.5 px-3.5 py-2 rounded-xl {{ !$isMapa01PesertaValidated ? 'bg-emerald-600 hover:bg-emerald-700 text-white shadow-2xs' : 'bg-slate-100 hover:bg-slate-200 text-slate-700 border border-slate-300' }} text-xs font-bold transition">
                    <span>{{ !$isMapa01PesertaValidated ? 'Validasi FR.MAPA.01' : 'Lihat Dokumen FR.MAPA.01' }} &rarr;</span>
                </a>
            </div>
        </div>

        <!-- =====================================================================
             CARD 2: DOKUMEN PERSYARATAN & FITUR "PERIKSA & VERIFIKASI BERKAS"
             ===================================================================== -->
        <div class="bg-white rounded-2xl border border-slate-200/90 shadow-xs overflow-hidden">
            <!-- Header Bar Card Dokumen -->
            <div class="bg-slate-50/90 px-6 py-4 border-b border-slate-200/80 flex flex-col sm:flex-row sm:items-center justify-between gap-3">
                <div>
                    <h3 class="font-bold text-slate-900 text-sm sm:text-base">Dokumen Persyaratan & Bukti Portofolio</h3>
                    <p class="text-xs text-slate-500 mt-0.5">
                        Tinjau seluruh berkas dalam pop-up viewer terpadu untuk validasi dokumen dan catatan revisi.
                    </p>
                </div>
                <!-- Single Primary Review Button -->
                <div class="flex items-center gap-2.5 flex-shrink-0">
                    <button type="button" @click="openReview(0)" 
                            class="px-4 py-2 bg-blue-600 hover:bg-blue-700 text-white rounded-xl text-xs font-semibold shadow-xs transition-all flex items-center gap-2">
                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/>
                        </svg>
                        <span>Periksa & Verifikasi Berkas</span>
                    </button>
                </div>
            </div>

            <!-- Tabel Ringkas Dokumen (Tanpa Dropdown / Input Per Baris) -->
            <div class="overflow-x-auto">
                <table class="w-full text-left text-xs">
                    <thead class="bg-[#f0f6fb] text-[#1c2d42] font-bold uppercase tracking-wider border-b border-[#dce7f2]">
                        <tr>
                            <th class="px-5 py-3 w-12 text-center">No</th>
                            <th class="px-5 py-3 min-w-[220px]">Jenis Dokumen & Nama Berkas</th>
                            <th class="px-5 py-3 min-w-[140px]">Format Berkas</th>
                            <th class="px-5 py-3 min-w-[200px]">Status Hasil Verifikasi</th>
                            <th class="px-5 py-3 w-28 text-center">Pratinjau</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100">
                        @forelse($pendaftaran->dokumen as $index => $d)
                            @php
                                $filePath = (string) $d->file_path;
                                $fileName = (string) ($d->nama_dokumen ?: '');
                                $checkStr = strtolower($filePath . ' ' . $fileName);
                                $isImg = Str::contains($checkStr, ['.jpg', '.jpeg', '.png', '.webp', '.gif', '.svg', '.jfif']);

                                if (Str::startsWith($filePath, ['http://', 'https://', 'data:'])) {
                                    $fileUrl = $filePath;
                                } else {
                                    $cleanPath = ltrim($filePath, '/');
                                    if (Str::startsWith($cleanPath, 'storage/')) {
                                        $fileUrl = asset($cleanPath);
                                    } else {
                                        $fileUrl = asset('storage/' . $cleanPath);
                                    }
                                }
                                $ext = strtoupper(pathinfo($d->nama_dokumen ?: $d->file_path, PATHINFO_EXTENSION)) ?: ($isImg ? 'GAMBAR' : 'DOKUMEN');
                            @endphp
                            <tr class="hover:bg-[#f8fbfe] transition-colors cursor-pointer" @click="openReview({{ $index }})">
                                <!-- No -->
                                <td class="px-5 py-3.5 text-center font-bold text-slate-400">
                                    {{ $index + 1 }}
                                </td>

                                <!-- Jenis Dokumen & Thumbnail Mini (36x36px) -->
                                <td class="px-5 py-3.5">
                                    <div class="flex items-center gap-3">
                                        <div class="w-9 h-9 rounded-lg overflow-hidden bg-slate-100 border border-slate-200 flex-shrink-0 flex items-center justify-center shadow-2xs">
                                            @if($isImg)
                                                <img src="{{ $fileUrl }}" alt="{{ $d->jenis_dokumen }}" class="w-full h-full object-cover">
                                            @else
                                                <span class="text-[10px] font-bold text-rose-600">PDF</span>
                                            @endif
                                        </div>
                                        <div class="space-y-0.5 overflow-hidden">
                                            <div class="font-bold text-slate-800 text-xs">{{ $d->jenis_dokumen }}</div>
                                            <div class="text-[11px] text-slate-400 truncate max-w-[240px]" title="{{ $d->nama_dokumen }}">
                                                {{ $d->nama_dokumen }}
                                            </div>
                                        </div>
                                    </div>
                                </td>

                                <!-- Format Dokumen -->
                                <td class="px-5 py-3.5">
                                    <span class="px-2 py-0.5 rounded-md font-mono text-[11px] font-semibold {{ $isImg ? 'bg-blue-50 text-blue-700 border border-blue-200' : 'bg-rose-50 text-rose-700 border border-rose-200' }}">
                                        {{ $ext }}
                                    </span>
                                </td>

                                <!-- Status Hasil Verifikasi dari Modal -->
                                <td class="px-5 py-3.5">
                                    <div>
                                        <template x-if="docStatuses['{{ $d->id }}'] === 'valid'">
                                            <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-lg bg-emerald-50 border border-emerald-200 text-emerald-800 text-[11px] font-semibold">
                                                <span class="w-1.5 h-1.5 rounded-full bg-emerald-600"></span> Valid (Disetujui)
                                            </span>
                                        </template>
                                        <template x-if="docStatuses['{{ $d->id }}'] === 'tidak_valid'">
                                            <div class="space-y-0.5">
                                                <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-lg bg-rose-50 border border-rose-200 text-rose-800 text-[11px] font-semibold">
                                                    <span class="w-1.5 h-1.5 rounded-full bg-rose-600"></span> Perlu Revisi
                                                </span>
                                                <div x-show="docNotes['{{ $d->id }}']" class="text-[11px] text-rose-600 italic truncate max-w-[220px]" x-text="'Catatan: ' + docNotes['{{ $d->id }}']"></div>
                                            </div>
                                        </template>
                                        <template x-if="docStatuses['{{ $d->id }}'] === 'menunggu'">
                                            <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-lg bg-slate-100 border border-slate-200 text-slate-600 text-[11px] font-medium">
                                                <span class="w-1.5 h-1.5 rounded-full bg-slate-400"></span> Menunggu Verifikasi
                                            </span>
                                        </template>
                                    </div>
                                </td>

                                <!-- Tombol Aksi Buka Viewer -->
                                <td class="px-5 py-3.5 text-center">
                                    <button type="button" @click.stop="openReview({{ $index }})" 
                                            class="px-3 py-1.5 rounded-lg border border-slate-200 bg-white hover:bg-slate-100 text-slate-700 font-semibold text-xs shadow-2xs transition-colors">
                                        Periksa
                                    </button>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="5" class="px-6 py-8 text-center text-slate-400 text-xs">
                                    Asesi belum mengunggah dokumen persyaratan apapun pada permohonan ini.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <!-- Mini Summary Footer -->
            <div class="bg-slate-50/80 px-6 py-3 border-t border-slate-200/80 flex flex-col sm:flex-row sm:items-center justify-between gap-2 text-xs text-slate-500">
                <div class="flex items-center gap-3">
                    <span>Ringkasan Verifikasi:</span>
                    <span class="font-bold text-emerald-700" x-text="validCount + ' Dokumen Valid'"></span>
                    <span>&bull;</span>
                    <span class="font-bold text-rose-700" x-text="invalidCount + ' Perlu Revisi'"></span>
                    <span>&bull;</span>
                    <span class="font-medium text-slate-500" x-text="pendingCount + ' Belum Diperiksa'"></span>
                </div>
                <button type="button" @click="setAllValid()" x-show="!isVerified" class="text-emerald-700 hover:text-emerald-800 font-bold hover:underline">
                    Set Semua Valid
                </button>
            </div>
        </div>

        <!-- =====================================================================
             CARD 3: PENETAPAN JADWAL, TUK, DAN ASESOR PENGUJI
             ===================================================================== -->
        <div class="bg-white rounded-2xl border border-slate-200/90 shadow-xs p-6 space-y-4 transition-all duration-200"
             :class="{ 'border-amber-300 bg-amber-50/20': hasInvalidDoc }">
            <div class="border-b border-slate-100 pb-3 flex flex-col sm:flex-row sm:items-center justify-between gap-2">
                <div>
                    <div class="flex items-center gap-2">
                        <h3 class="font-bold text-slate-900 text-sm sm:text-base">Penempatan Sesi Jadwal Uji & Asesor Penguji</h3>
                        <span x-show="hasInvalidDoc" style="display: none;" 
                              class="inline-flex items-center px-2.5 py-0.5 rounded-full text-[11px] font-bold bg-amber-100 text-amber-800 border border-amber-300">
                            Terkunci (Ada Dokumen Perlu Revisi)
                        </span>
                    </div>
                    <p class="text-xs text-slate-500 mt-0.5">
                        Kapasitas standar: <strong>Maksimal 10 asesi per asesor per hari</strong>. Rekomendasi penempatan antrean: Sesi Hari ke-{{ ceil(($nomorUrutPendaftaran ?? 1) / 10) }}.
                    </p>
                </div>
            </div>

            <div class="pt-1">
                @if($isVerified)
                    <div class="p-4 bg-slate-50 border border-slate-200 rounded-xl space-y-1">
                        <div class="text-xs text-slate-500 font-medium">Sesi Jadwal Asesmen:</div>
                        <div class="font-bold text-slate-900 text-sm">
                            [{{ $pendaftaran->jadwal ? date('d M Y', strtotime($pendaftaran->jadwal->tanggal_uji)) : 'Belum Ditentukan' }}] {{ $pendaftaran->jadwal->nama_tuk ?? '-' }}
                        </div>
                        <div class="text-xs text-slate-600">
                            Asesor Penguji: <strong>{{ $pendaftaran->asesor->nama_lengkap ?? ($pendaftaran->jadwal->asesor->nama_lengkap ?? 'Belum Ditugaskan') }}</strong>
                            @if($pendaftaran->asesor && $pendaftaran->asesor->skema)
                                <span class="ml-1 text-[11px] font-semibold text-emerald-700 bg-emerald-50 border border-emerald-200 px-2 py-0.5 rounded-md">
                                    {{ $pendaftaran->asesor->skema->nama_skema }}
                                </span>
                            @endif
                        </div>
                    </div>
                    <input type="hidden" name="jadwal_id" value="{{ $pendaftaran->jadwal_id }}">
                    <input type="hidden" name="asesor_id" value="{{ $pendaftaran->asesor_id }}">
                @else
                    <!-- Banner Peringatan saat Terkunci karena Ada Berkas Perlu Revisi -->
                    <div x-show="hasInvalidDoc" style="display: none;" 
                         class="p-3.5 bg-amber-50 border border-amber-200 rounded-xl text-xs text-amber-800 flex items-start gap-2.5 mb-3">
                        <div class="space-y-0.5">
                            <p class="font-bold text-amber-900">Penempatan Sesi Jadwal & Asesor Belum Dapat Diisi</p>
                            <p class="text-[11px] text-amber-700 leading-relaxed">
                                Terdapat <span class="font-bold text-rose-700" x-text="invalidCount + ' berkas dokumen'"></span> yang ditandai <strong>Perlu Revisi</strong>. Bagian penempatan sesi jadwal dan asesor ini dinonaktifkan sementara dan baru dapat diisi setelah seluruh berkas dokumen berstatus valid / tidak ada revisi.
                            </p>
                        </div>
                    </div>

                    <!-- Dropdown Tunggal: Sesi Jadwal Asesmen & Asesor Penguji -->
                    <div class="space-y-1.5">
                        <label class="block text-xs font-bold transition-colors" :class="hasInvalidDoc ? 'text-slate-400' : 'text-slate-800'">
                            Pilih Sesi Jadwal Uji & Asesor Penguji: <span class="text-rose-600" x-show="!hasInvalidDoc">*</span>
                        </label>
                        <select name="jadwal_id" id="select-jadwal-asesmen" 
                                :required="!hasInvalidDoc"
                                :disabled="hasInvalidDoc"
                                :class="{
                                    'bg-slate-100/80 text-slate-400 border-slate-200 cursor-not-allowed select-none': hasInvalidDoc,
                                    'bg-white text-slate-800 border-slate-300 focus:ring-2 focus:ring-blue-500': !hasInvalidDoc
                                }"
                                class="w-full text-xs font-semibold rounded-xl border px-3.5 py-2.5 outline-hidden transition-colors">
                            <option value="">-- Pilih Sesi Jadwal & Asesor Penguji --</option>
                            @foreach($jadwalList as $j)
                                @php
                                    $namaAsesor = $j->asesor->nama_lengkap ?? 'Asesor Belum Ditugaskan';
                                    $tglUji = date('d M Y', strtotime($j->tanggal_uji));
                                    $isSelected = ($pendaftaran->jadwal_id == $j->id);
                                @endphp
                                <option value="{{ $j->id }}" {{ $isSelected ? 'selected' : '' }} {{ ($j->is_penuh && !$isSelected) ? 'disabled' : '' }}>
                                    [{{ $tglUji }}] {{ $j->nama_tuk }} — Asesor: {{ $namaAsesor }} (Slot: {{ $j->terisi }}/{{ $j->kuota }} Asesi {{ $j->is_penuh ? '- PENUH' : '' }})
                                </option>
                            @endforeach
                        </select>
                        <div x-show="!hasInvalidDoc">
                            @if($jadwalList->isEmpty())
                                <div class="p-3 bg-amber-50 border border-amber-200 rounded-xl text-xs text-amber-800 flex items-start gap-2.5 mt-2">
                                    <div>
                                        <span class="font-bold text-amber-900">Belum Ada Sesi Jadwal dengan Asesor untuk Skema Ini:</span>
                                        <p class="text-[11px] text-amber-700 mt-0.5 leading-relaxed">
                                            Tidak ditemukan jadwal uji dengan asesor berlisensi skema <strong>{{ $pendaftaran->skema->nama_skema ?? '-' }}</strong>. 
                                            Silakan buat jadwal baru di menu <a href="{{ route('admin.manajemen-jadwal') }}" target="_blank" class="font-bold underline text-amber-900 hover:text-amber-950">Manajemen Jadwal</a> terlebih dahulu.
                                        </p>
                                    </div>
                                </div>
                            @elseif($semuaJadwalPenuh)
                                <div class="p-3 bg-rose-50 border border-rose-200 rounded-xl text-xs text-rose-800 flex items-start gap-2.5 mt-2">
                                    <div>
                                        <span class="font-bold text-rose-900">Seluruh Sesi Jadwal Sudah Penuh:</span>
                                        <p class="text-[11px] text-rose-700 mt-0.5 leading-relaxed">
                                            Seluruh ({{ $jadwalList->count() }}) jadwal uji untuk skema <strong>{{ $pendaftaran->skema->nama_skema ?? '-' }}</strong> kuotanya telah terisi penuh. 
                                            Silakan tambah kuota pada jadwal yang ada atau buat sesi jadwal baru di menu <a href="{{ route('admin.manajemen-jadwal') }}" target="_blank" class="font-bold underline text-rose-900 hover:text-rose-950">Manajemen Jadwal</a> terlebih dahulu.
                                        </p>
                                    </div>
                                </div>
                            @else
                                <p class="text-[11px] text-emerald-700 font-medium mt-1">
                                    Menampilkan {{ $jadwalList->count() }} sesi jadwal dengan asesor role skema {{ $pendaftaran->skema->kode_skema ?? '' }}.
                                </p>
                            @endif
                        </div>
                    </div>
                @endif
            </div>
        </div>

        <!-- =====================================================================
             CARD 4: PENGESAHAN & TANDA TANGAN DIGITAL VERIFIKATOR ADMIN
             ===================================================================== -->
        <div class="bg-white rounded-2xl border border-slate-200/90 shadow-xs p-6 space-y-6">
            <div class="border-b border-slate-100 pb-3">
                <h3 class="font-bold text-slate-900 text-sm sm:text-base">Pengesahan & Tanda Tangan Verifikator Admin</h3>
                <p class="text-xs text-slate-500 mt-0.5">
                    Tanda tangan verifikator admin wajib tersimpan sebelum dapat menyetujui (ACC) permohonan sertifikasi.
                </p>
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-2 gap-5">
                <!-- TTD Asesi -->
                <div class="p-4 rounded-xl border border-slate-200 bg-slate-50/60 text-center space-y-2">
                    <span class="text-xs font-bold text-slate-700 block">Tanda Tangan Pemohon (Asesi):</span>
                    @if($pendaftaran->tanda_tangan_asesi)
                        <img src="{{ Str::startsWith($pendaftaran->tanda_tangan_asesi, 'data:') ? $pendaftaran->tanda_tangan_asesi : asset($pendaftaran->tanda_tangan_asesi) }}" 
                             alt="TTD Asesi" class="max-h-20 mx-auto border border-slate-200 rounded-lg p-1 bg-white">
                        <span class="text-[11px] font-bold text-emerald-700 block">✓ Tanda Tangan Sah</span>
                    @else
                        <div class="text-xs text-rose-600 italic py-4">Belum ada tanda tangan asesi</div>
                    @endif
                </div>

                <!-- TTD Admin -->
                <div class="p-4 rounded-xl border border-slate-200 bg-slate-50/60 text-center space-y-2">
                    <span class="text-xs font-bold text-slate-700 block">Tanda Tangan Verifikator Admin:</span>
                    <div>
                        <img id="preview-ttd-admin-img" 
                             :src="ttdAdmin" 
                             alt="TTD Admin" 
                             class="max-h-20 mx-auto border border-slate-200 rounded-lg p-1 bg-white" 
                             :class="{ 'hidden': !ttdAdmin }">
                        <div id="pesan-ttd-kosong" 
                             class="text-xs text-rose-600 italic py-2" 
                             :class="{ 'hidden': ttdAdmin }">
                            Tanda tangan admin belum dibuat.
                        </div>
                    </div>
                    @if($isVerified)
                        <span class="text-[11px] font-bold text-emerald-700 block mt-1">✓ Tanda Tangan Verifikator Sah (Tersimpan)</span>
                        <div class="text-[10px] text-slate-400">Diverifikasi: {{ $pendaftaran->tanggal_ttd_admin ? date('d M Y H:i', strtotime($pendaftaran->tanggal_ttd_admin)) : 'Tercatat' }}</div>
                    @else
                        <button type="button" onclick="bukaModalTtdAdmin()" 
                                class="px-3.5 py-1.5 rounded-lg border border-slate-300 bg-white hover:bg-slate-50 text-slate-700 text-xs font-bold shadow-2xs transition-colors">
                            Buat / Ganti Tanda Tangan
                        </button>
                    @endif
                </div>
            </div>

            <!-- Catatan Verifikasi Keseluruhan -->
            <div class="space-y-1.5">
                <label class="block text-xs font-bold text-slate-800">
                    Catatan Keputusan Rekomendasi Admin (Opsional):
                </label>
                <textarea name="catatan_verifikasi" rows="2" 
                          {{ $isVerified ? 'readonly' : '' }}
                          placeholder="Tuliskan catatan tambahan untuk asesi (misal instruksi khusus pelaksanaan uji)..."
                          class="w-full text-xs rounded-xl border border-slate-300 {{ $isVerified ? 'bg-slate-50 text-slate-600 cursor-not-allowed' : 'bg-white text-slate-700 focus:ring-2 focus:ring-blue-500' }} p-3 outline-hidden">{{ $pendaftaran->catatan_verifikasi }}</textarea>
            </div>
        </div>

        <!-- =====================================================================
             CARD 5: ACTION FOOTER / STICKY BOTTOM BAR (BARIS PERSETUJUAN FINAL)
             ===================================================================== -->
        @if($isVerified)
            <div class="bg-white rounded-2xl border border-slate-200/90 shadow-sm p-4 sm:p-5 flex items-center justify-between gap-4">
                <a href="{{ route('admin.verifikasi-berkas') }}" 
                   class="px-5 py-2.5 text-xs font-semibold text-slate-700 bg-white hover:bg-slate-50 border border-slate-300 rounded-xl transition-colors text-center inline-flex items-center gap-2 shadow-2xs">
                    <span>&larr;</span> Kembali ke Antrean Verifikasi
                </a>

                <div class="text-xs font-semibold text-emerald-700 flex items-center">
                    <span>FR.APL.01 Telah Disetujui (ACC)</span>
                </div>
            </div>
        @else
            <div class="bg-white rounded-2xl border border-slate-200/90 shadow-sm p-5 flex flex-col sm:flex-row items-center justify-between gap-4">
                <div class="flex items-center gap-2.5 w-full sm:w-auto">
                    <a href="{{ route('admin.verifikasi-berkas') }}" 
                       class="w-full sm:w-auto px-4 py-2.5 text-xs font-semibold text-slate-600 bg-white hover:bg-slate-50 border border-slate-300 rounded-xl transition-colors text-center">
                        Batal / Kembali
                    </a>

                    <button type="submit" name="status_pendaftaran" value="ditolak" 
                            onclick="return confirm('Peringatan: Menolak pendaftaran akan membatalkan permohonan asesi pada skema ini. Lanjutkan?')"
                            class="w-full sm:w-auto px-4 py-2.5 text-xs font-semibold text-rose-700 bg-white hover:bg-rose-50 border border-rose-200 rounded-xl transition-colors text-center">
                        Tolak Permohonan
                    </button>
                </div>

                <!-- Dynamic Primary Action Button -->
                <div class="w-full sm:w-auto">
                    <!-- If any document is 'tidak_valid': Return for revision -->
                    <template x-if="hasInvalidDoc">
                        <button type="submit" name="status_pendaftaran" value="revisi" 
                                onclick="return confirm('Kirim Catatan Revisi: Pendaftaran ini akan dikembalikan ke Asesi untuk memperbaiki dokumen yang berstatus Tidak Valid. Lanjutkan?')"
                                class="w-full sm:w-auto px-7 py-3 bg-rose-600 hover:bg-rose-700 text-white text-xs sm:text-sm font-bold rounded-xl shadow-xs transition-colors flex items-center justify-center gap-2">
                            <span>Kirim Catatan Revisi ke Asesi</span>
                            <span>&rarr;</span>
                        </button>
                    </template>

                    <!-- If all documents valid: Approve & Unlock APL.02 -->
                    <template x-if="!hasInvalidDoc">
                        <button type="submit" name="status_pendaftaran" value="diverifikasi" 
                                onclick="return validasiSubmitAccAdmin(event)"
                                class="w-full sm:w-auto px-7 py-3 bg-emerald-600 hover:bg-emerald-700 text-white text-xs sm:text-sm font-bold rounded-xl shadow-xs transition-colors flex items-center justify-center gap-2">
                            <span>Setujui Permohonan (APL.01 Selesai / ACC)</span>
                            <span>&rarr;</span>
                        </button>
                    </template>
                </div>
            </div>
        @endif
    </form>

    <!-- =========================================================================
         MODAL POP-UP VIEWER TERPADU (MATCHING EXACT DESIGN TEMPLATE)
         ========================================================================= -->
    <template x-teleport="body">
        <div x-show="galleryOpen" 
             x-transition:enter="transition ease-out duration-200"
             x-transition:enter-start="opacity-0"
             x-transition:enter-end="opacity-100"
             x-transition:leave="transition ease-in duration-150"
             x-transition:leave-start="opacity-100"
             x-transition:leave-end="opacity-0"
             @keydown.escape.window="closeReview()"
             @keydown.arrow-left.window="prevDoc()"
             @keydown.arrow-right.window="nextDoc()"
             style="display: none;"
             class="modal-overlay-verifikasi select-none">

            <div class="verification-modal">
                <!-- HEADER -->
                <header class="modal-header-clean">
                    <div class="header-left-clean">
                        <div class="title-clean" x-text="currentDoc.jenis">
                            Ijazah / Rapor Terakhir
                        </div>
                        <div class="counter-clean" x-text="'Berkas ' + (activeDocIndex + 1) + ' dari ' + documents.length">
                            Berkas 1 dari 4
                        </div>
                    </div>

                    <div class="header-right-clean">
                        <a :href="currentDoc.url" target="_blank" class="open-tab-clean">
                            Buka di Tab Baru ↗
                        </a>
                        <button type="button" class="close-button-clean" @click="closeReview()" title="Tutup Modal (Esc)">
                            &times;
                        </button>
                    </div>
                </header>

                <!-- MAIN -->
                <main class="modal-main-clean">
                    <!-- DOCUMENT VIEWER -->
                    <section class="viewer-clean">
                        <div class="image-wrapper-clean">
                            <template x-if="currentDoc.isImage">
                                <img :src="currentDoc.url" :alt="currentDoc.jenis" class="document-image-clean">
                            </template>
                            <template x-if="!currentDoc.isImage">
                                <iframe :src="currentDoc.url" class="w-full h-full rounded-lg bg-white"></iframe>
                            </template>

                            <button type="button" 
                                    class="image-arrow-clean arrow-left-clean" 
                                    @click="prevDoc()" 
                                    :disabled="activeDocIndex === 0"
                                    :style="activeDocIndex === 0 ? 'opacity: 0.3; cursor: not-allowed;' : ''">
                                &#8249;
                            </button>

                            <button type="button" 
                                    class="image-arrow-clean arrow-right-clean" 
                                    @click="nextDoc()" 
                                    :disabled="activeDocIndex === documents.length - 1"
                                    :style="activeDocIndex === documents.length - 1 ? 'opacity: 0.3; cursor: not-allowed;' : ''">
                                &#8250;
                            </button>
                        </div>

                        <!-- THUMBNAILS -->
                        <div class="thumbnails-clean">
                            <template x-for="(doc, idx) in documents" :key="doc.id">
                                <div class="thumbnail-clean" 
                                     :class="{ 'active': activeDocIndex === idx }" 
                                     @click="activeDocIndex = idx">
                                    <template x-if="doc.isImage">
                                        <img :src="doc.url" :alt="doc.jenis">
                                    </template>
                                    <template x-if="!doc.isImage">
                                        <div class="w-full h-full flex items-center justify-center bg-slate-800 text-[10px] font-bold text-red-400 font-mono">
                                            PDF
                                        </div>
                                    </template>

                                    <span class="status-dot-clean" 
                                          :class="{
                                              'valid': docStatuses[doc.id] === 'valid',
                                              'revision': docStatuses[doc.id] === 'tidak_valid',
                                              'pending': docStatuses[doc.id] === 'menunggu'
                                          }"></span>
                                </div>
                            </template>
                        </div>
                    </section>

                    <!-- DECISION PANEL -->
                    <section class="decision-clean">
                        <div>
                            <div class="eyebrow-clean">
                                Keputusan Verifikasi
                            </div>

                            <div class="file-name-clean" x-text="currentDoc.nama">
                                captured_image.png
                            </div>

                            <!-- STATUS -->
                            <div class="label-clean">
                                Status Kelayakan Dokumen
                            </div>

                            <template x-if="isVerified">
                                <div class="p-3 bg-emerald-50 border border-emerald-200 rounded-xl flex items-center text-xs font-bold text-emerald-800 mb-3">
                                    <span>Dokumen Valid (Telah Terverifikasi)</span>
                                </div>
                            </template>

                            <div class="status-selector-clean" x-show="!isVerified">
                                <button type="button" 
                                        class="status-button-clean" 
                                        :class="{ 'selected-valid': docStatuses[currentDoc.id] === 'valid' }"
                                        @click="setDocStatus(currentDoc.id, 'valid')">
                                    Valid
                                </button>

                                <button type="button" 
                                        class="status-button-clean" 
                                        :class="{ 'selected-revision': docStatuses[currentDoc.id] === 'tidak_valid' }"
                                        @click="setDocStatus(currentDoc.id, 'tidak_valid')">
                                    Perlu Revisi
                                </button>
                            </div>

                            <!-- CATATAN REVISI -->
                            <div class="revision-box-clean" 
                                 x-show="docStatuses[currentDoc.id] === 'tidak_valid' || (isVerified && docNotes[currentDoc.id])" 
                                 x-transition:enter="transition ease-out duration-150"
                                 x-transition:enter-start="opacity-0 -translate-y-2"
                                 x-transition:enter-end="opacity-100 translate-y-0">
                                <div class="note-header-clean">
                                    <div class="label-clean" style="margin:0;">
                                        Catatan Dokumen
                                    </div>
                                    <span class="note-required-clean" x-show="!isVerified">
                                        Wajib diisi
                                    </span>
                                </div>

                                <div class="note-wrapper-clean">
                                    <textarea x-model="docNotes[currentDoc.id]" 
                                              :disabled="isVerified"
                                              maxlength="200" 
                                              class="note-input-clean" 
                                              :class="{ 'bg-slate-50 cursor-not-allowed': isVerified }"
                                              placeholder="Jelaskan bagian dokumen yang perlu diperbaiki..."></textarea>
                                    <div class="note-count-clean" x-show="!isVerified">
                                        <span x-text="(docNotes[currentDoc.id] || '').length">0</span>/200
                                    </div>
                                </div>
                            </div>

                            <!-- NEXT BUTTON -->
                            <template x-if="activeDocIndex < documents.length - 1">
                                <button type="button" class="next-button-clean" @click="nextDoc()">
                                    Lanjut ke Berkas Berikutnya &rarr;
                                </button>
                            </template>
                        </div>
                    </section>
                </main>

                <!-- FOOTER -->
                <footer class="modal-footer-clean">
                    <div class="progress-section-clean">
                        <div class="progress-header-clean">
                            <span class="progress-title-clean">
                                Progress Verifikasi
                            </span>
                            <span class="progress-count-clean" x-text="reviewedCount + ' dari ' + documents.length + ' berkas'">
                                4 dari 4 berkas
                            </span>

                            <div class="summary-clean">
                                <span class="valid-summary-clean" x-text="validCount + ' Valid'">
                                    3 Valid
                                </span>
                                <span class="revision-summary-clean" x-text="invalidCount + ' Revisi'">
                                    1 Revisi
                                </span>
                            </div>
                        </div>

                        <div class="progress-track-clean">
                            <div class="progress-fill-clean" :style="'width: ' + progressPercentage + '%'"></div>
                        </div>
                    </div>

                    <button type="button" class="save-button-clean" @click="closeReview()" x-text="isVerified ? 'Tutup Pratinjau' : 'Terapkan & Simpan Verifikasi'">
                        Terapkan & Simpan Verifikasi
                    </button>
                </footer>
            </div>
        </div>
    </template>
</div>

<!-- =========================================================================
     MODAL CANVAS TTD ADMIN
     ========================================================================= -->
<div id="modal-ttd-admin" style="display: none; position: fixed; inset: 0; background: rgba(0,0,0,0.6); z-index: 9999; align-items: center; justify-content: center; backdrop-filter: blur(4px);">
    <div class="bg-white w-full max-w-lg rounded-3xl p-6 sm:p-8 shadow-2xl border border-slate-200 space-y-4 m-4">
        <div>
            <h3 class="font-bold text-slate-900 text-lg">Buat Tanda Tangan Digital Verifikator</h3>
            <p class="text-xs text-slate-500 mt-0.5">
                Goreskan tanda tangan Anda pada kotak canvas di bawah ini:
            </p>
        </div>

        <div class="text-center">
            <canvas id="canvas-admin-pad" class="canvas-signature-pad" width="440" height="200" style="width: 100%; max-width: 440px; height: 200px;"></canvas>
        </div>

        <div class="flex items-center justify-between gap-3 pt-2">
            <button type="button" onclick="resetCanvasAdmin()" 
                    class="px-4 py-2 rounded-xl border border-slate-300 text-slate-700 bg-white hover:bg-slate-50 text-xs font-semibold transition-colors">
                Bersihkan
            </button>
            <div class="flex items-center gap-2">
                <button type="button" onclick="tutupModalTtdAdmin()" 
                        class="px-4 py-2 rounded-xl border border-slate-200 text-slate-600 bg-white hover:bg-slate-100 text-xs font-semibold transition-colors">
                    Batal
                </button>
                <button type="button" onclick="simpanCanvasTtdAdmin()" 
                        class="px-5 py-2 rounded-xl bg-blue-600 hover:bg-blue-700 text-white text-xs font-bold shadow-xs transition-colors">
                    Simpan Tanda Tangan
                </button>
            </div>
        </div>
    </div>
</div>

@push('js')
<script>
    var totalJadwalTersedia = {{ $jadwalList->count() }};
    var isSemuaJadwalPenuh = {{ $semuaJadwalPenuh ? 'true' : 'false' }};
    var namaSkemaAsesi = {!! json_encode($pendaftaran->skema->nama_skema ?? 'Skema Sertifikasi') !!};
    var namaLengkapAsesi = {!! json_encode($pendaftaran->asesi->nama_lengkap ?? 'Asesi') !!};

    function validasiSubmitAccAdmin(event) {
        if (event) event.preventDefault();

        // 1. Validasi Tanda Tangan Digital Verifikator Admin
        var ttdInput = document.getElementById('input-ttd-admin-base64').value;
        if (!ttdInput) {
            if (typeof Swal !== 'undefined') {
                Swal.fire({
                    icon: 'warning',
                    title: 'Tanda Tangan Belum Ada!',
                    text: 'Tanda tangan Admin LSP wajib dibubuhkan/digambar sebelum Anda dapat menyetujui (ACC) permohonan sertifikasi ini.',
                    confirmButtonColor: '#f59e0b',
                    confirmButtonText: '✓ Buat Tanda Tangan Sekarang',
                    customClass: { popup: 'swal2-modern-popup' }
                }).then(() => {
                    bukaModalTtdAdmin();
                });
            } else {
                alert('Perhatian: Tanda tangan Admin LSP wajib dibuat sebelum Anda dapat menyetujui (ACC) permohonan sertifikasi ini.');
                bukaModalTtdAdmin();
            }
            return false;
        }

        // 2. Validasi Ketersediaan Sesi Jadwal (Belum ada jadwal sama sekali)
        if (totalJadwalTersedia === 0) {
            if (typeof Swal !== 'undefined') {
                Swal.fire({
                    icon: 'warning',
                    title: 'Jadwal Belum Tersedia!',
                    html: 'Permohonan FR.APL.01 belum dapat disetujui (ACC) karena <b>belum ada sesi jadwal uji aktif</b> dengan asesor penguji untuk skema <b>' + namaSkemaAsesi + '</b>.<br><br>Silakan buat sesi jadwal baru terlebih dahulu di menu <b>Manajemen Jadwal</b>.',
                    showCancelButton: true,
                    confirmButtonColor: '#2563eb',
                    cancelButtonColor: '#64748b',
                    confirmButtonText: 'Buka Manajemen Jadwal',
                    cancelButtonText: 'Tutup',
                    customClass: { popup: 'swal2-modern-popup' }
                }).then((result) => {
                    if (result.isConfirmed) {
                        window.open('{{ route("admin.manajemen-jadwal") }}', '_blank');
                    }
                });
            } else {
                alert('Perhatian: Belum ada jadwal uji untuk skema ini. Silakan buat jadwal baru terlebih dahulu.');
            }
            return false;
        }

        // 3. Validasi Seluruh Jadwal Penuh Kuotanya
        if (isSemuaJadwalPenuh) {
            if (typeof Swal !== 'undefined') {
                Swal.fire({
                    icon: 'warning',
                    title: 'Seluruh Jadwal Penuh!',
                    html: 'Permohonan FR.APL.01 belum dapat disetujui (ACC) karena <b>seluruh (' + totalJadwalTersedia + ') sesi jadwal uji untuk skema ini sudah penuh</b> kuotanya.<br><br>Silakan tambah kuota pada jadwal yang ada atau buat sesi jadwal baru di menu <b>Manajemen Jadwal</b> sebelum menyetujui permohonan ini.',
                    showCancelButton: true,
                    confirmButtonColor: '#2563eb',
                    cancelButtonColor: '#64748b',
                    confirmButtonText: 'Buka Manajemen Jadwal',
                    cancelButtonText: 'Tutup',
                    customClass: { popup: 'swal2-modern-popup' }
                }).then((result) => {
                    if (result.isConfirmed) {
                        window.open('{{ route("admin.manajemen-jadwal") }}', '_blank');
                    }
                });
            } else {
                alert('Perhatian: Seluruh sesi jadwal uji untuk skema ini sudah penuh.');
            }
            return false;
        }

        // 4. Validasi Pemilihan Sesi Jadwal
        var selectJadwal = document.getElementById('select-jadwal-asesmen');
        if (selectJadwal && !selectJadwal.value) {
            if (typeof Swal !== 'undefined') {
                Swal.fire({
                    icon: 'warning',
                    title: 'Pilih Jadwal Uji Terlebih Dahulu!',
                    text: 'Harap pilih salah satu Sesi Jadwal Uji & Asesor Penguji yang masih memiliki kuota sebelum menyetujui (ACC) permohonan APL.01.',
                    confirmButtonColor: '#f59e0b',
                    confirmButtonText: '✓ Mengerti',
                    customClass: { popup: 'swal2-modern-popup' }
                }).then(() => {
                    selectJadwal.focus();
                });
            } else {
                alert('Perhatian: Harap pilih Sesi Jadwal Uji & Asesor Penguji untuk menempatkan asesi sebelum menyetujui (ACC).');
                selectJadwal.focus();
            }
            return false;
        }

        // 5. Konfirmasi Persetujuan Final (ACC)
        if (typeof Swal !== 'undefined') {
            Swal.fire({
                title: 'Konfirmasi Persetujuan (ACC)',
                html: 'Apakah Anda yakin ingin menyetujui (ACC) berkas FR.APL.01 untuk asesi <b>' + namaLengkapAsesi + '</b>?<br><br><span style="font-size: 0.85rem; color: #64748b;">Akses formulir asesmen mandiri (FR.APL.02) akan segera dibuka untuk asesi ini.</span>',
                icon: 'question',
                showCancelButton: true,
                confirmButtonColor: '#16a34a',
                cancelButtonColor: '#64748b',
                confirmButtonText: '✓ Ya, Setujui (ACC)',
                cancelButtonText: 'Batal',
                customClass: { popup: 'swal2-modern-popup' }
            }).then((result) => {
                if (result.isConfirmed) {
                    var form = document.getElementById('form-verifikasi-admin');
                    var existingInput = form.querySelector('input[name="status_pendaftaran"]');
                    if (existingInput) {
                        existingInput.value = 'diverifikasi';
                    } else {
                        var hiddenInput = document.createElement('input');
                        hiddenInput.type = 'hidden';
                        hiddenInput.name = 'status_pendaftaran';
                        hiddenInput.value = 'diverifikasi';
                        form.appendChild(hiddenInput);
                    }
                    form.submit();
                }
            });
            return false;
        } else {
            if (confirm('Konfirmasi Persetujuan: Apakah Anda yakin ingin menyetujui (ACC) berkas FR.APL.01 asesi ini dan membuka akses formulir FR.APL.02?')) {
                var form = document.getElementById('form-verifikasi-admin');
                var existingInput = form.querySelector('input[name="status_pendaftaran"]');
                if (existingInput) {
                    existingInput.value = 'diverifikasi';
                } else {
                    var hiddenInput = document.createElement('input');
                    hiddenInput.type = 'hidden';
                    hiddenInput.name = 'status_pendaftaran';
                    hiddenInput.value = 'diverifikasi';
                    form.appendChild(hiddenInput);
                }
                form.submit();
            }
            return false;
        }
    }

    // CANVAS TTD ADMIN LOGIC
    var canvasAdmin = document.getElementById('canvas-admin-pad');
    var ctxAdmin = canvasAdmin ? canvasAdmin.getContext('2d') : null;
    var isDrawingAdmin = false;

    if (canvasAdmin && ctxAdmin) {
        ctxAdmin.strokeStyle = "#0f172a";
        ctxAdmin.lineWidth = 2.5;
        ctxAdmin.lineCap = "round";

        function getCanvasPos(e) {
            var rect = canvasAdmin.getBoundingClientRect();
            var clientX = e.clientX || (e.touches && e.touches[0] ? e.touches[0].clientX : 0);
            var clientY = e.clientY || (e.touches && e.touches[0] ? e.touches[0].clientY : 0);
            return {
                x: clientX - rect.left,
                y: clientY - rect.top
            };
        }

        function startDrawing(e) {
            isDrawingAdmin = true;
            var pos = getCanvasPos(e);
            ctxAdmin.beginPath();
            ctxAdmin.moveTo(pos.x, pos.y);
            if (e.type.startsWith('touch')) e.preventDefault();
        }

        function draw(e) {
            if (!isDrawingAdmin) return;
            var pos = getCanvasPos(e);
            ctxAdmin.lineTo(pos.x, pos.y);
            ctxAdmin.stroke();
            if (e.type.startsWith('touch')) e.preventDefault();
        }

        function stopDrawing() {
            isDrawingAdmin = false;
        }

        canvasAdmin.addEventListener('mousedown', startDrawing);
        canvasAdmin.addEventListener('mousemove', draw);
        canvasAdmin.addEventListener('mouseup', stopDrawing);
        canvasAdmin.addEventListener('mouseleave', stopDrawing);

        canvasAdmin.addEventListener('touchstart', startDrawing, { passive: false });
        canvasAdmin.addEventListener('touchmove', draw, { passive: false });
        canvasAdmin.addEventListener('touchend', stopDrawing);
    }

    function bukaModalTtdAdmin() {
        var modal = document.getElementById('modal-ttd-admin');
        if (modal) {
            modal.style.display = 'flex';
            resetCanvasAdmin();
        }
    }

    function tutupModalTtdAdmin() {
        var modal = document.getElementById('modal-ttd-admin');
        if (modal) modal.style.display = 'none';
    }

    function resetCanvasAdmin() {
        if (ctxAdmin && canvasAdmin) {
            ctxAdmin.clearRect(0, 0, canvasAdmin.width, canvasAdmin.height);
        }
    }

    function simpanCanvasTtdAdmin() {
        if (!canvasAdmin) return;
        var dataUrl = canvasAdmin.toDataURL('image/png');
        document.getElementById('input-ttd-admin-base64').value = dataUrl;

        var imgPreview = document.getElementById('preview-ttd-admin-img');
        var pesanKosong = document.getElementById('pesan-ttd-kosong');

        if (imgPreview) {
            imgPreview.src = dataUrl;
            imgPreview.classList.remove('hidden');
        }
        if (pesanKosong) {
            pesanKosong.classList.add('hidden');
        }

        tutupModalTtdAdmin();
    }
</script>
@endpush
@endsection
