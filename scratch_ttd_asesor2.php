<?php
$file = 'c:/laragon/www/lspv3/resources/views/asesor/input-penilaian.blade.php';
$content = file_get_contents($file);

$oldTtdAsesor = '<!-- Asesor Signature Box -->
                        <div class="bg-slate-50 p-3 rounded-lg border border-slate-200 space-y-2.5" id="container-box-ttd-asesor">
                            <div class="flex items-center justify-between">
                                <div class="space-y-0.5">
                                    <span class="text-[9px] font-bold text-slate-400 uppercase tracking-wider">Ditinjau Oleh Asesor:</span>
                                    <div class="font-bold text-slate-900 text-xs sm:text-sm">{{ auth()->user()->nama_lengkap }}</div>
                                    <div class="text-[10px] text-blue-600 font-mono font-semibold">
                                        No. Reg: {{ auth()->user()->nomor_registrasi ?? (\'MET.000.00\' . auth()->id() . \' 2026\') }}
                                    </div>
                                </div>
                                <div class="text-[10px] text-slate-400">
                                    Tanggal: {{ $pendaftaran->tanggal_ttd_asesor ? $pendaftaran->tanggal_ttd_asesor->format(\'d/m/Y\') : date(\'d/m/Y\') }}
                                </div>
                            </div>

                            @php
                                $ttdAsesorApl02 = $pendaftaran->tanda_tangan_asesor;
                                $srcAsesorApl02 = $ttdAsesorApl02 ? (\Illuminate\Support\Str::startsWith($ttdAsesorApl02, [\'data:image\', \'http://\', \'https://\']) ? $ttdAsesorApl02 : asset($ttdAsesorApl02)) : \'\';
                            @endphp

                            <!-- Hidden Base64 Input -->
                            <input type="hidden" name="tanda_tangan_asesor" id="input-ttd-asesi-base64" value="{{ old(\'tanda_tangan_asesor\', $ttdAsesorApl02) }}">

                            <!-- Preview & Button -->
                            <div class="flex items-center gap-3">
                                <div id="box-preview-ttd-asesi" class="h-12 w-28 bg-white rounded border border-slate-200 flex items-center justify-center p-1 shrink-0" style="{{ $srcAsesorApl02 ? \'\' : \'display: none;\' }}">
                                    <img id="preview-ttd-asesi-img" src="{{ $srcAsesorApl02 }}" alt="TTD Asesor" class="max-h-full object-contain">
                                </div>

                                <div class="flex-1">
                                    <div id="pesan-ttd-asesor-kosong" class="text-[11px] text-rose-600 italic mb-1" style="{{ $srcAsesorApl02 ? \'display: none;\' : \'display: block;\' }}">
                                        Tanda Tangan Asesor belum dibubuhkan.
                                    </div>

                                    @if(!$isLocked)
                                        <button type="button" 
                                                id="btn-modal-ttd-asesor" 
                                                @click="bukaModalTtdAsesor()"
                                                class="px-2.5 py-1.5 rounded-lg {{ $srcAsesorApl02 ? \'bg-white border-slate-300 text-slate-700 hover:bg-slate-50\' : \'bg-blue-600 text-white hover:bg-blue-700 shadow-2xs\' }} border font-semibold text-xs transition-colors inline-flex items-center gap-1 cursor-pointer">
                                            <span>{{ $pendaftaran->tanda_tangan_asesor ? \'Ubah Tanda Tangan Canvas\' : \'Gambar TTD Digital (Wajib)\' }}</span>
                                        </button>
                                    @endif
                                </div>
                            </div>
                        </div>';

$newTtdAsesor = '<!-- Asesor Signature Box -->
                        @php
                            $ttdAsesorApl02 = $pendaftaran->tanda_tangan_asesor;
                            $srcAsesorApl02 = $ttdAsesorApl02 ? (\Illuminate\Support\Str::startsWith($ttdAsesorApl02, [\'data:image\', \'http://\', \'https://\']) ? $ttdAsesorApl02 : asset($ttdAsesorApl02)) : \'\';
                        @endphp
                        <div class="border border-slate-200 rounded-xl bg-slate-50/70 p-4 flex flex-col sm:flex-row items-center justify-between gap-4 mt-4" id="container-box-ttd-asesor">
                            <div class="space-y-1 text-center sm:text-left">
                                <span class="text-[9px] font-bold text-slate-400 uppercase tracking-wider block">Ditinjau Oleh Asesor:</span>
                                <span class="text-xs font-bold text-slate-800 block">{{ auth()->user()->nama_lengkap }}</span>
                                <span class="text-xs text-blue-600 block font-mono">No. Reg: {{ auth()->user()->nomor_registrasi ?? (\'MET.000.00\' . auth()->id() . \' 2026\') }}</span>
                                <span class="text-[11px] text-slate-400 block font-medium">Tanggal: {{ $pendaftaran->tanggal_ttd_asesor ? $pendaftaran->tanggal_ttd_asesor->format(\'d M Y\') : date(\'d M Y\') }}</span>
                                
                                <div id="pesan-ttd-asesor-kosong" class="text-[11px] text-rose-600 italic mt-2 font-bold" style="{{ $srcAsesorApl02 ? \'display: none;\' : \'display: block;\' }}">
                                    Tanda Tangan Asesor belum dibubuhkan.
                                </div>
                                @if(!$isLocked)
                                    <div class="pt-2">
                                        <button type="button" 
                                                id="btn-modal-ttd-asesor" 
                                                @click="bukaModalTtdAsesor()"
                                                class="px-3 py-1.5 rounded-lg {{ $srcAsesorApl02 ? \'bg-white border border-slate-300 text-slate-700 hover:bg-slate-50\' : \'bg-blue-600 border border-blue-600 text-white hover:bg-blue-700 shadow-sm\' }} font-semibold text-[11px] transition-colors inline-flex items-center gap-1 cursor-pointer">
                                            <span>{{ $pendaftaran->tanda_tangan_asesor ? \'Ubah Tanda Tangan Canvas\' : \'Gambar TTD Digital (Wajib)\' }}</span>
                                        </button>
                                    </div>
                                @endif
                            </div>
                            
                            <input type="hidden" name="tanda_tangan_asesor" id="input-ttd-asesi-base64" value="{{ old(\'tanda_tangan_asesor\', $ttdAsesorApl02) }}">

                            <div class="p-2 bg-white rounded-lg border border-slate-200 shadow-2xs" id="box-preview-ttd-asesi" style="{{ $srcAsesorApl02 ? \'\' : \'display: none;\' }}">
                                <img id="preview-ttd-asesi-img" src="{{ $srcAsesorApl02 }}" alt="TTD Asesor" class="max-h-24 max-w-xs object-contain">
                            </div>
                        </div>';

$content = str_replace($oldTtdAsesor, $newTtdAsesor, $content);
file_put_contents($file, $content);
echo "Updated Asesor TTD Asesor\n";
