@extends('tata-letak.dasbor')

@section('judul', 'Ruang Ujian Online Asesmen')

@push('css')
    <style>
        .timer-badge {
            font-variant-numeric: tabular-nums;
        }
        /* Focus mode transition */
        [x-cloak] { display: none !important; }
    </style>
@endpush

@section('konten')
@php
    $isReadonly = ($statusSesi['is_readonly'] ?? false) || ($isSubmitted ?? false);
    $totalSoalCbt = count($soalCbt);
    $totalSoalEsai = count($soalEsai);
    $firstCbtNo = !empty($soalCbt) ? array_key_first($soalCbt) : 1;
@endphp

<div class="min-h-screen bg-slate-50/70 pb-24" 
     x-cloak
     x-data="{
        activeTab: '{{ $defaultTab ?? 'cbt' }}',
        pendaftaranId: {{ $pendaftaran->id }},
        csrfToken: '{{ csrf_token() }}',
        autosaveUrl: '{{ route('asesi.ujian.autosave') }}',
        isSubmitted: {{ $isSubmitted ? 'true' : 'false' }},
        isReadonly: {{ ($statusSesi['is_readonly'] ?? false) || $isSubmitted ? 'true' : 'false' }},
        saveStatus: 'Tersimpan otomatis',
        saveStatusTime: '{{ now()->format('H:i:s') }}',
        saving: false,
        statusJadwal: '{{ $statusSesi['status'] ?? 'aktif' }}',
        bisaAkses: {{ ($statusSesi['bisa_akses'] ?? false) ? 'true' : 'false' }},
        
        // CBT Navigation State
        cbtKeys: {{ json_encode(array_keys($soalCbt)) }},
        currentPgNo: {{ $firstCbtNo }},
        viewMode: 'fokus', // 'fokus' (per soal) atau 'daftar' (semua soal)
        fontSize: 'normal', // 'kecil', 'normal', 'besar'
        showMobilePalette: false,
        showSubmitModal: false,

        // Jawaban state
        jawabanPg: {{ json_encode($savedJawabanPg ?: (object)[]) }},
        jawabanEsai: {{ json_encode($savedJawabanEsai ?: (object)[]) }},
        catatanPraktik: '{{ addslashes($savedPraktik['catatan_praktik'] ?? '') }}',

        // Timer real-time
        timerSeconds: {{ $sisaDetik ?? 0 }},
        detikMenujuMulai: {{ $detikMenujuMulai ?? 0 }},
        timerDisplay: '00:00:00',
        countdownMulaiDisplay: '00:00:00',
        timerInterval: null,
        heartbeatInterval: null,

        formatDuration(sec) {
            const s = Math.max(0, parseInt(sec) || 0);
            const h = Math.floor(s / 3600);
            const m = Math.floor((s % 3600) / 60);
            const remainS = s % 60;
            return [
                h.toString().padStart(2, '0'),
                m.toString().padStart(2, '0'),
                remainS.toString().padStart(2, '0')
            ].join(':');
        },

        init() {
            if (this.statusJadwal === 'belum_mulai') {
                this.updateCountdownMulai();
                this.timerInterval = setInterval(() => {
                    if (this.detikMenujuMulai > 0) {
                        this.detikMenujuMulai--;
                        this.updateCountdownMulai();
                    } else {
                        clearInterval(this.timerInterval);
                        window.location.reload();
                    }
                }, 1000);

                // Heartbeat: Cek jika Asesor membuka sesi lebih awal
                this.heartbeatInterval = setInterval(async () => {
                    try {
                        const res = await fetch('{{ route('asesi.ujian.status-live') }}?pendaftaran_id=' + this.pendaftaranId, {
                            headers: { 'Accept': 'application/json', 'X-Requested-With': 'XMLHttpRequest' }
                        });
                        if (!res.ok) return;
                        const data = await res.json();
                        if (data.can_access && data.has_active_exam) {
                            clearInterval(this.heartbeatInterval);
                            clearInterval(this.timerInterval);
                            window.location.reload();
                        }
                    } catch (e) {}
                }, 4000);
            } else if (!this.isReadonly && this.statusJadwal === 'aktif') {
                this.updateTimerDisplay();
                this.timerInterval = setInterval(() => {
                    if (this.timerSeconds > 0) {
                        this.timerSeconds--;
                        this.updateTimerDisplay();
                    } else {
                        clearInterval(this.timerInterval);
                        alert('Waktu ujian telah berakhir. Jawaban Anda akan otomatis dikumpulkan.');
                        const form = document.getElementById('formSubmitUjianFinal');
                        if (form) {
                            form.submit();
                        } else {
                            window.location.reload();
                        }
                    }
                }, 1000);

                // Polling pantau perpanjangan waktu atau penutupan oleh asesor
                this.heartbeatInterval = setInterval(async () => {
                    try {
                        const res = await fetch('{{ route('asesi.ujian.status-live') }}?pendaftaran_id=' + this.pendaftaranId, {
                            headers: { 'Accept': 'application/json', 'X-Requested-With': 'XMLHttpRequest' }
                        });
                        if (!res.ok) return;
                        const data = await res.json();
                        
                        if (data.status_jadwal === 'selesai' || data.is_readonly) {
                            clearInterval(this.heartbeatInterval);
                            clearInterval(this.timerInterval);
                            alert('Asesor Penguji telah menutup sesi ujian ini. Jawaban Anda akan otomatis dikumpulkan.');
                            const form = document.getElementById('formSubmitUjianFinal');
                            if (form) {
                                form.submit();
                            } else {
                                window.location.reload();
                            }
                        } else if (data.sisa_detik && Math.abs(data.sisa_detik - this.timerSeconds) > 30) {
                            this.timerSeconds = data.sisa_detik;
                            this.updateTimerDisplay();
                        }
                    } catch (e) {}
                }, 12000);
            } else {
                this.timerDisplay = '00:00:00';
            }
        },

        updateTimerDisplay() {
            this.timerDisplay = this.formatDuration(this.timerSeconds);
        },

        updateCountdownMulai() {
            this.countdownMulaiDisplay = this.formatDuration(this.detikMenujuMulai);
        },

        goToFirstUnanswered() {
            for (let i = 0; i < this.cbtKeys.length; i++) {
                const no = this.cbtKeys[i];
                if (!this.jawabanPg[no] || this.jawabanPg[no].toString().trim() === '') {
                    this.activeTab = 'cbt';
                    this.goToPg(no);
                    return;
                }
            }
        },

        goToPg(no) {
            this.currentPgNo = no;
            this.showMobilePalette = false;
            if (this.viewMode === 'daftar') {
                const el = document.getElementById('soal_pg_' + no);
                if (el) {
                    el.scrollIntoView({ behavior: 'smooth', block: 'center' });
                }
            } else {
                window.scrollTo({ top: 120, behavior: 'smooth' });
            }
        },

        prevPg() {
            const idx = this.cbtKeys.indexOf(this.currentPgNo);
            if (idx > 0) {
                this.goToPg(this.cbtKeys[idx - 1]);
            }
        },

        nextPg() {
            const idx = this.cbtKeys.indexOf(this.currentPgNo);
            if (idx >= 0 && idx < this.cbtKeys.length - 1) {
                this.goToPg(this.cbtKeys[idx + 1]);
            }
        },

        isFirstPg() {
            return this.cbtKeys.indexOf(this.currentPgNo) === 0;
        },

        isLastPg() {
            return this.cbtKeys.indexOf(this.currentPgNo) === this.cbtKeys.length - 1;
        },

        async savePg(no, val) {
            if (this.isReadonly) return;
            this.jawabanPg[no] = val;
            this.saving = true;
            this.saveStatus = 'Menyimpan...';

            try {
                const res = await fetch(this.autosaveUrl, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': this.csrfToken,
                        'Accept': 'application/json'
                    },
                    body: JSON.stringify({
                        pendaftaran_id: this.pendaftaranId,
                        tipe: 'cbt',
                        no: no,
                        jawaban: val
                    })
                });
                const data = await res.json();
                if (data.status === 'success') {
                    this.saveStatus = 'Tersimpan otomatis';
                    this.saveStatusTime = data.saved_at || new Date().toLocaleTimeString();
                }
            } catch (err) {
                console.error(err);
                this.saveStatus = 'Gagal menyimpan';
            } finally {
                this.saving = false;
            }
        },

        async saveEsai(no, val) {
            if (this.isReadonly) return;
            this.jawabanEsai[no] = val;
            this.saving = true;
            this.saveStatus = 'Menyimpan esai...';

            try {
                const res = await fetch(this.autosaveUrl, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': this.csrfToken,
                        'Accept': 'application/json'
                    },
                    body: JSON.stringify({
                        pendaftaran_id: this.pendaftaranId,
                        tipe: 'esai',
                        no: no,
                        jawaban: val
                    })
                });
                const data = await res.json();
                if (data.status === 'success') {
                    this.saveStatus = 'Tersimpan otomatis';
                    this.saveStatusTime = data.saved_at || new Date().toLocaleTimeString();
                }
            } catch (err) {
                console.error(err);
                this.saveStatus = 'Gagal menyimpan';
            } finally {
                this.saving = false;
            }
        },

        countPgAnswered() {
            return Object.keys(this.jawabanPg).filter(k => this.jawabanPg[k] && this.jawabanPg[k].toString().trim() !== '').length;
        },

        countEsaiAnswered() {
            return Object.values(this.jawabanEsai).filter(v => v && v.trim() !== '').length;
        }
     }">

    <!-- =========================================================================
         1. STICKY TOP APP BAR (HEADER KONTROL, TIMER & AUTOSAVE STATUS)
         ========================================================================= -->
    <header class="sticky top-0 z-40 bg-white/95 backdrop-blur-md border-b border-slate-200/80 shadow-xs">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 py-2.5 sm:py-3">
            <div class="flex items-center justify-between gap-3">
                
                <!-- Left: Skema & Status Asesmen -->
                <div class="flex items-center gap-3 min-w-0">
                    <a href="{{ route('asesi.dashboard') }}" 
                       title="Kembali ke Dasbor Asesi"
                       class="hidden sm:inline-flex items-center justify-center w-8 h-8 rounded-lg bg-slate-100 hover:bg-slate-200 text-slate-700 text-xs font-bold transition-colors">
                        ←
                    </a>
                    <div class="min-w-0">
                        <div class="flex items-center gap-2 flex-wrap">
                            <span class="inline-flex items-center gap-1.5 px-2 py-0.5 rounded-md text-[10px] font-extrabold uppercase tracking-wide
                                @if(($statusSesi['status'] ?? '') === 'belum_mulai')
                                    bg-amber-50 text-amber-700 border border-amber-200
                                @elseif(($statusSesi['status'] ?? '') === 'selesai' || $isReadonly)
                                    bg-slate-100 text-slate-700 border border-slate-300
                                @else
                                    bg-emerald-50 text-emerald-700 border border-emerald-200
                                @endif">
                                @if(($statusSesi['status'] ?? '') === 'aktif' && !$isReadonly)
                                    <span class="relative flex h-2 w-2">
                                        <span class="animate-ping absolute inline-flex h-full w-full rounded-full bg-emerald-400 opacity-75"></span>
                                        <span class="relative inline-flex rounded-full h-2 w-2 bg-emerald-500"></span>
                                    </span>
                                    Sesi Ujian Aktif
                                @elseif(($statusSesi['status'] ?? '') === 'belum_mulai')
                                    Menunggu Jadwal Mulai
                                @else
                                    Ujian Selesai / Ditutup
                                @endif
                            </span>

                            <span class="text-xs text-slate-400 font-mono hidden md:inline">
                                {{ $pendaftaran->skema->kode_skema }}
                            </span>
                        </div>

                        <h1 class="text-sm sm:text-base font-bold text-slate-900 truncate leading-snug">
                            {{ $pendaftaran->skema->nama_skema }}
                        </h1>
                    </div>
                </div>

                <!-- Right: Live Timer, Autosave Indicator & Utilities -->
                <div class="flex items-center gap-2 sm:gap-4 shrink-0">
                    
                    <!-- Autosave Indicator -->
                    @if(!$isReadonly && ($statusSesi['status'] ?? '') === 'aktif')
                        <div class="hidden lg:flex items-center gap-2 px-3 py-1.5 bg-slate-50 border border-slate-200/80 rounded-xl text-xs">
                            <span class="w-2 h-2 rounded-full"
                                  :class="saving ? 'bg-amber-400 animate-ping' : 'bg-emerald-500'"></span>
                            <span class="text-slate-600 text-[11px] font-medium" x-text="saveStatus"></span>
                            <span class="text-[10px] text-slate-400 font-mono" x-show="!saving" x-text="'(' + saveStatusTime + ')'"></span>
                        </div>
                    @endif

                    <!-- Sisa Waktu Ujian / Countdown -->
                    @if(($statusSesi['status'] ?? '') === 'belum_mulai')
                        <div class="flex items-center gap-2 px-3 py-1.5 bg-amber-50 border border-amber-200 rounded-xl">
                            <div class="text-right">
                                <span class="text-[9px] font-bold uppercase text-amber-700 block tracking-wider leading-tight">Dimulai Dalam</span>
                                <span class="text-sm sm:text-base font-bold font-mono text-amber-800 timer-badge leading-tight" x-text="countdownMulaiDisplay">
                                    00:00:00
                                </span>
                            </div>
                        </div>
                    @elseif(!$isReadonly && ($statusSesi['status'] ?? '') === 'aktif')
                        <div class="flex items-center gap-2.5 px-3 py-1.5 rounded-xl border transition-colors"
                             :class="timerSeconds <= 300 
                                ? 'bg-rose-50 border-rose-300 text-rose-800 animate-pulse' 
                                : (timerSeconds <= 900 
                                    ? 'bg-amber-50 border-amber-300 text-amber-800' 
                                    : 'bg-blue-50/80 border-blue-200 text-blue-900')">
                            <div class="text-right">
                                <span class="text-[9px] font-bold uppercase tracking-wider block opacity-75 leading-tight">Sisa Waktu</span>
                                <span class="text-sm sm:text-base font-black font-mono timer-badge leading-tight" x-text="timerDisplay">
                                    00:00:00
                                </span>
                            </div>
                        </div>
                    @else
                        <span class="px-2.5 py-1 rounded-xl text-[11px] font-bold border {{ $isSubmitted ? 'bg-emerald-50 text-emerald-800 border-emerald-200' : 'bg-slate-100 text-slate-700 border-slate-300' }}">
                            {{ $isSubmitted ? 'Terkumpul' : 'Selesai' }}
                        </span>
                    @endif

                    <!-- Mobile Drawer Toggle Button (Soal Palette) -->
                    @if($pendaftaran->skema->hasInstrumen('FR.IA.05'))
                        <button type="button" 
                                @click="showMobilePalette = true"
                                x-show="activeTab === 'cbt'"
                                class="inline-flex lg:hidden items-center gap-1.5 px-2.5 py-1.5 bg-slate-100 hover:bg-slate-200 text-slate-800 border border-slate-200 rounded-xl text-xs font-bold transition-colors">
                            <span>No:</span>
                            <span class="text-blue-600" x-text="currentPgNo"></span>
                            <span class="text-slate-400">/{{ $totalSoalCbt }}</span>
                        </button>
                    @endif

                    <!-- Button Selesaikan Ujian (Top Desktop) -->
                    @if(!$isReadonly && ($statusSesi['status'] ?? '') === 'aktif')
                        <button type="button" 
                                @click="showSubmitModal = true"
                                class="hidden sm:inline-flex items-center px-4 py-2 bg-emerald-600 hover:bg-emerald-700 text-white text-xs font-bold rounded-xl shadow-xs transition-colors">
                            Selesaikan Ujian
                        </button>
                    @endif
                </div>

            </div>
        </div>
    </header>

    <!-- =========================================================================
         2. MAIN CONTAINER
         ========================================================================= -->
    <main class="max-w-7xl mx-auto px-4 sm:px-6 pt-5 space-y-6">

        <!-- Banner Info Asesor & Pelaksanaan -->
        <div class="bg-white rounded-2xl border border-slate-200/80 p-4 sm:p-5 shadow-2xs">
            <div class="flex flex-col md:flex-row md:items-center justify-between gap-4">
                <div class="grid grid-cols-2 sm:grid-cols-4 gap-3 sm:gap-6 text-xs text-slate-600">
                    <div>
                        <span class="block text-[10px] uppercase font-bold text-slate-400 tracking-wider">Asesi Peserta</span>
                        <strong class="text-slate-800 font-semibold truncate block">{{ $pendaftaran->asesi->nama_lengkap ?? auth()->user()->nama_lengkap }}</strong>
                    </div>
                    <div>
                        <span class="block text-[10px] uppercase font-bold text-slate-400 tracking-wider">Asesor Penguji</span>
                        <strong class="text-slate-800 font-semibold truncate block">{{ $pendaftaran->asesor->nama_lengkap ?? 'Asesor Penguji' }}</strong>
                    </div>
                    <div>
                        <span class="block text-[10px] uppercase font-bold text-slate-400 tracking-wider">Lokasi / TUK</span>
                        <strong class="text-slate-800 font-semibold truncate block">{{ $pendaftaran->jadwal->nama_tuk ?? 'TUK Sewaktu' }}</strong>
                    </div>
                    <div>
                        <span class="block text-[10px] uppercase font-bold text-slate-400 tracking-wider">Waktu Pelaksanaan</span>
                        <strong class="text-slate-800 font-semibold block">
                            {{ $pendaftaran->jadwal?->time_status['formatted_mulai'] }} - {{ $pendaftaran->jadwal?->time_status['formatted_selesai'] }} WIB
                        </strong>
                    </div>
                </div>

                @if($isSubmitted)
                    <div class="px-3.5 py-2 bg-emerald-50 border border-emerald-200 text-emerald-800 rounded-xl text-xs font-bold flex items-center gap-2 shrink-0">
                        <span>✓</span>
                        <span>Lembar Ujian Telah Dikumpulkan</span>
                    </div>
                @endif
            </div>
        </div>

        <!-- =========================================================================
             WAITING ROOM: JIKA JADWAL BELUM MULAI
             ========================================================================= -->
        @if(($statusSesi['status'] ?? '') === 'belum_mulai')
            <div class="bg-gradient-to-br from-amber-50 via-orange-50/70 to-amber-50 border-2 border-amber-300/80 rounded-3xl p-8 sm:p-12 text-center space-y-6 shadow-xs max-w-3xl mx-auto">
                <div class="w-16 h-16 bg-amber-100 text-amber-800 font-black rounded-2xl flex items-center justify-center mx-auto text-sm tracking-wider shadow-inner border border-amber-200">
                    STANDBY
                </div>
                
                <div class="space-y-3">
                    <span class="inline-flex items-center px-3 py-1 bg-amber-100/80 text-amber-800 text-xs font-bold rounded-full">
                        RUANG UJIAN TERKUNCI SEMENTARA
                    </span>
                    <h2 class="text-2xl sm:text-3xl font-black text-slate-900 tracking-tight">Sesi Ujian Belum Dimulai</h2>
                    <p class="text-sm text-slate-600 max-w-xl mx-auto leading-relaxed">
                        Sesuai jadwal resmi LSP, ruang ujian untuk skema <strong>{{ $pendaftaran->skema->nama_skema }}</strong> akan dibuka otomatis pada:
                    </p>
                    
                    <div class="bg-white/90 backdrop-blur-xs border border-amber-200 rounded-2xl p-4 sm:p-5 max-w-md mx-auto text-left shadow-2xs space-y-1.5">
                        <div class="text-xs text-slate-500 font-medium">Jadwal Pelaksanaan Asesmen:</div>
                        <div class="text-base font-bold text-slate-900">
                            {{ $pendaftaran->jadwal ? \Carbon\Carbon::parse($pendaftaran->jadwal->tanggal_uji)->translatedFormat('l, d F Y') : '-' }}
                        </div>
                        <div class="text-xs font-semibold text-slate-700">
                            Pukul {{ $pendaftaran->jadwal?->time_status['formatted_mulai'] }} - {{ $pendaftaran->jadwal?->time_status['formatted_selesai'] }} WIB
                        </div>
                    </div>
                    
                    <div class="pt-6 border-t border-amber-200/70 max-w-sm mx-auto">
                        <span class="text-[11px] font-bold uppercase tracking-widest text-amber-800 block mb-1">Soal Terbuka Otomatis Dalam:</span>
                        <div class="text-4xl sm:text-5xl font-black font-mono text-amber-600 tracking-wider py-2" x-text="countdownMulaiDisplay">
                            00:00:00
                        </div>
                        <p class="text-xs text-amber-700 font-medium bg-amber-100/60 px-3.5 py-1.5 rounded-xl inline-block mt-2">
                            Halaman akan terbuka otomatis tanpa perlu me-refresh browser.
                        </p>
                    </div>
                </div>
            </div>

        @elseif(!empty($statusSesi) && !$statusSesi['bisa_akses'])
            <div class="bg-amber-50 border border-amber-200 rounded-2xl p-6 shadow-xs max-w-2xl mx-auto text-center space-y-3">
                <div class="w-12 h-12 bg-amber-100 text-amber-800 rounded-2xl font-bold text-xs flex items-center justify-center mx-auto">
                    INFO
                </div>
                <h3 class="text-base font-bold text-amber-950">Ruang Ujian Belum Dapat Diakses</h3>
                <p class="text-amber-800 text-xs sm:text-sm leading-relaxed max-w-lg mx-auto">
                    {{ $statusSesi['pesan'] ?? 'Rencana asesmen (FR.MAPA.01 & FR.MAPA.02) belum disahkan asesor. Ruang ujian belum tersedia.' }}
                </p>
                <div class="pt-2">
                    <a href="{{ route('asesi.dashboard') }}" class="inline-block px-5 py-2.5 bg-amber-600 hover:bg-amber-700 text-white rounded-xl text-xs font-bold shadow-xs">
                        Kembali ke Dasbor Asesi
                    </a>
                </div>
            </div>
        @endif

        @if($statusSesi['bisa_akses'] ?? false)

        <!-- =========================================================================
             3. SEGMENTED TAB SWITCHER (FR.IA.05, FR.IA.06, FR.IA.02, FR.IA.04A)
             ========================================================================= -->
        @php
            $showCbt = $pendaftaran->skema->hasInstrumen('FR.IA.05') && $pendaftaran->isInstrumenAktif('FR.IA.05');
            $showEsai = $pendaftaran->skema->hasInstrumen('FR.IA.06') && $pendaftaran->isInstrumenAktif('FR.IA.06');
            $showPraktik = $pendaftaran->skema->hasInstrumen('FR.IA.02') && $pendaftaran->isInstrumenAktif('FR.IA.02');
            $showProyek = $pendaftaran->skema->hasInstrumen('FR.IA.04A') && $pendaftaran->isInstrumenAktif('FR.IA.04A');

            // Fallback jika belum terkonfigurasi di AK-01: tampilkan instrumen yang tersedia di skema
            if (!$showCbt && !$showEsai && !$showPraktik && !$showProyek) {
                $showCbt = $pendaftaran->skema->hasInstrumen('FR.IA.05');
                $showEsai = $pendaftaran->skema->hasInstrumen('FR.IA.06');
                $showPraktik = $pendaftaran->skema->hasInstrumen('FR.IA.02');
                $showProyek = $pendaftaran->skema->hasInstrumen('FR.IA.04A');
            }
        @endphp
        <div class="bg-white rounded-2xl border border-slate-200/80 p-1.5 shadow-2xs flex flex-wrap gap-1.5">
            @if($showCbt)
                <button type="button" 
                        @click="activeTab = 'cbt'"
                        :class="activeTab === 'cbt' 
                            ? 'bg-blue-600 text-white shadow-xs font-bold' 
                            : 'text-slate-600 hover:bg-slate-100 font-medium'"
                        class="flex-1 min-w-[200px] py-2.5 px-4 rounded-xl text-xs sm:text-sm transition-all flex items-center justify-center gap-2">
                    <span>FR.IA.05 (Ujian Teori CBT PG)</span>
                    <span class="text-[11px] px-2 py-0.5 rounded-full"
                          :class="activeTab === 'cbt' 
                            ? (countPgAnswered() === {{ $totalSoalCbt }} ? 'bg-emerald-400 text-emerald-950 font-black' : 'bg-blue-500/80 text-white') 
                            : (countPgAnswered() === {{ $totalSoalCbt }} ? 'bg-emerald-100 text-emerald-800 font-bold' : 'bg-slate-100 text-slate-600')">
                        <span x-text="countPgAnswered()"></span>/{{ $totalSoalCbt }}
                    </span>
                </button>
            @endif

            @if($showEsai)
                <button type="button" 
                        @click="activeTab = 'esai'"
                        :class="activeTab === 'esai' 
                            ? 'bg-blue-600 text-white shadow-xs font-bold' 
                            : 'text-slate-600 hover:bg-slate-100 font-medium'"
                        class="flex-1 min-w-[200px] py-2.5 px-4 rounded-xl text-xs sm:text-sm transition-all flex items-center justify-center gap-2">
                    <span>FR.IA.06 (Ujian Tertulis Esai)</span>
                    <span class="text-[11px] px-2 py-0.5 rounded-full"
                          :class="activeTab === 'esai' 
                            ? (countEsaiAnswered() === {{ $totalSoalEsai }} ? 'bg-emerald-400 text-emerald-950 font-black' : 'bg-blue-500/80 text-white') 
                            : (countEsaiAnswered() === {{ $totalSoalEsai }} ? 'bg-emerald-100 text-emerald-800 font-bold' : 'bg-slate-100 text-slate-600')">
                        <span x-text="countEsaiAnswered()"></span>/{{ $totalSoalEsai }}
                    </span>
                </button>
            @endif

            @if($showPraktik)
                <button type="button" 
                        @click="activeTab = 'praktik'"
                        :class="activeTab === 'praktik' 
                            ? 'bg-blue-600 text-white shadow-xs font-bold' 
                            : 'text-slate-600 hover:bg-slate-100 font-medium'"
                        class="flex-1 min-w-[200px] py-2.5 px-4 rounded-xl text-xs sm:text-sm transition-all flex items-center justify-center gap-2">
                    <span>FR.IA.02 (Tugas Praktik Demonstrasi)</span>
                    @if($dokumenPraktik)
                        <span class="text-[10px] px-2 py-0.5 rounded-full font-bold"
                              :class="activeTab === 'praktik' ? 'bg-emerald-400 text-emerald-950' : 'bg-emerald-100 text-emerald-800'">
                            Terkumpul
                        </span>
                    @endif
                </button>
            @endif

            @if($showProyek)
                <button type="button" 
                        @click="activeTab = 'proyek'"
                        :class="activeTab === 'proyek' 
                            ? 'bg-blue-600 text-white shadow-xs font-bold' 
                            : 'text-slate-600 hover:bg-slate-100 font-medium'"
                        class="flex-1 min-w-[180px] py-2.5 px-4 rounded-xl text-xs sm:text-sm transition-all flex items-center justify-center gap-2">
                    <span>FR.IA.04A (Proyek / TOR)</span>
                </button>
            @endif
        </div>

        <!-- =========================================================================
             TAB 1: FR.IA.05 UJIAN TEORI CBT (PILIHAN GANDA)
             ========================================================================= -->
        @if($pendaftaran->skema->hasInstrumen('FR.IA.05'))
            <div x-show="activeTab === 'cbt'" class="space-y-6">
                
                <!-- CBT Sub-Bar: Mode Pengerjaan & Font Size Controls -->
                <div class="flex flex-wrap items-center justify-between gap-3 bg-white px-4 py-2.5 rounded-xl border border-slate-200/80 shadow-2xs">
                    <div class="flex items-center gap-2">
                        <span class="text-xs font-bold text-slate-500 uppercase tracking-wider">Mode Tampilan:</span>
                        <div class="inline-flex rounded-lg bg-slate-100 p-0.5 border border-slate-200">
                            <button type="button" 
                                    @click="viewMode = 'fokus'"
                                    :class="viewMode === 'fokus' ? 'bg-white text-blue-700 shadow-2xs font-bold' : 'text-slate-600 hover:text-slate-900 font-medium'"
                                    class="px-3 py-1 rounded-md text-xs transition-all">
                                Mode Fokus (Per Soal)
                            </button>
                            <button type="button" 
                                    @click="viewMode = 'daftar'"
                                    :class="viewMode === 'daftar' ? 'bg-white text-blue-700 shadow-2xs font-bold' : 'text-slate-600 hover:text-slate-900 font-medium'"
                                    class="px-3 py-1 rounded-md text-xs transition-all">
                                Mode Semua Soal (Daftar)
                            </button>
                        </div>
                    </div>

                    <div class="flex items-center gap-2">
                        <span class="text-xs font-bold text-slate-500 uppercase tracking-wider">Ukuran Teks:</span>
                        <div class="inline-flex rounded-lg bg-slate-100 p-0.5 border border-slate-200">
                            <button type="button" 
                                    @click="fontSize = 'kecil'"
                                    :class="fontSize === 'kecil' ? 'bg-white text-blue-700 shadow-2xs font-bold' : 'text-slate-600 hover:text-slate-900 font-medium'"
                                    class="px-2.5 py-1 rounded-md text-xs transition-all">
                                A-
                            </button>
                            <button type="button" 
                                    @click="fontSize = 'normal'"
                                    :class="fontSize === 'normal' ? 'bg-white text-blue-700 shadow-2xs font-bold' : 'text-slate-600 hover:text-slate-900 font-medium'"
                                    class="px-2.5 py-1 rounded-md text-xs transition-all">
                                A
                            </button>
                            <button type="button" 
                                    @click="fontSize = 'besar'"
                                    :class="fontSize === 'besar' ? 'bg-white text-blue-700 shadow-2xs font-bold' : 'text-slate-600 hover:text-slate-900 font-medium'"
                                    class="px-2.5 py-1 rounded-md text-xs transition-all">
                                A+
                            </button>
                        </div>
                    </div>
                </div>

                <!-- 2-Column Responsive Layout: Left Questions Area (8 col) / Right Navigation Sidebar (4 col) -->
                <div class="grid grid-cols-1 lg:grid-cols-12 gap-6 items-start">
                    
                    <!-- ==========================================
                         LEFT COLUMN: CBT QUESTIONS
                         ========================================== -->
                    <div class="lg:col-span-8 space-y-5">
                        
                        <!-- ---------------------------------------
                             VIEW 1: MODE FOKUS (PER SOAL)
                             --------------------------------------- -->
                        <template x-if="viewMode === 'fokus'">
                            <div class="space-y-4">
                                @foreach($soalCbt as $no => $item)
                                    <div x-show="currentPgNo === {{ $no }}" 
                                         class="bg-white rounded-2xl border border-slate-200/80 shadow-xs p-6 sm:p-8 space-y-6">
                                        
                                        <!-- Card Top Header -->
                                        <div class="flex flex-wrap items-center justify-between gap-3 border-b border-slate-100 pb-4">
                                            <div class="flex items-center gap-3">
                                                <span class="px-3 py-1 bg-blue-600 text-white text-xs font-bold rounded-xl shadow-2xs">
                                                    Soal No. {{ $no }} dari {{ $totalSoalCbt }}
                                                </span>
                                                <span class="text-xs text-slate-500 font-medium hidden sm:inline truncate max-w-sm">
                                                    {{ $item['kuk'] ?? 'Standar Kompetensi Kejuruan' }}
                                                </span>
                                            </div>

                                            <div class="flex items-center gap-2">
                                                <!-- Status Terjawab Badge -->
                                                <template x-if="jawabanPg[{{ $no }}]">
                                                    <span class="px-2.5 py-1 bg-emerald-50 text-emerald-700 border border-emerald-200 text-xs font-bold rounded-xl flex items-center gap-1">
                                                        <span>✓ Terjawab:</span>
                                                        <span x-text="jawabanPg[{{ $no }}]"></span>
                                                    </span>
                                                </template>
                                                <template x-if="!jawabanPg[{{ $no }}]">
                                                    <span class="px-2.5 py-1 bg-slate-100 text-slate-500 border border-slate-200 text-xs font-semibold rounded-xl">
                                                        Belum Dijawab
                                                    </span>
                                                </template>
                                            </div>
                                        </div>

                                        <!-- Question Statement -->
                                        <div class="text-slate-800 leading-relaxed font-semibold"
                                             :class="{
                                                 'text-sm': fontSize === 'kecil',
                                                 'text-base sm:text-lg': fontSize === 'normal',
                                                 'text-lg sm:text-xl': fontSize === 'besar'
                                             }">
                                            {{ $item['pertanyaan'] }}
                                        </div>

                                        @if(!empty($item['gambar']))
                                            <div class="pt-2">
                                                <img src="{{ asset($item['gambar']) }}" alt="Lampiran Soal No {{ $no }}" class="max-w-lg w-full rounded-2xl border border-slate-200 shadow-2xs">
                                            </div>
                                        @endif

                                        <!-- Option Cards (A, B, C, D, E) -->
                                        <div class="space-y-3 pt-2">
                                            @foreach($item['opsi'] as $huruf => $teksOpsi)
                                                <div @click="savePg({{ $no }}, '{{ $huruf }}')"
                                                     :class="jawabanPg[{{ $no }}] === '{{ $huruf }}'
                                                        ? 'border-blue-600 bg-blue-50/70 shadow-xs ring-2 ring-blue-500/20'
                                                        : 'border-slate-200/90 bg-white hover:bg-slate-50/80 hover:border-slate-300'"
                                                     class="p-3.5 sm:p-4 rounded-2xl border cursor-pointer flex items-start gap-3.5 transition-all select-none">
                                                    
                                                    <!-- Letter Badge -->
                                                    <span class="w-7 h-7 rounded-xl font-bold text-xs flex items-center justify-center shrink-0 mt-0.5 transition-colors"
                                                          :class="jawabanPg[{{ $no }}] === '{{ $huruf }}' 
                                                            ? 'bg-blue-600 text-white font-black' 
                                                            : 'bg-slate-100 text-slate-700 border border-slate-200'">
                                                        {{ $huruf }}
                                                    </span>

                                                    <!-- Option Text -->
                                                    <div class="flex-1 leading-relaxed text-slate-800"
                                                         :class="{
                                                             'text-xs sm:text-sm': fontSize === 'kecil',
                                                             'text-sm sm:text-base': fontSize === 'normal',
                                                             'text-base sm:text-lg': fontSize === 'besar'
                                                         }">
                                                        {{ $teksOpsi }}
                                                    </div>

                                                    <!-- Check Indicator -->
                                                    <span x-show="jawabanPg[{{ $no }}] === '{{ $huruf }}'" 
                                                          class="text-blue-600 font-black text-sm shrink-0">
                                                        ✓
                                                    </span>
                                                </div>
                                            @endforeach
                                        </div>

                                        <!-- Question Card Navigation Footer -->
                                        <div class="pt-6 border-t border-slate-100 flex items-center justify-between gap-3">
                                            <button type="button" 
                                                    @click="prevPg()"
                                                    :disabled="isFirstPg()"
                                                    :class="isFirstPg() ? 'opacity-40 cursor-not-allowed bg-slate-100 text-slate-400' : 'bg-slate-100 hover:bg-slate-200 text-slate-700'"
                                                    class="px-4 sm:px-5 py-2.5 rounded-xl text-xs sm:text-sm font-bold transition-colors flex items-center gap-1.5">
                                                <span>←</span>
                                                <span>Sebelumnya</span>
                                            </button>

                                            <!-- Center Indicator -->
                                            <span class="text-xs text-slate-500 font-medium">
                                                <span x-text="cbtKeys.indexOf(currentPgNo) + 1"></span> dari {{ $totalSoalCbt }}
                                            </span>

                                            <template x-if="!isLastPg()">
                                                <button type="button" 
                                                        @click="nextPg()"
                                                        class="px-4 sm:px-6 py-2.5 bg-blue-600 hover:bg-blue-700 text-white rounded-xl text-xs sm:text-sm font-bold shadow-xs transition-colors flex items-center gap-1.5">
                                                    <span>Selanjutnya</span>
                                                    <span>→</span>
                                                </button>
                                            </template>

                                            <template x-if="isLastPg() && !isReadonly">
                                                <button type="button" 
                                                        @click="showSubmitModal = true"
                                                        class="px-5 py-2.5 bg-emerald-600 hover:bg-emerald-700 text-white rounded-xl text-xs sm:text-sm font-bold shadow-xs transition-colors flex items-center gap-1.5">
                                                    <span>Selesaikan Ujian</span>
                                                    <span>✓</span>
                                                </button>
                                            </template>
                                        </div>

                                    </div>
                                @endforeach
                            </div>
                        </template>

                        <!-- ---------------------------------------
                             VIEW 2: MODE DAFTAR (SEMUA SOAL)
                             --------------------------------------- -->
                        <template x-if="viewMode === 'daftar'">
                            <div class="space-y-6">
                                @foreach($soalCbt as $no => $item)
                                    <div id="soal_pg_{{ $no }}" 
                                         class="bg-white rounded-2xl border border-slate-200/80 shadow-xs p-6 sm:p-7 space-y-4">
                                        
                                        <div class="flex items-center justify-between gap-3 border-b border-slate-100 pb-3">
                                            <div class="flex items-center gap-2.5">
                                                <span class="px-2.5 py-1 bg-blue-600 text-white text-xs font-bold rounded-lg">
                                                    {{ $no }}
                                                </span>
                                                <span class="text-xs font-semibold text-slate-500 truncate max-w-sm">
                                                    {{ $item['kuk'] ?? 'Standar Kompetensi Kejuruan' }}
                                                </span>
                                            </div>

                                            <div class="flex items-center gap-2">
                                                <template x-if="jawabanPg[{{ $no }}]">
                                                    <span class="px-2 py-0.5 bg-emerald-50 text-emerald-700 border border-emerald-200 text-xs font-bold rounded-md flex items-center gap-1">
                                                        <span>✓ Terjawab:</span>
                                                        <span x-text="jawabanPg[{{ $no }}]"></span>
                                                    </span>
                                                </template>
                                                <template x-if="!jawabanPg[{{ $no }}]">
                                                    <span class="px-2 py-0.5 bg-slate-100 text-slate-500 border border-slate-200 text-xs font-semibold rounded-md">
                                                        Belum Dijawab
                                                    </span>
                                                </template>
                                            </div>
                                        </div>

                                        <div class="text-slate-800 leading-relaxed font-semibold text-sm sm:text-base">
                                            {{ $item['pertanyaan'] }}
                                        </div>

                                        @if(!empty($item['gambar']))
                                            <div class="pt-1">
                                                <img src="{{ asset($item['gambar']) }}" alt="Gambar Soal No {{ $no }}" class="max-w-md w-full rounded-xl border border-slate-200">
                                            </div>
                                        @endif

                                        <div class="space-y-2.5 pt-1">
                                            @foreach($item['opsi'] as $huruf => $teksOpsi)
                                                <div @click="savePg({{ $no }}, '{{ $huruf }}')"
                                                     :class="jawabanPg[{{ $no }}] === '{{ $huruf }}'
                                                        ? 'border-blue-600 bg-blue-50/70 shadow-2xs ring-1 ring-blue-500/20'
                                                        : 'border-slate-200 bg-white hover:bg-slate-50'"
                                                     class="p-3 sm:p-3.5 rounded-xl border cursor-pointer flex items-start gap-3 text-xs sm:text-sm text-slate-700 transition-all select-none">
                                                    
                                                    <span class="w-6 h-6 rounded-lg font-bold text-xs flex items-center justify-center shrink-0 mt-0.5"
                                                          :class="jawabanPg[{{ $no }}] === '{{ $huruf }}' 
                                                            ? 'bg-blue-600 text-white font-black' 
                                                            : 'bg-slate-100 text-slate-700 border border-slate-200'">
                                                        {{ $huruf }}
                                                    </span>

                                                    <span class="flex-1 leading-relaxed">{{ $teksOpsi }}</span>
                                                    
                                                    <span x-show="jawabanPg[{{ $no }}] === '{{ $huruf }}'" class="text-blue-600 font-bold text-xs shrink-0">
                                                        ✓
                                                    </span>
                                                </div>
                                            @endforeach
                                        </div>
                                    </div>
                                @endforeach
                            </div>
                        </template>

                    </div>

                    <!-- ==========================================
                         RIGHT COLUMN: STICKY QUESTION NAVIGATOR (DESKTOP)
                         ========================================== -->
                    <aside class="hidden lg:block lg:col-span-4 sticky top-20 space-y-4">
                        <div class="bg-white rounded-2xl border border-slate-200/80 p-5 shadow-xs space-y-4">
                            
                            <div class="flex items-center justify-between border-b border-slate-100 pb-3">
                                <div>
                                    <h3 class="text-xs font-bold uppercase tracking-wider text-slate-800">Lembar Nomor Soal</h3>
                                    <p class="text-[11px] text-slate-400 mt-0.5">Klik nomor untuk membuka soal</p>
                                </div>
                                <span class="text-xs font-extrabold text-blue-700 bg-blue-50 border border-blue-200 px-2.5 py-0.5 rounded-lg">
                                    <span x-text="countPgAnswered()"></span>/{{ $totalSoalCbt }}
                                </span>
                            </div>

                            <!-- Summary Progress Stats -->
                            <div class="grid grid-cols-2 gap-2 text-center">
                                <div class="bg-emerald-50 border border-emerald-200 rounded-xl p-2.5">
                                    <span class="text-xs font-black text-emerald-800 block" x-text="countPgAnswered()"></span>
                                    <span class="text-[10px] font-bold text-emerald-700 uppercase">Dijawab</span>
                                </div>
                                <div class="bg-slate-50 border border-slate-200 rounded-xl p-2.5">
                                    <span class="text-xs font-black text-slate-700 block" x-text="{{ $totalSoalCbt }} - countPgAnswered()"></span>
                                    <span class="text-[10px] font-bold text-slate-500 uppercase">Belum</span>
                                </div>
                            </div>

                            <!-- Progress Bar -->
                            <div class="space-y-1">
                                <div class="w-full h-2 bg-slate-100 rounded-full overflow-hidden">
                                    <div class="h-full bg-emerald-500 transition-all duration-300"
                                         :style="'width: ' + ((countPgAnswered() / {{ $totalSoalCbt ?: 1 }}) * 100) + '%'"></div>
                                </div>
                            </div>

                            <!-- Legend -->
                            <div class="flex items-center justify-around text-[11px] text-slate-500 pt-1 border-t border-slate-100">
                                <div class="flex items-center gap-1.5">
                                    <span class="w-3 h-3 rounded-md bg-emerald-600 inline-block"></span>
                                    <span>Sudah</span>
                                </div>
                                <div class="flex items-center gap-1.5">
                                    <span class="w-3 h-3 rounded-md bg-white border-2 border-blue-600 inline-block"></span>
                                    <span>Aktif</span>
                                </div>
                                <div class="flex items-center gap-1.5">
                                    <span class="w-3 h-3 rounded-md bg-slate-100 border border-slate-300 inline-block"></span>
                                    <span>Belum</span>
                                </div>
                            </div>

                            <!-- Grid of Question Numbers -->
                            <div class="grid grid-cols-5 gap-2 max-h-[380px] overflow-y-auto pr-1">
                                @foreach($soalCbt as $no => $item)
                                    <button type="button" 
                                            @click="goToPg({{ $no }})"
                                            class="h-10 rounded-xl text-xs font-bold flex items-center justify-center transition-all select-none"
                                            :class="{
                                                'ring-2 ring-blue-600 ring-offset-2 scale-105 z-10': currentPgNo === {{ $no }},
                                                'bg-emerald-600 text-white border border-emerald-600 shadow-2xs': jawabanPg[{{ $no }}],
                                                'bg-slate-50 text-slate-700 border border-slate-200 hover:bg-slate-100': !jawabanPg[{{ $no }}]
                                            }">
                                        {{ $no }}
                                    </button>
                                @endforeach
                            </div>

                            <!-- Sidebar Action Button -->
                            @if(!$isReadonly && ($statusSesi['status'] ?? '') === 'aktif')
                                <div class="pt-2 border-t border-slate-100">
                                    <button type="button" 
                                            @click="showSubmitModal = true"
                                            class="w-full py-3 bg-emerald-600 hover:bg-emerald-700 text-white font-bold text-xs rounded-xl shadow-xs transition-colors flex items-center justify-center gap-2">
                                        <span>Selesaikan Ujian</span>
                                        <span>✓</span>
                                    </button>
                                </div>
                            @endif

                        </div>
                    </aside>

                </div>

            </div>
        @endif

        <!-- =========================================================================
             TAB 2: FR.IA.06 UJIAN TERTULIS ESAI
             ========================================================================= -->
        @if($pendaftaran->skema->hasInstrumen('FR.IA.06'))
            <div x-show="activeTab === 'esai'" class="space-y-6">
                
                <!-- Petunjuk Pengerjaan Esai -->
                <div class="bg-blue-50/80 border border-blue-200 rounded-2xl p-5 text-xs sm:text-sm text-blue-950 space-y-1.5 shadow-2xs">
                    <div class="font-bold flex items-center gap-2">
                        <span>Petunjuk Pengerjaan Ujian Esai:</span>
                    </div>
                    <p class="text-blue-800 leading-relaxed">
                        Jawablah setiap butir pertanyaan studi kasus di bawah ini dengan jelas, runtut, dan menguraikan prosedur operasional standar (SOP) sesuai bidang keahlian Anda. Jawaban Anda tersimpan otomatis saat Anda mengetik.
                    </p>
                </div>

                <div class="space-y-5">
                    @foreach($soalEsai as $no => $item)
                        <div class="bg-white rounded-2xl border border-slate-200/80 p-6 sm:p-8 shadow-xs space-y-4">
                            <div class="flex items-center justify-between border-b border-slate-100 pb-3">
                                <div class="flex items-center gap-2.5">
                                    <span class="w-7 h-7 rounded-xl bg-blue-600 text-white text-xs font-bold flex items-center justify-center shrink-0">
                                        {{ $no }}
                                    </span>
                                    <span class="text-xs font-semibold text-slate-500">{{ $item['kuk'] ?? 'Kriteria Unjuk Kerja Esai' }}</span>
                                </div>
                                <span class="text-[11px] font-bold text-slate-400 font-mono">FR.IA.06 &bull; Soal {{ $no }}</span>
                            </div>

                            <div class="text-slate-800 text-sm sm:text-base leading-relaxed font-semibold">
                                {{ $item['pertanyaan'] }}
                            </div>

                            <div class="space-y-2 pt-1">
                                <div class="flex items-center justify-between text-xs font-bold text-slate-700 uppercase tracking-wider">
                                    <span>Lembar Jawaban Asesi:</span>
                                    <span class="text-[11px] font-normal lowercase text-slate-400">Otomatis tersimpan</span>
                                </div>
                                
                                <textarea rows="6" 
                                          :disabled="isReadonly"
                                          placeholder="Tuliskan uraian jawaban teknis Anda di sini secara rinci dan terstruktur..."
                                          @input.debounce.700ms="saveEsai({{ $no }}, $event.target.value)"
                                          class="w-full bg-slate-50/50 hover:bg-white focus:bg-white border border-slate-200 focus:border-blue-500 focus:ring-3 focus:ring-blue-500/20 rounded-xl p-4 text-sm text-slate-800 outline-hidden leading-relaxed transition-all">{{ $savedJawabanEsai[$no] ?? '' }}</textarea>
                            </div>
                        </div>
                    @endforeach
                </div>

            </div>
        @endif

        <!-- =========================================================================
             TAB 3: FR.IA.02 TUGAS PRAKTIK DEMONSTRASI
             ========================================================================= -->
        @if($pendaftaran->skema->hasInstrumen('FR.IA.02'))
            <div x-show="activeTab === 'praktik'" class="space-y-6">
                <div class="bg-white rounded-2xl border border-slate-200/80 p-6 sm:p-8 shadow-xs space-y-6">
                    
                    <div class="border-b border-slate-200/80 pb-4">
                        <div class="text-blue-600 font-bold text-xs uppercase tracking-wider">
                            FR.IA.02 &bull; Lembar Penugasan Praktik Demonstrasi
                        </div>
                        <h2 class="text-xl font-bold text-slate-900 mt-1">{{ $panduanPraktik['judul_tugas'] ?? 'Tugas Praktik Demonstrasi' }}</h2>
                        <p class="text-slate-500 text-xs mt-1">Alokasi Waktu Maksimal: <strong>{{ $panduanPraktik['waktu_menit'] ?? 120 }} Menit</strong> di Bengkel/Lab Tempat Uji Kompetensi (TUK).</p>
                    </div>

                    @if(!empty($panduanPraktik['skenario']))
                        <div class="p-4 bg-slate-50 rounded-xl border border-slate-200/70 text-xs sm:text-sm text-slate-700 leading-relaxed space-y-1">
                            <span class="font-bold text-slate-900 block uppercase tracking-wider text-[11px]">Skenario Tugas Praktik:</span>
                            <p>{{ $panduanPraktik['skenario'] }}</p>
                        </div>
                    @endif

                    <!-- Instruksi Kerja -->
                    @if(!empty($panduanPraktik['instruksi_kerja']))
                        <div class="space-y-2.5">
                            <h3 class="text-xs font-bold uppercase tracking-wider text-slate-800">Instruksi Langkah Kerja:</h3>
                            <ul class="text-xs sm:text-sm text-slate-700 space-y-2 list-decimal list-inside bg-slate-50 p-4 rounded-xl border border-slate-200/60 leading-relaxed">
                                @foreach($panduanPraktik['instruksi_kerja'] as $instruksi)
                                    <li>{{ $instruksi }}</li>
                                @endforeach
                            </ul>
                        </div>
                    @endif

                    <!-- Peralatan & Bahan -->
                    @if(!empty($panduanPraktik['peralatan_bahan']))
                        <div class="space-y-2.5">
                            <h3 class="text-xs font-bold uppercase tracking-wider text-slate-800">Daftar Peralatan & Bahan di TUK:</h3>
                            <ul class="text-xs sm:text-sm text-slate-700 space-y-1.5 list-disc list-inside bg-slate-50 p-4 rounded-xl border border-slate-200/60 leading-relaxed">
                                @foreach($panduanPraktik['peralatan_bahan'] as $alat)
                                    <li>{{ $alat }}</li>
                                @endforeach
                            </ul>
                        </div>
                    @endif

                    <!-- Form Upload Laporan / Foto Benda Kerja -->
                    @if(!$isReadonly)
                        <form action="{{ route('asesi.ujian.upload-ia02') }}" method="POST" enctype="multipart/form-data" class="border-t border-slate-200/80 pt-6 space-y-4">
                            @csrf
                            <input type="hidden" name="pendaftaran_id" value="{{ $pendaftaran->id }}">

                            <div class="space-y-1.5">
                                <label class="block text-xs font-bold text-slate-800 uppercase tracking-wider">
                                    Unggah Dokumen Laporan / Foto Benda Kerja:
                                </label>
                                <input type="file" name="file_praktik" accept=".pdf,.jpg,.jpeg,.png,.webp,.zip,.rar,.doc,.docx" required
                                       class="w-full text-xs text-slate-600 file:mr-3 file:py-2.5 file:px-4 file:rounded-xl file:border-0 file:text-xs file:font-semibold file:bg-blue-50 file:text-blue-700 hover:file:bg-blue-100 cursor-pointer">
                                <p class="text-[11px] text-slate-400">Format yang didukung: PDF, JPG, PNG, ZIP, RAR, DOCX. Maksimal 10MB.</p>
                            </div>

                            <button type="submit" class="px-5 py-2.5 bg-blue-600 hover:bg-blue-700 text-white rounded-xl text-xs font-bold shadow-xs transition-colors">
                                Unggah Berkas Laporan Praktik
                            </button>
                        </form>
                    @endif

                    @if($dokumenPraktik)
                        <div class="p-4 bg-emerald-50 border border-emerald-200 rounded-xl text-xs text-emerald-800 flex flex-wrap items-center justify-between gap-2">
                            <div>
                                <span class="font-bold block">Berkas Hasil Praktik Telah Terunggah:</span>
                                <span class="text-slate-600">{{ $dokumenPraktik->nama_dokumen }}</span>
                            </div>
                            <a href="{{ asset($dokumenPraktik->file_path) }}" target="_blank" class="px-3 py-1.5 bg-white border border-emerald-300 text-emerald-800 font-bold rounded-lg hover:bg-emerald-100 transition-colors">
                                Buka Berkas ↗
                            </a>
                        </div>
                    @endif

                </div>
            </div>
        @endif

        <!-- =========================================================================
             TAB 4: FR.IA.04A PROYEK / TOR
             ========================================================================= -->
        @if($pendaftaran->skema->hasInstrumen('FR.IA.04A'))
            <div x-show="activeTab === 'proyek'" class="space-y-6">
                <div class="bg-white rounded-2xl border border-slate-200/80 p-6 sm:p-8 shadow-xs space-y-6">
                    <div class="border-b border-slate-200/80 pb-4">
                        <div class="text-indigo-600 font-bold text-xs uppercase tracking-wider">
                            FR.IA.04A &bull; Penugasan Proyek Singkat / Term of Reference (TOR)
                        </div>
                        <h2 class="text-xl font-bold text-slate-900 mt-1">Studi Kasus Proyek Skema {{ $pendaftaran->skema->nama_skema }}</h2>
                        <p class="text-slate-500 text-xs mt-0.5">Selesaikan penugasan proyek sesuai batasan spesifikasi teknis dan kerangka acuan kerja (TOR).</p>
                    </div>

                    <div class="p-4 bg-indigo-50/60 rounded-xl border border-indigo-200/60 text-xs sm:text-sm text-indigo-950 space-y-2 leading-relaxed">
                        <div class="font-bold">Ketentuan Pengerjaan Proyek:</div>
                        <p>1. Kerjakan proyek secara sistematis sesuai standar industri dan spesifikasi tugas yang diberikan.</p>
                        <p>2. Susun dokumen laporan akhir atau serahkan artefak proyek kepada asesor penguji melalui sistem atau demonstrasi langsung di TUK.</p>
                    </div>
                </div>
            </div>
        @endif

        <!-- =========================================================================
             4. BOTTOM STICKY ACTION BAR (PENGUMPULAN UJIAN)
             ========================================================================= -->
        <div class="fixed bottom-0 inset-x-0 z-30 bg-white/95 backdrop-blur-md border-t border-slate-200/80 py-3 px-4 sm:px-6 shadow-lg">
            <div class="max-w-7xl mx-auto flex flex-col sm:flex-row items-center justify-between gap-3">
                <div class="text-xs text-slate-600 flex items-center gap-3">
                    <div>
                        CBT: <strong class="text-blue-600" x-text="countPgAnswered()"></strong>/{{ $totalSoalCbt }} terjawab
                    </div>
                    <span>&bull;</span>
                    <div>
                        Esai: <strong class="text-blue-600" x-text="countEsaiAnswered()"></strong>/{{ $totalSoalEsai }} terisi
                    </div>
                    <span class="hidden sm:inline">&bull;</span>
                    <div class="hidden sm:inline text-slate-500" x-text="saveStatus"></div>
                </div>

                <div class="flex items-center gap-3 w-full sm:w-auto">
                    <!-- Mobile Palette Toggle -->
                    @if($pendaftaran->skema->hasInstrumen('FR.IA.05'))
                        <button type="button" 
                                @click="showMobilePalette = true"
                                x-show="activeTab === 'cbt'"
                                class="sm:hidden flex-1 py-2.5 px-3 bg-slate-100 text-slate-700 font-bold text-xs rounded-xl border border-slate-200">
                            Daftar Soal (<span x-text="countPgAnswered()"></span>/{{ $totalSoalCbt }})
                        </button>
                    @endif

                    @if(!$isReadonly && ($statusSesi['status'] ?? '') === 'aktif')
                        <button type="button" 
                                @click="showSubmitModal = true"
                                class="flex-1 sm:flex-initial px-6 py-2.5 bg-emerald-600 hover:bg-emerald-700 text-white font-bold text-xs rounded-xl shadow-xs transition-colors flex items-center justify-center gap-2">
                            <span>Kumpulkan Ujian</span>
                            <span>✓</span>
                        </button>
                    @else
                        <span class="text-xs text-slate-500 italic">
                            Lembar ujian telah dikunci / selesai.
                        </span>
                    @endif
                </div>
            </div>
        </div>

        @endif

    </main>

    <!-- =========================================================================
         5. MOBILE BOTTOM SHEET / MODAL DAFTAR NOMOR SOAL
         ========================================================================= -->
    <div x-show="showMobilePalette" 
         x-transition.opacity.duration.200ms
         class="fixed inset-0 z-50 flex items-end sm:items-center justify-center p-0 sm:p-4 bg-slate-900/60 backdrop-blur-xs"
         style="display: none;">
        
        <div @click.away="showMobilePalette = false"
             class="bg-white rounded-t-3xl sm:rounded-3xl border border-slate-200 max-w-lg w-full p-6 space-y-4 shadow-2xl max-h-[85vh] flex flex-col">
            
            <div class="flex items-center justify-between border-b border-slate-100 pb-3">
                <div>
                    <h3 class="text-sm font-bold text-slate-900">Navigasi Nomor Soal</h3>
                    <p class="text-xs text-slate-500">Pilih nomor butir soal untuk melompat</p>
                </div>
                <button type="button" 
                        @click="showMobilePalette = false"
                        class="w-8 h-8 rounded-full bg-slate-100 text-slate-600 font-bold text-sm flex items-center justify-center">
                    ✕
                </button>
            </div>

            <!-- Stats Bar in Mobile Drawer -->
            <div class="grid grid-cols-2 gap-2 text-center text-xs">
                <div class="bg-emerald-50 border border-emerald-200 rounded-xl p-2.5">
                    <span class="font-bold text-emerald-800 text-sm block" x-text="countPgAnswered()"></span>
                    <span class="text-[10px] block text-emerald-700 font-bold uppercase">Dijawab</span>
                </div>
                <div class="bg-slate-50 border border-slate-200 rounded-xl p-2.5">
                    <span class="font-bold text-slate-700 text-sm block" x-text="{{ $totalSoalCbt }} - countPgAnswered()"></span>
                    <span class="text-[10px] block text-slate-500 font-bold uppercase">Belum</span>
                </div>
            </div>

            <!-- Numbers Grid in Mobile Drawer -->
            <div class="grid grid-cols-5 gap-2 overflow-y-auto py-2">
                @foreach($soalCbt as $no => $item)
                    <button type="button" 
                            @click="goToPg({{ $no }})"
                            class="h-11 rounded-xl text-xs font-bold flex items-center justify-center transition-all"
                            :class="{
                                'ring-2 ring-blue-600 ring-offset-2 scale-105 font-black': currentPgNo === {{ $no }},
                                'bg-emerald-600 text-white border border-emerald-600': jawabanPg[{{ $no }}],
                                'bg-slate-50 text-slate-700 border border-slate-200': !jawabanPg[{{ $no }}]
                            }">
                        {{ $no }}
                    </button>
                @endforeach
            </div>

            <div class="pt-2 border-t border-slate-100">
                <button type="button" 
                        @click="showMobilePalette = false"
                        class="w-full py-2.5 bg-slate-100 hover:bg-slate-200 text-slate-800 rounded-xl text-xs font-bold transition-colors">
                    Tutup Navigasi
                </button>
            </div>
        </div>
    </div>

    <!-- =========================================================================
         6. MODAL KONFIRMASI PENGUMPULAN UJIAN FINAL
         ========================================================================= -->
    <div x-show="showSubmitModal" 
         x-transition.opacity.duration.200ms
         class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-slate-900/60 backdrop-blur-xs"
         style="display: none;">
        
        <div class="bg-white rounded-3xl border border-slate-200 max-w-md w-full p-6 sm:p-7 space-y-5 shadow-2xl text-center">
            
            <template x-if="countPgAnswered() >= {{ $totalSoalCbt }}">
                <div class="w-14 h-14 bg-emerald-100 text-emerald-700 rounded-2xl flex items-center justify-center mx-auto text-xl font-bold">
                    ✓
                </div>
            </template>
            <template x-if="countPgAnswered() < {{ $totalSoalCbt }}">
                <div class="w-14 h-14 bg-rose-100 text-rose-700 rounded-2xl flex items-center justify-center mx-auto text-xl font-black">
                    !
                </div>
            </template>

            <div class="space-y-2">
                <template x-if="countPgAnswered() >= {{ $totalSoalCbt }}">
                    <h3 class="text-lg font-bold text-slate-900">Konfirmasi Pengumpulan Ujian</h3>
                </template>
                <template x-if="countPgAnswered() < {{ $totalSoalCbt }}">
                    <h3 class="text-lg font-bold text-rose-900">Soal Ujian Belum Lengkap</h3>
                </template>

                <p class="text-slate-600 text-xs leading-relaxed" x-show="countPgAnswered() >= {{ $totalSoalCbt }}">
                    Seluruh soal pilihan ganda telah dijawab dengan lengkap. Lembar jawaban akan dikunci dan diserahkan kepada Asesor Penguji.
                </p>
                
                <!-- Status Summary Box -->
                <div class="bg-slate-50 p-3.5 rounded-2xl border border-slate-200 text-xs text-slate-700 text-left space-y-1.5 mt-2">
                    <div class="flex justify-between items-center">
                        <span>Pilihan Ganda (CBT):</span>
                        <strong :class="countPgAnswered() === {{ $totalSoalCbt }} ? 'text-emerald-700' : 'text-rose-700'"
                                x-text="countPgAnswered() + ' dari {{ $totalSoalCbt }} soal'"></strong>
                    </div>

                    <div class="flex justify-between items-center" x-show="countPgAnswered() < {{ $totalSoalCbt }}">
                        <span class="text-rose-700 font-semibold">Belum Dijawab:</span>
                        <strong class="text-rose-700 font-bold" x-text="({{ $totalSoalCbt }} - countPgAnswered()) + ' butir soal'"></strong>
                    </div>

                    <div class="flex justify-between items-center">
                        <span>Ujian Esai:</span>
                        <strong :class="countEsaiAnswered() === {{ $totalSoalEsai }} ? 'text-emerald-700' : 'text-amber-700'"
                                x-text="countEsaiAnswered() + ' dari {{ $totalSoalEsai }} soal'"></strong>
                    </div>

                    @if($pendaftaran->skema->hasInstrumen('FR.IA.02'))
                        <div class="flex justify-between items-center">
                            <span>Laporan Praktik (IA.02):</span>
                            @if($dokumenPraktik)
                                <strong class="text-emerald-700">Sudah Diunggah</strong>
                            @else
                                <strong class="text-slate-500">Belum Diunggah (Opsional)</strong>
                            @endif
                        </div>
                    @endif
                </div>

                <!-- Warning when still has uncompleted questions -->
                <template x-if="countPgAnswered() < {{ $totalSoalCbt }}">
                    <div class="p-3.5 bg-rose-50 border border-rose-200 rounded-2xl text-left text-xs text-rose-900 space-y-2 mt-2">
                        <span class="font-bold block text-rose-800">Ujian Tidak Dapat Dikirim:</span>
                        <p class="text-rose-700 leading-relaxed">
                            Anda belum menjawab seluruh butir soal pilihan ganda. Masih ada <strong class="text-rose-900" x-text="{{ $totalSoalCbt }} - countPgAnswered()"></strong> soal yang kosong. <strong>Seluruh soal wajib dijawab</strong> sebelum Anda dapat mengumpulkan ujian.
                        </p>
                        <button type="button" 
                                @click="goToFirstUnanswered(); showSubmitModal = false"
                                class="w-full py-2.5 px-3 bg-rose-600 hover:bg-rose-700 text-white rounded-xl text-xs font-bold transition shadow-2xs flex items-center justify-center gap-1.5 cursor-pointer">
                            <span>Isi Soal yang Belum Dijawab</span>
                            <span>&rarr;</span>
                        </button>
                    </div>
                </template>
            </div>

            <!-- Action Buttons -->
            <template x-if="countPgAnswered() >= {{ $totalSoalCbt }}">
                <form id="formSubmitUjianFinal" action="{{ route('asesi.ujian.submit') }}" method="POST" class="pt-2 flex items-center justify-center gap-3">
                    @csrf
                    <input type="hidden" name="pendaftaran_id" value="{{ $pendaftaran->id }}">
                    <button type="button" 
                            @click="showSubmitModal = false" 
                            class="flex-1 py-2.5 rounded-xl border border-slate-300 text-slate-700 hover:bg-slate-50 text-xs font-semibold transition-colors cursor-pointer">
                        Periksa Kembali
                    </button>
                    <button type="submit" 
                            class="flex-1 py-2.5 rounded-xl bg-emerald-600 hover:bg-emerald-700 text-white text-xs font-bold shadow-xs transition-colors cursor-pointer">
                        Ya, Kumpulkan Ujian
                    </button>
                </form>
            </template>
            <template x-if="countPgAnswered() < {{ $totalSoalCbt }}">
                <div class="pt-2 flex items-center justify-center gap-3">
                    <button type="button" 
                            @click="showSubmitModal = false" 
                            class="w-full py-2.5 rounded-xl border border-slate-300 text-slate-700 hover:bg-slate-50 text-xs font-semibold transition-colors cursor-pointer">
                        Kembali Periksa Soal
                    </button>
                </div>
            </template>
        </div>
    </div>

</div>
@endsection
