@extends('tata-letak.dasbor')

@section('judul', 'FR.IA.03 - Pertanyaan Mendukung Observasi (PMO)')

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
        $isAsesor = auth()->check() && auth()->user()->peran === 'asesor';
        $savedPertanyaan = $iaRecord->data_jawaban['pertanyaan'] ?? [];
        $savedRespon = $iaRecord->data_jawaban['respon'] ?? [];
        $savedPencapaian = $iaRecord->data_jawaban['pencapaian'] ?? [];
    @endphp

    <!-- ACTION BAR ATAS -->
    @include('komponen.action-bar-formulir', [
        'kodeForm' => 'FR.IA.03',
        'namaForm' => 'Pertanyaan Mendukung Observasi',
        'pendaftaranId' => $pendaftaran->id,
        'isAsesi' => $isAsesi,
        'showSave' => $isAsesor,
        'saveLabel' => 'Simpan Formulir',
        'saveFormId' => 'form-ia-03',
        'signed' => (bool)$asesiTtd,
        'signedLabel' => 'Hasil Terverifikasi & Ditandatangani',
        'signRoute' => route('formulir.ia.simpan-ttd-asesi', ['kodeForm' => 'FR.IA.03', 'pendaftaranId' => $pendaftaran->id]),
        'signLabel' => 'Tanda Tangani Hasil Asesmen'
    ])

    @if($isAsesi)
        @include('komponen.banner-readonly-asesi', [
            'judul' => 'Mode Pratinjau Asesi (Hanya Baca)',
            'keterangan' => 'Pertanyaan lisan pendukung observasi ini dinilai langsung oleh Asesor Penguji Anda.',
            'status' => 'Hanya Baca',
            'tipe' => 'info'
        ])
    @endif

    <div class="dokumen-kertas">
        
        <!-- KOP RESMI DOKUMEN STANDAR BNSP -->
        @include('komponen.kop-formulir-bnsp', [
            'kodeForm' => 'FR.IA.03',
            'judulForm' => 'PMO – PERTANYAAN MENDUKUNG OBSERVASI',
            'tipeDokumen' => 'Pertanyaan Lisan'
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
            <ul>
                <li>Formulir ini digunakan untuk mencatat pertanyaan lisan yang diajukan asesor guna mendukung observasi langsung dan memastikan pencapaian kompetensi secara komprehensif.</li>
                <li>Pertanyaan difokuskan pada aspek kritis, penanganan situasi darurat, prosedur K3, atau logika pemecahan masalah.</li>
                <li>Beri tanda centang (&radic;) pada kolom <strong>M</strong> (Memuaskan) atau <strong>BM</strong> (Belum Memuaskan).</li>
            </ul>
        </div>

        <form id="form-ia-03" action="{{ route('formulir.ia.simpan', ['kodeForm' => 'FR.IA.03', 'pendaftaranId' => $pendaftaran->id]) }}" method="POST">
            @csrf
            
            @forelse($pendaftaran->skema->unitKompetensi as $uIdx => $unit)
                <div style="margin-bottom: 1.75rem;">
                    <div style="background: #e2e8f0; padding: 0.6rem 0.85rem; font-weight: 800; border: 1px solid #334155; border-bottom: none; font-size: 0.95rem; color: #0f172a;">
                        Unit Kompetensi {{ $uIdx + 1 }}: {{ $unit->kode_unit }} - {{ $unit->judul_unit }}
                    </div>
                    
                    <table class="tabel-bnsp" style="margin-bottom: 0; font-size: 0.85rem;">
                        <thead>
                            <tr>
                                <th style="width: 5%; text-align: center;">No.</th>
                                <th style="width: 48%;">Kriteria Unjuk Kerja & Pertanyaan Asesor</th>
                                <th style="width: 33%;">Tanggapan / Respon Lisan Asesi</th>
                                <th colspan="2" style="width: 14%; text-align: center;">Pencapaian</th>
                            </tr>
                            <tr>
                                <th></th>
                                <th></th>
                                <th></th>
                                <th style="text-align: center; width: 7%;">M</th>
                                <th style="text-align: center; width: 7%;">BM</th>
                            </tr>
                        </thead>
                        <tbody>
                            @php $qCount = 1; @endphp
                            @foreach($unit->elemenKompetensi as $elem)
                                @foreach($elem->kriteriaUnjukKerja as $kuk)
                                    @php
                                        $defaultPertanyaan = 'Jelaskan prosedur dan pertimbangan utama Anda saat mengeksekusi langkah kerja pada kriteria unjuk kerja ' . $kuk->nomor_kuk . ' ini?';
                                        $valPertanyaan = $savedPertanyaan[$kuk->id] ?? $defaultPertanyaan;
                                        $valRespon = $savedRespon[$kuk->id] ?? 'Asesi dapat menjelaskan prosedur teknis dengan tepat, runtut, dan sesuai standar SOP kerja.';
                                        $valPencapaian = $savedPencapaian[$kuk->id] ?? null;
                                    @endphp
                                    <tr>
                                        <td style="text-align: center; font-weight: 700; vertical-align: top;">{{ $qCount }}.</td>
                                        <td>
                                            <div style="margin-bottom: 0.4rem;">
                                                <strong style="color: #0284c7;">KUK {{ $kuk->nomor_kuk }}</strong>: {{ $kuk->pernyataan_kuk }}
                                            </div>
                                            <div>
                                                <label style="font-size: 0.8rem; font-weight: 700; color: #1e293b; display: flex; align-items: center; gap: 0.35rem; margin-bottom: 0.25rem;">
                                                    Pertanyaan Asesor:
                                                </label>
                                                <textarea name="pertanyaan[{{ $kuk->id }}]" class="input-inline-bnsp" rows="2" placeholder="Ketikkan pertanyaan lisan spesifik dari asesor di sini..." {{ !$isAsesor ? 'readonly' : '' }} style="font-size: 0.84rem; background: {{ $isAsesor ? '#ffffff' : '#f8fafc' }};">{{ $valPertanyaan }}</textarea>
                                            </div>
                                        </td>
                                        <td>
                                            <textarea name="respon[{{ $kuk->id }}]" class="input-inline-bnsp" rows="3" placeholder="Tuliskan tanggapan / respon lisan asesi..." {{ !$isAsesor ? 'readonly' : '' }} style="font-size: 0.84rem;">{{ $valRespon }}</textarea>
                                        </td>
                                        <td style="text-align: center; vertical-align: middle;">
                                            <input type="radio" name="pencapaian[{{ $kuk->id }}]" value="M" {{ $valPencapaian === 'M' ? 'checked' : '' }} class="checkbox-bnsp checkbox-bnsp-hijau" {{ !$isAsesor ? 'disabled' : '' }}>
                                        </td>
                                        <td style="text-align: center; vertical-align: middle;">
                                            <input type="radio" name="pencapaian[{{ $kuk->id }}]" value="BM" {{ $valPencapaian === 'BM' ? 'checked' : '' }} class="checkbox-bnsp checkbox-bnsp-merah" {{ !$isAsesor ? 'disabled' : '' }}>
                                        </td>
                                    </tr>
                                    @php $qCount++; @endphp
                                @endforeach
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @empty
                <p style="text-align: center; color: #64748b;">Belum ada data unit kompetensi.</p>
            @endforelse

            <!-- UMPAN BALIK & REKOMENDASI -->
            <div style="border: 1px solid #334155; padding: 1rem; border-radius: 4px; margin-bottom: 1.5rem; background: #f8fafc;">
                <label style="font-weight: 700; font-size: 0.88rem; display: block; margin-bottom: 0.35rem; color: #0f172a;">
                    Umpan Balik untuk Asesi:
                </label>
                <textarea name="umpan_balik" class="input-inline-bnsp" rows="2" placeholder="Tuliskan umpan balik terkait pemahaman konsep teknis..." {{ !$isAsesor ? 'readonly' : '' }}>Asesi menunjukkan penguasaan teori yang sangat baik dan mampu menjelaskan logika sistem dengan logis.</textarea>
            </div>

            <!-- PENGESAHAN ASESI & ASESOR -->
            <table class="tabel-bnsp">
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
                            @if($isAsesi)
                                <div style="margin-top: 0.5rem;" class="no-print">
                                    <button type="submit" form="form-ttd-asesi-ia03" class="tombol tombol-utama tombol-sm" style="background: #2563eb; border-color: #2563eb; font-size: 0.82rem;">
                                        Tanda Tangani & Setujui Hasil Penilaian
                                    </button>
                                </div>
                            @else
                                <span style="font-style: italic; color: #64748b;">(Belum Ditandatangani Asesi)</span>
                            @endif
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
        </form>

        @if($isAsesi)
            <form id="form-ttd-asesi-ia03" action="{{ route('formulir.ia.simpan-ttd-asesi', ['kodeForm' => 'FR.IA.03', 'pendaftaranId' => $pendaftaran->id]) }}" method="POST" style="display: none;">
                @csrf
            </form>
        @endif

    </div>
</div>
@endsection

@push('js')
    <script src="{{ asset('js/formulir/formulir-bnsp.js') }}"></script>
@endpush
