@extends('tata-letak.dasbor')

@section('judul', 'Pemberitahuan & Notifikasi')

@section('konten')
<div class="space-y-6 max-w-5xl mx-auto pb-12">

    <!-- HEADER & STATS -->
    <div class="bg-white rounded-3xl p-6 sm:p-8 border border-slate-200/80 shadow-xs relative overflow-hidden">
        <div class="absolute -right-12 -top-12 w-48 h-48 bg-blue-50/50 rounded-full blur-2xl pointer-events-none"></div>
        <div class="absolute -left-12 -bottom-12 w-48 h-48 bg-indigo-50/40 rounded-full blur-2xl pointer-events-none"></div>

        <div class="relative flex flex-col sm:flex-row items-start sm:items-center justify-between gap-4">
            <div>
                <div class="flex items-center gap-2.5 mb-1.5">
                    <div class="w-10 h-10 rounded-2xl bg-blue-50 text-blue-600 flex items-center justify-center text-lg border border-blue-100 shadow-2xs">
                        <i class="fa-solid fa-bell"></i>
                    </div>
                    <div>
                        <h1 class="text-xl sm:text-2xl font-black text-slate-900 tracking-tight">Notifikasi & Pemberitahuan</h1>
                        <p class="text-xs sm:text-sm text-slate-500">Pusat informasi status asesmen, berkas verifikasi, dan penugasan Anda.</p>
                    </div>
                </div>
            </div>

            @if($unreadCount > 0)
                <form action="{{ route('notifications.read_all') }}" method="POST">
                    @csrf
                    <button type="submit" 
                            class="px-4 py-2.5 rounded-xl bg-blue-600 hover:bg-blue-700 text-white font-semibold text-xs shadow-xs transition-all flex items-center gap-2 cursor-pointer hover:shadow-md active:scale-95">
                        <i class="fa-solid fa-check-double"></i>
                        <span>Tandai Semua Dibaca</span>
                    </button>
                </form>
            @endif
        </div>

        <!-- STATS OVERVIEW CARDS -->
        <div class="grid grid-cols-3 gap-3 sm:gap-4 mt-6 pt-6 border-t border-slate-100">
            <div class="bg-slate-50/80 p-3.5 sm:p-4 rounded-2xl border border-slate-100/90 text-center sm:text-left">
                <span class="text-[11px] font-bold uppercase tracking-wider text-slate-400">Total Notifikasi</span>
                <div class="text-lg sm:text-2xl font-black text-slate-800 mt-0.5">{{ $totalCount }}</div>
            </div>
            <div class="bg-rose-50/50 p-3.5 sm:p-4 rounded-2xl border border-rose-100/80 text-center sm:text-left">
                <span class="text-[11px] font-bold uppercase tracking-wider text-rose-500">Belum Dibaca</span>
                <div class="text-lg sm:text-2xl font-black text-rose-600 mt-0.5">{{ $unreadCount }}</div>
            </div>
            <div class="bg-emerald-50/50 p-3.5 sm:p-4 rounded-2xl border border-emerald-100/80 text-center sm:text-left">
                <span class="text-[11px] font-bold uppercase tracking-wider text-emerald-600">Sudah Dibaca</span>
                <div class="text-lg sm:text-2xl font-black text-emerald-700 mt-0.5">{{ $readCount }}</div>
            </div>
        </div>
    </div>

    <!-- FILTER TABS & SEARCH -->
    <div class="flex items-center justify-between gap-3 border-b border-slate-200/80 pb-2">
        <div class="flex items-center gap-1 sm:gap-2">
            <a href="{{ route('notifications.index', ['filter' => 'all']) }}" 
               class="px-3.5 py-2 rounded-xl text-xs font-bold transition-colors {{ $filter === 'all' ? 'bg-slate-900 text-white shadow-2xs' : 'text-slate-600 hover:bg-slate-100' }}">
                Semua ({{ $totalCount }})
            </a>
            <a href="{{ route('notifications.index', ['filter' => 'unread']) }}" 
               class="px-3.5 py-2 rounded-xl text-xs font-bold transition-colors flex items-center gap-1.5 {{ $filter === 'unread' ? 'bg-blue-600 text-white shadow-2xs' : 'text-slate-600 hover:bg-slate-100' }}">
                <span>Belum Dibaca</span>
                @if($unreadCount > 0)
                    <span class="px-1.5 py-0.2 rounded-full text-[10px] font-black {{ $filter === 'unread' ? 'bg-white text-blue-700' : 'bg-rose-100 text-rose-700' }}">
                        {{ $unreadCount }}
                    </span>
                @endif
            </a>
            <a href="{{ route('notifications.index', ['filter' => 'read']) }}" 
               class="px-3.5 py-2 rounded-xl text-xs font-bold transition-colors {{ $filter === 'read' ? 'bg-emerald-600 text-white shadow-2xs' : 'text-slate-600 hover:bg-slate-100' }}">
                Sudah Dibaca ({{ $readCount }})
            </a>
        </div>
    </div>

    <!-- NOTIFICATION CARDS LIST -->
    <div class="space-y-3">
        @forelse($notifications as $item)
            @php
                $data = $item->data ?? [];
                $title = $data['title'] ?? 'Pemberitahuan Sistem';
                $message = $data['message'] ?? '';
                $targetUrl = $data['url'] ?? null;
                $isUnread = is_null($item->read_at);
                $type = $data['type'] ?? 'info';
            @endphp

            <div class="bg-white rounded-2xl border transition-all duration-200 p-4 sm:p-5 flex flex-col sm:flex-row items-start sm:items-center justify-between gap-4 {{ $isUnread ? 'border-blue-200 bg-blue-50/20 shadow-xs' : 'border-slate-200/80 hover:border-slate-300' }}">
                
                <!-- Left: Icon & Text Content -->
                <div class="flex items-start gap-3.5 flex-1 min-w-0">
                    <!-- Icon Box -->
                    <div class="w-10 h-10 rounded-2xl flex items-center justify-center shrink-0 mt-0.5 text-sm shadow-2xs
                        @if($type === 'penugasan' || $type === 'jadwal')
                            bg-indigo-50 text-indigo-600 border border-indigo-100
                        @elseif($type === 'approved' || $type === 'ak01_approved')
                            bg-emerald-50 text-emerald-600 border border-emerald-100
                        @elseif($type === 'apl01_submission')
                            bg-blue-50 text-blue-600 border border-blue-100
                        @elseif($type === 'revision')
                            bg-amber-50 text-amber-600 border border-amber-100
                        @elseif($type === 'danger' || $type === 'ditolak')
                            bg-rose-50 text-rose-600 border border-rose-100
                        @else
                            bg-slate-100 text-slate-600 border border-slate-200
                        @endif">
                        @if($type === 'penugasan' || $type === 'jadwal')
                            <i class="fa-solid fa-user-check"></i>
                        @elseif($type === 'approved' || $type === 'ak01_approved')
                            <i class="fa-solid fa-circle-check"></i>
                        @elseif($type === 'apl01_submission')
                            <i class="fa-solid fa-file-signature"></i>
                        @elseif($type === 'revision')
                            <i class="fa-solid fa-triangle-exclamation"></i>
                        @elseif($type === 'danger' || $type === 'ditolak')
                            <i class="fa-solid fa-circle-xmark"></i>
                        @else
                            <i class="fa-solid fa-bell"></i>
                        @endif
                    </div>

                    <!-- Texts -->
                    <div class="space-y-1 min-w-0 flex-1">
                        <div class="flex flex-wrap items-center gap-2">
                            <h3 class="font-bold text-sm text-slate-900 leading-snug">
                                {{ $title }}
                            </h3>
                            @if($isUnread)
                                <span class="px-2 py-0.5 rounded-full bg-blue-100 text-blue-700 text-[10px] font-extrabold tracking-wide uppercase">
                                    Baru
                                </span>
                            @endif
                        </div>
                        <p class="text-xs text-slate-600 leading-relaxed break-words">
                            {{ $message }}
                        </p>
                        <div class="flex items-center gap-3 pt-1 text-[11px] text-slate-400 font-medium">
                            <span title="{{ $item->created_at ? $item->created_at->format('d M Y, H:i') : '' }}">
                                <i class="fa-regular fa-clock mr-1"></i>
                                {{ $item->created_at ? $item->created_at->diffForHumans() : '-' }}
                            </span>
                            @if($item->read_at)
                                <span class="text-slate-400">
                                    <i class="fa-regular fa-eye mr-1"></i> Dibaca {{ $item->read_at->diffForHumans() }}
                                </span>
                            @endif
                        </div>
                    </div>
                </div>

                <!-- Right: Action Buttons -->
                <div class="flex items-center gap-2 self-end sm:self-center shrink-0 pt-2 sm:pt-0 border-t sm:border-t-0 border-slate-100 w-full sm:w-auto justify-end">
                    @if($targetUrl && $targetUrl !== '#')
                        <a href="{{ route('notifications.read', $item->id) }}" 
                           class="px-3.5 py-1.5 rounded-xl bg-blue-50 hover:bg-blue-600 text-blue-700 hover:text-white border border-blue-200/80 font-bold text-xs transition-colors flex items-center gap-1.5 shadow-2xs">
                            <span>Buka Halaman</span>
                            <i class="fa-solid fa-arrow-up-right-from-square text-[10px]"></i>
                        </a>
                    @endif

                    @if($isUnread)
                        <form action="{{ route('notifications.read', $item->id) }}" method="POST">
                            @csrf
                            <button type="submit" 
                                    class="p-2 rounded-xl text-slate-500 hover:text-slate-800 hover:bg-slate-100 transition-colors text-xs font-semibold"
                                    title="Tandai Sudah Dibaca">
                                <i class="fa-regular fa-circle-check text-sm"></i>
                            </button>
                        </form>
                    @endif

                    <form action="{{ route('notifications.destroy', $item->id) }}" method="POST" 
                          onsubmit="return confirm('Hapus notifikasi ini?')">
                        @csrf
                        @method('DELETE')
                        <button type="submit" 
                                class="p-2 rounded-xl text-slate-400 hover:text-rose-600 hover:bg-rose-50 transition-colors text-xs"
                                title="Hapus Notifikasi">
                            <i class="fa-regular fa-trash-can text-sm"></i>
                        </button>
                    </form>
                </div>
            </div>
        @empty
            <div class="bg-white rounded-3xl p-12 text-center border border-slate-200/80 shadow-xs space-y-3">
                <div class="w-16 h-16 rounded-full bg-slate-50 border border-slate-100 text-slate-300 flex items-center justify-center text-2xl mx-auto">
                    <i class="fa-regular fa-bell-slash"></i>
                </div>
                <h3 class="font-bold text-slate-700 text-base">Tidak Ada Notifikasi</h3>
                <p class="text-xs text-slate-400 max-w-sm mx-auto">
                    @if($filter === 'unread')
                        Semua notifikasi sudah Anda baca. Tidak ada pemberitahuan baru yang tertunda.
                    @else
                        Saat ini belum ada pemberitahuan atau pembaruan status asesmen untuk akun Anda.
                    @endif
                </p>
                @if($filter !== 'all')
                    <div class="pt-2">
                        <a href="{{ route('notifications.index') }}" class="px-4 py-2 rounded-xl bg-slate-100 text-slate-700 font-bold text-xs hover:bg-slate-200 transition-colors">
                            Lihat Semua Notifikasi
                        </a>
                    </div>
                @endif
            </div>
        @endforelse
    </div>

    <!-- PAGINATION -->
    @if($notifications->hasPages())
        <div class="pt-4 flex justify-center">
            {{ $notifications->links() }}
        </div>
    @endif

</div>
@endsection
