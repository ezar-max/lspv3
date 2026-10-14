@extends('tata-letak.dasbor')

@section('judul', 'Asesi - Data & Verifikasi')

@push('css')
    <link rel="stylesheet" href="{{ asset('css/admin/dashboard-admin.css') }}">
    <style>
        .animasi-fade-in {
            animation: fadeIn 0.3s cubic-bezier(0.16, 1, 0.3, 1);
        }
        @keyframes fadeIn {
            from { opacity: 0; transform: translateY(6px); }
            to { opacity: 1; transform: translateY(0); }
        }
    </style>
@endpush

@section('konten')
<div class="animasi-slide space-y-6 max-w-7xl mx-auto pb-10">

    <!-- PAGE HEADER -->
    <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-4">
        <div>
            <div class="inline-flex items-center gap-2 px-2.5 py-1 rounded-lg bg-blue-50 border border-blue-200/80 text-blue-700 text-xs font-bold mb-2">
                <i class="fa-solid fa-id-card-clip text-blue-600"></i>
                <span>Portal Master Asesi</span>
            </div>
            <h1 class="text-2xl sm:text-3xl font-extrabold text-slate-900 tracking-tight">
                Data Asesi &amp; Verifikasi Berkas
            </h1>
            <p class="text-xs sm:text-sm text-slate-500 mt-1">
                Pusat administrasi master biodata peserta asesi, riwayat skema sertifikasi, dan verifikasi berkas permohonan APL-01
            </p>
        </div>
        <div class="flex items-center gap-2">
            <span class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-xl bg-slate-100 text-slate-600 text-xs font-semibold border border-slate-200">
                <i class="fa-regular fa-clock text-slate-400"></i>
                Diperbarui {{ now()->translatedFormat('d M Y') }}
            </span>
        </div>
    </div>

    <!-- STATS COUNTER CARDS (4 METRICS) -->
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
        <!-- 1. Total Asesi -->
        <a href="{{ route('admin.manajemen-asesi', ['tab' => 'data-asesi']) }}" 
           class="group bg-white rounded-2xl border p-4.5 transition-all duration-200 hover:-translate-y-0.5 hover:shadow-md cursor-pointer block {{ ($tab ?? 'data-asesi') === 'data-asesi' && empty($skemaFilter) ? 'border-blue-400 ring-2 ring-blue-500/10 shadow-xs' : 'border-slate-200/90 shadow-2xs' }}">
            <div class="flex items-center justify-between">
                <div>
                    <div class="text-[11px] font-bold text-slate-500 uppercase tracking-wider">Total Asesi Terdaftar</div>
                    <div class="text-2xl sm:text-3xl font-black text-slate-900 mt-1 group-hover:text-blue-600 transition-colors">
                        {{ number_format($totalAsesi ?? 0) }}
                    </div>
                    <div class="text-[11px] text-slate-400 mt-1 flex items-center gap-1">
                        <span>Database master akun</span>
                    </div>
                </div>
                <div class="w-12 h-12 rounded-2xl bg-blue-50 text-blue-600 border border-blue-100 flex items-center justify-center text-xl font-bold group-hover:scale-105 group-hover:bg-blue-600 group-hover:text-white transition-all">
                    <i class="fa-solid fa-users"></i>
                </div>
            </div>
        </a>

        <!-- 2. Belum Mendaftar Skema -->
        <a href="{{ route('admin.manajemen-asesi', ['tab' => 'data-asesi', 'skema' => 'belum_daftar']) }}" 
           class="group bg-white rounded-2xl border p-4.5 transition-all duration-200 hover:-translate-y-0.5 hover:shadow-md cursor-pointer block {{ ($skemaFilter ?? '') === 'belum_daftar' ? 'border-amber-400 ring-2 ring-amber-500/10 shadow-xs' : 'border-slate-200/90 shadow-2xs' }}">
            <div class="flex items-center justify-between">
                <div>
                    <div class="text-[11px] font-bold text-amber-700 uppercase tracking-wider">Belum Daftar Skema</div>
                    <div class="text-2xl sm:text-3xl font-black text-amber-600 mt-1">
                        {{ number_format($totalBelumDaftar ?? 0) }}
                    </div>
                    <div class="text-[11px] text-slate-400 mt-1">
                        <span>Belum ajukan APL-01</span>
                    </div>
                </div>
                <div class="w-12 h-12 rounded-2xl bg-amber-50 text-amber-600 border border-amber-100 flex items-center justify-center text-xl font-bold group-hover:scale-105 group-hover:bg-amber-500 group-hover:text-white transition-all">
                    <i class="fa-solid fa-user-clock"></i>
                </div>
            </div>
        </a>

        <!-- 3. Menunggu Verifikasi -->
        <a href="{{ route('admin.manajemen-asesi', ['tab' => 'verifikasi', 'status' => 'diajukan']) }}" 
           class="group bg-white rounded-2xl border p-4.5 transition-all duration-200 hover:-translate-y-0.5 hover:shadow-md cursor-pointer block {{ ($tab ?? '') === 'verifikasi' && ($statusVerifikasi ?? '') === 'diajukan' ? 'border-amber-400 ring-2 ring-amber-500/10 shadow-xs' : 'border-slate-200/90 shadow-2xs' }}">
            <div class="flex items-center justify-between">
                <div>
                    <div class="text-[11px] font-bold text-amber-700 uppercase tracking-wider">Menunggu Verifikasi</div>
                    <div class="text-2xl sm:text-3xl font-black text-amber-600 mt-1 flex items-center gap-2">
                        <span>{{ number_format($totalPendingVerifikasi ?? 0) }}</span>
                        @if(($totalPendingVerifikasi ?? 0) > 0)
                            <span class="w-2.5 h-2.5 rounded-full bg-amber-500 animate-ping inline-block"></span>
                        @endif
                    </div>
                    <div class="text-[11px] text-slate-400 mt-1">
                        <span>Berkas permohonan baru</span>
                    </div>
                </div>
                <div class="w-12 h-12 rounded-2xl bg-amber-50 text-amber-600 border border-amber-100 flex items-center justify-center text-xl font-bold group-hover:scale-105 group-hover:bg-amber-500 group-hover:text-white transition-all">
                    <i class="fa-solid fa-hourglass-half"></i>
                </div>
            </div>
        </a>

        <!-- 4. Berkas Terverifikasi -->
        <a href="{{ route('admin.manajemen-asesi', ['tab' => 'verifikasi', 'status' => 'diverifikasi']) }}" 
           class="group bg-white rounded-2xl border p-4.5 transition-all duration-200 hover:-translate-y-0.5 hover:shadow-md cursor-pointer block {{ ($tab ?? '') === 'verifikasi' && ($statusVerifikasi ?? '') === 'diverifikasi' ? 'border-emerald-400 ring-2 ring-emerald-500/10 shadow-xs' : 'border-slate-200/90 shadow-2xs' }}">
            <div class="flex items-center justify-between">
                <div>
                    <div class="text-[11px] font-bold text-emerald-700 uppercase tracking-wider">Berkas Terverifikasi</div>
                    <div class="text-2xl sm:text-3xl font-black text-emerald-600 mt-1">
                        {{ number_format($totalTerverifikasi ?? 0) }}
                    </div>
                    <div class="text-[11px] text-slate-400 mt-1">
                        <span>Memenuhi syarat APL-01</span>
                    </div>
                </div>
                <div class="w-12 h-12 rounded-2xl bg-emerald-50 text-emerald-600 border border-emerald-100 flex items-center justify-center text-xl font-bold group-hover:scale-105 group-hover:bg-emerald-600 group-hover:text-white transition-all">
                    <i class="fa-solid fa-circle-check"></i>
                </div>
            </div>
        </a>
    </div>

    <!-- MODERN NAVIGATION TAB -->
    <div class="flex items-center justify-between flex-wrap gap-4 border-b border-slate-200 pb-3">
        <div class="inline-flex p-1.5 bg-slate-200/60 rounded-2xl border border-slate-200 gap-1.5">
            <a href="{{ route('admin.manajemen-asesi', ['tab' => 'data-asesi']) }}" 
               class="inline-flex items-center gap-2 px-4 py-2.5 rounded-xl text-xs font-bold transition-all {{ ($tab ?? 'data-asesi') === 'data-asesi' ? 'bg-white text-blue-700 shadow-sm border border-slate-200/80' : 'text-slate-600 hover:text-slate-900 hover:bg-white/60' }}">
                <i class="fa-solid fa-users text-xs"></i>
                <span>Data Master Asesi</span>
                <span class="px-2 py-0.5 rounded-full text-[10px] font-extrabold {{ ($tab ?? 'data-asesi') === 'data-asesi' ? 'bg-blue-100 text-blue-800' : 'bg-slate-300/80 text-slate-700' }}">
                    {{ $totalAsesi ?? 0 }}
                </span>
            </a>
            <a href="{{ route('admin.manajemen-asesi', ['tab' => 'verifikasi']) }}" 
               class="inline-flex items-center gap-2 px-4 py-2.5 rounded-xl text-xs font-bold transition-all {{ ($tab ?? '') === 'verifikasi' ? 'bg-white text-blue-700 shadow-sm border border-slate-200/80' : 'text-slate-600 hover:text-slate-900 hover:bg-white/60' }}">
                <i class="fa-solid fa-clipboard-check text-xs"></i>
                <span>Verifikasi Berkas &amp; Pendaftaran</span>
                @if(($totalPendingVerifikasi ?? 0) > 0)
                    <span class="px-2 py-0.5 rounded-full text-[10px] font-extrabold bg-amber-100 text-amber-800 border border-amber-300">
                        {{ $totalPendingVerifikasi }} Pending
                    </span>
                @else
                    <span class="px-2 py-0.5 rounded-full text-[10px] font-extrabold bg-slate-300/80 text-slate-700">
                        0
                    </span>
                @endif
            </a>
        </div>
    </div>

    <!-- ========================================================================= -->
    <!-- TAB 1: DATA MASTER ASESI -->
    <!-- ========================================================================= -->
    @if(($tab ?? 'data-asesi') === 'data-asesi')
        
        <!-- SEARCH & FILTER CARD -->
        <div class="bg-white rounded-2xl border border-slate-200/90 p-4 shadow-2xs">
            <form action="{{ route('admin.manajemen-asesi') }}" method="GET" class="space-y-3">
                <input type="hidden" name="tab" value="data-asesi">

                <div class="grid grid-cols-1 md:grid-cols-12 gap-3">
                    <!-- Search Input -->
                    <div class="relative md:col-span-5">
                        <i class="fa-solid fa-magnifying-glass absolute left-3.5 top-1/2 -translate-y-1/2 text-slate-400 text-xs"></i>
                        <input type="text" 
                               name="q" 
                               class="w-full pl-9 pr-4 py-2.5 rounded-xl border border-slate-200 text-xs focus:outline-none focus:border-blue-500 focus:ring-1 focus:ring-blue-500 transition-all bg-slate-50/50 focus:bg-white text-slate-800 font-medium" 
                               placeholder="Cari nama lengkap asesi, email, atau NIK..." 
                               value="{{ $kataKunci }}">
                    </div>

                    <!-- Skema Filter Dropdown -->
                    <div class="relative md:col-span-5">
                        <select name="skema" 
                                onchange="this.form.submit()"
                                class="w-full px-3.5 py-2.5 rounded-xl border border-slate-200 text-xs focus:outline-none focus:border-blue-500 focus:ring-1 focus:ring-blue-500 transition-all bg-slate-50/50 focus:bg-white text-slate-800 font-medium cursor-pointer">
                            <option value="">Semua Skema &amp; Status</option>
                            <option value="belum_daftar" {{ ($skemaFilter ?? '') === 'belum_daftar' ? 'selected' : '' }} class="text-amber-700 font-bold">
                                &#9888; Belum Mendaftar Skema ({{ $totalBelumDaftar ?? 0 }})
                            </option>
                            <optgroup label="Filter Berdasarkan Skema">
                                @foreach($daftarSkema as $s)
                                    <option value="{{ $s->id }}" {{ ($skemaFilter ?? '') == $s->id ? 'selected' : '' }}>
                                        {{ $s->kode_skema ? '[' . $s->kode_skema . '] ' : '' }}{{ $s->nama_skema }}
                                    </option>
                                @endforeach
                            </optgroup>
                        </select>
                    </div>

                    <!-- Action Buttons -->
                    <div class="flex items-center gap-2 md:col-span-2">
                        <button type="submit" 
                                class="flex-1 px-4 py-2.5 bg-slate-900 hover:bg-slate-800 text-white rounded-xl text-xs font-bold transition-colors cursor-pointer shadow-2xs flex items-center justify-center gap-1.5">
                            <i class="fa-solid fa-filter text-[11px]"></i>
                            <span>Filter</span>
                        </button>
                        @if($kataKunci || $skemaFilter)
                            <a href="{{ route('admin.manajemen-asesi', ['tab' => 'data-asesi']) }}" 
                               class="px-3.5 py-2.5 bg-slate-100 hover:bg-slate-200 text-slate-700 rounded-xl text-xs font-bold transition-colors text-center"
                               title="Reset filter">
                                <i class="fa-solid fa-rotate-left"></i>
                            </a>
                        @endif
                    </div>
                </div>

                <!-- Active Filter Tags -->
                @if($kataKunci || $skemaFilter)
                    <div class="pt-2 border-t border-slate-100 flex items-center gap-2 flex-wrap">
                        <span class="text-[11px] font-semibold text-slate-400">Filter Aktif:</span>
                        
                        @if($kataKunci)
                            <span class="inline-flex items-center gap-1 px-2.5 py-1 rounded-lg bg-blue-50 text-blue-700 border border-blue-200 text-[11px] font-semibold">
                                <span>Pencarian: "{{ $kataKunci }}"</span>
                                <a href="{{ route('admin.manajemen-asesi', ['tab' => 'data-asesi', 'skema' => $skemaFilter]) }}" class="hover:text-blue-900 ml-1 font-bold">&times;</a>
                            </span>
                        @endif

                        @if($skemaFilter === 'belum_daftar')
                            <span class="inline-flex items-center gap-1 px-2.5 py-1 rounded-lg bg-amber-50 text-amber-700 border border-amber-200 text-[11px] font-semibold">
                                <i class="fa-solid fa-user-clock text-[10px]"></i>
                                <span>Belum Mendaftar Skema</span>
                                <a href="{{ route('admin.manajemen-asesi', ['tab' => 'data-asesi', 'q' => $kataKunci]) }}" class="hover:text-amber-900 ml-1 font-bold">&times;</a>
                            </span>
                        @elseif($skemaFilter)
                            @php $selectedSkemaObj = $daftarSkema->firstWhere('id', $skemaFilter); @endphp
                            @if($selectedSkemaObj)
                                <span class="inline-flex items-center gap-1 px-2.5 py-1 rounded-lg bg-blue-50 text-blue-700 border border-blue-200 text-[11px] font-semibold">
                                    <i class="fa-solid fa-certificate text-[10px]"></i>
                                    <span>Skema: {{ $selectedSkemaObj->nama_skema }}</span>
                                    <a href="{{ route('admin.manajemen-asesi', ['tab' => 'data-asesi', 'q' => $kataKunci]) }}" class="hover:text-blue-900 ml-1 font-bold">&times;</a>
                                </span>
                            @endif
                        @endif
                    </div>
                @endif
            </form>
        </div>

        <!-- TABLE: DATA MASTER ASESI -->
        <div class="bg-white rounded-2xl border border-slate-200/90 shadow-2xs overflow-hidden">
            <div class="overflow-x-auto">
                <table class="w-full text-left text-xs text-slate-700">
                    <thead class="bg-[#f8fafc] border-b border-slate-200 text-[11px] uppercase font-bold text-slate-700 tracking-wider">
                        <tr>
                            <th class="px-5 py-3.5">Nama Asesi</th>
                            <th class="px-5 py-3.5">Kontak</th>
                            <th class="px-5 py-3.5 min-w-[280px]">Skema Sertifikasi &amp; Status</th>
                            <th class="px-5 py-3.5">Instansi / Asal Sekolah</th>
                            <th class="px-5 py-3.5">NIK</th>
                            <th class="px-5 py-3.5">Tgl Terdaftar</th>
                            <th class="px-5 py-3.5 text-center">Aksi</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100">
                        @forelse($asesiList as $a)
                            <tr class="hover:bg-blue-50/20 transition-colors">
                                <!-- 1. Nama Asesi -->
                                <td class="px-5 py-4">
                                    <div class="flex items-center gap-3">
                                        <div class="w-9 h-9 rounded-xl bg-gradient-to-br from-blue-50 to-indigo-50 border border-blue-200/80 text-blue-700 font-extrabold text-xs flex items-center justify-center shrink-0 shadow-2xs">
                                            {{ strtoupper(substr($a->nama_lengkap ?? 'A', 0, 2)) }}
                                        </div>
                                        <div>
                                            <div class="font-bold text-slate-900 text-xs hover:text-blue-600 transition-colors">
                                                {{ $a->nama_lengkap }}
                                            </div>
                                            <div class="text-[11px] text-slate-500 font-mono mt-0.5">
                                                ID #{{ $a->id }}
                                            </div>
                                        </div>
                                    </div>
                                </td>

                                <!-- 2. Kontak -->
                                <td class="px-5 py-4">
                                    <div class="space-y-1">
                                        <div class="font-medium text-slate-800 text-[11px] flex items-center gap-1.5">
                                            <i class="fa-regular fa-envelope text-slate-400 text-[10px]"></i>
                                            <span>{{ $a->email }}</span>
                                        </div>
                                        @if($a->nomor_telepon)
                                            <div class="text-[11px] text-slate-500 flex items-center gap-1.5">
                                                <i class="fa-solid fa-phone text-slate-400 text-[10px]"></i>
                                                <a href="https://wa.me/{{ preg_replace('/^0/', '62', preg_replace('/[^0-9]/', '', $a->nomor_telepon)) }}" 
                                                   target="_blank" 
                                                   class="hover:text-emerald-600 transition-colors font-mono">
                                                    {{ $a->nomor_telepon }}
                                                </a>
                                            </div>
                                        @else
                                            <div class="text-[11px] text-slate-400 italic">-</div>
                                        @endif
                                    </div>
                                </td>

                                <!-- 3. Skema Sertifikasi (SOLUSI: Kartu rapi, bukan balon lingkaran) -->
                                <td class="px-5 py-4">
                                    @if($a->pendaftaranAsesi->isEmpty())
                                        <span class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-xl bg-slate-100 text-slate-600 border border-slate-200 font-semibold text-[11px]">
                                            <i class="fa-solid fa-circle-info text-slate-400 text-[10px]"></i>
                                            Belum Mendaftar Skema
                                        </span>
                                    @else
                                        <div class="flex flex-col gap-2 max-w-sm">
                                            @foreach($a->pendaftaranAsesi as $p)
                                                <div class="p-2.5 rounded-xl bg-slate-50/90 border border-slate-200 hover:border-blue-200 hover:bg-blue-50/30 transition-all space-y-1.5">
                                                    <div class="font-bold text-slate-800 text-xs leading-snug">
                                                        {{ $p->skema->nama_skema ?? '-' }}
                                                    </div>
                                                    <div class="flex items-center gap-2 flex-wrap">
                                                        @if($p->status_pendaftaran === 'diverifikasi')
                                                            <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-md text-[10px] font-bold bg-emerald-100 text-emerald-800 border border-emerald-200">
                                                                <span class="w-1.5 h-1.5 rounded-full bg-emerald-500"></span>
                                                                Diterima
                                                            </span>
                                                        @elseif($p->status_pendaftaran === 'ditolak')
                                                            <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-md text-[10px] font-bold bg-rose-100 text-rose-800 border border-rose-200">
                                                                <span class="w-1.5 h-1.5 rounded-full bg-rose-500"></span>
                                                                Ditolak
                                                            </span>
                                                        @else
                                                            <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-md text-[10px] font-bold bg-amber-100 text-amber-800 border border-amber-200">
                                                                <span class="w-1.5 h-1.5 rounded-full bg-amber-500"></span>
                                                                Diajukan
                                                            </span>
                                                        @endif

                                                        @if($p->skema && $p->skema->kode_skema)
                                                            <span class="text-[10px] font-mono text-slate-400 bg-white px-1.5 py-0.5 rounded border border-slate-200/80">
                                                                {{ $p->skema->kode_skema }}
                                                            </span>
                                                        @endif
                                                    </div>
                                                </div>
                                            @endforeach
                                        </div>
                                    @endif
                                </td>

                                <!-- 4. Instansi / Asal Sekolah -->
                                <td class="px-5 py-4">
                                    <div class="flex items-center gap-2 text-xs font-semibold text-slate-800">
                                        <i class="fa-solid fa-school text-slate-400 text-xs shrink-0"></i>
                                        <span>{{ $a->profilAsesi->nama_sekolah_instansi ?? '-' }}</span>
                                    </div>
                                </td>

                                <!-- 5. NIK -->
                                <td class="px-5 py-4 font-mono">
                                    @if($a->profilAsesi && $a->profilAsesi->nik)
                                        <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-lg bg-slate-100 border border-slate-200 text-[11px] font-bold text-slate-700">
                                            <i class="fa-solid fa-id-card text-slate-400 text-[10px]"></i>
                                            {{ $a->profilAsesi->nik }}
                                        </span>
                                    @else
                                        <span class="text-slate-400 italic">-</span>
                                    @endif
                                </td>

                                <!-- 6. Tgl Terdaftar -->
                                <td class="px-5 py-4">
                                    <div class="flex items-center gap-1.5 text-slate-600 text-xs font-medium">
                                        <i class="fa-regular fa-calendar text-slate-400 text-[11px]"></i>
                                        <span>{{ date('d M Y', strtotime($a->created_at)) }}</span>
                                    </div>
                                </td>

                                <!-- 7. Aksi (SOLUSI: Tombol rapi sejajar tanpa terjepit) -->
                                <td class="px-5 py-4 text-center">
                                    <a href="{{ route('admin.detail-asesi', $a->id) }}" 
                                       class="inline-flex items-center justify-center gap-1.5 px-3 py-1.5 rounded-xl border border-slate-200 bg-white hover:bg-blue-50 hover:border-blue-200 text-slate-700 hover:text-blue-700 text-xs font-bold transition-all shadow-2xs whitespace-nowrap group">
                                        <span>Detail &amp; Riwayat</span>
                                        <i class="fa-solid fa-chevron-right text-[10px] text-slate-400 group-hover:text-blue-600 transition-transform group-hover:translate-x-0.5"></i>
                                    </a>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="7" class="px-6 py-12 text-center">
                                    <div class="flex flex-col items-center justify-center space-y-2">
                                        <div class="w-12 h-12 rounded-2xl bg-slate-100 text-slate-400 flex items-center justify-center text-xl">
                                            <i class="fa-solid fa-users-slash"></i>
                                        </div>
                                        <div class="text-sm font-bold text-slate-800">Data Asesi Tidak Ditemukan</div>
                                        <p class="text-xs text-slate-500 max-w-sm">
                                            @if($kataKunci || $skemaFilter)
                                                Tidak ditemukan data asesi yang cocok dengan kata kunci atau filter skema yang dipilih.
                                            @else
                                                Belum ada data asesi yang terdaftar dalam database sistem.
                                            @endif
                                        </p>
                                        @if($kataKunci || $skemaFilter)
                                            <a href="{{ route('admin.manajemen-asesi', ['tab' => 'data-asesi']) }}" 
                                               class="mt-2 inline-flex items-center gap-1.5 px-3.5 py-2 rounded-xl bg-slate-900 text-white text-xs font-bold hover:bg-slate-800 transition-colors">
                                                <i class="fa-solid fa-rotate-left text-[11px]"></i>
                                                <span>Bersihkan Filter</span>
                                            </a>
                                        @endif
                                    </div>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <!-- PAGINATION -->
            @if($asesiList->hasPages())
                <div class="px-5 py-4 border-t border-slate-100 bg-slate-50/50">
                    {{ $asesiList->appends(['tab' => 'data-asesi', 'q' => $kataKunci, 'skema' => $skemaFilter])->links() }}
                </div>
            @endif
        </div>

    <!-- ========================================================================= -->
    <!-- TAB 2: VERIFIKASI BERKAS & PENDAFTARAN -->
    <!-- ========================================================================= -->
    @else

        <!-- SEARCH & FILTER CARD -->
        <div class="bg-white rounded-2xl border border-slate-200/90 p-4 shadow-2xs">
            <form action="{{ route('admin.manajemen-asesi') }}" method="GET" class="space-y-3">
                <input type="hidden" name="tab" value="verifikasi">

                <div class="grid grid-cols-1 md:grid-cols-12 gap-3">
                    <!-- Search Input -->
                    <div class="relative md:col-span-4">
                        <i class="fa-solid fa-magnifying-glass absolute left-3.5 top-1/2 -translate-y-1/2 text-slate-400 text-xs"></i>
                        <input type="text" 
                               name="q" 
                               class="w-full pl-9 pr-4 py-2.5 rounded-xl border border-slate-200 text-xs focus:outline-none focus:border-blue-500 focus:ring-1 focus:ring-blue-500 transition-all bg-slate-50/50 focus:bg-white text-slate-800 font-medium" 
                               placeholder="Cari nomor pendaftaran, nama asesi, atau email..." 
                               value="{{ $kataKunci }}">
                    </div>

                    <!-- Skema Filter Dropdown -->
                    <div class="relative md:col-span-4">
                        <select name="skema" 
                                onchange="this.form.submit()"
                                class="w-full px-3.5 py-2.5 rounded-xl border border-slate-200 text-xs focus:outline-none focus:border-blue-500 focus:ring-1 focus:ring-blue-500 transition-all bg-slate-50/50 focus:bg-white text-slate-800 font-medium cursor-pointer">
                            <option value="">Semua Skema Sertifikasi</option>
                            @foreach($daftarSkema as $s)
                                <option value="{{ $s->id }}" {{ ($skemaFilter ?? '') == $s->id ? 'selected' : '' }}>
                                    {{ $s->kode_skema ? '[' . $s->kode_skema . '] ' : '' }}{{ $s->nama_skema }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <!-- Status Filter Dropdown -->
                    <div class="relative md:col-span-2">
                        <select name="status" 
                                onchange="this.form.submit()"
                                class="w-full px-3.5 py-2.5 rounded-xl border border-slate-200 text-xs focus:outline-none focus:border-blue-500 focus:ring-1 focus:ring-blue-500 transition-all bg-slate-50/50 focus:bg-white text-slate-800 font-medium cursor-pointer">
                            <option value="diajukan" {{ ($statusVerifikasi ?? '') == 'diajukan' ? 'selected' : '' }}>Menunggu (Diajukan)</option>
                            <option value="diverifikasi" {{ ($statusVerifikasi ?? '') == 'diverifikasi' ? 'selected' : '' }}>Diterima (Diverifikasi)</option>
                            <option value="ditolak" {{ ($statusVerifikasi ?? '') == 'ditolak' ? 'selected' : '' }}>Ditolak</option>
                            <option value="semua" {{ ($statusVerifikasi ?? '') == 'semua' ? 'selected' : '' }}>Semua Status</option>
                        </select>
                    </div>

                    <!-- Action Buttons -->
                    <div class="flex items-center gap-2 md:col-span-2">
                        <button type="submit" 
                                class="flex-1 px-4 py-2.5 bg-slate-900 hover:bg-slate-800 text-white rounded-xl text-xs font-bold transition-colors cursor-pointer shadow-2xs flex items-center justify-center gap-1.5">
                            <i class="fa-solid fa-filter text-[11px]"></i>
                            <span>Filter</span>
                        </button>
                        @if($kataKunci || ($statusVerifikasi && $statusVerifikasi !== 'diajukan') || $skemaFilter)
                            <a href="{{ route('admin.manajemen-asesi', ['tab' => 'verifikasi']) }}" 
                               class="px-3.5 py-2.5 bg-slate-100 hover:bg-slate-200 text-slate-700 rounded-xl text-xs font-bold transition-colors text-center"
                               title="Reset filter">
                                <i class="fa-solid fa-rotate-left"></i>
                            </a>
                        @endif
                    </div>
                </div>

                <!-- Active Filter Tags -->
                @if($kataKunci || ($statusVerifikasi && $statusVerifikasi !== 'diajukan') || $skemaFilter)
                    <div class="pt-2 border-t border-slate-100 flex items-center gap-2 flex-wrap">
                        <span class="text-[11px] font-semibold text-slate-400">Filter Aktif:</span>

                        @if($kataKunci)
                            <span class="inline-flex items-center gap-1 px-2.5 py-1 rounded-lg bg-blue-50 text-blue-700 border border-blue-200 text-[11px] font-semibold">
                                <span>Pencarian: "{{ $kataKunci }}"</span>
                                <a href="{{ route('admin.manajemen-asesi', ['tab' => 'verifikasi', 'status' => $statusVerifikasi, 'skema' => $skemaFilter]) }}" class="hover:text-blue-900 ml-1 font-bold">&times;</a>
                            </span>
                        @endif

                        @if($statusVerifikasi && $statusVerifikasi !== 'diajukan')
                            <span class="inline-flex items-center gap-1 px-2.5 py-1 rounded-lg bg-amber-50 text-amber-700 border border-amber-200 text-[11px] font-semibold">
                                <span>Status: {{ ucfirst($statusVerifikasi) }}</span>
                                <a href="{{ route('admin.manajemen-asesi', ['tab' => 'verifikasi', 'q' => $kataKunci, 'skema' => $skemaFilter]) }}" class="hover:text-amber-900 ml-1 font-bold">&times;</a>
                            </span>
                        @endif

                        @if($skemaFilter)
                            @php $selectedSkemaObj = $daftarSkema->firstWhere('id', $skemaFilter); @endphp
                            @if($selectedSkemaObj)
                                <span class="inline-flex items-center gap-1 px-2.5 py-1 rounded-lg bg-blue-50 text-blue-700 border border-blue-200 text-[11px] font-semibold">
                                    <i class="fa-solid fa-certificate text-[10px]"></i>
                                    <span>Skema: {{ $selectedSkemaObj->nama_skema }}</span>
                                    <a href="{{ route('admin.manajemen-asesi', ['tab' => 'verifikasi', 'q' => $kataKunci, 'status' => $statusVerifikasi]) }}" class="hover:text-blue-900 ml-1 font-bold">&times;</a>
                                </span>
                            @endif
                        @endif
                    </div>
                @endif
            </form>
        </div>

        <!-- TABLE: VERIFIKASI BERKAS -->
        <div class="bg-white rounded-2xl border border-slate-200/90 shadow-2xs overflow-hidden">
            <div class="overflow-x-auto">
                <table class="w-full text-left text-xs text-slate-700">
                    <thead class="bg-[#f8fafc] border-b border-slate-200 text-[11px] uppercase font-bold text-slate-700 tracking-wider">
                        <tr>
                            <th class="px-5 py-3.5">No. Pendaftaran</th>
                            <th class="px-5 py-3.5">Nama Asesi</th>
                            <th class="px-5 py-3.5 min-w-[240px]">Skema Sertifikasi</th>
                            <th class="px-5 py-3.5">Jadwal &amp; TUK</th>
                            <th class="px-5 py-3.5">Status Berkas</th>
                            <th class="px-5 py-3.5 text-center">Aksi</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100">
                        @forelse($pendaftaranList as $p)
                            <tr class="hover:bg-blue-50/20 transition-colors">
                                <!-- 1. No. Pendaftaran -->
                                <td class="px-5 py-4 font-mono">
                                    <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-lg bg-blue-50 border border-blue-200 text-blue-700 font-bold text-xs">
                                        <i class="fa-solid fa-hashtag text-blue-400 text-[10px]"></i>
                                        {{ $p->nomor_pendaftaran }}
                                    </span>
                                </td>

                                <!-- 2. Nama Asesi -->
                                <td class="px-5 py-4">
                                    <div class="flex items-center gap-3">
                                        <div class="w-9 h-9 rounded-xl bg-gradient-to-br from-indigo-50 to-blue-50 border border-indigo-200/80 text-indigo-700 font-extrabold text-xs flex items-center justify-center shrink-0 shadow-2xs">
                                            {{ strtoupper(substr($p->asesi->nama_lengkap ?? 'A', 0, 2)) }}
                                        </div>
                                        <div>
                                            <div class="font-bold text-slate-900 text-xs">
                                                {{ $p->asesi->nama_lengkap ?? '-' }}
                                            </div>
                                            <div class="text-[11px] text-slate-500 font-mono mt-0.5">
                                                {{ $p->asesi->email ?? '-' }}
                                            </div>
                                        </div>
                                    </div>
                                </td>

                                <!-- 3. Skema Sertifikasi -->
                                <td class="px-5 py-4">
                                    <div class="p-2.5 rounded-xl bg-slate-50 border border-slate-200/90 space-y-1">
                                        <div class="font-bold text-slate-900 text-xs leading-snug">
                                            {{ $p->skema->nama_skema ?? '-' }}
                                        </div>
                                        @if($p->skema && $p->skema->kode_skema)
                                            <span class="inline-block text-[10px] font-mono text-slate-500 bg-white px-1.5 py-0.5 rounded border border-slate-200/80">
                                                {{ $p->skema->kode_skema }}
                                            </span>
                                        @endif
                                    </div>
                                </td>

                                <!-- 4. Jadwal & TUK -->
                                <td class="px-5 py-4">
                                    @if($p->jadwal)
                                        <div class="space-y-1">
                                            <div class="flex items-center gap-1.5 font-bold text-slate-800 text-xs">
                                                <i class="fa-regular fa-calendar-check text-emerald-600 text-[11px]"></i>
                                                {{ date('d M Y', strtotime($p->jadwal->tanggal_uji)) }}
                                            </div>
                                            <div class="flex items-center gap-1.5 text-[11px] text-slate-500">
                                                <i class="fa-solid fa-location-dot text-slate-400 text-[10px]"></i>
                                                <span>{{ $p->jadwal->nama_tuk }}</span>
                                            </div>
                                        </div>
                                    @else
                                        <span class="inline-flex items-center gap-1 px-2.5 py-1 rounded-lg bg-slate-100 text-slate-400 italic text-[11px] border border-slate-200/80">
                                            <i class="fa-regular fa-clock text-slate-400"></i>
                                            Belum Dijadwalkan
                                        </span>
                                    @endif
                                </td>

                                <!-- 5. Status Berkas -->
                                <td class="px-5 py-4">
                                    @if($p->status_pendaftaran === 'diverifikasi')
                                        <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-[11px] font-bold bg-emerald-100 text-emerald-800 border border-emerald-200">
                                            <span class="w-1.5 h-1.5 rounded-full bg-emerald-500"></span>
                                            Diverifikasi (Diterima)
                                        </span>
                                    @elseif($p->status_pendaftaran === 'ditolak')
                                        <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-[11px] font-bold bg-rose-100 text-rose-800 border border-rose-200">
                                            <span class="w-1.5 h-1.5 rounded-full bg-rose-500"></span>
                                            Ditolak
                                        </span>
                                    @else
                                        <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-[11px] font-bold bg-amber-100 text-amber-800 border border-amber-200">
                                            <span class="w-1.5 h-1.5 rounded-full bg-amber-500 animate-pulse"></span>
                                            Menunggu Verifikasi
                                        </span>
                                    @endif
                                </td>

                                <!-- 6. Aksi -->
                                <td class="px-5 py-4 text-center">
                                    <a href="{{ route('admin.detail-verifikasi', $p->id) }}" 
                                       class="inline-flex items-center justify-center gap-1.5 px-3.5 py-1.5 rounded-xl bg-blue-600 hover:bg-blue-700 text-white font-bold text-xs transition-colors shadow-2xs whitespace-nowrap">
                                        <i class="fa-solid fa-clipboard-check text-[11px]"></i>
                                        <span>Periksa Berkas</span>
                                    </a>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6" class="px-6 py-12 text-center">
                                    <div class="flex flex-col items-center justify-center space-y-2">
                                        <div class="w-12 h-12 rounded-2xl bg-slate-100 text-slate-400 flex items-center justify-center text-xl">
                                            <i class="fa-solid fa-folder-open"></i>
                                        </div>
                                        <div class="text-sm font-bold text-slate-800">Tidak Ada Berkas Pendaftaran</div>
                                        <p class="text-xs text-slate-500 max-w-sm">
                                            Tidak ada data permohonan pendaftaran asesi pada status atau filter yang dipilih.
                                        </p>
                                    </div>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <!-- PAGINATION -->
            @if($pendaftaranList->hasPages())
                <div class="px-5 py-4 border-t border-slate-100 bg-slate-50/50">
                    {{ $pendaftaranList->appends(['tab' => 'verifikasi', 'q' => $kataKunci, 'status' => $statusVerifikasi, 'skema' => $skemaFilter])->links() }}
                </div>
            @endif
        </div>
    @endif

</div>
@endsection

