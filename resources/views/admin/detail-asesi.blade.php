@extends('tata-letak.dasbor')

@section('judul', 'Detail Lengkap Asesi')

@push('css')
    <link rel="stylesheet" href="{{ asset('css/admin/dashboard-admin.css') }}">
@endpush

@section('konten')
<div style="max-width: 1050px;" class="animasi-slide">
    
    <div style="margin-bottom: 1.5rem;">
        <a href="{{ route('admin.manajemen-asesi') }}" class="tombol tombol-sekunder tombol-sm">
            Kembali ke Data & Verifikasi
        </a>
    </div>

    <!-- PROFILE CARD -->
    <div class="kartu" style="padding: 2rem; margin-bottom: 2rem;">
        <div style="display: flex; gap: 1.75rem; align-items: flex-start; flex-wrap: wrap;">
            <div style="width: 76px; height: 76px; border-radius: 50%; background: var(--biru-soft); display: flex; align-items: center; justify-content: center; color: var(--biru-utama); font-size: 2rem; font-weight: 800; flex-shrink: 0;">
                {{ strtoupper(substr($asesi->nama_lengkap, 0, 2)) }}
            </div>
            <div style="flex: 1; min-width: 260px;">
                <div style="display: flex; align-items: center; gap: 0.75rem; flex-wrap: wrap;">
                    <h1 style="font-size: 1.8rem; color: var(--biru-malam); margin: 0;">{{ $asesi->nama_lengkap }}</h1>
                    <span class="lencana lencana-biru">Asesi Terdaftar</span>
                </div>
                <p style="color: var(--abu-teks); font-size: 0.95rem; margin-top: 0.35rem; margin-bottom: 1rem;">
                    Email: {{ $asesi->email }} &bull; Telepon/WhatsApp: {{ $asesi->nomor_telepon ?? '-' }}
                </p>

                <!-- DETAIL BIODATA GRID -->
                <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(220px, 1fr)); gap: 1rem; background: var(--biru-bg); padding: 1.25rem; border-radius: var(--radius-md); border: 1px solid var(--biru-soft);">
                    <div>
                        <div style="font-size: 0.75rem; color: var(--abu-teks); font-weight: 700; text-transform: uppercase;">NIK (KTP/KK)</div>
                        <div style="font-weight: 700; color: var(--biru-malam); font-family: monospace;">{{ $asesi->profilAsesi->nik ?? '-' }}</div>
                    </div>
                    <div>
                        <div style="font-size: 0.75rem; color: var(--abu-teks); font-weight: 700; text-transform: uppercase;">Tempat / Tgl Lahir</div>
                        <div style="font-weight: 600; color: var(--biru-malam);">{{ $asesi->profilAsesi->tempat_lahir ?? '-' }}, {{ $asesi->profilAsesi->tanggal_lahir ? date('d M Y', strtotime($asesi->profilAsesi->tanggal_lahir)) : '-' }}</div>
                    </div>
                    <div>
                        <div style="font-size: 0.75rem; color: var(--abu-teks); font-weight: 700; text-transform: uppercase;">Jenis Kelamin</div>
                        <div style="font-weight: 600; color: var(--biru-malam);">{{ $asesi->profilAsesi->jenis_kelamin === 'L' ? 'Laki-Laki' : ($asesi->profilAsesi->jenis_kelamin === 'P' ? 'Perempuan' : '-') }}</div>
                    </div>
                    <div>
                        <div style="font-size: 0.75rem; color: var(--abu-teks); font-weight: 700; text-transform: uppercase;">Instansi / Sekolah</div>
                        <div style="font-weight: 600; color: var(--biru-malam);">{{ $asesi->profilAsesi->nama_sekolah_instansi ?? '-' }}</div>
                    </div>
                    <div>
                        <div style="font-size: 0.75rem; color: var(--abu-teks); font-weight: 700; text-transform: uppercase;">Jurusan / Program Studi</div>
                        <div style="font-weight: 600; color: var(--biru-malam);">{{ $asesi->profilAsesi->jurusan ?? '-' }}</div>
                    </div>
                    <div>
                        <div style="font-size: 0.75rem; color: var(--abu-teks); font-weight: 700; text-transform: uppercase;">Alamat Domisili</div>
                        <div style="font-weight: 600; color: var(--biru-malam); font-size: 0.88rem;">{{ $asesi->profilAsesi->alamat_rumah ?? '-' }}</div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- RIWAYAT PENDAFTARAN, BERKAS, & HASIL ASESMEN -->
    <div class="kartu">
        <h3 style="color: var(--biru-malam); margin-bottom: 1.25rem;">
            Riwayat Pendaftaran Skema & Hasil Asesmen
        </h3>

        @forelse($asesi->pendaftaranAsesi as $index => $p)
            <div style="border: 1px solid var(--biru-soft); border-radius: var(--radius-md); padding: 1.5rem; margin-bottom: 1.5rem; background: #ffffff;">
                <div style="display: flex; justify-content: space-between; align-items: flex-start; flex-wrap: wrap; gap: 1rem; margin-bottom: 1rem; border-bottom: 1px solid #f1f5f9; padding-bottom: 0.75rem;">
                    <div>
                        <div style="font-size: 0.8rem; font-weight: 700; color: var(--abu-teks);">No. Pendaftaran: <span class="font-mono" style="color: var(--biru-utama);">{{ $p->nomor_pendaftaran }}</span></div>
                        <h4 style="color: var(--biru-malam); font-size: 1.15rem; margin-top: 0.25rem; margin-bottom: 0.25rem;">{{ $p->skema->nama_skema ?? 'Skema Sertifikasi' }}</h4>
                        <div style="font-size: 0.8rem; color: var(--abu-teks);">Kode Skema: <span class="font-mono">{{ $p->skema->kode_skema ?? '-' }}</span> &bull; Kategori: {{ $p->skema->kategori ?? '-' }}</div>
                    </div>
                    <div>
                        <a href="{{ route('admin.detail-verifikasi', $p->id) }}" class="tombol tombol-utama tombol-sm">
                            Periksa & Verifikasi Berkas
                        </a>
                    </div>
                </div>

                <!-- 3 KOLOM STATUS -->
                <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(220px, 1fr)); gap: 1rem;">
                    
                    <!-- 1. STATUS VERIFIKASI BERKAS -->
                    <div style="background: #f8fafc; padding: 1rem; border-radius: var(--radius-sm); border: 1px solid #e2e8f0;">
                        <div style="font-size: 0.75rem; font-weight: 700; color: var(--abu-teks); text-transform: uppercase;">1. Verifikasi Berkas APL-01</div>
                        <div style="margin-top: 0.35rem;">
                            @if($p->status_pendaftaran === 'diverifikasi')
                                <span class="lencana lencana-hijau">Diverifikasi (Diterima)</span>
                            @elseif($p->status_pendaftaran === 'ditolak')
                                <span class="lencana lencana-merah">Ditolak</span>
                            @else
                                <span class="lencana lencana-amber">Menunggu Verifikasi</span>
                            @endif
                        </div>
                        <div style="font-size: 0.78rem; color: var(--abu-teks); margin-top: 0.35rem;">
                            Total Berkas Diunggah: {{ $p->dokumen->count() }} Dokumen
                        </div>
                    </div>

                    <!-- 2. JADWAL ASESMEN & ASESOR -->
                    <div style="background: #f8fafc; padding: 1rem; border-radius: var(--radius-sm); border: 1px solid #e2e8f0;">
                        <div style="font-size: 0.75rem; font-weight: 700; color: var(--abu-teks); text-transform: uppercase;">2. Jadwal Uji & Asesor</div>
                        @if($p->jadwal)
                            <div style="font-weight: 700; color: var(--biru-malam); font-size: 0.9rem; margin-top: 0.25rem;">
                                {{ date('d M Y', strtotime($p->jadwal->tanggal_uji)) }}
                            </div>
                            <div style="font-size: 0.78rem; color: var(--abu-teks);">
                                TUK: {{ $p->jadwal->nama_tuk }}<br>
                                Asesor: {{ $p->jadwal->asesor->nama_lengkap ?? ($p->asesor->nama_lengkap ?? 'Belum Ditugaskan') }}
                            </div>
                        @else
                            <div style="color: var(--abu-teks); font-style: italic; font-size: 0.85rem; margin-top: 0.35rem;">
                                Belum dijadwalkan
                            </div>
                        @endif
                    </div>

                    <!-- 3. HASIL ASESMEN & KELULUSAN -->
                    <div style="background: #f8fafc; padding: 1rem; border-radius: var(--radius-sm); border: 1px solid #e2e8f0;">
                        <div style="font-size: 0.75rem; font-weight: 700; color: var(--abu-teks); text-transform: uppercase;">3. Hasil & Rekomendasi</div>
                        <div style="margin-top: 0.35rem;">
                            @if($p->rekomendasi)
                                @if($p->rekomendasi->keputusan === 'kompeten')
                                    <span class="lencana lencana-hijau">KOMPETEN (K)</span>
                                @else
                                    <span class="lencana lencana-merah">BELUM KOMPETEN (BK)</span>
                                @endif
                                <div style="font-size: 0.75rem; color: var(--abu-teks); margin-top: 0.25rem;">
                                    Tgl: {{ date('d M Y', strtotime($p->rekomendasi->tanggal_rekomendasi)) }}
                                </div>
                            @else
                                <span class="lencana lencana-amber">Belum Ada Keputusan</span>
                            @endif
                        </div>
                    </div>

                </div>
            </div>
        @empty
            <div style="text-align: center; color: var(--abu-teks); padding: 2.5rem 1rem;">
                Asesi ini belum pernah mendaftar pada skema sertifikasi manapun.
            </div>
        @endforelse
    </div>

</div>
@endsection
