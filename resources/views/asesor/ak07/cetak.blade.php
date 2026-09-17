<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>FR.AK.07 Ceklis Penyesuaian yang Wajar - {{ $pendaftaran->asesi->nama_lengkap ?? 'Asesi' }}</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Public+Sans:wght@400;600;700;800;900&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    
    <style>
        * {
            box-sizing: border-box;
            margin: 0;
            padding: 0;
        }

        body {
            font-family: 'Public Sans', -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;
            font-size: 9.5pt;
            color: #0f172a;
            background-color: #f8fafc;
            line-height: 1.45;
        }

        .paper-container {
            max-width: 210mm;
            min-height: 297mm;
            margin: 20px auto;
            background: #ffffff;
            padding: 15mm 20mm;
            box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1), 0 2px 4px -1px rgba(0, 0, 0, 0.06);
            border-radius: 4px;
        }

        .tabel-bnsp {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 1rem;
            font-size: 9pt;
        }

        .tabel-bnsp th, 
        .tabel-bnsp td {
            border: 1px solid #1e293b;
            padding: 5px 8px;
            vertical-align: middle;
        }

        .tabel-bnsp th {
            background-color: #f1f5f9;
            font-weight: 700;
            text-align: center;
        }

        .badge-status {
            display: inline-block;
            padding: 2px 8px;
            border-radius: 4px;
            font-size: 8pt;
            font-weight: 700;
            text-transform: uppercase;
        }

        .badge-confirmed {
            background-color: #dcfce7;
            color: #15803d;
            border: 1px solid #86efac;
        }

        .badge-draft {
            background-color: #fef3c7;
            color: #b45309;
            border: 1px solid #fde68a;
        }

        .signature-box {
            height: 65px;
            display: flex;
            align-items: center;
            justify-content: center;
        }

        .signature-box img {
            max-height: 60px;
            max-width: 160px;
            object-fit: contain;
        }

        .no-print-bar {
            max-width: 210mm;
            margin: 15px auto 0 auto;
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 10px 15px;
            background: #ffffff;
            border-radius: 8px;
            border: 1px solid #e2e8f0;
        }

        @media print {
            body {
                background: #ffffff !important;
                color: #000000 !important;
            }

            .paper-container {
                margin: 0 !important;
                padding: 10mm 15mm !important;
                box-shadow: none !important;
                max-width: 100% !important;
                min-height: auto !important;
            }

            .no-print-bar {
                display: none !important;
            }

            .page-break {
                page-break-before: always;
            }

            .tabel-bnsp th, 
            .tabel-bnsp td {
                border-color: #000000 !important;
            }
        }
    </style>
</head>
<body>

    <!-- TOP ACTION BAR UNTUK CETAK -->
    <div class="no-print-bar">
        <div style="font-size: 0.85rem; font-weight: 700; color: #1e293b;">
            Preview Lembar FR.AK.07 (Format Cetak A4 BNSP)
        </div>
        <div style="display: flex; gap: 8px;">
            <button type="button" onclick="window.close()" style="padding: 6px 14px; border-radius: 6px; border: 1px solid #cbd5e1; background: #fff; font-size: 0.8rem; font-weight: 600; cursor: pointer;">
                Tutup
            </button>
            <button type="button" onclick="window.print()" style="padding: 6px 16px; border-radius: 6px; border: none; background: #2563eb; color: #fff; font-size: 0.8rem; font-weight: 700; cursor: pointer;">
                Cetak / Simpan PDF
            </button>
        </div>
    </div>

    <div class="paper-container">

        <!-- KOP RESMI BNSP & LSP -->
        @include('komponen.kop-formulir-bnsp', [
            'kodeForm' => 'FR.AK.07',
            'judulForm' => 'CEKLIS PENYESUAIAN YANG WAJAR DAN BERALASAN',
            'tipeDokumen' => 'Kontekstualisasi Asesmen'
        ])

        <!-- TABEL IDENTITAS ASESMEN -->
        <table class="tabel-bnsp" style="margin-bottom: 0.75rem;">
            <tr>
                <td rowspan="2" style="width: 25%; font-weight: 700; background: #f8fafc;">
                    Skema Sertifikasi<br>
                    <span style="font-size: 8pt; font-weight: normal; color: #475569;">(KKNI/Okupasi/Klaster)</span>
                </td>
                <td style="width: 12%; font-weight: 600;">Judul</td>
                <td style="width: 2%; text-align: center;">:</td>
                <td style="font-weight: 700;">{{ $pendaftaran->skema->nama_skema ?? '-' }}</td>
            </tr>
            <tr>
                <td style="font-weight: 600;">Nomor</td>
                <td style="text-align: center;">:</td>
                <td style="font-weight: 700;">{{ $pendaftaran->skema->kode_skema ?? '-' }}</td>
            </tr>
            <tr>
                <td colspan="2" style="font-weight: 600; background: #f8fafc;">TUK</td>
                <td style="text-align: center;">:</td>
                <td>{{ $pendaftaran->jadwal->nama_tuk ?? 'Sewaktu / Tempat Kerja / Mandiri' }}</td>
            </tr>
            <tr>
                <td colspan="2" style="font-weight: 600; background: #f8fafc;">Nama Asesor</td>
                <td style="text-align: center;">:</td>
                <td style="font-weight: 700;">{{ $pendaftaran->asesor->nama_lengkap ?? auth()->user()->nama_lengkap }} (No. MET: {{ $pendaftaran->asesor->nomor_registrasi ?? auth()->user()->nomor_registrasi ?? '-' }})</td>
            </tr>
            <tr>
                <td colspan="2" style="font-weight: 600; background: #f8fafc;">Nama Asesi</td>
                <td style="text-align: center;">:</td>
                <td style="font-weight: 700;">{{ $pendaftaran->asesi->nama_lengkap ?? '-' }} (Reg: #{{ $pendaftaran->nomor_pendaftaran }})</td>
            </tr>
            <tr>
                <td colspan="2" style="font-weight: 600; background: #f8fafc;">Tanggal Asesmen</td>
                <td style="text-align: center;">:</td>
                <td>{{ $pendaftaran->jadwal?->tanggal_mulai ? \Carbon\Carbon::parse($pendaftaran->jadwal->tanggal_mulai)->translatedFormat('d F Y') : \Carbon\Carbon::now()->translatedFormat('d F Y') }}</td>
            </tr>
        </table>

        <!-- 1. POTENSI ASESI & FASE -->
        @php
            $potensiVal = (int) ($ak07->potensi_asesi ?? 1);
            $potensiText = $potensiDefinitions[$potensiVal] ?? '-';
            $faseVal = match($ak07->fase_penggunaan ?? 'saat_pra_asesmen') {
                'pra_asesmen' => 'Pra Asesmen',
                'saat_pra_asesmen' => 'Pada Saat Asesmen',
                'setelah_pra_asesmen' => 'Setelah Asesmen',
                default => 'Pada Saat Asesmen'
            };
            $savedChecklist = (array) ($ak07->items_checklist ?? []);
        @endphp

        <table class="tabel-bnsp" style="margin-bottom: 0.75rem;">
            <tr>
                <td style="width: 25%; font-weight: 700; background: #f8fafc;">Potensi Asesi</td>
                <td style="width: 2%; text-align: center;">:</td>
                <td><strong>Kategori {{ $potensiVal }}:</strong> {{ $potensiText }}</td>
            </tr>
            <tr>
                <td style="font-weight: 700; background: #f8fafc;">Fase Penggunaan</td>
                <td style="text-align: center;">:</td>
                <td><strong>{{ $faseVal }}</strong></td>
            </tr>
        </table>

        <!-- 2. MATRIKS 8 KATEGORI PENYESUAIAN YANG WAJAR -->
        <div style="font-weight: 800; font-size: 9.5pt; text-transform: uppercase; margin-bottom: 0.35rem; color: #0f172a;">
            Matriks Penyesuaian yang Wajar dan Beralasan (8 Kategori BNSP):
        </div>

        <table class="tabel-bnsp" style="margin-bottom: 0.75rem;">
            <thead>
                <tr>
                    <th style="width: 5%;">No</th>
                    <th style="width: 38%;">Kategori Kebutuhan Penyesuaian</th>
                    <th style="width: 12%;">Status</th>
                    <th style="width: 45%;">Bentuk Penyesuaian yang Disepakati / Keterangan</th>
                </tr>
            </thead>
            <tbody>
                @foreach($criteriaDefinitions as $catId => $crit)
                    @php
                        $itemSaved = $savedChecklist[$catId] ?? [];
                        $isPerlu = filter_var($itemSaved['perlu_penyesuaian'] ?? false, FILTER_VALIDATE_BOOLEAN);
                        $opsiSelected = (array) ($itemSaved['opsi_dipilih'] ?? []);
                        $ket = $itemSaved['keterangan'] ?? '';
                    @endphp
                    <tr>
                        <td style="text-align: center; font-weight: 700;">{{ $catId }}</td>
                        <td>
                            <strong>{{ $crit['title'] }}</strong>
                        </td>
                        <td style="text-align: center;">
                            @if($isPerlu)
                                <span style="font-weight: 800; color: #b45309;">PERLU [&#10003;]</span>
                            @else
                                <span style="color: #64748b;">Tidak [ &minus; ]</span>
                            @endif
                        </td>
                        <td>
                            @if($isPerlu)
                                @if(!empty($opsiSelected))
                                    <ul style="margin: 0; padding-left: 1rem; font-size: 8.5pt;">
                                        @foreach($opsiSelected as $opKey)
                                            @if(isset($crit['sub_options'][$opKey]))
                                                <li>{{ $crit['sub_options'][$opKey] }}</li>
                                            @endif
                                        @endforeach
                                    </ul>
                                @endif
                                @if(!empty($ket))
                                    <div style="font-size: 8.5pt; color: #1e293b; margin-top: 3px; font-style: italic;">
                                        Catatan: {{ $ket }}
                                    </div>
                                @endif
                            @else
                                <span style="color: #94a3b8; font-style: italic;">Tidak memerlukan penyesuaian khusus.</span>
                            @endif
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>

        <!-- 3. HASIL REKOMENDASI KESEPAKATAN -->
        <table class="tabel-bnsp" style="margin-bottom: 1rem;">
            <tr>
                <td colspan="2" style="font-weight: 800; background: #f8fafc; text-transform: uppercase;">
                    Rekomendasi Hasil Kesepakatan Penyesuaian Asesmen:
                </td>
            </tr>
            <tr>
                <td style="width: 30%; font-weight: 700;">Acuan Pembanding Disepakati</td>
                <td>{{ $ak07->acuan_pembanding_disepakati ?? "Standar Kompetensi Kerja Nasional Indonesia (SKKNI) {$pendaftaran->skema->nama_skema}" }}</td>
            </tr>
            <tr>
                <td style="font-weight: 700;">Metode Asesmen Disepakati</td>
                <td>{{ $ak07->metode_disepakati ?? 'Observasi Demonstrasi Praktik & Wawancara Terstruktur Klarifikasi' }}</td>
            </tr>
            <tr>
                <td style="font-weight: 700;">Instrumen Pengganti / Penyesuaian</td>
                <td>{{ $ak07->instrumen_disepakati ?? 'FR.IA.01 (Ceklis Observasi), FR.IA.03 (Pertanyaan Pendukung Observasi)' }}</td>
            </tr>
            @if($ak07->catatan_asesor)
                <tr>
                    <td style="font-weight: 700;">Catatan Tambahan Asesor</td>
                    <td>{{ $ak07->catatan_asesor }}</td>
                </tr>
            @endif
        </table>

        <!-- 4. PENGESAHAN TANDA TANGAN ASESOR & ASESI -->
        <table class="tabel-bnsp" style="margin-top: 1rem;">
            <thead>
                <tr>
                    <th style="width: 50%;">Asesi (Kandidat)</th>
                    <th style="width: 50%;">Asesor Penguji</th>
                </tr>
            </thead>
            <tbody>
                <tr>
                    <td style="text-align: center; vertical-align: bottom; padding: 10px;">
                        <div style="font-size: 8pt; color: #64748b; margin-bottom: 5px;">
                            Menyetujui Penyesuaian yang Disepakati:
                        </div>
                        <div class="signature-box">
                            @if($ak07->asesi_signature)
                                <img src="{{ asset($ak07->asesi_signature) }}" alt="TTD Asesi">
                            @else
                                <span style="color: #cbd5e1; font-style: italic; font-size: 8pt;">(Belum Ditandatangani)</span>
                            @endif
                        </div>
                        <div style="font-weight: 700; margin-top: 5px; text-decoration: underline;">
                            {{ $pendaftaran->asesi->nama_lengkap ?? '-' }}
                        </div>
                        <div style="font-size: 7.5pt; color: #475569;">
                            Tanggal: {{ $ak07->asesi_signed_at ? \Carbon\Carbon::parse($ak07->asesi_signed_at)->translatedFormat('d F Y') : '-' }}
                        </div>
                    </td>
                    <td style="text-align: center; vertical-align: bottom; padding: 10px;">
                        <div style="font-size: 8pt; color: #64748b; margin-bottom: 5px;">
                            Mengesahkan Penyesuaian Asesmen:
                        </div>
                        <div class="signature-box">
                            @if($ak07->asesor_signature)
                                <img src="{{ asset($ak07->asesor_signature) }}" alt="TTD Asesor">
                            @else
                                <span style="color: #cbd5e1; font-style: italic; font-size: 8pt;">(Belum Ditandatangani)</span>
                            @endif
                        </div>
                        <div style="font-weight: 700; margin-top: 5px; text-decoration: underline;">
                            {{ $pendaftaran->asesor->nama_lengkap ?? auth()->user()->nama_lengkap }}
                        </div>
                        <div style="font-size: 7.5pt; color: #475569;">
                            No. Reg: {{ $pendaftaran->asesor->nomor_registrasi ?? auth()->user()->nomor_registrasi ?? '-' }}<br>
                            Tanggal: {{ $ak07->asesor_signed_at ? \Carbon\Carbon::parse($ak07->asesor_signed_at)->translatedFormat('d F Y') : '-' }}
                        </div>
                    </td>
                </tr>
            </tbody>
        </table>

        <div style="display: flex; justify-content: space-between; align-items: center; font-size: 7.5pt; color: #64748b; border-top: 1px dashed #cbd5e1; padding-top: 5px; margin-top: 10px;">
            <div>Dokumen Resmi LSP SMKN 1 Gunungputri &bull; FR.AK.07 Penyesuaian yang Wajar</div>
            <div>Status Dokumen: <strong>{{ strtoupper($ak07->status ?? 'DRAFT') }}</strong></div>
        </div>

    </div>

</body>
</html>
