@extends('tata-letak.dasbor')

@section('judul', 'FR.APL.01 - Bagian 3.2: TTD & Pengesahan')

@push('css')
    <link rel="stylesheet" href="{{ asset('css/asesi/pendaftaran-bagian32.css') }}">
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
        <a href="{{ route('asesi.pendaftaran.bagian31') }}" class="step-pill selesai">
            <div class="step-num">3.1</div>
            <div>Bagian 3.1: Syarat Dasar</div>
        </a>
        <div class="step-pill aktif">
            <div class="step-num">3.2</div>
            <div>Bagian 3.2: TTD & Admin</div>
        </div>
    </div>

    <div class="card-form-apl">
        <!-- KOP RESMI DOKUMEN STANDAR BNSP -->
        @include('komponen.kop-formulir-bnsp', [
            'kodeForm' => 'FR.APL.01',
            'judulForm' => 'PERMOHONAN SERTIFIKASI KOMPETENSI',
            'tipeDokumen' => 'Bagian 3.2: TTD & Pengesahan',
            'subJudul' => 'Bagian 3.2 : Bukti Administratif & Lembar Pengesahan Tanda Tangan Permohonan Sertifikasi.'
        ])

        <form action="{{ route('asesi.pendaftaran.simpan32') }}" method="POST" enctype="multipart/form-data">
            @csrf

            @php
                $dokumenKtp = isset($pendaftaranAktif) ? $pendaftaranAktif->dokumen->where('jenis_dokumen', 'KTP / Kartu Pelajar')->first() : null;
                $dokumenFoto = isset($pendaftaranAktif) ? $pendaftaranAktif->dokumen->where('jenis_dokumen', 'Pasfoto 3x4 Background Merah')->first() : null;
                $ttdCanvas = $draftData['tanda_tangan_asesi'] ?? '';
            @endphp

            <div class="tabel-wadah" style="margin-bottom: 2rem;">
                <table class="tabel-custom">
                    <thead>
                        <tr>
                            <th style="width: 50px;">No.</th>
                            <th style="width: 45%;">Bukti Administratif</th>
                            <th>Unggah Berkas Persyaratan (PDF, JPG, PNG - Maks 5MB)</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr>
                            <td style="vertical-align: top; padding-top: 1rem;">1</td>
                            <td style="vertical-align: top; padding-top: 1rem;">
                                <strong style="color: var(--biru-malam);">Kartu Pelajar / Kartu Mahasiswa / KTP / KK</strong>
                                <p style="font-size: 0.8rem; color: var(--abu-teks); margin-top: 0.25rem;">
                                    Dokumen resmi bukti identitas diri pemohon.
                                </p>
                            </td>
                            <td style="vertical-align: top;">
                                @if($dokumenKtp)
                                    <div style="margin-bottom: 0.5rem;">
                                        @if($dokumenKtp->status_verifikasi === 'tidak_valid')
                                            <span class="lencana lencana-merah" style="margin-bottom: 0.35rem; display: inline-block;">
                                                TIDAK VALID (Silakan Upload File Baru)
                                            </span>
                                            @if($dokumenKtp->catatan)
                                                <div style="background: #fef2f2; color: #991b1b; padding: 0.5rem 0.75rem; border-radius: var(--radius-sm); border: 1px solid #fecaca; font-size: 0.82rem; margin-top: 0.25rem;">
                                                    <strong>Catatan Verifikator Admin:</strong> "{{ $dokumenKtp->catatan }}"
                                                </div>
                                            @endif
                                        @else
                                            <span class="lencana lencana-hijau">
                                                File Terunggah: <strong>{{ $dokumenKtp->nama_dokumen }}</strong>
                                            </span>
                                        @endif
                                    </div>
                                @endif

                                <input type="file" name="file_ktp" class="input-control" accept=".pdf,.jpg,.jpeg,.png" {{ ($dokumenKtp && $dokumenKtp->status_verifikasi !== 'tidak_valid') ? '' : 'required' }} style="padding: 0.4rem 0.6rem; font-size: 0.88rem;">
                                <small style="color: var(--abu-teks); display: block; margin-top: 0.35rem;">
                                    * {{ $dokumenKtp ? 'Pilih file baru jika ingin memperbarui berkas' : 'Wajib mengunggah file KTP/Kartu Pelajar' }}
                                </small>
                            </td>
                        </tr>
                        <tr>
                            <td style="vertical-align: top; padding-top: 1rem;">2</td>
                            <td style="vertical-align: top; padding-top: 1rem;">
                                <strong style="color: var(--biru-malam);">Pas Foto 3 X 4 (Latar Belakang Merah)</strong>
                                <p style="font-size: 0.8rem; color: var(--abu-teks); margin-top: 0.25rem;">
                                    Foto formal terbaru 3x4 latar belakang merah untuk cetak sertifikat.
                                </p>
                            </td>
                            <td style="vertical-align: top;">
                                @if($dokumenFoto)
                                    <div style="margin-bottom: 0.5rem;">
                                        @if($dokumenFoto->status_verifikasi === 'tidak_valid')
                                            <span class="lencana lencana-merah" style="margin-bottom: 0.35rem; display: inline-block;">
                                                TIDAK VALID (Silakan Upload File Baru)
                                            </span>
                                            @if($dokumenFoto->catatan)
                                                <div style="background: #fef2f2; color: #991b1b; padding: 0.5rem 0.75rem; border-radius: var(--radius-sm); border: 1px solid #fecaca; font-size: 0.82rem; margin-top: 0.25rem;">
                                                    <strong>Catatan Verifikator Admin:</strong> "{{ $dokumenFoto->catatan }}"
                                                </div>
                                            @endif
                                        @else
                                            <span class="lencana lencana-hijau">
                                                File Terunggah: <strong>{{ $dokumenFoto->nama_dokumen }}</strong>
                                            </span>
                                        @endif
                                    </div>
                                @endif

                                <input type="file" name="file_foto" class="input-control" accept=".jpg,.jpeg,.png" {{ ($dokumenFoto && $dokumenFoto->status_verifikasi !== 'tidak_valid') ? '' : 'required' }} style="padding: 0.4rem 0.6rem; font-size: 0.88rem;">
                                <small style="color: var(--abu-teks); display: block; margin-top: 0.35rem;">
                                    * {{ $dokumenFoto ? 'Pilih file baru jika ingin memperbarui berkas' : 'Wajib mengunggah file Pasfoto 3x4' }}
                                </small>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>

            <!-- LEMBAR PERSETUJUAN & TTD DUA KOLOM -->
            <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 1.5rem; margin-top: 2rem;">
                <!-- KOLOM 1: REKOMENDASI (DIISI OLEH LSP/ADMIN) -->
                <div style="background: var(--biru-bg); padding: 1.5rem; border-radius: var(--radius-md); border: 1px solid var(--biru-soft);">
                    <h4 style="color: var(--biru-malam); margin-bottom: 0.75rem;">Rekomendasi (diisi oleh LSP):</h4>
                    <p style="font-size: 0.85rem; color: var(--abu-teks); margin-bottom: 1rem;">
                        Berdasarkan ketentuan persyaratan dasar, maka pemohon:
                    </p>
                    <div style="display: flex; gap: 1rem; margin-bottom: 1rem;">
                        <label style="display: flex; align-items: center; gap: 0.35rem; font-weight: 700;">
                            <input type="checkbox" disabled {{ ($draftData['rekomendasi_admin_status'] ?? '') === 'diterima' ? 'checked' : '' }}> Diterima
                        </label>
                        <label style="display: flex; align-items: center; gap: 0.35rem; font-weight: 700;">
                            <input type="checkbox" disabled {{ ($draftData['rekomendasi_admin_status'] ?? '') === 'tidak_diterima' ? 'checked' : '' }}> Tidak Diterima
                        </label>
                    </div>
                    <small style="color: var(--abu-teks); display: block;">* Status rekomendasi & catatan akan diisi oleh Admin LSP saat verifikasi berkas.</small>
                </div>

                <!-- KOLOM 2: PEMOHON / KANDIDAT (ASESI) TTD CANVAS POPUP -->
                <div style="background: var(--putih); padding: 1.5rem; border-radius: var(--radius-md); border: 2px dashed var(--biru-muda); text-align: center;">
                    <h4 style="color: var(--biru-malam); margin-bottom: 0.5rem;">Pemohon / Kandidat (Asesi):</h4>
                    <div style="font-weight: 700; color: var(--biru-utama); margin-bottom: 0.75rem;">{{ $pengguna->nama_lengkap }}</div>

                    <!-- INPUT HIDDEN BASE64 TTD -->
                    <input type="hidden" name="tanda_tangan_asesi" id="input-ttd-asesi-base64" value="{{ old('tanda_tangan_asesi', $ttdCanvas) }}">

                    <!-- PREVIEW TTD CANVAS -->
                    <div id="box-preview-ttd-asesi" style="margin: 0.75rem 0; {{ $ttdCanvas ? '' : 'display: none;' }}">
                        <img id="preview-ttd-asesi-img" src="{{ $ttdCanvas }}" alt="TTD Asesi" style="max-height: 90px; border: 1px solid var(--biru-soft); padding: 0.25rem; background: var(--putih); border-radius: var(--radius-sm);">
                    </div>

                    <button type="button" class="tombol tombol-utama tombol-sm" onclick="bukaModal('modalCanvasTtd')" style="margin-top: 0.5rem;">
                        {{ $ttdCanvas ? 'Ubah Tanda Tangan Canvas' : 'Gambar Tanda Tangan Digital' }}
                    </button>
                    <small style="color: var(--abu-teks); display: block; margin-top: 0.5rem;">Tanggal: {{ date('d F Y') }}</small>
                </div>
            </div>

            <!-- KOLOM ADMIN LSP FOOTER -->
            <div style="margin-top: 1.5rem; background: var(--biru-bg); padding: 1.25rem; border-radius: var(--radius-md); border: 1px solid var(--biru-soft); display: flex; justify-content: space-between; align-items: center;">
                <div>
                    <strong style="color: var(--biru-malam);">Admin LSP:</strong>
                    <div style="font-size: 0.88rem; color: var(--abu-teks);">Tanda tangan & persetujuan Admin akan dibubuhkan di Halaman Verifikasi Admin.</div>
                </div>
                <div style="text-align: right;">
                    <span class="lencana lencana-biru">Verifikasi Admin</span>
                </div>
            </div>

            <div style="margin-top: 2.5rem; display: flex; justify-content: space-between;">
                <a href="{{ route('asesi.pendaftaran.bagian31') }}" class="tombol tombol-sekunder">
                    Kembali ke Bagian 3.1
                </a>
                <button type="submit" class="tombol tombol-sukses" style="padding: 0.85rem 2.2rem; font-size: 1rem;">
                    Selesaikan FR.APL.01 & Kirim Permohonan
                </button>
            </div>
        </form>
    </div>
</div>

<!-- MODAL POPUP CANVAS SIGNATURE PAD FOR ASESI -->
<div class="modal-overlay" id="modalCanvasTtd">
    <div class="modal-konten" style="max-width: 550px; text-align: center;">
        <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 1rem;">
            <h3 style="color: var(--biru-malam);">Gambar Tanda Tangan Asesi</h3>
            <button onclick="tutupModal('modalCanvasTtd')" style="background: none; border: none; font-size: 1.4rem; cursor: pointer;">&times;</button>
        </div>

        <p style="font-size: 0.88rem; color: var(--abu-teks); margin-bottom: 1rem;">
            Gunakan tetikus (mouse) atau jari Anda (layar sentuh) untuk menggambar tanda tangan digital pada kotak di bawah ini:
        </p>

        <canvas id="canvas-ttd-asesi" width="460" height="200" class="canvas-signature-pad"></canvas>

        <div style="margin-top: 1.25rem; display: flex; gap: 0.75rem; justify-content: center;">
            <button type="button" class="tombol tombol-sekunder tombol-sm" id="btn-clear-canvas">
                Bersihkan Canvas
            </button>
            <button type="button" class="tombol tombol-utama tombol-sm" id="btn-simpan-canvas">
                Gunakan Tanda Tangan Ini
            </button>
        </div>
    </div>
</div>
@endsection

@push('js')
    <script src="{{ asset('js/asesi/pendaftaran-bagian32.js') }}"></script>
@endpush
