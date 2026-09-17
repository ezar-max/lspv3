@props(['item'])

@php
    $mukInfo = $item->muk_info;
    $soalCount = $item->soal_count;
    $isSpec = ($item->instrument_code === 'ia11');
@endphp

<div class="bg-white rounded-2xl border border-slate-200/90 shadow-2xs hover:shadow-md hover:border-blue-300 transition-all duration-200 flex flex-col justify-between p-5 group">
    <div class="space-y-3.5">
        
        <!-- HEADER: BADGE PILL TERPADU & STATUS AKTIF -->
        <div class="flex items-center justify-between gap-2">
            <!-- Pill [KODE FORM] • [KATEGORI] -->
            <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-lg text-xs font-bold border shadow-2xs {{ $mukInfo['badge_classes'] }}">
                <span class="w-1.5 h-1.5 rounded-full {{ $mukInfo['dot_color'] }}"></span>
                <span>{{ $mukInfo['code'] }} &bull; {{ $mukInfo['badge'] }}</span>
            </span>

            <!-- Status Badge -->
            @if($item->is_active)
                <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-full bg-emerald-50 text-emerald-700 border border-emerald-200 text-[11px] font-bold">
                    <span class="w-1.5 h-1.5 rounded-full bg-emerald-500 animate-pulse"></span>
                    Aktif
                </span>
            @else
                <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-full bg-slate-100 text-slate-500 border border-slate-200 text-[11px] font-bold">
                    <span class="w-1.5 h-1.5 rounded-full bg-slate-400"></span>
                    Nonaktif
                </span>
            @endif
        </div>

        <!-- TYPOGRAPHY & HIERARKI: JUDUL SPESIFIK & METADATA SKEMA -->
        <div class="space-y-1.5">
            <h3 class="text-base font-bold text-slate-900 group-hover:text-blue-600 transition-colors line-clamp-2 leading-snug" title="{{ $item->title }}">
                {{ $item->title }}
            </h3>

            <!-- Sub-info: Skema Sertifikasi (Compact Label dengan Icon Folder) -->
            <div class="flex items-start gap-1.5 text-xs text-slate-500 font-medium pt-0.5">
                <i class="fa-solid fa-folder-open text-slate-400 text-[11px] mt-0.5 shrink-0"></i>
                <span class="line-clamp-2" title="{{ $item->skema->nama_skema ?? 'Skema Umum' }}">
                    Skema: <strong class="text-slate-700 font-semibold">{{ $item->skema->nama_skema ?? 'Skema Umum' }}</strong>
                </span>
            </div>

            @if($item->unitKompetensi)
                <div class="flex items-center gap-1.5 text-xs text-slate-500 font-medium">
                    <i class="fa-solid fa-layer-group text-slate-400 text-[11px] shrink-0"></i>
                    <span class="truncate" title="{{ $item->unitKompetensi->kode_unit }} - {{ $item->unitKompetensi->judul_unit }}">
                        Unit: <span class="text-slate-700">{{ $item->unitKompetensi->kode_unit }}</span>
                    </span>
                </div>
            @endif
        </div>

        <!-- METRIK INFO: BUTIR SOAL & DURASI (Layout Badge Netral) -->
        <div class="bg-slate-50/80 border border-slate-100 rounded-xl p-2.5 flex items-center justify-between gap-2 text-xs">
            <!-- Indikator Butir Soal / Parameter -->
            @if($soalCount > 0)
                <div class="flex items-center gap-1.5 font-bold text-slate-700">
                    <i class="fa-solid {{ $isSpec ? 'fa-cube text-teal-600' : 'fa-clipboard-list text-blue-600' }} text-xs"></i>
                    <span>{{ $soalCount }} {{ $isSpec ? 'Parameter' : 'Butir Soal' }}</span>
                </div>
            @else
                <div class="flex items-center gap-1.5 font-bold text-amber-700 bg-amber-50 px-2 py-0.5 rounded-md border border-amber-200">
                    <i class="fa-solid fa-triangle-exclamation text-amber-600 text-xs"></i>
                    <span>0 {{ $isSpec ? 'Parameter' : 'Butir Soal' }}</span>
                </div>
            @endif

            <!-- Indikator Durasi -->
            <div class="flex items-center gap-1 text-slate-600 font-medium">
                <i class="fa-regular fa-clock text-slate-400 text-xs"></i>
                <span>{{ $item->time_limit_minutes ?? 60 }} Menit</span>
            </div>
        </div>

    </div>

    <!-- ACTION FOOTER: KELOLA SOAL (PRIMARY), EDIT & HAPUS -->
    <div class="border-t border-slate-100 pt-3 mt-4 flex items-center justify-between gap-2">
        <!-- Tombol Kelola Butir Soal -->
        <a href="{{ route('admin.master-muk.manage', $item->id) }}" class="inline-flex items-center gap-1.5 px-3.5 py-2 rounded-xl text-xs font-bold shadow-2xs transition-all {{ $soalCount > 0 ? 'bg-slate-900 hover:bg-slate-800 text-white' : 'bg-blue-600 hover:bg-blue-700 text-white' }}">
            <i class="fa-solid {{ $isSpec ? 'fa-list-check' : 'fa-circle-plus' }} text-[11px]"></i>
            <span>{{ $isSpec ? 'Kelola Parameter' : 'Kelola Butir Soal' }}</span>
        </a>

        <!-- Grup Aksi: Edit & Hapus -->
        <div class="flex items-center gap-1.5">
            <a href="{{ route('admin.master-muk.edit', $item->id) }}" class="inline-flex items-center gap-1 px-2.5 py-1.5 rounded-lg border border-slate-200 bg-white text-xs font-semibold text-slate-700 hover:bg-slate-50 hover:text-blue-600 transition-colors" title="Edit Pengaturan Instrumen">
                <i class="fa-solid fa-pen-to-square text-[11px]"></i>
                <span class="hidden sm:inline">Edit</span>
            </a>

            <form action="{{ route('admin.master-muk.destroy', $item->id) }}" method="POST" onsubmit="return confirm('Hapus paket instrumen master {{ addslashes($item->title) }} beserta seluruh butir soalnya?')" class="inline">
                @csrf
                @method('DELETE')
                <button type="submit" class="inline-flex items-center gap-1 px-2.5 py-1.5 rounded-lg border border-transparent text-xs font-semibold text-rose-600 hover:bg-rose-50 hover:border-rose-200 transition-colors" title="Hapus Paket Instrumen">
                    <i class="fa-solid fa-trash-can text-[11px]"></i>
                    <span class="hidden sm:inline">Hapus</span>
                </button>
            </form>
        </div>
    </div>
</div>
