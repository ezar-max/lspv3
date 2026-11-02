@extends('tata-letak.dasbor')

@section('judul', 'Pengaturan Global LSP - Super Admin')

@section('konten')
<div class="space-y-6 max-w-5xl mx-auto pb-12">

    <!-- NOTIFIKASI SUKSES / ERROR -->
    @if(session('sukses'))
        <div class="p-4 rounded-2xl bg-emerald-50 border border-emerald-200 text-emerald-800 text-sm shadow-2xs flex items-center justify-between gap-3 animate-fade-in">
            <div class="flex items-center gap-3">
                <div class="w-8 h-8 rounded-xl bg-emerald-100 text-emerald-700 flex items-center justify-center shrink-0">
                    <i class="fa-solid fa-circle-check text-base"></i>
                </div>
                <div>
                    <span class="font-bold">Berhasil:</span> {{ session('sukses') }}
                </div>
            </div>
            <button type="button" @click="$el.parentElement.remove()" class="text-emerald-600 hover:text-emerald-900 font-bold px-2.5 py-1 text-xs rounded-lg hover:bg-emerald-100/50 transition-colors cursor-pointer">
                Tutup
            </button>
        </div>
    @endif

    @if(isset($errors) && $errors->any())
        <div class="p-4 rounded-2xl bg-rose-50 border border-rose-200 text-rose-800 text-sm shadow-2xs">
            <div class="flex items-center gap-2 font-bold mb-2 text-rose-900">
                <i class="fa-solid fa-circle-exclamation text-rose-600"></i>
                <span>Terjadi Kesalahan Validasi:</span>
            </div>
            <ul class="list-disc list-inside space-y-1 text-xs text-rose-700 ml-5">
                @foreach($errors->all() as $err)
                    <li>{{ $err }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <!-- EXECUTIVE HERO HEADER -->
    <div class="bg-white rounded-3xl p-6 sm:p-8 border border-slate-200/90 shadow-2xs">
        <div class="flex flex-col md:flex-row md:items-center justify-between gap-6">
            <div class="flex items-start sm:items-center gap-4 sm:gap-5">
                <div class="w-14 h-14 sm:w-16 sm:h-16 rounded-2xl bg-blue-50 border border-blue-200/80 text-blue-600 flex items-center justify-center text-2xl sm:text-3xl shrink-0 shadow-2xs">
                    <i class="fa-solid fa-sliders"></i>
                </div>
                <div class="space-y-1.5 min-w-0">
                    <div class="flex flex-wrap items-center gap-2">
                        <span class="inline-flex items-center gap-1.5 px-2.5 py-0.5 rounded-full bg-blue-50 border border-blue-200/80 text-blue-700 text-[11px] font-extrabold uppercase tracking-wider">
                            <span class="w-1.5 h-1.5 rounded-full bg-blue-600 animate-pulse"></span>
                            <span>Master System Configuration</span>
                        </span>
                        <span class="text-slate-300 hidden sm:inline">&bull;</span>
                        <span class="text-xs font-semibold text-slate-500">Parameter Lembaga &amp; Lisensi BNSP</span>
                    </div>
                    <h1 class="text-2xl sm:text-3xl font-black tracking-tight text-slate-900 leading-tight">
                        Pengaturan Global Sistem LSP
                    </h1>
                    <p class="text-slate-600 text-xs sm:text-sm max-w-2xl leading-relaxed">
                        Konfigurasi identitas resmi lembaga, nomor lisensi dan SK BNSP, masa aktif sertifikasi, saluran kontak operasional, serta profil institusi yang dipublikasikan secara nasional.
                    </p>
                </div>
            </div>

            <div class="flex items-center gap-3 shrink-0 self-start md:self-center">
                <a href="{{ route('superadmin.dashboard') }}" 
                   class="inline-flex items-center gap-2 px-4 py-2.5 rounded-xl bg-slate-100 hover:bg-slate-200 text-slate-700 font-bold text-xs sm:text-sm border border-slate-200 transition-colors">
                    <i class="fa-solid fa-arrow-left text-xs text-slate-500"></i>
                    <span>Kembali ke Panel</span>
                </a>
            </div>
        </div>
    </div>

    <!-- MAIN FORM CONTAINER -->
    <div class="bg-white rounded-3xl border border-slate-200/90 shadow-2xs p-6 sm:p-8">
        <form action="{{ auth()->user()->peran === 'admin' ? route('admin.pengaturan-sistem.simpan') : route('superadmin.pengaturan-sistem.simpan') }}" 
              method="POST" 
              enctype="multipart/form-data" 
              class="space-y-8">
            @csrf

            <!-- SEKSI 1: LEGALITAS & IDENTITAS -->
            <div class="space-y-5">
                <div class="flex items-center gap-3 pb-3 border-b border-slate-100">
                    <div class="w-9 h-9 rounded-xl bg-blue-50 text-blue-600 flex items-center justify-center shrink-0 border border-blue-100">
                        <i class="fa-solid fa-building-columns text-sm"></i>
                    </div>
                    <div>
                        <h2 class="text-sm sm:text-base font-bold text-slate-900">Identitas Resmi &amp; Legalitas Lisensi BNSP</h2>
                        <p class="text-[11px] text-slate-400">Informasi hukum resmi yang dicantumkan pada sertifikat uji kompetensi dan arsip BNSP</p>
                    </div>
                </div>

                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 sm:gap-5">
                    <div>
                        <label class="block text-xs font-bold text-slate-700 mb-1.5">
                            Nama Resmi Lembaga (LSP) <span class="text-rose-500">*</span>
                        </label>
                        <input type="text" 
                               name="nama_lsp" 
                               value="{{ old('nama_lsp', $pengaturan->nama_lsp) }}" 
                               required 
                               class="w-full px-4 py-2.5 rounded-xl bg-slate-50/70 border border-slate-200 text-xs sm:text-sm text-slate-900 font-bold focus:bg-white focus:outline-hidden focus:border-blue-600 focus:ring-2 focus:ring-blue-100 transition-all">
                    </div>

                    <div>
                        <label class="block text-xs font-bold text-slate-700 mb-1.5">
                            Kode Singkat Lembaga <span class="text-rose-500">*</span>
                        </label>
                        <input type="text" 
                               name="kode_lsp" 
                               value="{{ old('kode_lsp', $pengaturan->kode_lsp) }}" 
                               required 
                               class="w-full px-4 py-2.5 rounded-xl bg-slate-50/70 border border-slate-200 text-xs sm:text-sm text-slate-900 font-bold focus:bg-white focus:outline-hidden focus:border-blue-600 focus:ring-2 focus:ring-blue-100 transition-all">
                    </div>
                </div>

                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 sm:gap-5">
                    <div>
                        <label class="block text-xs font-bold text-slate-700 mb-1.5">
                            Nomor SK Lisensi BNSP
                        </label>
                        <input type="text" 
                               name="no_sk_lisensi" 
                               value="{{ old('no_sk_lisensi', $pengaturan->no_sk_lisensi) }}" 
                               placeholder="Contoh: KEP.1215/BNSP/V/2025" 
                               class="w-full px-4 py-2.5 rounded-xl bg-slate-50/70 border border-slate-200 text-xs sm:text-sm text-slate-900 font-mono focus:bg-white focus:outline-hidden focus:border-blue-600 focus:ring-2 focus:ring-blue-100 transition-all">
                    </div>

                    <div>
                        <label class="block text-xs font-bold text-slate-700 mb-1.5">
                            Nomor Lisensi Resmi BNSP <span class="text-rose-500">*</span>
                        </label>
                        <input type="text" 
                               name="nomor_lisensi" 
                               value="{{ old('nomor_lisensi', $pengaturan->nomor_lisensi) }}" 
                               required 
                               placeholder="Contoh: BNSP-LSP-2629-ID" 
                               class="w-full px-4 py-2.5 rounded-xl bg-slate-50/70 border border-slate-200 text-xs sm:text-sm text-slate-900 font-mono font-black focus:bg-white focus:outline-hidden focus:border-blue-600 focus:ring-2 focus:ring-blue-100 transition-all">
                    </div>
                </div>

                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 sm:gap-5">
                    <div>
                        <label class="block text-xs font-bold text-slate-700 mb-1.5">
                            Masa Berlaku Lisensi
                        </label>
                        <input type="text" 
                               name="masa_berlaku" 
                               value="{{ old('masa_berlaku', $pengaturan->masa_berlaku) }}" 
                               placeholder="Contoh: Hingga 23 Mei 2030" 
                               class="w-full px-4 py-2.5 rounded-xl bg-slate-50/70 border border-slate-200 text-xs sm:text-sm text-slate-900 focus:bg-white focus:outline-hidden focus:border-blue-600 focus:ring-2 focus:ring-blue-100 transition-all">
                    </div>

                    <div>
                        <label class="block text-xs font-bold text-slate-700 mb-1.5">
                            Status Keaktifan Lisensi
                        </label>
                        <select name="status_keaktifan" 
                                class="w-full px-4 py-2.5 rounded-xl bg-slate-50/70 border border-slate-200 text-xs sm:text-sm text-slate-900 font-bold focus:bg-white focus:outline-hidden focus:border-blue-600 focus:ring-2 focus:ring-blue-100 transition-all">
                            <option value="Aktif" {{ old('status_keaktifan', $pengaturan->status_keaktifan) == 'Aktif' ? 'selected' : '' }}>Aktif (Berlaku Penuh)</option>
                            <option value="Perpanjangan" {{ old('status_keaktifan', $pengaturan->status_keaktifan) == 'Perpanjangan' ? 'selected' : '' }}>Dalam Proses Relisensi / Perpanjangan</option>
                            <option value="Tidak Aktif" {{ old('status_keaktifan', $pengaturan->status_keaktifan) == 'Tidak Aktif' ? 'selected' : '' }}>Tidak Aktif / Ditangguhkan</option>
                        </select>
                    </div>
                </div>
            </div>

            <!-- SEKSI 2: KONTAK & ALAMAT -->
            <div class="space-y-5 pt-4">
                <div class="flex items-center gap-3 pb-3 border-b border-slate-100">
                    <div class="w-9 h-9 rounded-xl bg-blue-50 text-blue-600 flex items-center justify-center shrink-0 border border-blue-100">
                        <i class="fa-solid fa-address-book text-sm"></i>
                    </div>
                    <div>
                        <h2 class="text-sm sm:text-base font-bold text-slate-900">Kontak Resmi &amp; Lokasi Sekretariat</h2>
                        <p class="text-[11px] text-slate-400">Saluran informasi yang dapat dihubungi oleh asesi, asesor, dan publik</p>
                    </div>
                </div>

                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 sm:gap-5">
                    <div>
                        <label class="block text-xs font-bold text-slate-700 mb-1.5">
                            Email Resmi LSP <span class="text-rose-500">*</span>
                        </label>
                        <div class="relative">
                            <div class="absolute inset-y-0 left-0 pl-3.5 flex items-center pointer-events-none text-slate-400">
                                <i class="fa-regular fa-envelope text-xs"></i>
                            </div>
                            <input type="email" 
                                   name="email_resmi" 
                                   value="{{ old('email_resmi', $pengaturan->email_resmi) }}" 
                                   required 
                                   class="w-full pl-10 pr-4 py-2.5 rounded-xl bg-slate-50/70 border border-slate-200 text-xs sm:text-sm text-slate-900 focus:bg-white focus:outline-hidden focus:border-blue-600 focus:ring-2 focus:ring-blue-100 transition-all">
                        </div>
                    </div>

                    <div>
                        <label class="block text-xs font-bold text-slate-700 mb-1.5">
                            Nomor Telepon Sekretariat <span class="text-rose-500">*</span>
                        </label>
                        <div class="relative">
                            <div class="absolute inset-y-0 left-0 pl-3.5 flex items-center pointer-events-none text-slate-400">
                                <i class="fa-solid fa-phone text-xs"></i>
                            </div>
                            <input type="text" 
                                   name="nomor_telepon" 
                                   value="{{ old('nomor_telepon', $pengaturan->nomor_telepon) }}" 
                                   required 
                                   class="w-full pl-10 pr-4 py-2.5 rounded-xl bg-slate-50/70 border border-slate-200 text-xs sm:text-sm text-slate-900 focus:bg-white focus:outline-hidden focus:border-blue-600 focus:ring-2 focus:ring-blue-100 transition-all">
                        </div>
                    </div>
                </div>

                <div>
                    <label class="block text-xs font-bold text-slate-700 mb-1.5">
                        Alamat Operasional Lengkap <span class="text-rose-500">*</span>
                    </label>
                    <div class="relative">
                        <div class="absolute top-3 left-3.5 flex items-center pointer-events-none text-slate-400">
                            <i class="fa-solid fa-map-location-dot text-xs"></i>
                        </div>
                        <input type="text" 
                               name="alamat_lengkap" 
                               value="{{ old('alamat_lengkap', $pengaturan->alamat_lengkap) }}" 
                               required 
                               class="w-full pl-10 pr-4 py-2.5 rounded-xl bg-slate-50/70 border border-slate-200 text-xs sm:text-sm text-slate-900 focus:bg-white focus:outline-hidden focus:border-blue-600 focus:ring-2 focus:ring-blue-100 transition-all">
                    </div>
                </div>
            </div>

            <!-- SEKSI 3: TENTANG, VISI & MISI -->
            <div class="space-y-5 pt-4">
                <div class="flex items-center gap-3 pb-3 border-b border-slate-100">
                    <div class="w-9 h-9 rounded-xl bg-blue-50 text-blue-600 flex items-center justify-center shrink-0 border border-blue-100">
                        <i class="fa-solid fa-bullseye text-sm"></i>
                    </div>
                    <div>
                        <h2 class="text-sm sm:text-base font-bold text-slate-900">Profil Kelembagaan, Visi, dan Misi LSP</h2>
                        <p class="text-[11px] text-slate-400">Deskripsi institusional yang tampil pada halaman portal sertifikasi dan beranda publik</p>
                    </div>
                </div>

                <div>
                    <label class="block text-xs font-bold text-slate-700 mb-1.5">
                        Tentang LSP (Profil Singkat Institusi)
                    </label>
                    <textarea name="tentang_lsp" 
                              rows="3" 
                              class="w-full px-4 py-2.5 rounded-xl bg-slate-50/70 border border-slate-200 text-xs sm:text-sm text-slate-900 focus:bg-white focus:outline-hidden focus:border-blue-600 focus:ring-2 focus:ring-blue-100 transition-all leading-relaxed">{{ old('tentang_lsp', $pengaturan->tentang_lsp) }}</textarea>
                </div>

                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 sm:gap-5">
                    <div>
                        <label class="block text-xs font-bold text-slate-700 mb-1.5">
                            Visi Lembaga
                        </label>
                        <textarea name="visi" 
                                  rows="4" 
                                  class="w-full px-4 py-2.5 rounded-xl bg-slate-50/70 border border-slate-200 text-xs sm:text-sm text-slate-900 focus:bg-white focus:outline-hidden focus:border-blue-600 focus:ring-2 focus:ring-blue-100 transition-all leading-relaxed">{{ old('visi', $pengaturan->visi) }}</textarea>
                    </div>

                    <div>
                        <label class="block text-xs font-bold text-slate-700 mb-1.5">
                            Misi Lembaga
                        </label>
                        <textarea name="misi" 
                                  rows="4" 
                                  class="w-full px-4 py-2.5 rounded-xl bg-slate-50/70 border border-slate-200 text-xs sm:text-sm text-slate-900 focus:bg-white focus:outline-hidden focus:border-blue-600 focus:ring-2 focus:ring-blue-100 transition-all leading-relaxed">{{ old('misi', $pengaturan->misi) }}</textarea>
                    </div>
                </div>
            </div>

            <!-- BUTTON ACTIONS -->
            <div class="pt-6 border-t border-slate-100 flex items-center justify-end gap-3">
                <button type="submit" 
                        class="inline-flex items-center gap-2 px-6 py-2.5 rounded-xl bg-blue-600 hover:bg-blue-700 active:bg-blue-800 text-white font-bold text-xs sm:text-sm shadow-xs hover:shadow-md transition-all cursor-pointer">
                    <i class="fa-solid fa-floppy-disk text-xs"></i>
                    <span>Simpan Seluruh Pengaturan</span>
                </button>
            </div>
        </form>
    </div>

</div>
@endsection
