@php
    $isReadonly = ($statusSesi['is_readonly'] ?? false) || ($isSubmitted ?? false);
    $totalSoalCbt = count($soalCbt ?? []);
    $totalSoalEsai = count($soalEsai ?? []);
    $firstCbtNo = !empty($soalCbt) ? array_key_first($soalCbt) : 1;
    $asesorNama = $pendaftaran->asesor->nama_lengkap ?? $pendaftaran->jadwal?->asesor?->nama_lengkap ?? 'Asesor LSP';
@endphp

<div id="ruangUjianApp" 
     class="space-y-4"
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
                    this.saveStatusTime = data.saved_at || new Date().toLocaleTimeString();
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
                    this.saveStatusTime = data.saved_at || new Date().toLocaleTimeString();
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
        }
     }">

    <!-- =========================================================================
         HEADER UTAMA RUANG UJIAN
         ========================================================================= -->
    <div class="bg-white rounded-2xl border border-slate-200/90 shadow-2xs p-4 sm:p-6 space-y-4">
        <div class="flex flex-col lg:flex-row lg:items-center lg:justify-between gap-4 border-b border-slate-100 pb-4">
            <div class="space-y-1">
                <div class="flex items-center gap-2">
                    <span class="px-2.5 py-0.5 rounded-full bg-blue-50 text-blue-700 border border-blue-200 text-[10px] font-black uppercase tracking-wider">
                        Ruang Asesmen & Uji Kompetensi
                    </span>
                    @if($isSubmitted)
                        <span class="px-2.5 py-0.5 rounded-full bg-emerald-50 text-emerald-700 border border-emerald-200 text-[10px] font-bold flex items-center gap-1">
                            <span class="w-1.5 h-1.5 rounded-full bg-emerald-500"></span>
                            Ujian Selesai Dikumpulkan
                        </span>
                    @elseif($statusSesi['status'] === 'aktif')
                        <span class="px-2.5 py-0.5 rounded-full bg-emerald-50 text-emerald-700 border border-emerald-200 text-[10px] font-bold flex items-center gap-1">
                            <span class="w-1.5 h-1.5 rounded-full bg-emerald-500 animate-pulse"></span>
                            Sesi Ujian Aktif
                        </span>
                    @else
                        <span class="px-2.5 py-0.5 rounded-full bg-amber-50 text-amber-700 border border-amber-200 text-[10px] font-bold flex items-center gap-1">
                            <span class="w-1.5 h-1.5 rounded-full bg-amber-500"></span>
                            {{ ucfirst(str_replace('_', ' ', $statusSesi['status'] ?? 'Terjadwal')) }}
                        </span>
                    @endif
                </div>
                <h1 class="text-lg sm:text-xl font-bold text-slate-900 leading-tight">
                    {{ $pendaftaran->skema->nama_skema ?? 'Skema Sertifikasi' }}
                </h1>
                <p class="text-xs text-slate-500">
                    Pelaksanaan instrumen asesmen terpadu BNSP (FR.IA) &bull; Asesor Penguji: <strong>{{ $asesorNama }}</strong>
                </p>
            </div>

            <!-- Timer & Action Bar -->
            <div class="flex flex-wrap items-center gap-3">
                <!-- Autosave indicator -->
                <div class="flex items-center gap-2 px-3 py-1.5 bg-slate-50 border border-slate-200 rounded-xl text-xs text-slate-600">
                    <span class="w-2 h-2 rounded-full" :class="saving ? 'bg-amber-500 animate-ping' : 'bg-emerald-500'"></span>
                    <span class="text-[11px] font-medium" x-text="saveStatus"></span>
                    <span class="text-[10px] text-slate-400" x-text="'(' + saveStatusTime + ')'"></span>
                </div>

                <!-- Timer countdown -->
                @if(!$isSubmitted && $statusSesi['status'] === 'aktif')
                    <div class="flex items-center gap-2 px-3.5 py-1.5 bg-slate-900 text-white rounded-xl shadow-xs">
                        <svg class="w-4 h-4 text-amber-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                        <div class="font-mono font-black text-sm tracking-wider tabular-nums" x-text="timerDisplay">
                            00:00:00
                        </div>
                    </div>
                @endif

                <!-- Submit Button -->
                @if(!$isReadonly)
                    <button type="button" 
                            @click="showSubmitModal = true"
                            class="px-4 py-2 bg-emerald-600 hover:bg-emerald-700 text-white font-bold text-xs rounded-xl shadow-xs transition cursor-pointer flex items-center gap-1.5">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                        <span>Kumpulkan Jawaban</span>
                    </button>
                @endif
            </div>
        </div>

        <!-- 4-Kolom Metadata Singkat -->
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-2.5 text-xs">
            <div class="bg-slate-50/70 border border-slate-100 rounded-xl p-2.5 space-y-0.5">
                <span class="text-[10px] font-bold uppercase text-slate-400">Kode Skema</span>
                <div class="font-mono font-bold text-slate-800">{{ $pendaftaran->skema->kode_skema ?? '-' }}</div>
            </div>
            <div class="bg-slate-50/70 border border-slate-100 rounded-xl p-2.5 space-y-0.5">
                <span class="text-[10px] font-bold uppercase text-slate-400">Peserta (Asesi)</span>
                <div class="font-bold text-slate-800 truncate">{{ $pendaftaran->asesi->nama_lengkap ?? auth()->user()->nama_lengkap }}</div>
            </div>
            <div class="bg-slate-50/70 border border-slate-100 rounded-xl p-2.5 space-y-0.5">
                <span class="text-[10px] font-bold uppercase text-slate-400">Lokasi TUK</span>
                <div class="font-bold text-slate-800 truncate">{{ $pendaftaran->jadwal->nama_tuk ?? ($pendaftaran->tuk_type ? 'TUK ' . $pendaftaran->tuk_type : 'TUK Mandiri LSP') }}</div>
            </div>
            <div class="bg-slate-50/70 border border-slate-100 rounded-xl p-2.5 space-y-0.5">
                <span class="text-[10px] font-bold uppercase text-slate-400">Jadwal Sesi</span>
                <div class="font-bold text-slate-800 truncate">
                    {{ $pendaftaran->jadwal?->tanggal_mulai ? \Carbon\Carbon::parse($pendaftaran->jadwal->tanggal_mulai)->translatedFormat('d M Y') : 'Sesuai Jadwal' }}
                </div>
            </div>
        </div>

        <!-- TAB NAVIGASI INSTRUMEN DINAMIS -->
        <div class="flex items-center gap-2 overflow-x-auto pb-1 pt-1 border-t border-slate-100 text-xs font-semibold">
            @if(!empty($soalCbt))
                <button type="button" 
                        @click="activeTab = 'cbt'"
                        class="px-3.5 py-2 rounded-xl transition flex items-center gap-2 shrink-0 cursor-pointer"
                        :class="activeTab === 'cbt' ? 'bg-blue-600 text-white shadow-xs font-bold' : 'bg-slate-100 text-slate-700 hover:bg-slate-200'">
                    <span>FR.IA.05 (Ujian Teori CBT PG)</span>
                    <span class="px-1.5 py-0.2 rounded-md text-[10px]"
                          :class="activeTab === 'cbt' ? 'bg-blue-800/80 text-white' : 'bg-white text-slate-700 font-bold'">
                        <span x-text="getTotalTerjawabCbt()"></span>/{{ $totalSoalCbt }}
                    </span>
                </button>
            @endif

            @if(!empty($soalEsai))
                <button type="button" 
                        @click="activeTab = 'esai'"
                        class="px-3.5 py-2 rounded-xl transition flex items-center gap-2 shrink-0 cursor-pointer"
                        :class="activeTab === 'esai' ? 'bg-blue-600 text-white shadow-xs font-bold' : 'bg-slate-100 text-slate-700 hover:bg-slate-200'">
                    <span>FR.IA.06 (Ujian Tertulis Esai)</span>
                    <span class="px-1.5 py-0.2 rounded-md text-[10px]"
                          :class="activeTab === 'esai' ? 'bg-blue-800/80 text-white' : 'bg-white text-slate-700 font-bold'">
                        <span x-text="getTotalTerjawabEsai()"></span>/{{ $totalSoalEsai }}
                    </span>
                </button>
            @endif

            <button type="button" 
                    @click="activeTab = 'praktik'"
                    class="px-3.5 py-2 rounded-xl transition flex items-center gap-2 shrink-0 cursor-pointer"
                    :class="activeTab === 'praktik' ? 'bg-blue-600 text-white shadow-xs font-bold' : 'bg-slate-100 text-slate-700 hover:bg-slate-200'">
                <span>FR.IA.02 (Tugas Praktik Demonstrasi)</span>
            </button>
        </div>
    </div>

    <!-- =========================================================================
         GATE STATUS: MAPA BELUM DISAHKAN
         ========================================================================= -->
    @if($pendaftaran && !$pendaftaran->isMapaConfirmed())
        <div class="bg-amber-50 border border-amber-200 rounded-2xl p-6 sm:p-8 text-center space-y-3 shadow-2xs">
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

    <!-- =========================================================================
         GATE STATUS: JADWAL BELUM MULAI (TIME-LOCK)
         ========================================================================= -->
    @if(!$isSubmitted && $statusSesi['status'] === 'belum_mulai')
        <div class="bg-amber-50 border border-amber-200 rounded-2xl p-6 sm:p-8 text-center space-y-3 shadow-2xs">
            <div class="w-12 h-12 rounded-full bg-amber-100 text-amber-600 flex items-center justify-center mx-auto text-xl font-bold">
                ⏳
            </div>
            <h3 class="text-base sm:text-lg font-bold text-amber-900">
                Sesi Ujian Belum Dibuka
            </h3>
            <p class="text-xs sm:text-sm text-amber-800 max-w-xl mx-auto leading-relaxed">
                {{ $statusSesi['pesan'] ?? 'Ruang ujian dan instrumen asesmen baru akan dapat diakses saat sesi ujian resmi dimulai oleh Asesor Penguji.' }}
            </p>
            <div class="pt-2 flex flex-col items-center justify-center gap-1">
                <span class="text-[11px] uppercase tracking-wider font-bold text-amber-700">Waktu Hitung Mundur Menuju Mulai:</span>
                <div class="font-mono font-black text-2xl text-amber-900 bg-white/80 px-4 py-2 rounded-xl border border-amber-300" x-text="countdownMulaiDisplay">
                    00:00:00
                </div>
                <span class="text-[10px] text-amber-600 italic">Halaman akan otomatis memuat saat sesi telah aktif dibuka.</span>
            </div>
        </div>
    @endif

    <!-- =========================================================================
         GATE STATUS: TELAH DIKUMPULKAN / SELESAI
         ========================================================================= -->
    @if($isSubmitted)
        <div class="bg-emerald-50 border border-emerald-200 rounded-2xl p-6 sm:p-8 text-center space-y-3 shadow-2xs">
            <div class="w-12 h-12 rounded-full bg-emerald-100 text-emerald-600 flex items-center justify-center mx-auto text-xl font-bold">
                ✓
            </div>
            <h3 class="text-base sm:text-lg font-bold text-emerald-950">
                Jawaban Asesmen Telah Dikumpulkan
            </h3>
            <p class="text-xs sm:text-sm text-emerald-800 max-w-xl mx-auto leading-relaxed">
                Seluruh jawaban instrumen asesmen Anda telah berhasil dikirimkan ke sistem dan sedang dalam tahap pemeriksaan / verifikasi oleh Asesor Penguji (<strong>{{ $asesorNama }}</strong>).
            </p>
        </div>
    @endif

    <!-- =========================================================================
         KONTEN TAB 1: FR.IA.05 CBT PILIHAN GANDA
         ========================================================================= -->
    @if(!empty($soalCbt))
        <div x-show="activeTab === 'cbt'" class="space-y-4">
            <div class="grid grid-cols-1 lg:grid-cols-4 gap-4">
                
                <!-- SISI KIRI: KARTU PERTANYAAN (3/4 lebar pada layar besar) -->
                <div class="lg:col-span-3 space-y-4">
                    @foreach($soalCbt as $no => $item)
                        <div x-show="currentPgNo === {{ $no }}" class="bg-white rounded-2xl border border-slate-200/90 shadow-2xs p-4 sm:p-6 space-y-4">
                            <!-- Header Butir Soal -->
                            <div class="flex items-center justify-between border-b border-slate-100 pb-3">
                                <div class="flex items-center gap-2.5">
                                    <span class="px-3 py-1 bg-blue-600 text-white rounded-lg font-black text-xs">
                                        Soal {{ $no }}
                                    </span>
                                    <span class="text-xs text-slate-500 font-medium">dari {{ $totalSoalCbt }} Soal</span>
                                </div>
                                <span class="text-[10px] text-slate-400 bg-slate-50 border border-slate-100 px-2 py-1 rounded-md font-mono">
                                    {{ $item['kuk'] ?? 'Standar BNSP' }}
                                </span>
                            </div>

                            <!-- Teks Pertanyaan -->
                            <div class="text-slate-800 text-sm sm:text-base leading-relaxed font-medium">
                                {!! nl2br(e($item['pertanyaan'])) !!}
                            </div>

                            <!-- Gambar jika ada -->
                            @if(!empty($item['gambar']))
                                <div class="p-2 border border-slate-200 rounded-xl bg-slate-50 flex justify-center">
                                    <img src="{{ asset($item['gambar']) }}" alt="Lampiran Soal" class="max-h-64 object-contain rounded-lg">
                                </div>
                            @endif

                            <!-- Daftar Opsi Jawaban -->
                            <div class="space-y-2 pt-2">
                                @foreach(($item['opsi'] ?? []) as $opsiKey => $opsiText)
                                    <label class="flex items-start gap-3 p-3.5 rounded-xl border transition-all cursor-pointer text-xs sm:text-sm"
                                           :class="jawabanPg[{{ $no }}] === '{{ $opsiKey }}' 
                                                    ? 'border-blue-500 bg-blue-50/70 font-semibold text-blue-950 shadow-2xs ring-1 ring-blue-500/20' 
                                                    : 'border-slate-200 hover:border-slate-300 hover:bg-slate-50/80 text-slate-700'">
                                        <input type="radio" 
                                               name="soal_pg_{{ $no }}" 
                                               value="{{ $opsiKey }}"
                                               :checked="jawabanPg[{{ $no }}] === '{{ $opsiKey }}'"
                                               @change="pilihJawabanPg({{ $no }}, '{{ $opsiKey }}')"
                                               :disabled="isReadonly"
                                               class="mt-0.5 text-blue-600 focus:ring-blue-500">
                                        <div class="flex items-start gap-2 min-w-0">
                                            <span class="font-bold uppercase shrink-0 text-slate-500" :class="jawabanPg[{{ $no }}] === '{{ $opsiKey }}' ? 'text-blue-700 font-black' : ''">
                                                {{ $opsiKey }}.
                                            </span>
                                            <span class="leading-relaxed">{{ $opsiText }}</span>
                                        </div>
                                    </label>
                                @endforeach
                            </div>

                            <!-- Tombol Navigasi Soal -->
                            <div class="flex items-center justify-between pt-4 border-t border-slate-100 text-xs">
                                <button type="button" 
                                        @click="prevSoal()"
                                        :disabled="currentPgNo === {{ $firstCbtNo }}"
                                        class="px-4 py-2 rounded-xl border border-slate-200 text-slate-700 font-semibold hover:bg-slate-50 disabled:opacity-40 disabled:cursor-not-allowed cursor-pointer flex items-center gap-1.5 transition">
                                    &larr; Sebelumnya
                                </button>
                                
                                <span class="text-[11px] text-slate-400">
                                    Nomor <strong class="text-slate-700" x-text="currentPgNo"></strong> / {{ $totalSoalCbt }}
                                </span>

                                <button type="button" 
                                        @click="nextSoal()"
                                        :disabled="currentPgNo === {{ !empty($soalCbt) ? array_key_last($soalCbt) : 1 }}"
                                        class="px-4 py-2 rounded-xl bg-blue-600 text-white font-semibold hover:bg-blue-700 disabled:opacity-40 disabled:cursor-not-allowed cursor-pointer flex items-center gap-1.5 transition shadow-2xs">
                                    Selanjutnya &rarr;
                                </button>
                            </div>
                        </div>
                    @endforeach
                </div>

                <!-- SISI KANAN: PALET NOMOR SOAL CBT (1/4 lebar) -->
                <div class="lg:col-span-1 space-y-4">
                    <div class="bg-white rounded-2xl border border-slate-200/90 shadow-2xs p-4 space-y-3 sticky top-20">
                        <div class="flex items-center justify-between border-b border-slate-100 pb-2">
                            <h4 class="font-bold text-xs sm:text-sm text-slate-900">Palet Nomor Soal</h4>
                            <span class="text-[10px] text-slate-400">CBT FR.IA.05</span>
                        </div>

                        <!-- Grid Tombol Nomor -->
                        <div class="grid grid-cols-5 gap-1.5 max-h-72 overflow-y-auto pr-1">
                            @foreach($soalCbt as $no => $item)
                                <button type="button" 
                                        @click="goToSoal({{ $no }})"
                                        class="h-9 rounded-lg font-bold text-xs transition flex items-center justify-center cursor-pointer border"
                                        :class="{
                                            'ring-2 ring-blue-500 ring-offset-1': currentPgNo === {{ $no }},
                                            'bg-emerald-600 text-white border-emerald-600 shadow-2xs': jawabanPg[{{ $no }}],
                                            'bg-slate-50 text-slate-700 border-slate-200 hover:bg-slate-100': !jawabanPg[{{ $no }}]
                                        }">
                                    {{ $no }}
                                </button>
                            @endforeach
                        </div>

                        <!-- Keterangan Warna Palet -->
                        <div class="pt-2 border-t border-slate-100 text-[10px] space-y-1 text-slate-500">
                            <div class="flex items-center gap-2">
                                <span class="w-3 h-3 rounded bg-emerald-600 shrink-0"></span>
                                <span>Sudah Terjawab (<span x-text="getTotalTerjawabCbt()"></span>)</span>
                            </div>
                            <div class="flex items-center gap-2">
                                <span class="w-3 h-3 rounded bg-slate-100 border border-slate-300 shrink-0"></span>
                                <span>Belum Terjawab (<span x-text="{{ $totalSoalCbt }} - getTotalTerjawabCbt()"></span>)</span>
                            </div>
                        </div>

                        @if(!$isReadonly)
                            <button type="button" 
                                    @click="showSubmitModal = true"
                                    class="w-full py-2 bg-emerald-600 hover:bg-emerald-700 text-white font-bold text-xs rounded-xl shadow-xs transition mt-2 cursor-pointer">
                                Selesai & Kumpulkan
                            </button>
                        @endif
                    </div>
                </div>

            </div>
        </div>
    @endif

    <!-- =========================================================================
         KONTEN TAB 2: FR.IA.06 ESAI TERTULIS
         ========================================================================= -->
    @if(!empty($soalEsai))
        <div x-show="activeTab === 'esai'" class="space-y-4">
            <div class="bg-white rounded-2xl border border-slate-200/90 shadow-2xs p-4 sm:p-6 space-y-4">
                <div class="border-b border-slate-100 pb-3">
                    <h3 class="text-sm sm:text-base font-bold text-slate-900">
                        FR.IA.06 &bull; Daftar Pertanyaan Tertulis Esai
                    </h3>
                    <p class="text-xs text-slate-500">
                        Tuliskan jawaban penjelasan Anda secara lengkap dan jelas sesuai standar kompetensi kerja.
                    </p>
                </div>

                <div class="space-y-4">
                    @foreach($soalEsai as $no => $esai)
                        <div class="p-4 rounded-xl border border-slate-200 bg-slate-50/40 space-y-2.5 text-xs sm:text-sm">
                            <div class="flex items-center justify-between">
                                <span class="px-2.5 py-0.5 rounded bg-blue-600 text-white text-[11px] font-bold">
                                    Pertanyaan {{ $no }}
                                </span>
                                <span class="text-[10px] text-slate-400">{{ $esai['kuk'] ?? 'SKKNI' }}</span>
                            </div>

                            <div class="font-medium text-slate-800 leading-relaxed">
                                {!! nl2br(e($esai['pertanyaan'])) !!}
                            </div>

                            <textarea rows="4" 
                                      :disabled="isReadonly"
                                      x-model="jawabanEsai[{{ $no }}]"
                                      @input.debounce.800ms="simpanJawabanEsai({{ $no }}, $event.target.value)"
                                      placeholder="Tuliskan jawaban penjelasan Anda di sini..."
                                      class="w-full p-3 rounded-xl border border-slate-300 bg-white text-xs sm:text-sm text-slate-800 focus:ring-2 focus:ring-blue-500 outline-hidden transition disabled:bg-slate-100 disabled:cursor-not-allowed"></textarea>
                        </div>
                    @endforeach
                </div>
            </div>
        </div>
    @endif

    <!-- =========================================================================
         KONTEN TAB 3: FR.IA.02 TUGAS PRAKTIK DEMONSTRASI
         ========================================================================= -->
    <div x-show="activeTab === 'praktik'" class="space-y-4">
        <div class="bg-white rounded-2xl border border-slate-200/90 shadow-2xs p-4 sm:p-6 space-y-4">
            <div class="border-b border-slate-100 pb-3">
                <h3 class="text-sm sm:text-base font-bold text-slate-900">
                    FR.IA.02 &bull; Tugas Praktik Demonstrasi & Proyek Kerja
                </h3>
                <p class="text-xs text-slate-500">
                    Petunjuk pelaksanaan demonstrasi langsung di Tempat Uji Kompetensi (TUK) dan pengunggahan berkas hasil kerja/proyek.
                </p>
            </div>

            <!-- Petunjuk Kerja Praktik -->
            <div class="p-4 rounded-xl bg-blue-50/60 border border-blue-200 space-y-2 text-xs">
                <span class="font-bold text-blue-900 block">Skenario / Instruksi Demonstrasi:</span>
                <p class="text-slate-700 leading-relaxed">
                    {{ $panduanPraktik['instruksi'] ?? 'Laksanakan observasi demonstrasi kerja sesuai SOP teknis dan instruksi yang diberikan oleh Asesor Penguji di tempat uji kompetensi.' }}
                </p>
            </div>

            <!-- Form Upload Bukti / Hasil Proyek Praktik -->
            <div class="p-4 rounded-xl border border-slate-200 bg-slate-50/50 space-y-3 text-xs">
                <span class="font-bold text-slate-800 block">Unggah Laporan Hasil Proyek / Demonstrasi (Opsional / Jika Diwajibkan):</span>
                
                @if($dokumenPraktik)
                    <div class="p-3 bg-emerald-50 border border-emerald-200 rounded-xl flex items-center justify-between">
                        <div>
                            <span class="font-bold text-emerald-900 block">{{ $dokumenPraktik->nama_dokumen }}</span>
                            <span class="text-[10px] text-emerald-700">Berkas telah diunggah & tersimpan</span>
                        </div>
                        <a href="{{ asset('storage/' . $dokumenPraktik->file_path) }}" target="_blank" class="px-3 py-1 bg-emerald-600 text-white rounded-lg font-bold text-[11px]">
                            Lihat File
                        </a>
                    </div>
                @endif

                @if(!$isReadonly)
                    <form action="{{ route('asesi.ujian.upload-ia02') }}" method="POST" enctype="multipart/form-data" class="space-y-3">
                        @csrf
                        <input type="hidden" name="pendaftaran_id" value="{{ $pendaftaran ? $pendaftaran->id : '' }}">
                        
                        <div>
                            <input type="file" name="file_dokumen" required class="block w-full text-xs text-slate-500 file:mr-3 file:py-2 file:px-3 file:rounded-xl file:border-0 file:text-xs file:font-semibold file:bg-blue-50 file:text-blue-700 hover:file:bg-blue-100">
                        </div>

                        <div>
                            <textarea name="catatan_praktik" rows="2" placeholder="Catatan penjelasan terkait berkas yang diunggah (opsional)..." class="w-full p-2.5 border border-slate-300 rounded-xl text-xs"></textarea>
                        </div>

                        <button type="submit" class="px-4 py-2 bg-blue-600 hover:bg-blue-700 text-white font-bold text-xs rounded-xl shadow-xs transition cursor-pointer">
                            Unggah Berkas Praktik
                        </button>
                    </form>
                @endif
            </div>
        </div>
    </div>

    <!-- =========================================================================
         MODAL SUBMIT UJIAN FINAL
         ========================================================================= -->
    <div x-show="showSubmitModal" 
         class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-slate-900/60 backdrop-blur-xs" 
         style="display: none;">
        <div class="bg-white rounded-2xl border border-slate-200 shadow-2xl max-w-md w-full p-5 sm:p-6 space-y-4">
            <div class="flex items-center gap-3">
                <div class="w-10 h-10 rounded-xl bg-emerald-100 text-emerald-700 flex items-center justify-center font-black shrink-0">
                    ✓
                </div>
                <div>
                    <h3 class="font-bold text-slate-900 text-base">Konfirmasi Pengumpulan Ujian</h3>
                    <p class="text-xs text-slate-500">Pastikan seluruh butir soal telah Anda periksa</p>
                </div>
            </div>

            <!-- Rekapitulasi pengerjaan -->
            <div class="bg-slate-50 border border-slate-200 rounded-xl p-3.5 text-xs space-y-1.5">
                <div class="flex justify-between">
                    <span class="text-slate-600">Soal Pilihan Ganda Terjawab:</span>
                    <strong class="text-slate-900" x-text="getTotalTerjawabCbt() + ' / {{ $totalSoalCbt }}'"></strong>
                </div>
                @if($totalSoalEsai > 0)
                    <div class="flex justify-between">
                        <span class="text-slate-600">Soal Esai Terjawab:</span>
                        <strong class="text-slate-900" x-text="getTotalTerjawabEsai() + ' / {{ $totalSoalEsai }}'"></strong>
                    </div>
                @endif
            </div>

            <p class="text-xs text-slate-600 leading-relaxed">
                Setelah mengumpulkan, Anda tidak dapat lagi mengubah jawaban. Jawaban akan langsung dikirimkan ke Asesor Penguji untuk dinilai.
            </p>

            <div class="flex items-center justify-end gap-2 pt-2 border-t border-slate-100">
                <button type="button" 
                        @click="showSubmitModal = false"
                        class="px-4 py-2 border border-slate-200 text-slate-600 rounded-xl font-bold text-xs hover:bg-slate-50 cursor-pointer">
                    Periksa Lagi
                </button>
                <form id="formSubmitUjianFinal" action="{{ route('asesi.ujian.submit') }}" method="POST">
                    @csrf
                    <input type="hidden" name="pendaftaran_id" value="{{ $pendaftaran ? $pendaftaran->id : '' }}">
                    <button type="submit" 
                            class="px-5 py-2 bg-emerald-600 hover:bg-emerald-700 text-white rounded-xl font-bold text-xs shadow-xs transition cursor-pointer">
                        Ya, Kumpulkan Sekarang
                    </button>
                </form>
            </div>
        </div>
    </div>

</div>
