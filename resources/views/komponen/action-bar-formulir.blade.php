{{-- Action Bar Reusable untuk Seluruh Formulir BNSP --}}
@php
    $pendaftaranId = $pendaftaranId ?? ($pendaftaran->id ?? null);
    $tipeForm = $tipeForm ?? null;
    $galeriUrl = $galeriUrl ?? ($pendaftaranId ? route('formulir.index', array_filter(['pendaftaran_id' => $pendaftaranId, 'tipe_form' => $tipeForm])) : '#');
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
@endphp

<div class="action-bar-formulir no-print">
    <div class="action-bar-kiri">
        <a href="{{ (url()->previous() && url()->previous() !== url()->current()) ? url()->previous() : $galeriUrl }}" 
           onclick="if (document.referrer && document.referrer !== window.location.href) { window.location.href = document.referrer; return false; } else if (window.history.length > 1) { window.history.back(); return false; }"
           class="tombol tombol-sekunder tombol-sm cursor-pointer">
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
