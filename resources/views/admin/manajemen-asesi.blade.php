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
        .lencana-abu {
            background: #f1f5f9;
            color: #475569;
            border: 1px solid #cbd5e1;
        }
        .kartu-stat-link {
            text-decoration: none;
            color: inherit;
            display: block;
            transition: transform 0.2s ease, box-shadow 0.2s ease;
        }
        .kartu-stat-link:hover {
            transform: translateY(-2px);
            box-shadow: 0 6px 15px rgba(0, 0, 0, 0.06);
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
    <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(210px, 1fr)); gap: 1rem; margin-bottom: 1.5rem;">
        <a href="{{ route('admin.manajemen-asesi', ['tab' => 'data-asesi']) }}" class="kartu kartu-stat-link" style="padding: 1.15rem 1.25rem;">
            <div style="font-size: 0.78rem; font-weight: 700; color: var(--abu-teks); text-transform: uppercase;">Total Asesi</div>
            <div style="font-size: 1.6rem; font-weight: 800; color: var(--biru-malam); margin-top: 0.25rem;">{{ $totalAsesi ?? 0 }}</div>
        </a>
        <a href="{{ route('admin.manajemen-asesi', ['tab' => 'data-asesi', 'skema' => 'belum_daftar']) }}" class="kartu kartu-stat-link" style="padding: 1.15rem 1.25rem;">
            <div style="font-size: 0.78rem; font-weight: 700; color: #b45309; text-transform: uppercase;">Belum Mendaftar Skema</div>
            <div style="font-size: 1.6rem; font-weight: 800; color: #d97706; margin-top: 0.25rem;">{{ $totalBelumDaftar ?? 0 }}</div>
        </a>
        <a href="{{ route('admin.manajemen-asesi', ['tab' => 'verifikasi', 'status' => 'diajukan']) }}" class="kartu kartu-stat-link" style="padding: 1.15rem 1.25rem;">
            <div style="font-size: 0.78rem; font-weight: 700; color: var(--amber-teks); text-transform: uppercase;">Menunggu Verifikasi</div>
            <div style="font-size: 1.6rem; font-weight: 800; color: var(--amber-utama); margin-top: 0.25rem;">{{ $totalPendingVerifikasi ?? 0 }}</div>
        </a>
        <a href="{{ route('admin.manajemen-asesi', ['tab' => 'verifikasi', 'status' => 'diverifikasi']) }}" class="kartu kartu-stat-link" style="padding: 1.15rem 1.25rem;">
            <div style="font-size: 0.78rem; font-weight: 700; color: var(--hijau-teks); text-transform: uppercase;">Berkas Terverifikasi</div>
            <div style="font-size: 1.6rem; font-weight: 800; color: var(--hijau-sukses); margin-top: 0.25rem;">{{ $totalTerverifikasi ?? 0 }}</div>
        </a>
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
                <div style="display: flex; gap: 0.75rem; flex-wrap: wrap; align-items: center;">
                    <div style="flex: 1; min-width: 250px;">
                        <input type="text" name="q" class="input-control" placeholder="Cari nama asesi, email, atau NIK..." value="{{ $kataKunci }}">
                    </div>
                    <div style="min-width: 260px; max-width: 380px; flex: 1;">
                        <select name="skema" class="input-control" onchange="this.form.submit()">
                            <option value="">Semua Skema & Status</option>
                            <option value="belum_daftar" {{ ($skemaFilter ?? '') === 'belum_daftar' ? 'selected' : '' }} style="color: #b45309; font-weight: 700;">
                                &#9888; Belum Mendaftar Skema ({{ $totalBelumDaftar ?? 0 }})
                            </option>
                            <optgroup label="Filter Berdasarkan Skema">
                                @foreach($daftarSkema as $s)
                                    <option value="{{ $s->id }}" {{ ($skemaFilter ?? '') == $s->id ? 'selected' : '' }}>
                                        {{ $s->kode_skema ? '[' . $s->kode_skema . '] ' : '' }}{{ $s->nama_skema }}
                                    </option>
                                @endforeach
                            </optgroup>
                        </select>
                    </div>
                    <div style="display: flex; gap: 0.5rem;">
                        <button type="submit" class="tombol tombol-utama">Filter</button>
                        @if($kataKunci || $skemaFilter)
                            <a href="{{ route('admin.manajemen-asesi', ['tab' => 'data-asesi']) }}" class="tombol tombol-sekunder">Reset</a>
                        @endif
                    </div>
                </div>
            </form>
        </div>

        @if($skemaFilter)
            <div style="margin-bottom: 1rem; display: flex; align-items: center; gap: 0.5rem; flex-wrap: wrap;">
                <span style="font-size: 0.85rem; color: var(--abu-teks);">Filter aktif:</span>
                @if($skemaFilter === 'belum_daftar')
                    <span class="lencana lencana-amber" style="font-size: 0.8rem; padding: 0.3rem 0.7rem;">
                        Asesi Belum Mendaftar Skema
                        <a href="{{ route('admin.manajemen-asesi', ['tab' => 'data-asesi', 'q' => $kataKunci]) }}" style="color: inherit; text-decoration: none; margin-left: 0.35rem; font-weight: 800;" title="Hapus filter ini">&times;</a>
                    </span>
                @else
                    @php $selectedSkemaObj = $daftarSkema->firstWhere('id', $skemaFilter); @endphp
                    @if($selectedSkemaObj)
                        <span class="lencana lencana-biru" style="font-size: 0.8rem; padding: 0.3rem 0.7rem;">
                            Skema: {{ $selectedSkemaObj->nama_skema }}
                            <a href="{{ route('admin.manajemen-asesi', ['tab' => 'data-asesi', 'q' => $kataKunci]) }}" style="color: inherit; text-decoration: none; margin-left: 0.35rem; font-weight: 800;" title="Hapus filter ini">&times;</a>
                        </span>
                    @endif
                @endif
            </div>
        @endif

        <div class="kartu">
            <div class="tabel-wadah">
                <table class="tabel-custom">
                    <thead>
                        <tr>
                            <th>Nama Asesi</th>
                            <th>Email & No. Telp</th>
                            <th>Skema Sertifikasi</th>
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
                                <td>
                                    @if($a->pendaftaranAsesi->isEmpty())
                                        <span class="lencana lencana-abu" style="font-size: 0.78rem;">
                                            Belum Mendaftar Skema
                                        </span>
                                    @else
                                        <div style="display: flex; flex-direction: column; gap: 0.35rem;">
                                            @foreach($a->pendaftaranAsesi as $p)
                                                <div style="display: flex; align-items: center; gap: 0.35rem; flex-wrap: wrap;">
                                                    <span class="lencana lencana-biru" style="font-size: 0.78rem; text-align: left; max-width: 250px; white-space: normal; line-height: 1.3;" title="{{ $p->skema->nama_skema ?? '-' }}">
                                                        {{ $p->skema->nama_skema ?? '-' }}
                                                    </span>
                                                    @if($p->status_pendaftaran === 'diverifikasi')
                                                        <span class="lencana lencana-hijau" style="font-size: 0.7rem; padding: 0.15rem 0.45rem;">Diterima</span>
                                                    @elseif($p->status_pendaftaran === 'ditolak')
                                                        <span class="lencana lencana-merah" style="font-size: 0.7rem; padding: 0.15rem 0.45rem;">Ditolak</span>
                                                    @else
                                                        <span class="lencana lencana-amber" style="font-size: 0.7rem; padding: 0.15rem 0.45rem;">Diajukan</span>
                                                    @endif
                                                </div>
                                            @endforeach
                                        </div>
                                    @endif
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
                                <td colspan="7" style="text-align: center; color: var(--abu-teks); padding: 2.5rem 1rem;">
                                    @if($kataKunci || $skemaFilter)
                                        Tidak ditemukan data asesi yang sesuai dengan pencarian atau filter skema yang dipilih.
                                    @else
                                        Belum ada data asesi yang terdaftar dalam database.
                                    @endif
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <div style="margin-top: 1.5rem;">
                {{ $asesiList->appends(['tab' => 'data-asesi', 'q' => $kataKunci, 'skema' => $skemaFilter])->links() }}
            </div>
        </div>

    <!-- TAB 2: VERIFIKASI BERKAS & PENDAFTARAN -->
    @else
        <div class="kartu" style="margin-bottom: 1.5rem;">
            <form action="{{ route('admin.manajemen-asesi') }}" method="GET">
                <input type="hidden" name="tab" value="verifikasi">
                <div style="display: flex; gap: 0.75rem; flex-wrap: wrap;">
                    <div style="flex: 1; min-width: 220px;">
                        <input type="text" name="q" class="input-control" placeholder="Cari nama asesi atau nomor pendaftaran..." value="{{ $kataKunci }}">
                    </div>
                    <div style="min-width: 220px; flex: 1;">
                        <select name="skema" class="input-control" onchange="this.form.submit()">
                            <option value="">Semua Skema Sertifikasi</option>
                            @foreach($daftarSkema as $s)
                                <option value="{{ $s->id }}" {{ ($skemaFilter ?? '') == $s->id ? 'selected' : '' }}>
                                    {{ $s->kode_skema ? '[' . $s->kode_skema . '] ' : '' }}{{ $s->nama_skema }}
                                </option>
                            @endforeach
                        </select>
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
                    @if($kataKunci || ($statusVerifikasi && $statusVerifikasi !== 'diajukan') || $skemaFilter)
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
                {{ $pendaftaranList->appends(['tab' => 'verifikasi', 'q' => $kataKunci, 'status' => $statusVerifikasi, 'skema' => $skemaFilter])->links() }}
            </div>
        </div>
    @endif

</div>
@endsection
