@extends('tata-letak.dasbor')

@section('judul', 'FR.IA.09 - Pertanyaan Wawancara')

@push('css')
    <link rel="stylesheet" href="{{ asset('css/formulir/formulir-bnsp.css') }}">
    <style>
        .tabel-pw th {
            background-color: #f1f5f9;
            font-weight: 700;
            color: #0f172a;
        }
        .header-unit-pw {
            background-color: #1e293b !important;
            color: #ffffff !important;
            font-weight: 700;
            padding: 0.6rem 0.85rem;
            font-size: 0.88rem;
        }
    </style>
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

        $savedPW = $savedData['pertanyaan_wawancara'] ?? [];
        $masterIa09 = null;
        if ($pendaftaran->skema_id) {
            $masterIa09 = \App\Models\SchemeMasterInstrument::where('skema_id', $pendaftaran->skema_id)
                ->whereIn('instrument_code', ['ia_09', 'ia09'])
                ->first();
        }
        $masterPW = ($masterIa09 && !empty($masterIa09->additional_metadata['pertanyaan_wawancara']))
            ? $masterIa09->additional_metadata['pertanyaan_wawancara']
            : [];
        $kesimpulanVal = $savedData['kesimpulan'] ?? ($iaRecord->catatan_asesor ?? ($masterIa09->additional_metadata['umpan_balik'] ?? ''));
        $rekomendasiVal = $savedData['rekomendasi'] ?? ($iaRecord->rekomendasi ?? 'K');
    @endphp

    <!-- ACTION BAR ATAS -->
    @include('komponen.action-bar-formulir', [
        'kodeForm' => 'FR.IA.09',
        'namaForm' => 'FR.IA.09 Pertanyaan Wawancara',
        'pendaftaranId' => $pendaftaran->id,
        'canSubmit' => !$isAsesi,
        'saveFormId' => 'form-ia-09',
        'submitLabel' => 'Simpan Formulir',
        'canSignAsesi' => $isAsesi && !$asesiTtd,
        'signAsesiRoute' => route('formulir.ia.simpan-ttd-asesi', ['kodeForm' => 'FR.IA.09', 'pendaftaranId' => $pendaftaran->id]),
        'isAsesiSigned' => $isAsesi && $asesiTtd,
        'prevForm' => ['route' => route('formulir.ia08', $pendaftaran->id), 'label' => 'FR.IA.08 Portofolio'],
        'nextForm' => ['route' => route('formulir.ia10', $pendaftaran->id), 'label' => 'FR.IA.10 Pihak Ketiga']
    ])

    @if($isAsesi)
        @include('komponen.banner-readonly-asesi', [
            'pesan' => 'Formulir Pertanyaan Wawancara (FR.IA.09) ini merupakan lembar klarifikasi langsung oleh Asesor Penguji Anda.'
        ])
    @endif

    <div class="dokumen-kertas">
        
        <!-- KOP RESMI DOKUMEN STANDAR BNSP -->
        @include('komponen.kop-formulir-bnsp', [
            'kodeForm' => 'FR.IA.09',
            'judulForm' => 'PW – PERTANYAAN WAWANCARA',
            'tipeDokumen' => 'Klarifikasi Portofolio'
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
                <td style="font-weight: 700; color: #0f172a;">{{ $pendaftaran->skema->nama_skema ?? 'Skema Sertifikasi' }}</td>
            </tr>
            <tr>
                <td style="font-weight: 600;">Nomor</td>
                <td>:</td>
                <td style="font-weight: 700; color: #0f172a;">{{ $pendaftaran->skema->kode_skema ?? '-' }}</td>
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
                <li>Formulir ini digunakan untuk mengklarifikasi pemenuhan bukti portofolio asesi yang belum memadai (VATM) pada FR.IA.08.</li>
                <li>Pertanyaan wawancara dikembangkan berdasarkan unit kompetensi dan elemen kompetensi yang tercantum pada skema sertifikasi.</li>
                <li>Tuliskan ringkasan jawaban/tanggapan asesi pada kolom yang disediakan.</li>
                <li>Berikan keputusan pencapaian untuk setiap butir pertanyaan dengan memberi tanda centang (&radic;) pada kolom <strong>Memuaskan (M)</strong> atau <strong>Belum Memuaskan (BM)</strong>.</li>
            </ul>
        </div>

        <form id="form-ia-09" method="POST" action="{{ route('formulir.ia.simpan', ['kodeForm' => 'FR.IA.09', 'pendaftaranId' => $pendaftaran->id]) }}">
            @csrf

            <!-- DAFTAR PERTANYAAN WAWANCARA PER UNIT & ELEMEN SKEMA -->
            @php $globalNo = 1; @endphp
            @forelse($pendaftaran->skema->unitKompetensi as $uIdx => $unit)
                <div style="margin-bottom: 1.5rem; border: 1px solid #cbd5e1; border-radius: 6px; overflow: hidden;">
                    <div class="header-unit-pw">
                        Unit Kompetensi {{ $uIdx + 1 }}: {{ $unit->kode_unit }} - {{ $unit->judul_unit }}
                    </div>

                    <table class="tabel-bnsp tabel-pw" style="margin-bottom: 0; font-size: 0.85rem;">
                        <thead>
                            <tr>
                                <th style="width: 5%; text-align: center;">No.</th>
                                <th style="width: 25%;">Elemen Kompetensi</th>
                                <th style="width: 35%;">Daftar Pertanyaan Wawancara</th>
                                <th style="width: 23%;">Tanggapan / Jawaban Asesi</th>
                                <th colspan="2" style="width: 12%; text-align: center;">Keputusan</th>
                            </tr>
                            <tr style="font-size: 0.78rem; text-align: center; background: #f8fafc;">
                                <th></th>
                                <th></th>
                                <th></th>
                                <th></th>
                                <th style="width: 6%;">M</th>
                                <th style="width: 6%;">BM</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($unit->elemenKompetensi as $eIdx => $elem)
                                @php
                                    $rowKey = $elem->id;
                                    $savedRow = $savedPW[$rowKey] ?? [];

                                    $masterTanya = $masterPW[$elem->id]['pertanyaan'] ?? null;
                                    $defaultTanya = !empty($masterTanya) ? $masterTanya : ('Bagaimana Anda membuktikan penerapan standar dan prosedur kerja pada pelaksanaan ' . $elem->nama_elemen . ' sesuai kriteria unjuk kerja yang berlaku?');
                                    $tanyaVal = $savedRow['pertanyaan'] ?? $defaultTanya;
                                    $tanggapanVal = $savedRow['tanggapan'] ?? ($isAsesi ? 'Asesi memberikan tanggapan secara komprehensif dan sistematis sesuai SOP kejuruan.' : '');
                                    $keputusanVal = $savedRow['keputusan'] ?? ($isAsesi ? 'M' : 'M');
                                @endphp
                                <tr>
                                    <td style="text-align: center; font-weight: 700; vertical-align: top;">
                                        {{ $globalNo }}.
                                    </td>
                                    <td style="vertical-align: top;">
                                        <strong style="color: #0f172a;">Elemen {{ $elem->nomor_elemen }}:</strong><br>
                                        <span style="color: #334155;">{{ $elem->nama_elemen }}</span>
                                    </td>
                                    <td style="vertical-align: top;">
                                        <textarea name="pertanyaan_wawancara[{{ $rowKey }}][pertanyaan]" class="input-inline-bnsp" rows="3" placeholder="Pertanyaan wawancara klarifikasi..." {{ $isAsesi ? 'readonly' : '' }}>{{ $tanyaVal }}</textarea>
                                    </td>
                                    <td style="vertical-align: top;">
                                        <textarea name="pertanyaan_wawancara[{{ $rowKey }}][tanggapan]" class="input-inline-bnsp" rows="3" placeholder="{{ $isAsesi ? 'Tanggapan dicatat oleh asesor...' : 'Catat tanggapan / bukti lisan asesi di sini...' }}" {{ $isAsesi ? 'readonly' : '' }}>{{ $tanggapanVal }}</textarea>
                                    </td>
                                    <td style="text-align: center; vertical-align: middle;">
                                        <input type="radio" name="pertanyaan_wawancara[{{ $rowKey }}][keputusan]" value="M" {{ $keputusanVal === 'M' ? 'checked' : '' }} class="checkbox-bnsp checkbox-bnsp-hijau" {{ $isAsesi ? 'disabled' : '' }}>
                                    </td>
                                    <td style="text-align: center; vertical-align: middle;">
                                        <input type="radio" name="pertanyaan_wawancara[{{ $rowKey }}][keputusan]" value="BM" {{ $keputusanVal === 'BM' ? 'checked' : '' }} class="checkbox-bnsp checkbox-bnsp-merah" {{ $isAsesi ? 'disabled' : '' }}>
                                    </td>
                                </tr>
                                @php $globalNo++; @endphp
                            @empty
                                <tr>
                                    <td colspan="6" style="text-align: center; color: #64748b; padding: 0.75rem;">
                                        Unit kompetensi ini belum memiliki elemen kompetensi.
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            @empty
                <div style="padding: 1.5rem; text-align: center; color: #64748b; background: #f8fafc; border: 1px dashed #cbd5e1; border-radius: 6px; margin-bottom: 1.5rem;">
                    Skema sertifikasi ini belum memiliki daftar unit kompetensi di database.
                </div>
            @endforelse

            <!-- KESIMPULAN WAWANCARA -->
            <div style="border: 1px solid #334155; padding: 1rem; border-radius: 4px; margin-bottom: 1.5rem; background: #f8fafc;">
                <label style="font-weight: 700; font-size: 0.88rem; display: block; margin-bottom: 0.35rem; color: #0f172a;">
                    Kesimpulan & Catatan Klarifikasi Wawancara:
                </label>
                <textarea name="kesimpulan" class="input-inline-bnsp" rows="3" placeholder="Tuliskan catatan kesimpulan klarifikasi wawancara asesi..." {{ $isAsesi ? 'readonly' : '' }}>{{ $kesimpulanVal }}</textarea>
            </div>

            <!-- REKOMENDASI ASESOR -->
            <table class="tabel-bnsp" style="margin-bottom: 1.5rem;">
                <tr>
                    <td style="width: 25%; font-weight: 700; background: #f8fafc; vertical-align: middle;">Rekomendasi Asesor:</td>
                    <td>
                        <div style="margin-bottom: 0.75rem;">
                            <label style="display: flex; align-items: flex-start; gap: 0.5rem; cursor: pointer; font-weight: 600; color: #059669;">
                                <input type="radio" name="rekomendasi" value="K" {{ $rekomendasiVal === 'K' ? 'checked' : '' }} style="margin-top: 0.2rem; accent-color: #059669;" {{ $isAsesi ? 'disabled' : '' }}>
                                <span>Asesi telah memenuhi kriteria klarifikasi wawancara, direkomendasikan <strong>KOMPETEN</strong></span>
                            </label>
                        </div>
                        <div>
                            <label style="display: flex; align-items: flex-start; gap: 0.5rem; cursor: pointer; font-weight: 600; color: #dc2626;">
                                <input type="radio" name="rekomendasi" value="BK" {{ $rekomendasiVal === 'BK' ? 'checked' : '' }} style="margin-top: 0.2rem; accent-color: #dc2626;" {{ $isAsesi ? 'disabled' : '' }}>
                                <span>Asesi belum memenuhi kriteria wawancara, direkomendasikan <strong>BELUM KOMPETEN</strong></span>
                            </label>
                        </div>
                    </td>
                </tr>
            </table>

            @include('komponen.pengesahan-asesi-asesor', [
                'kodeForm' => 'FR.IA.09',
                'isAsesi' => $isAsesi ?? false,
                'pendaftaran' => $pendaftaran ?? null,
                'asesiNama' => $asesiNama ?? '-',
                'asesiTtd' => $asesiTtd ?? null,
                'tglTtdAsesi' => $tglTtdAsesi ?? null,
                'asesorNama' => $asesorNama ?? '-',
                'asesorMet' => $asesorMet ?? '-',
                'asesorTtd' => $asesorTtd ?? null,
                'tglAsesmen' => $tglAsesmen ?? null
            ])

        </form>

    </div>
</div>
@endsection