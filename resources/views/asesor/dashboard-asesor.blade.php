@extends('tata-letak.dasbor')

@section('judul', 'Dashboard Asesor')

@section('konten')
@php
    $user = auth()->user();
    $totalAntrean = $totalPendingApl02;
@endphp

<div class="space-y-6" 
     x-data="{
        checkUrl: '{{ route('asesor.dashboard.heartbeat') }}',
        currentHash: '{{ $initialHash ?? '' }}',
        newUpdateToast: false,
        candidateName: '',
        schemeName: '',
        isChecking: false,
        pollTimer: null,

        init() {
            this.startPolling();
            document.addEventListener('visibilitychange', () => {
                if (document.hidden) {
                    this.stopPolling();
                } else {
                    this.checkUpdates();
                    this.startPolling();
                }
            });
        },

        startPolling() {
            this.stopPolling();
            this.pollTimer = setInterval(() => {
                if (!document.hidden && !this.isChecking) {
                    this.checkUpdates();
                }
            }, 5000); // Polling berkala setiap 5 detik saat tab aktif
        },

        stopPolling() {
            if (this.pollTimer) {
                clearInterval(this.pollTimer);
                this.pollTimer = null;
            }
        },

        async checkUpdates() {
            this.isChecking = true;
            try {
                const url = `${this.checkUrl}?hash=${encodeURIComponent(this.currentHash)}`;
                const res = await fetch(url, {
                    headers: {
                        'Accept': 'application/json',
                        'X-Requested-With': 'XMLHttpRequest'
                    },
                    cache: 'no-store'
                });

                if (res.status === 401 || res.redirected) {
                    window.location.reload();
                    return;
                }

                if (res.ok) {
                    const data = await res.json();
                    if (!this.currentHash) {
                        this.currentHash = data.hash;
                    } else if (data.changed || data.has_new) {
                        this.stopPolling();
                        this.candidateName = data.nama_asesi || 'Asesi Binaan';
                        this.schemeName = data.nama_skema || '';
                        this.newUpdateToast = true;

                        // Refresh halaman secara otomatis saat pembaruan masuk
                        setTimeout(() => {
                            window.location.reload();
                        }, 1000);
                    }
                }
            } catch(err) {
                console.debug('Dashboard asesor heartbeat error:', err);
            } finally {
                this.isChecking = false;
            }
        }
     }">

    <!-- Banner Toast Notifikasi Otomatis Saat Ada Pembaruan Data Asesi / Berkas Baru -->
    <div x-show="newUpdateToast" 
         x-transition:enter="transition ease-out duration-300 transform"
         x-transition:enter-start="-translate-y-4 opacity-0 scale-95"
         x-transition:enter-end="translate-y-0 opacity-100 scale-100"
         class="fixed top-5 right-5 z-50 max-w-md bg-white border-2 border-indigo-500 rounded-2xl shadow-2xl p-4 flex items-center gap-3.5"
         style="display: none;">
        <div class="w-10 h-10 rounded-xl bg-indigo-100 text-indigo-700 flex items-center justify-center font-black shrink-0 text-xs">
            BARU
        </div>
        <div class="space-y-0.5 flex-1 pr-2">
            <h4 class="text-xs font-bold text-slate-900">Pembaruan Data Asesi Diterima!</h4>
            <p class="text-[11px] text-slate-600 leading-tight">
                Terdapat pembaruan data/berkas dari <strong x-text="candidateName"></strong>. Memperbarui dashboard...
            </p>
        </div>
    </div>

    <!-- =========================================================================
         1. HERO / GREETING & IDENTITY CARD
         ========================================================================= -->
    <div class="bg-white rounded-3xl border border-slate-200/90 shadow-2xs p-5 sm:p-6 transition-all relative overflow-hidden">
        <!-- Subtle Decorative Background Pattern -->
        <div class="absolute -right-16 -top-16 w-56 h-56 bg-indigo-50/60 rounded-full blur-3xl pointer-events-none"></div>
        <div class="absolute right-20 -bottom-16 w-40 h-40 bg-blue-50/60 rounded-full blur-2xl pointer-events-none"></div>

        <div class="flex flex-col lg:flex-row lg:items-center justify-between gap-5 relative z-10">
            <!-- Left Info -->
            <div class="flex items-start sm:items-center gap-4">
                <div class="w-14 h-14 rounded-2xl bg-indigo-50 border border-indigo-100 text-indigo-700 flex items-center justify-center font-black text-xl shadow-xs shrink-0">
                    {{ strtoupper(substr($user->nama_lengkap ?? 'A', 0, 2)) }}
                </div>
                <div class="space-y-1">
                    <div class="flex items-center gap-2 flex-wrap">
                        <h1 class="text-lg sm:text-xl font-bold text-slate-900 leading-tight">
                            Selamat Datang, {{ $user->nama_lengkap }}
                        </h1>
                        <span class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-full bg-emerald-50 text-emerald-700 border border-emerald-200 text-[10px] font-bold">
                            <span class="w-1.5 h-1.5 rounded-full bg-emerald-500"></span> Asesor Aktif
                        </span>
                    </div>
                    <p class="text-xs text-slate-500 flex items-center gap-2 flex-wrap">
                        <span>Asesor Kompetensi LSP SMKN 1 Gunungputri</span>
                        @if($user->nomor_registrasi)
                            <span class="text-slate-300">&bull;</span>
                            <span class="font-mono text-indigo-600 font-bold bg-indigo-50/70 px-2 py-0.5 rounded border border-indigo-100">
                                No. Reg: {{ $user->nomor_registrasi }}
                            </span>
                        @endif
                    </p>
                </div>
            </div>

            <!-- Right Quick Actions -->
            <div class="flex items-center gap-2.5 flex-wrap">
                <a href="{{ route('asesor.penilaian') }}" 
                   class="px-4 py-2.5 rounded-xl bg-indigo-600 hover:bg-indigo-700 text-white font-bold text-xs shadow-xs transition-colors">
                    Mulai Penilaian Asesi
                </a>
                <a href="{{ route('asesor.mapa') }}" 
                   class="px-3.5 py-2.5 rounded-xl border border-slate-200 hover:bg-slate-50 text-slate-700 font-semibold text-xs transition-colors">
                    Formulir
                </a>
            </div>
        </div>
    </div>

    <!-- =========================================================================
         2. EXECUTIVE STAT CARDS (4 GRID)
         ========================================================================= -->
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
        
        <!-- STAT 1: JADWAL PENUGASAN -->
        <div class="bg-white rounded-2xl border border-slate-200/90 shadow-2xs p-4 space-y-2 hover:border-slate-300 transition-all">
            <div class="flex items-center justify-between text-slate-400">
                <span class="text-[11px] font-bold uppercase tracking-wider text-slate-500">Jadwal Penugasan</span>
                <span class="text-[10px] font-bold text-blue-600 px-2 py-0.5 rounded bg-blue-50 border border-blue-100">
                    JADWAL
                </span>
            </div>
            <div class="flex items-baseline gap-2">
                <span class="text-2xl sm:text-3xl font-extrabold text-slate-900">{{ $jadwalList->count() }}</span>
                <span class="text-xs text-slate-400 font-medium">Sesi Uji</span>
            </div>
            <div class="text-[11px] text-slate-500 pt-1 border-t border-slate-100 flex items-center justify-between">
                <span>Jadwal aktif & mendatang</span>
                <a href="{{ route('asesor.jadwal') }}" class="text-blue-600 hover:underline font-semibold">Lihat &rarr;</a>
            </div>
        </div>

        <!-- STAT 2: TOTAL ASESI -->
        <div class="bg-white rounded-2xl border border-slate-200/90 shadow-2xs p-4 space-y-2 hover:border-slate-300 transition-all">
            <div class="flex items-center justify-between text-slate-400">
                <span class="text-[11px] font-bold uppercase tracking-wider text-slate-500">Total Asesi Bimbingan</span>
                <span class="text-[10px] font-bold text-indigo-600 px-2 py-0.5 rounded bg-indigo-50 border border-indigo-100">
                    ASESI
                </span>
            </div>
            <div class="flex items-baseline gap-2">
                <span class="text-2xl sm:text-3xl font-extrabold text-indigo-700">{{ $totalAsesi }}</span>
                <span class="text-xs text-slate-400 font-medium">Peserta</span>
            </div>
            <div class="text-[11px] text-slate-500 pt-1 border-t border-slate-100 flex items-center justify-between">
                <span>Seluruh pendaftar binaan</span>
                <a href="{{ route('asesor.daftar-peserta') }}" class="text-indigo-600 hover:underline font-semibold">Kelola &rarr;</a>
            </div>
        </div>

        <!-- STAT 3: ANTREAN VERIFIKASI (ALERT CARD) -->
        <div class="bg-white rounded-2xl border {{ $totalAntrean > 0 ? 'border-amber-200 bg-amber-50/10' : 'border-slate-200/90' }} shadow-2xs p-4 space-y-2 hover:border-amber-300 transition-all">
            <div class="flex items-center justify-between">
                <span class="text-[11px] font-bold uppercase tracking-wider {{ $totalAntrean > 0 ? 'text-amber-700' : 'text-slate-500' }}">
                    Antrean Tindakan
                </span>
                <span class="text-[10px] font-bold {{ $totalAntrean > 0 ? 'text-amber-700 bg-amber-100 border border-amber-200' : 'text-slate-500 bg-slate-100 border border-slate-200' }} px-2 py-0.5 rounded">
                    ANTREAN
                </span>
            </div>
            <div class="flex items-baseline gap-2">
                <span class="text-2xl sm:text-3xl font-extrabold {{ $totalAntrean > 0 ? 'text-amber-600' : 'text-slate-900' }}">
                    {{ $totalAntrean }}
                </span>
                <span class="text-xs text-slate-400 font-medium">Perlu Respon</span>
            </div>
            <div class="text-[11px] text-slate-500 pt-1 border-t border-slate-100 flex items-center justify-between">
                <span>{{ $totalPendingApl02 }} APL.02 Menunggu</span>
                @if($totalAntrean > 0)
                    <span class="text-amber-700 font-bold">Urgent</span>
                @else
                    <span class="text-emerald-600 font-medium">Clear</span>
                @endif
            </div>
        </div>

        <!-- STAT 4: REKOMENDASI TERKIRIM -->
        <div class="bg-white rounded-2xl border border-slate-200/90 shadow-2xs p-4 space-y-2 hover:border-slate-300 transition-all">
            <div class="flex items-center justify-between text-slate-400">
                <span class="text-[11px] font-bold uppercase tracking-wider text-slate-500">Asesmen Selesai</span>
                <span class="text-[10px] font-bold text-emerald-600 px-2 py-0.5 rounded bg-emerald-50 border border-emerald-100">
                    SELESAI
                </span>
            </div>
            <div class="flex items-baseline gap-2">
                <span class="text-2xl sm:text-3xl font-extrabold text-emerald-700">{{ $totalPenilaian }}</span>
                <span class="text-xs text-slate-400 font-medium">Asesi Dinilai</span>
            </div>
            <div class="text-[11px] text-slate-500 pt-1 border-t border-slate-100 flex items-center justify-between">
                <span>Rekomendasi K / BK</span>
                <a href="{{ route('asesor.berita-acara') }}" class="text-emerald-600 hover:underline font-semibold">Rekap &rarr;</a>
            </div>
        </div>

    </div>


    <!-- =========================================================================
         3. TWO-COLUMN WORKSPACE: LEFT (QUEUES & SCHEDULES) | RIGHT (SIDEBAR WIDGETS)
         ========================================================================= -->
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">

        <!-- LEFT COLUMN (2 SPAN): ANTREAN VERIFIKASI & JADWAL UJI -->
        <div class="lg:col-span-2 space-y-6">
            
            <!-- SECTION A: ANTREAN TINDAKAN MENDESAK (APL.02 / AK.01) -->
            <div class="bg-white rounded-3xl border border-slate-200/90 shadow-2xs overflow-hidden">
                <div class="px-5 py-4 bg-slate-50/70 border-b border-slate-200/80 flex items-center justify-between gap-3">
                    <div class="flex items-center gap-2.5">
                        <div class="w-2 h-2 rounded-full {{ $totalAntrean > 0 ? 'bg-amber-500 animate-pulse' : 'bg-emerald-500' }}"></div>
                        <h2 class="font-bold text-xs sm:text-sm text-slate-900">
                            Antrean Tindakan Asesor (FR.APL.02 & FR.AK.01)
                        </h2>
                    </div>
                    <span class="text-[11px] font-semibold px-2.5 py-0.5 rounded-full {{ $totalAntrean > 0 ? 'bg-amber-100 text-amber-800' : 'bg-emerald-100 text-emerald-800' }}">
                        {{ $antreanVerifikasi->count() }} Menunggu
                    </span>
                </div>

                @if($antreanVerifikasi->count() > 0)
                    <div class="divide-y divide-slate-100">
                        @foreach($antreanVerifikasi as $item)
                            <div class="p-4 hover:bg-slate-50/60 transition-colors flex flex-col sm:flex-row sm:items-center justify-between gap-3">
                                <div class="flex items-start gap-3">
                                    <div class="w-9 h-9 rounded-xl bg-slate-100 text-slate-700 flex items-center justify-center font-bold text-xs shrink-0 mt-0.5">
                                        {{ strtoupper(substr($item->asesi->nama_lengkap ?? 'U', 0, 2)) }}
                                    </div>
                                    <div class="space-y-0.5">
                                        <div class="font-bold text-xs text-slate-900 leading-tight">
                                            {{ $item->asesi->nama_lengkap ?? '-' }}
                                        </div>
                                        <div class="text-[11px] text-slate-500">
                                            {{ $item->skema->nama_skema ?? '-' }} &bull; <span class="font-mono">{{ $item->nomor_pendaftaran }}</span>
                                        </div>
                                        <div class="flex items-center gap-2 pt-1">
                                            @if($item->status_apl02 === 'submitted' || $item->status_apl02 === 'under_review')
                                                <span class="px-2 py-0.5 rounded-md bg-amber-50 text-amber-700 border border-amber-200 text-[10px] font-semibold">
                                                    FR.APL.02 Menunggu Verifikasi
                                                </span>
                                            @elseif(in_array($item->status_apl02, ['revision_requested', 'revision']))
                                                <span class="px-2 py-0.5 rounded-md bg-rose-50 text-rose-700 border border-rose-200 text-[10px] font-semibold">
                                                    FR.APL.02 Perlu Tinjauan Revisi
                                                </span>
                                            @endif
                                            <span class="text-[10px] text-slate-400">
                                                {{ $item->updated_at ? $item->updated_at->diffForHumans() : '' }}
                                            </span>
                                        </div>
                                    </div>
                                </div>

                                <div class="shrink-0 sm:self-center">
                                    <a href="{{ route('asesor.input-penilaian', $item->id) }}" 
                                       class="inline-flex items-center px-3.5 py-1.5 rounded-xl bg-indigo-50 hover:bg-indigo-100 text-indigo-700 text-xs font-bold border border-indigo-200 transition-colors">
                                        Proses Verifikasi
                                    </a>
                                </div>
                            </div>
                        @endforeach
                    </div>
                @else
                    <div class="p-8 text-center space-y-2">
                        <h3 class="font-bold text-xs sm:text-sm text-slate-800">Semua Antrean Selesai Diproses</h3>
                        <p class="text-xs text-slate-500 max-w-sm mx-auto">
                            Tidak ada berkas APL.02 atau AK.01 yang menunggu tindakan Anda saat ini.
                        </p>
                    </div>
                @endif
            </div>

            <!-- SECTION B: JADWAL UJI KOMPETENSI MENDATANG -->
            <div class="bg-white rounded-3xl border border-slate-200/90 shadow-2xs overflow-hidden">
                <div class="px-5 py-4 bg-slate-50/70 border-b border-slate-200/80 flex items-center justify-between gap-3">
                    <h2 class="font-bold text-xs sm:text-sm text-slate-900">
                        Jadwal Penugasan Uji Kompetensi
                    </h2>
                    <a href="{{ route('asesor.jadwal') }}" class="text-xs text-blue-600 hover:text-blue-800 font-semibold">
                        Lihat Semua &rarr;
                    </a>
                </div>

                <div class="overflow-x-auto">
                    <table class="w-full text-left border-collapse text-xs">
                        <thead>
                            <tr class="bg-slate-50/50 border-b border-slate-200 text-[11px] font-bold text-slate-500 uppercase tracking-wider">
                                <th class="py-2.5 px-4">Kode & Skema</th>
                                <th class="py-2.5 px-4">Tanggal & Waktu</th>
                                <th class="py-2.5 px-4">Tempat Uji (TUK)</th>
                                <th class="py-2.5 px-4 text-center">Peserta</th>
                                <th class="py-2.5 px-4 text-right">Aksi</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100">
                            @forelse($jadwalList->take(5) as $j)
                                @php
                                     $isJadwalAktif = $j->isWaktuAktif();
                                @endphp
                                <tr class="hover:bg-slate-50/60 transition-colors {{ $isJadwalAktif ? 'bg-emerald-50/20' : '' }}">
                                    <td class="py-3 px-4">
                                        <div class="flex items-center gap-2">
                                            <span class="font-mono font-bold text-indigo-700 text-[11px]">{{ $j->kode_jadwal }}</span>
                                            @if($isJadwalAktif)
                                                <span class="inline-flex items-center gap-1.5 px-2 py-0.5 rounded-full bg-emerald-100 text-emerald-800 border border-emerald-300 font-extrabold text-[10px] animate-pulse">
                                                    <span class="w-1.5 h-1.5 rounded-full bg-emerald-500 animate-ping"></span>
                                                    <span>Live Dimulai</span>
                                                </span>
                                            @endif
                                        </div>
                                        <div class="font-semibold text-slate-900 truncate max-w-[200px]" title="{{ $j->skema->nama_skema ?? '-' }}">
                                            {{ $j->skema->nama_skema ?? '-' }}
                                        </div>
                                    </td>
                                    <td class="py-3 px-4 text-slate-700 whitespace-nowrap">
                                        <div class="font-semibold">{{ date('d M Y', strtotime($j->tanggal_uji)) }}</div>
                                        <div class="text-[11px] text-slate-500">{{ $j->waktu_mulai }} WIB</div>
                                    </td>
                                    <td class="py-3 px-4 text-slate-600 text-[11px]">
                                        <div class="font-medium text-slate-800">{{ $j->nama_tuk ?? 'TUK LSP' }}</div>
                                        <div class="text-slate-400">{{ $j->tipe_tuk ?? 'Sewaktu' }}</div>
                                    </td>
                                    <td class="py-3 px-4 text-center">
                                        <span class="inline-flex items-center px-2 py-0.5 rounded-full bg-slate-100 text-slate-700 font-bold text-[10px]">
                                            {{ $j->pendaftaranAsesi->count() }} Asesi
                                        </span>
                                    </td>
                                    <td class="py-3 px-4 text-right whitespace-nowrap">
                                        <a href="{{ route('asesor.daftar-peserta', ['jadwal_id' => $j->id]) }}" 
                                           class="px-2.5 py-1.5 rounded-lg {{ $isJadwalAktif ? 'bg-emerald-600 hover:bg-emerald-700 text-white shadow-2xs font-bold' : 'border border-slate-200 hover:bg-slate-100 text-slate-700 font-semibold' }} text-[11px] transition-colors inline-flex items-center">
                                            @if($isJadwalAktif)
                                                <span>Uji Live</span>
                                            @else
                                                <span>Daftar Peserta</span>
                                            @endif
                                        </a>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="5" class="py-8 text-center text-slate-400 italic">
                                        Belum ada jadwal penugasan asesmen aktif untuk Anda.
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>

        </div>

        <!-- RIGHT COLUMN (1 SPAN): PINTASAN MUK & REKAP TERAKHIR -->
        <div class="space-y-6">

            <!-- WIDGET 1: PINTASAN INSTRUMEN BNSP RESMI -->
            <div class="bg-white rounded-3xl border border-slate-200/90 shadow-2xs p-5 space-y-3">
                <h3 class="font-bold text-xs sm:text-sm text-slate-900">
                    Pintasan Instrumen BNSP
                </h3>

                <div class="grid grid-cols-1 gap-2 pt-1 text-xs">
                    <a href="{{ route('asesor.mapa') }}" 
                       class="p-3 rounded-2xl border border-slate-100 bg-slate-50/60 hover:bg-indigo-50/50 hover:border-indigo-200 transition-all flex items-center justify-between group">
                        <div class="space-y-0.5">
                            <div class="font-bold text-slate-800 group-hover:text-indigo-700 transition-colors">FR.MAPA.01 & MAPA.02</div>
                            <div class="text-[11px] text-slate-400">Penyusunan rencana & perangkat uji</div>
                        </div>
                        <span class="text-xs text-slate-300 group-hover:text-indigo-600 font-bold transition-colors">&rarr;</span>
                    </a>

                    <a href="{{ route('asesor.penilaian') }}" 
                       class="p-3 rounded-2xl border border-slate-100 bg-slate-50/60 hover:bg-indigo-50/50 hover:border-indigo-200 transition-all flex items-center justify-between group">
                        <div class="space-y-0.5">
                            <div class="font-bold text-slate-800 group-hover:text-indigo-700 transition-colors">FR.APL.02 & FR.AK.01</div>
                            <div class="text-[11px] text-slate-400">Verifikasi portofolio & persetujuan</div>
                        </div>
                        <span class="text-xs text-slate-300 group-hover:text-indigo-600 font-bold transition-colors">&rarr;</span>
                    </a>

                    <a href="{{ route('asesor.berita-acara') }}" 
                       class="p-3 rounded-2xl border border-slate-100 bg-slate-50/60 hover:bg-indigo-50/50 hover:border-indigo-200 transition-all flex items-center justify-between group">
                        <div class="space-y-0.5">
                            <div class="font-bold text-slate-800 group-hover:text-indigo-700 transition-colors">FR.AK.05 & Berita Acara</div>
                            <div class="text-[11px] text-slate-400">Laporan asesmen & rekapitulasi nilai</div>
                        </div>
                        <span class="text-xs text-slate-300 group-hover:text-indigo-600 font-bold transition-colors">&rarr;</span>
                    </a>

                </div>
            </div>

            <!-- WIDGET 2: REKAPITULASI HASIL KELULUSAN TERAKHIR -->
            <div class="bg-white rounded-3xl border border-slate-200/90 shadow-2xs p-5 space-y-3">
                <div class="flex items-center justify-between">
                    <h3 class="font-bold text-xs sm:text-sm text-slate-900">
                        Keputusan Asesmen Terkini
                    </h3>
                    <span class="text-[10px] font-bold text-slate-400 uppercase">Terbaru</span>
                </div>

                @if($rekomendasiTerbaru->count() > 0)
                    <div class="divide-y divide-slate-100 text-xs">
                        @foreach($rekomendasiTerbaru as $rek)
                            <div class="py-2.5 space-y-1">
                                <div class="flex items-center justify-between">
                                    <div class="font-bold text-slate-900 truncate max-w-[160px]">
                                        {{ $rek->pendaftaran->asesi->nama_lengkap ?? 'Asesi' }}
                                    </div>
                                    @if(str_contains(strtolower($rek->keputusan ?? ''), 'belum') || str_contains(strtolower($rek->keputusan ?? ''), 'bk'))
                                        <span class="px-2 py-0.5 rounded-full bg-rose-50 text-rose-700 border border-rose-200 font-bold text-[10px]">
                                            BK (Belum Kompeten)
                                        </span>
                                    @else
                                        <span class="px-2 py-0.5 rounded-full bg-emerald-50 text-emerald-700 border border-emerald-200 font-bold text-[10px]">
                                            K (Kompeten)
                                        </span>
                                    @endif
                                </div>
                                <div class="text-[11px] text-slate-400 flex items-center justify-between">
                                    <span class="truncate max-w-[170px]">{{ $rek->pendaftaran->skema->nama_skema ?? '-' }}</span>
                                    <span>{{ $rek->tanggal_rekomendasi ? date('d/m/y', strtotime($rek->tanggal_rekomendasi)) : '-' }}</span>
                                </div>
                            </div>
                        @endforeach
                    </div>
                @else
                    <div class="py-6 text-center text-slate-400 italic text-xs">
                        Belum ada keputusan rekomendasi yang diterbitkan.
                    </div>
                @endif
            </div>

            <!-- WIDGET 3: CATATAN INTEGRITAS & BNSP COMPLIANCE -->
            <div class="bg-indigo-50/50 rounded-3xl border border-indigo-100 p-4 space-y-2 text-xs text-indigo-900 leading-relaxed">
                <div class="font-bold text-indigo-800">
                    Prinsip Asesmen BNSP
                </div>
                <p class="text-[11px] text-indigo-700">
                    Pastikan seluruh proses verifikasi bukti portofolio dan asesmen unjuk kerja memenuhi prinsip <strong>Valid, Asli, Terkini, dan Memadai (VATM)</strong> serta berpegang pada standar kerahasiaan materi uji.
                </p>
            </div>

        </div>

    </div>
</div>
@endsection
