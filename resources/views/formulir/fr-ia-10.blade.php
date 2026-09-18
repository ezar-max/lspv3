@extends('tata-letak.dasbor')

@section('judul', 'FR.IA.10 - Verifikasi Pihak Ketiga')

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
    @endphp

    <!-- ACTION BAR ATAS -->
    @include('komponen.action-bar-formulir', [
        'kembaliRoute' => route('formulir.index', ['pendaftaran_id' => $pendaftaran->id]),
        'kodeForm' => 'FR.IA.10',
        'namaForm' => 'FR.IA.10 Verifikasi Pihak Ketiga',
        'pendaftaranId' => $pendaftaran->id,
        'canSubmit' => !$isAsesi,
        'submitOnClick' => "simpanNotifikasiFormulir('FR.IA.10')",
        'submitLabel' => 'Simpan Formulir'
    ])

    @if($isAsesi)
        @include('komponen.banner-readonly-asesi', [
            'pesan' => 'Formulir verifikasi pihak ketiga (atasan/supervisor industri) ini ditinjau dan divalidasi oleh Asesor.'
        ])
    @endif

    <div class="dokumen-kertas">
        
        <!-- KOP RESMI DOKUMEN STANDAR BNSP -->
        @include('komponen.kop-formulir-bnsp', [
            'kodeForm' => 'FR.IA.10',
            'judulForm' => 'VPK – VERIFIKASI PIHAK KETIGA',
            'tipeDokumen' => 'Verifikasi Pihak Ketiga'
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
        <div style="display: flex; justify-content: space-between; font-size: 0.75rem; font-style: italic; color: #64748b; margin-top: -1rem; margin-bottom: 1.25rem;">
            <span>*Coret yang tidak perlu</span>
            <span style="font-weight: 700; color: #dc2626;">Informasi Rahasia</span>
        </div>

        <!-- PANDUAN BAGI ASESOR -->
        <div class="kotak-panduan-asesor">
            <strong>PANDUAN BAGI ASESOR</strong>
            <ol>
                <li>Verifikasi pihak ketiga dapat dilakukan untuk keseluruhan unit kompetensi dalam skema sertifikasi atau dilakukan untuk masing-masing kelompok pekerjaan dalam satu skema sertifikasi.</li>
                <li>Tentukan pihak ketiga yang akan dimintai verifikasi.</li>
                <li>Ajukan pertanyaan kepada pihak ketiga.</li>
                <li>Berikan penilaian kepada asesi berdasarkan verifikasi pihak ketiga.</li>
                <li>Pertanyaan/pernyataan dapat dikembangkan sesuai dengan konteks pekerjaan dan relasi.</li>
            </ol>
        </div>

        <!-- IDENTITAS PIHAK KETIGA -->
        <table class="tabel-bnsp" style="margin-bottom: 1.5rem;">
            <tr>
                <td style="width: 35%; font-weight: 700; background: #f8fafc;">Nama Pengawas/penyelia/atasan/orang lain di perusahaan :</td>
                <td><input type="text" class="input-inline-bnsp" placeholder="Nama lengkap atasan / penyelia..." value="Budi Santoso, S.Kom. (Lead Software Engineer)"></td>
            </tr>
            <tr>
                <td style="font-weight: 700; background: #f8fafc;">Tempat kerja :</td>
                <td><input type="text" class="input-inline-bnsp" placeholder="Nama perusahaan..." value="PT Teknologi Digital Nusantara"></td>
            </tr>
            <tr>
                <td style="font-weight: 700; background: #f8fafc;">Alamat :</td>
                <td><input type="text" class="input-inline-bnsp" placeholder="Alamat kantor..." value="Jl. Sudirman No. 45, Jakarta Pusat"></td>
            </tr>
            <tr>
                <td style="font-weight: 700; background: #f8fafc;">Telepon :</td>
                <td><input type="text" class="input-inline-bnsp" placeholder="Nomor telepon..." value="0812-3456-7890"></td>
            </tr>
        </table>

        <!-- TABEL PERTANYAAN VERIFIKASI -->
        <table class="tabel-bnsp" style="font-size: 0.88rem;">
            <thead>
                <tr>
                    <th style="width: 80%;">Pertanyaan</th>
                    <th style="width: 10%; text-align: center;">Ya</th>
                    <th style="width: 10%; text-align: center;">Tdk</th>
                </tr>
            </thead>
            <tbody>
                <tr>
                    <td>Apakah asesi bekerja dengan mempertimbangkan Kesehatan, Keamanan dan Keselamatan Kerja?</td>
                    <td style="text-align: center;"><input type="checkbox" checked class="checkbox-bnsp checkbox-bnsp-hijau"></td>
                    <td style="text-align: center;"><input type="checkbox" class="checkbox-bnsp checkbox-bnsp-merah"></td>
                </tr>
                <tr>
                    <td>Apakah asesi berinteraksi dengan harmonis didalam kelompoknya?</td>
                    <td style="text-align: center;"><input type="checkbox" checked class="checkbox-bnsp checkbox-bnsp-hijau"></td>
                    <td style="text-align: center;"><input type="checkbox" class="checkbox-bnsp checkbox-bnsp-merah"></td>
                </tr>
                <tr>
                    <td>Apakah asesi dapat mengelola tugas-tugas secara bersamaan?</td>
                    <td style="text-align: center;"><input type="checkbox" checked class="checkbox-bnsp checkbox-bnsp-hijau"></td>
                    <td style="text-align: center;"><input type="checkbox" class="checkbox-bnsp checkbox-bnsp-merah"></td>
                </tr>
                <tr>
                    <td>Apakah asesi dapat dengan cepat beradaptasi dengan peralatan dan lingkungan yang baru?</td>
                    <td style="text-align: center;"><input type="checkbox" checked class="checkbox-bnsp checkbox-bnsp-hijau"></td>
                    <td style="text-align: center;"><input type="checkbox" class="checkbox-bnsp checkbox-bnsp-merah"></td>
                </tr>
                <tr>
                    <td>Apakah asesi dapat merespon dengan cepat masalah-masalah yang ada di tempat kerjanya?</td>
                    <td style="text-align: center;"><input type="checkbox" checked class="checkbox-bnsp checkbox-bnsp-hijau"></td>
                    <td style="text-align: center;"><input type="checkbox" class="checkbox-bnsp checkbox-bnsp-merah"></td>
                </tr>
                <tr>
                    <td>Apakah Anda bersedia dihubungi jika verifikasi lebih lanjut dari pernyataan ini diperlukan?</td>
                    <td style="text-align: center;"><input type="checkbox" checked class="checkbox-bnsp checkbox-bnsp-hijau"></td>
                    <td style="text-align: center;"><input type="checkbox" class="checkbox-bnsp checkbox-bnsp-merah"></td>
                </tr>
            </tbody>
        </table>

        <!-- WAWANCARA PIHAK KETIGA -->
        <table class="tabel-bnsp" style="margin-bottom: 1.5rem;">
            <tr>
                <td style="width: 35%; font-weight: 700; background: #f8fafc;">Apa hubungan Anda dengan asesi?</td>
                <td><input type="text" class="input-inline-bnsp" value="Atasan Langsung / Tech Lead"></td>
            </tr>
            <tr>
                <td style="font-weight: 700; background: #f8fafc;">Berapa lama Anda bekerja dengan asesi?</td>
                <td><input type="text" class="input-inline-bnsp" value="1 Tahun 6 Bulan"></td>
            </tr>
            <tr>
                <td style="font-weight: 700; background: #f8fafc;">Seberapa dekat Anda bekerja dengan asesi di area yang dinilai?</td>
                <td><input type="text" class="input-inline-bnsp" value="Sangat dekat, memonitor daily standup dan code review setiap sprint"></td>
            </tr>
            <tr>
                <td style="font-weight: 700; background: #f8fafc;">Apa pengalaman teknis dan / atau kualifikasi Anda di bidang yang dinilai? <span style="font-weight: 400; font-size: 0.78rem; color: #64748b;">(termasuk asesmen atau kualifikasi pelatihan)</span></td>
                <td><textarea class="input-inline-bnsp" rows="2">Senior Software Architect dengan pengalaman lebih dari 8 tahun di industri teknologi dan bersertifikasi BNSP.</textarea></td>
            </tr>
        </table>

        <!-- KESIMPULAN & EVALUASI -->
        <table class="tabel-bnsp" style="margin-bottom: 1.5rem;">
            <tr>
                <td style="width: 45%; font-weight: 700; background: #f8fafc;">
                    Secara keseluruhan, apakah Anda yakin asesi melakukan sesuai standar yang diminta oleh unit kompetensi secara konsisten?
                </td>
                <td>
                    <textarea class="input-inline-bnsp" rows="2">Ya, asesi secara konsisten menunjukkan performa kerja yang sangat baik sesuai standar industri.</textarea>
                </td>
            </tr>
            <tr>
                <td style="font-weight: 700; background: #f8fafc;">
                    Identifikasi kebutuhan pelatihan lebih lanjut untuk asesi:
                </td>
                <td>
                    <textarea class="input-inline-bnsp" rows="2">Dapat diperdalam pada materi Microservices Architecture dan Cloud Infrastructure Deployment.</textarea>
                </td>
            </tr>
            <tr>
                <td style="font-weight: 700; background: #f8fafc;">
                    Ada komentar lain:
                </td>
                <td>
                    <textarea class="input-inline-bnsp" rows="2">Asesi memiliki dedikasi tinggi dan kemampuan problem-solving yang sangat solid.</textarea>
                </td>
            </tr>
        </table>

        <!-- PENGESAHAN ASESOR -->
        <div style="border: 1px solid #334155; padding: 1.25rem; border-radius: 4px; background: #f8fafc; display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 1rem;">
            <div>
                <strong style="display: block; font-size: 0.95rem; color: #0f172a;">Tanda tangan Asesor:</strong>
                <div style="height: 55px; display: flex; align-items: center; margin-top: 0.25rem;">
                    @if($asesorTtd)
                        <img src="{{ $asesorTtd }}" alt="TTD Asesor" style="max-height: 45px;">
                    @else
                        <span style="font-style: italic; color: #64748b; font-size: 0.85rem;">(Tanda Tangan Digital Asesor)</span>
                    @endif
                </div>
                <div style="font-weight: 700; color: #0f172a; margin-top: 0.25rem;">{{ $asesorNama }}</div>
                <div style="font-size: 0.78rem; color: #64748b;">No. Reg: {{ $asesorMet }}</div>
            </div>
            <div>
                <strong style="display: block; font-size: 0.95rem; color: #0f172a;">Tanggal:</strong>
                <div style="font-weight: 700; color: #0f172a; font-size: 1.1rem; margin-top: 0.5rem;">{{ date('d F Y') }}</div>
            </div>
        </div>

        <div style="font-size: 0.75rem; font-style: italic; color: #64748b; margin-top: 1rem; border-top: 1px solid #e2e8f0; padding-top: 0.5rem; margin-bottom: 1.5rem;">
            Diadopsi dari templat yang disediakan di Departemen Pendidikan dan Pelatihan, Australia. Merancang alat asesmen untuk hasil yang berkualitas di VET. 2008
        </div>

        @include('komponen.navigasi-form-bawah', [
            'pendaftaranId' => $pendaftaran->id,
            'prevForm' => ['route' => route('formulir.ia09', $pendaftaran->id), 'label' => 'FR.IA.09 Pertanyaan Wawancara'],
            'nextForm' => ['route' => route('formulir.ia.index', ['kodeForm' => 'FR.IA.11', 'pendaftaranId' => $pendaftaran->id]), 'label' => 'FR.IA.11 Meninjau Asesmen']
        ])

    </div>
</div>
@endsection

@push('js')
    <script src="{{ asset('js/formulir/formulir-bnsp.js') }}"></script>
@endpush
