<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>FR.VA - Memberikan Kontribusi dalam Validasi Asesmen - {{ $va->nomor_validasi }}</title>
    <link rel="icon" type="image/jpeg" href="{{ asset('images/logo-lsp.jpeg') }}">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Public+Sans:wght@400;600;700;800;900&display=swap" rel="stylesheet">
    <script src="https://cdn.tailwindcss.com"></script>

    <style>
        @page {
            size: A4 portrait;
            margin: 12mm 15mm 15mm 15mm;
        }
        body {
            font-family: 'Public Sans', sans-serif;
            font-size: 8pt;
            color: #0f172a;
            line-height: 1.3;
            background: #f8fafc;
        }
        .page-sheet {
            background: #ffffff;
            width: 210mm;
            min-height: 297mm;
            margin: 0 auto;
            padding: 12mm 15mm;
            box-shadow: 0 4px 6px -1px rgb(0 0 0 / 0.1);
        }
        @media print {
            body { background: #ffffff !important; font-size: 7.5pt !important; }
            .page-sheet { width: 100% !important; min-height: auto !important; margin: 0 !important; padding: 0 !important; box-shadow: none !important; }
            .no-print { display: none !important; }
        }
        table { border-collapse: collapse; width: 100%; }
        th, td { border: 1px solid #1e293b; padding: 3.5px 5px; }
        th { background-color: #f1f5f9; font-weight: 800; font-size: 7pt; text-transform: uppercase; }
        .section-header {
            margin-top: 10px;
            margin-bottom: 4px;
            font-weight: 900;
            font-size: 7.5pt;
            text-transform: uppercase;
            background: #e2e8f0;
            padding: 3px 6px;
        }
    </style>
</head>
<body class="py-6 print:py-0">

    <div class="no-print max-w-4xl mx-auto mb-4 flex items-center justify-between gap-3 px-4">
        <div class="text-xs text-slate-600 font-semibold">
            Pratinjau Format Cetak Dokumen Resmi BNSP (FR.VA)
        </div>
        <div class="flex items-center gap-2">
            <button onclick="window.print()" class="px-4 py-2 bg-teal-600 hover:bg-teal-700 text-white rounded-xl font-bold text-xs shadow-md transition-colors cursor-pointer">
                <span>Cetak / Simpan PDF</span>
            </button>
            <button onclick="window.close()" class="px-3 py-2 bg-white border border-slate-200 text-slate-700 rounded-xl font-semibold text-xs hover:bg-slate-50 transition-colors cursor-pointer">
                Tutup
            </button>
        </div>
    </div>

    <div class="page-sheet">
        <!-- KOP RESMI BNSP -->
        <div style="display: flex; align-items: center; justify-content: space-between; padding-bottom: 8px; margin-bottom: 10px; border-bottom: 2px solid #0f172a; gap: 12px;">
            <div style="display: flex; align-items: center; gap: 10px; flex: 1;">
                <div style="width: 44px; height: 44px; border: 1px solid #94a3b8; padding: 2px; display: flex; align-items: center; justify-content: center;">
                    <img src="{{ asset('images/logo-lsp.jpeg') }}" alt="Logo LSP" style="max-height: 40px; max-width: 40px; object-fit: contain;">
                </div>
                <div>
                    <div style="font-size: 9pt; font-weight: 900; text-transform: uppercase; line-height: 1.15;">
                        LSP-P1 SMKN 1 GUNUNGPUTRI
                    </div>
                    <div style="font-size: 7pt; font-weight: 700; color: #1e40af; margin-top: 1px;">
                        LISENSI RESMI BNSP: BNSP-LSP-2629-ID &bull; SK: KEP.1215/BNSP/V/2025
                    </div>
                    <div style="font-size: 6.5pt; color: #475569; margin-top: 1px;">
                        Jl. Barokah No. 6, Wanaherang, Kec. Gunungputri, Kab. Bogor, Jawa Barat
                    </div>
                </div>
            </div>
            <div style="border: 2px solid #0f172a; padding: 4px 12px; text-align: center; background: #f8fafc;">
                <div style="font-size: 10pt; font-weight: 900;">FR.VA</div>
                <div style="font-size: 6pt; font-weight: 800; text-transform: uppercase;">STANDAR BNSP</div>
            </div>
        </div>

        <div style="text-align: center; margin-bottom: 10px;">
            <h1 style="font-size: 10pt; font-weight: 900; text-transform: uppercase; letter-spacing: 0.03em;">
                FR.VA — MEMBERIKAN KONTRIBUSI DALAM VALIDASI ASESMEN
            </h1>
            <div style="font-size: 7pt; color: #475569; font-mono;">No. Dokumen: {{ $va->nomor_validasi }}</div>
        </div>

        <!-- 1. INFORMASI VALIDASI -->
        <table style="margin-bottom: 8px; font-size: 7.5pt;">
            <tr>
                <td style="width: 25%; font-weight: bold; background: #f8fafc;">Skema Sertifikasi</td>
                <td style="width: 75%; font-weight: bold;">
                    {{ $va->skema->nama_skema ?? '-' }} ({{ $va->skema->kode_skema ?? '-' }})
                </td>
            </tr>
            <tr>
                <td style="font-weight: bold; background: #f8fafc;">Ketua Tim / Lead Asesor</td>
                <td>
                    {{ $va->leadAsesor->nama_lengkap ?? '-' }} &bull; No. Reg: {{ $va->leadAsesor->nomor_registrasi ?? 'MET.000.003344' }}
                </td>
            </tr>
            <tr>
                <td style="font-weight: bold; background: #f8fafc;">Tanggal & Tempat Validasi</td>
                <td>
                    {{ $va->tanggal_validasi ? $va->tanggal_validasi->translatedFormat('d F Y') : date('d F Y') }}
                    di {{ $va->tempat_validasi ?: 'TUK Mandiri SMKN 1 Gunungputri' }}
                </td>
            </tr>
            <tr>
                <td style="font-weight: bold; background: #f8fafc;">Periode Validasi</td>
                <td>
                    @php
                        $pv = (array) ($va->periode_validasi ?? []);
                        $pvLabels = [];
                        if (in_array('sebelum_asesmen', $pv)) $pvLabels[] = '[V] Sebelum Asesmen';
                        if (in_array('pada_saat_asesmen', $pv)) $pvLabels[] = '[V] Pada Saat Asesmen';
                        if (in_array('setelah_asesmen', $pv)) $pvLabels[] = '[V] Setelah Asesmen';
                    @endphp
                    {{ count($pvLabels) ? implode('  &bull;  ', $pvLabels) : '-' }}
                </td>
            </tr>
        </table>

        <!-- 2. MENYIAPKAN PROSES VALIDASI -->
        <div class="section-header">
            1. Menyiapkan Proses Validasi & Orang yang Relevan
        </div>
        <table style="margin-bottom: 8px; font-size: 7.5pt;">
            <tr>
                <td style="width: 25%; font-weight: bold; background: #f8fafc;">Tujuan / Fokus Validasi</td>
                <td>
                    @php
                        $tf = (array) ($va->tujuan_fokus ?? []);
                        $tfLabels = [];
                        if (!empty($tf['penjaminan_mutu'])) $tfLabels[] = 'Penjaminan mutu pelaksanaan asesmen';
                        if (!empty($tf['mengantisipasi_risiko'])) $tfLabels[] = 'Mengantisipasi risiko ketidaksesuaian';
                        if (!empty($tf['memenuhi_bnsp'])) $tfLabels[] = 'Memenuhi regulasi BNSP';
                        if (!empty($tf['kesesuaian_bukti'])) $tfLabels[] = 'Kesesuaian bukti & keputusan K/BK';
                    @endphp
                    {{ count($tfLabels) ? implode(', ', $tfLabels) : 'Penjaminan mutu asesmen' }}
                </td>
            </tr>
            <tr>
                <td style="font-weight: bold; background: #f8fafc;">Konteks & Pendekatan</td>
                <td>
                    Internal LSP-P1 bersama kolega asesor sejawat melalui panel kaji ulang perangkat & sampel bukti asesmen.
                </td>
            </tr>
        </table>

        <!-- PESERTA VALIDASI TABLE -->
        <table style="margin-bottom: 8px; font-size: 7pt;">
            <thead>
                <tr>
                    <th style="width: 20px; text-align: center;">No</th>
                    <th style="width: 130px;">Peran Peserta</th>
                    <th style="width: 140px;">Nama Peserta</th>
                    <th>Hasil Konfirmasi</th>
                    <th>Tujuan Keterlibatan</th>
                </tr>
            </thead>
            <tbody>
                @forelse((array) ($va->peserta_relevan ?? []) as $pIdx => $p)
                    <tr>
                        <td style="text-align: center;">{{ $pIdx + 1 }}</td>
                        <td style="font-weight: bold;">{{ App\Models\AssessmentVa::PARTICIPANT_ROLES[$p['peran'] ?? ''] ?? ($p['peran'] ?? '-') }}</td>
                        <td>{{ $p['nama'] ?? '-' }}</td>
                        <td>{{ $p['hasil_konfirmasi'] ?? '-' }}</td>
                        <td>{{ $p['tujuan'] ?? '-' }}</td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="5" style="text-align: center; color: #64748b;">Tidak ada data peserta tercatat.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>

        <!-- 3. ACUAN PEMBANDING -->
        <div class="section-header">
            2. Acuan Pembanding & Dokumen Terkait
        </div>
        <table style="margin-bottom: 8px; font-size: 7.5pt;">
            <tr>
                <td style="width: 25%; font-weight: bold; background: #f8fafc;">Acuan Pembanding</td>
                <td>
                    Standar Kompetensi Kerja Nasional Indonesia (SKKNI), Skema Sertifikasi LSP, SOP/Instruksi Kerja Industri & Sekolah, Standar K3.
                </td>
            </tr>
            <tr>
                <td style="font-weight: bold; background: #f8fafc;">Dokumen Terkait</td>
                <td>
                    Perangkat Asesmen (FR.IA.01, 02, 03, 07), Pedoman Mutu BNSP, Sampel Berkas Portofolio Peserta.
                </td>
            </tr>
        </table>

        <!-- 4. KONTRIBUSI KOMUNIKASI -->
        <div class="section-header">
            3. Kontribusi dalam Validasi
        </div>
        <table style="margin-bottom: 8px; font-size: 7.5pt;">
            <tr>
                <td style="width: 25%; font-weight: bold; background: #f8fafc;">Keterampilan Komunikasi</td>
                <td>Proaktif, active listening, empati profesional, dan negosiasi pencapaian kesepakatan mutu.</td>
            </tr>
            <tr>
                <td style="font-weight: bold; background: #f8fafc;">Catatan Kontribusi</td>
                <td>{{ $va->catatan_kontribusi ?: 'Memberikan masukan konstruktif atas validitas instrumen dan keselarasan bukti unjuk kerja.' }}</td>
            </tr>
        </table>

        <!-- 5. MATRIKS 8 ASPEK VALIDASI -->
        <div class="section-header">
            4. Matriks Penilaian 8 Aspek Kegiatan Validasi
        </div>
        <table style="margin-bottom: 8px; font-size: 7pt;">
            <thead>
                <tr>
                    <th style="width: 20px; text-align: center;">No</th>
                    <th style="width: 170px;">Aspek Kegiatan Validasi</th>
                    <th style="width: 70px; text-align: center;">Aturan Bukti (VATM)</th>
                    <th style="width: 70px; text-align: center;">Prinsip (VRFA)</th>
                    <th>Catatan / Temuan Aspek</th>
                </tr>
            </thead>
            <tbody>
                @php 
                    $matriks = (array) ($va->matriks_penilaian ?? []);
                @endphp
                @foreach(App\Models\AssessmentVa::VALIDATION_ASPECTS as $no => $aspek)
                    @php
                        $m = $matriks[$no] ?? [];
                        $vatm = $m['vatm'] ?? [];
                        $vrfa = $m['vrfa'] ?? [];

                        $vatmStr = (!empty($vatm['v'])?'V':'_') . (!empty($vatm['a'])?'A':'_') . (!empty($vatm['t'])?'T':'_') . (!empty($vatm['m'])?'M':'_');
                        $vrfaStr = (!empty($vrfa['v'])?'V':'_') . (!empty($vrfa['r'])?'R':'_') . (!empty($vrfa['f'])?'F':'_') . (!empty($vrfa['a'])?'A':'_');
                    @endphp
                    <tr>
                        <td style="text-align: center;">{{ $no }}</td>
                        <td style="font-weight: bold;">{{ $aspek }}</td>
                        <td style="text-align: center; font-family: monospace; font-weight: bold;">{{ $vatmStr }}</td>
                        <td style="text-align: center; font-family: monospace; font-weight: bold;">{{ $vrfaStr }}</td>
                        <td>{{ $m['catatan'] ?? '-' }}</td>
                    </tr>
                @endforeach
            </tbody>
        </table>
        <div style="font-size: 6.5pt; color: #475569; margin-top: -6px; margin-bottom: 6px;">
            * VATM = Valid, Asli, Terkini, Memadai &bull; VRFA = Valid, Reliabel, Fleksibel, Adil
        </div>

        <!-- 6. TEMUAN VALIDASI -->
        <div class="section-header">
            5. Hasil Validasi & Temuan
        </div>
        <table style="margin-bottom: 8px; font-size: 7pt;">
            <thead>
                <tr>
                    <th style="width: 20px; text-align: center;">No</th>
                    <th>Uraian Temuan / Gap</th>
                    <th style="width: 100px;">Kategori</th>
                    <th style="width: 60px; text-align: center;">Prioritas</th>
                    <th>Rekomendasi Tindakan</th>
                </tr>
            </thead>
            <tbody>
                @forelse((array) ($va->temuan_validasi ?? []) as $tIdx => $t)
                    <tr>
                        <td style="text-align: center;">{{ $tIdx + 1 }}</td>
                        <td>{{ $t['temuan'] ?? '-' }}</td>
                        <td>{{ $t['kategori'] ?? '-' }}</td>
                        <td style="text-align: center; font-weight: bold;">{{ $t['prioritas'] ?? 'Sedang' }}</td>
                        <td>{{ $t['rekomendasi'] ?? '-' }}</td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="5" style="text-align: center; color: #64748b;">Tidak ada temuan gap validasi tercatat.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>

        <!-- 7. RENCANA PERBAIKAN -->
        <div class="section-header">
            6. Rencana Implementasi Perbaikan Sesuai Rekomendasi
        </div>
        <table style="margin-bottom: 12px; font-size: 7pt;">
            <thead>
                <tr>
                    <th style="width: 20px; text-align: center;">No</th>
                    <th>Kegiatan Perbaikan Sesuai Rekomendasi</th>
                    <th style="width: 75px; text-align: center;">Waktu</th>
                    <th style="width: 120px;">Penanggung Jawab</th>
                    <th style="width: 65px; text-align: center;">Status</th>
                </tr>
            </thead>
            <tbody>
                @forelse((array) ($va->rencana_perbaikan ?? []) as $rIdx => $r)
                    <tr>
                        <td style="text-align: center;">{{ $rIdx + 1 }}</td>
                        <td>{{ $r['kegiatan'] ?? '-' }}</td>
                        <td style="text-align: center;">{{ $r['waktu'] ?? '-' }}</td>
                        <td>{{ $r['penanggung_jawab'] ?? '-' }}</td>
                        <td style="text-align: center; font-weight: bold;">{{ $r['status'] ?? 'Belum Dimulai' }}</td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="5" style="text-align: center; color: #64748b;">Belum ada rencana perbaikan tercatat.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>

        <!-- BLOK TANDA TANGAN -->
        <div style="margin-top: 15px; display: flex; justify-content: flex-end;">
            <div style="width: 240px; text-align: center; font-size: 8pt;">
                <div>Gunungputri, {{ $va->tanggal_validasi ? $va->tanggal_validasi->translatedFormat('d F Y') : date('d F Y') }}</div>
                <div style="font-weight: bold; margin-top: 2px;">Ketua Tim / Lead Asesor,</div>
                <div style="height: 60px; display: flex; align-items: center; justify-content: center; margin: 4px 0;">
                    @if($va->tanda_tangan_lead)
                        <img src="{{ $va->tanda_tangan_lead }}" alt="Tanda Tangan Lead Asesor" style="max-height: 52px; max-width: 180px; object-fit: contain;">
                    @else
                        <div style="border-bottom: 1px dotted #94a3b8; width: 140px; height: 35px;"></div>
                    @endif
                </div>
                <div style="font-weight: 800; text-decoration: underline;">{{ $va->leadAsesor->nama_lengkap ?? '....................................' }}</div>
                <div style="font-size: 7.5pt; color: #475569;">No. Reg: {{ $va->leadAsesor->nomor_registrasi ?? 'MET.000.003344' }}</div>
            </div>
        </div>

        <div style="margin-top: 20px; padding-top: 6px; border-top: 1px dashed #cbd5e1; display: flex; justify-content: space-between; font-size: 6.5pt; color: #94a3b8;">
            <span>Sistem Informasi LSP-P1 SMKN 1 Gunungputri &bull; FR.VA v{{ $va->version }}</span>
            <span>Dicetak secara otomatis pada {{ now()->format('d/m/Y H:i') }}</span>
        </div>
    </div>

</body>
</html>
