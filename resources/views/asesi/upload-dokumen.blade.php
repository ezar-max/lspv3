@extends('tata-letak.dasbor')

@section('judul', 'Upload Dokumen Persyaratan & APL-02')

@push('css')
    <link rel="stylesheet" href="{{ asset('css/asesi/dashboard-asesi.css') }}">
@endpush

@section('konten')
<div style="max-width: 1000px;" class="animasi-slide">
    <div style="margin-bottom: 2rem; display: flex; justify-content: space-between; align-items: flex-start;">
        <div>
            <span class="lencana lencana-biru" style="margin-bottom: 0.5rem;">Nomor Pendaftaran: {{ $pendaftaran->nomor_pendaftaran }}</span>
            <h1 style="font-size: 1.8rem; color: var(--biru-malam);">Unggah Dokumen Requirements & APL-02</h1>
            <p style="color: var(--abu-teks);">Skema: <strong>{{ $pendaftaran->skema->nama_skema }}</strong></p>
        </div>
        <div>
            @if($pendaftaran->status_pendaftaran === 'draft')
                <form action="{{ route('asesi.ajukan', $pendaftaran->id) }}" method="POST">
                    @csrf
                    <button type="submit" class="tombol tombol-sukses" onclick="if({{ $pendaftaran->dokumen->count() }} === 0) { alert('Dokumen persyaratan belum diunggah! Silakan unggah minimal 1 file berkas (KTP / Ijazah / Portofolio APL-02) di form bawah terlebih dahulu.'); return false; } return confirm('Apakah Anda yakin seluruh dokumen telah lengkap dan siap diajukan ke Admin?')">
                        Ajukan Berkas ke Admin
                    </button>
                </form>
            @else
                <span class="lencana lencana-amber" style="padding: 0.6rem 1rem; font-size: 0.9rem;">
                    Status: {{ strtoupper($pendaftaran->status_pendaftaran) }}
                </span>
            @endif
        </div>
    </div>

    @if($pendaftaran->dokumen->count() === 0 && $pendaftaran->status_pendaftaran === 'draft')
        <div style="background: var(--amber-bg); color: #92400e; padding: 1.1rem 1.35rem; border-radius: var(--radius-md); border: 1.5px solid #fcd34d; margin-bottom: 1.75rem;" class="animasi-pulse">
            <div>
                <strong style="font-size: 1rem; color: #78350f;">Dokumen Persyaratan Belum Lengkap!</strong>
                <div style="font-size: 0.88rem; margin-top: 0.2rem;">
                    Anda telah menyelesaikan Formulir FR.APL.01. Silakan unggah berkas persyaratan (seperti KTP, Ijazah, Pasfoto, atau APL-02) menggunakan form di bawah. Halaman ini akan tetap aktif sampai dokumen Anda diunggah dan diajukan.
                </div>
            </div>
        </div>
    @endif

    <div style="display: grid; grid-template-columns: 1fr 2fr; gap: 1.5rem;">
        <!-- FORM UNGGAH FILE -->
        <div class="kartu">
            <h3 style="color: var(--biru-malam); margin-bottom: 1rem;">Form Unggah Berkas</h3>
            <form action="{{ route('asesi.upload-dokumen.simpan', $pendaftaran->id) }}" method="POST" enctype="multipart/form-data">
                @csrf
                <div class="grup-form">
                    <label class="label-form">Jenis Dokumen Persyaratan</label>
                    <select name="jenis_dokumen" class="input-control" required>
                        <option value="KTP / Kartu Pelajar">KTP / Kartu Pelajar</option>
                        <option value="Ijazah / Rapor Terakhir">Ijazah / Rapor Terakhir</option>
                        <option value="Pasfoto 3x4 Background Merah">Pasfoto 3x4 Background Merah</option>
                        <option value="Formulir Mandiri APL-02">Formulir Mandiri APL-02</option>
                        <option value="Portofolio Sertifikat/Karya">Portofolio Sertifikat / Karya</option>
                    </select>
                </div>

                <div class="grup-form">
                    <label class="label-form">Pilih File (PDF, JPG, PNG - Maks 5MB)</label>
                    <input type="file" name="file_dokumen" class="input-control" required accept=".pdf,.jpg,.jpeg,.png">
                </div>

                <button type="submit" class="tombol tombol-utama" style="width: 100%; margin-top: 1rem;">
                    Unggah File
                </button>
            </form>
        </div>

        <!-- DAFTAR DOKUMEN TERUNGGAH -->
        <div class="kartu">
            <h3 style="color: var(--biru-malam); margin-bottom: 1rem;">Berkas Persyaratan Terunggah</h3>
            <div class="tabel-wadah">
                <table class="tabel-custom">
                    <thead>
                        <tr>
                            <th>Jenis Dokumen</th>
                            <th>Nama File</th>
                            <th>Status Verifikasi</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($pendaftaran->dokumen as $d)
                            <tr>
                                <td><strong>{{ $d->jenis_dokumen }}</strong></td>
                                <td>
                                    <a href="{{ asset($d->file_path) }}" target="_blank" style="font-weight: 600;">
                                        {{ Str::limit($d->nama_dokumen, 20) }}
                                    </a>
                                </td>
                                <td>
                                    @if($d->status_verifikasi === 'valid')
                                        <span class="lencana lencana-hijau">Valid</span>
                                    @elseif($d->status_verifikasi === 'tidak_valid')
                                        <span class="lencana lencana-merah">Ditolak</span>
                                    @else
                                        <span class="lencana lencana-amber">Menunggu</span>
                                    @endif
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="3" style="text-align: center; color: var(--abu-teks); padding: 2rem;">
                                    Belum ada berkas terunggah. Silakan unggah berkas KTP, Ijazah, dan Portofolio APL-02.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
@endsection
