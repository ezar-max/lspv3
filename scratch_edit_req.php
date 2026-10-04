<?php
$file = 'c:/laragon/www/lspv3/resources/views/asesi/tahapan/index.blade.php';
$content = file_get_contents($file);

// 1. Rename Tinjau Kembali
$content = str_replace(
    '&larr; Tinjau Kembali Data Permohonan (FR.APL.01)',
    '&larr; Kembali ke Data Permohonan (FR.APL.01)',
    $content
);

// 2. Insert "Ajukan Permohonan Edit" button when read-only
$buttonHtml = '
                        @if($isReadOnlyApl01 && $pendaftaran && !$pendaftaran->request_perbaikan)
                            <button type="button" onclick="document.getElementById(\'modalRequestEdit\').classList.remove(\'hidden\'); document.getElementById(\'modalRequestEdit\').style.display=\'flex\';" class="px-4 py-1.5 rounded-lg border border-amber-300 bg-amber-50 hover:bg-amber-100 text-amber-700 font-bold text-xs shadow-sm flex items-center justify-center gap-1.5 cursor-pointer mt-3 transition-colors w-full sm:w-auto">
                                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.232 5.232l3.536 3.536m-2.036-5.036a2.5 2.5 0 113.536 3.536L6.5 21.036H3v-3.572L16.732 3.732z"></path></svg>
                                Ajukan Izin Ubah Data
                            </button>
                        @elseif($pendaftaran && $pendaftaran->request_perbaikan)
                            <div class="px-4 py-2 rounded-lg border border-slate-200 bg-slate-50 text-slate-500 font-semibold text-xs mt-3 w-full sm:w-auto text-center inline-flex items-center justify-center gap-1.5">
                                <svg class="w-4 h-4 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                                Menunggu Persetujuan Ubah Data...
                            </div>
                        @endif
';

// Put it below the lock badge section
$content = str_replace(
    'Terkunci (Read-Only)
                            </span>
                        @endif',
    'Terkunci (Read-Only)
                            </span>
                        @endif
                        ' . $buttonHtml,
    $content
);

// 3. Insert Modal at the end of the body
$modalHtml = '
    <!-- Modal Request Edit Data -->
    <div id="modalRequestEdit" class="fixed inset-0 items-center justify-center bg-slate-900/70 backdrop-blur-sm p-4 z-[99999] hidden" style="display: none; z-index: 99999;">
        <div class="bg-white rounded-2xl border border-slate-200 shadow-2xl max-w-lg w-full p-6 space-y-4 relative transform transition-all">
            <div class="flex items-center justify-between border-b border-slate-100 pb-3">
                <div class="flex items-center gap-3">
                    <div class="w-10 h-10 rounded-xl bg-amber-100 text-amber-600 flex items-center justify-center font-bold text-lg shrink-0 shadow-inner">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.232 5.232l3.536 3.536m-2.036-5.036a2.5 2.5 0 113.536 3.536L6.5 21.036H3v-3.572L16.732 3.732z"></path></svg>
                    </div>
                    <div>
                        <h3 class="font-bold text-slate-900 text-base">Permohonan Izin Ubah Data</h3>
                        <p class="text-[11px] sm:text-xs text-slate-500">Minta akses ke Admin LSP untuk memperbaiki data APL.01 / Skema.</p>
                    </div>
                </div>
                <button type="button" onclick="document.getElementById(\'modalRequestEdit\').classList.add(\'hidden\'); document.getElementById(\'modalRequestEdit\').style.display=\'none\';" class="text-slate-400 hover:text-slate-700 bg-slate-50 hover:bg-slate-100 rounded-full w-8 h-8 flex items-center justify-center transition-colors cursor-pointer">&times;</button>
            </div>

            <div class="text-xs sm:text-sm text-slate-600 leading-relaxed bg-blue-50/50 p-3 rounded-xl border border-blue-100">
                Data permohonan sertifikasi Anda saat ini dikunci. Jika ada perubahan skema atau biodata, silakan jelaskan bagian yang ingin diubah. Admin LSP akan mengevaluasi permohonan Anda.
            </div>

            <form action="{{ $pendaftaran ? route(\'asesi.request-perbaikan\', $pendaftaran->id) : \'#\' }}" method="POST">
                @csrf
                <div class="space-y-1.5 mb-4">
                    <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider">
                        Alasan & Bagian yang Ingin Diubah <span class="text-rose-500">*</span>
                    </label>
                    <textarea name="catatan_request_perbaikan" rows="3" required placeholder="Contoh: Saya ingin mengubah pilihan skema sertifikasi karena salah pilih..." class="w-full bg-slate-50 border border-slate-300 rounded-xl p-3 text-sm text-slate-800 focus:ring-2 focus:ring-amber-500 focus:border-amber-500 outline-none leading-relaxed transition-all shadow-sm"></textarea>
                </div>

                <div class="flex items-center justify-end gap-3 pt-3 border-t border-slate-100 mt-2">
                    <button type="button" onclick="document.getElementById(\'modalRequestEdit\').classList.add(\'hidden\'); document.getElementById(\'modalRequestEdit\').style.display=\'none\';" class="px-4 py-2.5 rounded-xl border border-slate-200 hover:bg-slate-50 text-slate-600 font-semibold text-xs sm:text-sm transition-colors cursor-pointer">
                        Batal
                    </button>
                    <button type="submit" class="px-5 py-2.5 rounded-xl bg-amber-500 hover:bg-amber-600 text-white font-bold text-xs sm:text-sm shadow-md hover:shadow-lg transition-all flex items-center gap-2 cursor-pointer">
                        Kirim Permohonan
                    </button>
                </div>
            </form>
        </div>
    </div>
';

$content = str_replace('<!-- Script Alpine & Signature -->', $modalHtml . "\n" . '<!-- Script Alpine & Signature -->', $content);

file_put_contents($file, $content);
echo "Updated index.blade.php\n";
