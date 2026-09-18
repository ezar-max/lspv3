@extends('tata-letak.dasbor')

@section('judul', 'FR.IA.06C - Lembar Jawaban & Penilaian Ujian Esai')

@push('css')
    <link rel="stylesheet" href="{{ asset('css/formulir/formulir-bnsp.css') }}">
    <style>
        .essay-card {
            background: #ffffff;
            border: 1px solid #e2e8f0;
            border-radius: 10px;
            padding: 1.5rem;
            margin-bottom: 1.5rem;
            box-shadow: 0 2px 4px rgba(0,0,0,0.03);
        }
        .essay-soal-header {
            font-size: 1.02rem;
            font-weight: 700;
            color: #0f172a;
            margin-bottom: 0.75rem;
            display: flex;
            align-items: flex-start;
            gap: 0.6rem;
        }
        .essay-textarea {
            width: 100%;
            border: 1.5px solid #cbd5e1;
            border-radius: 8px;
            padding: 0.85rem 1rem;
            font-size: 0.95rem;
            line-height: 1.6;
            color: #1e293b;
            font-family: inherit;
            resize: vertical;
            min-height: 120px;
            transition: border-color 0.15s ease;
        }
        .essay-textarea:focus {
            outline: none;
            border-color: #2563eb;
            box-shadow: 0 0 0 3px rgba(37, 99, 235, 0.1);
        }
        .rubrik-box {
            background: #f8fafc;
            border-left: 4px solid #0284c7;
            padding: 0.85rem 1.15rem;
            border-radius: 0 8px 8px 0;
            margin-top: 0.75rem;
            font-size: 0.88rem;
            color: #334155;
        }
        .grading-panel-item {
            background: #f1f5f9;
            border-radius: 8px;
            padding: 0.75rem 1rem;
            margin-top: 0.75rem;
            display: flex;
            justify-content: space-between;
            align-items: center;
            flex-wrap: wrap;
            gap: 0.75rem;
        }
        .auto-save-indicator {
            font-size: 0.78rem;
            color: #16a34a;
            display: inline-flex;
            align-items: center;
            gap: 0.35rem;
            font-weight: 600;
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
        
        $statusUjian = $iaRecord->status ?? 'draft'; // draft, submitted, completed
        $isSubmitted = in_array($statusUjian, ['submitted', 'completed', 'evaluated']);
        $isEvaluated = ($statusUjian === 'completed' || $statusUjian === 'evaluated');
        
        $savedJawaban = $iaRecord->data_jawaban['jawaban_esai'] ?? [];
        $savedPencapaian = $iaRecord->data_jawaban['pencapaian'] ?? [];
        $savedCatatanSoal = $iaRecord->data_jawaban['catatan_per_soal'] ?? [];
        $rekomendasiVal = $iaRecord->rekomendasi ?? null;
    @endphp

    <!-- ACTION BAR ATAS -->
    <div class="action-bar-formulir no-print">
        <div class="action-bar-kiri">
            @if($isAsesi && !$isSubmitted && count($soalList) > 0)
                <span class="lencana lencana-amber">
                    Mode Ujian Aktif (Sesi Pengerjaan Esai)
                </span>
            @else
                <a href="{{ route('formulir.index', ['pendaftaran_id' => $pendaftaran->id]) }}" class="tombol tombol-sekunder tombol-sm">
                    Kembali ke Daftar Formulir
                </a>
            @endif
            <span class="lencana lencana-hijau">
                FR.IA.06C Ujian Esai
            </span>
            @if($isAsesi && !$isSubmitted && count($soalList) > 0)
                <span class="auto-save-indicator" id="draft-status-indicator" style="font-size: 0.78rem; font-weight: 600; color: #0284c7;">
                    Draft Auto-Save Aktif
                </span>
            @endif
        </div>
        <div class="action-bar-kanan">
            <button type="button" onclick="window.print()" class="tombol tombol-sekunder tombol-sm btn-cetak-formulir">
                Cetak Dokumen
            </button>
            @if($isAsesi)
                @if(!$isSubmitted)
                    @if(count($soalList) > 0)
                        <button type="button" onclick="konfirmasiKirimEsai()" class="tombol tombol-utama tombol-sm" style="background: #16a34a; border-color: #16a34a; font-weight: 700;">
                            Kirim Jawaban Esai
                        </button>
                    @endif
                @else
                    <span class="lencana lencana-biru">
                        {{ $isEvaluated ? 'Selesai Dinilai Asesor' : 'Menunggu Penilaian Asesor' }}
                    </span>
                @endif
            @else
                @if(count($soalList) > 0)
                    <button type="submit" form="form-ia-06c" class="tombol tombol-utama tombol-sm">
                        Simpan Formulir
                    </button>
                @endif
            @endif
        </div>
    </div>

    @if($isAsesi)
        @if(!$isSubmitted)
            @include('komponen.banner-readonly-asesi', [
                'judul' => 'Lembar Pengerjaan Ujian Esai Mandiri (Asesi)',
                'keterangan' => 'Ketikkan uraian jawaban Anda secara komprehensif. Jawaban otomatis tersimpan ke draft lokal. Klik Kirim Jawaban Esai setelah selesai.',
                'status' => 'Mode Pengerjaan',
                'tipe' => 'sukses'
            ])
        @else
            @include('komponen.banner-readonly-asesi', [
                'judul' => 'Jawaban Telah Dikirim & Terkunci (Hanya Baca)',
                'keterangan' => $isEvaluated ? 'Asesor telah menilai jawaban esai Anda. Silakan lihat hasil evaluasi pada masing-masing butir soal di bawah.' : 'Jawaban esai Anda telah diterima sistem dan sedang dalam antrean koreksi oleh Asesor Penguji.',
                'status' => $isEvaluated ? 'Telah Dinilai' : 'Menunggu Koreksi',
                'tipe' => 'info'
            ])
        @endif
    @else
        @include('komponen.banner-readonly-asesi', [
            'judul' => 'Mode Penilaian & Koreksi Asesor (FR.IA.06C)',
            'keterangan' => 'Uraian jawaban diisi oleh asesi (' . $asesiNama . '). Cocokkan dengan kunci jawaban acuan (FR.IA.06B) dan tentukan status pencapaian beserta catatan umpan balik.',
            'status' => 'Penilaian Asesor',
            'tipe' => 'info'
        ])
    @endif

    <div class="dokumen-kertas">
        
        <!-- KOP RESMI DOKUMEN STANDAR BNSP -->
        @include('komponen.kop-formulir-bnsp', [
            'kodeForm' => 'FR.IA.06C',
            'judulForm' => 'DPT – LEMBAR JAWABAN TERTULIS ESAI',
            'tipeDokumen' => 'Lembar Jawaban Esai'
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
            <tr>
                <td colspan="2" style="font-weight: 600;">Waktu</td>
                <td>:</td>
                <td>45 Menit</td>
            </tr>
        </table>
        <div style="font-size: 0.75rem; font-style: italic; color: #64748b; margin-top: -1rem; margin-bottom: 1.25rem;">*Coret yang tidak perlu</div>

        @if(count($soalList) === 0)
            <!-- KONDISI KOSONG: JIKA SKEMA INI BELUM MEMILIKI DATA SOAL ESAI -->
            <div style="background: #ffffff; border: 1.5px solid #e2e8f0; border-radius: 16px; padding: 3.5rem 2rem; text-align: center; margin: 1.5rem auto; max-width: 680px; box-shadow: 0 4px 15px rgba(0,0,0,0.04);">
                <h3 style="font-size: 1.35rem; font-weight: 800; color: #0f172a; margin: 0 0 0.5rem 0;">Bank Soal Esai Belum Tersedia</h3>
                <p style="font-size: 0.92rem; color: #64748b; line-height: 1.6; margin: 0 0 1.5rem 0;">
                    Perangkat butir soal esai untuk skema <strong>{{ $pendaftaran->skema->nama_skema ?? 'Skema Terpilih' }}</strong> ({{ $pendaftaran->skema->kode_skema ?? '-' }}) belum diunggah atau belum diterbitkan oleh Tim Asesor / LSP. Lembar ujian belum dapat diisi saat ini.
                </p>
                <a href="{{ route('formulir.index', ['pendaftaran_id' => $pendaftaran->id]) }}" class="tombol tombol-utama" style="background: #2563eb; border-color: #2563eb; padding: 0.65rem 1.5rem; font-weight: 700;">
                    Kembali ke Daftar Formulir
                </a>
            </div>
        @else
            <!-- FORM UTAMA -->
            <form id="form-ia-06c" action="{{ route('formulir.ia.simpan', ['kodeForm' => 'FR.IA.06C', 'pendaftaranId' => $pendaftaran->id]) }}" method="POST">
                @csrf

                <!-- DAFTAR SOAL & LEMBAR JAWABAN ESAI -->
                <div style="margin-top: 1.5rem;">
                    <div style="font-weight: 800; font-size: 0.95rem; color: #0f172a; margin-bottom: 1rem; text-transform: uppercase;">
                        LEMBAR KERJA & EVALUASI ESAI
                    </div>

                @foreach($soalList as $no => $item)
                    @php
                        $valJawaban = $savedJawaban[$no] ?? '';
                        $pencapaianVal = $savedPencapaian[$no] ?? null;
                        $catatanItem = $savedCatatanSoal[$no] ?? '';
                    @endphp

                    <div class="essay-card" id="essay-card-{{ $no }}">
                        <div class="essay-soal-header">
                            <span style="background: #1e3a8a; color: #ffffff; width: 26px; height: 26px; border-radius: 6px; display: inline-flex; align-items: center; justify-content: center; font-size: 0.85rem; flex-shrink: 0;">
                                {{ $no }}
                            </span>
                            <div style="flex: 1;">
                                <div>{{ $item['pertanyaan'] }}</div>
                                <span style="font-size: 0.75rem; font-weight: 700; color: #0284c7; text-transform: uppercase;">
                                    KUK: {{ $item['kuk'] }}
                                </span>
                            </div>
                        </div>

                        <!-- INPUT / TAMPILAN JAWABAN ASESI -->
                        <div style="margin-top: 0.85rem;">
                            <label style="display: block; font-weight: 700; font-size: 0.85rem; color: #0f172a; margin-bottom: 0.35rem;">
                                Jawaban Asesi:
                            </label>
                            <textarea 
                                name="jawaban_esai[{{ $no }}]" 
                                id="jawaban_esai_{{ $no }}" 
                                class="essay-textarea" 
                                rows="3" 
                                placeholder="{{ $isAsesi ? 'Ketikkan uraian jawaban teknis Anda di sini...' : ($valJawaban ? '' : '(Asesi belum mengisi jawaban)') }}" 
                                {{ (!$isAsesi || $isSubmitted) ? 'readonly' : '' }}
                                {{ $isAsesi ? "oninput=\"onEssayInput($no)\"" : '' }}
                            >{{ $valJawaban }}</textarea>
                            
                            @if($isAsesi && !$isSubmitted)
                                <div style="text-align: right; font-size: 0.75rem; color: #64748b; margin-top: 0.25rem;">
                                    <span style="font-style: italic;">Otomatis tersimpan saat mengetik</span>
                                </div>
                            @endif
                        </div>

                        <!-- PANEL KUNCI / RUBRIK ACUAN & PENILAIAN ASESOR -->
                        @if(!$isAsesi || $isEvaluated)
                            <!-- RUBRIK JAWABAN STANDAR (IA.06.B) -->
                            <div class="rubrik-box">
                                <strong style="color: #0369a1; display: block; margin-bottom: 0.2rem;">
                                    Kunci Jawaban Acuan / Rubrik Penilaian (FR.IA.06.B):
                                </strong>
                                <div>{{ $item['kunci_referensi'] }}</div>
                            </div>

                            <!-- GRADING CONTROLS UNTUK ASESOR -->
                            <div class="grading-panel-item">
                                <div style="display: flex; align-items: center; gap: 1rem;">
                                    <span style="font-weight: 700; font-size: 0.85rem; color: #0f172a;">Pencapaian:</span>
                                    <label style="display: inline-flex; align-items: center; gap: 0.4rem; cursor: pointer; font-size: 0.88rem; font-weight: 700; color: #166534;">
                                        <input type="radio" name="pencapaian[{{ $no }}]" value="ya" {{ $pencapaianVal === 'ya' ? 'checked' : '' }} {{ $isAsesi ? 'disabled' : '' }} style="accent-color: #16a34a; width: 16px; height: 16px;">
                                        Memuaskan (M)
                                    </label>
                                    <label style="display: inline-flex; align-items: center; gap: 0.4rem; cursor: pointer; font-size: 0.88rem; font-weight: 700; color: #991b1b;">
                                        <input type="radio" name="pencapaian[{{ $no }}]" value="tidak" {{ $pencapaianVal === 'tidak' ? 'checked' : '' }} {{ $isAsesi ? 'disabled' : '' }} style="accent-color: #dc2626; width: 16px; height: 16px;">
                                        Belum Memuaskan (BM)
                                    </label>
                                </div>
                                <div style="flex: 1; min-width: 250px;">
                                    <input type="text" name="catatan_per_soal[{{ $no }}]" class="input-inline-bnsp" placeholder="Catatan / umpan balik asesor untuk nomor ini..." value="{{ $catatanItem }}" {{ $isAsesi ? 'readonly' : '' }}>
                                </div>
                            </div>
                        @endif

                    </div>
                @endforeach
            </div>

            <!-- UMPAN BALIK & REKOMENDASI UMUM ASESOR (HANYA DITAMPILKAN UNTUK ASESOR) -->
            @if(!$isAsesi)
                <table class="tabel-bnsp" style="margin-top: 1.5rem; margin-bottom: 1.5rem;">
                    <tr>
                        <td style="width: 25%; font-weight: 700; background: #f8fafc;">Rekomendasi Hasil Esai</td>
                        <td style="width: 2%;">:</td>
                        <td>
                            <div style="display: flex; gap: 1.5rem; align-items: center;">
                                <label style="display: inline-flex; align-items: center; gap: 0.4rem; cursor: pointer; font-weight: 700; color: #166534;">
                                    <input type="radio" name="rekomendasi" value="K" {{ $rekomendasiVal === 'K' ? 'checked' : '' }} style="accent-color: #16a34a; width: 16px; height: 16px;">
                                    Kompeten (K) – Memenuhi Syarat
                                </label>
                                <label style="display: inline-flex; align-items: center; gap: 0.4rem; cursor: pointer; font-weight: 700; color: #991b1b;">
                                    <input type="radio" name="rekomendasi" value="BK" {{ $rekomendasiVal === 'BK' ? 'checked' : '' }} style="accent-color: #dc2626; width: 16px; height: 16px;">
                                    Belum Kompeten (BK)
                                </label>
                            </div>
                        </td>
                    </tr>
                    <tr>
                        <td style="font-weight: 700; background: #f8fafc;">Umpan Balik / Catatan Umum</td>
                        <td>:</td>
                        <td>
                            <textarea name="catatan" class="input-inline-bnsp" rows="2" placeholder="Catatan evaluasi menyeluruh untuk asesi...">{{ $iaRecord->catatan_asesor ?? 'Asesi mampu menguraikan konsep teknis dan metodologi pemecahan masalah dengan baik.' }}</textarea>
                        </td>
                    </tr>
                </table>
            @endif

            <!-- PENGESAHAN ASESI & ASESOR -->
            <table class="tabel-bnsp">
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
                                        Diserahkan oleh Asesi ({{ $tglTtdAsesi ?? date('d-m-Y H:i') }})
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
                'prevForm' => ['route' => route('formulir.ia.index', ['kodeForm' => 'FR.IA.06B', 'pendaftaranId' => $pendaftaran->id]), 'label' => 'FR.IA.06.B Kunci Jawaban'],
                'nextForm' => ['route' => route('formulir.ia.index', ['kodeForm' => 'FR.IA.07', 'pendaftaranId' => $pendaftaran->id]), 'label' => 'FR.IA.07 Pertanyaan Lisan']
            ])

        </div>
    </form>
    @endif
</div>
@endsection

@push('js')
    <script src="{{ asset('js/formulir/formulir-bnsp.js') }}"></script>
    <script>
        const isAsesi = {{ $isAsesi ? 'true' : 'false' }};
        const isSubmitted = {{ $isSubmitted ? 'true' : 'false' }};
        const pendaftaranId = {{ $pendaftaran->id }};
        const storageKey = 'draft_esai_' + pendaftaranId;

        let saveTimeout = null;
        function onEssayInput(no) {
            if (!isAsesi || isSubmitted) return;

            clearTimeout(saveTimeout);
            saveTimeout = setTimeout(function() {
                let draft = {};
                try {
                    draft = JSON.parse(localStorage.getItem(storageKey) || '{}');
                } catch(e) {}

                draft[no] = document.getElementById('jawaban_esai_' + no).value;
                try {
                    localStorage.setItem(storageKey, JSON.stringify(draft));
                    const statusEl = document.getElementById('draft-status-indicator');
                    if (statusEl) {
                        statusEl.innerHTML = 'Draft Tersimpan';
                        setTimeout(() => {
                            statusEl.innerHTML = 'Draft Auto-Save Aktif';
                        }, 2000);
                    }
                } catch(e) {}
            }, 300);
        }

        function restoreDraft() {
            if (!isAsesi || isSubmitted) return;
            try {
                const draft = JSON.parse(localStorage.getItem(storageKey) || '{}');
                Object.keys(draft).forEach(function(no) {
                    const el = document.getElementById('jawaban_esai_' + no);
                    if (el && (!el.value || el.value.trim() === '')) {
                        el.value = draft[no];
                    }
                });
            } catch(e) {}
        }

        let isSubmitting = false;

        function konfirmasiKirimEsai() {
            let kosong = 0;
            let firstKosong = null;
            
            @foreach($soalList as $no => $item)
                const el{{ $no }} = document.getElementById('jawaban_esai_{{ $no }}');
                if (!el{{ $no }} || el{{ $no }}.value.trim() === '') {
                    kosong++;
                    if (!firstKosong) firstKosong = {{ $no }};
                }
            @endforeach

            if (kosong > 0) {
                const targetEl = document.getElementById('jawaban_esai_' + firstKosong);
                if (targetEl) {
                    targetEl.scrollIntoView({ behavior: 'smooth', block: 'center' });
                    targetEl.focus();
                    targetEl.style.borderColor = '#ef4444';
                    targetEl.style.boxShadow = '0 0 0 3px rgba(239, 68, 68, 0.2)';
                    setTimeout(() => {
                        targetEl.style.borderColor = '#cbd5e1';
                        targetEl.style.boxShadow = '';
                    }, 2500);
                }

                if (window.Swal) {
                    Swal.fire({
                        icon: 'error',
                        title: 'Jawaban Esai Belum Lengkap!',
                        html: '<p style="margin:0 0 0.5rem 0;">Masih ada <strong>' + kosong + ' butir soal esai</strong> yang belum dijawab dengan lengkap.</p><p style="margin:0; font-size:0.9rem; color:#64748b;">(Sistem telah mengarahkan kursor Anda ke <strong>Soal No. ' + firstKosong + '</strong>). Seluruh butir soal esai wajib diisi sebelum mengumpulkan.</p>',
                        confirmButtonColor: '#ef4444',
                        confirmButtonText: 'Lengkapi Jawaban Sekarang'
                    });
                } else {
                    alert('Ujian esai belum dapat dikirimkan!\n\nMasih ada ' + kosong + ' butir soal esai yang belum dijawab (Soal No. ' + firstKosong + ').\nSeluruh soal wajib diisi sebelum mengumpulkan.');
                }
                return;
            }

            if (window.Swal) {
                Swal.fire({
                    icon: 'question',
                    title: 'Konfirmasi Pengumpulan Ujian Esai',
                    html: '<div style="text-align:left; font-size:0.92rem; color:#334155; line-height:1.6;">' +
                          '<div style="background:#f0fdf4; border:1px solid #86efac; border-radius:8px; padding:0.75rem 1rem; margin-bottom:0.75rem; color:#15803d; font-weight:700;">' +
                          'Seluruh jawaban soal esai telah terisi dengan lengkap!' +
                          '</div>' +
                          '<p style="margin:0 0 0.5rem 0;"><strong>Perhatian Penting:</strong></p>' +
                          '<ul style="margin:0 0 0 1.25rem; padding:0;">' +
                          '<li>Jawaban hanya dapat dikirimkan <strong>1 (satu) kali</strong>.</li>' +
                          '<li>Lembar jawaban yang dikirimkan akan langsung diteruskan ke Asesor Penguji untuk evaluasi.</li>' +
                          '</ul></div>',
                    showCancelButton: true,
                    confirmButtonColor: '#16a34a',
                    cancelButtonColor: '#64748b',
                    confirmButtonText: 'Ya, Kumpulkan Lembar Esai',
                    cancelButtonText: 'Batal / Periksa Kembali'
                }).then((result) => {
                    if (result.isConfirmed) {
                        try {
                            localStorage.removeItem(storageKey);
                        } catch(e) {}
                        isSubmitting = true;
                        document.getElementById('form-ia-06c').submit();
                    }
                });
            } else {
                const msg = 'Seluruh butir soal esai telah terisi lengkap.\n\nApakah Anda yakin ingin mengirimkan lembar jawaban esai ini sekarang?\n(Jawaban hanya dapat dikirim 1 kali dan tidak dapat diubah kembali).';
                if (confirm(msg)) {
                    try {
                        localStorage.removeItem(storageKey);
                    } catch(e) {}
                    isSubmitting = true;
                    document.getElementById('form-ia-06c').submit();
                }
            }
        }

        // =====================================================================
        // CBT LOCKDOWN: PENCEGAHAN KELUAR / BERPINDAH HALAMAN SAAT UJIAN ESAI AKTIF
        // =====================================================================
        if (isAsesi && !isSubmitted) {
            // 1. Kunci reload & tutup tab browser
            window.addEventListener('beforeunload', function (e) {
                if (!isSubmitting) {
                    e.preventDefault();
                    e.returnValue = 'Peringatan: Sesi ujian esai sedang berlangsung! Jawaban Anda belum dikirimkan.';
                    return e.returnValue;
                }
            });

            // 2. Kunci tombol Back / History browser
            history.pushState(null, null, location.href);
            window.onpopstate = function () {
                if (!isSubmitting) {
                    history.pushState(null, null, location.href);
                    if (window.Swal) {
                        Swal.fire({
                            icon: 'warning',
                            title: 'Akses Keluar Dinonaktifkan!',
                            text: 'Anda sedang berada dalam sesi ujian esai. Anda tidak diizinkan meninggalkan halaman sebelum mengirimkan seluruh jawaban.',
                            confirmButtonColor: '#2563eb',
                            confirmButtonText: 'Kembali ke Lembar Esai'
                        });
                    }
                }
            };

            // 3. Kunci semua tautan / link navigasi di dalam halaman
            document.addEventListener('click', function(e) {
                const link = e.target.closest('a');
                if (link && !isSubmitting) {
                    const href = link.getAttribute('href');
                    if (href && !href.startsWith('#') && !href.startsWith('javascript:')) {
                        e.preventDefault();
                        if (window.Swal) {
                            Swal.fire({
                                icon: 'warning',
                                title: 'Halaman Lain Terkunci!',
                                text: 'Selesaikan seluruh butir soal esai dan kumpulkan lembar ujian terlebih dahulu untuk dapat mengakses menu lainnya.',
                                confirmButtonColor: '#2563eb',
                                confirmButtonText: 'Tetap di Ujian'
                            });
                        }
                    }
                }
            }, true);

            // 4. Deteksi perpindahan tab / jendela aplikasi
            document.addEventListener('visibilitychange', function() {
                if (!isSubmitting && !document.hidden && window.Swal) {
                    Swal.fire({
                        icon: 'warning',
                        title: 'Peringatan: Tetap Fokus!',
                        text: 'Anda terdeteksi berpindah jendela/tab aplikasi. Harap tetap berada pada lembar ujian esai ini hingga selesai.',
                        confirmButtonColor: '#f59e0b',
                        confirmButtonText: 'Mengerti'
                    });
                }
            });
        }

        document.addEventListener('DOMContentLoaded', function() {
            if (isAsesi && !isSubmitted) {
                document.body.classList.add('mode-fokus-ujian');
                restoreDraft();
            }
        });
    </script>
@endpush

