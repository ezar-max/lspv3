<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Berita Acara & Pleno Asesmen - {{ $ba->nomor_berita_acara }}</title>
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
        .judul-bap {
            text-align: center;
            margin-bottom: 16px;
        }
        .judul-bap h3 {
            margin: 0;
            font-size: 12.5pt;
            text-decoration: underline;
            text-transform: uppercase;
            font-weight: bold;
        }
        .judul-bap p {
            margin: 2px 0 0 0;
            font-size: 10.5pt;
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
        .tabel-peserta {
            width: 100%;
            border-collapse: collapse;
            margin: 12px 0 16px 0;
            font-size: 10pt;
        }
        .tabel-peserta th, .tabel-peserta td {
            border: 1px solid #000;
            padding: 5px 8px;
        }
        .tabel-peserta th {
            background: #f1f5f9;
            text-align: center;
            font-weight: bold;
        }
        .rekap-box {
            display: flex;
            gap: 15px;
            margin: 12px 0;
            padding: 8px 12px;
            background: #f8fafc;
            border: 1px solid #cbd5e1;
            font-size: 10.5pt;
        }
        .area-ttd-grid {
            margin-top: 25px;
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 20px;
            text-align: center;
            font-size: 10.5pt;
        }
        .ttd-img {
            height: 60px;
            margin: 5px 0;
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
        <span>FR.AK.05 & FR.AK.06 Berita Acara & Pleno</span>
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

    <!-- JUDUL BAP -->
    <div class="judul-bap">
        <h3>BERITA ACARA PELAKSANAAN ASESMEN & RAPAT PLENO KELULUSAN</h3>
        <p><strong>FR.AK.05 (Laporan Asesmen) &bull; FR.AK.06 (Meninjau Proses Asesmen)</strong></p>
        <p style="font-family: monospace;">Nomor: {{ $ba->nomor_berita_acara }}</p>
    </div>

    <!-- INFORMASI PELAKSANAAN -->
    <table class="tabel-info">
        <tr>
            <td>Skema Sertifikasi</td>
            <td>:</td>
            <td><strong>{{ $ba->jadwal->skema->nama_skema ?? '-' }}</strong> (Kode: {{ $ba->jadwal->skema->kode_skema ?? '-' }})</td>
        </tr>
        <tr>
            <td>Tempat Uji Kompetensi</td>
            <td>:</td>
            <td>{{ $ba->jadwal->nama_tuk ?? 'TUK SMKN 1 Gunungputri' }}</td>
        </tr>
        <tr>
            <td>Tanggal Pelaksanaan</td>
            <td>:</td>
            <td>{{ date('d F Y', strtotime($ba->tanggal_pelaksanaan)) }}</td>
        </tr>
        <tr>
            <td>Asesor Kompetensi</td>
            <td>:</td>
            <td><strong>{{ $ba->jadwal->asesor->nama_lengkap ?? '-' }}</strong> (No. Reg: {{ $ba->jadwal->asesor->nomor_registrasi ?? '-' }})</td>
        </tr>
    </table>

    <div class="rekap-box">
        <div>Total Peserta Terdaftar: <strong>{{ $ba->jumlah_peserta }} Orang</strong></div>
        <div>&bull;</div>
        <div>Direkomendasikan Kompeten (K): <strong style="color: #15803d;">{{ $ba->jumlah_kompeten }} Orang</strong></div>
        <div>&bull;</div>
        <div>Belum Kompeten (BK): <strong style="color: #b91c1c;">{{ $ba->jumlah_belum_kompeten }} Orang</strong></div>
    </div>

    <div style="font-weight: bold; margin-top: 10px; margin-bottom: 4px;">
        Daftar Rekapitulasi Hasil Uji Asesi:
    </div>

    <table class="tabel-peserta">
        <thead>
            <tr>
                <th style="width: 30px;">No</th>
                <th style="width: 120px;">No. Pendaftaran</th>
                <th>Nama Lengkap Asesi</th>
                <th style="width: 120px;">NIK</th>
                <th style="width: 110px;">Hasil Asesmen</th>
                <th>Catatan / Keterangan</th>
            </tr>
        </thead>
        <tbody>
            @if($ba->jadwal && $ba->jadwal->pendaftaranAsesi && $ba->jadwal->pendaftaranAsesi->count() > 0)
                @foreach($ba->jadwal->pendaftaranAsesi as $index => $p)
                    <tr>
                        <td style="text-align: center;">{{ $index + 1 }}</td>
                        <td style="font-family: monospace;">{{ $p->nomor_pendaftaran }}</td>
                        <td><strong>{{ $p->asesi->nama_lengkap ?? '-' }}</strong></td>
                        <td style="font-family: monospace;">{{ $p->asesi->profilAsesi->nik ?? '-' }}</td>
                        <td style="text-align: center; font-weight: bold;">
                            @if($p->rekomendasi && $p->rekomendasi->keputusan === 'kompeten')
                                <span style="color: #15803d;">KOMPETEN (K)</span>
                            @elseif($p->rekomendasi && $p->rekomendasi->keputusan === 'belum_kompeten')
                                <span style="color: #b91c1c;">BELUM KOMPETEN (BK)</span>
                            @else
                                <span style="color: #64748b;">-</span>
                            @endif
                        </td>
                        <td style="font-size: 9pt;">{{ $p->rekomendasi->catatan ?? '-' }}</td>
                    </tr>
                @endforeach
            @else
                <tr>
                    <td colspan="6" style="text-align: center; color: #64748b; padding: 15px;">Belum ada data asesi pada jadwal ini.</td>
                </tr>
            @endif
        </tbody>
    </table>

    <div style="font-size: 10.5pt; text-align: justify; margin-top: 10px;">
        <strong>Catatan Hasil Rapat Pleno:</strong> {{ $ba->catatan_pelaksanaan }}
    </div>

    <!-- AREA TTD -->
    <div class="area-ttd-grid">
        <div>
            <div>Mengetahui,</div>
            <div><strong>Penanggung Jawab TUK / Admin LSP</strong></div>
            @if(auth()->user() && auth()->user()->tanda_tangan)
                <img src="{{ asset(auth()->user()->tanda_tangan) }}" class="ttd-img" alt="TTD Admin">
            @else
                <div style="height: 55px;"></div>
            @endif
            <div style="font-weight: bold; text-decoration: underline;">{{ auth()->user()->nama_lengkap ?? 'Admin LSP' }}</div>
        </div>

        <div>
            <div>Bogor, {{ date('d F Y', strtotime($ba->tanggal_pelaksanaan)) }}</div>
            <div><strong>Asesor Kompetensi Penguji</strong></div>
            <div style="height: 55px;"></div>
            <div style="font-weight: bold; text-decoration: underline;">
                {{ $ba->jadwal->asesor->nama_lengkap ?? 'Asesor Penguji' }}
            </div>
            <div style="font-size: 9pt;">No. Reg: {{ $ba->jadwal->asesor->nomor_registrasi ?? '-' }}</div>
        </div>
    </div>

</body>
</html>
