@extends('tata-letak.dasbor')

@section('judul', 'FR.IA.05A - Pertanyaan Tertulis Pilihan Ganda')

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
        'kodeForm' => 'FR.IA.05A',
        'namaForm' => 'Pertanyaan Tertulis Pilihan Ganda',
        'pendaftaranId' => $pendaftaran->id,
        'isAsesi' => $isAsesi,
        'saveLabel' => 'Simpan Formulir',
        'saveAction' => "alert('Formulir FR.IA.05A berhasil disimpan!')"
    ])

    @if($isAsesi)
        @include('komponen.banner-readonly-asesi', [
            'judul' => 'Daftar Pertanyaan Pilihan Ganda (Hanya Baca)',
            'keterangan' => 'Untuk mengerjakan ujian pilihan ganda interaktif, silakan buka menu FR.IA.05C (Lembar Ujian PG).',
            'status' => 'Hanya Baca',
            'tipe' => 'info'
        ])
    @endif

    <div class="dokumen-kertas">
        
        <!-- KOP RESMI DOKUMEN STANDAR BNSP -->
        @include('komponen.kop-formulir-bnsp', [
            'kodeForm' => 'FR.IA.05A',
            'judulForm' => 'DPT – PERTANYAAN TERTULIS PILIHAN GANDA',
            'tipeDokumen' => 'Soal Pilihan Ganda'
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
            <tr>
                <td colspan="2" style="font-weight: 600;">Waktu</td>
                <td>:</td>
                <td>60 Menit</td>
            </tr>
        </table>
        <div style="font-size: 0.75rem; font-style: italic; color: #64748b; margin-top: -1rem; margin-bottom: 1.25rem;">*Coret yang tidak perlu</div>

        <!-- DAFTAR UNIT KOMPETENSI SKEMA -->
        <table class="tabel-bnsp" style="margin-bottom: 1.25rem;">
            <thead>
                <tr style="background-color: #f1f5f9;">
                    <th style="width: 5%; text-align: center;">No.</th>
                    <th style="width: 25%; text-align: center;">Kode Unit</th>
                    <th style="width: 50%; text-align: center;">Judul Unit Kompetensi</th>
                    <th style="width: 20%; text-align: center;">Standar Kompetensi</th>
                </tr>
            </thead>
            <tbody>
                @forelse($pendaftaran->skema->unitKompetensi as $idxU => $u)
                    <tr>
                        <td style="text-align: center;">{{ $idxU + 1 }}</td>
                        <td style="font-weight: 700; font-family: monospace;">{{ $u->kode_unit }}</td>
                        <td>{{ $u->nama_unit ?? $u->judul_unit }}</td>
                        <td style="font-size: 0.85rem; color: #475569;">{{ $u->standar_kompetensi ?? 'SKKNI' }}</td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="4" style="text-align: center; color: #94a3b8; font-style: italic;">
                            Tidak ada unit kompetensi terdaftar pada skema sertifikasi ini.
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>

        <!-- DAFTAR PERTANYAAN PILIHAN GANDA -->
        <div style="margin-bottom: 1.5rem;">
            <div style="font-weight: 800; font-size: 0.95rem; color: #0f172a; margin-bottom: 0.75rem;">
                Jawab semua pertanyaan berikut:
            </div>

            @foreach($soalList as $no => $item)
                <div class="soal-item" style="margin-bottom: 1.25rem; padding: 1rem; border: 1px solid #e2e8f0; border-radius: 8px; background: #ffffff;">
                    <div class="soal-pertanyaan" style="font-weight: 600; color: #0f172a; margin-bottom: 0.75rem;">
                        {{ $no }}. {{ $item['pertanyaan'] }}
                    </div>
                    <div class="soal-opsi" style="display: grid; grid-template-columns: repeat(auto-fit, minmax(280px, 1fr)); gap: 0.5rem;">
                        @foreach($item['opsi'] as $abjad => $teksOpsi)
                            <label style="display: flex; align-items: center; gap: 0.5rem; cursor: pointer; padding: 0.4rem 0.6rem; border: 1px solid #e2e8f0; border-radius: 6px;">
                                <input type="radio" name="preview_soal_{{ $no }}" value="{{ $abjad }}" style="accent-color: #0284c7;">
                                <strong style="color: #0369a1;">{{ $abjad }}.</strong> {{ $teksOpsi }}
                            </label>
                        @endforeach
                    </div>
                </div>
            @endforeach

        </div>

        <!-- TABEL PENYUSUN DAN VALIDATOR -->
        <div style="margin-top: 2rem;">
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
            'nextUrl' => $isAsesi ? route('formulir.ia05c', $pendaftaran->id) : route('formulir.ia05b', $pendaftaran->id),
            'nextLabel' => $isAsesi ? 'FR.IA.05C (Ujian PG)' : 'FR.IA.05B (Kunci Jawaban)'
        ])

    </div>
</div>
@endsection

@push('js')
    <script src="{{ asset('js/formulir/formulir-bnsp.js') }}"></script>
@endpush

