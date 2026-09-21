@extends('tata-letak.dasbor')

@section('judul', 'Penilaian Peserta Asesi')

@section('konten')
<div class="space-y-5" x-data="{ filterOpen: false }">

    <!-- =========================================================================
         1. PAGE HEADER & BREADCRUMB
         ========================================================================= -->
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3 pb-1">
        <div class="space-y-0.5">
            <div class="flex items-center gap-2 text-xs text-slate-500">
                <a href="{{ route('asesor.dashboard') }}" class="hover:text-indigo-600 font-medium">Dashboard</a>
                <span>/</span>
                <span class="text-slate-800 font-bold">Penilaian Peserta</span>
            </div>
            <h1 class="text-lg sm:text-xl font-bold text-slate-900 leading-tight">
                Penilaian & Verifikasi Peserta Asesi
            </h1>
            <p class="text-xs text-slate-500">
                Pemeriksaan bukti asesmen mandiri FR.APL.02, persetujuan FR.AK.01, dan pelaksanaan penilaian unjuk kerja.
            </p>
        </div>

        <div class="flex items-center gap-2 flex-wrap">
            <a href="{{ route('asesor.dashboard') }}" class="px-3.5 py-2 rounded-xl border border-slate-200 hover:bg-slate-50 text-slate-700 font-semibold text-xs transition-colors">
                Ke Dashboard
            </a>
            @php
                $activeJadwal = !empty($jadwalId) ? $jadwalOption->firstWhere('id', $jadwalId) : null;
                $activeSkemaId = $activeJadwal?->skema_id ?? auth()->user()->skema_id ?? $pesertaList->first()?->skema_id;
            @endphp
            @if($activeSkemaId)
                <a href="{{ route('asesor.skema.ak-07', $activeSkemaId) }}" class="px-3.5 py-2 rounded-xl border border-indigo-200 bg-indigo-50 hover:bg-indigo-100 text-indigo-700 font-bold text-xs transition-colors inline-flex items-center gap-1.5 shadow-2xs" title="Kelola Master Formulir Penyesuaian Asesmen (1 Form untuk Semua Asesi)">
                    <i class="fa-solid fa-file-pen text-indigo-600"></i>
                    <span>Master FR.AK.07</span>
                </a>
            @else
                <a href="{{ route('asesor.mapa') }}" class="px-3.5 py-2 rounded-xl border border-indigo-200 bg-indigo-50 hover:bg-indigo-100 text-indigo-700 font-bold text-xs transition-colors inline-flex items-center gap-1.5 shadow-2xs" title="Pusat Formulir Perencanaan (FR.MAPA, AK.01, AK.07)">
                    <i class="fa-solid fa-file-pen text-indigo-600"></i>
                    <span>Master FR.AK.07</span>
                </a>
            @endif
            <a href="{{ route('asesor.koreksi-teori', ['jadwal_id' => $jadwalId ?? '']) }}" class="px-3.5 py-2 rounded-xl bg-slate-50 hover:bg-slate-100 text-slate-700 font-semibold text-xs border border-slate-200 transition-colors">
            <a href="{{ route('asesor.koreksi-teori', ['jadwal_id' => $jadwalId ?? '']) }}" class="px-3.5 py-2 rounded-xl bg-indigo-50 hover:bg-indigo-100 text-indigo-700 font-bold text-xs border border-indigo-200 transition-colors">
                Koreksi Teori (IA.05 & 06)
            </a>
            <a href="{{ route('asesor.berita-acara') }}" class="px-3.5 py-2 rounded-xl border border-slate-200 hover:bg-slate-50 text-slate-700 font-semibold text-xs transition-colors">
                Berita Acara
            </a>
        </div>
    </div>



    <!-- =========================================================================
         3. SEARCH & FILTER TOOLBAR
         ========================================================================= -->
    <div class="bg-white rounded-2xl border border-slate-200/90 shadow-2xs p-3.5 sm:p-4 space-y-3">
        
        <form action="{{ route('asesor.penilaian') }}" method="GET" class="space-y-3">
            <div class="grid grid-cols-1 md:grid-cols-12 gap-2.5">
                
                <!-- Keyword Search Input -->
                <div class="md:col-span-5 relative">
                    <input type="text" 
                           name="q" 
                           value="{{ request('q') }}" 
                           placeholder="Cari nama asesi, no. pendaftaran, NIK..."
                           class="w-full bg-slate-50 border border-slate-200 rounded-xl px-3 py-2 text-xs text-slate-800 placeholder-slate-400 focus:bg-white focus:ring-1 focus:ring-indigo-500 transition-colors">
                </div>

                <!-- Filter Jadwal Selector -->
                <div class="md:col-span-5">
                    <select name="jadwal_id" 
                            class="w-full bg-slate-50 border border-slate-200 rounded-xl px-3 py-2 text-xs text-slate-800 font-medium focus:bg-white focus:ring-1 focus:ring-indigo-500 transition-colors">
                        <option value="">-- Semua Jadwal Penugasan --</option>
                        @foreach($jadwalOption as $j)
                            <option value="{{ $j->id }}" {{ $jadwalId == $j->id ? 'selected' : '' }}>
                                [{{ $j->kode_jadwal }}] {{ $j->skema->nama_skema ?? 'Skema' }} &bull; {{ date('d/m/Y', strtotime($j->tanggal_uji)) }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <!-- Submit & Reset Buttons -->
                <div class="md:col-span-2 flex items-center gap-1.5">
                    <button type="submit" class="flex-1 py-2 px-3 bg-indigo-600 hover:bg-indigo-700 text-white font-bold text-xs rounded-xl transition-colors cursor-pointer">
                        Filter
                    </button>
                    @if(request('q') || request('jadwal_id') || request('status'))
                        <a href="{{ route('asesor.penilaian') }}" class="py-2 px-3 border border-slate-200 hover:bg-slate-100 text-slate-600 font-semibold rounded-xl text-xs transition-colors" title="Reset Filter">
                            Reset
                        </a>
                    @endif
                </div>
            </div>

            <!-- Status Filter Pills -->
            <div class="flex items-center gap-1.5 overflow-x-auto pb-1 text-xs custom-scrollbar">
                <span class="text-[11px] font-bold text-slate-400 uppercase tracking-wider mr-1 shrink-0">Status:</span>
                
                <a href="{{ request()->fullUrlWithQuery(['status' => null, 'page' => 1]) }}" 
                   class="px-3 py-1 rounded-lg font-semibold shrink-0 transition-colors {{ empty($statusFilter) ? 'bg-indigo-600 text-white shadow-2xs' : 'bg-slate-100 text-slate-600 hover:bg-slate-200' }}">
                    Semua ({{ $countSemua }})
                </a>

                <a href="{{ request()->fullUrlWithQuery(['status' => 'pending_apl02', 'page' => 1]) }}" 
                   class="px-3 py-1 rounded-lg font-semibold shrink-0 transition-colors {{ $statusFilter === 'pending_apl02' ? 'bg-amber-600 text-white shadow-2xs' : 'bg-amber-50 text-amber-700 hover:bg-amber-100 border border-amber-200/60' }}">
                    Menunggu APL.02 ({{ $countPendingApl02 }})
                </a>

                <a href="{{ request()->fullUrlWithQuery(['status' => 'revisi_apl02', 'page' => 1]) }}" 
                   class="px-3 py-1 rounded-lg font-semibold shrink-0 transition-colors {{ $statusFilter === 'revisi_apl02' ? 'bg-rose-600 text-white shadow-2xs' : 'bg-rose-50 text-rose-700 hover:bg-rose-100 border border-rose-200/60' }}">
                    Perlu Revisi
                </a>

                <a href="{{ request()->fullUrlWithQuery(['status' => 'approved_apl02', 'page' => 1]) }}" 
                   class="px-3 py-1 rounded-lg font-semibold shrink-0 transition-colors {{ $statusFilter === 'approved_apl02' ? 'bg-emerald-600 text-white shadow-2xs' : 'bg-emerald-50 text-emerald-700 hover:bg-emerald-100 border border-emerald-200/60' }}">
                    APL.02 Disetujui
                </a>

                <a href="{{ request()->fullUrlWithQuery(['status' => 'selesai', 'page' => 1]) }}" 
                   class="px-3 py-1 rounded-lg font-semibold shrink-0 transition-colors {{ $statusFilter === 'selesai' ? 'bg-slate-800 text-white shadow-2xs' : 'bg-slate-100 text-slate-700 hover:bg-slate-200' }}">
                    Selesai Dinilai
                </a>
            </div>
        </form>

    </div>

    <!-- =========================================================================
         4. CANDIDATES TABLE (ENTERPRISE GRID)
         ========================================================================= -->
    <div class="bg-white rounded-2xl border border-slate-200/90 shadow-2xs overflow-hidden">
        <div class="px-4 py-3 bg-slate-50/80 border-b border-slate-200/80 flex items-center justify-between">
            <div class="flex items-center gap-2">
                <span class="font-bold text-xs sm:text-sm text-slate-800">Daftar Kandidat Asesi</span>
                <span class="text-xs text-slate-400">({{ $pesertaList->total() }} Total Peserta)</span>
            </div>
            <span class="text-[11px] text-slate-500 font-medium">
                Menampilkan {{ $pesertaList->firstItem() ?? 0 }}-{{ $pesertaList->lastItem() ?? 0 }} dari {{ $pesertaList->total() }}
            </span>
        </div>

        <div class="overflow-x-auto">
            <table class="w-full text-left border-collapse text-xs">
                <thead>
                    <tr class="bg-slate-50/60 border-b border-slate-200 text-[11px] font-bold text-slate-500 uppercase tracking-wider">
                        <th class="py-2.5 px-3 w-10 text-center">No</th>
                        <th class="py-2.5 px-4 min-w-[200px]">Data Asesi</th>
                        <th class="py-2.5 px-4 min-w-[180px]">Skema & Jadwal</th>
                        <th class="py-2.5 px-4 w-44">Status Formulir</th>
                        <th class="py-2.5 px-4 w-36 text-center">Hasil Akhir</th>
                        <th class="py-2.5 px-4 min-w-[220px] text-right">Aksi Penilaian</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @forelse($pesertaList as $idx => $p)
                        @php
                            $isWaitingApl02 = in_array($p->status_apl02, ['submitted', 'under_review']);
                            $isRevisionApl02 = in_array($p->status_apl02, ['revision_requested', 'revision']);
                            $isWaitingAk01 = false;
                        @endphp
                        <tr class="hover:bg-slate-50/60 transition-colors {{ $isWaitingApl02 ? 'bg-indigo-50/20' : '' }}">
                            
                            <!-- 1. No -->
                            <td class="py-3 px-3 text-center text-slate-400 font-medium">
                                {{ $pesertaList->firstItem() + $idx }}
                            </td>

                            <!-- 2. Data Asesi -->
                            <td class="py-3 px-4">
                                <div class="flex items-start gap-2.5">
                                    <div class="w-8 h-8 rounded-lg bg-slate-100 text-slate-700 flex items-center justify-center font-bold text-xs shrink-0 mt-0.5 border border-slate-200/70">
                                        {{ strtoupper(substr($p->asesi->nama_lengkap ?? 'U', 0, 2)) }}
                                    </div>
                                    <div class="space-y-0.5 min-w-0">
                                        <div class="font-bold text-slate-900 truncate" title="{{ $p->asesi->nama_lengkap }}">
                                            {{ $p->asesi->nama_lengkap }}
                                        </div>
                                        <div class="text-[11px] text-slate-500 font-mono">
                                            {{ $p->nomor_pendaftaran }}
                                        </div>
                                        <div class="text-[10px] text-slate-400 truncate max-w-[190px]" title="{{ $p->asesi->profilAsesi->nama_sekolah_instansi ?? '-' }}">
                                            {{ $p->asesi->profilAsesi->nama_sekolah_instansi ?? 'SMKN 1 Gunungputri' }}
                                        </div>
                                    </div>
                                </div>
                            </td>

                            <!-- 3. Skema & Jadwal -->
                            <td class="py-3 px-4 space-y-1">
                                <div class="font-semibold text-slate-800 truncate max-w-[190px]" title="{{ $p->skema->nama_skema ?? '-' }}">
                                    {{ $p->skema->nama_skema ?? '-' }}
                                </div>
                                <div class="text-[11px] text-slate-500 flex items-center gap-1.5">
                                    <span>{{ $p->jadwal ? date('d/m/Y', strtotime($p->jadwal->tanggal_uji)) : 'Sesuai Jadwal' }}</span>
                                </div>
                                <div class="text-[10px] text-slate-400 truncate max-w-[180px]">
                                    TUK: {{ $p->jadwal->nama_tuk ?? 'TUK Sewaktu' }}
                                </div>
                            </td>

                            <!-- 4. Status Formulir (APL.02 & AK.01) -->
                            <td class="py-3 px-4 space-y-1.5">
                                <!-- APL.02 Badge -->
                                <div>
                                    @if($p->isApl02Approved())
                                        <a href="{{ route('asesor.input-penilaian', $p->id) }}" class="inline-flex items-center px-2 py-0.5 rounded-md bg-emerald-50 text-emerald-700 border border-emerald-200 text-[10px] font-bold hover:bg-emerald-100 transition-colors" title="Lihat Berkas FR.APL.02">
                                            APL.02 ACC
                                        </a>
                                    @elseif($p->status_apl02 === 'submitted' || $p->status_apl02 === 'under_review')
                                        <a href="{{ route('asesor.input-penilaian', $p->id) }}" class="inline-flex items-center px-2 py-0.5 rounded-md bg-amber-50 text-amber-700 border border-amber-200 text-[10px] font-bold hover:bg-amber-100 transition-colors animate-pulse" title="Verifikasi Portofolio Asesi">
                                            APL.02 Menunggu
                                        </a>
                                    @elseif(in_array($p->status_apl02, ['revision_requested', 'revision']))
                                        <a href="{{ route('asesor.input-penilaian', $p->id) }}" class="inline-flex items-center px-2 py-0.5 rounded-md bg-rose-50 text-rose-700 border border-rose-200 text-[10px] font-bold hover:bg-rose-100 transition-colors" title="Tinjau Catatan Revisi APL.02">
                                            APL.02 Revisi
                                        </a>
                                    @else
                                        <span class="inline-flex items-center px-2 py-0.5 rounded-md bg-slate-100 text-slate-500 text-[10px] font-medium">
                                            Draft Asesi
                                        </span>
                                    @endif
                                </div>

                                <!-- AK.01 Badge -->
                                <div>
                                    @if($p->status_ak01 === 'disetujui_asesor' || $p->status_ak01 === 'selesai' || !empty($p->tanda_tangan_asesor_ak01) || !empty($p->tanda_tangan_asesi_ak01))
                                        <span class="inline-flex items-center px-2 py-0.5 rounded-md bg-emerald-50 text-emerald-700 border border-emerald-200 text-[10px] font-bold">
                                            AK.01 Selesai
                                        </span>
                                    @elseif($p->isApl02Approved())
                                        <span class="inline-flex items-center px-2 py-0.5 rounded-md bg-slate-100 text-slate-600 text-[10px]">
                                            Menunggu Asesi TTD
                                        </span>
                                    @else
                                        <span class="inline-flex items-center px-2 py-0.5 rounded-md bg-slate-100 text-slate-400 text-[10px]">
                                            AK.01 Terkunci
                                        </span>
                                    @endif
                                </div>

                                <!-- AK.07 Status Badge -->
                                <div>
                                    @php $ak07P = $p->ak07Adjustment; @endphp
                                    <a href="{{ route('asesor.pendaftaran.ak07.edit', $p->id) }}" class="inline-block group" title="Buka Formulir FR.AK.07 Peserta">
                                    @if($ak07P && $ak07P->isConfirmed())
                                        <span class="inline-flex items-center px-2 py-0.5 rounded-md bg-emerald-50 text-emerald-700 border border-emerald-200 text-[10px] font-bold" title="FR.AK.07 Disetujui & Sah oleh Asesi">
                                            AK.07 Sah
                                        <span class="inline-flex items-center px-2 py-0.5 rounded-md bg-emerald-50 text-emerald-700 border border-emerald-200 text-[10px] font-bold group-hover:bg-emerald-100 transition-colors" title="FR.AK.07 Disetujui & Sah oleh Asesi">
                                            FR.AK.07 Sah
                                        </span>
                                    @elseif($ak07P && !empty($ak07P->asesor_signature))
                                        <span class="inline-flex items-center px-2 py-0.5 rounded-md bg-blue-50 text-blue-700 border border-blue-200 text-[10px] font-bold" title="FR.AK.07 Diselaraskan Master, Menunggu TTD Asesi">
                                            AK.07 Menunggu Asesi
                                        <span class="inline-flex items-center px-2 py-0.5 rounded-md bg-blue-50 text-blue-700 border border-blue-200 text-[10px] font-bold group-hover:bg-blue-100 transition-colors" title="FR.AK.07 Diselaraskan Master, Menunggu TTD Asesi">
                                            FR.AK.07 Menunggu Asesi
                                        </span>
                                    @elseif($ak07P)
                                        <span class="inline-flex items-center px-2 py-0.5 rounded-md bg-amber-50 text-amber-700 border border-amber-200 text-[10px] font-bold" title="FR.AK.07 Draf">
                                            AK.07 Draf
                                        <span class="inline-flex items-center px-2 py-0.5 rounded-md bg-amber-50 text-amber-700 border border-amber-200 text-[10px] font-bold group-hover:bg-amber-100 transition-colors" title="FR.AK.07 Draf">
                                            FR.AK.07 Draf
                                        </span>
                                    @else
                                        <span class="inline-flex items-center px-2 py-0.5 rounded-md bg-slate-100 text-slate-500 border border-slate-200 text-[10px] font-medium" title="Belum Ada Penyesuaian Asesmen">
                                            AK.07
                                        <span class="inline-flex items-center px-2 py-0.5 rounded-md bg-slate-100 text-slate-500 border border-slate-200 text-[10px] font-medium group-hover:bg-slate-200 transition-colors" title="Belum Ada Penyesuaian Asesmen">
                                            FR.AK.07
                                        </span>
                                    @endif
                                    </a>
                                </div>

                                <!-- Assessment Live Badge -->
                                @if($p->isRuangUjiOpen() && empty($p->rekomendasi))
                                    <div>
                                        <span class="inline-flex items-center gap-1.5 px-2 py-0.5 rounded-full bg-emerald-100 text-emerald-800 border border-emerald-300 text-[10px] font-extrabold shadow-2xs animate-pulse">
                                            <span class="w-1.5 h-1.5 rounded-full bg-emerald-500 animate-ping"></span>
                                            <span>Ujian Dimulai</span>
                                        </span>
                                    </div>
                                @endif
                            </td>

                            <!-- 5. Keputusan Rekomendasi -->
                            <td class="py-3 px-4 text-center">
                                @if($p->rekomendasi)
                                    @php $kep = strtolower($p->rekomendasi->keputusan ?? ''); @endphp
                                    @if(str_contains($kep, 'belum') || str_contains($kep, 'bk'))
                                        <span class="inline-flex items-center px-2 py-1 rounded-full bg-rose-50 text-rose-700 border border-rose-200 font-extrabold text-[10px]">
                                            BK (Belum Kompeten)
                                        </span>
                                    @else
                                        <span class="inline-flex items-center px-2 py-1 rounded-full bg-emerald-50 text-emerald-700 border border-emerald-200 font-extrabold text-[10px]">
                                            K (Kompeten)
                                        </span>
                                    @endif
                                @else
                                    <span class="inline-flex items-center px-2 py-1 rounded-full bg-slate-100 text-slate-600 text-[10px] font-medium">
                                        Belum Dinilai
                                    </span>
                                @endif
                            </td>

                            <!-- 6. Aksi Asesor -->
                            <td class="py-3 px-4 text-right whitespace-nowrap">
                                <div class="inline-flex items-center gap-1.5 justify-end flex-wrap">
                                    <!-- Tombol Tunggal Uji -->
                                    @php
                                        $jadwalP = $p->jadwal;
                                        $isBelumMulai = false;
                                        $isDibatalkan = false;
                                        $pesanPopup = '';

                                        if ($jadwalP) {
                                            $jadwalP->syncRealtimeStatus();
                                            if ($jadwalP->status_jadwal === 'dibatalkan') {
                                                $isDibatalkan = true;
                                                $pesanPopup = 'Jadwal asesmen ini telah dibatalkan oleh LSP.';
                                            } elseif ($jadwalP->status_jadwal === 'terjadwal' || $jadwalP->isBelumMulai()) {
                                                $isBelumMulai = true;
                                                $tglUji = $jadwalP->tanggal_uji ? \Carbon\Carbon::parse($jadwalP->tanggal_uji)->translatedFormat('d F Y') : '-';
                                                $jamUji = $jadwalP->waktu_mulai ? substr($jadwalP->waktu_mulai, 0, 5) . ' WIB' : '-';
                                                $tukUji = $jadwalP->nama_tuk ?? 'TUK';
                                                $pesanPopup = "Ujian belum dimulai. Sesi asesmen dijadwalkan pada {$tglUji} pukul {$jamUji} di {$tukUji}.";
                                            }
                                        }
                                    @endphp

                                    @if($isDibatalkan)
                                        <button type="button" 
                                                onclick="bukaPopupUjianBelumMulai('{{ addslashes($pesanPopup) }}', 'Jadwal Dibatalkan')" 
                                                class="px-4 py-1.5 rounded-lg bg-rose-600 hover:bg-rose-700 text-white font-bold text-xs shadow-2xs transition-colors inline-flex items-center cursor-pointer"
                                                title="Jadwal Dibatalkan">
                                            Uji
                                        </button>
                                    @elseif($isBelumMulai)
                                        <button type="button" 
                                                onclick="bukaPopupUjianBelumMulai('{{ addslashes($pesanPopup) }}', 'Ujian Belum Dimulai')" 
                                                class="px-4 py-1.5 rounded-lg bg-emerald-600 hover:bg-emerald-700 text-white font-bold text-xs shadow-2xs transition-colors inline-flex items-center cursor-pointer"
                                                title="Ujian Belum Dimulai">
                                            Uji
                                        </button>
                                    @else
                                        <a href="{{ route('asesor.penilaian-live', $p->id) }}" 
                                            class="px-4 py-1.5 rounded-lg {{ ($p->isRuangUjiOpen() && empty($p->rekomendasi)) ? 'bg-emerald-600 hover:bg-emerald-700 ring-2 ring-emerald-400 ring-offset-1 text-white animate-pulse font-extrabold' : 'bg-emerald-600 hover:bg-emerald-700 text-white font-bold' }} text-xs shadow-2xs transition-colors inline-flex items-center">
                                            Uji
                                        </a>
                                    @endif
                                </div>
                            </td>

                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="py-12 text-center space-y-2">
                                <h3 class="font-bold text-xs sm:text-sm text-slate-800">Tidak Ada Peserta Ditemukan</h3>
                                <p class="text-xs text-slate-500 max-w-sm mx-auto">
                                    Tidak ada kandidat asesi yang sesuai dengan filter atau kata kunci pencarian Anda.
                                </p>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <!-- PAGINATION -->
        @if($pesertaList->hasPages())
            <div class="px-4 py-3 bg-slate-50/60 border-t border-slate-200/80">
                {{ $pesertaList->links() }}
            </div>
        @endif
    </div>

</div>
@endsection

@push('js')
<script>
function bukaPopupUjianBelumMulai(pesan, judul = 'Ujian Belum Dimulai') {
    if (typeof Swal !== 'undefined') {
        Swal.fire({
            icon: 'warning',
            title: judul,
            text: pesan || 'Ujian belum dimulai. Sesi asesmen belum dapat diakses sebelum waktu yang dijadwalkan.',
            confirmButtonColor: '#4f46e5',
            confirmButtonText: 'Mengerti',
            customClass: {
                popup: 'swal2-modern-popup'
            }
        });
    } else {
        alert(judul + ':\n\n' + (pesan || 'Sesi asesmen belum dapat diakses sebelum waktu yang dijadwalkan.'));
    }
}
</script>
@endpush
