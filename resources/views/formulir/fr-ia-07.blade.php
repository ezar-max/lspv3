@extends('tata-letak.dasbor')

@section('judul', 'FR.IA.07 - Pertanyaan Lisan (DPL)')

@push('css')
    <link rel="stylesheet" href="{{ asset('css/formulir/formulir-bnsp.css') }}">
    <style>
        .lisan-card {
            background: #ffffff;
            border: 1px solid #e2e8f0;
            border-radius: 10px;
            padding: 1.5rem;
            margin-bottom: 1.5rem;
            box-shadow: 0 2px 4px rgba(0,0,0,0.03);
        }
        .lisan-kunci-box {
            background: #f0f9ff;
            border-left: 4px solid #0284c7;
            padding: 0.85rem 1.15rem;
            border-radius: 0 8px 8px 0;
            margin: 0.75rem 0;
            font-size: 0.88rem;
            color: #0369a1;
        }
        .lisan-live-notes {
            width: 100%;
            border: 1.5px solid #cbd5e1;
            border-radius: 8px;
            padding: 0.75rem 1rem;
            font-size: 0.92rem;
            line-height: 1.6;
            color: #1e293b;
            font-family: inherit;
            background: #ffffff;
            resize: vertical;
            min-height: 90px;
        }
        .lisan-live-notes:focus {
            outline: none;
            border-color: #2563eb;
            box-shadow: 0 0 0 3px rgba(37, 99, 235, 0.1);
        }
        .lisan-grading-bar {
            background: #f8fafc;
            border: 1px solid #e2e8f0;
            border-radius: 8px;
            padding: 0.75rem 1.25rem;
            display: flex;
            justify-content: space-between;
            align-items: center;
            flex-wrap: wrap;
            gap: 1rem;
            margin-top: 0.75rem;
        }
        .banner-sesi-lisan {
            background: linear-gradient(135deg, #1e3a8a 0%, #0284c7 100%);
            color: #ffffff;
            border-radius: 12px;
            padding: 1.25rem 1.5rem;
            margin-bottom: 1.5rem;
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 1rem;
            box-shadow: 0 4px 6px -1px rgba(0,0,0,0.1);
        }
    </style>
@endpush

@section('konten')
<div class="wadah-formulir-bnsp animasi-slide">
    
    @php
        $isAsesi = auth()->check() && auth()->user()->peran === 'asesi';
        $isAsesor = auth()->check() && auth()->user()->peran === 'asesor';
        $asesorNama = $pendaftaran->asesor->nama_lengkap ?? ($isAsesor ? auth()->user()->nama_lengkap : 'Asesor LSP');
        $asesorMet = $pendaftaran->asesor->nomor_registrasi ?? 'MET.000.004455.2023';
        $asesiNama = $pendaftaran->asesi->nama_lengkap ?? 'Nama Asesi';
        $asesorTtd = $pendaftaran->tanda_tangan_asesor ?? (auth()->user()->tanda_tangan ?? null);
        $asesiTtd = $iaRecord->data_jawaban['ttd_asesi'] ?? ($pendaftaran->tanda_tangan_asesi ?? null);
        $tglTtdAsesi = $iaRecord->data_jawaban['tgl_ttd_asesi'] ?? null;
        
        $isCompleted = ($iaRecord && $iaRecord->status === 'completed');
        $savedRespon = $iaRecord->data_jawaban['respon_lisan'] ?? [];
        $savedPencapaian = $iaRecord->data_jawaban['pencapaian'] ?? [];
        $rekomendasiVal = $iaRecord->rekomendasi ?? null;
    @endphp

    <!-- ACTION BAR ATAS -->
    @include('komponen.action-bar-formulir', [
        'kembaliRoute' => route('formulir.index', ['pendaftaran_id' => $pendaftaran->id]),
        'kodeForm' => 'FR.IA.07',
        'namaForm' => 'FR.IA.07 Pertanyaan Lisan',
        'pendaftaranId' => $pendaftaran->id,
        'canSubmit' => !$isAsesi,
        'submitFormId' => 'form-ia-07',
        'submitLabel' => 'Simpan & Selesaikan Uji Lisan',
        'canSignAsesi' => $isAsesi && $isCompleted && !$asesiTtd,
        'signAsesiRoute' => route('formulir.ia.simpan-ttd-asesi', ['kodeForm' => 'FR.IA.07', 'pendaftaranId' => $pendaftaran->id]),
        'isAsesiSigned' => $isAsesi && $asesiTtd
    ])

    <!-- BANNER STATUS UNTUK ASESI -->
    @if($isAsesi)
        @if(!$isCompleted)
            <div class="banner-readonly-wrap no-print" style="background: #f8fafc; border: 1px solid #cbd5e1; color: #334155; margin-bottom: 1.5rem;">
                <div>
                    <strong style="display: block; font-size: 0.95rem; margin-bottom: 0.25rem;">Sesi Uji Lisan Sedang Berlangsung Bersama Asesor</strong>
                    <span style="font-size: 0.85rem; color: #64748b;">
                        Asesor sedang memandu sesi tanya jawab lisan dan mencatat respons Anda secara langsung. Hasil pencapaian akan tampil setelah sesi diselesaikan oleh Asesor.
                    </span>
                </div>
            </div>
        @else
            <div class="banner-readonly-wrap no-print" style="background: #f0fdf4; border: 1px solid #86efac; color: #166534; margin-bottom: 1.5rem;">
                <div>
                    <strong style="display: block; font-size: 0.95rem; color: #14532d; margin-bottom: 0.25rem;">Sesi Uji Lisan Telah Selesai Dinilai oleh Asesor</strong>
                    <span style="font-size: 0.85rem; color: #15803d;">
                        Silakan periksa catatan respons lisan dan status pencapaian di bawah ini, kemudian lakukan tanda tangan verifikasi melalui tombol aksi di atas.
                    </span>
                </div>
            </div>
        @endif
    @endif

    <form id="form-ia-07" action="{{ route('formulir.ia.simpan', ['kodeForm' => 'FR.IA.07', 'pendaftaranId' => $pendaftaran->id]) }}" method="POST">
        @csrf

        <div class="dokumen-kertas">
            
            <!-- KOP RESMI DOKUMEN STANDAR BNSP -->
            @include('komponen.kop-formulir-bnsp', [
                'kodeForm' => 'FR.IA.07',
                'judulForm' => 'DPL – DAFTAR PERTANYAAN LISAN',
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
                    <td>Sewaktu / Tempat Kerja / Mandiri* (<strong>{{ $pendaftaran->tuk_type ?? 'Sewaktu' }}</strong>)</td>
                </tr>
                <tr>
                    <td colspan="2" style="font-weight: 600;">Nama Asesor</td>
                    <td>:</td>
                    <td><strong>{{ $asesorNama }}</strong> (No. Reg: {{ $asesorMet }})</td>
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

            <!-- PANDUAN PELAKSANAAN -->
            <div style="border: 1px solid #334155; padding: 0.85rem 1.25rem; background: #f8fafc; border-radius: 4px; margin-bottom: 1.5rem;">
                <strong style="display: block; font-size: 0.92rem; color: #0f172a; margin-bottom: 0.35rem; text-transform: uppercase;">
                    PANDUAN ASESMEN PERTANYAAN LISAN
                </strong>
                <ol style="margin: 0; padding-left: 1.2rem; font-size: 0.82rem; color: #334155; line-height: 1.6;">
                    <li>Asesor mengajukan butir pertanyaan dari daftar di bawah ini untuk mengonfirmasi pemahaman & aspek kritis.</li>
                    <li>Asesor mengetik rangkuman respons lisan asesi secara langsung (live notes) pada kolom yang disediakan.</li>
                    <li>Tentukan keputusan pencapaian: <strong>Memuaskan (M)</strong> atau <strong>Belum Memuaskan (BM)</strong>.</li>
                </ol>
            </div>

            <!-- DAFTAR BUTIR PERTANYAAN LISAN & LIVE NOTES -->
            <div style="margin-bottom: 1.5rem;">
                <div style="font-weight: 800; font-size: 0.95rem; color: #0f172a; margin-bottom: 1rem; text-transform: uppercase;">
                    DAFTAR BUTIR PERTANYAAN LISAN & PENCATATAN RESPONS
                </div>

                @foreach($soalList as $no => $item)
                    @php
                        $valRespon = $savedRespon[$no] ?? '';
                        $pencapaianVal = $savedPencapaian[$no] ?? null;
                    @endphp

                    <div class="lisan-card">
                        <div style="display: flex; justify-content: space-between; align-items: flex-start; gap: 0.75rem; margin-bottom: 0.65rem;">
                            <div style="display: flex; align-items: flex-start; gap: 0.6rem; flex: 1;">
                                <span style="background: #1e3a8a; color: #ffffff; width: 26px; height: 26px; border-radius: 6px; display: inline-flex; align-items: center; justify-content: center; font-size: 0.85rem; flex-shrink: 0;">
                                    {{ $no }}
                                </span>
                                <div>
                                    <div style="font-size: 1.02rem; font-weight: 700; color: #0f172a; line-height: 1.5;">
                                        {{ $item['pertanyaan'] }}
                                    </div>
                                    <span style="font-size: 0.75rem; font-weight: 700; color: #0284c7; text-transform: uppercase;">
                                        KUK: {{ $item['kuk'] }}
                                    </span>
                                </div>
                            </div>
                        </div>

                        <!-- KUNCI JAWABAN RUJUKAN (HANYA DITAMPILKAN KE ASESOR / SETELAH EVALUASI) -->
                        @if(!$isAsesi || $isCompleted)
                            <div class="lisan-kunci-box">
                                <strong style="display: block; font-size: 0.82rem; margin-bottom: 0.2rem;">
                                    Kunci Jawaban Rujukan Asesor:
                                </strong>
                                <div>{{ $item['kunci_rujukan'] }}</div>
                            </div>
                        @endif

                        <!-- KOLOM LIVE NOTES RESPONS ASESI -->
                        <div style="margin-top: 0.75rem;">
                            <label style="display: block; font-weight: 700; font-size: 0.85rem; color: #0f172a; margin-bottom: 0.35rem;">
                                Tanggapan / Respons Lisan Asesi (Live Notes):
                            </label>
                            <textarea 
                                name="respon_lisan[{{ $no }}]" 
                                class="lisan-live-notes" 
                                rows="2" 
                                placeholder="{{ $isAsesi ? 'Respons lisan dicatat langsung oleh asesor saat wawancara...' : 'Ketik rangkuman jawaban lisan yang diucapkan asesi saat wawancara...' }}"
                                {{ $isAsesi ? 'readonly' : '' }}
                            >{{ $valRespon }}</textarea>
                        </div>

                        <!-- PENILAIAN PENCAPAIAN BUTIR -->
                        <div class="lisan-grading-bar">
                            <div style="display: flex; align-items: center; gap: 1.25rem;">
                                <span style="font-weight: 700; font-size: 0.88rem; color: #0f172a;">Pencapaian:</span>
                                <label style="display: inline-flex; align-items: center; gap: 0.4rem; cursor: pointer; font-size: 0.88rem; font-weight: 700; color: #166534;">
                                    <input type="radio" name="pencapaian[{{ $no }}]" value="ya" {{ $pencapaianVal === 'ya' ? 'checked' : '' }} {{ $isAsesi ? 'disabled' : '' }} style="accent-color: #16a34a; width: 16px; height: 16px;">
                                    Memuaskan (M)
                                </label>
                                <label style="display: inline-flex; align-items: center; gap: 0.4rem; cursor: pointer; font-size: 0.88rem; font-weight: 700; color: #991b1b;">
                                    <input type="radio" name="pencapaian[{{ $no }}]" value="tidak" {{ $pencapaianVal === 'tidak' ? 'checked' : '' }} {{ $isAsesi ? 'disabled' : '' }} style="accent-color: #dc2626; width: 16px; height: 16px;">
                                    Belum Memuaskan (BM)
                                </label>
                            </div>
                            @if($isCompleted)
                                <span class="lencana {{ $pencapaianVal === 'ya' ? 'lencana-hijau' : 'lencana-merah' }}" style="font-size: 0.78rem;">
                                    {{ $pencapaianVal === 'ya' ? 'Kompeten' : 'Belum Kompeten' }}
                                </span>
                            @endif
                        </div>

                    </div>
                @endforeach
            </div>

            <!-- UMPAN BALIK & KEPUTUSAN ASESOR -->
            <table class="tabel-bnsp" style="margin-bottom: 1.5rem;">
                <tr>
                    <td style="width: 25%; font-weight: 700; background: #f8fafc;">Rekomendasi Uji Lisan</td>
                    <td style="width: 2%;">:</td>
                    <td>
                        <div style="display: flex; gap: 1.5rem; align-items: center;">
                            <label style="display: inline-flex; align-items: center; gap: 0.4rem; cursor: pointer; font-weight: 700; color: #166534;">
                                <input type="radio" name="rekomendasi" value="K" {{ $rekomendasiVal === 'K' ? 'checked' : '' }} {{ $isAsesi ? 'disabled' : '' }} style="accent-color: #16a34a; width: 16px; height: 16px;">
                                Kompeten (K) – Memenuhi Syarat
                            </label>
                            <label style="display: inline-flex; align-items: center; gap: 0.4rem; cursor: pointer; font-weight: 700; color: #991b1b;">
                                <input type="radio" name="rekomendasi" value="BK" {{ $rekomendasiVal === 'BK' ? 'checked' : '' }} {{ $isAsesi ? 'disabled' : '' }} style="accent-color: #dc2626; width: 16px; height: 16px;">
                                Belum Kompeten (BK)
                            </label>
                        </div>
                    </td>
                </tr>
                <tr>
                    <td style="font-weight: 700; background: #f8fafc;">Umpan Balik Asesor</td>
                    <td>:</td>
                    <td>
                        <textarea name="catatan" class="input-inline-bnsp" rows="2" placeholder="Tuliskan umpan balik / catatan pelaksanaan uji lisan..." {{ $isAsesi ? 'readonly' : '' }}>{{ $iaRecord->catatan_asesor ?? 'Asesi mampu menjawab dan mengklarifikasi pertanyaan teknis lisan secara jelas dan runtut.' }}</textarea>
                    </td>
                </tr>
            </table>

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

            @include('komponen.navigasi-form-bawah', [
                'pendaftaranId' => $pendaftaran->id,
                'prevForm' => ['route' => route('formulir.ia.index', ['kodeForm' => 'FR.IA.06C', 'pendaftaranId' => $pendaftaran->id]), 'label' => 'FR.IA.06.C Ujian Esai'],
                'nextForm' => ['route' => route('formulir.ia.index', ['kodeForm' => 'FR.IA.08', 'pendaftaranId' => $pendaftaran->id]), 'label' => 'FR.IA.08 Portofolio']
            ])

        </div>
    </form>
</div>
@endsection

@push('js')
    <script src="{{ asset('js/formulir/formulir-bnsp.js') }}"></script>
@endpush


