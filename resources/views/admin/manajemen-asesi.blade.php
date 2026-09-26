@extends('tata-letak.dasbor')

@section('judul', 'Asesi - Data & Verifikasi')

@push('css')
    <link rel="stylesheet" href="{{ asset('css/admin/dashboard-admin.css') }}">
    <style>
        .nav-tab-hub {
            display: flex;
            gap: 0.5rem;
            border-bottom: 2px solid var(--biru-soft);
            margin-bottom: 1.5rem;
            flex-wrap: wrap;
        }
        .btn-tab-hub {
            padding: 0.75rem 1.25rem;
            font-weight: 700;
            font-size: 0.9rem;
            color: var(--abu-teks);
            background: none;
            border: none;
            border-bottom: 3px solid transparent;
            cursor: pointer;
            text-decoration: none;
            display: inline-flex;
            align-items: center;
            gap: 0.5rem;
            transition: all 0.2s ease;
        }
        .btn-tab-hub:hover {
            color: var(--biru-utama);
        }
        .btn-tab-hub.aktif {
            color: var(--biru-utama);
            border-bottom-color: var(--biru-utama);
            background: var(--biru-bg);
            border-radius: var(--radius-md) var(--radius-md) 0 0;
        }
    </style>
@endpush

@section('konten')
<div style="max-width: 1100px;" class="animasi-slide">
    
    <!-- HEADER -->
    <div style="margin-bottom: 1.75rem;">
        <h1 style="font-size: 1.75rem; color: var(--biru-malam); margin-bottom: 0.35rem;">Asesi &bull; Data & Verifikasi</h1>
        <p style="color: var(--abu-teks); font-size: 0.92rem; margin: 0;">Pusat pengelolaan master biodata asesi, riwayat pendaftaran, dan verifikasi berkas APL-01</p>
    </div>

    <!-- STATS COUNTER -->
    <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 1rem; margin-bottom: 1.5rem;">
        <div class="kartu" style="padding: 1.15rem 1.25rem;">
            <div style="font-size: 0.78rem; font-weight: 700; color: var(--abu-teks); text-transform: uppercase;">Total Asesi</div>
            <div style="font-size: 1.6rem; font-weight: 800; color: var(--biru-malam); margin-top: 0.25rem;">{{ $totalAsesi ?? 0 }}</div>
        </div>
        <div class="kartu" style="padding: 1.15rem 1.25rem;">
            <div style="font-size: 0.78rem; font-weight: 700; color: var(--amber-teks); text-transform: uppercase;">Menunggu Verifikasi</div>
            <div style="font-size: 1.6rem; font-weight: 800; color: var(--amber-utama); margin-top: 0.25rem;">{{ $totalPendingVerifikasi ?? 0 }}</div>
        </div>
        <div class="kartu" style="padding: 1.15rem 1.25rem;">
            <div style="font-size: 0.78rem; font-weight: 700; color: var(--hijau-teks); text-transform: uppercase;">Berkas Terverifikasi</div>
            <div style="font-size: 1.6rem; font-weight: 800; color: var(--hijau-sukses); margin-top: 0.25rem;">{{ $totalTerverifikasi ?? 0 }}</div>
        </div>
    </div>

    <!-- NAVIGATION TAB -->
    <div class="nav-tab-hub">
        <a href="{{ route('admin.manajemen-asesi', ['tab' => 'data-asesi']) }}" class="btn-tab-hub {{ ($tab ?? 'data-asesi') === 'data-asesi' ? 'aktif' : '' }}">
            Data Master Asesi ({{ $totalAsesi ?? 0 }})
        </a>
        <a href="{{ route('admin.manajemen-asesi', ['tab' => 'verifikasi']) }}" class="btn-tab-hub {{ ($tab ?? '') === 'verifikasi' ? 'aktif' : '' }}">
            Verifikasi Berkas & Pendaftaran ({{ $totalPendingVerifikasi ?? 0 }} Pending)
        </a>
    </div>

    <!-- TAB 1: DATA MASTER ASESI -->
    @if(($tab ?? 'data-asesi') === 'data-asesi')
        <div class="kartu" style="margin-bottom: 1.5rem;">
            <form action="{{ route('admin.manajemen-asesi') }}" method="GET">
                <input type="hidden" name="tab" value="data-asesi">
                <div style="display: flex; gap: 0.75rem;">
                    <input type="text" name="q" class="input-control" placeholder="Cari nama asesi, email, atau NIK..." value="{{ $kataKunci }}">
                    <button type="submit" class="tombol tombol-utama">Cari</button>
                    @if($kataKunci)
                        <a href="{{ route('admin.manajemen-asesi', ['tab' => 'data-asesi']) }}" class="tombol tombol-sekunder">Reset</a>
                    @endif
                </div>
            </form>
        </div>

        <div class="kartu">
            <div class="tabel-wadah">
                <table class="tabel-custom">
                    <thead>
                        <tr>
                            <th>Nama Asesi</th>
                            <th>Email & No. Telp</th>
                            <th>Instansi / Asal Sekolah</th>
                            <th>NIK</th>
                            <th>Tgl Terdaftar</th>
                            <th style="text-align: center;">Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($asesiList as $a)
                            <tr>
                                <td>
                                    <strong style="color: var(--biru-malam);">{{ $a->nama_lengkap }}</strong>
                                </td>
                                <td>
                                    <span style="font-weight: 500;">{{ $a->email }}</span><br>
                                    <small style="color: var(--abu-teks);">{{ $a->nomor_telepon ?? '-' }}</small>
                                </td>
                                <td>{{ $a->profilAsesi->nama_sekolah_instansi ?? '-' }}</td>
                                <td><span class="font-mono">{{ $a->profilAsesi->nik ?? '-' }}</span></td>
                                <td>{{ date('d M Y', strtotime($a->created_at)) }}</td>
                                <td style="text-align: center;">
                                    <a href="{{ route('admin.detail-asesi', $a->id) }}" class="tombol tombol-sekunder tombol-sm">
                                        Detail & Riwayat
                                    </a>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6" style="text-align: center; color: var(--abu-teks); padding: 2.5rem 1rem;">
                                    Belum ada data asesi yang terdaftar dalam database.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <div style="margin-top: 1.5rem;">
                {{ $asesiList->appends(['tab' => 'data-asesi', 'q' => $kataKunci])->links() }}
            </div>
        </div>

    <!-- TAB 2: VERIFIKASI BERKAS & PENDAFTARAN -->
    @else
        <div class="kartu" style="margin-bottom: 1.5rem;">
            <form action="{{ route('admin.manajemen-asesi') }}" method="GET">
                <input type="hidden" name="tab" value="verifikasi">
                <div style="display: flex; gap: 0.75rem; flex-wrap: wrap;">
                    <div style="flex: 1; min-width: 240px;">
                        <input type="text" name="q" class="input-control" placeholder="Cari nama asesi atau nomor pendaftaran..." value="{{ $kataKunci }}">
                    </div>
                    <div style="width: 200px;">
                        <select name="status" class="input-control" onchange="this.form.submit()">
                            <option value="diajukan" {{ ($statusVerifikasi ?? '') == 'diajukan' ? 'selected' : '' }}>Menunggu Verifikasi (Diajukan)</option>
                            <option value="diverifikasi" {{ ($statusVerifikasi ?? '') == 'diverifikasi' ? 'selected' : '' }}>Diverifikasi (Diterima)</option>
                            <option value="ditolak" {{ ($statusVerifikasi ?? '') == 'ditolak' ? 'selected' : '' }}>Ditolak</option>
                            <option value="semua" {{ ($statusVerifikasi ?? '') == 'semua' ? 'selected' : '' }}>Semua Status</option>
                        </select>
                    </div>
                    <button type="submit" class="tombol tombol-utama">Filter</button>
                    @if($kataKunci || ($statusVerifikasi && $statusVerifikasi !== 'diajukan'))
                        <a href="{{ route('admin.manajemen-asesi', ['tab' => 'verifikasi']) }}" class="tombol tombol-sekunder">Reset</a>
                    @endif
                </div>
            </form>
        </div>

        <div class="kartu">
            <div class="tabel-wadah">
                <table class="tabel-custom">
                    <thead>
                        <tr>
                            <th>No. Pendaftaran</th>
                            <th>Nama Asesi</th>
                            <th>Skema Sertifikasi</th>
                            <th>Jadwal & TUK</th>
                            <th>Status Berkas</th>
                            <th style="text-align: center;">Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($pendaftaranList as $p)
                        <tr>
                            <td><span class="font-mono">{{ $p->nomor_pendaftaran }}</span></td>
                            <td>
                                <strong style="color: var(--biru-malam);">{{ $p->asesi->nama_lengkap ?? '-' }}</strong><br>
                                <small style="color: var(--abu-teks);">{{ $p->asesi->email ?? '-' }}</small>
                            </td>
                            <td style="font-weight: 600;">{{ $p->skema->nama_skema ?? '-' }}</td>
                                <td>
                                    @if($p->jadwal)
                                        {{ date('d M Y', strtotime($p->jadwal->tanggal_uji)) }}<br>
                                        <small style="color: var(--abu-teks);">{{ $p->jadwal->nama_tuk }}</small>
                                    @else
                                        <span style="color: var(--abu-teks); font-style: italic;">Belum Dijadwalkan</span>
                                    @endif
                                </td>
                                <td>
                                    @if($p->status_pendaftaran === 'diverifikasi')
                                        <span class="lencana lencana-hijau">Diverifikasi</span>
                                    @elseif($p->status_pendaftaran === 'ditolak')
                                        <span class="lencana lencana-merah">Ditolak</span>
                                    @else
                                        <span class="lencana lencana-amber">Menunggu Verifikasi</span>
                                    @endif
                                </td>
                                <td style="text-align: center;">
                                    <a href="{{ route('admin.detail-verifikasi', $p->id) }}" class="tombol tombol-utama tombol-sm">
                                        Periksa Berkas
                                    </a>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6" style="text-align: center; color: var(--abu-teks); padding: 2.5rem 1rem;">
                                    Tidak ada pendaftaran berkas asesi pada status filter ini.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <div style="margin-top: 1.5rem;">
                {{ $pendaftaranList->appends(['tab' => 'verifikasi', 'q' => $kataKunci, 'status' => $statusVerifikasi])->links() }}
            </div>
        </div>
    @endif

</div>
@endsection
