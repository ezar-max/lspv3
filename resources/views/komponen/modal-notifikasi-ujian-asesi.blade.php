@if(auth()->check() && auth()->user()->peran === 'asesi' && !request()->routeIs('asesi.ruang-uji') && !request()->routeIs('asesi.ujian'))
<!-- =========================================================================
     MODAL NOTIFIKASI REAL-TIME: SESI UJIAN TELAH DIMULAI ASESOR
     ========================================================================= -->
<div id="modal-notifikasi-ujian-aktif" class="fixed inset-0 z-[99999] flex items-center justify-center p-4 bg-slate-900/60 backdrop-blur-xs transition-opacity duration-300" style="display: none;">
    <div class="bg-white rounded-2xl border border-slate-200/90 shadow-2xl max-w-lg w-full p-6 sm:p-7 transform transition-all duration-300 scale-95 opacity-0" id="box-notifikasi-ujian">
        
        <div class="flex items-center justify-between gap-3 mb-4">
            <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full bg-emerald-50 text-emerald-700 border border-emerald-200 text-xs font-bold uppercase tracking-wider">
                <span class="relative flex h-2 w-2">
                    <span class="animate-ping absolute inline-flex h-full w-full rounded-full bg-emerald-400 opacity-75"></span>
                    <span class="relative inline-flex rounded-full h-2 w-2 bg-emerald-500"></span>
                </span>
                Sesi Ujian Sedang Berlangsung
            </span>
            <button type="button" onclick="tutupNotifikasiUjian(60)" class="text-slate-400 hover:text-slate-600 text-xs font-semibold px-2 py-1 rounded hover:bg-slate-100 transition-colors">
                Tutup Sementara
            </button>
        </div>

        <div class="space-y-3 mb-6">
            <h3 class="text-xl font-bold text-slate-900 tracking-tight">
                Sesi Ujian Telah Dimulai!
            </h3>
            <p class="text-sm text-slate-600 leading-relaxed">
                Asesor Penguji telah membuka sesi ujian asesmen untuk skema sertifikasi Anda. Waktu pengerjaan saat ini sedang berjalan.
            </p>

            <div class="bg-slate-50 rounded-xl p-4 border border-slate-200/80 space-y-2 text-xs">
                <div class="flex justify-between items-center py-0.5 border-b border-slate-100">
                    <span class="text-slate-500 font-medium">Skema Sertifikasi:</span>
                    <strong class="text-slate-900 text-right" id="live-notif-skema-nama">-</strong>
                </div>
                <div class="flex justify-between items-center py-0.5 border-b border-slate-100">
                    <span class="text-slate-500 font-medium">Asesor Penguji:</span>
                    <strong class="text-slate-900 text-right" id="live-notif-asesor-nama">-</strong>
                </div>
                <div class="flex justify-between items-center py-0.5">
                    <span class="text-slate-500 font-medium">Tempat Uji (TUK):</span>
                    <strong class="text-slate-900 text-right" id="live-notif-tuk-nama">-</strong>
                </div>
            </div>

            <div class="bg-amber-50 border border-amber-200 rounded-xl p-3 text-xs text-amber-800 leading-relaxed">
                Silakan segera masuki Ruang Ujian untuk mengerjakan instrumen tertulis atau praktik sesuai arahan Asesor.
            </div>
        </div>

        <div class="flex flex-col sm:flex-row items-center justify-end gap-2.5 pt-2 border-t border-slate-100">
            <button type="button" onclick="tutupNotifikasiUjian(120)" class="w-full sm:w-auto px-4 py-2.5 rounded-xl border border-slate-200 text-slate-700 text-xs font-semibold hover:bg-slate-50 transition-colors">
                Nanti Dulu
            </button>
            <a href="#" id="live-notif-btn-masuk" class="w-full sm:w-auto px-5 py-2.5 rounded-xl bg-emerald-600 hover:bg-emerald-700 text-white text-xs font-bold text-center shadow-xs transition-colors">
                Masuk ke Ruang Ujian Sekarang &rarr;
            </a>
        </div>

    </div>
</div>

<script>
(function() {
    let checkInterval = null;
    let dismissedUntil = 0;
    let isExamActive = false;
    const checkEndpoint = '{{ route("asesi.ujian.status-live") }}';

    function playNotificationChime() {
        try {
            const AudioContext = window.AudioContext || window.webkitAudioContext;
            if (!AudioContext) return;
            const ctx = new AudioContext();
            
            const now = ctx.currentTime;
            const osc = ctx.createOscillator();
            const gain = ctx.createGain();

            osc.type = 'sine';
            // Nada 1
            osc.frequency.setValueAtTime(587.33, now); // D5
            // Nada 2
            osc.frequency.setValueAtTime(880.00, now + 0.15); // A5

            gain.gain.setValueAtTime(0, now);
            gain.gain.linearRampToValueAtTime(0.15, now + 0.05);
            gain.gain.linearRampToValueAtTime(0.01, now + 0.45);
            gain.gain.linearRampToValueAtTime(0, now + 0.5);

            osc.connect(gain);
            gain.connect(ctx.destination);

            osc.start(now);
            osc.stop(now + 0.5);
        } catch (e) {
            // Audio context failed or blocked by policy
        }
    }

    function showExamModal(data) {
        const modal = document.getElementById('modal-notifikasi-ujian-aktif');
        const box = document.getElementById('box-notifikasi-ujian');
        if (!modal || !box) return;

        document.getElementById('live-notif-skema-nama').textContent = data.skema_nama || 'Skema Sertifikasi';
        document.getElementById('live-notif-asesor-nama').textContent = data.asesor_nama || 'Asesor Penguji';
        document.getElementById('live-notif-tuk-nama').textContent = data.nama_tuk || 'TUK LSP';
        
        const btnMasuk = document.getElementById('live-notif-btn-masuk');
        if (btnMasuk) {
            btnMasuk.href = data.ruang_uji_url || '{{ route("asesi.ruang-uji") }}';
        }

        if (modal.style.display === 'none' || modal.style.display === '') {
            modal.style.display = 'flex';
            setTimeout(() => {
                box.classList.remove('scale-95', 'opacity-0');
                box.classList.add('scale-100', 'opacity-100');
            }, 20);
            playNotificationChime();
        }
    }

    window.tutupNotifikasiUjian = function(seconds) {
        dismissedUntil = Date.now() + (seconds * 1000);
        const modal = document.getElementById('modal-notifikasi-ujian-aktif');
        const box = document.getElementById('box-notifikasi-ujian');
        if (box) {
            box.classList.remove('scale-100', 'opacity-100');
            box.classList.add('scale-95', 'opacity-0');
        }
        setTimeout(() => {
            if (modal) modal.style.display = 'none';
        }, 250);
    };

    async function checkStatus() {
        if (Date.now() < dismissedUntil) {
            return;
        }

        try {
            const res = await fetch(checkEndpoint, {
                headers: {
                    'Accept': 'application/json',
                    'X-Requested-With': 'XMLHttpRequest'
                }
            });
            if (!res.ok) return;
            const data = await res.json();

            if (data.has_active_exam) {
                isExamActive = true;
                showExamModal(data);
            }
        } catch (e) {
            // Silent error on network/connection drop
        }
    }

    // Polling setiap 8 detik
    document.addEventListener('DOMContentLoaded', function() {
        setTimeout(checkStatus, 1500);
        checkInterval = setInterval(checkStatus, 8000);

        // Langsung cek saat tab aktif kembali
        document.addEventListener('visibilitychange', function() {
            if (!document.hidden) {
                checkStatus();
            }
        });
    });
})();
</script>
@endif
