@extends('tata-letak.dasbor')

@section('judul', 'FR.APL.02 Asesmen Mandiri')

@php $pendaftaran = $pendaftaran ?? ($pendaftaranTerakhir ?? null); @endphp

@push('css')
    <style>
        .wadah-apl02 {
            max-width: 1050px;
            margin: 0 auto;
        }
        .kartu-kertas-bnsp {
            background: #ffffff;
            border: 1px solid #e2e8f0;
            border-radius: 16px;
            padding: 2.25rem;
            box-shadow: 0 4px 20px -2px rgba(15, 23, 42, 0.05);
            margin-bottom: 2rem;
        }
        .header-bnsp {
            border: 1px solid #e2e8f0;
            border-radius: var(--radius-md);
            overflow: hidden;
            margin-bottom: 1.5rem;
        }
        .tabel-header-bnsp {
            width: 100%;
            border-collapse: collapse;
        }
        .tabel-header-bnsp td {
            padding: 0.85rem 1.15rem;
            border: 1px solid #e2e8f0;
        }
        .kotak-panduan {
            background: #f8fafc;
            border: 1px solid #e2e8f0;
            padding: 1.25rem 1.5rem;
            border-radius: var(--radius-md);
            margin-bottom: 1.75rem;
            box-shadow: 0 1px 3px rgba(0, 0, 0, 0.02);
        }
        .kotak-panduan ul {
            margin: 0.5rem 0 0 1.25rem;
            padding: 0;
        }
        .kotak-panduan li {
            margin-bottom: 0.35rem;
            font-size: 0.9rem;
            color: #334155;
            line-height: 1.55;
        }
        .blok-unit-kompetensi {
            border: 1px solid #e2e8f0;
            border-radius: 12px;
            overflow: hidden;
            margin-bottom: 2rem;
            background: #ffffff;
            box-shadow: 0 2px 8px rgba(15, 23, 42, 0.03);
        }
        .header-unit {
            background: #1e293b;
            color: #ffffff;
            padding: 1.1rem 1.5rem;
            display: flex;
            justify-content: space-between;
            align-items: center;
            gap: 1rem;
        }
        .pertanyaan-unit {
            background: #f1f5f9;
            padding: 0.9rem 1.5rem;
            font-weight: 700;
            color: #0f172a;
            border-bottom: 1px solid #e2e8f0;
            font-size: 0.95rem;
            display: flex;
            align-items: center;
            gap: 0.5rem;
        }
        .tabel-apl02 {
            width: 100%;
            border-collapse: collapse;
        }
        .tabel-apl02 th {
            background: #f8fafc;
            color: #1e293b;
            padding: 0.85rem 1rem;
            border: 1px solid #e2e8f0;
            font-size: 0.88rem;
            font-weight: 700;
            text-align: center;
        }
        .tabel-apl02 td {
            padding: 1rem;
            border: 1px solid #e2e8f0;
            vertical-align: top;
        }
        .opsi-radio-k {
            display: flex;
            justify-content: center;
            align-items: center;
            height: 100%;
            padding-top: 0.25rem;
        }
        .opsi-radio-k input[type="radio"] {
            width: 20px;
            height: 20px;
            cursor: pointer;
            accent-color: #2563eb;
        }
        .preview-bukti-foto {
            margin-top: 0.5rem;
            padding: 0.5rem 0.75rem;
            background: #f8fafc;
            border-radius: var(--radius-sm);
            border: 1px solid #e2e8f0;
            display: flex;
            align-items: center;
            gap: 0.65rem;
            font-size: 0.82rem;
        }
        .preview-bukti-foto img {
            width: 52px;
            height: 52px;
            object-fit: cover;
            border-radius: 6px;
            border: 1px solid #cbd5e1;
        }
    </style>
@endpush

@section('konten')
<div class="wadah-apl02 animasi-slide">

    @if(!$pendaftaranTerakhir)
        <div class="kartu" style="text-align: center; padding: 3.5rem 2rem; border-radius: 16px;">
            <h2 style="color: var(--biru-malam); font-size: 1.5rem;">Belum Ada Pendaftaran Skema</h2>
            <p style="color: var(--abu-teks); margin-bottom: 1.75rem;">Anda belum mendaftar skema sertifikasi apapun. Silakan lakukan pendaftaran skema terlebih dahulu untuk mengisi FR.APL.02 Asesmen Mandiri.</p>
            <a href="{{ route('asesi.pendaftaran.bagian1') }}" class="tombol tombol-utama" style="padding: 0.75rem 2rem;">
                Mulai Pendaftaran Skema
            </a>
        </div>
    @else

        @if(isset($semuaPendaftaran) && $semuaPendaftaran->count() > 1)
            <div style="background: #ffffff; padding: 1rem 1.5rem; border-radius: 12px; border: 1px solid #e2e8f0; margin-bottom: 1.5rem; display: flex; align-items: center; justify-content: space-between; gap: 1rem; box-shadow: 0 1px 3px rgba(0,0,0,0.02);">
                <div>
                    <strong style="color: #0f172a; display: block; font-size: 0.95rem;">Pilih Skema Sertifikasi Pendaftaran:</strong>
                    <small style="color: var(--abu-teks);">Anda memiliki {{ $semuaPendaftaran->count() }} pendaftaran skema sertifikasi aktif.</small>
                </div>
                <div>
                    <select class="input-control" onchange="window.location.href='?pendaftaran_id=' + this.value" style="font-weight: 700; min-width: 270px; background: #f8fafc; border-color: #cbd5e1;">
                        @foreach($semuaPendaftaran as $sp)
                            @php
                                $spAcc = ($sp->status_pendaftaran === 'diverifikasi' && $sp->rekomendasi_admin_status === 'diterima');
                                $spStatusTeks = 'Belum Diisi';
                                if (!$spAcc) {
                                    $spStatusTeks = 'Belum ACC Admin';
                                } elseif ($sp->jawabanApl02->count() > 0) {
                                    $spStatusTeks = 'Sudah Diisi - Terkunci';
                                }
                            @endphp
                            <option value="{{ $sp->id }}" {{ $pendaftaranTerakhir->id == $sp->id ? 'selected' : '' }}>
                                {{ $sp->skema->nama_skema }} ({{ $spStatusTeks }})
                            </option>
                        @endforeach
                    </select>
                </div>
            </div>
        @endif

        @if(!$isAccAdmin)
            <div style="background: #fffbeef8; color: #b45309; padding: 1.25rem 1.5rem; border-radius: 12px; border: 1.5px solid #fde68a; margin-bottom: 1.5rem; box-shadow: 0 1px 3px rgba(217,119,6,0.05);">
                <div>
                    <strong style="display: block; font-size: 1.05rem; color: #92400e; margin-bottom: 0.2rem;">
                        Formulir FR.APL.02 Belum Dapat Diisi
                    </strong>
                    <span style="font-size: 0.9rem; color: #b45309;">
                        Formulir FR.APL.01 (Pendaftaran Skema) Anda belum disetujui / diverifikasi oleh Admin LSP. Silakan tunggu verifikasi dari Admin sebelum dapat mengisi dan mengirimkan Formulir Asesmen Mandiri FR.APL.02 ini.
                    </span>
                    @if(in_array($pendaftaranTerakhir->status_pendaftaran, ['draft', 'revisi']))
                        <div style="margin-top: 0.6rem;">
                            <a href="{{ route('asesi.pendaftaran') }}" class="tombol tombol-utama tombol-sm" style="font-size: 0.82rem; padding: 0.4rem 1rem;">
                                Periksa / Lengkapi Form APL-01
                            </a>
                        </div>
                    @endif
                </div>
            </div>
        @elseif($isLocked)
            <div style="background: #eff6ff; color: #1e40af; padding: 1.25rem 1.5rem; border-radius: 12px; border: 1px solid #bfdbfe; margin-bottom: 1.5rem; box-shadow: 0 1px 3px rgba(37,99,235,0.05);">
                <div>
                    <strong style="display: block; font-size: 1.05rem; color: #1e3a8a; margin-bottom: 0.2rem;">Formulir FR.APL.02 Asesmen Mandiri Terkunci</strong>
                    <span style="font-size: 0.9rem; color: #1e40af;">Anda telah menyelesaikan dan mengirimkan Asesmen Mandiri untuk skema <strong>{{ $pendaftaranTerakhir->skema->nama_skema }}</strong>. Isian formulir di bawah berada dalam mode <strong>Baca Saja (Read-Only)</strong>.</span>
                </div>
            </div>
        @endif

        <div class="kartu-kertas-bnsp">
            <!-- KOP RESMI DOKUMEN STANDAR BNSP -->
            @include('komponen.kop-formulir-bnsp', [
                'kodeForm' => 'FR.APL.02',
                'judulForm' => 'ASESMEN MANDIRI',
                'tipeDokumen' => 'Asesmen Mandiri'
            ])

            <!-- HEADER TABLE SKEMA SERTIFIKASI -->
            <div class="header-bnsp">
                <table class="tabel-header-bnsp">
                    <tr>
                        <td rowspan="2" style="width: 28%; background: #f8fafc; font-weight: 700; color: #1e293b;">
                            Skema Sertifikasi<br><small style="font-weight: 400; color: #64748b;">(KKNI / Okupasi / Klaster)</small>
                        </td>
                        <td style="width: 15%; font-weight: 700; color: #1e293b;">Judul</td>
                        <td style="font-weight: 800; color: #2563eb; font-size: 1.05rem;">
                            {{ $pendaftaranTerakhir->skema->nama_skema }}
                        </td>
                    </tr>
                    <tr>
                        <td style="font-weight: 700; color: #1e293b;">Nomor Kode</td>
                        <td style="font-weight: 700; color: #0f172a;">
                            {{ $pendaftaranTerakhir->skema->kode_skema }}
                        </td>
                    </tr>
                </table>
            </div>

            <!-- PANDUAN ASESMEN MANDIRI (BERSIH DENGAN BORDER UNIFORM) -->
            <div class="kotak-panduan">
                <strong style="color: #0f172a; font-size: 0.98rem; display: block; margin-bottom: 0.5rem;">
                    PANDUAN ASESMEN MANDIRI
                </strong>
                <ul>
                    <li>Baca setiap pertanyaan di kolom sebelah kiri dengan cermat dan teliti.</li>
                    <li>Beri tanda centang (✓) pada kolom <strong>K (Kompeten)</strong> jika Anda yakin dapat melakukan tugas yang dijelaskan.</li>
                    <li>Isi kolom di sebelah kanan dengan <strong>mengunggah foto / berkas bukti yang relevan</strong> yang Anda miliki (seperti foto hasil praktek, karya, sertifikat, atau dokumen pendukung) untuk membuktikan kemampuan Anda.</li>
                </ul>
            </div>

            <!-- FORM ASESMEN MANDIRI -->
            <form action="{{ route('asesi.apl02.simpan') }}" method="POST" enctype="multipart/form-data">
                @csrf
                <input type="hidden" name="pendaftaran_id" value="{{ $pendaftaranTerakhir->id }}">

                @forelse($pendaftaranTerakhir->skema->unitKompetensi as $unitIndex => $unit)
                    <div class="blok-unit-kompetensi">
                        <!-- HEADER UNIT KOMPETENSI -->
                        <div class="header-unit">
                            <div>
                                <span style="background: rgba(255,255,255,0.15); padding: 0.2rem 0.6rem; border-radius: 4px; font-size: 0.8rem; font-weight: 600; text-transform: uppercase; letter-spacing: 0.05em;">Unit {{ $unitIndex + 1 }}</span>
                                <div style="font-size: 1.05rem; font-weight: 700; margin-top: 0.35rem;">{{ $unit->judul_unit }}</div>
                            </div>
                            <div style="text-align: right; font-size: 0.88rem; opacity: 0.9;">
                                Kode Unit: <strong>{{ $unit->kode_unit }}</strong>
                            </div>
                        </div>

                        <!-- PERTANYAAN SOP UNIT -->
                        <div class="pertanyaan-unit">
                            Dapatkah Saya {{ $unit->judul_unit }} sesuai SOP ?
                        </div>

                        <!-- TABEL ELEMEN & KUK -->
                        <table class="tabel-apl02">
                            <thead>
                                <tr>
                                    <th style="text-align: left; width: 48%;">Elemen & Kriteria Unjuk Kerja (KUK)</th>
                                    <th style="width: 55px;">K</th>
                                    <th style="width: 55px;">BK</th>
                                    <th style="width: 38%;">Bukti yang Relevan (Upload Foto / File PDF)</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($unit->elemenKompetensi as $elemen)
                                    @php
                                        $jawaban = $jawabanMap[$elemen->id] ?? null;
                                        $nilaiSaatIni = $jawaban ? $jawaban->nilai_kompetensi : 'K';
                                    @endphp
                                    <tr>
                                        <td>
                                            <strong style="color: #0f172a; font-size: 0.95rem; display: block; margin-bottom: 0.4rem;">
                                                {{ $elemen->nomor_elemen }}. Elemen: {{ $elemen->nama_elemen }}
                                            </strong>
                                            
                                            <!-- LIST KUK -->
                                            <ul style="margin: 0.35rem 0 0 1rem; padding-left: 0.5rem; color: #475569; font-size: 0.88rem; line-height: 1.55;">
                                                @forelse($elemen->kriteriaUnjukKerja as $kuk)
                                                    <li style="margin-bottom: 0.3rem;">
                                                        <strong style="color: #2563eb;">{{ $kuk->nomor_kuk }}</strong> {{ $kuk->pernyataan_kuk }}
                                                    </li>
                                                @empty
                                                    <li style="font-style: italic; color: #94a3b8;">Standard KUK belum diinput oleh Admin.</li>
                                                @endforelse
                                            </ul>
                                        </td>

                                        <!-- RADIO K -->
                                        <td style="text-align: center; vertical-align: middle;">
                                            <div class="opsi-radio-k">
                                                <input type="radio" name="penilaian[{{ $elemen->id }}]" value="K" {{ $nilaiSaatIni === 'K' ? 'checked' : '' }} {{ $isFormDisabled ? 'disabled' : '' }} required title="Kompeten (K)">
                                            </div>
                                        </td>

                                        <!-- RADIO BK -->
                                        <td style="text-align: center; vertical-align: middle;">
                                            <div class="opsi-radio-k">
                                                <input type="radio" name="penilaian[{{ $elemen->id }}]" value="BK" {{ $nilaiSaatIni === 'BK' ? 'checked' : '' }} {{ $isFormDisabled ? 'disabled' : '' }} title="Belum Kompeten (BK)">
                                            </div>
                                        </td>

                                        <!-- BUKTI RELEVAN (FILE FOTO UPLOAD) -->
                                        <td>
                                            <label style="font-size: 0.8rem; font-weight: 700; color: #1e293b; display: block; margin-bottom: 0.35rem;">
                                                Pilih Foto / File Bukti:
                                            </label>
                                            <input type="file" name="bukti_foto[{{ $elemen->id }}]" accept="image/*,application/pdf" class="input-control" {{ $isFormDisabled ? 'disabled' : '' }} style="font-size: 0.82rem; padding: 0.4rem; background: #f8fafc;">

                                            @if($jawaban && $jawaban->bukti_relevan)
                                                <div class="preview-bukti-foto">
                                                    @php
                                                        $buktiUrl = $jawaban->url_bukti ?? asset($jawaban->bukti_relevan);
                                                    @endphp
                                                    @if(Str::endsWith(strtolower($jawaban->bukti_relevan), ['.jpg', '.jpeg', '.png', '.webp']))
                                                        <img src="{{ $buktiUrl }}" alt="Bukti Foto" class="preview-gambar-modal" data-judul="File Bukti: {{ $elemen->nama_elemen }}" style="cursor: pointer;" title="Klik untuk pratinjau pop-up">
                                                        <div>
                                                            <strong style="color: #0f172a; display: block;">Bukti Terunggah:</strong>
                                                            <button type="button" class="tombol tombol-sekunder tombol-sm preview-gambar-link" data-pratinjau-gambar="{{ $buktiUrl }}" data-judul="Bukti Relevan: {{ $elemen->nama_elemen }}" style="font-size: 0.78rem; padding: 0.2rem 0.5rem; margin-top: 0.25rem;">
                                                                Lihat Bukti
                                                            </button>
                                                        </div>
                                                    @else
                                                        <span style="display: inline-block; font-size: 0.75rem; font-weight: 800; background: #fee2e2; color: #dc2626; padding: 0.2rem 0.4rem; border-radius: 4px;">PDF</span>
                                                        <div>
                                                            <strong style="color: #0f172a; display: block;">Dokumen PDF:</strong>
                                                            <a href="{{ $buktiUrl }}" target="_blank" style="color: #2563eb; text-decoration: underline; font-weight: 600;">
                                                                Buka PDF
                                                            </a>
                                                        </div>
                                                    @endif
                                                </div>
                                            @endif
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="4" style="text-align: center; color: #94a3b8; font-style: italic; padding: 1.5rem;">
                                            Belum ada Elemen Kompetensi yang terdaftar pada unit ini.
                                        </td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                @empty
                    <div style="text-align: center; padding: 2.5rem; color: #94a3b8; background: #ffffff; border-radius: 12px; border: 1px solid #e2e8f0;">
                        Belum ada Unit Kompetensi terdaftar pada skema ini.
                    </div>
                @endforelse

                <!-- TABEL REKOMENDASI & TANDA TANGAN ASESI - ASESOR (DESAIN RESMI BNSP FR.APL.02) -->
                <div style="margin: 2rem 0 1.75rem 0; border: 1px solid #e2e8f0; border-radius: 12px; overflow: hidden; background: #ffffff; box-shadow: 0 2px 8px rgba(15,23,42,0.03);">
                    <table style="width: 100%; border-collapse: collapse; font-size: 0.9rem;">
                        <tr>
                            <!-- REKOMENDASI UNTUK ASESI -->
                            <td style="width: 48%; padding: 1.5rem; border-right: 1px solid #e2e8f0; vertical-align: top; background: #f8fafc;">
                                <strong style="display: block; font-weight: 700; color: #0f172a; margin-bottom: 0.75rem; font-size: 0.95rem;">
                                    Rekomendasi Untuk Asesi:
                                </strong>
                                <p style="font-size: 0.9rem; color: #334155; margin-bottom: 1rem;">
                                    Asesmen <strong>dapat / tidak dapat</strong> dilanjutkan
                                </p>

                                @if($pendaftaran->rekomendasi_asesor_status === 'dapat_dilanjutkan')
                                    <div class="lencana lencana-hijau" style="padding: 0.5rem 1rem; font-size: 0.9rem; font-weight: 700; display: inline-flex; align-items: center;">
                                        Asesmen DAPAT dilanjutkan
                                    </div>
                                @elseif($pendaftaran->rekomendasi_asesor_status === 'tidak_dapat_dilanjutkan')
                                    <div class="lencana lencana-merah" style="padding: 0.5rem 1rem; font-size: 0.9rem; font-weight: 700; display: inline-flex; align-items: center;">
                                        Asesmen TIDAK DAPAT dilanjutkan
                                    </div>
                                @else
                                    <div class="lencana lencana-amber" style="padding: 0.5rem 1rem; font-size: 0.88rem; font-weight: 700; display: inline-flex; align-items: center;">
                                        Menunggu Peninjauan Asesor
                                    </div>
                                @endif

                                @if($pendaftaran->catatan_peninjauan_asesor)
                                    <div style="margin-top: 1rem; font-size: 0.85rem; color: #475569; font-style: italic; background: #ffffff; padding: 0.75rem; border-radius: 8px; border: 1px solid #e2e8f0;">
                                        <strong style="color: #0f172a;">Catatan Asesor:</strong> "{{ $pendaftaran->catatan_peninjauan_asesor }}"
                                    </div>
                                @endif
                            </td>

                            <!-- KANAN: ASESI & ASESOR -->
                            <td style="width: 52%; padding: 0; vertical-align: top;">
                                <table style="width: 100%; border-collapse: collapse;">
                                    <!-- BLOCK ASESI -->
                                    <tr>
                                        <th colspan="2" style="text-align: left; background: #f1f5f9; padding: 0.75rem 1rem; border-bottom: 1px solid #e2e8f0; color: #0f172a; font-weight: 700; font-size: 0.92rem;">
                                            Asesi :
                                        </th>
                                    </tr>
                                    <tr>
                                        <td style="width: 140px; padding: 0.75rem 1rem; border-bottom: 1px solid #e2e8f0; font-weight: 600; color: #475569;">Nama</td>
                                        <td style="padding: 0.75rem 1rem; border-bottom: 1px solid #e2e8f0; font-weight: 700; color: #0f172a;">
                                            {{ $pendaftaran->asesi->nama_lengkap }}
                                        </td>
                                    </tr>
                                    <tr>
                                        <td style="padding: 0.75rem 1rem; border-bottom: 1px solid #e2e8f0; font-weight: 600; color: #475569;">Tanda tangan / Tanggal</td>
                                        <td style="padding: 0.75rem 1rem; border-bottom: 1px solid #e2e8f0;">
                                            @php
                                                $ttdAsesiApl02 = $pendaftaran->tanda_tangan_asesi ?? $pendaftaran->asesi->tanda_tangan;
                                                $srcAsesiApl02 = $ttdAsesiApl02 ? (\Illuminate\Support\Str::startsWith($ttdAsesiApl02, ['data:image', 'http://', 'https://']) ? $ttdAsesiApl02 : asset($ttdAsesiApl02)) : '';
                                            @endphp
                                            <!-- INPUT HIDDEN BASE64 TTD -->
                                            <input type="hidden" name="tanda_tangan_asesi" id="input-ttd-asesi-base64" value="{{ old('tanda_tangan_asesi', $ttdAsesiApl02) }}">

                                            <!-- PREVIEW TTD CANVAS -->
                                            <div id="box-preview-ttd-asesi" style="margin: 0.25rem 0; {{ $srcAsesiApl02 ? '' : 'display: none;' }}">
                                                <img id="preview-ttd-asesi-img" src="{{ $srcAsesiApl02 }}" alt="TTD Asesi" style="max-height: 60px; border: 1px solid #cbd5e1; padding: 0.2rem; background: #ffffff; border-radius: 6px;">
                                            </div>

                                            @if(!$isFormDisabled)
                                                <button type="button" class="tombol tombol-utama tombol-sm" onclick="bukaModal('modalCanvasTtd')" style="margin-top: 0.35rem; font-size: 0.8rem;">
                                                    {{ $srcAsesiApl02 ? 'Ubah Tanda Tangan Canvas' : 'Gambar Tanda Tangan Digital' }}
                                                </button>
                                            @endif

                                            <div style="font-size: 0.8rem; color: #64748b; margin-top: 0.35rem;">
                                                Tanggal: {{ $pendaftaran->tanggal_ttd_asesi ? $pendaftaran->tanggal_ttd_asesi->format('d/m/Y') : date('d/m/Y') }}
                                            </div>
                                        </td>
                                    </tr>

                                    <!-- BLOCK ASESOR -->
                                    <tr>
                                        <th colspan="2" style="text-align: left; background: #f1f5f9; padding: 0.75rem 1rem; border-bottom: 1px solid #e2e8f0; color: #0f172a; font-weight: 700; font-size: 0.92rem;">
                                            Ditinjau Oleh Asesor :
                                        </th>
                                    </tr>
                                    <tr>
                                        <td style="padding: 0.75rem 1rem; border-bottom: 1px solid #e2e8f0; font-weight: 600; color: #475569;">Nama :</td>
                                        <td style="padding: 0.75rem 1rem; border-bottom: 1px solid #e2e8f0; font-weight: 700; color: #0f172a;">
                                            {{ $pendaftaran->asesor->nama_lengkap ?? 'Belum Ditentukan' }}
                                        </td>
                                    </tr>
                                    <tr>
                                        <td style="padding: 0.75rem 1rem; border-bottom: 1px solid #e2e8f0; font-weight: 600; color: #475569;">No. Reg:</td>
                                        <td style="padding: 0.75rem 1rem; border-bottom: 1px solid #e2e8f0; font-weight: 700; color: #2563eb;">
                                            {{ $pendaftaran->asesor->nomor_registrasi ?? ('MET.000.00' . ($pendaftaran->asesor_id ?? '00') . ' 2026') }}
                                        </td>
                                    </tr>
                                    <tr>
                                        <td style="padding: 0.75rem 1rem; font-weight: 600; color: #475569;">Tanda tangan / Tanggal</td>
                                        <td style="padding: 0.75rem 1rem;">
                                            @php
                                                $ttdAsesorApl02 = $pendaftaran->tanda_tangan_asesor ?? ($pendaftaran->asesor->tanda_tangan ?? null);
                                                $srcAsesorApl02 = $ttdAsesorApl02 ? (\Illuminate\Support\Str::startsWith($ttdAsesorApl02, ['data:image', 'http://', 'https://']) ? $ttdAsesorApl02 : asset($ttdAsesorApl02)) : null;
                                            @endphp
                                            @if($srcAsesorApl02)
                                                <img src="{{ $srcAsesorApl02 }}" alt="TTD Asesor" style="max-height: 55px; display: block; margin-bottom: 0.25rem;">
                                            @else
                                                <span style="color: #64748b; font-style: italic; font-size: 0.82rem;">(Disahkan oleh Asesor di halaman peninjauan Asesor)</span>
                                            @endif
                                            @if($pendaftaran->tanggal_ttd_asesor)
                                                <div style="font-size: 0.8rem; color: #64748b; margin-top: 0.2rem;">
                                                    Tanggal: {{ $pendaftaran->tanggal_ttd_asesor->format('d/m/Y') }}
                                                </div>
                                            @endif
                                        </td>
                                    </tr>
                                </table>
                            </td>
                        </tr>
                    </table>
                </div>

                <!-- TOMBOL SIMPAN / LENCANA TERKUNCI -->
                <div style="background: #ffffff; padding: 1.5rem 1.75rem; border-radius: 12px; border: 1px solid #e2e8f0; text-align: right; display: flex; justify-content: space-between; align-items: center; box-shadow: 0 2px 8px rgba(15,23,42,0.03);">
                    <div style="text-align: left;">
                        <strong style="color: #0f172a; display: block; font-size: 0.95rem;">Verifikasi Mandiri Asesi</strong>
                        <span style="font-size: 0.85rem; color: #64748b;">
                            @if(!$isAccAdmin)
                                Formulir FR.APL.02 belum dapat diisi sebelum Formulir FR.APL.01 disetujui oleh Admin.
                            @elseif($isLocked)
                                Formulir FR.APL.02 Asesmen Mandiri untuk skema ini telah diselesaikan dan dikunci.
                            @else
                                Pastikan seluruh jawaban K/BK dan foto bukti relevan telah lengkap sebelum disimpan.
                            @endif
                        </span>
                    </div>

                    @if(!$isAccAdmin)
                        <span class="lencana lencana-amber" style="font-size: 0.9rem; padding: 0.75rem 1.5rem; border-radius: 8px;">
                            Belum Disetujui Admin (APL-01)
                        </span>
                    @elseif($isLocked)
                        <span class="lencana lencana-hijau" style="font-size: 0.95rem; padding: 0.75rem 1.75rem; border-radius: 8px;">
                            Formulir Telah Dikirim & Terkunci
                        </span>
                    @else
                        <button type="submit" class="tombol tombol-utama" style="padding: 0.85rem 2.5rem; font-size: 1.05rem; border-radius: 8px;">
                            Simpan FR.APL.02 Asesmen Mandiri
                        </button>
                    @endif
                </div>

            </form>
        </div>

    @endif

</div>

<!-- MODAL POPUP CANVAS SIGNATURE PAD FOR ASESI -->
<div class="modal-overlay" id="modalCanvasTtd">
    <div class="modal-konten" style="max-width: 550px; text-align: center; border-radius: 16px;">
        <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 1rem;">
            <h3 style="color: #0f172a; margin: 0; font-size: 1.2rem;">Gambar Tanda Tangan Asesi</h3>
            <button type="button" onclick="tutupModal('modalCanvasTtd')" style="background: none; border: none; font-size: 1.4rem; cursor: pointer;">&times;</button>
        </div>

        <p style="font-size: 0.88rem; color: #64748b; margin-bottom: 1rem;">
            Gunakan tetikus (mouse) atau jari Anda (layar sentuh) untuk menggambar tanda tangan digital pada kotak di bawah ini:
        </p>

        <canvas id="canvas-ttd-asesi" width="460" height="200" class="canvas-signature-pad" style="border: 2px dashed #93c5fd; border-radius: 12px; background: #ffffff; cursor: crosshair; touch-action: none; display: block; margin: 0 auto;"></canvas>

        <div style="margin-top: 1.25rem; display: flex; gap: 0.75rem; justify-content: center;">
            <button type="button" class="tombol tombol-sekunder tombol-sm" id="btn-clear-canvas">
                Bersihkan Canvas
            </button>
            <button type="button" class="tombol tombol-utama tombol-sm" id="btn-simpan-canvas">
                Gunakan Tanda Tangan Ini
            </button>
        </div>
    </div>
</div>
@endsection

@push('js')
    <script src="{{ asset('js/asesi/pendaftaran-bagian32.js') }}"></script>
@endpush
