@extends('tata-letak.dasbor')

@section('judul', 'FR.AK.05 - Laporan Asesmen ' . $ak05->nomor_laporan)

@push('css')
    <!-- Signature Pad CDN -->
    <script src="https://cdn.jsdelivr.net/npm/signature_pad@4.1.7/dist/signature_pad.umd.min.js"></script>
@endpush

@section('konten')
@php
    $user = auth()->user();
    $isFinal = $ak05->isFinalized();
    $canEdit = in_array($user->peran, ['asesor', 'admin', 'superadmin']) && !$isFinal;
    $rekap = (array) ($ak05->rekap_asesi ?? []);
    $saranList = (array) ($ak05->saran_perbaikan ?? []);
@endphp

<div class="space-y-4" x-data="ak05EditApp()" x-cloak>

    <!-- TOP BREADCRUMB & ACTIONS -->
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3 border-b border-slate-200/80 pb-3">
        <div>
            <div class="flex items-center gap-2 text-xs text-slate-500 mb-1">
                <a href="{{ route('dokumen-asesmen.index') }}" class="hover:text-blue-600">Dokumen Asesmen</a>
                <span>/</span>
                <a href="{{ route('dokumen-asesmen.ak05.index') }}" class="hover:text-blue-600">FR.AK.05</a>
                <span>/</span>
                <span class="text-slate-800 font-bold font-mono">{{ $ak05->nomor_laporan }}</span>
            </div>
            <h1 class="text-base sm:text-lg font-black text-slate-900 tracking-tight flex items-center gap-2">
                <span>FR.AK.05 &bull; Laporan Asesmen ({{ $ak05->skema->nama_skema ?? '-' }})</span>
            </h1>
        </div>

        <div class="flex items-center gap-2 flex-wrap">
            <a href="{{ route('dokumen-asesmen.ak05.cetak', $ak05->id) }}" target="_blank" class="inline-flex items-center px-3 py-1.5 rounded-xl border border-slate-200 bg-white text-slate-700 font-semibold text-xs hover:bg-slate-50 transition-colors shadow-2xs">
                <span>Cetak A4</span>
            </a>

            @if($canEdit)
                <!-- Sinkronkan Ulang -->
                <form method="POST" action="{{ route('dokumen-asesmen.ak05.sync', $ak05->id) }}" class="inline">
                    @csrf
                    <button type="submit" class="inline-flex items-center px-3 py-1.5 rounded-xl border border-purple-200 bg-purple-50 text-purple-700 font-bold text-xs hover:bg-purple-100 transition-colors">
                        <span>Sinkronkan FR.AK.02</span>
                    </button>
                </form>
                <button type="button" @click="submitSave(false)" class="inline-flex items-center px-3.5 py-1.5 rounded-xl bg-slate-900 text-white font-bold text-xs hover:bg-slate-800 transition-colors shadow-2xs">
                    <span>Simpan Draf</span>
                </button>
            @endif
        </div>
    </div>

    <!-- NOTIFIKASI FLASH -->
    @if(session('sukses'))
        <div class="p-3.5 bg-emerald-50 border border-emerald-200 rounded-xl text-emerald-800 text-xs font-semibold flex items-center gap-2.5 shadow-2xs">
            <span>{{ session('sukses') }}</span>
        </div>
    @endif
    @if(session('error'))
        <div class="p-3.5 bg-rose-50 border border-rose-200 rounded-xl text-rose-800 text-xs font-semibold flex items-center gap-2.5 shadow-2xs">
            <span>{{ session('error') }}</span>
        </div>
    @endif

    <!-- CONTEXTUAL HEADER -->
    <div class="bg-white rounded-2xl border border-slate-200/90 shadow-2xs p-4 sm:p-5 space-y-3">
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-3 text-xs">
            <div class="bg-slate-50/70 border border-slate-100 rounded-xl p-3">
                <span class="text-[10px] font-bold text-slate-400 uppercase tracking-wider block">Skema Sertifikasi</span>
                <span class="font-bold text-slate-800 block mt-0.5">{{ $ak05->skema->nama_skema ?? '-' }}</span>
                <span class="text-[10px] text-slate-500 font-mono">Kode: {{ $ak05->skema->kode_skema ?? '-' }}</span>
            </div>

            <div class="bg-slate-50/70 border border-slate-100 rounded-xl p-3">
                <span class="text-[10px] font-bold text-slate-400 uppercase tracking-wider block">Tempat Uji & Sesi</span>
                <span class="font-bold text-slate-800 block mt-0.5">{{ $ak05->jadwal ? $ak05->jadwal->nama_tuk : 'Seluruh TUK Terdaftar' }}</span>
                <span class="text-[10px] text-slate-500">{{ $ak05->jadwal ? $ak05->jadwal->kode_jadwal : 'Agregasi Mandiri' }}</span>
            </div>

            <div class="bg-slate-50/70 border border-slate-100 rounded-xl p-3">
                <span class="text-[10px] font-bold text-slate-400 uppercase tracking-wider block">Asesor Pelapor</span>
                <span class="font-bold text-slate-800 block mt-0.5">{{ $ak05->asesor->nama_lengkap ?? '-' }}</span>
                <span class="text-[10px] text-slate-500 font-mono">MET: {{ $ak05->asesor->nomor_registrasi ?? 'MET.000.004455' }}</span>
            </div>

            <div class="bg-slate-50/70 border border-slate-100 rounded-xl p-3">
                <span class="text-[10px] font-bold text-slate-400 uppercase tracking-wider block">Rekapitulasi Total</span>
                <div class="flex items-center gap-2 mt-1">
                    <span class="px-2 py-0.5 bg-emerald-100 text-emerald-800 font-bold rounded">{{ $ak05->total_k }} K</span>
                    <span class="px-2 py-0.5 bg-rose-100 text-rose-800 font-bold rounded">{{ $ak05->total_bk }} BK</span>
                    <span class="text-slate-500">/ {{ $ak05->total_asesi }} Asesi</span>
                </div>
            </div>
        </div>
    </div>

    <!-- MAIN FORM -->
    <form id="ak05Form" method="POST" action="{{ route('dokumen-asesmen.ak05.simpan', $ak05->id) }}" class="space-y-4">
        @csrf
        <input type="hidden" name="finalize" id="finalizeInput" value="0">
        <input type="hidden" name="signature" id="signatureInput" value="">

        <!-- TABEL HASIL ASESMEN PESERTA (TERINTEGRASI FR.AK.02) -->
        <div class="bg-white rounded-2xl border border-slate-200/90 shadow-2xs overflow-hidden">
            <div class="px-4 py-3 bg-slate-50/80 border-b border-slate-200 flex items-center justify-between">
                <div>
                    <h3 class="text-xs font-black text-slate-800 uppercase tracking-wider">
                        1. Tabel Hasil Asesmen Peserta
                    </h3>
                    <p class="text-[11px] text-slate-500">
                        Hasil K/BK secara otomatis ditarik dari status keputusan akhir formulir FR.AK.02 masing-masing peserta.
                    </p>
                </div>
            </div>

            <div class="overflow-x-auto">
                <table class="w-full text-left border-collapse text-xs">
                    <thead>
                        <tr class="bg-slate-100/80 text-slate-600 text-[10px] font-bold uppercase tracking-wider border-b border-slate-200">
                            <th class="py-2.5 px-3 w-8 text-center">No</th>
                            <th class="py-2.5 px-3 min-w-[200px]">Nama Asesi</th>
                            <th class="py-2.5 px-3 w-32">No. Registrasi</th>
                            <th class="py-2.5 px-3 text-center w-16">K</th>
                            <th class="py-2.5 px-3 text-center w-16">BK</th>
                            <th class="py-2.5 px-3 min-w-[200px]">Keterangan</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100 font-medium">
                        @forelse($rekap as $idx => $r)
                            <tr class="hover:bg-slate-50/50">
                                <td class="py-2.5 px-3 text-center text-slate-400 font-bold">{{ $idx + 1 }}</td>
                                <td class="py-2.5 px-3 font-bold text-slate-800">
                                    {{ $r['nama_asesi'] }}
                                    <input type="hidden" name="rekap_asesi[{{ $idx }}][nama_asesi]" value="{{ $r['nama_asesi'] }}">
                                    <input type="hidden" name="rekap_asesi[{{ $idx }}][pendaftaran_id]" value="{{ $r['pendaftaran_id'] }}">
                                </td>
                                <td class="py-2.5 px-3 font-mono text-slate-600">
                                    {{ $r['nomor_pendaftaran'] }}
                                    <input type="hidden" name="rekap_asesi[{{ $idx }}][nomor_pendaftaran]" value="{{ $r['nomor_pendaftaran'] }}">
                                </td>
                                <td class="py-2.5 px-3 text-center">
                                    @if(strtoupper($r['k_bk']) === 'K')
                                        <span class="w-5 h-5 rounded-full bg-emerald-100 text-emerald-800 inline-flex items-center justify-center font-bold text-[10px]">✓</span>
                                    @endif
                                </td>
                                <td class="py-2.5 px-3 text-center">
                                    @if(strtoupper($r['k_bk']) === 'BK')
                                        <span class="w-5 h-5 rounded-full bg-rose-100 text-rose-800 inline-flex items-center justify-center font-bold text-[10px]">✓</span>
                                    @endif
                                </td>
                                <td class="py-2.5 px-3">
                                    <input type="text" name="rekap_asesi[{{ $idx }}][keterangan]" 
                                           value="{{ $r['keterangan'] ?? '' }}"
                                           {{ !$canEdit ? 'disabled' : '' }}
                                           placeholder="Keterangan hasil / tindak lanjut..."
                                           class="w-full text-xs rounded-xl border-slate-200 px-2.5 py-1 bg-slate-50/50 focus:bg-white">
                                    <input type="hidden" name="rekap_asesi[{{ $idx }}][k_bk]" value="{{ $r['k_bk'] }}">
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6" class="text-center py-6 text-slate-400">
                                    Belum ada peserta yang terdaftar pada skema ini.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

        <!-- SECTION 2: ANALISIS ASESMEN & REKOMENDASI -->
        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
            <!-- Aspek Positif & Negatif -->
            <div class="bg-white rounded-2xl border border-slate-200/90 shadow-2xs p-4 sm:p-5 space-y-3">
                <h3 class="text-xs font-black text-slate-800 uppercase tracking-wider border-b border-slate-100 pb-2">
                    2. Aspek Positif dan Negatif dalam Asesmen
                </h3>

                <div class="space-y-1">
                    <label class="block text-[11px] font-bold text-emerald-800 uppercase tracking-wider">Aspek Positif:</label>
                    <textarea name="aspek_positif" rows="3" {{ !$canEdit ? 'disabled' : '' }}
                              class="w-full text-xs rounded-xl border-slate-200 p-2.5 focus:border-purple-500">{{ old('aspek_positif', $ak05->aspek_positif) }}</textarea>
                </div>

                <div class="space-y-1">
                    <label class="block text-[11px] font-bold text-amber-800 uppercase tracking-wider">Aspek Negatif / Catatan Kendala:</label>
                    <textarea name="aspek_negatif" rows="3" {{ !$canEdit ? 'disabled' : '' }}
                              class="w-full text-xs rounded-xl border-slate-200 p-2.5 focus:border-purple-500">{{ old('aspek_negatif', $ak05->aspek_negatif) }}</textarea>
                </div>
            </div>

            <!-- Pencatatan Penolakan Hasil -->
            <div class="bg-white rounded-2xl border border-slate-200/90 shadow-2xs p-4 sm:p-5 space-y-3">
                <h3 class="text-xs font-black text-slate-800 uppercase tracking-wider border-b border-slate-100 pb-2">
                    3. Pencatatan Penolakan / Banding Hasil Asesmen
                </h3>

                <p class="text-[11px] text-slate-500">
                    Catat apabila terdapat peserta yang mengajukan banding atau menolak keputusan hasil asesmen kompetensi:
                </p>

                <textarea name="penolakan_hasil" rows="6" {{ !$canEdit ? 'disabled' : '' }}
                          placeholder="Tuliskan catatan jika ada asesi yang menolak hasil atau tidak setuju..."
                          class="w-full text-xs rounded-xl border-slate-200 p-2.5 focus:border-purple-500">{{ old('penolakan_hasil', $ak05->penolakan_hasil) }}</textarea>
            </div>
        </div>

        <!-- SECTION 3: SARAN PERBAIKAN BERPRIORITAS -->
        <div class="bg-white rounded-2xl border border-slate-200/90 shadow-2xs p-4 sm:p-5 space-y-3">
            <div class="flex items-center justify-between border-b border-slate-100 pb-2">
                <h3 class="text-xs font-black text-slate-800 uppercase tracking-wider">
                    4. Saran Perbaikan (Rekomendasi Tindak Lanjut)
                </h3>
                @if($canEdit)
                    <button type="button" @click="addSaranRow()" class="text-xs font-bold text-purple-600 hover:text-purple-800 flex items-center gap-1">
                        <span>Tambah Butir Saran</span>
                    </button>
                @endif
            </div>

            <div class="space-y-2">
                <template x-for="(saran, sIdx) in saranItems" :key="sIdx">
                    <div class="grid grid-cols-1 sm:grid-cols-12 gap-2 p-2.5 bg-slate-50 rounded-xl border border-slate-200/80 items-center">
                        <div class="sm:col-span-3">
                            <label class="block text-[10px] font-bold text-slate-400 uppercase">Pihak Terkait</label>
                            <input type="text" :name="'saran_perbaikan[' + sIdx + '][pihak]'" x-model="saran.pihak" {{ !$canEdit ? 'disabled' : '' }}
                                   placeholder="Contoh: Asesor / TUK / LSP"
                                   class="w-full text-xs rounded-lg border-slate-200 bg-white p-1.5">
                        </div>

                        <div class="sm:col-span-5">
                            <label class="block text-[10px] font-bold text-slate-400 uppercase">Tindakan Rekomendasi</label>
                            <input type="text" :name="'saran_perbaikan[' + sIdx + '][tindakan]'" x-model="saran.tindakan" {{ !$canEdit ? 'disabled' : '' }}
                                   placeholder="Tindakan yang perlu diperbaiki..."
                                   class="w-full text-xs rounded-lg border-slate-200 bg-white p-1.5">
                        </div>

                        <div class="sm:col-span-2">
                            <label class="block text-[10px] font-bold text-slate-400 uppercase">Prioritas</label>
                            <select :name="'saran_perbaikan[' + sIdx + '][prioritas]'" x-model="saran.prioritas" {{ !$canEdit ? 'disabled' : '' }}
                                    class="w-full text-xs rounded-lg border-slate-200 bg-white p-1.5">
                                <option value="Tinggi">Tinggi</option>
                                <option value="Sedang">Sedang</option>
                                <option value="Rendah">Rendah</option>
                            </select>
                        </div>

                        <div class="sm:col-span-2 flex items-center gap-1">
                            <div class="flex-1">
                                <label class="block text-[10px] font-bold text-slate-400 uppercase">Batas Waktu</label>
                                <input type="date" :name="'saran_perbaikan[' + sIdx + '][batas_waktu]'" x-model="saran.batas_waktu" {{ !$canEdit ? 'disabled' : '' }}
                                       class="w-full text-xs rounded-lg border-slate-200 bg-white p-1.5">
                            </div>
                            @if($canEdit)
                                <button type="button" @click="removeSaranRow(sIdx)" class="text-[11px] font-bold text-rose-600 hover:text-rose-800 p-1 mt-3" title="Hapus Baris">
                                    Hapus
                                </button>
                            @endif
                        </div>
                    </div>
                </template>
            </div>
        </div>

        <!-- SECTION 4: TANDA TANGAN ASESOR & FINALISASI LAPORAN -->
        <div class="bg-white rounded-2xl border border-slate-200/90 shadow-2xs p-4 sm:p-5 space-y-4">
            <div class="flex items-center justify-between border-b border-slate-100 pb-2">
                <div>
                    <h3 class="text-xs font-black text-slate-800 uppercase tracking-wider">
                        5. Pengesahan Laporan Asesor
                    </h3>
                    <p class="text-[11px] text-slate-500">
                        Finalisasi laporan akan mengunci data dan mencatat tanda tangan resmi asesor.
                    </p>
                </div>
                <div>
                    @if($ak05->isFinalized())
                        <span class="px-3 py-1 rounded-full bg-emerald-100 text-emerald-800 text-xs font-bold flex items-center">
                            <span>Laporan Telah Difinalisasi</span>
                        </span>
                    @endif
                </div>
            </div>

            <div class="flex flex-col sm:flex-row items-center justify-between gap-4">
                <div class="flex items-center gap-3">
                    <div class="w-24 h-20 border border-dashed border-slate-300 rounded-xl bg-slate-50/50 flex items-center justify-center p-1">
                        @if($ak05->tanda_tangan_asesor)
                            <img src="{{ $ak05->tanda_tangan_asesor }}" alt="TTD Asesor" class="max-h-16 object-contain">
                        @else
                            <span class="text-slate-400 text-[10px] italic text-center">Belum disahkan</span>
                        @endif
                    </div>
                    <div class="text-xs space-y-0.5">
                        <div class="font-bold text-slate-800">{{ $ak05->asesor->nama_lengkap ?? '-' }}</div>
                        <div class="text-slate-500 font-mono text-[11px]">No. Reg: {{ $ak05->asesor->nomor_registrasi ?? 'MET.000.004455' }}</div>
                        <div class="text-slate-400 text-[10px]">Tgl TTD: {{ $ak05->tanggal_ttd_asesor ? $ak05->tanggal_ttd_asesor->format('d/m/Y H:i') : '-' }}</div>
                    </div>
                </div>

                @if($canEdit)
                    <div class="flex items-center gap-2">
                        <button type="button" @click="submitSave(false)" class="py-2.5 px-4 rounded-xl border border-slate-200 bg-white text-slate-700 font-bold text-xs hover:bg-slate-50 transition-colors">
                            Simpan Draf Laporan
                        </button>
                        <button type="button" @click="openFinalizeModal()" class="py-2.5 px-4 rounded-xl bg-purple-600 hover:bg-purple-700 text-white font-bold text-xs transition-colors shadow-2xs">
                            Finalisasi & Kunci Laporan
                        </button>
                    </div>
                @endif
            </div>
        </div>
    </form>

    <!-- MODAL FINALISASI -->
    <div x-show="finalizeModalOpen" class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-slate-900/60 backdrop-blur-xs" style="display: none;">
        <div class="bg-white rounded-2xl border border-slate-200 shadow-2xl max-w-md w-full p-5 space-y-4" @click.outside="finalizeModalOpen = false">
            <div class="border-b border-slate-100 pb-2">
                <h4 class="font-bold text-sm text-slate-900">Konfirmasi Finalisasi Laporan Asesmen</h4>
                <p class="text-xs text-slate-500">Laporan FR.AK.05 yang difinalisasi akan dikunci sebagai dokumen resmi.</p>
            </div>

            <div class="space-y-2">
                <label class="block text-xs font-bold text-slate-700">Bubuhkan Tanda Tangan Asesor:</label>
                <div class="border border-slate-300 rounded-xl overflow-hidden bg-white">
                    <canvas id="ak05Canvas" width="400" height="170" class="w-full h-40 cursor-crosshair touch-none"></canvas>
                </div>
                <div class="flex items-center justify-between text-xs">
                    <button type="button" @click="clearCanvas()" class="text-rose-600 hover:underline">
                        Bersihkan Kanvas
                    </button>
                </div>
            </div>

            <div class="flex items-center gap-2 pt-2 border-t border-slate-100">
                <button type="button" @click="finalizeModalOpen = false" class="flex-1 py-2 rounded-xl bg-slate-100 text-slate-700 font-bold text-xs">
                    Batal
                </button>
                <button type="button" @click="confirmFinalize()" class="flex-1 py-2 rounded-xl bg-purple-600 text-white font-bold text-xs hover:bg-purple-700 shadow-2xs">
                    Sahkan & Kunci
                </button>
            </div>
        </div>
    </div>

</div>

@push('css')
<script>
    function ak05EditApp() {
        return {
            saranItems: @json($saranList),
            finalizeModalOpen: false,
            signaturePad: null,

            addSaranRow() {
                this.saranItems.push({
                    pihak: 'Asesor',
                    tindakan: '',
                    prioritas: 'Sedang',
                    batas_waktu: ''
                });
            },

            removeSaranRow(idx) {
                this.saranItems.splice(idx, 1);
            },

            submitSave(isFinalize) {
                document.getElementById('finalizeInput').value = isFinalize ? '1' : '0';
                document.getElementById('ak05Form').submit();
            },

            openFinalizeModal() {
                this.finalizeModalOpen = true;
                this.$nextTick(() => {
                    const canvas = document.getElementById('ak05Canvas');
                    if (canvas) {
                        this.signaturePad = new SignaturePad(canvas, {
                            backgroundColor: 'rgb(255, 255, 255)',
                            penColor: 'rgb(15, 23, 42)',
                        });
                    }
                });
            },

            clearCanvas() {
                if (this.signaturePad) this.signaturePad.clear();
            },

            confirmFinalize() {
                if (this.signaturePad && !this.signaturePad.isEmpty()) {
                    document.getElementById('signatureInput').value = this.signaturePad.toDataURL();
                } else {
                    alert('Silakan bubuhkan tanda tangan asesor pada kanvas.');
                    return;
                }
                this.submitSave(true);
            }
        };
    }
</script>
@endpush
@endsection
