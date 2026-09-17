@extends('tata-letak.dasbor')

@section('judul', 'FR.AK.06 - Meninjau Proses Asesmen ' . $ak06->nomor_review)

@push('css')
    <!-- Signature Pad CDN -->
    <script src="https://cdn.jsdelivr.net/npm/signature_pad@4.1.7/dist/signature_pad.umd.min.js"></script>
@endpush

@section('konten')
@php
    $user = auth()->user();
    $isFinal = $ak06->isFinalized();
    $canEdit = in_array($user->peran, ['asesor', 'admin', 'superadmin']) && !$isFinal;
    $prosedurMatrix = (array) ($ak06->prosedur_matrix ?? []);
    $dimensiKompetensi = (array) ($ak06->dimensi_kompetensi ?? []);
    $rekomendasiList = (array) ($ak06->rekomendasi_peningkatan ?? []);
@endphp

<div class="space-y-4" x-data="ak06EditApp()" x-cloak>

    <!-- TOP BREADCRUMB & ACTIONS -->
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3 border-b border-slate-200/80 pb-3">
        <div>
            <div class="flex items-center gap-2 text-xs text-slate-500 mb-1">
                <a href="{{ route('dokumen-asesmen.index') }}" class="hover:text-blue-600">Dokumen Asesmen</a>
                <span>/</span>
                <a href="{{ route('dokumen-asesmen.ak06.index') }}" class="hover:text-blue-600">FR.AK.06</a>
                <span>/</span>
                <span class="text-slate-800 font-bold font-mono">{{ $ak06->nomor_review }}</span>
            </div>
            <h1 class="text-base sm:text-lg font-black text-slate-900 tracking-tight flex items-center gap-2">
                <span>FR.AK.06 &bull; Meninjau Proses Asesmen</span>
                <span class="text-xs px-2 py-0.5 rounded-full font-bold uppercase tracking-wider {{ $ak06->status === 'final' ? 'bg-emerald-100 text-emerald-800 border border-emerald-200' : 'bg-amber-100 text-amber-800 border border-amber-200' }}">
                    {{ $ak06->status === 'final' ? 'Final / Terkunci' : 'Draf Peninjauan' }}
                </span>
                <span class="text-xs px-2 py-0.5 rounded-full bg-slate-100 text-slate-700 font-mono font-bold">v{{ $ak06->version }}</span>
            </h1>
        </div>

        <div class="flex items-center gap-2 flex-wrap">
            <a href="{{ route('dokumen-asesmen.ak06.cetak', $ak06->id) }}" target="_blank" class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-xl border border-slate-200 bg-white text-slate-700 font-semibold text-xs hover:bg-slate-50 transition-colors shadow-2xs">
                <span>Cetak A4</span>
            </a>

            @if($canEdit)
                <button type="button" @click="submitSave(false)" class="inline-flex items-center gap-1.5 px-3.5 py-1.5 rounded-xl bg-slate-900 text-white font-bold text-xs hover:bg-slate-800 transition-colors shadow-2xs">
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

    <!-- CONTEXTUAL INFO CARD -->
    <div class="bg-white rounded-2xl border border-slate-200/90 shadow-2xs p-4 sm:p-5 space-y-3">
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-3 text-xs">
            <div class="bg-slate-50/70 border border-slate-100 rounded-xl p-3">
                <span class="text-[10px] font-bold text-slate-400 uppercase tracking-wider block">Skema Sertifikasi</span>
                <span class="font-bold text-slate-800 block mt-0.5">{{ $ak06->skema->nama_skema ?? '-' }}</span>
                <span class="text-[10px] text-slate-500 font-mono">Kode: {{ $ak06->skema->kode_skema ?? '-' }}</span>
            </div>

            <div class="bg-slate-50/70 border border-slate-100 rounded-xl p-3">
                <span class="text-[10px] font-bold text-slate-400 uppercase tracking-wider block">Lingkup Peninjauan</span>
                <span class="font-bold text-slate-800 block mt-0.5 capitalize">
                    @if($ak06->scope_type === 'individual')
                        Individual ({{ $ak06->pendaftaran->asesi->nama_lengkap ?? 'Peserta' }})
                    @elseif($ak06->scope_type === 'kelompok')
                        Kelompok / Sesi ({{ $ak06->jadwal->kode_jadwal ?? 'Sesi Uji' }})
                    @else
                        Skema Keseluruhan
                    @endif
                </span>
                <span class="text-[10px] text-slate-500">TUK: {{ $ak06->jadwal ? $ak06->jadwal->nama_tuk : 'TUK SMKN 1 Gunungputri' }}</span>
            </div>

            <div class="bg-slate-50/70 border border-slate-100 rounded-xl p-3">
                <span class="text-[10px] font-bold text-slate-400 uppercase tracking-wider block">Reviewer / Lead Asesor</span>
                <span class="font-bold text-slate-800 block mt-0.5">{{ $ak06->asesor->nama_lengkap ?? '-' }}</span>
                <span class="text-[10px] text-slate-500 font-mono">MET: {{ $ak06->asesor->nomor_registrasi ?? 'MET.000.005566' }}</span>
            </div>

            <div class="bg-slate-50/70 border border-slate-100 rounded-xl p-3">
                <span class="text-[10px] font-bold text-slate-400 uppercase tracking-wider block">Tanggal Review</span>
                <span class="font-bold text-slate-800 block mt-0.5">{{ $ak06->tanggal_review ? $ak06->tanggal_review->translatedFormat('d F Y') : date('d F Y') }}</span>
                <span class="text-[10px] text-slate-500">Status: {{ strtoupper($ak06->status) }}</span>
            </div>
        </div>

        <div class="bg-amber-50/60 border border-amber-200/70 rounded-xl p-3 text-xs text-amber-900 flex items-start gap-2.5">
            <div>
                <span class="font-bold block">Pedoman Peninjauan Proses Asesmen:</span>
                Dokumen ini digunakan oleh Asesor/Lead Asesor untuk mereview kesesuaian penerapan 4 Prinsip Asesmen (Valid, Reliabel, Fleksibel, Adil) pada 6 prosedur pokok asesmen, konsistensi 5 Dimensi Kompetensi, serta merumuskan rekomendasi tindak lanjut perbaikan proses.
            </div>
        </div>
    </div>

    <!-- MAIN FORM CONTAINER -->
    <form id="form-ak06" method="POST" action="{{ route('dokumen-asesmen.ak06.simpan', $ak06->id) }}" class="space-y-6">
        @csrf
        <input type="hidden" name="finalize" id="input-finalize" value="0">
        <input type="hidden" name="signature" id="input-signature" value="">

        <!-- ================================================================= -->
        <!-- BAGIAN A: PROSEDUR ASESMEN & 4 PRINSIP ASESMEN -->
        <!-- ================================================================= -->
        <div class="bg-white rounded-2xl border border-slate-200/90 shadow-2xs overflow-hidden">
            <div class="px-4 sm:px-5 py-3.5 bg-slate-50/90 border-b border-slate-200 flex items-center justify-between">
                <div class="flex items-center gap-2">
                    <span class="w-6 h-6 rounded-lg bg-blue-600 text-white font-bold text-xs flex items-center justify-center">A</span>
                    <h2 class="text-sm font-bold text-slate-900">Peninjauan Prosedur & 4 Prinsip Asesmen</h2>
                </div>
                <span class="text-[11px] text-slate-500 font-medium">Valid, Reliabel, Fleksibel, Adil</span>
            </div>

            <div class="overflow-x-auto">
                <table class="w-full text-left text-xs border-collapse">
                    <thead>
                        <tr class="bg-slate-100/75 text-slate-700 font-bold border-b border-slate-200">
                            <th class="py-2.5 px-3 w-10 text-center">No</th>
                            <th class="py-2.5 px-3 min-w-[200px]">Aspek / Prosedur Asesmen</th>
                            <th class="py-2.5 px-2 text-center w-28">Valid</th>
                            <th class="py-2.5 px-2 text-center w-28">Reliabel</th>
                            <th class="py-2.5 px-2 text-center w-28">Fleksibel</th>
                            <th class="py-2.5 px-2 text-center w-28">Adil</th>
                            <th class="py-2.5 px-3 min-w-[220px]">Catatan / Komentar Aspek</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-200/80">
                        @php $no = 1; @endphp
                        @foreach(App\Models\AssessmentAk06::PROCEDURES as $key => $title)
                            @php
                                $row = $prosedurMatrix[$key] ?? [
                                    'aspek' => $title,
                                    'valid' => 'sesuai',
                                    'reliabel' => 'sesuai',
                                    'fleksibel' => 'sesuai',
                                    'adil' => 'sesuai',
                                    'catatan' => '',
                                ];
                            @endphp
                            <tr class="hover:bg-slate-50/70 transition-colors">
                                <td class="py-3 px-3 text-center text-slate-400 font-mono">{{ $no++ }}</td>
                                <td class="py-3 px-3 font-semibold text-slate-800">
                                    {{ $title }}
                                    <input type="hidden" name="prosedur_matrix[{{ $key }}][aspek]" value="{{ $title }}">
                                </td>

                                <!-- VALID -->
                                <td class="py-2 px-2 text-center">
                                    <select name="prosedur_matrix[{{ $key }}][valid]" class="w-full text-[11px] font-bold py-1 px-1.5 rounded-lg border border-slate-200 focus:ring-1 focus:ring-blue-500 bg-white" {{ !$canEdit ? 'disabled' : '' }}>
                                        <option value="sesuai" {{ ($row['valid'] ?? '') === 'sesuai' ? 'selected' : '' }}>Sesuai</option>
                                        <option value="tidak_sesuai" {{ ($row['valid'] ?? '') === 'tidak_sesuai' ? 'selected' : '' }}>Tidak Sesuai</option>
                                    </select>
                                </td>

                                <!-- RELIABEL -->
                                <td class="py-2 px-2 text-center">
                                    <select name="prosedur_matrix[{{ $key }}][reliabel]" class="w-full text-[11px] font-bold py-1 px-1.5 rounded-lg border border-slate-200 focus:ring-1 focus:ring-blue-500 bg-white" {{ !$canEdit ? 'disabled' : '' }}>
                                        <option value="sesuai" {{ ($row['reliabel'] ?? '') === 'sesuai' ? 'selected' : '' }}>Sesuai</option>
                                        <option value="tidak_sesuai" {{ ($row['reliabel'] ?? '') === 'tidak_sesuai' ? 'selected' : '' }}>Tidak Sesuai</option>
                                    </select>
                                </td>

                                <!-- FLEKSIBEL -->
                                <td class="py-2 px-2 text-center">
                                    <select name="prosedur_matrix[{{ $key }}][fleksibel]" class="w-full text-[11px] font-bold py-1 px-1.5 rounded-lg border border-slate-200 focus:ring-1 focus:ring-blue-500 bg-white" {{ !$canEdit ? 'disabled' : '' }}>
                                        <option value="sesuai" {{ ($row['fleksibel'] ?? '') === 'sesuai' ? 'selected' : '' }}>Sesuai</option>
                                        <option value="tidak_sesuai" {{ ($row['fleksibel'] ?? '') === 'tidak_sesuai' ? 'selected' : '' }}>Tidak Sesuai</option>
                                    </select>
                                </td>

                                <!-- ADIL -->
                                <td class="py-2 px-2 text-center">
                                    <select name="prosedur_matrix[{{ $key }}][adil]" class="w-full text-[11px] font-bold py-1 px-1.5 rounded-lg border border-slate-200 focus:ring-1 focus:ring-blue-500 bg-white" {{ !$canEdit ? 'disabled' : '' }}>
                                        <option value="sesuai" {{ ($row['adil'] ?? '') === 'sesuai' ? 'selected' : '' }}>Sesuai</option>
                                        <option value="tidak_sesuai" {{ ($row['adil'] ?? '') === 'tidak_sesuai' ? 'selected' : '' }}>Tidak Sesuai</option>
                                    </select>
                                </td>

                                <!-- CATATAN -->
                                <td class="py-2 px-3">
                                    <input type="text" name="prosedur_matrix[{{ $key }}][catatan]" value="{{ $row['catatan'] ?? '' }}" placeholder="Catatan kesesuaian / temuan khusus..." class="w-full text-xs py-1 px-2.5 rounded-lg border border-slate-200 focus:ring-1 focus:ring-blue-500 bg-white" {{ !$canEdit ? 'readonly' : '' }}>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>

        <!-- ================================================================= -->
        <!-- BAGIAN B: KONSISTENSI 5 DIMENSI KOMPETENSI -->
        <!-- ================================================================= -->
        <div class="bg-white rounded-2xl border border-slate-200/90 shadow-2xs overflow-hidden">
            <div class="px-4 sm:px-5 py-3.5 bg-slate-50/90 border-b border-slate-200 flex items-center justify-between">
                <div class="flex items-center gap-2">
                    <span class="w-6 h-6 rounded-lg bg-indigo-600 text-white font-bold text-xs flex items-center justify-center">B</span>
                    <h2 class="text-sm font-bold text-slate-900">Konsistensi Pemberian Bukti Asesmen (5 Dimensi Kompetensi)</h2>
                </div>
                <span class="text-[11px] text-slate-500 font-medium">Task, Management, Contingency, Environment, Transfer</span>
            </div>

            <div class="overflow-x-auto">
                <table class="w-full text-left text-xs border-collapse">
                    <thead>
                        <tr class="bg-slate-100/75 text-slate-700 font-bold border-b border-slate-200">
                            <th class="py-2.5 px-3 w-10 text-center">No</th>
                            <th class="py-2.5 px-3 min-w-[180px]">Dimensi Kompetensi</th>
                            <th class="py-2.5 px-3 min-w-[220px]">Bukti yang Digunakan</th>
                            <th class="py-2.5 px-3 min-w-[160px]">Instrumen Asesmen</th>
                            <th class="py-2.5 px-3 min-w-[220px]">Catatan / Konsistensi</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-200/80">
                        @php $noD = 1; @endphp
                        @foreach(App\Models\AssessmentAk06::COMPETENCY_DIMENSIONS as $dimKey => $dimTitle)
                            @php
                                $dimRow = $dimensiKompetensi[$dimKey] ?? [
                                    'dimensi' => $dimTitle,
                                    'bukti' => '',
                                    'instrumen' => '',
                                    'catatan' => '',
                                ];
                            @endphp
                            <tr class="hover:bg-slate-50/70 transition-colors">
                                <td class="py-3 px-3 text-center text-slate-400 font-mono">{{ $noD++ }}</td>
                                <td class="py-3 px-3 font-semibold text-slate-800">
                                    {{ $dimTitle }}
                                    <input type="hidden" name="dimensi_kompetensi[{{ $dimKey }}][dimensi]" value="{{ $dimTitle }}">
                                </td>
                                <td class="py-2 px-3">
                                    <input type="text" name="dimensi_kompetensi[{{ $dimKey }}][bukti]" value="{{ $dimRow['bukti'] ?? '' }}" placeholder="Contoh: Observasi unjuk kerja, portofolio..." class="w-full text-xs py-1 px-2.5 rounded-lg border border-slate-200 focus:ring-1 focus:ring-blue-500 bg-white" {{ !$canEdit ? 'readonly' : '' }}>
                                </td>
                                <td class="py-2 px-3">
                                    <input type="text" name="dimensi_kompetensi[{{ $dimKey }}][instrumen]" value="{{ $dimRow['instrumen'] ?? '' }}" placeholder="Contoh: FR.IA.01, FR.IA.02..." class="w-full text-xs py-1 px-2.5 rounded-lg border border-slate-200 focus:ring-1 focus:ring-blue-500 bg-white" {{ !$canEdit ? 'readonly' : '' }}>
                                </td>
                                <td class="py-2 px-3">
                                    <input type="text" name="dimensi_kompetensi[{{ $dimKey }}][catatan]" value="{{ $dimRow['catatan'] ?? '' }}" placeholder="Catatan konsistensi dimensi..." class="w-full text-xs py-1 px-2.5 rounded-lg border border-slate-200 focus:ring-1 focus:ring-blue-500 bg-white" {{ !$canEdit ? 'readonly' : '' }}>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>

        <!-- ================================================================= -->
        <!-- BAGIAN C: TEMUAN & REKOMENDASI PENINGKATAN (REPEATABLE TABLE) -->
        <!-- ================================================================= -->
        <div class="bg-white rounded-2xl border border-slate-200/90 shadow-2xs overflow-hidden">
            <div class="px-4 sm:px-5 py-3.5 bg-slate-50/90 border-b border-slate-200 flex items-center justify-between">
                <div class="flex items-center gap-2">
                    <span class="w-6 h-6 rounded-lg bg-emerald-600 text-white font-bold text-xs flex items-center justify-center">C</span>
                    <h2 class="text-sm font-bold text-slate-900">Temuan & Rekomendasi untuk Peningkatan</h2>
                </div>
                @if($canEdit)
                    <button type="button" @click="addRekomendasi()" class="inline-flex items-center gap-1 px-2.5 py-1 rounded-lg bg-emerald-50 text-emerald-700 hover:bg-emerald-100 font-bold text-xs transition-colors border border-emerald-200">
                        <span>Tambah Rekomendasi</span>
                    </button>
                @endif
            </div>

            <div class="overflow-x-auto">
                <table class="w-full text-left text-xs border-collapse">
                    <thead>
                        <tr class="bg-slate-100/75 text-slate-700 font-bold border-b border-slate-200">
                            <th class="py-2.5 px-3 w-10 text-center">No</th>
                            <th class="py-2.5 px-3 min-w-[200px]">Temuan / Gap Proses</th>
                            <th class="py-2.5 px-3 min-w-[220px]">Rekomendasi Tindakan</th>
                            <th class="py-2.5 px-3 min-w-[150px]">Penanggung Jawab</th>
                            <th class="py-2.5 px-3 w-36">Target Tanggal</th>
                            <th class="py-2.5 px-3 w-32">Status</th>
                            @if($canEdit)
                                <th class="py-2.5 px-3 w-12 text-center">Aksi</th>
                            @endif
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-200/80">
                        <template x-for="(item, index) in rekomendasiList" :key="index">
                            <tr class="hover:bg-slate-50/70 transition-colors">
                                <td class="py-3 px-3 text-center text-slate-400 font-mono" x-text="index + 1"></td>
                                <td class="py-2 px-3">
                                    <input type="text" :name="'rekomendasi_peningkatan[' + index + '][temuan]'" x-model="item.temuan" placeholder="Temuan gap asesmen..." class="w-full text-xs py-1 px-2.5 rounded-lg border border-slate-200 focus:ring-1 focus:ring-blue-500 bg-white" {{ !$canEdit ? 'readonly' : '' }}>
                                </td>
                                <td class="py-2 px-3">
                                    <input type="text" :name="'rekomendasi_peningkatan[' + index + '][rekomendasi]'" x-model="item.rekomendasi" placeholder="Rekomendasi perbaikan..." class="w-full text-xs py-1 px-2.5 rounded-lg border border-slate-200 focus:ring-1 focus:ring-blue-500 bg-white" {{ !$canEdit ? 'readonly' : '' }}>
                                </td>
                                <td class="py-2 px-3">
                                    <input type="text" :name="'rekomendasi_peningkatan[' + index + '][penanggung_jawab]'" x-model="item.penanggung_jawab" placeholder="Asesor / Ketua TUK / LSP..." class="w-full text-xs py-1 px-2.5 rounded-lg border border-slate-200 focus:ring-1 focus:ring-blue-500 bg-white" {{ !$canEdit ? 'readonly' : '' }}>
                                </td>
                                <td class="py-2 px-3">
                                    <input type="date" :name="'rekomendasi_peningkatan[' + index + '][target_tanggal]'" x-model="item.target_tanggal" class="w-full text-xs py-1 px-2 rounded-lg border border-slate-200 focus:ring-1 focus:ring-blue-500 bg-white" {{ !$canEdit ? 'readonly' : '' }}>
                                </td>
                                <td class="py-2 px-3">
                                    <select :name="'rekomendasi_peningkatan[' + index + '][status]'" x-model="item.status" class="w-full text-xs py-1 px-2 rounded-lg border border-slate-200 focus:ring-1 focus:ring-blue-500 bg-white font-semibold" {{ !$canEdit ? 'disabled' : '' }}>
                                        <option value="Belum Dimulai">Belum Dimulai</option>
                                        <option value="Berjalan">Berjalan</option>
                                        <option value="Selesai">Selesai</option>
                                        <option value="Ditunda">Ditunda</option>
                                    </select>
                                </td>
                                @if($canEdit)
                                    <td class="py-2 px-3 text-center">
                                        <button type="button" @click="removeRekomendasi(index)" class="text-rose-500 hover:text-rose-700 p-1 text-xs font-bold">
                                            Hapus
                                        </button>
                                    </td>
                                @endif
                            </tr>
                        </template>
                        <tr x-show="rekomendasiList.length === 0">
                            <td colspan="{{ $canEdit ? 7 : 6 }}" class="py-6 text-center text-xs text-slate-400">
                                Belum ada catatan rekomendasi peningkatan. Klik tombol "+ Tambah Rekomendasi" di atas.
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>

        <!-- ================================================================= -->
        <!-- BAGIAN D: KOMENTAR UMUM & TANDA TANGAN REVIEWER -->
        <!-- ================================================================= -->
        <div class="bg-white rounded-2xl border border-slate-200/90 shadow-2xs p-4 sm:p-5 space-y-4">
            <div>
                <label class="block text-xs font-bold text-slate-800 uppercase tracking-wider mb-1.5">
                    Komentar / Kesimpulan Umum Reviewer
                </label>
                <textarea name="komentar_reviewer" rows="3" placeholder="Tuliskan kesimpulan menyeluruh atas pelaksanaan dan penjaminan mutu asesmen ini..." class="w-full text-xs p-3 rounded-xl border border-slate-200 focus:ring-1 focus:ring-blue-500 focus:border-blue-500" {{ !$canEdit ? 'readonly' : '' }}>{{ $ak06->komentar_reviewer }}</textarea>
            </div>

            <!-- TANDA TANGAN REVIEWER -->
            <div class="border-t border-slate-200/80 pt-4 grid grid-cols-1 md:grid-cols-2 gap-4">
                <div class="space-y-2">
                    <span class="text-xs font-bold text-slate-800 uppercase tracking-wider block">
                        Tanda Tangan Reviewer / Lead Asesor
                    </span>
                    <p class="text-xs text-slate-500">
                        Dengan memfinalisasi, reviewer menyatakan bahwa peninjauan proses asesmen ini dilakukan secara independen, objektif, dan mengacu pada standar mutu BNSP.
                    </p>
                    <div class="text-xs font-semibold text-slate-700">
                        Nama Reviewer: <span class="font-bold text-slate-900">{{ $ak06->asesor->nama_lengkap ?? '-' }}</span>
                    </div>
                </div>

                <div>
                    @if($ak06->tanda_tangan_reviewer)
                        <div class="border border-slate-200 rounded-xl p-3 bg-slate-50/50 flex flex-col items-center justify-center">
                            <img src="{{ $ak06->tanda_tangan_reviewer }}" alt="Tanda Tangan Reviewer" class="max-h-24 object-contain">
                            <span class="text-[10px] text-slate-400 font-mono mt-1">Ditandatangani pada: {{ $ak06->tanggal_ttd_reviewer ? $ak06->tanggal_ttd_reviewer->format('d/m/Y H:i') : '-' }}</span>
                        </div>
                    @elseif($canEdit)
                        <div class="border border-slate-200 rounded-xl p-3 bg-slate-50/50 space-y-2">
                            <canvas id="canvas-reviewer" class="w-full h-24 border border-slate-200 bg-white rounded-lg"></canvas>
                            <div class="flex items-center justify-between">
                                <button type="button" @click="clearSignature()" class="text-[11px] text-slate-500 hover:text-slate-700 font-medium">
                                    Bersihkan
                                </button>
                                <span class="text-[10px] text-slate-400">Goreskan tanda tangan di kanvas</span>
                            </div>
                        </div>
                    @else
                        <div class="p-4 bg-slate-50 rounded-xl border border-dashed border-slate-200 text-center text-xs text-slate-400">
                            Belum dibubuhi tanda tangan.
                        </div>
                    @endif
                </div>
            </div>
        </div>

        <!-- BOTTOM BUTTONS -->
        @if($canEdit)
            <div class="flex items-center justify-end gap-3 pt-2">
                <button type="button" @click="submitSave(false)" class="px-4 py-2 rounded-xl border border-slate-200 bg-white text-slate-700 font-bold text-xs hover:bg-slate-50 transition-colors shadow-2xs">
                    <span>Simpan Draf Saja</span>
                </button>

                <button type="button" @click="openFinalizeModal()" class="px-5 py-2 rounded-xl bg-emerald-600 text-white font-bold text-xs hover:bg-emerald-700 transition-colors shadow-2xs flex items-center gap-1.5">
                    <span>Finalisasi & Kunci Peninjauan</span>
                </button>
            </div>
        @endif
    </form>

    <!-- AUDIT TRAIL LOG PREVIEW -->
    <div class="bg-white rounded-2xl border border-slate-200/90 shadow-2xs p-4 sm:p-5">
        <h3 class="text-xs font-bold text-slate-900 uppercase tracking-wider mb-3 flex items-center gap-2">
            <span>Riwayat Audit & Versi FR.AK.06</span>
        </h3>
        <div class="space-y-2">
            @forelse($ak06->auditLogs as $log)
                <div class="text-xs p-2.5 bg-slate-50 border border-slate-100 rounded-xl flex items-center justify-between">
                    <div>
                        <span class="font-bold text-slate-800">{{ $log->keterangan }}</span>
                        <span class="text-slate-400 text-[10px] block">Oleh {{ $log->user->nama_lengkap ?? 'Sistem' }} &bull; Versi v{{ $log->version }}</span>
                    </div>
                    <span class="text-[10px] text-slate-400 font-mono">{{ $log->created_at->format('d/m/Y H:i') }}</span>
                </div>
            @empty
                <p class="text-xs text-slate-400 italic">Belum ada riwayat perubahan.</p>
            @endforelse
        </div>
    </div>

    <!-- MODAL KONFIRMASI FINALISASI -->
    <div x-show="showFinalizeModal" class="fixed inset-0 z-50 overflow-y-auto" style="display: none;">
        <div class="fixed inset-0 bg-slate-900/50 backdrop-blur-xs transition-opacity" @click="showFinalizeModal = false"></div>
        <div class="flex min-h-full items-center justify-center p-4">
            <div class="relative transform overflow-hidden rounded-2xl bg-white p-5 text-left shadow-xl transition-all sm:w-full sm:max-w-md border border-slate-200">
                <div class="flex items-start gap-3">
                    <div class="w-10 h-10 rounded-xl bg-emerald-100 text-emerald-600 flex items-center justify-center shrink-0 font-bold text-xs">
                        KUNCI
                    </div>
                    <div>
                        <h3 class="text-sm font-bold text-slate-900">Finalisasi Review FR.AK.06?</h3>
                        <p class="text-xs text-slate-500 mt-1">
                            Setelah difinalisasi, dokumen peninjauan ini akan <strong>terkunci (read-only)</strong> dan tidak dapat diubah kembali kecuali oleh Admin / Superadmin melalui mekanisme revisi versi.
                        </p>
                    </div>
                </div>

                <div class="mt-5 flex items-center justify-end gap-2">
                    <button type="button" @click="showFinalizeModal = false" class="px-3 py-1.5 rounded-xl border border-slate-200 text-slate-600 text-xs font-semibold hover:bg-slate-50">
                        Batal
                    </button>
                    <button type="button" @click="submitSave(true)" class="px-4 py-1.5 rounded-xl bg-emerald-600 text-white text-xs font-bold hover:bg-emerald-700 shadow-2xs">
                        Ya, Finalisasi & Kunci
                    </button>
                </div>
            </div>
        </div>
    </div>

</div>

@push('js')
<script>
function ak06EditApp() {
    return {
        showFinalizeModal: false,
        rekomendasiList: @json($rekomendasiList),
        padReviewer: null,

        init() {
            this.$nextTick(() => {
                const canvas = document.getElementById('canvas-reviewer');
                if (canvas) {
                    this.padReviewer = new SignaturePad(canvas, {
                        backgroundColor: 'rgb(255, 255, 255)',
                        penColor: 'rgb(15, 23, 42)'
                    });
                }
            });
        },

        addRekomendasi() {
            this.rekomendasiList.push({
                temuan: '',
                rekomendasi: '',
                penanggung_jawab: '',
                target_tanggal: '{{ now()->addWeeks(2)->format('Y-m-d') }}',
                status: 'Belum Dimulai'
            });
        },

        removeRekomendasi(idx) {
            this.rekomendasiList.splice(idx, 1);
        },

        clearSignature() {
            if (this.padReviewer) {
                this.padReviewer.clear();
            }
        },

        openFinalizeModal() {
            this.showFinalizeModal = true;
        },

        submitSave(isFinalize) {
            document.getElementById('input-finalize').value = isFinalize ? '1' : '0';

            if (this.padReviewer && !this.padReviewer.isEmpty()) {
                document.getElementById('input-signature').value = this.padReviewer.toDataURL('image/png');
            }

            document.getElementById('form-ak06').submit();
        }
    };
}
</script>
@endpush
@endsection
