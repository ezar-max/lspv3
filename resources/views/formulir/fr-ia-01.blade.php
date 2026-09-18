@extends('tata-letak.dasbor')

@section('judul', 'FR.IA.01 - Ceklis Observasi Aktivitas')

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
        $asesiTtd = $iaRecord->data_jawaban['ttd_asesi'] ?? ($pendaftaran->tanda_tangan_asesi ?? null);
        $tglTtdAsesi = $iaRecord->data_jawaban['tgl_ttd_asesi'] ?? null;
    @endphp

    <!-- ACTION BAR ATAS -->
    @include('komponen.action-bar-formulir', [
        'kodeForm' => 'FR.IA.01',
        'namaForm' => 'Ceklis Observasi Aktivitas',
        'pendaftaranId' => $pendaftaran->id,
        'isAsesi' => $isAsesi,
        'saveLabel' => 'Simpan Formulir',
        'saveAction' => "alert('Formulir FR.IA.01 berhasil disimpan!')",
        'signed' => (bool)$asesiTtd,
        'signedLabel' => 'Hasil Terverifikasi & Ditandatangani',
        'signRoute' => route('formulir.ia.simpan-ttd-asesi', ['kodeForm' => 'FR.IA.01', 'pendaftaranId' => $pendaftaran->id]),
        'signLabel' => 'Tanda Tangani Hasil Asesmen'
    ])

    @if($isAsesi)
        @include('komponen.banner-readonly-asesi', [
            'judul' => 'Mode Pratinjau Asesi (Hanya Baca)',
            'keterangan' => 'Formulir dan instrumen penilaian ini diisi dan dinilai oleh Asesor Kompetensi Anda.',
            'status' => 'Hanya Baca',
            'tipe' => 'info'
        ])
    @endif

    <div class="dokumen-kertas">
        
        <!-- KOP RESMI DOKUMEN STANDAR BNSP -->
        @include('komponen.kop-formulir-bnsp', [
            'kodeForm' => 'FR.IA.01',
            'judulForm' => 'CL - CEKLIS OBSERVASI AKTIVITAS DI TEMPAT KERJA ATAU TEMPAT KERJA SIMULASI',
            'tipeDokumen' => 'Ceklis Observasi'
        ])

        <!-- IDENTITAS DOKUMEN -->
        <table class="tabel-bnsp">
            <tr>
                <td rowspan="2" style="width: 25%; font-weight: 700; vertical-align: middle; background-color: #f8fafc;">
                    Skema Sertifikasi<br>
                    <span style="font-weight: 500; font-size: 0.85rem; color: #475569;">(KKNI/Okupasi/Klaster)</span>
                </td>
                <td style="width: 12%; font-weight: 600;">Judul</td>
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
                <li>Lengkapi nama unit kompetensi, elemen, dan kriteria unjuk kerja sesuai kolom dalam tabel.</li>
                <li>Isilah standar industri atau tempat kerja.</li>
                <li>Beri tanda centang (&radic;) pada kolom "YA" jika Anda yakin asesi dapat melakukan/mendemonstrasikan tugas sesuai KUK, atau centang (&radic;) pada kolom "Tidak" bila sebaliknya.</li>
                <li>Penilaian Lanjut diisi bila hasil belum dapat disimpulkan, untuk itu gunakan metode lain sehingga keputusan dapat dibuat.</li>
                <li>Isilah kolom KUK sesuai dengan Unit Kompetensi / SKKNI.</li>
            </ul>
        </div>

        <!-- KELOMPOK PEKERJAAN -->
        <table class="tabel-bnsp" style="margin-bottom: 1.5rem;">
            <thead>
                <tr>
                    <th style="width: 22%; text-align: center;">Kelompok Pekerjaan</th>
                    <th style="width: 8%; text-align: center;">No.</th>
                    <th style="width: 25%;">Kode Unit</th>
                    <th>Judul Unit</th>
                </tr>
            </thead>
            <tbody>
                @forelse($pendaftaran->skema->unitKompetensi as $indexUnit => $unit)
                    <tr>
                        @if($indexUnit === 0)
                            <td rowspan="{{ count($pendaftaran->skema->unitKompetensi) }}" style="vertical-align: middle; text-align: center; font-weight: 700; background-color: #f8fafc;">
                                Kelompok Pekerjaan 1
                            </td>
                        @endif
                        <td style="text-align: center; font-weight: 700;">{{ $indexUnit + 1 }}.</td>
                        <td style="font-weight: 600; color: #0284c7;">{{ $unit->kode_unit }}</td>
                        <td style="font-weight: 600; color: #0f172a;">{{ $unit->judul_unit }}</td>
                    </tr>
                @empty
                    <tr><td colspan="4" style="text-align: center; color: #64748b;">Belum ada data unit kompetensi.</td></tr>
                @endforelse
            </tbody>
        </table>

        <!-- LOOP CEKLIS OBSERVASI PER UNIT KOMPETENSI -->
        @foreach($pendaftaran->skema->unitKompetensi as $indexUnit => $unit)
            <div style="margin-top: 1.75rem;" class="{{ $indexUnit > 0 ? 'page-break' : '' }}">
                
                <table class="tabel-bnsp" style="margin-bottom: 0; border-bottom: none;">
                    <tr>
                        <td rowspan="2" style="width: 22%; font-weight: 700; vertical-align: middle; background-color: #f1f5f9;">
                            Unit Kompetensi {{ $indexUnit + 1 }}
                        </td>
                        <td style="width: 15%; font-weight: 600;">Kode Unit</td>
                        <td style="width: 2%;">:</td>
                        <td style="font-weight: 700; color: #0284c7;">{{ $unit->kode_unit }}</td>
                    </tr>
                    <tr>
                        <td style="font-weight: 600;">Judul Unit</td>
                        <td>:</td>
                        <td style="font-weight: 700; color: #0f172a;">{{ $unit->judul_unit }}</td>
                    </tr>
                </table>

                <table class="tabel-bnsp" style="font-size: 0.85rem;">
                    <thead>
                        <tr>
                            <th rowspan="2" style="width: 5%; text-align: center;">No.</th>
                            <th rowspan="2" style="width: 22%;">Elemen</th>
                            <th rowspan="2" style="width: 33%;">Kriteria Unjuk Kerja</th>
                            <th rowspan="2" style="width: 18%;">Standar Industri atau Tempat Kerja</th>
                            <th colspan="2" style="width: 12%; text-align: center;">Pencapaian</th>
                            <th rowspan="2" style="width: 10%; text-align: center;">Penilaian Lanjut</th>
                        </tr>
                        <tr>
                            <th style="width: 6%; text-align: center;">Ya</th>
                            <th style="width: 6%; text-align: center;">Tidak</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($unit->elemenKompetensi as $idxElem => $elem)
                            @php
                                $totalKuk = count($elem->kriteriaUnjukKerja);
                            @endphp
                            @if($totalKuk > 0)
                                @foreach($elem->kriteriaUnjukKerja as $kIdx => $kuk)
                                    <tr>
                                        @if($kIdx === 0)
                                            <td rowspan="{{ $totalKuk }}" style="text-align: center; font-weight: 700; vertical-align: middle;">
                                                {{ $idxElem + 1 }}
                                            </td>
                                            <td rowspan="{{ $totalKuk }}" style="font-weight: 600; color: #0f172a; vertical-align: top;">
                                                {{ $elem->nomor_elemen }}. {{ $elem->nama_elemen }}
                                                @if(!empty($elem->pertanyaan_elemen))
                                                    <div style="font-size: 0.78rem; color: #0284c7; font-style: italic; margin-top: 0.25rem;">"{{ $elem->pertanyaan_elemen }}"</div>
                                                @endif
                                            </td>
                                        @endif
                                        <td>
                                            <strong style="color: #0284c7;">{{ $kuk->nomor_kuk }}</strong> {{ $kuk->pernyataan_kuk }}
                                        </td>
                                        <td>
                                            <input type="text" class="input-inline-bnsp" value="SKKNI {{ $unit->kode_unit }}" style="font-size: 0.78rem;">
                                        </td>
                                        <td style="text-align: center; vertical-align: middle;">
                                            <input type="checkbox" checked style="width: 16px; height: 16px; accent-color: #059669;">
                                        </td>
                                        <td style="text-align: center; vertical-align: middle;">
                                            <input type="checkbox" style="width: 16px; height: 16px; accent-color: #dc2626;">
                                        </td>
                                        <td>
                                            <input type="text" class="input-inline-bnsp" placeholder="Catatan..." style="font-size: 0.78rem;">
                                        </td>
                                    </tr>
                                @endforeach
                            @else
                                <tr>
                                    <td style="text-align: center; font-weight: 700;">{{ $idxElem + 1 }}</td>
                                    <td style="font-weight: 600;">{{ $elem->nomor_elemen }}. {{ $elem->nama_elemen }}</td>
                                    <td style="font-style: italic; color: #64748b;">KUK belum diinput.</td>
                                    <td><input type="text" class="input-inline-bnsp" value="SKKNI" style="font-size: 0.78rem;"></td>
                                    <td style="text-align: center;"><input type="checkbox" checked style="width: 16px; height: 16px;"></td>
                                    <td style="text-align: center;"><input type="checkbox" style="width: 16px; height: 16px;"></td>
                                    <td><input type="text" class="input-inline-bnsp" style="font-size: 0.78rem;"></td>
                                </tr>
                            @endif
                        @empty
                            <tr><td colspan="7" style="text-align: center; color: #64748b;">Belum ada data elemen.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        @endforeach

        <!-- UMPAN BALIK DAN PENGESAHAN -->
        <div style="margin-top: 1.5rem; margin-bottom: 1.5rem;">
            <div style="font-weight: 700; font-size: 0.95rem; margin-bottom: 0.4rem; color: #0f172a;">Umpan Balik / Catatan Asesor:</div>
            <textarea class="input-inline-bnsp" rows="3" placeholder="Tuliskan umpan balik untuk asesi...">{{ $iaRecord->catatan_asesor ?? 'Seluruh instruksi kerja dan demonstrasi praktik telah diobservasi dengan baik sesuai standar kompetensi SKKNI.' }}</textarea>
        </div>

        <!-- PENGESAHAN ASESI & ASESOR -->
        <table class="tabel-bnsp" style="margin-bottom: 2rem;">
            <tr>
                <td style="width: 50%; font-weight: 700; background: #f8fafc;">ASESI :</td>
                <td style="width: 50%; font-weight: 700; background: #f8fafc;">ASESOR :</td>
            </tr>
            <tr>
                <td>
                    <div style="margin-bottom: 0.4rem;">Nama : <strong>{{ $asesiNama }}</strong></div>
                    <div style="margin-top: 1rem; border-top: 1px dashed #cbd5e1; padding-top: 0.4rem;">
                        Tanda tangan dan Tanggal :<br>
                        @if($asesiTtd)
                            <div style="margin-top: 0.35rem;">
                                <img src="{{ $asesiTtd }}" alt="TTD Asesi" style="max-height: 45px;">
                                <div style="font-size: 0.78rem; color: #16a34a; font-weight: 700; margin-top: 0.25rem;">
                                    Terverifikasi & Disetujui Asesi ({{ $tglTtdAsesi ?? date('d-m-Y') }})
                                </div>
                            </div>
                        @else
                            @if($isAsesi)
                                <div style="margin-top: 0.5rem;" class="no-print">
                                    <form action="{{ route('formulir.ia.simpan-ttd-asesi', ['kodeForm' => 'FR.IA.01', 'pendaftaranId' => $pendaftaran->id]) }}" method="POST">
                                        @csrf
                                        <button type="submit" class="tombol tombol-utama tombol-sm" style="background: #2563eb; border-color: #2563eb; font-size: 0.82rem;">
                                            Tanda Tangani & Setujui Hasil Penilaian
                                        </button>
                                    </form>
                                </div>
                            @else
                                <span style="font-style: italic; color: #64748b;">(Belum Ditandatangani Asesi)</span>
                            @endif
                        @endif
                    </div>
                </td>
                <td>
                    <div style="margin-bottom: 0.2rem;">Nama : <strong>{{ $asesorNama }}</strong></div>
                    <div style="margin-bottom: 0.4rem;">No. Reg : <strong>{{ $asesorMet }}</strong></div>
                    <div style="margin-top: 0.75rem; border-top: 1px dashed #cbd5e1; padding-top: 0.4rem;">
                        Tanda tangan dan Tanggal :<br>
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
        <div style="margin-top: 2rem;">
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

    </div>
</div>
@endsection

@push('js')
    <script src="{{ asset('js/formulir/formulir-bnsp.js') }}"></script>
@endpush

