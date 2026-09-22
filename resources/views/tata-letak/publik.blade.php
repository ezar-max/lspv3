<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('judul', 'LSP SMKN 1 Gunungputri') - Lembaga Sertifikasi Profesi SMK Negeri 1 Gunungputri</title>
    <link rel="icon" type="image/jpeg" href="{{ asset('images/logo-lsp.jpeg') }}">
    
    <!-- Google Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Public+Sans:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    
    <!-- FontAwesome Icons -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">

    <!-- CSS Tata Letak & Animasi Global -->
    <link rel="stylesheet" href="{{ asset('css/tata-letak.css') }}">
    <link rel="stylesheet" href="{{ asset('css/animasi.css') }}">

    <!-- CSS Khusus Per Halaman -->
    @stack('css')
</head>
<body class="animasi-fade">

    <!-- SCROLL PROGRESS BAR -->
    <div class="scroll-progress"></div>

    <!-- NAVBAR PUBLIK BERSIH (Full-Width Kotak Transparan Glassmorphism) -->
    <nav class="navbar-publik">
        <div class="navbar-wadah">
            <a href="{{ route('beranda') }}" class="brand-lsp" aria-label="Beranda LSP SMKN 1 Gunungputri">
                <img src="{{ asset('logo/logo-lsp.jpeg') }}" alt="Logo LSP SMKN 1 Gunungputri" class="brand-logo-img">
                <span class="brand-nama">LSP SMKN 1 Gunungputri</span>
            </a>

            <ul class="nav-menu" id="menuNavigasi">
                <li><a href="{{ route('beranda') }}#beranda" class="nav-link {{ request()->routeIs('beranda') ? 'aktif' : '' }}">Beranda</a></li>
                <li><a href="{{ route('beranda') }}#skema" class="nav-link">Skema Keahlian</a></li>
                <li><a href="{{ route('beranda') }}#berita" class="nav-link">Berita &amp; Pengumuman</a></li>
            </ul>

            <div class="nav-aksi">
                @auth
                    <a href="{{ route(auth()->user()->peran . '.dasbor') }}" class="tombol-cta-header">
                        Buka Dasbor
                    </a>
                @else
                    <a href="{{ route('masuk') }}" class="tombol-masuk {{ request()->routeIs('masuk') ? 'aktif' : '' }}">
                        Masuk
                    </a>
                    <a href="{{ route('daftar') }}" class="tombol-cta-header {{ (request()->routeIs('daftar') || request()->routeIs('registrasi')) ? 'aktif' : '' }}">
                        Daftar Asesi
                    </a>
                @endauth
                <button type="button" class="tombol-menu-hp" id="tombolMenuHp" aria-label="Buka Menu" aria-expanded="false" aria-controls="drawerMobileOverlay">
                    <svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><line x1="3" y1="12" x2="21" y2="12"></line><line x1="3" y1="6" x2="21" y2="6"></line><line x1="3" y1="18" x2="21" y2="18"></line></svg>
                </button>
            </div>
        </div>
    </nav>

    <!-- MOBILE DRAWER NAVIGATION (Hanya aktif di mobile, 100% tersembunyi di desktop) -->
    <div class="drawer-mobile-overlay" id="drawerMobileOverlay" aria-hidden="true">
        <div class="drawer-mobile-panel" id="drawerMobilePanel" role="dialog" aria-modal="true" aria-label="Menu Navigasi Mobile">
            <div class="drawer-mobile-header">
                <div class="drawer-brand">
                    <div class="drawer-logo-wrap">
                        <img src="{{ asset('logo/logo-lsp.jpeg') }}" alt="Logo LSP SMKN 1 Gunungputri" class="drawer-logo-img">
                    </div>
                    <div class="drawer-brand-text">
                        <span class="drawer-brand-nama">LSP SMKN 1 Gunungputri</span>
                        <span class="drawer-brand-sub">Sertifikasi BNSP Resmi</span>
                    </div>
                </div>
                <button type="button" class="drawer-tombol-tutup" id="drawerTombolTutup" aria-label="Tutup menu navigasi">
                    <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round">
                        <line x1="18" y1="6" x2="6" y2="18"></line>
                        <line x1="6" y1="6" x2="18" y2="18"></line>
                    </svg>
                </button>
            </div>
            <div class="drawer-mobile-body">
                <ul class="drawer-nav-list">
                    <li>
                        <a href="{{ route('beranda') }}#beranda" class="drawer-nav-link {{ request()->routeIs('beranda') ? 'aktif' : '' }}">
                            <div class="drawer-nav-link-left">
                                <div class="drawer-nav-icon">
                                    <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M3 9l9-7 9 7v11a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2z"></path><polyline points="9 22 9 12 15 12 15 22"></polyline></svg>
                                </div>
                                <span>Beranda</span>
                            </div>
                            <svg class="drawer-nav-chevron" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><polyline points="9 18 15 12 9 6"></polyline></svg>
                        </a>
                    </li>
                    <li>
                        <a href="{{ route('beranda') }}#skema" class="drawer-nav-link">
                            <div class="drawer-nav-link-left">
                                <div class="drawer-nav-icon">
                                    <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M22 19a2 2 0 0 1-2 2H4a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h5l2 3h9a2 2 0 0 1 2 2z"></path></svg>
                                </div>
                                <span>Skema Keahlian</span>
                            </div>
                            <svg class="drawer-nav-chevron" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><polyline points="9 18 15 12 9 6"></polyline></svg>
                        </a>
                    </li>
                    <li>
                        <a href="{{ route('beranda') }}#berita" class="drawer-nav-link">
                            <div class="drawer-nav-link-left">
                                <div class="drawer-nav-icon">
                                    <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M19 20H5a2 2 0 0 1-2-2V6a2 2 0 0 1 2-2h10a2 2 0 0 1 2 2v1m2 13a2 2 0 0 1-2-2V7m2 13a2 2 0 0 0 2-2V9a2 2 0 0 0-2-2h-2m-4-3H9M7 16h6M7 8h6v4H7V8z"></path></svg>
                                </div>
                                <span>Berita &amp; Pengumuman</span>
                            </div>
                            <svg class="drawer-nav-chevron" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><polyline points="9 18 15 12 9 6"></polyline></svg>
                        </a>
                    </li>
                </ul>

                <div class="drawer-aksi-wadah">
                    @auth
                        <a href="{{ route(auth()->user()->peran . '.dasbor') }}" class="drawer-btn-cta">
                            <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect x="3" y="3" width="7" height="7"></rect><rect x="14" y="3" width="7" height="7"></rect><rect x="14" y="14" width="7" height="7"></rect><rect x="3" y="14" width="7" height="7"></rect></svg>
                            <span>Buka Dasbor Saya</span>
                        </a>
                    @else
                        <a href="{{ route('masuk') }}" class="drawer-btn-masuk {{ request()->routeIs('masuk') ? 'aktif' : '' }}">
                            <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M15 3h4a2 2 0 0 1 2 2v14a2 2 0 0 1-2 2h-4"></path><polyline points="10 17 15 12 10 7"></polyline><line x1="15" y1="12" x2="3" y2="12"></line></svg>
                            <span>Masuk Portal</span>
                        </a>
                        <a href="{{ route('daftar') }}" class="drawer-btn-cta {{ (request()->routeIs('daftar') || request()->routeIs('registrasi')) ? 'aktif' : '' }}">
                            <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M16 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"></path><circle cx="8.5" cy="7" r="4"></circle><line x1="20" y1="8" x2="20" y2="14"></line><line x1="23" y1="11" x2="17" y2="11"></line></svg>
                            <span>Daftar Asesi Baru</span>
                        </a>
                    @endauth
                </div>

                <div class="drawer-footer-info">
                    <div class="drawer-footer-badge">
                        <span class="drawer-dot-verified"></span>
                        <span>BNSP-LSP-2629-ID Terlisensi</span>
                    </div>
                    <p>&copy; {{ date('Y') }} SMKN 1 Gunungputri</p>
                </div>
            </div>
        </div>
    </div>

    <!-- KONTEN UTAMA -->
    <main>
        @yield('konten')
    </main>

    <!-- SCROLL TO TOP BUTTON -->
    <button class="scroll-top" title="Kembali ke Atas">
        <i class="fa-solid fa-arrow-up"></i>
    </button>

    <!-- FOOTER PUBLIK -->
    <footer style="background: var(--biru-malam); color: var(--putih); padding: 4rem 2rem 2rem 2rem; margin-top: 5rem;">
        <div style="max-width: 1200px; margin: 0 auto; display: grid; grid-template-columns: 2fr 1fr 1fr; gap: 3rem;">
            <div>
                <div class="brand-lsp" style="color: var(--putih); margin-bottom: 1rem; display: flex; align-items: center; gap: 0.85rem;">
                    <img src="{{ asset('images/logo-lsp.jpeg') }}" alt="Logo LSP" style="height: 48px; width: 48px; object-fit: contain; border-radius: 8px; background: #fff; padding: 2px;">
                    <div>
                        <div style="font-weight: 800; font-size: 1.15rem; color: #fff;">LSP P1 SMKN 1 Gunungputri</div>
                        <div style="font-size: 0.78rem; color: var(--biru-muda); font-weight: 700;">No. Lisensi: BNSP-LSP-2629-ID</div>
                    </div>
                </div>
                <p style="color: #94a3b8; font-size: 0.9rem; line-height: 1.6; max-width: 420px;">
                    Lembaga Sertifikasi Profesi Pihak Kesatu (LSP P1) SMKN 1 Gunungputri terlisensi resmi BNSP (No. SK: KEP.1215/BNSP/V/2025 | No. Lisensi: BNSP-LSP-2629-ID) untuk menjamin mutu kompetensi dan daya saing profesional lulusan vokasi di dunia industri global.
                </p>
            </div>
            <div>
                <h4 style="color: var(--biru-muda); margin-bottom: 1rem;">Tautan Cepat</h4>
                <ul style="list-style: none; display: flex; flex-direction: column; gap: 0.5rem;">
                    <li><a href="{{ route('profil-lsp') }}" style="color: #cbd5e1;">Tentang Kami</a></li>
                    <li><a href="{{ route('publik.skema') }}" style="color: #cbd5e1;">Skema Sertifikasi</a></li>
                    <li><a href="{{ route('registrasi') }}" style="color: #cbd5e1;">Pendaftaran Asesi</a></li>
                    <li><a href="{{ route('publik.berita') }}" style="color: #cbd5e1;">Berita & Pengumuman</a></li>
                </ul>
            </div>
            <div>
                <h4 style="color: var(--biru-muda); margin-bottom: 1rem;">Kontak Resmi</h4>
                <p style="color: #cbd5e1; font-size: 0.88rem; margin-bottom: 0.5rem;"><i class="fa-solid fa-envelope" style="margin-right: 0.5rem;"></i> lsp.smkn1gnputri@gmail.com</p>
                <p style="color: #cbd5e1; font-size: 0.88rem; margin-bottom: 0.5rem;"><i class="fa-solid fa-phone" style="margin-right: 0.5rem;"></i> (021) 867-3310</p>
                <p style="color: #94a3b8; font-size: 0.82rem; line-height: 1.5;"><i class="fa-solid fa-location-dot" style="margin-right: 0.5rem;"></i> Jl. Barokah No. 6, Desa Wanaherang, Kecamatan Gunungputri, Kabupaten Bogor, Jawa Barat</p>
            </div>
        </div>
        <div style="max-width: 1200px; margin: 3rem auto 0 auto; padding-top: 1.5rem; border-top: 1px solid #334155; text-align: center; font-size: 0.85rem; color: #94a3b8;">
            &copy; {{ date('Y') }} Lembaga Sertifikasi Profesi Pihak Kesatu (LSP P1) SMKN 1 Gunungputri. No. Lisensi Resmi: BNSP-LSP-2629-ID. Hak Cipta Dilindungi Undang-Undang.
        </div>
    </footer>

    <!-- SweetAlert2 Script -->
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

    <!-- JS Tata Letak Global -->
    <script src="{{ asset('js/tata-letak.js') }}"></script>

    <!-- Auto Popup Notifikasi Session Flash (SweetAlert2 - Format Gambar 2) -->
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            // Mobile Drawer Toggle
            const tombolMenuHp = document.getElementById('tombolMenuHp');
            const drawerOverlay = document.getElementById('drawerMobileOverlay');
            const drawerTutup = document.getElementById('drawerTombolTutup');
            const drawerLinks = drawerOverlay ? drawerOverlay.querySelectorAll('.drawer-nav-link, .drawer-btn-masuk, .drawer-btn-cta') : [];

            function bukaDrawer() {
                if (drawerOverlay) {
                    drawerOverlay.classList.add('aktif');
                    drawerOverlay.setAttribute('aria-hidden', 'false');
                    document.body.style.overflow = 'hidden';
                    if (tombolMenuHp) tombolMenuHp.setAttribute('aria-expanded', 'true');
                    if (drawerTutup) drawerTutup.focus();
                }
            }

            function tutupDrawer() {
                if (drawerOverlay) {
                    drawerOverlay.classList.remove('aktif');
                    drawerOverlay.setAttribute('aria-hidden', 'true');
                    document.body.style.overflow = '';
                    if (tombolMenuHp) {
                        tombolMenuHp.setAttribute('aria-expanded', 'false');
                        tombolMenuHp.focus();
                    }
                }
            }

            if (tombolMenuHp) {
                tombolMenuHp.addEventListener('click', function(e) {
                    e.stopPropagation();
                    if (drawerOverlay && drawerOverlay.classList.contains('aktif')) {
                        tutupDrawer();
                    } else {
                        bukaDrawer();
                    }
                });
            }

            if (drawerTutup) {
                drawerTutup.addEventListener('click', tutupDrawer);
            }

            if (drawerOverlay) {
                drawerOverlay.addEventListener('click', function(e) {
                    if (e.target === drawerOverlay) {
                        tutupDrawer();
                    }
                });

                drawerLinks.forEach(function(link) {
                    link.addEventListener('click', function() {
                        tutupDrawer();
                    });
                });

                document.addEventListener('keydown', function(e) {
                    if (e.key === 'Escape' && drawerOverlay.classList.contains('aktif')) {
                        tutupDrawer();
                    }
                });
            }

            @if (session('sukses'))
                Swal.fire({
                    icon: 'success',
                    title: 'Berhasil!',
                    text: {!! json_encode(session('sukses')) !!},
                    confirmButtonColor: '#16a34a',
                    confirmButtonText: '✓ Tutup',
                    customClass: {
                        popup: 'swal2-modern-popup'
                    }
                });
            @endif

            @if (session('error'))
                Swal.fire({
                    icon: 'error',
                    title: 'Perhatian!',
                    text: {!! json_encode(session('error')) !!},
                    confirmButtonColor: '#ef4444',
                    confirmButtonText: '✓ Tutup',
                    customClass: {
                        popup: 'swal2-modern-popup'
                    }
                });
            @endif

            @if (session('info'))
                Swal.fire({
                    icon: 'info',
                    title: 'Informasi',
                    text: {!! json_encode(session('info')) !!},
                    confirmButtonColor: '#2563eb',
                    confirmButtonText: '✓ Tutup',
                    customClass: {
                        popup: 'swal2-modern-popup'
                    }
                });
            @endif
        });
    </script>

    <!-- JS Khusus Per Halaman -->
    @stack('js')
</body>
</html>
