@extends('tata-letak.dasbor')

@section('judul', 'Penjadwalan Asesmen')

@push('css')
    <link rel="stylesheet" href="{{ asset('css/admin/dashboard-admin.css') }}">
    <style>
        /* ── Stat Summary Cards ── */
        .jadwal-stats {
            display: grid;
            grid-template-columns: repeat(4, 1fr);
            gap: 1rem;
            margin-bottom: 1.75rem;
        }
        .jadwal-stat-card {
            background: #fff;
            border-radius: 14px;
            padding: 1.25rem 1.5rem;
            display: flex;
            align-items: center;
            gap: 1rem;
            border: 1px solid #e5e7eb;
            transition: transform 0.2s ease, box-shadow 0.2s ease;
        }
        .jadwal-stat-card:hover {
            transform: translateY(-2px);
            box-shadow: 0 8px 25px rgba(0,0,0,0.06);
        }
        .jadwal-stat-icon {
            width: 48px;
            height: 48px;
            border-radius: 12px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.2rem;
            flex-shrink: 0;
        }
        .jadwal-stat-icon.total   { background: #eff6ff; color: #3b82f6; }
        .jadwal-stat-icon.aktif   { background: #ecfdf5; color: #10b981; }
        .jadwal-stat-icon.jadwal  { background: #fefce8; color: #eab308; }
        .jadwal-stat-icon.selesai { background: #f1f5f9; color: #64748b; }
        .jadwal-stat-angka {
            font-size: 1.65rem;
            font-weight: 800;
            color: var(--biru-malam);
            line-height: 1.1;
        }
        .jadwal-stat-label {
            font-size: 0.8rem;
            color: #94a3b8;
            font-weight: 500;
            margin-top: 2px;
        }

        /* ── Table Card Container ── */
        .jadwal-table-card {
            background: #fff;
            border-radius: 16px;
            border: 1px solid #e5e7eb;
            overflow: hidden;
        }
        .jadwal-table-header {
            padding: 1.25rem 1.75rem;
            border-bottom: 1px solid #f1f5f9;
            display: flex;
            align-items: center;
            justify-content: space-between;
        }
        .jadwal-table-header h3 {
            font-size: 1.05rem;
            font-weight: 700;
            color: var(--biru-malam);
            margin: 0;
            display: flex;
            align-items: center;
            gap: 0.5rem;
        }
        .jadwal-table-header h3 i {
            color: var(--biru-utama);
            font-size: 1rem;
        }
        .jadwal-table-count {
            font-size: 0.8rem;
            color: #94a3b8;
            font-weight: 500;
        }

        /* ── Refined Table ── */
        .jadwal-tabel {
            width: 100%;
            border-collapse: collapse;
        }
        .jadwal-tabel thead th {
            background: #f8fafc;
            padding: 0.85rem 1rem;
            font-size: 0.72rem;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: 0.6px;
            color: #64748b;
            border-bottom: 1px solid #e5e7eb;
            text-align: left;
            white-space: nowrap;
        }
        .jadwal-tabel tbody tr {
            border-bottom: 1px solid #f1f5f9;
            transition: background 0.15s ease;
        }
        .jadwal-tabel tbody tr:last-child {
            border-bottom: none;
        }
        .jadwal-tabel tbody tr:hover {
            background: #fafbff;
        }
        .jadwal-tabel tbody td {
            padding: 1rem 1rem;
            font-size: 0.875rem;
            color: #334155;
            vertical-align: middle;
        }

        /* ── Kode Badge ── */
        .kode-jadwal {
            font-family: 'JetBrains Mono', 'Fira Code', 'SF Mono', monospace;
            font-size: 0.75rem;
            font-weight: 600;
            color: #475569;
            background: #f1f5f9;
            padding: 0.3rem 0.6rem;
            border-radius: 6px;
            letter-spacing: 0.3px;
            white-space: nowrap;
        }

        /* ── Skema Info ── */
        .skema-nama {
            font-weight: 700;
            color: var(--biru-malam);
            font-size: 0.85rem;
            line-height: 1.4;
            max-width: 220px;
        }

        /* ── Asesor Info ── */
        .asesor-info {
            display: flex;
            align-items: center;
            gap: 0.6rem;
        }
        .asesor-avatar {
            width: 34px;
            height: 34px;
            border-radius: 10px;
            background: linear-gradient(135deg, var(--biru-utama), #6366f1);
            display: flex;
            align-items: center;
            justify-content: center;
            color: #fff;
            font-size: 0.75rem;
            font-weight: 700;
            flex-shrink: 0;
            text-transform: uppercase;
        }
        .asesor-nama {
            font-weight: 600;
            color: var(--biru-malam);
            font-size: 0.85rem;
            line-height: 1.3;
        }

        /* ── Tanggal & Waktu ── */
        .jadwal-waktu {
            display: flex;
            flex-direction: column;
            gap: 2px;
        }
        .jadwal-tanggal {
            font-weight: 600;
            color: #334155;
            font-size: 0.85rem;
            white-space: nowrap;
        }
        .jadwal-jam {
            font-size: 0.75rem;
            color: #94a3b8;
            font-weight: 500;
            display: flex;
            align-items: center;
            gap: 4px;
        }

        /* ── Lokasi TUK ── */
        .tuk-lokasi {
            display: flex;
            align-items: center;
            gap: 0.4rem;
            font-size: 0.85rem;
            color: #475569;
        }
        .tuk-lokasi i {
            color: #94a3b8;
            font-size: 0.75rem;
        }

        /* ── Kuota ── */
        .kuota-badge {
            display: inline-flex;
            align-items: center;
            gap: 4px;
            background: #eff6ff;
            color: #3b82f6;
            padding: 0.3rem 0.65rem;
            border-radius: 8px;
            font-weight: 700;
            font-size: 0.85rem;
        }
        .kuota-badge small {
            font-weight: 500;
            font-size: 0.7rem;
            color: #60a5fa;
        }

        /* ── Status Badges ── */
        .status-badge {
            display: inline-flex;
            align-items: center;
            gap: 6px;
            padding: 0.35rem 0.85rem;
            border-radius: 20px;
            font-size: 0.75rem;
            font-weight: 700;
            white-space: nowrap;
            letter-spacing: 0.2px;
        }
        .status-badge.aktif {
            background: #ecfdf5;
            color: #059669;
            border: 1px solid #a7f3d0;
        }
        .status-badge.aktif .pulse-dot {
            width: 7px; height: 7px;
            border-radius: 50%;
            background: #10b981;
            animation: pulse-glow 1.5s ease-in-out infinite;
        }
        .status-badge.terjadwal {
            background: #fefce8;
            color: #b45309;
            border: 1px solid #fde68a;
        }
        .status-badge.selesai {
            background: #f1f5f9;
            color: #64748b;
            border: 1px solid #cbd5e1;
        }
        .status-badge.dibatalkan {
            background: #fef2f2;
            color: #dc2626;
            border: 1px solid #fecaca;
        }

        @keyframes pulse-glow {
            0%, 100% { opacity: 1; transform: scale(1); }
            50% { opacity: 0.5; transform: scale(1.4); }
        }

        /* ── Action Buttons ── */
        .aksi-grup {
            display: flex;
            gap: 0.35rem;
            align-items: center;
        }
        .btn-aksi {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: 0.35rem;
            padding: 0.45rem 0.85rem;
            border-radius: 8px;
            font-size: 0.78rem;
            font-weight: 600;
            border: none;
            cursor: pointer;
            transition: all 0.2s ease;
            text-decoration: none;
        }
        .btn-aksi.edit {
            background: #eff6ff;
            color: #3b82f6;
            border: 1px solid #bfdbfe;
        }
        .btn-aksi.edit:hover {
            background: #dbeafe;
            border-color: #93c5fd;
            transform: translateY(-1px);
        }
        .btn-aksi.hapus {
            background: #fef2f2;
            color: #ef4444;
            border: 1px solid #fecaca;
            padding: 0.45rem 0.55rem;
        }
        .btn-aksi.hapus:hover {
            background: #fee2e2;
            border-color: #fca5a5;
            transform: translateY(-1px);
        }

        /* ── Empty State ── */
        .empty-state {
            text-align: center;
            padding: 4rem 2rem;
        }
        .empty-state-icon {
            width: 72px;
            height: 72px;
            border-radius: 20px;
            background: #f1f5f9;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            margin-bottom: 1.25rem;
        }
        .empty-state-icon i {
            font-size: 1.8rem;
            color: #94a3b8;
        }
        .empty-state h4 {
            font-size: 1.05rem;
            font-weight: 700;
            color: var(--biru-malam);
            margin-bottom: 0.5rem;
        }
        .empty-state p {
            font-size: 0.85rem;
            color: #94a3b8;
            max-width: 380px;
            margin: 0 auto;
            line-height: 1.6;
        }

        /* ── Page Header ── */
        .page-header-jadwal {
            margin-bottom: 2rem;
            display: flex;
            justify-content: space-between;
            align-items: flex-start;
            gap: 1rem;
        }
        .page-header-jadwal h1 {
            font-size: 1.65rem;
            font-weight: 800;
            color: var(--biru-malam);
            margin: 0 0 0.35rem 0;
            letter-spacing: -0.3px;
            line-height: 1.2;
        }
        .page-header-jadwal p {
            color: #94a3b8;
            font-size: 0.9rem;
            margin: 0;
        }
        .btn-buat-jadwal {
            display: inline-flex;
            align-items: center;
            gap: 0.5rem;
            padding: 0.7rem 1.35rem;
            background: linear-gradient(135deg, var(--biru-utama), #6366f1);
            color: #fff;
            border: none;
            border-radius: 12px;
            font-size: 0.88rem;
            font-weight: 700;
            cursor: pointer;
            transition: all 0.25s ease;
            box-shadow: 0 4px 15px rgba(99, 102, 241, 0.25);
            white-space: nowrap;
        }
        .btn-buat-jadwal:hover {
            transform: translateY(-2px);
            box-shadow: 0 8px 25px rgba(99, 102, 241, 0.35);
        }

        /* ── Pagination Override ── */
        .jadwal-pagination {
            padding: 1rem 1.75rem;
            border-top: 1px solid #f1f5f9;
        }

        /* ── Modal Improvements ── */
        .modal-header-refined {
            display: flex;
            justify-content: space-between;
            align-items: flex-start;
            padding-bottom: 1.25rem;
            margin-bottom: 1.5rem;
            border-bottom: 1px solid #f1f5f9;
        }
        .modal-header-refined h3 {
            color: var(--biru-malam);
            margin: 0;
            font-size: 1.2rem;
            font-weight: 800;
            display: flex;
            align-items: center;
            gap: 0.5rem;
        }
        .modal-header-refined h3 i {
            color: var(--biru-utama);
            font-size: 1rem;
        }
        .modal-header-refined p {
            color: #94a3b8;
            font-size: 0.82rem;
            margin: 0.3rem 0 0 0;
        }
        .modal-close-btn {
            background: #f1f5f9;
            border: none;
            width: 32px;
            height: 32px;
            border-radius: 8px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.15rem;
            color: #64748b;
            cursor: pointer;
            transition: all 0.15s ease;
            flex-shrink: 0;
        }
        .modal-close-btn:hover {
            background: #e2e8f0;
            color: #334155;
        }
        .modal-section-title {
            font-size: 0.72rem;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: 0.8px;
            color: #94a3b8;
            margin-bottom: 0.75rem;
            display: flex;
            align-items: center;
            gap: 0.4rem;
        }
        .modal-section-title i {
            font-size: 0.7rem;
        }
        .modal-divider {
            border: none;
            border-top: 1px solid #f1f5f9;
            margin: 1.25rem 0;
        }
        .modal-footer-refined {
            margin-top: 1.75rem;
            padding-top: 1.25rem;
            border-top: 1px solid #f1f5f9;
            display: flex;
            gap: 0.6rem;
            justify-content: flex-end;
        }

        /* ── Responsive ── */
        @media (max-width: 768px) {
            .jadwal-stats { grid-template-columns: repeat(2, 1fr); }
            .page-header-jadwal { flex-direction: column; }
        }
    </style>
@endpush

@section('konten')
<div style="max-width: 1200px;" class="animasi-slide">

    {{-- ── Page Header ── --}}
    <div class="page-header-jadwal">
        <div>
            <h1><i class="fa-solid fa-calendar-days" style="color: var(--biru-utama); font-size: 1.5rem;"></i> Manajemen Penjadwalan</h1>
            <p>Atur jadwal uji kompetensi, lokasi TUK, dan penugasan Asesor penguji</p>
        </div>
        <button class="btn-buat-jadwal" onclick="bukaModal('modalTambahJadwal')">
            <i class="fa-solid fa-plus"></i> Buat Jadwal Baru
        </button>
    </div>

    {{-- ── Summary Stat Cards ── --}}
    @php
        $totalJadwal = $jadwalList->total();
        $jadwalAktif = $jadwalList->filter(fn($j) => $j->status_jadwal === 'berlangsung')->count();
        $jadwalTerjadwal = $jadwalList->filter(fn($j) => $j->status_jadwal === 'terjadwal')->count();
        $jadwalSelesai = $jadwalList->filter(fn($j) => $j->status_jadwal === 'selesai')->count();
    @endphp
    <div class="jadwal-stats">
        <div class="jadwal-stat-card">
            <div class="jadwal-stat-icon total"><i class="fa-solid fa-layer-group"></i></div>
            <div>
                <div class="jadwal-stat-angka">{{ $totalJadwal }}</div>
                <div class="jadwal-stat-label">Total Jadwal</div>
            </div>
        </div>
        <div class="jadwal-stat-card">
            <div class="jadwal-stat-icon aktif"><i class="fa-solid fa-circle-play"></i></div>
            <div>
                <div class="jadwal-stat-angka">{{ $jadwalAktif }}</div>
                <div class="jadwal-stat-label">Sedang Berlangsung</div>
            </div>
        </div>
        <div class="jadwal-stat-card">
            <div class="jadwal-stat-icon jadwal"><i class="fa-solid fa-clock"></i></div>
            <div>
                <div class="jadwal-stat-angka">{{ $jadwalTerjadwal }}</div>
                <div class="jadwal-stat-label">Terjadwal</div>
            </div>
        </div>
        <div class="jadwal-stat-card">
            <div class="jadwal-stat-icon selesai"><i class="fa-solid fa-circle-check"></i></div>
            <div>
                <div class="jadwal-stat-angka">{{ $jadwalSelesai }}</div>
                <div class="jadwal-stat-label">Selesai</div>
            </div>
        </div>
    </div>

    {{-- ── Table Card ── --}}
    <div class="jadwal-table-card">
        <div class="jadwal-table-header">
            <h3><i class="fa-solid fa-table-list"></i> Daftar Jadwal Asesmen</h3>
            <span class="jadwal-table-count">Menampilkan {{ $jadwalList->count() }} dari {{ $jadwalList->total() }} jadwal</span>
        </div>

        <div class="tabel-wadah" style="overflow-x: auto;">
            <table class="jadwal-tabel">
                <thead>
                    <tr>
                        <th style="padding-left: 1.75rem;">Kode</th>
                        <th>Skema Sertifikasi</th>
                        <th>Asesor Penguji</th>
                        <th>Tanggal & Waktu</th>
                        <th>Lokasi TUK</th>
                        <th style="text-align: center;">Kuota</th>
                        <th style="text-align: center;">Status</th>
                        <th style="text-align: center; padding-right: 1.75rem;">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($jadwalList as $j)
                        <tr>
                            <td style="padding-left: 1.75rem;">
                                <span class="kode-jadwal">{{ $j->kode_jadwal }}</span>
                            </td>
                            <td>
                                <div class="skema-nama">{{ $j->skema->nama_skema }}</div>
                            </td>
                            <td>
                                <div class="asesor-info">
                                    <div class="asesor-avatar">{{ strtoupper(substr($j->asesor->nama_lengkap, 0, 2)) }}</div>
                                    <span class="asesor-nama">{{ $j->asesor->nama_lengkap }}</span>
                                </div>
                            </td>
                            <td>
                                <div class="jadwal-waktu">
                                    <span class="jadwal-tanggal">{{ \Carbon\Carbon::parse($j->tanggal_uji)->translatedFormat('d M Y') }}</span>
                                    <span class="jadwal-jam">
                                        <i class="fa-regular fa-clock"></i>
                                        {{ substr($j->waktu_mulai, 0, 5) }} – {{ substr($j->waktu_selesai, 0, 5) }} WIB
                                    </span>
                                </div>
                            </td>
                            <td>
                                <div class="tuk-lokasi">
                                    <i class="fa-solid fa-location-dot"></i>
                                    {{ $j->nama_tuk }}
                                </div>
                            </td>
                            <td style="text-align: center;">
                                <span class="kuota-badge">
                                    {{ $j->kuota }} <small>Asesi</small>
                                </span>
                            </td>
                            <td style="text-align: center;">
                                @if($j->status_jadwal === 'berlangsung')
                                    <span class="status-badge aktif">
                                        <span class="pulse-dot"></span> Berlangsung
                                    </span>
                                @elseif($j->status_jadwal === 'selesai')
                                    <span class="status-badge selesai">
                                        <i class="fa-solid fa-check" style="font-size: 0.65rem;"></i> Selesai
                                    </span>
                                @elseif($j->status_jadwal === 'dibatalkan')
                                    <span class="status-badge dibatalkan">
                                        <i class="fa-solid fa-ban" style="font-size: 0.65rem;"></i> Dibatalkan
                                    </span>
                                @else
                                    <span class="status-badge terjadwal">
                                        <i class="fa-regular fa-clock" style="font-size: 0.65rem;"></i> Terjadwal
                                    </span>
                                @endif
                            </td>
                            <td style="text-align: center; padding-right: 1.75rem;">
                                <div class="aksi-grup" style="justify-content: center;">
                                    <button type="button"
                                            class="btn-aksi edit"
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
                                    <form action="{{ route('admin.jadwal.hapus', $j->id) }}" method="POST" style="margin: 0;" onsubmit="return confirm('Apakah Anda yakin ingin menghapus jadwal uji #{{ $j->kode_jadwal }}? Tindakan ini tidak dapat dibatalkan.')">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="btn-aksi hapus" title="Hapus Jadwal">
                                            <i class="fa-solid fa-trash-can"></i>
                                        </button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="8">
                                <div class="empty-state">
                                    <div class="empty-state-icon">
                                        <i class="fa-solid fa-calendar-xmark"></i>
                                    </div>
                                    <h4>Belum Ada Jadwal Asesmen</h4>
                                    <p>Belum ada jadwal uji kompetensi yang terdaftar. Klik tombol <strong>"Buat Jadwal Baru"</strong> untuk membuat jadwal pertama.</p>
                                </div>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if($jadwalList->hasPages())
            <div class="jadwal-pagination">
                {{ $jadwalList->links() }}
            </div>
        @endif
    </div>
</div>

{{-- ══════════════════════════════════════════════════════ --}}
{{-- ── MODAL: BUAT JADWAL BARU ── --}}
{{-- ══════════════════════════════════════════════════════ --}}
<div class="modal-overlay" id="modalTambahJadwal">
    <div class="modal-konten" style="max-width: 650px;">
        <div class="modal-header-refined">
            <div>
                <h3><i class="fa-solid fa-calendar-plus"></i> Buat Jadwal Asesmen Baru</h3>
                <p>Tentukan sesi uji kompetensi, lokasi TUK, dan tugaskan Asesor penguji</p>
            </div>
            <button type="button" class="modal-close-btn" onclick="tutupModal('modalTambahJadwal')">&times;</button>
        </div>

        <form action="{{ route('admin.jadwal.simpan') }}" method="POST">
            @csrf

            {{-- Section: Identitas Jadwal --}}
            <div class="modal-section-title"><i class="fa-solid fa-tag"></i> Identitas Jadwal</div>
            <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 1rem;">
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

            <hr class="modal-divider">

            {{-- Section: Penugasan Asesor --}}
            <div class="modal-section-title"><i class="fa-solid fa-user-tie"></i> Penugasan Asesor</div>
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

            <hr class="modal-divider">

            {{-- Section: Lokasi & Waktu --}}
            <div class="modal-section-title"><i class="fa-solid fa-location-dot"></i> Lokasi & Waktu Pelaksanaan</div>
            <div class="grup-form">
                <label class="label-form">Tempat Uji Kompetensi (TUK)</label>
                <input type="text" name="nama_tuk" class="input-control" placeholder="contoh: Lab Komputer RPL 1" required>
            </div>
            <div style="display: grid; grid-template-columns: 1fr 1fr 1fr; gap: 1rem;">
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

            <hr class="modal-divider">

            {{-- Section: Kuota --}}
            <div class="modal-section-title"><i class="fa-solid fa-users"></i> Kapasitas Peserta</div>
            <div class="grup-form">
                <label class="label-form">Kuota Asesi per Sesi</label>
                <input type="number" name="kuota" class="input-control" value="10" min="1" max="50" required>
                <small style="color: #94a3b8; font-size: 0.78rem; margin-top: 0.3rem; display: flex; align-items: center; gap: 4px;">
                    <i class="fa-solid fa-circle-info" style="font-size: 0.7rem;"></i>
                    Standar BNSP: 10 asesi per asesor per hari
                </small>
            </div>

            <div class="modal-footer-refined">
                <button type="button" class="tombol tombol-sekunder" onclick="tutupModal('modalTambahJadwal')">Batal</button>
                <button type="submit" class="tombol tombol-utama">
                    <i class="fa-solid fa-check"></i> Simpan Jadwal
                </button>
            </div>
        </form>
    </div>
</div>

{{-- ══════════════════════════════════════════════════════ --}}
{{-- ── MODAL: EDIT JADWAL ── --}}
{{-- ══════════════════════════════════════════════════════ --}}
<div class="modal-overlay" id="modalEditJadwal">
    <div class="modal-konten" style="max-width: 650px;">
        <div class="modal-header-refined">
            <div>
                <h3><i class="fa-solid fa-calendar-check"></i> Edit Jadwal Asesmen</h3>
                <p>Perbarui data sesi uji kompetensi, penugasan asesor, dan status pelaksanaan</p>
            </div>
            <button type="button" class="modal-close-btn" onclick="tutupModal('modalEditJadwal')">&times;</button>
        </div>

        <form id="formEditJadwal" method="POST" action="">
            @csrf

            {{-- Section: Identitas & Status --}}
            <div class="modal-section-title"><i class="fa-solid fa-tag"></i> Identitas & Status</div>
            <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 1rem;">
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

            <hr class="modal-divider">

            {{-- Section: Skema & Asesor --}}
            <div class="modal-section-title"><i class="fa-solid fa-user-tie"></i> Skema & Penugasan Asesor</div>
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

            <hr class="modal-divider">

            {{-- Section: Lokasi & Waktu --}}
            <div class="modal-section-title"><i class="fa-solid fa-location-dot"></i> Lokasi & Waktu Pelaksanaan</div>
            <div class="grup-form">
                <label class="label-form">Tempat Uji Kompetensi (TUK)</label>
                <input type="text" name="nama_tuk" id="edit_nama_tuk" class="input-control" required placeholder="contoh: Lab Komputer RPL 1">
            </div>
            <div style="display: grid; grid-template-columns: 1fr 1fr 1fr; gap: 1rem;">
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

            <hr class="modal-divider">

            {{-- Section: Kuota --}}
            <div class="modal-section-title"><i class="fa-solid fa-users"></i> Kapasitas Peserta</div>
            <div class="grup-form">
                <label class="label-form">Kuota Asesi per Sesi</label>
                <input type="number" name="kuota" id="edit_kuota" class="input-control" value="10" min="1" max="50" required>
                <small style="color: #94a3b8; font-size: 0.78rem; margin-top: 0.3rem; display: flex; align-items: center; gap: 4px;">
                    <i class="fa-solid fa-circle-info" style="font-size: 0.7rem;"></i>
                    Standar BNSP: 10 asesi per asesor per hari
                </small>
            </div>

            <div class="modal-footer-refined">
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
