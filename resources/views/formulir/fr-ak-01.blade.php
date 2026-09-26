@extends('tata-letak.dasbor')

@section('judul', 'FR.AK.01 - Persetujuan Asesmen dan Kerahasiaan')

@push('css')
    <link rel="stylesheet" href="{{ asset('css/formulir/formulir-bnsp.css') }}">
    <style>
        .tabel-pilihan-bukti label {
            display: flex;
            align-items: center;
            gap: 0.5rem;
            cursor: pointer;
            font-size: 0.88rem;
            color: #1e293b;
            padding: 0.25rem 0;
        }
        .tabel-pilihan-bukti input[type="checkbox"],
        .tabel-pilihan-bukti input[type="radio"] {
            width: 16px;
            height: 16px;
            accent-color: #0284c7;
            cursor: pointer;
        }
    </style>
@endpush

@section('konten')
<div class="wadah-formulir-bnsp animasi-slide {{ (auth()->check() && auth()->user()->peran === 'asesi' && !empty($pendaftaran->tanda_tangan_asesi_ak01)) ? 'mode-read-only-asesi' : '' }}">
    
    @php
        $isAsesi = auth()->check() && auth()->user()->peran === 'asesi';
        $asesorNama = $pendaftaran->asesor->nama_lengkap ?? (auth()->user()->peran === 'asesor' ? auth()->user()->nama_lengkap : 'Asesor LSP');
        $asesorMet = $pendaftaran->asesor->nomor_registrasi ?? 'MET.000.004455.2023';
        $asesiNama = $pendaftaran->asesi->nama_lengkap ?? 'Nama Asesi';
        
        $asesorTtd = $pendaftaran->tanda_tangan_asesor_ak01 ?? ($pendaftaran->tanda_tangan_asesor ?? null);
        $asesiTtd = $pendaftaran->tanda_tangan_asesi_ak01 ?? ($pendaftaran->tanda_tangan_asesi ?? ($pendaftaran->asesi->tanda_tangan ?? (auth()->user()->peran === 'asesi' ? auth()->user()->tanda_tangan : null)));
        
        $tglTtdAsesor = $pendaftaran->tanggal_ttd_asesor_ak01 ? \Carbon\Carbon::parse($pendaftaran->tanggal_ttd_asesor_ak01)->isoFormat('D MMMM YYYY') : ($pendaftaran->tanggal_ttd_asesor ? \Carbon\Carbon::parse($pendaftaran->tanggal_ttd_asesor)->isoFormat('D MMMM YYYY') : null);
        $tglTtdAsesi = $pendaftaran->tanggal_ttd_asesi_ak01 ? \Carbon\Carbon::parse($pendaftaran->tanggal_ttd_asesi_ak01)->isoFormat('D MMMM YYYY') : ($pendaftaran->tanggal_ttd_asesi ? \Carbon\Carbon::parse($pendaftaran->tanggal_ttd_asesi)->isoFormat('D MMMM YYYY') : null);

        $buktiSelected = $pendaftaran->bukti_dikumpulkan ?? null;
        if (!is_array($buktiSelected)) $buktiSelected = [];
        $tukSelected = $pendaftaran->tuk_type ?? null;
    @endphp

    <!-- ACTION BAR ATAS -->
    @include('komponen.action-bar-formulir', [
        'kodeForm' => 'FR.AK.01',
        'namaForm' => 'Persetujuan Asesmen & Kerahasiaan',
        'pendaftaranId' => $pendaftaran->id,
        'tipeForm' => 'FR.AK',
        'isAsesi' => $isAsesi,
        'saveLabel' => 'Simpan Persetujuan',
        'saveFormId' => 'form-ak01-bnsp',
        'signed' => (bool)$asesiTtd,
        'signedLabel' => 'Telah Disetujui & Ditandatangani',
        'signLabel' => 'Setujui & Tanda Tangani FR.AK.01'
    ])

    @if($isAsesi)
        @if(!$asesiTtd)
            @include('komponen.banner-readonly-asesi', [
                'judul' => 'Persetujuan Rencana Asesmen & Kerahasiaan (FR.AK.01)',
                'keterangan' => 'Silakan tentukan jenis TUK dan metode pengumpulan bukti yang disepakati, lalu klik Setujui & Tanda Tangani FR.AK.01.',
                'status' => 'Menunggu Persetujuan',
                'tipe' => 'info'
            ])
        @else
            @include('komponen.banner-readonly-asesi', [
                'judul' => 'Persetujuan Rencana Asesmen & Kerahasiaan Selesai',
                'keterangan' => 'Seluruh rincian pelaksanaan, bukti yang dikumpulkan, dan komitmen kerahasiaan telah Anda setujui dan tanda tangani.',
                'status' => 'Disetujui Asesi',
                'tipe' => 'sukses'
            ])
        @endif
    @endif

    <form id="form-ak01-bnsp" action="{{ route('formulir.ak01.simpan', $pendaftaran->id) }}" method="POST">
        @csrf

        <div class="dokumen-kertas">
            
            <!-- KOP RESMI DOKUMEN STANDAR BNSP -->
            @include('komponen.kop-formulir-bnsp', [
                'kodeForm' => 'FR.AK.01',
                'judulForm' => 'PERSETUJUAN ASESMEN DAN KERAHASIAAN',
                'tipeDokumen' => 'Persetujuan Asesmen',
                'subJudul' => 'Persetujuan Asesmen ini untuk menjamin bahwa Asesi telah diberi arahan secara rinci tentang perencanaan dan proses asesmen'
            ])

            <!-- IDENTITAS SKEMA & DATA ASESMEN -->
            <table class="tabel-bnsp" style="margin-bottom: 1.25rem;">
                <tr>
                    <td rowspan="2" style="width: 25%; font-weight: 700; vertical-align: middle; background-color: #f8fafc;">
                        Skema Sertifikasi<br>
                        <span style="font-weight: 500; font-size: 0.82rem; color: #64748b;">(KKNI/Okupasi/Klaster)</span>
                    </td>
                    <td style="width: 14%; font-weight: 600;">Judul</td>
                    <td style="width: 2%; text-align: center;">:</td>
                    <td style="font-weight: 700; color: #0f172a;">{{ $pendaftaran->skema->nama_skema }}</td>
                </tr>
                <tr>
                    <td style="font-weight: 600;">Nomor</td>
                    <td style="text-align: center;">:</td>
                    <td style="font-weight: 700; color: #0f172a;">{{ $pendaftaran->skema->kode_skema }}</td>
                </tr>
                <tr>
                    <td colspan="2" style="font-weight: 600; background-color: #f8fafc;">TUK</td>
                    <td style="text-align: center;">:</td>
                    <td>
                        @if($isAsesi && $asesiTtd)
                            <strong>{{ $tukSelected }}</strong>
                            <input type="hidden" name="tuk_type" value="{{ $tukSelected }}">
                        @else
                            <div class="tabel-pilihan-bukti" style="display: flex; gap: 1.5rem; align-items: center;">
                                <label style="cursor: pointer;">
                                    <input type="radio" name="tuk_type" value="Sewaktu" {{ $tukSelected === 'Sewaktu' ? 'checked' : '' }}> Sewaktu
                                </label>
                                <label style="cursor: pointer;">
                                    <input type="radio" name="tuk_type" value="Tempat Kerja" {{ $tukSelected === 'Tempat Kerja' ? 'checked' : '' }}> Tempat Kerja
                                </label>
                                <label style="cursor: pointer;">
                                    <input type="radio" name="tuk_type" value="Mandiri" {{ $tukSelected === 'Mandiri' ? 'checked' : '' }}> Mandiri*
                                </label>
                            </div>
                        @endif
                    </td>
                </tr>
                <tr>
                    <td colspan="2" style="font-weight: 600; background-color: #f8fafc;">Nama Asesor</td>
                    <td style="text-align: center;">:</td>
                    <td><strong>{{ $asesorNama }}</strong> @if($asesorMet)<span style="font-size: 0.82rem; color: #64748b;">(No. Reg: {{ $asesorMet }})</span>@endif</td>
                </tr>
                <tr>
                    <td colspan="2" style="font-weight: 600; background-color: #f8fafc;">Nama Asesi</td>
                    <td style="text-align: center;">:</td>
                    <td><strong>{{ $asesiNama }}</strong> <span style="font-size: 0.82rem; color: #64748b;">(No. Reg: {{ $pendaftaran->nomor_pendaftaran }})</span></td>
                </tr>
            </table>

            <!-- BUKTI YANG AKAN DIKUMPULKAN -->
            <table class="tabel-bnsp" style="margin-bottom: 1.25rem;">
                <tr>
                    <td style="width: 25%; font-weight: 700; vertical-align: top; background-color: #f8fafc;">
                        Bukti yang akan dikumpulkan :
                    </td>
                    <td>
                        <div class="tabel-pilihan-bukti" style="display: grid; grid-template-columns: repeat(auto-fit, minmax(240px, 1fr)); gap: 0.5rem 1rem;">
                            <label style="{{ ($isAsesi && $asesiTtd) ? '' : 'cursor: pointer;' }}">
                                <input type="checkbox" name="bukti_dikumpulkan[]" value="Hasil Verifikasi Portofolio" {{ in_array('Hasil Verifikasi Portofolio', $buktiSelected) ? 'checked' : '' }} {{ ($isAsesi && $asesiTtd) ? 'disabled' : '' }}>
                                Hasil Verifikasi Portofolio
                            </label>
                            <label style="{{ ($isAsesi && $asesiTtd) ? '' : 'cursor: pointer;' }}">
                                <input type="checkbox" name="bukti_dikumpulkan[]" value="Hasil Reviu Produk" {{ in_array('Hasil Reviu Produk', $buktiSelected) ? 'checked' : '' }} {{ ($isAsesi && $asesiTtd) ? 'disabled' : '' }}>
                                Hasil Reviu Produk
                            </label>
                            <label style="{{ ($isAsesi && $asesiTtd) ? '' : 'cursor: pointer;' }}">
                                <input type="checkbox" name="bukti_dikumpulkan[]" value="Hasil Observasi Langsung" {{ in_array('Hasil Observasi Langsung', $buktiSelected) ? 'checked' : '' }} {{ ($isAsesi && $asesiTtd) ? 'disabled' : '' }}>
                                Hasil Observasi Langsung
                            </label>
                            <label style="{{ ($isAsesi && $asesiTtd) ? '' : 'cursor: pointer;' }}">
                                <input type="checkbox" name="bukti_dikumpulkan[]" value="Hasil Kegiatan Terstruktur" {{ in_array('Hasil Kegiatan Terstruktur', $buktiSelected) ? 'checked' : '' }} {{ ($isAsesi && $asesiTtd) ? 'disabled' : '' }}>
                                Hasil Kegiatan Terstruktur
                            </label>
                            <label style="{{ ($isAsesi && $asesiTtd) ? '' : 'cursor: pointer;' }}">
                                <input type="checkbox" name="bukti_dikumpulkan[]" value="Hasil Pertanyaan Lisan" {{ in_array('Hasil Pertanyaan Lisan', $buktiSelected) ? 'checked' : '' }} {{ ($isAsesi && $asesiTtd) ? 'disabled' : '' }}>
                                Hasil Pertanyaan Lisan
                            </label>
                            <label style="{{ ($isAsesi && $asesiTtd) ? '' : 'cursor: pointer;' }}">
                                <input type="checkbox" name="bukti_dikumpulkan[]" value="Hasil Pertanyaan Tertulis" {{ in_array('Hasil Pertanyaan Tertulis', $buktiSelected) ? 'checked' : '' }} {{ ($isAsesi && $asesiTtd) ? 'disabled' : '' }}>
                                Hasil Pertanyaan Tertulis
                            </label>
                            <label style="{{ ($isAsesi && $asesiTtd) ? '' : 'cursor: pointer;' }}">
                                <input type="checkbox" name="bukti_dikumpulkan[]" value="Hasil Pertanyaan Wawancara" {{ in_array('Hasil Pertanyaan Wawancara', $buktiSelected) ? 'checked' : '' }} {{ ($isAsesi && $asesiTtd) ? 'disabled' : '' }}>
                                Hasil Pertanyaan Wawancara
                            </label>
                            <div style="display: flex; align-items: center; gap: 0.5rem;">
                                <label style="margin: 0; padding: 0; {{ ($isAsesi && $asesiTtd) ? '' : 'cursor: pointer;' }}">
                                    <input type="checkbox" name="bukti_dikumpulkan[]" value="Lainnya" {{ in_array('Lainnya', $buktiSelected) ? 'checked' : '' }} {{ ($isAsesi && $asesiTtd) ? 'disabled' : '' }}>
                                    <span>Lainnya :</span>
                                </label>
                                <input type="text" name="bukti_dikumpulkan_lainnya" value="{{ old('bukti_dikumpulkan_lainnya', $pendaftaran->bukti_dikumpulkan_lainnya) }}" class="input-control" placeholder="Keterangan lainnya..." {{ ($isAsesi && $asesiTtd) ? 'readonly' : '' }} style="padding: 0.2rem 0.5rem; font-size: 0.82rem; height: 30px; flex: 1;">
                            </div>
                        </div>
                    </td>
                </tr>
            </table>

            <!-- PELAKSANAAN ASESMEN DISEPAKATI -->
            <table class="tabel-bnsp" style="margin-bottom: 1.25rem;">
                <tr>
                    <td rowspan="3" style="width: 25%; font-weight: 700; vertical-align: middle; background-color: #f8fafc;">
                        Pelaksanaan asesmen disepakati pada :
                    </td>
                    <td style="width: 14%; font-weight: 600;">Hari / Tanggal</td>
                    <td style="width: 2%; text-align: center;">:</td>
                    <td style="font-weight: 700; color: #0f172a;">
                        {{ $pendaftaran->jadwal ? \Carbon\Carbon::parse($pendaftaran->jadwal->tanggal_uji)->isoFormat('dddd, D MMMM YYYY') : date('d-m-Y') }}
                    </td>
                </tr>
                <tr>
                    <td style="font-weight: 600;">Waktu</td>
                    <td style="text-align: center;">:</td>
                    <td style="font-weight: 700; color: #0f172a;">
                        {{ $pendaftaran->jadwal->waktu_uji ?? '08:00 WIB - Selesai' }}
                    </td>
                </tr>
                <tr>
                    <td style="font-weight: 600;">TUK</td>
                    <td style="text-align: center;">:</td>
                    <td style="font-weight: 700; color: #0f172a;">
                        {{ $pendaftaran->jadwal->lokasi_uji ?? ($pendaftaran->jadwal->nama_tuk ?? ($pendaftaran->tuk_type ?? 'TUK Sewaktu LSP')) }}
                    </td>
                </tr>
            </table>

            <!-- PERNYATAAN KERAHASIAAN -->
            <div style="border: 1px solid #0f172a; padding: 1.25rem; background: #fafafa; font-size: 0.88rem; line-height: 1.6; color: #1e293b; margin-bottom: 1.5rem; border-radius: 4px;">
                <div style="margin-bottom: 0.85rem;">
                    <strong style="color: #0f172a; display: block; font-size: 0.92rem;">Asesi :</strong>
                    <p style="margin: 0.2rem 0 0 0; color: #334155;">
                        Bahwa saya telah mendapatkan penjelasan terkait hak dan prosedur banding asesmen dari asesor.
                    </p>
                </div>

                <div style="margin-bottom: 0.85rem;">
                    <strong style="color: #0f172a; display: block; font-size: 0.92rem;">Asesor :</strong>
                    <p style="margin: 0.2rem 0 0 0; color: #334155;">
                        Menyatakan tidak akan membuka hasil pekerjaan yang saya peroleh karena penugasan saya sebagai Asesor dalam pekerjaan Asesmen kepada siapapun atau organisasi apapun selain kepada pihak yang berwenang sehubungan dengan kewajiban saya sebagai Asesor yang ditugaskan oleh LSP.
                    </p>
                </div>

                <div>
                    <strong style="color: #0f172a; display: block; font-size: 0.92rem;">Asesi :</strong>
                    <p style="margin: 0.2rem 0 0 0; color: #334155;">
                        Saya setuju mengikuti asesmen dengan pemahaman bahwa informasi yang dikumpulkan hanya digunakan untuk pengembangan profesional dan hanya dapat diakses oleh orang tertentu saja.
                    </p>
                </div>
            </div>

            <!-- TANDA TANGAN BERDAMPINGAN -->
            <table class="tabel-bnsp">
                <thead>
                    <tr>
                        <th style="width: 50%; text-align: center; background-color: #f8fafc; padding: 0.75rem;">
                            Asesor Kompetensi
                        </th>
                        <th style="width: 50%; text-align: center; background-color: #f8fafc; padding: 0.75rem;">
                            Peserta Uji (Asesi)
                        </th>
                    </tr>
                </thead>
                <tbody>
                    <tr>
                        <!-- KOLOM ASESOR -->
                        <td style="padding: 1.25rem; vertical-align: top; text-align: center;">
                            <div style="font-weight: 700; color: #0f172a; margin-bottom: 0.5rem; text-align: left;">Tanda Tangan Asesor :</div>
                            <div style="min-height: 90px; display: flex; align-items: center; justify-content: center; background: #f8fafc; border: 1px dashed #cbd5e1; border-radius: 6px; margin-bottom: 0.75rem;">
                                @if($asesorTtd)
                                    @php
                                        $srcAsesorTtd = \Illuminate\Support\Str::startsWith($asesorTtd, ['data:image', 'http://', 'https://']) ? $asesorTtd : asset($asesorTtd);
                                    @endphp
                                    <img src="{{ $srcAsesorTtd }}" alt="TTD Asesor" style="max-height: 75px;">
                                @else
                                    <span style="font-size: 0.8rem; color: #94a3b8; font-style: italic;">(Belum Ditandatangani)</span>
                                @endif
                            </div>
                            <div style="font-size: 0.88rem; text-align: left;">
                                Nama : <strong>{{ $asesorNama }}</strong><br>
                                Tanggal : <strong>{{ $tglTtdAsesor ?? ($pendaftaran->tanggal_daftar ? \Carbon\Carbon::parse($pendaftaran->tanggal_daftar)->isoFormat('D MMMM YYYY') : date('d-m-Y')) }}</strong>
                            </div>
                        </td>

                        <!-- KOLOM ASESI -->
                        <td style="padding: 1.25rem; vertical-align: top; text-align: center;">
                            <div style="font-weight: 700; color: #0f172a; margin-bottom: 0.5rem; text-align: left;">Tanda Tangan Asesi :</div>
                            <div style="min-height: 90px; display: flex; align-items: center; justify-content: center; background: #f8fafc; border: 1px dashed #cbd5e1; border-radius: 6px; margin-bottom: 0.75rem;">
                                @if($asesiTtd)
                                    @php
                                        $srcAsesiTtd = \Illuminate\Support\Str::startsWith($asesiTtd, ['data:image', 'http://', 'https://']) ? $asesiTtd : asset($asesiTtd);
                                    @endphp
                                    <img src="{{ $srcAsesiTtd }}" alt="TTD Asesi" style="max-height: 75px;">
                                @else
                                    <span style="font-size: 0.8rem; color: #94a3b8; font-style: italic;">(Belum Ditandatangani)</span>
                                @endif
                            </div>
                            <div style="font-size: 0.88rem; text-align: left;">
                                Nama : <strong>{{ $asesiNama }}</strong><br>
                                Tanggal : <strong>{{ $tglTtdAsesi ?? ($pendaftaran->tanggal_daftar ? \Carbon\Carbon::parse($pendaftaran->tanggal_daftar)->isoFormat('D MMMM YYYY') : date('d-m-Y')) }}</strong>
                            </div>
                        </td>
                    </tr>
                </tbody>
            </table>

        </div>
    </form>

</div>
@endsection
