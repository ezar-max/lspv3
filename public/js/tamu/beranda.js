/**
 * BERANDA.JS - LSP SMKN 1 GUNUNGPUTRI (LANDING TAMU)
 * Logika Filter Skema, Modal Rincian Unit, Menu Mobile, & Interaktivitas
 */

document.addEventListener('DOMContentLoaded', () => {
  // 1. Mobile Menu Toggle
  const tombolMenuHp = document.getElementById('tombolMenuHp');
  const menuNavigasi = document.getElementById('menuNavigasi');

  if (tombolMenuHp && menuNavigasi) {
    tombolMenuHp.addEventListener('click', (e) => {
      e.stopPropagation();
      menuNavigasi.classList.toggle('buka');
      const isOpen = menuNavigasi.classList.contains('buka');
      tombolMenuHp.setAttribute('aria-expanded', isOpen);
    });

    document.addEventListener('click', (e) => {
      if (!menuNavigasi.contains(e.target) && e.target !== tombolMenuHp) {
        menuNavigasi.classList.remove('buka');
      }
    });

    menuNavigasi.querySelectorAll('.nav-link').forEach(link => {
      link.addEventListener('click', () => {
        menuNavigasi.classList.remove('buka');
      });
    });
  }

  // 2. Sticky Navbar Effect on Scroll
  const navbar = document.querySelector('.navbar-publik');
  if (navbar) {
    window.addEventListener('scroll', () => {
      if (window.scrollY > 20) {
        navbar.classList.add('scrolled');
      } else {
        navbar.classList.remove('scrolled');
      }
    });
  }

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

  // 4. Modal Rincian Unit Skema
  const modalUnit = document.getElementById('modalUnitSkema');
  const tutupModal = document.getElementById('tutupModalUnit');
  const tombolBatal = document.getElementById('tombolBatalModal');
  const judulModal = document.getElementById('judulModalUnit');
  const wadahTabel = document.getElementById('wadahTabelUnit');
  const inputCari = document.getElementById('inputCariUnit');

  let unitsDataCache = [];

  function bukaModal() {
    if (modalUnit) {
      modalUnit.classList.add('aktif');
      document.body.style.overflow = 'hidden';
    }
  }

  function tutupModalHandler() {
    if (modalUnit) {
      modalUnit.classList.remove('aktif');
      document.body.style.overflow = '';
      if (inputCari) inputCari.value = '';
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

    document.addEventListener('keydown', (e) => {
      if (e.key === 'Escape' && modalUnit.classList.contains('aktif')) {
        tutupModalHandler();
      }
    });
  }

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
            <th style="width: 50px; text-align: center;">No</th>
            <th style="width: 170px;">Kode Unit</th>
            <th>Judul Unit Kompetensi</th>
            <th style="width: 100px; text-align: center;">Standar</th>
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
});
