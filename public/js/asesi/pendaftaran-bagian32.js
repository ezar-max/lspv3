/* PENDAFTARAN-BAGIAN32.JS - Multi-Stroke Smooth Signature Canvas Engine */

document.addEventListener('DOMContentLoaded', () => {
  const canvas = document.getElementById('canvas-ttd-asesi');
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

  const btnClear = document.getElementById('btn-clear-canvas');
  if (btnClear) {
    btnClear.addEventListener('click', () => {
      allStrokes = [];
      currentStroke = [];
      isDrawing = false;
      ctx.clearRect(0, 0, canvas.width, canvas.height);
    });
  }

  const btnSimpanTTD = document.getElementById('btn-simpan-canvas');
  if (btnSimpanTTD) {
    btnSimpanTTD.addEventListener('click', () => {
      const inputHidden = document.getElementById('input-ttd-asesi-base64');
      const hasContent = allStrokes.length > 0 || currentStroke.length > 0;

      if (!hasContent && (!inputHidden || !inputHidden.value)) {
        alert('Silakan buat tanda tangan terlebih dahulu pada canvas.');
        return;
      }

      if (hasContent) {
        const dataURL = canvas.toDataURL('image/png');
        const imgPreview = document.getElementById('preview-ttd-asesi-img');
        const boxPreview = document.getElementById('box-preview-ttd-asesi');

        if (inputHidden) inputHidden.value = dataURL;
        if (imgPreview) imgPreview.src = dataURL;
        if (boxPreview) boxPreview.style.display = 'block';
      }

      tutupModal('modalCanvasTtd');
    });
  }
});
