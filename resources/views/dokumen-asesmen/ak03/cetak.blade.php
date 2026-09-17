<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>FR.AK.03 - Umpan Balik Asesi - {{ $pendaftaran->asesi->nama_lengkap ?? 'Peserta' }}</title>
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
        th, td { border: 1px solid #1e293b; padding: 5px 7px; }
        th { background-color: #f1f5f9; font-weight: 800; font-size: 7.5pt; text-transform: uppercase; }
    </style>
</head>
<body class="py-6 print:py-0">

    <!-- NO PRINT ACTION BAR -->
    <div class="no-print max-w-4xl mx-auto mb-4 flex items-center justify-between gap-3 px-4">
        <div class="text-xs text-slate-600 font-semibold">
            Pratinjau Format Cetak Dokumen Resmi BNSP (FR.AK.03)
        </div>
        <div class="flex items-center gap-2">
            <button onclick="window.print()" class="px-4 py-2 bg-blue-600 hover:bg-blue-700 text-white rounded-xl font-bold text-xs shadow-md transition-colors flex items-center gap-1.5 cursor-pointer">
                <span>Cetak / Simpan PDF</span>
            </button>
            <button onclick="window.close()" class="px-3 py-2 bg-white border border-slate-200 text-slate-700 rounded-xl font-semibold text-xs hover:bg-slate-50 transition-colors cursor-pointer">
                Tutup
            </button>
        </div>
    </div>

    <!-- SHEET A4 -->
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
                <div style="font-size: 10pt; font-weight: 900;">FR.AK.03</div>
                <div style="font-size: 6pt; font-weight: 800; text-transform: uppercase;">STANDAR BNSP</div>
            </div>
        </div>

        <div style="text-align: center; margin-bottom: 12px;">
            <h1 style="font-size: 10.5pt; font-weight: 900; text-transform: uppercase; letter-spacing: 0.03em;">
                FR.AK.03 — UMPAN BALIK DAN CATATAN ASESMEN
            </h1>
        </div>

        <!-- INFORMASI KONTEKS -->
        <table style="margin-bottom: 12px; font-size: 8pt;">
            <tr>
                <td style="width: 25%; font-weight: bold; background: #f8fafc;">Skema Sertifikasi</td>
                <td style="width: 75%; font-weight: bold;">
                    {{ $pendaftaran->skema->nama_skema ?? '-' }} ({{ $pendaftaran->skema->kode_skema ?? '-' }})
                </td>
            </tr>
            <tr>
                <td style="font-weight: bold; background: #f8fafc;">Tempat Uji Kompetensi (TUK)</td>
                <td>{{ $pendaftaran->jadwal->nama_tuk ?? 'TUK Mandiri SMKN 1 Gunungputri' }}</td>
            </tr>
            <tr>
                <td style="font-weight: bold; background: #f8fafc;">Nama Asesor</td>
                <td>{{ $pendaftaran->asesor->nama_lengkap ?? '-' }}</td>
            </tr>
            <tr>
                <td style="font-weight: bold; background: #f8fafc;">Nama Asesi</td>
                <td>{{ $pendaftaran->asesi->nama_lengkap ?? '-' }} (No. Reg: {{ $pendaftaran->nomor_pendaftaran }})</td>
            </tr>
            <tr>
                <td style="font-weight: bold; background: #f8fafc;">Tanggal Pengisian</td>
                <td>{{ $ak03->tanggal_ttd_asesi ? $ak03->tanggal_ttd_asesi->format('d/m/Y') : date('d/m/Y') }}</td>
            </tr>
        </table>

        <!-- KUESIONER 10 BUTIR -->
        @php
            $jawaban = (array) ($ak03->jawaban_kuesioner ?? []);
        @endphp
        <table style="margin-bottom: 12px; font-size: 7.5pt;">
            <thead>
                <tr>
                    <th style="width: 20px; text-align: center;">No</th>
                    <th>Komponen / Pernyataan Umpan Balik Asesmen</th>
                    <th style="width: 32px; text-align: center;">Ya</th>
                    <th style="width: 32px; text-align: center;">Tidak</th>
                    <th style="width: 140px;">Catatan / Komentar Asesi</th>
                </tr>
            </thead>
            <tbody>
                @foreach(\App\Models\AssessmentAk03::FEEDBACK_QUESTIONS as $no => $pertanyaan)
                    @php
                        $ans = $jawaban[$no] ?? [];
                        $isYa = strtolower($ans['jawaban'] ?? 'ya') === 'ya';
                    @endphp
                    <tr>
                        <td style="text-align: center; font-weight: bold;">{{ $no }}</td>
                        <td>{{ $pertanyaan }}</td>
                        <td style="text-align: center; font-weight: bold;">{{ $isYa ? '✓' : '' }}</td>
                        <td style="text-align: center; font-weight: bold;">{{ !$isYa ? '✓' : '' }}</td>
                        <td>{{ $ans['catatan'] ?? '-' }}</td>
                    </tr>
                @endforeach
            </tbody>
        </table>

        @if($ak03->catatan_lainnya)
            <table style="margin-bottom: 12px; font-size: 8pt;">
                <tr>
                    <td style="width: 25%; font-weight: bold; background: #f8fafc; vertical-align: top;">Catatan / Komentar Lainnya</td>
                    <td>{{ $ak03->catatan_lainnya }}</td>
                </tr>
            </table>
        @endif

        <!-- TANDA TANGAN ASESI -->
        <table style="margin-top: 20px; font-size: 8pt; width: 45%; margin-left: auto;">
            <tr>
                <td style="font-weight: bold; background: #f8fafc; text-align: center; padding: 6px;">Tanda Tangan Asesi (Peserta)</td>
            </tr>
            <tr>
                <td style="height: 75px; vertical-align: bottom; text-align: center; padding-bottom: 8px;">
                    @if($ak03->tanda_tangan_asesi)
                        <img src="{{ $ak03->tanda_tangan_asesi }}" alt="TTD Asesi" style="max-height: 55px; margin: 0 auto; display: block;">
                    @endif
                    <div style="font-weight: bold; text-decoration: underline;">
                        {{ $pendaftaran->asesi->nama_lengkap ?? 'Peserta' }}
                    </div>
                    <div style="font-size: 7pt; color: #475569;">
                        Tanggal: {{ $ak03->tanggal_ttd_asesi ? $ak03->tanggal_ttd_asesi->format('d/m/Y H:i') : date('d/m/Y') }}
                    </div>
                </td>
            </tr>
        </table>

        <div style="margin-top: 25px; padding-top: 6px; border-top: 1px dashed #cbd5e1; display: flex; justify-content: space-between; font-size: 6.5pt; color: #64748b;">
            <div>Dokumen Resmi LSP SMKN 1 Gunungputri &bull; FR.AK.03 v{{ $ak03->version }}</div>
            <div>Status: {{ strtoupper($ak03->status) }} &bull; Dicetak: {{ date('d/m/Y H:i:s') }}</div>
        </div>
    </div>

</body>
</html>
