@extends('tata-letak.publik')

@section('judul', 'Kontak Resmi LSP')

@push('css')
    <link rel="stylesheet" href="{{ asset('css/publik/beranda.css') }}">
@endpush

@section('konten')
<div style="max-width: 900px; margin: 3rem auto; padding: 0 1.5rem;" class="animasi-slide">
    <div class="kartu" style="padding: 2.5rem;">
        <div style="margin-bottom: 1.5rem;">
            <span class="lencana lencana-biru" style="margin-bottom: 0.75rem;"><i class="fa-solid fa-headset"></i> Layanan Sekretariat LSP</span>
            <h1 style="font-size: 2rem; color: var(--biru-malam); margin-bottom: 0.5rem;">Hubungi {{ $pengaturan->nama_lsp }}</h1>
            <p style="color: var(--abu-teks);">Layanan informasi pendaftaran, verifikasi berkas, dan bantuan jadwal uji kompetensi berlisensi resmi BNSP ({{ $pengaturan->nomor_lisensi }})</p>
        </div>

        <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 2rem;">
            <div style="background: var(--biru-bg); padding: 1.5rem; border-radius: var(--radius-md); border: 1px solid var(--biru-soft);">
                <h3 style="color: var(--biru-malam); margin-bottom: 1rem;"><i class="fa-solid fa-building" style="color: var(--biru-utama);"></i> Sekretariat LSP</h3>
                <p style="color: var(--hitam-teks); font-size: 0.95rem; margin-bottom: 1rem;">{{ $pengaturan->alamat_lengkap }}</p>

                <h4 style="color: var(--biru-malam); margin-bottom: 0.5rem;"><i class="fa-solid fa-phone" style="color: var(--biru-muda);"></i> Telepon / WA</h4>
                <p style="color: var(--hitam-teks); font-size: 0.95rem; margin-bottom: 1rem;">{{ $pengaturan->nomor_telepon }}</p>

                <h4 style="color: var(--biru-malam); margin-bottom: 0.5rem;"><i class="fa-solid fa-envelope" style="color: var(--biru-muda);"></i> Email Resmi</h4>
                <p style="color: var(--hitam-teks); font-size: 0.95rem;">{{ $pengaturan->email_resmi }}</p>
            </div>

            <div>
                <h3 style="color: var(--biru-malam); margin-bottom: 1rem;">Kirim Pesan Pertanyaan</h3>
                <form onsubmit="alert('Pesan Anda berhasil dikirim ke Sekretariat LSP.'); return false;">
                    <div class="grup-form">
                        <label class="label-form">Nama Lengkap</label>
                        <input type="text" class="input-control" required placeholder="Nama Anda">
                    </div>
                    <div class="grup-form">
                        <label class="label-form">Email</label>
                        <input type="email" class="input-control" required placeholder="Email Anda">
                    </div>
                    <div class="grup-form">
                        <label class="label-form">Pesan</label>
                        <textarea class="input-control" rows="4" required placeholder="Tuliskan pertanyaan Anda..."></textarea>
                    </div>
                    <button type="submit" class="tombol tombol-utama" style="width: 100%;"><i class="fa-solid fa-paper-plane"></i> Kirim Pesan</button>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection
