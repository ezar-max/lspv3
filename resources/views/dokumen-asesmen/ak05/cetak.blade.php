<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>FR.AK.05 - Laporan Asesmen - {{ $ak05->nomor_laporan }}</title>
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
            Pratinjau Format Cetak Dokumen Resmi BNSP (FR.AK.05)
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
                <div style="font-size: 10pt; font-weight: 900;">FR.AK.05</div>
                <div style="font-size: 6pt; font-weight: 800; text-transform: uppercase;">STANDAR BNSP</div>
            </div>
        </div>

        <div style="text-align: center; margin-bottom: 12px;">
            <h1 style="font-size: 10.5pt; font-weight: 900; text-transform: uppercase; letter-spacing: 0.03em;">
                FR.AK.05 — LAPORAN ASESMEN
            </h1>
            <div style="font-size: 7.5pt; color: #475569; font-mono;">No. Laporan: {{ $ak05->nomor_laporan }}</div>
        </div>

        <!-- INFORMASI KONTEKS -->
        <table style="margin-bottom: 12px; font-size: 8pt;">
            <tr>
                <td style="width: 25%; font-weight: bold; background: #f8fafc;">Skema Sertifikasi</td>
                <td style="width: 75%; font-weight: bold;">
                    {{ $ak05->skema->nama_skema ?? '-' }} ({{ $ak05->skema->kode_skema ?? '-' }})
                </td>
            </tr>
            <tr>
                <td style="font-weight: bold; background: #f8fafc;">Tempat Uji Kompetensi (TUK)</td>
                <td>{{ $ak05->jadwal ? $ak05->jadwal->nama_tuk : 'TUK Mandiri SMKN 1 Gunungputri' }}</td>
            </tr>
            <tr>
                <td style="font-weight: bold; background: #f8fafc;">Nama Asesor</td>
                <td>{{ $ak05->asesor->nama_lengkap ?? '-' }} (No. Reg: {{ $ak05->asesor->nomor_registrasi ?? 'MET.000.004455' }})</td>
            </tr>
            <tr>
                <td style="font-weight: bold; background: #f8fafc;">Tanggal Pelaksanaan Laporan</td>
                <td>{{ $ak05->tanggal_laporan ? $ak05->tanggal_laporan->format('d/m/Y') : date('d/m/Y') }}</td>
            </tr>
        </table>

        <!-- HASIL ASESMEN PESERTA -->
        @php
            $rekap = (array) ($ak05->rekap_asesi ?? []);
        @endphp
        <div style="font-weight: bold; font-size: 8pt; margin-bottom: 4px; text-transform: uppercase;">
            I. Daftar Hasil Asesmen Peserta
        </div>
        <table style="margin-bottom: 12px; font-size: 7.5pt;">
            <thead>
                <tr>
                    <th style="width: 24px; text-align: center;">No</th>
                    <th>Nama Asesi</th>
                    <th style="width: 100px;">No. Registrasi</th>
                    <th style="width: 32px; text-align: center;">K</th>
                    <th style="width: 32px; text-align: center;">BK</th>
                    <th>Keterangan</th>
                </tr>
            </thead>
            <tbody>
                @forelse($rekap as $idx => $r)
                    <tr>
                        <td style="text-align: center; font-weight: bold;">{{ $idx + 1 }}</td>
                        <td style="font-weight: bold;">{{ $r['nama_asesi'] }}</td>
                        <td style="font-family: monospace;">{{ $r['nomor_pendaftaran'] }}</td>
                        <td style="text-align: center; font-weight: bold;">{{ strtoupper($r['k_bk']) === 'K' ? '✓' : '' }}</td>
                        <td style="text-align: center; font-weight: bold;">{{ strtoupper($r['k_bk']) === 'BK' ? '✓' : '' }}</td>
                        <td>{{ $r['keterangan'] ?? '-' }}</td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="6" style="text-align: center; color: #94a3b8;">Tidak ada data peserta</td>
                    </tr>
                @endforelse
                <tr style="background: #f8fafc; font-weight: bold;">
                    <td colspan="3" style="text-align: right;">Total Hasil:</td>
                    <td style="text-align: center;">{{ $ak05->total_k }}</td>
                    <td style="text-align: center;">{{ $ak05->total_bk }}</td>
                    <td>Total Peserta: {{ $ak05->total_asesi }}</td>
                </tr>
            </tbody>
        </table>

        <!-- ASPEK POSITIF DAN NEGATIF -->
        <div style="font-weight: bold; font-size: 8pt; margin-bottom: 4px; text-transform: uppercase;">
            II. Aspek Positif dan Negatif dalam Asesmen
        </div>
        <table style="margin-bottom: 12px; font-size: 8pt;">
            <tr>
                <td style="width: 25%; font-weight: bold; background: #f8fafc; vertical-align: top;">Aspek Positif</td>
                <td>{{ $ak05->aspek_positif ?: 'Asesi menunjukkan pemahaman SOP yang baik dan tertib selama proses asesmen.' }}</td>
            </tr>
            <tr>
                <td style="font-weight: bold; background: #f8fafc; vertical-align: top;">Aspek Negatif</td>
                <td>{{ $ak05->aspek_negatif ?: 'Tidak ditemukan aspek negatif yang signifikan selama proses pengujian.' }}</td>
            </tr>
            <tr>
                <td style="font-weight: bold; background: #f8fafc; vertical-align: top;">Penolakan Hasil / Banding</td>
                <td>{{ $ak05->penolakan_hasil ?: 'Tidak ada penolakan atau permohonan banding dari peserta.' }}</td>
            </tr>
        </table>

        <!-- SARAN PERBAIKAN -->
        @php
            $saran = (array) ($ak05->saran_perbaikan ?? []);
        @endphp
        <div style="font-weight: bold; font-size: 8pt; margin-bottom: 4px; text-transform: uppercase;">
            III. Saran Perbaikan
        </div>
        <table style="margin-bottom: 15px; font-size: 7.5pt;">
            <thead>
                <tr>
                    <th style="width: 24px; text-align: center;">No</th>
                    <th style="width: 100px;">Pihak Terkait</th>
                    <th>Tindakan yang Disarankan</th>
                    <th style="width: 70px; text-align: center;">Prioritas</th>
                    <th style="width: 80px; text-align: center;">Batas Waktu</th>
                </tr>
            </thead>
            <tbody>
                @forelse($saran as $sIdx => $s)
                    <tr>
                        <td style="text-align: center; font-weight: bold;">{{ $sIdx + 1 }}</td>
                        <td style="font-weight: bold;">{{ $s['pihak'] ?? '-' }}</td>
                        <td>{{ $s['tindakan'] ?? '-' }}</td>
                        <td style="text-align: center;">{{ $s['prioritas'] ?? '-' }}</td>
                        <td style="text-align: center;">{{ $s['batas_waktu'] ?? '-' }}</td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="5" style="text-align: center; color: #94a3b8;">Tidak ada butir saran perbaikan</td>
                    </tr>
                @endforelse
            </tbody>
        </table>

        <!-- TANDA TANGAN ASESOR -->
        <table style="margin-top: 15px; font-size: 8pt; width: 45%; margin-left: auto;">
            <tr>
                <td style="font-weight: bold; background: #f8fafc; text-align: center; padding: 6px;">Asesor Pelapor</td>
            </tr>
            <tr>
                <td style="height: 75px; vertical-align: bottom; text-align: center; padding-bottom: 8px;">
                    @if($ak05->tanda_tangan_asesor)
                        <img src="{{ $ak05->tanda_tangan_asesor }}" alt="TTD Asesor" style="max-height: 55px; margin: 0 auto; display: block;">
                    @endif
                    <div style="font-weight: bold; text-decoration: underline;">
                        {{ $ak05->asesor->nama_lengkap ?? '-' }}
                    </div>
                    <div style="font-size: 7pt; color: #475569;">
                        No. Reg: {{ $ak05->asesor->nomor_registrasi ?? 'MET.000.004455' }} &bull;
                        {{ $ak05->tanggal_ttd_asesor ? $ak05->tanggal_ttd_asesor->format('d/m/Y') : date('d/m/Y') }}
                    </div>
                </td>
            </tr>
        </table>

        <div style="margin-top: 20px; padding-top: 6px; border-top: 1px dashed #cbd5e1; display: flex; justify-content: space-between; font-size: 6.5pt; color: #64748b;">
            <div>Dokumen Resmi LSP SMKN 1 Gunungputri &bull; FR.AK.05 v{{ $ak05->version }}</div>
            <div>Dicetak: {{ date('d/m/Y H:i:s') }} &bull; Status: {{ strtoupper($ak05->status) }}</div>
        </div>
    </div>

</body>
</html>
