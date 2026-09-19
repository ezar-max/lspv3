@php
    $isReadonly = ($statusSesi['is_readonly'] ?? false) || ($isSubmitted ?? false);
    $totalSoalCbt = count($soalCbt ?? []);
    $totalSoalEsai = count($soalEsai ?? []);
    $firstCbtNo = !empty($soalCbt) ? array_key_first($soalCbt) : 1;
    $asesorNama = $pendaftaran->asesor->nama_lengkap ?? $pendaftaran->jadwal?->asesor?->nama_lengkap ?? 'Asesor LSP';
@endphp

<div id="ruangUjianApp" 
     class="space-y-5"
     x-data="{
        activeTab: '{{ $defaultTab ?? 'cbt' }}',
        pendaftaranId: {{ $pendaftaran ? $pendaftaran->id : 0 }},
        csrfToken: '{{ csrf_token() }}',
        autosaveUrl: '{{ route('asesi.ujian.autosave') }}',
        isSubmitted: {{ $isSubmitted ? 'true' : 'false' }},
        isReadonly: {{ (($statusSesi['is_readonly'] ?? false) || $isSubmitted) ? 'true' : 'false' }},
        saveStatus: 'Tersimpan otomatis',
        saveStatusTime: '{{ now()->format('H:i:s') }}',
        saving: false,
        statusJadwal: '{{ $statusSesi['status'] ?? 'aktif' }}',
        bisaAkses: {{ ($statusSesi['bisa_akses'] ?? false) ? 'true' : 'false' }},
        
        // CBT Navigation State
        cbtKeys: {{ json_encode(!empty($soalCbt) ? array_keys($soalCbt) : []) }},
        currentPgNo: {{ $firstCbtNo }},
        fontSize: 'normal',
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
                }, 5000);
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

        // Navigasi Soal CBT
        goToSoal(no) {
            this.currentPgNo = parseInt(no);
        },

        prevSoal() {
            const idx = this.cbtKeys.indexOf(this.currentPgNo);
            if (idx > 0) {
                this.currentPgNo = this.cbtKeys[idx - 1];
            }
        },

        nextSoal() {
            const idx = this.cbtKeys.indexOf(this.currentPgNo);
            if (idx < this.cbtKeys.length - 1) {
                this.currentPgNo = this.cbtKeys[idx + 1];
            }
        },

        // Autosave Jawaban Pilihan Ganda (CBT)
        async pilihJawabanPg(no, opsiKey) {
            if (this.isReadonly) return;
            this.jawabanPg[no] = opsiKey;
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
                        jawaban: opsiKey
                    })
                });
                const data = await res.json();
                if (data.status === 'success') {
                    this.saveStatus = 'Tersimpan otomatis';
                    this.saveStatusTime = data.saved_at || new Date().toLocaleTimeString([], { hour: '2-digit', minute: '2-digit', second: '2-digit' });
                } else {
                    this.saveStatus = 'Gagal menyimpan: ' + (data.message || 'Error');
                }
            } catch (err) {
                this.saveStatus = 'Koneksi terganggu. Mencoba lagi...';
            } finally {
                this.saving = false;
            }
        },

        // Autosave Jawaban Esai
        async simpanJawabanEsai(no, textVal) {
            if (this.isReadonly) return;
            this.jawabanEsai[no] = textVal;
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
                        tipe: 'esai',
                        no: no,
                        jawaban: textVal
                    })
                });
                const data = await res.json();
                if (data.status === 'success') {
                    this.saveStatus = 'Tersimpan otomatis';
                    this.saveStatusTime = data.saved_at || new Date().toLocaleTimeString([], { hour: '2-digit', minute: '2-digit', second: '2-digit' });
                }
            } catch (err) {
                this.saveStatus = 'Koneksi terganggu.';
            } finally {
                this.saving = false;
            }
        },

        // Rekapitulasi pengerjaan
        getTotalTerjawabCbt() {
            return Object.keys(this.jawabanPg).length;
        },
        getTotalTerjawabEsai() {
            return Object.values(this.jawabanEsai).filter(v => v && v.trim().length > 0).length;
        },
        getPercentCbt() {
            const total = this.cbtKeys.length;
            if (total === 0) return 0;
            return Math.round((this.getTotalTerjawabCbt() / total) * 100);
        },
        getUnansweredCbt() {
            return Math.max(0, this.cbtKeys.length - this.getTotalTerjawabCbt());
        }
     }">

    <!-- =========================================================================
         HEADER UTAMA RUANG UJIAN & ASESMEN (MODERN & PROFESSIONAL)
         ========================================================================= -->
    <div class="bg-white rounded-2xl border border-slate-200/80 shadow-xs overflow-hidden transition-all">
        <!-- Top Status Bar Ribbon -->
        <div class="px-5 py-3 bg-slate-50/70 border-b border-slate-100 flex flex-wrap items-center justify-between gap-3 text-xs">
            <div class="flex items-center gap-2.5">
                <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-lg bg-blue-50 text-blue-700 border border-blue-200/80 text-[11px] font-bold tracking-wide">
                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"/></svg>
                    <span>Asesmen & Uji Kompetensi BNSP</span>
                </span>

                @if($isSubmitted)
                    <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-lg bg-emerald-50 text-emerald-700 border border-emerald-200 text-[11px] font-semibold">
                        <span class="w-2 h-2 rounded-full bg-emerald-500"></span>
                        <span>Jawaban Terkirim</span>
                    </span>
                @elseif($statusSesi['status'] === 'aktif')
                    <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-lg bg-emerald-50 text-emerald-700 border border-emerald-200 text-[11px] font-semibold">
                        <span class="w-2 h-2 rounded-full bg-emerald-500 animate-pulse"></span>
                        <span>Sesi Ujian Aktif</span>
                    </span>
                @else
                    <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-lg bg-amber-50 text-amber-800 border border-amber-200 text-[11px] font-semibold">
                        <span class="w-2 h-2 rounded-full bg-amber-500"></span>
                        <span>{{ ucfirst(str_replace('_', ' ', $statusSesi['status'] ?? 'Terjadwal')) }}</span>
                    </span>
                @endif
            </div>

            <!-- Autosave & Timer Controls -->
            <div class="flex items-center gap-2.5 ml-auto">
                <!-- Autosave indicator -->
                <div class="inline-flex items-center gap-2 px-3 py-1 rounded-full bg-white border border-slate-200 text-[11px] text-slate-600 shadow-2xs">
                    <span class="w-2 h-2 rounded-full transition-colors" :class="saving ? 'bg-amber-500 animate-ping' : 'bg-emerald-500'"></span>
                    <span class="font-medium" x-text="saveStatus"></span>
                    <span class="text-slate-400 font-mono" x-text="'• ' + saveStatusTime"></span>
                </div>

                <!-- Live Timer Countdown Display -->
                @if(!$isSubmitted && $statusSesi['status'] === 'aktif')
                    <div class="inline-flex items-center gap-2 px-3 py-1 rounded-xl bg-slate-900 text-white shadow-xs border border-slate-800">
                        <svg class="w-3.5 h-3.5 text-amber-400 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                        <span class="text-[10px] uppercase font-semibold text-slate-400 tracking-wider">Sisa:</span>
                        <span class="font-mono font-bold text-xs tracking-wider tabular-nums text-white" x-text="timerDisplay">00:00:00</span>
                    </div>
                @endif

                <!-- Submit Button in Header -->
                @if(!$isReadonly)
                    <button type="button" 
                            @click="showSubmitModal = true"
                            class="inline-flex items-center gap-1.5 px-3.5 py-1.5 bg-emerald-600 hover:bg-emerald-700 text-white font-bold text-xs rounded-xl shadow-xs transition cursor-pointer">
                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                        <span>Kumpulkan</span>
                    </button>
                @endif
            </div>
        </div>

        <!-- Main Title & Scheme Information -->
        <div class="p-5 sm:p-6 space-y-4">
            <div class="flex flex-col lg:flex-row lg:items-center lg:justify-between gap-3">
                <div class="space-y-1">
                    <h1 class="text-lg sm:text-xl font-bold text-slate-900 leading-tight">
                        {{ $pendaftaran->skema->nama_skema ?? 'Skema Sertifikasi Kompetensi' }}
                    </h1>
                    <p class="text-xs text-slate-500 flex items-center gap-2">
                        <span>Pelaksanaan Instrumen Asesmen Terpadu (FR.IA)</span>
                        <span class="text-slate-300">&bull;</span>
                        <span>Asesor Penguji: <strong class="text-slate-700 font-semibold">{{ $asesorNama }}</strong></span>
                    </p>
                </div>
            </div>

            <!-- Modern 4-Column Metadata Cards -->
            <div class="grid grid-cols-2 lg:grid-cols-4 gap-3 text-xs">
                <div class="bg-slate-50/80 border border-slate-100/90 rounded-xl p-3 space-y-1">
                    <span class="text-[10px] font-bold uppercase tracking-wider text-slate-400 flex items-center gap-1.5">
                        <svg class="w-3 h-3 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 20l4-16m2 16l4-16M6 9h14M4 15h14"/></svg>
                        Kode Skema
                    </span>
                    <div class="font-mono font-bold text-slate-800 text-xs truncate">{{ $pendaftaran->skema->kode_skema ?? '-' }}</div>
                </div>

                <div class="bg-slate-50/80 border border-slate-100/90 rounded-xl p-3 space-y-1">
                    <span class="text-[10px] font-bold uppercase tracking-wider text-slate-400 flex items-center gap-1.5">
                        <svg class="w-3 h-3 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/></svg>
                        Peserta Asesi
                    </span>
                    <div class="font-bold text-slate-800 text-xs truncate">{{ $pendaftaran->asesi->nama_lengkap ?? auth()->user()->nama_lengkap }}</div>
                </div>

                <div class="bg-slate-50/80 border border-slate-100/90 rounded-xl p-3 space-y-1">
                    <span class="text-[10px] font-bold uppercase tracking-wider text-slate-400 flex items-center gap-1.5">
                        <svg class="w-3 h-3 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"/></svg>
                        Tempat Uji (TUK)
                    </span>
                    <div class="font-bold text-slate-800 text-xs truncate">{{ $pendaftaran->jadwal->nama_tuk ?? ($pendaftaran->tuk_type ? 'TUK ' . $pendaftaran->tuk_type : 'TUK Mandiri LSP') }}</div>
                </div>

                <div class="bg-slate-50/80 border border-slate-100/90 rounded-xl p-3 space-y-1">
                    <span class="text-[10px] font-bold uppercase tracking-wider text-slate-400 flex items-center gap-1.5">
                        <svg class="w-3 h-3 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>
                        Jadwal Uji
                    </span>
                    <div class="font-bold text-slate-800 text-xs truncate">
                        {{ $pendaftaran->jadwal?->tanggal_mulai ? \Carbon\Carbon::parse($pendaftaran->jadwal->tanggal_mulai)->translatedFormat('d M Y') : 'Sesuai Jadwal' }}
                    </div>
                </div>
            </div>

            <!-- Segmented Instrument Navigation Tabs -->
            <div class="pt-2 border-t border-slate-100">
                <div class="inline-flex p-1 bg-slate-100/90 rounded-xl border border-slate-200/70 gap-1 overflow-x-auto max-w-full text-xs">
                    @if(!empty($soalCbt))
                        <button type="button" 
                                @click="activeTab = 'cbt'"
                                class="px-3.5 py-2 rounded-lg font-semibold transition flex items-center gap-2 shrink-0 cursor-pointer"
                                :class="activeTab === 'cbt' ? 'bg-white text-blue-700 shadow-xs border border-slate-200/60' : 'text-slate-600 hover:text-slate-900 hover:bg-white/50'">
                            <span>FR.IA.05 (Ujian Teori CBT PG)</span>
                            <span class="px-2 py-0.5 rounded-full text-[10px] font-bold tracking-tight"
                                  :class="activeTab === 'cbt' ? 'bg-blue-50 text-blue-700 border border-blue-200' : 'bg-slate-200 text-slate-700'">
                                <span x-text="getTotalTerjawabCbt()"></span>/{{ $totalSoalCbt }}
                            </span>
                        </button>
                    @endif

                    @if(!empty($soalEsai))
                        <button type="button" 
                                @click="activeTab = 'esai'"
                                class="px-3.5 py-2 rounded-lg font-semibold transition flex items-center gap-2 shrink-0 cursor-pointer"
                                :class="activeTab === 'esai' ? 'bg-white text-blue-700 shadow-xs border border-slate-200/60' : 'text-slate-600 hover:text-slate-900 hover:bg-white/50'">
                            <span>FR.IA.06 (Ujian Tertulis Esai)</span>
                            <span class="px-2 py-0.5 rounded-full text-[10px] font-bold tracking-tight"
                                  :class="activeTab === 'esai' ? 'bg-blue-50 text-blue-700 border border-blue-200' : 'bg-slate-200 text-slate-700'">
                                <span x-text="getTotalTerjawabEsai()"></span>/{{ $totalSoalEsai }}
                            </span>
                        </button>
                    @endif

                    <button type="button" 
                            @click="activeTab = 'praktik'"
                            class="px-3.5 py-2 rounded-lg font-semibold transition flex items-center gap-2 shrink-0 cursor-pointer"
                            :class="activeTab === 'praktik' ? 'bg-white text-blue-700 shadow-xs border border-slate-200/60' : 'text-slate-600 hover:text-slate-900 hover:bg-white/50'">
                        <span>FR.IA.02 (Tugas Praktik Demonstrasi)</span>
                        @if($dokumenPraktik)
                            <span class="w-2 h-2 rounded-full bg-emerald-500"></span>
                        @endif
                    </button>
                </div>
            </div>
        </div>
    </div>

    <!-- =========================================================================
         GATE STATUS: MAPA BELUM DISAHKAN
         ========================================================================= -->
    @if($pendaftaran && !$pendaftaran->isMapaConfirmed())
        <div class="bg-white rounded-2xl border border-amber-200 shadow-xs p-8 text-center space-y-3">
            <div class="w-12 h-12 bg-amber-50 text-amber-600 border border-amber-200 rounded-2xl flex items-center justify-center mx-auto">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/></svg>
            </div>
            <h3 class="text-base font-bold text-slate-900">Ruang Ujian Belum Dapat Diakses</h3>
            <p class="text-slate-600 text-xs sm:text-sm leading-relaxed max-w-lg mx-auto">
                {{ $statusSesi['pesan'] ?? 'Rencana asesmen (FR.MAPA.01 & FR.MAPA.02) belum disahkan asesor. Ruang ujian belum tersedia.' }}
            </p>
            <div class="pt-2">
                <a href="{{ route('asesi.dashboard') }}" class="inline-flex items-center gap-2 px-5 py-2.5 bg-slate-800 hover:bg-slate-900 text-white rounded-xl text-xs font-bold shadow-xs transition">
                    Kembali ke Dasbor
                </a>
            </div>
        </div>
    @endif

    <!-- =========================================================================
         GATE STATUS: JADWAL BELUM MULAI (TIME-LOCK)
         ========================================================================= -->
    @if(!$isSubmitted && $statusSesi['status'] === 'belum_mulai')
        <div class="bg-white rounded-2xl border border-amber-200/80 shadow-xs p-8 text-center space-y-4">
            <div class="w-14 h-14 rounded-2xl bg-amber-50 text-amber-600 border border-amber-200 flex items-center justify-center mx-auto">
                <svg class="w-7 h-7" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
            </div>
            <div class="space-y-1">
                <h3 class="text-base sm:text-lg font-bold text-slate-900">
                    Sesi Ujian Belum Dibuka
                </h3>
                <p class="text-xs sm:text-sm text-slate-500 max-w-lg mx-auto leading-relaxed">
                    {{ $statusSesi['pesan'] ?? 'Ruang ujian dan instrumen asesmen baru akan dapat diakses saat sesi ujian resmi dibuka oleh Asesor Penguji.' }}
                </p>
            </div>

            <div class="pt-2 flex flex-col items-center justify-center gap-2 max-w-xs mx-auto">
                <span class="text-[11px] uppercase tracking-wider font-bold text-slate-400">Hitung Mundur Sesi:</span>
                <div class="w-full font-mono font-bold text-2xl text-slate-900 bg-slate-50 px-5 py-2.5 rounded-2xl border border-slate-200 shadow-inner tracking-widest tabular-nums" x-text="countdownMulaiDisplay">
                    00:00:00
                </div>
                <span class="text-[11px] text-slate-400">Halaman akan otomatis dimuat saat waktu sesi telah dimulai.</span>
            </div>
        </div>
    @endif

    <!-- =========================================================================
         GATE STATUS: TELAH DIKUMPULKAN / SELESAI
         ========================================================================= -->
    @if($isSubmitted)
        <div class="bg-white rounded-2xl border border-emerald-200 shadow-xs p-8 text-center space-y-3">
            <div class="w-14 h-14 rounded-2xl bg-emerald-50 text-emerald-600 border border-emerald-200 flex items-center justify-center mx-auto">
                <svg class="w-7 h-7" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7"/></svg>
            </div>
            <h3 class="text-base sm:text-lg font-bold text-slate-900">
                Jawaban Asesmen Telah Berhasil Dikumpulkan
            </h3>
            <p class="text-xs sm:text-sm text-slate-600 max-w-lg mx-auto leading-relaxed">
                Seluruh butir jawaban instrumen asesmen Anda telah berhasil dikirimkan ke sistem dan sedang dalam proses peninjauan / verifikasi oleh Asesor Penguji (<strong>{{ $asesorNama }}</strong>).
            </p>
        </div>
    @endif

    <!-- =========================================================================
         KONTEN TAB 1: FR.IA.05 CBT PILIHAN GANDA (MODERN EXAM CARD)
         ========================================================================= -->
    @if(!empty($soalCbt))
        <div x-show="activeTab === 'cbt'" class="space-y-4">
            <div class="grid grid-cols-1 lg:grid-cols-4 gap-5 items-start">
                
                <!-- SISI KIRI: KARTU PERTANYAAN CBT (3/4 LEBAR) -->
                <div class="lg:col-span-3 space-y-4">
                    @foreach($soalCbt as $no => $item)
                        <div x-show="currentPgNo === {{ $no }}" class="bg-white rounded-2xl border border-slate-200/90 shadow-xs p-5 sm:p-7 space-y-5 transition-all">
                            <!-- Top Question Meta -->
                            <div class="flex items-center justify-between border-b border-slate-100 pb-3.5">
                                <div class="flex items-center gap-2.5">
                                    <span class="px-3 py-1 bg-blue-600 text-white rounded-xl font-bold text-xs tracking-wide shadow-2xs">
                                        Soal {{ $no }}
                                    </span>
                                    <span class="text-xs text-slate-500 font-medium">dari {{ $totalSoalCbt }} Butir</span>
                                </div>
                                <span class="text-[11px] text-slate-500 bg-slate-50 border border-slate-200 px-2.5 py-1 rounded-lg font-mono">
                                    {{ $item['kuk'] ?? 'Standar SKKNI' }}
                                </span>
                            </div>

                            <!-- Question Statement -->
                            <div class="text-slate-900 text-base sm:text-[17px] leading-relaxed font-medium">
                                {!! nl2br(e($item['pertanyaan'])) !!}
                            </div>

                            <!-- Lampiran Gambar Soal (Jika Ada) -->
                            @if(!empty($item['gambar']))
                                <div class="p-3 border border-slate-200 rounded-2xl bg-slate-50 flex justify-center">
                                    <img src="{{ asset($item['gambar']) }}" alt="Lampiran Soal {{ $no }}" class="max-h-72 object-contain rounded-xl shadow-2xs">
                                </div>
                            @endif

                            <!-- Pilihan Jawaban (Modern Interactive Cards) -->
                            <div class="space-y-2.5 pt-2">
                                @foreach(($item['opsi'] ?? []) as $opsiKey => $opsiText)
                                    <label class="group relative flex items-start gap-3.5 p-4 rounded-xl border transition-all duration-150 cursor-pointer select-none"
                                           :class="jawabanPg[{{ $no }}] === '{{ $opsiKey }}' 
                                                    ? 'border-blue-600 bg-blue-50/50 text-slate-900 shadow-2xs ring-1 ring-blue-600/30' 
                                                    : 'border-slate-200/90 bg-white hover:border-slate-300 hover:bg-slate-50/70 text-slate-700'">
                                        <input type="radio" 
                                               name="soal_pg_{{ $no }}" 
                                               value="{{ $opsiKey }}"
                                               :checked="jawabanPg[{{ $no }}] === '{{ $opsiKey }}'"
                                               @change="pilihJawabanPg({{ $no }}, '{{ $opsiKey }}')"
                                               :disabled="isReadonly"
                                               class="sr-only">

                                        <!-- Badge Huruf (A, B, C, D) -->
                                        <div class="w-8 h-8 rounded-lg flex items-center justify-center font-bold text-xs shrink-0 transition-all duration-150 mt-0.5"
                                             :class="jawabanPg[{{ $no }}] === '{{ $opsiKey }}'
                                                      ? 'bg-blue-600 text-white shadow-2xs'
                                                      : 'bg-slate-100 text-slate-600 group-hover:bg-slate-200/80 group-hover:text-slate-800'">
                                            {{ $opsiKey }}
                                        </div>

                                        <!-- Teks Opsi Jawaban -->
                                        <div class="flex-1 text-sm sm:text-[15px] leading-relaxed pt-1 font-normal">
                                            {{ $opsiText }}
                                        </div>

                                        <!-- Selected Check Icon -->
                                        <div class="shrink-0 mt-1" x-show="jawabanPg[{{ $no }}] === '{{ $opsiKey }}'" x-transition.opacity>
                                            <div class="w-5 h-5 rounded-full bg-blue-600 text-white flex items-center justify-center shadow-2xs">
                                                <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7"/></svg>
                                            </div>
                                        </div>
                                    </label>
                                @endforeach
                            </div>

                            <!-- Bottom Navigation Controls -->
                            <div class="flex items-center justify-between pt-5 border-t border-slate-100 text-xs">
                                <button type="button" 
                                        @click="prevSoal()"
                                        :disabled="currentPgNo === {{ $firstCbtNo }}"
                                        class="px-4 py-2.5 rounded-xl border border-slate-200 text-slate-700 font-semibold hover:bg-slate-50 disabled:opacity-40 disabled:cursor-not-allowed cursor-pointer flex items-center gap-2 transition">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/></svg>
                                    <span>Sebelumnya</span>
                                </button>
                                
                                <span class="text-xs font-medium text-slate-500">
                                    Nomor <strong class="text-slate-900 font-bold" x-text="currentPgNo"></strong> / {{ $totalSoalCbt }}
                                </span>

                                <button type="button" 
                                        @click="nextSoal()"
                                        :disabled="currentPgNo === {{ !empty($soalCbt) ? array_key_last($soalCbt) : 1 }}"
                                        class="px-5 py-2.5 rounded-xl bg-blue-600 text-white font-bold hover:bg-blue-700 disabled:opacity-40 disabled:cursor-not-allowed cursor-pointer flex items-center gap-2 transition shadow-xs">
                                    <span>Selanjutnya</span>
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
                                </button>
                            </div>
                        </div>
                    @endforeach
                </div>

                <!-- SISI KANAN: PALET NOMOR SOAL CBT (1/4 LEBAR) -->
                <div class="lg:col-span-1 space-y-4">
                    <div class="bg-white rounded-2xl border border-slate-200/90 shadow-xs p-4 sm:p-5 space-y-4 sticky top-6">
                        <div class="border-b border-slate-100 pb-3">
                            <div class="flex items-center justify-between">
                                <h4 class="font-bold text-xs sm:text-sm text-slate-900">Palet Nomor Soal</h4>
                                <span class="text-[11px] font-mono text-slate-400 font-medium">FR.IA.05</span>
                            </div>
                            <!-- Progress Bar Info -->
                            <div class="mt-2.5 space-y-1.5">
                                <div class="flex items-center justify-between text-[11px] text-slate-500">
                                    <span>Progres Pengerjaan</span>
                                    <span class="font-bold text-slate-800"><span x-text="getTotalTerjawabCbt()"></span>/{{ $totalSoalCbt }} (<span x-text="getPercentCbt() + '%'"></span>)</span>
                                </div>
                                <div class="w-full h-1.5 rounded-full bg-slate-100 overflow-hidden">
                                    <div class="h-full bg-emerald-500 rounded-full transition-all duration-300" :style="'width: ' + getPercentCbt() + '%'"></div>
                                </div>
                            </div>
                        </div>

                        <!-- Grid Tombol Nomor -->
                        <div class="grid grid-cols-5 gap-1.5 max-h-72 overflow-y-auto pr-1">
                            @foreach($soalCbt as $no => $item)
                                <button type="button" 
                                        @click="goToSoal({{ $no }})"
                                        class="h-9 rounded-xl font-bold text-xs transition flex items-center justify-center cursor-pointer border select-none"
                                        :class="{
                                            'ring-2 ring-blue-600 ring-offset-2 ring-offset-white': currentPgNo === {{ $no }},
                                            'bg-emerald-600 text-white border-emerald-600 shadow-2xs': jawabanPg[{{ $no }}],
                                            'bg-slate-50 text-slate-700 border-slate-200 hover:bg-slate-100': !jawabanPg[{{ $no }}]
                                        }">
                                    {{ $no }}
                                </button>
                            @endforeach
                        </div>

                        <!-- Keterangan Warna Palet -->
                        <div class="pt-3 border-t border-slate-100 text-[11px] space-y-1.5 text-slate-500">
                            <div class="flex items-center justify-between">
                                <div class="flex items-center gap-2">
                                    <span class="w-2.5 h-2.5 rounded-full bg-emerald-600 shrink-0"></span>
                                    <span>Sudah Terjawab</span>
                                </div>
                                <span class="font-bold text-slate-700" x-text="getTotalTerjawabCbt()"></span>
                            </div>
                            <div class="flex items-center justify-between">
                                <div class="flex items-center gap-2">
                                    <span class="w-2.5 h-2.5 rounded-full bg-slate-200 border border-slate-300 shrink-0"></span>
                                    <span>Belum Terjawab</span>
                                </div>
                                <span class="font-bold text-slate-700" x-text="getUnansweredCbt()"></span>
                            </div>
                        </div>

                        @if(!$isReadonly)
                            <button type="button" 
                                    @click="showSubmitModal = true"
                                    class="w-full py-2.5 bg-emerald-600 hover:bg-emerald-700 text-white font-bold text-xs rounded-xl shadow-xs transition mt-2 cursor-pointer flex items-center justify-center gap-2">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                                <span>Kumpulkan Jawaban</span>
                            </button>
                        @endif
                    </div>
                </div>

            </div>
        </div>
    @endif

    <!-- =========================================================================
         KONTEN TAB 2: FR.IA.06 ESAI TERTULIS (MODERN ESSAY FORM)
         ========================================================================= -->
    @if(!empty($soalEsai))
        <div x-show="activeTab === 'esai'" class="space-y-4">
            <div class="bg-white rounded-2xl border border-slate-200/90 shadow-xs p-5 sm:p-7 space-y-5">
                <div class="border-b border-slate-100 pb-4 flex flex-col sm:flex-row sm:items-center justify-between gap-2">
                    <div>
                        <h3 class="text-sm sm:text-base font-bold text-slate-900 flex items-center gap-2">
                            <span>FR.IA.06 &bull; Daftar Pertanyaan Tertulis Esai</span>
                        </h3>
                        <p class="text-xs text-slate-500 mt-0.5">
                            Tuliskan penjelasan dan pemahaman Anda secara lengkap sesuai standar kompetensi kerja.
                        </p>
                    </div>
                    <div class="text-xs font-semibold text-slate-600 px-3 py-1 bg-slate-50 border border-slate-200 rounded-lg">
                        Terisi: <strong class="text-slate-900" x-text="getTotalTerjawabEsai()"></strong> / {{ $totalSoalEsai }} Pertanyaan
                    </div>
                </div>

                <div class="space-y-5">
                    @foreach($soalEsai as $no => $esai)
                        <div class="p-5 rounded-2xl border border-slate-200/80 bg-slate-50/50 space-y-3.5 transition-all">
                            <div class="flex items-center justify-between">
                                <span class="px-3 py-1 rounded-lg bg-blue-600 text-white text-xs font-bold shadow-2xs">
                                    Pertanyaan {{ $no }}
                                </span>
                                <span class="text-[11px] text-slate-500 font-mono bg-white border border-slate-200 px-2 py-0.5 rounded-md">
                                    {{ $esai['kuk'] ?? 'Standar SKKNI' }}
                                </span>
                            </div>

                            <div class="font-medium text-slate-900 text-sm sm:text-[15px] leading-relaxed">
                                {!! nl2br(e($esai['pertanyaan'])) !!}
                            </div>

                            <div class="space-y-1">
                                <textarea rows="4" 
                                          :disabled="isReadonly"
                                          x-model="jawabanEsai[{{ $no }}]"
                                          @input.debounce.800ms="simpanJawabanEsai({{ $no }}, $event.target.value)"
                                          placeholder="Ketikkan uraian jawaban Anda di sini..."
                                          class="w-full p-3.5 rounded-xl border border-slate-200/90 bg-white text-xs sm:text-sm text-slate-800 placeholder:text-slate-400 focus:ring-2 focus:ring-blue-500/20 focus:border-blue-500 outline-none transition disabled:bg-slate-100 disabled:cursor-not-allowed shadow-2xs"></textarea>
                                <span class="text-[10px] text-slate-400 block text-right">Perubahan otomatis disimpan saat Anda mengetik.</span>
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>
        </div>
    @endif

    <!-- =========================================================================
         KONTEN TAB 3: FR.IA.02 TUGAS PRAKTIK DEMONSTRASI (MODERN TASK CARD)
         ========================================================================= -->
    <div x-show="activeTab === 'praktik'" class="space-y-4">
        <div class="bg-white rounded-2xl border border-slate-200/90 shadow-xs p-5 sm:p-7 space-y-5">
            <div class="border-b border-slate-100 pb-4">
                <h3 class="text-sm sm:text-base font-bold text-slate-900">
                    FR.IA.02 &bull; Tugas Praktik Demonstrasi & Hasil Proyek Kerja
                </h3>
                <p class="text-xs text-slate-500 mt-0.5">
                    Petunjuk observasi demonstrasi langsung di Tempat Uji Kompetensi (TUK) dan pengunggahan berkas bukti pelaksanaan praktik.
                </p>
            </div>

            <!-- Petunjuk Kerja Praktik -->
            <div class="p-5 rounded-2xl bg-blue-50/50 border border-blue-200/80 space-y-2.5">
                <div class="flex items-center gap-2 text-blue-900 font-bold text-xs uppercase tracking-wider">
                    <svg class="w-4 h-4 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                    <span>Skenario & Instruksi Kerja Demonstrasi:</span>
                </div>
                <p class="text-slate-700 text-xs sm:text-sm leading-relaxed">
                    {{ $panduanPraktik['instruksi'] ?? 'Laksanakan observasi demonstrasi kerja sesuai SOP teknis dan instruksi yang diberikan oleh Asesor Penguji di tempat uji kompetensi.' }}
                </p>
            </div>

            <!-- Form Upload Bukti / Hasil Proyek Praktik -->
            <div class="p-5 rounded-2xl border border-slate-200/80 bg-slate-50/40 space-y-4">
                <div>
                    <span class="font-bold text-slate-900 text-xs sm:text-sm block">Unggah Laporan Hasil Proyek / Demonstrasi</span>
                    <p class="text-[11px] text-slate-500">Unggah berkas hasil kerja demonstrasi jika diinstruksikan oleh Asesor Penguji.</p>
                </div>
                
                @if($dokumenPraktik)
                    <div class="p-4 bg-emerald-50 border border-emerald-200 rounded-xl flex flex-wrap items-center justify-between gap-3">
                        <div class="flex items-center gap-3">
                            <div class="w-9 h-9 rounded-xl bg-emerald-100 text-emerald-700 flex items-center justify-center shrink-0">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
                            </div>
                            <div>
                                <span class="font-bold text-emerald-900 text-xs block">{{ $dokumenPraktik->nama_dokumen }}</span>
                                <span class="text-[10px] text-emerald-700">Berkas telah tersimpan dan siap ditinjau oleh Asesor</span>
                            </div>
                        </div>
                        <a href="{{ asset('storage/' . $dokumenPraktik->file_path) }}" target="_blank" class="inline-flex items-center gap-1.5 px-3.5 py-1.5 bg-emerald-600 hover:bg-emerald-700 text-white rounded-xl font-bold text-xs transition shadow-2xs">
                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/></svg>
                            <span>Lihat Berkas</span>
                        </a>
                    </div>
                @endif

                @if(!$isReadonly)
                    <form action="{{ route('asesi.ujian.upload-ia02') }}" method="POST" enctype="multipart/form-data" class="space-y-3.5">
                        @csrf
                        <input type="hidden" name="pendaftaran_id" value="{{ $pendaftaran ? $pendaftaran->id : '' }}">
                        
                        <div>
                            <input type="file" name="file_dokumen" required class="block w-full text-xs text-slate-500 file:mr-3 file:py-2 file:px-4 file:rounded-xl file:border-0 file:text-xs file:font-semibold file:bg-blue-50 file:text-blue-700 hover:file:bg-blue-100 cursor-pointer">
                        </div>

                        <div>
                            <textarea name="catatan_praktik" rows="2" placeholder="Tuliskan catatan ringkas mengenai berkas proyek/praktik yang Anda unggah (opsional)..." class="w-full p-3 border border-slate-200 rounded-xl text-xs bg-white focus:ring-2 focus:ring-blue-500/20 focus:border-blue-500 outline-none transition"></textarea>
                        </div>

                        <button type="submit" class="px-5 py-2.5 bg-blue-600 hover:bg-blue-700 text-white font-bold text-xs rounded-xl shadow-xs transition cursor-pointer flex items-center gap-2">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-8l-4-4m0 0L8 8m4-4v12"/></svg>
                            <span>Unggah Berkas Praktik</span>
                        </button>
                    </form>
                @endif
            </div>
        </div>
    </div>

    <!-- =========================================================================
         MODAL SUBMIT UJIAN FINAL (CLEAN & PROFESSIONAL DIALOG)
         ========================================================================= -->
    <div x-show="showSubmitModal" 
         class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-slate-900/60 backdrop-blur-xs" 
         style="display: none;"
         x-transition.opacity>
        <div class="bg-white rounded-2xl border border-slate-200 shadow-2xl max-w-md w-full p-6 space-y-5"
             @click.away="showSubmitModal = false">
            <div class="flex items-center gap-3">
                <div class="w-11 h-11 rounded-xl bg-emerald-50 text-emerald-600 border border-emerald-200 flex items-center justify-center font-bold shrink-0">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                </div>
                <div>
                    <h3 class="font-bold text-slate-900 text-base">Konfirmasi Pengumpulan Jawaban</h3>
                    <p class="text-xs text-slate-500">Pastikan seluruh butir asesmen telah Anda teliti</p>
                </div>
            </div>

            <!-- Ringkasan Statistik Pengerjaan -->
            <div class="bg-slate-50/80 border border-slate-200/80 rounded-xl p-4 text-xs space-y-2.5">
                <div class="flex justify-between items-center">
                    <span class="text-slate-600">Soal Teori CBT (FR.IA.05):</span>
                    <span class="font-bold text-slate-900"><span x-text="getTotalTerjawabCbt()"></span> / {{ $totalSoalCbt }} Terjawab</span>
                </div>
                @if($totalSoalEsai > 0)
                    <div class="flex justify-between items-center">
                        <span class="text-slate-600">Soal Esai (FR.IA.06):</span>
                        <span class="font-bold text-slate-900"><span x-text="getTotalTerjawabEsai()"></span> / {{ $totalSoalEsai }} Terjawab</span>
                    </div>
                @endif
            </div>

            <!-- Peringatan jika ada soal belum terjawab -->
            <div x-show="getUnansweredCbt() > 0" class="p-3 bg-amber-50 border border-amber-200 rounded-xl text-xs text-amber-800 flex items-start gap-2">
                <svg class="w-4 h-4 text-amber-600 shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/></svg>
                <span>Masih ada <strong x-text="getUnansweredCbt()"></strong> butir soal CBT yang belum terjawab.</span>
            </div>

            <p class="text-xs text-slate-500 leading-relaxed">
                Setelah formulir dikumpulkan, Anda tidak dapat mengubah jawaban kembali. Seluruh hasil akan langsung disimpan dan diserahkan kepada Asesor Penguji.
            </p>

            <div class="flex items-center justify-end gap-2.5 pt-3 border-t border-slate-100">
                <button type="button" 
                        @click="showSubmitModal = false"
                        class="px-4 py-2 border border-slate-200 text-slate-700 rounded-xl font-semibold text-xs hover:bg-slate-50 cursor-pointer transition">
                    Periksa Kembali
                </button>
                <form id="formSubmitUjianFinal" action="{{ route('asesi.ujian.submit') }}" method="POST">
                    @csrf
                    <input type="hidden" name="pendaftaran_id" value="{{ $pendaftaran ? $pendaftaran->id : '' }}">
                    <button type="submit" 
                            class="px-5 py-2 bg-emerald-600 hover:bg-emerald-700 text-white rounded-xl font-bold text-xs shadow-xs transition cursor-pointer flex items-center gap-1.5">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                        <span>Kumpulkan Sekarang</span>
                    </button>
                </form>
            </div>
        </div>
    </div>

</div>
