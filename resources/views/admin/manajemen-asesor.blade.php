@extends('tata-letak.dasbor')

@section('judul', 'Data Asesor Penguji')

@push('css')
    <link rel="stylesheet" href="{{ asset('css/admin/dashboard-admin.css') }}">
    <style>
        .modal-overlay {
            backdrop-filter: blur(8px) !important;
            -webkit-backdrop-filter: blur(8px) !important;
            background: rgba(15, 23, 42, 0.65) !important;
            transition: opacity 0.25s cubic-bezier(0.16, 1, 0.3, 1), visibility 0.25s !important;
            overscroll-behavior: contain !important;
        }
        .modal-konten-modern {
            background: #ffffff;
            border-radius: 20px;
            box-shadow: 0 25px 50px -12px rgba(15, 23, 42, 0.25), 0 0 0 1px rgba(226, 232, 240, 0.9);
            border: none;
            overflow: hidden;
            display: flex;
            flex-direction: column;
            max-height: 88vh;
            height: auto;
            transform: scale(0.96) translateY(8px);
            transition: transform 0.25s cubic-bezier(0.16, 1, 0.3, 1);
            overscroll-behavior: contain;
        }
        .modal-konten-modern form,
        .modal-konten-modern > form {
            display: flex;
            flex-direction: column;
            flex: 1 1 auto;
            min-height: 0;
            overflow: hidden;
        }
        .modal-overlay.terbuka .modal-konten-modern {
            transform: scale(1) translateY(0);
        }
        .modal-scroll-body {
            flex: 1 1 auto;
            min-height: 0;
            overflow-y: auto;
            overscroll-behavior: contain;
            scrollbar-width: thin;
        }
    </style>
@endpush

@section('konten')
<div class="animasi-slide space-y-6">

    <!-- HEADER & ACTION BUTTON -->
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
        <div>
            <h1 class="text-xl sm:text-2xl font-extrabold text-slate-900 tracking-tight">
                Data Asesor Penguji
            </h1>
            <p class="text-xs sm:text-sm text-slate-500 mt-1">
                Kelola data akun asesor penguji, penugasan skema sertifikasi, nomor registrasi MET BNSP, dan status keaktifan
            </p>
        </div>
        <div>
            <button type="button" 
                    onclick="bukaModal('modalTambahAsesor')"
                    class="inline-flex items-center gap-2 px-4 py-2.5 rounded-xl bg-emerald-600 hover:bg-emerald-700 text-white font-semibold text-xs transition-colors shadow-2xs cursor-pointer">
                <i class="fa-solid fa-user-plus"></i>
                <span>Tambah Akun Asesor</span>
            </button>
        </div>
    </div>

    <!-- STATS COUNTER CARDS -->
    <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
        <div class="bg-white rounded-2xl border border-slate-200/90 p-4 shadow-2xs flex items-center justify-between">
            <div>
                <div class="text-xs font-semibold text-slate-500 uppercase tracking-wider">Total Asesor</div>
                <div class="text-2xl font-black text-slate-900 mt-1">{{ $totalAsesor ?? 0 }}</div>
            </div>
            <div class="w-10 h-10 rounded-xl bg-emerald-50 text-emerald-600 flex items-center justify-center text-lg font-bold">
                <i class="fa-solid fa-user-tie"></i>
            </div>
        </div>

        <div class="bg-white rounded-2xl border border-slate-200/90 p-4 shadow-2xs flex items-center justify-between">
            <div>
                <div class="text-xs font-semibold text-slate-500 uppercase tracking-wider">Status Aktif</div>
                <div class="text-2xl font-black text-emerald-600 mt-1">{{ $totalAktif ?? 0 }}</div>
            </div>
            <div class="w-10 h-10 rounded-xl bg-emerald-50 text-emerald-600 flex items-center justify-center text-lg font-bold">
                <i class="fa-solid fa-circle-check"></i>
            </div>
        </div>

        <div class="bg-white rounded-2xl border border-slate-200/90 p-4 shadow-2xs flex items-center justify-between">
            <div>
                <div class="text-xs font-semibold text-slate-500 uppercase tracking-wider">Status Non-Aktif</div>
                <div class="text-2xl font-black text-rose-600 mt-1">{{ $totalNonaktif ?? 0 }}</div>
            </div>
            <div class="w-10 h-10 rounded-xl bg-rose-50 text-rose-600 flex items-center justify-center text-lg font-bold">
                <i class="fa-solid fa-user-xmark"></i>
            </div>
        </div>
    </div>

    <!-- SEARCH & FILTER CARD -->
    <div class="bg-white rounded-2xl border border-slate-200/90 p-4 shadow-2xs">
        <form action="{{ route('admin.manajemen-asesor') }}" method="GET" class="flex flex-col sm:flex-row gap-3">
            <div class="relative flex-1">
                <i class="fa-solid fa-magnifying-glass absolute left-3.5 top-1/2 -translate-y-1/2 text-slate-400 text-xs"></i>
                <input type="text" 
                       name="q" 
                       class="w-full pl-9 pr-4 py-2 rounded-xl border border-slate-200 text-xs focus:outline-none focus:border-emerald-500 focus:ring-1 focus:ring-emerald-500 transition-all" 
                       placeholder="Cari nama asesor, email, skema, no. registrasi MET, atau no. hp..." 
                       value="{{ $kataKunci }}">
            </div>
            <div class="flex gap-2">
                <button type="submit" class="px-4 py-2 bg-slate-900 hover:bg-slate-800 text-white rounded-xl text-xs font-semibold transition-colors cursor-pointer">
                    Cari
                </button>
                @if($kataKunci)
                    <a href="{{ route('admin.manajemen-asesor') }}" class="px-4 py-2 bg-slate-100 hover:bg-slate-200 text-slate-700 rounded-xl text-xs font-semibold transition-colors text-center">
                        Reset
                    </a>
                @endif
            </div>
        </form>
    </div>

    <!-- TABEL ASESOR -->
    <div class="bg-white rounded-xl border border-[#dce7f2] shadow-xs overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-left text-xs text-slate-700">
                <thead class="bg-[#f0f6fb] border-b border-[#dce7f2] text-[11px] uppercase font-bold text-[#1c2d42] tracking-wider">
                    <tr>
                        <th class="px-4 py-3.5">Nama & Email Asesor</th>
                        <th class="px-4 py-3.5">Skema Sertifikasi (Role)</th>
                        <th class="px-4 py-3.5">No. Reg MET (BNSP)</th>
                        <th class="px-4 py-3.5">No. Telepon / WA</th>
                        <th class="px-4 py-3.5 text-center">Status Account</th>
                        <th class="px-4 py-3.5 text-right">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @forelse($asesorList as $asesor)
                        <tr class="hover:bg-[#f8fbfe] transition-colors">
                            <td class="px-4 py-3.5">
                                <div class="flex items-center gap-3">
                                    <div class="w-8 h-8 rounded-lg bg-blue-50 text-[#4682b4] border border-blue-200/80 flex items-center justify-center font-extrabold text-xs shrink-0">
                                        {{ strtoupper(substr($asesor->nama_lengkap, 0, 2)) }}
                                    </div>
                                    <div>
                                        <div class="font-bold text-slate-900 text-xs">{{ $asesor->nama_lengkap }}</div>
                                        <div class="text-[11px] text-slate-500">{{ $asesor->email }}</div>
                                    </div>
                                </div>
                            </td>
                            <td class="px-4 py-3.5 font-medium text-slate-800">
                                @if($asesor->skema)
                                    <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-lg bg-blue-50 text-blue-800 border border-blue-200/80 font-bold text-[11px]" title="{{ $asesor->skema->nama_skema }}">
                                        <i class="fa-solid fa-certificate text-[#4682b4] text-[10px]"></i>
                                        {{ $asesor->skema->nama_skema }}
                                    </span>
                                @else
                                    <span class="text-slate-400 italic">Semua Skema / Belum Diatur</span>
                                @endif
                            </td>
                            <td class="px-4 py-3.5 font-mono text-slate-800">
                                @if($asesor->nomor_registrasi)
                                    <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-md bg-[#edf5fc] border border-[#dce7f2] font-bold text-[11px] text-[#36648b]">
                                        <i class="fa-solid fa-id-card text-[#4682b4] text-[10px]"></i>
                                        {{ $asesor->nomor_registrasi }}
                                    </span>
                                @else
                                    <span class="text-slate-400 italic">Belum diisi</span>
                                @endif
                            </td>
                            <td class="px-4 py-3.5 text-slate-600">
                                {{ $asesor->nomor_telepon ?? '-' }}
                            </td>
                            <td class="px-4 py-3.5 text-center">
                                @if($asesor->aktif)
                                    <span class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-full text-[10px] font-bold bg-emerald-100 text-emerald-800 border border-emerald-200">
                                        <span class="w-1.5 h-1.5 rounded-full bg-emerald-500"></span>
                                        Aktif
                                    </span>
                                @else
                                    <span class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-full text-[10px] font-bold bg-rose-100 text-rose-800 border border-rose-200">
                                        <span class="w-1.5 h-1.5 rounded-full bg-rose-500"></span>
                                        Non-Aktif
                                    </span>
                                @endif
                            </td>
                            <td class="px-4 py-3.5 text-right">
                                <div class="flex items-center justify-end gap-1.5">
                                    <button type="button" 
                                            onclick="bukaModal('modalEditAsesor_{{ $asesor->id }}')"
                                            class="px-2.5 py-1.5 rounded-lg border border-slate-200 bg-white hover:bg-slate-50 text-slate-700 text-[11px] font-semibold transition-colors cursor-pointer">
                                        <i class="fa-solid fa-pen-to-square text-blue-600 mr-1"></i> Edit
                                    </button>

                                    <form action="{{ route('admin.asesor.hapus', $asesor->id) }}" method="POST" onsubmit="return confirm('Apakah Anda yakin ingin menghapus akun Asesor {{ $asesor->nama_lengkap }}?')" class="inline">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="px-2.5 py-1.5 rounded-lg border border-rose-200 bg-rose-50 hover:bg-rose-100 text-rose-700 text-[11px] font-semibold transition-colors cursor-pointer">
                                            <i class="fa-solid fa-trash mr-1"></i> Hapus
                                        </button>
                                    </form>
                                </div>

                                <!-- MODAL EDIT ASESOR -->
                                <div class="modal-overlay" id="modalEditAsesor_{{ $asesor->id }}">
                                    <div class="modal-konten-modern w-[95vw] max-w-2xl text-left">
                                        <!-- Modal Header -->
                                        <div class="px-6 py-4 sm:py-5 bg-white border-b border-slate-100 flex items-center justify-between gap-4 shrink-0">
                                            <div class="flex items-center gap-3.5">
                                                <div class="w-11 h-11 rounded-2xl bg-indigo-50 border border-indigo-100 text-indigo-600 flex items-center justify-center shrink-0 shadow-2xs">
                                                    <i class="fa-solid fa-user-pen text-base"></i>
                                                </div>
                                                <div>
                                                    <div class="flex items-center gap-2">
                                                        <h3 class="text-base sm:text-lg font-extrabold text-slate-900 tracking-tight">
                                                            Edit Akun Asesor
                                                        </h3>
                                                        <span class="px-2 py-0.5 rounded-full text-[10px] font-bold bg-indigo-50 text-indigo-700 border border-indigo-200">
                                                            ID #{{ $asesor->id }}
                                                        </span>
                                                    </div>
                                                    <p class="text-xs text-slate-500 mt-0.5">{{ $asesor->nama_lengkap }} ({{ $asesor->email }})</p>
                                                </div>
                                            </div>
                                            <button type="button" 
                                                    onclick="tutupModal('modalEditAsesor_{{ $asesor->id }}')" 
                                                    aria-label="Tutup modal"
                                                    class="w-9 h-9 rounded-xl bg-slate-100 hover:bg-slate-200 text-slate-400 hover:text-slate-700 transition-colors flex items-center justify-center cursor-pointer">
                                                <i class="fa-solid fa-xmark text-sm"></i>
                                            </button>
                                        </div>

                                        <form action="{{ route('admin.asesor.ubah', $asesor->id) }}" method="POST" class="flex flex-col flex-1 min-h-0 overflow-hidden">
                                            @csrf

                                            <div class="modal-scroll-body p-6 space-y-5 bg-slate-50/50">
                                                <!-- SEKSI 1: IDENTITAS & KREDENSIAL -->
                                                <div class="bg-white rounded-2xl border border-slate-200/90 p-5 space-y-4 shadow-2xs">
                                                    <div class="flex items-center gap-2 border-b border-slate-100 pb-3">
                                                        <span class="w-6 h-6 rounded-lg bg-indigo-50 text-indigo-700 font-extrabold text-xs flex items-center justify-center">1</span>
                                                        <h4 class="text-xs font-bold text-slate-800 uppercase tracking-wider">Identitas & Akses Login</h4>
                                                    </div>

                                                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                                                        <div>
                                                            <label class="block text-xs font-bold text-slate-700 mb-1.5">
                                                                Nama Lengkap & Gelar <span class="text-rose-500">*</span>
                                                            </label>
                                                            <div class="relative">
                                                                <i class="fa-regular fa-user absolute left-3.5 top-1/2 -translate-y-1/2 text-slate-400 text-xs pointer-events-none"></i>
                                                                <input type="text" 
                                                                       name="nama_lengkap" 
                                                                       required 
                                                                       value="{{ $asesor->nama_lengkap }}" 
                                                                       class="w-full pl-9 pr-3.5 py-2.5 bg-slate-50/60 border border-slate-200 rounded-xl text-xs font-semibold text-slate-800 focus:bg-white focus:outline-none focus:border-indigo-500 focus:ring-3 focus:ring-indigo-500/10 hover:border-slate-300 transition-all shadow-2xs">
                                                            </div>
                                                        </div>

                                                        <div>
                                                            <label class="block text-xs font-bold text-slate-700 mb-1.5">
                                                                Alamat Email Login <span class="text-rose-500">*</span>
                                                            </label>
                                                            <div class="relative">
                                                                <i class="fa-regular fa-envelope absolute left-3.5 top-1/2 -translate-y-1/2 text-slate-400 text-xs pointer-events-none"></i>
                                                                <input type="email" 
                                                                       name="email" 
                                                                       required 
                                                                       value="{{ $asesor->email }}" 
                                                                       class="w-full pl-9 pr-3.5 py-2.5 bg-slate-50/60 border border-slate-200 rounded-xl text-xs font-semibold text-slate-800 focus:bg-white focus:outline-none focus:border-indigo-500 focus:ring-3 focus:ring-indigo-500/10 hover:border-slate-300 transition-all shadow-2xs">
                                                            </div>
                                                        </div>

                                                        <div>
                                                            <label class="block text-xs font-bold text-slate-700 mb-1.5">
                                                                Kata Sandi Baru <span class="text-[10px] text-slate-400 font-normal">(Kosongkan jika tetap)</span>
                                                            </label>
                                                            <div class="relative">
                                                                <i class="fa-solid fa-lock absolute left-3.5 top-1/2 -translate-y-1/2 text-slate-400 text-xs pointer-events-none"></i>
                                                                <input type="password" 
                                                                       id="sandi_edit_asesor_{{ $asesor->id }}" 
                                                                       name="kata_sandi" 
                                                                       placeholder="Minimal 6 karakter" 
                                                                       class="w-full pl-9 pr-10 py-2.5 bg-slate-50/60 border border-slate-200 rounded-xl text-xs font-semibold text-slate-800 placeholder:text-slate-400 placeholder:font-normal focus:bg-white focus:outline-none focus:border-indigo-500 focus:ring-3 focus:ring-indigo-500/10 hover:border-slate-300 transition-all shadow-2xs">
                                                                <button type="button" 
                                                                        onclick="toggleSandiVisibility('sandi_edit_asesor_{{ $asesor->id }}', this)" 
                                                                        aria-label="Tampilkan sandi"
                                                                        class="absolute right-2.5 top-1/2 -translate-y-1/2 text-slate-400 hover:text-slate-600 p-1 rounded-lg transition-colors cursor-pointer">
                                                                    <i class="fa-regular fa-eye text-xs"></i>
                                                                </button>
                                                            </div>
                                                        </div>

                                                        <div>
                                                            <label class="block text-xs font-bold text-slate-700 mb-1.5">
                                                                No. Telepon / WhatsApp
                                                            </label>
                                                            <div class="relative">
                                                                <i class="fa-brands fa-whatsapp absolute left-3.5 top-1/2 -translate-y-1/2 text-emerald-500 text-xs pointer-events-none"></i>
                                                                <input type="text" 
                                                                       name="nomor_telepon" 
                                                                       value="{{ $asesor->nomor_telepon }}" 
                                                                       placeholder="Contoh: 081234567890" 
                                                                       class="w-full pl-9 pr-3.5 py-2.5 bg-slate-50/60 border border-slate-200 rounded-xl text-xs font-semibold text-slate-800 placeholder:text-slate-400 placeholder:font-normal focus:bg-white focus:outline-none focus:border-indigo-500 focus:ring-3 focus:ring-indigo-500/10 hover:border-slate-300 transition-all shadow-2xs">
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>

                                                <!-- SEKSI 2: KUALIFIKASI, SKEMA & KEAKTIFAN -->
                                                <div class="bg-white rounded-2xl border border-slate-200/90 p-5 space-y-4 shadow-2xs">
                                                    <div class="flex items-center gap-2 border-b border-slate-100 pb-3">
                                                        <span class="w-6 h-6 rounded-lg bg-indigo-50 text-indigo-700 font-extrabold text-xs flex items-center justify-center">2</span>
                                                        <h4 class="text-xs font-bold text-slate-800 uppercase tracking-wider">Kualifikasi, Skema & Keaktifan</h4>
                                                    </div>

                                                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                                                        <div>
                                                            <label class="block text-xs font-bold text-slate-700 mb-1.5">
                                                                No. Registrasi MET (BNSP)
                                                            </label>
                                                            <div class="relative">
                                                                <i class="fa-solid fa-id-card absolute left-3.5 top-1/2 -translate-y-1/2 text-slate-400 text-xs pointer-events-none"></i>
                                                                <input type="text" 
                                                                       name="nomor_registrasi" 
                                                                       value="{{ $asesor->nomor_registrasi }}" 
                                                                       placeholder="Contoh: MET.000.001234.2024" 
                                                                       class="w-full pl-9 pr-3.5 py-2.5 bg-slate-50/60 border border-slate-200 rounded-xl text-xs font-mono font-semibold text-slate-800 placeholder:text-slate-400 placeholder:font-normal placeholder:font-sans focus:bg-white focus:outline-none focus:border-indigo-500 focus:ring-3 focus:ring-indigo-500/10 hover:border-slate-300 transition-all shadow-2xs">
                                                            </div>
                                                        </div>

                                                        <div>
                                                            <label class="block text-xs font-bold text-slate-700 mb-1.5">
                                                                Skema Sertifikasi (Tugas Asesor)
                                                            </label>
                                                            <div class="relative">
                                                                <i class="fa-solid fa-layer-group absolute left-3.5 top-1/2 -translate-y-1/2 text-slate-400 text-xs pointer-events-none"></i>
                                                                <select name="skema_id" 
                                                                        class="w-full pl-9 pr-8 py-2.5 bg-slate-50/60 border border-slate-200 rounded-xl text-xs font-semibold text-slate-800 focus:bg-white focus:outline-none focus:border-indigo-500 focus:ring-3 focus:ring-indigo-500/10 hover:border-slate-300 transition-all shadow-2xs cursor-pointer appearance-none truncate">
                                                                    <option value="">-- Bebas / Semua Skema --</option>
                                                                    @foreach($skemaList as $skema)
                                                                        <option value="{{ $skema->id }}" {{ $asesor->skema_id == $skema->id ? 'selected' : '' }}>
                                                                            {{ $skema->kode_skema }} - {{ $skema->nama_skema }}
                                                                        </option>
                                                                    @endforeach
                                                                </select>
                                                                <i class="fa-solid fa-chevron-down absolute right-3.5 top-1/2 -translate-y-1/2 text-slate-400 text-[10px] pointer-events-none"></i>
                                                            </div>
                                                        </div>

                                                        <div class="sm:col-span-2 pt-1">
                                                            <label class="flex items-center gap-3.5 p-3.5 rounded-xl border border-slate-200 hover:border-emerald-300 bg-slate-50/60 hover:bg-emerald-50/20 cursor-pointer transition-all">
                                                                <input type="checkbox" 
                                                                       name="aktif" 
                                                                       value="1" 
                                                                       {{ $asesor->aktif ? 'checked' : '' }} 
                                                                       class="w-4 h-4 text-emerald-600 rounded border-slate-300 focus:ring-emerald-500 cursor-pointer">
                                                                <div>
                                                                    <div class="text-xs font-bold text-slate-800 flex items-center gap-2">
                                                                        <span>Status Akun Asesor Aktif</span>
                                                                        @if($asesor->aktif)
                                                                            <span class="px-2 py-0.5 rounded-full text-[10px] font-bold bg-emerald-100 text-emerald-700">Aktif</span>
                                                                        @else
                                                                            <span class="px-2 py-0.5 rounded-full text-[10px] font-bold bg-rose-100 text-rose-700">Non-Aktif</span>
                                                                        @endif
                                                                    </div>
                                                                    <div class="text-[11px] text-slate-400 mt-0.5">Asesor dapat login ke portal dasbor, menerima jadwal uji, dan melakukan asesmen mandiri.</div>
                                                                </div>
                                                            </label>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>

                                            <!-- Modal Footer -->
                                            <div class="px-6 py-4 bg-white border-t border-slate-200/80 flex items-center justify-end gap-2.5 shrink-0">
                                                <button type="button" 
                                                        onclick="tutupModal('modalEditAsesor_{{ $asesor->id }}')" 
                                                        class="px-4 py-2.5 rounded-xl bg-white hover:bg-slate-100 text-slate-700 font-semibold text-xs border border-slate-200 shadow-2xs transition-colors cursor-pointer">
                                                    Batal
                                                </button>
                                                <button type="submit" 
                                                        class="inline-flex items-center gap-2 px-5 py-2.5 rounded-xl bg-indigo-600 hover:bg-indigo-700 active:scale-[0.98] text-white font-bold text-xs shadow-xs transition-all cursor-pointer">
                                                    <i class="fa-solid fa-check text-xs"></i>
                                                    <span>Simpan Perubahan</span>
                                                </button>
                                            </div>
                                        </form>
                                    </div>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="px-4 py-8 text-center text-slate-400">
                                <div class="w-12 h-12 rounded-full bg-slate-100 flex items-center justify-center mx-auto mb-2 text-slate-400 text-lg">
                                    <i class="fa-solid fa-user-tie"></i>
                                </div>
                                <div class="font-bold text-slate-600 text-xs">Belum Ada Data Asesor</div>
                                <div class="text-[11px] text-slate-400">Silakan klik tombol "Tambah Akun Asesor" di atas untuk menambahkan akun baru.</div>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if($asesorList->hasPages())
            <div class="p-4 border-t border-slate-100 bg-slate-50/50">
                {{ $asesorList->withQueryString()->links() }}
            </div>
        @endif
    </div>

</div>

<!-- MODAL TAMBAH ASESOR -->
<div class="modal-overlay" id="modalTambahAsesor">
    <div class="modal-konten-modern w-[95vw] max-w-2xl">
        <!-- Modal Header -->
        <div class="px-6 py-4 sm:py-5 bg-white border-b border-slate-100 flex items-center justify-between gap-4 shrink-0">
            <div class="flex items-center gap-3.5">
                <div class="w-11 h-11 rounded-2xl bg-emerald-50 border border-emerald-100 text-emerald-600 flex items-center justify-center shrink-0 shadow-2xs">
                    <i class="fa-solid fa-user-plus text-base"></i>
                </div>
                <div>
                    <div class="flex items-center gap-2">
                        <h3 class="text-base sm:text-lg font-extrabold text-slate-900 tracking-tight">
                            Tambah Akun Asesor Baru
                        </h3>
                        <span class="px-2 py-0.5 rounded-full text-[10px] font-bold bg-emerald-50 text-emerald-700 border border-emerald-200">
                            BNSP
                        </span>
                    </div>
                    <p class="text-xs text-slate-500 mt-0.5">Daftarkan akun asesor kompetensi baru & penugasan skema sertifikasi</p>
                </div>
            </div>
            <button type="button" 
                    onclick="tutupModal('modalTambahAsesor')" 
                    aria-label="Tutup modal"
                    class="w-9 h-9 rounded-xl bg-slate-100 hover:bg-slate-200 text-slate-400 hover:text-slate-700 transition-colors flex items-center justify-center cursor-pointer">
                <i class="fa-solid fa-xmark text-sm"></i>
            </button>
        </div>

        <form action="{{ route('admin.asesor.simpan') }}" method="POST" class="flex flex-col flex-1 min-h-0 overflow-hidden">
            @csrf

            <div class="modal-scroll-body p-6 space-y-5 bg-slate-50/50">
                <!-- SEKSI 1: AKUN & KREDENSIAL -->
                <div class="bg-white rounded-2xl border border-slate-200/90 p-5 space-y-4 shadow-2xs">
                    <div class="flex items-center gap-2 border-b border-slate-100 pb-3">
                        <span class="w-6 h-6 rounded-lg bg-emerald-50 text-emerald-700 font-extrabold text-xs flex items-center justify-center">1</span>
                        <h4 class="text-xs font-bold text-slate-800 uppercase tracking-wider">Identitas & Akses Login</h4>
                    </div>

                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                        <div>
                            <label class="block text-xs font-bold text-slate-700 mb-1.5">
                                Nama Lengkap & Gelar <span class="text-rose-500">*</span>
                            </label>
                            <div class="relative">
                                <i class="fa-regular fa-user absolute left-3.5 top-1/2 -translate-y-1/2 text-slate-400 text-xs pointer-events-none"></i>
                                <input type="text" 
                                       name="nama_lengkap" 
                                       required 
                                       placeholder="Contoh: Drs. Ahmad Subagja, M.Pd., Met." 
                                       class="w-full pl-9 pr-3.5 py-2.5 bg-slate-50/60 border border-slate-200 rounded-xl text-xs font-semibold text-slate-800 placeholder:text-slate-400 placeholder:font-normal focus:bg-white focus:outline-none focus:border-emerald-500 focus:ring-3 focus:ring-emerald-500/10 hover:border-slate-300 transition-all shadow-2xs">
                            </div>
                        </div>

                        <div>
                            <label class="block text-xs font-bold text-slate-700 mb-1.5">
                                Alamat Email Login <span class="text-rose-500">*</span>
                            </label>
                            <div class="relative">
                                <i class="fa-regular fa-envelope absolute left-3.5 top-1/2 -translate-y-1/2 text-slate-400 text-xs pointer-events-none"></i>
                                <input type="email" 
                                       name="email" 
                                       required 
                                       placeholder="asesor@smkn1gunungputri.sch.id" 
                                       class="w-full pl-9 pr-3.5 py-2.5 bg-slate-50/60 border border-slate-200 rounded-xl text-xs font-semibold text-slate-800 placeholder:text-slate-400 placeholder:font-normal focus:bg-white focus:outline-none focus:border-emerald-500 focus:ring-3 focus:ring-emerald-500/10 hover:border-slate-300 transition-all shadow-2xs">
                            </div>
                        </div>

                        <div>
                            <label class="block text-xs font-bold text-slate-700 mb-1.5">
                                Kata Sandi Initial <span class="text-rose-500">*</span>
                            </label>
                            <div class="relative">
                                <i class="fa-solid fa-lock absolute left-3.5 top-1/2 -translate-y-1/2 text-slate-400 text-xs pointer-events-none"></i>
                                <input type="password" 
                                       id="sandi_tambah_asesor" 
                                       name="kata_sandi" 
                                       required 
                                       placeholder="Minimal 6 karakter" 
                                       class="w-full pl-9 pr-10 py-2.5 bg-slate-50/60 border border-slate-200 rounded-xl text-xs font-semibold text-slate-800 placeholder:text-slate-400 placeholder:font-normal focus:bg-white focus:outline-none focus:border-emerald-500 focus:ring-3 focus:ring-emerald-500/10 hover:border-slate-300 transition-all shadow-2xs">
                                <button type="button" 
                                        onclick="toggleSandiVisibility('sandi_tambah_asesor', this)" 
                                        aria-label="Tampilkan sandi"
                                        class="absolute right-2.5 top-1/2 -translate-y-1/2 text-slate-400 hover:text-slate-600 p-1 rounded-lg transition-colors cursor-pointer">
                                    <i class="fa-regular fa-eye text-xs"></i>
                                </button>
                            </div>
                        </div>

                        <div>
                            <label class="block text-xs font-bold text-slate-700 mb-1.5">
                                No. Telepon / WhatsApp
                            </label>
                            <div class="relative">
                                <i class="fa-brands fa-whatsapp absolute left-3.5 top-1/2 -translate-y-1/2 text-emerald-500 text-xs pointer-events-none"></i>
                                <input type="text" 
                                       name="nomor_telepon" 
                                       placeholder="Contoh: 081234567890" 
                                       class="w-full pl-9 pr-3.5 py-2.5 bg-slate-50/60 border border-slate-200 rounded-xl text-xs font-semibold text-slate-800 placeholder:text-slate-400 placeholder:font-normal focus:bg-white focus:outline-none focus:border-emerald-500 focus:ring-3 focus:ring-emerald-500/10 hover:border-slate-300 transition-all shadow-2xs">
                            </div>
                        </div>
                    </div>
                </div>

                <!-- SEKSI 2: REGISTRASI & PENUGASAN SKEMA -->
                <div class="bg-white rounded-2xl border border-slate-200/90 p-5 space-y-4 shadow-2xs">
                    <div class="flex items-center gap-2 border-b border-slate-100 pb-3">
                        <span class="w-6 h-6 rounded-lg bg-emerald-50 text-emerald-700 font-extrabold text-xs flex items-center justify-center">2</span>
                        <h4 class="text-xs font-bold text-slate-800 uppercase tracking-wider">Kualifikasi & Penugasan Skema</h4>
                    </div>

                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                        <div>
                            <label class="block text-xs font-bold text-slate-700 mb-1.5">
                                No. Registrasi MET (BNSP)
                            </label>
                            <div class="relative">
                                <i class="fa-solid fa-id-card absolute left-3.5 top-1/2 -translate-y-1/2 text-slate-400 text-xs pointer-events-none"></i>
                                <input type="text" 
                                       name="nomor_registrasi" 
                                       placeholder="Contoh: MET.000.001234.2024" 
                                       class="w-full pl-9 pr-3.5 py-2.5 bg-slate-50/60 border border-slate-200 rounded-xl text-xs font-mono font-semibold text-slate-800 placeholder:text-slate-400 placeholder:font-normal placeholder:font-sans focus:bg-white focus:outline-none focus:border-emerald-500 focus:ring-3 focus:ring-emerald-500/10 hover:border-slate-300 transition-all shadow-2xs">
                            </div>
                        </div>

                        <div>
                            <label class="block text-xs font-bold text-slate-700 mb-1.5">
                                Skema Sertifikasi (Tugas Asesor)
                            </label>
                            <div class="relative">
                                <i class="fa-solid fa-layer-group absolute left-3.5 top-1/2 -translate-y-1/2 text-slate-400 text-xs pointer-events-none"></i>
                                <select name="skema_id" 
                                        class="w-full pl-9 pr-8 py-2.5 bg-slate-50/60 border border-slate-200 rounded-xl text-xs font-semibold text-slate-800 focus:bg-white focus:outline-none focus:border-emerald-500 focus:ring-3 focus:ring-emerald-500/10 hover:border-slate-300 transition-all shadow-2xs cursor-pointer appearance-none truncate">
                                    <option value="">-- Bebas / Semua Skema Sertifikasi --</option>
                                    @foreach($skemaList as $skema)
                                        <option value="{{ $skema->id }}">
                                            {{ $skema->kode_skema }} - {{ $skema->nama_skema }}
                                        </option>
                                    @endforeach
                                </select>
                                <i class="fa-solid fa-chevron-down absolute right-3.5 top-1/2 -translate-y-1/2 text-slate-400 text-[10px] pointer-events-none"></i>
                            </div>
                        </div>
                    </div>

                    <!-- Info Alert -->
                    <div class="flex items-start gap-3 p-3.5 rounded-xl bg-emerald-50/70 border border-emerald-100 text-slate-600 text-xs leading-relaxed">
                        <div class="w-6 h-6 rounded-lg bg-emerald-100 text-emerald-700 flex items-center justify-center shrink-0 mt-0.5 text-xs font-bold">
                            <i class="fa-solid fa-info"></i>
                        </div>
                        <div class="text-[11px] text-slate-600">
                            <span class="font-bold text-slate-800">Catatan:</span> Satu asesor dapat difokuskan untuk 1 skema sertifikasi tertentu atau dibebaskan untuk semua skema. Akun yang baru dibuat otomatis aktif dan dapat langsung digunakan untuk login dan pengujian.
                        </div>
                    </div>
                </div>
            </div>

            <!-- Modal Footer -->
            <div class="px-6 py-4 bg-white border-t border-slate-200/80 flex items-center justify-end gap-2.5 shrink-0">
                <button type="button" 
                        onclick="tutupModal('modalTambahAsesor')" 
                        class="px-4 py-2.5 rounded-xl bg-white hover:bg-slate-100 text-slate-700 font-semibold text-xs border border-slate-200 shadow-2xs transition-colors cursor-pointer">
                    Batal
                </button>
                <button type="submit" 
                        class="inline-flex items-center gap-2 px-5 py-2.5 rounded-xl bg-emerald-600 hover:bg-emerald-700 active:scale-[0.98] text-white font-bold text-xs shadow-xs transition-all cursor-pointer">
                    <i class="fa-solid fa-user-plus text-xs"></i>
                    <span>Simpan Akun Asesor</span>
                </button>
            </div>
        </form>
    </div>
</div>
@endsection

@push('js')
<script>
    function toggleSandiVisibility(inputId, btn) {
        const input = document.getElementById(inputId);
        if (!input) return;
        const icon = btn.querySelector('i');
        if (input.type === 'password') {
            input.type = 'text';
            if (icon) {
                icon.classList.remove('fa-eye');
                icon.classList.add('fa-eye-slash');
            }
        } else {
            input.type = 'password';
            if (icon) {
                icon.classList.remove('fa-eye-slash');
                icon.classList.add('fa-eye');
            }
        }
    }

    document.addEventListener('DOMContentLoaded', () => {
        // Klik di luar konten modal (pada overlay) untuk menutup modal
        document.querySelectorAll('.modal-overlay').forEach(overlay => {
            overlay.addEventListener('click', (e) => {
                if (e.target === overlay) {
                    overlay.classList.remove('terbuka');
                    if (typeof sinkronkanStatusBodyModal === 'function') {
                        sinkronkanStatusBodyModal();
                    }
                }
            });
        });
    });
</script>
@endpush
