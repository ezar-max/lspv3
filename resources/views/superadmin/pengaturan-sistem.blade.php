@extends('tata-letak.dasbor')

@section('judul', 'Pengaturan Global LSP - Super Admin')

@section('konten')
<div class="space-y-6 max-w-5xl mx-auto pb-12">

    <!-- FLASH MESSAGE -->
    @if(session('sukses'))
        <div class="p-4 rounded-2xl bg-emerald-50 border border-emerald-200 text-emerald-800 text-sm shadow-xs flex items-center justify-between">
            <div>
                <span class="font-bold">Berhasil:</span> {{ session('sukses') }}
            </div>
            <button type="button" @click="$el.parentElement.remove()" class="text-emerald-600 hover:text-emerald-900 font-bold px-2 py-1 text-sm">Tutup</button>
        </div>
    @endif

    <!-- HEADER SECTION (BRIGHT THEME, NO ICONS) -->
    <div class="bg-white rounded-3xl p-6 sm:p-8 border border-slate-200 shadow-xs">
        <div class="flex flex-col md:flex-row md:items-center justify-between gap-6">
            <div class="space-y-2">
                <div class="inline-block px-3 py-1 rounded-md bg-blue-50 border border-blue-200 text-blue-700 text-xs font-bold tracking-wide uppercase">
                    Master System Configuration
                </div>
                <h1 class="text-2xl sm:text-3xl font-black tracking-tight text-slate-900 leading-tight">
                    Pengaturan Global Sistem LSP
                </h1>
                <p class="text-slate-600 text-xs sm:text-sm max-w-2xl leading-relaxed">
                    Konfigurasi parameter kelembagaan, lisensi resmi BNSP, kontak operasional, serta visi & misi Lembaga Sertifikasi Profesi.
                </p>
            </div>

            <div class="flex items-center gap-3 shrink-0">
                <a href="{{ route('superadmin.dashboard') }}" 
                   class="px-4 py-2.5 rounded-xl bg-slate-100 hover:bg-slate-200 text-slate-700 font-semibold text-xs sm:text-sm border border-slate-200 transition-colors">
                    Kembali ke Panel
                </a>
            </div>
        </div>
    </div>

    <!-- FORM CARD (BRIGHT THEME, NO ICONS) -->
    <div class="bg-white rounded-3xl border border-slate-200 shadow-xs p-6 sm:p-8">
        <form action="{{ auth()->user()->peran === 'admin' ? route('admin.pengaturan-sistem.simpan') : route('superadmin.pengaturan-sistem.simpan') }}" 
              method="POST" 
              enctype="multipart/form-data" 
              class="space-y-8">
            @csrf

            <!-- SEKSI 1: LEGALITAS & IDENTITAS -->
            <div class="space-y-4">
                <div class="border-b border-slate-100 pb-2">
                    <h2 class="text-sm font-bold text-slate-900">Identitas & Legalitas Lisensi BNSP</h2>
                    <p class="text-[11px] text-slate-400">Informasi resmi yang tertera pada sertifikat kompetensi dan dokumen LSP</p>
                </div>

                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 sm:gap-5">
                    <div>
                        <label class="block text-xs font-bold text-slate-700 mb-1.5">Nama Resmi Lembaga (LSP) <span class="text-rose-500">*</span></label>
                        <input type="text" 
                               name="nama_lsp" 
                               value="{{ old('nama_lsp', $pengaturan->nama_lsp) }}" 
                               required 
                               class="w-full px-4 py-2.5 rounded-xl bg-slate-50 border border-slate-200 text-xs sm:text-sm text-slate-900 font-semibold focus:bg-white focus:outline-hidden focus:border-blue-600 focus:ring-1 focus:ring-blue-600 transition-all">
                    </div>

                    <div>
                        <label class="block text-xs font-bold text-slate-700 mb-1.5">Kode Singkat LSP <span class="text-rose-500">*</span></label>
                        <input type="text" 
                               name="kode_lsp" 
                               value="{{ old('kode_lsp', $pengaturan->kode_lsp) }}" 
                               required 
                               class="w-full px-4 py-2.5 rounded-xl bg-slate-50 border border-slate-200 text-xs sm:text-sm text-slate-900 font-semibold focus:bg-white focus:outline-hidden focus:border-blue-600 focus:ring-1 focus:ring-blue-600 transition-all">
                    </div>
                </div>

                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 sm:gap-5">
                    <div>
                        <label class="block text-xs font-bold text-slate-700 mb-1.5">Nomor SK Lisensi BNSP</label>
                        <input type="text" 
                               name="no_sk_lisensi" 
                               value="{{ old('no_sk_lisensi', $pengaturan->no_sk_lisensi) }}" 
                               placeholder="Contoh: KEP.1215/BNSP/V/2025" 
                               class="w-full px-4 py-2.5 rounded-xl bg-slate-50 border border-slate-200 text-xs sm:text-sm text-slate-900 font-mono focus:bg-white focus:outline-hidden focus:border-blue-600 focus:ring-1 focus:ring-blue-600 transition-all">
                    </div>

                    <div>
                        <label class="block text-xs font-bold text-slate-700 mb-1.5">Nomor Lisensi Resmi BNSP <span class="text-rose-500">*</span></label>
                        <input type="text" 
                               name="nomor_lisensi" 
                               value="{{ old('nomor_lisensi', $pengaturan->nomor_lisensi) }}" 
                               required 
                               placeholder="Contoh: BNSP-LSP-2629-ID" 
                               class="w-full px-4 py-2.5 rounded-xl bg-slate-50 border border-slate-200 text-xs sm:text-sm text-slate-900 font-mono font-bold focus:bg-white focus:outline-hidden focus:border-blue-600 focus:ring-1 focus:ring-blue-600 transition-all">
                    </div>
                </div>

                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 sm:gap-5">
                    <div>
                        <label class="block text-xs font-bold text-slate-700 mb-1.5">Masa Berlaku Lisensi</label>
                        <input type="text" 
                               name="masa_berlaku" 
                               value="{{ old('masa_berlaku', $pengaturan->masa_berlaku) }}" 
                               placeholder="Contoh: Hingga 23 Mei 2030" 
                               class="w-full px-4 py-2.5 rounded-xl bg-slate-50 border border-slate-200 text-xs sm:text-sm text-slate-900 focus:bg-white focus:outline-hidden focus:border-blue-600 focus:ring-1 focus:ring-blue-600 transition-all">
                    </div>

                    <div>
                        <label class="block text-xs font-bold text-slate-700 mb-1.5">Status Keaktifan Lisensi</label>
                        <select name="status_keaktifan" 
                                class="w-full px-4 py-2.5 rounded-xl bg-slate-50 border border-slate-200 text-xs sm:text-sm text-slate-900 font-semibold focus:bg-white focus:outline-hidden focus:border-blue-600 focus:ring-1 focus:ring-blue-600 transition-all">
                            <option value="Aktif" {{ old('status_keaktifan', $pengaturan->status_keaktifan) == 'Aktif' ? 'selected' : '' }}>Aktif (Berlaku)</option>
                            <option value="Perpanjangan" {{ old('status_keaktifan', $pengaturan->status_keaktifan) == 'Perpanjangan' ? 'selected' : '' }}>Dalam Proses Relisensi / Perpanjangan</option>
                            <option value="Tidak Aktif" {{ old('status_keaktifan', $pengaturan->status_keaktifan) == 'Tidak Aktif' ? 'selected' : '' }}>Tidak Aktif</option>
                        </select>
                    </div>
                </div>
            </div>

            <!-- SEKSI 2: KONTAK & ALAMAT -->
            <div class="space-y-4">
                <div class="border-b border-slate-100 pb-2">
                    <h2 class="text-sm font-bold text-slate-900">Kontak Resmi & Lokasi Sekretariat</h2>
                    <p class="text-[11px] text-slate-400">Saluran komunikasi yang dapat dihubungi oleh asesi dan publik</p>
                </div>

                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 sm:gap-5">
                    <div>
                        <label class="block text-xs font-bold text-slate-700 mb-1.5">Email Resmi LSP <span class="text-rose-500">*</span></label>
                        <input type="email" 
                               name="email_resmi" 
                               value="{{ old('email_resmi', $pengaturan->email_resmi) }}" 
                               required 
                               class="w-full px-4 py-2.5 rounded-xl bg-slate-50 border border-slate-200 text-xs sm:text-sm text-slate-900 focus:bg-white focus:outline-hidden focus:border-blue-600 focus:ring-1 focus:ring-blue-600 transition-all">
                    </div>

                    <div>
                        <label class="block text-xs font-bold text-slate-700 mb-1.5">Nomor Telepon Sekretariat <span class="text-rose-500">*</span></label>
                        <input type="text" 
                               name="nomor_telepon" 
                               value="{{ old('nomor_telepon', $pengaturan->nomor_telepon) }}" 
                               required 
                               class="w-full px-4 py-2.5 rounded-xl bg-slate-50 border border-slate-200 text-xs sm:text-sm text-slate-900 focus:bg-white focus:outline-hidden focus:border-blue-600 focus:ring-1 focus:ring-blue-600 transition-all">
                    </div>
                </div>

                <div>
                    <label class="block text-xs font-bold text-slate-700 mb-1.5">Alamat Operasional Lengkap <span class="text-rose-500">*</span></label>
                    <input type="text" 
                           name="alamat_lengkap" 
                           value="{{ old('alamat_lengkap', $pengaturan->alamat_lengkap) }}" 
                           required 
                           class="w-full px-4 py-2.5 rounded-xl bg-slate-50 border border-slate-200 text-xs sm:text-sm text-slate-900 focus:bg-white focus:outline-hidden focus:border-blue-600 focus:ring-1 focus:ring-blue-600 transition-all">
                </div>
            </div>

            <!-- SEKSI 3: TENTANG, VISI & MISI -->
            <div class="space-y-4">
                <div class="border-b border-slate-100 pb-2">
                    <h2 class="text-sm font-bold text-slate-900">Profil, Visi, dan Misi LSP</h2>
                    <p class="text-[11px] text-slate-400">Deskripsi institusional yang tampil pada halaman beranda publik</p>
                </div>

                <div>
                    <label class="block text-xs font-bold text-slate-700 mb-1.5">Tentang LSP (Profil Singkat)</label>
                    <textarea name="tentang_lsp" 
                              rows="3" 
                              class="w-full px-4 py-2.5 rounded-xl bg-slate-50 border border-slate-200 text-xs sm:text-sm text-slate-900 focus:bg-white focus:outline-hidden focus:border-blue-600 focus:ring-1 focus:ring-blue-600 transition-all">{{ old('tentang_lsp', $pengaturan->tentang_lsp) }}</textarea>
                </div>

                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 sm:gap-5">
                    <div>
                        <label class="block text-xs font-bold text-slate-700 mb-1.5">Visi Lembaga</label>
                        <textarea name="visi" 
                                  rows="4" 
                                  class="w-full px-4 py-2.5 rounded-xl bg-slate-50 border border-slate-200 text-xs sm:text-sm text-slate-900 focus:bg-white focus:outline-hidden focus:border-blue-600 focus:ring-1 focus:ring-blue-600 transition-all">{{ old('visi', $pengaturan->visi) }}</textarea>
                    </div>

                    <div>
                        <label class="block text-xs font-bold text-slate-700 mb-1.5">Misi Lembaga</label>
                        <textarea name="misi" 
                                  rows="4" 
                                  class="w-full px-4 py-2.5 rounded-xl bg-slate-50 border border-slate-200 text-xs sm:text-sm text-slate-900 focus:bg-white focus:outline-hidden focus:border-blue-600 focus:ring-1 focus:ring-blue-600 transition-all">{{ old('misi', $pengaturan->misi) }}</textarea>
                    </div>
                </div>
            </div>

            <!-- BUTTON ACTIONS (NO ICONS) -->
            <div class="pt-6 border-t border-slate-100 flex items-center justify-end gap-3">
                <button type="submit" 
                        class="px-6 py-2.5 rounded-xl bg-blue-600 hover:bg-blue-700 text-white font-bold text-xs sm:text-sm shadow-xs transition-colors cursor-pointer">
                    Simpan Seluruh Pengaturan
                </button>
            </div>
        </form>
    </div>

</div>
@endsection
