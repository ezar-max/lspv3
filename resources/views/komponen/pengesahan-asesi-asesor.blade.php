@php
    $showSupervisor = isset($showSupervisor) && $showSupervisor;
    $hideAsesi = isset($hideAsesi) && $hideAsesi;
    
    $cols = 1;
    if (!$hideAsesi) $cols++;
    if ($showSupervisor) $cols++;
    
    $gridClass = 'md:grid-cols-' . $cols;
@endphp
<div class="grid grid-cols-1 {{ $gridClass }} gap-4 mb-8">
    <!-- Kolom Asesi -->
    @if(!$hideAsesi)
    <div class="bg-white rounded-xl border border-slate-200 shadow-sm overflow-hidden">
        <div class="bg-slate-50 border-b border-slate-200 px-4 py-2.5 font-bold text-slate-700 text-sm">
            ASESI :
        </div>
        <div class="p-4 space-y-4">
            <div class="text-sm">
                <div class="text-slate-500 mb-1">Nama :</div>
                <div class="font-bold text-slate-800">{{ $asesiNama ?? '-' }}</div>
            </div>
            
            <div class="border-t border-slate-100 pt-4">
                @if(!empty($asesiTtd))
                    <div class="mb-2">
                        @include('komponen.signature-display', [
                            'role' => 'Asesi',
                            'metadata' => 'Telah menyetujui hasil penilaian',
                            'signature' => $asesiTtd,
                            'date' => 'Ditandatangani pada: ' . ($tglTtdAsesi ?? date('d-m-Y'))
                        ])
                    </div>
                @else
                    <div class="text-sm text-slate-600 mb-2 font-medium">Tanda tangan dan Tanggal :</div>
                    @if(isset($isAsesi) && $isAsesi)
                        <div class="no-print">
                            <form action="{{ route('formulir.ia.simpan-ttd-asesi', ['kodeForm' => $kodeForm ?? '', 'pendaftaranId' => $pendaftaran->id ?? '']) }}" method="POST">
                                @csrf
                                <button type="submit" class="w-full sm:w-auto px-4 py-2 bg-blue-600 hover:bg-blue-700 text-white text-sm font-semibold rounded-lg shadow-sm transition-colors">
                                    Tanda Tangani & Setujui Hasil Penilaian
                                </button>
                            </form>
                        </div>
                    @else
                        <div class="p-3 bg-slate-50 border border-slate-100 rounded-lg text-slate-400 italic text-sm text-center">
                            (Belum Ditandatangani Asesi)
                        </div>
                    @endif
                @endif
            </div>
        </div>
    </div>
    @endif

    <!-- Kolom Asesor -->
    <div class="bg-white rounded-xl border border-slate-200 shadow-sm overflow-hidden">
        <div class="bg-slate-50 border-b border-slate-200 px-4 py-2.5 font-bold text-slate-700 text-sm">
            ASESOR :
        </div>
        <div class="p-4 space-y-4">
            <div class="text-sm">
                <div class="text-slate-500 mb-1">Nama / No. Reg :</div>
                <div class="font-bold text-slate-800">{{ $asesorNama ?? '-' }}</div>
                <div class="font-mono text-xs text-slate-500 mt-0.5">{{ $asesorMet ?? '-' }}</div>
            </div>
            
            <div class="border-t border-slate-100 pt-4">
                @if(!empty($asesorTtd))
                    <div class="mb-2">
                        @include('komponen.signature-display', [
                            'role' => 'Asesor Penguji',
                            'metadata' => 'No. Reg: ' . ($asesorMet ?? '-'),
                            'signature' => $asesorTtd,
                            'date' => 'Disahkan pada: ' . ($tglAsesmen ?? date('d-m-Y'))
                        ])
                    </div>
                @else
                    <div class="text-sm text-slate-600 mb-2 font-medium">Tanda tangan dan Tanggal :</div>
                    <div class="p-3 bg-slate-50 border border-slate-100 rounded-lg text-slate-400 italic text-sm text-center">
                        (Tanda Tangan Digital Asesor)
                    </div>
                @endif
            </div>
        </div>
    </div>

    @if(isset($showSupervisor) && $showSupervisor)
    <!-- Kolom Supervisor -->
    <div class="bg-white rounded-xl border border-slate-200 shadow-sm overflow-hidden">
        <div class="bg-slate-50 border-b border-slate-200 px-4 py-2.5 font-bold text-slate-700 text-sm">
            SUPERVISOR (Jika ada) :
        </div>
        <div class="p-4 space-y-4">
            <div class="text-sm">
                <div class="text-slate-500 mb-1">Nama :</div>
                <div class="font-bold text-slate-800">-</div>
            </div>
            
            <div class="border-t border-slate-100 pt-4">
                <div class="text-sm text-slate-600 mb-2 font-medium">Tanda tangan dan Tanggal :</div>
                <div class="p-3 bg-slate-50 border border-slate-100 rounded-lg text-slate-400 italic text-sm text-center">
                    (TTD Supervisor Tempat Kerja / TUK)
                </div>
            </div>
        </div>
    </div>
    @endif
</div>
