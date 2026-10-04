@php
    $showSupervisor = isset($showSupervisor) && $showSupervisor;
    $hideAsesi = isset($hideAsesi) && $hideAsesi;

    $cols = 1;
    if (!$hideAsesi) $cols++;
    if ($showSupervisor) $cols++;

    $gridClass = 'md:grid-cols-' . $cols;
@endphp

<div class="grid grid-cols-1 {{ $gridClass }} gap-4 mb-8">
    @if(!$hideAsesi)
        <div class="rounded-xl border border-slate-200 bg-white p-4">
            <div class="mb-1 text-xs font-bold text-slate-500">ASESI</div>
            <div class="text-sm font-bold text-slate-800">{{ $asesiNama ?? '-' }}</div>
        </div>
    @endif

    <div class="rounded-xl border border-slate-200 bg-white p-4">
        <div class="mb-1 text-xs font-bold text-slate-500">ASESOR</div>
        <div class="text-sm font-bold text-slate-800">{{ $asesorNama ?? '-' }}</div>
        <div class="mt-0.5 font-mono text-xs text-slate-500">{{ $asesorMet ?? '-' }}</div>
    </div>

    @if($showSupervisor)
        <div class="rounded-xl border border-slate-200 bg-white p-4">
            <div class="mb-1 text-xs font-bold text-slate-500">SUPERVISOR (JIKA ADA)</div>
            <div class="text-sm font-bold text-slate-800">-</div>
        </div>
    @endif
</div>