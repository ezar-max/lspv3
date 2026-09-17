@php
    $user = auth()->user();
    $unreadCount = $user ? $user->unreadNotifications()->count() : 0;
    $notifications = $user ? $user->notifications()->take(10)->get() : collect();
@endphp

<div x-data="{ 
        open: false,
        unreadCount: {{ $unreadCount }},
        isMarking: false,

        init() {
            this.$watch('open', val => {
                if (val && this.unreadCount > 0 && !this.isMarking) {
                    this.autoMarkAllRead();
                }
            });
        },

        async autoMarkAllRead() {
            if (this.isMarking || this.unreadCount <= 0) return;
            this.isMarking = true;
            try {
                const res = await fetch('{{ route('notifications.read_all') }}', {
                    method: 'POST',
                    headers: {
                        'X-CSRF-TOKEN': '{{ csrf_token() }}',
                        'Accept': 'application/json',
                        'X-Requested-With': 'XMLHttpRequest'
                    }
                });
                if (res.ok) {
                    this.unreadCount = 0;
                    // Berikan feedback visual lembut pemudaran dot belum dibaca
                    setTimeout(() => {
                        document.querySelectorAll('.notif-unread-dot').forEach(el => {
                            el.classList.add('transition-all', 'duration-500', 'opacity-0', 'scale-50');
                            setTimeout(() => el.remove(), 500);
                        });
                        document.querySelectorAll('.notif-item-unread').forEach(el => {
                            el.classList.remove('bg-blue-50/30', 'notif-item-unread');
                        });
                    }, 800);
                }
            } catch (e) {
                console.error('Error auto-marking notifications as read:', e);
            } finally {
                this.isMarking = false;
            }
        },

        async clickNotif(id, targetUrl) {
            try {
                const res = await fetch('/notifications/' + id + '/read', {
                    method: 'POST',
                    headers: {
                        'X-CSRF-TOKEN': '{{ csrf_token() }}',
                        'Accept': 'application/json',
                        'X-Requested-With': 'XMLHttpRequest'
                    }
                });
                const data = await res.json();
                if (data.url || targetUrl) {
                    window.location.href = data.url || targetUrl;
                }
            } catch (e) {
                if (targetUrl) window.location.href = targetUrl;
            }
        }
    }" 
    class="relative inline-block text-left" 
    @click.outside="open = false" 
    @keydown.escape.window="open = false">

    <!-- Notification Bell Trigger Button -->
    <button type="button" 
            @click="open = !open" 
            class="relative p-2 rounded-xl text-slate-500 hover:text-slate-800 hover:bg-slate-100 transition-all duration-200 focus:outline-hidden cursor-pointer"
            title="Notifikasi & Pemberitahuan"
            aria-label="Buka Notifikasi">
        <svg class="w-5 h-5 transition-transform duration-200" :class="open ? 'scale-105 text-blue-600' : ''" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="1.8">
            <path stroke-linecap="round" stroke-linejoin="round" d="M14.857 17.082a23.848 23.848 0 005.454-1.31A8.967 8.967 0 0118 9.75v-.7V9A6 6 0 006 9v.75a8.967 8.967 0 01-2.312 6.022c1.733.64 3.56 1.085 5.455 1.31m5.714 0a24.255 24.255 0 01-5.714 0m5.714 0a3 3 0 11-5.714 0" />
        </svg>

        <!-- Unread Badge Indicator on Bell -->
        <template x-if="unreadCount > 0">
            <span class="absolute top-1 right-1 min-w-[18px] h-[18px] flex items-center justify-center bg-rose-500 text-white text-[10px] font-extrabold rounded-full px-1 shadow-sm ring-2 ring-white select-none pointer-events-none transition-all duration-300"
                  x-text="unreadCount > 99 ? '99+' : unreadCount">
                {{ $unreadCount > 99 ? '99+' : $unreadCount }}
            </span>
        </template>
    </button>

    <!-- Notification Popover Dropdown -->
    <div x-show="open" 
         x-transition:enter="transition ease-out duration-150"
         x-transition:enter-start="opacity-0 scale-95 -translate-y-1"
         x-transition:enter-end="opacity-100 scale-100 translate-y-0"
         x-transition:leave="transition ease-in duration-100"
         x-transition:leave-start="opacity-100 scale-100 translate-y-0"
         x-transition:leave-end="opacity-0 scale-95 -translate-y-1"
         class="absolute right-0 mt-2 w-80 sm:w-96 rounded-2xl bg-white border border-slate-200/90 shadow-2xl overflow-hidden z-50"
         style="display: none; max-width: calc(100vw - 24px);">

        <!-- Popover Header -->
        <div class="px-4 py-3 border-b border-slate-100 bg-slate-50/75 flex items-center justify-between gap-3">
            <div class="flex items-center gap-2">
                <span class="font-bold text-slate-800 text-xs sm:text-sm">Notifikasi & Pemberitahuan</span>
                <template x-if="unreadCount > 0">
                    <span class="inline-flex items-center px-2 py-0.5 rounded-full text-[10px] font-bold bg-rose-50 border border-rose-200 text-rose-700 whitespace-nowrap shadow-2xs"
                          x-text="unreadCount + ' baru'"></span>
                </template>
            </div>
        </div>

        <!-- Notification List -->
        <div class="max-h-[380px] overflow-y-auto divide-y divide-slate-100 custom-scrollbar">
            @forelse($notifications as $item)
                @php
                    $data = $item->data ?? [];
                    $title = $data['title'] ?? 'Pemberitahuan Sistem';
                    $message = $data['message'] ?? '';
                    $targetUrl = $data['url'] ?? '#';
                    $isUnread = is_null($item->read_at);
                    $type = $data['type'] ?? 'info';
                @endphp
                <div @click="clickNotif('{{ $item->id }}', '{{ $targetUrl }}')"
                     class="p-3.5 hover:bg-slate-50/90 transition-all duration-200 cursor-pointer flex items-start gap-3 border-b border-slate-100/80 last:border-0 {{ $isUnread ? 'bg-blue-50/30 notif-item-unread' : 'bg-white' }}">
                    
                    <!-- Icon Type -->
                    <div class="mt-0.5 shrink-0">
                        @if($type === 'revision')
                            <div class="w-8 h-8 rounded-xl bg-amber-50 border border-amber-200 text-amber-600 flex items-center justify-center text-xs shadow-2xs">
                                <i class="fa-solid fa-triangle-exclamation"></i>
                            </div>
                        @elseif($type === 'approved' || $type === 'ak01_approved')
                            <div class="w-8 h-8 rounded-xl bg-emerald-50 border border-emerald-200 text-emerald-600 flex items-center justify-center text-xs shadow-2xs">
                                <i class="fa-solid fa-circle-check"></i>
                            </div>
                        @elseif($type === 'ak01_ready' || $type === 'apl01_submission')
                            <div class="w-8 h-8 rounded-xl bg-blue-50 border border-blue-200 text-blue-600 flex items-center justify-center text-xs shadow-2xs">
                                <i class="fa-solid fa-file-signature"></i>
                            </div>
                        @elseif($type === 'penugasan' || $type === 'jadwal')
                            <div class="w-8 h-8 rounded-xl bg-indigo-50 border border-indigo-200 text-indigo-600 flex items-center justify-center text-xs shadow-2xs">
                                <i class="fa-solid fa-user-check"></i>
                            </div>
                        @elseif($type === 'danger' || $type === 'ditolak')
                            <div class="w-8 h-8 rounded-xl bg-rose-50 border border-rose-200 text-rose-600 flex items-center justify-center text-xs shadow-2xs">
                                <i class="fa-solid fa-circle-xmark"></i>
                            </div>
                        @else
                            <div class="w-8 h-8 rounded-xl bg-slate-50 border border-slate-200 text-slate-600 flex items-center justify-center text-xs shadow-2xs">
                                <i class="fa-solid fa-bell"></i>
                            </div>
                        @endif
                    </div>

                    <!-- Content -->
                    <div class="flex-1 min-w-0 space-y-1">
                        <div class="flex items-start justify-between gap-2">
                            <h4 class="font-bold text-slate-800 text-xs sm:text-[13px] leading-snug truncate {{ $isUnread ? 'text-blue-900' : '' }}">
                                {{ $title }}
                            </h4>
                            @if($isUnread)
                                <span class="w-2 h-2 rounded-full bg-blue-600 shrink-0 notif-unread-dot mt-1 transition-all duration-400 ring-2 ring-blue-100" title="Belum dibaca"></span>
                            @endif
                        </div>
                        <p class="text-[11px] sm:text-xs text-slate-600 leading-relaxed line-clamp-2">
                            {{ $message }}
                        </p>
                        <div class="flex items-center gap-1.5 text-[10px] text-slate-400 font-medium pt-0.5">
                            <i class="fa-regular fa-clock text-[9px]"></i>
                            <span>{{ $item->created_at ? $item->created_at->diffForHumans() : 'Baru saja' }}</span>
                        </div>
                    </div>
                </div>
            @empty
                <!-- Empty State -->
                <div class="py-8 px-4 text-center space-y-1.5 text-slate-400">
                    <svg class="w-8 h-8 mx-auto text-slate-300 stroke-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M14.857 17.082a23.848 23.848 0 005.454-1.31A8.967 8.967 0 0118 9.75v-.7V9A6 6 0 006 9v.75a8.967 8.967 0 01-2.312 6.022c1.733.64 3.56 1.085 5.455 1.31m5.714 0a24.255 24.255 0 01-5.714 0m5.714 0a3 3 0 11-5.714 0" />
                    </svg>
                    <div class="text-xs font-semibold text-slate-500">Tidak ada pemberitahuan baru</div>
                    <p class="text-[11px] text-slate-400">Semua notifikasi dan informasi penting akan tampil di sini.</p>
                </div>
            @endforelse
        </div>

        <!-- Footer link to full notification page -->
        <div class="p-2.5 bg-slate-50/90 border-t border-slate-100 text-center">
            <a href="{{ route('notifications.index') }}" 
               class="text-xs font-bold text-blue-600 hover:text-blue-700 transition-colors inline-flex items-center justify-center gap-1.5 py-1 px-3 rounded-lg hover:bg-blue-50/60">
                <span>Lihat Seluruh Halaman Notifikasi</span>
                <i class="fa-solid fa-arrow-right text-[10px]"></i>
            </a>
        </div>
    </div>
</div>
