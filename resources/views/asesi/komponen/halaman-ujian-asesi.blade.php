@php
    $bisaAksesUjian = ($statusSesi['bisa_akses'] ?? $statusSesi['can_access'] ?? false) 
        || ($pendaftaran && in_array($pendaftaran->status_pendaftaran, ['diverifikasi', 'lulus', 'tidak_lulus']));
    $isReadonly = ($statusSesi['is_readonly'] ?? false) || ($isSubmitted ?? false) || !$bisaAksesUjian;
    $totalSoalCbt = count($soalCbt ?? []);
    $totalSoalEsai = count($soalEsai ?? []);
    $firstCbtNo = !empty($soalCbt) ? array_key_first($soalCbt) : 1;
    $asesorNama = $pendaftaran->asesor->nama_lengkap ?? $pendaftaran->jadwal?->asesor?->nama_lengkap ?? 'Asesor LSP';
@endphp

<div id="ruangUjianApp" 
     class="space-y-6"
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

    @if(($statusSesi['status'] ?? '') === 'mapa_pending')
        <span class="sr-only">{{ $statusSesi['pesan'] ?? 'Rencana asesmen (FR.MAPA.01 & FR.MAPA.02) belum disahkan asesor.' }}</span>
    @endif

    <!-- =========================================================================
         HEADER & NAVIGASI KONTROL (MODERN, RINGKAS, NON-DARK, BEBAS KARTU BERLEBIHAN)
         ========================================================================= -->
    <div class="bg-white rounded-2xl border border-slate-200/80 shadow-xs overflow-hidden transition-all duration-200">
        
        <!-- Top Bar: Status, Autosave, Timer, Aksi -->
        <div class="px-5 py-3.5 bg-slate-50/70 border-b border-slate-100 flex flex-wrap items-center justify-between gap-3 text-xs">
            
            <!-- Status & Nama Asesor -->
            <div class="flex items-center gap-2.5">
                @if($isSubmitted || ($statusSesi['is_readonly'] ?? false))
                    <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full bg-emerald-50 text-emerald-700 border border-emerald-200 text-[11px] font-bold">
                        <span class="w-2 h-2 rounded-full bg-emerald-500"></span>
                        <span>{{ ($statusSesi['status'] ?? '') === 'selesai_dinilai' ? 'Selesai Dinilai' : 'Jawaban Terkirim' }}</span>
                    </span>
                @elseif(($statusSesi['status'] ?? '') === 'aktif')
                    <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full bg-emerald-50 text-emerald-700 border border-emerald-200 text-[11px] font-bold">
                        <span class="w-2 h-2 rounded-full bg-emerald-500 animate-pulse"></span>
                        <span>Sesi Ujian Aktif</span>
                    </span>
                @else
                    <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full bg-slate-100 text-slate-700 border border-slate-200 text-[11px] font-bold">
                        <span>{{ ucfirst(str_replace('_', ' ', $statusSesi['status'] ?? 'Terjadwal')) }}</span>
                    </span>
                @endif
                <span class="text-slate-300 hidden sm:inline">&bull;</span>
                <span class="text-slate-600 font-medium hidden sm:inline">
                    Asesor: <strong class="text-slate-800">{{ $asesorNama }}</strong>
                </span>
            </div>

            <!-- Kontrol Kanan (Warna Terang, Bebas Dark/Gelap Sesuai Rules) -->
            <div class="flex items-center gap-2.5 ml-auto">
                @if(!$isReadonly)
                    <!-- Indikator Autosave -->
                    <div class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full bg-white border border-slate-200 text-[11px] text-slate-600 shadow-2xs">
                        <span class="w-2 h-2 rounded-full transition-colors" :class="saving ? 'bg-amber-500 animate-ping' : 'bg-emerald-500'"></span>
                        <span class="font-medium" x-text="saveStatus"></span>
                        <span class="text-slate-400 font-mono" x-text="'(' + saveStatusTime + ')'"></span>
                    </div>

                    <!-- Timer Countdown (Tema Cerah Biru/Slate Elegan, Tanpa Warna Gelap) -->
                    @if(($statusSesi['status'] ?? '') === 'aktif')
                        <div class="inline-flex items-center gap-2 px-3.5 py-1 rounded-full bg-blue-50 text-blue-800 border border-blue-200 text-xs shadow-2xs">
                            <svg class="w-3.5 h-3.5 text-blue-600 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                            <span class="text-[10px] uppercase font-bold text-blue-600 tracking-wider">Sisa:</span>
                            <span class="font-mono font-bold text-xs tracking-wider tabular-nums text-blue-900" x-text="timerDisplay">00:00:00</span>
                        </div>
                    @endif

                    <!-- Tombol Kumpulkan -->
                    <button type="button" 
                            @click="showSubmitModal = true"
                            class="inline-flex items-center gap-1.5 px-4 py-1.5 bg-blue-600 hover:bg-blue-700 text-white font-bold text-xs rounded-xl shadow-xs transition-all duration-150 cursor-pointer active:scale-95">
                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                        <span>Kumpulkan</span>
                    </button>
                @else
                    @if(($statusSesi['status'] ?? '') === 'belum_mulai' && ($detikMenujuMulai ?? 0) > 0)
                        <div class="inline-flex items-center gap-2 px-3.5 py-1 rounded-full bg-blue-50 text-blue-800 border border-blue-200 text-xs shadow-2xs">
                            <span class="text-[10px] uppercase font-bold text-blue-600">Mulai Dalam:</span>
                            <span class="font-mono font-bold tracking-wider tabular-nums text-blue-900" x-text="countdownMulaiDisplay">00:00:00</span>
                        </div>
                    @endif
                @endif
            </div>
        </div>

        <!-- Ringkasan Metadata 1 Baris (Flats & Clean, Bebas Nested Card) -->
        <div class="px-5 py-3 border-b border-slate-100 grid grid-cols-2 md:grid-cols-4 gap-3 text-xs bg-white">
            <div class="flex items-center gap-2 min-w-0">
                <span class="text-slate-400 font-bold uppercase text-[10px] shrink-0">Skema:</span>
                <span class="font-bold text-slate-800 truncate" title="{{ $pendaftaran->skema->nama_skema ?? '-' }}">{{ $pendaftaran->skema->kode_skema ?? '-' }}</span>
            </div>
            <div class="flex items-center gap-2 min-w-0">
                <span class="text-slate-400 font-bold uppercase text-[10px] shrink-0">Peserta:</span>
                <span class="font-bold text-slate-800 truncate">{{ $pendaftaran->asesi->nama_lengkap ?? auth()->user()->nama_lengkap }}</span>
            </div>
            <div class="flex items-center gap-2 min-w-0">
                <span class="text-slate-400 font-bold uppercase text-[10px] shrink-0">TUK:</span>
                <span class="font-bold text-slate-800 truncate">{{ $pendaftaran->jadwal->nama_tuk ?? ($pendaftaran->tuk_type ? 'TUK ' . $pendaftaran->tuk_type : 'TUK Mandiri') }}</span>
            </div>
            <div class="flex items-center gap-2 min-w-0">
                <span class="text-slate-400 font-bold uppercase text-[10px] shrink-0">Jadwal:</span>
                <span class="font-bold text-slate-800 truncate">{{ $pendaftaran->jadwal?->tanggal_mulai ? \Carbon\Carbon::parse($pendaftaran->jadwal->tanggal_mulai)->translatedFormat('d M Y') : 'Sesuai Jadwal' }}</span>
            </div>
        </div>

        <!-- Tab Navigasi Segmented (Smooth & Sleek) -->
        <div class="px-5 py-2.5 bg-slate-50/40">
            <div class="inline-flex p-1 bg-slate-100 rounded-xl border border-slate-200/80 gap-1 overflow-x-auto max-w-full text-xs">
                @if(!empty($instrumenAsesi['cbt']) && !empty($soalCbt))
                    <button type="button" 
                            @click="activeTab = 'cbt'"
                            class="px-3.5 py-1.5 rounded-lg font-semibold transition-all duration-150 flex items-center gap-2 shrink-0 cursor-pointer"
                            :class="activeTab === 'cbt' ? 'bg-white text-blue-700 shadow-xs border border-slate-200/70' : 'text-slate-600 hover:text-slate-900 hover:bg-white/50'">
                        <span>FR.IA.05 (Ujian Teori CBT PG)</span>
                        <span class="px-2 py-0.5 rounded-full text-[10px] font-bold"
                              :class="activeTab === 'cbt' ? 'bg-blue-50 text-blue-700 border border-blue-200' : 'bg-slate-200 text-slate-700'">
                            <span x-text="getTotalTerjawabCbt()"></span>/{{ $totalSoalCbt }}
                        </span>
                    </button>
                @endif

                @if(!empty($instrumenAsesi['esai']) && !empty($soalEsai))
                    <button type="button" 
                            @click="activeTab = 'esai'"
                            class="px-3.5 py-1.5 rounded-lg font-semibold transition-all duration-150 flex items-center gap-2 shrink-0 cursor-pointer"
                            :class="activeTab === 'esai' ? 'bg-white text-blue-700 shadow-xs border border-slate-200/70' : 'text-slate-600 hover:text-slate-900 hover:bg-white/50'">
                        <span>FR.IA.06 (Ujian Tertulis Esai)</span>
                        <span class="px-2 py-0.5 rounded-full text-[10px] font-bold"
                              :class="activeTab === 'esai' ? 'bg-blue-50 text-blue-700 border border-blue-200' : 'bg-slate-200 text-slate-700'">
                            <span x-text="getTotalTerjawabEsai()"></span>/{{ $totalSoalEsai }}
                        </span>
                    </button>
                @endif

                @if(!empty($instrumenAsesi['praktik']))
                    <button type="button" 
                            @click="activeTab = 'praktik'"
                            class="px-3.5 py-1.5 rounded-lg font-semibold transition-all duration-150 flex items-center gap-2 shrink-0 cursor-pointer"
                            :class="activeTab === 'praktik' ? 'bg-white text-blue-700 shadow-xs border border-slate-200/70' : 'text-slate-600 hover:text-slate-900 hover:bg-white/50'">
                        <span>FR.IA.02 (Tugas Praktik Demonstrasi)</span>
                        @if($dokumenPraktik)
                            <span class="w-2 h-2 rounded-full bg-emerald-500"></span>
                        @endif
                    </button>
                @endif
            </div>
        </div>
    </div>

    <!-- =========================================================================
         TAB 1: FR.IA.05 CBT PILIHAN GANDA (ANIMATED, RINGAN, NON-DARK)
         ========================================================================= -->
    @if(!empty($instrumenAsesi['cbt']) && !empty($soalCbt))
        <div x-show="activeTab === 'cbt'" 
             x-transition:enter="transition ease-out duration-200"
             x-transition:enter-start="opacity-0 translate-y-1"
             x-transition:enter-end="opacity-100 translate-y-0"
             class="space-y-4">
            
            <div class="grid grid-cols-1 lg:grid-cols-4 gap-5 items-start">
                
                <!-- SISI KIRI: LEMBAR PERTANYAAN (3/4 LEBAR) -->
                <div class="lg:col-span-3">
                    @foreach($soalCbt as $no => $item)
                        <div x-show="currentPgNo === {{ $no }}" 
                             x-transition:enter="transition ease-out duration-200"
                             x-transition:enter-start="opacity-0 translate-y-1"
                             x-transition:enter-end="opacity-100 translate-y-0"
                             class="bg-white rounded-2xl border border-slate-200/80 shadow-xs p-6 sm:p-7 space-y-5">
                            
                            <!-- Header Butir Soal -->
                            <div class="flex items-center justify-between border-b border-slate-100 pb-3.5">
                                <div class="flex items-center gap-2.5">
                                    <span class="px-3 py-1 bg-blue-600 text-white rounded-xl font-bold text-xs shadow-2xs">
                                        Soal {{ $no }}
                                    </span>
                                    <span class="text-xs text-slate-500 font-medium">dari {{ $totalSoalCbt }} Soal</span>
                                </div>
                                <span class="text-[11px] text-slate-500 bg-slate-50 border border-slate-200 px-2.5 py-1 rounded-lg font-mono">
                                    {{ $item['kuk'] ?? 'Standar SKKNI' }}
                                </span>
                            </div>

                            <!-- Kalimat Pernyataan / Pertanyaan -->
                            <div class="text-slate-900 text-base sm:text-[17px] leading-relaxed font-medium">
                                {!! nl2br(e($item['pertanyaan'])) !!}
                            </div>

                            <!-- Lampiran Gambar Soal (Jika Ada) -->
                            @if(!empty($item['gambar']))
                                <div class="p-3 border border-slate-200 rounded-2xl bg-slate-50 flex justify-center">
                                    <img src="{{ asset($item['gambar']) }}" alt="Lampiran Soal {{ $no }}" class="max-h-72 object-contain rounded-xl shadow-2xs">
                                </div>
                            @endif

                            <!-- Daftar Pilihan Jawaban (Interaktif & Lembut) -->
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

                                        <!-- Badge Huruf Pilihan (A, B, C, D) -->
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

                                        <!-- Indikator Checkmark Terpilih -->
                                        <div class="shrink-0 mt-1" x-show="jawabanPg[{{ $no }}] === '{{ $opsiKey }}'" x-transition.opacity>
                                            <div class="w-5 h-5 rounded-full bg-blue-600 text-white flex items-center justify-center shadow-2xs">
                                                <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7"/></svg>
                                            </div>
                                        </div>
                                    </label>
                                @endforeach
                            </div>

                            <!-- Navigasi Bawah Butir Soal -->
                            <div class="flex items-center justify-between pt-5 border-t border-slate-100 text-xs">
                                <button type="button" 
                                        @click="prevSoal()"
                                        :disabled="currentPgNo === {{ $firstCbtNo }}"
                                        class="px-4 py-2.5 rounded-xl border border-slate-200 text-slate-700 font-semibold hover:bg-slate-50 disabled:opacity-40 disabled:cursor-not-allowed cursor-pointer flex items-center gap-2 transition-all duration-150">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/></svg>
                                    <span>Sebelumnya</span>
                                </button>
                                
                                <span class="text-xs font-medium text-slate-500">
                                    Nomor <strong class="text-slate-900 font-bold" x-text="currentPgNo"></strong> / {{ $totalSoalCbt }}
                                </span>

                                <button type="button" 
                                        @click="nextSoal()"
                                        :disabled="currentPgNo === {{ !empty($soalCbt) ? array_key_last($soalCbt) : 1 }}"
                                        class="px-5 py-2.5 rounded-xl bg-blue-600 text-white font-bold hover:bg-blue-700 disabled:opacity-40 disabled:cursor-not-allowed cursor-pointer flex items-center gap-2 transition-all duration-150 shadow-xs">
                                    <span>Selanjutnya</span>
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
                                </button>
                            </div>
                        </div>
                    @endforeach
                </div>

                <!-- SISI KANAN: PALET NOMOR SOAL CBT (1/4 LEBAR) -->
                <div class="lg:col-span-1">
                    <div class="bg-white rounded-2xl border border-slate-200/80 shadow-xs p-5 space-y-4 sticky top-6">
                        <div class="border-b border-slate-100 pb-3">
                            <div class="flex items-center justify-between">
                                <h4 class="font-bold text-xs sm:text-sm text-slate-900">Palet Nomor Soal</h4>
                                <span class="text-[11px] font-mono text-slate-400 font-medium">FR.IA.05</span>
                            </div>
                            <!-- Progress Bar Pengerjaan -->
                            <div class="mt-2.5 space-y-1.5">
                                <div class="flex items-center justify-between text-[11px] text-slate-500">
                                    <span>Progres Pengerjaan</span>
                                    <span class="font-bold text-slate-800"><span x-text="getTotalTerjawabCbt()"></span>/{{ $totalSoalCbt }} (<span x-text="getPercentCbt() + '%'"></span>)</span>
                                </div>
                                <div class="w-full h-1.5 rounded-full bg-slate-100 overflow-hidden">
                                    <div class="h-full bg-blue-600 rounded-full transition-all duration-300" :style="'width: ' + getPercentCbt() + '%'"></div>
                                </div>
                            </div>
                        </div>

                        <!-- Grid Tombol Nomor Soal -->
                        <div class="grid grid-cols-5 gap-1.5 max-h-72 overflow-y-auto pr-1">
                            @foreach($soalCbt as $no => $item)
                                <button type="button" 
                                        @click="goToSoal({{ $no }})"
                                        class="h-9 rounded-xl font-bold text-xs transition-all duration-150 flex items-center justify-center cursor-pointer border select-none"
                                        :class="{
                                            'ring-2 ring-blue-600 ring-offset-2 ring-offset-white': currentPgNo === {{ $no }},
                                            'bg-blue-600 text-white border-blue-600 shadow-2xs': jawabanPg[{{ $no }}],
                                            'bg-slate-50 text-slate-700 border-slate-200 hover:bg-slate-100': !jawabanPg[{{ $no }}]
                                        }">
                                    {{ $no }}
                                </button>
                            @endforeach
                        </div>

                        <!-- Keterangan Status Warna Palet -->
                        <div class="pt-3 border-t border-slate-100 text-[11px] space-y-1.5 text-slate-500">
                            <div class="flex items-center justify-between">
                                <div class="flex items-center gap-2">
                                    <span class="w-2.5 h-2.5 rounded-full bg-blue-600 shrink-0"></span>
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
                                    class="w-full py-2.5 bg-blue-600 hover:bg-blue-700 text-white font-bold text-xs rounded-xl shadow-xs transition-all duration-150 mt-2 cursor-pointer flex items-center justify-center gap-2 active:scale-95">
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
         TAB 2: FR.IA.06 ESAI TERTULIS (ANIMATED & RINGKAS)
         ========================================================================= -->
    @if(!empty($instrumenAsesi['esai']) && !empty($soalEsai))
        <div x-show="activeTab === 'esai'" 
             x-transition:enter="transition ease-out duration-200"
             x-transition:enter-start="opacity-0 translate-y-1"
             x-transition:enter-end="opacity-100 translate-y-0"
             class="space-y-4">
            
            <div class="bg-white rounded-2xl border border-slate-200/80 shadow-xs p-6 sm:p-7 space-y-5">
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
                        <div class="p-5 rounded-2xl border border-slate-200/70 bg-slate-50/40 space-y-3.5 transition-all">
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
                                          class="w-full p-3.5 rounded-xl border border-slate-200 bg-white text-xs sm:text-sm text-slate-800 placeholder:text-slate-400 focus:ring-2 focus:ring-blue-500/20 focus:border-blue-500 outline-none transition disabled:bg-slate-100 disabled:cursor-not-allowed shadow-2xs"></textarea>
                                <span class="text-[10px] text-slate-400 block text-right">Perubahan otomatis disimpan saat Anda mengetik.</span>
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>
        </div>
    @endif

    <!-- =========================================================================
         TAB 3: FR.IA.02 TUGAS PRAKTIK DEMONSTRASI (ANIMATED & STREAMLINED)
         ========================================================================= -->
    @if(!empty($instrumenAsesi['praktik']))
    <div x-show="activeTab === 'praktik'" 
         x-transition:enter="transition ease-out duration-200"
         x-transition:enter-start="opacity-0 translate-y-1"
         x-transition:enter-end="opacity-100 translate-y-0"
         class="space-y-4">
        
        <div class="bg-white rounded-2xl border border-slate-200/80 shadow-xs p-6 sm:p-7 space-y-5">
            <div class="border-b border-slate-100 pb-4">
                <h3 class="text-sm sm:text-base font-bold text-slate-900">
                    FR.IA.02 &bull; Tugas Praktik Demonstrasi & Hasil Proyek Kerja
                </h3>
                <p class="text-xs text-slate-500 mt-0.5">
                    Petunjuk observasi demonstrasi langsung di Tempat Uji Kompetensi (TUK) dan pengunggahan berkas bukti pelaksanaan praktik.
                </p>
            </div>

            <!-- Petunjuk Kerja Praktik -->
            <div class="p-5 rounded-2xl bg-blue-50/40 border border-blue-200/70 space-y-2.5">
                <div class="flex items-center gap-2 text-blue-900 font-bold text-xs uppercase tracking-wider">
                    <svg class="w-4 h-4 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                    <span>Skenario & Instruksi Kerja Demonstrasi:</span>
                </div>
                <p class="text-slate-700 text-xs sm:text-sm leading-relaxed">
                    {{ $panduanPraktik['instruksi'] ?? 'Laksanakan observasi demonstrasi kerja sesuai SOP teknis dan instruksi yang diberikan oleh Asesor Penguji di tempat uji kompetensi.' }}
                </p>
            </div>

            <!-- Form Upload Bukti / Hasil Proyek Praktik -->
            <div class="p-5 rounded-2xl border border-slate-200/70 bg-slate-50/30 space-y-4">
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

                        <button type="submit" class="px-5 py-2.5 bg-blue-600 hover:bg-blue-700 text-white font-bold text-xs rounded-xl shadow-xs transition cursor-pointer flex items-center gap-2 active:scale-95">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-8l-4-4m0 0L8 8m4-4v12"/></svg>
                            <span>Unggah Berkas Praktik</span>
                        </button>
                    </form>
                @endif
            </div>
        </div>
    </div>
    @endif

    <!-- =========================================================================
         MODAL KONFIRMASI PENGUMPULAN FINAL (LIGHT BACKDROP, SMOOTH ANIMATION)
         ========================================================================= -->
    <div x-show="showSubmitModal" 
         class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-slate-600/30 backdrop-blur-xs" 
         style="display: none;"
         x-transition:enter="transition ease-out duration-200"
         x-transition:enter-start="opacity-0"
         x-transition:enter-end="opacity-100"
         x-transition:leave="transition ease-in duration-150"
         x-transition:leave-start="opacity-100"
         x-transition:leave-end="opacity-0">
        
        <div class="bg-white rounded-2xl border border-slate-200 shadow-2xl max-w-md w-full p-6 space-y-5"
             @click.away="showSubmitModal = false"
             x-transition:enter="transition ease-out duration-200"
             x-transition:enter-start="opacity-0 scale-95"
             x-transition:enter-end="opacity-100 scale-100"
             x-transition:leave="transition ease-in duration-150"
             x-transition:leave-start="opacity-100 scale-100"
             x-transition:leave-end="opacity-0 scale-95">
            
            <div class="flex items-center gap-3">
                <div class="w-11 h-11 rounded-xl bg-blue-50 text-blue-600 border border-blue-200 flex items-center justify-center font-bold shrink-0">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                </div>
                <div>
                    <h3 class="font-bold text-slate-900 text-base">Konfirmasi Pengumpulan Jawaban</h3>
                    <p class="text-xs text-slate-500">Pastikan seluruh butir asesmen telah Anda teliti</p>
                </div>
            </div>

            <!-- Ringkasan Statistik Pengerjaan -->
            <div class="bg-slate-50/80 border border-slate-200/80 rounded-xl p-4 text-xs space-y-2.5">
                @if(!empty($instrumenAsesi['cbt']))
                    <div class="flex justify-between items-center">
                        <span class="text-slate-600">Soal Teori CBT (FR.IA.05):</span>
                        <span class="font-bold text-slate-900"><span x-text="getTotalTerjawabCbt()"></span> / {{ $totalSoalCbt }} Terjawab</span>
                    </div>
                @endif
                @if(!empty($instrumenAsesi['esai']) && $totalSoalEsai > 0)
                    <div class="flex justify-between items-center">
                        <span class="text-slate-600">Soal Esai (FR.IA.06):</span>
                        <span class="font-bold text-slate-900"><span x-text="getTotalTerjawabEsai()"></span> / {{ $totalSoalEsai }} Terjawab</span>
                    </div>
                @endif
                @if(!empty($instrumenAsesi['praktik']))
                    <div class="flex justify-between items-center">
                        <span class="text-slate-600">Laporan Praktik (FR.IA.02):</span>
                        <span class="font-bold {{ $dokumenPraktik ? 'text-emerald-600' : 'text-slate-500' }}">
                            {{ $dokumenPraktik ? 'Sudah Diunggah' : 'Belum Ada File / Catatan' }}
                        </span>
                    </div>
                @endif
            </div>

            <!-- Peringatan jika ada soal belum terjawab -->
            @if(!empty($instrumenAsesi['cbt']))
                <div x-show="getUnansweredCbt() > 0" class="p-3 bg-amber-50 border border-amber-200 rounded-xl text-xs text-amber-800 flex items-start gap-2">
                    <svg class="w-4 h-4 text-amber-600 shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/></svg>
                    <span>Masih ada <strong x-text="getUnansweredCbt()"></strong> butir soal CBT yang belum terjawab.</span>
                </div>
            @endif

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
                            class="px-5 py-2 bg-blue-600 hover:bg-blue-700 text-white rounded-xl font-bold text-xs shadow-xs transition cursor-pointer flex items-center gap-1.5 active:scale-95">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                        <span>Kumpulkan Sekarang</span>
                    </button>
                </form>
            </div>
        </div>
    </div>

</div>
