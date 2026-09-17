@extends('tata-letak.dasbor')

@section('judul', 'FR.APL.01 - Bagian 3.1: Syarat Dasar')

@push('css')
    <link rel="stylesheet" href="{{ asset('css/asesi/pendaftaran-bagian31.css') }}">
@endpush

@section('konten')
<div style="max-width: 1050px;" class="animasi-slide">
    <!-- STEPPER NAVIGATION -->
    <div class="stepper-header-page">
        <a href="{{ route('asesi.pendaftaran.bagian1') }}" class="step-pill selesai">
            <div class="step-num">1</div>
            <div>Bagian 1: Data Pemohon</div>
        </a>
        <a href="{{ route('asesi.pendaftaran.bagian2') }}" class="step-pill selesai">
            <div class="step-num">2</div>
            <div>Bagian 2: Data Sertifikasi</div>
        </a>
        <div class="step-pill aktif">
            <div class="step-num">3.1</div>
            <div>Bagian 3.1: Syarat Dasar</div>
        </div>
        <div class="step-pill">
            <div class="step-num">3.2</div>
            <div>Bagian 3.2: TTD & Admin</div>
        </div>
    </div>

    <div class="card-form-apl">
        <!-- KOP RESMI DOKUMEN STANDAR BNSP -->
        @include('komponen.kop-formulir-bnsp', [
            'kodeForm' => 'FR.APL.01',
            'judulForm' => 'PERMOHONAN SERTIFIKASI KOMPETENSI',
            'tipeDokumen' => 'Bagian 3.1: Bukti Syarat Dasar',
            'subJudul' => 'Bagian 3.1 : Bukti Persyaratan Dasar Pemohon - Pilih status pemenuhan kelengkapan bukti persyaratan dasar untuk verifikasi permohonan sertifikasi Anda.'
        ])

        <form action="{{ route('asesi.pendaftaran.simpan31') }}" method="POST" enctype="multipart/form-data">
            @csrf

            @php
                $dokumenRapor = isset($pendaftaranAktif) ? $pendaftaranAktif->dokumen->where('jenis_dokumen', 'Ijazah / Rapor Terakhir')->first() : null;
                $dokumenPkl = isset($pendaftaranAktif) ? $pendaftaranAktif->dokumen->where('jenis_dokumen', 'Portofolio Sertifikat/Karya')->first() : null;
            @endphp

            <div class="tabel-wadah" style="margin-top: 1rem;">
                <table class="tabel-custom">
                    <thead>
                        <tr>
                            <th style="width: 50px;">No.</th>
                            <th style="width: 45%;">Bukti Persyaratan Dasar</th>
                            <th>Unggah Berkas Persyaratan (PDF, JPG, PNG - Maks 5MB)</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr>
                            <td style="vertical-align: top; padding-top: 1rem;">1</td>
                            <td style="vertical-align: top; padding-top: 1rem;">
                                <strong style="color: var(--biru-malam);">Foto copy Rapor Kelas 10 – 12 (Semester 1-6) / Ijazah</strong>
                                <p style="font-size: 0.8rem; color: var(--abu-teks); margin-top: 0.25rem;">
                                    Berisi nilai mata pelajaran/keahlian yang relevan dengan skema uji kompetensi.
                                </p>
                            </td>
                            <td style="vertical-align: top;">
                                @if($dokumenRapor)
                                    <div style="margin-bottom: 0.5rem;">
                                        @if($dokumenRapor->status_verifikasi === 'tidak_valid')
                                            <span class="lencana lencana-merah" style="margin-bottom: 0.35rem; display: inline-block;">
                                                TIDAK VALID (Silakan Upload File Baru)
                                            </span>
                                            @if($dokumenRapor->catatan)
                                                <div style="background: #fef2f2; color: #991b1b; padding: 0.5rem 0.75rem; border-radius: var(--radius-sm); border: 1px solid #fecaca; font-size: 0.82rem; margin-top: 0.25rem;">
                                                    <strong>Catatan Verifikator Admin:</strong> "{{ $dokumenRapor->catatan }}"
                                                </div>
                                            @endif
                                        @else
                                            <span class="lencana lencana-hijau">
                                                File Terunggah: <strong>{{ $dokumenRapor->nama_dokumen }}</strong>
                                            </span>
                                        @endif
                                    </div>
                                @endif

                                <input type="file" name="file_rapor" class="input-control" accept=".pdf,.jpg,.jpeg,.png" {{ ($dokumenRapor && $dokumenRapor->status_verifikasi !== 'tidak_valid') ? '' : 'required' }} style="padding: 0.4rem 0.6rem; font-size: 0.88rem;">
                                <small style="color: var(--abu-teks); display: block; margin-top: 0.35rem;">
                                    * {{ $dokumenRapor ? 'Pilih file baru jika ingin memperbarui berkas' : 'Wajib mengunggah file Rapor/Ijazah' }}
                                </small>
                            </td>
                        </tr>
                        <tr>
                            <td style="vertical-align: top; padding-top: 1rem;">2</td>
                            <td style="vertical-align: top; padding-top: 1rem;">
                                <strong style="color: var(--biru-malam);">Foto copy Sertifikat PKL / Sertifikat Pelatihan</strong>
                                <p style="font-size: 0.8rem; color: var(--abu-teks); margin-top: 0.25rem;">
                                    Sertifikat Praktik Kerja Lapangan atau pelatihan pada bidang keahlian teruji.
                                </p>
                            </td>
                            <td style="vertical-align: top;">
                                @if($dokumenPkl)
                                    <div style="margin-bottom: 0.5rem;">
                                        @if($dokumenPkl->status_verifikasi === 'tidak_valid')
                                            <span class="lencana lencana-merah" style="margin-bottom: 0.35rem; display: inline-block;">
                                                TIDAK VALID (Silakan Upload File Baru)
                                            </span>
                                            @if($dokumenPkl->catatan)
                                                <div style="background: #fef2f2; color: #991b1b; padding: 0.5rem 0.75rem; border-radius: var(--radius-sm); border: 1px solid #fecaca; font-size: 0.82rem; margin-top: 0.25rem;">
                                                    <strong>Catatan Verifikator Admin:</strong> "{{ $dokumenPkl->catatan }}"
                                                </div>
                                            @endif
                                        @else
                                            <span class="lencana lencana-hijau">
                                                File Terunggah: <strong>{{ $dokumenPkl->nama_dokumen }}</strong>
                                            </span>
                                        @endif
                                    </div>
                                @endif

                                <input type="file" name="file_pkl" class="input-control" accept=".pdf,.jpg,.jpeg,.png" {{ ($dokumenPkl && $dokumenPkl->status_verifikasi !== 'tidak_valid') ? '' : 'required' }} style="padding: 0.4rem 0.6rem; font-size: 0.88rem;">
                                <small style="color: var(--abu-teks); display: block; margin-top: 0.35rem;">
                                    * {{ $dokumenPkl ? 'Pilih file baru jika ingin memperbarui berkas' : 'Wajib mengunggah file Sertifikat PKL/Pelatihan' }}
                                </small>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>

            <div style="margin-top: 2.5rem; display: flex; justify-content: space-between;">
                <a href="{{ route('asesi.pendaftaran.bagian2') }}" class="tombol tombol-sekunder">
                    Kembali ke Bagian 2
                </a>
                <button type="submit" class="tombol tombol-utama" style="padding: 0.8rem 2rem;">
                    Simpan & Lanjut ke Bagian 3.2 (TTD & Admin)
                </button>
            </div>
        </form>
    </div>
</div>
@endsection
