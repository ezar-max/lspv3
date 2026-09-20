@php
    $user = auth()->user();
    if (!$user) return;

    $alert = null;

    if ($user->peran === 'asesi') {
        $pendaftaranAsesi = \App\Models\PendaftaranAsesi::where('asesi_id', $user->id)
            ->where('status_pendaftaran', '!=', 'ditolak')
            ->latest()
            ->first();

        if ($pendaftaranAsesi) {
            if ($pendaftaranAsesi->isApl02Revision() && !(request()->routeIs('asesi.tahapan*') && request('step') == 2)) {
                $alert = [
                    'key' => 'asesi_revision_' . $pendaftaranAsesi->id . '_' . ($pendaftaranAsesi->updated_at?->timestamp ?? 0),
                    'icon' => 'fa-solid fa-triangle-exclamation',
                    'icon_color' => 'text-amber-600',
                    'icon_bg' => 'bg-amber-50 border border-amber-200/70',
                    'badge_label' => 'Perlu Tindakan',
                    'badge_class' => 'bg-amber-50 text-amber-700 border-amber-200/80',
                    'badge_dot' => 'bg-amber-500',
                    'title' => 'Permintaan Revisi FR.APL.02',
                    'message' => 'Asesor meminta revisi pada butir asesmen mandiri atau dokumen bukti Anda. Silakan periksa catatan dan ajukan ulang.',
                    'quote' => $pendaftaranAsesi->catatan_peninjauan_asesor,
                    'action_label' => 'Periksa Revisi',
                    'action_url' => route('asesi.tahapan', ['step' => 2, 'pendaftaran_id' => $pendaftaranAsesi->id]),
                    'btn_class' => 'bg-amber-600 hover:bg-amber-700 text-white',
                    'bar_class' => 'bg-gradient-to-r from-amber-400 to-amber-600',
                ];
            } elseif ($pendaftaranAsesi->isApl02Approved() && empty($pendaftaranAsesi->tanda_tangan_asesi_ak01) && !in_array($pendaftaranAsesi->status_ak01, ['disetujui_asesi', 'selesai']) && !request()->routeIs('asesi.ak01*') && !request()->is('asesi/ak-01*') && !(request()->routeIs('asesi.tahapan*') && (!request()->has('step') || request('step') == 3))) {
                $alert = [
                    'key' => 'asesi_approved_' . $pendaftaranAsesi->id,
                    'icon' => 'fa-solid fa-file-circle-check',
                    'icon_color' => 'text-blue-600',
                    'icon_bg' => 'bg-blue-50 border border-blue-200/70',
                    'badge_label' => 'Tahap Selanjutnya',
                    'badge_class' => 'bg-blue-50 text-blue-700 border-blue-200/80',
                    'badge_dot' => 'bg-blue-500',
                    'title' => 'FR.APL.02 Telah Disetujui',
                    'message' => 'Asesmen mandiri Anda telah disetujui Asesor. Lanjutkan untuk menyetujui Formulir FR.AK.01 (Persetujuan Asesmen & Kerahasiaan).',
                    'quote' => null,
                    'action_label' => 'Lanjut ke FR.AK.01',
                    'action_url' => route('asesi.tahapan', ['step' => 3, 'pendaftaran_id' => $pendaftaranAsesi->id]),
                    'btn_class' => 'bg-blue-600 hover:bg-blue-700 text-white',
                    'bar_class' => 'bg-gradient-to-r from-blue-500 to-indigo-600',
                ];
            }
        }
    } elseif ($user->peran === 'asesor') {
        if (!request()->routeIs('asesor.penilaian*') && !request()->routeIs('asesor.input-penilaian*')) {
            $pendingApl02Query = \App\Models\PendaftaranAsesi::with(['asesi.profilAsesi', 'skema'])
                ->where(function($q) use ($user) {
                    $q->where('asesor_id', $user->id)
                      ->orWhereHas('jadwal', fn($j) => $j->where('asesor_id', $user->id));
                })
                ->whereIn('status_apl02', ['submitted', 'under_review']);

            $submittedApl02Count = (clone $pendingApl02Query)->count();

            if ($submittedApl02Count > 0) {
                $pendingPendaftaran = $pendingApl02Query->orderBy('updated_at', 'desc')->first();
                $namaAsesi = $pendingPendaftaran?->asesi?->nama_lengkap ?? $pendingPendaftaran?->nama_lengkap;
                $actionUrl = $pendingPendaftaran ? route('asesor.input-penilaian', $pendingPendaftaran->id) : route('asesor.penilaian');

                $message = ($submittedApl02Count === 1 && $namaAsesi)
                    ? "Terdapat 1 berkas FR.APL.02 ({$namaAsesi}) yang menunggu peninjauan dan rekomendasi Anda."
                    : "Terdapat {$submittedApl02Count} berkas FR.APL.02 yang menunggu peninjauan dan rekomendasi Anda.";

                $alert = [
                    'key' => 'asesor_queue_' . $user->id . '_' . ($pendingPendaftaran?->id ?? 0) . '_' . $submittedApl02Count,
                    'icon' => 'fa-solid fa-clipboard-check',
                    'icon_color' => 'text-blue-600',
                    'icon_bg' => 'bg-blue-50 border border-blue-200/70',
                    'badge_label' => 'Antrean Verifikasi',
                    'badge_class' => 'bg-blue-50 text-blue-700 border-blue-200/80',
                    'badge_dot' => 'bg-blue-500',
                    'title' => 'Menunggu Verifikasi Asesmen',
                    'message' => $message,
                    'quote' => null,
                    'action_label' => 'Periksa Sekarang',
                    'action_url' => $actionUrl,
                    'btn_class' => 'bg-slate-900 hover:bg-slate-800 text-white',
                    'bar_class' => 'bg-gradient-to-r from-slate-700 to-slate-900',
                ];
            }
        }
    }
@endphp

@if($alert)
<div x-data="{ 
        show: false,
        progress: 100,
        duration: 7500,
        timer: null,
        isPaused: false,

        init() {
            const storageKey = 'dismissed_alert_' + '{{ $alert['key'] }}';
            if (sessionStorage.getItem(storageKey)) {
                return;
            }
            setTimeout(() => {
                this.show = true;
                this.startTimer();
            }, 300);
        },

        startTimer() {
            const interval = 50;
            const step = (interval / this.duration) * 100;
            this.timer = setInterval(() => {
                if (!this.isPaused) {
                    this.progress -= step;
                    if (this.progress <= 0) {
                        this.close(false);
                    }
                }
            }, interval);
        },

        pause() {
            this.isPaused = true;
        },

        resume() {
            this.isPaused = false;
        },

        close(manual = true) {
            this.show = false;
            if (this.timer) clearInterval(this.timer);
            if (manual) {
                sessionStorage.setItem('dismissed_alert_' + '{{ $alert['key'] }}', '1');
            }
        }
    }"
    x-show="show"
    x-transition:enter="transition cubic-bezier(0.16, 1, 0.3, 1) duration-300 transform"
    x-transition:enter-start="opacity-0 translate-y-4 scale-95"
    x-transition:enter-end="opacity-100 translate-y-0 scale-100"
    x-transition:leave="transition ease-in duration-200 transform"
    x-transition:leave-start="opacity-100 translate-y-0 scale-100"
    x-transition:leave-end="opacity-0 translate-y-4 scale-95"
    @mouseenter="pause()"
    @mouseleave="resume()"
    style="display: none;"
    class="fixed bottom-5 right-4 sm:right-6 z-[9999] w-[410px] max-w-[calc(100vw-2rem)]"
>
    <div class="bg-white/95 backdrop-blur-md rounded-2xl shadow-[0_20px_45px_-10px_rgba(15,23,42,0.12),0_8px_20px_-4px_rgba(15,23,42,0.04)] border border-slate-200/90 overflow-hidden relative transition-all group hover:shadow-[0_24px_50px_-10px_rgba(15,23,42,0.16)]">
        <div class="p-4 sm:p-5">
            <div class="flex items-start gap-3.5">
                <!-- Status Icon Box -->
                <div class="w-10 h-10 rounded-xl {{ $alert['icon_bg'] }} flex items-center justify-center shrink-0 shadow-2xs mt-0.5">
                    <i class="{{ $alert['icon'] }} {{ $alert['icon_color'] }} text-base"></i>
                </div>

                <!-- Content Area -->
                <div class="space-y-1.5 min-w-0 flex-1">
                    <div class="flex items-center justify-between gap-2">
                        @if(!empty($alert['badge_label']))
                            <span class="inline-flex items-center gap-1.5 px-2.5 py-0.5 rounded-full text-[10px] font-bold uppercase tracking-wider border {{ $alert['badge_class'] }}">
                                <span class="w-1.5 h-1.5 rounded-full {{ $alert['badge_dot'] ?? 'bg-blue-500' }}"></span>
                                {{ $alert['badge_label'] }}
                            </span>
                        @else
                            <span></span>
                        @endif

                        <button type="button" 
                                @click="close(true)" 
                                title="Tutup pemberitahuan"
                                class="w-6 h-6 rounded-lg text-slate-400 hover:text-slate-700 hover:bg-slate-100 flex items-center justify-center transition-colors shrink-0 cursor-pointer -mr-1 -mt-1">
                            <i class="fa-solid fa-xmark text-xs"></i>
                        </button>
                    </div>

                    <h4 class="font-bold text-slate-900 text-sm tracking-tight leading-snug">
                        {{ $alert['title'] }}
                    </h4>

                    <p class="text-xs text-slate-500 leading-relaxed">
                        {{ $alert['message'] }}
                    </p>

                    @if(!empty($alert['quote']))
                        <div class="mt-2 p-2.5 rounded-xl bg-amber-50/70 border border-amber-200/80 text-[11px] text-amber-900 leading-relaxed font-medium">
                            <span class="font-bold text-amber-950 block mb-0.5 text-[10px] uppercase tracking-wider">Catatan Asesor:</span>
                            &ldquo;{{ $alert['quote'] }}&rdquo;
                        </div>
                    @endif

                    <div class="pt-2 flex items-center justify-end">
                        <a href="{{ $alert['action_url'] }}" 
                           class="{{ $alert['btn_class'] }} text-xs font-bold px-4 py-2 rounded-xl shadow-xs hover:shadow-md active:scale-[0.98] transition-all duration-150 inline-flex items-center gap-2">
                            <span>{{ $alert['action_label'] }}</span>
                            <svg class="w-3.5 h-3.5 transition-transform duration-150 group-hover:translate-x-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M14 5l7 7m0 0l-7 7m7-7H3"/>
                            </svg>
                        </a>
                    </div>
                </div>
            </div>
        </div>
        
        <!-- Hairline Progress Bar Flush to Card Bottom -->
        <div class="absolute bottom-0 inset-x-0 h-[2.5px] bg-slate-100 overflow-hidden">
            <div class="{{ $alert['bar_class'] }} h-full transition-all duration-75 ease-linear" 
                 :style="'width: ' + progress + '%'"></div>
        </div>
    </div>
</div>
@endif
