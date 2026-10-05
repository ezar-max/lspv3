(() => {
    const dialog = document.getElementById('ia01-signature-dialog');
    const canvas = document.getElementById('ia01-signature-canvas');
    const form = document.getElementById('ia01-signature-form');

    if (!dialog || !canvas || !form) {
        return;
    }

    const context = canvas.getContext('2d', { alpha: true });
    const signatureInput = document.getElementById('ia01-signature-value');
    const title = document.getElementById('ia01-signature-title');
    const error = document.getElementById('ia01-signature-error');
    let drawing = false;
    let hasInk = false;

    const setCanvasSize = () => {
        const bounds = canvas.getBoundingClientRect();
        const scale = Math.min(window.devicePixelRatio || 1, 2);
        canvas.width = Math.max(1, Math.round(bounds.width * scale));
        canvas.height = Math.max(1, Math.round(bounds.height * scale));
        context.setTransform(scale, 0, 0, scale, 0, 0);
        context.lineWidth = 2.4;
        context.lineCap = 'round';
        context.lineJoin = 'round';
        context.strokeStyle = '#111827';
    };

    const pointFromEvent = (event) => {
        const bounds = canvas.getBoundingClientRect();
        return { x: event.clientX - bounds.left, y: event.clientY - bounds.top };
    };

    document.querySelectorAll('.ia01-open-signature').forEach((button) => {
        button.addEventListener('click', () => {
            title.textContent = button.dataset.title || 'Tanda Tangan';
            form.action = button.dataset.endpoint;
            signatureInput.value = '';
            error.hidden = true;
            error.textContent = '';
            hasInk = false;
            dialog.showModal();
            requestAnimationFrame(() => {
                setCanvasSize();
                context.clearRect(0, 0, canvas.width, canvas.height);
            });
        });
    });

    canvas.addEventListener('pointerdown', (event) => {
        event.preventDefault();
        drawing = true;
        hasInk = true;
        canvas.setPointerCapture(event.pointerId);
        const point = pointFromEvent(event);
        context.beginPath();
        context.arc(point.x, point.y, 1.2, 0, Math.PI * 2);
        context.fillStyle = '#111827';
        context.fill();
        context.beginPath();
        context.moveTo(point.x, point.y);
    });

    canvas.addEventListener('pointermove', (event) => {
        if (!drawing) {
            return;
        }

        event.preventDefault();
        const point = pointFromEvent(event);
        context.lineWidth = Math.max(1.2, 2.4 * (event.pressure || 0.5));
        context.lineTo(point.x, point.y);
        context.stroke();
        context.beginPath();
        context.moveTo(point.x, point.y);
    });

    const stopDrawing = () => {
        drawing = false;
        context.beginPath();
    };

    canvas.addEventListener('pointerup', stopDrawing);
    canvas.addEventListener('pointercancel', stopDrawing);
    canvas.addEventListener('lostpointercapture', stopDrawing);

    document.getElementById('ia01-signature-clear').addEventListener('click', () => {
        context.clearRect(0, 0, canvas.width, canvas.height);
        hasInk = false;
        signatureInput.value = '';
        error.hidden = true;
    });

    document.getElementById('ia01-signature-cancel').addEventListener('click', () => {
        dialog.close();
    });

    form.addEventListener('submit', (event) => {
        if (!hasInk) {
            event.preventDefault();
            error.textContent = 'Bubuhkan tanda tangan sebelum menyimpan.';
            error.hidden = false;
            return;
        }

        signatureInput.value = canvas.toDataURL('image/png');
    });
})();
