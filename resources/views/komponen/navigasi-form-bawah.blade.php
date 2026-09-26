{{-- Navigasi Bawah Formulir: Kembali ke Halaman Sebelumnya --}}
@php
    $role = auth()->check() ? auth()->user()->peran : null;
    $pendaftaranSkemaId = $skemaId ?? ($pendaftaran->skema_id ?? null);
    $defaultBack = match($role) {
        'asesi' => route('asesi.tahapan'),
        'asesor' => ($pendaftaranSkemaId ? route('asesor.mapa', ['skema_id' => $pendaftaranSkemaId]) : route('asesor.daftar-peserta')),
        default => ($pendaftaranSkemaId ? route('admin.master-muk.index', ['skema_id' => $pendaftaranSkemaId]) : route('admin.dokumen.index')),
    };
    $targetKembali = $kembaliRoute ?? $prevUrl ?? $defaultBack;
@endphp
<div class="nav-form-bawah no-print" style="margin-top: 2rem; display: flex; justify-content: flex-start;">
    <div>
        <a href="{{ $targetKembali }}" class="tombol tombol-sekunder tombol-sm cursor-pointer">
            &larr; Kembali
        </a>
    </div>
</div>
