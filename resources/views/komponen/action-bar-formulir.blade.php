{{-- Action Bar Reusable untuk Seluruh Formulir BNSP --}}
@php
    $pendaftaranId = $pendaftaranId ?? ($pendaftaran->id ?? null);
    $tipeForm = $tipeForm ?? null;
    $isAsesi = $isAsesi ?? (auth()->check() && auth()->user()->peran === 'asesi');
    $showSave = $showSave ?? !$isAsesi;
    $showPrint = $showPrint ?? true;
    $saveLabel = $saveLabel ?? 'Simpan Formulir';
    $saveAction = $saveAction ?? "document.querySelector('form').submit()";
    $saveFormId = $saveFormId ?? null;
    $signed = $signed ?? false;
    $signedLabel = $signedLabel ?? 'Telah Ditandatangani';
    $signRoute = $signRoute ?? null;
    $signLabel = $signLabel ?? 'Tanda Tangani Hasil Asesmen';
    $role = auth()->check() ? auth()->user()->peran : null;

    $skemaId = $skemaId ?? ($pendaftaran->skema_id ?? request('skema_id') ?? (auth()->check() ? auth()->user()->skema_id : null) ?? session('active_selected_skema_id'));

    $defaultBack = match($role) {
        'asesi' => route('asesi.tahapan'),
        'asesor' => (!empty($skemaId) ? route('asesor.mapa', ['skema_id' => $skemaId]) : route('asesor.mapa')),
        default => (!empty($skemaId) ? route('admin.master-muk.index', ['skema_id' => $skemaId]) : route('admin.master-muk.index')),
    };

    $targetKembali = $kembaliRoute ?? null;
    if (empty($targetKembali) || $targetKembali === '#' || $targetKembali === route('formulir.index') || str_contains($targetKembali, route('formulir.index')) || (str_contains($targetKembali, 'daftar-peserta') && !empty($skemaId))) {
        $targetKembali = $defaultBack;
    } elseif ($role !== 'asesor' && str_contains($targetKembali, 'asesor/mapa')) {
        $targetKembali = $defaultBack;
    }
@endphp

<div class="action-bar-formulir no-print">
    <div class="action-bar-kiri">
        <a href="{{ $targetKembali }}" class="tombol tombol-sekunder tombol-sm cursor-pointer">
            &larr; Kembali
        </a>
        <span class="lencana lencana-biru">{{ $kodeForm ?? 'FORMULIR' }}</span>
        @if(!empty($namaForm))
            <span style="font-size: 0.85rem; color: #475569; font-weight: 500;">{{ $namaForm }}</span>
        @endif
    </div>

    <div class="action-bar-kanan">
        @if($showPrint)
            <button type="button" onclick="window.print()" class="tombol tombol-sekunder tombol-sm">
                Cetak Dokumen
            </button>
        @endif

        @if($isAsesi)
            @if($signed)
                <span class="lencana lencana-hijau">
                    {{ $signedLabel }}
                </span>
            @elseif($signRoute)
                <form action="{{ $signRoute }}" method="POST" style="display: inline-block;">
                    @csrf
                    <button type="submit" class="tombol tombol-utama tombol-sm">
                        {{ $signLabel }}
                    </button>
                </form>
            @elseif($saveFormId)
                <button type="button" onclick="document.getElementById('{{ $saveFormId }}').submit()" class="tombol tombol-utama tombol-sm">
                    {{ $signLabel }}
                </button>
            @elseif($signAction)
                <button type="button" onclick="{!! $signAction !!}" class="tombol tombol-utama tombol-sm">
                    {{ $signLabel }}
                </button>
            @endif
        @else
            @if($showSave)
                @if($saveFormId)
                    <button type="button" onclick="document.getElementById('{{ $saveFormId }}').submit()" class="tombol tombol-utama tombol-sm">
                        {{ $saveLabel }}
                    </button>
                @elseif($saveAction)
                    <button type="button" onclick="{!! $saveAction !!}" class="tombol tombol-utama tombol-sm">
                        {{ $saveLabel }}
                    </button>
                @endif
            @endif
        @endif

        {{ $slot ?? '' }}
    </div>
</div>
