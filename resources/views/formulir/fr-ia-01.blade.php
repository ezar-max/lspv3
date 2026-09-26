@extends('tata-letak.dasbor')

@section('judul', 'FR.IA.01 - Ceklis Observasi Aktivitas')

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
        'kodeForm' => 'FR.IA.01',
        'namaForm' => 'Ceklis Observasi Aktivitas',
        'pendaftaranId' => $pendaftaran->id,
        'isAsesi' => $isAsesi,
        'saveLabel' => 'Simpan Formulir',
        'saveFormId' => 'form-ia-01',
        'signed' => (bool)$asesiTtd,
        'signedLabel' => 'Hasil Terverifikasi & Ditandatangani',
        'signRoute' => route('formulir.ia.simpan-ttd-asesi', ['kodeForm' => 'FR.IA.01', 'pendaftaranId' => $pendaftaran->id]),
        'signLabel' => 'Tanda Tangani Hasil Asesmen'
    ])

    @if($isAsesi)
        @include('komponen.banner-readonly-asesi', [
            'judul' => 'Mode Pratinjau Asesi (Hanya Baca)',
            'keterangan' => 'Formulir dan instrumen penilaian ini diisi dan dinilai oleh Asesor Kompetensi Anda.',
            'status' => 'Hanya Baca',
            'tipe' => 'info'
        ])
    @endif

    <div class="dokumen-kertas">
        
        <!-- KOP RESMI DOKUMEN STANDAR BNSP -->
        @include('komponen.kop-formulir-bnsp', [
            'kodeForm' => 'FR.IA.01',
            'judulForm' => 'CL - CEKLIS OBSERVASI AKTIVITAS DI TEMPAT KERJA ATAU TEMPAT KERJA SIMULASI',
            'tipeDokumen' => 'Ceklis Observasi'
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

        <!-- PANDUAN BAGI ASESOR (SESUAI GAMBAR) -->
        <div style="border: 1px solid #0f172a; padding: 0.85rem 1.25rem; background: #ffffff; margin-bottom: 1.5rem;">
            <div style="font-weight: 800; font-size: 0.92rem; color: #0f172a; margin-bottom: 0.45rem; text-transform: uppercase;">
                PANDUAN BAGI ASESOR
            </div>
            <ul style="margin: 0; padding-left: 1.2rem; font-size: 0.84rem; color: #0f172a; line-height: 1.65; list-style-type: disc;">
                <li>Lengkapi nama unit kompetensi, elemen, dan kriteria unjuk kerja sesuai kolom dalam tabel.</li>
                <li>Isilah standar industri atau tempat kerja</li>
                <li>Beri tanda centang (&radic;) pada kolom "YA" jika Anda yakin asesi dapat melakukan/mendemonstrasikan tugas sesuai KUK, atau centang (&radic;) pada kolom "Tidak" bila sebaliknya.</li>
                <li>Penilaian Lanjut diisi bila hasil belum dapat disimpulkan, untuk itu gunakan metode lain sehingga keputusan dapat dibuat.</li>
                <li>Isilah kolom KUK sesuai dengan Unit Kompetensi/ SKKNI</li>
            </ul>
        </div>

        <form id="form-ia-01" method="POST" action="{{ route('formulir.ia.simpan', ['kodeForm' => 'FR.IA.01', 'pendaftaranId' => $pendaftaran->id]) }}">
            @csrf
            @php
                $standarMap = $savedData['standar_industri'] ?? [];
                $pencapaianMap = $savedData['pencapaian'] ?? [];
                $lanjutMap = $savedData['penilaian_lanjut'] ?? [];
                $catatanKukMap = $savedData['catatan_kuk'] ?? [];

                $allUnits = $pendaftaran->skema->unitKompetensi ?? collect();
                $meta = $masterInst->additional_metadata ?? [];
                if (!is_array($meta)) $meta = json_decode($meta, true) ?: [];
                $kelompokSplit = (int)($meta['kelompok_split'] ?? 0);
                $defaultStandarMaster = $meta['default_standard'] ?? null;
                $standarElemenMaster = $meta['standar_elemen'] ?? [];

                if ($kelompokSplit > 0 && $allUnits->count() > $kelompokSplit) {
                    $group1Units = $allUnits->slice(0, $kelompokSplit);
                    $group2Units = $allUnits->slice($kelompokSplit);
                } else {
                    $group1Units = $allUnits;
                    $group2Units = collect();
                }
            @endphp

        <!-- KELOMPOK PEKERJAAN 1 -->
        <table class="tabel-bnsp" style="width: 100%; border-collapse: collapse; margin-bottom: 1.5rem; font-size: 0.86rem;">
            <thead>
                <tr>
                    <th rowspan="{{ $group1Units->count() + 1 }}" style="width: 25%; text-align: center; vertical-align: middle; font-weight: 800; background-color: #ffffff; border: 1px solid #0f172a; padding: 8px 10px;">
                        Kelompok Pekerjaan 1
                    </th>
                    <th style="width: 8%; text-align: center; font-weight: 700; border: 1px solid #0f172a; padding: 6px;">No.</th>
                    <th style="width: 27%; text-align: center; font-weight: 700; border: 1px solid #0f172a; padding: 6px;">Kode Unit</th>
                    <th style="text-align: center; font-weight: 700; border: 1px solid #0f172a; padding: 6px;">Judul Unit</th>
                </tr>
            </thead>
            <tbody>
                @forelse($group1Units as $indexUnit => $unit)
                    <tr>
                        <td style="text-align: center; font-weight: 700; border: 1px solid #0f172a; padding: 6px;">{{ $indexUnit + 1 }}.</td>
                        <td style="font-weight: 600; color: #0f172a; border: 1px solid #0f172a; padding: 6px 10px;">{{ $unit->kode_unit }}</td>
                        <td style="font-weight: 600; color: #0f172a; border: 1px solid #0f172a; padding: 6px 10px;">{{ $unit->judul_unit }}</td>
                    </tr>
                @empty
                    <tr><td colspan="3" style="text-align: center; color: #64748b; font-style: italic; border: 1px solid #0f172a; padding: 10px;">Belum ada data unit kompetensi.</td></tr>
                @endforelse
            </tbody>
        </table>

        <!-- LOOP CEKLIS OBSERVASI PER UNIT KOMPETENSI (KELOMPOK 1) -->
        @foreach($group1Units as $indexUnit => $unit)
            <div style="margin-top: 1.75rem; margin-bottom: 2rem;" class="{{ $indexUnit > 0 ? 'page-break' : '' }}">
                
                <table class="tabel-bnsp" style="width: 100%; border-collapse: collapse; margin-bottom: 0; font-size: 0.88rem;">
                    <tr>
                        <td rowspan="2" style="width: 25%; font-weight: 800; vertical-align: middle; background-color: #ffffff; border: 1px solid #0f172a; padding: 8px 10px;">
                            Unit Kompetensi {{ $indexUnit + 1 }}
                        </td>
                        <td style="width: 14%; font-weight: 600; border: 1px solid #0f172a; padding: 6px 10px;">Kode Unit</td>
                        <td style="width: 2%; text-align: center; border: 1px solid #0f172a; padding: 6px 4px;">:</td>
                        <td style="font-weight: 700; color: #0f172a; border: 1px solid #0f172a; padding: 6px 10px;">{{ $unit->kode_unit }}</td>
                    </tr>
                    <tr>
                        <td style="font-weight: 600; border: 1px solid #0f172a; padding: 6px 10px;">Judul Unit</td>
                        <td style="text-align: center; border: 1px solid #0f172a; padding: 6px 4px;">:</td>
                        <td style="font-weight: 700; color: #0f172a; border: 1px solid #0f172a; padding: 6px 10px;">{{ $unit->judul_unit }}</td>
                    </tr>
                </table>

                <table class="tabel-bnsp" style="width: 100%; border-collapse: collapse; font-size: 0.82rem; margin-top: -1px;">
                    <thead>
                        <tr>
                            <th rowspan="2" style="width: 5%; text-align: center; border: 1px solid #0f172a; padding: 6px;">No.</th>
                            <th rowspan="2" style="width: 22%; text-align: center; border: 1px solid #0f172a; padding: 6px;">Elemen</th>
                            <th rowspan="2" style="width: 33%; text-align: center; border: 1px solid #0f172a; padding: 6px;">Kriteria Unjuk Kerja</th>
                            <th rowspan="2" style="width: 20%; text-align: center; border: 1px solid #0f172a; padding: 6px;">Standar Industri atau Tempat Kerja</th>
                            <th colspan="2" style="width: 12%; text-align: center; border: 1px solid #0f172a; padding: 6px;">Pencapaian</th>
                            <th rowspan="2" style="width: 8%; text-align: center; border: 1px solid #0f172a; padding: 6px;">Penilaian Lanjut</th>
                        </tr>
                        <tr>
                            <th style="width: 6%; text-align: center; border: 1px solid #0f172a; padding: 4px;">Ya</th>
                            <th style="width: 6%; text-align: center; border: 1px solid #0f172a; padding: 4px;">Tidak</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($unit->elemenKompetensi as $idxElem => $elem)
                            @php
                                $totalKuk = count($elem->kriteriaUnjukKerja);
                                $elemStandar = $standarMap[$elem->id] 
                                    ?? ($standarElemenMaster[$elem->id] 
                                    ?? ($defaultStandarMaster 
                                    ?? ('SKKNI ' . $unit->kode_unit)));
                            @endphp
                            @if($totalKuk > 0)
                                @foreach($elem->kriteriaUnjukKerja as $kIdx => $kuk)
                                    @php
                                        $curPencapaian = $pencapaianMap[$kuk->id] ?? null;
                                        $curLanjut = $lanjutMap[$kuk->id] ?? '';
                                    @endphp
                                    <tr>
                                        @if($kIdx === 0)
                                            <td rowspan="{{ $totalKuk }}" style="text-align: center; font-weight: 700; vertical-align: top; border: 1px solid #0f172a; padding: 6px;">
                                                {{ $idxElem + 1 }}
                                            </td>
                                            <td rowspan="{{ $totalKuk }}" style="font-weight: 600; color: #0f172a; vertical-align: top; border: 1px solid #0f172a; padding: 6px 8px;">
                                                {{ $elem->nomor_elemen }}. {{ $elem->nama_elemen }}
                                                @if(!empty($elem->pertanyaan_elemen))
                                                    <div style="font-size: 0.76rem; color: #0284c7; font-style: italic; margin-top: 0.25rem;">"{{ $elem->pertanyaan_elemen }}"</div>
                                                @endif
                                            </td>
                                        @endif

                                        <td style="border: 1px solid #0f172a; padding: 6px 8px; vertical-align: top;">
                                            <strong style="color: #0f172a;">{{ $elem->nomor_elemen }}.{{ $kuk->nomor_kuk }}</strong> {{ $kuk->pernyataan_kuk }}
                                        </td>

                                        @if($kIdx === 0)
                                            <!-- Standar Industri per Elemen Spanning Row (Sesuai Gambar) -->
                                            <td rowspan="{{ $totalKuk }}" style="border: 1px solid #0f172a; padding: 6px; vertical-align: middle; text-align: center; background: #fafafa;">
                                                <textarea name="standar_industri[{{ $elem->id }}]" rows="{{ max(2, $totalKuk) }}" class="input-inline-bnsp" style="width: 100%; border: 1px dashed #94a3b8; padding: 4px; font-size: 0.78rem; text-align: center; resize: vertical;" placeholder="Standar Industri..." {{ $isAsesi ? 'readonly' : '' }}>{{ $elemStandar }}</textarea>
                                            </td>
                                        @endif

                                        <td style="text-align: center; vertical-align: middle; border: 1px solid #0f172a; padding: 4px;">
                                            <input type="radio" name="pencapaian[{{ $kuk->id }}]" value="K" {{ $curPencapaian === 'K' || ($curPencapaian === null && $isAsesi) ? 'checked' : '' }} style="width: 16px; height: 16px; accent-color: #059669; cursor: pointer;" {{ $isAsesi ? 'disabled' : '' }}>
                                        </td>
                                        <td style="text-align: center; vertical-align: middle; border: 1px solid #0f172a; padding: 4px;">
                                            <input type="radio" name="pencapaian[{{ $kuk->id }}]" value="BK" {{ $curPencapaian === 'BK' ? 'checked' : '' }} style="width: 16px; height: 16px; accent-color: #dc2626; cursor: pointer;" {{ $isAsesi ? 'disabled' : '' }}>
                                        </td>
                                        <td style="border: 1px solid #0f172a; padding: 4px; vertical-align: middle;">
                                            <input type="text" name="penilaian_lanjut[{{ $kuk->id }}]" class="input-inline-bnsp" value="{{ $curLanjut }}" placeholder="..." style="font-size: 0.78rem; width: 100%;" {{ $isAsesi ? 'readonly' : '' }}>
                                        </td>
                                    </tr>
                                @endforeach
                            @else
                                <tr>
                                    <td style="text-align: center; font-weight: 700; border: 1px solid #0f172a; padding: 6px;">{{ $idxElem + 1 }}</td>
                                    <td style="font-weight: 600; border: 1px solid #0f172a; padding: 6px 8px;">{{ $elem->nomor_elemen }}. {{ $elem->nama_elemen }}</td>
                                    <td style="font-style: italic; color: #64748b; border: 1px solid #0f172a; padding: 6px 8px;">KUK belum diinput.</td>
                                    <td style="border: 1px solid #0f172a; padding: 6px;"><input type="text" class="input-inline-bnsp" value="SKKNI" style="font-size: 0.78rem;"></td>
                                    <td style="text-align: center; border: 1px solid #0f172a; padding: 4px;"><input type="checkbox" checked style="width: 16px; height: 16px;"></td>
                                    <td style="text-align: center; border: 1px solid #0f172a; padding: 4px;"><input type="checkbox" style="width: 16px; height: 16px;"></td>
                                    <td style="border: 1px solid #0f172a; padding: 4px;"><input type="text" class="input-inline-bnsp" style="font-size: 0.78rem;"></td>
                                </tr>
                            @endif
                        @empty
                            <tr><td colspan="7" style="text-align: center; color: #64748b; font-style: italic; border: 1px solid #0f172a; padding: 10px;">Belum ada data elemen.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        @endforeach

        <!-- ========================================================================= -->
        <!-- UMPAN BALIK UNTUK ASESI (SESUAI GAMBAR) -->
        <!-- ========================================================================= -->
        <div style="border: 1px solid #0f172a; padding: 0.85rem 1rem; margin-top: 1.5rem; margin-bottom: 1.5rem;">
            <div style="font-weight: 700; font-size: 0.92rem; margin-bottom: 0.45rem; color: #0f172a;">
                Umpan Balik untuk asesi:
            </div>
            <textarea name="umpan_balik" class="input-inline-bnsp" rows="3" style="width: 100%; border: 1px dashed #cbd5e1; padding: 0.5rem; font-size: 0.85rem; border-radius: 4px; box-sizing: border-box;" placeholder="Tuliskan umpan balik untuk asesi..." {{ $isAsesi ? 'readonly' : '' }}>{{ $iaRecord->catatan_asesor ?? ($savedData['umpan_balik'] ?? ($meta['umpan_balik'] ?? 'Seluruh instruksi kerja dan demonstrasi praktik telah diobservasi dengan baik sesuai standar kompetensi SKKNI.')) }}</textarea>
            
            <div style="margin-top: 0.85rem; padding: 0.75rem 1rem; background: #f8fafc; border: 1px solid #cbd5e1; border-radius: 4px;">
                <div style="font-weight: 700; font-size: 0.88rem; color: #0f172a; margin-bottom: 0.35rem;">Rekomendasi Keputusan Asesor:</div>
                <div style="display: flex; align-items: center; gap: 1.5rem;">
                    <label style="display: inline-flex; align-items: center; gap: 0.35rem; font-weight: 600; font-size: 0.85rem; cursor: pointer;">
                        <input type="radio" name="rekomendasi" value="K" {{ ($iaRecord->rekomendasi ?? 'K') === 'K' ? 'checked' : '' }} {{ $isAsesi ? 'disabled' : '' }}>
                        <span style="color: #16a34a;">Kompeten (K)</span>
                    </label>
                    <label style="display: inline-flex; align-items: center; gap: 0.35rem; font-weight: 600; font-size: 0.85rem; cursor: pointer;">
                        <input type="radio" name="rekomendasi" value="BK" {{ ($iaRecord->rekomendasi ?? '') === 'BK' ? 'checked' : '' }} {{ $isAsesi ? 'disabled' : '' }}>
                        <span style="color: #dc2626;">Belum Kompeten (BK)</span>
                    </label>
                </div>
            </div>
        </div>

        <!-- KELOMPOK PEKERJAAN 2 (JIKA ADA) -->
        @if($group2Units->count() > 0)
            <table class="tabel-bnsp" style="width: 100%; border-collapse: collapse; margin-top: 2rem; margin-bottom: 1.5rem; font-size: 0.86rem;">
                <thead>
                    <tr>
                        <th rowspan="{{ $group2Units->count() + 1 }}" style="width: 25%; text-align: center; vertical-align: middle; font-weight: 800; background-color: #ffffff; border: 1px solid #0f172a; padding: 8px 10px;">
                            Kelompok Pekerjaan 2
                        </th>
                        <th style="width: 8%; text-align: center; font-weight: 700; border: 1px solid #0f172a; padding: 6px;">No.</th>
                        <th style="width: 27%; text-align: center; font-weight: 700; border: 1px solid #0f172a; padding: 6px;">Kode Unit</th>
                        <th style="text-align: center; font-weight: 700; border: 1px solid #0f172a; padding: 6px;">Judul Unit</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($group2Units as $indexUnit => $unit)
                        <tr>
                            <td style="text-align: center; font-weight: 700; border: 1px solid #0f172a; padding: 6px;">{{ $indexUnit + 1 }}.</td>
                            <td style="font-weight: 600; color: #0f172a; border: 1px solid #0f172a; padding: 6px 10px;">{{ $unit->kode_unit }}</td>
                            <td style="font-weight: 600; color: #0f172a; border: 1px solid #0f172a; padding: 6px 10px;">{{ $unit->judul_unit }}</td>
                        </tr>
                    @endforeach
                </tbody>
            </table>

            @foreach($group2Units as $indexUnit => $unit)
                <div style="margin-top: 1.75rem; margin-bottom: 2rem;" class="page-break">
                    <table class="tabel-bnsp" style="width: 100%; border-collapse: collapse; margin-bottom: 0; font-size: 0.88rem;">
                        <tr>
                            <td rowspan="2" style="width: 25%; font-weight: 800; vertical-align: middle; background-color: #ffffff; border: 1px solid #0f172a; padding: 8px 10px;">
                                Unit Kompetensi {{ $group1Units->count() + $indexUnit + 1 }}
                            </td>
                            <td style="width: 14%; font-weight: 600; border: 1px solid #0f172a; padding: 6px 10px;">Kode Unit</td>
                            <td style="width: 2%; text-align: center; border: 1px solid #0f172a; padding: 6px 4px;">:</td>
                            <td style="font-weight: 700; color: #0f172a; border: 1px solid #0f172a; padding: 6px 10px;">{{ $unit->kode_unit }}</td>
                        </tr>
                        <tr>
                            <td style="font-weight: 600; border: 1px solid #0f172a; padding: 6px 10px;">Judul Unit</td>
                            <td style="text-align: center; border: 1px solid #0f172a; padding: 6px 4px;">:</td>
                            <td style="font-weight: 700; color: #0f172a; border: 1px solid #0f172a; padding: 6px 10px;">{{ $unit->judul_unit }}</td>
                        </tr>
                    </table>

                    <table class="tabel-bnsp" style="width: 100%; border-collapse: collapse; font-size: 0.82rem; margin-top: -1px;">
                        <thead>
                            <tr>
                                <th rowspan="2" style="width: 5%; text-align: center; border: 1px solid #0f172a; padding: 6px;">No.</th>
                                <th rowspan="2" style="width: 22%; text-align: center; border: 1px solid #0f172a; padding: 6px;">Elemen</th>
                                <th rowspan="2" style="width: 33%; text-align: center; border: 1px solid #0f172a; padding: 6px;">Kriteria Unjuk Kerja</th>
                                <th rowspan="2" style="width: 20%; text-align: center; border: 1px solid #0f172a; padding: 6px;">Standar Industri atau Tempat Kerja</th>
                                <th colspan="2" style="width: 12%; text-align: center; border: 1px solid #0f172a; padding: 6px;">Pencapaian</th>
                                <th rowspan="2" style="width: 8%; text-align: center; border: 1px solid #0f172a; padding: 6px;">Penilaian Lanjut</th>
                            </tr>
                            <tr>
                                <th style="width: 6%; text-align: center; border: 1px solid #0f172a; padding: 4px;">Ya</th>
                                <th style="width: 6%; text-align: center; border: 1px solid #0f172a; padding: 4px;">Tidak</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($unit->elemenKompetensi as $idxElem => $elem)
                                @php
                                    $totalKuk = count($elem->kriteriaUnjukKerja);
                                    $elemStandar = $standarMap[$elem->id] 
                                        ?? ($standarElemenMaster[$elem->id] 
                                        ?? ($defaultStandarMaster 
                                        ?? ('SKKNI ' . $unit->kode_unit)));
                                @endphp
                                @if($totalKuk > 0)
                                    @foreach($elem->kriteriaUnjukKerja as $kIdx => $kuk)
                                        @php
                                            $curPencapaian = $pencapaianMap[$kuk->id] ?? null;
                                            $curLanjut = $lanjutMap[$kuk->id] ?? '';
                                        @endphp
                                        <tr>
                                            @if($kIdx === 0)
                                                <td rowspan="{{ $totalKuk }}" style="text-align: center; font-weight: 700; vertical-align: top; border: 1px solid #0f172a; padding: 6px;">
                                                    {{ $idxElem + 1 }}
                                                </td>
                                                <td rowspan="{{ $totalKuk }}" style="font-weight: 600; color: #0f172a; vertical-align: top; border: 1px solid #0f172a; padding: 6px 8px;">
                                                    {{ $elem->nomor_elemen }}. {{ $elem->nama_elemen }}
                                                </td>
                                            @endif

                                            <td style="border: 1px solid #0f172a; padding: 6px 8px; vertical-align: top;">
                                                <strong style="color: #0f172a;">{{ $elem->nomor_elemen }}.{{ $kuk->nomor_kuk }}</strong> {{ $kuk->pernyataan_kuk }}
                                            </td>

                                            @if($kIdx === 0)
                                                <td rowspan="{{ $totalKuk }}" style="border: 1px solid #0f172a; padding: 6px; vertical-align: middle; text-align: center; background: #fafafa;">
                                                    <textarea name="standar_industri[{{ $elem->id }}]" rows="{{ max(2, $totalKuk) }}" class="input-inline-bnsp" style="width: 100%; border: 1px dashed #94a3b8; padding: 4px; font-size: 0.78rem; text-align: center; resize: vertical;" placeholder="Standar Industri..." {{ $isAsesi ? 'readonly' : '' }}>{{ $elemStandar }}</textarea>
                                                </td>
                                            @endif

                                            <td style="text-align: center; vertical-align: middle; border: 1px solid #0f172a; padding: 4px;">
                                                <input type="radio" name="pencapaian[{{ $kuk->id }}]" value="K" {{ $curPencapaian === 'K' || ($curPencapaian === null && $isAsesi) ? 'checked' : '' }} style="width: 16px; height: 16px; accent-color: #059669; cursor: pointer;" {{ $isAsesi ? 'disabled' : '' }}>
                                            </td>
                                            <td style="text-align: center; vertical-align: middle; border: 1px solid #0f172a; padding: 4px;">
                                                <input type="radio" name="pencapaian[{{ $kuk->id }}]" value="BK" {{ $curPencapaian === 'BK' ? 'checked' : '' }} style="width: 16px; height: 16px; accent-color: #dc2626; cursor: pointer;" {{ $isAsesi ? 'disabled' : '' }}>
                                            </td>
                                            <td style="border: 1px solid #0f172a; padding: 4px; vertical-align: middle;">
                                                <input type="text" name="penilaian_lanjut[{{ $kuk->id }}]" class="input-inline-bnsp" value="{{ $curLanjut }}" placeholder="..." style="font-size: 0.78rem; width: 100%;" {{ $isAsesi ? 'readonly' : '' }}>
                                            </td>
                                        </tr>
                                    @endforeach
                                @endif
                            @empty
                                <tr><td colspan="7" style="text-align: center; color: #64748b; font-style: italic; border: 1px solid #0f172a; padding: 10px;">Belum ada data elemen.</td></tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            @endforeach
        @endif
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
                                    <form action="{{ route('formulir.ia.simpan-ttd-asesi', ['kodeForm' => 'FR.IA.01', 'pendaftaranId' => $pendaftaran->id]) }}" method="POST">
                                        @csrf
                                        <button type="submit" class="tombol tombol-utama tombol-sm" style="background: #2563eb; border-color: #2563eb; font-size: 0.82rem;">
                                            Tanda Tangani & Setujui Hasil Penilaian
                                        </button>
                                    </form>
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

    </div>
</div>
@endsection

@push('js')
    <script src="{{ asset('js/formulir/formulir-bnsp.js') }}"></script>
@endpush

