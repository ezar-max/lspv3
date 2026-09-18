/**
 * BERANDA.JS - LSP SMKN 1 GUNUNGPUTRI (LANDING TAMU)
 * Logika Filter Skema, Modal Rincian Unit (Bottom-Sheet di Mobile),
 * Mobile Drawer, Back to Top, Scroll Reveal, & Scrollspy
 */

document.addEventListener('DOMContentLoaded', () => {
  // 1. Mobile Drawer & Navigation (Progressive Enhancement & Accessible)
  const tombolMenuHp = document.getElementById('tombolMenuHp');
  const drawerOverlay = document.getElementById('drawerMobileOverlay');
  const drawerTutup = document.getElementById('drawerTombolTutup');
  const menuNavigasi = document.getElementById('menuNavigasi');
  const drawerLinks = drawerOverlay ? drawerOverlay.querySelectorAll('.drawer-nav-link, .drawer-btn-masuk, .drawer-btn-cta') : [];

  function bukaDrawer() {
    if (drawerOverlay) {
      drawerOverlay.classList.add('aktif');
      drawerOverlay.setAttribute('aria-hidden', 'false');
      document.body.style.overflow = 'hidden';
      if (tombolMenuHp) tombolMenuHp.setAttribute('aria-expanded', 'true');
      if (drawerTutup) drawerTutup.focus();
    } else if (menuNavigasi && tombolMenuHp) {
      menuNavigasi.classList.add('buka');
      tombolMenuHp.setAttribute('aria-expanded', 'true');
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
    } else if (menuNavigasi && tombolMenuHp) {
      menuNavigasi.classList.remove('buka');
      tombolMenuHp.setAttribute('aria-expanded', 'false');
    }
  }

  if (tombolMenuHp) {
    tombolMenuHp.addEventListener('click', (e) => {
      e.stopPropagation();
      const isDrawerActive = drawerOverlay ? drawerOverlay.classList.contains('aktif') : false;
      const isMenuOpen = menuNavigasi ? menuNavigasi.classList.contains('buka') : false;

      if (isDrawerActive || isMenuOpen) {
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
    drawerOverlay.addEventListener('click', (e) => {
      if (e.target === drawerOverlay) {
        tutupDrawer();
      }
    });

    drawerLinks.forEach(link => {
      link.addEventListener('click', () => {
        tutupDrawer();
      });
    });
  }

  if (menuNavigasi) {
    document.addEventListener('click', (e) => {
      if (!menuNavigasi.contains(e.target) && e.target !== tombolMenuHp) {
        menuNavigasi.classList.remove('buka');
        if (tombolMenuHp) tombolMenuHp.setAttribute('aria-expanded', 'false');
      }
    });

    menuNavigasi.querySelectorAll('.nav-link').forEach(link => {
      link.addEventListener('click', () => {
        menuNavigasi.classList.remove('buka');
        if (tombolMenuHp) tombolMenuHp.setAttribute('aria-expanded', 'false');
      });
    });
  }

  // 2. Sticky Navbar Scrolled Effect & Scrollspy
  const navbar = document.querySelector('.navbar-publik');
  const navLinks = document.querySelectorAll('.nav-menu .nav-link');
  const sections = document.querySelectorAll('header[id], section[id]');
  const drawerNavLinks = document.querySelectorAll('.drawer-nav-link');

  function onScroll() {
    const scrollY = window.scrollY;

    // Scrolled class for elevated navbar
    if (navbar) {
      if (scrollY > 20) {
        navbar.classList.add('scrolled');
      } else {
        navbar.classList.remove('scrolled');
      }
    }

    // Scrollspy active indicator
    let currentSectionId = '';
    sections.forEach(section => {
      const sectionTop = section.offsetTop - 130;
      const sectionHeight = section.offsetHeight;
      if (scrollY >= sectionTop && scrollY < sectionTop + sectionHeight) {
        currentSectionId = section.getAttribute('id');
      }
    });

    if (currentSectionId) {
      navLinks.forEach(link => {
        const href = link.getAttribute('href');
        if (href === `#${currentSectionId}`) {
          link.classList.add('aktif');
        } else {
          link.classList.remove('aktif');
        }
      });

      drawerNavLinks.forEach(link => {
        const href = link.getAttribute('href');
        if (href === `#${currentSectionId}`) {
          link.classList.add('aktif');
        } else {
          link.classList.remove('aktif');
        }
      });
    }
  }

  window.addEventListener('scroll', onScroll, { passive: true });
  onScroll(); // Inisialisasi posisi awal

  // 3. Filter Skema Berdasarkan Kategori
  const tombolFilters = document.querySelectorAll('.tombol-filter');
  const kartuSkemas = document.querySelectorAll('.kartu-skema-item');

  tombolFilters.forEach(tombol => {
    tombol.addEventListener('click', () => {
      tombolFilters.forEach(t => t.classList.remove('aktif'));
      tombol.classList.add('aktif');

      const kategoriDipilih = tombol.getAttribute('data-kategori');

      kartuSkemas.forEach(kartu => {
        const kategoriKartu = kartu.getAttribute('data-kategori');
        if (kategoriDipilih === 'semua' || kategoriKartu === kategoriDipilih) {
          kartu.classList.remove('sembunyi');
        } else {
          kartu.classList.add('sembunyi');
        }
      });
    });
  });

  // 4. Modal Rincian Unit Skema (Bottom-Sheet di Mobile)
  const modalUnit = document.getElementById('modalUnitSkema');
  const tutupModal = document.getElementById('tutupModalUnit');
  const tombolBatal = document.getElementById('tombolBatalModal');
  const judulModal = document.getElementById('judulModalUnit');
  const wadahTabel = document.getElementById('wadahTabelUnit');
  const inputCari = document.getElementById('inputCariUnit');

  let unitsDataCache = [];
  let elemenPemicuModal = null;

  function bukaModal() {
    if (modalUnit) {
      modalUnit.classList.add('aktif');
      document.body.style.overflow = 'hidden';
      if (inputCari) {
        setTimeout(() => inputCari.focus(), 150);
      }
    }
  }

  function tutupModalHandler() {
    if (modalUnit) {
      modalUnit.classList.remove('aktif');
      document.body.style.overflow = '';
      if (inputCari) inputCari.value = '';
      if (elemenPemicuModal) {
        elemenPemicuModal.focus();
        elemenPemicuModal = null;
      }
    }
  }

  if (tutupModal) tutupModal.addEventListener('click', tutupModalHandler);
  if (tombolBatal) tombolBatal.addEventListener('click', tutupModalHandler);

  if (modalUnit) {
    modalUnit.addEventListener('click', (e) => {
      if (e.target === modalUnit) {
        tutupModalHandler();
      }
    });
  }

  // Universal Escape Key Handler untuk Drawer dan Modal
  document.addEventListener('keydown', (e) => {
    if (e.key === 'Escape') {
      if (drawerOverlay && drawerOverlay.classList.contains('aktif')) {
        tutupDrawer();
      } else if (modalUnit && modalUnit.classList.contains('aktif')) {
        tutupModalHandler();
      }
    }
  });

  function renderTabelUnit(units) {
    if (!wadahTabel) return;

    if (!units || units.length === 0) {
      wadahTabel.innerHTML = `
        <div style="text-align: center; padding: 2.5rem 1rem; color: var(--teks-abu);">
          Tidak ada unit kompetensi yang ditemukan.
        </div>
      `;
      return;
    }

    let html = `
      <table class="tabel-unit-modal">
        <thead>
          <tr>
            <th style="width: 45px; text-align: center;">No</th>
            <th style="width: 160px;">Kode Unit</th>
            <th>Judul Unit Kompetensi</th>
            <th style="width: 90px; text-align: center;">Standar</th>
          </tr>
        </thead>
        <tbody>
    `;

    units.forEach((u, idx) => {
      const kode = u.kode_unit || '-';
      const judul = u.judul_unit || u.nama_unit || '-';
      const standar = u.standar_kompetensi || u.jenis_standar || 'SKKNI';

      html += `
        <tr>
          <td style="text-align: center; color: var(--teks-abu);">${idx + 1}</td>
          <td><strong style="color: var(--biru-utama); font-family: var(--font-mono); font-size: 0.82rem;">${kode}</strong></td>
          <td style="font-weight: 500; color: var(--teks-gelap);">${judul}</td>
          <td style="text-align: center;"><span style="font-size: 0.74rem; font-weight: 700; background: var(--biru-tint); color: var(--biru-utama); padding: 3px 8px; border-radius: 4px;">${standar}</span></td>
        </tr>
      `;
    });

    html += `
        </tbody>
      </table>
    `;

    wadahTabel.innerHTML = html;
  }

  // Live filter dalam modal
  if (inputCari) {
    inputCari.addEventListener('input', (e) => {
      const q = e.target.value.toLowerCase().trim();
      if (!q) {
        renderTabelUnit(unitsDataCache);
        return;
      }

      const terfilter = unitsDataCache.filter(u => {
        const kode = (u.kode_unit || '').toLowerCase();
        const judul = (u.judul_unit || u.nama_unit || '').toLowerCase();
        return kode.includes(q) || judul.includes(q);
      });

      renderTabelUnit(terfilter);
    });
  }

  // Tombol Rincian Unit Click
  const tombolRincians = document.querySelectorAll('.tombol-skema-rincian');
  tombolRincians.forEach(btn => {
    btn.addEventListener('click', async () => {
      elemenPemicuModal = btn;
      const idSkema = btn.getAttribute('data-id-skema');
      const namaSkema = btn.getAttribute('data-nama-skema') || 'Unit Kompetensi';

      if (judulModal) {
        judulModal.textContent = `Daftar Unit - ${namaSkema}`;
      }

      if (wadahTabel) {
        wadahTabel.innerHTML = `
          <div style="text-align: center; padding: 3rem 1rem; color: var(--teks-abu);">
            <div style="display: inline-block; width: 28px; height: 28px; border: 3px solid var(--biru-soft); border-top-color: var(--biru-utama); border-radius: 50%; animation: spin 0.8s linear infinite; margin-bottom: 0.75rem;"></div>
            <div>Memuat daftar unit kompetensi...</div>
          </div>
          <style>
            @keyframes spin { to { transform: rotate(360deg); } }
          </style>
        `;
      }

      bukaModal();

      try {
        const res = await fetch(`/api/skema/${idSkema}/units`, {
          headers: {
            'Accept': 'application/json'
          }
        });

        if (!res.ok) {
          throw new Error('Gagal memuat unit kompetensi');
        }

        const data = await res.json();
        unitsDataCache = data.units || data.data?.unit_kompetensi || [];
        renderTabelUnit(unitsDataCache);
      } catch (err) {
        if (wadahTabel) {
          wadahTabel.innerHTML = `
            <div style="text-align: center; padding: 2.5rem 1rem; color: #dc2626;">
              Gagal mengambil data unit kompetensi skema ini. Silakan coba lagi nanti.
            </div>
          `;
        }
      }
    });
  });

  // 5. Scroll Reveal Animation dengan IntersectionObserver
  const reveals = document.querySelectorAll('.reveal');
  if (reveals.length > 0 && 'IntersectionObserver' in window) {
    const revealObserver = new IntersectionObserver((entries) => {
      entries.forEach(entry => {
        if (entry.isIntersecting) {
          entry.target.classList.add('aktif');
        } else {
          // Hanya hapus jika sudah scroll jauh keluar dari viewport
          const rect = entry.boundingClientRect;
          if (rect.top > window.innerHeight || rect.bottom < 0) {
            entry.target.classList.remove('aktif');
          }
        }
      });
    }, {
      root: null,
      threshold: 0.08,
      rootMargin: '0px 0px -40px 0px'
    });

    reveals.forEach(el => revealObserver.observe(el));
  } else {
    // Fallback jika browser tidak support IntersectionObserver
    reveals.forEach(el => el.classList.add('aktif'));
  }

  // 6. Tombol Floating Back to Top (Hanya muncul setelah scroll)
  const tombolKeAtas = document.getElementById('tombolKeAtas');
  if (tombolKeAtas) {
    window.addEventListener('scroll', () => {
      if (window.scrollY > 350) {
        tombolKeAtas.classList.add('terlihat');
      } else {
        tombolKeAtas.classList.remove('terlihat');
      }
    }, { passive: true });

    tombolKeAtas.addEventListener('click', () => {
      window.scrollTo({
        top: 0,
        behavior: 'smooth'
      });
    });
  }
});
