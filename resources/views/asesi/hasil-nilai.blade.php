@extends('tata-letak.dasbor')

@section('judul', 'Hasil & Nilai Asesmen')

@push('css')
    <link rel="stylesheet" href="{{ asset('css/asesi/dashboard-asesi.css') }}">
    <style>
        /* Standar Ukuran Kertas Cetak A4 Portrait */
        @page {
            size: A4 portrait;
            margin: 10mm 12mm 12mm 12mm;
        }

        /* Default elemen cetak disembunyikan saat tampil di browser */
        .hanya-cetak {
            display: none !important;
        }

        @media print {
            /* Sembunyikan seluruh UI navigasi web, navbar, header, footer, alert, dan tombol aksi */
            nav,
            header,
            footer,
            .no-print,
            .tombol-aksi-cetak,
            .btn-kembali,
            .layar-header-web,
            .tombol,
            button,
            a.tombol,
            .alert-banner-global,
            aside,
            [role="navigation"],
            .sticky {
                display: none !important;
            }

            /* Reset tampilan root & body agar pas di kertas */
            html, body {
                background: #ffffff !important;
                color: #000000 !important;
                font-family: 'Public Sans', -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, Helvetica, Arial, sans-serif !important;
                font-size: 8.5pt !important;
                line-height: 1.35 !important;
                margin: 0 !important;
                padding: 0 !important;
                width: 100% !important;
                -webkit-print-color-adjust: exact !important;
                print-color-adjust: exact !important;
            }

            main, .animasi-fade, .animasi-slide, .wadah-konten {
                margin: 0 !important;
                padding: 0 !important;
                max-width: 100% !important;
                width: 100% !important;
                display: block !important;
            }

            /* Tampilkan elemen khusus cetak (Kop surat, tabel info cetak, tanda tangan resmi) */
            .hanya-cetak {
                display: block !important;
            }

            /* Elemen khusus layar web disembunyikan saat cetak */
            .hanya-layar {
                display: none !important;
            }

            /* Wadah dokumen per pendaftaran */
            .lembar-dokumen-cetak {
                background: #ffffff !important;
                padding: 0 !important;
                margin: 0 !important;
                border: none !important;
                box-shadow: none !important;
                page-break-after: always;
                page-break-inside: auto !important;
            }

            .lembar-dokumen-cetak:last-of-type {
                page-break-after: auto;
            }

            /* Styling tabel untuk cetak */
            .tabel-print-rapi {
                width: 100% !important;
                border-collapse: collapse !important;
                margin-bottom: 8px !important;
                font-size: 8pt !important;
            }

            .tabel-print-rapi th, 
            .tabel-print-rapi td {
                border: 1px solid #1e293b !important;
                padding: 4px 6px !important;
                color: #0f172a !important;
                vertical-align: middle;
            }

            .tabel-print-rapi th {
                background-color: #f1f5f9 !important;
                font-weight: 800 !important;
                text-transform: uppercase !important;
                font-size: 7.5pt !important;
            }

            tr {
                page-break-inside: avoid !important;
            }

            .hindari-potong {
                page-break-inside: avoid !important;
            }

            /* Text utility cetak */
            .teks-cetak-kompeten {
                color: #15803d !important;
                font-weight: 800 !important;
            }

            .teks-cetak-belum-kompeten {
                color: #b91c1c !important;
                font-weight: 800 !important;
            }

            .teks-cetak-peringatan {
                color: #b45309 !important;
                font-weight: 800 !important;
            }
        }
    </style>
@endpush

@section('konten')
<div style="max-width: 1050px; margin: 0 auto;" class="animasi-slide">
    <!-- HEADER LAYAR (Hanya tampil di browser, disembunyikan saat cetak) -->
    <div class="layar-header-web" style="display: flex; justify-content: space-between; align-items: flex-start; flex-wrap: wrap; gap: 1rem; margin-bottom: 1.5rem;">
        <div>
            <h1 style="font-size: 1.8rem; color: var(--biru-malam); margin-bottom: 0.35rem;">Hasil & Nilai Kelulusan Uji Kompetensi</h1>
            <p style="color: var(--abu-teks); margin: 0;">Rincian penilaian kompetensi per unit dan status rekomendasi kelulusan dari Asesor Penguji</p>
        </div>
        <div class="tombol-aksi-cetak no-print" style="display: flex; gap: 0.5rem; align-items: center;">
            <a href="{{ route('asesi.dasbor') }}" class="tombol tombol-sekunder btn-kembali" style="font-size: 0.88rem;">
                <i class="fa-solid fa-arrow-left" style="margin-right: 0.35rem;"></i> Kembali ke Dasbor
            </a>
            <button type="button" onclick="window.print()" class="tombol tombol-utama" style="font-size: 0.88rem; background: var(--biru-utama); color: #ffffff;">
                <i class="fa-solid fa-print" style="margin-right: 0.35rem;"></i> Cetak Hasil / Simpan PDF
            </button>
        </div>
    </div>

    @forelse($pendaftaranList as $p)
        @php
            $namaAsesor = $p->rekomendasi?->asesor?->nama_lengkap 
                ?? $p->asesor?->nama_lengkap 
                ?? $p->jadwal?->asesor?->nama_lengkap 
                ?? 'Asesor LSP';
            
            $ttdAsesor = $p->rekomendasi?->tanda_tangan_asesor 
                ?: ($p->tanda_tangan_asesor ?: ($p->asesor?->tanda_tangan ?? null));

            $ttdAsesi = $p->tanda_tangan_asesi ?: ($p->asesi?->tanda_tangan ?? null);

            $namaAsesi = $p->asesi?->nama_lengkap ?? auth()->user()->nama_lengkap;

            $unitList = $p->skema && $p->skema->unitKompetensi ? $p->skema->unitKompetensi : collect();
            
            // Map penilaian dari tabel penilaian_asesmen
            $penilaianMap = $p->penilaian ? $p->penilaian->keyBy('unit_id') : collect();
            
            // Map rekomendasi unit dari FR.AK.02 jika ada
            $ak02Units = ($p->ak02 && is_array($p->ak02->rekomendasi_unit)) ? $p->ak02->rekomendasi_unit : [];

            // Validasi ketat: Asesmen HANYA dianggap selesai jika status pendaftaran adalah 'selesai' dan ada rekomendasi final
            $isDitolak = ($p->status_pendaftaran === 'ditolak' || $p->rekomendasi_admin_status === 'tidak_diterima');
            $isSelesai = !$isDitolak && ($p->status_pendaftaran === 'selesai') && !empty($p->rekomendasi) && !empty($p->rekomendasi->keputusan);
            $keputusanAkhir = $isSelesai ? $p->rekomendasi->keputusan : null;
        @endphp

        <div class="kartu lembar-dokumen-cetak" style="margin-bottom: 2rem; padding: 2rem; border-radius: 12px; background: #ffffff;">
            
            <!-- =========================================================================
                 1. KOP SURAT RESMI BNSP & LSP (HANYA MUNCUL DI CETAK)
                 ========================================================================= -->
            <div class="hanya-cetak" style="border-bottom: 2.5px solid #0f172a; padding-bottom: 8px; margin-bottom: 12px;">
                <div style="display: flex; align-items: center; justify-content: space-between; gap: 12px;">
                    <div style="display: flex; align-items: center; gap: 12px; flex: 1;">
                        <img src="{{ asset('images/logo-lsp.jpeg') }}" alt="Logo LSP" style="width: 52px; height: 52px; object-fit: contain; border: 1px solid #94a3b8; padding: 2px;">
                        <div>
                            <div style="font-size: 7.5pt; font-weight: 800; color: #475569; letter-spacing: 0.04em; text-transform: uppercase;">
                                BADAN NASIONAL SERTIFIKASI PROFESI (BNSP)
                            </div>
                            <div style="font-size: 10.5pt; font-weight: 900; color: #0f172a; text-transform: uppercase; line-height: 1.2; margin: 1px 0;">
                                {{ $pengaturan->nama_lsp ?? 'LSP-P1 SMKN 1 GUNUNGPUTRI' }}
                            </div>
                            <div style="font-size: 7pt; font-weight: 700; color: #1e40af;">
                                SK LISENSI: {{ $pengaturan->no_sk_lisensi ?? 'KEP.1215/BNSP/V/2025' }} &bull; NO. LISENSI: {{ $pengaturan->nomor_lisensi ?? 'BNSP-LSP-2629-ID' }}
                            </div>
                            <div style="font-size: 6.5pt; color: #475569; margin-top: 1px;">
                                {{ $pengaturan->alamat_lengkap ?? 'Jl. Barokah No. 6, Wanaherang, Kec. Gunungputri, Kab. Bogor, Jawa Barat 16965' }}
                            </div>
                        </div>
                    </div>
                    <div style="border: 2px solid #0f172a; padding: 5px 12px; text-align: center; background: #f8fafc; min-width: 120px;">
                        <div style="font-size: 8.5pt; font-weight: 900; color: #0f172a;">LEMBAR HASIL</div>
                        <div style="font-size: 6.5pt; font-weight: 800; color: #1e40af; text-transform: uppercase;">UJI KOMPETENSI</div>
                    </div>
                </div>

                <div style="text-align: center; margin-top: 10px;">
                    <h2 style="font-size: 11pt; font-weight: 900; text-transform: uppercase; letter-spacing: 0.03em; margin: 0 0 2px 0; color: #0f172a;">
                        LEMBAR REKAPITULASI HASIL & KEPUTUSAN ASESMEN
                    </h2>
                    <p style="font-size: 7.5pt; color: #475569; margin: 0;">
                        Rekapitulasi Penilaian Kualifikasi Kompetensi dan Rekomendasi Hasil Sertifikasi
                    </p>
                </div>
            </div>

            <!-- =========================================================================
                 2. HEADER VERSI WEB SCREEN (HANYA MUNCUL DI LAYAR BROWSER)
                 ========================================================================= -->
            <div class="hanya-layar" style="display: flex; justify-content: space-between; align-items: flex-start; flex-wrap: wrap; gap: 1rem; margin-bottom: 1.5rem; padding-bottom: 1.25rem; border-bottom: 1px solid #e2e8f0;">
                <div>
                    <div style="display: flex; align-items: center; gap: 0.5rem; margin-bottom: 0.35rem;">
                        <span class="lencana lencana-biru">{{ $p->skema->kode_skema ?? 'SKEMA' }}</span>
                        @if($p->jadwal && !$isDitolak)
                            <span class="lencana" style="background: #f1f5f9; color: #475569; font-weight: 600;">
                                {{ $p->jadwal->kode_jadwal ?? 'Jadwal Asesmen' }}
                            </span>
                        @endif
                    </div>
                    <h2 style="color: var(--biru-malam); font-size: 1.35rem; margin: 0 0 0.35rem 0; font-weight: 700;">
                        {{ $p->skema->nama_skema ?? 'Skema Sertifikasi' }}
                    </h2>
                    <div style="font-size: 0.88rem; color: var(--abu-teks); display: flex; gap: 1.25rem; flex-wrap: wrap;">
                        <span>No. Registrasi: <strong style="color: #1e293b;">{{ $p->nomor_pendaftaran }}</strong></span>
                        <span>Asesor Penguji: <strong style="color: #1e293b;">{{ $isDitolak ? '-' : $namaAsesor }}</strong></span>
                        @if(!$isDitolak && $p->jadwal && $p->jadwal->tanggal_uji)
                            <span>Tanggal Asesmen: <strong style="color: #1e293b;">{{ date('d F Y', strtotime($p->jadwal->tanggal_uji)) }}</strong></span>
                        @endif
                    </div>
                </div>

                <div style="text-align: right;">
                    <div style="font-size: 0.78rem; font-weight: 700; color: #64748b; text-transform: uppercase; margin-bottom: 0.35rem;">
                        Keputusan Akhir Asesmen:
                    </div>
                    @if($isDitolak)
                        <span class="lencana lencana-merah" style="padding: 0.6rem 1.25rem; font-size: 0.95rem; font-weight: 800; display: inline-flex; align-items: center; gap: 0.4rem;">
                            <i class="fa-solid fa-circle-xmark"></i> Permohonan Ditolak (Tidak Diterima)
                        </span>
                    @elseif($isSelesai && $keputusanAkhir === 'kompeten')
                        <span class="lencana lencana-hijau" style="padding: 0.6rem 1.25rem; font-size: 1rem; font-weight: 800; display: inline-flex; align-items: center; gap: 0.4rem;">
                            <i class="fa-solid fa-circle-check"></i> KOMPETEN (K)
                        </span>
                    @elseif($isSelesai && $keputusanAkhir === 'belum_kompeten')
                        <span class="lencana lencana-merah" style="padding: 0.6rem 1.25rem; font-size: 1rem; font-weight: 800; display: inline-flex; align-items: center; gap: 0.4rem;">
                            <i class="fa-solid fa-circle-xmark"></i> BELUM KOMPETEN (BK)
                        </span>
                    @else
                        <span class="lencana lencana-amber" style="padding: 0.6rem 1.25rem; font-size: 0.9rem; display: inline-flex; align-items: center; gap: 0.4rem;">
                            <i class="fa-solid fa-hourglass-half"></i> Asesmen Belum Selesai
                        </span>
                    @endif
                    @if($isSelesai || $p->jadwal?->status_jadwal === 'selesai')
                        <div style="margin-top:.65rem;" class="no-print">
                            <a href="{{ route('asesi.hasil-formulir', $p->id) }}" class="tombol tombol-utama" style="font-size:.84rem;">
                                Lihat Formulir & Rincian Hasil
                            </a>
                        </div>
                    @endif
                </div>
            </div>

            <!-- =========================================================================
                 3. TABEL INFORMASI ASESI & ASESMEN (MUNCUL DI CETAK DENGAN RAPI)
                 ========================================================================= -->
            <div class="hanya-cetak" style="margin-bottom: 12px;">
                <table class="tabel-print-rapi">
                    <tr>
                        <td style="width: 20%; font-weight: bold; background: #f8fafc;">Nama Peserta (Asesi)</td>
                        <td style="width: 30%; font-weight: 600;">{{ $namaAsesi }}</td>
                        <td style="width: 20%; font-weight: bold; background: #f8fafc;">No. Registrasi</td>
                        <td style="width: 30%; font-family: monospace; font-weight: bold;">{{ $p->nomor_pendaftaran }}</td>
                    </tr>
                    <tr>
                        <td style="font-weight: bold; background: #f8fafc;">Skema Sertifikasi</td>
                        <td style="font-weight: 600;">{{ $p->skema->nama_skema ?? '-' }} ({{ $p->skema->kode_skema ?? '-' }})</td>
                        <td style="font-weight: bold; background: #f8fafc;">Tempat Uji (TUK)</td>
                        <td>{{ $p->jadwal->nama_tuk ?? 'TUK SMKN 1 Gunungputri' }}</td>
                    </tr>
                    <tr>
                        <td style="font-weight: bold; background: #f8fafc;">Asesor Penguji</td>
                        <td>{{ $isDitolak ? '-' : $namaAsesor }}</td>
                        <td style="font-weight: bold; background: #f8fafc;">Tanggal Asesmen</td>
                        <td>{{ (!$isDitolak && $p->jadwal && $p->jadwal->tanggal_uji) ? date('d F Y', strtotime($p->jadwal->tanggal_uji)) : '-' }}</td>
                    </tr>
                    <tr>
                        <td style="font-weight: bold; background: #f8fafc;">Keputusan Akhir</td>
                        <td colspan="3">
                            @if($isDitolak)
                                <strong style="color: #dc2626;">DITOLAK / TIDAK DITERIMA</strong>
                                <span style="font-size: 7.5pt; color: #64748b; margin-left: 6px;">(Verifikasi berkas permohonan APL.01 ditolak oleh Admin LSP)</span>
                            @elseif($isSelesai && $keputusanAkhir === 'kompeten')
                                <strong style="color: #15803d; font-size: 9pt;">KOMPETEN (K)</strong>
                                <span style="font-size: 7.5pt; color: #166534; margin-left: 6px;">&mdash; Direkomendasikan untuk diterbitkan Sertifikat Kompetensi BNSP</span>
                            @elseif($isSelesai && $keputusanAkhir === 'belum_kompeten')
                                <strong style="color: #b91c1c; font-size: 9pt;">BELUM KOMPETEN (BK)</strong>
                                <span style="font-size: 7.5pt; color: #991b1b; margin-left: 6px;">&mdash; Belum memenuhi seluruh kriteria unjuk kerja yang dipersyaratkan</span>
                            @else
                                <strong style="color: #b45309; font-size: 8.5pt;">ASESMEN SEDANG BERLANGSUNG / BELUM SELESAI</strong>
                                <span style="font-size: 7.5pt; color: #64748b; margin-left: 6px;">(Menunggu penyelesaian seluruh instrumen uji dan pengesahan final oleh Asesor)</span>
                            @endif
                        </td>
                    </tr>
                </table>
            </div>

            <!-- Banner Penolakan Jika Pendaftaran Ditolak (Layar) -->
            @if($isDitolak)
                <div class="hanya-layar" style="background-color: #fef2f2; border: 1.5px solid #fecaca; border-radius: 12px; padding: 1.25rem 1.5rem; margin-bottom: 1.5rem;">
                    <div style="display: flex; align-items: flex-start; gap: 0.85rem;">
                        <i class="fa-solid fa-triangle-exclamation" style="font-size: 1.4rem; color: #dc2626; margin-top: 0.15rem;"></i>
                        <div>
                            <h4 style="margin: 0 0 0.35rem 0; color: #991b1b; font-weight: 800; font-size: 1.05rem;">Permohonan Skema Ditolak</h4>
                            <p style="margin: 0 0 0.5rem 0; font-size: 0.9rem; color: #7f1d1d; line-height: 1.5;">
                                Pendaftaran skema sertifikasi ini dinyatakan <strong>Tidak Diterima / Ditolak</strong> pada verifikasi berkas FR.APL.01 oleh Admin LSP, sehingga proses uji kompetensi tidak dapat dilaksanakan.
                            </p>
                            @if($p->catatan_verifikasi)
                                <div style="background: #ffffff; border: 1px solid #fca5a5; border-radius: 8px; padding: 0.75rem 1rem; font-size: 0.88rem; color: #991b1b; margin-top: 0.5rem;">
                                    <strong>Catatan Verifikator Admin:</strong> "{{ $p->catatan_verifikasi }}"
                                </div>
                            @endif
                        </div>
                    </div>
                </div>
            @endif

            <!-- =========================================================================
                 4. TABEL RINCIAN UNIT KOMPETENSI (SEMPURNA DI LAYAR & CETAK)
                 ========================================================================= -->
            <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 0.5rem;">
                <h4 style="color: var(--biru-malam); margin: 0; font-size: 0.95rem; font-weight: 800; text-transform: uppercase; letter-spacing: 0.02em;">
                    Rincian Penilaian Unit Kompetensi:
                </h4>
                <small style="color: #64748b; font-size: 7.5pt; font-weight: 600;">Total: {{ $unitList->count() }} Unit Kompetensi</small>
            </div>

            <div class="tabel-wadah" style="margin-bottom: 1rem;">
                <table class="tabel-custom tabel-print-rapi">
                    <thead>
                        <tr>
                            <th style="width: 40px; text-align: center;">No.</th>
                            <th style="width: 155px; text-align: center;">Kode Unit</th>
                            <th>Judul Unit Kompetensi</th>
                            <th style="width: 150px; text-align: center;">Status Kompetensi</th>
                            <th style="width: 220px;">Catatan / Umpan Balik Asesor</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($unitList as $idx => $unit)
                            @php
                                $nilaiModel = $penilaianMap->get($unit->id);
                                $ak02Item = $ak02Units[$unit->id] ?? null;

                                $nilaiStatus = null;
                                $catatanUnit = null;

                                if ($isSelesai) {
                                    if ($nilaiModel) {
                                        $nilaiStatus = $nilaiModel->nilai_kompetensi;
                                        $catatanUnit = $nilaiModel->catatan_asesor;
                                    } elseif ($ak02Item) {
                                        $nilaiStatus = is_array($ak02Item) ? ($ak02Item['hasil'] ?? null) : $ak02Item;
                                        $catatanUnit = is_array($ak02Item) ? ($ak02Item['catatan'] ?? null) : null;
                                    } elseif ($keputusanAkhir === 'kompeten') {
                                        $nilaiStatus = 'K';
                                        $catatanUnit = 'Kompeten sesuai pemenuhan bukti observasi dan ujian.';
                                    } elseif ($keputusanAkhir === 'belum_kompeten') {
                                        $nilaiStatus = 'BK';
                                        $catatanUnit = 'Belum memenuhi kriteria unjuk kerja pada unit ini.';
                                    }
                                }
                            @endphp
                            <tr>
                                <td style="text-align: center; color: #475569; font-weight: 600;">{{ $idx + 1 }}</td>
                                <td style="text-align: center;">
                                    <strong style="color: var(--biru-utama); font-family: monospace; font-size: 0.85rem;">
                                        {{ $unit->kode_unit }}
                                    </strong>
                                </td>
                                <td>
                                    <div style="font-weight: 600; color: #0f172a; line-height: 1.3;">{{ $unit->judul_unit }}</div>
                                    @if($unit->jenis_standar)
                                        <small style="color: #64748b; font-size: 7pt; display: block; margin-top: 2px;">Standar: {{ $unit->jenis_standar }}</small>
                                    @endif
                                </td>
                                <td style="text-align: center;">
                                    @if($isDitolak)
                                        <span class="lencana lencana-merah hanya-layar" style="font-size: 0.78rem; font-weight: 700;">
                                            Dibatalkan (Ditolak)
                                        </span>
                                        <span class="hanya-cetak teks-cetak-belum-kompeten" style="font-size: 7.5pt;">
                                            Dibatalkan (Ditolak)
                                        </span>
                                    @elseif($isSelesai && ($nilaiStatus === 'K' || strtoupper((string)$nilaiStatus) === 'KOMPETEN'))
                                        <span class="lencana lencana-hijau hanya-layar" style="font-weight: 700;">
                                            Kompeten (K)
                                        </span>
                                        <span class="hanya-cetak teks-cetak-kompeten" style="font-size: 8pt;">
                                            KOMPETEN (K)
                                        </span>
                                    @elseif($isSelesai && ($nilaiStatus === 'BK' || strtoupper((string)$nilaiStatus) === 'BELUM_KOMPETEN'))
                                        <span class="lencana lencana-merah hanya-layar" style="font-weight: 700;">
                                            Belum Kompeten (BK)
                                        </span>
                                        <span class="hanya-cetak teks-cetak-belum-kompeten" style="font-size: 8pt;">
                                            BELUM KOMPETEN (BK)
                                        </span>
                                    @else
                                        <span class="lencana lencana-amber hanya-layar" style="font-size: 0.78rem;">
                                            Menunggu Penilaian
                                        </span>
                                        <span class="hanya-cetak teks-cetak-peringatan" style="font-size: 7.5pt;">
                                            Menunggu Ujian / Nilai
                                        </span>
                                    @endif
                                </td>
                                <td>
                                    <span style="color: {{ !empty($catatanUnit) ? '#1e293b' : '#64748b' }}; font-size: 0.82rem; line-height: 1.35; display: block;">
                                        {{ $isDitolak ? 'Tidak dapat diuji karena permohonan pendaftaran APL.01 ditolak.' : ($catatanUnit ?? ($isSelesai ? 'Memenuhi kriteria unjuk kerja.' : 'Belum diuji oleh Asesor Penguji.')) }}
                                    </span>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="5" style="text-align: center; color: var(--abu-teks); padding: 1.25rem;">
                                    Unit kompetensi pada skema ini belum dikonfigurasi.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <!-- =========================================================================
                 5. CATATAN ASESOR & PENGESAHAN TANDA TANGAN (HINDARI TERPOTONG HALAMAN)
                 ========================================================================= -->
            <div class="hindari-potong" style="margin-top: 0.75rem;">
                
                <!-- Kotak Catatan / Umpan Balik Asesor -->
                <div style="background: #f8fafc; border: 1px solid #cbd5e1; border-radius: 8px; padding: 0.75rem 1rem; margin-bottom: 0.75rem;">
                    <div style="font-weight: 700; color: #0f172a; margin-bottom: 0.25rem; font-size: 8.5pt;">
                        <i class="fa-solid fa-comment-dots no-print" style="color: var(--biru-utama); margin-right: 0.35rem;"></i>
                        Umpan Balik & Catatan Rekomendasi Asesor:
                    </div>
                    <div style="color: #334155; font-size: 8pt; line-height: 1.4; font-style: italic;">
                        @if($isDitolak)
                            "Permohonan pendaftaran skema ditolak pada tahap verifikasi berkas FR.APL.01. {{ $p->catatan_verifikasi ? 'Catatan verifikator: ' . $p->catatan_verifikasi : '' }}"
                        @elseif($isSelesai && $p->rekomendasi)
                            "{{ $p->rekomendasi->catatan_rekomendasi ?? 'Asesi telah menyelesaikan seluruh tahapan asesmen sesuai dengan skema sertifikasi yang diujikan.' }}"
                        @else
                            "Pelaksanaan uji kompetensi sedang berlangsung atau belum selesai disahkan secara final oleh Asesor Penguji."
                        @endif
                    </div>
                    @if($isSelesai && $p->rekomendasi && $p->rekomendasi->tanggal_rekomendasi)
                        <div style="font-size: 7.5pt; color: #64748b; margin-top: 0.35rem;">
                            Tanggal Rekomendasi: <strong>{{ date('d F Y', strtotime($p->rekomendasi->tanggal_rekomendasi)) }}</strong>
                        </div>
                    @endif
                </div>

                <!-- Area Tanda Tangan Resmi (2 Kolom Berdampingan) -->
                <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 1rem; text-align: center; font-size: 8pt; margin-bottom: 0.75rem;">
                    
                    <!-- Kolom Kiri: Asesi -->
                    <div style="border: 1px solid #cbd5e1; border-radius: 8px; padding: 0.65rem; background: #ffffff;">
                        <div style="font-size: 7.2pt; font-weight: 800; color: #475569; text-transform: uppercase; margin-bottom: 0.35rem;">
                            Peserta Uji (Asesi):
                        </div>
                        <div style="height: 52px; display: flex; align-items: center; justify-content: center; margin-bottom: 0.35rem;">
                            @if($ttdAsesi)
                                @if(\Illuminate\Support\Str::startsWith($ttdAsesi, 'data:image') || \Illuminate\Support\Str::startsWith($ttdAsesi, '<svg'))
                                    <img src="{{ $ttdAsesi }}" alt="TTD Asesi" style="max-height: 48px; max-width: 140px; object-fit: contain;">
                                @else
                                    <img src="{{ asset($ttdAsesi) }}" alt="TTD Asesi" style="max-height: 48px; max-width: 140px; object-fit: contain;">
                                @endif
                            @else
                                <span style="font-size: 7.2pt; color: #94a3b8; font-style: italic;">
                                    (Tanda Tangan Digital Terverifikasi)
                                </span>
                            @endif
                        </div>
                        <div style="font-weight: 800; color: #0f172a; text-decoration: underline; font-size: 8.5pt;">
                            {{ $namaAsesi }}
                        </div>
                        <div style="font-size: 7pt; color: #64748b; margin-top: 1px;">
                            No. Registrasi: {{ $p->nomor_pendaftaran }}
                        </div>
                    </div>

                    <!-- Kolom Kanan: Asesor Penguji -->
                    <div style="border: 1px solid #cbd5e1; border-radius: 8px; padding: 0.65rem; background: #ffffff;">
                        <div style="font-size: 7.2pt; font-weight: 800; color: #475569; text-transform: uppercase; margin-bottom: 0.35rem;">
                            Disahkan Oleh Asesor Penguji:
                        </div>
                        <div style="height: 52px; display: flex; align-items: center; justify-content: center; margin-bottom: 0.35rem;">
                            @if($ttdAsesor)
                                @if(\Illuminate\Support\Str::startsWith($ttdAsesor, 'data:image') || \Illuminate\Support\Str::startsWith($ttdAsesor, '<svg'))
                                    <img src="{{ $ttdAsesor }}" alt="TTD Asesor" style="max-height: 48px; max-width: 140px; object-fit: contain;">
                                @else
                                    <img src="{{ asset($ttdAsesor) }}" alt="TTD Asesor" style="max-height: 48px; max-width: 140px; object-fit: contain;">
                                @endif
                            @else
                                <span style="font-size: 7.2pt; color: #94a3b8; font-style: italic;">
                                    (Tanda Tangan Digital Terverifikasi)
                                </span>
                            @endif
                        </div>
                        <div style="font-weight: 800; color: #0f172a; text-decoration: underline; font-size: 8.5pt;">
                            {{ $isDitolak ? '-' : $namaAsesor }}
                        </div>
                        <div style="font-size: 7pt; color: #64748b; margin-top: 1px;">
                            Asesor Kompetensi LSP SMKN 1 Gunungputri
                        </div>
                    </div>
                </div>

                <!-- Footer Resmi Lembar Cetak (Hanya Muncul di Kertas/PDF) -->
                <div class="hanya-cetak" style="border-top: 1px dashed #cbd5e1; padding-top: 6px; margin-top: 6px; font-size: 6.8pt; color: #64748b; display: flex; justify-content: space-between;">
                    <span>Dokumen ini diterbitkan secara sah melalui Sistem Informasi Manajemen Sertifikasi {{ $pengaturan->nama_lsp ?? 'LSP SMKN 1 Gunungputri' }}.</span>
                    <span>Dicetak: {{ date('d/m/Y H:i') }} WIB</span>
                </div>

                <!-- Tautan Dokumen Resmi & Tombol Lanjutan di Layar Web (no-print) -->
                @if($isSelesai && $p->rekomendasi)
                    <div style="margin-top: 1rem; padding-top: 0.75rem; border-top: 1px dashed #cbd5e1; display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 0.75rem;" class="tombol-aksi-cetak no-print">
                        <span style="font-size: 0.84rem; color: #64748b;">
                            <i class="fa-solid fa-file-lines" style="margin-right: 0.35rem;"></i> Dokumen Rekaman Asesmen Resmi (FR.AK.02):
                        </span>
                        <div style="display: flex; gap: 0.5rem;">
                            @if(Route::has('dokumen-asesmen.ak02.cetak'))
                                <a href="{{ route('dokumen-asesmen.ak02.cetak', $p->id) }}" target="_blank" class="tombol tombol-sekunder" style="font-size: 0.84rem; padding: 0.4rem 0.85rem;">
                                    <i class="fa-solid fa-external-link" style="margin-right: 0.35rem;"></i> Cetak Lembar FR.AK.02 Resmi BNSP
                                </a>
                            @endif
                            @if(Route::has('dokumen-asesmen.ak03.show'))
                                <a href="{{ route('dokumen-asesmen.ak03.show', $p->id) }}" class="tombol tombol-utama" style="font-size: 0.84rem; padding: 0.4rem 0.85rem; background: var(--biru-utama);">
                                    <i class="fa-solid fa-pen-to-square" style="margin-right: 0.35rem;"></i> Umpan Balik Asesi (FR.AK.03)
                                </a>
                            @endif
                        </div>
                    </div>
                @elseif(!$isDitolak)
                    <div class="hanya-layar no-print" style="margin-top: 1rem; text-align: center; padding: 1rem; background: #f8fafc; border-radius: 8px; border: 1.5px dashed #cbd5e1;">
                        <h4 style="color: var(--biru-malam); margin-bottom: 0.25rem; font-weight: 700; font-size: 0.95rem;">Tahapan Asesmen Belum Selesai</h4>
                        <p style="color: #64748b; font-size: 0.85rem; max-width: 550px; margin: 0 auto 0.85rem auto;">
                            Hasil kelulusan dan nilai uji kompetensi final belum disahkan oleh Asesor Penguji. Silakan lanjutkan tahapan ujian asesmen Anda.
                        </p>
                        <div style="display: flex; justify-content: center; gap: 0.75rem; flex-wrap: wrap;">
                            <a href="{{ route('asesi.dasbor') }}" class="tombol tombol-sekunder" style="font-size: 0.85rem;">
                                <i class="fa-solid fa-arrow-left" style="margin-right: 0.35rem;"></i> Dasbor Utama
                            </a>
                            <a href="{{ route('asesi.tahapan', ['step' => 5, 'pendaftaran_id' => $p->id]) }}" class="tombol tombol-utama" style="font-size: 0.85rem;">
                                Buka Ruang Ujian &rarr;
                            </a>
                        </div>
                    </div>
                @endif
            </div>

        </div>
    @empty
        <div class="kartu" style="text-align: center; padding: 3.5rem 2rem; color: var(--abu-teks); border-radius: 12px; background: #ffffff;">
            <div style="font-size: 3rem; color: #cbd5e1; margin-bottom: 1rem;">
                <i class="fa-solid fa-clipboard-list"></i>
            </div>
            <h3 style="color: var(--biru-malam); margin-bottom: 0.5rem;">Belum Ada Riwayat Nilai Asesmen</h3>
            <p style="max-width: 500px; margin: 0 auto 1.5rem auto; font-size: 0.92rem;">
                Anda belum memiliki data pendaftaran asesmen yang telah selesai diuji. Silakan daftarkan diri Anda pada skema sertifikasi yang tersedia.
            </p>
            <a href="{{ route('asesi.pendaftaran') }}" class="tombol tombol-utama no-print">
                Mendaftar Skema Sertifikasi &rarr;
            </a>
        </div>
    @endforelse
</div>
@endsection
