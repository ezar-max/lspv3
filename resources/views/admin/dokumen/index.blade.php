@extends('tata-letak.dasbor')

@section('judul', 'Pusat Arsip Dokumen & Legalitas LSP')

@push('css')
    <link rel="stylesheet" href="{{ asset('css/admin/dashboard-admin.css') }}">
    <style>
        .nav-tab-dokumen {
            display: flex;
            gap: 0.5rem;
            border-bottom: 2px solid var(--biru-soft);
            margin-bottom: 1.5rem;
            flex-wrap: wrap;
        }
        .btn-tab-dokumen {
            padding: 0.75rem 1.25rem;
            font-weight: 700;
            font-size: 0.88rem;
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
        .btn-tab-dokumen:hover {
            color: var(--biru-utama);
        }
        .btn-tab-dokumen.aktif {
            color: var(--biru-utama);
            border-bottom-color: var(--biru-utama);
            background: var(--biru-bg);
            border-radius: var(--radius-md) var(--radius-md) 0 0;
        }
        .progress-form-badges {
            display: flex;
            gap: 0.35rem;
            flex-wrap: wrap;
        }
        .badge-form-item {
            font-size: 0.72rem;
            font-weight: 700;
            padding: 0.2rem 0.5rem;
            border-radius: 4px;
            text-transform: uppercase;
        }
        .badge-form-complete {
            background: #dcfce7;
            color: #166534;
            border: 1px solid #86efac;
        }
        .badge-form-pending {
            background: #f1f5f9;
            color: #64748b;
            border: 1px solid #cbd5e1;
        }
    </style>
@endpush

@section('konten')
<div style="max-width: 1150px;" class="animasi-slide">
    
    <!-- HEADER HALAMAN -->
    <div style="margin-bottom: 1.75rem; display: flex; justify-content: space-between; align-items: flex-start; flex-wrap: wrap; gap: 1rem;">
        <div>
            <h1 style="font-size: 1.75rem; color: var(--biru-malam); margin-bottom: 0.35rem; font-weight: 800;">
                Pusat Arsip Dokumen & Berkas Asesmen
            </h1>
            <p style="color: var(--abu-teks); margin: 0; font-size: 0.92rem;">
                Pengelolaan arsip portofolio uji, penerbitan surat tugas asesor, berita acara pleno, dan master legalitas LSP
            </p>
        </div>
        <div>
            @if(($tab ?? 'bundel') === 'surat-tugas')
                <button type="button" class="tombol tombol-utama" onclick="bukaModal('modalBuatSuratTugas')" style="font-weight: 700;">
                    + Buat Surat Tugas Asesor
                </button>
            @elseif(($tab ?? '') === 'berita-acara')
                <button type="button" class="tombol tombol-utama" onclick="bukaModal('modalGenerateBa')" style="font-weight: 700;">
                    + Generate Berita Acara Otomatis
                </button>
            @elseif(($tab ?? '') === 'legalitas')
                <button type="button" class="tombol tombol-utama" onclick="bukaModal('modalUploadLegalitas')" style="font-weight: 700;">
                    + Upload Dokumen Legalitas
                </button>
            @endif
        </div>
    </div>

    @if(session('sukses'))
        <div class="pesan-sukses" style="margin-bottom: 1.5rem; background: #f0fdf4; border: 1px solid #86efac; color: #166534; padding: 1rem 1.25rem; border-radius: 10px;">
            {{ session('sukses') }}
        </div>
    @endif

    <!-- 4 TAB NAVIGASI UTAMA -->
    <div class="nav-tab-dokumen">
        <a href="{{ route('admin.dokumen.index', ['tab' => 'bundel']) }}" class="btn-tab-dokumen {{ ($tab ?? 'bundel') === 'bundel' ? 'aktif' : '' }}">
            Berkas Bundel Asesmen ({{ $counts['total_bundel'] ?? 0 }})
        </a>
        <a href="{{ route('admin.dokumen.index', ['tab' => 'surat-tugas']) }}" class="btn-tab-dokumen {{ ($tab ?? '') === 'surat-tugas' ? 'aktif' : '' }}">
            Surat Tugas Asesor ({{ $counts['total_surat'] ?? 0 }})
        </a>
        <a href="{{ route('admin.dokumen.index', ['tab' => 'berita-acara']) }}" class="btn-tab-dokumen {{ ($tab ?? '') === 'berita-acara' ? 'aktif' : '' }}">
            Berita Acara & Pleno ({{ $counts['total_ba'] ?? 0 }})
        </a>
        <a href="{{ route('admin.dokumen.index', ['tab' => 'legalitas']) }}" class="btn-tab-dokumen {{ ($tab ?? '') === 'legalitas' ? 'aktif' : '' }}">
            Dokumen Legalitas & Lisensi ({{ $counts['total_legalitas'] ?? 0 }})
        </a>
    </div>

    <!-- ============================================================== -->
    <!-- TAB 1: BERKAS BUNDEL ASESMEN (PORTOFOLIO UJI)                 -->
    <!-- ============================================================== -->
    @if(($tab ?? 'bundel') === 'bundel')
        <div class="kartu" style="margin-bottom: 1.5rem;">
            <form action="{{ route('admin.dokumen.index') }}" method="GET">
                <input type="hidden" name="tab" value="bundel">
                <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 0.75rem; margin-bottom: 0.75rem;">
                    <div>
                        <label style="display: block; font-size: 0.78rem; font-weight: 700; color: #334155; margin-bottom: 0.25rem;">Skema Sertifikasi</label>
                        <select name="skema_id" class="input-control" onchange="this.form.submit()">
                            <option value="">-- Semua Skema --</option>
                            @foreach($skemaOptions as $s)
                                <option value="{{ $s->id }}" {{ ($skemaId ?? '') == $s->id ? 'selected' : '' }}>{{ $s->nama_skema }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div>
                        <label style="display: block; font-size: 0.78rem; font-weight: 700; color: #334155; margin-bottom: 0.25rem;">Jadwal Asesmen</label>
                        <select name="jadwal_id" class="input-control" onchange="this.form.submit()">
                            <option value="">-- Semua Jadwal --</option>
                            @foreach($jadwalOptions as $j)
                                <option value="{{ $j->id }}" {{ ($jadwalId ?? '') == $j->id ? 'selected' : '' }}>
                                    {{ date('d/m/y', strtotime($j->tanggal_uji)) }} - {{ $j->skema->nama_skema ?? '' }} ({{ $j->nama_tuk }})
                                </option>
                            @endforeach
                        </select>
                    </div>
                    <div>
                        <label style="display: block; font-size: 0.78rem; font-weight: 700; color: #334155; margin-bottom: 0.25rem;">Rekomendasi Hasil</label>
                        <select name="hasil" class="input-control" onchange="this.form.submit()">
                            <option value="">-- Semua Hasil --</option>
                            <option value="kompeten" {{ ($statusHasil ?? '') === 'kompeten' ? 'selected' : '' }}>Kompeten (K)</option>
                            <option value="belum_kompeten" {{ ($statusHasil ?? '') === 'belum_kompeten' ? 'selected' : '' }}>Belum Kompeten (BK)</option>
                            <option value="belum_dinilai" {{ ($statusHasil ?? '') === 'belum_dinilai' ? 'selected' : '' }}>Belum Dinilai</option>
                        </select>
                    </div>
                    <div>
                        <label style="display: block; font-size: 0.78rem; font-weight: 700; color: #334155; margin-bottom: 0.25rem;">Status Pendaftaran</label>
                        <select name="status_berkas" class="input-control" onchange="this.form.submit()">
                            <option value="">-- Semua Status --</option>
                            <option value="diverifikasi" {{ ($statusBerkas ?? '') === 'diverifikasi' ? 'selected' : '' }}>Diverifikasi</option>
                            <option value="diajukan" {{ ($statusBerkas ?? '') === 'diajukan' ? 'selected' : '' }}>Diajukan</option>
                            <option value="ditolak" {{ ($statusBerkas ?? '') === 'ditolak' ? 'selected' : '' }}>Ditolak</option>
                        </select>
                    </div>
                </div>

                <div style="display: flex; gap: 0.75rem;">
                    <input type="text" name="q" class="input-control" placeholder="Cari nama asesi atau nomor pendaftaran..." value="{{ $kataKunci }}">
                    <button type="submit" class="tombol tombol-utama" style="padding: 0.55rem 1.25rem;">Filter</button>
                    @if($skemaId || $jadwalId || $statusHasil || $statusBerkas || $kataKunci)
                        <a href="{{ route('admin.dokumen.index', ['tab' => 'bundel']) }}" class="tombol tombol-sekunder" style="padding: 0.55rem 1rem;">Reset</a>
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
                            <th>Nama Asesi & NIK</th>
                            <th>Skema & Jadwal Uji</th>
                            <th>Asesor Penguji</th>
                            <th>Kelengkapan Dokumen</th>
                            <th>Hasil Akhir</th>
                            <th style="text-align: center; width: 190px;">Aksi Cetak & Arsip</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($bundelList as $p)
                            <tr>
                                <td class="font-mono"><strong>{{ $p->nomor_pendaftaran }}</strong></td>
                                <td>
                                    <strong style="color: #0f172a;">{{ $p->asesi->nama_lengkap ?? '-' }}</strong>
                                    <div style="font-size: 0.78rem; color: #64748b;">NIK: {{ $p->asesi->profilAsesi->nik ?? '-' }}</div>
                                </td>
                                <td>
                                    <strong>{{ $p->skema->nama_skema ?? '-' }}</strong>
                                    <div style="font-size: 0.78rem; color: #64748b;">
                                        @if($p->jadwal)
                                            {{ date('d M Y', strtotime($p->jadwal->tanggal_uji)) }} &bull; {{ $p->jadwal->nama_tuk }}
                                        @else
                                            <span style="font-style: italic;">Belum terjadwal</span>
                                        @endif
                                    </div>
                                </td>
                                <td>
                                    {{ $p->jadwal->asesor->nama_lengkap ?? ($p->asesor->nama_lengkap ?? 'Belum Ditugaskan') }}
                                    @if(isset($p->jadwal->asesor->nomor_registrasi))
                                        <div style="font-size: 0.75rem; color: #2563eb; font-family: monospace;">{{ $p->jadwal->asesor->nomor_registrasi }}</div>
                                    @endif
                                </td>
                                <td>
                                    <div class="progress-form-badges">
                                        <span class="badge-form-item badge-form-complete" title="APL-01 Pendaftaran">APL.01</span>
                                        <span class="badge-form-item {{ $p->status_pendaftaran === 'diverifikasi' ? 'badge-form-complete' : 'badge-form-pending' }}" title="APL-02 Asesmen Mandiri">APL.02</span>
                                        <span class="badge-form-item {{ $p->jadwal ? 'badge-form-complete' : 'badge-form-pending' }}" title="MAPA.01 Perencanaan Asesmen">MAPA</span>
                                        <span class="badge-form-item {{ $p->ak01_ttd_asesi ? 'badge-form-complete' : 'badge-form-pending' }}" title="AK.01 Persetujuan & Kerahasiaan">AK.01</span>
                                        <span class="badge-form-item {{ $p->rekomendasi ? 'badge-form-complete' : 'badge-form-pending' }}" title="FR.IA Pelaksanaan Uji">FR.IA</span>
                                    </div>
                                </td>
                                <td>
                                    @if($p->rekomendasi)
                                        @if($p->rekomendasi->keputusan === 'kompeten')
                                            <span class="lencana lencana-hijau">KOMPETEN (K)</span>
                                        @else
                                            <span class="lencana lencana-merah">BELUM KOMPETEN (BK)</span>
                                        @endif
                                    @else
                                        <span class="lencana lencana-biru" style="opacity: 0.8;">Dalam Proses</span>
                                    @endif
                                </td>
                                <td style="text-align: center;">
                                    <div style="display: flex; flex-direction: column; gap: 0.35rem;">
                                        <a href="{{ route('admin.dokumen.bundel.cetak', $p->id) }}" target="_blank" class="tombol tombol-utama tombol-sm" style="font-size: 0.78rem; padding: 0.35rem 0.6rem;">
                                            Cetak Bundel Portofolio
                                        </a>
                                        <a href="{{ route('admin.detail-verifikasi', $p->id) }}" class="tombol tombol-sekunder tombol-sm" style="font-size: 0.78rem; padding: 0.35rem 0.6rem;">
                                            Periksa Form APL-01
                                        </a>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="7" style="text-align: center; color: var(--abu-teks); padding: 3rem 1.5rem;">
                                    Belum ada berkas asesmen yang tersimpan untuk jadwal atau filter ini.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <div style="margin-top: 1.5rem;">
                {{ $bundelList->links() }}
            </div>
        </div>

    <!-- ============================================================== -->
    <!-- TAB 2: SURAT TUGAS & PENUGASAN ASESOR                          -->
    <!-- ============================================================== -->
    @elseif($tab === 'surat-tugas')
        <div class="kartu">
            <div class="tabel-wadah">
                <table class="tabel-custom">
                    <thead>
                        <tr>
                            <th>Nomor Surat Tugas</th>
                            <th>Nama Asesor & No. Reg MET</th>
                            <th>Skema Sertifikasi</th>
                            <th>Tempat Uji (TUK)</th>
                            <th>Tanggal Tugas</th>
                            <th>Status</th>
                            <th style="text-align: center; width: 160px;">Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($suratTugasList as $st)
                            <tr>
                                <td class="font-mono"><strong>{{ $st->nomor_surat }}</strong></td>
                                <td>
                                    <strong style="color: #0f172a;">{{ $st->asesor->nama_lengkap ?? '-' }}</strong>
                                    <div style="font-size: 0.78rem; color: #2563eb; font-family: monospace;">{{ $st->asesor->nomor_registrasi ?? '-' }}</div>
                                </td>
                                <td>{{ $st->jadwal->skema->nama_skema ?? 'Semua Skema Penugasan' }}</td>
                                <td>{{ $st->lokasi_tuk }}</td>
                                <td>{{ date('d M Y', strtotime($st->tanggal_surat)) }}</td>
                                <td><span class="lencana lencana-hijau">{{ $st->status }}</span></td>
                                <td style="text-align: center;">
                                    <div style="display: inline-flex; gap: 0.4rem; align-items: center;">
                                        <a href="{{ route('admin.dokumen.surat-tugas.cetak', $st->id) }}" target="_blank" class="tombol tombol-utama tombol-sm" style="font-size: 0.78rem;">
                                            Cetak PDF
                                        </a>
                                        <form action="{{ route('admin.dokumen.surat-tugas.hapus', $st->id) }}" method="POST" onsubmit="return confirm('Hapus surat tugas {{ $st->nomor_surat }}?')" style="display: inline;">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="tombol tombol-bahaya tombol-sm" style="font-size: 0.78rem; padding: 0.35rem 0.6rem;">
                                                Hapus
                                            </button>
                                        </form>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="7" style="text-align: center; color: var(--abu-teks); padding: 3rem 1.5rem;">
                                    Belum ada Surat Tugas Asesor yang diterbitkan. Klik tombol <strong>"+ Buat Surat Tugas Asesor"</strong> di atas.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <div style="margin-top: 1.5rem;">
                {{ $suratTugasList->links() }}
            </div>
        </div>

    <!-- ============================================================== -->
    <!-- TAB 3: BERITA ACARA & RAPAT PLENO (FR.AK.05 & FR.AK.06)        -->
    <!-- ============================================================== -->
    @elseif($tab === 'berita-acara')
        <div class="kartu">
            <div class="tabel-wadah">
                <table class="tabel-custom">
                    <thead>
                        <tr>
                            <th>Nomor Berita Acara</th>
                            <th>Skema Sertifikasi</th>
                            <th>Asesor Penguji</th>
                            <th>Tgl Pelaksanaan</th>
                            <th>Rekap Hasil (K / BK)</th>
                            <th style="text-align: center; width: 170px;">Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($beritaAcaraList as $ba)
                            <tr>
                                <td class="font-mono"><strong>{{ $ba->nomor_berita_acara }}</strong></td>
                                <td>
                                    <strong>{{ $ba->jadwal->skema->nama_skema ?? '-' }}</strong>
                                    <div style="font-size: 0.78rem; color: #64748b;">TUK: {{ $ba->jadwal->nama_tuk ?? '-' }}</div>
                                </td>
                                <td>{{ $ba->jadwal->asesor->nama_lengkap ?? '-' }}</td>
                                <td>{{ date('d M Y', strtotime($ba->tanggal_pelaksanaan)) }}</td>
                                <td>
                                    <div style="display: flex; gap: 0.4rem; align-items: center;">
                                        <span class="lencana lencana-biru">{{ $ba->jumlah_peserta }} Peserta</span>
                                        <span class="lencana lencana-hijau">{{ $ba->jumlah_kompeten }} K</span>
                                        @if($ba->jumlah_belum_kompeten > 0)
                                            <span class="lencana lencana-merah">{{ $ba->jumlah_belum_kompeten }} BK</span>
                                        @endif
                                    </div>
                                </td>
                                <td style="text-align: center;">
                                    <div style="display: inline-flex; gap: 0.4rem; align-items: center;">
                                        <a href="{{ route('admin.dokumen.berita-acara.cetak', $ba->id) }}" target="_blank" class="tombol tombol-utama tombol-sm" style="font-size: 0.78rem;">
                                            Cetak BAP & Pleno
                                        </a>
                                        <form action="{{ route('admin.dokumen.berita-acara.hapus', $ba->id) }}" method="POST" onsubmit="return confirm('Hapus berita acara {{ $ba->nomor_berita_acara }}?')" style="display: inline;">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="tombol tombol-bahaya tombol-sm" style="font-size: 0.78rem; padding: 0.35rem 0.6rem;">
                                                Hapus
                                            </button>
                                        </form>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6" style="text-align: center; color: var(--abu-teks); padding: 3rem 1.5rem;">
                                    Belum ada Berita Acara & Rekap Pleno yang dibuat. Klik tombol <strong>"+ Generate Berita Acara Otomatis"</strong> di atas.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <div style="margin-top: 1.5rem;">
                {{ $beritaAcaraList->links() }}
            </div>
        </div>

    <!-- ============================================================== -->
    <!-- TAB 4: DOKUMEN LEGALITAS & LISENSI LSP                         -->
    <!-- ============================================================== -->
    @elseif($tab === 'legalitas')
        <div class="kartu">
            <div class="tabel-wadah">
                <table class="tabel-custom">
                    <thead>
                        <tr>
                            <th>Nama Dokumen Resmi</th>
                            <th>Nomor Surat / SK</th>
                            <th>Kategori</th>
                            <th>Tanggal Terbit & Masa Berlaku</th>
                            <th>Status Dokumen</th>
                            <th style="text-align: center; width: 160px;">Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($legalitasList as $leg)
                            <tr>
                                <td>
                                    <strong style="color: #0f172a;">{{ $leg->nama_dokumen }}</strong>
                                    @if($leg->keterangan)
                                        <div style="font-size: 0.78rem; color: #64748b; margin-top: 0.2rem;">{{ $leg->keterangan }}</div>
                                    @endif
                                </td>
                                <td class="font-mono"><strong>{{ $leg->nomor_dokumen ?? '-' }}</strong></td>
                                <td><span class="lencana lencana-biru">{{ $leg->kategori }}</span></td>
                                <td>
                                    @if($leg->tanggal_terbit)
                                        {{ date('d M Y', strtotime($leg->tanggal_terbit)) }}<br>
                                    @endif
                                    <small style="color: #64748b;">Masa Berlaku: {{ $leg->masa_berlaku ?? '-' }}</small>
                                </td>
                                <td><span class="lencana lencana-hijau">{{ $leg->status_dokumen }}</span></td>
                                <td style="text-align: center;">
                                    <div style="display: inline-flex; gap: 0.4rem; align-items: center;">
                                        @if($leg->file_path)
                                            <a href="{{ asset($leg->file_path) }}" target="_blank" class="tombol tombol-utama tombol-sm" style="font-size: 0.78rem;">
                                                Unduh File
                                            </a>
                                        @else
                                            <button type="button" class="tombol tombol-sekunder tombol-sm" disabled style="font-size: 0.78rem; opacity: 0.6;">
                                                Tersimpan
                                            </button>
                                        @endif
                                        <form action="{{ route('admin.dokumen.legalitas.hapus', $leg->id) }}" method="POST" onsubmit="return confirm('Hapus dokumen legalitas ini?')" style="display: inline;">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="tombol tombol-bahaya tombol-sm" style="font-size: 0.78rem; padding: 0.35rem 0.6rem;">
                                                Hapus
                                            </button>
                                        </form>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6" style="text-align: center; color: var(--abu-teks); padding: 3rem 1.5rem;">
                                    Belum ada dokumen legalitas yang tersimpan. Klik tombol <strong>"+ Upload Dokumen Legalitas"</strong> di atas.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <div style="margin-top: 1.5rem;">
                {{ $legalitasList->links() }}
            </div>
        </div>
    @endif

</div>

<!-- ============================================================== -->
<!-- MODALS                                                         -->
<!-- ============================================================== -->

<!-- 1. MODAL BUAT SURAT TUGAS ASESOR -->
<div class="modal-overlay" id="modalBuatSuratTugas">
    <div class="modal-konten" style="max-width: 650px; border-radius: 14px; padding: 2rem;">
        <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 1.5rem; border-bottom: 1px solid #e2e8f0; padding-bottom: 0.85rem;">
            <h3 style="color: #0f172a; font-size: 1.25rem; font-weight: 800; margin: 0;">Penerbitan Surat Tugas Asesor</h3>
            <button type="button" onclick="tutupModal('modalBuatSuratTugas')" style="background: none; border: none; font-size: 1.5rem; cursor: pointer; color: #64748b;">&times;</button>
        </div>

        <form action="{{ route('admin.dokumen.surat-tugas.simpan') }}" method="POST">
            @csrf
            
            <div class="grup-form">
                <label class="label-form">Pilih Jadwal Asesmen (Opsional)</label>
                <select name="jadwal_id" class="input-control" id="pilih_jadwal_st">
                    <option value="">-- Tanpa Jadwal Tertentu / Penugasan Umum --</option>
                    @foreach($jadwalOptions as $j)
                        <option value="{{ $j->id }}" data-asesor="{{ $j->asesor_id }}" data-tuk="{{ $j->nama_tuk }}">
                            {{ date('d M Y', strtotime($j->tanggal_uji)) }} &bull; {{ $j->skema->nama_skema ?? '' }} ({{ $j->nama_tuk }})
                        </option>
                    @endforeach
                </select>
            </div>

            <div class="grup-form">
                <label class="label-form">Pilih Asesor Kompetensi</label>
                <select name="asesor_id" class="input-control" required id="pilih_asesor_st">
                    <option value="">-- Pilih Asesor Terdaftar --</option>
                    @foreach($asesorOptions as $a)
                        <option value="{{ $a->id }}">
                            {{ $a->nama_lengkap }} ({{ $a->nomor_registrasi ?? 'Asesor' }})
                        </option>
                    @endforeach
                </select>
            </div>

            <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 1rem;">
                <div class="grup-form">
                    <label class="label-form">Tanggal Surat Tugas</label>
                    <input type="date" name="tanggal_surat" class="input-control" value="{{ date('Y-m-d') }}" required>
                </div>
                <div class="grup-form">
                    <label class="label-form">Lokasi Tempat Uji (TUK)</label>
                    <input type="text" name="lokasi_tuk" id="input_tuk_st" class="input-control" value="TUK SMKN 1 Gunungputri" required>
                </div>
            </div>

            <div class="grup-form">
                <label class="label-form">Tujuan Penugasan</label>
                <input type="text" name="tujuan_penugasan" class="input-control" value="Pelaksanaan Uji Kompetensi Keahlian / Asesmen Sertifikasi BNSP" required>
            </div>

            <div class="grup-form">
                <label class="label-form">Catatan Tambahan (Opsional)</label>
                <textarea name="catatan" class="input-control" rows="2" placeholder="Petunjuk khusus pelaksanaan tugas..."></textarea>
            </div>

            <div style="margin-top: 1.75rem; text-align: right; display: flex; gap: 0.75rem; justify-content: flex-end; border-top: 1px solid #e2e8f0; padding-top: 1rem;">
                <button type="button" class="tombol tombol-sekunder" onclick="tutupModal('modalBuatSuratTugas')">Batal</button>
                <button type="submit" class="tombol tombol-utama" style="font-weight: 700;">Terbitkan Surat Tugas</button>
            </div>
        </form>
    </div>
</div>

<!-- 2. MODAL GENERATE BERITA ACARA OTOMATIS -->
<div class="modal-overlay" id="modalGenerateBa">
    <div class="modal-konten" style="max-width: 650px; border-radius: 14px; padding: 2rem;">
        <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 1.5rem; border-bottom: 1px solid #e2e8f0; padding-bottom: 0.85rem;">
            <h3 style="color: #0f172a; font-size: 1.25rem; font-weight: 800; margin: 0;">Generate Berita Acara & Rapat Pleno (FR.AK.05)</h3>
            <button type="button" onclick="tutupModal('modalGenerateBa')" style="background: none; border: none; font-size: 1.5rem; cursor: pointer; color: #64748b;">&times;</button>
        </div>

        <form action="{{ route('admin.dokumen.berita-acara.generate') }}" method="POST">
            @csrf
            
            <div class="grup-form">
                <label class="label-form">Pilih Sesi Jadwal Asesmen</label>
                <select name="jadwal_id" class="input-control" required>
                    <option value="">-- Pilih Jadwal Asesmen --</option>
                    @foreach($jadwalOptions as $j)
                        <option value="{{ $j->id }}">
                            {{ date('d M Y', strtotime($j->tanggal_uji)) }} &bull; {{ $j->skema->nama_skema ?? '' }} ({{ $j->nama_tuk }}) - {{ $j->asesor->nama_lengkap ?? 'Asesor' }}
                        </option>
                    @endforeach
                </select>
                <small style="color: #64748b; font-size: 0.78rem;">Sistem akan otomatis menghitung jumlah peserta hadir, rekomendasi Kompeten (K), dan Belum Kompeten (BK).</small>
            </div>

            <div class="grup-form">
                <label class="label-form">Tanggal Pelaksanaan Rapat Pleno</label>
                <input type="date" name="tanggal_pelaksanaan" class="input-control" value="{{ date('Y-m-d') }}" required>
            </div>

            <div class="grup-form">
                <label class="label-form">Catatan Pelaksanaan / Hasil Pleno (Opsional)</label>
                <textarea name="catatan_pelaksanaan" class="input-control" rows="3" placeholder="Uraian jalannya asesmen, insiden khusus jika ada, dan catatan tim komite teknis..."></textarea>
            </div>

            <div style="margin-top: 1.75rem; text-align: right; display: flex; gap: 0.75rem; justify-content: flex-end; border-top: 1px solid #e2e8f0; padding-top: 1rem;">
                <button type="button" class="tombol tombol-sekunder" onclick="tutupModal('modalGenerateBa')">Batal</button>
                <button type="submit" class="tombol tombol-utama" style="font-weight: 700;">Generate BAP & Pleno</button>
            </div>
        </form>
    </div>
</div>

<!-- 3. MODAL UPLOAD DOKUMEN LEGALITAS -->
<div class="modal-overlay" id="modalUploadLegalitas">
    <div class="modal-konten" style="max-width: 650px; border-radius: 14px; padding: 2rem;">
        <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 1.5rem; border-bottom: 1px solid #e2e8f0; padding-bottom: 0.85rem;">
            <h3 style="color: #0f172a; font-size: 1.25rem; font-weight: 800; margin: 0;">Upload Dokumen Legalitas & Lisensi LSP</h3>
            <button type="button" onclick="tutupModal('modalUploadLegalitas')" style="background: none; border: none; font-size: 1.5rem; cursor: pointer; color: #64748b;">&times;</button>
        </div>

        <form action="{{ route('admin.dokumen.legalitas.simpan') }}" method="POST" enctype="multipart/form-data">
            @csrf
            
            <div class="grup-form">
                <label class="label-form">Nama Dokumen Resmi</label>
                <input type="text" name="nama_dokumen" class="input-control" placeholder="contoh: SK Lisensi BNSP LSP SMKN 1 Gunungputri" required>
            </div>

            <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 1rem;">
                <div class="grup-form">
                    <label class="label-form">Nomor Dokumen / SK</label>
                    <input type="text" name="nomor_dokumen" class="input-control" placeholder="contoh: KEP.1215/BNSP/V/2025">
                </div>
                <div class="grup-form">
                    <label class="label-form">Kategori Dokumen</label>
                    <select name="kategori" class="input-control" required>
                        <option value="Lisensi BNSP">Lisensi BNSP</option>
                        <option value="Legalitas TUK">Legalitas TUK</option>
                        <option value="Panduan Mutu">Panduan Mutu / SOP</option>
                        <option value="Sertifikat Asesor">Sertifikat Asesor (MET)</option>
                        <option value="Lainnya">Lainnya</option>
                    </select>
                </div>
            </div>

            <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 1rem;">
                <div class="grup-form">
                    <label class="label-form">Tanggal Terbit</label>
                    <input type="date" name="tanggal_terbit" class="input-control" value="{{ date('Y-m-d') }}">
                </div>
                <div class="grup-form">
                    <label class="label-form">Masa Berlaku</label>
                    <input type="text" name="masa_berlaku" class="input-control" placeholder="contoh: Hingga 23 Mei 2030">
                </div>
            </div>

            <div class="grup-form">
                <label class="label-form">Status Dokumen</label>
                <select name="status_dokumen" class="input-control" required>
                    <option value="Aktif">Aktif</option>
                    <option value="Dalam Proses Perpanjangan">Dalam Proses Perpanjangan</option>
                    <option value="Arsip">Arsip</option>
                </select>
            </div>

            <div class="grup-form">
                <label class="label-form">Unggah Berkas File (PDF / Gambar / Doc, maks 10MB)</label>
                <input type="file" name="file_dokumen" class="input-control" accept=".pdf,.jpg,.jpeg,.png,.doc,.docx">
            </div>

            <div class="grup-form">
                <label class="label-form">Keterangan Dokumen (Opsional)</label>
                <textarea name="keterangan" class="input-control" rows="2" placeholder="Catatan keabsahan atau deskripsi dokumen..."></textarea>
            </div>

            <div style="margin-top: 1.75rem; text-align: right; display: flex; gap: 0.75rem; justify-content: flex-end; border-top: 1px solid #e2e8f0; padding-top: 1rem;">
                <button type="button" class="tombol tombol-sekunder" onclick="tutupModal('modalUploadLegalitas')">Batal</button>
                <button type="submit" class="tombol tombol-utama" style="font-weight: 700;">Simpan Dokumen</button>
            </div>
        </form>
    </div>
</div>
@endsection

@push('js')
<script>
    document.addEventListener('DOMContentLoaded', () => {
        const selectJadwal = document.getElementById('pilih_jadwal_st');
        const selectAsesor = document.getElementById('pilih_asesor_st');
        const inputTuk = document.getElementById('input_tuk_st');

        if (selectJadwal && selectAsesor && inputTuk) {
            selectJadwal.addEventListener('change', () => {
                const selectedOpt = selectJadwal.options[selectJadwal.selectedIndex];
                const asesorId = selectedOpt.getAttribute('data-asesor');
                const tukName = selectedOpt.getAttribute('data-tuk');

                if (asesorId) {
                    selectAsesor.value = asesorId;
                }
                if (tukName) {
                    inputTuk.value = tukName;
                }
            });
        }
    });
</script>
@endpush
