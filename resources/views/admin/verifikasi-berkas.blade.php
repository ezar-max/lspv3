@extends('tata-letak.dasbor')

@section('judul', 'Antrean Verifikasi Pendaftaran Admin')

@push('css')
    <link rel="stylesheet" href="{{ asset('css/admin/dashboard-admin.css') }}">
@endpush

@section('konten')
<div class="animasi-slide space-y-6 max-w-7xl mx-auto pb-10">

    <!-- PAGE HEADER -->
    <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-4">
        <div>
            <div class="inline-flex items-center gap-2 px-2.5 py-1 rounded-lg bg-blue-50 border border-blue-200/80 text-blue-700 text-xs font-bold mb-2">
                <i class="fa-solid fa-clipboard-check text-blue-600"></i>
                <span>Antrean Verifikasi Admin</span>
            </div>
            <h1 class="text-2xl sm:text-3xl font-extrabold text-slate-900 tracking-tight">
                Antrean Verifikasi Pendaftaran (FR.APL.01 &amp; APL.02)
            </h1>
            <p class="text-xs sm:text-sm text-slate-500 mt-1">
                Halaman khusus pemeriksaan keabsahan berkas persyaratan dasar permohonan sertifikasi dan penugasan asesor penguji
            </p>
        </div>
        <div class="flex items-center gap-2">
            <a href="{{ route('admin.dashboard') }}" 
               class="inline-flex items-center gap-2 px-3.5 py-2 rounded-xl bg-white hover:bg-slate-50 text-slate-700 text-xs font-bold border border-slate-200 shadow-2xs transition-colors">
                <i class="fa-solid fa-arrow-left text-[11px] text-slate-400"></i>
                <span>Kembali ke Dashboard</span>
            </a>
        </div>
    </div>

    <!-- STATUS FILTER PILLS -->
    <div class="flex items-center flex-wrap gap-2">
        <a href="{{ route('admin.verifikasi-berkas', ['status' => 'diajukan', 'q' => $kataKunci ?? null, 'skema' => $skemaFilter ?? null]) }}" 
           class="inline-flex items-center gap-2 px-4 py-2 rounded-xl text-xs font-bold border transition-all {{ ($status ?? 'diajukan') === 'diajukan' ? 'bg-amber-500 text-white border-amber-500 shadow-xs' : 'bg-white text-slate-600 border-slate-200 hover:bg-slate-50' }}">
            <i class="fa-solid fa-hourglass-half text-[11px]"></i>
            <span>Perlu Verifikasi (Diajukan)</span>
            <span class="px-2 py-0.5 rounded-full text-[10px] font-extrabold {{ ($status ?? 'diajukan') === 'diajukan' ? 'bg-white/25 text-white' : 'bg-amber-100 text-amber-800' }}">
                {{ $totalPendingVerifikasi ?? 0 }}
            </span>
        </a>

        <a href="{{ route('admin.verifikasi-berkas', ['status' => 'diverifikasi', 'q' => $kataKunci ?? null, 'skema' => $skemaFilter ?? null]) }}" 
           class="inline-flex items-center gap-2 px-4 py-2 rounded-xl text-xs font-bold border transition-all {{ ($status ?? '') === 'diverifikasi' ? 'bg-emerald-600 text-white border-emerald-600 shadow-xs' : 'bg-white text-slate-600 border-slate-200 hover:bg-slate-50' }}">
            <i class="fa-solid fa-circle-check text-[11px]"></i>
            <span>Terverifikasi (Disetujui)</span>
            <span class="px-2 py-0.5 rounded-full text-[10px] font-extrabold {{ ($status ?? '') === 'diverifikasi' ? 'bg-white/25 text-white' : 'bg-emerald-100 text-emerald-800' }}">
                {{ $totalTerverifikasi ?? 0 }}
            </span>
        </a>

        <a href="{{ route('admin.verifikasi-berkas', ['status' => 'revisi', 'q' => $kataKunci ?? null, 'skema' => $skemaFilter ?? null]) }}" 
           class="inline-flex items-center gap-2 px-4 py-2 rounded-xl text-xs font-bold border transition-all {{ ($status ?? '') === 'revisi' ? 'bg-orange-600 text-white border-orange-600 shadow-xs' : 'bg-white text-slate-600 border-slate-200 hover:bg-slate-50' }}">
            <i class="fa-solid fa-rotate text-[11px]"></i>
            <span>Perlu Revisi</span>
            <span class="px-2 py-0.5 rounded-full text-[10px] font-extrabold {{ ($status ?? '') === 'revisi' ? 'bg-white/25 text-white' : 'bg-orange-100 text-orange-800' }}">
                {{ $totalRevisi ?? 0 }}
            </span>
        </a>

        <a href="{{ route('admin.verifikasi-berkas', ['status' => 'ditolak', 'q' => $kataKunci ?? null, 'skema' => $skemaFilter ?? null]) }}" 
           class="inline-flex items-center gap-2 px-4 py-2 rounded-xl text-xs font-bold border transition-all {{ ($status ?? '') === 'ditolak' ? 'bg-rose-600 text-white border-rose-600 shadow-xs' : 'bg-white text-slate-600 border-slate-200 hover:bg-slate-50' }}">
            <i class="fa-solid fa-circle-xmark text-[11px]"></i>
            <span>Ditolak</span>
            <span class="px-2 py-0.5 rounded-full text-[10px] font-extrabold {{ ($status ?? '') === 'ditolak' ? 'bg-white/25 text-white' : 'bg-rose-100 text-rose-800' }}">
                {{ $totalDitolak ?? 0 }}
            </span>
        </a>

        <a href="{{ route('admin.verifikasi-berkas', ['status' => 'semua', 'q' => $kataKunci ?? null, 'skema' => $skemaFilter ?? null]) }}" 
           class="inline-flex items-center gap-2 px-4 py-2 rounded-xl text-xs font-bold border transition-all {{ ($status ?? '') === 'semua' ? 'bg-slate-900 text-white border-slate-900 shadow-xs' : 'bg-white text-slate-600 border-slate-200 hover:bg-slate-50' }}">
            <i class="fa-solid fa-layer-group text-[11px]"></i>
            <span>Semua Antrean</span>
            <span class="px-2 py-0.5 rounded-full text-[10px] font-extrabold {{ ($status ?? '') === 'semua' ? 'bg-white/20 text-white' : 'bg-slate-100 text-slate-700' }}">
                {{ $totalSemua ?? 0 }}
            </span>
        </a>
    </div>

    <!-- SEARCH & FILTER BAR -->
    <div class="bg-white rounded-2xl border border-slate-200/90 p-4 shadow-2xs">
        <form action="{{ route('admin.verifikasi-berkas') }}" method="GET" class="grid grid-cols-1 md:grid-cols-12 gap-3">
            <input type="hidden" name="status" value="{{ $status ?? 'diajukan' }}">

            <div class="relative md:col-span-5">
                <i class="fa-solid fa-magnifying-glass absolute left-3.5 top-1/2 -translate-y-1/2 text-slate-400 text-xs"></i>
                <input type="text" 
                       name="q" 
                       value="{{ $kataKunci ?? '' }}"
                       placeholder="Cari nomor pendaftaran, nama asesi, atau email..." 
                       class="w-full pl-9 pr-4 py-2.5 rounded-xl border border-slate-200 text-xs focus:outline-none focus:border-blue-500 focus:ring-1 focus:ring-blue-500 transition-all bg-slate-50/50 focus:bg-white text-slate-800 font-medium">
            </div>

            <div class="relative md:col-span-5">
                <select name="skema" 
                        onchange="this.form.submit()"
                        class="w-full px-3.5 py-2.5 rounded-xl border border-slate-200 text-xs focus:outline-none focus:border-blue-500 focus:ring-1 focus:ring-blue-500 transition-all bg-slate-50/50 focus:bg-white text-slate-800 font-medium cursor-pointer">
                    <option value="">Semua Skema Sertifikasi</option>
                    @foreach($daftarSkema ?? [] as $s)
                        <option value="{{ $s->id }}" {{ ($skemaFilter ?? '') == $s->id ? 'selected' : '' }}>
                            {{ $s->kode_skema ? '[' . $s->kode_skema . '] ' : '' }}{{ $s->nama_skema }}
                        </option>
                    @endforeach
                </select>
            </div>

            <div class="flex items-center gap-2 md:col-span-2">
                <button type="submit" 
                        class="flex-1 px-4 py-2.5 bg-slate-900 hover:bg-slate-800 text-white rounded-xl text-xs font-bold transition-colors cursor-pointer shadow-2xs flex items-center justify-center gap-1.5">
                    <i class="fa-solid fa-filter text-[11px]"></i>
                    <span>Filter</span>
                </button>
                @if(!empty($kataKunci) || !empty($skemaFilter))
                    <a href="{{ route('admin.verifikasi-berkas', ['status' => $status ?? 'diajukan']) }}" 
                       class="px-3.5 py-2.5 bg-slate-100 hover:bg-slate-200 text-slate-700 rounded-xl text-xs font-bold transition-colors text-center"
                       title="Reset filter">
                        <i class="fa-solid fa-rotate-left"></i>
                    </a>
                @endif
            </div>
        </form>
    </div>

    <!-- TABLE: ANTREAN VERIFIKASI PENDAFTARAN -->
    <div class="bg-white rounded-2xl border border-slate-200/90 shadow-2xs overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-left text-xs text-slate-700">
                <thead class="bg-[#f8fafc] border-b border-slate-200 text-[11px] uppercase font-bold text-slate-700 tracking-wider">
                    <tr>
                        <th class="px-5 py-3.5">No. Pendaftaran</th>
                        <th class="px-5 py-3.5">Nama Asesi</th>
                        <th class="px-5 py-3.5 min-w-[240px]">Skema Sertifikasi</th>
                        <th class="px-5 py-3.5">Jumlah Berkas</th>
                        <th class="px-5 py-3.5">Status Verifikasi</th>
                        <th class="px-5 py-3.5 text-center">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @forelse($pendaftaranList as $p)
                        <tr class="hover:bg-blue-50/20 transition-colors">
                            <td class="px-5 py-4 font-mono">
                                <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-lg bg-blue-50 border border-blue-200 text-blue-700 font-bold text-xs">
                                    <i class="fa-solid fa-hashtag text-blue-400 text-[10px]"></i>
                                    {{ $p->nomor_pendaftaran }}
                                </span>
                                <div class="text-[10px] text-slate-400 mt-1">
                                    {{ $p->created_at ? $p->created_at->translatedFormat('d M Y, H:i') : '-' }}
                                </div>
                            </td>

                            <td class="px-5 py-4">
                                <div class="flex items-center gap-3">
                                    <div class="w-9 h-9 rounded-xl bg-gradient-to-br from-blue-50 to-indigo-50 border border-blue-200/80 text-blue-700 font-extrabold text-xs flex items-center justify-center shrink-0 shadow-2xs">
                                        {{ strtoupper(substr($p->asesi->nama_lengkap ?? 'A', 0, 2)) }}
                                    </div>
                                    <div>
                                        <div class="font-bold text-slate-900 text-xs">
                                            {{ $p->asesi->nama_lengkap ?? '-' }}
                                        </div>
                                        <div class="text-[11px] text-slate-500 mt-0.5">
                                            Instansi: {{ $p->asesi->profilAsesi->nama_sekolah_instansi ?? '-' }}
                                        </div>
                                    </div>
                                </div>
                            </td>

                            <td class="px-5 py-4">
                                <div class="font-bold text-slate-800 text-xs">{{ $p->skema->nama_skema ?? '-' }}</div>
                                @if($p->skema && $p->skema->kode_skema)
                                    <span class="inline-block mt-1 text-[10px] font-mono text-slate-500 bg-slate-50 px-1.5 py-0.5 rounded border border-slate-200/80">
                                        {{ $p->skema->kode_skema }}
                                    </span>
                                @endif
                            </td>

                            <td class="px-5 py-4">
                                <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-lg bg-slate-100 border border-slate-200 text-slate-700 font-bold text-[11px]">
                                    <i class="fa-regular fa-file-lines text-slate-400 text-[10px]"></i>
                                    {{ $p->dokumen->count() }} Berkas
                                </span>
                            </td>

                            <td class="px-5 py-4">
                                @if($p->status_pendaftaran === 'diverifikasi')
                                    <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full text-[11px] font-bold bg-emerald-100 text-emerald-800 border border-emerald-200">
                                        <span class="w-1.5 h-1.5 rounded-full bg-emerald-500"></span>
                                        Disetujui Admin
                                    </span>
                                @elseif($p->status_pendaftaran === 'revisi')
                                    <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full text-[11px] font-bold bg-orange-100 text-orange-800 border border-orange-200">
                                        <span class="w-1.5 h-1.5 rounded-full bg-orange-500"></span>
                                        Perlu Revisi
                                    </span>
                                @elseif($p->status_pendaftaran === 'ditolak')
                                    <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full text-[11px] font-bold bg-rose-100 text-rose-800 border border-rose-200">
                                        <span class="w-1.5 h-1.5 rounded-full bg-rose-500"></span>
                                        Ditolak
                                    </span>
                                @else
                                    <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full text-[11px] font-bold bg-amber-100 text-amber-800 border border-amber-200">
                                        <span class="w-1.5 h-1.5 rounded-full bg-amber-500 animate-pulse"></span>
                                        Diajukan (Pending)
                                    </span>
                                @endif

                                @if($p->request_perbaikan == 1)
                                    <div class="mt-1">
                                        <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded bg-yellow-100 text-amber-800 border border-yellow-300 text-[10px] font-bold">
                                            <i class="fa-solid fa-unlock text-[9px]"></i> Permohonan Ubah Data
                                        </span>
                                    </div>
                                @endif
                            </td>

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
                                    <div class="text-sm font-bold text-slate-800">Tidak Ada Antrean Berkas</div>
                                    <p class="text-xs text-slate-500 max-w-sm">
                                        Tidak ditemukan permohonan pendaftaran pada status atau filter yang dipilih.
                                    </p>
                                </div>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if($pendaftaranList->hasPages())
            <div class="px-5 py-4 border-t border-slate-100 bg-slate-50/50">
                {{ $pendaftaranList->appends(['status' => $status ?? 'diajukan', 'q' => $kataKunci ?? null, 'skema' => $skemaFilter ?? null])->links() }}
            </div>
        @endif
    </div>
</div>
@endsection
