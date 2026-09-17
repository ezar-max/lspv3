<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>FR.AK.06 - Meninjau Proses Asesmen - {{ $ak06->nomor_review }}</title>
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
            font-size: 8.5pt;
            color: #0f172a;
            line-height: 1.35;
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
            body { background: #ffffff !important; font-size: 8pt !important; }
            .page-sheet { width: 100% !important; min-height: auto !important; margin: 0 !important; padding: 0 !important; box-shadow: none !important; }
            .no-print { display: none !important; }
        }
        table { border-collapse: collapse; width: 100%; }
        th, td { border: 1px solid #1e293b; padding: 4px 6px; }
        th { background-color: #f1f5f9; font-weight: 800; font-size: 7.5pt; text-transform: uppercase; }
    </style>
</head>
<body class="py-6 print:py-0">

    <div class="no-print max-w-4xl mx-auto mb-4 flex items-center justify-between gap-3 px-4">
        <div class="text-xs text-slate-600 font-semibold">
            Pratinjau Format Cetak Dokumen Resmi BNSP (FR.AK.06)
        </div>
        <div class="flex items-center gap-2">
            <button onclick="window.print()" class="px-4 py-2 bg-blue-600 hover:bg-blue-700 text-white rounded-xl font-bold text-xs shadow-md transition-colors cursor-pointer">
                <span>Cetak / Simpan PDF</span>
            </button>
            <button onclick="window.close()" class="px-3 py-2 bg-white border border-slate-200 text-slate-700 rounded-xl font-semibold text-xs hover:bg-slate-50 transition-colors cursor-pointer">
                Tutup
            </button>
        </div>
    </div>

    <div class="page-sheet">
        <!-- KOP RESMI BNSP -->
        <div style="display: flex; align-items: center; justify-content: space-between; padding-bottom: 8px; margin-bottom: 12px; border-bottom: 2px solid #0f172a; gap: 12px;">
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
                <div style="font-size: 10pt; font-weight: 900;">FR.AK.06</div>
                <div style="font-size: 6pt; font-weight: 800; text-transform: uppercase;">STANDAR BNSP</div>
            </div>
        </div>

        <div style="text-align: center; margin-bottom: 12px;">
            <h1 style="font-size: 10.5pt; font-weight: 900; text-transform: uppercase; letter-spacing: 0.03em;">
                FR.AK.06 — MENINJAU PROSES ASESMEN
            </h1>
            <div style="font-size: 7.5pt; color: #475569; font-mono;">No. Dokumen: {{ $ak06->nomor_review }}</div>
        </div>

        <!-- INFORMASI KONTEKS -->
        <table style="margin-bottom: 12px; font-size: 8pt;">
            <tr>
                <td style="width: 25%; font-weight: bold; background: #f8fafc;">Skema Sertifikasi</td>
                <td style="width: 75%; font-weight: bold;">
                    {{ $ak06->skema->nama_skema ?? '-' }} ({{ $ak06->skema->kode_skema ?? '-' }})
                </td>
            </tr>
            <tr>
                <td style="font-weight: bold; background: #f8fafc;">Lingkup Peninjauan</td>
                <td>
                    <span style="font-weight: bold; text-transform: capitalize;">{{ $ak06->scope_type }}</span>
                    @if($ak06->scope_type === 'individual' && $ak06->pendaftaran)
                        - Asesi: {{ $ak06->pendaftaran->asesi->nama_lengkap ?? '-' }} (No. Reg: {{ $ak06->pendaftaran->nomor_pendaftaran }})
                    @elseif($ak06->scope_type === 'kelompok' && $ak06->jadwal)
                        - Sesi: {{ $ak06->jadwal->kode_jadwal ?? '-' }}
                    @endif
                </td>
            </tr>
            <tr>
                <td style="font-weight: bold; background: #f8fafc;">Tempat Uji Kompetensi (TUK)</td>
                <td>{{ $ak06->jadwal ? $ak06->jadwal->nama_tuk : 'TUK Mandiri SMKN 1 Gunungputri' }}</td>
            </tr>
            <tr>
                <td style="font-weight: bold; background: #f8fafc;">Reviewer / Lead Asesor</td>
                <td>
                    {{ $ak06->asesor->nama_lengkap ?? '-' }} &bull; No. Reg: {{ $ak06->asesor->nomor_registrasi ?? 'MET.000.005566' }}
                </td>
            </tr>
            <tr>
                <td style="font-weight: bold; background: #f8fafc;">Tanggal Peninjauan</td>
                <td>{{ $ak06->tanggal_review ? $ak06->tanggal_review->translatedFormat('d F Y') : date('d F Y') }}</td>
            </tr>
        </table>

        <!-- BAGIAN A: PROSEDUR ASESMEN & 4 PRINSIP -->
        <div style="margin-top: 10px; margin-bottom: 4px; font-weight: 900; font-size: 8pt; text-transform: uppercase; background: #e2e8f0; padding: 3px 6px;">
            A. Peninjauan Prosedur Asesmen Terhadap 4 Prinsip Asesmen
        </div>
        <table style="margin-bottom: 12px; font-size: 7.5pt;">
            <thead>
                <tr>
                    <th style="width: 25px; text-align: center;">No</th>
                    <th>Aspek / Prosedur Asesmen</th>
                    <th style="width: 50px; text-align: center;">Valid</th>
                    <th style="width: 55px; text-align: center;">Reliabel</th>
                    <th style="width: 55px; text-align: center;">Fleksibel</th>
                    <th style="width: 50px; text-align: center;">Adil</th>
                    <th>Catatan Reviewer</th>
                </tr>
            </thead>
            <tbody>
                @php 
                    $no = 1;
                    $matrix = (array) ($ak06->prosedur_matrix ?? []);
                @endphp
                @foreach(App\Models\AssessmentAk06::PROCEDURES as $key => $title)
                    @php
                        $r = $matrix[$key] ?? [];
                    @endphp
                    <tr>
                        <td style="text-align: center;">{{ $no++ }}</td>
                        <td style="font-weight: bold;">{{ $title }}</td>
                        <td style="text-align: center; font-weight: bold;">
                            {{ ($r['valid'] ?? '') === 'sesuai' ? 'S' : 'TS' }}
                        </td>
                        <td style="text-align: center; font-weight: bold;">
                            {{ ($r['reliabel'] ?? '') === 'sesuai' ? 'S' : 'TS' }}
                        </td>
                        <td style="text-align: center; font-weight: bold;">
                            {{ ($r['fleksibel'] ?? '') === 'sesuai' ? 'S' : 'TS' }}
                        </td>
                        <td style="text-align: center; font-weight: bold;">
                            {{ ($r['adil'] ?? '') === 'sesuai' ? 'S' : 'TS' }}
                        </td>
                        <td>{{ $r['catatan'] ?? '-' }}</td>
                    </tr>
                @endforeach
            </tbody>
        </table>
        <div style="font-size: 6.5pt; color: #475569; margin-top: -8px; margin-bottom: 10px;">
            * Keterangan: S = Sesuai; TS = Tidak Sesuai
        </div>

        <!-- BAGIAN B: KONSISTENSI 5 DIMENSI KOMPETENSI -->
        <div style="margin-top: 10px; margin-bottom: 4px; font-weight: 900; font-size: 8pt; text-transform: uppercase; background: #e2e8f0; padding: 3px 6px;">
            B. Konsistensi Pemberian Bukti Asesmen (5 Dimensi Kompetensi)
        </div>
        <table style="margin-bottom: 12px; font-size: 7.5pt;">
            <thead>
                <tr>
                    <th style="width: 25px; text-align: center;">No</th>
                    <th style="width: 140px;">Dimensi Kompetensi</th>
                    <th>Bukti yang Digunakan</th>
                    <th style="width: 110px;">Instrumen Asesmen</th>
                    <th>Catatan / Konsistensi</th>
                </tr>
            </thead>
            <tbody>
                @php 
                    $noD = 1;
                    $dimensi = (array) ($ak06->dimensi_kompetensi ?? []);
                @endphp
                @foreach(App\Models\AssessmentAk06::COMPETENCY_DIMENSIONS as $dimKey => $dimTitle)
                    @php
                        $d = $dimensi[$dimKey] ?? [];
                    @endphp
                    <tr>
                        <td style="text-align: center;">{{ $noD++ }}</td>
                        <td style="font-weight: bold;">{{ $dimTitle }}</td>
                        <td>{{ $d['bukti'] ?? '-' }}</td>
                        <td>{{ $d['instrumen'] ?? '-' }}</td>
                        <td>{{ $d['catatan'] ?? '-' }}</td>
                    </tr>
                @endforeach
            </tbody>
        </table>

        <!-- BAGIAN C: TEMUAN & REKOMENDASI PENINGKATAN -->
        <div style="margin-top: 10px; margin-bottom: 4px; font-weight: 900; font-size: 8pt; text-transform: uppercase; background: #e2e8f0; padding: 3px 6px;">
            C. Temuan & Rekomendasi Peningkatan Proses Asesmen
        </div>
        <table style="margin-bottom: 12px; font-size: 7.5pt;">
            <thead>
                <tr>
                    <th style="width: 25px; text-align: center;">No</th>
                    <th>Temuan / Gap</th>
                    <th>Rekomendasi Tindakan</th>
                    <th style="width: 110px;">Penanggung Jawab</th>
                    <th style="width: 75px; text-align: center;">Target Waktu</th>
                    <th style="width: 65px; text-align: center;">Status</th>
                </tr>
            </thead>
            <tbody>
                @forelse((array) ($ak06->rekomendasi_peningkatan ?? []) as $idx => $rek)
                    <tr>
                        <td style="text-align: center;">{{ $idx + 1 }}</td>
                        <td>{{ $rek['temuan'] ?? '-' }}</td>
                        <td>{{ $rek['rekomendasi'] ?? '-' }}</td>
                        <td>{{ $rek['penanggung_jawab'] ?? '-' }}</td>
                        <td style="text-align: center;">{{ $rek['target_tanggal'] ?? '-' }}</td>
                        <td style="text-align: center; font-weight: bold;">{{ $rek['status'] ?? 'Belum Dimulai' }}</td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="6" style="text-align: center; color: #64748b; padding: 6px;">
                            Tidak ada temuan atau rekomendasi peningkatan khusus.
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>

        <!-- BAGIAN D: KOMENTAR & TANDA TANGAN -->
        <table style="margin-bottom: 16px; font-size: 8pt;">
            <tr>
                <td style="font-weight: bold; background: #f8fafc;">Komentar / Kesimpulan Umum Reviewer:</td>
            </tr>
            <tr>
                <td style="height: 38px; vertical-align: top;">
                    {{ $ak06->komentar_reviewer ?: 'Proses asesmen telah ditinjau dan dinyatakan memenuhi seluruh prinsip serta dimensi kompetensi BNSP.' }}
                </td>
            </tr>
        </table>

        <!-- BLOK TANDA TANGAN -->
        <div style="margin-top: 15px; display: flex; justify-content: flex-end;">
            <div style="width: 240px; text-align: center; font-size: 8pt;">
                <div>Gunungputri, {{ $ak06->tanggal_review ? $ak06->tanggal_review->translatedFormat('d F Y') : date('d F Y') }}</div>
                <div style="font-weight: bold; margin-top: 2px;">Reviewer / Lead Asesor,</div>
                <div style="height: 65px; display: flex; align-items: center; justify-content: center; margin: 4px 0;">
                    @if($ak06->tanda_tangan_reviewer)
                        <img src="{{ $ak06->tanda_tangan_reviewer }}" alt="Tanda Tangan Reviewer" style="max-height: 55px; max-width: 180px; object-fit: contain;">
                    @else
                        <div style="border-bottom: 1px dotted #94a3b8; width: 140px; height: 40px;"></div>
                    @endif
                </div>
                <div style="font-weight: 800; text-decoration: underline;">{{ $ak06->asesor->nama_lengkap ?? '....................................' }}</div>
                <div style="font-size: 7.5pt; color: #475569;">No. Reg: {{ $ak06->asesor->nomor_registrasi ?? 'MET.000.005566' }}</div>
            </div>
        </div>

        <div style="margin-top: 25px; padding-top: 6px; border-top: 1px dashed #cbd5e1; display: flex; justify-content: space-between; font-size: 6.5pt; color: #94a3b8;">
            <span>Sistem Informasi LSP-P1 SMKN 1 Gunungputri &bull; FR.AK.06 v{{ $ak06->version }}</span>
            <span>Dicetak secara otomatis pada {{ now()->format('d/m/Y H:i') }}</span>
        </div>
    </div>

</body>
</html>
