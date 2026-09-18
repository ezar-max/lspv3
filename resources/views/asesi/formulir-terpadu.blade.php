@extends('tata-letak.dasbor')

@section('judul', 'Portal Formulir & Asesmen Terpadu')

@push('css')
<style>
    .portal-formulir-container {
        max-width: 1100px;
        margin: 0 auto;
    }

    /* HEADER BANNER */
    .portal-header-card {
        background: linear-gradient(135deg, #1e3a8a 0%, #0284c7 100%);
        color: #ffffff;
        padding: 1.75rem 2rem;
        border-radius: var(--radius-lg);
        margin-bottom: 1.5rem;
        box-shadow: 0 10px 25px -5px rgba(2, 132, 199, 0.25);
        display: flex;
        justify-content: space-between;
        align-items: center;
        flex-wrap: wrap;
        gap: 1.25rem;
    }

    .portal-header-teks h1 {
        font-size: 1.6rem;
        font-weight: 800;
        margin-bottom: 0.35rem;
        color: #ffffff;
        display: flex;
        align-items: center;
        gap: 0.6rem;
    }

    .portal-header-teks p {
        font-size: 0.92rem;
        color: rgba(255, 255, 255, 0.85);
        margin: 0;
    }

    /* SKEMA SELECTOR & ACTIONS */
    .portal-skema-selector {
        background: rgba(255, 255, 255, 0.15);
        backdrop-filter: blur(10px);
        padding: 0.75rem 1.25rem;
        border-radius: var(--radius-md);
        border: 1px solid rgba(255, 255, 255, 0.25);
        display: flex;
        align-items: center;
        gap: 0.75rem;
    }

    /* STEP NAVIGATION TABS */
    .nav-tabs-portal {
        display: grid;
        grid-template-columns: repeat(3, 1fr);
        gap: 1rem;
        margin-bottom: 2rem;
    }

    .tab-item-portal {
        background: #ffffff;
        border: 2px solid var(--abu-border);
        border-radius: var(--radius-md);
        padding: 1.25rem 1.15rem;
        cursor: pointer;
        transition: all 0.25s ease;
        position: relative;
        text-align: left;
        display: flex;
        flex-direction: column;
        justify-content: space-between;
    }

    .tab-item-portal:hover:not(.terkunci) {
        border-color: var(--biru-utama);
        transform: translateY(-2px);
        box-shadow: var(--bayangan-soft);
    }

    .tab-item-portal.aktif {
        border-color: var(--biru-utama);
        background: #f0f9ff;
        box-shadow: 0 4px 15px -2px rgba(2, 132, 199, 0.15);
    }

    .tab-item-portal.aktif::after {
        content: '';
        position: absolute;
        bottom: -2px;
        left: 0;
        right: 0;
        height: 4px;
        background: var(--biru-utama);
        border-radius: 0 0 var(--radius-md) var(--radius-md);
    }

    .tab-item-portal.terkunci {
        background: #f8fafc;
        border-color: #e2e8f0;
        cursor: not-allowed;
        opacity: 0.75;
    }

    .tab-header-top {
        display: flex;
        justify-content: space-between;
        align-items: center;
        margin-bottom: 0.5rem;
    }

    .tab-number-badge {
        width: 32px;
        height: 32px;
        border-radius: 50%;
        background: #e2e8f0;
        color: #475569;
        display: flex;
        align-items: center;
        justify-content: center;
        font-weight: 800;
        font-size: 0.9rem;
    }

    .tab-item-portal.aktif .tab-number-badge {
        background: var(--biru-utama);
        color: #ffffff;
    }

    .tab-item-portal.selesai .tab-number-badge {
        background: var(--hijau-sukses);
        color: #ffffff;
    }

    .tab-title {
        font-size: 1.05rem;
        font-weight: 700;
        color: var(--biru-malam);
        margin-bottom: 0.2rem;
    }

    .tab-subtitle {
        font-size: 0.8rem;
        color: var(--abu-teks);
        line-height: 1.35;
    }

    /* CARD WRAPPERS */
    .portal-card {
        background: #ffffff;
        border: 1px solid #e2e8f0;
        border-radius: var(--radius-lg);
        padding: 2.25rem;
        box-shadow: 0 4px 20px -2px rgba(15, 23, 42, 0.05);
        margin-bottom: 2rem;
    }

    .portal-card-header {
        border-bottom: 2px solid #f1f5f9;
        padding-bottom: 1.25rem;
        margin-bottom: 1.75rem;
        display: flex;
        justify-content: space-between;
        align-items: center;
        flex-wrap: wrap;
        gap: 1rem;
    }

    .portal-card-title {
        font-size: 1.3rem;
        font-weight: 800;
        color: var(--biru-malam);
        margin: 0;
        display: flex;
        align-items: center;
        gap: 0.5rem;
    }

    /* REJECTION ALERT */
    .alert-rejection-card {
        background: #fef2f2;
        border: 2px solid #f87171;
        border-radius: var(--radius-md);
        padding: 1.5rem;
        color: #991b1b;
        margin-bottom: 1.75rem;
        display: flex;
        gap: 1.25rem;
        align-items: flex-start;
    }

    .alert-lock-card {
        background: #f8fafc;
        border: 2px dashed #cbd5e1;
        border-radius: var(--radius-md);
        padding: 2.5rem 1.5rem;
        text-align: center;
        color: #64748b;
        margin-bottom: 1.75rem;
    }

    /* FORM SUBSECTIONS */
    .subseksi-form {
        background: #f8fafc;
        border: 1px solid #e2e8f0;
        border-radius: var(--radius-md);
        padding: 1.5rem;
        margin-bottom: 1.75rem;
    }

    .subseksi-header {
        font-size: 1.05rem;
        font-weight: 700;
        color: var(--biru-malam);
        margin-bottom: 1.25rem;
        display: flex;
        align-items: center;
        gap: 0.5rem;
        border-bottom: 1px solid #e2e8f0;
        padding-bottom: 0.65rem;
    }

    /* APL-02 TABLES */
    .tabel-unit-header {
        background: #1e293b;
        color: #ffffff;
        padding: 1rem 1.25rem;
        border-radius: var(--radius-md) var(--radius-md) 0 0;
        font-weight: 700;
        font-size: 0.95rem;
        display: flex;
        justify-content: space-between;
        align-items: center;
    }

    .tabel-unit-wrap {
        border: 1px solid #e2e8f0;
        border-radius: var(--radius-md);
        overflow: hidden;
        margin-bottom: 1.75rem;
    }

    .tabel-kuk {
        width: 100%;
        border-collapse: collapse;
    }

    .tabel-kuk th {
        background: #f1f5f9;
        color: #334155;
        padding: 0.75rem 1rem;
        font-size: 0.85rem;
        font-weight: 700;
        border: 1px solid #e2e8f0;
        text-align: center;
    }

    .tabel-kuk td {
        padding: 0.85rem 1rem;
        border: 1px solid #e2e8f0;
        vertical-align: top;
        font-size: 0.88rem;
    }

    /* CANVAS SIGNATURE */
    .canvas-signature-wrap {
        border: 2px dashed #94a3b8;
        border-radius: var(--radius-md);
        background: #f8fafc;
        text-align: center;
        padding: 0.75rem;
        margin-top: 0.5rem;
    }

    .canvas-signature {
        background: #ffffff;
        border: 1px solid #cbd5e1;
        border-radius: var(--radius-sm);
        cursor: crosshair;
        width: 100%;
        max-width: 480px;
        height: 160px;
        touch-action: none;
    }

    @media (max-width: 768px) {
        .nav-tabs-portal {
            grid-template-columns: 1fr;
        }
        .portal-header-card {
            flex-direction: column;
            align-items: flex-start;
        }
    }
</style>
@endpush

@section('konten')
@php
    $pengguna = $pengguna ?? auth()->user();
    $profil = $profil ?? ($pengguna->profilAsesi ?? ($pendaftaran->asesi->profilAsesi ?? null));
    $semuaPendaftaran = $semuaPendaftaran ?? (isset($pendaftaran) && $pendaftaran ? collect([$pendaftaran]) : collect([]));
    $pendaftaran = $pendaftaran ?? $semuaPendaftaran->first();
    $isModeBaru = $isModeBaru ?? false;
    $skemaList = $skemaList ?? \App\Models\SkemaSertifikasi::where('status_aktif', true)->with('unitKompetensi.elemenKompetensi.kriteriaUnjukKerja')->get();
    $ditolakSkemaIds = $ditolakSkemaIds ?? [];
    $runningSkemaIds = $runningSkemaIds ?? [];

    $isDraft = $isDraft ?? ($pendaftaran ? in_array($pendaftaran->status_pendaftaran, ['draft', 'revisi']) : false);
    $isDiajukan = $isDiajukan ?? ($pendaftaran ? ($pendaftaran->status_pendaftaran === 'diajukan') : false);
    $isDitolakAdmin = $isDitolakAdmin ?? ($pendaftaran ? ($pendaftaran->status_pendaftaran === 'ditolak' || $pendaftaran->rekomendasi_admin_status === 'tidak_diterima') : false);
    $isAccAdmin = $isAccAdmin ?? ($pendaftaran ? ($pendaftaran->status_pendaftaran === 'diverifikasi' && $pendaftaran->rekomendasi_admin_status === 'diterima') : false);
    $isApl02Selesai = $isApl02Selesai ?? ($pendaftaran && $pendaftaran->jawabanApl02 ? ($pendaftaran->jawabanApl02->count() > 0) : false);

    $isApl02Approved = $isApl02Approved ?? ($pendaftaran ? $pendaftaran->isApl02Approved() : false);
    $isApl02Revision = $isApl02Revision ?? ($pendaftaran ? $pendaftaran->isApl02Revision() : false);
    $isApl02Submitted = $isApl02Submitted ?? ($pendaftaran ? $pendaftaran->isApl02Submitted() : false);
    $isApl02UnderReview = $isApl02UnderReview ?? ($pendaftaran ? $pendaftaran->isApl02UnderReview() : false);
    $isApl02Draft = $isApl02Draft ?? ($pendaftaran ? $pendaftaran->isApl02Draft() : true);

    $isDitolakAsesor = $isDitolakAsesor ?? ($pendaftaran ? $pendaftaran->isApl02Rejected() : false);
    $isAccAsesor = $isApl02Approved;
    $isAk01Selesai = $isAk01Selesai ?? ($pendaftaran ? (!empty($pendaftaran->tanda_tangan_asesi_ak01) || in_array($pendaftaran->status_ak01, ['disetujui_asesi', 'selesai'])) : false);
    $isTotalSelesai = $isTotalSelesai ?? ($isAk01Selesai && ($pendaftaran && $pendaftaran->rekomendasi != null));

    $tabAktif = $tabAktif ?? (request()->get('tab') ?? 'apl01');

    $draftData = $draftData ?? [
        'nama_lengkap' => $pengguna->nama_lengkap ?? ($pendaftaran->asesi->nama_lengkap ?? ''),
        'nik' => $profil->nik ?? '',
        'tempat_lahir' => $profil->tempat_lahir ?? '',
        'tanggal_lahir' => $profil->tanggal_lahir ?? '',
        'jenis_kelamin' => $profil->jenis_kelamin ?? 'Laki-laki',
        'kebangsaan' => $pendaftaran->kebangsaan ?? 'Indonesia',
        'pendidikan_terakhir' => $profil->pendidikan_terakhir ?? '',
        'alamat' => $profil->alamat ?? '',
        'kode_pos' => $pendaftaran->kode_pos ?? null,
        'no_telp_rumah' => $pendaftaran->no_telp_rumah ?? null,
        'nomor_telepon' => $pengguna->nomor_telepon ?? ($pendaftaran->asesi->nomor_telepon ?? ''),
        'nama_sekolah_instansi' => $pendaftaran->nama_perusahaan ?? ($profil->nama_sekolah_instansi ?? ''),
        'pekerjaan' => $pendaftaran->jabatan_perusahaan ?? ($profil->pekerjaan ?? ''),
        'alamat_kantor' => $pendaftaran->alamat_kantor ?? null,
        'kode_pos_kantor' => $pendaftaran->kode_pos_kantor ?? null,
        'telp_kantor' => $pendaftaran->telp_kantor ?? null,
        'fax_kantor' => $pendaftaran->fax_kantor ?? null,
        'email_kantor' => $pendaftaran->email_kantor ?? null,
        'skema_id' => $pendaftaran ? $pendaftaran->skema_id : (old('skema_id') ?? null),
        'tujuan_asesmen' => $pendaftaran ? $pendaftaran->tujuan_asesmen : 'Sertifikasi',
        'tujuan_asesmen_lainnya' => $pendaftaran ? $pendaftaran->tujuan_asesmen_lainnya : null,
        'bukti_persyaratan_dasar' => $pendaftaran ? ($pendaftaran->bukti_persyaratan_dasar ?? []) : [],
        'bukti_administratif' => $pendaftaran ? ($pendaftaran->bukti_administratif ?? []) : [],
        'tanda_tangan_asesi' => $pendaftaran ? ($pendaftaran->tanda_tangan_asesi ?? ($pengguna->tanda_tangan ?? null)) : ($pengguna->tanda_tangan ?? null),
    ];

    $jawabanMap = $jawabanMap ?? (($pendaftaran && $pendaftaran->jawabanApl02) ? $pendaftaran->jawabanApl02->keyBy('elemen_id') : collect([]));
    $buktiApl02Map = $buktiApl02Map ?? (($pendaftaran && $pendaftaran->buktiApl02) ? $pendaftaran->buktiApl02->groupBy('elemen_id') : collect([]));
    $dokumenList = $dokumenList ?? ($pendaftaran ? $pendaftaran->dokumen : collect([]));
    $dokumenTeknis = $dokumenTeknis ?? collect($dokumenList)->filter(function ($dok) {
        $jenis = strtolower($dok->jenis_dokumen ?? '');
        return !str_contains($jenis, 'ktp') && !str_contains($jenis, 'pasfoto') && !str_contains($jenis, 'foto');
    });
@endphp
<div class="portal-formulir-container animasi-slide">

    <!-- HEADER KHUSUS ASESOR / ADMIN (REVIEW MODE) -->
    @if(auth()->check() && auth()->user()->peran !== 'asesi')
        <div style="display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 1rem; margin-bottom: 1.5rem; background: #ffffff; padding: 1rem 1.5rem; border-radius: var(--radius-md); border: 1px solid #e2e8f0; box-shadow: 0 1px 3px rgba(0,0,0,0.05);">
            <div style="display: flex; align-items: center; gap: 0.75rem;">
                <a href="{{ route('asesor.daftar-peserta') }}" class="tombol tombol-sekunder tombol-sm" style="font-weight: 700;">
                    Kembali ke Daftar Peserta
                </a>
                <span class="lencana lencana-biru" style="font-size: 0.82rem; padding: 0.3rem 0.6rem;">FR.APL.01 Permohonan Sertifikasi</span>
            </div>
            <div style="font-size: 0.88rem; color: #475569;">
                Asesi: <strong style="color: #0f172a;">{{ $pendaftaran->asesi->nama_lengkap ?? ($pengguna->nama_lengkap ?? 'Asesi') }}</strong> | Skema: <strong style="color: #0f172a;">{{ $pendaftaran->skema->nama_skema ?? '-' }}</strong>
            </div>
        </div>
    @else
        <!-- HEADER PORTAL TERPADU UNTUK ASESI -->
        <div class="portal-header-card">
            <div class="portal-header-teks">
                <h1>Portal Formulir & Asesmen Terpadu</h1>
                <p>Pengisian terpadu FR.APL.01, FR.APL.02, dan FR.AK.01 dalam satu pintu akses</p>
            </div>
        </div>

        <!-- NOTIFIKASI JIKA PENDAFTARAN DITOLAK (HANYA 1X MUNCUL) -->
        @if(!empty($tampilkanNotifDitolak) && !empty($pendaftaranTerakhirDitolak))
            @php
                $infoDitolak = $pendaftaranTerakhirDitolak;
                $namaSkemaDitolak = $infoDitolak->skema->nama_skema ?? 'Sertifikasi';
                $catatanDitolak = $infoDitolak->catatan_verifikasi ?? ($infoDitolak->catatan_peninjauan_asesor ?? null);
            @endphp
            <div class="alert-rejection-card animasi-slide" id="alert-penolakan-notif">
                <div style="flex: 1;">
                    <div style="display: flex; justify-content: space-between; align-items: flex-start; gap: 1rem;">
                        <h3 style="font-size: 1.15rem; font-weight: 800; color: #991b1b; margin-bottom: 0.4rem;">
                            Pendaftaran Skema "{{ $namaSkemaDitolak }}" Dinyatakan DITOLAK
                        </h3>
                        <button type="button" onclick="tutupAlertPenolakan()" style="background: transparent; border: none; font-size: 1.25rem; color: #991b1b; cursor: pointer; padding: 0.2rem 0.5rem; line-height: 1;" title="Tutup Notifikasi">
                            &times;
                        </button>
                    </div>
                    <p style="font-size: 0.9rem; line-height: 1.5; margin-bottom: 0.75rem;">
                        Permohonan sertifikasi (FR.APL.01) Anda tidak dapat diterima oleh Verifikator Admin LSP.
                        @if($catatanDitolak)
                            <br><strong>Alasan / Catatan Admin:</strong> <em>"{{ $catatanDitolak }}"</em>
                        @endif
                    </p>
                    <div style="background: #ffffff; padding: 0.75rem 1rem; border-radius: var(--radius-sm); border: 1px solid #fecaca; font-size: 0.85rem; color: #7f1d1d; display: flex; align-items: center; gap: 0.5rem;">
                        Formulir FR.APL.01 otomatis di-reset. Silakan pilih skema sertifikasi lainnya yang tersedia pada formulir di bawah.
                    </div>
                </div>
            </div>
            <script>
                try {
                    if (localStorage.getItem('notif_ditolak_dismissed_{{ $pendaftaranTerakhirDitolak->id }}')) {
                        var el = document.getElementById('alert-penolakan-notif');
                        if (el) el.style.display = 'none';
                    }
                } catch(e) {}
            </script>
        @endif

        <!-- TAB STEPPER NAVIGATION UNTUK ASESI -->
        <div class="nav-tabs-portal">
            <!-- TAB 1: APL-01 -->
            <div class="tab-item-portal {{ $tabAktif === 'apl01' ? 'aktif' : '' }} {{ ($isAccAdmin || $isDiajukan) ? 'selesai' : '' }}" onclick="gantiTabPortal('apl01')">
                <div class="tab-header-top">
                    <div class="tab-number-badge">
                        @if($isAccAdmin)
                            &#10003;
                        @elseif($isDitolakAdmin)
                            &#10005;
                        @else
                            1
                        @endif
                    </div>
                    <div>
                        @if($isDitolakAdmin)
                            <span class="lencana lencana-merah">&times; Ditolak</span>
                        @elseif($isAccAdmin)
                            <span class="lencana lencana-hijau">Di-ACC Admin</span>
                        @elseif($isDiajukan)
                            <span class="lencana lencana-amber">Diajukan</span>
                        @elseif($pendaftaran && $pendaftaran->status_pendaftaran === 'revisi')
                            <span class="lencana lencana-merah">Revisi</span>
                        @else
                            <span class="lencana lencana-biru">Draft</span>
                        @endif
                    </div>
                </div>
                <div class="tab-title">1. FR.APL.01 Permohonan</div>
                <div class="tab-subtitle">Data Pribadi, Pilihan Skema, dan Berkas Persyaratan</div>
            </div>

            <!-- TAB 2: APL-02 -->
            @php
                $isApl02Locked = !$isAccAdmin || $isDitolakAdmin;
            @endphp
            <div class="tab-item-portal {{ $tabAktif === 'apl02' ? 'aktif' : '' }} {{ $isApl02Approved ? 'selesai' : '' }} {{ $isApl02Locked ? 'terkunci' : '' }}" onclick="{{ $isApl02Locked ? 'peringatanTerkunci(\'apl02\')' : 'gantiTabPortal(\'apl02\')' }}">
                <div class="tab-header-top">
                    <div class="tab-number-badge">
                        @if($isApl02Approved)
                            &#10003;
                        @elseif($isApl02Locked)
                            
                        @else
                            2
                        @endif
                    </div>
                    <div>
                        @if($isApl02Approved)
                            <span class="lencana lencana-hijau">Disetujui Asesor</span>
                        @elseif($isApl02Revision)
                            <span class="lencana lencana-merah">Perlu Revisi</span>
                        @elseif($isApl02UnderReview)
                            <span class="lencana lencana-biru">Sedang Diperiksa</span>
                        @elseif($isApl02Submitted)
                            <span class="lencana lencana-amber">Menunggu Pemeriksaan</span>
                        @elseif($isApl02Locked)
                            <span class="lencana lencana-abu"> Terkunci</span>
                        @else
                            <span class="lencana lencana-biru">Siap Diisi</span>
                        @endif
                    </div>
                </div>
                <div class="tab-title">2. FR.APL.02 Asesmen Mandiri</div>
                <div class="tab-subtitle">Penilaian Mandiri K/BK & Bukti Portofolio</div>
            </div>

            <!-- TAB 3: AK-01 -->
            @php
                $isAk01Unlocked = $pendaftaran && $pendaftaran->isAk01Unlocked();
                $isAk01Locked = !$isAk01Unlocked || $isDitolakAdmin;
            @endphp
            <div class="tab-item-portal {{ $tabAktif === 'ak01' ? 'aktif' : '' }} {{ $isAk01Selesai ? 'selesai' : '' }} {{ $isAk01Locked ? 'terkunci' : '' }}" onclick="{{ $isAk01Locked ? 'peringatanTerkunci(\'ak01\')' : 'gantiTabPortal(\'ak01\')' }}">
                <div class="tab-header-top">
                    <div class="tab-number-badge">
                        @if($isAk01Selesai)
                            &#10003;
                        @elseif($isAk01Locked)
                            
                        @else
                            3
                        @endif
                    </div>
                    <div>
                        @if($isAk01Selesai)
                            <span class="lencana lencana-hijau">Disetujui & TTD</span>
                        @elseif(!$isAk01Unlocked)
                            <span class="lencana lencana-abu"> Menunggu APL-02 Disetujui</span>
                        @else
                            <span class="lencana lencana-biru" style="background: #e0f2fe; color: #0369a1;">Siap Diisi & TTD ✍️</span>
                        @endif
                    </div>
                </div>
                <div class="tab-title">3. FR.AK.01 Persetujuan & Kerahasiaan</div>
                <div class="tab-subtitle">Persetujuan Asesmen, Metode Uji & Kerahasiaan</div>
            </div>
        </div>
    @endif

    <!-- ======================================================================= -->
    <!-- TAB 1 CONTENT: FR.APL.01 PERMOHONAN SERTIFIKASI                         -->
    <!-- ======================================================================= -->
    <div id="konten-tab-apl01" style="display: {{ $tabAktif === 'apl01' ? 'block' : 'none' }};">
        <div class="portal-card">
            <!-- KOP RESMI DOKUMEN STANDAR BNSP -->
            @include('komponen.kop-formulir-bnsp', [
                'kodeForm' => 'FR.APL.01',
                'judulForm' => 'PERMOHONAN SERTIFIKASI KOMPETENSI',
                'tipeDokumen' => 'Permohonan Sertifikasi',
                'subJudul' => 'Lengkapi rincian data pemohon, pilih skema sertifikasi, dan unggah dokumen bukti persyaratan dasar.'
            ])

                @if($pendaftaran)
                    <div>
                        <span style="font-size: 0.85rem; color: var(--abu-teks);">No. Registrasi:</span>
                        <strong style="color: var(--biru-malam); font-size: 0.95rem;">{{ $pendaftaran->nomor_pendaftaran }}</strong>
                    </div>
                @endif
            </div>

            @if($pendaftaran && $pendaftaran->status_pendaftaran === 'revisi' && $pendaftaran->catatan_verifikasi)
                <div style="background: #fffbebf5; color: #92400e; padding: 1.25rem; border-radius: var(--radius-md); border: 1.5px solid #fcd34d; margin-bottom: 1.75rem;">
                    <h4 style="color: #92400e; margin-bottom: 0.4rem; display: flex; align-items: center; gap: 0.5rem;">
                        CATATAN PERBAIKAN / REVISI DARI ADMIN LSP:
                    </h4>
                    <div style="font-size: 0.92rem; background: #ffffff; padding: 0.75rem 1rem; border-radius: var(--radius-sm); border: 1px solid #fde68a; color: #78350f; white-space: pre-line; line-height: 1.5;">
                        {!! nl2br(e($pendaftaran->catatan_verifikasi)) !!}
                    </div>
                </div>
            @endif

            @if($isDiajukan)
                <div style="background: #f0f9ff; color: #0369a1; padding: 1.25rem 1.5rem; border-radius: var(--radius-md); border: 1.5px solid #bae6fd; margin-bottom: 1.75rem; display: flex; align-items: center; gap: 1rem;">
                    <div>
                        <strong style="display: block; font-size: 1rem; color: #0369a1;">Formulir FR.APL.01 Sedang Dalam Proses Verifikasi Admin LSP</strong>
                        <span style="font-size: 0.88rem; color: #0c4a6e;">Data dan dokumen persyaratan Anda telah terkirim. Begitu Admin menyetujui (ACC), <strong>Tab 2 (FR.APL.02 Asesmen Mandiri)</strong> akan terbuka secara otomatis.</span>
                    </div>
                </div>
            @elseif($isAccAdmin)
                <div style="background: #f0fdf4; color: #166534; padding: 1.25rem 1.5rem; border-radius: var(--radius-md); border: 1.5px solid #bbf7d0; margin-bottom: 1.75rem; display: flex; align-items: center; justify-content: space-between; flex-wrap: wrap; gap: 1rem;">
                    <div style="display: flex; align-items: center; gap: 1rem;">
                        <div>
                            <strong style="display: block; font-size: 1rem; color: #166534;">Formulir FR.APL.01 Telah Disetujui (ACC) oleh Admin LSP</strong>
                            <span style="font-size: 0.88rem; color: #15803d;">
                                Asesor Ditugaskan: <strong>{{ $pendaftaran->asesor->nama_lengkap ?? 'Asesor Resmi LSP' }}</strong> | Jadwal TUK: <strong>{{ $pendaftaran->jadwal->nama_tuk ?? 'TUK Lab Komputer' }}</strong>
                            </span>
                        </div>
                    </div>
                    @if(!$isApl02Selesai)
                        <button type="button" class="tombol tombol-sukses tombol-sm" onclick="gantiTabPortal('apl02')">
                            Lanjut Isi FR.APL.02 &rarr;
                        </button>
                    @endif
                </div>
            @endif

            <!-- FORMULIR APL-01 LENGKAP -->
            <form action="{{ route('asesi.formulir.simpan-apl01') }}" method="POST" enctype="multipart/form-data" id="form-apl01-terpadu">
                @csrf
                <input type="hidden" name="pendaftaran_id" value="{{ $pendaftaran->id ?? '' }}">

                <!-- SUBSEKSI 1: DATA PEMOHON -->
                <div class="subseksi-form">
                    <div class="subseksi-header">
                        Bagian 1: Rincian Data Pemohon Sertifikasi
                    </div>

                    <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(280px, 1fr)); gap: 1.25rem;">
                        <div class="grup-form">
                            <label class="label-form">Nama Lengkap</label>
                            <input type="text" name="nama_lengkap" class="input-control" value="{{ old('nama_lengkap', $draftData['nama_lengkap']) }}" required {{ ($isDiajukan || $isAccAdmin) ? 'readonly' : '' }}>
                        </div>
                        <div class="grup-form">
                            <label class="label-form">No. KTP / NIK (Wajib 16 Digit) <span style="color: var(--merah-bahaya);">*</span></label>
                            <input type="text" name="nik" class="input-control" placeholder="16 digit NIK" minlength="16" maxlength="16" pattern="[0-9]{16}" inputmode="numeric" oninput="this.value = this.value.replace(/[^0-9]/g, '').slice(0, 16)" title="NIK harus berjumlah tepat 16 digit angka" value="{{ old('nik', $draftData['nik']) }}" required {{ ($isDiajukan || $isAccAdmin) ? 'readonly' : '' }}>
                        </div>
                    </div>

                    <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 1.25rem; margin-top: 1rem;">
                        <div class="grup-form">
                            <label class="label-form">Tempat Lahir</label>
                            <input type="text" name="tempat_lahir" class="input-control" value="{{ old('tempat_lahir', $draftData['tempat_lahir']) }}" required {{ ($isDiajukan || $isAccAdmin) ? 'readonly' : '' }}>
                        </div>
                        <div class="grup-form">
                            <label class="label-form">Tanggal Lahir</label>
                            <input type="date" name="tanggal_lahir" class="input-control" value="{{ old('tanggal_lahir', $draftData['tanggal_lahir']) }}" max="{{ date('Y-m-d') }}" required {{ ($isDiajukan || $isAccAdmin) ? 'readonly' : '' }}>
                        </div>
                        <div class="grup-form">
                            <label class="label-form">Jenis Kelamin</label>
                            <select name="jenis_kelamin" class="input-control" required {{ ($isDiajukan || $isAccAdmin) ? 'disabled' : '' }}>
                                <option value="Laki-laki" {{ old('jenis_kelamin', $draftData['jenis_kelamin']) == 'Laki-laki' ? 'selected' : '' }}>Laki-laki</option>
                                <option value="Perempuan" {{ old('jenis_kelamin', $draftData['jenis_kelamin']) == 'Perempuan' ? 'selected' : '' }}>Perempuan</option>
                            </select>
                        </div>
                    </div>

                    <div style="display: grid; grid-template-columns: 2fr 1fr; gap: 1.25rem; margin-top: 1rem;">
                        <div class="grup-form">
                            <label class="label-form">Alamat Rumah Tinggal</label>
                            <input type="text" name="alamat" class="input-control" value="{{ old('alamat', $draftData['alamat']) }}" required {{ ($isDiajukan || $isAccAdmin) ? 'readonly' : '' }}>
                        </div>
                        <div class="grup-form">
                            <label class="label-form">Nomor WhatsApp / HP</label>
                            <input type="text" name="nomor_telepon" class="input-control" value="{{ old('nomor_telepon', $draftData['nomor_telepon']) }}" required {{ ($isDiajukan || $isAccAdmin) ? 'readonly' : '' }}>
                        </div>
                    </div>

                    <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(240px, 1fr)); gap: 1.25rem; margin-top: 1rem;">
                        <div class="grup-form">
                            <label class="label-form">Kualifikasi Pendidikan</label>
                            <input type="text" name="pendidikan_terakhir" class="input-control" placeholder="contoh: SMK Teknik Komputer" value="{{ old('pendidikan_terakhir', $draftData['pendidikan_terakhir']) }}" {{ ($isDiajukan || $isAccAdmin) ? 'readonly' : '' }}>
                        </div>
                        <div class="grup-form">
                            <label class="label-form">Nama Sekolah / Lembaga / Kantor</label>
                            <input type="text" name="nama_sekolah_instansi" class="input-control" placeholder="contoh: SMK TI Indonesia" value="{{ old('nama_sekolah_instansi', $draftData['nama_sekolah_instansi']) }}" {{ ($isDiajukan || $isAccAdmin) ? 'readonly' : '' }}>
                        </div>
                    </div>
                </div>

                <!-- SUBSEKSI 2: DATA SERTIFIKASI & SKEMA -->
                <div class="subseksi-form">
                    <div class="subseksi-header">
                        Bagian 2: Data Sertifikasi & Daftar Unit Kompetensi
                    </div>

                    <div class="grup-form">
                        <label class="label-form" style="font-size: 1rem;">Pilihan Skema Sertifikasi</label>
                        <select name="skema_id" id="portal-select-skema" class="input-control" required style="font-size: 0.95rem; font-weight: 700; padding: 0.75rem;" onchange="updateDaftarUnitSkema(this.value)" {{ ($isDiajukan || $isAccAdmin) ? 'disabled' : '' }}>
                            <option value="">-- Pilih Skema Sertifikasi --</option>
                            @foreach($skemaList as $s)
                                @php
                                    $isDitolakSkema = in_array($s->id, $ditolakSkemaIds ?? []);
                                    $isSedangBerjalan = in_array($s->id, $runningSkemaIds ?? []) && ($pendaftaran ? $pendaftaran->skema_id != $s->id : true);
                                    $isDisabled = $isDitolakSkema || $isSedangBerjalan;
                                @endphp
                                <option value="{{ $s->id }}" {{ $isDisabled ? 'disabled' : '' }} {{ (old('skema_id', $draftData['skema_id']) == $s->id && !$isDitolakSkema) ? 'selected' : '' }}>
                                    [{{ $s->kode_skema }}] {{ $s->nama_skema }} ({{ $s->unitKompetensi->count() }} Unit Kompetensi)
                                    @if($isDitolakSkema)
                                        ❌ [Ditolak - Tidak Dapat Dipilih Kembali]
                                    @elseif($isSedangBerjalan)
                                        ⚠️ [Sedang Berjalan]
                                    @endif
                                </option>
                            @endforeach
                        </select>
                        @if($isDiajukan || $isAccAdmin)
                            <input type="hidden" name="skema_id" value="{{ $draftData['skema_id'] }}">
                        @endif
                    </div>

                    <div class="grup-form" style="margin-top: 1.25rem;">
                        <label class="label-form">Tujuan Asesmen</label>
                        @php $tujuanAktif = old('tujuan_asesmen', $draftData['tujuan_asesmen']); @endphp
                        <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(220px, 1fr)); gap: 0.75rem; background: #ffffff; padding: 1rem; border-radius: var(--radius-md); border: 1px solid #cbd5e1;">
                            <label style="display: flex; align-items: center; gap: 0.5rem; font-weight: 600; cursor: pointer; font-size: 0.9rem;">
                                <input type="radio" name="tujuan_asesmen" value="Sertifikasi" {{ $tujuanAktif === 'Sertifikasi' ? 'checked' : '' }} {{ ($isDiajukan || $isAccAdmin) ? 'disabled' : '' }}> Sertifikasi
                            </label>
                            <label style="display: flex; align-items: center; gap: 0.5rem; font-weight: 600; cursor: pointer; font-size: 0.9rem;">
                                <input type="radio" name="tujuan_asesmen" value="Pengakuan Kompetensi Terkini (PKT)" {{ $tujuanAktif === 'Pengakuan Kompetensi Terkini (PKT)' ? 'checked' : '' }} {{ ($isDiajukan || $isAccAdmin) ? 'disabled' : '' }}> Pengakuan Kompetensi Terkini (PKT)
                            </label>
                            <label style="display: flex; align-items: center; gap: 0.5rem; font-weight: 600; cursor: pointer; font-size: 0.9rem;">
                                <input type="radio" name="tujuan_asesmen" value="Rekognisi Pembelajaran Lampau (RPL)" {{ $tujuanAktif === 'Rekognisi Pembelajaran Lampau (RPL)' ? 'checked' : '' }} {{ ($isDiajukan || $isAccAdmin) ? 'disabled' : '' }}> Rekognisi Pembelajaran Lampau (RPL)
                            </label>
                        </div>
                    </div>

                    <!-- TABEL DAFTAR UNIT KOMPETENSI SKEMA -->
                    <div style="margin-top: 1.5rem;">
                        <h4 style="font-size: 0.95rem; color: var(--biru-malam); margin-bottom: 0.75rem;">
                            Daftar Unit Kompetensi sesuai Kemasan Skema:
                        </h4>

                        @foreach($skemaList as $s)
                            <div class="tabel-unit-skema-box" id="unit-box-{{ $s->id }}" style="display: {{ (old('skema_id', $draftData['skema_id']) == $s->id) ? 'block' : 'none' }}; border: 1px solid #e2e8f0; border-radius: var(--radius-md); overflow: hidden; background: #ffffff;">
                                <table class="tabel-custom" style="margin: 0; font-size: 0.88rem;">
                                    <thead>
                                        <tr style="background: #f8fafc;">
                                            <th style="width: 45px; text-align: center;">No.</th>
                                            <th style="width: 25%;">Kode Unit</th>
                                            <th>Judul Unit Kompetensi</th>
                                            <th style="width: 20%;">Standar</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @forelse($s->unitKompetensi as $idx => $u)
                                            <tr>
                                                <td style="text-align: center;">{{ $idx + 1 }}</td>
                                                <td><strong style="color: var(--biru-utama);">{{ $u->kode_unit }}</strong></td>
                                                <td>{{ $u->judul_unit }}</td>
                                                <td><span class="lencana lencana-biru" style="font-size: 0.75rem;">{{ $u->standar_kompetensi ?? 'SKKNI' }}</span></td>
                                            </tr>
                                        @empty
                                            <tr>
                                                <td colspan="4" style="text-align: center; color: var(--abu-teks); padding: 1.5rem;">Belum ada unit kompetensi terdaftar pada skema ini.</td>
                                            </tr>
                                        @endforelse
                                    </tbody>
                                </table>
                            </div>
                        @endforeach
                    </div>
                </div>

                <!-- SUBSEKSI 3: BUKTI PERSYARATAN & ADMINISTRATIF -->
                <div class="subseksi-form" id="subseksi-bukti-persyaratan">
                    <div class="subseksi-header">
                        Bagian 3: Bukti Persyaratan Dasar & Administratif
                    </div>

                    @php
                        $dokumenRapor = $pendaftaran ? $pendaftaran->dokumen->where('jenis_dokumen', 'Ijazah / Rapor Terakhir')->first() : null;
                        $dokumenPkl = $pendaftaran ? $pendaftaran->dokumen->where('jenis_dokumen', 'Portofolio Sertifikat/Karya')->first() : null;
                        $dokumenKtp = $pendaftaran ? $pendaftaran->dokumen->where('jenis_dokumen', 'KTP / Kartu Pelajar')->first() : null;
                        $dokumenFoto = $pendaftaran ? $pendaftaran->dokumen->where('jenis_dokumen', 'Pasfoto 3x4 Background Merah')->first() : null;
                    @endphp

                    <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(320px, 1fr)); gap: 1.25rem;">
                        <!-- 1. RAPOR -->
                        @php $isInvalidRapor = ($dokumenRapor && $dokumenRapor->status_verifikasi === 'tidak_valid'); @endphp
                        <div id="dokumen-rapor" style="background: #ffffff; padding: 1.15rem; border-radius: var(--radius-md); border: 1px solid #e2e8f0; transition: all 0.3s ease;">
                            <div style="display: flex; justify-content: space-between; align-items: flex-start; margin-bottom: 0.25rem;">
                                <strong style="color: var(--biru-malam); font-size: 0.95rem;">
                                    1. Foto Copy Rapor / Ijazah Terakhir
                                </strong>
                                @if($isInvalidRapor)
                                    <span style="font-size: 0.72rem; font-weight: 800; background: #dc2626; color: #ffffff; padding: 0.15rem 0.5rem; border-radius: 4px; text-transform: uppercase;">
                                        Perlu Revisi
                                    </span>
                                @endif
                            </div>
                            <p style="font-size: 0.8rem; color: var(--abu-teks); margin-bottom: 0.75rem;">Berisi nilai mata pelajaran/keahlian relevan (PDF/JPG/PNG max 5MB)</p>
                            
                            @if($isInvalidRapor)
                                <div style="background: #fef2f2; color: #991b1b; padding: 0.5rem 0.75rem; border-radius: 6px; border: 1px solid #fca5a5; margin-bottom: 0.75rem; font-size: 0.82rem;">
                                    <strong>Catatan Perbaikan Admin:</strong> {{ $dokumenRapor->catatan ?: 'Berkas ini ditandai Tidak Valid. Harap unggah file pengganti.' }}
                                </div>
                            @endif

                            @if($dokumenRapor)
                                <div style="margin-bottom: 0.5rem; display: flex; align-items: center; justify-content: space-between; flex-wrap: wrap; gap: 0.5rem; background: #f8fafc; padding: 0.5rem 0.75rem; border-radius: var(--radius-sm); border: 1px solid #e2e8f0;">
                                    @if($isInvalidRapor)
                                        <span class="lencana lencana-merah">&times; Berkas Lama: {{ $dokumenRapor->nama_dokumen }}</span>
                                    @else
                                        <span class="lencana lencana-hijau">&#10003; {{ $dokumenRapor->nama_dokumen }}</span>
                                    @endif
                                    @php
                                        $isImgRapor = Str::endsWith(strtolower($dokumenRapor->file_path), ['.jpg', '.jpeg', '.png', '.webp', '.gif', '.svg', '.jfif']);
                                        $urlRapor = Str::startsWith($dokumenRapor->file_path, ['http://', 'https://']) ? $dokumenRapor->file_path : asset($dokumenRapor->file_path);
                                    @endphp
                                    @if($isImgRapor)
                                        <button type="button" class="tombol tombol-sekunder tombol-sm preview-gambar-link" data-pratinjau-gambar="{{ $urlRapor }}" data-judul="Rapor / Ijazah" style="font-size: 0.75rem; padding: 0.2rem 0.5rem;">
                                             Pratinjau
                                        </button>
                                    @else
                                        <a href="{{ $urlRapor }}" target="_blank" class="tombol tombol-sekunder tombol-sm" style="font-size: 0.75rem; padding: 0.2rem 0.5rem;">
                                            Buka PDF
                                        </a>
                                    @endif
                                </div>
                            @endif
                            @if(!$isDiajukan && !$isAccAdmin)
                                <input type="file" name="file_rapor" id="input_file_rapor" class="input-control" accept=".pdf,.jpg,.jpeg,.png" {{ ($dokumenRapor && !$isInvalidRapor) ? '' : 'required' }} style="padding: 0.4rem; font-size: 0.85rem;">
                            @endif
                        </div>

                        <!-- 2. PKL -->
                        @php $isInvalidPkl = ($dokumenPkl && $dokumenPkl->status_verifikasi === 'tidak_valid'); @endphp
                        <div id="dokumen-pkl" style="background: #ffffff; padding: 1.15rem; border-radius: var(--radius-md); border: 1px solid #e2e8f0; transition: all 0.3s ease;">
                            <div style="display: flex; justify-content: space-between; align-items: flex-start; margin-bottom: 0.25rem;">
                                <strong style="color: var(--biru-malam); font-size: 0.95rem;">
                                    2. Sertifikat PKL / Pelatihan Keahlian
                                </strong>
                                @if($isInvalidPkl)
                                    <span style="font-size: 0.72rem; font-weight: 800; background: #dc2626; color: #ffffff; padding: 0.15rem 0.5rem; border-radius: 4px; text-transform: uppercase;">
                                        Perlu Revisi
                                    </span>
                                @endif
                            </div>
                            <p style="font-size: 0.8rem; color: var(--abu-teks); margin-bottom: 0.75rem;">Bukti pengalaman praktik kerja / sertifikat pelatihan (PDF/JPG/PNG max 5MB)</p>
                            
                            @if($isInvalidPkl)
                                <div style="background: #fef2f2; color: #991b1b; padding: 0.5rem 0.75rem; border-radius: 6px; border: 1px solid #fca5a5; margin-bottom: 0.75rem; font-size: 0.82rem;">
                                    <strong>Catatan Perbaikan Admin:</strong> {{ $dokumenPkl->catatan ?: 'Berkas ini ditandai Tidak Valid. Harap unggah file pengganti.' }}
                                </div>
                            @endif

                            @if($dokumenPkl)
                                <div style="margin-bottom: 0.5rem; display: flex; align-items: center; justify-content: space-between; flex-wrap: wrap; gap: 0.5rem; background: #f8fafc; padding: 0.5rem 0.75rem; border-radius: var(--radius-sm); border: 1px solid #e2e8f0;">
                                    @if($isInvalidPkl)
                                        <span class="lencana lencana-merah">&times; Berkas Lama: {{ $dokumenPkl->nama_dokumen }}</span>
                                    @else
                                        <span class="lencana lencana-hijau">&#10003; {{ $dokumenPkl->nama_dokumen }}</span>
                                    @endif
                                    @php
                                        $isImgPkl = Str::endsWith(strtolower($dokumenPkl->file_path), ['.jpg', '.jpeg', '.png', '.webp', '.gif', '.svg', '.jfif']);
                                        $urlPkl = Str::startsWith($dokumenPkl->file_path, ['http://', 'https://']) ? $dokumenPkl->file_path : asset($dokumenPkl->file_path);
                                    @endphp
                                    @if($isImgPkl)
                                        <button type="button" class="tombol tombol-sekunder tombol-sm preview-gambar-link" data-pratinjau-gambar="{{ $urlPkl }}" data-judul="Sertifikat PKL / Pelatihan" style="font-size: 0.75rem; padding: 0.2rem 0.5rem;">
                                             Pratinjau
                                        </button>
                                    @else
                                        <a href="{{ $urlPkl }}" target="_blank" class="tombol tombol-sekunder tombol-sm" style="font-size: 0.75rem; padding: 0.2rem 0.5rem;">
                                            Buka PDF
                                        </a>
                                    @endif
                                </div>
                            @endif
                            @if(!$isDiajukan && !$isAccAdmin)
                                <input type="file" name="file_pkl" id="input_file_pkl" class="input-control" accept=".pdf,.jpg,.jpeg,.png" {{ ($dokumenPkl && !$isInvalidPkl) ? '' : 'required' }} style="padding: 0.4rem; font-size: 0.85rem;">
                            @endif
                        </div>

                        <!-- 3. KTP -->
                        @php $isInvalidKtp = ($dokumenKtp && $dokumenKtp->status_verifikasi === 'tidak_valid'); @endphp
                        <div id="dokumen-ktp" style="background: #ffffff; padding: 1.15rem; border-radius: var(--radius-md); border: 1px solid #e2e8f0; transition: all 0.3s ease;">
                            <div style="display: flex; justify-content: space-between; align-items: flex-start; margin-bottom: 0.25rem;">
                                <strong style="color: var(--biru-malam); font-size: 0.95rem;">
                                    3. KTP / Kartu Pelajar / Kartu Keluarga
                                </strong>
                                @if($isInvalidKtp)
                                    <span style="font-size: 0.72rem; font-weight: 800; background: #dc2626; color: #ffffff; padding: 0.15rem 0.5rem; border-radius: 4px; text-transform: uppercase;">
                                        Perlu Revisi
                                    </span>
                                @endif
                            </div>
                            <p style="font-size: 0.8rem; color: var(--abu-teks); margin-bottom: 0.75rem;">Dokumen identitas resmi kependudukan (PDF/JPG/PNG max 5MB)</p>
                            
                            @if($isInvalidKtp)
                                <div style="background: #fef2f2; color: #991b1b; padding: 0.5rem 0.75rem; border-radius: 6px; border: 1px solid #fca5a5; margin-bottom: 0.75rem; font-size: 0.82rem;">
                                    <strong>Catatan Perbaikan Admin:</strong> {{ $dokumenKtp->catatan ?: 'Berkas ini ditandai Tidak Valid. Harap unggah file pengganti.' }}
                                </div>
                            @endif

                            @if($dokumenKtp)
                                <div style="margin-bottom: 0.5rem; display: flex; align-items: center; justify-content: space-between; flex-wrap: wrap; gap: 0.5rem; background: #f8fafc; padding: 0.5rem 0.75rem; border-radius: var(--radius-sm); border: 1px solid #e2e8f0;">
                                    @if($isInvalidKtp)
                                        <span class="lencana lencana-merah">&times; Berkas Lama: {{ $dokumenKtp->nama_dokumen }}</span>
                                    @else
                                        <span class="lencana lencana-hijau">&#10003; {{ $dokumenKtp->nama_dokumen }}</span>
                                    @endif
                                    @php
                                        $isImgKtp = Str::endsWith(strtolower($dokumenKtp->file_path), ['.jpg', '.jpeg', '.png', '.webp', '.gif', '.svg', '.jfif']);
                                        $urlKtp = Str::startsWith($dokumenKtp->file_path, ['http://', 'https://']) ? $dokumenKtp->file_path : asset($dokumenKtp->file_path);
                                    @endphp
                                    @if($isImgKtp)
                                        <button type="button" class="tombol tombol-sekunder tombol-sm preview-gambar-link" data-pratinjau-gambar="{{ $urlKtp }}" data-judul="KTP / Kartu Pelajar" style="font-size: 0.75rem; padding: 0.2rem 0.5rem;">
                                             Pratinjau
                                        </button>
                                    @else
                                        <a href="{{ $urlKtp }}" target="_blank" class="tombol tombol-sekunder tombol-sm" style="font-size: 0.75rem; padding: 0.2rem 0.5rem;">
                                            Buka PDF
                                        </a>
                                    @endif
                                </div>
                            @endif
                            @if(!$isDiajukan && !$isAccAdmin)
                                <input type="file" name="file_ktp" id="input_file_ktp" class="input-control" accept=".pdf,.jpg,.jpeg,.png" {{ ($dokumenKtp && !$isInvalidKtp) ? '' : 'required' }} style="padding: 0.4rem; font-size: 0.85rem;">
                            @endif
                        </div>

                        <!-- 4. PASFOTO -->
                        @php $isInvalidFoto = ($dokumenFoto && $dokumenFoto->status_verifikasi === 'tidak_valid'); @endphp
                        <div id="dokumen-foto" style="background: #ffffff; padding: 1.15rem; border-radius: var(--radius-md); border: 1px solid #e2e8f0; transition: all 0.3s ease;">
                            <div style="display: flex; justify-content: space-between; align-items: flex-start; margin-bottom: 0.25rem;">
                                <strong style="color: var(--biru-malam); font-size: 0.95rem;">
                                    4. Pasfoto 3x4 (Background Merah)
                                </strong>
                                @if($isInvalidFoto)
                                    <span style="font-size: 0.72rem; font-weight: 800; background: #dc2626; color: #ffffff; padding: 0.15rem 0.5rem; border-radius: 4px; text-transform: uppercase;">
                                        Perlu Revisi
                                    </span>
                                @endif
                            </div>
                            <p style="font-size: 0.8rem; color: var(--abu-teks); margin-bottom: 0.75rem;">Foto formal terbaru untuk cetak sertifikat BNSP (JPG/PNG max 5MB)</p>
                            
                            @if($isInvalidFoto)
                                <div style="background: #fef2f2; color: #991b1b; padding: 0.5rem 0.75rem; border-radius: 6px; border: 1px solid #fca5a5; margin-bottom: 0.75rem; font-size: 0.82rem;">
                                    <strong>Catatan Perbaikan Admin:</strong> {{ $dokumenFoto->catatan ?: 'Berkas ini ditandai Tidak Valid. Harap unggah file pengganti.' }}
                                </div>
                            @endif

                            @if($dokumenFoto)
                                <div style="margin-bottom: 0.5rem; display: flex; align-items: center; justify-content: space-between; flex-wrap: wrap; gap: 0.5rem; background: #f8fafc; padding: 0.5rem 0.75rem; border-radius: var(--radius-sm); border: 1px solid #e2e8f0;">
                                    @if($isInvalidFoto)
                                        <span class="lencana lencana-merah">&times; Berkas Lama: {{ $dokumenFoto->nama_dokumen }}</span>
                                    @else
                                        <span class="lencana lencana-hijau">&#10003; {{ $dokumenFoto->nama_dokumen }}</span>
                                    @endif
                                    @php
                                        $isImgFoto = Str::endsWith(strtolower($dokumenFoto->file_path), ['.jpg', '.jpeg', '.png', '.webp', '.gif', '.svg', '.jfif']);
                                        $urlFoto = Str::startsWith($dokumenFoto->file_path, ['http://', 'https://']) ? $dokumenFoto->file_path : asset($dokumenFoto->file_path);
                                    @endphp
                                    @if($isImgFoto)
                                        <button type="button" class="tombol tombol-sekunder tombol-sm preview-gambar-link" data-pratinjau-gambar="{{ $urlFoto }}" data-judul="Pasfoto 3x4" style="font-size: 0.75rem; padding: 0.2rem 0.5rem;">
                                             Pratinjau
                                        </button>
                                    @else
                                        <a href="{{ $urlFoto }}" target="_blank" class="tombol tombol-sekunder tombol-sm" style="font-size: 0.75rem; padding: 0.2rem 0.5rem;">
                                            Buka PDF
                                        </a>
                                    @endif
                                </div>
                            @endif
                            @if(!$isDiajukan && !$isAccAdmin)
                                <input type="file" name="file_foto" id="input_file_foto" class="input-control" accept=".jpg,.jpeg,.png" {{ ($dokumenFoto && !$isInvalidFoto) ? '' : 'required' }} style="padding: 0.4rem; font-size: 0.85rem;">
                            @endif
                        </div>
                    </div>
                </div>

                <!-- SUBSEKSI 4: TANDA TANGAN ASESI -->
                <div class="subseksi-form">
                    <div class="subseksi-header">
                        Pengesahan & Tanda Tangan Digital Pemohon
                    </div>

                    <div style="display: flex; justify-content: space-between; align-items: flex-start; flex-wrap: wrap; gap: 1.5rem;">
                        <div style="max-width: 500px;">
                            <p style="font-size: 0.88rem; color: var(--hitam-teks); line-height: 1.5; margin-bottom: 0.5rem;">
                                Dengan ini saya menyatakan bahwa data yang saya cantumkan dalam permohonan sertifikasi ini adalah benar dan dapat dipertanggungjawabkan.
                            </p>
                            <small style="color: var(--abu-teks);">
                                Tanggal Pengajuan: <strong>{{ date('d F Y') }}</strong>
                            </small>
                        </div>

                        <div style="text-align: center;">
                            <label class="label-form" style="margin-bottom: 0.35rem; display: block;">Tanda Tangan Digital Asesi:</label>
                            @if($pendaftaran && $pendaftaran->tanda_tangan_asesi)
                                <div style="background: #ffffff; padding: 0.5rem; border: 1px solid #cbd5e1; border-radius: var(--radius-sm); margin-bottom: 0.5rem; display: inline-block;">
                                    <img src="{{ Str::startsWith($pendaftaran->tanda_tangan_asesi, 'data:') ? $pendaftaran->tanda_tangan_asesi : asset($pendaftaran->tanda_tangan_asesi) }}" alt="TTD Asesi" style="max-height: 80px;">
                                </div>
                            @endif

                            @if(!$isDiajukan && !$isAccAdmin)
                                <div class="canvas-signature-wrap">
                                    <canvas id="canvas-ttd-apl01" width="800" height="240" class="canvas-signature" style="touch-action: none; -ms-touch-action: none;"></canvas>
                                    <input type="hidden" name="tanda_tangan_asesi" id="input-ttd-apl01" value="{{ $pendaftaran->tanda_tangan_asesi ?? '' }}">
                                    <div style="margin-top: 0.4rem;">
                                        <button type="button" class="tombol tombol-outline tombol-sm" onclick="resetCanvas('canvas-ttd-apl01', 'input-ttd-apl01')" style="font-size: 0.78rem; padding: 0.25rem 0.65rem;">
                                            Bersihkan Canvas
                                        </button>
                                    </div>
                                </div>
                            @endif
                        </div>
                    </div>
                </div>

                <!-- SUBSEKSI 5: BUKTI VERIFIKASI & PENGESAHAN ADMIN LSP (BAGIAN 3) -->
                @if($isAccAdmin)
                    <div class="subseksi-form" style="background: #f0fdf4; border: 1.5px solid #86efac;">
                        <div class="subseksi-header" style="color: #166534; border-bottom-color: #bbf7d0;">
                            Bukti Verifikasi & Rekomendasi Admin LSP (FR.APL.01 Bagian 3)
                        </div>

                        <div style="display: grid; grid-template-columns: 1.5fr 1fr; gap: 1.5rem; align-items: center; flex-wrap: wrap;">
                            <div>
                                <div style="display: flex; align-items: center; gap: 0.5rem; margin-bottom: 0.5rem;">
                                    <span class="lencana lencana-hijau" style="font-size: 0.88rem; font-weight: 800;">
                                        STATUS: DITERIMA SEBAGAI PESERTA (APPROVED)
                                    </span>
                                </div>
                                <p style="font-size: 0.9rem; color: #166534; line-height: 1.5; margin-bottom: 0.75rem;">
                                    Permohonan sertifikasi dan dokumen persyaratan Anda telah diverifikasi dan <strong>disetujui (Approved)</strong> oleh Verifikator Admin LSP SMKN 1 Gunungputri (Lisensi Resmi BNSP: BNSP-LSP-2629-ID).
                                </p>
                                
                                <div style="background: #ffffff; padding: 0.75rem 1rem; border-radius: var(--radius-sm); border: 1px solid #bbf7d0; font-size: 0.84rem; color: #334155;">
                                    <div><strong>Catatan Verifikator:</strong> <em>"{{ $pendaftaran->catatan_verifikasi ?? 'Berkas persyaratan lengkap, valid, dan memenuhi standar skema sertifikasi.' }}"</em></div>
                                    @if($pendaftaran->jadwal)
                                        <div style="margin-top: 0.35rem; color: var(--biru-malam);">
                                            <strong>Jadwal Uji TUK:</strong> {{ date('d M Y', strtotime($pendaftaran->jadwal->tanggal_uji)) }} ({{ $pendaftaran->jadwal->nama_tuk }}) | <strong>Asesor:</strong> {{ $pendaftaran->asesor->nama_lengkap ?? ($pendaftaran->jadwal->asesor->nama_lengkap ?? 'Asesor Resmi') }}
                                        </div>
                                    @endif
                                </div>
                            </div>

                            <div style="text-align: center; background: #ffffff; padding: 1rem; border-radius: var(--radius-md); border: 1px solid #bbf7d0;">
                                <div style="font-size: 0.8rem; font-weight: 700; color: #166534; margin-bottom: 0.4rem;">
                                    Pengesahan & Tanda Tangan Admin LSP:
                                </div>
                                @if($pendaftaran->tanda_tangan_admin)
                                    <img src="{{ Str::startsWith($pendaftaran->tanda_tangan_admin, 'data:') ? $pendaftaran->tanda_tangan_admin : asset($pendaftaran->tanda_tangan_admin) }}" alt="TTD Admin LSP" style="max-height: 75px; border: 1px dashed #86efac; padding: 0.25rem; background: #fff; border-radius: var(--radius-sm);">
                                @else
                                    <div style="display: inline-block; padding: 0.5rem 1rem; background: #dcfce7; color: #166534; border-radius: var(--radius-sm); font-weight: 700; font-size: 0.82rem;">
                                        Terverifikasi Resmi Admin
                                    </div>
                                @endif
                                <div style="font-size: 0.78rem; color: #64748b; margin-top: 0.35rem;">
                                    Tanggal Verifikasi: <strong>{{ $pendaftaran->tanggal_ttd_admin ? date('d F Y', strtotime($pendaftaran->tanggal_ttd_admin)) : date('d F Y') }}</strong>
                                </div>
                            </div>
                        </div>

                        <!-- TOMBOL LANJUT KE APL-02 -->
                        <div style="margin-top: 1.25rem; padding-top: 1rem; border-top: 1px solid #bbf7d0; display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 0.75rem;">
                            <span style="font-size: 0.85rem; color: #15803d; font-weight: 600;">
                                Akses Formulir FR.APL.02 Asesmen Mandiri kini telah terbuka!
                            </span>
                            <button type="button" class="tombol tombol-sukses" onclick="gantiTabPortal('apl02')" style="padding: 0.6rem 1.5rem; font-weight: 700;">
                                Buka & Lanjut Isi FR.APL.02 &rarr;
                            </button>
                        </div>
                    </div>
                @elseif($isDiajukan)
                    <div class="subseksi-form" style="background: #fffbeb; border: 1.5px solid #fde68a;">
                        <div style="display: flex; align-items: center; gap: 1rem;">
                            <div>
                                <div style="display: flex; align-items: center; gap: 0.5rem; margin-bottom: 0.25rem;">
                                    <span class="lencana lencana-amber" style="font-weight: 800;">
                                        STATUS: MENUNGGU VERIFIKASI ADMIN
                                    </span>
                                </div>
                                <p style="font-size: 0.88rem; color: #92400e; margin: 0; line-height: 1.5;">
                                    Formulir FR.APL.01 Anda telah berhasil diajukan dan sedang dalam proses pemeriksaan oleh Verifikator Admin LSP. <strong>Formulir FR.APL.02 akan otomatis terbuka</strong> setelah berkas permohonan disetujui (Approved).
                                </p>
                            </div>
                        </div>
                    </div>
                @endif

                <!-- AKSI FORM APL-01 (HANYA UNTUK ASESI YANG BELUM DIAJUKAN/ACC) -->
                @if(auth()->check() && auth()->user()->peran === 'asesi' && !$isDiajukan && !$isAccAdmin)
                    <div style="display: flex; justify-content: flex-end; gap: 1rem; margin-top: 2rem; border-top: 1px solid #e2e8f0; padding-top: 1.5rem;">
                        <button type="submit" name="aksi" value="draft" class="tombol tombol-sekunder">
                            Simpan Draft
                        </button>
                        <button type="submit" name="aksi" value="ajukan" class="tombol tombol-utama" onclick="return validasiSebelumSubmitApl01()">
                            Ajukan Formulir FR.APL.01 &rarr;
                        </button>
                    </div>
                @endif
            </form>
        </div>
    </div>

    <!-- ======================================================================= -->
    <!-- TAB 2 CONTENT: FR.APL.02 ASESMEN MANDIRI                              -->
    <!-- ======================================================================= -->
    <div id="konten-tab-apl02" style="display: {{ $tabAktif === 'apl02' ? 'block' : 'none' }};">
        <div class="portal-card">
            <div class="portal-card-header">
                <div>
                    <h2 class="portal-card-title">
                        FR.APL.02. ASESMEN MANDIRI
                    </h2>
                    <p style="font-size: 0.88rem; color: var(--abu-teks); margin: 0.35rem 0 0 0;">
                        Lakukan asesmen mandiri terhadap seluruh kriteria unjuk kerja pada setiap unit kompetensi dan lampirkan bukti relevan.
                    </p>
                </div>

                <div>
                    <span class="lencana lencana-biru">
                        {{ $pendaftaran->skema->kode_skema ?? 'SKEMA' }}
                    </span>
                </div>
            </div>

            <!-- JIKA BELUM DI-ACC ADMIN -->
            @if(!$isAccAdmin && !$isDitolakAdmin)
                <div class="alert-lock-card">
                    <h3 style="font-size: 1.25rem; font-weight: 800; color: var(--biru-malam); margin-bottom: 0.5rem;">
                        Formulir FR.APL.02 Belum Dapat Diisi
                    </h3>
                    <p style="max-width: 600px; margin: 0 auto 1.25rem auto; font-size: 0.92rem; line-height: 1.5;">
                        Formulir FR.APL.01 Anda belum disetujui (ACC) oleh Admin LSP. Asesor Penguji akan ditugaskan setelah verifikasi berkas APL-01 selesai, dan formulir asesmen mandiri ini akan otomatis terbuka.
                    </p>
                    <button type="button" class="tombol tombol-utama tombol-sm" onclick="gantiTabPortal('apl01')">
                         Periksa Status Tab APL-01
                    </button>
                </div>
            @elseif($isDitolakAdmin || $isDitolakAsesor)
                <div class="alert-lock-card" style="border-color: #fca5a5; background: #fff5f5;">
                    <h3 style="font-size: 1.25rem; font-weight: 800; color: #991b1b; margin-bottom: 0.5rem;">
                        Formulir Dinonaktifkan (Pendaftaran Ditolak)
                    </h3>
                    <p style="max-width: 600px; margin: 0 auto 1.25rem auto; font-size: 0.92rem; color: #7f1d1d;">
                        Pendaftaran pada skema ini tidak dapat dilanjutkan. Silakan pilih dan mendaftar skema sertifikasi lainnya.
                    </p>
                    <button type="button" class="tombol tombol-bahaya tombol-sm" onclick="bukaModalSkemaBaru()">
                        Mendaftar Skema Lainnya
                    </button>
                </div>
            @else
                <!-- FORMULIR APL-02 AKTIF -->
                @if($isApl02Selesai)
                    @if(!$isAccAsesor)
                        <div style="background: #fffbebf5; color: #92400e; padding: 1.25rem 1.5rem; border-radius: var(--radius-md); border: 1.5px solid #fcd34d; margin-bottom: 1.75rem; display: flex; align-items: center; justify-content: space-between; flex-wrap: wrap; gap: 1rem;">
                            <div style="display: flex; align-items: center; gap: 1rem;">
                                <div>
                                    <strong style="display: block; font-size: 1rem; color: #92400e;">Formulir FR.APL.02 Telah Terkirim — Menunggu Persetujuan (ACC) Asesor Penguji</strong>
                                    <span style="font-size: 0.88rem; color: #78350f;">
                                        Asesor Penguji (<strong>{{ $pendaftaran->asesor->nama_lengkap ?? 'Asesor Resmi' }}</strong>) sedang memeriksa asesmen mandiri dan bukti portofolio Anda. Formulir <strong>FR.AK.01</strong> akan otomatis terbuka begitu Asesor menyetujui (ACC).
                                    </span>
                                </div>
                            </div>
                        </div>
                    @else
                        <div style="background: #f0fdf4; color: #166534; padding: 1.25rem 1.5rem; border-radius: var(--radius-md); border: 1.5px solid #bbf7d0; margin-bottom: 1.75rem; display: flex; align-items: center; justify-content: space-between; flex-wrap: wrap; gap: 1rem;">
                            <div style="display: flex; align-items: center; gap: 1rem;">
                                <div>
                                    <strong style="display: block; font-size: 1rem; color: #166534;">Formulir FR.APL.02 Telah Disetujui (ACC) oleh Asesor Penguji</strong>
                                    <span style="font-size: 0.88rem; color: #15803d;">
                                        Asesor Penguji (<strong>{{ $pendaftaran->asesor->nama_lengkap ?? 'Asesor' }}</strong>) telah menyetujui rekomendasi asesmen mandiri Anda. Anda sekarang dapat mengisi dan menandatangani Formulir FR.AK.01.
                                    </span>
                                </div>
                            </div>
                            @if(!$isAk01Selesai)
                                <button type="button" class="tombol tombol-sukses tombol-sm" onclick="gantiTabPortal('ak01')">
                                    Lanjut Isi & TTD FR.AK.01 &rarr;
                                </button>
                            @endif
                        </div>
                    @endif
                @else
                    <div style="background: #eff6ff; color: #1e40af; padding: 1rem 1.25rem; border-radius: var(--radius-md); border: 1px solid #bfdbfe; margin-bottom: 1.75rem; display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 1rem;">
                        <div>
                            <strong style="color: #1e3a8a;">Petunjuk Asesmen Mandiri:</strong>
                            <span style="font-size: 0.88rem; display: block; margin-top: 0.2rem;">Pilih <strong>Kompeten (K)</strong> jika Anda yakin mampu memenuhi kriteria, atau <strong>Belum Kompeten (BK)</strong>. Unggah foto/dokumen bukti relevan jika diperlukan.</span>
                        </div>
                        <button type="button" class="tombol tombol-outline tombol-sm" onclick="pilihSemuaKompeten()" style="font-size: 0.82rem; padding: 0.35rem 0.85rem;">
                            Pilih Semua Kompeten (K)
                        </button>
                    </div>
                @endif

                <form action="{{ route('asesi.apl02.simpan') }}" method="POST" enctype="multipart/form-data" id="form-apl02-terpadu">
                    @csrf
                    <input type="hidden" name="pendaftaran_id" value="{{ $pendaftaran->id ?? '' }}">

                    @if($pendaftaran && $pendaftaran->skema)
                        @foreach($pendaftaran->skema->unitKompetensi as $idxUnit => $unit)
                            <div class="tabel-unit-wrap" style="border: 1px solid #e2e8f0; border-radius: 12px; overflow: hidden; margin-bottom: 1.5rem; background: #ffffff;">
                                <div class="tabel-unit-header" style="background: #f8fafc; padding: 0.9rem 1.25rem; border-bottom: 1px solid #e2e8f0; display: flex; justify-content: space-between; align-items: center; gap: 1rem; flex-wrap: wrap;">
                                    <div>
                                        <span style="font-size: 0.75rem; font-weight: 700; font-family: monospace; background: #eff6ff; color: #1d4ed8; padding: 0.15rem 0.5rem; border-radius: 4px; border: 1px solid #bfdbfe; text-transform: uppercase;">
                                            Unit {{ $idxUnit + 1 }} &bull; {{ $unit->kode_unit }}
                                        </span>
                                        <h4 style="font-size: 0.95rem; font-weight: 700; color: #1e293b; margin: 0.35rem 0 0 0;">{{ $unit->judul_unit }}</h4>
                                    </div>
                                    <div style="display: flex; align-items: center; gap: 0.5rem;">
                                        <button type="button" onclick="pilihSemuaKompetenUnit('{{ $unit->id }}')" class="tombol tombol-outline tombol-sm" style="font-size: 0.78rem; padding: 0.3rem 0.75rem; border-color: #a7f3d0; color: #047857; background: #ecfdf5;">
                                            &#10003; Pilih Semua K pada Unit Ini
                                        </button>
                                        <span class="lencana" style="background: #e2e8f0; color: #334155; font-weight: 700; font-size: 0.78rem;">
                                            {{ $unit->elemenKompetensi->count() }} Elemen
                                        </span>
                                    </div>
                                </div>

                                <div style="padding: 1rem; display: flex; flex-direction: column; gap: 1rem;">
                                    @foreach($unit->elemenKompetensi as $idxElem => $elem)
                                        @php
                                            $jawaban = $jawabanMap[$elem->id] ?? null;
                                            $nilaiDipilih = $jawaban ? $jawaban->nilai_kompetensi : (old("penilaian.{$elem->id}") ?? 'K');
                                            $listBukti = $buktiApl02Map->get($elem->id, collect());
                                        @endphp
                                        <div style="border: 1px solid #e2e8f0; border-radius: 8px; overflow: hidden; background: #ffffff; margin-bottom: 0.75rem;">
                                            <!-- Baris Header Elemen Pemisah -->
                                            <div style="background: #f1f5f9; padding: 0.6rem 1rem; border-bottom: 1px solid #e2e8f0; display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 0.5rem;">
                                                <div style="display: flex; align-items: center; gap: 0.5rem;">
                                                    <span style="font-size: 0.72rem; font-weight: 700; font-family: monospace; background: #e2e8f0; color: #334155; padding: 0.1rem 0.4rem; border-radius: 4px;">
                                                        Elemen {{ $elem->nomor_elemen ?? ($idxElem + 1) }}
                                                    </span>
                                                    <strong style="color: #0f172a; font-size: 0.88rem;">{{ $elem->nama_elemen }}</strong>
                                                </div>
                                            </div>

                                            <!-- Rincian KUK -->
                                            @if($elem->kriteriaUnjukKerja && $elem->kriteriaUnjukKerja->count() > 0)
                                                <div style="display: flex; flex-direction: column; divide-y: 1px solid #f1f5f9;">
                                                    @foreach($elem->kriteriaUnjukKerja as $idxKuk => $kuk)
                                                        <div style="padding: 0.6rem 1rem; border-bottom: 1px solid #f1f5f9; display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 0.5rem;">
                                                            <div style="display: flex; align-items: flex-start; gap: 0.5rem; flex: 1; min-width: 200px;">
                                                                <span style="font-family: monospace; font-size: 0.78rem; font-weight: 700; color: #2563eb; background: #eff6ff; padding: 0.1rem 0.35rem; border-radius: 4px; border: 1px solid #dbeafe; shrink: 0;">
                                                                    {{ $kuk->nomor_kuk ?: ($elem->nomor_elemen . '.' . ($idxKuk + 1)) }}
                                                                </span>
                                                                <span style="font-size: 0.82rem; color: #334155; line-height: 1.45;">
                                                                    {{ $kuk->pernyataan_kuk }}
                                                                </span>
                                                            </div>
                                                            <!-- Segmented Control for K / BK (Independen Per Butir KUK) -->
                                                            <div style="display: flex; gap: 0.3rem; margin-left: auto;" class="segmented-k-bk-group">
                                                                <label style="cursor: pointer; display: inline-flex; align-items: center;">
                                                                    <input type="radio" 
                                                                           id="penilaian_kuk_{{ $kuk->id }}_k"
                                                                           name="penilaian_kuk[{{ $kuk->id }}]" 
                                                                           value="K" 
                                                                           data-unit="{{ $unit->id }}"
                                                                           data-kuk="{{ $kuk->id }}"
                                                                           class="peer radio-k radio-penilaian-kuk-k" 
                                                                           checked 
                                                                           style="display: none;">
                                                                    <span class="btn-k-state bg-emerald-600 text-white font-semibold shadow-xs border border-emerald-600" 
                                                                          style="padding: 0.25rem 0.75rem; border-radius: 6px; font-size: 0.78rem; font-weight: 700; cursor: pointer; transition: all 0.15s ease;">K</span>
                                                                </label>
                                                                <label style="cursor: pointer; display: inline-flex; align-items: center;">
                                                                    <input type="radio" 
                                                                           id="penilaian_kuk_{{ $kuk->id }}_bk"
                                                                           name="penilaian_kuk[{{ $kuk->id }}]" 
                                                                           value="BK" 
                                                                           data-unit="{{ $unit->id }}"
                                                                           data-kuk="{{ $kuk->id }}"
                                                                           class="peer radio-bk radio-penilaian-kuk-bk" 
                                                                           style="display: none;">
                                                                    <span class="btn-bk-state bg-slate-100 text-slate-600 hover:bg-slate-200 border border-slate-200" 
                                                                          style="padding: 0.25rem 0.75rem; border-radius: 6px; font-size: 0.78rem; font-weight: 700; cursor: pointer; transition: all 0.15s ease;">BK</span>
                                                                </label>
                                                            </div>
                                                        </div>
                                                    @endforeach
                                                </div>
                                                <input type="hidden" name="penilaian[{{ $elem->id }}]" id="input_elemen_{{ $elem->id }}" value="{{ $nilaiDipilih }}">
                                            @else
                                                <div style="padding: 0.75rem 1rem; display: flex; justify-content: space-between; align-items: center; gap: 0.5rem;">
                                                    <span style="font-size: 0.82rem; color: #475569;">Beri penilaian mandiri untuk elemen ini</span>
                                                    <div style="display: flex; gap: 0.3rem;" class="segmented-k-bk-group">
                                                        <label style="cursor: pointer; display: inline-flex; align-items: center;">
                                                            <input type="radio" 
                                                                   name="penilaian[{{ $elem->id }}]" 
                                                                   value="K" 
                                                                   data-unit="{{ $unit->id }}"
                                                                   data-elemen="{{ $elem->id }}"
                                                                   class="peer radio-k radio-penilaian-k" 
                                                                   {{ $nilaiDipilih === 'K' ? 'checked' : '' }} 
                                                                   required style="display: none;">
                                                                <span class="btn-k-state {{ $nilaiDipilih === 'K' ? 'bg-emerald-600 text-white font-semibold shadow-xs border border-emerald-600' : 'bg-slate-100 text-slate-600 hover:bg-slate-200 border border-slate-200' }}" 
                                                                      style="padding: 0.25rem 0.75rem; border-radius: 6px; font-size: 0.78rem; font-weight: 700; cursor: pointer; transition: all 0.15s ease;">K</span>
                                                        </label>
                                                        <label style="cursor: pointer; display: inline-flex; align-items: center;">
                                                            <input type="radio" 
                                                                   name="penilaian[{{ $elem->id }}]" 
                                                                   value="BK" 
                                                                   data-unit="{{ $unit->id }}"
                                                                   data-elemen="{{ $elem->id }}"
                                                                   class="peer radio-bk radio-penilaian-bk" 
                                                                   {{ $nilaiDipilih === 'BK' ? 'checked' : '' }} 
                                                                   required style="display: none;">
                                                                <span class="btn-bk-state {{ $nilaiDipilih === 'BK' ? 'bg-rose-600 text-white font-semibold shadow-xs border border-rose-600' : 'bg-slate-100 text-slate-600 hover:bg-slate-200 border border-slate-200' }}" 
                                                                      style="padding: 0.25rem 0.75rem; border-radius: 6px; font-size: 0.78rem; font-weight: 700; cursor: pointer; transition: all 0.15s ease;">BK</span>
                                                        </label>
                                                    </div>
                                                </div>
                                            @endif

                                            <!-- =========================================================
                                                 KOMPONEN BUKTI PENDUKUNG (MULTI-FILE UPLOAD & APL.01)
                                                 ========================================================= -->
                                            <div style="background: #f8fafc; padding: 0.85rem 1rem; border-top: 1px solid #e2e8f0;">
                                                <div style="display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 0.5rem; margin-bottom: 0.5rem;">
                                                    <div>
                                                        <div style="font-weight: 700; font-size: 0.8rem; color: #1e293b; display: flex; align-items: center; gap: 0.4rem;">
                                                            <span>Bukti Pendukung</span>
                                                        </div>
                                                        <p style="font-size: 0.72rem; color: #64748b; margin: 0.15rem 0 0 0;">
                                                            Upload dokumen atau file yang dapat mendukung pernyataan kompetensi Anda. (PDF, JPG, JPEG, PNG &bull; Maks. 10 MB)
                                                        </p>
                                                    </div>

                                                    <div style="display: flex; align-items: center; gap: 0.4rem;">
                                                        <!-- Hidden File Input per Elemen -->
                                                        <input type="file" 
                                                               id="file-input-elem-{{ $elem->id }}" 
                                                               accept=".pdf,.jpg,.jpeg,.png"
                                                               style="display: none;"
                                                               onchange="handleUploadBukti(this, '{{ $elem->id }}', '{{ $pendaftaran ? $pendaftaran->id : '' }}')">
                                                        
                                                        <!-- Tombol Upload Bukti Baru -->
                                                        <button type="button" 
                                                                id="btn-upload-elem-{{ $elem->id }}"
                                                                onclick="triggerUploadBukti('{{ $elem->id }}')"
                                                                style="padding: 0.3rem 0.65rem; border-radius: 6px; background: #2563eb; color: #ffffff; font-weight: 600; font-size: 0.75rem; border: none; cursor: pointer; display: inline-flex; align-items: center; gap: 0.35rem;">
                                                            <span>Upload Bukti</span>
                                                        </button>

                                                        <!-- Tombol Pilih Bukti dari APL.01 -->
                                                        <button type="button" 
                                                                id="btn-apl01-elem-{{ $elem->id }}"
                                                                onclick="bukaModalPilihApl01('{{ $elem->id }}')"
                                                                style="padding: 0.3rem 0.65rem; border-radius: 6px; background: #ffffff; color: #334155; font-weight: 600; font-size: 0.75rem; border: 1px solid #cbd5e1; cursor: pointer; display: inline-flex; align-items: center; gap: 0.35rem;">
                                                            <span>Gunakan Bukti dari APL.01</span>
                                                        </button>
                                                    </div>
                                                </div>

                                                <!-- Upload Loading Indicator -->
                                                <div id="upload-loading-elem-{{ $elem->id }}" style="display: none; padding: 0.3rem 0;">
                                                    <span style="font-size: 0.75rem; color: #2563eb; font-weight: 600;">
                                                        Mengunggah berkas bukti...
                                                    </span>
                                                </div>

                                                <!-- Daftar File Bukti Terunggah -->
                                                <div id="bukti-list-elem-{{ $elem->id }}" style="display: flex; flex-direction: column; gap: 0.4rem;">
                                                    @foreach($listBukti as $b)
                                                        <div id="bukti-item-{{ $b->id }}" style="display: flex; justify-content: space-between; align-items: center; padding: 0.4rem 0.65rem; border-radius: 6px; border: 1px solid #e2e8f0; background: #ffffff; font-size: 0.78rem; gap: 0.5rem;">
                                                            <div style="display: flex; align-items: center; gap: 0.5rem; min-width: 0; flex: 1;">
                                                                @if($b->is_pdf)
                                                                    <div style="width: 26px; height: 26px; border-radius: 4px; background: #fee2e2; color: #dc2626; display: flex; align-items: center; justify-content: center; font-weight: 700; font-size: 0.75rem; shrink: 0;">
                                                                        </div>
                                                                @else
                                                                    <div style="width: 26px; height: 26px; border-radius: 4px; background: #dbeafe; color: #2563eb; display: flex; align-items: center; justify-content: center; font-weight: 700; font-size: 0.75rem; shrink: 0;">
                                                                        </div>
                                                                @endif
                                                                <div style="min-width: 0; flex: 1;">
                                                                    <div style="font-weight: 600; color: #1e293b; overflow: hidden; text-overflow: ellipsis; white-space: nowrap;" title="{{ $b->nama_tampil }}">{{ $b->nama_tampil }}</div>
                                                                    <div style="font-size: 0.7rem; color: #94a3b8; display: flex; align-items: center; gap: 0.4rem; margin-top: 0.1rem;">
                                                                        <span>{{ $b->file_size_formatted }}</span>
                                                                        @if($b->sumber === 'apl01')
                                                                            <span style="background: #eff6ff; color: #1d4ed8; padding: 0.05rem 0.35rem; border-radius: 4px; border: 1px solid #bfdbfe; font-size: 0.65rem; font-weight: 600;">APL.01</span>
                                                                        @endif
                                                                    </div>
                                                                </div>
                                                            </div>
                                                            <div style="display: flex; align-items: center; gap: 0.35rem; shrink: 0;">
                                                                <button type="button" 
                                                                        onclick="bukaPratinjauBukti('{{ $b->url }}', '{{ addslashes($b->nama_tampil) }}', {{ $b->is_pdf ? 'true' : 'false' }})"
                                                                        style="padding: 0.25rem 0.5rem; border-radius: 4px; background: #ffffff; border: 1px solid #cbd5e1; color: #334155; font-weight: 600; font-size: 0.72rem; cursor: pointer; display: inline-flex; align-items: center; gap: 0.25rem;">
                                                                    Lihat
                                                                </button>
                                                                <button type="button" 
                                                                        onclick="hapusBukti('{{ $b->id }}', '{{ $elem->id }}')"
                                                                        style="padding: 0.25rem 0.45rem; border-radius: 4px; background: #ffffff; border: 1px solid #fecdd3; color: #e11d48; font-weight: 600; font-size: 0.72rem; cursor: pointer;"
                                                                        title="Hapus Bukti">
                                                                    Hapus
                                                                </button>
                                                            </div>
                                                        </div>
                                                    @endforeach
                                                </div>

                                                <!-- Status Kosong -->
                                                <div id="bukti-empty-elem-{{ $elem->id }}" style="font-size: 0.75rem; color: #94a3b8; font-style: italic; padding: 0.25rem 0; {{ $listBukti->count() > 0 ? 'display: none;' : '' }}">
                                                    Belum ada bukti yang dilampirkan.
                                                </div>
                                            </div>
                                        </div>
                                    @endforeach
                                </div>
                            </div>
                        @endforeach

                        @if(!$isApl02Selesai)
                            <div class="subseksi-form" style="margin-top: 2rem;">
                                <div class="subseksi-header">
                                    Pengesahan Tanda Tangan Asesi pada FR.APL.02
                                </div>
                                <div style="display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 1.5rem;">
                                    <div>
                                        <p style="font-size: 0.88rem; color: var(--hitam-teks); margin: 0;">
                                            Saya menyatakan bahwa seluruh penilaian mandiri di atas adalah benar dan sesuai kompetensi yang saya miliki.
                                        </p>
                                    </div>
                                    <div style="text-align: center;">
                                        <div class="canvas-signature-wrap">
                                            <canvas id="canvas-ttd-apl02" width="800" height="240" class="canvas-signature" style="touch-action: none; -ms-touch-action: none;"></canvas>
                                            <input type="hidden" name="tanda_tangan_asesi" id="input-ttd-apl02" value="{{ $pendaftaran->tanda_tangan_asesi ?? '' }}">
                                            <div style="margin-top: 0.4rem;">
                                                <button type="button" class="tombol tombol-outline tombol-sm" onclick="resetCanvas('canvas-ttd-apl02', 'input-ttd-apl02')" style="font-size: 0.78rem; padding: 0.25rem 0.65rem;">
                                                    Bersihkan Canvas
                                                </button>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <div style="display: flex; justify-content: flex-end; margin-top: 2rem; border-top: 1px solid #e2e8f0; padding-top: 1.5rem;">
                                <button type="submit" class="tombol tombol-utama" onclick="return confirm('Apakah Anda yakin ingin mengirim Formulir Asesmen Mandiri FR.APL.02? Data yang telah dikirim akan dikunci.')">
                                    Simpan FR.APL.02 & Lanjut ke FR.AK.01 &rarr;
                                </button>
                            </div>
                        @endif
                    @endif
                </form>
            @endif
        </div>
    </div>

    <!-- ======================================================================= -->
    <!-- TAB 3 CONTENT: FR.AK.01 PERSETUJUAN ASESMEN & KERAHASIAAN              -->
    <!-- ======================================================================= -->
    <div id="konten-tab-ak01" style="display: {{ $tabAktif === 'ak01' ? 'block' : 'none' }};">
        <div class="portal-card">
            <div class="portal-card-header">
                <div>
                    <h2 class="portal-card-title">
                        FR.AK.01. PERSETUJUAN ASESMEN DAN KERAHASIAAN
                    </h2>
                    <p style="font-size: 0.88rem; color: var(--abu-teks); margin: 0.35rem 0 0 0;">
                        Persetujuan pelaksanaan asesmen, penentuan TUK, bukti yang akan dikumpulkan, dan komitmen kerahasiaan.
                    </p>
                </div>

                <div>
                    @if($isAk01Selesai)
                        <span class="lencana lencana-hijau">&#10003; Siap Uji Kompetensi</span>
                    @endif
                </div>
            </div>

            <!-- JIKA APL-02 BELUM SELESAI ATAU BELUM DI-ACC ASESOR -->
            @if((!$pendaftaran || !$pendaftaran->isAk01Unlocked()) && !$isDitolakAdmin && !$isDitolakAsesor)
                @if(!$isApl02Selesai)
                    <div class="alert-lock-card">
                        <h3 style="font-size: 1.25rem; font-weight: 800; color: var(--biru-malam); margin-bottom: 0.5rem;">
                            Formulir FR.AK.01 Belum Dapat Diisi
                        </h3>
                        <p style="max-width: 600px; margin: 0 auto 1.25rem auto; font-size: 0.92rem; line-height: 1.5;">
                            Anda harus menyelesaikan dan mengirim <strong>Formulir FR.APL.02 Asesmen Mandiri</strong> terlebih dahulu sebelum mengisi persetujuan asesmen FR.AK.01.
                        </p>
                        <button type="button" class="tombol tombol-utama tombol-sm" onclick="gantiTabPortal('apl02')">
                            Buka Tab FR.APL.02
                        </button>
                    </div>
                @else
                    <div class="alert-lock-card" style="border-color: #fcd34d; background: #fffdf5;">
                        <h3 style="font-size: 1.25rem; font-weight: 800; color: #92400e; margin-bottom: 0.5rem;">
                            Formulir FR.AK.01 Menunggu Persetujuan (ACC) Asesor Penguji
                        </h3>
                        <p style="max-width: 620px; margin: 0 auto 1.25rem auto; font-size: 0.92rem; line-height: 1.5; color: #78350f;">
                            Formulir FR.APL.02 Asesmen Mandiri Anda telah berhasil dikirim dan saat ini <strong>sedang dalam proses peninjauan oleh Asesor Penguji ({{ $pendaftaran->asesor->nama_lengkap ?? 'Asesor Resmi' }})</strong>. Formulir FR.AK.01 akan terbuka otomatis setelah Asesor menyetujui (ACC) hasil asesmen mandiri Anda.
                        </p>
                        <button type="button" class="tombol tombol-sekunder tombol-sm" onclick="gantiTabPortal('apl02')">
                             Periksa Status FR.APL.02
                        </button>
                    </div>
                @endif
            @elseif($isDitolakAdmin || $isDitolakAsesor)
                <div class="alert-lock-card" style="border-color: #fca5a5; background: #fff5f5;">
                    <h3 style="font-size: 1.25rem; font-weight: 800; color: #991b1b; margin-bottom: 0.5rem;">
                        Formulir Dinonaktifkan (Pendaftaran Ditolak)
                    </h3>
                    <p style="max-width: 600px; margin: 0 auto 1.25rem auto; font-size: 0.92rem; color: #7f1d1d;">
                        Pendaftaran pada skema ini telah ditolak. Silakan memilih skema sertifikasi lainnya.
                    </p>
                    <button type="button" class="tombol tombol-bahaya tombol-sm" onclick="bukaModalSkemaBaru()">
                        Mendaftar Skema Lainnya
                    </button>
                </div>
            @else
                <!-- FORMULIR AK-01 AKTIF -->
                @if($isAk01Selesai)
                    <div style="background: #f0fdf4; color: #166534; padding: 1.25rem 1.5rem; border-radius: var(--radius-md); border: 1.5px solid #bbf7d0; margin-bottom: 1.75rem; display: flex; align-items: center; justify-content: space-between; flex-wrap: wrap; gap: 1rem;">
                        <div style="display: flex; align-items: center; gap: 1rem;">
                            <div>
                                <strong style="display: block; font-size: 1.05rem; color: #166534;">🎉 Seluruh Formulir Asesmen Lengkap & Disetujui!</strong>
                                <span style="font-size: 0.88rem; color: #15803d;">
                                    Anda telah menandatangani FR.AK.01 dan berada pada status: <strong>Siap Melaksanakan Uji Kompetensi</strong>.
                                </span>
                            </div>
                        </div>
                        <a href="{{ route('asesi.jadwal') }}" class="tombol tombol-sukses tombol-sm">
                            Cek Jadwal Uji
                        </a>
                    </div>
                @endif

                <form action="{{ route('asesi.ak01.simpan', $pendaftaran->id ?? 0) }}" method="POST" id="form-ak01-terpadu">
                    @csrf

                    <!-- TABEL INFORMASI SKEMA & PESERTA -->
                    <div class="subseksi-form" style="background: #ffffff; border: 1.5px solid var(--biru-malam);">
                        <table class="tabel-custom" style="margin: 0; font-size: 0.9rem;">
                            <tbody>
                                <tr>
                                    <td style="width: 25%; font-weight: 700; background: #f8fafc;">Skema Sertifikasi</td>
                                    <td><strong>{{ $pendaftaran->skema->nama_skema ?? '-' }}</strong> ({{ $pendaftaran->skema->kode_skema ?? '-' }})</td>
                                </tr>
                                <tr>
                                    <td style="font-weight: 700; background: #f8fafc;">Nama Asesi (Peserta)</td>
                                    <td><strong>{{ $pengguna->nama_lengkap }}</strong> (NIK: {{ $profil->nik ?? '-' }})</td>
                                </tr>
                                <tr>
                                    <td style="font-weight: 700; background: #f8fafc;">Nama Asesor Penguji</td>
                                    <td><strong>{{ $pendaftaran->asesor->nama_lengkap ?? 'Asesor Resmi LSP' }}</strong> (No. Reg: {{ $pendaftaran->asesor->nomor_registrasi ?? 'MET.000.001 2026' }})</td>
                                </tr>
                                <tr>
                                    <td style="font-weight: 700; background: #f8fafc;">TUK & Tanggal Uji</td>
                                    <td>
                                        <strong>{{ $pendaftaran->jadwal->nama_tuk ?? 'TUK Lab Komputer LSP' }}</strong> | 
                                        Tanggal: <strong>{{ $pendaftaran->jadwal ? \Carbon\Carbon::parse($pendaftaran->jadwal->tanggal_uji)->format('d F Y') : date('d F Y') }}</strong>
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>

                    <!-- PILIHAN TUK (Read-Only) -->
                    <div class="subseksi-form">
                        <div class="subseksi-header" style="display: flex; align-items: center; justify-content: space-between;">
                            <span>1. Tempat Uji Kompetensi (TUK)</span>
                            <span style="font-size: 0.75rem; background: #f1f5f9; color: #475569; padding: 0.2rem 0.5rem; border-radius: 4px; font-weight: 600;">
                                Ditetapkan Asesor / LSP
                            </span>
                        </div>
                        @php $tukDipilih = $pendaftaran->tuk_type ?? ''; @endphp
                        <input type="hidden" name="tuk_type" value="{{ $tukDipilih }}">
                        <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 1rem;">
                            <label style="display: flex; align-items: center; gap: 0.5rem; font-weight: 700; cursor: not-allowed; opacity: 0.85;">
                                <input type="radio" disabled value="Sewaktu" {{ $tukDipilih === 'Sewaktu' ? 'checked' : '' }}> TUK Sewaktu
                            </label>
                            <label style="display: flex; align-items: center; gap: 0.5rem; font-weight: 700; cursor: not-allowed; opacity: 0.85;">
                                <input type="radio" disabled value="Tempat Kerja" {{ $tukDipilih === 'Tempat Kerja' ? 'checked' : '' }}> Tempat Kerja
                            </label>
                            <label style="display: flex; align-items: center; gap: 0.5rem; font-weight: 700; cursor: not-allowed; opacity: 0.85;">
                                <input type="radio" disabled value="Mandiri" {{ $tukDipilih === 'Mandiri' ? 'checked' : '' }}> Mandiri
                            </label>
                        </div>
                    </div>

                    <!-- BUKTI YANG DIKUMPULKAN (Read-Only) -->
                    <div class="subseksi-form">
                        <div class="subseksi-header" style="display: flex; align-items: center; justify-content: space-between;">
                            <span>2. Bukti yang Dikumpulkan</span>
                            <span style="font-size: 0.75rem; background: #f1f5f9; color: #475569; padding: 0.2rem 0.5rem; border-radius: 4px; font-weight: 600;">
                                Ditetapkan Asesor / LSP
                            </span>
                        </div>
                        @php $buktiList = (array) ($pendaftaran->bukti_dikumpulkan ?? []); @endphp
                        @foreach($buktiList as $b)
                            <input type="hidden" name="bukti_dikumpulkan[]" value="{{ $b }}">
                        @endforeach
                        <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(260px, 1fr)); gap: 0.85rem;">
                            <label style="display: flex; align-items: center; gap: 0.5rem; cursor: not-allowed; font-size: 0.9rem; opacity: 0.85;">
                                <input type="checkbox" disabled value="TL : Verifikasi Portofolio" {{ in_array('TL : Verifikasi Portofolio', $buktiList) ? 'checked' : '' }}>
                                TL : Verifikasi Portofolio
                            </label>
                            <label style="display: flex; align-items: center; gap: 0.5rem; cursor: not-allowed; font-size: 0.9rem; opacity: 0.85;">
                                <input type="checkbox" disabled value="L : Observasi Langsung / Praktik" {{ in_array('L : Observasi Langsung / Praktik', $buktiList) ? 'checked' : '' }}>
                                L : Observasi Langsung / Praktik
                            </label>
                            <label style="display: flex; align-items: center; gap: 0.5rem; cursor: not-allowed; font-size: 0.9rem; opacity: 0.85;">
                                <input type="checkbox" disabled value="T : Tes Tertulis / Soal Esai" {{ in_array('T : Tes Tertulis / Soal Esai', $buktiList) ? 'checked' : '' }}>
                                T : Tes Tertulis / Soal Esai
                            </label>
                            <label style="display: flex; align-items: center; gap: 0.5rem; cursor: not-allowed; font-size: 0.9rem; opacity: 0.85;">
                                <input type="checkbox" disabled value="T : Tes Lisan / Wawancara" {{ in_array('T : Tes Lisan / Wawancara', $buktiList) ? 'checked' : '' }}>
                                T : Tes Lisan / Wawancara
                            </label>
                        </div>
                        <div style="margin-top: 0.75rem; padding-top: 0.75rem; border-top: 1px solid #f1f5f9;">
                            <label style="display: block; font-size: 0.82rem; font-weight: 700; color: #475569; margin-bottom: 0.35rem;">Bukti / Metode Lainnya:</label>
                            <input type="hidden" name="bukti_dikumpulkan_lainnya" value="{{ old('bukti_dikumpulkan_lainnya', $pendaftaran->bukti_dikumpulkan_lainnya) }}">
                            <div style="font-size: 0.85rem; padding: 0.4rem 0.75rem; background: #f8fafc; border: 1px solid #e2e8f0; border-radius: var(--radius-sm); color: #334155;">
                                {{ $pendaftaran->bukti_dikumpulkan_lainnya ?: '' }}
                            </div>
                        </div>
                    </div>

                    <!-- PERNYATAAN KERAHASIAAN & TTD DUAL BOX -->
                    <div class="subseksi-form">
                        <div class="subseksi-header">
                            3. Pernyataan Persetujuan & Kerahasiaan
                        </div>

                        <div style="background: #ffffff; padding: 1rem 1.25rem; border-radius: var(--radius-sm); border: 1px solid #cbd5e1; font-size: 0.88rem; line-height: 1.5; color: #334155; margin-bottom: 1.5rem;">
                            <strong>Asesi dan Asesor sepakat bahwa:</strong>
                            <ol style="margin: 0.4rem 0 0 1.25rem; padding: 0;">
                                <li>Pelaksanaan asesmen akan dilakukan sesuai dengan rencana asesmen dan skema sertifikasi yang telah ditetapkan.</li>
                                <li>Seluruh perangkat asesmen dan hasil penilaian bersifat <strong>RAHASIA</strong> dan tidak akan dibocorkan kepada pihak yang tidak berwenang.</li>
                            </ol>
                        </div>

                        <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(280px, 1fr)); gap: 1.5rem;">
                            <!-- BOX ASESI -->
                            <div style="border: 1px solid #cbd5e1; padding: 1.25rem; border-radius: var(--radius-md); text-align: center; background: #ffffff;">
                                <div style="font-weight: 700; color: var(--biru-malam); margin-bottom: 0.25rem;">Tanda Tangan Asesi (Peserta)</div>
                                <div style="font-size: 0.8rem; color: var(--abu-teks); margin-bottom: 0.75rem;">{{ $pengguna->nama_lengkap }}</div>

                                @php
                                    $existingTtdTerpadu = $pendaftaran ? ($pendaftaran->tanda_tangan_asesi_ak01 ?: ($pendaftaran->tanda_tangan_asesi ?: ($pengguna->tanda_tangan ?? null))) : ($pengguna->tanda_tangan ?? null);
                                    $hasExistingTtdTerpadu = !empty($existingTtdTerpadu);
                                    $existingTtdTerpaduUrl = null;
                                    if ($hasExistingTtdTerpadu) {
                                        $existingTtdTerpaduUrl = Str::startsWith($existingTtdTerpadu, ['data:', 'http://', 'https://']) ? $existingTtdTerpadu : asset($existingTtdTerpadu);
                                    }
                                @endphp

                                @if($pendaftaran && $pendaftaran->tanda_tangan_asesi_ak01)
                                    <img src="{{ Str::startsWith($pendaftaran->tanda_tangan_asesi_ak01, 'data:') ? $pendaftaran->tanda_tangan_asesi_ak01 : asset($pendaftaran->tanda_tangan_asesi_ak01) }}" alt="TTD Asesi AK01" style="max-height: 80px; margin-bottom: 0.5rem;">
                                    <div style="font-size: 0.75rem; color: var(--hijau-sukses); font-weight: 700;">
                                        Ditandatangani {{ \Carbon\Carbon::parse($pendaftaran->tanggal_ttd_asesi_ak01)->format('d/m/Y H:i') }}
                                    </div>
                                @elseif($hasExistingTtdTerpadu)
                                    <div id="box-preview-ttd-ak01-terpadu">
                                        <img src="{{ $existingTtdTerpaduUrl }}" alt="TTD Asesi" style="max-height: 80px; margin-bottom: 0.5rem;">
                                        <div style="font-size: 0.75rem; color: var(--hijau-sukses); font-weight: 600; margin-bottom: 0.5rem;">
                                            Tanda tangan otomatis terisi dari profil/pendaftaran Anda
                                        </div>
                                        <button type="button" class="tombol tombol-outline tombol-sm" onclick="document.getElementById('box-preview-ttd-ak01-terpadu').style.display='none'; document.getElementById('box-canvas-ttd-ak01-terpadu').style.display='block'; document.getElementById('input-ttd-ak01').value='';">
                                            Ubah Tanda Tangan
                                        </button>
                                    </div>
                                    <div id="box-canvas-ttd-ak01-terpadu" class="canvas-signature-wrap" style="display: none;">
                                        <canvas id="canvas-ttd-ak01" width="800" height="240" class="canvas-signature" style="height: 120px; touch-action: none; -ms-touch-action: none;"></canvas>
                                        <div style="margin-top: 0.4rem; display: flex; gap: 0.5rem; justify-content: center;">
                                            <button type="button" class="tombol tombol-outline tombol-sm" onclick="resetCanvas('canvas-ttd-ak01', 'input-ttd-ak01')" style="font-size: 0.75rem; padding: 0.2rem 0.5rem;">
                                                Bersihkan
                                            </button>
                                            <button type="button" class="tombol tombol-outline tombol-sm" onclick="document.getElementById('box-canvas-ttd-ak01-terpadu').style.display='none'; document.getElementById('box-preview-ttd-ak01-terpadu').style.display='block'; document.getElementById('input-ttd-ak01').value='{{ $existingTtdTerpadu }}';" style="font-size: 0.75rem; padding: 0.2rem 0.5rem;">
                                                Batal Ubah
                                            </button>
                                        </div>
                                    </div>
                                    <input type="hidden" name="tanda_tangan_asesi_ak01" id="input-ttd-ak01" value="{{ $existingTtdTerpadu }}">
                                @elseif(!$isAk01Selesai)
                                    <div class="canvas-signature-wrap">
                                        <canvas id="canvas-ttd-ak01" width="800" height="240" class="canvas-signature" style="height: 120px; touch-action: none; -ms-touch-action: none;"></canvas>
                                        <input type="hidden" name="tanda_tangan_asesi_ak01" id="input-ttd-ak01" value="">
                                        <div style="margin-top: 0.4rem;">
                                            <button type="button" class="tombol tombol-outline tombol-sm" onclick="resetCanvas('canvas-ttd-ak01', 'input-ttd-ak01')" style="font-size: 0.75rem; padding: 0.2rem 0.5rem;">
                                                Bersihkan
                                            </button>
                                        </div>
                                    </div>
                                @endif
                            </div>

                            <!-- BOX ASESOR -->
                            <div style="border: 1px solid #cbd5e1; padding: 1.25rem; border-radius: var(--radius-md); text-align: center; background: #ffffff;">
                                <div style="font-weight: 700; color: var(--biru-malam); margin-bottom: 0.25rem;">Tanda Tangan Asesor Penguji</div>
                                <div style="font-size: 0.8rem; color: var(--abu-teks); margin-bottom: 0.75rem;">{{ $pendaftaran->asesor->nama_lengkap ?? 'Asesor Resmi LSP' }}</div>

                                @if($pendaftaran && ($pendaftaran->tanda_tangan_asesor_ak01 || $pendaftaran->tanda_tangan_asesor))
                                    @php
                                        $ttdAsesorVal = $pendaftaran->tanda_tangan_asesor_ak01 ?: $pendaftaran->tanda_tangan_asesor;
                                    @endphp
                                    <img src="{{ Str::startsWith($ttdAsesorVal, 'data:') ? $ttdAsesorVal : asset($ttdAsesorVal) }}" alt="TTD Asesor" style="max-height: 80px; margin-bottom: 0.5rem;">
                                    <div style="font-size: 0.75rem; color: var(--hijau-sukses); font-weight: 700;">
                                        Disahkan Asesor Penguji
                                    </div>
                                @else
                                    <div style="padding: 2rem 1rem; color: var(--abu-teks); font-size: 0.85rem; font-style: italic;">
                                        Menunggu pengesahan tanda tangan Asesor Penguji
                                    </div>
                                @endif
                            </div>
                        </div>
                    </div>

                    @if(!$isAk01Selesai)
                        <div style="display: flex; justify-content: flex-end; margin-top: 2rem; border-top: 1px solid #e2e8f0; padding-top: 1.5rem;">
                            <button type="submit" class="tombol tombol-sukses" onclick="return confirm('Apakah Anda yakin ingin mengirim dan menyetujui Formulir FR.AK.01?')">
                                Kirim & Setujui Formulir FR.AK.01 ✅
                            </button>
                        </div>
                    @endif
                </form>
            @endif
        </div>
    </div>

</div>

<!-- MODAL DAFTAR SKEMA BARU -->
<div id="modal-skema-baru" style="display: none; position: fixed; inset: 0; background: rgba(0,0,0,0.5); z-index: 9999; align-items: center; justify-content: center; padding: 1rem;">
    <div style="background: #ffffff; border-radius: var(--radius-lg); max-width: 550px; width: 100%; padding: 2rem; box-shadow: var(--bayangan-soft); position: relative;" class="animasi-slide">
        <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 1.25rem;">
            <h3 style="color: var(--biru-malam); font-size: 1.25rem; font-weight: 800; margin: 0;">
                Mendaftar Skema Sertifikasi Baru
            </h3>
            <button type="button" onclick="tutupModalSkemaBaru()" style="background: none; border: none; font-size: 1.25rem; cursor: pointer; color: var(--abu-teks);">
                &times;
            </button>
        </div>

        <p style="font-size: 0.88rem; color: var(--abu-teks); margin-bottom: 1.5rem;">
            Pilih skema sertifikasi keahlian yang ingin Anda daftarkan. Anda dapat mendaftar skema baru jika skema sebelumnya telah selesai atau ditolak.
        </p>

        <form action="{{ route('asesi.formulir.skema-baru') }}" method="POST">
            @csrf
            <div class="grup-form" style="margin-bottom: 1.5rem;">
                <label class="label-form">Pilih Skema Sertifikasi</label>
                <select name="skema_id" class="input-control" required style="font-size: 0.95rem; font-weight: 700; padding: 0.75rem;">
                    <option value="">-- Pilih Skema --</option>
                    @foreach($skemaList as $s)
                        @php
                            $isRegistered = in_array($s->id, $registeredSkemaIds ?? []);
                        @endphp
                        <option value="{{ $s->id }}" {{ $isRegistered ? 'disabled' : '' }}>
                            [{{ $s->kode_skema }}] {{ $s->nama_skema }} {{ $isRegistered ? '⚠️ (Sudah Aktif)' : '' }}
                        </option>
                    @endforeach
                </select>
            </div>

            <div style="display: flex; justify-content: flex-end; gap: 0.75rem;">
                <button type="button" class="tombol tombol-sekunder" onclick="tutupModalSkemaBaru()">Batal</button>
                <button type="submit" class="tombol tombol-utama">
                    &#10003; Buat Pendaftaran Skema Ini
                </button>
            </div>
        </form>
    </div>
</div>

    <!-- =========================================================================
         MODAL PILIH BUKTI DARI APL.01
         ========================================================================= -->
    <div id="modal-pilih-apl01" class="fixed inset-0 items-center justify-center bg-slate-900/70 backdrop-blur-xs p-4 hidden" style="z-index: 99999; display: none;">
        <div class="bg-white rounded-2xl border border-slate-200 shadow-2xl max-w-md w-full p-5 space-y-4 animate-scale-up relative">
            <div class="flex items-center justify-between border-b border-slate-100 pb-3">
                <div class="flex items-center gap-2.5">
                    <span class="w-8 h-8 rounded-xl bg-blue-50 text-blue-600 flex items-center justify-center font-bold text-sm">
                        </span>
                    <div>
                        <h3 class="font-bold text-slate-800 text-sm">Pilih Bukti dari APL.01</h3>
                        <p class="text-[11px] text-slate-400">Pilih berkas APL.01 yang ingin ditautkan pada elemen ini</p>
                    </div>
                </div>
                <button type="button" onclick="tutupModalApl01()" class="text-slate-400 hover:text-slate-600 text-xl font-bold p-1 cursor-pointer">&times;</button>
            </div>

            <div class="space-y-2 max-h-64 overflow-y-auto pr-1">
                @forelse($dokumenTeknis as $dok)
                    <label class="flex items-center gap-3 p-3 rounded-xl border border-slate-200 hover:bg-slate-50 cursor-pointer transition-colors">
                        <input type="radio" name="pilih_dokumen_apl01" value="{{ $dok->id }}" class="text-blue-600 focus:ring-blue-500">
                        <div class="min-w-0 flex-1 text-xs">
                            <div class="font-bold text-slate-800">{{ $dok->jenis_dokumen }}</div>
                            <div class="text-slate-500 text-[11px] truncate">{{ $dok->nama_dokumen }}</div>
                        </div>
                    </label>
                @empty
                    <div class="text-center py-6 text-xs text-slate-400 italic">
                        Tidak ada berkas teknis APL.01 yang tersedia.
                    </div>
                @endforelse
            </div>

            <div class="flex items-center justify-end gap-2 pt-2 border-t border-slate-100">
                <button type="button" onclick="tutupModalApl01()" class="px-4 py-2 rounded-xl border border-slate-200 hover:bg-slate-50 text-slate-600 font-semibold text-xs cursor-pointer">
                    Batal
                </button>
                <button type="button" id="btn-submit-taut-apl01" onclick="submitTautkanApl01('{{ $pendaftaran ? $pendaftaran->id : '' }}')" class="px-4 py-2 rounded-xl bg-blue-600 hover:bg-blue-700 text-white font-semibold text-xs shadow-xs flex items-center gap-1.5 cursor-pointer">
                    Gunakan Bukti
                </button>
            </div>
        </div>
    </div>

    <!-- =========================================================================
         MODAL PRATINJAU PDF (UNIVERSAL VIEWER)
         ========================================================================= -->
    <div id="modal-pratinjau-pdf" class="fixed inset-0 items-center justify-center bg-slate-900/75 backdrop-blur-xs p-4 hidden" style="z-index: 99999; display: none;">
        <div class="bg-white rounded-2xl border border-slate-200 shadow-2xl max-w-4xl w-full h-[85vh] flex flex-col overflow-hidden animate-scale-up relative">
            <div class="flex items-center justify-between px-5 py-3.5 border-b border-slate-100 bg-slate-50">
                <div class="flex items-center gap-2 min-w-0">
                    <h3 id="pdf-viewer-title" class="font-bold text-slate-800 text-xs sm:text-sm truncate">Dokumen PDF</h3>
                </div>
                <div class="flex items-center gap-2 shrink-0">
                    <a id="pdf-viewer-download" href="" target="_blank" class="px-3 py-1.5 bg-white border border-slate-200 rounded-lg text-xs font-semibold text-slate-700 hover:bg-slate-50 transition-colors flex items-center gap-1">
                        Buka Tab Baru
                    </a>
                    <button type="button" onclick="tutupModalPdf()" class="text-slate-400 hover:text-slate-600 text-xl font-bold px-2 cursor-pointer">&times;</button>
                </div>
            </div>
            <div class="flex-1 bg-slate-100 p-1">
                <iframe id="pdf-viewer-frame" src="" class="w-full h-full rounded-xl border-none"></iframe>
            </div>
        </div>
    </div>

    <!-- =========================================================================
         MODAL PRATINJAU GAMBAR (UNIVERSAL LIGHTBOX)
         ========================================================================= -->
    <div id="modal-pratinjau-gambar" class="fixed inset-0 items-center justify-center bg-slate-900/75 backdrop-blur-xs p-4 hidden" style="z-index: 99999; display: none;">
        <div class="bg-white rounded-2xl border border-slate-200 shadow-2xl max-w-3xl w-full max-h-[90vh] flex flex-col overflow-hidden animate-scale-up relative">
            <div class="flex items-center justify-between px-5 py-3.5 border-b border-slate-100 bg-slate-50">
                <div class="flex items-center gap-2 min-w-0">
                    <h3 id="judul-pratinjau-gambar-universal" class="font-bold text-slate-800 text-xs sm:text-sm truncate">Pratinjau Gambar</h3>
                </div>
                <div class="flex items-center gap-2 shrink-0">
                    <a id="btn-download-gambar-universal" href="" target="_blank" download class="px-3 py-1.5 bg-white border border-slate-200 rounded-lg text-xs font-semibold text-slate-700 hover:bg-slate-50 transition-colors flex items-center gap-1">
                        Unduh File
                    </a>
                    <button type="button" onclick="tutupModalGambar()" class="text-slate-400 hover:text-slate-600 text-xl font-bold px-2 cursor-pointer">&times;</button>
                </div>
            </div>
            <div class="flex-1 bg-slate-950/90 p-4 flex items-center justify-center overflow-auto min-h-[300px]">
                <img id="img-pratinjau-gambar-universal" src="" alt="Pratinjau Gambar" class="max-w-full max-h-[70vh] object-contain rounded-lg shadow-lg">
            </div>
        </div>
    </div>
</div>
@endsection

@push('js')
<script src="https://cdn.jsdelivr.net/npm/signature_pad@4.1.7/dist/signature_pad.umd.min.js"></script>
<script>
    // FUNGSI GANTI TAB PORTAL
    function gantiTabPortal(tabName) {
        document.getElementById('konten-tab-apl01').style.display = (tabName === 'apl01') ? 'block' : 'none';
        document.getElementById('konten-tab-apl02').style.display = (tabName === 'apl02') ? 'block' : 'none';
        document.getElementById('konten-tab-ak01').style.display = (tabName === 'ak01') ? 'block' : 'none';

        // Update active class on tab buttons
        const tabItems = document.querySelectorAll('.tab-item-portal');
        tabItems.forEach(el => el.classList.remove('aktif'));

        if (tabName === 'apl01') tabItems[0].classList.add('aktif');
        if (tabName === 'apl02') tabItems[1].classList.add('aktif');
        if (tabName === 'ak01') tabItems[2].classList.add('aktif');

        // Update URL query parameter without full reload
        const url = new URL(window.location);
        url.searchParams.set('tab', tabName);
        window.history.replaceState({}, '', url);

        // Resize and re-render signature canvases with slight delay to ensure tab is visible
        setTimeout(() => {
            initSignatureCanvas('canvas-ttd-apl01', 'input-ttd-apl01');
            initSignatureCanvas('canvas-ttd-apl02', 'input-ttd-apl02');
            initSignatureCanvas('canvas-ttd-ak01', 'input-ttd-ak01');
        }, 60);
    }

    function peringatanTerkunci(jenis) {
        if (jenis === 'apl02') {
            alert('Formulir FR.APL.02 Asesmen Mandiri masih terkunci. Anda harus menunggu persetujuan (ACC) Formulir FR.APL.01 oleh Admin LSP terlebih dahulu.');
        } else if (jenis === 'ak01') {
            @if(!$isApl02Selesai)
                alert('Formulir FR.AK.01 Persetujuan Asesmen masih terkunci. Anda harus mengisi dan mengirim Formulir FR.APL.02 Asesmen Mandiri terlebih dahulu.');
            @elseif(!$isAccAsesor)
                alert('Formulir FR.AK.01 Persetujuan Asesmen masih terkunci. Anda harus menunggu Formulir FR.APL.02 disetujui (ACC) oleh Asesor Penguji ({{ $pendaftaran->asesor->nama_lengkap ?? "Asesor" }}) terlebih dahulu.');
            @else
                alert('Formulir FR.AK.01 Persetujuan Asesmen masih terkunci.');
            @endif
        }
    }

    function updateDaftarUnitSkema(skemaId) {
        document.querySelectorAll('.tabel-unit-skema-box').forEach(el => el.style.display = 'none');
        const target = document.getElementById('unit-box-' + skemaId);
        if (target) {
            target.style.display = 'block';
        }
    }

    function syncKukState(kukId, val, elemenId) {
        const kBtn = document.querySelector(`.btn-k-state-kuk-${kukId}`);
        const bkBtn = document.querySelector(`.btn-bk-state-kuk-${kukId}`);

        if (kBtn && bkBtn) {
            if (val === 'K') {
                kBtn.className = 'btn-k-state-kuk-' + kukId + ' bg-emerald-600 text-white font-semibold shadow-xs border border-emerald-600';
                bkBtn.className = 'btn-bk-state-kuk-' + kukId + ' bg-slate-100 text-slate-600 hover:bg-slate-200 border border-slate-200';
            } else {
                kBtn.className = 'btn-k-state-kuk-' + kukId + ' bg-slate-100 text-slate-600 hover:bg-slate-200 border border-slate-200';
                bkBtn.className = 'btn-bk-state-kuk-' + kukId + ' bg-rose-600 text-white font-semibold shadow-xs border border-rose-600';
            }
        }

        if (elemenId) {
            const elemenInputs = document.querySelectorAll(`input[data-elemen='${elemenId}']:checked`);
            let isBk = false;
            elemenInputs.forEach(i => {
                if (i.value === 'BK') isBk = true;
            });
            const hiddenElem = document.getElementById(`input_elemen_${elemenId}`);
            if (hiddenElem) {
                hiddenElem.value = isBk ? 'BK' : 'K';
            }
        }
    }

    function pilihSemuaKompeten() {
        const kInputs = document.querySelectorAll('.radio-k');
        kInputs.forEach(input => {
            input.checked = true;
            const kukId = input.getAttribute('data-kuk-id');
            const elemId = input.getAttribute('data-elemen');
            if (kukId && elemId) {
                syncKukState(kukId, 'K', elemId);
            }
        });
    }

    function pilihSemuaKompetenUnit(unitId) {
        const kInputs = document.querySelectorAll(`input[data-unit='${unitId}'][value='K']`);
        kInputs.forEach(input => {
            input.checked = true;
            const kukId = input.getAttribute('data-kuk-id');
            const elemId = input.getAttribute('data-elemen');
            if (kukId && elemId) {
                syncKukState(kukId, 'K', elemId);
            }
        });
    }

    function bukaModalSkemaBaru() {
        const modal = document.getElementById('modal-skema-baru');
        if (modal) modal.style.display = 'flex';
    }

    function tutupModalSkemaBaru() {
        const modal = document.getElementById('modal-skema-baru');
        if (modal) modal.style.display = 'none';
    }

    // CANVAS DIGITAL SIGNATURE ENGINE (DUAL-ENGINE: SIGNATURE_PAD + NATIVE HTML5 FALLBACK)
    window._signaturePadInstances = window._signaturePadInstances || {};

    function initSignatureCanvas(canvasId, inputId) {
        const canvas = document.getElementById(canvasId);
        const input = document.getElementById(inputId);
        if (!canvas || !input) return null;

        if (window._signaturePadInstances[canvasId]) {
            const inst = window._signaturePadInstances[canvasId];
            setTimeout(() => inst.resize(), 30);
            return inst;
        }

        let pad = null;
        let isNativeDrawing = false;
        let strokes = [];
        let currentStroke = [];

        function getCanvasCoords(e) {
            const rect = canvas.getBoundingClientRect();
            const clientX = (e.touches && e.touches.length > 0) ? e.touches[0].clientX : e.clientX;
            const clientY = (e.touches && e.touches.length > 0) ? e.touches[0].clientY : e.clientY;
            const scaleX = (rect.width > 0) ? (canvas.width / rect.width) : 1;
            const scaleY = (rect.height > 0) ? (canvas.height / rect.height) : 1;
            return {
                x: (clientX - rect.left) * scaleX,
                y: (clientY - rect.top) * scaleY
            };
        }

        function redrawNativeStrokes() {
            const ctx = canvas.getContext('2d');
            ctx.clearRect(0, 0, canvas.width, canvas.height);
            ctx.strokeStyle = '#0f172a';
            ctx.lineWidth = 2.5;
            ctx.lineCap = 'round';
            ctx.lineJoin = 'round';

            const allStrokes = [...strokes];
            if (currentStroke.length > 0) {
                allStrokes.push(currentStroke);
            }

            for (const stroke of allStrokes) {
                if (stroke.length < 2) {
                    if (stroke.length === 1) {
                        ctx.beginPath();
                        ctx.arc(stroke[0].x, stroke[0].y, 1.25, 0, Math.PI * 2);
                        ctx.fillStyle = '#0f172a';
                        ctx.fill();
                    }
                    continue;
                }
                ctx.beginPath();
                ctx.moveTo(stroke[0].x, stroke[0].y);
                for (let i = 1; i < stroke.length; i++) {
                    ctx.lineTo(stroke[i].x, stroke[i].y);
                }
                ctx.stroke();
            }
        }

        function resizeCanvas() {
            const rect = canvas.getBoundingClientRect();
            const displayWidth = rect.width || canvas.clientWidth || 480;
            const displayHeight = rect.height || canvas.clientHeight || (canvasId === 'canvas-ttd-ak01' ? 120 : 160);

            if (displayWidth <= 0 || displayHeight <= 0) return;

            const ratio = Math.max(window.devicePixelRatio || 1, 1);
            const targetW = Math.round(displayWidth * ratio);
            const targetH = Math.round(displayHeight * ratio);

            if (canvas.width !== targetW || canvas.height !== targetH) {
                let savedData = null;
                if (pad && !pad.isEmpty()) {
                    savedData = pad.toDataURL();
                } else if (input && input.value && input.value.startsWith('data:image')) {
                    savedData = input.value;
                }

                canvas.width = targetW;
                canvas.height = targetH;

                const ctx = canvas.getContext('2d');
                ctx.scale(ratio, ratio);

                if (pad) {
                    pad.clear();
                    if (savedData) {
                        pad.fromDataURL(savedData);
                    }
                } else if (strokes.length > 0) {
                    redrawNativeStrokes();
                } else if (savedData) {
                    const img = new Image();
                    img.onload = () => ctx.drawImage(img, 0, 0, displayWidth, displayHeight);
                    img.src = savedData;
                }
            }
        }

        if (typeof SignaturePad !== 'undefined') {
            try {
                pad = new SignaturePad(canvas, {
                    backgroundColor: 'rgba(255, 255, 255, 0)',
                    penColor: '#0f172a',
                    minWidth: 1.2,
                    maxWidth: 2.8
                });

                if (input && input.value && input.value.startsWith('data:image')) {
                    pad.fromDataURL(input.value);
                }

                pad.addEventListener('endStroke', () => {
                    input.value = pad.toDataURL('image/png');
                });
            } catch (err) {
                console.warn('SignaturePad initialization failed, falling back to native engine:', err);
                pad = null;
            }
        }

        if (!pad) {
            const startNative = (e) => {
                e.preventDefault();
                isNativeDrawing = true;
                currentStroke = [getCanvasCoords(e)];
                redrawNativeStrokes();
            };

            const moveNative = (e) => {
                if (!isNativeDrawing) return;
                e.preventDefault();
                currentStroke.push(getCanvasCoords(e));
                redrawNativeStrokes();
            };

            const stopNative = (e) => {
                if (!isNativeDrawing) return;
                isNativeDrawing = false;
                if (currentStroke.length > 0) {
                    strokes.push(currentStroke);
                    currentStroke = [];
                    input.value = canvas.toDataURL('image/png');
                }
            };

            canvas.addEventListener('mousedown', startNative);
            canvas.addEventListener('mousemove', moveNative);
            canvas.addEventListener('mouseup', stopNative);
            canvas.addEventListener('mouseleave', stopNative);

            canvas.addEventListener('touchstart', startNative, { passive: false });
            canvas.addEventListener('touchmove', moveNative, { passive: false });
            canvas.addEventListener('touchend', stopNative);
        }

        if (window.ResizeObserver) {
            const ro = new ResizeObserver((entries) => {
                for (const entry of entries) {
                    if (entry.contentRect.width > 0) {
                        resizeCanvas();
                    }
                }
            });
            ro.observe(canvas);
        }

        window.addEventListener('resize', resizeCanvas);
        setTimeout(resizeCanvas, 50);
        setTimeout(resizeCanvas, 200);

        const instance = {
            pad,
            clear() {
                if (pad) {
                    pad.clear();
                } else {
                    strokes = [];
                    currentStroke = [];
                    const ctx = canvas.getContext('2d');
                    ctx.clearRect(0, 0, canvas.width, canvas.height);
                }
                if (input) input.value = '';
            },
            toDataURL() {
                return pad ? pad.toDataURL('image/png') : canvas.toDataURL('image/png');
            },
            isEmpty() {
                if (pad) return pad.isEmpty();
                return strokes.length === 0;
            },
            resize: resizeCanvas
        };

        window._signaturePadInstances[canvasId] = instance;
        return instance;
    }

    let targetElemenIdForApl01 = null;

    function triggerUploadBukti(elemenId) {
        const input = document.getElementById(`file-input-elem-${elemenId}`);
        if (input) {
            input.click();
        }
    }

    function handleUploadBukti(input, elemenId, pendaftaranId) {
        if (!input.files || input.files.length === 0) return;
        const file = input.files[0];

        // Validasi client-side
        const allowedExts = ['pdf', 'jpg', 'jpeg', 'png'];
        const ext = file.name.split('.').pop().toLowerCase();
        if (!allowedExts.includes(ext)) {
            alert('Format file tidak didukung. Gunakan PDF, JPG, JPEG, atau PNG.');
            input.value = '';
            return;
        }

        if (file.size > 10 * 1024 * 1024) {
            alert('Ukuran file terlalu besar. Maksimal 10 MB.');
            input.value = '';
            return;
        }

        const formData = new FormData();
        formData.append('pendaftaran_id', pendaftaranId);
        formData.append('elemen_id', elemenId);
        formData.append('file', file);
        formData.append('_token', '{{ csrf_token() }}');

        // Loading state
        const loadingEl = document.getElementById(`upload-loading-elem-${elemenId}`);
        const btnUpload = document.getElementById(`btn-upload-elem-${elemenId}`);
        const btnApl01 = document.getElementById(`btn-apl01-elem-${elemenId}`);
        if (loadingEl) loadingEl.style.display = 'block';
        if (btnUpload) btnUpload.disabled = true;
        if (btnApl01) btnApl01.disabled = true;

        fetch('{{ route("asesi.apl02.upload_bukti") }}', {
            method: 'POST',
            body: formData,
            headers: {
                'X-Requested-With': 'XMLHttpRequest'
            }
        })
        .then(res => res.json())
        .then(data => {
            if (loadingEl) loadingEl.style.display = 'none';
            if (btnUpload) btnUpload.disabled = false;
            if (btnApl01) btnApl01.disabled = false;
            input.value = '';

            if (data.success) {
                tambahKartuBuktiKeDom(elemenId, data.data);
                tampilkanToast(data.message || 'Bukti berhasil diupload.');
            } else {
                alert(data.message || 'Gagal mengupload file.');
            }
        })
        .catch(err => {
            if (loadingEl) loadingEl.style.display = 'none';
            if (btnUpload) btnUpload.disabled = false;
            if (btnApl01) btnApl01.disabled = false;
            input.value = '';
            alert('Terjadi kesalahan jaringan atau server saat mengupload file.');
        });
    }

    function bukaModalPilihApl01(elemenId) {
        targetElemenIdForApl01 = elemenId;
        const modal = document.getElementById('modal-pilih-apl01');
        if (modal) {
            modal.classList.remove('hidden');
            modal.style.display = 'flex';
        }
    }

    function tutupModalApl01() {
        targetElemenIdForApl01 = null;
        const modal = document.getElementById('modal-pilih-apl01');
        if (modal) {
            modal.classList.add('hidden');
            modal.style.display = 'none';
        }
    }

    function submitTautkanApl01(pendaftaranId) {
        if (!targetElemenIdForApl01) return;
        const selected = document.querySelector('input[name="pilih_dokumen_apl01"]:checked');
        if (!selected) {
            alert('Silakan pilih salah satu dokumen APL.01.');
            return;
        }

        const elemenId = targetElemenIdForApl01;
        const dokumenId = selected.value;

        const btnSubmit = document.getElementById('btn-submit-taut-apl01');
        if (btnSubmit) {
            btnSubmit.disabled = true;
            btnSubmit.innerHTML = 'Menautkan...';
        }

        fetch('{{ route("asesi.apl02.pilih_bukti_apl01") }}', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': '{{ csrf_token() }}',
                'X-Requested-With': 'XMLHttpRequest'
            },
            body: JSON.stringify({
                pendaftaran_id: pendaftaranId,
                elemen_id: elemenId,
                dokumen_id: dokumenId
            })
        })
        .then(res => res.json())
        .then(data => {
            if (btnSubmit) {
                btnSubmit.disabled = false;
                btnSubmit.innerHTML = 'Gunakan Bukti';
            }
            tutupModalApl01();

            if (data.success) {
                tambahKartuBuktiKeDom(elemenId, data.data);
                tampilkanToast(data.message || 'Bukti dari APL.01 berhasil ditautkan.');
            } else {
                alert(data.message || 'Gagal menautkan dokumen APL.01.');
            }
        })
        .catch(err => {
            if (btnSubmit) {
                btnSubmit.disabled = false;
                btnSubmit.innerHTML = 'Gunakan Bukti';
            }
            alert('Terjadi kesalahan saat memproses permintaan.');
        });
    }

    function hapusBukti(buktiId, elemenId) {
        if (!confirm('Apakah Anda yakin ingin menghapus bukti pendukung ini?')) {
            return;
        }

        const itemEl = document.getElementById(`bukti-item-${buktiId}`);
        if (itemEl) itemEl.style.opacity = '0.4';

        fetch('{{ url("/asesi/tahapan/apl02/hapus-bukti") }}/' + buktiId, {
            method: 'DELETE',
            headers: {
                'X-CSRF-TOKEN': '{{ csrf_token() }}',
                'X-Requested-With': 'XMLHttpRequest'
            }
        })
        .then(res => res.json())
        .then(data => {
            if (data.success) {
                if (itemEl) itemEl.remove();
                const listEl = document.getElementById(`bukti-list-elem-${elemenId}`);
                const emptyEl = document.getElementById(`bukti-empty-elem-${elemenId}`);
                if (listEl && listEl.children.length === 0 && emptyEl) {
                    emptyEl.style.display = 'block';
                }
                tampilkanToast('Bukti berhasil dihapus.');
            } else {
                if (itemEl) itemEl.style.opacity = '1';
                alert(data.message || 'Gagal menghapus bukti.');
            }
        })
        .catch(err => {
            if (itemEl) itemEl.style.opacity = '1';
            alert('Terjadi kesalahan saat menghapus bukti.');
        });
    }

    function tambahKartuBuktiKeDom(elemenId, item) {
        const listEl = document.getElementById(`bukti-list-elem-${elemenId}`);
        const emptyEl = document.getElementById(`bukti-empty-elem-${elemenId}`);
        if (emptyEl) emptyEl.style.display = 'none';

        const iconHtml = item.is_pdf
            ? '<div style="width: 26px; height: 26px; border-radius: 4px; background: #fee2e2; color: #dc2626; display: flex; align-items: center; justify-content: center; font-weight: 700; font-size: 0.75rem; shrink: 0;">PDF</div>'
            : '<div style="width: 26px; height: 26px; border-radius: 4px; background: #dbeafe; color: #2563eb; display: flex; align-items: center; justify-content: center; font-weight: 700; font-size: 0.75rem; shrink: 0;">IMG</div>';

        const aplBadge = item.sumber === 'apl01'
            ? '<span style="background: #eff6ff; color: #1d4ed8; padding: 0.05rem 0.35rem; border-radius: 4px; border: 1px solid #bfdbfe; font-size: 0.65rem; font-weight: 600;">APL.01</span>'
            : '';

        const div = document.createElement('div');
        div.id = `bukti-item-${item.id}`;
        div.style.cssText = 'display: flex; justify-content: space-between; align-items: center; padding: 0.4rem 0.65rem; border-radius: 6px; border: 1px solid #e2e8f0; background: #ffffff; font-size: 0.78rem; gap: 0.5rem;';
        div.innerHTML = `
            <div style="display: flex; align-items: center; gap: 0.5rem; min-width: 0; flex: 1;">
                ${iconHtml}
                <div style="min-width: 0; flex: 1;">
                    <div style="font-weight: 600; color: #1e293b; overflow: hidden; text-overflow: ellipsis; white-space: nowrap;" title="${item.nama}">${item.nama}</div>
                    <div style="font-size: 0.7rem; color: #94a3b8; display: flex; align-items: center; gap: 0.4rem; margin-top: 0.1rem;">
                        <span>${item.ukuran}</span>
                        ${aplBadge}
                    </div>
                </div>
            </div>
            <div style="display: flex; align-items: center; gap: 0.35rem; shrink: 0;">
                <button type="button" 
                        onclick="bukaPratinjauBukti('${item.url}', '${item.nama.replace(/'/g, "\\'")}', ${item.is_pdf ? 'true' : 'false'})"
                        style="padding: 0.25rem 0.5rem; border-radius: 4px; background: #ffffff; border: 1px solid #cbd5e1; color: #334155; font-weight: 600; font-size: 0.72rem; cursor: pointer; display: inline-flex; align-items: center; gap: 0.25rem;">
                    Lihat
                </button>
                <button type="button" 
                        onclick="hapusBukti('${item.id}', '${elemenId}')"
                        style="padding: 0.25rem 0.45rem; border-radius: 4px; background: #ffffff; border: 1px solid #fecdd3; color: #e11d48; font-weight: 600; font-size: 0.72rem; cursor: pointer;"
                        title="Hapus Bukti">
                    Hapus
                </button>
            </div>
        `;

        if (listEl) {
            listEl.appendChild(div);
        }
    }

    function bukaPratinjauBukti(url, title, isPdf) {
        if (!url) return;
        if (isPdf || url.toLowerCase().includes('.pdf')) {
            const modal = document.getElementById('modal-pratinjau-pdf');
            const frame = document.getElementById('pdf-viewer-frame');
            const titleEl = document.getElementById('pdf-viewer-title');
            const dwnEl = document.getElementById('pdf-viewer-download');
            if (frame) frame.src = url;
            if (titleEl) titleEl.innerText = title || 'Dokumen PDF';
            if (dwnEl) dwnEl.href = url;
            if (modal) {
                modal.classList.remove('hidden');
                modal.style.display = 'flex';
            }
        } else {
            const modal = document.getElementById('modal-pratinjau-gambar');
            const img = document.getElementById('img-pratinjau-gambar-universal');
            const titleEl = document.getElementById('judul-pratinjau-gambar-universal');
            const dwnEl = document.getElementById('btn-download-gambar-universal');
            if (img) img.src = url;
            if (titleEl) titleEl.innerText = title || 'Pratinjau Gambar';
            if (dwnEl) dwnEl.href = url;
            if (modal) {
                modal.classList.remove('hidden');
                modal.style.display = 'flex';
            }
        }
    }

    function tutupModalPdf() {
        const modal = document.getElementById('modal-pratinjau-pdf');
        const frame = document.getElementById('pdf-viewer-frame');
        if (frame) frame.src = '';
        if (modal) {
            modal.classList.add('hidden');
            modal.style.display = 'none';
        }
    }

    function tutupModalGambar() {
        const modal = document.getElementById('modal-pratinjau-gambar');
        const img = document.getElementById('img-pratinjau-gambar-universal');
        if (img) img.src = '';
        if (modal) {
            modal.classList.add('hidden');
            modal.style.display = 'none';
        }
    }

    function tampilkanToast(pesan) {
        const toast = document.createElement('div');
        toast.className = 'fixed bottom-6 right-6 z-99999 bg-slate-900 text-white text-xs font-semibold px-4 py-3 rounded-xl shadow-lg flex items-center gap-2 transition-all duration-300 transform translate-y-4 opacity-0';
        toast.innerHTML = '<span class="text-emerald-400">✓</span> ' + pesan;
        document.body.appendChild(toast);
        setTimeout(() => {
            toast.classList.remove('translate-y-4', 'opacity-0');
        }, 10);
        setTimeout(() => {
            toast.classList.add('translate-y-4', 'opacity-0');
            setTimeout(() => toast.remove(), 300);
        }, 3000);
    }

    function resetCanvas(canvasId, inputId) {
        if (window._signaturePadInstances && window._signaturePadInstances[canvasId]) {
            window._signaturePadInstances[canvasId].clear();
        } else {
            const canvas = document.getElementById(canvasId);
            if (canvas) {
                const ctx = canvas.getContext('2d');
                ctx.clearRect(0, 0, canvas.width, canvas.height);
            }
        }
        const input = document.getElementById(inputId);
        if (input) {
            input.value = '';
        }
    }

    function validasiSebelumSubmitApl01() {
        const skema = document.getElementById('portal-select-skema');
        if (skema && !skema.value) {
            alert('Silakan pilih Skema Sertifikasi terlebih dahulu!');
            skema.focus();
            return false;
        }
        return confirm('Apakah Anda yakin ingin mengajukan Formulir FR.APL.01 ke Admin LSP untuk diverifikasi?');
    }

    function tutupAlertPenolakan() {
        const el = document.getElementById('alert-penolakan-notif');
        if (el) {
            @if(!empty($pendaftaranTerakhirDitolak))
                try {
                    localStorage.setItem('notif_ditolak_dismissed_{{ $pendaftaranTerakhirDitolak->id }}', '1');
                } catch(e) {}
            @endif
            el.style.transition = 'all 0.5s ease';
            el.style.opacity = '0';
            el.style.transform = 'translateY(-15px)';
            el.style.maxHeight = '0';
            el.style.paddingTop = '0';
            el.style.paddingBottom = '0';
            el.style.marginTop = '0';
            el.style.marginBottom = '0';
            el.style.overflow = 'hidden';
            setTimeout(() => {
                if (el && el.parentNode) el.remove();
            }, 500);
        }
    }

    // Auto initialize on DOM loaded
    document.addEventListener('DOMContentLoaded', function() {
        initSignatureCanvas('canvas-ttd-apl01', 'input-ttd-apl01');
        initSignatureCanvas('canvas-ttd-apl02', 'input-ttd-apl02');
        initSignatureCanvas('canvas-ttd-ak01', 'input-ttd-ak01');

        // Otomatis hilangkan banner notifikasi penolakan setelah 5 detik
        setTimeout(() => {
            tutupAlertPenolakan();
        }, 5000);

        // Smooth scroll ke dokumen yang perlu direvisi jika ada hash di URL
        const hash = window.location.hash;
        if (hash) {
            setTimeout(() => {
                const targetEl = document.querySelector(hash);
                if (targetEl) {
                    targetEl.scrollIntoView({ behavior: 'smooth', block: 'center' });
                    const fileInput = targetEl.querySelector('input[type="file"]');
                    if (fileInput) {
                        fileInput.focus();
                    }
                }
            }, 300);
        }

        // Ketika asesi memilih berkas baru, hilangkan peringatan revisi secara instant di layar
        const fileInputs = document.querySelectorAll('#dokumen-ktp input[type="file"], #dokumen-rapor input[type="file"], #dokumen-pkl input[type="file"], #dokumen-foto input[type="file"]');
        fileInputs.forEach(function(input) {
            input.addEventListener('change', function() {
                if (this.files && this.files.length > 0) {
                    const card = this.closest('[id^="dokumen-"]');
                    if (card) {
                        const alertBox = card.querySelector('div[style*="background: #fef2f2"]');
                        if (alertBox) {
                            alertBox.style.transition = 'all 0.4s ease';
                            alertBox.style.opacity = '0';
                            alertBox.style.maxHeight = '0';
                            alertBox.style.padding = '0';
                            alertBox.style.margin = '0';
                            alertBox.style.overflow = 'hidden';
                            setTimeout(() => { alertBox.style.display = 'none'; }, 400);
                        }
                        const badge = card.querySelector('span[style*="background: #dc2626"]');
                        if (badge) {
                            badge.style.background = '#16a34a';
                            badge.textContent = 'Berkas Baru Dipilih';
                        }
                    }
                }
            });
        });
    });
</script>
@endpush
