@extends('tata-letak.dasbor')

@section('judul', 'FR.IA.06B - Lembar Kunci Jawaban Pertanyaan Esai')

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
        'kodeForm' => 'FR.IA.06B',
        'namaForm' => 'Lembar Kunci Jawaban Esai',
        'pendaftaranId' => $pendaftaran->id,
        'isAsesi' => $isAsesi,
        'saveLabel' => 'Simpan Formulir',
        'saveAction' => "alert('Kunci Jawaban FR.IA.06B berhasil disimpan!')"
    ])

    @if($isAsesi)
        @include('komponen.banner-readonly-asesi', [
            'judul' => 'Mode Pratinjau Kunci Jawaban Esai (Hanya Baca)',
            'keterangan' => 'Kunci jawaban standar BNSP untuk pembanding penilaian soal esai.',
            'status' => 'Hanya Baca',
            'tipe' => 'info'
        ])
    @endif

    <div class="dokumen-kertas">
        
        <!-- KOP RESMI DOKUMEN STANDAR BNSP -->
        @include('komponen.kop-formulir-bnsp', [
            'kodeForm' => 'FR.IA.06B',
            'judulForm' => 'LEMBAR KUNCI JAWABAN PERTANYAAN TERTULIS ESAI',
            'tipeDokumen' => 'Kunci Jawaban Esai'
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

        <!-- TABEL KUNCI JAWABAN ESAI & RUBRIK PENILAIAN -->
        <div style="margin-bottom: 1.5rem;">
            <div style="font-weight: 800; font-size: 0.95rem; color: #0f172a; margin-bottom: 0.75rem; text-transform: uppercase;">
                KUNCI JAWABAN RUJUKAN & RUBRIK PENILAIAN ASESOR
            </div>
            <table class="tabel-bnsp">
                <thead>
                    <tr>
                        <th style="width: 5%; text-align: center;">No.</th>
                        <th style="width: 45%;">Pertanyaan & Unit / KUK</th>
                        <th style="width: 50%;">Kunci Jawaban Acuan / Rubrik Penilaian Standar</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($soalList as $no => $item)
                        <tr>
                            <td style="text-align: center; font-weight: 700; vertical-align: top;">{{ $no }}.</td>
                            <td style="vertical-align: top;">
                                <strong style="color: #0f172a; display: block; margin-bottom: 0.25rem;">{{ $item['pertanyaan'] }}</strong>
                                <span style="font-size: 0.75rem; color: #0284c7; font-weight: 700;">{{ $item['kuk'] }}</span>
                            </td>
                            <td style="vertical-align: top; background: #f8fafc;">
                                <div style="color: #334155; font-size: 0.88rem; line-height: 1.6;">
                                    {{ $item['kunci_referensi'] }}
                                </div>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
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

        @include('komponen.navigasi-form-bawah', [
            'prevUrl' => route('formulir.ia06a', $pendaftaran->id),
            'prevLabel' => 'FR.IA.06A (Soal Esai)',
            'nextUrl' => route('formulir.ia06c', $pendaftaran->id),
            'nextLabel' => 'FR.IA.06C (Ujian & Penilaian Esai)'
        ])

    </div>
</div>
@endsection

@push('js')
    <script src="{{ asset('js/formulir/formulir-bnsp.js') }}"></script>
@endpush

