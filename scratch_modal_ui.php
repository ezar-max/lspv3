<?php
$file = 'c:/laragon/www/lspv3/resources/views/asesor/input-penilaian.blade.php';
$content = file_get_contents($file);

// Replace modalMintaRevisi
$oldModalRevisiStart = '<div id="modalMintaRevisi"';
$oldModalRevisiEnd = '</div>
    </div>';

$newModalRevisi = '    <div id="modalMintaRevisi" class="fixed inset-0 items-center justify-center bg-slate-900/80 backdrop-blur-sm p-4 z-[99999] hidden" style="display: none; z-index: 99999;">
        <div class="bg-white rounded-2xl border border-slate-200 shadow-2xl max-w-lg w-full p-6 space-y-4 relative transform transition-all" @click.stop>
            <div class="flex items-center justify-between border-b border-slate-100 pb-3">
                <div class="flex items-center gap-3">
                    <div class="w-10 h-10 rounded-xl bg-amber-100 text-amber-600 flex items-center justify-center font-bold text-lg shrink-0 shadow-inner">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"></path></svg>
                    </div>
                    <div>
                        <h3 class="font-bold text-slate-900 text-base">Minta Revisi FR.APL.02</h3>
                        <p class="text-[11px] sm:text-xs text-slate-500">Kembalikan formulir ke asesi untuk diperbaiki</p>
                    </div>
                </div>
                <button type="button" @click="tutupModalMintaRevisi()" class="text-slate-400 hover:text-slate-700 bg-slate-50 hover:bg-slate-100 rounded-full w-8 h-8 flex items-center justify-center transition-colors cursor-pointer">&times;</button>
            </div>

            <div class="text-xs sm:text-sm text-amber-800 leading-relaxed bg-amber-50 p-3 rounded-xl border border-amber-200">
                Formulir FR.APL.02 akan dikembalikan ke Asesi dengan status <strong>Perlu Revisi</strong>. Asesi akan menerima catatan perbaikan dan dapat memperbaiki isian serta berkas bukti pendukung.
            </div>

            <div class="space-y-1.5">
                <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider">
                    Catatan Umum Revisi <span class="text-amber-500">*</span>
                </label>
                <textarea id="modal-catatan-revisi-input" 
                          rows="4" 
                          x-model="modalCatatanRevisi"
                          placeholder="Tuliskan bagian KUK atau bukti yang perlu diperbaiki..." 
                          class="w-full bg-slate-50 border border-slate-300 rounded-xl p-3 text-sm text-slate-800 focus:ring-2 focus:ring-amber-500 focus:border-amber-500 outline-none leading-relaxed transition-all shadow-sm"></textarea>
            </div>

            <div class="flex items-center justify-end gap-3 pt-3 border-t border-slate-100 mt-2">
                <button type="button" @click="tutupModalMintaRevisi()" class="px-4 py-2.5 rounded-xl border border-slate-200 hover:bg-slate-50 text-slate-600 font-semibold text-xs sm:text-sm transition-colors cursor-pointer">
                    Batal
                </button>
                <button type="button" @click="submitMintaRevisiModal()" class="px-5 py-2.5 rounded-xl bg-amber-500 hover:bg-amber-600 text-white font-bold text-xs sm:text-sm shadow-md hover:shadow-lg transition-all flex items-center gap-2 cursor-pointer">
                    Kirim Catatan Revisi
                </button>
            </div>
        </div>
    </div>';

$newModalTolak = '    <div id="modalTolak" class="fixed inset-0 items-center justify-center bg-slate-900/80 backdrop-blur-sm p-4 z-[99999] hidden" style="display: none; z-index: 99999;">
        <div class="bg-white rounded-2xl border border-slate-200 shadow-2xl max-w-lg w-full p-6 space-y-4 relative transform transition-all" @click.stop>
            <div class="flex items-center justify-between border-b border-slate-100 pb-3">
                <div class="flex items-center gap-3">
                    <div class="w-10 h-10 rounded-xl bg-rose-100 text-rose-600 flex items-center justify-center font-bold text-lg shrink-0 shadow-inner">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M18.364 18.364A9 9 0 005.636 5.636m12.728 12.728A9 9 0 015.636 5.636m12.728 12.728L5.636 5.636"></path></svg>
                    </div>
                    <div>
                        <h3 class="font-bold text-slate-900 text-base">Tolak Permohonan FR.APL.02</h3>
                        <p class="text-[11px] sm:text-xs text-slate-500">Asesi tidak memenuhi kriteria persyaratan asesmen</p>
                    </div>
                </div>
                <button type="button" @click="tutupModalTolak()" class="text-slate-400 hover:text-slate-700 bg-slate-50 hover:bg-slate-100 rounded-full w-8 h-8 flex items-center justify-center transition-colors cursor-pointer">&times;</button>
            </div>

            <div class="text-xs sm:text-sm text-rose-800 leading-relaxed bg-rose-50 p-3 rounded-xl border border-rose-200">
                <strong>Perhatian:</strong> Permohonan FR.APL.02 asesi akan dinyatakan <strong>Ditolak</strong>. Asesi tidak dapat melanjutkan asesmen mandiri pada skema ini.
            </div>

            <div class="space-y-1.5">
                <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider">
                    Alasan Penolakan <span class="text-rose-500">*</span>
                </label>
                <textarea id="modal-catatan-tolak-input" 
                          rows="4" 
                          x-model="modalCatatanTolak"
                          placeholder="Tuliskan alasan penolakan permohonan asesmen..." 
                          class="w-full bg-slate-50 border border-slate-300 rounded-xl p-3 text-sm text-slate-800 focus:ring-2 focus:ring-rose-500 focus:border-rose-500 outline-none leading-relaxed transition-all shadow-sm"></textarea>
            </div>

            <div class="flex items-center justify-end gap-3 pt-3 border-t border-slate-100 mt-2">
                <button type="button" @click="tutupModalTolak()" class="px-4 py-2.5 rounded-xl border border-slate-200 hover:bg-slate-50 text-slate-600 font-semibold text-xs sm:text-sm transition-colors cursor-pointer">
                    Batal
                </button>
                <button type="button" @click="submitTolakModal()" class="px-5 py-2.5 rounded-xl bg-rose-600 hover:bg-rose-700 text-white font-bold text-xs sm:text-sm shadow-md hover:shadow-lg transition-all flex items-center gap-2 cursor-pointer">
                    Konfirmasi Penolakan
                </button>
            </div>
        </div>
    </div>';

// Extract parts
$parts1 = explode('<!-- =========================================================================
         MODAL POPUP MINTA REVISI
         ========================================================================= -->', $content);

if (count($parts1) == 2) {
    $beforeRevisi = $parts1[0];
    
    $parts2 = explode('<!-- =========================================================================
         MODAL POPUP CANVAS SIGNATURE PAD', $parts1[1]);
    
    if (count($parts2) == 2) {
        $afterTolak = '<!-- =========================================================================
         MODAL POPUP CANVAS SIGNATURE PAD' . $parts2[1];
        
        $content = $beforeRevisi . "<!-- =========================================================================\n         MODAL POPUP MINTA REVISI\n         ========================================================================= -->\n" . $newModalRevisi . "\n\n    <!-- =========================================================================\n         MODAL POPUP TOLAK PERMOHONAN FR.APL.02\n         ========================================================================= -->\n" . $newModalTolak . "\n\n    " . $afterTolak;
        
        file_put_contents($file, $content);
        echo "Updated input-penilaian.blade.php\n";
    } else {
        echo "Could not find MODAL POPUP CANVAS SIGNATURE PAD";
    }
} else {
    echo "Could not find MODAL POPUP MINTA REVISI";
}
