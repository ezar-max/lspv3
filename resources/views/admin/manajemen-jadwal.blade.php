@extends('tata-letak.dasbor')

@section('judul', 'Penjadwalan Asesmen')

@push('css')
    <link rel="stylesheet" href="{{ asset('css/admin/dashboard-admin.css') }}">
    <style>
        /* ── Summary Stats Grid ── */
        .jadwal-stats-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(220px, 1fr));
            gap: 1rem;
            margin-bottom: 1.75rem;
        }
        .jadwal-stat-box {
            background: #ffffff;
            border: 1px solid #e2e8f0;
            border-radius: 14px;
            padding: 1.15rem 1.35rem;
            display: flex;
            align-items: center;
            gap: 1rem;
            box-shadow: 0 1px 3px rgba(0, 0, 0, 0.03);
            transition: all 0.2s ease;
        }
        .jadwal-stat-box:hover {
            transform: translateY(-2px);
            box-shadow: 0 6px 18px rgba(0, 0, 0, 0.06);
            border-color: #cbd5e1;
        }
        .jadwal-stat-icon-wrap {
            width: 44px;
            height: 44px;
            border-radius: 12px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.15rem;
            flex-shrink: 0;
        }
        .jadwal-stat-icon-wrap.total   { background: #eff6ff; color: #2563eb; }
        .jadwal-stat-icon-wrap.aktif   { background: #ecfdf5; color: #059669; }
        .jadwal-stat-icon-wrap.jadwal  { background: #fefce8; color: #d97706; }
        .jadwal-stat-icon-wrap.selesai { background: #f8fafc; color: #64748b; }
        .jadwal-stat-num {
            font-size: 1.55rem;
            font-weight: 800;
            color: #0f172a;
            line-height: 1.1;
        }
        .jadwal-stat-desc {
            font-size: 0.78rem;
            color: #64748b;
            font-weight: 500;
            margin-top: 3px;
        }

        /* ── Main Container Card ── */
        .jadwal-main-card {
            background: #ffffff;
            border: 1px solid #e2e8f0;
            border-radius: 16px;
            overflow: hidden;
            box-shadow: 0 2px 6px rgba(0, 0, 0, 0.02);
        }
        .jadwal-card-topbar {
            padding: 1.25rem 1.5rem;
            border-bottom: 1px solid #f1f5f9;
            display: flex;
            align-items: center;
            justify-content: space-between;
            flex-wrap: wrap;
            gap: 0.75rem;
        }
        .jadwal-card-topbar-title {
            font-size: 1rem;
            font-weight: 700;
            color: #0f172a;
            display: flex;
            align-items: center;
            gap: 0.5rem;
            margin: 0;
        }
        .jadwal-card-topbar-title i {
            color: #3b82f6;
        }
        .jadwal-card-counter {
            font-size: 0.8rem;
            color: #64748b;
            background: #f8fafc;
            padding: 0.3rem 0.75rem;
            border-radius: 20px;
            border: 1px solid #e2e8f0;
            font-weight: 500;
        }

        /* ── Refined Table ── */
        .table-responsive-wrapper {
            overflow-x: auto;
            width: 100%;
        }
        .jadwal-table-clean {
            width: 100%;
            border-collapse: collapse;
            text-align: left;
        }
        .jadwal-table-clean thead th {
            background: #f8fafc;
            padding: 0.85rem 1.15rem;
            font-size: 0.72rem;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            color: #64748b;
            border-bottom: 1px solid #e2e8f0;
            white-space: nowrap;
        }
        .jadwal-table-clean tbody tr {
            border-bottom: 1px solid #f1f5f9;
            transition: background 0.15s ease;
        }
        .jadwal-table-clean tbody tr:last-child {
            border-bottom: none;
        }
        .jadwal-table-clean tbody tr:hover {
            background: #f8faff;
        }
        .jadwal-table-clean tbody td {
            padding: 1rem 1.15rem;
            font-size: 0.85rem;
            color: #334155;
            vertical-align: middle;
        }

        /* ── Code Badge ── */
        .badge-kode-jadwal {
            font-family: ui-monospace, SFMono-Regular, Menlo, Monaco, Consolas, monospace;
            font-size: 0.75rem;
            font-weight: 600;
            color: #334155;
            background: #f1f5f9;
            padding: 0.25rem 0.55rem;
            border-radius: 6px;
            border: 1px solid #e2e8f0;
            white-space: nowrap;
        }

        /* ── Skema Column ── */
        .skema-detail-nama {
            font-weight: 700;
            color: #0f172a;
            font-size: 0.85rem;
            line-height: 1.35;
            max-width: 250px;
        }
        .skema-detail-kode {
            font-size: 0.72rem;
            color: #64748b;
            margin-top: 2px;
        }

        /* ── Asesor Column ── */
        .asesor-pill {
            display: flex;
            align-items: center;
            gap: 0.5rem;
        }
        .asesor-pill-avatar {
            width: 32px;
            height: 32px;
            border-radius: 8px;
            background: linear-gradient(135deg, #3b82f6, #6366f1);
            color: #ffffff;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 0.75rem;
            font-weight: 700;
            flex-shrink: 0;
        }
        .asesor-pill-name {
            font-weight: 600;
            color: #1e293b;
            font-size: 0.84rem;
        }

        /* ── Time & Date Column ── */
        .waktu-col-wrapper {
            display: flex;
            flex-direction: column;
            gap: 3px;
        }
        .waktu-tanggal {
            font-weight: 600;
            color: #1e293b;
            font-size: 0.84rem;
            white-space: nowrap;
        }
        .waktu-jam {
            font-size: 0.74rem;
            color: #64748b;
            display: flex;
            align-items: center;
            gap: 4px;
            white-space: nowrap;
        }

        /* ── TUK Location ── */
        .tuk-wrapper {
            display: flex;
            align-items: center;
            gap: 0.4rem;
            color: #334155;
            font-size: 0.84rem;
        }
        .tuk-wrapper i {
            color: #94a3b8;
            font-size: 0.75rem;
        }

        /* ── Kuota Badge ── */
        .badge-kuota {
            display: inline-flex;
            align-items: center;
            gap: 4px;
            background: #f0fdf4;
            color: #166534;
            border: 1px solid #bbf7d0;
            padding: 0.25rem 0.6rem;
            border-radius: 6px;
            font-weight: 700;
            font-size: 0.8rem;
        }
        .badge-kuota span {
            font-weight: 500;
            font-size: 0.72rem;
            color: #15803d;
        }

        /* ── Status Pill ── */
        .status-pill {
            display: inline-flex;
            align-items: center;
            gap: 6px;
            padding: 0.35rem 0.8rem;
            border-radius: 9999px;
            font-size: 0.74rem;
            font-weight: 700;
            white-space: nowrap;
        }
        .status-pill.aktif {
            background: #ecfdf5;
            color: #065f46;
            border: 1px solid #a7f3d0;
        }
        .status-pill.aktif .live-dot {
            width: 7px;
            height: 7px;
            border-radius: 50%;
            background: #10b981;
            box-shadow: 0 0 0 2px rgba(16, 185, 129, 0.25);
            animation: pulse-dot-anim 1.6s infinite ease-in-out;
        }
        .status-pill.terjadwal {
            background: #fefce8;
            color: #854d0e;
            border: 1px solid #fef08a;
        }
        .status-pill.selesai {
            background: #f1f5f9;
            color: #475569;
            border: 1px solid #cbd5e1;
        }
        .status-pill.dibatalkan {
            background: #fef2f2;
            color: #991b1b;
            border: 1px solid #fecaca;
        }

        @keyframes pulse-dot-anim {
            0%, 100% { opacity: 1; transform: scale(1); }
            50% { opacity: 0.4; transform: scale(1.35); }
        }

        /* ── Action Buttons ── */
        .aksi-btn-group {
            display: flex;
            align-items: center;
            gap: 0.4rem;
            justify-content: flex-end;
        }
        .btn-tbl-action {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: 0.35rem;
            padding: 0.45rem 0.75rem;
            border-radius: 8px;
            font-size: 0.78rem;
            font-weight: 600;
            border: 1px solid transparent;
            cursor: pointer;
            transition: all 0.15s ease;
            text-decoration: none;
        }
        .btn-tbl-action.edit {
            background: #eff6ff;
            color: #1d4ed8;
            border-color: #bfdbfe;
        }
        .btn-tbl-action.edit:hover {
            background: #dbeafe;
            border-color: #93c5fd;
        }
        .btn-tbl-action.delete {
            background: #fef2f2;
            color: #b91c1c;
            border-color: #fecaca;
            padding: 0.45rem 0.55rem;
        }
        .btn-tbl-action.delete:hover {
            background: #fee2e2;
            border-color: #fca5a5;
        }

        /* ── Primary Header Button ── */
        .btn-create-header {
            display: inline-flex;
            align-items: center;
            gap: 0.5rem;
            padding: 0.65rem 1.25rem;
            background: #2563eb;
            color: #ffffff;
            border: none;
            border-radius: 10px;
            font-size: 0.85rem;
            font-weight: 700;
            cursor: pointer;
            transition: all 0.2s ease;
            box-shadow: 0 2px 8px rgba(37, 99, 235, 0.25);
            white-space: nowrap;
        }
        .btn-create-header:hover {
            background: #1d4ed8;
            box-shadow: 0 4px 14px rgba(37, 99, 235, 0.35);
            transform: translateY(-1px);
        }

        /* ── Empty State Clean ── */
        .empty-jadwal-wrapper {
            padding: 3.5rem 1.5rem;
            text-align: center;
        }
        .empty-jadwal-icon {
            width: 60px;
            height: 60px;
            border-radius: 16px;
            background: #f1f5f9;
            color: #94a3b8;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            font-size: 1.6rem;
            margin-bottom: 1rem;
        }
        .empty-jadwal-title {
            font-size: 1.05rem;
            font-weight: 700;
            color: #1e293b;
            margin-bottom: 0.35rem;
        }
        .empty-jadwal-subtitle {
            font-size: 0.85rem;
            color: #64748b;
            max-width: 380px;
            margin: 0 auto;
            line-height: 1.5;
        }

        /* ── Modal Design Clean ── */
        .modal-clean-header {
            display: flex;
            align-items: flex-start;
            justify-content: space-between;
            padding-bottom: 1rem;
            margin-bottom: 1.25rem;
            border-bottom: 1px solid #f1f5f9;
        }
        .modal-clean-title {
            font-size: 1.15rem;
            font-weight: 800;
            color: #0f172a;
            margin: 0;
            display: flex;
            align-items: center;
            gap: 0.5rem;
        }
        .modal-clean-title i {
            color: #2563eb;
        }
        .modal-clean-subtitle {
            font-size: 0.82rem;
            color: #64748b;
            margin-top: 0.25rem;
            margin-bottom: 0;
        }
        .modal-clean-close {
            background: #f1f5f9;
            border: none;
            width: 30px;
            height: 30px;
            border-radius: 8px;
            display: flex;
            align-items: center;
            justify-content: center;
            color: #64748b;
            font-size: 1.1rem;
            cursor: pointer;
            transition: all 0.15s ease;
        }
        .modal-clean-close:hover {
            background: #e2e8f0;
            color: #1e293b;
        }
        .modal-step-label {
            font-size: 0.72rem;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: 0.6px;
            color: #64748b;
            margin-bottom: 0.6rem;
            display: flex;
            align-items: center;
            gap: 0.35rem;
        }
        .modal-clean-divider {
            border: none;
            border-top: 1px solid #f1f5f9;
            margin: 1.15rem 0;
        }
        .modal-clean-footer {
            margin-top: 1.5rem;
            padding-top: 1rem;
            border-top: 1px solid #f1f5f9;
            display: flex;
            gap: 0.5rem;
            justify-content: flex-end;
        }

        /* ── Responsive Form Grid & Mobile Modal UI/UX ── */
        .jadwal-form-grid-2 {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 1rem;
        }
        .jadwal-form-grid-3 {
            display: grid;
            grid-template-columns: 1fr 1fr 1fr;
            gap: 1rem;
        }
        @media (max-width: 640px) {
            .modal-overlay {
                padding: 0.75rem !important;
                align-items: center !important;
            }
            .modal-konten {
                padding: 1.25rem 1rem !important;
                max-width: 100% !important;
                width: 100% !important;
                border-radius: 14px !important;
                box-sizing: border-box !important;
                margin: 0 !important;
            }
            .jadwal-form-grid-2,
            .jadwal-form-grid-3 {
                grid-template-columns: 1fr !important;
                gap: 0.85rem !important;
            }
            .modal-clean-header {
                padding-bottom: 0.75rem !important;
                margin-bottom: 1rem !important;
            }
            .modal-clean-title {
                font-size: 1rem !important;
            }
            .modal-clean-subtitle {
                font-size: 0.75rem !important;
            }
            .modal-clean-footer {
                flex-direction: column-reverse !important;
                gap: 0.5rem !important;
            }
            .modal-clean-footer .tombol {
                width: 100% !important;
                justify-content: center !important;
                text-align: center !important;
                padding: 0.65rem 1rem !important;
            }
            .grup-form .input-control {
                width: 100% !important;
                box-sizing: border-box !important;
            }
        }
    </style>
@endpush

@section('konten')
<div class="animasi-slide max-w-7xl mx-auto">

    {{-- ── 1. Page Header (Tunggal & Rapi) ── --}}
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 mb-6">
        <div>
            <h1 class="text-2xl font-extrabold text-slate-900 tracking-tight flex items-center gap-2.5">
                <i class="fa-solid fa-calendar-days text-blue-600"></i>
                Manajemen Penjadwalan Asesmen
            </h1>
            <p class="text-sm text-slate-500 mt-1">
                Atur jadwal uji kompetensi, penugasan Asesor penguji, lokasi TUK, dan kapasitas kuota peserta.
            </p>
        </div>
        <div>
            <button type="button" class="btn-create-header" onclick="bukaModal('modalTambahJadwal')">
                <i class="fa-solid fa-calendar-plus"></i> Buat Jadwal Uji Baru
            </button>
        </div>
    </div>

    {{-- ── 2. Stat Summary Cards ── --}}
    @php
        $totalJadwal = $jadwalList->total();
        $jadwalAktif = $jadwalList->filter(fn($j) => $j->status_jadwal === 'berlangsung')->count();
        $jadwalTerjadwal = $jadwalList->filter(fn($j) => $j->status_jadwal === 'terjadwal')->count();
        $jadwalSelesai = $jadwalList->filter(fn($j) => $j->status_jadwal === 'selesai')->count();
    @endphp
    <div class="jadwal-stats-grid">
        <div class="jadwal-stat-box">
            <div class="jadwal-stat-icon-wrap total">
                <i class="fa-solid fa-layer-group"></i>
            </div>
            <div>
                <div class="jadwal-stat-num">{{ $totalJadwal }}</div>
                <div class="jadwal-stat-desc">Total Jadwal Terdaftar</div>
            </div>
        </div>

        <div class="jadwal-stat-box">
            <div class="jadwal-stat-icon-wrap aktif">
                <i class="fa-solid fa-circle-play"></i>
            </div>
            <div>
                <div class="jadwal-stat-num">{{ $jadwalAktif }}</div>
                <div class="jadwal-stat-desc">Sedang Berlangsung</div>
            </div>
        </div>

        <div class="jadwal-stat-box">
            <div class="jadwal-stat-icon-wrap jadwal">
                <i class="fa-regular fa-clock"></i>
            </div>
            <div>
                <div class="jadwal-stat-num">{{ $jadwalTerjadwal }}</div>
                <div class="jadwal-stat-desc">Terjadwal (Mendatang)</div>
            </div>
        </div>

        <div class="jadwal-stat-box">
            <div class="jadwal-stat-icon-wrap selesai">
                <i class="fa-solid fa-circle-check"></i>
            </div>
            <div>
                <div class="jadwal-stat-num">{{ $jadwalSelesai }}</div>
                <div class="jadwal-stat-desc">Pelaksanaan Selesai</div>
            </div>
        </div>
    </div>

    {{-- ── 3. Main Table Card ── --}}
    <div class="jadwal-main-card">
        <div class="jadwal-card-topbar">
            <h3 class="jadwal-card-topbar-title">
                <i class="fa-solid fa-table-list"></i>
                Daftar Jadwal Uji Kompetensi
            </h3>
            <span class="jadwal-card-counter">
                Menampilkan {{ $jadwalList->count() }} dari {{ $jadwalList->total() }} jadwal
            </span>
        </div>

        <div class="table-responsive-wrapper">
            <table class="jadwal-table-clean">
                <thead>
                    <tr>
                        <th style="padding-left: 1.5rem;">Kode</th>
                        <th>Skema Sertifikasi</th>
                        <th>Asesor Penguji</th>
                        <th>Tanggal & Waktu</th>
                        <th>Lokasi TUK</th>
                        <th style="text-align: center;">Kuota</th>
                        <th style="text-align: center;">Status</th>
                        <th style="text-align: right; padding-right: 1.5rem;">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($jadwalList as $j)
                        <tr>
                            {{-- Kode Jadwal --}}
                            <td style="padding-left: 1.5rem;">
                                <span class="badge-kode-jadwal">{{ $j->kode_jadwal }}</span>
                            </td>

                            {{-- Skema Sertifikasi --}}
                            <td>
                                <div class="skema-detail-nama">{{ $j->skema->nama_skema }}</div>
                                <div class="skema-detail-kode">{{ $j->skema->kode_skema }}</div>
                            </td>

                            {{-- Asesor Penguji --}}
                            <td>
                                <div class="asesor-pill">
                                    <div class="asesor-pill-avatar">
                                        {{ strtoupper(substr($j->asesor->nama_lengkap, 0, 2)) }}
                                    </div>
                                    <span class="asesor-pill-name">{{ $j->asesor->nama_lengkap }}</span>
                                </div>
                            </td>

                            {{-- Tanggal & Waktu --}}
                            <td>
                                <div class="waktu-col-wrapper">
                                    <span class="waktu-tanggal">{{ date('d M Y', strtotime($j->tanggal_uji)) }}</span>
                                    <span class="waktu-jam">
                                        <i class="fa-regular fa-clock"></i>
                                        {{ substr($j->waktu_mulai, 0, 5) }} - {{ substr($j->waktu_selesai, 0, 5) }} WIB
                                    </span>
                                </div>
                            </td>

                            {{-- Lokasi TUK --}}
                            <td>
                                <div class="tuk-wrapper">
                                    <i class="fa-solid fa-location-dot"></i>
                                    <span>{{ $j->nama_tuk }}</span>
                                </div>
                            </td>

                            {{-- Kuota --}}
                            <td style="text-align: center;">
                                <span class="badge-kuota">
                                    {{ $j->kuota }} <span>Asesi</span>
                                </span>
                            </td>

                            {{-- Status --}}
                            <td style="text-align: center;">
                                @if($j->status_jadwal === 'berlangsung')
                                    <span class="status-pill aktif">
                                        <span class="live-dot"></span> Aktif (Berlangsung)
                                    </span>
                                @elseif($j->status_jadwal === 'selesai')
                                    <span class="status-pill selesai">
                                        <i class="fa-solid fa-check" style="font-size: 0.65rem;"></i> Selesai
                                    </span>
                                @elseif($j->status_jadwal === 'dibatalkan')
                                    <span class="status-pill dibatalkan">
                                        <i class="fa-solid fa-ban" style="font-size: 0.65rem;"></i> Dibatalkan
                                    </span>
                                @else
                                    <span class="status-pill terjadwal">
                                        <i class="fa-regular fa-clock" style="font-size: 0.65rem;"></i> Terjadwal
                                    </span>
                                @endif
                            </td>

                            {{-- Aksi --}}
                            <td style="text-align: right; padding-right: 1.5rem;">
                                <div class="aksi-btn-group">
                                    <button type="button"
                                            class="btn-tbl-action edit"
                                            title="Edit Jadwal"
                                            onclick='bukaModalEditJadwal({
                                                id: {{ $j->id }},
                                                kode_jadwal: "{{ addslashes($j->kode_jadwal) }}",
                                                skema_id: {{ $j->skema_id }},
                                                asesor_id: {{ $j->asesor_id }},
                                                nama_tuk: "{{ addslashes($j->nama_tuk) }}",
                                                tanggal_uji: "{{ $j->tanggal_uji }}",
                                                waktu_mulai: "{{ substr($j->waktu_mulai, 0, 5) }}",
                                                waktu_selesai: "{{ substr($j->waktu_selesai, 0, 5) }}",
                                                kuota: {{ $j->kuota }},
                                                status_jadwal: "{{ $j->status_jadwal }}"
                                            })'>
                                        <i class="fa-solid fa-pen-to-square"></i> Edit
                                    </button>

                                    <form action="{{ route('admin.jadwal.hapus', $j->id) }}" method="POST" style="margin: 0;" onsubmit="return confirm('Apakah Anda yakin ingin menghapus jadwal uji #{{ $j->kode_jadwal }}?')">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="btn-tbl-action delete" title="Hapus Jadwal">
                                            <i class="fa-solid fa-trash"></i>
                                        </button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="8">
                                <div class="empty-jadwal-wrapper">
                                    <div class="empty-jadwal-icon">
                                        <i class="fa-solid fa-calendar-xmark"></i>
                                    </div>
                                    <div class="empty-jadwal-title">Belum Ada Jadwal Uji Terdaftar</div>
                                    <div class="empty-jadwal-subtitle">
                                        Belum ada jadwal uji kompetensi yang aktif atau terjadwal. Klik tombol <strong>"Buat Jadwal Uji Baru"</strong> untuk membuat jadwal pertama.
                                    </div>
                                </div>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if($jadwalList->hasPages())
            <div style="padding: 1.25rem 1.5rem; border-top: 1px solid #f1f5f9;">
                {{ $jadwalList->links() }}
            </div>
        @endif
    </div>
</div>

{{-- ══════════════════════════════════════════════════════ --}}
{{-- ── MODAL: BUAT JADWAL BARU ── --}}
{{-- ══════════════════════════════════════════════════════ --}}
<div class="modal-overlay" id="modalTambahJadwal">
    <div class="modal-konten" style="max-width: 620px; width: 100%; box-sizing: border-box;">
        <div class="modal-clean-header">
            <div>
                <h3 class="modal-clean-title">
                    <i class="fa-solid fa-calendar-plus"></i> Buat Jadwal Uji Baru
                </h3>
                <p class="modal-clean-subtitle">Atur skema sertifikasi, penugasan asesor, lokasi TUK, dan jadwal waktu.</p>
            </div>
            <button type="button" class="modal-clean-close" onclick="tutupModal('modalTambahJadwal')">&times;</button>
        </div>

        <form action="{{ route('admin.jadwal.simpan') }}" method="POST">
            @csrf

            <div class="modal-step-label"><i class="fa-solid fa-tag"></i> Identitas Jadwal & Skema</div>
            <div class="jadwal-form-grid-2">
                <div class="grup-form">
                    <label class="label-form">Kode Jadwal</label>
                    <input type="text" name="kode_jadwal" class="input-control" value="JDW-{{ date('Ymd') }}-{{ rand(10,99) }}" required>
                </div>
                <div class="grup-form">
                    <label class="label-form">Pilih Skema</label>
                    <select name="skema_id" id="pilih_skema_jadwal" class="input-control" required onchange="filterAsesorBySkema()">
                        <option value="">-- Pilih Skema Sertifikasi --</option>
                        @foreach($skemaOptions as $s)
                            <option value="{{ $s->id }}">{{ $s->nama_skema }} ({{ $s->kode_skema }})</option>
                        @endforeach
                    </select>
                </div>
            </div>

            <hr class="modal-clean-divider">

            <div class="modal-step-label"><i class="fa-solid fa-user-tie"></i> Penugasan Asesor Penguji</div>
            <div class="grup-form">
                <label class="label-form">Tugaskan Asesor Penguji</label>
                <select name="asesor_id" id="pilih_asesor_jadwal" class="input-control" required>
                    <option value="">-- Pilih Skema Terlebih Dahulu --</option>
                    @foreach($asesorOptions as $a)
                        <option value="{{ $a->id }}" data-skema-id="{{ $a->skema_id ?? '' }}">
                            {{ $a->nama_lengkap }} ({{ $a->nomor_registrasi ? 'Reg: ' . $a->nomor_registrasi : $a->email }})
                        </option>
                    @endforeach
                </select>
                <small id="pesan_asesor_kosong" style="color: #dc2626; font-size: 0.8rem; margin-top: 0.35rem; display: none;">
                    <i class="fa-solid fa-triangle-exclamation"></i> Tidak ada asesor dengan role skema ini. Harap atur role skema asesor di <a href="{{ route('admin.manajemen-asesor') }}" target="_blank" style="color: var(--biru-utama); text-decoration: underline; font-weight: bold;">Manajemen Asesor</a> terlebih dahulu.
                </small>
                <small id="pesan_asesor_info" style="color: #15803d; font-size: 0.8rem; margin-top: 0.35rem; display: none;">
                    <i class="fa-solid fa-circle-check"></i> <span id="text_asesor_count">0</span> asesor ditemukan untuk skema ini.
                </small>
            </div>

            <hr class="modal-clean-divider">

            <div class="modal-step-label"><i class="fa-solid fa-location-dot"></i> Tempat & Waktu Pelaksanaan</div>
            <div class="grup-form">
                <label class="label-form">Tempat Uji Kompetensi (TUK)</label>
                <input type="text" name="nama_tuk" class="input-control" placeholder="contoh: Bengkel Pengelasan / Lab RPL 1" required>
            </div>

            <div class="jadwal-form-grid-3">
                <div class="grup-form">
                    <label class="label-form">Tanggal Uji</label>
                    <input type="date" name="tanggal_uji" class="input-control" min="{{ date('Y-m-d') }}" value="{{ old('tanggal_uji') }}" required>
                </div>
                <div class="grup-form">
                    <label class="label-form">Waktu Mulai</label>
                    <input type="time" name="waktu_mulai" class="input-control" value="08:00" required>
                </div>
                <div class="grup-form">
                    <label class="label-form">Waktu Selesai</label>
                    <input type="time" name="waktu_selesai" class="input-control" value="16:00" required>
                </div>
            </div>

            <hr class="modal-clean-divider">

            <div class="modal-step-label"><i class="fa-solid fa-users"></i> Kapasitas Peserta Asesi</div>
            <div class="grup-form">
                <label class="label-form">Kapasitas Kuota Asesi (Standar: 10 Asesi / Asesor / Hari)</label>
                <input type="number" name="kuota" class="input-control" value="10" min="1" max="50" required>
                <small style="color: var(--abu-teks); font-size: 0.8rem; margin-top: 0.25rem; display: block;">
                    * Disarankan 10 asesi per sesi/hari per asesor penguji sesuai standar beban kerja asesmen BNSP.
                </small>
            </div>

            <div class="modal-clean-footer">
                <button type="button" class="tombol tombol-sekunder" onclick="tutupModal('modalTambahJadwal')">Batal</button>
                <button type="submit" class="tombol tombol-utama">
                    <i class="fa-solid fa-calendar-check"></i> Simpan Jadwal
                </button>
            </div>
        </form>
    </div>
</div>

{{-- ══════════════════════════════════════════════════════ --}}
{{-- ── MODAL: EDIT JADWAL ── --}}
{{-- ══════════════════════════════════════════════════════ --}}
<div class="modal-overlay" id="modalEditJadwal">
    <div class="modal-konten" style="max-width: 620px; width: 100%; box-sizing: border-box;">
        <div class="modal-clean-header">
            <div>
                <h3 class="modal-clean-title">
                    <i class="fa-solid fa-calendar-check"></i> Edit Jadwal Asesmen
                </h3>
                <p class="modal-clean-subtitle">Perbarui data sesi uji kompetensi, penugasan asesor, dan status pelaksanaan.</p>
            </div>
            <button type="button" class="modal-clean-close" onclick="tutupModal('modalEditJadwal')">&times;</button>
        </div>

        <form id="formEditJadwal" method="POST" action="">
            @csrf
            <div class="modal-step-label"><i class="fa-solid fa-tag"></i> Identitas & Status Jadwal</div>
            <div class="jadwal-form-grid-2">
                <div class="grup-form">
                    <label class="label-form">Kode Jadwal</label>
                    <input type="text" name="kode_jadwal" id="edit_kode_jadwal" class="input-control" required>
                </div>
                <div class="grup-form">
                    <label class="label-form">Status Pelaksanaan</label>
                    <select name="status_jadwal" id="edit_status_jadwal" class="input-control" required>
                        <option value="terjadwal">Terjadwal (Belum Mulai)</option>
                        <option value="berlangsung">Aktif (Sedang Berlangsung)</option>
                        <option value="selesai">Selesai</option>
                        <option value="dibatalkan">Dibatalkan</option>
                    </select>
                </div>
            </div>

            <hr class="modal-clean-divider">

            <div class="modal-step-label"><i class="fa-solid fa-user-tie"></i> Skema & Penugasan Asesor</div>
            <div class="grup-form">
                <label class="label-form">Pilih Skema Sertifikasi</label>
                <select name="skema_id" id="edit_skema_id" class="input-control" required onchange="filterEditAsesorBySkema()">
                    <option value="">-- Pilih Skema Sertifikasi --</option>
                    @foreach($skemaOptions as $s)
                        <option value="{{ $s->id }}">{{ $s->nama_skema }} ({{ $s->kode_skema }})</option>
                    @endforeach
                </select>
            </div>

            <div class="grup-form">
                <label class="label-form">Tugaskan Asesor Penguji</label>
                <select name="asesor_id" id="edit_asesor_id" class="input-control" required>
                    <option value="">-- Pilih Skema Terlebih Dahulu --</option>
                    @foreach($asesorOptions as $a)
                        <option value="{{ $a->id }}" data-skema-id="{{ $a->skema_id ?? '' }}">
                            {{ $a->nama_lengkap }} ({{ $a->nomor_registrasi ? 'Reg: ' . $a->nomor_registrasi : $a->email }})
                        </option>
                    @endforeach
                </select>
                <small id="edit_pesan_asesor_kosong" style="color: #dc2626; font-size: 0.8rem; margin-top: 0.35rem; display: none;">
                    <i class="fa-solid fa-triangle-exclamation"></i> Tidak ada asesor dengan role skema ini. Harap atur role skema asesor di <a href="{{ route('admin.manajemen-asesor') }}" target="_blank" style="color: var(--biru-utama); text-decoration: underline; font-weight: bold;">Manajemen Asesor</a> terlebih dahulu.
                </small>
                <small id="edit_pesan_asesor_info" style="color: #15803d; font-size: 0.8rem; margin-top: 0.35rem; display: none;">
                    <i class="fa-solid fa-circle-check"></i> <span id="edit_text_asesor_count">0</span> asesor ditemukan untuk skema ini.
                </small>
            </div>

            <hr class="modal-clean-divider">

            <div class="modal-step-label"><i class="fa-solid fa-location-dot"></i> Tempat & Waktu Pelaksanaan</div>
            <div class="grup-form">
                <label class="label-form">Tempat Uji Kompetensi (TUK)</label>
                <input type="text" name="nama_tuk" id="edit_nama_tuk" class="input-control" required placeholder="contoh: Bengkel Pengelasan / Lab RPL 1">
            </div>

            <div class="jadwal-form-grid-3">
                <div class="grup-form">
                    <label class="label-form">Tanggal Uji</label>
                    <input type="date" name="tanggal_uji" id="edit_tanggal_uji" class="input-control" required>
                </div>
                <div class="grup-form">
                    <label class="label-form">Waktu Mulai</label>
                    <input type="time" name="waktu_mulai" id="edit_waktu_mulai" class="input-control" required>
                </div>
                <div class="grup-form">
                    <label class="label-form">Waktu Selesai</label>
                    <input type="time" name="waktu_selesai" id="edit_waktu_selesai" class="input-control" required>
                </div>
            </div>

            <hr class="modal-clean-divider">

            <div class="modal-step-label"><i class="fa-solid fa-users"></i> Kapasitas Peserta Asesi</div>
            <div class="grup-form">
                <label class="label-form">Kapasitas Kuota Asesi (Standar: 10 Asesi / Asesor / Hari)</label>
                <input type="number" name="kuota" id="edit_kuota" class="input-control" value="10" min="1" max="50" required>
                <small style="color: var(--abu-teks); font-size: 0.8rem; margin-top: 0.25rem; display: block;">
                    * Disarankan 10 asesi per sesi/hari per asesor penguji sesuai standar beban kerja asesmen BNSP.
                </small>
            </div>

            <div class="modal-clean-footer">
                <button type="button" class="tombol tombol-sekunder" onclick="tutupModal('modalEditJadwal')">Batal</button>
                <button type="submit" class="tombol tombol-utama">
                    <i class="fa-solid fa-floppy-disk"></i> Simpan Perubahan
                </button>
            </div>
        </form>
    </div>
</div>
@endsection

@push('js')
<script>
    document.addEventListener('DOMContentLoaded', function() {
        var selectSkema = document.getElementById('pilih_skema_jadwal');
        var selectAsesor = document.getElementById('pilih_asesor_jadwal');
        var pesanKosong = document.getElementById('pesan_asesor_kosong');
        var pesanInfo = document.getElementById('pesan_asesor_info');
        var textAsesorCount = document.getElementById('text_asesor_count');

        // Modal Edit Elems
        var editSelectSkema = document.getElementById('edit_skema_id');
        var editSelectAsesor = document.getElementById('edit_asesor_id');
        var editPesanKosong = document.getElementById('edit_pesan_asesor_kosong');
        var editPesanInfo = document.getElementById('edit_pesan_asesor_info');
        var editTextAsesorCount = document.getElementById('edit_text_asesor_count');

        // Ambil dan simpan seluruh data opsi asesor dari DOM
        var daftarAsesor = [];
        var srcSelect = selectAsesor || editSelectAsesor;
        if (srcSelect) {
            for (var i = 0; i < srcSelect.options.length; i++) {
                var opt = srcSelect.options[i];
                if (opt.value) {
                    daftarAsesor.push({
                        value: opt.value,
                        text: opt.text,
                        skemaId: opt.getAttribute('data-skema-id') || ''
                    });
                }
            }
        }

        // Handler Filter Asesor Modal Tambah
        window.filterAsesorBySkema = function() {
            if (!selectSkema || !selectAsesor) return;
            var skemaDipilih = selectSkema.value;
            selectAsesor.innerHTML = '';

            if (!skemaDipilih) {
                var defaultOpt = document.createElement('option');
                defaultOpt.value = '';
                defaultOpt.text = '-- Pilih Skema Terlebih Dahulu --';
                selectAsesor.appendChild(defaultOpt);
                if (pesanKosong) pesanKosong.style.display = 'none';
                if (pesanInfo) pesanInfo.style.display = 'none';
                return;
            }

            var asesorTersaring = daftarAsesor.filter(function(a) {
                return String(a.skemaId) === String(skemaDipilih);
            });

            if (asesorTersaring.length === 0) {
                var kosongOpt = document.createElement('option');
                kosongOpt.value = '';
                kosongOpt.text = '-- Tidak ada asesor dengan role skema ini --';
                selectAsesor.appendChild(kosongOpt);

                if (pesanKosong) pesanKosong.style.display = 'block';
                if (pesanInfo) pesanInfo.style.display = 'none';
            } else {
                var pilihOpt = document.createElement('option');
                pilihOpt.value = '';
                pilihOpt.text = '-- Pilih Asesor Penguji (' + asesorTersaring.length + ' Asesor Tersedia) --';
                selectAsesor.appendChild(pilihOpt);

                asesorTersaring.forEach(function(a) {
                    var opt = document.createElement('option');
                    opt.value = a.value;
                    opt.text = a.text;
                    selectAsesor.appendChild(opt);
                });

                if (pesanKosong) pesanKosong.style.display = 'none';
                if (pesanInfo && textAsesorCount) {
                    textAsesorCount.textContent = asesorTersaring.length;
                    pesanInfo.style.display = 'block';
                }
            }
        };

        // Handler Filter Asesor Modal Edit
        window.filterEditAsesorBySkema = function(selectedAsesorId = null) {
            if (!editSelectSkema || !editSelectAsesor) return;
            var skemaDipilih = editSelectSkema.value;
            editSelectAsesor.innerHTML = '';

            if (!skemaDipilih) {
                var defaultOpt = document.createElement('option');
                defaultOpt.value = '';
                defaultOpt.text = '-- Pilih Skema Terlebih Dahulu --';
                editSelectAsesor.appendChild(defaultOpt);
                if (editPesanKosong) editPesanKosong.style.display = 'none';
                if (editPesanInfo) editPesanInfo.style.display = 'none';
                return;
            }

            var asesorTersaring = daftarAsesor.filter(function(a) {
                return String(a.skemaId) === String(skemaDipilih);
            });

            if (asesorTersaring.length === 0) {
                var kosongOpt = document.createElement('option');
                kosongOpt.value = '';
                kosongOpt.text = '-- Tidak ada asesor dengan role skema ini --';
                editSelectAsesor.appendChild(kosongOpt);

                if (editPesanKosong) editPesanKosong.style.display = 'block';
                if (editPesanInfo) editPesanInfo.style.display = 'none';
            } else {
                var pilihOpt = document.createElement('option');
                pilihOpt.value = '';
                pilihOpt.text = '-- Pilih Asesor Penguji (' + asesorTersaring.length + ' Asesor Tersedia) --';
                editSelectAsesor.appendChild(pilihOpt);

                asesorTersaring.forEach(function(a) {
                    var opt = document.createElement('option');
                    opt.value = a.value;
                    opt.text = a.text;
                    if (selectedAsesorId && String(a.value) === String(selectedAsesorId)) {
                        opt.selected = true;
                    }
                    editSelectAsesor.appendChild(opt);
                });

                if (editPesanKosong) editPesanKosong.style.display = 'none';
                if (editPesanInfo && editTextAsesorCount) {
                    editTextAsesorCount.textContent = asesorTersaring.length;
                    editPesanInfo.style.display = 'block';
                }
            }
        };

        // Handler Buka Modal Edit Jadwal dengan data yang dipilih
        window.bukaModalEditJadwal = function(data) {
            var form = document.getElementById('formEditJadwal');
            if (!form) return;
            form.action = "{{ url('admin/jadwal') }}/" + data.id + "/ubah";

            document.getElementById('edit_kode_jadwal').value = data.kode_jadwal || '';
            document.getElementById('edit_status_jadwal').value = data.status_jadwal || 'terjadwal';
            document.getElementById('edit_nama_tuk').value = data.nama_tuk || '';
            document.getElementById('edit_tanggal_uji').value = data.tanggal_uji || '';
            document.getElementById('edit_waktu_mulai').value = data.waktu_mulai || '08:00';
            document.getElementById('edit_waktu_selesai').value = data.waktu_selesai || '16:00';
            document.getElementById('edit_kuota').value = data.kuota || 10;

            if (editSelectSkema) {
                editSelectSkema.value = data.skema_id || '';
                window.filterEditAsesorBySkema(data.asesor_id);
            }

            bukaModal('modalEditJadwal');
        };

        // Inisialisasi awal saat load
        filterAsesorBySkema();
    });
</script>
@endpush
