@extends('tata-letak.dasbor')

@section('judul', 'FR.AK.06 - Meninjau Proses Asesmen')

@section('konten')
<div class="space-y-4" x-data="{ modalOpen: false }" x-cloak>

    <!-- TOP BREADCRUMB & HEADER -->
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3 border-b border-slate-200/80 pb-3">
        <div>
            <div class="flex items-center gap-2 text-xs text-slate-500 mb-1">
                <a href="{{ route('dokumen-asesmen.index') }}" class="hover:text-blue-600">Dokumen Asesmen</a>
                <span>/</span>
                <span class="px-2 py-0.5 rounded-md bg-amber-50 text-amber-700 font-mono font-bold text-[10px]">FR.AK.06</span>
                <span class="text-slate-800 font-bold hidden md:inline">Review Proses Asesmen</span>
            </div>
            <h1 class="text-base sm:text-lg font-black text-slate-900 tracking-tight flex items-center gap-2">
                <span>FR.AK.06 &bull; Meninjau Proses Asesmen</span>
            </h1>
            <p class="text-xs text-slate-500 mt-0.5">
                Evaluasi mutu prosedur asesmen, pemenuhan 5 dimensi kompetensi, dan rekomendasi peningkatan kualitas pengujian.
            </p>
        </div>

        <div class="flex items-center gap-2">
            <button type="button" @click="modalOpen = true" class="inline-flex items-center gap-1.5 px-3.5 py-2 rounded-xl bg-amber-600 hover:bg-amber-700 text-white font-bold text-xs transition-colors shadow-2xs">
                <span>Buat Review Baru</span>
            </button>
            <a href="{{ (url()->previous() && url()->previous() !== url()->current()) ? url()->previous() : route('dokumen-asesmen.index') }}" 
               onclick="if (document.referrer && document.referrer !== window.location.href) { window.location.href = document.referrer; return false; } else if (window.history.length > 1) { window.history.back(); return false; }"
               class="inline-flex items-center gap-1.5 px-3 py-2 rounded-xl border border-slate-200 bg-white text-slate-700 font-semibold text-xs hover:bg-slate-50 transition-colors cursor-pointer shadow-2xs">
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

    <!-- LIST OF AK.06 REVIEWS -->
    <div class="bg-white rounded-2xl border border-slate-200/90 shadow-2xs overflow-hidden">
        <div class="px-4 py-3 border-b border-slate-100 flex items-center justify-between">
            <div class="text-xs font-bold text-slate-800">
                Arsip Peninjauan Proses Asesmen (FR.AK.06)
            </div>
            <span class="px-2 py-0.5 rounded-full bg-slate-100 text-slate-600 text-[10px] font-extrabold">{{ $reviews->count() }} Dokumen</span>
        </div>

        <div class="overflow-x-auto">
            <table class="w-full text-left border-collapse text-xs">
                <thead>
                    <tr class="bg-slate-50/80 text-slate-500 text-[10px] font-bold uppercase tracking-wider border-b border-slate-200">
                        <th class="py-2.5 px-3">No. Review</th>
                        <th class="py-2.5 px-3">Skema Sertifikasi</th>
                        <th class="py-2.5 px-3">Lingkup Review</th>
                        <th class="py-2.5 px-3">Peninjau / Asesor</th>
                        <th class="py-2.5 px-3">Tgl Review</th>
                        <th class="py-2.5 px-3">Status</th>
                        <th class="py-2.5 px-3 text-right">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100 font-medium">
                    @forelse($reviews as $rev)
                        <tr class="hover:bg-slate-50/50">
                            <td class="py-2.5 px-3 font-mono font-bold text-slate-800 whitespace-nowrap">
                                {{ $rev->nomor_review }}
                            </td>
                            <td class="py-2.5 px-3 text-slate-700 font-semibold">
                                {{ $rev->skema ? $rev->skema->nama_skema : '-' }}
                            </td>
                            <td class="py-2.5 px-3 text-slate-600 uppercase text-[10px] font-bold">
                                {{ $rev->scope_type }}
                            </td>
                            <td class="py-2.5 px-3 text-slate-800">
                                {{ $rev->asesor ? $rev->asesor->nama_lengkap : '-' }}
                            </td>
                            <td class="py-2.5 px-3 text-slate-500 whitespace-nowrap">
                                {{ $rev->tanggal_review ? $rev->tanggal_review->format('d/m/Y') : '-' }}
                            </td>
                            <td class="py-2.5 px-3 whitespace-nowrap">
                                <span class="px-2 py-0.5 rounded-full border text-[10px] font-bold uppercase tracking-wider {{ $rev->isFinalized() ? 'bg-emerald-50 text-emerald-700 border-emerald-200' : 'bg-amber-50 text-amber-700 border-amber-200' }}">
                                    {{ $rev->status }}
                                </span>
                            </td>
                            <td class="py-2.5 px-3 text-right whitespace-nowrap space-x-1">
                                <a href="{{ route('dokumen-asesmen.ak06.edit', $rev->id) }}" class="inline-flex items-center gap-1 px-2.5 py-1 rounded-lg bg-amber-50 text-amber-700 hover:bg-amber-100 text-[11px] font-bold">
                                    <span>Kelola</span>
                                </a>
                                <a href="{{ route('dokumen-asesmen.ak06.cetak', $rev->id) }}" target="_blank" class="inline-flex items-center gap-1 px-2.5 py-1 rounded-lg border border-slate-200 text-slate-600 hover:bg-slate-50 text-[11px] font-semibold">
                                    <span>Cetak</span>
                                </a>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="text-center py-8 text-slate-400">
                                Belum ada dokumen peninjauan FR.AK.06 yang dibuat.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    <!-- MODAL BUAT REVIEW BARU -->
    <div x-show="modalOpen" class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-slate-900/60 backdrop-blur-xs" style="display: none;">
        <div class="bg-white rounded-2xl border border-slate-200 shadow-2xl max-w-md w-full p-5 space-y-4" @click.outside="modalOpen = false">
            <div class="flex items-center justify-between border-b border-slate-100 pb-3">
                <h4 class="font-bold text-sm text-slate-900">Inisialisasi Peninjauan Proses Asesmen (FR.AK.06)</h4>
                <button type="button" @click="modalOpen = false" class="text-slate-400 hover:text-slate-600 text-base font-bold p-1">
                    &times;
                </button>
            </div>

            <form method="POST" action="{{ route('dokumen-asesmen.ak06.create') }}" class="space-y-3 text-xs">
                @csrf
                <div>
                    <label class="block font-bold text-slate-700 mb-1">Pilih Skema Sertifikasi:</label>
                    <select name="skema_id" required class="w-full rounded-xl border-slate-200 focus:border-amber-500 focus:ring-amber-500 p-2">
                        @foreach($skemas as $sk)
                            <option value="{{ $sk->id }}">{{ $sk->nama_skema }} ({{ $sk->kode_skema }})</option>
                        @endforeach
                    </select>
                </div>

                <div>
                    <label class="block font-bold text-slate-700 mb-1">Lingkup Peninjauan:</label>
                    <select name="scope_type" class="w-full rounded-xl border-slate-200 focus:border-amber-500 focus:ring-amber-500 p-2">
                        <option value="skema">Skema Sertifikasi (Menyeluruh)</option>
                        <option value="kelompok">Kelompok Asesi (Satu Jadwal / Gelombang)</option>
                        <option value="individual">Individual Asesi</option>
                    </select>
                </div>

                <div class="flex items-center gap-2 pt-2 border-t border-slate-100">
                    <button type="button" @click="modalOpen = false" class="flex-1 py-2 rounded-xl bg-slate-100 text-slate-700 font-bold">
                        Batal
                    </button>
                    <button type="submit" class="flex-1 py-2 rounded-xl bg-amber-600 text-white font-bold hover:bg-amber-700 shadow-2xs">
                        Lanjutkan
                    </button>
                </div>
            </form>
        </div>
    </div>

</div>
@endsection
