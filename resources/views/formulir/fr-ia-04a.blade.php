@extends('tata-letak.dasbor')

@section('judul', 'FR.IA.04A - Penjelasan Proyek Singkat (DIT)')

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
        $asesiTtd = $pendaftaran->tanda_tangan_asesi ?? null;
    @endphp

    <!-- ACTION BAR ATAS -->
    @include('komponen.action-bar-formulir', [
        'kodeForm' => 'FR.IA.04A',
        'namaForm' => 'Daftar Instruksi Terstruktur',
        'pendaftaranId' => $pendaftaran->id,
        'isAsesi' => $isAsesi,
        'saveLabel' => 'Simpan Formulir',
        'saveFormId' => 'form-ia-04a'
    ])

    @if($isAsesi)
        @include('komponen.banner-readonly-asesi', [
            'judul' => 'Petunjuk Teknis Proyek Singkat (Hanya Baca)',
            'keterangan' => 'Pelajari TOR penugasan proyek terstruktur berikut untuk persiapan demonstrasi dan presentasi.',
            'status' => 'Panduan Asesi',
            'tipe' => 'info'
        ])
    @endif

    <div class="dokumen-kertas">
        
        <!-- KOP RESMI DOKUMEN STANDAR BNSP -->
        @include('komponen.kop-formulir-bnsp', [
            'kodeForm' => 'FR.IA.04A',
            'judulForm' => 'DIT – DAFTAR INSTRUKSI TERSTRUKTUR (PENJELASAN PROYEK SINGKAT/ KEGIATAN TERSTRUKTUR LAINNYA*)',
            'tipeDokumen' => 'Instruksi Terstruktur'
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
                <td>Sewaktu / Tempat Kerja / Mandiri** (<strong>{{ $pendaftaran->tuk_type ?? '' }}</strong>)</td>
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
        <div style="font-size: 0.75rem; font-style: italic; color: #64748b; margin-top: -1rem; margin-bottom: 1.25rem;">**Coret yang tidak perlu</div>

        <!-- PANDUAN BAGI ASESOR -->
        <div style="border: 1px solid #334155; padding: 1rem 1.25rem; background: #f8fafc; border-radius: 4px; margin-bottom: 1.5rem;">
            <strong style="display: block; font-size: 0.95rem; color: #0f172a; margin-bottom: 0.5rem; text-transform: uppercase;">PANDUAN BAGI ASESOR</strong>
            <ul style="margin: 0; padding-left: 1.2rem; font-size: 0.85rem; color: #334155; line-height: 1.6;">
                <li>Tentukan proyek singkat atau kegiatan terstruktur lainnya yang harus dipersiapkan dan dipresentasikan oleh asesi.</li>
                <li>Proyek singkat atau kegiatan terstruktur lainnya dibuat untuk keseluruhan unit kompetensi dalam Skema Sertifikasi atau untuk masing-masing kelompok pekerjaan.</li>
                <li>Kumpulkan hasil proyek singkat atau kegiatan terstruktur lainnya sesuai dengan hasil keluaran yang telah ditetapkan.</li>
            </ul>
        </div>

        <!-- KELOMPOK PEKERJAAN -->
        <table class="tabel-bnsp" style="margin-bottom: 1.5rem;">
            <thead>
                <tr>
                    <th style="width: 22%; text-align: center;">Kelompok Pekerjaan</th>
                    <th style="width: 8%; text-align: center;">No.</th>
                    <th style="width: 25%;">Kode Unit</th>
                    <th>Judul Unit</th>
                </tr>
            </thead>
            <tbody>
                @forelse($pendaftaran->skema->unitKompetensi as $indexUnit => $unit)
                    <tr>
                        @if($indexUnit === 0)
                            <td rowspan="{{ count($pendaftaran->skema->unitKompetensi) }}" style="vertical-align: middle; text-align: center; font-weight: 700; background-color: #f8fafc;">
                                Kelompok Pekerjaan 1
                            </td>
                        @endif
                        <td style="text-align: center; font-weight: 700;">{{ $indexUnit + 1 }}.</td>
                        <td style="font-weight: 600; color: #0284c7;">{{ $unit->kode_unit }}</td>
                        <td style="font-weight: 600; color: #0f172a;">{{ $unit->judul_unit }}</td>
                    </tr>
                @empty
                    <tr><td colspan="4" style="text-align: center; color: #64748b;">Belum ada data unit kompetensi.</td></tr>
                @endforelse
            </tbody>
        </table>

        <!-- INSTRUKSI PROYEK SINGKAT (DATABASE DRIVEN) -->
        <form id="form-ia-04a" method="POST" action="{{ route('formulir.ia.simpan', ['kodeForm' => 'FR.IA.04A', 'pendaftaranId' => $pendaftaran->id]) }}">
            @csrf
            @php
                $skenarioVal = $dataProyek['skenario'] ?? '';
                $waktuVal = $dataProyek['waktu_menit'] ?? 90;
                $demoVal = $dataProyek['demonstrasi'] ?? '';
                $waktuDemoVal = $dataProyek['waktu_demo'] ?? 30;
                $umpanBalikVal = $dataProyek['umpan_balik'] ?? ($iaRecord->catatan_asesor ?? '');
            @endphp
        <table class="tabel-bnsp">
            <tr>
                <td style="width: 30%; font-weight: 700; background: #f8fafc;">
                    Hal yang harus disiapkan atau dilakukan atau dihasilkan untuk suatu proyek singkat/ kegiatan terstruktur lainnya
                </td>
                <td>
                    <div style="margin-bottom: 0.5rem; font-weight: 600; color: #0f172a;">
                        Skenario proyek singkat / kegiatan terstruktur lainnya yang berisikan data informasi, lingkup bahasan dan instruksi untuk asesi
                    </div>
                    <textarea name="skenario" class="input-inline-bnsp" rows="5" placeholder="{{ $isAsesi ? 'Skenario penugasan proyek belum diisi oleh Asesor di sistem.' : 'Tuliskan detail skenario proyek singkat dan instruksi untuk asesi...' }}" {{ $isAsesi ? 'readonly' : '' }}>{{ $skenarioVal }}</textarea>
                    <div style="margin-top: 0.75rem; display: flex; align-items: center; gap: 0.5rem;">
                        <strong style="font-size: 0.85rem;">Waktu :</strong>
                        <input type="text" name="waktu_menit" class="input-inline-bnsp" value="{{ $waktuVal }} Menit" style="max-width: 150px;" {{ $isAsesi ? 'readonly' : '' }}>
                    </div>
                </td>
            </tr>
            <tr>
                <td style="font-weight: 700; background: #f8fafc;">
                    Hal yang perlu didemonstrasikan /dipresentasikan
                </td>
                <td>
                    <div style="margin-bottom: 0.5rem; font-weight: 600; color: #0f172a;">
                        Hasil proyek singkat / kegiatan terstruktur lainnya
                    </div>
                    <textarea name="demonstrasi" class="input-inline-bnsp" rows="4" placeholder="{{ $isAsesi ? 'Poin demonstrasi belum diisi oleh Asesor.' : 'Tuliskan hal-hal yang perlu didemonstrasikan atau dipresentasikan oleh asesi...' }}" {{ $isAsesi ? 'readonly' : '' }}>{{ $demoVal }}</textarea>
                    <div style="margin-top: 0.75rem; display: flex; align-items: center; gap: 0.5rem;">
                        <strong style="font-size: 0.85rem;">Waktu :</strong>
                        <input type="text" name="waktu_demo" class="input-inline-bnsp" value="{{ $waktuDemoVal }} Menit" style="max-width: 150px;" {{ $isAsesi ? 'readonly' : '' }}>
                    </div>
                </td>
            </tr>
            <tr>
                <td style="font-weight: 700; background: #f8fafc;">
                    Umpan Balik Untuk Asesi:
                </td>
                <td>
                    <textarea name="umpan_balik" class="input-inline-bnsp" rows="3" placeholder="Tuliskan catatan umpan balik pelaksanaan proyek singkat..." {{ $isAsesi ? 'readonly' : '' }}>{{ $umpanBalikVal }}</textarea>
                </td>
            </tr>
        </table>
        </form></table>

        <!-- TANDA TANGAN PENGESAHAN -->
        <table class="tabel-bnsp" style="margin-bottom: 1.5rem;">
            <thead>
                <tr>
                    <th style="width: 33%; text-align: center;">Tanda Tangan Asesi</th>
                    <th style="width: 33%; text-align: center;">Tanda Tangan Asesor</th>
                    <th style="width: 34%; text-align: center;">Nama & Tanda Tangan Supervisor (Jika ada)</th>
                </tr>
            </thead>
            <tbody>
                <tr>
                    <td style="text-align: center; vertical-align: middle; height: 90px;">
                        @if($asesiTtd)
                            <img src="{{ $asesiTtd }}" alt="TTD Asesi" style="max-height: 45px; display: block; margin: 0 auto;">
                        @else
                            <span style="font-style: italic; color: #94a3b8; font-size: 0.8rem;">(TTD Asesi)</span>
                        @endif
                        <div style="font-weight: 700; margin-top: 0.5rem; color: #0f172a;">{{ $asesiNama }}</div>
                    </td>
                    <td style="text-align: center; vertical-align: middle;">
                        @if($asesorTtd)
                            <img src="{{ $asesorTtd }}" alt="TTD Asesor" style="max-height: 45px; display: block; margin: 0 auto;">
                        @else
                            <span style="font-style: italic; color: #94a3b8; font-size: 0.8rem;">(TTD Asesor)</span>
                        @endif
                        <div style="font-weight: 700; margin-top: 0.5rem; color: #0f172a;">{{ $asesorNama }}</div>
                    </td>
                    <td style="text-align: center; vertical-align: middle;">
                        <span style="font-style: italic; color: #94a3b8; font-size: 0.8rem;">(TTD Supervisor)</span>
                        <div style="font-weight: 600; margin-top: 0.5rem; color: #64748b;">Supervisor Tempat Kerja / TUK</div>
                    </td>
                </tr>
            </tbody>
        </table>
        <div style="font-size: 0.78rem; font-style: italic; color: #475569; margin-bottom: 1.5rem;">
            *) Apabila asesi pada Level 4 ke atas, berikan tugas proyek yang meliputi tentang pemecahan masalah dan analisa
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
            'nextUrl' => route('formulir.ia04b', $pendaftaran->id),
            'nextLabel' => 'FR.IA.04B (Penilaian Proyek)'
        ])

    </div>
</div>
@endsection

@push('js')
    <script src="{{ asset('js/formulir/formulir-bnsp.js') }}"></script>
@endpush

