@extends('tata-letak.dasbor')

@section('judul', 'Pusat Dokumen Asesmen BNSP')

@section('konten')
<div class="space-y-4" x-data="dokumenHubApp()" x-cloak>

    <!-- TOP BREADCRUMB & HEADER -->
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3 border-b border-slate-200/80 pb-3">
        <div>
            <div class="flex items-center gap-2 text-xs text-slate-500 mb-1">
                <a href="{{ route('beranda') }}" class="hover:text-blue-600">Home</a>
                <span>/</span>
                <span class="text-slate-800 font-bold">Dokumen Asesmen</span>
            </div>
            <h1 class="text-lg sm:text-xl font-black text-slate-900 tracking-tight flex items-center gap-2">
                <span class="w-2.5 h-6 bg-blue-600 rounded-xs"></span>
                <span>Pusat Manajemen Dokumen Asesmen Standar BNSP</span>
            </h1>
            <p class="text-xs text-slate-500 mt-0.5">
                Pusat manajemen dan administrasi rekaman uji kompetensi: FR.AK.02, FR.AK.03, FR.AK.05, FR.AK.06, & FR.VA
            </p>
        </div>

        <!-- Role Action Quick Links -->
        <div class="flex items-center gap-2 flex-wrap">
            @if(in_array(auth()->user()->peran, ['asesor', 'admin', 'superadmin']))
                <a href="{{ route('dokumen-asesmen.ak05.index') }}" class="inline-flex items-center px-3 py-1.5 rounded-xl border border-slate-200 bg-white text-xs font-semibold text-slate-700 hover:bg-slate-50 hover:text-blue-600 transition-colors shadow-2xs">
                    <span>Laporan (AK.05)</span>
                </a>
                <a href="{{ route('dokumen-asesmen.ak06.index') }}" class="inline-flex items-center px-3 py-1.5 rounded-xl border border-slate-200 bg-white text-xs font-semibold text-slate-700 hover:bg-slate-50 hover:text-blue-600 transition-colors shadow-2xs">
                    <span>Review (AK.06)</span>
                </a>
                <a href="{{ route('dokumen-asesmen.va.index') }}" class="inline-flex items-center px-3 py-1.5 rounded-xl border border-slate-200 bg-white text-xs font-semibold text-slate-700 hover:bg-slate-50 hover:text-emerald-600 transition-colors shadow-2xs">
                    <span>Validasi (FR.VA)</span>
                </a>
            @endif
        </div>
    </div>

    <!-- STATISTIC STATUS COUNTERS -->
    <div class="grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-6 gap-2.5">
        <!-- Total -->
        <div class="bg-white border border-slate-200/90 rounded-2xl p-3 shadow-2xs">
            <div class="text-[10px] font-bold text-slate-400 uppercase tracking-wider">Total Dokumen</div>
            <div class="text-xl font-extrabold text-slate-800 mt-0.5">{{ $stats['total'] }}</div>
            <div class="text-[10px] text-slate-500 mt-0.5 flex items-center gap-1">
                <span>Semua instrumen</span>
            </div>
        </div>

        <!-- Draft -->
        <div class="bg-white border border-slate-200/90 rounded-2xl p-3 shadow-2xs">
            <div class="text-[10px] font-bold text-slate-400 uppercase tracking-wider">Draft</div>
            <div class="text-xl font-extrabold text-slate-600 mt-0.5">{{ $stats['draft'] }}</div>
            <div class="text-[10px] text-slate-500 mt-0.5 flex items-center gap-1">
                <span class="w-1.5 h-1.5 rounded-full bg-slate-400"></span>
                <span>Belum diajukan</span>
            </div>
        </div>

        <!-- Dalam Proses -->
        <div class="bg-white border border-blue-100 rounded-2xl p-3 shadow-2xs">
            <div class="text-[10px] font-bold text-blue-600 uppercase tracking-wider">Dalam Proses</div>
            <div class="text-xl font-extrabold text-blue-700 mt-0.5">{{ $stats['dalam_proses'] }}</div>
            <div class="text-[10px] text-blue-500 mt-0.5 flex items-center gap-1">
                <span class="w-1.5 h-1.5 rounded-full bg-blue-500 animate-pulse"></span>
                <span>Sedang dinilai</span>
            </div>
        </div>

        <!-- Menunggu Review / TTD -->
        <div class="bg-white border border-amber-100 rounded-2xl p-3 shadow-2xs">
            <div class="text-[10px] font-bold text-amber-600 uppercase tracking-wider">Menunggu Review</div>
            <div class="text-xl font-extrabold text-amber-700 mt-0.5">{{ $stats['menunggu_review'] }}</div>
            <div class="text-[10px] text-amber-500 mt-0.5 flex items-center gap-1">
                <span class="w-1.5 h-1.5 rounded-full bg-amber-500"></span>
                <span>Perlu pengesahan</span>
            </div>
        </div>

        <!-- Selesai / Final -->
        <div class="bg-white border border-emerald-100 rounded-2xl p-3 shadow-2xs">
            <div class="text-[10px] font-bold text-emerald-600 uppercase tracking-wider">Selesai (Final)</div>
            <div class="text-xl font-extrabold text-emerald-700 mt-0.5">{{ $stats['selesai'] }}</div>
            <div class="text-[10px] text-emerald-600 mt-0.5 flex items-center gap-1">
                <span class="w-1.5 h-1.5 rounded-full bg-emerald-500"></span>
                <span>Terkunci & sah</span>
            </div>
        </div>

        <!-- Perlu Revisi -->
        <div class="bg-white border border-rose-100 rounded-2xl p-3 shadow-2xs">
            <div class="text-[10px] font-bold text-rose-600 uppercase tracking-wider">Perlu Revisi</div>
            <div class="text-xl font-extrabold text-rose-700 mt-0.5">{{ $stats['perlu_revisi'] }}</div>
            <div class="text-[10px] text-rose-500 mt-0.5 flex items-center gap-1">
                <span class="w-1.5 h-1.5 rounded-full bg-rose-500"></span>
                <span>Perbaikan data</span>
            </div>
        </div>
    </div>

    <!-- FILTER & SEARCH BAR -->
    <div class="bg-white rounded-2xl border border-slate-200/90 shadow-2xs p-3.5 space-y-3">
        <form method="GET" action="{{ route('dokumen-asesmen.index') }}" class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-5 gap-2.5 items-end">
            <!-- Jenis Dokumen -->
            <div>
                <label class="block text-[10px] font-bold text-slate-500 uppercase tracking-wider mb-1">Jenis Dokumen</label>
                <select name="jenis_dokumen" class="w-full text-xs font-semibold rounded-xl border-slate-200 bg-slate-50/60 focus:bg-white focus:border-blue-500 focus:ring-blue-500 py-1.5">
                    <option value="semua" {{ $jenisDokumen === 'semua' ? 'selected' : '' }}>Semua Dokumen</option>
                    <option value="ak02" {{ $jenisDokumen === 'ak02' ? 'selected' : '' }}>FR.AK.02 — Rekaman Asesmen</option>
                    <option value="ak03" {{ $jenisDokumen === 'ak03' ? 'selected' : '' }}>FR.AK.03 — Umpan Balik Asesi</option>
                    @if(auth()->user()->peran !== 'asesi')
                        <option value="ak05" {{ $jenisDokumen === 'ak05' ? 'selected' : '' }}>FR.AK.05 — Laporan Asesmen</option>
                        <option value="ak06" {{ $jenisDokumen === 'ak06' ? 'selected' : '' }}>FR.AK.06 — Review Proses Asesmen</option>
                        <option value="va" {{ $jenisDokumen === 'va' ? 'selected' : '' }}>FR.VA — Validasi Asesmen</option>
                    @endif
                </select>
            </div>

            <!-- Skema -->
            <div>
                <label class="block text-[10px] font-bold text-slate-500 uppercase tracking-wider mb-1">Skema Sertifikasi</label>
                <select name="skema_id" class="w-full text-xs rounded-xl border-slate-200 bg-slate-50/60 focus:bg-white focus:border-blue-500 focus:ring-blue-500 py-1.5">
                    <option value="">Semua Skema</option>
                    @foreach($skemas as $s)
                        <option value="{{ $s->id }}" {{ (string)$skemaId === (string)$s->id ? 'selected' : '' }}>
                            {{ Str::limit($s->nama_skema, 32) }}
                        </option>
                    @endforeach
                </select>
            </div>

            <!-- Status -->
            <div>
                <label class="block text-[10px] font-bold text-slate-500 uppercase tracking-wider mb-1">Status Dokumen</label>
                <select name="status" class="w-full text-xs rounded-xl border-slate-200 bg-slate-50/60 focus:bg-white focus:border-blue-500 focus:ring-blue-500 py-1.5">
                    <option value="">Semua Status</option>
                    <option value="draft" {{ $statusFilter === 'draft' ? 'selected' : '' }}>Draft</option>
                    <option value="dalam_pengisian" {{ $statusFilter === 'dalam_pengisian' ? 'selected' : '' }}>Dalam Pengisian</option>
                    <option value="submitted" {{ $statusFilter === 'submitted' ? 'selected' : '' }}>Submitted</option>
                    <option value="decision_recorded" {{ $statusFilter === 'decision_recorded' ? 'selected' : '' }}>Keputusan Dicatat</option>
                    <option value="final" {{ $statusFilter === 'final' ? 'selected' : '' }}>Final / Disahkan</option>
                    <option value="terkunci" {{ $statusFilter === 'terkunci' ? 'selected' : '' }}>Terkunci</option>
                </select>
            </div>

            <!-- Pencarian -->
            <div>
                <label class="block text-[10px] font-bold text-slate-500 uppercase tracking-wider mb-1">Pencarian</label>
                <div>
                    <input type="text" name="cari" value="{{ $cari }}" placeholder="Nama / No. Reg / Dokumen..."
                           class="w-full text-xs rounded-xl border-slate-200 bg-slate-50/60 focus:bg-white focus:border-blue-500 focus:ring-blue-500 px-3 py-1.5">
                </div>
            </div>

            <!-- Button Filter & Reset -->
            <div class="flex items-center gap-1.5">
                <button type="submit" class="flex-1 py-1.5 px-3 rounded-xl bg-slate-900 text-white text-xs font-bold hover:bg-slate-800 transition-colors shadow-2xs flex items-center justify-center">
                    <span>Terapkan</span>
                </button>
                <a href="{{ route('dokumen-asesmen.index') }}" class="py-1.5 px-2.5 rounded-xl border border-slate-200 bg-white text-xs font-semibold text-slate-600 hover:bg-slate-50 transition-colors">
                    Reset
                </a>
            </div>
        </form>
    </div>

    <!-- DENSE ENTERPRISE TABLE -->
    <div class="bg-white rounded-2xl border border-slate-200/90 shadow-2xs overflow-hidden">
        <div class="px-4 py-3 border-b border-slate-100 flex items-center justify-between">
            <div class="text-xs font-bold text-slate-800 flex items-center gap-2">
                <span>Daftar Dokumen Asesmen</span>
                <span class="px-2 py-0.5 rounded-full bg-slate-100 text-slate-600 text-[10px] font-extrabold">{{ $items->count() }} Data</span>
            </div>
            <div class="text-[11px] text-slate-400">Menampilkan seluruh arsip resmi</div>
        </div>

        <div class="overflow-x-auto">
            <table class="w-full text-left border-collapse text-xs">
                <thead>
                    <tr class="bg-slate-50/80 text-slate-500 text-[10px] font-bold uppercase tracking-wider border-b border-slate-200">
                        <th class="py-2.5 px-3">Jenis</th>
                        <th class="py-2.5 px-3">No. Dokumen</th>
                        <th class="py-2.5 px-3">Skema Sertifikasi</th>
                        <th class="py-2.5 px-3">Asesi / Subjek</th>
                        <th class="py-2.5 px-3">Asesor</th>
                        <th class="py-2.5 px-3">Tgl Update</th>
                        <th class="py-2.5 px-3">Status</th>
                        <th class="py-2.5 px-3 text-right">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100 font-medium">
                    @forelse($items as $doc)
                        @php
                            $badgeColor = match($doc['status']) {
                                'final', 'terkunci', 'signed' => 'bg-emerald-50 text-emerald-700 border-emerald-200',
                                'dalam_pengisian', 'review', 'assessment_completed' => 'bg-blue-50 text-blue-700 border-blue-200',
                                'decision_recorded', 'submitted' => 'bg-amber-50 text-amber-700 border-amber-200',
                                'perlu_revisi', 'revisi' => 'bg-rose-50 text-rose-700 border-rose-200',
                                default => 'bg-slate-100 text-slate-600 border-slate-200',
                            };
                            $codeColor = match($doc['jenis_kode']) {
                                'FR.AK.02' => 'text-blue-700 bg-blue-50 border-blue-200',
                                'FR.AK.03' => 'text-indigo-700 bg-indigo-50 border-indigo-200',
                                'FR.AK.05' => 'text-purple-700 bg-purple-50 border-purple-200',
                                'FR.AK.06' => 'text-amber-700 bg-amber-50 border-amber-200',
                                'FR.VA' => 'text-emerald-700 bg-emerald-50 border-emerald-200',
                                default => 'text-slate-700 bg-slate-50 border-slate-200',
                            };
                        @endphp
                        <tr class="hover:bg-slate-50/70 transition-colors">
                            <!-- Jenis -->
                            <td class="py-2.5 px-3 whitespace-nowrap">
                                <span class="px-2 py-0.5 rounded-lg border text-[10px] font-mono font-extrabold {{ $codeColor }}">
                                    {{ $doc['jenis_kode'] }}
                                </span>
                            </td>

                            <!-- No Dokumen -->
                            <td class="py-2.5 px-3 whitespace-nowrap font-mono font-bold text-slate-800">
                                {{ $doc['nomor_dokumen'] }}
                            </td>

                            <!-- Skema -->
                            <td class="py-2.5 px-3 max-w-[200px] truncate text-slate-700" title="{{ $doc['skema'] }}">
                                {{ $doc['skema'] }}
                            </td>

                            <!-- Subjek / Asesi -->
                            <td class="py-2.5 px-3 text-slate-800 font-semibold whitespace-nowrap">
                                {{ $doc['subjek'] }}
                            </td>

                            <!-- Asesor -->
                            <td class="py-2.5 px-3 whitespace-nowrap text-slate-600">
                                {{ $doc['asesor'] }}
                            </td>

                            <!-- Tanggal -->
                            <td class="py-2.5 px-3 whitespace-nowrap text-slate-500 text-[11px]">
                                {{ $doc['tanggal'] }}
                            </td>

                            <!-- Status -->
                            <td class="py-2.5 px-3 whitespace-nowrap">
                                <span class="px-2 py-0.5 rounded-full border text-[10px] font-bold uppercase tracking-wider {{ $badgeColor }}">
                                    {{ str_replace('_', ' ', $doc['status']) }}
                                    @if($doc['version'] > 1)
                                        <span class="ml-1 opacity-70">v{{ $doc['version'] }}</span>
                                    @endif
                                </span>
                            </td>

                            <!-- Aksi -->
                            <td class="py-2.5 px-3 text-right whitespace-nowrap space-x-1">
                                <!-- Lihat / Lanjutkan -->
                                <a href="{{ $doc['view_url'] }}" 
                                   class="inline-flex items-center px-2.5 py-1 rounded-lg bg-blue-50 text-blue-700 hover:bg-blue-100 text-[11px] font-bold transition-colors">
                                    <span>Buka</span>
                                </a>

                                <!-- Cetak -->
                                <a href="{{ $doc['print_url'] }}" target="_blank" 
                                   class="inline-flex items-center px-2 py-1 rounded-lg border border-slate-200 bg-white text-slate-600 hover:bg-slate-50 text-[11px] font-semibold transition-colors"
                                   title="Cetak Format A4 BNSP">
                                    <span>Cetak</span>
                                </a>

                                <!-- Riwayat Audit Modal -->
                                <button type="button" 
                                        @click="openAuditModal('{{ $doc['jenis_kode'] }}', {{ $doc['id'] }})"
                                        class="inline-flex items-center px-2 py-1 rounded-lg border border-slate-200 bg-white text-slate-500 hover:bg-slate-50 text-[11px] font-semibold transition-colors"
                                        title="Jejak Audit Status">
                                    <span>Audit</span>
                                </button>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="8" class="text-center py-8 text-slate-400">
                                <span>Tidak ditemukan dokumen asesmen yang cocok dengan kriteria filter.</span>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    <!-- MODAL JEJAK REKAM AUDIT TRAIL -->
    <div x-show="auditModalOpen" 
         class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-slate-900/60 backdrop-blur-xs" 
         x-transition:enter="transition ease-out duration-150"
         x-transition:enter-start="opacity-0"
         x-transition:enter-end="opacity-100"
         x-transition:leave="transition ease-in duration-100"
         x-transition:leave-start="opacity-100"
         x-transition:leave-end="opacity-0"
         style="display: none;">
        <div class="bg-white rounded-2xl border border-slate-200 shadow-2xl max-w-xl w-full p-5 space-y-4 max-h-[85vh] flex flex-col" @click.outside="auditModalOpen = false">
            
            <div class="flex items-center justify-between border-b border-slate-100 pb-3">
                <div class="flex items-center gap-2">
                    <div class="w-8 h-8 rounded-xl bg-blue-50 border border-blue-100 flex items-center justify-center text-blue-600 font-bold text-[10px]">
                        LOG
                    </div>
                    <div>
                        <h3 class="font-bold text-sm text-slate-900 leading-tight">Jejak Audit Status & Versi Dokumen</h3>
                        <p class="text-[11px] text-slate-400" x-text="activeDocType + ' #' + activeDocId"></p>
                    </div>
                </div>
                <button type="button" @click="auditModalOpen = false" class="text-slate-400 hover:text-slate-600 text-xs font-semibold px-2 py-1 rounded-lg hover:bg-slate-100">
                    Tutup
                </button>
            </div>

            <!-- Content Logs -->
            <div class="overflow-y-auto space-y-3 pr-1 text-xs flex-1">
                <template x-if="loadingLogs">
                    <div class="text-center py-6 text-slate-400">
                        <span>Memuat log aktivitas...</span>
                    </div>
                </template>

                <template x-if="!loadingLogs && logs.length === 0">
                    <div class="text-center py-6 text-slate-400">
                        Belum ada jejak audit yang dicatat untuk dokumen ini.
                    </div>
                </template>

                <template x-for="(log, idx) in logs" :key="log.id">
                    <div class="border border-slate-100 rounded-xl p-3 bg-slate-50/60 space-y-1">
                        <div class="flex items-center justify-between">
                            <span class="font-bold text-slate-800" x-text="log.action.toUpperCase()"></span>
                            <span class="text-[10px] text-slate-400 font-mono" x-text="formatDate(log.created_at)"></span>
                        </div>
                        <div class="text-slate-600 text-[11px]">
                            <span class="font-semibold text-slate-700" x-text="log.user ? log.user.nama_lengkap : 'Sistem'"></span>: 
                            <span x-text="log.notes || 'Perubahan status dokumen.'"></span>
                        </div>
                        <div class="flex items-center gap-2 pt-1 text-[10px] text-slate-500">
                            <span>Status:</span>
                            <span class="font-mono bg-slate-200/80 px-1.5 py-0.2 rounded" x-text="log.previous_status || 'null'"></span>
                            <span>&rarr;</span>
                            <span class="font-mono bg-blue-100 text-blue-700 px-1.5 py-0.2 rounded font-bold" x-text="log.new_status"></span>
                            <span class="ml-auto text-slate-400" x-text="'v' + log.version"></span>
                        </div>
                    </div>
                </template>
            </div>

            <div class="border-t border-slate-100 pt-3 text-right">
                <button type="button" @click="auditModalOpen = false" class="px-4 py-1.5 bg-slate-100 text-slate-700 rounded-xl font-bold text-xs hover:bg-slate-200">
                    Tutup
                </button>
            </div>
        </div>
    </div>

</div>

@push('css')
<script>
    function dokumenHubApp() {
        return {
            auditModalOpen: false,
            loadingLogs: false,
            activeDocType: '',
            activeDocId: null,
            logs: [],

            openAuditModal(type, id) {
                this.activeDocType = type;
                this.activeDocId = id;
                this.auditModalOpen = true;
                this.loadingLogs = true;
                this.logs = [];

                fetch(`/dokumen-asesmen/audit-trail/${type}/${id}`)
                    .then(res => res.json())
                    .then(data => {
                        this.logs = data.logs || [];
                        this.loadingLogs = false;
                    })
                    .catch(() => {
                        this.loadingLogs = false;
                    });
            },

            formatDate(d) {
                if (!d) return '-';
                const date = new Date(d);
                return date.toLocaleDateString('id-ID', {
                    day: '2-digit',
                    month: 'short',
                    year: 'numeric',
                    hour: '2-digit',
                    minute: '2-digit'
                });
            }
        };
    }
</script>
@endpush
@endsection
