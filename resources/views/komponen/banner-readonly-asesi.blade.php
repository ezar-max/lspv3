{{-- Banner Read-Only Reusable untuk Asesi (Tanpa Icon) --}}
@php
    $judul = $judul ?? 'Mode Pratinjau Asesi (Hanya Baca)';
    $keterangan = $keterangan ?? 'Formulir dan instrumen penilaian ini dikelola oleh Asesor Kompetensi Anda.';
    $status = $status ?? 'Hanya Baca';
    $tipe = $tipe ?? 'info';
@endphp

<div class="banner-readonly-wrap no-print {{ $tipe === 'sukses' ? 'sukses' : '' }}">
    <div>
        <strong class="banner-readonly-judul">{{ $judul }}</strong>
        <span class="banner-readonly-ket">{{ $keterangan }}</span>
    </div>
    <span class="banner-readonly-status {{ $tipe === 'sukses' ? 'selesai' : '' }}">
        {{ $status }}
    </span>
</div>
