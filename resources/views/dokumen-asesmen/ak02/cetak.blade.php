<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>FR.AK.02 - Rekaman Asesmen Kompetensi - {{ $pendaftaran->asesi->nama_lengkap ?? 'Peserta' }}</title>
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

    <!-- NO PRINT ACTION BAR -->
    <div class="no-print max-w-4xl mx-auto mb-4 flex items-center justify-between gap-3 px-4">
        <div class="text-xs text-slate-600 font-semibold">
            Pratinjau Format Cetak Dokumen Resmi BNSP (FR.AK.02)
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
                <div style="font-size: 10pt; font-weight: 900;">FR.AK.02</div>
                <div style="font-size: 6pt; font-weight: 800; text-transform: uppercase;">STANDAR BNSP</div>
            </div>
        </div>

        <div style="text-align: center; margin-bottom: 12px;">
            <h1 style="font-size: 10.5pt; font-weight: 900; text-transform: uppercase; letter-spacing: 0.03em;">
                FR.AK.02 — REKAMAN ASESMEN KOMPETENSI
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
                <td>{{ $pendaftaran->asesor->nama_lengkap ?? auth()->user()->nama_lengkap }} (No. Reg: {{ $pendaftaran->asesor->nomor_registrasi ?? 'MET.000.004455' }})</td>
            </tr>
            <tr>
                <td style="font-weight: bold; background: #f8fafc;">Nama Asesi</td>
                <td>{{ $pendaftaran->asesi->nama_lengkap ?? '-' }} (No. Reg: {{ $pendaftaran->nomor_pendaftaran }})</td>
            </tr>
            <tr>
                <td style="font-weight: bold; background: #f8fafc;">Tanggal Pelaksanaan</td>
                <td>{{ $ak02->updated_at ? $ak02->updated_at->format('d/m/Y') : date('d/m/Y') }}</td>
            </tr>
        </table>

        <!-- MATRIKS METODE BUKTI & KEPUTUSAN UNIT -->
        @php
            $units = $pendaftaran->skema ? $pendaftaran->skema->unitKompetensi : collect();
            $matriks = (array) ($ak02->matriks_bukti ?? []);
            $rekomendasi = (array) ($ak02->rekomendasi_unit ?? []);
        @endphp

        <table style="margin-bottom: 12px; font-size: 7.5pt;">
            <thead>
                <tr>
                    <th rowspan="2" style="width: 20px; text-align: center;">No</th>
                    <th rowspan="2">Unit Kompetensi</th>
                    <th colspan="8" style="text-align: center;">Metode Asesmen / Pengumpulan Bukti</th>
                    <th colspan="2" style="text-align: center; width: 50px;">Keputusan</th>
                </tr>
                <tr>
                    <th style="text-align: center; width: 26px;">CLO</th>
                    <th style="text-align: center; width: 26px;">VP</th>
                    <th style="text-align: center; width: 26px;">P3</th>
                    <th style="text-align: center; width: 26px;">PW</th>
                    <th style="text-align: center; width: 26px;">DPL</th>
                    <th style="text-align: center; width: 26px;">DPE</th>
                    <th style="text-align: center; width: 26px;">PK</th>
                    <th style="text-align: center; width: 26px;">Lain</th>
                    <th style="text-align: center; width: 25px;">K</th>
                    <th style="text-align: center; width: 25px;">BK</th>
                </tr>
            </thead>
            <tbody>
                @foreach($units as $i => $u)
                    @php
                        $m = $matriks[$u->id]['methods'] ?? [];
                        $rek = $rekomendasi[$u->id] ?? [];
                        $hasil = strtoupper(is_array($rek) ? ($rek['hasil'] ?? 'K') : $rek);
                    @endphp
                    <tr>
                        <td style="text-align: center; font-weight: bold;">{{ $i + 1 }}</td>
                        <td>
                            <div style="font-weight: bold; font-family: monospace;">{{ $u->kode_unit }}</div>
                            <div>{{ $u->judul_unit }}</div>
                        </td>
                        <td style="text-align: center;">{{ !empty($m['observasi_demonstrasi']) ? '✓' : '' }}</td>
                        <td style="text-align: center;">{{ !empty($m['portofolio']) ? '✓' : '' }}</td>
                        <td style="text-align: center;">{{ !empty($m['pernyataan_pihak_ketiga']) ? '✓' : '' }}</td>
                        <td style="text-align: center;">{{ !empty($m['pertanyaan_wawancara']) ? '✓' : '' }}</td>
                        <td style="text-align: center;">{{ !empty($m['pertanyaan_lisan']) ? '✓' : '' }}</td>
                        <td style="text-align: center;">{{ !empty($m['pertanyaan_tertulis']) ? '✓' : '' }}</td>
                        <td style="text-align: center;">{{ !empty($m['proyek_kerja']) ? '✓' : '' }}</td>
                        <td style="text-align: center;">{{ !empty($m['lainnya']) ? '✓' : '' }}</td>
                        <td style="text-align: center; font-weight: bold;">{{ $hasil === 'K' ? '✓' : '' }}</td>
                        <td style="text-align: center; font-weight: bold;">{{ $hasil === 'BK' ? '✓' : '' }}</td>
                    </tr>
                @endforeach
            </tbody>
        </table>

        <!-- REKAP & TINDAK LANJUT -->
        <table style="margin-bottom: 12px; font-size: 8pt;">
            <tr>
                <td style="width: 25%; font-weight: bold; background: #f8fafc;">Rekomendasi Hasil Asesmen</td>
                <td style="width: 75%; font-weight: bold; font-size: 8.5pt;">
                    @if($ak02->keputusan_final === 'kompeten')
                        <span style="color: #065f46;">[ ✓ ] KOMPETEN</span> &nbsp;&nbsp;&nbsp; [ &nbsp; ] BELUM KOMPETEN
                    @else
                        [ &nbsp; ] KOMPETEN &nbsp;&nbsp;&nbsp; <span style="color: #991b1b;">[ ✓ ] BELUM KOMPETEN</span>
                    @endif
                </td>
            </tr>
            @if($ak02->tindak_lanjut)
                <tr>
                    <td style="font-weight: bold; background: #f8fafc; vertical-align: top;">Tindak Lanjut yang Dibutuhkan</td>
                    <td>{{ $ak02->tindak_lanjut }}</td>
                </tr>
            @endif
            <tr>
                <td style="font-weight: bold; background: #f8fafc; vertical-align: top;">Komentar / Observasi Asesor</td>
                <td>{{ $ak02->komentar_asesor ?: 'Peserta telah mendemonstrasikan seluruh kriteria unjuk kerja sesuai standar kompetensi yang dipersyaratkan.' }}</td>
            </tr>
        </table>

        <!-- TANDA TANGAN DUA BELAH PIHAK -->
        <table style="margin-top: 15px; font-size: 8pt; text-align: center;">
            <tr>
                <td style="width: 50%; font-weight: bold; background: #f8fafc; padding: 6px;">Asesi (Peserta)</td>
                <td style="width: 50%; font-weight: bold; background: #f8fafc; padding: 6px;">Asesor Kompetensi</td>
            </tr>
            <tr>
                <td style="height: 75px; vertical-align: bottom; padding-bottom: 8px;">
                    @if($ak02->tanda_tangan_asesi)
                        <img src="{{ $ak02->tanda_tangan_asesi }}" alt="TTD Asesi" style="max-height: 55px; margin: 0 auto; display: block;">
                    @endif
                    <div style="font-weight: bold; text-decoration: underline;">
                        {{ $pendaftaran->asesi->nama_lengkap ?? 'Peserta' }}
                    </div>
                    <div style="font-size: 7pt; color: #475569;">
                        Tanggal: {{ $ak02->tanggal_ttd_asesi ? $ak02->tanggal_ttd_asesi->format('d/m/Y') : '-' }}
                    </div>
                </td>
                <td style="height: 75px; vertical-align: bottom; padding-bottom: 8px;">
                    @if($ak02->tanda_tangan_asesor)
                        <img src="{{ $ak02->tanda_tangan_asesor }}" alt="TTD Asesor" style="max-height: 55px; margin: 0 auto; display: block;">
                    @endif
                    <div style="font-weight: bold; text-decoration: underline;">
                        {{ $pendaftaran->asesor->nama_lengkap ?? auth()->user()->nama_lengkap }}
                    </div>
                    <div style="font-size: 7pt; color: #475569;">
                        No. Reg: {{ $pendaftaran->asesor->nomor_registrasi ?? 'MET.000.004455' }} &bull; 
                        Tanggal: {{ $ak02->tanggal_ttd_asesor ? $ak02->tanggal_ttd_asesor->format('d/m/Y') : '-' }}
                    </div>
                </td>
            </tr>
        </table>

        <!-- FOOTER RESMI DOKUMEN -->
        <div style="margin-top: 15px; padding-top: 6px; border-top: 1px dashed #cbd5e1; display: flex; justify-content: space-between; font-size: 6.5pt; color: #64748b;">
            <div>Dokumen Resmi LSP SMKN 1 Gunungputri &bull; FR.AK.02 v{{ $ak02->version }}</div>
            <div>Dicetak pada: {{ date('d/m/Y H:i:s') }} &bull; Status: {{ strtoupper($ak02->status) }}</div>
        </div>
    </div>

</body>
</html>
