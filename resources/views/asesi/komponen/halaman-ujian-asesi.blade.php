@php
    $jadwalUji = $pendaftaran?->jadwal;
    $statusJadwalSesi = $statusSesi['status'] ?? 'belum_mulai';
    
    // Cek status ketersediaan dan kesiapan jadwal
    $isBelumAdaJadwal = ($statusJadwalSesi === 'belum_ada_jadwal') || (!$pendaftaran || !$jadwalUji);
    $isJadwalDibatalkan = ($statusJadwalSesi === 'dibatalkan') || ($jadwalUji && $jadwalUji->status_jadwal === 'dibatalkan');
    $isSesiBelumMulai = !$isBelumAdaJadwal && !$isJadwalDibatalkan && (
        ($statusJadwalSesi === 'belum_mulai') 
        || ($jadwalUji && $jadwalUji->isBelumMulai() && !($pendaftaran && $pendaftaran->status_pendaftaran === 'selesai'))
    );
    
    // Asesmen HANYA bisa diakses dan dikerjakan jika jadwal sudah aktif/mulai, jadwal tidak dibatalkan, dan sudah ada jadwalnya
    $bisaAksesUjian = ($statusSesi['bisa_akses'] ?? $statusSesi['can_access'] ?? false) 
        && !$isSesiBelumMulai && !$isBelumAdaJadwal && !$isJadwalDibatalkan;
    
    $isReadonly = ($statusSesi['is_readonly'] ?? false) || ($isSubmitted ?? false) || !$bisaAksesUjian;
    $totalSoalCbt = count($soalCbt ?? []);
    $totalSoalEsai = count($soalEsai ?? []);
    $firstCbtNo = !empty($soalCbt) ? array_key_first($soalCbt) : 1;
    $asesorNama = $pendaftaran?->asesor?->nama_lengkap ?? $jadwalUji?->asesor?->nama_lengkap ?? 'Asesor LSP';
    $tglUjianFormatted = $jadwalUji?->tanggal_uji_carbon ? $jadwalUji->tanggal_uji_carbon->translatedFormat('l, d F Y') : ($statusSesi['formatted_tanggal'] ?? '-');
    $jamMulaiFormatted = $jadwalUji?->waktu_mulai ? substr($jadwalUji->waktu_mulai, 0, 5) . ' WIB' : ($statusSesi['formatted_mulai'] ?? '-');
    $jamSelesaiFormatted = $jadwalUji?->waktu_selesai ? substr($jadwalUji->waktu_selesai, 0, 5) . ' WIB' : ($statusSesi['formatted_selesai'] ?? '-');
    $tukFormatted = $jadwalUji?->nama_tuk ?? ($pendaftaran?->tuk_type ? 'TUK ' . $pendaftaran->tuk_type : 'TUK LSP');
@endphp

<div id="ruangUjianApp" 
     class="space-y-6"
     x-data="{
        activeTab: '{{ $defaultTab ?? 'cbt' }}',
        pendaftaranId: {{ $pendaftaran ? $pendaftaran->id : 0 }},
        csrfToken: '{{ csrf_token() }}',
        autosaveUrl: '{{ route('asesi.ujian.autosave') }}',
        isSubmitted: {{ $isSubmitted ? 'true' : 'false' }},
        isReadonly: {{ $isReadonly ? 'true' : 'false' }},
        saveStatus: 'Tersimpan otomatis',
        saveStatusTime: '{{ now()->format('H:i:s') }}',
        saving: false,
        statusJadwal: '{{ $isSesiBelumMulai ? 'belum_mulai' : ($isBelumAdaJadwal ? 'belum_ada_jadwal' : ($isJadwalDibatalkan ? 'dibatalkan' : ($statusSesi['status'] ?? 'aktif'))) }}',
        bisaAkses: {{ $bisaAksesUjian ? 'true' : 'false' }},
        
        // CBT Navigation State
        cbtKeys: {{ json_encode(!empty($soalCbt) ? array_keys($soalCbt) : []) }},
        currentPgNo: {{ $firstCbtNo }},
        fontSize: 'normal',
        showSubmitModal: false,

        // Jawaban state
        jawabanPg: {{ json_encode($savedJawabanPg ?: (object)[]) }},
        jawabanEsai: {{ json_encode($savedJawabanEsai ?: (object)[]) }},
        catatanPraktik: '{{ addslashes($savedPraktik['catatan_praktik'] ?? '') }}',

        // Feedback state tombol simpan
        savedPgFeedback: {},
        savedEsaiFeedback: {},
        savedPraktikFeedback: false,
        savingAllEsai: false,
        savingAllCbt: false,

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

        // Simpan Jawaban Pilihan Ganda (CBT) - Mendukung Autosave & Tombol Simpan Manual
        async simpanJawabanPg(no, opsiKey, isManual = false) {
            if (this.isReadonly || this.isSubmitted) return;
            if (opsiKey === undefined || opsiKey === null) {
                opsiKey = this.jawabanPg[no] || '';
            }
            if (!opsiKey) return;
            
            this.jawabanPg[no] = opsiKey;
            this.saving = true;
            this.saveStatus = isManual ? ('Menyimpan soal ' + no + '...') : 'Menyimpan...';

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
                    this.saveStatus = isManual ? ('Jawaban no. ' + no + ' berhasil disimpan') : 'Tersimpan otomatis';
                    this.saveStatusTime = data.saved_at || new Date().toLocaleTimeString([], { hour: '2-digit', minute: '2-digit', second: '2-digit' });
                    if (isManual) {
                        this.savedPgFeedback[no] = true;
                        setTimeout(() => { this.savedPgFeedback[no] = false; }, 2500);
                    }
                } else {
                    this.saveStatus = 'Gagal menyimpan: ' + (data.message || 'Error');
                }
            } catch (err) {
                this.saveStatus = 'Koneksi terganggu. Mencoba lagi...';
            } finally {
                this.saving = false;
            }
        },

        pilihJawabanPg(no, opsiKey) {
            this.simpanJawabanPg(no, opsiKey, false);
        },

        // Batch simpan seluruh opsi CBT
        async simpanSemuaCbt() {
            if (this.isReadonly || this.isSubmitted || this.savingAllCbt) return;
            this.savingAllCbt = true;
            this.saving = true;
            this.saveStatus = 'Menyimpan seluruh jawaban CBT...';

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
                        tipe: 'cbt_batch',
                        jawaban_pg: this.jawabanPg
                    })
                });
                const data = await res.json();
                if (data.status === 'success') {
                    const count = Object.keys(this.jawabanPg).length;
                    this.saveStatus = 'Seluruh jawaban CBT (' + count + ' butir) tersimpan';
                    this.saveStatusTime = data.saved_at || new Date().toLocaleTimeString([], { hour: '2-digit', minute: '2-digit', second: '2-digit' });
                    this.savedPgFeedback['all'] = true;
                    setTimeout(() => { this.savedPgFeedback['all'] = false; }, 3000);
                } else {
                    this.saveStatus = 'Gagal menyimpan CBT: ' + (data.message || 'Error');
                }
            } catch (err) {
                this.saveStatus = 'Koneksi terganggu saat menyimpan CBT.';
            } finally {
                this.savingAllCbt = false;
                this.saving = false;
            }
        },

        // Simpan Jawaban Esai - Mendukung Autosave & Tombol Simpan Manual
        async simpanJawabanEsai(no, textVal, isManual = false) {
            if (this.isReadonly || this.isSubmitted) return;
            if (textVal === undefined || textVal === null) {
                textVal = this.jawabanEsai[no] || '';
            }
            this.jawabanEsai[no] = textVal;
            this.saving = true;
            this.saveStatus = isManual ? ('Menyimpan esai no. ' + no + '...') : 'Menyimpan...';

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
                    this.saveStatus = isManual ? ('Jawaban esai no. ' + no + ' berhasil disimpan') : 'Tersimpan otomatis';
                    this.saveStatusTime = data.saved_at || new Date().toLocaleTimeString([], { hour: '2-digit', minute: '2-digit', second: '2-digit' });
                    if (isManual) {
                        this.savedEsaiFeedback[no] = true;
                        setTimeout(() => { this.savedEsaiFeedback[no] = false; }, 2500);
                    }
                } else {
                    this.saveStatus = 'Gagal menyimpan: ' + (data.message || 'Error');
                }
            } catch (err) {
                this.saveStatus = 'Koneksi terganggu saat menyimpan esai.';
            } finally {
                this.saving = false;
            }
        },

        // Batch simpan seluruh data jawaban esai
        async simpanSemuaEsai() {
            if (this.isReadonly || this.isSubmitted || this.savingAllEsai) return;
            this.savingAllEsai = true;
            this.saving = true;
            this.saveStatus = 'Menyimpan seluruh jawaban esai...';

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
                        jawaban_esai: this.jawabanEsai
                    })
                });
                const data = await res.json();
                if (data.status === 'success') {
                    const count = Object.values(this.jawabanEsai).filter(v => v && v.trim().length > 0).length;
                    this.saveStatus = 'Seluruh jawaban esai (' + count + ' butir) tersimpan';
                    this.saveStatusTime = data.saved_at || new Date().toLocaleTimeString([], { hour: '2-digit', minute: '2-digit', second: '2-digit' });
                    this.savedEsaiFeedback['all'] = true;
                    setTimeout(() => { this.savedEsaiFeedback['all'] = false; }, 3000);
                } else {
                    this.saveStatus = 'Gagal menyimpan esai: ' + (data.message || 'Error');
                }
            } catch (err) {
                this.saveStatus = 'Koneksi terganggu saat menyimpan esai.';
            } finally {
                this.savingAllEsai = false;
                this.saving = false;
            }
        },

        // Simpan Catatan Tugas Praktik (FR.IA.02)
        async simpanCatatanPraktikManual() {
            if (this.isReadonly || this.isSubmitted) return;
            this.saving = true;
            this.saveStatus = 'Menyimpan catatan tugas praktik...';

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
                        tipe: 'praktik_catatan',
                        catatan: this.catatanPraktik
                    })
                });
                const data = await res.json();
                if (data.status === 'success') {
                    this.saveStatus = 'Catatan tugas praktik berhasil disimpan';
                    this.saveStatusTime = data.saved_at || new Date().toLocaleTimeString([], { hour: '2-digit', minute: '2-digit', second: '2-digit' });
                    this.savedPraktikFeedback = true;
                    setTimeout(() => { this.savedPraktikFeedback = false; }, 3000);
                } else {
                    this.saveStatus = 'Gagal menyimpan: ' + (data.message || 'Error');
                }
            } catch (err) {
                this.saveStatus = 'Koneksi terganggu saat menyimpan catatan praktik.';
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
                @if($isSubmitted || (($statusSesi['status'] ?? '') === 'selesai_dinilai'))
                    <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full bg-emerald-50 text-emerald-700 border border-emerald-200 text-[11px] font-bold">
                        <span class="w-2 h-2 rounded-full bg-emerald-500"></span>
                        <span>{{ ($statusSesi['status'] ?? '') === 'selesai_dinilai' ? 'Selesai Dinilai' : 'Jawaban Terkirim' }}</span>
                    </span>
                @elseif($bisaAksesUjian)
                    <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full bg-emerald-50 text-emerald-700 border border-emerald-200 text-[11px] font-bold">
                        <span class="w-2 h-2 rounded-full bg-emerald-500 animate-pulse"></span>
                        <span>Sesi Ujian Aktif</span>
                    </span>
                @elseif($isSesiBelumMulai)
                    <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full bg-amber-50 text-amber-800 border border-amber-200 text-[11px] font-bold">
                        <span class="w-2 h-2 rounded-full bg-amber-500"></span>
                        <span>Menunggu Jadwal Mulai</span>
                    </span>
                @elseif($isBelumAdaJadwal)
                    <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full bg-slate-100 text-slate-700 border border-slate-200 text-[11px] font-bold">
                        <span>Belum Ada Jadwal</span>
                    </span>
                @elseif($isJadwalDibatalkan)
                    <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full bg-rose-50 text-rose-700 border border-rose-200 text-[11px] font-bold">
                        <span>Jadwal Dibatalkan</span>
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
                @if($bisaAksesUjian && !$isReadonly)
                    <!-- Indikator Autosave -->
                    <div class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full bg-white border border-slate-200 text-[11px] text-slate-600 shadow-2xs">
                        <span class="w-2 h-2 rounded-full transition-colors" :class="saving ? 'bg-amber-500 animate-ping' : 'bg-emerald-500'"></span>
                        <span class="font-medium" x-text="saveStatus"></span>
                        <span class="text-slate-400 font-mono" x-text="'(' + saveStatusTime + ')'"></span>
                    </div>

                    <!-- Timer Countdown Sisa Waktu Ujian -->
                    <div class="inline-flex items-center gap-2 px-3.5 py-1 rounded-full bg-blue-50 text-blue-800 border border-blue-200 text-xs shadow-2xs">
                        <svg class="w-3.5 h-3.5 text-blue-600 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                        <span class="text-[10px] uppercase font-bold text-blue-600 tracking-wider">Sisa:</span>
                        <span class="font-mono font-bold text-xs tracking-wider tabular-nums text-blue-900" x-text="timerDisplay">00:00:00</span>
                    </div>

                    <!-- Tombol Kumpulkan -->
                    <button type="button" 
                            @click="showSubmitModal = true"
                            class="inline-flex items-center gap-1.5 px-4 py-1.5 bg-blue-600 hover:bg-blue-700 text-white font-bold text-xs rounded-xl shadow-xs transition-all duration-150 cursor-pointer active:scale-95">
                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                        <span>Kumpulkan</span>
                    </button>
                @elseif($isSesiBelumMulai && ($detikMenujuMulai ?? 0) > 0)
                    <div class="inline-flex items-center gap-2 px-3.5 py-1 rounded-full bg-blue-50 text-blue-800 border border-blue-200 text-xs shadow-2xs">
                        <span class="text-[10px] uppercase font-bold text-blue-600">Mulai Dalam:</span>
                        <span class="font-mono font-bold tracking-wider tabular-nums text-blue-900" x-text="countdownMulaiDisplay">00:00:00</span>
                    </div>
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
                <span class="font-bold text-slate-800 truncate">{{ $tukFormatted }}</span>
            </div>
            <div class="flex items-center gap-2 min-w-0">
                <span class="text-slate-400 font-bold uppercase text-[10px] shrink-0">Jadwal:</span>
                <span class="font-bold text-slate-800 truncate">{{ $tglUjianFormatted }} ({{ $jamMulaiFormatted }})</span>
            </div>
        </div>

        @if($bisaAksesUjian || $isSubmitted || ($statusJadwalSesi === 'selesai'))
            <!-- Tab Navigasi Segmented (Smooth & Sleek - Hanya Tampil Saat Sesi Dimulai / Selesai) -->
            <div class="px-5 py-2.5 bg-slate-50/40">
                <div class="inline-flex p-1 bg-slate-100 rounded-xl border border-slate-200/80 gap-1 overflow-x-auto max-w-full text-xs">
                    @if(!empty($instrumenAsesi['cbt']))
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

                    @if(!empty($instrumenAsesi['esai']))
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
        @endif
    </div>

    @if($isSubmitted || (($statusSesi['status'] ?? '') === 'selesai_dinilai'))
        <!-- =========================================================================
             BANNER NOTIFIKASI UJIAN TELAH DIKUMPULKAN & JAWABAN TERKUNCI
             ========================================================================= -->
        <div class="p-4 sm:p-5 rounded-2xl bg-emerald-50/90 border border-emerald-200/90 text-emerald-950 flex flex-col sm:flex-row items-start sm:items-center justify-between gap-3 shadow-2xs">
            <div class="flex items-start sm:items-center gap-3.5">
                <div class="w-10 h-10 rounded-xl bg-emerald-600 text-white flex items-center justify-center shrink-0 shadow-2xs">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7"/></svg>
                </div>
                <div>
                    <h4 class="font-bold text-sm text-emerald-950 flex items-center gap-2">
                        <span>Ujian Telah Dikumpulkan & Terkunci</span>
                        <span class="text-[10px] uppercase font-mono px-2 py-0.5 rounded-full bg-emerald-200 text-emerald-800 font-extrabold">Read-Only</span>
                    </h4>
                    <p class="text-xs text-emerald-800 mt-0.5 leading-relaxed">
                        Seluruh lembar asesmen dan jawaban Anda telah berhasil dikirim ke server. Sesi ujian ini kini bersifat hanya-lihat (read-only) dan jawaban tidak dapat diisi atau diubah lagi selagi menunggu penilaian resmi dari Asesor Penguji.
                    </p>
                </div>
            </div>
            <div class="shrink-0 flex items-center gap-2 self-end sm:self-center">
                <span class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-xl bg-white text-emerald-800 border border-emerald-300 text-xs font-bold shadow-2xs">
                    <svg class="w-4 h-4 text-emerald-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"/></svg>
                    <span>Jawaban Terkunci</span>
                </span>
            </div>
        </div>
    @endif

    @if($isSesiBelumMulai)
        <!-- =========================================================================
             LAYAR TUNGGU JADWAL ASESMEN: ASESMEN BELUM DIMULAI SESUAI JADWAL
             Halaman instrumen hanya dapat diisi dan mulai dikerjakan saat sesi dimulai
             ========================================================================= -->
        <div class="bg-white rounded-2xl border border-slate-200/90 shadow-xs p-6 sm:p-10 text-center space-y-6">
            
            <!-- Badge Status Sesi -->
            <div class="inline-flex items-center gap-2 px-4 py-1.5 rounded-full bg-amber-50 text-amber-800 border border-amber-200 text-xs font-bold">
                <span class="relative flex h-2.5 w-2.5">
                    <span class="animate-ping absolute inline-flex h-full w-full rounded-full bg-amber-400 opacity-75"></span>
                    <span class="relative inline-flex rounded-full h-2.5 w-2.5 bg-amber-500"></span>
                </span>
                <span>Sesi Asesmen Belum Dimulai</span>
            </div>

            <!-- Judul & Keterangan Standar Asesmen -->
            <div class="space-y-2 max-w-xl mx-auto">
                <h3 class="text-xl sm:text-2xl font-extrabold text-slate-900 tracking-tight">
                    Menunggu Waktu Pelaksanaan Asesmen
                </h3>
                <p class="text-xs sm:text-sm text-slate-600 leading-relaxed">
                    Sesuai dengan ketentuan standar uji kompetensi LSP, instrumen asesmen (<strong class="text-slate-800">FR.IA</strong>) hanya dapat diakses, diisi, dan mulai dikerjakan saat sesi asesmen resmi dimulai sesuai jadwal di bawah ini atau dibuka oleh Asesor Penguji.
                </p>
            </div>

            <!-- Kotak Hitungan Mundur (Countdown Timer Besar) -->
            <div class="py-5 px-6 sm:px-10 bg-gradient-to-b from-blue-50/90 to-slate-50/80 border border-blue-200/80 rounded-2xl max-w-md mx-auto shadow-xs space-y-2">
                <div class="text-[11px] uppercase font-extrabold text-blue-700 tracking-wider flex items-center justify-center gap-1.5">
                    <svg class="w-4 h-4 text-blue-600 animate-spin" style="animation-duration: 4s;" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
                    </svg>
                    <span>Asesmen Dimulai Dalam:</span>
                </div>
                <div class="text-3xl sm:text-5xl font-mono font-black tracking-widest tabular-nums text-blue-950" x-text="countdownMulaiDisplay">
                    00:00:00
                </div>
                <div class="text-[11px] text-slate-500 font-medium">
                    (Jam : Menit : Detik Menuju Waktu Pelaksanaan)
                </div>
            </div>

            <!-- Ringkasan Informasi Jadwal Lengkap -->
            <div class="max-w-2xl mx-auto bg-slate-50/80 border border-slate-200/80 rounded-2xl p-4 sm:p-5 text-left text-xs space-y-3">
                <div class="font-bold text-slate-800 text-xs flex items-center gap-2 border-b border-slate-200/70 pb-2">
                    <svg class="w-4 h-4 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>
                    <span>Rincian Jadwal Pelaksanaan Uji Kompetensi</span>
                </div>
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-3 pt-1">
                    <div>
                        <span class="text-slate-400 block text-[11px]">Skema Sertifikasi:</span>
                        <strong class="text-slate-800 text-xs">{{ $pendaftaran->skema->nama_skema ?? '-' }}</strong>
                    </div>
                    <div>
                        <span class="text-slate-400 block text-[11px]">Asesor Penguji:</span>
                        <strong class="text-slate-800 text-xs">{{ $asesorNama }}</strong>
                    </div>
                    <div>
                        <span class="text-slate-400 block text-[11px]">Tanggal Pelaksanaan:</span>
                        <strong class="text-slate-800 text-xs">{{ $tglUjianFormatted }}</strong>
                    </div>
                    <div>
                        <span class="text-slate-400 block text-[11px]">Jam Pelaksanaan:</span>
                        <strong class="text-blue-700 text-xs font-mono font-bold">{{ $jamMulaiFormatted }} - {{ $jamSelesaiFormatted }}</strong>
                    </div>
                    <div class="sm:col-span-2">
                        <span class="text-slate-400 block text-[11px]">Tempat Uji Kompetensi (TUK):</span>
                        <strong class="text-slate-800 text-xs">{{ $tukFormatted }}</strong>
                    </div>
                </div>
            </div>

            <!-- Indikator Sinkronisasi Real-Time & Tindakan -->
            <div class="pt-2 flex flex-col sm:flex-row items-center justify-center gap-3 text-xs text-slate-500">
                <div class="flex items-center gap-2">
                    <span class="relative flex h-2 w-2">
                        <span class="animate-ping absolute inline-flex h-full w-full rounded-full bg-emerald-400 opacity-75"></span>
                        <span class="relative inline-flex rounded-full h-2 w-2 bg-emerald-500"></span>
                    </span>
                    <span class="text-[11px]">Sistem live-sync aktif. Lembar ujian akan terbuka otomatis tanpa refresh manual.</span>
                </div>
                <button type="button" 
                        @click="window.location.reload()" 
                        class="inline-flex items-center gap-1.5 px-3.5 py-1.5 rounded-xl border border-slate-200 bg-white hover:bg-slate-50 text-slate-700 font-semibold shadow-2xs transition-colors cursor-pointer text-xs">
                    <svg class="w-3.5 h-3.5 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/></svg>
                    <span>Periksa Sekarang</span>
                </button>
            </div>
        </div>

    @elseif($isBelumAdaJadwal)
        <!-- =========================================================================
             LAYAR INFO: BELUM MEMILIKI JADWAL ASESMEN
             ========================================================================= -->
        <div class="bg-white rounded-2xl border border-slate-200/90 shadow-xs p-8 sm:p-10 text-center max-w-2xl mx-auto space-y-4">
            <div class="w-16 h-16 rounded-2xl bg-amber-50 text-amber-600 flex items-center justify-center mx-auto text-2xl shadow-2xs">
                <svg class="w-8 h-8 text-amber-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.75" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>
            </div>
            <div class="space-y-1">
                <h3 class="text-lg font-extrabold text-slate-800">Jadwal Asesmen Belum Ditetapkan</h3>
                <p class="text-xs text-slate-500 max-w-md mx-auto leading-relaxed">
                    Pendaftaran sertifikasi Anda belum memiliki jadwal pelaksanaan uji kompetensi. Silakan hubungi admin LSP atau periksa jadwal yang tersedia.
                </p>
            </div>
            <div class="pt-2">
                <a href="{{ route('asesi.jadwal') }}" class="inline-flex items-center gap-2 px-4 py-2 rounded-xl bg-blue-600 hover:bg-blue-700 text-white font-bold text-xs shadow-xs transition-colors">
                    <span>Lihat Jadwal Tersedia &rarr;</span>
                </a>
            </div>
        </div>

    @elseif($isJadwalDibatalkan)
        <!-- =========================================================================
             LAYAR INFO: JADWAL DIBATALKAN OLEH LSP
             ========================================================================= -->
        <div class="bg-white rounded-2xl border border-rose-200 shadow-xs p-8 sm:p-10 text-center max-w-2xl mx-auto space-y-4">
            <div class="w-16 h-16 rounded-2xl bg-rose-50 text-rose-600 flex items-center justify-center mx-auto text-2xl shadow-2xs">
                <svg class="w-8 h-8 text-rose-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.75" d="M18.364 18.364A9 9 0 005.636 5.636m12.728 12.728A9 9 0 015.636 5.636m12.728 12.728L5.636 5.636"/></svg>
            </div>
            <div class="space-y-1">
                <h3 class="text-lg font-extrabold text-slate-800">Jadwal Asesmen Telah Dibatalkan</h3>
                <p class="text-xs text-slate-500 max-w-md mx-auto leading-relaxed">
                    Jadwal pelaksanaan uji kompetensi pada sesi ini telah dibatalkan oleh pihak LSP. Silakan berkoordinasi dengan admin LSP untuk penjadwalan ulang.
                </p>
            </div>
        </div>

    @else
        <!-- =========================================================================
             SESI ASESMEN AKTIF / SELESAI: LEMBAR PENGERJAAN FR.IA DAPAT DIAKSES
             ========================================================================= -->
        @if($isSubmitted || ($statusJadwalSesi === 'selesai'))
            <div class="p-4 bg-emerald-50 border border-emerald-200 rounded-2xl flex items-center gap-3 text-xs text-emerald-900">
                <svg class="w-5 h-5 text-emerald-600 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                <div>
                    <div class="font-bold">Ujian Asesmen Telah Selesai / Terkirim</div>
                    <div class="text-emerald-700 mt-0.5">Seluruh jawaban Anda telah tersimpan dan lembar asesmen ini dalam mode arsip (hanya baca).</div>
                </div>
            </div>
        @endif

    <!-- =========================================================================
         TAB 1: FR.IA.05 CBT PILIHAN GANDA (ANIMATED, RINGAN, NON-DARK)
         ========================================================================= -->
    @if(!empty($instrumenAsesi['cbt']))
        <div x-show="activeTab === 'cbt'" 
             x-transition:enter="transition ease-out duration-200"
             x-transition:enter-start="opacity-0 translate-y-1"
             x-transition:enter-end="opacity-100 translate-y-0"
             class="space-y-4">
            
            @if(!empty($soalCbt))
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
                                    <label class="group relative flex items-start gap-3.5 p-4 rounded-xl border transition-all duration-150 select-none"
                                           :class="{
                                               'cursor-not-allowed opacity-90': isReadonly,
                                               'cursor-pointer': !isReadonly,
                                               'bg-blue-50/70 border-blue-500 text-blue-900 shadow-2xs ring-1 ring-blue-500/30': jawabanPg[{{ $no }}] === '{{ $opsiKey }}',
                                               'bg-slate-50/50 border-slate-200/80 hover:bg-slate-50 hover:border-slate-300 text-slate-700': jawabanPg[{{ $no }}] !== '{{ $opsiKey }}' && !isReadonly,
                                               'bg-slate-50/30 border-slate-200/60 text-slate-500': jawabanPg[{{ $no }}] !== '{{ $opsiKey }}' && isReadonly
                                           }"
                                           @click="if (isReadonly) $event.preventDefault()">
                                        <div class="pt-0.5">
                                            <input type="radio" 
                                                   name="jawaban_pg_{{ $no }}" 
                                                   value="{{ $opsiKey }}"
                                                   :disabled="isReadonly"
                                                   x-model="jawabanPg[{{ $no }}]"
                                                   @change="simpanJawabanPg({{ $no }}, '{{ $opsiKey }}')"
                                                   class="w-4 h-4 text-blue-600 border-slate-300 focus:ring-blue-500 disabled:cursor-not-allowed"
                                                   :class="isReadonly ? 'cursor-not-allowed' : 'cursor-pointer'">
                                        </div>
                                        <div class="flex-1 text-xs sm:text-sm font-medium leading-relaxed">
                                            <span class="font-bold mr-1.5 uppercase text-slate-900">{{ $opsiKey }}.</span>
                                            {{ $opsiText }}
                                        </div>
                                    </label>
                                @endforeach
                            </div>

                            <!-- Navigasi Bawah Butir Soal -->
                            <div class="flex flex-wrap items-center justify-between gap-3 pt-5 border-t border-slate-100 text-xs">
                                <button type="button" 
                                        @click="prevSoal()"
                                        :disabled="currentPgNo === {{ $firstCbtNo }}"
                                        class="px-4 py-2.5 rounded-xl border border-slate-200 text-slate-700 font-semibold hover:bg-slate-50 disabled:opacity-40 disabled:cursor-not-allowed cursor-pointer flex items-center gap-2 transition-all duration-150">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/></svg>
                                    <span>Sebelumnya</span>
                                </button>
                                
                                <div class="flex items-center gap-3">
                                    <span class="text-xs font-medium text-slate-500 hidden sm:inline">
                                        Nomor <strong class="text-slate-900 font-bold" x-text="currentPgNo"></strong> / {{ $totalSoalCbt }}
                                    </span>

                                    @if(!$isReadonly)
                                        <button type="button" 
                                                @click="simpanJawabanPg(currentPgNo, jawabanPg[currentPgNo], true)"
                                                :disabled="isReadonly || !jawabanPg[currentPgNo]"
                                                class="px-4 py-2.5 rounded-xl font-bold text-xs transition-all duration-150 flex items-center gap-1.5 cursor-pointer shadow-2xs active:scale-95 disabled:opacity-50 disabled:cursor-not-allowed"
                                                :class="savedPgFeedback[currentPgNo] 
                                                        ? 'bg-emerald-600 text-white shadow-emerald-200' 
                                                        : 'bg-emerald-50 text-emerald-700 border border-emerald-300 hover:bg-emerald-100'">
                                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                                            </svg>
                                            <span x-text="savedPgFeedback[currentPgNo] ? '✓ Tersimpan!' : 'Simpan Jawaban No. ' + currentPgNo"></span>
                                        </button>
                                    @else
                                        <span class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-xl bg-slate-100 text-slate-600 border border-slate-200 text-xs font-medium">
                                            <svg class="w-3.5 h-3.5 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"/></svg>
                                            <span>Jawaban Terkunci</span>
                                        </span>
                                    @endif
                                </div>

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
                            <div class="space-y-2 pt-2">
                                <button type="button" 
                                        @click="simpanSemuaCbt()"
                                        :disabled="isReadonly || savingAllCbt || getTotalTerjawabCbt() === 0"
                                        class="w-full py-2.5 font-bold text-xs rounded-xl shadow-2xs transition-all duration-150 cursor-pointer flex items-center justify-center gap-2 active:scale-95 disabled:opacity-50 disabled:cursor-not-allowed"
                                        :class="savedPgFeedback['all']
                                                ? 'bg-emerald-600 text-white shadow-emerald-200'
                                                : 'bg-emerald-50 text-emerald-700 border border-emerald-300 hover:bg-emerald-100'">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7H5a2 2 0 00-2 2v9a2 2 0 002 2h14a2 2 0 002-2V9a2 2 0 00-2-2h-3m-1 4l-3 3m0 0l-3-3m3 3V4"/>
                                    </svg>
                                    <span x-text="savingAllCbt ? 'Menyimpan...' : (savedPgFeedback['all'] ? '✓ Seluruh CBT Tersimpan!' : 'Simpan Seluruh Jawaban CBT')"></span>
                                </button>

                                <button type="button" 
                                        @click="showSubmitModal = true"
                                        class="w-full py-2.5 bg-blue-600 hover:bg-blue-700 text-white font-bold text-xs rounded-xl shadow-xs transition-all duration-150 cursor-pointer flex items-center justify-center gap-2 active:scale-95">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                                    <span>Kumpulkan Jawaban</span>
                                </button>
                            </div>
                        @endif
                    </div>
                </div>

            </div>
            @else
                <div class="p-8 text-center bg-white rounded-2xl border border-slate-200 shadow-xs space-y-3">
                    <div class="w-12 h-12 rounded-2xl bg-amber-50 text-amber-600 flex items-center justify-center mx-auto">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/></svg>
                    </div>
                    <h4 class="font-bold text-slate-800 text-sm">Bank Soal Pilihan Ganda Belum Tersedia</h4>
                    <p class="text-xs text-slate-500 max-w-md mx-auto">
                        Asesor atau Admin belum mengonfigurasi bank soal CBT (FR.IA.05) untuk skema ini di database. Silakan konfirmasikan kepada Asesor Penguji Anda.
                    </p>
                </div>
            @endif
        </div>
    @endif
    <!-- =========================================================================
         TAB 2: FR.IA.06 ESAI TERTULIS (ANIMATED & RINGKAS)
         ========================================================================= -->
    @if(!empty($instrumenAsesi['esai']))
        <div x-show="activeTab === 'esai'" 
             x-transition:enter="transition ease-out duration-200"
             x-transition:enter-start="opacity-0 translate-y-1"
             x-transition:enter-end="opacity-100 translate-y-0"
             class="space-y-4">
            @if(!empty($soalEsai))
            
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

                            <div class="space-y-2">
                                <textarea rows="4" 
                                          :disabled="isReadonly"
                                          x-model="jawabanEsai[{{ $no }}]"
                                          @input.debounce.800ms="simpanJawabanEsai({{ $no }}, $event.target.value)"
                                          placeholder="Ketikkan uraian jawaban Anda di sini..."
                                          class="w-full p-3.5 rounded-xl border border-slate-200 bg-white text-xs sm:text-sm text-slate-800 placeholder:text-slate-400 focus:ring-2 focus:ring-blue-500/20 focus:border-blue-500 outline-none transition disabled:bg-slate-100 disabled:cursor-not-allowed shadow-2xs"></textarea>
                                
                                <div class="flex flex-wrap items-center justify-between gap-2 pt-0.5">
                                    @if(!$isReadonly)
                                        <span class="text-[10px] text-slate-400">Perubahan otomatis disimpan saat Anda mengetik.</span>
                                        <button type="button" 
                                                @click="simpanJawabanEsai({{ $no }}, jawabanEsai[{{ $no }}], true)"
                                                :disabled="isReadonly || !jawabanEsai[{{ $no }}]"
                                                class="px-3.5 py-1.5 rounded-lg font-bold text-xs transition-all duration-150 flex items-center gap-1.5 cursor-pointer shadow-2xs active:scale-95 disabled:opacity-50 disabled:cursor-not-allowed"
                                                :class="savedEsaiFeedback[{{ $no }}] 
                                                        ? 'bg-emerald-600 text-white shadow-emerald-200' 
                                                        : 'bg-emerald-50 text-emerald-700 border border-emerald-300 hover:bg-emerald-100'">
                                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                                            </svg>
                                            <span x-text="savedEsaiFeedback[{{ $no }}] ? '✓ Tersimpan!' : 'Simpan Jawaban No. {{ $no }}'"></span>
                                        </button>
                                    @else
                                        <span class="text-[11px] text-slate-500 font-medium flex items-center gap-1.5">
                                            <svg class="w-3.5 h-3.5 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"/></svg>
                                            <span>Jawaban esai terkunci (Ujian telah dikumpulkan).</span>
                                        </span>
                                    @endif
                                </div>
                            </div>
                        </div>
                    @endforeach
                </div>

                @if(!$isReadonly)
                    <!-- Tombol Simpan Seluruh Jawaban Esai -->
                    <div class="pt-5 border-t border-slate-100 flex flex-wrap items-center justify-between gap-3">
                        <div class="text-xs text-slate-500 font-medium">
                            Klik simpan untuk memastikan seluruh uraian jawaban esai tersimpan di server.
                        </div>
                        <button type="button" 
                                @click="simpanSemuaEsai()"
                                :disabled="isReadonly || savingAllEsai || getTotalTerjawabEsai() === 0"
                                class="px-5 py-2.5 rounded-xl font-bold text-xs transition-all duration-150 flex items-center gap-2 cursor-pointer shadow-2xs active:scale-95 disabled:opacity-50 disabled:cursor-not-allowed"
                                :class="savedEsaiFeedback['all']
                                        ? 'bg-emerald-600 text-white shadow-emerald-200' 
                                        : 'bg-emerald-50 text-emerald-700 border border-emerald-300 hover:bg-emerald-100'">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7H5a2 2 0 00-2 2v9a2 2 0 002 2h14a2 2 0 002-2V9a2 2 0 00-2-2h-3m-1 4l-3 3m0 0l-3-3m3 3V4"/>
                            </svg>
                            <span x-text="savingAllEsai ? 'Menyimpan...' : (savedEsaiFeedback['all'] ? '✓ Seluruh Esai Tersimpan!' : 'Simpan Seluruh Jawaban Esai')"></span>
                        </button>
                    </div>
                @else
                    <div class="pt-5 border-t border-slate-100 flex items-center gap-2 text-xs text-slate-600 font-medium bg-slate-50 p-4 rounded-xl border border-slate-200">
                        <svg class="w-4 h-4 text-emerald-600 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                        <span>Seluruh pertanyaan tertulis esai telah Anda jawab dan tersimpan. Formulir ini telah terkunci dan siap dinilai oleh asesor.</span>
                    </div>
                @endif
            </div>
            @else
                <div class="p-8 text-center bg-white rounded-2xl border border-slate-200 shadow-xs space-y-3">
                    <div class="w-12 h-12 rounded-2xl bg-amber-50 text-amber-600 flex items-center justify-center mx-auto">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/></svg>
                    </div>
                    <h4 class="font-bold text-slate-800 text-sm">Bank Soal Esai Belum Tersedia</h4>
                    <p class="text-xs text-slate-500 max-w-md mx-auto">
                        Asesor atau Admin belum mengonfigurasi butir soal esai (FR.IA.06) untuk skema ini di database. Silakan konfirmasikan kepada Asesor Penguji Anda.
                    </p>
                </div>
            @endif
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

            <!-- Petunjuk Kerja Praktik dari Database -->
            <div class="p-5 rounded-2xl bg-blue-50/40 border border-blue-200/70 space-y-3">
                <div class="flex items-center justify-between gap-2 border-b border-blue-200/50 pb-2">
                    <div class="flex items-center gap-2 text-blue-900 font-bold text-xs uppercase tracking-wider">
                        <svg class="w-4 h-4 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                        <span>{{ $panduanPraktik['judul_tugas'] ?? 'Tugas Praktik Demonstrasi' }}</span>
                    </div>
                    @if(!empty($panduanPraktik['waktu_menit']))
                        <span class="text-[11px] font-bold text-blue-800 bg-blue-100 px-2.5 py-0.5 rounded-full">
                            {{ $panduanPraktik['waktu_menit'] }} Menit
                        </span>
                    @endif
                </div>

                @if(!empty($panduanPraktik['skenario']))
                    <div>
                        <span class="text-[11px] font-bold text-blue-900 block mb-0.5">Skenario Penugasan:</span>
                        <p class="text-slate-700 text-xs sm:text-sm leading-relaxed whitespace-pre-line">
                            {{ $panduanPraktik['skenario'] }}
                        </p>
                    </div>
                @else
                    <p class="text-slate-500 text-xs italic">
                        Skenario tugas praktik demonstrasi (FR.IA.02) belum dikonfigurasi di database oleh Asesor/Admin.
                    </p>
                @endif

                @if(!empty($panduanPraktik['peralatan_bahan']))
                    <div class="pt-2 border-t border-blue-200/50">
                        <span class="text-[11px] font-bold text-blue-900 block mb-1">Peralatan & Bahan:</span>
                        @if(is_array($panduanPraktik['peralatan_bahan']))
                            <ul class="list-disc pl-5 text-xs text-slate-700 space-y-0.5">
                                @foreach($panduanPraktik['peralatan_bahan'] as $alat)
                                    <li>{{ $alat }}</li>
                                @endforeach
                            </ul>
                        @else
                            <p class="text-xs text-slate-700 whitespace-pre-line">{{ $panduanPraktik['peralatan_bahan'] }}</p>
                        @endif
                    </div>
                @endif

                @if(!empty($panduanPraktik['instruksi_kerja']))
                    <div class="pt-2 border-t border-blue-200/50">
                        <span class="text-[11px] font-bold text-blue-900 block mb-1">Langkah / Instruksi Kerja:</span>
                        @if(is_array($panduanPraktik['instruksi_kerja']))
                            <ol class="list-decimal pl-5 text-xs text-slate-700 space-y-1">
                                @foreach($panduanPraktik['instruksi_kerja'] as $step)
                                    <li>{{ $step }}</li>
                                @endforeach
                            </ol>
                        @else
                            <p class="text-xs text-slate-700 whitespace-pre-line">{{ $panduanPraktik['instruksi_kerja'] }}</p>
                        @endif
                    </div>
                @endif
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
                    <!-- Area Catatan Praktik Mandiri -->
                    <div class="space-y-2 p-4 rounded-xl bg-white border border-slate-200">
                        <div class="flex flex-wrap items-center justify-between gap-2">
                            <label class="font-bold text-slate-800 text-xs flex items-center gap-1.5">
                                <svg class="w-4 h-4 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/></svg>
                                <span>Catatan / Uraian Pelaksanaan Demonstrasi:</span>
                            </label>

                            <button type="button" 
                                    @click="simpanCatatanPraktikManual()"
                                    :disabled="isReadonly || !catatanPraktik"
                                    class="px-3.5 py-1.5 rounded-lg font-bold text-xs transition-all duration-150 flex items-center gap-1.5 cursor-pointer shadow-2xs active:scale-95 disabled:opacity-50 disabled:cursor-not-allowed"
                                    :class="savedPraktikFeedback 
                                            ? 'bg-emerald-600 text-white shadow-emerald-200' 
                                            : 'bg-emerald-50 text-emerald-700 border border-emerald-300 hover:bg-emerald-100'">
                                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                                </svg>
                                <span x-text="savedPraktikFeedback ? '✓ Catatan Tersimpan!' : 'Simpan Catatan Praktik'"></span>
                            </button>
                        </div>
                        <textarea x-model="catatanPraktik" 
                                  rows="3" 
                                  placeholder="Tuliskan ringkasan prosedur, langkah yang telah Anda laksanakan, atau catatan pendukung tugas demonstrasi..." 
                                  class="w-full p-3 border border-slate-200 rounded-xl text-xs bg-slate-50/40 focus:bg-white focus:ring-2 focus:ring-blue-500/20 focus:border-blue-500 outline-none transition shadow-2xs"></textarea>
                    </div>

                    <!-- Form Unggah Berkas Bukti Praktik -->
                    <form action="{{ route('asesi.ujian.upload-ia02') }}" method="POST" enctype="multipart/form-data" class="space-y-3.5 pt-2">
                        @csrf
                        <input type="hidden" name="pendaftaran_id" value="{{ $pendaftaran ? $pendaftaran->id : '' }}">
                        <input type="hidden" name="catatan_praktik" :value="catatanPraktik">
                        
                        <div>
                            <input type="file" name="file_dokumen" required class="block w-full text-xs text-slate-500 file:mr-3 file:py-2 file:px-4 file:rounded-xl file:border-0 file:text-xs file:font-semibold file:bg-blue-50 file:text-blue-700 hover:file:bg-blue-100 cursor-pointer">
                        </div>

                        <button type="submit" class="px-5 py-2.5 bg-blue-600 hover:bg-blue-700 text-white font-bold text-xs rounded-xl shadow-xs transition cursor-pointer flex items-center gap-2 active:scale-95">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-8l-4-4m0 0L8 8m4-4v12"/></svg>
                            <span>Unggah Berkas Praktik</span>
                        </button>
                    </form>
                @else
                    <!-- Tampilan Read-only Catatan Praktik & Info Kunci -->
                    <div class="space-y-3 pt-1">
                        @if(!empty($savedPraktik['catatan_praktik']))
                            <div class="space-y-1.5 p-4 rounded-xl bg-slate-50 border border-slate-200">
                                <label class="font-bold text-slate-800 text-xs flex items-center gap-1.5">
                                    <svg class="w-4 h-4 text-slate-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/></svg>
                                    <span>Catatan Pelaksanaan Demonstrasi:</span>
                                </label>
                                <p class="text-xs text-slate-700 whitespace-pre-line bg-white p-3 rounded-lg border border-slate-200/80">{{ $savedPraktik['catatan_praktik'] }}</p>
                            </div>
                        @endif
                        <div class="p-3.5 bg-amber-50/70 border border-amber-200 rounded-xl text-xs text-amber-800 flex items-center gap-2.5">
                            <svg class="w-4 h-4 text-amber-600 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"/></svg>
                            <span>Sesi asesmen telah dikumpulkan / dikunci. Anda tidak dapat mengunggah atau mengubah berkas dan catatan demonstrasi lagi.</span>
                        </div>
                    </div>
                @endif
            </div>
        </div>
    </div>
    @endif

    <!-- =========================================================================
         MODAL KONFIRMASI PENGUMPULAN FINAL (LIGHT BACKDROP, SMOOTH ANIMATION)
         ========================================================================= -->
    @if(!$isReadonly)
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
    @endif
    @endif

</div>
