/**
 * JS Modular untuk Formulir Standar BNSP
 * Menangani fungsi cetak, notifikasi simpan, dan tanda tangan digital
 */

document.addEventListener('DOMContentLoaded', function () {
    // Mode Read-Only Otomatis untuk Asesi
    const readOnlyContainers = document.querySelectorAll('.mode-read-only-asesi, [data-readonly="true"]');
    readOnlyContainers.forEach(function (container) {
        const formElements = container.querySelectorAll('input, select, textarea, button[type="submit"]');
        formElements.forEach(function (el) {
            // Jangan sembunyikan atau disable tombol tanda tangan & verifikasi asesi
            if (el.closest('form[action*="simpan-ttd-asesi"]') || el.classList.contains('btn-ttd-asesi')) {
                return;
            }

            if (el.tagName === 'BUTTON' || el.type === 'submit') {
                if (!el.classList.contains('btn-cetak-formulir') && !el.getAttribute('onclick')?.includes('print')) {
                    el.style.display = 'none';
                }
            } else if (el.type === 'checkbox' || el.type === 'radio') {
                el.disabled = true;
                el.style.pointerEvents = 'none';
                el.style.cursor = 'default';
            } else {
                el.readOnly = true;
                el.style.pointerEvents = 'none';
                el.style.cursor = 'default';
            }
        });
    });

    // Handler cetak form
    const btnCetak = document.querySelectorAll('.btn-cetak-formulir, [onclick*="window.print"]');
    btnCetak.forEach(function (btn) {
        btn.addEventListener('click', function () {
            window.print();
        });
    });

    // Auto-expand textarea
    const textareas = document.querySelectorAll('textarea.input-inline-bnsp');
    textareas.forEach(function (textarea) {
        textarea.addEventListener('input', function () {
            this.style.height = 'auto';
            this.style.height = (this.scrollHeight) + 'px';
        });
    });
});

/**
 * Helper notifikasi simpan
 */
function simpanNotifikasiFormulir(kodeForm) {
    if (typeof Swal !== 'undefined') {
        Swal.fire({
            icon: 'success',
            title: 'Berhasil Disimpan!',
            text: 'Data ' + kodeForm + ' berhasil disimpan ke sistem.',
            timer: 2000,
            showConfirmButton: false
        });
    } else {
        alert('Data ' + kodeForm + ' berhasil disimpan ke sistem.');
    }
}

/**
 * Filter Tipe Formulir pada Halaman Galeri
 */
function filterTipeFormulir(kode) {
    if (!kode) kode = 'semua';

    // Simpan status filter yang dipilih agar tetap aktif saat kembali
    try {
        if (kode === 'semua') {
            localStorage.removeItem('active_filter_tipe_form');
        } else {
            localStorage.setItem('active_filter_tipe_form', kode);
        }
    } catch(e) {}

    const cards = document.querySelectorAll('[data-kode-form]');
    const categoryHeaders = document.querySelectorAll('.kategori-header');
    const resetBanners = document.querySelectorAll('#filter-active-banner, #filter-active-banner-asesi');
    const labelFilters = document.querySelectorAll('#filter-selected-name, #filter-selected-name-asesi');
    const selectElems = document.querySelectorAll('#filter-tipe-form, #filter-tipe-form-asesi');

    if (kode === 'semua') {
        cards.forEach(function (c) {
            c.style.display = '';
        });
        categoryHeaders.forEach(function (h) {
            h.style.display = '';
        });
        resetBanners.forEach(function (b) {
            b.style.display = 'none';
        });
        selectElems.forEach(function (s) {
            s.value = 'semua';
        });
        return;
    }

    let selectedLabel = kode;
    selectElems.forEach(function (selectElem) {
        if (selectElem) {
            selectElem.value = kode;
            const opt = selectElem.options[selectElem.selectedIndex];
            if (opt) selectedLabel = opt.text;
        }
    });

    const cleanKode = kode.toUpperCase().replace(/\s+/g, '');

    cards.forEach(function (c) {
        if (cleanKode === 'FR.IA.AKTIF') {
            const cardKodes = (c.getAttribute('data-kode-form') || '').toUpperCase();
            const isActive = c.getAttribute('data-is-active') === 'true';
            if (cardKodes.includes('FR.IA') && isActive) {
                c.style.display = '';
            } else {
                c.style.display = 'none';
            }
        } else {
            const cardKodes = (c.getAttribute('data-kode-form') || '').toUpperCase().split(' ');
            const isMatch = cardKodes.some(function (k) {
                return k === cleanKode || k.startsWith(cleanKode) || cleanKode.startsWith(k);
            });

            if (isMatch) {
                c.style.display = '';
            } else {
                c.style.display = 'none';
            }
        }
    });

    categoryHeaders.forEach(function (h) {
        let sibling = h.nextElementSibling;
        let hasVisible = false;
        while (sibling && !sibling.classList.contains('kategori-header')) {
            if (sibling.classList.contains('grid-formulir')) {
                const visibleInGrid = Array.from(sibling.querySelectorAll('[data-kode-form]')).filter(function (el) {
                    return el.style.display !== 'none';
                });
                if (visibleInGrid.length > 0) hasVisible = true;
            }
            sibling = sibling.nextElementSibling;
        }
        h.style.display = hasVisible ? '' : 'none';
    });

    resetBanners.forEach(function (b) {
        b.style.display = 'flex';
    });
    labelFilters.forEach(function (l) {
        l.innerText = selectedLabel;
    });
}

function resetFilterFormulir() {
    try {
        localStorage.removeItem('active_filter_tipe_form');
    } catch(e) {}
    filterTipeFormulir('semua');
}

// Inisialisasi filter dari query URL atau localStorage saat halaman dimuat (hanya jika elemen filter ada)
document.addEventListener('DOMContentLoaded', function () {
    const filterSelect = document.getElementById('filter-tipe-form');
    if (!filterSelect) return;

    const urlParams = new URLSearchParams(window.location.search);
    let tipeForm = urlParams.get('tipe_form');

    if (!tipeForm) {
        try {
            tipeForm = localStorage.getItem('active_filter_tipe_form');
        } catch(e) {}
    }

    if (tipeForm && tipeForm !== 'semua') {
        filterTipeFormulir(tipeForm);
    }
});

/**
 * Dialog Konfirmasi Pop-up JS Sebelum Memulai Ujian (Asesi)
 */
function konfirmasiMulaiUjian(event, url, judulUjian, infoTambahan) {
    if (event) event.preventDefault();

    const titleText = judulUjian || 'Ujian Kompetensi';
    const subInfo = infoTambahan ? ('<div style="background:#eff6ff; border:1px solid #bfdbfe; border-radius:6px; padding:0.35rem 0.6rem; margin-bottom:0.45rem; color:#1e40af; font-weight:700; font-size:0.8rem;"><i class="fa-solid fa-circle-info mr-1"></i> ' + infoTambahan + '</div>') : '';

    if (typeof Swal !== 'undefined') {
        Swal.fire({
            icon: 'question',
            title: 'Mulai ' + titleText + '?',
            html: '<div style="text-align:left; font-size:0.82rem; color:#334155; line-height:1.45;">' +
                  subInfo +
                  '<div style="background:#fef2f2; border:1px solid #fecaca; border-radius:6px; padding:0.45rem 0.65rem; color:#991b1b; font-size:0.78rem;">' +
                  '<strong>Ketentuan:</strong> Pengiriman hanya 1x, seluruh butir soal wajib dijawab, dan timer mulai berjalan saat ujian dibuka.' +
                  '</div>' +
                  '</div>',
            showCancelButton: true,
            confirmButtonColor: '#16a34a',
            cancelButtonColor: '#64748b',
            confirmButtonText: '<i class="fa-solid fa-play mr-1"></i> Mulai Ujian',
            cancelButtonText: 'Batal',
            reverseButtons: true
        }).then((result) => {
            if (result.isConfirmed) {
                window.location.href = url;
            }
        });
    } else {
        const confirmMsg = 'Mulai ' + titleText + ' sekarang?\n\nWaktu pengerjaan akan langsung berjalan dan hanya bisa dikirim 1 kali.\nApakah Anda siap?';
        if (confirm(confirmMsg)) {
            window.location.href = url;
        }
    }
    return false;
}
