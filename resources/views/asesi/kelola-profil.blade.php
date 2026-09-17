@extends('tata-letak.dasbor')

@section('judul', 'Kelola Profil & Hasil Pendaftaran')

@push('css')
    <link rel="stylesheet" href="{{ asset('css/asesi/dashboard-asesi.css') }}">
@endpush

@section('konten')
<div style="max-width: 1000px;" class="animasi-slide">
    <div style="margin-bottom: 2rem;">
        <h1 style="font-size: 1.8rem; color: var(--biru-malam);">Kelola Profil Biodata & Pendaftaran Asesi</h1>
        <p style="color: var(--abu-teks);">Kelola data pribadi dan periksa data formulir FR.APL.01 yang telah diajukan</p>
    </div>

    <!-- TAMPILAN DATA HASIL PENDAFTARAN YANG SUDAH DIAJUKAN -->
    @if($pendaftaranAktif)
        <div class="kartu" style="padding: 2rem; margin-bottom: 2.5rem; box-shadow: var(--bayangan-hover);">
            <div style="display: flex; justify-content: space-between; align-items: flex-start; margin-bottom: 1.5rem; border-bottom: 2px solid var(--biru-soft); padding-bottom: 1rem;">
                <div>
                    <span class="lencana lencana-biru" style="font-size: 0.85rem;">No. Registrasi: {{ $pendaftaranAktif->nomor_pendaftaran }}</span>
                    <h2 style="color: var(--biru-malam); margin-top: 0.35rem;">Formulir FR.APL.01 Permohonan Sertifikasi</h2>
                    <p style="color: var(--abu-teks); font-size: 0.92rem;">Skema Sertifikasi: <strong>{{ $pendaftaranAktif->skema->nama_skema }}</strong> ({{ $pendaftaranAktif->skema->kode_skema }})</p>
                </div>
                <div>
                    @if($pendaftaranAktif->request_perbaikan)
                        <span class="lencana lencana-amber" style="padding: 0.6rem 1rem;">Request Perbaikan Dikirim</span>
                    @else
                        <button class="tombol tombol-outline tombol-sm" onclick="bukaModal('modalRequestPerbaikan')">
                            Request Perbaikan Biodata
                        </button>
                    @endif
                </div>
            </div>

            @if($pendaftaranAktif->request_perbaikan)
                <div style="background: var(--amber-bg); color: #92400e; padding: 1rem 1.25rem; border-radius: var(--radius-md); border: 1px solid #fef3c7; font-size: 0.9rem; margin-bottom: 1.5rem;">
                    <strong>Catatan Permohonan Perbaikan:</strong> "{{ $pendaftaranAktif->catatan_request_perbaikan }}"
                    <small style="display: block; margin-top: 0.25rem; opacity: 0.8;">Admin LSP akan memeriksa dan membuka akses edit formulir Anda.</small>
                </div>
            @endif

            <!-- TABEL DETAIL DATA HASIL APL-01 -->
            <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 1.5rem; margin-bottom: 1.5rem;">
                <div style="background: var(--biru-bg); padding: 1.25rem; border-radius: var(--radius-md); border: 1px solid var(--biru-soft);">
                    <h4 style="color: var(--biru-malam); margin-bottom: 0.75rem;">Bagian 1: Data Pemohon</h4>
                    <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 0.5rem; font-size: 0.88rem;">
                        <div><span style="color: var(--abu-teks);">NIK:</span><br><strong>{{ $profil->nik ?? '-' }}</strong></div>
                        <div><span style="color: var(--abu-teks);">TTL:</span><br><strong>{{ $profil->tempat_lahir ?? '-' }}, {{ $profil->tanggal_lahir ?? '-' }}</strong></div>
                        <div><span style="color: var(--abu-teks);">Jenis Kelamin:</span><br><strong>{{ $profil->jenis_kelamin ?? '-' }}</strong></div>
                        <div><span style="color: var(--abu-teks);">Pendidikan:</span><br><strong>{{ $profil->pendidikan_terakhir ?? '-' }}</strong></div>
                        <div style="grid-column: 1/-1;"><span style="color: var(--abu-teks);">Alamat Rumah:</span><br><strong>{{ $profil->alamat ?? '-' }} (Kode Pos: {{ $pendaftaranAktif->kode_pos ?? '-' }})</strong></div>
                        <div style="grid-column: 1/-1;"><span style="color: var(--abu-teks);">Instansi/Pekerjaan:</span><br><strong>{{ $profil->nama_sekolah_instansi ?? '-' }} ({{ $profil->pekerjaan ?? '-' }})</strong></div>
                    </div>
                </div>

                <div style="background: var(--biru-bg); padding: 1.25rem; border-radius: var(--radius-md); border: 1px solid var(--biru-soft);">
                    <h4 style="color: var(--biru-malam); margin-bottom: 0.75rem;">Bagian 2: Data Sertifikasi</h4>
                    <div style="font-size: 0.88rem;">
                        <div style="margin-bottom: 0.5rem;"><span style="color: var(--abu-teks);">Tujuan Asesmen:</span><br><span class="lencana lencana-biru">{{ $pendaftaranAktif->tujuan_asesmen }}</span></div>
                        <div style="margin-bottom: 0.5rem;"><span style="color: var(--abu-teks);">Status Verifikasi Admin:</span><br>
                            @if($pendaftaranAktif->status_pendaftaran === 'diverifikasi')
                                <span class="lencana lencana-hijau">Terverifikasi (Disetujui Admin)</span>
                            @elseif($pendaftaranAktif->status_pendaftaran === 'diajukan')
                                <span class="lencana lencana-amber">Menunggu Verifikasi Admin</span>
                            @else
                                <span class="lencana lencana-merah">Revisi / Draft</span>
                            @endif
                        </div>
                        <div style="margin-top: 0.75rem;"><span style="color: var(--abu-teks);">Tanda Tangan Digital Canvas Asesi:</span></div>
                        <div style="margin-top: 0.25rem;">
                            @if($pendaftaranAktif->tanda_tangan_asesi)
                                <img src="{{ $pendaftaranAktif->tanda_tangan_asesi }}" alt="TTD Canvas" style="max-height: 55px; background: #fff; padding: 2px; border-radius: 4px; border: 1px solid var(--biru-soft);">
                            @else
                                <span style="color: var(--merah-bahaya);">Belum ada TTD Canvas</span>
                            @endif
                        </div>
                    </div>
                </div>
            </div>

            <!-- UNIT KOMPETENSI REKAP -->
            <h4 style="color: var(--biru-malam); margin-bottom: 0.75rem;">Daftar Unit Kompetensi Terdaftar:</h4>
            <div class="tabel-wadah">
                <table class="tabel-custom">
                    <thead>
                        <tr>
                            <th style="width: 50px;">No.</th>
                            <th>Kode Unit</th>
                            <th>Judul Unit Kompetensi</th>
                            <th>Standar</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($pendaftaranAktif->skema->unitKompetensi as $idx => $u)
                            <tr>
                                <td>{{ $idx + 1 }}</td>
                                <td><strong style="color: var(--biru-utama);">{{ $u->kode_unit }}</strong></td>
                                <td>{{ $u->judul_unit }}</td>
                                <td><span class="lencana lencana-biru">{{ $u->standar_kompetensi }}</span></td>
                            </tr>
                        @empty
                            <tr><td colspan="4" style="text-align: center; color: var(--abu-teks);">Belum ada unit kompetensi.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    @endif

    <!-- FORM EDIT KELOLA BIODATA PROFIL UTAMA -->
    <div class="kartu" style="padding: 2rem;">
        <form action="{{ route('asesi.profil.simpan') }}" method="POST" enctype="multipart/form-data">
            @csrf
            <h3 style="color: var(--biru-malam); margin-bottom: 1.25rem; border-bottom: 1px solid var(--biru-soft); padding-bottom: 0.5rem;">
                Data Utama Akun Asesi
            </h3>

            <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 1.25rem;">
                <div class="grup-form">
                    <label class="label-form">Nama Lengkap (Sesuai Ijazah)</label>
                    <input type="text" name="nama_lengkap" class="input-control" value="{{ old('nama_lengkap', $pengguna->nama_lengkap) }}" required>
                </div>
                <div class="grup-form">
                    <label class="label-form">Nomor Telepon / WhatsApp</label>
                    <input type="text" name="nomor_telepon" class="input-control" value="{{ old('nomor_telepon', $pengguna->nomor_telepon) }}" required>
                </div>
            </div>

            <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 1.25rem;">
                <div class="grup-form">
                    <label class="label-form">NIK (Wajib 16 Digit) <span style="color: var(--merah-bahaya);">*</span></label>
                    <input type="text" name="nik" class="input-control" placeholder="Masukkan 16 digit NIK" minlength="16" maxlength="16" pattern="[0-9]{16}" inputmode="numeric" oninput="this.value = this.value.replace(/[^0-9]/g, '').slice(0, 16)" title="NIK harus berjumlah tepat 16 digit angka" value="{{ old('nik', $profil->nik) }}" required>
                </div>
                <div class="grup-form">
                    <label class="label-form">Jenis Kelamin</label>
                    <select name="jenis_kelamin" class="input-control">
                        <option value="Laki-laki" {{ old('jenis_kelamin', $profil->jenis_kelamin) == 'Laki-laki' ? 'selected' : '' }}>Laki-laki</option>
                        <option value="Perempuan" {{ old('jenis_kelamin', $profil->jenis_kelamin) == 'Perempuan' ? 'selected' : '' }}>Perempuan</option>
                    </select>
                </div>
            </div>

            <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 1.25rem;">
                <div class="grup-form">
                    <label class="label-form">Tempat Lahir</label>
                    <input type="text" name="tempat_lahir" class="input-control" value="{{ old('tempat_lahir', $profil->tempat_lahir) }}">
                </div>
                <div class="grup-form">
                    <label class="label-form">Tanggal Lahir</label>
                    <input type="date" name="tanggal_lahir" class="input-control" value="{{ old('tanggal_lahir', $profil->tanggal_lahir) }}" max="{{ date('Y-m-d') }}">
                </div>
            </div>

            <div class="grup-form">
                <label class="label-form">Alamat Lengkap Rumah</label>
                <textarea name="alamat" class="input-control" rows="3">{{ old('alamat', $profil->alamat) }}</textarea>
            </div>

            <h3 style="color: var(--biru-malam); margin-top: 2rem; margin-bottom: 1.25rem; border-bottom: 1px solid var(--biru-soft); padding-bottom: 0.5rem;">
                Pendidikan & Instansi
            </h3>

            <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 1.25rem;">
                <div class="grup-form">
                    <label class="label-form">Pendidikan Terakhir</label>
                    <input type="text" name="pendidikan_terakhir" class="input-control" value="{{ old('pendidikan_terakhir', $profil->pendidikan_terakhir) }}">
                </div>
                <div class="grup-form">
                    <label class="label-form">Nama Sekolah / Instansi</label>
                    <input type="text" name="nama_sekolah_instansi" class="input-control" value="{{ old('nama_sekolah_instansi', $profil->nama_sekolah_instansi) }}">
                </div>
            </div>

            <div style="margin-top: 2.5rem; text-align: right;">
                <button type="submit" class="tombol tombol-utama">
                    Simpan Perubahan Biodata
                </button>
            </div>
        </form>
    </div>
</div>

<!-- MODAL REQUEST PERBAIKAN BIODATA -->
@if($pendaftaranAktif)
<div class="modal-overlay" id="modalRequestPerbaikan">
    <div class="modal-konten">
        <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 1.5rem;">
            <h3 style="color: var(--biru-malam);">Request Perbaikan Biodata Pendaftaran</h3>
            <button onclick="tutupModal('modalRequestPerbaikan')" style="background: none; border: none; font-size: 1.2rem; cursor: pointer;">&times;</button>
        </div>

        <form action="{{ route('asesi.request-perbaikan', $pendaftaranAktif->id) }}" method="POST">
            @csrf
            <p style="font-size: 0.9rem; color: var(--abu-teks); margin-bottom: 1rem;">
                Jika terdapat kesalahan penulisan NIK, Nama, Alamat, atau Skema pada formulir yang sudah terkirim, jelaskan detail perbaikan di bawah ini untuk dikonfirmasi oleh Admin LSP:
            </p>

            <div class="grup-form">
                <label class="label-form">Jelaskan Bagian Data yang Perlu Diperbaiki</label>
                <textarea name="catatan_request_perbaikan" class="input-control" rows="4" required placeholder="contoh: Salah ketik NIK dan Alamat rumah pada Formulir APL-01..."></textarea>
            </div>

            <div style="margin-top: 1.5rem; text-align: right; display: flex; gap: 0.5rem; justify-content: flex-end;">
                <button type="button" class="tombol tombol-sekunder" onclick="tutupModal('modalRequestPerbaikan')">Batal</button>
                <button type="submit" class="tombol tombol-utama">Kirim Request Perbaikan</button>
            </div>
        </form>
    </div>
</div>
@endif
@endsection
