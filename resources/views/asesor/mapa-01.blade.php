@extends('tata-letak.dasbor')

@section('judul', 'FR.MAPA.01 - Perencanaan Aktivitas dan Proses Asesmen')

@push('css')
    <link rel="stylesheet" href="{{ asset('css/formulir/formulir-bnsp.css') }}">
    <style>
        /* ==========================================================================
           MODERN & PROFESSIONAL STYLING FOR FR.MAPA.01 (COMPACT & BALANCED)
           Theme: LSP SMKN 1 Gunungputri (Steel Blue & Midnight Navy)
           ========================================================================== */
        
        .wadah-mapa01 {
            max-width: 1020px;
            margin: 0 auto;
            color: #0f172a;
            font-family: 'Public Sans', -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;
            font-size: 0.85rem;
        }

        /* Top Executive Header Card */
        .header-eksekutif-mapa {
            background: linear-gradient(135deg, #1c2d42 0%, #2a4365 100%);
            color: #ffffff;
            border-radius: 8px;
            padding: 0.85rem 1.15rem;
            margin-bottom: 1rem;
            box-shadow: 0 2px 6px rgba(28, 45, 66, 0.12);
            display: flex;
            justify-content: space-between;
            align-items: center;
            flex-wrap: wrap;
            gap: 0.75rem;
        }

        .header-eksekutif-info h1 {
            font-size: 1.1rem;
            font-weight: 700;
            color: #ffffff;
            margin: 0.15rem 0 0.25rem 0;
            letter-spacing: -0.1px;
        }

        .header-eksekutif-meta {
            display: flex;
            align-items: center;
            gap: 0.75rem;
            font-size: 0.78rem;
            color: #cbd5e1;
            flex-wrap: wrap;
        }

        .header-eksekutif-meta span strong {
            color: #ffffff;
        }

        /* Dokumen Kertas Standar BNSP */
        .dokumen-kertas-mapa {
            background: #ffffff;
            border: 1px solid #cbd5e1;
            border-radius: 8px;
            padding: 1.25rem 1.5rem;
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.04);
            margin-bottom: 1.25rem;
            font-size: 0.85rem;
            line-height: 1.5;
        }

        .kop-logo-wrap img {
            height: 42px !important;
            max-height: 42px !important;
            width: auto !important;
            max-width: 120px !important;
            object-fit: contain !important;
            display: block;
        }

        .kop-lembaga-nama {
            font-size: 1.02rem;
            font-weight: 900;
            color: #0f172a;
            line-height: 1.2;
        }

        .kop-lembaga-sub {
            font-size: 0.72rem;
            font-weight: 700;
            color: #475569;
            letter-spacing: 0.2px;
        }

        .kop-kode-box {
            border: 1.5px solid #0f172a;
            padding: 0.25rem 0.75rem;
            border-radius: 6px;
            text-align: center;
            background: #f8fafc;
            min-width: 110px;
        }

        .kop-kode-utama {
            font-size: 0.95rem;
            font-weight: 900;
            color: #0f172a;
            line-height: 1.2;
        }

        .kop-kode-sub {
            font-size: 0.68rem;
            font-weight: 700;
            color: #475569;
        }

        .judul-form-bnsp {
            font-size: 1rem;
            font-weight: 800;
            color: #0f172a;
            letter-spacing: 0.3px;
            margin-bottom: 0.85rem;
            text-transform: uppercase;
            border-bottom: 2px solid #0f172a;
            padding-bottom: 0.4rem;
        }

        /* Section Header Modern */
        .section-header-modern {
            background: #1c2d42;
            color: #ffffff;
            font-weight: 700;
            font-size: 0.88rem;
            padding: 0.45rem 0.85rem;
            border-radius: 6px 6px 0 0;
            display: flex;
            align-items: center;
            gap: 0.5rem;
            letter-spacing: 0.2px;
        }

        .section-header-modern .badge-num {
            background: #4682b4;
            color: #ffffff;
            width: 22px;
            height: 22px;
            border-radius: 4px;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            font-size: 0.78rem;
            font-weight: 800;
        }

        /* Modern Crisp Table */
        .tabel-mapa-modern {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 1rem;
            background: #ffffff;
            border: 1px solid #cbd5e1;
            font-size: 0.82rem;
        }

        .tabel-mapa-modern th,
        .tabel-mapa-modern td {
            border: 1px solid #cbd5e1;
            padding: 0.45rem 0.65rem;
            vertical-align: top;
        }

        .tabel-mapa-modern th {
            background-color: #f1f5f9;
            font-weight: 700;
            color: #0f172a;
        }

        .tabel-mapa-modern .th-navy {
            background-color: #1c2d42 !important;
            color: #ffffff !important;
            font-weight: 700;
        }

        .tabel-mapa-modern .th-slate {
            background-color: #e2e8f0 !important;
            color: #0f172a !important;
            font-weight: 700;
        }

        .tabel-mapa-modern .td-label {
            background-color: #f8fafc;
            font-weight: 700;
            color: #1e293b;
            vertical-align: middle;
        }

        /* Option Cards & Checkboxes */
        .opsi-grid-2 {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(230px, 1fr));
            gap: 0.45rem;
        }

        .opsi-card-modern {
            display: flex;
            align-items: flex-start;
            gap: 0.45rem;
            padding: 0.4rem 0.6rem;
            border: 1.2px solid #e2e8f0;
            border-radius: 6px;
            background: #ffffff;
            cursor: pointer;
            transition: all 0.15s ease;
            margin-bottom: 0.25rem;
            font-size: 0.8rem;
            color: #1e293b;
            line-height: 1.4;
        }

        .opsi-card-modern:hover {
            border-color: #4682b4;
            background-color: #f8fafc;
        }

        .opsi-card-modern:has(input:checked) {
            border-color: #36648b;
            background-color: #f0f6fa;
            font-weight: 600;
            color: #0f172a;
        }

        .opsi-card-modern input[type="checkbox"],
        .opsi-card-modern input[type="radio"] {
            margin-top: 0.1rem;
            width: 14px;
            height: 14px;
            accent-color: #36648b;
            flex-shrink: 0;
            cursor: pointer;
        }

        /* Professional Rating Pill Selector */
        .rating-row-modern {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 0.35rem 0.65rem;
            border-radius: 6px;
            background: #f8fafc;
            border: 1px solid #e2e8f0;
            margin-bottom: 0.35rem;
            flex-wrap: wrap;
            gap: 0.5rem;
        }

        .rating-row-title {
            font-size: 0.8rem;
            font-weight: 600;
            color: #1e293b;
        }

        .rating-pills-wrap {
            display: inline-flex;
            background: #ffffff;
            padding: 2px;
            border-radius: 6px;
            border: 1px solid #cbd5e1;
            gap: 3px;
        }

        .rating-pill {
            display: inline-flex;
            align-items: center;
            gap: 0.25rem;
            padding: 0.2rem 0.5rem;
            border-radius: 4px;
            font-size: 0.75rem;
            font-weight: 600;
            color: #475569;
            cursor: pointer;
            transition: all 0.15s ease;
            user-select: none;
        }

        .rating-pill input[type="radio"] {
            display: none;
        }

        .rating-pill:hover {
            background: #f1f5f9;
            color: #0f172a;
        }

        .rating-pill:has(input[value="senang"]:checked) {
            background: #ecfdf5;
            color: #065f46;
            font-weight: 700;
            border: 1px solid #a7f3d0;
        }

        .rating-pill:has(input[value="datar"]:checked) {
            background: #fffbeb;
            color: #92400e;
            font-weight: 700;
            border: 1px solid #fde68a;
        }

        .rating-pill:has(input[value="sedih"]:checked) {
            background: #fef2f2;
            color: #991b1b;
            font-weight: 700;
            border: 1px solid #fecaca;
        }

        /* 2-Way Segmented Toggle (Ada / Tidak Ada) */
        .toggle-segment-wrap {
            display: inline-flex;
            background: #f1f5f9;
            padding: 2px;
            border-radius: 6px;
            border: 1px solid #cbd5e1;
            gap: 3px;
            margin-bottom: 0.35rem;
        }

        .toggle-segment-btn {
            display: inline-flex;
            align-items: center;
            padding: 0.25rem 0.65rem;
            border-radius: 4px;
            font-size: 0.78rem;
            font-weight: 600;
            color: #475569;
            cursor: pointer;
            transition: all 0.15s ease;
        }

        .toggle-segment-btn input[type="radio"] {
            display: none;
        }

        .toggle-segment-btn:hover {
            background: #e2e8f0;
            color: #0f172a;
        }

        .toggle-segment-btn:has(input:checked) {
            background: #ffffff;
            color: #0f172a;
            font-weight: 700;
            box-shadow: 0 1px 2px rgba(0, 0, 0, 0.08);
        }

        /* Inline Input Modern */
        .input-inline-modern {
            width: 100%;
            min-height: 32px;
            border: 1.2px solid #cbd5e1;
            border-radius: 4px;
            padding: 0.35rem 0.55rem;
            font-size: 0.82rem;
            background: #ffffff;
            color: #0f172a;
            font-family: inherit;
            transition: all 0.15s ease;
            box-sizing: border-box;
        }

        .input-inline-modern:focus {
            border-color: #36648b;
            outline: none;
            box-shadow: 0 0 0 2px rgba(54, 100, 139, 0.15);
        }

        .input-inline-modern:disabled,
        .input-inline-modern[disabled],
        .input-inline-modern.is-disabled {
            background-color: #f1f5f9 !important;
            color: #94a3b8 !important;
            border-color: #e2e8f0 !important;
            cursor: not-allowed !important;
            opacity: 0.8;
            box-shadow: none !important;
        }

        .input-inline-modern.input-error {
            border-color: #ef4444 !important;
            background-color: #fef2f2 !important;
            box-shadow: 0 0 0 2px rgba(239, 68, 68, 0.2) !important;
        }

        .badge-wajib-ada {
            font-size: 0.75rem;
            font-weight: 700;
            color: #dc2626;
            margin-left: 0.25rem;
        }

        /* Textarea Bukti Matriks Modern */
        .textarea-bukti-mapa {
            width: 100%;
            min-width: 240px;
            min-height: 60px;
            border: 1.2px solid #cbd5e1;
            border-radius: 6px;
            padding: 0.45rem 0.65rem;
            font-size: 0.8rem;
            color: #0f172a;
            font-family: inherit;
            line-height: 1.4;
            background: #ffffff;
            transition: all 0.15s ease;
            resize: vertical;
            box-sizing: border-box;
        }

        .textarea-bukti-mapa:focus {
            border-color: #36648b;
            background: #ffffff;
            outline: none;
            box-shadow: 0 0 0 2px rgba(54, 100, 139, 0.15);
        }

        /* Legend Card */
        .legend-card-mapa {
            background: #f0f6fa;
            border: 1px solid #dce7f2;
            border-radius: 6px;
            padding: 0.6rem 0.85rem;
            margin-top: 0.5rem;
            font-size: 0.75rem;
            color: #1e293b;
        }

        .legend-badge {
            display: inline-block;
            padding: 0.1rem 0.35rem;
            background: #1c2d42;
            color: #ffffff;
            border-radius: 3px;
            font-weight: 700;
            font-size: 0.7rem;
            margin-right: 0.2rem;
        }

        @media screen and (max-width: 640px) {
            #container-mapa01,
            .wadah-mapa01 {
                width: 100%;
                max-width: 100%;
                min-width: 0;
                box-sizing: border-box;
            }

            .dokumen-kertas-mapa {
                width: 100%;
                min-width: 0;
                padding: 0.75rem;
                box-sizing: border-box;
                overflow-x: hidden;
            }

            .dokumen-kertas-mapa > table.tabel-mapa-modern {
                width: 100%;
                max-width: 100%;
                table-layout: fixed;
            }

            .dokumen-kertas-mapa > table.tabel-mapa-modern th,
            .dokumen-kertas-mapa > table.tabel-mapa-modern td {
                min-width: 0;
                padding: 0.35rem;
                overflow-wrap: anywhere;
                word-break: normal;
            }

            .opsi-grid-2 {
                grid-template-columns: minmax(0, 1fr);
            }

            .rating-row-modern {
                align-items: flex-start;
            }

            .rating-pills-wrap {
                flex-wrap: wrap;
                gap: 0.3rem;
            }

            .header-eksekutif-mapa {
                padding: 0.75rem;
            }

            .kop-kode-box {
                min-width: 0;
            }

            .tombol-aksi-container {
                align-items: stretch !important;
            }

            .tombol-aksi-container > div {
                width: 100%;
            }

            .tombol-aksi-container button,
            .tombol-aksi-container a {
                max-width: 100%;
                white-space: normal;
            }
        }

        /* Print Settings */
        @media print {
            body {
                background: #ffffff !important;
                color: #000000 !important;
                font-size: 9.5pt !important;
            }
            .sidebar-dasbor, .header-dasbor, .tombol-aksi-container, .modal-overlay, #btn-cetak-mapa, #btn-kembali-mapa, .header-eksekutif-mapa {
                display: none !important;
            }
            .wadah-dasbor {
                margin: 0 !important;
                padding: 0 !important;
            }
            .dokumen-kertas-mapa {
                border: none !important;
                box-shadow: none !important;
                padding: 0 !important;
                width: 100% !important;
            }
            .tabel-mapa-modern th, .tabel-mapa-modern td {
                border: 1px solid #000 !important;
            }
            .page-break {
                page-break-before: always;
            }
            .baris-relevan-dicoret {
                opacity: 0.65 !important;
            }
            .baris-relevan-dicoret .role-title {
                text-decoration: line-through !important;
                color: #475569 !important;
            }
            .helper-manajer-lsp, .btn-today-relevan, .toggle-relevan-tabel {
                display: none !important;
            }
        }

        /* Styling Interaktif untuk Tabel Konfirmasi dengan Orang yang Relevan */
        .baris-relevan-aktif {
            background-color: #ffffff;
            transition: all 0.2s ease;
        }
        .baris-relevan-dicoret {
            background-color: #f8fafc;
            opacity: 0.72;
            transition: all 0.2s ease;
        }
        .baris-relevan-dicoret .role-title {
            text-decoration: line-through;
            color: #94a3b8;
        }

        /* Mode Tampilan Terkunci (Read-Only) untuk Asesor */
        .mode-view-locked input[type="checkbox"],
        .mode-view-locked input[type="radio"] {
            pointer-events: none !important;
            cursor: default !important;
        }
        .mode-view-locked input[type="text"],
        .mode-view-locked select,
        .mode-view-locked textarea {
            pointer-events: none !important;
            cursor: default !important;
            background-color: #f8fafc !important;
            border-color: #e2e8f0 !important;
        }
        .mode-view-locked .tombol-tambah-baris,
        .mode-view-locked .tombol-hapus-baris,
        .mode-view-locked .btn-ubah-ttd,
        .mode-view-locked .btn-simpan-mapa01 {
            display: none !important;
        }
    </style>
@endpush

@section('konten')
@php
    $isAsesi = auth()->check() && auth()->user()->peran === 'asesi';
    $isAdmin = auth()->check() && in_array(auth()->user()->peran, ['admin', 'superadmin']);
    $isMasterMode = !empty($isMasterMode) || (isset($pendaftaran) && (empty($pendaftaran->id) || $pendaftaran->id === 0));
    $isConfigured = !empty($mapa01) && $mapa01->exists && (($mapa01->status_mapa ?? '') === 'final' || !empty($mapa01->rencana_unit_matriks) || !empty($mapa01->updated_at));

    $penyusunTabelSaved = $mapa01->penyusun_validator_tabel ?? [];
    $adminValidatorData = $penyusunTabelSaved['validator_1'] ?? [];

    $masterMapa01 = !$isMasterMode && !empty($pendaftaran->skema_id) 
        ? \App\Models\Mapa01::where('skema_id', $pendaftaran->skema_id)->whereNull('pendaftaran_id')->first() 
        : null;
    $masterValidatorData = $masterMapa01?->penyusun_validator_tabel['validator_1'] ?? [];
    $isMasterSchemeValidated = ($masterValidatorData['status_validasi'] ?? '') === 'tervalidasi';
    $isAdminAuthoring = $isAdmin
        && empty($mapa01?->tanda_tangan_asesor)
        && (!$isMasterSchemeValidated || empty($masterMapa01?->tanda_tangan_asesor));

    if (!$isMasterMode && $isMasterSchemeValidated) {
        $adminValidatorData = !empty($adminValidatorData['ttd']) ? $adminValidatorData : $masterValidatorData;
    }

    // Ambil default identitas Validator dari akun Admin di database
    $adminUserDb = (auth()->check() && in_array(auth()->user()->peran, ['admin', 'superadmin']))
        ? auth()->user()
        : (\App\Models\Pengguna::where('peran', 'admin')->first() ?: \App\Models\Pengguna::where('peran', 'superadmin')->first());
    $dbAdminNama = $adminUserDb?->nama_lengkap ?: 'Administrator LSP';
    $dbAdminMet = $adminUserDb?->nomor_registrasi ?: 'REG.ADM.LSP.001';

    $adminValidatorNama = !empty($adminValidatorData['nama']) ? $adminValidatorData['nama'] : $dbAdminNama;
    $adminValidatorMet = !empty($adminValidatorData['nomor_met']) ? $adminValidatorData['nomor_met'] : $dbAdminMet;
    $adminTtd = $mapa01?->tanda_tangan_admin ?: ($adminValidatorData['ttd'] ?? null);
    $adminTtdTgl = $mapa01?->tanggal_ttd_admin
        ? $mapa01->tanggal_ttd_admin->timezone('Asia/Jakarta')->format('d/m/Y H:i:s') . ' WIB'
        : ($adminValidatorData['ttd_tanggal'] ?? '');
    $adminStatusValidasi = $adminValidatorData['status_validasi'] ?? null;
    $isValidatedAdmin = $adminStatusValidasi === 'tervalidasi' || (!$isMasterMode && $isMasterSchemeValidated);
    $catatanValidasi = $adminValidatorData['catatan'] ?? null;

    $targetValidasiId = !empty($mapa01->id) ? $mapa01->id : (!empty($pendaftaran->id) ? $pendaftaran->id : ($pendaftaran->skema_id ?? 0));

    // Ekstraksi Data Tersimpan untuk Evaluasi Kelengkapan Dokumen (Role Admin)
    $tujuanDb = strtolower($pendaftaran->tujuan_asesmen ?? '');
    $tujuanSaved = $mapa01?->tujuan_asesmen ?? ($isMasterMode ? null : ($pendaftaran->tujuan_asesmen ?? null));
    $pendekatanSaved = (array) ($mapa01?->pendekatan_asesi ?? []);
    $pelaksanaSaved = (array) ($mapa01?->pelaksana_asesmen ?? []);
    $konfirmasiSaved = (array) ($mapa01?->konfirmasi_orang_relevan ?? []);
    $standarIndustriSaved = (array) ($mapa01?->standar_industri ?? []);
    $matriksSaved = (array) ($mapa01?->rencana_unit_matriks ?? []);
    $konfirmasiTabelSaved = (array) ($mapa01?->konfirmasi_pihak_relevan_tabel ?? []);

    // Evaluasi Kelengkapan Setiap Komponen FR.MAPA.01
    $evaluasiMapa01 = [];

    // 1. Pendekatan Asesmen
    $hasPendekatan = !empty($pendekatanSaved);
    $evaluasiMapa01[] = [
        'label' => 'Pendekatan Asesmen Kandidat (Bagian 1.1)',
        'lengkap' => $hasPendekatan,
        'keterangan' => $hasPendekatan ? 'Telah dipilih (' . implode(', ', array_slice($pendekatanSaved, 0, 2)) . (count($pendekatanSaved) > 2 ? '...' : '') . ')' : 'Belum ada pendekatan kandidat yang dipilih.'
    ];

    // 2. Tujuan Asesmen
    $hasTujuan = !empty($tujuanSaved);
    $evaluasiMapa01[] = [
        'label' => 'Tujuan Asesmen (Bagian 1.1)',
        'lengkap' => $hasTujuan,
        'keterangan' => $hasTujuan ? 'Tujuan: ' . ucfirst($tujuanSaved) : 'Tujuan asesmen belum ditentukan.'
    ];

    // 3. Matriks Rencana Unit Kompetensi
    $totalUnits = $pendaftaran->skema?->unitKompetensi?->count() ?? ($skema->unitKompetensi?->count() ?? 0);
    $filledUnits = 0;
    if ($totalUnits > 0) {
        $unitsList = $pendaftaran->skema?->unitKompetensi ?? ($skema->unitKompetensi ?? collect());
        foreach ($unitsList as $u) {
            $mU = $matriksSaved[$u->id] ?? null;
            if ($mU && (!empty($mU['l']) || !empty($mU['tl']) || !empty($mU['t'])) && !empty($mU['methods'])) {
                $filledUnits++;
            }
        }
    }
    $hasMatriks = ($totalUnits > 0 && $filledUnits >= $totalUnits);
    $evaluasiMapa01[] = [
        'label' => 'Matriks Rencana Asesmen Unit (Bagian 2)',
        'lengkap' => $hasMatriks,
        'keterangan' => $hasMatriks 
            ? "Seluruh unit kompetensi ({$filledUnits}/{$totalUnits} unit) telah memiliki rencana bukti dan metode." 
            : ($filledUnits > 0 
                ? "Rencana unit belum lengkap: baru {$filledUnits} dari {$totalUnits} unit kompetensi yang terisi."
                : "Belum ada rencana bukti atau metode unit kompetensi yang dikonfigurasi ({$totalUnits} unit).")
    ];

    // 4. Konfirmasi Orang Relevan
    $hasPihakRelevan = false;
    if (!empty($konfirmasiTabelSaved)) {
        foreach ($konfirmasiTabelSaved as $rk => $rData) {
            if (!empty($rData['relevan']) && !empty($rData['nama'])) {
                $hasPihakRelevan = true;
                break;
            }
        }
    }
    if (!$hasPihakRelevan && !empty($konfirmasiSaved)) {
        $hasPihakRelevan = true;
    }
    $evaluasiMapa01[] = [
        'label' => 'Konfirmasi Orang Relevan (Bagian 1.1)',
        'lengkap' => $hasPihakRelevan,
        'keterangan' => $hasPihakRelevan ? 'Pihak relevan telah terkonfirmasi.' : 'Belum ada pihak relevan yang dikonfirmasi nama & tanggalnya.'
    ];

    // Resolusi Asesor Penguji & TTD Asesor (Penyusun)
    $resolvedAsesor = null;
    if (auth()->check() && auth()->user()->peran === 'asesor') {
        $resolvedAsesor = auth()->user();
    } elseif (!empty($pendaftaran->asesor) && $pendaftaran->asesor->peran === 'asesor') {
        $resolvedAsesor = $pendaftaran->asesor;
    } elseif (!empty($mapa01->asesor) && $mapa01->asesor->peran === 'asesor') {
        $resolvedAsesor = $mapa01->asesor;
    } elseif (!empty($pendaftaran->jadwal?->asesor) && $pendaftaran->jadwal->asesor->peran === 'asesor') {
        $resolvedAsesor = $pendaftaran->jadwal->asesor;
    } else {
        $targetSkemaId = $pendaftaran->skema_id ?? ($mapa01->skema_id ?? ($skema->id ?? null));
        if ($targetSkemaId) {
            $resolvedAsesor = \App\Models\Pengguna::where('peran', 'asesor')->where('skema_id', $targetSkemaId)->first();
        }
        if (!$resolvedAsesor) {
            $resolvedAsesor = \App\Models\Pengguna::where('peran', 'asesor')->first();
        }
    }

    $adminTtdList = \App\Models\Pengguna::whereIn('peran', ['admin', 'superadmin'])->pluck('tanda_tangan')->filter()->toArray();
    if (!empty($adminTtd)) {
        $adminTtdList[] = $adminTtd;
    }

    $candidateTtd = $mapa01?->tanda_tangan_asesor;
    if (!empty($candidateTtd) && in_array($candidateTtd, $adminTtdList, true)) {
        $candidateTtd = null;
    }
    $asesorTtd = $candidateTtd;

    $totalKurang = collect($evaluasiMapa01)->where('lengkap', false)->count();
    $semuaLengkapKecualiAdmin = ($totalKurang === 0);
    $adaDataKurang = !$semuaLengkapKecualiAdmin;
@endphp
<div class="wadah-mapa01 animasi-slide {{ $isAsesi ? 'mode-read-only-asesi' : '' }}" id="container-mapa01">
    
    <!-- NAVIGATION BAR ATAS -->
    <div class="tombol-aksi-container no-print" style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 1.25rem; flex-wrap: wrap; gap: 0.75rem;">
        <div style="display: flex; gap: 0.5rem; flex-wrap: wrap; align-items: center;">
            @php
                $isAdminUser = auth()->check() && in_array(auth()->user()->peran, ['admin', 'superadmin']);
                $halamanFormulir = $isAdminUser 
                    ? route('admin.master-muk.index', array_filter(['skema_id' => $pendaftaran->skema_id ?? request('skema_id')]))
                    : route('asesor.mapa', array_filter(['skema_id' => $pendaftaran->skema_id ?? request('skema_id')]));
                $prevUrl = url()->previous();
                
                // Mencegah bug: jangan pernah kembali ke halaman MAPA 01 atau MAPA 02 itu sendiri
                $isInvalidPrev = empty($prevUrl) 
                    || $prevUrl === url()->current() 
                    || str_contains($prevUrl, 'mapa-02') 
                    || str_contains($prevUrl, 'mapa02') 
                    || str_contains($prevUrl, 'mapa-01') 
                    || str_contains($prevUrl, 'mapa01');

                if ($isAsesi) {
                    $targetKembali = route('asesi.tahapan', ['step' => 2, 'pendaftaran_id' => $pendaftaran->id]);
                } elseif (!$isInvalidPrev && (str_contains($prevUrl, 'daftar-peserta') || str_contains($prevUrl, 'penilaian') || str_contains($prevUrl, 'verifikasi'))) {
                    $targetKembali = $prevUrl;
                } else {
                    $targetKembali = $halamanFormulir;
                }
            @endphp
            <a href="{{ $targetKembali }}" 
               onclick="if (document.referrer && document.referrer !== window.location.href && !document.referrer.includes('mapa-02') && !document.referrer.includes('mapa02') && !document.referrer.includes('mapa-01') && !document.referrer.includes('mapa01')) { window.location.href = document.referrer; return false; } else { window.location.href = '{{ $targetKembali }}'; return false; }"
               id="btn-kembali-mapa" 
               class="tombol tombol-sekunder tombol-sm cursor-pointer">
                &larr; Kembali
            </a>
        </div>
        <div style="display: flex; gap: 0.5rem; align-items: center; flex-wrap: wrap;">
            @if($isAdmin && !$isAdminAuthoring)
                @if(!$isMasterMode && $isMasterSchemeValidated)
                    <span class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-lg text-xs font-bold bg-emerald-50 text-emerald-700 border border-emerald-200">
                        <span class="w-2 h-2 rounded-full bg-emerald-500"></span>
                        Tervalidasi Resmi (Master Skema)
                    </span>
                @elseif($isValidatedAdmin)
                    <button type="button" onclick="bukaModal('modalValidasiAdminMapa01')" class="tombol tombol-sm" style="background: #059669; color: #ffffff; border: 1px solid #065f46; font-weight: 700;">
                        <span>Perbarui Validasi Admin</span>
                    </button>
                @else
                    <button type="button" onclick="bukaModal('modalValidasiAdminMapa01')" class="tombol tombol-sm" style="background: #047857; color: #ffffff; border: 1px solid #065f46; font-weight: 700;">
                        <span>Validasi &amp; Sahkan FR.MAPA.01</span>
                    </button>
                @endif
            @endif
            @if(!$isAsesi && $isConfigured)
                <button type="button" id="btn-toggle-edit-mapa01" onclick="toggleEditMapa01()" class="tombol tombol-sm" style="background: #4f46e5; color: #ffffff; border: 1px solid #4338ca; font-weight: 700;">
                    <span id="text-toggle-mapa01">Edit Formulir</span>
                </button>
            @endif
            <button type="button" onclick="window.print()" id="btn-cetak-mapa" class="tombol tombol-sekunder tombol-sm btn-cetak-formulir">
                Cetak Dokumen
            </button>
            @if(!$isAsesi)
                <button type="button" id="btn-simpan-mapa01" onclick="submitMapa01('draft')" class="tombol tombol-utama tombol-sm btn-simpan-mapa01" style="background: #059669; border-color: #059669; font-weight: 700; display: {{ $isConfigured ? 'none' : 'inline-flex' }};">
                    {{ $isAdminAuthoring ? 'Simpan Draft' : 'Simpan Formulir' }}
                </button>
            @endif
        </div>
    </div>

    @if(!empty($isMasterMode))
        <div class="header-eksekutif-mapa no-print" style="margin-bottom: 1rem;">
            <div class="header-eksekutif-info">
                <div style="display: flex; align-items: center; gap: 0.5rem; margin-bottom: 0.25rem;">
                    <span class="lencana lencana-biru" style="background: rgba(255,255,255,0.2); color: #ffffff; border: 1px solid rgba(255,255,255,0.3); font-size: 0.72rem; font-weight: 800;">
                        MASTER TEMPLATE SKEMA
                    </span>
                    <span style="font-family: monospace; font-size: 0.75rem; color: #cbd5e1;">{{ $pendaftaran->skema->kode_skema ?? '' }}</span>
                </div>
                <h1 style="font-size: 1.15rem; margin: 0;">FR.MAPA.01 &bull; Perencanaan Aktivitas & Proses Asesmen Master</h1>
            </div>
        </div>
    @endif

    @if(!$isAsesi && $isConfigured)
        <div id="banner-mapa01-locked" class="no-print" style="margin-bottom: 1rem; background: #f8fafc; border: 1px solid #e2e8f0; border-radius: 8px; padding: 0.85rem 1.15rem; display: flex; align-items: center; justify-content: space-between; gap: 0.75rem;">
            <div style="font-size: 0.82rem; color: #475569;">
                <strong style="display: block; color: #0f172a; font-size: 0.88rem; margin-bottom: 0.2rem;">Mode Tampilan (Terkunci)</strong>
                <span>Formulir telah dikonfigurasi dan ditampilkan dalam mode hanya lihat. Klik tombol <strong>Edit Formulir</strong> di atas jika Anda ingin mengubah isi formulir.</span>
            </div>
        </div>

        <div id="banner-mapa01-editing" class="no-print" style="margin-bottom: 1rem; background: #fffbeb; border: 1px solid #fde68a; border-radius: 8px; padding: 0.85rem 1.15rem; display: none; align-items: center; gap: 0.75rem; font-size: 0.82rem; color: #92400e;">
            <div>
                <strong style="display: block; color: #78350f; font-size: 0.88rem; margin-bottom: 0.2rem;">Mode Edit Aktif</strong>
                <span>Anda sekarang dapat mengubah pendekatan, unit, dan parameter MAPA.01 di bawah ini. Klik <strong>Simpan Formulir</strong> di atas setelah selesai.</span>
            </div>
        </div>
    @endif



    @if($isAsesi)
        @include('komponen.banner-readonly-asesi', [
            'pesan' => 'Formulir Perencanaan Aktivitas dan Proses Asesmen (FR.MAPA.01) ini dirancang dan ditetapkan oleh Asesor Penguji Anda.'
        ])
    @endif



    <form id="form-mapa01" action="{{ !empty($isMasterMode) ? route('asesor.skema.mapa-01.simpan', $pendaftaran->skema_id) : route('asesor.mapa-01.simpan', $pendaftaran->id) }}" method="POST">
        @csrf
        <input type="hidden" name="aksi" id="inputAksiMapa01" value="draft">

        @php
            $tujuanDb = strtolower($pendaftaran->tujuan_asesmen ?? '');
            $tujuanSaved = $mapa01?->tujuan_asesmen ?? ($isMasterMode ? null : ($pendaftaran->tujuan_asesmen ?? null));
            $pendekatanSaved = (array) ($mapa01?->pendekatan_asesi ?? []);
            $pelaksanaSaved = (array) ($mapa01?->pelaksana_asesmen ?? []);
            $konfirmasiSaved = (array) ($mapa01?->konfirmasi_orang_relevan ?? []);
            $standarIndustriSaved = (array) ($mapa01?->standar_industri ?? []);
            $matriksSaved = (array) ($mapa01?->rencana_unit_matriks ?? []);
            $konfirmasiTabelSaved = (array) ($mapa01?->konfirmasi_pihak_relevan_tabel ?? []);
            $penyusunTabelSaved = (array) ($mapa01?->penyusun_validator_tabel ?? []);

            $roleDefinitions = [
                'manajer_lsp' => [
                    'label' => 'Manajer sertifikasi LSP',
                    'sublabel' => 'Penjamin mutu asesmen dari LSP',
                    'aliases' => ['Manajer sertifikasi LSP'],
                ],
                'lead_asesor' => [
                    'label' => 'Master Asesor / Master Trainer / Lead Asesor Kompetensi',
                    'sublabel' => 'Lead asesor / pengarah teknis asesmen',
                    'aliases' => ['Master Asesor / Master Trainer / Lead Asesor Kompetensi'],
                ],
                'manajer_pelatihan' => [
                    'label' => 'Manajer pelatihan Lembaga Training terakreditasi / terdaftar',
                    'sublabel' => 'Lembaga diklat / mitra pelatihan terakreditasi',
                    'aliases' => [
                        'Manajer pelatihan Lembaga Training terakreditasi / terdaftar',
                        'Manajer Pelatihan Lembaga Training terakreditasi / Lembaga Training terdaftar',
                    ],
                ],
                'supervisor' => [
                    'label' => 'Manajer atau supervisor di tempat kerja',
                    'sublabel' => 'Penyelia teknis tempat kerja / industri / DU-DI',
                    'aliases' => [
                        'Manajer atau supervisor di tempat kerja',
                        'Manajer atau supervisor ditempat kerja',
                    ],
                ],
            ];
            
            // Resolusi Asesor Penguji yang sebenarnya (bukan Admin LSP)
            $resolvedAsesor = null;
            if (auth()->check() && auth()->user()->peran === 'asesor') {
                $resolvedAsesor = auth()->user();
            } elseif (!empty($pendaftaran->asesor) && $pendaftaran->asesor->peran === 'asesor') {
                $resolvedAsesor = $pendaftaran->asesor;
            } elseif (!empty($mapa01->asesor) && $mapa01->asesor->peran === 'asesor') {
                $resolvedAsesor = $mapa01->asesor;
            } elseif (!empty($pendaftaran->jadwal?->asesor) && $pendaftaran->jadwal->asesor->peran === 'asesor') {
                $resolvedAsesor = $pendaftaran->jadwal->asesor;
            } else {
                $targetSkemaId = $pendaftaran->skema_id ?? ($mapa01->skema_id ?? ($skema->id ?? null));
                if ($targetSkemaId) {
                    $resolvedAsesor = \App\Models\Pengguna::where('peran', 'asesor')->where('skema_id', $targetSkemaId)->first();
                }
                if (!$resolvedAsesor) {
                    $resolvedAsesor = \App\Models\Pengguna::where('peran', 'asesor')->first();
                }
            }

            // Dapatkan daftar TTD admin untuk mencegah kontaminasi TTD asesor
            $adminTtdList = \App\Models\Pengguna::whereIn('peran', ['admin', 'superadmin'])->pluck('tanda_tangan')->filter()->toArray();
            if (!empty($adminTtd)) {
                $adminTtdList[] = $adminTtd;
            }

            $savedPenyusunNama = $penyusunTabelSaved['penyusun_1']['nama'] ?? null;
            // Jika tersimpan nama Admin / Administrator, gantikan dengan nama Asesor!
            $isSavedAdminName = !empty($savedPenyusunNama) && (
                stripos($savedPenyusunNama, 'admin') !== false ||
                $savedPenyusunNama === $adminValidatorNama ||
                (auth()->check() && $isAdmin && $savedPenyusunNama === auth()->user()->nama_lengkap)
            );

            $asesorNama = (!$isSavedAdminName && !empty($savedPenyusunNama))
                ? $savedPenyusunNama
                : ($resolvedAsesor?->nama_lengkap ?? 'Asesor Penguji');

            $savedPenyusunMet = $penyusunTabelSaved['penyusun_1']['nomor_met'] ?? null;
            $asesorMet = (!$isSavedAdminName && !empty($savedPenyusunMet))
                ? $savedPenyusunMet
                : ($resolvedAsesor?->nomor_registrasi ?? 'MET.000.001222 2026');

            // Resolusi TTD Asesor: Hanya gunakan gambar tanda tangan nyata dari Asesor (abaikan SVG teks otomatis)
            $candidateTtd = $asesorTtd ?: ($mapa01?->tanda_tangan_asesor 
                ?? ($penyusunTabelSaved['penyusun_1']['ttd'] 
                ?? ($pendaftaran->tanda_tangan_asesor 
                ?? (auth()->check() && auth()->user()->peran === 'asesor' ? (auth()->user()->tanda_tangan ?? null) : null))));
            if (!empty($candidateTtd) && (in_array($candidateTtd, $adminTtdList, true) || Str::contains($candidateTtd, 'svg'))) {
                $candidateTtd = null;
            }

            $asesorTtd = $candidateTtd;
            $isAsesorAutoSigned = false;
        @endphp

        <!-- ========================================================================= -->
        <!-- HALAMAN 1: IDENTITAS SKEMA & 1. MENENTUKAN PENDEKATAN ASESMEN -->
        <!-- ========================================================================= -->
        <div class="dokumen-kertas-mapa">
            
            <!-- KOP RESMI DOKUMEN STANDAR BNSP -->
            @include('komponen.kop-formulir-bnsp', [
                'kodeForm' => 'FR.MAPA.01',
                'judulForm' => 'MERENCANAKAN AKTIVITAS DAN PROSES ASESMEN',
                'tipeDokumen' => 'Rencana Asesmen'
            ])

            <!-- TABEL SKEMA SERTIFIKASI -->
            <table class="tabel-mapa-modern" style="margin-bottom: 1.5rem;">
                <tr>
                    <td rowspan="2" style="width: 25%; font-weight: 700; vertical-align: middle; background-color: #f8fafc;">
                        Skema Sertifikasi<br>
                        <span style="font-weight: 500; font-size: 0.82rem; color: #475569;">(KKNI/Okupasi/Klaster)</span>
                    </td>
                    <td style="width: 12%; font-weight: 600;">Judul</td>
                    <td style="width: 2%;">:</td>
                    <td style="font-weight: 700; color: #0f172a;">
                        {{ $pendaftaran->skema->nama_skema }}
                    </td>
                </tr>
                <tr>
                    <td style="font-weight: 600;">Nomor</td>
                    <td>:</td>
                    <td style="font-weight: 700; color: #0f172a;">
                        {{ $pendaftaran->skema->kode_skema }}
                    </td>
                </tr>
            </table>

            <!-- BAGIAN 1: MENENTUKAN PENDEKATAN ASESMEN -->
            <div class="section-header-modern">
                <span class="badge-num">1</span>
                <span>Menentukan Pendekatan Asesmen</span>
            </div>

            <table class="tabel-mapa-modern" style="border-top: none; margin-bottom: 0;">
                
                <!-- 1.1 ASESI -->
                <tr>
                    <td style="width: 5%; font-weight: 800; text-align: center; background: #f8fafc;">1.1</td>
                    <td style="width: 23%; font-weight: 700; color: #0f172a;">Kandidat / Asesi</td>
                    <td colspan="2">
                        @php
                            $opsiAsesi = [
                                'Hasil pelatihan dan / atau pendidikan, dimana Kurikulum dan fasilitas praktek mampu telusur terhadap standar kompetensi',
                                'Hasil pelatihan dan / atau pendidikan, dimana kurikulum belum berbasis kompetensi.',
                                'Pekerja berpengalaman, dimana berasal dari industri/tempat kerja yang dalam operasionalnya mampu telusur dengan standar kompetensi',
                                'Pekerja berpengalaman, dimana berasal dari industri/tempat kerja yang dalam operasionalnya belum berbasis kompetensi.',
                                'Pelatihan / belajar mandiri atau otodidak.'
                            ];
                        @endphp
                        <div style="display: flex; flex-direction: column; gap: 0.4rem;">
                            @foreach($opsiAsesi as $idx => $opsi)
                                <label class="opsi-card-modern">
                                    <input type="checkbox" name="pendekatan_asesi[]" value="{{ $opsi }}"
                                        {{ in_array($opsi, $pendekatanSaved) ? 'checked' : '' }}>
                                    <span>{{ $opsi }}</span>
                                </label>
                            @endforeach
                        </div>
                    </td>
                </tr>

                <!-- TUJUAN ASESMEN -->
                <tr>
                    <td style="background: #f8fafc;"></td>
                    <td style="font-weight: 700; color: #0f172a;">Tujuan Asesmen</td>
                    <td colspan="2">
                        <div class="opsi-grid-2">
                            <label class="opsi-card-modern">
                                <input type="radio" name="tujuan_asesmen" value="Sertifikasi" 
                                    {{ (!empty($tujuanSaved) && (strcasecmp($tujuanSaved, 'Sertifikasi') === 0 || strtolower($tujuanSaved) === 'sertifikasi')) ? 'checked' : '' }}>
                                <span>Sertifikasi</span>
                            </label>
                            <label class="opsi-card-modern">
                                <input type="radio" name="tujuan_asesmen" value="Pengakuan Kompetensi Terkini (PKT)"
                                    {{ (!empty($tujuanSaved) && (str_contains(strtolower($tujuanSaved), 'pkt') || strcasecmp($tujuanSaved, 'Pengakuan Kompetensi Terkini (PKT)') === 0)) ? 'checked' : '' }}>
                                <span>Pengakuan Kompetensi Terkini (PKT)</span>
                            </label>
                            <label class="opsi-card-modern">
                                <input type="radio" name="tujuan_asesmen" value="Rekognisi Pembelajaran Lampau (RPL)"
                                    {{ (!empty($tujuanSaved) && (str_contains(strtolower($tujuanSaved), 'rpl') || strcasecmp($tujuanSaved, 'Rekognisi Pembelajaran Lampau (RPL)') === 0)) ? 'checked' : '' }}>
                                <span>Rekognisi Pembelajaran Lampau (RPL)</span>
                            </label>
                            <label class="opsi-card-modern">
                                <input type="radio" name="tujuan_asesmen" value="Lainnya"
                                    {{ (!empty($tujuanSaved) && (str_contains(strtolower($tujuanSaved), 'lainnya') || strcasecmp($tujuanSaved, 'Lainnya') === 0)) ? 'checked' : '' }}>
                                <span>Lainnya</span>
                            </label>
                        </div>
                    </td>
                </tr>

                <!-- KONTEKS ASESMEN -->
                <tr>
                    <td rowspan="4" style="background: #f8fafc;"></td>
                    <td rowspan="4" style="font-weight: 700; color: #0f172a; vertical-align: top;">Konteks Asesmen:</td>
                    <td style="width: 25%; font-weight: 600; color: #334155;">Lingkungan</td>
                    <td>
                        <div style="display: flex; gap: 1.5rem; flex-wrap: wrap;">
                            <label class="opsi-card-modern" style="flex: 1; margin-bottom: 0;">
                                <input type="radio" name="konteks_lingkungan" value="Tempat kerja nyata" 
                                    {{ in_array($mapa01->konteks_lingkungan ?? '', ['Tempat kerja nyata', 'nyata']) ? 'checked' : '' }}>
                                <span>Tempat kerja nyata</span>
                            </label>
                            <label class="opsi-card-modern" style="flex: 1; margin-bottom: 0;">
                                <input type="radio" name="konteks_lingkungan" value="Tempat kerja simulasi"
                                    {{ in_array($mapa01->konteks_lingkungan ?? '', ['Tempat kerja simulasi', 'simulasi']) ? 'checked' : '' }}>
                                <span>Tempat kerja simulasi</span>
                            </label>
                        </div>
                    </td>
                </tr>

                <tr>
                    <td style="font-weight: 600; color: #334155;">Peluang untuk mengumpulkan bukti dalam sejumlah situasi</td>
                    <td>
                        <div style="display: flex; gap: 1.5rem; flex-wrap: wrap;">
                            <label class="opsi-card-modern" style="flex: 1; margin-bottom: 0;">
                                <input type="radio" name="konteks_peluang_bukti" value="Tersedia"
                                    {{ in_array($mapa01->konteks_peluang_bukti ?? '', ['Tersedia', 'tersedia']) ? 'checked' : '' }}>
                                <span>Tersedia</span>
                            </label>
                            <label class="opsi-card-modern" style="flex: 1; margin-bottom: 0;">
                                <input type="radio" name="konteks_peluang_bukti" value="Terbatas"
                                    {{ in_array($mapa01->konteks_peluang_bukti ?? '', ['Terbatas', 'terbatas']) ? 'checked' : '' }}>
                                <span>Terbatas</span>
                            </label>
                        </div>
                    </td>
                </tr>

                <tr>
                    <td style="font-weight: 600; color: #334155;">Hubungan antara standar kompetensi dan:</td>
                    <td>
                        <!-- Bukti untuk mendukung asesmen -->
                        <div class="rating-row-modern">
                            <span class="rating-row-title">Bukti untuk mendukung asesmen:</span>
                            <div class="rating-pills-wrap">
                                <label class="rating-pill">
                                    <input type="radio" name="hubungan_standar_bukti" value="senang" {{ (($mapa01->hubungan_standar_bukti ?? '') == 'senang') ? 'checked' : '' }}>
                                    <span>Sangat Relevan</span>
                                </label>
                                <label class="rating-pill">
                                    <input type="radio" name="hubungan_standar_bukti" value="datar" {{ (($mapa01->hubungan_standar_bukti ?? '') == 'datar') ? 'checked' : '' }}>
                                    <span>Cukup Sesuai</span>
                                </label>
                                <label class="rating-pill">
                                    <input type="radio" name="hubungan_standar_bukti" value="sedih" {{ (($mapa01->hubungan_standar_bukti ?? '') == 'sedih') ? 'checked' : '' }}>
                                    <span>Kurang Sesuai</span>
                                </label>
                            </div>
                        </div>

                        <!-- Aktivitas kerja di tempat kerja Asesi -->
                        <div class="rating-row-modern">
                            <span class="rating-row-title">Aktivitas kerja di tempat kerja Asesi:</span>
                            <div class="rating-pills-wrap">
                                <label class="rating-pill">
                                    <input type="radio" name="hubungan_standar_aktivitas" value="senang" {{ (($mapa01->hubungan_standar_aktivitas ?? '') == 'senang') ? 'checked' : '' }}>
                                    <span>Sangat Relevan</span>
                                </label>
                                <label class="rating-pill">
                                    <input type="radio" name="hubungan_standar_aktivitas" value="datar" {{ (($mapa01->hubungan_standar_aktivitas ?? '') == 'datar') ? 'checked' : '' }}>
                                    <span>Cukup Sesuai</span>
                                </label>
                                <label class="rating-pill">
                                    <input type="radio" name="hubungan_standar_aktivitas" value="sedih" {{ (($mapa01->hubungan_standar_aktivitas ?? '') == 'sedih') ? 'checked' : '' }}>
                                    <span>Kurang Sesuai</span>
                                </label>
                            </div>
                        </div>

                        <!-- Kegiatan Pembelajaran -->
                        <div class="rating-row-modern" style="margin-bottom: 0;">
                            <span class="rating-row-title">Kegiatan Pembelajaran:</span>
                            <div class="rating-pills-wrap">
                                <label class="rating-pill">
                                    <input type="radio" name="hubungan_standar_pembelajaran" value="senang" {{ (($mapa01->hubungan_standar_pembelajaran ?? '') == 'senang') ? 'checked' : '' }}>
                                    <span>Sangat Relevan</span>
                                </label>
                                <label class="rating-pill">
                                    <input type="radio" name="hubungan_standar_pembelajaran" value="datar" {{ (($mapa01->hubungan_standar_pembelajaran ?? '') == 'datar') ? 'checked' : '' }}>
                                    <span>Cukup Sesuai</span>
                                </label>
                                <label class="rating-pill">
                                    <input type="radio" name="hubungan_standar_pembelajaran" value="sedih" {{ (($mapa01->hubungan_standar_pembelajaran ?? '') == 'sedih') ? 'checked' : '' }}>
                                    <span>Kurang Sesuai</span>
                                </label>
                            </div>
                        </div>
                    </td>
                </tr>

                <tr>
                    <td style="font-weight: 600; color: #334155;">Siapa yang melakukan asesmen / RPL</td>
                    <td>
                        @php
                            $opsiPelaksana = ['Lembaga Sertifikasi', 'Organisasi Pelatihan', 'Asesor Perusahaan'];
                        @endphp
                        <div class="opsi-grid-2">
                            @foreach($opsiPelaksana as $idx => $pel)
                                <label class="opsi-card-modern" style="margin-bottom: 0;">
                                    <input type="checkbox" name="pelaksana_asesmen[]" value="{{ $pel }}"
                                        {{ in_array($pel, $pelaksanaSaved) ? 'checked' : '' }}>
                                    <span>{{ $pel }}</span>
                                </label>
                            @endforeach
                        </div>
                    </td>
                </tr>

                <!-- KONFIRMASI DENGAN ORANG YANG RELEVAN -->
                <tr>
                    <td style="background: #f8fafc;"></td>
                    <td style="font-weight: 700; color: #0f172a;">
                        Konfirmasi dengan orang yang relevan
                        <div style="font-size: 0.72rem; font-weight: normal; color: #64748b; margin-top: 2px;">
                            *Coret yang tidak perlu &bull; Tersinkronisasi dengan tabel konfirmasi di bawah
                        </div>
                    </td>
                    <td colspan="2">
                        <div class="opsi-grid-2">
                            @foreach($roleDefinitions as $roleKey => $roleData)
                                @php
                                    $isRoleChecked = false;
                                    foreach ($roleData['aliases'] as $alias) {
                                        if (in_array($alias, $konfirmasiSaved, true)) {
                                            $isRoleChecked = true;
                                            break;
                                        }
                                    }
                                    if (!$isRoleChecked && !empty($konfirmasiTabelSaved[$roleKey]['relevan'])) {
                                        $isRoleChecked = true;
                                    }
                                @endphp
                                <label class="opsi-card-modern">
                                    <input type="checkbox" 
                                           name="konfirmasi_orang_relevan[]" 
                                           value="{{ $roleData['label'] }}"
                                           data-role-key="{{ $roleKey }}"
                                           class="input-konfirmasi-1-1"
                                           onchange="syncKonfirmasiToTabel('{{ $roleKey }}', this.checked)"
                                           {{ $isRoleChecked ? 'checked' : '' }}>
                                    <span>{{ $roleData['label'] }}</span>
                                </label>
                            @endforeach
                        </div>
                    </td>
                </tr>

                <!-- 1.2 STANDAR INDUSTRI ATAU TEMPAT KERJA -->
                <tr>
                    <td style="font-weight: 800; text-align: center; background: #f8fafc;">1.2</td>
                    <td style="font-weight: 700; color: #0f172a;">Standar Industri atau Tempat Kerja</td>
                    <td colspan="2">
                        @php
                            $opsiStandar = [
                                'Standar Kompetensi:',
                                'Kriteria asesmen dari kurikulum pelatihan',
                                'Spesifikasi kinerja suatu perusahaan atau industri:',
                                'Spesifikasi Produk:',
                                'Pedoman khusus:'
                            ];
                        @endphp
                        <div class="opsi-grid-2">
                            @foreach($opsiStandar as $idx => $st)
                                <label class="opsi-card-modern">
                                    <input type="checkbox" name="standar_industri[]" value="{{ $st }}"
                                        {{ in_array($st, $standarIndustriSaved) ? 'checked' : '' }}>
                                    <span>{{ $st }}</span>
                                </label>
                            @endforeach
                        </div>
                    </td>
                </tr>

            </table>

        </div>

        <!-- ========================================================================= -->
        <!-- HALAMAN 2: 2. MEMPERSIAPKAN RENCANA ASESMEN (MATRIKS UNIT KOMPETENSI) -->
        <!-- ========================================================================= -->
        <div class="dokumen-kertas-mapa page-break">
            
            <div class="section-header-modern">
                <span class="badge-num">2</span>
                <span>Mempersiapkan Rencana Asesmen</span>
            </div>

            <!-- TABEL DAFTAR KELOMPOK PEKERJAAN & UNIT KOMPETENSI -->
            <table class="tabel-mapa-modern" style="border-top: none; margin-bottom: 1.5rem;">
                <thead>
                    <tr>
                        <th style="width: 22%; text-align: center;" class="th-slate">Kelompok Pekerjaan</th>
                        <th style="width: 8%; text-align: center;" class="th-slate">No.</th>
                        <th style="width: 25%;" class="th-slate">Kode Unit</th>
                        <th class="th-slate">Judul Unit</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($pendaftaran->skema->unitKompetensi as $indexUnit => $unit)
                        <tr>
                            @if($indexUnit === 0)
                                <td rowspan="{{ count($pendaftaran->skema->unitKompetensi) }}" style="vertical-align: middle; text-align: center; font-weight: 700; color: #1e293b; background-color: #f8fafc;">
                                    Kelompok Pekerjaan 1
                                </td>
                            @endif
                            <td style="text-align: center; font-weight: 700; color: #475569;">{{ $indexUnit + 1 }}.</td>
                            <td style="font-weight: 700; color: #0284c7;">{{ $unit->kode_unit }}</td>
                            <td style="font-weight: 600; color: #0f172a;">{{ $unit->judul_unit }}</td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="4" style="text-align: center; color: #64748b;">Belum ada data unit kompetensi untuk skema ini.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>

            <!-- TABEL MATRIKS BUKTI & PERANGKAT ASESMEN -->
            <div style="font-weight: 800; font-size: 0.95rem; margin-bottom: 0.6rem; color: #0f172a;">
                Matriks Perangkat Asesmen & Bukti-Bukti Kompetensi:
            </div>

            <div style="overflow-x: auto; margin-bottom: 1.5rem; border: 1px solid #cbd5e1; border-radius: 8px;">
                <table class="tabel-mapa-modern" style="font-size: 0.82rem; min-width: 1100px; margin-bottom: 0;">
                    <thead>
                        <tr>
                            <th rowspan="3" style="width: 22%; min-width: 220px; vertical-align: middle; text-align: center;" class="th-navy">
                                Unit Kompetensi
                            </th>
                            <th rowspan="3" style="width: 34%; min-width: 320px; vertical-align: middle; text-align: center;" class="th-navy">
                                Bukti-Bukti<br>
                                <span style="font-weight: 400; font-size: 0.72rem; opacity: 0.85;">(Kinerja, Produk, Portofolio, Pengetahuan)</span>
                            </th>
                            <th colspan="3" style="text-align: center;" class="th-navy">Jenis Bukti</th>
                            <th colspan="8" style="text-align: center;" class="th-navy">
                                Metode dan Perangkat Asesmen
                            </th>
                        </tr>
                        <tr>
                            <th rowspan="2" style="width: 3.5%; min-width: 38px; text-align: center;" class="th-slate" title="L = Langsung">L</th>
                            <th rowspan="2" style="width: 3.5%; min-width: 38px; text-align: center;" class="th-slate" title="TL = Tidak Langsung">TL</th>
                            <th rowspan="2" style="width: 3.5%; min-width: 38px; text-align: center;" class="th-slate" title="T = Tambahan">T</th>
                            
                            <!-- Sub Kolom Metode -->
                            <th style="width: 5%; min-width: 46px; text-align: center; font-size: 0.75rem;" class="th-slate">CL</th>
                            <th style="width: 5%; min-width: 46px; text-align: center; font-size: 0.75rem;" class="th-slate">DIT</th>
                            <th style="width: 5%; min-width: 46px; text-align: center; font-size: 0.75rem;" class="th-slate">DPL</th>
                            <th style="width: 5%; min-width: 46px; text-align: center; font-size: 0.75rem;" class="th-slate">DPT</th>
                            <th style="width: 5%; min-width: 46px; text-align: center; font-size: 0.75rem;" class="th-slate">VPK</th>
                            <th style="width: 5%; min-width: 46px; text-align: center; font-size: 0.75rem;" class="th-slate">CVP</th>
                            <th style="width: 5%; min-width: 46px; text-align: center; font-size: 0.75rem;" class="th-slate">CRP</th>
                            <th style="width: 5%; min-width: 46px; text-align: center; font-size: 0.75rem;" class="th-slate">PW</th>
                        </tr>
                        <tr>
                            <th style="font-size: 0.65rem; font-weight: 500; text-align: center; line-height: 1.1; background: #f8fafc;">Observasi</th>
                            <th style="font-size: 0.65rem; font-weight: 500; text-align: center; line-height: 1.1; background: #f8fafc;">Instruksi</th>
                            <th style="font-size: 0.65rem; font-weight: 500; text-align: center; line-height: 1.1; background: #f8fafc;">Lisan</th>
                            <th style="font-size: 0.65rem; font-weight: 500; text-align: center; line-height: 1.1; background: #f8fafc;">Tertulis</th>
                            <th style="font-size: 0.65rem; font-weight: 500; text-align: center; line-height: 1.1; background: #f8fafc;">Pihak 3</th>
                            <th style="font-size: 0.65rem; font-weight: 500; text-align: center; line-height: 1.1; background: #f8fafc;">Portofolio</th>
                            <th style="font-size: 0.65rem; font-weight: 500; text-align: center; line-height: 1.1; background: #f8fafc;">Produk</th>
                            <th style="font-size: 0.65rem; font-weight: 500; text-align: center; line-height: 1.1; background: #f8fafc;">Wawancara</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($pendaftaran->skema->unitKompetensi as $indexUnit => $unit)
                            @php
                                $uId = $unit->id;
                                $savedUnit = $matriksSaved[$uId] ?? [];
                                $savedL = !empty($savedUnit['l']);
                                $savedTl = !empty($savedUnit['tl']);
                                $savedT = !empty($savedUnit['t']);
                                $savedMethods = (array) ($savedUnit['methods'] ?? []);
                                $savedBukti = $savedUnit['bukti'] ?? '';
                            @endphp
                            <tr class="row-matriks-asesmen">
                                <td style="font-weight: 700; color: #0f172a; vertical-align: middle;">
                                    <div style="color: #0284c7; font-size: 0.82rem; font-weight: 800;">{{ $unit->kode_unit }}</div>
                                    <div style="font-size: 0.84rem; font-weight: 600; color: #1e293b; line-height: 1.4; margin-top: 0.25rem;">{{ $unit->judul_unit }}</div>
                                </td>
                                <td>
                                    <textarea name="rencana_unit_matriks[{{ $uId }}][bukti]" rows="3" class="textarea-bukti-mapa" placeholder="Tuliskan bukti unjuk kerja, produk, portofolio...">{{ $savedBukti }}</textarea>
                                </td>
                                <!-- JENIS BUKTI -->
                                <td style="text-align: center; vertical-align: middle;">
                                    <input type="checkbox" class="chk-bukti-l" name="rencana_unit_matriks[{{ $uId }}][l]" value="1" {{ $savedL ? 'checked' : '' }} style="accent-color: #36648b; width: 16px; height: 16px; cursor: pointer;">
                                </td>
                                <td style="text-align: center; vertical-align: middle;">
                                    <input type="checkbox" class="chk-bukti-tl" name="rencana_unit_matriks[{{ $uId }}][tl]" value="1" {{ $savedTl ? 'checked' : '' }} style="accent-color: #36648b; width: 16px; height: 16px; cursor: pointer;">
                                </td>
                                <td style="text-align: center; vertical-align: middle;">
                                    <input type="checkbox" class="chk-bukti-t" name="rencana_unit_matriks[{{ $uId }}][t]" value="1" {{ $savedT ? 'checked' : '' }} style="accent-color: #36648b; width: 16px; height: 16px; cursor: pointer;">
                                </td>

                                <!-- METODE & PERANGKAT -->
                                <td style="text-align: center; vertical-align: middle;">
                                    <input type="checkbox" class="chk-metode chk-metode-l" data-type="l" name="rencana_unit_matriks[{{ $uId }}][methods][]" value="CL" {{ in_array('CL', $savedMethods) ? 'checked' : '' }} style="accent-color: #36648b; cursor: pointer; width: 15px; height: 15px;">
                                </td>
                                <td style="text-align: center; vertical-align: middle;">
                                    <input type="checkbox" class="chk-metode chk-metode-l" data-type="l" name="rencana_unit_matriks[{{ $uId }}][methods][]" value="DIT" {{ in_array('DIT', $savedMethods) ? 'checked' : '' }} style="accent-color: #36648b; cursor: pointer; width: 15px; height: 15px;">
                                </td>
                                <td style="text-align: center; vertical-align: middle;">
                                    <input type="checkbox" class="chk-metode chk-metode-t" data-type="t" name="rencana_unit_matriks[{{ $uId }}][methods][]" value="DPL" {{ in_array('DPL', $savedMethods) ? 'checked' : '' }} style="accent-color: #36648b; cursor: pointer; width: 15px; height: 15px;">
                                </td>
                                <td style="text-align: center; vertical-align: middle;">
                                    <input type="checkbox" class="chk-metode chk-metode-t" data-type="t" name="rencana_unit_matriks[{{ $uId }}][methods][]" value="DPT" {{ in_array('DPT', $savedMethods) ? 'checked' : '' }} style="accent-color: #36648b; cursor: pointer; width: 15px; height: 15px;">
                                </td>
                                <td style="text-align: center; vertical-align: middle;">
                                    <input type="checkbox" class="chk-metode chk-metode-tl" data-type="tl" name="rencana_unit_matriks[{{ $uId }}][methods][]" value="VPK" {{ in_array('VPK', $savedMethods) ? 'checked' : '' }} style="accent-color: #36648b; cursor: pointer; width: 15px; height: 15px;">
                                </td>
                                <td style="text-align: center; vertical-align: middle;">
                                    <input type="checkbox" class="chk-metode chk-metode-tl" data-type="tl" name="rencana_unit_matriks[{{ $uId }}][methods][]" value="CVP" {{ in_array('CVP', $savedMethods) ? 'checked' : '' }} style="accent-color: #36648b; cursor: pointer; width: 15px; height: 15px;">
                                </td>
                                <td style="text-align: center; vertical-align: middle;">
                                    <input type="checkbox" class="chk-metode" name="rencana_unit_matriks[{{ $uId }}][methods][]" value="CRP" {{ in_array('CRP', $savedMethods) ? 'checked' : '' }} style="accent-color: #36648b; cursor: pointer; width: 15px; height: 15px;">
                                </td>
                                <td style="text-align: center; vertical-align: middle;">
                                    <input type="checkbox" class="chk-metode" name="rencana_unit_matriks[{{ $uId }}][methods][]" value="PW" {{ in_array('PW', $savedMethods) ? 'checked' : '' }} style="accent-color: #36648b; cursor: pointer; width: 15px; height: 15px;">
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="13" style="text-align: center; color: #64748b; padding: 1.5rem;">Tidak ada unit kompetensi ditemukan.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <!-- KETERANGAN SINGKATAN METODE -->
            <div class="legend-card-mapa">
                <div style="font-weight: 700; margin-bottom: 0.35rem; color: #0f172a;">
                    Keterangan Singkatan Metode & Perangkat Asesmen:
                </div>
                <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(220px, 1fr)); gap: 0.35rem;">
                    <div><span class="legend-badge">CL</span> Ceklis Observasi Praktik (IA.01)</div>
                    <div><span class="legend-badge">DIT</span> Daftar Instruksi Terstruktur (IA.04)</div>
                    <div><span class="legend-badge">DPL</span> Pertanyaan Lisan (IA.07)</div>
                    <div><span class="legend-badge">DPT</span> Pertanyaan Tertulis (IA.05/06)</div>
                    <div><span class="legend-badge">VPK</span> Verifikasi Pihak Ketiga (IA.10)</div>
                    <div><span class="legend-badge">CVP</span> Verifikasi Portofolio (IA.08)</div>
                    <div><span class="legend-badge">CRP</span> Reviu Produk (IA.11)</div>
                    <div><span class="legend-badge">PW</span> Pertanyaan Wawancara (IA.09)</div>
                </div>
            </div>

        </div>

        <!-- ========================================================================= -->
        <!-- HALAMAN 3: 3. MODIFIKASI & KONTEKSTUALISASI, KONFIRMASI, VALIDATOR -->
        <!-- ========================================================================= -->
        <div class="dokumen-kertas-mapa page-break">
            
            <div class="section-header-modern">
                <span class="badge-num">3</span>
                <span>Mengidentifikasi Persyaratan Modifikasi dan Kontekstualisasi</span>
            </div>

            <table class="tabel-mapa-modern" style="border-top: none; margin-bottom: 1.5rem;">
                
                <!-- 3.1. a. Karakteristik Kandidat -->
                <tr>
                    <td style="width: 42%; font-weight: 700; color: #0f172a;">
                        3.1. a. Karakteristik Kandidat:
                    </td>
                    <td>
                        <div class="toggle-segment-wrap">
                            <label class="toggle-segment-btn">
                                <input type="radio" name="karakteristik_kandidat_status" value="ada" 
                                    {{ ($mapa01->karakteristik_kandidat_status ?? '') == 'ada' ? 'checked' : '' }}>
                                <span>Ada</span>
                            </label>
                            <label class="toggle-segment-btn">
                                <input type="radio" name="karakteristik_kandidat_status" value="tidak_ada" 
                                    {{ ($mapa01->karakteristik_kandidat_status ?? '') == 'tidak_ada' ? 'checked' : '' }}>
                                <span>Tidak Ada*</span>
                            </label>
                        </div>
                        <div style="font-size: 0.8rem; color: #475569; margin-bottom: 0.25rem;">
                            Jika Ada, tuliskan:
                            <span class="badge-wajib-ada text-rose-600 font-bold" style="display: {{ ($mapa01->karakteristik_kandidat_status ?? '') == 'ada' ? 'inline' : 'none' }};">* (Wajib diisi jika Ada)</span>
                        </div>
                        <input type="text" name="karakteristik_kandidat_teks" 
                            value="{{ ($mapa01->karakteristik_kandidat_status ?? '') == 'ada' ? ($mapa01->karakteristik_kandidat_teks ?? '') : '' }}" 
                            class="input-inline-modern {{ ($mapa01->karakteristik_kandidat_status ?? '') !== 'ada' ? 'is-disabled' : '' }}" 
                            data-status-field="karakteristik_kandidat_status" 
                            data-validation-label="Karakteristik Kandidat" 
                            data-placeholder-default="Tuliskan spesifikasi karakteristik khusus jika ada..." 
                            data-placeholder-disabled="Tidak perlu diisi (Memilih Tidak Ada)" 
                            placeholder="{{ ($mapa01->karakteristik_kandidat_status ?? '') == 'ada' ? 'Tuliskan spesifikasi karakteristik khusus jika ada...' : 'Tidak perlu diisi (Memilih Tidak Ada)' }}"
                            {{ ($mapa01->karakteristik_kandidat_status ?? '') !== 'ada' ? 'disabled' : '' }}>
                    </td>
                </tr>

                <!-- 3.1. b. Kebutuhan kontekstualisasi -->
                <tr>
                    <td style="font-weight: 700; color: #0f172a;">
                        b. Kebutuhan kontekstualisasi terkait tempat kerja:
                    </td>
                    <td>
                        <div class="toggle-segment-wrap">
                            <label class="toggle-segment-btn">
                                <input type="radio" name="kebutuhan_kontekstualisasi_status" value="ada"
                                    {{ ($mapa01->kebutuhan_kontekstualisasi_status ?? '') == 'ada' ? 'checked' : '' }}>
                                <span>Ada</span>
                            </label>
                            <label class="toggle-segment-btn">
                                <input type="radio" name="kebutuhan_kontekstualisasi_status" value="tidak_ada" 
                                    {{ ($mapa01->kebutuhan_kontekstualisasi_status ?? '') == 'tidak_ada' ? 'checked' : '' }}>
                                <span>Tidak Ada*</span>
                            </label>
                        </div>
                        <div style="font-size: 0.8rem; color: #475569; margin-bottom: 0.25rem;">
                            Jika Ada, tuliskan:
                            <span class="badge-wajib-ada text-rose-600 font-bold" style="display: {{ ($mapa01->kebutuhan_kontekstualisasi_status ?? '') == 'ada' ? 'inline' : 'none' }};">* (Wajib diisi jika Ada)</span>
                        </div>
                        <input type="text" name="kebutuhan_kontekstualisasi_teks" 
                            value="{{ ($mapa01->kebutuhan_kontekstualisasi_status ?? '') == 'ada' ? ($mapa01->kebutuhan_kontekstualisasi_teks ?? '') : '' }}" 
                            class="input-inline-modern {{ ($mapa01->kebutuhan_kontekstualisasi_status ?? '') !== 'ada' ? 'is-disabled' : '' }}" 
                            data-status-field="kebutuhan_kontekstualisasi_status" 
                            data-validation-label="Kebutuhan Kontekstualisasi" 
                            data-placeholder-default="Tuliskan kebutuhan kontekstualisasi jika ada..." 
                            data-placeholder-disabled="Tidak perlu diisi (Memilih Tidak Ada)" 
                            placeholder="{{ ($mapa01->kebutuhan_kontekstualisasi_status ?? '') == 'ada' ? 'Tuliskan kebutuhan kontekstualisasi jika ada...' : 'Tidak perlu diisi (Memilih Tidak Ada)' }}"
                            {{ ($mapa01->kebutuhan_kontekstualisasi_status ?? '') !== 'ada' ? 'disabled' : '' }}>
                    </td>
                </tr>

                <!-- 3.2. Saran paket pelatihan -->
                <tr>
                    <td style="font-weight: 700; color: #0f172a;">
                        3.2. Saran yang diberikan oleh paket pelatihan atau pengembang pelatihan:
                    </td>
                    <td>
                        <div class="toggle-segment-wrap">
                            <label class="toggle-segment-btn">
                                <input type="radio" name="saran_pelatihan_status" value="ada"
                                    {{ ($mapa01->saran_pelatihan_status ?? '') == 'ada' ? 'checked' : '' }}>
                                <span>Ada</span>
                            </label>
                            <label class="toggle-segment-btn">
                                <input type="radio" name="saran_pelatihan_status" value="tidak_ada" 
                                    {{ ($mapa01->saran_pelatihan_status ?? '') == 'tidak_ada' ? 'checked' : '' }}>
                                <span>Tidak Ada*</span>
                            </label>
                        </div>
                        <div style="font-size: 0.8rem; color: #475569; margin-bottom: 0.25rem;">
                            Jika Ada, tuliskan:
                            <span class="badge-wajib-ada text-rose-600 font-bold" style="display: {{ ($mapa01->saran_pelatihan_status ?? '') == 'ada' ? 'inline' : 'none' }};">* (Wajib diisi jika Ada)</span>
                        </div>
                        <input type="text" name="saran_pelatihan_teks" 
                            value="{{ ($mapa01->saran_pelatihan_status ?? '') == 'ada' ? ($mapa01->saran_pelatihan_teks ?? '') : '' }}" 
                            class="input-inline-modern {{ ($mapa01->saran_pelatihan_status ?? '') !== 'ada' ? 'is-disabled' : '' }}" 
                            data-status-field="saran_pelatihan_status" 
                            data-validation-label="Saran Pelatihan" 
                            data-placeholder-default="Tuliskan saran paket pelatihan jika ada..." 
                            data-placeholder-disabled="Tidak perlu diisi (Memilih Tidak Ada)" 
                            placeholder="{{ ($mapa01->saran_pelatihan_status ?? '') == 'ada' ? 'Tuliskan saran paket pelatihan jika ada...' : 'Tidak perlu diisi (Memilih Tidak Ada)' }}"
                            {{ ($mapa01->saran_pelatihan_status ?? '') !== 'ada' ? 'disabled' : '' }}>
                    </td>
                </tr>

                <!-- 3.3. Penyesuaian perangkat -->
                <tr>
                    <td style="font-weight: 700; color: #0f172a;">
                        3.3. Penyesuaian perangkat asesmen terkait kebutuhan kontekstualisasi:
                    </td>
                    <td>
                        <div class="toggle-segment-wrap">
                            <label class="toggle-segment-btn">
                                <input type="radio" name="penyesuaian_perangkat_status" value="ada"
                                    {{ ($mapa01->penyesuaian_perangkat_status ?? '') == 'ada' ? 'checked' : '' }}>
                                <span>Ada</span>
                            </label>
                            <label class="toggle-segment-btn">
                                <input type="radio" name="penyesuaian_perangkat_status" value="tidak_ada" 
                                    {{ ($mapa01->penyesuaian_perangkat_status ?? '') == 'tidak_ada' ? 'checked' : '' }}>
                                <span>Tidak Ada*</span>
                            </label>
                        </div>
                        <div style="font-size: 0.8rem; color: #475569; margin-bottom: 0.25rem;">
                            Jika Ada, tuliskan:
                            <span class="badge-wajib-ada text-rose-600 font-bold" style="display: {{ ($mapa01->penyesuaian_perangkat_status ?? '') == 'ada' ? 'inline' : 'none' }};">* (Wajib diisi jika Ada)</span>
                        </div>
                        <input type="text" name="penyesuaian_perangkat_teks" 
                            value="{{ ($mapa01->penyesuaian_perangkat_status ?? '') == 'ada' ? ($mapa01->penyesuaian_perangkat_teks ?? '') : '' }}" 
                            class="input-inline-modern {{ ($mapa01->penyesuaian_perangkat_status ?? '') !== 'ada' ? 'is-disabled' : '' }}" 
                            data-status-field="penyesuaian_perangkat_status" 
                            data-validation-label="Penyesuaian Perangkat" 
                            data-placeholder-default="Tuliskan penyesuaian perangkat jika ada..." 
                            data-placeholder-disabled="Tidak perlu diisi (Memilih Tidak Ada)" 
                            placeholder="{{ ($mapa01->penyesuaian_perangkat_status ?? '') == 'ada' ? 'Tuliskan penyesuaian perangkat jika ada...' : 'Tidak perlu diisi (Memilih Tidak Ada)' }}"
                            {{ ($mapa01->penyesuaian_perangkat_status ?? '') !== 'ada' ? 'disabled' : '' }}>
                    </td>
                </tr>

                <!-- 3.4. Peluang terintegrasi -->
                <tr>
                    <td style="font-weight: 700; color: #0f172a;">
                        3.4. Peluang untuk kegiatan asesmen terintegrasi dan mencatat setiap perubahan yang diperlukan:
                    </td>
                    <td>
                        <div class="toggle-segment-wrap">
                            <label class="toggle-segment-btn">
                                <input type="radio" name="peluang_terintegrasi_status" value="ada"
                                    {{ ($mapa01->peluang_terintegrasi_status ?? '') == 'ada' ? 'checked' : '' }}>
                                <span>Ada</span>
                            </label>
                            <label class="toggle-segment-btn">
                                <input type="radio" name="peluang_terintegrasi_status" value="tidak_ada" 
                                    {{ ($mapa01->peluang_terintegrasi_status ?? '') == 'tidak_ada' ? 'checked' : '' }}>
                                <span>Tidak Ada*</span>
                            </label>
                        </div>
                        <div style="font-size: 0.8rem; color: #475569; margin-bottom: 0.25rem;">
                            Jika Ada, tuliskan:
                            <span class="badge-wajib-ada text-rose-600 font-bold" style="display: {{ ($mapa01->peluang_terintegrasi_status ?? '') == 'ada' ? 'inline' : 'none' }};">* (Wajib diisi jika Ada)</span>
                        </div>
                        <input type="text" name="peluang_terintegrasi_teks" 
                            value="{{ ($mapa01->peluang_terintegrasi_status ?? '') == 'ada' ? ($mapa01->peluang_terintegrasi_teks ?? '') : '' }}" 
                            class="input-inline-modern {{ ($mapa01->peluang_terintegrasi_status ?? '') !== 'ada' ? 'is-disabled' : '' }}" 
                            data-status-field="peluang_terintegrasi_status" 
                            data-validation-label="Peluang Kegiatan Terintegrasi" 
                            data-placeholder-default="Tuliskan peluang asesmen terintegrasi jika ada..." 
                            data-placeholder-disabled="Tidak perlu diisi (Memilih Tidak Ada)" 
                            placeholder="{{ ($mapa01->peluang_terintegrasi_status ?? '') == 'ada' ? 'Tuliskan peluang asesmen terintegrasi jika ada...' : 'Tidak perlu diisi (Memilih Tidak Ada)' }}"
                            {{ ($mapa01->peluang_terintegrasi_status ?? '') !== 'ada' ? 'disabled' : '' }}>
                    </td>
                </tr>
            </table>
            <div style="font-size: 0.75rem; font-style: italic; color: #64748b; margin-top: -1rem; margin-bottom: 1.5rem;">*Coret yang tidak perlu</div>

            <!-- TABEL KONFIRMASI DENGAN ORANG YANG RELEVAN -->
            <div style="font-weight: 800; font-size: 0.95rem; margin-top: 1.5rem; margin-bottom: 0.6rem; color: #0f172a; display: flex; align-items: center; justify-content: space-between; flex-wrap: wrap; gap: 0.5rem;">
                <span>Konfirmasi dengan Orang yang Relevan:</span>
                <span style="font-size: 0.75rem; font-style: italic; color: #64748b; font-weight: normal;">
                    *Coret yang tidak perlu &bull; Tersinkronisasi dengan Bagian 1.1
                </span>
            </div>

            @php
                $defaultLspManagerNama = $adminValidatorNama 
                    ?: (auth()->check() && in_array(auth()->user()->peran, ['admin', 'superadmin']) 
                        ? auth()->user()->nama_lengkap 
                        : (\App\Models\Pengguna::whereIn('peran', ['admin', 'superadmin'])->value('nama_lengkap') ?: 'Manajer Sertifikasi LSP'));
            @endphp

            <div style="overflow-x: auto; margin-bottom: 1.5rem; border: 1px solid #cbd5e1; border-radius: 6px;">
                <table class="tabel-mapa-modern" id="tabel-konfirmasi-relevan" style="font-size: 0.82rem; width: 100%; margin-bottom: 0; border: none;">
                    <thead>
                        <tr>
                            <th class="th-slate" style="width: 44%; text-align: left; padding: 0.55rem 0.75rem;">
                                Orang yang Relevan (*Coret jika tidak perlu)
                            </th>
                            <th class="th-slate" style="width: 32%; text-align: left; padding: 0.55rem 0.75rem;">
                                Nama Pejabat / Petugas
                            </th>
                            <th class="th-slate" style="width: 24%; text-align: left; padding: 0.55rem 0.75rem;">
                                Tanggal Konfirmasi
                            </th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($roleDefinitions as $key => $roleData)
                            @php
                                $isRelevan = false;
                                foreach ($roleData['aliases'] as $alias) {
                                    if (in_array($alias, $konfirmasiSaved, true)) {
                                        $isRelevan = true;
                                        break;
                                    }
                                }
                                if (!$isRelevan && !empty($konfirmasiTabelSaved[$key]['relevan'])) {
                                    $isRelevan = true;
                                }
                                if (!$isRelevan && !empty($konfirmasiTabelSaved[$key]['nama'])) {
                                    $isRelevan = true;
                                }

                                $namaVal = $konfirmasiTabelSaved[$key]['nama'] ?? '';
                                $ttdVal = $konfirmasiTabelSaved[$key]['ttd_tanggal'] ?? '';
                                $isComplete = !empty(trim($namaVal)) && !empty(trim($ttdVal));
                            @endphp
                            <tr id="row-konfirmasi-{{ $key }}" 
                                class="transition-colors {{ $isRelevan ? 'baris-relevan-aktif' : 'baris-relevan-dicoret' }}">
                                
                                <!-- Kolom 1: Status & Jabatan -->
                                <td style="padding: 0.6rem 0.75rem; vertical-align: top;">
                                    <div class="flex items-start gap-2.5">
                                        <input type="checkbox" 
                                               name="konfirmasi_pihak_relevan_tabel[{{ $key }}][relevan]" 
                                               value="1" 
                                               id="check-tabel-{{ $key }}" 
                                               class="toggle-relevan-tabel mt-1 h-4 w-4 rounded border-slate-300 text-slate-800 focus:ring-slate-700 cursor-pointer"
                                               data-role-key="{{ $key }}"
                                               onchange="syncTabelToKonfirmasi('{{ $key }}', this.checked)" 
                                               {{ $isRelevan ? 'checked' : '' }}>
                                        
                                        <div class="flex-1">
                                            <label for="check-tabel-{{ $key }}" 
                                                   id="title-role-{{ $key }}"
                                                   class="role-title block text-sm font-semibold cursor-pointer {{ $isRelevan ? 'text-slate-900' : 'text-slate-400 line-through' }}">
                                                {{ $roleData['label'] }}
                                            </label>
                                            <div class="text-[11px] text-slate-500 mt-0.5">
                                                {{ $roleData['sublabel'] }}
                                            </div>
                                            <div class="mt-1.5" id="badge-wrapper-{{ $key }}">
                                                @if($isRelevan)
                                                    <span class="inline-flex items-center gap-1 text-[10px] font-bold px-2 py-0.5 rounded-full bg-blue-50 text-blue-800 border border-blue-200" id="badge-role-{{ $key }}">
                                                        <span class="w-1.5 h-1.5 rounded-full bg-blue-700"></span> Relevan &bull; Dikonfirmasi
                                                    </span>
                                                @else
                                                    <span class="inline-flex items-center gap-1 text-[10px] font-semibold px-2 py-0.5 rounded-full bg-slate-100 text-slate-500 border border-slate-200" id="badge-role-{{ $key }}">
                                                        <span class="w-1.5 h-1.5 rounded-full bg-slate-400"></span> Dicoret (*tidak perlu)
                                                    </span>
                                                @endif
                                            </div>
                                        </div>
                                    </div>
                                </td>

                                <!-- Kolom 2: Nama Pejabat -->
                                <td style="padding: 0.6rem 0.75rem; vertical-align: top;">
                                    <input type="text" 
                                           name="konfirmasi_pihak_relevan_tabel[{{ $key }}][nama]" 
                                           id="input-nama-{{ $key }}"
                                           value="{{ $namaVal }}" 
                                           oninput="updateRowStatusBadge('{{ $key }}')"
                                           class="input-nama-relevan w-full px-3 py-1.5 text-sm rounded border border-slate-300 focus:border-slate-700 focus:ring-1 focus:ring-slate-700 bg-white placeholder-slate-400 text-slate-800 transition-colors {{ !$isRelevan ? 'bg-slate-100 text-slate-400 cursor-not-allowed' : '' }}" 
                                           placeholder="{{ $isRelevan ? 'Nama pejabat/petugas...' : '— Dicoret (tidak perlu diisi) —' }}"
                                           {{ !$isRelevan ? 'disabled' : '' }}>
                                    
                                    @if($key === 'manajer_lsp')
                                        <div class="mt-1.5 helper-manajer-lsp" id="helper-manajer-{{ $key }}" style="{{ !$isRelevan ? 'display: none;' : '' }}">
                                            <button type="button" 
                                                    onclick="isiOtomatisManajerLsp('{{ $key }}', '{{ addslashes($defaultLspManagerNama) }}')" 
                                                    class="inline-flex items-center gap-1 text-[11px] font-semibold text-sky-700 hover:text-sky-900 hover:underline cursor-pointer">
                                                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z" />
                                                </svg>
                                                Gunakan Data Manajer/Admin LSP ({{ $defaultLspManagerNama }})
                                            </button>
                                        </div>
                                    @endif
                                </td>

                                <!-- Kolom 3: Tanggal Konfirmasi -->
                                <td style="padding: 0.6rem 0.75rem; vertical-align: top;">
                                    <div class="flex flex-col gap-1.5">
                                        <div class="flex items-center gap-1.5">
                                            <input type="text" 
                                                   name="konfirmasi_pihak_relevan_tabel[{{ $key }}][ttd_tanggal]" 
                                                   id="input-tgl-{{ $key }}"
                                                   value="{{ $ttdVal }}" 
                                                   oninput="updateRowStatusBadge('{{ $key }}')"
                                                   class="input-tgl-relevan w-28 px-2.5 py-1.5 text-xs font-semibold rounded border border-slate-300 text-slate-700 bg-white focus:border-slate-700 focus:ring-1 focus:ring-slate-700 placeholder-slate-400 transition-colors {{ !$isRelevan ? 'bg-slate-100 text-slate-400 cursor-not-allowed' : '' }}" 
                                                   placeholder="dd/mm/yyyy"
                                                   {{ !$isRelevan ? 'disabled' : '' }}>
                                            
                                            <button type="button" 
                                                    id="btn-today-{{ $key }}"
                                                    onclick="isiTanggalHariIni('{{ $key }}')"
                                                    class="btn-today-relevan px-2 py-1.5 text-[11px] font-semibold rounded bg-slate-100 hover:bg-slate-200 text-slate-700 border border-slate-300 transition-colors shrink-0 cursor-pointer"
                                                    style="{{ !$isRelevan ? 'display: none;' : '' }}"
                                                    title="Isi dengan tanggal hari ini">
                                                Hari Ini
                                            </button>
                                        </div>

                                        <!-- Status Konfirmasi Dinamis -->
                                        <div id="status-container-{{ $key }}">
                                            @if(!$isRelevan)
                                                <span class="inline-flex items-center gap-1 text-[10px] font-semibold px-2 py-0.5 rounded bg-slate-100 text-slate-600 border border-slate-200">
                                                    <span class="w-1.5 h-1.5 rounded-full bg-slate-400"></span> Dicoret
                                                </span>
                                            @elseif($isComplete)
                                                <span class="inline-flex items-center gap-1 text-[10px] font-bold px-2 py-0.5 rounded bg-emerald-50 text-emerald-700 border border-emerald-200">
                                                    <span class="w-1.5 h-1.5 rounded-full bg-emerald-500"></span> &check; Terkonfirmasi
                                                </span>
                                            @else
                                                <span class="inline-flex items-center gap-1 text-[10px] font-bold px-2 py-0.5 rounded bg-amber-50 text-amber-700 border border-amber-200">
                                                    <span class="w-1.5 h-1.5 rounded-full bg-amber-500"></span> &#9203; Belum Dikonfirmasi
                                                </span>
                                            @endif
                                        </div>
                                    </div>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

            @php
                if (empty($adminValidatorNama)) {
                    $adminValidatorNama = $penyusunTabelSaved['validator_1']['nama'] ?? $dbAdminNama;
                }
                if (empty($adminValidatorMet)) {
                    $adminValidatorMet = $penyusunTabelSaved['validator_1']['nomor_met'] ?? $dbAdminMet;
                }
                if (empty($adminTtd)) {
                    $adminTtd = $mapa01?->tanda_tangan_admin ?: ($penyusunTabelSaved['validator_1']['ttd'] ?? null);
                }
                if (empty($adminTtdTgl)) {
                    $adminTtdTgl = $mapa01?->tanggal_ttd_admin
                        ? $mapa01->tanggal_ttd_admin->timezone('Asia/Jakarta')->format('d/m/Y H:i:s') . ' WIB'
                        : ($penyusunTabelSaved['validator_1']['ttd_tanggal'] ?? '');
                }
            @endphp

            <div class="grid grid-cols-1 {{ $isAdminAuthoring ? '' : 'md:grid-cols-2' }} gap-4 mb-6">
                <!-- 1. PENYUSUN (ASESOR PENGUJI) -->
                @if(!$isAdminAuthoring)
                <div class="relative">
                    <div style="height: 100%; border: 1px solid #dbe2ea; border-radius: 8px; padding: 1rem; background: #fff;">
                        <div style="font-size: .8rem; font-weight: 700; color: #334155; margin-bottom: .25rem;">Tanda Tangan Asesor <span style="color: #dc2626;">*</span></div>
                        <div style="font-size: .72rem; color: #64748b; margin-bottom: .75rem;">{{ $asesorNama }} · {{ $asesorMet }}</div>
                        @if(!$isAdmin && !$isAsesi)
                            <div id="container-ttd-asesor-preview" style="position: relative; margin-bottom: .75rem;">
                                @if($asesorTtd)
                                    <img src="{{ asset($asesorTtd) }}" alt="Tanda tangan asesor tersimpan" style="display: block; max-width: 100%; max-height: 90px; margin: 0 auto; object-fit: contain;">
                                    @if($mapa01?->tanggal_ttd_asesor)
                                        <div style="text-align: center; font-size: .7rem; color: #64748b; margin-top: .25rem;">{{ $mapa01->tanggal_ttd_asesor->timezone('Asia/Jakarta')->format('d/m/Y H:i:s') }} WIB</div>
                                    @endif
                                @else
                                    <div style="padding: .5rem; text-align: center; font-size: .75rem; color: #64748b;">Belum ada tanda tangan yang disimpan.</div>
                                @endif
                            </div>
                            <canvas id="canvas-ttd-asesor" width="900" height="240" style="display: block; width: 100%; height: 190px; border: 1px solid #dbe2ea; border-radius: 6px; background: #fff; cursor: crosshair; touch-action: none;"></canvas>
                            <p style="margin: .45rem 0 .75rem; font-size: .7rem; color: #64748b;">Gunakan mouse, layar sentuh, atau stylus untuk menggambar tanda tangan.</p>
                            <div style="display: flex; gap: .6rem; flex-wrap: wrap;">
                                <button type="button" id="btn-clear-canvas-asesor" class="tombol tombol-sekunder tombol-sm">Hapus</button>
                                <button type="button" onclick="window.saveTtdAsesor()" class="tombol tombol-utama tombol-sm" style="background: #4f46e5; border-color: #4f46e5;">Simpan Tanda Tangan</button>
                            </div>
                        @elseif($asesorTtd)
                            <img src="{{ asset($asesorTtd) }}" alt="Tanda tangan asesor" style="display: block; max-width: 100%; max-height: 120px; margin: .75rem auto; object-fit: contain;">
                            @if($mapa01?->tanggal_ttd_asesor)
                                <div style="text-align: center; font-size: .7rem; color: #64748b;">{{ $mapa01->tanggal_ttd_asesor->timezone('Asia/Jakarta')->format('d/m/Y H:i:s') }} WIB</div>
                            @endif
                        @else
                            <div style="padding: 1.5rem .75rem; text-align: center; color: #b45309; font-size: .78rem;">Menunggu tanda tangan Asesor.</div>
                        @endif

                    <input type="hidden" name="penyusun_validator_tabel[penyusun_1][nama]" value="{{ $asesorNama }}">
                    <input type="hidden" name="penyusun_validator_tabel[penyusun_1][nomor_met]" value="{{ $asesorMet }}">
                    <input type="hidden" name="tanda_tangan_asesor" id="input-ttd-asesor-base64" value="{{ $asesorTtd }}">
                    <input type="hidden" name="penyusun_validator_tabel[penyusun_1][ttd]" id="input-penyusun-ttd" value="{{ $asesorTtd }}">
                    <input type="hidden" name="penyusun_validator_tabel[penyusun_1][ttd_tanggal]" id="input-penyusun-ttd-tgl" value="{{ $penyusunTabelSaved['penyusun_1']['ttd_tanggal'] ?? date('d/m/Y') }}">
                    </div>
                </div>
                @endif

                <!-- 2. VALIDATOR (ADMIN LSP) -->
                @if($isAdminAuthoring)
                <div class="relative" style="border: 1px solid #dbe2ea; border-radius: 8px; padding: 1rem; background: #fff;">
                    <div style="font-size: .8rem; font-weight: 700; color: #334155; margin-bottom: .25rem;">Tanda Tangan Admin <span style="color: #dc2626;">*</span></div>
                    <div style="font-size: .72rem; color: #64748b; margin-bottom: .75rem;">{{ $adminValidatorNama }} · {{ $adminValidatorMet }}</div>
                    <div id="admin-author-signature-preview" style="min-height: 40px; margin-bottom: .75rem;">
                        @if($adminTtd)
                            <img src="{{ asset($adminTtd) }}" alt="Tanda tangan Admin tersimpan" style="display: block; max-width: 100%; max-height: 90px; margin: 0 auto; object-fit: contain;">
                            <div style="text-align: center; font-size: .7rem; color: #64748b;">{{ $adminTtdTgl }}</div>
                        @endif
                    </div>
                    <canvas id="canvas-ttd-admin-author" width="900" height="240" style="display: block; width: 100%; height: 190px; border: 1px solid #dbe2ea; border-radius: 6px; background: #fff; cursor: crosshair; touch-action: none;"></canvas>
                    <p id="admin-author-signature-time" style="margin: .45rem 0 .75rem; font-size: .7rem; color: #64748b;">{{ $adminTtd ? 'Tanda tangan tersimpan: ' . $adminTtdTgl : 'Belum ditandatangani.' }}</p>
                    <div style="display: flex; gap: .6rem; flex-wrap: wrap;">
                        <button type="button" id="btn-clear-canvas-admin-author" class="tombol tombol-sekunder tombol-sm">Hapus</button>
                        <button type="button" onclick="window.saveAdminAuthorSignature()" class="tombol tombol-utama tombol-sm" style="background: #4f46e5; border-color: #4f46e5;">Simpan Tanda Tangan</button>
                    </div>
                    <input type="hidden" name="tanda_tangan_admin" id="input-ttd-admin-author-base64" value="{{ $adminTtd }}">
                    <input type="hidden" name="penyusun_validator_tabel[validator_1][nama]" value="{{ $adminValidatorNama }}">
                    <input type="hidden" name="penyusun_validator_tabel[validator_1][nomor_met]" value="{{ $adminValidatorMet }}">
                </div>
                @else
                <div class="relative" style="border: 1px solid #dbe2ea; border-radius: 8px; padding: 1rem; background: #fff;">
                    <div style="font-size: .8rem; font-weight: 700; color: #334155; margin-bottom: .25rem;">Tanda Tangan Validator (Admin LSP)</div>
                    <div style="font-size: .72rem; color: #64748b; margin-bottom: .75rem;">{{ $adminValidatorNama }} · {{ $adminValidatorMet }}</div>
                    @if($adminTtd)
                        <img src="{{ asset($adminTtd) }}" alt="Tanda tangan admin" style="display: block; max-width: 100%; max-height: 120px; margin: .75rem auto; object-fit: contain;">
                        <div style="text-align: center; font-size: .7rem; color: #64748b;">{{ $adminTtdTgl }}</div>
                    @else
                        <div style="padding: 1.5rem .75rem; text-align: center; color: #64748b; font-size: .78rem;">Menunggu validasi dan tanda tangan Admin.</div>
                    @endif
                    
                    <input type="hidden" name="penyusun_validator_tabel[validator_1][nama]" value="{{ $adminValidatorNama }}">
                    <input type="hidden" name="penyusun_validator_tabel[validator_1][nomor_met]" value="{{ $adminValidatorMet }}">
                    <input type="hidden" name="penyusun_validator_tabel[validator_1][ttd]" id="input-ttd-validator-base64" value="{{ $adminTtd }}">
                    <input type="hidden" name="penyusun_validator_tabel[validator_1][ttd_tanggal]" value="{{ $adminTtdTgl ?: ($isAdmin ? date('d/m/Y') : '') }}">
                    <input type="hidden" name="penyusun_validator_tabel[validator_1][status_validasi]" value="{{ $isValidatedAdmin ? 'tervalidasi' : ($isAdmin ? 'tervalidasi' : 'menunggu') }}">
                    
                    @if($isAdmin && (!$isMasterMode && $isMasterSchemeValidated) == false)
                        @if($adminTtd)
                            <div class="absolute bottom-4 left-1/2 -translate-x-1/2">
                                <button type="button" onclick="bukaModal('modalValidasiAdminMapa01')" class="px-3 py-1 bg-white/80 backdrop-blur text-indigo-600 border border-indigo-200 shadow-sm rounded text-[10px] font-bold hover:bg-indigo-50 transition-colors">Ubah Validasi</button>
                            </div>
                        @else
                            <div class="absolute inset-0 bg-transparent cursor-pointer z-10" style="top: 50px;" onclick="bukaModal('modalValidasiAdminMapa01')"></div>
                            <div class="absolute bottom-8 left-1/2 -translate-x-1/2 z-0">
                                <button type="button" onclick="bukaModal('modalValidasiAdminMapa01')" class="px-3 py-1.5 bg-emerald-50 text-emerald-700 border border-emerald-200 rounded-lg text-xs font-bold hover:bg-emerald-100 shadow-sm flex items-center gap-2 transition-colors">
                                    <i class="fa-solid fa-check"></i> Validasi
                                </button>
                            </div>
                        @endif
                    @endif
                </div>
                @endif
            </div>

            <!-- TOMBOL AKSI & NAVIGASI DI BAWAH -->
            @php
                $isAdminUserBawah = auth()->check() && in_array(auth()->user()->peran, ['admin', 'superadmin']);
                    $halamanFormulirBawah = $isAdminUserBawah
                        ? route('admin.master-muk.index', array_filter(['skema_id' => $pendaftaran->skema_id ?? request('skema_id')]))
                        : route('asesor.mapa', array_filter(['skema_id' => $pendaftaran->skema_id ?? request('skema_id')]));
                $prevUrlBawah = url()->previous();
                
                // Mencegah bug: jangan pernah kembali ke MAPA 01 atau MAPA 02
                $isInvalidPrevBawah = empty($prevUrlBawah) 
                    || $prevUrlBawah === url()->current() 
                    || str_contains($prevUrlBawah, 'mapa-02') 
                    || str_contains($prevUrlBawah, 'mapa02') 
                    || str_contains($prevUrlBawah, 'mapa-01') 
                    || str_contains($prevUrlBawah, 'mapa01');

                if ($isAsesi) {
                    $kembaliUrlBawah = route('asesi.tahapan', ['step' => 2, 'pendaftaran_id' => $pendaftaran->id]);
                } elseif (!$isInvalidPrevBawah && (str_contains($prevUrlBawah, 'daftar-peserta') || str_contains($prevUrlBawah, 'penilaian'))) {
                    $kembaliUrlBawah = $prevUrlBawah;
                } else {
                    $kembaliUrlBawah = $halamanFormulirBawah;
                }
            @endphp

            @if(!$isAsesi)
                <div class="tombol-aksi-container no-print" style="margin-top: 2rem; display: flex; justify-content: space-between; align-items: center; gap: 0.75rem; flex-wrap: wrap;">
                    <a href="{{ $kembaliUrlBawah }}" 
                       onclick="if (document.referrer && document.referrer !== window.location.href && !document.referrer.includes('mapa-02') && !document.referrer.includes('mapa02') && !document.referrer.includes('mapa-01') && !document.referrer.includes('mapa01')) { window.location.href = document.referrer; return false; } else { window.location.href = '{{ $kembaliUrlBawah }}'; return false; }"
                       class="tombol tombol-sekunder cursor-pointer">
                        &larr; Kembali
                    </a>
                    
                    <div style="display: flex; gap: 0.75rem; flex-wrap: wrap; align-items: center;">
                        @if($isAdmin && !$isAdminAuthoring && (!$isMasterSchemeValidated || $isMasterMode))
                            <button type="button" onclick="bukaModal('modalValidasiAdminMapa01')" class="tombol tombol-utama" style="background: #059669; border-color: #059669; font-weight: 700; padding: 0.65rem 1.5rem;">
                                {{ $isValidatedAdmin ? 'Perbarui Validasi MAPA.01' : 'Validasi & Sahkan FR.MAPA.01' }}
                            </button>
                        @endif

                        @if(!$isAdmin || $isAdminAuthoring || $isMasterMode || !$isMasterSchemeValidated)
                        <div id="bottom-actions-edit" style="display: {{ $isConfigured ? 'none' : 'flex' }}; gap: 0.75rem; flex-wrap: wrap;">
                            <button type="button" onclick="submitMapa01('draft')" class="tombol tombol-sekunder" style="font-weight: 700;">
                                Simpan Draft
                            </button>
                            <button type="button" onclick="submitMapa01('konfirmasi')" class="tombol tombol-utama" style="background: #2563eb; border-color: #2563eb; font-weight: 700; padding: 0.65rem 1.5rem;">
                                {{ $isAdminAuthoring ? 'Sahkan & Selesaikan FR.MAPA.01' : ($isAdmin ? 'Validasi & Sahkan FR.MAPA.01' : 'Sahkan Rencana FR.MAPA.01') }}
                            </button>
                        </div>
                        @endif

                        <div id="bottom-actions-view" style="display: {{ $isConfigured ? 'flex' : 'none' }};">
                            <span class="text-xs text-slate-500 font-medium">Dokumen FR.MAPA.01 Telah Dikonfigurasi</span>
                        </div>
                    </div>
                </div>
            @else
                <div class="tombol-aksi-container no-print" style="margin-top: 2rem; display: flex; justify-content: space-between; align-items: center; gap: 0.75rem; flex-wrap: wrap;">
                    <a href="{{ $kembaliUrlBawah }}" 
                       onclick="if (document.referrer && document.referrer !== window.location.href && !document.referrer.includes('mapa-02') && !document.referrer.includes('mapa02') && !document.referrer.includes('mapa-01') && !document.referrer.includes('mapa01')) { window.location.href = document.referrer; return false; } else { window.location.href = '{{ $kembaliUrlBawah }}'; return false; }"
                       class="tombol tombol-sekunder cursor-pointer">
                        &larr; Kembali
                    </a>
                </div>
            @endif

        </div>

    </form>
</div>

<!-- MODAL POPUP VALIDASI ADMIN LSP MAPA 01 -->
<div class="modal-overlay" id="modalValidasiAdminMapa01">
    <div class="modal-konten" style="max-width: 540px; text-align: left; border-radius: 14px; padding: 1.75rem; background: #ffffff; box-shadow: 0 20px 25px -5px rgba(0, 0, 0, 0.1), 0 8px 10px -6px rgba(0, 0, 0, 0.1);">
        <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 1.25rem; border-bottom: 1px solid #f1f5f9; padding-bottom: 0.85rem;">
            <div>
                <span style="font-size: 0.72rem; font-weight: 800; color: #059669; text-transform: uppercase; letter-spacing: 0.5px;">Validasi Validator LSP</span>
                <h3 style="color: #0f172a; margin: 0.15rem 0 0 0; font-size: 1.15rem; font-weight: 800;">
                    Pengesahan Dokumen FR.MAPA.01
                </h3>
            </div>
            <button type="button" onclick="tutupModal('modalValidasiAdminMapa01')" style="background: none; border: none; font-size: 1.5rem; cursor: pointer; color: #64748b; line-height: 1;">&times;</button>
        </div>

        <form id="form-validasi-admin-mapa01" action="{{ route('admin.mapa-01.validasi', $targetValidasiId) }}" method="POST">
            @csrf
            <input type="hidden" name="skema_id" value="{{ $pendaftaran->skema_id ?? $skemaId ?? 0 }}">
            <input type="hidden" name="pendaftaran_id" value="{{ $pendaftaran->id ?? '' }}">
            <input type="hidden" name="is_master_mode" value="{{ !empty($isMasterMode) ? '1' : '0' }}">

            @if($adaDataKurang)
                <div style="margin-bottom: 1.15rem; background: #fffbeb; border: 1.5px solid #fde68a; border-radius: 10px; padding: 0.85rem 1rem; font-size: 0.8rem; color: #92400e;">
                    <div style="font-weight: 800; display: flex; align-items: center; gap: 0.5rem; margin-bottom: 0.35rem; color: #b45309;">
                        <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><path d="M10.29 3.86L1.82 18a2 2 0 0 0 1.71 3h16.94a2 2 0 0 0 1.71-3L13.71 3.86a2 2 0 0 0-3.42 0z"></path><line x1="12" y1="9" x2="12" y2="13"></line><line x1="12" y1="17" x2="12.01" y2="17"></line></svg>
                        <span>Peringatan Kelengkapan Data Formulir ({{ $totalKurang }} Belum Lengkap)</span>
                    </div>
                    <p style="margin: 0 0 0.45rem 0; line-height: 1.45;">
                        Terdapat beberapa komponen yang belum terisi lengkap pada dokumen ini:
                    </p>
                    <ul style="margin: 0 0 0.45rem 0; padding-left: 1.25rem; line-height: 1.5;">
                        @foreach($evaluasiMapa01 as $ev)
                            @if(!$ev['lengkap'])
                                <li><strong>{{ $ev['label'] }}:</strong> {{ $ev['keterangan'] }}</li>
                            @endif
                        @endforeach
                    </ul>
                    <div style="font-size: 0.73rem; color: #78350f; font-style: italic; border-top: 1px dashed #fcd34d; padding-top: 0.35rem;">
                        * Sebagai Administrator LSP, Anda dapat mengesahkan dokumen setelah memverifikasi keabsahan fisik/berkas terkait.
                    </div>
                </div>
            @else
                <div style="margin-bottom: 1.15rem; background: #f0fdf4; border: 1.5px solid #86efac; border-radius: 10px; padding: 0.75rem 1rem; font-size: 0.8rem; color: #166534; display: flex; align-items: center; gap: 0.5rem;">
                    <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="#16a34a" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><path d="M22 11.08V12a10 10 0 1 1-5.93-9.14"></path><polyline points="22 4 12 14.01 9 11.01"></polyline></svg>
                    <span><strong>Data Lengkap:</strong> Seluruh butir data perencanaan asesmen FR.MAPA.01 telah memenuhi standar kelayakan BNSP.</span>
                </div>
            @endif

            <div style="margin-bottom: 1rem;">
                <label style="display: block; font-size: 0.78rem; font-weight: 700; color: #334155; margin-bottom: 0.35rem;">
                    Nama Validator (Admin LSP) <span style="color: #dc2626;">*</span>
                </label>
                <input type="text" name="validator_nama" value="{{ $adminValidatorNama }}" required
                       style="width: 100%; padding: 0.55rem 0.75rem; font-size: 0.85rem; border: 1.2px solid #cbd5e1; border-radius: 8px; outline: none; box-sizing: border-box; font-weight: 600;"
                       placeholder="Nama lengkap validator...">
            </div>

            <div style="margin-bottom: 1rem;">
                <label style="display: block; font-size: 0.78rem; font-weight: 700; color: #334155; margin-bottom: 0.35rem;">
                    Nomor Registrasi / NIP Validator <span style="color: #dc2626;">*</span>
                </label>
                <input type="text" name="validator_nomor_met" value="{{ $adminValidatorMet }}" required
                       style="width: 100%; padding: 0.55rem 0.75rem; font-size: 0.85rem; border: 1.2px solid #cbd5e1; border-radius: 8px; outline: none; box-sizing: border-box; font-weight: 600;"
                       placeholder="NIP atau Nomor Registrasi...">
            </div>

            <div style="margin-bottom: 1rem;">
                <label style="display: block; font-size: 0.78rem; font-weight: 700; color: #334155; margin-bottom: 0.35rem;">
                    Catatan Validasi <span style="color: #64748b; font-weight: 400;">(Opsional)</span>
                </label>
                <textarea name="catatan_validasi" rows="2"
                          style="width: 100%; padding: 0.55rem 0.75rem; font-size: 0.82rem; border: 1.2px solid #cbd5e1; border-radius: 8px; outline: none; box-sizing: border-box; resize: vertical;"
                          placeholder="Catatan hasil verifikasi kelayakan rencana asesmen...">{{ $catatanValidasi }}</textarea>
            </div>

            <div style="margin-bottom: 1rem; border: 1px solid #cbd5e1; border-radius: 10px; overflow: hidden;">
                <div style="padding: 0.7rem 0.9rem; background: #f8fafc; display: flex; justify-content: space-between; align-items: center; gap: .75rem;">
                    <div>
                        <div style="font-size: .78rem; font-weight: 800; color: #334155;">Tanda tangan Validator</div>
                        <div style="font-size: .72rem; color: #64748b; margin-top: .1rem;">Gambar ulang untuk mengganti TTD profil.</div>
                    </div>
                    <button type="button" id="btn-clear-canvas-validator" class="tombol tombol-sekunder tombol-sm">Bersihkan</button>
                </div>
                <div style="padding: .75rem; background: #fff;">
                    <canvas id="canvas-ttd-validator" width="480" height="150" class="canvas-signature-pad" style="width: 100%; height: 150px; border: 1.5px dashed #94a3b8; border-radius: 8px; background: #fff; cursor: crosshair; touch-action: none; display: block;"></canvas>
                    <input type="hidden" name="tanda_tangan_admin" id="input-ttd-validator-modal-base64">
                    <p id="validator-signature-time" style="font-size: .72rem; color: #64748b; margin: .45rem 0 0;">Belum ditandatangani.</p>
                    <p id="validator-signature-hint" style="font-size: .72rem; color: #64748b; margin: .2rem 0 0;">Gambar tanda tangan admin untuk menyelesaikan validasi.</p>
                </div>
            </div>

            <label style="display: flex; align-items: flex-start; gap: .6rem; margin-bottom: 1rem; padding: .75rem; border-radius: 9px; background: #f0fdf4; color: #166534; cursor: pointer; font-size: .78rem; line-height: 1.45;">
                <input type="checkbox" id="check-review-mapa01" name="konfirmasi_pemeriksaan" value="1" required style="margin-top: .18rem; accent-color: #059669;">
                <span>Saya telah memeriksa isi formulir dan menyatakan data ini layak untuk disahkan.</span>
            </label>


            <div style="display: flex; gap: 0.75rem; justify-content: flex-end; border-top: 1px solid #f1f5f9; padding-top: 1rem;">
                <button type="button" onclick="tutupModal('modalValidasiAdminMapa01')" class="tombol tombol-sekunder tombol-sm">
                    Batal
                </button>
                <button type="submit" class="tombol tombol-utama tombol-sm" style="background: #059669; border-color: #059669; font-weight: 700;">
                    Sahkan & Validasi FR.MAPA.01
                </button>
            </div>
        </form>
    </div>
</div>
@endsection

@push('js')
    <script src="https://cdn.jsdelivr.net/npm/signature_pad@4.1.7/dist/signature_pad.umd.min.js"></script>
    <script src="{{ asset('js/asesi/pendaftaran-bagian32.js') }}"></script>
    <script>
        if (!window.SignaturePad) {
            window.SignaturePad = class NativeSignaturePad {
                constructor(canvas, options = {}) {
                    this.canvas = canvas;
                    this.context = canvas.getContext('2d');
                    this.penColor = options.penColor || '#111827';
                    this.backgroundColor = options.backgroundColor || 'rgba(0, 0, 0, 0)';
                    this.empty = true;
                    this.drawing = false;
                    this.lastPoint = null;
                    this.listeners = new Map();
                    this.canvas.style.touchAction = 'none';
                    this.context.setTransform(1, 0, 0, 1, 0, 0);
                    this.canvas.addEventListener('pointerdown', event => this.startStroke(event));
                    this.canvas.addEventListener('pointermove', event => this.moveStroke(event));
                    this.canvas.addEventListener('pointerup', event => this.endStroke(event));
                    this.canvas.addEventListener('pointercancel', event => this.endStroke(event));
                    this.canvas.addEventListener('lostpointercapture', event => this.endStroke(event));
                    this.clear();
                }

                addEventListener(name, callback) {
                    const callbacks = this.listeners.get(name) || [];
                    callbacks.push(callback);
                    this.listeners.set(name, callbacks);
                }

                pointFromEvent(event) {
                    const rect = this.canvas.getBoundingClientRect();
                    return {
                        x: (event.clientX - rect.left) * (this.canvas.width / (rect.width || 1)),
                        y: (event.clientY - rect.top) * (this.canvas.height / (rect.height || 1)),
                    };
                }

                startStroke(event) {
                    event.preventDefault();
                    this.canvas.setPointerCapture?.(event.pointerId);
                    this.drawing = true;
                    this.empty = false;
                    this.lastPoint = this.pointFromEvent(event);
                    const scale = this.canvas.width / (this.canvas.getBoundingClientRect().width || 1);
                    this.context.fillStyle = this.penColor;
                    this.context.beginPath();
                    this.context.arc(this.lastPoint.x, this.lastPoint.y, 1.4 * scale, 0, Math.PI * 2);
                    this.context.fill();
                }

                moveStroke(event) {
                    if (!this.drawing) return;
                    event.preventDefault();
                    const point = this.pointFromEvent(event);
                    const scale = this.canvas.width / (this.canvas.getBoundingClientRect().width || 1);
                    this.context.strokeStyle = this.penColor;
                    this.context.lineWidth = 2.8 * scale;
                    this.context.lineCap = 'round';
                    this.context.lineJoin = 'round';
                    this.context.beginPath();
                    this.context.moveTo(this.lastPoint.x, this.lastPoint.y);
                    this.context.lineTo(point.x, point.y);
                    this.context.stroke();
                    this.lastPoint = point;
                }

                endStroke(event) {
                    if (!this.drawing) return;
                    event.preventDefault?.();
                    this.drawing = false;
                    this.lastPoint = null;
                    (this.listeners.get('endStroke') || []).forEach(callback => callback({ type: 'endStroke', target: this.canvas }));
                }

                clear() {
                    this.context.clearRect(0, 0, this.canvas.width, this.canvas.height);
                    if (this.backgroundColor !== 'rgba(0, 0, 0, 0)') {
                        this.context.fillStyle = this.backgroundColor;
                        this.context.fillRect(0, 0, this.canvas.width, this.canvas.height);
                    }
                    this.empty = true;
                    this.drawing = false;
                }

                isEmpty() {
                    return this.empty;
                }

                toDataURL(type = 'image/png') {
                    return this.canvas.toDataURL(type);
                }
            };
        }

        let isMapa01EditMode = {{ (!$isAsesi && $isConfigured) ? 'false' : 'true' }};
        const isAdminAuthoringMapa01 = @json($isAdminAuthoring);

        function applyMapa01Mode() {
            const container = document.getElementById('container-mapa01');
            const form = document.getElementById('form-mapa01');
            const btnToggle = document.getElementById('btn-toggle-edit-mapa01');
            const textToggle = document.getElementById('text-toggle-mapa01');
            const btnSimpan = document.getElementById('btn-simpan-mapa01');
            const bannerLocked = document.getElementById('banner-mapa01-locked');
            const bannerEditing = document.getElementById('banner-mapa01-editing');
            const bottomEdit = document.getElementById('bottom-actions-edit');
            const bottomView = document.getElementById('bottom-actions-view');

            if (!isMapa01EditMode) {
                // Locked mode
                if (container) container.classList.add('mode-view-locked');
                if (textToggle) textToggle.textContent = 'Edit Formulir';
                if (btnToggle) {
                    btnToggle.style.background = '#4f46e5';
                    btnToggle.style.borderColor = '#4338ca';
                }
                if (btnSimpan) btnSimpan.style.display = 'none';
                if (bannerLocked) bannerLocked.style.display = 'flex';
                if (bannerEditing) bannerEditing.style.display = 'none';
                if (bottomEdit) bottomEdit.style.display = 'none';
                if (bottomView) bottomView.style.display = 'flex';

                if (form) {
                    form.querySelectorAll('input, select, textarea').forEach(el => {
                        if (el.type === 'checkbox' || el.type === 'radio') {
                            el.disabled = true;
                        } else if (el.type !== 'hidden') {
                            el.readOnly = true;
                        }
                    });
                }
            } else {
                // Edit mode
                if (container) container.classList.remove('mode-view-locked');
                if (textToggle) textToggle.textContent = 'Kunci Formulir';
                if (btnToggle) {
                    btnToggle.style.background = '#d97706';
                    btnToggle.style.borderColor = '#b45309';
                }
                if (btnSimpan) btnSimpan.style.display = 'inline-flex';
                if (bannerLocked) bannerLocked.style.display = 'none';
                if (bannerEditing) bannerEditing.style.display = 'flex';
                if (bottomEdit) bottomEdit.style.display = 'flex';
                if (bottomView) bottomView.style.display = 'none';

                if (form) {
                    form.querySelectorAll('input, select, textarea').forEach(el => {
                        el.disabled = false;
                        el.readOnly = false;
                    });
                }
                applyKonfirmasiTabelState();
                updateModificationInputsState();
            }
        }

        function toggleEditMapa01() {
            isMapa01EditMode = !isMapa01EditMode;
            applyMapa01Mode();
        }

        function applyKonfirmasiTabelState() {
            if (!isMapa01EditMode) return;
            document.querySelectorAll('.toggle-relevan-tabel').forEach(cb => {
                const key = cb.getAttribute('data-role-key');
                if (key) {
                    syncKonfirmasiToTabel(key, cb.checked);
                }
            });
        }

        function syncKonfirmasiToTabel(roleKey, isChecked) {
            const row = document.getElementById('row-konfirmasi-' + roleKey);
            const checkTabel = document.getElementById('check-tabel-' + roleKey);
            const titleRole = document.getElementById('title-role-' + roleKey);
            const badgeWrapper = document.getElementById('badge-wrapper-' + roleKey);
            const inputNama = document.getElementById('input-nama-' + roleKey);
            const inputTgl = document.getElementById('input-tgl-' + roleKey);
            const btnToday = document.getElementById('btn-today-' + roleKey);
            const helperLsp = document.getElementById('helper-manajer-' + roleKey);

            if (checkTabel && checkTabel.checked !== isChecked) {
                checkTabel.checked = isChecked;
            }

            if (isChecked) {
                if (row) {
                    row.classList.remove('baris-relevan-dicoret', 'hover:bg-slate-100/60');
                    row.classList.add('baris-relevan-aktif', 'hover:bg-slate-50/70');
                }
                if (titleRole) {
                    titleRole.classList.remove('text-slate-400', 'line-through');
                    titleRole.classList.add('text-slate-800');
                }
                if (badgeWrapper) {
                    badgeWrapper.innerHTML = `
                        <span class="inline-flex items-center gap-1 text-[10px] font-bold px-2 py-0.5 rounded-full bg-blue-50 text-blue-800 border border-blue-200" id="badge-role-${roleKey}">
                            <span class="w-1.5 h-1.5 rounded-full bg-blue-700"></span> Relevan &bull; Dikonfirmasi
                        </span>
                    `;
                }
                if (inputNama) {
                    inputNama.disabled = false;
                    inputNama.classList.remove('bg-slate-100', 'text-slate-400', 'cursor-not-allowed');
                    inputNama.placeholder = 'Nama pejabat/petugas...';
                }
                if (inputTgl) {
                    inputTgl.disabled = false;
                    inputTgl.classList.remove('bg-slate-100', 'text-slate-400', 'cursor-not-allowed');
                }
                if (btnToday) btnToday.style.display = 'inline-block';
                if (helperLsp) helperLsp.style.display = 'flex';
            } else {
                if (row) {
                    row.classList.remove('baris-relevan-aktif', 'hover:bg-slate-50/70');
                    row.classList.add('baris-relevan-dicoret', 'hover:bg-slate-100/60');
                }
                if (titleRole) {
                    titleRole.classList.remove('text-slate-800');
                    titleRole.classList.add('text-slate-400', 'line-through');
                }
                if (badgeWrapper) {
                    badgeWrapper.innerHTML = `
                        <span class="inline-flex items-center gap-1 text-[10px] font-semibold px-2 py-0.5 rounded-full bg-slate-100 text-slate-500 border border-slate-200" id="badge-role-${roleKey}">
                            <span class="w-1.5 h-1.5 rounded-full bg-slate-400"></span> Dicoret (*tidak perlu)
                        </span>
                    `;
                }
                if (inputNama) {
                    inputNama.disabled = true;
                    inputNama.classList.add('bg-slate-100', 'text-slate-400', 'cursor-not-allowed');
                    inputNama.placeholder = '— Dicoret (tidak perlu diisi) —';
                }
                if (inputTgl) {
                    inputTgl.disabled = true;
                    inputTgl.classList.add('bg-slate-100', 'text-slate-400', 'cursor-not-allowed');
                }
                if (btnToday) btnToday.style.display = 'none';
                if (helperLsp) helperLsp.style.display = 'none';
            }

            updateRowStatusBadge(roleKey);
        }

        function syncTabelToKonfirmasi(roleKey, isChecked) {
            const input11 = document.querySelector(`.input-konfirmasi-1-1[data-role-key="${roleKey}"]`);
            if (input11) input11.checked = isChecked;
            syncKonfirmasiToTabel(roleKey, isChecked);
        }

        function updateRowStatusBadge(roleKey) {
            const checkTabel = document.getElementById('check-tabel-' + roleKey);
            const statusContainer = document.getElementById('status-container-' + roleKey);
            const inputNama = document.getElementById('input-nama-' + roleKey);
            const inputTgl = document.getElementById('input-tgl-' + roleKey);

            if (!statusContainer) return;

            const isRelevan = checkTabel && checkTabel.checked;
            if (!isRelevan) {
                statusContainer.innerHTML = `
                    <span class="inline-flex items-center gap-1 text-[10px] font-semibold px-2 py-0.5 rounded-md bg-slate-100 text-slate-500 border border-slate-200">
                        <span class="w-1.5 h-1.5 rounded-full bg-slate-400"></span> Dicoret
                    </span>
                `;
                return;
            }

            const namaVal = (inputNama ? inputNama.value : '').trim();
            const tglVal = (inputTgl ? inputTgl.value : '').trim();

            if (namaVal && tglVal) {
                statusContainer.innerHTML = `
                    <span class="inline-flex items-center gap-1 text-[10px] font-bold px-2 py-0.5 rounded-md bg-emerald-50 text-emerald-700 border border-emerald-200">
                        <span class="w-1.5 h-1.5 rounded-full bg-emerald-500"></span> &check; Terkonfirmasi
                    </span>
                `;
            } else {
                statusContainer.innerHTML = `
                    <span class="inline-flex items-center gap-1 text-[10px] font-bold px-2 py-0.5 rounded-md bg-amber-50 text-amber-700 border border-amber-200">
                        <span class="w-1.5 h-1.5 rounded-full bg-amber-500"></span> &#9203; Belum Dikonfirmasi
                    </span>
                `;
            }
        }

        function isiTanggalHariIni(roleKey) {
            const inputTgl = document.getElementById('input-tgl-' + roleKey);
            if (!inputTgl) return;
            const now = new Date();
            const d = String(now.getDate()).padStart(2, '0');
            const m = String(now.getMonth() + 1).padStart(2, '0');
            const y = now.getFullYear();
            inputTgl.value = `${d}/${m}/${y}`;
            updateRowStatusBadge(roleKey);
        }

        function isiOtomatisManajerLsp(roleKey, defaultNama) {
            const inputNama = document.getElementById('input-nama-' + roleKey);
            if (inputNama) {
                inputNama.value = defaultNama;
            }
            isiTanggalHariIni(roleKey);
            updateRowStatusBadge(roleKey);
        }

        function submitMapa01(aksi) {
            const form = document.getElementById('form-mapa01');
            if (!form) return;

            const requiredSignatureInput = document.getElementById(isAdminAuthoringMapa01 ? 'input-ttd-admin-author-base64' : 'input-ttd-asesor-base64');
            if (aksi === 'konfirmasi' && !requiredSignatureInput?.value) {
                if (typeof Swal !== 'undefined') {
                    Swal.fire({ icon: 'warning', title: 'Tanda Tangan Wajib', text: isAdminAuthoringMapa01 ? 'Bubuhkan tanda tangan Admin untuk menyelesaikan FR.MAPA.01.' : 'Bubuhkan tanda tangan Asesor sebelum mengirim FR.MAPA.01 untuk validasi.', confirmButtonColor: '#059669' });
                } else {
                    alert(isAdminAuthoringMapa01 ? 'Bubuhkan tanda tangan Admin untuk menyelesaikan FR.MAPA.01.' : 'Bubuhkan tanda tangan Asesor sebelum mengirim FR.MAPA.01 untuk validasi.');
                }
                document.getElementById(isAdminAuthoringMapa01 ? 'canvas-ttd-admin-author' : 'canvas-ttd-asesor')?.scrollIntoView({ behavior: 'smooth', block: 'center' });
                return;
            }
            const inputAksi = document.getElementById('inputAksiMapa01');
            if (inputAksi) inputAksi.value = aksi;
            
            const conditionalTextFields = form.querySelectorAll('input[data-status-field]');
            for (const textInput of conditionalTextFields) {
                const status = form.querySelector(`input[name="${textInput.dataset.statusField}"]:checked`);
                if (status?.value === 'ada' && !textInput.value.trim()) {
                    textInput.classList.add('input-error');
                    const label = textInput.dataset.validationLabel || 'Bagian Modifikasi';
                    const message = `Kolom "Jika Ada, tuliskan:" pada ${label} wajib diisi karena memilih Ada.`;
                    if (typeof Swal !== 'undefined') {
                        Swal.fire({ 
                            icon: 'warning', 
                            title: 'Formulir Belum Lengkap', 
                            text: message, 
                            confirmButtonColor: '#059669',
                            confirmButtonText: 'Lengkapi Sekarang'
                        })
                        .then(() => {
                            textInput.scrollIntoView({ behavior: 'smooth', block: 'center' });
                            textInput.focus();
                        });
                    } else {
                        alert(message);
                        textInput.focus();
                    }
                    return;
                } else {
                    textInput.classList.remove('input-error');
                }
            }

            // Validasi Kelengkapan (Kecuali Simpan Draft)
            if (aksi !== 'simpan_draft') {
                const rules = [
                    { selector: 'input[name="pendekatan_asesi[]"]:checked', msg: 'Pendekatan Asesi belum dipilih (Bagian 1.1)', elem: 'input[name="pendekatan_asesi[]"]' },
                    { selector: 'input[name="tujuan_asesmen"]:checked', msg: 'Tujuan Asesmen belum dipilih (Bagian 1.1)', elem: 'input[name="tujuan_asesmen"]' },
                    { selector: 'input[name="konteks_lingkungan"]:checked', msg: 'Konteks Lingkungan belum dipilih (Bagian 1.1)', elem: 'input[name="konteks_lingkungan"]' },
                    { selector: 'input[name="konteks_peluang_bukti"]:checked', msg: 'Peluang Bukti belum dipilih (Bagian 1.1)', elem: 'input[name="konteks_peluang_bukti"]' },
                    { selector: 'input[name="hubungan_standar_bukti"]:checked', msg: 'Hubungan standar dengan bukti belum dipilih (Bagian 1.1)', elem: 'input[name="hubungan_standar_bukti"]' },
                    { selector: 'input[name="hubungan_standar_aktivitas"]:checked', msg: 'Hubungan standar dengan aktivitas kerja belum dipilih (Bagian 1.1)', elem: 'input[name="hubungan_standar_aktivitas"]' },
                    { selector: 'input[name="hubungan_standar_pembelajaran"]:checked', msg: 'Hubungan standar dengan kegiatan pembelajaran belum dipilih (Bagian 1.1)', elem: 'input[name="hubungan_standar_pembelajaran"]' },
                    { selector: 'input[name="pelaksana_asesmen[]"]:checked', msg: 'Pelaksana Asesmen belum dipilih (Bagian 1.1)', elem: 'input[name="pelaksana_asesmen[]"]' },
                    { selector: 'input[name="standar_industri[]"]:checked', msg: 'Standar Industri belum dipilih (Bagian 1.1)', elem: 'input[name="standar_industri[]"]' },
                    { selector: 'input[name="karakteristik_kandidat_status"]:checked', msg: 'Karakteristik Kandidat belum dipilih (Bagian 3)', elem: 'input[name="karakteristik_kandidat_status"]' },
                    { selector: 'input[name="kebutuhan_kontekstualisasi_status"]:checked', msg: 'Kebutuhan Kontekstualisasi belum dipilih (Bagian 3)', elem: 'input[name="kebutuhan_kontekstualisasi_status"]' },
                    { selector: 'input[name="saran_pelatihan_status"]:checked', msg: 'Saran Pelatihan belum dipilih (Bagian 3)', elem: 'input[name="saran_pelatihan_status"]' },
                    { selector: 'input[name="penyesuaian_perangkat_status"]:checked', msg: 'Penyesuaian Perangkat belum dipilih (Bagian 3)', elem: 'input[name="penyesuaian_perangkat_status"]' },
                    { selector: 'input[name="peluang_terintegrasi_status"]:checked', msg: 'Peluang Kegiatan Terintegrasi belum dipilih (Bagian 3)', elem: 'input[name="peluang_terintegrasi_status"]' },
                ];

                for (let r of rules) {
                    if (!form.querySelector(r.selector)) {
                        if (typeof Swal !== 'undefined') {
                            Swal.fire({ icon: 'warning', title: 'Formulir Belum Lengkap', text: r.msg, confirmButtonColor: '#059669' })
                            .then(() => {
                                const el = form.querySelector(r.elem);
                                if (el) el.scrollIntoView({ behavior: 'smooth', block: 'center' });
                            });
                        } else {
                            alert('Belum Lengkap: ' + r.msg);
                        }
                        return;
                    }
                }

                // Validasi Matriks Unit
                let unitValid = true;
                let errorUnitMsg = '';
                let errorElem = null;
                const trUnits = form.querySelectorAll('tr[id^="baris-unit-"]');
                for (let i = 0; i < trUnits.length; i++) {
                    const tr = trUnits[i];
                    // Some rows might not have checkboxes if they are element rows, but id starts with baris-unit- usually means unit
                    const chkBukti = tr.querySelectorAll('input[type="checkbox"][name*="[bukti]"]');
                    const chkMetode = tr.querySelectorAll('input[type="checkbox"][name*="[methods]"]');
                    
                    if (chkBukti.length > 0 && chkMetode.length > 0) {
                        const hasBukti = Array.from(chkBukti).some(cb => cb.checked);
                        const hasMetode = Array.from(chkMetode).some(cb => cb.checked);
                        
                        if (!hasBukti || !hasMetode) {
                            unitValid = false;
                            errorUnitMsg = 'Rencana Matriks (Bagian 2) belum lengkap. Setiap unit harus dipilih minimal 1 Bukti dan 1 Metode Asesmen.';
                            errorElem = tr;
                            break;
                        }
                    }
                }

                if (!unitValid) {
                    if (typeof Swal !== 'undefined') {
                        Swal.fire({ icon: 'warning', title: 'Matriks Belum Lengkap', text: errorUnitMsg, confirmButtonColor: '#059669' })
                        .then(() => {
                            if (errorElem) errorElem.scrollIntoView({ behavior: 'smooth', block: 'center' });
                        });
                    } else {
                        alert(errorUnitMsg);
                    }
                    return;
                }
            }

            // Pastikan input nama dan tanggal pada tabel konfirmasi di-enable agar terkirim ke backend
            form.querySelectorAll('.input-nama-relevan, .input-tgl-relevan').forEach(el => {
                el.disabled = false;
            });

            // Pastikan field modifikasi yang 'tidak_ada' dikosongkan nilainya dan di-enable sementara agar tidak terhalang submit
            form.querySelectorAll('input[data-status-field]').forEach(el => {
                const status = form.querySelector(`input[name="${el.dataset.statusField}"]:checked`);
                if (status?.value !== 'ada') {
                    el.value = '';
                }
                el.disabled = false;
            });

            form.submit();
        }

        function toggleRincianValidasiAdmin() {
            const wadah = document.getElementById('wadah-rincian-validasi-admin');
            const text = document.getElementById('text-toggle-rincian-validasi');
            const icon = document.getElementById('icon-toggle-rincian');
            if (!wadah) return;

            if (wadah.style.display === 'none' || !wadah.style.display) {
                wadah.style.display = 'block';
                if (text) text.textContent = 'Tutup Cek Kelengkapan';
                if (icon) icon.style.transform = 'rotate(180deg)';
            } else {
                wadah.style.display = 'none';
                if (text) text.textContent = 'Lihat Cek Kelengkapan';
                if (icon) icon.style.transform = 'rotate(0deg)';
            }
        }

        function formatWibDateTime(date = new Date()) {
            return new Intl.DateTimeFormat('id-ID', {
                timeZone: 'Asia/Jakarta',
                day: '2-digit',
                month: '2-digit',
                year: 'numeric',
                hour: '2-digit',
                minute: '2-digit',
                second: '2-digit',
                hourCycle: 'h23'
            }).format(date).replace(',', '') + ' WIB';
        }

        function initValidatorCanvas() {
            const canvas = document.getElementById('canvas-ttd-validator');
            if (!canvas) return;

            let validatorPad;
            if (window.SignaturePad) {
                const ratio = Math.max(window.devicePixelRatio || 1, 1);
                canvas.width = (canvas.offsetWidth || 480) * ratio;
                canvas.height = (canvas.offsetHeight || 150) * ratio;
                canvas.getContext("2d").scale(ratio, ratio);
                validatorPad = new window.SignaturePad(canvas, { backgroundColor: 'rgb(255, 255, 255)' });
                validatorPad.clear();
                validatorPad.addEventListener('endStroke', () => {
                    const time = document.getElementById('validator-signature-time');
                    if (time) time.textContent = `Ditandatangani: ${formatWibDateTime()}`;
                });
            }

            document.getElementById('btn-clear-canvas-validator')?.addEventListener('click', () => {
                if (validatorPad) validatorPad.clear();
                const input = document.getElementById('input-ttd-validator-modal-base64');
                if (input) input.value = '';
                const time = document.getElementById('validator-signature-time');
                if (time) time.textContent = 'Belum ditandatangani.';
            });

            document.getElementById('form-validasi-admin-mapa01')?.addEventListener('submit', (event) => {
                const reviewed = document.getElementById('check-review-mapa01');
                if (!reviewed?.checked) {
                    event.preventDefault();
                    reviewed?.focus();
                    return;
                }
                if (!validatorPad || validatorPad.isEmpty()) {
                    event.preventDefault();
                    alert('Tanda tangan Admin wajib dibuat untuk menyelesaikan validasi.');
                    return;
                }
                const input = document.getElementById('input-ttd-validator-modal-base64');
                if (input) input.value = validatorPad.toDataURL('image/png');
            });
        }

        window.adminAuthorSignaturePad = null;

        function initAdminAuthorSignatureCanvas() {
            const canvas = document.getElementById('canvas-ttd-admin-author');
            if (!canvas || !window.SignaturePad) return;

            const ratio = Math.max(window.devicePixelRatio || 1, 1);
            canvas.width = (canvas.offsetWidth || 900) * ratio;
            canvas.height = (canvas.offsetHeight || 190) * ratio;
            canvas.getContext('2d').scale(ratio, ratio);
            window.adminAuthorSignaturePad = new window.SignaturePad(canvas, { backgroundColor: 'rgb(255, 255, 255)' });
            window.adminAuthorSignaturePad.addEventListener('endStroke', window.saveAdminAuthorSignature);
        }

        window.clearAdminAuthorSignature = function() {
            window.adminAuthorSignaturePad?.clear();
            const input = document.getElementById('input-ttd-admin-author-base64');
            if (input) input.value = '';
            const preview = document.getElementById('admin-author-signature-preview');
            if (preview) preview.innerHTML = '';
            const signedAt = document.getElementById('admin-author-signature-time');
            if (signedAt) signedAt.textContent = 'Belum ditandatangani.';
        };

        window.saveAdminAuthorSignature = function() {
            const pad = window.adminAuthorSignaturePad;
            if (!pad || pad.isEmpty()) {
                alert('Silakan gambar tanda tangan Admin terlebih dahulu.');
                return;
            }

            const signature = pad.toDataURL('image/png');
            const input = document.getElementById('input-ttd-admin-author-base64');
            if (input) input.value = signature;
            const signedAt = formatWibDateTime();
            const preview = document.getElementById('admin-author-signature-preview');
            if (preview) preview.innerHTML = `<img src="${signature}" alt="Pratinjau tanda tangan Admin" style="display:block;max-width:100%;max-height:90px;margin:0 auto;object-fit:contain">`;
            const time = document.getElementById('admin-author-signature-time');
            if (time) time.textContent = `Ditandatangani: ${signedAt}`;
        };

        window.signaturePadModal = null;

        window.bukaModalTtd = function() {
            const canvas = document.getElementById('canvas-ttd-asesor');
            if (canvas && window.SignaturePad && !window.signaturePadModal) {
                const ratio = Math.max(window.devicePixelRatio || 1, 1);
                canvas.width = (canvas.offsetWidth || 900) * ratio;
                canvas.height = (canvas.offsetHeight || 190) * ratio;
                canvas.getContext('2d').scale(ratio, ratio);
                window.signaturePadModal = new window.SignaturePad(canvas, { backgroundColor: 'rgb(255, 255, 255)' });
                window.signaturePadModal.clear();
                window.signaturePadModal.addEventListener('endStroke', window.saveTtdAsesor);
            }
        };

        window.clearTtdAsesor = function() {
            window.signaturePadModal?.clear();
            ['input-ttd-asesor-base64', 'input-penyusun-ttd', 'input-penyusun-ttd-tgl'].forEach((id) => {
                const input = document.getElementById(id);
                if (input) input.value = '';
            });
            const preview = document.getElementById('container-ttd-asesor-preview');
            if (preview) preview.innerHTML = '<div style="padding: .5rem; text-align: center; font-size: .75rem; color: #64748b;">Belum ada tanda tangan yang disimpan.</div>';
        };

        window.saveTtdAsesor = function() {
            if (window.signaturePadModal && !window.signaturePadModal.isEmpty()) {
                const dataUrl = window.signaturePadModal.toDataURL('image/png');
                
                let hiddenInput = document.getElementById('input-ttd-asesor-base64');
                if (!hiddenInput) {
                    hiddenInput = document.createElement('input');
                    hiddenInput.type = 'hidden';
                    hiddenInput.name = 'tanda_tangan_asesor';
                    hiddenInput.id = 'input-ttd-asesor-base64';
                    const f = document.getElementById('form-mapa01') || document.body;
                    f.appendChild(hiddenInput);
                }
                hiddenInput.value = dataUrl;

                let hiddenPenyusun = document.getElementById('input-penyusun-ttd');
                if (!hiddenPenyusun) {
                    hiddenPenyusun = document.createElement('input');
                    hiddenPenyusun.type = 'hidden';
                    hiddenPenyusun.name = 'penyusun_validator_tabel[penyusun_1][ttd]';
                    hiddenPenyusun.id = 'input-penyusun-ttd';
                    const f = document.getElementById('form-mapa01') || document.body;
                    f.appendChild(hiddenPenyusun);
                }
                hiddenPenyusun.value = dataUrl;

                const signedAt = formatWibDateTime();

                let hiddenTgl = document.getElementById('input-penyusun-ttd-tgl');
                if (!hiddenTgl) {
                    hiddenTgl = document.createElement('input');
                    hiddenTgl.type = 'hidden';
                    hiddenTgl.name = 'penyusun_validator_tabel[penyusun_1][ttd_tanggal]';
                    hiddenTgl.id = 'input-penyusun-ttd-tgl';
                    const f = document.getElementById('form-mapa01') || document.body;
                    f.appendChild(hiddenTgl);
                }
                hiddenTgl.value = signedAt;

                const container = document.getElementById('container-ttd-asesor-preview');
                if (container) container.innerHTML = `<img src="${dataUrl}" alt="Pratinjau tanda tangan asesor" style="display:block;max-width:100%;max-height:90px;margin:0 auto;object-fit:contain"><div style="text-align:center;font-size:.7rem;color:#64748b;margin-top:.25rem">Ditandatangani: ${signedAt}</div>`;

                const badgePenyusun = document.getElementById('badge-status-penyusun-ttd');
                if (badgePenyusun) {
                    badgePenyusun.innerHTML = `
                        <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-full text-[10px] font-bold bg-emerald-50 text-emerald-700 border border-emerald-200">
                            <span class="w-1.5 h-1.5 rounded-full bg-emerald-500"></span>
                            Siap Disimpan
                        </span>
                    `;
                }

            } else {
                if (typeof Swal !== 'undefined') {
                    Swal.fire({
                        icon: 'warning',
                        title: 'Canvas Kosong',
                        text: 'Silakan gambar tanda tangan Anda terlebih dahulu.',
                        confirmButtonText: 'Mengerti',
                        confirmButtonColor: '#059669'
                    });
                } else {
                    alert('Silakan gambar tanda tangan Anda terlebih dahulu.');
                }
            }
        };

        function updateModificationInputsState(options = {}) {
            const form = document.getElementById('form-mapa01');
            if (!form) return;

            form.querySelectorAll('input[data-status-field]').forEach(textInput => {
                const statusField = textInput.dataset.statusField;
                const checkedRadio = form.querySelector(`input[name="${statusField}"]:checked`);
                const statusVal = checkedRadio ? checkedRadio.value : 'tidak_ada';
                const isAda = (statusVal === 'ada');

                const td = textInput.closest('td');
                const badgeWajib = td ? td.querySelector('.badge-wajib-ada') : null;

                if (isAda) {
                    if (typeof isMapa01EditMode === 'undefined' || isMapa01EditMode) {
                        textInput.disabled = false;
                        textInput.readOnly = false;
                    }
                    textInput.classList.remove('is-disabled');
                    textInput.required = true;
                    textInput.setAttribute('aria-required', 'true');
                    if (textInput.dataset.placeholderDefault) {
                        textInput.placeholder = textInput.dataset.placeholderDefault;
                    }
                    if (badgeWajib) {
                        badgeWajib.style.display = 'inline';
                    }
                    if (options.targetField === statusField && options.focusTarget) {
                        textInput.focus();
                    }
                } else {
                    textInput.disabled = true;
                    textInput.classList.add('is-disabled');
                    textInput.classList.remove('input-error');
                    textInput.required = false;
                    textInput.removeAttribute('aria-required');
                    textInput.value = '';
                    if (textInput.dataset.placeholderDisabled) {
                        textInput.placeholder = textInput.dataset.placeholderDisabled;
                    }
                    if (badgeWajib) {
                        badgeWajib.style.display = 'none';
                    }
                }
            });
        }

        document.addEventListener('DOMContentLoaded', function() {
            applyMapa01Mode();
            updateModificationInputsState();

            document.querySelectorAll('#form-mapa01 input[type="radio"]').forEach(radio => {
                if (radio.name.endsWith('_status')) {
                    radio.addEventListener('change', function() {
                        updateModificationInputsState({
                            targetField: this.name,
                            focusTarget: (this.value === 'ada')
                        });
                    });
                }
            });

            document.querySelectorAll('#form-mapa01 input[data-status-field]').forEach(textInput => {
                textInput.addEventListener('input', function() {
                    if (this.value.trim()) {
                        this.classList.remove('input-error');
                    }
                });
            });

            window.bukaModalTtd();
            initAdminAuthorSignatureCanvas();
            initValidatorCanvas();
            document.getElementById('btn-clear-canvas-asesor')?.addEventListener('click', window.clearTtdAsesor);
            document.getElementById('btn-clear-canvas-admin-author')?.addEventListener('click', window.clearAdminAuthorSignature);
            initMatriksAsesmenLogic();

            const formM = document.getElementById('form-mapa01');
            if (formM) {
                formM.addEventListener('submit', function(e) {
                    const conditionalTextFields = formM.querySelectorAll('input[data-status-field]');
                    for (const textInput of conditionalTextFields) {
                        const status = formM.querySelector(`input[name="${textInput.dataset.statusField}"]:checked`);
                        if (status?.value === 'ada' && !textInput.value.trim()) {
                            e.preventDefault();
                            textInput.classList.add('input-error');
                            const label = textInput.dataset.validationLabel || 'Bagian Modifikasi';
                            const message = `Kolom "Jika Ada, tuliskan:" pada ${label} wajib diisi karena memilih Ada.`;
                            if (typeof Swal !== 'undefined') {
                                Swal.fire({ 
                                    icon: 'warning', 
                                    title: 'Formulir Belum Lengkap', 
                                    text: message, 
                                    confirmButtonColor: '#059669',
                                    confirmButtonText: 'Lengkapi Sekarang'
                                }).then(() => {
                                    textInput.scrollIntoView({ behavior: 'smooth', block: 'center' });
                                    textInput.focus();
                                });
                            } else {
                                alert(message);
                                textInput.focus();
                            }
                            return false;
                        }
                    }

                    formM.querySelectorAll('.input-nama-relevan, .input-tgl-relevan').forEach(el => {
                        el.disabled = false;
                    });
                    formM.querySelectorAll('input[data-status-field]').forEach(el => {
                        const status = formM.querySelector(`input[name="${el.dataset.statusField}"]:checked`);
                        if (status?.value !== 'ada') {
                            el.value = '';
                        }
                        el.disabled = false;
                    });
                });
            }

            // Fungsi Sinkronisasi Checkbox Matriks (Otomatisasi Bukti -> Metode)
            function initMatriksAsesmenLogic() {
                const rows = document.querySelectorAll('.row-matriks-asesmen');
                
                rows.forEach(row => {
                    const chkL = row.querySelector('.chk-bukti-l');
                    const chkTL = row.querySelector('.chk-bukti-tl');
                    const chkT = row.querySelector('.chk-bukti-t');
                    const methods = row.querySelectorAll('.chk-metode');
                    
                    methods.forEach(method => {
                        method.addEventListener('change', function() {
                            const checkedMethods = Array.from(methods)
                                                        .filter(m => m.checked)
                                                        .map(m => m.value);
                            
                            if (chkL) {
                                if (checkedMethods.includes('CL') || checkedMethods.includes('DIT')) {
                                    chkL.checked = true;
                                } else {
                                    chkL.checked = false;
                                }
                            }
                            
                            if (chkTL) {
                                if (checkedMethods.includes('VPK') || checkedMethods.includes('CVP')) {
                                    chkTL.checked = true;
                                } else {
                                    chkTL.checked = false;
                                }
                            }
                            
                            if (chkT) {
                                if (checkedMethods.includes('DPL') || checkedMethods.includes('DPT')) {
                                    chkT.checked = true;
                                } else {
                                    chkT.checked = false;
                                }
                            }
                        });
                    });
                });
            }
        });
    </script>
@endpush
