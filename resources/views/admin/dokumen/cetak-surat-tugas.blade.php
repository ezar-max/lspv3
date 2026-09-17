<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Surat Tugas Asesor - {{ $surat->nomor_surat }}</title>
    <style>
        @page {
            size: A4 portrait;
            margin: 20mm 20mm 20mm 20mm;
        }
        body {
            font-family: 'Times New Roman', Times, serif;
            color: #000;
            background: #fff;
            line-height: 1.5;
            font-size: 12pt;
            margin: 0;
            padding: 20px;
        }
        .kop-surat {
            display: flex;
            align-items: center;
            border-bottom: 3px double #000;
            padding-bottom: 12px;
            margin-bottom: 24px;
        }
        .kop-logo {
            width: 85px;
            height: 85px;
            object-fit: contain;
            margin-right: 18px;
        }
        .kop-text {
            text-align: center;
            flex: 1;
        }
        .kop-text h2 {
            margin: 0;
            font-size: 14pt;
            font-weight: bold;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }
        .kop-text h1 {
            margin: 2px 0;
            font-size: 15pt;
            font-weight: bold;
            text-transform: uppercase;
        }
        .kop-text p {
            margin: 2px 0;
            font-size: 9.5pt;
        }
        .judul-surat {
            text-align: center;
            margin-bottom: 24px;
        }
        .judul-surat h3 {
            margin: 0;
            font-size: 13pt;
            text-decoration: underline;
            text-transform: uppercase;
            font-weight: bold;
        }
        .judul-surat p {
            margin: 3px 0 0 0;
            font-size: 11pt;
        }
        .isi-paragraf {
            text-align: justify;
            margin-bottom: 14px;
            text-indent: 30px;
        }
        .tabel-detail {
            width: 100%;
            margin: 14px 0 20px 0;
            border-collapse: collapse;
        }
        .tabel-detail td {
            padding: 4px 8px;
            vertical-align: top;
            font-size: 11.5pt;
        }
        .tabel-detail td:first-child {
            width: 25%;
        }
        .tabel-detail td:nth-child(2) {
            width: 3%;
        }
        .area-ttd {
            margin-top: 35px;
            display: flex;
            justify-content: flex-end;
        }
        .ttd-box {
            width: 260px;
            text-align: center;
            font-size: 11.5pt;
        }
        .ttd-img {
            height: 75px;
            margin: 8px 0;
            object-fit: contain;
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
        <span>Surat Tugas Resmi Asesor</span>
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
            <p>Email: {{ $pengaturan->email_resmi ?? 'lsp.smkn1gnputri@gmail.com' }} | Telp: {{ $pengaturan->nomor_telepon ?? '081283854572' }}</p>
        </div>
    </div>

    <!-- JUDUL SURAT -->
    <div class="judul-surat">
        <h3>SURAT TUGAS ASESOR KOMPETENSI</h3>
        <p>Nomor: {{ $surat->nomor_surat }}</p>
    </div>

    <!-- ISI SURAT -->
    <div class="isi-paragraf">
        Ketua Lembaga Sertifikasi Profesi Pihak Kesatu (LSP P-1) SMKN 1 Gunungputri dengan ini memberikan tugas kedinasan dan wewenang teknis asesmen kepada:
    </div>

    <table class="tabel-detail">
        <tr>
            <td><strong>Nama Asesor</strong></td>
            <td>:</td>
            <td><strong>{{ $surat->asesor->nama_lengkap ?? '-' }}</strong></td>
        </tr>
        <tr>
            <td><strong>No. Registrasi (MET)</strong></td>
            <td>:</td>
            <td><strong style="font-family: monospace;">{{ $surat->asesor->nomor_registrasi ?? '-' }}</strong></td>
        </tr>
        <tr>
            <td><strong>Jabatan / Peran</strong></td>
            <td>:</td>
            <td>Asesor Kompetensi LSP-P1 SMKN 1 Gunungputri</td>
        </tr>
    </table>

    <div class="isi-paragraf">
        Untuk melaksanakan tugas sebagai <strong>Asesor Penguji</strong> pada kegiatan Uji Kompetensi Keahlian / Asesmen Sertifikasi Profesi dengan ketentuan rincian penugasan sebagai berikut:
    </div>

    <table class="tabel-detail">
        <tr>
            <td><strong>Skema Sertifikasi</strong></td>
            <td>:</td>
            <td><strong>{{ $surat->jadwal->skema->nama_skema ?? 'Skema Penugasan Terkait' }}</strong></td>
        </tr>
        <tr>
            <td><strong>Tujuan Tugas</strong></td>
            <td>:</td>
            <td>{{ $surat->tujuan_penugasan }}</td>
        </tr>
        <tr>
            <td><strong>Tempat Uji (TUK)</strong></td>
            <td>:</td>
            <td>{{ $surat->lokasi_tuk }}</td>
        </tr>
        <tr>
            <td><strong>Waktu Pelaksanaan</strong></td>
            <td>:</td>
            <td>{{ date('d F Y', strtotime($surat->tanggal_mulai ?? $surat->tanggal_surat)) }}</td>
        </tr>
        @if($surat->catatan)
        <tr>
            <td><strong>Catatan Khusus</strong></td>
            <td>:</td>
            <td>{{ $surat->catatan }}</td>
        </tr>
        @endif
    </table>

    <div class="isi-paragraf">
        Demikian Surat Tugas ini dibuat untuk dilaksanakan dengan penuh rasa tanggung jawab, menjunjung tinggi kode etik asesor, serta melaporkan seluruh hasil asesmen (FR.APL, FR.MAPA, FR.AK, FR.IA) kepada pengurus LSP setelah tugas selesai.
    </div>

    <!-- AREA TANDA TANGAN -->
    <div class="area-ttd">
        <div class="ttd-box">
            <div>Bogor, {{ date('d F Y', strtotime($surat->tanggal_surat)) }}</div>
            <div style="margin-top: 2px;"><strong>LSP P-1 SMKN 1 Gunungputri</strong></div>
            <div style="font-size: 10.5pt; color: #444;">Ketua / Kepala LSP,</div>
            
            @if(auth()->user() && auth()->user()->tanda_tangan)
                <img src="{{ asset(auth()->user()->tanda_tangan) }}" class="ttd-img" alt="TTD Ketua LSP">
            @else
                <div style="height: 65px;"></div>
            @endif
            
            <div style="font-weight: bold; text-decoration: underline;">
                {{ auth()->user()->nama_lengkap ?? 'Administrator LSP' }}
            </div>
            <div style="font-size: 9.5pt; color: #333;">NIP. 197508122005011004</div>
        </div>
    </div>

</body>
</html>
