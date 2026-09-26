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
    $isMasterSchemeValidated = !empty($masterValidatorData['ttd']) || (($masterValidatorData['status_validasi'] ?? '') === 'tervalidasi');

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
    $adminTtd = $adminValidatorData['ttd'] ?? ($pendaftaran->tanda_tangan_admin ?? null);
    $adminTtdTgl = $adminValidatorData['ttd_tanggal'] ?? ($pendaftaran->tanggal_ttd_admin ? \Carbon\Carbon::parse($pendaftaran->tanggal_ttd_admin)->format('d/m/Y') : '');
    $adminStatusValidasi = $adminValidatorData['status_validasi'] ?? null;
    $isValidatedAdmin = !empty($adminValidatorData['ttd']) || $adminStatusValidasi === 'tervalidasi' || (!empty($pendaftaran->tanda_tangan_admin) && !empty($pendaftaran->tanggal_ttd_admin)) || (!$isMasterMode && $isMasterSchemeValidated);
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

    // 5. Tanda Tangan Asesor Penguji (Penyusun)
    $hasTtdAsesor = !empty($mapa01?->tanda_tangan_asesor) || !empty($penyusunTabelSaved['penyusun_1']['ttd']);
    $evaluasiMapa01[] = [
        'label' => 'Tanda Tangan Asesor Penguji (Penyusun)',
        'lengkap' => $hasTtdAsesor,
        'keterangan' => $hasTtdAsesor ? 'Tanda tangan Asesor Penguji telah tersedia.' : 'Asesor Penguji (Penyusun) belum membubuhkan tanda tangan.'
    ];

    $totalKurang = collect($evaluasiMapa01)->where('lengkap', false)->count();
    $semuaLengkapKecualiAdmin = ($totalKurang === 0);
    $adaDataKurang = !$semuaLengkapKecualiAdmin;
@endphp
<div class="wadah-mapa01 animasi-slide {{ $isAsesi ? 'mode-read-only-asesi' : '' }}" id="container-mapa01">
    
    <!-- NAVIGATION BAR ATAS -->
    <div class="tombol-aksi-container no-print" style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 1.25rem; flex-wrap: wrap; gap: 0.75rem;">
        <div style="display: flex; gap: 0.5rem; flex-wrap: wrap; align-items: center;">
            @php
                $halamanFormulir = route('asesor.mapa', array_filter(['skema_id' => $pendaftaran->skema_id ?? request('skema_id')]));
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
                } elseif (!$isInvalidPrev && (str_contains($prevUrl, 'daftar-peserta') || str_contains($prevUrl, 'penilaian'))) {
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
            @if($isAdmin)
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
                    {{ $isAdmin ? 'Simpan & Validasi Formulir' : 'Simpan Formulir' }}
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

    @if($isAdmin)
        <div class="no-print" style="margin-bottom: 1.25rem; background: {{ $isValidatedAdmin ? '#f0fdf4' : ($semuaLengkapKecualiAdmin ? '#eff6ff' : '#fffbeb') }}; border: 1.5px solid {{ $isValidatedAdmin ? '#86efac' : ($semuaLengkapKecualiAdmin ? '#93c5fd' : '#fcd34d') }}; border-radius: 12px; padding: 1.1rem 1.35rem; box-shadow: 0 1px 3px rgba(0,0,0,0.04);">
            <div style="display: flex; justify-content: space-between; align-items: flex-start; gap: 1rem; flex-wrap: wrap;">
                <div style="flex: 1; min-width: 280px;">
                    <div style="display: flex; align-items: center; gap: 0.5rem; margin-bottom: 0.35rem; flex-wrap: wrap;">
                        <span style="display: inline-flex; align-items: center; gap: 0.35rem; padding: 0.2rem 0.65rem; border-radius: 9999px; font-size: 0.72rem; font-weight: 800; text-transform: uppercase; letter-spacing: 0.5px; background: {{ $isValidatedAdmin ? '#dcfce7' : ($semuaLengkapKecualiAdmin ? '#dbeafe' : '#fef3c7') }}; color: {{ $isValidatedAdmin ? '#166534' : ($semuaLengkapKecualiAdmin ? '#1e40af' : '#92400e') }}; border: 1px solid {{ $isValidatedAdmin ? '#bbf7d0' : ($semuaLengkapKecualiAdmin ? '#bfdbfe' : '#fde68a') }};">
                            @if($isValidatedAdmin)
                                <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3" stroke-linecap="round" stroke-linejoin="round"><polyline points="20 6 9 17 4 12"></polyline></svg>
                                Tervalidasi Resmi (Admin LSP)
                            @elseif($semuaLengkapKecualiAdmin)
                                <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3" stroke-linecap="round" stroke-linejoin="round"><polyline points="20 6 9 17 4 12"></polyline></svg>
                                Siap Divalidasi &bull; Data Lengkap
                            @else
                                <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><path d="M10.29 3.86L1.82 18a2 2 0 0 0 1.71 3h16.94a2 2 0 0 0 1.71-3L13.71 3.86a2 2 0 0 0-3.42 0z"></path><line x1="12" y1="9" x2="12" y2="13"></line><line x1="12" y1="17" x2="12.01" y2="17"></line></svg>
                                Data Belum Lengkap ({{ $totalKurang }} Peringatan)
                            @endif
                        </span>
                        @if(!$isMasterMode && $isMasterSchemeValidated)
                            <span style="font-size: 0.72rem; color: #047857; font-weight: 700; background: #e6fcf5; padding: 0.2rem 0.5rem; border-radius: 6px; border: 1px solid #c3fae8;">
                                Valid via Master Skema
                            </span>
                        @endif
                    </div>

                    <h3 style="margin: 0.2rem 0 0.25rem 0; font-size: 1rem; font-weight: 800; color: {{ $isValidatedAdmin ? '#064e3b' : ($semuaLengkapKecualiAdmin ? '#1e3a8a' : '#78350f') }};">
                        @if($isValidatedAdmin)
                            Dokumen FR.MAPA.01 Telah Tervalidasi &amp; Disahkan
                        @elseif($semuaLengkapKecualiAdmin)
                            Pemberitahuan Validasi: Seluruh Komponen Asesmen Lengkap
                        @else
                            Pemberitahuan Validasi: Terdapat {{ $totalKurang }} Bagian yang Belum Lengkap
                        @endif
                    </h3>

                    <p style="margin: 0; font-size: 0.82rem; line-height: 1.5; color: {{ $isValidatedAdmin ? '#047857' : ($semuaLengkapKecualiAdmin ? '#1e40af' : '#92400e') }};">
                        @if($isValidatedAdmin)
                            Validator: <strong>{{ $adminValidatorNama }}</strong> ({{ $adminValidatorMet }}) pada tanggal <strong>{{ $adminTtdTgl }}</strong>.
                            @if(!empty($catatanValidasi))
                                <br><span style="font-style: italic;">Catatan: "{{ $catatanValidasi }}"</span>
                            @endif
                        @elseif($semuaLengkapKecualiAdmin)
                            Seluruh pendekatan, rencana unit matriks, konfirmasi pihak relevan, dan tanda tangan asesor telah lengkap sesuai regulasi BNSP. Silakan lakukan pengesahan validator.
                        @else
                            Administrator mendeteksi beberapa data perencanaan belum lengkap. Anda dapat meninjau rincian di bawah atau langsung melengkapinya pada formulir sebelum mengesahkan.
                        @endif
                    </p>
                </div>

                <div style="display: flex; gap: 0.65rem; align-items: center; flex-wrap: wrap;">
                    <button type="button" onclick="toggleRincianValidasiAdmin()" id="btn-toggle-rincian-validasi" style="background: #ffffff; border: 1.2px solid {{ $isValidatedAdmin ? '#86efac' : ($semuaLengkapKecualiAdmin ? '#bfdbfe' : '#fcd34d') }}; color: {{ $isValidatedAdmin ? '#166534' : ($semuaLengkapKecualiAdmin ? '#1e40af' : '#92400e') }}; padding: 0.5rem 0.85rem; border-radius: 8px; font-size: 0.78rem; font-weight: 700; cursor: pointer; display: inline-flex; align-items: center; gap: 0.4rem;">
                        <span id="text-toggle-rincian-validasi">Lihat Cek Kelengkapan</span>
                        <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round" id="icon-toggle-rincian"><polyline points="6 9 12 15 18 9"></polyline></svg>
                    </button>

                    @if($isAdmin && ($isMasterMode || !$isMasterSchemeValidated))
                        <button type="button" onclick="bukaModal('modalValidasiAdminMapa01')" class="tombol tombol-sm" style="background: {{ $isValidatedAdmin ? '#ffffff' : '#059669' }}; color: {{ $isValidatedAdmin ? '#065f46' : '#ffffff' }}; border: 1px solid {{ $isValidatedAdmin ? '#a7f3d0' : '#047857' }}; font-weight: 700; padding: 0.5rem 1rem;">
                            {{ $isValidatedAdmin ? 'Ubah Validasi' : 'Validasi & Sahkan Sekarang' }}
                        </button>
                    @endif
                </div>
            </div>

            <!-- RINCIAN CHECKLIST KELENGKAPAN MAPA 01 (COLLAPSIBLE) -->
            <div id="wadah-rincian-validasi-admin" style="display: {{ $adaDataKurang && !$isValidatedAdmin ? 'block' : 'none' }}; margin-top: 1rem; border-top: 1px solid {{ $isValidatedAdmin ? '#bbf7d0' : ($semuaLengkapKecualiAdmin ? '#bfdbfe' : '#fde68a') }}; padding-top: 0.85rem;">
                <div style="font-size: 0.76rem; font-weight: 800; color: #475569; text-transform: uppercase; letter-spacing: 0.5px; margin-bottom: 0.5rem;">
                    Status Parameter Kelengkapan Dokumen:
                </div>
                <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(280px, 1fr)); gap: 0.65rem;">
                    @foreach($evaluasiMapa01 as $ev)
                        <div style="display: flex; align-items: flex-start; gap: 0.5rem; background: rgba(255,255,255,0.8); border: 1px solid {{ $ev['lengkap'] ? '#bbf7d0' : '#fecaca' }}; border-radius: 8px; padding: 0.6rem 0.75rem;">
                            <div style="margin-top: 0.1rem;">
                                @if($ev['lengkap'])
                                    <span style="display: inline-flex; align-items: center; justify-content: center; width: 18px; height: 18px; border-radius: 50%; background: #22c55e; color: #ffffff; font-size: 0.68rem; font-weight: 900;">✓</span>
                                @else
                                    <span style="display: inline-flex; align-items: center; justify-content: center; width: 18px; height: 18px; border-radius: 50%; background: #ef4444; color: #ffffff; font-size: 0.68rem; font-weight: 900;">✕</span>
                                @endif
                            </div>
                            <div style="font-size: 0.78rem; line-height: 1.35;">
                                <strong style="display: block; color: {{ $ev['lengkap'] ? '#14532d' : '#991b1b' }};">{{ $ev['label'] }}</strong>
                                <span style="color: #64748b; font-size: 0.74rem;">{{ $ev['keterangan'] }}</span>
                            </div>
                        </div>
                    @endforeach
                </div>
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
            $candidateTtd = $mapa01?->tanda_tangan_asesor ?? ($penyusunTabelSaved['penyusun_1']['ttd'] ?? null);
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
                            <th rowspan="2" style="width: 3.5%; min-width: 38px; text-align: center;" class="th-slate">L</th>
                            <th rowspan="2" style="width: 3.5%; min-width: 38px; text-align: center;" class="th-slate">TL</th>
                            <th rowspan="2" style="width: 3.5%; min-width: 38px; text-align: center;" class="th-slate">T</th>
                            
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
                            <tr>
                                <td style="font-weight: 700; color: #0f172a; vertical-align: middle;">
                                    <div style="color: #0284c7; font-size: 0.82rem; font-weight: 800;">{{ $unit->kode_unit }}</div>
                                    <div style="font-size: 0.84rem; font-weight: 600; color: #1e293b; line-height: 1.4; margin-top: 0.25rem;">{{ $unit->judul_unit }}</div>
                                </td>
                                <td>
                                    <textarea name="rencana_unit_matriks[{{ $uId }}][bukti]" rows="3" class="textarea-bukti-mapa" placeholder="Tuliskan bukti unjuk kerja, produk, portofolio...">{{ $savedBukti }}</textarea>
                                </td>
                                <!-- JENIS BUKTI -->
                                <td style="text-align: center; vertical-align: middle;">
                                    <input type="checkbox" name="rencana_unit_matriks[{{ $uId }}][l]" value="1" {{ $savedL ? 'checked' : '' }} style="accent-color: #36648b; width: 16px; height: 16px; cursor: pointer;">
                                </td>
                                <td style="text-align: center; vertical-align: middle;">
                                    <input type="checkbox" name="rencana_unit_matriks[{{ $uId }}][tl]" value="1" {{ $savedTl ? 'checked' : '' }} style="accent-color: #36648b; width: 16px; height: 16px; cursor: pointer;">
                                </td>
                                <td style="text-align: center; vertical-align: middle;">
                                    <input type="checkbox" name="rencana_unit_matriks[{{ $uId }}][t]" value="1" {{ $savedT ? 'checked' : '' }} style="accent-color: #36648b; width: 16px; height: 16px; cursor: pointer;">
                                </td>

                                <!-- METODE & PERANGKAT -->
                                <td style="text-align: center; vertical-align: middle;">
                                    <input type="checkbox" name="rencana_unit_matriks[{{ $uId }}][methods][]" value="CL" {{ in_array('CL', $savedMethods) ? 'checked' : '' }} style="accent-color: #36648b; cursor: pointer; width: 15px; height: 15px;">
                                </td>
                                <td style="text-align: center; vertical-align: middle;">
                                    <input type="checkbox" name="rencana_unit_matriks[{{ $uId }}][methods][]" value="DIT" {{ in_array('DIT', $savedMethods) ? 'checked' : '' }} style="accent-color: #36648b; cursor: pointer; width: 15px; height: 15px;">
                                </td>
                                <td style="text-align: center; vertical-align: middle;">
                                    <input type="checkbox" name="rencana_unit_matriks[{{ $uId }}][methods][]" value="DPL" {{ in_array('DPL', $savedMethods) ? 'checked' : '' }} style="accent-color: #36648b; cursor: pointer; width: 15px; height: 15px;">
                                </td>
                                <td style="text-align: center; vertical-align: middle;">
                                    <input type="checkbox" name="rencana_unit_matriks[{{ $uId }}][methods][]" value="DPT" {{ in_array('DPT', $savedMethods) ? 'checked' : '' }} style="accent-color: #36648b; cursor: pointer; width: 15px; height: 15px;">
                                </td>
                                <td style="text-align: center; vertical-align: middle;">
                                    <input type="checkbox" name="rencana_unit_matriks[{{ $uId }}][methods][]" value="VPK" {{ in_array('VPK', $savedMethods) ? 'checked' : '' }} style="accent-color: #36648b; cursor: pointer; width: 15px; height: 15px;">
                                </td>
                                <td style="text-align: center; vertical-align: middle;">
                                    <input type="checkbox" name="rencana_unit_matriks[{{ $uId }}][methods][]" value="CVP" {{ in_array('CVP', $savedMethods) ? 'checked' : '' }} style="accent-color: #36648b; cursor: pointer; width: 15px; height: 15px;">
                                </td>
                                <td style="text-align: center; vertical-align: middle;">
                                    <input type="checkbox" name="rencana_unit_matriks[{{ $uId }}][methods][]" value="CRP" {{ in_array('CRP', $savedMethods) ? 'checked' : '' }} style="accent-color: #36648b; cursor: pointer; width: 15px; height: 15px;">
                                </td>
                                <td style="text-align: center; vertical-align: middle;">
                                    <input type="checkbox" name="rencana_unit_matriks[{{ $uId }}][methods][]" value="PW" {{ in_array('PW', $savedMethods) ? 'checked' : '' }} style="accent-color: #36648b; cursor: pointer; width: 15px; height: 15px;">
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
                        <div style="font-size: 0.8rem; color: #475569; margin-bottom: 0.25rem;">Jika Ada, tuliskan:</div>
                        <input type="text" name="karakteristik_kandidat_teks" value="{{ $mapa01->karakteristik_kandidat_teks ?? '' }}" class="input-inline-modern" placeholder="Tuliskan spesifikasi karakteristik khusus jika ada...">
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
                        <div style="font-size: 0.8rem; color: #475569; margin-bottom: 0.25rem;">Jika Ada, tuliskan:</div>
                        <input type="text" name="kebutuhan_kontekstualisasi_teks" value="{{ $mapa01->kebutuhan_kontekstualisasi_teks ?? '' }}" class="input-inline-modern" placeholder="Tuliskan kebutuhan kontekstualisasi jika ada...">
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
                        <div style="font-size: 0.8rem; color: #475569; margin-bottom: 0.25rem;">Jika Ada, tuliskan:</div>
                        <input type="text" name="saran_pelatihan_teks" value="{{ $mapa01->saran_pelatihan_teks ?? '' }}" class="input-inline-modern" placeholder="Tuliskan saran paket pelatihan jika ada...">
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
                        <div style="font-size: 0.8rem; color: #475569; margin-bottom: 0.25rem;">Jika Ada, tuliskan:</div>
                        <input type="text" name="penyesuaian_perangkat_teks" value="{{ $mapa01->penyesuaian_perangkat_teks ?? '' }}" class="input-inline-modern" placeholder="Tuliskan penyesuaian perangkat jika ada...">
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
                        <div style="font-size: 0.8rem; color: #475569; margin-bottom: 0.25rem;">Jika Ada, tuliskan:</div>
                        <input type="text" name="peluang_terintegrasi_teks" value="{{ $mapa01->peluang_terintegrasi_teks ?? '' }}" class="input-inline-modern" placeholder="Tuliskan peluang asesmen terintegrasi jika ada...">
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
                                Tanda Tangan & Tanggal
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

                                <!-- Kolom 3: Tanda Tangan & Tanggal -->
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

            <!-- TABEL PENYUSUN DAN VALIDATOR -->
            <div style="font-weight: 800; font-size: 0.95rem; margin-top: 1.5rem; margin-bottom: 0.6rem; color: #0f172a;">
                Penyusun dan Validator:
            </div>

            @php
                if (empty($adminValidatorNama)) {
                    $adminValidatorNama = $penyusunTabelSaved['validator_1']['nama'] ?? $dbAdminNama;
                }
                if (empty($adminValidatorMet)) {
                    $adminValidatorMet = $penyusunTabelSaved['validator_1']['nomor_met'] ?? $dbAdminMet;
                }
                if (empty($adminTtd)) {
                    $adminTtd = $penyusunTabelSaved['validator_1']['ttd'] 
                        ?? ($pendaftaran->tanda_tangan_admin ?? null);
                }
                if (empty($adminTtdTgl)) {
                    $adminTtdTgl = $penyusunTabelSaved['validator_1']['ttd_tanggal'] 
                        ?? ($pendaftaran->tanggal_ttd_admin ? \Carbon\Carbon::parse($pendaftaran->tanggal_ttd_admin)->format('d/m/Y') : '');
                }
            @endphp

            <div style="overflow-x: auto; margin-bottom: 1.5rem; border: 1px solid #cbd5e1; border-radius: 6px;">
                <table class="tabel-mapa-modern" style="font-size: 0.82rem; width: 100%; margin-bottom: 0; border: none;">
                    <thead>
                        <tr>
                            <th class="th-slate" style="width: 20%; text-align: center; padding: 0.55rem 0.75rem;">Status</th>
                            <th class="th-slate" style="width: 5%; text-align: center; padding: 0.55rem 0.75rem;">No.</th>
                            <th class="th-slate" style="width: 32%; text-align: left; padding: 0.55rem 0.75rem;">Nama</th>
                            <th class="th-slate" style="width: 23%; text-align: left; padding: 0.55rem 0.75rem;">Nomor MET / Registrasi</th>
                            <th class="th-slate" style="width: 20%; text-align: center; padding: 0.55rem 0.75rem;">Tanda Tangan & Tgl</th>
                        </tr>
                    </thead>
                    <tbody>
                        <!-- 1. PENYUSUN (ASESOR PENGUJI) -->
                        <tr>
                            <td style="text-align: center; background-color: #f8fafc; padding: 0.6rem 0.75rem;">
                                <div style="font-weight: 800; font-size: 0.88rem; color: #0f172a; line-height: 1.2;">PENYUSUN</div>
                                <div style="font-size: 0.75rem; color: #64748b; font-weight: 500;">(Asesor Penguji)</div>
                                <div style="margin-top: 0.35rem;">
                                    @if($asesorTtd)
                                        <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-full text-[10px] font-bold bg-emerald-50 text-emerald-700 border border-emerald-200">
                                            <span class="w-1.5 h-1.5 rounded-full bg-emerald-500"></span>
                                            Telah Ditandatangani
                                        </span>
                                    @else
                                        <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-full text-[10px] font-bold bg-amber-50 text-amber-700 border border-amber-200">
                                            <span class="w-1.5 h-1.5 rounded-full bg-amber-500"></span>
                                            Menunggu TTD
                                        </span>
                                    @endif
                                </div>
                            </td>
                            <td style="text-align: center; font-weight: 700; color: #475569; padding: 0.6rem 0.75rem;">
                                1.
                            </td>
                            <td style="padding: 0.6rem 0.75rem;">
                                <input type="text" 
                                       name="penyusun_validator_tabel[penyusun_1][nama]" 
                                       value="{{ $asesorNama }}" 
                                       class="w-full px-3 py-1.5 text-sm font-semibold rounded border border-slate-300 focus:border-slate-700 focus:ring-1 focus:ring-slate-700 bg-white text-slate-800 transition-colors" 
                                       placeholder="Nama asesor...">
                            </td>
                            <td style="padding: 0.6rem 0.75rem;">
                                <input type="text" 
                                       name="penyusun_validator_tabel[penyusun_1][nomor_met]" 
                                       value="{{ $asesorMet }}" 
                                       class="w-full px-3 py-1.5 text-sm font-medium rounded border border-slate-300 focus:border-slate-700 focus:ring-1 focus:ring-slate-700 bg-white text-slate-800 transition-colors" 
                                       placeholder="Nomor MET/Registrasi...">
                            </td>
                            <td style="padding: 0.6rem 0.75rem; text-align: center;" id="container-ttd-asesor-preview">
                                @if($asesorTtd)
                                    <div class="flex flex-col items-center gap-1">
                                        <div class="h-11 w-28 rounded border border-slate-200 bg-slate-50/80 p-1 flex items-center justify-center shadow-2xs">
                                            <img src="{{ Str::startsWith($asesorTtd, 'data:') ? $asesorTtd : asset($asesorTtd) }}" alt="TTD Asesor" class="max-h-9 max-w-full object-contain">
                                        </div>
                                        <span class="text-[11px] font-semibold text-slate-500">{{ $penyusunTabelSaved['penyusun_1']['ttd_tanggal'] ?? date('d/m/Y') }}</span>
                                        @if(!$isAdmin)
                                            <button type="button" onclick="bukaModal('modalCanvasTtd')" class="text-[10px] text-indigo-600 hover:underline mt-0.5">Ubah</button>
                                        @endif
                                    </div>
                                @else
                                    @if(!$isAdmin)
                                        <button type="button" 
                                                onclick="bukaModal('modalCanvasTtd')" 
                                                class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded bg-indigo-600 hover:bg-indigo-700 text-white text-xs font-semibold shadow-2xs transition-colors cursor-pointer">
                                            <span>Bubuhkan Tanda Tangan</span>
                                        </button>
                                    @else
                                        <span class="inline-flex items-center px-2.5 py-1 rounded-full bg-slate-100 text-slate-500 border border-slate-200 text-[11px] font-medium">
                                            Menunggu TTD Asesor
                                        </span>
                                    @endif
                                @endif
                                <input type="hidden" name="tanda_tangan_asesor" id="input-ttd-asesor-base64" value="{{ $asesorTtd }}">
                                <input type="hidden" name="penyusun_validator_tabel[penyusun_1][ttd]" id="input-penyusun-ttd" value="{{ $asesorTtd }}">
                                <input type="hidden" name="penyusun_validator_tabel[penyusun_1][ttd_tanggal]" value="{{ $penyusunTabelSaved['penyusun_1']['ttd_tanggal'] ?? date('d/m/Y') }}">
                            </td>
                        </tr>

                        <!-- 2. VALIDATOR (ADMIN LSP) -->
                        <tr>
                            <td style="text-align: center; background-color: #f8fafc; padding: 0.6rem 0.75rem;">
                                <div style="font-weight: 800; font-size: 0.88rem; color: #0f172a; line-height: 1.2;">VALIDATOR</div>
                                <div style="font-size: 0.75rem; color: #64748b; font-weight: 500;">(Admin LSP)</div>
                                <div style="margin-top: 0.35rem;">
                                    @if($adminTtd)
                                        <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-full text-[10px] font-bold bg-emerald-50 text-emerald-700 border border-emerald-200">
                                            <span class="w-1.5 h-1.5 rounded-full bg-emerald-500"></span>
                                            Tervalidasi
                                        </span>
                                    @else
                                        <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-full text-[10px] font-bold bg-amber-50 text-amber-700 border border-amber-200">
                                            <span class="w-1.5 h-1.5 rounded-full bg-amber-500"></span>
                                            Menunggu
                                        </span>
                                    @endif
                                </div>
                            </td>
                            <td style="text-align: center; font-weight: 700; color: #475569; padding: 0.6rem 0.75rem;">
                                2.
                            </td>
                            <td style="padding: 0.6rem 0.75rem;">
                                <input type="text" 
                                       name="penyusun_validator_tabel[validator_1][nama]" 
                                       value="{{ $adminValidatorNama }}" 
                                       class="w-full px-3 py-1.5 text-sm font-semibold rounded border border-slate-300 focus:border-slate-700 focus:ring-1 focus:ring-slate-700 bg-white text-slate-800 transition-colors" 
                                       placeholder="Nama admin validator...">
                            </td>
                            <td style="padding: 0.6rem 0.75rem;">
                                <input type="text" 
                                       name="penyusun_validator_tabel[validator_1][nomor_met]" 
                                       value="{{ $adminValidatorMet }}" 
                                       class="w-full px-3 py-1.5 text-sm font-medium rounded border border-slate-300 focus:border-slate-700 focus:ring-1 focus:ring-slate-700 bg-white text-slate-800 transition-colors" 
                                       placeholder="Nomor registrasi admin...">
                            </td>
                            <td style="padding: 0.6rem 0.75rem; text-align: center;" id="container-ttd-validator-preview">
                                @if($adminTtd)
                                    <div class="flex flex-col items-center gap-1">
                                        <div class="h-11 w-28 rounded border border-slate-200 bg-slate-50/80 p-1 flex items-center justify-center shadow-2xs">
                                            <img src="{{ Str::startsWith($adminTtd, 'data:') ? $adminTtd : asset($adminTtd) }}" alt="TTD Validator" class="max-h-9 max-w-full object-contain">
                                        </div>
                                        <span class="inline-flex items-center px-2 py-0.5 rounded-full bg-emerald-50 text-emerald-700 border border-emerald-200 text-[10px] font-bold">
                                            <span class="w-1.5 h-1.5 rounded-full bg-emerald-500 mr-1.5"></span>
                                            <span>Tervalidasi Admin</span>
                                        </span>
                                        <span class="text-[11px] font-semibold text-slate-500">{{ $adminTtdTgl }}</span>
                                        @if($isAdmin)
                                            @if(!$isMasterMode && $isMasterSchemeValidated)
                                                <span class="text-[10px] text-emerald-700 font-semibold italic mt-0.5">(Tervalidasi via Master Skema)</span>
                                            @else
                                                <button type="button" onclick="bukaModal('modalValidasiAdminMapa01')" class="text-[11px] text-sky-700 hover:text-sky-900 font-semibold underline cursor-pointer mt-0.5">
                                                    Ubah Validasi
                                                </button>
                                            @endif
                                        @endif
                                    </div>
                                @else
                                    <div class="flex flex-col items-center gap-1">
                                        @if($isAdmin)
                                            <button type="button" 
                                                    onclick="bukaModal('modalValidasiAdminMapa01')" 
                                                    class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded bg-emerald-600 hover:bg-emerald-700 text-white text-xs font-semibold shadow-2xs transition-colors cursor-pointer">
                                                <span>Validasi</span>
                                            </button>
                                        @else
                                            <span class="inline-flex items-center px-2.5 py-1 rounded bg-amber-50 text-amber-700 border border-amber-200 text-xs font-semibold">
                                                <span class="w-1.5 h-1.5 rounded-full bg-amber-500 mr-1.5"></span>
                                                <span>Menunggu Validasi</span>
                                            </span>
                                            <span class="text-[10px] text-slate-400 italic">(Divalidasi oleh Admin)</span>
                                        @endif
                                    </div>
                                @endif
                                <input type="hidden" name="penyusun_validator_tabel[validator_1][ttd]" id="input-ttd-validator-base64" value="{{ $adminTtd }}">
                                <input type="hidden" name="penyusun_validator_tabel[validator_1][ttd_tanggal]" value="{{ $adminTtdTgl ?: ($isAdmin ? date('d/m/Y') : '') }}">
                                <input type="hidden" name="penyusun_validator_tabel[validator_1][status_validasi]" value="{{ $isValidatedAdmin ? 'tervalidasi' : ($isAdmin ? 'tervalidasi' : 'menunggu') }}">
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>

            <!-- TOMBOL AKSI & NAVIGASI DI BAWAH -->
            @php
                $halamanFormulirBawah = route('asesor.mapa', array_filter(['skema_id' => $pendaftaran->skema_id ?? request('skema_id')]));
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
                        @if($isAdmin)
                            <button type="button" onclick="bukaModal('modalValidasiAdminMapa01')" class="tombol tombol-utama" style="background: #059669; border-color: #059669; font-weight: 700; padding: 0.65rem 1.5rem;">
                                {{ $isValidatedAdmin ? 'Perbarui Validasi MAPA.01' : 'Validasi & Sahkan FR.MAPA.01' }}
                            </button>
                        @endif

                        <div id="bottom-actions-edit" style="display: {{ $isConfigured ? 'none' : 'flex' }}; gap: 0.75rem; flex-wrap: wrap;">
                            <button type="button" onclick="submitMapa01('draft')" class="tombol tombol-sekunder" style="font-weight: 700;">
                                Simpan Draft
                            </button>
                            <button type="button" onclick="submitMapa01('konfirmasi')" class="tombol tombol-utama" style="background: #2563eb; border-color: #2563eb; font-weight: 700; padding: 0.65rem 1.5rem;">
                                {{ $isAdmin ? 'Sahkan & Validasi FR.MAPA.01' : 'Sahkan Rencana FR.MAPA.01' }}
                            </button>
                        </div>

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

<!-- MODAL POPUP CANVAS SIGNATURE PAD -->
<div class="modal-overlay" id="modalCanvasTtd">
    <div class="modal-konten" style="max-width: 500px; text-align: center; border-radius: 12px; padding: 1.75rem;">
        <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 1rem;">
            <h3 style="color: #0f172a; margin: 0; font-weight: 800;">Tanda Tangan Digital Asesor</h3>
            <button type="button" onclick="tutupModal('modalCanvasTtd')" style="background: none; border: none; font-size: 1.4rem; cursor: pointer; color: #64748b;">&times;</button>
        </div>

        <p style="font-size: 0.85rem; color: #64748b; margin-bottom: 1rem;">
            Gunakan mouse atau layar sentuh untuk menggambar tanda tangan Anda secara presisi pada bidang di bawah:
        </p>

        <canvas id="canvas-ttd-asesi" width="440" height="180" class="canvas-signature-pad" style="border: 2px dashed #94a3b8; border-radius: 8px; background: #ffffff; cursor: crosshair; touch-action: none; display: block; margin: 0 auto;"></canvas>

        <div style="margin-top: 1.25rem; display: flex; gap: 0.75rem; justify-content: center;">
            <button type="button" class="tombol tombol-sekunder tombol-sm" id="btn-clear-canvas">
                Bersihkan
            </button>
            <button type="button" class="tombol tombol-utama tombol-sm" id="btn-simpan-canvas" style="background: #059669; border-color: #059669; font-weight: 700;">
                Gunakan Tanda Tangan
            </button>
        </div>
    </div>
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
            <input type="hidden" name="tanda_tangan_admin_base64" id="input-ttd-validator-modal-base64" value="{{ $adminTtd }}">

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
    <script src="{{ asset('js/asesi/pendaftaran-bagian32.js') }}"></script>
    <script>
        let isMapa01EditMode = {{ (!$isAsesi && $isConfigured) ? 'false' : 'true' }};

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
            const inputAksi = document.getElementById('inputAksiMapa01');
            if (inputAksi) inputAksi.value = aksi;

            // Pastikan input nama dan tanggal pada tabel konfirmasi di-enable agar terkirim ke backend
            form.querySelectorAll('.input-nama-relevan, .input-tgl-relevan').forEach(el => {
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

        document.addEventListener('DOMContentLoaded', function() {
            const formVal = document.getElementById('form-validasi-admin-mapa01');
            if (formVal) {
                formVal.addEventListener('submit', function(e) {
                    const inputNama = formVal.querySelector('input[name="validator_nama"]');
                    const inputMet = formVal.querySelector('input[name="validator_nomor_met"]');

                    if (!inputNama || !inputNama.value.trim()) {
                        e.preventDefault();
                        if (typeof Swal !== 'undefined') {
                            Swal.fire({
                                icon: 'warning',
                                title: 'Nama Validator Belum Diisi',
                                text: 'Harap masukkan nama lengkap validator sebelum melakukan pengesahan dokumen.',
                                confirmButtonColor: '#059669'
                            });
                        } else {
                            alert('Nama Validator (Admin LSP) wajib diisi.');
                        }
                        inputNama.focus();
                        return;
                    }

                    if (!inputMet || !inputMet.value.trim()) {
                        e.preventDefault();
                        if (typeof Swal !== 'undefined') {
                            Swal.fire({
                                icon: 'warning',
                                title: 'Nomor Registrasi/NIP Belum Diisi',
                                text: 'Harap masukkan Nomor Registrasi / NIP validator.',
                                confirmButtonColor: '#059669'
                            });
                        } else {
                            alert('Nomor Registrasi / NIP Validator wajib diisi.');
                        }
                        inputMet.focus();
                        return;
                    }
                });
            }
        });

        function initAsesorCanvas() {
            const canvas = document.getElementById('canvas-ttd-asesi');
            if (!canvas) return;
            const ctx = canvas.getContext('2d');
            let isDrawing = false;
            ctx.lineWidth = 2.5;
            ctx.lineCap = 'round';
            ctx.lineJoin = 'round';
            ctx.strokeStyle = '#1e3a8a';

            function getPos(e) {
                const rect = canvas.getBoundingClientRect();
                const clientX = e.touches ? e.touches[0].clientX : e.clientX;
                const clientY = e.touches ? e.touches[0].clientY : e.clientY;
                const scaleX = canvas.width / rect.width;
                const scaleY = canvas.height / rect.height;
                return {
                    x: (clientX - rect.left) * scaleX,
                    y: (clientY - rect.top) * scaleY
                };
            }

            function start(e) {
                isDrawing = true;
                const pos = getPos(e);
                ctx.beginPath();
                ctx.moveTo(pos.x, pos.y);
                if (e.cancelable) e.preventDefault();
            }

            function draw(e) {
                if (!isDrawing) return;
                const pos = getPos(e);
                ctx.lineTo(pos.x, pos.y);
                ctx.stroke();
                if (e.cancelable) e.preventDefault();
            }

            function stop() {
                if (!isDrawing) return;
                isDrawing = false;
                ctx.closePath();
            }

            canvas.addEventListener('mousedown', start);
            canvas.addEventListener('mousemove', draw);
            window.addEventListener('mouseup', stop);

            canvas.addEventListener('touchstart', start, { passive: false });
            canvas.addEventListener('touchmove', draw, { passive: false });
            window.addEventListener('touchend', stop);

            const btnClear = document.getElementById('btn-clear-canvas');
            if (btnClear) {
                btnClear.addEventListener('click', function() {
                    ctx.clearRect(0, 0, canvas.width, canvas.height);
                });
            }
        }

        document.addEventListener('DOMContentLoaded', function() {
            applyMapa01Mode();
            initAsesorCanvas();

            const formM = document.getElementById('form-mapa01');
            if (formM) {
                formM.addEventListener('submit', function() {
                    formM.querySelectorAll('.input-nama-relevan, .input-tgl-relevan').forEach(el => {
                        el.disabled = false;
                    });
                });
            }

            // Callback saat simpan tanda tangan canvas asesor
            const btnSimpan = document.getElementById('btn-simpan-canvas');
            if (btnSimpan) {
                btnSimpan.addEventListener('click', function() {
                    const canvas = document.getElementById('canvas-ttd-asesi');
                    if (canvas) {
                        const dataUrl = canvas.toDataURL('image/png');
                        const hiddenInput = document.getElementById('input-ttd-asesor-base64');
                        if (hiddenInput) {
                            hiddenInput.value = dataUrl;
                        }
                        const hiddenPenyusun = document.getElementById('input-penyusun-ttd');
                        if (hiddenPenyusun) {
                            hiddenPenyusun.value = dataUrl;
                        }
                        const container = document.getElementById('container-ttd-asesor-preview');
                        if (container) {
                            container.innerHTML = `
                                <div class="flex flex-col items-center gap-1">
                                    <div class="h-11 w-28 rounded-lg border border-slate-200 bg-slate-50/80 p-1 flex items-center justify-center shadow-2xs">
                                        <img src="${dataUrl}" alt="TTD Asesor" class="max-h-9 max-w-full object-contain">
                                    </div>
                                    <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-full text-[10px] font-bold bg-emerald-50 text-emerald-700 border border-emerald-200">
                                        <span class="w-1.5 h-1.5 rounded-full bg-emerald-500 mr-1"></span> Siap Disimpan
                                    </span>
                                    <button type="button" onclick="bukaModal('modalCanvasTtd')" class="text-[10px] text-indigo-600 hover:underline mt-0.5">Ubah</button>
                                </div>
                            `;
                        }
                        if (typeof tutupModal === 'function') {
                            tutupModal('modalCanvasTtd');
                        } else {
                            const modal = document.getElementById('modalCanvasTtd');
                            if (modal) modal.style.display = 'none';
                        }
                    }
                });
            }
        });
    </script>
@endpush
