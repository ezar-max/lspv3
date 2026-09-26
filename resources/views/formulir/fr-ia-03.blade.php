@extends('tata-letak.dasbor')

@section('judul', 'FR.IA.03 - Pertanyaan untuk Mendukung Observasi')

@push('css')
    <link rel="stylesheet" href="{{ asset('css/formulir/formulir-bnsp.css') }}">
    <style>
        /* Gaya Khusus Format Resmi BNSP Sesuai Gambar FR.IA.03 */
        .dokumen-ia03 {
            background-color: #ffffff;
            color: #000000;
            font-family: 'Segoe UI', Arial, sans-serif;
            font-size: 0.88rem;
            line-height: 1.5;
            padding: 2.5rem 3rem;
            margin: 0 auto 2rem auto;
            max-width: 950px;
            box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.08), 0 2px 4px -1px rgba(0, 0, 0, 0.04);
            border: 1px solid #cbd5e1;
        }

        .header-judul-ia03 {
            font-size: 0.95rem;
            font-weight: 800;
            color: #000000;
            margin-bottom: 0.85rem;
            letter-spacing: 0.3px;
            text-transform: uppercase;
        }

        .tabel-ia03 {
            width: 100%;
            border-collapse: collapse;
            border: 1px solid #000000;
            margin-bottom: 0.5rem;
            font-size: 0.86rem;
        }

        .tabel-ia03 th, 
        .tabel-ia03 td {
            border: 1px solid #000000;
            padding: 6px 10px;
            color: #000000;
        }

        .tabel-ia03 th {
            background-color: #ffffff;
            font-weight: 700;
            text-align: center;
        }

        .catatan-coret {
            font-size: 0.75rem;
            font-style: italic;
            color: #222222;
            margin-top: -0.25rem;
            margin-bottom: 1.25rem;
        }

        /* Kotak Panduan Asesor Bergaris Hitam Tegas Sesuai Gambar */
        .kotak-panduan-ia03 {
            border: 1px solid #000000;
            padding: 0.85rem 1.15rem;
            margin-bottom: 1.35rem;
            background-color: #ffffff;
        }

        .kotak-panduan-ia03 .judul-panduan {
            font-weight: 800;
            font-size: 0.88rem;
            color: #000000;
            margin-bottom: 0.45rem;
            text-transform: uppercase;
        }

        .kotak-panduan-ia03 ul {
            margin: 0;
            padding-left: 1.2rem;
            font-size: 0.84rem;
            color: #000000;
            line-height: 1.6;
        }

        .kotak-panduan-ia03 li {
            margin-bottom: 0.35rem;
        }

        /* Tabel Pertanyaan & Tanggapan Format BNSP */
        .tabel-pertanyaan-ia03 {
            width: 100%;
            border-collapse: collapse;
            border: 1px solid #000000;
            margin-bottom: 1.35rem;
            font-size: 0.86rem;
        }

        .tabel-pertanyaan-ia03 th, 
        .tabel-pertanyaan-ia03 td {
            border: 1px solid #000000;
            padding: 6px 8px;
            color: #000000;
        }

        .tabel-pertanyaan-ia03 th {
            background-color: #ffffff;
            font-weight: 700;
            text-align: center;
        }

        .input-pertanyaan-ia03 {
            width: 100%;
            border: 1px solid transparent;
            background-color: transparent;
            font-weight: 600;
            font-family: inherit;
            font-size: 0.86rem;
            color: #000000;
            box-sizing: border-box;
            resize: vertical;
            line-height: 1.45;
            padding: 2px 4px;
        }

        .input-pertanyaan-ia03:focus {
            outline: none;
            background-color: #f8fafc;
            border: 1px solid #94a3b8;
            border-radius: 2px;
        }

        .input-tanggapan-ia03 {
            width: 100%;
            border: 1px solid transparent;
            background-color: transparent;
            font-family: inherit;
            font-size: 0.85rem;
            color: #000000;
            box-sizing: border-box;
            resize: vertical;
            line-height: 1.45;
            padding: 4px;
            min-height: 48px;
        }

        .input-tanggapan-ia03:focus {
            outline: none;
            background-color: #f8fafc;
            border: 1px solid #94a3b8;
            border-radius: 2px;
        }

        .radio-bnsp {
            width: 17px;
            height: 17px;
            cursor: pointer;
            accent-color: #0f172a;
            vertical-align: middle;
        }

        /* Kotak Umpan Balik untuk Asesi */
        .kotak-umpan-balik-ia03 {
            border: 1px solid #000000;
            padding: 0.85rem 1rem;
            margin-bottom: 1.35rem;
            background-color: #ffffff;
        }

        .label-umpan-balik {
            font-weight: 700;
            font-size: 0.88rem;
            color: #000000;
            margin-bottom: 0.4rem;
            display: block;
        }

        .textarea-umpan-balik {
            width: 100%;
            border: 1px solid transparent;
            background-color: transparent;
            font-family: inherit;
            font-size: 0.86rem;
            color: #000000;
            box-sizing: border-box;
            resize: vertical;
            line-height: 1.5;
            min-height: 70px;
            padding: 4px;
        }

        .textarea-umpan-balik:focus {
            outline: none;
            background-color: #f8fafc;
            border: 1px solid #94a3b8;
            border-radius: 2px;
        }

        /* Tabel Tanda Tangan Format BNSP Sesuai Gambar */
        .tabel-ttd-ia03 {
            width: 100%;
            border-collapse: collapse;
            border: 1px solid #000000;
            margin-top: 1.25rem;
            font-size: 0.86rem;
        }

        .tabel-ttd-ia03 td {
            border: 1px solid #000000;
            padding: 6px 10px;
            color: #000000;
        }

        .page-break-divider {
            border-top: 2px dashed #cbd5e1;
            margin: 2.5rem 0 2rem 0;
            position: relative;
        }

        .page-break-divider::after {
            content: "Halaman Berikutnya (Standar Dokumen BNSP)";
            position: absolute;
            top: -10px;
            left: 50%;
            transform: translateX(-50%);
            background: #ffffff;
            padding: 0 10px;
            font-size: 0.72rem;
            color: #64748b;
            font-style: italic;
        }

        @media print {
            .wadah-formulir-bnsp {
                padding: 0;
                margin: 0;
            }
            .dokumen-ia03 {
                box-shadow: none;
                border: none;
                padding: 0;
                max-width: 100%;
            }
            .page-break-divider {
                border: none;
                page-break-after: always;
                margin: 0;
                height: 0;
            }
            .page-break-divider::after {
                display: none;
            }
            .input-pertanyaan-ia03,
            .input-tanggapan-ia03,
            .textarea-umpan-balik {
                border: none !important;
                background: transparent !important;
            }
        }
    </style>
@endpush

@section('konten')
<div class="wadah-formulir-bnsp animasi-slide {{ auth()->check() && auth()->user()->peran === 'asesi' ? 'mode-read-only-asesi' : '' }}">
    
    @php
        $isAsesi = auth()->check() && auth()->user()->peran === 'asesi';
        $asesorNama = $pendaftaran->asesor->nama_lengkap ?? (auth()->user()->peran === 'asesor' ? auth()->user()->nama_lengkap : 'Asesor LSP');
        $asesorMet = $pendaftaran->asesor->nomor_registrasi ?? 'MET.000.004455.2023';
        $asesiNama = $pendaftaran->asesi->nama_lengkap ?? 'Nama Asesi';
        $asesorTtd = $pendaftaran->tanda_tangan_asesor ?? (auth()->user()->tanda_tangan ?? null);
        $asesiTtd = $iaRecord->data_jawaban['ttd_asesi'] ?? ($pendaftaran->tanda_tangan_asesi ?? null);
        $tglTtdAsesi = $iaRecord->data_jawaban['tgl_ttd_asesi'] ?? null;

        $savedData = $iaRecord->data_jawaban ?? [];
        $savedKelompokSoal = $savedData['kelompok_soal'] ?? [];
        $umpanBalikVal = $savedData['umpan_balik'] ?? ($savedData['catatan'] ?? ($iaRecord->catatan_asesor ?? 'Asesi menunjukkan pemahaman yang sangat baik terhadap konsep kerja, kepatuhan K3, dan penanganan aspek kritis kejuruan.'));

        // Data Unit Kompetensi dari Skema Database
        $allUnits = $pendaftaran->skema->unitKompetensi ?? collect();
        $totalUnits = $allUnits->count();

        // Distribusikan seluruh unit kompetensi ke 3 Kelompok Pekerjaan sesuai template 3 halaman BNSP
        $kelompokUnits = [
            1 => collect(),
            2 => collect(),
            3 => collect(),
        ];

        if ($totalUnits <= 1) {
            $kelompokUnits[1] = $allUnits;
            $kelompokUnits[2] = $allUnits;
            $kelompokUnits[3] = $allUnits;
        } elseif ($totalUnits === 2) {
            $kelompokUnits[1] = $allUnits->slice(0, 1);
            $kelompokUnits[2] = $allUnits->slice(1, 1);
            $kelompokUnits[3] = $allUnits;
        } else {
            $base = intdiv($totalUnits, 3);
            $remainder = $totalUnits % 3;
            $s1 = $base + ($remainder > 0 ? 1 : 0);
            $s2 = $base + ($remainder > 1 ? 1 : 0);
            $kelompokUnits[1] = $allUnits->slice(0, $s1);
            $kelompokUnits[2] = $allUnits->slice($s1, $s2);
            $kelompokUnits[3] = $allUnits->slice($s1 + $s2);
        }
    @endphp

    <!-- ACTION BAR ATAS (NAVIGASI RESMI) -->
    @include('komponen.action-bar-formulir', [
        'kembaliRoute' => (!empty($pendaftaran->skema_id) ? route('asesor.mapa', ['skema_id' => $pendaftaran->skema_id]) : route('asesor.daftar-peserta')),
        'kodeForm' => 'FR.IA.03',
        'namaForm' => 'FR.IA.03 Pertanyaan untuk Mendukung Observasi',
        'pendaftaranId' => $pendaftaran->id,
        'canSubmit' => !$isAsesi,
        'saveFormId' => 'form-ia-03',
        'submitLabel' => 'Simpan Formulir',
        'prevForm' => ['route' => route('formulir.ia02', ['pendaftaranId' => $pendaftaran->id, 'skema_id' => $pendaftaran->skema_id]), 'label' => 'FR.IA.02 Tugas Praktik'],
        'nextForm' => ['route' => route('formulir.ia04a', ['pendaftaranId' => $pendaftaran->id, 'skema_id' => $pendaftaran->skema_id]), 'label' => 'FR.IA.04A Proyek Singkat']
    ])

    @if($isAsesi)
        @include('komponen.banner-readonly-asesi', [
            'pesan' => 'Formulir Pertanyaan untuk Mendukung Observasi (FR.IA.03) ini dinilai dan diisi langsung oleh Asesor Penguji Anda.'
        ])
    @endif

    <!-- DOKUMEN STANDAR RESMI BNSP PERSIS GAMBAR -->
    <div class="dokumen-ia03">
        
        <form id="form-ia-03" method="POST" action="{{ route('formulir.ia.simpan', ['kodeForm' => 'FR.IA.03', 'pendaftaranId' => $pendaftaran->id]) }}">
            @csrf

            <!-- ====================================================================
                 HALAMAN 1: IDENTITAS, PANDUAN ASESOR, & KELOMPOK PEKERJAAN 1
                 ==================================================================== -->
            
            <!-- HEADER JUDUL RESMI (PERSIS GAMBAR) -->
            <div class="header-judul-ia03">
                FR.IA.03. &nbsp; PERTANYAAN UNTUK MENDUKUNG OBSERVASI
            </div>

            <!-- TABEL IDENTITAS (PERSIS GAMBAR) -->
            <table class="tabel-ia03">
                <tr>
                    <td rowspan="2" style="width: 25%; font-weight: 700; vertical-align: middle;">
                        Skema Sertifikasi<br>
                        <span style="font-weight: normal; font-size: 0.82rem;">(KKNI/Okupasi/Klaster)</span>
                    </td>
                    <td style="width: 14%; font-weight: 600;">Judul</td>
                    <td style="width: 2%; text-align: center;">:</td>
                    <td style="font-weight: 700;">{{ $pendaftaran->skema->nama_skema ?? 'Skema Sertifikasi' }}</td>
                </tr>
                <tr>
                    <td style="font-weight: 600;">Nomor</td>
                    <td style="text-align: center;">:</td>
                    <td style="font-weight: 700;">{{ $pendaftaran->skema->kode_skema ?? '-' }}</td>
                </tr>
                <tr>
                    <td colspan="2" style="font-weight: 600;">TUK</td>
                    <td style="text-align: center;">:</td>
                    <td>Sewaktu/Tempat Kerja/Mandiri*</td>
                </tr>
                <tr>
                    <td colspan="2" style="font-weight: 600;">Nama Asesor</td>
                    <td style="text-align: center;">:</td>
                    <td><strong>{{ $asesorNama }}</strong></td>
                </tr>
                <tr>
                    <td colspan="2" style="font-weight: 600;">Nama Asesi</td>
                    <td style="text-align: center;">:</td>
                    <td><strong>{{ $asesiNama }}</strong></td>
                </tr>
                <tr>
                    <td colspan="2" style="font-weight: 600;">Tanggal</td>
                    <td style="text-align: center;">:</td>
                    <td>{{ date('d-m-Y') }}</td>
                </tr>
            </table>
            <div class="catatan-coret">*Coret yang tidak perlu</div>

            <!-- PANDUAN BAGI ASESOR (PERSIS GAMBAR) -->
            <div class="kotak-panduan-ia03">
                <div class="judul-panduan">PANDUAN BAGI ASESOR</div>
                <ul>
                    <li>Formulir ini di isi oleh asesor kompetensi dapat sebelum, pada saat atau setelah melakukan asesmen dengan metode observasi demonstrasi.</li>
                    <li>Pertanyaan dibuat dengan tujuan untuk menggali, dapat berisi pertanyaan yang berkaitan dengan dimensi kompetensi, batasan variabel dan aspek kritis yang relevan dengan skenario tugas dan praktik demonstrasi.</li>
                    <li>Jika pertanyaan disampaikan sebelum asesi melakukan praktik demonstrasi, maka pertanyaan dibuat berkaitan dengan aspek K3L, SOP, penggunaan peralatan dan perlengkapan.</li>
                    <li>Jika setelah asesi melakukan praktik demonstrasi terdapat item pertanyaan pendukung observasi telah terpenuhi, maka pertanyaan tersebut tidak perlu ditanyakan lagi dan cukup memberi catatan bahwa sudah terpenuhi pada saat tugas praktek demonstrasi pada kolom tanggapan</li>
                    <li>Jika pada saat observasi ada hal yang perlu dikonfirmasi sedangkan di instrumen daftar pertanyaan pendukung observasi tidak ada, maka asesor dapat memberikan pertanyaan dengan syarat pertanyaan harus berkaitan dengan tugas praktek demonstrasi. Jika dilakukan, asesor harus mencatat dalam instrumen pertanyaan pendukung observasi.</li>
                    <li>Tanggapan asesi ditulis pada kolom tanggapan.</li>
                </ul>
            </div>

            @php
                // Setup default pertanyaan dan tanggapan per kelompok
                $generateDefaultQ = function($kIdx, $qIdx, $units) use ($pendaftaran) {
                    $uSample = $units->pluck('judul_unit')->filter()->take(2)->implode(' & ') ?: ($pendaftaran->skema->nama_skema ?? 'Kejuruan');
                    if ($qIdx === 1) {
                        return [
                            'tanya' => "Bagaimanakah Anda memastikan penerapan prosedur K3L, kesiapan peralatan kerja, serta ketaatan instruksi kerja (SOP) sebelum memulai tugas pada unit: {$uSample}?",
                            'jawab' => 'Asesi menjelaskan tahapan pemeriksaan keselamatan kerja, penggunaan APD wajib standar industri, dan kesiapan operasional peralatan sesuai SOP kejuruan.'
                        ];
                    } elseif ($qIdx === 2) {
                        return [
                            'tanya' => "Tindakan teknis apa yang Anda lakukan apabila menjumpai kendala operasional, deviasi spesifikasi, atau situasi kritis saat melaksanakan pekerjaan ini?",
                            'jawab' => 'Asesi mampu mengidentifikasi sumber masalah secara tepat, menghentikan proses darurat sesuai SOP, dan mengambil tindakan korektif secara terukur dan aman.'
                        ];
                    } else {
                        return [
                            'tanya' => "Bagaimana cara Anda memverifikasi bahwa dimensi hasil kerja dan standar mutu pada unit ini telah memenuhi kriteria toleransi yang disyaratkan?",
                            'jawab' => 'Asesi mendemonstrasikan metode pengukuran dan pemeriksaan kualitas hasil kerja menggunakan alat ukur presisi dan membandingkannya pada lembar standar mutu.'
                        ];
                    }
                };
            @endphp

            @for($k = 1; $k <= 3; $k++)
                @php
                    $unitsInK = $kelompokUnits[$k]->values();
                    $maxRows = max($unitsInK->count(), 3);
                    $leftColRowspan = $maxRows + 2; // header No/Kode/Judul + rows + baris Dst..
                @endphp

                @if($k === 2 || $k === 3)
                    <!-- PEMBATAS HALAMAN STANDAR DOKUMEN BNSP (HALAMAN {{ $k }}) -->
                    <div class="page-break-divider"></div>
                @endif

                <!-- TABEL KELOMPOK PEKERJAAN (BENTUK PERSIS GAMBAR BNSP) -->
                <table class="tabel-ia03" style="margin-bottom: 0.85rem;">
                    <tbody>
                        <!-- Baris 1: Kolom Kiri "Kelompok Pekerjaan X" menyatu dari atas + Header No/Kode/Judul -->
                        <tr>
                            <td rowspan="{{ $leftColRowspan }}" style="width: 25%; text-align: center; vertical-align: middle; font-weight: 700; font-size: 0.95rem; border: 1px solid #000000; padding: 10px; background-color: #ffffff;">
                                Kelompok<br>Pekerjaan {{ $k }}
                            </td>
                            <th style="width: 8%; text-align: center; border: 1px solid #000000; padding: 6px 4px; font-weight: 700; background-color: #ffffff;">No.</th>
                            <th style="width: 28%; text-align: center; border: 1px solid #000000; padding: 6px 8px; font-weight: 700; background-color: #ffffff;">Kode Unit</th>
                            <th style="text-align: center; border: 1px solid #000000; padding: 6px 8px; font-weight: 700; background-color: #ffffff;">Judul Unit</th>
                        </tr>

                        <!-- Baris 1, 2, 3.. Unit Kompetensi -->
                        @for($i = 0; $i < $maxRows; $i++)
                            @php $u = $unitsInK->get($i); @endphp
                            <tr>
                                <td style="text-align: center; font-weight: 700; border: 1px solid #000000; padding: 6px 4px;">{{ $i + 1 }}.</td>
                                <td style="font-weight: 600; border: 1px solid #000000; padding: 6px 8px; font-family: monospace;">
                                    {{ $u ? $u->kode_unit : '' }}
                                </td>
                                <td style="font-weight: 600; border: 1px solid #000000; padding: 6px 8px;">
                                    {{ $u ? ($u->nama_unit ?? $u->judul_unit) : '' }}
                                </td>
                            </tr>
                        @endfor

                        <!-- Baris Dst.. (Persis Gambar) -->
                        <tr>
                            <td style="text-align: center; font-weight: 700; border: 1px solid #000000; padding: 6px 4px;">Dst..</td>
                            <td style="border: 1px solid #000000; padding: 6px 8px;"></td>
                            <td style="border: 1px solid #000000; padding: 6px 8px;"></td>
                        </tr>
                    </tbody>
                </table>

                <!-- TABEL PERTANYAAN UNTUK KELOMPOK PEKERJAAN (BENTUK PERSIS GAMBAR BNSP) -->
                <table class="tabel-pertanyaan-ia03">
                    <thead>
                        <tr>
                            <th colspan="2" rowspan="2" style="text-align: center; font-weight: 700; vertical-align: middle;">
                                Pertanyaan
                            </th>
                            <th colspan="2" style="width: 14%; text-align: center; font-weight: 700;">
                                Pencapaian
                            </th>
                        </tr>
                        <tr>
                            <th style="width: 7%; text-align: center; font-weight: 700;">Ya</th>
                            <th style="width: 7%; text-align: center; font-weight: 700;">Tdk</th>
                        </tr>
                    </thead>
                    <tbody>
                        @for($q = 1; $q <= 3; $q++)
                            @php
                                $defQ = $generateDefaultQ($k, $q, $unitsInK);
                                $valPertanyaan = $savedKelompokSoal[$k][$q]['pertanyaan'] ?? ($savedData['pertanyaan']["{$k}_{$q}"] ?? $defQ['tanya']);
                                $valTanggapan = $savedKelompokSoal[$k][$q]['tanggapan'] ?? ($savedData['respon']["{$k}_{$q}"] ?? $defQ['jawab']);
                                $valPencapaian = $savedKelompokSoal[$k][$q]['pencapaian'] ?? ($savedData['pencapaian']["{$k}_{$q}"] ?? 'Ya');
                            @endphp

                            <!-- BARIS PERTANYAAN (PERSIS GAMBAR) -->
                            <tr>
                                <td style="width: 5%; text-align: center; font-weight: 700; vertical-align: top; border-bottom: none;">
                                    {{ $q }}.
                                </td>
                                <td style="vertical-align: top; border-bottom: none;">
                                    <textarea name="kelompok_soal[{{ $k }}][{{ $q }}][pertanyaan]" class="input-pertanyaan-ia03" rows="2" placeholder="Tuliskan pertanyaan pendukung observasi..." {{ $isAsesi ? 'readonly' : '' }}>{{ $valPertanyaan }}</textarea>
                                </td>
                                <td style="border-bottom: none;"></td>
                                <td style="border-bottom: none;"></td>
                            </tr>

                            <!-- BARIS TANGGAPAN & CHECKBOX YA / TDK (PERSIS GAMBAR) -->
                            <tr>
                                <td style="border-top: none;"></td>
                                <td style="vertical-align: top; border-top: none; padding-top: 0;">
                                    <div style="font-weight: 700; font-size: 0.85rem; color: #000000; margin-bottom: 0.2rem;">
                                        Tanggapan:
                                    </div>
                                    <textarea name="kelompok_soal[{{ $k }}][{{ $q }}][tanggapan]" class="input-tanggapan-ia03" rows="3" placeholder="{{ $isAsesi ? 'Tanggapan dicatat oleh Asesor...' : 'Tuliskan catatan respons/tanggapan asesi...' }}" {{ $isAsesi ? 'readonly' : '' }}>{{ $valTanggapan }}</textarea>
                                </td>
                                <td style="text-align: center; vertical-align: middle; border-top: none;">
                                    <input type="radio" name="kelompok_soal[{{ $k }}][{{ $q }}][pencapaian]" value="Ya" class="radio-bnsp" {{ $valPencapaian === 'Ya' ? 'checked' : '' }} {{ $isAsesi ? 'disabled' : '' }}>
                                </td>
                                <td style="text-align: center; vertical-align: middle; border-top: none;">
                                    <input type="radio" name="kelompok_soal[{{ $k }}][{{ $q }}][pencapaian]" value="Tdk" class="radio-bnsp" {{ $valPencapaian === 'Tdk' ? 'checked' : '' }} {{ $isAsesi ? 'disabled' : '' }}>
                                </td>
                            </tr>
                        @endfor
                    </tbody>
                </table>
            @endfor

            <!-- UMPAN BALIK UNTUK ASESI (PERSIS GAMBAR HALAMAN 3) -->
            <div class="kotak-umpan-balik-ia03">
                <label class="label-umpan-balik">Umpan balik untuk asesi:</label>
                <textarea name="umpan_balik" class="textarea-umpan-balik" rows="3" placeholder="Tuliskan catatan umpan balik dan evaluasi kualitatif untuk asesi..." {{ $isAsesi ? 'readonly' : '' }}>{{ $umpanBalikVal }}</textarea>
            </div>

            <!-- TABEL PENGESAHAN ASESI & ASESOR (PERSIS GAMBAR HALAMAN 3) -->
            <table class="tabel-ttd-ia03">
                <tr>
                    <td colspan="3" style="font-weight: 700; background-color: #ffffff;">Asesi :</td>
                </tr>
                <tr>
                    <td style="width: 25%; font-weight: 600;">Nama</td>
                    <td style="width: 2%; text-align: center;">:</td>
                    <td style="font-weight: 700;">{{ $asesiNama }}</td>
                </tr>
                <tr>
                    <td style="font-weight: 600; vertical-align: top;">Tanda tangan dan Tanggal</td>
                    <td style="text-align: center; vertical-align: top;">:</td>
                    <td style="min-height: 55px; vertical-align: middle;">
                        @if($asesiTtd)
                            <div style="display: flex; align-items: center; gap: 1rem;">
                                <img src="{{ $asesiTtd }}" alt="TTD Asesi" style="max-height: 45px;">
                                <span style="font-size: 0.82rem; color: #16a34a; font-weight: 700;">
                                    Terverifikasi ({{ $tglTtdAsesi ?? date('d-m-Y') }})
                                </span>
                            </div>
                        @else
                            @if($isAsesi)
                                <button type="submit" form="form-ttd-asesi-ia03" class="tombol tombol-utama tombol-sm no-print" style="background: #2563eb; border-color: #2563eb; font-size: 0.82rem;">
                                    Tanda Tangani Hasil Asesmen
                                </button>
                            @else
                                <span style="font-style: italic; color: #64748b; font-size: 0.82rem;">(Belum Ditandatangani Asesi)</span>
                            @endif
                        @endif
                    </td>
                </tr>
                <tr>
                    <td colspan="3" style="font-weight: 700; background-color: #ffffff;">Asesor :</td>
                </tr>
                <tr>
                    <td style="font-weight: 600;">Nama</td>
                    <td style="text-align: center;">:</td>
                    <td style="font-weight: 700;">{{ $asesorNama }}</td>
                </tr>
                <tr>
                    <td style="font-weight: 600;">No. Reg</td>
                    <td style="text-align: center;">:</td>
                    <td>{{ $asesorMet }}</td>
                </tr>
                <tr>
                    <td style="font-weight: 600; vertical-align: top;">Tanda tangan dan Tanggal</td>
                    <td style="text-align: center; vertical-align: top;">:</td>
                    <td style="min-height: 55px; vertical-align: middle;">
                        @if($asesorTtd)
                            <div style="display: flex; align-items: center; gap: 1rem;">
                                <img src="{{ $asesorTtd }}" alt="TTD Asesor" style="max-height: 45px;">
                                <span style="font-size: 0.82rem; color: #475569;">
                                    {{ date('d-m-Y') }}
                                </span>
                            </div>
                        @else
                            <span style="font-style: italic; color: #64748b; font-size: 0.82rem;">(Tanda Tangan Digital Asesor) - {{ date('d-m-Y') }}</span>
                        @endif
                    </td>
                </tr>
            </table>

            <!-- FOOTER RESMI BNSP PERSIS GAMBAR -->
            <div style="font-size: 0.72rem; color: #333333; margin-top: 0.5rem; font-style: italic; line-height: 1.4;">
                Diadaptasi dari template yang disediakan di Departemen Pendidikan dan Pelatihan, Australia, Merancang instrumen asesmen untuk hasil yang berkualitas di VET, 2008 di VET, 2008
            </div>

        </form>

        @if($isAsesi)
            <form id="form-ttd-asesi-ia03" action="{{ route('formulir.ia.simpan-ttd-asesi', ['kodeForm' => 'FR.IA.03', 'pendaftaranId' => $pendaftaran->id]) }}" method="POST" style="display: none;">
                @csrf
            </form>
        @endif

    </div>

    <!-- NAVIGASI BAWAH FORMULIR -->
    @include('komponen.navigasi-form-bawah', [
        'kembaliRoute' => (!empty($pendaftaran->skema_id) ? route('asesor.mapa', ['skema_id' => $pendaftaran->skema_id]) : route('asesor.daftar-peserta'))
    ])

</div>
@endsection

@push('js')
    <script src="{{ asset('js/formulir/formulir-bnsp.js') }}"></script>
@endpush
