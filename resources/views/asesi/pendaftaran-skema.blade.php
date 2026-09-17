@extends('tata-letak.dasbor')

@section('judul', 'FR.APL.01 Permohonan Sertifikasi Kompetensi')

@push('css')
    <link rel="stylesheet" href="{{ asset('css/asesi/pendaftaran-skema.css') }}">
@endpush

@section('konten')
<div style="max-width: 1050px;" class="animasi-slide">
    <div style="margin-bottom: 1.5rem; display: flex; justify-content: space-between; align-items: center;">
        <div>
            <h1 style="font-size: 1.8rem; color: var(--biru-malam);">FR.APL.01. PERMOHONAN SERTIFIKASI KOMPETENSI</h1>
            <p style="color: var(--abu-teks);">Isi formulir permohonan sertifikasi secara lengkap sesuai dokumen fisik standar LSP</p>
        </div>
    </div>

    <!-- STEPPER WIZARD INDICATOR (1 -> 2 -> 3.1 -> 3.2) -->
    <div class="stepper-apl01">
        <div class="item-step aktif" id="indicator-step-1">
            <div class="nomor-step">1</div>
            <div>Bagian 1: Data Pemohon</div>
        </div>
        <div class="item-step" id="indicator-step-2">
            <div class="nomor-step">2</div>
            <div>Bagian 2: Data Sertifikasi</div>
        </div>
        <div class="item-step" id="indicator-step-3">
            <div class="nomor-step">3.1</div>
            <div>Bagian 3.1: Syarat Dasar</div>
        </div>
        <div class="item-step" id="indicator-step-4">
            <div class="nomor-step">3.2</div>
            <div>Bagian 3.2: TTD & Admin</div>
        </div>
    </div>

    <form action="{{ route('asesi.pendaftaran.simpan') }}" method="POST" id="form-apl01-multi">
        @csrf

        <!-- BOX FORM PHYSIC FR.APL.01 -->
        <div class="box-dokumen-apl01">
            <!-- KOP RESMI DOKUMEN STANDAR BNSP -->
            @include('komponen.kop-formulir-bnsp', [
                'kodeForm' => 'FR.APL.01',
                'judulForm' => 'PERMOHONAN SERTIFIKASI KOMPETENSI',
                'tipeDokumen' => 'Permohonan Sertifikasi'
            ])

            <!-- ==========================================
                 BAGIAN 1: RINCIAN DATA PEMOHON SERTIFIKASI
                 ========================================== -->
            <div class="konten-step-apl01 aktif" id="step-apl01-1">
                <div class="subjudul-apl01">Bagian 1 : Rincian Data Pemohon Sertifikasi</div>
                <p style="font-size: 0.88rem; color: var(--abu-teks); margin-bottom: 1.5rem;">
                    Pada bagian ini, cantumkan data pribadi, data pendidikan formal serta data pekerjaan Anda pada saat ini.
                </p>

                <h4 style="color: var(--biru-malam); margin-bottom: 1rem; font-size: 1rem;">a. Data Pribadi</h4>

                <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 1.25rem;">
                    <div class="grup-form">
                        <label class="label-form">Nama Lengkap</label>
                        <input type="text" name="nama_lengkap" class="input-control" value="{{ old('nama_lengkap', $pengguna->nama_lengkap) }}" required>
                    </div>
                    <div class="grup-form">
                        <label class="label-form">No. KTP / NIK (Wajib 16 Digit) <span style="color: var(--merah-bahaya);">*</span></label>
                        <input type="text" name="nik" class="input-control" placeholder="16 digit NIK" minlength="16" maxlength="16" pattern="[0-9]{16}" inputmode="numeric" oninput="this.value = this.value.replace(/[^0-9]/g, '').slice(0, 16)" title="NIK harus berjumlah tepat 16 digit angka" value="{{ old('nik', $profil->nik) }}" required>
                    </div>
                </div>

                <div style="display: grid; grid-template-columns: 1fr 1fr 1fr; gap: 1.25rem;">
                    <div class="grup-form">
                        <label class="label-form">Tempat Lahir</label>
                        <input type="text" name="tempat_lahir" class="input-control" value="{{ old('tempat_lahir', $profil->tempat_lahir) }}" required>
                    </div>
                    <div class="grup-form">
                        <label class="label-form">Tanggal Lahir</label>
                        <input type="date" name="tanggal_lahir" class="input-control" value="{{ old('tanggal_lahir', $profil->tanggal_lahir) }}" max="{{ date('Y-m-d') }}" required>
                    </div>
                    <div class="grup-form">
                        <label class="label-form">Jenis Kelamin</label>
                        <select name="jenis_kelamin" class="input-control" required>
                            <option value="Laki-laki" {{ old('jenis_kelamin', $profil->jenis_kelamin) == 'Laki-laki' ? 'selected' : '' }}>Laki-laki</option>
                            <option value="Perempuan" {{ old('jenis_kelamin', $profil->jenis_kelamin) == 'Perempuan' ? 'selected' : '' }}>Perempuan</option>
                        </select>
                    </div>
                </div>

                <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 1.25rem;">
                    <div class="grup-form">
                        <label class="label-form">Kebangsaan</label>
                        <input type="text" name="kebangsaan" class="input-control" value="{{ old('kebangsaan', 'Indonesia') }}" required>
                    </div>
                    <div class="grup-form">
                        <label class="label-form">Kualifikasi Pendidikan</label>
                        <input type="text" name="pendidikan_terakhir" class="input-control" placeholder="contoh: SMK Teknik Informatika" value="{{ old('pendidikan_terakhir', $profil->pendidikan_terakhir) }}">
                    </div>
                </div>

                <div style="display: grid; grid-template-columns: 3fr 1fr; gap: 1.25rem;">
                    <div class="grup-form">
                        <label class="label-form">Alamat Rumah</label>
                        <input type="text" name="alamat" class="input-control" value="{{ old('alamat', $profil->alamat) }}" required>
                    </div>
                    <div class="grup-form">
                        <label class="label-form">Kode Pos</label>
                        <input type="text" name="kode_pos" class="input-control" placeholder="12340" value="{{ old('kode_pos') }}">
                    </div>
                </div>

                <div style="display: grid; grid-template-columns: 1fr 1fr 1fr; gap: 1.25rem;">
                    <div class="grup-form">
                        <label class="label-form">No. Telepon Rumah</label>
                        <input type="text" name="no_telp_rumah" class="input-control" placeholder="(021) 555-..." value="{{ old('no_telp_rumah') }}">
                    </div>
                    <div class="grup-form">
                        <label class="label-form">No. HP / WhatsApp</label>
                        <input type="text" name="nomor_telepon" class="input-control" value="{{ old('nomor_telepon', $pengguna->nomor_telepon) }}" required>
                    </div>
                    <div class="grup-form">
                        <label class="label-form">E-mail Resmi</label>
                        <input type="email" name="email" class="input-control" value="{{ old('email', $pengguna->email) }}" required readonly style="background: var(--biru-bg);">
                    </div>
                </div>

                <h4 style="color: var(--biru-malam); margin-top: 1.5rem; margin-bottom: 1rem; font-size: 1rem;">b. Data Pekerjaan Sekarang</h4>

                <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 1.25rem;">
                    <div class="grup-form">
                        <label class="label-form">Nama Institusi / Perusahaan</label>
                        <input type="text" name="nama_sekolah_instansi" class="input-control" placeholder="contoh: SMK Negeri 1 Jakarta / PT..." value="{{ old('nama_sekolah_instansi', $profil->nama_sekolah_instansi) }}">
                    </div>
                    <div class="grup-form">
                        <label class="label-form">Jabatan</label>
                        <input type="text" name="pekerjaan" class="input-control" placeholder="contoh: Siswa Vokasi / Staff IT" value="{{ old('pekerjaan', $profil->pekerjaan) }}">
                    </div>
                </div>

                <div style="display: grid; grid-template-columns: 3fr 1fr; gap: 1.25rem;">
                    <div class="grup-form">
                        <label class="label-form">Alamat Kantor / Sekolah</label>
                        <input type="text" name="alamat_kantor" class="input-control" placeholder="Alamat instansi..." value="{{ old('alamat_kantor') }}">
                    </div>
                    <div class="grup-form">
                        <label class="label-form">Kode Pos Kantor</label>
                        <input type="text" name="kode_pos_kantor" class="input-control" placeholder="12340" value="{{ old('kode_pos_kantor') }}">
                    </div>
                </div>

                <div style="display: grid; grid-template-columns: 1fr 1fr 1fr; gap: 1.25rem;">
                    <div class="grup-form">
                        <label class="label-form">No. Telp Kantor</label>
                        <input type="text" name="telp_kantor" class="input-control" placeholder="(021) 777-..." value="{{ old('telp_kantor') }}">
                    </div>
                    <div class="grup-form">
                        <label class="label-form">No. Fax Kantor</label>
                        <input type="text" name="fax_kantor" class="input-control" placeholder="Fax..." value="{{ old('fax_kantor') }}">
                    </div>
                    <div class="grup-form">
                        <label class="label-form">E-mail Kantor</label>
                        <input type="email" name="email_kantor" class="input-control" placeholder="info@sekolah.sch.id" value="{{ old('email_kantor') }}">
                    </div>
                </div>

                <div style="margin-top: 2rem; text-align: right;">
                    <button type="button" class="tombol tombol-utama btn-lanjut-step" data-next="2">
                        Lanjut ke Bagian 2 (Data Sertifikasi)
                    </button>
                </div>
            </div>

            <!-- ==========================================
                 BAGIAN 2: DATA SERTIFIKASI
                 ========================================== -->
            <div class="konten-step-apl01" id="step-apl01-2">
                <div class="subjudul-apl01">Bagian 2 : Data Sertifikasi</div>
                <p style="font-size: 0.88rem; color: var(--abu-teks); margin-bottom: 1.5rem;">
                    Tuliskan Judul dan Nomor Skema Sertifikasi yang Anda ajukan berikut Daftar Unit Kompetensi sesuai kemasan pada skema sertifikasi untuk mendapatkan pengakuan sesuai latar belakang pendidikan.
                </p>

                <div class="grup-form" style="background: var(--biru-bg); padding: 1.25rem; border-radius: var(--radius-md); border: 1px solid var(--biru-soft);">
                    <label class="label-form" style="font-size: 1rem;">Pilih Skema Sertifikasi Keahlian</label>
                    <select name="skema_id" id="select-skema-apl01" class="input-control" required style="font-size: 1rem; font-weight: 700; padding: 0.85rem;">
                        <option value="">-- Pilih Skema Sertifikasi --</option>
                        @foreach($skemaList as $s)
                            <option value="{{ $s->id }}" data-kode="{{ $s->kode_skema }}" data-nama="{{ $s->nama_skema }}">
                                [{{ $s->kode_skema }}] {{ $s->nama_skema }} ({{ $s->unitKompetensi->count() }} Unit)
                            </option>
                        @endforeach
                    </select>
                </div>

                <!-- TUJUAN ASESMEN (RADIO & OTHER TEXT) -->
                <div class="grup-form" style="margin-top: 1.5rem;">
                    <label class="label-form">Tujuan Asesmen:</label>
                    <div style="display: grid; grid-template-columns: repeat(2, 1fr); gap: 1rem; background: var(--putih); padding: 1rem; border-radius: var(--radius-md); border: 1px solid var(--abu-border);">
                        <label style="display: flex; align-items: center; gap: 0.5rem; font-weight: 600; cursor: pointer;">
                            <input type="radio" name="tujuan_asesmen" value="Sertifikasi" checked> Sertifikasi
                        </label>
                        <label style="display: flex; align-items: center; gap: 0.5rem; font-weight: 600; cursor: pointer;">
                            <input type="radio" name="tujuan_asesmen" value="Pengakuan Kompetensi Terkini (PKT)"> Pengakuan Kompetensi Terkini (PKT)
                        </label>
                        <label style="display: flex; align-items: center; gap: 0.5rem; font-weight: 600; cursor: pointer;">
                            <input type="radio" name="tujuan_asesmen" value="Rekognisi Pembelajaran Lampau (RPL)"> Rekognisi Pembelajaran Lampau (RPL)
                        </label>
                        <label style="display: flex; align-items: center; gap: 0.5rem; font-weight: 600; cursor: pointer;">
                            <input type="radio" name="tujuan_asesmen" value="Lainnya"> Lainnya (dapat diketik)
                        </label>
                    </div>

                    <div id="box-tujuan-lainnya" style="display: none; margin-top: 0.75rem;">
                        <input type="text" name="tujuan_asesmen_lainnya" class="input-control" placeholder="Tuliskan tujuan asesmen lainnya...">
                    </div>
                </div>

                <!-- TABEL DAFTAR UNIT KOMPETENSI SESUAI KEMASAN -->
                <div style="margin-top: 2rem;">
                    <h4 style="color: var(--biru-malam); margin-bottom: 0.75rem;">Daftar Unit Kompetensi sesuai kemasan:</h4>

                    @foreach($skemaList as $s)
                        <div class="tabel-wadah tabel-unit-skema" data-skema-id="{{ $s->id }}" style="display: none;">
                            <table class="tabel-custom">
                                <thead>
                                    <tr>
                                        <th style="width: 50px;">No.</th>
                                        <th>Kode Unit</th>
                                        <th>Judul Unit Kompetensi</th>
                                        <th>Standar Kompetensi Kerja</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @forelse($s->unitKompetensi as $idx => $u)
                                        <tr>
                                            <td>{{ $idx + 1 }}</td>
                                            <td><strong style="color: var(--biru-utama);">{{ $u->kode_unit }}</strong></td>
                                            <td>{{ $u->judul_unit }}</td>
                                            <td><span class="lencana lencana-biru">{{ $u->standar_kompetensi }}</span></td>
                                        </tr>
                                    @empty
                                        <tr><td colspan="4" style="text-align: center; color: var(--abu-teks);">Unit kompetensi belum diisi.</td></tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>
                    @endforeach
                </div>

                <div style="margin-top: 2rem; display: flex; justify-content: space-between;">
                    <button type="button" class="tombol tombol-sekunder btn-kembali-step" data-prev="1">
                        Kembali ke Bagian 1
                    </button>
                    <button type="button" class="tombol tombol-utama btn-lanjut-step" data-next="3">
                        Lanjut ke Bagian 3.1 (Syarat Dasar)
                    </button>
                </div>
            </div>

            <!-- ==========================================
                 BAGIAN 3: BUKTI KELENGKAPAN PEMOHON
                 BAGIAN 3.1 BUKTI PERSYARATAN DASAR
                 ========================================== -->
            <div class="konten-step-apl01" id="step-apl01-3">
                <div class="subjudul-apl01">Bagian 3 : Bukti Kelengkapan Pemohon</div>
                <h4 style="color: var(--biru-malam); margin-bottom: 0.5rem;">3.1 Bukti Persyaratan Dasar Pemohon</h4>

                <div class="tabel-wadah" style="margin-top: 1rem;">
                    <table class="tabel-custom">
                        <thead>
                            <tr>
                                <th style="width: 50px;">No.</th>
                                <th>Bukti Persyaratan Dasar</th>
                                <th style="text-align: center; width: 140px;">Memenuhi Syarat</th>
                                <th style="text-align: center; width: 160px;">Tidak Memenuhi Syarat</th>
                                <th style="text-align: center; width: 100px;">Tidak Ada</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr>
                                <td>1</td>
                                <td>Foto copy rapor kelas 10 – 12 semester 1-6 berisi mata uji yang akan diujikan di asesmen</td>
                                <td style="text-align: center;">
                                    <input type="radio" name="bukti_dasar[rapor]" value="Memenuhi Syarat" checked>
                                </td>
                                <td style="text-align: center;">
                                    <input type="radio" name="bukti_dasar[rapor]" value="Tidak Memenuhi Syarat">
                                </td>
                                <td style="text-align: center;">
                                    <input type="radio" name="bukti_dasar[rapor]" value="Tidak Ada">
                                </td>
                            </tr>
                            <tr>
                                <td>2</td>
                                <td>Foto copy sertifikat PKL / Sertifikat Pelatihan pada bidang keahlian teruji</td>
                                <td style="text-align: center;">
                                    <input type="radio" name="bukti_dasar[pkl]" value="Memenuhi Syarat" checked>
                                </td>
                                <td style="text-align: center;">
                                    <input type="radio" name="bukti_dasar[pkl]" value="Tidak Memenuhi Syarat">
                                </td>
                                <td style="text-align: center;">
                                    <input type="radio" name="bukti_dasar[pkl]" value="Tidak Ada">
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>

                <div style="margin-top: 2rem; display: flex; justify-content: space-between;">
                    <button type="button" class="tombol tombol-sekunder btn-kembali-step" data-prev="2">
                        Kembali ke Bagian 2
                    </button>
                    <button type="button" class="tombol tombol-utama btn-lanjut-step" data-next="4">
                        Lanjut ke Bagian 3.2 (Bukti Admin & TTD)
                    </button>
                </div>
            </div>

            <!-- ==========================================
                 BAGIAN 3.2: BUKTI ADMINISTRATIF & SIGNATURE
                 ========================================== -->
            <div class="konten-step-apl01" id="step-apl01-4">
                <div class="subjudul-apl01">3.2 Bukti Administratif</div>

                <div class="tabel-wadah" style="margin-bottom: 2rem;">
                    <table class="tabel-custom">
                        <thead>
                            <tr>
                                <th style="width: 50px;">No.</th>
                                <th>Bukti Administratif</th>
                                <th style="text-align: center; width: 140px;">Memenuhi Syarat</th>
                                <th style="text-align: center; width: 160px;">Tidak Memenuhi Syarat</th>
                                <th style="text-align: center; width: 100px;">Tidak Ada</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr>
                                <td>1</td>
                                <td>Kartu Pelajar / Kartu Mahasiswa / ID Card</td>
                                <td style="text-align: center;"><input type="radio" name="bukti_admin[kartu_pelajar]" value="Memenuhi Syarat" checked></td>
                                <td style="text-align: center;"><input type="radio" name="bukti_admin[kartu_pelajar]" value="Tidak Memenuhi Syarat"></td>
                                <td style="text-align: center;"><input type="radio" name="bukti_admin[kartu_pelajar]" value="Tidak Ada"></td>
                            </tr>
                            <tr>
                                <td>2</td>
                                <td>KK atau KTP</td>
                                <td style="text-align: center;"><input type="radio" name="bukti_admin[ktp]" value="Memenuhi Syarat" checked></td>
                                <td style="text-align: center;"><input type="radio" name="bukti_admin[ktp]" value="Tidak Memenuhi Syarat"></td>
                                <td style="text-align: center;"><input type="radio" name="bukti_admin[ktp]" value="Tidak Ada"></td>
                            </tr>
                            <tr>
                                <td>3</td>
                                <td>Pas Foto 3 X 4 (Latar Belakang Merah)</td>
                                <td style="text-align: center;"><input type="radio" name="bukti_admin[foto]" value="Memenuhi Syarat" checked></td>
                                <td style="text-align: center;"><input type="radio" name="bukti_admin[foto]" value="Tidak Memenuhi Syarat"></td>
                                <td style="text-align: center;"><input type="radio" name="bukti_admin[foto]" value="Tidak Ada"></td>
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
                                <input type="checkbox" disabled {{ ($pendaftaranAktif->rekomendasi_admin_status ?? '') === 'diterima' ? 'checked' : '' }}> Diterima
                            </label>
                            <label style="display: flex; align-items: center; gap: 0.35rem; font-weight: 700;">
                                <input type="checkbox" disabled {{ ($pendaftaranAktif->rekomendasi_admin_status ?? '') === 'tidak_diterima' ? 'checked' : '' }}> Tidak Diterima
                            </label>
                        </div>
                        <small style="color: var(--abu-teks); display: block;">* Status rekomendasi & catatan akan diisi oleh Admin LSP saat verifikasi berkas.</small>
                    </div>

                    <!-- KOLOM 2: PEMOHON / KANDIDAT (ASESI) TTD CANVAS POPUP -->
                    <div style="background: var(--putih); padding: 1.5rem; border-radius: var(--radius-md); border: 2px dashed var(--biru-muda); text-align: center;">
                        <h4 style="color: var(--biru-malam); margin-bottom: 0.5rem;">Pemohon / Kandidat (Asesi):</h4>
                        <div style="font-weight: 700; color: var(--biru-utama); margin-bottom: 0.75rem;">{{ $pengguna->nama_lengkap }}</div>

                        <!-- INPUT HIDDEN BASE64 TTD -->
                        <input type="hidden" name="tanda_tangan_asesi" id="input-ttd-asesi-base64" value="{{ old('tanda_tangan_asesi', $pendaftaranAktif->tanda_tangan_asesi ?? auth()->user()->tanda_tangan ?? '') }}">

                        <!-- PREVIEW TTD CANVAS -->
                        <div id="box-preview-ttd-asesi" style="margin: 0.75rem 0; {{ ($pendaftaranAktif->tanda_tangan_asesi ?? false) ? '' : 'display: none;' }}">
                            <img id="preview-ttd-asesi-img" src="{{ $pendaftaranAktif->tanda_tangan_asesi ?? '' }}" alt="TTD Asesi" style="max-height: 90px; border: 1px solid var(--biru-soft); padding: 0.25rem; background: var(--putih); border-radius: var(--radius-sm);">
                        </div>

                        <button type="button" class="tombol tombol-utama tombol-sm" onclick="bukaModal('modalCanvasTtd')" style="margin-top: 0.5rem;">
                            {{ ($pendaftaranAktif->tanda_tangan_asesi ?? false) ? 'Ubah Tanda Tangan Canvas' : 'Gambar Tanda Tangan Digital' }}
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
                    <button type="button" class="tombol tombol-sekunder btn-kembali-step" data-prev="3">
                        Kembali ke Bagian 3.1
                    </button>
                    <button type="submit" class="tombol tombol-sukses" style="padding: 0.8rem 2rem; font-size: 1rem;">
                        Simpan & Kirim Formulir FR.APL.01
                    </button>
                </div>
            </div>
        </div>
    </form>
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
    <script src="{{ asset('js/asesi/pendaftaran-skema.js') }}"></script>
@endpush
