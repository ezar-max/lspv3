@extends('tata-letak.dasbor')

@section('judul', 'FR.APL.01 - Bagian 1: Data Pemohon')

@push('css')
    <link rel="stylesheet" href="{{ asset('css/asesi/pendaftaran-bagian1.css') }}">
@endpush

@section('konten')
<div style="max-width: 1050px;" class="animasi-slide">
    <!-- STEPPER NAVIGATION -->
    <div class="stepper-header-page">
        <div class="step-pill aktif">
            <div class="step-num">1</div>
            <div>Bagian 1: Data Pemohon</div>
        </div>
        <div class="step-pill">
            <div class="step-num">2</div>
            <div>Bagian 2: Data Sertifikasi</div>
        </div>
        <div class="step-pill">
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
            'tipeDokumen' => 'Bagian 1: Data Pemohon',
            'subJudul' => 'Bagian 1: Rincian Data Pemohon Sertifikasi - Cantumkan data pribadi, pendidikan formal, serta pekerjaan Anda saat ini.'
        ])

        <form action="{{ route('asesi.pendaftaran.simpan1') }}" method="POST">
            @csrf

            <h4 style="color: var(--biru-malam); margin-bottom: 1rem; font-size: 1.05rem;">a. Data Pribadi</h4>

            <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 1.25rem;">
                <div class="grup-form">
                    <label class="label-form">Nama Lengkap</label>
                    <input type="text" name="nama_lengkap" class="input-control" value="{{ old('nama_lengkap', $draftData['nama_lengkap'] ?? $pengguna->nama_lengkap) }}" required>
                </div>
                <div class="grup-form">
                    <label class="label-form">No. KTP / NIK (Wajib 16 Digit) <span style="color: var(--merah-bahaya);">*</span></label>
                    <input type="text" name="nik" class="input-control" placeholder="16 digit NIK" minlength="16" maxlength="16" pattern="[0-9]{16}" inputmode="numeric" oninput="this.value = this.value.replace(/[^0-9]/g, '').slice(0, 16)" title="NIK harus berjumlah tepat 16 digit angka" value="{{ old('nik', $draftData['nik'] ?? $profil->nik) }}" required>
                </div>
            </div>

            <div style="display: grid; grid-template-columns: 1fr 1fr 1fr; gap: 1.25rem;">
                <div class="grup-form">
                    <label class="label-form">Tempat Lahir</label>
                    <input type="text" name="tempat_lahir" class="input-control" value="{{ old('tempat_lahir', $draftData['tempat_lahir'] ?? $profil->tempat_lahir) }}" required>
                </div>
                <div class="grup-form">
                    <label class="label-form">Tanggal Lahir</label>
                    <input type="date" name="tanggal_lahir" class="input-control" value="{{ old('tanggal_lahir', $draftData['tanggal_lahir'] ?? $profil->tanggal_lahir) }}" max="{{ date('Y-m-d') }}" required>
                </div>
                <div class="grup-form">
                    <label class="label-form">Jenis Kelamin</label>
                    <select name="jenis_kelamin" class="input-control" required>
                        <option value="Laki-laki" {{ old('jenis_kelamin', $draftData['jenis_kelamin'] ?? $profil->jenis_kelamin) == 'Laki-laki' ? 'selected' : '' }}>Laki-laki</option>
                        <option value="Perempuan" {{ old('jenis_kelamin', $draftData['jenis_kelamin'] ?? $profil->jenis_kelamin) == 'Perempuan' ? 'selected' : '' }}>Perempuan</option>
                    </select>
                </div>
            </div>

            <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 1.25rem;">
                <div class="grup-form">
                    <label class="label-form">Kebangsaan</label>
                    <input type="text" name="kebangsaan" class="input-control" value="{{ old('kebangsaan', $draftData['kebangsaan'] ?? 'Indonesia') }}" required>
                </div>
                <div class="grup-form">
                    <label class="label-form">Kualifikasi Pendidikan</label>
                    <input type="text" name="pendidikan_terakhir" class="input-control" placeholder="contoh: SMK Teknik Informatika" value="{{ old('pendidikan_terakhir', $draftData['pendidikan_terakhir'] ?? $profil->pendidikan_terakhir) }}">
                </div>
            </div>

            <div style="display: grid; grid-template-columns: 3fr 1fr; gap: 1.25rem;">
                <div class="grup-form">
                    <label class="label-form">Alamat Rumah</label>
                    <input type="text" name="alamat" class="input-control" value="{{ old('alamat', $draftData['alamat'] ?? $profil->alamat) }}" required>
                </div>
                <div class="grup-form">
                    <label class="label-form">Kode Pos</label>
                    <input type="text" name="kode_pos" class="input-control" placeholder="12340" value="{{ old('kode_pos', $draftData['kode_pos'] ?? '') }}">
                </div>
            </div>

            <div style="display: grid; grid-template-columns: 1fr 1fr 1fr; gap: 1.25rem;">
                <div class="grup-form">
                    <label class="label-form">No. Telp Rumah</label>
                    <input type="text" name="no_telp_rumah" class="input-control" placeholder="(021) 555-..." value="{{ old('no_telp_rumah', $draftData['no_telp_rumah'] ?? '') }}">
                </div>
                <div class="grup-form">
                    <label class="label-form">No. HP / WhatsApp</label>
                    <input type="text" name="nomor_telepon" class="input-control" value="{{ old('nomor_telepon', $draftData['nomor_telepon'] ?? $pengguna->nomor_telepon) }}" required>
                </div>
                <div class="grup-form">
                    <label class="label-form">E-mail Resmi</label>
                    <input type="email" name="email" class="input-control" value="{{ old('email', $pengguna->email) }}" required readonly style="background: var(--biru-bg);">
                </div>
            </div>

            <h4 style="color: var(--biru-malam); margin-top: 2rem; margin-bottom: 1rem; font-size: 1.05rem;">b. Data Pekerjaan Sekarang</h4>

            <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 1.25rem;">
                <div class="grup-form">
                    <label class="label-form">Nama Institusi / Perusahaan</label>
                    <input type="text" name="nama_sekolah_instansi" class="input-control" placeholder="contoh: SMK Negeri 1 Jakarta" value="{{ old('nama_sekolah_instansi', $draftData['nama_sekolah_instansi'] ?? $profil->nama_sekolah_instansi) }}">
                </div>
                <div class="grup-form">
                    <label class="label-form">Jabatan</label>
                    <input type="text" name="pekerjaan" class="input-control" placeholder="contoh: Siswa Vokasi / Staff IT" value="{{ old('pekerjaan', $draftData['pekerjaan'] ?? $profil->pekerjaan) }}">
                </div>
            </div>

            <div style="display: grid; grid-template-columns: 3fr 1fr; gap: 1.25rem;">
                <div class="grup-form">
                    <label class="label-form">Alamat Kantor / Sekolah</label>
                    <input type="text" name="alamat_kantor" class="input-control" placeholder="Alamat instansi..." value="{{ old('alamat_kantor', $draftData['alamat_kantor'] ?? '') }}">
                </div>
                <div class="grup-form">
                    <label class="label-form">Kode Pos Kantor</label>
                    <input type="text" name="kode_pos_kantor" class="input-control" placeholder="12340" value="{{ old('kode_pos_kantor', $draftData['kode_pos_kantor'] ?? '') }}">
                </div>
            </div>

            <div style="display: grid; grid-template-columns: 1fr 1fr 1fr; gap: 1.25rem;">
                <div class="grup-form">
                    <label class="label-form">No. Telp Kantor</label>
                    <input type="text" name="telp_kantor" class="input-control" placeholder="(021) 777-..." value="{{ old('telp_kantor', $draftData['telp_kantor'] ?? '') }}">
                </div>
                <div class="grup-form">
                    <label class="label-form">No. Fax Kantor</label>
                    <input type="text" name="fax_kantor" class="input-control" placeholder="Fax..." value="{{ old('fax_kantor', $draftData['fax_kantor'] ?? '') }}">
                </div>
                <div class="grup-form">
                    <label class="label-form">E-mail Kantor</label>
                    <input type="email" name="email_kantor" class="input-control" placeholder="info@sekolah.sch.id" value="{{ old('email_kantor', $draftData['email_kantor'] ?? '') }}">
                </div>
            </div>

            <div style="margin-top: 2.5rem; text-align: right;">
                <button type="submit" class="tombol tombol-utama" style="padding: 0.8rem 2rem;">
                    Simpan & Lanjut ke Bagian 2 (Data Sertifikasi)
                </button>
            </div>
        </form>
    </div>
</div>
@endsection
