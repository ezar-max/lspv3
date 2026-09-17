<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>LSP SMKN 1 Gunungputri | Sertifikasi Profesi BNSP</title>
    <meta name="description" content="Lembaga Sertifikasi Profesi P1 SMKN 1 Gunungputri terlisensi resmi oleh Badan Nasional Sertifikasi Profesi (BNSP) No. BNSP-LSP-2629-ID.">

    <!-- Tipografi Public Sans & Plus Jakarta Sans -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Public+Sans:wght@400;500;600;700;800&family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap" rel="stylesheet">

    <!-- Berkas CSS Khusus Tamu (Aturan Peraturan No. 1, 5, 7) -->
    <link rel="stylesheet" href="{{ asset('css/tamu/beranda.css') }}">
</head>
<body>

    <!-- NAVBAR PUBLIK BERSIH -->
    <nav class="navbar-publik">
        <div class="wadah navbar-wadah">
            <a href="#beranda" class="brand-lsp" aria-label="Beranda LSP SMKN 1 Gunungputri">
                <img src="{{ asset('logo/logo-lsp.jpeg') }}" alt="Logo LSP SMKN 1 Gunungputri" class="brand-logo-img">
                <div class="brand-info">
                    <span class="brand-nama">LSP SMKN 1 Gunungputri</span>
                    <span class="brand-lisensi">Lisensi Resmi BNSP: BNSP-LSP-2629-ID</span>
                </div>
            </a>

            <ul class="nav-menu" id="menuNavigasi">
                <li><a href="#beranda" class="nav-link aktif">Beranda</a></li>
                <li><a href="#skema" class="nav-link">Skema</a></li>
                <li><a href="#berita" class="nav-link">Berita &amp; Pengumuman</a></li>
            </ul>

            @if(!request()->routeIs('masuk', 'registrasi', 'daftar'))
            <div class="nav-aksi">
                @auth
                    <a href="{{ route(auth()->user()->peran . '.dasbor') }}" class="tombol-cta-header">
                        Buka Dasbor
                    </a>
                @else
                    <a href="{{ route('masuk') }}" style="text-decoration: none; color: var(--biru-malam); font-weight: 700; font-size: 0.86rem; padding: 8px 14px; border-radius: var(--radius-sm); border: 1px solid var(--biru-soft); transition: var(--transisi); background: #ffffff;">
                        Masuk
                    </a>
                    <a href="{{ route('daftar') }}" class="tombol-cta-header">
                        Daftar Asesi
                    </a>
                @endauth
                <button type="button" class="tombol-menu-hp" id="tombolMenuHp" aria-label="Buka Menu">
                    ☰
                </button>
            </div>
            @endif
        </div>
    </nav>

    <!-- HERO SECTION (MENGIKUTI STRUKTUR REPO EZAR-MAX) -->
    <header class="hero-publik" id="beranda">
        <div class="hero-pola-titik"></div>
        <div class="wadah">
            <div class="hero-konten">
                <div class="hero-teks">
                    <span class="hero-lisensi-chip">
                        Lisensi Resmi BNSP &middot; KEP.1215/BNSP/V/2025
                    </span>
                    <h1 class="hero-judul">
                        Sertifikasi Profesi Vokasi
                        <span class="hero-judul-aksen">Unggul &amp; Berstandar Nasional</span>
                    </h1>
                    <p class="hero-deskripsi">
                        Portal resmi sistem informasi asesmen kompetensi keahlian LSP SMKN 1 Gunungputri. Menguji, memverifikasi, dan menerbitkan sertifikasi resmi berlisensi BNSP untuk mencetak lulusan vokasi siap kerja berdaya saing global.
                    </p>
                    <div class="hero-aksi">
                        <a href="#skema" class="tombol-utama">
                            Eksplorasi Skema Keahlian
                        </a>
                        <a href="{{ route('daftar') }}" class="tombol-outline">
                            Daftar Asesi Baru
                        </a>
                    </div>
                    <div class="hero-kepercayaan">
                        <span>Standar Kompetensi Nasional</span>
                        <span>Asesor Praktisi Industri</span>
                        <span>Sertifikat Diakui DUDI</span>
                    </div>
                </div>

                <div class="hero-visual">
                    <div class="hero-kartu-logo">
                        <img src="{{ asset('logo/logo-lsp.jpeg') }}" alt="Logo LSP SMKN 1 Gunungputri">
                        <div class="hero-kartu-caption">
                            <strong>LSP SMKN 1 Gunungputri</strong>
                            <span>Terakreditasi &amp; Berlisensi BNSP RI</span>
                            <div style="font-family: var(--font-mono); font-size: 0.82rem; font-weight: 700; color: var(--biru-utama); margin-top: 8px;">
                                BNSP-LSP-2629-ID
                            </div>
                            <div style="font-size: 0.76rem; color: var(--sukses); font-weight: 600; margin-top: 2px;">
                                Berlaku s/d 23 Mei 2030
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </header>


    <!-- SKEMA SERTIFIKASI KEAHLIAN VOKASI -->
    <section class="seksi-konten" id="skema">
        <div class="wadah">
            <div class="judul-seksi">
                <span class="kicker-section">Program Skema</span>
                <h2>Skema Sertifikasi Keahlian Vokasi</h2>
                <p>Pilih skema sertifikasi standar nasional BNSP yang sesuai program keahlian Anda.</p>
            </div>

            <!-- FILTER KATEGORI -->
            <div class="bilah-filter-skema">
                <button type="button" class="tombol-filter aktif" data-kategori="semua">Semua Skema (5)</button>
                <button type="button" class="tombol-filter" data-kategori="pplg">PPLG</button>
                <button type="button" class="tombol-filter" data-kategori="kimia">Kimia Industri</button>
                <button type="button" class="tombol-filter" data-kategori="mesin">Teknik Pemesinan</button>
                <button type="button" class="tombol-filter" data-kategori="las">Teknik Pengelasan</button>
                <button type="button" class="tombol-filter" data-kategori="elind">Elektronika Industri</button>
            </div>

            <div class="grid-skema-publik">
                @foreach($daftarSkema as $skema)
                    @php
                        $kategoriSlug = 'semua';
                        $namaRendah = strtolower($skema->nama_skema);
                        $bidangRendah = strtolower($skema->bidang_keahlian);

                        if (str_contains($namaRendah, 'pemrogram') || str_contains($bidangRendah, 'perangkat lunak')) {
                            $kategoriSlug = 'pplg';
                        } elseif (str_contains($namaRendah, 'ekstraksi') || str_contains($bidangRendah, 'kimia')) {
                            $kategoriSlug = 'kimia';
                        } elseif (str_contains($namaRendah, 'bubut') || str_contains($bidangRendah, 'mesin')) {
                            $kategoriSlug = 'mesin';
                        } elseif (str_contains($namaRendah, 'pengelasan') || str_contains($bidangRendah, 'las')) {
                            $kategoriSlug = 'las';
                        } elseif (str_contains($namaRendah, 'measuring') || str_contains($bidangRendah, 'elektronika')) {
                            $kategoriSlug = 'elind';
                        }
                    @endphp

                    <div class="kartu-skema-item" data-kategori="{{ $kategoriSlug }}">
                        <div>
                            <span class="skema-kategori-chip">{{ $skema->bidang_keahlian }}</span>
                            <h3 class="skema-nama">{{ $skema->nama_skema }}</h3>
                            <p class="skema-deskripsi">{{ $skema->deskripsi }}</p>
                        </div>
                        <div class="skema-footer">
                            <span class="skema-unit-count">{{ $skema->jumlah_unit }} Unit SKKNI</span>
                            <button type="button" class="tombol-skema-rincian" data-id-skema="{{ $skema->id }}" data-nama-skema="{{ $skema->nama_skema }}">
                                Rincian Unit
                            </button>
                        </div>
                    </div>
                @endforeach
            </div>
        </div>
    </section>

    <!-- BERITA & PENGUMUMAN ASESMEN (MENGIKUTI REPOSITORI EZAR-MAX) -->
    <section class="seksi-konten seksi-putih" id="berita">
        <div class="wadah">
            <div class="judul-seksi">
                <span class="kicker-section">Informasi Publik</span>
                <h2>Berita &amp; Pengumuman Asesmen</h2>
                <p>Update terkini seputar kegiatan uji kompetensi dan jadwal sertifikasi vokasi LSP SMKN 1 Gunungputri.</p>
            </div>

            <div class="grid-berita">
                @forelse($daftarBerita as $berita)
                    <article class="kartu-berita">
                        @if($berita->gambar)
                            <a href="{{ route('berita.detail', $berita->slug) }}" class="berita-gambar-wadah" style="margin: -24px -24px 16px -24px; overflow: hidden; border-radius: var(--radius-md) var(--radius-md) 0 0; max-height: 180px; display: block;">
                                <img src="{{ asset($berita->gambar) }}" alt="{{ $berita->judul }}" style="width: 100%; height: 180px; object-fit: cover; transition: transform 0.3s ease;">
                            </a>
                        @endif
                        <div class="berita-kencang">
                            <span class="lencana-kategori lencana-{{ strtolower($berita->kategori) }}">
                                {{ $berita->kategori }}
                            </span>
                            <span class="berita-tanggal">
                                {{ $berita->tanggal_publikasi ? $berita->tanggal_publikasi->translatedFormat('d M Y') : '' }}
                            </span>
                        </div>
                        <h3 class="berita-judul">
                            <a href="{{ route('berita.detail', $berita->slug) }}" style="color: inherit; text-decoration: none;">
                                {{ $berita->judul }}
                            </a>
                        </h3>
                        <p class="berita-ringkasan">{{ $berita->ringkasan ?: \Illuminate\Support\Str::limit(strip_tags($berita->konten), 120) }}</p>
                        <a href="{{ route('berita.detail', $berita->slug) }}" class="berita-tautan">
                            Informasi Selengkapnya &rarr;
                        </a>
                    </article>
                @empty
                    <div class="kosong-berita">
                        Belum ada berita atau pengumuman yang dipublikasikan saat ini.
                    </div>
                @endforelse
            </div>
        </div>
    </section>

    <!-- BANNER CTA SEKSI (STEEL BLUE & NAVY) -->
    <section class="seksi-konten" style="padding-top: 20px; padding-bottom: 50px;">
        <div class="wadah">
            <div class="banner-cta-publik">
                <h3>Siap Menguji &amp; Membuktikan Kompetensi Keahlian Anda?</h3>
                <p>Daftarkan diri Anda sekarang untuk mengikuti program sertifikasi kompetensi berlisensi resmi BNSP (BNSP-LSP-2629-ID) di LSP SMKN 1 Gunungputri.</p>
                <div class="cta-aksi">
                    <a href="#skema" class="tombol-cta-putih">
                        Pilih Skema Keahlian
                    </a>
                    <a href="{{ route('daftar') }}" class="tombol-cta-transparan">
                        Daftar Asesi Baru
                    </a>
                </div>
            </div>
        </div>
    </section>

    <!-- MODAL RINCIAN UNIT SKEMA -->
    <div class="latar-modal" id="modalUnitSkema" role="dialog" aria-modal="true">
        <div class="wadah-modal">
            <div class="kepala-modal">
                <h4 class="judul-modal-unit" id="judulModalUnit">Daftar Unit Kompetensi</h4>
                <button type="button" class="tombol-tutup-modal" id="tutupModalUnit" aria-label="Tutup">✕</button>
            </div>
            <div class="bilah-cari-modal">
                <input type="text" id="inputCariUnit" class="input-cari-unit" placeholder="Cari kode atau judul unit kompetensi...">
            </div>
            <div class="isi-modal" id="wadahTabelUnit">
                <!-- Konten unit dimuat secara asinkron via JS -->
            </div>
            <div class="kaki-modal">
                <button type="button" class="tombol-skema-rincian" id="tombolBatalModal">
                    Tutup
                </button>
            </div>
        </div>
    </div>

    <!-- FOOTER PUBLIK (MENGIKUTI REPO EZAR-MAX) -->
    <footer class="footer-publik">
        <div class="wadah">
            <div class="footer-grid">
                <div>
                    <div class="footer-brand-nama">LSP P1 SMKN 1 Gunungputri</div>
                    <div class="footer-brand-lisensi">No. Lisensi: BNSP-LSP-2629-ID</div>
                    <p class="footer-deskripsi">
                        Lembaga Sertifikasi Profesi Pihak Kesatu (LSP P1) SMKN 1 Gunungputri terlisensi resmi BNSP untuk menjamin mutu kompetensi dan daya saing profesional lulusan vokasi di dunia industri global.
                    </p>
                </div>

                <div>
                    <h4 class="footer-kolom-judul">Program Skema</h4>
                    <ul class="footer-daftar">
                        <li><a href="#skema">Pemrogram Junior (PPLG)</a></li>
                        <li><a href="#skema">Ekstraksi &amp; Destilasi (Kimia)</a></li>
                        <li><a href="#skema">Mesin Bubut (Pemesinan)</a></li>
                        <li><a href="#skema">Pengelasan Logam (Las)</a></li>
                        <li><a href="#skema">Measuring Operator (Elektronika)</a></li>
                    </ul>
                </div>

                <div>
                    <h4 class="footer-kolom-judul">Akses &amp; Tautan Resmi</h4>
                    <ul class="footer-daftar">
                        <li><a href="{{ route('masuk') }}">Masuk Portal Asesmen</a></li>
                        <li><a href="{{ route('daftar') }}">Pendaftaran Peserta Asesi</a></li>
                        <li><a href="https://bnsp.go.id" target="_blank" rel="noopener noreferrer">Badan Nasional Sertifikasi Profesi (BNSP)</a></li>
                        <li><a href="https://bnsp.go.id/lsp/smkn-1-gunungputri" target="_blank" rel="noopener noreferrer">Profil BNSP SMKN 1 Gunungputri</a></li>
                        <li><a href="https://smkn1gunungputri.sch.id" target="_blank" rel="noopener noreferrer">Website SMKN 1 Gunungputri</a></li>
                    </ul>
                </div>
            </div>

            <div class="footer-bawah">
                &copy; {{ date('Y') }} LSP P1 SMKN 1 Gunungputri. Lisensi BNSP: BNSP-LSP-2629-ID. Hak Cipta Dilindungi Undang-Undang.
            </div>
        </div>
    </footer>

    <!-- Berkas JS Khusus Tamu (Aturan Peraturan No. 1 & 6) -->
    <script src="{{ asset('js/tamu/beranda.js') }}"></script>
</body>
</html>
