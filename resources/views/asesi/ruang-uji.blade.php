@extends('tata-letak.dasbor')

@section('judul', 'Ruang Ujian Online Asesmen')

@section('konten')
<div class="max-w-6xl mx-auto px-2 sm:px-4 py-3 space-y-4">
    <!-- Breadcrumb -->
    <div class="flex items-center gap-2 text-xs text-slate-500 pb-1">
        <a href="{{ route('asesi.dashboard') }}" class="hover:text-blue-600 font-medium">Dashboard</a>
        <span>/</span>
        <a href="{{ route('asesi.tahapan', ['step' => 5, 'pendaftaran_id' => $pendaftaran->id]) }}" class="hover:text-blue-600 font-medium">Tahapan Asesmen</a>
        <span>/</span>
        <span class="text-slate-800 font-bold">Ruang Ujian Online</span>
    </div>

    <!-- Alert Notifikasi -->
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

    @if(session('warning'))
        <div class="p-3.5 bg-amber-50 border border-amber-200 rounded-xl text-amber-800 text-xs font-semibold shadow-2xs">
            <span>{{ session('warning') }}</span>
        </div>
    @endif

    <!-- Halaman Ujian Baru -->
    @include('asesi.komponen.halaman-ujian-asesi')
</div>
@endsection

