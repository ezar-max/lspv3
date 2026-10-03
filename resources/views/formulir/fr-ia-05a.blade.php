@extends('tata-letak.dasbor')

@section('judul', 'FR.IA.05A - Pertanyaan Tertulis Pilihan Ganda')

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
        'kodeForm' => 'FR.IA.05A',
        'namaForm' => 'Pertanyaan Tertulis Pilihan Ganda',
        'pendaftaranId' => $pendaftaran->id,
        'isAsesi' => $isAsesi,
        'saveLabel' => 'Simpan Formulir',
        'saveFormId' => 'form-ia05a'
    ])

    @if($isAsesi)
        @include('komponen.banner-readonly-asesi', [
            'judul' => 'Daftar Pertanyaan Pilihan Ganda (Hanya Baca)',
            'keterangan' => 'Untuk mengerjakan ujian pilihan ganda interaktif, silakan buka menu FR.IA.05C (Lembar Ujian PG).',
            'status' => 'Hanya Baca',
            'tipe' => 'info'
        ])
    @endif

    <form id="form-ia05a" action="{{ route('formulir.ia.simpan', ['kodeForm' => 'FR.IA.05A', 'pendaftaranId' => $pendaftaran->id]) }}" method="POST">
        @csrf
        <div class="dokumen-kertas">
            
            <!-- KOP RESMI DOKUMEN STANDAR BNSP -->
            @include('komponen.kop-formulir-bnsp', [
                'kodeForm' => 'FR.IA.05A',
                'judulForm' => 'DPT – PERTANYAAN TERTULIS PILIHAN GANDA',
                'tipeDokumen' => 'Soal Pilihan Ganda'
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
                    <td>60 Menit</td>
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

            <!-- DAFTAR PERTANYAAN PILIHAN GANDA -->
            <div style="margin-bottom: 1.5rem;" x-data="{
                soalList: {{ json_encode(array_values($soalList ?? [])) }},
                tambahSoal() {
                    this.soalList.push({
                        no: this.soalList.length + 1,
                        pertanyaan: '',
                        opsi: { A: '', B: '', C: '', D: '' },
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
                    Jawab semua pertanyaan berikut:
                </div>

                <template x-for="(item, index) in soalList" :key="index">
                    <div class="soal-item" style="margin-bottom: 1.25rem; padding: 1rem; border: 1px solid #e2e8f0; border-radius: 8px; background: #ffffff; position: relative;">
                        @if(!$isAsesi)
                        <button type="button" @click="hapusSoal(index)" style="position: absolute; top: 0.5rem; right: 0.5rem; background: #fee2e2; color: #ef4444; border: none; padding: 0.25rem 0.5rem; border-radius: 4px; cursor: pointer; font-size: 0.75rem;">Hapus</button>
                        @endif

                        <div class="soal-pertanyaan" style="font-weight: 600; color: #0f172a; margin-bottom: 0.75rem; display: flex; gap: 0.5rem;">
                            <span x-text="item.no + '.'"></span>
                            @if($isAsesi)
                                <div>
                                    <span x-text="item.pertanyaan"></span>
                                    <div x-show="item.kuk" style="font-size: 0.75rem; color: #0284c7; font-weight: 700; margin-top: 0.25rem;" x-text="item.kuk"></div>
                                </div>
                            @else
                                <div style="flex: 1;">
                                    <textarea x-model="item.pertanyaan" :name="'data_jawaban[' + (index+1) + '][pertanyaan]'" style="width: 100%; padding: 0.5rem; border: 1px solid #cbd5e1; border-radius: 4px; resize: vertical;" rows="2" placeholder="Tulis pertanyaan..." required></textarea>
                                    <input type="text" x-model="item.kuk" :name="'data_jawaban[' + (index+1) + '][kuk]'" style="width: 100%; padding: 0.25rem 0.5rem; border: 1px solid #cbd5e1; border-radius: 4px; margin-top: 0.25rem; font-size: 0.8rem;" placeholder="KUK (opsional)">
                                    <input type="hidden" :name="'data_jawaban[' + (index+1) + '][no]'" :value="item.no">
                                </div>
                            @endif
                        </div>
                        
                        <div class="soal-opsi" style="display: grid; grid-template-columns: repeat(auto-fit, minmax(280px, 1fr)); gap: 0.5rem;">
                            <template x-for="(teksOpsi, abjad) in item.opsi" :key="abjad">
                                <label style="display: flex; align-items: center; gap: 0.5rem; padding: 0.4rem 0.6rem; border: 1px solid #e2e8f0; border-radius: 6px;">
                                    <strong style="color: #0369a1;" x-text="abjad + '.'"></strong>
                                    @if($isAsesi)
                                        <span x-text="teksOpsi"></span>
                                    @else
                                        <input type="text" x-model="item.opsi[abjad]" :name="'data_jawaban[' + (index+1) + '][opsi][' + abjad + ']'" style="width: 100%; border: none; outline: none; border-bottom: 1px solid #cbd5e1; padding: 0.25rem; font-size: 0.85rem;" placeholder="Pilihan jawaban..." required>
                                    @endif
                                </label>
                            </template>
                        </div>
                        
                        @if(!$isAsesi)
                        <div style="margin-top: 1rem; padding-top: 0.75rem; border-top: 1px dashed #e2e8f0;">
                            <label style="font-size: 0.85rem; font-weight: 600;">Kunci Jawaban:</label>
                            <select x-model="item.kunci" :name="'data_jawaban[' + (index+1) + '][kunci]'" style="padding: 0.25rem 0.5rem; border: 1px solid #cbd5e1; border-radius: 4px; margin-left: 0.5rem; font-weight: bold; color: #059669;" required>
                                <option value="">- Pilih Kunci -</option>
                                <option value="A">A</option>
                                <option value="B">B</option>
                                <option value="C">C</option>
                                <option value="D">D</option>
                            </select>
                        </div>
                        @endif
                    </div>
                </template>

                @if(!$isAsesi)
                <div style="margin-bottom: 1.5rem; text-align: center;">
                    <button type="button" @click="tambahSoal" class="tombol tombol-sekunder" style="border: 2px dashed #cbd5e1; background: #f8fafc; color: #475569; width: 100%; padding: 0.75rem; border-radius: 8px;">
                        + Tambah Soal Pilihan Ganda
                    </button>
                </div>
                @endif
            </div>

        <!-- TABEL PENYUSUN DAN VALIDATOR -->
        @include('komponen.tabel-penyusun-validator', [
            'pendaftaran' => $pendaftaran,
            'kodeForm' => 'FR.IA.05A',
            'tableClass' => 'tabel-bnsp'
        ])

        @include('komponen.navigasi-form-bawah', [
            'nextUrl' => $isAsesi ? route('formulir.ia05c', $pendaftaran->id) : route('formulir.ia05b', $pendaftaran->id),
            'nextLabel' => $isAsesi ? 'FR.IA.05C (Ujian PG)' : 'FR.IA.05B (Kunci Jawaban)'
        ])

    </div>
</div>
@endsection

@push('js')
    <script src="{{ asset('js/formulir/formulir-bnsp.js') }}"></script>
@endpush

