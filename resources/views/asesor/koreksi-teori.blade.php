@extends('tata-letak.dasbor')

@section('judul', 'Pemeriksaan Jawaban Ujian Teori Asesi (FR.IA.05 & IA.06)')

@push('css')
    <style>
        .custom-radio-k:checked + label {
            background-color: #ecfdf5;
            border-color: #10b981;
            color: #047857;
            font-weight: 700;
        }
        .custom-radio-bk:checked + label {
            background-color: #fef2f2;
            border-color: #ef4444;
            color: #b91c1c;
            font-weight: 700;
        }
        .no-scrollbar::-webkit-scrollbar {
            display: none;
        }
        .no-scrollbar {
            -ms-overflow-style: none;
            scrollbar-width: none;
        }
    </style>
@endpush

@section('konten')
<div class="space-y-6 pb-20" x-data="{
    searchQuery: '',
    selectedAsesiId: '{{ request('pendaftaran_id') ?: '' }}',
    modalOpen: {{ request('pendaftaran_id') ? 'true' : 'false' }},
    
    bukaModalAsesi(id) {
        if (!id) return;
        this.selectedAsesiId = id.toString();
        this.modalOpen = true;
    },
    tutupModal() {
        this.modalOpen = false;
    }
}">

    <!-- =========================================================================
         1. PAGE HEADER & BREADCRUMB
         ========================================================================= -->
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 pb-1">
        <div class="space-y-1">
            <div class="flex items-center gap-2 text-xs text-slate-500">
                <a href="{{ route('asesor.dashboard') }}" class="hover:text-indigo-600 font-medium">Dashboard</a>
                <span>/</span>
                <a href="{{ route('asesor.daftar-peserta') }}" class="hover:text-indigo-600 font-medium">Penilaian Peserta</a>
                <span>/</span>
                <span class="text-slate-800 font-bold">Jawaban Ujian Teori (FR.IA.05 & IA.06)</span>
            </div>
            <h1 class="text-xl sm:text-2xl font-bold text-slate-900 tracking-tight">
                Pemeriksaan Jawaban Ujian Teori Asesi (FR.IA.05 & FR.IA.06)
            </h1>
            <p class="text-xs text-slate-500">
                Halaman terpisah khusus untuk memilih akun asesi dan memeriksa rincian jawaban CBT pilihan ganda serta koreksi esai tanpa membuka menu observasi praktik asesi.
            </p>
        </div>

        <div class="flex items-center gap-2 flex-wrap">
            <a href="{{ (url()->previous() && url()->previous() !== url()->current()) ? url()->previous() : route('asesor.daftar-peserta', ['jadwal_id' => $activeJadwal->id ?? '']) }}" 
               onclick="if (document.referrer && document.referrer !== window.location.href) { window.location.href = document.referrer; return false; } else if (window.history.length > 1) { window.history.back(); return false; }"
               class="px-3.5 py-2 rounded-xl border border-slate-200 bg-white hover:bg-slate-50 text-slate-700 font-semibold text-xs transition-colors cursor-pointer">
                &larr; Kembali
            </a>
            @if($activeJadwal)
                <a href="{{ route('asesor.koreksi-teori', ['jadwal_id' => $activeJadwal->id]) }}" 
                   class="px-3.5 py-2 rounded-xl bg-indigo-50 hover:bg-indigo-100 text-indigo-700 font-bold text-xs border border-indigo-200 transition-colors">
                    Segarkan Data
                </a>
            @endif
        </div>
    </div>

    <!-- =========================================================================
         2. SELECTOR JADWAL, PILIHAN AKUN ASESI & METRIK
         ========================================================================= -->
    <div class="bg-white rounded-2xl border border-slate-200/90 shadow-2xs p-5 space-y-4">
        <div class="grid grid-cols-1 md:grid-cols-12 gap-4 border-b border-slate-100 pb-4">
            <!-- 1. Jadwal Uji Selector -->
            <div class="md:col-span-6 space-y-1">
                <label class="text-xs font-bold text-slate-600 uppercase tracking-wider block">Pilih Jadwal Uji:</label>
                <select onchange="window.location.href='{{ route('asesor.koreksi-teori') }}?jadwal_id=' + this.value"
                        class="w-full bg-slate-50 border border-slate-200 rounded-xl px-3.5 py-2.5 text-xs font-bold text-slate-800 focus:bg-white focus:ring-2 focus:ring-indigo-500 transition-colors">
                    @forelse($jadwalOptions as $j)
                        <option value="{{ $j->id }}" {{ ($activeJadwal && $activeJadwal->id == $j->id) ? 'selected' : '' }}>
                            [{{ $j->kode_jadwal }}] {{ $j->skema->nama_skema ?? 'Skema' }} &bull; {{ date('d/m/Y', strtotime($j->tanggal_uji)) }}
                        </option>
                    @empty
                        <option value="">Belum Ada Jadwal Penugasan</option>
                    @endforelse
                </select>
            </div>

            <!-- 2. Pilihan Akun Asesi (Langsung Lihat Jawaban) -->
            <div class="md:col-span-6 space-y-1">
                <label class="text-xs font-bold text-slate-600 uppercase tracking-wider block">Pilih Akun Asesi:</label>
                <div class="flex items-center gap-2">
                    <select x-model="selectedAsesiId"
                            class="flex-1 bg-slate-50 border border-slate-200 rounded-xl px-3.5 py-2.5 text-xs font-bold text-indigo-900 focus:bg-white focus:ring-2 focus:ring-indigo-500 transition-colors">
                        <option value="">-- Pilih Akun Asesi --</option>
                        @foreach($pesertaData as $pData)
                            @php $p = $pData['pendaftaran']; @endphp
                            <option value="{{ $p->id }}">
                                {{ $p->asesi->nama_lengkap }} ({{ $p->nomor_pendaftaran }}) &bull; CBT: {{ $pData['skor_cbt'] !== null ? $pData['skor_cbt'] . '%' : 'Belum' }}
                            </option>
                        @endforeach
                    </select>
                    <button type="button" 
                            @click="if(selectedAsesiId) { bukaModalAsesi(selectedAsesiId); } else { alert('Silakan pilih salah satu akun asesi terlebih dahulu.'); }"
                            class="px-4 py-2.5 rounded-xl bg-indigo-600 hover:bg-indigo-700 text-white font-bold text-xs shadow-xs transition-colors shrink-0 cursor-pointer flex items-center gap-1.5">
                        <span>Lihat Jawaban Asesi &rarr;</span>
                    </button>
                </div>
            </div>
        </div>

        @if($activeJadwal)
            <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3 text-xs text-slate-600">
                <div class="flex items-center gap-2 flex-wrap">
                    <span class="px-2.5 py-1 rounded-md font-mono font-bold bg-slate-100 text-slate-700 border border-slate-200">
                        {{ $activeJadwal->skema->kode_skema ?? '-' }}
                    </span>
                    <span><strong>{{ $activeJadwal->skema->nama_skema ?? '-' }}</strong></span>
                    <span>&bull;</span>
                    <span>TUK: <strong>{{ $activeJadwal->nama_tuk ?? 'TUK LSP' }}</strong></span>
                </div>

                <div class="flex items-center gap-2 flex-wrap">
                    <span class="px-2.5 py-1 rounded-lg bg-indigo-50 text-indigo-700 font-bold border border-indigo-100 text-xs">
                        Total: {{ count($pesertaData) }} Asesi
                    </span>
                    @if(!empty($stats['esai_perlu_dinilai']) && $stats['esai_perlu_dinilai'] > 0)
                        <span class="px-2.5 py-1 rounded-lg bg-amber-50 text-amber-700 font-bold border border-amber-200 text-xs flex items-center gap-1">
                            <span class="w-1.5 h-1.5 rounded-full bg-amber-500 animate-pulse"></span>
                            {{ $stats['esai_perlu_dinilai'] }} Perlu Koreksi Esai
                        </span>
                    @endif
                </div>
            </div>
        @endif
    </div>

    @if(!$activeJadwal)
        <div class="bg-white rounded-2xl border border-slate-200 p-12 text-center space-y-2">
            <h3 class="font-bold text-sm text-slate-800">Tidak Ada Jadwal Dipilih</h3>
            <p class="text-xs text-slate-500">Silakan pilih jadwal penugasan asesmen untuk melihat akun asesi dan lembar jawabannya.</p>
        </div>
    @elseif(empty($pesertaData))
        <div class="bg-white rounded-2xl border border-slate-200 p-12 text-center space-y-2">
            <h3 class="font-bold text-sm text-slate-800">Belum Ada Peserta Terdaftar</h3>
            <p class="text-xs text-slate-500">Tidak ada peserta yang terdaftar pada jadwal asesmen yang dipilih.</p>
        </div>
    @else

        <!-- =========================================================================
             3. TAMPILAN TABEL REKAP & PEMERIKSAAN JAWABAN
             ========================================================================= -->
        <div class="bg-white rounded-2xl border border-slate-200/90 shadow-2xs overflow-hidden">
            <div class="px-5 py-4 border-b border-slate-200 flex flex-col sm:flex-row sm:items-center justify-between gap-3 bg-slate-50/50">
                <div>
                    <h3 class="text-sm font-bold text-slate-900">Daftar Akun Asesi & Pemeriksaan Jawaban Teori</h3>
                    <p class="text-xs text-slate-500">Klik tombol <strong>Periksa Jawaban</strong> untuk melihat rincian CBT dan melakukan koreksi esai.</p>
                </div>
                <div class="w-full sm:w-64">
                    <input type="text" 
                           x-model="searchQuery" 
                           placeholder="Cari nama / no. reg asesi..." 
                           class="w-full bg-white border border-slate-200 rounded-xl px-3.5 py-2 text-xs text-slate-800 focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 transition-colors shadow-2xs">
                </div>
            </div>

            <div class="overflow-x-auto">
                <table class="w-full text-left text-xs text-slate-700">
                    <thead class="bg-slate-50 text-slate-600 font-bold border-b border-slate-200 uppercase tracking-wider text-[11px]">
                        <tr>
                            <th class="py-3 px-4 w-12 text-center">No</th>
                            <th class="py-3 px-4">Identitas Asesi</th>
                            <th class="py-3 px-4 text-center">CBT (FR.IA.05)</th>
                            <th class="py-3 px-4 text-center">Esai (FR.IA.06)</th>
                            <th class="py-3 px-4 text-center">Status Koreksi</th>
                            <th class="py-3 px-4 text-center w-56">Aksi</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100">
                        @foreach($pesertaData as $idx => $pData)
                            @php
                                $p = $pData['pendaftaran'];
                                $skorCbt = $pData['skor_cbt'];
                                $statusCbt = $pData['status_cbt'];
                                $statusEsai = $pData['status_esai'];
                                $isGraded = $pData['is_esai_graded'];
                            @endphp
                            <tr class="hover:bg-slate-50/80 transition-colors"
                                x-show="!searchQuery || '{{ strtolower(addslashes($p->asesi->nama_lengkap)) }} {{ strtolower($p->nomor_pendaftaran) }} {{ strtolower(addslashes($p->asesi->profilAsesi->nama_sekolah_instansi ?? '')) }}'.includes(searchQuery.toLowerCase().trim())">
                                <td class="py-3.5 px-4 text-center font-bold text-slate-400">{{ $idx + 1 }}</td>
                                <td class="py-3.5 px-4">
                                    <div class="flex items-center gap-3">
                                        <div class="w-8 h-8 rounded-lg bg-indigo-50 border border-indigo-100 text-indigo-700 font-bold text-xs flex items-center justify-center shrink-0">
                                            {{ strtoupper(substr($p->asesi->nama_lengkap, 0, 2)) }}
                                        </div>
                                        <div class="space-y-0.5">
                                            <div class="font-bold text-slate-900 text-sm leading-snug">{{ $p->asesi->nama_lengkap }}</div>
                                            <div class="flex items-center gap-2 text-[11px] text-slate-500">
                                                <span class="font-mono bg-slate-100 px-1.5 py-0.5 rounded border border-slate-200">{{ $p->nomor_pendaftaran }}</span>
                                                <span>&bull;</span>
                                                <span class="truncate max-w-[180px]">{{ $p->asesi->profilAsesi->nama_sekolah_instansi ?? 'Peserta Sertifikasi' }}</span>
                                            </div>
                                        </div>
                                    </div>
                                </td>
                                <td class="py-3.5 px-4 text-center">
                                    @if($skorCbt !== null)
                                        <span class="font-extrabold px-2.5 py-1 rounded-full text-xs {{ $skorCbt >= 75 ? 'bg-emerald-50 text-emerald-800 border border-emerald-200' : 'bg-rose-50 text-rose-800 border border-rose-200' }}">
                                            {{ $skorCbt }}%
                                        </span>
                                    @elseif($statusCbt === 'draft')
                                        <span class="font-bold text-amber-700 bg-amber-50 border border-amber-200 px-2 py-0.5 rounded-full text-[11px]">
                                            Draft Jawaban
                                        </span>
                                    @else
                                        <span class="text-slate-400 italic">Belum Mengisi</span>
                                    @endif
                                </td>
                                <td class="py-3.5 px-4 text-center">
                                    @if($statusEsai === 'terkumpul')
                                        <span class="px-2.5 py-0.5 rounded-full bg-emerald-50 text-emerald-700 border border-emerald-200 font-bold text-[11px]">
                                            Terkumpul
                                        </span>
                                    @elseif($statusEsai === 'draft')
                                        <span class="px-2.5 py-0.5 rounded-full bg-amber-50 text-amber-700 border border-amber-200 font-bold text-[11px]">
                                            Draft Jawaban
                                        </span>
                                    @else
                                        <span class="text-slate-400 italic">Belum Ada</span>
                                    @endif
                                </td>
                                <td class="py-3.5 px-4 text-center">
                                    @if($isGraded)
                                        <span class="px-2.5 py-1 rounded-full bg-emerald-100 text-emerald-800 border border-emerald-300 font-bold text-[11px] inline-flex items-center gap-1">
                                            <svg class="w-3 h-3 text-emerald-600" fill="currentColor" viewBox="0 0 20 20">
                                                <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"></path>
                                            </svg>
                                            Sudah Dikoreksi
                                        </span>
                                    @elseif($statusEsai === 'terkumpul')
                                        <span class="px-2.5 py-1 rounded-full bg-amber-100 text-amber-800 border border-amber-300 font-bold text-[11px] inline-flex items-center gap-1">
                                            <span class="w-1.5 h-1.5 rounded-full bg-amber-500 animate-pulse"></span>
                                            Perlu Dikoreksi
                                        </span>
                                    @else
                                        <span class="text-slate-400 text-[11px]">-</span>
                                    @endif
                                </td>
                                <td class="py-3.5 px-4">
                                    <div class="flex items-center justify-center gap-2">
                                        <button type="button" 
                                                @click="bukaModalAsesi({{ $p->id }})" 
                                                class="px-3.5 py-1.5 rounded-xl bg-indigo-600 hover:bg-indigo-700 text-white font-bold text-xs shadow-xs transition-colors cursor-pointer flex items-center gap-1.5 shrink-0">
                                            <span>Periksa Jawaban &rarr;</span>
                                        </button>
                                        <a href="{{ route('asesor.penilaian-live', $p->id) }}" 
                                           title="Buka Lembar Observasi Praktik Asesi Ini"
                                           class="px-2.5 py-1.5 rounded-xl border border-slate-200 bg-white hover:bg-slate-50 text-slate-600 font-semibold text-xs transition-colors shrink-0">
                                            Observasi
                                        </a>
                                    </div>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>

    @endif

    <!-- =========================================================================
         5. MODAL INTERAKTIF: LEMBAR DETAIL JAWABAN AKUN ASESI (CBT & ESAI)
         ========================================================================= -->
    <div x-show="modalOpen" 
         class="fixed inset-0 z-50 overflow-y-auto bg-slate-900/60 backdrop-blur-xs flex items-center justify-center p-3 sm:p-6"
         style="display: none;"
         x-transition:enter="transition ease-out duration-200"
         x-transition:enter-start="opacity-0"
         x-transition:enter-end="opacity-100"
         x-transition:leave="transition ease-in duration-150"
         x-transition:leave-start="opacity-100"
         x-transition:leave-end="opacity-0">

        <div class="bg-white w-full max-w-4xl rounded-2xl shadow-2xl border border-slate-200 overflow-hidden flex flex-col max-h-[90vh]"
             @click.outside="tutupModal()">

            @foreach($pesertaData as $idx => $pData)
                @php
                    $p = $pData['pendaftaran'];
                    $jawabanPg = $pData['jawaban_pg'];
                    $skorCbt = $pData['skor_cbt'];
                    $jwbEsai = $pData['jawaban_esai'];
                    $penEsai = $pData['penilaian_esai'];
                @endphp
                <div x-show="selectedAsesiId == {{ $p->id }}" class="flex flex-col h-full overflow-hidden">
                    
                    <!-- Modal Header -->
                    <div class="px-6 py-4 bg-slate-50 border-b border-slate-200 flex items-center justify-between gap-4 shrink-0">
                        <div>
                            <span class="text-[10px] font-mono font-bold text-indigo-700 uppercase tracking-wider block">
                                LEMBAR JAWABAN UJIAN TEORI &bull; AKUN ASESI
                            </span>
                            <h3 class="text-base sm:text-lg font-bold text-slate-900 leading-snug">
                                {{ $p->asesi->nama_lengkap }}
                            </h3>
                            <div class="text-xs text-slate-500 flex items-center gap-2 flex-wrap">
                                <span>No. Reg: <strong>{{ $p->nomor_pendaftaran }}</strong></span>
                                <span>&bull;</span>
                                <span>{{ $p->asesi->profilAsesi->nama_sekolah_instansi ?? 'SMKN 1 Gunungputri' }}</span>
                            </div>
                        </div>

                        <div class="flex items-center gap-2">
                            <!-- Navigasi Asesi Sebelumnya / Selanjutnya -->
                            @if($idx > 0)
                                @php $prevId = $pesertaData[$idx - 1]['pendaftaran']->id; @endphp
                                <button type="button" 
                                        @click="selectedAsesiId = '{{ $prevId }}'" 
                                        class="px-2.5 py-1.5 rounded-lg border border-slate-200 hover:bg-slate-100 text-slate-700 text-xs font-bold transition-colors cursor-pointer"
                                        title="Ke Asesi Sebelumnya">
                                    &larr; Prev
                                </button>
                            @endif

                            @if($idx < count($pesertaData) - 1)
                                @php $nextId = $pesertaData[$idx + 1]['pendaftaran']->id; @endphp
                                <button type="button" 
                                        @click="selectedAsesiId = '{{ $nextId }}'" 
                                        class="px-2.5 py-1.5 rounded-lg border border-slate-200 hover:bg-slate-100 text-slate-700 text-xs font-bold transition-colors cursor-pointer"
                                        title="Ke Asesi Selanjutnya">
                                    Next &rarr;
                                </button>
                            @endif

                            <button type="button" 
                                    @click="tutupModal()" 
                                    class="px-3 py-1.5 rounded-lg bg-slate-200 hover:bg-slate-300 text-slate-800 text-xs font-bold transition-colors cursor-pointer">
                                Tutup
                            </button>
                        </div>
                    </div>

                    <!-- Modal Body (Scrollable) -->
                    <div class="p-6 overflow-y-auto space-y-6 text-slate-800">
                        
                        <!-- =========================================================
                             BAGIAN 1: FR.IA.05 UJIAN CBT PILIHAN GANDA
                             ========================================================= -->
                        <div class="bg-slate-50/70 border border-slate-200 rounded-2xl p-5 space-y-4">
                            <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3 border-b border-slate-200 pb-3">
                                <div>
                                    <span class="text-xs font-bold text-indigo-700 uppercase tracking-wider">FR.IA.05</span>
                                    <h4 class="text-sm font-bold text-slate-900">Hasil Ujian Teori CBT Pilihan Ganda</h4>
                                </div>
                                <div>
                                    @if($skorCbt !== null)
                                        <span class="px-3 py-1 rounded-full text-xs font-extrabold {{ $skorCbt >= 75 ? 'bg-emerald-100 text-emerald-800 border border-emerald-300' : 'bg-rose-100 text-rose-800 border border-rose-300' }}">
                                            Skor CBT: {{ $skorCbt }}%
                                        </span>
                                    @else
                                        <span class="text-xs text-slate-400 italic">Asesi Belum Mengerjakan</span>
                                    @endif
                                </div>
                            </div>

                            @if(empty($soalCbt))
                                <p class="text-xs text-slate-500">Skema ini tidak menyertakan pertanyaan pilihan ganda.</p>
                            @else
                                <div class="space-y-3">
                                    <span class="text-xs font-bold text-slate-700 uppercase tracking-wider block">Rincian Jawaban Soal:</span>
                                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-2.5">
                                        @foreach($soalCbt as $noSoal => $soal)
                                            @php
                                                $ans = $jawabanPg[$noSoal] ?? null;
                                                $kunci = $soal['kunci'] ?? 'A';
                                                $isCorrect = ($ans && strtoupper($ans) === strtoupper($kunci));
                                            @endphp
                                            <div class="p-3 rounded-xl border text-xs space-y-1.5 {{ $isCorrect ? 'bg-emerald-50/70 border-emerald-200' : ($ans ? 'bg-rose-50/70 border-rose-200' : 'bg-white border-slate-200') }}">
                                                <div class="flex items-center justify-between">
                                                    <span class="font-bold text-slate-900">No. {{ $noSoal }}</span>
                                                    <span class="px-2 py-0.5 rounded font-mono font-bold text-[10px] {{ $isCorrect ? 'bg-emerald-200 text-emerald-900' : ($ans ? 'bg-rose-200 text-rose-900' : 'bg-slate-200 text-slate-600') }}">
                                                        {{ $isCorrect ? 'BENAR' : ($ans ? 'SALAH' : 'KOSONG') }}
                                                    </span>
                                                </div>
                                                <p class="text-slate-700 line-clamp-2">{{ $soal['pertanyaan'] }}</p>
                                                <div class="flex items-center justify-between text-[11px] pt-1 border-t border-slate-200/60">
                                                    <span>Jawaban Asesi: <strong class="font-mono {{ $isCorrect ? 'text-emerald-700' : 'text-rose-700' }}">{{ $ans ?: '-' }}</strong></span>
                                                    <span>Kunci: <strong class="font-mono text-slate-700">{{ $kunci }}</strong></span>
                                                </div>
                                            </div>
                                        @endforeach
                                    </div>
                                </div>
                            @endif
                        </div>

                        <!-- =========================================================
                             BAGIAN 2: FR.IA.06 UJIAN TERTULIS ESAI & FORM KOREKSI
                             ========================================================= -->
                        <form action="{{ route('asesor.koreksi-teori.simpan') }}" method="POST" class="bg-white border border-slate-200 rounded-2xl p-5 space-y-5">
                            @csrf
                            <input type="hidden" name="jadwal_id" value="{{ $activeJadwal->id }}">

                            <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3 border-b border-slate-200 pb-3">
                                <div>
                                    <span class="text-xs font-bold text-indigo-700 uppercase tracking-wider">FR.IA.06</span>
                                    <h4 class="text-sm font-bold text-slate-900">Koreksi Jawaban Ujian Esai Asesi</h4>
                                    <p class="text-xs text-slate-500">Periksa uraian asesi, pilih nilai K/BK, lalu klik Simpan.</p>
                                </div>
                                <button type="submit" 
                                        class="px-4 py-2 rounded-xl bg-indigo-600 hover:bg-indigo-700 text-white text-xs font-bold shadow-xs transition-colors cursor-pointer shrink-0">
                                    Simpan Penilaian Esai
                                </button>
                            </div>

                            @if(empty($soalEsai))
                                <p class="text-xs text-slate-500">Skema ini tidak menyertakan pertanyaan esai.</p>
                            @else
                                <div class="space-y-5 divide-y divide-slate-100">
                                    @foreach($soalEsai as $noSoal => $soal)
                                        @php
                                            $jawabanAsesi = $jwbEsai[$noSoal] ?? null;
                                            $currentGrade = $penEsai[$noSoal] ?? 'K';
                                        @endphp
                                        <div class="{{ $loop->first ? '' : 'pt-5' }} space-y-3">
                                            <div class="flex flex-col sm:flex-row sm:items-start justify-between gap-3">
                                                <div class="space-y-1">
                                                    <span class="px-2 py-0.5 rounded bg-indigo-50 text-indigo-700 font-mono text-[10px] font-bold border border-indigo-100">
                                                        Soal Esai No. {{ $noSoal }}
                                                    </span>
                                                    <p class="text-xs sm:text-sm font-bold text-slate-900">{{ $soal['pertanyaan'] }}</p>
                                                </div>

                                                <!-- K / BK Grade Buttons -->
                                                <div class="flex items-center gap-2 shrink-0">
                                                    <div>
                                                        <input type="radio" 
                                                               id="modal_esai_{{ $p->id }}_{{ $noSoal }}_k" 
                                                               name="penilaian_esai[{{ $p->id }}][{{ $noSoal }}]" 
                                                               value="K" 
                                                               {{ $currentGrade === 'K' ? 'checked' : '' }} 
                                                               class="custom-radio-k hidden peer">
                                                        <label for="modal_esai_{{ $p->id }}_{{ $noSoal }}_k" 
                                                               class="px-3 py-1.5 rounded-lg border border-slate-200 bg-white text-slate-600 font-bold text-xs cursor-pointer flex items-center justify-center hover:bg-slate-50 transition-all">
                                                            K (Kompeten)
                                                        </label>
                                                    </div>

                                                    <div>
                                                        <input type="radio" 
                                                               id="modal_esai_{{ $p->id }}_{{ $noSoal }}_bk" 
                                                               name="penilaian_esai[{{ $p->id }}][{{ $noSoal }}]" 
                                                               value="BK" 
                                                               {{ $currentGrade === 'BK' ? 'checked' : '' }} 
                                                               class="custom-radio-bk hidden peer">
                                                        <label for="modal_esai_{{ $p->id }}_{{ $noSoal }}_bk" 
                                                               class="px-3 py-1.5 rounded-lg border border-slate-200 bg-white text-slate-600 font-bold text-xs cursor-pointer flex items-center justify-center hover:bg-slate-50 transition-all">
                                                            BK (Belum)
                                                        </label>
                                                    </div>
                                                </div>
                                            </div>

                                            <!-- Teks Jawaban Asesi -->
                                            <div class="space-y-1">
                                                <span class="text-[10px] font-bold text-slate-500 uppercase tracking-wider block">Jawaban Yang Diketik Asesi:</span>
                                                <div class="p-3.5 bg-slate-50 border border-slate-200/80 rounded-xl text-xs sm:text-sm text-slate-800 font-mono whitespace-pre-wrap leading-relaxed">
                                                    {{ $jawabanAsesi ?: '(Asesi belum menginputkan jawaban)' }}
                                                </div>
                                            </div>

                                            <!-- Kunci Standar -->
                                            <div class="p-2.5 bg-blue-50 border border-blue-100 rounded-xl text-xs text-blue-900 space-y-0.5">
                                                <span class="font-bold text-[11px] block">Rujukan Kunci / Standar Jawaban:</span>
                                                <p>{{ $soal['kunci_referensi'] }}</p>
                                            </div>
                                        </div>
                                    @endforeach

                                    <!-- Catatan Asesor -->
                                    <div class="pt-4 space-y-1">
                                        <label class="block text-xs font-bold text-slate-700">Catatan Koreksi Esai (Opsional):</label>
                                        <input type="text" 
                                               name="catatan_esai[{{ $p->id }}]" 
                                               value="{{ $pData['catatan_esai'] }}" 
                                               placeholder="Catatan hasil penilaian esai untuk asesi ini..." 
                                               class="w-full bg-slate-50 border border-slate-200 rounded-xl px-3 py-2 text-xs text-slate-800 focus:bg-white focus:ring-1 focus:ring-indigo-500 transition-colors">
                                    </div>
                                </div>
                            @endif

                            <div class="pt-3 border-t border-slate-100 flex justify-end">
                                <button type="submit" 
                                        class="px-5 py-2.5 rounded-xl bg-indigo-600 hover:bg-indigo-700 text-white text-xs font-bold shadow-xs transition-colors cursor-pointer">
                                    Simpan Penilaian Esai Asesi Ini
                                </button>
                            </div>
                        </form>

                    </div>
                </div>
            @endforeach

        </div>
    </div>

</div>
@endsection
