@extends('tata-letak.dasbor')

@section('judul', 'Dashboard Admin LSP')

@push('css')
    <link rel="stylesheet" href="{{ asset('css/admin/dashboard-admin.css') }}">
@endpush

@section('konten')
<div class="space-y-6" 
     x-data="{
        checkUrl: '{{ route('admin.cek-pendaftaran-terbaru') }}',
        lastId: {{ $latestPendaftaranId ?? 0 }},
        lastHash: '{{ $latestHash ?? '' }}',
        newRegistrationToast: false,
        newCandidateName: '',
        newSchemeName: '',
        pollTimer: null,

        init() {
            this.startPolling();
            document.addEventListener('visibilitychange', () => {
                if (document.hidden) {
                    this.stopPolling();
                } else {
                    this.checkNewRegistration();
                    this.startPolling();
                }
            });
        },

        startPolling() {
            this.stopPolling();
            this.pollTimer = setInterval(() => {
                this.checkNewRegistration();
            }, 5000); // Cek secara berkala setiap 5 detik
        },

        stopPolling() {
            if (this.pollTimer) {
                clearInterval(this.pollTimer);
                this.pollTimer = null;
            }
        },

        async checkNewRegistration() {
            try {
                const res = await fetch(`${this.checkUrl}?last_id=${this.lastId}&last_hash=${this.lastHash}`, {
                    headers: { 'Accept': 'application/json' }
                });
                if (res.ok) {
                    const data = await res.json();
                    if (data.has_new) {
                        this.stopPolling();
                        this.newCandidateName = data.nama_asesi || 'Asesi Baru';
                        this.newSchemeName = data.nama_skema || '';
                        this.newRegistrationToast = true;

                        // Refresh halaman secara otomatis saat pengajuan baru masuk
                        setTimeout(() => {
                            window.location.reload();
                        }, 1000);
                    }
                }
            } catch(e) {
                // Abaikan kesalahan koneksi sementara
            }
        }
     }">

    <!-- Banner Toast Notifikasi Otomatis Saat Ada Pengajuan Pendaftaran Baru -->
    <div x-show="newRegistrationToast" 
         x-transition:enter="transition ease-out duration-300 transform"
         x-transition:enter-start="-translate-y-4 opacity-0 scale-95"
         x-transition:enter-end="translate-y-0 opacity-100 scale-100"
         class="fixed top-5 right-5 z-50 max-w-md bg-white border-2 border-emerald-500 rounded-2xl shadow-2xl p-4 flex items-center gap-3.5"
         style="display: none;">
        <div class="w-10 h-10 rounded-xl bg-emerald-100 text-emerald-600 flex items-center justify-center font-bold shrink-0 text-base">
            <i class="fa-solid fa-user-plus animate-bounce"></i>
        </div>
        <div class="space-y-0.5 flex-1 pr-2">
            <h4 class="text-xs font-bold text-slate-900">Pengajuan Pendaftaran Baru Masuk!</h4>
            <p class="text-[11px] text-slate-600 leading-tight">
                <strong x-text="newCandidateName"></strong> telah mengajukan berkas. Memperbarui dashboard...
            </p>
        </div>
        <div class="shrink-0 text-emerald-600">
            <i class="fa-solid fa-circle-notch animate-spin text-sm"></i>
        </div>
    </div>

    <!-- =========================================================================
         1. HEADER DASHBOARD (BERSIH & MINIMALIS)
         ========================================================================= -->
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
        <div>
            <h1 class="text-xl font-bold text-slate-800 tracking-tight">Ringkasan Eksekutif</h1>
            <p class="text-xs text-slate-500 mt-0.5">
                Pantau verifikasi berkas APL, skema sertifikasi, dan aktivitas asesmen terkini.
            </p>
        </div>
        <div class="flex items-center gap-2.5">
            <span class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-xl border border-slate-200/90 bg-white text-slate-600 font-medium text-xs shadow-2xs">
                <span class="w-2 h-2 rounded-full bg-emerald-500"></span>
                <span>Periode {{ date('Y') }}</span>
            </span>
        </div>
    </div>

    <!-- =========================================================================
         2. KARTU STATISTIK METRIK (4 STATS CARDS)
         ========================================================================= -->
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
        <!-- Metrik 1: Total Asesi -->
        <div class="bg-white p-4 rounded-xl border border-slate-200/80 shadow-xs flex items-center justify-between">
            <div>
                <span class="text-xs text-slate-500 font-medium block">Total Asesi Terdaftar</span>
                <div class="text-2xl font-bold text-slate-800 mt-1">{{ number_format($totalAsesi) }}</div>
            </div>
            <div class="p-2.5 rounded-lg bg-blue-50 text-blue-600 flex items-center justify-center flex-shrink-0">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"/>
                </svg>
            </div>
        </div>

        <!-- Metrik 2: Menunggu Verifikasi -->
        <div class="bg-white p-4 rounded-xl border border-slate-200/80 shadow-xs flex items-center justify-between">
            <div>
                <span class="text-xs text-slate-500 font-medium block">Menunggu Verifikasi APL</span>
                <div class="text-2xl font-bold text-amber-600 mt-1">{{ number_format($totalPendingVerifikasi) }}</div>
            </div>
            <div class="p-2.5 rounded-lg bg-amber-50 text-amber-600 flex items-center justify-center flex-shrink-0">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
                </svg>
            </div>
        </div>

        <!-- Metrik 3: Berkas Terverifikasi -->
        <div class="bg-white p-4 rounded-xl border border-slate-200/80 shadow-xs flex items-center justify-between">
            <div>
                <span class="text-xs text-slate-500 font-medium block">APL Disetujui</span>
                <div class="text-2xl font-bold text-emerald-600 mt-1">{{ number_format($totalTerverifikasi) }}</div>
            </div>
            <div class="p-2.5 rounded-lg bg-emerald-50 text-emerald-600 flex items-center justify-center flex-shrink-0">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                </svg>
            </div>
        </div>

        <!-- Metrik 4: Master Skema Aktif -->
        <div class="bg-white p-4 rounded-xl border border-slate-200/80 shadow-xs flex items-center justify-between">
            <div>
                <span class="text-xs text-slate-500 font-medium block">Skema Sertifikasi</span>
                <div class="text-2xl font-bold text-slate-800 mt-1">{{ number_format($totalSkema) }}</div>
            </div>
            <div class="p-2.5 rounded-lg bg-slate-100 text-slate-600 flex items-center justify-center flex-shrink-0">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10"/>
                </svg>
            </div>
        </div>
    </div>

    <!-- =========================================================================
         3. TABEL PENDAFTARAN & VERIFIKASI TERBARU (MODERN & COMPACT)
         ========================================================================= -->
    <div class="bg-white rounded-xl border border-[#dce7f2] shadow-xs overflow-hidden">
        <!-- Header Card Tabel -->
        <div class="bg-[#f0f6fb] px-5 py-3.5 border-b border-[#dce7f2] flex items-center justify-between">
            <h3 class="text-sm font-bold text-[#1c2d42]">Pengajuan Pendaftaran Terbaru</h3>
            <a href="{{ route('admin.verifikasi-berkas') }}" 
               class="text-xs text-[#4682b4] hover:text-[#36648b] font-semibold flex items-center gap-1 transition-colors">
                <span>Lihat Semua Antrean</span>
                <span>&rarr;</span>
            </a>
        </div>

        <!-- Tabel Kontainer (Fit 100% Layar Tanpa Horizontal Scroll) -->
        <div class="w-full overflow-x-auto">
            <table class="w-full text-left text-xs table-auto">
                <thead class="bg-[#f0f6fb] border-b border-[#dce7f2] text-[#1c2d42] font-bold text-[11px] uppercase tracking-wider">
                    <tr>
                        <th class="px-4 py-3.5">Nama Asesi</th>
                        <th class="px-4 py-3.5">Skema Sertifikasi</th>
                        <th class="px-4 py-3.5 whitespace-nowrap">Tanggal Pengajuan</th>
                        <th class="px-4 py-3.5 whitespace-nowrap">Status</th>
                        <th class="px-4 py-3.5 text-center whitespace-nowrap">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @forelse($pendaftaranTerbaru as $p)
                        <tr class="hover:bg-[#f8fbfe] transition-colors">
                            <!-- Nama Asesi & NIK/NISN -->
                            <td class="px-4 py-3.5">
                                <div class="font-bold text-slate-800">{{ $p->asesi->nama_lengkap }}</div>
                                <div class="text-[11px] text-slate-400 font-mono mt-0.5">
                                    NIK: {{ $p->asesi->profilAsesi->nik ?? '-' }}
                                    @if(optional($p->asesi->profilAsesi)->nisn)
                                        &bull; NISN: {{ $p->asesi->profilAsesi->nisn }}
                                    @endif
                                </div>
                            </td>

                            <!-- Skema Sertifikasi -->
                            <td class="px-4 py-3.5">
                                <div class="font-medium text-slate-700 max-w-xs truncate" title="{{ $p->skema->nama_skema }}">
                                    {{ $p->skema->nama_skema }}
                                </div>
                                <div class="text-[10px] text-slate-400 font-mono mt-0.5">
                                    {{ $p->skema->kode_skema }}
                                </div>
                            </td>

                            <!-- Tanggal Pengajuan -->
                            <td class="px-4 py-3.5 text-slate-500 whitespace-nowrap">
                                {{ $p->created_at ? $p->created_at->format('d M Y') : '-' }}
                            </td>

                            <!-- Status Badge -->
                            <td class="px-4 py-3.5 whitespace-nowrap">
                                @if($p->status_pendaftaran === 'diajukan')
                                    <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full font-medium text-[11px] bg-amber-50 text-amber-700 border border-amber-200">
                                        <span class="w-1.5 h-1.5 rounded-full bg-amber-500"></span>
                                        <span>Menunggu Verifikasi</span>
                                    </span>
                                @elseif($p->status_pendaftaran === 'diverifikasi')
                                    <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full font-medium text-[11px] bg-emerald-50 text-emerald-700 border border-emerald-200">
                                        <span class="w-1.5 h-1.5 rounded-full bg-emerald-500"></span>
                                        <span>Disetujui (ACC)</span>
                                    </span>
                                @elseif($p->status_pendaftaran === 'revisi')
                                    <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full font-medium text-[11px] bg-rose-50 text-rose-700 border border-rose-200">
                                        <span class="w-1.5 h-1.5 rounded-full bg-rose-500"></span>
                                        <span>Perlu Revisi</span>
                                    </span>
                                @else
                                    <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full font-medium text-[11px] bg-rose-50 text-rose-700 border border-rose-200">
                                        <span class="w-1.5 h-1.5 rounded-full bg-rose-500"></span>
                                        <span>Ditolak</span>
                                    </span>
                                @endif
                            </td>

                            <!-- Tombol Aksi -->
                            <td class="px-4 py-3.5 text-center whitespace-nowrap">
                                <a href="{{ route('admin.detail-verifikasi', $p->id) }}" 
                                   class="inline-flex items-center justify-center gap-1 px-3 py-1.5 bg-[#4682b4] hover:bg-[#36648b] text-white rounded-lg text-xs font-semibold transition-colors shadow-2xs">
                                    <span>Periksa</span>
                                    <span>&rarr;</span>
                                </a>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="px-6 py-12 text-center text-slate-400 text-xs">
                                Belum ada pengajuan pendaftaran baru yang perlu diverifikasi.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection
