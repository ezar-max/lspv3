@extends('tata-letak.dasbor')

@section('judul', 'FR.IA.11 - Ceklis Reviu Produk')

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
        'kodeForm' => 'FR.IA.11',
        'namaForm' => 'FR.IA.11 Ceklis Reviu Produk',
        'pendaftaranId' => $pendaftaran->id,
        'canSubmit' => !$isAsesi,
        'submitOnClick' => "simpanNotifikasiFormulir('FR.IA.11')",
        'submitLabel' => 'Simpan Formulir',
        'canSignAsesi' => $isAsesi && !$asesiTtd,
        'signAsesiRoute' => route('formulir.ia.simpan-ttd-asesi', ['kodeForm' => 'FR.IA.11', 'pendaftaranId' => $pendaftaran->id]),
        'isAsesiSigned' => $isAsesi && $asesiTtd
    ])

    @if($isAsesi)
        @include('komponen.banner-readonly-asesi', [
            'pesan' => 'Ceklis reviu spesifikasi teknis dan mutu produk karya ini dinilai langsung oleh Asesor Penguji Anda.'
        ])
    @endif

    <div class="dokumen-kertas">
        
        <!-- KOP RESMI DOKUMEN STANDAR BNSP -->
        @include('komponen.kop-formulir-bnsp', [
            'kodeForm' => 'FR.IA.11',
            'judulForm' => 'CRP – CEKLIS REVIU PRODUK',
            'tipeDokumen' => 'Reviu Mutu Produk'
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
                <td>Sewaktu / Tempat Kerja / Mandiri* (<strong>{{ $pendaftaran->tuk_type ?? '' }}</strong>)</td>
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
            <div style="font-weight: 700; font-size: 0.88rem; color: #0f172a; margin-bottom: 0.35rem;">Instruksi:</div>
            <ul>
                <li>Formulir ini digunakan untuk menilai produk yang telah dinikmati/dioperasikan/digunakan minimal satu tahun (sesuai garansi yang diberikan).</li>
                <li>Pernyataan yang ada pada tabel formulir ini dapat diganti atau dikembangkan yang lebih spesifik sesuai kebutuhan diprofesinya.</li>
            </ul>
        </div>

        <!-- RANCANGAN PRODUK ATAU DATA TEKNIS PRODUK -->
        <table class="tabel-bnsp" style="margin-bottom: 1.5rem;">
            <thead>
                <tr>
                    <th colspan="2" style="background: #e2e8f0; font-weight: 800; font-size: 0.95rem;">
                        Rancangan Produk atau Data Teknis Produk
                    </th>
                </tr>
            </thead>
            <tbody>
                <tr>
                    <td style="width: 30%; font-weight: 700; background: #f8fafc;">Nama produk yang dihasilkan</td>
                    <td><input type="text" class="input-inline-bnsp" value="Modul Aplikasi Sistem Informasi Sertifikasi Profesi Berbasis Web"></td>
                </tr>
                <tr>
                    <td style="font-weight: 700; background: #f8fafc;">Standar Industri atau tempat kerja</td>
                    <td><input type="text" class="input-inline-bnsp" value="SKKNI Bidang Pengembangan Perangkat Lunak / Web Development"></td>
                </tr>
                <tr>
                    <td style="font-weight: 700; background: #f8fafc;">Spesifikasi produk secara umum</td>
                    <td>
                        <div style="display: flex; flex-direction: column; gap: 0.35rem;">
                            <div>Dimensi / Format : <input type="text" class="input-inline-bnsp" value="Web Application Responsive (Desktop & Mobile)" style="display: inline-block; max-width: 400px; padding: 0.2rem 0.4rem;"></div>
                            <div>Bahan / Teknologi : <input type="text" class="input-inline-bnsp" value="PHP Laravel, MySQL, HTML5, CSS3, JavaScript" style="display: inline-block; max-width: 400px; padding: 0.2rem 0.4rem;"></div>
                            <div>Kapasitas / Ukuran : <input type="text" class="input-inline-bnsp" value="Source Code ~45 MB, Database Storage ~100 MB" style="display: inline-block; max-width: 400px; padding: 0.2rem 0.4rem;"></div>
                        </div>
                    </td>
                </tr>
                <tr>
                    <td style="font-weight: 700; background: #f8fafc;">Spesifikasi produk secara teknis</td>
                    <td>
                        <div>Data Teknis :</div>
                        <textarea class="input-inline-bnsp" rows="2" style="margin-top: 0.25rem;">Arsitektur MVC, RESTful API endpoints, Enkripsi password bcrypt, Database normalization 3NF, Responsive CSS Flex/Grid.</textarea>
                    </td>
                </tr>
                <tr>
                    <td style="font-weight: 700; background: #f8fafc;">Tanggal pengoperasian / penggunaan</td>
                    <td><input type="text" class="input-inline-bnsp" value="{{ date('d F Y') }}"></td>
                </tr>
                <tr>
                    <td style="font-weight: 700; background: #f8fafc;">Gambar produk (jika ada)</td>
                    <td>
                        <input type="text" class="input-inline-bnsp" placeholder="URL lampiran tangkapan layar antarmuka sistem..." value="assets/dokumen/preview-sistem-asesi.png">
                    </td>
                </tr>
            </tbody>
        </table>

        <!-- TABEL SPESIFIKASI DAN PERFORMA PRODUK -->
        <table class="tabel-bnsp" style="font-size: 0.85rem;">
            <thead>
                <tr>
                    <th rowspan="2" style="width: 25%;">Spesifikasi dan Performa Produk</th>
                    <th rowspan="2" style="width: 61%;">Hasil Review Produk*</th>
                    <th colspan="2" style="width: 14%; text-align: center;">Pencapaian</th>
                </tr>
                <tr>
                    <th style="width: 7%; text-align: center;">Ya</th>
                    <th style="width: 7%; text-align: center;">Tidak</th>
                </tr>
            </thead>
            <tbody>
                <!-- 1. SPESIFIKASI PRODUK -->
                <tr>
                    <td rowspan="3" style="font-weight: 700; vertical-align: top; background: #f8fafc;">
                        1. Spesifikasi produk
                    </td>
                    <td>Ukuran produk sesuai rencana atau gambar kerja</td>
                    <td style="text-align: center;"><input type="checkbox" checked class="checkbox-bnsp checkbox-bnsp-hijau"></td>
                    <td style="text-align: center;"><input type="checkbox" class="checkbox-bnsp checkbox-bnsp-merah"></td>
                </tr>
                <tr>
                    <td>Estetika / penampilan produk</td>
                    <td style="text-align: center;"><input type="checkbox" checked class="checkbox-bnsp checkbox-bnsp-hijau"></td>
                    <td style="text-align: center;"><input type="checkbox" class="checkbox-bnsp checkbox-bnsp-merah"></td>
                </tr>
                <tr>
                    <td>Kebersihan dan kerapian permukaan produk</td>
                    <td style="text-align: center;"><input type="checkbox" checked class="checkbox-bnsp checkbox-bnsp-hijau"></td>
                    <td style="text-align: center;"><input type="checkbox" class="checkbox-bnsp checkbox-bnsp-merah"></td>
                </tr>

                <!-- 2. PERFORMA PRODUK -->
                <tr>
                    <td rowspan="5" style="font-weight: 700; vertical-align: top; background: #f8fafc;">
                        2. Performa produk atau Karakteristik Produk
                    </td>
                    <td>Kesesuaian ukuran (dimensi dan/atau berat)</td>
                    <td style="text-align: center;"><input type="checkbox" checked class="checkbox-bnsp checkbox-bnsp-hijau"></td>
                    <td style="text-align: center;"><input type="checkbox" class="checkbox-bnsp checkbox-bnsp-merah"></td>
                </tr>
                <tr>
                    <td>Kesesuaian dengan gambar kerja atau bentuk</td>
                    <td style="text-align: center;"><input type="checkbox" checked class="checkbox-bnsp checkbox-bnsp-hijau"></td>
                    <td style="text-align: center;"><input type="checkbox" class="checkbox-bnsp checkbox-bnsp-merah"></td>
                </tr>
                <tr>
                    <td>Kerapian dan kerapatan sambungan</td>
                    <td style="text-align: center;"><input type="checkbox" checked class="checkbox-bnsp checkbox-bnsp-hijau"></td>
                    <td style="text-align: center;"><input type="checkbox" class="checkbox-bnsp checkbox-bnsp-merah"></td>
                </tr>
                <tr>
                    <td>Pemasangan perlengkapan bahan penolong</td>
                    <td style="text-align: center;"><input type="checkbox" checked class="checkbox-bnsp checkbox-bnsp-hijau"></td>
                    <td style="text-align: center;"><input type="checkbox" class="checkbox-bnsp checkbox-bnsp-merah"></td>
                </tr>
                <tr>
                    <td>Kualitas produk sesuai dengan rujukan</td>
                    <td style="text-align: center;"><input type="checkbox" checked class="checkbox-bnsp checkbox-bnsp-hijau"></td>
                    <td style="text-align: center;"><input type="checkbox" class="checkbox-bnsp checkbox-bnsp-merah"></td>
                </tr>
            </tbody>
        </table>
        <div style="font-size: 0.75rem; font-style: italic; color: #64748b; margin-top: -1rem; margin-bottom: 1.25rem;">*diisi sesuai dengan jenis produk yang direview</div>

        <!-- REKOMENDASI ASESOR -->
        <table class="tabel-bnsp" style="margin-bottom: 1.5rem;">
            <tr>
                <td style="width: 25%; font-weight: 700; background: #f8fafc; vertical-align: middle;">Rekomendasi Asesor:</td>
                <td>
                    <div style="margin-bottom: 0.75rem;">
                        <label style="display: flex; align-items: flex-start; gap: 0.5rem; cursor: pointer; font-weight: 600; color: #059669;">
                            <input type="radio" name="rekomendasi_crp" value="kompeten" checked style="margin-top: 0.2rem; accent-color: #059669;">
                            <span>Asesi telah memenuhi pencapaian seluruh kriteria unjuk kerja, direkomendasikan <strong>KOMPETEN</strong></span>
                        </label>
                    </div>
                    <div>
                        <label style="display: flex; align-items: flex-start; gap: 0.5rem; cursor: pointer; font-weight: 600; color: #dc2626;">
                            <input type="radio" name="rekomendasi_crp" value="observasi_langsung" style="margin-top: 0.2rem; accent-color: #dc2626;">
                            <span>Asesi belum memenuhi pencapaian seluruh kriteria unjuk kerja, direkomendasikan <strong>OBSERVASI LANGSUNG</strong> pada:</span>
                        </label>
                        <div style="padding-left: 1.5rem; margin-top: 0.35rem; font-size: 0.82rem; color: #475569;">
                            Kelompok Pekerjaan : <input type="text" class="input-inline-bnsp" style="max-width: 200px; display: inline-block; padding: 0.2rem 0.4rem;"><br>
                            Unit : <input type="text" class="input-inline-bnsp" style="max-width: 200px; display: inline-block; padding: 0.2rem 0.4rem; margin-top: 0.2rem;"><br>
                            Elemen : <input type="text" class="input-inline-bnsp" style="max-width: 200px; display: inline-block; padding: 0.2rem 0.4rem; margin-top: 0.2rem;"><br>
                            KUK : <input type="text" class="input-inline-bnsp" style="max-width: 200px; display: inline-block; padding: 0.2rem 0.4rem; margin-top: 0.2rem;">
                        </div>
                    </div>
                </td>
            </tr>
        </table>

        <!-- PENGESAHAN ASESI & ASESOR -->
        <table class="tabel-bnsp" style="margin-bottom: 1.5rem;">
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

        <!-- CATATAN TEMUAN HASIL REVIU PRODUK -->
        <div style="border: 1px solid #334155; padding: 1rem; border-radius: 4px; margin-bottom: 1.5rem; background: #f8fafc;">
            <div style="font-weight: 700; font-size: 0.88rem; margin-bottom: 0.35rem; color: #0f172a;">
                Catatan : Tuliskan temuan asesmen pencapaian hasil reviu produk, jika belum/tidak terpenuhi :
            </div>
            <textarea class="input-inline-bnsp" rows="3" placeholder="Tuliskan catatan temuan asesmen...">Seluruh spesifikasi produk sistem informasi telah terpenuhi dengan baik dan siap diimplementasikan secara penuh.</textarea>
        </div>

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

        @include('komponen.navigasi-form-bawah')

    </div>
</div>
@endsection

@push('js')
    <script src="{{ asset('js/formulir/formulir-bnsp.js') }}"></script>
@endpush
