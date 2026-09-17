@php
    $lspData = $pengaturan ?? \App\Models\PengaturanSistem::ambilData();
    $namaLsp = $lspData->nama_lsp ?? config('lsp.nama_lengkap', 'Lembaga Sertifikasi Profesi Pihak Kesatu (LSP P1) SMKN 1 Gunungputri');
    $nomorLisensi = $lspData->nomor_lisensi ?? config('lsp.nomor_lisensi', 'BNSP-LSP-2629-ID');
    $noSkLisensi = $lspData->no_sk_lisensi ?? config('lsp.no_sk_lisensi', 'KEP.1215/BNSP/V/2025');
    $alamatLsp = $lspData->alamat_lengkap ?? config('lsp.alamat_resmi', 'Jl. Barokah No. 6, Desa Wanaherang, Kecamatan Gunungputri, Kabupaten Bogor, Jawa Barat');
    $emailLsp = $lspData->email_resmi ?? config('lsp.email_resmi', 'lsp.smkn1gnputri@gmail.com');
    $teleponLsp = $lspData->nomor_telepon ?? config('lsp.nomor_telepon', '(021) 867-3310');
@endphp

<!-- HEADER FORMULIR SIMPLE & MODERN (TAMPILAN WEB) -->
<div class="header-formulir-simple no-print" style="margin-bottom: 1.25rem; padding-bottom: 0.85rem; border-bottom: 1px solid #e2e8f0;">
    <div style="display: flex; align-items: flex-start; justify-content: space-between; gap: 1rem; flex-wrap: wrap;">
        <div style="display: flex; flex-direction: column; gap: 0.2rem; min-width: 250px; flex: 1;">
            @if(!empty($kodeForm))
                <div style="font-family: ui-monospace, SFMono-Regular, Menlo, Monaco, Consolas, monospace; font-size: 0.72rem; font-weight: 800; color: #4f46e5; text-transform: uppercase; letter-spacing: 0.05em;">
                    {{ $kodeForm }}
                </div>
            @endif
            @if(!empty($judulForm))
                <h2 style="font-size: 1.05rem; font-weight: 800; color: #0f172a; line-height: 1.35; margin: 0; text-transform: uppercase; letter-spacing: 0.02em;">
                    {{ $judulForm }}
                </h2>
            @endif
            @if(!empty($subJudul))
                <p style="font-size: 0.8rem; color: #64748b; margin: 0.2rem 0 0 0; line-height: 1.4;">
                    {{ $subJudul }}
                </p>
            @endif
        </div>
        @if(!empty($tipeDokumen))
            <div style="flex-shrink: 0;">
                <span style="display: inline-flex; align-items: center; padding: 0.25rem 0.65rem; border-radius: 6px; font-size: 0.72rem; font-weight: 700; background: #f8fafc; color: #475569; border: 1px solid #e2e8f0; text-transform: uppercase; letter-spacing: 0.03em;">
                    {{ $tipeDokumen }}
                </span>
            </div>
        @endif
    </div>
</div>

<!-- KOP RESMI DOKUMEN STANDAR BNSP (HANYA DITAMPILKAN SAAT CETAK / PRINT DOKUMEN KE KERTAS ATAU PDF) -->
<div class="kop-bnsp-cetak" style="display: none; align-items: center; justify-content: space-between; padding-bottom: 1rem; margin-bottom: 1.25rem; border-bottom: 2.5px double #0f172a; gap: 1rem;">
    <div style="display: flex; align-items: center; gap: 1rem; flex: 1; min-width: 280px;">
        <div style="width: 50px; height: 50px; border-radius: 6px; border: 1px solid #cbd5e1; background: #ffffff; padding: 2px; display: flex; align-items: center; justify-content: center; flex-shrink: 0;">
            <img src="{{ asset('images/logo-lsp.jpeg') }}" alt="Logo LSP" style="max-height: 44px; max-width: 44px; object-fit: contain; display: block;">
        </div>
        <div style="display: flex; flex-direction: column;">
            <div style="font-size: 0.95rem; font-weight: 900; color: #0f172a; line-height: 1.2; text-transform: uppercase;">
                {{ $namaLsp }}
            </div>
            <div style="font-size: 0.72rem; font-weight: 700; color: #1e40af; margin-top: 0.15rem;">
                LISENSI RESMI BNSP: <span style="color: #0f172a;">{{ $nomorLisensi }}</span> &bull; SK: <span style="color: #0f172a;">{{ $noSkLisensi }}</span>
            </div>
            <div style="font-size: 0.68rem; color: #475569; margin-top: 0.1rem; line-height: 1.3;">
                {{ $alamatLsp }} | Telp: {{ $teleponLsp }} | Email: {{ $emailLsp }}
            </div>
        </div>
    </div>

    <div style="border: 2px solid #0f172a; border-radius: 6px; padding: 0.4rem 1rem; text-align: center; background: #f8fafc; min-width: 120px; flex-shrink: 0;">
        <div style="font-size: 1.05rem; font-weight: 900; color: #0f172a; line-height: 1.2;">
            {{ $kodeForm ?? 'DOKUMEN' }}
        </div>
        <div style="font-size: 0.65rem; font-weight: 700; color: #475569; text-transform: uppercase; margin-top: 0.1rem;">
            {{ $tipeDokumen ?? 'Standar BNSP' }}
        </div>
    </div>
</div>

<div class="kop-bnsp-cetak-judul" style="display: none; margin-bottom: 0.85rem;">
    @if(!empty($judulForm))
        <div style="font-size: 1rem; font-weight: 800; color: #0f172a; text-transform: uppercase; border-bottom: 1.5px solid #0f172a; padding-bottom: 0.35rem;">
            {{ $kodeForm ?? '' }}. {{ $judulForm }}
        </div>
    @endif
    @if(!empty($subJudul))
        <p style="margin-top: 0.35rem; margin-bottom: 0.75rem; font-size: 0.78rem; color: #64748b; font-style: italic; line-height: 1.4;">
            {{ $subJudul }}
        </p>
    @endif
</div>

<style>
    @media print {
        .header-formulir-simple {
            display: none !important;
        }
        .kop-bnsp-cetak {
            display: flex !important;
        }
        .kop-bnsp-cetak-judul {
            display: block !important;
        }
    }
</style>
