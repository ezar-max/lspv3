<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Konfigurasi Resmi Profil & Legalitas LSP (BNSP)
    |--------------------------------------------------------------------------
    |
    | Konfigurasi global identitas lembaga sertifikasi profesi pihak kesatu (P1),
    | nomor lisensi resmi BNSP, nomor SK, masa berlaku, kontak, dan alamat sekretariat / TUK.
    |
    */

    'nama_lsp' => env('LSP_NAMA', 'LSP-P1 SMKN 1 Gunungputri'),
    'nama_lengkap' => env('LSP_NAMA_LENGKAP', 'Lembaga Sertifikasi Profesi Pihak Kesatu (LSP P1) SMKN 1 Gunungputri'),
    'kode_lsp' => env('LSP_KODE', 'LSP-P1-SMKN1-GUNUNGPUTRI'),
    'nomor_lisensi' => env('LSP_NOMOR_LISENSI', 'BNSP-LSP-2629-ID'),
    'no_sk_lisensi' => env('LSP_NO_SK', 'KEP.1215/BNSP/V/2025'),
    'masa_berlaku' => env('LSP_MASA_BERLAKU', 'Hingga 23 Mei 2030'),
    'status_keaktifan' => env('LSP_STATUS', 'Aktif'),
    'email_resmi' => env('LSP_EMAIL', 'lsp.smkn1gnputri@gmail.com'),
    'nomor_telepon' => env('LSP_TELEPON', '(021) 867-3310'),
    'alamat_resmi' => env('LSP_ALAMAT', 'Jl. Barokah No. 6, Desa Wanaherang, Kecamatan Gunungputri, Kabupaten Bogor, Jawa Barat'),
    'alamat_singkat' => env('LSP_ALAMAT_SINGKAT', 'Jl. Barokah No. 6, Wanaherang, Kec. Gunungputri, Bogor'),
    'url_cek_lisensi' => env('LSP_URL_BNSP', 'https://bnsp.go.id'),
    'tuk_utama' => env('LSP_TUK_UTAMA', 'TUK SMKN 1 Gunungputri'),
    'logo_path' => 'images/logo-lsp.jpeg',
];
