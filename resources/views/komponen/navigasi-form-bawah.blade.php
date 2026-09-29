{{-- Navigasi Bawah Formulir: Kembali ke Halaman Sebelumnya --}}
@php
    $role = auth()->check() ? auth()->user()->peran : null;
    $pendaftaranSkemaId = $skemaId ?? ($pendaftaran->skema_id ?? request('skema_id') ?? (auth()->check() ? auth()->user()->skema_id : null) ?? session('active_selected_skema_id'));
    $defaultBack = match($role) {
        'asesi' => route('asesi.tahapan'),
        'asesor' => ($pendaftaranSkemaId ? route('asesor.mapa', ['skema_id' => $pendaftaranSkemaId]) : route('asesor.mapa')),
        default => ($pendaftaranSkemaId ? route('admin.master-muk.index', ['skema_id' => $pendaftaranSkemaId]) : route('admin.master-muk.index')),
    };
    $targetKembali = $kembaliRoute ?? $prevUrl ?? null;
    if (empty($targetKembali) || $targetKembali === '#' || $targetKembali === route('formulir.index') || str_contains($targetKembali, route('formulir.index')) || (str_contains($targetKembali, 'daftar-peserta') && !empty($pendaftaranSkemaId))) {
        $targetKembali = $defaultBack;
    } elseif ($role !== 'asesor' && str_contains($targetKembali, 'asesor/mapa')) {
        $targetKembali = $defaultBack;
    }
@endphp
<div class="nav-form-bawah no-print" style="margin-top: 2rem; display: flex; justify-content: flex-start;">
    <div>
        <a href="{{ $targetKembali }}" class="tombol tombol-sekunder tombol-sm cursor-pointer">
            &larr; Kembali
        </a>
    </div>
</div>
