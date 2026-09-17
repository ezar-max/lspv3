@extends('tata-letak.dasbor')

@section('judul', 'Data Asesor Penguji')

@push('css')
    <link rel="stylesheet" href="{{ asset('css/admin/dashboard-admin.css') }}">
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
                                    <div class="modal-konten text-left max-w-md">
                                        <div class="flex items-center justify-between border-b border-slate-100 pb-3 mb-4">
                                            <h3 class="font-bold text-sm text-slate-900">
                                                Edit Akun Asesor: {{ $asesor->nama_lengkap }}
                                            </h3>
                                            <button type="button" onclick="tutupModal('modalEditAsesor_{{ $asesor->id }}')" class="text-slate-400 hover:text-slate-600 text-lg">&times;</button>
                                        </div>

                                        <form action="{{ route('admin.asesor.ubah', $asesor->id) }}" method="POST" class="space-y-3.5">
                                            @csrf

                                            <div>
                                                <label class="block text-[11px] font-bold text-slate-700 mb-1">Nama Lengkap & Gelar <span class="text-rose-500">*</span></label>
                                                <input type="text" name="nama_lengkap" required value="{{ $asesor->nama_lengkap }}" class="w-full px-3 py-2 rounded-xl border border-slate-200 text-xs focus:outline-none focus:border-emerald-500">
                                            </div>

                                            <div>
                                                <label class="block text-[11px] font-bold text-slate-700 mb-1">Alamat Email Login <span class="text-rose-500">*</span></label>
                                                <input type="email" name="email" required value="{{ $asesor->email }}" class="w-full px-3 py-2 rounded-xl border border-slate-200 text-xs focus:outline-none focus:border-emerald-500">
                                            </div>

                                            <div>
                                                <label class="block text-[11px] font-bold text-slate-700 mb-1">Skema Sertifikasi (Tugas Asesor)</label>
                                                <select name="skema_id" class="w-full px-3 py-2 rounded-xl border border-slate-200 text-xs focus:outline-none focus:border-emerald-500">
                                                    <option value="">-- Bebas / Semua Skema --</option>
                                                    @foreach($skemaList as $skema)
                                                        <option value="{{ $skema->id }}" {{ $asesor->skema_id == $skema->id ? 'selected' : '' }}>
                                                            {{ $skema->kode_skema }} - {{ $skema->nama_skema }}
                                                        </option>
                                                    @endforeach
                                                </select>
                                                <div class="text-[10px] text-slate-400 mt-1">Asesor ini difokuskan untuk mengurus 1 skema sertifikasi tertentu.</div>
                                            </div>

                                            <div>
                                                <label class="block text-[11px] font-bold text-slate-700 mb-1">No. Registrasi MET (BNSP)</label>
                                                <input type="text" name="nomor_registrasi" value="{{ $asesor->nomor_registrasi }}" placeholder="Contoh: MET.000.001234.2024" class="w-full px-3 py-2 rounded-xl border border-slate-200 text-xs focus:outline-none focus:border-emerald-500">
                                            </div>

                                            <div>
                                                <label class="block text-[11px] font-bold text-slate-700 mb-1">No. Telepon / WhatsApp</label>
                                                <input type="text" name="nomor_telepon" value="{{ $asesor->nomor_telepon }}" placeholder="08xxxxxxxxxx" class="w-full px-3 py-2 rounded-xl border border-slate-200 text-xs focus:outline-none focus:border-emerald-500">
                                            </div>

                                            <div>
                                                <label class="block text-[11px] font-bold text-slate-700 mb-1">Kata Sandi Baru (Kosongkan jika tidak diubah)</label>
                                                <input type="password" name="kata_sandi" placeholder="Minimal 6 karakter" class="w-full px-3 py-2 rounded-xl border border-slate-200 text-xs focus:outline-none focus:border-emerald-500">
                                            </div>

                                            <div class="pt-1">
                                                <label class="inline-flex items-center gap-2 cursor-pointer">
                                                    <input type="checkbox" name="aktif" value="1" {{ $asesor->aktif ? 'checked' : '' }} class="rounded border-slate-300 text-emerald-600 focus:ring-emerald-500">
                                                    <span class="text-xs font-semibold text-slate-800">Akun Asesor Aktif (Bisa Login & Menguji)</span>
                                                </label>
                                            </div>

                                            <div class="pt-3 flex justify-end gap-2 border-t border-slate-100">
                                                <button type="button" onclick="tutupModal('modalEditAsesor_{{ $asesor->id }}')" class="px-4 py-2 bg-slate-100 hover:bg-slate-200 text-slate-700 rounded-xl text-xs font-semibold">
                                                    Batal
                                                </button>
                                                <button type="submit" class="px-4 py-2 bg-emerald-600 hover:bg-emerald-700 text-white rounded-xl text-xs font-semibold">
                                                    Simpan Perubahan
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
    <div class="modal-konten text-left max-w-md">
        <div class="flex items-center justify-between border-b border-slate-100 pb-3 mb-4">
            <h3 class="font-bold text-sm text-slate-900 flex items-center gap-2">
                <i class="fa-solid fa-user-plus text-emerald-600"></i>
                <span>Tambah Akun Asesor Baru</span>
            </h3>
            <button type="button" onclick="tutupModal('modalTambahAsesor')" class="text-slate-400 hover:text-slate-600 text-lg">&times;</button>
        </div>

        <form action="{{ route('admin.asesor.simpan') }}" method="POST" class="space-y-3.5">
            @csrf

            <div>
                <label class="block text-[11px] font-bold text-slate-700 mb-1">Nama Lengkap & Gelar <span class="text-rose-500">*</span></label>
                <input type="text" name="nama_lengkap" required placeholder="Contoh: Drs. Ahmad Subagja, M.Pd., Met." class="w-full px-3 py-2 rounded-xl border border-slate-200 text-xs focus:outline-none focus:border-emerald-500">
            </div>

            <div>
                <label class="block text-[11px] font-bold text-slate-700 mb-1">Alamat Email Login <span class="text-rose-500">*</span></label>
                <input type="email" name="email" required placeholder="asesor@smkn1gunungputri.sch.id" class="w-full px-3 py-2 rounded-xl border border-slate-200 text-xs focus:outline-none focus:border-emerald-500">
            </div>

            <div>
                <label class="block text-[11px] font-bold text-slate-700 mb-1">Skema Sertifikasi (Tugas Asesor)</label>
                <select name="skema_id" class="w-full px-3 py-2 rounded-xl border border-slate-200 text-xs focus:outline-none focus:border-emerald-500">
                    <option value="">-- Bebas / Semua Skema --</option>
                    @foreach($skemaList as $skema)
                        <option value="{{ $skema->id }}">
                            {{ $skema->kode_skema }} - {{ $skema->nama_skema }}
                        </option>
                    @endforeach
                </select>
                <div class="text-[10px] text-slate-400 mt-1">Satu asesor dapat difokuskan untuk mengurus 1 skema sertifikasi tertentu.</div>
            </div>

            <div>
                <label class="block text-[11px] font-bold text-slate-700 mb-1">Kata Sandi Initial <span class="text-rose-500">*</span></label>
                <input type="password" name="kata_sandi" required placeholder="Minimal 6 karakter" class="w-full px-3 py-2 rounded-xl border border-slate-200 text-xs focus:outline-none focus:border-emerald-500">
            </div>

            <div>
                <label class="block text-[11px] font-bold text-slate-700 mb-1">No. Registrasi MET (BNSP)</label>
                <input type="text" name="nomor_registrasi" placeholder="Contoh: MET.000.001234.2024" class="w-full px-3 py-2 rounded-xl border border-slate-200 text-xs focus:outline-none focus:border-emerald-500">
            </div>

            <div>
                <label class="block text-[11px] font-bold text-slate-700 mb-1">No. Telepon / WhatsApp</label>
                <input type="text" name="nomor_telepon" placeholder="08xxxxxxxxxx" class="w-full px-3 py-2 rounded-xl border border-slate-200 text-xs focus:outline-none focus:border-emerald-500">
            </div>

            <div class="pt-3 flex justify-end gap-2 border-t border-slate-100">
                <button type="button" onclick="tutupModal('modalTambahAsesor')" class="px-4 py-2 bg-slate-100 hover:bg-slate-200 text-slate-700 rounded-xl text-xs font-semibold">
                    Batal
                </button>
                <button type="submit" class="px-4 py-2 bg-emerald-600 hover:bg-emerald-700 text-white rounded-xl text-xs font-semibold">
                    Simpan Akun Asesor
                </button>
            </div>
        </form>
    </div>
</div>
@endsection
