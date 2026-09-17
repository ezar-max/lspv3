@extends('tata-letak.dasbor')

@section('judul', 'FR.AK.07 - Kesepakatan Penyesuaian yang Wajar')

@push('css')
    <!-- Signature Pad CDN -->
    <script src="https://cdn.jsdelivr.net/npm/signature_pad@4.1.7/dist/signature_pad.umd.min.js"></script>
@endpush

@section('konten')
@php
    $asesorNama = $pendaftaran->asesor->nama_lengkap ?? 'Asesor LSP';
    $profileTtd = auth()->user()->tanda_tangan;
    $isSignedByAsesi = !empty($ak07->asesi_signature);
    $isConfirmed = $ak07->isConfirmed();
    $savedChecklist = (array) ($ak07->items_checklist ?? []);
    $potensiVal = (int) ($ak07->potensi_asesi ?? 1);
    $potensiText = $potensiDefinitions[$potensiVal] ?? '-';
    $faseText = match($ak07->fase_penggunaan ?? 'saat_pra_asesmen') {
        'pra_asesmen' => 'Pra Asesmen',
        'saat_pra_asesmen' => 'Pada Saat Asesmen',
        'setelah_pra_asesmen' => 'Setelah Asesmen',
        default => 'Pada Saat Asesmen'
    };
@endphp

<div class="max-w-5xl mx-auto px-2 sm:px-4 py-3 space-y-4" x-data="asesiAk07App()">

    <!-- BREADCRUMB -->
    <div class="flex items-center gap-2 text-xs text-slate-500 pb-1">
        <a href="{{ route('asesi.dashboard') }}" class="hover:text-blue-600 font-medium">Dashboard</a>
        <span>/</span>
        <a href="{{ route('asesi.tahapan', ['step' => 2, 'pendaftaran_id' => $pendaftaran->id]) }}" class="hover:text-blue-600 font-medium">Tahapan Asesmen</a>
        <span>/</span>
        <span class="text-slate-800 font-bold">FR.AK.07</span>
    </div>

    <!-- NOTIFIKASI SUKSES / ERROR -->
    @if(session('sukses'))
        <div class="p-3.5 bg-emerald-50 border border-emerald-200 rounded-xl text-emerald-800 text-xs font-semibold shadow-2xs">
            <span>{{ session('sukses') }}</span>
        </div>
    @endif

    @if(session('error'))
        <div class="p-3.5 bg-rose-50 border border-rose-200 rounded-xl text-rose-800 text-xs font-semibold shadow-2xs">
            <span>{{ session('error') }}</span>
        </div>
    @endif

    <!-- HEADER CARD -->
    <div class="bg-white rounded-2xl border border-slate-200/90 shadow-2xs p-4 sm:p-5 space-y-3">
        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3 border-b border-slate-100 pb-3">
            <div>
                <h1 class="text-base sm:text-lg font-bold text-slate-900 leading-tight">
                    FR.AK.07 &bull; Ceklis Penyesuaian yang Wajar dan Beralasan
                </h1>
                <p class="text-xs text-slate-500">
                    Kesepakatan penyesuaian metode dan instrumen asesmen antara Asesor dan Asesi.
                </p>
            </div>

            <!-- Status Pill Header -->
            <div class="flex items-center gap-2">
                @if($isConfirmed)
                    <span class="px-3 py-1 rounded-full bg-emerald-50 text-emerald-700 border border-emerald-200 text-xs font-bold flex items-center gap-1.5 shadow-2xs">
                        <span class="w-2 h-2 rounded-full bg-emerald-500"></span>
                        CONFIRMED &bull; Disahkan Kedua Pihak
                    </span>
                @else
                    <span class="px-3 py-1 rounded-full bg-amber-50 text-amber-700 border border-amber-200 text-xs font-bold flex items-center gap-1.5 shadow-2xs">
                        <span class="w-2 h-2 rounded-full bg-amber-500"></span>
                        {{ $isSignedByAsesi ? 'Menunggu Asesor' : 'Perlu Tanda Tangan Asesi' }}
                    </span>
                @endif
            </div>
        </div>

        <!-- Metadata Grid -->
        <div class="grid grid-cols-1 sm:grid-cols-3 gap-2.5 pt-1 text-xs">
            <div class="bg-slate-50 border border-slate-100 rounded-xl p-2.5 space-y-0.5">
                <span class="text-[10px] font-bold uppercase tracking-wider text-slate-400">Skema Sertifikasi</span>
                <div class="font-bold text-slate-800 truncate">{{ $pendaftaran->skema->nama_skema ?? '-' }}</div>
                <div class="text-[10px] text-slate-500">Kode: {{ $pendaftaran->skema->kode_skema ?? '-' }}</div>
            </div>

            <div class="bg-slate-50 border border-slate-100 rounded-xl p-2.5 space-y-0.5">
                <span class="text-[10px] font-bold uppercase tracking-wider text-slate-400">Asesor Penguji</span>
                <div class="font-bold text-slate-800 truncate">{{ $asesorNama }}</div>
                <div class="text-[10px] text-slate-500">TUK: {{ $pendaftaran->jadwal->nama_tuk ?? 'TUK LSP' }}</div>
            </div>

            <div class="bg-slate-50 border border-slate-100 rounded-xl p-2.5 space-y-0.5">
                <span class="text-[10px] font-bold uppercase tracking-wider text-slate-400">Potensi & Fase</span>
                <div class="font-bold text-slate-800">Kategori {{ $potensiVal }} &bull; {{ $faseText }}</div>
                <div class="text-[10px] text-slate-500 truncate" title="{{ $potensiText }}">{{ $potensiText }}</div>
            </div>
        </div>
    </div>

    <!-- DETAIL KESEPAKATAN MATRIKS 8 KATEGORI -->
    <div class="bg-white rounded-2xl border border-slate-200/90 shadow-2xs p-4 sm:p-5 space-y-4">
        <div class="flex items-center justify-between border-b border-slate-100 pb-2.5">
            <h3 class="text-sm font-bold text-slate-900">
                Matriks Bentuk Penyesuaian yang Disepakati
            </h3>
            <span class="text-[11px] text-slate-400">Standar Acuan BNSP FR.AK.07</span>
        </div>

        <div class="space-y-2.5 text-xs">
            @php $adaPenyesuaian = false; @endphp
            @foreach($criteriaDefinitions as $cId => $crit)
                @php
                    $itemSaved = $savedChecklist[$cId] ?? [];
                    $isPerlu = filter_var($itemSaved['perlu_penyesuaian'] ?? false, FILTER_VALIDATE_BOOLEAN);
                    $opsiSelected = (array) ($itemSaved['opsi_dipilih'] ?? []);
                    $ket = $itemSaved['keterangan'] ?? '';
                    if ($isPerlu) $adaPenyesuaian = true;
                @endphp

                @if($isPerlu)
                    <div class="p-3 bg-amber-50/40 border border-amber-200/80 rounded-xl space-y-1.5">
                        <div class="flex items-center gap-2">
                            <span class="px-2 py-0.5 rounded bg-amber-600 text-white font-bold text-[10px]">Kategori {{ $cId }}</span>
                            <span class="font-bold text-slate-800">{{ $crit['title'] }}</span>
                        </div>
                        @if(!empty($opsiSelected))
                            <ul class="list-disc list-inside text-[11px] text-slate-700 pl-1 space-y-0.5">
                                @foreach($opsiSelected as $opKey)
                                    @if(isset($crit['sub_options'][$opKey]))
                                        <li>{{ $crit['sub_options'][$opKey] }}</li>
                                    @endif
                                @endforeach
                            </ul>
                        @endif
                        @if(!empty($ket))
                            <div class="text-[11px] text-slate-600 italic bg-white p-2 rounded-lg border border-amber-100">
                                <strong>Catatan Khusus:</strong> {{ $ket }}
                            </div>
                        @endif
                    </div>
                @endif
            @endforeach

            @if(!$adaPenyesuaian)
                <div class="p-4 bg-slate-50 border border-slate-200 rounded-xl text-center text-slate-500">
                    Seluruh proses asesmen disepakati dapat dilaksanakan sesuai prosedur standar umum.
                </div>
            @endif
        </div>

        <!-- REKOMENDASI KESEPAKATAN -->
        <div class="bg-slate-50/80 border border-slate-100 rounded-xl p-3.5 space-y-2 text-xs">
            <span class="font-bold text-slate-800 block border-b border-slate-200 pb-1">
                Rekomendasi Kesepakatan Asesmen:
            </span>
            <div class="grid grid-cols-1 sm:grid-cols-3 gap-2 pt-1">
                <div>
                    <span class="text-slate-400 font-bold block text-[10px] uppercase">Acuan Pembanding</span>
                    <div class="font-medium text-slate-800">{{ $ak07->acuan_pembanding_disepakati ?? "SKKNI {$pendaftaran->skema->nama_skema}" }}</div>
                </div>
                <div>
                    <span class="text-slate-400 font-bold block text-[10px] uppercase">Metode Asesmen</span>
                    <div class="font-medium text-slate-800">{{ $ak07->metode_disepakati ?? 'Observasi Demonstrasi & Wawancara' }}</div>
                </div>
                <div>
                    <span class="text-slate-400 font-bold block text-[10px] uppercase">Instrumen Penyesuaian</span>
                    <div class="font-medium text-slate-800">{{ $ak07->instrumen_disepakati ?? 'FR.IA.01, FR.IA.03' }}</div>
                </div>
            </div>
            @if($ak07->catatan_asesor)
                <div class="pt-1 text-[11px] text-slate-600">
                    <strong>Catatan Asesor:</strong> {{ $ak07->catatan_asesor }}
                </div>
            @endif
        </div>
    </div>

    <!-- PANEL TANDA TANGAN ASESI -->
    <div class="bg-white rounded-2xl border border-slate-200/90 shadow-2xs p-4 sm:p-5 space-y-4">
        <div class="text-sm font-bold text-slate-900 border-b border-slate-100 pb-2.5">
            Persetujuan & Tanda Tangan Digital Asesi
        </div>

        <div class="grid grid-cols-1 md:grid-cols-2 gap-4 text-xs">
            <!-- Asesor Info & Signature Status -->
            <div class="border border-slate-200 rounded-xl p-3.5 space-y-2 bg-slate-50/50">
                <span class="font-bold text-slate-800 block">Asesor Penguji</span>
                <div class="text-slate-700 font-semibold">{{ $asesorNama }}</div>
                <div class="h-28 border border-slate-200 rounded-xl bg-white flex items-center justify-center p-2">
                    @if($ak07->asesor_signature)
                        <img src="{{ asset($ak07->asesor_signature) }}" alt="TTD Asesor" class="max-h-24 object-contain">
                    @else
                        <span class="text-slate-400 italic">Menunggu TTD Asesor</span>
                    @endif
                </div>
            </div>

            <!-- Asesi Signature Box -->
            <div class="border border-slate-200 rounded-xl p-3.5 space-y-2 bg-slate-50/50">
                <span class="font-bold text-slate-800 block">Tanda Tangan Anda (Asesi)</span>
                
                @if($isSignedByAsesi)
                    <div class="h-28 border border-emerald-200 bg-emerald-50/30 rounded-xl flex flex-col items-center justify-center p-2 text-center">
                        <img src="{{ asset($ak07->asesi_signature) }}" alt="TTD Asesi" class="max-h-20 object-contain mb-1">
                        <span class="text-[10px] text-emerald-700 font-bold">
                            Telah Ditandatangani ({{ $ak07->asesi_signed_at ? \Carbon\Carbon::parse($ak07->asesi_signed_at)->format('d/m/Y H:i') : '' }})
                        </span>
                    </div>
                @else
                    <form id="formSignAsesi" action="{{ route('asesi.ak07.sign-asesi', $pendaftaran->id) }}" method="POST" class="space-y-2">
                        @csrf
                        <input type="hidden" name="tanda_tangan_asesi" id="inputTtdAsesi" value="{{ $profileTtd }}">

                        <div class="flex items-center gap-2">
                            @if(!empty($profileTtd))
                                <label class="inline-flex items-center gap-1.5 cursor-pointer text-[11px] font-medium text-slate-700">
                                    <input type="radio" name="sigMode" value="profile" x-model="signatureMode" class="accent-blue-600">
                                    <span>Gunakan TTD Akun Profil</span>
                                </label>
                            @endif
                            <label class="inline-flex items-center gap-1.5 cursor-pointer text-[11px] font-medium text-slate-700">
                                <input type="radio" name="sigMode" value="canvas" x-model="signatureMode" class="accent-blue-600">
                                <span>Gores TTD Baru</span>
                            </label>
                        </div>

                        <!-- Canvas Box -->
                        <div x-show="signatureMode === 'canvas'" class="space-y-1">
                            <div class="border-2 border-dashed border-slate-300 rounded-xl bg-white relative p-1">
                                <canvas id="canvasAk07Asesi" class="w-full h-24 rounded-lg cursor-crosshair touch-none"></canvas>
                                <button type="button" @click="clearCanvas()" class="absolute top-1 right-1 px-1.5 py-0.5 bg-slate-100 text-slate-600 rounded text-[9px] font-bold">
                                    Bersihkan
                                </button>
                            </div>
                        </div>

                        <!-- Preview Profil Box -->
                        <div x-show="signatureMode === 'profile'" class="p-2 border border-slate-200 rounded-xl bg-white flex items-center justify-center h-24">
                            @if(!empty($profileTtd))
                                <img src="{{ asset($profileTtd) }}" alt="TTD Profil" class="max-h-20 object-contain">
                            @else
                                <span class="text-slate-400 italic">Belum ada tanda tangan di profil.</span>
                            @endif
                        </div>

                        <button type="button" @click="submitSignature()" class="w-full py-2 bg-blue-600 hover:bg-blue-700 text-white rounded-xl font-bold text-xs shadow-xs transition flex items-center justify-center">
                            <span>Tandatangani & Setujui Kesepakatan</span>
                        </button>
                    </form>
                @endif
            </div>
        </div>
    </div>

</div>

<!-- Alpine.js Component -->
<script>
    function asesiAk07App() {
        return {
            signatureMode: '{{ !empty($profileTtd) ? "profile" : "canvas" }}',
            canvasSignatureData: '',
            signaturePad: null,

            init() {
                this.$nextTick(() => {
                    this.initSignaturePad();
                });
            },

            initSignaturePad() {
                const canvas = document.getElementById('canvasAk07Asesi');
                if (!canvas) return;

                function resizeCanvas() {
                    const ratio = Math.max(window.devicePixelRatio || 1, 1);
                    canvas.width = canvas.offsetWidth * ratio;
                    canvas.height = canvas.offsetHeight * ratio;
                    canvas.getContext('2d').scale(ratio, ratio);
                }

                window.addEventListener('resize', resizeCanvas);
                resizeCanvas();

                this.signaturePad = new SignaturePad(canvas, {
                    backgroundColor: 'rgb(255, 255, 255)',
                    penColor: 'rgb(15, 23, 42)',
                    minWidth: 1.2,
                    maxWidth: 2.5
                });
            },

            clearCanvas() {
                if (this.signaturePad) {
                    this.signaturePad.clear();
                    this.canvasSignatureData = '';
                }
            },

            submitSignature() {
                if (this.signatureMode === 'canvas' && this.signaturePad && !this.signaturePad.isEmpty()) {
                    this.canvasSignatureData = this.signaturePad.toDataURL('image/png');
                    document.getElementById('inputTtdAsesi').value = this.canvasSignatureData;
                } else if (this.signatureMode === 'profile') {
                    document.getElementById('inputTtdAsesi').value = '{{ $profileTtd }}';
                }
                document.getElementById('formSignAsesi').submit();
            }
        };
    }
</script>
@endsection
