@extends('tata-letak.dasbor')

@section('judul', 'FR.IA.02 - Tugas Praktik Demonstrasi')

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
        'kodeForm' => 'FR.IA.02',
        'namaForm' => 'Tugas Praktik Demonstrasi',
        'pendaftaranId' => $pendaftaran->id,
        'isAsesi' => $isAsesi,
        'saveLabel' => 'Simpan Formulir',
        'saveFormId' => 'form-ia-02'
    ])

    @if($isAsesi)
        @include('komponen.banner-readonly-asesi', [
            'judul' => 'Panduan Tugas Praktik Demonstrasi (Hanya Baca)',
            'keterangan' => 'Pelajari skenario, daftar unit kompetensi, dan perlengkapan demonstrasi praktik kerja berikut.',
            'status' => 'Panduan Asesi',
            'tipe' => 'info'
        ])
    @endif

    <div class="dokumen-kertas">
        
        <!-- KOP RESMI DOKUMEN STANDAR BNSP -->
        @include('komponen.kop-formulir-bnsp', [
            'kodeForm' => 'FR.IA.02',
            'judulForm' => 'TPD - TUGAS PRAKTIK DEMONSTRASI',
            'tipeDokumen' => 'Tugas Praktik'
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

        <!-- A. PETUNJUK -->
        <div style="margin-bottom: 1.5rem;">
            <div style="font-weight: 800; font-size: 0.95rem; color: #0f172a; margin-bottom: 0.4rem;">A. Petunjuk</div>
            <ol style="margin: 0; padding-left: 1.2rem; font-size: 0.88rem; color: #334155; line-height: 1.6;">
                <li>Baca dan pelajari setiap instruksi kerja di bawah ini dengan cermat sebelum melaksanakan praktek.</li>
                <li>Klarifikasi kepada asesor kompetensi apabila ada hal-hal yang belum jelas.</li>
                <li>Laksanakan pekerjaan sesuai dengan urutan proses yang sudah ditetapkan.</li>
                <li>Seluruh proses kerja mengacu kepada SOP/WI yang dipersyaratkan (Jika Ada).</li>
            </ol>
        </div>

        <form id="form-ia-02" method="POST" action="{{ route('formulir.ia.simpan', ['kodeForm' => 'FR.IA.02', 'pendaftaranId' => $pendaftaran->id]) }}">
            @csrf
            @php
                $judulTugasVal = $dataPraktik['judul_tugas'] ?? ($pendaftaran->skema ? 'Tugas Praktik Demonstrasi ' . $pendaftaran->skema->nama_skema : 'Tugas Praktik Demonstrasi');
                $skenarioVal = $dataPraktik['skenario'] ?? '';
                $peralatanVal = is_array($dataPraktik['peralatan_bahan'] ?? null) ? implode("\n", $dataPraktik['peralatan_bahan']) : ($dataPraktik['peralatan_bahan'] ?? '');
                $durasiVal = $dataPraktik['durasi_waktu'] ?? '120 Menit';
                $instruksiVal = is_array($dataPraktik['instruksi_kerja'] ?? null) ? implode("\n", $dataPraktik['instruksi_kerja']) : ($dataPraktik['instruksi_kerja'] ?? '');
            @endphp

        <!-- B. SKENARIO TUGAS PRAKTIK DEMONSTRASI -->
        <div style="margin-bottom: 1.5rem;">
            <div style="font-weight: 800; font-size: 0.95rem; color: #0f172a; margin-bottom: 0.75rem;">B. Skenario Tugas Praktik Demonstrasi</div>

            <!-- KELOMPOK PEKERJAAN 1 -->
            <table class="tabel-bnsp" style="margin-bottom: 0.75rem;">
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

            <!-- DETAIL SKENARIO PRAKTIK (DATABASE DRIVEN) -->
            <div style="background: #f8fafc; border: 1px solid #cbd5e1; border-radius: 4px; padding: 1rem; margin-bottom: 1.5rem;">
                <div style="margin-bottom: 0.85rem;">
                    <label style="font-weight: 700; font-size: 0.88rem; display: block; margin-bottom: 0.35rem; color: #0f172a;">Judul Tugas Praktik:</label>
                    <input type="text" name="judul_tugas" class="input-inline-bnsp" value="{{ $judulTugasVal }}" placeholder="Masukkan judul tugas praktik..." {{ $isAsesi ? 'readonly' : '' }}>
                </div>
                <div style="margin-bottom: 0.85rem;">
                    <label style="font-weight: 700; font-size: 0.88rem; display: block; margin-bottom: 0.35rem; color: #0f172a;">Skenario Tugas Praktik Demonstrasi:</label>
                    <textarea name="skenario" class="input-inline-bnsp" rows="4" placeholder="{{ $isAsesi ? 'Skenario penugasan praktik belum diisi oleh Asesor di sistem.' : 'Tuliskan skenario penugasan demonstrasi praktik kerja yang harus diselesaikan oleh asesi...' }}" {{ $isAsesi ? 'readonly' : '' }}>{{ $skenarioVal }}</textarea>
                </div>
                <div style="margin-bottom: 0.85rem;">
                    <label style="font-weight: 700; font-size: 0.88rem; display: block; margin-bottom: 0.35rem; color: #0f172a;">Perlengkapan dan Peralatan (pisahkan baris baru):</label>
                    <textarea name="peralatan_bahan" class="input-inline-bnsp" rows="3" placeholder="{{ $isAsesi ? 'Daftar alat dan bahan belum diisi.' : 'Sebutkan alat, bahan uji, APD, dan peralatan yang diperlukan (satu per baris)...' }}" {{ $isAsesi ? 'readonly' : '' }}>{{ $peralatanVal }}</textarea>
                </div>
                <div style="margin-bottom: 0.85rem;">
                    <label style="font-weight: 700; font-size: 0.88rem; display: block; margin-bottom: 0.35rem; color: #0f172a;">Durasi Waktu Praktik:</label>
                    <input type="text" name="durasi_waktu" class="input-inline-bnsp" value="{{ $durasiVal }}" placeholder="Contoh: 120 Menit" {{ $isAsesi ? 'readonly' : '' }}>
                </div>
                <div>
                    <label style="font-weight: 700; font-size: 0.88rem; display: block; margin-bottom: 0.35rem; color: #0f172a;">Langkah / Instruksi Kerja (pisahkan baris baru):</label>
                    <textarea name="instruksi_kerja" class="input-inline-bnsp" rows="5" placeholder="{{ $isAsesi ? 'Instruksi kerja belum diisi.' : 'Tuliskan butir instruksi kerja yang wajib didemonstrasikan asesi (satu per baris)...' }}" {{ $isAsesi ? 'readonly' : '' }}>{{ $instruksiVal }}</textarea>
                </div>
            </div>

        </div>

        </form>

        <!-- PENGESAHAN ASESI & ASESOR -->
        <table class="tabel-bnsp" style="margin-bottom: 2rem;">
            <tr>
                <td style="width: 50%; font-weight: 700; background: #f8fafc;">ASESI :</td>
                <td style="width: 50%; font-weight: 700; background: #f8fafc;">ASESOR :</td>
            </tr>
            <tr>
                <td>
                    <div style="margin-bottom: 0.4rem;">Nama : <strong>{{ $asesiNama }}</strong></div>
                    <div style="margin-top: 1.5rem; border-top: 1px dashed #cbd5e1; padding-top: 0.4rem;">
                        Tanda tangan dan Tanggal :<br>
                        @if($asesiTtd)
                            <img src="{{ $asesiTtd }}" alt="TTD Asesi" style="max-height: 45px; margin-top: 0.25rem;">
                        @else
                            <span style="font-style: italic; color: #64748b;">(Tanda Tangan Digital Asesi)</span>
                        @endif
                        <div style="font-size: 0.78rem; color: #64748b; margin-top: 0.25rem;">{{ date('d-m-Y') }}</div>
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

    </div>
</div>
@endsection

@push('js')
    <script src="{{ asset('js/formulir/formulir-bnsp.js') }}"></script>
@endpush

