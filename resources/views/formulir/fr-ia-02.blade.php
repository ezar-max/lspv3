@extends('tata-letak.dasbor')

@section('judul', 'FR.IA.02 - Tugas Praktik Demonstrasi')

@push('css')
    <link rel="stylesheet" href="{{ asset('css/formulir/formulir-bnsp.css') }}">
    <style>
        /* Gaya Khusus Format Resmi BNSP Sesuai Gambar FR.IA.02 */
        .dokumen-ia02 {
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

        .header-judul-ia02 {
            font-size: 0.95rem;
            font-weight: 800;
            color: #000000;
            margin-bottom: 0.85rem;
            letter-spacing: 0.3px;
            text-transform: uppercase;
        }

        .tabel-ia02 {
            width: 100%;
            border-collapse: collapse;
            border: 1px solid #000000;
            margin-bottom: 0.5rem;
            font-size: 0.86rem;
        }

        .tabel-ia02 th, 
        .tabel-ia02 td {
            border: 1px solid #000000;
            padding: 6px 10px;
            color: #000000;
        }

        .tabel-ia02 th {
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

        .judul-bagian {
            font-weight: 800;
            font-size: 0.92rem;
            color: #000000;
            margin-top: 1.25rem;
            margin-bottom: 0.4rem;
        }

        .daftar-petunjuk {
            margin: 0 0 1.25rem 0;
            padding-left: 1.35rem;
            font-size: 0.86rem;
            color: #000000;
            line-height: 1.6;
        }

        .daftar-petunjuk li {
            margin-bottom: 0.2rem;
        }

        .label-skenario {
            font-weight: 700;
            font-size: 0.88rem;
            color: #000000;
            margin-top: 0.75rem;
            margin-bottom: 0.35rem;
            display: block;
        }

        .textarea-skenario {
            width: 100%;
            border: 1px solid #94a3b8;
            border-radius: 2px;
            padding: 0.5rem 0.65rem;
            font-family: inherit;
            font-size: 0.85rem;
            color: #000000;
            background-color: #ffffff;
            box-sizing: border-box;
            resize: vertical;
            line-height: 1.45;
        }

        .textarea-skenario:focus {
            outline: none;
            border-color: #000000;
        }

        .input-baris-ia02 {
            border: 1px solid #94a3b8;
            border-radius: 2px;
            padding: 0.35rem 0.6rem;
            font-size: 0.85rem;
            font-family: inherit;
            color: #000000;
            box-sizing: border-box;
        }

        .input-baris-ia02:focus {
            outline: none;
            border-color: #000000;
        }

        .grup-peralatan-waktu {
            margin-top: 0.85rem;
            margin-bottom: 1.5rem;
            display: flex;
            flex-direction: column;
            gap: 0.65rem;
        }

        .baris-label-input {
            display: flex;
            align-items: flex-start;
            gap: 0.75rem;
        }

        .baris-label-input .nama-label {
            width: 220px;
            font-weight: 700;
            color: #000000;
            font-size: 0.88rem;
            flex-shrink: 0;
            padding-top: 0.25rem;
        }

        .baris-label-input .isi-input {
            flex-grow: 1;
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
            background: #f8fafc;
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
            .dokumen-ia02 {
                box-shadow: none;
                border: none;
                padding: 0;
                max-width: 100%;
            }
            .page-break-divider {
                border-top: none;
                margin: 0;
                page-break-before: always;
            }
            .page-break-divider::after {
                display: none;
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

        $savedPV = $iaRecord->data_jawaban['penyusun_validator'] ?? [];
        $savedKelompok = $iaRecord->data_jawaban['kelompok_skenario'] ?? [];

        // Data Unit Kompetensi dari Skema Database
        $allUnits = $pendaftaran->skema->unitKompetensi ?? collect();
        $meta = $masterInst->additional_metadata ?? [];
        if (!is_array($meta)) $meta = json_decode($meta, true) ?: [];

        $kelompokSplit = (int)($meta['kelompok_split'] ?? 0);
        if ($kelompokSplit > 0 && $allUnits->count() > $kelompokSplit) {
            $kelompokList = [
                1 => $allUnits->slice(0, $kelompokSplit),
                2 => $allUnits->slice($kelompokSplit),
            ];
        } else {
            $kelompokList = [
                1 => $allUnits,
            ];
        }

        $judulTugasVal = $dataPraktik['judul_tugas'] ?? ($pendaftaran->skema ? 'Tugas Praktik Demonstrasi ' . $pendaftaran->skema->nama_skema : 'Tugas Praktik Demonstrasi');
        $skenarioVal = $dataPraktik['skenario'] ?? 'Anda diminta untuk mendemonstrasikan tugas praktik kerja sesuai dengan standar operasional prosedur (SOP) dan kriteria unjuk kerja yang berlaku.';
        $peralatanVal = is_array($dataPraktik['peralatan_bahan'] ?? null) ? implode("\n", $dataPraktik['peralatan_bahan']) : ($dataPraktik['peralatan_bahan'] ?? 'Peralatan dan bahan praktik standar sesuai unit kompetensi kejuruan.');
        $durasiVal = $dataPraktik['durasi_waktu'] ?? '120 Menit';
    @endphp

    <!-- ACTION BAR ATAS (NAVIGASI RESMI) -->
    @include('komponen.action-bar-formulir', [
        'kembaliRoute' => (!empty($pendaftaran->skema_id) ? route('asesor.mapa', ['skema_id' => $pendaftaran->skema_id]) : route('asesor.daftar-peserta')),
        'kodeForm' => 'FR.IA.02',
        'namaForm' => 'FR.IA.02 Tugas Praktik Demonstrasi',
        'pendaftaranId' => $pendaftaran->id,
        'canSubmit' => !$isAsesi,
        'saveFormId' => 'form-ia-02',
        'submitLabel' => 'Simpan Formulir',
        'prevForm' => ['route' => route('formulir.ia01', ['pendaftaranId' => $pendaftaran->id, 'skema_id' => $pendaftaran->skema_id]), 'label' => 'FR.IA.01 Observasi'],
        'nextForm' => ['route' => route('formulir.ia03', ['pendaftaranId' => $pendaftaran->id, 'skema_id' => $pendaftaran->skema_id]), 'label' => 'FR.IA.03 Pertanyaan Lisan']
    ])

    @if($isAsesi)
        @include('komponen.banner-readonly-asesi', [
            'pesan' => 'Formulir Tugas Praktik Demonstrasi (FR.IA.02) ini merupakan lembar penugasan resmi dari Asesor Penguji Anda.'
        ])
    @endif

    <!-- DOKUMEN STANDAR RESMI BNSP PERSIS GAMBAR -->
    <div class="dokumen-ia02">
        
        <form id="form-ia-02" method="POST" action="{{ route('formulir.ia.simpan', ['kodeForm' => 'FR.IA.02', 'pendaftaranId' => $pendaftaran->id]) }}">
            @csrf

            <!-- ====================================================================
                 HALAMAN 1: IDENTITAS, PETUNJUK, & KELOMPOK PEKERJAAN 1
                 ==================================================================== -->
            
            <!-- HEADER JUDUL RESMI (PERSIS GAMBAR) -->
            <div class="header-judul-ia02">
                FR.IA.02. &nbsp; TPD - TUGAS PRAKTIK DEMONSTRASI
            </div>

            <!-- TABEL IDENTITAS (PERSIS GAMBAR) -->
            <table class="tabel-ia02">
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

            <!-- A. PETUNJUK (PERSIS GAMBAR) -->
            <div class="judul-bagian">A. Petunjuk</div>
            <ol class="daftar-petunjuk">
                <li>Baca dan pelajari setiap instruksi kerja di bawah ini dengan cermat sebelum melaksanakan praktek</li>
                <li>Klarifikasi kepada asesor kompetensi apabila ada hal-hal yang belum jelas</li>
                <li>Laksanakan pekerjaan sesuai dengan urutan proses yang sudah ditetapkan</li>
                <li>Seluruh proses kerja mengacu kepada SOP/WI yang dipersyaratkan (Jika Ada)</li>
            </ol>

            <!-- B. SKENARIO TUGAS PRAKTIK DEMONSTRASI (PERSIS GAMBAR) -->
            <div class="judul-bagian">B. Skenario Tugas Praktik Demonstrasi</div>

            @foreach($kelompokList as $kIndex => $unitsInGroup)
                @php
                    $kSkenario = $savedKelompok[$kIndex]['skenario'] ?? ($kIndex === 1 ? $skenarioVal : 'Demonstrasikan seluruh proses kerja teknis pada kelompok pekerjaan ' . $kIndex . ' sesuai SOP yang berlaku.');
                    $kPeralatan = $savedKelompok[$kIndex]['peralatan'] ?? ($kIndex === 1 ? $peralatanVal : 'Peralatan dan instrumen kerja standar unit kompetensi.');
                    $kWaktu = $savedKelompok[$kIndex]['waktu'] ?? ($kIndex === 1 ? $durasiVal : '120 Menit');
                    
                    $displayUnits = $unitsInGroup->values();
                    $maxRows = max($displayUnits->count(), 3);
                    $leftColRowspan = $maxRows + 2; // 1 baris header (No/Kode/Judul) + maxRows unit + 1 baris Dst..
                @endphp

                @if($kIndex > 1)
                    <!-- PEMBATAS HALAMAN / KELOMPOK BERIKUTNYA -->
                    <div class="page-break-divider"></div>
                @endif

                <!-- TABEL KELOMPOK PEKERJAAN (BENTUK PERSIS GAMBAR BNSP) -->
                <table class="tabel-ia02" style="margin-bottom: 0.85rem;">
                    <tbody>
                        <!-- Baris 1: Kolom Kiri "Kelompok Pekerjaan X" menyatu dari atas + Header No/Kode/Judul -->
                        <tr>
                            <td rowspan="{{ $leftColRowspan }}" style="width: 25%; text-align: center; vertical-align: middle; font-weight: 700; font-size: 0.95rem; border: 1px solid #000000; padding: 10px; background-color: #ffffff;">
                                Kelompok<br>Pekerjaan {{ $kIndex }}
                            </td>
                            <th style="width: 8%; text-align: center; border: 1px solid #000000; padding: 6px 4px; font-weight: 700; background-color: #ffffff;">No.</th>
                            <th style="width: 28%; text-align: center; border: 1px solid #000000; padding: 6px 8px; font-weight: 700; background-color: #ffffff;">Kode Unit</th>
                            <th style="text-align: center; border: 1px solid #000000; padding: 6px 8px; font-weight: 700; background-color: #ffffff;">Judul Unit</th>
                        </tr>

                        <!-- Baris 1, 2, 3.. Unit Kompetensi -->
                        @for($i = 0; $i < $maxRows; $i++)
                            @php $u = $displayUnits->get($i); @endphp
                            <tr>
                                <td style="text-align: center; font-weight: 700; border: 1px solid #000000; padding: 6px 4px;">{{ $i + 1 }}.</td>
                                <td style="font-weight: 600; border: 1px solid #000000; padding: 6px 8px;">
                                    {{ $u ? $u->kode_unit : '' }}
                                </td>
                                <td style="font-weight: 600; border: 1px solid #000000; padding: 6px 8px;">
                                    {{ $u ? $u->judul_unit : '' }}
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

                <!-- ISIAN SKENARIO TUGAS PRAKTIK DEMONSTRASI (PERSIS GAMBAR) -->
                <label class="label-skenario">Skenario Tugas Praktik Demonstrasi:</label>
                <textarea name="kelompok_skenario[{{ $kIndex }}][skenario]" class="textarea-skenario" rows="4" placeholder="{{ $isAsesi ? 'Skenario demonstrasi praktik belum diisi oleh Asesor.' : 'Tuliskan skenario tugas praktik demonstrasi yang harus dilaksanakan oleh asesi...' }}" {{ $isAsesi ? 'readonly' : '' }}>{{ $kSkenario }}</textarea>
                
                @if($kIndex === 1)
                    <!-- Simpan juga ke field root skenario untuk backward compatibility -->
                    <input type="hidden" name="skenario" value="{{ $kSkenario }}">
                @endif

                <!-- PERLENGKAPAN, PERALATAN, DAN WAKTU (PERSIS GAMBAR) -->
                <div class="grup-peralatan-waktu">
                    <div class="baris-label-input">
                        <div class="nama-label">Perlengkapan dan Peralatan :</div>
                        <div class="isi-input">
                            <textarea name="kelompok_skenario[{{ $kIndex }}][peralatan]" class="textarea-skenario" rows="2" placeholder="{{ $isAsesi ? 'Daftar perlengkapan dan peralatan belum diisi.' : 'Sebutkan perlengkapan kerja, bahan uji, APD, dan peralatan yang digunakan...' }}" {{ $isAsesi ? 'readonly' : '' }}>{{ $kPeralatan }}</textarea>
                            @if($kIndex === 1)
                                <input type="hidden" name="peralatan_bahan" value="{{ $kPeralatan }}">
                            @endif
                        </div>
                    </div>

                    <div class="baris-label-input" style="align-items: center;">
                        <div class="nama-label">{{ $kIndex === 1 ? 'Durasi Waktu :' : 'Waktu :' }}</div>
                        <div class="isi-input">
                            <input type="text" name="kelompok_skenario[{{ $kIndex }}][waktu]" class="input-baris-ia02" style="max-width: 250px;" value="{{ $kWaktu }}" placeholder="Contoh: 120 Menit" {{ $isAsesi ? 'readonly' : '' }}>
                            @if($kIndex === 1)
                                <input type="hidden" name="durasi_waktu" value="{{ $kWaktu }}">
                            @endif
                        </div>
                    </div>
                </div>
            @endforeach

            <!-- ====================================================================
                 HALAMAN 3: ASESI, ASESOR, & PENYUSUN DAN VALIDATOR (PERSIS GAMBAR)
                 ==================================================================== -->
            <div class="page-break-divider"></div>

            <!-- TABEL PENGESAHAN ASESI & ASESOR (FORMAT VERTIKAL PERSIS GAMBAR HALAMAN 3) -->
            <table class="tabel-ia02" style="margin-bottom: 2rem;">
                <!-- SEKSI ASESI -->
                <tr>
                    <th colspan="3" style="text-align: left; font-weight: 800; padding: 6px 10px; font-size: 0.9rem; background: #ffffff; border: 1px solid #000000;">
                        ASESI :
                    </th>
                </tr>
                <tr>
                    <td style="width: 26%; font-weight: 600; padding: 6px 10px; border: 1px solid #000000;">Nama</td>
                    <td style="width: 3%; text-align: center; border: 1px solid #000000;">:</td>
                    <td style="padding: 6px 10px; border: 1px solid #000000;">
                        <strong>{{ $asesiNama }}</strong>
                    </td>
                </tr>
                <tr>
                    <td style="font-weight: 600; padding: 6px 10px; vertical-align: top; border: 1px solid #000000;">
                        Tanda tangan dan Tanggal
                    </td>
                    <td style="text-align: center; vertical-align: top; padding-top: 10px; border: 1px solid #000000;">:</td>
                    <td style="padding: 8px 10px; height: 80px; vertical-align: middle; border: 1px solid #000000;">
                        @if(!empty($asesiTtd))
                            <img src="{{ asset($asesiTtd) }}" alt="Tanda Tangan Asesi" style="max-height: 48px; display: block; margin-bottom: 4px;">
                            <span style="font-size: 0.72rem; color: #059669; font-weight: 700;">✓ Terverifikasi Digital</span>
                        @else
                            <span style="color: #94a3b8; font-style: italic; font-size: 0.82rem;">(Tanda Tangan Asesi)</span>
                        @endif
                        <div style="font-size: 0.78rem; color: #475569; margin-top: 2px;">{{ date('d-m-Y') }}</div>
                    </td>
                </tr>

                <!-- SEKSI ASESOR -->
                <tr>
                    <th colspan="3" style="text-align: left; font-weight: 800; padding: 6px 10px; font-size: 0.9rem; background: #ffffff; border: 1px solid #000000;">
                        ASESOR :
                    </th>
                </tr>
                <tr>
                    <td style="font-weight: 600; padding: 6px 10px; border: 1px solid #000000;">Nama</td>
                    <td style="text-align: center; border: 1px solid #000000;">:</td>
                    <td style="padding: 6px 10px; border: 1px solid #000000;">
                        <strong>{{ $asesorNama }}</strong>
                    </td>
                </tr>
                <tr>
                    <td style="font-weight: 600; padding: 6px 10px; border: 1px solid #000000;">No. Reg</td>
                    <td style="text-align: center; border: 1px solid #000000;">:</td>
                    <td style="padding: 6px 10px; border: 1px solid #000000;">
                        <strong>{{ $asesorMet }}</strong>
                    </td>
                </tr>
                <tr>
                    <td style="font-weight: 600; padding: 6px 10px; vertical-align: top; border: 1px solid #000000;">
                        Tanda tangan dan Tanggal
                    </td>
                    <td style="text-align: center; vertical-align: top; padding-top: 10px; border: 1px solid #000000;">:</td>
                    <td style="padding: 8px 10px; height: 80px; vertical-align: middle; border: 1px solid #000000;">
                        @if(!empty($asesorTtd))
                            <img src="{{ asset($asesorTtd) }}" alt="Tanda Tangan Asesor" style="max-height: 48px; display: block; margin-bottom: 4px;">
                            <span style="font-size: 0.72rem; color: #059669; font-weight: 700;">✓ Terverifikasi Asesor</span>
                        @else
                            <span style="color: #94a3b8; font-style: italic; font-size: 0.82rem;">(Tanda Tangan Asesor)</span>
                        @endif
                        <div style="font-size: 0.78rem; color: #475569; margin-top: 2px;">{{ date('d-m-Y') }}</div>
                    </td>
                </tr>
            </table>

            <!-- TABEL PENYUSUN DAN VALIDATOR (PERSIS GAMBAR HALAMAN 3) -->
            <div>
                <div style="font-weight: 800; font-size: 0.92rem; color: #000000; margin-bottom: 0.5rem; text-transform: uppercase;">
                    PENYUSUN DAN VALIDATOR
                </div>
                
                <table class="tabel-ia02">
                    <thead>
                        <tr>
                            <th style="width: 18%; text-align: center;">STATUS</th>
                            <th style="width: 6%; text-align: center;">NO</th>
                            <th style="width: 32%; text-align: center;">NAMA</th>
                            <th style="width: 22%; text-align: center;">NOMOR MET</th>
                            <th style="width: 22%; text-align: center;">TANDA TANGAN DAN TANGGAL</th>
                        </tr>
                    </thead>
                    <tbody>
                        <!-- PENYUSUN 1 -->
                        <tr>
                            <td rowspan="2" style="font-weight: 800; text-align: center; vertical-align: middle; background: #ffffff;">
                                PENYUSUN
                            </td>
                            <td style="text-align: center; font-weight: 700;">1</td>
                            <td style="padding: 6px 8px;"><strong>{{ $asesorNama }}</strong></td>
                            <td style="padding: 6px 8px;">{{ $asesorMet }}</td>
                            <td style="text-align: center; padding: 4px;">
                                @if(!empty($asesorTtd))
                                    <img src="{{ asset($asesorTtd) }}" alt="TTD" style="max-height: 36px; margin: 0 auto; display: block;">
                                @endif
                                <span style="font-size: 0.75rem; color: #475569;">{{ date('d/m/Y') }}</span>
                            </td>
                        </tr>
                        <!-- PENYUSUN 2 -->
                        <tr>
                            <td style="text-align: center; font-weight: 700;">2</td>
                            <td style="padding: 4px;">
                                <input type="text" name="penyusun_validator[penyusun_2_nama]" class="input-baris-ia02" style="width: 100%;" value="{{ $savedPV['penyusun_2_nama'] ?? '' }}" placeholder="Nama Penyusun 2..." {{ $isAsesi ? 'readonly' : '' }}>
                            </td>
                            <td style="padding: 4px;">
                                <input type="text" name="penyusun_validator[penyusun_2_met]" class="input-baris-ia02" style="width: 100%;" value="{{ $savedPV['penyusun_2_met'] ?? '' }}" placeholder="No. MET..." {{ $isAsesi ? 'readonly' : '' }}>
                            </td>
                            <td style="padding: 4px;">
                                <input type="text" name="penyusun_validator[penyusun_2_ttd]" class="input-baris-ia02" style="width: 100%; text-align: center;" value="{{ $savedPV['penyusun_2_ttd'] ?? '' }}" placeholder="TTD & Tgl..." {{ $isAsesi ? 'readonly' : '' }}>
                            </td>
                        </tr>

                        <!-- VALIDATOR 1 -->
                        <tr>
                            <td rowspan="2" style="font-weight: 800; text-align: center; vertical-align: middle; background: #ffffff;">
                                VALIDATOR
                            </td>
                            <td style="text-align: center; font-weight: 700;">1</td>
                            <td style="padding: 4px;">
                                <input type="text" name="penyusun_validator[validator_1_nama]" class="input-baris-ia02" style="width: 100%;" value="{{ $savedPV['validator_1_nama'] ?? '' }}" placeholder="Nama Validator 1..." {{ $isAsesi ? 'readonly' : '' }}>
                            </td>
                            <td style="padding: 4px;">
                                <input type="text" name="penyusun_validator[validator_1_met]" class="input-baris-ia02" style="width: 100%;" value="{{ $savedPV['validator_1_met'] ?? '' }}" placeholder="No. MET..." {{ $isAsesi ? 'readonly' : '' }}>
                            </td>
                            <td style="padding: 4px;">
                                <input type="text" name="penyusun_validator[validator_1_ttd]" class="input-baris-ia02" style="width: 100%; text-align: center;" value="{{ $savedPV['validator_1_ttd'] ?? '' }}" placeholder="TTD & Tgl..." {{ $isAsesi ? 'readonly' : '' }}>
                            </td>
                        </tr>
                        <!-- VALIDATOR 2 -->
                        <tr>
                            <td style="text-align: center; font-weight: 700;">2</td>
                            <td style="padding: 4px;">
                                <input type="text" name="penyusun_validator[validator_2_nama]" class="input-baris-ia02" style="width: 100%;" value="{{ $savedPV['validator_2_nama'] ?? '' }}" placeholder="Nama Validator 2..." {{ $isAsesi ? 'readonly' : '' }}>
                            </td>
                            <td style="padding: 4px;">
                                <input type="text" name="penyusun_validator[validator_2_met]" class="input-baris-ia02" style="width: 100%;" value="{{ $savedPV['validator_2_met'] ?? '' }}" placeholder="No. MET..." {{ $isAsesi ? 'readonly' : '' }}>
                            </td>
                            <td style="padding: 4px;">
                                <input type="text" name="penyusun_validator[validator_2_ttd]" class="input-baris-ia02" style="width: 100%; text-align: center;" value="{{ $savedPV['validator_2_ttd'] ?? '' }}" placeholder="TTD & Tgl..." {{ $isAsesi ? 'readonly' : '' }}>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>

        </form>

    </div>

    <!-- NAVIGASI BAWAH FORMULIR -->
    @include('komponen.navigasi-form-bawah', [
        'pendaftaran' => $pendaftaran,
        'kembaliRoute' => (!empty($pendaftaran->skema_id) ? route('asesor.mapa', ['skema_id' => $pendaftaran->skema_id]) : route('asesor.daftar-peserta'))
    ])

</div>
@endsection

@push('js')
    <script src="{{ asset('js/formulir/formulir-bnsp.js') }}"></script>
@endpush
