<?php

namespace App\Http\Controllers;

use App\Models\PendaftaranAsesi;
use App\Models\JadwalAsesmen;
use App\Models\IaPenilaian;
use App\Models\RekomendasiAsesmen;
use App\Models\PenilaianAsesmen;
use App\Models\LogAktivitas;
use App\Notifications\SystemAlert;
use Illuminate\Http\Request;
use Carbon\Carbon;

class AsesorPenilaianController extends Controller
{
    /**
     * Tampilan Lembar Kerja Penilaian Live Asesor Hari H
     */
    public function index($pendaftaranId)
    {
        $user = auth()->user();
        if (!$user || !in_array($user->peran, ['asesor', 'admin', 'superadmin'])) {
            abort(403, 'Akses Ditolak: Halaman ini khusus untuk Asesor Penguji.');
        }

        $asesorId = $user->id;

        $pendaftaran = PendaftaranAsesi::with([
            'asesi.profilAsesi',
            'skema.unitKompetensi.elemenKompetensi.kriteriaUnjukKerja',
            'asesor',
            'jadwal',
            'dokumen',
            'rekomendasi',
            'penilaian',
            'iaPenilaian'
        ])
        ->when($user->peran === 'asesor', function($q) use ($asesorId) {
            $q->where(function($sub) use ($asesorId) {
                $sub->where('asesor_id', $asesorId)
                    ->orWhereHas('jadwal', fn($j) => $j->where('asesor_id', $asesorId));
            });
        })
        ->findOrFail($pendaftaranId);

        // Validasi status jadwal: jika belum mulai atau dibatalkan, kembalikan dengan notifikasi peringatan
        if ($user->peran === 'asesor' && $pendaftaran->jadwal) {
            $pendaftaran->jadwal->syncRealtimeStatus();
            if ($pendaftaran->jadwal->status_jadwal === 'dibatalkan') {
                return redirect()->route('asesor.daftar-peserta', ['jadwal_id' => $pendaftaran->jadwal_id])
                    ->with('error', 'Jadwal asesmen ini telah dibatalkan oleh LSP.');
            }
            if ($pendaftaran->jadwal->status_jadwal === 'terjadwal' || $pendaftaran->jadwal->isBelumMulai()) {
                $tgl = $pendaftaran->jadwal->tanggal_uji ? Carbon::parse($pendaftaran->jadwal->tanggal_uji)->translatedFormat('d F Y') : '-';
                $jam = $pendaftaran->jadwal->waktu_mulai ? substr($pendaftaran->jadwal->waktu_mulai, 0, 5) . ' WIB' : '-';
                $tuk = $pendaftaran->jadwal->nama_tuk ?? 'TUK';
                return redirect()->route('asesor.daftar-peserta', ['jadwal_id' => $pendaftaran->jadwal_id])
                    ->with('warning', "Ujian belum dimulai. Sesi asesmen dijadwalkan pada {$tgl} pukul {$jam} di {$tuk}.");
            }
        }

        // Ambil data soal yang sama dengan yang dikerjakan asesi
        $soalCbt = AsesiUjianController::getDaftarSoalCbt($pendaftaran->skema);
        $soalEsai = AsesiUjianController::getDaftarSoalEsai($pendaftaran->skema);
        $panduanPraktik = AsesiUjianController::getPanduanPraktikIa02($pendaftaran->skema);
        $daftarLisan = AsesiUjianController::getDaftarPertanyaanLisan($pendaftaran->skema);

        // Ambil seluruh rekor penilaian IA asesi
        $recordIa01 = IaPenilaian::where('pendaftaran_id', $pendaftaran->id)->where('kode_formulir', 'FR.IA.01')->first();
        $recordIa02 = IaPenilaian::where('pendaftaran_id', $pendaftaran->id)->where('kode_formulir', 'FR.IA.02')->first();
        $recordIa03 = IaPenilaian::where('pendaftaran_id', $pendaftaran->id)->where('kode_formulir', 'FR.IA.03')->first();
        $recordIa04a = IaPenilaian::where('pendaftaran_id', $pendaftaran->id)->where('kode_formulir', 'FR.IA.04A')->first();
        $recordIa05 = IaPenilaian::where('pendaftaran_id', $pendaftaran->id)->where('kode_formulir', 'FR.IA.05')->first();
        $recordIa06 = IaPenilaian::where('pendaftaran_id', $pendaftaran->id)->where('kode_formulir', 'FR.IA.06')->first();
        $recordIa06b = IaPenilaian::where('pendaftaran_id', $pendaftaran->id)->where('kode_formulir', 'FR.IA.06B')->first();
        $recordIa07 = IaPenilaian::where('pendaftaran_id', $pendaftaran->id)->where('kode_formulir', 'FR.IA.07')->first();
        $recordIa11 = IaPenilaian::where('pendaftaran_id', $pendaftaran->id)->where('kode_formulir', 'FR.IA.11')->first();

        // Data CBT (FR.IA.05) & Esai (FR.IA.06 / 06B)
        $savedJawabanPg = $recordIa05 ? ($recordIa05->data_jawaban['jawaban_pg'] ?? ($recordIa05->data_jawaban['jawaban'] ?? [])) : [];
        $savedSkorCbt = $recordIa05 ? ($recordIa05->data_jawaban['skor_cbt'] ?? ($recordIa05->data_jawaban['skor'] ?? null)) : null;
        $savedJawabanEsai = $recordIa06 ? ($recordIa06->data_jawaban['jawaban_esai'] ?? ($recordIa06->data_jawaban['jawaban'] ?? [])) : [];
        $savedPenilaianEsai = $recordIa06b ? ($recordIa06b->data_jawaban['penilaian_esai'] ?? []) : [];
        $savedCatatanEsai = $recordIa06b ? ($recordIa06b->catatan_asesor ?? '') : '';

        // Data Praktik (FR.IA.02)
        $savedPraktik = $recordIa02 ? ($recordIa02->data_jawaban ?? []) : [];
        $savedCatatanPraktik = $recordIa02 ? ($recordIa02->catatan_asesor ?? ($recordIa02->data_jawaban['catatan_praktik'] ?? '')) : '';
        $savedRekomendasiPraktik = $recordIa02 ? ($recordIa02->rekomendasi ?? 'K') : 'K';

        // Data Observasi (FR.IA.01)
        $savedPenilaianIa01 = $recordIa01 ? ($recordIa01->data_jawaban['penilaian_kuk'] ?? ($recordIa01->data_jawaban['pencapaian'] ?? [])) : [];
        $savedCatatanIa01 = $recordIa01 ? ($recordIa01->catatan_asesor ?? ($recordIa01->data_jawaban['catatan_observasi'] ?? ($recordIa01->data_jawaban['umpan_balik'] ?? ''))) : '';
        $savedLanjutIa01 = $recordIa01 ? ($recordIa01->data_jawaban['penilaian_lanjut'] ?? []) : [];
        $savedRekomendasiIa01 = $recordIa01 ? ($recordIa01->rekomendasi ?? 'K') : 'K';

        // Data Pertanyaan Lisan (FR.IA.03 / FR.IA.07)
        $recordLisan = $recordIa03 ?: $recordIa07;
        $savedResponLisan = $recordLisan ? ($recordLisan->data_jawaban['respon_lisan'] ?? ($recordLisan->data_jawaban['respon'] ?? [])) : [];
        $savedPenilaianLisan = $recordLisan ? ($recordLisan->data_jawaban['penilaian_lisan'] ?? ($recordLisan->data_jawaban['pencapaian'] ?? [])) : [];
        $savedCatatanLisan = $recordLisan ? ($recordLisan->catatan_asesor ?? '') : '';
        $savedRekomendasiLisan = $recordLisan ? ($recordLisan->rekomendasi ?? 'K') : 'K';

        // Data Proyek (FR.IA.04A) & Mutu (FR.IA.11)
        $savedIa04a = $recordIa04a ? ($recordIa04a->data_jawaban ?? []) : [];
        $savedCatatanIa04a = $recordIa04a ? ($recordIa04a->catatan_asesor ?? ($recordIa04a->data_jawaban['catatan_proyek'] ?? '')) : '';
        $savedRekomendasiIa04a = $recordIa04a ? ($recordIa04a->rekomendasi ?? 'K') : 'K';

        $savedIa11 = $recordIa11 ? ($recordIa11->data_jawaban ?? []) : [];
        $savedCatatanIa11 = $recordIa11 ? ($recordIa11->catatan_asesor ?? ($recordIa11->data_jawaban['catatan_mutu'] ?? '')) : '';
        $savedRekomendasiIa11 = $recordIa11 ? ($recordIa11->rekomendasi ?? 'K') : 'K';

        // Master Standards untuk IA-01 dari SchemeMasterInstrument jika tersedia
        $masterIa01 = \App\Models\SchemeMasterInstrument::where('skema_id', $pendaftaran->skema_id)
            ->whereIn('instrument_code', \App\Models\SchemeMasterInstrument::getCodeAliases('ia_01'))
            ->first();
        $masterStandardsIa01 = $masterIa01?->additional_metadata['published_standards'] 
            ?? $masterIa01?->additional_metadata['standar_industri'] 
            ?? $masterIa01?->additional_metadata['draft_standards']
            ?? [];

        $dokumenPraktik = $pendaftaran->dokumen->where('jenis_dokumen', 'Hasil Proyek / Laporan Praktik FR.IA.02')->first();
        $isFinalized = ($pendaftaran->status_pendaftaran === 'selesai' && !empty($pendaftaran->rekomendasi));

        // Rangkuman Agregat Hasil Bukti Asesmen (Bahan Rekap & Rekomendasi FR.AK.02)
        $ringkasanBukti = [
            'ia01' => [
                'exists' => !empty($recordIa01),
                'total_kuk' => 0,
                'k_count' => 0,
                'bk_count' => 0,
                'rekomendasi' => $savedRekomendasiIa01,
                'catatan' => $savedCatatanIa01,
            ],
            'ia02' => [
                'exists' => !empty($recordIa02) || !empty($savedCatatanPraktik),
                'rekomendasi' => $savedRekomendasiPraktik,
                'catatan' => $savedCatatanPraktik,
                'has_dokumen' => !empty($dokumenPraktik),
            ],
            'ia03' => [
                'exists' => !empty($recordLisan) || !empty($savedPenilaianLisan),
                'total_pertanyaan' => count($daftarLisan),
                'k_count' => collect($savedPenilaianLisan)->filter(fn($v) => $v === 'K')->count(),
                'bk_count' => collect($savedPenilaianLisan)->filter(fn($v) => $v === 'BK')->count(),
                'rekomendasi' => $savedRekomendasiLisan,
                'catatan' => $savedCatatanLisan,
            ],
            'ia05' => [
                'exists' => !empty($recordIa05),
                'skor' => $savedSkorCbt,
                'status' => ($savedSkorCbt !== null ? ($savedSkorCbt >= 70 ? 'K' : 'BK') : 'belum'),
            ],
            'ia06' => [
                'exists' => !empty($recordIa06),
                'is_graded' => !empty($recordIa06b),
                'rekomendasi' => $recordIa06b?->rekomendasi ?? 'belum',
                'catatan' => $savedCatatanEsai,
            ],
            'ia04a' => [
                'exists' => !empty($recordIa04a) || !empty($savedCatatanIa04a),
                'rekomendasi' => $savedRekomendasiIa04a,
                'catatan' => $savedCatatanIa04a,
            ],
            'ia11' => [
                'exists' => !empty($recordIa11) || !empty($savedCatatanIa11),
                'rekomendasi' => $savedRekomendasiIa11,
                'catatan' => $savedCatatanIa11,
            ],
        ];

        // Analisis Status Per Unit Kompetensi berdasarkan Observasi KUK FR.IA.01
        $statusPerUnit = [];
        $hasAnyBk = false;
        if ($pendaftaran->skema && $pendaftaran->skema->unitKompetensi) {
            foreach ($pendaftaran->skema->unitKompetensi as $unit) {
                $unitKukIds = [];
                foreach ($unit->elemenKompetensi as $elemen) {
                    foreach ($elemen->kriteriaUnjukKerja as $kuk) {
                        $unitKukIds[] = $kuk->id;
                        $ringkasanBukti['ia01']['total_kuk']++;
                        $eval = $savedPenilaianIa01[$kuk->id] ?? null;
                        if ($eval === 'K') {
                            $ringkasanBukti['ia01']['k_count']++;
                        } elseif ($eval === 'BK') {
                            $ringkasanBukti['ia01']['bk_count']++;
                            $hasAnyBk = true;
                        }
                    }
                }

                $unitEvaluations = collect($unitKukIds)->map(fn($id) => $savedPenilaianIa01[$id] ?? null)->filter();
                if ($unitEvaluations->contains('BK')) {
                    $statusPerUnit[$unit->id] = 'belum_kompeten';
                } elseif ($unitEvaluations->isNotEmpty() && $unitEvaluations->count() === count($unitKukIds) && $unitEvaluations->every(fn($v) => $v === 'K')) {
                    $statusPerUnit[$unit->id] = 'kompeten';
                } else {
                    $statusPerUnit[$unit->id] = 'belum_dinilai';
                }
            }
        }

        // Cek juga indikasi BK pada instrumen lisan, CBT, atau praktik
        if ($ringkasanBukti['ia03']['bk_count'] > 0 
            || ($savedSkorCbt !== null && $savedSkorCbt < 70) 
            || ($savedRekomendasiPraktik === 'BK' && !empty($savedCatatanPraktik))) {
            $hasAnyBk = true;
        }

        $saranRekomendasi = $hasAnyBk ? 'belum_kompeten' : 'kompeten';

        return view('asesor.penilaian-live', compact(
            'pendaftaran',
            'soalCbt',
            'soalEsai',
            'panduanPraktik',
            'daftarLisan',
            'recordIa01',
            'recordIa02',
            'recordIa03',
            'recordIa04a',
            'recordIa05',
            'recordIa06',
            'recordIa06b',
            'recordIa07',
            'recordIa11',
            'savedJawabanPg',
            'savedSkorCbt',
            'savedJawabanEsai',
            'savedPraktik',
            'savedCatatanPraktik',
            'savedRekomendasiPraktik',
            'savedPenilaianIa01',
            'savedCatatanIa01',
            'savedLanjutIa01',
            'savedRekomendasiIa01',
            'savedResponLisan',
            'savedPenilaianLisan',
            'savedCatatanLisan',
            'savedRekomendasiLisan',
            'savedPenilaianEsai',
            'savedCatatanEsai',
            'savedIa04a',
            'savedCatatanIa04a',
            'savedRekomendasiIa04a',
            'savedIa11',
            'savedCatatanIa11',
            'savedRekomendasiIa11',
            'masterStandardsIa01',
            'ringkasanBukti',
            'statusPerUnit',
            'saranRekomendasi',
            'dokumenPraktik',
            'isFinalized'
        ));
    }

    /**
     * Simpan Lembar Penilaian Live & Keputusan Akhir Asesmen (FR.IA & FR.AK.02/03)
     */
    public function simpanPenilaianLive(Request $request, $pendaftaranId)
    {
        $user = auth()->user();
        if (!$user || !in_array($user->peran, ['asesor', 'admin', 'superadmin'])) {
            abort(403, 'Akses Ditolak: Anda tidak memiliki wewenang untuk menilai asesi.');
        }

        $pendaftaran = PendaftaranAsesi::with(['skema', 'jadwal'])
            ->when($user->peran === 'asesor', function ($q) use ($user) {
                $q->where(function ($sub) use ($user) {
                    $sub->where('asesor_id', $user->id)
                        ->orWhereHas('jadwal', fn ($jadwal) => $jadwal->where('asesor_id', $user->id));
                });
            })
            ->findOrFail($pendaftaranId);

        if (!$pendaftaran->isMapaConfirmed()) {
            abort(422, 'Perencanaan Asesmen (FR.MAPA.01 & FR.MAPA.02) belum disahkan oleh Asesor. Penilaian tidak dapat disimpan.');
        }

        $tabAction = $request->input('tab_action', 'rekap');
        $isFinal = ($tabAction === 'rekap_final' && $request->filled('keputusan'));

        $request->validate([
            'keputusan' => $isFinal ? 'required|in:kompeten,belum_kompeten' : 'nullable|in:kompeten,belum_kompeten',
            'catatan_rekomendasi' => 'nullable|string',
            'tanda_tangan_asesor' => 'nullable|string',
            'penilaian_kuk' => 'nullable|array',
            'catatan_observasi' => 'nullable|string',
            'penilaian_lanjut' => 'nullable|array',
            'catatan_praktik' => 'nullable|string',
            'respon_lisan' => 'nullable|array',
            'penilaian_lisan' => 'nullable|array',
            'penilaian_esai' => 'nullable|array',
        ]);

        $ttdAsesor = $request->tanda_tangan_asesor ?: ($user->tanda_tangan ?? $pendaftaran->tanda_tangan_asesor);

        // 1. Simpan Ceklis Observasi Praktik (FR.IA.01) jika instrumen aktif di MAPA.02 atau dikirimkan
        $penilaianKuk = $request->input('penilaian_kuk', $request->input('pencapaian', []));
        $catatanObservasi = $request->input('catatan_observasi', $request->input('umpan_balik', ''));
        $penilaianLanjut = $request->input('penilaian_lanjut', []);
        $standarIndustri = $request->input('standar_industri', []);
        $rekomendasiIa01 = $request->input('rekomendasi_ia01', in_array('BK', $penilaianKuk) ? 'BK' : 'K');

        if ($pendaftaran->isInstrumenAktif('FR.IA.01') && (!empty($penilaianKuk) || $request->has('catatan_observasi') || $tabAction === 'observasi')) {
            $existingIa01 = IaPenilaian::where('pendaftaran_id', $pendaftaran->id)->where('kode_formulir', 'FR.IA.01')->first();
            $existingDataIa01 = $existingIa01 ? ($existingIa01->data_jawaban ?? []) : [];

            $dataPayloadIa01 = array_merge($existingDataIa01, [
                'penilaian_kuk' => $penilaianKuk,
                'pencapaian' => $penilaianKuk,
                'penilaian_lanjut' => $penilaianLanjut,
                'standar_industri' => $standarIndustri,
                'catatan_observasi' => $catatanObservasi,
                'umpan_balik' => $catatanObservasi,
                'total_kuk_dinilai' => count($penilaianKuk),
                'evaluated_at' => now()->toDateTimeString(),
                'saved_at' => now()->toDateTimeString(),
            ]);

            IaPenilaian::updateOrCreate(
                ['pendaftaran_id' => $pendaftaran->id, 'kode_formulir' => 'FR.IA.01'],
                [
                    'user_id' => $user->id,
                    'role' => 'asesor',
                    'data_jawaban' => $dataPayloadIa01,
                    'rekomendasi' => $rekomendasiIa01,
                    'catatan_asesor' => $catatanObservasi ?: 'Seluruh kriteria unjuk kerja telah diobservasi secara langsung di TUK.',
                    'status' => 'completed',
                    'tanda_tangan' => $ttdAsesor,
                    'tanda_tangan_asesor' => $ttdAsesor,
                    'tanggal_tanda_tangan' => now(),
                    'tanggal_ttd_asesor' => now(),
                ]
            );
        }

        // 2. Simpan Tugas Praktik Demonstrasi (FR.IA.02) jika ada catatan atau aksi praktik
        if ($pendaftaran->isInstrumenAktif('FR.IA.02') && ($request->has('catatan_praktik') || $tabAction === 'praktik')) {
            $catatanPraktik = $request->input('catatan_praktik', '');
            $rekomendasiPraktik = $request->input('rekomendasi_praktik', 'K');
            $existingIa02 = IaPenilaian::where('pendaftaran_id', $pendaftaran->id)->where('kode_formulir', 'FR.IA.02')->first();
            IaPenilaian::updateOrCreate(
                ['pendaftaran_id' => $pendaftaran->id, 'kode_formulir' => 'FR.IA.02'],
                [
                    'user_id' => $user->id,
                    'role' => 'asesor',
                    'data_jawaban' => array_merge(
                        $existingIa02 ? ($existingIa02->data_jawaban ?? []) : [],
                        [
                            'catatan_praktik' => $catatanPraktik,
                            'rekomendasi' => $rekomendasiPraktik,
                            'updated_at' => now()->toDateTimeString()
                        ]
                    ),
                    'rekomendasi' => $rekomendasiPraktik,
                    'catatan_asesor' => $catatanPraktik ?: 'Tugas praktik demonstrasi telah diverifikasi.',
                    'status' => 'completed',
                    'tanda_tangan' => $ttdAsesor,
                    'tanggal_tanda_tangan' => now(),
                ]
            );
        }

        // 3. Simpan Tanya Jawab Lisan (FR.IA.07 / FR.IA.03) jika instrumen aktif di MAPA.02
        $responLisan = $request->input('respon_lisan', $request->input('respon', []));
        $penilaianLisan = $request->input('penilaian_lisan', $request->input('penilaian_lisan_kuk', []));
        $catatanLisan = $request->input('catatan_lisan', 'Asesi mampu menjawab pertanyaan lisan pendukung dengan baik.');
        $rekomendasiLisan = $request->input('rekomendasi_lisan', in_array('BK', $penilaianLisan) ? 'BK' : 'K');

        if (($pendaftaran->isInstrumenAktif('FR.IA.07') || $pendaftaran->isInstrumenAktif('FR.IA.03')) && (!empty($responLisan) || !empty($penilaianLisan) || $tabAction === 'lisan')) {
            $dataLisanPayload = [
                'respon_lisan' => $responLisan,
                'penilaian_lisan' => $penilaianLisan,
                'evaluated_at' => now()->toDateTimeString()
            ];

            IaPenilaian::updateOrCreate(
                ['pendaftaran_id' => $pendaftaran->id, 'kode_formulir' => 'FR.IA.07'],
                [
                    'user_id' => $user->id,
                    'role' => 'asesor',
                    'data_jawaban' => $dataLisanPayload,
                    'rekomendasi' => $rekomendasiLisan,
                    'catatan_asesor' => $catatanLisan,
                    'status' => 'completed',
                    'tanda_tangan' => $ttdAsesor,
                    'tanggal_tanda_tangan' => now(),
                ]
            );

            IaPenilaian::updateOrCreate(
                ['pendaftaran_id' => $pendaftaran->id, 'kode_formulir' => 'FR.IA.03'],
                [
                    'user_id' => $user->id,
                    'role' => 'asesor',
                    'data_jawaban' => $dataLisanPayload,
                    'rekomendasi' => $rekomendasiLisan,
                    'catatan_asesor' => $catatanLisan,
                    'status' => 'completed',
                    'tanda_tangan' => $ttdAsesor,
                    'tanggal_tanda_tangan' => now(),
                ]
            );
        }

        // 4. Simpan Penilaian Esai (FR.IA.06B) jika instrumen aktif di MAPA.02
        $penilaianEsai = $request->input('penilaian_esai', []);
        if (($pendaftaran->isInstrumenAktif('FR.IA.06B') || $pendaftaran->isInstrumenAktif('FR.IA.06')) && !empty($penilaianEsai)) {
            IaPenilaian::updateOrCreate(
                ['pendaftaran_id' => $pendaftaran->id, 'kode_formulir' => 'FR.IA.06B'],
                [
                    'user_id' => $user->id,
                    'role' => 'asesor',
                    'data_jawaban' => [
                        'penilaian_esai' => $penilaianEsai,
                        'evaluated_at' => now()->toDateTimeString()
                    ],
                    'rekomendasi' => in_array('BK', $penilaianEsai) ? 'BK' : 'K',
                    'status' => 'completed',
                    'tanda_tangan' => $ttdAsesor,
                    'tanggal_tanda_tangan' => now(),
                ]
            );
        }

        // 5. Simpan Proyek / TOR (FR.IA.04A) jika ada
        if ($pendaftaran->isInstrumenAktif('FR.IA.04A') && ($request->has('catatan_proyek') || $tabAction === 'proyek')) {
            $catatanProyek = $request->input('catatan_proyek', 'Hasil proyek asesi telah diperiksa.');
            $rekomendasiProyek = $request->input('rekomendasi_proyek', 'K');
            IaPenilaian::updateOrCreate(
                ['pendaftaran_id' => $pendaftaran->id, 'kode_formulir' => 'FR.IA.04A'],
                [
                    'user_id' => $user->id,
                    'role' => 'asesor',
                    'data_jawaban' => [
                        'catatan_proyek' => $catatanProyek,
                        'rekomendasi' => $rekomendasiProyek,
                        'evaluated_at' => now()->toDateTimeString(),
                    ],
                    'rekomendasi' => $rekomendasiProyek,
                    'catatan_asesor' => $catatanProyek,
                    'status' => 'completed',
                    'tanda_tangan' => $ttdAsesor,
                    'tanggal_tanda_tangan' => now(),
                ]
            );
        }

        // 6. Simpan Ceklis Mutu (FR.IA.11) jika ada
        if ($pendaftaran->isInstrumenAktif('FR.IA.11') && ($request->has('catatan_mutu') || $tabAction === 'mutu')) {
            $catatanMutu = $request->input('catatan_mutu', 'Ceklis mutu dan spesifikasi produk telah diverifikasi.');
            $rekomendasiMutu = $request->input('rekomendasi_mutu', 'K');
            IaPenilaian::updateOrCreate(
                ['pendaftaran_id' => $pendaftaran->id, 'kode_formulir' => 'FR.IA.11'],
                [
                    'user_id' => $user->id,
                    'role' => 'asesor',
                    'data_jawaban' => [
                        'catatan_mutu' => $catatanMutu,
                        'rekomendasi' => $rekomendasiMutu,
                        'evaluated_at' => now()->toDateTimeString(),
                    ],
                    'rekomendasi' => $rekomendasiMutu,
                    'catatan_asesor' => $catatanMutu,
                    'status' => 'completed',
                    'tanda_tangan' => $ttdAsesor,
                    'tanggal_tanda_tangan' => now(),
                ]
            );
        }

        if ($isFinal) {
            $keputusan = $request->input('keputusan');
            $catatanRekomendasi = $request->input('catatan_rekomendasi', 'Asesi telah menyelesaikan seluruh tahapan asesmen dan dinyatakan ' . strtoupper($keputusan) . '.');

            // Simpan Keputusan Rekomendasi Akhir & Umpan Balik (FR.AK.02 & FR.AK.03)
            RekomendasiAsesmen::updateOrCreate(
                ['pendaftaran_id' => $pendaftaran->id],
                [
                    'asesor_id' => $user->id,
                    'keputusan' => $keputusan,
                    'catatan_rekomendasi' => $catatanRekomendasi,
                    'tanggal_rekomendasi' => now(),
                    'tanda_tangan_asesor' => $ttdAsesor,
                ]
            );

            // Update status pendaftaran menjadi selesai
            $pendaftaran->update([
                'status_pendaftaran' => 'selesai',
                'rekomendasi_asesor_status' => ($keputusan === 'kompeten' ? 'dapat_dilanjutkan' : 'tidak_dapat_dilanjutkan'),
                'catatan_peninjauan_asesor' => $catatanRekomendasi,
                'tanda_tangan_asesor' => $ttdAsesor,
                'tanggal_ttd_asesor' => now(),
                'asesor_id' => $pendaftaran->asesor_id ?: $user->id,
            ]);

            // Sinkronkan seluruh unit kompetensi dalam skema ke tabel penilaian_asesmen
            if ($pendaftaran->skema && $pendaftaran->skema->unitKompetensi) {
                $ia01Record = IaPenilaian::where('pendaftaran_id', $pendaftaran->id)->where('kode_formulir', 'FR.IA.01')->first();
                $penilaianKukMap = $ia01Record ? ($ia01Record->data_jawaban['penilaian_kuk'] ?? ($ia01Record->data_jawaban['pencapaian'] ?? [])) : [];

                foreach ($pendaftaran->skema->unitKompetensi as $unit) {
                    $hasUnitBk = false;
                    foreach ($unit->elemenKompetensi as $elemen) {
                        foreach ($elemen->kriteriaUnjukKerja as $kuk) {
                            if (($penilaianKukMap[$kuk->id] ?? null) === 'BK') {
                                $hasUnitBk = true;
                                break 2;
                            }
                        }
                    }

                    $unitNilai = ($keputusan === 'kompeten' && !$hasUnitBk) ? 'K' : 'BK';
                    $unitCatatan = ($unitNilai === 'K')
                        ? 'Kompeten sesuai hasil observasi langsung, uji teori, dan praktik demonstrasi.'
                        : 'Belum memenuhi kriteria unjuk kerja yang dipersyaratkan pada unit ini.';

                    PenilaianAsesmen::updateOrCreate(
                        [
                            'pendaftaran_id' => $pendaftaran->id,
                            'unit_id' => $unit->id,
                        ],
                        [
                            'asesor_id' => $user->id,
                            'nilai_kompetensi' => $unitNilai,
                            'catatan_asesor' => $unitCatatan
                        ]
                    );
                }
            }

            // Kirim notifikasi lonceng ke akun Asesi
            $asesi = $pendaftaran->asesi;
            if ($asesi) {
                $statusTeks = strtoupper($keputusan);
                $asesi->notify(new SystemAlert(
                    'Hasil Akhir Asesmen Tersedia',
                    "Asesor {$user->nama_lengkap} telah menetapkan keputusan akhir asesmen: {$statusTeks}. Silakan buka menu Hasil & Nilai.",
                    route('asesi.hasil-nilai'),
                    'success',
                    ['pendaftaran_id' => $pendaftaran->id, 'keputusan' => $keputusan]
                ));
            }

            LogAktivitas::catat('Penilaian Live Disahkan', "Asesor {$user->nama_lengkap} menetapkan keputusan " . strtoupper($keputusan) . " pada asesi {$pendaftaran->asesi->nama_lengkap} (Reg #{$pendaftaran->nomor_pendaftaran})");

            return redirect()->route('asesor.penilaian-live', ['pendaftaranId' => $pendaftaran->id, 'tab' => 'rekap'])
                ->with('sukses', 'Penilaian asesmen hari H dan Keputusan Rekomendasi Asesor berhasil disahkan dan ditutup!');
        } else {
            // Simpan draft rekomendasi jika ada input
            if ($request->filled('keputusan') || $request->filled('catatan_rekomendasi')) {
                RekomendasiAsesmen::updateOrCreate(
                    ['pendaftaran_id' => $pendaftaran->id],
                    [
                        'asesor_id' => $user->id,
                        'keputusan' => $request->input('keputusan', 'kompeten'),
                        'catatan_rekomendasi' => $request->input('catatan_rekomendasi', ''),
                        'tanggal_rekomendasi' => now(),
                        'tanda_tangan_asesor' => $ttdAsesor,
                    ]
                );
            }

            $targetTab = match($tabAction) {
                'rekap_final' => 'rekap',
                default => $tabAction
            };

            $pesanSukses = match($targetTab) {
                'observasi' => 'Penilaian Ceklis Observasi (FR.IA.01) berhasil disimpan!',
                'praktik' => 'Catatan Tugas Praktik (FR.IA.02) berhasil disimpan!',
                'lisan' => 'Penilaian Pertanyaan Lisan (FR.IA.03) berhasil disimpan!',
                'proyek' => 'Penilaian Proyek (FR.IA.04A) berhasil disimpan!',
                'mutu' => 'Penilaian Ceklis Mutu (FR.IA.11) berhasil disimpan!',
                'rekap' => 'Draf Rekapitulasi & Rekomendasi (FR.AK.02) berhasil disimpan!',
                default => 'Progres penilaian formulir berhasil disimpan!',
            };

            LogAktivitas::catat('Penilaian Live Disimpan (Draf)', "Asesor {$user->nama_lengkap} menyimpan draf penilaian formulir {$targetTab} pada asesi {$pendaftaran->asesi->nama_lengkap} (Reg #{$pendaftaran->nomor_pendaftaran})");

            return redirect()->route('asesor.penilaian-live', ['pendaftaranId' => $pendaftaran->id, 'tab' => $targetTab])
                ->with('sukses', $pesanSukses);
        }
    }

    /**
     * Quick toggle status jadwal asesmen (Buka Sesi / Tutup Sesi)
     */
    public function toggleStatusJadwal(Request $request, $jadwalId)
    {
        $user = auth()->user();
        if (!$user || !in_array($user->peran, ['asesor', 'admin', 'superadmin'])) {
            abort(403, 'Akses Ditolak');
        }

        $jadwal = JadwalAsesmen::when($user->peran === 'asesor', fn ($q) => $q->where('asesor_id', $user->id))
            ->findOrFail($jadwalId);
        $statusBaru = $request->input('status', 'berlangsung');
        if ($statusBaru === 'sedang_berlangsung') {
            $statusBaru = 'berlangsung';
        }

        $jadwal->status_jadwal = $statusBaru;

        // Jika dibuka, pastikan waktu hari ini dan waktu selesai belum terlewat
        if ($statusBaru === 'berlangsung') {
            $tz = config('app.timezone', 'Asia/Jakarta');
            $now = Carbon::now($tz);
            $endCarbon = $jadwal->waktu_selesai_carbon;

            // Jika jadwal sudah lampau atau waktu selesai sudah terlewat, sesuaikan otomatis
            if (!$endCarbon || $now->isAfter($endCarbon) || $jadwal->tanggal_uji < $now->toDateString()) {
                $jadwal->tanggal_uji = $now->toDateString();
                $jadwal->waktu_mulai = $now->format('H:i:s');
                $jadwal->waktu_selesai = $now->copy()->addMinutes(120)->format('H:i:s');
            }
        }
        $jadwal->save();

        $label = ($statusBaru === 'berlangsung') ? 'DIBUKA (Sedang Berlangsung)' : 'DIUBAH (' . strtoupper($statusBaru) . ')';
        LogAktivitas::catat('Status Jadwal Diubah', "Sesi jadwal {$jadwal->kode_jadwal} telah {$label} oleh {$user->nama_lengkap}");

        return redirect()->back()->with('sukses', "Status sesi jadwal asesmen berhasil diubah menjadi {$label}.");
    }

    /**
     * Perpanjang waktu durasi ujian (+15 menit atau sesuai input)
     */
    public function perpanjangWaktuJadwal(Request $request, $jadwalId)
    {
        $user = auth()->user();
        if (!$user || !in_array($user->peran, ['asesor', 'admin', 'superadmin'])) {
            abort(403, 'Akses Ditolak');
        }

        $jadwal = JadwalAsesmen::when($user->peran === 'asesor', fn ($q) => $q->where('asesor_id', $user->id))
            ->findOrFail($jadwalId);

        $menitTambahan = (int) $request->input('menit', 15);
        if ($menitTambahan <= 0) {
            $menitTambahan = 15;
        }

        $tz = config('app.timezone', 'Asia/Jakarta');
        $now = Carbon::now($tz);
        $endCarbon = $jadwal->waktu_selesai_carbon;

        $baseTime = ($endCarbon && $endCarbon->isAfter($now)) ? $endCarbon : $now;
        $newEnd = $baseTime->copy()->addMinutes($menitTambahan);

        $jadwal->waktu_selesai = $newEnd->format('H:i:s');
        if ($jadwal->status_jadwal === 'selesai' || $jadwal->status_jadwal === 'terjadwal') {
            $jadwal->status_jadwal = 'berlangsung';
        }
        $jadwal->save();

        LogAktivitas::catat('Perpanjangan Waktu Ujian', "Sesi jadwal {$jadwal->kode_jadwal} diperpanjang {$menitTambahan} menit (sampai {$newEnd->format('H:i')} WIB) oleh {$user->nama_lengkap}");

        return redirect()->back()->with('sukses', "Waktu sesi ujian berhasil diperpanjang {$menitTambahan} menit hingga pukul {$newEnd->format('H:i')} WIB.");
    }

    /**
     * Halaman Terpadu Rekap & Koreksi Ujian Teori Seluruh Asesi (FR.IA.05 & FR.IA.06)
     */
    public function koreksiTeoriMassal(Request $request)
    {
        $user = auth()->user();
        if (!$user || !in_array($user->peran, ['asesor', 'admin', 'superadmin'])) {
            abort(403, 'Akses Ditolak: Halaman ini khusus untuk Asesor Penguji.');
        }

        JadwalAsesmen::syncAllStatuses();

        $jadwalOptions = JadwalAsesmen::with('skema')
            ->when($user->peran === 'asesor', fn($q) => $q->where('asesor_id', $user->id))
            ->latest('tanggal_uji')
            ->get();

        $jadwalId = $request->get('jadwal_id');
        $activeJadwal = null;

        if ($jadwalId) {
            $activeJadwal = $jadwalOptions->firstWhere('id', $jadwalId);
        }

        if (!$activeJadwal && $jadwalOptions->isNotEmpty()) {
            $activeJadwal = $jadwalOptions->firstWhere('status_jadwal', 'berlangsung') 
                ?: $jadwalOptions->first();
        }

        $pesertaData = [];
        $soalCbt = [];
        $soalEsai = [];
        $stats = [
            'total_asesi' => 0,
            'cbt_selesai' => 0,
            'cbt_rata_rata' => 0,
            'esai_terkumpul' => 0,
            'esai_selesai_dinilai' => 0,
            'esai_perlu_dinilai' => 0,
        ];

        if ($activeJadwal) {
            $skema = $activeJadwal->skema;
            $soalCbt = AsesiUjianController::getDaftarSoalCbt($skema);
            $soalEsai = AsesiUjianController::getDaftarSoalEsai($skema);

            $pendaftarans = PendaftaranAsesi::with([
                'asesi.profilAsesi',
                'iaPenilaian'
            ])
            ->where('jadwal_id', $activeJadwal->id)
            ->get();

            $totalSkorCbt = 0;
            $countSkorCbt = 0;

            foreach ($pendaftarans as $p) {
                $recordIa05 = $p->iaPenilaian->firstWhere('kode_formulir', 'FR.IA.05');
                $recordIa06 = $p->iaPenilaian->firstWhere('kode_formulir', 'FR.IA.06');
                $recordIa06b = $p->iaPenilaian->firstWhere('kode_formulir', 'FR.IA.06B');

                // Data CBT (FR.IA.05)
                $jawabanPg = $recordIa05 ? ($recordIa05->data_jawaban['jawaban_pg'] ?? []) : [];
                $skorCbt = $recordIa05 ? ($recordIa05->data_jawaban['skor_cbt'] ?? null) : null;
                $statusCbt = 'belum';
                if ($recordIa05 && ($recordIa05->status === 'submitted' || $recordIa05->status === 'completed')) {
                    $statusCbt = 'selesai';
                } elseif (!empty($jawabanPg)) {
                    $statusCbt = 'draft';
                }

                if ($skorCbt !== null) {
                    $totalSkorCbt += $skorCbt;
                    $countSkorCbt++;
                }

                // Data Esai (FR.IA.06 & FR.IA.06B)
                $jawabanEsai = $recordIa06 ? ($recordIa06->data_jawaban['jawaban_esai'] ?? []) : [];
                $penilaianEsai = $recordIa06b ? ($recordIa06b->data_jawaban['penilaian_esai'] ?? []) : [];
                $catatanEsai = $recordIa06b ? ($recordIa06b->catatan_asesor ?? '') : '';
                
                $statusEsai = 'belum';
                if ($recordIa06 && ($recordIa06->status === 'submitted' || $recordIa06->status === 'completed')) {
                    $statusEsai = 'terkumpul';
                } elseif (!empty(array_filter($jawabanEsai))) {
                    $statusEsai = 'draft';
                }

                $isEsaiGraded = false;
                if (!empty($soalEsai) && !empty($penilaianEsai)) {
                    $isEsaiGraded = true;
                    foreach (array_keys($soalEsai) as $numSoal) {
                        if (empty($penilaianEsai[$numSoal])) {
                            $isEsaiGraded = false;
                            break;
                        }
                    }
                }

                if ($statusCbt === 'selesai') {
                    $stats['cbt_selesai']++;
                }
                if ($statusEsai === 'terkumpul') {
                    $stats['esai_terkumpul']++;
                    if ($isEsaiGraded) {
                        $stats['esai_selesai_dinilai']++;
                    } else {
                        $stats['esai_perlu_dinilai']++;
                    }
                }

                $pesertaData[] = [
                    'pendaftaran' => $p,
                    'record_ia05' => $recordIa05,
                    'record_ia06' => $recordIa06,
                    'record_ia06b' => $recordIa06b,
                    'skor_cbt' => $skorCbt,
                    'status_cbt' => $statusCbt,
                    'jawaban_pg' => $jawabanPg,
                    'status_esai' => $statusEsai,
                    'jawaban_esai' => $jawabanEsai,
                    'penilaian_esai' => $penilaianEsai,
                    'catatan_esai' => $catatanEsai,
                    'is_esai_graded' => $isEsaiGraded,
                ];
            }

            $stats['total_asesi'] = count($pesertaData);
            $stats['cbt_rata_rata'] = $countSkorCbt > 0 ? round($totalSkorCbt / $countSkorCbt, 1) : 0;
        }

        return view('asesor.koreksi-teori', compact(
            'jadwalOptions',
            'activeJadwal',
            'pesertaData',
            'soalCbt',
            'soalEsai',
            'stats'
        ));
    }

    /**
     * Simpan Koreksi Massal Ujian Esai (FR.IA.06B) untuk seluruh asesi pada jadwal
     */
    public function simpanKoreksiTeoriMassal(Request $request)
    {
        $user = auth()->user();
        if (!$user || !in_array($user->peran, ['asesor', 'admin', 'superadmin'])) {
            abort(403, 'Akses Ditolak');
        }

        $jadwalId = $request->input('jadwal_id');
        $penilaianMassal = $request->input('penilaian_esai', []);
        $catatanMassal = $request->input('catatan_esai', []);

        if (empty($penilaianMassal)) {
            return redirect()->back()->with('warning', 'Tidak ada penilaian esai yang dikirimkan.');
        }

        $countUpdated = 0;
        $ttdAsesor = $user->tanda_tangan ?: null;

        foreach ($penilaianMassal as $pendaftaranId => $penilaianPerSoal) {
            $pendaftaran = PendaftaranAsesi::with('jadwal')->find($pendaftaranId);
            if (!$pendaftaran) continue;

            // Authorization Guard: Asesor hanya bisa mengoreksi pendaftarannya sendiri
            if ($user->peran === 'asesor') {
                $isAssigned = ($pendaftaran->asesor_id === $user->id) || 
                              ($pendaftaran->jadwal && $pendaftaran->jadwal->asesor_id === $user->id);
                
                if (!$isAssigned) {
                    continue; // Skip silently to not leak data existence
                }
            }

            $evaluations = is_array($penilaianPerSoal) ? $penilaianPerSoal : [];
            $catatan = $catatanMassal[$pendaftaranId] ?? 'Penilaian hasil ujian esai selesai diperiksa.';

            IaPenilaian::updateOrCreate(
                ['pendaftaran_id' => $pendaftaran->id, 'kode_formulir' => 'FR.IA.06B'],
                [
                    'user_id' => $user->id,
                    'role' => 'asesor',
                    'data_jawaban' => [
                        'penilaian_esai' => $evaluations,
                        'evaluated_at' => now()->toDateTimeString(),
                        'graded_by' => $user->nama_lengkap
                    ],
                    'rekomendasi' => in_array('BK', $evaluations) ? 'BK' : 'K',
                    'catatan_asesor' => $catatan,
                    'status' => 'completed',
                    'tanda_tangan' => $ttdAsesor,
                    'tanggal_tanda_tangan' => now(),
                ]
            );

            $countUpdated++;
        }

        LogAktivitas::catat('Koreksi Esai Massal', "Asesor {$user->nama_lengkap} menyimpan koreksi esai untuk {$countUpdated} asesi pada jadwal ID #{$jadwalId}");

        return redirect()->route('asesor.koreksi-teori', ['jadwal_id' => $jadwalId])
            ->with('sukses', "Berhasil menyimpan penilaian ujian esai untuk {$countUpdated} asesi.");
    }
}
