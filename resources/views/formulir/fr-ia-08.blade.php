@extends('tata-letak.dasbor')

@section('judul', 'FR.IA.08 - Ceklis Verifikasi Portofolio')

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
        'kembaliRoute' => route('formulir.index', ['pendaftaran_id' => $pendaftaran->id]),
        'kodeForm' => 'FR.IA.08',
        'namaForm' => 'FR.IA.08 Verifikasi Portofolio',
        'pendaftaranId' => $pendaftaran->id,
        'canSubmit' => !$isAsesi,
        'saveFormId' => 'form-ia-08',
        'submitLabel' => 'Simpan Formulir',
        'canSignAsesi' => $isAsesi && !$asesiTtd,
        'signAsesiRoute' => route('formulir.ia.simpan-ttd-asesi', ['kodeForm' => 'FR.IA.08', 'pendaftaranId' => $pendaftaran->id]),
        'isAsesiSigned' => $isAsesi && $asesiTtd
    ])

    @if($isAsesi)
        @include('komponen.banner-readonly-asesi', [
            'pesan' => 'Ceklis verifikasi bukti portofolio (VATM) ini dinilai langsung oleh Asesor Penguji Anda.'
        ])
    @endif

    <div class="dokumen-kertas">
        
        <!-- KOP RESMI DOKUMEN STANDAR BNSP -->
        @include('komponen.kop-formulir-bnsp', [
            'kodeForm' => 'FR.IA.08',
            'judulForm' => 'CVP – CEKLIS VERIFIKASI PORTOFOLIO',
            'tipeDokumen' => 'Verifikasi Portofolio'
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
        <div class="kotak-panduan-asesor">
            <strong>PANDUAN BAGI ASESOR</strong>
            <ul>
                <li>Verifikasi portofolio dapat dilakukan untuk keseluruhan unit kompetensi dalam skema sertifikasi atau dilakukan untuk masing-masing kelompok pekerjaan dalam satu skema sertifikasi.</li>
                <li>Isilah bukti portofolio sesuai ketentuan bukti berkualitas dan relevan dengan standar kompetensi kerja sebagaimana yang telah disepakati pada rekaman asesmen mandiri.</li>
                <li>Lakukan verifikasi portofolio berdasarkan aturan bukti (Valid, Asli, Terkini, Memadai - VATM).</li>
                <li>Berikan hasil verifikasi portofolio dengan memberi centang (&radic;) pada kolom yang sesuai.</li>
                <li>Jika hasil verifikasi dokumen portofolio belum memenuhi aturan bukti maka asesor melanjutkan dengan metode tanya jawab pertanyaan wawancara dan/atau verifikasi pihak ketiga.</li>
            </ul>
        </div>

        <form id="form-ia-08" method="POST" action="{{ route('formulir.ia.simpan', ['kodeForm' => 'FR.IA.08', 'pendaftaranId' => $pendaftaran->id]) }}">
            @csrf
            @php
                $savedDocs = $savedData['dokumen_portofolio'] ?? [];
                if (empty($savedDocs)) {
                    $docList = [];
                    if ($pendaftaran->buktiApl02 && $pendaftaran->buktiApl02->count() > 0) {
                        foreach ($pendaftaran->buktiApl02 as $b) {
                            $docList[] = $b->nama_dokumen ?: ($b->jenis_dokumen ?? 'Dokumen Portofolio Asesi');
                        }
                    }
                    if ($pendaftaran->dokumen && $pendaftaran->dokumen->count() > 0) {
                        foreach ($pendaftaran->dokumen as $d) {
                            $docList[] = $d->nama_dokumen ?: ($d->jenis_dokumen ?? 'Lampiran Dokumen Teknis');
                        }
                    }
                    $docList = array_unique(array_filter($docList));
                    if (!empty($docList)) {
                        foreach (array_values($docList) as $dIdx => $dNama) {
                            $savedDocs[$dIdx] = [
                                'nama' => $dNama,
                                'valid' => 'ya',
                                'asli' => 'ya',
                                'terkini' => 'ya',
                                'memadai' => 'ya',
                            ];
                        }
                    }
                }
                $savedKlarifikasi = $savedData['klarifikasi_elemen'] ?? [];
                $buktiTambahanVal = $savedData['bukti_tambahan'] ?? ($iaRecord->catatan_asesor ?? '');
            @endphp

        <!-- TABEL ATURAN BUKTI (VATM) -->
        <table class="tabel-bnsp" style="font-size: 0.85rem;">
            <thead>
                <tr>
                    <th rowspan="2" style="width: 40%; vertical-align: middle;">Bukti Portofolio:</th>
                    <th colspan="8" style="text-align: center; background: #e2e8f0;">Aturan Bukti</th>
                </tr>
                <tr>
                    <th colspan="2" style="text-align: center; width: 15%;">Valid</th>
                    <th colspan="2" style="text-align: center; width: 15%;">Asli</th>
                    <th colspan="2" style="text-align: center; width: 15%;">Terkini</th>
                    <th colspan="2" style="text-align: center; width: 15%;">Memadai</th>
                </tr>
                <tr style="background: #f8fafc; text-align: center; font-size: 0.8rem;">
                    <th></th>
                    <th>Ya</th><th>Tidak</th>
                    <th>Ya</th><th>Tidak</th>
                    <th>Ya</th><th>Tidak</th>
                    <th>Ya</th><th>Tidak</th>
                </tr>
            </thead>
            <tbody>
                @forelse($savedDocs as $bIdx => $bItem)
                    @php
                        $bNama = is_array($bItem) ? ($bItem['nama'] ?? 'Dokumen Bukti ' . ($bIdx + 1)) : $bItem;
                        $vValid = is_array($bItem) ? ($bItem['valid'] ?? 'ya') : 'ya';
                        $vAsli = is_array($bItem) ? ($bItem['asli'] ?? 'ya') : 'ya';
                        $vTerkini = is_array($bItem) ? ($bItem['terkini'] ?? 'ya') : 'ya';
                        $vMemadai = is_array($bItem) ? ($bItem['memadai'] ?? 'ya') : 'ya';
                    @endphp
                    <tr>
                        <td>
                            <strong>{{ $bIdx + 1 }}.</strong> {{ $bNama }}
                            <input type="hidden" name="dokumen_portofolio[{{ $bIdx }}][nama]" value="{{ $bNama }}">
                        </td>
                        <td style="text-align: center;"><input type="radio" name="dokumen_portofolio[{{ $bIdx }}][valid]" value="ya" {{ $vValid === 'ya' ? 'checked' : '' }} {{ $isAsesi ? 'disabled' : '' }}></td>
                        <td style="text-align: center;"><input type="radio" name="dokumen_portofolio[{{ $bIdx }}][valid]" value="tidak" {{ $vValid === 'tidak' ? 'checked' : '' }} {{ $isAsesi ? 'disabled' : '' }}></td>
                        <td style="text-align: center;"><input type="radio" name="dokumen_portofolio[{{ $bIdx }}][asli]" value="ya" {{ $vAsli === 'ya' ? 'checked' : '' }} {{ $isAsesi ? 'disabled' : '' }}></td>
                        <td style="text-align: center;"><input type="radio" name="dokumen_portofolio[{{ $bIdx }}][asli]" value="tidak" {{ $vAsli === 'tidak' ? 'checked' : '' }} {{ $isAsesi ? 'disabled' : '' }}></td>
                        <td style="text-align: center;"><input type="radio" name="dokumen_portofolio[{{ $bIdx }}][terkini]" value="ya" {{ $vTerkini === 'ya' ? 'checked' : '' }} {{ $isAsesi ? 'disabled' : '' }}></td>
                        <td style="text-align: center;"><input type="radio" name="dokumen_portofolio[{{ $bIdx }}][terkini]" value="tidak" {{ $vTerkini === 'tidak' ? 'checked' : '' }} {{ $isAsesi ? 'disabled' : '' }}></td>
                        <td style="text-align: center;"><input type="radio" name="dokumen_portofolio[{{ $bIdx }}][memadai]" value="ya" {{ $vMemadai === 'ya' ? 'checked' : '' }} {{ $isAsesi ? 'disabled' : '' }}></td>
                        <td style="text-align: center;"><input type="radio" name="dokumen_portofolio[{{ $bIdx }}][memadai]" value="tidak" {{ $vMemadai === 'tidak' ? 'checked' : '' }} {{ $isAsesi ? 'disabled' : '' }}></td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="9" style="text-align: center; color: #64748b; font-style: italic; padding: 1rem;">
                            Asesi belum mengunggah dokumen portofolio di sistem.
                        </td>
                    </tr>
                @endforelse</tbody>
        </table>

        <!-- SUBSTANSI WAWANCARA TINDAK LANJUT -->
        <table class="tabel-bnsp" style="font-size: 0.85rem;">
            <thead>
                <tr>
                    <th colspan="4" style="background: #e2e8f0; font-size: 0.88rem;">
                        Sebagai tindak lanjut dari hasil verifikasi bukti, substansi materi di bawah ini (no elemen yang diceklist) harus diklarifikasi selama wawancara:
                    </th>
                </tr>
                <tr>
                    <th style="width: 8%; text-align: center;">Cek List</th>
                    <th style="width: 22%;">No. Unit Kompetensi</th>
                    <th style="width: 15%; text-align: center;">No. Elemen</th>
                    <th>Materi / Substansi Wawancara / KUK</th>
                </tr>
            </thead>
            <tbody>
                @forelse($pendaftaran->skema->unitKompetensi as $uIdx => $unit)
                    @foreach($unit->elemenKompetensi as $eIdx => $elem)
                        @php
                            $isKlarifikasi = isset($savedKlarifikasi[$elem->id]) || empty($savedData);
                        @endphp
                        <tr>
                            <td style="text-align: center; vertical-align: middle;">
                                <input type="checkbox" name="klarifikasi_elemen[{{ $elem->id }}]" value="1" {{ $isKlarifikasi ? 'checked' : '' }} class="checkbox-bnsp checkbox-bnsp-hijau" {{ $isAsesi ? 'disabled' : '' }}>
                            </td>
                            <td>
                                <strong>{{ $unit->kode_unit }}</strong><br>
                                <span style="font-size: 0.78rem; color: #475569;">{{ $unit->judul_unit }}</span>
                            </td>
                            <td style="text-align: center; font-weight: 700;">
                                Elemen {{ $elem->nomor_elemen }}
                            </td>
                            <td>
                                <strong>{{ $elem->nama_elemen }}</strong>
                                <div style="font-size: 0.78rem; color: #64748b; margin-top: 0.2rem;">Klarifikasi bukti implementasi KUK pada proyek asesi.</div>
                            </td>
                        </tr>
                    @endforeach
                @empty
                    <tr><td colspan="4" style="text-align: center; color: #64748b;">Belum ada data unit kompetensi.</td></tr>
                @endforelse
            </tbody>
        </table>

        <!-- BUKTI TAMBAHAN -->
        <div style="border: 1px solid #334155; padding: 1rem; border-radius: 4px; margin-bottom: 1.5rem; background: #f8fafc;">
            <label style="font-weight: 700; font-size: 0.88rem; display: block; margin-bottom: 0.35rem; color: #0f172a;">
                Bukti tambahan diperlukan pada unit / elemen kompetensi sebagai berikut:
            </label>
            <textarea name="bukti_tambahan" class="input-inline-bnsp" rows="3" placeholder="Tuliskan jika terdapat bukti tambahan yang dipersyaratkan..." {{ $isAsesi ? 'readonly' : '' }}>{{ $buktiTambahanVal }}</textarea>
        </div>

        <!-- REKOMENDASI ASESOR -->
        <table class="tabel-bnsp" style="margin-bottom: 1.5rem;">
            <tr>
                <td style="width: 25%; font-weight: 700; background: #f8fafc; vertical-align: middle;">Rekomendasi Asesor:</td>
                <td>
                    <div style="margin-bottom: 0.75rem;">
                        <label style="display: flex; align-items: flex-start; gap: 0.5rem; cursor: pointer; font-weight: 600; color: #059669;">
                            <input type="radio" name="rekomendasi" value="K" {{ ($iaRecord->rekomendasi ?? 'K') === 'K' ? 'checked' : '' }} style="margin-top: 0.2rem; accent-color: #059669;" {{ $isAsesi ? 'disabled' : '' }}>
                            <span>Asesi telah memenuhi pencapaian seluruh kriteria unjuk kerja, direkomendasikan <strong>KOMPETEN</strong></span>
                        </label>
                    </div>
                    <div>
                        <label style="display: flex; align-items: flex-start; gap: 0.5rem; cursor: pointer; font-weight: 600; color: #dc2626;">
                            <input type="radio" name="rekomendasi" value="BK" {{ ($iaRecord->rekomendasi ?? '') === 'BK' ? 'checked' : '' }} style="margin-top: 0.2rem; accent-color: #dc2626;" {{ $isAsesi ? 'disabled' : '' }}>
                            <span>Asesi belum memenuhi aturan bukti (VATM), direkomendasikan <strong>BELUM KOMPETEN</strong> (perlu asesmen lanjutan)</span>
                        </label>
                    </div>
                </td>
            </tr>
        </table>
        </form>

        <!-- PENGESAHAN ASESI & ASESOR -->
        <table class="tabel-bnsp">
            <tr>
                <td style="width: 50%; font-weight: 700; background: #f8fafc;">Asesi :</td>
                <td style="width: 50%; font-weight: 700; background: #f8fafc;">Asesor :</td>
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
                            <span style="font-style: italic; color: #64748b;">(Tanda Tangan Digital Akun Asesi)</span>
                            <div style="font-size: 0.78rem; color: #64748b; margin-top: 0.25rem;">{{ date('d-m-Y') }}</div>
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
        <div style="font-size: 0.75rem; font-style: italic; color: #64748b; margin-top: 0.5rem; margin-bottom: 1.5rem;">*Coret yang tidak perlu</div>

        @include('komponen.navigasi-form-bawah', [
            'pendaftaranId' => $pendaftaran->id,
            'prevForm' => ['route' => route('formulir.ia.index', ['kodeForm' => 'FR.IA.07', 'pendaftaranId' => $pendaftaran->id]), 'label' => 'FR.IA.07 Pertanyaan Lisan'],
            'nextForm' => ['route' => route('formulir.ia09', $pendaftaran->id), 'label' => 'FR.IA.09 Pertanyaan Wawancara']
        ])

    </div>
</div>
@endsection

@push('js')
    <script src="{{ asset('js/formulir/formulir-bnsp.js') }}"></script>
@endpush
