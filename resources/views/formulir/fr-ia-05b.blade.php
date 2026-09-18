@extends('tata-letak.dasbor')

@section('judul', 'FR.IA.05B - Kunci Jawaban & Evaluasi Hasil Ujian PG')

@push('css')
    <link rel="stylesheet" href="{{ asset('css/formulir/formulir-bnsp.css') }}">
    <style>
        .skor-summary-card {
            background: linear-gradient(135deg, #065f46 0%, #047857 100%);
            color: #ffffff;
            border-radius: 12px;
            padding: 1.5rem 1.75rem;
            margin-bottom: 1.5rem;
            box-shadow: 0 4px 6px -1px rgba(6, 95, 70, 0.2);
            display: flex;
            justify-content: space-between;
            align-items: center;
            flex-wrap: wrap;
            gap: 1rem;
        }
        .komparasi-table tbody tr:hover {
            background-color: #f8fafc;
        }
    </style>
@endpush

@section('konten')
<div class="wadah-formulir-bnsp animasi-slide">
    
    @php
        $isAsesi = auth()->check() && auth()->user()->peran === 'asesi';
        $asesorNama = $pendaftaran->asesor->nama_lengkap ?? (auth()->user()->peran === 'asesor' ? auth()->user()->nama_lengkap : 'Asesor LSP');
        $asesorMet = $pendaftaran->asesor->nomor_registrasi ?? 'MET.000.004455.2023';
        $asesiNama = $pendaftaran->asesi->nama_lengkap ?? 'Nama Asesi';
        $asesorTtd = $pendaftaran->tanda_tangan_asesor ?? (auth()->user()->tanda_tangan ?? null);
        
        $ia05cRecord = $iaRecord05c ?? null;
        $skorData = $ia05cRecord->data_jawaban ?? ($iaRecord05b->data_jawaban ?? []);
        $jawabanAsesi = $skorData['jawaban'] ?? [];
        $totalSoal = count($soalList);

        $hitungBenar = 0;
        $hitungSalah = 0;
        foreach ($soalList as $no => $item) {
            $kunci = strtoupper(trim($item['kunci'] ?? ''));
            $ans = isset($jawabanAsesi[$no]) ? strtoupper(trim($jawabanAsesi[$no])) : null;
            if ($ans !== null && $ans !== '' && $ans === $kunci) {
                $hitungBenar++;
            } else {
                $hitungSalah++;
            }
        }

        if (!empty($jawabanAsesi) && $totalSoal > 0) {
            $totalBenar = $hitungBenar;
            $totalSalah = $hitungSalah;
            $skorNilai = (int) round(($totalBenar / $totalSoal) * 100);
        } else {
            $skorNilai = $skorData['skor'] ?? null;
            $totalBenar = $skorData['benar'] ?? 0;
            $totalSalah = $skorData['salah'] ?? 0;
        }

        $rekomendasiVal = $skorNilai !== null ? ($skorNilai >= 70 ? 'K' : 'BK') : ($iaRecord05b->rekomendasi ?? ($ia05cRecord->rekomendasi ?? null));
    @endphp

    <!-- ACTION BAR ATAS -->
    @include('komponen.action-bar-formulir', [
        'kodeForm' => 'FR.IA.05B',
        'namaForm' => 'Kunci Jawaban & Evaluasi Pilihan Ganda',
        'pendaftaranId' => $pendaftaran->id,
        'isAsesi' => false,
        'saveLabel' => 'Simpan Formulir',
        'saveFormId' => 'form-ia05b-evaluasi'
    ])

    <!-- PANEL RINGKASAN SKOR OTOMATIS -->
    @if($skorNilai !== null)
        <div class="skor-summary-card">
            <div>
                <div style="font-size: 0.85rem; text-transform: uppercase; letter-spacing: 0.5px; opacity: 0.9;">
                    Ringkasan Hasil Uji Tertulis PG (Auto-Grading)
                </div>
                <div style="font-size: 2.25rem; font-weight: 900; margin: 0.2rem 0;">
                    Skor: {{ $skorNilai }} / 100
                </div>
                <div style="font-size: 0.92rem; opacity: 0.95;">
                    Status: <strong>{{ $rekomendasiVal === 'K' ? 'Kompeten – Memenuhi Syarat Kelulusan Pengetahuan' : 'Belum Kompeten – Perlu Tindak Lanjut' }}</strong>
                </div>
            </div>
            <div style="display: flex; gap: 1rem; flex-wrap: wrap;">
                <div style="background: rgba(255,255,255,0.2); padding: 0.6rem 1.2rem; border-radius: 8px; text-align: center;">
                    <div style="font-size: 1.4rem; font-weight: 800;">{{ $totalBenar }}</div>
                    <div style="font-size: 0.78rem;">Soal Benar</div>
                </div>
                <div style="background: rgba(255,255,255,0.2); padding: 0.6rem 1.2rem; border-radius: 8px; text-align: center;">
                    <div style="font-size: 1.4rem; font-weight: 800;">{{ $totalSalah }}</div>
                    <div style="font-size: 0.78rem;">Soal Salah</div>
                </div>
                <div style="background: rgba(255,255,255,0.2); padding: 0.6rem 1.2rem; border-radius: 8px; text-align: center;">
                    <div style="font-size: 1.4rem; font-weight: 800;">{{ $totalSoal }}</div>
                    <div style="font-size: 0.78rem;">Total Butir</div>
                </div>
            </div>
        </div>
    @else
        <div style="background: #fef3c7; border: 1.5px solid #f59e0b; border-radius: 10px; padding: 1rem 1.25rem; margin-bottom: 1.5rem; color: #92400e; display: flex; align-items: center; justify-content: space-between;">
            <div>
                <strong>Asesi Belum Menyelesaikan Ujian CBT (FR.IA.05C)</strong>
                <div style="font-size: 0.82rem;">Tabel di bawah menampilkan kunci jawaban standar. Hasil perbandingan akan otomatis terisi setelah asesi mengumpulkan ujian.</div>
            </div>
            <a href="{{ route('formulir.ia05c', $pendaftaran->id) }}" class="tombol tombol-sekunder tombol-sm" style="background: #ffffff; color: #b45309; border-color: #f59e0b;">
                Buka Lembar Ujian FR.IA.05C
            </a>
        </div>
    @endif

    <form id="form-ia05b-evaluasi" action="{{ route('formulir.ia.simpan', ['kodeForm' => 'FR.IA.05B', 'pendaftaranId' => $pendaftaran->id]) }}" method="POST">
        @csrf

        <div class="dokumen-kertas">
            
            <!-- KOP RESMI DOKUMEN STANDAR BNSP -->
            @include('komponen.kop-formulir-bnsp', [
                'kodeForm' => 'FR.IA.05B',
                'judulForm' => 'KUNCI JAWABAN & LEMBAR EVALUASI PILIHAN GANDA (DPT)',
                'tipeDokumen' => 'Evaluasi Asesor'
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
                    <td colspan="2" style="font-weight: 600;">Nama Asesi (Peserta)</td>
                    <td>:</td>
                    <td><strong style="color: #0f172a;">{{ $asesiNama }}</strong></td>
                </tr>
                <tr>
                    <td colspan="2" style="font-weight: 600;">Nama Asesor</td>
                    <td>:</td>
                    <td><strong>{{ $asesorNama }}</strong> (No. Reg: {{ $asesorMet }})</td>
                </tr>
                <tr>
                    <td colspan="2" style="font-weight: 600;">Tanggal Evaluasi</td>
                    <td>:</td>
                    <td>{{ date('d F Y') }}</td>
                </tr>
            </table>

            <!-- TABEL KOMPARASI JAWABAN ASESI VS KUNCI JAWABAN -->
            <div style="font-weight: 800; font-size: 0.95rem; color: #0f172a; margin: 1.25rem 0 0.6rem 0; text-transform: uppercase;">
                TABEL PERBANDINGAN BUTIR SOAL: KUNCI ASLI VS JAWABAN ASESI
            </div>
            
            <table class="tabel-bnsp komparasi-table" style="font-size: 0.85rem;">
                <thead>
                    <tr>
                        <th style="width: 5%; text-align: center;">No.</th>
                        <th style="width: 40%;">Pertanyaan & Unit / KUK Terkait</th>
                        <th style="width: 10%; text-align: center;">Kunci Resmi</th>
                        <th style="width: 12%; text-align: center;">Pilihan Asesi</th>
                        <th style="width: 10%; text-align: center;">Hasil</th>
                        <th>Penjelasan / Pembahasan Standar</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($soalList as $no => $item)
                        @php
                            $kunciResmi = strtoupper(trim($item['kunci'] ?? ''));
                            $rawJawaban = isset($jawabanAsesi[$no]) ? trim($jawabanAsesi[$no]) : null;
                            $pilihanAsesi = ($rawJawaban !== null && $rawJawaban !== '') ? strtoupper($rawJawaban) : null;
                            $isMatch = ($pilihanAsesi !== null && $pilihanAsesi === $kunciResmi);
                        @endphp
                        <tr>
                            <td style="text-align: center; font-weight: 700; vertical-align: middle;">{{ $no }}.</td>
                            <td>
                                <strong style="color: #0f172a; display: block; margin-bottom: 0.2rem;">{{ $item['pertanyaan'] }}</strong>
                                <span style="font-size: 0.75rem; color: #0284c7; font-weight: 700;">{{ $item['kuk'] }}</span>
                            </td>
                            <td style="text-align: center; vertical-align: middle; font-weight: 800; font-size: 1.1rem; color: #059669; background: #ecfdf5;">
                                {{ $kunciResmi ?: '-' }}
                            </td>
                            <td style="text-align: center; vertical-align: middle; font-weight: 800; font-size: 1.1rem; color: {{ $pilihanAsesi === null ? '#64748b' : ($isMatch ? '#059669' : '#dc2626') }}; background: {{ $pilihanAsesi === null ? 'transparent' : ($isMatch ? '#ecfdf5' : '#fef2f2') }};">
                                {{ $pilihanAsesi ?? '-' }}
                            </td>
                            <td style="text-align: center; vertical-align: middle;">
                                @if($pilihanAsesi === null)
                                    <span class="lencana lencana-abu" style="font-size: 0.72rem;">Belum Diisi</span>
                                @elseif($isMatch)
                                    <span class="lencana lencana-hijau" style="font-size: 0.75rem; padding: 0.3rem 0.6rem; font-weight: 800;">Benar</span>
                                @else
                                    <span class="lencana lencana-merah" style="font-size: 0.75rem; padding: 0.3rem 0.6rem; font-weight: 800;">Salah</span>
                                @endif
                            </td>
                            <td style="color: #475569; font-size: 0.8rem; vertical-align: middle;">
                                {{ $item['pembahasan'] ?? '-' }}
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>

            <!-- REKOMENDASI DAN CATATAN EVALUASI ASESOR -->
            <div style="margin-top: 1.5rem;">
                <table class="tabel-bnsp">
                    <tr>
                        <td style="width: 25%; font-weight: 700; background: #f8fafc;">Rekomendasi Hasil Pengetahuan</td>
                        <td style="width: 2%;">:</td>
                        <td>
                            <div style="display: flex; gap: 1.5rem; align-items: center;">
                                <label style="display: inline-flex; align-items: center; gap: 0.4rem; cursor: pointer; font-weight: 700; color: #166534;">
                                    <input type="radio" name="rekomendasi" value="K" {{ $rekomendasiVal === 'K' ? 'checked' : '' }} style="accent-color: #16a34a; width: 16px; height: 16px;">
                                    Kompeten (K) – Memenuhi Syarat
                                </label>
                                <label style="display: inline-flex; align-items: center; gap: 0.4rem; cursor: pointer; font-weight: 700; color: #991b1b;">
                                    <input type="radio" name="rekomendasi" value="BK" {{ $rekomendasiVal === 'BK' ? 'checked' : '' }} style="accent-color: #dc2626; width: 16px; height: 16px;">
                                    Belum Kompeten (BK) – Perlu Remidi
                                </label>
                            </div>
                        </td>
                    </tr>
                    <tr>
                        <td style="font-weight: 700; background: #f8fafc;">Catatan Evaluasi Asesor</td>
                        <td>:</td>
                        <td>
                            <textarea name="catatan" class="input-inline-bnsp" rows="2" placeholder="Tuliskan catatan evaluasi pencapaian pengetahuan asesi...">{{ $iaRecord05b->catatan_asesor ?? ($ia05cRecord->catatan_asesor ?? 'Asesi menunjukkan penguasaan teori pengetahuan yang baik sesuai standar kompetensi.') }}</textarea>
                        </td>
                    </tr>
                </table>
            </div>

            <!-- PENGESAHAN ASESOR -->
            <div style="border: 1px solid #334155; padding: 1.25rem; border-radius: 4px; background: #f8fafc; display: flex; justify-content: space-between; align-items: center; margin-top: 1.5rem;">
                <div>
                    <strong style="display: block; font-size: 0.95rem; color: #0f172a;">Asesor Kompetensi:</strong>
                    <div style="height: 50px; display: flex; align-items: center; margin-top: 0.25rem;">
                        @if($asesorTtd)
                            <img src="{{ $asesorTtd }}" alt="TTD Asesor" style="max-height: 40px;">
                        @else
                            <span style="font-style: italic; color: #64748b; font-size: 0.85rem;">(Tanda Tangan Digital Asesor)</span>
                        @endif
                    </div>
                    <div style="font-weight: 700; color: #0f172a;">{{ $asesorNama }}</div>
                    <div style="font-size: 0.78rem; color: #64748b;">No. Reg: {{ $asesorMet }}</div>
                </div>
                <div>
                    <strong style="display: block; font-size: 0.95rem; color: #0f172a;">Tanggal Pengesahan:</strong>
                    <div style="font-weight: 700; color: #0f172a; font-size: 1rem; margin-top: 0.5rem;">{{ date('d F Y') }}</div>
                </div>
            </div>

            @include('komponen.navigasi-form-bawah', [
                'prevUrl' => route('formulir.ia05a', $pendaftaran->id),
                'prevLabel' => 'FR.IA.05A (Soal PG)',
                'nextUrl' => route('formulir.ia05c', $pendaftaran->id),
                'nextLabel' => 'FR.IA.05C (Lembar Ujian PG)'
            ])

        </div>
    </form>
</div>
@endsection

@push('js')
    <script src="{{ asset('js/formulir/formulir-bnsp.js') }}"></script>
@endpush
