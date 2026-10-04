<?php
$file = 'c:/laragon/www/lspv3/resources/views/asesi/apl02.blade.php';
$content = file_get_contents($file);

$oldKanan = '<td style="width: 52%; padding: 0; vertical-align: top;">
                                <table style="width: 100%; border-collapse: collapse;">
                                    <!-- BLOCK ASESI -->
                                    <tr>
                                        <th colspan="2" style="text-align: left; background: #f1f5f9; padding: 0.75rem 1rem; border-bottom: 1px solid #e2e8f0; color: #0f172a; font-weight: 700; font-size: 0.92rem;">
                                            Asesi :
                                        </th>
                                    </tr>
                                    <tr>
                                        <td style="width: 140px; padding: 0.75rem 1rem; border-bottom: 1px solid #e2e8f0; font-weight: 600; color: #475569;">Nama</td>
                                        <td style="padding: 0.75rem 1rem; border-bottom: 1px solid #e2e8f0; font-weight: 700; color: #0f172a;">
                                            {{ $pendaftaran->asesi->nama_lengkap }}
                                        </td>
                                    </tr>
                                    <tr>
                                        <td style="padding: 0.75rem 1rem; border-bottom: 1px solid #e2e8f0; font-weight: 600; color: #475569;">Tanda tangan / Tanggal</td>
                                        <td style="padding: 0.75rem 1rem; border-bottom: 1px solid #e2e8f0;">
                                            @php
                                                $ttdAsesiApl02 = $pendaftaran->tanda_tangan_asesi ?? $pendaftaran->asesi->tanda_tangan;
                                                $srcAsesiApl02 = $ttdAsesiApl02 ? (\Illuminate\Support\Str::startsWith($ttdAsesiApl02, [\'data:image\', \'http://\', \'https://\']) ? $ttdAsesiApl02 : asset($ttdAsesiApl02)) : \'\';
                                            @endphp
                                            <!-- INPUT HIDDEN BASE64 TTD -->
                                            <input type="hidden" name="tanda_tangan_asesi" id="input-ttd-asesi-base64" value="{{ old(\'tanda_tangan_asesi\', $ttdAsesiApl02) }}">

                                            <!-- PREVIEW TTD CANVAS -->
                                            <div id="box-preview-ttd-asesi" style="margin: 0.25rem 0; {{ $srcAsesiApl02 ? \'\' : \'display: none;\' }}">
                                                <img id="preview-ttd-asesi-img" src="{{ $srcAsesiApl02 }}" alt="TTD Asesi" style="max-height: 60px; border: 1px solid #cbd5e1; padding: 0.2rem; background: #ffffff; border-radius: 6px;">
                                            </div>

                                            @if(!$isFormDisabled)
                                                <button type="button" class="tombol tombol-utama tombol-sm" onclick="bukaModal(\'modalCanvasTtd\')" style="margin-top: 0.35rem; font-size: 0.8rem;">
                                                    {{ $srcAsesiApl02 ? \'Ubah Tanda Tangan Canvas\' : \'Gambar Tanda Tangan Digital\' }}
                                                </button>
                                            @endif

                                            <div style="font-size: 0.8rem; color: #64748b; margin-top: 0.35rem;">
                                                Tanggal: {{ $pendaftaran->tanggal_ttd_asesi ? $pendaftaran->tanggal_ttd_asesi->format(\'d/m/Y\') : date(\'d/m/Y\') }}
                                            </div>
                                        </td>
                                    </tr>

                                    <!-- BLOCK ASESOR -->
                                    <tr>
                                        <th colspan="2" style="text-align: left; background: #f1f5f9; padding: 0.75rem 1rem; border-bottom: 1px solid #e2e8f0; color: #0f172a; font-weight: 700; font-size: 0.92rem;">
                                            Ditinjau Oleh Asesor :
                                        </th>
                                    </tr>
                                    <tr>
                                        <td style="padding: 0.75rem 1rem; border-bottom: 1px solid #e2e8f0; font-weight: 600; color: #475569;">Nama :</td>
                                        <td style="padding: 0.75rem 1rem; border-bottom: 1px solid #e2e8f0; font-weight: 700; color: #0f172a;">
                                            {{ $pendaftaran->asesor->nama_lengkap ?? \'Belum Ditentukan\' }}
                                        </td>
                                    </tr>
                                    <tr>
                                        <td style="padding: 0.75rem 1rem; border-bottom: 1px solid #e2e8f0; font-weight: 600; color: #475569;">No. Reg:</td>
                                        <td style="padding: 0.75rem 1rem; border-bottom: 1px solid #e2e8f0; font-weight: 700; color: #2563eb;">
                                            {{ $pendaftaran->asesor->nomor_registrasi ?? (\'MET.000.00\' . ($pendaftaran->asesor_id ?? \'00\') . \' 2026\') }}
                                        </td>
                                    </tr>
                                    <tr>
                                        <td style="padding: 0.75rem 1rem; font-weight: 600; color: #475569;">Tanda tangan / Tanggal</td>
                                        <td style="padding: 0.75rem 1rem;">
                                            @php
                                                $ttdAsesorApl02 = $pendaftaran->tanda_tangan_asesor ?? ($pendaftaran->asesor->tanda_tangan ?? null);
                                                $srcAsesorApl02 = $ttdAsesorApl02 ? (\Illuminate\Support\Str::startsWith($ttdAsesorApl02, [\'data:image\', \'http://\', \'https://\']) ? $ttdAsesorApl02 : asset($ttdAsesorApl02)) : null;
                                            @endphp
                                            @if($srcAsesorApl02)
                                                <img src="{{ $srcAsesorApl02 }}" alt="TTD Asesor" style="max-height: 55px; display: block; margin-bottom: 0.25rem;">
                                            @else
                                                <span style="color: #64748b; font-style: italic; font-size: 0.82rem;">(Disahkan oleh Asesor di halaman peninjauan Asesor)</span>
                                            @endif
                                            @if($pendaftaran->tanggal_ttd_asesor)
                                                <div style="font-size: 0.8rem; color: #64748b; margin-top: 0.2rem;">
                                                    Tanggal: {{ $pendaftaran->tanggal_ttd_asesor->format(\'d/m/Y\') }}
                                                </div>
                                            @endif
                                        </td>
                                    </tr>
                                </table>
                            </td>';

$newKanan = '<td style="width: 52%; padding: 1.5rem; vertical-align: top;">
                                <!-- BLOCK ASESI -->
                                @php
                                    $ttdAsesiApl02 = $pendaftaran->tanda_tangan_asesi ?? $pendaftaran->asesi->tanda_tangan;
                                    $srcAsesiApl02 = $ttdAsesiApl02 ? (\Illuminate\Support\Str::startsWith($ttdAsesiApl02, [\'data:image\', \'http://\', \'https://\']) ? $ttdAsesiApl02 : asset($ttdAsesiApl02)) : \'\';
                                @endphp
                                <input type="hidden" name="tanda_tangan_asesi" id="input-ttd-asesi-base64" value="{{ old(\'tanda_tangan_asesi\', $ttdAsesiApl02) }}">
                                <div class="border border-slate-200 rounded-xl bg-slate-50/70 p-4 flex flex-col sm:flex-row items-center justify-between gap-4 mb-4" style="background: rgba(248, 250, 252, 0.7); border: 1px solid #e2e8f0; border-radius: 0.75rem; padding: 1rem; display: flex; align-items: center; justify-content: space-between; gap: 1rem;">
                                    <div class="space-y-1 text-center sm:text-left" style="line-height: 1.4;">
                                        <span class="block" style="font-size: 9px; font-weight: 700; color: #94a3b8; text-transform: uppercase; letter-spacing: 0.05em;">Asesi Pemohon:</span>
                                        <span class="block" style="font-size: 0.85rem; font-weight: 700; color: #1e293b;">{{ $pendaftaran->asesi->nama_lengkap }}</span>
                                        <span class="block" style="font-size: 0.75rem; color: #64748b; font-family: monospace;">NIK: {{ $pendaftaran->asesi->nik ?? \'-\' }}</span>
                                        <span class="block" style="font-size: 0.7rem; color: #94a3b8; font-weight: 500; margin-top: 0.25rem;">Ditandatangani: {{ $pendaftaran->tanggal_ttd_asesi ? $pendaftaran->tanggal_ttd_asesi->format(\'d M Y\') : date(\'d M Y\') }}</span>
                                        
                                        @if(!$isFormDisabled)
                                            <button type="button" class="tombol tombol-utama tombol-sm mt-2" onclick="bukaModal(\'modalCanvasTtd\')" style="font-size: 0.7rem; padding: 0.35rem 0.6rem; border-radius: 6px;">
                                                {{ $srcAsesiApl02 ? \'Ubah TTD Canvas\' : \'Gambar TTD Digital\' }}
                                            </button>
                                        @endif
                                    </div>
                                    <div style="padding: 0.5rem; background: #ffffff; border-radius: 0.5rem; border: 1px solid #e2e8f0; box-shadow: 0 1px 2px rgba(0,0,0,0.05);" id="box-preview-ttd-asesi" class="{{ $srcAsesiApl02 ? \'\' : \'hidden\' }}">
                                        <img id="preview-ttd-asesi-img" src="{{ $srcAsesiApl02 }}" alt="TTD Asesi" style="max-height: 60px; max-width: 150px; object-fit: contain;">
                                    </div>
                                </div>

                                <!-- BLOCK ASESOR -->
                                @php
                                    $ttdAsesorApl02 = $pendaftaran->tanda_tangan_asesor ?? ($pendaftaran->asesor->tanda_tangan ?? null);
                                    $srcAsesorApl02 = $ttdAsesorApl02 ? (\Illuminate\Support\Str::startsWith($ttdAsesorApl02, [\'data:image\', \'http://\', \'https://\']) ? $ttdAsesorApl02 : asset($ttdAsesorApl02)) : null;
                                @endphp
                                <div class="border border-slate-200 rounded-xl bg-slate-50/70 p-4 flex flex-col sm:flex-row items-center justify-between gap-4" style="background: rgba(248, 250, 252, 0.7); border: 1px solid #e2e8f0; border-radius: 0.75rem; padding: 1rem; display: flex; align-items: center; justify-content: space-between; gap: 1rem;">
                                    <div class="space-y-1 text-center sm:text-left" style="line-height: 1.4;">
                                        <span class="block" style="font-size: 9px; font-weight: 700; color: #94a3b8; text-transform: uppercase; letter-spacing: 0.05em;">Ditinjau Oleh Asesor:</span>
                                        <span class="block" style="font-size: 0.85rem; font-weight: 700; color: #1e293b;">{{ $pendaftaran->asesor->nama_lengkap ?? \'Belum Ditentukan\' }}</span>
                                        <span class="block" style="font-size: 0.75rem; color: #2563eb; font-family: monospace;">No. Reg: {{ $pendaftaran->asesor->nomor_registrasi ?? (\'MET.000.00\' . ($pendaftaran->asesor_id ?? \'00\') . \' 2026\') }}</span>
                                        <span class="block" style="font-size: 0.7rem; color: #94a3b8; font-weight: 500; margin-top: 0.25rem;">Ditandatangani: {{ $pendaftaran->tanggal_ttd_asesor ? $pendaftaran->tanggal_ttd_asesor->format(\'d M Y\') : \'-\' }}</span>
                                    </div>
                                    <div style="padding: 0.5rem; background: #ffffff; border-radius: 0.5rem; border: 1px solid #e2e8f0; box-shadow: 0 1px 2px rgba(0,0,0,0.05);">
                                        @if($srcAsesorApl02)
                                            <img src="{{ $srcAsesorApl02 }}" alt="TTD Asesor" style="max-height: 60px; max-width: 150px; object-fit: contain;">
                                        @else
                                            <span style="font-size: 9px; color: #94a3b8; font-style: italic;">Belum ada TTD</span>
                                        @endif
                                    </div>
                                </div>
                            </td>';

$content = str_replace($oldKanan, $newKanan, $content);
file_put_contents($file, $content);
echo "Updated Asesi APL02 Kanan\n";
