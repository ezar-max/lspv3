<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>LSP SMKN 1 Gunungputri | Sertifikasi Profesi BNSP</title>
    <meta name="description" content="Lembaga Sertifikasi Profesi P1 SMKN 1 Gunungputri terlisensi resmi oleh Badan Nasional Sertifikasi Profesi (BNSP) No. BNSP-LSP-2629-ID.">

    <!-- Tipografi Inter & Plus Jakarta Sans -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&family=Plus+Jakarta+Sans:wght@500;600;700;800&display=swap" rel="stylesheet">

    <!-- Berkas CSS Khusus Tamu -->
    <link rel="stylesheet" href="{{ asset('css/tamu/beranda.css') }}">
</head>
<body>

    <!-- NAVBAR PUBLIK BERSIH (Full-Width Kotak Transparan Glassmorphism) -->
    <nav class="navbar-publik">
        <div class="wadah navbar-wadah">
            <a href="#beranda" class="brand-lsp" aria-label="Beranda LSP SMKN 1 Gunungputri">
                <img src="{{ asset('logo/logo-lsp.jpeg') }}" alt="Logo LSP SMKN 1 Gunungputri" class="brand-logo-img">
                <span class="brand-nama">LSP SMKN 1 Gunungputri</span>
            </a>

            <ul class="nav-menu" id="menuNavigasi">
                <li><a href="#beranda" class="nav-link aktif">Beranda</a></li>
                <li><a href="#skema" class="nav-link">Skema Keahlian</a></li>
                <li><a href="#berita" class="nav-link">Berita</a></li>
            </ul>

            @if(!request()->routeIs('masuk', 'registrasi', 'daftar'))
            <div class="nav-aksi">
                @auth
                    <a href="{{ route(auth()->user()->peran . '.dasbor') }}" class="tombol-cta-header">
                        Dasbor
                    </a>
                @else
                    <a href="{{ route('masuk') }}" class="tombol-masuk">
                        Masuk
                    </a>
                    <a href="{{ route('daftar') }}" class="tombol-cta-header">
                        Daftar Asesi
                    </a>
                @endauth
                <button type="button" class="tombol-menu-hp" id="tombolMenuHp" aria-label="Buka Menu">
                    <svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><line x1="3" y1="12" x2="21" y2="12"></line><line x1="3" y1="6" x2="21" y2="6"></line><line x1="3" y1="18" x2="21" y2="18"></line></svg>
                </button>
            </div>
            @endif
        </div>
    </nav>

    <!-- HERO SECTION (Linear Light Aesthetic - Margin Rapi & Pendaran Cahaya) -->
    <header class="hero-publik" id="beranda">
        <div class="hero-glow"></div>
        <div class="wadah">
            <div class="hero-konten">
                <h1 class="hero-judul">
                    Sertifikasi Profesi Vokasi <br />
                    <span class="hero-judul-aksen">Standar Nasional &amp; Industri</span>
                </h1>
                
                <p class="hero-deskripsi">
                    Uji kompetensi keahlian terstandar BNSP bagi siswa dan profesional di SMKN 1 Gunungputri untuk melahirkan lulusan unggul berdaya saing global.
                </p>
                
                <div class="hero-aksi">
                    <a href="#skema" class="tombol-utama">
                        <span>Jelajahi Skema</span>
                        <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round"><line x1="5" y1="12" x2="19" y2="12"></line><polyline points="12 5 19 12 12 19"></polyline></svg>
                    </a>
                    <a href="{{ route('daftar') }}" class="tombol-outline">
                        Daftar Peserta Asesi
                    </a>
                </div>
            </div>
        </div>
    </header>

    <!-- SKEMA SERTIFIKASI KEAHLIAN VOKASI -->
    <section class="seksi-konten seksi-abu" id="skema">
        <div class="wadah">
            <div class="judul-seksi reveal">
                <h2>Skema Sertifikasi Keahlian</h2>
                <p>Standar Kompetensi Kerja Nasional Indonesia (SKKNI) terverifikasi BNSP.</p>
            </div>

            <!-- FILTER KATEGORI -->
            <div class="bilah-filter-skema reveal">
                <button type="button" class="tombol-filter aktif" data-kategori="semua">Semua Skema ({{ count($daftarSkema) }})</button>
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

                    <div class="kartu-skema-item reveal" data-kategori="{{ $kategoriSlug }}">
                        <div class="skema-kartu-atas">
                            <span class="skema-kategori-chip">{{ $skema->bidang_keahlian }}</span>
                            <span class="skema-unit-count">{{ $skema->jumlah_unit }} Unit</span>
                        </div>

                        <div class="skema-kartu-tengah">
                            <h3 class="skema-nama">{{ $skema->nama_skema }}</h3>
                            @if($skema->deskripsi)
                                <p class="skema-deskripsi">{{ $skema->deskripsi }}</p>
                            @endif
                        </div>

                        <div class="skema-kartu-bawah">
                            <button type="button" class="tombol-skema-rincian" data-id-skema="{{ $skema->id }}" data-nama-skema="{{ $skema->nama_skema }}">
                                <span>Rincian Unit</span>
                                <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round"><line x1="5" y1="12" x2="19" y2="12"></line><polyline points="12 5 19 12 12 19"></polyline></svg>
                            </button>
                        </div>
                    </div>
                @endforeach
            </div>
        </div>
    </section>

    <!-- BERITA & PENGUMUMAN -->
    <section class="seksi-konten" id="berita">
        <div class="wadah">
            <div class="judul-seksi reveal">
                <h2>Berita &amp; Pengumuman</h2>
                <p>Informasi jadwal dan kegiatan sertifikasi kompetensi terbaru.</p>
            </div>

            <div class="grid-berita">
                @forelse($daftarBerita as $berita)
                    <article class="kartu-berita reveal">
                        @if($berita->gambar)
                            <a href="{{ route('berita.detail', $berita->slug) }}" class="berita-gambar-wadah">
                                <img src="{{ asset($berita->gambar) }}" alt="{{ $berita->judul }}" loading="lazy">
                            </a>
                        @endif
                        <div class="berita-kencang">
                            <span class="lencana-kategori">
                                {{ $berita->kategori }}
                            </span>
                            <span class="berita-tanggal">
                                &middot; {{ $berita->tanggal_publikasi ? $berita->tanggal_publikasi->translatedFormat('d M Y') : '' }}
                            </span>
                        </div>
                        <h3 class="berita-judul">
                            <a href="{{ route('berita.detail', $berita->slug) }}" style="color: inherit; text-decoration: none;">
                                {{ $berita->judul }}
                            </a>
                        </h3>
                        <p class="berita-ringkasan">{{ $berita->ringkasan ?: \Illuminate\Support\Str::limit(strip_tags($berita->konten), 120) }}</p>
                        <a href="{{ route('berita.detail', $berita->slug) }}" class="berita-tautan">
                            Baca selengkapnya &rarr;
                        </a>
                    </article>
                @empty
                    <div class="kosong-berita" style="grid-column: 1 / -1; text-align: center; color: var(--teks-abu); padding: 3rem 0;">
                        Belum ada berita atau pengumuman yang dipublikasikan saat ini.
                    </div>
                @endforelse
            </div>
        </div>
    </section>

    <!-- BANNER CTA SEKSI -->
    <section class="seksi-konten seksi-abu">
        <div class="wadah">
            <div class="banner-cta-publik reveal">
                <h3>Siap Meraih Sertifikasi Kompetensi Resmi?</h3>
                <p>Uji kemampuan Anda dan dapatkan pengakuan profesi berstandar nasional dari Badan Nasional Sertifikasi Profesi (BNSP).</p>
                <div class="hero-aksi" style="justify-content: center;">
                    <a href="#skema" class="tombol-outline">
                        Pilih Program Skema
                    </a>
                    <a href="{{ route('daftar') }}" class="tombol-utama">
                        <span>Daftar Sekarang</span>
                        <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round"><line x1="5" y1="12" x2="19" y2="12"></line><polyline points="12 5 19 12 12 19"></polyline></svg>
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
                <button type="button" class="tombol-outline" id="tombolBatalModal" style="padding: 6px 14px; font-size: 0.85rem;">
                    Tutup
                </button>
            </div>
        </div>
    </div>

    <!-- FOOTER PUBLIK -->
    <footer class="footer-publik reveal">
        <div class="wadah">
            <div class="footer-grid">
                <div>
                    <div class="footer-brand-nama">LSP P1 SMKN 1 Gunungputri</div>
                    <div class="footer-brand-lisensi">Lisensi BNSP: BNSP-LSP-2629-ID</div>
                    <p class="footer-deskripsi">
                        Lembaga Sertifikasi Profesi Pihak Kesatu (LSP P1) SMKN 1 Gunungputri berkomitmen menjamin mutu kompetensi dan daya saing profesional lulusan vokasi di dunia industri.
                    </p>
                </div>

                <div>
                    <h4 class="footer-kolom-judul">Program Skema</h4>
                    <ul class="footer-daftar">
                        <li><a href="#skema">Rekayasa Perangkat Lunak</a></li>
                        <li><a href="#skema">Kimia Industri</a></li>
                        <li><a href="#skema">Teknik Pemesinan</a></li>
                        <li><a href="#skema">Teknik Pengelasan</a></li>
                        <li><a href="#skema">Elektronika Industri</a></li>
                    </ul>
                </div>

                <div>
                    <h4 class="footer-kolom-judul">Akses &amp; Informasi</h4>
                    <ul class="footer-daftar">
                        <li><a href="{{ route('masuk') }}">Masuk Portal Asesmen</a></li>
                        <li><a href="{{ route('daftar') }}">Pendaftaran Peserta Asesi</a></li>
                        <li><a href="https://bnsp.go.id" target="_blank" rel="noopener noreferrer">Website Resmi BNSP</a></li>
                        <li><a href="https://smkn1gunungputri.sch.id" target="_blank" rel="noopener noreferrer">SMKN 1 Gunungputri</a></li>
                    </ul>
                </div>
            </div>

            <div class="footer-bawah">
                &copy; {{ date('Y') }} LSP P1 SMKN 1 Gunungputri. Lisensi Resmi BNSP-LSP-2629-ID. Hak Cipta Dilindungi.
            </div>
        </div>
    </footer>

    <!-- Berkas JS Khusus Tamu -->
    <script src="{{ asset('js/tamu/beranda.js') }}"></script>
</body>
</html>
