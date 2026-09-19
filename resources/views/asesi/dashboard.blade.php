@extends('tata-letak.dasbor')

@section('judul', 'Dashboard Asesi')

@push('css')
    <link rel="stylesheet" href="{{ asset('css/asesi/dashboard-asesi.css') }}">
    <style>
        .skema-selector-badge {
            background: rgba(255, 255, 255, 0.15);
            padding: 0.5rem 0.85rem;
            border-radius: 10px;
            display: inline-flex;
            align-items: center;
            gap: 0.5rem;
            border: 1px solid rgba(255, 255, 255, 0.2);
        }
        .progress-stat-pill {
            display: inline-flex;
            align-items: center;
            gap: 0.4rem;
            padding: 0.35rem 0.75rem;
            border-radius: 9999px;
            font-size: 0.8rem;
            font-weight: 700;
        }
        .card-context-detail {
            background: #ffffff;
            border: 1px solid var(--biru-soft);
            border-radius: var(--radius-md);
            padding: 1.1rem 1.25rem;
            margin-top: 1rem;
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 1rem;
        }
        .action-callout-box {
            margin-top: 1.25rem;
            padding: 1.25rem;
            border-radius: var(--radius-md);
            display: flex;
            justify-content: space-between;
            align-items: center;
            flex-wrap: wrap;
            gap: 1rem;
        }
        .tahap-summary-list {
            margin-top: 1.25rem;
            background: #f8fafc;
            border: 1px solid #e2e8f0;
            border-radius: var(--radius-md);
            padding: 1rem 1.25rem;
        }
    </style>
@endpush

@section('konten')
<div class="header-dashboard-asesi">
    <div class="ambient-glow-1"></div>
    <div class="ambient-glow-2"></div>

    <div class="header-asesi-content">
        <div class="avatar-asesi-box">
            <span>{{ strtoupper(substr($pengguna->nama_lengkap ?? 'A', 0, 2)) }}</span>
        </div>
        <div class="teks-header-asesi">
            <div class="header-title-row">
                <h1>Selamat Datang, {{ $pengguna->nama_lengkap }}!</h1>
                <span class="badge-role-asesi">
                    <span class="dot-aktif"></span> Asesi Terdaftar
                </span>
            </div>
            <p>Pantau progress pendaftaran dan jadwal uji kompetensi keahlian Anda secara real-time</p>
        </div>
<!-- =========================================================================
     HEADER DASHBOARD ASESI (BERSIH, TERBUKA & PROFESIONAL)
     - Tanpa Card Box / Banner Kaku
     - Tanpa Kotak Avatar Inisial
     - Tipografi Modern & Aksi Cepat
     ========================================================================= -->
<div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 mb-6">
    <div>
        <h1 class="text-xl sm:text-2xl font-bold text-slate-800 tracking-tight">
            Selamat Datang, {{ $pengguna->nama_lengkap }}!
        </h1>
        <p class="text-xs sm:text-sm text-slate-500 mt-0.5">
            Pantau progres pendaftaran dan jadwal uji kompetensi keahlian Anda secara real-time.
        </p>
    </div>

    <div class="header-asesi-actions">
    <div class="flex items-center gap-3 flex-wrap">
        @if(isset($semuaPendaftaran) && $semuaPendaftaran->count() > 1)
            <div class="skema-selector-badge-modern">
                <i class="fa-solid fa-graduation-cap" style="color: #0284c7; font-size: 0.85rem;"></i>
                <label>Skema:</label>
                <select onchange="window.location.href='?pendaftaran_id=' + this.value">
            <div class="inline-flex items-center gap-2 bg-white border border-slate-200/90 px-3 py-2 rounded-xl text-xs shadow-2xs">
                <i class="fa-solid fa-graduation-cap text-blue-600"></i>
                <label class="font-bold text-slate-500 text-[11px]">Skema:</label>
                <select onchange="window.location.href='?pendaftaran_id=' + this.value" class="bg-transparent border-0 text-slate-800 font-bold text-xs focus:ring-0 cursor-pointer outline-hidden pr-2">
                    @foreach($semuaPendaftaran as $itemP)
                        <option value="{{ $itemP->id }}" {{ (isset($pendaftaranTerakhir) && $pendaftaranTerakhir->id == $itemP->id) ? 'selected' : '' }}>
                            {{ $itemP->skema->kode_skema ?? 'SKEMA' }} - {{ Str::limit($itemP->skema->nama_skema ?? 'Skema', 22) }}
                            {{ $itemP->skema->kode_skema ?? 'SKEMA' }} - {{ Str::limit($itemP->skema->nama_skema ?? 'Skema', 24) }}
                        </option>
                    @endforeach
                </select>
            </div>
        @endif
        <a href="{{ route('asesi.pendaftaran') }}" class="btn-daftar-skema-baru">
            <i class="fa-solid fa-plus-circle"></i>

        <a href="{{ route('asesi.pendaftaran') }}" class="inline-flex items-center gap-2 bg-blue-600 hover:bg-blue-700 text-white text-xs sm:text-sm font-semibold px-4 py-2.5 rounded-xl shadow-xs hover:shadow transition-all">
            <i class="fa-solid fa-plus text-xs"></i>
            <span>Daftar Skema Baru</span>
        </a>
    </div>
</div>

<div class="grid-progress-asesi">
    <!-- STATUS PENDAFTARAN AKTIF -->
    <div class="kartu-status-pendaftaran">
        
        @if($pendaftaranTerakhir)
            @php
                $p = $pendaftaranTerakhir;
                
                // Status variables from database
                $statusPendaftaran = $p->status_pendaftaran ?? 'draft';
                $rekomAdmin = $p->rekomendasi_admin_status;
                $rekomAsesor = $p->rekomendasi_asesor_status;
                $statusAk01 = $p->status_ak01;
                
                $jumlahJawabanApl02 = $p->jawabanApl02 ? $p->jawabanApl02->count() : 0;
                $hasJawabanApl02 = ($jumlahJawabanApl02 > 0);
                
                $hasRekomendasi = ($p->rekomendasi != null || $statusPendaftaran === 'selesai');
                $isKompeten = ($p->rekomendasi && strtolower($p->rekomendasi->keputusan) === 'kompeten');
                $isBelumKompeten = ($p->rekomendasi && strtolower($p->rekomendasi->keputusan) === 'belum_kompeten');

                $isDitolakAdmin = ($statusPendaftaran === 'ditolak' || $rekomAdmin === 'tidak_diterima');
                $isDitolakAsesor = $p->isApl02Rejected();
                $isDitolak = ($isDitolakAdmin || $isDitolakAsesor);
                $isRevisi = ($statusPendaftaran === 'revisi' || $p->isApl02Revision() || $rekomAsesor === 'tidak_dapat_dilanjutkan');

                // Tanda Tangan AK-01 Flags
                $asesiTtdAk01 = !empty($p->tanda_tangan_asesi_ak01) || in_array($statusAk01, ['disetujui_asesi', 'selesai']);
                $asesorTtdAk01 = !empty($p->tanda_tangan_asesor_ak01) || ($statusAk01 === 'selesai');
                $isAk01Selesai = $asesiTtdAk01 && $asesorTtdAk01;

                // ==========================================
                // 1. TAHAP 1: FR.APL.01 (Permohonan Sertifikasi)
                // ==========================================
                if ($statusPendaftaran === 'draft') {
                    $tahap1 = ['state' => 'aktif', 'label' => 'Draft APL-01', 'sub' => 'Lengkapi & Kirim'];
                } elseif ($statusPendaftaran === 'revisi') {
                    $tahap1 = ['state' => 'revisi', 'label' => 'Perlu Revisi', 'sub' => 'Perbaiki Berkas'];
                } else {
                    $tahap1 = ['state' => 'selesai', 'label' => 'APL-01 Diajukan', 'sub' => 'Terkirim Lengkap'];
                }

                // ==========================================
                // 2. TAHAP 2: Verifikasi Berkas & Asesor (Admin LSP)
                // ==========================================
                if ($isDitolakAdmin) {
                    $tahap2 = ['state' => 'ditolak', 'label' => 'Ditolak Admin', 'sub' => 'Tidak Diterima'];
                } elseif ($tahap1['state'] !== 'selesai') {
                    $tahap2 = ['state' => 'menunggu', 'label' => 'Verifikasi Admin', 'sub' => 'Menunggu APL-01'];
                } elseif ($statusPendaftaran === 'diajukan') {
                    $tahap2 = ['state' => 'aktif', 'label' => 'Verifikasi Admin', 'sub' => 'Sedang Diperiksa'];
                } else {
                    $tahap2 = ['state' => 'selesai', 'label' => 'APL-01 Disetujui', 'sub' => 'Asesor Ditugaskan'];
                }

                // ==========================================
                // 3. TAHAP 3: FR.APL.02 (Asesmen Mandiri)
                // ==========================================
                if ($isDitolakAsesor) {
                    $tahap3 = ['state' => 'ditolak', 'label' => 'APL-02 Ditolak', 'sub' => 'Tidak Diterima'];
                } elseif ($tahap2['state'] !== 'selesai' || $isDitolakAdmin) {
                    $tahap3 = ['state' => 'menunggu', 'label' => 'APL-02 Mandiri', 'sub' => 'Terkunci'];
                } elseif ($p->isApl02Approved()) {
                    $tahap3 = ['state' => 'selesai', 'label' => 'APL-02 Disetujui', 'sub' => 'Kompeten (ACC Asesor)'];
                } elseif ($p->isApl02Revision()) {
                    $tahap3 = ['state' => 'revisi', 'label' => 'Revisi APL-02', 'sub' => 'Perbaiki Butir BK'];
                } elseif ($p->isApl02Submitted() || $p->isApl02UnderReview()) {
                    $tahap3 = ['state' => 'aktif', 'label' => 'APL-02 Terkirim', 'sub' => 'Menunggu Asesor'];
                } elseif (!$hasJawabanApl02) {
                    $tahap3 = ['state' => 'aktif', 'label' => 'APL-02 Mandiri', 'sub' => 'Wajib Anda Isi'];
                } else {
                    $tahap3 = ['state' => 'aktif', 'label' => 'APL-02 Draft', 'sub' => $jumlahJawabanApl02 . ' KUK Terisi (Draft)'];
                }

                // ==========================================
                // 4. TAHAP 4: Verifikasi Asesor & FR.AK.01 (Kesepakatan)
                // ==========================================
                if (!$p->isApl02Approved() || $isDitolak) {
                    $tahap4 = ['state' => 'menunggu', 'label' => 'Verif & AK-01', 'sub' => 'Terkunci (Tunggu ACC)'];
                } elseif ($asesiTtdAk01 && $asesorTtdAk01) {
                    $tahap4 = ['state' => 'selesai', 'label' => 'AK-01 Disetujui', 'sub' => 'Kesepakatan Sah'];
                } elseif ($asesiTtdAk01) {
                    $tahap4 = ['state' => 'aktif', 'label' => 'Pengesahan AK-01', 'sub' => 'Menunggu Asesor'];
                } else {
                    $tahap4 = ['state' => 'aktif', 'label' => 'Tanda Tangan AK-01', 'sub' => 'Wajib TTD Anda'];
                }

                // Check if exam is live / started
                $isRuangUjiOpen = $p->isRuangUjiOpen();
                $timeInfo = $p->assessment_time_status;
                $isUjianBerlangsung = $isRuangUjiOpen && !$hasRekomendasi;

                // ==========================================
                // 5. TAHAP 5: Pelaksanaan Uji Kompetensi & Rekomendasi
                // ==========================================
                if ($tahap4['state'] !== 'selesai' || $isDitolak) {
                    $tahap5 = ['state' => 'menunggu', 'label' => 'Pelaksanaan Uji', 'sub' => 'Terkunci'];
                } elseif ($hasRekomendasi) {
                    $keputusanTeks = strtoupper($p->rekomendasi->keputusan ?? 'SELESAI');
                    $tahap5 = ['state' => 'selesai', 'label' => 'Asesmen Selesai', 'sub' => 'Hasil: ' . $keputusanTeks];
                } elseif ($isUjianBerlangsung) {
                    $tahap5 = ['state' => 'aktif', 'label' => 'Asesmen Dimulai', 'sub' => 'Sesi Asesmen Aktif'];
                } else {
                    $tahap5 = ['state' => 'aktif', 'label' => 'Sesi Ujian', 'sub' => 'Menunggu Jadwal'];
                }

                // ==========================================
                // PERSENTASE PROGRESS AKURAT SESUAI REAL-TIME
                // ==========================================
                if ($isDitolak) {
                    $persentase = 0;
                    $badgeClass = 'background: #fee2e2; color: #dc2626; border: 1px solid #fca5a5;';
                    $statusHeader = 'Pendaftaran Ditolak';
                } elseif ($hasRekomendasi) {
                    $persentase = 100;
                    $badgeClass = $isKompeten ? 'background: #dcfce7; color: #15803d; border: 1px solid #86efac;' : 'background: #fee2e2; color: #dc2626; border: 1px solid #fca5a5;';
                    $statusHeader = 'Selesai (' . ($isKompeten ? 'KOMPETEN' : 'BELUM KOMPETEN') . ')';
                } elseif ($p->isApl02Revision() || $statusPendaftaran === 'revisi' || $isRevisi) {
                    $persentase = ($statusPendaftaran === 'revisi') ? 20 : 40;
                    $badgeClass = 'background: #fef3c7; color: #b45309; border: 1px solid #fde68a;';
                    $statusHeader = 'Revisi';
                } elseif ($tahap4['state'] === 'selesai') {
                    $persentase = 85;
                    if ($isUjianBerlangsung) {
                        $badgeClass = 'background: #ecfdf5; color: #047857; border: 1.5px solid #10b981; font-weight: 800; box-shadow: 0 0 10px rgba(16, 185, 129, 0.25);';
                        $statusHeader = 'Sesi Asesmen Telah Dimulai!';
                    } elseif ($timeInfo['status'] === 'selesai') {
                        $badgeClass = 'background: #f1f5f9; color: #475569; border: 1px solid #cbd5e1;';
                        $statusHeader = 'Sesi Asesmen Selesai (Menunggu Rekomendasi)';
                    } else {
                        $badgeClass = 'background: #fef3c7; color: #b45309; border: 1px solid #fde68a;';
                        $statusHeader = 'Menunggu Asesmen Dimulai';
                    }
                } elseif ($rekomAsesor === 'dapat_dilanjutkan' && $asesiTtdAk01) {
                    $persentase = 75;
                    $badgeClass = 'background: #fef3c7; color: #b45309; border: 1px solid #fde68a;';
                    $statusHeader = 'AK-01 Ditandatangani (Menunggu Asesor)';
                } elseif ($rekomAsesor === 'dapat_dilanjutkan') {
                    $persentase = 70;
                    $badgeClass = 'background: #fef3c7; color: #b45309; border: 1px solid #fde68a;';
                    $statusHeader = 'APL-02 Di-ACC (Wajib TTD FR.AK.01)';
                } elseif ($hasJawabanApl02) {
                    $persentase = 60;
                    $badgeClass = 'background: #fef3c7; color: #b45309; border: 1px solid #fde68a;';
                    $statusHeader = 'APL-02 Dikirim (Diverifikasi Asesor)';
                } elseif ($tahap2['state'] === 'selesai') {
                    $persentase = 45;
                    $badgeClass = 'background: #dcfce7; color: #15803d; border: 1px solid #86efac;' ;
                    $statusHeader = 'APL-01 Disetujui (Wajib Isi APL-02)';
                } elseif ($statusPendaftaran === 'diajukan') {
                    $persentase = 30;
                    $badgeClass = 'background: #fef3c7; color: #b45309; border: 1px solid #fde68a;';
                    $statusHeader = 'APL-01 Dikirim (Diverifikasi Admin)';
                } else {
                    $persentase = 15;
                    $badgeClass = 'background: #f1f5f9; color: #475569; border: 1px solid #cbd5e1;';
                    $statusHeader = 'Draft (Belum Dikirim)';
                }
            @endphp

            <!-- HEADER MONITORING -->
            <div style="display: flex; justify-content: space-between; align-items: flex-start; flex-wrap: wrap; gap: 1rem; margin-bottom: 1.25rem;">
                <div>
                    <div style="display: flex; align-items: center; gap: 0.6rem; margin-bottom: 0.35rem;">
                        <span class="lencana lencana-biru" style="font-weight: 800;">{{ $p->skema->kode_skema }}</span>
                        <span style="font-size: 0.82rem; color: #64748b; font-weight: 600;">No. Reg: <strong>{{ $p->nomor_pendaftaran }}</strong></span>
                    </div>
                    <h3 style="color: var(--biru-malam); margin: 0; font-size: 1.25rem;">{{ $p->skema->nama_skema }}</h3>
                </div>
                <div style="display: flex; flex-direction: column; align-items: flex-end; gap: 0.35rem;">
                    <span class="progress-stat-pill" style="{{ $badgeClass }}">
                        {{ $statusHeader }}
                    </span>
                    <span style="font-size: 0.78rem; color: #64748b; font-weight: 700;">Progress: {{ $persentase }}%</span>
                </div>
            </div>

            <!-- DETAIL KONTEN & KARTU KONTEN ASESOR/TUK -->
            <div class="card-context-detail">
                <div>
                    <div style="font-size: 0.75rem; font-weight: 700; color: #64748b; text-transform: uppercase;">Asesor Penguji:</div>
                    @if($p->asesor)
                        <strong style="font-size: 0.95rem; color: var(--biru-malam); display: block;">{{ $p->asesor->nama_lengkap }}</strong>
                        <span style="font-size: 0.78rem; color: var(--biru-utama);">No. Reg: {{ $p->asesor->nomor_registrasi ?? ('MET.000.00' . $p->asesor->id . ' 2026') }}</span>
                    @else
                        <span style="font-size: 0.88rem; color: #94a3b8; font-style: italic;">Menunggu Verifikasi Admin</span>
                    @endif
                </div>

                <div>
                    <div style="font-size: 0.75rem; font-weight: 700; color: #64748b; text-transform: uppercase;">Tempat Uji (TUK) & Jadwal:</div>
                    <strong style="font-size: 0.95rem; color: var(--biru-malam); display: block;">{{ $p->jadwal->nama_tuk ?? ($p->tuk_type ? 'TUK ' . $p->tuk_type : 'TUK Sewaktu') }}</strong>
                    <span style="font-size: 0.78rem; color: #64748b;">
                        {{ $p->jadwal ? \Carbon\Carbon::parse($p->jadwal->tanggal_uji)->format('d F Y') : 'Jadwal Ditentukan Kemudian' }}
                    </span>
                </div>

                <div>
                    <div style="font-size: 0.75rem; font-weight: 700; color: #64748b; text-transform: uppercase;">Tanggal Pengajuan:</div>
                    <strong style="font-size: 0.95rem; color: var(--biru-malam); display: block;">
                        {{ $p->tanggal_daftar ? \Carbon\Carbon::parse($p->tanggal_daftar)->format('d M Y') : date('d M Y') }}
                    </strong>
                    <span style="font-size: 0.78rem; color: #16a34a; font-weight: 600;">
                        {{ $p->dokumen->count() }} Dokumen Terunggah
                    </span>
                </div>
            </div>

            <!-- PERINGATAN DITOLAK JIKA ADA -->
            @if($isDitolak)
                <div style="margin-top: 1.25rem; background: #fef2f2; padding: 1.25rem; border-radius: var(--radius-md); border: 1.5px solid #fca5a5;">
                    <h4 style="color: #991b1b; margin-bottom: 0.5rem;">Pendaftaran Tidak Dapat Dilanjutkan (Ditolak)</h4>
                    <p style="font-size: 0.88rem; color: #7f1d1d; margin-bottom: 0.75rem; line-height: 1.5;">
                        @if($isDitolakAdmin)
                            Permohonan sertifikasi skema ini tidak disetujui oleh Verifikator Admin LSP.
                            @if($p->catatan_verifikasi)
                                <br><strong>Catatan Admin:</strong> <em>"{{ $p->catatan_verifikasi }}"</em>
                            @endif
                        @else
                            Permohonan FR.APL.02 Asesmen Mandiri ditolak oleh Asesor Penguji.
                            @if($p->catatan_peninjauan_asesor)
                                <br><strong>Catatan Asesor:</strong> <em>"{{ $p->catatan_peninjauan_asesor }}"</em>
                            @endif
                        @endif
                    </p>
                    <a href="{{ route('asesi.pendaftaran') }}" class="tombol tombol-utama tombol-sm" style="background: #dc2626; border-color: #dc2626;">
                        Ajukan Pendaftaran Skema Lainnya &rarr;
                    </a>
                </div>
            @endif

            <!-- ACTION CALLOUT REALTIME (PANDUAN LANGKAH UTAMA ASESI) -->
            @if(!$isDitolak && !$isRevisi)
                <div class="action-callout-box" style="background: {{ $hasRekomendasi ? '#f0fdf4' : ($tahap4['state'] === 'selesai' ? '#f0fdfa' : '#f0f9ff') }}; border: 1.5px solid {{ $hasRekomendasi ? '#86efac' : ($tahap4['state'] === 'selesai' ? '#99f6e4' : '#bae6fd') }};">
                    
                    <div style="flex: 1; min-width: 260px;">
                        @if($hasRekomendasi)
                            <div style="font-weight: 800; color: #166534; font-size: 1rem; margin-bottom: 0.25rem;">
                                Asesmen Telah Selesai & Hasil Kelulusan Tersedia!
                            </div>
                            <p style="font-size: 0.86rem; color: #14532d; margin: 0;">
                                Asesor Penguji telah menetapkan keputusan asesmen: <strong>{{ strtoupper($p->rekomendasi->keputusan ?? 'KOMPETEN') }}</strong>. Silakan unduh dan reviu lembar hasil asesmen Anda.
                            </p>
                        @elseif($tahap4['state'] === 'selesai')
                            @php
                                $timeInfo = $p->assessment_time_status;
                            @endphp
                            @if($timeInfo['status'] === 'belum_mulai')
                                <div style="font-weight: 800; color: #b45309; font-size: 1rem; margin-bottom: 0.25rem;">
                                    Menunggu Jadwal Asesmen Dimulai
                                </div>
                                <p style="font-size: 0.86rem; color: #78350f; margin: 0;">
                                    Persetujuan FR.AK.01 telah sah ditandatangani. Sesi asesmen akan dibuka otomatis sesuai jadwal pada tanggal dan jam di bawah ini di <strong>{{ $p->jadwal->nama_tuk ?? 'TUK' }}</strong>.
                                </p>
                            @elseif($timeInfo['status'] === 'selesai')
                                <div style="font-weight: 800; color: #475569; font-size: 1rem; margin-bottom: 0.25rem;">
                                    Sesi Asesmen Telah Berakhir
                                </div>
                                <p style="font-size: 0.86rem; color: #475569; margin: 0;">
                                    Waktu pelaksanaan ujian telah selesai pada pukul <strong>{{ $timeInfo['formatted_selesai'] }} WIB</strong>. Menunggu keputusan rekomendasi akhir dari Asesor Penguji.
                                </p>
                            @else
                                <div style="font-weight: 800; color: #166534; font-size: 1rem; margin-bottom: 0.25rem;">
                                    Sesi Asesmen Telah Dimulai!
                                </div>
                                <p style="font-size: 0.86rem; color: #14532d; margin: 0;">
                                    Waktu ujian aktif sampai pukul <strong>{{ $timeInfo['formatted_selesai'] }} WIB</strong> di <strong>{{ $p->jadwal->nama_tuk ?? 'TUK' }}</strong>. Silakan segera masuki Ruang Ujian untuk mengerjakan butir instrumen yang telah ditentukan.
                                </p>
                            @endif
                        @elseif($p->isApl02Approved() && !$asesiTtdAk01)
                            <div style="font-weight: 800; color: #92400e; font-size: 1rem; margin-bottom: 0.25rem;">
                                APL-02 Disetujui Asesor! Silakan Tanda Tangani FR.AK.01
                            </div>
                            <p style="font-size: 0.86rem; color: #78350f; margin: 0;">
                                Asesmen mandiri Anda telah disetujui (ACC) oleh Asesor. Langkah selanjutnya adalah menyepakati rencana asesmen dan menandatangani formulir FR.AK.01.
                            </p>
                        @elseif($p->isApl02Approved() && $asesiTtdAk01 && !$asesorTtdAk01)
                            <div style="font-weight: 800; color: #92400e; font-size: 1rem; margin-bottom: 0.25rem;">
                                FR.AK.01 Telah Anda Tandatangani (Menunggu Pengesahan Asesor)
                            </div>
                            <p style="font-size: 0.86rem; color: #78350f; margin: 0;">
                                Anda telah menandatangani kesepakatan FR.AK.01. Menunggu tanda tangan pengesahan dari Asesor Penguji ({{ $p->asesor->nama_lengkap ?? 'Asesor' }}).
                            </p>
                        @elseif($p->isApl02Revision())
                            <div style="font-weight: 800; color: #b91c1c; font-size: 1rem; margin-bottom: 0.25rem;">
                                FR.APL.02 Perlu Revisi
                            </div>
                            <p style="font-size: 0.86rem; color: #991b1b; margin: 0;">
                                Asesor meminta perbaikan pada asesmen mandiri atau bukti Anda. Silakan periksa catatan dan kirim ulang.
                            </p>
                        @elseif($p->isApl02Submitted() || $p->isApl02UnderReview())
                            <div style="font-weight: 800; color: #0369a1; font-size: 1rem; margin-bottom: 0.25rem;">
                                APL-02 Telah Dikirim & Sedang Diverifikasi Asesor
                            </div>
                            <p style="font-size: 0.86rem; color: #075985; margin: 0;">
                                Asesor Penguji (<strong>{{ $p->asesor->nama_lengkap ?? 'Asesor' }}</strong>) sedang memeriksa kelayakan bukti relevan dan isian asesmen mandiri Anda.
                            </p>
                        @elseif($tahap2['state'] === 'selesai')
                            <div style="font-weight: 800; color: #166534; font-size: 1rem; margin-bottom: 0.25rem;">
                                APL-01 Disetujui! Silakan Isi Asesmen Mandiri (FR.APL.02)
                            </div>
                            <p style="font-size: 0.86rem; color: #14532d; margin: 0;">
                                Admin LSP telah memvalidasi berkas dan menugaskan Asesor. Wajib lakukan penilaian mandiri (K/BK) pada Formulir FR.APL.02 untuk melanjutkan.
                            </p>
                        @elseif($statusPendaftaran === 'diajukan')
                            <div style="font-weight: 800; color: #0369a1; font-size: 1rem; margin-bottom: 0.25rem;">
                                Formulir APL-01 Diajukan (Menunggu Verifikasi Admin)
                            </div>
                            <p style="font-size: 0.86rem; color: #075985; margin: 0;">
                                Berkas permohonan sertifikasi Anda sedang dalam antrean pemeriksaan oleh Tim Verifikator Admin LSP.
                            </p>
                        @else
                            <div style="font-weight: 800; color: #0369a1; font-size: 1rem; margin-bottom: 0.25rem;">
                                Formulir APL-01 Masih Berstatus Draft
                            </div>
                            <p style="font-size: 0.86rem; color: #075985; margin: 0;">
                                Silakan lengkapi isian biodata, data pekerjaan, dan unggah dokumen bukti persyaratan dasar sebelum mengajukan.
                            </p>
                        @endif
                    </div>

                    <!-- TOMBOL AKSI TEPAT -->
                    <div>
                        @if($hasRekomendasi)
                            <a href="{{ route('asesi.hasil-nilai') }}" class="tombol tombol-utama" style="background: #16a34a; border-color: #16a34a; font-size: 0.9rem;">
                                Buka Lembar Hasil Nilai &rarr;
                            </a>
                        @elseif($tahap4['state'] === 'selesai' || ($p->isApl02Approved() && $isAk01Selesai))
                            @php
                                $timeInfo = $p->assessment_time_status;
                            @endphp
                            @if($timeInfo['status'] === 'belum_mulai')
                                <div style="display: flex; gap: 0.5rem; flex-wrap: wrap; align-items: center;">
                                    <button type="button" class="tombol tombol-sekunder" style="background: #fef3c7; color: #b45309; border-color: #fde68a; cursor: default; font-weight: 700; font-size: 0.88rem;">
                                        Dibuka Pukul {{ $timeInfo['formatted_mulai'] }} WIB
                                    </button>
                                    <a href="{{ route('asesi.tahapan', ['step' => 3, 'pendaftaran_id' => $p->id]) }}" class="tombol tombol-sekunder" style="font-size: 0.85rem;">
                                        Dokumen FR.AK.01
                                    </a>
                                    <a href="{{ route('asesi.ak07', ['pendaftaranId' => $p->id]) }}" class="tombol tombol-sekunder" style="font-size: 0.85rem;" title="Formulir Penyesuaian yang Wajar dan Beralasan (FR.AK.07)">
                                        Formulir FR.AK.07
                                    </a>
                                </div>
                            @elseif($timeInfo['status'] === 'selesai')
                                <div style="display: flex; gap: 0.5rem; flex-wrap: wrap; align-items: center;">
                                    <a href="{{ route('asesi.ujian', ['pendaftaran_id' => $p->id]) }}" class="tombol tombol-sekunder" style="font-size: 0.88rem;">
                                        Pratinjau Ruang Uji
                                    </a>
                                    <a href="{{ route('asesi.tahapan', ['step' => 3, 'pendaftaran_id' => $p->id]) }}" class="tombol tombol-sekunder" style="font-size: 0.85rem;">
                                        Dokumen FR.AK.01
                                    </a>
                                    <a href="{{ route('asesi.ak07', ['pendaftaranId' => $p->id]) }}" class="tombol tombol-sekunder" style="font-size: 0.85rem;" title="Formulir Penyesuaian yang Wajar dan Beralasan (FR.AK.07)">
                                        Formulir FR.AK.07
                                    </a>
                                </div>
                            @else
                                <div style="display: flex; gap: 0.5rem; flex-wrap: wrap; align-items: center;">
                                    <a href="{{ route('asesi.ruang-uji', ['pendaftaran_id' => $p->id]) }}" class="tombol tombol-utama" style="background: #16a34a; border-color: #16a34a; font-size: 0.92rem; font-weight: 800; box-shadow: 0 4px 6px -1px rgba(22, 163, 74, 0.3);">
                                        Masuk Ruang Uji &rarr;
                                    </a>
                                    <a href="{{ route('asesi.tahapan', ['step' => 3, 'pendaftaran_id' => $p->id]) }}" class="tombol tombol-sekunder" style="font-size: 0.85rem;">
                                        Dokumen FR.AK.01
                                    </a>
                                    <a href="{{ route('asesi.ak07', ['pendaftaranId' => $p->id]) }}" class="tombol tombol-sekunder" style="font-size: 0.85rem;" title="Formulir Penyesuaian yang Wajar dan Beralasan (FR.AK.07)">
                                        Formulir FR.AK.07
                                    </a>
                                </div>
                            @endif
                        @elseif($p->isApl02Approved() && !$asesiTtdAk01)
                            <div style="display: flex; gap: 0.5rem; flex-wrap: wrap; align-items: center;">
                                <a href="{{ route('asesi.tahapan', ['step' => 3, 'pendaftaran_id' => $p->id]) }}" class="tombol tombol-utama" style="background: #2563eb; border-color: #2563eb; font-size: 0.9rem;">
                                    Isi & TTD FR.AK.01 &rarr;
                                </a>
                                <a href="{{ route('asesi.ak07', ['pendaftaranId' => $p->id]) }}" class="tombol tombol-sekunder" style="font-size: 0.85rem;">
                                    Formulir FR.AK.07
                                </a>
                            </div>
                        @elseif($p->isApl02Approved() && $asesiTtdAk01 && !$asesorTtdAk01)
                            <div style="display: flex; gap: 0.5rem; flex-wrap: wrap; align-items: center;">
                                <a href="{{ route('asesi.tahapan', ['step' => 3, 'pendaftaran_id' => $p->id]) }}" class="tombol tombol-sekunder" style="font-size: 0.85rem;">
                                    Menunggu Asesor
                                </a>
                                <a href="{{ route('asesi.ak07', ['pendaftaranId' => $p->id]) }}" class="tombol tombol-sekunder" style="font-size: 0.85rem;">
                                    Formulir FR.AK.07
                                </a>
                            </div>
                        @elseif($p->isApl02Revision())
                            <a href="{{ route('asesi.tahapan', ['step' => 2, 'pendaftaran_id' => $p->id]) }}" class="tombol tombol-utama" style="background: #dc2626; border-color: #dc2626; font-size: 0.9rem;">
                                Perbaiki APL-02 &rarr;
                            </a>
                        @elseif($p->isApl02Submitted() || $p->isApl02UnderReview())
                            <a href="{{ route('asesi.tahapan', ['step' => 2, 'pendaftaran_id' => $p->id]) }}" class="tombol tombol-sekunder" style="font-size: 0.85rem;">
                                Tinjau Isian APL-02
                            </a>
                        @elseif($tahap2['state'] === 'selesai')
                            <a href="{{ route('asesi.tahapan', ['step' => 2, 'pendaftaran_id' => $p->id]) }}" class="tombol tombol-utama" style="background: #16a34a; border-color: #16a34a; font-size: 0.9rem;">
                                Isi Asesmen Mandiri (APL-02) &rarr;
                            </a>
                        @elseif($statusPendaftaran === 'diajukan')
                            <a href="{{ route('asesi.tahapan', ['step' => 1, 'pendaftaran_id' => $p->id]) }}" class="tombol tombol-sekunder" style="font-size: 0.85rem;">
                                Lihat Dokumen APL-01
                            </a>
                        @else
                            <a href="{{ route('asesi.tahapan', ['step' => 1, 'pendaftaran_id' => $p->id]) }}" class="tombol tombol-utama" style="font-size: 0.9rem;">
                                Lengkapi & Ajukan APL-01 &rarr;
                            </a>
                        @endif
                    </div>

                </div>
            @endif

            <!-- KARTU JADWAL & FORMULIR ASESMEN YANG DITENTUKAN -->
            @if($tahap4['state'] === 'selesai' && !$hasRekomendasi)
                @php
                    $jadwal = $p->jadwal;
                    $tglUjian = $jadwal && $jadwal->tanggal_uji ? \Carbon\Carbon::parse($jadwal->tanggal_uji)->translatedFormat('l, d F Y') : 'Menunggu Penetapan Tanggal';
                    $jamMulai = $jadwal && $jadwal->jam_mulai ? date('H:i', strtotime($jadwal->jam_mulai)) : '08:00';
                    $jamSelesai = $jadwal && $jadwal->jam_selesai ? date('H:i', strtotime($jadwal->jam_selesai)) : '16:00';
                    $namaTuk = $jadwal->nama_tuk ?? ($p->tuk_type ? 'TUK ' . $p->tuk_type : 'TUK Sewaktu');

                    $listFormulir = [];
                    if ($p->isInstrumenAktif('FR.IA.02') && ($p->skema ? $p->skema->hasInstrumen('FR.IA.02') : true)) {
                        $listFormulir[] = [
                            'kode' => 'FR.IA.02',
                            'nama' => 'Tugas Praktik Demonstrasi',
                            'tab' => 'praktik',
                            'deskripsi' => 'Pengujian observasi demonstrasi praktik langsung sesuai SKKNI'
                        ];
                    }
                    if ($p->isInstrumenAktif('FR.IA.05') && ($p->skema ? $p->skema->hasInstrumen('FR.IA.05') : true)) {
                        $listFormulir[] = [
                            'kode' => 'FR.IA.05',
                            'nama' => 'Pertanyaan Tertulis Pilihan Ganda (CBT)',
                            'tab' => 'cbt',
                            'deskripsi' => 'Ujian teori objektif pilihan ganda online'
                        ];
                    }
                    if ($p->isInstrumenAktif('FR.IA.06') && ($p->skema ? $p->skema->hasInstrumen('FR.IA.06') : true)) {
                        $listFormulir[] = [
                            'kode' => 'FR.IA.06',
                            'nama' => 'Pertanyaan Tertulis Esai',
                            'tab' => 'esai',
                            'deskripsi' => 'Ujian jawaban tertulis esai studi kasus'
                        ];
                    }
                    if ($p->isInstrumenAktif('FR.IA.04A') && ($p->skema ? $p->skema->hasInstrumen('FR.IA.04A') : false)) {
                        $listFormulir[] = [
                            'kode' => 'FR.IA.04A',
                            'nama' => 'Penjelasan Proyek Singkat / Proyek Kerja',
                            'tab' => 'proyek',
                            'deskripsi' => 'Pengumpulan berkas proyek kerja terstruktur'
                        ];
                    }
                    if (empty($listFormulir) && $p->skema) {
                        if ($p->skema->hasInstrumen('FR.IA.05')) {
                            $listFormulir[] = ['kode' => 'FR.IA.05', 'nama' => 'Pertanyaan Tertulis Pilihan Ganda (CBT)', 'tab' => 'cbt', 'deskripsi' => 'Ujian teori objektif pilihan ganda online'];
                        }
                        if ($p->skema->hasInstrumen('FR.IA.06')) {
                            $listFormulir[] = ['kode' => 'FR.IA.06', 'nama' => 'Pertanyaan Tertulis Esai', 'tab' => 'esai', 'deskripsi' => 'Ujian jawaban tertulis esai studi kasus'];
                        }
                        if ($p->skema->hasInstrumen('FR.IA.02')) {
                            $listFormulir[] = ['kode' => 'FR.IA.02', 'nama' => 'Tugas Praktik Demonstrasi', 'tab' => 'praktik', 'deskripsi' => 'Pengujian observasi demonstrasi praktik langsung'];
                        }
                    }
                @endphp

                <div style="margin-top: 1.25rem; background: #ffffff; border: 1.5px solid {{ $isUjianBerlangsung ? '#86efac' : '#cbd5e1' }}; border-radius: var(--radius-md); padding: 1.25rem; box-shadow: 0 1px 3px 0 rgba(0, 0, 0, 0.05);">
                    <div style="display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 0.75rem; border-bottom: 1px solid #e2e8f0; padding-bottom: 0.85rem; margin-bottom: 1rem;">
                        <div>
                            <h4 style="color: var(--biru-malam); margin: 0; font-size: 1.05rem; font-weight: 800;">
                                {{ $isUjianBerlangsung ? 'Sesi Asesmen Telah Dimulai!' : 'Jadwal & Formulir Pelaksanaan Asesmen' }}
                            </h4>
                            <p style="font-size: 0.82rem; color: #64748b; margin: 0.2rem 0 0;">
                                {{ $isUjianBerlangsung ? 'Sesi ujian sedang aktif. Silakan pilih dan kerjakan butir formulir yang telah ditentukan di bawah ini.' : 'Berikut adalah jadwal pelaksanaan dan daftar formulir instrumen yang akan Anda kerjakan saat sesi dimulai.' }}
                            </p>
                        </div>
                        @if($isUjianBerlangsung)
                            <a href="{{ route('asesi.ruang-uji', ['pendaftaran_id' => $p->id]) }}" class="tombol tombol-utama" style="background: #16a34a; border-color: #16a34a; font-size: 0.88rem; font-weight: 800; padding: 0.5rem 1.1rem;">
                                Masuk Ruang Uji &rarr;
                            </a>
                        @endif
                    </div>

                    <!-- RINGKASAN JADWAL (TANGGAL & JAM SAJA, TANPA HITUNGAN MUNDUR) -->
                    <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 0.85rem; background: #f8fafc; border: 1px solid #e2e8f0; border-radius: 10px; padding: 0.9rem 1.1rem; margin-bottom: 1.25rem;">
                        <div>
                            <div style="font-size: 0.72rem; font-weight: 700; color: #64748b; text-transform: uppercase;">Tanggal Pelaksanaan:</div>
                            <strong style="font-size: 0.92rem; color: var(--biru-malam); display: block; margin-top: 0.15rem;">{{ $tglUjian }}</strong>
                        </div>
                        <div>
                            <div style="font-size: 0.72rem; font-weight: 700; color: #64748b; text-transform: uppercase;">Jam Pelaksanaan:</div>
                            <strong style="font-size: 0.92rem; color: var(--biru-malam); display: block; margin-top: 0.15rem;">Pukul {{ $jamMulai }} - {{ $jamSelesai }} WIB</strong>
                        </div>
                        <div>
                            <div style="font-size: 0.72rem; font-weight: 700; color: #64748b; text-transform: uppercase;">Tempat Uji Kompetensi (TUK):</div>
                            <strong style="font-size: 0.92rem; color: var(--biru-malam); display: block; margin-top: 0.15rem;">{{ $namaTuk }}</strong>
                        </div>
                    </div>

                    <!-- DAFTAR FORMULIR YANG TELAH DITENTUKAN -->
                    <div>
                        <div style="font-size: 0.8rem; font-weight: 800; color: #475569; text-transform: uppercase; margin-bottom: 0.75rem;">
                            Daftar Formulir Asesmen yang Ditentukan:
                        </div>
                        <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(260px, 1fr)); gap: 0.85rem;">
                            @foreach($listFormulir as $formItem)
                                <div style="background: #ffffff; border: 1px solid #e2e8f0; border-radius: 10px; padding: 0.9rem 1rem; display: flex; flex-direction: column; justify-content: space-between; gap: 0.75rem;">
                                    <div>
                                        <div style="display: flex; align-items: center; justify-content: space-between; gap: 0.5rem; margin-bottom: 0.35rem;">
                                            <span style="font-size: 0.75rem; font-weight: 800; background: #e0f2fe; color: #0369a1; padding: 0.2rem 0.5rem; border-radius: 6px;">
                                                {{ $formItem['kode'] }}
                                            </span>
                                            @if($isUjianBerlangsung)
                                                <span style="font-size: 0.72rem; font-weight: 700; color: #16a34a;">
                                                    Siap Dikerjakan
                                                </span>
                                            @elseif($timeInfo['status'] === 'selesai')
                                                <span style="font-size: 0.72rem; font-weight: 700; color: #64748b;">
                                                    Sesi Selesai
                                                </span>
                                            @else
                                                <span style="font-size: 0.72rem; font-weight: 700; color: #b45309; background: #fef3c7; padding: 0.15rem 0.5rem; border-radius: 6px;">
                                                    Menunggu Asesmen Dimulai
                                                </span>
                                            @endif
                                        </div>
                                        <div style="font-weight: 700; color: #1e293b; font-size: 0.88rem;">
                                            {{ $formItem['nama'] }}
                                        </div>
                                        <p style="font-size: 0.78rem; color: #64748b; margin: 0.25rem 0 0; line-height: 1.4;">
                                            {{ $formItem['deskripsi'] }}
                                        </p>
                                    </div>

                                    @if($isUjianBerlangsung)
                                        <a href="{{ route('asesi.ruang-uji', ['pendaftaran_id' => $p->id, 'tab' => $formItem['tab']]) }}" class="tombol tombol-utama tombol-sm" style="background: #2563eb; border-color: #2563eb; font-size: 0.8rem; text-align: center; width: 100%;">
                                            Kerjakan {{ $formItem['kode'] }} &rarr;
                                        </a>
                                    @endif
                                </div>
                            @endforeach
                        </div>
                    </div>
                </div>
            @endif

            <!-- PERINGATAN REVISI & RINCIAN DOKUMEN (DI PALING BAWAH KARTU) -->
            @if($statusPendaftaran === 'revisi' && $p->catatan_verifikasi)
                @php
                    $docsPerluRevisi = $p->dokumen->where('status_verifikasi', 'tidak_valid');
                @endphp
                <div style="margin-top: 1.5rem; background: #fffbeb; padding: 1.25rem; border-radius: 12px; border: 1px solid #fde68a;">
                    <!-- Header Kotak Revisi -->
                    <div style="display: flex; align-items: center; justify-content: space-between; margin-bottom: 0.85rem; flex-wrap: wrap; gap: 0.5rem;">
                        <div style="font-weight: 700; color: #92400e; font-size: 0.95rem; display: flex; align-items: center; gap: 0.45rem;">
                            Catatan Revisi dari Admin LSP
                        </div>
                        <span style="font-size: 0.72rem; font-weight: 700; background: #ea580c; color: #ffffff; padding: 0.15rem 0.55rem; border-radius: 4px; text-transform: uppercase;">
                            Perlu Perbaikan
                        </span>
                    </div>

                    <!-- Daftar Dokumen yang Tidak Valid -->
                    @if($docsPerluRevisi->count() > 0)
                        <div style="background: #ffffff; padding: 0.75rem 1rem; border-radius: 8px; border: 1px solid #fed7aa; margin-bottom: 0.85rem;">
                            <div style="font-size: 0.82rem; font-weight: 700; color: #9a3412; margin-bottom: 0.4rem; display: flex; align-items: center; gap: 0.35rem;">
                                Dokumen yang Perlu Diperbaiki / Diunggah Ulang:
                            </div>
                            <ul style="margin: 0; padding-left: 1.25rem; font-size: 0.84rem; color: #7c2d12; line-height: 1.6;">
                                @foreach($docsPerluRevisi as $dRev)
                                    <li>
                                        <strong>{{ $dRev->jenis_dokumen }}</strong>
                                        @if($dRev->catatan)
                                            <span style="color: #c2410c;">&mdash; Catatan: {{ $dRev->catatan }}</span>
                                        @endif
                                    </li>
                                @endforeach
                            </ul>
                        </div>
                    @endif

                    <!-- Footer & Tombol Aksi Tunggal -->
                    <div style="display: flex; align-items: center; justify-content: space-between; flex-wrap: wrap; gap: 0.75rem; padding-top: 0.25rem;">
                        <p style="font-size: 0.82rem; color: #92400e; margin: 0;">
                            Silakan perbarui formulir atau unggah berkas pengganti untuk diverifikasi kembali oleh Admin LSP.
                        </p>
                        <a href="{{ route('asesi.tahapan', ['step' => 1, 'pendaftaran_id' => $p->id]) . '#subseksi-bukti-persyaratan' }}" 
                           class="tombol tombol-utama" 
                           style="background: #d97706; border-color: #d97706; font-size: 0.85rem; padding: 0.5rem 1.15rem; border-radius: 8px;">
                            Perbaiki Formulir APL-01 Sekarang &rarr;
                        </a>
                    </div>
                </div>
            @endif

        @else
            <div style="text-align: center; padding: 3.5rem 1rem; color: var(--abu-teks);">
                <div style="width: 70px; height: 70px; background: #f1f5f9; color: var(--biru-utama); border-radius: 50%; display: flex; align-items: center; justify-content: center; margin: 0 auto 1.25rem auto; font-size: 2rem;">
                    </div>
                <h3 style="color: var(--biru-malam); margin-bottom: 0.5rem;">Anda Belum Terdaftar Pada Skema Apapun</h3>
                <p style="font-size: 0.92rem; max-width: 500px; margin: 0 auto 1.5rem auto; line-height: 1.5;">
                    Silakan pilih dan daftarkan diri Anda pada skema uji kompetensi yang tersedia di LSP untuk memulai proses sertifikasi.
                </p>
                <a href="{{ route('asesi.pendaftaran') }}" class="tombol tombol-utama" style="padding: 0.75rem 1.75rem;">
                    Mendaftar Skema Sekarang
                </a>
            </div>
        @endif

    </div>

    <!-- KARTU KELENGKAPAN PROFIL & JALUR CEPAT -->
    <div class="kartu">
        <h4 style="color: var(--biru-malam); margin-bottom: 0.75rem;">Kelengkapan Biodata</h4>
        <div style="display: flex; align-items: center; gap: 1rem; margin-bottom: 1rem;">
            <div style="width: 52px; height: 52px; border-radius: 50%; background: #e0f2fe; display: flex; align-items: center; justify-content: center; color: var(--biru-utama); font-weight: 800; font-size: 1.25rem; flex-shrink: 0;">
                {{ strtoupper(substr($pengguna->nama_lengkap, 0, 2)) }}
            </div>
            <div>
                <div style="font-weight: 800; color: var(--biru-malam); font-size: 1.05rem;">{{ $pengguna->nama_lengkap }}</div>
                <div style="font-size: 0.82rem; color: var(--abu-teks);">NIK: {{ $profil->nik ?? 'Belum diisi' }}</div>
                <div style="font-size: 0.78rem; color: #16a34a; font-weight: 600;">
                    {{ !empty($pengguna->tanda_tangan) ? 'TTD Digital Tersimpan' : 'TTD Belum Disimpan' }}
                </div>
            </div>
        </div>
        
        <p style="font-size: 0.85rem; color: var(--abu-teks); margin-bottom: 1.25rem; line-height: 1.5;">
            Pastikan biodata, pas foto, dan Tanda Tangan Digital (TTD) Anda sudah lengkap untuk penerbitan dokumen sertifikasi resmi BNSP.
        </p>

        <a href="{{ route('asesi.profil') }}" class="tombol tombol-sekunder tombol-sm" style="width: 100%; text-align: center; margin-bottom: 1.25rem;">
            Edit Profil & TTD Digital
        </a>

        <!-- JALUR PINTAS CEPAT ASESI -->
        <div style="border-top: 1px solid #e2e8f0; padding-top: 1rem;">
            <div style="font-size: 0.78rem; font-weight: 800; color: #64748b; text-transform: uppercase; margin-bottom: 0.75rem;">
                Menu Cepat Asesi:
            </div>
            <div style="display: flex; flex-direction: column; gap: 0.5rem;">
                <a href="{{ route('asesi.formulir') }}" style="display: flex; align-items: center; justify-content: space-between; padding: 0.6rem 0.85rem; background: #f8fafc; border: 1px solid #e2e8f0; border-radius: 8px; color: #1e293b; font-size: 0.85rem; font-weight: 700; text-decoration: none; transition: all 0.2s;">
                    <span>Portal Formulir Terpadu</span>
                    </a>
                <a href="{{ route('asesi.ak07') }}" style="display: flex; align-items: center; justify-content: space-between; padding: 0.6rem 0.85rem; background: #f8fafc; border: 1px solid #e2e8f0; border-radius: 8px; color: #1e293b; font-size: 0.85rem; font-weight: 700; text-decoration: none; transition: all 0.2s;">
                    <span>Penyesuaian Asesmen (FR.AK.07)</span>
                    <i class="fa-solid fa-arrow-right" style="color: #94a3b8; font-size: 0.75rem;"></i>
                </a>
                <a href="{{ route('asesi.hasil-nilai') }}" style="display: flex; align-items: center; justify-content: space-between; padding: 0.6rem 0.85rem; background: #f8fafc; border: 1px solid #e2e8f0; border-radius: 8px; color: #1e293b; font-size: 0.85rem; font-weight: 700; text-decoration: none; transition: all 0.2s;">
                    <span>Lembar Hasil & Rekomendasi</span>
                    </a>
            </div>
        </div>

    </div>
</div>
@endsection

@push('js')
@if(session('notif_ak01_selesai'))
<script>
    document.addEventListener('DOMContentLoaded', function() {
        if (typeof Swal !== 'undefined') {
            Swal.fire({
                icon: 'success',
                title: 'Persetujuan FR.AK.01 Selesai!',
                text: 'Persetujuan Asesmen (FR.AK.01) telah berhasil ditandatangani. Silakan menunggu sesi asesmen Anda dimulai sesuai jadwal.',
                confirmButtonColor: '#2563eb',
                confirmButtonText: 'Oke'
            });
        }
    });
</script>
@endif
@endpush
