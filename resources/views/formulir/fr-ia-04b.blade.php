@extends('tata-letak.dasbor')

@section('judul', 'FR.IA.04B - Penilaian Proyek Singkat')

@push('css')
    <link rel="stylesheet" href="{{ asset('css/formulir/formulir-bnsp.css') }}">
@endpush

@section('konten')
<div class="wadah-formulir-bnsp animasi-slide {{ auth()->check() && auth()->user()->peran === 'asesi' ? 'mode-read-only-asesi' : '' }}">
    
    @php
        $isAsesi = auth()->check() && auth()->user()->peran === 'asesi';
        $asesorNama = $pendaftaran->asesor->nama_lengkap ?? (auth()->user()->peran === 'asesor' ? auth()->user()->nama_lengkap : 'Asesor LSP');
        $asesorMet = $pendaftaran->asesor->nomor_registrasi ?? 'MET.000.004455.2023';
        $asesiNama = $pendaftaran->asesi->nama_lengkap ?? 'Nama Asesi';
        $asesorTtd = $pendaftaran->tanda_tangan_asesor ?? (auth()->user()->tanda_tangan ?? null);
        $asesiTtd = $pendaftaran->tanda_tangan_asesi ?? null;
    @endphp

    <!-- ACTION BAR ATAS -->
    @include('komponen.action-bar-formulir', [
        'kodeForm' => 'FR.IA.04B',
        'namaForm' => 'Penilaian Proyek Singkat',
        'pendaftaranId' => $pendaftaran->id,
        'isAsesi' => $isAsesi,
        'saveLabel' => 'Simpan Formulir',
        'saveAction' => "alert('Penilaian Proyek Singkat (FR.IA.04B) berhasil disimpan!')"
    ])

    @if($isAsesi)
        @include('komponen.banner-readonly-asesi', [
            'judul' => 'Mode Pratinjau Asesi (Hanya Baca)',
            'keterangan' => 'Lembar penilaian proyek singkat ini dinilai langsung oleh Asesor Penguji Anda.',
            'status' => 'Hanya Baca',
            'tipe' => 'info'
        ])
    @endif

    <div class="dokumen-kertas">
        
        <!-- KOP RESMI DOKUMEN STANDAR BNSP -->
        @include('komponen.kop-formulir-bnsp', [
            'kodeForm' => 'FR.IA.04B',
            'judulForm' => 'PENILAIAN PROYEK SINGKAT ATAU KEGIATAN TERSTRUKTUR LAINNYA',
            'tipeDokumen' => 'Penilaian Proyek'
        ])

        <!-- IDENTITAS DOKUMEN -->
        <table class="tabel-bnsp">
            <tr>
                <td rowspan="2" style="width: 25%; font-weight: 700; vertical-align: middle; background-color: #f8fafc;">
                    Skema Sertifikasi<br>
                    <span style="font-weight: 500; font-size: 0.85rem; color: #475569;">(KKNI/Okupasi/Klaster)</span>
                </td>
                <td style="width: 15%; font-weight: 600;">Judul</td>
                <td style="width: 2%;">:</td>
                <td style="font-weight: 700; color: #0f172a;">{{ $pendaftaran->skema->nama_skema }}</td>
            </tr>
            <tr>
                <td style="font-weight: 600;">Nomor</td>
                <td>:</td>
                <td style="font-weight: 700; color: #0f172a;">{{ $pendaftaran->skema->kode_skema }}</td>
            </tr>
            <tr>
                <td colspan="2" style="font-weight: 600;">TUK</td>
                <td>:</td>
                <td>Sewaktu / Tempat Kerja / Mandiri* (<strong>{{ $pendaftaran->tuk_type ?? '' }}</strong>)</td>
            </tr>
            <tr>
                <td colspan="2" style="font-weight: 600;">Judul Kegiatan Terstruktur</td>
                <td>:</td>
                <td><input type="text" class="input-inline-bnsp" value="Implementasi Proyek Pengembangan Modul Aplikasi Berbasis Web" style="font-weight: 600;"></td>
            </tr>
            <tr>
                <td colspan="2" style="font-weight: 600;">Nama Asesor</td>
                <td>:</td>
                <td><strong>{{ $asesorNama }}</strong></td>
            </tr>
            <tr>
                <td colspan="2" style="font-weight: 600;">Nama Asesi</td>
                <td>:</td>
                <td><strong>{{ $asesiNama }}</strong></td>
            </tr>
            <tr>
                <td colspan="2" style="font-weight: 600;">Tanggal</td>
                <td>:</td>
                <td>{{ date('d-m-Y') }}</td>
            </tr>
        </table>
        <div style="font-size: 0.75rem; font-style: italic; color: #64748b; margin-top: -1rem; margin-bottom: 1.25rem;">*Coret yang tidak perlu</div>

        <!-- PANDUAN BAGI ASESOR -->
        <div style="border: 1px solid #334155; padding: 1rem 1.25rem; background: #f8fafc; border-radius: 4px; margin-bottom: 1.5rem;">
            <strong style="display: block; font-size: 0.95rem; color: #0f172a; margin-bottom: 0.5rem; text-transform: uppercase;">PANDUAN BAGI ASESOR</strong>
            <ul style="margin: 0; padding-left: 1.2rem; font-size: 0.85rem; color: #334155; line-height: 1.6;">
                <li>Lakukan penilaian pencapaian hasil proyek singkat atau kegiatan terstruktur lainnya melalui presentasi.</li>
                <li>Penilaian dilakukan sesuai dengan FR IA 04A. DIT. Daftar Instruksi Terstruktur (Penjelasan Proyek Singkat/ Kegiatan Terstruktur Lainnya).</li>
                <li>Pertanyaan disampaikan oleh asesor setelah asesi melakukan presentasi proyek singkat/kegiatan terstruktur lainnya.</li>
                <li>Pertanyaan dapat dikembangkan oleh asesor berdasarkan dokumen presentasi dan atau hasil presentasi.</li>
                <li>Pertanyaan yang disampaikan untuk pemenuhan pencapaian 5 dimensi kompetensi.</li>
                <li>Isilah kolom lingkup penyajian proyek atau kegiatan terstruktur lainnya sesuai sektor/sub-sektor/profesi.</li>
                <li>Berikan keputusan pencapaian berdasarkan kesimpulan jawaban asesi.</li>
            </ul>
        </div>

        <!-- TABEL ASPEK PENILAIAN -->
        <table class="tabel-bnsp" style="font-size: 0.85rem;">
            <thead>
                <tr>
                    <th colspan="3" style="text-align: center;" class="th-dark">Aspek Penilaian</th>
                    <th colspan="2" style="width: 12%; text-align: center;" class="th-dark">Pencapaian</th>
                </tr>
                <tr>
                    <th style="width: 25%;">Lingkup Penyajian proyek atau kegiatan terstruktur lainnya</th>
                    <th style="width: 45%;">Daftar Pertanyaan</th>
                    <th style="width: 20%;">Kesesuaian dengan standar kompetensi kerja (unit/elemen/KUK)</th>
                    <th style="width: 6%; text-align: center;">Ya</th>
                    <th style="width: 6%; text-align: center;">Tdk</th>
                </tr>
            </thead>
            <tbody>
                @php
                    $aspekContoh = [
                        [
                            'lingkup' => 'Persiapan Lingkungan & Struktur Proyek',
                            'tanya' => 'Bagaimana Anda menentukan struktur folder dan library yang digunakan dalam proyek ini?',
                            'jawab' => 'Struktur folder disesuaikan dengan arsitektur standar MVC dan library diintegrasikan melalui package manager.',
                            'kuk' => 'Unit 1, Elemen 1 (KUK 1.1, 1.2)'
                        ],
                        [
                            'lingkup' => 'Implementasi Algoritma & Logika Pemrograman',
                            'tanya' => 'Jelaskan logika percabangan dan manipulasi data yang Anda terapkan pada fungsi utama aplikasi!',
                            'jawab' => 'Menggunakan conditional logic if/else serta perulangan untuk memproses array data secara efisien.',
                            'kuk' => 'Unit 1, Elemen 2 (KUK 2.1, 2.2)'
                        ],
                        [
                            'lingkup' => 'Pengujian & Penanganan Error (Debugging)',
                            'tanya' => 'Jika terjadi error atau input yang tidak valid dari pengguna, bagaimana sistem Anda menanganinya?',
                            'jawab' => 'Diterapkan validasi input request serta blok try-catch untuk menangani exception dan memberikan feedback yang jelas.',
                            'kuk' => 'Unit 2, Elemen 1 (KUK 1.1)'
                        ]
                    ];
                @endphp

                @foreach($aspekContoh as $idx => $asp)
                    <tr>
                        <td style="font-weight: 600; color: #0f172a;">
                            {{ $idx + 1 }}. {{ $asp['lingkup'] }}
                        </td>
                        <td>
                            <div style="margin-bottom: 0.35rem;">
                                <strong style="color: #0284c7; display: block;">Pertanyaan:</strong>
                                <textarea class="input-inline-bnsp" rows="2" style="font-size: 0.8rem;">{{ $asp['tanya'] }}</textarea>
                            </div>
                            <div>
                                <strong style="color: #059669; display: block;">Tanggapan:</strong>
                                <textarea class="input-inline-bnsp" rows="2" style="font-size: 0.8rem;">{{ $asp['jawab'] }}</textarea>
                            </div>
                        </td>
                        <td>
                            <input type="text" class="input-inline-bnsp" value="{{ $asp['kuk'] }}" style="font-size: 0.8rem; font-weight: 600;">
                        </td>
                        <td style="text-align: center; vertical-align: middle;">
                            <input type="checkbox" checked style="width: 16px; height: 16px; accent-color: #059669;">
                        </td>
                        <td style="text-align: center; vertical-align: middle;">
                            <input type="checkbox" style="width: 16px; height: 16px; accent-color: #dc2626;">
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>

        <!-- REKOMENDASI ASESOR -->
        <table class="tabel-bnsp" style="margin-bottom: 1.5rem;">
            <tr>
                <td style="width: 25%; font-weight: 700; background: #f8fafc;">Rekomendasi Asesor:</td>
                <td>
                    <div style="margin-bottom: 0.5rem;">
                        Asesi telah memenuhi/belum memenuhi pencapaian seluruh kriteria unjuk kerja, direkomendasikan:
                    </div>
                    <div style="display: flex; gap: 2rem;">
                        <label style="display: flex; align-items: center; gap: 0.4rem; font-weight: 700; color: #059669; cursor: pointer;">
                            <input type="radio" name="rekomendasi_proyek" value="kompeten" checked style="width: 16px; height: 16px; accent-color: #059669;"> Kompeten
                        </label>
                        <label style="display: flex; align-items: center; gap: 0.4rem; font-weight: 700; color: #dc2626; cursor: pointer;">
                            <input type="radio" name="rekomendasi_proyek" value="belum_kompeten" style="width: 16px; height: 16px; accent-color: #dc2626;"> Belum Kompeten
                        </label>
                    </div>
                </td>
            </tr>
        </table>

        <!-- PENGESAHAN ASESI & ASESOR -->
        <table class="tabel-bnsp" style="margin-bottom: 2rem;">
            <tr>
                <td style="width: 50%; font-weight: 700; background: #f8fafc;">Asesi :</td>
                <td style="width: 50%; font-weight: 700; background: #f8fafc;">Asesor :</td>
            </tr>
            <tr>
                <td>
                    <div style="margin-bottom: 0.4rem;">Nama : <strong>{{ $asesiNama }}</strong></div>
                    <div style="margin-top: 1.25rem; border-top: 1px dashed #cbd5e1; padding-top: 0.4rem;">
                        Tanda tangan/Tanggal :<br>
                        @if($asesiTtd)
                            <img src="{{ $asesiTtd }}" alt="TTD Asesi" style="max-height: 45px; margin-top: 0.25rem;">
                        @else
                            <span style="font-style: italic; color: #64748b;">(Tanda Tangan Digital Asesi)</span>
                        @endif
                        <div style="font-size: 0.78rem; color: #64748b; margin-top: 0.25rem;">{{ date('d-m-Y') }}</div>
                    </div>
                </td>
                <td>
                    <div style="margin-bottom: 0.2rem;">Nama : <strong>{{ $asesorNama }}</strong></div>
                    <div style="margin-bottom: 0.4rem;">No. Reg : <strong>{{ $asesorMet }}</strong></div>
                    <div style="margin-top: 0.75rem; border-top: 1px dashed #cbd5e1; padding-top: 0.4rem;">
                        Tanda tangan/Tanggal :<br>
                        @if($asesorTtd)
                            <img src="{{ $asesorTtd }}" alt="TTD Asesor" style="max-height: 45px; margin-top: 0.25rem;">
                        @else
                            <span style="font-style: italic; color: #64748b;">(Tanda Tangan Digital Asesor)</span>
                        @endif
                        <div style="font-size: 0.78rem; color: #64748b; margin-top: 0.25rem;">{{ date('d-m-Y') }}</div>
                    </div>
                </td>
            </tr>
        </table>

        <!-- TABEL PENYUSUN DAN VALIDATOR -->
        <div>
            <div style="font-weight: 800; font-size: 0.95rem; margin-bottom: 0.6rem; color: #0f172a; text-transform: uppercase;">
                PENYUSUN DAN VALIDATOR
            </div>
            <table class="tabel-bnsp">
                <thead>
                    <tr>
                        <th style="width: 18%; text-align: center;">STATUS</th>
                        <th style="width: 6%; text-align: center;">NO</th>
                        <th style="width: 32%;">NAMA</th>
                        <th style="width: 22%;">NOMOR MET</th>
                        <th style="width: 22%;">TANDA TANGAN DAN TANGGAL</th>
                    </tr>
                </thead>
                <tbody>
                    <tr>
                        <td rowspan="2" style="font-weight: 800; vertical-align: middle; text-align: center; background-color: #f8fafc;">PENYUSUN</td>
                        <td style="text-align: center; font-weight: 700;">1</td>
                        <td><strong>{{ $asesorNama }}</strong></td>
                        <td>{{ $asesorMet }}</td>
                        <td style="text-align: center;">
                            @if($asesorTtd)
                                <img src="{{ $asesorTtd }}" alt="TTD" style="max-height: 40px;">
                            @else
                                <span style="font-size: 0.78rem; color: #64748b;">{{ date('d/m/Y') }}</span>
                            @endif
                        </td>
                    </tr>
                    <tr>
                        <td style="text-align: center; font-weight: 700;">2</td>
                        <td><input type="text" class="input-inline-bnsp" placeholder="Penyusun 2..."></td>
                        <td><input type="text" class="input-inline-bnsp" placeholder="No. MET..."></td>
                        <td><input type="text" class="input-inline-bnsp" placeholder="TTD & Tgl..."></td>
                    </tr>
                    <tr>
                        <td rowspan="2" style="font-weight: 800; vertical-align: middle; text-align: center; background-color: #f8fafc;">VALIDATOR</td>
                        <td style="text-align: center; font-weight: 700;">1</td>
                        <td><input type="text" class="input-inline-bnsp" placeholder="Validator 1..."></td>
                        <td><input type="text" class="input-inline-bnsp" placeholder="No. MET..."></td>
                        <td><input type="text" class="input-inline-bnsp" placeholder="TTD & Tgl..."></td>
                    </tr>
                    <tr>
                        <td style="text-align: center; font-weight: 700;">2</td>
                        <td><input type="text" class="input-inline-bnsp" placeholder="Validator 2..."></td>
                        <td><input type="text" class="input-inline-bnsp" placeholder="No. MET..."></td>
                        <td><input type="text" class="input-inline-bnsp" placeholder="TTD & Tgl..."></td>
                    </tr>
                </tbody>
            </table>
        </div>

        @include('komponen.navigasi-form-bawah', [
            'prevUrl' => route('formulir.ia04a', $pendaftaran->id),
            'prevLabel' => 'FR.IA.04A (DIT)'
        ])

    </div>
</div>
@endsection

@push('js')
    <script src="{{ asset('js/formulir/formulir-bnsp.js') }}"></script>
@endpush

