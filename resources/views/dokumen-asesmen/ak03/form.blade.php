@extends('tata-letak.dasbor')

@section('judul', 'FR.AK.03 - Umpan Balik dan Catatan Asesmen')

@push('css')
    <!-- Signature Pad CDN -->
    <script src="https://cdn.jsdelivr.net/npm/signature_pad@4.1.7/dist/signature_pad.umd.min.js"></script>
@endpush

@section('konten')
@php
    $user = auth()->user();
    $isAsesi = $user->peran === 'asesi';
    $isSubmitted = $ak03->isSubmitted();
    $canEdit = $isAsesi && $isUnlocked && !$isSubmitted;
    $jawabanSaved = (array) ($ak03->jawaban_kuesioner ?? []);
@endphp

<div class="space-y-4" x-data="ak03FormApp()" x-cloak>

    <!-- TOP BREADCRUMB & HEADER -->
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3 border-b border-slate-200/80 pb-3">
        <div>
            <div class="flex items-center gap-2 text-xs text-slate-500 mb-1">
                <a href="{{ route('dokumen-asesmen.index') }}" class="hover:text-blue-600">Dokumen Asesmen</a>
                <span>/</span>
                <span class="px-2 py-0.5 rounded-md bg-indigo-50 text-indigo-700 font-mono font-bold text-[10px]">FR.AK.03</span>
                <span class="text-slate-800 font-bold hidden md:inline">Umpan Balik Asesi</span>
            </div>
            <h1 class="text-base sm:text-lg font-black text-slate-900 tracking-tight flex items-center gap-2">
                <span>FR.AK.03 &bull; Formulir Umpan Balik Asesi dan Catatan Asesmen</span>
            </h1>
        </div>

        <div class="flex items-center gap-2">
            <a href="{{ route('dokumen-asesmen.ak03.cetak', $pendaftaran->id) }}" target="_blank" class="inline-flex items-center px-3 py-1.5 rounded-xl border border-slate-200 bg-white text-slate-700 font-semibold text-xs hover:bg-slate-50 transition-colors shadow-2xs">
                <span>Cetak A4</span>
            </a>
            <a href="{{ (url()->previous() && url()->previous() !== url()->current()) ? url()->previous() : route('dokumen-asesmen.index') }}" 
               onclick="if (document.referrer && document.referrer !== window.location.href) { window.location.href = document.referrer; return false; } else if (window.history.length > 1) { window.history.back(); return false; }"
               class="inline-flex items-center px-3 py-1.5 rounded-xl border border-slate-200 bg-white text-slate-700 font-semibold text-xs hover:bg-slate-50 transition-colors cursor-pointer shadow-2xs">
                &larr; Kembali
            </a>
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

    <!-- GATEKEEPER BANNER JIKA BELUM TERBUKA -->
    @if(!$isUnlocked)
        <div class="p-4 bg-amber-50 border border-amber-200 rounded-2xl text-amber-900 text-xs space-y-1.5 shadow-2xs">
            <div class="flex items-center gap-2 font-black text-sm text-amber-800">
                <span>Formulir Belum Terbuka &bull; FR.AK.03 Terkunci (Menunggu keputusan asesmen FR.AK.02)</span>
            </div>
            <p class="text-amber-700 leading-relaxed">
                Formulir umpan balik asesi ini dibuka secara otomatis setelah <strong>Asesor Kompetensi</strong> menyelesaikan penilaian dan menetapkan keputusan asesmen pada dokumen <strong>FR.AK.02</strong>. Silakan periksa kembali setelah asesmen selesai.
            </p>
        </div>
    @else
        <!-- BANNER TERBUKA ATAU TELAH DIKIRIM -->
        @if($isSubmitted)
            <div class="p-3.5 bg-emerald-50 border border-emerald-200 rounded-2xl text-emerald-800 text-xs font-semibold flex items-center justify-between gap-3 shadow-2xs">
                <div>
                    <span>Umpan balik telah dikirimkan oleh asesi dan lembar FR.AK.03 terkunci untuk menjaga integritas data.</span>
                </div>
                <span class="px-2.5 py-0.5 rounded-full bg-emerald-200/80 text-emerald-900 text-[10px] font-extrabold uppercase tracking-wider">
                    Terkunci
                </span>
            </div>
        @else
            <div class="p-3.5 bg-blue-50 border border-blue-200 rounded-2xl text-blue-800 text-xs flex items-center gap-2.5 shadow-2xs">
                <span>Silakan berikan umpan balik yang jujur dan konstruktif terhadap seluruh proses asesmen yang telah Anda ikuti.</span>
            </div>
        @endif
    @endif

    <!-- CONTEXTUAL INFORMATION CARD -->
    <div class="bg-white rounded-2xl border border-slate-200/90 shadow-2xs p-4 sm:p-5 space-y-3">
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-3 text-xs">
            <div class="bg-slate-50/70 border border-slate-100 rounded-xl p-3">
                <span class="text-[10px] font-bold text-slate-400 uppercase tracking-wider block">Skema Sertifikasi</span>
                <span class="font-bold text-slate-800 block mt-0.5">{{ $pendaftaran->skema->nama_skema ?? '-' }}</span>
                <span class="text-[10px] text-slate-500 font-mono">Kode: {{ $pendaftaran->skema->kode_skema ?? '-' }}</span>
            </div>

            <div class="bg-slate-50/70 border border-slate-100 rounded-xl p-3">
                <span class="text-[10px] font-bold text-slate-400 uppercase tracking-wider block">Tempat Uji Kompetensi (TUK)</span>
                <span class="font-bold text-slate-800 block mt-0.5">{{ $pendaftaran->jadwal->nama_tuk ?? 'TUK Mandiri SMKN 1 Gunungputri' }}</span>
                <span class="text-[10px] text-slate-500">Asesor: {{ $pendaftaran->asesor->nama_lengkap ?? '-' }}</span>
            </div>

            <div class="bg-slate-50/70 border border-slate-100 rounded-xl p-3">
                <span class="text-[10px] font-bold text-slate-400 uppercase tracking-wider block">Nama Asesi</span>
                <span class="font-bold text-slate-800 block mt-0.5">{{ $pendaftaran->asesi->nama_lengkap ?? '-' }}</span>
                <span class="text-[10px] text-blue-600 font-mono">No. Reg: #{{ $pendaftaran->nomor_pendaftaran }}</span>
            </div>

            <div class="bg-slate-50/70 border border-slate-100 rounded-xl p-3">
                <span class="text-[10px] font-bold text-slate-400 uppercase tracking-wider block">Status Dokumen</span>
                <span class="font-bold text-slate-800 block mt-0.5 uppercase">{{ str_replace('_', ' ', $ak03->status) }}</span>
                <span class="text-[10px] text-slate-500">Versi: v{{ $ak03->version }}</span>
            </div>
        </div>
    </div>

    <!-- KUESIONER 10 BUTIR STANDAR BNSP -->
    <form id="ak03Form" method="POST" action="{{ route('dokumen-asesmen.ak03.simpan', $pendaftaran->id) }}" class="space-y-4">
        @csrf
        <input type="hidden" name="submit" id="submitAction" value="0">
        <input type="hidden" name="signature" id="signatureValue" value="">

        <div class="bg-white rounded-2xl border border-slate-200/90 shadow-2xs overflow-hidden">
            <div class="px-4 py-3 bg-slate-50/80 border-b border-slate-200">
                <h3 class="text-xs font-black text-slate-800 uppercase tracking-wider">
                    Kuesioner Umpan Balik Peserta Asesmen
                </h3>
                <p class="text-[11px] text-slate-500">
                    Isi jawaban dengan memilih "Ya" atau "Tidak". Jika Anda memilih "Tidak", wajib menyertakan catatan penjelasan.
                </p>
            </div>

            <div class="overflow-x-auto">
                <table class="w-full text-left border-collapse text-xs">
                    <thead>
                        <tr class="bg-slate-100/80 text-slate-600 text-[10px] font-bold uppercase tracking-wider border-b border-slate-200">
                            <th class="py-2.5 px-3 w-8 text-center">No</th>
                            <th class="py-2.5 px-3 min-w-[280px]">Komponen / Pernyataan Umpan Balik</th>
                            <th class="py-2.5 px-3 text-center w-20">Ya</th>
                            <th class="py-2.5 px-3 text-center w-20">Tidak</th>
                            <th class="py-2.5 px-3 min-w-[200px]">Catatan / Komentar Asesi</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100 font-medium">
                        @foreach(\App\Models\AssessmentAk03::FEEDBACK_QUESTIONS as $no => $pertanyaan)
                            @php
                                $itemSaved = $jawabanSaved[$no] ?? [];
                                $pilihanSaved = strtolower($itemSaved['jawaban'] ?? 'ya');
                                $catatanSaved = $itemSaved['catatan'] ?? '';
                            @endphp
                            <tr class="hover:bg-slate-50/50">
                                <td class="py-3 px-3 text-center font-bold text-slate-400 align-top">{{ $no }}</td>
                                <td class="py-3 px-3 text-slate-800 leading-relaxed align-top">
                                    {{ $pertanyaan }}
                                </td>

                                <!-- Radio Ya -->
                                <td class="py-3 px-3 text-center align-top">
                                    <label class="inline-flex items-center justify-center p-1 rounded-lg cursor-pointer">
                                        <input type="radio" name="jawaban[{{ $no }}][jawaban]" value="ya"
                                               x-model="answers[{{ $no }}]"
                                               {{ !$canEdit ? 'disabled' : '' }}
                                               class="text-blue-600 focus:ring-blue-500 h-4 w-4">
                                    </label>
                                </td>

                                <!-- Radio Tidak -->
                                <td class="py-3 px-3 text-center align-top">
                                    <label class="inline-flex items-center justify-center p-1 rounded-lg cursor-pointer">
                                        <input type="radio" name="jawaban[{{ $no }}][jawaban]" value="tidak"
                                               x-model="answers[{{ $no }}]"
                                               {{ !$canEdit ? 'disabled' : '' }}
                                               class="text-rose-600 focus:ring-rose-500 h-4 w-4">
                                    </label>
                                </td>

                                <!-- Catatan Komentar -->
                                <td class="py-3 px-3 align-top">
                                    <input type="text" name="jawaban[{{ $no }}][catatan]" 
                                           value="{{ old("jawaban.{$no}.catatan", $catatanSaved) }}"
                                           :placeholder="answers[{{ $no }}] === 'tidak' ? 'Wajib cantumkan alasan...' : 'Catatan opsional...'"
                                           :class="answers[{{ $no }}] === 'tidak' ? 'border-rose-300 bg-rose-50/30' : 'border-slate-200'"
                                           {{ !$canEdit ? 'disabled' : '' }}
                                           class="w-full text-xs rounded-xl focus:border-blue-500 focus:ring-blue-500 px-2.5 py-1.5">
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>

        <!-- CATATAN / KOMENTAR LAINNYA & TANDA TANGAN -->
        <div class="grid grid-cols-1 lg:grid-cols-3 gap-4">
            <div class="lg:col-span-2 bg-white rounded-2xl border border-slate-200/90 shadow-2xs p-4 sm:p-5 space-y-3">
                <label class="block text-xs font-bold text-slate-700">
                    Catatan / Komentar Lainnya (Opsional):
                </label>
                <textarea name="catatan_lainnya" rows="3"
                          {{ !$canEdit ? 'disabled' : '' }}
                          placeholder="Sampaikan apresiasi, masukan fasilitas, atau catatan tambahan bagi LSP..."
                          class="w-full text-xs rounded-xl border-slate-200 focus:border-blue-500 focus:ring-blue-500 p-2.5">{{ old('catatan_lainnya', $ak03->catatan_lainnya) }}</textarea>
            </div>

            <!-- Tanda Tangan Asesi -->
            <div class="bg-white rounded-2xl border border-slate-200/90 shadow-2xs p-4 sm:p-5 space-y-3 flex flex-col justify-between">
                <div>
                    <div class="flex items-center justify-between border-b border-slate-100 pb-2">
                        <span class="text-xs font-bold text-slate-800">Tanda Tangan Asesi</span>
                        @if($ak03->tanda_tangan_asesi)
                            <span class="px-2 py-0.5 rounded-full bg-emerald-100 text-emerald-800 text-[10px] font-bold">
                                Terverifikasi
                            </span>
                        @endif
                    </div>

                    <div class="h-24 border border-dashed border-slate-300 rounded-xl bg-slate-50/50 mt-2 flex items-center justify-center p-2">
                        @if($ak03->tanda_tangan_asesi)
                            <img src="{{ $ak03->tanda_tangan_asesi }}" alt="Tanda Tangan Asesi" class="max-h-20 object-contain">
                        @else
                            <span class="text-slate-400 text-xs italic">Belum ditandatangani</span>
                        @endif
                    </div>
                </div>

                @if($canEdit)
                    <div class="flex items-center gap-2 pt-2">
                        <button type="button" @click="saveDraftOnly()" class="flex-1 py-2 px-3 rounded-xl border border-slate-200 bg-white text-slate-700 font-bold text-xs hover:bg-slate-50 transition-colors">
                            Simpan Draf
                        </button>
                        <button type="button" @click="openSignPadModal()" class="flex-1 py-2 px-3 rounded-xl bg-blue-600 text-white font-bold text-xs hover:bg-blue-700 transition-colors shadow-2xs">
                            Kirim Umpan Balik
                        </button>
                    </div>
                @endif
            </div>
        </div>
    </form>

    <!-- MODAL TANDA TANGAN ASESI -->
    <div x-show="signModalOpen" class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-slate-900/60 backdrop-blur-xs" style="display: none;">
        <div class="bg-white rounded-2xl border border-slate-200 shadow-2xl max-w-md w-full p-5 space-y-4" @click.outside="signModalOpen = false">
            <div class="flex items-center justify-between border-b border-slate-100 pb-3">
                <h4 class="font-bold text-sm text-slate-800">Tanda Tangan Pengiriman Umpan Balik</h4>
                <button type="button" @click="signModalOpen = false" class="text-slate-400 hover:text-slate-600 text-xs font-semibold px-2 py-1 rounded-lg hover:bg-slate-50">
                    Tutup
                </button>
            </div>

            <p class="text-xs text-slate-500">
                Setelah mengirimkan umpan balik, formulir akan <strong>terkunci</strong> dan tidak dapat diubah kembali.
            </p>

            <div class="border border-slate-300 rounded-xl overflow-hidden bg-white">
                <canvas id="ak03Canvas" width="400" height="180" class="w-full h-44 cursor-crosshair touch-none"></canvas>
            </div>

            <div class="flex items-center justify-between text-xs">
                <button type="button" @click="clearCanvas()" class="text-rose-600 hover:underline">
                    Bersihkan Kanvas
                </button>
                @if(auth()->user()->tanda_tangan)
                    <button type="button" @click="useStoredProfileSign()" class="text-blue-600 hover:underline">
                        Gunakan Tanda Tangan Profil
                    </button>
                @endif
            </div>

            <div class="flex items-center gap-2 pt-2 border-t border-slate-100">
                <button type="button" @click="signModalOpen = false" class="flex-1 py-2 rounded-xl bg-slate-100 text-slate-700 font-bold text-xs hover:bg-slate-200">
                    Batal
                </button>
                <button type="button" @click="confirmSubmitFeedback()" class="flex-1 py-2 rounded-xl bg-blue-600 text-white font-bold text-xs hover:bg-blue-700 shadow-2xs">
                    Kirim & Kunci Formulir
                </button>
            </div>
        </div>
    </div>

</div>

@push('css')
<script>
    function ak03FormApp() {
        return {
            answers: @json(collect(\App\Models\AssessmentAk03::FEEDBACK_QUESTIONS)->mapWithKeys(fn($q, $no) => [$no => strtolower($jawabanSaved[$no]['jawaban'] ?? 'ya')])),
            signModalOpen: false,
            signaturePad: null,

            saveDraftOnly() {
                document.getElementById('submitAction').value = '0';
                document.getElementById('ak03Form').submit();
            },

            openSignPadModal() {
                // Validasi lokal jika ada pilihan 'tidak' tapi komentar kosong
                for (let k in this.answers) {
                    if (this.answers[k] === 'tidak') {
                        const inputCatatan = document.querySelector(`input[name="jawaban[${k}][catatan]"]`);
                        if (!inputCatatan || !inputCatatan.value.trim()) {
                            alert(`Silakan berikan catatan untuk butir nomor ${k} karena memilih 'Tidak'.`);
                            if (inputCatatan) inputCatatan.focus();
                            return;
                        }
                    }
                }

                this.signModalOpen = true;
                this.$nextTick(() => {
                    const canvas = document.getElementById('ak03Canvas');
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

            useStoredProfileSign() {
                document.getElementById('signatureValue').value = "{{ auth()->user()->tanda_tangan }}";
                document.getElementById('submitAction').value = '1';
                document.getElementById('ak03Form').submit();
            },

            confirmSubmitFeedback() {
                if (this.signaturePad && !this.signaturePad.isEmpty()) {
                    document.getElementById('signatureValue').value = this.signaturePad.toDataURL();
                } else if (!"{{ auth()->user()->tanda_tangan }}") {
                    alert('Silakan bubuhkan tanda tangan pada kanvas terlebih dahulu.');
                    return;
                }
                document.getElementById('submitAction').value = '1';
                document.getElementById('ak03Form').submit();
            }
        };
    }
</script>
@endpush
@endsection
