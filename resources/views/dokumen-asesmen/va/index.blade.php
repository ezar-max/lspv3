@extends('tata-letak.dasbor')

@section('judul', 'FR.VA - Memberikan Kontribusi dalam Validasi Asesmen')

@section('konten')
<div class="space-y-4" x-data="{ modalOpen: false }" x-cloak>

    <!-- TOP BREADCRUMB & HEADER -->
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3 border-b border-slate-200/80 pb-3">
        <div>
            <div class="flex items-center gap-2 text-xs text-slate-500 mb-1">
                <a href="{{ route('dokumen-asesmen.index') }}" class="hover:text-blue-600">Dokumen Asesmen</a>
                <span>/</span>
                <span class="px-2 py-0.5 rounded-md bg-teal-50 text-teal-700 font-mono font-bold text-[10px]">FR.VA</span>
                <span class="text-slate-800 font-bold hidden md:inline">Validasi Asesmen</span>
            </div>
            <h1 class="text-base sm:text-lg font-black text-slate-900 tracking-tight flex items-center gap-2">
                <span>FR.VA &bull; Memberikan Kontribusi dalam Validasi Asesmen</span>
            </h1>
            <p class="text-xs text-slate-500 mt-0.5">
                Kegiatan penjaminan mutu asesmen independen: evaluasi acuan pembanding, matriks 8 aspek validasi (VATM & VRFA), dan rencana perbaikan mutu LSP.
            </p>
        </div>

        <div class="flex items-center gap-2">
            <button type="button" @click="modalOpen = true" class="inline-flex items-center px-3.5 py-2 rounded-xl bg-teal-600 hover:bg-teal-700 text-white font-bold text-xs transition-colors shadow-2xs">
                <span>Mulai Kegiatan Validasi</span>
            </button>
            <a href="{{ (url()->previous() && url()->previous() !== url()->current()) ? url()->previous() : route('dokumen-asesmen.index') }}" 
               onclick="if (document.referrer && document.referrer !== window.location.href) { window.location.href = document.referrer; return false; } else if (window.history.length > 1) { window.history.back(); return false; }"
               class="inline-flex items-center px-3 py-2 rounded-xl border border-slate-200 bg-white text-slate-700 font-semibold text-xs hover:bg-slate-50 transition-colors cursor-pointer shadow-2xs">
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

    <!-- CONTEXT BANNER -->
    <div class="bg-teal-50/60 border border-teal-200/80 rounded-2xl p-4 text-xs text-teal-900 flex items-start gap-3 shadow-2xs">
        <div class="w-8 h-8 rounded-xl bg-teal-600 text-white flex items-center justify-center shrink-0 mt-0.5 text-[11px] font-extrabold tracking-wider">
            QA
        </div>
        <div>
            <span class="font-bold block text-teal-950">Aktivitas Penjaminan Mutu & Validasi Asesmen (Quality Assurance):</span>
            Sesuai regulasi BNSP, formulir FR.VA adalah aktivitas validasi mandiri per-skema sertifikasi atau sesi uji, bukan langkah berulang yang diwajibkan untuk setiap peserta perorangan. Validasi dapat dilaksanakan: <strong>Sebelum Asesmen</strong> (uji coba perangkat), <strong>Pada Saat Asesmen</strong>, atau <strong>Setelah Asesmen</strong> (kaji ulang berkala).
        </div>
    </div>

    <!-- LIST OF FR.VA ACTIVITIES -->
    <div class="bg-white rounded-2xl border border-slate-200/90 shadow-2xs overflow-hidden">
        <div class="px-4 py-3 border-b border-slate-100 flex items-center justify-between">
            <div class="text-xs font-bold text-slate-800">
                Daftar Kegiatan Validasi Asesmen (FR.VA)
            </div>
            <span class="px-2 py-0.5 rounded-full bg-slate-100 text-slate-600 text-[10px] font-extrabold">{{ $validations->count() }} Kegiatan</span>
        </div>

        <div class="overflow-x-auto">
            <table class="w-full text-left border-collapse text-xs">
                <thead>
                    <tr class="bg-slate-50/80 text-slate-500 text-[10px] font-bold uppercase tracking-wider border-b border-slate-200">
                        <th class="py-2.5 px-3">No. Validasi</th>
                        <th class="py-2.5 px-3">Skema Sertifikasi</th>
                        <th class="py-2.5 px-3">Lead / Ketua Validasi</th>
                        <th class="py-2.5 px-3">Tanggal & Tempat</th>
                        <th class="py-2.5 px-3 text-center">Tahapan Wizard</th>
                        <th class="py-2.5 px-3">Status</th>
                        <th class="py-2.5 px-3 text-right">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100 font-medium">
                    @forelse($validations as $va)
                        <tr class="hover:bg-slate-50/50">
                            <td class="py-2.5 px-3 font-mono font-bold text-slate-800 whitespace-nowrap">
                                {{ $va->nomor_validasi }}
                            </td>
                            <td class="py-2.5 px-3 text-slate-700 font-semibold">
                                {{ $va->skema ? $va->skema->nama_skema : '-' }}
                            </td>
                            <td class="py-2.5 px-3 text-slate-800">
                                {{ $va->leadAsesor ? $va->leadAsesor->nama_lengkap : '-' }}
                            </td>
                            <td class="py-2.5 px-3 text-slate-500 whitespace-nowrap">
                                <div>{{ $va->tanggal_validasi ? $va->tanggal_validasi->format('d/m/Y') : '-' }}</div>
                                <div class="text-[10px] text-slate-400">{{ $va->tempat_validasi ?: 'TUK Mandiri' }}</div>
                            </td>
                            <td class="py-2.5 px-3 text-center">
                                <span class="px-2 py-0.5 rounded-full bg-slate-100 text-slate-700 font-bold text-[10px]">
                                    Langkah {{ $va->current_step }} / 7
                                </span>
                            </td>
                            <td class="py-2.5 px-3 whitespace-nowrap">
                                <span class="px-2 py-0.5 rounded-full border text-[10px] font-bold uppercase tracking-wider {{ $va->isFinalized() ? 'bg-emerald-50 text-emerald-700 border-emerald-200' : 'bg-teal-50 text-teal-700 border-teal-200' }}">
                                    {{ $va->status }}
                                </span>
                            </td>
                            <td class="py-2.5 px-3 text-right whitespace-nowrap space-x-1">
                                <a href="{{ route('dokumen-asesmen.va.wizard', $va->id) }}" class="inline-flex items-center px-2.5 py-1 rounded-lg bg-teal-50 text-teal-700 hover:bg-teal-100 text-[11px] font-bold">
                                     <span>Buka Wizard</span>
                                 </a>
                                 <a href="{{ route('dokumen-asesmen.va.cetak', $va->id) }}" target="_blank" class="inline-flex items-center px-2 py-1 rounded-lg border border-slate-200 text-slate-600 hover:bg-slate-50 text-[11px] font-semibold">
                                     <span>Cetak</span>
                                 </a>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="text-center py-8 text-slate-400">
                                Belum ada dokumen validasi asesmen FR.VA. Silakan klik tombol "Mulai Kegiatan Validasi".
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    <!-- MODAL MEMBUAT VALIDASI BARU -->
    <div x-show="modalOpen" class="fixed inset-0 z-50 overflow-y-auto" style="display: none;">
        <div class="fixed inset-0 bg-slate-900/50 backdrop-blur-xs transition-opacity" @click="modalOpen = false"></div>
        <div class="flex min-h-full items-center justify-center p-4">
            <div class="relative transform overflow-hidden rounded-2xl bg-white p-5 text-left shadow-xl transition-all sm:w-full sm:max-w-md border border-slate-200">
                <div class="flex items-center justify-between pb-3 border-b border-slate-100 mb-4">
                    <h3 class="text-sm font-bold text-slate-900">Inisialisasi Kegiatan Validasi (FR.VA)</h3>
                    <button type="button" @click="modalOpen = false" class="text-slate-400 hover:text-slate-600 text-xs font-semibold px-2 py-1 rounded-lg hover:bg-slate-50">
                        Tutup
                    </button>
                </div>

                <form action="{{ route('dokumen-asesmen.va.create') }}" method="POST" class="space-y-4">
                    @csrf
                    <div>
                        <label class="block text-xs font-semibold text-slate-700 mb-1">Pilih Skema Sertifikasi yang Divalidasi *</label>
                        <select name="skema_id" required class="w-full text-xs rounded-xl border border-slate-200 p-2.5 focus:ring-1 focus:ring-teal-500 bg-white">
                            <option value="">-- Pilih Skema --</option>
                            @foreach($skemas as $s)
                                <option value="{{ $s->id }}">{{ $s->kode_skema }} - {{ $s->nama_skema }}</option>
                            @endforeach
                        </select>
                    </div>

                    <div class="p-3 bg-slate-50 border border-slate-100 rounded-xl text-slate-500 text-xs">
                        Proses validasi akan dipandu melalui 7 langkah formulir wizard standar BNSP hingga penandatanganan rekomendasi perbaikan mutu.
                    </div>

                    <div class="flex items-center justify-end gap-2 pt-2">
                        <button type="button" @click="modalOpen = false" class="px-3.5 py-1.5 rounded-xl border border-slate-200 text-slate-600 text-xs font-semibold hover:bg-slate-50">
                            Batal
                        </button>
                        <button type="submit" class="px-4 py-1.5 rounded-xl bg-teal-600 text-white text-xs font-bold hover:bg-teal-700 shadow-2xs">
                            Lanjutkan ke Wizard
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

</div>
@endsection
