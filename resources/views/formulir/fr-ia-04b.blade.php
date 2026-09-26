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

        $savedSpecs = $savedData['spesifikasi'] ?? [];
        $judulProyekVal = $savedData['judul_proyek'] ?? ($pendaftaran->skema ? 'Proyek Terstruktur ' . ($pendaftaran->skema->nama_skema ?? '') : 'Proyek Terstruktur');
        
        if (empty($savedSpecs)) {
            if (isset($masterSpecs) && $masterSpecs->count() > 0) {
                foreach ($masterSpecs as $mIdx => $ms) {
                    $savedSpecs[$mIdx] = [
                        'lingkup' => $ms->spec_name,
                        'tanya' => 'Bagaimana Anda memenuhi standar toleransi ' . $ms->standard_tolerance . ' pada aspek ini?',
                        'jawab' => 'Asesi mendemonstrasikan hasil sesuai standar spesifikasi kerja.',
                        'kuk' => 'Standar Mutu Proyek',
                        'pencapaian' => 'K',
                    ];
                }
            } elseif ($pendaftaran->skema && $pendaftaran->skema->unitKompetensi && $pendaftaran->skema->unitKompetensi->count() > 0) {
                foreach ($pendaftaran->skema->unitKompetensi as $uIdx => $u) {
                    $savedSpecs[$uIdx] = [
                        'lingkup' => $u->judul_unit,
                        'tanya' => 'Bagaimana keterkaitan implementasi unit ' . $u->kode_unit . ' terhadap hasil proyek yang Anda susun?',
                        'jawab' => 'Asesi menjelaskan alur pengerjaan dan pembuktian kinerja dengan tepat.',
                        'kuk' => $u->kode_unit,
                        'pencapaian' => 'K',
                    ];
                }
            }
        }
    @endphp

    <!-- ACTION BAR ATAS -->
    @include('komponen.action-bar-formulir', [
        'kodeForm' => 'FR.IA.04B',
        'namaForm' => 'Penilaian Proyek Singkat',
        'pendaftaranId' => $pendaftaran->id,
        'isAsesi' => $isAsesi,
        'saveLabel' => 'Simpan Formulir',
        'saveFormId' => 'form-ia-04b'
    ])

    @if($isAsesi)
        @include('komponen.banner-readonly-asesi', [
            'judul' => 'Mode Pratinjau Asesi (Hanya Baca)',
            'keterangan' => 'Lembar penilaian proyek singkat ini dinilai langsung oleh Asesor Penguji Anda.',
            'status' => 'Hanya Baca',
            'tipe' => 'info'
        ])
    @endif

    <form id="form-ia-04b" method="POST" action="{{ route('formulir.ia.simpan', ['kodeForm' => 'FR.IA.04B', 'pendaftaranId' => $pendaftaran->id]) }}">
        @csrf
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
                <td><input type="text" name="judul_proyek" class="input-inline-bnsp" value="{{ $judulProyekVal }}" style="font-weight: 600;" {{ $isAsesi ? "readonly" : "" }}></td>
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

        <!-- DAFTAR UNIT KOMPETENSI SKEMA -->
        <table class="tabel-bnsp" style="margin-bottom: 1.25rem;">
            <thead>
                <tr style="background-color: #f1f5f9;">
                    <th style="width: 5%; text-align: center;">No.</th>
                    <th style="width: 25%; text-align: center;">Kode Unit</th>
                    <th style="width: 50%; text-align: center;">Judul Unit Kompetensi</th>
                    <th style="width: 20%; text-align: center;">Standar Kompetensi</th>
                </tr>
            </thead>
            <tbody>
                @forelse($pendaftaran->skema->unitKompetensi as $idxU => $u)
                    <tr>
                        <td style="text-align: center;">{{ $idxU + 1 }}</td>
                        <td style="font-weight: 700; font-family: monospace;">{{ $u->kode_unit }}</td>
                        <td>{{ $u->nama_unit ?? $u->judul_unit }}</td>
                        <td style="font-size: 0.85rem; color: #475569;">{{ $u->standar_kompetensi ?? 'SKKNI' }}</td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="4" style="text-align: center; color: #94a3b8; font-style: italic;">
                            Tidak ada unit kompetensi terdaftar pada skema sertifikasi ini.
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>

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
                @forelse($savedSpecs as $idx => $asp)
                    @php
                        $pencapaianVal = $asp['pencapaian'] ?? ($isAsesi ? 'K' : null);
                    @endphp
                    <tr>
                        <td style="font-weight: 600; color: #0f172a;">
                            <input type="text" name="spesifikasi[{{ $idx }}][lingkup]" class="input-inline-bnsp" value="{{ $asp['lingkup'] ?? '' }}" placeholder="Lingkup penyajian..." {{ $isAsesi ? 'readonly' : '' }}>
                        </td>
                        <td>
                            <div style="margin-bottom: 0.35rem;">
                                <strong style="color: #0284c7; display: block;">Pertanyaan:</strong>
                                <textarea name="spesifikasi[{{ $idx }}][tanya]" class="input-inline-bnsp" rows="2" style="font-size: 0.8rem;" {{ $isAsesi ? 'readonly' : '' }}>{{ $asp['tanya'] ?? '' }}</textarea>
                            </div>
                            <div>
                                <strong style="color: #059669; display: block;">Tanggapan:</strong>
                                <textarea name="spesifikasi[{{ $idx }}][jawab]" class="input-inline-bnsp" rows="2" style="font-size: 0.8rem;" {{ $isAsesi ? 'readonly' : '' }}>{{ $asp['jawab'] ?? '' }}</textarea>
                            </div>
                        </td>
                        <td>
                            <input type="text" name="spesifikasi[{{ $idx }}][kuk]" class="input-inline-bnsp" value="{{ $asp['kuk'] ?? '' }}" style="font-size: 0.8rem; font-weight: 600;" {{ $isAsesi ? 'readonly' : '' }}>
                        </td>
                        <td style="text-align: center; vertical-align: middle;">
                            <input type="radio" name="spesifikasi[{{ $idx }}][pencapaian]" value="K" {{ $pencapaianVal === 'K' ? 'checked' : '' }} style="width: 16px; height: 16px; accent-color: #059669;" {{ $isAsesi ? 'disabled' : '' }}>
                        </td>
                        <td style="text-align: center; vertical-align: middle;">
                            <input type="radio" name="spesifikasi[{{ $idx }}][pencapaian]" value="BK" {{ $pencapaianVal === 'BK' ? 'checked' : '' }} style="width: 16px; height: 16px; accent-color: #dc2626;" {{ $isAsesi ? 'disabled' : '' }}>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="5" style="text-align: center; color: #64748b; font-style: italic; padding: 1rem;">
                            Belum ada aspek penilaian proyek singkat yang dikonfigurasi di database.
                        </td>
                    </tr>
                @endforelse</tbody>
        </table>

        <!-- REKOMENDASI ASESOR -->
        <table class="tabel-bnsp" style="margin-bottom: 1.5rem;">
            <tr>
                <td style="width: 25%; font-weight: 700; background: #f8fafc;">Rekomendasi Asesor:</td>
                <td>
                    <div style="display: flex; align-items: center; gap: 1.5rem; margin-bottom: 0.75rem;">
                        <label style="display: inline-flex; align-items: center; gap: 0.35rem; font-weight: 600; font-size: 0.88rem; cursor: pointer;">
                            <input type="radio" name="rekomendasi" value="K" {{ ($iaRecord->rekomendasi ?? 'K') === 'K' ? 'checked' : '' }} {{ $isAsesi ? 'disabled' : '' }}>
                            <span style="color: #16a34a;">Kompeten (K)</span>
                        </label>
                        <label style="display: inline-flex; align-items: center; gap: 0.35rem; font-weight: 600; font-size: 0.88rem; cursor: pointer;">
                            <input type="radio" name="rekomendasi" value="BK" {{ ($iaRecord->rekomendasi ?? '') === 'BK' ? 'checked' : '' }} {{ $isAsesi ? 'disabled' : '' }}>
                            <span style="color: #dc2626;">Belum Kompeten (BK)</span>
                        </label>
                    </div>
                    <div>
                        <label style="font-weight: 700; font-size: 0.85rem; color: #0f172a; display: block; margin-bottom: 0.25rem;">Catatan Asesor:</label>
                        <textarea name="catatan" class="input-inline-bnsp" rows="2" placeholder="Catatan kesimpulan penilaian proyek..." {{ $isAsesi ? 'readonly' : '' }}>{{ $iaRecord->catatan_asesor ?? '' }}</textarea>
                    </div>
                </td>
            </tr>
        </table>
        </form>

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

