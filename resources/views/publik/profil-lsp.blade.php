@extends('tata-letak.publik')

@section('judul', 'Profil Lembaga Sertifikasi Profesi - BNSP')

@push('css')
<style>
    /* BNSP Official Profile Page Styling */
    .bnsp-profile-container {
        max-width: 1200px;
        margin: 2.5rem auto 4rem auto;
        padding: 0 1.5rem;
    }

    .bnsp-top-banner {
        background: linear-gradient(135deg, #1e3a8a 0%, #0284c7 100%);
        border-radius: var(--radius-lg);
        padding: 2.5rem 2rem;
        color: #ffffff;
        margin-bottom: 2.5rem;
        position: relative;
        overflow: hidden;
        box-shadow: 0 10px 25px -5px rgba(2, 132, 199, 0.25);
    }

    .bnsp-top-banner::after {
        content: '';
        position: absolute;
        right: -30px;
        bottom: -30px;
        width: 180px;
        height: 180px;
        background: rgba(255, 255, 255, 0.08);
        border-radius: 50%;
        pointer-events: none;
    }

    .bnsp-layout-grid {
        display: grid;
        grid-template-columns: 300px 1fr;
        gap: 2.5rem;
        align-items: start;
    }

    @media (max-width: 900px) {
        .bnsp-layout-grid {
            grid-template-columns: 1fr;
        }
    }

    /* ASIDE OVERVIEW BOX */
    .bnsp-aside-card {
        background: #ffffff;
        border: 1px solid #e2e8f0;
        border-radius: var(--radius-lg);
        padding: 1.75rem;
        box-shadow: 0 4px 15px rgba(0,0,0,0.03);
        text-align: center;
    }

    .bnsp-logo-frame {
        width: 160px;
        height: 160px;
        border-radius: 16px;
        background: #ffffff;
        border: 2px solid #e2e8f0;
        padding: 10px;
        margin: 0 auto 1.5rem auto;
        display: flex;
        align-items: center;
        justify-content: center;
        box-shadow: 0 4px 10px rgba(0,0,0,0.04);
    }

    .bnsp-logo-frame img {
        max-width: 100%;
        max-height: 100%;
        object-fit: contain;
    }

    .bnsp-overview-section {
        margin-top: 1.5rem;
        text-align: left;
    }

    .bnsp-overview-title {
        font-size: 1.05rem;
        font-weight: 800;
        color: #0f172a;
        margin-bottom: 0.75rem;
        border-bottom: 2px solid #e2e8f0;
        padding-bottom: 0.5rem;
    }

    .bnsp-stat-item {
        display: flex;
        justify-content: space-between;
        align-items: center;
        background: #f8fafc;
        border: 1px solid #e2e8f0;
        border-radius: 8px;
        padding: 0.65rem 1rem;
        margin-bottom: 0.5rem;
        font-size: 0.88rem;
    }

    .bnsp-stat-item .stat-name {
        font-weight: 700;
        color: #334155;
    }

    .bnsp-stat-item .stat-val {
        font-weight: 800;
        color: #2563eb;
        background: #eff6ff;
        padding: 0.15rem 0.6rem;
        border-radius: 6px;
        font-size: 0.95rem;
    }

    /* RIGHT MAIN CONTENT */
    .bnsp-main-card {
        background: #ffffff;
        border: 1px solid #e2e8f0;
        border-radius: var(--radius-lg);
        padding: 2.25rem 2.5rem;
        box-shadow: 0 4px 15px rgba(0,0,0,0.03);
    }

    .bnsp-lsp-title {
        font-size: 1.85rem;
        font-weight: 800;
        color: #0f172a;
        margin-bottom: 1.25rem;
        line-height: 1.25;
    }

    .bnsp-section-heading {
        font-size: 1.15rem;
        font-weight: 800;
        color: #1e293b;
        margin-bottom: 1rem;
        display: flex;
        align-items: center;
        gap: 0.5rem;
    }

    /* OFFICIAL TABLE FORMAT (BNSP STYLE) */
    .tabel-profil-bnsp {
        width: 100%;
        border-collapse: collapse;
        margin-bottom: 2rem;
    }

    .tabel-profil-bnsp tr {
        border-bottom: 1px solid #f1f5f9;
    }

    .tabel-profil-bnsp tr:last-child {
        border-bottom: none;
    }

    .tabel-profil-bnsp td {
        padding: 0.75rem 0.5rem;
        font-size: 0.92rem;
        vertical-align: top;
    }

    .tabel-profil-bnsp td.label-col {
        width: 220px;
        font-weight: 700;
        color: #334155;
    }

    .tabel-profil-bnsp td.colon-col {
        width: 15px;
        color: #64748b;
        text-align: center;
    }

    .tabel-profil-bnsp td.value-col {
        color: #0f172a;
        font-weight: 500;
    }

    /* TABS SECTION */
    .bnsp-tabs-nav {
        display: flex;
        border-bottom: 2px solid #e2e8f0;
        gap: 0.5rem;
        margin-top: 2.5rem;
        flex-wrap: wrap;
    }

    .bnsp-tab-btn {
        padding: 0.85rem 1.5rem;
        font-weight: 800;
        font-size: 0.9rem;
        color: #64748b;
        background: none;
        border: none;
        border-bottom: 3px solid transparent;
        cursor: pointer;
        transition: all 0.2s ease;
        text-transform: uppercase;
        letter-spacing: 0.5px;
    }

    .bnsp-tab-btn:hover {
        color: #2563eb;
    }

    .bnsp-tab-btn.aktif {
        color: #2563eb;
        border-bottom-color: #2563eb;
        background: #eff6ff;
        border-radius: 8px 8px 0 0;
    }

    .bnsp-tab-pane {
        display: none;
        padding-top: 1.5rem;
    }

    .bnsp-tab-pane.aktif {
        display: block;
    }

    /* TAB CONTENT TABLE */
    .tabel-data-bnsp {
        width: 100%;
        border-collapse: collapse;
        font-size: 0.88rem;
    }

    .tabel-data-bnsp th {
        background: #f8fafc;
        color: #334155;
        font-weight: 800;
        padding: 0.85rem 1rem;
        border: 1px solid #e2e8f0;
        text-align: left;
    }

    .tabel-data-bnsp td {
        padding: 0.85rem 1rem;
        border: 1px solid #e2e8f0;
        color: #0f172a;
    }

    .tabel-data-bnsp tr:hover {
        background: #f8fafc;
    }

    .empty-state-bnsp {
        text-align: center;
        padding: 3rem 1.5rem;
        background: #f8fafc;
        border: 1.5px dashed #cbd5e1;
        border-radius: 12px;
        color: #64748b;
    }

    .empty-state-bnsp i {
        font-size: 2.5rem;
        color: #94a3b8;
        margin-bottom: 0.75rem;
    }
</style>
@endpush

@section('konten')
<div class="bnsp-profile-container animasi-slide">
    
    <!-- HEADER BANNER -->
    <div class="bnsp-top-banner">
        <div style="display: flex; align-items: center; gap: 0.6rem; margin-bottom: 0.5rem; font-size: 0.85rem; font-weight: 700; opacity: 0.9;">
            <i class="fa-solid fa-building-columns"></i> BADAN NASIONAL SERTIFIKASI PROFESI (BNSP)
        </div>
        <h1 style="font-size: clamp(1.8rem, 3vw, 2.3rem); font-weight: 800; margin: 0 0 0.5rem 0; color: #ffffff;">
            Profil Lembaga Sertifikasi Profesi (LSP)
        </h1>
        <p style="font-size: 0.95rem; opacity: 0.9; margin: 0; max-width: 680px;">
            Informasi resmi registrasi, lisensi, Tempat Uji Kompetensi (TUK), skema sertifikasi, dan asesor kompetensi berlisensi BNSP.
        </p>
    </div>

    <!-- MAIN TWO COLUMN LAYOUT (BNSP STANDARD FORMAT) -->
    <div class="bnsp-layout-grid">
        
        <!-- LEFT COLUMN: LOGO & DATA OVERVIEW WIDGET -->
        <aside>
            <div class="bnsp-aside-card">
                <div class="bnsp-logo-frame">
                    <img src="{{ asset('images/logo-lsp.jpeg') }}" alt="Logo LSP SMKN 1 Gunungputri">
                </div>

                <div class="bnsp-overview-section">
                    <div class="bnsp-overview-title">
                        <i class="fa-solid fa-chart-pie mr-1" style="color: #2563eb;"></i> Data Overview
                    </div>
                    <div class="bnsp-stat-item">
                        <span class="stat-name">TUK</span>
                        <span class="stat-val">{{ $statistik['total_tuk'] ?? 0 }}</span>
                    </div>
                    <div class="bnsp-stat-item">
                        <span class="stat-name">SKEMA</span>
                        <span class="stat-val">{{ $statistik['total_skema'] ?? 0 }}</span>
                    </div>
                    <div class="bnsp-stat-item">
                        <span class="stat-name">ASSESOR</span>
                        <span class="stat-val">{{ $statistik['total_asesor'] ?? 0 }}</span>
                    </div>
                </div>

                <div style="margin-top: 1.5rem; border-top: 1px solid #e2e8f0; padding-top: 1.25rem;">
                    <a href="{{ config('lsp.url_cek_lisensi', 'https://bnsp.go.id/lsp/smkn-1-gunungputri') }}" target="_blank" rel="noopener noreferrer" class="tombol tombol-outline tombol-sm" style="width: 100%; display: inline-flex; align-items: center; justify-content: center; gap: 0.4rem; font-size: 0.8rem; font-weight: 700; color: #2563eb; border-color: #93c5fd; background: #eff6ff;">
                        <span>Cek di Portal BNSP</span>
                        <i class="fa-solid fa-arrow-up-right-from-square" style="font-size: 0.7rem;"></i>
                    </a>
                </div>
            </div>
        </aside>

        <!-- RIGHT COLUMN: PROFILE DETAILS -->
        <main>
            <div class="bnsp-main-card">
                <h2 class="bnsp-lsp-title">
                    {{ $pengaturan->nama_lsp ?? 'SMKN 1 Gunungputri' }}
                </h2>

                <article>
                    <div class="bnsp-section-heading">
                        <i class="fa-solid fa-id-card" style="color: #2563eb;"></i> Profile LSP
                    </div>

                    <table class="tabel-profil-bnsp">
                        <tr>
                            <td class="label-col">No. SK Lisensi</td>
                            <td class="colon-col">:</td>
                            <td class="value-col font-mono"><strong>{{ $pengaturan->no_sk_lisensi ?? 'KEP.1215/BNSP/V/2025' }}</strong></td>
                        </tr>
                        <tr>
                            <td class="label-col">No Lisensi</td>
                            <td class="colon-col">:</td>
                            <td class="value-col font-mono" style="color: #2563eb;"><strong>{{ $pengaturan->nomor_lisensi ?? 'BNSP-LSP-2629-ID' }}</strong></td>
                        </tr>
                        <tr>
                            <td class="label-col">Jenis</td>
                            <td class="colon-col">:</td>
                            <td class="value-col">LSP Pihak Kesatu</td>
                        </tr>
                        <tr>
                            <td class="label-col">No Telp</td>
                            <td class="colon-col">:</td>
                            <td class="value-col">{{ $pengaturan->nomor_telepon ?? '081283854572' }}</td>
                        </tr>
                        <tr>
                            <td class="label-col">No Hp</td>
                            <td class="colon-col">:</td>
                            <td class="value-col">081283854572</td>
                        </tr>
                        <tr>
                            <td class="label-col">No Fax</td>
                            <td class="colon-col">:</td>
                            <td class="value-col">-</td>
                        </tr>
                        <tr>
                            <td class="label-col">Email</td>
                            <td class="colon-col">:</td>
                            <td class="value-col">
                                <a href="mailto:{{ $pengaturan->email_resmi ?? 'lsp.smkn1gnputri@gmail.com' }}" style="color: #2563eb; text-decoration: none; font-weight: 600;">
                                    {{ $pengaturan->email_resmi ?? 'lsp.smkn1gnputri@gmail.com' }}
                                </a>
                            </td>
                        </tr>
                        <tr>
                            <td class="label-col">Website</td>
                            <td class="colon-col">:</td>
                            <td class="value-col">
                                <a href="https://smkn1gunungputri.sch.id" target="_blank" rel="noopener noreferrer" style="color: #2563eb; text-decoration: none; font-weight: 600;">
                                    https://smkn1gunungputri.sch.id
                                </a>
                            </td>
                        </tr>
                        <tr>
                            <td class="label-col">Masa Berlaku Sertifikat</td>
                            <td class="colon-col">:</td>
                            <td class="value-col">{{ $pengaturan->masa_berlaku ?? '2030-05-23' }}</td>
                        </tr>
                        <tr>
                            <td class="label-col">Status Lisensi</td>
                            <td class="colon-col">:</td>
                            <td class="value-col">
                                <span class="lencana lencana-hijau" style="font-size: 0.78rem; font-weight: 700; padding: 0.25rem 0.65rem;">
                                    <i class="fa-solid fa-circle-check mr-1"></i> Aktif
                                </span>
                            </td>
                        </tr>
                    </table>
                </article>

                <article>
                    <div class="bnsp-section-heading">
                        <i class="fa-solid fa-location-dot" style="color: #2563eb;"></i> Alamat
                    </div>
                    <p style="color: #475569; font-size: 0.95rem; line-height: 1.6; margin: 0;">
                        {{ $pengaturan->alamat_lengkap ?? 'Jl. Barokah No. 6, Desa Wanaherang Kec. Gunungputri, Kab. Bogor, Jawa Barat' }}
                    </p>
                </article>
            </div>
        </main>
    </div>

    <!-- BOTTOM TABS SECTION: DATA SKEMA, DATA TUK, DATA ASESOR -->
    <div style="margin-top: 2.5rem; background: #ffffff; border: 1px solid #e2e8f0; border-radius: var(--radius-lg); padding: 1.5rem 2rem; box-shadow: 0 4px 15px rgba(0,0,0,0.03);">
        
        <!-- TABS NAV -->
        <div class="bnsp-tabs-nav">
            <button type="button" class="bnsp-tab-btn aktif" onclick="gantiTabBnsp('skema', this)">
                <i class="fa-solid fa-list-check mr-1"></i> DATA SKEMA
            </button>
            <button type="button" class="bnsp-tab-btn" onclick="gantiTabBnsp('tuk', this)">
                <i class="fa-solid fa-building mr-1"></i> DATA TUK
            </button>
            <button type="button" class="bnsp-tab-btn" onclick="gantiTabBnsp('asesor', this)">
                <i class="fa-solid fa-user-tie mr-1"></i> DATA ASESOR
            </button>
        </div>

        <!-- TAB 1: DATA SKEMA (DIAMBIL DARI DATABASE) -->
        <div id="tab-skema" class="bnsp-tab-pane aktif">
            @if(isset($skemaList) && $skemaList->count() > 0)
                <div style="overflow-x: auto;">
                    <table class="tabel-data-bnsp">
                        <thead>
                            <tr>
                                <th style="width: 70px; text-align: center;">Nomor</th>
                                <th style="width: 160px;">Kode Skema</th>
                                <th>Skema Sertifikasi</th>
                                <th style="width: 120px; text-align: center;">Unit</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($skemaList as $index => $skema)
                                <tr>
                                    <td style="text-align: center; font-weight: 700;">{{ $index + 1 }}</td>
                                    <td style="font-family: monospace; font-weight: 700; color: #2563eb;">{{ $skema->kode_skema }}</td>
                                    <td>
                                        <strong style="color: #0f172a;">{{ $skema->nama_skema }}</strong>
                                        <div style="font-size: 0.78rem; color: #64748b;">{{ $skema->kategori }}</div>
                                    </td>
                                    <td style="text-align: center;">
                                        <span class="lencana lencana-biru" style="font-size: 0.8rem; font-weight: 800;">
                                            {{ $skema->unit_kompetensi_count ?? $skema->unitKompetensi()->count() }}
                                        </span>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @else
                <div class="empty-state-bnsp">
                    <i class="fa-solid fa-folder-open"></i>
                    <h4 style="color: #334155; font-weight: 700; margin-bottom: 0.35rem;">Belum Ada Data Skema</h4>
                    <p style="font-size: 0.88rem; margin: 0;">Data skema sertifikasi saat ini masih kosong dalam database sistem.</p>
                </div>
            @endif
        </div>

        <!-- TAB 2: DATA TUK (DIAMBIL DARI DATABASE) -->
        <div id="tab-tuk" class="bnsp-tab-pane">
            @if(isset($tukList) && $tukList->count() > 0)
                <div style="overflow-x: auto;">
                    <table class="tabel-data-bnsp">
                        <thead>
                            <tr>
                                <th style="width: 70px; text-align: center;">Nomor</th>
                                <th>Nama Tempat Uji Kompetensi (TUK)</th>
                                <th style="width: 150px;">Jenis TUK</th>
                                <th>Alamat / Kampus</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($tukList as $index => $tuk)
                                <tr>
                                    <td style="text-align: center; font-weight: 700;">{{ $index + 1 }}</td>
                                    <td style="font-weight: 700; color: #0f172a;">{{ $tuk->nama_tuk }}</td>
                                    <td><span class="lencana lencana-biru">Sewaktu</span></td>
                                    <td>{{ $pengaturan->alamat_lengkap }}</td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @else
                <div class="empty-state-bnsp">
                    <i class="fa-solid fa-building-circle-check"></i>
                    <h4 style="color: #334155; font-weight: 700; margin-bottom: 0.35rem;">Belum Ada Data TUK</h4>
                    <p style="font-size: 0.88rem; margin: 0;">Data Tempat Uji Kompetensi (TUK) saat ini masih kosong dalam database sistem.</p>
                </div>
            @endif
        </div>

        <!-- TAB 3: DATA ASESOR (DIAMBIL DARI DATABASE) -->
        <div id="tab-asesor" class="bnsp-tab-pane">
            @if(isset($asesorList) && $asesorList->count() > 0)
                <div style="overflow-x: auto;">
                    <table class="tabel-data-bnsp">
                        <thead>
                            <tr>
                                <th style="width: 70px; text-align: center;">Nomor</th>
                                <th>Nama Asesor Kompetensi</th>
                                <th style="width: 220px;">No. Registrasi (MET)</th>
                                <th style="width: 140px; text-align: center;">Status</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($asesorList as $index => $asesor)
                                <tr>
                                    <td style="text-align: center; font-weight: 700;">{{ $index + 1 }}</td>
                                    <td>
                                        <strong style="color: #0f172a;">{{ $asesor->nama_lengkap }}</strong>
                                        <div style="font-size: 0.78rem; color: #64748b;">{{ $asesor->email }}</div>
                                    </td>
                                    <td style="font-family: monospace; font-weight: 700; color: #2563eb;">
                                        {{ $asesor->nomor_registrasi ?? '-' }}
                                    </td>
                                    <td style="text-align: center;">
                                        <span class="lencana lencana-hijau" style="font-size: 0.78rem;">
                                            <i class="fa-solid fa-check mr-1"></i> Aktif
                                        </span>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @else
                <div class="empty-state-bnsp">
                    <i class="fa-solid fa-user-slash"></i>
                    <h4 style="color: #334155; font-weight: 700; margin-bottom: 0.35rem;">Belum Ada Data Asesor</h4>
                    <p style="font-size: 0.88rem; margin: 0;">Data asesor kompetensi saat ini masih kosong dalam database sistem.</p>
                </div>
            @endif
        </div>

    </div>

</div>
@endsection

@push('js')
<script>
    function gantiTabBnsp(tabName, btnElement) {
        document.querySelectorAll('.bnsp-tab-pane').forEach(function(pane) {
            pane.classList.remove('aktif');
        });
        document.querySelectorAll('.bnsp-tab-btn').forEach(function(btn) {
            btn.classList.remove('aktif');
        });

        const targetPane = document.getElementById('tab-' + tabName);
        if (targetPane) {
            targetPane.classList.add('aktif');
        }
        if (btnElement) {
            btnElement.classList.add('aktif');
        }
    }
</script>
@endpush
