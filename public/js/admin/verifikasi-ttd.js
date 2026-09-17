/* VERIFIKASI-TTD.JS - Multi-Stroke Smooth Signature Canvas Engine for Admin LSP */

document.addEventListener('DOMContentLoaded', () => {
  const canvas = document.getElementById('canvas-ttd-admin');
  if (!canvas) return;

  const ctx = canvas.getContext('2d');
  let isDrawing = false;
  let allStrokes = [];
  let currentStroke = [];

  function setupCanvasContext() {
    ctx.strokeStyle = '#0284c7';
    ctx.fillStyle = '#0284c7';
    ctx.lineWidth = 2.8;
    ctx.lineCap = 'round';
    ctx.lineJoin = 'round';
    ctx.imageSmoothingEnabled = true;
  }

  setupCanvasContext();

  function getPos(e) {
    const rect = canvas.getBoundingClientRect();
    let clientX = e.clientX;
    let clientY = e.clientY;
    if (e.touches && e.touches.length > 0) {
      clientX = e.touches[0].clientX;
      clientY = e.touches[0].clientY;
    }
    return {
      x: clientX - rect.left,
      y: clientY - rect.top
    };
  }

  function renderStroke(pts) {
    if (!pts || pts.length === 0) return;

    if (pts.length < 3) {
      const b = pts[0];
      ctx.beginPath();
      ctx.arc(b.x, b.y, ctx.lineWidth / 2, 0, Math.PI * 2, true);
      ctx.fill();
      ctx.closePath();
      return;
    }

    ctx.beginPath();
    ctx.moveTo(pts[0].x, pts[0].y);

    for (let i = 1; i < pts.length - 2; i++) {
      const xc = (pts[i].x + pts[i + 1].x) / 2;
      const yc = (pts[i].y + pts[i + 1].y) / 2;
      ctx.quadraticCurveTo(pts[i].x, pts[i].y, xc, yc);
    }

    ctx.quadraticCurveTo(
      pts[pts.length - 2].x,
      pts[pts.length - 2].y,
      pts[pts.length - 1].x,
      pts[pts.length - 1].y
    );

    ctx.stroke();
  }

  function redrawAll() {
    setupCanvasContext();
    ctx.clearRect(0, 0, canvas.width, canvas.height);
    for (let i = 0; i < allStrokes.length; i++) {
      renderStroke(allStrokes[i]);
    }
    if (currentStroke.length > 0) {
      renderStroke(currentStroke);
    }
  }

  function startDrawing(e) {
    isDrawing = true;
    const pos = getPos(e);
    currentStroke = [pos];
    redrawAll();
    e.preventDefault();
  }

  function draw(e) {
    if (!isDrawing) return;
    const pos = getPos(e);
    currentStroke.push(pos);
    redrawAll();
    e.preventDefault();
  }

  function stopDrawing(e) {
    if (!isDrawing) return;
    isDrawing = false;
    if (currentStroke.length > 0) {
      allStrokes.push(currentStroke);
      currentStroke = [];
    }
    redrawAll();
  }

  canvas.addEventListener('mousedown', startDrawing);
  canvas.addEventListener('mousemove', draw);
  canvas.addEventListener('mouseup', stopDrawing);
  canvas.addEventListener('mouseleave', stopDrawing);

  canvas.addEventListener('touchstart', startDrawing, { passive: false });
  canvas.addEventListener('touchmove', draw, { passive: false });
  canvas.addEventListener('touchend', stopDrawing);

  const btnClear = document.getElementById('btn-clear-canvas-admin');
  if (btnClear) {
    btnClear.addEventListener('click', () => {
      allStrokes = [];
      currentStroke = [];
      isDrawing = false;
      ctx.clearRect(0, 0, canvas.width, canvas.height);
    });
  }

  const btnSimpanTTD = document.getElementById('btn-simpan-canvas-admin');
  if (btnSimpanTTD) {
    btnSimpanTTD.addEventListener('click', () => {
      const inputHidden = document.getElementById('input-ttd-admin-base64');
      const hasContent = allStrokes.length > 0 || currentStroke.length > 0;

      if (!hasContent && (!inputHidden || !inputHidden.value)) {
        alert('Silakan buat tanda tangan terlebih dahulu pada canvas.');
        return;
      }

      if (hasContent) {
        const dataURL = canvas.toDataURL('image/png');
        const imgPreview = document.getElementById('preview-ttd-admin-img');
        const boxPreview = document.getElementById('box-preview-ttd-admin');
        const pesanKosong = document.getElementById('pesan-ttd-kosong');
        const btnModal = document.getElementById('btn-modal-ttd-admin');

        if (inputHidden) inputHidden.value = dataURL;
        if (imgPreview) {
          imgPreview.src = dataURL;
          imgPreview.style.display = 'inline-block';
        }
        if (boxPreview) boxPreview.style.display = 'block';
        if (pesanKosong) pesanKosong.style.display = 'none';
        if (btnModal) {
          btnModal.innerHTML = '<i class="fa-solid fa-pen-nib"></i> Ganti TTD Admin (Canvas)';
        }
      }

      tutupModal('modalCanvasTtdAdmin');
    });
  }

  // DYNAMIC LOCKING AND SYNCHRONIZATION FOR VERIFICATION FORM
  const formVerifikasi = document.getElementById('form-verifikasi-admin');
  const selectStatus = document.getElementById('select-status-pendaftaran');
  const btnModalTtd = document.getElementById('btn-modal-ttd-admin');
  const radiosRekomendasi = document.querySelectorAll('.input-rekomendasi-admin');
  const selectJadwal = document.getElementById('select-jadwal-id');
  const containerJadwal = document.getElementById('container-jadwal-admin');
  const alertDraftInfo = document.getElementById('alert-status-draft-info');
  const alertDitolakInfo = document.getElementById('alert-status-ditolak-info');

  function updateStatusFormState(source) {
    if (!selectStatus) return;

    const statusVal = selectStatus.value;
    const isDraft = ['draft', 'revisi'].includes(statusVal);
    const isDitolak = statusVal === 'ditolak';
    const isDiverifikasi = statusVal === 'diverifikasi';

    // Reset alert visibility
    if (alertDraftInfo) alertDraftInfo.style.display = 'none';
    if (alertDitolakInfo) alertDitolakInfo.style.display = 'none';

    if (isDraft) {
      // Disable TTD Admin button
      if (btnModalTtd) {
        btnModalTtd.disabled = true;
        btnModalTtd.style.opacity = '0.5';
        btnModalTtd.style.cursor = 'not-allowed';
      }

      // Disable & uncheck Rekomendasi
      radiosRekomendasi.forEach(radio => {
        radio.checked = false;
        radio.disabled = true;
      });

      // Disable & reset Jadwal Select
      if (selectJadwal) {
        selectJadwal.value = '';
        selectJadwal.disabled = true;
      }
      if (containerJadwal) containerJadwal.style.opacity = '0.4';

      // Show draft/revisi alert
      if (alertDraftInfo) alertDraftInfo.style.display = 'block';
    } else if (isDitolak) {
      // Enable TTD Admin button (Admin can still stamp/sign rejection)
      if (btnModalTtd) {
        btnModalTtd.disabled = false;
        btnModalTtd.style.opacity = '1';
        btnModalTtd.style.cursor = 'pointer';
      }

      // Sync radio to 'tidak_diterima'
      radiosRekomendasi.forEach(radio => {
        radio.disabled = false;
        if (radio.value === 'tidak_diterima') {
          radio.checked = true;
        }
      });

      // Disable & reset Jadwal Select because applicant is rejected
      if (selectJadwal) {
        selectJadwal.value = '';
        selectJadwal.disabled = true;
      }
      if (containerJadwal) containerJadwal.style.opacity = '0.4';

      // Show rejection alert
      if (alertDitolakInfo) alertDitolakInfo.style.display = 'block';
    } else if (isDiverifikasi) {
      // Enable TTD Admin button
      if (btnModalTtd) {
        btnModalTtd.disabled = false;
        btnModalTtd.style.opacity = '1';
        btnModalTtd.style.cursor = 'pointer';
      }

      // Sync radio to 'diterima'
      radiosRekomendasi.forEach(radio => {
        radio.disabled = false;
        if (radio.value === 'diterima') {
          radio.checked = true;
        }
      });

      // Enable Jadwal Select
      if (selectJadwal) {
        selectJadwal.disabled = false;
      }
      if (containerJadwal) containerJadwal.style.opacity = '1';
    }
  }

  // Radio button click handler
  radiosRekomendasi.forEach(radio => {
    radio.addEventListener('change', () => {
      if (!selectStatus) return;
      if (radio.value === 'tidak_diterima') {
        selectStatus.value = 'ditolak';
      } else if (radio.value === 'diterima') {
        selectStatus.value = 'diverifikasi';
      }
      updateStatusFormState('radio');
    });
  });

  if (selectStatus) {
    selectStatus.addEventListener('change', () => updateStatusFormState('select'));
    updateStatusFormState('init');
  }

  // FORM SUBMISSION CLIENT-SIDE VALIDATION
  if (formVerifikasi) {
    formVerifikasi.addEventListener('submit', (e) => {
      const statusVal = selectStatus ? selectStatus.value : 'diverifikasi';
      const isAcc = (statusVal === 'diverifikasi');

      if (isAcc) {
        // 1. Validasi Tanda Tangan Admin
        const inputHiddenTtd = document.getElementById('input-ttd-admin-base64');
        const ttdVal = inputHiddenTtd ? inputHiddenTtd.value.trim() : '';

        if (!ttdVal) {
          e.preventDefault();
          alert('PERINGATAN: Tanda Tangan Admin LSP wajib dibubuhkan/digambar sebelum menyetujui (ACC) permohonan!');
          const containerTtd = document.getElementById('container-box-ttd-admin');
          if (containerTtd) {
            containerTtd.scrollIntoView({ behavior: 'smooth', block: 'center' });
            containerTtd.style.outline = '2px solid var(--merah-bahaya)';
            setTimeout(() => { containerTtd.style.outline = 'none'; }, 3000);
          }
          if (typeof bukaModal === 'function') {
            bukaModal('modalCanvasTtdAdmin');
          }
          return false;
        }

        // 2. Validasi Penugasan Jadwal & Asesor Penguji
        const jadwalVal = selectJadwal ? selectJadwal.value.trim() : '';
        if (!jadwalVal) {
          e.preventDefault();
          alert('PERINGATAN: Penugasan Jadwal Uji & Asesor Penguji wajib dipilih sebelum menyetujui (ACC) permohonan!');
          if (selectJadwal) {
            selectJadwal.focus();
            selectJadwal.scrollIntoView({ behavior: 'smooth', block: 'center' });
            selectJadwal.style.outline = '2px solid var(--merah-bahaya)';
            setTimeout(() => { selectJadwal.style.outline = 'none'; }, 3000);
          }
          return false;
        }

        // 3. Validasi Kelayakan Dokumen Persyaratan
        const docSelects = document.querySelectorAll('.select-status-dokumen');
        let adaMenunggu = null;
        let adaTidakValid = null;

        docSelects.forEach(sel => {
          if (sel.value === 'menunggu' && !adaMenunggu) adaMenunggu = sel;
          if (sel.value === 'tidak_valid' && !adaTidakValid) adaTidakValid = sel;
        });

        if (adaMenunggu) {
          e.preventDefault();
          alert('PERINGATAN: Masih terdapat dokumen persyaratan yang berstatus "Menunggu". Harap tentukan status kelayakannya (ubah menjadi Valid) sebelum menyetujui (ACC) permohonan.');
          adaMenunggu.focus();
          adaMenunggu.scrollIntoView({ behavior: 'smooth', block: 'center' });
          adaMenunggu.style.outline = '2px solid var(--amber-peringatan)';
          setTimeout(() => { adaMenunggu.style.outline = 'none'; }, 3000);
          return false;
        }

        if (adaTidakValid) {
          e.preventDefault();
          alert('PERINGATAN: Terdapat dokumen yang berstatus "Tidak Valid". Anda tidak dapat menyetujui (ACC) jika berkas tidak valid. Silakan ubah status pendaftaran menjadi REVISI atau DITOLAK.');
          adaTidakValid.focus();
          adaTidakValid.scrollIntoView({ behavior: 'smooth', block: 'center' });
          adaTidakValid.style.outline = '2px solid var(--merah-bahaya)';
          setTimeout(() => { adaTidakValid.style.outline = 'none'; }, 3000);
          return false;
        }
      }

      // Catatan verifikasi tidak dicek (opsional)
      return true;
    });
  }
});

