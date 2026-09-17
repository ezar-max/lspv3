# Product

<!-- impeccable:product-schema 1 -->

## Platform

web

## Stack
Laravel 11, PHP 8.x, Blade Templates, Vanilla CSS Design System, MySQL

## Users
- **Asesi (Siswa SMKN 1 Gunungputri)**: Calon peserta sertifikasi yang mendaftar skema keahlian vokasi, melengkapi biodata pemohon (FR.APL.01), mengunggah berkas persyaratan dasar & administratif, mengisi Formulir Asesmen Mandiri (FR.APL.02) beserta bukti foto/karya relevan, memantau jadwal asesmen, dan melihat hasil kelulusan.
- **Asesor (Penguji / Penilai)**: Penguji tersertifikasi yang meninjau berkas & jawaban asesmen mandiri peserta, melakukan penilaian uji kompetensi, menerbitkan rekomendasi asesmen, serta mengisi berita acara pelaksanaan.
- **Admin (Administrator LSP)**: Pengelola sistem yang mengampu verifikasi berkas pendaftaran APL-01 & APL-02, manajemen master skema sertifikasi, unit kompetensi SKKNI, elemen & KUK, penjadwalan TUK, publikasi berita/pengumuman, dan penerbitan laporan kelulusan.
- **Super Admin**: Pengawas sistem dengan wewenang penuh untuk manajemen pengguna, pemantauan audit log aktivitas, dan konfigurasi pengaturan global LSP.

## Product Purpose
Menyediakan sistem informasi Lembaga Sertifikasi Profesi Pihak Pertama (LSP-P1) SMKN 1 Gunungputri yang terintegrasi, transparan, dan berstandar BNSP/SKKNI. Sistem ini mendigitalisasi alur pendaftaran APL-01, asesmen mandiri APL-02, pengujian oleh asesor, hingga pelaporan kelulusan guna mempercepat proses validasi kualifikasi keahlian vokasi siswa.

## Positioning
Sistem sertifikasi digital khusus LSP-P1 SMK yang memadukan manajemen master skema SKKNI dinamis (unit, elemen, KUK), pengisian APL-02 mandiri berbasis bukti foto/karya digital, serta integrasi alur verifikasi multi-peran (Asesi, Asesor, Admin, Super Admin) dalam satu dasbor yang cepat, intuitif, dan elegan.

## Operating Context
- Digunakan dalam lingkungan SMK Keahlian Vokasi (SMKN 1 Gunungputri).
- Mendukung proses Tempat Uji Kompetensi (TUK) di laboratorium keahlian (RPL, TKJ, Pengelasan, dll.).
- Mengikuti acuan formulir resmi BNSP (FR.APL.01 Permohonan Sertifikasi & FR.APL.02 Asesmen Mandiri).

## Capabilities and Constraints
- **Kemampuan Utama**:
  - Manajemen Skema Sertifikasi, Unit Kompetensi, Elemen, dan Kriteria Unjuk Kerja (KUK) yang dapat dikustomisasi penuh oleh Admin.
  - Alur multi-step pendaftaran APL-01 dan asesmen mandiri APL-02 interaktif dengan unggah berkas bukti relevan (foto/pdf).
  - Tanda tangan digital (canvas signature pad) untuk Asesi, Asesor, dan Admin.
  - Verifikasi berkas, penjadwalan uji TUK, input penilaian Asesor, dan Berita Acara.
  - Audit log aktivitas pengguna dan portal berita/pengumuman publik.
- **Batasan**:
  - Berfokus pada sistem LSP-P1 berbasis web.
  - Menggunakan otentikasi peran (superadmin, admin, asesor, asesi).

## Brand Commitments
- Nama Resmi: LSP SMKN 1 Gunungputri (LSP-P1 SMK).
- Identitas Visual: Desain modern, clean, dan profesional berkarakter warna biru malam & biru utama dengan typography Plus Jakarta Sans.

## Evidence on Hand
- Repositori aktif dengan struktur Laravel lengkap (`resources/views`, `app/Models`, `database/migrations`).
- Format Formulir BNSP FR.APL.01 & FR.APL.02 sesuai fisik dokumen uji kompetensi SMK.

## Product Principles
1. **Kepatuhan Standar BNSP & SKKNI**: Setiap struktur data skema, unit, elemen, dan KUK wajib presisi mengikuti acuan baku sertifikasi profesi.
2. **Kemudahan Pengalaman Asesi**: Alur pendaftaran dan asesmen mandiri dibuat bertahap, responsif, dan intuitif agar peserta tidak mengalami kebingungan saat melengkapi berkas & foto bukti.
3. **Akuntabilitas & Keamanan Data**: Seluruh aktivitas verifikasi, penilaian, rekomendasi, dan tanda tangan digital tercatat dalam audit log yang transparan.

## Accessibility & Inclusion
- Tampilan responsif di desktop maupun perangkat mobile.
- Elemen form menggunakan kontras warna yang cukup, font buatan yang mudah dibaca (Plus Jakarta Sans), serta elemen navigasi yang jelas.
