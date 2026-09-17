@extends('tata-letak.dasbor')

@section('judul', 'Laporan Terpadu LSP')

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
<div style="max-width: 1150px;" class="animasi-slide">
    
    <!-- HEADER -->
    <div style="margin-bottom: 1.75rem; display: flex; justify-content: space-between; align-items: flex-start; flex-wrap: wrap; gap: 1rem;">
        <div>
            <h1 style="font-size: 1.75rem; color: var(--biru-malam); margin-bottom: 0.35rem;">Pusat Laporan Terpadu LSP</h1>
            <p style="color: var(--abu-teks); font-size: 0.92rem; margin: 0;">Rekapitulasi data hasil asesmen, kelulusan, peserta asesi, dan pelaksanaan uji kompetensi</p>
        </div>
        <div class="no-print">
            <button class="tombol tombol-sekunder tombol-sm" onclick="window.print()">
                Cetak Laporan PDF
            </button>
        </div>
    </div>

    <!-- TABS NAV -->
    <div class="nav-tab-hub no-print">
        <a href="{{ route('admin.laporan-kelulusan', ['tab' => 'kelulusan']) }}" class="btn-tab-hub {{ ($tab ?? 'kelulusan') === 'kelulusan' ? 'aktif' : '' }}">
            Laporan Kelulusan
        </a>
        <a href="{{ route('admin.laporan-kelulusan', ['tab' => 'asesi']) }}" class="btn-tab-hub {{ ($tab ?? '') === 'asesi' ? 'aktif' : '' }}">
            Laporan Asesi
        </a>
        <a href="{{ route('admin.laporan-kelulusan', ['tab' => 'asesmen']) }}" class="btn-tab-hub {{ ($tab ?? '') === 'asesmen' ? 'aktif' : '' }}">
            Laporan Asesmen & Jadwal
        </a>
        <a href="{{ route('admin.laporan-kelulusan', ['tab' => 'rekapitulasi']) }}" class="btn-tab-hub {{ ($tab ?? '') === 'rekapitulasi' ? 'aktif' : '' }}">
            Rekapitulasi Statistik
        </a>
    </div>

    <!-- TAB 1: LAPORAN KELULUSAN -->
    @if(($tab ?? 'kelulusan') === 'kelulusan')
        <div class="kartu no-print" style="margin-bottom: 1.5rem;">
            <form action="{{ route('admin.laporan-kelulusan') }}" method="GET">
                <input type="hidden" name="tab" value="kelulusan">
                <div style="display: flex; gap: 0.75rem; flex-wrap: wrap;">
                    <div style="flex: 1; min-width: 220px;">
                        <select name="skema_id" class="input-control" onchange="this.form.submit()">
                            <option value="">-- Semua Skema Sertifikasi --</option>
                            @foreach($skemaOptions as $s)
                                <option value="{{ $s->id }}" {{ ($skemaId ?? '') == $s->id ? 'selected' : '' }}>{{ $s->nama_skema }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div style="width: 200px;">
                        <select name="status" class="input-control" onchange="this.form.submit()">
                            <option value="">-- Semua Hasil --</option>
                            <option value="kompeten" {{ ($status ?? '') == 'kompeten' ? 'selected' : '' }}>Kompeten (K)</option>
                            <option value="belum_kompeten" {{ ($status ?? '') == 'belum_kompeten' ? 'selected' : '' }}>Belum Kompeten (BK)</option>
                        </select>
                    </div>
                    <button type="submit" class="tombol tombol-utama">Filter</button>
                    @if($skemaId || $status)
                        <a href="{{ route('admin.laporan-kelulusan', ['tab' => 'kelulusan']) }}" class="tombol tombol-sekunder">Reset</a>
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
                            <th>NIK</th>
                            <th>Skema Sertifikasi</th>
                            <th>Asesor Penguji</th>
                            <th>Status Rekomendasi</th>
                            <th>Tanggal Rekomendasi</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($laporanKelulusan as $l)
                            <tr>
                                <td class="font-mono"><strong>{{ $l->nomor_pendaftaran }}</strong></td>
                                <td><strong>{{ $l->asesi->nama_lengkap ?? '-' }}</strong></td>
                                <td class="font-mono">{{ $l->asesi->profilAsesi->nik ?? '-' }}</td>
                                <td>{{ $l->skema->nama_skema ?? '-' }}</td>
                                <td>{{ $l->rekomendasi->asesor->nama_lengkap ?? '-' }}</td>
                                <td>
                                    @if($l->rekomendasi->keputusan === 'kompeten')
                                        <span class="lencana lencana-hijau">KOMPETEN (K)</span>
                                    @else
                                        <span class="lencana lencana-merah">BELUM KOMPETEN (BK)</span>
                                    @endif
                                </td>
                                <td>{{ date('d M Y', strtotime($l->rekomendasi->tanggal_rekomendasi)) }}</td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="7" style="text-align: center; color: var(--abu-teks); padding: 2.5rem 1rem;">
                                    Belum ada data kelulusan asesmen yang tercatat.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <div style="margin-top: 1.5rem;" class="no-print">
                {{ $laporanKelulusan->appends(['tab' => 'kelulusan', 'skema_id' => $skemaId, 'status' => $status])->links() }}
            </div>
        </div>

    <!-- TAB 2: LAPORAN ASESI -->
    @elseif($tab === 'asesi')
        <div class="kartu no-print" style="margin-bottom: 1.5rem;">
            <form action="{{ route('admin.laporan-kelulusan') }}" method="GET">
                <input type="hidden" name="tab" value="asesi">
                <div style="display: flex; gap: 0.75rem;">
                    <input type="text" name="q" class="input-control" placeholder="Cari nama asesi, email, atau NIK..." value="{{ $kataKunci }}">
                    <button type="submit" class="tombol tombol-utama">Cari</button>
                    @if($kataKunci)
                        <a href="{{ route('admin.laporan-kelulusan', ['tab' => 'asesi']) }}" class="tombol tombol-sekunder">Reset</a>
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
                            <th>Kontak (Email / Telp)</th>
                            <th>NIK</th>
                            <th>Instansi / Asal Sekolah</th>
                            <th>Total Pendaftaran</th>
                            <th>Tgl Terdaftar</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($laporanAsesi as $a)
                            <tr>
                                <td><strong>{{ $a->nama_lengkap }}</strong></td>
                                <td>
                                    {{ $a->email }}<br>
                                    <small style="color: var(--abu-teks);">{{ $a->nomor_telepon ?? '-' }}</small>
                                </td>
                                <td class="font-mono">{{ $a->profilAsesi->nik ?? '-' }}</td>
                                <td>{{ $a->profilAsesi->nama_sekolah_instansi ?? '-' }}</td>
                                <td><span class="lencana lencana-biru">{{ $a->pendaftaranAsesi->count() }} Skema</span></td>
                                <td>{{ date('d M Y', strtotime($a->created_at)) }}</td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6" style="text-align: center; color: var(--abu-teks); padding: 2.5rem 1rem;">
                                    Belum ada data asesi yang tercatat.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <div style="margin-top: 1.5rem;" class="no-print">
                {{ $laporanAsesi->appends(['tab' => 'asesi', 'q' => $kataKunci])->links() }}
            </div>
        </div>

    <!-- TAB 3: LAPORAN ASESMEN & JADWAL -->
    @elseif($tab === 'asesmen')
        <div class="kartu no-print" style="margin-bottom: 1.5rem;">
            <form action="{{ route('admin.laporan-kelulusan') }}" method="GET">
                <input type="hidden" name="tab" value="asesmen">
                <div style="display: flex; gap: 0.75rem;">
                    <select name="skema_id" class="input-control" onchange="this.form.submit()">
                        <option value="">-- Semua Skema Sertifikasi --</option>
                        @foreach($skemaOptions as $s)
                            <option value="{{ $s->id }}" {{ ($skemaId ?? '') == $s->id ? 'selected' : '' }}>{{ $s->nama_skema }}</option>
                        @endforeach
                    </select>
                    <button type="submit" class="tombol tombol-utama">Filter</button>
                    @if($skemaId)
                        <a href="{{ route('admin.laporan-kelulusan', ['tab' => 'asesmen']) }}" class="tombol tombol-sekunder">Reset</a>
                    @endif
                </div>
            </form>
        </div>

        <div class="kartu">
            <div class="tabel-wadah">
                <table class="tabel-custom">
                    <thead>
                        <tr>
                            <th>Kode Jadwal</th>
                            <th>Skema Sertifikasi</th>
                            <th>Asesor Penguji</th>
                            <th>Tempat Uji (TUK)</th>
                            <th>Waktu & Tanggal</th>
                            <th>Peserta / Kuota</th>
                            <th>Status</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($laporanAsesmen as $jadwal)
                            <tr>
                                <td class="font-mono"><strong>{{ $jadwal->kode_jadwal }}</strong></td>
                                <td>{{ $jadwal->skema->nama_skema ?? '-' }}</td>
                                <td>{{ $jadwal->asesor->nama_lengkap ?? '-' }}</td>
                                <td>{{ $jadwal->nama_tuk }}</td>
                                <td>
                                    {{ date('d M Y', strtotime($jadwal->tanggal_uji)) }}<br>
                                    <small style="color: var(--abu-teks);">{{ substr($jadwal->waktu_mulai, 0, 5) }} - {{ substr($jadwal->waktu_selesai, 0, 5) }} WIB</small>
                                </td>
                                <td>
                                    <strong>{{ $jadwal->pendaftaranAsesi->count() }}</strong> / {{ $jadwal->kuota }} asesi
                                </td>
                                <td>
                                    <span class="lencana lencana-biru">{{ ucfirst($jadwal->status_jadwal) }}</span>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="7" style="text-align: center; color: var(--abu-teks); padding: 2.5rem 1rem;">
                                    Belum ada data jadwal asesmen yang tercatat.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <div style="margin-top: 1.5rem;" class="no-print">
                {{ $laporanAsesmen->appends(['tab' => 'asesmen', 'skema_id' => $skemaId])->links() }}
            </div>
        </div>

    <!-- TAB 4: REKAPITULASI STATISTIK -->
    @elseif($tab === 'rekapitulasi')
        <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(220px, 1fr)); gap: 1rem; margin-bottom: 2rem;">
            <div class="kartu" style="padding: 1.25rem;">
                <div style="font-size: 0.78rem; font-weight: 700; color: var(--abu-teks); text-transform: uppercase;">Total Asesi Terdaftar</div>
                <div style="font-size: 1.8rem; font-weight: 800; color: var(--biru-malam); margin-top: 0.25rem;">{{ $rekapitulasi['total_asesi'] ?? 0 }}</div>
            </div>
            <div class="kartu" style="padding: 1.25rem;">
                <div style="font-size: 0.78rem; font-weight: 700; color: var(--abu-teks); text-transform: uppercase;">Total Pendaftaran Skema</div>
                <div style="font-size: 1.8rem; font-weight: 800; color: var(--biru-utama); margin-top: 0.25rem;">{{ $rekapitulasi['total_pendaftaran'] ?? 0 }}</div>
            </div>
            <div class="kartu" style="padding: 1.25rem;">
                <div style="font-size: 0.78rem; font-weight: 700; color: var(--abu-teks); text-transform: uppercase;">Peserta Kompeten (K)</div>
                <div style="font-size: 1.8rem; font-weight: 800; color: var(--hijau-sukses); margin-top: 0.25rem;">{{ $rekapitulasi['total_kompeten'] ?? 0 }}</div>
            </div>
            <div class="kartu" style="padding: 1.25rem;">
                <div style="font-size: 0.78rem; font-weight: 700; color: var(--abu-teks); text-transform: uppercase;">Peserta Belum Kompeten (BK)</div>
                <div style="font-size: 1.8rem; font-weight: 800; color: #dc2626; margin-top: 0.25rem;">{{ $rekapitulasi['total_belum_kompeten'] ?? 0 }}</div>
            </div>
        </div>

        <div class="kartu">
            <h3 style="color: var(--biru-malam); margin-bottom: 1.25rem;">Rekapitulasi Per Skema Sertifikasi</h3>
            <div class="tabel-wadah">
                <table class="tabel-custom">
                    <thead>
                        <tr>
                            <th>Kode Skema</th>
                            <th>Nama Skema Sertifikasi</th>
                            <th>Kategori</th>
                            <th>Jumlah Unit</th>
                            <th>Total Peserta</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($rekapitulasi['skema_stats'] as $skema)
                            <tr>
                                <td class="font-mono"><strong>{{ $skema->kode_skema }}</strong></td>
                                <td><strong>{{ $skema->nama_skema }}</strong></td>
                                <td>{{ $skema->kategori }}</td>
                                <td>{{ $skema->unit_kompetensi_count }} Unit</td>
                                <td>
                                    <span class="lencana lencana-biru">{{ $skema->pendaftaran_asesi_count }} Asesi</span>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="5" style="text-align: center; color: var(--abu-teks); padding: 2rem;">Belum ada skema sertifikasi terdaftar.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    @endif

</div>
@endsection
