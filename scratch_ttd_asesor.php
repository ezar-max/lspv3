<?php
$file = 'c:/laragon/www/lspv3/resources/views/asesor/input-penilaian.blade.php';
$content = file_get_contents($file);

$oldTtdAsesi = '<div class="flex items-center justify-between">
                            <div class="space-y-0.5">
                                <span class="text-[9px] font-bold text-slate-400 uppercase tracking-wider">Asesi Pemohon:</span>
                                <div class="font-bold text-slate-900 text-xs sm:text-sm">{{ $pendaftaran->asesi->nama_lengkap }}</div>
                                <div class="text-[10px] text-slate-400">
                                    Tanggal: {{ $pendaftaran->tanggal_ttd_asesi ? $pendaftaran->tanggal_ttd_asesi->format(\'d/m/Y\') : date(\'d/m/Y\') }}
                                </div>
                            </div>
                            <div class="w-28 h-12 bg-white rounded border border-slate-200 flex items-center justify-center p-1 shrink-0">
                                @php
                                    $ttdAsesiApl02 = $pendaftaran->tanda_tangan_asesi ?? $pendaftaran->asesi->tanda_tangan;
                                    $srcAsesiApl02 = $ttdAsesiApl02 ? (\Illuminate\Support\Str::startsWith($ttdAsesiApl02, [\'data:image\', \'http://\', \'https://\']) ? $ttdAsesiApl02 : asset($ttdAsesiApl02)) : null;
                                @endphp
                                @if($srcAsesiApl02)
                                    <img src="{{ $srcAsesiApl02 }}" alt="TTD Asesi" class="max-h-full object-contain">
                                @else
                                    <span class="text-[9px] text-slate-400 italic">TTD Profil</span>
                                @endif
                            </div>
                        </div>';

$newTtdAsesi = '@php
                            $ttdAsesiApl02 = $pendaftaran->tanda_tangan_asesi ?? $pendaftaran->asesi->tanda_tangan;
                            $srcAsesiApl02 = $ttdAsesiApl02 ? (\Illuminate\Support\Str::startsWith($ttdAsesiApl02, [\'data:image\', \'http://\', \'https://\']) ? $ttdAsesiApl02 : asset($ttdAsesiApl02)) : null;
                        @endphp
                        <div class="border border-slate-200 rounded-xl bg-slate-50/70 p-4 flex flex-col sm:flex-row items-center justify-between gap-4">
                            <div class="space-y-1 text-center sm:text-left">
                                <span class="text-[9px] font-bold text-slate-400 uppercase tracking-wider block">Asesi Pemohon:</span>
                                <span class="text-xs font-bold text-slate-800 block">{{ $pendaftaran->asesi->nama_lengkap }}</span>
                                <span class="text-xs text-slate-500 block font-mono">NIK: {{ $pendaftaran->asesi->nik ?? \'-\' }}</span>
                                <span class="text-[11px] text-slate-400 block font-medium">Ditandatangani: {{ $pendaftaran->tanggal_ttd_asesi ? $pendaftaran->tanggal_ttd_asesi->format(\'d M Y\') : date(\'d M Y\') }}</span>
                            </div>
                            <div class="p-2 bg-white rounded-lg border border-slate-200 shadow-2xs">
                                @if($srcAsesiApl02)
                                    <img src="{{ $srcAsesiApl02 }}" alt="TTD Asesi" class="max-h-24 max-w-xs object-contain">
                                @else
                                    <span class="text-[9px] text-slate-400 italic">Belum ada TTD</span>
                                @endif
                            </div>
                        </div>';

$content = str_replace($oldTtdAsesi, $newTtdAsesi, $content);
file_put_contents($file, $content);
echo "Updated Asesor TTD Asesi\n";
