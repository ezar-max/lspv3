@once
<!-- Signature Pad script -->
<script src="https://cdn.jsdelivr.net/npm/signature_pad@4.1.7/dist/signature_pad.umd.min.js"></script>
<script>
    // --- SIGNATURE PAD LOGIC ---
    let signaturePadPenyusun = null;

    window.openSignaturePadPenyusun = function() {
        const modal = document.getElementById('modal-signature-penyusun');
        if (modal) {
            modal.classList.remove('hidden');
            modal.classList.add('flex');
            
            // Initialize if not already initialized
            if (!signaturePadPenyusun) {
                const canvas = document.getElementById('canvas-signature-penyusun');
                if (canvas && window.SignaturePad) {
                    signaturePadPenyusun = new SignaturePad(canvas, {
                        backgroundColor: 'rgb(255, 255, 255)'
                    });
                    
                    // Resize canvas
                    const ratio =  Math.max(window.devicePixelRatio || 1, 1);
                    canvas.width = canvas.offsetWidth * ratio;
                    canvas.height = canvas.offsetHeight * ratio;
                    canvas.getContext("2d").scale(ratio, ratio);
                    signaturePadPenyusun.clear();
                }
            }
        }
    };

    window.closeSignaturePadPenyusun = function() {
        const modal = document.getElementById('modal-signature-penyusun');
        if (modal) {
            modal.classList.add('hidden');
            modal.classList.remove('flex');
        }
    };

    window.clearSignaturePenyusun = function() {
        if (signaturePadPenyusun) {
            signaturePadPenyusun.clear();
        }
    };

    window.saveSignaturePenyusun = function() {
        if (signaturePadPenyusun && !signaturePadPenyusun.isEmpty()) {
            const dataUrl = signaturePadPenyusun.toDataURL('image/svg+xml');
            
            // Set hidden input values in ALL forms
            const hiddenInputs = document.querySelectorAll('.hidden-penyusun-ttd');
            hiddenInputs.forEach(input => {
                input.value = dataUrl;
            });

            // Set preview images
            const previewImgs = document.querySelectorAll('.preview-penyusun-ttd');
            previewImgs.forEach(img => {
                img.src = dataUrl;
                img.classList.remove('hidden');
            });
            
            // Set parent form to submit if inside master form
            const button = document.querySelector('button[onclick="openSignaturePadPenyusun()"]');
            if (button) {
                button.classList.add('hidden');
            }

            closeSignaturePadPenyusun();
        } else {
            alert('Silakan gambar tanda tangan Anda terlebih dahulu.');
        }
    };
</script>

<!-- Modal Signature Pad -->
<div id="modal-signature-penyusun" class="fixed inset-0 z-[100] hidden items-center justify-center bg-slate-900/60 backdrop-blur-sm">
    <div class="bg-white rounded-2xl shadow-xl w-full max-w-lg mx-4 overflow-hidden border border-slate-200">
        <div class="px-5 py-4 border-b border-slate-100 flex items-center justify-between bg-slate-50/50">
            <h3 class="font-bold text-slate-800 flex items-center gap-2">
                <i class="fa-solid fa-signature text-blue-600"></i>
                Gambar Tanda Tangan
            </h3>
            <button type="button" onclick="closeSignaturePadPenyusun()" class="text-slate-400 hover:text-slate-600 p-1 transition-colors">
                <i class="fa-solid fa-xmark text-lg"></i>
            </button>
        </div>
        <div class="p-5 text-center">
            <p class="text-xs text-slate-500 mb-3 text-left">Gunakan kursor atau sentuhan untuk menggambar tanda tangan Anda. Tanda tangan ini akan disimpan di konfigurasi formulir master ini.</p>
            
            <div class="border-2 border-dashed border-slate-300 rounded-xl bg-slate-50/50 overflow-hidden mb-4 relative" style="height: 220px; width: 100%;">
                <canvas id="canvas-signature-penyusun" class="w-full h-full cursor-crosshair"></canvas>
            </div>
            
            <div class="flex items-center justify-between gap-3">
                <button type="button" onclick="clearSignaturePenyusun()" class="px-4 py-2 bg-slate-100 hover:bg-slate-200 text-slate-700 font-semibold rounded-lg text-sm transition shadow-sm border border-slate-200">
                    <i class="fa-solid fa-eraser mr-1"></i> Bersihkan
                </button>
                <div class="flex gap-2">
                    <button type="button" onclick="closeSignaturePadPenyusun()" class="px-4 py-2 bg-white hover:bg-slate-50 text-slate-600 font-medium rounded-lg text-sm transition border border-slate-200">
                        Batal
                    </button>
                    <button type="button" onclick="saveSignaturePenyusun()" class="px-4 py-2 bg-blue-600 hover:bg-blue-700 text-white font-semibold rounded-lg text-sm transition shadow-sm shadow-blue-200">
                        Simpan & Terapkan
                    </button>
                </div>
            </div>
        </div>
    </div>
</div>
@endonce
