@props([
    'role' => 'Administrator LSP',
    'metadata' => '',
    'signature' => null,
    'date' => ''
])

<div class="bg-white rounded-[1.25rem] border border-slate-100 p-5 w-full max-w-sm shadow-sm">
    <!-- Header -->
    <div class="flex items-start justify-between gap-4 mb-4">
        <div>
            <h3 class="font-bold text-slate-800 text-[15px] leading-snug">{{ $role }}</h3>
            @if($metadata)
                <p class="text-[13px] text-slate-500 font-medium mt-0.5">{{ $metadata }}</p>
            @endif
        </div>
        @if($signature)
            <div class="flex items-center gap-1.5 px-3 py-1 bg-emerald-50 border border-emerald-200 rounded-full shrink-0">
                <svg xmlns="http://www.w3.org/2000/svg" class="w-3.5 h-3.5 text-emerald-600" viewBox="0 0 20 20" fill="currentColor">
                    <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd" />
                </svg>
                <span class="text-[11px] font-bold text-emerald-700 tracking-wide">Tertera TTD</span>
            </div>
        @endif
    </div>

    <!-- Divider -->
    <div class="h-px bg-slate-100 mb-4 w-full"></div>

    <!-- Signature Area -->
    <div class="relative bg-white border border-slate-200 rounded-2xl h-44 flex flex-col items-center justify-center p-4">
        @if($signature)
            <img src="{{ Str::startsWith($signature, 'data:') ? $signature : asset($signature) }}" 
                 alt="Tanda Tangan" 
                 class="max-h-24 max-w-[80%] object-contain -mt-2">
            @if($date)
                <div class="absolute bottom-3 left-0 w-full text-center">
                    <span class="text-[11px] font-mono text-slate-400 tracking-wider uppercase font-medium">{{ $date }}</span>
                </div>
            @endif
        @else
            <div class="text-slate-400 flex flex-col items-center gap-2">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6 opacity-50" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
                </svg>
                <span class="text-xs font-medium">Belum ditandatangani</span>
            </div>
        @endif
    </div>
</div>
