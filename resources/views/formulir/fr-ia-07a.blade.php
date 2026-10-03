@extends('tata-letak.dasbor')

@section('judul', 'FR.IA.07A - Daftar Pertanyaan Lisan')

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
    @endphp

    <!-- ACTION BAR ATAS -->
    @include('komponen.action-bar-formulir', [
        'kodeForm' => 'FR.IA.07A',
        'namaForm' => 'Daftar Pertanyaan Lisan',
        'pendaftaranId' => $pendaftaran->id,
        'isAsesi' => $isAsesi,
        'saveLabel' => 'Simpan Formulir',
        'saveFormId' => 'form-ia07a'
    ])

    @if($isAsesi)
        @include('komponen.banner-readonly-asesi', [
            'judul' => 'Daftar Pertanyaan Lisan (Hanya Baca)',
            'keterangan' => 'Pertanyaan lisan akan diberikan secara langsung oleh asesor pada saat sesi uji lisan.',
            'status' => 'Hanya Baca',
            'tipe' => 'info'
        ])
    @endif

    <form id="form-ia07a" action="{{ route('formulir.ia.simpan', ['kodeForm' => 'FR.IA.07A', 'pendaftaranId' => $pendaftaran->id]) }}" method="POST">
        @csrf
        <div class="dokumen-kertas">
            
            <!-- KOP RESMI DOKUMEN STANDAR BNSP -->
            @include('komponen.kop-formulir-bnsp', [
                'kodeForm' => 'FR.IA.07A',
                'judulForm' => 'DPL – DAFTAR PERTANYAAN LISAN',
                'tipeDokumen' => 'Soal Uji Lisan'
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
                <tr>
                    <td colspan="2" style="font-weight: 600;">Waktu</td>
                    <td>:</td>
                    <td>45 Menit</td>
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

            <!-- DAFTAR SOAL LISAN -->
            <div style="margin-bottom: 1.5rem;" x-data="{
                soalList: {{ json_encode(array_values($soalList ?? [])) }},
                tambahSoal() {
                    this.soalList.push({
                        no: this.soalList.length + 1,
                        pertanyaan: '',
                        kunci: '',
                        kuk: ''
                    });
                },
                hapusSoal(index) {
                    this.soalList.splice(index, 1);
                    this.soalList.forEach((item, i) => item.no = i + 1);
                }
            }">
                <div style="font-weight: 800; font-size: 0.95rem; color: #0f172a; margin-bottom: 0.75rem;">
                    Daftar Pertanyaan Lisan:
                </div>

                <table class="tabel-bnsp">
                    <tbody>
                        <template x-for="(item, index) in soalList" :key="index">
                            <tr>
                                <td style="width: 5%; text-align: center; font-weight: 700; vertical-align: top;" x-text="item.no + '.'"></td>
                                <td>
                                    @if($isAsesi)
                                        <div style="font-weight: 600; color: #0f172a; margin-bottom: 0.25rem;" x-text="item.pertanyaan"></div>
                                        <span style="font-size: 0.75rem; color: #0284c7; font-weight: 700;" x-text="item.kuk"></span>
                                    @else
                                        <div style="margin-bottom: 0.5rem;">
                                            <textarea x-model="item.pertanyaan" :name="'data_jawaban[' + (index+1) + '][pertanyaan]'" style="width: 100%; padding: 0.5rem; border: 1px solid #cbd5e1; border-radius: 4px; resize: vertical;" rows="2" placeholder="Tulis pertanyaan lisan..." required></textarea>
                                        </div>
                                        <div style="margin-bottom: 0.5rem;">
                                            <textarea x-model="item.kunci" :name="'data_jawaban[' + (index+1) + '][kunci]'" style="width: 100%; padding: 0.5rem; border: 1px solid #10b981; border-radius: 4px; resize: vertical;" rows="2" placeholder="Kunci jawaban atau pedoman penilaian..." required></textarea>
                                        </div>
                                        <div style="display: flex; gap: 0.5rem; align-items: center;">
                                            <input type="text" x-model="item.kuk" :name="'data_jawaban[' + (index+1) + '][kuk]'" style="flex: 1; padding: 0.25rem 0.5rem; border: 1px solid #cbd5e1; border-radius: 4px; font-size: 0.8rem;" placeholder="KUK (opsional)">
                                            <input type="hidden" :name="'data_jawaban[' + (index+1) + '][no]'" :value="item.no">
                                            <button type="button" @click="hapusSoal(index)" style="background: #fee2e2; color: #ef4444; border: none; padding: 0.25rem 0.5rem; border-radius: 4px; cursor: pointer; font-size: 0.75rem;">Hapus</button>
                                        </div>
                                    @endif
                                </td>
                            </tr>
                        </template>
                    </tbody>
                </table>
                
                @if(!$isAsesi)
                <div style="margin-top: 1rem; text-align: center;">
                    <button type="button" @click="tambahSoal" class="tombol tombol-sekunder" style="border: 2px dashed #cbd5e1; background: #f8fafc; color: #475569; width: 100%; padding: 0.75rem; border-radius: 8px;">
                        + Tambah Soal Lisan
                    </button>
                </div>
                @endif
            </div>

        <!-- TABEL PENYUSUN DAN VALIDATOR -->
        @include('komponen.tabel-penyusun-validator', [
            'pendaftaran' => $pendaftaran,
            'kodeForm' => 'FR.IA.07A',
            'tableClass' => 'tabel-bnsp'
        ])

        @include('komponen.navigasi-form-bawah', [
            'nextUrl' => route('formulir.ia07', $pendaftaran->id),
            'nextLabel' => 'FR.IA.07 (Uji Lisan & Pencatatan)'
        ])

    </div>
</div>
@endsection

@push('js')
    <script src="{{ asset('js/formulir/formulir-bnsp.js') }}"></script>
@endpush

