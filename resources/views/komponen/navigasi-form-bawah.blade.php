{{-- Navigasi Bawah Formulir: Kembali ke Halaman Sebelumnya --}}
<div class="nav-form-bawah no-print" style="margin-top: 2rem; display: flex; justify-content: flex-start;">
    <div>
        <a href="{{ (url()->previous() && url()->previous() !== url()->current()) ? url()->previous() : ($prevUrl ?? url()->previous()) }}" 
           onclick="if (document.referrer && document.referrer !== window.location.href) { window.location.href = document.referrer; return false; } else if (window.history.length > 1) { window.history.back(); return false; }"
           class="tombol tombol-sekunder tombol-sm cursor-pointer">
            &larr; Kembali
        </a>
    </div>
</div>
