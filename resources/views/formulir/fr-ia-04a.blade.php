@extends('tata-letak.dasbor')

@section('judul', 'FR.IA.04A - Penjelasan Proyek Singkat (DIT)')

@push('css')
    <link rel="stylesheet" href="{{ asset('css/formulir/formulir-bnsp.css') }}">
    <style>
        .input-inline-bnsp.is-invalid {
            border-color: #ef4444 !important;
            box-shadow: 0 0 0 3px rgba(239, 68, 68, 0.15) !important;
        }
        .input-inline-bnsp:focus {
            border-color: #0284c7 !important;
            box-shadow: 0 0 0 3px rgba(2, 132, 199, 0.15) !important;
        }
    </style>
@endpush

@section('konten')
<div class="wadah-formulir-bnsp animasi-slide {{ auth()->check() && auth()->user()->peran === 'asesi' ? 'mode-read-only-asesi' : '' }}">
    
    @php
        $isAsesi = auth()->check() && auth()->user()->peran === 'asesi';
        $isMasterMode = isset($isMasterMode) ? $isMasterMode : (empty($pendaftaran->id) || $pendaftaran->id == 0 || !request()->filled('pendaftaran_id'));
        $asesorNama = $pendaftaran->asesor->nama_lengkap ?? (auth()->user()->peran === 'asesor' ? auth()->user()->nama_lengkap : 'Asesor LSP');
        $asesorMet = $pendaftaran->asesor->nomor_registrasi ?? 'MET.000.004455.2023';
        $asesiNama = $pendaftaran->asesi->nama_lengkap ?? 'Nama Asesi';
        $asesorTtd = $pendaftaran->tanda_tangan_asesor ?? (auth()->user()->tanda_tangan ?? null);
        $asesiTtd = $pendaftaran->tanda_tangan_asesi ?? null;
    @endphp

    <!-- ACTION BAR ATAS -->
    @include('komponen.action-bar-formulir', [
        'kodeForm' => 'FR.IA.04A',
        'namaForm' => 'Daftar Instruksi Terstruktur',
        'pendaftaranId' => $pendaftaran->id,
        'isAsesi' => $isAsesi,
        'showDraft' => false,
        'saveLabel' => 'Simpan Formulir',
        'saveFormId' => 'form-ia-04a'
    ])

    @if($isAsesi)
        @include('komponen.banner-readonly-asesi', [
            'judul' => 'Petunjuk Teknis Proyek Singkat (Hanya Baca)',
            'keterangan' => 'Pelajari TOR penugasan proyek terstruktur berikut untuk persiapan demonstrasi dan presentasi.',
            'status' => 'Panduan Asesi',
            'tipe' => 'info'
        ])
    @endif

    <div class="dokumen-kertas">
        
        <!-- KOP RESMI DOKUMEN STANDAR BNSP -->
        @include('komponen.kop-formulir-bnsp', [
            'kodeForm' => 'FR.IA.04A',
            'judulForm' => 'DIT – DAFTAR INSTRUKSI TERSTRUKTUR (PENJELASAN PROYEK SINGKAT/ KEGIATAN TERSTRUKTUR LAINNYA*)',
            'tipeDokumen' => 'Instruksi Terstruktur'
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
                <td>Sewaktu / Tempat Kerja / Mandiri** (<strong>{{ $pendaftaran->tuk_type ?? '' }}</strong>)</td>
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
        <div style="font-size: 0.75rem; font-style: italic; color: #64748b; margin-top: -1rem; margin-bottom: 1.25rem;">**Coret yang tidak perlu</div>

        <!-- PANDUAN BAGI ASESOR -->
        <div style="border: 1px solid #334155; padding: 1rem 1.25rem; background: #f8fafc; border-radius: 4px; margin-bottom: 1.5rem;">
            <strong style="display: block; font-size: 0.95rem; color: #0f172a; margin-bottom: 0.5rem; text-transform: uppercase;">PANDUAN BAGI ASESOR</strong>
            <ul style="margin: 0; padding-left: 1.2rem; font-size: 0.85rem; color: #334155; line-height: 1.6;">
                <li>Tentukan proyek singkat atau kegiatan terstruktur lainnya yang harus dipersiapkan dan dipresentasikan oleh asesi.</li>
                <li>Proyek singkat atau kegiatan terstruktur lainnya dibuat untuk keseluruhan unit kompetensi dalam Skema Sertifikasi atau untuk masing-masing kelompok pekerjaan.</li>
                <li>Kumpulkan hasil proyek singkat atau kegiatan terstruktur lainnya sesuai dengan hasil keluaran yang telah ditetapkan.</li>
            </ul>
        </div>

        <!-- KELOMPOK PEKERJAAN -->
        @php
            $unitsIa04 = $pendaftaran->skema->unitKompetensi ?? collect();
        @endphp
        <table class="tabel-bnsp" style="width: 100%; border-collapse: collapse; margin-bottom: 1.5rem; font-size: 0.88rem; border: 1px solid #000000;">
            <tbody>
                <tr>
                    <td rowspan="{{ max(1, count($unitsIa04)) + 1 }}" style="width: 25%; text-align: center; vertical-align: middle; font-weight: 700; font-size: 0.95rem; border: 1px solid #000000; padding: 10px; background-color: #ffffff; color: #000000;">
                        <span style="display: inline-block; max-width: 130px; line-height: 1.35;">Kelompok Pekerjaan 1</span>
                    </td>
                    <th style="width: 8%; text-align: center; font-weight: 700; border: 1px solid #000000; padding: 6px 4px; background-color: #ffffff; color: #000000;">No.</th>
                    <th style="width: 28%; text-align: center; font-weight: 700; border: 1px solid #000000; padding: 6px 8px; background-color: #ffffff; color: #000000;">Kode Unit</th>
                    <th style="text-align: center; font-weight: 700; border: 1px solid #000000; padding: 6px 8px; background-color: #ffffff; color: #000000;">Judul Unit</th>
                </tr>
                @forelse($unitsIa04 as $indexUnit => $unit)
                    <tr>
                        <td style="text-align: center; font-weight: 700; border: 1px solid #000000; padding: 6px 4px; color: #000000;">{{ $indexUnit + 1 }}.</td>
                        <td style="font-weight: 700; color: #000000; border: 1px solid #000000; padding: 6px 10px;">{{ $unit->kode_unit }}</td>
                        <td style="font-weight: 700; color: #000000; border: 1px solid #000000; padding: 6px 10px;">{{ $unit->judul_unit }}</td>
                    </tr>
                @empty
                    <tr><td colspan="3" style="text-align: center; color: #64748b; font-style: italic; border: 1px solid #000000; padding: 10px;">Belum ada data unit kompetensi.</td></tr>
                @endforelse
            </tbody>
        </table>

        <!-- INSTRUKSI PROYEK SINGKAT (DATABASE DRIVEN) -->
        <form id="form-ia-04a" method="POST" action="{{ route('formulir.ia.simpan', ['kodeForm' => 'FR.IA.04A', 'pendaftaranId' => $pendaftaran->id ?: 0]) }}">
            @csrf
            <input type="hidden" name="skema_id" value="{{ $pendaftaran->skema_id }}">
            <input type="hidden" name="pendaftaran_id" value="{{ $pendaftaran->id }}">
            @php
                $skenarioVal = old('skenario', $dataProyek['skenario'] ?? '');
                $waktuVal = old('waktu_menit', $dataProyek['waktu_menit'] ?? ($dataProyek['durasi_waktu'] ?? ''));
                $demoVal = old('demonstrasi', $dataProyek['demonstrasi'] ?? '');
                $waktuDemoVal = old('waktu_demo', $dataProyek['waktu_demo'] ?? '');
                $umpanBalikVal = old('umpan_balik', $dataProyek['umpan_balik'] ?? ($iaRecord->catatan_asesor ?? ''));

                $listPilihanWaktuProyek = [
                    '1 Jam' => '1 Jam',
                    '2 Jam' => '2 Jam',
                    '3 Jam' => '3 Jam',
                    '4 Jam' => '4 Jam',
                    '5 Jam' => '5 Jam',
                    '6 Jam' => '6 Jam',
                    '7 Jam' => '7 Jam',
                    '8 Jam' => '8 Jam',
                ];

                $selectedWaktuMenit = '';
                if (!empty($waktuVal)) {
                    $cleanVal = trim((string)$waktuVal);
                    if (isset($listPilihanWaktuProyek[$cleanVal])) {
                        $selectedWaktuMenit = $cleanVal;
                    } elseif (is_numeric($cleanVal)) {
                        $numVal = (int)$cleanVal;
                        if ($numVal > 0 && $numVal % 60 === 0) {
                            $candidate = ($numVal / 60) . ' Jam';
                            if (isset($listPilihanWaktuProyek[$candidate])) {
                                $selectedWaktuMenit = $candidate;
                            }
                        }
                    } elseif (preg_match('/^(\d+(?:\.\d+)?)\s*jam$/i', $cleanVal, $m)) {
                        $candidate = ((float)$m[1] == (int)$m[1] ? (int)$m[1] : (float)$m[1]) . ' Jam';
                        if (isset($listPilihanWaktuProyek[$candidate])) {
                            $selectedWaktuMenit = $candidate;
                        }
                    } elseif (preg_match('/^(\d+)\s*menit$/i', $cleanVal, $m)) {
                        $jamVal = ((int)$m[1]) / 60;
                        $candidate = ($jamVal == (int)$jamVal ? (int)$jamVal : $jamVal) . ' Jam';
                        if (isset($listPilihanWaktuProyek[$candidate])) {
                            $selectedWaktuMenit = $candidate;
                        } else {
                            $selectedWaktuMenit = $cleanVal;
                        }
                    } else {
                        $selectedWaktuMenit = $cleanVal;
                    }
                }

                $listPilihanWaktuDemo = [
                    '15 Menit' => '15 Menit',
                    '30 Menit' => '30 Menit',
                    '45 Menit' => '45 Menit',
                    '1 Jam' => '1 Jam',
                    '2 Jam' => '2 Jam',
                    '3 Jam' => '3 Jam',
                    '4 Jam' => '4 Jam',
                    '5 Jam' => '5 Jam',
                    '6 Jam' => '6 Jam',
                    '7 Jam' => '7 Jam',
                    '8 Jam' => '8 Jam',
                ];

                $selectedWaktuDemo = '';
                if (!empty($waktuDemoVal)) {
                    $cleanDemo = trim((string)$waktuDemoVal);
                    if (isset($listPilihanWaktuDemo[$cleanDemo])) {
                        $selectedWaktuDemo = $cleanDemo;
                    } elseif (is_numeric($cleanDemo)) {
                        $numDemo = (int)$cleanDemo;
                        if ($numDemo > 0 && $numDemo % 60 === 0) {
                            $candidate = ($numDemo / 60) . ' Jam';
                            if (isset($listPilihanWaktuDemo[$candidate])) {
                                $selectedWaktuDemo = $candidate;
                            }
                        } elseif (isset($listPilihanWaktuDemo[$numDemo . ' Menit'])) {
                            $selectedWaktuDemo = $numDemo . ' Menit';
                        }
                    } elseif (preg_match('/^(\d+(?:\.\d+)?)\s*jam$/i', $cleanDemo, $m)) {
                        $candidate = ((float)$m[1] == (int)$m[1] ? (int)$m[1] : (float)$m[1]) . ' Jam';
                        if (isset($listPilihanWaktuDemo[$candidate])) {
                            $selectedWaktuDemo = $candidate;
                        }
                    } elseif (preg_match('/^(\d+)\s*menit$/i', $cleanDemo, $m)) {
                        $mNum = (int)$m[1];
                        if ($mNum % 60 === 0) {
                            $candidate = ($mNum / 60) . ' Jam';
                            if (isset($listPilihanWaktuDemo[$candidate])) {
                                $selectedWaktuDemo = $candidate;
                            } else {
                                $selectedWaktuDemo = $cleanDemo;
                            }
                        } elseif (isset($listPilihanWaktuDemo[$mNum . ' Menit'])) {
                            $selectedWaktuDemo = $mNum . ' Menit';
                        } else {
                            $selectedWaktuDemo = $cleanDemo;
                        }
                    } else {
                        $selectedWaktuDemo = $cleanDemo;
                    }
                }
            @endphp
        <table class="tabel-bnsp">
            <tr>
                <td style="width: 30%; font-weight: 700; background: #f8fafc; vertical-align: top;">
                    Hal yang harus disiapkan atau dilakukan atau dihasilkan untuk suatu proyek singkat/ kegiatan terstruktur lainnya
                </td>
                <td>
                    <div style="margin-bottom: 0.5rem; font-weight: 600; color: #0f172a;">
                        Skenario proyek singkat / kegiatan terstruktur lainnya yang berisikan data informasi, lingkup bahasan dan instruksi untuk asesi: <span style="color: #ef4444; font-weight: 700;">*</span>
                    </div>
                    <textarea name="skenario" id="skenario" class="input-inline-bnsp @error('skenario') is-invalid @enderror" rows="5" placeholder="{{ $isAsesi ? 'Skenario penugasan proyek belum diisi oleh Asesor di sistem.' : 'Tuliskan data informasi, lingkup bahasan, dan instruksi penugasan proyek untuk asesi...' }}" {{ $isAsesi ? 'readonly' : 'required' }}>{{ $skenarioVal }}</textarea>
                    @error('skenario')
                        <div style="color: #ef4444; font-size: 0.8rem; margin-top: 0.25rem;">{{ $message }}</div>
                    @enderror

                    <div style="margin-top: 0.75rem; display: flex; align-items: center; gap: 0.5rem;">
                        <strong style="font-size: 0.85rem;">Waktu : <span style="color: #ef4444; font-weight: 700;">*</span></strong>
                        @if($isAsesi)
                            <input type="text" name="waktu_menit" id="waktu_menit" class="input-inline-bnsp" value="{{ $selectedWaktuMenit ?: ($waktuVal ?: '-') }}" style="max-width: 200px;" readonly>
                        @else
                            <select name="waktu_menit" id="waktu_menit" class="input-inline-bnsp @error('waktu_menit') is-invalid @enderror" style="max-width: 200px; height: 34px; cursor: pointer; background-color: #ffffff;" required>
                                <option value="" disabled {{ empty($selectedWaktuMenit) ? 'selected' : '' }}>-- Pilih Durasi Jam --</option>
                                @foreach($listPilihanWaktuProyek as $valJam => $lblJam)
                                    <option value="{{ $valJam }}" {{ $selectedWaktuMenit === $valJam ? 'selected' : '' }}>{{ $lblJam }}</option>
                                @endforeach
                                @if(!empty($waktuVal) && !array_key_exists($selectedWaktuMenit, $listPilihanWaktuProyek))
                                    <option value="{{ $waktuVal }}" selected>{{ $waktuVal }}</option>
                                @endif
                            </select>
                        @endif
                    </div>
                    @error('waktu_menit')
                        <div style="color: #ef4444; font-size: 0.8rem; margin-top: 0.25rem;">{{ $message }}</div>
                    @enderror
                </td>
            </tr>
            <tr>
                <td style="font-weight: 700; background: #f8fafc; vertical-align: top;">
                    Hal yang perlu didemonstrasikan /dipresentasikan
                </td>
                <td>
                    <div style="margin-bottom: 0.5rem; font-weight: 600; color: #0f172a;">
                        Hasil proyek singkat / kegiatan terstruktur lainnya: <span style="color: #ef4444; font-weight: 700;">*</span>
                    </div>
                    <textarea name="demonstrasi" id="demonstrasi" class="input-inline-bnsp @error('demonstrasi') is-invalid @enderror" rows="4" placeholder="{{ $isAsesi ? 'Poin demonstrasi belum diisi oleh Asesor.' : 'Tuliskan hasil proyek singkat atau kegiatan terstruktur yang perlu didemonstrasikan / dipresentasikan oleh asesi...' }}" {{ $isAsesi ? 'readonly' : 'required' }}>{{ $demoVal }}</textarea>
                    @error('demonstrasi')
                        <div style="color: #ef4444; font-size: 0.8rem; margin-top: 0.25rem;">{{ $message }}</div>
                    @enderror

                    <div style="margin-top: 0.75rem; display: flex; align-items: center; gap: 0.5rem;">
                        <strong style="font-size: 0.85rem;">Waktu : <span style="color: #ef4444; font-weight: 700;">*</span></strong>
                        @if($isAsesi)
                            <input type="text" name="waktu_demo" id="waktu_demo" class="input-inline-bnsp" value="{{ $selectedWaktuDemo ?: ($waktuDemoVal ?: '-') }}" style="max-width: 200px;" readonly>
                        @else
                            <select name="waktu_demo" id="waktu_demo" class="input-inline-bnsp @error('waktu_demo') is-invalid @enderror" style="max-width: 200px; height: 34px; cursor: pointer; background-color: #ffffff;" required>
                                <option value="" disabled {{ empty($selectedWaktuDemo) ? 'selected' : '' }}>-- Pilih Durasi Waktu --</option>
                                @foreach($listPilihanWaktuDemo as $valDemo => $lblDemo)
                                    <option value="{{ $valDemo }}" {{ $selectedWaktuDemo === $valDemo ? 'selected' : '' }}>{{ $lblDemo }}</option>
                                @endforeach
                                @if(!empty($waktuDemoVal) && !array_key_exists($selectedWaktuDemo, $listPilihanWaktuDemo))
                                    <option value="{{ $waktuDemoVal }}" selected>{{ $waktuDemoVal }}</option>
                                @endif
                            </select>
                        @endif
                    </div>
                    @error('waktu_demo')
                        <div style="color: #ef4444; font-size: 0.8rem; margin-top: 0.25rem;">{{ $message }}</div>
                    @enderror
                </td>
            </tr>
            <tr>
                <td style="font-weight: 700; background: #f8fafc; vertical-align: top;">
                    Umpan Balik Untuk Asesi: 
                    @if(!$isMasterMode)
                        <span style="color: #ef4444; font-weight: 700;">*</span>
                    @else
                        <span style="font-size: 0.75rem; font-weight: normal; color: #64748b; font-style: italic;">(Diisi saat Asesmen)</span>
                    @endif
                </td>
                <td>
                    @if($isMasterMode)
                        <textarea class="input-inline-bnsp" rows="3" placeholder="Catatan umpan balik pelaksanaan proyek singkat akan diisi oleh Asesor saat pelaksanaan asesmen." disabled readonly style="background-color: #f8fafc !important; cursor: not-allowed; color: #64748b; font-style: italic;"></textarea>
                    @else
                        <textarea name="umpan_balik" id="umpan_balik" class="input-inline-bnsp @error('umpan_balik') is-invalid @enderror" rows="3" placeholder="Tuliskan catatan umpan balik pelaksanaan proyek singkat..." {{ $isAsesi ? 'readonly' : 'required' }}>{{ $umpanBalikVal }}</textarea>
                        @error('umpan_balik')
                            <div style="color: #ef4444; font-size: 0.8rem; margin-top: 0.25rem;">{{ $message }}</div>
                        @enderror
                    @endif
                </td>
            </tr>
        </table>
        </form>

        <!-- TANDA TANGAN PENGESAHAN -->
        @include('komponen.pengesahan-asesi-asesor', [
            'kodeForm' => 'FR.IA.04A',
            'isAsesi' => $isAsesi ?? false,
            'pendaftaran' => $pendaftaran ?? null,
            'asesiNama' => $asesiNama ?? '-',
            'asesiTtd' => $asesiTtd ?? null,
            'tglTtdAsesi' => $tglTtdAsesi ?? null,
            'asesorNama' => $asesorNama ?? '-',
            'asesorMet' => $asesorMet ?? '-',
            'asesorTtd' => $asesorTtd ?? null,
            'tglAsesmen' => $tglAsesmen ?? null,
            'showSupervisor' => true
        ])
        <div style="font-size: 0.78rem; font-style: italic; color: #475569; margin-bottom: 1.5rem;">
            *) Apabila asesi pada Level 4 ke atas, berikan tugas proyek yang meliputi tentang pemecahan masalah dan analisa
        </div>

        @include('komponen.navigasi-form-bawah', [
            'nextUrl' => route('formulir.ia04b', ['pendaftaranId' => $pendaftaran->id ?: 0, 'skema_id' => $pendaftaran->skema_id]),
            'nextLabel' => 'FR.IA.04B (Penilaian Proyek)'
        ])

    </div>
</div>
@endsection

@push('js')
    <script src="{{ asset('js/formulir/formulir-bnsp.js') }}"></script>
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            var formIa04a = document.getElementById('form-ia-04a');
            if (!formIa04a) return;

            window.submitFormulir = function(formId) {
                var form = document.getElementById(formId);
                if (!form) return;

                var skenario = form.querySelector('[name="skenario"]');
                var waktuMenit = form.querySelector('[name="waktu_menit"]');
                var demonstrasi = form.querySelector('[name="demonstrasi"]');
                var waktuDemo = form.querySelector('[name="waktu_demo"]');

                var inputs = [
                    { el: skenario, label: 'Skenario proyek singkat / kegiatan terstruktur' },
                    { el: waktuMenit, label: 'Waktu pengerjaan proyek singkat' },
                    { el: demonstrasi, label: 'Hasil proyek singkat yang perlu didemonstrasikan / dipresentasikan' },
                    { el: waktuDemo, label: 'Waktu demonstrasi / presentasi' }
                ];

                for (var i = 0; i < inputs.length; i++) {
                    var item = inputs[i];
                    if (item.el && !item.el.readOnly && !item.el.disabled) {
                        if (!item.el.value || item.el.value.trim() === '') {
                            item.el.focus();
                            item.el.classList.add('is-invalid');
                            
                            if (typeof Swal !== 'undefined') {
                                Swal.fire({
                                    icon: 'warning',
                                    title: 'Kolom Wajib Diisi',
                                    text: item.label + ' wajib diisi saat membuat / menyimpan formulir FR.IA.04A!',
                                    confirmButtonColor: '#0284c7',
                                    confirmButtonText: '✓ Mengerti'
                                });
                            } else {
                                alert(item.label + ' wajib diisi saat membuat / menyimpan formulir!');
                            }
                            return false;
                        } else {
                            item.el.classList.remove('is-invalid');
                        }
                    }
                }

                if (typeof form.reportValidity === 'function' && !form.reportValidity()) {
                    return false;
                }

                form.submit();
            };

            // Reset border merah saat user mulai mengetik atau memilih opsi
            var inputsToWatch = formIa04a.querySelectorAll('textarea, input[type="text"], select');
            inputsToWatch.forEach(function (input) {
                var handler = function () {
                    if (this.value && this.value.trim() !== '') {
                        this.classList.remove('is-invalid');
                    }
                };
                input.addEventListener('input', handler);
                input.addEventListener('change', handler);
            });
        });
    </script>
@endpush

