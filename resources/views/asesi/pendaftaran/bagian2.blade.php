@extends('tata-letak.dasbor')

@section('judul', 'FR.APL.01 - Bagian 2: Data Sertifikasi')

@push('css')
    <link rel="stylesheet" href="{{ asset('css/asesi/pendaftaran-bagian2.css') }}">
@endpush

@section('konten')
<div style="max-width: 1050px;" class="animasi-slide">
    <!-- STEPPER NAVIGATION -->
    <div class="stepper-header-page">
        <a href="{{ route('asesi.pendaftaran.bagian1') }}" class="step-pill selesai">
            <div class="step-num">1</div>
            <div>Bagian 1: Data Pemohon</div>
        </a>
        <div class="step-pill aktif">
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
            'tipeDokumen' => 'Bagian 2: Data Skema',
            'subJudul' => 'Bagian 2 : Data Sertifikasi & Tabel Unit Kompetensi - Tuliskan Judul dan Nomor Skema Sertifikasi yang Anda ajukan berikut Daftar Unit Kompetensi.'
        ])

        <form action="{{ route('asesi.pendaftaran.simpan2') }}" method="POST">
            @csrf

            <div class="grup-form" style="background: var(--biru-bg); padding: 1.5rem; border-radius: var(--radius-md); border: 1px solid var(--biru-soft);">
                <label class="label-form" style="font-size: 1.05rem;">Pilih Skema Sertifikasi Keahlian</label>
                <select name="skema_id" id="select-skema-apl01" class="input-control" required style="font-size: 1rem; font-weight: 700; padding: 0.85rem;">
                    <option value="">-- Pilih Skema Sertifikasi --</option>
                    @foreach($skemaList as $s)
                        @php $isRegistered = in_array($s->id, $registeredSkemaIds ?? []); @endphp
                        <option value="{{ $s->id }}" {{ $isRegistered ? 'disabled' : '' }} {{ (old('skema_id', $draftData['skema_id'] ?? '') == $s->id) ? 'selected' : '' }}>
                            [{{ $s->kode_skema }}] {{ $s->nama_skema }} ({{ $s->unitKompetensi->count() }} Unit) {{ $isRegistered ? '⚠️ [Sudah Didaftarkan]' : '' }}
                        </option>
                    @endforeach
                </select>
            </div>

            <!-- TUJUAN ASESMEN -->
            <div class="grup-form" style="margin-top: 1.5rem;">
                <label class="label-form">Tujuan Asesmen:</label>
                @php $tujuanAktif = old('tujuan_asesmen', $draftData['tujuan_asesmen'] ?? 'Sertifikasi'); @endphp
                <div style="display: grid; grid-template-columns: repeat(2, 1fr); gap: 1rem; background: var(--putih); padding: 1.25rem; border-radius: var(--radius-md); border: 1px solid var(--abu-border);">
                    <label style="display: flex; align-items: center; gap: 0.5rem; font-weight: 600; cursor: pointer;">
                        <input type="radio" name="tujuan_asesmen" value="Sertifikasi" {{ $tujuanAktif === 'Sertifikasi' ? 'checked' : '' }}> Sertifikasi
                    </label>
                    <label style="display: flex; align-items: center; gap: 0.5rem; font-weight: 600; cursor: pointer;">
                        <input type="radio" name="tujuan_asesmen" value="Pengakuan Kompetensi Terkini (PKT)" {{ $tujuanAktif === 'Pengakuan Kompetensi Terkini (PKT)' ? 'checked' : '' }}> Pengakuan Kompetensi Terkini (PKT)
                    </label>
                    <label style="display: flex; align-items: center; gap: 0.5rem; font-weight: 600; cursor: pointer;">
                        <input type="radio" name="tujuan_asesmen" value="Rekognisi Pembelajaran Lampau (RPL)" {{ $tujuanAktif === 'Rekognisi Pembelajaran Lampau (RPL)' ? 'checked' : '' }}> Rekognisi Pembelajaran Lampau (RPL)
                    </label>
                    <label style="display: flex; align-items: center; gap: 0.5rem; font-weight: 600; cursor: pointer;">
                        <input type="radio" name="tujuan_asesmen" value="Lainnya" {{ $tujuanAktif === 'Lainnya' ? 'checked' : '' }}> Lainnya (dapat diketik)
                    </label>
                </div>

                <div id="box-tujuan-lainnya" style="display: {{ $tujuanAktif === 'Lainnya' ? 'block' : 'none' }}; margin-top: 0.75rem;">
                    <input type="text" name="tujuan_asesmen_lainnya" class="input-control" placeholder="Tuliskan tujuan asesmen lainnya..." value="{{ old('tujuan_asesmen_lainnya', $draftData['tujuan_asesmen_lainnya'] ?? '') }}">
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

            <div style="margin-top: 2.5rem; display: flex; justify-content: space-between;">
                <a href="{{ route('asesi.pendaftaran.bagian1') }}" class="tombol tombol-sekunder">
                    Kembali ke Bagian 1
                </a>
                <button type="submit" class="tombol tombol-utama" style="padding: 0.8rem 2rem;">
                    Simpan & Lanjut ke Bagian 3.1 (Syarat Dasar)
                </button>
            </div>
        </form>
    </div>
</div>
@endsection

@push('js')
    <script src="{{ asset('js/asesi/pendaftaran-bagian2.js') }}"></script>
@endpush
