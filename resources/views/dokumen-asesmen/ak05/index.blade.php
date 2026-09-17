@extends('tata-letak.dasbor')

@section('judul', 'FR.AK.05 - Laporan Asesmen')

@section('konten')
<div class="space-y-4" x-data="{ modalOpen: false }" x-cloak>

    <!-- TOP BREADCRUMB & HEADER -->
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3 border-b border-slate-200/80 pb-3">
        <div>
            <div class="flex items-center gap-2 text-xs text-slate-500 mb-1">
                <a href="{{ route('dokumen-asesmen.index') }}" class="hover:text-blue-600">Dokumen Asesmen</a>
                <span>/</span>
                <span class="px-2 py-0.5 rounded-md bg-purple-50 text-purple-700 font-mono font-bold text-[10px]">FR.AK.05</span>
                <span class="text-slate-800 font-bold hidden md:inline">Laporan Asesmen</span>
            </div>
            <h1 class="text-base sm:text-lg font-black text-slate-900 tracking-tight flex items-center gap-2">
                <span>FR.AK.05 &bull; Laporan Asesmen Kelompok / Skema</span>
            </h1>
            <p class="text-xs text-slate-500 mt-0.5">
                Dokumen pelaporan rekapitulasi hasil asesmen multi-peserta, aspek positif/negatif, dan rekomendasi perbaikan.
            </p>
        </div>

        <div class="flex items-center gap-2">
            <button type="button" @click="modalOpen = true" class="inline-flex items-center px-3.5 py-2 rounded-xl bg-purple-600 hover:bg-purple-700 text-white font-bold text-xs transition-colors shadow-2xs">
                <span>Buat Laporan Baru</span>
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

    <!-- LIST OF AK.05 REPORTS -->
    <div class="bg-white rounded-2xl border border-slate-200/90 shadow-2xs overflow-hidden">
        <div class="px-4 py-3 border-b border-slate-100 flex items-center justify-between">
            <div class="text-xs font-bold text-slate-800">
                Arsip Laporan Asesmen (FR.AK.05)
            </div>
            <span class="px-2 py-0.5 rounded-full bg-slate-100 text-slate-600 text-[10px] font-extrabold">{{ $laporans->count() }} Laporan</span>
        </div>

        <div class="overflow-x-auto">
            <table class="w-full text-left border-collapse text-xs">
                <thead>
                    <tr class="bg-slate-50/80 text-slate-500 text-[10px] font-bold uppercase tracking-wider border-b border-slate-200">
                        <th class="py-2.5 px-3">No. Laporan</th>
                        <th class="py-2.5 px-3">Skema Sertifikasi</th>
                        <th class="py-2.5 px-3">Jadwal / Sesi</th>
                        <th class="py-2.5 px-3">Asesor Pelapor</th>
                        <th class="py-2.5 px-3 text-center">Rekap Hasil</th>
                        <th class="py-2.5 px-3">Tgl Laporan</th>
                        <th class="py-2.5 px-3">Status</th>
                        <th class="py-2.5 px-3 text-right">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100 font-medium">
                    @forelse($laporans as $rep)
                        <tr class="hover:bg-slate-50/50">
                            <td class="py-2.5 px-3 font-mono font-bold text-slate-800 whitespace-nowrap">
                                {{ $rep->nomor_laporan }}
                            </td>
                            <td class="py-2.5 px-3 text-slate-700 font-semibold">
                                {{ $rep->skema ? $rep->skema->nama_skema : '-' }}
                            </td>
                            <td class="py-2.5 px-3 text-slate-600">
                                {{ $rep->jadwal ? $rep->jadwal->kode_jadwal . ' (' . $rep->jadwal->tanggal_uji->format('d/m/Y') . ')' : 'Seluruh Sesi' }}
                            </td>
                            <td class="py-2.5 px-3 text-slate-800">
                                {{ $rep->asesor ? $rep->asesor->nama_lengkap : '-' }}
                            </td>
                            <td class="py-2.5 px-3 text-center whitespace-nowrap">
                                <span class="px-2 py-0.5 rounded-md bg-emerald-50 text-emerald-700 font-bold text-[11px] border border-emerald-200">
                                    {{ $rep->total_k }} K
                                </span>
                                <span class="px-2 py-0.5 rounded-md bg-rose-50 text-rose-700 font-bold text-[11px] border border-rose-200 ml-1">
                                    {{ $rep->total_bk }} BK
                                </span>
                                <span class="text-slate-400 text-[10px] ml-1">/ {{ $rep->total_asesi }} Total</span>
                            </td>
                            <td class="py-2.5 px-3 text-slate-500 whitespace-nowrap">
                                {{ $rep->tanggal_laporan ? $rep->tanggal_laporan->format('d/m/Y') : '-' }}
                            </td>
                            <td class="py-2.5 px-3 whitespace-nowrap">
                                <span class="px-2 py-0.5 rounded-full border text-[10px] font-bold uppercase tracking-wider {{ $rep->isFinalized() ? 'bg-emerald-50 text-emerald-700 border-emerald-200' : 'bg-amber-50 text-amber-700 border-amber-200' }}">
                                    {{ $rep->status }}
                                </span>
                            </td>
                            <td class="py-2.5 px-3 text-right whitespace-nowrap space-x-1">
                                <a href="{{ route('dokumen-asesmen.ak05.edit', $rep->id) }}" class="inline-flex items-center px-2.5 py-1 rounded-lg bg-purple-50 text-purple-700 hover:bg-purple-100 text-[11px] font-bold">
                                    <span>Kelola</span>
                                </a>
                                <a href="{{ route('dokumen-asesmen.ak05.cetak', $rep->id) }}" target="_blank" class="inline-flex items-center px-2 py-1 rounded-lg border border-slate-200 text-slate-600 hover:bg-slate-50 text-[11px] font-semibold">
                                    <span>Cetak</span>
                                </a>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="8" class="text-center py-8 text-slate-400">
                                Belum ada laporan FR.AK.05 yang dibuat. Klik tombol "Buat Laporan Baru" di atas.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    <!-- MODAL BUAT LAPORAN BARU -->
    <div x-show="modalOpen" class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-slate-900/60 backdrop-blur-xs" style="display: none;">
        <div class="bg-white rounded-2xl border border-slate-200 shadow-2xl max-w-md w-full p-5 space-y-4" @click.outside="modalOpen = false">
            <div class="flex items-center justify-between border-b border-slate-100 pb-3">
                <h4 class="font-bold text-sm text-slate-900">Buat Laporan Asesmen Baru (FR.AK.05)</h4>
                <button type="button" @click="modalOpen = false" class="text-slate-400 hover:text-slate-600 text-xs font-semibold px-2 py-1 rounded-lg hover:bg-slate-50">
                    Tutup
                </button>
            </div>

            <form method="POST" action="{{ route('dokumen-asesmen.ak05.create') }}" class="space-y-3 text-xs">
                @csrf
                <div>
                    <label class="block font-bold text-slate-700 mb-1">Pilih Skema Sertifikasi:</label>
                    <select name="skema_id" required class="w-full rounded-xl border-slate-200 focus:border-purple-500 focus:ring-purple-500 p-2">
                        @foreach($skemas as $sk)
                            <option value="{{ $sk->id }}">{{ $sk->nama_skema }} ({{ $sk->kode_skema }})</option>
                        @endforeach
                    </select>
                </div>

                <div>
                    <label class="block font-bold text-slate-700 mb-1">Pilih Jadwal / Sesi (Opsional):</label>
                    <select name="jadwal_id" class="w-full rounded-xl border-slate-200 focus:border-purple-500 focus:ring-purple-500 p-2">
                        <option value="">Semua Jadwal / Peserta Skema Saya</option>
                        @foreach($jadwals as $jd)
                            <option value="{{ $jd->id }}">
                                {{ $jd->kode_jadwal }} &bull; {{ $jd->tanggal_uji->format('d/m/Y') }} ({{ $jd->nama_tuk }})
                            </option>
                        @endforeach
                    </select>
                </div>

                <div class="flex items-center gap-2 pt-2 border-t border-slate-100">
                    <button type="button" @click="modalOpen = false" class="flex-1 py-2 rounded-xl bg-slate-100 text-slate-700 font-bold">
                        Batal
                    </button>
                    <button type="submit" class="flex-1 py-2 rounded-xl bg-purple-600 text-white font-bold hover:bg-purple-700 shadow-2xs">
                        Lanjutkan
                    </button>
                </div>
            </form>
        </div>
    </div>

</div>
@endsection
