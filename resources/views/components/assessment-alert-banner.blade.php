@php
    $user = auth()->user();
    if (!$user) return;
@endphp

@if($user->peran === 'asesi')
    @php
        $pendaftaranAsesi = \App\Models\PendaftaranAsesi::where('asesi_id', $user->id)
            ->where('status_pendaftaran', '!=', 'ditolak')
            ->latest()
            ->first();
    @endphp

    @if($pendaftaranAsesi)
        @if($pendaftaranAsesi->isApl02Revision() && !(request()->routeIs('asesi.tahapan*') && request('step') == 2))
            <!-- ASESI: AMBER REVISION ALERT -->
            <div class="mb-4 bg-amber-50 border border-amber-200/90 rounded-2xl p-4 shadow-2xs flex flex-col sm:flex-row sm:items-center justify-between gap-3 animate-fade-in">
                <div class="flex items-start gap-3">
                    <div class="w-9 h-9 rounded-xl bg-amber-100 text-amber-700 flex items-center justify-center shrink-0 mt-0.5">
                        <i class="fa-solid fa-triangle-exclamation text-sm"></i>
                    </div>
                    <div class="space-y-0.5 min-w-0">
                        <div class="font-bold text-amber-900 text-xs sm:text-sm flex items-center gap-1.5">
                            <span>FR.APL.02 Anda memerlukan perbaikan</span>
                        </div>
                        <p class="text-xs text-amber-800 leading-relaxed">
                            Asesor meminta revisi pada isian asesmen mandiri atau bukti pendukung Anda. Silakan periksa catatan dan ajukan ulang.
                            @if($pendaftaranAsesi->catatan_peninjauan_asesor)
                                <span class="block text-[11px] text-amber-700 italic mt-0.5 font-medium">"{{ $pendaftaranAsesi->catatan_peninjauan_asesor }}"</span>
                            @endif
                        </p>
                    </div>
                </div>
                <a href="{{ route('asesi.tahapan', ['step' => 2, 'pendaftaran_id' => $pendaftaranAsesi->id]) }}" 
                   class="shrink-0 bg-amber-600 hover:bg-amber-700 text-white text-xs font-semibold px-4 py-2.5 rounded-xl shadow-xs transition-colors inline-flex items-center justify-center gap-1.5 whitespace-nowrap self-start sm:self-center">
                    <span>Periksa Catatan & Revisi</span>
                    <span>&rarr;</span>
                </a>
            </div>
        @elseif($pendaftaranAsesi->isApl02Approved() && empty($pendaftaranAsesi->tanda_tangan_asesi_ak01) && !in_array($pendaftaranAsesi->status_ak01, ['disetujui_asesi', 'selesai']) && !request()->routeIs('asesi.ak01*') && !request()->is('asesi/ak-01*'))
            <!-- ASESI: BLUE APPROVED -> AK.01 READY ALERT -->
            <div class="mb-4 bg-blue-50 border border-blue-200/90 rounded-2xl p-4 shadow-2xs flex flex-col sm:flex-row sm:items-center justify-between gap-3 animate-fade-in">
                <div class="flex items-start gap-3">
                    <div class="w-9 h-9 rounded-xl bg-blue-100 text-blue-700 flex items-center justify-center shrink-0 mt-0.5">
                        <i class="fa-solid fa-file-contract text-sm"></i>
                    </div>
                    <div class="space-y-0.5 min-w-0">
                        <div class="font-bold text-blue-900 text-xs sm:text-sm flex items-center gap-1.5">
                            <span>FR.APL.02 telah disetujui</span>
                        </div>
                        <p class="text-xs text-blue-800 leading-relaxed">
                            Asesmen mandiri Anda telah disetujui oleh Asesor. Silakan isi dan tandatangani Formulir FR.AK.01 (Persetujuan Asesmen & Kerahasiaan).
                        </p>
                    </div>
                </div>
                <a href="{{ route('asesi.ak01', ['pendaftaran_id' => $pendaftaranAsesi->id]) }}" 
                   class="shrink-0 bg-blue-600 hover:bg-blue-700 text-white text-xs font-semibold px-4 py-2.5 rounded-xl shadow-xs transition-colors inline-flex items-center justify-center gap-1.5 whitespace-nowrap self-start sm:self-center">
                    <span>Isi FR.AK.01</span>
                    <span>&rarr;</span>
                </a>
            </div>
        @endif
    @endif

@elseif($user->peran === 'asesor')
    @php
        $submittedApl02Count = \App\Models\PendaftaranAsesi::where(function($q) use ($user) {
                $q->where('asesor_id', $user->id)
                  ->orWhereHas('jadwal', fn($j) => $j->where('asesor_id', $user->id));
            })
            ->whereIn('status_apl02', ['submitted', 'under_review'])
            ->count();
    @endphp

    @if($submittedApl02Count > 0)
        <!-- ASESOR: APL.02 QUEUE ALERT -->
        <div class="mb-4 bg-sky-50 border border-sky-200/90 rounded-2xl p-4 shadow-2xs flex flex-col sm:flex-row sm:items-center justify-between gap-3 animate-fade-in">
            <div class="flex items-start gap-3">
                <div class="w-9 h-9 rounded-xl bg-sky-100 text-sky-700 flex items-center justify-center shrink-0 mt-0.5">
                    <i class="fa-solid fa-clipboard-check text-sm"></i>
                </div>
                <div class="space-y-0.5 min-w-0">
                    <div class="font-bold text-sky-900 text-xs sm:text-sm">
                        Menunggu Verifikasi Asesmen Mandiri
                    </div>
                    <p class="text-xs text-sky-800 leading-relaxed">
                        Anda memiliki <strong>{{ $submittedApl02Count }} berkas FR.APL.02</strong> yang menunggu pemeriksaan dan penentuan rekomendasi.
                    </p>
                </div>
            </div>
            <a href="{{ route('asesor.penilaian') }}" 
               class="shrink-0 bg-sky-600 hover:bg-sky-700 text-white text-xs font-semibold px-4 py-2.5 rounded-xl shadow-xs transition-colors inline-flex items-center justify-center gap-1.5 whitespace-nowrap self-start sm:self-center">
                <span>Periksa Sekarang</span>
                <span>&rarr;</span>
            </a>
        </div>
    @endif
@endif
