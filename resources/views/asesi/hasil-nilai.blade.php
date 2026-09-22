@extends('tata-letak.dasbor')

@section('judul', 'Hasil & Nilai Asesmen')

@push('css')
    <link rel="stylesheet" href="{{ asset('css/asesi/dashboard-asesi.css') }}">
    <style>
        @media print {
            body {
                background: #ffffff !important;
                color: #000000 !important;
            }
            .sidebar, .navbar, .tombol-aksi-cetak, .footer, .btn-kembali {
                display: none !important;
            }
            .wadah-konten, .animasi-slide {
                max-width: 100% !important;
                margin: 0 !important;
                padding: 0 !important;
            }
            .kartu {
                box-shadow: none !important;
                border: 1px solid #cbd5e1 !important;
                page-break-inside: avoid;
            }
        }
    </style>
@endpush

@section('konten')
<div style="max-width: 1050px;" class="animasi-slide">
    <div style="display: flex; justify-content: space-between; align-items: flex-start; flex-wrap: wrap; gap: 1rem; margin-bottom: 2rem;">
        <div>
            <h1 style="font-size: 1.8rem; color: var(--biru-malam); margin-bottom: 0.35rem;">Hasil & Nilai Kelulusan Uji Kompetensi</h1>
            <p style="color: var(--abu-teks); margin: 0;">Rincian penilaian kompetensi per unit dan status rekomendasi kelulusan dari Asesor Penguji</p>
        </div>
        <div class="tombol-aksi-cetak" style="display: flex; gap: 0.5rem; align-items: center;">
            <a href="{{ route('asesi.dasbor') }}" class="tombol tombol-sekunder btn-kembali" style="font-size: 0.88rem;">
                <i class="fa-solid fa-arrow-left" style="margin-right: 0.35rem;"></i> Kembali ke Dasbor
            </a>
            <button type="button" onclick="window.print()" class="tombol tombol-sekunder" style="font-size: 0.88rem; background: #ffffff;">
                <i class="fa-solid fa-print" style="margin-right: 0.35rem;"></i> Cetak Hasil
            </button>
        </div>
    </div>

    @forelse($pendaftaranList as $p)
        @php
            $namaAsesor = $p->rekomendasi->asesor->nama_lengkap 
                ?? $p->asesor->nama_lengkap 
                ?? $p->jadwal->asesor->nama_lengkap 
            $namaAsesor = $p->rekomendasi?->asesor?->nama_lengkap 
                ?? $p->asesor?->nama_lengkap 
                ?? $p->jadwal?->asesor?->nama_lengkap 
                ?? 'Asesor LSP';
            
            $ttdAsesor = $p->rekomendasi->tanda_tangan_asesor 
                ?: ($p->tanda_tangan_asesor ?: ($p->asesor->tanda_tangan ?? null));
            $ttdAsesor = $p->rekomendasi?->tanda_tangan_asesor 
                ?: ($p->tanda_tangan_asesor ?: ($p->asesor?->tanda_tangan ?? null));

            $unitList = $p->skema && $p->skema->unitKompetensi ? $p->skema->unitKompetensi : collect();
            
            // Map penilaian dari tabel penilaian_asesmen
            $penilaianMap = $p->penilaian ? $p->penilaian->keyBy('unit_id') : collect();
            
            // Map rekomendasi unit dari FR.AK.02 jika ada
            $ak02Units = ($p->ak02 && is_array($p->ak02->rekomendasi_unit)) ? $p->ak02->rekomendasi_unit : [];

            $isSelesai = ($p->status_pendaftaran === 'selesai') || !empty($p->rekomendasi);
            $keputusanAkhir = $p->rekomendasi->keputusan ?? ($p->ak02->keputusan_final ?? null);
            // Validasi ketat: Asesmen HANYA dianggap selesai jika status pendaftaran adalah 'selesai' dan ada rekomendasi final
            $isSelesai = ($p->status_pendaftaran === 'selesai') && !empty($p->rekomendasi) && !empty($p->rekomendasi->keputusan);
            $keputusanAkhir = $isSelesai ? $p->rekomendasi->keputusan : null;
        @endphp

        <div class="kartu" style="margin-bottom: 2rem; padding: 2rem; border-radius: 12px;">
            <!-- Header Kartu: Info Skema & Keputusan Akhir -->
            <div style="display: flex; justify-content: space-between; align-items: flex-start; flex-wrap: wrap; gap: 1rem; margin-bottom: 1.5rem; padding-bottom: 1.25rem; border-bottom: 1px solid #e2e8f0;">
                <div>
                    <div style="display: flex; align-items: center; gap: 0.5rem; margin-bottom: 0.35rem;">
                        <span class="lencana lencana-biru">{{ $p->skema->kode_skema ?? 'SKEMA' }}</span>
                        @if($p->jadwal)
                            <span class="lencana" style="background: #f1f5f9; color: #475569; font-weight: 600;">
                                {{ $p->jadwal->kode_jadwal ?? 'Jadwal Asesmen' }}
                            </span>
                        @endif
                    </div>
                    <h2 style="color: var(--biru-malam); font-size: 1.35rem; margin: 0 0 0.35rem 0; font-weight: 700;">
                        {{ $p->skema->nama_skema ?? 'Skema Sertifikasi' }}
                    </h2>
                    <div style="font-size: 0.88rem; color: var(--abu-teks); display: flex; gap: 1.25rem; flex-wrap: wrap;">
                        <span>No. Registrasi: <strong style="color: #1e293b;">{{ $p->nomor_pendaftaran }}</strong></span>
                        <span>Asesor Penguji: <strong style="color: #1e293b;">{{ $namaAsesor }}</strong></span>
                        @if($p->jadwal && $p->jadwal->tanggal_uji)
                            <span>Tanggal Asesmen: <strong style="color: #1e293b;">{{ date('d F Y', strtotime($p->jadwal->tanggal_uji)) }}</strong></span>
                        @endif
                    </div>
                </div>

                <div style="text-align: right;">
                    <div style="font-size: 0.78rem; font-weight: 700; color: #64748b; text-transform: uppercase; margin-bottom: 0.35rem;">
                        Keputusan Akhir Asesmen:
                    </div>
                    @if($keputusanAkhir === 'kompeten')
                    @if($isSelesai && $keputusanAkhir === 'kompeten')
                        <span class="lencana lencana-hijau" style="padding: 0.6rem 1.25rem; font-size: 1rem; font-weight: 800; display: inline-flex; align-items: center; gap: 0.4rem;">
                            <i class="fa-solid fa-circle-check"></i> KOMPETEN (K)
                        </span>
                    @elseif($keputusanAkhir === 'belum_kompeten')
                    @elseif($isSelesai && $keputusanAkhir === 'belum_kompeten')
                        <span class="lencana lencana-merah" style="padding: 0.6rem 1.25rem; font-size: 1rem; font-weight: 800; display: inline-flex; align-items: center; gap: 0.4rem;">
                            <i class="fa-solid fa-circle-xmark"></i> BELUM KOMPETEN (BK)
                        </span>
                    @else
                        <span class="lencana lencana-amber" style="padding: 0.6rem 1.25rem; font-size: 0.9rem; display: inline-flex; align-items: center; gap: 0.4rem;">
                            <i class="fa-solid fa-hourglass-half"></i> Proses Penilaian Asesor
                            <i class="fa-solid fa-hourglass-half"></i> Asesmen Belum Selesai
                        </span>
                    @endif
                </div>
            </div>

            <!-- Tabel Rincian Unit Kompetensi -->
            <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 0.75rem;">
                <h4 style="color: var(--biru-malam); margin: 0; font-size: 1.05rem; font-weight: 700;">
                    Rincian Penilaian Per Unit Kompetensi:
                </h4>
                <small style="color: #64748b;">Total: {{ $unitList->count() }} Unit Kompetensi</small>
            </div>

            <div class="tabel-wadah" style="margin-bottom: 1.5rem;">
                <table class="tabel-custom">
                    <thead>
                        <tr>
                            <th style="width: 60px; text-align: center;">No.</th>
                            <th style="width: 170px;">Kode Unit</th>
                            <th>Judul Unit Kompetensi</th>
                            <th style="width: 170px; text-align: center;">Status Kompetensi</th>
                            <th>Catatan / Umpan Balik Asesor</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($unitList as $idx => $unit)
                            @php
                                $nilaiModel = $penilaianMap->get($unit->id);
                                $ak02Item = $ak02Units[$unit->id] ?? null;

                                $nilaiStatus = null;
                                $catatanUnit = null;

                                if ($nilaiModel) {
                                    $nilaiStatus = $nilaiModel->nilai_kompetensi;
                                    $catatanUnit = $nilaiModel->catatan_asesor;
                                } elseif ($ak02Item) {
                                    $nilaiStatus = is_array($ak02Item) ? ($ak02Item['hasil'] ?? null) : $ak02Item;
                                    $catatanUnit = is_array($ak02Item) ? ($ak02Item['catatan'] ?? null) : null;
                                } elseif ($isSelesai && $keputusanAkhir === 'kompeten') {
                                    $nilaiStatus = 'K';
                                    $catatanUnit = 'Kompeten sesuai pemenuhan bukti observasi dan ujian.';
                                } elseif ($isSelesai && $keputusanAkhir === 'belum_kompeten') {
                                    $nilaiStatus = 'BK';
                                    $catatanUnit = 'Belum memenuhi kriteria unjuk kerja pada unit ini.';
                                if ($isSelesai) {
                                    if ($nilaiModel) {
                                        $nilaiStatus = $nilaiModel->nilai_kompetensi;
                                        $catatanUnit = $nilaiModel->catatan_asesor;
                                    } elseif ($ak02Item) {
                                        $nilaiStatus = is_array($ak02Item) ? ($ak02Item['hasil'] ?? null) : $ak02Item;
                                        $catatanUnit = is_array($ak02Item) ? ($ak02Item['catatan'] ?? null) : null;
                                    } elseif ($keputusanAkhir === 'kompeten') {
                                        $nilaiStatus = 'K';
                                        $catatanUnit = 'Kompeten sesuai pemenuhan bukti observasi dan ujian.';
                                    } elseif ($keputusanAkhir === 'belum_kompeten') {
                                        $nilaiStatus = 'BK';
                                        $catatanUnit = 'Belum memenuhi kriteria unjuk kerja pada unit ini.';
                                    }
                                }
                            @endphp
                            <tr>
                                <td style="text-align: center; color: #64748b;">{{ $idx + 1 }}</td>
                                <td>
                                    <strong style="color: var(--biru-utama); font-family: monospace; font-size: 0.9rem;">
                                        {{ $unit->kode_unit }}
                                    </strong>
                                </td>
                                <td>
                                    <div style="font-weight: 600; color: #1e293b;">{{ $unit->judul_unit }}</div>
                                    @if($unit->jenis_standar)
                                        <small style="color: #64748b;">Standar: {{ $unit->jenis_standar }}</small>
                                    @endif
                                </td>
                                <td style="text-align: center;">
                                    @if($nilaiStatus === 'K' || strtoupper((string)$nilaiStatus) === 'KOMPETEN')
                                    @if($isSelesai && ($nilaiStatus === 'K' || strtoupper((string)$nilaiStatus) === 'KOMPETEN'))
                                        <span class="lencana lencana-hijau" style="font-weight: 700;">
                                            Kompeten (K)
                                        </span>
                                    @elseif($nilaiStatus === 'BK' || strtoupper((string)$nilaiStatus) === 'BELUM_KOMPETEN')
                                    @elseif($isSelesai && ($nilaiStatus === 'BK' || strtoupper((string)$nilaiStatus) === 'BELUM_KOMPETEN'))
                                        <span class="lencana lencana-merah" style="font-weight: 700;">
                                            Belum Kompeten (BK)
                                        </span>
                                    @else
                                        <span class="lencana lencana-amber" style="font-size: 0.8rem;">
                                            Sedang Dinilai
                                            Menunggu Ujian / Penilaian
                                        </span>
                                    @endif
                                </td>
                                <td>
                                    <span style="color: {{ !empty($catatanUnit) ? '#334155' : '#94a3b8' }}; font-size: 0.9rem;">
                                        {{ $catatanUnit ?? ($isSelesai ? 'Memenuhi kriteria unjuk kerja.' : 'Belum ada catatan khusus.') }}
                                        {{ $catatanUnit ?? ($isSelesai ? 'Memenuhi kriteria unjuk kerja.' : 'Belum diuji oleh Asesor Penguji.') }}
                                    </span>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="5" style="text-align: center; color: var(--abu-teks); padding: 1.5rem;">
                                    Unit kompetensi pada skema ini belum dikonfigurasi.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <!-- Kotak Umpan Balik & Rekomendasi Asesor -->
            @if($p->rekomendasi)
            <!-- Kotak Umpan Balik & Rekomendasi Asesor (HANYA MUNCUL JIKA ASESMEN SUDAH SELESAI) -->
            @if($isSelesai && $p->rekomendasi)
                <div style="background: var(--biru-bg); padding: 1.5rem; border-radius: var(--radius-md); border: 1px solid var(--biru-soft);">
                    <div style="display: flex; justify-content: space-between; align-items: flex-start; flex-wrap: wrap; gap: 1.5rem;">
                        <div style="flex: 1; min-width: 280px;">
                            <div style="font-weight: 700; color: var(--biru-malam); margin-bottom: 0.5rem; font-size: 0.98rem;">
                                <i class="fa-solid fa-comment-dots" style="color: var(--biru-utama); margin-right: 0.35rem;"></i>
                                Umpan Balik & Catatan Rekomendasi Asesor:
                            </div>
                            <blockquote style="margin: 0; padding: 0.75rem 1rem; background: #ffffff; border-left: 4px solid var(--biru-utama); border-radius: 4px; color: #1e293b; font-size: 0.92rem; line-height: 1.5;">
                                "{{ $p->rekomendasi->catatan_rekomendasi ?? 'Asesi telah menyelesaikan seluruh tahapan asesmen sesuai skema sertifikasi.' }}"
                            </blockquote>
                            <div style="margin-top: 0.75rem; font-size: 0.84rem; color: #64748b;">
                                <span>Tanggal Rekomendasi: <strong>{{ date('d F Y', strtotime($p->rekomendasi->tanggal_rekomendasi)) }}</strong></span>
                            </div>
                        </div>

                        <!-- Panel Tanda Tangan Asesor -->
                        <div style="min-width: 220px; text-align: center; background: #ffffff; padding: 1rem; border-radius: 8px; border: 1px solid #e2e8f0;">
                            <div style="font-size: 0.76rem; font-weight: 700; color: #64748b; text-transform: uppercase; margin-bottom: 0.5rem;">
                                Disahkan Oleh Asesor Penguji:
                            </div>
                            @if($ttdAsesor)
                                <div style="display: flex; justify-content: center; align-items: center; min-height: 65px; margin-bottom: 0.5rem;">
                                    @if(\Illuminate\Support\Str::startsWith($ttdAsesor, 'data:image') || \Illuminate\Support\Str::startsWith($ttdAsesor, '<svg'))
                                        <img src="{{ $ttdAsesor }}" alt="Tanda Tangan Asesor" style="max-height: 60px; max-width: 180px; object-fit: contain;">
                                    @else
                                        <img src="{{ asset($ttdAsesor) }}" alt="Tanda Tangan Asesor" style="max-height: 60px; max-width: 180px; object-fit: contain;">
                                    @endif
                                </div>
                            @else
                                <div style="min-height: 65px; display: flex; align-items: center; justify-content: center; color: #94a3b8; font-style: italic; font-size: 0.85rem;">
                                    (Tanda Tangan Digital Terverifikasi)
                                </div>
                            @endif
                            <strong style="color: var(--biru-malam); display: block; font-size: 0.92rem;">{{ $namaAsesor }}</strong>
                            <small style="color: #64748b; display: block; font-size: 0.75rem;">Asesor Kompetensi LSP</small>
                        </div>
                    </div>

                    <!-- Tautan Dokumen Resmi -->
                    <div style="margin-top: 1.25rem; padding-top: 1rem; border-top: 1px dashed #cbd5e1; display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 0.75rem;" class="tombol-aksi-cetak">
                        <span style="font-size: 0.84rem; color: #64748b;">
                            <i class="fa-solid fa-file-lines" style="margin-right: 0.35rem;"></i> Dokumen Rekaman Asesmen Resmi (FR.AK.02):
                        </span>
                        <div style="display: flex; gap: 0.5rem;">
                            @if(Route::has('dokumen-asesmen.ak02.cetak'))
                                <a href="{{ route('dokumen-asesmen.ak02.cetak', $p->id) }}" target="_blank" class="tombol tombol-sekunder" style="font-size: 0.84rem; padding: 0.4rem 0.85rem;">
                                    <i class="fa-solid fa-external-link" style="margin-right: 0.35rem;"></i> Cetak Lembar FR.AK.02 Resmi BNSP
                                </a>
                            @endif
                            @if(Route::has('dokumen-asesmen.ak03.show'))
                                <a href="{{ route('dokumen-asesmen.ak03.show', $p->id) }}" class="tombol tombol-utama" style="font-size: 0.84rem; padding: 0.4rem 0.85rem; background: var(--biru-utama);">
                                    <i class="fa-solid fa-pen-to-square" style="margin-right: 0.35rem;"></i> Umpan Balik Asesi (FR.AK.03)
                                </a>
                            @endif
                        </div>
                    </div>
                </div>
            @else
                <!-- KOTAK INFORMASI JIKA ASESMEN BELUM SELESAI -->
                <div style="background: #f8fafc; padding: 1.5rem; border-radius: var(--radius-md); border: 1.5px dashed #cbd5e1; margin-top: 1rem; text-align: center;">
                    <div style="font-size: 2.2rem; color: #94a3b8; margin-bottom: 0.5rem;">
                        <i class="fa-solid fa-clipboard-question"></i>
                    </div>
                    <h4 style="color: var(--biru-malam); margin-bottom: 0.35rem; font-weight: 700;">Tahapan Asesmen Belum Selesai</h4>
                    <p style="color: #64748b; font-size: 0.92rem; max-width: 580px; margin: 0 auto 1.25rem auto;">
                        Hasil kelulusan dan nilai uji kompetensi belum diterbitkan karena pelaksanaan ujian asesmen belum selesai dan belum disahkan oleh Asesor Penguji.
                    </p>
                    <div style="display: flex; justify-content: center; gap: 0.75rem; flex-wrap: wrap;">
                        <a href="{{ route('asesi.dasbor') }}" class="tombol tombol-sekunder" style="font-size: 0.88rem;">
                            <i class="fa-solid fa-arrow-left" style="margin-right: 0.35rem;"></i> Dasbor Utama
                        </a>
                        <a href="{{ route('asesi.ujian', ['pendaftaran_id' => $p->id]) }}" class="tombol tombol-utama" style="font-size: 0.88rem;">
                            Buka Ruang Ujian &rarr;
                        </a>
                    </div>
                </div>
            @endif
        </div>
    @empty
        <div class="kartu" style="text-align: center; padding: 3.5rem 2rem; color: var(--abu-teks); border-radius: 12px;">
            <div style="font-size: 3rem; color: #cbd5e1; margin-bottom: 1rem;">
                <i class="fa-solid fa-clipboard-list"></i>
            </div>
            <h3 style="color: var(--biru-malam); margin-bottom: 0.5rem;">Belum Ada Riwayat Nilai Asesmen</h3>
            <p style="max-width: 500px; margin: 0 auto 1.5rem auto; font-size: 0.92rem;">
                Anda belum memiliki data pendaftaran asesmen yang telah selesai diuji. Silakan daftarkan diri Anda pada skema sertifikasi yang tersedia.
            </p>
            <a href="{{ route('asesi.pendaftaran') }}" class="tombol tombol-utama">
                Mendaftar Skema Sertifikasi &rarr;
            </a>
        </div>
    @endforelse
</div>
@endsection
