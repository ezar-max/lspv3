<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Bundel Portofolio Asesmen - {{ $pendaftaran->nomor_pendaftaran }}</title>
    <style>
        @page {
            size: A4 portrait;
            margin: 15mm 15mm 15mm 15mm;
        }
        body {
            font-family: 'Times New Roman', Times, serif;
            color: #000;
            background: #fff;
            line-height: 1.4;
            font-size: 11pt;
            margin: 0;
            padding: 15px;
        }
        .kop-surat {
            display: flex;
            align-items: center;
            border-bottom: 3px double #000;
            padding-bottom: 8px;
            margin-bottom: 16px;
        }
        .kop-logo {
            width: 75px;
            height: 75px;
            object-fit: contain;
            margin-right: 15px;
        }
        .kop-text {
            text-align: center;
            flex: 1;
        }
        .kop-text h2 {
            margin: 0;
            font-size: 13pt;
            font-weight: bold;
            text-transform: uppercase;
        }
        .kop-text h1 {
            margin: 2px 0;
            font-size: 14pt;
            font-weight: bold;
            text-transform: uppercase;
        }
        .kop-text p {
            margin: 1px 0;
            font-size: 9pt;
        }
        .judul-bundel {
            text-align: center;
            margin-bottom: 16px;
        }
        .judul-bundel h3 {
            margin: 0;
            font-size: 12.5pt;
            text-decoration: underline;
            text-transform: uppercase;
            font-weight: bold;
        }
        .tabel-info {
            width: 100%;
            margin-bottom: 14px;
            border-collapse: collapse;
        }
        .tabel-info td {
            padding: 3px 6px;
            vertical-align: top;
            font-size: 10.5pt;
        }
        .tabel-info td:first-child {
            width: 25%;
            font-weight: bold;
        }
        .tabel-info td:nth-child(2) {
            width: 3%;
        }
        .tabel-standar {
            width: 100%;
            border-collapse: collapse;
            margin: 12px 0;
            font-size: 10pt;
        }
        .tabel-standar th, .tabel-standar td {
            border: 1px solid #000;
            padding: 5px 8px;
        }
        .tabel-standar th {
            background: #f1f5f9;
            font-weight: bold;
            text-align: center;
        }
        .area-ttd-grid {
            margin-top: 25px;
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 20px;
            text-align: center;
            font-size: 10.5pt;
        }
        .btn-print-bar {
            position: fixed;
            bottom: 20px;
            right: 20px;
            background: #1e293b;
            color: #fff;
            padding: 10px 18px;
            border-radius: 8px;
            display: flex;
            gap: 10px;
            box-shadow: 0 4px 12px rgba(0,0,0,0.25);
            font-family: sans-serif;
            font-size: 14px;
            z-index: 999;
        }
        .btn-print-bar button {
            background: #2563eb;
            color: #fff;
            border: none;
            padding: 6px 14px;
            border-radius: 6px;
            cursor: pointer;
            font-weight: bold;
        }
        @media print {
            .btn-print-bar {
                display: none !important;
            }
            body {
                padding: 0;
            }
        }
    </style>
</head>
<body>

    <div class="btn-print-bar">
        <span>Bundel Portofolio Asesmen</span>
        <button onclick="window.print()">Cetak / Simpan PDF</button>
    </div>

    <!-- KOP RESMI LSP -->
    <div class="kop-surat">
        <img src="{{ asset('images/logo-lsp.jpeg') }}" class="kop-logo" alt="Logo LSP">
        <div class="kop-text">
            <h2>BADAN NASIONAL SERTIFIKASI PROFESI</h2>
            <h1>LEMBAGA SERTIFIKASI PROFESI (LSP P-1) SMKN 1 GUNUNGPUTRI</h1>
            <p><strong>No. SK Lisensi: {{ $pengaturan->no_sk_lisensi ?? 'KEP.1215/BNSP/V/2025' }} &bull; No. Lisensi: {{ $pengaturan->nomor_lisensi ?? 'BNSP-LSP-2629-ID' }}</strong></p>
            <p>{{ $pengaturan->alamat_lengkap ?? 'Jl. Barokah No. 6, Desa Wanaherang, Kec. Gunungputri, Kab. Bogor, Jawa Barat' }}</p>
        </div>
    </div>

    <!-- JUDUL -->
    <div class="judul-bundel">
        <h3>LEMBAR KELENGKAPAN BUNDEL PORTOFOLIO ASESMEN</h3>
        <p>No. Registrasi / Pendaftaran: <strong style="font-family: monospace;">{{ $pendaftaran->nomor_pendaftaran }}</strong></p>
    </div>

    <!-- IDENTITAS ASESI -->
    <table class="tabel-info">
        <tr>
            <td>Nama Lengkap Asesi</td>
            <td>:</td>
            <td><strong>{{ $pendaftaran->asesi->nama_lengkap ?? '-' }}</strong></td>
        </tr>
        <tr>
            <td>NIK / KTP</td>
            <td>:</td>
            <td><span style="font-family: monospace;">{{ $pendaftaran->asesi->profilAsesi->nik ?? '-' }}</span></td>
        </tr>
        <tr>
            <td>Instansi / Sekolah</td>
            <td>:</td>
            <td>{{ $pendaftaran->asesi->profilAsesi->nama_sekolah_instansi ?? 'SMKN 1 Gunungputri' }}</td>
        </tr>
        <tr>
            <td>Skema Sertifikasi</td>
            <td>:</td>
            <td><strong>{{ $pendaftaran->skema->nama_skema ?? '-' }}</strong> (Kode: {{ $pendaftaran->skema->kode_skema ?? '-' }})</td>
        </tr>
        <tr>
            <td>Tempat Uji (TUK)</td>
            <td>:</td>
            <td>{{ $pendaftaran->jadwal->nama_tuk ?? 'TUK SMKN 1 Gunungputri' }}</td>
        </tr>
        <tr>
            <td>Asesor Penguji</td>
            <td>:</td>
            <td><strong>{{ $pendaftaran->jadwal->asesor->nama_lengkap ?? ($pendaftaran->asesor->nama_lengkap ?? 'Asesor Kompetensi') }}</strong></td>
        </tr>
        <tr>
            <td>Keputusan Rekomendasi</td>
            <td>:</td>
            <td>
                @if($pendaftaran->rekomendasi && $pendaftaran->rekomendasi->keputusan === 'kompeten')
                    <strong style="color: #15803d; font-size: 11.5pt;">KOMPETEN (K)</strong>
                @elseif($pendaftaran->rekomendasi && $pendaftaran->rekomendasi->keputusan === 'belum_kompeten')
                    <strong style="color: #b91c1c; font-size: 11.5pt;">BELUM KOMPETEN (BK)</strong>
                @else
                    <strong>DALAM PROSES ASESMEN</strong>
                @endif
            </td>
        </tr>
    </table>

    <div style="font-weight: bold; margin-top: 10px; margin-bottom: 4px;">
        1. Checklist Kelengkapan Dokumen & Formulir Standar BNSP:
    </div>

    <table class="tabel-standar">
        <thead>
            <tr>
                <th style="width: 40px;">No</th>
                <th style="width: 130px;">Kode Formulir</th>
                <th>Nama Dokumen / Tahapan Asesmen</th>
                <th style="width: 140px;">Status Berkas</th>
            </tr>
        </thead>
        <tbody>
            <tr>
                <td style="text-align: center;">1</td>
                <td style="font-family: monospace; font-weight: bold;">FR.APL.01</td>
                <td>Permohonan Sertifikasi Kompetensi & Biodata Asesi</td>
                <td style="text-align: center; font-weight: bold; color: #15803d;">Lengkap / Valid</td>
            </tr>
            <tr>
                <td style="text-align: center;">2</td>
                <td style="font-family: monospace; font-weight: bold;">FR.APL.02</td>
                <td>Asesmen Mandiri Peserta Sertifikasi</td>
                <td style="text-align: center; font-weight: bold; color: {{ $pendaftaran->status_pendaftaran === 'diverifikasi' ? '#15803d' : '#64748b' }};">
                    {{ $pendaftaran->status_pendaftaran === 'diverifikasi' ? 'Diverifikasi' : 'Diajukan' }}
                </td>
            </tr>
            <tr>
                <td style="text-align: center;">3</td>
                <td style="font-family: monospace; font-weight: bold;">FR.MAPA.01</td>
                <td>Merencanakan Aktivitas & Proses Asesmen</td>
                <td style="text-align: center; font-weight: bold; color: #15803d;">Tersedia</td>
            </tr>
            <tr>
                <td style="text-align: center;">4</td>
                <td style="font-family: monospace; font-weight: bold;">FR.AK.01</td>
                <td>Persetujuan Asesmen & Kerahasiaan</td>
                <td style="text-align: center; font-weight: bold; color: {{ $pendaftaran->ak01_ttd_asesi ? '#15803d' : '#b45309' }};">
                    {{ $pendaftaran->ak01_ttd_asesi ? 'Ditandatangani' : 'Tersedia' }}
                </td>
            </tr>
            <tr>
                <td style="text-align: center;">5</td>
                <td style="font-family: monospace; font-weight: bold;">FR.IA (MUK)</td>
                <td>Instrumen Asesmen Kompetensi (Uji Praktik, CBT, Esai, Wawancara)</td>
                <td style="text-align: center; font-weight: bold; color: {{ $pendaftaran->rekomendasi ? '#15803d' : '#64748b' }};">
                    {{ $pendaftaran->rekomendasi ? 'Selesai Dinilai' : 'Dalam Proses' }}
                </td>
            </tr>
            <tr>
                <td style="text-align: center;">6</td>
                <td style="font-family: monospace; font-weight: bold;">FR.AK.02 - 06</td>
                <td>Rekaman Asesmen, Banding, Pleno & Umpan Balik</td>
                <td style="text-align: center; font-weight: bold; color: #15803d;">Lengkap</td>
            </tr>
        </tbody>
    </table>

    <div style="font-weight: bold; margin-top: 14px; margin-bottom: 4px;">
        2. Daftar Unit Kompetensi Yang Diases:
    </div>

    <table class="tabel-standar">
        <thead>
            <tr>
                <th style="width: 30px;">No</th>
                <th style="width: 140px;">Kode Unit</th>
                <th>Judul Unit Kompetensi</th>
                <th style="width: 100px;">Standar</th>
            </tr>
        </thead>
        <tbody>
            @if($pendaftaran->skema && $pendaftaran->skema->unitKompetensi && $pendaftaran->skema->unitKompetensi->count() > 0)
                @foreach($pendaftaran->skema->unitKompetensi as $idx => $u)
                    <tr>
                        <td style="text-align: center;">{{ $idx + 1 }}</td>
                        <td style="font-family: monospace; font-weight: bold;">{{ $u->kode_unit }}</td>
                        <td>{{ $u->judul_unit }}</td>
                        <td style="text-align: center;">{{ $u->standar_kompetensi ?? 'SKKNI' }}</td>
                    </tr>
                @endforeach
            @else
                <tr>
                    <td colspan="4" style="text-align: center; color: #64748b;">Belum ada unit terdaftar.</td>
                </tr>
            @endif
        </tbody>
    </table>

    <!-- AREA TTD -->
    <div class="area-ttd-grid">
        <div>
            <div>Peserta / Asesi,</div>
            <div style="height: 55px;"></div>
            <div style="font-weight: bold; text-decoration: underline;">
                {{ $pendaftaran->asesi->nama_lengkap ?? 'Nama Asesi' }}
            </div>
            <div style="font-size: 9pt;">NIK: {{ $pendaftaran->asesi->profilAsesi->nik ?? '-' }}</div>
        </div>

        <div>
            <div>Asesor Penguji,</div>
            <div style="height: 55px;"></div>
            <div style="font-weight: bold; text-decoration: underline;">
                {{ $pendaftaran->jadwal->asesor->nama_lengkap ?? ($pendaftaran->asesor->nama_lengkap ?? 'Asesor Kompetensi') }}
            </div>
            <div style="font-size: 9pt;">No. Reg: {{ $pendaftaran->jadwal->asesor->nomor_registrasi ?? '-' }}</div>
        </div>
    </div>

</body>
</html>
