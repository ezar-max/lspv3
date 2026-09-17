@extends('tata-letak.dasbor')

@section('judul', 'FR.IA.05C - Antarmuka Ujian CBT Pilihan Ganda (Asesi)')

@push('css')
    <link rel="stylesheet" href="{{ asset('css/formulir/formulir-bnsp.css') }}">
    <style>
        .cbt-container {
            display: grid;
            grid-template-columns: 1fr 320px;
            gap: 1.5rem;
            align-items: start;
        }
        @media (max-width: 992px) {
            .cbt-container {
                grid-template-columns: 1fr;
            }
        }
        .timer-box {
            background: linear-gradient(135deg, #fef2f2 0%, #fee2e2 100%);
            border: 2px solid #ef4444;
            color: #b91c1c;
            padding: 0.6rem 1.25rem;
            border-radius: 12px;
            font-weight: 800;
            font-size: 1.25rem;
            display: inline-flex;
            align-items: center;
            gap: 0.6rem;
            box-shadow: 0 4px 6px -1px rgba(239, 68, 68, 0.15);
            font-family: 'Courier New', Courier, monospace;
            letter-spacing: 1px;
        }
        .panel-nav-soal {
            background: #ffffff;
            border: 1px solid #e2e8f0;
            border-radius: 12px;
            padding: 1.25rem;
            box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.05);
            position: sticky;
            top: 1rem;
        }
        .grid-nomor-soal {
            display: grid;
            grid-template-columns: repeat(5, 1fr);
            gap: 0.6rem;
            margin-top: 0.85rem;
        }
        .btn-nomor {
            height: 44px;
            display: flex;
            align-items: center;
            justify-content: center;
            border-radius: 8px;
            font-weight: 800;
            font-size: 1rem;
            cursor: pointer;
            border: 2px solid #ef4444;
            background: #fee2e2;
            color: #dc2626;
            transition: all 0.15s ease;
        }
        .btn-nomor:hover {
            transform: translateY(-2px);
            box-shadow: 0 4px 6px -1px rgba(0,0,0,0.1);
        }
        .btn-nomor.terjawab {
            background: #16a34a !important;
            border-color: #15803d !important;
            color: #ffffff !important;
        }
        .btn-nomor.belum-terjawab {
            background: #fee2e2;
            border-color: #ef4444;
            color: #dc2626;
        }
        .btn-nomor.aktif {
            border-color: #2563eb !important;
            box-shadow: 0 0 0 3.5px rgba(37, 99, 235, 0.35) !important;
            transform: scale(1.05);
        }
        .kartu-soal-cbt {
            background: #ffffff;
            border: 1px solid #e2e8f0;
            border-radius: 12px;
            padding: 1.75rem;
            box-shadow: 0 4px 6px -1px rgba(0,0,0,0.05);
            margin-bottom: 1.5rem;
        }
        .opsi-cbt-card {
            display: flex;
            align-items: flex-start;
            gap: 0.85rem;
            padding: 0.9rem 1.15rem;
            border: 1.5px solid #e2e8f0;
            border-radius: 10px;
            cursor: pointer;
            margin-bottom: 0.65rem;
            transition: all 0.2s ease;
            background: #ffffff;
        }
        .opsi-cbt-card:hover {
            border-color: #3b82f6;
            background-color: #f8fafc;
        }
        .opsi-cbt-card.terpilih {
            border-color: #16a34a;
            background-color: #f0fdf4;
        }
        .opsi-cbt-card input[type="radio"] {
            margin-top: 0.2rem;
            width: 18px;
            height: 18px;
            accent-color: #16a34a;
            cursor: pointer;
        }
        .opsi-abjad {
            width: 26px;
            height: 26px;
            border-radius: 6px;
            background: #f1f5f9;
            color: #334155;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            font-weight: 800;
            font-size: 0.85rem;
            flex-shrink: 0;
        }
        .opsi-cbt-card.terpilih .opsi-abjad {
            background: #16a34a;
            color: #ffffff;
        }
        .skor-badge-jumbo {
            background: linear-gradient(135deg, #1e3a8a 0%, #0284c7 100%);
            color: #ffffff;
            border-radius: 12px;
            padding: 1.5rem;
            text-align: center;
            margin-bottom: 1.5rem;
            box-shadow: 0 10px 15px -3px rgba(0, 0, 0, 0.1);
        }
        .cbt-nav-controls {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-top: 1.5rem;
            padding-top: 1.25rem;
            border-top: 1px solid #f1f5f9;
            flex-wrap: wrap;
            gap: 0.75rem;
        }
        @keyframes shake {
            0%, 100% { transform: translateX(0); }
            20%, 60% { transform: translateX(-6px); }
            40%, 80% { transform: translateX(6px); }
        }
    </style>
@endpush

@section('konten')
<div class="wadah-formulir-bnsp animasi-slide">
    
    @php
        $asesorNama = $pendaftaran->asesor->nama_lengkap ?? 'Asesor LSP';
        $asesiNama = $pendaftaran->asesi->nama_lengkap ?? (auth()->user()->nama_lengkap ?? 'Nama Asesi');
        $isAsesi = auth()->check() && auth()->user()->peran === 'asesi';
        
        $isCompleted = ($iaRecord && ($iaRecord->status === 'completed' || isset($iaRecord->data_jawaban['skor'])));
        $skorData = $iaRecord->data_jawaban ?? [];
        $savedJawaban = $skorData['jawaban'] ?? [];
        $totalSoal = count($soalList);

        $hitungBenar = 0;
        $hitungSalah = 0;
        foreach ($soalList as $no => $item) {
            $kunci = strtoupper(trim($item['kunci'] ?? ''));
            $ans = isset($savedJawaban[$no]) ? strtoupper(trim($savedJawaban[$no])) : null;
            if ($ans !== null && $ans !== '' && $ans === $kunci) {
                $hitungBenar++;
            } else {
                $hitungSalah++;
            }
        }

        if (!empty($savedJawaban) && $totalSoal > 0) {
            $totalBenar = $hitungBenar;
            $totalSalah = $hitungSalah;
            $skorNilai = (int) round(($totalBenar / $totalSoal) * 100);
        } else {
            $skorNilai = $skorData['skor'] ?? 0;
            $totalBenar = $skorData['benar'] ?? 0;
            $totalSalah = $skorData['salah'] ?? 0;
        }

        $rekomendasiHasil = $skorNilai >= 70 ? 'K' : 'BK';
    @endphp

    <!-- ACTION BAR ATAS -->
    <div class="action-bar-formulir no-print">
        <div class="action-bar-kiri">
            @if($isAsesi && !$isCompleted && $totalSoal > 0)
                <span class="lencana lencana-amber">
                    Mode Ujian Aktif (Sesi Pengerjaan Berlangsung)
                </span>
            @else
                <a href="{{ route('formulir.index', ['pendaftaran_id' => $pendaftaran->id]) }}" class="tombol tombol-sekunder tombol-sm">
                    Kembali ke Daftar Formulir
                </a>
            @endif
            <span class="lencana lencana-hijau">
                FR.IA.05C Ujian CBT Online
            </span>
            @if(!$isAsesi)
                <span class="lencana lencana-biru">
                    Mode Pratinjau Asesor
                </span>
            @endif
        </div>
        <div class="action-bar-kanan">
            @if($isAsesi && !$isCompleted)
                @if($totalSoal > 0)
                    <div class="timer-box" style="display: inline-flex; align-items: center; padding: 0.35rem 0.75rem; background: #fee2e2; color: #b91c1c; border-radius: 6px; font-weight: 800;">
                        Waktu: <span id="cbt-timer" style="margin-left: 0.35rem;">60:00</span>
                    </div>
                    <button type="button" onclick="konfirmasiSelesaiUjian()" class="tombol tombol-utama tombol-sm" style="background: #16a34a; border-color: #16a34a; font-weight: 700;">
                        Selesai & Kumpulkan Ujian
                    </button>
                @endif
            @elseif(!$isAsesi)
                <a href="{{ route('formulir.ia05b', $pendaftaran->id) }}" class="tombol tombol-utama tombol-sm" style="background: #059669; border-color: #059669; font-weight: 700;">
                    Kunci & Evaluasi (FR.IA.05B)
                </a>
                <button type="button" onclick="window.print()" class="tombol tombol-sekunder tombol-sm btn-cetak-formulir">
                    Cetak Lembar Ujian
                </button>
            @else
                <span class="lencana lencana-biru">
                    Ujian Telah Selesai & Terkunci
                </span>
                <button type="button" onclick="window.print()" class="tombol tombol-sekunder tombol-sm btn-cetak-formulir">
                    Cetak Lembar Jawaban
                </button>
            @endif
        </div>
    </div>

    @if(!$isAsesi)
        @include('komponen.banner-readonly-asesi', [
            'judul' => 'Mode Pratinjau Asesor (Hanya Baca)',
            'keterangan' => 'Lembar pengerjaan soal pilihan ganda ini dikerjakan langsung oleh asesi (' . $asesiNama . '). Kunci jawaban dan evaluasi ada pada FR.IA.05B.',
            'status' => 'Pratinjau Asesor',
            'tipe' => 'info'
        ])
    @endif

    <!-- JIKA SUDAH SELESAI: TAMPILKAN HASIL AUTO-GRADING -->
    @if($isCompleted && $totalSoal > 0)
        <div class="skor-badge-jumbo">
            <div style="font-size: 0.9rem; text-transform: uppercase; letter-spacing: 1px; opacity: 0.9; margin-bottom: 0.35rem;">
                Hasil Evaluasi Uji Pengetahuan Pilihan Ganda (Auto-Grading)
            </div>
            <div style="font-size: 3rem; font-weight: 900; line-height: 1; margin-bottom: 0.5rem;">
                {{ $skorNilai }} <span style="font-size: 1.25rem; font-weight: 600; opacity: 0.85;">/ 100</span>
            </div>
            <div style="display: flex; justify-content: center; gap: 1.5rem; flex-wrap: wrap; margin-top: 1rem;">
                <div style="background: rgba(255,255,255,0.15); padding: 0.4rem 1rem; border-radius: 8px; font-size: 0.9rem;">
                    Benar: <strong>{{ $totalBenar }} Soal</strong>
                </div>
                <div style="background: rgba(255,255,255,0.15); padding: 0.4rem 1rem; border-radius: 8px; font-size: 0.9rem;">
                    Salah: <strong>{{ $totalSalah }} Soal</strong>
                </div>
                <div style="background: rgba(255,255,255,0.15); padding: 0.4rem 1rem; border-radius: 8px; font-size: 0.9rem;">
                    Rekomendasi: <strong>{{ $rekomendasiHasil === 'K' ? 'Kompeten (Lulus)' : 'Belum Kompeten' }}</strong>
                </div>
            </div>
        </div>
    @endif

    @if($totalSoal === 0)
        <!-- KONDISI KOSONG: JIKA SKEMA INI BELUM MEMILIKI DATA SOAL -->
        <div style="background: #ffffff; border: 1.5px solid #e2e8f0; border-radius: 16px; padding: 3.5rem 2rem; text-align: center; margin: 1.5rem auto; max-width: 680px; box-shadow: 0 4px 15px rgba(0,0,0,0.04);">
            <h3 style="font-size: 1.35rem; font-weight: 800; color: #0f172a; margin: 0 0 0.5rem 0;">Bank Soal CBT Belum Tersedia</h3>
            <p style="font-size: 0.92rem; color: #64748b; line-height: 1.6; margin: 0 0 1.5rem 0;">
                Perangkat instrumen & bank soal pilihan ganda untuk skema <strong>{{ $pendaftaran->skema->nama_skema ?? 'Skema Terpilih' }}</strong> ({{ $pendaftaran->skema->kode_skema ?? '-' }}) belum diunggah atau belum diterbitkan oleh Tim Asesor / LSP. Lembar ujian belum dapat diisi saat ini.
            </p>
            <a href="{{ route('formulir.index', ['pendaftaran_id' => $pendaftaran->id]) }}" class="tombol tombol-utama" style="background: #2563eb; border-color: #2563eb; padding: 0.65rem 1.5rem; font-weight: 700;">
                Kembali ke Galeri Formulir
            </a>
        </div>
    @else
        <div class="cbt-container">
            
            <!-- KOLOM UTAMA: LEMBAR SOAL UJIAN (1 SOAL PER TAMPILAN SESUAI NOMOR AKTIF) -->
            <div class="area-soal-cbt">
                
                <!-- KOP RESMI DOKUMEN STANDAR BNSP -->
                <div style="background: #fff; padding: 1.5rem 1.5rem 0.5rem 1.5rem; border-radius: 12px; margin-bottom: 1.5rem; border: 1px solid #e2e8f0;">
                    @include('komponen.kop-formulir-bnsp', [
                        'kodeForm' => 'FR.IA.05C',
                        'judulForm' => 'LEMBAR UJIAN TERTULIS PILIHAN GANDA (CBT)',
                        'tipeDokumen' => 'Ujian Tertulis'
                    ])
                </div>

                <form id="form-cbt-ia05" action="{{ route('formulir.ia.simpan', ['kodeForm' => 'FR.IA.05C', 'pendaftaranId' => $pendaftaran->id]) }}" method="POST">
                    @csrf

                @foreach($soalList as $no => $item)
                    @php
                        $userAnswer = isset($savedJawaban[$no]) ? strtoupper(trim($savedJawaban[$no])) : null;
                        $kunciResmi = strtoupper(trim($item['kunci'] ?? ''));
                        $detailItem = $skorData['detail_hasil'][$no] ?? null;
                        $isItemBenar = ($userAnswer !== null && $userAnswer !== '' && $userAnswer === $kunciResmi);
                    @endphp

                    <div class="kartu-soal-cbt cbt-question-card" id="soal-block-{{ $no }}" data-soal-number="{{ $no }}" style="{{ $no === 1 ? '' : 'display: none;' }}">
                        <div style="display: flex; justify-content: space-between; align-items: center; border-bottom: 1px solid #f1f5f9; padding-bottom: 0.75rem; margin-bottom: 1.25rem;">
                            <div style="display: flex; align-items: center; gap: 0.6rem;">
                                <span style="background: #1e3a8a; color: #ffffff; min-width: 32px; height: 32px; border-radius: 8px; display: inline-flex; align-items: center; justify-content: center; font-weight: 800; font-size: 0.95rem;">
                                    {{ $no }}
                                </span>
                                <span style="font-size: 0.85rem; font-weight: 700; color: #475569; text-transform: uppercase;">
                                    {{ $item['kuk'] }}
                                </span>
                            </div>
                            @if($isCompleted)
                                @if($isItemBenar)
                                    <span class="lencana lencana-hijau" style="font-size: 0.8rem; padding: 0.35rem 0.75rem; font-weight: 800;">
                                        Jawaban Anda Benar
                                    </span>
                                @else
                                    <span class="lencana lencana-merah" style="font-size: 0.8rem; padding: 0.35rem 0.75rem; font-weight: 800;">
                                        Jawaban Anda Salah (Kunci: {{ $kunciResmi }})
                                    </span>
                                @endif
                            @else
                                <span style="font-size: 0.82rem; font-weight: 700; color: #64748b;">
                                    Soal {{ $no }} dari {{ $totalSoal }}
                                </span>
                            @endif
                        </div>

                        <!-- TEKS PERTANYAAN -->
                        <div style="font-size: 1.08rem; font-weight: 600; color: #0f172a; line-height: 1.65; margin-bottom: 1.5rem;">
                            {{ $item['pertanyaan'] }}
                        </div>

                        <!-- OPSI PILIHAN A, B, C, D -->
                        <div class="daftar-opsi-soal">
                            @foreach($item['opsi'] as $abjad => $teksOpsi)
                                @php
                                    $abjadClean = strtoupper(trim($abjad));
                                    $isSelected = ($userAnswer === $abjadClean);
                                    $isKunci = ($kunciResmi === $abjadClean);

                                    $cardClass = 'opsi-cbt-card';
                                    $cardStyle = '';
                                    if ($isCompleted) {
                                        if ($isKunci) {
                                            $cardStyle = 'border: 2px solid #16a34a; background: #f0fdf4;';
                                        } elseif ($isSelected && !$isKunci) {
                                            $cardStyle = 'border: 2px solid #ef4444; background: #fef2f2;';
                                        }
                                    } elseif ($isSelected) {
                                        $cardClass .= ' terpilih';
                                    }
                                @endphp
                                <label class="{{ $cardClass }}" style="{{ $cardStyle }} {{ (!$isAsesi || $isCompleted) ? 'cursor: default;' : 'cursor: pointer;' }}">
                                    <input type="radio" name="jawaban[{{ $no }}]" value="{{ $abjadClean }}" {{ $isSelected ? 'checked' : '' }} {{ ($isCompleted || !$isAsesi) ? 'disabled' : '' }} onchange="updateIndikatorNomor({{ $no }})">
                                    <span class="opsi-abjad" style="{{ $isCompleted && $isKunci ? 'background: #16a34a; color: #ffffff;' : ($isCompleted && $isSelected && !$isKunci ? 'background: #ef4444; color: #ffffff;' : '') }}">
                                        {{ $abjadClean }}
                                    </span>
                                    <span style="font-size: 0.95rem; color: #1e293b; line-height: 1.5; flex: 1;">
                                        {{ $teksOpsi }}
                                    </span>
                                    @if($isCompleted)
                                        @if($isKunci && $isSelected)
                                            <span class="lencana lencana-hijau" style="font-size: 0.72rem; margin-left: auto;">
                                                Pilihan Anda (Benar)
                                            </span>
                                        @elseif($isKunci)
                                            <span class="lencana lencana-hijau" style="font-size: 0.72rem; margin-left: auto;">
                                                Kunci Jawaban Resmi
                                            </span>
                                        @elseif($isSelected)
                                            <span class="lencana lencana-merah" style="font-size: 0.72rem; margin-left: auto;">
                                                Pilihan Anda (Salah)
                                            </span>
                                        @endif
                                    @endif
                                </label>
                            @endforeach
                        </div>

                        <!-- PEMBAHASAN JIKA SUDAH DINILAI -->
                        @if($isCompleted && !empty($item['pembahasan']))
                            <div style="background: #f8fafc; border-left: 3px solid #0284c7; padding: 0.75rem 1rem; border-radius: 0 6px 6px 0; margin-top: 1rem; font-size: 0.85rem; color: #334155;">
                                <strong style="color: #0369a1; display: block; margin-bottom: 0.2rem;">Penjelasan / Pembahasan:</strong>
                                {{ $item['pembahasan'] }}
                            </div>
                        @endif

                        <!-- TOMBOL NAVIGASI SEBELUMNYA / SELANJUTNYA -->
                        <div class="cbt-nav-controls">
                            <div>
                                @if($no > 1)
                                    <button type="button" class="tombol tombol-sekunder tombol-sm" onclick="bukaSoalNomor({{ $no - 1 }})" style="font-weight: 700;">
                                        Soal Sebelumnya
                                    </button>
                                @endif
                            </div>
                            <div style="display: flex; gap: 0.5rem;">
                                @if($no < $totalSoal)
                                    <button type="button" class="tombol tombol-utama tombol-sm" onclick="bukaSoalNomor({{ $no + 1 }})" style="background: #2563eb; border-color: #2563eb; font-weight: 700;">
                                        Soal Selanjutnya
                                    </button>
                                @endif
                            </div>
                        </div>

                    </div>
                @endforeach

            </form>
        </div>

        <!-- KOLOM KANAN: PANEL NAVIGASI NOMOR SOAL & RINGKASAN CBT -->
        <div class="kolom-sidebar-cbt">
            
            <div class="panel-nav-soal">
                <div style="display: flex; justify-content: space-between; align-items: center; border-bottom: 1px solid #f1f5f9; padding-bottom: 0.6rem;">
                    <strong style="font-size: 0.92rem; color: #0f172a;">Navigasi Butir Soal</strong>
                    <span id="stat-terjawab" style="font-size: 0.78rem; font-weight: 700; color: #16a34a;">0 / {{ $totalSoal }} Terjawab</span>
                </div>

                <div class="grid-nomor-soal">
                    @foreach($soalList as $no => $item)
                        @php
                            $userAnswer = isset($savedJawaban[$no]) ? strtoupper(trim($savedJawaban[$no])) : null;
                            $kunciResmi = strtoupper(trim($item['kunci'] ?? ''));
                            $isItemBenar = ($userAnswer !== null && $userAnswer !== '' && $userAnswer === $kunciResmi);
                            
                            $btnClass = 'belum-terjawab';
                            if ($isCompleted) {
                                $btnClass = $isItemBenar ? 'terjawab' : 'belum-terjawab';
                            } elseif ($userAnswer) {
                                $btnClass = 'terjawab';
                            }
                            if ($no === 1) {
                                $btnClass .= ' aktif';
                            }
                        @endphp
                        <button type="button" class="btn-nomor {{ $btnClass }}" id="nav-no-{{ $no }}" onclick="bukaSoalNomor({{ $no }})">
                            {{ $no }}
                        </button>
                    @endforeach
                </div>

                <div style="border-top: 1px solid #f1f5f9; margin-top: 1.25rem; padding-top: 0.85rem; font-size: 0.8rem; color: #475569;">
                    @if($isCompleted)
                        <div style="display: flex; align-items: center; gap: 0.5rem; margin-bottom: 0.45rem;">
                            <span style="width: 14px; height: 14px; border-radius: 4px; background: #16a34a; border: 1px solid #15803d;"></span>
                            <span style="font-weight: 600; color: #166534;">Jawaban Benar (Hijau)</span>
                        </div>
                        <div style="display: flex; align-items: center; gap: 0.5rem; margin-bottom: 0.45rem;">
                            <span style="width: 14px; height: 14px; border-radius: 4px; background: #fee2e2; border: 1.5px solid #ef4444;"></span>
                            <span style="font-weight: 600; color: #dc2626;">Jawaban Salah (Merah)</span>
                        </div>
                    @else
                        <div style="display: flex; align-items: center; gap: 0.5rem; margin-bottom: 0.45rem;">
                            <span style="width: 14px; height: 14px; border-radius: 4px; background: #16a34a; border: 1px solid #15803d;"></span>
                            <span style="font-weight: 600; color: #166534;">Sudah Terjawab (Hijau)</span>
                        </div>
                        <div style="display: flex; align-items: center; gap: 0.5rem; margin-bottom: 0.45rem;">
                            <span style="width: 14px; height: 14px; border-radius: 4px; background: #fee2e2; border: 1.5px solid #ef4444;"></span>
                            <span style="font-weight: 600; color: #dc2626;">Belum Terjawab (Merah)</span>
                        </div>
                    @endif
                    <div style="display: flex; align-items: center; gap: 0.5rem;">
                        <span style="width: 14px; height: 14px; border-radius: 4px; background: #ffffff; border: 2.5px solid #2563eb;"></span>
                        <span style="font-weight: 600; color: #1d4ed8;">Sedang Dibuka (Aktif)</span>
                    </div>
                </div>

                @if(!$isCompleted)
                    <div style="margin-top: 1.25rem; padding: 0.75rem; background: #f8fafc; border: 1px solid #e2e8f0; border-radius: 6px;">
                        <div style="font-size: 0.78rem; text-align: center; color: #475569; line-height: 1.5;">
                            Auto-save aktif otomatis. Klik <strong>Selesai & Kumpulkan Ujian</strong> di bagian atas setelah selesai.
                        </div>
                    </div>
                @endif
            </div>

        </div>

    </div>
    @endif

</div>
@endsection

@push('js')
    <script src="{{ asset('js/formulir/formulir-bnsp.js') }}"></script>
    <script>
        // Logika CBT Interaktif
        const isCompleted = {{ $isCompleted ? 'true' : 'false' }};
        const isAsesi = {{ $isAsesi ? 'true' : 'false' }};
        const totalSoal = {{ $totalSoal }};
        const pendaftaranId = {{ $pendaftaran->id }};
        const storageKey = 'cbt_jawaban_' + pendaftaranId;
        const timerKey = 'cbt_timer_' + pendaftaranId;
        let activeQuestionNumber = 1;

        function bukaSoalNomor(no, force = false) {
            if (no < 1 || no > totalSoal) return;

            // Validasi: Wajib menjawab soal saat ini sebelum berpindah ke nomor lain
            if (!force && isAsesi && !isCompleted && activeQuestionNumber !== no) {
                const currentAnswer = document.querySelector('input[name="jawaban[' + activeQuestionNumber + ']"]:checked');
                if (!currentAnswer) {
                    tampilkanPeringatanWajibJawab(activeQuestionNumber);
                    return;
                }
            }

            activeQuestionNumber = no;

            // Sembunyikan semua card soal, tampilkan hanya nomor yang dipilih
            document.querySelectorAll('.cbt-question-card').forEach(function(card) {
                card.style.display = 'none';
            });
            const targetBlock = document.getElementById('soal-block-' + no);
            if (targetBlock) {
                targetBlock.style.display = 'block';
            }

            // Update border aktif di panel nomor sidebar
            document.querySelectorAll('.btn-nomor').forEach(function(btn) {
                btn.classList.remove('aktif');
            });
            const curBtn = document.getElementById('nav-no-' + no);
            if (curBtn) {
                curBtn.classList.add('aktif');
            }

            // Scroll halus ke atas area soal bila di layar kecil
            if (window.innerWidth < 992 && targetBlock) {
                targetBlock.scrollIntoView({ behavior: 'smooth', block: 'start' });
            }
        }

        function tampilkanPeringatanWajibJawab(noSoal) {
            const card = document.getElementById('soal-block-' + noSoal);
            if (card) {
                card.style.animation = 'shake 0.4s ease-in-out';
                card.style.borderColor = '#ef4444';
                setTimeout(() => {
                    card.style.animation = '';
                    card.style.borderColor = '#e2e8f0';
                }, 1000);
            }

            if (window.Swal) {
                Swal.fire({
                    icon: 'warning',
                    title: 'Soal No. ' + noSoal + ' Belum Dijawab!',
                    text: 'Anda wajib memilih salah satu jawaban pada soal ini sebelum dapat berpindah ke nomor lain atau menyelesaikan ujian.',
                    confirmButtonColor: '#2563eb',
                    confirmButtonText: 'Mengerti & Jawab Sekarang'
                });
            } else {
                alert('Peringatan: Soal No. ' + noSoal + ' belum Anda jawab!\nAnda wajib memilih salah satu jawaban sebelum dapat berpindah ke soal berikutnya.');
            }
        }

        function updateIndikatorNomor(no) {
            if (!isAsesi || isCompleted) return;
            const navBtn = document.getElementById('nav-no-' + no);
            const radios = document.querySelectorAll('input[name="jawaban[' + no + ']"]');
            let isChecked = false;
            let val = '';

            radios.forEach(function(r) {
                const parentCard = r.closest('.opsi-cbt-card');
                if (r.checked) {
                    isChecked = true;
                    val = r.value;
                    if (parentCard) parentCard.classList.add('terpilih');
                } else {
                    if (parentCard) parentCard.classList.remove('terpilih');
                }
            });

            if (navBtn && !isCompleted) {
                if (isChecked) {
                    navBtn.classList.remove('belum-terjawab');
                    navBtn.classList.add('terjawab');
                } else {
                    navBtn.classList.remove('terjawab');
                    navBtn.classList.add('belum-terjawab');
                }
            }

            hitungTerjawab();
            simpanDraftLocal(no, val);
        }

        function pilihOpsiCbt(no, abjad) {
            if (!isAsesi || isCompleted) return;
            const targetRadio = document.querySelector('input[name="jawaban[' + no + ']"][value="' + abjad + '"]');
            if (targetRadio) {
                targetRadio.checked = true;
                updateIndikatorNomor(no);
            }
        }

        function hitungTerjawab() {
            let count = 0;
            for (let i = 1; i <= totalSoal; i++) {
                const checked = document.querySelector('input[name="jawaban[' + i + ']"]:checked');
                const navBtn = document.getElementById('nav-no-' + i);
                if (checked) {
                    count++;
                    if (navBtn && !isCompleted) {
                        navBtn.classList.remove('belum-terjawab');
                        navBtn.classList.add('terjawab');
                    }
                } else {
                    if (navBtn && !isCompleted) {
                        navBtn.classList.remove('terjawab');
                        navBtn.classList.add('belum-terjawab');
                    }
                }
            }
            const label = document.getElementById('stat-terjawab');
            if (label) label.innerText = count + ' / ' + totalSoal + ' Terjawab';
        }

        function simpanDraftLocal(no, val) {
            if (!isAsesi || isCompleted) return;
            try {
                let saved = JSON.parse(localStorage.getItem(storageKey) || '{}');
                saved[no] = val;
                localStorage.setItem(storageKey, JSON.stringify(saved));
            } catch(e) {}
        }

        function pulihkanDraftLocal() {
            if (!isAsesi || isCompleted) return;
            try {
                let saved = JSON.parse(localStorage.getItem(storageKey) || '{}');
                Object.keys(saved).forEach(function(no) {
                    const val = saved[no];
                    const radio = document.querySelector('input[name="jawaban[' + no + ']"][value="' + val + '"]');
                    if (radio) {
                        radio.checked = true;
                        updateIndikatorNomor(no);
                    }
                });
            } catch(e) {}
        }

        // Timer CBT Countdown (60 Menit) - Hanya berjalan untuk asesi
        let waktuSisa = 60 * 60; // 3600 detik
        try {
            const savedTimer = localStorage.getItem(timerKey);
            if (savedTimer && isAsesi && !isCompleted) {
                waktuSisa = parseInt(savedTimer, 10);
            }
        } catch(e) {}

        function formatWaktu(detik) {
            const m = Math.floor(detik / 60);
            const s = detik % 60;
            return (m < 10 ? '0' : '') + m + ':' + (s < 10 ? '0' : '') + s;
        }

        let timerInterval = null;
        if (isAsesi && !isCompleted) {
            timerInterval = setInterval(function() {
                waktuSisa--;
                const elem = document.getElementById('cbt-timer');
                if (elem) elem.innerText = formatWaktu(waktuSisa);

                try {
                    localStorage.setItem(timerKey, waktuSisa);
                } catch(e) {}

                if (waktuSisa <= 0) {
                    clearInterval(timerInterval);
                    alert('Waktu ujian telah habis! Sistem akan mengumpulkan jawaban Anda secara otomatis.');
                    isSubmitting = true;
                    document.getElementById('form-cbt-ia05').submit();
                }
            }, 1000);
        }

        let isSubmitting = false;

        function konfirmasiSelesaiUjian() {
            if (!isAsesi || isCompleted) return;
            let terjawab = 0;
            let firstUnanswered = null;

            for (let i = 1; i <= totalSoal; i++) {
                if (document.querySelector('input[name="jawaban[' + i + ']"]:checked')) {
                    terjawab++;
                } else if (!firstUnanswered) {
                    firstUnanswered = i;
                }
            }
            const sisa = totalSoal - terjawab;

            if (sisa > 0) {
                bukaSoalNomor(firstUnanswered, true);
                if (window.Swal) {
                    Swal.fire({
                        icon: 'error',
                        title: 'Ujian Belum Lengkap!',
                        html: '<p style="margin:0 0 0.5rem 0;">Masih ada <strong>' + sisa + ' butir soal</strong> yang belum Anda jawab.</p><p style="margin:0; font-size:0.9rem; color:#64748b;">(Sistem telah mengarahkan Anda ke <strong>Soal No. ' + firstUnanswered + '</strong>). Seluruh soal wajib dijawab sebelum dapat mengumpulkan ujian.</p>',
                        confirmButtonColor: '#ef4444',
                        confirmButtonText: 'Kembali Kerjakan Soal'
                    });
                } else {
                    alert('Ujian belum dapat diselesaikan!\n\nMasih ada ' + sisa + ' butir soal yang belum dijawab (Soal No. ' + firstUnanswered + ').\nSeluruh butir soal wajib dijawab sebelum mengumpulkan.');
                }
                return;
            }

            // Jika semua sudah terjawab
            if (window.Swal) {
                Swal.fire({
                    icon: 'question',
                    title: 'Konfirmasi Pengumpulan Ujian',
                    html: '<div style="text-align:left; font-size:0.92rem; color:#334155; line-height:1.6;">' +
                          '<div style="background:#f0fdf4; border:1px solid #86efac; border-radius:8px; padding:0.75rem 1rem; margin-bottom:0.75rem; color:#15803d; font-weight:700;">' +
                          'Seluruh ' + totalSoal + ' butir soal telah dijawab dengan lengkap!' +
                          '</div>' +
                          '<p style="margin:0 0 0.5rem 0;"><strong>Perhatian Penting:</strong></p>' +
                          '<ul style="margin:0 0 0 1.25rem; padding:0;">' +
                          '<li>Jawaban hanya dapat dikirimkan <strong>1 (satu) kali</strong>.</li>' +
                          '<li>Sistem akan langsung menilai otomatis (Auto-Grading) dan mengunci lembar ujian.</li>' +
                          '</ul></div>',
                    showCancelButton: true,
                    confirmButtonColor: '#16a34a',
                    cancelButtonColor: '#64748b',
                    confirmButtonText: 'Ya, Kumpulkan Jawaban Sekarang',
                    cancelButtonText: 'Batal / Periksa Kembali'
                }).then((result) => {
                    if (result.isConfirmed) {
                        try {
                            localStorage.removeItem(timerKey);
                            localStorage.removeItem(storageKey);
                        } catch(e) {}
                        isSubmitting = true;
                        document.getElementById('form-cbt-ia05').submit();
                    }
                });
            } else {
                const pesan = 'Seluruh ' + totalSoal + ' soal telah dijawab lengkap.\n\nApakah Anda yakin ingin mengirimkan jawaban ujian sekarang?\n(Jawaban hanya dapat dikirim 1 kali dan langsung dikunci).';
                if (confirm(pesan)) {
                    try {
                        localStorage.removeItem(timerKey);
                        localStorage.removeItem(storageKey);
                    } catch(e) {}
                    isSubmitting = true;
                    document.getElementById('form-cbt-ia05').submit();
                }
            }
        }

        // =====================================================================
        // CBT LOCKDOWN: PENCEGAHAN KELUAR / BERPINDAH HALAMAN SAAT UJIAN AKTIF
        // =====================================================================
        if (isAsesi && !isCompleted) {
            // 1. Kunci navigasi reload & tutup tab browser
            window.addEventListener('beforeunload', function (e) {
                if (!isSubmitting) {
                    e.preventDefault();
                    e.returnValue = 'Peringatan: Sesi ujian sedang berlangsung! Jawaban Anda belum dikirimkan.';
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
                            text: 'Anda sedang berada dalam sesi ujian aktif. Anda tidak diizinkan meninggalkan halaman sebelum mengirimkan seluruh jawaban.',
                            confirmButtonColor: '#2563eb',
                            confirmButtonText: 'Kembali ke Lembar Ujian'
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
                                text: 'Selesaikan seluruh butir soal dan kumpulkan lembar ujian terlebih dahulu untuk dapat mengakses menu lainnya.',
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
                        text: 'Anda terdeteksi berpindah jendela/tab aplikasi. Harap tetap berada pada lembar ujian ini hingga selesai.',
                        confirmButtonColor: '#f59e0b',
                        confirmButtonText: 'Mengerti'
                    });
                }
            });
        }

        document.addEventListener('DOMContentLoaded', function() {
            if (isAsesi && !isCompleted) {
                document.body.classList.add('mode-fokus-ujian');
                pulihkanDraftLocal();
            }
            hitungTerjawab();
            bukaSoalNomor(1, true);
        });
    </script>
@endpush
