@extends('tata-letak.dasbor')

@section('judul', 'FR.IA.09 - Pertanyaan Wawancara')

@push('css')
    <link rel="stylesheet" href="{{ asset('css/formulir/formulir-bnsp.css') }}">
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
    @endphp

    <!-- ACTION BAR ATAS -->
    @include('komponen.action-bar-formulir', [
        'kembaliRoute' => route('formulir.index', ['pendaftaran_id' => $pendaftaran->id]),
        'kodeForm' => 'FR.IA.09',
        'namaForm' => 'FR.IA.09 Pertanyaan Wawancara',
        'pendaftaranId' => $pendaftaran->id,
        'canSubmit' => !$isAsesi,
        'submitOnClick' => "simpanNotifikasiFormulir('FR.IA.09')",
        'submitLabel' => 'Simpan Formulir',
        'canSignAsesi' => $isAsesi && !$asesiTtd,
        'signAsesiRoute' => route('formulir.ia.simpan-ttd-asesi', ['kodeForm' => 'FR.IA.09', 'pendaftaranId' => $pendaftaran->id]),
        'isAsesiSigned' => $isAsesi && $asesiTtd
    ])

    @if($isAsesi)
        @include('komponen.banner-readonly-asesi', [
            'pesan' => 'Pertanyaan wawancara pembuktian portofolio ini dinilai langsung oleh Asesor Penguji Anda.'
        ])
    @endif

    <div class="dokumen-kertas">
        
        <!-- KOP RESMI DOKUMEN STANDAR BNSP -->
        @include('komponen.kop-formulir-bnsp', [
            'kodeForm' => 'FR.IA.09',
            'judulForm' => 'PW – PERTANYAAN WAWANCARA',
            'tipeDokumen' => 'Pertanyaan Wawancara'
        ])

        <!-- IDENTITAS DOKUMEN -->
        <table class="tabel-bnsp">
            <tr>
                <td rowspan="2" style="width: 25%; font-weight: 700; vertical-align: middle; background-color: #f8fafc;">
                    Skema Sertifikasi<br>
                    <span style="font-weight: 500; font-size: 0.85rem; color: #475569;">(KKNI/Okupasi/Klaster)</span>
                </td>
                <td style="width: 12%; font-weight: 600;">Judul</td>
                <td style="width: 2%;">:</td>
                <td style="font-weight: 700; color: #0f172a;">{{ $pendaftaran->skema->nama_skema }}</td>
            </tr>
            <tr>
                <td style="font-weight: 600;">Nomor</td>
                <td>:</td>
                <td style="font-weight: 700; color: #0f172a;">{{ $pendaftaran->skema->kode_skema }}</td>
            </tr>
            <tr>
                <td colspan="2" style="font-weight: 600;">TUK</td>
                <td>:</td>
                <td>Sewaktu / Tempat Kerja / Mandiri* (<strong>{{ $pendaftaran->tuk_type ?? 'Sewaktu' }}</strong>)</td>
            </tr>
            <tr>
                <td colspan="2" style="font-weight: 600;">Nama Asesor</td>
                <td>:</td>
                <td><strong>{{ $asesorNama }}</strong></td>
            </tr>
            <tr>
                <td colspan="2" style="font-weight: 600;">Nama Asesi</td>
                <td>:</td>
                <td><strong>{{ $asesiNama }}</strong></td>
            </tr>
            <tr>
                <td colspan="2" style="font-weight: 600;">Tanggal</td>
                <td>:</td>
                <td>{{ date('d-m-Y') }}</td>
            </tr>
        </table>
        <div style="font-size: 0.75rem; font-style: italic; color: #64748b; margin-top: -1rem; margin-bottom: 1.25rem;">*Coret yang tidak perlu</div>

        <!-- PANDUAN BAGI ASESOR -->
        <div class="kotak-panduan-asesor">
            <strong>PANDUAN BAGI ASESOR</strong>
            <ul>
                <li>Pertanyaan wawancara dapat dilakukan untuk keseluruhan unit kompetensi dalam skema sertifikasi atau dilakukan untuk masing-masing kelompok pekerjaan dalam satu skema sertifikasi.</li>
                <li>Isilah bukti portofolio sesuai dengan bukti yang diminta pada skema sertifikasi sebagaimana yang telah dibuat pada FR-IA.08.</li>
                <li>Ajukan pertanyaan verifikasi portofolio untuk semua unit/elemen kompetensi yang di checklist pada FR-IA.08.</li>
                <li>Ajukan pertanyaan kepada asesi sebagai tindak lanjut hasil verifikasi portofolio.</li>
                <li>Jika hasil verifikasi portofolio telah memenuhi aturan bukti maka pertanyaan wawancara tidak perlu dilakukan terhadap bukti tersebut.</li>
                <li>Tuliskan pencapaian atas setiap kesimpulan pertanyaan wawancara dengan cara mencentang (&radic;) "Ya" atau "Tidak".</li>
            </ul>
        </div>

        <!-- DAFTAR BUKTI PORTOFOLIO -->
        <table class="tabel-bnsp" style="margin-bottom: 1.5rem;">
            <thead>
                <tr>
                    <th style="width: 8%; text-align: center;">No.</th>
                    <th>Bukti Portofolio</th>
                </tr>
            </thead>
            <tbody>
                <tr>
                    <td style="text-align: center; font-weight: 700;">1.</td>
                    <td>Sertifikat Kompetensi / Pelatihan Pemrograman Web Terstruktur</td>
                </tr>
                <tr>
                    <td style="text-align: center; font-weight: 700;">2.</td>
                    <td>Dokumen Laporan Source Code Proyek Aplikasi & Database Schema</td>
                </tr>
                <tr>
                    <td style="text-align: center; font-weight: 700;">3.</td>
                    <td>Surat Keterangan Pengalaman Kerja / Magang Bidang Software Engineering</td>
                </tr>
                <tr>
                    <td style="text-align: center; font-weight: 700;">4.</td>
                    <td>Logbook Harian Praktik Kerja Lapangan</td>
                </tr>
            </tbody>
        </table>

        <!-- TABEL PERTANYAAN WAWANCARA -->
        <table class="tabel-bnsp" style="font-size: 0.85rem;">
            <thead>
                <tr>
                    <th rowspan="2" style="width: 5%; text-align: center;">No.</th>
                    <th rowspan="2" style="width: 45%;">Daftar Pertanyaan Wawancara</th>
                    <th rowspan="2" style="width: 36%;">Kesimpulan Jawaban Asesi</th>
                    <th colspan="2" style="width: 14%; text-align: center;">Pencapaian</th>
                </tr>
                <tr>
                    <th style="width: 7%; text-align: center;">Ya</th>
                    <th style="width: 7%; text-align: center;">Tidak</th>
                </tr>
            </thead>
            <tbody>
                <tr>
                    <td style="text-align: center; font-weight: 700; vertical-align: top;">1.</td>
                    <td>
                        <textarea class="input-inline-bnsp" rows="3">Sesuai dengan bukti no. 1 yang Anda ajukan, jelaskan bagaimana Anda mengimplementasikan materi pelatihan tersebut ke dalam proyek nyata yang Anda bangun?</textarea>
                    </td>
                    <td>
                        <textarea class="input-inline-bnsp" rows="3">Asesi dapat menjelaskan secara runtut implementasi struktur program, penanganan database query, dan validasi input pengguna.</textarea>
                    </td>
                    <td style="text-align: center; vertical-align: middle;">
                        <input type="checkbox" checked class="checkbox-bnsp checkbox-bnsp-hijau">
                    </td>
                    <td style="text-align: center; vertical-align: middle;">
                        <input type="checkbox" class="checkbox-bnsp checkbox-bnsp-merah">
                    </td>
                </tr>
                <tr>
                    <td style="text-align: center; font-weight: 700; vertical-align: top;">2.</td>
                    <td>
                        <textarea class="input-inline-bnsp" rows="3">Sesuai dengan bukti no. 2 yang Anda ajukan, bagaimana Anda memastikan bahwa logika algoritma dan relasi antar tabel telah bebas dari anomali data?</textarea>
                    </td>
                    <td>
                        <textarea class="input-inline-bnsp" rows="3">Asesi memaparkan penerapan normalisasi database serta penggunaan foreign key constraint dan transaksi database.</textarea>
                    </td>
                    <td style="text-align: center; vertical-align: middle;">
                        <input type="checkbox" checked class="checkbox-bnsp checkbox-bnsp-hijau">
                    </td>
                    <td style="text-align: center; vertical-align: middle;">
                        <input type="checkbox" class="checkbox-bnsp checkbox-bnsp-merah">
                    </td>
                </tr>
                <tr>
                    <td style="text-align: center; font-weight: 700; vertical-align: top;">3.</td>
                    <td>
                        <textarea class="input-inline-bnsp" rows="3">Sesuai dengan bukti no. 3 yang Anda ajukan, apa kontribusi utama Anda saat bekerja dalam tim pengembang perangkat lunak?</textarea>
                    </td>
                    <td>
                        <textarea class="input-inline-bnsp" rows="3">Asesi bertanggung jawab membangun modul backend autentikasi dan pembuatan API endpoint sesuai spesifikasi teknis.</textarea>
                    </td>
                    <td style="text-align: center; vertical-align: middle;">
                        <input type="checkbox" checked class="checkbox-bnsp checkbox-bnsp-hijau">
                    </td>
                    <td style="text-align: center; vertical-align: middle;">
                        <input type="checkbox" class="checkbox-bnsp checkbox-bnsp-merah">
                    </td>
                </tr>
            </tbody>
        </table>

        <!-- PENGESAHAN ASESI & ASESOR -->
        <table class="tabel-bnsp" style="margin-bottom: 2rem;">
            <tr>
                <td style="width: 50%; font-weight: 700; background: #f8fafc;">Asesi :</td>
                <td style="width: 50%; font-weight: 700; background: #f8fafc;">Asesor :</td>
            </tr>
            <tr>
                <td>
                    <div style="margin-bottom: 0.4rem;">Nama : <strong>{{ $asesiNama }}</strong></div>
                    <div style="margin-top: 1rem; border-top: 1px dashed #cbd5e1; padding-top: 0.4rem;">
                        Tanda tangan dan Tanggal :<br>
                        @if($asesiTtd)
                            <div style="margin-top: 0.35rem;">
                                <img src="{{ $asesiTtd }}" alt="TTD Asesi" style="max-height: 45px;">
                                <div style="font-size: 0.78rem; color: #16a34a; font-weight: 700; margin-top: 0.25rem;">
                                    Terverifikasi & Disetujui Asesi ({{ $tglTtdAsesi ?? date('d-m-Y') }})
                                </div>
                            </div>
                        @else
                            <span style="font-style: italic; color: #64748b;">(Tanda Tangan Digital Akun Asesi)</span>
                            <div style="font-size: 0.78rem; color: #64748b; margin-top: 0.25rem;">{{ date('d-m-Y') }}</div>
                        @endif
                    </div>
                </td>
                <td>
                    <div style="margin-bottom: 0.2rem;">Nama : <strong>{{ $asesorNama }}</strong></div>
                    <div style="margin-bottom: 0.4rem;">No. Reg : <strong>{{ $asesorMet }}</strong></div>
                    <div style="margin-top: 0.75rem; border-top: 1px dashed #cbd5e1; padding-top: 0.4rem;">
                        Tanda tangan dan Tanggal :<br>
                        @if($asesorTtd)
                            <img src="{{ $asesorTtd }}" alt="TTD Asesor" style="max-height: 45px; margin-top: 0.25rem;">
                        @else
                            <span style="font-style: italic; color: #64748b;">(Tanda Tangan Digital Asesor)</span>
                        @endif
                        <div style="font-size: 0.78rem; color: #64748b; margin-top: 0.25rem;">{{ date('d-m-Y') }}</div>
                    </div>
                </td>
            </tr>
        </table>

        <!-- TABEL PENYUSUN DAN VALIDATOR -->
        <div>
            <div style="font-weight: 800; font-size: 0.95rem; margin-bottom: 0.6rem; color: #0f172a; text-transform: uppercase;">
                PENYUSUN DAN VALIDATOR
            </div>
            <table class="tabel-bnsp">
                <thead>
                    <tr>
                        <th style="width: 18%; text-align: center;">STATUS</th>
                        <th style="width: 6%; text-align: center;">NO</th>
                        <th style="width: 32%;">NAMA</th>
                        <th style="width: 22%;">NOMOR MET</th>
                        <th style="width: 22%;">TANDA TANGAN DAN TANGGAL</th>
                    </tr>
                </thead>
                <tbody>
                    <tr>
                        <td rowspan="2" style="font-weight: 800; vertical-align: middle; text-align: center; background-color: #f8fafc;">PENYUSUN</td>
                        <td style="text-align: center; font-weight: 700;">1</td>
                        <td><strong>{{ $asesorNama }}</strong></td>
                        <td>{{ $asesorMet }}</td>
                        <td style="text-align: center;">
                            @if($asesorTtd)
                                <img src="{{ $asesorTtd }}" alt="TTD" style="max-height: 40px;">
                            @else
                                <span style="font-size: 0.78rem; color: #64748b;">{{ date('d/m/Y') }}</span>
                            @endif
                        </td>
                    </tr>
                    <tr>
                        <td style="text-align: center; font-weight: 700;">2</td>
                        <td><input type="text" class="input-inline-bnsp" placeholder="Penyusun 2..."></td>
                        <td><input type="text" class="input-inline-bnsp" placeholder="No. MET..."></td>
                        <td><input type="text" class="input-inline-bnsp" placeholder="TTD & Tgl..."></td>
                    </tr>
                    <tr>
                        <td rowspan="2" style="font-weight: 800; vertical-align: middle; text-align: center; background-color: #f8fafc;">VALIDATOR</td>
                        <td style="text-align: center; font-weight: 700;">1</td>
                        <td><input type="text" class="input-inline-bnsp" placeholder="Validator 1..."></td>
                        <td><input type="text" class="input-inline-bnsp" placeholder="No. MET..."></td>
                        <td><input type="text" class="input-inline-bnsp" placeholder="TTD & Tgl..."></td>
                    </tr>
                    <tr>
                        <td style="text-align: center; font-weight: 700;">2</td>
                        <td><input type="text" class="input-inline-bnsp" placeholder="Validator 2..."></td>
                        <td><input type="text" class="input-inline-bnsp" placeholder="No. MET..."></td>
                        <td><input type="text" class="input-inline-bnsp" placeholder="TTD & Tgl..."></td>
                    </tr>
                </tbody>
            </table>
        </div>

        @include('komponen.navigasi-form-bawah', [
            'pendaftaranId' => $pendaftaran->id,
            'prevForm' => ['route' => route('formulir.ia08', $pendaftaran->id), 'label' => 'FR.IA.08 Portofolio'],
            'nextForm' => ['route' => route('formulir.ia.index', ['kodeForm' => 'FR.IA.10', 'pendaftaranId' => $pendaftaran->id]), 'label' => 'FR.IA.10 Pihak Ketiga']
        ])

    </div>
</div>
@endsection

@push('js')
    <script src="{{ asset('js/formulir/formulir-bnsp.js') }}"></script>
@endpush
