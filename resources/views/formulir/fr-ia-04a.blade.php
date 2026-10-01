@extends('tata-letak.dasbor')

@section('judul', 'FR.IA.04A - Penjelasan Proyek Singkat (DIT)')

@push('css')
    <link rel="stylesheet" href="{{ asset('css/formulir/formulir-bnsp.css') }}">
@endpush

@section('konten')
<div class="wadah-formulir-bnsp animasi-slide {{ auth()->check() && auth()->user()->peran === 'asesi' ? 'mode-read-only-asesi' : '' }}">
    
    @php
        $isAsesi = auth()->check() && auth()->user()->peran === 'asesi';
        $isMasterMode = isset($isMasterMode) ? $isMasterMode : (empty($pendaftaran->id) || $pendaftaran->id == 0 || !request()->filled('pendaftaran_id'));
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
        @php
            $unitsIa04 = $pendaftaran->skema->unitKompetensi ?? collect();
        @endphp
        <table class="tabel-bnsp" style="width: 100%; border-collapse: collapse; margin-bottom: 1.5rem; font-size: 0.88rem; border: 1px solid #000000;">
            <tbody>
                <tr>
                    <td rowspan="{{ max(1, count($unitsIa04)) + 1 }}" style="width: 25%; text-align: center; vertical-align: middle; font-weight: 700; font-size: 0.95rem; border: 1px solid #000000; padding: 10px; background-color: #ffffff; color: #000000;">
                        <span style="display: inline-block; max-width: 130px; line-height: 1.35;">Kelompok Pekerjaan 1</span>
                    </td>
                    <th style="width: 8%; text-align: center; font-weight: 700; border: 1px solid #000000; padding: 6px 4px; background-color: #ffffff; color: #000000;">No.</th>
                    <th style="width: 28%; text-align: center; font-weight: 700; border: 1px solid #000000; padding: 6px 8px; background-color: #ffffff; color: #000000;">Kode Unit</th>
                    <th style="text-align: center; font-weight: 700; border: 1px solid #000000; padding: 6px 8px; background-color: #ffffff; color: #000000;">Judul Unit</th>
                </tr>
                @forelse($unitsIa04 as $indexUnit => $unit)
                    <tr>
                        <td style="text-align: center; font-weight: 700; border: 1px solid #000000; padding: 6px 4px; color: #000000;">{{ $indexUnit + 1 }}.</td>
                        <td style="font-weight: 700; color: #000000; border: 1px solid #000000; padding: 6px 10px;">{{ $unit->kode_unit }}</td>
                        <td style="font-weight: 700; color: #000000; border: 1px solid #000000; padding: 6px 10px;">{{ $unit->judul_unit }}</td>
                    </tr>
                @empty
                    <tr><td colspan="3" style="text-align: center; color: #64748b; font-style: italic; border: 1px solid #000000; padding: 10px;">Belum ada data unit kompetensi.</td></tr>
                @endforelse
            </tbody>
        </table>

        <!-- INSTRUKSI PROYEK SINGKAT (DATABASE DRIVEN) -->
        <form id="form-ia-04a" method="POST" action="{{ route('formulir.ia.simpan', ['kodeForm' => 'FR.IA.04A', 'pendaftaranId' => $pendaftaran->id]) }}">
            @csrf
            @php
                $skenarioVal = $dataProyek['skenario'] ?? '';
                $waktuVal = $dataProyek['waktu_menit'] ?? ($dataProyek['durasi_waktu'] ?? '');
                $demoVal = $dataProyek['demonstrasi'] ?? '';
                $waktuDemoVal = $dataProyek['waktu_demo'] ?? '';
                $umpanBalikVal = $dataProyek['umpan_balik'] ?? ($iaRecord->catatan_asesor ?? '');
            @endphp
        <table class="tabel-bnsp">
            <tr>
                <td style="width: 30%; font-weight: 700; background: #f8fafc;">
                    Hal yang harus disiapkan atau dilakukan atau dihasilkan untuk suatu proyek singkat/ kegiatan terstruktur lainnya
                </td>
                <td>
                    <div style="margin-bottom: 0.5rem; font-weight: 600; color: #0f172a;">
                        Skenario proyek singkat / kegiatan terstruktur lainnya yang berisikan data informasi, lingkup bahasan dan instruksi untuk asesi: <span style="color: #ef4444;">*</span>
                    </div>
                    <textarea name="skenario" class="input-inline-bnsp" rows="5" placeholder="{{ $isAsesi ? 'Skenario penugasan proyek belum diisi oleh Asesor di sistem.' : 'Tuliskan detail skenario proyek singkat dan instruksi untuk asesi...' }}" {{ $isAsesi ? 'readonly' : 'required' }}>{{ $skenarioVal }}</textarea>
                    <div style="margin-top: 0.75rem; display: flex; align-items: center; gap: 0.5rem;">
                        <strong style="font-size: 0.85rem;">Waktu : <span style="color: #ef4444;">*</span></strong>
                        <input type="text" name="waktu_menit" class="input-inline-bnsp" value="{{ $waktuVal ? ($waktuVal . (str_contains($waktuVal, 'Menit') ? '' : ' Menit')) : '' }}" placeholder="Contoh: 90 Menit" style="max-width: 180px;" {{ $isAsesi ? 'readonly' : 'required' }}>
                    </div>
                </td>
            </tr>
            <tr>
                <td style="font-weight: 700; background: #f8fafc;">
                    Hal yang perlu didemonstrasikan /dipresentasikan
                </td>
                <td>
                    <div style="margin-bottom: 0.5rem; font-weight: 600; color: #0f172a;">
                        Hasil proyek singkat / kegiatan terstruktur lainnya: <span style="color: #ef4444;">*</span>
                    </div>
                    <textarea name="demonstrasi" class="input-inline-bnsp" rows="4" placeholder="{{ $isAsesi ? 'Poin demonstrasi belum diisi oleh Asesor.' : 'Tuliskan hal-hal yang perlu didemonstrasikan atau dipresentasikan oleh asesi...' }}" {{ $isAsesi ? 'readonly' : 'required' }}>{{ $demoVal }}</textarea>
                    <div style="margin-top: 0.75rem; display: flex; align-items: center; gap: 0.5rem;">
                        <strong style="font-size: 0.85rem;">Waktu : <span style="color: #ef4444;">*</span></strong>
                        <input type="text" name="waktu_demo" class="input-inline-bnsp" value="{{ $waktuDemoVal ? ($waktuDemoVal . (str_contains($waktuDemoVal, 'Menit') ? '' : ' Menit')) : '' }}" placeholder="Contoh: 30 Menit" style="max-width: 180px;" {{ $isAsesi ? 'readonly' : 'required' }}>
                    </div>
                </td>
            </tr>
            <tr>
                <td style="font-weight: 700; background: #f8fafc;">
                    Umpan Balik Untuk Asesi: 
                    @if(!$isMasterMode)
                        <span style="color: #ef4444;">*</span>
                    @else
                        <span style="font-size: 0.75rem; font-weight: normal; color: #64748b; font-style: italic;">(Diisi saat Asesmen)</span>
                    @endif
                </td>
                <td>
                    @if($isMasterMode)
                        <textarea class="input-inline-bnsp" rows="3" placeholder="Catatan umpan balik pelaksanaan proyek singkat akan diisi oleh Asesor saat pelaksanaan asesmen." disabled readonly style="background-color: #f8fafc !important; cursor: not-allowed; color: #64748b; font-style: italic;"></textarea>
                    @else
                        <textarea name="umpan_balik" class="input-inline-bnsp" rows="3" placeholder="Tuliskan catatan umpan balik pelaksanaan proyek singkat..." {{ $isAsesi ? 'readonly' : 'required' }}>{{ $umpanBalikVal }}</textarea>
                    @endif
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
        @include('komponen.tabel-penyusun-validator', [
            'pendaftaran' => $pendaftaran,
            'kodeForm' => 'FR.IA.04A',
            'tableClass' => 'tabel-bnsp'
        ])

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

