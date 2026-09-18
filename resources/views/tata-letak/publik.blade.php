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
                <li><a href="{{ route('beranda') }}#berita" class="nav-link">Berita</a></li>
            </ul>

            <div class="nav-aksi">
                @auth
                    <a href="{{ route(auth()->user()->peran . '.dasbor') }}" class="tombol-cta-header">
                        Dasbor
                    </a>
                @else
                    @if(!request()->routeIs('masuk'))
                        <a href="{{ route('masuk') }}" class="tombol-masuk">
                            Masuk
                        </a>
                    @endif
                    @if(!request()->routeIs('registrasi', 'daftar'))
                        <a href="{{ route('daftar') }}" class="tombol-cta-header">
                            Daftar Asesi
                        </a>
                    @else
                        <a href="{{ route('masuk') }}" class="tombol-cta-header">
                            Masuk Portal
                        </a>
                    @endif
                @endauth
            </div>
        </div>
    </nav>

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
