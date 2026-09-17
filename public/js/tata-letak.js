/* TATA-LETAK.JS - Global Interactive UI Functions & Design System Scripts */

// FUNGSI GLOBAL TUTUP TOAST (Smooth exit & remove from DOM)
function tutupToast(toastEl) {
  if (!toastEl || toastEl.classList.contains('toast-hiding')) return;
  toastEl.classList.add('toast-hiding');
  setTimeout(() => {
    if (toastEl && toastEl.parentNode) {
      toastEl.remove();
    }
  }, 400);
}

// INSIALISASI FLOATING TOAST NOTIFIKASI DENGAN AUTO-DISMISS & HOVER PAUSE
function initToastNotifications() {
  const toasts = document.querySelectorAll('.toast-notif');
  toasts.forEach(toast => {
    let timeoutId = null;
    const duration = 4500; // 4.5 detik
    let startTime = Date.now();
    let remaining = duration;

    function startTimer() {
      startTime = Date.now();
      timeoutId = setTimeout(() => {
        tutupToast(toast);
      }, remaining);
    }

    function pauseTimer() {
      clearTimeout(timeoutId);
      remaining -= (Date.now() - startTime);
    }

    // Hover pause pada desktop
    toast.addEventListener('mouseenter', pauseTimer);
    toast.addEventListener('mouseleave', () => {
      if (remaining > 0) startTimer();
    });

    // Touch pause pada HP
    toast.addEventListener('touchstart', pauseTimer, { passive: true });
    toast.addEventListener('touchend', () => {
      if (remaining > 0) startTimer();
    }, { passive: true });

    startTimer();
  });

  // Backward compatibility untuk alert-notif lama
  const alertList = document.querySelectorAll('.alert-notif');
  alertList.forEach(alert => {
    setTimeout(() => {
      alert.style.opacity = '0';
      alert.style.transition = 'opacity 0.5s ease';
      setTimeout(() => alert.remove(), 500);
    }, 4500);
  });
}

// Global programmatic alert helper via SweetAlert2 (Format Gambar 2)
window.tampilkanToast = function(tipe, pesan, judul = null) {
  if (typeof Swal !== 'undefined') {
    const swalIcon = tipe === 'sukses' ? 'success' : (tipe === 'error' ? 'error' : (tipe === 'warning' ? 'warning' : 'info'));
    const swalTitle = judul || (tipe === 'sukses' ? 'Berhasil!' : (tipe === 'error' ? 'Perhatian!' : (tipe === 'warning' ? 'Peringatan' : 'Informasi')));
    const btnColor = tipe === 'sukses' ? '#16a34a' : (tipe === 'error' ? '#ef4444' : (tipe === 'warning' ? '#f59e0b' : '#2563eb'));

    Swal.fire({
      icon: swalIcon,
      title: swalTitle,
      text: pesan,
      confirmButtonColor: btnColor,
      confirmButtonText: '✓ Tutup',
      customClass: {
        popup: 'swal2-modern-popup'
      }
    });
  } else {
    alert(pesan);
  }
};

document.addEventListener('DOMContentLoaded', () => {
  // 1. Inisialisasi notifikasi toast
  initToastNotifications();

  // 2. Scroll Progress Bar & Navbar Scrolled Class & Scroll To Top
  const scrollProgress = document.querySelector('.scroll-progress');
  const navbars = document.querySelectorAll('.navbar-publik, .navbar');
  const scrollTopBtn = document.querySelector('.scroll-top');

  window.addEventListener('scroll', () => {
    const winScroll = document.body.scrollTop || document.documentElement.scrollTop;
    const height = document.documentElement.scrollHeight - document.documentElement.clientHeight;

    // Scroll progress line
    if (scrollProgress && height > 0) {
      const scrolled = (winScroll / height) * 100;
      scrollProgress.style.width = scrolled + '%';
    }

    // Navbar scrolled shadow effect
    navbars.forEach(nav => {
      if (winScroll > 30) {
        nav.classList.add('scrolled');
      } else {
        nav.classList.remove('scrolled');
      }
    });

    // Scroll to top button visibility
    if (scrollTopBtn) {
      if (winScroll > 300) {
        scrollTopBtn.classList.add('show');
      } else {
        scrollTopBtn.classList.remove('show');
      }
    }
  });

  // Scroll to top click event
  if (scrollTopBtn) {
    scrollTopBtn.addEventListener('click', () => {
      window.scrollTo({ top: 0, behavior: 'smooth' });
    });
  }

  // 3. Reveal on Scroll Observer
  const observerOptions = {
    threshold: 0.1,
    rootMargin: '0px 0px -50px 0px'
  };

  const observer = new IntersectionObserver((entries) => {
    entries.forEach(entry => {
      if (entry.isIntersecting) {
        entry.target.classList.add('is-visible');
      }
    });
  }, observerOptions);

  document.querySelectorAll('.reveal-up, .reveal-card').forEach(el => {
    observer.observe(el);
  });
});

// Modal Dialog Helpers
function sinkronkanStatusBodyModal() {
  const adaModalTerbuka = document.querySelector('.modal-overlay.terbuka');
  if (adaModalTerbuka) {
    document.body.classList.add('modal-terbuka');
    document.body.style.overflow = 'hidden';
  } else {
    document.body.classList.remove('modal-terbuka');
    document.body.style.overflow = '';
  }
}

function bukaModal(modalId) {
  const modal = document.getElementById(modalId);
  if (modal) {
    modal.classList.add('terbuka');
    sinkronkanStatusBodyModal();
  }
}

function tutupModal(modalId) {
  const modal = document.getElementById(modalId);
  if (modal) {
    modal.classList.remove('terbuka');
    sinkronkanStatusBodyModal();
  }
}

// Global Image Lightbox Preview Popup
function lihatPratinjauGambar(url, judul = 'Detail Pratinjau Berkas Gambar') {
  if (!url) return;
  const img = document.getElementById('imgPratinjauGambar');
  const title = document.getElementById('judulPratinjauGambar');
  const btnDownload = document.getElementById('btnDownloadGambar');

  if (img) {
    img.src = url;
    img.onerror = function() {
      this.onerror = null;
      console.warn('Gagal memuat gambar pratinjau:', url);
    };
  }
  if (title) title.innerHTML = '<i class="fa-solid fa-image" style="color: var(--biru-utama);"></i> ' + judul;
  if (btnDownload) {
    btnDownload.href = url;
    btnDownload.style.display = url.startsWith('data:') ? 'none' : 'inline-flex';
  }

  bukaModal('modalPratinjauGambar');
}

// Automatic Delegation for images & image preview links / buttons
document.addEventListener('click', (e) => {
  const trigger = e.target.closest('[data-pratinjau-gambar], img.bisa-di-zoom, img.preview-gambar-modal, a.preview-gambar-link, button.preview-gambar-link, .preview-gambar-link, .buka-modal-gambar');
  if (trigger) {
    const src = trigger.getAttribute('data-pratinjau-gambar') || trigger.getAttribute('src') || trigger.getAttribute('href');
    if (src && (src.match(/\.(jpeg|jpg|gif|png|webp|svg|jfif|bmp|avif)/i) || src.startsWith('data:image') || trigger.hasAttribute('data-pratinjau-gambar'))) {
      e.preventDefault();
      const judul = trigger.getAttribute('data-judul') || trigger.getAttribute('alt') || 'Detail Pratinjau Berkas Gambar';
      lihatPratinjauGambar(src, judul);
    }
  }
});

// Close modal when clicking backdrop outside modal content
document.addEventListener('click', (e) => {
  if (e.target.classList.contains('modal-overlay')) {
    e.target.classList.remove('terbuka');
    sinkronkanStatusBodyModal();
  }
});

// Close modal on Escape key press
document.addEventListener('keydown', (e) => {
  if (e.key === 'Escape') {
    document.querySelectorAll('.modal-overlay.terbuka').forEach(m => m.classList.remove('terbuka'));
    sinkronkanStatusBodyModal();
  }
});

// Mobile Sidebar Toggle
function toggleSidebar() {
  const sidebar = document.querySelector('.sidebar-dasbor');
  if (sidebar) {
    sidebar.classList.toggle('terbuka');
  }
}

// Toggle Visibility Password Input Field
function togglePasswordVisibility(targetInput, btnElement) {
  const input = typeof targetInput === 'string' ? document.getElementById(targetInput) : targetInput;
  if (!input) return;

  const btn = btnElement || (input.parentElement ? input.parentElement.querySelector('.tombol-intip-sandi') : null);
  const icon = btn ? btn.querySelector('i') : null;

  if (input.type === 'password') {
    input.type = 'text';
    if (icon) {
      if (icon.classList.contains('fa-eye')) {
        icon.classList.replace('fa-eye', 'fa-eye-slash');
      } else if (icon.classList.contains('fa-eye-slash')) {
        icon.classList.replace('fa-eye-slash', 'fa-eye');
      } else {
        icon.classList.add('fa-eye-slash');
      }
    }
  } else {
    input.type = 'password';
    if (icon) {
      if (icon.classList.contains('fa-eye-slash')) {
        icon.classList.replace('fa-eye-slash', 'fa-eye');
      } else if (icon.classList.contains('fa-eye')) {
        icon.classList.replace('fa-eye', 'fa-eye-slash');
      } else {
        icon.classList.add('fa-eye');
      }
    }
  }
}

// Global Event Delegation for Tombol Intip Sandi
document.addEventListener('click', (e) => {
  const toggleBtn = e.target.closest('.tombol-intip-sandi');
  if (toggleBtn) {
    e.preventDefault();
    const wrapper = toggleBtn.closest('.input-sandi-wrapper');
    if (wrapper) {
      const input = wrapper.querySelector('input');
      if (input) {
        togglePasswordVisibility(input, toggleBtn);
      }
    }
  }
});

