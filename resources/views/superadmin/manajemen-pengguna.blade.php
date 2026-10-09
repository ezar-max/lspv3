@extends('tata-letak.dasbor')

@section('judul', 'Manajemen Pengguna & Hak Akses - Super Admin')

@section('konten')
<div class="space-y-6 max-w-7xl mx-auto pb-12" x-data="{ 
    modalTambah: false,
    peranTambah: 'asesi',
    activeEditModal: null,
    showPasswordEdit: {},
    showPasswordAdd: false
}">

    <!-- FLASH MESSAGE -->
    @if(session('sukses'))
        <div class="p-4 rounded-2xl bg-emerald-50 border border-emerald-200 text-emerald-800 text-sm shadow-xs flex items-center justify-between">
            <div>
                <span class="font-bold">Berhasil:</span> {{ session('sukses') }}
            </div>
            <button type="button" @click="$el.parentElement.remove()" class="text-emerald-600 hover:text-emerald-900 font-bold px-2 py-1 text-sm">Tutup</button>
        </div>
    @endif

    @if(session('error'))
        <div class="p-4 rounded-2xl bg-rose-50 border border-rose-200 text-rose-800 text-sm shadow-xs flex items-center justify-between">
            <div>
                <span class="font-bold">Perhatian:</span> {{ session('error') }}
            </div>
            <button type="button" @click="$el.parentElement.remove()" class="text-rose-600 hover:text-rose-900 font-bold px-2 py-1 text-sm">Tutup</button>
        </div>
    @endif

    @if(isset($errors) && $errors->any())
        <div class="p-4 rounded-2xl bg-rose-50 border border-rose-200 text-rose-800 text-sm shadow-xs">
            <div class="font-bold mb-1">Terjadi Kesalahan Input:</div>
            <ul class="list-disc list-inside space-y-0.5 text-xs text-rose-700">
                @foreach($errors->all() as $err)
                    <li>{{ $err }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <!-- HEADER SECTION (BRIGHT THEME, NO ICONS) -->
    <div class="bg-white rounded-3xl p-6 sm:p-8 border border-slate-200 shadow-xs">
        <div class="flex flex-col md:flex-row md:items-center justify-between gap-6">
            <div class="space-y-2">
                <div class="inline-block px-3 py-1 rounded-md bg-blue-50 border border-blue-200 text-blue-700 text-xs font-bold tracking-wide uppercase">
                    Master Access Control
                </div>
                <h1 class="text-2xl sm:text-3xl font-black tracking-tight text-slate-900 leading-tight">
                    Manajemen Akun Pengguna
                </h1>
                <p class="text-slate-600 text-xs sm:text-sm max-w-2xl leading-relaxed">
                    Pusat kendali akun pengguna tingkat tinggi. Anda memiliki wewenang penuh untuk membuat, memperbarui kredensial, serta mengedit detail profil Asesi dan nomor registrasi Asesor.
                </p>
            </div>

            <div class="flex items-center gap-3 shrink-0">
                <button type="button" 
                        @click="modalTambah = true; peranTambah = 'asesi'"
                        class="px-5 py-2.5 rounded-xl bg-blue-600 hover:bg-blue-700 text-white font-bold text-xs sm:text-sm shadow-xs transition-colors cursor-pointer">
                    Tambah Akun Baru
                </button>
            </div>
        </div>
    </div>

    <!-- METRICS CARDS (BRIGHT THEME, NO ICONS) -->
    <div class="grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-5 gap-3.5 sm:gap-4">
        <!-- TOTAL -->
        <a href="{{ route('superadmin.manajemen-pengguna') }}" 
           class="p-4 rounded-2xl bg-white border border-slate-200 shadow-xs hover:border-slate-300 transition-colors {{ empty($peranFilter) ? 'ring-2 ring-slate-800' : '' }}">
            <div class="text-xs font-semibold text-slate-500 mb-1">Total Akun</div>
            <div class="text-2xl font-black text-slate-900">{{ number_format($statistik['total'] ?? 0) }}</div>
            <div class="text-[11px] text-slate-400 mt-0.5">Semua pengguna</div>
        </a>

        <!-- SUPER ADMIN -->
        <a href="{{ route('superadmin.manajemen-pengguna', ['peran' => 'superadmin']) }}" 
           class="p-4 rounded-2xl bg-white border border-slate-200 shadow-xs hover:border-slate-300 transition-colors {{ $peranFilter === 'superadmin' ? 'ring-2 ring-blue-600' : '' }}">
            <div class="text-xs font-semibold text-slate-700 mb-1">Super Admin</div>
            <div class="text-2xl font-black text-slate-900">{{ number_format($statistik['superadmin'] ?? 0) }}</div>
            <div class="text-[11px] text-slate-400 mt-0.5">Akses master</div>
        </a>

        <!-- ADMIN -->
        <a href="{{ route('superadmin.manajemen-pengguna', ['peran' => 'admin']) }}" 
           class="p-4 rounded-2xl bg-white border border-slate-200 shadow-xs hover:border-slate-300 transition-colors {{ $peranFilter === 'admin' ? 'ring-2 ring-emerald-600' : '' }}">
            <div class="text-xs font-semibold text-emerald-800 mb-1">Admin LSP</div>
            <div class="text-2xl font-black text-emerald-950">{{ number_format($statistik['admin'] ?? 0) }}</div>
            <div class="text-[11px] text-slate-400 mt-0.5">Operasional uji</div>
        </a>

        <!-- ASESOR -->
        <a href="{{ route('superadmin.manajemen-pengguna', ['peran' => 'asesor']) }}" 
           class="p-4 rounded-2xl bg-white border border-slate-200 shadow-xs hover:border-slate-300 transition-colors {{ $peranFilter === 'asesor' ? 'ring-2 ring-amber-600' : '' }}">
            <div class="text-xs font-semibold text-amber-800 mb-1">Asesor Penguji</div>
            <div class="text-2xl font-black text-amber-950">{{ number_format($statistik['asesor'] ?? 0) }}</div>
            <div class="text-[11px] text-slate-400 mt-0.5">Penilai kompetensi</div>
        </a>

        <!-- ASESI -->
        <a href="{{ route('superadmin.manajemen-pengguna', ['peran' => 'asesi']) }}" 
           class="p-4 rounded-2xl bg-white border border-slate-200 shadow-xs hover:border-slate-300 transition-colors col-span-2 sm:col-span-1 {{ $peranFilter === 'asesi' ? 'ring-2 ring-blue-600' : '' }}">
            <div class="text-xs font-semibold text-blue-800 mb-1">Asesi (Peserta)</div>
            <div class="text-2xl font-black text-blue-950">{{ number_format($statistik['asesi'] ?? 0) }}</div>
            <div class="text-[11px] text-slate-400 mt-0.5">Peserta sertifikasi</div>
        </a>
    </div>

    <!-- FILTER & SEARCH BAR (BRIGHT THEME, NO ICONS) -->
    <div class="bg-white rounded-3xl p-4 sm:p-5 border border-slate-200 shadow-xs">
        <form action="{{ route('superadmin.manajemen-pengguna') }}" method="GET" class="flex flex-col md:flex-row items-stretch md:items-center justify-between gap-3">
            <div class="flex-1 flex flex-col sm:flex-row items-center gap-3">
                <!-- SEARCH INPUT -->
                <div class="w-full sm:flex-1">
                    <input type="text" 
                           name="q" 
                           value="{{ $kataKunci }}" 
                           placeholder="Cari nama, email, NIK, No. Registrasi, atau sekolah/instansi..." 
                           class="w-full px-4 py-2.5 rounded-xl bg-slate-50 border border-slate-200 text-xs sm:text-sm text-slate-800 placeholder-slate-400 focus:bg-white focus:outline-hidden focus:border-blue-600 focus:ring-1 focus:ring-blue-600 transition-all">
                </div>

                <!-- ROLE DROPDOWN -->
                <div class="w-full sm:w-56">
                    <select name="peran" 
                            class="w-full px-3.5 py-2.5 rounded-xl bg-slate-50 border border-slate-200 text-xs sm:text-sm text-slate-700 font-medium focus:bg-white focus:outline-hidden focus:border-blue-600 focus:ring-1 focus:ring-blue-600 transition-all">
                        <option value="">Semua Peran (Role)</option>
                        <option value="superadmin" {{ $peranFilter == 'superadmin' ? 'selected' : '' }}>Super Admin</option>
                        <option value="admin" {{ $peranFilter == 'admin' ? 'selected' : '' }}>Admin LSP</option>
                        <option value="asesor" {{ $peranFilter == 'asesor' ? 'selected' : '' }}>Asesor Penguji</option>
                        <option value="asesi" {{ $peranFilter == 'asesi' ? 'selected' : '' }}>Asesi (Peserta Uji)</option>
                    </select>
                </div>
            </div>

            <div class="flex items-center gap-2 shrink-0">
                <button type="submit" 
                        class="flex-1 sm:flex-none px-5 py-2.5 rounded-xl bg-blue-600 hover:bg-blue-700 text-white font-semibold text-xs sm:text-sm transition-colors cursor-pointer">
                    Terapkan Filter
                </button>
                @if(!empty($kataKunci) || !empty($peranFilter))
                    <a href="{{ route('superadmin.manajemen-pengguna') }}" 
                       class="px-4 py-2.5 rounded-xl bg-slate-100 hover:bg-slate-200 text-slate-700 font-semibold text-xs sm:text-sm border border-slate-200 transition-colors">
                        Reset
                    </a>
                @endif
            </div>
        </form>
    </div>

    <!-- MAIN DATA TABLE (BRIGHT THEME, NO ICONS) -->
    <div class="bg-white rounded-3xl border border-slate-200 shadow-xs overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-left border-collapse">
                <thead>
                    <tr class="bg-slate-50 border-b border-slate-200 text-[11px] font-extrabold text-slate-500 uppercase tracking-wider">
                        <th class="py-3.5 px-4 sm:px-6">Pengguna & Kontak</th>
                        <th class="py-3.5 px-4">Peran (Role)</th>
                        <th class="py-3.5 px-4">Data Khusus / Afiliasi</th>
                        <th class="py-3.5 px-4">Status Akun</th>
                        <th class="py-3.5 px-4">Terdaftar</th>
                        <th class="py-3.5 px-4 sm:px-6 text-right">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100 text-xs sm:text-sm">
                    @forelse($penggunaList as $u)
                        @php
                            $roleBadges = [
                                'superadmin' => ['bg' => 'bg-slate-100', 'text' => 'text-slate-900', 'border' => 'border-slate-300'],
                                'admin' => ['bg' => 'bg-emerald-50', 'text' => 'text-emerald-800', 'border' => 'border-emerald-200'],
                                'asesor' => ['bg' => 'bg-amber-50', 'text' => 'text-amber-800', 'border' => 'border-amber-200'],
                                'asesi' => ['bg' => 'bg-blue-50', 'text' => 'text-blue-800', 'border' => 'border-blue-200'],
                            ];
                            $currBadge = $roleBadges[$u->peran] ?? $roleBadges['asesi'];
                        @endphp
                        <tr class="hover:bg-slate-50/70 transition-colors">
                            <!-- 1. IDENTITAS & KONTAK -->
                            <td class="py-4 px-4 sm:px-6">
                                <div class="flex items-center gap-3">
                                    <div class="w-9 h-9 rounded-xl {{ $currBadge['bg'] }} {{ $currBadge['text'] }} font-extrabold flex items-center justify-center text-xs border {{ $currBadge['border'] }} shrink-0">
                                        {{ strtoupper(substr($u->nama_lengkap, 0, 2)) }}
                                    </div>
                                    <div class="min-w-0">
                                        <div class="font-bold text-slate-900 truncate flex items-center gap-1.5">
                                            <span>{{ $u->nama_lengkap }}</span>
                                            @if($u->id === auth()->id())
                                                <span class="px-1.5 py-0.2 rounded bg-slate-100 text-slate-700 text-[9px] font-bold border border-slate-200">Anda</span>
                                            @endif
                                        </div>
                                        <div class="text-xs text-slate-500 truncate flex items-center gap-2 mt-0.5">
                                            <span>{{ $u->email }}</span>
                                            @if($u->nomor_telepon)
                                                <span>&bull;</span>
                                                <span class="text-slate-600">{{ $u->nomor_telepon }}</span>
                                            @endif
                                        </div>
                                    </div>
                                </div>
                            </td>

                            <!-- 2. PERAN -->
                            <td class="py-4 px-4">
                                <span class="inline-block px-2.5 py-1 rounded-md text-xs font-bold border {{ $currBadge['bg'] }} {{ $currBadge['text'] }} {{ $currBadge['border'] }}">
                                    {{ strtoupper($u->peran) }}
                                </span>
                            </td>

                            <!-- 3. DETAIL KHUSUS BERDASARKAN ROLE -->
                            <td class="py-4 px-4">
                                @if($u->peran === 'asesi')
                                    <div class="space-y-0.5">
                                        <div class="text-xs font-semibold text-slate-800">
                                            NIK: {{ $u->profilAsesi?->nik ?? 'Belum Diisi' }}
                                        </div>
                                        <div class="text-[11px] text-slate-500 truncate max-w-xs">
                                            {{ $u->profilAsesi?->nama_sekolah_instansi ?? 'Instansi: -' }}
                                        </div>
                                        @if($u->pendaftaranAsesi->isNotEmpty())
                                            @php $skemaDiikuti = $u->pendaftaranAsesi->first()->skema?->nama_skema; @endphp
                                            <div class="text-[10px] text-blue-700 bg-blue-50 px-2 py-0.5 rounded inline-block mt-0.5 font-medium border border-blue-200 truncate max-w-[220px]" title="{{ $skemaDiikuti }}">
                                                Skema: {{ $skemaDiikuti ?? 'Terdaftar' }}
                                            </div>
                                        @else
                                            <span class="text-[10px] text-slate-400 italic">Belum mendaftar skema</span>
                                        @endif
                                    </div>
                                @elseif($u->peran === 'asesor')
                                    <div class="space-y-0.5">
                                        <div class="text-xs font-semibold text-slate-800">
                                            No. MET: {{ $u->nomor_registrasi ?? '-' }}
                                        </div>
                                        <div class="text-[11px] text-slate-600 truncate max-w-[220px]" title="{{ $u->skema?->nama_skema }}">
                                            Skema: {{ $u->skema?->nama_skema ?? 'Semua Skema' }}
                                        </div>
                                    </div>
                                @elseif($u->peran === 'superadmin')
                                    <div class="text-xs font-bold text-slate-900">
                                        Master Hak Akses Penuh
                                    </div>
                                    <div class="text-[11px] text-slate-400">Bypass hak akses seluruh fitur</div>
                                @else
                                    <div class="text-xs font-bold text-emerald-900">
                                        Administrator LSP
                                    </div>
                                    <div class="text-[11px] text-slate-400">Operasional & tata kelola sertifikasi</div>
                                @endif
                            </td>

                            <!-- 4. STATUS -->
                            <td class="py-4 px-4">
                                @if($u->aktif)
                                    <span class="inline-block px-2.5 py-0.5 rounded-md text-[11px] font-bold bg-emerald-50 text-emerald-700 border border-emerald-200">
                                        Aktif
                                    </span>
                                @else
                                    <span class="inline-block px-2.5 py-0.5 rounded-md text-[11px] font-bold bg-slate-100 text-slate-500 border border-slate-200">
                                        Nonaktif
                                    </span>
                                @endif
                            </td>

                            <!-- 5. TANGGAL TERDAFTAR -->
                            <td class="py-4 px-4 text-xs text-slate-500">
                                <div>{{ $u->created_at ? $u->created_at->format('d M Y') : '-' }}</div>
                                <div class="text-[10px] text-slate-400">{{ $u->created_at ? $u->created_at->format('H:i') : '' }} WIB</div>
                            </td>

                            <!-- 6. AKSI -->
                            <td class="py-4 px-4 sm:px-6 text-right">
                                <div class="flex items-center justify-end gap-1.5">
                                    <button type="button" 
                                            @click="activeEditModal = {{ $u->id }}"
                                            class="px-3 py-1.5 rounded-lg bg-slate-100 hover:bg-slate-200 text-slate-700 font-semibold text-xs border border-slate-200 transition-colors cursor-pointer">
                                        Edit
                                    </button>

                                    @if($u->id !== auth()->id())
                                        <form action="{{ route('superadmin.pengguna.hapus', $u->id) }}" 
                                              method="POST" 
                                              onsubmit="return confirm('Apakah Anda yakin ingin menghapus akun {{ addslashes($u->nama_lengkap) }}? Tindakan ini tidak dapat dibatalkan.')">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" 
                                                    class="px-2.5 py-1.5 rounded-lg bg-rose-50 hover:bg-rose-100 text-rose-700 font-semibold text-xs border border-rose-200 transition-colors cursor-pointer"
                                                    title="Hapus Akun Pengguna">
                                                Hapus
                                            </button>
                                        </form>
                                    @endif
                                </div>
                            </td>
                        </tr>

                        <!-- ============================================== -->
                        <!-- MODAL EDIT PENGGUNA                            -->
                        <!-- ============================================== -->
                        <div x-show="activeEditModal === {{ $u->id }}" 
                             x-transition:enter="transition ease-out duration-200"
                             x-transition:enter-start="opacity-0 scale-95"
                             x-transition:enter-end="opacity-100 scale-100"
                             x-transition:leave="transition ease-in duration-150"
                             x-transition:leave-start="opacity-100 scale-100"
                             x-transition:leave-end="opacity-0 scale-95"
                             class="fixed inset-0 z-50 overflow-y-auto bg-slate-900/60 backdrop-blur-xs flex items-center justify-center p-3 sm:p-5"
                             style="display: none;"
                             @keydown.escape.window="activeEditModal = null">

                            <div @click.outside="activeEditModal = null" 
                                 class="bg-white rounded-3xl max-w-2xl w-full shadow-2xl border border-slate-100 overflow-hidden flex flex-col max-h-[90vh] my-auto"
                                 x-data="{ editRole: '{{ $u->peran }}', showPwd: false, statusAktif: {{ $u->aktif ? 'true' : 'false' }} }">

                                <!-- MODAL HEADER -->
                                <div class="px-6 py-4.5 bg-white border-b border-slate-100 flex items-center justify-between shrink-0">
                                    <div class="flex items-center gap-3">
                                        <div class="w-10 h-10 rounded-2xl bg-blue-50 text-blue-600 flex items-center justify-center border border-blue-100 shrink-0">
                                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
                                            </svg>
                                        </div>
                                        <div>
                                            <div class="flex items-center gap-2">
                                                <h3 class="text-base sm:text-lg font-bold text-slate-900 leading-tight">Edit Akun Pengguna</h3>
                                                <span class="text-[10px] px-2 py-0.5 rounded-full font-bold uppercase tracking-wider bg-slate-100 text-slate-600 border border-slate-200">
                                                    {{ $u->peran }}
                                                </span>
                                            </div>
                                            <p class="text-xs text-slate-500 mt-0.5">{{ $u->nama_lengkap }} &bull; Peran: <span class="font-semibold text-slate-700">{{ strtoupper($u->peran) }}</span></p>
                                        </div>
                                    </div>
                                    <button type="button" 
                                            @click="activeEditModal = null"
                                            class="w-8 h-8 rounded-full bg-slate-100 hover:bg-slate-200 text-slate-400 hover:text-slate-700 flex items-center justify-center transition-colors cursor-pointer"
                                            title="Tutup dialog">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M6 18L18 6M6 6l12 12" />
                                        </svg>
                                    </button>
                                </div>

                                <!-- MODAL FORM (SCROLLABLE BODY + DOCKED FOOTER) -->
                                <form action="{{ route('superadmin.pengguna.ubah', $u->id) }}" method="POST" class="flex flex-col flex-1 min-h-0 overflow-hidden">
                                    @csrf

                                    <div class="flex-1 overflow-y-auto p-6 sm:p-7 space-y-6">
                                        <!-- BAGIAN 1: KREDENSIAL UTAMA & HAK AKSES -->
                                        <div class="space-y-4">
                                            <div class="flex items-center gap-2 pb-2 border-b border-slate-100">
                                                <span class="w-1.5 h-4 bg-blue-600 rounded-full"></span>
                                                <h4 class="text-xs font-bold uppercase tracking-wider text-slate-700">Informasi Akun & Akses Login</h4>
                                            </div>

                                            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                                                <div>
                                                    <label class="block text-xs font-bold text-slate-700 mb-1.5">Nama Lengkap <span class="text-rose-500">*</span></label>
                                                    <input type="text" 
                                                           name="nama_lengkap" 
                                                           value="{{ old('nama_lengkap', $u->nama_lengkap) }}" 
                                                           required 
                                                           class="w-full px-3.5 py-2.5 rounded-xl border border-slate-200 text-xs sm:text-sm text-slate-900 bg-white placeholder-slate-400 focus:outline-hidden focus:border-blue-600 focus:ring-2 focus:ring-blue-100 transition-all">
                                                </div>

                                                <div>
                                                    <label class="block text-xs font-bold text-slate-700 mb-1.5">Email Login <span class="text-rose-500">*</span></label>
                                                    <input type="email" 
                                                           name="email" 
                                                           value="{{ old('email', $u->email) }}" 
                                                           required 
                                                           class="w-full px-3.5 py-2.5 rounded-xl border border-slate-200 text-xs sm:text-sm text-slate-900 bg-white placeholder-slate-400 focus:outline-hidden focus:border-blue-600 focus:ring-2 focus:ring-blue-100 transition-all">
                                                </div>
                                            </div>

                                            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                                                <div>
                                                    <label class="block text-xs font-bold text-slate-700 mb-1.5">Peran (Role Sistem) <span class="text-rose-500">*</span></label>
                                                    <select name="peran" 
                                                            x-model="editRole"
                                                            required 
                                                            class="w-full px-3.5 py-2.5 rounded-xl border border-slate-200 text-xs sm:text-sm text-slate-900 font-semibold bg-white focus:outline-hidden focus:border-blue-600 focus:ring-2 focus:ring-blue-100 transition-all">
                                                        <option value="asesi">Asesi (Peserta Uji)</option>
                                                        <option value="asesor">Asesor Penguji</option>
                                                        <option value="admin">Administrator LSP</option>
                                                        <option value="superadmin">Super Admin Master</option>
                                                    </select>
                                                </div>

                                                <div>
                                                    <label class="block text-xs font-bold text-slate-700 mb-1.5">Nomor Telepon / WhatsApp</label>
                                                    <input type="text" 
                                                           name="nomor_telepon" 
                                                           value="{{ old('nomor_telepon', $u->nomor_telepon) }}" 
                                                           placeholder="08xxxxxxxxxx"
                                                           class="w-full px-3.5 py-2.5 rounded-xl border border-slate-200 text-xs sm:text-sm text-slate-900 bg-white placeholder-slate-400 focus:outline-hidden focus:border-blue-600 focus:ring-2 focus:ring-blue-100 transition-all">
                                                </div>
                                            </div>

                                            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 items-start">
                                                <div>
                                                    <label class="block text-xs font-bold text-slate-700 mb-1.5">Kata Sandi Baru</label>
                                                    <div class="relative">
                                                        <input :type="showPwd ? 'text' : 'password'" 
                                                               name="kata_sandi" 
                                                               placeholder="Kosongkan jika tidak diganti" 
                                                               class="w-full pl-3.5 pr-10 py-2.5 rounded-xl border border-slate-200 text-xs sm:text-sm text-slate-900 bg-white placeholder-slate-400 focus:outline-hidden focus:border-blue-600 focus:ring-2 focus:ring-blue-100 transition-all">
                                                        <button type="button" 
                                                                @click="showPwd = !showPwd"
                                                                class="absolute inset-y-0 right-0 pr-3 flex items-center text-slate-400 hover:text-slate-600 focus:outline-hidden cursor-pointer"
                                                                tabindex="-1"
                                                                title="Tampilkan / Sembunyikan sandi">
                                                            <svg x-show="!showPwd" class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
                                                            </svg>
                                                            <svg x-show="showPwd" class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" style="display: none;">
                                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13.875 18.825A10.05 10.05 0 0112 19c-4.478 0-8.268-2.943-9.543-7a9.97 9.97 0 011.563-3.029m5.858.908a3 3 0 114.243 4.243M9.878 9.878l4.242 4.242M9.88 9.88l-3.29-3.29m7.532 7.532l3.29 3.29M3 3l18 18" />
                                                            </svg>
                                                        </button>
                                                    </div>
                                                    <p class="text-[11px] text-slate-400 mt-1">Kosongkan jika tidak ingin mengubah sandi</p>
                                                </div>

                                                <div>
                                                    <label class="block text-xs font-bold text-slate-700 mb-1.5">Status Akses Akun</label>
                                                    <label class="flex items-center justify-between px-3.5 py-2.5 rounded-xl border border-slate-200 bg-slate-50/70 hover:bg-slate-100/70 transition-all cursor-pointer select-none">
                                                        <div class="pr-2">
                                                            <div class="text-xs font-bold text-slate-800 flex items-center gap-1.5">
                                                                <span>Status Aktif</span>
                                                                <span class="inline-block w-2 h-2 rounded-full" :class="statusAktif ? 'bg-emerald-500' : 'bg-slate-300'"></span>
                                                            </div>
                                                            <div class="text-[10px] text-slate-500 mt-0.5" x-text="statusAktif ? 'Izin login aktif & dapat diakses' : 'Akun ditangguhkan sementara'"></div>
                                                        </div>
                                                        <input type="checkbox" 
                                                               name="aktif" 
                                                               value="1" 
                                                               {{ $u->aktif ? 'checked' : '' }}
                                                               @change="statusAktif = $el.checked"
                                                               class="w-4 h-4 rounded border-slate-300 text-blue-600 focus:ring-blue-500 cursor-pointer">
                                                    </label>
                                                    <p class="text-[11px] text-slate-400 mt-1">Nonaktifkan untuk menangguhkan izin login</p>
                                                </div>
                                            </div>
                                        </div>

                                        <!-- BAGIAN 2: DATA KHUSUS ASESI -->
                                        <div x-show="editRole === 'asesi'" class="space-y-4 pt-2" style="display: none;">
                                            <div class="flex items-center justify-between pb-2 border-b border-blue-100">
                                                <div class="flex items-center gap-2">
                                                    <span class="w-1.5 h-4 bg-blue-600 rounded-full"></span>
                                                    <h4 class="text-xs font-bold uppercase tracking-wider text-blue-900">Biodata & Profil Lengkap Asesi</h4>
                                                </div>
                                                <span class="text-[10px] px-2 py-0.5 rounded-md bg-blue-50 border border-blue-200/80 text-blue-700 font-semibold font-mono">FR.APL.01 Data</span>
                                            </div>

                                            <div class="bg-blue-50/30 border border-blue-100/70 rounded-2xl p-4 sm:p-5 space-y-4">
                                                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                                                    <div>
                                                        <label class="block text-xs font-bold text-slate-700 mb-1.5">NIK (Nomor Induk Kependudukan)</label>
                                                        <input type="text" 
                                                               name="nik" 
                                                               maxlength="16"
                                                               value="{{ old('nik', $u->profilAsesi?->nik) }}" 
                                                               placeholder="16 digit NIK KTP/KK"
                                                               class="w-full px-3.5 py-2.5 rounded-xl border border-slate-200 text-xs sm:text-sm text-slate-900 bg-white font-mono focus:outline-hidden focus:border-blue-600 focus:ring-2 focus:ring-blue-100 transition-all">
                                                    </div>

                                                    <div>
                                                        <label class="block text-xs font-bold text-slate-700 mb-1.5">Asal Sekolah / Lembaga / Instansi</label>
                                                        <input type="text" 
                                                               name="nama_sekolah_instansi" 
                                                               value="{{ old('nama_sekolah_instansi', $u->profilAsesi?->nama_sekolah_instansi) }}" 
                                                               placeholder="Contoh: SMKN 1 Gunungputri"
                                                               class="w-full px-3.5 py-2.5 rounded-xl border border-slate-200 text-xs sm:text-sm text-slate-900 bg-white focus:outline-hidden focus:border-blue-600 focus:ring-2 focus:ring-blue-100 transition-all">
                                                    </div>
                                                </div>

                                                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                                                    <div>
                                                        <label class="block text-xs font-bold text-slate-700 mb-1.5">Tempat Lahir</label>
                                                        <input type="text" 
                                                               name="tempat_lahir" 
                                                               value="{{ old('tempat_lahir', $u->profilAsesi?->tempat_lahir) }}" 
                                                               placeholder="Kota Tempat Lahir"
                                                               class="w-full px-3.5 py-2.5 rounded-xl border border-slate-200 text-xs sm:text-sm text-slate-900 bg-white focus:outline-hidden focus:border-blue-600 focus:ring-2 focus:ring-blue-100 transition-all">
                                                    </div>

                                                    <div>
                                                        <label class="block text-xs font-bold text-slate-700 mb-1.5">Tanggal Lahir</label>
                                                        <input type="date" 
                                                               name="tanggal_lahir" 
                                                               value="{{ old('tanggal_lahir', $u->profilAsesi?->tanggal_lahir) }}" 
                                                               class="w-full px-3.5 py-2.5 rounded-xl border border-slate-200 text-xs sm:text-sm text-slate-900 bg-white focus:outline-hidden focus:border-blue-600 focus:ring-2 focus:ring-blue-100 transition-all">
                                                    </div>
                                                </div>

                                                <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
                                                    <div>
                                                        <label class="block text-xs font-bold text-slate-700 mb-1.5">Jenis Kelamin</label>
                                                        <select name="jenis_kelamin" 
                                                                class="w-full px-3.5 py-2.5 rounded-xl border border-slate-200 text-xs sm:text-sm text-slate-900 bg-white focus:outline-hidden focus:border-blue-600 focus:ring-2 focus:ring-blue-100 transition-all">
                                                            <option value="">Pilih</option>
                                                            <option value="Laki-laki" {{ old('jenis_kelamin', $u->profilAsesi?->jenis_kelamin) == 'Laki-laki' ? 'selected' : '' }}>Laki-laki</option>
                                                            <option value="Perempuan" {{ old('jenis_kelamin', $u->profilAsesi?->jenis_kelamin) == 'Perempuan' ? 'selected' : '' }}>Perempuan</option>
                                                        </select>
                                                    </div>

                                                    <div>
                                                        <label class="block text-xs font-bold text-slate-700 mb-1.5">Pendidikan Terakhir</label>
                                                        <input type="text" 
                                                               name="pendidikan_terakhir" 
                                                               value="{{ old('pendidikan_terakhir', $u->profilAsesi?->pendidikan_terakhir) }}" 
                                                               placeholder="SMK / SMA / S1"
                                                               class="w-full px-3.5 py-2.5 rounded-xl border border-slate-200 text-xs sm:text-sm text-slate-900 bg-white focus:outline-hidden focus:border-blue-600 focus:ring-2 focus:ring-blue-100 transition-all">
                                                    </div>

                                                    <div>
                                                        <label class="block text-xs font-bold text-slate-700 mb-1.5">Pekerjaan</label>
                                                        <input type="text" 
                                                               name="pekerjaan" 
                                                               value="{{ old('pekerjaan', $u->profilAsesi?->pekerjaan) }}" 
                                                               placeholder="Pelajar / Mahasiswa"
                                                               class="w-full px-3.5 py-2.5 rounded-xl border border-slate-200 text-xs sm:text-sm text-slate-900 bg-white focus:outline-hidden focus:border-blue-600 focus:ring-2 focus:ring-blue-100 transition-all">
                                                    </div>
                                                </div>

                                                <div>
                                                    <label class="block text-xs font-bold text-slate-700 mb-1.5">Alamat Domisili Lengkap</label>
                                                    <textarea name="alamat" 
                                                              rows="2" 
                                                              placeholder="Nama jalan, RT/RW, Kelurahan, Kecamatan, Kota/Kabupaten"
                                                              class="w-full px-3.5 py-2.5 rounded-xl border border-slate-200 text-xs sm:text-sm text-slate-900 bg-white focus:outline-hidden focus:border-blue-600 focus:ring-2 focus:ring-blue-100 transition-all">{{ old('alamat', $u->profilAsesi?->alamat) }}</textarea>
                                                </div>
                                            </div>
                                        </div>

                                        <!-- BAGIAN 3: DATA KHUSUS ASESOR -->
                                        <div x-show="editRole === 'asesor'" class="space-y-4 pt-2" style="display: none;">
                                            <div class="flex items-center justify-between pb-2 border-b border-amber-100">
                                                <div class="flex items-center gap-2">
                                                    <span class="w-1.5 h-4 bg-amber-600 rounded-full"></span>
                                                    <h4 class="text-xs font-bold uppercase tracking-wider text-amber-900">Kredensial Asesor Penguji BNSP</h4>
                                                </div>
                                                <span class="text-[10px] px-2 py-0.5 rounded-md bg-amber-50 border border-amber-200/80 text-amber-700 font-semibold font-mono">Lisensi MET</span>
                                            </div>

                                            <div class="bg-amber-50/30 border border-amber-100/70 rounded-2xl p-4 sm:p-5 space-y-4">
                                                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                                                    <div>
                                                        <label class="block text-xs font-bold text-slate-700 mb-1.5">Nomor Registrasi / No. MET Asesor</label>
                                                        <input type="text" 
                                                               name="nomor_registrasi" 
                                                               value="{{ old('nomor_registrasi', $u->nomor_registrasi) }}" 
                                                               placeholder="MET.000.000000 2024"
                                                               class="w-full px-3.5 py-2.5 rounded-xl border border-slate-200 text-xs sm:text-sm text-slate-900 bg-white font-mono focus:outline-hidden focus:border-amber-600 focus:ring-2 focus:ring-amber-100 transition-all">
                                                        <p class="text-[10px] text-slate-400 mt-1">Nomor lisensi resmi asesor dari BNSP</p>
                                                    </div>

                                                    <div>
                                                        <label class="block text-xs font-bold text-slate-700 mb-1.5">Skema Sertifikasi Penugasan</label>
                                                        <select name="skema_id" 
                                                                class="w-full px-3.5 py-2.5 rounded-xl border border-slate-200 text-xs sm:text-sm text-slate-900 bg-white focus:outline-hidden focus:border-amber-600 focus:ring-2 focus:ring-amber-100 transition-all">
                                                            <option value="">Semua Skema / Fleksibel</option>
                                                            @foreach($daftarSkema as $skm)
                                                                <option value="{{ $skm->id }}" {{ old('skema_id', $u->skema_id) == $skm->id ? 'selected' : '' }}>
                                                                    [{{ $skm->kode_skema }}] {{ $skm->nama_skema }}
                                                                </option>
                                                            @endforeach
                                                        </select>
                                                        <p class="text-[10px] text-slate-400 mt-1">Skema utama yang diuji oleh asesor ini</p>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>

                                    <!-- MODAL FOOTER DOCKED -->
                                    <div class="px-6 py-4 bg-slate-50 border-t border-slate-100 flex items-center justify-end gap-3 shrink-0">
                                        <button type="button" 
                                                @click="activeEditModal = null" 
                                                class="px-4 py-2.5 rounded-xl bg-white hover:bg-slate-100 text-slate-700 font-semibold text-xs sm:text-sm border border-slate-200 shadow-2xs transition-colors cursor-pointer">
                                            Batal
                                        </button>
                                        <button type="submit" 
                                                class="px-5 py-2.5 rounded-xl bg-blue-600 hover:bg-blue-700 active:bg-blue-800 text-white font-bold text-xs sm:text-sm shadow-xs transition-colors cursor-pointer flex items-center gap-1.5">
                                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                                            </svg>
                                            <span>Simpan Perubahan</span>
                                        </button>
                                    </div>
                                </form>
                            </div>
                        </div>
                    @empty
                        <tr>
                            <td colspan="6" class="py-12 text-center text-slate-400">
                                <div class="font-bold text-slate-700 text-base">Tidak Ada Pengguna Ditemukan</div>
                                <p class="text-xs text-slate-400 max-w-sm mx-auto mt-1">Coba sesuaikan kata kunci pencarian atau ubah filter peran yang dipilih.</p>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <!-- PAGINATION -->
        @if($penggunaList->hasPages())
            <div class="p-4 sm:p-5 border-t border-slate-100 bg-slate-50/50">
                {{ $penggunaList->links() }}
            </div>
        @endif
    </div>

    <!-- ============================================== -->
    <!-- MODAL TAMBAH PENGGUNA BARU                     -->
    <!-- ============================================== -->
    <div x-show="modalTambah" 
         x-transition:enter="transition ease-out duration-200"
         x-transition:enter-start="opacity-0 scale-95"
         x-transition:enter-end="opacity-100 scale-100"
         x-transition:leave="transition ease-in duration-150"
         x-transition:leave-start="opacity-100 scale-100"
         x-transition:leave-end="opacity-0 scale-95"
         class="fixed inset-0 z-50 overflow-y-auto bg-slate-900/60 backdrop-blur-xs flex items-center justify-center p-3 sm:p-5"
         style="display: none;"
         @keydown.escape.window="modalTambah = false">

        <div @click.outside="modalTambah = false" 
             class="bg-white rounded-3xl max-w-2xl w-full shadow-2xl border border-slate-100 overflow-hidden flex flex-col max-h-[90vh] my-auto"
             x-data="{ showPwdAdd: false }">

            <!-- MODAL HEADER -->
            <div class="px-6 py-4.5 bg-white border-b border-slate-100 flex items-center justify-between shrink-0">
                <div class="flex items-center gap-3">
                    <div class="w-10 h-10 rounded-2xl bg-blue-50 text-blue-600 flex items-center justify-center border border-blue-100 shrink-0">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M18 9v3m0 0v3m0-3h3m-3 0h-3m-2-5a4 4 0 11-8 0 4 4 0 018 0zM3 20a6 6 0 0112 0v1H3v-1z" />
                        </svg>
                    </div>
                    <div>
                        <h3 class="text-base sm:text-lg font-bold text-slate-900 leading-tight">Buat Akun Pengguna Baru</h3>
                        <p class="text-xs text-slate-500 mt-0.5">Daftarkan akun Super Admin, Admin LSP, Asesor Penguji, atau Asesi</p>
                    </div>
                </div>
                <button type="button" 
                        @click="modalTambah = false"
                        class="w-8 h-8 rounded-full bg-slate-100 hover:bg-slate-200 text-slate-400 hover:text-slate-700 flex items-center justify-center transition-colors cursor-pointer"
                        title="Tutup dialog">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M6 18L18 6M6 6l12 12" />
                    </svg>
                </button>
            </div>

            <!-- MODAL FORM (SCROLLABLE BODY + DOCKED FOOTER) -->
            <form action="{{ route('superadmin.pengguna.simpan') }}" method="POST" class="flex flex-col flex-1 min-h-0 overflow-hidden">
                @csrf

                <div class="flex-1 overflow-y-auto p-6 sm:p-7 space-y-6">
                    <!-- BAGIAN 1: KREDENSIAL UTAMA -->
                    <div class="space-y-4">
                        <div class="flex items-center gap-2 pb-2 border-b border-slate-100">
                            <span class="w-1.5 h-4 bg-blue-600 rounded-full"></span>
                            <h4 class="text-xs font-bold uppercase tracking-wider text-slate-700">Informasi Akun & Akses Login</h4>
                        </div>

                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                            <div>
                                <label class="block text-xs font-bold text-slate-700 mb-1.5">Nama Lengkap <span class="text-rose-500">*</span></label>
                                <input type="text" 
                                       name="nama_lengkap" 
                                       required 
                                       placeholder="Contoh: Budi Santoso"
                                       class="w-full px-3.5 py-2.5 rounded-xl border border-slate-200 text-xs sm:text-sm text-slate-900 bg-white placeholder-slate-400 focus:outline-hidden focus:border-blue-600 focus:ring-2 focus:ring-blue-100 transition-all">
                            </div>

                            <div>
                                <label class="block text-xs font-bold text-slate-700 mb-1.5">Email Login <span class="text-rose-500">*</span></label>
                                <input type="email" 
                                       name="email" 
                                       required 
                                       placeholder="budi@example.com"
                                       class="w-full px-3.5 py-2.5 rounded-xl border border-slate-200 text-xs sm:text-sm text-slate-900 bg-white placeholder-slate-400 focus:outline-hidden focus:border-blue-600 focus:ring-2 focus:ring-blue-100 transition-all">
                            </div>
                        </div>

                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                            <div>
                                <label class="block text-xs font-bold text-slate-700 mb-1.5">Peran (Role Sistem) <span class="text-rose-500">*</span></label>
                                <select name="peran" 
                                        x-model="peranTambah"
                                        required 
                                        class="w-full px-3.5 py-2.5 rounded-xl border border-slate-200 text-xs sm:text-sm text-slate-900 font-semibold bg-white focus:outline-hidden focus:border-blue-600 focus:ring-2 focus:ring-blue-100 transition-all">
                                    <option value="asesi">Asesi (Peserta Uji)</option>
                                    <option value="asesor">Asesor Penguji</option>
                                    <option value="admin">Administrator LSP</option>
                                    <option value="superadmin">Super Admin Master</option>
                                </select>
                            </div>

                            <div>
                                <label class="block text-xs font-bold text-slate-700 mb-1.5">Nomor Telepon / WhatsApp</label>
                                <input type="text" 
                                       name="nomor_telepon" 
                                       placeholder="08xxxxxxxxxx"
                                       class="w-full px-3.5 py-2.5 rounded-xl border border-slate-200 text-xs sm:text-sm text-slate-900 bg-white placeholder-slate-400 focus:outline-hidden focus:border-blue-600 focus:ring-2 focus:ring-blue-100 transition-all">
                            </div>
                        </div>

                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 items-start">
                            <div>
                                <label class="block text-xs font-bold text-slate-700 mb-1.5">Kata Sandi Awal <span class="text-rose-500">*</span></label>
                                <div class="relative">
                                    <input :type="showPwdAdd ? 'text' : 'password'" 
                                           name="kata_sandi" 
                                           required 
                                           placeholder="Minimal 6 karakter"
                                           class="w-full pl-3.5 pr-10 py-2.5 rounded-xl border border-slate-200 text-xs sm:text-sm text-slate-900 bg-white placeholder-slate-400 focus:outline-hidden focus:border-blue-600 focus:ring-2 focus:ring-blue-100 transition-all">
                                    <button type="button" 
                                            @click="showPwdAdd = !showPwdAdd"
                                            class="absolute inset-y-0 right-0 pr-3 flex items-center text-slate-400 hover:text-slate-600 focus:outline-hidden cursor-pointer"
                                            tabindex="-1"
                                            title="Tampilkan / Sembunyikan sandi">
                                        <svg x-show="!showPwdAdd" class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
                                        </svg>
                                        <svg x-show="showPwdAdd" class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" style="display: none;">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13.875 18.825A10.05 10.05 0 0112 19c-4.478 0-8.268-2.943-9.543-7a9.97 9.97 0 011.563-3.029m5.858.908a3 3 0 114.243 4.243M9.878 9.878l4.242 4.242M9.88 9.88l-3.29-3.29m7.532 7.532l3.29 3.29M3 3l18 18" />
                                        </svg>
                                    </button>
                                </div>
                                <p class="text-[11px] text-slate-400 mt-1">Gunakan kombinasi sandi yang aman</p>
                            </div>

                            <div>
                                <label class="block text-xs font-bold text-slate-700 mb-1.5">Status Akun Baru</label>
                                <div class="flex items-center justify-between px-3.5 py-2.5 rounded-xl border border-slate-200 bg-slate-50/70 select-none">
                                    <div class="pr-2">
                                        <div class="text-xs font-bold text-slate-800 flex items-center gap-1.5">
                                            <span>Otomatis Aktif</span>
                                            <span class="inline-block w-2 h-2 rounded-full bg-emerald-500"></span>
                                        </div>
                                        <div class="text-[10px] text-slate-500 mt-0.5">Akun siap digunakan setelah didaftarkan</div>
                                    </div>
                                    <span class="text-[10px] font-bold px-2 py-0.5 rounded-md bg-emerald-50 border border-emerald-200 text-emerald-700">Aktif</span>
                                </div>
                                <p class="text-[11px] text-slate-400 mt-1">Status dapat diubah setelah akun dibuat</p>
                            </div>
                        </div>
                    </div>

                    <!-- BAGIAN 2: DATA KHUSUS ASESI JIKA PERAN ASESI -->
                    <div x-show="peranTambah === 'asesi'" class="space-y-4 pt-2">
                        <div class="flex items-center justify-between pb-2 border-b border-blue-100">
                            <div class="flex items-center gap-2">
                                <span class="w-1.5 h-4 bg-blue-600 rounded-full"></span>
                                <h4 class="text-xs font-bold uppercase tracking-wider text-blue-900">Biodata Awal Profil Asesi (Opsional)</h4>
                            </div>
                            <span class="text-[10px] px-2 py-0.5 rounded-md bg-blue-50 border border-blue-200/80 text-blue-700 font-semibold font-mono">Biodata</span>
                        </div>

                        <div class="bg-blue-50/30 border border-blue-100/70 rounded-2xl p-4 sm:p-5 space-y-4">
                            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                                <div>
                                    <label class="block text-xs font-bold text-slate-700 mb-1.5">NIK (KTP/KK)</label>
                                    <input type="text" 
                                           name="nik" 
                                           maxlength="16"
                                           placeholder="16 digit NIK"
                                           class="w-full px-3.5 py-2.5 rounded-xl border border-slate-200 text-xs sm:text-sm text-slate-900 bg-white font-mono focus:outline-hidden focus:border-blue-600 focus:ring-2 focus:ring-blue-100 transition-all">
                                </div>

                                <div>
                                    <label class="block text-xs font-bold text-slate-700 mb-1.5">Asal Sekolah / Lembaga</label>
                                    <input type="text" 
                                           name="nama_sekolah_instansi" 
                                           placeholder="SMKN 1 Gunungputri"
                                           class="w-full px-3.5 py-2.5 rounded-xl border border-slate-200 text-xs sm:text-sm text-slate-900 bg-white focus:outline-hidden focus:border-blue-600 focus:ring-2 focus:ring-blue-100 transition-all">
                                </div>
                            </div>

                            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                                <div>
                                    <label class="block text-xs font-bold text-slate-700 mb-1.5">Tempat Lahir</label>
                                    <input type="text" 
                                           name="tempat_lahir" 
                                           placeholder="Kota Lahir"
                                           class="w-full px-3.5 py-2.5 rounded-xl border border-slate-200 text-xs sm:text-sm text-slate-900 bg-white focus:outline-hidden focus:border-blue-600 focus:ring-2 focus:ring-blue-100 transition-all">
                                </div>

                                <div>
                                    <label class="block text-xs font-bold text-slate-700 mb-1.5">Tanggal Lahir</label>
                                    <input type="date" 
                                           name="tanggal_lahir" 
                                           class="w-full px-3.5 py-2.5 rounded-xl border border-slate-200 text-xs sm:text-sm text-slate-900 bg-white focus:outline-hidden focus:border-blue-600 focus:ring-2 focus:ring-blue-100 transition-all">
                                </div>
                            </div>

                            <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
                                <div>
                                    <label class="block text-xs font-bold text-slate-700 mb-1.5">Jenis Kelamin</label>
                                    <select name="jenis_kelamin" 
                                            class="w-full px-3.5 py-2.5 rounded-xl border border-slate-200 text-xs sm:text-sm text-slate-900 bg-white focus:outline-hidden focus:border-blue-600 focus:ring-2 focus:ring-blue-100 transition-all">
                                        <option value="">Pilih</option>
                                        <option value="Laki-laki">Laki-laki</option>
                                        <option value="Perempuan">Perempuan</option>
                                    </select>
                                </div>

                                <div>
                                    <label class="block text-xs font-bold text-slate-700 mb-1.5">Pendidikan Terakhir</label>
                                    <input type="text" 
                                           name="pendidikan_terakhir" 
                                           placeholder="SMK / SMA"
                                           class="w-full px-3.5 py-2.5 rounded-xl border border-slate-200 text-xs sm:text-sm text-slate-900 bg-white focus:outline-hidden focus:border-blue-600 focus:ring-2 focus:ring-blue-100 transition-all">
                                </div>

                                <div>
                                    <label class="block text-xs font-bold text-slate-700 mb-1.5">Pekerjaan</label>
                                    <input type="text" 
                                           name="pekerjaan" 
                                           placeholder="Pelajar / Lainnya"
                                           class="w-full px-3.5 py-2.5 rounded-xl border border-slate-200 text-xs sm:text-sm text-slate-900 bg-white focus:outline-hidden focus:border-blue-600 focus:ring-2 focus:ring-blue-100 transition-all">
                                </div>
                            </div>

                            <div>
                                <label class="block text-xs font-bold text-slate-700 mb-1.5">Alamat Domisili</label>
                                <textarea name="alamat" 
                                          rows="2" 
                                          placeholder="Alamat tempat tinggal lengkap..."
                                          class="w-full px-3.5 py-2.5 rounded-xl border border-slate-200 text-xs sm:text-sm text-slate-900 bg-white focus:outline-hidden focus:border-blue-600 focus:ring-2 focus:ring-blue-100 transition-all"></textarea>
                            </div>
                        </div>
                    </div>

                    <!-- BAGIAN 3: DATA KHUSUS ASESOR JIKA PERAN ASESOR -->
                    <div x-show="peranTambah === 'asesor'" class="space-y-4 pt-2" style="display: none;">
                        <div class="flex items-center justify-between pb-2 border-b border-amber-100">
                            <div class="flex items-center gap-2">
                                <span class="w-1.5 h-4 bg-amber-600 rounded-full"></span>
                                <h4 class="text-xs font-bold uppercase tracking-wider text-amber-900">Kredensial Asesor Penguji BNSP</h4>
                            </div>
                            <span class="text-[10px] px-2 py-0.5 rounded-md bg-amber-50 border border-amber-200/80 text-amber-700 font-semibold font-mono">Lisensi MET</span>
                        </div>

                        <div class="bg-amber-50/30 border border-amber-100/70 rounded-2xl p-4 sm:p-5 space-y-4">
                            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                                <div>
                                    <label class="block text-xs font-bold text-slate-700 mb-1.5">Nomor Registrasi / No. MET Asesor</label>
                                    <input type="text" 
                                           name="nomor_registrasi" 
                                           placeholder="MET.000.000000 2024"
                                           class="w-full px-3.5 py-2.5 rounded-xl border border-slate-200 text-xs sm:text-sm text-slate-900 bg-white font-mono focus:outline-hidden focus:border-amber-600 focus:ring-2 focus:ring-amber-100 transition-all">
                                    <p class="text-[10px] text-slate-400 mt-1">Nomor lisensi resmi asesor dari BNSP</p>
                                </div>

                                <div>
                                    <label class="block text-xs font-bold text-slate-700 mb-1.5">Skema Sertifikasi Penugasan</label>
                                    <select name="skema_id" 
                                            class="w-full px-3.5 py-2.5 rounded-xl border border-slate-200 text-xs sm:text-sm text-slate-900 bg-white focus:outline-hidden focus:border-amber-600 focus:ring-2 focus:ring-amber-100 transition-all">
                                        <option value="">Semua Skema / Fleksibel</option>
                                        @foreach($daftarSkema as $skm)
                                            <option value="{{ $skm->id }}">[{{ $skm->kode_skema }}] {{ $skm->nama_skema }}</option>
                                        @endforeach
                                    </select>
                                    <p class="text-[10px] text-slate-400 mt-1">Skema penugasan uji asesor</p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- MODAL FOOTER DOCKED -->
                <div class="px-6 py-4 bg-slate-50 border-t border-slate-100 flex items-center justify-end gap-3 shrink-0">
                    <button type="button" 
                            @click="modalTambah = false" 
                            class="px-4 py-2.5 rounded-xl bg-white hover:bg-slate-100 text-slate-700 font-semibold text-xs sm:text-sm border border-slate-200 shadow-2xs transition-colors cursor-pointer">
                        Batal
                    </button>
                    <button type="submit" 
                            class="px-5 py-2.5 rounded-xl bg-blue-600 hover:bg-blue-700 active:bg-blue-800 text-white font-bold text-xs sm:text-sm shadow-xs transition-colors cursor-pointer flex items-center gap-1.5">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
                        </svg>
                        <span>Buat Akun Pengguna</span>
                    </button>
                </div>
            </form>
        </div>
    </div>

</div>
@endsection
